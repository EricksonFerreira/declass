@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Itens de Manutenção Preventiva', 'url' => route('manutencao.itens.index')], ['name' => 'Editar']]" />
@stop

@section('content')
    <div class="col-xl-8 col-lg-10 col-md-12 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1">Editar Item de Manutenção Preventiva</h4>
                    <p class="mb-0 text-muted">Ajuste o nome e a descrição do item que será reutilizado nos planos.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('manutencao.itens.update', $item_manutencao->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome', $item_manutencao->nome) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" rows="4">{{ old('descricao', $item_manutencao->descricao) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary col-12">Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>
@stop
