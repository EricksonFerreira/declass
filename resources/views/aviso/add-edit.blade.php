@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($aviso) ? 'Editar Aviso' : 'Novo Aviso'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($aviso) ? route('aviso.update', $aviso->id) : route('aviso.store') }}">
            @csrf
            @if(isset($aviso))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id', $aviso->escola_id ?? '')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="turma_id" label="Turma (opcional)" :options="['' => 'Escola toda'] + ($turmas ?? [])" :selected="old('turma_id', $aviso->turma_id ?? '')" />
                </div>
                <div class="col-md-2">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $aviso->tipo ?? 'info')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="destino" label="Público" :options="$destinos ?? []" :selected="old('destino', $aviso->destino ?? 'familias')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $aviso->titulo ?? '')" required maxlength="160" placeholder="Ex.: Reunião de pais e mestres" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_publicacao" type="date" label="Data de publicação" :value="old('data_publicacao', isset($aviso) ? optional($aviso->data_publicacao)->format('Y-m-d') : now()->format('Y-m-d'))" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $aviso->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <label class="form-label">Mensagem</label>
                    <textarea name="mensagem" rows="6" class="form-control" required maxlength="5000">{{ old('mensagem', $aviso->mensagem ?? '') }}</textarea>
                    <small class="text-muted">Deixe a turma em branco para enviar o aviso para toda a escola.</small>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('aviso.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
