@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('opcional_grupo_veiculo.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $opcional_grupo_veiculos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('opcional_grupo_veiculo.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $opcional_grupo_veiculos->links() }}
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
                    @foreach ($opcional_grupo_veiculos as $opcional_grupo_veiculo)
                        <tr>
                            <td>{{ $opcional_grupo_veiculo->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $opcional_grupo_veiculo->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $opcional_grupo_veiculo->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('opcional_grupo_veiculo.edit', $opcional_grupo_veiculo->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('opcional_grupo_veiculo.destroy', $opcional_grupo_veiculo->id)" />
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
    <x-page-tour tour-id="opcional-grupo-veiculo-index" :steps="[
        [
            'element' => '.page-title',
            'popover' => [
                'title' => 'Vínculos de opcionais por grupo',
                'description' => 'Aqui você gerencia os vínculos que organizam opcionais por grupos de veículos para uso nas reservas.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '.btn-create-resource, a[href*=\"opcional_grupo_veiculo/create\"]',
            'popover' => [
                'title' => 'Novo vínculo',
                'description' => 'Clique aqui para criar um novo vínculo de opcional com grupo de veículo.',
                'side' => 'left',
            ],
        ],
        [
            'element' => '.card-header',
            'popover' => [
                'title' => 'Resumo da tela',
                'description' => 'A parte superior mostra o total de registros, paginação e seletor da quantidade exibida por página.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table thead tr',
            'popover' => [
                'title' => 'Tabela de vínculos',
                'description' => 'A lista apresenta o nome do vínculo e se ele está ativo para ser utilizado nas regras do sistema.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table tbody tr:first-child td:last-child',
            'popover' => [
                'title' => 'Ações do vínculo',
                'description' => 'Cada registro pode ser editado para ajuste do cadastro ou removido quando não for mais necessário.',
                'side' => 'left',
            ],
        ],
    ]" />
@stop
