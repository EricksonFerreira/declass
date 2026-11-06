@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($escola) ? 'Editar Escola' : 'Nova Escola'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($escola) ? route('escola.update', $escola->id) : route('escola.store') }}">
            @csrf
            @if(isset($escola))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $escola->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="organizacao_id" label="Organização" :options="$organizacoes ?? []" :selected="old('organizacao_id', $escola->organizacao_id ?? '')" placeholder="Sem organização" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="cnpj" label="CNPJ" :value="old('cnpj', $escola->cnpj ?? '')" maxlength="18" placeholder="00.000.000/0000-00" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $escola->email ?? '')" maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $escola->telefone ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="endereco" label="Endereço" :value="old('endereco', $escola->endereco ?? '')" maxlength="500" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="capacidade_alunos" type="number" label="Capacidade de alunos" :value="old('capacidade_alunos', $escola->capacidade_alunos ?? '')" min="1" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="cidade" label="Cidade" :value="old('cidade', $escola->cidade ?? '')" maxlength="120" />
                </div>
                <div class="col-md-2">
                    <x-form-input name="estado" label="UF" :value="old('estado', $escola->estado ?? '')" maxlength="2" placeholder="SP" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="turno_padrao" label="Turno padrão" :options="$turnos ?? []" :selected="old('turno_padrao', $escola->turno_padrao ?? '')" placeholder="Selecionar" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativa" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativa', $escola->ativa ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="cor_primaria" type="color" label="Cor primária" :value="old('cor_primaria', $escola->cor_primaria ?? '#1e3a8a')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="cor_secundaria" type="color" label="Cor secundária" :value="old('cor_secundaria', $escola->cor_secundaria ?? '#f59e0b')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('escola.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
