@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo"  />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="mov-summary">
        <div class="mov-summary-card">
            <div class="mov-summary-label">Total de movimentacoes</div>
            <div class="mov-summary-value">{{ $movimentacaos->total() }}</div>
        </div>
        <div class="mov-summary-card">
            <div class="mov-summary-label">Pagina atual</div>
            <div class="mov-summary-value">{{ $movimentacaos->currentPage() }} / {{ $movimentacaos->lastPage() }}</div>
        </div>
        <div class="mov-summary-actions">
            <form action="{{ route('movimentacao.setPerPage') }}" method="POST">
                @csrf
                <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
            </form>
            <div class="mov-pagination">
                {{ $movimentacaos->links() }}
            </div>
        </div>
    </div>

    <div class="card movimentacao-card">
        <div class="card-header">
            <div class="mov-header">
                <div>
                    <div class="mov-title">Registros de movimentacao</div>
                    <div class="mov-sub">Visualize, edite ou remova entradas de frota.</div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover mov-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipo / Status</th>
                        <th>Usuario</th>
                        <th>Veiculo</th>
                        <th>Grupo</th>
                        <th>Datas</th>
                        <th class="text-center" style="width: 160px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimentacaos as $movimentacao)
                        <tr>
                            <td>{{ $movimentacao->id }}</td>
                            <td>
                                <div class="mov-type">{{ $movimentacao->tipoMovimentacao?->nome ?? '-' }}</div>
                                <span class="badge badge-pill bg-{{ $movimentacao->statusMovimentacao?->cor ?? 'secondary' }}">
                                    {{ $movimentacao->statusMovimentacao?->nome ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $movimentacao->usuario?->nome ?? '-' }}</td>
                            <td>
                                @if($movimentacao->veiculo)
                                    <div class="mov-vehicle">
                                        <div class="mov-vehicle__main">
                                            <i class="fas fa-car"></i>
                                            <span>{{ $movimentacao->veiculo->modelo->nome ?? 'Modelo' }}</span>
                                        </div>
                                        <div class="mov-vehicle__meta">
                                            <span>Placa: {{ $movimentacao->veiculo->placa ?? '-' }}</span>
                                            <span>Ano: {{ $movimentacao->veiculo->ano ?? 'N/A' }}</span>
                                            <span>Cor: {{ $movimentacao->veiculo->cor ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Nao definido</span>
                                @endif
                            </td>
                            <td>
                                @if($movimentacao->veiculo && $movimentacao->veiculo->grupoFrota)
                                    {{ $movimentacao->veiculo->grupoFrota->nome }}
                                @else
                                    <span class="text-muted">Nao definido</span>
                                @endif
                            </td>
                            <td>
                                <div class="mov-date">
                                    <span>Registro</span>
                                    <strong>
                                        @if($movimentacao->data_registro)
                                            {{ Carbon\Carbon::parse($movimentacao->data_registro)->format('d/m/Y H:i') }}
                                        @else
                                            --
                                        @endif
                                    </strong>
                                </div>
                                <div class="mov-date">
                                    <span>Saida</span>
                                    <strong>
                                        @if($movimentacao->data_saida)
                                            {{ Carbon\Carbon::parse($movimentacao->data_saida)->format('d/m/Y H:i') }}
                                        @else
                                            --
                                        @endif
                                    </strong>
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

@section('css')
<style>
    .mov-summary {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .mov-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        min-width: 180px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }
    .mov-summary-label {
        font-size: .8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 6px;
    }
    .mov-summary-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .mov-summary-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .movimentacao-card {
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .mov-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .mov-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .mov-sub {
        color: #64748b;
        font-size: .9rem;
        margin-top: 4px;
    }
    .mov-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .mov-pagination {
        display: flex;
        align-items: center;
    }
    .mov-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #475569;
    }
    .mov-table tbody td {
        vertical-align: middle;
    }
    .mov-type {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .mov-vehicle {
        display: grid;
        gap: 6px;
    }
    .mov-vehicle__main {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #0f172a;
    }
    .mov-vehicle__meta {
        display: grid;
        gap: 4px;
        font-size: .85rem;
        color: #64748b;
    }
    .mov-date {
        display: grid;
        gap: 2px;
        font-size: .82rem;
        color: #64748b;
        margin-bottom: 6px;
    }
    .mov-date strong {
        font-size: .86rem;
        color: #0f172a;
        font-weight: 600;
    }
    @media (max-width: 991px) {
        .mov-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .mov-actions {
            width: 100%;
            justify-content: space-between;
        }
        .mov-summary-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@stop

@section('js')
    <script></script>
@stop
