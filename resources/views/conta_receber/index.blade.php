@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Contas a receber da escola e da rede" :create-url="auth()->user()->hasPermissao('conta_receber.create') ? route('conta_receber.create') : null" :create-can="'conta_receber.create'" />
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
                        <th>Aluno</th>
                        <th>Categoria</th>
                        <th>Escola</th>
                        <th>Vencimento</th>
                        <th class="text-right">Valor</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contas as $conta)
                        @php $situacao = $conta->situacao; @endphp
                        <tr class="{{ $situacao === 'atrasada' ? 'table-danger' : '' }}">
                            <td class="font-weight-bold">{{ $conta->descricao }}</td>
                            <td>{{ $conta->aluno->pessoa->nome ?? '—' }}</td>
                            <td><span class="badge badge-light border">{{ $conta->categoriaLabel() }}</span></td>
                            <td>{{ $conta->escola->nome ?? 'Rede toda' }}</td>
                            <td>{{ $conta->data_vencimento->format('d/m/Y') }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                            <td>
                                @php $cor = ['prevista' => 'warning', 'atrasada' => 'danger', 'recebida' => 'success', 'cancelada' => 'secondary'][$situacao] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ ucfirst($situacao) }}</span>
                                @if($conta->status === 'recebida')
                                    <br><small class="text-muted">{{ $conta->data_recebimento?->format('d/m/Y') }} · {{ \App\Models\ContaReceber::FORMAS_RECEBIMENTO[$conta->forma_recebimento] ?? $conta->forma_recebimento }}</small>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @if($conta->status === 'prevista')
                                    @can('conta_receber.edit')
                                        <button type="button" class="btn btn-xs btn-success" title="Baixar (receber)"
                                                data-toggle="modal" data-target="#modal-receber-{{ $conta->id }}">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <a href="{{ route('conta_receber.edit', $conta->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                        <form method="POST" action="{{ route('conta_receber.cancelar', $conta->id) }}" class="d-inline"
                                              onsubmit="return confirm('Cancelar a conta a receber &quot;{{ addslashes($conta->descricao) }}&quot;?');">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-secondary" title="Cancelar conta"><i class="fa fa-ban"></i></button>
                                        </form>
                                    @endcan
                                    @can('conta_receber.destroy')
                                        <x-delete-button :action="route('conta_receber.destroy', $conta->id)" titulo="Excluir" mensagem="Excluir a conta a receber &quot;{{ $conta->descricao }}&quot;?" />
                                    @endcan
                                @else
                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>
                                Nenhuma conta a receber encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($contas->hasPages())
        <div class="card-footer">
            {{ $contas->links() }}
        </div>
    @endif
</div>

{{-- Modais de baixa de recebimento --}}
@foreach($contas->where('status', 'prevista') as $conta)
    <div class="modal fade" id="modal-receber-{{ $conta->id }}" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" action="{{ route('conta_receber.receber', $conta->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Baixar recebimento</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">{{ $conta->descricao }} — R$ {{ number_format($conta->valor, 2, ',', '.') }}</p>
                        <div class="form-group">
                            <label>Forma de recebimento</label>
                            <select name="forma_recebimento" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach(\App\Models\ContaReceber::FORMAS_RECEBIMENTO as $valor => $label)
                                    <option value="{{ $valor }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Data do recebimento</label>
                            <input type="date" name="data_recebimento" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Voltar</button>
                        <button type="submit" class="btn btn-success btn-sm">Confirmar recebimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@stop
