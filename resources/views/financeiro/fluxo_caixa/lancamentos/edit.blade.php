@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa.lancamentos._form', [
        'action' => route('financeiro.fluxo-caixa.update', $lancamento->id),
    ])
@stop
