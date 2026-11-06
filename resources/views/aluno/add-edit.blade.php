@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($aluno) ? 'Editar Aluno' : 'Novo Aluno'" />
@stop

@section('content')
@php($p = $aluno->pessoa ?? null)
<div class="card">
    <div class="card-header font-weight-bold">Dados Pessoais</div>
    <div class="card-body">
        <form method="POST" action="{{ isset($aluno) ? route('aluno.update', $aluno->id) : route('aluno.store') }}">
            @csrf
            @if(isset($aluno))
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

    <div class="card-header font-weight-bold">Dados Escolares</div>
    <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="registro_academico" label="RA (Registro Acadêmico)" :value="old('registro_academico', $aluno->registro_academico ?? '')" maxlength="30" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $aluno->escola_id ?? '')" placeholder="Selecionar" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_ingresso" type="date" label="Data de ingresso" :value="old('data_ingresso', optional($aluno->data_ingresso ?? null)->format('Y-m-d'))" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="turno_preferencial" label="Turno preferencial" :options="$turnos ?? []" :selected="old('turno_preferencial', $aluno->turno_preferencial ?? '')" placeholder="Selecionar" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $aluno->ativo ?? 'S')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="user_id" label="Conta de acesso (portal)" :options="$usuariosPortal ?? []" :selected="old('user_id', $aluno->user_id ?? '')" placeholder="Sem acesso ao portal" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Necessidades especiais / acessibilidade</label>
                    <textarea name="necessidades_especiais" rows="3" class="form-control">{{ old('necessidades_especiais', $aluno->necessidades_especiais ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Informações de saúde</label>
                    <textarea name="informacoes_saude" rows="3" class="form-control">{{ old('informacoes_saude', $aluno->informacoes_saude ?? '') }}</textarea>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <label class="form-label">Contatos de emergência</label>
                    <textarea name="contatos_emergencia" rows="3" class="form-control" placeholder="Nome, parentesco e telefone">{{ old('contatos_emergencia', $aluno->contatos_emergencia ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Restrições de acesso</label>
                    <textarea name="restricoes_acesso" rows="3" class="form-control">{{ old('restricoes_acesso', $aluno->restricoes_acesso ?? '') }}</textarea>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="2" class="form-control">{{ old('observacoes', $aluno->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('aluno.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
