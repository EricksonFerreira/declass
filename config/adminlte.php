<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Título
    |--------------------------------------------------------------------------
    |
    | Aqui você pode alterar o título padrão do seu painel de administração.
    |
    | Para instruções detalhadas, você pode consultar a seção de título aqui:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Xpand Cars',
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

    'use_ico_only' => false,
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

    'logo' => '<b>XPAND</b>cars',
    'logo_img' => 'assets/imgs/logo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Logo Xpand Cars',

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
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
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
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Imagem de Pré-carregamento AdminLTE',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
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

    'usermenu_enabled' => false,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
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
    'classes_sidebar_nav' => 'nav-child-indent',
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

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => true,
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
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => true,

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
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
        ],
        [
            'text' => 'blog',
            'url' => 'admin/blog',
            'can' => 'manage-blog',
        ],
        [
            'text' => 'sidebar.reserva',
            'url' => 'reserva',
            'icon' => 'fas fa-calendar-check',
            // 'label' => 4,
            // 'label_color' => 'success',
        ],
        [
            'text' => 'sidebar.status_reserva',
            'url' => 'status_reserva',
            'icon' => 'fas fa-check-circle',
            // 'label' => 4,
            // 'label_color' => 'success',
        ],
        [
            'text' => 'sidebar.movimentacoes',
            'url' => 'movimentacao',
            'icon' => 'fas fa-truck-moving',
            // 'label' => 4,
            // 'label_color' => 'success',
        ],
        [
            'text' => 'sidebar.status_movimentacao',
            'url' => 'status_movimentacao',
            'icon' => 'fas fa-circle',
            // 'label' => 4,
            // 'label_color' => 'success',
        ],
        [
            'text' => 'sidebar.tipo_movimentacao',
            'url' => 'tipo_movimentacao',
            'icon' => 'fas fa-truck',
            // 'label' => 4,
            // 'label_color' => 'success',
        ],
        [
            'text' => 'sidebar.tempo_estacoes',
            'url' => 'tempo_entre_entacao',
            'icon' => 'fas fa-stopwatch',
            // 'label' => 4,
            // 'label_color' => 'success',
        ],
        [
            'header' => 'sidebar.configuracao_usuario',
            'classes' => 'text-bold text-secondary text-uppercase mt-1',
            'icon_color' => 'yellow',
        ],
        [
            'text' => 'sidebar.usuario',
            'url' => 'usuario',
            'icon_color' => 'yellow',
            'icon' => 'fas fa-fw fa-user',
        ],
        [
            'text' => 'sidebar.regra',
            'url' => 'regra',
            'icon_color' => 'yellow',
            'icon' => 'fas fa-key',
        ],
        [
            'text' => 'sidebar.permissao',
            'url' => 'permissao',
            'icon_color' => 'yellow',
            'icon' => 'fas fa-lock',
        ],
        [
            'text' => 'sidebar.termo_consentimento',
            'url' => 'termo_consentimento',
            'icon_color' => 'yellow',
            'icon' => 'fas fa-file-signature',
        ],
        [
            'header' => 'sidebar.configuracao_reserva',
            'classes' => 'text-bold text-secondary text-uppercase mt-1',
        ],
        [
            'text' => 'sidebar.pais',
            'url' => 'pais',
            'icon' => 'fas fa-globe',
        ],
        [
            'text' => 'sidebar.estacao',
            'url' => 'estacao',
            'icon' => 'fas fa-map-marker-alt',
        ],
        [
            'text' => 'sidebar.dia_de_trabalho',
            'url' => 'dia_util',
            'icon' => 'fas fa-calendar-day',
        ],
        [
            'text' => 'sidebar.horario_diario',
            'url' => 'horario_diario',
            'icon' => 'fas fa-clock',
        ],
        [
            'header' => 'sidebar.configuracao_frota',
            'classes' => 'text-bold text-secondary text-uppercase mt-1',
        ],

        [
            'text' => 'sidebar.veiculo',
            'url' => 'veiculo',
            'icon' => 'fas fa-car',
        ],
        [
            'text' => 'sidebar.veiculo_e_opcional.disponibilidade',
            'url' => 'veiculo/disponibilidade',
            'icon' => 'fas fa-car',
        ],
        [
            'text' => 'sidebar.cupom_desconto',
            'url' => 'cupom_desconto',
            'icon' => 'fas fa-percentage',
        ],
        [
            'text' => 'sidebar.dano',
            'icon' => 'fas fa-exclamation-triangle',
            'submenu' => [
                [
                    'text' => 'sidebar.categoria_dano',
                    'url' => 'categoria-dano',
                    'icon' => 'fas fa-tags',
                ],
                [
                    'text' => 'sidebar.dano',
                    'url' => 'dano',
                    'icon' => 'fas fa-tools',
                ]
            ],
        ],
        [
            'text' => 'sidebar.checklist',
            'url' => 'checklist',
            'icon' => 'fas fa-list-alt',
            'submenu' => [
                [
                    'text' => 'sidebar.configuracao_checklist',
                    'url' => 'configuracao-checklist',
                    'icon' => 'fas fa-clipboard-check',
                ],
                [
                    'text' => 'sidebar.item_checklist',
                    'url' => 'item_checklist',
                    'icon' => 'fas fa-tasks',
                ],
                [
                    'text' => 'sidebar.tipo_checklist',
                    'url' => 'tipo_checklist',
                    'icon' => 'fas fa-clipboard-list',
                ],
                [
                    'text' => 'sidebar.status_item_checklist',
                    'url' => 'status_item_checklist',
                    'icon' => 'fas fa-check-square',
                ]
            ],
        ],

        [
            'text' => 'sidebar.configuracao_frota',
            'icon' => 'fas fa-cogs ',
            'submenu' => [
                [
                    'text' => 'sidebar.tipo_frota',
                    'url' => 'tipo_frota',
                    'icon' => 'fas fa-list-alt',
                ],
                [
                    'text' => 'sidebar.caracteristica_tipo_frota',
                    'url' => 'caracteristica_frota',
                    'icon' => 'fas fa-th',
                ],
                [
                    'text' => 'sidebar.caracteristica_valor_tipo_frota',
                    'url' => 'tipovalorcaracteristicafrota',
                    'icon' => 'fas fa-tag ',
                ],
            ],
        ],
        [
            'text' => 'sidebar.configuracao_opcionais',
            'icon' => 'fas fa-sliders-h ',
            'submenu' => [
                [
                    'text' => 'sidebar.tipos_opcionais',
                    'url' => 'tipo_opcional',
                    'icon' => 'fas fa-layer-group',
                ],
                [
                    'text' => 'sidebar.opcionais',
                    'url' => 'opcional',
                    'icon' => 'fas fa-toolbox',
                ],
            ],
        ],
        [
            'text' => 'sidebar.configuracao_veiculo',
            'icon' => 'fas fa-wrench ',
            'submenu' => [
                [
                    'text' => 'sidebar.grupo_frota',
                    'url' => 'grupo_frota',
                    'icon' => 'fas fa-th-list',
                ],
                [
                    'text' => 'sidebar.marca',
                    'url' => 'marca',
                    'icon' => 'fas fa-certificate ',
                ],
                [
                    'text' => 'sidebar.modelo',
                    'url' => 'modelo',
                    'icon' => 'fas fa-truck ',
                ],
                [
                    'text' => 'sidebar.segmento',
                    'url' => 'segmento',
                    'icon' => 'fas fa-bars ',
                ],
                [
                    'text' => 'sidebar.status_veiculo',
                    'url' => 'status_veiculo',
                    'icon' => 'fas fa-tachometer-alt ',
                ],
            ],
        ],
        // [
        //     'header' => 'sidebar.transacao_e_pagamento',
        //     'classes' => 'text-bold text-secondary text-uppercase mt-1',
        // ],
        // [
        //     'text' => 'sidebar.transacao_e_pagamento.tarifa_reserva',
        //     'icon' => 'fas fa-exchange-alt',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'sidebar.transacao_e_pagamento.grupo_tarifa',
        //     'icon' => 'fas fa-tag',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'sidebar.transacao_e_pagamento.preco_tarifa',
        //     'icon' => 'fas fa-dollar-sign',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'sidebar.transacao_e_pagamento.tipo_transacao',
        //     'icon' => 'fas fa-money-check-alt',
        //     'icon_color' => 'cyan',
        //     'url' => 'tipotransacao',
        // ],
        // [
        //     'text' => 'sidebar.transacao_e_pagamento.metodo_pagamento',
        //     'icon' => 'fas fa-credit-card',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],

// --------------------------------------------------------


        // ['header' => 'Colors'],
        // [
        //     'text' => 'sidebar.transacao_e_pagamento.tarifa_reserva',
        //     'icon_color' => 'red',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'warning',
        //     'icon_color' => 'yellow',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'information',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
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
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
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
