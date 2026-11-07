@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Nova Justificativa" subtitle="Atestado ou declaração que justifica/abona as faltas do aluno no período" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info small">
            <i class="fas fa-info-circle"></i>
            <strong>Falta justificada</strong>: a ausência continua registrada como justificada (direito a reposição, não derruba a frequência).
            <strong>Falta abonada</strong>: a ausência é anulada nos casos de amparo legal.
            As faltas já lançadas no período são convertidas automaticamente; deixe a disciplina em branco para valer em todas.
        </div>

        <form method="POST" action="{{ route('justificativa.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Turma <span class="text-danger">*</span></label>
                    <select id="sel-turma" name="turma_ref" class="form-control" required>
                        <option value="">Selecionar</option>
                        @foreach($turmas as $t)
                            <option value="{{ $t->id }}" {{ (string) old('turma_ref', request('turma_id')) === (string) $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Aluno <span class="text-danger">*</span></label>
                    <select name="matricula_id" id="sel-matricula" class="form-control" required>
                        <option value="">Selecione a turma primeiro</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <x-form-input name="data_inicio" type="date" label="Início do afastamento" :value="old('data_inicio')" required />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_fim" type="date" label="Fim do afastamento" :value="old('data_fim')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="tipo" label="Documento" :options="$tipos" :selected="old('tipo')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="efeito" label="Efeito nas faltas" :options="$efeitos" :selected="old('efeito', 'justificada')" required />
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-4">
                    <x-form-select name="disciplina_id" label="Disciplina (vazio = todas)" :options="$disciplinas ?? []" :selected="old('disciplina_id')" placeholder="Todas as disciplinas" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="arquivo" type="file" label="Anexo (PDF/imagem, opcional)" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="motivo" label="Motivo / observação" :value="old('motivo')" maxlength="1000" placeholder="Ex.: consulta médica, campeonato" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('justificativa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar e aplicar</button>
            </div>
        </form>
    </div>
</div>
@stop

@push('js')
<script>
    const matriculasPorTurma = @json($matriculasPorTurma);
    const disciplinasPorTurma = @json($disciplinasPorTurma ?? []);

    const selTurma = document.getElementById('sel-turma');
    const selMatricula = document.getElementById('sel-matricula');
    const selDisciplina = document.querySelector('select[name="disciplina_id"]');

    const matriculaAnterior = @json((string) old('matricula_id', ''));
    const disciplinaAnterior = @json((string) old('disciplina_id', ''));

    function preencherMatriculas() {
        const tid = selTurma.value;
        const itens = matriculasPorTurma[tid] || {};
        selMatricula.innerHTML = '<option value="">Selecionar</option>';
        Object.entries(itens).forEach(([id, nome]) => {
            selMatricula.insertAdjacentHTML('beforeend', `<option value="${id}">${nome}</option>`);
        });
        selMatricula.querySelector('option').textContent =
            Object.keys(itens).length ? 'Selecionar' : 'Nenhum aluno ativo nesta turma';
        selMatricula.value = matriculaAnterior;
    }

    function preencherDisciplinas() {
        if (! selDisciplina) return;
        const itens = disciplinasPorTurma[selTurma.value] || {};
        selDisciplina.innerHTML = '<option value="">Todas as disciplinas</option>';
        Object.entries(itens).forEach(([id, nome]) => {
            selDisciplina.insertAdjacentHTML('beforeend', `<option value="${id}">${nome}</option>`);
        });
        selDisciplina.value = disciplinaAnterior;
    }

    selTurma.addEventListener('change', () => { preencherMatriculas(); preencherDisciplinas(); });
    if (selTurma.value) { preencherMatriculas(); preencherDisciplinas(); }
</script>
@endpush
