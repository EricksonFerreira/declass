<?php

namespace App\Actions\Reserva\Checkout\Finalizar;

use App\Actions\Reserva\Checkout\TermoDevolucao\Exibir\ExibirTermoDevolucaoReservaComumAction;
use App\Models\ContratoCheckout;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FinalizarCheckoutReservaComumAction
{
    public function __construct(
        private ExibirTermoDevolucaoReservaComumAction $exibirTermoDevolucaoAction,
    ) {}

    public function handle(Reserva $reserva, string $assinaturaBase64): ContratoCheckout
    {
        $reserva->loadMissing([
            'contratoCheckout',
            'usuario',
            'empresa',
            'veiculo',
            'grupoFrota',
            'estacaoRetirada',
            'estacaoDevolucao',
            'opcionais',
            'contratoCheckin',
            'modelos',
            'statusReserva',
            'cupomDesconto',
            'transacoes.user',
            'transacoes.pagoPor',
            'contratoCheckout.veiculo.marca',
            'contratoCheckout.veiculo.modelo',
            'contratoCheckin.veiculo.marca',
            'contratoCheckin.veiculo.modelo',
            'contratoCheckin.veiculo.grupoFrota',
            'contratoCheckin.marca',
            'contratoCheckin.modelo',
            'veiculoDanos.dano.categoria',
            'veiculoDanos.imagem',
            'veiculoDanos.usuarioCorrecao',
            'avariaCobrancas',
            'calcao',
        ]);

        $checkout = $reserva->contratoCheckout;

        if (! $checkout) {
            throw new RuntimeException('Check-out não encontrado para esta reserva.');
        }

        if (
            $checkout->assinado === 'S'
            && filled($checkout->pdf_path)
            && Storage::disk('public')->exists($checkout->pdf_path)
        ) {
            return $checkout;
        }

        if (blank($assinaturaBase64)) {
            throw new RuntimeException('A assinatura do termo de devolução não foi informada.');
        }

        if (! str_starts_with($assinaturaBase64, 'data:image/')) {
            throw new RuntimeException('O formato da assinatura é inválido.');
        }

        $arquivoPdfSalvo = null;

        DB::beginTransaction();

        try {
            $checkout->forceFill([
                'assinatura' => null,
                'assinado' => 'N',
                'data_assinatura' => now(),
                'pdf_path' => null,
                'pdf_hash' => null,
            ])->save();

            $checkout->refresh();

            $checkout->setAttribute('assinatura', $assinaturaBase64);
            $checkout->setAttribute('assinado', 'N');

            $reserva->setRelation('contratoCheckout', $checkout);

            $pdfContent = $this->exibirTermoDevolucaoAction->gerarPdf($reserva);

            if (blank($pdfContent)) {
                throw new RuntimeException('O conteúdo do termo de devolução não foi gerado.');
            }

            if (! str_starts_with(ltrim($pdfContent), '%PDF')) {
                throw new RuntimeException('O conteúdo gerado não corresponde a um PDF válido.');
            }

            $diretorio = sprintf(
                'contratos/checkout/empresa_%s/reserva_%s',
                $reserva->empresa_id ?: 'sem_empresa',
                $reserva->id
            );

            $nomeArquivo = sprintf(
                'termo-devolucao-%s-%s.pdf',
                $reserva->id,
                now()->format('YmdHis')
            );

            $pdfPath = trim($diretorio, '/\\').'/'.$nomeArquivo;

            if (! Storage::disk('public')->put($pdfPath, $pdfContent)) {
                throw new RuntimeException('Não foi possível salvar o termo de devolução.');
            }

            if (! Storage::disk('public')->exists($pdfPath)) {
                throw new RuntimeException('O termo de devolução não foi encontrado após o salvamento.');
            }

            $arquivoPdfSalvo = $pdfPath;

            $checkout->forceFill([
                'assinatura' => null,
                'pdf_path' => $pdfPath,
                'pdf_hash' => hash('sha256', $pdfContent),
                'data_assinatura' => $checkout->data_assinatura ?? now(),
                'assinado' => 'S',
            ])->save();

            // Gerar Operação Fiscal, Calcular Tributos e Despachar Emissão de NFS-e
            $operacaoFiscal = \App\Models\OperacaoFiscal::create([
                'empresa_id' => $reserva->empresa_id,
                'locacao_id' => $reserva->id,
                'tipo' => \App\Modules\Fiscal\Enums\TipoOperacaoFiscal::LOCACAO_VEICULO->value,
                'data_fato_gerador' => now(),
                'valor_operacao' => (float) ($reserva->valor_total_final ?? 0.0),
                'valor_base_calculo' => (float) ($reserva->valor_total_final ?? 0.0),
                'status' => 'PENDENTE',
            ]);

            app(\App\Modules\Fiscal\Actions\CalcularTributosAction::class)->handle($operacaoFiscal);

            \App\Modules\Fiscal\Jobs\EmitirNfseJob::dispatch($operacaoFiscal->id);

            DB::commit();

            return $checkout->fresh();
        } catch (Throwable $e) {
            DB::rollBack();

            if (
                filled($arquivoPdfSalvo)
                && Storage::disk('public')->exists($arquivoPdfSalvo)
            ) {
                Storage::disk('public')->delete($arquivoPdfSalvo);
            }

            Log::error('Erro ao finalizar o checkout da reserva', [
                'reserva_id' => $reserva->id,
                'contrato_checkout_id' => $checkout->id,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}