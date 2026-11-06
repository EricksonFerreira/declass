@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($responsavel) ? 'Editar Responsável' : 'Novo Responsável'" />
@stop

@section('content')
@php($p = $responsavel->pessoa ?? null)
<div class="card">
    <div class="card-header font-weight-bold">Dados Pessoais</div>
    <div class="card-body">
        <form method="POST" action="{{ isset($responsavel) ? route('responsavel.update', $responsavel->id) : route('responsavel.store') }}">
            @csrf
            @if(isset($responsavel))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome completo" :value="old('nome', $p->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-6">
                    <x-form-input name="nome_social" label="Nome social" :value="old('nome_social', $p->nome_social ?? '')" maxlength="255" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="cpf" label="CPF" :value="old('cpf', $p->cpf ?? '')" maxlength="20" placeholder="000.000.000-00" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="rg" label="RG" :value="old('rg', $p->rg ?? '')" maxlength="20" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_nascimento" type="date" label="Data de nascimento" :value="old('data_nascimento', optional($p->data_nascimento ?? null)->format('Y-m-d'))" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="sexo" label="Sexo" :options="$sexos ?? []" :selected="old('sexo', $p->sexo ?? '')" placeholder="Selecionar" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $p->email ?? '')" maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $p->telefone ?? '')" maxlength="30" placeholder="(00) 0000-0000" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="celular" label="Celular" :value="old('celular', $p->celular ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="cep" label="CEP" :value="old('cep', $p->cep ?? '')" maxlength="10" placeholder="00000-000" />
                </div>
                <div class="col-md-5">
                    <x-form-input name="endereco" label="Endereço" :value="old('endereco', $p->endereco ?? '')" maxlength="500" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="cidade" label="Cidade" :value="old('cidade', $p->cidade ?? '')" maxlength="120" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <x-form-input name="estado" label="UF" :value="old('estado', $p->estado ?? '')" maxlength="2" placeholder="SP" />
                </div>
            </div>
        </div>
    </div>

    <div class="card-header font-weight-bold">Dados do Responsável</div>
    <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="profissao" label="Profissão" :value="old('profissao', $responsavel->profissao ?? '')" maxlength="120" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="local_trabalho" label="Local de trabalho" :value="old('local_trabalho', $responsavel->local_trabalho ?? '')" maxlength="160" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $responsavel->ativo ?? 'S')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="user_id" label="Conta de acesso (portal)" :options="$usuariosPortal ?? []" :selected="old('user_id', $responsavel->user_id ?? '')" placeholder="Sem acesso ao portal" />
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="3" class="form-control">{{ old('observacoes', $responsavel->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('responsavel.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
