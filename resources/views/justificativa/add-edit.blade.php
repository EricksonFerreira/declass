@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Nova Justificativa" subtitle="Atestado ou declaração que justifica/abona as faltas do aluno no período" />
@stop

@push('css')
<style>
    .jf-sec { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1.1rem 0 .6rem; padding-bottom:.25rem; border-bottom:1px solid #e5e7eb; }
    .jf-sec:first-child { margin-top:0; }
    .jf-sec i { width:16px; text-align:center; margin-right:.15rem; }
    .jf-num { display:inline-flex; width:22px; height:22px; border-radius:50%; background:#2563eb; color:#fff; font-size:.72rem; font-weight:700; align-items:center; justify-content:center; margin-right:.35rem; }
    .jf-hint { font-size:.75rem; color:#9ca3af; margin-top:.25rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info small">
            <i class="fas fa-info-circle"></i>
            <strong>Falta justificada</strong>: a ausência continua registrada como justificada (direito a reposição, não derruba a frequência).
            <strong>Falta abonada</strong>: a ausência é anulada nos casos de amparo legal.
            As faltas já lançadas no período são convertidas automaticamente; deixe a disciplina em branco para valer em todas.
        </div>

        <form method="POST" action="{{ route('justificativa.store') }}" enctype="multipart/form-data" id="jfForm" novalidate>
            @csrf

            <div class="jf-sec"><i class="fa fa-school"></i> Localização do aluno</div>
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label"><span class="jf-num">1</span>Escola</label>
                    <select id="sel-escola" class="form-control">
                        <option value="">Todas as escolas</option>
                        @foreach($escolas as $id => $nome)
                            <option value="{{ $id }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                    <div class="jf-hint">Filtra as turmas disponíveis.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><span class="jf-num">2</span>Turma <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="jf_turma_display" class="form-control" placeholder="Buscar turma..." readonly tabindex="-1">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="btnBuscarTurma"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <input type="hidden" name="turma_ref" id="turma_ref" value="{{ old('turma_ref', request('turma_id')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><span class="jf-num">3</span>Aluno <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="jf_aluno_display" class="form-control" placeholder="Escolha a turma primeiro" readonly tabindex="-1">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="btnBuscarAluno"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                    <input type="hidden" name="matricula_id" id="matricula_id" value="{{ old('matricula_id') }}">
                </div>
            </div>

            <div class="jf-sec"><i class="fa fa-file-medical"></i> Documento e período</div>
            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="tipo" label="Documento" :options="$tipos" :selected="old('tipo')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="efeito" label="Efeito nas faltas" :options="$efeitos" :selected="old('efeito', 'justificada')" required />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_inicio" type="date" label="Início do afastamento" :value="old('data_inicio')" required />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_fim" type="date" label="Fim do afastamento" :value="old('data_fim')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="disciplina_id" label="Disciplina (vazio = todas)" :options="[]" :selected="old('disciplina_id')" placeholder="Todas as disciplinas" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="arquivo" type="file" label="Anexo (PDF/imagem, opcional)" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="motivo" label="Motivo / observação" :value="old('motivo')" maxlength="1000" placeholder="Ex.: consulta médica, campeonato" />
                </div>
            </div>

            <div class="mt-3 pt-3" style="border-top:1px solid #f0f3f8;">
                <a href="{{ route('justificativa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Salvar e aplicar</button>
            </div>
        </form>
    </div>
</div>

@include('partials.entity-picker')
@php
    $matriculasPorTurmaJson = $matriculasPicker;
@endphp
<script>
(function () {
    const TURMAS_TODAS = @json($turmasPicker ?? []);
    const MATRICULAS = @json($matriculasPorTurmaJson ?? []);
    const DISCIPLINAS = @json($disciplinasPorTurma ?? []);

    const selEscola = document.getElementById('sel-escola');
    const inputTurma = document.getElementById('turma_ref');
    const displayTurma = document.getElementById('jf_turma_display');
    const inputMatricula = document.getElementById('matricula_id');
    const displayAluno = document.getElementById('jf_aluno_display');
    const selDisciplina = document.querySelector('select[name="disciplina_id"]');
    const disciplinaAnterior = @json((string) old('disciplina_id', ''));

    function turmasDaEscola() {
        const eid = selEscola.value;
        return TURMAS_TODAS.filter(function (t) { return !eid || String(t.escola_id) === eid; });
    }

    function registrarTurma() {
        EntityPicker.registrar('turma', {
            titulo: 'Buscar turma',
            dados: turmasDaEscola(),
            filtros: [{ campo: 'escola_id', campoRotulo: 'escola', label: 'Escola' }],
            onSelect: function (d) { escolherTurma(d); },
        });
    }

    function registrarAluno(turmaId) {
        const itens = MATRICULAS[turmaId] || [];
        EntityPicker.registrar('aluno', {
            titulo: 'Buscar aluno da turma',
            dados: itens,
            filtros: [],
            onSelect: function (d) {
                inputMatricula.value = d.id;
                displayAluno.value = d.rotulo;
                displayAluno.classList.remove('is-invalid');
            },
        });
    }

    function preencherDisciplinas(turmaId) {
        if (!selDisciplina) { return; }
        const itens = DISCIPLINAS[turmaId] || {};
        selDisciplina.innerHTML = '<option value="">Todas as disciplinas</option>';
        Object.entries(itens).forEach(function (p) {
            const o = document.createElement('option');
            o.value = p[0]; o.textContent = p[1];
            selDisciplina.appendChild(o);
        });
        selDisciplina.value = disciplinaAnterior;
    }

    function escolherTurma(d) {
        inputTurma.value = d.id;
        displayTurma.value = d.rotulo;
        displayTurma.classList.remove('is-invalid');
        if (d.escola_id) { selEscola.value = d.escola_id; registrarTurma(); }
        inputMatricula.value = '';
        displayAluno.value = '';
        displayAluno.placeholder = 'Buscar aluno...';
        registrarAluno(d.id);
        preencherDisciplinas(d.id);
    }

    selEscola.addEventListener('change', function () {
        registrarTurma();
        if (inputTurma.value) {
            const atual = TURMAS_TODAS.find(function (t) { return String(t.id) === inputTurma.value; });
            if (atual && String(atual.escola_id) !== selEscola.value) {
                inputTurma.value = '';
                displayTurma.value = '';
                inputMatricula.value = '';
                displayAluno.value = '';
                displayAluno.placeholder = 'Escolha a turma primeiro';
            }
        }
    });

    registrarTurma();
    document.getElementById('btnBuscarTurma').addEventListener('click', function () { EntityPicker.abrir('turma'); });
    document.getElementById('btnBuscarAluno').addEventListener('click', function () {
        if (!inputTurma.value) {
            displayTurma.classList.add('is-invalid');
            EntityPicker.abrir('turma');
            return;
        }
        EntityPicker.abrir('aluno');
    });

    // Preselecao (?turma_id= ou old())
    if (inputTurma.value) {
        const t = TURMAS_TODAS.find(function (x) { return String(x.id) === String(inputTurma.value); });
        if (t) {
            escolherTurma(t);
            if (inputMatricula.value) {
                const m = (MATRICULAS[t.id] || []).find(function (x) { return String(x.id) === String(inputMatricula.value); });
                if (m) { displayAluno.value = m.rotulo; }
            }
        }
    }

    document.getElementById('jfForm').addEventListener('submit', function (e) {
        if (!inputTurma.value) {
            e.preventDefault();
            displayTurma.classList.add('is-invalid');
            EntityPicker.abrir('turma');
            return;
        }
        if (!inputMatricula.value) {
            e.preventDefault();
            displayAluno.classList.add('is-invalid');
            EntityPicker.abrir('aluno');
        }
    });
})();
</script>
@stop
