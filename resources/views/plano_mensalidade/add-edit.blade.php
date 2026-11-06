@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($plano) ? 'Editar Plano de Mensalidade' : 'Novo Plano de Mensalidade'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($plano) ? route('plano_mensalidade.update', $plano->id) : route('plano_mensalidade.store') }}">
            @csrf
            @if(isset($plano))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $plano->nome ?? '')" required maxlength="120" placeholder="Ex.: Mensalidade Integral" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="valor" type="number" label="Valor mensal (R$)" :value="old('valor', $plano->valor ?? '')" required min="0" step="0.01" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $plano->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" rows="2" class="form-control">{{ old('descricao', $plano->descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('plano_mensalidade.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
