@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('css')
@if(!$projeto)
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@endif
<style>
    body { background-color: #f4f6f9; }

    .planning-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    }

    /* Team rows in main table */
    .team-row {
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .team-row:hover { background-color: #f0f7ff !important; }
    .team-row.expanded { background-color: #e8f4fd !important; }

    /* Expansion row */
    .expand-row { display: none; }
    .expand-row.open { display: table-row; }
    .expand-inner {
        background: #f8fafc;
        border-left: 4px solid #0284c7;
        border-radius: 0 0 8px 8px;
        padding: 16px 20px;
    }

    /* Member table inside expansion */
    .member-table th {
        background: #f1f5f9;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    .member-row-allocated { background: #f0fdf4 !important; }
    .member-row-allocated td { border-left: 3px solid #28a745; }

    /* Date input constraint indicator */
    input[type="date"]:invalid,
    input[type="date"].out-of-range {
        border-color: #dc3545 !important;
        background: #fff5f5;
    }
    .date-hint {
        font-size: 11px;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }

    /* Period pills */
    .period-pill {
        display: inline-block;
        background: #e0f2fe;
        color: #0284c7;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .toggle-icon { transition: transform 0.2s ease; }
    .team-row.expanded .toggle-icon { transform: rotate(90deg); }

    /* Mobile: a tabela de membros vira cards empilhados com rotulos */
    @media (max-width: 767.98px) {
        .expand-inner { padding: 10px 12px; }

        .member-table thead { display: none; }
        .member-table,
        .member-table tbody,
        .member-table tr,
        .member-table td { display: block; width: 100%; }

        .member-table tr.member-row {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 10px 12px;
            background: #fff;
        }
        .member-table tr.member-row-allocated { border-left: 4px solid #28a745; }
        .member-table tr.member-row-allocated td { border-left: none !important; }

        .member-table td {
            border: none !important;
            padding: 5px 0 !important;
        }
        .member-table td::before {
            content: attr(data-label);
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .member-table td[data-label=""]::before { display: none; }
        .member-table td .form-control { width: 100%; }
        .member-table .member-checkbox { transform: scale(1.35); margin-left: 3px; }
    }
</style>
@stop

@section('content')
@php
    // Build JS-safe data about existing allocations keyed by user_id
    $alocacoesJs = [];
    foreach ($alocacoes as $userId => $userAlocs) {
        foreach ($userAlocs as $aloc) {
            $cronId = $aloc->clockify_projeto_cronograma_id ?? 'all';
            $alocacoesJs[$userId][$cronId] = [
                'cronograma_id'=> $aloc->clockify_projeto_cronograma_id,
                'data_inicio'  => $aloc->data_inicio  ? $aloc->data_inicio->format('Y-m-d') : null,
                'data_fim'     => $aloc->data_fim     ? $aloc->data_fim->format('Y-m-d') : null,
                'horas_por_dia'=> $aloc->horas_por_dia ?? 8,
                'observacao'   => $aloc->observacao    ?? '',
            ];
        }
    }
@endphp

@if(!$projeto)
{{-- Project selector (general planning entry point) --}}
<div class="card planning-card border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <h3 class="card-title text-bold text-dark mb-0">
            <i class="fas fa-project-diagram text-primary mr-2"></i> Selecione o projeto
            <small class="text-muted font-weight-normal ml-2">— escolha um projeto para planejar a equipe</small>
        </h3>
    </div>
    <div class="card-body">
        @if($projetos->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <h5 class="font-weight-bold">Nenhum projeto ativo</h5>
                <p class="mb-0">Cadastre um projeto antes de planejar a equipe.</p>
            </div>
        @else
            <form method="GET" action="{{ route('operacao.projetos.planejar-equipe-geral') }}" class="d-flex flex-wrap align-items-end">
                <div class="form-group mb-2 mr-3" style="min-width: 300px; max-width: 100%;">
                    <label class="text-xs text-uppercase text-muted font-weight-bold">Projeto</label>
                    <select name="projeto_id" id="projeto-selector" class="form-control select2" required>
                        <option value="">Selecione um projeto...</option>
                        @foreach($projetos as $p)
                            <option value="{{ $p->id }}">{{ $p->nome }}{{ $p->cliente ? ' — ' . $p->cliente->nome : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary font-weight-bold shadow-sm mb-2">
                    <i class="fas fa-users-cog mr-1"></i> Planejar equipe
                </button>
            </form>
        @endif
    </div>
</div>
@else
{{-- Context Banner --}}
<div class="card planning-card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h5 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-project-diagram text-primary mr-2"></i>{{ $projeto->nome }}
                </h5>
                <span class="text-muted text-sm">Cliente: <strong>{{ $projeto->cliente->nome ?? '—' }}</strong></span>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                @forelse($cronogramas as $cron)
                    @if($cron->data_inicio && $cron->data_fim)
                        <span class="period-pill mr-2 mb-1">
                            <i class="far fa-calendar-alt mr-1"></i>
                            {{ $cron->nome }}: {{ $cron->data_inicio->format('d/m/Y') }} – {{ $cron->data_fim->format('d/m/Y') }}
                        </span>
                    @endif
                @empty
                    <span class="text-warning text-sm"><i class="fas fa-exclamation-triangle mr-1"></i>Nenhum cronograma com datas definidas</span>
                @endforelse
            </div>
            <a href="{{ route('operacao.projetos.show', $projeto->id) }}" class="btn btn-default btn-sm font-weight-bold text-secondary mt-2 mt-md-0">
                <i class="fas fa-arrow-left mr-1"></i> Voltar ao Projeto
            </a>
        </div>
    </div>
</div>

@if(!$dataInicioGlobal)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Atenção:</strong> Nenhum cronograma possui datas definidas. Defina as datas de início e fim nas etapas do projeto antes de planejar a equipe.
    </div>
@endif

{{-- Teams Table --}}
<div class="card planning-card border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <h3 class="card-title text-bold text-dark mb-0">
            <i class="fas fa-users text-primary mr-2"></i> Equipes disponíveis
            <small class="text-muted font-weight-normal ml-2">— clique em uma equipe para alocar membros</small>
        </h3>
    </div>
    <div class="card-body p-0">
        <form id="form-alocacao" action="{{ route('operacao.projetos.planejar-equipe.store', $projeto->id) }}" method="POST">
            @csrf
            <input type="hidden" id="global-min-date" value="{{ $dataInicioGlobal }}">
            <input type="hidden" id="global-max-date" value="{{ $dataFimGlobal }}">

            <div class="table-responsive">
            <table class="table table-hover mb-0" id="teams-table">
                <thead class="thead-light">
                    <tr class="text-xs text-uppercase">
                        <th class="pl-4" style="width:40px;"></th>
                        <th>Equipe / Time</th>
                        <th class="text-center">Membros</th>
                        <th class="text-center">Alocados neste Projeto</th>
                        <th class="text-center d-none d-md-table-cell">Período coberto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($times as $time)
                        @php
                            $membrosAlocados = $time->users->filter(fn($u) => isset($alocacoesJs[$u->id]))->count();
                        @endphp
                        {{-- Team header row --}}
                        <tr class="team-row {{ $membrosAlocados > 0 ? 'expanded' : '' }}"
                            data-team-id="{{ $time->id }}"
                            onclick="toggleTeam({{ $time->id }})">
                            <td class="pl-4 align-middle text-center">
                                <i class="fas fa-chevron-right toggle-icon text-primary"></i>
                            </td>
                            <td class="align-middle font-weight-bold text-dark">
                                <i class="fas fa-users text-secondary mr-2"></i>{{ $time->nome }}
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-secondary px-2">{{ $time->users->count() }}</span>
                            </td>
                            <td class="align-middle text-center">
                                @if($membrosAlocados > 0)
                                    <span class="badge badge-success px-2">{{ $membrosAlocados }} alocado(s)</span>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>
                            <td class="align-middle text-center d-none d-md-table-cell">
                                @if($dataInicioGlobal && $dataFimGlobal)
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($dataInicioGlobal)->format('d/m/Y') }}
                                        –
                                        {{ \Carbon\Carbon::parse($dataFimGlobal)->format('d/m/Y') }}
                                    </small>
                                @else
                                    <small class="text-warning">Sem datas</small>
                                @endif
                            </td>
                        </tr>

                        {{-- Expansion row with member allocation --}}
                        <tr class="expand-row {{ $membrosAlocados > 0 ? 'open' : '' }}" id="expand-{{ $time->id }}">
                            <td colspan="5" class="p-0">
                                <div class="expand-inner">
                                    @if($time->users->count() === 0)
                                        <p class="text-muted text-sm mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>Esta equipe não possui membros cadastrados.
                                        </p>
                                    @else
                                        <table class="table table-sm member-table mb-0 bg-white rounded" style="border-radius:8px; overflow:hidden;">
                                            <thead>
                                                <tr>
                                                    <th style="width:50px;" class="text-center pl-3">Alocar</th>
                                                    <th>Membro</th>
                                                    <th style="width:180px;">Etapa do Projeto</th>
                                                    <th>Função / Tarefa</th>
                                                    <th style="width:100px;" class="text-center">Horas/Dia</th>
                                                    <th style="width:145px;">Data Início</th>
                                                    <th style="width:145px;">Data Fim</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($time->users as $idx => $user)
                                                    @php
                                                        $userAlocs = $alocacoesJs[$user->id] ?? null;
                                                        // Tenta pegar a alocação específica desta etapa ou a primeira disponível
                                                        $aloc = null;
                                                        if ($userAlocs) {
                                                            $aloc = reset($userAlocs); // Pega a primeira alocação encontrada
                                                        }
                                                        $isAllocated = $aloc !== null;
                                                        $key = $time->id . '_' . $idx;
                                                    @endphp
                                                    <tr class="member-row {{ $isAllocated ? 'member-row-allocated' : '' }}"
                                                        id="member-row-{{ $key }}">
                                                        <td class="text-center align-middle pl-3" data-label="Alocar">
                                                            <input type="hidden"
                                                                   name="alocacoes[{{ $key }}][checked]"
                                                                   id="hidden-check-{{ $key }}"
                                                                   value="{{ $isAllocated ? '1' : '0' }}">
                                                            <input type="checkbox"
                                                                   class="member-checkbox"
                                                                   data-key="{{ $key }}"
                                                                   {{ $isAllocated ? 'checked' : '' }}
                                                                   onchange="toggleMember('{{ $key }}', this)">
                                                        </td>
                                                        <td class="align-middle font-weight-bold text-dark" data-label="Membro">
                                                            <input type="hidden" name="alocacoes[{{ $key }}][user_id]" value="{{ $user->id }}">
                                                            {{ $user->name ?? $user->nome }}
                                                        </td>
                                                        {{-- Etapa --}}
                                                        <td class="align-middle" data-label="Etapa do Projeto">
                                                            <select name="alocacoes[{{ $key }}][clockify_projeto_cronograma_id]"
                                                                    id="etapa-{{ $key }}"
                                                                    class="form-control form-control-sm etapa-select"
                                                                    data-key="{{ $key }}"
                                                                    onchange="onEtapaChange('{{ $key }}')">
                                                                <option value="">— Todas as etapas —</option>
                                                                @foreach($cronogramas as $cron)
                                                                    @if($cron->data_inicio && $cron->data_fim)
                                                                        <option value="{{ $cron->id }}"
                                                                            data-inicio="{{ $cron->data_inicio->format('Y-m-d') }}"
                                                                            data-fim="{{ $cron->data_fim->format('Y-m-d') }}"
                                                                            @if($aloc && ($aloc['cronograma_id'] ?? null) == $cron->id) selected @endif>
                                                                            {{ $cron->nome }}
                                                                            ({{ $cron->data_inicio->format('d/m') }}–{{ $cron->data_fim->format('d/m/Y') }})
                                                                        </option>
                                                                    @else
                                                                        <option value="{{ $cron->id }}"
                                                                            @if($aloc && ($aloc['cronograma_id'] ?? null) == $cron->id) selected @endif>
                                                                            {{ $cron->nome }} (sem datas)
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="align-middle" data-label="Função / Tarefa">
                                                            <input type="text"
                                                                   name="alocacoes[{{ $key }}][observacao]"
                                                                   class="form-control form-control-sm"
                                                                   placeholder="Ex: Dev Front-end, QA..."
                                                                   value="{{ $aloc['observacao'] ?? '' }}">
                                                        </td>
                                                        <td class="align-middle" data-label="Horas/Dia">
                                                            <input type="number"
                                                                   name="alocacoes[{{ $key }}][horas_por_dia]"
                                                                   class="form-control form-control-sm text-center font-weight-bold"
                                                                   step="0.5" min="0.5" max="24"
                                                                   value="{{ $aloc['horas_por_dia'] ?? 8 }}">
                                                        </td>
                                                        <td class="align-middle" data-label="Data Início">
                                                            <input type="date"
                                                                   name="alocacoes[{{ $key }}][data_inicio]"
                                                                   id="start-{{ $key }}"
                                                                   class="form-control form-control-sm date-constrained"
                                                                   min="{{ $dataInicioGlobal }}"
                                                                   max="{{ $dataFimGlobal }}"
                                                                   {{ $isAllocated ? 'required' : '' }}
                                                                   value="{{ ($aloc && $aloc['data_inicio']) ? $aloc['data_inicio'] : '' }}"
                                                                   onchange="validateDateRange('{{ $key }}')">
                                                            @if($dataInicioGlobal)
                                                                <span class="date-hint">mín: {{ \Carbon\Carbon::parse($dataInicioGlobal)->format('d/m/Y') }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="align-middle" data-label="Data Fim">
                                                            <input type="date"
                                                                   name="alocacoes[{{ $key }}][data_fim]"
                                                                   id="end-{{ $key }}"
                                                                   class="form-control form-control-sm date-constrained"
                                                                   min="{{ $dataInicioGlobal }}"
                                                                   max="{{ $dataFimGlobal }}"
                                                                   {{ $isAllocated ? 'required' : '' }}
                                                                   value="{{ ($aloc && $aloc['data_fim']) ? $aloc['data_fim'] : '' }}"
                                                                   onchange="validateDateRange('{{ $key }}')">
                                                            @if($dataFimGlobal)
                                                                <span class="date-hint">máx: {{ \Carbon\Carbon::parse($dataFimGlobal)->format('d/m/Y') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                <p class="mb-0">Nenhuma equipe cadastrada ainda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-between align-items-center p-3 border-top bg-white" style="border-radius: 0 0 12px 12px;">
                <a href="{{ route('operacao.projetos.show', $projeto->id) }}" class="btn btn-default font-weight-bold text-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                @can('operacao.projetos.planejar-equipe.store')
                    <button type="submit" class="btn btn-success font-weight-bold shadow-sm px-4">
                        <i class="fas fa-save mr-1"></i> Salvar Alocações
                    </button>
                @endcan
            </div>
        </form>
    </div>
</div>
@endif
@stop

@section('js')
@if(!$projeto)
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(function () {
        $('#projeto-selector').select2({
            theme: 'bootstrap4',
            placeholder: 'Selecione um projeto...',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
@else
<script>
    const MIN_DATE = document.getElementById('global-min-date').value;
    const MAX_DATE = document.getElementById('global-max-date').value;

    // Toggle team expansion
    function toggleTeam(teamId) {
        const row     = document.querySelector(`[data-team-id="${teamId}"]`);
        const expRow  = document.getElementById(`expand-${teamId}`);
        const isOpen  = expRow.classList.contains('open');
        expRow.classList.toggle('open', !isOpen);
        row.classList.toggle('expanded', !isOpen);
    }

    // When user selects a stage: auto-fill dates with stage dates and update min/max constraints
    function onEtapaChange(key) {
        const select   = document.getElementById(`etapa-${key}`);
        const startEl  = document.getElementById(`start-${key}`);
        const endEl    = document.getElementById(`end-${key}`);
        const selected = select.options[select.selectedIndex];

        const inicio = selected.getAttribute('data-inicio') || MIN_DATE;
        const fim    = selected.getAttribute('data-fim')    || MAX_DATE;

        // Set min/max constraints to the chosen stage (or global if none)
        if (inicio) {
            startEl.min = inicio;
            endEl.min   = inicio;
        }
        if (fim) {
            startEl.max = fim;
            endEl.max   = fim;
        }

        // Auto-fill dates if fields are empty or if user explicitly picked a stage
        if (select.value) {
            if (inicio) startEl.value = inicio;
            if (fim)    endEl.value   = fim;
        } else {
            // Reset to global project limits
            startEl.min = MIN_DATE;
            startEl.max = MAX_DATE;
            endEl.min   = MIN_DATE;
            endEl.max   = MAX_DATE;
        }

        validateDateRange(key);
    }

    // Toggle member allocation
    function toggleMember(key, checkbox) {
        const hidden  = document.getElementById(`hidden-check-${key}`);
        const startEl = document.getElementById(`start-${key}`);
        const endEl   = document.getElementById(`end-${key}`);
        const row     = document.getElementById(`member-row-${key}`);

        hidden.value       = checkbox.checked ? '1' : '0';
        startEl.required   = checkbox.checked;
        endEl.required     = checkbox.checked;
        row.classList.toggle('member-row-allocated', checkbox.checked);
    }

    // Validate that picked dates are within selected stage (or project) range
    function validateDateRange(key) {
        const startEl = document.getElementById(`start-${key}`);
        const endEl   = document.getElementById(`end-${key}`);

        [startEl, endEl].forEach(el => {
            if (!el.value) { el.setCustomValidity(''); el.classList.remove('out-of-range'); return; }
            const d   = el.value;
            const min = el.min || MIN_DATE;
            const max = el.max || MAX_DATE;
            const out = (min && d < min) || (max && d > max);
            el.classList.toggle('out-of-range', out);
            el.setCustomValidity(out
                ? `Data fora do período permitido (${min ? formatDate(min) : '?'} – ${max ? formatDate(max) : '?'})`
                : '');
        });

        // End must be >= Start
        if (startEl.value && endEl.value && endEl.value < startEl.value) {
            endEl.setCustomValidity('Data de fim deve ser igual ou posterior ao início.');
            endEl.classList.add('out-of-range');
        }
    }

    function formatDate(ymd) {
        const [y, m, d] = ymd.split('-');
        return `${d}/${m}/${y}`;
    }

    // Block form submit if any date is out of range
    document.getElementById('form-alocacao').addEventListener('submit', function(e) {
        const invalids = this.querySelectorAll('.out-of-range');
        if (invalids.length > 0) {
            e.preventDefault();
            alert('Existem datas fora do período de planejamento. Corrija antes de salvar.');
            invalids[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Run initial validation on pre-filled dates
    document.querySelectorAll('.date-constrained').forEach(el => {
        if (el.value) {
            const key = el.id.replace(/^(start|end)-/, '');
            validateDateRange(key);
        }
    });
</script>
@endif
@stop
