@extends('adminlte::page')

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

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('relatorio_frequencia.index') }}" class="form-inline flex-wrap" style="gap:.5rem;">
            @unless(app(\App\Support\EscolaContext::class)->filtrar())
            <select name="escola_id" class="form-control form-control-sm">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $escola)
                    <option value="{{ $escola->id }}" {{ ($filtros['escola_id'] ?? '') == $escola->id ? 'selected' : '' }}>{{ $escola->nome }}</option>
                @endforeach
            </select>
            @endunless
            <select name="ano_letivo_id" class="form-control form-control-sm">
                <option value="">Todos os anos letivos</option>
                @foreach($anosLetivos as $ano)
                    <option value="{{ $ano->id }}" {{ ($filtros['ano_letivo_id'] ?? '') == $ano->id ? 'selected' : '' }}>Ano {{ $ano->ano }}</option>
                @endforeach
            </select>
            <select name="turma_id" class="form-control form-control-sm">
                <option value="">Todas as turmas</option>
                @foreach($turmas as $turma)
                    <option value="{{ $turma->id }}" {{ ($filtros['turma_id'] ?? '') == $turma->id ? 'selected' : '' }}>
                        {{ $turma->nome }} — {{ $turma->escola->nome ?? '' }}
                    </option>
                @endforeach
            </select>
            <div class="form-check">
                <input type="checkbox" name="apenas_risco" value="1" class="form-check-input" id="apenas_risco"
                    {{ !empty($filtros['apenas_risco']) ? 'checked' : '' }}>
                <label class="form-check-label" for="apenas_risco">Somente em risco (&lt;75%)</label>
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
            <a href="{{ route('relatorio_frequencia.index') }}" class="btn btn-sm btn-secondary">Limpar</a>
        </form>
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
