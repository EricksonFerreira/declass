@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@section('title', 'Conta criada')

@section('auth_header')
    <h4 class="mb-0 font-weight-bold">Conta criada com sucesso!</h4>
@stop

@section('auth_body')
<div class="alert alert-success">
    Sua escola/rede foi cadastrada e o usuário administrador foi criado.
</div>

<div class="card card-outline card-success mb-3">
    <div class="card-body">
        <p class="mb-1"><strong>E-mail de acesso:</strong> {{ $email }}</p>
        <p class="mb-1"><strong>Senha inicial:</strong> <code>{{ $senha }}</code></p>
        <p class="mb-0 text-muted small">No primeiro login você será orientado a definir uma nova senha.</p>
    </div>
</div>

<a href="{{ route('login') }}" class="btn btn-primary btn-block">Ir para o login</a>
@stop
