@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Chamada — '.$diario->data?->format('d/m/Y')" :subtitle="$diario->turma->nome.' · '.$diario->disciplina->nome.' · '.$diario->professor->nome" />
@stop

@section('content')
<div class="card">
    <div class="card-header font-weight-bold">
        Alunos matriculados ({{ $matriculas->count() }})
        @if($diario->conteudo)
            <small class="text-muted d-block font-weight-normal">{{ \Illuminate\Support\Str::limit($diario->conteudo, 120) }}</small>
        @endif
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('diario.frequencia.salvar', $diario->id) }}">
            @csrf
            @method('PUT')
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px;">Nº</th>
                            <th>Aluno</th>
                            <th style="width:200px;">Status</th>
                            <th>Justificativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $matricula)
                            @php $reg = $registradas->get($matricula->id); @endphp
                            <tr>
                                <td class="text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                                <td class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</td>
                                <td>
                                    <select name="frequencias[{{ $loop->index }}][status]" class="form-control form-control-sm">
                                        @foreach($statusOpcoes as $sk => $sl)
                                            <option value="{{ $sk }}" @selected(old("frequencias.$loop->index.status", $reg->status ?? 'presente') === $sk)>{{ $sl }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="frequencias[{{ $loop->index }}][matricula_id]" value="{{ $matricula->id }}">
                                </td>
                                <td>
                                    <input type="text" name="frequencias[{{ $loop->index }}][justificativa]" class="form-control form-control-sm" maxlength="2000"
                                        placeholder="Obrigatória se falta justificada" value="{{ old("frequencias.$loop->index.justificativa", $reg->justificativa ?? '') }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum aluno ativo nesta turma.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($matriculas->isNotEmpty())
                <div class="card-footer d-flex" style="gap:.5rem;">
                    <button type="submit" class="btn btn-primary">Salvar chamada</button>
                    <a href="{{ route('diario.index') }}" class="btn btn-secondary">Voltar</a>
                </div>
            @endif
        </form>
    </div>
</div>
@stop
