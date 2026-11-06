@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($livro) ? 'Editar Livro' : 'Novo Livro'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($livro) ? route('livro.update', $livro->id) : route('livro.store') }}">
            @csrf
            @if(isset($livro))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-7">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $livro->titulo ?? '')" required maxlength="180" placeholder="Ex.: Dom Casmurro" />
                </div>
                <div class="col-md-5">
                    <x-form-input name="autor" label="Autor" :value="old('autor', $livro->autor ?? '')" maxlength="140" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="editora" label="Editora" :value="old('editora', $livro->editora ?? '')" maxlength="140" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="isbn" label="ISBN" :value="old('isbn', $livro->isbn ?? '')" maxlength="20" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="ano_publicacao" type="number" label="Ano de publicação" :value="old('ano_publicacao', $livro->ano_publicacao ?? '')" min="1400" max="2100" />
                </div>
                <div class="col-md-2">
                    <x-form-input name="exemplares" type="number" label="Exemplares" :value="old('exemplares', $livro->exemplares ?? 1)" required min="1" max="500" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id', $livro->escola_id ?? '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="categoria" label="Categoria" :options="['' => 'Selecionar'] + ($categorias ?? [])" :selected="old('categoria', $livro->categoria ?? 'outro')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $livro->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="form-label">Descrição / Sinopse</label>
                    <textarea name="descricao" rows="4" class="form-control" maxlength="2000">{{ old('descricao', $livro->descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('livro.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
