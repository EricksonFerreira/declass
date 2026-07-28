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
                <form method="GET" action="{{ route('financeiro.contas-pagar') }}" class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="fc-label" for="data_inicio">Data inicial</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="fc-label" for="data_fim">Data final</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="fc-label" for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ request('status') === 'pago' ? 'selected' : '' }}>Pago</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1"><i class="fas fa-filter"></i> Filtrar</button>
                        <a href="{{ route('financeiro.contas-pagar') }}" class="btn btn-outline-secondary fc-btn">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h3 class="fc-title"><i class="fas fa-file-invoice-dollar"></i><span>Contas a Pagar<span class="fc-subtitle">Despesas lançadas, vencimentos e pagamentos pendentes</span></span></h3>
                    <div class="fc-actions">
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
                                <th class="py-3">Data de Lançamento</th>
                                <th class="py-3">Descrição</th>
                                <th class="py-3">Categoria</th>
                                <th class="py-3">Valor</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-right px-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lancamentos as $lancamento)
                                <tr>
                                    <td class="px-4 py-3">{{ optional($lancamento->data_vencimento)->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-3">{{ $lancamento->data_lancamento?->format('d/m/Y H:i') ?? ($lancamento->data_pagamento ?? $lancamento->updated_at ?? $lancamento->created_at)?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="py-3">
                                        <span class="font-weight-bold text-dark">{{ $lancamento->descricao }}</span>
                                    </td>
                                    <td class="py-3 text-secondary">{{ $lancamento->categoria?->nome ?? '-' }}</td>
                                    <td class="py-3 font-weight-bold text-danger">{{ $moeda === 'BRL' ? 'R$' : $moeda }} {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                                    <td class="py-3"><span class="fc-badge fc-badge--{{ $lancamento->status }}">{{ ucfirst($lancamento->status) }}</span></td>
                                    <td class="py-3 text-right px-4">
                                        <a href="{{ route('financeiro.fluxo-caixa.edit', $lancamento->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5 text-muted">Nenhuma conta a pagar encontrada.</td></tr>
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
