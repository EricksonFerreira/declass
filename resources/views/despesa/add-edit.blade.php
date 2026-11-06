@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($despesa) ? 'Editar Despesa' : 'Nova Despesa'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($despesa) ? route('despesa.update', $despesa->id) : route('despesa.store') }}">
            @csrf
            @if(isset($despesa))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="descricao" label="Descrição" :value="old('descricao', $despesa->descricao ?? '')" required maxlength="255" placeholder="Ex.: Conta de luz — unidade central" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="fornecedor" label="Fornecedor" :value="old('fornecedor', $despesa->fornecedor ?? '')" maxlength="190" placeholder="Opcional" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="categoria" label="Categoria" :options="$categorias" :selected="old('categoria', $despesa->categoria ?? 'outros')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas" :selected="old('escola_id', $despesa->escola_id ?? '')" placeholder="Rede toda" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="valor" type="number" label="Valor (R$)" :value="old('valor', $despesa->valor ?? '')" required min="0.01" step="0.01" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="data_vencimento" type="date" label="Vencimento" :value="old('data_vencimento', isset($despesa) ? $despesa->data_vencimento?->toDateString() : '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="forma_pagamento" label="Forma de pagamento (prevista)" :options="$formasPagamento" :selected="old('forma_pagamento', $despesa->forma_pagamento ?? '')" placeholder="—" />
                </div>
                <div class="col-md-4"></div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="2" class="form-control">{{ old('observacoes', $despesa->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('despesa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
