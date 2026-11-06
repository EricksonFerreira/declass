@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Capacidade dos veículos versus passageiros por rota" />
@stop

@section('content')
@php
    $corSituacao = ['ok' => 'success', 'lotada' => 'warning', 'superlotada' => 'danger', 'sem_veiculo' => 'secondary', 'sem_capacidade' => 'dark'];
    $labelSituacao = ['ok' => 'Com vagas', 'lotada' => 'Lotada', 'superlotada' => 'Superlotada', 'sem_veiculo' => 'Sem veículo', 'sem_capacidade' => 'Sem capacidade'];
@endphp

<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-route"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Rotas</span>
                <span class="info-box-number">{{ $resumo['rotas'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-user-friends"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Passageiros</span>
                <span class="info-box-number">{{ $resumo['passageiros'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ocupação média</span>
                <span class="info-box-number">{{ $resumo['ocupacao_media'] !== null ? $resumo['ocupacao_media'].'%' : '—' }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Lotadas / Superlotadas</span>
                <span class="info-box-number">{{ $resumo['lotadas'] }} / {{ $resumo['superlotadas'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('relatorio_transporte.index') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            @unless(app(\App\Support\EscolaContext::class)->filtrar())
            <select name="escola_id" class="form-control form-control-sm">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}" {{ ($filtros['escola_id'] ?? '') == $escola->id ? 'selected' : '' }}>{{ $escola->nome }}</option>
                @endforeach
            </select>
            @endunless
            <select name="situacao" class="form-control form-control-sm">
                <option value="">Todas as situações</option>
                @foreach($situacoes as $val => $label)
                    <option value="{{ $val }}" {{ ($filtros['situacao'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-check">
                <input type="checkbox" name="apenas_ativas" value="1" class="form-check-input" id="apenas_ativas"
                    {{ !empty($filtros['apenas_ativas']) ? 'checked' : '' }}>
                <label class="form-check-label" for="apenas_ativas">Somente rotas ativas</label>
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
            <a href="{{ route('relatorio_transporte.index') }}" class="btn btn-sm btn-secondary">Limpar</a>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Escola</th>
                        <th>Rota</th>
                        <th>Veículo</th>
                        <th>Horários</th>
                        <th class="text-center">Paradas</th>
                        <th class="text-center">Passageiros</th>
                        <th class="text-center">Capacidade</th>
                        <th class="text-center">Ocupação</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($linhas as $l)
                        <tr>
                            <td><small>{{ $l['escola'] ?? '—' }}</small></td>
                            <td class="font-weight-bold">{{ $l['rota']->nome }}</td>
                            <td>
                                @if($l['veiculo'])
                                    {{ $l['veiculo']->modelo }}<br>
                                    <small class="text-muted">{{ $l['veiculo']->placa }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <small>
                                    Ida: {{ $l['rota']->horario_ida?->format('H:i') ?? '—' }}<br>
                                    Volta: {{ $l['rota']->horario_volta?->format('H:i') ?? '—' }}
                                </small>
                            </td>
                            <td class="text-center">{{ $l['paradas'] }}</td>
                            <td class="text-center font-weight-bold">{{ $l['passageiros'] }}</td>
                            <td class="text-center">{{ $l['capacidade'] ?? '—' }}</td>
                            <td class="text-center">
                                @if($l['pct'] !== null)
                                    <span class="badge badge-{{ $corSituacao[$l['situacao']] }}">{{ $l['pct'] }}%</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $corSituacao[$l['situacao']] }}">{{ $labelSituacao[$l['situacao']] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-route fa-2x mb-2 d-block"></i>
                                Nenhuma rota encontrada com os filtros informados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
