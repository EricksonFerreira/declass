<?php

namespace App\Actions\Financeiro;

use App\Models\CategoriaFinanceira;
use App\Models\LancamentoFinanceiro;
use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class RegistrarReceitaPagamentoReservaAction
{
    public function handle(
        Reserva|ReservaMotoristaApp $reserva,
        float $valorPago,
        ?string $metodoPagamento = null,
        ?CarbonInterface $pagoEm = null,
        ?string $referenciaPagamento = null,
        ?int $userId = null,
        ?string $origemIdempotencia = null,
    ): ?LancamentoFinanceiro {
        $valorPago = round($valorPago, 2);
        if ($valorPago <= 0) {
            return null;
        }

        $empresaId = (int) ($reserva->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return null;
        }

        $pagoEm ??= now();
        $tipoOrigem = $reserva instanceof ReservaMotoristaApp ? 'motorista_app' : 'reserva';
        $referencia = trim((string) ($referenciaPagamento ?: $origemIdempotencia ?: 'sem-referencia'));
        $chave = implode('|', [
            'pagamento_reserva',
            $tipoOrigem,
            (string) $reserva->getKey(),
            $referencia,
            number_format($valorPago, 2, '.', ''),
            $pagoEm->toDateString(),
        ]);

        $existente = LancamentoFinanceiro::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'receita')
            ->where('observacoes', 'like', '%'.$chave.'%')
            ->first();

        if ($existente) {
            return $existente;
        }

        $categoria = CategoriaFinanceira::firstOrCreate(
            [
                'empresa_id' => $empresaId,
                'nome' => 'Pagamento de Reserva',
                'tipo' => 'receita',
            ],
            [
                'descricao' => 'Receitas geradas automaticamente ao confirmar pagamentos de reservas.',
            ]
        );

        $codigo = $this->codigoReserva($reserva);
        $reservaId = $reserva instanceof Reserva
            ? (int) $reserva->id
            : $this->reservaOperacionalId($reserva);

        return LancamentoFinanceiro::create([
            'empresa_id' => $empresaId,
            'categoria_financeira_id' => $categoria->id,
            'tipo' => 'receita',
            'descricao' => 'Pagamento da reserva #'.$codigo,
            'valor' => $valorPago,
            'status' => 'pago',
            'data_vencimento' => $pagoEm->toDateString(),
            'data_pagamento' => $pagoEm->toDateString(),
            'metodo_pagamento' => $metodoPagamento ?: 'nao_informado',
            'reserva_id' => $reservaId > 0 ? $reservaId : null,
            'user_id' => $userId,
            'observacoes' => 'Lancamento gerado automaticamente ao confirmar pagamento da reserva. Chave: '.$chave,
        ]);
    }

    private function codigoReserva(Model $reserva): string
    {
        if (method_exists($reserva, 'codigoReserva')) {
            $reserva->loadMissing('codigoReserva');
        }

        $codigo = (string) ($reserva->codigoReserva?->codigo ?? '');
        if ($codigo !== '') {
            return $codigo;
        }

        $prefixo = $reserva instanceof ReservaMotoristaApp ? 'MAP-' : 'RES-';

        return $prefixo.str_pad((string) $reserva->getKey(), 6, '0', STR_PAD_LEFT);
    }

    private function reservaOperacionalId(ReservaMotoristaApp $reserva): int
    {
        $documentos = is_array($reserva->documentos_json ?? null) ? $reserva->documentos_json : [];

        return (int) data_get($documentos, 'checkin.reserva_operacional_id', 0);
    }
}
