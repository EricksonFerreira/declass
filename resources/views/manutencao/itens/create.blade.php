@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Itens de Manutenção Preventiva', 'url' => route('manutencao.itens.index')], ['name' => 'Criar']]" />
@stop

@section('content')
    <div class="col-xl-8 col-lg-10 col-md-12 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1">Novo Item de Manutenção Preventiva</h4>
                    <p class="mb-0 text-muted">Cadastre um item para reutilizar nos planos de manutenção.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('manutencao.itens.store') }}">
                    @csrf

                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" class="form-control" value="{{ old('nome') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" rows="4">{{ old('descricao') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary col-12">Salvar Item</button>
                </form>
            </div>
        </div>
    </div>
@stop
