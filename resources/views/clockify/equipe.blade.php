@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center mb-3">
        <x-page-title :title="$titulo" />
        <div class="badge badge-info py-2 px-3 shadow-sm border" id="autorefresh-indicator" style="border-radius: 20px; font-family:'Plus Jakarta Sans',sans-serif; font-size:12px;">
            <i class="fas fa-sync fa-spin mr-2"></i> Autorefresh ativo: <span id="countdown">10</span>s
        </div>
    </div>
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { background-color: #f4f6f9; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    .clockify-tabs { display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; background: #fff; padding: 10px 16px 0; border-radius: 12px 12px 0 0; }
    .clockify-tab-btn {
        background: transparent; border: none; padding: 12px 24px; font-size: 13px; font-weight: 700;
        color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s ease;
        margin-bottom: -2px; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .clockify-tab-btn:hover { color: #1e293b; }
    .clockify-tab-btn.active { color: #0284c7; border-bottom-color: #0284c7; }

    .clockify-card-summary {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02); text-align: center;
        padding: 24px 15px; margin-bottom: 20px; transition: transform 0.25s ease;
    }
    .clockify-card-summary:hover { transform: translateY(-2px); }
    .clockify-card-summary__label {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px;
        font-weight: 700; text-transform: uppercase; color: #94a3b8;
        letter-spacing: 0.08em; margin-bottom: 6px;
    }
    .clockify-card-summary__value {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 28px;
        font-weight: 800; color: #1e293b; margin: 0; letter-spacing: -0.02em;
    }

    .chart-container-large {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .chart-container-large__title {
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px;
        font-weight: 800; color: #1e293b; margin-bottom: 20px;
        display: flex; align-items: center; gap: 8px;
    }

    .eq-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .eq-card {
        background: #fff; border-radius: 12px; padding: 18px;
        border: 1px solid #eef1f6; display: flex; flex-direction: column;
        position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }
    .eq-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background: var(--status-color, #94a3b8); }
    .eq-card__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .eq-card__name { font-family:'Plus Jakarta Sans',sans-serif; font-size:14px; font-weight:700; color:#1a1d2e; margin:0; }
    
    .status-badge { font-size: 9px; font-weight: 800; text-transform: uppercase; padding: 3px 8px; border-radius: 999px; }
    .status-badge.tracking { background: #dcfce7; color: #166534; }
    .status-badge.recent { background: #e0f2fe; color: #0369a1; }
    .status-badge.offline { background: #f1f5f9; color: #475569; }
    .status-badge.inactive { background: #fee2e2; color: #991b1b; }

    .eq-card.tracking { --status-color: #22c55e; }
    .eq-card.recent { --status-color: #0ea5e9; }
    .eq-card.offline { --status-color: #94a3b8; }
    .eq-card.inactive { --status-color: #ef4444; }

    .eq-card__meta { font-size: 12px; color: #64748b; margin-bottom: 6px; }
    .eq-card__meta strong { color: #1a1d2e; }

    .progress-list-item { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 12px; }
    .progress-list-item__name { font-weight: 600; color: #475569; width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .progress-list-item__bar-wrap { flex-grow: 1; height: 8px; background: #f1f5f9; border-radius: 999px; margin: 0 12px; overflow: hidden; }
    .progress-list-item__bar { height: 100%; border-radius: 999px; background: #0891b2; }
    .progress-list-item__hours { font-weight: 700; color: #1e293b; width: 60px; text-align: right; }
    .progress-list-item__percent { font-weight: 600; color: #94a3b8; width: 45px; text-align: right; }

    /* Gantt Planner Table Styles */
    .planner-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; }
    .planner-table-container { overflow-x: auto; width: 100%; }
    .planner-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .planner-table th, .planner-table td { border: 1px solid #e2e8f0; padding: 10px; font-size: 12px; vertical-align: middle; }
    
    .planner-table th.col-member { width: 250px; background: #f8fafc; font-weight: 800; color: #475569; text-align: left; }
    .planner-table th.col-hours { width: 80px; background: #f8fafc; font-weight: 800; color: #475569; text-align: center; }
    .planner-table th.col-day { width: 45px; background: #f8fafc; text-align: center; font-weight: 700; color: #64748b; }
    .planner-table th.col-day.today { background: #e0f2fe; color: #0284c7; }

    .planner-member-cell { display: flex; align-items: center; gap: 10px; }
    .planner-member-avatar {
        width: 32px; height: 32px; border-radius: 50%; background: #38bdf8;
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 12px; border: 1px solid rgba(0,0,0,0.05);
    }
    .planner-member-name { font-weight: 700; color: #1e293b; }

    /* Gantt Blocks */
    .planner-block {
        border-radius: 6px; padding: 6px 4px; font-size: 10px; font-weight: 800; text-align: center;
        border: 1px solid transparent; min-height: 28px; display: flex; align-items: center; justify-content: center;
        text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer;
        transition: transform 0.15s ease;
    }
    .planner-block:hover { transform: scale(1.05); }
    .planner-block.green { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
    .planner-block.green-light { background: #f0fdf4; border-color: #dcfce7; color: #15803d; }
    .planner-block.red { background: #fee2e2; border-color: #fecdd3; color: #991b1b; }
    .planner-block.pink { background: #fce7f3; border-color: #fbcfe8; color: #9d174d; }

    /* Expandable rows */
    .planner-sub-row { background: #fafafa; display: none; }
    .planner-sub-row.is-open { display: table-row; }
    .btn-expand-member { background: transparent; border: none; color: #64748b; cursor: pointer; padding: 0 4px; transition: transform 0.2s; }
    .btn-expand-member.is-open { transform: rotate(180deg); }

    .clickable-day-cell { cursor: pointer; transition: background 0.15s; }
    .clickable-day-cell:hover { background: #f8fafc; }
</style>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('home.equipe') }}" method="GET" class="form-inline" id="filter-form">
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
                <a href="{{ route('home.equipe') }}" class="btn btn-sm btn-default mb-2 ml-2">Limpar</a>
            </form>
        </div>
    </div>

    <!-- Navegação de Abas do Clockify -->
    <div class="clockify-tabs">
        <button class="clockify-tab-btn active" onclick="switchTab('tab-dashboard')">
            <i class="fas fa-chart-pie mr-2"></i> Dashboard Analítico
        </button>
        <button class="clockify-tab-btn" onclick="switchTab('tab-planner')">
            <i class="fas fa-calendar-alt mr-2"></i> Planejador (Equipe)
        </button>
    </div>

    <!-- Aba 1: Dashboard Analítico -->
    <div id="tab-dashboard" class="clockify-tab-content">
        <!-- Alertas -->
        <div id="alerts-container">
            @if(!empty($inactiveMembers))
                <div class="alert alert-warning alert-dismissible shadow-sm mb-4" style="border-radius:12px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5>
                    Há <strong>{{ count($inactiveMembers) }}</strong> colaboradores sem registrar tempo há mais de 7 dias:
                    <span>{{ implode(', ', array_column($inactiveMembers, 'nome')) }}</span>.
                </div>
            @endif
        </div>

        <!-- Cards de Resumo Estilo Premium -->
        <div class="row">
            <div class="col-md-4">
                <div class="clockify-card-summary">
                    <div class="clockify-card-summary__label">Total Time</div>
                    <div class="clockify-card-summary__value" id="kpi-total-time">{{ $totalTimeFormatted }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="clockify-card-summary">
                    <div class="clockify-card-summary__label">Top Member</div>
                    <div class="clockify-card-summary__value" id="kpi-top-member" style="font-size: 20px; line-height: 1.45; font-weight: 800;">
                        {{ $topMemberName }}
                        <span class="text-sm text-muted d-block" style="font-weight: 600;">{{ number_format($topMemberHours, 1) }}h registradas</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="clockify-card-summary">
                    <div class="clockify-card-summary__label">Top Project</div>
                    <div class="clockify-card-summary__value" id="kpi-top-project" style="font-size: 20px; line-height: 1.45; font-weight: 800;">
                        {{ $topProjectName }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de barras empilhadas grande (Breakdown diário por colaborador) -->
        <div class="chart-container-large">
            <div class="chart-container-large__title">
                <i class="fas fa-chart-bar text-primary"></i> Tempo Rastreado Diário por Colaborador
            </div>
            <div style="position: relative; height: 260px;">
                <canvas id="stackedHoursChart"></canvas>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Rosca de Distribuição Lateral -->
            <div class="col-md-5">
                <div class="card card-outline card-primary shadow-sm" style="border-radius: 12px;">
                    <div class="card-header">
                        <h3 class="card-title text-sm text-bold"><i class="fas fa-chart-pie mr-1"></i> Distribuição (%)</h3>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 210px;">
                            <canvas id="distChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listagem ordenada com barras horizontais e porcentagem -->
            <div class="col-md-7">
                <div class="card card-outline card-secondary shadow-sm" style="border-radius: 12px;">
                    <div class="card-header">
                        <h3 class="card-title text-sm text-bold"><i class="fas fa-list-ol mr-1"></i> Participação da Equipe</h3>
                    </div>
                    <div class="card-body" id="progress-list-container" style="max-height: 250px; overflow-y: auto;">
                        @php
                            $totalHoursSum = collect($distribution)->sum('hours') ?: 1;
                        @endphp
                        @forelse($distribution as $d)
                            @php
                                $percentage = ($d['hours'] / $totalHoursSum) * 100;
                            @endphp
                            <div class="progress-list-item">
                                <span class="progress-list-item__name">{{ $d['nome'] }}</span>
                                <div class="progress-list-item__bar-wrap">
                                    <div class="progress-list-item__bar" style="width: {{ $percentage }}%;"></div>
                                </div>
                                <span class="progress-list-item__hours">{{ number_format($d['hours'], 1) }}h</span>
                                <span class="progress-list-item__percent">{{ number_format($percentage, 0) }}%</span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">Nenhum registro de tempo no período.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Rastreadores Ativos Agora -->
        <div class="card card-outline card-danger shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-header">
                <h3 class="card-title text-sm text-bold"><i class="fas fa-spinner fa-pulse text-danger mr-1"></i> Rastreadores Ativos no Momento</h3>
                <div class="card-tools">
                    <span class="badge badge-danger" id="total-tracking-badge">{{ $totalTracking }} online</span>
                </div>
            </div>
            <div class="card-body p-0" style="max-height: 260px; overflow-y: auto;">
                <ul class="nav nav-pills flex-column" id="active-trackers-list">
                    @forelse ($activeTrackers as $active)
                        <li class="nav-item border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-bold text-dark" style="font-size:14px;">{{ $active->user->nome }}</span>
                                    <span class="text-muted text-sm ml-1">está trabalhando em</span>
                                    <span class="text-bold text-sm" style="color: {{ $active->projeto->cor ?? '#6c757d' }}">{{ $active->projeto->nome ?? 'Sem Projeto' }}</span>
                                    <br>
                                    <span class="text-muted text-xs"><i class="far fa-edit"></i> {{ $active->descricao ?: 'Sem descrição' }}</span>
                                </div>
                                <span class="badge badge-success px-2 py-1 text-sm font-weight-bold">
                                    <i class="far fa-clock mr-1"></i> {{ $active->tempoFormato }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="p-4 text-center text-muted text-sm" id="no-active-msg">Ninguém da equipe está rastreando tempo no momento.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Status da Equipe -->
        <p class="dash-section-title" style="font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#94a3b8; margin:28px 0 14px; display:flex; align-items:center; gap:10px;">
            Todos os Membros da Equipe
            <span style="flex:1; height:1px; background:#e2e5ef;"></span>
        </p>
        
        <div class="eq-grid" id="members-status-grid">
            @foreach($memberStatus as $member)
                <div class="eq-card {{ $member['status'] }}" data-user-id="{{ $member['id'] }}">
                    <div class="eq-card__header">
                        <h3 class="eq-card__name">{{ $member['nome'] }}</h3>
                        <span class="status-badge {{ $member['status'] }}">
                            @if($member['status'] === 'tracking')
                                Rastreando
                            @elseif($member['status'] === 'recent')
                                Ativo recente
                            @elseif($member['status'] === 'inactive')
                                Inativo
                            @else
                                Offline
                            @endif
                        </span>
                    </div>
                    <div class="eq-card__meta">Horas no período: <strong>{{ number_format($member['period_hours'], 1) }}h</strong></div>
                    <div class="eq-card__meta">
                        Última atividade: 
                        @if($member['last_activity'])
                            <strong>{{ \Carbon\Carbon::parse($member['last_activity'])->format('d/m/Y H:i') }}</strong>
                        @else
                            <span class="text-muted">Nunca</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Aba 2: Planejador (Equipe) Gantt Interativo -->
    <div id="tab-planner" class="clockify-tab-content d-none">
        <div class="planner-card">
            <div class="planner-table-container">
                <table class="planner-table" id="gantt-planner-table">
                    <thead>
                        <tr>
                            <th class="col-member">Colaborador</th>
                            <th class="col-hours">Planejado</th>
                            @foreach($plannerDays as $pDay)
                                @php
                                    $isToday = $pDay['date_str'] === date('Y-m-d');
                                @endphp
                                <th class="col-day {{ $isToday ? 'today' : '' }}" title="{{ $pDay['date_str'] }}">
                                    <div>{{ $pDay['day_name'] }}</div>
                                    <div style="font-size:13px; font-weight:800;">{{ $pDay['day_num'] }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="gantt-planner-body">
                        @foreach($plannerData as $member)
                            <tr class="planner-member-row" data-user-id="{{ $member['id'] }}">
                                <td class="col-member">
                                    <div class="planner-member-cell">
                                        <button class="btn-expand-member" onclick="togglePlannerExpand({{ $member['id'] }}, this)">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        @php
                                            $hash = crc32($member['nome']);
                                            $colorsList = ['#0891b2', '#059669', '#4f46e5', '#d97706', '#ec4899', '#84cc16', '#e11d48'];
                                            $avatarColor = $colorsList[$hash % count($colorsList)];
                                        @endphp
                                        <div class="planner-member-avatar" style="background-color: {{ $avatarColor }};">
                                            {{ $member['initials'] }}
                                        </div>
                                        <div class="planner-member-name" title="{{ $member['nome'] }}">
                                            {{ $member['nome'] }}
                                        </div>
                                    </div>
                                </td>
                                <td class="col-hours text-center font-weight-bold text-muted" id="planner-total-hours-{{ $member['id'] }}">
                                    {{ number_format($member['total_hours'], 1) }}h
                                </td>
                                @foreach($plannerDays as $pDay)
                                    @php
                                        $dayInfo = $member['days'][$pDay['date_str']];
                                    @endphp
                                    <td class="clickable-day-cell" onclick="openAddAllocationModal({{ $member['id'] }}, '{{ $pDay['date_str'] }}')" data-date="{{ $pDay['date_str'] }}">
                                        @if($dayInfo['type'] !== 'empty')
                                            <div class="planner-block {{ $dayInfo['color_class'] }}" 
                                                 title="{{ $dayInfo['label'] }}"
                                                 @if($dayInfo['type'] === 'alloc')
                                                    onclick="event.stopPropagation(); openEditAllocationModal({{ $dayInfo['alloc_id'] }}, '{{ $dayInfo['projeto_nome'] }}', {{ $dayInfo['hours'] }}, '{{ $dayInfo['observacao'] }}')"
                                                 @endif>
                                                {{ $dayInfo['type'] === 'off' ? $dayInfo['label'] : ($dayInfo['type'] === 'alloc' ? $dayInfo['projeto_nome'] : 'TRAB.') }}
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            
                            <!-- Sub-rows showing details per project -->
                            @foreach($member['allocations'] as $alloc)
                                <tr class="planner-sub-row planner-sub-{{ $member['id'] }}" data-alloc-id="{{ $alloc['id'] }}">
                                    <td class="col-member pl-5" style="background:#fafafa;">
                                        <div class="pl-4 text-xs font-weight-bold" style="color: {{ $alloc['projeto_cor'] }}">
                                            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> {{ $alloc['projeto_nome'] }}
                                        </div>
                                    </td>
                                    <td class="col-hours text-center text-xs" style="background:#fafafa;">
                                        {{ $alloc['horas_por_dia'] }}h/dia
                                    </td>
                                    @foreach($plannerDays as $pDay)
                                        @php
                                            $dayDate = \Carbon\Carbon::parse($pDay['date_str']);
                                            $isAlloc = $dayDate->between(\Carbon\Carbon::parse($alloc['data_inicio'])->startOfDay(), \Carbon\Carbon::parse($alloc['data_fim'])->endOfDay());
                                        @endphp
                                        <td style="background:#fafafa;">
                                            @if($isAlloc)
                                                <div class="planner-block" 
                                                     style="background: rgba(14, 165, 233, 0.08); border-color: rgba(14, 165, 233, 0.2); color: #0284c7;"
                                                     onclick="openEditAllocationModal({{ $alloc['id'] }}, '{{ $alloc['projeto_nome'] }}', {{ $alloc['horas_por_dia'] }}, '{{ $alloc['observacao'] }}')"
                                                     title="Planejado: {{ $alloc['horas_por_dia'] }}h/dia">
                                                    {{ $alloc['horas_por_dia'] }}h
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE CADASTRO DE ALOCAÇÃO -->
    <div class="modal fade" id="modalAddAllocation" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-plus mr-2"></i> Agendar Alocação de Tempo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAddAllocation">
                    @csrf
                    <input type="hidden" name="user_id" id="modal-user-id">
                    <div class="modal-body p-4">
                        <div class="form-group">
                            <label class="text-xs font-weight-bold text-muted">Membro da Equipe</label>
                            <input type="text" id="modal-user-name" class="form-control" readonly style="background:#f8fafc; border-radius:6px;">
                        </div>
                        <div class="form-group">
                            <label for="modal-projeto-id" class="text-xs font-weight-bold text-muted">Projeto <span class="text-danger">*</span></label>
                            <select name="clockify_projeto_id" id="modal-projeto-id" class="form-control" required style="border-radius:6px;">
                                <option value="">Selecione o projeto...</option>
                                @foreach ($usuarios[0]->empresa->projetos ?? \App\Models\ClockifyProjeto::where('ativo', true)->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 form-group">
                                <label for="modal-data-inicio" class="text-xs font-weight-bold text-muted">Data de Início <span class="text-danger">*</span></label>
                                <input type="date" name="data_inicio" id="modal-data-inicio" class="form-control" required style="border-radius:6px;">
                            </div>
                            <div class="col-6 form-group">
                                <label for="modal-data-fim" class="text-xs font-weight-bold text-muted">Data de Fim <span class="text-danger">*</span></label>
                                <input type="date" name="data_fim" id="modal-data-fim" class="form-control" required style="border-radius:6px;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="modal-horas" class="text-xs font-weight-bold text-muted">Horas por Dia</label>
                            <input type="number" step="0.5" name="horas_por_dia" id="modal-horas" class="form-control" value="8.0" min="0.5" max="24" style="border-radius:6px;">
                        </div>
                        <div class="form-group mb-0">
                            <label for="modal-obs" class="text-xs font-weight-bold text-muted">Observação</label>
                            <textarea name="observacao" id="modal-obs" rows="2" class="form-control" placeholder="Algum detalhe opcional..." style="border-radius:6px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" style="border-radius:8px;">Cancelar</button>
                        <button type="submit" class="btn btn-success font-weight-bold" style="border-radius:8px;"><i class="fas fa-check mr-1"></i> Confirmar Alocação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE DETALHES / EXCLUSÃO DE ALOCAÇÃO -->
    <div class="modal fade" id="modalEditAllocation" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div class="modal-header bg-dark text-white" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-sm"><i class="fas fa-calendar-alt mr-2"></i> Alocação Planejada</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-1 text-xs text-muted font-weight-bold">PROJETO</p>
                    <h5 class="font-weight-bold text-primary mb-3" id="edit-proj-name">--</h5>
                    
                    <p class="mb-1 text-xs text-muted font-weight-bold">CARGA ALOCADA</p>
                    <p class="font-weight-bold text-dark mb-3"><span id="edit-hours">--</span> horas por dia</p>

                    <div id="edit-obs-wrap" class="d-none">
                        <p class="mb-1 text-xs text-muted font-weight-bold">OBSERVAÇÃO</p>
                        <p class="text-sm text-dark mb-0 bg-light p-2 border" style="border-radius: 6px;" id="edit-obs">--</p>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light d-flex justify-content-between" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary font-weight-bold text-xs" data-dismiss="modal" style="border-radius:8px;">Fechar</button>
                    <button type="button" class="btn btn-danger font-weight-bold text-xs" id="btn-delete-allocation" style="border-radius:8px;"><i class="fas fa-trash mr-1"></i> Remover</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Switch between Tabs
function switchTab(tabId) {
    document.querySelectorAll('.clockify-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.clockify-tab-content').forEach(content => content.classList.add('d-none'));

    // Set active button
    const activeBtn = Array.from(document.querySelectorAll('.clockify-tab-btn')).find(btn => btn.getAttribute('onclick').includes(tabId));
    if (activeBtn) activeBtn.classList.add('active');

    // Show content
    document.getElementById(tabId).classList.remove('d-none');
}

// Toggle sub-rows Gantt
function togglePlannerExpand(userId, btn) {
    const subRows = document.querySelectorAll('.planner-sub-' + userId);
    const isOpen = btn.classList.contains('is-open');

    if (isOpen) {
        btn.classList.remove('is-open');
        subRows.forEach(r => r.classList.remove('is-open'));
    } else {
        btn.classList.add('is-open');
        subRows.forEach(r => r.classList.add('is-open'));
    }
}

// Open modal for adding allocation
const memberList = @json($plannerData);
function openAddAllocationModal(userId, dateStr) {
    const member = memberList.find(m => m.id == userId);
    if (!member) return;

    document.getElementById('modal-user-id').value = userId;
    document.getElementById('modal-user-name').value = member.nome;
    document.getElementById('modal-data-inicio').value = dateStr;
    document.getElementById('modal-data-fim').value = dateStr;

    $('#modalAddAllocation').modal('show');
}

// Open modal for deleting allocation
let selectedAllocIdForDelete = null;
function openEditAllocationModal(allocId, projName, hours, obs) {
    selectedAllocIdForDelete = allocId;
    document.getElementById('edit-proj-name').textContent = projName;
    document.getElementById('edit-hours').textContent = hours;
    
    const obsWrap = document.getElementById('edit-obs-wrap');
    if (obs) {
        obsWrap.classList.remove('d-none');
        document.getElementById('edit-obs').textContent = obs;
    } else {
        obsWrap.classList.add('d-none');
    }

    $('#modalEditAllocation').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    let myChart = null;
    let stackedChart = null;

    // Data lists from Backend
    let distData = {!! json_encode($distribution) !!};
    let dateLabels = {!! json_encode($dateLabels) !!};
    let memberStats = {!! json_encode($memberStats) !!};
    let plannerDays = {!! json_encode($plannerDays) !!};

    const colors = [
        '#0891b2', '#059669', '#4f46e5', '#d97706', '#ec4899', '#84cc16', '#e11d48',
        '#6366f1', '#14b8a6', '#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#8b5cf6'
    ];

    function renderDoughnutChart() {
        const ctx = document.getElementById('distChart').getContext('2d');
        if (myChart) {
            myChart.destroy();
        }

        if (distData.length === 0) {
            ctx.clearRect(0, 0, 300, 210);
            ctx.font = '14px Arial';
            ctx.fillStyle = '#6c757d';
            ctx.textAlign = 'center';
            ctx.fillText('Sem dados registrados no período.', 150, 100);
            return;
        }

        myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: distData.map(d => d.nome),
                datasets: [{
                    data: distData.map(d => d.hours),
                    backgroundColor: colors.slice(0, distData.length),
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: { cornerRadius: 8 }
                }
            }
        });
    }

    function renderStackedBarChart() {
        const ctx = document.getElementById('stackedHoursChart').getContext('2d');
        if (stackedChart) {
            stackedChart.destroy();
        }

        const datasets = Object.entries(memberStats).map(([id, data], index) => ({
            label: data.name,
            backgroundColor: colors[index % colors.length],
            data: data.dailyOrdered,
            barPercentage: 0.6,
            categoryPercentage: 0.8
        }));

        const formattedLabels = dateLabels.map(d => {
            const parts = d.split('-');
            const dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
            return dateObj.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });
        });

        stackedChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: formattedLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { font: { size: 9 } } },
                    y: { stacked: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 9 } }, beginAtZero: true }
                }
            }
        });
    }

    renderDoughnutChart();
    renderStackedBarChart();

    // Auto-Refresh (10 seconds)
    let timeLeft = 10;
    const countdownEl = document.getElementById('countdown');
    const indicatorEl = document.getElementById('autorefresh-indicator');

    function updateCountdown() {
        timeLeft--;
        if (timeLeft < 0) {
            timeLeft = 10;
            refreshData();
        }
        countdownEl.textContent = timeLeft;
    }

    let intervalId = setInterval(updateCountdown, 1000);

    // Save allocation via AJAX
    document.getElementById('formAddAllocation').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('operacao.calendario.store') }}",
            type: 'POST',
            data: formData,
            success: function() {
                $('#modalAddAllocation').modal('hide');
                document.getElementById('formAddAllocation').reset();
                refreshData();
            },
            error: function(err) {
                alert('Erro ao agendar alocação. Verifique os dados.');
            }
        });
    });

    // Delete allocation via AJAX
    document.getElementById('btn-delete-allocation').addEventListener('click', function() {
        if (!selectedAllocIdForDelete) return;
        if (!confirm('Deseja realmente remover esta alocação planejada?')) return;

        $.ajax({
            url: `/admin/calendario/${selectedAllocIdForDelete}`,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                _method: 'DELETE'
            },
            success: function() {
                $('#modalEditAllocation').modal('hide');
                refreshData();
            },
            error: function() {
                alert('Erro ao remover alocação.');
            }
        });
    });

    function refreshData() {
        indicatorEl.classList.remove('badge-info');
        indicatorEl.classList.add('badge-warning');
        indicatorEl.innerHTML = '<i class="fas fa-sync fa-spin mr-2"></i> Atualizando...';

        const dataInicio = document.getElementById('data_inicio').value;
        const dataFim = document.getElementById('data_fim').value;

        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: {
                data_inicio: dataInicio,
                data_fim: dataFim
            },
            dataType: 'json',
            success: function(response) {
                // Update KPIs
                document.getElementById('kpi-total-time').textContent = response.totalTimeFormatted;
                
                const topMemHtml = response.topMemberName + `<span class="text-sm text-muted d-block" style="font-weight: 600;">${response.topMemberHours.toFixed(1)}h registradas</span>`;
                document.getElementById('kpi-top-member').innerHTML = topMemHtml;
                document.getElementById('kpi-top-project').textContent = response.topProjectName;

                // Update trackers count badge
                document.getElementById('total-tracking-badge').textContent = response.totalTracking + ' online';

                // Update active trackers list
                const listEl = document.getElementById('active-trackers-list');
                listEl.innerHTML = '';
                if (response.activeTrackersFormatted && response.activeTrackersFormatted.length > 0) {
                    response.activeTrackersFormatted.forEach(function(tracker) {
                        const li = document.createElement('li');
                        li.className = 'nav-item border-bottom p-3';
                        li.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-bold text-dark" style="font-size:14px;">${tracker.user_nome}</span>
                                    <span class="text-muted text-sm ml-1">está trabalhando em</span>
                                    <span class="text-bold text-sm" style="color: ${tracker.tracker_cor || tracker.projeto_cor}">${tracker.projeto_name}</span>
                                    <br>
                                    <span class="text-muted text-xs"><i class="far fa-edit"></i> ${tracker.descricao}</span>
                                </div>
                                <span class="badge badge-success px-2 py-1 text-sm font-weight-bold">
                                    <i class="far fa-clock mr-1"></i> ${tracker.tempoFormato}
                                </span>
                            </div>
                        `;
                        listEl.appendChild(li);
                    });
                } else {
                    listEl.innerHTML = '<li class="p-4 text-center text-muted text-sm">Ninguém da equipe está rastreando tempo no momento.</li>';
                }

                // Update alerts
                const alertsContainer = document.getElementById('alerts-container');
                if (response.inactiveMembers && response.inactiveMembers.length > 0) {
                    const names = response.inactiveMembers.map(m => m.nome).join(', ');
                    alertsContainer.innerHTML = `
                        <div class="alert alert-warning alert-dismissible shadow-sm mb-4" style="border-radius:12px;">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5>
                            Há <strong>${response.inactiveMembers.length}</strong> colaboradores sem registrar tempo há mais de 7 dias:
                            <span>${names}</span>.
                        </div>
                    `;
                } else {
                    alertsContainer.innerHTML = '';
                }

                // Update Member status grid (no duplicate details)
                const gridEl = document.getElementById('members-status-grid');
                gridEl.innerHTML = '';
                response.memberStatus.forEach(function(member) {
                    const card = document.createElement('div');
                    card.className = `eq-card ${member.status}`;
                    card.setAttribute('data-user-id', member.id);

                    let statusLabel = 'Offline';
                    if (member.status === 'tracking') statusLabel = 'Rastreando';
                    else if (member.status === 'recent') statusLabel = 'Ativo recente';
                    else if (member.status === 'inactive') statusLabel = 'Inativo';

                    let lastActStr = '<span class="text-muted">Nunca</span>';
                    if (member.last_activity) {
                        const d = new Date(member.last_activity);
                        const day = String(d.getDate()).padStart(2, '0');
                        const month = String(d.getMonth() + 1).padStart(2, '0');
                        const year = d.getFullYear();
                        const hours = String(d.getHours()).padStart(2, '0');
                        const minutes = String(d.getMinutes()).padStart(2, '0');
                        lastActStr = `<strong>${day}/${month}/${year} ${hours}:${minutes}</strong>`;
                    }

                    card.innerHTML = `
                        <div class="eq-card__header">
                            <h3 class="eq-card__name">${member.nome}</h3>
                            <span class="status-badge ${member.status}">${statusLabel}</span>
                        </div>
                        <div class="eq-card__meta">Horas no período: <strong>${member.period_hours.toFixed(1)}h</strong></div>
                        <div class="eq-card__meta">Última atividade: ${lastActStr}</div>
                    `;
                    gridEl.appendChild(card);
                });

                // Update Progress list (participation)
                const progressContainer = document.getElementById('progress-list-container');
                progressContainer.innerHTML = '';
                const totalHoursSum = response.distribution.reduce((acc, curr) => acc + curr.hours, 0) || 1;
                
                if (response.distribution.length > 0) {
                    response.distribution.forEach(function(d) {
                        const pct = (d.hours / totalHoursSum) * 100;
                        const item = document.createElement('div');
                        item.className = 'progress-list-item';
                        item.innerHTML = `
                            <span class="progress-list-item__name">${d.nome}</span>
                            <div class="progress-list-item__bar-wrap">
                                <div class="progress-list-item__bar" style="width: ${pct}%;"></div>
                            </div>
                            <span class="progress-list-item__hours">${d.hours.toFixed(1)}h</span>
                            <span class="progress-list-item__percent">${pct.toFixed(0)}%</span>
                        `;
                        progressContainer.appendChild(item);
                    });
                } else {
                    progressContainer.innerHTML = '<div class="text-center text-muted py-5">Nenhum registro de tempo no período.</div>';
                }

                // Update Gantt Planner
                const plannerBody = document.getElementById('gantt-planner-body');
                plannerBody.innerHTML = '';
                
                // Update local memory list for modal
                memberList.length = 0;
                response.plannerData.forEach(function(member) {
                    memberList.push(member);

                    // Render main member row
                    const tr = document.createElement('tr');
                    tr.className = 'planner-member-row';
                    tr.setAttribute('data-user-id', member.id);

                    let hash = 0;
                    for (let i = 0; i < member.nome.length; i++) {
                        hash = member.nome.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    const avatarColor = colors[Math.abs(hash) % colors.length];

                    let dayCellsHtml = '';
                    response.plannerDays.forEach(function(pDay) {
                        const dayInfo = member.days[pDay.date_str];
                        let blockHtml = '';

                        if (dayInfo.type !== 'empty') {
                            if (dayInfo.type === 'alloc') {
                                blockHtml = `<div class="planner-block ${dayInfo.color_class}" title="${dayInfo.label}" onclick="event.stopPropagation(); openEditAllocationModal(${dayInfo.alloc_id}, '${dayInfo.projeto_nome}', ${dayInfo.hours}, '${dayInfo.observacao}')">${dayInfo.projeto_name}</div>`;
                            } else if (dayInfo.type === 'off') {
                                blockHtml = `<div class="planner-block ${dayInfo.color_class}" title="${dayInfo.label}">${dayInfo.label}</div>`;
                            } else {
                                blockHtml = `<div class="planner-block ${dayInfo.color_class}" title="${dayInfo.label}">TRAB.</div>`;
                            }
                        }

                        dayCellsHtml += `<td class="clickable-day-cell" onclick="openAddAllocationModal(${member.id}, '${pDay.date_str}')">${blockHtml}</td>`;
                    });

                    tr.innerHTML = `
                        <td class="col-member">
                            <div class="planner-member-cell">
                                <button class="btn-expand-member" onclick="togglePlannerExpand(${member.id}, this)">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="planner-member-avatar" style="background-color: ${avatarColor};">
                                    ${member.initials}
                                </div>
                                <div class="planner-member-name" title="${member.nome}">
                                    ${member.nome}
                                </div>
                            </div>
                        </td>
                        <td class="col-hours text-center font-weight-bold text-muted">
                            ${member.total_hours.toFixed(1)}h
                        </td>
                        ${dayCellsHtml}
                    `;
                    plannerBody.appendChild(tr);

                    // Render child sub-rows per project allocation
                    member.allocations.forEach(function(alloc) {
                        const subTr = document.createElement('tr');
                        subTr.className = `planner-sub-row planner-sub-${member.id}`;
                        subTr.setAttribute('data-alloc-id', alloc.id);

                        let subDayCellsHtml = '';
                        response.plannerDays.forEach(function(pDay) {
                            const dayDate = new Date(pDay.date_str);
                            const startD = new Date(alloc.data_inicio);
                            const endD = new Date(alloc.data_fim);
                            
                            // Simple date comparison
                            startD.setHours(0,0,0,0);
                            endD.setHours(23,59,59,999);
                            
                            const isAlloc = dayDate >= startD && dayDate <= endD;
                            let blockHtml = '';

                            if (isAlloc) {
                                blockHtml = `<div class="planner-block" style="background: rgba(14, 165, 233, 0.08); border-color: rgba(14, 165, 233, 0.2); color: #0284c7;" onclick="openEditAllocationModal(${alloc.id}, '${alloc.projeto_name}', ${alloc.horas_por_dia}, '${alloc.observacao}')" title="Planejado: ${alloc.horas_por_dia}h/dia">${alloc.horas_por_dia}h</div>`;
                            }
                            subDayCellsHtml += `<td style="background:#fafafa;">${blockHtml}</td>`;
                        });

                        subTr.innerHTML = `
                            <td class="col-member pl-5" style="background:#fafafa;">
                                <div class="pl-4 text-xs font-weight-bold" style="color: ${alloc.projeto_cor}">
                                    <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> ${alloc.projeto_name}
                                </div>
                            </td>
                            <td class="col-hours text-center text-xs" style="background:#fafafa;">
                                ${alloc.horas_por_dia}h/dia
                            </td>
                            ${subDayCellsHtml}
                        `;
                        plannerBody.appendChild(subTr);
                    });
                });

                // Update charts data
                distData = response.distribution;
                dateLabels = response.dateLabels;
                memberStats = response.memberStats;
                plannerDays = response.plannerDays;
                
                renderDoughnutChart();
                renderStackedBarChart();
            },
            complete: function() {
                indicatorEl.classList.remove('badge-warning');
                indicatorEl.classList.add('badge-info');
                indicatorEl.innerHTML = '<i class="fas fa-sync fa-spin mr-2"></i> Autorefresh ativo: <span id="countdown">10</span>s';
                timeLeft = 10;
            }
        });
    }
});
</script>
@stop
