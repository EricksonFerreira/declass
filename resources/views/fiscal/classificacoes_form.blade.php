@extends('adminlte::page')

@section('title', 'Nova Classificação Fiscal (NCM)')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Nova Classificação Fiscal (NCM)" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    <div class="fc-page">
        <div class="card fc-card fc-form-card">
            <div class="card-header py-3">
                <h5 class="fc-title">
                    <i class="fas fa-tags"></i>
                    <span>Formulário de Cadastro de NCM</span>
                </h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('fiscal.classificacoes.salvar') }}">
                    @csrf

                    <div class="row">
                        <!-- Descrição -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Descrição *</label>
                            <input type="text" name="descricao" class="form-control @error('descricao') is-invalid @enderror" value="{{ old('descricao') }}" required placeholder="Ex: Automóvel de Passageiros - Flex">
                            @error('descricao')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- NCM -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Código NCM (8 dígitos) *</label>
                            <input type="text" name="ncm" maxlength="8" class="form-control @error('ncm') is-invalid @enderror" value="{{ old('ncm') }}" required placeholder="Ex: 87032310">
                            @error('ncm')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Tipo de Bem -->
                        <div class="col-md-12 mb-3">
                            <label class="fc-label">Tipo do Bem *</label>
                            <input type="text" name="tipo_bem" class="form-control @error('tipo_bem') is-invalid @enderror" value="{{ old('tipo_bem') }}" required placeholder="Ex: Veículo Terrestre / Utilitário">
                            @error('tipo_bem')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="fc-actions mt-4">
                        <button type="submit" class="btn btn-primary fc-btn">
                            <i class="fas fa-save mr-1"></i> Salvar Classificação
                        </button>
                        <a href="{{ route('fiscal.classificacoes') }}" class="btn btn-outline-secondary fc-btn">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
