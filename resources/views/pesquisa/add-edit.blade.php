@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($pesquisa) ? 'Editar Pesquisa' : 'Nova Pesquisa'" />
@stop

@section('content')
@php
    $perguntasAntigas = old('perguntas', isset($pesquisa) ? $pesquisa->perguntas->map(fn ($p) => [
        'pergunta' => $p->pergunta,
        'tipo' => $p->tipo,
        'obrigatoria' => $p->obrigatoria,
    ])->all() : [['pergunta' => '', 'tipo' => 'nota', 'obrigatoria' => 'S']]);
@endphp
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($pesquisa) ? route('pesquisa.update', $pesquisa->id) : route('pesquisa.store') }}" id="form-pesquisa">
            @csrf
            @if(isset($pesquisa))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="titulo" label="Título" :value="old('titulo', $pesquisa->titulo ?? '')" required maxlength="190" placeholder="Ex.: Pesquisa de satisfação — 2º semestre" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="status" label="Status" :options="$statusList ?? []" :selected="old('status', $pesquisa->status ?? 'rascunho')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    @if(isset($pesquisa))
                        <x-form-input name="escola_nome" label="Escola" :value="$pesquisa->escola->nome ?? 'Rede toda'" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Rede toda'] + ($escolas ?? [])" :selected="old('escola_id')" />
                    @endif
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_inicio" type="date" label="Início das respostas" :value="old('data_inicio', isset($pesquisa) ? $pesquisa->data_inicio->format('Y-m-d') : '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_fim" type="date" label="Fim das respostas" :value="old('data_fim', isset($pesquisa) ? $pesquisa->data_fim->format('Y-m-d') : '')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Descrição / instruções</label>
                    <textarea name="descricao" class="form-control" rows="2" maxlength="4000"
                        placeholder="Explique o objetivo da pesquisa para as famílias...">{{ old('descricao', $pesquisa->descricao ?? '') }}</textarea>
                </div>
            </div>

            <hr>
            <h5 class="font-weight-bold">Perguntas</h5>
            <p class="text-muted small mb-2">As perguntas aparecem na ordem listada. Marque "obrigatória" para exigir resposta.</p>

            <div id="perguntas-container">
                @foreach($perguntasAntigas as $i => $p)
                    <div class="row pergunta-item align-items-end mb-2" data-index="{{ $i }}">
                        <div class="col-md-5">
                            <label class="form-label">Pergunta {{ $i + 1 }}</label>
                            <input type="text" name="perguntas[{{ $i }}][pergunta]" class="form-control" maxlength="500"
                                value="{{ $p['pergunta'] ?? '' }}" placeholder="Ex.: Como você avalia o atendimento da secretaria?" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de resposta</label>
                            <select name="perguntas[{{ $i }}][tipo]" class="form-control">
                                @foreach($tiposPergunta ?? [] as $valor => $label)
                                    <option value="{{ $valor }}" {{ ($p['tipo'] ?? 'nota') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Obrigatória</label>
                            <select name="perguntas[{{ $i }}][obrigatoria]" class="form-control">
                                <option value="S" {{ ($p['obrigatoria'] ?? 'S') === 'S' ? 'selected' : '' }}>Sim</option>
                                <option value="N" {{ ($p['obrigatoria'] ?? 'S') === 'N' ? 'selected' : '' }}>Não</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-remover-pergunta" title="Remover pergunta">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" id="btn-add-pergunta" class="btn btn-outline-primary btn-sm mb-3">
                <i class="fa fa-plus"></i> Adicionar pergunta
            </button>

            <div class="mt-3">
                <a href="{{ route('pesquisa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<template id="template-pergunta">
    <div class="row pergunta-item align-items-end mb-2" data-index="__INDEX__">
        <div class="col-md-5">
            <label class="form-label">Pergunta</label>
            <input type="text" name="perguntas[__INDEX__][pergunta]" class="form-control" maxlength="500" placeholder="Digite a pergunta..." required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo de resposta</label>
            <select name="perguntas[__INDEX__][tipo]" class="form-control">
                @foreach($tiposPergunta ?? [] as $valor => $label)
                    <option value="{{ $valor }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Obrigatória</label>
            <select name="perguntas[__INDEX__][obrigatoria]" class="form-control">
                <option value="S">Sim</option>
                <option value="N">Não</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-remover-pergunta" title="Remover pergunta">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
</template>
@stop

@section('js')
<script>
$(document).ready(function () {
    let proximoIndex = {{ count($perguntasAntigas) }};

    $('#btn-add-pergunta').on('click', function () {
        const html = $('#template-pergunta').html().replace(/__INDEX__/g, proximoIndex);
        $('#perguntas-container').append(html);
        proximoIndex++;
    });

    $(document).on('click', '.btn-remover-pergunta', function () {
        $(this).closest('.pergunta-item').remove();
    });
});
</script>
@stop
