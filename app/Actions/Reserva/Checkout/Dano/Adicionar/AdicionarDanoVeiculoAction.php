<?php

namespace App\Actions\Reserva\Checkout\Dano\Adicionar;

use App\Models\ReservaCodigo;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class AdicionarDanoVeiculoAction
{
    public function __construct(
        private AdicionarDanoReservaComumAction $comum,
        private AdicionarDanoMotoristaAppAction $motoristaApp,
        private ReservaResolver $resolver,
    ) {}

    public function handle(array $dados, $codigo, Request $request): mixed
    {
        $reservaResolvida = $this->resolver->resolver((string) $codigo);
        
        $reservaId = $reservaResolvida->registro->id;
        $reservaCodigoId = ReservaCodigo::where('reserva_id', $reservaId)->value('id');
        
        $dados['reserva_id'] = $reservaId;
        $dados['reserva_codigo_id'] = $reservaCodigoId;

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($dados, $reservaCodigoId, $request),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($dados, $reservaCodigoId, $request),
            default => throw new \LogicException('Tipo de reserva não suportado para adicionar dano.'),
        };
    }
}
