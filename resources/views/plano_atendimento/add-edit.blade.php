@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($plano) ? 'Editar Plano de Atendimento' : 'Novo Plano de Atendimento'" />
@stop

@push('css')
<style>
    .pf-sec { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1.1rem 0 .6rem; padding-bottom:.25rem; border-bottom:1px solid #e5e7eb; }
    .pf-sec:first-child { margin-top:0; }
    .pf-sec i { width:16px; text-align:center; margin-right:.15rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($plano) ? route('plano_atendimento.update', $plano->id) : route('plano_atendimento.store') }}" id="pfForm" novalidate>
            @csrf
            @if(isset($plano))
                @method('PUT')
            @endif

            <div class="pf-sec"><i class="fa fa-user-graduate"></i> Identificação</div>
            <div class="row">
                <div class="col-md-6">
                    @if(isset($plano))
                        <x-form-input name="aluno_nome" label="Aluno" :value="$plano->aluno->pessoa->nome ?? ''" disabled />
                    @else
                        <label class="form-label" for="pf_aluno_display">Aluno <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="pf_aluno_display" class="form-control" placeholder="Buscar aluno..." readonly tabindex="-1">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btnBuscarAluno"><i class="fa fa-search mr-1"></i> Buscar</button>
                            </div>
                        </div>
                        @error('aluno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <input type="hidden" name="aluno_id" id="aluno_id" value="{{ old('aluno_id') }}">
                    @endif
                </div>
                <div class="col-md-6">
                    @if(isset($plano))
                        <x-form-input name="escola_nome" label="Escola" :value="$plano->escola->nome ?? ''" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Selecionar'] + ($escolas ?? [])" :selected="old('escola_id')" required />
                    @endif
                </div>
            </div>

            <div class="pf-sec"><i class="fa fa-clipboard-list"></i> Plano</div>
            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="tipo" label="Tipo de plano" :options="$tipos ?? []" :selected="old('tipo', $plano->tipo ?? '')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="status" label="Status" :options="$statusList ?? []" :selected="old('status', $plano->status ?? 'ativo')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="user_id" label="Profissional responsável" :options="['' => '—'] + ($profissionais ?? [])" :selected="old('user_id', $plano->user_id ?? '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Situação" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $plano->ativo ?? 'S')" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="data_elaboracao" type="date" label="Data de elaboração" :value="old('data_elaboracao', isset($plano) && $plano->data_elaboracao ? $plano->data_elaboracao->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="proxima_revisao" type="date" label="Próxima revisão" :value="old('proxima_revisao', isset($plano) && $plano->proxima_revisao ? $plano->proxima_revisao->format('Y-m-d') : '')" />
                </div>
            </div>

            <div class="pf-sec"><i class="fa fa-notes-medical"></i> Conteúdo pedagógico</div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Diagnóstico</label>
                    <textarea name="diagnostico" class="form-control" rows="3" maxlength="4000"
                        placeholder="Avaliação, laudos e condições identificadas.">{{ old('diagnostico', $plano->diagnostico ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Necessidades específicas</label>
                    <textarea name="necessidades" class="form-control" rows="3" maxlength="4000"
                        placeholder="Acessibilidade, recursos, adaptações necessárias.">{{ old('necessidades', $plano->necessidades ?? '') }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Objetivos <span class="text-danger">*</span></label>
                    <textarea name="objetivos" class="form-control" rows="3" required maxlength="4000"
                        placeholder="Metas do plano (curto, médio e longo prazo).">{{ old('objetivos', $plano->objetivos ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estratégias e adaptações</label>
                    <textarea name="estrategias" class="form-control" rows="3" maxlength="4000"
                        placeholder="Metodologias, recursos e adaptações de avaliação.">{{ old('estrategias', $plano->estrategias ?? '') }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $plano->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3 pt-3" style="border-top:1px solid #f0f3f8;">
                <a href="{{ route('plano_atendimento.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Salvar plano</button>
            </div>
        </form>
    </div>
</div>

@if(!isset($plano))
@include('partials.entity-picker')
<script>
(function () {
@php $alunoPre = collect($alunosPicker ?? [])->firstWhere('id', (int) old('aluno_id')); @endphp
    const input = document.getElementById('aluno_id');
    const display = document.getElementById('pf_aluno_display');
    const pre = @json($alunoPre);

    EntityPicker.registrar('aluno', {
        titulo: 'Buscar aluno',
        dados: @json($alunosPicker ?? []),
        filtros: [{ campo: 'escola_id', campoRotulo: 'escola', label: 'Escola' }],
        onSelect: function (d) {
            input.value = d.id;
            display.value = d.rotulo;
            display.classList.remove('is-invalid');
        },
    });
    if (pre) {
        input.value = pre.id;
        display.value = pre.rotulo;
    }
    document.getElementById('btnBuscarAluno').addEventListener('click', function () {
        EntityPicker.abrir('aluno');
    });
    document.getElementById('pfForm').addEventListener('submit', function (e) {
        if (!input.value) {
            e.preventDefault();
            display.classList.add('is-invalid');
            EntityPicker.abrir('aluno');
        }
    });
})();
</script>
@endif
@stop
