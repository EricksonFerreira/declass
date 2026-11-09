@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Cobranças mensais das matrículas" :create-url="auth()->user()->hasPermissao('mensalidade.create') ? route('mensalidade.create') : null" :create-can="'mensalidade.create'" />
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php($din = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.'))
<div class="row">
    <div class="col-md-3 col-6 mb-2">
        <div class="card mb-0 border"><div class="card-body py-2 px-3 text-center">
            <div class="h4 mb-0 text-success">{{ $din($resumo['recebido']) }}</div>
            <small class="text-muted">Recebido</small>
        </div></div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card mb-0 border"><div class="card-body py-2 px-3 text-center">
            <div class="h4 mb-0 text-info">{{ $din($resumo['a_receber']) }}</div>
            <small class="text-muted">A receber</small>
        </div></div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card mb-0 border"><div class="card-body py-2 px-3 text-center">
            <div class="h4 mb-0 text-danger">{{ $din($resumo['vencido']) }}</div>
            <small class="text-muted">Vencido (R$)</small>
        </div></div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card mb-0 border"><div class="card-body py-2 px-3 text-center">
            <div class="h4 mb-0 text-danger">{{ $totalInadimplente }}</div>
            <small class="text-muted">Cobranças vencidas</small>
        </div></div>
    </div>
</div>

@if($alunoSel)
    <div class="alert alert-info d-flex align-items-center" style="gap:.6rem;">
        <i class="fas fa-user-graduate fa-lg"></i>
        <div class="flex-grow-1">
            <strong>Extrato: {{ $alunoSel->pessoa->nome ?? 'Aluno #'.$alunoSel->id }}</strong>
            <div class="small">Mostrando apenas as mensalidades deste aluno.</div>
        </div>
        <a href="{{ route('mensalidade.removerFiltro', ['atributo' => 'aluno_id']) }}" class="btn btn-sm btn-outline-info">Ver todos</a>
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header font-weight-bold">Gerar mensalidades de uma competência</div>
            <div class="card-body">
                <form method="POST" action="{{ route('mensalidade.gerar') }}" class="form-inline">
                    @csrf
                    <div class="form-group mr-2">
                        <label class="mr-1">Competência</label>
                        <input type="month" name="competencia" class="form-control" value="{{ now()->format('Y-m') }}" required>
                    </div>
                    <div class="form-group mr-2">
                        <label class="mr-1">Vencimento</label>
                        <input type="date" name="vencimento" class="form-control" required>
                    </div>
                    @can('mensalidade.gerar')
                        <button type="submit" class="btn btn-primary">Gerar</button>
                    @endcan
                </form>
                <small class="text-muted">Gera cobranças para matrículas ativas com plano de mensalidade. Competências já existentes são ignoradas.</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnExtratoAluno">
            <i class="fa fa-user-graduate mr-1"></i> Extrato do aluno
        </button>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Competência</th>
                        <th>Plano</th>
                        <th class="text-right">Valor</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mensalidades as $mensalidade)
                        @php $situacao = $mensalidade->situacao; @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $mensalidade->matricula->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $mensalidade->matricula->turma->nome ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $mensalidade->competencia)->format('m/Y') }}</td>
                            <td>{{ $mensalidade->planoMensalidade->nome ?? '—' }}</td>
                            <td class="text-right">R$ {{ number_format($mensalidade->valor, 2, ',', '.') }}</td>
                            <td>{{ $mensalidade->vencimento->format('d/m/Y') }}</td>
                            <td>
                                @php $cor = ['pendente' => 'warning', 'vencida' => 'danger', 'paga' => 'success', 'cancelada' => 'secondary'][$situacao] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ ucfirst($situacao) }}</span>
                            </td>
                            <td>
                                @if($mensalidade->status === 'paga')
                                    <small>{{ $mensalidade->data_pagamento?->format('d/m/Y') }} · {{ \App\Models\Mensalidade::FORMAS_PAGAMENTO[$mensalidade->forma_pagamento] ?? $mensalidade->forma_pagamento }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">
                                @if($mensalidade->status === 'pendente')
                                    @can('mensalidade.edit')
                                        <form method="POST" action="{{ route('mensalidade.baixar', $mensalidade->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="forma_pagamento" value="pix">
                                            <button type="submit" class="btn btn-xs btn-success" title="Baixar (receber) — registra Pix"><i class="fa fa-check"></i></button>
                                        </form>
                                    @endcan
                                @endif
                                @if($mensalidade->status === 'paga')
                                    @can('mensalidade.recibo')
                                        <a href="{{ route('mensalidade.recibo', $mensalidade->id) }}" class="btn btn-xs btn-outline-primary" title="Recibo de pagamento"><i class="fa fa-file-invoice"></i></a>
                                    @endcan
                                @endif
                                @if($mensalidade->boleto_path)
                                    @can('mensalidade.edit')
                                        <a href="{{ route('mensalidade.boleto', $mensalidade->id) }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="Ver boleto anexado"><i class="fa fa-barcode"></i></a>
                                    @endcan
                                @endif
                                @can('mensalidade.edit')
                                    <a href="{{ route('mensalidade.edit', $mensalidade->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('mensalidade.destroy')
                                    <x-delete-button :action="route('mensalidade.destroy', $mensalidade->id)" titulo="Excluir" mensagem="Excluir a mensalidade de &quot;{{ $mensalidade->matricula->aluno->pessoa->nome ?? 'aluno' }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block"></i>
                                Nenhuma mensalidade encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($mensalidades->hasPages())
        <div class="card-footer">
            {{ $mensalidades->links() }}
        </div>
    @endif
</div>

@include('partials.entity-picker')
@stop

@push('js')
<script>
$(function () {
    if (!window.EntityPicker) return;
    EntityPicker.registrar('aluno', {
        titulo: 'Extrato do aluno',
        dados: @json($alunosPicker ?? []),
        filtros: [],
        onSelect: function (d) {
            // aplica o filtro aluno_id na sessao e recarrega o extrato
            window.location.href = "{{ route('mensalidade.filtro') }}?aluno_id=" + d.id;
        },
    });
    document.getElementById('btnExtratoAluno').addEventListener('click', function () {
        EntityPicker.abrir('aluno');
    });
});
</script>
@endpush
