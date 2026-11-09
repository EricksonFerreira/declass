<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Escola\StoreEscolaRequest;
use App\Http\Requests\Escola\UpdateEscolaRequest;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\EscolaService;
use App\Support\UploadStorage;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class EscolaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected EscolaService $escolaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Escolas';
        $this->nomeFiltro = 'escola';

        $this->middleware('can:escola.index')->only('index');
        $this->middleware('can:escola.show')->only('show');
        $this->middleware('can:escola.create')->only('create');
        $this->middleware('can:escola.store')->only('store');
        $this->middleware('can:escola.edit')->only('edit');
        $this->middleware('can:escola.update')->only('update');
        $this->middleware('can:escola.destroy')->only('destroy');
        $this->middleware('can:escola.filtro')->only('filtro');
        $this->middleware('can:escola.removerFiltro')->only('removerFiltro');
        $this->middleware('can:escola.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $escolas = $this->escolaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['empresa']
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Escolas', 'Gerenciar');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('escola.index', compact(
            'escolas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Escolas', 'escola.index', 'Criar', 'Gerenciar');
        $turnos = Escola::TURNOS;

        return view('escola.add-edit', compact('breadcrumb', 'turnos'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEscolaRequest $request)
    {
        $data = $request->validated();
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        if ($logo) {
            $data['logo_path'] = UploadStorage::putFile($logo, 'escolas/logo');
        }

        $this->escolaService->create($data);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Escola criada com sucesso.');
    }

    public function show($id)
    {
        $escola = $this->escolaService->getById($id, ['empresa', 'usuarios']);
        abort_if(! $escola, 404, 'Escola não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Escolas', 'escola.index', $escola->nome);

        return view('escola.show', compact('escola', 'breadcrumb'))->with('titulo', $escola->nome);
    }

    public function edit($id)
    {
        $escola = $this->escolaService->getById($id);
        abort_if(! $escola, 404, 'Escola não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Escolas', 'escola.index', "Editar: {$escola->nome}");
        $turnos = Escola::TURNOS;

        return view('escola.add-edit', compact('escola', 'breadcrumb', 'turnos'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEscolaRequest $request, $id)
    {
        $escola = $this->escolaService->getById($id);
        abort_if(! $escola, 404, 'Escola não encontrada.');

        $data = $request->validated();
        $logo = $data['logo'] ?? null;
        unset($data['logo']);

        if ($logo) {
            if ($escola->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($escola->logo_path);
            }
            $data['logo_path'] = UploadStorage::putFile($logo, 'escolas/logo');
        }

        $escola = $this->escolaService->update($id, $data);
        abort_if(! $escola, 403, 'Sem acesso para editar esta escola.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Escola atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->escolaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta escola.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'escola');
        session()->put("$this->nomeFiltro.rotaRedirect", 'escola.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cidade', 'label' => 'Cidade', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'estado', 'label' => 'UF', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }
}
