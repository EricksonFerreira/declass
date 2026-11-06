@php
    $empresaTemaAtual = null;
    $empresaSelecionada = 0;

    $contrasteParaHex = function (?string $hex, string $fallback = '#FFFFFF'): string {
        $hex = trim((string) ($hex ?? ''));
        if ($hex === '') {
            return $fallback;
        }

        if ($hex[0] === '#') {
            $hex = substr($hex, 1);
        }

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return $fallback;
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // sRGB -> linear
        $rl = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $gl = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $bl = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        $luminancia = 0.2126 * $rl + 0.7152 * $gl + 0.0722 * $bl;

        // Se fundo for claro, texto escuro; se fundo for escuro, texto branco.
        return $luminancia > 0.58 ? '#0F172A' : '#FFFFFF';
    };

    if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('empresas')) {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            // Empresa da sessão tem prioridade; fallback = empresa do próprio usuário
            $empresaSelecionada = (int) session('empresa_context_id', 0);
            $empresaIdTema = $empresaSelecionada > 0
                ? $empresaSelecionada
                : (int) ($user->empresa_id ?? 0);
        } else {
            // Demais usuários: sempre a empresa própria
            $empresaIdTema = (int) ($user->empresa_id ?? 0);
            $empresaSelecionada = $empresaIdTema;
        }

        if ($empresaIdTema > 0) {
            $empresaTemaAtual = (object) \Illuminate\Support\Facades\DB::table('empresas')
                ->select(['id', 'nome', 'logo_path', 'cor_primaria', 'cor_secundaria', 'cor_terciaria'])
                ->where('id', $empresaIdTema)
                ->first();
        }
    }
@endphp

@php
    $corPrimaria = $empresaTemaAtual->cor_primaria ?? '#FFFFFF';
    $corSecundaria = $empresaTemaAtual->cor_secundaria ?? '#F8FAFC';
    $corTerciaria = $empresaTemaAtual->cor_terciaria ?? '#CBD5E1';

    $corPrimariaContraste = $contrasteParaHex($corPrimaria);
    $corSecundariaContraste = $contrasteParaHex($corSecundaria);
    $corTerciariaContraste = $contrasteParaHex($corTerciaria);
@endphp

