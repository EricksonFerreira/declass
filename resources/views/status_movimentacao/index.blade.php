@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('status_movimentacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $status_movimentacaos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('status_movimentacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $status_movimentacaos->links() }}
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
                        <th>Cor</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($status_movimentacaos as $status_movimentacao)
                        <tr>
                            <td>{{ $status_movimentacao->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $status_movimentacao->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $status_movimentacao->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-xs bg-{{ $status_movimentacao->cor }}">
                                    {{ $status_movimentacao->nome }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('status_movimentacao.edit', $status_movimentacao->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('status_movimentacao.destroy', $status_movimentacao->id)" />
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
    <x-page-tour tour-id="status-movimentacao-index" :steps="[
        [
            'element' => '.page-title',
            'popover' => [
                'title' => 'Lista de status de movimentações',
                'description' => 'Aqui você gerencia os status usados no fluxo de movimentações, como pendente, em andamento ou concluído.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '.btn-create-resource, a[href*=\"status_movimentacao/create\"]',
            'popover' => [
                'title' => 'Novo status',
                'description' => 'Use este botão para cadastrar um novo status de movimentação com nome, cor visual e disponibilidade.',
                'side' => 'left',
            ],
        ],
        [
            'element' => '.card-header',
            'popover' => [
                'title' => 'Resumo e paginação',
                'description' => 'A área superior mostra a quantidade de registros, paginação e seletor de itens por página.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table thead tr',
            'popover' => [
                'title' => 'Tabela de status',
                'description' => 'A listagem mostra nome, status ativo/inativo e a cor usada para destacar a situação nas movimentações.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table tbody tr:first-child td:last-child',
            'popover' => [
                'title' => 'Ações disponíveis',
                'description' => 'Em cada registro você pode editar os dados do status ou remover o cadastro quando ele não estiver mais em uso.',
                'side' => 'left',
            ],
        ],
    ]" />
@stop
