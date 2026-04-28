@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('opcional.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $opcionals->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('opcional.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $opcionals->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo Opcional</th>
                        <th>Preço Integral</th>
                        <th>Preço por dia</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($opcionals as $opcional)
                        <tr>
                            <td>{{ $opcional->nome }}</td>
                            <td>{{ $opcional->tipoOpcional?->nome }}</td>
                            <td>{{ $opcional->preco_por_dia }}</td>
                            <td>{{ $opcional->preco_integral  }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $opcional->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $opcional->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('opcional.edit', $opcional->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('opcional.destroy', $opcional->id)" />
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
<x-page-tour tour-id="opcional-index" :steps="[
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Opcionais',
            'description' => 'Aqui você gerencia os serviços e itens opcionais disponíveis para contratação nas reservas (seguro, GPS, cadeirinha, motorista adicional…). Cada opcional tem um tipo e forma de cobrança.',
        ],
    ],
    [
        'element' => '.btn-create-resource, a[href*=\"opcional/create\"]',
        'popover' => [
            'title' => 'Adicionar opcional',
            'description' => 'Clique aqui para criar um novo opcional informando nome, tipo, forma de cobrança (por dia ou valor integral) e preço.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table thead tr',
        'popover' => [
            'title' => 'Tabela de opcionais',
            'description' => 'Lista todos os opcionais com nome, tipo, valor e status. Opcionais ativos ficam disponíveis para seleção nos grupos de frota e reservas.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações',
            'description' => 'Use <b>Editar</b> para alterar dados do opcional e <b>Excluir</b> para remover (somente se não estiver vinculado a reservas ativas).',
            'side' => 'left',
        ],
    ],
]" />
@stop

@section('css')
@stop
