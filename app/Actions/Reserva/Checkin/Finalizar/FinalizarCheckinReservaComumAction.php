<?php

namespace App\Actions\Reserva\Checkin\Finalizar;

use App\Actions\Reserva\Checkin\Contrato\Exibir\ExibirContratoCheckinReservaComumAction;
use App\DTO\Reserva\ReservaResolvida;
use App\Models\ContratoCheckin;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\UpgradeVeiculoService;

class FinalizarCheckinReservaComumAction
{
    use ReservaHelpersTrait;

    public function __construct(
        private ReservaService $reservaService,
        private MovimentacaoService $movimentacaoService,
        private ExibirContratoCheckinReservaComumAction $exibirContratoAction,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;

        $contratoId = (int) $request->input('contrato_id', $registro->contratoCheckin->id ?? 0);
        $veiculoId = (int) $request->input('veiculo_id', $registro->contratoCheckin->veiculo_id ?? 0);
        $assinatura = (string) $request->input('assinatura');
        $modo = $request->input('modo', 'digital');

        $contrato = ContratoCheckin::find($contratoId);
        if (!$contrato) {
            return response()->json(['success' => false, 'message' => 'Contrato de check-in nao encontrado.'], 404);
        }

        try {
            DB::beginTransaction();

            if ($modo === 'upload' && $request->hasFile('contrato_pdf')) {
                // Modo upload: salvar o PDF anexado diretamente
                $file = $request->file('contrato_pdf');
                if (!$file->isValid() || $file->getMimeType() !== 'application/pdf') {
                    return response()->json(['success' => false, 'message' => 'Arquivo inválido. Envie um PDF.'], 422);
                }

                $caminhoArquivo = $this->salvarArquivo($file, 'contratos', 'checkin_' . $registro->id . '_' . time());
                if (!$caminhoArquivo) {
                    throw new \Exception('Erro ao salvar PDF do contrato.');
                }

                $contrato->pdf_path = $caminhoArquivo;
                $contrato->assinado = 'S';
                $contrato->data_assinatura = now();
                $contrato->assinatura = 'PDF anexado';
                $contrato->save();
            } else {
                // Modo digital: assinatura no canvas
                // Optional: salvar a base64 usando $this->salvarArquivo($assinatura, 'checkout/reserva_...');
                $contrato->assinatura = $assinatura;
                $contrato->data_assinatura = now();
                $contrato->assinado = 'S';
                $contrato->save();

                $this->gerarESalvarPdfContrato($registro);
            }

            $this->reservaService->checkInRealizado((int) $registro->id, $contrato->veiculo_id);

            $this->movimentacaoService->realizarCheckIn(
                $veiculoId,
                (int) $registro->estacao_retirada_id,
                (int) $registro->id,
                'Check-in realizado e contrato assinado'
            );

            $this->reservaService->adicionaLog(
                (int) $registro->id,
                Auth::id(),
                ['veiculo' => $contrato->veiculo?->nome ?? "ID {$contrato->veiculo_id}"],
                'Contrato de check-in assinado' . ($modo === 'upload' ? ' (PDF anexado)' : ''),
                'success'
            );

            $this->sincronizarAssinaturaCheckinMotoristaApp($registro, $contratoId);

            DB::commit();

            $redirectUrl = route('reserva.show', $reserva->codigo);
            if ($request->input('retorno')) {
                $redirectUrl = route('reserva.show', $request->input('retorno'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Check-in finalizado com sucesso.',
                'redirect' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao finalizar check-in: ' . $e->getMessage()], 500);
        }
    }

    private function gerarESalvarPdfContrato($registro): void
    {
        try {
            $reserva = $this->reservaService->getById((int) $registro->id, [
                'usuario', 'empresa', 'veiculo', 'grupoFrota', 'estacaoRetirada',
                'estacaoDevolucao', 'opcionais', 'contratoCheckin', 'modelos',
                'statusReserva', 'cupomDesconto', 'transacoes.user', 'transacoes.pagoPor',
                'contratoCheckin.marca', 'contratoCheckin.modelo', 'contratoCheckin.veiculo.marca',
                'contratoCheckin.veiculo.modelo', 'contratoCheckin.veiculo.grupoFrota',
                'contratoCheckin.grupoFrotaOriginal', 'contratoCheckin.veiculoPrevisto',
                'contratoCheckin.usuarioUpgrade', 'contratoCheckin.calcao', 'contratoCheckout',
                'calcao', 'veiculoDanos.dano.categoria', 'veiculoDanos.imagem',
                'veiculoDanos.usuarioCorrecao', 'avariaCobrancas', 'multas', 'nfseAtual',
            ]);

            if (!$reserva || !$reserva->contratoCheckin) return;

            $pdfContent = $this->exibirContratoAction->gerarPdf($reserva);
            $filename = 'contratos/checkin_' . $registro->id . '_' . time() . '.pdf';
            Storage::disk('public')->put($filename, $pdfContent);

            $reserva->contratoCheckin->update(['pdf_path' => $filename]);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar PDF do contrato', ['reserva_id' => $registro->id, 'error' => $e->getMessage()]);
        }
    }

    private function sincronizarAssinaturaCheckinMotoristaApp($registro, int $contratoId): void
    {
        $motoristaApp = \App\Models\ReservaMotoristaApp::whereHas('codigoReserva', function ($q) use ($registro) {
            $q->where('reserva_id', (int) $registro->id);
        })
        ->orWhere('id', (int) $registro->id)
        ->first();

        if (!$motoristaApp) return;

        $contrato = ContratoCheckin::find($contratoId);
        if (!$contrato) return;

        $documentos = is_array($motoristaApp->documentos_json ?? null) ? $motoristaApp->documentos_json : [];
        data_set($documentos, 'checkin.reserva_operacional_id', (int) $registro->id);
        data_set($documentos, 'checkin.contrato_checkin_id', $contratoId);
        data_set($documentos, 'checkin.assinado', $contrato->assinado === 'S');
        data_set($documentos, 'checkin.assinado_em', $contrato->data_assinatura ? \Carbon\Carbon::parse($contrato->data_assinatura)->toDateTimeString() : now()->toDateTimeString());
        data_set($documentos, 'checkin.pdf_path', $contrato->pdf_path);

        $motoristaApp->forceFill(['documentos_json' => $documentos])->save();
    }
}
