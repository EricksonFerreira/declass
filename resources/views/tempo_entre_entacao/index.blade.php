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
@stop
