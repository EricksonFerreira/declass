@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Entregas — '.$tarefa->titulo" :subtitle="$tarefa->turma->nome.' · entrega até '.$tarefa->data_entrega->format('d/m/Y')" />
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('tarefa.entregas.salvar', $tarefa->id) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Aluno</th>
                            <th style="width:140px;">Status</th>
                            <th style="width:110px;">Nota</th>
                            <th style="width:150px;">Data entrega</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $matricula)
                            @php($e = $entregas->get($matricula->id))
                            <tr>
                                <td>{{ $matricula->numero_chamada ?? '—' }}</td>
                                <td class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? 'Aluno #'.$matricula->aluno_id }}</td>
                                <td>
                                    <select name="entregas[{{ $matricula->id }}][status]" class="form-control form-control-sm">
                                        @foreach(\App\Models\Tarefa::STATUS_ENTREGA as $st)
                                            <option value="{{ $st }}" {{ ($e->status ?? 'pendente') === $st ? 'selected' : '' }}>
                                                {{ \App\Models\EntregaTarefa::STATUS[$st] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="entregas[{{ $matricula->id }}][nota]" class="form-control form-control-sm"
                                        step="0.5" min="0" max="{{ $tarefa->nota_maxima ?? 100 }}"
                                        value="{{ $e->nota ?? '' }}" {{ ($e->status ?? '') !== 'avaliada' ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="date" name="entregas[{{ $matricula->id }}][data_entrega]" class="form-control form-control-sm"
                                        value="{{ $e?->data_entrega?->format('Y-m-d') ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="entregas[{{ $matricula->id }}][observacoes]" class="form-control form-control-sm"
                                        maxlength="1000" value="{{ $e->observacoes ?? '' }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Turma sem alunos matriculados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($matriculas->isNotEmpty())
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Salvar entregas</button>
            @endif
            <a href="{{ route('tarefa.index') }}" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</div>

<script>
// Habilita campo de nota apenas quando status = avaliada
document.querySelectorAll('select[name^="entregas"]').forEach(sel => {
    const row = sel.closest('tr');
    const nota = row.querySelector('input[name$="[nota]"]');
    sel.addEventListener('change', () => {
        nota.disabled = sel.value !== 'avaliada';
        if (sel.value !== 'avaliada') nota.value = '';
    });
    sel.dispatchEvent(new Event('change'));
});
</script>
@stop
