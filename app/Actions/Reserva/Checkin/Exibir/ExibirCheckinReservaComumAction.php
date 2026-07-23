<?php

namespace App\Actions\Reserva\Checkin\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\ContratoCheckinService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\VeiculoService;
use App\Models\Modelo;
use Illuminate\Http\Request;

class ExibirCheckinReservaComumAction
{
    public function __construct(
        private BreadcrumbService $breadcrumbService,
        private VeiculoService $veiculoService,
        private ModeloService $modeloService,
        private CategoriaDanoService $categoriaDanoService,
        private MarcaService $marcaService,
        private ContratoCheckinService $contratoCheckinService,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;
        $registro->loadMissing(['transacoes']);
        $reservaId = (int) $registro->id;
        $codigo = $reserva->codigo;

        $contexto = $this->montarContextoCheckin($reserva);
        if (! $contexto) {
            return redirect()->route('reserva.index')->with('error', 'Reserva nao encontrada.');
        }

        if (! $registro->isPagamentoEfetuado()) {
            return redirect()->route('reserva.show', $codigo)
                ->with('warning', 'O pagamento deve ser efetuado antes de realizar o check-in.');
        }

        $categoriaDanos = $this->categoriaDanoService->getAllForSelect();

        $modeloIds = $contexto['modelo_ids'] ?? [];
        $modelos = !empty($modeloIds)
            ? Modelo::whereIn('id', $modeloIds)->where('ativo', 'S')->with('marcas')->orderBy('nome')->get()
            : collect();

        $marcaIds = $modelos->pluck('marca_id')->unique()->filter()->values()->all();
        $marcas = !empty($marcaIds)
            ? $this->marcaService->getModel()->newQuery()
                ->whereIn('id', $marcaIds)->where('ativo', 'S')->orderBy('nome')->get()
            : collect();

        $checkinModelosJson = $modelos->map(fn ($m) => [
            'id' => $m->id, 'nome' => $m->nome, 'marca_id' => (int) $m->marca_id,
        ])->values();

        $contratoExistente = $this->contratoCheckinService->getModel()->newQuery()
            ->with('veiculo')->where('reserva_id', $reservaId)->first();

        if ($contratoExistente && $contratoExistente->assinado === 'S') {
            return redirect()->route('reserva.show', $codigo)
                ->with('warning', 'O contrato ja foi assinado e nao pode ser editado.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº'.$codigo, route('reserva.show', $codigo)),
            new BreadCrumbItem($contratoExistente ? 'Editar Check-in' : 'Check-in'),
        ]);

        $titulo = $contratoExistente ? 'Editar Check-in' : 'Check-in';

        return view('reserva.checkin', compact(
            'titulo', 'registro', 'marcas', 'modelos', 'categoriaDanos',
            'checkinModelosJson', 'contratoExistente', 'breadcrumb',
        ) + [
            'reserva' => $registro,
            'codigoBreadcrumb' => $codigo,
            'veiculos' => collect(),
            'isMotoristaAppCheckin' => false,
        ]);
    }

    private function montarContextoCheckin(ReservaResolvida $reserva): ?array
    {
        $registro = $reserva->registro;
        if (! $registro->grupo_frota_id) {
            return null;
        }

        $modelos = \App\Models\Veiculo::where('grupo_frota_id', $registro->grupo_frota_id)
            ->whereNotNull('modelo_id')
            ->distinct()
            ->pluck('modelo_id')
            ->all();

        return [
            'modelo_ids' => $modelos,
        ];
    }
}
