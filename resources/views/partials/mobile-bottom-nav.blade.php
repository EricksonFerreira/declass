{{-- Menu inferior fixo para mobile (<768px). Injetado pelo layout adminlte::page. --}}
@php
    $mobileNavUser = auth()->user();
    $mobileSecoes = $mobileNavUser
        ? collect(app(\App\Services\MenuSecoesService::class)->secoes($mobileNavUser))->pluck('slug')->all()
        : [];

    $mobileRotaAtual = \Illuminate\Support\Facades\Route::currentRouteName() ?? '';
    $mobileSecaoParam = request()->route('secao');

    // Prefixos de rota que acendem cada item da barra.
    $mobileItens = [
        [
            'label' => 'Início',
            'icone' => 'fas fa-home',
            'url' => route('home'),
            'slug' => null,
            'prefixos' => ['home', 'admin.index'],
        ],
        [
            'label' => 'Registrar',
            'icone' => 'fas fa-stopwatch',
            'url' => route('menu.index', 'registrar-tempo'),
            'slug' => 'registrar-tempo',
            'prefixos' => ['clockify.tracker.', 'clockify.timesheet', 'clockify.calendario.'],
        ],
        [
            'label' => 'Painel',
            'icone' => 'fas fa-chart-pie',
            'url' => route('menu.index', 'painel'),
            'slug' => 'painel',
            'prefixos' => ['dashboard', 'home.produtividade', 'home.equipe', 'home.projetos'],
        ],
        [
            'label' => 'Relatórios',
            'icone' => 'fas fa-file-alt',
            'url' => route('menu.index', 'relatorios'),
            'slug' => 'relatorios',
            'prefixos' => ['relatorios.'],
        ],
        [
            'label' => 'Gerenciar',
            'icone' => 'fas fa-folder-open',
            'url' => route('menu.index', 'gerenciar'),
            'slug' => 'gerenciar',
            'prefixos' => ['clockify.projetos.', 'clockify.clientes.', 'clockify.times.', 'clockify.timeoff.', 'clockify.funcoes.', 'clockify.tags.', 'usuario.'],
        ],
        [
            'label' => 'Menu',
            'icone' => 'fas fa-bars',
            'url' => route('menu.index'),
            'slug' => '__menu__',
            'prefixos' => [],
        ],
    ];

    $mobileAtivo = function (array $item) use ($mobileRotaAtual, $mobileSecaoParam) {
        if ($item['slug'] === '__menu__') {
            return $mobileRotaAtual === 'menu.index' && ! in_array($mobileSecaoParam, ['registrar-tempo', 'painel', 'relatorios', 'gerenciar'], true);
        }

        if ($mobileSecaoParam === $item['slug']) {
            return true;
        }

        return collect($item['prefixos'])->contains(fn ($p) => str_starts_with($mobileRotaAtual, $p));
    };

    $mobileItens = array_values(array_filter($mobileItens, function ($item) use ($mobileSecoes) {
        return $item['slug'] === null
            || $item['slug'] === '__menu__'
            || in_array($item['slug'], $mobileSecoes, true);
    }));
@endphp

@if ($mobileNavUser)
<nav class="mobile-bottom-nav d-md-none" aria-label="Menu inferior">
    @foreach ($mobileItens as $item)
        <a href="{{ $item['url'] }}" class="mbn-item {{ $mobileAtivo($item) ? 'active' : '' }}">
            <i class="{{ $item['icone'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
<style>
    .mobile-bottom-nav {
        position: fixed;
        left: 0; right: 0; bottom: 0;
        z-index: 1035;
        display: flex;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        box-shadow: 0 -4px 16px rgba(15, 23, 42, .08);
        padding-bottom: env(safe-area-inset-bottom, 0);
    }
    .mbn-item {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 8px 2px 7px;
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-decoration: none !important;
        min-width: 0;
    }
    .mbn-item i { font-size: 17px; }
    .mbn-item span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .mbn-item:active { background: #f1f5f9; }
    .mbn-item.active { color: #0a1628; }
    .mbn-item.active i { color: #0284c7; }
    .mbn-item.active::before {
        content: '';
        position: absolute;
        top: 0; left: 50%;
        transform: translateX(-50%);
        width: 34px; height: 3px;
        border-radius: 0 0 4px 4px;
        background: #0284c7;
    }
    .mbn-item { position: relative; }

    @media (max-width: 767.98px) {
        .content-wrapper { padding-bottom: 64px; }
    }
</style>
@endif
