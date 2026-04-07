<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Http\Requests\Reserva\StoreAvariaCobrancaRequest;
use App\Models\AvariaCobranca;
use App\Models\Reserva;
use App\Models\VeiculoDanos;
use App\Services\AvariaCobrancaService;
use App\Services\BreadcrumbService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AvariaCobrancaController extends BaseController
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly AvariaCobrancaService $avariaCobrancaService,
    ) {
        $this->titulo = 'Cobrança de Avarias';
    }

    public function create(Request $request, int $reservaId)
    {
        $this->authorize('avaria.cobrar');

        $reserva = $this->reservaService->getById($reservaId, ['usuario', 'veiculo', 'calcao', 'contratoCheckout']);
        if (!$reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva não encontrada.');
        }

        if (!$reserva->contratoCheckout) {
            return redirect()
                ->route('reserva.show', $reserva->id)
                ->with('warning', 'Finalize o check-out antes de registrar cobrança por avarias.');
        }

        $danos = VeiculoDanos::query()
            ->where('reserva_id', $reserva->id)
            ->orderByDesc('id')
            ->get();

        $valorSugerido = (float) $danos->sum(fn ($d) => (float) ($d->custo_estimado ?? 0));

        $cobrancas = [];
        if (Schema::hasTable('avaria_cobrancas')) {
            $cobrancas = AvariaCobranca::query()
                ->where('reserva_id', $reserva->id)
                ->orderByDesc('id')
                ->take(10)
                ->get();
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Detalhes da Reserva Nº ' . $reserva->id, route('reserva.show', $reserva->id)),
            new BreadCrumbItem('Cobrança de avarias'),
        ]);

        return view('reserva.avarias.cobranca', compact('reserva', 'danos', 'valorSugerido', 'cobrancas', 'breadcrumb'))
            ->with('titulo', 'Cobrança de avarias — reserva Nº ' . $reserva->id);
    }

    public function store(StoreAvariaCobrancaRequest $request, int $reservaId)
    {
        $this->authorize('avaria.cobrar');

        $reserva = $this->reservaService->getById($reservaId, ['usuario', 'veiculo', 'calcao', 'contratoCheckout']);
        if (!$reserva) {
            return redirect()->route('reserva.index')->with('error', 'Reserva não encontrada.');
        }

        if (!$reserva->contratoCheckout) {
            return redirect()
                ->route('reserva.show', $reserva->id)
                ->with('warning', 'Finalize o check-out antes de registrar cobrança por avarias.');
        }

        $payload = $request->validated();

        $cobranca = $this->avariaCobrancaService->criarECobrar(
            $reserva,
            $payload['dano_veiculo_ids'] ?? [],
            (float) $payload['valor'],
            (string) $payload['descricao'],
            $payload['metodo'] ?? null,
            $payload['payment_method_id'] ?? null,
        );

        $this->reservaService->adicionaLog(
            $reserva->id,
            Auth::id(),
            json_encode([
                'acao' => 'Cobrança de avaria registrada',
                'cobranca_avaria_id' => $cobranca->id,
                'status' => $cobranca->status,
                'metodo' => $cobranca->metodo,
                'valor' => $cobranca->valor,
                'moeda' => $cobranca->moeda,
                'gateway_payment_intent_id' => $cobranca->gateway_payment_intent_id,
            ])
        );

        if ($cobranca->status === 'cobrado') {
            return redirect()
                ->route('reserva.show', $reserva->id)
                ->with('success', 'Cobrança realizada com sucesso.');
        }

        if ($cobranca->status === 'pendente') {
            return redirect()
                ->route('reserva.show', $reserva->id)
                ->with('warning', 'Cobrança registrada como pendente (manual).');
        }

        return redirect()
            ->route('reserva.show', $reserva->id)
            ->with('error', 'Não foi possível efetivar a cobrança automaticamente. Verifique o histórico.');
    }
}
