@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Planos de Manutenção', 'url' => route('manutencao.planos.index')], ['name' => 'Criar Plano']]" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('manutencao.planos.form')
@stop
