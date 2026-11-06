@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($turma) ? 'Editar Turma' : 'Nova Turma'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($turma) ? route('turma.update', $turma->id) : route('turma.store') }}">
            @csrf
            @if(isset($turma))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome da turma" :value="old('nome', $turma->nome ?? '')" required maxlength="120" placeholder="Ex.: 1º Ano A" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $turma->escola_id ?? '')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ano_letivo_id" label="Ano letivo" :options="$anosLetivos ?? []" :selected="old('ano_letivo_id', $turma->ano_letivo_id ?? '')" placeholder="Selecionar" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="etapa_id" label="Etapa" :options="$etapas ?? []" :selected="old('etapa_id', $turma->etapa_id ?? '')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="turno" label="Turno" :options="$turnos ?? []" :selected="old('turno', $turma->turno ?? '')" placeholder="Selecionar" />
                </div>
                <div class="col-md-2">
                    <x-form-input name="sala" label="Sala" :value="old('sala', $turma->sala ?? '')" maxlength="40" placeholder="101" />
                </div>
                <div class="col-md-2">
                    <x-form-input name="capacidade_maxima" type="number" label="Capacidade" :value="old('capacidade_maxima', $turma->capacidade_maxima ?? '')" min="1" max="500" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $turma->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('turma.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
