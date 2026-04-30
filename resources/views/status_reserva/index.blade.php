@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo">
        @can('acessar-superadmin')
            <a href="{{ route('status_reserva.create') }}" class="btn btn-xs btn-primary btn-create-resource">
                <i class="fa fa-plus" style="font-size:10px"></i> Adicionar
            </a>
        @endcan
    </x-page-title>
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $status_reservas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('status_reserva.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $status_reservas->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        @can('acessar-superadmin')
                            <th style="width: 160px;">Ações</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($status_reservas as $sr)
                        <tr>
                            <td>{{ $sr->id }}</td>
                            <td>
                                <span class="badge-hex"
                                      style="background-color:{{ $sr->cor ?? '#6c757d' }}">
                                    {{ $sr->nome }}
                                </span>
                            </td>
                            <td>{{ $sr->descricao }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $sr->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $sr->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            @can('acessar-superadmin')
                                <td>
                                    <a href="{{ route('status_reserva.edit', $sr->id) }}"
                                        class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                    <x-delete-button :action="route('status_reserva.destroy', $sr->id)" />
                                </td>
                            @endcan
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
