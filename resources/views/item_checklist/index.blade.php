@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('item_checklist.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $item_checklists->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('item_checklist.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $item_checklists->links() }}
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
                    @foreach ($item_checklists as $item_checklist)
                        <tr>
                            <td>{{ $item_checklist->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $item_checklist->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $item_checklist->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('item_checklist.edit', $item_checklist->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('item_checklist.destroy', $item_checklist->id)" />
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
<script src="{{ asset('js/page-tour-inline.js') }}"></script>
<script>
initInlinePageTour({
    tourId: 'item-checklist-index',
    steps: @json([
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Itens de Checklist',
            'description' => 'Aqui você gerencia o catálogo de itens de inspeção (pneus, lataria, vidros, documentos, combustível…). Esses itens são selecionados nas configurações de checklist.',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"item-checklist/create\"], a[href*=\"item_checklist/create\"]',
        'popover' => [
            'title' => 'Adicionar item',
            'description' => 'Clique aqui para cadastrar um novo item de inspeção informando o nome e o status.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table thead tr',
        'popover' => [
            'title' => 'Tabela de itens',
            'description' => 'Lista todos os itens disponíveis para uso em configurações de checklist com nome e status (ativo/inativo).',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações',
            'description' => 'Use <b>Editar</b> para alterar o nome ou status do item, e <b>Excluir</b> para removê-lo do catálogo.',
            'side' => 'left',
        ],
    ],
    ]),
});
</script>
@stop

@section('css')
@stop
