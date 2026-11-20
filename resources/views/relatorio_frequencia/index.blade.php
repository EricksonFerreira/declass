@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Assiduidade dos alunos — piores índices primeiro" />
@stop

@push('css')
<style>
    .rf-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .8rem; margin-bottom: 1rem; }
    .rf-stat { background: #fff; border: 1px solid #e8edf5; border-radius: 14px; padding: .85rem 1rem; display: flex; align-items: center; gap: .75rem; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .rf-stat i { width: 40px; height: 40px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
    .rf-stat .n { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1.05; }
    .rf-stat .l { font-size: .66rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }
    .rf-ic--blue { background: #eff6ff; color: #3b82f6; }
    .rf-ic--slate { background: #f1f5f9; color: #64748b; }
    .rf-ic--green { background: #f0fdf4; color: #22c55e; }
    .rf-ic--red { background: #fef2f2; color: #dc2626; }
    .rf-ic--violet { background: #f5f3ff; color: #8b5cf6; }
    .rf-ic--amber { background: #fffbeb; color: #f59e0b; }

    .rf-tabs { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .9rem; }
    .rf-tab { display: inline-flex; align-items: center; gap: .4rem; padding: .42rem 1rem; border-radius: 999px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .78rem; font-weight: 700; color: #64748b; cursor: pointer; transition: all .13s; }
    .rf-tab:hover { border-color: #c7d2fe; color: #4f46e5; }
    .rf-tab.is-on { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    .rf-pane { display: none; }
    .rf-pane.is-on { display: block; }

    .rf-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.05); margin-bottom: 1rem; overflow: hidden; }
    .rf-head { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9; background: #fbfcfe; }
    .rf-head i { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .78rem; flex-shrink: 0; }
    .rf-head .rf-t { font-size: .86rem; font-weight: 800; color: #0f172a; }
    .rf-head .rf-s { font-size: .72rem; color: #94a3b8; }
    .rf-head .flex-grow-1 { min-width: 0; }
    .rf-body { padding: 1rem 1.15rem; }
    .rf-empty { border: 1.5px dashed #e2e8f0; border-radius: 14px; padding: 2.2rem 1rem; text-align: center; color: #94a3b8; font-size: .82rem; }
    .rf-empty i { font-size: 1.6rem; display: block; margin-bottom: .5rem; color: #cbd5e1; }
</style>
@endpush

@section('content')
{{-- Resumo --}}
<div class="rf-stats">
    <div class="rf-stat">
        <i class="fas fa-user-graduate rf-ic--blue"></i>
        <div><div class="n">{{ $resumo['alunos'] }}</div><div class="l">Alunos</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-clipboard-check rf-ic--slate"></i>
        <div><div class="n">{{ $resumo['com_frequencia'] }}</div><div class="l">Com frequência registrada</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-percentage rf-ic--green"></i>
        <div><div class="n">{{ $resumo['media_pct'] !== null ? $resumo['media_pct'].'%' : '—' }}</div><div class="l">Frequência média</div></div>
    </div>
    <div class="rf-stat">
        <i class="fas fa-exclamation-triangle rf-ic--red"></i>
        <div><div class="n">{{ $resumo['em_risco'] }}</div><div class="l">Em risco (&lt;75%)</div></div>
    </div>
</div>

<div class="rf-tabs" role="tablist">
    <button type="button" class="rf-tab is-on" data-pane="rf-visao"><i class="fas fa-chart-pie"></i> Visão geral</button>
    <button type="button" class="rf-tab" data-pane="rf-alunos"><i class="fas fa-list"></i> Alunos</button>
</div>

{{-- Visão geral: gráficos --}}
<div class="rf-pane is-on" id="rf-visao">
    @php $temDados = array_sum($graficos['faixas'] ?? []) > 0; @endphp
    @if($temDados)
    <div class="row">
        <div class="col-md-5">
            <div class="rf-card">
                <div class="rf-head">
                    <i class="fas fa-chart-pie rf-ic--violet"></i>
                    <div>
                        <div class="rf-t">Distribuição por faixa de frequência</div>
                        <div class="rf-s">≥90% regular · 75–90% atenção · 50–75% alerta · &lt;50% crítico</div>
                    </div>
                </div>
                <div class="rf-body">
                    <div id="chartFaixas"></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="rf-card">
                <div class="rf-head">
                    <i class="fas fa-chart-column rf-ic--amber"></i>
                    <div>
                        <div class="rf-t">Turmas com mais alunos em risco</div>
                        <div class="rf-s">Quantidade em risco × frequência média da turma</div>
                    </div>
                </div>
                <div class="rf-body">
                    <div id="chartTurmas"></div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="rf-card">
        <div class="rf-body">
            <div class="rf-empty">
                <i class="fas fa-chart-pie"></i>
                Ainda não há frequência registrada suficiente para montar os gráficos.
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Alunos: filtros + tabela --}}
<div class="rf-pane" id="rf-alunos">
    <div class="rf-card">
        <div class="rf-head">
            <i class="fas fa-list rf-ic--blue"></i>
            <div class="flex-grow-1">
                <div class="rf-t">Alunos</div>
                <div class="rf-s">Frequência por matrícula — piores índices primeiro</div>
            </div>
            <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        </div>
        <div class="px-3 pt-3">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Escola</th>
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
                            <td class="pl-3"><small>{{ $m->escola }}</small></td>
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
        @if($matriculas->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $matriculas->links() }}
            </div>
        @endif
    </div>
</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
$(function () {
    // Tabs com persistencia (sobrevive ao reload dos filtros).
    var storeKey = 'relatorio_frequencia_tab';
    function ativar(id) {
        var btn = $('.rf-tab[data-pane="' + id + '"]');
        if (!btn.length || !$('#' + id).length) { return; }
        $('.rf-tab').removeClass('is-on');
        $('.rf-pane').removeClass('is-on');
        btn.addClass('is-on');
        $('#' + id).addClass('is-on');
    }
    $('.rf-tab').on('click', function () {
        ativar($(this).data('pane'));
        try { localStorage.setItem(storeKey, $(this).data('pane')); } catch (e) {}
    });
    var salva = null;
    try { salva = localStorage.getItem(storeKey); } catch (e) {}
    if (salva) { ativar(salva); }

    if (typeof ApexCharts === 'undefined') { return; }

    var faixas = @json($graficos['faixas'] ?? []);
    var porTurma = @json($graficos['por_turma'] ?? []);

    // Donut: distribuicao por faixa de frequencia.
    if (document.getElementById('chartFaixas')) {
        new ApexCharts(document.getElementById('chartFaixas'), {
            chart: { type: 'donut', height: 265, fontFamily: 'inherit' },
            labels: ['Regular (≥90%)', 'Atenção (75–90%)', 'Alerta (50–75%)', 'Crítico (<50%)', 'Sem aulas'],
            series: [faixas.regular || 0, faixas.atencao || 0, faixas.alerta || 0, faixas.critico || 0, faixas.sem_aulas || 0],
            colors: ['#22c55e', '#eab308', '#f97316', '#dc2626', '#cbd5e1'],
            stroke: { width: 2, colors: ['#fff'] },
            legend: { position: 'bottom', fontSize: '12px', markers: { radius: 12 } },
            dataLabels: { enabled: true, formatter: function (v) { return v >= 6 ? Math.round(v) + '%' : ''; } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '64%',
                        labels: {
                            show: true,
                            total: { show: true, label: 'Alunos', fontSize: '11px', fontWeight: 700, color: '#94a3b8',
                                     formatter: function (w) { return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0); } }
                        }
                    }
                }
            },
            tooltip: { y: { formatter: function (v) { return v + ' aluno(s)'; } } }
        }).render();
    }

    // Colunas com eixo duplo: alunos em risco (esq) x media % (dir).
    if (document.getElementById('chartTurmas') && porTurma.length) {
        var dados = porTurma.filter(function (t) { return t.em_risco > 0; });
        if (!dados.length) { dados = porTurma.slice(0, 5); }
        new ApexCharts(document.getElementById('chartTurmas'), {
            chart: { type: 'line', height: 265, fontFamily: 'inherit', toolbar: { show: false } },
            series: [
                { name: 'Alunos em risco (<75%)', type: 'column', data: dados.map(function (t) { return t.em_risco; }) },
                { name: 'Frequência média (%)', type: 'line', data: dados.map(function (t) { return t.media_pct; }) }
            ],
            colors: ['#dc2626', '#3b82f6'],
            stroke: { width: [0, 3], curve: 'smooth' },
            xaxis: { categories: dados.map(function (t) { return t.turma; }), labels: { rotate: -35, style: { fontSize: '10px' } } },
            yaxis: [
                { title: { text: 'Alunos' }, decimalsInFloat: 0 },
                { opposite: true, min: 0, max: 100, title: { text: '% média' } }
            ],
            legend: { position: 'bottom', fontSize: '12px', markers: { radius: 12 } },
            markers: { size: 4 },
            grid: { strokeDashArray: 4 },
            tooltip: { shared: true, intersect: false }
        }).render();
    }
});
</script>
@endpush
