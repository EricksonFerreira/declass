@extends('errors.layout')

@section('code', '403')
@section('icon', '🚫')
@section('title', 'Acesso não permitido')
@section('message', 'Você não tem permissão para acessar este recurso. Se acredita que isso é um erro, fale com o administrador da sua empresa.')

@section('actions')
    <button type="button" onclick="history.back()" class="error-btn error-btn-outline">Voltar</button>
@endsection
