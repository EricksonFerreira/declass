@php
    $empresaTemaAtual = null;
    $empresaSelecionada = 0;

    if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('empresas')) {
        $empresaIdTema = null;

        if (Auth::user()->isAdministrador()) {
            $empresaIdSessao = (int) session('empresa_context_id', 0);
            $empresaSelecionada = $empresaIdSessao;
            if ($empresaIdSessao > 0) {
                $empresaIdTema = $empresaIdSessao;
            }
        } else {
            $empresaIdTema = (int) (Auth::user()->empresa_id ?? 0);
            $empresaSelecionada = $empresaIdTema;
        }

        if ($empresaIdTema) {
            $empresaTemaAtual = \App\Models\Empresa::query()
                ->select(['id', 'nome', 'logo_path', 'cor_primaria', 'cor_secundaria', 'cor_terciaria'])
                ->find($empresaIdTema);
        }
    }
@endphp

<style>
    :root {
        --empresa-cor-primaria: {{ $empresaTemaAtual->cor_primaria ?? '#1F2937' }};
        --empresa-cor-secundaria: {{ $empresaTemaAtual->cor_secundaria ?? '#E2E8F0' }};
        --empresa-cor-terciaria: {{ $empresaTemaAtual->cor_terciaria ?? '#64748B' }};
    }

    /* Sidebar search: keep typed match text readable on light results panel */
    .main-sidebar .sidebar-search-results .search-title,
    .main-sidebar .sidebar-search-results .search-path {
        color: #111827 !important;
    }

    .main-sidebar .sidebar-search-results .search-title .text-light,
    .main-sidebar .sidebar-search-results .search-path .text-light,
    .main-sidebar .sidebar-search-results .search-title strong,
    .main-sidebar .sidebar-search-results .search-path strong {
        color: #000 !important;
        font-weight: 700 !important;
    }

    .user-menu .dropdown-menu {
        border: 1px solid #e2e8f0;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        border-radius: 14px;
        padding: 0.35rem 0.4rem;
    }

    .user-menu .dropdown-menu .user-footer {
        border-top: 1px solid #e2e8f0;
        padding: 12px;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }

    .user-menu .dropdown-menu .user-footer .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    .user-menu .user-footer .empresa-context-select {
        margin-top: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
    }

    .user-menu .empresa-context-select label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        margin-bottom: 6px;
        display: block;
        font-weight: 700;
    }

    .user-menu .empresa-context-select select {
        border-radius: 10px;
        font-size: 13px;
    }
</style>

@if($empresaTemaAtual)
    <style>
        body:not(.login-page) .content-wrapper {
            background-image: linear-gradient(
                135deg,
                color-mix(in srgb, var(--empresa-cor-primaria) 16%, white 84%) 0%,
                color-mix(in srgb, var(--empresa-cor-secundaria) 14%, white 86%) 55%,
                color-mix(in srgb, var(--empresa-cor-terciaria) 12%, white 88%) 100%
            );
            background-attachment: fixed;
        }

        body:not(.login-page) .main-header.navbar {
            background: var(--empresa-cor-primaria) !important;
            border-bottom: 2px solid color-mix(in srgb, var(--empresa-cor-terciaria) 60%, #0f172a 40%);
        }

        body:not(.login-page) .main-header.navbar .nav-link,
        body:not(.login-page) .main-header.navbar .navbar-nav .nav-link i {
            color: var(--empresa-cor-secundaria) !important;
        }

        body:not(.login-page) .main-sidebar {
            background: var(--empresa-cor-primaria) !important;
        }

        body:not(.login-page) .main-sidebar .brand-text,
        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link,
        body:not(.login-page) .main-sidebar .nav-header,
        body:not(.login-page) .main-sidebar .user-panel .info,
        body:not(.login-page) .main-sidebar .user-panel .info a {
            color: var(--empresa-cor-secundaria) !important;
        }

        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link.active,
        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link:hover {
            background: color-mix(in srgb, var(--empresa-cor-terciaria) 35%, transparent 65%) !important;
            color: var(--empresa-cor-secundaria) !important;
        }

        body:not(.login-page) .main-sidebar .nav-item.menu-open > .nav-treeview > .nav-item > .nav-link {
            background: color-mix(in srgb, var(--empresa-cor-secundaria) 16%, transparent 84%) !important;
            border-radius: .35rem;
            margin: 2px 6px;
        }

        body:not(.login-page) .main-sidebar .nav-item.menu-open > .nav-treeview > .nav-item > .nav-link.active {
            background: color-mix(in srgb, var(--empresa-cor-terciaria) 55%, transparent 45%) !important;
            font-weight: 700;
        }

        body:not(.login-page) .main-header.navbar {
            border-bottom: 2px solid color-mix(in srgb, var(--empresa-cor-primaria) 35%, #dbe5f0 65%);
        }

        .empresa-context-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 260px;
            padding: .3rem .6rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--empresa-cor-terciaria) 35%, #0f172a 65%);
            color: var(--empresa-cor-secundaria);
            font-size: 12px;
            line-height: 1;
        }

        .empresa-context-chip img {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            object-fit: contain;
            background: var(--empresa-cor-primaria);
            border: 1px solid color-mix(in srgb, var(--empresa-cor-secundaria) 25%, transparent 75%);
            padding: 1px;
        }

        .empresa-context-chip .empresa-chip-inicial {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--empresa-cor-secundaria);
            color: var(--empresa-cor-primaria);
            font-weight: 700;
        }
    </style>
@endif

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        @if($empresaTemaAtual)
            @php
                $empresaLogoPath = is_string($empresaTemaAtual->logo_path ?? null)
                    ? trim($empresaTemaAtual->logo_path)
                    : '';
            @endphp
            <li class="nav-item d-flex align-items-center mr-2">
                <span class="empresa-context-chip" title="Empresa ativa">
                    @if($empresaLogoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogoPath))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaLogoPath) }}" alt="Logo empresa">
                    @else
                        <span class="empresa-chip-inicial">{{ strtoupper(mb_substr($empresaTemaAtual->nome ?? 'E', 0, 1)) }}</span>
                    @endif
                    <span class="text-truncate">{{ $empresaTemaAtual->nome }}</span>
                </span>
            </li>
        @endif

        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('[data-widget="pushmenu"]');
        if (!toggle) return;

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            if (typeof $ !== 'undefined' && typeof $.fn.PushMenu !== 'undefined') {
                $(this).PushMenu('toggle');
                return;
            }

            document.body.classList.toggle('sidebar-open');
            document.body.classList.toggle('sidebar-collapse');
        });
    });
</script>

