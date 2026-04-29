@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('frota.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $frotas->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('frota.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $frotas->links() }}
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
                    @foreach ($frotas as $frota)
                        <tr>
                            <td>{{ $frota->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $frota->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $frota->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('frota.edit', $frota->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('frota.destroy', $frota->id)" />
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
    tourId: 'frota-index',
    steps: @json([
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Veículos',
            'description' => 'Aqui você gerencia todos os grupos de veículos (frotas) cadastrados no sistema. Cada grupo agrupa modelos com características similares para facilitar as reservas.',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"frota/create\"]',
        'popover' => [
            'title' => 'Adicionar veículo',
            'description' => 'Clique aqui para cadastrar um novo grupo de veículos. Você informará o nome e o status (ativo/inativo).',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table thead tr',
        'popover' => [
            'title' => 'Tabela de veículos',
            'description' => 'Exibe o nome do grupo de veículos e seu status. Grupos ativos aparecem com badge verde; inativos, com vermelho.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações disponíveis',
            'description' => 'Para cada grupo você pode <b>Editar</b> as informações ou <b>Excluir</b> o cadastro. A exclusão só é permitida se o grupo não estiver vinculado a reservas.',
            'side' => 'left',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:nth-child(2)',
        'popover' => [
            'title' => 'Status do grupo',
            'description' => 'Grupos <b>Ativos</b> aparecem nas opções de reserva. Grupos <b>Inativos</b> ficam ocultos no processo de criação de reservas.',
            'side' => 'right',
        ],
    ],
    ]),
});
</script>
@stop

@section('css')
@stop
