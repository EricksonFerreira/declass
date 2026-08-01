@extends('adminlte::page')
@section('title', $subcategoria->exists ? 'Editar Subcategoria' : 'Nova Subcategoria')
@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Config. Chamados', 'url' => route('config_chamados.subcategorias.index')],
        ['name' => 'Subcategorias'],
    ]" />
    <x-page-title :title="$subcategoria->exists ? 'Editar Subcategoria' : 'Nova Subcategoria'" />
@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $subcategoria->exists ? route('config_chamados.subcategorias.update', $subcategoria) : route('config_chamados.subcategorias.store') }}">
                @csrf
                @if($subcategoria->exists)
                    @method('PUT')
                @endif
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="chamado_categoria_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('chamado_categoria_id', $subcategoria->chamado_categoria_id) == $categoria->id)>{{ $categoria->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $subcategoria->nome) }}" required>
                </div>
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $subcategoria->descricao) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Prazo em horas</label>
                    <input type="number" name="prazo_horas" class="form-control" min="0" value="{{ old('prazo_horas', $subcategoria->prazo_horas) }}">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $subcategoria->ativo ?? true) ? 'checked' : '' }}>
                        Ativo
                    </label>
                </div>
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('config_chamados.subcategorias.index') }}" class="btn btn-default">Voltar</a>
            </form>
        </div>
    </div>
@stop
