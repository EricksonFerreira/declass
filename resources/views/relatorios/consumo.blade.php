@extends('adminlte::page')

@section('title', 'Consumo × Estimado')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Consumo × Estimado por Projeto'" />
@stop

@section('css')
<style>
.consumo-link-projeto { color: var(--empresa-cor-primaria, #007bff); }
.consumo-link-projeto:hover { color: var(--empresa-cor-primaria, #007bff); text-decoration: underline; filter: brightness(.8); }
</style>
@stop

@section('content')
@php
    $fmtHora = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $fmtPct  = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.').'%';
    $corPct  = fn ($v) => $v === null ? 'text-muted' : ($v > 100 ? 'text-danger' : 'text-success');
    $bgPct   = fn ($v) => $v === null ? '' : ($v > 100 ? 'bg-danger' : 'bg-success');
@endphp

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('relatorios.consumo') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            <input type="text" name="busca" class="form-control form-control-sm" placeholder="Buscar projeto..."
                   value="{{ $filtros['busca'] ?? '' }}" style="width:200px;">
            <select name="cliente_id" class="form-control form-control-sm">
                <option value="">Todos os clientes</option>
                @foreach ($clientes as $id => $nome)
                    <option value="{{ $id }}" {{ (string) ($filtros['cliente_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm">
                <option value="">Todos os status</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" {{ ($filtros['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="ano" class="form-control form-control-sm">
                <option value="">Todos os anos</option>
                @foreach ($anos as $ano)
                    <option value="{{ $ano }}" {{ (string) ($filtros['ano'] ?? '') === (string) $ano ? 'selected' : '' }}>{{ $ano }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
            <a href="{{ route('relatorios.consumo') }}" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm mb-0" style="font-size:.82rem;">
            <thead class="thead-light">
                <tr>
                    <th rowspan="2" class="align-middle pl-3">Projeto</th>
                    <th rowspan="2" class="align-middle">Cliente</th>
                    <th rowspan="2" class="align-middle">Status</th>
                    @foreach ($funcoes as $nome)
                        <th colspan="3" class="text-center border-left">{{ $nome }}</th>
                    @endforeach
                    <th colspan="3" class="text-center border-left bg-light font-weight-bold">Compilado</th>
                </tr>
                <tr>
                    @foreach ($funcoes as $nome)
                        <th class="text-right border-left" style="font-weight:600;">Consumida</th>
                        <th class="text-right" style="font-weight:600;">Estimada</th>
                        <th class="text-center" style="font-weight:600;">%</th>
                    @endforeach
                    <th class="text-right border-left" style="font-weight:600;">Consumida</th>
                    <th class="text-right" style="font-weight:600;">Estimada</th>
                    <th class="text-center" style="font-weight:600;">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($linhas as $linha)
                    @php
                        $p = $linha['projeto'];
                        $r = $linha['resumo'];
                        $rowClass = $p->status === 'cancelado' ? 'text-muted' : ($p->status === 'em_andamento' ? 'table-warning' : '');
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="pl-3">
                            <a href="{{ route('relatorios.consumo.show', $p->id) }}" class="font-weight-bold consumo-link-projeto">
                                {{ $p->nome }}
                            </a>
                        </td>
                        <td>{{ $p->cliente?->nome ?? '—' }}</td>
                        <td><span class="badge badge-{{ $p->status_cor }}">{{ $p->status_label }}</span></td>
                        @foreach ($funcoes as $fid => $nome)
                            @php $f = $r['por_funcao'][$fid] ?? null; @endphp
                            <td class="text-right border-left">{{ $fmtHora($f['consumida'] ?? null) }}</td>
                            <td class="text-right">{{ $fmtHora($f['estimada'] ?? null) }}</td>
                            <td class="text-center">
                                @if (($f['pct'] ?? null) !== null)
                                    <span class="badge {{ $bgPct($f['pct']) }}">{{ $fmtPct($f['pct']) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-right border-left font-weight-bold">{{ $fmtHora($r['consumida']) }}</td>
                        <td class="text-right font-weight-bold">{{ $fmtHora($r['estimada']) }}</td>
                        <td class="text-center">
                            @if ($r['pct'] !== null)
                                <span class="badge {{ $bgPct($r['pct']) }}">{{ $fmtPct($r['pct']) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + (count($funcoes) * 3) + 3 }}" class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                            Nenhum projeto encontrado com os filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer text-muted" style="font-size:.78rem;">
        <i class="fas fa-info-circle mr-1"></i>
        Horas <strong>consumidas</strong> são os lançamentos reais da equipe (rastreador/timesheet);
        <strong>estimadas</strong> são as horas previstas por função, configuradas no detalhe de cada projeto.
        Semáforo: <span class="badge bg-success">≤ 100%</span> dentro do estimado, <span class="badge bg-danger">&gt; 100%</span> estouro.
    </div>
</div>
@endsection
