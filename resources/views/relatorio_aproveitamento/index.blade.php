@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Médias, aprovação e cobertura de notas por turma" />
@stop

@push('css')
<style>
    .rf-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .rf-stat { background: #fff; border: 1px solid #e8edf5; border-radius: 14px; padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rf-stat i { width: 40px; height: 40px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .rf-stat .n { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1.05; }
    .rf-stat .l { font-size: .66rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }
    .rf-stat .d { font-size: .66rem; color: #94a3b8; }
    .rf-ic--blue { background: #eff6ff; color: #3b82f6; }
    .rf-ic--slate { background: #f1f5f9; color: #64748b; }
    .rf-ic--green { background: #f0fdf4; color: #22c55e; }
    .rf-ic--red { background: #fef2f2; color: #dc2626; }
    .rf-ic--violet { background: #f5f3ff; color: #8b5cf6; }
    .rf-ic--amber { background: #fffbeb; color: #f59e0b; }

    .rf-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.05); margin-bottom: 1rem; overflow: hidden; }
    .rf-head { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9; background: #fbfcfe; }
    .rf-head > i { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .78rem; flex-shrink: 0; }
    .rf-head .rf-t { font-size: .86rem; font-weight: 800; color: #0f172a; }
    .rf-head .rf-s { font-size: .72rem; color: #94a3b8; }
    .rf-head .flex-grow-1 { min-width: 0; }
    .rf-body { padding: 1.1rem 1.15rem; }

    .rf-bar { height: 8px; border-radius: 5px; background: #eef1f6; overflow: hidden; }
    .rf-bar > div { height: 8px; border-radius: 5px; }
</style>
@endpush

@section('content')
{{-- Resumo --}}
<div class="rf-stats">
    <div class="rf-stat">
        <i class="fas fa-chalkboard rf-ic--blue"></i>
        <div><div class="n">{{ $resumo['turmas'] }}</div><div class="l">Turmas</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-user-graduate rf-ic--violet"></i>
        <div><div class="n">{{ $resumo['alunos'] }}</div><div class="l">Alunos</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-star-half-alt rf-ic--green"></i>
        <div><div class="n">{{ $resumo['media_geral'] !== null ? number_format($resumo['media_geral'], 1, ',', '') : '—' }}</div><div class="l">Média geral</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-check-circle rf-ic--amber"></i>
        <div>
            <div class="n">{{ $resumo['pct_aprovacao'] !== null ? $resumo['pct_aprovacao'].'%' : '—' }}</div>
            <div class="l">Aprovação (≥ {{ $notaAprovacao }})</div>
            <div class="d">{{ $resumo['aprovados'] }} de {{ $resumo['alunos_com_media'] }} com média</div>
        </div>
    </div>
</div>

{{-- Panorama por turma --}}
@if($linhas->isNotEmpty())
<div class="rf-card">
    <div class="rf-head">
        <i class="fas fa-chart-bar rf-ic--violet"></i>
        <div>
            <div class="rf-t">Panorama por turma</div>
            <div class="rf-s">Aprovação geral e comparativo entre turmas</div>
        </div>
    </div>
    <div class="rf-body">
        <div class="row">
            <div class="col-md-3 d-flex align-items-center justify-content-center">
                @php $pctG = $resumo['pct_aprovacao'] ?? 0; @endphp
                <div style="width:130px;height:130px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                    background:conic-gradient(#22c55e {{ $pctG }}%, #ef4444 {{ $pctG }}%);">
                    <div style="width:95px;height:95px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <strong style="font-size:1.3rem;color:#1e3a8a;">{{ $pctG }}%</strong>
                        <small class="text-muted" style="font-size:.62rem;">aprovação</small>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                @foreach($linhas as $l)
                    <div class="d-flex align-items-center mb-2">
                        <div style="width:130px;font-size:.72rem;font-weight:600;" class="text-truncate" title="{{ $l['turma'] }}">{{ $l['turma'] }}</div>
                        <div class="flex-grow-1">
                            <div class="rf-bar">
                                <div style="width:{{ $l['pct_aprovacao'] ?? 0 }}%;
                                    background:{{ ($l['pct_aprovacao'] ?? 0) >= 70 ? '#22c55e' : (($l['pct_aprovacao'] ?? 0) >= 40 ? '#f59e0b' : '#ef4444') }};"></div>
                            </div>
                        </div>
                        <div style="width:120px;font-size:.72rem;" class="text-right">
                            <b>{{ $l['pct_aprovacao'] !== null ? $l['pct_aprovacao'].'%' : '—' }}</b>
                            @if($l['media_geral'] !== null)<span class="text-muted"> · média {{ number_format($l['media_geral'], 1, ',', '') }}</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- Filtro padrao (GET) --}}
@php
    $chips = [];
    $mapasChips = [
        'escola_id' => ['Escola', collect($escolas)->pluck('nome', 'id')],
        'ano_letivo_id' => ['Ano', collect($anosLetivos)->pluck('ano', 'id')],
        'etapa_id' => ['Etapa', collect($etapas)->pluck('nome', 'id')],
        'turma_id' => ['Turma', collect($turmas)->pluck('nome', 'id')],
        'turno' => ['Turno', collect($turnos)->mapWithKeys(fn ($t) => [$t => $t])],
        'situacao' => ['Situação', collect(['risco' => 'Em risco (<60%)', 'boas' => 'Boas (≥80%)'])],
    ];
    foreach ($mapasChips as $ck => [$clabel, $cmap]) {
        $cv = $filtros[$ck] ?? null;
        if ($cv !== null && $cv !== '') {
            $chips[] = ['label' => $clabel, 'valor' => $cmap[$cv] ?? $cv, 'param' => $ck];
        }
    }
@endphp
<div class="card mb-2 filter-card">
    <div class="card-body p-2">
        <h6 class="filter-heading text-secondary text-xs mb-1"><i class="fa fa-filter mr-1"></i> Filtros</h6>
        <hr class="filter-heading m-0 mb-2">
        <form method="GET" action="{{ route('relatorio_aproveitamento.index') }}">
            <div class="row align-items-end">
                @unless(app(\App\Support\EscolaContext::class)->filtrar())
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Escola</label>
                    <select name="escola_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($escolas as $escola)
                            <option value="{{ $escola->id }}" {{ ($filtros['escola_id'] ?? '') == $escola->id ? 'selected' : '' }}>{{ $escola->nome }}</option>
                        @endforeach
                    </select>
                </div>
                @endunless
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Ano letivo</label>
                    <select name="ano_letivo_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($anosLetivos as $ano)
                            <option value="{{ $ano->id }}" {{ ($filtros['ano_letivo_id'] ?? '') == $ano->id ? 'selected' : '' }}>{{ $ano->ano }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Etapa</label>
                    <select name="etapa_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($etapas as $etapa)
                            <option value="{{ $etapa->id }}" {{ ($filtros['etapa_id'] ?? '') == $etapa->id ? 'selected' : '' }}>{{ $etapa->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Turma</label>
                    <select name="turma_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($turmas as $turma)
                            <option value="{{ $turma->id }}" {{ ($filtros['turma_id'] ?? '') == $turma->id ? 'selected' : '' }}>{{ $turma->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Turno</label>
                    <select name="turno" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno }}" {{ ($filtros['turno'] ?? '') == $turno ? 'selected' : '' }}>{{ $turno }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto flex-grow-1 filter-field">
                    <label class="text-xs mb-0">Situação</label>
                    <select name="situacao" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="risco" {{ ($filtros['situacao'] ?? '') === 'risco' ? 'selected' : '' }}>⚠ Em risco (&lt;60%)</option>
                        <option value="boas" {{ ($filtros['situacao'] ?? '') === 'boas' ? 'selected' : '' }}>✓ Boas (≥80%)</option>
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end filter-actions" style="gap:6px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-search mr-1"></i> Filtrar</button>
                </div>
            </div>
        </form>
        @if(count($chips) > 0)
            <div class="mt-2 d-flex flex-wrap align-items-center" style="gap:6px;">
                <span class="text-xs text-secondary text-bold">Aplicados:</span>
                @foreach($chips as $chip)
                    <a href="{{ route('relatorio_aproveitamento.index', request()->except($chip['param'])) }}"
                       class="badge badge-secondary font-weight-normal" title="Remover filtro">
                        <i class="fa fa-times mr-1"></i>{{ $chip['label'] }}: <b>{{ $chip['valor'] }}</b>
                    </a>
                @endforeach
                <a href="{{ route('relatorio_aproveitamento.index') }}" class="badge badge-danger font-weight-normal" title="Limpar todos">
                    <i class="fa fa-times mr-1"></i> Limpar tudo
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Tabela --}}
<div class="rf-card">
    <div class="rf-head">
        <i class="fas fa-list rf-ic--blue"></i>
        <div class="flex-grow-1">
            <div class="rf-t">Turmas</div>
            <div class="rf-s">Média geral, aprovação e cobertura de notas</div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th class="pl-3">Escola</th>
                    <th>Turma</th>
                    <th>Etapa</th>
                    <th>Ano</th>
                    <th class="text-center">Alunos</th>
                    <th class="text-center">Com média</th>
                    <th class="text-center">Média geral</th>
                    <th class="text-center">Aprovados</th>
                    <th class="text-center">% Aprovação</th>
                    <th class="text-center">Notas lançadas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($linhas as $l)
                    <tr>
                        <td class="pl-3"><small>{{ $l['escola'] ?? '—' }}</small></td>
                        <td class="font-weight-bold">{{ $l['turma'] }}</td>
                        <td><small>{{ $l['etapa'] ?? '—' }}</small></td>
                        <td class="text-center">{{ $l['ano_letivo'] ?? '—' }}</td>
                        <td class="text-center">{{ $l['alunos'] }}</td>
                        <td class="text-center">{{ $l['alunos_com_media'] }}</td>
                        <td class="text-center">
                            @if($l['media_geral'] !== null)
                                <span class="badge badge-{{ $l['media_geral'] >= $notaAprovacao ? 'success' : ($l['media_geral'] >= 5 ? 'warning' : 'danger') }}">
                                    {{ number_format($l['media_geral'], 1, ',', '') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $l['aprovados'] }}</td>
                        <td class="text-center">
                            @if($l['pct_aprovacao'] !== null)
                                <span class="badge badge-{{ $l['pct_aprovacao'] >= 70 ? 'success' : ($l['pct_aprovacao'] >= 40 ? 'warning' : 'danger') }}">
                                    {{ $l['pct_aprovacao'] }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center"><small>{{ number_format($l['notas_lancadas'], 0, ',', '.') }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                            Nenhuma turma encontrada com os filtros informados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
