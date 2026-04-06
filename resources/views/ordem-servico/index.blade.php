@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('ordem-servico.create')" />
@stop

@section('content')
    <div class="os-index-page">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card p-2 os-index-card">
            <div class="card-header px-0 pt-0 pb-2 border-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap os-index-toolbar">
                    <h3 class="card-title text-sm text-bold mb-2 mb-md-0">
                        REGISTROS <span class="badge badge-success">{{ $ordens->total() }}</span>
                    </h3>
                    <div class="os-filter-tabs">
                        <a href="{{ route('ordem-servico.index') }}"
                            class="btn btn-xs {{ is_null($status ?? null) ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fas fa-list mr-1"></i>Todas
                        </a>
                        <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_ABERTA]) }}"
                            class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_ABERTA ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fas fa-folder-open mr-1"></i>Abertas
                        </a>
                        <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_EM_ANDAMENTO]) }}"
                            class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fas fa-spinner mr-1"></i>Em andamento
                        </a>
                        <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CONCLUIDA]) }}"
                            class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_CONCLUIDA ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fas fa-check-circle mr-1"></i>Concluidas
                        </a>
                        <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CANCELADA]) }}"
                            class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_CANCELADA ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fas fa-ban mr-1"></i>Canceladas
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-1 table-responsive">
                <table class="table table-hover table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Veiculo</th>
                            <th>Reserva</th>
                            <th>Status</th>
                            <th>Aberta por</th>
                            <th>Atualizacao</th>
                            <th class="text-center">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordens as $os)
                            <tr onclick="window.location='{{ route('ordem-servico.show', $os) }}';" style="cursor:pointer;">
                                <td class="text-center">{{ $os->numero ?? $os->id }}</td>
                                <td>
                                    <strong>{{ optional($os->veiculo)->nome ?? ('Veiculo #' . $os->veiculo_id) }}</strong><br>
                                    <small>{{ optional($os->veiculo)->placa ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($os->reserva_id)
                                        <strong>#{{ $os->reserva_id }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = match ($os->status) {
                                            \App\Models\OrdemServico::STATUS_ABERTA => 'warning',
                                            \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'primary',
                                            \App\Models\OrdemServico::STATUS_CONCLUIDA => 'success',
                                            \App\Models\OrdemServico::STATUS_CANCELADA => 'secondary',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', (string) $os->status)) }}
                                    </span>
                                </td>
                                <td>{{ optional($os->usuarioAbertura)->name ?? '-' }}</td>
                                <td>{{ $os->updated_at ? $os->updated_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('ordem-servico.show', $os) }}" class="btn btn-xs btn-info" onclick="event.stopPropagation();">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Nenhuma ordem de servico encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ordens->hasPages())
                <div class="card-footer bg-white border-0 pt-2">
                    {{ $ordens->appends(['status' => $status ?? null])->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
    .os-index-page .card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .os-index-toolbar {
        gap: 12px;
    }

    .os-filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
    }

    .os-index-card .table thead th {
        white-space: nowrap;
    }

    .os-index-card .badge {
        font-size: 11px;
        padding: 0.4rem 0.55rem;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .os-filter-tabs {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@stop
