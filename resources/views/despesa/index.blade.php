@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Contas a pagar da escola e da rede" :create-url="auth()->user()->hasPermissao('despesa.create') ? route('despesa.create') : null" :create-can="'despesa.create'" />
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Descrição</th>
                        <th>Fornecedor</th>
                        <th>Categoria</th>
                        <th>Escola</th>
                        <th>Vencimento</th>
                        <th class="text-right">Valor</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($despesas as $despesa)
                        @php $situacao = $despesa->situacao; @endphp
                        <tr class="{{ $situacao === 'atrasada' ? 'table-danger' : '' }}">
                            <td class="font-weight-bold">{{ $despesa->descricao }}</td>
                            <td>{{ $despesa->fornecedor ?: '—' }}</td>
                            <td><span class="badge badge-light border">{{ $despesa->categoriaLabel() }}</span></td>
                            <td>{{ $despesa->escola->nome ?? 'Rede toda' }}</td>
                            <td>{{ $despesa->data_vencimento->format('d/m/Y') }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($despesa->valor, 2, ',', '.') }}</td>
                            <td>
                                @php $cor = ['prevista' => 'warning', 'atrasada' => 'danger', 'paga' => 'success', 'cancelada' => 'secondary'][$situacao] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ ucfirst($situacao) }}</span>
                                @if($despesa->status === 'paga')
                                    <br><small class="text-muted">{{ $despesa->data_pagamento?->format('d/m/Y') }} · {{ \App\Models\Despesa::FORMAS_PAGAMENTO[$despesa->forma_pagamento] ?? $despesa->forma_pagamento }}</small>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @if($despesa->status === 'prevista')
                                    @can('despesa.edit')
                                        <button type="button" class="btn btn-xs btn-success" title="Baixar (pagar)"
                                                data-toggle="modal" data-target="#modal-pagar-{{ $despesa->id }}">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <a href="{{ route('despesa.edit', $despesa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                        <form method="POST" action="{{ route('despesa.cancelar', $despesa->id) }}" class="d-inline"
                                              onsubmit="return confirm('Cancelar a despesa &quot;{{ addslashes($despesa->descricao) }}&quot;?');">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-secondary" title="Cancelar despesa"><i class="fa fa-ban"></i></button>
                                        </form>
                                    @endcan
                                    @can('despesa.destroy')
                                        <x-delete-button :action="route('despesa.destroy', $despesa->id)" titulo="Excluir" mensagem="Excluir a despesa &quot;{{ $despesa->descricao }}&quot;?" />
                                    @endcan
                                @else
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-file-invoice-dollar fa-2x mb-2 d-block"></i>
                                Nenhuma despesa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($despesas->hasPages())
        <div class="card-footer">
            {{ $despesas->links() }}
        </div>
    @endif
</div>

{{-- Modais de baixa de pagamento --}}
@foreach($despesas->where('status', 'prevista') as $despesa)
    <div class="modal fade" id="modal-pagar-{{ $despesa->id }}" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" action="{{ route('despesa.pagar', $despesa->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Baixar pagamento</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">{{ $despesa->descricao }} — R$ {{ number_format($despesa->valor, 2, ',', '.') }}</p>
                        <div class="form-group">
                            <label>Forma de pagamento</label>
                            <select name="forma_pagamento" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach(\App\Models\Despesa::FORMAS_PAGAMENTO as $valor => $label)
                                    <option value="{{ $valor }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Data do pagamento</label>
                            <input type="date" name="data_pagamento" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Voltar</button>
                        <button type="submit" class="btn btn-success btn-sm">Confirmar pagamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@stop
