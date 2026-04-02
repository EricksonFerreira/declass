<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Models\Empresa;
use App\Services\BreadcrumbService;
use App\Services\EmpresaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmpresaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $empresaService;
    protected $breadcrumbService;

    public function __construct(EmpresaService $empresaService, BreadcrumbService $breadcrumbService)
    {
        $this->middleware('can:gerenciar-empresa');

        $this->titulo = 'Empresas';
        $this->nomeFiltro = 'empresa';
        $this->empresaService = $empresaService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        if (!$this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $empresas = $this->empresaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Empresas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('empresa.index', compact(
            'empresas',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        if (!$this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Empresas', 'empresa.index', 'Criar');
        [$metodo, $rota] = $this->getFormMethodAndRoute();

        return view('empresa.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEmpresaRequest $request)
    {
        if (!$this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $data = $request->validated();
        $data = $this->handleLogoUpload($request, $data);

        $this->empresaService->createWithDefaultTemplate($data);

        return $this->redirecionaRotaRedirect($this->nomeFiltro)
            ->with('success', 'Empresa criada com base no modelo padrao.');
    }

    public function show($id)
    {
        return redirect()->route('empresa.edit', $id);
    }

    public function edit($id)
    {
        if (!$this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $empresa = $this->empresaService->getById($id);
        if (!$empresa) {
            abort(403, 'Sem acesso a esta empresa.');
        }

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresas', 'empresa.index', "Editar: $empresa->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($empresa);

        return view('empresa.add-edit', compact('empresa', 'breadcrumb', 'status', 'metodo', 'rota'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEmpresaRequest $request, $id)
    {
        if (!$this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $data = $request->validated();

        $empresaAtual = $this->empresaService->getById($id);
        if (!$empresaAtual) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        foreach (['stripe_secret', 'mercado_livre_access_token', 'paypal_client_secret'] as $secretField) {
            if (!array_key_exists($secretField, $data) || $data[$secretField] === null || $data[$secretField] === '') {
                unset($data[$secretField]);
            }
        }

        $data = $this->handleLogoUpload($request, $data, $empresaAtual);
        $updated = $this->empresaService->update($id, $data);
        if (!$updated) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        if (!$this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $deleted = $this->empresaService->delete($id);
        if (!$deleted) {
            abort(403, 'Sem acesso para excluir esta empresa.');
        }
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $atributo = $request->input('atributo') ?? null;
        $this->removeSessao($this->nomeFiltro, $atributo);
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
            ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cnpj', 'label' => 'CNPJ', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false],
            ]],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

    private function getFormMethodAndRoute($empresa = null)
    {
        if ($empresa instanceof Empresa) {
            return [
                'PUT',
                route('empresa.update', $empresa->id),
            ];
        }

        return [
            'POST',
            route('empresa.store'),
        ];
    }

    private function empresaTableExists(): bool
    {
        return Schema::hasTable('empresas');
    }

    private function handleLogoUpload(Request $request, array $data, ?Empresa $empresaAtual = null): array
    {
        if (!$request->hasFile('logo')) {
            return $data;
        }

        $file = $request->file('logo');

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $data;
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            $fileName = Str::uuid()->toString() . '.' . $extension;
            $path = 'empresas/logos/' . $fileName;

            $stored = Storage::disk('public')->put($path, $file->getContent());
            if (!$stored) {
                return $data;
            }
        } catch (\Throwable $e) {
            Log::error('Falha ao salvar logo da empresa.', [
                'empresa_id' => $empresaAtual?->id,
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return $data;
        }

        if (!is_string($path) || $path === '') {
            return $data;
        }

        $data['logo_path'] = $path;

        if ($empresaAtual && !empty($empresaAtual->logo_path) && Storage::disk('public')->exists($empresaAtual->logo_path)) {
            Storage::disk('public')->delete($empresaAtual->logo_path);
        }

        return $data;
    }
}
