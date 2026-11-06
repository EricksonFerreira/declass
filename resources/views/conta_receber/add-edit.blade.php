@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($conta) ? 'Editar Conta a Receber' : 'Nova Conta a Receber'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($conta) ? route('conta_receber.update', $conta->id) : route('conta_receber.store') }}">
            @csrf
            @if(isset($conta))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="descricao" label="Descrição" :value="old('descricao', $conta->descricao ?? '')" required maxlength="255" placeholder="Ex.: Uniforme completo — 1º ano" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="categoria" label="Categoria" :options="$categorias" :selected="old('categoria', $conta->categoria ?? 'outros')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="aluno_id" label="Aluno (opcional)" :options="$alunos" :selected="old('aluno_id', $conta->aluno_id ?? '')" placeholder="Cobrança geral" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas" :selected="old('escola_id', $conta->escola_id ?? '')" placeholder="Rede toda" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="valor" type="number" label="Valor (R$)" :value="old('valor', $conta->valor ?? '')" required min="0.01" step="0.01" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="data_vencimento" type="date" label="Vencimento" :value="old('data_vencimento', isset($conta) ? $conta->data_vencimento?->toDateString() : '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="forma_recebimento" label="Forma de recebimento (prevista)" :options="$formasRecebimento" :selected="old('forma_recebimento', $conta->forma_recebimento ?? '')" placeholder="—" />
                </div>
                <div class="col-md-4"></div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="2" class="form-control">{{ old('observacoes', $conta->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('conta_receber.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
