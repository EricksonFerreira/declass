@extends('adminlte::page')

@section('title', 'OS Categorias')

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Categorias de OS']]" />
    <x-page-title title="Categorias de OS" :createUrl="route('os-categorias.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters ?? []" :filters="$filters" :itensFiltered="$itensFiltered ?? false" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span class="badge badge-success">{{ $categorias->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('os-categorias.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages ?? [15, 30, 50, 100]" :selectedPerPage="$perPage ?? 15" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $categorias->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $categoria->is_active ? 'success' : 'danger' }}">
                                    {{ $categoria->is_active ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('os-categorias.edit', $categoria->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('os-categorias.destroy', $categoria->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
