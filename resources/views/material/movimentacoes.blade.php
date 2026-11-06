@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Movimentações — '.$material->nome" subtitle="Histórico de entradas, saídas e ajustes de saldo" />
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <small class="text-muted text-uppercase">Saldo atual</small>
                <div class="h3 font-weight-bold {{ $material->baixoEstoque() ? 'text-danger' : 'text-success' }}">
                    {{ $material->estoqueFormatado() }}
                </div>
                <small class="text-muted">
                    Mínimo: {{ number_format((float) $material->estoque_minimo, 0, ',', '.') }} {{ $material->unidade_medida }}
                    · {{ $material->escola->nome ?? 'Rede toda' }}
                </small>
                @if($material->baixoEstoque())
                    <div class="mt-2"><span class="badge badge-danger">Estoque baixo</span></div>
                @endif
            </div>
        </div>

        @can('material.movimentar')
        <div class="card">
            <div class="card-header font-weight-bold">Nova movimentação</div>
            <div class="card-body">
                <form method="POST" action="{{ route('material.movimentar', $material->id) }}">
                    @csrf
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" class="form-control" required>
                            @foreach($tipos as $valor => $label)
                                <option value="{{ $valor }}" {{ old('tipo') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantidade</label>
                        <input type="number" name="quantidade" class="form-control" step="0.01" min="0.01" required
                            value="{{ old('quantidade') }}" placeholder="{{ old('tipo') === 'ajuste' ? 'Novo saldo' : 'Quantidade' }}">
                        <small class="text-muted">Em "ajuste", informe o novo saldo absoluto.</small>
                    </div>
                    <div class="form-group">
                        <label>Data</label>
                        <input type="date" name="data" class="form-control" value="{{ old('data', now()->toDateString()) }}">
                    </div>
                    <div class="form-group">
                        <label>Motivo</label>
                        <textarea name="motivo" class="form-control" rows="2" maxlength="1000"
                            placeholder="Ex.: Compra fornecedor X, uso na cantina...">{{ old('motivo') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Registrar</button>
                </form>
            </div>
        </div>
        @endcan
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Histórico</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th class="text-right">Quantidade</th>
                                <th class="text-right">Saldo</th>
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
                                            {{ $mov->tipoLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-right">{{ $mov->quantidadeFormatada() }}</td>
                                    <td class="text-right text-muted">{{ number_format((float) $mov->saldo_anterior, 0, ',', '.') }} → {{ number_format((float) $mov->saldo_novo, 0, ',', '.') }}</td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($mov->motivo, 60) }}</small></td>
                                    <td><small>{{ $mov->usuario->name ?? '—' }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Nenhuma movimentação registrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($movimentacoes->hasPages())
                <div class="card-footer">{{ $movimentacoes->links() }}</div>
            @endif
        </div>
    </div>
</div>
@stop
