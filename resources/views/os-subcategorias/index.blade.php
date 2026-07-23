@extends('adminlte::page')

@section('title', 'OS Subcategorias')

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Subcategorias de OS']]" />
    <x-page-title title="Subcategorias de OS" :createUrl="route('os-subcategorias.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters ?? []" :filters="$filters" :itensFiltered="$itensFiltered ?? false" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span class="badge badge-success">{{ $subcategorias->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('os-subcategorias.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages ?? [15, 30, 50, 100]" :selectedPerPage="$perPage ?? 15" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $subcategorias->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Exibe Sinistro</th>
                        <th>Exibe Reserva</th>
                        <th>Exibe Avarias</th>
                        <th>Exibe Checklist Prev.</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subcategorias as $subcategoria)
                        <tr>
                            <td>{{ $subcategoria->nome }}</td>
                            <td>{{ $subcategoria->categoria?->nome }}</td>
                            <td>{{ $subcategoria->show_sinistro ? 'Sim' : 'Não' }}</td>
                            <td>{{ $subcategoria->show_reserva ? 'Sim' : 'Não' }}</td>
                            <td>{{ $subcategoria->show_avarias ? 'Sim' : 'Não' }}</td>
                            <td>{{ $subcategoria->show_checklist_prevencao ? 'Sim' : 'Não' }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $subcategoria->is_active ? 'success' : 'danger' }}">
                                    {{ $subcategoria->is_active ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('os-subcategorias.edit', $subcategoria->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('os-subcategorias.destroy', $subcategoria->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
