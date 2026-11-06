@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($veiculo) ? 'Editar Veículo' : 'Novo Veículo'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($veiculo) ? route('veiculo.update', $veiculo->id) : route('veiculo.store') }}">
            @csrf
            @if(isset($veiculo))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($veiculo))
                        <x-form-input name="escola_nome" label="Escola" :value="$veiculo->escola->nome ?? ''" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Selecionar'] + ($escolas ?? [])" :selected="old('escola_id')" required />
                    @endif
                </div>
                <div class="col-md-3">
                    <x-form-input name="placa" label="Placa" :value="old('placa', $veiculo->placa ?? '')" required maxlength="12" placeholder="ABC1D23" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $veiculo->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="modelo" label="Modelo" :value="old('modelo', $veiculo->modelo ?? '')" required maxlength="60" placeholder="Ex.: Mercedes Sprinter 2020" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="ano" type="number" label="Ano" :value="old('ano', $veiculo->ano ?? '')" min="1980" max="2100" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="capacidade" type="number" label="Capacidade (lugares)" :value="old('capacidade', $veiculo->capacidade ?? '')" min="1" max="100" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="motorista_nome" label="Nome do motorista" :value="old('motorista_nome', $veiculo->motorista_nome ?? '')" maxlength="100" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="motorista_telefone" label="Telefone do motorista" :value="old('motorista_telefone', $veiculo->motorista_telefone ?? '')" maxlength="20" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('veiculo.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
