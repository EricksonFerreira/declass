@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <x-page-header-card :title="$titulo" :subtitle="$subtitulo">
        <x-slot:actions>
            <a href="{{ route('plano.index') }}" class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <a href="{{ route('plano.acessos', $plano) }}" class="btn btn-xs btn-outline-primary">
                <i class="fas fa-user-shield mr-1"></i> Política de Acesso
            </a>
        </x-slot:actions>

        <form action="{{ route('plano.update', $plano) }}" method="POST" id="formPlano">
            @csrf @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @include('plano._form')

            <div class="d-flex justify-content-end mt-2" style="gap:8px;">
                <a href="{{ route('plano.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Salvar Alteracoes
                </button>
            </div>
        </form>
    </x-page-header-card>
@stop
