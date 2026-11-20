@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$turma->nome" subtitle="Disciplinas ofertadas e professores" />
@stop

@push('css')
@include('partials.ux-index-styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    @include('partials.home-app-styles')
<style>
    .te-sec-btn {
        margin-left: auto;
        border: 0;
        border-radius: 999px;
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        padding: .3rem .75rem;
        line-height: 1.4;
        cursor: pointer;
    }
    .te-chip {
        display: inline-flex;
        align-items: center;
        font-size: .68rem;
        font-weight: 700;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 2px 8px;
        white-space: nowrap;
    }
    .te-chip--blue {
        color: #1d4ed8;
        background: rgba(59,130,246,.12);
        border-color: rgba(59,130,246,.25);
        margin-left: 4px;
    }

    /* Cards compactos do diario: ate 4 por linha. */
    .reg-cards { grid-template-columns: repeat(auto-fill, minmax(215px, 1fr)); gap: 10px; padding: 10px; }
    .reg-cards .ux-card { border-radius: 12px; }
    .reg-cards .ux-card__head { padding: 8px 10px 7px; gap: 7px; }
    .reg-cards .ux-card__avatar { width: 30px; height: 30px; border-radius: 8px; font-size: .72rem !important; flex-shrink: 0; }
    .reg-cards .ux-card__id { font-size: .62rem; }
    .reg-cards .ux-card__title { font-size: .76rem; line-height: 1.25; }
    .reg-cards .ux-card__sub { font-size: .66rem; }
    .reg-cards .ux-card__badges { gap: 4px; }
    .reg-cards .ux-card__badge { font-size: .56rem; padding: 2px 6px; }
    .reg-cards .ux-card__meta { padding: 6px 10px 4px; gap: 3px 8px; }
    .reg-cards .ux-card__meta-label { font-size: .58rem; }
    .reg-cards .ux-card__meta-value { font-size: .68rem; white-space: normal; }
    .reg-cards .ux-card__foot { padding: 6px 10px 8px; }
    /* Mobile: acoes principais ocupam a linha inteira */
    @media (max-width: 576px) {
        .turma-acoes .btn,
        .turma-acoes .dropdown { flex: 1 1 100%; }
        .turma-acoes .dropdown .btn { width: 100%; }
    }

    /* Calendario do diario/avaliacoes */
    .rcal-head { display:flex; align-items:center; gap:.5rem; padding:.65rem .9rem; border-bottom:1px solid #f1f5f9; }
    .rcal-head h6 { flex:1; text-align:center; margin:0; font-weight:700; text-transform:capitalize; }
    .rcal-head .btn { border-radius:999px; }
    .rcal-grid { display:grid; grid-template-columns:repeat(7,1fr); border-top:1px solid #eef2f7; }
    .rcal-dow { padding:.4rem .2rem; font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; text-align:center; background:#f8fafc; border-bottom:1px solid #eef2f7; }
    .rcal-dia { min-height:88px; padding:4px 4px 6px; border-right:1px solid #f1f5f9; border-bottom:1px solid #f1f5f9; background:#fff; overflow:hidden; }
    .rcal-dia:nth-child(7n) { border-right:0; }
    .rcal-out { background:#fafbfc; }
    .rcal-out .rcal-num { color:#cbd5e1; }
    .rcal-hoje .rcal-num { background:#4f46e5; color:#fff; }
    .rcal-num { width:20px; height:20px; border-radius:50%; font-size:.66rem; font-weight:700; color:#475569; display:flex; align-items:center; justify-content:center; margin-bottom:3px; }
    .rc-item { display:block; width:100%; text-align:left; border:0; border-radius:5px; padding:2px 5px; margin-bottom:2px; font-size:.62rem; font-weight:600; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer; }
    .rc-item--diario { background:#e0f2fe; color:#0369a1; }
    .rc-item--diario:hover { background:#bae6fd; color:#075985; }
    .rc-item--avaliacao { background:#fef3c7; color:#b45309; }
    .rc-item--avaliacao:hover { background:#fde68a; color:#92400e; }
    .rc-more { font-size:.6rem; color:#94a3b8; padding-left:4px; }
    .rcal-vazio { padding:2rem; text-align:center; color:#94a3b8; font-size:.85rem; }
    @media (max-width: 576px) {
        .rcal-dia { min-height:52px; }
        .rc-item { font-size:.55rem; }
    }
    /* Detalhes no drawer */
    .rd-date { display:flex; align-items:center; gap:.5rem; color:#475569; font-size:.9rem; margin:.35rem 0 1rem; }
    .rd-date .sem { color:#94a3b8; text-transform:capitalize; }
    .rd-row { display:flex; gap:.6rem; padding:.5rem 0; border-bottom:1px solid #f1f5f9; font-size:.85rem; }
    .rd-row:last-child { border-bottom:0; }
    .rd-label { width:96px; flex-shrink:0; color:#94a3b8; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding-top:2px; }
    .rd-val { color:#1e293b; }
    #regDrawer .side-drawer-footer { display:flex; gap:.5rem; }
    #regDrawer .side-drawer-footer .btn { flex:1; font-weight:600; }
</style>
@endpush

@section('content')
@php
    $alunosAtivos = $turma->matriculas->where('status', 'ativa')->count();
    $isProfessor = auth()->user()->isProfessor();
    // Aba de equipe/disciplinas: visivel so para secretaria/direcao
    // (quem gerencia ofertas e atribuicoes). Professor nao ve.
    $podeVerEquipe = ! $isProfessor && (
        auth()->user()->hasPermissao('turma.disciplina.store')
        || auth()->user()->hasPermissao('turma.atribuicao.store')
    );
    $totalCarga = $isProfessor
        ? (int) $minhasAulas->sum()
        : $disciplinasVisiveis->sum(fn ($d) => (int) $d->pivot->carga_horaria_semanal);
    $professoresAtivos = $turma->atribuicoes->filter->esta_ativa->pluck('user_id')->unique()->count();
    $capacidade = (int) ($turma->capacidade_maxima ?? 0);
    $ocupacao = $capacidade > 0 ? min(100, (int) round($alunosAtivos * 100 / $capacidade)) : null;
@endphp

<div class="mb-3 d-flex flex-wrap align-items-center turma-acoes" style="gap:.5rem;">
    @can('diario.frequencia')
        <a href="{{ route('turma.chamada.create', $turma->id) }}" class="btn btn-success"><i class="fa fa-clipboard-check"></i> {{ $diarioHoje ? 'Chamada de hoje' : 'Fazer chamada' }}</a>
    @endcan
    @can('avaliacao.create')
        <a href="{{ route('turma.avaliacao.create', $turma->id) }}" class="btn btn-outline-danger btn-sm"><i class="fa fa-star"></i> Nova avaliação</a>
    @endcan
    <div class="dropdown">
        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-ellipsis-h"></i> Mais ações
        </button>
        <div class="dropdown-menu">
            @can('turma.grade')
                <a href="{{ route('turma.grade', $turma->id) }}" class="dropdown-item"><i class="fa fa-calendar-week mr-1"></i> Grade horária</a>
            @endcan
            @can('calendario.index')
                <a href="{{ route('turma.calendario', $turma->id) }}" class="dropdown-item"><i class="fa fa-calendar-check mr-1"></i> Calendário letivo</a>
            @endcan
            @can('turma.grade.pdf')
                <a href="{{ route('turma.grade.pdf', $turma->id) }}" class="dropdown-item"><i class="fa fa-file-pdf mr-1"></i> Quadro em PDF</a>
            @endcan
            @can('matricula.create')
                <a href="{{ route('turma.matricula.create', $turma->id) }}" class="dropdown-item"><i class="fa fa-user-plus mr-1"></i> Nova matrícula</a>
            @endcan
            @can('matricula.rematricula')
                <a href="{{ route('matricula.rematricula', ['escola_origem' => $turma->escola_id, 'ano_origem' => $turma->ano_letivo_id, 'turma_origem' => $turma->id, 'escola_destino' => $turma->escola_id]) }}" class="dropdown-item"><i class="fa fa-retweet mr-1"></i> Rematricular turma</a>
            @endcan
            @can('justificativa.index')
                <a href="{{ route('turma.justificativas', $turma->id) }}" class="dropdown-item"><i class="fa fa-file-medical mr-1"></i> Atestados e abonos</a>
            @endcan
            @can('turma.edit')
                <div class="dropdown-divider"></div>
                <a href="{{ route('turma.edit', $turma->id) }}" class="dropdown-item"><i class="fa fa-edit mr-1"></i> Editar turma</a>
            @endcan
        </div>
    </div>
</div>

<ul class="nav nav-tabs nav-tabs-app" id="turmaTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-visao-link" data-toggle="tab" href="#tab-visao" role="tab"><i class="fas fa-info-circle mr-1"></i> Visão geral</a>
    </li>
    @if($podeVerEquipe)
    <li class="nav-item">
        <a class="nav-link" id="tab-equipe-link" data-toggle="tab" href="#tab-equipe" role="tab"><i class="fas fa-chalkboard-teacher mr-1"></i> Professores e disciplinas <span class="badge badge-light border ml-1">{{ $disciplinasVisiveis->count() }}</span></a>
    </li>
    @endif
    <li class="nav-item">
        <a class="nav-link" id="tab-alunos-link" data-toggle="tab" href="#tab-alunos" role="tab"><i class="fas fa-user-graduate mr-1"></i> Alunos <span class="badge badge-light border ml-1">{{ $alunosAtivos }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-registros-link" data-toggle="tab" href="#tab-registros" role="tab"><i class="fas fa-book-open mr-1"></i> Diário e avaliações <span class="badge badge-light border ml-1">{{ $registros->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-tarefas-link" data-toggle="tab" href="#tab-tarefas" role="tab"><i class="fas fa-tasks mr-1"></i> Atividades <span class="badge badge-{{ $tarefasCorrecao->sum('pendentes_count') > 0 ? 'danger' : 'light border' }} ml-1">{{ $tarefasCorrecao->sum('pendentes_count') > 0 ? $tarefasCorrecao->sum('pendentes_count').' pendentes' : $tarefasCorrecao->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-materiais-link" data-toggle="tab" href="#tab-materiais" role="tab"><i class="fas fa-folder-open mr-1"></i> Materiais <span class="badge badge-light border ml-1">{{ $materiais->count() }}</span></a>
    </li>
</ul>

<div class="tab-content pt-3">
<div class="tab-pane fade show active" id="tab-visao" role="tabpanel">

    <div class="hs-widgets mb-3" style="grid-template-columns:repeat(auto-fit,minmax(110px,1fr));">
        <div class="hs-widget">
            <span class="hs-widget-icone"><i class="fas fa-user-graduate"></i></span>
            <b>{{ $alunosAtivos }}</b>
            <small>Alunos ativos</small>
        </div>
        <div class="hs-widget">
            <span class="hs-widget-icone"><i class="fas fa-book"></i></span>
            <b>{{ $disciplinasVisiveis->count() }}</b>
            <small>{{ $isProfessor ? 'Minhas disciplinas' : 'Disciplinas' }}</small>
        </div>
        @if($podeVerEquipe)
        <div class="hs-widget">
            <span class="hs-widget-icone"><i class="fas fa-chalkboard-teacher"></i></span>
            <b>{{ $professoresAtivos }}</b>
            <small>Professores</small>
        </div>
        @endif
        <div class="hs-widget">
            <span class="hs-widget-icone"><i class="fas fa-clock"></i></span>
            <b>{{ $totalCarga }}{{ $isProfessor ? '' : 'h' }}</b>
            <small>{{ $isProfessor ? 'Minhas aulas/sem.' : 'Carga semanal' }}</small>
        </div>
        <div class="hs-widget">
            <span class="hs-widget-icone"><i class="fas fa-users"></i></span>
            <b>{{ $ocupacao !== null ? $ocupacao.'%' : '—' }}</b>
            <small>Ocupação</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="hs-sec"><i class="fa fa-info-circle"></i> Dados da turma</div>
            <div class="hs-lista mb-3">
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:#007bff;"><i class="fas fa-school"></i></span>
                    <div class="hs-item-info"><small>Escola</small><b>{{ $turma->escola->nome ?? '—' }}</b></div>
                </div>
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:#8b5cf6;"><i class="fas fa-calendar-alt"></i></span>
                    <div class="hs-item-info"><small>Ano letivo</small><b>{{ $turma->anoLetivo->ano ?? '—' }}{{ ($turma->anoLetivo->status ?? 'letivo') === 'encerrado' ? ' (encerrado)' : '' }}</b></div>
                </div>
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:#06b6d4;"><i class="fas fa-graduation-cap"></i></span>
                    <div class="hs-item-info"><small>Etapa</small><b>{{ $turma->etapa->nome ?? '—' }}</b></div>
                </div>
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:#f59e0b;"><i class="fas fa-sun"></i></span>
                    <div class="hs-item-info"><small>Turno</small><b>{{ $turnos[$turma->turno ?? ''] ?? '—' }}</b></div>
                </div>
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:#10b981;"><i class="fas fa-door-open"></i></span>
                    <div class="hs-item-info"><small>Sala · Capacidade</small><b>{{ $turma->sala ?? '—' }} · {{ $turma->capacidade_maxima ?? '—' }} vagas</b></div>
                </div>
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:{{ $turma->esta_ativa ? '#22c55e' : '#94a3b8' }};"><i class="fas fa-toggle-on"></i></span>
                    <div class="hs-item-info"><small>Status</small><b>{{ $turma->esta_ativa ? 'Ativa' : 'Inativa' }}</b></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            @if($podeVerEquipe)
            <div class="hs-sec"><i class="fa fa-chart-pie"></i> Ocupação</div>
            <div class="hs-lista mb-3">
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:{{ $ocupacao !== null && $ocupacao >= 90 ? '#ef4444' : '#007bff' }};"><i class="fas fa-users"></i></span>
                    <div class="hs-item-info">
                        <small>Vagas preenchidas</small>
                        <b>{{ $alunosAtivos }}{{ $capacidade ? ' de '.$capacidade : '' }} alunos</b>
                        @if($ocupacao !== null)
                        <div class="progress mt-1" style="height:6px; min-width:140px;">
                            <div class="progress-bar {{ $ocupacao >= 90 ? 'bg-danger' : 'bg-primary' }}" style="width:{{ $ocupacao }}%"></div>
                        </div>
                        @endif
                    </div>
                    @if($ocupacao !== null)<span class="hs-item-qtd" style="--app:{{ $ocupacao >= 90 ? '#ef4444' : '#007bff' }};">{{ $ocupacao }}%</span>@endif
                </div>
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:#8b5cf6;"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div class="hs-item-info"><small>Corpo docente</small><b>{{ $professoresAtivos }} professor(es) · {{ $disciplinasVisiveis->count() }} disciplina(s)</b></div>
                    <a href="#tab-equipe" data-toggle="tab" class="hs-item-seta" onclick="document.getElementById('tab-equipe-link').click();return false;"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            @else
                @include('turma.partials.disciplinas-card')
            @endif
        </div>
    </div>
</div>

@if($podeVerEquipe)
<div class="tab-pane fade" id="tab-equipe" role="tabpanel">
    <div class="row">
        <div class="col-lg-7">
            @include('turma.partials.disciplinas-card')
        </div>
        <div class="col-lg-5">
            @can('turma.atribuicao.store')
                @include('turma.partials.atribuicoes-card')
            @endcan
        </div>
    </div>
</div>
@endif

<div class="tab-pane fade" id="tab-registros" role="tabpanel">
    @php
        $chipsRegistros = [];
        if ($filtrosRegistros['disciplina_id'] ?? null) {
            $chipsRegistros[] = [
                'label' => 'Disciplina',
                'valor' => $disciplinasVisiveis->firstWhere('id', (int) $filtrosRegistros['disciplina_id'])->nome ?? $filtrosRegistros['disciplina_id'],
                'param' => 'f_disciplina',
            ];
        }
        if ($filtrosRegistros['data_inicio'] ?? null) {
            $chipsRegistros[] = ['label' => 'De', 'valor' => \Carbon\Carbon::parse($filtrosRegistros['data_inicio'])->format('d/m/Y'), 'param' => 'f_ini'];
        }
        if ($filtrosRegistros['data_fim'] ?? null) {
            $chipsRegistros[] = ['label' => 'Até', 'valor' => \Carbon\Carbon::parse($filtrosRegistros['data_fim'])->format('d/m/Y'), 'param' => 'f_fim'];
        }
        if ($filtrosRegistros['tipo'] ?? null) {
            $chipsRegistros[] = [
                'label' => 'Tipo',
                'valor' => \App\Models\Avaliacao::TIPOS[$filtrosRegistros['tipo']] ?? $filtrosRegistros['tipo'],
                'param' => 'f_tipo',
            ];
        }
        if ($filtrosRegistros['periodo_letivo_id'] ?? null) {
            $chipsRegistros[] = [
                'label' => 'Período',
                'valor' => $periodosLetivos->firstWhere('id', (int) $filtrosRegistros['periodo_letivo_id'])->nome ?? $filtrosRegistros['periodo_letivo_id'],
                'param' => 'f_periodo',
            ];
        }
    @endphp
    <div class="card mb-2 filter-card">
        <div class="card-body p-2">
            <h6 class="filter-heading text-secondary text-xs mb-1"><i class="fa fa-filter mr-1"></i> Filtros</h6>
            <hr class="filter-heading m-0 mb-2">
            <form method="GET" action="{{ route('turma.show', $turma->id) }}#tab-registros">
                <div class="row align-items-end">
                    <div class="col-auto flex-grow-1 filter-field">
                        <label class="text-xs mb-0">Disciplina</label>
                        <select name="f_disciplina" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach($disciplinasVisiveis as $disc)
                                <option value="{{ $disc->id }}" {{ (string) ($filtrosRegistros['disciplina_id'] ?? '') === (string) $disc->id ? 'selected' : '' }}>{{ $disc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto flex-grow-1 filter-field">
                        <label class="text-xs mb-0">De</label>
                        <input type="date" name="f_ini" class="form-control form-control-sm" value="{{ $filtrosRegistros['data_inicio'] ?? '' }}">
                    </div>
                    <div class="col-auto flex-grow-1 filter-field">
                        <label class="text-xs mb-0">Até</label>
                        <input type="date" name="f_fim" class="form-control form-control-sm" value="{{ $filtrosRegistros['data_fim'] ?? '' }}">
                    </div>
                    <div class="col-auto flex-grow-1 filter-field">
                        <label class="text-xs mb-0">Tipo <small class="text-muted">(só avaliações)</small></label>
                        <select name="f_tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach(\App\Models\Avaliacao::TIPOS as $tk => $tl)
                                <option value="{{ $tk }}" {{ ($filtrosRegistros['tipo'] ?? '') === $tk ? 'selected' : '' }}>{{ $tl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto flex-grow-1 filter-field">
                        <label class="text-xs mb-0">Período <small class="text-muted">(só avaliações)</small></label>
                        <select name="f_periodo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($periodosLetivos as $p)
                                <option value="{{ $p->id }}" {{ (string) ($filtrosRegistros['periodo_letivo_id'] ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-end filter-actions" style="gap:6px;">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search mr-1"></i> Filtrar</button>
                    </div>
                </div>
            </form>
            @if(count($chipsRegistros) > 0)
                <div class="mt-2 d-flex flex-wrap align-items-center" style="gap:6px;">
                    <span class="text-xs text-secondary text-bold">Aplicados:</span>
                    @foreach($chipsRegistros as $chip)
                        <a href="{{ route('turma.show', ['turma' => $turma->id] + request()->except($chip['param'])) }}#tab-registros"
                           class="badge badge-secondary font-weight-normal" title="Remover filtro">
                            <i class="fa fa-times mr-1"></i>{{ $chip['label'] }}: <b>{{ $chip['valor'] }}</b>
                        </a>
                    @endforeach
                    <a href="{{ route('turma.show', $turma->id) }}#tab-registros" class="badge badge-danger font-weight-normal" title="Limpar todos">
                        <i class="fa fa-times mr-1"></i> Limpar tudo
                    </a>
                </div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header font-weight-bold d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
            <span>Diário e avaliações ({{ $registros->count() }})</span>
            <span class="d-flex align-items-center" style="gap:.5rem;">
                <div class="btn-group btn-group-sm reg-modo" role="group" title="Alternar visualização">
                    <button type="button" class="btn btn-outline-primary active" data-modo="lista"><i class="fa fa-list"></i> Lista</button>
                    <button type="button" class="btn btn-outline-primary" data-modo="cal"><i class="fa fa-calendar-alt"></i> Calendário</button>
                </div>
                @include('partials.ux-view-toggle', ['viewKey' => 'turma_registros_view'])
            </span>
        </div>
        <div class="card-body p-0 ux-index-body" data-view="cards">
            <div class="ux-cards reg-cards">
                @forelse($registros as $r)
                    <article class="ux-card">
                        <div class="ux-card__accent"></div>
                        <div class="ux-card__head">
                            <div class="ux-card__avatar" style="background:{{ $r['tipo'] === 'diario' ? '#e0f2fe;color:#0369a1' : '#fef3c7;color:#b45309' }};font-size:.85rem;">
                                <i class="fa {{ $r['tipo'] === 'diario' ? 'fa-book' : 'fa-star' }}"></i>
                            </div>
                            <div class="ux-card__head-info">
                                <div class="ux-card__id">{{ $r['data']?->format('d/m/Y') ?? '—' }}</div>
                                <div class="ux-card__title">{{ $r['tipo'] === 'diario' ? \Illuminate\Support\Str::limit($r['item'], 60) : $r['item'] }}</div>
                                <div class="ux-card__sub">{{ $r['disciplina'] ?? '—' }}</div>
                            </div>
                            <div class="ux-card__badges">
                                @if($r['tipo'] === 'diario')
                                    <span class="ux-card__badge ux-card__badge--blue">Diário</span>
                                @else
                                    <span class="ux-card__badge ux-card__badge--amber">Avaliação</span>
                                @endif
                            </div>
                        </div>
                        @if($r['detalhe'] ?? null)
                            <div class="ux-card__meta">
                                <div class="ux-card__meta-row">
                                    <span class="ux-card__meta-label">Detalhe</span>
                                    <span class="ux-card__meta-value">{{ $r['detalhe'] }}</span>
                                </div>
                            </div>
                        @endif
                        <div class="ux-card__foot">
                            @if($r['tipo'] === 'diario')
                                @php $d = $r['model']; @endphp
                                @can('diario.frequencia')
                                    <a href="{{ route('diario.chamada', ['turma_id' => $d->turma_id, 'disciplina_id' => $d->disciplina_id, 'data' => $d->data?->format('Y-m-d')]) }}" class="btn btn-xs btn-success" title="Chamada"><i class="fa fa-clipboard-check"></i></a>
                                @endcan
                                @can('diario.edit')
                                    <a href="{{ route('diario.edit', $d->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                            @else
                                @php $a = $r['model']; @endphp
                                @can('avaliacao.notas')
                                    <a href="{{ route('avaliacao.notas', $a->id) }}" class="btn btn-xs btn-success" title="Lançar notas"><i class="fa fa-pencil-alt"></i></a>
                                @endcan
                                @can('avaliacao.edit')
                                    <a href="{{ route('avaliacao.edit', $a->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                        <i class="fas fa-book-open fa-2x mb-2 d-block"></i>
                        Nenhum registro nesta turma.
                    </div>
                @endforelse
            </div>
            <div class="ux-table table-responsive"><table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:115px">Registro</th>
                        <th style="width:95px">Data</th>
                        <th>Item</th>
                        <th>Disciplina</th>
                        <th>Detalhe</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td>
                                @if($r['tipo'] === 'diario')
                                    <span class="badge badge-info"><i class="fa fa-book"></i> Diário</span>
                                @else
                                    <span class="badge badge-warning"><i class="fa fa-star"></i> Avaliação</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $r['data']?->format('d/m/Y') ?? '—' }}</td>
                            <td class="{{ $r['tipo'] === 'avaliacao' ? 'font-weight-bold' : '' }}">
                                {{ $r['tipo'] === 'diario' ? \Illuminate\Support\Str::limit($r['item'], 60) : $r['item'] }}
                            </td>
                            <td>{{ $r['disciplina'] ?? '—' }}</td>
                            <td class="text-muted small">{{ $r['detalhe'] ?? '—' }}</td>
                            <td class="text-right text-nowrap">
                                @if($r['tipo'] === 'diario')
                                    @php $d = $r['model']; @endphp
                                    @can('diario.frequencia')
                                        <a href="{{ route('diario.chamada', ['turma_id' => $d->turma_id, 'disciplina_id' => $d->disciplina_id, 'data' => $d->data?->format('Y-m-d')]) }}" class="btn btn-xs btn-success" title="Chamada"><i class="fa fa-clipboard-check"></i></a>
                                    @endcan
                                    @can('diario.edit')
                                        <a href="{{ route('diario.edit', $d->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                @else
                                    @php $a = $r['model']; @endphp
                                    @can('avaliacao.notas')
                                        <a href="{{ route('avaliacao.notas', $a->id) }}" class="btn btn-xs btn-success" title="Lançar notas"><i class="fa fa-pencil-alt"></i></a>
                                    @endcan
                                    @can('avaliacao.edit')
                                        <a href="{{ route('avaliacao.edit', $a->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Nenhum registro nesta turma.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
        <div id="registros-cal" style="display:none;">
            <div class="rcal-head">
                <a href="{{ route('turma.show', ['turma' => $turma->id] + request()->except('r_mes') + ['r_mes' => $mesRegistros->copy()->subMonth()->format('Y-m')]) }}#tab-registros"
                   class="btn btn-sm btn-outline-secondary" title="Mês anterior"><i class="fa fa-chevron-left"></i></a>
                <h6>{{ $mesRegistros->locale('pt_BR')->translatedFormat('F \d\e Y') }}</h6>
                <a href="{{ route('turma.show', ['turma' => $turma->id] + request()->except('r_mes') + ['r_mes' => $mesRegistros->copy()->addMonth()->format('Y-m')]) }}#tab-registros"
                   class="btn btn-sm btn-outline-secondary" title="Próximo mês"><i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="rcal-grid">
                @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $dow)
                    <div class="rcal-dow">{{ $dow }}</div>
                @endforeach
                @foreach($semanasRegistros as $semana)
                    @foreach($semana as $dia)
                        @php
                            $diaKey = $dia->toDateString();
                            $doDia = $registrosMes[$diaKey] ?? collect();
                        @endphp
                        <div class="rcal-dia {{ $dia->month !== $mesRegistros->month ? 'rcal-out' : '' }} {{ $dia->isToday() ? 'rcal-hoje' : '' }}">
                            <div class="rcal-num">{{ $dia->day }}</div>
                            @foreach($doDia->take(3) as $r)
                                @php $m = $r['model']; @endphp
                                <button type="button" class="rc-item rc-item--{{ $r['tipo'] }}"
                                    data-tipo="{{ $r['tipo'] }}"
                                    data-titulo="{{ $r['item'] }}"
                                    data-data="{{ $r['data']?->format('d/m/Y') ?? '—' }}"
                                    data-semana="{{ $r['data']?->locale('pt_BR')->translatedFormat('l') ?? '' }}"
                                    data-disciplina="{{ $r['disciplina'] ?? '' }}"
                                    data-detalhe="{{ $r['detalhe'] ?? '' }}"
                                    @if($r['tipo'] === 'avaliacao')
                                        data-desc="{{ $m->descricao ?? '' }}"
                                        data-nota="{{ $m->nota_maxima ?? '' }}"
                                    @endif
                                    @if($r['tipo'] === 'diario')
                                        @can('diario.frequencia')
                                            data-lancar-url="{{ route('diario.chamada', ['turma_id' => $m->turma_id, 'disciplina_id' => $m->disciplina_id, 'data' => $m->data?->format('Y-m-d')]) }}"
                                            data-lancar-label="Chamada"
                                        @endcan
                                        @can('diario.edit')
                                            data-edit-url="{{ route('diario.edit', $m->id) }}"
                                        @endcan
                                        @can('diario.destroy')
                                            data-del-url="{{ route('diario.destroy', $m->id) }}"
                                        @endcan
                                    @else
                                        @can('avaliacao.notas')
                                            data-lancar-url="{{ route('avaliacao.notas', $m->id) }}"
                                            data-lancar-label="Lançar notas"
                                        @endcan
                                        @can('avaliacao.edit')
                                            data-edit-url="{{ route('avaliacao.edit', $m->id) }}"
                                        @endcan
                                        @can('avaliacao.destroy')
                                            data-del-url="{{ route('avaliacao.destroy', $m->id) }}"
                                        @endcan
                                    @endif
                                >{{ $r['tipo'] === 'diario' ? \Illuminate\Support\Str::limit($r['item'], 40) : $r['item'] }}</button>
                            @endforeach
                            @if($doDia->count() > 3)
                                <div class="rc-more">+{{ $doDia->count() - 3 }}</div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
            @if($registrosMes->isEmpty())
                <div class="rcal-vazio"><i class="fa fa-calendar-times mr-1"></i> Nenhum registro neste mês.</div>
            @endif
        </div>
        @can('avaliacao.create')
            <div class="card-footer">
                <a href="{{ route('turma.avaliacao.create', $turma->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-plus"></i> Nova avaliação</a>
            </div>
        @endcan
    </div>
    <x-side-drawer id="regDrawer" title="Detalhes do registro">
        <div>
            <span id="rdBadge" class="badge mb-1"></span>
            <h5 id="rdTitulo" class="font-weight-bold mb-0"></h5>
            <div class="rd-date">
                <i class="fa fa-calendar-day"></i> <b id="rdData"></b>
                <span class="sem" id="rdSemana"></span>
            </div>
            <div class="rd-row" id="rdRowDisc"><span class="rd-label">Disciplina</span><span class="rd-val" id="rdDisc"></span></div>
            <div class="rd-row" id="rdRowDet"><span class="rd-label" id="rdDetLabel">Detalhe</span><span class="rd-val" id="rdDet"></span></div>
            <div class="rd-row" id="rdRowNota" style="display:none;"><span class="rd-label">Nota máx.</span><span class="rd-val" id="rdNota"></span></div>
            <div class="rd-row" id="rdRowDesc" style="display:none;"><span class="rd-label">Descrição</span><span class="rd-val" id="rdDesc"></span></div>
        </div>
        <x-slot name="footer">
            <a id="rdLancar" href="#" class="btn btn-success"><i class="fa fa-pencil-alt mr-1"></i><span id="rdLancarTxt"></span></a>
            <a id="rdEditar" href="#" class="btn btn-warning"><i class="fa fa-edit mr-1"></i>Editar</a>
            <button type="button" id="rdExcluir" class="btn btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
        </x-slot>
    </x-side-drawer>
    <form id="formDelReg" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>


<div class="tab-pane fade" id="tab-alunos" role="tabpanel">
        @php $disciplinaFreq = $disciplinasVisiveis->firstWhere('id', (int) request('al_disciplina')); @endphp
        <form method="GET" action="{{ route('turma.show', $turma->id) }}#tab-alunos" class="card card-outline-secondary mb-3">
            <div class="card-body py-2">
                <div class="form-row align-items-end">
                    <div class="col-md-4 col-8">
                        <label class="form-label mb-1 small">Frequência por disciplina</label>
                        <select name="al_disciplina" class="form-control form-control-sm">
                            <option value="">Todas as disciplinas (consolidado)</option>
                            @foreach($disciplinasVisiveis as $disc)
                                <option value="{{ $disc->id }}" {{ (string) request('al_disciplina') === (string) $disc->id ? 'selected' : '' }}>{{ $disc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-4 text-right">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                        <a href="{{ route('turma.show', $turma->id) }}#tab-alunos" class="btn btn-sm btn-outline-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="card">
            <div class="card-header font-weight-bold">
                Lista de presença — Alunos matriculados ({{ $turma->matriculas->where('status', 'ativa')->count() }})
                @if($disciplinaFreq)
                    <span class="badge badge-info ml-1">{{ $disciplinaFreq->nome }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="th-sort">Nº</th>
                            <th class="th-sort">Aluno</th>
                            <th class="text-center th-sort">Presenças</th>
                            <th class="text-center th-sort">Faltas</th>
                            <th class="text-center th-sort">Justif./Abon.</th>
                            <th class="text-center th-sort">Freq. %</th>
                            <th class="th-sort">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turma->matriculas->where('status', 'ativa')->sortBy('numero_chamada') as $matricula)
                            @php
                                $freq = $presencas[$matricula->id] ?? null;
                                $pct = $freq && (int) $freq->total > 0
                                    ? round(((int) $freq->total - (int) $freq->falta) * 100 / (int) $freq->total)
                                    : null;
                            @endphp
                            <tr>
                                <td class="text-muted" data-sort="{{ $matricula->numero_chamada ?? -1 }}">{{ $matricula->numero_chamada ?? '—' }}</td>
                                <td>
                                    @can('aluno.show')
                                        <a href="{{ route('aluno.show', $matricula->aluno_id) }}" class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</a>
                                    @else
                                        {{ $matricula->aluno->pessoa->nome ?? '—' }}
                                    @endcan
                                </td>
                                <td class="text-center" data-sort="{{ $freq?->presente ?? -1 }}">{{ $freq?->presente ?? '—' }}</td>
                                <td class="text-center" data-sort="{{ $freq?->falta ?? -1 }}">{{ $freq?->falta ?? '—' }}</td>
                                <td class="text-center" data-sort="{{ $freq?->justificada ?? -1 }}" title="Justificadas / Abonadas">
                                    {{ $freq?->justificada ?? '—' }}@if($freq && (int) $freq->abonada > 0) <span class="text-muted">/ {{ $freq->abonada }}</span>@endif
                                </td>
                                <td class="text-center" data-sort="{{ $pct ?? -1 }}">
                                    @if($pct === null)
                                        <span class="text-muted">—</span>
                                    @else
                                        <span class="badge badge-{{ $pct >= 75 ? 'success' : 'danger' }}">{{ $pct }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @php $cor = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'][$matricula->status] ?? 'secondary'; @endphp
                                    <span class="badge badge-{{ $cor }}">{{ \App\Models\Matricula::STATUS[$matricula->status] ?? $matricula->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Nenhum aluno matriculado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
            @can('matricula.create')
                <div class="card-footer">
                    <a href="{{ route('turma.matricula.create', $turma->id) }}" class="btn btn-sm btn-primary">Nova matrícula nesta turma</a>
                </div>
            @endcan
        </div>
</div>

<div class="tab-pane fade" id="tab-tarefas" role="tabpanel">
    <div class="card">
        <div class="card-header font-weight-bold d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
            <span><i class="fas fa-tasks mr-1"></i> Atividades e provas da turma</span>
            <span class="d-flex align-items-center" style="gap:.5rem;">
                @include('partials.ux-view-toggle', ['viewKey' => 'turma_tarefas_view'])
                @can('tarefa.create')
                    <a href="{{ route('turma.tarefa.create', $turma->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-plus mr-1"></i> Nova tarefa</a>
                @endcan
            </span>
        </div>
        <div class="card-body p-0 ux-index-body" data-view="cards">
            <div class="ux-cards reg-cards">
                @forelse($tarefasCorrecao as $tarefa)
                    <article class="ux-card">
                        <div class="ux-card__accent"></div>
                        <div class="ux-card__head">
                            <div class="ux-card__avatar" style="background:{{ $tarefa->tipo === 'prova' ? '#fee2e2;color:#b91c1c' : '#e0e7ff;color:#4338ca' }};font-size:.72rem;">
                                <i class="fa {{ $tarefa->tipo === 'prova' ? 'fa-star' : 'fa-tasks' }}"></i>
                            </div>
                            <div class="ux-card__head-info">
                                <div class="ux-card__id">entrega até {{ $tarefa->data_entrega->format('d/m/Y') }}</div>
                                <div class="ux-card__title">{{ $tarefa->titulo }}</div>
                                <div class="ux-card__sub">{{ $tarefa->disciplina->nome ?? 'Geral' }} · {{ $tarefa->total_count }} entrega(s)</div>
                            </div>
                            <div class="ux-card__badges">
                                @if($tarefa->tipo !== 'atividade')
                                    <span class="ux-card__badge ux-card__badge--{{ $tarefa->tipo === 'prova' ? 'red' : 'amber' }}">{{ $tarefa->tipo_label }}</span>
                                @endif
                                @if($tarefa->pendentes_count > 0)
                                    <span class="ux-card__badge ux-card__badge--red">{{ $tarefa->pendentes_count }} aguardando</span>
                                @else
                                    <span class="ux-card__badge ux-card__badge--green">em dia</span>
                                @endif
                            </div>
                        </div>
                        <div class="ux-card__foot">
                            @can('tarefa.entregas')
                                <a href="{{ route('tarefa.entregas', $tarefa->id) }}" class="btn btn-xs btn-{{ $tarefa->pendentes_count > 0 ? 'danger' : 'outline-secondary' }}"><i class="fa fa-check-double mr-1"></i> Corrigir</a>
                            @endcan
                            @can('tarefa.edit')
                                <a href="{{ route('tarefa.edit', $tarefa->id) }}" class="btn btn-xs btn-outline-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                        </div>
                    </article>
                @empty
                    <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                        <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
                        Nenhuma atividade publicada nesta turma.
                    </div>
                @endforelse
            </div>
            <div class="ux-table table-responsive"><table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Título</th>
                        <th>Disciplina</th>
                        <th style="width:110px">Entrega até</th>
                        <th style="width:110px">Entregas</th>
                        <th style="width:130px">Situação</th>
                        <th class="text-right" style="width:160px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tarefasCorrecao as $tarefa)
                        <tr>
                            <td class="font-weight-bold">
                                {{ $tarefa->titulo }}
                                @if($tarefa->tipo !== 'atividade')
                                    <span class="badge badge-{{ $tarefa->tipo === 'prova' ? 'danger' : 'warning' }}">{{ $tarefa->tipo_label }}</span>
                                @endif
                            </td>
                            <td>{{ $tarefa->disciplina->nome ?? 'Geral' }}</td>
                            <td class="text-nowrap">{{ $tarefa->data_entrega->format('d/m/Y') }}</td>
                            <td>{{ $tarefa->total_count }}</td>
                            <td>
                                @if($tarefa->pendentes_count > 0)
                                    <span class="badge badge-danger">{{ $tarefa->pendentes_count }} aguardando</span>
                                @else
                                    <span class="badge badge-success">em dia</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @can('tarefa.entregas')
                                    <a href="{{ route('tarefa.entregas', $tarefa->id) }}" class="btn btn-xs btn-{{ $tarefa->pendentes_count > 0 ? 'danger' : 'outline-secondary' }}"><i class="fa fa-check-double mr-1"></i> Corrigir</a>
                                @endcan
                                @can('tarefa.edit')
                                    <a href="{{ route('tarefa.edit', $tarefa->id) }}" class="btn btn-xs btn-outline-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Nenhuma atividade publicada nesta turma.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-materiais" role="tabpanel">
    <div class="row">
        <div class="col-lg-5">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title font-weight-bold">Publicar material</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('turma.material.store', $turma->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="titulo" class="form-control" maxlength="200" required placeholder="Ex.: Lista de exercícios — cap. 3">
                        </div>
                        <div class="form-group">
                            <label>Disciplina</label>
                            <select name="disciplina_id" class="form-control">
                                <option value="">Geral (todas)</option>
                                @foreach($disciplinasVisiveis as $d)
                                    <option value="{{ $d->id }}">{{ $d->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descrição <small class="text-muted">(opcional)</small></label>
                            <textarea name="descricao" class="form-control" rows="2" maxlength="2000"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Arquivo</label>
                            <input type="file" name="arquivo" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.txt">
                            <small class="text-muted">PDF, Office, imagem ou ZIP — até 15MB. Ou informe um link:</small>
                        </div>
                        <div class="form-group">
                            <input type="url" name="url" class="form-control" maxlength="500" placeholder="https://…">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-upload mr-1"></i> Publicar material</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
                    <span>Materiais publicados</span>
                    @include('partials.ux-view-toggle', ['viewKey' => 'turma_materiais_view'])
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards reg-cards">
                        @forelse($materiais as $material)
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar" style="background:#dcfce7;color:#15803d;font-size:.72rem;">
                                        <i class="fa {{ $material->arquivo_path ? 'fa-file-alt' : 'fa-link' }}"></i>
                                    </div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__id">{{ $material->created_at?->format('d/m/Y') ?? '—' }}</div>
                                        <div class="ux-card__title">{{ $material->titulo }}</div>
                                        <div class="ux-card__sub">{{ $material->disciplina->nome ?? 'Geral' }} · {{ $material->autor->nome ?? '' }}</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        @if($material->arquivo_path)
                                            <span class="ux-card__badge ux-card__badge--blue">Arquivo</span>
                                        @elseif($material->url)
                                            <span class="ux-card__badge ux-card__badge--gray">Link</span>
                                        @endif
                                    </div>
                                </div>
                                @if($material->descricao)
                                    <div class="ux-card__meta">
                                        <div class="ux-card__meta-row">
                                            <span class="ux-card__meta-value">{{ $material->descricao }}</span>
                                        </div>
                                    </div>
                                @endif
                                <div class="ux-card__foot">
                                    @if($material->arquivo_path)
                                        <a href="{{ asset('storage/'.$material->arquivo_path) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Baixar"><i class="fa fa-download"></i></a>
                                    @elseif($material->url)
                                        <a href="{{ $material->url }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary" title="Abrir link"><i class="fa fa-external-link-alt"></i></a>
                                    @endif
                                    <form method="POST" action="{{ route('turma.material.destroy', [$turma->id, $material->id]) }}" onsubmit="return confirm('Remover este material?');" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger" title="Remover"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Nenhum material publicado ainda.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive"><table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Título</th>
                                <th>Disciplina</th>
                                <th>Autor</th>
                                <th style="width:95px">Data</th>
                                <th class="text-right" style="width:110px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materiais as $material)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $material->titulo }}</span>
                                        @if($material->descricao)<small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($material->descricao, 60) }}</small>@endif
                                    </td>
                                    <td>{{ $material->disciplina->nome ?? 'Geral' }}</td>
                                    <td>{{ $material->autor->nome ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $material->created_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-right text-nowrap">
                                        @if($material->arquivo_path)
                                            <a href="{{ asset('storage/'.$material->arquivo_path) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Baixar"><i class="fa fa-download"></i></a>
                                        @elseif($material->url)
                                            <a href="{{ $material->url }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary" title="Abrir link"><i class="fa fa-external-link-alt"></i></a>
                                        @endif
                                        <form method="POST" action="{{ route('turma.material.destroy', [$turma->id, $material->id]) }}" onsubmit="return confirm('Remover este material?');" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger" title="Remover"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Nenhum material publicado ainda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
@stop

@section('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
    $(function () {
        var hash = window.location.hash;
        if (hash && $('#turmaTabs a[href="' + hash + '"]').length) {
            $('#turmaTabs a[href="' + hash + '"]').tab('show');
        }
        $('#turmaTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', e.target.hash);
        });

        $('.te-select2').each(function () {
            $(this).select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: $(this).data('placeholder') || '',
                allowClear: true,
                dropdownParent: $(this).closest('.modal')
            });
        });

        /* Diario e avaliacoes: alterna Lista / Calendario (persiste). */
        var regBody = $('.ux-index-body').has('.reg-cards');
        var regCal = $('#registros-cal');
        function setModoReg(modo) {
            var cal = modo === 'cal';
            regCal.toggle(cal);
            regBody.toggle(!cal);
            $('.reg-modo [data-modo]').removeClass('active')
                .filter('[data-modo="' + (cal ? 'cal' : 'lista') + '"]').addClass('active');
            try { localStorage.setItem('turma_registros_modo', cal ? 'cal' : 'lista'); } catch (e) {}
        }
        $('.reg-modo [data-modo]').on('click', function () {
            setModoReg($(this).data('modo'));
        });
        try { setModoReg(localStorage.getItem('turma_registros_modo') || 'lista'); } catch (e) {}

        /* Drawer de detalhes do registro (clique no item do calendario). */
        var delUrl = '';
        $(document).on('click', '.rc-item', function () {
            var d = $(this).data();
            var diario = d.tipo === 'diario';
            $('#rdBadge')
                .attr('class', 'badge mb-1 ' + (diario ? 'badge-info' : 'badge-warning'))
                .html('<i class="fa ' + (diario ? 'fa-book' : 'fa-star') + ' mr-1"></i>' + (diario ? 'Diário' : 'Avaliação'));
            $('#rdTitulo').text(d.titulo || '—');
            $('#rdData').text(d.data || '—');
            $('#rdSemana').text(d.semana || '');
            $('#rdRowDisc').toggle(!!d.disciplina).find('#rdDisc').text(d.disciplina || '');
            $('#rdDetLabel').text(diario ? 'Professor' : 'Tipo / Período');
            $('#rdRowDet').toggle(!!d.detalhe).find('#rdDet').text(d.detalhe || '');
            $('#rdRowNota').toggle(!diario && d.nota !== undefined && d.nota !== '').find('#rdNota').text(d.nota || '');
            $('#rdRowDesc').toggle(!!d.desc).find('#rdDesc').text(d.desc || '');

            $('#rdLancar').toggle(!!d.lancarUrl).attr('href', d.lancarUrl || '#');
            $('#rdLancarTxt').text(d.lancarLabel || '');
            $('#rdEditar').toggle(!!d.editUrl).attr('href', d.editUrl || '#');
            delUrl = d.delUrl || '';
            $('#rdExcluir').toggle(!!delUrl);

            document.getElementById('regDrawer').classList.add('open');
            document.getElementById('regDrawerBackdrop').classList.add('show');
        });

        $('#rdExcluir').on('click', function () {
            if (!delUrl) return;
            var titulo = $('#rdTitulo').text();
            var confirma = function () {
                $('#formDelReg').attr('action', delUrl).submit();
            };
            if (typeof window.Swal !== 'undefined' && window.Swal.fire) {
                window.Swal.fire({
                    title: 'Excluir o registro?',
                    text: '"' + titulo + '" será removido. Esta ação não pode ser desfeita.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    focusCancel: true,
                }).then(function (r) { if (r && r.isConfirmed) confirma(); });
            } else if (window.confirm('Excluir "' + titulo + '"?')) {
                confirma();
            }
        });
    });
</script>
@stop
