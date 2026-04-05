    @php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )
    @php( $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout') )
    @php( $empresaSelecionada = (int) session('empresa_context_id', 0) )

@if (config('adminlte.usermenu_profile_url', false))
    @php( $profile_url = Auth::user()->adminlte_profile_url() )
@endif

@if (config('adminlte.use_route_url', false))
    @php( $profile_url = $profile_url ? route($profile_url) : '' )
    @php( $logout_url = $logout_url ? route($logout_url) : '' )
@else
    @php( $profile_url = $profile_url ? url($profile_url) : '' )
    @php( $logout_url = $logout_url ? url($logout_url) : '' )
@endif

<li class="nav-item dropdown user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <i class="fas fa-user-circle mr-1"></i>
        @if(config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}"
                 class="user-image img-circle elevation-2"
                 alt="{{ Auth::user()->name }}">
        @endif
        <span @if(config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        {{-- User menu header --}}
        @if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }}
                @if(!config('adminlte.usermenu_image')) h-auto @endif">
                @if(config('adminlte.usermenu_image'))
                    <img src="{{ Auth::user()->adminlte_image() }}"
                         class="img-circle elevation-2"
                         alt="{{ Auth::user()->name }}">
                @endif
                <p class="@if(!config('adminlte.usermenu_image')) mt-0 @endif">
                    {{ Auth::user()->name }}
                    @if(config('adminlte.usermenu_desc'))
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    @endif
                </p>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        {{-- Configured user menu links --}}
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu("navbar-user"), 'item')

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body">
                @yield('usermenu_body')
            </li>
        @endif

        {{-- User menu footer --}}
        <li class="user-footer">
            @if($profile_url)
                <a href="{{ $profile_url }}" class="nav-link btn btn-default btn-flat d-inline-block">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    {{ __('adminlte::menu.profile') }}
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if(!$profile_url) btn-block @endif"
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                {{ __('adminlte::adminlte.log_out') }}
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if(config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>

            @if(Auth::check() && Auth::user()->isAdministrador() && \Illuminate\Support\Facades\Schema::hasTable('empresas'))
                @php
                    $empresasMenu = \App\Models\Empresa::query()->orderBy('nome')->pluck('nome', 'id')->toArray();
                @endphp
                <div class="empresa-context-select">
                    <label>Empresa em visualizacao</label>
                    <form method="POST" action="{{ route('empresa_context.update') }}" class="mb-0">
                        @csrf
                        <select
                            name="empresa_id"
                            class="form-control form-control-sm"
                            onchange="this.form.submit()"
                            title="Empresa em visualizacao"
                        >
                            <option value="0" {{ $empresaSelecionada === 0 ? 'selected' : '' }}>Todas as empresas</option>
                            @foreach($empresasMenu as $empresaId => $empresaNome)
                                <option value="{{ $empresaId }}" {{ $empresaSelecionada === (int) $empresaId ? 'selected' : '' }}>
                                    {{ $empresaNome }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </li>

    </ul>

</li>
