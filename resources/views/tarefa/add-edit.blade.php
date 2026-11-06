@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($tarefa) ? 'Editar Tarefa' : 'Nova Tarefa'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($tarefa) ? route('tarefa.update', $tarefa->id) : route('tarefa.store') }}">
            @csrf
            @if(isset($tarefa))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($tarefa))
                        <x-form-input name="turma_nome" label="Turma" :value="$tarefa->turma->nome ?? ''" disabled />
                    @else
                        <x-form-select name="turma_id" label="Turma" :options="['' => 'Selecionar'] + ($turmas ?? [])" :selected="old('turma_id')" required id="turma_id" />
                    @endif
                </div>
                <div class="col-md-4">
                    <x-form-select name="disciplina_id" label="Disciplina" :options="['' => 'Sem disciplina específica']" id="disciplina_id" :selected="old('disciplina_id', $tarefa->disciplina_id ?? '')" />
                </div>
                <div class="col-md-2">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $tarefa->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $tarefa->titulo ?? '')" required maxlength="200" placeholder="Ex.: Exercícios da página 42" />
                </div>
                <div class="col-md-2">
                    <x-form-input name="data_entrega" type="date" label="Data de entrega" :value="old('data_entrega', isset($tarefa) ? $tarefa->data_entrega->format('Y-m-d') : '')" required />
                </div>
                <div class="col-md-2">
                    <x-form-input name="nota_maxima" type="number" label="Nota máx." :value="old('nota_maxima', $tarefa->nota_maxima ?? '')" min="0" max="100" step="0.5" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3" maxlength="4000"
                        placeholder="O que deve ser feito, formato de entrega, materiais necessários.">{{ old('descricao', $tarefa->descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Instruções</label>
                    <textarea name="instrucoes" class="form-control" rows="2" maxlength="4000"
                        placeholder="Passo a passo, links, observações para os alunos.">{{ old('instrucoes', $tarefa->instrucoes ?? '') }}</textarea>
                </div>
            </div>

            @if(!isset($tarefa))
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" name="gerar_entregas" value="1" class="form-check-input" id="gerar_entregas" {{ old('gerar_entregas') ? 'checked' : 'checked' }}>
                            <label class="form-check-label" for="gerar_entregas">
                                Criar controle de entregas para todos os alunos da turma
                                <small class="text-muted">(permite lançar status e notas por aluno)</small>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-3">
                <a href="{{ route('tarefa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const disciplinasPorTurma = {!! $disciplinasPorTurma ?? '{}' !!};
    const selTurma = document.getElementById('turma_id');
    const selDisc = document.getElementById('disciplina_id');
    const oldDisc = "{{ old('disciplina_id', $tarefa->disciplina_id ?? '') }}";

    function preencherDisciplinas() {
        const lista = disciplinasPorTurma[selTurma ? selTurma.value : ''] || {};
        selDisc.innerHTML = '<option value="">Sem disciplina específica</option>';
        Object.entries(lista).forEach(([id, nome]) => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = nome;
            if (String(id) === oldDisc) opt.selected = true;
            selDisc.appendChild(opt);
        });
    }

    if (selTurma) {
        selTurma.addEventListener('change', preencherDisciplinas);
        preencherDisciplinas();
    }
})();
</script>
@stop
