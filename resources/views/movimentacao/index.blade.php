@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('movimentacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success">{{ $movimentacaos->total() }}</span>
                </h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="{{ route('movimentacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $movimentacaos->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo / Status</th>
                        <th>Usuario</th>
                        <th>Veiculo</th>
                        <th>Origem / Destino</th>
                        <th>Datas</th>
                        <th class="text-center" style="width: 160px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimentacaos as $movimentacao)
                        <tr>
                            <td>{{ $movimentacao->id }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $movimentacao->tipoMovimentacao?->nome ?? '-' }}</div>
                                <span class="badge badge-xs bg-{{ $movimentacao->statusMovimentacao?->cor ?? 'secondary' }}">
                                    {{ $movimentacao->statusMovimentacao?->nome ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $movimentacao->usuario?->nome ?? '-' }}</td>
                            <td>
                                @php
                                    $veiculoLabel = null;
                                    if ($movimentacao->veiculo) {
                                        $veiculoLabel = $movimentacao->veiculo->nome ?? ('Veículo #' . $movimentacao->veiculo->id);
                                        if (!empty($movimentacao->veiculo->placa_licenca)) {
                                            $veiculoLabel .= ' - ' . $movimentacao->veiculo->placa_licenca;
                                        }
                                    }
                                @endphp

                                <div>{{ $veiculoLabel ?? ($movimentacao->modelo?->nome ?? '-') }}</div>
                                @if($movimentacao->veiculo?->grupoFrota)
                                    <div class="text-muted small">Grupo: {{ $movimentacao->veiculo->grupoFrota->nome }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $movimentacao->localOrigem?->nome ?? '-' }}</div>
                                <div class="text-muted small">{{ $movimentacao->localDestino?->nome ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="small">
                                    <div><span class="text-muted">Registro:</span> {{ $movimentacao->data_registro ? \Carbon\Carbon::parse($movimentacao->data_registro)->format('d/m/Y H:i') : '--' }}</div>
                                    <div><span class="text-muted">Saida:</span> {{ $movimentacao->data_saida ? \Carbon\Carbon::parse($movimentacao->data_saida)->format('d/m/Y H:i') : '--' }}</div>
                                    <div><span class="text-muted">Prev.:</span> {{ $movimentacao->data_chegada_prevista ? \Carbon\Carbon::parse($movimentacao->data_chegada_prevista)->format('d/m/Y H:i') : '--' }}</div>
                                    <div><span class="text-muted">Chegada:</span> {{ $movimentacao->data_chegada_real ? \Carbon\Carbon::parse($movimentacao->data_chegada_real)->format('d/m/Y H:i') : '--' }}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('movimentacao.edit', $movimentacao->id) }}" class="btn btn-xs btn-outline-primary">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('movimentacao.destroy', $movimentacao->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Nenhuma movimentacao encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
