@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('caracteristica_frota.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $caracteristicaFrotas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('caracteristica_frota.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $caracteristicaFrotas->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Descricao</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($caracteristicaFrotas as $caracteristica_frota)
                        <tr>
                            <td>
                                @if(!empty($caracteristica_frota->imagem_url))
                                    <img src="{{ $caracteristica_frota->imagem_url }}" alt="Imagem {{ $caracteristica_frota->nome }}" class="caracteristica-thumb">
                                @else
                                    <span class="text-muted">Sem imagem</span>
                                @endif
                            </td>
                            <td>{{ $caracteristica_frota->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $caracteristica_frota->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $caracteristica_frota->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($caracteristica_frota->descricao))
                                    <button class="btn btn-xs btn-light btn-desc-toggle" type="button"
                                        data-toggle="collapse" data-target="#desc-{{ $caracteristica_frota->id }}"
                                        aria-expanded="false" aria-controls="desc-{{ $caracteristica_frota->id }}">
                                        Ver descricao
                                    </button>
                                @else
                                    <span class="text-muted">Sem descricao</span>
                                @endif
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('caracteristica_frota.edit', $caracteristica_frota->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('caracteristica_frota.destroy', $caracteristica_frota->id)" />
                            </td>
                        </tr>
                        <tr class="collapse-row">
                            <td colspan="5" class="p-0 border-0">
                                <div class="collapse" id="desc-{{ $caracteristica_frota->id }}">
                                    <div class="desc-content">
                                        {{ $caracteristica_frota->descricao }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
<style>
    .btn-desc-toggle {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #d5dde8;
        background: #f8fafc;
    }

    .desc-content {
        background: #f8fbff;
        border: 1px dashed #d7e1ee;
        border-radius: 10px;
        padding: 12px 14px;
        margin: 6px 0 10px;
        color: #475569;
        font-size: .9rem;
    }

    .caracteristica-thumb {
        width: 72px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .table.table-striped tbody tr:hover td {
        background: #f4f8ff;
    }

    .card-header .card-title {
        color: #0f2740;
    }
</style>
@stop

@section('js')
    <script></script>
@stop
