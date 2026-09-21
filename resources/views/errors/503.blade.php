@extends('errors.layout')

@section('code', '503')
@section('icon', '🔧')
@section('title', 'Manutenção em andamento')
@section('message', 'Estamos fazendo uma manutenção rápida para melhorar o sistema. Voltamos em instantes — obrigado pela paciência.')

@section('actions')
    <button type="button" onclick="location.reload()" class="error-btn error-btn-outline">Recarregar</button>
@endsection
