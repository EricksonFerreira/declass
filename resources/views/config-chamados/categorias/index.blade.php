@extends('adminlte::page')
@section('title', 'Categorias de Chamados')
@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Config. Chamados', 'url' => route('config_chamados.categorias.index')],
        ['name' => 'Categorias'],
    ]" />
    <x-page-title title="Categorias de Chamados" :createUrl="route('config_chamados.categorias.create')" />
@stop
@section('content')
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Subcategorias</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->nome }}</td>
                            <td>{{ $categoria->subcategorias_count }}</td>
                            <td><span class="badge badge-{{ $categoria->ativo ? 'success' : 'danger' }}">{{ $categoria->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                            <td>
                                <a class="btn btn-xs btn-warning" href="{{ route('config_chamados.categorias.edit', $categoria) }}">Editar</a>
                                <x-delete-button :action="route('config_chamados.categorias.destroy', $categoria)" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Nenhuma categoria cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $categorias->links() }}</div>
    </div>
@stop
