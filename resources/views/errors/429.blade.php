@extends('errors.layout')

@section('code', '429')
@section('icon', '🐢')
@section('title', 'Muitas tentativas')
@section('message', 'Você fez muitas requisições em pouco tempo. Aguarde alguns instantes e tente novamente.')

@section('actions')
    <button type="button" onclick="location.reload()" class="error-btn error-btn-outline">Tentar novamente</button>
@endsection
