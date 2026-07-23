<?php

namespace App\Actions\Reserva\Checkin\Upgrade;

use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\UpgradeVeiculoService;

class CheckinStatusUpgradeAction
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService
    ) {}

    public function handle(Request $request, $id)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($id);
        $reserva = $contextoCheckin['reserva'] ?? $this->reservaService->getById((int) $id, ['grupoFrota', 'modelos']);

        if (!$reserva) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        $temDisponibilidade = $this->upgradeVeiculoService->categoriaOriginalTemDisponibilidade($reserva);

        $payload = [
            'tem_disponibilidade_categoria_original' => $temDisponibilidade,
            'grupo_original' => [
                'id'   => $reserva->grupoFrota?->id,
                'nome' => $reserva->grupoFrota?->nome,
            ],
        ];

        if (!$temDisponibilidade) {
            $grupos = $this->upgradeVeiculoService->veiculosDisponiveisParaUpgrade($reserva);
            $payload['grupos_upgrade'] = collect($grupos)->map(fn($item) => [
                'grupo' => [
                    'id'    => $item['grupo']->id,
                    'nome'  => $item['grupo']->nome,
                    'ordem' => $item['grupo']->ordem,
                ],
                'veiculos' => $item['veiculos']->map(fn($v) => [
                    'id'   => $v->id,
                    'nome' => $v->nome,
                    'placa' => $v->placa_licenca,
                ])->values(),
            ])->values();

            $todos = $this->upgradeVeiculoService->veiculosDisponiveisNoPeriodo($reserva);
            $payload['veiculos_periodo'] = $todos->map(function ($v) use ($reserva) {
                $grupo = $v->grupoFrota ?? null;
                return [
                    'id'   => $v->id,
                    'nome' => $v->nome,
                    'placa' => $v->placa_licenca,
                    'grupo' => [
                        'id'    => $grupo?->id,
                        'nome'  => $grupo?->nome,
                        'ordem' => $grupo?->ordem,
                    ],
                    'is_upgrade' => (int) ($grupo?->ordem ?? 0) > (int) ($reserva->grupoFrota?->ordem ?? 0),
                    'is_downgrade' => (int) ($grupo?->ordem ?? 0) < (int) ($reserva->grupoFrota?->ordem ?? 0),
                ];
            })->values();
        }

        return response()->json($payload);
    }
}
