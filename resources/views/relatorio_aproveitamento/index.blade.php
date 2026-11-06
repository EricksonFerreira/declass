@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Médias, aprovação e cobertura de notas por turma" />
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-chalkboard"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Turmas</span>
                <span class="info-box-number">{{ $resumo['turmas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Alunos</span>
                <span class="info-box-number">{{ $resumo['alunos'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-star-half-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Média geral</span>
                <span class="info-box-number">{{ $resumo['media_geral'] !== null ? number_format($resumo['media_geral'], 1, ',', '') : '—' }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Aprovação (≥ {{ $notaAprovacao }})</span>
                <span class="info-box-number">{{ $resumo['pct_aprovacao'] !== null ? $resumo['pct_aprovacao'].'%' : '—' }}</span>
                <small>{{ $resumo['aprovados'] }} de {{ $resumo['alunos_com_media'] }} com média</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('relatorio_aproveitamento.index') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            <select name="escola_id" class="form-control form-control-sm">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}" {{ ($filtros['escola_id'] ?? '') == $escola->id ? 'selected' : '' }}>{{ $escola->nome }}</option>
                @endforeach
            </select>
            <select name="ano_letivo_id" class="form-control form-control-sm">
                <option value="">Todos os anos</option>
                @foreach($anosLetivos as $ano)
                    <option value="{{ $ano->id }}" {{ ($filtros['ano_letivo_id'] ?? '') == $ano->id ? 'selected' : '' }}>{{ $ano->ano }}</option>
                @endforeach
            </select>
            <select name="etapa_id" class="form-control form-control-sm">
                <option value="">Todas as etapas</option>
                @foreach($etapas as $etapa)
                    <option value="{{ $etapa->id }}" {{ ($filtros['etapa_id'] ?? '') == $etapa->id ? 'selected' : '' }}>{{ $etapa->nome }}</option>
                @endforeach
            </select>
            <select name="turma_id" class="form-control form-control-sm">
                <option value="">Todas as turmas</option>
                @foreach($turmas as $turma)
                    <option value="{{ $turma->id }}" {{ ($filtros['turma_id'] ?? '') == $turma->id ? 'selected' : '' }}>{{ $turma->nome }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
            <a href="{{ route('relatorio_aproveitamento.index') }}" class="btn btn-sm btn-secondary">Limpar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Escola</th>
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
                            <td><small>{{ $l['escola'] ?? '—' }}</small></td>
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
</div>
@stop
