@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($anoLetivo) ? 'Editar Ano Letivo' : 'Novo Ano Letivo'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($anoLetivo) ? route('ano_letivo.update', $anoLetivo->id) : route('ano_letivo.store') }}">
            @csrf
            @if(isset($anoLetivo))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="ano" type="number" label="Ano" :value="old('ano', $anoLetivo->ano ?? date('Y'))" required min="2000" max="2100" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $anoLetivo->escola_id ?? '')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="status" label="Status" :options="$status ?? []" :selected="old('status', $anoLetivo->status ?? 'letivo')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="data_inicio" type="date" label="Início" :value="old('data_inicio', optional($anoLetivo->data_inicio ?? null)->format('Y-m-d'))" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_fim" type="date" label="Fim" :value="old('data_fim', optional($anoLetivo->data_fim ?? null)->format('Y-m-d'))" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Situação" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $anoLetivo->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('ano_letivo.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
