@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Realizado × previsto por mês — ano {{ $ano }}" />
@stop

@section('content')
@php
    $nomesMeses = [1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $mesAtual = now()->month;
@endphp

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>R$ {{ number_format($totais['rec_realizado'], 0, ',', '.') }}</h3>
                <p>Receitas recebidas</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-down"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>R$ {{ number_format($totais['rec_previsto'], 0, ',', '.') }}</h3>
                <p>A receber (pendente)</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>R$ {{ number_format($totais['des_realizado'], 0, ',', '.') }}</h3>
                <p>Despesas pagas</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-up"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="small-box {{ $totais['saldo_projetado'] >= 0 ? 'bg-success' : 'bg-dark' }}">
            <div class="inner">
                <h3>R$ {{ number_format($totais['saldo_projetado'], 0, ',', '.') }}</h3>
                <p>Saldo projetado do ano</p>
            </div>
            <div class="icon"><i class="fas fa-balance-scale"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <form method="GET" action="{{ route('fluxo_caixa.index') }}" class="form-inline">
            <select name="ano" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a === $ano ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            <select name="escola_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $id => $nome)
                    <option value="{{ $id }}" {{ (int) $escolaId === $id ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
        </form>
        <span class="text-muted small ml-auto">
            <i class="fas fa-info-circle"></i> Realizado = por data de pagamento · Previsto = por vencimento
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Mês</th>
                        <th class="text-right">Receitas recebidas</th>
                        <th class="text-right">A receber</th>
                        <th class="text-right">Despesas pagas</th>
                        <th class="text-right">A pagar</th>
                        <th class="text-right">Saldo realizado</th>
                        <th class="text-right">Saldo projetado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meses as $mes => $linha)
                        <tr class="{{ $mes === $mesAtual && $ano === now()->year ? 'table-info' : '' }}">
                            <td class="font-weight-bold">
                                {{ $nomesMeses[$mes] }}
                                @if($mes === $mesAtual && $ano === now()->year)<span class="badge badge-info ml-1">atual</span>@endif
                            </td>
                            <td class="text-right text-success">R$ {{ number_format($linha['rec_realizado'], 2, ',', '.') }}</td>
                            <td class="text-right text-warning">R$ {{ number_format($linha['rec_previsto'], 2, ',', '.') }}</td>
                            <td class="text-right text-danger">R$ {{ number_format($linha['des_realizado'], 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($linha['des_previsto'], 2, ',', '.') }}</td>
                            <td class="text-right font-weight-bold {{ $linha['saldo'] >= 0 ? 'text-success' : 'text-danger' }}">
                                R$ {{ number_format($linha['saldo'], 2, ',', '.') }}
                            </td>
                            <td class="text-right font-weight-bold {{ $linha['saldo_projetado'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                R$ {{ number_format($linha['saldo_projetado'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="thead-light">
                    <tr>
                        <th>Total {{ $ano }}</th>
                        <th class="text-right text-success">R$ {{ number_format($totais['rec_realizado'], 2, ',', '.') }}</th>
                        <th class="text-right text-warning">R$ {{ number_format($totais['rec_previsto'], 2, ',', '.') }}</th>
                        <th class="text-right text-danger">R$ {{ number_format($totais['des_realizado'], 2, ',', '.') }}</th>
                        <th class="text-right">R$ {{ number_format($totais['des_previsto'], 2, ',', '.') }}</th>
                        <th class="text-right {{ $totais['saldo'] >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($totais['saldo'], 2, ',', '.') }}</th>
                        <th class="text-right {{ $totais['saldo_projetado'] >= 0 ? 'text-primary' : 'text-danger' }}">R$ {{ number_format($totais['saldo_projetado'], 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop
