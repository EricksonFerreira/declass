<?php

namespace App\Http\Controllers;

use App\Actions\Reserva\Avaria\CriarCobranca\CriarCobrancaAvariaAction;
use App\Actions\Reserva\Avaria\Exibir\ExibirCobrancaAvariaAction;
use App\DTO\BreadCrumbItem;
use App\Http\Requests\Reserva\StoreAvariaCobrancaRequest;
use App\Services\BreadcrumbService;

class AvariaCobrancaController extends BaseController
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Cobrança de Avarias';
    }

    public function create(
        string $codigo,
        ExibirCobrancaAvariaAction $action
    ) {
        $this->authorize('avaria.cobrar');

        try {
            $dados = $action->handle($codigo);
        } catch (\DomainException $exception) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', $exception->getMessage());
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem(
                'Detalhes da reserva '.$codigo,
                route('reserva.show', $codigo)
            ),
            new BreadCrumbItem('Cobrança de avarias'),
        ]);

        return view('reserva.avarias.cobranca', [
            ...$dados,
            'breadcrumb' => $breadcrumb,
            'titulo' => 'Cobrança de avarias — '.$codigo,
        ]);
    }

    public function store(
        StoreAvariaCobrancaRequest $request,
        string $codigo,
        CriarCobrancaAvariaAction $action
    ) {
        $this->authorize('avaria.cobrar');

        try {
            $cobranca = $action->handle(
                $codigo,
                $request->validated()
            );
        } catch (\DomainException $exception) {
            return redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', $exception->getMessage());
        }

        return match ($cobranca->status) {
            'cobrado' => redirect()
                ->route('reserva.show', $codigo)
                ->with('success', 'Cobrança realizada com sucesso.'),

            'pendente' => redirect()
                ->route('reserva.show', $codigo)
                ->with('warning', 'Cobrança registrada como pendente.'),

            default => redirect()
                ->route('reserva.show', $codigo)
                ->with(
                    'error',
                    'Não foi possível efetivar a cobrança automaticamente.'
                ),
        };
    }
}
