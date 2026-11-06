@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($cardapio) ? 'Editar Cardápio' : 'Novo Cardápio'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($cardapio) ? route('cardapio.update', $cardapio->id) : route('cardapio.store') }}">
            @csrf
            @if(isset($cardapio))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($cardapio))
                        <x-form-input name="escola_nome" label="Escola" :value="$cardapio->escola->nome ?? ''" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Selecionar'] + ($escolas ?? [])" :selected="old('escola_id')" required />
                    @endif
                </div>
                <div class="col-md-3">
                    <x-form-input name="data" type="date" label="Data" :value="old('data', isset($cardapio) ? $cardapio->data->format('Y-m-d') : '')" required />
                </div>
                <div class="col-md-3">
                    <x-form-select name="tipo" label="Refeição" :options="$tipos ?? []" :selected="old('tipo', $cardapio->tipo ?? '')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição do cardápio <span class="text-danger">*</span></label>
                    <textarea name="descricao" class="form-control" rows="4" required maxlength="4000"
                        placeholder="Ex.: Arroz, feijão, frango grelhado, salada de alface e tomate, suco de laranja e fruta da estação.">{{ old('descricao', $cardapio->descricao ?? '') }}</textarea>
                    <small class="text-muted">Liste os itens servidos, separados por vírgula.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="restricoes_alimentares" label="Restrições alimentares" :value="old('restricoes_alimentares', $cardapio->restricoes_alimentares ?? '')" maxlength="500" placeholder="Ex.: Sem glúten, opção vegetariana disponível" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $cardapio->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $cardapio->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('cardapio.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
