@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($matricula) ? 'Editar Matrícula' : 'Nova Matrícula'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($matricula) ? route('matricula.update', $matricula->id) : route('matricula.store') }}">
            @csrf
            @if(isset($matricula))
                @method('PUT')
            @endif

            @if(isset($matricula))
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="aluno_nome" label="Aluno" :value="$matricula->aluno->pessoa->nome ?? ''" disabled />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="turma_nome" label="Turma" :value="$matricula->turma->nome ?? ''" disabled />
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-6">
                        <x-form-select name="aluno_id" label="Aluno" :options="$alunos ?? []" :selected="old('aluno_id')" placeholder="Selecionar" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-select name="turma_id" label="Turma" :options="$turmas ?? []" :selected="old('turma_id', request('turma_id'))" placeholder="Selecionar" required />
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="status" label="Status" :options="$status ?? []" :selected="old('status', $matricula->status ?? 'ativa')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="numero_chamada" type="number" label="Nº de chamada" :value="old('numero_chamada', $matricula->numero_chamada ?? '')" min="1" max="999" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_matricula" type="date" label="Data da matrícula" :value="old('data_matricula', isset($matricula) && $matricula->data_matricula ? $matricula->data_matricula->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_saida" type="date" label="Data de saída" :value="old('data_saida', isset($matricula) && $matricula->data_saida ? $matricula->data_saida->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="plano_mensalidade_id" label="Plano de mensalidade" :options="$planos ?? []" :selected="old('plano_mensalidade_id', $matricula->plano_mensalidade_id ?? '')" placeholder="Sem plano" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3" maxlength="2000">{{ old('observacoes', $matricula->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('matricula.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
