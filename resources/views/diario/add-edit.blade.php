@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($diario) ? 'Editar Aula' : 'Registrar Aula'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($diario) ? route('diario.update', $diario->id) : route('diario.store') }}">
            @csrf
            @if(isset($diario))
                @method('PUT')
            @endif

            @if(isset($diario))
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="turma_nome" label="Turma" :value="$diario->turma->nome ?? ''" disabled />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="disciplina_nome" label="Disciplina" :value="$diario->disciplina->nome ?? ''" disabled />
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-6">
                        <x-form-select name="turma_id" label="Turma" :options="$turmas ?? []" :selected="old('turma_id', request('turma_id'))" placeholder="Selecionar" required />
                    </div>
                    <div class="col-md-6">
                        <x-form-select name="disciplina_id" label="Disciplina" :options="$disciplinas ?? []" :selected="old('disciplina_id')" placeholder="Selecionar" required />
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="data" type="date" label="Data da aula" :value="old('data', isset($diario) && $diario->data ? $diario->data->format('Y-m-d') : '')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Conteúdo ministrado <span class="text-danger">*</span></label>
                    <textarea name="conteudo" class="form-control" rows="6" required maxlength="10000">{{ old('conteudo', $diario->conteudo ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $diario->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('diario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
