@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <x-page-title :title="'Dashboard'" subtitle="Visão geral da sua escola" />
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\Escola::count() }}</h3>
                <p>Escolas / unidades</p>
            </div>
            <div class="icon"><i class="fas fa-school"></i></div>
            <a href="{{ route('escola.index') }}" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Usuários</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('usuario.index') }}" class="small-box-footer">Gerenciar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header font-weight-bold">Acesso rápido</div>
    <div class="card-body">
        <a href="{{ route('home') }}" class="btn btn-primary btn-sm">Ir para o início</a>
    </div>
</div>
@stop
