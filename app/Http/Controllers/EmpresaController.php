<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaSiteApresentacaoRequest;
use App\Models\ConfiguracaoCalcao;
use App\Models\Empresa;
use App\Models\GrupoFrota;
use App\Support\ArquivoAnexoHelper;
use App\Services\BreadcrumbService;
use App\Services\EmpresaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private const SITE_TEXTO_KEYS = [
        'hero_tag',
        'hero_titulo',
        'hero_subtitulo',
        'hero_descricao',
        'menu_item_frota',
        'menu_item_vantagens',
        'menu_item_etapas',
        'menu_item_contato',
        'hero_form_titulo',
        'hero_form_descricao',
        'hero_form_label_retirada',
        'hero_form_label_devolucao',
        'hero_form_label_data_retirada',
        'hero_form_label_hora_retirada',
        'hero_form_label_data_devolucao',
        'hero_form_label_hora_devolucao',
        'hero_form_texto_botao',
        'hero_form_nota',
        'frota_titulo',
        'frota_label_superior',
        'frota_descricao',
        'frota_card_1_titulo',
        'frota_card_1_descricao',
        'frota_card_2_titulo',
        'frota_card_2_descricao',
        'frota_card_3_titulo',
        'frota_card_3_descricao',
        'vantagens_titulo',
        'vantagens_label_superior',
        'vantagens_descricao',
        'beneficio_1',
        'beneficio_2',
        'beneficio_3',
        'beneficio_4',
        'etapas_titulo',
        'etapas_label_superior',
        'etapa_1_titulo',
        'etapa_1_descricao',
        'etapa_2_titulo',
        'etapa_2_descricao',
        'etapa_3_titulo',
        'etapa_3_descricao',
        'contato_titulo',
        'contato_label_superior',
        'botao_admin_texto',
        'footer_texto',
    ];

    private const SITE_IMAGEM_FIELDS = [
        'imagem_hero' => 'hero',
        'imagem_frota_1' => 'frota_1',
        'imagem_frota_2' => 'frota_2',
        'imagem_frota_3' => 'frota_3',
        'imagem_vantagens' => 'vantagens',
        'imagem_contato' => 'contato',
    ];

    private const SITE_IMAGEM_VISIBILIDADE_FIELDS = [
        'usar_imagem_hero' => 'hero',
        'usar_imagem_frota_1' => 'frota_1',
        'usar_imagem_frota_2' => 'frota_2',
        'usar_imagem_frota_3' => 'frota_3',
        'usar_imagem_vantagens' => 'vantagens',
        'usar_imagem_contato' => 'contato',
    ];

    private const SITE_COR_FIELDS = [
        'cor_primaria' => 'brand_primaria',
        'cor_secundaria' => 'brand_secundaria',
        'cor_terciaria' => 'brand_terciaria',
        'cor_topbar_fundo' => 'topbar_fundo',
        'cor_topbar_texto' => 'topbar_texto',
        'cor_topbar_link_hover' => 'topbar_link_hover',
        'cor_hero_fundo' => 'hero_fundo',
        'cor_hero_texto' => 'hero_texto',
        'cor_form_fundo' => 'form_fundo',
        'cor_form_texto' => 'form_texto',
        'cor_frota_fundo' => 'frota_fundo',
        'cor_frota_etiqueta_texto' => 'frota_etiqueta_texto',
        'cor_frota_titulo_texto' => 'frota_titulo_texto',
        'cor_frota_descricao_texto' => 'frota_descricao_texto',
        'cor_frota_card_titulo_texto' => 'frota_card_titulo_texto',
        'cor_frota_card_descricao_texto' => 'frota_card_descricao_texto',
        'cor_frota_card_fundo' => 'frota_card_fundo',
        'cor_vantagens_fundo' => 'vantagens_fundo',
        'cor_vantagens_etiqueta_texto' => 'vantagens_etiqueta_texto',
        'cor_vantagens_titulo_texto' => 'vantagens_titulo_texto',
        'cor_vantagens_descricao_texto' => 'vantagens_descricao_texto',
        'cor_vantagens_item_texto' => 'vantagens_item_texto',
        'cor_etapas_fundo' => 'etapas_fundo',
        'cor_etapas_etiqueta_texto' => 'etapas_etiqueta_texto',
        'cor_etapas_titulo_texto' => 'etapas_titulo_texto',
        'cor_etapas_descricao_texto' => 'etapas_descricao_texto',
        'cor_etapas_passo_texto' => 'etapas_passo_texto',
        'cor_etapas_passo_descricao_texto' => 'etapas_passo_descricao_texto',
        'cor_etapas_passo_fundo' => 'etapas_passo_fundo',
        'cor_contato_fundo' => 'contato_fundo',
        'cor_footer_fundo' => 'footer_fundo',
        'cor_footer_texto' => 'footer_texto',
        'cor_botao_primario_fundo' => 'botao_primario_fundo',
        'cor_botao_primario_texto' => 'botao_primario_texto',
        'cor_botao_secundario_fundo' => 'botao_secundario_fundo',
        'cor_botao_secundario_texto' => 'botao_secundario_texto',
        'cor_admin_fundo' => 'admin_fundo',
        'cor_admin_texto' => 'admin_texto',
    ];

    protected $empresaService;

    protected $breadcrumbService;

    public function __construct(EmpresaService $empresaService, BreadcrumbService $breadcrumbService)
    {
        $this->middleware('can:gerenciar-empresa', ['except' => ['minhaEmpresa']]);

        $this->titulo = 'Empresas';
        $this->nomeFiltro = 'empresa';
        $this->empresaService = $empresaService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        if (! $this->empresaTableExists()) {
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
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Empresas', 'empresa.index', 'Criar');
        [$metodo, $rota] = $this->getFormMethodAndRoute();
        [$calcaoConfigEmpresa, $calcaoConfigsGrupo, $gruposFrota] = $this->dadosCalcaoForm(null);

        return view('empresa.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota', 'calcaoConfigEmpresa', 'calcaoConfigsGrupo', 'gruposFrota'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreEmpresaRequest $request)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $data = $request->validated();
        $data = $this->handleLogoUpload($request, $data);

        $empresa = $this->empresaService->createWithDefaultTemplate($data);
        $this->syncCalcaoConfiguracoes($empresa, $request);

        return $this->redirecionaRotaRedirect($this->nomeFiltro)
            ->with('success', 'Empresa criada com base no modelo padrao.');
    }

    public function editarEmpresa()
    {
        $user = auth()->user();
        if (!$user || !$user->empresa_id) {
            return redirect()->route('home')->with('error', 'Voce nao esta vinculado a uma empresa.');
        }

        $empresa = $this->empresaService->getById($user->empresa_id);
        if (!$empresa) {
            return redirect()->route('home')->with('error', 'Empresa nao encontrada.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresa', 'assinatura.meu-plano', "Editar: $empresa->nome");

        return view('empresa.edit-admin', compact('empresa', 'breadcrumb'));
    }

    public function atualizarEmpresa(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->empresa_id) {
            return redirect()->route('home')->with('error', 'Voce nao esta vinculado a uma empresa.');
        }

        $empresa = $this->empresaService->getById($user->empresa_id);
        if (!$empresa) {
            return redirect()->route('home')->with('error', 'Empresa nao encontrada.');
        }

        $data = $request->validate([
            'nome'       => 'required|string|max:200',
            'email'      => 'nullable|email|max:200',
            'telefone'   => 'nullable|string|max:30',
            'cnpj'       => 'nullable|string|max:20',
            'cor_primaria'   => 'nullable|string|max:20',
            'cor_secundaria' => 'nullable|string|max:20',
            'logo'       => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('empresas/logo', 'public');
            if ($path) {
                if ($empresa->logo_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($empresa->logo_path);
                }
                $data['logo_path'] = $path;
            }
        }

        $empresa->update($data);

        return redirect()->route('empresa.minha')
            ->with('success', 'Empresa atualizada com sucesso.');
    }

    public function minhaEmpresa()
    {
        $user = auth()->user();
        if (!$user || !$user->empresa_id) {
            return redirect()->route('home')->with('error', 'Voce nao esta vinculado a uma empresa.');
        }

        $empresa = $this->empresaService->getById($user->empresa_id);
        if (!$empresa) {
            return redirect()->route('home')->with('error', 'Empresa nao encontrada.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresa', 'assinatura.meu-plano', $empresa->nome);

        return view('empresa.show', compact('empresa', 'breadcrumb'));
    }

    public function show($id)
    {
        $empresa = $this->empresaService->getById($id);
        if (!$empresa) {
            return redirect()->route('empresa.index')->with('error', 'Empresa nao encontrada.');
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresas', 'empresa.index', $empresa->nome);

        return view('empresa.show', compact('empresa', 'breadcrumb'));
    }

    public function edit($id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $empresa = $this->empresaService->getById($id);
        if (! $empresa) {
            abort(403, 'Sem acesso a esta empresa.');
        }

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Empresas', 'empresa.index', "Editar: $empresa->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($empresa);
        [$calcaoConfigEmpresa, $calcaoConfigsGrupo, $gruposFrota] = $this->dadosCalcaoForm($empresa);

        return view('empresa.add-edit', compact('empresa', 'breadcrumb', 'status', 'metodo', 'rota', 'calcaoConfigEmpresa', 'calcaoConfigsGrupo', 'gruposFrota'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateEmpresaRequest $request, $id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $data = $request->validated();

        $empresaAtual = $this->empresaService->getById($id);
        if (! $empresaAtual) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        foreach (['stripe_secret', 'mercado_livre_access_token', 'paypal_client_secret', 'nfse_client_secret'] as $secretField) {
            if (! array_key_exists($secretField, $data) || $data[$secretField] === null || $data[$secretField] === '') {
                unset($data[$secretField]);
            }
        }

        $data = $this->handleLogoUpload($request, $data, $empresaAtual);
        $updated = $this->empresaService->update($id, $data);
        if (! $updated) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        $this->syncCalcaoConfiguracoes($empresaAtual->fresh(), $request);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function editSiteApresentacao($id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $empresa = $this->empresaService->getById($id);
        if (! $empresa) {
            abort(403, 'Sem acesso a esta empresa.');
        }

        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $modelosSite = [
            ['value' => 'modelo_1', 'label' => 'Modelo 1 - Premium Locadora', 'enabled' => true],
            ['value' => 'modelo_2', 'label' => 'Modelo 2 - Compacto (em breve)', 'enabled' => false],
            ['value' => 'modelo_3', 'label' => 'Modelo 3 - Corporativo (em breve)', 'enabled' => false],
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Empresas',
            'empresa.index',
            "Site de apresentacao: $empresa->nome"
        );
        $metodo = 'PUT';
        $rota = route('empresa.site-apresentacao.update', $empresa->id);
        $siteTextos = is_array($empresa->site_apresentacao_textos) ? $empresa->site_apresentacao_textos : [];
        $siteImagens = is_array($empresa->site_apresentacao_imagens) ? $empresa->site_apresentacao_imagens : [];
        $siteCores = is_array($empresa->site_apresentacao_cores) ? $empresa->site_apresentacao_cores : [];
        $siteImagensConfig = is_array($empresa->site_apresentacao_imagens_config) ? $empresa->site_apresentacao_imagens_config : [];
        $imageRequirements = $this->siteImageRequirements();

        return view('empresa.site-apresentacao', compact(
            'empresa',
            'breadcrumb',
            'metodo',
            'rota',
            'status',
            'modelosSite',
            'siteTextos',
            'siteImagens',
            'siteCores',
            'siteImagensConfig',
            'imageRequirements'
        ))
            ->with('titulo', 'Configuracoes do Site de Apresentacao');
    }

    public function updateSiteApresentacao(UpdateEmpresaSiteApresentacaoRequest $request, $id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $empresaAtual = $this->empresaService->getById($id);
        if (! $empresaAtual) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        $data = $request->validated();
        $data = $this->handleLogoUpload($request, $data, $empresaAtual);
        $data = $this->prepareSiteTextosPayload($request, $empresaAtual, $data);
        $data = $this->prepareSiteCoresPayload($request, $empresaAtual, $data);
        $data = $this->prepareSiteImagensConfigPayload($request, $empresaAtual, $data);
        $data = $this->handleSiteImagesUpload($request, $data, $empresaAtual);

        foreach (array_keys(self::SITE_IMAGEM_FIELDS) as $imageField) {
            unset($data[$imageField]);
        }

        foreach (array_keys(self::SITE_IMAGEM_VISIBILIDADE_FIELDS) as $imageVisibilityField) {
            unset($data[$imageVisibilityField]);
        }

        foreach (self::SITE_TEXTO_KEYS as $textKey) {
            unset($data[$textKey]);
        }

        foreach (array_keys(self::SITE_COR_FIELDS) as $siteColorField) {
            unset($data[$siteColorField]);
        }

        $updated = $this->empresaService->update($id, $data);
        if (! $updated) {
            abort(403, 'Sem acesso para editar esta empresa.');
        }

        return redirect()
            ->route('empresa.site-apresentacao.edit', $id)
            ->with('success', 'Configuracoes do site de apresentacao salvas com sucesso.');
    }

    public function destroy($id)
    {
        if (! $this->empresaTableExists()) {
            return redirect()->route('home')
                ->with('error', 'Tabela de empresas nao encontrada. Execute as migrations do modulo de empresa.');
        }

        $deleted = $this->empresaService->delete($id);
        if (! $deleted) {
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
        if (! $request->hasFile('logo')) {
            return $data;
        }

        try {
            $path = ArquivoAnexoHelper::armazenar(
                $request->file('logo'),
                'empresas/logos',
                $empresaAtual?->logo_path ?? null
            );

            if (! is_string($path) || $path === '') {
                return $data;
            }

            $data['logo_path'] = $path;
        } catch (\Throwable $e) {
            Log::error('Falha ao salvar logo da empresa.', [
                'empresa_id' => $empresaAtual?->id,
                'file_name' => $request->file('logo')?->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return $data;
        }

        return $data;
    }

    private function prepareSiteTextosPayload(Request $request, Empresa $empresaAtual, array $data): array
    {
        $textos = is_array($empresaAtual->site_apresentacao_textos) ? $empresaAtual->site_apresentacao_textos : [];

        foreach (self::SITE_TEXTO_KEYS as $key) {
            if ($request->has($key)) {
                $value = trim((string) $request->input($key, ''));
                $textos[$key] = $value === '' ? null : $value;
            }
        }

        $data['site_apresentacao_textos'] = $textos;

        if (! empty($textos['hero_titulo'])) {
            $data['site_apresentacao_titulo'] = $textos['hero_titulo'];
        }

        if (! empty($textos['hero_subtitulo'])) {
            $data['site_apresentacao_subtitulo'] = $textos['hero_subtitulo'];
        }

        if (! empty($textos['hero_descricao'])) {
            $data['site_apresentacao_descricao'] = $textos['hero_descricao'];
        }

        return $data;
    }

    private function prepareSiteCoresPayload(Request $request, Empresa $empresaAtual, array $data): array
    {
        $cores = is_array($empresaAtual->site_apresentacao_cores) ? $empresaAtual->site_apresentacao_cores : [];

        foreach (self::SITE_COR_FIELDS as $inputName => $colorKey) {
            if (! $request->has($inputName)) {
                continue;
            }

            $value = trim((string) $request->input($inputName, ''));
            if ($value === '') {
                $cores[$colorKey] = null;

                continue;
            }

            $value = strtoupper($value);
            if (! str_starts_with($value, '#')) {
                $value = '#'.$value;
            }

            $cores[$colorKey] = $value;
        }

        $data['site_apresentacao_cores'] = $cores;

        return $data;
    }

    private function prepareSiteImagensConfigPayload(Request $request, Empresa $empresaAtual, array $data): array
    {
        $config = is_array($empresaAtual->site_apresentacao_imagens_config) ? $empresaAtual->site_apresentacao_imagens_config : [];

        foreach (self::SITE_IMAGEM_VISIBILIDADE_FIELDS as $inputName => $imageKey) {
            if (! $request->has($inputName)) {
                continue;
            }

            $config[$imageKey] = $request->boolean($inputName);
        }

        $data['site_apresentacao_imagens_config'] = $config;

        return $data;
    }

    private function handleSiteImagesUpload(Request $request, array $data, Empresa $empresaAtual): array
    {
        $imagens = is_array($empresaAtual->site_apresentacao_imagens) ? $empresaAtual->site_apresentacao_imagens : [];

        foreach (self::SITE_IMAGEM_FIELDS as $inputName => $imageKey) {
            if (! $request->hasFile($inputName)) {
                continue;
            }

            try {
                $path = ArquivoAnexoHelper::armazenar(
                    $request->file($inputName),
                    'empresas/site_apresentacao/' . $empresaAtual->id . '/' . $imageKey,
                    $imagens[$imageKey] ?? null
                );

                if (! is_string($path) || $path === '') {
                    continue;
                }

                $imagens[$imageKey] = $path;
            } catch (\Throwable $e) {
                Log::error('Falha ao salvar imagem da apresentacao da empresa.', [
                    'empresa_id' => $empresaAtual->id,
                    'campo' => $inputName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $data['site_apresentacao_imagens'] = $imagens;

        return $data;
    }

    private function siteImageRequirements(): array
    {
        return [
            [
                'modelo' => 'modelo_1',
                'nome' => 'Modelo 1 - Premium Locadora',
                'status' => 'Disponivel',
                'locais' => [
                    ['area' => 'Hero principal', 'campo' => 'imagem_hero', 'minimo' => '1600x900', 'opcional' => true],
                    ['area' => 'Frota card 1', 'campo' => 'imagem_frota_1', 'minimo' => '900x600', 'opcional' => true],
                    ['area' => 'Frota card 2', 'campo' => 'imagem_frota_2', 'minimo' => '900x600', 'opcional' => true],
                    ['area' => 'Frota card 3', 'campo' => 'imagem_frota_3', 'minimo' => '900x600', 'opcional' => true],
                    ['area' => 'Secao vantagens', 'campo' => 'imagem_vantagens', 'minimo' => '1200x700', 'opcional' => true],
                    ['area' => 'Secao contato', 'campo' => 'imagem_contato', 'minimo' => '900x500', 'opcional' => true],
                ],
            ],
            [
                'modelo' => 'modelo_2',
                'nome' => 'Modelo 2 - Compacto',
                'status' => 'Em breve',
                'locais' => [],
            ],
            [
                'modelo' => 'modelo_3',
                'nome' => 'Modelo 3 - Corporativo',
                'status' => 'Em breve',
                'locais' => [],
            ],
        ];
    }

    /**
     * @return array{0:?ConfiguracaoCalcao,1:array<int, ConfiguracaoCalcao>,2:\Illuminate\Support\Collection<int, GrupoFrota>}
     */
    private function dadosCalcaoForm(?Empresa $empresa): array
    {
        $vazio = [null, [], collect()];

        if (!Schema::hasTable('configuracoes_calcao') || !Schema::hasTable('grupos_frota') || !$empresa) {
            return $vazio;
        }

        $configs = ConfiguracaoCalcao::query()
            ->where('empresa_id', $empresa->id)
            ->whereNull('modelo_id')
            ->get();

        $configEmpresa = $configs
            ->first(fn (ConfiguracaoCalcao $config) => $config->grupo_frota_id === null);

        $configsGrupo = $configs
            ->filter(fn (ConfiguracaoCalcao $config) => $config->grupo_frota_id !== null)
            ->keyBy('grupo_frota_id')
            ->all();

        $grupos = GrupoFrota::query()
            ->where('empresa_id', $empresa->id)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return [$configEmpresa, $configsGrupo, $grupos];
    }

    private function syncCalcaoConfiguracoes(Empresa $empresa, Request $request): void
    {
        if (!Schema::hasTable('configuracoes_calcao')) {
            return;
        }

        $ativoEmpresa = $request->input('calcao_ativo', 'N') === 'S';

        if (!$ativoEmpresa) {
            ConfiguracaoCalcao::query()
                ->where('empresa_id', $empresa->id)
                ->update(['ativo' => false, 'updated_at' => now()]);
            return;
        }

        $this->upsertCalcaoConfig($empresa->id, null, [
            'tipo_regra_valor' => $request->input('calcao_tipo_regra_valor', ConfiguracaoCalcao::TIPO_FIXO),
            'valor' => $request->input('calcao_valor'),
            'valor_minimo' => $request->input('calcao_valor_minimo'),
            'valor_maximo' => $request->input('calcao_valor_maximo'),
            'ativo' => true,
        ]);

        foreach ((array) $request->input('calcao_grupos', []) as $grupoId => $dadosGrupo) {
            $grupoId = (int) $grupoId;
            if ($grupoId <= 0 || !is_array($dadosGrupo)) {
                continue;
            }

            $ativo = ($dadosGrupo['ativo'] ?? 'N') === 'S';

            if (!$ativo) {
                ConfiguracaoCalcao::query()
                    ->where('empresa_id', $empresa->id)
                    ->where('grupo_frota_id', $grupoId)
                    ->whereNull('modelo_id')
                    ->update(['ativo' => false, 'updated_at' => now()]);
                continue;
            }

            $this->upsertCalcaoConfig($empresa->id, $grupoId, [
                'tipo_regra_valor' => $dadosGrupo['tipo_regra_valor'] ?? ConfiguracaoCalcao::TIPO_FIXO,
                'valor' => $dadosGrupo['valor'] ?? null,
                'valor_minimo' => $dadosGrupo['valor_minimo'] ?? null,
                'valor_maximo' => $dadosGrupo['valor_maximo'] ?? null,
                'ativo' => true,
            ]);
        }
    }

    private function upsertCalcaoConfig(int $empresaId, ?int $grupoFrotaId, array $dados): void
    {
        $config = ConfiguracaoCalcao::query()->firstOrNew([
            'empresa_id' => $empresaId,
            'grupo_frota_id' => $grupoFrotaId,
            'modelo_id' => null,
        ]);

        $config->tipo_regra_valor = $dados['tipo_regra_valor'] ?? ConfiguracaoCalcao::TIPO_FIXO;
        $config->valor = $this->decimalOrZero($dados['valor'] ?? null);
        $config->valor_minimo = $this->decimalOrNull($dados['valor_minimo'] ?? null);
        $config->valor_maximo = $this->decimalOrNull($dados['valor_maximo'] ?? null);
        $config->ativo = (bool) ($dados['ativo'] ?? true);
        $config->save();
    }

    private function decimalOrZero(mixed $value): float
    {
        $number = $this->decimalOrNull($value);

        return $number ?? 0.0;
    }

    private function decimalOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? round((float) $normalized, 2) : null;
    }
}
