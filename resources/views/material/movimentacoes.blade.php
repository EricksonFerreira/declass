@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Movimentações — '.$material->nome" subtitle="Histórico de entradas, saídas e ajustes de saldo" />
@stop

@push('css')
<style>
    .mv-saldo { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem; padding:1rem; }
    .mv-saldo .num { font-size:2.1rem; font-weight:800; line-height:1; }
    .mv-saldo small { color:#bfdbfe; }
    .mv-tipo-btn { flex:1; border:2px solid #e5e7eb; border-radius:10px; padding:.55rem .3rem; text-align:center; cursor:pointer; font-size:.78rem; font-weight:600; background:#fff; }
    .mv-tipo-btn i { display:block; font-size:1rem; margin-bottom:.15rem; }
    .mv-tipo-btn.on-entrada { border-color:#22c55e; background:#f0fdf4; color:#15803d; }
    .mv-tipo-btn.on-saida { border-color:#ef4444; background:#fef2f2; color:#b91c1c; }
    .mv-tipo-btn.on-ajuste { border-color:#f59e0b; background:#fffbeb; color:#b45309; }
    .mv-qtd-pos { color:#15803d; font-weight:700; }
    .mv-qtd-neg { color:#b91c1c; font-weight:700; }
    .mv-saldo-flow { font-size:.78rem; color:#6b7280; }
</style>
@endpush

@section('content')
@php $fmtQtd = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ','); @endphp
<div class="row">
    <div class="col-md-4">
        <div class="mv-saldo mb-3">
            <small class="text-uppercase font-weight-bold d-block">Saldo atual</small>
            <div class="num {{ $material->baixoEstoque() ? 'text-warning' : '' }}">{{ $material->estoqueFormatado() }}</div>
            <small>
                Mínimo: {{ number_format((float) $material->estoque_minimo, 0, ',', '.') }} {{ $material->unidade_medida }}
                · {{ $material->escola->nome ?? 'Rede toda' }}
            </small>
            @if($material->baixoEstoque())
                <div class="mt-2"><span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i> Estoque baixo</span></div>
            @endif
        </div>

        @can('material.movimentar')
        <div class="card">
            <div class="card-header font-weight-bold"><i class="fa fa-exchange-alt mr-1"></i>Nova movimentação</div>
            <div class="card-body">
                <form method="POST" action="{{ route('material.movimentar', $material->id) }}">
                    @csrf
                    <input type="hidden" name="tipo" id="mv-tipo" value="{{ old('tipo', 'entrada') }}">
                    <div class="d-flex mb-3" style="gap:.4rem;" id="mv-tipos">
                        @foreach($tipos as $valor => $label)
                            <label class="mv-tipo-btn mb-0" data-tipo="{{ $valor }}">
                                <i class="fa {{ ['entrada' => 'fa-arrow-down', 'saida' => 'fa-arrow-up', 'ajuste' => 'fa-balance-scale'][$valor] ?? 'fa-exchange-alt' }}"></i>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label>Quantidade <span class="text-danger">*</span></label>
                        <input type="number" name="quantidade" class="form-control" step="0.01" min="0.01" required
                            value="{{ old('quantidade') }}" placeholder="Quantidade">
                        <small class="text-muted" id="mv-ajuda"></small>
                    </div>
                    <div class="form-group">
                        <label>Data</label>
                        <input type="date" name="data" class="form-control" value="{{ old('data', now()->toDateString()) }}">
                    </div>
                    <div class="form-group mb-2">
                        <label>Motivo</label>
                        <textarea name="motivo" class="form-control" rows="2" maxlength="1000"
                            placeholder="Ex.: Compra fornecedor X, uso na cantina...">{{ old('motivo') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-check"></i> Registrar</button>
                </form>
            </div>
        </div>
        @endcan
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold"><i class="fa fa-history mr-1"></i>Extrato de movimentações ({{ $movimentacoes->total() ?? $movimentacoes->count() }})</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th class="text-right">Quantidade</th>
                                <th class="text-right">Saldo após</th>
                                <th>Motivo</th>
                                <th>Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimentacoes as $mov)
                                <tr>
                                    <td class="text-nowrap">{{ $mov->data->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ ['entrada' => 'success', 'saida' => 'danger', 'ajuste' => 'warning'][$mov->tipo] ?? 'secondary' }}">
                                            <i class="fa {{ ['entrada' => 'fa-arrow-down', 'saida' => 'fa-arrow-up', 'ajuste' => 'fa-balance-scale'][$mov->tipo] ?? 'fa-exchange-alt' }}"></i>
                                            {{ $mov->tipoLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-right {{ $mov->tipo === 'saida' ? 'mv-qtd-neg' : ($mov->tipo === 'entrada' ? 'mv-qtd-pos' : '') }}">
                                        {{ $mov->tipo === 'saida' ? '−' : ($mov->tipo === 'entrada' ? '+' : '=') }}{{ $mov->quantidadeFormatada() }}
                                    </td>
                                    <td class="text-right">
                                        <span class="mv-saldo-flow">{{ $fmtQtd($mov->saldo_anterior) }} →</span>
                                        <strong>{{ $fmtQtd($mov->saldo_novo) }}</strong>
                                    </td>
                                    <td><small class="text-muted">{{ $mov->motivo ?: '—' }}</small></td>
                                    <td><small>{{ $mov->usuario->name ?? '—' }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-exchange-alt fa-2x mb-2 d-block"></i>
                                        Nenhuma movimentação registrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($movimentacoes, 'hasPages') && $movimentacoes->hasPages())
                <div class="card-footer">{{ $movimentacoes->links() }}</div>
            @endif
        </div>
    </div>
</div>
@stop

@section('js')
<script>
(function () {
    var input = document.getElementById('mv-tipo');
    var btns = document.querySelectorAll('.mv-tipo-btn');
    var ajuda = document.getElementById('mv-ajuda');

    function marca() {
        btns.forEach(function (b) {
            b.className = 'mv-tipo-btn mb-0' + (b.dataset.tipo === input.value ? ' on-' + b.dataset.tipo : '');
        });
        ajuda.textContent = input.value === 'ajuste' ? 'Ajuste define o novo saldo absoluto (ex.: inventário).'
            : (input.value === 'saida' ? 'A saída não pode deixar o saldo negativo.' : '');
    }
    btns.forEach(function (b) {
        b.addEventListener('click', function () { input.value = b.dataset.tipo; marca(); });
    });
    marca();
})();
</script>
@stop
