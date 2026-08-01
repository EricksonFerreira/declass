<?php

namespace App\Actions\Reserva\Checkout\Revisao\Concluir;

use App\Actions\Financeiro\CreateLancamentoAction;
use App\Models\AvariaCobranca;
use App\Models\Calcao;
use App\Models\CategoriaFinanceira;
use App\Models\Reserva;
use App\Models\Transacao;
use App\Services\MovimentacaoService;
use App\Services\ReservaService;
use App\Services\SaldoService;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConcluirRevisaoCheckoutReservaComumAction
{
    private const CATEGORIA_DEVOLUCAO_CALCAO = 'Devolução de caução';
    private const CATEGORIA_COBRANCA_AVARIA = 'Cobrança de avaria';

    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly SaldoService $saldoService,
        private readonly CreateLancamentoAction $createLancamentoAction,
        private readonly MovimentacaoService $movimentacaoService,
    ) {}

    public function handle(Reserva $reserva, array $dados): void
    {
        $this->validarConclusao($reserva);

        DB::transaction(function () use ($reserva, $dados): void {
            $resumoFinanceiro = $this->calcularResumoFinanceiro($reserva);

            $this->processarFinanceiro(
                reserva: $reserva,
                resumo: $resumoFinanceiro,
            );

            $this->concluirRevisao($reserva);
            $this->marcarAvariasComoRevisadas($reserva);
            $this->registrarEntradaVeiculo($reserva);

            $this->registrarLog(
                reserva: $reserva,
                dados: $dados,
                resumo: $resumoFinanceiro,
            );
        });
    }

    private function validarConclusao(Reserva $reserva): void
    {
        if (! $reserva->contratoCheckout) {
            throw new RuntimeException(
                'Não é possível concluir a revisão do checkout, pois o contrato de checkout não foi encontrado.'
            );
        }

        if (! $this->checkoutAssinado($reserva)) {
            throw new RuntimeException(
                'Não é possível concluir a revisão do checkout, pois o contrato não foi assinado.'
            );
        }

        if ($reserva->revisao_checkout_concluida === 'S') {
            throw new RuntimeException(
                'Essa revisão de checkout já foi concluída.'
            );
        }
    }

    /**
     * @return array{
     *     calcao: Calcao|null,
     *     valor_recebido: float,
     *     valor_avarias: float,
     *     valor_usado_calcao: float,
     *     valor_devolver_calcao: float,
     *     valor_excedente: float
     * }
     */
    private function calcularResumoFinanceiro(Reserva $reserva): array
    {
        $calcao = $reserva->calcao;

        $valorRecebido = $this->valorRecebidoCalcao($calcao);
        $valorAvarias = $this->valorTotalAvarias($reserva);

        $valorUsadoCalcao = round(
            min($valorRecebido, $valorAvarias),
            2
        );

        $valorDevolverCalcao = round(
            max(0, $valorRecebido - $valorUsadoCalcao),
            2
        );

        $valorExcedente = round(
            max(0, $valorAvarias - $valorRecebido),
            2
        );

        return [
            'calcao' => $calcao,
            'valor_recebido' => $valorRecebido,
            'valor_avarias' => $valorAvarias,
            'valor_usado_calcao' => $valorUsadoCalcao,
            'valor_devolver_calcao' => $valorDevolverCalcao,
            'valor_excedente' => $valorExcedente,
        ];
    }

    /**
     * @param array{
     *     calcao: Calcao|null,
     *     valor_recebido: float,
     *     valor_avarias: float,
     *     valor_usado_calcao: float,
     *     valor_devolver_calcao: float,
     *     valor_excedente: float
     * } $resumo
     */
    private function processarFinanceiro(
        Reserva $reserva,
        array $resumo
    ): void {
        $this->usarCalcaoNasAvarias($reserva, $resumo);

        $this->registrarDevolucaoCalcao($reserva, $resumo);

        $this->registrarCobrancaExcedente($reserva, $resumo);
    }

    private function usarCalcaoNasAvarias(
        Reserva $reserva,
        array $resumo
    ): void {
        $valorUsado = $resumo['valor_usado_calcao'];

        if ($valorUsado <= 0 || ! $reserva->usuario) {
            return;
        }

        $this->saldoService->debitar(
            $reserva->usuario,
            $valorUsado,
            [
                'empresa_id' => $reserva->empresa_id,
                'reserva_id' => $reserva->id,
                'origem' => 'revisao_checkout',
                'observacoes' => sprintf(
                    'Débito gerado na conclusão da revisão do checkout da reserva %s. '
                    .'Valor da caução: %.2f. Total de avarias: %.2f. '
                    .'Valor usado da caução: %.2f.',
                    $this->codigoReserva($reserva),
                    $resumo['valor_recebido'],
                    $resumo['valor_avarias'],
                    $valorUsado
                ),
            ]
        );
    }

    private function registrarDevolucaoCalcao(
        Reserva $reserva,
        array $resumo
    ): void {
        $valorDevolucao = $resumo['valor_devolver_calcao'];

        if ($valorDevolucao <= 0) {
            return;
        }

        $categoria = $this->categoriaDevolucaoCalcao($reserva);

        $this->createLancamentoAction->handle([
            'empresa_id' => $reserva->empresa_id,
            'categoria_financeira_id' => $categoria->id,
            'descricao' => sprintf(
                'Devolução de caução - %s',
                $this->codigoReserva($reserva)
            ),
            'valor' => $valorDevolucao,
            'tipo' => 'despesa',
            'status' => 'pago',
            'data_lancamento' => now()->toDateTimeString(),
            'data_vencimento' => now()->toDateString(),
            'data_pagamento' => now()->toDateString(),
            'reserva_id' => $reserva->id,
            'veiculo_id' => $reserva->veiculo_id,
            'user_id' => $reserva->user_id,
            'observacoes' => sprintf(
                'Devolução de caução registrada na conclusão da revisão do checkout. '
                .'Caução recebida: %.2f. Total de avarias: %.2f. '
                .'Valor devolvido: %.2f.',
                $resumo['valor_recebido'],
                $resumo['valor_avarias'],
                $valorDevolucao
            ),
        ]);

        $transacao = [
            'user_id' => $reserva->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reserva->id,
            'valor' => -1 * abs($valorDevolucao),
            'moeda' => 'BRL',
            'metodo_pagamento' => 'caucao',
            'status' => 'refunded',
            'info_pagamento' => [
                'tipo' => 'calcao',
                'acao' => 'devolucao_checkout_revisao',
                'calcao_id' => $reserva->calcao?->id,
                'valor_recebido' => $resumo['valor_recebido'],
                'valor_total_avarias' => $resumo['valor_avarias'],
                'valor_devolvido' => $valorDevolucao,
            ],
        ];

        if (SchemaCache::hasColumn('transacoes', 'confirmado_em')) {
            $transacao['confirmado_em'] = now();
        }
        if (SchemaCache::hasColumn('transacoes', 'confirmado_por_user_id')) {
            $transacao['confirmado_por_user_id'] = Auth::id();
        }
        if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
            $transacao['empresa_id'] = $reserva->empresa_id ?? null;
        }

        Transacao::create($transacao);
    }

    private function registrarCobrancaExcedente(
        Reserva $reserva,
        array $resumo
    ): void {
        $valorExcedente = $resumo['valor_excedente'];

        if ($valorExcedente <= 0) {
            return;
        }

        $categoria = $this->categoriaCobrancaAvaria($reserva);

        $this->createLancamentoAction->handle([
            'empresa_id' => $reserva->empresa_id,
            'categoria_financeira_id' => $categoria->id,
            'descricao' => sprintf(
                'Cobrança de avarias excedente da caução - %s',
                $this->codigoReserva($reserva)
            ),
            'valor' => $valorExcedente,
            'tipo' => 'receita',
            'status' => 'pendente',
            'data_lancamento' => now()->toDateTimeString(),
            'data_vencimento' => now()->toDateString(),
            'reserva_id' => $reserva->id,
            'veiculo_id' => $reserva->veiculo_id,
            'user_id' => $reserva->user_id,
            'observacoes' => sprintf(
                'Cobrança automática gerada na conclusão da revisão do checkout. '
                .'Caução recebida: %.2f. Total de avarias: %.2f. '
                .'Valor excedente: %.2f.',
                $resumo['valor_recebido'],
                $resumo['valor_avarias'],
                $valorExcedente
            ),
        ]);
    }

    private function categoriaDevolucaoCalcao(
        Reserva $reserva
    ): CategoriaFinanceira {
        return CategoriaFinanceira::firstOrCreate(
            [
                'empresa_id' => $reserva->empresa_id,
                'nome' => self::CATEGORIA_DEVOLUCAO_CALCAO,
                'tipo' => 'despesa',
            ],
            [
                'codigo' => 'C.02',
                'status' => 'ativo',
                'descricao' => 'Valores devolvidos ao cliente após o encerramento da reserva.',
                'permite_lancamento' => true,
                'origem' => 'sistema',
            ]
        );
    }

    private function categoriaCobrancaAvaria(
        Reserva $reserva
    ): CategoriaFinanceira {
        return CategoriaFinanceira::firstOrCreate(
            [
                'empresa_id' => $reserva->empresa_id,
                'nome' => self::CATEGORIA_COBRANCA_AVARIA,
                'tipo' => 'receita',
            ],
            [
                'descricao' => 'Cobrança gerada automaticamente quando o valor das avarias excede a caução.',
                'status' => 'ativo',
                'permite_lancamento' => true,
                'origem' => 'sistema',
            ]
        );
    }

    private function concluirRevisao(Reserva $reserva): void
    {
        $reserva->update([
            'revisao_checkout_concluida' => 'S',
        ]);
    }

    private function registrarEntradaVeiculo(Reserva $reserva): void
    {
        if (! $reserva->veiculo_id || ! $reserva->estacao_devolucao_id) {
            return;
        }

        $this->movimentacaoService->registrarChegadaNaEstacao(
            (int) $reserva->veiculo_id,
            (int) $reserva->estacao_devolucao_id,
            'Veículo liberado após conclusão da revisão do checkout da reserva ' . $this->codigoReserva($reserva)
        );
    }

    private function marcarAvariasComoRevisadas(
        Reserva $reserva
    ): void {
        $reservaCodigoId = $reserva->codigoReserva?->id;

        if (! $reservaCodigoId) {
            return;
        }

        AvariaCobranca::query()
            ->where('reserva_codigo_id', $reservaCodigoId)
            ->where('status', '!=', AvariaCobranca::STATUS_COBRADO)
            ->update([
                'status' => AvariaCobranca::STATUS_COBRADO,
            ]);
    }

    private function registrarLog(
        Reserva $reserva,
        array $dados,
        array $resumo
    ): void {
        $this->reservaService->adicionaLog(
            (int) $reserva->id,
            (int) Auth::id(),
            [
                'acao' => 'Revisão do checkout concluída',
                'checkout_id' => $reserva->contratoCheckout?->id,
                'sem_pendencias' => (bool) ($dados['sem_pendencias'] ?? true),
                'observacao' => $dados['observacao'] ?? null,
                'calcao_id' => $resumo['calcao']?->id,
                'valor_recebido_calcao' => $resumo['valor_recebido'],
                'valor_total_avarias' => $resumo['valor_avarias'],
                'valor_usado_calcao' => $resumo['valor_usado_calcao'],
                'valor_devolvido_calcao' => $resumo['valor_devolver_calcao'],
                'valor_excedente_cobrado' => $resumo['valor_excedente'],
            ],
            'Revisão do checkout concluída',
            'success'
        );
    }

    private function checkoutAssinado(Reserva $reserva): bool
    {
        $contrato = $reserva->contratoCheckout;

        return filled($contrato?->assinatura)
            || filled($contrato?->data_assinatura);
    }

    private function valorRecebidoCalcao(?Calcao $calcao): float
    {
        if (! $calcao) {
            return 0.0;
        }

        return round(
            (float) (
                $calcao->valor_capturado
                ?: $calcao->valor_autorizado
                ?: $calcao->valor_calculado
                ?: 0
            ),
            2
        );
    }

    private function valorTotalAvarias(Reserva $reserva): float
    {
        $reservaCodigoId = $reserva->codigoReserva?->id;

        if (! $reservaCodigoId) {
            return 0.0;
        }

        $valor = AvariaCobranca::query()
            ->where('reserva_codigo_id', $reservaCodigoId)
            ->sum(
                DB::raw(
                    'COALESCE(valor_calcao, 0) + COALESCE(valor_stripe, 0)'
                )
            );

        return round((float) $valor, 2);
    }

    private function codigoReserva(Reserva $reserva): string
    {
        return (string) (
            $reserva->codigoReserva?->codigo
            ?? sprintf('RES-%06d', $reserva->id)
        );
    }
}
