@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('tempo_entre_entacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success">{{ $tempo_entre_entacaos->total() }}</span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('tempo_entre_entacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $tempo_entre_entacaos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Estação Origem</th>
                        <th>Estação Destino</th>
                        <th>Tempo de Deslocamento</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tempo_entre_entacaos as $tempo_entre_entacao)
                        <tr>
                            <td>{{ $tempo_entre_entacao->estacaoOrigem->nome }}</td>
                            <td>{{ $tempo_entre_entacao->estacaoDestino->nome }}</td>
                            <td>{{ $tempo_entre_entacao->tempo_deslocamento }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $tempo_entre_entacao->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $tempo_entre_entacao->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="{{ route('tempo_entre_entacao.edit', $tempo_entre_entacao->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('tempo_entre_entacao.destroy', $tempo_entre_entacao->id)" />
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
    <x-page-tour tour-id="tempo-entre-estacao-index" :steps="[
        [
            'element' => '.page-title',
            'popover' => [
                'title' => 'Tempos entre estações',
                'description' => 'Aqui você cadastra e consulta os tempos estimados de deslocamento entre estações de origem e destino.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => '.btn-create-resource, a[href*=\"tempo_entre_entacao/create\"]',
            'popover' => [
                'title' => 'Novo tempo de deslocamento',
                'description' => 'Use este botão para informar uma nova combinação entre estação de origem, destino e tempo previsto.',
                'side' => 'left',
            ],
        ],
        [
            'element' => '.card-header',
            'popover' => [
                'title' => 'Resumo da listagem',
                'description' => 'O topo mostra total de registros, paginação e o seletor de quantidade exibida por página.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table thead tr',
            'popover' => [
                'title' => 'Tabela de trajetos',
                'description' => 'Cada linha exibe a estação de origem, a estação de destino, o tempo configurado e o status do cadastro.',
                'side' => 'bottom',
            ],
        ],
        [
            'element' => 'table tbody tr:first-child td:last-child',
            'popover' => [
                'title' => 'Editar ou excluir',
                'description' => 'As ações permitem ajustar um trajeto cadastrado ou remover um vínculo que não será mais utilizado.',
                'side' => 'left',
            ],
        ],
    ]" />
@stop
