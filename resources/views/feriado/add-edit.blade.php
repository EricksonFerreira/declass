@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($feriado) ? 'Editar Feriado' : 'Novo Feriado'" subtitle="Dias sem aula aparecem no calendário letivo das turmas" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info small">
            <i class="fas fa-info-circle"></i>
            <strong>Suspende aulas</strong>: o dia aparece como "sem aula" no calendário (feriado nacional, recesso).
            <strong>Aula normal</strong>: o dia continua letivo e o feriado aparece apenas como registro (ponto facultativo parcial, data comemorativa com aula).
        </div>

        <form method="POST" action="{{ isset($feriado) ? route('feriado.update', $feriado->id) : route('feriado.store') }}">
            @csrf
            @if(isset($feriado)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $feriado->nome ?? '')" required maxlength="150" placeholder="Ex.: Carnaval, Tiradentes, Recesso de julho" />
                </div>
                <div class="col-md-6">
                    <x-form-select name="escola_id" label="Escola" :options="$escolas" :selected="old('escola_id', $feriado->escola_id ?? '')" placeholder="Toda a rede" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="data_inicio" type="date" label="Início" :value="old('data_inicio', isset($feriado) ? $feriado->data_inicio->format('Y-m-d') : '')" required />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_fim" type="date" label="Fim (opcional)" :value="old('data_fim', isset($feriado) && $feriado->data_fim ? $feriado->data_fim->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="suspende_aulas" label="Aulas neste período" :options="['S' => 'Suspensas (sem aula)', 'N' => 'Aula normal (só registro)']" :selected="old('suspende_aulas', $feriado->suspende_aulas ?? 'S')" required />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('feriado.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
