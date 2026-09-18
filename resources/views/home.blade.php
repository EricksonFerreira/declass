@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Acesse rapidamente as principais áreas do sistema" />
@stop

@section('css')
@include('partials.home-cards-styles')
@stop

@section('content')
@php
    $secoes = [
        [
            'titulo' => 'Registrar tempo',
            'cards' => [
                ['Rastreador', 'fas fa-stopwatch', 'clockify.tracker.index', 'Cronometre ou lance horas manualmente', '#0d9488'],
                ['Timesheet', 'fas fa-table', 'clockify.timesheet', 'Sua grade semanal de apontamentos', '#0891b2'],
                ['Calendário', 'fas fa-calendar-alt', 'clockify.calendario.index', 'Apontamentos e alocações por dia', '#2563eb'],
            ],
        ],
        [
            'titulo' => 'Painel',
            'cards' => [
                ['Visão Geral', 'fas fa-tachometer-alt', 'dashboard', 'Visão geral com gráficos e KPIs', '#2563eb'],
                ['Produtividade', 'fas fa-chart-line', 'home.produtividade', 'Horas e evolução da equipe', '#0d9488'],
                ['Equipe', 'fas fa-users', 'home.equipe', 'Quem está rastreando, status e alertas', '#16a34a'],
                ['Projetos', 'fas fa-project-diagram', 'home.projetos', 'Saúde e horas por projeto', '#0284c7'],
            ],
        ],
        [
            'titulo' => 'Relatórios',
            'cards' => [
                ['Relatórios', 'fas fa-file-alt', 'relatorios.index', 'Resumos e visões de horas', '#7c3aed'],
                ['Consumo × Estimado', 'fas fa-chart-bar', 'relatorios.consumo', 'Horas consumidas vs estimadas', '#b45309'],
                ['Exportar CSV', 'fas fa-file-export', 'relatorios.export', 'Baixe os dados em planilha', '#475569'],
            ],
        ],
        [
            'titulo' => 'Gerenciar',
            'cards' => [
                ['Projetos', 'fas fa-project-diagram', 'clockify.projetos.index', 'Cadastro e status dos projetos', '#0284c7'],
                ['Planejar Equipe', 'fas fa-users-cog', 'clockify.projetos.planejar-equipe-geral', 'Distribua a equipe nos projetos', '#4f46e5'],
                ['Clientes', 'fas fa-user-friends', 'clockify.clientes.index', 'Carteira de clientes', '#0891b2'],
                ['Equipe', 'fas fa-users', 'clockify.times.index', 'Times e membros', '#16a34a'],
                ['Folgas e Férias', 'fas fa-plane', 'clockify.timeoff.index', 'Férias e folgas da equipe', '#7c3aed'],
                ['Funções', 'fas fa-briefcase', 'clockify.funcoes.index', 'Funções e custo por hora', '#b45309'],
                ['Tags', 'fas fa-tags', 'clockify.tags.index', 'Etiquetas de classificação', '#db2777'],
                ['Usuários', 'fas fa-user-cog', 'usuario.index', 'Colaboradores e permissões', '#475569'],
            ],
        ],
        [
            'titulo' => 'Ajuda',
            'cards' => [
                ['Central de Ajuda', 'fas fa-question-circle', 'assinatura.ajuda', 'Dúvidas frequentes e guias', '#0891b2'],
            ],
        ],
    ];

    // Esconde tiles de areas que o usuario nao pode acessar (evita 403 no clique).
    $planoPermissao = app(\App\Services\PlanoPermissaoService::class);
    $secoes = collect($secoes)->map(function ($secao) use ($planoPermissao) {
        $secao['cards'] = array_values(array_filter($secao['cards'], function ($card) use ($planoPermissao) {
            return $planoPermissao->usuarioPodeAcessarRota(auth()->user(), $card[2]);
        }));

        return $secao;
    })->filter(fn ($secao) => count($secao['cards']) > 0)->values()->all();
@endphp

<div class="home-launcher">
    @foreach ($secoes as $secao)
        <div class="home-section">
            <div class="home-section-title">{{ $secao['titulo'] }}</div>
            <div class="row">
                @foreach ($secao['cards'] as $card)
                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                        <a href="{{ route($card[2]) }}" class="home-card" style="--card-cor: {{ $card[4] }};">
                            <span class="home-card-icone"><i class="{{ $card[1] }}"></i></span>
                            <span class="home-card-texto">
                                <span class="home-card-nome">{{ $card[0] }}</span>
                                <p class="home-card-desc">{{ $card[3] }}</p>
                            </span>
                            <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @can('acessar-superadmin')
        <div class="home-section">
            <div class="home-section-title">Configurações</div>
            <div class="row">
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <a href="{{ route('empresa.index') }}" class="home-card" style="--card-cor: #dc2626;">
                        <span class="home-card-icone"><i class="fas fa-building"></i></span>
                        <span class="home-card-texto">
                            <span class="home-card-nome">Empresas</span>
                            <p class="home-card-desc">Tenants cadastrados na plataforma</p>
                        </span>
                        <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <a href="{{ route('comandos.index') }}" class="home-card" style="--card-cor: #0f172a;">
                        <span class="home-card-icone"><i class="fas fa-terminal"></i></span>
                        <span class="home-card-texto">
                            <span class="home-card-nome">Comandos</span>
                            <p class="home-card-desc">Manutenção e comandos do sistema</p>
                        </span>
                        <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            </div>
        </div>
    @endcan
</div>
@stop
