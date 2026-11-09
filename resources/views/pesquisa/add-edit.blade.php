@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($pesquisa) ? 'Editar Pesquisa' : 'Nova Pesquisa'" />
@stop

@push('css')
<style>
    .frm-sec { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin: 1.1rem 0 .6rem; }
    .frm-sec:first-child { margin-top: 0; }
    .frm-sec i { margin-right: .3rem; }

    .pq-item { border: 1px solid #e5e9f0; border-radius: 12px; padding: 10px 12px 8px; margin-bottom: 10px; background: #fbfcfe; }
    .pq-head { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .pq-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 8px;
        background: linear-gradient(135deg,#2563eb,#0ea5e9); color: #fff;
        font-size: .72rem; font-weight: 800; flex-shrink: 0;
    }
    .pq-num-lbl { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
    .pq-del { margin-left: auto; }
    .pq-item .form-label { font-size: .72rem; font-weight: 700; color: #64748b; margin-bottom: 3px; }
    .pq-empty { border: 1px dashed #cbd5e1; border-radius: 12px; padding: 1.6rem; text-align: center; color: #94a3b8; font-size: .85rem; }
</style>
@endpush

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

            <div class="frm-sec"><i class="fa fa-poll"></i>Dados da pesquisa</div>
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

            <div class="frm-sec"><i class="fa fa-list-ol"></i>Perguntas</div>
            <p class="text-muted small mb-2">As perguntas aparecem na ordem listada. Marque "obrigatória" para exigir resposta.</p>

            <div id="perguntas-container">
                @foreach($perguntasAntigas as $i => $p)
                    <div class="pq-item" data-index="{{ $i }}">
                        <div class="pq-head">
                            <span class="pq-num">{{ $i + 1 }}</span>
                            <span class="pq-num-lbl">Pergunta</span>
                            <button type="button" class="btn btn-xs btn-outline-danger pq-del btn-remover-pergunta" title="Remover pergunta">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Texto da pergunta</label>
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
                            <div class="col-md-3">
                                <label class="form-label">Obrigatória</label>
                                <select name="perguntas[{{ $i }}][obrigatoria]" class="form-control">
                                    <option value="S" {{ ($p['obrigatoria'] ?? 'S') === 'S' ? 'selected' : '' }}>Sim</option>
                                    <option value="N" {{ ($p['obrigatoria'] ?? 'S') === 'N' ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
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
    <div class="pq-item" data-index="__INDEX__">
        <div class="pq-head">
            <span class="pq-num">__NUM__</span>
            <span class="pq-num-lbl">Pergunta</span>
            <button type="button" class="btn btn-xs btn-outline-danger pq-del btn-remover-pergunta" title="Remover pergunta">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Texto da pergunta</label>
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
            <div class="col-md-3">
                <label class="form-label">Obrigatória</label>
                <select name="perguntas[__INDEX__][obrigatoria]" class="form-control">
                    <option value="S">Sim</option>
                    <option value="N">Não</option>
                </select>
            </div>
        </div>
    </div>
</template>
@stop

@section('js')
<script>
$(document).ready(function () {
    let proximoIndex = {{ count($perguntasAntigas) }};

    function reordenar() {
        $('#perguntas-container .pq-item').each(function (i) {
            $(this).find('.pq-num').text(i + 1);
        });
    }

    $('#btn-add-pergunta').on('click', function () {
        const html = $('#template-pergunta').html()
            .replace(/__INDEX__/g, proximoIndex)
            .replace(/__NUM__/g, proximoIndex + 1);
        $('#perguntas-container').append(html);
        proximoIndex++;
        reordenar();
    });

    $(document).on('click', '.btn-remover-pergunta', function () {
        $(this).closest('.pq-item').remove();
        reordenar();
    });
});
</script>
@stop
