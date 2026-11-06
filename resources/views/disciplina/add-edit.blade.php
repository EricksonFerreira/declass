@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($disciplina) ? 'Editar Disciplina' : 'Nova Disciplina'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($disciplina) ? route('disciplina.update', $disciplina->id) : route('disciplina.store') }}">
            @csrf
            @if(isset($disciplina))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $disciplina->nome ?? '')" required maxlength="120" placeholder="Ex.: Matemática" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="sigla" label="Sigla" :value="old('sigla', $disciplina->sigla ?? '')" maxlength="20" placeholder="MAT" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" rows="2" class="form-control">{{ old('descricao', $disciplina->descricao ?? '') }}</textarea>
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $disciplina->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('disciplina.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
