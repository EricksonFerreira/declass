@extends('errors.layout')

@section('code', '419')
@section('icon', '⏱️')
@section('title', 'Sessão expirada')
@section('message', 'Sua sessão expirou por inatividade ou o formulário ficou aberto por muito tempo. Recarregue a página e tente novamente.')

@section('actions')
    <button type="button" onclick="location.reload()" class="error-btn error-btn-outline">Recarregar</button>
@endsection
