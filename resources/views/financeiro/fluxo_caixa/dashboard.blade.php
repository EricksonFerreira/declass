@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('financeiro.fluxo-caixa.dashboard') }}" class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="data_inicio" class="fc-label">Data início</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $dataInicio }}">
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="data_fim" class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $dataFim }}">
                    </div>
                    <div class="col-md-4 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1">
                            <i class="fas fa-sync-alt"></i> Atualizar relatório
                        </button>
                        <a href="{{ route('financeiro.fluxo-caixa.index') }}" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-list"></i> Lançamentos
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Receitas realizadas</span>
                        <strong class="text-success">{{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($receitasPagas, 2, ',', '.') }}</strong>
                    </div>
                    <small>Previsto pendente: {{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($receitasPendentes, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Despesas realizadas</span>
                        <strong class="text-danger">{{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($despesasPagas, 2, ',', '.') }}</strong>
                    </div>
                    <small>Previsto pendente: {{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($despesasPendentes, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--saldo h-100">
                    <div>
                        <span>Saldo realizado</span>
                        <strong class="{{ $saldoRealizado >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($saldoRealizado, 2, ',', '.') }}
                        </strong>
                    </div>
                    <small>Dinheiro em caixa</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Saldo projetado</span>
                        <strong class="{{ $saldoProjetado >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($saldoProjetado, 2, ',', '.') }}
                        </strong>
                    </div>
                    <small>Considera pendentes</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card fc-card h-100">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title"><i class="fas fa-chart-column"></i><span>Fluxo diário<span class="fc-subtitle">Receitas vs despesas por dia</span></span></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 320px;">
                            <canvas id="cashFlowChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card fc-card h-100">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title"><i class="fas fa-tags"></i><span>Maiores categorias<span class="fc-subtitle">Concentração financeira do período</span></span></h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table fc-table">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2">Categoria</th>
                                        <th class="py-2">Tipo</th>
                                        <th class="py-2 text-right px-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCategorias as $top)
                                        <tr>
                                            <td class="px-3 py-2 font-weight-bold">{{ $top->categoria }}</td>
                                            <td class="py-2">
                                                <span class="fc-badge {{ $top->tipo === 'receita' ? 'fc-badge--receita' : 'fc-badge--despesa' }}">
                                                    {{ $top->tipo === 'receita' ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>
                                            <td class="py-2 text-right px-3 font-weight-bold {{ $top->tipo === 'receita' ? 'text-success' : 'text-danger' }}">
                                                {{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($top->total, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted">Nenhum lançamento no período.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('cashFlowChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [
                        { label: 'Receitas', data: @json($chartReceitas), backgroundColor: '#059669', borderRadius: 6 },
                        { label: 'Despesas', data: @json($chartDespesas), backgroundColor: '#dc2626', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#edf2f7' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { boxWidth: 12, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        });
    </script>

    @include('financeiro.fluxo_caixa._styles')
@stop
