@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@section('title', 'Cadastrar escola ou rede')

@section('auth_header')
    <h4 class="mb-0 font-weight-bold">Cadastre sua escola ou rede</h4>
    <small class="text-muted">Crie o tenant e o usuário administrador em poucos passos.</small>
@stop

@section('auth_body')
<form method="POST" action="{{ route('cadastro.empresa.store') }}">
    @csrf

    <h6 class="font-weight-bold mt-2">Dados da escola / rede</h6>
    <div class="form-group">
        <label for="empresa_nome">Nome <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('empresa_nome') is-invalid @enderror" id="empresa_nome" name="empresa_nome" value="{{ old('empresa_nome') }}" required>
        @error('empresa_nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="empresa_cnpj">CNPJ</label>
        <input type="text" class="form-control @error('empresa_cnpj') is-invalid @enderror" id="empresa_cnpj" name="empresa_cnpj" value="{{ old('empresa_cnpj') }}" placeholder="00.000.000/0000-00">
        @error('empresa_cnpj')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="empresa_email">E-mail institucional</label>
            <input type="email" class="form-control @error('empresa_email') is-invalid @enderror" id="empresa_email" name="empresa_email" value="{{ old('empresa_email') }}">
            @error('empresa_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
        <div class="form-group col-md-6">
            <label for="empresa_telefone">Telefone</label>
            <input type="text" class="form-control @error('empresa_telefone') is-invalid @enderror" id="empresa_telefone" name="empresa_telefone" value="{{ old('empresa_telefone') }}" placeholder="(00) 00000-0000">
            @error('empresa_telefone')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    <hr>
    <h6 class="font-weight-bold">Usuário administrador</h6>
    <div class="form-group">
        <label for="admin_nome">Nome do responsável <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('admin_nome') is-invalid @enderror" id="admin_nome" name="admin_nome" value="{{ old('admin_nome') }}" required>
        @error('admin_nome')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="admin_email">E-mail de acesso <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
        @error('admin_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        <small class="form-text text-muted">Será o login de acesso ao painel.</small>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="{{ route('login') }}" class="btn btn-link text-sm">Já tenho conta</a>
        <button type="submit" class="btn btn-primary">Criar conta</button>
    </div>
</form>
@stop
