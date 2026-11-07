@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Atestados e Abonos" subtitle="Justificativa e abono de faltas por período — aplica automaticamente nas chamadas" />
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
        <form method="GET" action="{{ route('justificativa.index') }}" class="form-inline">
            <input type="text" name="aluno" class="form-control form-control-sm mr-2" placeholder="Aluno"
                value="{{ $filtros['aluno'] ?? '' }}">
            <select name="escola_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todas as escolas</option>
                @foreach($escolas as $id => $nome)
                    <option value="{{ $id }}" {{ (string) ($filtros['escola_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
            <select name="turma_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todas as turmas</option>
                @foreach($turmas as $t)
                    <option value="{{ $t->id }}" {{ (string) ($filtros['turma_id'] ?? '') === (string) $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                @endforeach
            </select>
            <select name="efeito" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todos os efeitos</option>
                @foreach($efeitos as $k => $v)
                    <option value="{{ $k }}" {{ ($filtros['efeito'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa fa-search"></i></button>
        </form>
        @can('justificativa.create')
            <a href="{{ route('justificativa.create', ['turma_id' => $filtros['turma_id'] ?? null]) }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nova justificativa</a>
        @endcan
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Período</th>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Tipo</th>
                    <th>Efeito</th>
                    <th>Disciplina</th>
                    <th>Registrado por</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($justificativas as $j)
                    <tr>
                        <td class="text-nowrap">{{ $j->data_inicio->format('d/m/Y') }} — {{ $j->data_fim->format('d/m/Y') }}</td>
                        <td class="font-weight-bold">{{ $j->matricula->aluno->pessoa->nome ?? '—' }}</td>
                        <td>{{ $j->matricula->turma->nome ?? '—' }}</td>
                        <td>{{ $j->tipoLabel() }}
                            @if($j->motivo)
                                <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($j->motivo, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($j->efeito === 'abonada')
                                <span class="badge badge-success">Abonada</span>
                            @else
                                <span class="badge badge-info">Justificada</span>
                            @endif
                        </td>
                        <td>{{ $j->disciplina->nome ?? 'Todas' }}</td>
                        <td class="text-muted small">{{ $j->registradoPor->nome ?? '—' }}</td>
                        <td class="text-right text-nowrap">
                            @if($j->arquivo_path)
                                <a href="{{ route('justificativa.arquivo', $j->id) }}" class="btn btn-xs btn-secondary" title="Baixar documento"><i class="fa fa-paperclip"></i></a>
                            @endif
                            @can('justificativa.destroy')
                                <form method="POST" action="{{ route('justificativa.destroy', $j->id) }}" class="d-inline"
                                    onsubmit="return confirm('Excluir esta justificativa? As faltas do período voltarão a contar.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma justificativa registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($justificativas->hasPages())
        <div class="card-footer">{{ $justificativas->links() }}</div>
    @endif
</div>
@stop
