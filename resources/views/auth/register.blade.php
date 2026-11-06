@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@section('title', 'Criar conta')

@section('auth_header')
    <h4 class="mb-0 font-weight-bold">Criar conta</h4>
    <small class="text-muted">{{ $empresa->nome ?? '' }}</small>
@stop

@section('auth_body')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label for="name">Nome <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="email">E-mail <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="celular">Celular</label>
        <input type="text" class="form-control @error('celular') is-invalid @enderror" id="celular" name="celular" value="{{ old('celular') }}" placeholder="(00) 00000-0000">
        @error('celular')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="password">Senha <span class="text-danger">*</span></label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirmar senha <span class="text-danger">*</span></label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>

    <p class="mt-3 mb-0 text-center">
        <a href="{{ route('login') }}">Já tenho conta</a>
    </p>
</form>
@stop