<style>
    :root {
        --empresa-cor-primaria: {{ $corPrimaria }};
        --empresa-cor-secundaria: {{ $corSecundaria }};
        --empresa-cor-terciaria: {{ $corTerciaria }};

        --empresa-cor-primaria-contraste: {{ $corPrimariaContraste }};
        --empresa-cor-secundaria-contraste: {{ $corSecundariaContraste }};
        --empresa-cor-terciaria-contraste: {{ $corTerciariaContraste }};
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

    /* Breadcrumb no menu superior (x-breadcrumb injeta via content_top_nav_left) */
    .main-header .navbar-breadcrumb .breadcrumb {
        font-size: .875rem;
        line-height: 1.5;
    }

    .main-header .navbar-breadcrumb .breadcrumb-item a {
        color: var(--empresa-cor-primaria-contraste, #495057);
        opacity: .8;
        font-weight: 500;
    }

    .main-header .navbar-breadcrumb .breadcrumb-item a:hover {
        opacity: 1;
        text-decoration: underline;
    }

    .main-header .navbar-breadcrumb .breadcrumb-item.active {
        color: var(--empresa-cor-primaria-contraste, #343a40);
        font-weight: 700;
    }

    .main-header .navbar-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
        color: var(--empresa-cor-primaria-contraste, #6c757d);
        opacity: .55;
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

    .user-menu .empresa-context-body {
        padding: 12px 12px 0;
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

    .user-menu .empresa-context-current {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        margin-bottom: 8px;
        padding: 8px 10px;
        border-radius: 10px;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 700;
        font-size: 13px;
    }

    .user-menu .empresa-context-select select {
        border-radius: 10px;
        font-size: 13px;
    }

    .user-menu .empresa-context-link {
        display: inline-flex;
        margin-top: 8px;
        font-size: 12px;
        font-weight: 600;
        color: var(--empresa-cor-primaria, #2563eb);
    }

    .user-menu .empresa-context-link:hover {
        text-decoration: underline;
    }

    .main-header.navbar .escola-context-select {
        min-width: 170px;
        max-width: 220px;
        height: 32px;
        padding: 4px 10px;
        font-size: 12.5px;
        font-weight: 600;
        border-radius: 999px;
        border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria) 45%, transparent 55%);
        background: color-mix(in srgb, var(--empresa-cor-primaria) 82%, #fff 18%);
        color: var(--empresa-cor-primaria-contraste, #fff);
    }

    .main-header.navbar .escola-context-select option {
        color: #0f172a;
        background: #fff;
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

        body:not(.logipn-page) .main-header.navbar {
            background: var(--empresa-cor-primaria) !important;
            border-bottom: 2px solid color-mix(in srgb, var(--empresa-cor-terciaria) 60%, #0f172a 40%);
        }

        body:not(.login-page) .main-header.navbar .nav-link,
        body:not(.login-page) .main-header.navbar .navbar-nav .nav-link i {
            background: var(--empresa-cor-primaria) !important;
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
            align-items: center;
        }

        body:not(.login-page) .main-sidebar {
            background: var(--empresa-cor-primaria) !important;
        }

        body:not(.login-page) .main-sidebar .brand-text,
        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link,
        body:not(.login-page) .main-sidebar .nav-header,
        body:not(.login-page) .main-sidebar .user-panel .info,
        body:not(.login-page) .main-sidebar .user-panel .info a {
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
        }

        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link.active,
        body:not(.login-page) .main-sidebar .nav-sidebar .nav-link:hover {
            background: color-mix(in srgb, var(--empresa-cor-terciaria) 35%, transparent 65%) !important;
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
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
            background: color-mix(in srgb, var(--empresa-cor-primaria) 75%, #000 25%);
            color: var(--empresa-cor-primaria-contraste, #fff);
            font-size: 12px;
            font-weight: 600;
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
                <span class="empresa-context-chip" title="{{ $empresaTemaAtual->nome }}">
                    @if($empresaLogoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaLogoPath))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaLogoPath) }}" alt="Logo empresa">
                    @else
                        <span class="empresa-chip-inicial">{{ strtoupper(mb_substr($empresaTemaAtual->nome, 0, 1)) }}</span>
                    @endif
                    <span class="text-truncate">{{ $empresaTemaAtual->nome }}</span>
                    @if(Auth::user()->isSuperAdmin() && $empresaSelecionada === 0)
                        <small style="opacity:.65;font-size:.75em;margin-left:2px">(Todas)</small>
                    @endif
                </span>
            </li>
        @endif

        {{-- Seletor de escola da sessao: filtra todos os dados do tenant --}}
        @if(Auth::check() && ! Auth::user()->isSuperAdmin())
            @php
                $escolasContexto = collect();
                $escolaContextoId = 0;
                $empresaContextoId = app(\App\Support\EmpresaContext::class)->empresaId();

                // So faz sentido com tenant ativo: na visao consolidada do
                // superadmin nao ha como saber a qual escola o dado pertence.
                if ($empresaContextoId !== null && \Illuminate\Support\Facades\Schema::hasTable('escolas')) {
                    $permitidas = auth()->user()?->escolasContextoIds();

                    $queryEscolas = \App\Models\Escola::query()->orderBy('nome');
                    if ($permitidas !== null) {
                        $queryEscolas->whereIn('id', $permitidas);
                    }

                    $escolasContexto = $queryEscolas->get(['id', 'nome']);
                    $escolaContextoId = (int) app(\App\Support\EscolaContext::class)->escolaId() ?? 0;
                }
            @endphp

            @if($escolasContexto->count() > 1)
                <li class="nav-item d-flex align-items-center mr-2">
                    <form method="POST" action="{{ route('escola_context.update') }}" class="mb-0">
                        @csrf
                        <select
                            name="escola_id"
                            class="form-control form-control-sm escola-context-select"
                            onchange="this.form.submit()"
                            title="Escola da sessão — filtra todos os dados"
                        >
                            <option value="0" {{ $escolaContextoId === 0 ? 'selected' : '' }}>Todas as escolas</option>
                            @foreach($escolasContexto as $escolaCtx)
                                <option value="{{ $escolaCtx->id }}" {{ $escolaContextoId === (int) $escolaCtx->id ? 'selected' : '' }}>
                                    {{ $escolaCtx->nome }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </li>
            @endif
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

