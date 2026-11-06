<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Responsavel\StoreResponsavelRequest;
use App\Http\Requests\Responsavel\UpdateResponsavelRequest;
use App\Models\Pessoa;
use App\Services\BreadcrumbService;
use App\Services\ResponsavelService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class ResponsavelController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected ResponsavelService $responsavelService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Responsáveis';
        $this->nomeFiltro = 'responsavel';

        $this->middleware('can:responsavel.index')->only('index');
        $this->middleware('can:responsavel.show')->only('show');
        $this->middleware('can:responsavel.create')->only('create');
        $this->middleware('can:responsavel.store')->only('store');
        $this->middleware('can:responsavel.edit')->only('edit');
        $this->middleware('can:responsavel.update')->only('update');
        $this->middleware('can:responsavel.destroy')->only('destroy');
        $this->middleware('can:responsavel.filtro')->only('filtro');
        $this->middleware('can:responsavel.removerFiltro')->only('removerFiltro');
        $this->middleware('can:responsavel.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $responsaveis = $this->responsavelService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Responsáveis', 'Acadêmico');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('responsavel.index', compact(
            'responsaveis', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Responsáveis', 'responsavel.index', 'Novo Responsável', 'Acadêmico');
        $sexos = Pessoa::SEXOS;

        return view('responsavel.add-edit', compact('breadcrumb', 'sexos'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreResponsavelRequest $request)
    {
        [$pessoaData, $responsavelData] = $this->separarDados($request);

        $this->responsavelService->createWithPessoa($pessoaData, $responsavelData);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Responsável cadastrado com sucesso.');
    }

    public function show($id)
    {
        $responsavel = $this->responsavelService->getById($id, ['pessoa', 'alunos.pessoa']);
        abort_if(! $responsavel, 404, 'Responsável não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Responsáveis', 'responsavel.index', $responsavel->nome ?? 'Responsável');

        return view('responsavel.show', compact('responsavel', 'breadcrumb'))
            ->with('titulo', $responsavel->nome ?? 'Responsável');
    }

    public function edit($id)
    {
        $responsavel = $this->responsavelService->getById($id, ['pessoa']);
        abort_if(! $responsavel, 404, 'Responsável não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Responsáveis', 'responsavel.index', 'Editar: '.($responsavel->nome ?? 'Responsável'), 'Acadêmico');
        $sexos = Pessoa::SEXOS;

        return view('responsavel.add-edit', compact('responsavel', 'breadcrumb', 'sexos'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateResponsavelRequest $request, $id)
    {
        [$pessoaData, $responsavelData] = $this->separarDados($request);

        $responsavel = $this->responsavelService->updateWithPessoa($id, $pessoaData, $responsavelData);
        abort_if(! $responsavel, 403, 'Sem acesso para editar este responsável.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Responsável atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->responsavelService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este responsável.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'responsavel');
        session()->put("$this->nomeFiltro.rotaRedirect", 'responsavel.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            ['name' => 'profissao', 'label' => 'Profissão', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    /**
     * Separa o payload flat do form em dados de Pessoa e de Responsavel.
     *
     * @return array{0: array, 1: array}
     */
    private function separarDados($request): array
    {
        $data = $request->validated();

        $pessoaData = [];
        foreach (StoreResponsavelRequest::PESSOA_FIELDS as $campo) {
            if (array_key_exists($campo, $data)) {
                $pessoaData[$campo] = $data[$campo];
            }
        }

        $responsavelData = [];
        foreach (StoreResponsavelRequest::RESPONSAVEL_FIELDS as $campo) {
            if (array_key_exists($campo, $data)) {
                $responsavelData[$campo] = $data[$campo];
            }
        }

        return [$pessoaData, $responsavelData];
    }
}
