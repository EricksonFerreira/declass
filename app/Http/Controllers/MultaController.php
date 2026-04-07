<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Http\Requests\Multa\StoreMultaRequest;
use App\Http\Requests\Multa\UpdateMultaRequest;
use App\Models\Multa;
use App\Services\BreadcrumbService;
use App\Services\MultaService;
use App\Services\ReservaService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MultaController extends BaseController
{
    public function __construct(
        private readonly MultaService      $multaService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly ReservaService    $reservaService,
        private readonly UsuarioService    $usuarioService,
        private readonly VeiculoService    $veiculoService,
    ) {
        $this->titulo = 'Multas';
    }

    public function index(Request $request)
    {
        $this->authorize('multa.index');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'reserva_id', 'placa',
            'cliente_id', 'status_multa', 'status_desconto',
            'vencimento_ate', 'veiculo_id',
        ]);

        $perPage = $request->input('per_page', 15);

        $query = $this->multaService->queryScoped()
            ->with(['reserva', 'veiculo', 'cliente', 'responsavelLancamento'])
            ->orderBy('id', 'desc');

        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_infracao', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_infracao', '<=', $filtros['data_fim'] . ' 23:59:59');
        }
        if (!empty($filtros['reserva_id'])) {
            $query->where('reserva_id', $filtros['reserva_id']);
        }
        if (!empty($filtros['placa'])) {
            $query->where('placa_momento', 'like', '%' . $filtros['placa'] . '%');
        }
        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['status_multa'])) {
            $query->where('status_multa', $filtros['status_multa']);
        }
        if (!empty($filtros['status_desconto'])) {
            $query->where('status_desconto', $filtros['status_desconto']);
        }
        if (!empty($filtros['vencimento_ate'])) {
            $query->where('data_vencimento', '<=', $filtros['vencimento_ate']);
        }
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }

        $multas   = $query->paginate($perPage)->withQueryString();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Multas');

        return view('multa.index', compact('multas', 'breadcrumb', 'filtros'))
            ->with('titulo', $this->titulo);
    }

    public function create(Request $request)
    {
        $this->authorize('multa.create');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Multas', 'multa.index', 'Nova Multa');
        $reservaId  = $request->query('reserva_id');
        $reserva    = $reservaId ? $this->reservaService->getById($reservaId, ['usuario', 'grupoFrota']) : null;

        $veiculoId = $request->query('veiculo_id');
        $veiculo   = $veiculoId ? $this->veiculoService->getById($veiculoId, ['marca', 'modelo']) : null;

        return view('multa.add-edit', [
            'multa'            => null,
            'reserva'          => $reserva,
            'veiculo'          => $veiculo,
            'statusMulta'      => Multa::STATUS_MULTA,
            'statusDesconto'   => Multa::STATUS_DESCONTO,
            'breadcrumb'       => $breadcrumb,
            'titulo'           => 'Nova Multa',
            'metodo'           => 'POST',
            'rota'             => route('multa.store'),
        ]);
    }

    public function store(StoreMultaRequest $request)
    {
        $this->authorize('multa.create');

        $dados = $request->except(['anexo_comprovante', 'anexo_pdf']);

        $multa = $this->multaService->criar($dados);

        if ($request->hasFile('anexo_comprovante')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_comprovante'), $multa->id);
            $multa->update(['anexo_comprovante_path' => $path]);
        }

        if ($request->hasFile('anexo_pdf')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_pdf'), $multa->id);
            $multa->update(['anexo_pdf_path' => $path]);
        }

        return redirect()->route('multa.show', $multa->id)
            ->with('success', 'Multa registrada com sucesso.');
    }

    public function show(int $id)
    {
        $this->authorize('multa.index');

        $multa = $this->multaService->queryScoped()->with([
            'reserva.usuario', 'veiculo', 'cliente', 'condutor',
            'responsavelLancamento', 'historico.usuario',
        ])->findOrFail($id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Multas', 'multa.index', 'Detalhes');

        return view('multa.show', compact('multa', 'breadcrumb'))
            ->with('titulo', 'Multa #' . $multa->id);
    }

    public function edit(int $id)
    {
        $this->authorize('multa.edit');

        $multa      = $this->multaService->queryScoped()->with(['reserva', 'veiculo.marca', 'veiculo.modelo'])->findOrFail($id);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Multas', 'multa.index', 'Editar Multa');

        return view('multa.add-edit', [
            'multa'          => $multa,
            'reserva'        => $multa->reserva,
            'veiculo'        => $multa->veiculo,
            'statusMulta'    => Multa::STATUS_MULTA,
            'statusDesconto' => Multa::STATUS_DESCONTO,
            'breadcrumb'     => $breadcrumb,
            'titulo'         => 'Editar Multa #' . $multa->id,
            'metodo'         => 'PUT',
            'rota'           => route('multa.update', $multa->id),
        ]);
    }

    public function update(UpdateMultaRequest $request, int $id)
    {
        $this->authorize('multa.edit');

        $dados = $request->except(['anexo_comprovante', 'anexo_pdf']);

        $multa = $this->multaService->atualizar($id, $dados);
        if (!$multa) {
            return redirect()->route('multa.index')
                ->with('error', 'Multa nao encontrada.');
        }

        if ($request->hasFile('anexo_comprovante')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_comprovante'), $multa->id);
            $multa->update(['anexo_comprovante_path' => $path]);
        }

        if ($request->hasFile('anexo_pdf')) {
            $path = $this->multaService->salvarAnexo($request->file('anexo_pdf'), $multa->id);
            $multa->update(['anexo_pdf_path' => $path]);
        }

        return redirect()->route('multa.show', $multa->id)
            ->with('success', 'Multa atualizada com sucesso.');
    }

    public function destroy(int $id)
    {
        $this->authorize('multa.delete');

        $multa = $this->multaService->queryScoped()->findOrFail($id);
        $multa->delete();

        return redirect()->route('multa.index')
            ->with('success', 'Multa removida com sucesso.');
    }

    public function export(Request $request)
    {
        $this->authorize('multa.export');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'reserva_id', 'placa',
            'cliente_id', 'status_multa', 'status_desconto',
            'vencimento_ate', 'veiculo_id',
        ]);

        return $this->multaService->exportarCsv($filtros);
    }
}
