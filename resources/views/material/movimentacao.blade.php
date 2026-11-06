@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Registre entradas, saídas e ajustes de estoque" />
@stop

@section('content')
@php
    $fmtQtd = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $corTipo = ['entrada' => 'success', 'saida' => 'danger', 'ajuste' => 'info'];
@endphp

<div class="row">
    <div class="col-lg-4">
        <div class="card card-primary card-outline">
            <div class="card-header font-weight-bold"><i class="fa fa-exchange-alt"></i> Nova movimentação</div>
            <div class="card-body">
                @can('material.movimentacao.store')
                <form method="POST" action="{{ route('material.movimentacao.store') }}">
                    @csrf

                    <div class="form-group">
                        <label for="material_id">Material</label>
                        <select name="material_id" id="material_id" class="form-control" required>
                            <option value="">Selecione…</option>
                            @foreach($materiais as $mat)
                                <option value="{{ $mat->id }}"
                                    data-saldo="{{ $mat->estoqueFormatado() }}"
                                    {{ (string) old('material_id') === (string) $mat->id ? 'selected' : '' }}>
                                    {{ $mat->nome }} — saldo {{ $mat->estoqueFormatado() }}{{ $mat->escola ? ' · '.$mat->escola->nome : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted" id="saldo-atual"></small>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control" required>
                            @foreach($tipos as $valor => $label)
                                <option value="{{ $valor }}" {{ old('tipo') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantidade">Quantidade</label>
                        <input type="number" name="quantidade" id="quantidade" class="form-control"
                               value="{{ old('quantidade') }}" min="0.01" step="0.01" required
                               placeholder="{{ old('tipo') === 'ajuste' ? 'Novo saldo absoluto' : 'Quantidade' }}">
                        <small class="form-text text-muted" id="ajuda-quantidade"></small>
                    </div>

                    <div class="form-group">
                        <label for="data">Data</label>
                        <input type="date" name="data" id="data" class="form-control"
                               value="{{ old('data', now()->toDateString()) }}">
                    </div>

                    <div class="form-group">
                        <label for="motivo">Motivo / observação</label>
                        <textarea name="motivo" id="motivo" rows="2" class="form-control"
                                  maxlength="1000" placeholder="Ex.: reposição, uso em sala, inventário…">{{ old('motivo') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-check"></i> Registrar movimentação
                    </button>
                </form>
                @else
                    <p class="text-muted mb-0">Você não tem permissão para registrar movimentações.</p>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Materiais com estoque</div>
            <div class="card-body p-0" style="max-height:420px; overflow-y:auto;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light sticky-top">
                        <tr><th>Material</th><th class="text-right">Saldo</th></tr>
                    </thead>
                    <tbody>
                        @forelse($materiais as $mat)
                            <tr>
                                <td>
                                    {{ $mat->nome }}
                                    <br><small class="text-muted">{{ $mat->categoriaLabel() }}{{ $mat->escola ? ' · '.$mat->escola->nome : '' }}</small>
                                    @if($mat->baixoEstoque()) <span class="badge badge-danger">baixo</span>@endif
                                </td>
                                <td class="text-right font-weight-bold {{ $mat->baixoEstoque() ? 'text-danger' : '' }}">{{ $mat->estoqueFormatado() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">Nenhum material ativo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header font-weight-bold">Movimentações recentes</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Data</th>
                                <th>Material</th>
                                <th>Tipo</th>
                                <th class="text-right">Qtd</th>
                                <th class="text-right">Saldo</th>
                                <th>Motivo</th>
                                <th>Usuário</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimentacoes as $mov)
                                <tr>
                                    <td class="text-nowrap">{{ $mov->data?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="font-weight-bold">{{ $mov->material->nome ?? '—' }}</td>
                                    <td><span class="badge badge-{{ $corTipo[$mov->tipo] ?? 'secondary' }}">{{ $mov->tipoLabel() }}</span></td>
                                    <td class="text-right">{{ $mov->quantidadeFormatada() }}</td>
                                    <td class="text-right text-nowrap">
                                        {{ $fmtQtd($mov->saldo_anterior) }} → <strong>{{ $fmtQtd($mov->saldo_novo) }}</strong>
                                    </td>
                                    <td><small class="text-muted">{{ $mov->motivo ?: '—' }}</small></td>
                                    <td><small>{{ $mov->usuario->name ?? '—' }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-exchange-alt fa-2x mb-2 d-block"></i>
                                        Nenhuma movimentação registrada ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted small">Últimas 30 movimentações de todos os materiais.</div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
(function () {
    var sel = document.getElementById('material_id');
    var saldo = document.getElementById('saldo-atual');
    var tipo = document.getElementById('tipo');
    var qtd = document.getElementById('quantidade');
    var ajuda = document.getElementById('ajuda-quantidade');

    function atualiza() {
        var opt = sel.options[sel.selectedIndex];
        saldo.textContent = opt && opt.dataset.saldo ? 'Saldo atual: ' + opt.dataset.saldo : '';
        if (tipo.value === 'ajuste') {
            ajuda.textContent = 'Ajuste define o novo saldo absoluto (ex.: inventário).';
        } else {
            ajuda.textContent = tipo.value === 'saida' ? 'A saída não pode deixar o saldo negativo.' : '';
        }
    }

    sel.addEventListener('change', atualiza);
    tipo.addEventListener('change', atualiza);
    atualiza();
})();
</script>
@stop
