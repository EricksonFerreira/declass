@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($evento) ? 'Editar Evento' : 'Novo Evento'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($evento) ? route('evento.update', $evento->id) : route('evento.store') }}">
            @csrf
            @if(isset($evento))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $evento->titulo ?? '')" required maxlength="190" placeholder="Ex.: Reunião de pais e mestres" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $evento->tipo ?? 'evento')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    @if(isset($evento))
                        <x-form-input name="escola_nome" label="Escola" :value="$evento->escola->nome ?? 'Rede toda'" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Rede toda'] + ($escolas ?? [])" :selected="old('escola_id')" />
                    @endif
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_inicio" type="date" label="Data de início" :value="old('data_inicio', isset($evento) ? $evento->data_inicio->format('Y-m-d') : '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_fim" type="date" label="Data de fim" :value="old('data_fim', isset($evento) && $evento->data_fim ? $evento->data_fim->format('Y-m-d') : '')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="hora_inicio" type="time" label="Hora de início" :value="old('hora_inicio', isset($evento) && $evento->hora_inicio ? \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="hora_fim" type="time" label="Hora de fim" :value="old('hora_fim', isset($evento) && $evento->hora_fim ? \Carbon\Carbon::parse($evento->hora_fim)->format('H:i') : '')" />
                </div>
                <div class="col-md-6">
                    <x-form-input name="local" label="Local" :value="old('local', $evento->local ?? '')" maxlength="190" placeholder="Ex.: Auditório, Sala dos Professores" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="publico" label="Público" :options="$publicos ?? []" :selected="old('publico', $evento->publico ?? 'interno')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="status" label="Status" :options="$statusList ?? []" :selected="old('status', $evento->status ?? 'agendado')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3" maxlength="4000"
                        placeholder="Detalhes do evento, programação, orientações...">{{ old('descricao', $evento->descricao ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('evento.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
