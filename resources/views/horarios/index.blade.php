@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Grades de Horário" subtitle="Todas as turmas da escola — exportar ou editar" />
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">Turmas ({{ $turmas->count() }})</span>
                <form method="GET" action="{{ route('horarios.index') }}" class="form-inline">
                    <select name="ano_letivo_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">Todos os anos letivos</option>
                        @foreach($anosLetivos as $ano)
                            <option value="{{ $ano->id }}" {{ $anoLetivoId == $ano->id ? 'selected' : '' }}>{{ $ano->ano }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Turma</th>
                            <th>Escola</th>
                            <th>Ano</th>
                            <th>Turno</th>
                            <th class="text-center">Aulas</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turmas as $turma)
                            <tr>
                                <td class="font-weight-bold">{{ $turma->nome }}</td>
                                <td>{{ $turma->escola->nome ?? '—' }}</td>
                                <td>{{ $turma->anoLetivo->ano ?? '—' }}</td>
                                <td>{{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '—' }}</td>
                                <td class="text-center">
                                    @if(!$turma->tem_matriz)
                                        <span class="badge badge-warning" title="Matriz de horários não configurada para este turno">sem matriz</span>
                                    @elseif($turma->aulas_count > 0)
                                        <span class="badge badge-success">{{ $turma->aulas_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @can('turma.grade')
                                        <a href="{{ route('turma.grade', $turma->id) }}" class="btn btn-xs btn-info" title="Abrir grade"><i class="fa fa-calendar-week"></i></a>
                                    @endcan
                                    @can('turma.grade.pdf')
                                        <a href="{{ route('turma.grade.pdf', [$turma->id, 'exibir' => 'disciplina']) }}" class="btn btn-xs btn-outline-danger" title="PDF da grade"><i class="fa fa-file-pdf"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma turma encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Exportar todas as grades</div>
            <div class="card-body">
                <p class="small text-muted">Gera um arquivo com a grade de <strong>todas as turmas</strong> listadas ao lado.</p>
                <form method="GET" action="{{ route('horarios.exportar') }}">
                    @if($anoLetivoId)<input type="hidden" name="ano_letivo_id" value="{{ $anoLetivoId }}">@endif
                    <div class="form-group">
                        <label class="font-weight-bold">Conteúdo das células</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exibir" value="disciplina" id="ex-d" checked>
                            <label class="form-check-label" for="ex-d">Somente disciplina <small class="text-muted">(padrão)</small></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exibir" value="professor" id="ex-p">
                            <label class="form-check-label" for="ex-p">Disciplina + professor</label>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold">Formato</label><br>
                        <button type="submit" name="formato" value="xlsx" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel"></i> Excel (.xlsx — uma aba por turma)
                        </button>
                        <button type="submit" name="formato" value="pdf" class="btn btn-danger btn-sm">
                            <i class="fa fa-file-pdf"></i> PDF (todas as turmas)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Matriz de horários</div>
            <div class="card-body">
                <p class="small text-muted">Antes de montar as grades, configure as faixas de horário (aulas por dia) de cada turno.</p>
                @can('horario_matriz.index')
                    <a href="{{ route('horario_matriz.index') }}" class="btn btn-outline-primary btn-sm btn-block">
                        <i class="fa fa-cog"></i> Configurar matrizes
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@stop
