@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $siteTextos = is_array($siteTextos ?? null) ? $siteTextos : [];
        $siteImagens = is_array($siteImagens ?? null) ? $siteImagens : [];
        $siteCores = is_array($siteCores ?? null) ? $siteCores : [];
        $siteImagensConfig = is_array($siteImagensConfig ?? null) ? $siteImagensConfig : [];

        $textoFallback = [
            'site_apresentacao_botao_texto' => $empresa->site_apresentacao_botao_texto ?? '',
            'site_apresentacao_botao_url' => $empresa->site_apresentacao_botao_url ?? '',
        ];

        $texto = function (string $key, string $default = '') use ($siteTextos, $textoFallback): string {
            $fallback = $siteTextos[$key] ?? ($textoFallback[$key] ?? $default);
            $value = old($key, $fallback);
            return is_string($value) ? $value : $default;
        };

        $imagemUrl = function (string $key) use ($siteImagens): ?string {
            $value = $siteImagens[$key] ?? null;
            if (!is_string($value) || trim($value) === '') {
                return null;
            }

            return \Illuminate\Support\Facades\Storage::url($value);
        };

        $imagemAtiva = function (string $key) use ($siteImagensConfig): bool {
            if (!array_key_exists($key, $siteImagensConfig)) {
                return true;
            }

            return (bool) $siteImagensConfig[$key];
        };

        $modeloAtual = old('site_apresentacao_modelo', $empresa->site_apresentacao_modelo ?: 'modelo_1');

        $modelCards = [
            [
                'valor' => 'modelo_1',
                'titulo' => 'Modelo 1 - Premium Locadora',
                'status' => 'Disponivel',
                'descricao' => 'Layout completo com hero, formulario de reserva e secoes configuraveis.',
            ],
            [
                'valor' => 'modelo_2',
                'titulo' => 'Modelo 2 - Compacto',
                'status' => 'Em breve',
                'descricao' => 'Variacao focada em conversao rapida para campanhas de midia.',
            ],
            [
                'valor' => 'modelo_3',
                'titulo' => 'Modelo 3 - Corporativo',
                'status' => 'Em breve',
                'descricao' => 'Layout institucional para operacoes B2B com blocos corporativos.',
            ],
        ];

        $textGroups = [
            [
                'title' => 'Menu e botoes',
                'fields' => [
                    ['name' => 'menu_item_frota', 'label' => 'Menu: Frota', 'type' => 'text', 'max' => 50],
                    ['name' => 'menu_item_vantagens', 'label' => 'Menu: Vantagens', 'type' => 'text', 'max' => 50],
                    ['name' => 'menu_item_etapas', 'label' => 'Menu: Etapas', 'type' => 'text', 'max' => 50],
                    ['name' => 'menu_item_contato', 'label' => 'Menu: Contato', 'type' => 'text', 'max' => 50],
                    ['name' => 'site_apresentacao_botao_texto', 'label' => 'Texto do botao Reservar', 'type' => 'text', 'max' => 60],
                    ['name' => 'site_apresentacao_botao_url', 'label' => 'URL customizada do botao (opcional)', 'type' => 'text', 'max' => 255],
                    ['name' => 'botao_admin_texto', 'label' => 'Texto botao administrativa', 'type' => 'text', 'max' => 80],
                    ['name' => 'footer_texto', 'label' => 'Texto do rodape', 'type' => 'text', 'max' => 200],
                ],
            ],
            [
                'title' => 'Hero principal',
                'fields' => [
                    ['name' => 'hero_tag', 'label' => 'Tag superior', 'type' => 'text', 'max' => 120],
                    ['name' => 'hero_titulo', 'label' => 'Titulo hero', 'type' => 'text', 'max' => 180],
                    ['name' => 'hero_subtitulo', 'label' => 'Subtitulo hero', 'type' => 'text', 'max' => 220],
                    ['name' => 'hero_descricao', 'label' => 'Descricao hero', 'type' => 'textarea', 'max' => 2000],
                ],
            ],
            [
                'title' => 'Formulario da hero',
                'fields' => [
                    ['name' => 'hero_form_titulo', 'label' => 'Titulo formulario', 'type' => 'text', 'max' => 120],
                    ['name' => 'hero_form_descricao', 'label' => 'Descricao formulario', 'type' => 'textarea', 'max' => 500],
                    ['name' => 'hero_form_label_retirada', 'label' => 'Label local retirada', 'type' => 'text', 'max' => 80],
                    ['name' => 'hero_form_label_data_retirada', 'label' => 'Label data retirada', 'type' => 'text', 'max' => 80],
                    ['name' => 'hero_form_label_hora_retirada', 'label' => 'Label hora retirada', 'type' => 'text', 'max' => 80],
                    ['name' => 'hero_form_label_data_devolucao', 'label' => 'Label data devolucao', 'type' => 'text', 'max' => 80],
                    ['name' => 'hero_form_label_hora_devolucao', 'label' => 'Label hora devolucao', 'type' => 'text', 'max' => 80],
                    ['name' => 'hero_form_texto_botao', 'label' => 'Texto botao formulario', 'type' => 'text', 'max' => 80],
                    ['name' => 'hero_form_nota', 'label' => 'Nota formulario', 'type' => 'textarea', 'max' => 280],
                ],
            ],
            [
                'title' => 'Secao frota',
                'fields' => [
                    ['name' => 'frota_label_superior', 'label' => 'Label superior frota', 'type' => 'text', 'max' => 60],
                    ['name' => 'frota_titulo', 'label' => 'Titulo frota', 'type' => 'text', 'max' => 160],
                    ['name' => 'frota_descricao', 'label' => 'Descricao frota', 'type' => 'textarea', 'max' => 1000],
                    ['name' => 'frota_card_1_titulo', 'label' => 'Card 1 titulo', 'type' => 'text', 'max' => 120],
                    ['name' => 'frota_card_1_descricao', 'label' => 'Card 1 descricao', 'type' => 'textarea', 'max' => 400],
                    ['name' => 'frota_card_2_titulo', 'label' => 'Card 2 titulo', 'type' => 'text', 'max' => 120],
                    ['name' => 'frota_card_2_descricao', 'label' => 'Card 2 descricao', 'type' => 'textarea', 'max' => 400],
                    ['name' => 'frota_card_3_titulo', 'label' => 'Card 3 titulo', 'type' => 'text', 'max' => 120],
                    ['name' => 'frota_card_3_descricao', 'label' => 'Card 3 descricao', 'type' => 'textarea', 'max' => 400],
                ],
            ],
            [
                'title' => 'Secao vantagens e etapas',
                'fields' => [
                    ['name' => 'vantagens_label_superior', 'label' => 'Label superior vantagens', 'type' => 'text', 'max' => 60],
                    ['name' => 'vantagens_titulo', 'label' => 'Titulo vantagens', 'type' => 'text', 'max' => 160],
                    ['name' => 'vantagens_descricao', 'label' => 'Descricao vantagens', 'type' => 'textarea', 'max' => 1000],
                    ['name' => 'beneficio_1', 'label' => 'Beneficio 1', 'type' => 'text', 'max' => 220],
                    ['name' => 'beneficio_2', 'label' => 'Beneficio 2', 'type' => 'text', 'max' => 220],
                    ['name' => 'beneficio_3', 'label' => 'Beneficio 3', 'type' => 'text', 'max' => 220],
                    ['name' => 'beneficio_4', 'label' => 'Beneficio 4', 'type' => 'text', 'max' => 220],
                    ['name' => 'etapas_label_superior', 'label' => 'Label superior etapas', 'type' => 'text', 'max' => 60],
                    ['name' => 'etapas_titulo', 'label' => 'Titulo etapas', 'type' => 'text', 'max' => 160],
                    ['name' => 'etapa_1_titulo', 'label' => 'Etapa 1 titulo', 'type' => 'text', 'max' => 120],
                    ['name' => 'etapa_1_descricao', 'label' => 'Etapa 1 descricao', 'type' => 'textarea', 'max' => 300],
                    ['name' => 'etapa_2_titulo', 'label' => 'Etapa 2 titulo', 'type' => 'text', 'max' => 120],
                    ['name' => 'etapa_2_descricao', 'label' => 'Etapa 2 descricao', 'type' => 'textarea', 'max' => 300],
                    ['name' => 'etapa_3_titulo', 'label' => 'Etapa 3 titulo', 'type' => 'text', 'max' => 120],
                    ['name' => 'etapa_3_descricao', 'label' => 'Etapa 3 descricao', 'type' => 'textarea', 'max' => 300],
                    ['name' => 'contato_label_superior', 'label' => 'Label superior contato', 'type' => 'text', 'max' => 60],
                    ['name' => 'contato_titulo', 'label' => 'Titulo contato', 'type' => 'text', 'max' => 160],
                ],
            ],
        ];
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger col-12">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-12 px-0">
        <div class="card card-site shadow-sm border-0">
            <div class="card-header card-header-site border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Defina o modelo do site, textos, cores e imagens por empresa.</p>
                </div>
                @if(!empty($empresa->dominio))
                    <span class="domain-chip">Dominio: {{ $empresa->dominio }}</span>
                @endif
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ $rota }}" enctype="multipart/form-data" id="siteWizardForm">
                    @csrf
                    @method($metodo)

                    <div class="wizard-nav" role="tablist" aria-label="Configuracao do site">
                        <button type="button" class="wizard-nav-item active" data-step-target="1">1. Geral</button>
                        <button type="button" class="wizard-nav-item" data-step-target="2">2. Textos</button>
                        <button type="button" class="wizard-nav-item" data-step-target="3">3. Cores</button>
                        <button type="button" class="wizard-nav-item" data-step-target="4">4. Imagens</button>
                        <button type="button" class="wizard-nav-item" data-step-target="5">5. Requisitos</button>
                    </div>

                    <section class="wizard-step active" data-step="1">
                        <div class="wizard-step-head">
                            <h5>Informacoes principais</h5>
                            <p>Defina dominio, status da pagina e modelo visual da apresentacao.</p>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <label for="dominio">Dominio da empresa</label>
                                <input type="text" class="form-control form-control-sm" id="dominio" name="dominio" value="{{ old('dominio', $empresa->dominio ?? '') }}" placeholder="empresa.com.br">
                            </div>
                            <div class="col-lg-4">
                                <label for="site_apresentacao_ativo">Site de apresentacao ativo</label>
                                <select class="form-control form-control-sm" id="site_apresentacao_ativo" name="site_apresentacao_ativo">
                                    @php
                                        $statusAtual = old('site_apresentacao_ativo', $empresa->site_apresentacao_ativo ?: 'S');
                                    @endphp
                                    @foreach($status as $value => $label)
                                        <option value="{{ $value }}" {{ $statusAtual === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label for="site_apresentacao_modelo">Modelo do site</label>
                                <select class="form-control form-control-sm" id="site_apresentacao_modelo" name="site_apresentacao_modelo">
                                    @foreach($modelosSite as $modelo)
                                        <option value="{{ $modelo['value'] }}" {{ $modeloAtual === $modelo['value'] ? 'selected' : '' }} {{ !$modelo['enabled'] ? 'disabled' : '' }}>
                                            {{ $modelo['label'] }}{{ !$modelo['enabled'] ? ' (em breve)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="site_apresentacao_titulo">Titulo base (fallback)</label>
                                <input type="text" class="form-control form-control-sm" id="site_apresentacao_titulo" name="site_apresentacao_titulo" maxlength="120" value="{{ old('site_apresentacao_titulo', $empresa->site_apresentacao_titulo ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="site_apresentacao_subtitulo">Subtitulo base (fallback)</label>
                                <input type="text" class="form-control form-control-sm" id="site_apresentacao_subtitulo" name="site_apresentacao_subtitulo" maxlength="180" value="{{ old('site_apresentacao_subtitulo', $empresa->site_apresentacao_subtitulo ?? '') }}">
                            </div>
                            <div class="col-12 mt-2">
                                <label for="site_apresentacao_descricao">Descricao base (fallback)</label>
                                <textarea class="form-control form-control-sm" id="site_apresentacao_descricao" name="site_apresentacao_descricao" rows="3" maxlength="2000">{{ old('site_apresentacao_descricao', $empresa->site_apresentacao_descricao ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="model-grid mt-3">
                            @foreach($modelCards as $modelCard)
                                <article class="model-card {{ $modeloAtual === $modelCard['valor'] ? 'active' : '' }}" data-model-card="{{ $modelCard['valor'] }}">
                                    <div class="model-status">{{ $modelCard['status'] }}</div>
                                    <h6>{{ $modelCard['titulo'] }}</h6>
                                    <p>{{ $modelCard['descricao'] }}</p>
                                </article>
                            @endforeach
                        </div>

                        <div class="row mt-3 align-items-end">
                            <div class="col-lg-5">
                                <label for="logo">Logo da empresa</label>
                                <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                                <small class="text-muted d-block mt-1">Formatos: JPG, JPEG, PNG, WEBP ou SVG (max 2MB).</small>
                            </div>
                            <div class="col-lg-7">
                                <div class="logo-preview">
                                    @if(!empty($empresa->logo_url))
                                        <img src="{{ $empresa->logo_url }}" alt="Logo atual da empresa">
                                    @else
                                        <span>Sem logo cadastrada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="wizard-step" data-step="2">
                        <div class="wizard-step-head">
                            <h5>Textos customizaveis por empresa</h5>
                            <p>Todos os textos da pagina publica podem ser ajustados para cada empresa.</p>
                        </div>

                        @foreach($textGroups as $group)
                            <div class="wizard-block mb-3">
                                <h6>{{ $group['title'] }}</h6>
                                <div class="row">
                                    @foreach($group['fields'] as $field)
                                        @php
                                            $isTextarea = $field['type'] === 'textarea';
                                        @endphp
                                        <div class="{{ $isTextarea ? 'col-12' : 'col-md-6' }} mt-2">
                                            <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                            @if($isTextarea)
                                                <textarea class="form-control form-control-sm" id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3" maxlength="{{ $field['max'] }}">{{ $texto($field['name']) }}</textarea>
                                            @else
                                                <input type="text" class="form-control form-control-sm" id="{{ $field['name'] }}" name="{{ $field['name'] }}" maxlength="{{ $field['max'] }}" value="{{ $texto($field['name']) }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </section>
                    <section class="wizard-step" data-step="3">
                        @php
                            $colorGroups = [
                                [
                                    'title' => 'Identidade principal da empresa',
                                    'items' => [
                                        ['name' => 'cor_primaria', 'label' => 'Cor primaria', 'default' => $empresa->cor_primaria ?: '#1F2937'],
                                        ['name' => 'cor_secundaria', 'label' => 'Cor secundaria', 'default' => $empresa->cor_secundaria ?: '#334155'],
                                        ['name' => 'cor_terciaria', 'label' => 'Cor terciaria', 'default' => $empresa->cor_terciaria ?: '#64748B'],
                                    ],
                                ],
                                [
                                    'title' => 'Topo, hero e formulario',
                                    'items' => [
                                        ['name' => 'cor_topbar_fundo', 'label' => 'Topo: fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_topbar_texto', 'label' => 'Topo: texto', 'default' => '#0F172A'],
                                        ['name' => 'cor_topbar_link_hover', 'label' => 'Topo: hover menu', 'default' => '#1F2937'],
                                        ['name' => 'cor_hero_fundo', 'label' => 'Hero: fundo', 'default' => '#1F2937'],
                                        ['name' => 'cor_hero_texto', 'label' => 'Hero: texto', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_form_fundo', 'label' => 'Formulario: fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_form_texto', 'label' => 'Formulario: texto', 'default' => '#0F172A'],
                                    ],
                                ],
                                [
                                    'title' => 'Secoes da pagina',
                                    'items' => [
                                        ['name' => 'cor_frota_fundo', 'label' => 'Secao frota', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_vantagens_fundo', 'label' => 'Secao vantagens', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_etapas_fundo', 'label' => 'Secao etapas', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_contato_fundo', 'label' => 'Secao contato', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title' => 'Botoes e rodape',
                                    'items' => [
                                        ['name' => 'cor_botao_primario_fundo', 'label' => 'Botao primario: fundo', 'default' => '#E7B93A'],
                                        ['name' => 'cor_botao_primario_texto', 'label' => 'Botao primario: texto', 'default' => '#111827'],
                                        ['name' => 'cor_botao_secundario_fundo', 'label' => 'Botao secundario: fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_botao_secundario_texto', 'label' => 'Botao secundario: texto', 'default' => '#1F2937'],
                                        ['name' => 'cor_footer_fundo', 'label' => 'Rodape: fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_footer_texto', 'label' => 'Rodape: texto', 'default' => '#64748B'],
                                    ],
                                ],
                                [
                                    'title' => 'Tela administrativa por dominio',
                                    'items' => [
                                        ['name' => 'cor_admin_fundo', 'label' => 'Administrativa: fundo', 'default' => '#193C68'],
                                        ['name' => 'cor_admin_texto', 'label' => 'Administrativa: texto', 'default' => '#FFFFFF'],
                                    ],
                                ],
                            ];
                        @endphp

                        <div class="wizard-step-head">
                            <h5>Cores por parte do site</h5>
                            <p>Escolha as cores de fundo e de fonte para cada bloco da apresentacao.</p>
                        </div>

                        @foreach($colorGroups as $group)
                            <div class="wizard-block mb-3">
                                <h6>{{ $group['title'] }}</h6>
                                <div class="row">
                                    @foreach($group['items'] as $item)
                                        @php
                                            $colorValue = old($item['name'], str_starts_with($item['name'], 'cor_') && !in_array($item['name'], ['cor_primaria', 'cor_secundaria', 'cor_terciaria'], true)
                                                ? ($siteCores[str_replace('cor_', '', $item['name'])] ?? $item['default'])
                                                : $item['default']);
                                        @endphp
                                        <div class="col-md-4 mt-2">
                                            <label for="{{ $item['name'] }}">{{ $item['label'] }}</label>
                                            <div class="color-field">
                                                <input type="color" class="form-control form-control-sm p-1" id="{{ $item['name'] }}" name="{{ $item['name'] }}" value="{{ $colorValue }}">
                                                <input type="text" class="form-control form-control-sm" data-color-text="{{ $item['name'] }}" value="{{ $colorValue }}" maxlength="7">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="row mt-3">
                            <div class="col-lg-6">
                                <div class="preview-box" id="previewSite">
                                    <div class="preview-title">Preview rapido do site</div>
                                    <div class="preview-desc">Usa cor primaria, secundaria e terciaria da empresa.</div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="preview-box" id="previewAdmin">
                                    <div class="preview-title">Preview tela administrativa</div>
                                    <div class="preview-desc">Usa as cores da pagina /administrativa.</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="wizard-step" data-step="4">
                        @php
                            $imageFields = [
                                ['key' => 'hero', 'upload' => 'imagem_hero', 'toggle' => 'usar_imagem_hero', 'label' => 'Imagem Hero principal'],
                                ['key' => 'frota_1', 'upload' => 'imagem_frota_1', 'toggle' => 'usar_imagem_frota_1', 'label' => 'Imagem Frota card 1'],
                                ['key' => 'frota_2', 'upload' => 'imagem_frota_2', 'toggle' => 'usar_imagem_frota_2', 'label' => 'Imagem Frota card 2'],
                                ['key' => 'frota_3', 'upload' => 'imagem_frota_3', 'toggle' => 'usar_imagem_frota_3', 'label' => 'Imagem Frota card 3'],
                                ['key' => 'vantagens', 'upload' => 'imagem_vantagens', 'toggle' => 'usar_imagem_vantagens', 'label' => 'Imagem secao Vantagens'],
                                ['key' => 'contato', 'upload' => 'imagem_contato', 'toggle' => 'usar_imagem_contato', 'label' => 'Imagem secao Contato'],
                            ];
                        @endphp

                        <div class="wizard-step-head">
                            <h5>Imagens por bloco com controle de uso</h5>
                            <p>Defina se cada imagem sera usada e envie arquivos com tamanho minimo conforme a tabela.</p>
                        </div>

                        <div class="row">
                            @foreach($imageFields as $field)
                                @php
                                    $urlAtual = $imagemUrl($field['key']);
                                    $ativa = (bool) old($field['toggle'], $imagemAtiva($field['key']));
                                @endphp
                                <div class="col-lg-6 mt-2">
                                    <div class="wizard-block image-block">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                            <h6 class="mb-0">{{ $field['label'] }}</h6>
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="{{ $field['toggle'] }}" value="0">
                                                <input
                                                    type="checkbox"
                                                    class="custom-control-input"
                                                    id="{{ $field['toggle'] }}"
                                                    name="{{ $field['toggle'] }}"
                                                    value="1"
                                                    {{ $ativa ? 'checked' : '' }}
                                                >
                                                <label class="custom-control-label" for="{{ $field['toggle'] }}">Usar imagem</label>
                                            </div>
                                        </div>

                                        <input
                                            type="file"
                                            class="form-control form-control-sm"
                                            id="{{ $field['upload'] }}"
                                            name="{{ $field['upload'] }}"
                                            accept=".jpg,.jpeg,.png,.webp"
                                        >

                                        <small class="text-muted d-block mt-1">Formato: JPG, JPEG, PNG ou WEBP (max 4MB).</small>

                                        <div class="image-preview mt-2">
                                            @if(!empty($urlAtual))
                                                <img src="{{ $urlAtual }}" alt="{{ $field['label'] }} atual">
                                            @else
                                                <span>Nenhuma imagem enviada.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="wizard-step" data-step="5">
                        <div class="wizard-step-head">
                            <h5>Tabela de requisitos de imagem por modelo</h5>
                            <p>Consulte os tamanhos minimos antes de enviar as imagens para cada area do template.</p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-requirements">
                                <thead>
                                    <tr>
                                        <th>Modelo</th>
                                        <th>Status</th>
                                        <th>Area</th>
                                        <th>Campo</th>
                                        <th>Tamanho minimo</th>
                                        <th>Opcional</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($imageRequirements as $modeloReq)
                                        @if(empty($modeloReq['locais']))
                                            <tr>
                                                <td>{{ $modeloReq['nome'] }}</td>
                                                <td>{{ $modeloReq['status'] }}</td>
                                                <td colspan="4">Sem requisitos definidos no momento.</td>
                                            </tr>
                                        @else
                                            @foreach($modeloReq['locais'] as $index => $local)
                                                <tr>
                                                    @if($index === 0)
                                                        <td rowspan="{{ count($modeloReq['locais']) }}">{{ $modeloReq['nome'] }}</td>
                                                        <td rowspan="{{ count($modeloReq['locais']) }}">{{ $modeloReq['status'] }}</td>
                                                    @endif
                                                    <td>{{ $local['area'] }}</td>
                                                    <td><code>{{ $local['campo'] }}</code></td>
                                                    <td>{{ $local['minimo'] }}</td>
                                                    <td>{{ $local['opcional'] ? 'Sim' : 'Nao' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="wizard-block mt-3 mb-0">
                            <h6>Tela administrativa por dominio</h6>
                            <p class="mb-0 text-muted">
                                Quando o usuario acessar <code>/administrativa</code> pelo dominio da empresa, sera exibida a logo da empresa e as cores definidas em
                                <strong>Administrativa: fundo</strong> e <strong>Administrativa: texto</strong>.
                            </p>
                        </div>
                    </section>

                    <div class="wizard-actions">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="wizardPrev">Voltar</button>
                        <div class="wizard-actions-right">
                            <button type="button" class="btn btn-primary btn-sm" id="wizardNext">Continuar</button>
                            <button type="submit" class="btn btn-success btn-sm" id="wizardSave">Salvar configuracoes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-site {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-site {
        background: linear-gradient(120deg, #f7fbff 0%, #eef5ff 65%, #f8fcff 100%);
        padding: 1rem 1.4rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .card-header-site h4 {
        margin: 0;
        color: #0f2740;
        font-weight: 700;
    }

    .domain-chip {
        border: 1px solid #d5e3f4;
        border-radius: 999px;
        padding: 0.35rem 0.7rem;
        font-size: 0.78rem;
        color: #1e3a5f;
        background: #ffffff;
    }

    .wizard-nav {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }

    .wizard-nav-item {
        border: 1px solid #dce7f3;
        border-radius: 10px;
        min-height: 38px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        background: #f8fbff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .wizard-nav-item.active {
        border-color: #2563eb;
        background: #ffffff;
        color: #1d4ed8;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.08);
    }

    .wizard-step {
        display: none;
    }

    .wizard-step.active {
        display: block;
    }

    .wizard-step-head h5 {
        margin-bottom: 4px;
        color: #0f2740;
        font-weight: 700;
    }

    .wizard-step-head p {
        margin-bottom: 12px;
        color: #64748b;
        font-size: 0.86rem;
    }

    .wizard-block {
        border: 1px solid #e2ebf5;
        border-radius: 12px;
        padding: 12px;
        background: #ffffff;
    }

    .wizard-block h6 {
        margin-bottom: 6px;
        color: #1e3a5f;
        font-weight: 700;
    }

    .wizard-block label,
    .wizard-step label {
        font-size: 0.78rem;
        color: #334155;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .wizard-step .form-control,
    .wizard-step .form-select {
        border-radius: 9px;
        border-color: #d8e1ec;
        min-height: 36px;
    }

    .wizard-step textarea.form-control {
        min-height: 90px;
    }

    .model-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .model-card {
        border: 1px solid #dbe6f2;
        border-radius: 10px;
        padding: 12px;
        background: #f8fbff;
        min-height: 130px;
    }

    .model-card.active {
        border-color: #2563eb;
        background: #eff6ff;
    }

    .model-card h6 {
        margin: 4px 0 6px;
        color: #0f2740;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .model-card p {
        margin: 0;
        color: #475569;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .model-status {
        display: inline-flex;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-weight: 700;
        font-size: 0.68rem;
        padding: 0.18rem 0.52rem;
    }

    .logo-preview {
        min-height: 110px;
        border: 1px dashed #d7e2ef;
        border-radius: 10px;
        background: #f8fbff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .logo-preview img {
        max-height: 90px;
        max-width: 100%;
        object-fit: contain;
    }

    .logo-preview span {
        font-size: 0.82rem;
        color: #64748b;
    }

    .color-field {
        display: grid;
        grid-template-columns: 46px 1fr;
        gap: 7px;
    }

    .image-block .image-preview {
        min-height: 120px;
        border: 1px dashed #d7e2ef;
        border-radius: 10px;
        background: #f8fbff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
    }

    .image-block .image-preview img {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
        border-radius: 8px;
    }

    .image-block .image-preview span {
        font-size: 0.8rem;
        color: #64748b;
    }

    .preview-box {
        min-height: 120px;
        border-radius: 12px;
        border: 1px solid #d8e1ec;
        padding: 12px;
    }

    .preview-title {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }

    .preview-desc {
        font-size: 0.8rem;
        opacity: 0.86;
    }

    .table-requirements th,
    .table-requirements td {
        font-size: 0.77rem;
        vertical-align: middle;
    }

    .wizard-actions {
        margin-top: 16px;
        border-top: 1px solid #e4edf7;
        padding-top: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .wizard-actions-right {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    @media (max-width: 992px) {
        .wizard-nav {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .model-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .wizard-actions,
        .wizard-actions-right {
            width: 100%;
        }

        .wizard-actions-right .btn,
        .wizard-actions > .btn {
            width: 100%;
        }
    }
</style>
@stop

@section('js')
<script>
    (function () {
        const stepButtons = Array.from(document.querySelectorAll('[data-step-target]'));
        const stepPanels = Array.from(document.querySelectorAll('[data-step]'));
        const prevBtn = document.getElementById('wizardPrev');
        const nextBtn = document.getElementById('wizardNext');
        const saveBtn = document.getElementById('wizardSave');
        const modelSelect = document.getElementById('site_apresentacao_modelo');
        const modelCards = Array.from(document.querySelectorAll('[data-model-card]'));
        let current = 1;
        const lastStep = stepPanels.length;

        function showStep(step) {
            current = Math.min(Math.max(step, 1), lastStep);

            stepPanels.forEach((panel) => {
                panel.classList.toggle('active', Number(panel.dataset.step) === current);
            });

            stepButtons.forEach((button) => {
                button.classList.toggle('active', Number(button.dataset.stepTarget) === current);
            });

            prevBtn.style.visibility = current === 1 ? 'hidden' : 'visible';
            nextBtn.style.display = current === lastStep ? 'none' : 'inline-flex';
            saveBtn.style.display = current === lastStep ? 'inline-flex' : 'none';
        }

        function syncModelCards() {
            if (!modelSelect) {
                return;
            }

            const model = modelSelect.value;
            modelCards.forEach((card) => {
                card.classList.toggle('active', card.dataset.modelCard === model);
            });
        }

        function colorInputSync() {
            const colorInputs = Array.from(document.querySelectorAll('input[type="color"]'));
            colorInputs.forEach((input) => {
                const text = document.querySelector(`[data-color-text="${input.id}"]`);
                if (!text) {
                    return;
                }

                const syncFromColor = () => {
                    text.value = input.value.toUpperCase();
                    updatePreviewBoxes();
                };

                const syncFromText = () => {
                    const value = (text.value || '').trim().toUpperCase();
                    if (/^#[A-F0-9]{6}$/.test(value)) {
                        input.value = value;
                        updatePreviewBoxes();
                    }
                };

                input.addEventListener('input', syncFromColor);
                text.addEventListener('change', syncFromText);
            });
        }

        function updatePreviewBoxes() {
            const c1 = document.getElementById('cor_primaria')?.value || '#1F2937';
            const c2 = document.getElementById('cor_secundaria')?.value || '#334155';
            const c3 = document.getElementById('cor_terciaria')?.value || '#64748B';
            const adminBg = document.getElementById('cor_admin_fundo')?.value || '#193C68';
            const adminText = document.getElementById('cor_admin_texto')?.value || '#FFFFFF';

            const sitePreview = document.getElementById('previewSite');
            if (sitePreview) {
                sitePreview.style.background = `linear-gradient(135deg, ${c1} 0%, ${c2} 58%, ${c3} 100%)`;
                sitePreview.style.color = '#FFFFFF';
                sitePreview.style.borderColor = c1;
            }

            const adminPreview = document.getElementById('previewAdmin');
            if (adminPreview) {
                adminPreview.style.background = adminBg;
                adminPreview.style.color = adminText;
                adminPreview.style.borderColor = adminBg;
            }
        }

        stepButtons.forEach((button) => {
            button.addEventListener('click', () => {
                showStep(Number(button.dataset.stepTarget || 1));
            });
        });

        prevBtn.addEventListener('click', () => {
            showStep(current - 1);
        });

        nextBtn.addEventListener('click', () => {
            showStep(current + 1);
        });

        if (modelSelect) {
            modelSelect.addEventListener('change', syncModelCards);
        }

        syncModelCards();
        colorInputSync();
        updatePreviewBoxes();
        showStep(1);
    })();
</script>
@stop
