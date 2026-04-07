@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-12 px-0">
    <div class="card card-outline card-danger">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>{{ $titulo }}
            </h3>
            @can('multa.create')
            <a href="{{ route('multa.create') }}" class="btn btn-sm btn-danger">
                <i class="fas fa-plus mr-1"></i> Nova Multa
            </a>
            @endcan
        </div>

        {{-- Filtros --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('multa.index') }}" id="form-filtros">
                <div class="row">
                    <div class="col-md-2">
                        <label class="small mb-0">Data infracao (de)</label>
                        <input type="date" name="data_inicio" class="form-control form-control-sm"
                               value="{{ $filtros['data_inicio'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Data infracao (ate)</label>
                        <input type="date" name="data_fim" class="form-control form-control-sm"
                               value="{{ $filtros['data_fim'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Placa</label>
                        <input type="text" name="placa" class="form-control form-control-sm"
                               value="{{ $filtros['placa'] ?? '' }}" placeholder="ABC-1234">
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Status Multa</label>
                        <select name="status_multa" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach(\App\Models\Multa::STATUS_MULTA as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['status_multa'] ?? '') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small mb-0">Status Desconto</label>
                        <select name="status_desconto" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach(\App\Models\Multa::STATUS_DESCONTO as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['status_desconto'] ?? '') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-50">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('multa.index') }}" class="btn btn-sm btn-secondary w-50">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Acoes em lote --}}
        <div class="card-body border-bottom py-2 d-flex justify-content-end">
            @can('multa.export')
            <a href="{{ route('multa.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv mr-1"></i> Exportar CSV
            </a>
            @endcan
        </div>

        <div class="card-body p-0">
            @if($multas->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                    Nenhuma multa encontrada para os filtros aplicados.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Reserva</th>
                            <th>Placa</th>
                            <th>Cliente</th>
                            <th>Orgao</th>
                            <th>Data Infracao</th>
                            <th>Vencimento</th>
                            <th class="text-right">Valor Final</th>
                            <th>Status Multa</th>
                            <th>Status Desconto</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($multas as $multa)
                        @php
                            $badgeMulta = match($multa->status_multa) {
                                'paga'       => 'success',
                                'cancelada'  => 'secondary',
                                'deferida'   => 'info',
                                'indeferida' => 'warning',
                                default      => 'danger',
                            };
                            $badgeDesc = match($multa->status_desconto) {
                                'descontado'   => 'success',
                                'nao_descontar' => 'secondary',
                                'estornado'    => 'warning',
                                'pendente'     => 'info',
                                default        => 'light',
                            };
                        @endphp
                        <tr>
                            <td>{{ $multa->id }}</td>
                            <td>
                                @if($multa->reserva_id)
                                    <a href="{{ route('reserva.show', $multa->reserva_id) }}">#{{ $multa->reserva_id }}</a>
                                @else —
                                @endif
                            </td>
                            <td>{{ $multa->placa_momento ?? '—' }}</td>
                            <td>{{ $multa->cliente->nome ?? '—' }}</td>
                            <td>{{ $multa->orgao_autuador ?? '—' }}</td>
                            <td>{{ $multa->data_hora_infracao?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $multa->data_vencimento?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right">R$ {{ number_format((float)$multa->valor_final, 2, ',', '.') }}</td>
                            <td><span class="badge badge-{{ $badgeMulta }}">{{ $multa->label_status_multa }}</span></td>
                            <td><span class="badge badge-{{ $badgeDesc }}">{{ $multa->label_status_desconto }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('multa.show', $multa->id) }}" class="btn btn-xs btn-outline-primary" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('multa.edit')
                                <a href="{{ route('multa.edit', $multa->id) }}" class="btn btn-xs btn-outline-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('multa.delete')
                                <form method="POST" action="{{ route('multa.destroy', $multa->id) }}" class="d-inline"
                                      onsubmit="return confirm('Confirma exclusao da multa #{{ $multa->id }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $multas->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@stop
