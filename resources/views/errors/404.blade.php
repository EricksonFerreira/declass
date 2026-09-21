@extends('errors.layout')

@section('code', '404')
@section('icon', '🧭')
@section('title', 'Página não encontrada')
@section('message', 'O endereço que você tentou acessar não existe ou foi movido. Verifique o link ou volte para a página inicial.')

@section('actions')
    <button type="button" onclick="history.back()" class="error-btn error-btn-outline">Voltar</button>
@endsection
