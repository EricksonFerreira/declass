<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\BreadcrumbService;
use App\Services\ReplicacaoBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class ReplicacaoBaseController extends Controller
{
    private ReplicacaoBaseService $replicacaoBaseService;
    private BreadcrumbService $breadcrumbService;

    public function __construct(ReplicacaoBaseService $replicacaoBaseService, BreadcrumbService $breadcrumbService)
    {
        $this->middleware('can:gerenciar-empresa');
        $this->replicacaoBaseService = $replicacaoBaseService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('empresas')) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $empresas = Empresa::query()->orderBy('nome')->get(['id', 'nome']);
        $modulos = $this->replicacaoBaseService->modulosDisponiveis();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Replicacao de Base');
        $titulo = 'Replicacao de Informacoes';

        $resultado = session('replicacao_resultado', []);
        $oldDestinoIds = collect((array) old('empresas_destino', []))->map(fn ($id) => (int) $id)->all();
        $oldModulos = (array) old('modulos', []);
        $oldEmpresaOrigem = (int) old('empresa_origem_id', 0);

        if (in_array('catalogo_fipe', $oldModulos, true)) {
            $oldEmpresaOrigem = 1;
        }

        return view('replicacao_base.index', compact(
            'empresas',
            'modulos',
            'breadcrumb',
            'titulo',
            'resultado',
            'oldDestinoIds',
            'oldModulos',
            'oldEmpresaOrigem'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_origem_id' => ['required', 'integer', 'exists:empresas,id'],
            'empresas_destino' => ['required', 'array', 'min:1'],
            'empresas_destino.*' => ['integer', 'exists:empresas,id'],
            'modulos' => ['required', 'array', 'min:1'],
            'modulos.*' => ['string'],
        ], [
            'empresa_origem_id.required' => 'Selecione a empresa base.',
            'empresa_origem_id.exists' => 'A empresa base selecionada e invalida.',
            'empresas_destino.required' => 'Selecione ao menos uma empresa de destino.',
            'empresas_destino.min' => 'Selecione ao menos uma empresa de destino.',
            'empresas_destino.*.exists' => 'Uma das empresas de destino e invalida.',
            'modulos.required' => 'Selecione ao menos um modulo para replicar.',
            'modulos.min' => 'Selecione ao menos um modulo para replicar.',
        ]);

        $empresaOrigemId = (int) $data['empresa_origem_id'];
        $empresasDestinoIds = array_map('intval', $data['empresas_destino']);
        $modulosSelecionados = array_values($data['modulos']);

        if (in_array('catalogo_fipe', $modulosSelecionados, true)) {
            $empresaOrigemId = 1;
        }

        if (in_array($empresaOrigemId, $empresasDestinoIds, true)) {
            return back()
                ->withInput()
                ->withErrors(['empresas_destino' => 'A empresa base nao pode estar na lista de destino.']);
        }

        try {
            $resultado = $this->replicacaoBaseService->replicar(
                $empresaOrigemId,
                $empresasDestinoIds,
                $modulosSelecionados
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['replicacao' => $e->getMessage()]);
        }

        return redirect()->route('replicacao_base.index')
            ->with('success', 'Replicacao executada com sucesso.')
            ->with('replicacao_resultado', $resultado);
    }
}
