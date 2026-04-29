<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tí­tulo
    |--------------------------------------------------------------------------
    |
    | Aqui você pode alterar o título padrão do seu painel de administração.
    |
    | Para instruções detalhadas, você pode consultar a seção de título aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'DECARS',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Aqui você pode ativar o favicon.
    |
    | Para instruções detalhadas, você pode consultar a seção de favicon aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Fontes do Google
    |--------------------------------------------------------------------------
    |
    | Aqui você pode permitir ou não o uso de fontes externas do Google. Desativar as
    | fontes do Google pode ser útil se o acesso à internet do seu painel de administração
    | for restrito de alguma forma.
    |
    | Para instruções detalhadas, você pode consultar a seção de fontes do Google aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo do Painel de Administração
    |--------------------------------------------------------------------------
    |
    | Aqui você pode alterar o logo do seu painel de administração.
    |
    | Para instruções detalhadas, você pode consultar a seção de logo aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>DECARS</b>',
    'logo_img' => 'assets/imgs/logo-sem-texto.png',
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Logo DECARS',

    /*
    |--------------------------------------------------------------------------
    | Logo de Autenticação
    |--------------------------------------------------------------------------
    |
    | Aqui você pode configurar um logo alternativo para usar nas telas de login e registro.
    | Quando desativado, o logo do painel de administração será usado.
    |
    | Para instruções detalhadas, você pode consultar a seção de logo de autenticação aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'public/assets/imgs/logo-sem-texto.png',
            'alt' => 'Logo de Autenticação',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Animação de Pré-carregamento
    |--------------------------------------------------------------------------
    |
    | Aqui você pode alterar a configuração da animação de pré-carregamento. Atualmente, dois
    | modos são suportados: 'fullscreen' para uma animação de pré-carregamento em tela cheia
    | e 'cwrapper' para anexar a animação de pré-carregamento ao elemento content-wrapper
    | e evitar sobreposição com as barras laterais e a barra superior.
    |
    | Para instruções detalhadas, você pode consultar a seção de pré-carregamento aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'cwrapper',
        'img' => [
            'path' => 'public/assets/imgs/logo-sem-texto.png',
            'alt' => 'Imagem de Pré-carregamento DECARS',
            'effect' => 'animation__shake',
            'width' => 160,
            'height' => 160,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu do Usuário
    |--------------------------------------------------------------------------
    |
    | Aqui você pode ativar e alterar o menu do usuário.
    |
    | Para instruções detalhadas, você pode consultar a seção de menu do usuário aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'bg-gradient-dark',
    'classes_auth_header' => '',
    'classes_auth_body' => 'bg-gradient-dark',
    'classes_auth_footer' => 'text-left text-light',
    'classes_auth_icon' => 'fa-fw text-light',
    'classes_auth_btn' => 'btn-flat btn-light',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-light-white',
    'classes_sidebar_nav' => 'nav-child-indent nav-compact text-sm',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => false,
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/home',
    'logout_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/logout',
    'login_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/login',
    'register_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/register',
    'password_reset_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/forgot-password',
    'password_email_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/forgot-password',
    'profile_url' => trim((string) config('app.admin_prefix', 'admin'), '/') . '/profile',

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        [
            'type' => 'navbar-notification',
            'id' => 'navbar-notificacoes',
            'icon' => 'far fa-bell',
            'icon_color' => 'secondary',
            'label' => 0,
            'label_color' => 'warning',
            'route' => 'notificacoes.index',
            'topnav_right' => true,
            'dropdown_mode' => true,
            'dropdown_flabel' => 'Ver todas',
            'update_cfg' => [
                'route' => 'notificacoes.navbar',
                'period' => 60,
            ],
        ],
        [
            'type'         => 'navbar-search',
            'text'         => 'search',
            'topnav_right' => false,
        ],
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => false,
        ],
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Procurar...',
        ],

        // Dashboard
        [
            'text' => 'sidebar.home',
            'url'  => 'home',
            'icon' => 'fas fa-tachometer-alt',
        ],
        [
            'text'  => 'Indicadores',
            'route' => 'indicadores.index',
            'icon'  => 'fas fa-chart-bar',
        ],
        [
            'text'  => 'Meu Plano',
            'route' => 'assinatura.meu-plano',
            'icon'  => 'fas fa-id-card',
        ],
        [
            'header'  => 'Atalhos rápidos',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
        ],
        [
            'text'  => 'Nova Reserva',
            'route' => 'reserva.create',
            'icon'  => 'fas fa-plus-circle',
            'can'   => 'reserva.create',
        ],
        [
            'text'  => 'Novo Veículo',
            'route' => 'veiculo.create',
            'icon'  => 'fas fa-car-side',
            'can'   => 'veiculo.create',
        ],
        [
            'text'  => 'Nova Estação',
            'route' => 'estacao.create',
            'icon'  => 'fas fa-map-marker-alt',
            'can'   => 'estacao.create',
        ],
        [
            'text'  => 'Nova Empresa',
            'route' => 'empresa.create',
            'icon'  => 'fas fa-building',
            'can'   => 'acessar-superadmin',
        ],
        [
            'text'  => 'Novo Usuário',
            'route' => 'usuario.create',
            'icon'  => 'fas fa-user-plus',
        ],
        [
            'text'  => 'Replicação de Base',
            'route' => 'replicacao_base.index',
            'icon'  => 'fas fa-copy',
            'can'   => 'acessar-superadmin',
        ],
        [
            'text'  => 'Sincronizar catálogo FIPE',
            'route' => 'fipe.catalogo.run',
            'icon'  => 'fas fa-car',
            'can'   => 'gerenciar-empresa',
        ],

        // OPERAÇÃO
        [
            'header'  => 'sidebar.operacao',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
        ],
        [
            'text'    => 'sidebar.reservas_atendimento',
            'icon'    => 'fas fa-calendar-check',
            'submenu' => [
                [
                    'text' => 'sidebar.reserva',
                    'url'  => 'reserva',
                    'icon' => 'fas fa-list',
                    'can'  => 'reserva.index',
                ],
                [
                    'text' => 'sidebar.nova_reserva',
                    'route' => 'reserva.create',
                    'icon' => 'fas fa-plus-circle',
                    'can'  => 'reserva.create',
                ],
                [
                    'text' => 'sidebar.minhas_reservas',
                    'url'  => 'reserva/minhas',
                    'icon' => 'fas fa-bookmark',
                    'can'  => 'reserva.minhas',
                ],
            ],
        ],
        [
            'text' => 'sidebar.notificacoes',
            'route' => 'notificacoes.index',
            'icon' => 'far fa-bell',
        ],
        [
            'text'    => 'Multas',
            'icon'    => 'fas fa-exclamation-triangle',
            'can'     => 'multa.index',
            'submenu' => [
                [
                    'text' => 'Listagem',
                    'route' => 'multa.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Novo',
                    'route' => 'multa.create',
                    'icon' => 'fas fa-plus-circle',
                ],
            ],
        ],
        [
            'text'    => 'Sinistros',
            'icon'    => 'fas fa-car-crash',
            'can'     => 'sinistro.index',
            'submenu' => [
                [
                    'text' => 'Listagem',
                    'route' => 'sinistro.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Novo',
                    'route' => 'sinistro.create',
                    'icon' => 'fas fa-plus-circle',
                ],
            ],
        ],
        [
            'text'    => 'Config. Movimentações',
            'icon'    => 'fas fa-route',
            'submenu' => [
                [
                    'text' => 'Movimentações',
                    'icon' => 'fas fa-list',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'movimentacao.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Nova',
                            'route' => 'movimentacao.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Tipos de Movimentações',
                    'icon' => 'fas fa-random',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'tipo_movimentacao.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'tipo_movimentacao.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Status de Movimentações',
                    'icon' => 'fas fa-circle-notch',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'status_movimentacao.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'status_movimentacao.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'sidebar.tempo_estacoes',
                    'url'  => 'tempo_entre_entacao',
                    'icon' => 'fas fa-stopwatch',
                ],
            ],
        ],
        [
            'header'  => 'sidebar.frota',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
        ],
        [
            'text'    => 'sidebar.veiculo',
            'icon'    => 'fas fa-car',
            'submenu' => [
                [
                    'text' => 'Listagem de Veículos',
                    'route' => 'veiculo.index',
                    'icon' => 'fas fa-car-side',
                ],
                [
                    'text' => 'Novo Veículo',
                    'route' => 'veiculo.create',
                    'icon' => 'fas fa-plus-circle',
                ],
                [
                    'text' => 'Disponibilidade',
                    'route' => 'veiculo.disponibilidade',
                    'icon' => 'fas fa-calendar-alt',
                ],
            ],
        ],
        [
            'text'    => 'sidebar.parametrizacao_frota',
            'icon'    => 'fas fa-cogs',
            'submenu' => [
                [
                    'text' => 'Grupos de Frota',
                    'icon' => 'fas fa-layer-group',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'grupo_frota.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'grupo_frota.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Tipos de Frota',
                    'icon' => 'fas fa-layer-group',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'tipo_frota.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'tipo_frota.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Características de Frota',
                    'icon' => 'fas fa-th-large',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'caracteristica_frota.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Nova',
                            'route' => 'caracteristica_frota.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Valores da Característica',
                    'icon' => 'fas fa-tags',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'tipo_valor_caracteristica_frota.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'tipo_valor_caracteristica_frota.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Marcas',
                    'icon' => 'fas fa-tag',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'marca.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Nova',
                            'route' => 'marca.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Modelos',
                    'icon' => 'fas fa-car-side',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'modelo.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'modelo.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Segmentos',
                    'icon' => 'fas fa-stream',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'segmento.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'segmento.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Status de Veículo',
                    'icon' => 'fas fa-traffic-light',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'status_veiculo.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'status_veiculo.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
            ],
        ],
        [
            'text'    => 'sidebar.opcionais',
            'icon'    => 'fas fa-puzzle-piece',
            'submenu' => [
                [
                    'text' => 'Opcionais',
                    'icon' => 'fas fa-toolbox',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'opcional.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'opcional.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Tipos de Opcionais',
                    'icon' => 'fas fa-layer-group',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'tipo_opcional.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'tipo_opcional.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Vínculos de Opcionais por Grupo',
                    'icon' => 'fas fa-link',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'opcional_grupo_veiculo.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'opcional_grupo_veiculo.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
            ],
        ],
        [
            'text'    => 'sidebar.dano',
            'icon'    => 'fas fa-tools',
            'submenu' => [
                [
                    'text' => 'Danos',
                    'icon' => 'fas fa-exclamation-triangle',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'dano.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'dano.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Categorias de Dano',
                    'icon' => 'fas fa-tags',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'categoria_dano.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Nova',
                            'route' => 'categoria_dano.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Monitoramento de Danos',
                    'url'  => 'veiculo-dano/monitoramento',
                    'icon' => 'fas fa-clipboard-list',
                ],
                                [
                    'text' => 'Ordens de Serviço',
                    'icon' => 'fas fa-file-alt',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'ordem-servico.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Nova',
                            'route' => 'ordem-servico.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],            ],
        ],
        [
            'text'    => 'sidebar.checklist',
            'icon'    => 'fas fa-clipboard-check',
            'submenu' => [
                [
                    'text' => 'Configuração de Checklist',
                    'icon' => 'fas fa-sliders-h',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'configuracao-checklist.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Nova',
                            'route' => 'configuracao-checklist.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Itens de Checklist',
                    'icon' => 'fas fa-tasks',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'item_checklist.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'item_checklist.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Tipos de Checklist',
                    'icon' => 'fas fa-list',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'tipo_checklist.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'tipo_checklist.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Status de Itens',
                    'icon' => 'fas fa-check-square',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'status_item_checklist.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'status_item_checklist.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
            ],
        ],

        // FINANCEIRO
        [
            'header'  => 'sidebar.financeiro',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
        ],
        [
            'text'    => 'sidebar.financeiro',
            'icon'    => 'fas fa-wallet',
            'submenu' => [
                [
                    'text'  => 'sidebar.financeiro_transferencias',
                    'route' => 'financeiro.index',
                    'icon'  => 'fas fa-exchange-alt',
                ],
                [
                    'text'  => 'sidebar.financeiro_reservas',
                    'route' => 'financeiro.reservas',
                    'icon'  => 'fas fa-receipt',
                ],
                [
                    'text'  => 'sidebar.notas_fiscais',
                    'route' => 'nfse.index',
                    'icon'  => 'fas fa-file-invoice',
                ],
                [
                    'text' => 'Cupons de Desconto',
                    'icon' => 'fas fa-percentage',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'cupom_desconto.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'cupom_desconto.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
            ],
        ],

        // CADASTROS E CONFIGURAÇÕES
        [
            'header'  => 'sidebar.configuracoes',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
        ],

        // PLANOS (SUPERADMIN)
        [
            'header'  => 'Planos',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
            'can'     => 'acessar-superadmin',
        ],
        [
            'text'    => 'Planos',
            'icon'    => 'fas fa-layer-group',
            'can'     => 'acessar-superadmin',
            'submenu' => [
                [
                    'text'  => 'Planos',
                    'route' => 'planos.hub',
                    'icon'  => 'fas fa-layer-group',
                ],
                [
                    'text'  => 'Política de Acesso',
                    'route' => 'plano.index',
                    'icon'  => 'fas fa-user-shield',
                ],
                [
                    'text'  => 'Regras',
                    'route' => 'regra.index',
                    'icon'  => 'fas fa-key',
                ],
                [
                    'text'  => 'Empresas',
                    'route' => 'empresa.index',
                    'icon'  => 'fas fa-building',
                ],
                [
                    'text'  => 'Permissões',
                    'route' => 'permissao.index',
                    'icon'  => 'fas fa-lock',
                ],
                [
                    'text'  => 'Perfis de Usuários',
                    'route' => 'tipo_usuario.index',
                    'icon'  => 'fas fa-user-tag',
                ],
                [
                    'text'  => 'Replicação de Base',
                    'route' => 'replicacao_base.index',
                    'icon'  => 'fas fa-copy',
                ],
            ],
        ],
        [
            'text'    => 'sidebar.parametrizacao_reserva',
            'icon'    => 'fas fa-calendar-cog',
            'submenu' => [
                [
                    'text' => 'Países',
                    'icon' => 'fas fa-globe-americas',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'pais.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'pais.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Dias Úteis',
                    'icon' => 'fas fa-calendar-day',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'dia_util.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'dia_util.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Horários Diários',
                    'icon' => 'fas fa-clock',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'horario_diario.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'horario_diario.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Feriados',
                    'icon' => 'fas fa-calendar-alt',
                    'submenu' => [
                        [
                            'text' => 'Listagem',
                            'route' => 'feriado.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Novo',
                            'route' => 'feriado.create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                    ],
                ],
                [
                    'text' => 'Status de Reserva',
                    'icon' => 'fas fa-tags',
                    'route' => 'status_reserva.index',
                    'can'   => 'acessar-superadmin',
                ],
            ],
        ],

        [
            'text' => 'Estações',
            'icon' => 'fas fa-map-marker-alt',
            'submenu' => [
                [
                    'text' => 'Listagem',
                    'route' => 'estacao.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Nova',
                    'route' => 'estacao.create',
                    'icon' => 'fas fa-plus-circle',
                ],
            ],
        ],
        // USUÁRIOS E ACESSOS
        [
            'header'  => 'sidebar.usuarios_acessos',
            'classes' => 'text-bold text-secondary text-uppercase mt-1 border-white border-bottom',
        ],
        [
            'text'  => 'sidebar.meu_perfil',
            'route' => 'profile.edit',
            'icon'  => 'fas fa-user-circle',
            'can'   => 'acessar-perfil',
        ],
        [
            'text'    => 'Usuários',
            'icon'    => 'fas fa-users',
            'submenu' => [
                [
                    'text' => 'Listagem',
                    'route' => 'usuario.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Novo',
                    'route' => 'usuario.create',
                    'icon' => 'fas fa-plus-circle',
                ],
            ],
        ],
        [
            'text'    => 'Perfis de Usuários',
            'icon'    => 'fas fa-user-tag',
            'can'     => 'gerenciar-empresa',
            'submenu' => [
                [
                    'text' => 'Listagem',
                    'route' => 'tipo_usuario.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Novo',
                    'route' => 'tipo_usuario.create',
                    'icon' => 'fas fa-plus-circle',
                ],
            ],
        ],
        [
        [
            'text' => 'sidebar.termo_consentimento',
            'icon' => 'fas fa-file-signature',
            'submenu' => [
                [
                    'text' => 'Listagem',
                    'route' => 'termo_consentimento.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Novo',
                    'route' => 'termo_consentimento.create',
                    'icon' => 'fas fa-plus-circle',
                ],
            ],
        ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        App\Menu\Filters\AutoPermissionFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        App\Menu\Filters\RoutePermissionVisibilityFilter::class,
        App\Menu\Filters\RemoveEmptySubmenuFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 10,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
