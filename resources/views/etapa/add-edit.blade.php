@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($etapa) ? 'Editar Etapa' : 'Nova Etapa'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($etapa) ? route('etapa.update', $etapa->id) : route('etapa.store') }}">
            @csrf
            @if(isset($etapa))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $etapa->nome ?? '')" required maxlength="120" placeholder="Ex.: 1º Ano do Ensino Fundamental" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="segmento" label="Segmento" :options="$segmentos ?? []" :selected="old('segmento', $etapa->segmento ?? 'outro')" required />
                </div>
                <div class="col-md-3">
                    <x-form-input name="ordem" type="number" label="Ordem" :value="old('ordem', $etapa->ordem ?? 0)" min="0" max="999" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" rows="2" class="form-control">{{ old('descricao', $etapa->descricao ?? '') }}</textarea>
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $etapa->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('etapa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
