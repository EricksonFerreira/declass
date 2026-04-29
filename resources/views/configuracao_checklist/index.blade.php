@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('configuracao-checklist.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $configuracaochecklists->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('configuracao_checklist.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $configuracaochecklists->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Tipo de Checklist</th>
                        <th>Item de Checklist</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($configuracaochecklists as $configuracaochecklist)
                        <tr>
                            <td>{{ $configuracaochecklist->tipoChecklistNome }}</td>
                            <td>{{ $configuracaochecklist->itemChecklistNome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $configuracaochecklist->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $configuracaochecklist->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('configuracao-checklist.edit', $configuracaochecklist->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('configuracao-checklist.destroy', $configuracaochecklist->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="{{ asset('js/page-tour-inline.js') }}"></script>
<script>
initInlinePageTour({
    tourId: 'configuracao-checklist-index',
    steps: @json([
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Configurações de Checklist',
            'description' => 'Aqui você gerencia as configurações de checklist: conjuntos de itens agrupados por tipo que definem o que deve ser verificado no check-in e checkout de cada reserva.',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"configuracao-checklist/create\"], a[href*=\"configuracao_checklist/create\"]',
        'popover' => [
            'title' => 'Adicionar configuração',
            'description' => 'Clique aqui para criar uma nova configuração de checklist, selecionando o tipo e os itens que devem ser verificados.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table thead tr',
        'popover' => [
            'title' => 'Tabela de configurações',
            'description' => 'Lista as configurações existentes com nome, tipo de checklist, quantidade de itens e status (ativa/inativa).',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações',
            'description' => 'Use <b>Editar</b> para alterar os itens da configuração e <b>Excluir</b> para remover.',
            'side' => 'left',
        ],
    ],
    ]),
});
</script>
@stop

@section('css')
@stop
