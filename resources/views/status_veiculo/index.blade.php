@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo">
        @can('acessar-superadmin')
            <a href="{{ route('status_veiculo.create') }}" class="btn btn-xs btn-primary btn-create-resource">
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
                        class="badge badge-success">{{ $status_veiculos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('status_veiculo.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $status_veiculos->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cor</th>
                        <th>Status</th>
                        @can('acessar-superadmin')
                            <th style="width: 180px;">Ações</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($status_veiculos as $status_veiculo)
                        @php
                            $cor = $status_veiculo->cor ?: 'secondary';
                        @endphp
                        <tr>
                            <td>
                                <span class="badge badge-xs bg-{{ $cor }}">
                                    {{ $status_veiculo->nome }}
                                </span>
                            </td>
                            <td>
                                <code>bg-{{ $cor }}</code>
                            </td>
                            <td>
                                <span class="badge badge-xs bg-{{ $status_veiculo->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $status_veiculo->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            @can('acessar-superadmin')
                                <td style="width: 180px;">
                                    <a href="{{ route('status_veiculo.edit', $status_veiculo->id) }}"
                                        class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                    <x-delete-button :action="route('status_veiculo.destroy', $status_veiculo->id)" />
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
