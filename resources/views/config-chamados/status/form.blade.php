@extends('adminlte::page')
@section('title', $status->exists ? 'Editar Status' : 'Novo Status')
@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Config. Chamados', 'url' => route('config_chamados.status.index')],
        ['name' => 'Status'],
    ]" />
    <x-page-title :title="$status->exists ? 'Editar Status' : 'Novo Status'" />
@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $status->exists ? route('config_chamados.status.update', $status) : route('config_chamados.status.store') }}">
                @csrf
                @if($status->exists)
                    @method('PUT')
                @endif
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome', $status->nome) }}" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $status->slug) }}" required>
                </div>
                <div class="form-group">
                    <label>Cor</label>
                    <input type="text" name="cor" class="form-control" value="{{ old('cor', $status->cor) }}" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $status->ativo ?? true) ? 'checked' : '' }}>
                        Ativo
                    </label>
                </div>
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('config_chamados.status.index') }}" class="btn btn-default">Voltar</a>
            </form>
        </div>
    </div>
@stop
