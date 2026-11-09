<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Models\Empresa;
use App\Services\BreadcrumbService;
use App\Services\EmpresaService;
use App\Support\UploadStorage;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmpresaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    protected $empresaService;

    protected $breadcrumbService;

    public function __construct(EmpresaService $empresaService, BreadcrumbService $breadcrumbService)
    {
        $this->middleware('can:gerenciar-empresa', ['except' => ['minhaEmpresa', 'editarEmpresa', 'atualizarEmpresa']]);

        $this->titulo = 'Empresas';
        $this->nomeFiltro = 'empresa';
        $this->empresaService = $empresaService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations.');
        }

        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 12);

        $empresas = $this->empresaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Empresas', 'Configurações');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 12, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('empresa.index', compact(
            'empresas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations.');
        }

        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Empresas', 'empresa.index', 'Criar', 'Configurações');
        [$metodo, $rota] = $this->getFormMethodAndRoute();

        return view('empresa.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEmpresaRequest $request)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations.');
        }

        $data = $request->validated();
        $data = $this->handleLogoUpload($request, $data);

        $this->empresaService->create($data);

        return $this->redirecionaRotaRedirect($this->nomeFiltro)
            ->with('success', 'Tenant criado com os perfis escolares padrao.');
    }

    public function show($id)
    {
        $empresa = $this->empresaService->getById($id, ['escolas', 'users']);
        if (! $empresa) {
            return redirect()->route('empresa.index')->with('error', 'Tenant nao encontrado.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresas', 'empresa.index', $empresa->nome, 'Configurações');

        return view('empresa.show', compact('empresa', 'breadcrumb'))->with('titulo', $empresa->nome);
    }

    public function edit($id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations.');
        }

        $empresa = $this->empresaService->getById($id);
        if (! $empresa) {
            abort(403, 'Sem acesso a esta empresa.');
        }

        $status = ['S' => 'Ativo', 'N' => 'Inativo'];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresas', 'empresa.index', "Editar: $empresa->nome", 'Configurações');
        [$metodo, $rota] = $this->getFormMethodAndRoute($empresa);

        return view('empresa.add-edit', compact('empresa', 'breadcrumb', 'status', 'metodo', 'rota'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEmpresaRequest $request, $id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations.');
        }

        $empresaAtual = $this->empresaService->getById($id);
        if (! $empresaAtual) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        $data = $request->validated();
        $data = $this->handleLogoUpload($request, $data, $empresaAtual);
        $updated = $this->empresaService->update($id, $data);
        if (! $updated) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations.');
        }

        $success = $this->empresaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta empresa.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /**
     * Administrador do tenant ve os proprios dados.
     */
    public function minhaEmpresa()
    {
        $user = auth()->user();
        if (! $user || ! $user->empresa_id) {
            return redirect()->route('home')->with('error', 'Voce nao esta vinculado a uma empresa.');
        }

        $empresa = $this->empresaService->getById($user->empresa_id);
        if (! $empresa) {
            return redirect()->route('home')->with('error', 'Tenant nao encontrado.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Minha Organização', 'home', $empresa->nome);

        return view('empresa.show', compact('empresa', 'breadcrumb'));
    }

    public function editarEmpresa()
    {
        $user = auth()->user();
        if (! $user || ! $user->empresa_id) {
            return redirect()->route('home')->with('error', 'Voce nao esta vinculado a uma empresa.');
        }

        $empresa = $this->empresaService->getById($user->empresa_id);
        if (! $empresa) {
            return redirect()->route('home')->with('error', 'Tenant nao encontrado.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Minha Organização', 'home', "Editar: $empresa->nome");
        $status = ['S' => 'Ativo', 'N' => 'Inativo'];

        return view('empresa.edit-admin', compact('empresa', 'breadcrumb', 'status'));
    }

    public function atualizarEmpresa(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->empresa_id) {
            return redirect()->route('home')->with('error', 'Voce nao esta vinculado a uma empresa.');
        }

        $empresa = $this->empresaService->getById($user->empresa_id);
        if (! $empresa) {
            return redirect()->route('home')->with('error', 'Tenant nao encontrado.');
        }

        $data = $request->validate([
            'nome' => 'required|string|max:200',
            'email' => 'nullable|email|max:200',
            'telefone' => 'nullable|string|max:30',
            'cnpj' => 'nullable|string|max:20',
            'cor_primaria' => 'nullable|string|max:20',
            'cor_secundaria' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
        ]);

        $data = $this->handleLogoUpload($request, $data, $empresa);

        $empresa->update($data);

        return redirect()->route('empresa.minha')
            ->with('success', 'Empresa atualizada com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'empresa');
        session()->put("$this->nomeFiltro.rotaRedirect", 'empresa.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cnpj', 'label' => 'CNPJ', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false],
            ]],
        ];
    }

    private function getFormMethodAndRoute($empresa = null)
    {
        if ($empresa) {
            return ['PUT', route('empresa.update', $empresa->id)];
        }

        return ['POST', route('empresa.store')];
    }

    private function empresaTableExists(): bool
    {
        return \App\Support\SchemaCache::hasTable('empresas');
    }

    private function handleLogoUpload(Request $request, array $data, ?Empresa $empresa = null): array
    {
        if (! $request->hasFile('logo')) {
            return $data;
        }

        $logo = $request->file('logo');

        if (! $logo instanceof UploadedFile || ! $logo->isValid()) {
            throw ValidationException::withMessages([
                'logo' => 'O arquivo de logo nao foi enviado corretamente. Tente novamente.',
            ]);
        }

        $path = UploadStorage::putFile($logo, 'empresas/logo', null, 'public');
        if (! $path) {
            throw ValidationException::withMessages([
                'logo' => 'O arquivo de logo nao foi enviado corretamente. Tente novamente.',
            ]);
        }

        if ($empresa?->logo_path) {
            Storage::disk('public')->delete($empresa->logo_path);
        }

        $data['logo_path'] = $path;

        return $data;
    }
}
