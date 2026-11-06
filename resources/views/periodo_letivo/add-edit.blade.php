@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($periodo) ? 'Editar Período Letivo' : 'Novo Período Letivo'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($periodo) ? route('periodo_letivo.update', $periodo->id) : route('periodo_letivo.store') }}">
            @csrf
            @if(isset($periodo))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($periodo))
                        <div class="form-group">
                            <label class="form-label">Ano letivo</label>
                            <input type="text" class="form-control" value="{{ $periodo->anoLetivo->ano ?? '' }} — {{ $periodo->escola->nome ?? '' }}" disabled>
                            <small class="text-muted">O ano letivo não pode ser alterado.</small>
                        </div>
                    @else
                        <x-form-select name="ano_letivo_id" label="Ano letivo" :options="['' => 'Selecionar'] + ($anosLetivos ?? [])" :selected="old('ano_letivo_id')" required />
                        <small class="text-muted">Ex.: 4 bimestres para ensino básico, 2 semestres para faculdade.</small>
                    @endif
                </div>
                <div class="col-md-3">
                    <x-form-input name="ordem" type="number" label="Ordem" :value="old('ordem', $periodo->ordem ?? 1)" required min="1" max="20" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $periodo->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome do período" :value="old('nome', $periodo->nome ?? '')" required maxlength="40" placeholder="Ex.: 1º Bimestre, 1º Semestre" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_inicio" type="date" label="Data de início" :value="old('data_inicio', isset($periodo) ? optional($periodo->data_inicio)->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_fim" type="date" label="Data de fim" :value="old('data_fim', isset($periodo) ? optional($periodo->data_fim)->format('Y-m-d') : '')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('periodo_letivo.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
