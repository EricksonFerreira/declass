@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Pedido à secretaria: declarações, atendimento, financeiro" />
@stop

@section('content')
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header font-weight-bold">Nova solicitação</div>
            <div class="card-body">
                @include('portal.partials.solicitacao-form')
                <a href="{{ route('portal.solicitacoes') }}" class="btn btn-secondary btn-block mt-2">Voltar</a>
            </div>
        </div>
    </div>
</div>
@stop
