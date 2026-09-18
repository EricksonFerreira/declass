@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Escolha uma área para acessar" />
@stop

@section('css')
@include('partials.home-cards-styles')
@stop

@section('content')
@if (empty($secoes))
    <div class="text-center text-muted py-5">
        <i class="fas fa-lock fa-3x mb-3 d-block"></i>
        <p class="mb-0">Nenhuma área disponível para o seu perfil.</p>
    </div>
@else
    @include('partials.menu-cards')
@endif
@stop
