@extends('adminlte::page')

@section('plugins.Chartjs', true)

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Assiduidade dos alunos — piores índices primeiro" />
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Alunos</span>
                <span class="info-box-number">{{ $resumo['alunos'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-secondary"><i class="fas fa-clipboard-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Com frequência registrada</span>
                <span class="info-box-number">{{ $resumo['com_frequencia'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Frequência média</span>
                <span class="info-box-number">{{ $resumo['media_pct'] !== null ? $resumo['media_pct'].'%' : '—' }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em risco (&lt;75%)</span>
                <span class="info-box-number">{{ $resumo['em_risco'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Graficos: distribuicao por faixa + turmas com mais alunos em risco --}}
@php($temDados = array_sum($graficos['faixas'] ?? []) > 0)
@if($temDados)
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold mb-0"><i class="fas fa-chart-pie mr-1"></i> Distribuição por faixa de frequência</h3>
            </div>
            <div class="card-body">
                <canvas id="chartFaixas" style="max-height:220px;"></canvas>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        ≥90% regular · 75–90% atenção · 50–75% alerta · &lt;50% crítico (abaixo do mínimo legal de 75%)
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold mb-0"><i class="fas fa-chart-bar mr-1"></i> Turmas com mais alunos em risco</h3>
            </div>
            <div class="card-body">
                <canvas id="chartTurmas" style="max-height:245px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Escola</th>
                        <th>Turma</th>
                        <th>Aluno</th>
                        <th class="text-center">#</th>
                        <th class="text-center">Presentes</th>
                        <th class="text-center">Justificadas</th>
                        <th class="text-center">Faltas</th>
                        <th class="text-center">Aulas</th>
                        <th class="text-center">Frequência</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $m)
                        @php
                            $pct = $m->total > 0 ? round((($m->total - $m->falta) / $m->total) * 100, 1) : null;
                            $cor = $pct === null ? 'secondary' : ($pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                        @endphp
                        <tr>
                            <td><small>{{ $m->escola }}</small></td>
                            <td>{{ $m->turma }}</td>
                            <td class="font-weight-bold">{{ $m->aluno }}</td>
                            <td class="text-center">{{ $m->numero_chamada ?? '—' }}</td>
                            <td class="text-center">{{ $m->presente }}</td>
                            <td class="text-center">{{ $m->justificada }}</td>
                            <td class="text-center font-weight-bold {{ $m->falta > 0 ? 'text-danger' : '' }}">{{ $m->falta }}</td>
                            <td class="text-center">{{ $m->total }}</td>
                            <td class="text-center">
                                @if($pct !== null)
                                    <span class="badge badge-{{ $cor }}">{{ $pct }}%</span>
                                @else
                                    <span class="text-muted">sem aulas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-check fa-2x mb-2 d-block"></i>
                                Nenhuma matrícula encontrada com os filtros informados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($matriculas->hasPages())
        <div class="card-footer">
            {{ $matriculas->links() }}
        </div>
    @endif
</div>
@stop

@push('js')
<script>
$(function () {
    if (typeof Chart === 'undefined') return;

    var faixas = @json($graficos['faixas'] ?? []);
    var porTurma = @json($graficos['por_turma'] ?? []);

    var ctxF = document.getElementById('chartFaixas');
    if (ctxF) {
        new Chart(ctxF.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Regular (≥90%)', 'Atenção (75–90%)', 'Alerta (50–75%)', 'Crítico (<50%)', 'Sem aulas'],
                datasets: [{
                    data: [faixas.regular, faixas.atencao, faixas.alerta, faixas.critico, faixas.sem_aulas],
                    backgroundColor: ['#22c55e', '#eab308', '#f97316', '#dc2626', '#cbd5e1'],
                }],
            },
            options: {
                legend: {position: 'bottom'},
                cutoutPercentage: 62,
            },
        });
    }

    var ctxT = document.getElementById('chartTurmas');
    if (ctxT && porTurma.length) {
        var dados = porTurma.filter(function (t) { return t.em_risco > 0; });
        if (!dados.length) dados = porTurma.slice(0, 5);
        new Chart(ctxT.getContext('2d'), {
            type: 'horizontalBar',
            data: {
                labels: dados.map(function (t) { return t.turma; }),
                datasets: [
                    {
                        label: 'Alunos em risco (<75%)',
                        data: dados.map(function (t) { return t.em_risco; }),
                        backgroundColor: '#dc2626',
                    },
                    {
                        label: 'Frequência média (%)',
                        data: dados.map(function (t) { return t.media_pct; }),
                        backgroundColor: '#93c5fd',
                    },
                ],
            },
            options: {
                legend: {position: 'bottom'},
                scales: {
                    xAxes: [{ticks: {beginAtZero: true}}],
                },
            },
        });
    }
});
</script>
@endpush
