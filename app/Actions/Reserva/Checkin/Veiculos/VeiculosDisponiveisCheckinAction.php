<?php

namespace App\Actions\Reserva\Checkin\Veiculos;

use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\UpgradeVeiculoService;

class VeiculosDisponiveisCheckinAction
{
    use ReservaHelpersTrait;

    public function __construct(
        protected ReservaService $reservaService,
        protected VeiculoService $veiculoService,
        protected ModeloService $modeloService,
        protected UpgradeVeiculoService $upgradeVeiculoService
    ) {}

    public function handle(Request $request, $codigo)
    {
        $contextoCheckin = $this->contextoCheckinPorCodigo($codigo);

        if (!$contextoCheckin) {
            return response()->json(['error' => 'Reserva nao encontrada.'], 404);
        }

        $reserva = $contextoCheckin['reserva'];
        $categoriaId = $request->query('categoria_id') ? (int) $request->query('categoria_id') : null;
        $veiculoIdAtual = (int) ($reserva->veiculo_id ?? 0);

        $veiculos = Veiculo::query()
            ->with(['marca', 'modelo', 'estacao'])
            ->when($veiculoIdAtual > 0, function ($q) use ($veiculoIdAtual) {
                $q->where(function ($sub) use ($veiculoIdAtual) {
                    $sub->where('status_veiculo_id', 1)
                        ->orWhere('id', $veiculoIdAtual);
                });
            }, function ($q) {
                $q->where('status_veiculo_id', 1);
            })
            ->when($categoriaId, function ($q) use ($categoriaId) {
                $q->whereHas('modelo', fn ($m) => $m->where('categoria_id', $categoriaId));
            })
            ->orderBy('placa_licenca')
            ->get();

        return response()->json($veiculos);
    }
}
