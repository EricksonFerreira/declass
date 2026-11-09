@extends('adminlte::page')

@section('title', $titulo)

@section('plugins.Chartjs', true)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Indicadores financeiros da rede — por escola e ano" />
@stop

@section('content')
<form method="GET" action="{{ route('financeiro_visao_geral.index') }}" class="mb-3">
    <div class="row justify-content-end">
        @unless($escolaContextoFixo)
        <div class="col-md-3 col-6">
            <select name="escola_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}" @selected($escolaId === $escola->id)>{{ $escola->nome }}</option>
                @endforeach
            </select>
        </div>
        @endunless
        <div class="col-md-2 col-4">
            <select name="ano" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($anos as $anoOpcao)
                    <option value="{{ $anoOpcao }}" @selected($ano === $anoOpcao)>{{ $anoOpcao }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-trend-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Saídas no ano</span>
                <span class="info-box-number">R$ {{ number_format($dados['saidas_ano'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">A receber no ano</span>
                <span class="info-box-number">R$ {{ number_format($dados['a_receber_ano'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-circle-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Recebido hoje</span>
                <span class="info-box-number">R$ {{ number_format($dados['recebido_hoje'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em atraso</span>
                <span class="info-box-number text-danger">R$ {{ number_format($dados['inadimplencia_total'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Inadimplência por Turma</h3>
                <div class="card-tools">
                    <a href="{{ route('relatorio_inadimplencia.index') }}" class="btn btn-tool">Ver detalhes</a>
                </div>
            </div>
            <div class="card-body">
                @if($dados['por_turma']->isEmpty())
                    <p class="text-muted text-center mb-0"><i class="fas fa-check-circle text-success"></i> Nenhuma mensalidade vencida em aberto em {{ $ano }}.</p>
                @else
                    @php $maiorTotal = max(1, (float) $dados['por_turma']->max('total')); @endphp
                    @foreach($dados['por_turma'] as $turma)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between" style="font-size:.85rem">
                                <strong>{{ $turma->nome }}</strong>
                                <span class="text-muted">{{ $turma->qtd }} {{ \Illuminate\Support\Str::plural('parcela', $turma->qtd) }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1" style="height:10px">
                                    <div class="progress-bar bg-danger" style="width: {{ round($turma->total / $maiorTotal * 100, 1) }}%"></div>
                                </div>
                                <span class="ml-2 text-danger font-weight-bold" style="font-size:.85rem;white-space:nowrap">
                                    R$ {{ number_format($turma->total, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Maiores atrasos</h3>
            </div>
            <div class="card-body p-0">
                @if($dados['maiores_atrasos']->isEmpty())
                    <p class="text-muted text-center my-3">Nenhum atraso registrado.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($dados['maiores_atrasos'] as $atraso)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $atraso->aluno }}</strong><br>
                                    <small class="text-muted">
                                        {{ $atraso->turma ?? 'Sem turma' }} · {{ $atraso->dias_atraso }} {{ \Illuminate\Support\Str::plural('dia', $atraso->dias_atraso) }} de atraso
                                    </small>
                                </div>
                                <span class="badge badge-danger" style="font-size:.85rem">R$ {{ number_format($atraso->valor, 2, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $diferenca = $dados['realizado_ano'] - $dados['previsto_ano'];
    $realizacao = $dados['previsto_ano'] > 0 ? round($dados['realizado_ano'] / $dados['previsto_ano'] * 100, 1) : 0;
    $meses = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
@endphp
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-column mr-1"></i> Receita Prevista × Realizada — {{ $ano }}</h3>
        <div class="card-tools">
            <a href="{{ route('fluxo_caixa.index') }}" class="btn btn-tool">Ver fluxo de caixa</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row text-center mb-3">
            <div class="col-md-3 col-6 mb-2">
                <small class="text-muted d-block">Previsto no ano</small>
                <strong>R$ {{ number_format($dados['previsto_ano'], 2, ',', '.') }}</strong>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <small class="text-muted d-block">Realizado</small>
                <strong class="text-success">R$ {{ number_format($dados['realizado_ano'], 2, ',', '.') }}</strong>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <small class="text-muted d-block">Diferença</small>
                <strong class="{{ $diferenca < 0 ? 'text-danger' : 'text-success' }}">R$ {{ number_format($diferenca, 2, ',', '.') }}</strong>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <small class="text-muted d-block">Realização</small>
                <strong>{{ number_format($realizacao, 1, ',', '.') }}%</strong>
            </div>
        </div>
        <div style="height:320px">
            <canvas id="chartReceita"></canvas>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('chartReceita');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($meses),
            datasets: [
                {
                    label: 'Previsto',
                    backgroundColor: '#d9a441',
                    data: @json(array_column($dados['mensal'], 'previsto'))
                },
                {
                    label: 'Realizado',
                    backgroundColor: '#4e7a5b',
                    data: @json(array_column($dados['mensal'], 'realizado'))
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            legend: { position: 'bottom' },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function (v) {
                            return 'R$ ' + Number(v).toLocaleString('pt-BR', {maximumFractionDigits: 0});
                        }
                    }
                }],
                xAxes: [{ gridLines: { display: false } }]
            },
            tooltips: {
                callbacks: {
                    label: function (item, data) {
                        return data.datasets[item.datasetIndex].label + ': R$ ' +
                            Number(item.yLabel).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    });
});
</script>
@stop
