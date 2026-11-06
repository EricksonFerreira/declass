@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($avaliacao) ? 'Editar Avaliação' : 'Nova Avaliação'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($avaliacao) ? route('avaliacao.update', $avaliacao->id) : route('avaliacao.store') }}">
            @csrf
            @if(isset($avaliacao))
                @method('PUT')
            @endif

            @if(isset($avaliacao))
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="turma_nome" label="Turma" :value="$avaliacao->turma->nome ?? ''" disabled />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="disciplina_nome" label="Disciplina" :value="$avaliacao->disciplina->nome ?? ''" disabled />
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
                    <x-form-input name="nome" label="Nome da avaliação" :value="old('nome', $avaliacao->nome ?? '')" required maxlength="150" placeholder="Ex.: Prova 1 — Equações" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $avaliacao->tipo ?? 'prova')" required />
                </div>
                <div class="col-md-2">
                    <x-form-input name="periodo" label="Período" :value="old('periodo', $avaliacao->periodo ?? '')" maxlength="30" placeholder="1º bim." />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data" type="date" label="Data de aplicação" :value="old('data', isset($avaliacao) && $avaliacao->data ? $avaliacao->data->format('Y-m-d') : '')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="nota_maxima" type="number" label="Nota máxima" :value="old('nota_maxima', $avaliacao->nota_maxima ?? 10)" min="0.1" max="1000" step="0.1" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição / instruções</label>
                    <textarea name="descricao" class="form-control" rows="3" maxlength="10000">{{ old('descricao', $avaliacao->descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $avaliacao->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('avaliacao.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
