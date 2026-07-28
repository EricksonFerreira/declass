@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb ?? []" />
    <x-page-title :title="$titulo" :createUrl="route('manutencao.planos.create')" />
@stop

@section('content')
    @if(isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters ?? []" :filters="$filters" :itensFiltered="$itensFiltered ?? false" />
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0 text-sm text-bold">
                REGISTROS <span class="badge badge-success">{{ $planos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $planos->total() : count($planos) }}</span>
            </h3>
            <form action="{{ route('manutencao.planos.setPerPage') }}" method="POST" class="mb-0">
                @csrf
                <x-per-page-selector :perPages="$perPages ?? [15, 30, 50, 100]" :selectedPerPage="$perPage ?? 15" />
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Grupo de Frota</th>
                        <th>Itens</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planos as $plano)
                        @php
                            $collapseId = 'plano-itens-'.$plano->id;
                        @endphp
                        <tr>
                            <td>{{ $plano->id }}</td>
                            <td>{{ $plano->nome }}</td>
                            <td>{{ $plano->grupoFrota->nome ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false">
                                    <i class="fas fa-list mr-1"></i>{{ $plano->itens->count() }} item(ns)
                                </button>
                            </td>
                            <td>
                                <a href="{{ route('manutencao.planos.edit', $plano->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('manutencao.planos.destroy', $plano->id)" />
                            </td>
                        </tr>
                        <tr class="collapse bg-light" id="{{ $collapseId }}">
                            <td colspan="5">
                                <div class="p-2">
                                    @if($plano->itens->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 bg-white">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Item</th>
                                                        <th>Meses</th>
                                                        <th>KM</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($plano->itens as $itemPlano)
                                                        <tr>
                                                            <td>{{ $itemPlano->item?->nome ?? ('Item #'.$itemPlano->item_id) }}</td>
                                                            <td>{{ $itemPlano->frequencia_meses ? $itemPlano->frequencia_meses . ' meses' : '-' }}</td>
                                                            <td>{{ $itemPlano->frequencia_km ? number_format((float) $itemPlano->frequencia_km, 0, ',', '.') . ' km' : '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-muted">Este plano ainda não possui itens vinculados.</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">Nenhum plano cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($planos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer d-flex justify-content-end">
                {{ $planos->links() }}
            </div>
        @endif
    </div>
@stop

@section('js')
    <script></script>
@stop

@section('css')
@stop
