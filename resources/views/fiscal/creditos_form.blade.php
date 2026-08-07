@extends('adminlte::page')

@section('title', 'Apropriar Crédito Tributário')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Apropriar Crédito Tributário" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    <div class="fc-page">
        <div class="card fc-card fc-form-card">
            <div class="card-header py-3">
                <h5 class="fc-title">
                    <i class="fas fa-piggy-bank"></i>
                    <span>Formulário de Apropriação de Crédito</span>
                </h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('fiscal.creditos.salvar') }}">
                    @csrf

                    <div class="row">
                        <!-- Tributo -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Tributo *</label>
                            <select name="tributo" class="form-control @error('tributo') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                <option value="IBS" {{ old('tributo') === 'IBS' ? 'selected' : '' }}>IBS (Imposto sobre Bens e Serviços)</option>
                                <option value="CBS" {{ old('tributo') === 'CBS' ? 'selected' : '' }}>CBS (Contribuição sobre Bens e Serviços)</option>
                            </select>
                            @error('tributo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Valor Original -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Valor do Crédito (R$) *</label>
                            <input type="number" name="valor_original" step="0.01" min="0.01" class="form-control @error('valor_original') is-invalid @enderror" value="{{ old('valor_original') }}" required placeholder="Ex: 1500.00">
                            @error('valor_original')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Origem -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Origem / Documento de Entrada *</label>
                            <input type="text" name="origem" class="form-control @error('origem') is-invalid @enderror" value="{{ old('origem') }}" required placeholder="Ex: NFe de Compra nº 12345 / Manutenção da Frota">
                            @error('origem')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Data Apropriação -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Data da Apropriação *</label>
                            <input type="datetime-local" name="data_apropriacao" class="form-control @error('data_apropriacao') is-invalid @enderror" value="{{ old('data_apropriacao', date('Y-m-d\TH:i')) }}" required>
                            @error('data_apropriacao')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="fc-actions mt-4">
                        <button type="submit" class="btn btn-primary fc-btn">
                            <i class="fas fa-save mr-1"></i> Confirmar Crédito
                        </button>
                        <a href="{{ route('fiscal.creditos') }}" class="btn btn-outline-secondary fc-btn">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
