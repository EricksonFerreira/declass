<?php

namespace App\Http\Controllers;

use App\Http\Requests\Justificativa\StoreJustificativaRequest;
use App\Models\Escola;
use App\Models\Justificativa;
use App\Services\BreadcrumbService;
use App\Services\JustificativaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Atestados e abonos de falta: registro pela secretaria com
 * efeito automatico nas ausencias do periodo.
 */
class JustificativaController extends BaseController
{
    public function __construct(
        protected JustificativaService $justificativaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Atestados e Abonos';
        $this->nomeFiltro = 'justificativa';

        $this->middleware('can:justificativa.index')->only('index');
        $this->middleware('can:justificativa.create')->only(['create', 'store']);
        $this->middleware('can:justificativa.destroy')->only('destroy');
        $this->middleware('can:justificativa.arquivo')->only('arquivo');
    }

    public function index(Request $request)
    {
        $justificativas = $this->justificativaService->paginateComFiltros(
            $request->only(['turma_id', 'escola_id', 'efeito', 'aluno']),
            (int) ($request->input('perPage') ?: 15)
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Atestados e Abonos', 'Vida Acadêmica');

        return view('justificativa.index', [
            'justificativas' => $justificativas,
            'turmas' => $this->justificativaService->turmasOpcoes(),
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'efeitos' => Justificativa::EFEITOS,
            'filtros' => $request->only(['turma_id', 'escola_id', 'efeito', 'aluno']),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function create(Request $request)
    {
        $turmas = $this->justificativaService->turmasOpcoes();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate(
            'Atestados e Abonos', 'justificativa.index', 'Nova justificativa', 'Vida Acadêmica'
        );

        return view('justificativa.add-edit', [
            'turmas' => $turmas,
            'matriculasPorTurma' => $this->justificativaService->matriculasPorTurma($turmas),
            'disciplinasPorTurma' => $this->justificativaService->disciplinasPorTurma($turmas),
            'tipos' => Justificativa::TIPOS,
            'efeitos' => Justificativa::EFEITOS,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function store(StoreJustificativaRequest $request)
    {
        $this->justificativaService->create($request->validated());

        return redirect()->route('justificativa.index')
            ->with('success', 'Justificativa registrada e faltas do período atualizadas.');
    }

    public function arquivo($id)
    {
        $justificativa = $this->justificativaService->getById($id);
        abort_if(! $justificativa, 404, 'Registro não encontrado.');
        abort_unless($justificativa->arquivo_path && Storage::disk('public')->exists($justificativa->arquivo_path), 404);

        return Storage::disk('public')->download(
            $justificativa->arquivo_path,
            'justificativa-'.$justificativa->id.'.'.pathinfo($justificativa->arquivo_path, PATHINFO_EXTENSION)
        );
    }

    public function destroy($id)
    {
        $success = $this->justificativaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este registro.');

        return redirect()->route('justificativa.index')
            ->with('success', 'Justificativa excluída e faltas revertidas.');
    }
}
