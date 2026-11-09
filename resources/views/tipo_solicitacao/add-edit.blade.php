@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($tipo) ? 'Editar Tipo de Solicitação' : 'Novo Tipo de Solicitação'" />
@stop

@section('content')
@php
    $camposAntigos = old('campos', isset($tipo) ? collect($tipo->campos ?? [])->map(fn ($c) => [
        'rotulo' => $c['rotulo'] ?? '',
        'tipo' => $c['tipo'] ?? 'texto',
        'obrigatoria' => ($c['obrigatoria'] ?? false) ? 'S' : 'N',
        'opcoes' => implode(', ', $c['opcoes'] ?? []),
    ])->all() : []);
@endphp
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($tipo) ? route('tipo_solicitacao.update', $tipo->id) : route('tipo_solicitacao.store') }}">
            @csrf
            @if(isset($tipo))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome do requerimento" :value="old('nome', $tipo->nome ?? '')" required maxlength="120" placeholder="Ex.: Declaração de matrícula, Pedido de transferência" />
                </div>
                <div class="col-md-6">
                    <x-form-select name="escola_id" label="Escola" :options="['' => 'Rede toda'] + ($escolas ?? [])" :selected="old('escola_id', $tipo->escola_id ?? '')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="exige_anexo" label="Anexo obrigatório" :options="['N' => 'Não — anexo opcional', 'S' => 'Sim — a família deve anexar arquivo']" :selected="old('exige_anexo', isset($tipo) ? ($tipo->exige_anexo ? 'S' : 'N') : 'N')" />
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="ativo">
                        Disponível no portal
                        <i class="fa fa-info-circle text-primary" style="cursor:help;"
                           data-toggle="tooltip" data-placement="top"
                           title="Quando ativo, a família/responsável pode criar solicitações deste tipo pelo portal do aluno. Desative para tipos que só a secretaria deve usar internamente."></i>
                    </label>
                    <select name="ativo" id="ativo" class="form-control">
                        <option value="S" {{ old('ativo', isset($tipo) ? ($tipo->ativo ? 'S' : 'N') : 'S') === 'S' ? 'selected' : '' }}>Sim</option>
                        <option value="N" {{ old('ativo', isset($tipo) ? ($tipo->ativo ? 'S' : 'N') : 'S') === 'N' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Instruções para a família <small class="text-muted">(opcional)</small></label>
                    <textarea name="descricao" class="form-control" rows="2" maxlength="2000"
                        placeholder="Ex.: informe a escola de destino e anexe o histórico escolar">{{ old('descricao', $tipo->descricao ?? '') }}</textarea>
                </div>
            </div>

            <hr>
            <h5 class="font-weight-bold">Campos extras</h5>
            <p class="text-muted small mb-2">
                Além da descrição livre, a família preenche estes campos no portal, na ordem listada.
                Para "Lista de opções", separe os valores por vírgula.
            </p>

            <div id="campos-container">
                @foreach($camposAntigos as $i => $campo)
                    <div class="row campo-item align-items-end mb-2" data-index="{{ $i }}">
                        <div class="col-md-4">
                            <label class="form-label">Campo {{ $i + 1 }}</label>
                            <input type="text" name="campos[{{ $i }}][rotulo]" class="form-control" maxlength="120"
                                value="{{ $campo['rotulo'] ?? '' }}" placeholder="Ex.: Escola de destino" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select name="campos[{{ $i }}][tipo]" class="form-control campo-tipo">
                                @foreach($tiposCampo ?? [] as $valor => $label)
                                    <option value="{{ $valor }}" {{ ($campo['tipo'] ?? 'texto') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Obrigatório</label>
                            <select name="campos[{{ $i }}][obrigatoria]" class="form-control">
                                <option value="N" {{ ($campo['obrigatoria'] ?? 'N') === 'N' ? 'selected' : '' }}>Não</option>
                                <option value="S" {{ ($campo['obrigatoria'] ?? 'N') === 'S' ? 'selected' : '' }}>Sim</option>
                            </select>
                        </div>
                        <div class="col-md-2 campo-opcoes" style="{{ ($campo['tipo'] ?? 'texto') === 'selecao' ? '' : 'display:none;' }}">
                            <label class="form-label">Opções</label>
                            <input type="text" name="campos[{{ $i }}][opcoes]" class="form-control" maxlength="500"
                                value="{{ $campo['opcoes'] ?? '' }}" placeholder="Opção 1, Opção 2">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-remover-campo" title="Remover campo">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="btn-add-campo" class="btn btn-outline-primary btn-sm mb-3">
                <i class="fa fa-plus"></i> Adicionar campo
            </button>

            <div class="mt-3">
                <a href="{{ route('tipo_solicitacao.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<template id="template-campo">
    <div class="row campo-item align-items-end mb-2" data-index="__INDEX__">
        <div class="col-md-4">
            <label class="form-label">Campo</label>
            <input type="text" name="campos[__INDEX__][rotulo]" class="form-control" maxlength="120" placeholder="Ex.: Escola de destino" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo</label>
            <select name="campos[__INDEX__][tipo]" class="form-control campo-tipo">
                @foreach($tiposCampo ?? [] as $valor => $label)
                    <option value="{{ $valor }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Obrigatório</label>
            <select name="campos[__INDEX__][obrigatoria]" class="form-control">
                <option value="N">Não</option>
                <option value="S">Sim</option>
            </select>
        </div>
        <div class="col-md-2 campo-opcoes" style="display:none;">
            <label class="form-label">Opções</label>
            <input type="text" name="campos[__INDEX__][opcoes]" class="form-control" maxlength="500" placeholder="Opção 1, Opção 2">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger btn-remover-campo" title="Remover campo">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
</template>
@stop

@section('js')
<script>
$(document).ready(function () {
    let proximoIndex = {{ count($camposAntigos) }};

    $('#btn-add-campo').on('click', function () {
        const html = $('#template-campo').html().replace(/__INDEX__/g, proximoIndex);
        $('#campos-container').append(html);
        proximoIndex++;
    });

    $(document).on('click', '.btn-remover-campo', function () {
        $(this).closest('.campo-item').remove();
    });

    $(document).on('change', '.campo-tipo', function () {
        $(this).closest('.campo-item').find('.campo-opcoes')
            .toggle($(this).val() === 'selecao');
    });
});
</script>
@stop
