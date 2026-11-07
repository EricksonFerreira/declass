@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($turma) ? 'Editar Turma' : 'Nova Turma'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($turma) ? route('turma.update', $turma->id) : route('turma.store') }}">
            @csrf
            @if(isset($turma))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome da turma" :value="old('nome', $turma->nome ?? '')" required maxlength="120" placeholder="Ex.: 1º Ano A" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $turma->escola_id ?? '')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ano_letivo_id" label="Ano letivo" :options="$anosLetivos ?? []" :selected="old('ano_letivo_id', $turma->ano_letivo_id ?? '')" placeholder="Selecionar" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="etapa_id" label="Etapa" :options="$etapas ?? []" :selected="old('etapa_id', $turma->etapa_id ?? '')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="turno" label="Turno" :options="$turnos ?? []" :selected="old('turno', $turma->turno ?? '')" placeholder="Selecionar" hint="Filtra os horários disponíveis abaixo." />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Grade de horário</label>
                    <select name="horario_id" id="select-horario" class="form-control">
                        <option value="">Padrão do turno (matriz legada)</option>
                    </select>
                    <small class="text-muted">Vincula a turma a um horário específico da escola. Cadastre horários em Estrutura Acadêmica → Matriz de Horários.</small>
                    @error('horario_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <x-form-input name="sala" label="Sala" :value="old('sala', $turma->sala ?? '')" maxlength="40" placeholder="101" />
                </div>
                <div class="col-md-2">
                    <x-form-input name="capacidade_maxima" type="number" label="Capacidade" :value="old('capacidade_maxima', $turma->capacidade_maxima ?? '')" min="1" max="500" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $turma->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('turma.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
(function () {
    var horarios = @json($horariosJson ?? []);
    var escolaSel = document.querySelector('[name="escola_id"]');
    var turnoSel = document.querySelector('[name="turno"]');
    var horarioSel = document.getElementById('select-horario');
    var atual = '{{ old('horario_id', $turma->horario_id ?? '') }}';

    function preencher() {
        var escolaId = parseInt(escolaSel.value, 10);
        var turno = turnoSel.value;
        horarioSel.querySelectorAll('option:not(:first-child)').forEach(function (o) { o.remove(); });

        horarios.filter(function (h) {
            return h.escola_id === escolaId && (!turno || h.turno === turno);
        }).forEach(function (h) {
            var o = document.createElement('option');
            o.value = h.id; o.textContent = h.label;
            if (String(h.id) === String(atual)) o.selected = true;
            horarioSel.appendChild(o);
        });
    }

    escolaSel.addEventListener('change', preencher);
    turnoSel.addEventListener('change', preencher);
    preencher();
})();
</script>
@stop
