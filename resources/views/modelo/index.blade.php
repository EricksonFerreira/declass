@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('modelo.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $modelos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('modelo.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $modelos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width: 90px;">Imagem</th>
                        <th>Nome</th>
                        <th>Marca</th>
                        <th>Segmento</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modelos as $modelo)
                        <tr>
                            <td class="text-center">
                                @if(!empty($modelo->imagem_url))
                                    <img src="{{ $modelo->imagem_url }}" alt="{{ $modelo->nome }}" style="width:64px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
                                @else
                                    <span class="text-muted" style="font-size:11px;">Sem imagem</span>
                                @endif
                            </td>
                            <td>{{ $modelo->nome }}</td>
                            <td>{{ $modelo->marcas?->nome }}</td>
                            <td>{{ $modelo->segmentos?->nome }}</td>
                            <td>{{ $modelo->ano_modelo ?? '—' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge badge-light border text-dark align-self-start mb-1">
                                        Código: {{ $modelo->codigo_fipe ?: '—' }}
                                    </span>
                                    <small class="text-muted">
                                        {{ $modelo->nome_fipe ?: 'Sem dados FIPE' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-xs bg-{{ $modelo->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $modelo->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('modelo.edit', $modelo->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('modelo.destroy', $modelo->id)" />
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
