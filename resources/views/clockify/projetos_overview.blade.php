@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-3">
        <x-page-title :title="$titulo" />
        
        <!-- Dropdown de Seleção de Projeto Clockify Style -->
        <div class="d-flex align-items-center gap-2">
            <select id="project-filter-select" class="form-control form-control-sm select2" style="min-width: 200px; border-radius: 8px;">
                <option value="all" @selected(!$selectedProjectId)>📂 Todos os Projetos (Geral)</option>
                @foreach($projetos as $p)
                    <option value="{{ $p->id }}" @selected($selectedProjectId == $p->id)>📁 {{ $p->nome }}</option>
                @endforeach
            </select>
        </div>
    </div>
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    /* Tabs Navigation Style Clockify */
    .clockify-tabs { display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; background: #fff; padding: 10px 16px 0; border-radius: 12px 12px 0 0; }
    .clockify-tab-btn {
        background: transparent; border: none; padding: 12px 24px; font-size: 13px; font-weight: 700;
        color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s ease;
        margin-bottom: -2px; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .clockify-tab-btn:hover { color: #1e293b; }
    .clockify-tab-btn.active { color: #0284c7; border-bottom-color: #0284c7; }

    /* Single Project KPI Block */
    .status-kpi-block { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 20px; }
    .status-kpi-row { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 10px 0; font-size: 13px; }
    .status-kpi-row:last-child { border-bottom: none; }
    .status-kpi-label { font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-kpi-value { font-weight: 800; color: #1e293b; }

    /* Progress bar custom */
    .status-progress-wrap { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; font-size: 12px; font-weight: 700; color: #64748b; }
    .status-progress-bar-bg { height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 24px; }
    .status-progress-bar-fill { height: 100%; background: #22c55e; border-radius: 999px; }

    .proj-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .proj-card {
        background: #fff; border-radius: 14px; padding: 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1px solid #eef1f6;
        position: relative; overflow: hidden;
    }
    .proj-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background: var(--proj-color, #6c757d); }
    .proj-card__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .proj-card__name { font-family:'Plus Jakarta Sans',sans-serif; font-size:15px; font-weight:700; color:#1a1d2e; margin:0; }
    
    .proj-card__metric { display: flex; justify-content: space-between; font-size: 13px; color: #64748b; margin-bottom: 6px; }
    .proj-card__metric strong { color: #1a1d2e; }

    .matrix-table th, .matrix-table td { text-align: center; vertical-align: middle !important; }
    .matrix-table td.project-col { text-align: left; font-weight: bold; }
    .matrix-badge { min-width: 45px; }
    .matrix-total-row { background: #eef3fb; }
    .matrix-total-row td { border-bottom: 2px solid #b6c7e4 !important; }

    /* Legend Row style */
    .chart-legend-wrap { display: flex; justify-content: center; gap: 18px; margin-top: 18px; font-size: 12px; font-weight: 700; color: #64748b; }
    .chart-legend-item { display: flex; align-items: center; gap: 6px; }
    .chart-legend-dot { width: 10px; height: 10px; border-radius: 2px; }
</style>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('home.projetos') }}" method="GET" class="form-inline" id="filter-form">
                <input type="hidden" name="projeto_id" value="{{ $selectedProjectId }}">
                <div class="form-group mr-3 mb-2">
                    <label for="data_inicio" class="mr-2 text-sm text-bold text-muted">Período de:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" value="{{ $selectedStartDate }}">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="data_fim" class="mr-2 text-sm text-bold text-muted">Até:</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" value="{{ $selectedEndDate }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary mb-2 font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('home.projetos') }}" class="btn btn-sm btn-default mb-2 ml-2">Limpar</a>
            </form>
        </div>
    </div>

    @if($isSingleProject)
        <!-- ============================================== -->
        <!-- VISÃO FOCADA: STATUS INDIVIDUAL DO PROJETO -->
        <!-- ============================================== -->
        <h3 class="font-weight-bold text-dark mb-4" style="font-family:'Plus Jakarta Sans',sans-serif; letter-spacing: -0.02em;">
            <i class="fas fa-circle mr-2" style="font-size: 14px; color: {{ $project->cor ?? '#6c757d' }};"></i> {{ $project->nome }}
            <!-- Abas do Projeto Clockify Style -->
        <div class="clockify-tabs">
            <button class="clockify-tab-btn" data-target="#tab-tarefas">Tarefas</button>
            <button class="clockify-tab-btn" data-target="#tab-acesso">Acesso</button>
            <button class="clockify-tab-btn active" data-target="#tab-status">Status</button>
            <button class="clockify-tab-btn" data-target="#tab-projecao">Projeção</button>
            <button class="clockify-tab-btn" data-target="#tab-configuracoes">Configurações</button>
        </div>

        <!-- Conteúdo da Aba STATUS -->
        <div id="tab-status" class="project-tab-content">
            <div class="row">
                <div class="col-md-4">
                    <!-- KPIs Financeiros -->
                    <div class="status-kpi-block">
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Orçamento</span>
                            <span class="status-kpi-value">R$ {{ number_format($budgetAmount, 2, ',', '.') }}</span>
                        </div>
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Valor (Faturado)</span>
                            <span class="status-kpi-value text-success">R$ {{ number_format($valor_faturado, 2, ',', '.') }}</span>
                            <span class="status-kpi-label">Valor Faturado Etapas</span>
                            <span class="status-kpi-value text-warning">R$ {{ number_format($valor_faturado_etapas, 2, ',', '.') }}</span>
                            <span class="status-kpi-label">Diferença</span>
                            <span class="status-kpi-value {{ $valor_faturado_diff >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($valor_faturado_diff, 2, ',', '.') }}</span>
                        </div>
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Expenses</span>
                            <span class="status-kpi-value text-danger">R$ {{ number_format($expenses, 2, ',', '.') }}</span>
                        </div>
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Restante</span>
                            <span class="status-kpi-value">R$ {{ number_format($remainingAmount, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- KPIs de Horas -->
                    <div class="status-kpi-block">
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Rastreado</span>
                            <span class="status-kpi-value">{{ number_format($totalHours, 1) }}h</span>
                        </div>
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Faturável</span>
                            <span class="status-kpi-value text-info">{{ number_format($billableHours, 1) }}h</span>
                        </div>
                        <div class="status-kpi-row">
                            <span class="status-kpi-label">Não-Faturável</span>
                            <span class="status-kpi-value text-muted">{{ number_format($nonBillableHours, 1) }}h</span>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Rosca / Conclusão de Orçamento -->
                <div class="col-md-8">
                    <div class="card shadow-sm p-4" style="border-radius: 12px; border: 1px solid #e2e8f0; min-height: 380px;">
                        <!-- Barra de progresso horizontal do orçamento -->
                        <div class="status-progress-wrap">
                            <span>Progresso do Orçamento (Horas)</span>
                            <span>{{ number_format($progressPercent, 2) }}%</span>
                        </div>
                        <div class="status-progress-bar-bg">
                            <div class="status-progress-bar-fill" style="width: {{ min($progressPercent, 100) }}%; background-color: {{ $progressPercent > 100 ? '#ef4444' : '#22c55e' }};"></div>
                        </div>

                        <!-- Gráfico circular central -->
                        <div style="position: relative; height: 180px;">
                            <canvas id="projectStatusDonut"></canvas>
                        </div>

                        <!-- Legendas coloridas -->
                        <div class="chart-legend-wrap">
                            <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#0ea5e9;"></span> Faturável ({{ number_format($billableHours, 1) }}h)</span>
                            <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#84cc16;"></span> Não-Faturável ({{ number_format($nonBillableHours, 1) }}h)</span>
                            <span class="chart-legend-item"><span class="chart-legend-dot" style="background:#e2e8f0;"></span> Restante ({{ number_format(max($budgetHours - $totalHours, 0), 1) }}h)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo da Aba TAREFAS -->
        <div id="tab-tarefas" class="project-tab-content" style="display: none;">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title text-sm text-bold font-weight-bold text-dark mb-2 mb-md-0"><i class="fas fa-list mr-1"></i> Tarefas e Lançamentos do Período</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" id="task-search" class="form-control float-right" placeholder="Filtrar lançamentos...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-filter"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-4">Nome da Tarefa / Descrição</th>
                                    <th>Encarregado</th>
                                    <th>Horário / Período</th>
                                    <th class="text-right">Despesas</th>
                                    <th class="text-right">Valor</th>
                                    <th class="text-center">Tempo Rastreado</th>
                                </tr>
                            </thead>
                            <tbody id="task-table-tbody">
                                @forelse($tasks as $t)
                                    <tr>
                                        <td class="pl-4 text-dark font-weight-bold">{{ $t['nome'] }}</td>
                                        <td><i class="fas fa-user text-muted mr-1"></i> {{ $t['encarregado'] }}</td>
                                        <td><i class="far fa-clock text-muted mr-1"></i> {{ $t['periodo'] }}</td>
                                        <td class="text-right text-danger">R$ {{ number_format($t['despesas'], 2, ',', '.') }}</td>
                                        <td class="text-right text-success">R$ {{ number_format($t['valor'], 2, ',', '.') }}</td>
                                        <td class="text-center text-primary font-weight-bold">{{ number_format($t['horas'], 2, ',', '.') }}h</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Sem lançamentos de tarefas no período selecionado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo da Aba ACESSO -->
        <div id="tab-acesso" class="project-tab-content" style="display: none;">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div class="card-header bg-white border-bottom p-3">
                    <h3 class="card-title text-sm text-bold font-weight-bold text-dark"><i class="fas fa-users mr-1"></i> Membros com Acesso ao Projeto</h3>
                </div>
                <div class="card-body p-0">
                    @if($project->publico)
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-globe fa-3x mb-3 text-info"></i>
                            <h5 class="font-weight-bold text-dark">Este projeto é Público</h5>
                            <p class="text-sm text-muted mb-0">Qualquer membro da empresa tem acesso para lançar tempo e visualizar este projeto.</p>
                        </div>
                    @else
                        <ul class="nav flex-column mb-0">
                            @forelse($project->users as $u)
                                <li class="nav-item border-bottom p-3">
                                    <div class="d-flex align-items-center">
                                        <span class="mr-3 text-secondary" style="font-size: 20px;"><i class="fas fa-user-circle text-primary"></i></span>
                                        <div>
                                            <span class="font-weight-bold text-dark">{{ $u->nome }}</span>
                                            <small class="text-muted d-block">{{ $u->email }}</small>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-5 text-center text-muted">
                                    <i class="fas fa-user-lock fa-3x mb-3 text-warning"></i>
                                    <h5 class="font-weight-bold text-dark">Projeto Privado sem Membros</h5>
                                    <p class="text-sm text-muted">Este projeto é privado, mas nenhum colaborador foi associado a ele ainda.</p>
                                </li>
                            @endforelse
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Conteúdo da Aba PROJEÇÃO -->
        <div id="tab-projecao" class="project-tab-content" style="display: none;">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div class="card-header bg-white border-bottom p-3">
                    <h3 class="card-title text-sm text-bold font-weight-bold text-dark"><i class="fas fa-chart-line mr-1"></i> Cronograma de Entrega e Metragem</h3>
                </div>
                <div class="card-body p-4">
                    @if($project->cronograma_ativo)
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="font-weight-bold text-dark mb-1"><i class="fas fa-check-circle text-success mr-1"></i> {{ $project->cronograma_ativo->nome }}</h5>
                                <p class="text-sm text-muted mb-3">Previsão: <strong>{{ $project->data_entrega_previsao ? $project->data_entrega_previsao->format('d/m/Y') : '-' }}</strong> (Restam: {{ $project->dias_uteis_planejamento }} dias úteis)</p>
                                <div class="row">
                                    <div class="col-6 border-right">
                                        <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Horas Necessárias</span>
                                        <span class="font-weight-bold text-dark text-lg">{{ number_format($project->horas_planejadas, 1) }}h</span>
                                    </div>
                                    <div class="col-6 pl-4">
                                        <span class="text-xs text-muted d-block text-uppercase font-weight-bold">Custo Previsto</span>
                                        <span class="font-weight-bold text-success text-lg">R$ {{ number_format($project->custo_planejado, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                <a href="{{ route('clockify.projetos.planejamento', $project->id) }}" class="btn btn-primary font-weight-bold shadow-sm" target="_blank">
                                    <i class="fas fa-edit mr-1"></i> Ajustar Planejamento
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="far fa-calendar-times fa-4x mb-3 text-muted"></i>
                            <h5 class="font-weight-bold text-dark">Nenhum Cronograma Ativo</h5>
                            <p class="text-sm text-muted mb-4">Este projeto ainda não possui um cronograma de entrega ativo ou válido.</p>
                            <a href="{{ route('clockify.projetos.planejamento', $project->id) }}" class="btn btn-primary font-weight-bold shadow-sm" target="_blank">
                                <i class="fas fa-calendar-alt mr-1"></i> Criar Planejamento
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Conteúdo da Aba CONFIGURAÇÕES -->
        <div id="tab-configuracoes" class="project-tab-content" style="display: none;">
            <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow:hidden;">
                <div class="card-header bg-white border-bottom p-3">
                    <h3 class="card-title text-sm text-bold font-weight-bold text-dark"><i class="fas fa-cog mr-1"></i> Configurações e Metadados do Projeto</h3>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="font-weight-bold text-dark mb-1">Painel Administrativo do Projeto</h5>
                            <p class="text-sm text-muted mb-0">Você pode gerenciar as cores, nome, orçamento base, atribuir clientes e alterar visibilidade pública/privada.</p>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <a href="{{ route('clockify.projetos.index') }}" class="btn btn-warning text-dark font-weight-bold shadow-sm">
                                <i class="fas fa-sliders-h mr-1"></i> Configurações Gerais
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- ============================================== -->
        <!-- VISÃO GLOBAL DE TODOS OS PROJETOS -->
        <!-- ============================================== -->
        <div class="row">
            <!-- Gráfico de horas por projeto -->
            <div class="col-md-7">
                <div class="card card-outline card-primary shadow-sm" style="border-radius:12px;">
                    <div class="card-header">
                        <h3 class="card-title text-sm text-bold"><i class="fas fa-chart-bar mr-1"></i> Horas por Projeto no Período</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="projChart" height="230"></canvas>
                    </div>
                </div>
            </div>

            <!-- Resumo / Projetos Ociosos -->
            <div class="col-md-5">
                <div class="card card-outline card-warning shadow-sm" style="border-radius:12px;">
                    <div class="card-header">
                        <h3 class="card-title text-sm text-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Projetos Ociosos</h3>
                    </div>
                    <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                        <ul class="nav nav-pills flex-column">
                            @forelse($inactiveProjects as $proj)
                                <li class="nav-item border-bottom p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-circle mr-2" style="color: {{ $proj['cor'] }};"></i>
                                            <span class="font-weight-bold">{{ $proj['nome'] }}</span>
                                        </div>
                                        <span class="text-xs text-muted">Sem registro no período</span>
                                    </div>
                                </li>
                            @empty
                                <li class="p-4 text-center text-muted text-sm">Todos os projetos ativos têm registros no período.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Projetos Ativos -->
        <p class="dash-section-title" style="font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#94a3b8; margin:28px 0 14px; display:flex; align-items:center; gap:10px;">
            Projetos com Horas no Período
            <span style="flex:1; height:1px; background:#e2e5ef;"></span>
        </p>

        <div class="proj-grid">
            @foreach($projectCards as $proj)
                @if($proj['hours'] > 0)
                    <div class="proj-card" style="--proj-color: {{ $proj['cor'] }};">
                        <div class="proj-card__header">
                            <h3 class="proj-card__name">{{ $proj['nome'] }}</h3>
                            <span class="badge font-weight-bold text-white" style="background: {{ $proj['cor'] }};">{{ number_format($proj['hours'], 1) }}h</span>
                        </div>
                        <div class="proj-card__metric">Membros envolvidos: <strong>{{ $proj['members_count'] }}</strong></div>
                        <div class="proj-card__metric">Lançamentos: <strong>{{ $proj['entries_count'] }}</strong></div>
                        <div class="proj-card__metric">Última atividade: <strong class="text-xs">{{ $proj['last_activity_human'] }}</strong></div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Matrix Projeto × Colaborador -->
        @if(!empty($matrix))
            <div class="card card-outline card-secondary shadow-sm mt-4" style="border-radius:12px;">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-th mr-1"></i> Matriz de Horas: Projeto × Colaborador</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 matrix-table">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-left pl-4" style="min-width: 200px;">Projeto</th>
                                    @foreach($matrixMembers as $member)
                                        <th>{{ $member->nome }}</th>
                                    @endforeach
                                    <th class="text-bold">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="matrix-total-row">
                                    <td class="project-col pl-4 text-left font-weight-bold">
                                        <i class="fas fa-equals mr-2 text-primary"></i>TOTAL
                                    </td>
                                    @foreach($matrixMembers as $member)
                                        @php $mt = $matrixTotals['members'][$member->id] ?? 0; @endphp
                                        <td>
                                            @if($mt > 0)
                                                <span class="badge badge-primary matrix-badge px-2 py-1 font-weight-bold">{{ number_format($mt, 1) }}h</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        <span class="badge badge-primary matrix-badge px-2 py-1 font-weight-bold">{{ number_format($matrixTotals['total'] ?? 0, 1) }}h</span>
                                    </td>
                                </tr>
                                @foreach($matrix as $row)
                                    <tr>
                                        <td class="project-col pl-4 text-left">
                                            <i class="fas fa-circle mr-2" style="color: {{ $row['project_color'] }}"></i>
                                            {{ $row['project_name'] }}
                                        </td>
                                        @foreach($matrixMembers as $member)
                                            @php
                                                $val = $row['members'][$member->id] ?? 0;
                                            @endphp
                                            <td>
                                                @if($val > 0)
                                                    <span class="badge badge-info matrix-badge px-2 py-1">{{ number_format($val, 1) }}h</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td>
                                            @if(($row['total'] ?? 0) > 0)
                                                <span class="badge badge-secondary matrix-badge px-2 py-1 font-weight-bold">{{ number_format($row['total'], 1) }}h</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching listener
    $('.clockify-tab-btn').on('click', function() {
        $('.clockify-tab-btn').removeClass('active');
        $(this).addClass('active');

        const target = $(this).data('target');
        $('.project-tab-content').hide();
        $(target).show();
    });

    // Dropdown URL change listener
    document.getElementById('project-filter-select').addEventListener('change', function() {
        const val = this.value;
        const url = new URL(window.location.href);
        if (val === 'all') {
            url.searchParams.delete('projeto_id');
        } else {
            url.searchParams.set('projeto_id', val);
        }
        window.location.href = url.toString();
    });

    @if($isSingleProject)
        // Render Single Project Status Donut
        const ctx = document.getElementById('projectStatusDonut').getContext('2d');
        const billable = {{ $billableHours }};
        const nonBillable = {{ $nonBillableHours }};
        const budget = {{ $budgetHours }};
        const remaining = Math.max(budget - (billable + nonBillable), 0);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Faturável', 'Não-Faturável', 'Restante'],
                datasets: [{
                    data: [billable, nonBillable, remaining],
                    backgroundColor: ['#0ea5e9', '#84cc16', '#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { cornerRadius: 8 }
                }
            }
        });
    @else
        // Render Global Projects bar chart
        const ctx = document.getElementById('projChart').getContext('2d');
        const labels = {!! json_encode($chartLabels) !!};
        const data = {!! json_encode($chartData) !!};
        const colors = {!! json_encode($chartColors) !!};

        if (labels.length === 0) {
            ctx.font = '14px Arial';
            ctx.fillStyle = '#6c757d';
            ctx.textAlign = 'center';
            ctx.fillText('Sem dados de horas registradas no período.', 150, 100);
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderRadius: 6,
                    maxBarThickness: 35,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { cornerRadius: 8, padding: 10 }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } }, beginAtZero: true }
                }
            }
        });
    @endif

    // Client-side quick filter for tasks table
    $('#task-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#task-table-tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });
});
</script>
@stop
