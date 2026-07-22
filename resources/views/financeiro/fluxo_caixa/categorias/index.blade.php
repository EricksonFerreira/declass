@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="fc-page">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card fc-card h-100">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h3 class="fc-title"><i class="fas fa-tags"></i><span>Categorias existentes<span class="fc-subtitle">Classificação das receitas e despesas</span></span></h3>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover fc-table">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">Nome</th>
                                        <th class="py-3">Tipo</th>
                                        <th class="py-3">Descrição</th>
                                        <th class="py-3 text-right px-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categorias as $categoria)
                                        <tr>
                                            <td class="px-4 py-3 font-weight-bold">{{ $categoria->nome }}</td>
                                            <td class="py-3">
                                                <span class="fc-badge {{ $categoria->tipo === 'receita' ? 'fc-badge--receita' : 'fc-badge--despesa' }}">
                                                    {{ $categoria->tipo === 'receita' ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-secondary">{{ $categoria->descricao ?? '-' }}</td>
                                            <td class="py-3 text-right px-4">
                                                <form action="{{ route('financeiro.fluxo-caixa.categorias.destroy', $categoria->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir esta categoria?');" class="d-inline">
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
                                            <td colspan="4" class="text-center py-5 text-muted">Nenhuma categoria cadastrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h3 class="fc-title"><i class="fas fa-folder-plus"></i><span>Nova categoria<span class="fc-subtitle">Crie uma classificação financeira</span></span></h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('financeiro.fluxo-caixa.categorias.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="nome" class="fc-label">Nome da categoria</label>
                                <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Combustível, manutenção">
                                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="tipo" class="fc-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                    <option value="" disabled selected>Selecione o tipo</option>
                                    <option value="receita" {{ old('tipo') === 'receita' ? 'selected' : '' }}>Receita (entrada)</option>
                                    <option value="despesa" {{ old('tipo') === 'despesa' ? 'selected' : '' }}>Despesa (saída)</option>
                                </select>
                                @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="descricao" class="fc-label">Descrição</label>
                                <textarea name="descricao" id="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror" placeholder="Descrição opcional">{{ old('descricao') }}</textarea>
                                @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary fc-btn w-100">
                                <i class="fas fa-save"></i> Salvar categoria
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('financeiro.fluxo_caixa._styles')
@stop
