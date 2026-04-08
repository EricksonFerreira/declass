@extends('adminlte::page')

@section('title', $título)

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
            return \Illuminate\Support\Façades\Storage::url($value);
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
                'título'   => 'Modelo 1 — Premium Locadora',
                'status'   => 'Disponível',
                'icon'     => 'fas fa-star',
                'descrição' => 'Layout completo com banner, formulário de reserva e seções configuráveis.',
            ],
            [
                'valor'    => 'modelo_2',
                'título'   => 'Modelo 2 — Compacto',
                'status'   => 'Em breve',
                'icon'     => 'fas fa-bolt',
                'descrição' => 'Variação focada em conversão rapida para campanhas de mídia.',
            ],
            [
                'valor'    => 'modelo_3',
                'título'   => 'Modelo 3 — Corporativo',
                'status'   => 'Em breve',
                'icon'     => 'fas fa-building',
                'descrição' => 'Layout institucional para operações B2B com blocos corporativos.',
            ],
        ];

        $textGroups = [
            [
                'title'    => 'Menu de Navegação e Botões',
                'icon'     => 'fas fa-bars',
                'descrição' => 'Nomes dos itens do menu, botão de reserva e texto do rodapé.',
                'fields'   => [
                    ['name' => 'menu_itêm_frota',              'label' => 'Itêm de menu: Frota',                  'hint' => 'Ex: Nossa Frota',                                      'type' => 'text',     'max' => 50],
                    ['name' => 'menu_itêm_vantagens',          'label' => 'Itêm de menu: Vantagens',              'hint' => 'Ex: Por que nos escolher',                             'type' => 'text',     'max' => 50],
                    ['name' => 'menu_itêm_etapas',             'label' => 'Itêm de menu: Como funciona',          'hint' => 'Ex: Como funciona',                                    'type' => 'text',     'max' => 50],
                    ['name' => 'menu_itêm_contato',            'label' => 'Itêm de menu: Contato',                'hint' => 'Ex: Fale conosco',                                     'type' => 'text',     'max' => 50],
                    ['name' => 'site_apresentacao_botao_texto','label' => 'Texto do botão de reserva',            'hint' => 'Ex: Reserve agora',                                    'type' => 'text',     'max' => 60],
                    ['name' => 'site_apresentacao_botao_url',  'label' => 'Link do botão de reserva (opcional)',  'hint' => 'Deixe em branco para usar o sistêma de reservas',      'type' => 'text',     'max' => 255],
                    ['name' => 'botão_admin_texto',            'label' => 'Botao da área administrativa',         'hint' => 'Ex: Área do Cliente',                                  'type' => 'text',     'max' => 80],
                    ['name' => 'footer_texto',                 'label' => 'Texto do rodapé',                      'hint' => 'Ex: Todos os direitos reservados.',   'type' => 'text',     'max' => 200],
                ],
            ],
            [
                'title'    => 'Seção Principal (Banner)',
                'icon'     => 'fas fa-image',
                'descrição' => 'Textos do grande banner que aparece no topo da página.',
                'fields'   => [
                    ['name' => 'hero_tag',        'label' => 'Etiqueta em destaque',     'hint' => 'Pequeno texto colorido acima do título. Ex: Aluguel de carros',         'type' => 'text',     'max' => 120],
                    ['name' => 'hero_titulo',     'label' => 'Título principal',          'hint' => 'Ex: Alugue o carro ideal para sua viagem',                             'type' => 'text',     'max' => 180],
                    ['name' => 'hero_subtítulo',  'label' => 'Subtítulo',                 'hint' => 'Ex: Frota completa, precos acessiveis e atendimento rapido',           'type' => 'text',     'max' => 220],
                    ['name' => 'hero_descricao',  'label' => 'Descrição complementar',   'hint' => 'Texto adicional abaixo do subtítulo (pode deixar em branco)',           'type' => 'textárea', 'max' => 2000],
                ],
            ],
            [
                'title'    => 'Formulário de Reserva',
                'icon'     => 'fas fa-calendar-alt',
                'descrição' => 'Textos exibidos dentro do formulário de reserva no banner principal.',
                'fields'   => [
                    ['name' => 'hero_form_titulo',              'label' => 'Título do formulário',                'hint' => 'Ex: Faça sua reserva',                                 'type' => 'text',     'max' => 120],
                    ['name' => 'hero_form_descricao',           'label' => 'Descrição do formulário',            'hint' => 'Texto explicativo acima dos campos de data',           'type' => 'textárea', 'max' => 500],
                    ['name' => 'hero_form_label_retirada',      'label' => 'Campo: Local de retirada',           'hint' => 'Ex: Onde você vai retirar o carro?',                   'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_devolucao',     'label' => 'Campo: Local de devolução',          'hint' => 'Ex: Onde você vai devolver o carro?',                  'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_data_retirada', 'label' => 'Campo: Data de retirada',            'hint' => 'Ex: Data de retirada',                                 'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_hora_retirada', 'label' => 'Campo: Hora de retirada',            'hint' => 'Ex: Hora de retirada',                                 'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_data_devolucao','label' => 'Campo: Data de devolução',           'hint' => 'Ex: Data de devolução',                                'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_label_hora_devolucao','label' => 'Campo: Hora de devolução',           'hint' => 'Ex: Hora de devolução',                                'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_texto_botao',         'label' => 'Texto do botão do formulário',       'hint' => 'Ex: Ver disponibilidade',                              'type' => 'text',     'max' => 80],
                    ['name' => 'hero_form_nota',                'label' => 'Nota abaixo do formulário',          'hint' => 'Ex: Reserva sem cartão de crédito',                    'type' => 'textárea', 'max' => 280],
                ],
            ],
            [
                'title'    => 'Seção Frota (Nossos Veículos)',
                'icon'     => 'fas fa-car',
                'descrição' => 'Textos da seção que apresenta os veículos disponíveis.',
                'fields'   => [
                    ['name' => 'frota_label_superior',   'label' => 'Etiqueta acima do título',  'hint' => 'Ex: Nossos veículos',                                           'type' => 'text',     'max' => 60],
                    ['name' => 'frota_titulo',           'label' => 'Título da seção',            'hint' => 'Ex: Escolha o carro ideal',                                    'type' => 'text',     'max' => 160],
                    ['name' => 'frota_descricao',        'label' => 'Descrição da seção',         'hint' => 'Texto introdutório sobre a frota',                             'type' => 'textárea', 'max' => 1000],
                    ['name' => 'frota_card_1_titulo',    'label' => 'Card 1 — Título',            'hint' => 'Ex: Hatch Econômico',                                          'type' => 'text',     'max' => 120],
                    ['name' => 'frota_card_1_descricao', 'label' => 'Card 1 — Descrição',         'hint' => 'Ex: Ideal para a cidade, econômico e confortável',             'type' => 'textárea', 'max' => 400],
                    ['name' => 'frota_card_2_titulo',    'label' => 'Card 2 — Título',            'hint' => 'Ex: Sedan Executivo',                                          'type' => 'text',     'max' => 120],
                    ['name' => 'frota_card_2_descricao', 'label' => 'Card 2 — Descrição',         'hint' => 'Ex: Conforto e elegância para viagens longas',                 'type' => 'textárea', 'max' => 400],
                    ['name' => 'frota_card_3_titulo',    'label' => 'Card 3 — Título',            'hint' => 'Ex: SUV Familiar',                                             'type' => 'text',     'max' => 120],
                    ['name' => 'frota_card_3_descricao', 'label' => 'Card 3 — Descrição',         'hint' => 'Ex: Espaço e seguranca para toda a família',                   'type' => 'textárea', 'max' => 400],
                ],
            ],
            [
                'title'    => 'Seção Vantagens',
                'icon'     => 'fas fa-thumbs-up',
                'descrição' => 'Textos que destacam os diferenciais da empresa.',
                'fields'   => [
                    ['name' => 'vantagens_label_superior', 'label' => 'Etiqueta acima do título', 'hint' => 'Ex: Por que nos escolher',                                      'type' => 'text',     'max' => 60],
                    ['name' => 'vantagens_titulo',         'label' => 'Título da seção',           'hint' => 'Ex: Nossas vantagens',                                         'type' => 'text',     'max' => 160],
                    ['name' => 'vantagens_descricao',      'label' => 'Descrição da seção',        'hint' => 'Texto introdutório sobre os benefícios',                       'type' => 'textárea', 'max' => 1000],
                    ['name' => 'beneficio_1',              'label' => 'Vantagem 1',                'hint' => 'Ex: Atendimento 24 horas',                                     'type' => 'text',     'max' => 220],
                    ['name' => 'beneficio_2',              'label' => 'Vantagem 2',                'hint' => 'Ex: Frota revisada e garantida',                               'type' => 'text',     'max' => 220],
                    ['name' => 'beneficio_3',              'label' => 'Vantagem 3',                'hint' => 'Ex: Sem taxa de cancelamento',                                 'type' => 'text',     'max' => 220],
                    ['name' => 'beneficio_4',              'label' => 'Vantagem 4',                'hint' => 'Ex: Cobertura em todo o Brasil',                               'type' => 'text',     'max' => 220],
                ],
            ],
            [
                'title'    => 'Seção Como Funciona (Etapas)',
                'icon'     => 'fas fa-list-ol',
                'descrição' => 'Passo a passo explicando como o cliente faz a reserva.',
                'fields'   => [
                    ['name' => 'etapas_label_superior', 'label' => 'Etiqueta acima do título',   'hint' => 'Ex: É muito simples',                                           'type' => 'text',     'max' => 60],
                    ['name' => 'etapas_titulo',         'label' => 'Título da seção',             'hint' => 'Ex: Como funciona',                                            'type' => 'text',     'max' => 160],
                    ['name' => 'etapa_1_titulo',        'label' => 'Passo 1 — Título',            'hint' => 'Ex: Escolha o veículo',                                        'type' => 'text',     'max' => 120],
                    ['name' => 'etapa_1_descricao',     'label' => 'Passo 1 — Descrição',         'hint' => 'Ex: Navegue pela nossa frota e encontre o carro ideal',        'type' => 'textárea', 'max' => 300],
                    ['name' => 'etapa_2_titulo',        'label' => 'Passo 2 — Título',            'hint' => 'Ex: Faça a reserva',                                           'type' => 'text',     'max' => 120],
                    ['name' => 'etapa_2_descricao',     'label' => 'Passo 2 — Descrição',         'hint' => 'Ex: Preencha o formulário com as datas desejadas',             'type' => 'textárea', 'max' => 300],
                    ['name' => 'etapa_3_titulo',        'label' => 'Passo 3 — Título',            'hint' => 'Ex: Retire e aproveite',                                       'type' => 'text',     'max' => 120],
                    ['name' => 'etapa_3_descricao',     'label' => 'Passo 3 — Descrição',         'hint' => 'Ex: Retire o veículo no local combinado e aproveite',          'type' => 'textárea', 'max' => 300],
                ],
            ],
            [
                'title'    => 'Seção Contato',
                'icon'     => 'fas fa-envelope',
                'descrição' => 'Textos da seção de contato ao final da página.',
                'fields'   => [
                    ['name' => 'contato_label_superior', 'label' => 'Etiqueta acima do título', 'hint' => 'Ex: Estamos aqui para ajudar',    'type' => 'text', 'max' => 60],
                    ['name' => 'contato_titulo',         'label' => 'Título da seção',           'hint' => 'Ex: Entre em contato',             'type' => 'text', 'max' => 160],
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

            {{-- Cabeçalho --}}
            <div class="config-card-header">
                <div class="config-header-left">
                    @if(!empty($empresa->logo_url))
                        <div class="config-header-logo">
                            <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome ?? '' }}">
                        </div>
                    @else
                        <div class="config-header-logo config-header-logo--empty">
                            <i class="fas fa-globe-americas"></i>
                        </div>
                    @endif
                    <div>
                        <div class="config-header-eyebrow">Site de Apresentação</div>
                        <h4 class="config-header-title">{{ $empresa->nome ?? 'Configurar site' }}</h4>
                        <p class="config-header-sub">Personalize o site público desta empresa passo a passo.</p>
                    </div>
                </div>
                <div class="config-header-right">
                    @if(!empty($empresa->domínio))
                        <div class="config-domain-badge">
                            <i class="fas fa-wifi"></i>
                            <span>{{ $empresa->domínio }}</span>
                        </div>
                        <a href="https://{{ $empresa->domínio }}" target="_blank" class="config-btn-preview">
                            <i class="fas fa-external-link-alt"></i>
                            <span>Ver site</span>
                        </a>
                    @else
                        <div class="config-domain-badge config-domain-badge--empty">
                            <i class="fas fa-unlink"></i>
                            <span>Sem domínio</span>
                        </div>
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
                            <p>Configure o domínio, situação, modelo de layout, textos padrão e logo da empresa.</p>
                        </div>

                        {{-- Bloco: Publicação --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-globe"></i>
                                <div>
                                    <strong>Publicação do site</strong>
                                    <span>Endereço e situação do site na internet</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="domínio">Domínio da empresa</label>
                                    <input type="text" class="form-control form-control-sm" id="domínio" name="domínio"
                                        value="{{ old('domínio', $empresa->domínio ?? '') }}"
                                        placeholder="Ex: minhalocadora.com.br">
                                    <small class="field-hint-text">Endereço que os clientes vao usar para acessar o site.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="site_apresentacao_ativo">Situação do site</label>
                                    @php $statusAtual = old('site_apresentacao_ativo', $empresa->site_apresentacao_ativo ?: 'S'); @endphp
                                    <select class="form-control form-control-sm" id="site_apresentacao_ativo" name="site_apresentacao_ativo">
                                        @foreach($status as $value => $label)
                                            <option value="{{ $value }}" {{ $statusAtual === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="field-hint-text">Quando <strong>Ativo</strong>, o site fica visível ao público.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Logo --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-image"></i>
                                <div>
                                    <strong>Logo da empresa</strong>
                                    <span>Aparece no menu de navegação e no rodapé do site</span>
                                </div>
                            </div>
                            <div class="row align-itêms-center">
                                <div class="col-md-6">
                                    <label for="logo">Enviar nova logo</label>
                                    <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                                    <small class="field-hint-text">Formatos aceitos: JPG, PNG, WEBP ou SVG. Tamanho máximo: 2MB.</small>
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

                        
                        {{-- Bloco: Modelo de layout --}}
                        <div class="config-block">
                            <div class="config-block-label">
                                <i class="fas fa-desktop"></i>
                                <div>
                                    <strong>Modelo de layout</strong>
                                    <span>Escolha o design visual do site de apresentação</span>
                                </div>
                            </div>
                            <input type="hidden" id="site_apresentacao_modelo" name="site_apresentacao_modelo" value="{{ $modeloAtual }}">
                            <div class="model-grid">
                                @foreach($modelCards as $modelCard)
                                    @php $isEnabled = $modelCard['status'] === 'Disponível'; @endphp
                                    <div class="model-card {{ $modeloAtual === $modelCard['valor'] ? 'active' : '' }} {{ !$isEnabled ? 'disabled' : '' }}"
                                        data-model-card="{{ $modelCard['valor'] }}"
                                        role="button"
                                        tabindex="{{ $isEnabled ? 0 : -1 }}"
                                        title="{{ !$isEnabled ? 'Em breve' : '' }}">
                                        <div class="model-card-top">
                                            <i class="{{ $modelCard['icon'] }} model-icon"></i>
                                            <span class="model-status {{ $isEnabled ? 'available' : 'soon' }}">{{ $modelCard['status'] }}</span>
                                        </div>
                                        <h6>{{ $modelCard['título'] }}</h6>
                                        <p>{{ $modelCard['descrição'] }}</p>
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
                            <p>Personalize todos os textos que aparecem em cada seção do site. Clique em uma seção para editar.</p>
                        </div>

                        <div class="text-accordion">
                            @foreach($textGroups as $groupIndex => $group)
                                <div class="text-acc-itêm {{ $groupIndex === 0 ? 'open' : '' }}" id="acc-group-{{ $groupIndex }}">
                                    <button type="button" class="text-acc-header" data-acc-toggle="{{ $groupIndex }}">
                                        <div class="text-acc-header-left">
                                            <i class="{{ $group['icon'] }} text-acc-icon"></i>
                                            <div>
                                                <span class="text-acc-title">{{ $group['title'] }}</span>
                                                <span class="text-acc-desc">{{ $group['descrição'] }}</span>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down text-acc-arrow"></i>
                                    </button>
                                    <div class="text-acc-body">
                                        <div class="text-fields-grid">
                                            @foreach($group['fields'] as $field)
                                                @php $isTextárea = $field['type'] === 'textárea'; @endphp
                                                <div class="text-field-itêm {{ $isTextárea ? 'text-field-full' : '' }}">
                                                    <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                                                    @if($isTextárea)
                                                        <textárea class="form-control form-control-sm"
                                                            id="{{ $field['name'] }}"
                                                            name="{{ $field['name'] }}"
                                                            rows="3"
                                                            maxlength="{{ $field['max'] }}"
                                                            placeholder="{{ $field['hint'] ?? '' }}">{{ $texto($field['name']) }}</textárea>
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
                                    'descrição' => 'As 3 cores base da empresa. São usadas em toda a página.',
                                    'itêms'    => [
                                        [
                                            'name' => 'cor_primaria',
                                            'label' => 'Cor primária',
                                            'default' => $siteCores['brand_primaria'] ?? ($empresa->cor_primaria ?: '#1F2937'),
                                            'hint' => 'Principal da marca: usada nos botões e destaques.',
                                        ],
                                        [
                                            'name' => 'cor_secundaria',
                                            'label' => 'Cor secundária',
                                            'default' => $siteCores['brand_secundaria'] ?? ($empresa->cor_secundaria ?: '#334155'),
                                            'hint' => 'Complementa a primária em cards e links.',
                                        ],
                                        [
                                            'name' => 'cor_terciaria',
                                            'label' => 'Cor terciária',
                                            'default' => $siteCores['brand_terciaria'] ?? ($empresa->cor_terciaria ?: '#64748B'),
                                            'hint' => 'Detalhes sutis e fundos suaves.',
                                        ],
                                    ],
                                ],
                                [
                                    'title'    => 'Topo / Navegação',
                                    'icon'     => 'fas fa-window-maximize',
                                    'descrição' => 'Barra de menu no topo da página.',
                                    'itêms'    => [
                                        ['name' => 'cor_topbar_fundo',      'label' => 'Fundo do menu',    'default' => '#FFFFFF'],
                                        ['name' => 'cor_topbar_texto',      'label' => 'Texto do menu',    'default' => '#0F172A'],
                                        ['name' => 'cor_topbar_link_hover', 'label' => 'Cor ao passar o mouse', 'default' => '#1F2937'],
                                    ],
                                ],
                                [
                                    'title'    => 'Seção Principal (Banner)',
                                    'icon'     => 'fas fa-image',
                                    'descrição' => 'Grande banner no topo da página.',
                                    'itêms'    => [
                                        ['name' => 'cor_hero_fundo', 'label' => 'Cor de fundo', 'default' => '#1F2937'],
                                        ['name' => 'cor_hero_texto', 'label' => 'Cor do texto', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Formulário de Reserva',
                                    'icon'     => 'fas fa-calendar-alt',
                                    'descrição' => 'Caixa de reserva dentro do banner.',
                                    'itêms'    => [
                                        ['name' => 'cor_form_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_form_texto', 'label' => 'Cor do texto', 'default' => '#0F172A'],
                                    ],
                                ],
                                [
                                    'title'    => 'Seção Frota',
                                    'icon'     => 'fas fa-car',
                                    'descrição' => 'Área que exibe os veículos disponíveis.',
                                    'itêms'    => [
                                        ['name' => 'cor_frota_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Seção Vantagens',
                                    'icon'     => 'fas fa-thumbs-up',
                                    'descrição' => 'Área de diferenciais da empresa.',
                                    'itêms'    => [
                                        ['name' => 'cor_vantagens_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Seção Como Funciona (Etapas)',
                                    'icon'     => 'fas fa-list-ol',
                                    'descrição' => 'Área do passo a passo da reserva.',
                                    'itêms'    => [
                                        ['name' => 'cor_etapas_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Seção Contato',
                                    'icon'     => 'fas fa-envelope',
                                    'descrição' => 'Área de contato ao final da página.',
                                    'itêms'    => [
                                        ['name' => 'cor_contato_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                    ],
                                ],
                                [
                                    'title'    => 'Botões',
                                    'icon'     => 'fas fa-hand-pointer',
                                    'descrição' => 'Botões de ação em toda a página.',
                                    'itêms'    => [
                                        ['name' => 'cor_botao_primario_fundo',    'label' => 'Botao principal: fundo',    'default' => '#E7B93A'],
                                        ['name' => 'cor_botao_primario_texto',    'label' => 'Botao principal: texto',    'default' => '#111827'],
                                        ['name' => 'cor_botao_secundario_fundo',  'label' => 'Botao secundario: fundo',  'default' => '#FFFFFF'],
                                        ['name' => 'cor_botao_secundario_texto',  'label' => 'Botao secundario: texto',  'default' => '#1F2937'],
                                    ],
                                ],
                                [
                                    'title'    => 'Rodapé',
                                    'icon'     => 'fas fa-minus',
                                    'descrição' => 'Barra inferior da página.',
                                    'itêms'    => [
                                        ['name' => 'cor_footer_fundo', 'label' => 'Cor de fundo', 'default' => '#FFFFFF'],
                                        ['name' => 'cor_footer_texto', 'label' => 'Cor do texto', 'default' => '#64748B'],
                                    ],
                                ],
                                [
                                    'title'    => 'Tela Administrativa',
                                    'icon'     => 'fas fa-shield-alt',
                                    'descrição' => 'Painel administrativo acessado pelo domínio da empresa.',
                                    'itêms'    => [
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
                                            <span class="color-group-desc">{{ $group['descrição'] }}</span>
                                        </div>
                                    </div>
                                    <div class="color-itêms">
                                        @foreach($group['itêms'] as $itêm)
                                            @php
                                                $colorValue = old($itêm['name'], str_starts_with($itêm['name'], 'cor_') && !in_array($itêm['name'], ['cor_primaria', 'cor_secundaria', 'cor_terciaria'], true)
                                                    ? ($siteCores[str_replace('cor_', '', $itêm['name'])] ?? $itêm['default'])
                                                    : $itêm['default']);
                                            @endphp
                                            <div class="color-itêm">
                                                <label for="{{ $itêm['name'] }}">{{ $itêm['label'] }}</label>
                                                <div class="color-field">
                                                    <input type="color" class="color-picker" id="{{ $itêm['name'] }}" name="{{ $itêm['name'] }}" value="{{ $colorValue }}">
                                                    <input type="text" class="form-control form-control-sm color-text" data-color-text="{{ $itêm['name'] }}" value="{{ $colorValue }}" maxlength="7" placeholder="#000000">
                                                </div>
                                                @if(!empty($itêm['hint']))
                                                    <small class="color-hint">{{ $itêm['hint'] }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="color-preview-row mt-4">
                            <div class="color-preview-box" id="previewSite">
                                <div class="preview-label">Pre-visualização do site</div>
                                <div class="preview-sublabel">Cores primária, secundária e terciária</div>
                            </div>
                            <div class="color-preview-box" id="previewAdmin">
                                <div class="preview-label">Pre-visualização administrativa</div>
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
                                ['key' => 'vantagens','upload' => 'imagem_vantagens','toggle' => 'usar_imagem_vantagens','label' => 'Seção Vantagens',        'icon' => 'fas fa-thumbs-up',    'hint' => 'Recomendado: 1200 x 700 pixels'],
                                ['key' => 'contato', 'upload' => 'imagem_contato', 'toggle' => 'usar_imagem_contato', 'label' => 'Seção Contato',           'icon' => 'fas fa-envelope',     'hint' => 'Recomendado: 900 x 500 pixels'],
                            ];
                        @endphp

                        <div class="step-head">
                            <h5><i class="fas fa-images mr-2"></i>Imagens do Site</h5>
                            <p>Envie as fotos para cada seção do site. Use o botão <strong>Usar imagem</strong> para ativar ou desativar cada foto.</p>
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

                                    <div class="image-preview-área">
                                        @if(!empty($urlAtual))
                                            <img src="{{ $urlAtual }}" alt="{{ $field['label'] }}">
                                        @else
                                            <div class="image-empty">
                                                <i class="fas fa-upload"></i>
                                                <span>Nenhuma imagem enviada</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="image-upload-área">
                                        <label for="{{ $field['upload'] }}" class="upload-btn">
                                            <i class="fas fa-upload mr-1"></i>
                                            {{ !empty($urlAtual) ? 'Trocar imagem' : 'Enviar imagem' }}
                                        </label>
                                        <input type="file"
                                            class="upload-input"
                                            id="{{ $field['upload'] }}"
                                            name="{{ $field['upload'] }}"
                                            accept=".jpg,.jpeg,.png,.webp">
                                        <small class="field-hint-text">JPG, PNG ou WEBP — máx. 4MB<br>{{ $field['hint'] }}</small>
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
                            <p>Confira o checklist abaixo antes de salvar. Não e obrigatorio preencher tudo agora.</p>
                        </div>

                        <div class="publish-grid">
                            <div class="publish-card checklist-card">
                                <h6><i class="fas fa-clipboard-check mr-2"></i>Checklist essencial</h6>
                                <ul class="checklist">
                                    <li><i class="fas fa-check-circle"></i> Preencha o domínio da empresa (Passo 1)</li>
                                    <li><i class="fas fa-check-circle"></i> Coloque o site como <strong>Ativo</strong> (Passo 1)</li>
                                    <li><i class="fas fa-check-circle"></i> Envie a logo da empresa (Passo 1)</li>
                                    <li><i class="fas fa-check-circle"></i> Preencha o título e subtítulo do banner (Passo 2)</li>
                                    <li><i class="fas fa-check-circle"></i> Escolha as cores principais da empresa (Passo 3)</li>
                                    <li><i class="fas fa-check-circle"></i> Envie pelo menos a imagem do banner (Passo 4)</li>
                                </ul>
                            </div>

                            <div class="publish-card tips-card">
                                <h6><i class="fas fa-lightbulb mr-2"></i>Dicas para um bom resultado</h6>
                                <ul class="checklist tips-list">
                                    <li><i class="fas fa-info-circle"></i> Use imagens com boa resolução para o site ficar bonito</li>
                                    <li><i class="fas fa-info-circle"></i> Se nao tiver imagens agora, desmarque os toggles no Passo 4</li>
                                    <li><i class="fas fa-info-circle"></i> Salve e abra o domínio para conferir o resultado</li>
                                    <li><i class="fas fa-info-circle"></i> Voce pode voltar e editar qualquer informação a qualquer momento</li>
                                </ul>
                            </div>
                        </div>

                        <div class="publish-faq">
                            <details class="faq-itêm">
                                <summary><i class="fas fa-question-circle mr-2"></i>Como vejo o site publicado?</summary>
                                <div class="faq-body">
                                    Acesse o domínio configurado no Passo 1
                                    @if(!empty($empresa->domínio))
                                        (<a href="https://{{ $empresa->domínio }}" target="_blank"><strong>{{ $empresa->domínio }}</strong></a>)
                                    @endif
                                    pelo navegador após salvar.
                                </div>
                            </details>
                            <details class="faq-itêm">
                                <summary><i class="fas fa-question-circle mr-2"></i>Preciso preencher todos os campos?</summary>
                                <div class="faq-body">
                                    Não. Os campos do Passo 1 são os mais importantes. Os textos das seções (Passo 2) têm valores padrão quando nao preenchidos.
                                </div>
                            </details>
                            <details class="faq-itêm">
                                <summary><i class="fas fa-question-circle mr-2"></i>O que é a tela administrativa?</summary>
                                <div class="faq-body">
                                    E a tela de login do sistêma. Quando o cliente acessar <code>/administrativa</code> pelo domínio da empresa, verá a logo e as cores definidas no Passo 3 (Tela Administrativa).
                                </div>
                            </details>
                        </div>

                        @if(!empty($empresa->domínio))
                            <div class="publish-action-row">
                                <a href="https://{{ $empresa->domínio }}" target="_blank" class="btn-preview-site">
                                    <i class="fas fa-external-link-alt mr-2"></i>Abrir site para conferir
                                </a>
                            </div>
                        @endif
                    </section>

                    {{-- Ações do wizard --}}
                    <div class="wizard-actions">
                        <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                            <i class="fas fa-arrow-left mr-1"></i>Voltar
                        </button>
                        <div class="wizard-actions-right">
                            <button type="button" class="btn-action btn-action-primary" id="wizardNext">
                                Continuar<i class="fas fa-arrow-right ml-1"></i>
                            </button>
                            <button type="submit" class="btn-action btn-action-success" id="wizardSave">
                                <i class="fas fa-save mr-1"></i>Salvar configurações
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
       VARIAVEIS E BASE
    ================================================ */
    :root {
        --c-ink:      #0c1428;
        --c-navy:     #1a2848;
        --c-indigo:   #4338ca;
        --c-blue:     #2563eb;
        --c-sky:      #0ea5e9;
        --c-green:    #10b981;
        --c-amber:    #f59e0b;
        --c-red:      #ef4444;
        --c-slate:    #64748b;
        --c-border:   #e2e9f4;
        --c-bg:       #f2f5fb;
        --c-card:     #ffffff;
        --r-card:     18px;
        --r-inner:    12px;
        --shadow-sm:  0 2px 8px rgba(12, 20, 40, 0.07);
        --shadow-md:  0 6px 24px rgba(12, 20, 40, 0.10);
        --shadow-lg:  0 16px 48px rgba(12, 20, 40, 0.13);
    }

    .content-wrapper { background: var(--c-bg); }

    /* ================================================
       CARD PRINCIPAL
    ================================================ */
    .config-card {
        border-radius: 20px;
        overflow: hidden;
        background: var(--c-card);
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--c-border);
    }

    /* ================================================
       HEADER ESCURO
    ================================================ */
    .config-card-header {
        position: relative;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 1.75rem 2rem;
        display: flex;
        align-itêms: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        overflow: hidden;
    }

    /* Padrão de pontos no header */
    .config-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 22px 22px;
        pointer-events: none;
    }

    /* Linha destaque no topo do header */
    .config-card-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }

    .config-header-left {
        position: relative;
        display: flex;
        align-itêms: center;
        gap: 16px;
        z-index: 1;
    }

    .config-header-logo {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        display: flex;
        align-itêms: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }

    .config-header-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 8px;
    }

    .config-header-logo--empty {
        font-size: 1.4rem;
        color: rgba(255,255,255,0.4);
    }

    .config-header-eyebrow {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, 0.8);
        margin-bottom: 3px;
    }

    .config-header-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -0.02em;
    }

    .config-header-sub {
        font-size: 0.78rem;
        color: rgba(148, 163, 184, 0.75);
        margin: 0;
    }

    .config-header-right {
        position: relative;
        display: flex;
        align-itêms: center;
        gap: 10px;
        flex-wrap: wrap;
        z-index: 1;
    }

    .config-domain-badge {
        display: flex;
        align-itêms: center;
        gap: 6px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        padding: 7px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        color: rgba(226,232,240,0.9);
        backdrop-filter: blur(6px);
    }

    .config-domain-badge--empty {
        color: rgba(148,163,184,0.6);
    }

    .config-btn-preview {
        display: inline-flex;
        align-itêms: center;
        gap: 7px;
        background: linear-gradient(135deg, #4338ca 0%, #0ea5e9 100%);
        color: #fff;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        transition: opacity 0.2s, transform 0.2s;
        box-shadow: 0 4px 14px rgba(67,56,202,0.35);
    }

    .config-btn-preview:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #fff;
        text-decoration: none;
    }

    .config-body {
        padding: 1.75rem;
        background: var(--c-bg);
    }

    /* ================================================
       STEP INDICATOR
    ================================================ */
    .step-indicator {
        display: flex;
        align-itêms: center;
        gap: 0;
        margin-bottom: 2rem;
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 14px;
        padding: 6px;
        overflow-x: auto;
    }

    .step-btn {
        display: flex;
        align-itêms: center;
        gap: 10px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 10px 16px;
        border-radius: 10px;
        flex-shrink: 0;
        transition: background 0.2s, color 0.2s;
        min-width: 0;
    }

    .step-btn:hover {
        background: #f1f5ff;
    }

    .step-num {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #d0dff0;
        background: #f4f8ff;
        color: #94a3b8;
        font-weight: 800;
        font-size: 0.78rem;
        display: flex;
        align-itêms: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.25s;
    }

    .step-btn.active {
        background: #eff3ff;
    }

    .step-btn.active .step-num {
        border-color: transparent;
        background: linear-gradient(135deg, var(--c-indigo), var(--c-sky));
        color: #fff;
        box-shadow: 0 3px 10px rgba(67,56,202,0.35);
    }

    .step-btn.completed .step-num {
        border-color: transparent;
        background: var(--c-green);
        color: #fff;
    }

    .step-label-text {
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
        display: flex;
        align-itêms: center;
        gap: 5px;
    }

    .step-btn.active .step-label-text {
        color: var(--c-indigo);
        font-weight: 700;
    }

    .step-btn.completed .step-label-text {
        color: var(--c-green);
    }

    .step-divider {
        flex: 1;
        height: 1px;
        background: var(--c-border);
        min-width: 12px;
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
        animation: fadeSlideIn 0.22s ease;
    }

    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .step-head {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--c-border);
    }

    .step-head h5 {
        font-weight: 800;
        color: var(--c-ink);
        margin-bottom: 4px;
        font-size: 1.02rem;
        letter-spacing: -0.01em;
    }

    .step-head p {
        color: var(--c-slate);
        font-size: 0.83rem;
        margin: 0;
    }

    /* ================================================
       CONFIG BLOCKS (Step 1)
    ================================================ */
    .config-block {
        border: 1px solid var(--c-border);
        border-left: 3px solid var(--c-indigo);
        border-radius: 14px;
        padding: 1.1rem 1.3rem;
        background: #fff;
        margin-bottom: 1rem;
        transition: box-shadow 0.2s;
    }

    .config-block:hover {
        box-shadow: var(--shadow-sm);
    }

    .config-block-label {
        display: flex;
        align-itêms: flex-start;
        gap: 12px;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px dashed #e5eaf3;
    }

    .config-block-label > i {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #eef2ff;
        color: var(--c-indigo);
        display: flex;
        align-itêms: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .config-block-label strong {
        display: block;
        color: var(--c-ink);
        font-size: 0.88rem;
        font-weight: 700;
    }

    .config-block-label span {
        color: var(--c-slate);
        font-size: 0.76rem;
        margin-top: 2px;
        display: block;
    }

    .config-block label {
        font-size: 0.76rem;
        font-weight: 600;
        color: #3d4f63;
        margin-bottom: 4px;
        display: block;
    }

    .config-block .form-control,
    .text-field-itêm .form-control {
        border-radius: 9px;
        border-color: #dce6f4;
        font-size: 0.84rem;
        background: #fcfeff;
        transition: border-color 0.18s, box-shadow 0.18s;
    }

    .config-block .form-control:focus,
    .text-field-itêm .form-control:focus {
        border-color: var(--c-indigo);
        box-shadow: 0 0 0 3px rgba(67,56,202,0.10);
    }

    .field-hint-text {
        display: block;
        font-size: 0.71rem;
        color: #8fa5c0;
        margin-top: 4px;
        font-style: italic;
    }

    /* ================================================
       LOGO PREVIEW
    ================================================ */
    .logo-preview {
        min-height: 110px;
        border: 2px dashed #c8d9ee;
        border-radius: 12px;
        background: linear-gradient(135deg, #f6f9ff 0%, #eef3ff 100%);
        display: flex;
        align-itêms: center;
        justify-content: center;
        padding: 14px;
        transition: border-color 0.2s;
    }

    .logo-preview:hover {
        border-color: var(--c-indigo);
    }

    .logo-preview img {
        max-height: 80px;
        max-width: 100%;
        object-fit: contain;
    }

    .logo-empty {
        display: flex;
        flex-direction: column;
        align-itêms: center;
        gap: 8px;
        color: #b0c4de;
    }

    .logo-empty i { font-size: 2rem; }
    .logo-empty span { font-size: 0.76rem; }

    /* ================================================
       MODEL CARDS (Step 1)
    ================================================ */
    .model-grid {
        display: grid;
        grid-têmplate-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .model-card {
        border: 2px solid var(--c-border);
        border-radius: 14px;
        padding: 16px;
        background: #fff;
        cursor: pointer;
        transition: all 0.22s ease;
        position: relative;
        overflow: hidden;
        min-height: 140px;
    }

    /* Linha de cor no topo do card */
    .model-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--c-indigo), var(--c-sky));
        opacity: 0;
        transition: opacity 0.2s;
    }

    .model-card.active::before,
    .model-card:hover:not(.disabled)::before {
        opacity: 1;
    }

    .model-card:hover:not(.disabled) {
        border-color: #c7d4ff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(67,56,202,0.14);
    }

    .model-card.active {
        border-color: var(--c-indigo);
        background: #f5f4ff;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.12);
    }

    .model-card.disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .model-card-top {
        display: flex;
        align-itêms: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .model-icon {
        font-size: 1.15rem;
        color: var(--c-indigo);
    }

    .model-status {
        font-size: 0.65rem;
        font-weight: 800;
        border-radius: 999px;
        padding: 3px 8px;
        letter-spacing: 0.04em;
    }

    .model-status.available { background: #dcfce7; color: #15803d; }
    .model-status.soon      { background: #fef3c7; color: #92400e; }

    .model-card h6 {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--c-ink);
        margin-bottom: 5px;
        letter-spacing: -0.01em;
    }

    .model-card p {
        font-size: 0.74rem;
        color: var(--c-slate);
        line-height: 1.5;
        margin: 0;
    }

    .model-check {
        position: absolute;
        bottom: 12px;
        right: 14px;
        color: var(--c-indigo);
        font-size: 1.1rem;
        display: none;
    }

    .model-card.active .model-check { display: block; }

    /* ================================================
       TEXT ACCORDION (Step 2)
    ================================================ */
    .text-accordion {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .text-acc-itêm {
        border: 1px solid var(--c-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow 0.2s;
    }

    .text-acc-itêm.open {
        border-color: #c7d4ff;
        box-shadow: 0 4px 16px rgba(67,56,202,0.08);
    }

    .text-acc-header {
        width: 100%;
        background: #fafbff;
        border: none;
        display: flex;
        align-itêms: center;
        justify-content: space-between;
        padding: 13px 16px;
        cursor: pointer;
        text-align: left;
        transition: background 0.18s;
        gap: 12px;
    }

    .text-acc-itêm.open .text-acc-header {
        background: #f0f2ff;
        border-bottom: 1px solid #dce2ff;
    }

    .text-acc-header:hover { background: #f0f2ff; }

    .text-acc-header-left {
        display: flex;
        align-itêms: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }

    .text-acc-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #eef0ff;
        color: var(--c-indigo);
        display: flex;
        align-itêms: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
        transition: background 0.18s;
    }

    .text-acc-itêm.open .text-acc-icon {
        background: var(--c-indigo);
        color: #fff;
    }

    .text-acc-title {
        display: block;
        font-weight: 700;
        color: var(--c-ink);
        font-size: 0.86rem;
    }

    .text-acc-desc {
        display: block;
        font-size: 0.74rem;
        color: var(--c-slate);
        margin-top: 1px;
    }

    .text-acc-arrow {
        color: #b0c0d8;
        font-size: 0.78rem;
        flex-shrink: 0;
        transition: transform 0.25s, color 0.18s;
    }

    .text-acc-itêm.open .text-acc-arrow {
        transform: rotate(180deg);
        color: var(--c-indigo);
    }

    .text-acc-body {
        display: none;
        padding: 18px 16px;
        background: #fafbff;
    }

    .text-acc-itêm.open .text-acc-body { display: block; }

    .text-fields-grid {
        display: grid;
        grid-têmplate-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .text-field-itêm {
        display: flex;
        flex-direction: column;
    }

    .text-field-itêm label {
        font-size: 0.76rem;
        font-weight: 600;
        color: #3d4f63;
        margin-bottom: 4px;
    }

    .text-field-full { grid-column: 1 / -1; }

    /* ================================================
       COLORS (Step 3)
    ================================================ */
    .color-groups-grid {
        display: grid;
        grid-têmplate-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .color-group-card {
        border: 1px solid var(--c-border);
        border-radius: 14px;
        padding: 14px;
        background: #fff;
        transition: box-shadow 0.2s;
    }

    .color-group-card:hover {
        box-shadow: var(--shadow-sm);
    }

    .color-group-header {
        display: flex;
        align-itêms: flex-start;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e5eaf3;
    }

    .color-group-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #eef0ff;
        color: var(--c-indigo);
        display: flex;
        align-itêms: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .color-group-header strong {
        display: block;
        font-size: 0.82rem;
        color: var(--c-ink);
        font-weight: 700;
    }

    .color-group-desc {
        font-size: 0.71rem;
        color: var(--c-slate);
        display: block;
        margin-top: 1px;
    }

    .color-itêms {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .color-itêm label {
        font-size: 0.73rem;
        font-weight: 600;
        color: #3d4f63;
        margin-bottom: 5px;
        display: block;
    }

    .color-field {
        display: grid;
        grid-têmplate-columns: 42px 1fr;
        gap: 8px;
        align-itêms: center;
    }

    .color-hint {
        display: block;
        margin-top: 6px;
        font-size: 0.72rem;
        color: #6b7c90;
    }

    .color-picker {
        width: 42px;
        height: 38px;
        border-radius: 10px;
        border: 2px solid #dce6f4;
        padding: 2px;
        cursor: pointer;
        background: #fff;
        transition: border-color 0.18s;
    }

    .color-picker:hover { border-color: var(--c-indigo); }

    .color-text {
        border-radius: 9px;
        border-color: #dce6f4;
        font-size: 0.82rem;
        font-family: 'SFMono-Regular', Consolas, monospace;
        background: #fcfeff;
    }

    .color-preview-row {
        display: grid;
        grid-têmplate-columns: 1fr 1fr;
        gap: 12px;
    }

    .color-preview-box {
        border-radius: 14px;
        min-height: 100px;
        padding: 18px;
        border: 1px solid var(--c-border);
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: all 0.35s ease;
    }

    .preview-label {
        font-weight: 800;
        font-size: 0.9rem;
        margin-bottom: 4px;
        letter-spacing: -0.01em;
    }

    .preview-sublabel {
        font-size: 0.74rem;
        opacity: 0.75;
    }

    /* ================================================
       IMAGES (Step 4)
    ================================================ */
    .image-grid {
        display: grid;
        grid-têmplate-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .image-card {
        border: 1px solid var(--c-border);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .image-card:hover {
        box-shadow: var(--shadow-md);
    }

    .image-card-header {
        display: flex;
        align-itêms: center;
        justify-content: space-between;
        padding: 11px 14px;
        background: #f8faff;
        border-bottom: 1px solid var(--c-border);
        gap: 8px;
        flex-wrap: wrap;
    }

    .image-card-title {
        display: flex;
        align-itêms: center;
        gap: 8px;
        font-size: 0.82rem;
    }

    .image-card-title i { color: var(--c-indigo); }

    .image-card-title strong {
        color: var(--c-ink);
        font-weight: 700;
    }

    .image-preview-área {
        min-height: 150px;
        background: linear-gradient(135deg, #f6f8ff 0%, #eef1ff 100%);
        display: flex;
        align-itêms: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .image-preview-área img {
        width: 100%;
        max-height: 190px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .image-card:hover .image-preview-área img {
        transform: scale(1.02);
    }

    .image-empty {
        display: flex;
        flex-direction: column;
        align-itêms: center;
        gap: 8px;
        color: #c0cfe4;
        padding: 24px;
    }

    .image-empty i { font-size: 2.2rem; }
    .image-empty span { font-size: 0.76rem; }

    .image-upload-área {
        padding: 12px 14px;
        border-top: 1px solid var(--c-border);
        background: #fafbff;
    }

    .upload-btn {
        display: inline-flex;
        align-itêms: center;
        gap: 6px;
        background: #eef0ff;
        border: 1px solid #c7d0ff;
        border-radius: 8px;
        padding: 7px 14px;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--c-indigo);
        cursor: pointer;
        transition: all 0.18s;
        margin-bottom: 6px;
    }

    .upload-btn:hover {
        background: var(--c-indigo);
        border-color: var(--c-indigo);
        color: #fff;
    }

    .upload-input { display: none; }

    /* ================================================
       PUBLISH (Step 5)
    ================================================ */
    .publish-grid {
        display: grid;
        grid-têmplate-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 1.25rem;
    }

    .publish-card {
        border: 1px solid var(--c-border);
        border-radius: 14px;
        padding: 18px;
        background: #fff;
    }

    .checklist-card {
        border-left: 3px solid var(--c-green);
    }

    .tips-card {
        border-left: 3px solid var(--c-sky);
    }

    .publish-card h6 {
        font-weight: 800;
        color: var(--c-ink);
        font-size: 0.88rem;
        margin-bottom: 14px;
        letter-spacing: -0.01em;
    }

    .checklist {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .checklist li {
        display: flex;
        align-itêms: flex-start;
        gap: 9px;
        font-size: 0.82rem;
        color: #334155;
        line-height: 1.45;
    }

    .checklist-card .checklist li i {
        color: var(--c-green);
        margin-top: 2px;
        flex-shrink: 0;
    }

    .tips-list li i {
        color: var(--c-sky);
        margin-top: 2px;
        flex-shrink: 0;
    }

    .publish-faq {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 1.25rem;
    }

    .faq-itêm {
        border: 1px solid var(--c-border);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .faq-itêm summary {
        padding: 12px 16px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--c-navy);
        cursor: pointer;
        background: #fafbff;
        list-style: none;
        display: flex;
        align-itêms: center;
        transition: background 0.18s;
    }

    .faq-itêm summary::-webkit-details-marker { display: none; }

    .faq-itêm summary:hover { background: #f0f2ff; }

    .faq-itêm[open] summary {
        background: #f0f2ff;
        border-bottom: 1px solid #dce2ff;
        color: var(--c-indigo);
    }

    .faq-body {
        padding: 12px 16px;
        font-size: 0.81rem;
        color: #475569;
        line-height: 1.55;
    }

    .faq-body code {
        background: #f0f2ff;
        color: var(--c-indigo);
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
        background: linear-gradient(135deg, var(--c-indigo) 0%, var(--c-sky) 100%);
        color: #fff;
        border-radius: 12px;
        padding: 13px 32px;
        font-size: 0.9rem;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-itêms: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 6px 20px rgba(67,56,202,0.28);
        letter-spacing: -0.01em;
    }

    .btn-preview-site:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(67,56,202,0.38);
    }

    /* ================================================
       WIZARD ACTIONS
    ================================================ */
    .wizard-actions {
        margin-top: 2rem;
        padding-top: 1.1rem;
        border-top: 1px solid var(--c-border);
        display: flex;
        justify-content: space-between;
        align-itêms: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .wizard-actions-right {
        display: flex;
        gap: 8px;
        align-itêms: center;
    }

    .btn-action {
        display: inline-flex;
        align-itêms: center;
        gap: 6px;
        border: none;
        border-radius: 10px;
        padding: 11px 22px;
        font-size: 0.84rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s;
        text-decoration: none;
        letter-spacing: -0.01em;
    }

    .btn-action-secondary {
        background: #fff;
        color: #475569;
        border: 1px solid var(--c-border);
        box-shadow: var(--shadow-sm);
    }

    .btn-action-secondary:hover {
        background: #f1f5f9;
        color: var(--c-ink);
        text-decoration: none;
    }

    .btn-action-primary {
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        color: #fff;
        box-shadow: 0 4px 12px rgba(67,56,202,0.28);
    }

    .btn-action-primary:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        color: #fff;
        text-decoration: none;
    }

    .btn-action-success {
        background: linear-gradient(135deg, var(--c-green), #059669);
        color: #fff;
        box-shadow: 0 4px 12px rgba(16,185,129,0.28);
    }

    .btn-action-success:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #fff;
        text-decoration: none;
    }

    /* ================================================
       RESPONSIVO
    ================================================ */
    @media (max-width: 1100px) {
        .model-grid {
            grid-têmplate-columns: repeat(2, 1fr);
        }

        .color-groups-grid {
            grid-têmplate-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .model-grid,
        .image-grid,
        .publish-grid,
        .color-preview-row {
            grid-têmplate-columns: 1fr;
        }

        .text-fields-grid {
            grid-têmplate-columns: 1fr;
        }

        .step-btn {
            padding: 8px 12px;
        }

        .step-label-text span {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .config-card-header {
            flex-direction: column;
            align-itêms: flex-start;
        }

        .config-body {
            padding: 1.1rem;
        }

        .config-header-right {
            width: 100%;
            justify-content: flex-start;
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

        .step-indicator {
            padding: 4px;
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
            const itêms = Array.from(document.querySelectorAll('.text-acc-itêm'));

            itêms.forEach(itêm => {
                const header = itêm.querySelector('.text-acc-header');
                if (!header) return;

                header.addEventListener('click', () => {
                    const isOpen = itêm.classList.contains('open');
                    itêm.classList.toggle('open', !isOpen);
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
