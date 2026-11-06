<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\PlanoAtendimento\StorePlanoAtendimentoRequest;
use App\Http\Requests\PlanoAtendimento\UpdatePlanoAtendimentoRequest;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\PlanoAtendimento;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\PlanoAtendimentoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class PlanoAtendimentoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected PlanoAtendimentoService $planoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Planos de Atendimento';
        $this->nomeFiltro = 'plano_atendimento';

        $this->middleware('can:plano_atendimento.index')->only('index');
        $this->middleware('can:plano_atendimento.create')->only('create');
        $this->middleware('can:plano_atendimento.store')->only('store');
        $this->middleware('can:plano_atendimento.edit')->only('edit');
        $this->middleware('can:plano_atendimento.update')->only('update');
        $this->middleware('can:plano_atendimento.destroy')->only('destroy');
        $this->middleware('can:plano_atendimento.filtro')->only('filtro');
        $this->middleware('can:plano_atendimento.removerFiltro')->only('removerFiltro');
        $this->middleware('can:plano_atendimento.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $planos = $this->planoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['aluno.pessoa', 'escola', 'profissional'], 'data_elaboracao', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Planos de Atendimento', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('plano_atendimento.index', compact(
            'planos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Planos de Atendimento', 'plano_atendimento.index', 'Novo Plano', 'Vida Acadêmica');

        return view('plano_atendimento.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StorePlanoAtendimentoRequest $request)
    {
        $this->planoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Plano de atendimento cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $plano = $this->planoService->getById($id, ['aluno.pessoa', 'escola', 'profissional']);
        abort_if(! $plano, 404, 'Plano não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Planos de Atendimento', 'plano_atendimento.index', "Editar: {$plano->tipoLabel()}", 'Vida Acadêmica');

        return view('plano_atendimento.add-edit', array_merge(compact('plano', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdatePlanoAtendimentoRequest $request, $id)
    {
        $plano = $this->planoService->update($id, $request->validated());
        abort_if(! $plano, 403, 'Sem acesso para editar este plano.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Plano atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->planoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este plano.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'plano_atendimento');
        session()->put("$this->nomeFiltro.rotaRedirect", 'plano_atendimento.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'tipo', 'label' => 'Tipo de plano', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => PlanoAtendimento::TIPOS,
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => PlanoAtendimento::STATUS,
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
                'name' => 'ativo', 'label' => 'Situação', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'alunos' => Aluno::query()->with('pessoa')->orderBy('id')->get()
                ->mapWithKeys(fn ($a) => [$a->id => ($a->pessoa->nome ?? 'Aluno #'.$a->id).' — '.$a->registro_academico])
                ->all(),
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'profissionais' => User::query()->orderBy('name')->limit(200)->pluck('name', 'id')->all(),
            'tipos' => PlanoAtendimento::TIPOS,
            'statusList' => PlanoAtendimento::STATUS,
        ];
    }

    private function alunosOpcoesFiltro()
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->limit(500)->get()
            ->map(fn ($a) => ['value' => $a->id, 'label' => ($a->pessoa->nome ?? 'Aluno #'.$a->id).' — '.$a->registro_academico])
            ->all();
    }
}
