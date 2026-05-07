@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('termo_consentimento.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS
                    <span class="badge badge-success">{{ $termo_consentimentos->total() }}</span>
                </h3>
                <div class="align-bottom d-flex flex-row justify-between" style="gap:8px;">
                    <form action="{{ route('termo_consentimento.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="d-flex align-items-end">
                        {{ $termo_consentimentos->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width:60px;">Ordem</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Texto</th>
                        <th style="width:80px;">Status</th>
                        <th style="width:180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($termo_consentimentos as $termo)
                        <tr>
                            <td class="text-center">{{ $termo->ordem ?? '-' }}</td>
                            <td>{{ $termo->titulo ?? $termo->nome ?? '-' }}</td>
                            <td>
                                <span class="badge badge-xs bg-{{ $termo->tipo === 'checkout' ? 'info' : 'primary' }}">
                                    {{ strtoupper($termo->tipo ?? 'CHECKIN') }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:12px;color:#666;">
                                    {{ Str::limit(strip_tags($termo->texto ?? ''), 120) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-xs bg-{{ $termo->ativo == 'S' ? 'success' : 'danger' }}">
                                    {{ $termo->ativo == 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('termo_consentimento.edit', $termo->id) }}"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <x-delete-button :action="route('termo_consentimento.destroy', $termo->id)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Nenhum termo de consentimento encontrado.
                            </td>
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
