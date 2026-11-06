@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="($ano_letivo->ano ?? 'Ano letivo').' — '.($turma->nome ?? 'Turma')" subtitle="Histórico escolar do aluno" />
@stop

@section('content')
@php
    $p = $aluno->pessoa;
    $statusMatricula = \App\Models\Matricula::STATUS;
    $statusBadge = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'];
    $fmtPct = fn ($v) => $v === null ? '—' : number_format($v, 1, ',', '.').'%';
    $cellClass = fn ($v) => $v === null ? 'text-muted' : ($v >= 75 ? 'text-success font-weight-bold' : ($v >= 50 ? 'text-warning font-weight-bold' : 'text-danger font-weight-bold'));
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Aluno</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nome</dt><dd class="col-sm-7">{{ $p->nome ?? $aluno->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">RA</dt><dd class="col-sm-7">{{ $aluno->registro_academico ?? '—' }}</dd>
                    <dt class="col-sm-5">Nascimento</dt><dd class="col-sm-7">{{ optional($p->data_nascimento)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Escola</dt><dd class="col-sm-7">{{ $escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Matrícula</dt><dd class="col-sm-7">{{ optional($matricula->data_matricula)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Situação</dt>
                    <dd class="col-sm-7"><span class="badge badge-{{ $statusBadge[$matricula->status] ?? 'secondary' }}">{{ $statusMatricula[$matricula->status] ?? $matricula->status }}</span></dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Turma</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Turma</dt><dd class="col-sm-7">{{ $turma->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Etapa</dt><dd class="col-sm-7">{{ $etapa->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Turno</dt><dd class="col-sm-7">{{ \App\Models\Turma::TURNOS[$turma->turno] ?? $turma->turno ?? '—' }}</dd>
                    <dt class="col-sm-5">Chamada</dt><dd class="col-sm-7">{{ $matricula->numero_chamada ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Frequência no ano</div>
            <div class="card-body">
                @if($frequencia['total'] === 0)
                    <p class="text-muted mb-0">Sem registros de frequência neste ano.</p>
                @else
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Presenças</dt><dd class="col-sm-6 text-success">{{ $frequencia['presente'] }}</dd>
                        <dt class="col-sm-6">Faltas just.</dt><dd class="col-sm-6">{{ $frequencia['justificada'] }}</dd>
                        <dt class="col-sm-6">Faltas</dt><dd class="col-sm-6 text-danger">{{ $frequencia['falta'] }}</dd>
                        <dt class="col-sm-6">Total</dt><dd class="col-sm-6">{{ $frequencia['total'] }}</dd>
                        <dt class="col-sm-6">Frequência</dt>
                        <dd class="col-sm-6">
                            @if($frequencia['pct'] !== null)
                                <span class="badge badge-{{ $frequencia['pct'] >= 75 ? 'success' : ($frequencia['pct'] >= 50 ? 'warning' : 'danger') }}">{{ $frequencia['pct'] }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>
                    </dl>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">Grade de notas — {{ $ano_letivo->ano ?? 'Ano letivo' }}</span>
                <div>
                    <a href="{{ route('aluno.historico.escolar.pdf', ['aluno' => $aluno->id, 'matricula' => $matricula->id]) }}" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fa fa-file-pdf"></i> PDF deste ano
                    </a>
                    <a href="{{ route('aluno.historico', $aluno->id) }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Histórico</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="2" style="min-width:180px;">Disciplina</th>
                                <th rowspan="2" class="text-center" style="width:70px;">CH/sem</th>
                                @foreach($periodos as $periodo)
                                    <th class="text-center">{{ $periodo->nome }}</th>
                                @endforeach
                                @if($tem_sem_periodo)
                                    <th class="text-center text-muted">Sem período</th>
                                @endif
                                <th rowspan="2" class="text-center" style="width:90px;">Média final</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($linhas as $linha)
                                <tr>
                                    <td class="font-weight-bold">{{ $linha['disciplina']->nome }}</td>
                                    <td class="text-center">{{ $linha['carga_horaria'] ?? '—' }}</td>
                                    @foreach($periodos as $periodo)
                                        <td class="text-center {{ $cellClass($linha['medias'][$periodo->id] ?? null) }}">
                                            {{ $fmtPct($linha['medias'][$periodo->id] ?? null) }}
                                        </td>
                                    @endforeach
                                    @if($tem_sem_periodo)
                                        <td class="text-center {{ $cellClass($linha['medias']['__sem_periodo__'] ?? null) }}">
                                            {{ $fmtPct($linha['medias']['__sem_periodo__'] ?? null) }}
                                        </td>
                                    @endif
                                    <td class="text-center font-weight-bold {{ $cellClass($linha['media_geral']) }}">
                                        {{ $fmtPct($linha['media_geral']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + $periodos->count() + ($tem_sem_periodo ? 1 : 0) }}" class="text-center text-muted py-4">
                                        Nenhuma disciplina ofertada nesta turma.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted small">
                    Médias calculadas sobre as avaliações lançadas (nota convertida para % da nota máxima). Células em branco = disciplina sem avaliação no período.
                </div>
            </div>
        </div>
    </div>
</div>
@stop
