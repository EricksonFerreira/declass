@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('tipo_checklist.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success">{{ $tipo_checklists->total() }}</span>
                </h3>
                <div class="align-bottom d-flex flex-row justify-between" style="gap:.75rem;">
                    <form action="{{ route('tipo_checklist.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $tipo_checklists->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Itens</th>
                        <th>Status</th>
                        <th style="width: 170px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tipo_checklists as $tipo_checklist)
                        @php
                            $itens = $tipo_checklist->configuracaoChecklist?->pluck('itemChecklist.nome')->filter()->values() ?? collect();
                            $totalItens = $itens->count();
                            $preview = $itens->take(3)->join(', ');
                            $modalId = 'modal-itens-tipo-' . $tipo_checklist->id;
                        @endphp
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $tipo_checklist->nome }}</div>
                                <div class="text-muted" style="font-size:.82rem;">ID {{ $tipo_checklist->id }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                    <span class="badge badge-info">{{ $totalItens }} item(ns)</span>
                                    @if ($totalItens > 0)
                                        <span class="text-muted" style="font-size:.85rem;">{{ $preview }}@if($totalItens > 3)...@endif</span>
                                        <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="modal" data-target="#{{ $modalId }}">
                                            Ver itens
                                        </button>
                                    @else
                                        <span class="text-muted" style="font-size:.85rem;">Nenhum item vinculado.</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-xs bg-{{ $tipo_checklist->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $tipo_checklist->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 170px;">
                                <a href="{{ route('tipo_checklist.edit', $tipo_checklist->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('tipo_checklist.destroy', $tipo_checklist->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nenhum tipo de checklist cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($tipo_checklists as $tipo_checklist)
        @php
            $itens = $tipo_checklist->configuracaoChecklist?->pluck('itemChecklist.nome')->filter()->values() ?? collect();
            $modalId = 'modal-itens-tipo-' . $tipo_checklist->id;
        @endphp
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Itens do tipo: {{ $tipo_checklist->nome }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($itens->isNotEmpty())
                            <div class="row">
                                @foreach ($itens as $item)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="border rounded px-3 py-2 bg-light">
                                            {{ $item }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">Nenhum item vinculado a este tipo de checklist.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop

@section('css')
    <style>
        .modal-backdrop {
            z-index: 1039;
        }
        .modal {
            z-index: 1040;
        }
    </style>
@stop
