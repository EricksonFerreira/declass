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

    'title' => 'DECLOCK',
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

    'logo' => '<b>DECLOCK</b>',
    'logo_img' => 'assets/imgs/logo-sem-texto.png',
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Logo DECLOCK',

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
            'path' => 'assets/imgs/logo-sem-texto.png',
            'alt' => 'Imagem de Pré-carregamento DECLOCK',
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
    'classes_sidebar' => 'sidebar-light-secondary',
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
    'dashboard_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/home',
    'logout_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/logout',
    'login_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/login',
    'register_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/register',
    'password_reset_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/forgot-password',
    'password_email_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/forgot-password',
    'profile_url' => trim((string) config('app.admin_prefix', 'admin'), '/').'/profile',

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
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => false,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => false,
        ],
        [
            'text' => 'Início',
            'route' => 'home',
            'icon' => 'fas fa-home',
        ],

        // REGISTRAR TEMPO
        [
            'header' => 'REGISTRAR TEMPO',
        ],
        [
            'text' => 'Rastreador',
            'route' => 'clockify.tracker.index',
            'icon' => 'fas fa-stopwatch',
        ],
        [
            'text' => 'Timesheet',
            'route' => 'clockify.timesheet',
            'icon' => 'fas fa-table',
        ],
        [
            'text' => 'Calendário',
            'route' => 'clockify.calendario.index',
            'icon' => 'fas fa-calendar-alt',
        ],
        [
            'text' => 'Time Off',
            'route' => 'clockify.timeoff.index',
            'icon' => 'fas fa-plane',
        ],

        // ANÁLISE
        [
            'header' => 'ANÁLISE',
        ],
        [
            'text' => 'Painel',
            'icon' => 'fas fa-chart-pie',
            'submenu' => [
                [
                    'text' => 'Visão Geral',
                    'route' => 'dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                ],
                [
                    'text' => 'Produtividade',
                    'route' => 'home.produtividade',
                    'icon' => 'fas fa-chart-line',
                ],
                [
                    'text' => 'Equipe',
                    'route' => 'home.equipe',
                    'icon' => 'fas fa-users',
                ],
                [
                    'text' => 'Projetos',
                    'route' => 'home.projetos',
                    'icon' => 'fas fa-project-diagram',
                ],
            ],
        ],
        [
            'text' => 'Relatórios',
            'icon' => 'fas fa-file-alt',
            'submenu' => [
                [
                    'text' => 'Visão geral',
                    'route' => 'relatorios.index',
                    'icon' => 'fas fa-file-alt',
                ],
                [
                    'text' => 'Horas trabalhadas',
                    'route' => 'relatorios.horas-trabalhadas',
                    'icon' => 'fas fa-clock',
                ],
                [
                    'text' => 'Consumo × Estimado',
                    'route' => 'relatorios.consumo',
                    'icon' => 'fas fa-chart-bar',
                ],
                [
                    'text' => 'Exportar CSV',
                    'route' => 'relatorios.export',
                    'icon' => 'fas fa-file-export',
                ],
            ],
        ],

        // GERENCIAR
        [
            'header' => 'GERENCIAR',
        ],
        [
            'text' => 'Projetos',
            'route' => 'clockify.projetos.index',
            'icon' => 'fas fa-project-diagram',
        ],
        [
            'text' => 'Planejar Equipe',
            'route' => 'clockify.projetos.planejar-equipe-geral',
            'icon' => 'fas fa-users-cog',
        ],
        [
            'text' => 'Clientes',
            'route' => 'clockify.clientes.index',
            'icon' => 'fas fa-user-friends',
        ],
        [
            'text' => 'Equipe',
            'route' => 'clockify.times.index',
            'icon' => 'fas fa-users',
        ],
        [
            'text' => 'Funções',
            'route' => 'clockify.funcoes.index',
            'icon' => 'fas fa-briefcase',
        ],
        [
            'text' => 'Tags',
            'route' => 'clockify.tags.index',
            'icon' => 'fas fa-tags',
        ],
        [
            'text' => 'Usuários',
            'route' => 'usuario.index',
            'icon' => 'fas fa-user-cog',
        ],

        // SISTEMA (somente superadmin — 'can' não funciona em header, por isso é submenu)
        [
            'text' => 'Configurações',
            'icon' => 'fas fa-cogs',
            'can' => 'acessar-superadmin',
            'submenu' => [
                [
                    'text' => 'Empresas',
                    'route' => 'empresa.index',
                    'icon' => 'fas fa-building',
                ],
                [
                    'text' => 'Comandos',
                    'route' => 'comandos.index',
                    'icon' => 'fas fa-terminal',
                ],
            ],
        ],

        // AJUDA
        [
            'header' => 'AJUDA',
        ],
        [
            'text' => 'Central de Ajuda (FAQ)',
            'route' => 'assinatura.ajuda',
            'icon' => 'fas fa-question-circle',
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
        'AppCustom' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/app-custom.css',
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
