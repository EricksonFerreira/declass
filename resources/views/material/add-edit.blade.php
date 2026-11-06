@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($material) ? 'Editar Material' : 'Novo Material'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($material) ? route('material.update', $material->id) : route('material.store') }}">
            @csrf
            @if(isset($material))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome do material" :value="old('nome', $material->nome ?? '')" required maxlength="190" placeholder="Ex.: Papel sulfite A4, Detergente neutro" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="categoria" label="Categoria" :options="$categorias ?? []" :selected="old('categoria', $material->categoria ?? 'outro')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="unidade_medida" label="Unidade" :options="$unidades ?? []" :selected="old('unidade_medida', $material->unidade_medida ?? 'un')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    @if(isset($material))
                        <x-form-input name="escola_nome" label="Escola" :value="$material->escola->nome ?? 'Rede toda'" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="$escolas ?? []" :selected="old('escola_id')" />
                    @endif
                </div>
                <div class="col-md-4">
                    <x-form-input name="estoque_atual" type="number" step="0.01" min="0" label="Estoque inicial" :value="old('estoque_atual', $material->estoque_atual ?? 0)" />
                    <small class="text-muted">Use movimentações para alterar depois.</small>
                </div>
                <div class="col-md-4">
                    <x-form-input name="estoque_minimo" type="number" step="0.01" min="0" label="Estoque mínimo" :value="old('estoque_minimo', $material->estoque_minimo ?? 0)" />
                    <small class="text-muted">Abaixo disso gera alerta de reposição.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="localizacao" label="Localização" :value="old('localizacao', $material->localizacao ?? '')" maxlength="120" placeholder="Ex.: Almoxarifado da cantina, prateleira B2" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $material->ativo ?? 'S')" required />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('material.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
