@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('cupom_desconto.create')" />
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $cupom_descontos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('cupom_desconto.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $cupom_descontos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Código</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cupom_descontos as $cupom_desconto)
                        <tr>
                            <td>{{ $cupom_desconto->nome }}</td>
                            <td>{{ $cupom_desconto?->codigo }}</td>
                            <td>{{ $cupom_desconto?->valor }} %</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $cupom_desconto->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $cupom_desconto->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('cupom_desconto.edit', $cupom_desconto->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('cupom_desconto.destroy', $cupom_desconto->id)" />
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
    <x-page-tour tour-id="cupom-desconto-index" :steps="[
        [
            'element' => '.page-title',
            'popover' => [
                'title' => 'Lista de cupons de desconto',
                'description' => 'Aqui você consulta e administra os cupons promocionais disponíveis para aplicação nas reservas.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '.btn-create-resource, a[href*=\"cupom_desconto/create\"]',
            'popover' => [
                'title' => 'Novo cupom',
                'description' => 'Use este botão para cadastrar um novo cupom com código, percentual e status de uso.',
                'side' => 'left',
            ],
        ],
        [
            'element' => '.card-header',
            'popover' => [
                'title' => 'Controle da listagem',
                'description' => 'O cabeçalho mostra total de registros, paginação e a quantidade de itens exibidos por página.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table thead tr',
            'popover' => [
                'title' => 'Tabela de cupons',
                'description' => 'A grade mostra nome, código promocional, valor percentual e situação atual de cada cupom.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table tbody tr:first-child td:last-child',
            'popover' => [
                'title' => 'Ações do cupom',
                'description' => 'Em cada linha você pode editar os dados do cupom ou excluir o cadastro quando necessário.',
                'side' => 'left',
            ],
        ],
    ]" />
@stop
