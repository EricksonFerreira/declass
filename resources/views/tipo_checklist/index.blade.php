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
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $tipo_checklists->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
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

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tipo_checklists as $tipo_checklist)
                        <tr>
                            <td>{{ $tipo_checklist->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $tipo_checklist->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $tipo_checklist->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('tipo_checklist.edit', $tipo_checklist->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('tipo_checklist.destroy', $tipo_checklist->id)" />
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
    tourId: 'tipo-checklist-index',
    steps: @json([
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Tipos de Checklist',
            'description' => 'Aqui você gerencia os tipos de checklist (ex.: Retirada, Devolução, Manutenção). Os tipos classificam as configurações de checklist e determinam em qual etapa do fluxo cada uma é aplicada.',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"tipo-checklist/create\"], a[href*=\"tipo_checklist/create\"]',
        'popover' => [
            'title' => 'Adicionar tipo',
            'description' => 'Clique aqui para criar um novo tipo de checklist informando o nome e o status.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table thead tr',
        'popover' => [
            'title' => 'Tabela de tipos',
            'description' => 'Lista todos os tipos de checklist com nome e status. Tipos ativos podem ser usados nas configurações de checklist.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações',
            'description' => 'Use <b>Editar</b> para renomear ou alterar o status do tipo, e <b>Excluir</b> para removê-lo (somente se não houver configurações vinculadas).',
            'side' => 'left',
        ],
    ],
    ]),
});
</script>
@stop

@section('css')
@stop
