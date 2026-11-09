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
    $abaAtiva = old('aba', $aba ?? 'listar');
@endphp

<div class="card">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs card-header-tabs" id="mvTabs">
            <li class="nav-item"><a class="nav-link {{ $abaAtiva === 'listar' ? 'active' : '' }}" data-toggle="tab" href="#mv-listar"><i class="fa fa-list mr-1"></i>Movimentações recentes</a></li>
            @can('material.movimentacao.store')
                <li class="nav-item"><a class="nav-link {{ $abaAtiva === 'registrar' ? 'active' : '' }}" data-toggle="tab" href="#mv-registrar"><i class="fa fa-exchange-alt mr-1"></i>Nova movimentação</a></li>
            @endcan
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- LISTAR --}}
            <div class="tab-pane {{ $abaAtiva === 'listar' ? 'show active' : '' }}" id="mv-listar">
                <form method="GET" action="{{ route('material.movimentacao.index') }}" class="d-flex flex-wrap align-items-end mb-3" style="gap:.5rem;">
                    <input type="hidden" name="aba" value="listar">
                    <div class="flex-grow-1" style="min-width:200px;">
                        <label class="small text-muted mb-0">Material</label>
                        <select name="material_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($materiais as $mat)
                                <option value="{{ $mat->id }}" {{ (string) ($filtros['material_id'] ?? '') === (string) $mat->id ? 'selected' : '' }}>{{ $mat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="small text-muted mb-0">Tipo</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($tipos as $valor => $label)
                                <option value="{{ $valor }}" {{ ($filtros['tipo'] ?? '') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="small text-muted mb-0">Usuário</label>
                        <select name="usuario_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($usuariosFiltro as $uid => $unome)
                                <option value="{{ $uid }}" {{ (string) ($filtros['usuario_id'] ?? '') === (string) $uid ? 'selected' : '' }}>{{ $unome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="small text-muted mb-0">De</label>
                        <input type="date" name="de" value="{{ $filtros['de'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div>
                        <label class="small text-muted mb-0">Até</label>
                        <input type="date" name="ate" value="{{ $filtros['ate'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                        <a href="{{ route('material.movimentacao.index') }}" class="btn btn-sm btn-secondary">Limpar</a>
                    </div>
                </form>

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

                @if($movimentacoes->hasPages())
                    <div class="mt-2">{{ $movimentacoes->links() }}</div>
                @endif
            </div>

            {{-- REGISTRAR --}}
            <div class="tab-pane {{ $abaAtiva === 'registrar' ? 'show active' : '' }}" id="mv-registrar">
                <div class="row">
                    <div class="col-lg-5">
                        @can('material.movimentacao.store')
                        <form method="POST" action="{{ route('material.movimentacao.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="material_id">Material <span class="text-danger">*</span></label>
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
                                <label for="tipo">Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" id="tipo" class="form-control" required>
                                    @foreach($tipos as $valor => $label)
                                        <option value="{{ $valor }}" {{ old('tipo') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="quantidade">Quantidade <span class="text-danger">*</span></label>
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

                    <div class="col-lg-7">
                        <div class="border rounded">
                            <div class="px-2 py-2 font-weight-bold border-bottom small">Materiais com estoque</div>
                            <div style="max-height:420px; overflow-y:auto;">
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
                </div>
            </div>

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
    var ajuda = document.getElementById('ajuda-quantidade');

    function atualiza() {
        if (!sel) { return; }
        var opt = sel.options[sel.selectedIndex];
        saldo.textContent = opt && opt.dataset.saldo ? 'Saldo atual: ' + opt.dataset.saldo : '';
        if (tipo.value === 'ajuste') {
            ajuda.textContent = 'Ajuste define o novo saldo absoluto (ex.: inventário).';
        } else {
            ajuda.textContent = tipo.value === 'saida' ? 'A saída não pode deixar o saldo negativo.' : '';
        }
    }

    if (sel) {
        sel.addEventListener('change', atualiza);
        tipo.addEventListener('change', atualiza);
        atualiza();
    }
    @if($errors->any())
        $('#mvTabs a[href="#mv-registrar"]').tab('show');
    @endif
})();
</script>
@stop
