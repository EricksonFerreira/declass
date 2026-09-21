@extends('errors.layout')

@section('code', '500')
@section('icon', '🛠️')
@section('title', 'Algo deu errado')
@section('message', 'Tivemos um problema interno ao processar sua solicitação. Nossa equipe já foi avisada — tente novamente em alguns instantes.')

@section('actions')
    <button type="button" onclick="location.reload()" class="error-btn error-btn-outline">Tentar novamente</button>
@endsection
