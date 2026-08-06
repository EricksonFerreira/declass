@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@php
    $empresaBranding = app(\App\Support\EmpresaBranding::class);
    $faviconUrl = $empresaBranding->logoUrl();
    $empresaAtual = $empresaBranding->empresaAtual();
    $appName = $empresaAtual?->nome ?: config('app.name', 'DeCars');
@endphp

@section('meta_tags')
    @parent
    <link rel="shortcut icon" href="{{ $faviconUrl }}" />
    <link rel="manifest" href="{{ route('site.manifest') }}">
    <meta name="theme-color" content="{{ $empresaAtual?->cor_primaria ?: '#0a1628' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $appName }}">
    <link rel="apple-touch-icon" href="{{ route('site.manifest.icon') }}">
@stop

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @hasSection('footer')
            @include('adminlte::partials.footer.footer')
        @endif

        {{-- Right Control Sidebar --}}
        @if(config('adminlte.right_sidebar'))
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')

    <script src="{{ asset('js/sweetAlert.min.js') }}"></script>
    @if (session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const flashSuccess = @json(session('success'));
                const flashError = @json(session('error'));

                const showFlash = (title, message, icon) => {
                    if (!message || typeof Swal === 'undefined' || typeof Swal.fire !== 'function') return;

                    Swal.fire({
                        icon,
                        // title,
                        text: String(message),
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4500,
                        timerProgressBar: true,
                    });
                };

                if (flashSuccess) {
                    showFlash('Sucesso', flashSuccess, 'success');
                }

                if (flashError) {
                    showFlash('Atenção', flashError, 'error');
                }
            });
        </script>
    @endif

    <script>
        if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' }).catch(function (error) {
                    console.warn('Service worker registration failed:', error);
                });
            });
        }
    </script>
@stop
