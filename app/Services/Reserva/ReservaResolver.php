<?php

namespace App\Services\Reserva;

use App\DTO\Reserva\ReservaResolvida;
use App\Enums\TipoReservaEnum;
use App\Exceptions\ReservaNaoEncontradaException;
use App\Models\ReservaCodigo;
use App\Support\EmpresaContext;

class ReservaResolver
{
    public function __construct(
        private EmpresaContext $empresaContext,
    ) {}

    public function resolver(
        string $codigo,
        ?int $empresaId = null
    ): ReservaResolvida {
        $codigoNormalizado = strtoupper(trim($codigo));

        if ($empresaId === null) {
            $empresaId = $this->empresaContext->empresaId();
        }

        $referencia = ReservaCodigo::withoutGlobalScopes()
            ->with([
                'reserva' => fn ($query) => $query->withoutGlobalScopes(),
                'reservaMotoristaApp' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->when($empresaId !== null, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('codigo', $codigoNormalizado)
            ->first();

        if (! $referencia) {
            throw new ReservaNaoEncontradaException(
                "A reserva '{$codigoNormalizado}' não foi encontrada."
            );
        }

        $tipoOrigem = TipoReservaEnum::tryFrom($referencia->tipo_origem);

        return match ($tipoOrigem) {
            TipoReservaEnum::COMUM => $this->resolverComum($referencia),

            TipoReservaEnum::MOTORISTA_APP => $this->resolverMotoristaApp(
                $referencia
            ),

            default => throw new \LogicException(
                "Tipo de origem '{$referencia->tipo_origem}' não suportado."
            ),
        };
    }

    private function resolverComum(
        ReservaCodigo $referencia
    ): ReservaResolvida {
        if (! $referencia->reserva) {
            throw new ReservaNaoEncontradaException(
                'Reserva comum vinculada ao código não encontrada.'
            );
        }

        return new ReservaResolvida(
            codigo: $referencia->codigo,
            tipo: TipoReservaEnum::COMUM,
            registro: $referencia->reserva,
        );
    }

    private function resolverMotoristaApp(
        ReservaCodigo $referencia
    ): ReservaResolvida {
        if (! $referencia->reservaMotoristaApp) {
            throw new ReservaNaoEncontradaException(
                'Reserva de motorista de aplicativo vinculada ao código não encontrada.'
            );
        }

        return new ReservaResolvida(
            codigo: $referencia->codigo,
            tipo: TipoReservaEnum::MOTORISTA_APP,
            registro: $referencia->reservaMotoristaApp,
        );
    }

    public function codigoPublico(
        int $reservaId,
        int $empresaId
    ): ?string {
        return ReservaCodigo::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('reserva_id', $reservaId)
            ->value('codigo');
    }
}
