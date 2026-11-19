@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($turma) ? 'Editar Turma' : 'Nova Turma'" />
@stop

@push('css')
<style>
    .tf-card {
        border: 1px solid #e5e7f0;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(15,23,42,.06);
    }
    .tf-sec {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        margin: 1.1rem 0 .7rem;
        padding-bottom: .4rem;
        border-bottom: 1px solid #f1f3f9;
    }
    .tf-sec:first-child { margin-top: 0; }
    .tf-sec i { color: #007bff; }
    .tf-dica {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f9;
        align-items: flex-start;
    }
    .tf-dica:last-child { border-bottom: 0; padding-bottom: 0; }
    .tf-dica i {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        flex-shrink: 0;
        color: #fff;
    }
    .tf-dica b { font-size: .8rem; color: #0f172a; display: block; }
    .tf-dica span { font-size: .74rem; color: #94a3b8; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ isset($turma) ? route('turma.update', $turma->id) : route('turma.store') }}">
    @csrf
    @if(isset($turma))
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card tf-card">
                <div class="card-body">
                    <div class="tf-sec"><i class="fa fa-graduation-cap mr-1"></i> Estrutura acadêmica</div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-form-input name="nome" label="Nome da turma" :value="old('nome', $turma->nome ?? '')" required maxlength="120" placeholder="Ex.: 1º Ano A" />
                        </div>
                        <div class="col-md-6">
                            <x-form-select name="etapa_id" label="Etapa" :options="$etapas ?? []" :selected="old('etapa_id', $turma->etapa_id ?? '')" placeholder="Selecionar" required />
                        </div>
                        <div class="col-md-6">
                            <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $turma->escola_id ?? '')" placeholder="Selecionar" required />
                        </div>
                        <div class="col-md-6">
                            <x-form-select name="ano_letivo_id" label="Ano letivo" :options="$anosLetivos ?? []" :selected="old('ano_letivo_id', $turma->ano_letivo_id ?? '')" placeholder="Selecionar" required />
                        </div>
                    </div>

                    <div class="tf-sec"><i class="fa fa-clock mr-1"></i> Horário</div>
                    <div class="row">
                        <div class="col-md-5">
                            <x-form-select name="turno" label="Turno" :options="$turnos ?? []" :selected="old('turno', $turma->turno ?? '')" placeholder="Selecionar" hint="Filtra os horários disponíveis." />
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Grade de horário</label>
                            <select name="horario_id" id="select-horario" class="form-control">
                                <option value="">Padrão do turno (matriz legada)</option>
                            </select>
                            <small class="text-muted">Opcional — vincula a turma a um horário da escola.</small>
                            @error('horario_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="tf-sec"><i class="fa fa-door-open mr-1"></i> Operação</div>
                    <div class="row">
                        <div class="col-md-4">
                            <x-form-input name="sala" label="Sala" :value="old('sala', $turma->sala ?? '')" maxlength="40" placeholder="101" />
                        </div>
                        <div class="col-md-4">
                            <x-form-input name="capacidade_maxima" type="number" label="Capacidade" :value="old('capacidade_maxima', $turma->capacidade_maxima ?? '')" min="1" max="500" />
                        </div>
                        <div class="col-md-4">
                            <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $turma->ativo ?? 'S')" />
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end" style="border-radius:0 0 16px 16px;">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> {{ isset($turma) ? 'Salvar alterações' : 'Criar turma' }}</button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card tf-card">
                <div class="card-body">
                    <div class="tf-sec"><i class="fa fa-lightbulb mr-1"></i> Dicas rápidas</div>
                    <div class="tf-dica">
                        <i class="fa fa-school" style="background:#007bff;"></i>
                        <div><b>Escola e ano letivo</b><span>Definem onde e quando a turma existe.</span></div>
                    </div>
                    <div class="tf-dica">
                        <i class="fa fa-clock" style="background:#8b5cf6;"></i>
                        <div><b>Turno e grade</b><span>O turno filtra as grades de horário cadastradas na escola.</span></div>
                    </div>
                    <div class="tf-dica">
                        <i class="fa fa-users" style="background:#10b981;"></i>
                        <div><b>Capacidade</b><span>Limite de alunos nas matrículas desta turma.</span></div>
                    </div>
                    <div class="tf-dica">
                        <i class="fa fa-toggle-on" style="background:#f59e0b;"></i>
                        <div><b>Status</b><span>Turmas inativas não recebem novas matrículas.</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
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
