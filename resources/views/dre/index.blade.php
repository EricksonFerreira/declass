@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Receitas × despesas por competência — ano {{ $ano }}" />
@stop

@section('content')
@php
    $nomesMeses = [1 => 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    $fmt = fn ($v) => 'R$ '.number_format($v, 2, ',', '.');
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <form method="GET" action="{{ route('dre.index') }}" class="form-inline">
            <select name="ano" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a === $ano ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            @unless(app(\App\Support\EscolaContext::class)->filtrar())
            <select name="escola_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $id => $nome)
                    <option value="{{ $id }}" {{ (int) $escolaId === $id ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
            @endunless
        </form>
        <span class="text-muted small ml-auto">
            <i class="fas fa-info-circle"></i> Regime de competência: receitas por competência cobrada, despesas por vencimento.
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width:200px;">Conta</th>
                        @foreach($nomesMeses as $m => $nome)
                            <th class="text-right">{{ $nome }}</th>
                        @endforeach
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="font-weight-bold">
                        <td>Receita bruta de mensalidades</td>
                        @foreach(range(1, 12) as $m)
                            <td class="text-right">{{ $fmt($dados['bruta'][$m]) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($dados['bruta']['total']) }}</td>
                    </tr>
                    <tr>
                        <td class="pl-4 text-muted">(−) Bolsas e descontos</td>
                        @foreach(range(1, 12) as $m)
                            <td class="text-right text-danger">− {{ $fmt($dados['descontos'][$m]) }}</td>
                        @endforeach
                        <td class="text-right text-danger">− {{ $fmt($dados['descontos']['total']) }}</td>
                    </tr>
                    <tr>
                        <td class="pl-4 text-success">(+) Outras receitas (contas a receber)</td>
                        @foreach(range(1, 12) as $m)
                            <td class="text-right text-success">{{ $dados['outras'][$m] > 0 ? '+ '.$fmt($dados['outras'][$m]) : '—' }}</td>
                        @endforeach
                        <td class="text-right text-success">{{ $dados['outras']['total'] > 0 ? '+ '.$fmt($dados['outras']['total']) : '—' }}</td>
                    </tr>
                    <tr class="font-weight-bold bg-light">
                        <td>(=) Receita líquida</td>
                        @foreach(range(1, 12) as $m)
                            <td class="text-right">{{ $fmt($dados['liquida'][$m]) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($dados['liquida']['total']) }}</td>
                    </tr>
                    <tr><td colspan="14" class="py-1 border-0"></td></tr>
                    @forelse($dados['despesas'] as $categoria => $serie)
                        <tr>
                            <td class="pl-4">(−) {{ $categorias[$categoria] ?? ucfirst($categoria) }}</td>
                            @foreach(range(1, 12) as $m)
                                <td class="text-right text-danger">{{ $serie[$m] > 0 ? '− '.$fmt($serie[$m]) : '—' }}</td>
                            @endforeach
                            <td class="text-right text-danger font-weight-bold">− {{ $fmt($serie['total']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-3">Nenhuma despesa registrada no ano.</td>
                        </tr>
                    @endforelse
                    <tr class="font-weight-bold">
                        <td>(=) Despesas totais</td>
                        @foreach(range(1, 12) as $m)
                            <td class="text-right text-danger">{{ $fmt($dados['despesasTotais'][$m]) }}</td>
                        @endforeach
                        <td class="text-right text-danger">{{ $fmt($dados['despesasTotais']['total']) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="{{ $dados['resultado']['total'] >= 0 ? 'table-success' : 'table-danger' }} font-weight-bold" style="font-size:1rem;">
                        <td>(=) RESULTADO DO EXERCÍCIO</td>
                        @foreach(range(1, 12) as $m)
                            <td class="text-right">{{ $fmt($dados['resultado'][$m]) }}</td>
                        @endforeach
                        <td class="text-right">{{ $fmt($dados['resultado']['total']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop
