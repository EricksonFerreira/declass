@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('financeiro.fluxo-caixa.index') }}" class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label for="descricao" class="fc-label">Descrição</label>
                        <input type="text" name="descricao" id="descricao" class="form-control" value="{{ request('descricao') }}" placeholder="Buscar descrição">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="tipo" class="fc-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="receita" {{ request('tipo') === 'receita' ? 'selected' : '' }}>Receita</option>
                            <option value="despesa" {{ request('tipo') === 'despesa' ? 'selected' : '' }}>Despesa</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="status" class="fc-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ request('status') === 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="categoria_id" class="fc-label">Categoria</label>
                        <select name="categoria_id" id="categoria_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1"><i class="fas fa-filter"></i> Filtrar</button>
                        <a href="{{ route('financeiro.fluxo-caixa.index') }}" class="btn btn-outline-secondary fc-btn">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h3 class="fc-title"><i class="fas fa-receipt"></i><span>Lançamentos<span class="fc-subtitle">Entradas, saídas, vencimentos e vínculos</span></span></h3>
                    <div class="fc-actions">
                        <a href="{{ route('financeiro.fluxo-caixa.categorias.index') }}" class="btn btn-outline-primary fc-btn">
                            <i class="fas fa-tags"></i> Categorias
                        </a>
                        <a href="{{ route('financeiro.fluxo-caixa.create') }}" class="btn btn-success fc-btn">
                            <i class="fas fa-plus"></i> Novo lançamento
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover fc-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Vencimento</th>
                                <th class="py-3">Descrição</th>
                                <th class="py-3">Categoria</th>
                                <th class="py-3">Tipo</th>
                                <th class="py-3">Valor</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Vínculo</th>
                                <th class="py-3 text-right px-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lancamentos as $lancamento)
                                @php
                                    $codigoReserva = $lancamento->reserva?->codigoReserva?->codigo ?? $lancamento->reserva?->id;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">{{ $lancamento->data_vencimento ? $lancamento->data_vencimento->format('d/m/Y') : '-' }}</td>
                                    <td class="py-3">
                                        <span class="font-weight-bold text-dark">{{ $lancamento->descricao }}</span>
                                        @if($lancamento->data_pagamento)
                                            <div class="text-xs text-muted">Pago em: {{ $lancamento->data_pagamento->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 text-secondary">{{ $lancamento->categoria->nome ?? '-' }}</td>
                                    <td class="py-3">
                                        <span class="fc-badge {{ $lancamento->tipo === 'receita' ? 'fc-badge--receita' : 'fc-badge--despesa' }}">
                                            {{ $lancamento->tipo === 'receita' ? 'Receita' : 'Despesa' }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-weight-bold {{ $lancamento->tipo === 'receita' ? 'text-success' : 'text-danger' }}">
                                        {{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($lancamento->valor, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3">
                                        <span class="fc-badge fc-badge--{{ $lancamento->status }}">
                                            {{ ucfirst($lancamento->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-xs">
                                        @if($lancamento->veiculo)
                                            <span class="font-weight-bold text-primary"><i class="fas fa-car mr-1"></i>{{ $lancamento->veiculo->placa_licenca }}</span>
                                        @elseif($lancamento->reserva)
                                            <span class="font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i>Reserva #{{ $codigoReserva }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right px-4">
                                        <a href="{{ route('financeiro.fluxo-caixa.edit', $lancamento->id) }}" class="btn btn-sm btn-outline-primary mr-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('financeiro.fluxo-caixa.destroy', $lancamento->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este lançamento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">Nenhum lançamento encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lancamentos->hasPages())
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
                        {{ $lancamentos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('financeiro.fluxo_caixa._styles')
@stop
