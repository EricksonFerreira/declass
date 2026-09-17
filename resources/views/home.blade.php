@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Acesse rapidamente as principais áreas do sistema" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .home-launcher {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .home-section-title {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin: 26px 0 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .home-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .home-section:first-child .home-section-title {
        margin-top: 6px;
    }

    .home-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
        height: 100%;
        padding: 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        text-decoration: none !important;
        color: #0f172a;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .home-card:hover {
        transform: translateY(-3px);
        border-color: var(--empresa-cor-primaria, #3b82f6);
        box-shadow: 0 12px 28px rgba(15, 23, 42, .10);
        color: #0f172a;
    }

    .home-card-icone {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        background: var(--card-cor, var(--empresa-cor-primaria, #3b82f6));
        box-shadow: 0 6px 14px rgba(15, 23, 42, .14);
    }

    .home-card-nome {
        font-size: 15px;
        font-weight: 700;
        line-height: 1.25;
    }

    .home-card-desc {
        font-size: 12px;
        line-height: 1.45;
        color: #64748b;
        margin: 0;
    }

    .home-card-seta {
        margin-top: auto;
        font-size: 12px;
        font-weight: 700;
        color: var(--card-cor, var(--empresa-cor-primaria, #3b82f6));
        opacity: 0;
        transition: opacity .15s ease;
    }

    .home-card:hover .home-card-seta {
        opacity: 1;
    }

    @media (max-width: 575.98px) {
        .home-card { padding: 14px; gap: 8px; }
        .home-card-icone { width: 38px; height: 38px; font-size: 16px; }
        .home-card-desc { display: none; }
    }
</style>
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
                ['Time Off', 'fas fa-plane', 'clockify.timeoff.index', 'Férias e folgas da equipe', '#7c3aed'],
            ],
        ],
        [
            'titulo' => 'Gerenciar',
            'cards' => [
                ['Projetos', 'fas fa-project-diagram', 'clockify.projetos.index', 'Cadastro e status dos projetos', '#0284c7'],
                ['Planejar Equipe', 'fas fa-users-cog', 'clockify.projetos.planejar-equipe-geral', 'Distribua a equipe nos projetos', '#4f46e5'],
                ['Clientes', 'fas fa-user-friends', 'clockify.clientes.index', 'Carteira de clientes', '#0891b2'],
                ['Equipe', 'fas fa-users', 'clockify.times.index', 'Times e membros', '#16a34a'],
                ['Funções', 'fas fa-briefcase', 'clockify.funcoes.index', 'Funções e custo por hora', '#b45309'],
                ['Tags', 'fas fa-tags', 'clockify.tags.index', 'Etiquetas de classificação', '#db2777'],
                ['Usuários', 'fas fa-user-cog', 'usuario.index', 'Colaboradores e permissões', '#475569'],
            ],
        ],
        [
            'titulo' => 'Análise',
            'cards' => [
                ['Painel', 'fas fa-tachometer-alt', 'dashboard', 'Visão geral com gráficos e KPIs', '#2563eb'],
                ['Produtividade', 'fas fa-chart-line', 'home.produtividade', 'Horas e evolução da equipe', '#0d9488'],
                ['Relatórios', 'fas fa-file-alt', 'relatorios.index', 'Resumos e visões de horas', '#7c3aed'],
                ['Consumo × Estimado', 'fas fa-chart-bar', 'relatorios.consumo', 'Horas consumidas vs estimadas', '#b45309'],
                ['Exportar CSV', 'fas fa-file-export', 'relatorios.export', 'Baixe os dados em planilha', '#475569'],
            ],
        ],
        [
            'titulo' => 'Ajuda',
            'cards' => [
                ['Central de Ajuda', 'fas fa-question-circle', 'assinatura.ajuda', 'Dúvidas frequentes e guias', '#0891b2'],
            ],
        ],
    ];
@endphp

<div class="home-launcher">
    @foreach ($secoes as $secao)
        <div class="home-section">
            <div class="home-section-title">{{ $secao['titulo'] }}</div>
            <div class="row">
                @foreach ($secao['cards'] as $card)
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <a href="{{ route($card[2]) }}" class="home-card" style="--card-cor: {{ $card[4] }};">
                            <span class="home-card-icone"><i class="{{ $card[1] }}"></i></span>
                            <span class="home-card-nome">{{ $card[0] }}</span>
                            <p class="home-card-desc">{{ $card[3] }}</p>
                            <span class="home-card-seta">Acessar <i class="fas fa-arrow-right ml-1"></i></span>
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
                <div class="col-6 col-md-4 col-lg-3 mb-3">
                    <a href="{{ route('empresa.index') }}" class="home-card" style="--card-cor: #dc2626;">
                        <span class="home-card-icone"><i class="fas fa-building"></i></span>
                        <span class="home-card-nome">Empresas</span>
                        <p class="home-card-desc">Tenants cadastrados na plataforma</p>
                        <span class="home-card-seta">Acessar <i class="fas fa-arrow-right ml-1"></i></span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3 mb-3">
                    <a href="{{ route('comandos.index') }}" class="home-card" style="--card-cor: #0f172a;">
                        <span class="home-card-icone"><i class="fas fa-terminal"></i></span>
                        <span class="home-card-nome">Comandos</span>
                        <p class="home-card-desc">Manutenção e comandos do sistema</p>
                        <span class="home-card-seta">Acessar <i class="fas fa-arrow-right ml-1"></i></span>
                    </a>
                </div>
            </div>
        </div>
    @endcan
</div>
@stop
