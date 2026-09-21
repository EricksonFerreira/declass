@extends('errors.layout')

@section('code', '401')
@section('icon', '🔒')
@section('title', 'Login necessário')
@section('message', 'Você precisa estar autenticado para acessar esta página. Entre com sua conta para continuar.')

@section('actions')
    <a href="{{ url('/login') }}" class="error-btn error-btn-outline">Fazer login</a>
@endsection
