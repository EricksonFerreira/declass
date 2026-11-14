@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($solicitacao) ? 'Editar Solicitação' : 'Nova Solicitação de Matrícula'" />
@stop

@section('content')
@php $s = $solicitacao ?? null; @endphp
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($s) ? route('solicitacao_matricula.update', $s->id) : route('solicitacao_matricula.store') }}">
            @csrf
            @if(isset($s))
                @method('PUT')
            @endif

            <h6 class="font-weight-bold text-primary">Escola e período pretendido</h6>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id', $s->escola_id ?? '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ano_letivo_id" label="Ano letivo" :options="($anosLetivos ?? collect())->pluck('ano', 'id')->all()" :selected="old('ano_letivo_id', $s->ano_letivo_id ?? '')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="etapa_id" label="Etapa pretendida" :options="['' => '—'] + ($etapas ?? [])" :selected="old('etapa_id', $s->etapa_id ?? '')" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="turma_id" label="Turma de preferência (opcional)" :options="['' => '—'] + ($turmas ?? collect())->mapWithKeys(fn ($t) => [$t->id => $t->nome.($t->escola ? ' ('.$t->escola->nome.')' : '')])->all()" :selected="old('turma_id', $s->turma_id ?? '')" />
                </div>
                <div class="col-md-6">
                    <x-form-select name="status" label="Status" :options="$statusList ?? []" :selected="old('status', $s->status ?? 'pendente')" />
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold text-primary">Aluno pretendido</h6>
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome_aluno" label="Nome completo" :value="old('nome_aluno', $s->nome_aluno ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_nascimento_aluno" type="date" label="Data de nascimento" :value="old('data_nascimento_aluno', isset($s) && $s->data_nascimento_aluno ? $s->nascimento_aluno_iso : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="sexo_aluno" label="Sexo" :options="['' => '—', 'masculino' => 'Masculino', 'feminino' => 'Feminino', 'outro' => 'Outro']" :selected="old('sexo_aluno', $s->sexo_aluno ?? '')" />
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold text-primary">Responsável</h6>
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome_responsavel" label="Nome completo" :value="old('nome_responsavel', $s->nome_responsavel ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="parentesco" label="Parentesco" :options="['' => '—'] + ($parentescos ?? [])" :selected="old('parentesco', $s->parentesco ?? '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="cpf_responsavel" label="CPF" :value="old('cpf_responsavel', $s->cpf_responsavel ?? '')" maxlength="20" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="telefone_responsavel" label="Telefone / WhatsApp" :value="old('telefone_responsavel', $s->telefone_responsavel ?? '')" maxlength="30" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="email_responsavel" type="email" label="E-mail" :value="old('email_responsavel', $s->email_responsavel ?? '')" maxlength="190" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3" maxlength="4000">{{ old('observacoes', $s->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('solicitacao_matricula.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
