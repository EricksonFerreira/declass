@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    @php
        $prefixoMoeda = $moeda === 'BRL' ? 'R$' : $moeda;
        $receitasPendentes = (float) ($pendentes->receitas ?? 0);
        $despesasPendentes = (float) ($pendentes->despesas ?? 0);
    @endphp

    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('financeiro.analise-veiculos') }}" class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="data_inicio" class="fc-label">Data inicio</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $dataInicio }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="data_fim" class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $dataFim }}">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label for="veiculo_id" class="fc-label">Veiculo</label>
                        <select name="veiculo_id" id="veiculo_id" class="form-control">
                            <option value="">Todos os veiculos</option>
                            @foreach($veiculos as $veiculo)
                                <option value="{{ $veiculo->id }}" {{ (int) $veiculoId === (int) $veiculo->id ? 'selected' : '' }}>
                                    {{ $veiculo->nome }} @if($veiculo->placa_licenca) - {{ $veiculo->placa_licenca }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-8 mb-2">
                        <label for="busca" class="fc-label">Busca</label>
                        <input type="text" name="busca" id="busca" class="form-control" value="{{ $busca }}" placeholder="Placa, veiculo ou descricao">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('financeiro.analise-veiculos') }}" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Receitas por frota</span>
                        <strong class="text-success">{{ $prefixoMoeda }} {{ number_format($receitas, 2, ',', '.') }}</strong>
                    </div>
                    <small>Pendentes: {{ $prefixoMoeda }} {{ number_format($receitasPendentes, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Despesas por frota</span>
                        <strong class="text-danger">{{ $prefixoMoeda }} {{ number_format($despesas, 2, ',', '.') }}</strong>
                    </div>
                    <small>Pendentes: {{ $prefixoMoeda }} {{ number_format($despesasPendentes, 2, ',', '.') }}</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--saldo h-100">
                    <div>
                        <span>Resultado realizado</span>
                        <strong class="{{ $resultado >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $prefixoMoeda }} {{ number_format($resultado, 2, ',', '.') }}
                        </strong>
                    </div>
                    <small>Receitas menos despesas pagas</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Margem operacional</span>
                        <strong class="{{ $margem >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($margem, 1, ',', '.') }}%
                        </strong>
                    </div>
                    <small>Baseada nas receitas realizadas</small>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-car-side"></i>
                        <span>DRE por carro<span class="fc-subtitle">Lucratividade por placa no periodo filtrado</span></span>
                    </h5>
                    <a href="{{ route('financeiro.fluxo-caixa.index', request()->query()) }}" class="btn btn-outline-secondary fc-btn">
                        <i class="fas fa-list"></i> Ver lançamentos
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table fc-table">
                    <thead>
                        <tr>
                            <th class="px-3">Veiculo</th>
                            <th>Placa</th>
                            <th class="text-right">Receitas</th>
                            <th class="text-right">Despesas</th>
                            <th class="text-right">Resultado</th>
                            <th class="text-right">Margem</th>
                            <th class="text-center">Lancamentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analise as $item)
                            @php
                                $receitaItem = (float) $item->receitas;
                                $despesaItem = (float) $item->despesas;
                                $resultadoItem = (float) $item->resultado;
                                $margemItem = $receitaItem > 0 ? ($resultadoItem / $receitaItem) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="px-3 font-weight-bold">
                                    {{ $item->veiculo_nome }}
                                    <small class="d-block text-muted">#{{ $item->veiculo_id }}</small>
                                </td>
                                <td>
                                    <span class="fc-badge bg-success-light">
                                        <i class="fas fa-id-card"></i>{{ $item->placa ?: '-' }}
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    {{ $prefixoMoeda }} {{ number_format($receitaItem, 2, ',', '.') }}
                                </td>
                                <td class="text-right font-weight-bold text-danger">
                                    {{ $prefixoMoeda }} {{ number_format($despesaItem, 2, ',', '.') }}
                                </td>
                                <td class="text-right font-weight-bold {{ $resultadoItem >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $prefixoMoeda }} {{ number_format($resultadoItem, 2, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    <span class="fc-badge {{ $resultadoItem >= 0 ? 'fc-badge--receita' : 'fc-badge--despesa' }}">
                                        {{ number_format($margemItem, 1, ',', '.') }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ (int) $item->quantidade_lancamentos }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-chart-pie d-block mb-3" style="font-size: 2.4rem; opacity: .18;"></i>
                                    Nenhum lançamento com veiculo vinculado nesse periodo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                {{ $analise->links() }}
            </div>
        </div>
    </div>
@stop
