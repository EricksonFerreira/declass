<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Empresa\StoreEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaRequest;
use App\Http\Requests\Empresa\UpdateEmpresaSiteApresentacaoRequest;
use App\Models\Empresa;
use App\Services\BreadcrumbService;
use App\Services\EmpresaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'cor_vantagens_fundo' => 'vantagens_fundo',
        'cor_etapas_fundo' => 'etapas_fundo',
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
        $this->middleware('can:gerenciar-empresa');

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

        return view('empresa.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota'))
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

        return view('empresa.add-edit', compact('empresa', 'breadcrumb', 'status', 'metodo', 'rota'))
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

        $file = $request->file('logo');

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return $data;
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
            $fileName = Str::uuid()->toString().'.'.$extension;
            $path = 'empresas/logos/'.$fileName;

            $stored = Storage::disk('public')->put($path, $file->getContent());
            if (! $stored) {
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

        if (! is_string($path) || $path === '') {
            return $data;
        }

        $data['logo_path'] = $path;

        if ($empresaAtual && ! empty($empresaAtual->logo_path) && Storage::disk('public')->exists($empresaAtual->logo_path)) {
            Storage::disk('public')->delete($empresaAtual->logo_path);
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

            $file = $request->file($inputName);
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            try {
                $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
                $fileName = Str::uuid()->toString().'.'.$extension;
                $path = 'empresas/site_apresentacao/'.$empresaAtual->id.'/'.$imageKey.'/'.$fileName;

                $stored = Storage::disk('public')->put($path, $file->getContent());
                if (! $stored || ! is_string($path) || $path === '') {

                }

                $oldPath = $imagens[$imageKey] ?? null;
                $imagens[$imageKey] = $path;

                if (is_string($oldPath) && $oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
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
}
