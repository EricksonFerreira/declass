@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('tipo_usuario.create')" />
@stop

@section('content')
    @if (!empty($filters))
        <x-filter
            :filters="$filters"
            :additionalFilters="$additionalFilters"
            :itensFiltered="$itensFiltered"
            :nomeFiltro="$nomeFiltro"
        />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span class="badge badge-success">{{ $tiposUsuario->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('tipo_usuario.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $tiposUsuario->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Guard Name</th>
                        <th>Permissoes</th>
                        <th style="width: 180px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tiposUsuario as $tipoUsuario)
                        <tr>
                            <td>{{ $tipoUsuario->name }}</td>
                            <td>{{ $tipoUsuario->guard_name }}</td>
                            <td>{{ $tipoUsuario->permissoes->count() }}</td>
                            <td style="width: 180px;">
                                <a href="{{ route('tipo_usuario.edit', $tipoUsuario->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('tipo_usuario.destroy', $tipoUsuario->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
