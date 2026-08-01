@extends('adminlte::page')
@section('title', 'Configuração de Chamados')
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Configuração de Chamados" />
@stop
@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card"><div class="card-body"><h5>Categorias</h5><ul class="mb-0">@foreach($categorias as $categoria)<li>{{ $categoria->nome }}</li>@endforeach</ul></div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body"><h5>Subcategorias</h5><ul class="mb-0">@foreach($subcategorias as $sub)<li>{{ $sub->nome }} - {{ $sub->categoria?->nome }} ({{ $sub->prazo_horas }}h)</li>@endforeach</ul></div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body"><h5>Status</h5><ul class="mb-0">@foreach($statuses as $status)<li>{{ $status->nome }} - {{ $status->slug }}</li>@endforeach</ul></div></div>
    </div>
</div>
@stop
