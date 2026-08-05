<?php

namespace App\Actions\Reserva\Checkout\Realizar;

use App\Actions\Financeiro\CreateLancamentoAction;
use App\Models\ArquivoRastreado;
use App\Models\CategoriaFinanceira;
use App\Models\Calcao;
use App\Models\ContratoCheckout;
use App\Models\Reserva;
use App\Services\CalcaoService;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use App\Support\ArquivoAnexoHelper;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RealizarCheckoutReservaComumAction
{
    public function __construct(
        private ReservaService $reservaService,
        private MovimentacaoService $movimentacaoService,
        private CalcaoService $calcaoService,
        private CreateLancamentoAction $createLancamentoAction,
    ) {}

    public function handle(
        Reserva $reserva,
        array $dados,
        Request $request
    ): ContratoCheckout {
        $this->validarCheckout($reserva, $dados);

        $arquivosSalvos = [];

        DB::beginTransaction();

        try {
            $diretorio = $this->obterDiretorioCheckout($reserva);

            $contratoCheckout = ContratoCheckout::create([
                'reserva_id' => (int) $reserva->id,
                'veiculo_id' => (int) $reserva->veiculo_id,
                'empresa_id' => $reserva->empresa_id,
                'quilometragem_devolucao' => (float) $dados['quilometragem_devolucao'],
                'nivel_combustivel_devolucao' => $dados['nivel_combustivel_devolucao'],
                'data_devolucao' => $dados['data_devolucao'],
                'hora_devolucao' => $dados['hora_devolucao'],
                'notas_devolucao' => $dados['notas_devolucao'] ?? null,
                'observacoes_devolucao' => $dados['observacoes_devolucao'] ?? null,
            ]);

            $dadosFotosCheckout = $this->salvarFotosCheckout(
                request: $request,
                diretorio: $diretorio,
                reserva: $reserva,
                contratoCheckout: $contratoCheckout,
                arquivosSalvos: $arquivosSalvos,
            );

            if ($dadosFotosCheckout !== []) {
                $contratoCheckout->forceFill($dadosFotosCheckout)->save();
            }

            $this->atualizarDocumentosCheckout(
                reserva: $reserva,
                contratoCheckout: $contratoCheckout,
                dados: $dados,
                dadosFotosCheckout: $dadosFotosCheckout,
            );

            if ($arquivosSalvos !== []) {
                ArquivoRastreado::query()
                    ->whereIn('path', $arquivosSalvos)
                    ->update([
                        'entidade_id' => $contratoCheckout->id,
                    ]);
            }

            if (! empty($dados['assinatura'])) {
                $assinaturaPath = $this->salvarAssinatura(
                    assinaturaBase64: (string) $dados['assinatura'],
                    diretorio: $diretorio,
                    reserva: $reserva,
                    contratoCheckout: $contratoCheckout,
                );

                $arquivosSalvos[] = $assinaturaPath;

                $contratoCheckout->forceFill([
                    'assinatura' => $assinaturaPath,
                    'data_assinatura' => now(),
                ])->save();
            }

            $this->movimentacaoService->realizarCheckOut(
                (int) $reserva->veiculo_id,
                (int) $reserva->estacao_devolucao_id,
                (int) $reserva->id,
                'Check-out com vistoria de devolução',
                (int) $dados['quilometragem_devolucao']
            );

            if (! empty($reserva->estacao_devolucao_id)) {
                $this->movimentacaoService->registrarChegadaNaEstacao(
                    (int) $reserva->veiculo_id,
                    (int) $reserva->estacao_devolucao_id,
                    'Chegada real registrada no check-out da reserva '.$reserva->id,
                    true
                );
            }

            $this->reservaService->checkOutRealizado(
                (int) $reserva->id
            );

            // $this->liberarCalcao($reserva);

            $this->reservaService->adicionaLog(
                (int) $reserva->id,
                Auth::id(),
                [
                    'acao' => 'Check-out com vistoria concluído',
                    'contrato_checkout_id' => $contratoCheckout->id,
                    'veiculo_id' => $reserva->veiculo_id,
                    'quilometragem_devolucao' => $dados['quilometragem_devolucao'],
                    'assinatura_salva' => filled($contratoCheckout->assinatura),
                ],
                'Check-out concluído',
                'success'
            );

            DB::commit();

            return $contratoCheckout->fresh();
        } catch (Throwable $e) {
            DB::rollBack();

            $this->removerArquivosSalvos($arquivosSalvos);

            Log::error('Erro ao realizar checkout da reserva', [
                'reserva_id' => $reserva->id,
                'empresa_id' => $reserva->empresa_id,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function validarCheckout(
        Reserva $reserva,
        array $dados
    ): void {
        if (! $reserva->podeFazerCheckout()) {
            throw new RuntimeException(
                'Reserva não está em status válido para check-out.'
            );
        }

        if (
            ContratoCheckout::query()
                ->where('reserva_id', $reserva->id)
                ->exists()
        ) {
            throw new RuntimeException(
                'Check-out desta reserva já foi registrado.'
            );
        }

        if (! $reserva->veiculo_id) {
            throw new RuntimeException(
                'Veículo não vinculado à reserva.'
            );
        }

        if (! $reserva->estacao_devolucao_id) {
            throw new RuntimeException(
                'Estação de devolução não informada na reserva.'
            );
        }

        $kmInicial = $reserva->contratoCheckin?->quilometragem_atual;

        if ($kmInicial !== null) {
            $kmDevolucao = (float) ($dados['quilometragem_devolucao'] ?? 0);

            if ($kmDevolucao < (float) $kmInicial) {
                throw new RuntimeException(
                    'A quilometragem na devolução deve ser maior ou igual à quilometragem inicial do check-in (KM '
                    .$kmInicial.').'
                );
            }
        }
    }

    private function salvarFotosCheckout(
        Request $request,
        string $diretorio,
        Reserva $reserva,
        ContratoCheckout $contratoCheckout,
        array &$arquivosSalvos
    ): array {
        $mapFotos = [
            'foto_direita_co' => 'foto_direita_path',
            'foto_esquerda_co' => 'foto_esquerda_path',
            'foto_frente_co' => 'foto_frente_path',
            'foto_tras_co' => 'foto_tras_path',
            'foto_velocimetro_co' => 'foto_velocimetro_path',
            'foto_combustivel_co' => 'foto_combustivel_path',
        ];

        $dadosFotos = [];

        foreach ($mapFotos as $input => $coluna) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $arquivo = $request->file($input);

            if (! $arquivo instanceof UploadedFile || ! $arquivo->isValid()) {
                throw new RuntimeException(
                    "O arquivo enviado no campo {$input} é inválido."
                );
            }

            $path = ArquivoAnexoHelper::armazenarUploadRastreado(
                $arquivo,
                $diretorio,
                [
                    'empresa_id' => $reserva->empresa_id,
                    'user_id' => Auth::id(),
                    'modulo' => 'checkout',
                    'entidade' => 'contrato_checkout',
                    'entidade_id' => $contratoCheckout->id,
                    'campo' => $coluna,
                    'contexto' => 'checkout',
                ],
                $contratoCheckout->{$coluna}
            );

            if (blank($path)) {
                throw new RuntimeException(
                    "Não foi possível salvar o arquivo do campo {$input}."
                );
            }

            $dadosFotos[$coluna] = $path;
            $arquivosSalvos[] = $path;
        }

        return $dadosFotos;
    }

    private function salvarAssinatura(
        string $assinaturaBase64,
        string $diretorio,
        Reserva $reserva,
        ContratoCheckout $contratoCheckout
    ): string {
        if (! preg_match(
            '/^data:image\/(png|jpe?g|webp);base64,/i',
            $assinaturaBase64,
            $matches
        )) {
            throw new RuntimeException(
                'Formato da assinatura inválido.'
            );
        }

        $conteudoBase64 = substr(
            $assinaturaBase64,
            strpos($assinaturaBase64, ',') + 1
        );

        $conteudo = base64_decode($conteudoBase64, true);

        if ($conteudo === false || $conteudo === '') {
            throw new RuntimeException(
                'Não foi possível processar a assinatura.'
            );
        }

        $extensao = strtolower($matches[1]);

        if ($extensao === 'jpeg') {
            $extensao = 'jpg';
        }

        $nomeArquivo = sprintf(
            'assinatura-checkout-%s-%s.%s',
            $contratoCheckout->id,
            Str::uuid(),
            $extensao
        );

        $path = trim($diretorio, '/\\').'/'.$nomeArquivo;

        $salvo = Storage::disk('public')->put(
            $path,
            $conteudo
        );

        if (! $salvo) {
            throw new RuntimeException(
                'Não foi possível salvar a assinatura do check-out.'
            );
        }

        if (! Storage::disk('public')->exists($path)) {
            throw new RuntimeException(
                'A assinatura não foi encontrada após o salvamento.'
            );
        }

        return $path;
    }

    private function atualizarDocumentosCheckout(
        Reserva $reserva,
        ContratoCheckout $contratoCheckout,
        array $dados,
        array $dadosFotosCheckout
    ): void {
        $contratoCheckout->forceFill(array_merge(
            $dadosFotosCheckout,
            [
                'notas_devolucao' => $dados['notas_devolucao'] ?? null,
                'observacoes_devolucao' => $dados['observacoes_devolucao'] ?? null,
            ]
        ))->save();
    }

    private function obterDiretorioCheckout(
        Reserva $reserva
    ): string {
        return sprintf(
            'checkout/empresa_%s/reserva_%s',
            $reserva->empresa_id ?: 'sem_empresa',
            $reserva->id
        );
    }

    private function removerArquivosSalvos(
        array $arquivosSalvos
    ): void {
        foreach (array_unique(array_filter($arquivosSalvos)) as $path) {
            try {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }

                ArquivoRastreado::query()
                    ->where('path', $path)
                    ->delete();
            } catch (Throwable $e) {
                Log::warning(
                    'Não foi possível remover arquivo após rollback do checkout.',
                    [
                        'path' => $path,
                        'erro' => $e->getMessage(),
                    ]
                );
            }
        }
    }

    private function liberarCalcao(
        Reserva $reserva
    ): void {
        $calcao = $reserva->calcao;

        if (! $calcao) {
            return;
        }

        if (! in_array((string) $calcao->status, [
            Calcao::STATUS_AUTORIZADO,
            Calcao::STATUS_CAPTURADO,
            Calcao::STATUS_PARCIALMENTE_CAPTURADO,
        ], true)) {
            return;
        }

        $valorRecebido = (float) (
            $calcao->valor_capturado
            ?: $calcao->valor_autorizado
            ?: $calcao->valor_calculado
            ?: 0
        );

        if ($valorRecebido <= 0) {
            return;
        }

        $valorRetido = $this->calcularValorRetidoDoCalcao($reserva, $calcao, $valorRecebido);
        $valorRetido = min($valorRetido, $valorRecebido);
        $valorDevolvido = round(max(0, $valorRecebido - $valorRetido), 2);
        $ehPreAutorizacao = (string) $calcao->status === Calcao::STATUS_AUTORIZADO
            && blank($calcao->valor_capturado);
        $statusCaucao = $valorRetido <= 0.01
            ? 'devolvida'
            : ($valorDevolvido <= 0.01 ? 'retida_integralmente' : 'parcialmente_retida');

        $transacaoOrigem = $this->obterTransacaoOriginalCalcao($reserva, $calcao);

        if ($valorDevolvido > 0 && ! $ehPreAutorizacao) {
            $categoria = $this->obterCategoriaDevolucaoCalcao($reserva);

            $this->createLancamentoAction->handle([
                'empresa_id' => $reserva->empresa_id,
                'descricao' => 'Devolução de Caução '.$this->codigoReserva($reserva),
                'valor' => $valorDevolvido,
                'tipo' => 'despesa',
                'categoria_financeira_id' => $categoria?->id,
                'data_lancamento' => now()->toDateTimeString(),
                'data_vencimento' => now()->toDateString(),
                'data_pagamento' => now()->toDateString(),
                'status' => 'pago',
                'metodo_pagamento' => $transacaoOrigem?->metodo_pagamento ?? 'pix',
                'veiculo_id' => $reserva->veiculo_id,
                'reserva_id' => $reserva->id,
                'user_id' => Auth::id(),
                'observacoes' => sprintf(
                    'Devolução de caução gerada no checkout da reserva %s. Status da caução: %s. Valor recebido: %.2f. Valor devolvido: %.2f. Valor retido: %.2f.',
                    $this->codigoReserva($reserva),
                    $statusCaucao,
                    $valorRecebido,
                    $valorDevolvido,
                    $valorRetido
                ),
            ]);

            \App\Models\Transacao::create([
                'user_id' => $reserva->user_id,
                'pago_por_user_id' => Auth::id(),
                'reserva_id' => $reserva->id,
                'valor' => -1 * $valorDevolvido,
                'moeda' => 'BRL',
                'metodo_pagamento' => 'caucao',
                'status' => 'reembolsado',
                'info_pagamento' => [
                    'tipo' => 'calcao_devolucao',
                    'acao' => 'devolucao_checkout',
                    'calcao_id' => $calcao->id,
                    'transacao_origem_id' => $transacaoOrigem?->id,
                    'caucao_status' => $statusCaucao,
                    'valor_recebido' => $valorRecebido,
                    'valor_devolvido' => $valorDevolvido,
                    'valor_retido' => $valorRetido,
                    'motivo' => 'Finalização normal do checkout',
                    'checkout_id' => $reserva->contratoCheckout?->id,
                ],
                'observacoes' => 'Devolução de caução gerada no checkout da reserva '.$this->codigoReserva($reserva),
                'confirmado_em' => now(),
                'confirmado_por_user_id' => Auth::id(),
                'empresa_id' => $reserva->empresa_id ?? null,
            ]);
        }

        if ($transacaoOrigem) {
            $info = is_array($transacaoOrigem->info_pagamento) ? $transacaoOrigem->info_pagamento : [];
            $info['caucao_status'] = $statusCaucao;
            $info['valor_recebido'] = $valorRecebido;
            $info['valor_devolvido'] = $valorDevolvido;
            $info['valor_retido'] = $valorRetido;
            $info['devolvida_em'] = now()->toDateTimeString();
            $info['motivo'] = 'Finalização normal do checkout';
            $info['checkout_id'] = $reserva->contratoCheckout?->id;
            $info['checkout_codigo'] = $this->codigoReserva($reserva);

            $transacaoOrigem->forceFill([
                'info_pagamento' => $info,
            ])->save();
        }

        $calcao->forceFill([
            'status' => $valorRetido > 0.01
                ? Calcao::STATUS_PARCIALMENTE_CAPTURADO
                : Calcao::STATUS_CANCELADO,
            'data_hora_cancelamento' => $valorRetido <= 0.01 ? now() : $calcao->data_hora_cancelamento,
            'data_hora_captura' => $valorRetido > 0.01 ? now() : $calcao->data_hora_captura,
            'usuario_responsavel_id' => Auth::id(),
            'observacao' => sprintf(
                'Checkout finalizado. Valor recebido: %.2f. Valor devolvido: %.2f. Valor retido: %.2f. Motivo: Finalização normal do checkout.',
                $valorRecebido,
                $valorDevolvido,
                $valorRetido
            ),
            'stripe_response' => array_merge(
                is_array($calcao->stripe_response) ? $calcao->stripe_response : [],
                [
                    'checkout_liquidacao' => [
                        'status' => $statusCaucao,
                        'valor_recebido' => $valorRecebido,
                        'valor_devolvido' => $valorDevolvido,
                        'valor_retido' => $valorRetido,
                        'motivo' => 'Finalização normal do checkout',
                        'checkout_id' => $reserva->contratoCheckout?->id,
                        'checkout_codigo' => $this->codigoReserva($reserva),
                    ],
                ]
            ),
        ])->save();

        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            Auth::id(),
            [
                'acao' => 'Calção liquidado no checkout',
                'calcao_id' => $calcao->id,
                'valor_recebido' => $valorRecebido,
                'valor_devolvido' => $valorDevolvido,
                'valor_retido' => $valorRetido,
                'status_caucao' => $statusCaucao,
                'lancamento_financeiro_criado' => $valorDevolvido > 0 && ! $ehPreAutorizacao,
            ],
            'Calção encerrado no checkout',
            'success'
        );
    }

    private function obterCategoriaDevolucaoCalcao(Reserva $reserva): ?CategoriaFinanceira
    {
        if (! $reserva->empresa_id) {
            return CategoriaFinanceira::query()
                ->where('nome', 'Devolução de caução')
                ->first();
        }

        return CategoriaFinanceira::query()->firstOrCreate(
            [
                'empresa_id' => $reserva->empresa_id,
                'nome' => 'Devolução de caução',
            ],
            [
                'codigo' => CategoriaFinanceira::sugerirCodigo($reserva->empresa_id, 'despesa'),
                'tipo' => 'despesa',
                'descricao' => 'Categoria automática para devolução de caução no check-out.',
                'status' => 'ativo',
                'permite_lancamento' => true,
                'origem' => 'sistema',
            ]
        );
    }

    private function calcularValorRetidoDoCalcao(
        Reserva $reserva,
        Calcao $calcao,
        float $valorRecebido
    ): float {
        if (! \Illuminate\Support\Facades\Schema::hasTable('avaria_cobrancas')) {
            return 0.0;
        }

        $retido = (float) \App\Models\AvariaCobranca::query()
            ->where('calcao_id', $calcao->id)
            ->whereIn('status', [
                \App\Models\AvariaCobranca::STATUS_COBRADO,
                \App\Models\AvariaCobranca::STATUS_PROCESSANDO,
            ])
            ->sum(DB::raw('COALESCE(valor_calcao, 0) + COALESCE(valor_stripe, 0)'));

        return round(min($valorRecebido, max(0, $retido)), 2);
    }

    private function obterTransacaoOriginalCalcao(
        Reserva $reserva,
        Calcao $calcao
    ): ?\App\Models\Transacao {
        return \App\Models\Transacao::query()
            ->where('reserva_id', $reserva->id)
            ->where('info_pagamento->tipo', 'calcao')
            ->orderByDesc('id')
            ->first();
    }

    private function codigoReserva(Reserva $reserva): string
    {
        return (string) (
            $reserva->codigoReserva?->codigo
            ?? ('RES-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT))
        );
    }
}
