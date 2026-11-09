<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Ocorrencia\StoreOcorrenciaRequest;
use App\Http\Requests\Ocorrencia\UpdateOcorrenciaRequest;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Ocorrencia;
use App\Services\BreadcrumbService;
use App\Services\OcorrenciaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class OcorrenciaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected OcorrenciaService $ocorrenciaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Ocorrências';
        $this->nomeFiltro = 'ocorrencia';

        $this->middleware('can:ocorrencia.index')->only('index');
        $this->middleware('can:ocorrencia.create')->only('create');
        $this->middleware('can:ocorrencia.store')->only('store');
        $this->middleware('can:ocorrencia.edit')->only('edit');
        $this->middleware('can:ocorrencia.update')->only('update');
        $this->middleware('can:ocorrencia.destroy')->only('destroy');
        $this->middleware('can:ocorrencia.filtro')->only('filtro');
        $this->middleware('can:ocorrencia.removerFiltro')->only('removerFiltro');
        $this->middleware('can:ocorrencia.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $ocorrencias = $this->ocorrenciaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['aluno.pessoa', 'escola', 'responsavel'], 'data', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Ocorrências', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('ocorrencia.index', compact(
            'ocorrencias', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Ocorrências', 'ocorrencia.index', 'Nova Ocorrência', 'Vida Acadêmica');

        return view('ocorrencia.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreOcorrenciaRequest $request)
    {
        $this->ocorrenciaService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Ocorrência registrada com sucesso.');
    }

    public function edit($id)
    {
        $ocorrencia = $this->ocorrenciaService->getById($id, ['aluno.pessoa', 'escola', 'responsavel']);
        abort_if(! $ocorrencia, 404, 'Ocorrência não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Ocorrências', 'ocorrencia.index', "Editar: {$ocorrencia->tipoLabel()}", 'Vida Acadêmica');

        return view('ocorrencia.add-edit', array_merge(compact('ocorrencia', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateOcorrenciaRequest $request, $id)
    {
        $ocorrencia = $this->ocorrenciaService->update($id, $request->validated());
        abort_if(! $ocorrencia, 403, 'Sem acesso para editar esta ocorrência.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Ocorrência atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->ocorrenciaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta ocorrência.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'ocorrencia');
        session()->put("$this->nomeFiltro.rotaRedirect", 'ocorrencia.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'tipo', 'label' => 'Tipo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Ocorrencia::TIPOS,
            ],
            [
                'name' => 'gravidade', 'label' => 'Gravidade', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Ocorrencia::GRAVIDADES,
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ],
            [
                'name' => 'aluno_id', 'label' => 'Aluno', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->alunosOpcoesFiltro(),
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        $alunos = Aluno::query()->with(['pessoa', 'escola'])->orderBy('id')->get();

        return [
            // Dados estruturados para o modal de busca (picker) do formulario.
            'alunosPicker' => $alunos->map(fn (Aluno $a) => [
                'id' => $a->id,
                'rotulo' => $a->pessoa->nome ?? ('Aluno #'.$a->id),
                'detalhe' => trim(($a->registro_academico ? 'RA '.$a->registro_academico.' · ' : '').($a->escola->nome ?? ''), ' ·'),
                'escola_id' => $a->escola_id,
                'escola' => $a->escola->nome ?? '',
            ])->values()->all(),
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'tipos' => Ocorrencia::TIPOS,
            'gravidades' => Ocorrencia::GRAVIDADES,
        ];
    }

    private function alunosOpcoesFiltro()
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->limit(500)->get()
            ->map(fn ($a) => ['value' => $a->id, 'label' => ($a->pessoa->nome ?? 'Aluno #'.$a->id).' — '.$a->registro_academico])
            ->all();
    }
}
