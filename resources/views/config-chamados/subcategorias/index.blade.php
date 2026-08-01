@extends('adminlte::page')
@section('title', 'Subcategorias de Chamados')
@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Config. Chamados', 'url' => route('config_chamados.subcategorias.index')],
        ['name' => 'Subcategorias'],
    ]" />
    <x-page-title title="Subcategorias de Chamados" :createUrl="route('config_chamados.subcategorias.create')" />
@stop
@section('content')
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Nome</th>
                        <th>Prazo (h)</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subcategorias as $subcategoria)
                        <tr>
                            <td>{{ $subcategoria->categoria?->nome ?? '-' }}</td>
                            <td>{{ $subcategoria->nome }}</td>
                            <td>{{ $subcategoria->prazo_horas ?? 0 }}</td>
                            <td><span class="badge badge-{{ $subcategoria->ativo ? 'success' : 'danger' }}">{{ $subcategoria->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                            <td>
                                <a class="btn btn-xs btn-warning" href="{{ route('config_chamados.subcategorias.edit', $subcategoria) }}">Editar</a>
                                <x-delete-button :action="route('config_chamados.subcategorias.destroy', $subcategoria)" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Nenhuma subcategoria cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $subcategorias->links() }}</div>
    </div>
@stop
