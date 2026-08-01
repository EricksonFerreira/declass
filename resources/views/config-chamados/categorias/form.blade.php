@extends('adminlte::page')
@section('title', $categoria->exists ? 'Editar Categoria' : 'Nova Categoria')
@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Config. Chamados', 'url' => route('config_chamados.categorias.index')],
        ['name' => 'Categorias'],
    ]" />
    <x-page-title :title="$categoria->exists ? 'Editar Categoria' : 'Nova Categoria'" />
@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $categoria->exists ? route('config_chamados.categorias.update', $categoria) : route('config_chamados.categorias.store') }}">
                @csrf
                @if($categoria->exists)
                    @method('PUT')
                @endif
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $categoria->nome) }}" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $categoria->ativo ?? true) ? 'checked' : '' }}>
                        Ativo
                    </label>
                </div>
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('config_chamados.categorias.index') }}" class="btn btn-default">Voltar</a>
            </form>
        </div>
    </div>
@stop
