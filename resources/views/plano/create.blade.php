@extends('adminlte::page')

@section('title', 'Novo Plano')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">Novo Plano</h1>
        </div>
        <a href="{{ route('plano.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    <form action="{{ route('plano.store') }}" method="POST" id="formPlano">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @include('plano._form')

        <div class="d-flex justify-content-end mt-2" style="gap:8px;">
            <a href="{{ route('plano.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Salvar Plano
            </button>
        </div>
    </form>
@stop
