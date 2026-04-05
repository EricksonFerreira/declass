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
        @if(Auth::check() && Auth::user()->isAdministrador() && \Illuminate\Support\Facades\Schema::hasTable('empresas'))
            @php
                $empresaSelecionada = (int) session('empresa_context_id', 0);
                $empresasMenu = \App\Models\Empresa::query()->orderBy('nome')->pluck('nome', 'id')->toArray();
            @endphp
            <li class="nav-item d-flex align-items-center mr-2">
                <form method="POST" action="{{ route('empresa_context.update') }}" class="mb-0">
                    @csrf
                    <select
                        name="empresa_id"
                        class="form-control form-control-sm"
                        style="min-width: 220px;"
                        onchange="this.form.submit()"
                        title="Empresa em visualização"
                    >
                        <option value="0" {{ $empresaSelecionada === 0 ? 'selected' : '' }}>Todas as empresas</option>
                        @foreach($empresasMenu as $empresaId => $empresaNome)
                            <option value="{{ $empresaId }}" {{ $empresaSelecionada === (int) $empresaId ? 'selected' : '' }}>
                                {{ $empresaNome }}
                            </option>
                        @endforeach
                    </select>
                </form>
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
