@php
    $navEmpresa = null;
    $navEmpresaSelecionada = 0;

    if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('empresas')) {
        $navUser = Auth::user();

        if ($navUser->isSuperAdmin()) {
            // Super admin: empresa da sessão; se "Todas" (0), usa a empresa própria como fallback do chip
            $navEmpresaSelecionada = (int) session('empresa_context_id', 0);
            $navEmpresaId = $navEmpresaSelecionada > 0
                ? $navEmpresaSelecionada
                : (int) ($navUser->empresa_id ?? 0);
        } else {
            // Demais usuários: sempre a empresa do próprio usuário
            $navEmpresaId = (int) ($navUser->empresa_id ?? 0);
            $navEmpresaSelecionada = $navEmpresaId;
        }

        if (!empty($navEmpresaId)) {
            $navEmpresa = \App\Models\Empresa::query()
                ->select(['id', 'nome', 'logo_path'])
                ->find($navEmpresaId);
        }
    }
@endphp

<style>
.nav-empresa-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: .22rem .7rem; border-radius: 999px;
    background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
    color: #fff; font-size: 12px; line-height: 1;
    white-space: nowrap; max-width: 240px; cursor: default;
}
.nav-empresa-chip img {
    width: 20px; height: 20px; border-radius: 50%;
    object-fit: contain; flex-shrink: 0;
    background: rgba(255,255,255,.2); padding: 1px;
}
.nav-empresa-chip__ini {
    width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.28); font-weight: 700; font-size: 11px; color: #fff;
}
.nav-empresa-chip__nome { overflow: hidden; text-overflow: ellipsis; }
.nav-empresa-chip__tag {
    font-size: 9px; opacity: .7; background: rgba(255,255,255,.2);
    border-radius: 4px; padding: 1px 4px; flex-shrink: 0;
}
</style>

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">

        {{-- Chip da empresa ativa — visível para todos --}}
        @if($navEmpresa)
            @php
                $navLogo = trim((string)($navEmpresa->logo_path ?? ''));
            @endphp
            <li class="nav-item d-flex align-items-center mr-2">
                <span class="nav-empresa-chip" title="{{ $navEmpresa->nome }}">
                    @if($navLogo !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($navLogo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($navLogo) }}" alt="">
                    @else
                        <span class="nav-empresa-chip__ini">{{ strtoupper(mb_substr($navEmpresa->nome, 0, 1)) }}</span>
                    @endif
                    <span class="nav-empresa-chip__nome">{{ $navEmpresa->nome }}</span>
                    @if(Auth::user()->isSuperAdmin() && $navEmpresaSelecionada === 0)
                        <span class="nav-empresa-chip__tag">Todas</span>
                    @endif
                </span>
            </li>
        @endif

        @yield('content_top_nav_right')
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
