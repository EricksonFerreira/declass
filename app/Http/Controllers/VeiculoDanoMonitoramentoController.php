<?php

namespace App\Http\Controllers;

use App\Http\Requests\VeiculoDano\RegistrarCorrecaoDanoRequest;
use App\Models\OrdemServicoItem;
use App\Models\VeiculoDanos;
use App\Services\BreadcrumbService;
use App\DTO\BreadCrumbItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VeiculoDanoMonitoramentoController extends Controller
{
    public function __construct(
        protected BreadcrumbService $breadcrumbService
    ) {
    }

    public function index(Request $request)
    {
        $filtro = $request->query('filtro', 'pendentes');

        $query = VeiculoDanos::query()
            ->with([
                'veiculo',
                'reservaCodigo',
                'reserva.codigoReserva',
                'contratoCheckin.reserva.codigoReserva',
            ])
            ->orderByDesc('id');

        if ($filtro === 'pendentes') {
            $query->semCorrecao();
        } elseif ($filtro === 'corrigidos') {
            $query->comCorrecao();
        }

        $danos = $query->paginate(20)->withQueryString();
        $osItensPorDano = OrdemServicoItem::query()
            ->with(['ordemServico:id,numero,status'])
            ->whereIn('veiculo_dano_id', $danos->getCollection()->pluck('id')->all())
            ->orderByDesc('id')
            ->get()
            ->keyBy('veiculo_dano_id');

        $danos->setCollection(
            $danos->getCollection()->map(function (VeiculoDanos $dano) use ($osItensPorDano) {
                $itemOs = $osItensPorDano->get($dano->id);
                $dano->os_associada = $itemOs?->ordemServico;

                return $dano;
            })
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Monitoramento de avarias e correções', route('veiculo-dano.monitoramento')),
        ]);

        return view('veiculo-dano.monitoramento', compact('danos', 'breadcrumb', 'filtro'))
            ->with('titulo', 'Monitoramento de avarias e correções');
    }

    public function registrarCorrecao(RegistrarCorrecaoDanoRequest $request, int $id)
    {
        $veiculoDano = VeiculoDanos::findOrFail($id);

        if ($veiculoDano->correcao_data) {
            return response()->json(['success' => false, 'message' => 'Esta avaria já possui correção registrada.'], 422);
        }

        $veiculoDano->update([
            'status' => 'corrigida',
            'correcao_descricao' => $request->validated()['correcao_descricao'],
            'correcao_data' => now(),
            'correcao_user_id' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Correção registrada com sucesso.']);
    }
}
