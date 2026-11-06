@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($plano) ? 'Editar Plano de Atendimento' : 'Novo Plano de Atendimento'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($plano) ? route('plano_atendimento.update', $plano->id) : route('plano_atendimento.store') }}">
            @csrf
            @if(isset($plano))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($plano))
                        <x-form-input name="aluno_nome" label="Aluno" :value="$plano->aluno->pessoa->nome ?? ''" disabled />
                    @else
                        <x-form-select name="aluno_id" label="Aluno" :options="['' => 'Selecionar'] + ($alunos ?? [])" :selected="old('aluno_id')" required />
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

            <div class="mt-3">
                <a href="{{ route('plano_atendimento.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
