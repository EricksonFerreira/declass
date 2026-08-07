@extends('adminlte::page')

@php
    $isEdit = isset($regra);
    $title = $isEdit ? 'Editar Regra Tributária' : 'Nova Regra Tributária';
@endphp

@section('title', $title)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$title" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    <div class="fc-page">
        <div class="card fc-card fc-form-card">
            <div class="card-header py-3">
                <h5 class="fc-title">
                    <i class="fas fa-cogs"></i>
                    <span>Formulário de Regra Tributária</span>
                </h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ $isEdit ? route('fiscal.regras.atualizar', $regra->id) : route('fiscal.regras.salvar') }}">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <!-- Tributo -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Tributo *</label>
                            <select name="tributo" class="form-control @error('tributo') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                <option value="IBS" {{ old('tributo', $regra->tributo ?? '') === 'IBS' ? 'selected' : '' }}>IBS (Imposto sobre Bens e Serviços)</option>
                                <option value="CBS" {{ old('tributo', $regra->tributo ?? '') === 'CBS' ? 'selected' : '' }}>CBS (Contribuição sobre Bens e Serviços)</option>
                                <option value="PIS" {{ old('tributo', $regra->tributo ?? '') === 'PIS' ? 'selected' : '' }}>PIS</option>
                                <option value="COFINS" {{ old('tributo', $regra->tributo ?? '') === 'COFINS' ? 'selected' : '' }}>COFINS</option>
                            </select>
                            @error('tributo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tipo de Operação -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Tipo de Operação *</label>
                            <select name="tipo_operacao" class="form-control @error('tipo_operacao') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                <option value="LOCACAO_VEICULO" {{ old('tipo_operacao', $regra->tipo_operacao ?? '') === 'LOCACAO_VEICULO' ? 'selected' : '' }}>LOCACAO_VEICULO (Locação de Veículos)</option>
                                <option value="AQUISICAO_VEICULO" {{ old('tipo_operacao', $regra->tipo_operacao ?? '') === 'AQUISICAO_VEICULO' ? 'selected' : '' }}>AQUISICAO_VEICULO (Compra de Frota)</option>
                                <option value="MANUTENCAO_VEICULO" {{ old('tipo_operacao', $regra->tipo_operacao ?? '') === 'MANUTENCAO_VEICULO' ? 'selected' : '' }}>MANUTENCAO_VEICULO (Manutenção)</option>
                                <option value="VENDA_VEICULO" {{ old('tipo_operacao', $regra->tipo_operacao ?? '') === 'VENDA_VEICULO' ? 'selected' : '' }}>VENDA_VEICULO (Desmobilização)</option>
                            </select>
                            @error('tipo_operacao')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Alíquota -->
                        <div class="col-md-4 mb-3">
                            <label class="fc-label">Alíquota (%) *</label>
                            <input type="number" name="aliquota" step="0.0001" min="0" max="100" class="form-control @error('aliquota') is-invalid @enderror" value="{{ old('aliquota', $regra->aliquota ?? '') }}" required placeholder="Ex: 0.9000">
                            @error('aliquota')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Data Início -->
                        <div class="col-md-4 mb-3">
                            <label class="fc-label">Início Vigência *</label>
                            <input type="date" name="data_inicio" class="form-control @error('data_inicio') is-invalid @enderror" value="{{ old('data_inicio', $isEdit ? $regra->data_inicio->format('Y-m-d') : '') }}" required>
                            @error('data_inicio')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Data Fim -->
                        <div class="col-md-4 mb-3">
                            <label class="fc-label">Fim Vigência</label>
                            <input type="date" name="data_fim" class="form-control @error('data_fim') is-invalid @enderror" value="{{ old('data_fim', ($isEdit && $regra->data_fim) ? $regra->data_fim->format('Y-m-d') : '') }}">
                            @error('data_fim')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Checkboxes -->
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline mr-4">
                                <input type="checkbox" name="gera_credito" value="1" id="gera_credito" class="form-check-input" {{ old('gera_credito', $regra->gera_credito ?? false) ? 'checked' : '' }}>
                                <label for="gera_credito" class="form-check-label font-weight-bold text-secondary">Gera Crédito Tributário na entrada</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="ativo" value="1" id="ativo" class="form-check-input" {{ old('ativo', $regra->ativo ?? true) ? 'checked' : '' }}>
                                <label for="ativo" class="form-check-label font-weight-bold text-secondary">Regra Ativa</label>
                            </div>
                        </div>
                    </div>

                    <div class="fc-actions mt-4">
                        <button type="submit" class="btn btn-primary fc-btn">
                            <i class="fas fa-save mr-1"></i> Salvar
                        </button>
                        <a href="{{ route('fiscal.regras') }}" class="btn btn-outline-secondary fc-btn">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
