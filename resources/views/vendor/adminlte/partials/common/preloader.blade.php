@php
    $empresaBranding = app(\App\Support\EmpresaBranding::class);
    $empresaAtual = $empresaBranding->empresaAtual();
    $logoUrl = $empresaBranding->logoUrl($empresaAtual);
@endphp

@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

<div class="{{ $preloaderHelper->makePreloaderClasses() }}" style="{{ $preloaderHelper->makePreloaderStyle() }}">

    @hasSection('preloader')

        {{-- Use a custom preloader content --}}
        @yield('preloader')

    @else

        {{-- Use the active company logo when available --}}
        <img src="{{ $logoUrl }}"
             class="img-circle {{ config('adminlte.preloader.img.effect', 'animation__shake') }}"
             alt="{{ $empresaAtual?->nome ? 'Logo ' . $empresaAtual->nome : config('adminlte.preloader.img.alt', 'AdminLTE Preloader Image') }}"
             width="{{ config('adminlte.preloader.img.width', 60) }}"
             height="{{ config('adminlte.preloader.img.height', 60) }}"
             style="animation-iteration-count:infinite; object-fit: contain;">

    @endif

</div>
