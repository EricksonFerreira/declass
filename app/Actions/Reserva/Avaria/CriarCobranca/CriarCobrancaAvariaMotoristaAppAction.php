<?php

namespace App\Actions\Reserva\Avaria\CriarCobranca;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Services\AvariaCobrancaService;

class CriarCobrancaAvariaMotoristaAppAction
{
    public function __construct(
        private readonly AvariaCobrancaService $avariaCobrancaService,
    ) {}

    public function handle(
        ReservaResolvida $reservaResolvida,
        array $dados
    ): AvariaCobranca {
        $reserva = $reservaResolvida->reservaMotoristaApp();

        $reserva->loadMissing([
            'codigoReserva',
            'user',
            'veiculo',
        ]);

        if (! $reserva->checkout_realizado_em) {
            throw new \DomainException(
                'Finalize o check-out antes de registrar cobrança por avarias.'
            );
        }

        return $this->avariaCobrancaService->criarECobrar(
            $reservaResolvida,
            $dados['dano_veiculo_ids'] ?? [],
            (float) $dados['valor'],
            (string) $dados['descricao'],
            $dados['metodo'] ?? null,
            $dados['payment_method_id'] ?? null,
        );
    }
}
