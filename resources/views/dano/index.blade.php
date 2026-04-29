@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('dano.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $danos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('dano.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $danos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Localização</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($danos as $dano)
                        <tr>
                            <td>{{ $dano->nome }}</td>
                            <td>{{ $dano->categoria?->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{$dano->localizacao == 'interior' ? 'warning' : 'dark'}}">
                                    {{ $dano->localizacao }}
                                </span>
                            </td>
                            <td>{{ $dano->nome }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $dano->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $dano->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('dano.edit', $dano->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('dano.destroy', $dano->id)" />
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
    tourId: 'dano-index',
    steps: @json([
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Tipos de Dano',
            'description' => 'Aqui você gerencia o catálogo de tipos de dano utilizados no check-in e checkout das reservas. Cada tipo pertence a uma categoria e tem uma localização (interior ou exterior).',
        ],
    ],
    [
        'element' => 'a[href*=\"dano/create\"], .btn-create-resource',
        'popover' => [
            'title' => 'Adicionar tipo de dano',
            'description' => 'Clique aqui para cadastrar um novo tipo de dano informando o nome, a categoria e a localização no veículo.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table thead tr',
        'popover' => [
            'title' => 'Tabela de tipos de dano',
            'description' => 'Lista todos os tipos de dano com nome, categoria, localização (interior/exterior) e status (ativo/inativo).',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:nth-child(3)',
        'popover' => [
            'title' => 'Localização do dano',
            'description' => '<b>Exterior</b> (badge escuro) — danos visíveis na carroceria. <b>Interior</b> (badge âmbar) — danos no habitáculo e componentes internos.',
            'side' => 'right',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:nth-child(4)',
        'popover' => [
            'title' => 'Status',
            'description' => 'Tipos <b>Ativos</b> (verde) aparecem nas opções de registro de dano durante o check-in e checkout. Tipos <b>Inativos</b> (vermelho) ficam ocultos.',
            'side' => 'right',
        ],
    ],
    [
        'element' => 'table tbody tr:first-child td:last-child',
        'popover' => [
            'title' => 'Ações',
            'description' => 'Use <b>Editar</b> para alterar nome, categoria ou localização. Use <b>Excluir</b> para remover o tipo (somente se não estiver vinculado a danos registrados).',
            'side' => 'left',
        ],
    ],
    ]),
});
</script>
@stop

@section('css')
@stop
