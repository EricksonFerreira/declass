@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Médias por disciplina e frequência" />
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header font-weight-bold">Boletim por turma</div>
            <div class="card-body">
                <form method="GET" action="{{ route('boletim.turma', ['turma' => '__ID__']) }}" id="formTurma">
                    <div class="form-group">
                        <label class="form-label">Selecionar turma</label>
                        <select id="selTurma" class="form-control" required>
                            <option value="">Escolher turma...</option>
                            @foreach($turmas as $t)
                                <option value="{{ $t->id }}">{{ $t->nome }} — {{ $t->escola->nome ?? '' }} ({{ $t->anoLetivo->ano ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary btn-block" id="btnTurma">Ver boletim da turma</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header font-weight-bold">Boletim individual</div>
            <div class="card-body">
                <form method="GET" action="{{ route('boletim.aluno', ['aluno' => '__ID__']) }}" id="formAluno">
                    <div class="form-group">
                        <label class="form-label">Selecionar aluno</label>
                        <select id="selAluno" class="form-control" required>
                            <option value="">Escolher aluno...</option>
                            @foreach($alunos as $a)
                                <option value="{{ $a->id }}">{{ $a->pessoa->nome ?? 'Aluno #'.$a->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary btn-block" id="btnAluno">Ver boletim do aluno</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.getElementById('btnTurma').addEventListener('click', function(e) {
    e.preventDefault();
    var id = document.getElementById('selTurma').value;
    if (id) window.location.href = "{{ route('boletim.turma', ['turma' => '__ID__']) }}".replace('__ID__', id);
});
document.getElementById('btnAluno').addEventListener('click', function(e) {
    e.preventDefault();
    var id = document.getElementById('selAluno').value;
    if (id) window.location.href = "{{ route('boletim.aluno', ['aluno' => '__ID__']) }}".replace('__ID__', id);
});
</script>
@endpush
@stop
