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
            'site_apresentacao_botao_url'   => $empresa->site_apresentacao_botao_url ?? '',
        ];

        $texto = function (string $key, string $default = '') use ($siteTextos, $textoFallback): string {
            $fallback = $siteTextos[$key] ?? ($textoFallback[$key] ?? $default);
            $value    = old($key, $fallback);
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
                'valor'    => 'modelo_1',
                'titulo'   => 'Modelo 1 — Premium Locadora',
                'status'   => 'Disponivel',
                'icon'     => 'fas fa-star',
                'descricao' => 'Layout completo com banner, formulario de reserva e secoes configuráveis.',
            ],
            [
                'valor'    => 'modelo_2',
                'titulo'   => 'Modelo 2 — Compacto',
                'status'   => 'Em breve',
                'icon'     => 'fas fa-bolt',
                'descricao' => 'Variacao focada em conversao rapida para campanhas de midia.',
            ],
            [
                'valor'    => 'modelo_3',
                'titulo'   => 'Modelo 3 — Corporativo',
                'status'   => 'Em breve',
                'icon'     => 'fas fa-building',
                'descricao' => 'Layout institucional para operacoes B2B com blocos corporativos.',
            ],
        ];

        $textGroups = [
            [
                'title'    => 'Menu de Navegacao e Botoes',
                'icon'     => 'fas fa-bars',
                'descricao' => 'Nomes dos itens do menu, botao de reserva e texto do rodape.',
                'fields'   => [
                    ['name' => 'menu_item_frota',              'label' => 'Item de menu: Frota',                  'hint' => 'Ex: Nossa Frota',                                      'type' => 'text',     'max' => 50],
                    ['name' => 'menu_item_vantagens',          'label' => 'Item de menu: Vantagens',              'hint' => 'Ex: Por que nos escolher',                             'type' => 'text',     'max' => 50],
                    ['name' => 'menu_item_etapas',             'label' => 'Item de menu: Como funciona',          'hint' => 'Ex: Como funciona',                                    'type' => 'text',     'max' => 50],
                    ['name' => 'menu_item_contato',            'label' => 'Item de menu: Contato',                'hint' => 'Ex: Fale conosco',                                     'type' => 'text',     'max' => 50],
                    ['name' => 'site_apresentacao_botao_texto','label' => 'Texto do botao de reserva',            'hint' => 'Ex: Reserve agora',                                    'type' => 'text',     'max' => 60],
                    ['name' => 'site_apresentacao_botao_url',  'label' => 'Link do botao de reserva (opcional)',  'hint' => 'Deixe em branco para usar o sistema de reservas',      'type' => 'text',     'max' => 255],
                    ['name' => 'botao_admin_texto',            'label' => 'Botao da area administrativa',         'hint' => 'Ex: Area do Cliente',                                  'type' => 'text',     'max' => 80],
                    ['name' => 'footer_texto',                 'label' => 'Texto do rodape',                      'hint' => 'Ex: © 2025 Locadora. Todos os direitos reservados.',   'type' => 'text',     'max' => 200],
                ],
            ],
            [
                'title'    => 'Secao Principal (Banner)',
                'icon'     => 'fas fa-image',
                'descricao' => 'Textos do grande banner que aparece no topo da pagina.',
                'fields'   => [
                    ['name' => 'hero_tag',        'label' => 'Etiqueta em destaque',     'hint' => 'Pequeno texto colorido acima do titulo. Ex: Aluguel de carros',         'type' => 'text',     'max' => 120],
                    ['name' => 'hero_titulo',     'label' => 'Titulo principal',          'hint' => 'Ex: Alugue o carro ideal para sua viagem',                             'type' => 'text',     'max' => 180],
                    ['name' => 'hero_subtitulo',  'label' => 'Subtitulo',                 'hint' => 'Ex: Frota completa, precos acessiveis e atendimento rapido',           'type' => 'text',     'max' => 220],
                    ['name' => 'hero_descricao',  'label' => 'Descricao complementar',   'hint' => 'Texto adicional abaixo do subtitulo (pode deixar em branco)',           'type' => 'textarea', 'max' => 2000],
                ],
            ],
            [
                'title'    => 'Formulario de Reserva',
                'icon'     => 'fas fa-calendar-alt',
                'descricao' => 'Textos exibidos dentro do formulario de reserva no banner principal.',
                'fields'   => [
                    ['name' => 'hero_form_titulo',              'label' => 'Titulo do formulario',                'hint' => 'Ex: Faca sua reserva',                                 'type' => 'text',     'max' => 120],
                    ['name' => 'hero_form_descricao',           'label' => 'Descricao do formulario',            'hint' => 'Texto explicativo acima dos campos de data',           'type' => 'textarea', 'max' => 500],
                    ['name' => 'hero_form_label_retirada',      'label' => 'Campo: Local de retirada',           'hint' => 'Ex: Onde voce vai retirar o carro?',                   'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_devolucao',     'label' => 'Campo: Local de devolucao',          'hint' => 'Ex: Onde voce vai devolver o carro?',                  'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_data_retirada', 'label' => 'Campo: Data de retirada',            'hint' => 'Ex: Data de retirada',                                 'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_hora_retirada', 'label' => 'Campo: Hora de retirada',            'hint' => 'Ex: Hora de retirada',                                 'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_data_devolucao','label' => 'Campo: Data de devolucao',           'hint' => 'Ex: Data de devolucao',                                'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_hora_devolucao','label' => 'Campo: Hora de devolucao',           'hint' => 'Ex: Hora de devolucao',                                'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_texto_botao',         'label' => 'Texto do botao do formulario',       'hint' => 'Ex: Ver disponibilidade',                              'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_nota',                'label' => 'Nota abaixo do formulario',          'hint' => 'Ex: Reserva sem cartao de credito',                    'type' => 'textarea', 'max' => 280],
                ],
            ],
            [
                'title'    => 'Secao Frota (Nossos Veiculos)',
                'icon'     => 'fas fa-car',
                'descricao' => 'Textos da secao que apresenta os veiculos disponiveis.',
                'fields'   => [
                    ['name' => 'frota_label_superior',   'label' => 'Etiqueta acima do titulo',  'hint' => 'Ex: Nossos veiculos',                                           'type' => 'text',     'max' => 60],
                    ['name' => 'frota_titulo',           'label' => 'Titulo da secao',            'hint' => 'Ex: Escolha o carro ideal',                                    'type' => 'text',     'max' => 160],
                    ['name' => 'frota_descricao',        'label' => 'Descricao da secao',         'hint' => 'Texto introdutorio sobre a frota',                             'type' => 'textarea', 'max' => 1000],
                    ['name' => 'frota_card_1_titulo',    'label' => 'Card 1 — Titulo',            'hint' => 'Ex: Hatch Economico',                                          'type' => 'text',     'max' => 120],
                    ['name' => 'frota_card_1_descricao', 'label' => 'Card 1 — Descricao',         'hint' => 'Ex: Ideal para a cidade, economico e confortavel',             'type' => 'textarea', 'max' => 400],
                    ['name' => 'frota_card_2_titulo',    'label' => 'Card 2 — Titulo',            'hint' => 'Ex: Sedan Executivo',                                          'type' => 'text',     'max' => 120],
                    ['name' => 'frota_card_2_descricao', 'label' => 'Card 2 — Descricao',         'hint' => 'Ex: Conforto e elegancia para viagens longas',                 'type' => 'textarea', 'max' => 400],
                    ['name' => 'frota_card_3_titulo',    'label' => 'Card 3 — Titulo',            'hint' => 'Ex: SUV Familiar',                                             'type' => 'text',     'max' => 120],
                    ['name' => 'frota_card_3_descricao', 'label' => 'Card 3 — Descricao',         'hint' => 'Ex: Espaco e seguranca para toda a familia',                   'type' => 'textarea', 'max' => 400],
                ],
            ],
            [
                'title'    => 'Secao Vantagens',
                'icon'     => 'fas fa-thumbs-up',
                'descricao' => 'Textos que destacam os diferenciais da empresa.',
                'fields'   => [
                    ['name' => 'vantagens_label_superior', 'label' => 'Etiqueta acima do titulo', 'hint' => 'Ex: Por que nos escolher',                                      'type' => 'text',     'max' => 60],
                    ['name' => 'vantagens_titulo',         'label' => 'Titulo da secao',           'hint' => 'Ex: Nossas vantagens',                                         'type' => 'text',     'max' => 160],
                    ['name' => 'vantagens_descricao',      'label' => 'Descricao da secao',        'hint' => 'Texto introdutorio sobre os beneficios',                       'type' => 'textarea', 'max' => 1000],
                    ['name' => 'beneficio_1',              'label' => 'Vantagem 1',                'hint' => 'Ex: Atendimento 24 horas',                                     'type' => 'text',     'max' => 220],
                    ['name' => 'beneficio_2',              'label' => 'Vantagem 2',                'hint' => 'Ex: Frota revisada e garantida',                               'type' => 'text',     'max' => 220],
                    ['name' => 'beneficio_3',              'label' => 'Vantagem 3',                'hint' => 'Ex: Sem taxa de cancelamento',                                 'type' => 'text',     'max' => 220],
                    ['name' => 'beneficio_4',              'label' => 'Vantagem 4',                'hint' => 'Ex: Cobertura em todo o Brasil',                               'type' => 'text',     'max' => 220],
                ],
            ],
            [
                'title'    => 'Secao Como Funciona (Etapas)',
                'icon'     => 'fas fa-list-ol',
                'descricao' => 'Passo a passo explicando como o cliente faz a reserva.',
                'fields'   => [
                    ['name' => 'etapas_label_superior', 'label' => 'Etiqueta acima do titulo',   'hint' => 'Ex: E muito simples',                                           'type' => 'text',     'max' => 60],
                    ['name' => 'etapas_titulo',         'label' => 'Titulo da secao',             'hint' => 'Ex: Como funciona',                                            'type' => 'text',     'max' => 160],
                    ['name' => 'etapa_1_titulo',        'label' => 'Passo 1 — Titulo',            'hint' => 'Ex: Escolha o veiculo',                                        'type' => 'text',     'max' => 120],
                    ['name' => 'etapa_1_descricao',     'label' => 'Passo 1 — Descricao',         'hint' => 'Ex: Navegue pela nossa frota e encontre o carro ideal',        'type' => 'textarea', 'max' => 300],
                    ['name' => 'etapa_2_titulo',        'label' => 'Passo 2 — Titulo',            'hint' => 'Ex: Faca a reserva',                                           'type' => 'text',     'max' => 120],
                    ['name' => 'etapa_2_descricao',     'label' => 'Passo 2 — Descricao',         'hint' => 'Ex: Preencha o formulario com as datas desejadas',             'type' => 'textarea', 'max' => 300],
                    ['name' => 'etapa_3_titulo',        'label' => 'Passo 3 — Titulo',            'hint' => 'Ex: Retire e aproveite',                                       'type' => 'text',     'max' => 120],
                    ['name' => 'etapa_3_descricao',     'label' => 'Passo 3 — Descricao',         'hint' => 'Ex: Retire o veiculo no local combinado e aproveite',          'type' => 'textarea', 'max' => 300],
                ],
            ],
            [
                'title'    => 'Secao Contato',
                'icon'     => 'fas fa-envelope',
                'descricao' => 'Textos da secao de contato ao final da pagina.',
                'fields'   => [
                    ['name' => 'contato_label_superior', 'label' => 'Etiqueta acima do titulo', 'hint' => 'Ex: Estamos aqui para ajudar',    'type' => 'text', 'max' => 60],
                    ['name' => 'contato_titulo',         'label' => 'Titulo da secao',           'hint' => 'Ex: Entre em contato',             'type' => 'text', 'max' => 160],
                ],
            ],
        ];
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger col-12 mb-3">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i>Corrija os erros abaixo antes de salvar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-12 px-0">
        <div class="config-card">

            {{-- Cabecalho --}}
            <div class="config-card-header">
                <div class="config-card-header-info">
                    <h4 class="mb-1">Configuracao do Site de Apresentacao</h4>
                    <p class="mb-0">Siga os passos abaixo para configurar o site da empresa.</p>
                </div>
                <div class="config-card-header-actions">
                    @if(!empty($empresa->dominio))
                        <span class="domain-chip"><i class="fas fa-globe mr-1"></i>{{ $empresa->dominio }}</span>
                        <a href="https://{{ $empresa->dominio }}" target="_blank" class="btn-ver-site">
                            <i class="fas fa-external-link-alt mr-1"></i>Ver site
                        </a>
                    @endif
                </div>
            </div>

            <div class="config-body">
                <form method="POST" action="{{ $rota }}" enctype="multipart/form-data" id="siteWizardForm">
                    @csrf
                    @method($metodo)

                    {{-- Indicador de passos --}}
                    <div class="step-indicator" role="tablist">
                        @php
                            $steps = [
                                ['label' => 'Dados Gerais', 'icon' => 'fas fa-cog'],
                                ['label' => 'Textos',       'icon' => 'fas fa-pen'],
                                ['label' => 'Cores',        'icon' => 'fas fa-palette'],
                                ['label' => 'Imagens',      'icon' => 'fas fa-images'],
                                ['label' => 'Publicar',     'icon' => 'fas fa-rocket'],
                            ];
                        @endphp
                        @foreach($steps as $i => $step)
                            <button type="button" class="step-btn {{ $i === 0 ? 'active' : '' }}" data-step-target="{{ $i + 1 }}">
                                <span class="step-num">{{ $i + 1 }}</span>
                                <span class="step-label-text">
                                    <i class="{{ $step['icon'] }}"></i>
                                    {{ $step['label'] }}
                                </span>
                            </button>
                            @if($i < count($steps) - 1)
                                <div class="step-divider"></div>
                            @endif
                        @endforeach
                    </div>

                    {{-- ======================================================= --}}
                    {{-- PASSO 1: Dados Gerais                                    --}}
                    {{-- ======================================================= --}}
                    <section class="wizard-step active" data-step="1">
                        <div class="step-head">
                            <h5><i class="fas fa-cog mr-2"></i>Dados Gerais</h5>
                            <p>Configure o dominio, situacao, modelo de layout, textos padrao e logo da empresa.</p>
                        </div>

                        {{-- Bloco: Publicacao --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-globe"></i>
                                <div>
                                    <strong>Publicacao do site</strong>
                                    <span>Endereco e situacao do site na internet</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="dominio">Dominio da empresa</label>
                                    <input type="text" class="form-control form-control-sm" id="dominio" name="dominio"
                                        value="{{ old('dominio', $empresa->dominio ?? '') }}"
                                        placeholder="Ex: minhalocadora.com.br">
                                    <small class="field-hint-text">Endereco que os clientes vao usar para acessar o site.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="site_apresentacao_ativo">Situacao do site</label>
                                    @php $statusAtual = old('site_apresentacao_ativo', $empresa->site_apresentacao_ativo ?: 'S'); @endphp
                                    <select class="form-control form-control-sm" id="site_apresentacao_ativo" name="site_apresentacao_ativo">
                                        @foreach($status as $value => $label)
                                            <option value="{{ $value }}" {{ $statusAtual === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="field-hint-text">Quando <strong>Ativo</strong>, o site fica visivel ao publico.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Logo --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-image"></i>
                                <div>
                                    <strong>Logo da empresa</strong>
                                    <span>Aparece no menu de navegacao e no rodape do site</span>
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <label for="logo">Enviar nova logo</label>
                                    <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                                    <small class="field-hint-text">Formatos aceitos: JPG, PNG, WEBP ou SVG. Tamanho maximo: 2MB.</small>
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <div class="logo-preview">
                                        @if(!empty($empresa->logo_url))
                                            <img src="{{ $empresa->logo_url }}" alt="Logo atual da empresa">
                                        @else
                                            <div class="logo-empty">
                                                <i class="fas fa-image"></i>
                                                <span>Nenhuma logo enviada ainda</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Textos padrao --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-pen-alt"></i>
                                <div>
                                    <strong>Textos padrao do site</strong>
                                    <span>Usados quando os textos especificos das secoes nao estiverem preenchidos</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="site_apresentacao_titulo">Titulo do site</label>
                                    <input type="text" class="form-control form-control-sm" id="site_apresentacao_titulo"
                                        name="site_apresentacao_titulo" maxlength="120"
                                        placeholder="Ex: Locadora Premium de Veiculos"
                                        value="{{ old('site_apresentacao_titulo', $empresa->site_apresentacao_titulo ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="site_apresentacao_subtitulo">Subtitulo do site</label>
                                    <input type="text" class="form-control form-control-sm" id="site_apresentacao_subtitulo"
                                        name="site_apresentacao_subtitulo" maxlength="180"
                                        placeholder="Ex: Qualidade e conforto para sua viagem"
                                        value="{{ old('site_apresentacao_subtitulo', $empresa->site_apresentacao_subtitulo ?? '') }}">
                                </div>
                                <div class="col-12 mt-2">
                                    <label for="site_apresentacao_descricao">Descricao do site</label>
                                    <textarea class="form-control form-control-sm" id="site_apresentacao_descricao"
                                        name="site_apresentacao_descricao" rows="3" maxlength="2000"
                                        placeholder="Ex: Somos especializados em aluguel de veiculos...">{{ old('site_apresentacao_descricao', $empresa->site_apresentacao_descricao ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Modelo de layout --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-desktop"></i>
                                <div>
                                    <strong>Modelo de layout</strong>
                                    <span>Escolha o design visual do site de apresentacao</span>
                                </div>
                            </div>
                            <input type="hidden" id="site_apresentacao_modelo" name="site_apresentacao_modelo" value="{{ $modeloAtual }}">
                            <div class="model-grid">
                                @foreach($modelCards as $modelCard)
                                    @php $isEnabled = $modelCard['status'] === 'Disponivel'; @endphp
                                    <div class="model-card {{ $modeloAtual === $modelCard['valor'] ? 'active' : '' }} {{ !$isEnabled ? 'disabled' : '' }}"
                                        data-model-card="{{ $modelCard['valor'] }}"
                                        role="button"
                                        tabindex="{{ $isEnabled ? 0 : -1 }}"
                                        title="{{ !$isEnabled ? 'Em breve' : '' }}">
                                        <div class="model-card-top">
                                            <i class="{{ $modelCard['icon'] }} model-icon"></i>
                                            <span class="model-status {{ $isEnabled ? 'available' : 'soon' }}">{{ $modelCard['status'] }}</span>
                                        </div>
                                        <h6>{{ $modelCard['titulo'] }}</h6>
                                        <p>{{ $modelCard['descricao'] }}</p>
                                        @if($isEnabled)
                                            <div class="model-check"><i class="fas fa-check-circle"></i></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- ======================================================= --}}
                    {{-- PASSO 2: Textos                                          --}}
                    {{-- ======================================================= --}}
                    <section class="wizard-step" data-step="2">
                        <div class="step-head">
                            <h5><i class="fas fa-pen mr-2"></i>Textos do Site</h5>
                            <p>Personalize todos os textos que aparecem em cada secao do site. Clique em uma secao para editar.</p>
                        </div>

                        <div class="text-accordion">
                            @foreach($textGroups as $groupIndex => $group)
                                <div class="text-acc-item {{ $groupIndex === 0 ? 'open' : '' }}" id="acc-group-{{ $groupIndex }}">
                                    <button type="button" class="text-acc-header" data-acc-toggle="{{ $groupIndex }}">
                                        <div class="text-acc-header-left">
                                            <i class="{{ $group['icon'] }} text-acc-icon"></i>
                                            <div>
                                                <span class="text-acc-title">{{ $group['title'] }}</span>
                                                <span class="text-acc-desc">{{ $group['descricao'] }}</span>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down text-acc-arrow"></i>
                                    </button>
                                    <div class="text-acc-body">
                                        <div class="text-fields-grid">
                                            @foreach($group['fields'] as $field)
                                                @php $isTextarea = $field['type'] === 'textarea'; @endphp
                                                <div class="text-field-item {{ $isTextarea ? 'text-field-full' : '' }}">
                                                    <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                                    @if($isTextarea)
                                                        <textarea class="form-control form-control-sm"
                                                            id="{{ $field['name'] }}"
                                                            name="{{ $field['name'] }}"
                                                            rows="3"
                                                            maxlength="{{ $field['max'] }}"
                                                            placeholder="{{ $field['hint'] ?? '' }}">{{ $texto($field['name']) }}</textarea>
                                                    @else
                                                        <input type="text"
                                                            class="form-control form-control-sm"
                                                            id="{{ $field['name'] }}"
                                                            name="{{ $field['name'] }}"
                                                            maxlength="{{ $field['max'] }}"
                                                            placeholder="{{ $field['hint'] ?? '' }}"
                                                            value="{{ $texto($field['name']) }}">
                                                    @endif
                                                    @if(!empty($field['hint']))
                                                        <small class="field-hint-text">{{ $field['hint'] }}</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- ======================================================= --}}
                    {{-- PASSO 3: Cores                                           --}}
                    {{-- ======================================================= --}}
                    <section class="wizard-step" data-step="3">
                        @php
                            $colorGroups = [
                                [
                                    'title'    => 'Identidade principal da empresa',
                                    'icon'     => 'fas fa-paint-brush',
                                    'descricao' => 'As 3 cores base da empresa. Sao usadas em toda a pagina.',
                                    'items'    => [
                                        ['name' => 'cor_primaria',   'label' => 'Cor primaria',   'default' => $siteCores['brand_primaria']   ?? ($empresa->cor_primaria   ?: '#1F2937')],
                                        ['name' => 'cor_secundaria', 'label' => 'Cor secundaria', 'default' => $siteCores['brand_secundaria'] ?? ($empresa->cor_secundaria ?: '#334155')],
                                        ['name' => 'cor_terciaria',  'label' => 'Cor terciaria',  'default' => $siteCores['brand_terciaria']  ?? ($empresa->cor_terciaria  ?: '#64748B')],
                                    ],
                                ],
                                [
                                    'title'    => 'Topo / Navegacao',
                                    'icon'     => 'fas fa-window-maximize',
                                    'descricao' => 'Barra de menu no topo da pagina.',
                                    'items'    => [
                                        ['name' => 'cor_topbar_fundo',      'label' => 'Fundo do menu',    'default' => '#FFFFFF'],
                                        ['name' => 'cor_topbar_texto',      'label' => 'Texto do menu',    'default' => '#0F172A'],
                                        ['name' => 'cor_topbar_link_hover', 'label' => 'Cor ao passar o mouse', 'default' => '#1F2937'],
                                    ],
                                ],
                                [
                                    'title'    => 'Secao Principal (Banner)',
                                    'icon'     => 'fas fa-image',
                                    'descricao' => 'Grande banner no topo da pagina.',
                                    'items'    => [
                                        ['name' => 'cor_hero_fundo', 'label' => 'Cor de fundo', 'default' => '#1F2937'],
                                        ['name' => 'cor_hero_texto', 'label' => 'Cor do texto', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Formulario de Reserva',
                                    'icon'     => 'fas fa-calendar-alt',
                                    'descricao' => 'Caixa de reserva dentro do banner.',
                                    'items'    => [
                                        ['name' => 'cor_form_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_form_texto', 'label' => 'Cor do texto', 'default' => '#0F172A'],
                                    ],
                                ],
                                [
                                    'title'    => 'Secao Frota',
                                    'icon'     => 'fas fa-car',
                                    'descricao' => 'Area que exibe os veiculos disponiveis.',
                                    'items'    => [
                                        ['name' => 'cor_frota_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Secao Vantagens',
                                    'icon'     => 'fas fa-thumbs-up',
                                    'descricao' => 'Area de diferenciais da empresa.',
                                    'items'    => [
                                        ['name' => 'cor_vantagens_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Secao Como Funciona (Etapas)',
                                    'icon'     => 'fas fa-list-ol',
                                    'descricao' => 'Area do passo a passo da reserva.',
                                    'items'    => [
                                        ['name' => 'cor_etapas_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Secao Contato',
                                    'icon'     => 'fas fa-envelope',
                                    'descricao' => 'Area de contato ao final da pagina.',
                                    'items'    => [
                                        ['name' => 'cor_contato_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Botoes',
                                    'icon'     => 'fas fa-hand-pointer',
                                    'descricao' => 'Botoes de acao em toda a pagina.',
                                    'items'    => [
                                        ['name' => 'cor_botao_primario_fundo',    'label' => 'Botao principal: fundo',    'default' => '#E7B93A'],
                                        ['name' => 'cor_botao_primario_texto',    'label' => 'Botao principal: texto',    'default' => '#111827'],
                                        ['name' => 'cor_botao_secundario_fundo',  'label' => 'Botao secundario: fundo',  'default' => '#FFFFFF'],
                                        ['name' => 'cor_botao_secundario_texto',  'label' => 'Botao secundario: texto',  'default' => '#1F2937'],
                                    ],
                                ],
                                [
                                    'title'    => 'Rodape',
                                    'icon'     => 'fas fa-minus',
                                    'descricao' => 'Barra inferior da pagina.',
                                    'items'    => [
                                        ['name' => 'cor_footer_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_footer_texto', 'label' => 'Cor do texto', 'default' => '#64748B'],
                                    ],
                                ],
                                [
                                    'title'    => 'Tela Administrativa',
                                    'icon'     => 'fas fa-shield-alt',
                                    'descricao' => 'Painel administrativo acessado pelo dominio da empresa.',
                                    'items'    => [
                                        ['name' => 'cor_admin_fundo', 'label' => 'Cor de fundo', 'default' => '#193C68'],
                                        ['name' => 'cor_admin_texto', 'label' => 'Cor do texto', 'default' => '#FFFFFF'],
                                    ],
                                ],
                            ];
                        @endphp

                        <div class="step-head">
                            <h5><i class="fas fa-palette mr-2"></i>Cores do Site</h5>
                            <p>Configure as cores de cada parte do site. Clique no quadradinho colorido para escolher a cor.</p>
                        </div>

                        <div class="color-groups-grid">
                            @foreach($colorGroups as $group)
                                <div class="color-group-card">
                                    <div class="color-group-header">
                                        <i class="{{ $group['icon'] }} color-group-icon"></i>
                                        <div>
                                            <strong>{{ $group['title'] }}</strong>
                                            <span class="color-group-desc">{{ $group['descricao'] }}</span>
                                        </div>
                                    </div>
                                    <div class="color-items">
                                        @foreach($group['items'] as $item)
                                            @php
                                                $colorValue = old($item['name'], str_starts_with($item['name'], 'cor_') && !in_array($item['name'], ['cor_primaria', 'cor_secundaria', 'cor_terciaria'], true)
                                                    ? ($siteCores[str_replace('cor_', '', $item['name'])] ?? $item['default'])
                                                    : $item['default']);
                                            @endphp
                                            <div class="color-item">
                                                <label for="{{ $item['name'] }}">{{ $item['label'] }}</label>
                                                <div class="color-field">
                                                    <input type="color" class="color-picker" id="{{ $item['name'] }}" name="{{ $item['name'] }}" value="{{ $colorValue }}">
                                                    <input type="text" class="form-control form-control-sm color-text" data-color-text="{{ $item['name'] }}" value="{{ $colorValue }}" maxlength="7" placeholder="#000000">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="color-preview-row mt-4">
                            <div class="color-preview-box" id="previewSite">
                                <div class="preview-label">Pre-visualizacao do site</div>
                                <div class="preview-sublabel">Cores primaria, secundaria e terciaria</div>
                            </div>
                            <div class="color-preview-box" id="previewAdmin">
                                <div class="preview-label">Pre-visualizacao administrativa</div>
                                <div class="preview-sublabel">Cores da tela administrativa</div>
                            </div>
                        </div>
                    </section>

                    {{-- ======================================================= --}}
                    {{-- PASSO 4: Imagens                                         --}}
                    {{-- ======================================================= --}}
                    <section class="wizard-step" data-step="4">
                        @php
                            $imageFields = [
                                ['key' => 'hero',    'upload' => 'imagem_hero',    'toggle' => 'usar_imagem_hero',    'label' => 'Banner principal',        'icon' => 'fas fa-image',        'hint' => 'Recomendado: 1600 x 900 pixels'],
                                ['key' => 'frota_1', 'upload' => 'imagem_frota_1', 'toggle' => 'usar_imagem_frota_1', 'label' => 'Frota — Card 1',          'icon' => 'fas fa-car',          'hint' => 'Recomendado: 900 x 600 pixels'],
                                ['key' => 'frota_2', 'upload' => 'imagem_frota_2', 'toggle' => 'usar_imagem_frota_2', 'label' => 'Frota — Card 2',          'icon' => 'fas fa-car',          'hint' => 'Recomendado: 900 x 600 pixels'],
                                ['key' => 'frota_3', 'upload' => 'imagem_frota_3', 'toggle' => 'usar_imagem_frota_3', 'label' => 'Frota — Card 3',          'icon' => 'fas fa-car',          'hint' => 'Recomendado: 900 x 600 pixels'],
                                ['key' => 'vantagens','upload' => 'imagem_vantagens','toggle' => 'usar_imagem_vantagens','label' => 'Secao Vantagens',        'icon' => 'fas fa-thumbs-up',    'hint' => 'Recomendado: 1200 x 700 pixels'],
                                ['key' => 'contato', 'upload' => 'imagem_contato', 'toggle' => 'usar_imagem_contato', 'label' => 'Secao Contato',           'icon' => 'fas fa-envelope',     'hint' => 'Recomendado: 900 x 500 pixels'],
                            ];
                        @endphp

                        <div class="step-head">
                            <h5><i class="fas fa-images mr-2"></i>Imagens do Site</h5>
                            <p>Envie as fotos para cada secao do site. Use o botao <strong>Usar imagem</strong> para ativar ou desativar cada foto.</p>
                        </div>

                        <div class="image-grid">
                            @foreach($imageFields as $field)
                                @php
                                    $urlAtual = $imagemUrl($field['key']);
                                    $ativa    = (bool) old($field['toggle'], $imagemAtiva($field['key']));
                                @endphp
                                <div class="image-card">
                                    <div class="image-card-header">
                                        <div class="image-card-title">
                                            <i class="{{ $field['icon'] }}"></i>
                                            <strong>{{ $field['label'] }}</strong>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="{{ $field['toggle'] }}" value="0">
                                            <input type="checkbox"
                                                class="custom-control-input"
                                                id="{{ $field['toggle'] }}"
                                                name="{{ $field['toggle'] }}"
                                                value="1"
                                                {{ $ativa ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="{{ $field['toggle'] }}">Usar imagem</label>
                                        </div>
                                    </div>

                                    <div class="image-preview-area">
                                        @if(!empty($urlAtual))
                                            <img src="{{ $urlAtual }}" alt="{{ $field['label'] }}">
                                        @else
                                            <div class="image-empty">
                                                <i class="fas fa-upload"></i>
                                                <span>Nenhuma imagem enviada</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="image-upload-area">
                                        <label for="{{ $field['upload'] }}" class="upload-btn">
                                            <i class="fas fa-upload mr-1"></i>
                                            {{ !empty($urlAtual) ? 'Trocar imagem' : 'Enviar imagem' }}
                                        </label>
                                        <input type="file"
                                            class="upload-input"
                                            id="{{ $field['upload'] }}"
                                            name="{{ $field['upload'] }}"
                                            accept=".jpg,.jpeg,.png,.webp">
                                        <small class="field-hint-text">JPG, PNG ou WEBP — max 4MB<br>{{ $field['hint'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- ======================================================= --}}
                    {{-- PASSO 5: Publicar                                        --}}
                    {{-- ======================================================= --}}
                    <section class="wizard-step" data-step="5">
                        <div class="step-head">
                            <h5><i class="fas fa-rocket mr-2"></i>Revisar e Publicar</h5>
                            <p>Confira o checklist abaixo antes de salvar. Nao e obrigatorio preencher tudo agora.</p>
                        </div>

                        <div class="publish-grid">
                            <div class="publish-card checklist-card">
                                <h6><i class="fas fa-clipboard-check mr-2"></i>Checklist essencial</h6>
                                <ul class="checklist">
                                    <li><i class="fas fa-check-circle"></i> Preencha o dominio da empresa (Passo 1)</li>
                                    <li><i class="fas fa-check-circle"></i> Coloque o site como <strong>Ativo</strong> (Passo 1)</li>
                                    <li><i class="fas fa-check-circle"></i> Envie a logo da empresa (Passo 1)</li>
                                    <li><i class="fas fa-check-circle"></i> Preencha o titulo e subtitulo do banner (Passo 2)</li>
                                    <li><i class="fas fa-check-circle"></i> Escolha as cores principais da empresa (Passo 3)</li>
                                    <li><i class="fas fa-check-circle"></i> Envie pelo menos a imagem do banner (Passo 4)</li>
                                </ul>
                            </div>

                            <div class="publish-card tips-card">
                                <h6><i class="fas fa-lightbulb mr-2"></i>Dicas para um bom resultado</h6>
                                <ul class="checklist tips-list">
                                    <li><i class="fas fa-info-circle"></i> Use imagens com boa resolucao para o site ficar bonito</li>
                                    <li><i class="fas fa-info-circle"></i> Se nao tiver imagens agora, desmarque os toggles no Passo 4</li>
                                    <li><i class="fas fa-info-circle"></i> Salve e abra o dominio para conferir o resultado</li>
                                    <li><i class="fas fa-info-circle"></i> Voce pode voltar e editar qualquer informacao a qualquer momento</li>
                                </ul>
                            </div>
                        </div>

                        <div class="publish-faq">
                            <details class="faq-item">
                                <summary><i class="fas fa-question-circle mr-2"></i>Como vejo o site publicado?</summary>
                                <div class="faq-body">
                                    Acesse o dominio configurado no Passo 1
                                    @if(!empty($empresa->dominio))
                                        (<a href="https://{{ $empresa->dominio }}" target="_blank"><strong>{{ $empresa->dominio }}</strong></a>)
                                    @endif
                                    pelo navegador apos salvar.
                                </div>
                            </details>
                            <details class="faq-item">
                                <summary><i class="fas fa-question-circle mr-2"></i>Preciso preencher todos os campos?</summary>
                                <div class="faq-body">
                                    Nao. Os campos do Passo 1 sao os mais importantes. Os textos das secoes (Passo 2) tem valores padrao quando nao preenchidos.
                                </div>
                            </details>
                            <details class="faq-item">
                                <summary><i class="fas fa-question-circle mr-2"></i>O que e a tela administrativa?</summary>
                                <div class="faq-body">
                                    E a tela de login do sistema. Quando o cliente acessar <code>/administrativa</code> pelo dominio da empresa, vera a logo e as cores definidas no Passo 3 (Tela Administrativa).
                                </div>
                            </details>
                        </div>

                        @if(!empty($empresa->dominio))
                            <div class="publish-action-row">
                                <a href="https://{{ $empresa->dominio }}" target="_blank" class="btn-preview-site">
                                    <i class="fas fa-external-link-alt mr-2"></i>Abrir site para conferir
                                </a>
                            </div>
                        @endif
                    </section>

                    {{-- Acoes do wizard --}}
                    <div class="wizard-actions">
                        <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                            <i class="fas fa-arrow-left mr-1"></i>Voltar
                        </button>
                        <div class="wizard-actions-right">
                            <button type="button" class="btn-action btn-action-primary" id="wizardNext">
                                Continuar<i class="fas fa-arrow-right ml-1"></i>
                            </button>
                            <button type="submit" class="btn-action btn-action-success" id="wizardSave">
                                <i class="fas fa-save mr-1"></i>Salvar configuracoes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    /* ================================================
       BASE
    ================================================ */
    .config-card {
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 24px rgba(15, 39, 64, 0.08);
        border: 1px solid #e4edf7;
    }

    .config-card-header {
        background: linear-gradient(120deg, #f0f7ff 0%, #e8f3ff 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #d8e8f5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .config-card-header h4 {
        color: #0f2740;
        font-weight: 700;
        font-size: 1.15rem;
        margin: 0;
    }

    .config-card-header p {
        color: #5b7a99;
        font-size: 0.85rem;
        margin: 4px 0 0;
    }

    .config-card-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .domain-chip {
        border: 1px solid #b8d4ee;
        border-radius: 999px;
        padding: 0.3rem 0.75rem;
        font-size: 0.78rem;
        color: #1e4a7a;
        background: #fff;
        font-weight: 600;
    }

    .btn-ver-site {
        background: #2563eb;
        color: #fff;
        border-radius: 999px;
        padding: 0.3rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-ver-site:hover {
        background: #1d4ed8;
        color: #fff;
        text-decoration: none;
    }

    .config-body {
        padding: 1.5rem;
    }

    /* ================================================
       STEP INDICATOR
    ================================================ */
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 1.75rem;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .step-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px 10px;
        min-width: 90px;
        flex-shrink: 0;
        transition: opacity 0.2s;
    }

    .step-btn:hover .step-num {
        border-color: #2563eb;
        color: #2563eb;
    }

    .step-num {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid #d0dff0;
        background: #f4f8ff;
        color: #7a99bb;
        font-weight: 700;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.25s;
    }

    .step-btn.active .step-num {
        border-color: #2563eb;
        background: #2563eb;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
    }

    .step-btn.completed .step-num {
        border-color: #16a34a;
        background: #16a34a;
        color: #fff;
    }

    .step-label-text {
        font-size: 0.72rem;
        font-weight: 600;
        color: #8ba5c0;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .step-btn.active .step-label-text {
        color: #2563eb;
    }

    .step-divider {
        flex: 1;
        height: 2px;
        background: #dce8f5;
        min-width: 20px;
        margin-bottom: 22px;
        flex-shrink: 1;
    }

    /* ================================================
       WIZARD STEPS
    ================================================ */
    .wizard-step {
        display: none;
    }

    .wizard-step.active {
        display: block;
    }

    .step-head {
        margin-bottom: 1.25rem;
    }

    .step-head h5 {
        font-weight: 700;
        color: #0f2740;
        margin-bottom: 4px;
        font-size: 1rem;
    }

    .step-head p {
        color: #64748b;
        font-size: 0.85rem;
        margin: 0;
    }

    /* ================================================
       CONFIG BLOCKS (Step 1)
    ================================================ */
    .config-block {
        border: 1px solid #e4edf7;
        border-radius: 14px;
        padding: 1rem 1.25rem;
        background: #fafcff;
        margin-bottom: 1rem;
    }

    .config-block-label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #eaf1fb;
    }

    .config-block-label > i {
        font-size: 1rem;
        color: #2563eb;
        margin-top: 3px;
        flex-shrink: 0;
    }

    .config-block-label strong {
        display: block;
        color: #0f2740;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .config-block-label span {
        color: #64748b;
        font-size: 0.78rem;
    }

    .config-block label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }

    .config-block .form-control {
        border-radius: 9px;
        border-color: #d0dff0;
        font-size: 0.85rem;
    }

    .field-hint-text {
        display: block;
        font-size: 0.73rem;
        color: #7a99bb;
        margin-top: 3px;
    }

    /* ================================================
       LOGO PREVIEW
    ================================================ */
    .logo-preview {
        min-height: 100px;
        border: 2px dashed #cddcee;
        border-radius: 12px;
        background: #f4f8ff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
    }

    .logo-preview img {
        max-height: 80px;
        max-width: 100%;
        object-fit: contain;
    }

    .logo-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
    }

    .logo-empty i {
        font-size: 1.8rem;
    }

    .logo-empty span {
        font-size: 0.78rem;
    }

    /* ================================================
       MODEL CARDS (Step 1)
    ================================================ */
    .model-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .model-card {
        border: 2px solid #dbe6f2;
        border-radius: 12px;
        padding: 14px;
        background: #f8fbff;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        min-height: 130px;
    }

    .model-card:hover:not(.disabled) {
        border-color: #2563eb;
        background: #eff6ff;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.12);
    }

    .model-card.active {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .model-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .model-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .model-icon {
        font-size: 1.1rem;
        color: #2563eb;
    }

    .model-status {
        font-size: 0.66rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.18rem 0.55rem;
    }

    .model-status.available {
        background: #dcfce7;
        color: #15803d;
    }

    .model-status.soon {
        background: #fef3c7;
        color: #92400e;
    }

    .model-card h6 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f2740;
        margin-bottom: 4px;
    }

    .model-card p {
        font-size: 0.75rem;
        color: #475569;
        line-height: 1.45;
        margin: 0;
    }

    .model-check {
        position: absolute;
        bottom: 10px;
        right: 12px;
        color: #2563eb;
        font-size: 1rem;
        display: none;
    }

    .model-card.active .model-check {
        display: block;
    }

    /* ================================================
       TEXT ACCORDION (Step 2)
    ================================================ */
    .text-accordion {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .text-acc-item {
        border: 1px solid #e4edf7;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .text-acc-item.open {
        border-color: #b8d4ee;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.06);
    }

    .text-acc-header {
        width: 100%;
        background: #f8fbff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        cursor: pointer;
        text-align: left;
        transition: background 0.2s;
        gap: 12px;
    }

    .text-acc-item.open .text-acc-header {
        background: #eef5ff;
        border-bottom: 1px solid #d8eaf8;
    }

    .text-acc-header:hover {
        background: #eef5ff;
    }

    .text-acc-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }

    .text-acc-icon {
        font-size: 1rem;
        color: #2563eb;
        flex-shrink: 0;
        width: 20px;
        text-align: center;
    }

    .text-acc-title {
        display: block;
        font-weight: 700;
        color: #0f2740;
        font-size: 0.85rem;
    }

    .text-acc-desc {
        display: block;
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 1px;
    }

    .text-acc-arrow {
        color: #7a99bb;
        font-size: 0.8rem;
        flex-shrink: 0;
        transition: transform 0.25s;
    }

    .text-acc-item.open .text-acc-arrow {
        transform: rotate(180deg);
        color: #2563eb;
    }

    .text-acc-body {
        display: none;
        padding: 16px;
    }

    .text-acc-item.open .text-acc-body {
        display: block;
    }

    .text-fields-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .text-field-item {
        display: flex;
        flex-direction: column;
    }

    .text-field-item label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }

    .text-field-item .form-control {
        border-radius: 9px;
        border-color: #d0dff0;
        font-size: 0.83rem;
    }

    .text-field-full {
        grid-column: 1 / -1;
    }

    /* ================================================
       COLORS (Step 3)
    ================================================ */
    .color-groups-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .color-group-card {
        border: 1px solid #e4edf7;
        border-radius: 12px;
        padding: 14px;
        background: #fafcff;
    }

    .color-group-header {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaf1fb;
    }

    .color-group-icon {
        font-size: 0.9rem;
        color: #2563eb;
        margin-top: 3px;
        flex-shrink: 0;
        width: 16px;
        text-align: center;
    }

    .color-group-header strong {
        display: block;
        font-size: 0.82rem;
        color: #0f2740;
        font-weight: 700;
    }

    .color-group-desc {
        font-size: 0.72rem;
        color: #64748b;
        display: block;
        margin-top: 1px;
    }

    .color-items {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .color-item label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
        display: block;
    }

    .color-field {
        display: grid;
        grid-template-columns: 44px 1fr;
        gap: 8px;
        align-items: center;
    }

    .color-picker {
        width: 44px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid #d0dff0;
        padding: 2px;
        cursor: pointer;
        background: #fff;
    }

    .color-text {
        border-radius: 9px;
        border-color: #d0dff0;
        font-size: 0.83rem;
        font-family: monospace;
    }

    .color-preview-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .color-preview-box {
        border-radius: 12px;
        min-height: 90px;
        padding: 16px;
        border: 1px solid #d0dff0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: all 0.3s;
    }

    .preview-label {
        font-weight: 700;
        font-size: 0.88rem;
        margin-bottom: 4px;
    }

    .preview-sublabel {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    /* ================================================
       IMAGES (Step 4)
    ================================================ */
    .image-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .image-card {
        border: 1px solid #e4edf7;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .image-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        background: #f4f8ff;
        border-bottom: 1px solid #e4edf7;
        gap: 8px;
        flex-wrap: wrap;
    }

    .image-card-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
    }

    .image-card-title i {
        color: #2563eb;
    }

    .image-card-title strong {
        color: #0f2740;
        font-weight: 700;
    }

    .image-preview-area {
        min-height: 140px;
        background: #f8fbff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .image-preview-area img {
        width: 100%;
        max-height: 180px;
        object-fit: cover;
    }

    .image-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
        padding: 20px;
    }

    .image-empty i {
        font-size: 2rem;
    }

    .image-empty span {
        font-size: 0.78rem;
    }

    .image-upload-area {
        padding: 12px 14px;
        border-top: 1px solid #e4edf7;
    }

    .upload-btn {
        display: inline-flex;
        align-items: center;
        background: #eff6ff;
        border: 1px solid #b8d4ee;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #1d4ed8;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 6px;
    }

    .upload-btn:hover {
        background: #dbeafe;
        border-color: #2563eb;
    }

    .upload-input {
        display: none;
    }

    /* ================================================
       PUBLISH (Step 5)
    ================================================ */
    .publish-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 1rem;
    }

    .publish-card {
        border: 1px solid #e4edf7;
        border-radius: 12px;
        padding: 16px;
    }

    .publish-card h6 {
        font-weight: 700;
        color: #0f2740;
        font-size: 0.88rem;
        margin-bottom: 12px;
    }

    .checklist {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .checklist li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.82rem;
        color: #334155;
    }

    .checklist-card .checklist li i {
        color: #16a34a;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .tips-list li i {
        color: #2563eb;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .publish-faq {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 1rem;
    }

    .faq-item {
        border: 1px solid #e4edf7;
        border-radius: 10px;
        overflow: hidden;
    }

    .faq-item summary {
        padding: 12px 16px;
        font-size: 0.83rem;
        font-weight: 600;
        color: #1e3a5f;
        cursor: pointer;
        background: #f8fbff;
        list-style: none;
        display: flex;
        align-items: center;
    }

    .faq-item summary::-webkit-details-marker {
        display: none;
    }

    .faq-item[open] summary {
        background: #eef5ff;
        border-bottom: 1px solid #d8eaf8;
    }

    .faq-body {
        padding: 12px 16px;
        font-size: 0.82rem;
        color: #475569;
    }

    .faq-body code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.78rem;
    }

    .publish-action-row {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
    }

    .btn-preview-site {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        border-radius: 10px;
        padding: 12px 28px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
    }

    .btn-preview-site:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }

    /* ================================================
       WIZARD ACTIONS
    ================================================ */
    .wizard-actions {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #e4edf7;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .wizard-actions-right {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 0.84rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-action-secondary {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #d0dff0;
    }

    .btn-action-secondary:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .btn-action-primary {
        background: #2563eb;
        color: #fff;
    }

    .btn-action-primary:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .btn-action-success {
        background: #16a34a;
        color: #fff;
    }

    .btn-action-success:hover {
        background: #15803d;
        color: #fff;
    }

    /* ================================================
       RESPONSIVE
    ================================================ */
    @media (max-width: 992px) {
        .model-grid,
        .color-groups-grid,
        .image-grid,
        .publish-grid,
        .color-preview-row {
            grid-template-columns: 1fr;
        }

        .text-fields-grid {
            grid-template-columns: 1fr;
        }

        .step-btn {
            min-width: 70px;
        }
    }

    @media (max-width: 768px) {
        .config-body {
            padding: 1rem;
        }

        .wizard-actions,
        .wizard-actions-right {
            width: 100%;
        }

        .wizard-actions-right {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@stop

@section('js')
<script>
    (function () {
        /* ========================
           WIZARD STEPS
        ======================== */
        const stepBtns  = Array.from(document.querySelectorAll('.step-btn'));
        const stepPanels = Array.from(document.querySelectorAll('[data-step]'));
        const prevBtn   = document.getElementById('wizardPrev');
        const nextBtn   = document.getElementById('wizardNext');
        const saveBtn   = document.getElementById('wizardSave');
        let current     = 1;
        const lastStep  = stepPanels.length;

        function showStep(n) {
            current = Math.min(Math.max(n, 1), lastStep);

            stepPanels.forEach(panel => {
                panel.classList.toggle('active', Number(panel.dataset.step) === current);
            });

            stepBtns.forEach(btn => {
                const t = Number(btn.dataset.stepTarget);
                btn.classList.toggle('active', t === current);
                btn.classList.toggle('completed', t < current);
            });

            prevBtn.style.visibility = current === 1 ? 'hidden' : 'visible';
            nextBtn.style.display    = current === lastStep ? 'none' : 'inline-flex';
            saveBtn.style.display    = 'inline-flex';

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        stepBtns.forEach(btn => {
            btn.addEventListener('click', () => showStep(Number(btn.dataset.stepTarget)));
        });

        prevBtn.addEventListener('click', () => showStep(current - 1));
        nextBtn.addEventListener('click', () => showStep(current + 1));

        /* ========================
           TEXT ACCORDION
        ======================== */
        function initAccordion() {
            const items = Array.from(document.querySelectorAll('.text-acc-item'));

            items.forEach(item => {
                const header = item.querySelector('.text-acc-header');
                if (!header) return;

                header.addEventListener('click', () => {
                    const isOpen = item.classList.contains('open');
                    item.classList.toggle('open', !isOpen);
                });
            });
        }

        /* ========================
           MODEL SELECTOR
        ======================== */
        function initModelCards() {
            const hiddenInput = document.getElementById('site_apresentacao_modelo');
            const cards = Array.from(document.querySelectorAll('[data-model-card]'));

            cards.forEach(card => {
                if (card.classList.contains('disabled')) return;

                card.addEventListener('click', () => {
                    const val = card.dataset.modelCard;
                    if (hiddenInput) hiddenInput.value = val;
                    cards.forEach(c => c.classList.toggle('active', c.dataset.modelCard === val));
                });

                card.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        card.click();
                    }
                });
            });
        }

        /* ========================
           IMAGE FILE LABEL
        ======================== */
        function initImageUploads() {
            document.querySelectorAll('.upload-input').forEach(input => {
                input.addEventListener('change', () => {
                    const btn = document.querySelector(`label[for="${input.id}"]`);
                    if (!btn) return;
                    const name = input.files[0]?.name;
                    if (name) {
                        btn.innerHTML = `<i class="fas fa-check mr-1"></i>${name.length > 24 ? name.slice(0, 22) + '…' : name}`;
                        btn.style.background = '#dcfce7';
                        btn.style.borderColor = '#16a34a';
                        btn.style.color = '#15803d';
                    }
                });
            });
        }

        /* ========================
           COLOR SYNC
        ======================== */
        function initColorSync() {
            document.querySelectorAll('.color-picker').forEach(input => {
                const text = document.querySelector(`[data-color-text="${input.id}"]`);
                if (!text) return;

                input.addEventListener('input', () => {
                    text.value = input.value.toUpperCase();
                    updatePreviews();
                });

                text.addEventListener('change', () => {
                    const val = (text.value || '').trim().toUpperCase();
                    if (/^#[A-F0-9]{6}$/.test(val)) {
                        input.value = val;
                        updatePreviews();
                    }
                });
            });
        }

        function updatePreviews() {
            const c1    = document.getElementById('cor_primaria')?.value   || '#1F2937';
            const c2    = document.getElementById('cor_secundaria')?.value || '#334155';
            const c3    = document.getElementById('cor_terciaria')?.value  || '#64748B';
            const abg   = document.getElementById('cor_admin_fundo')?.value || '#193C68';
            const atxt  = document.getElementById('cor_admin_texto')?.value || '#FFFFFF';

            const site = document.getElementById('previewSite');
            if (site) {
                site.style.background = `linear-gradient(135deg, ${c1} 0%, ${c2} 55%, ${c3} 100%)`;
                site.style.color = '#fff';
                site.style.borderColor = c1;
            }

            const admin = document.getElementById('previewAdmin');
            if (admin) {
                admin.style.background = abg;
                admin.style.color = atxt;
                admin.style.borderColor = abg;
            }
        }

        /* ========================
           INIT
        ======================== */
        initAccordion();
        initModelCards();
        initImageUploads();
        initColorSync();
        updatePreviews();
        showStep(1);
    })();
</script>
@stop
