@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? []" />
    <x-page-title :title="$titulo" :createUrl="route('manutencao.planos.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters ?? []" :filters="$filters" :itensFiltered="$itensFiltered ?? false" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ isset($planos) && $planos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $planos->total() : ($planos ? count($planos) : 0) }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('manutencao.planos.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages ?? [15, 30, 50, 100]" :selectedPerPage="$perPage ?? 15" />
                    </form>
                    <div class="d-flex align-items-end">
                        @if(isset($planos) && $planos instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $planos->links() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Grupo de Frota</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planos as $plano)
                        <tr>
                            <td>{{ $plano->id }}</td>
                            <td>{{ $plano->nome }}</td>
                            <td>{{ $plano->grupoFrota->nome ?? '-' }}</td>
                            <td style="width: 180px;">
                                <a href="{{ route('manutencao.planos.edit', $plano->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('manutencao.planos.destroy', $plano->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Nenhum plano cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop

@section('css')
@stop
