@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($rota) ? 'Editar Rota de Transporte' : 'Nova Rota de Transporte'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($rota) ? route('rota_transporte.update', $rota->id) : route('rota_transporte.store') }}">
            @csrf
            @if(isset($rota))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    @if(isset($rota))
                        <x-form-input name="escola_nome" label="Escola" :value="$rota->escola->nome ?? ''" disabled />
                    @else
                        <x-form-select name="escola_id" label="Escola" :options="['' => 'Selecionar'] + ($escolas ?? [])" :selected="old('escola_id')" required />
                    @endif
                </div>
                <div class="col-md-3">
                    <x-form-select name="veiculo_id" label="Veículo" :options="['' => 'Sem veículo definido'] + ($veiculos ?? [])" :selected="old('veiculo_id', $rota->veiculo_id ?? '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $rota->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome da rota" :value="old('nome', $rota->nome ?? '')" required maxlength="80" placeholder="Ex.: Rota Centro — Manhã" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="horario_ida" type="time" label="Horário de ida" :value="old('horario_ida', isset($rota) && $rota->horario_ida ? $rota->horario_ida->format('H:i') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="horario_volta" type="time" label="Horário de volta" :value="old('horario_volta', isset($rota) && $rota->horario_volta ? $rota->horario_volta->format('H:i') : '')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $rota->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <h5>Paradas <small class="text-muted">(pontos de embarque, em ordem)</small></h5>
                <div id="paradas-container">
                    @php
                        $paradasOld = old('paradas', isset($rota) ? $rota->paradas->map(fn($p) => [
                            'id' => $p->id, 'nome' => $p->nome, 'ordem' => $p->ordem,
                            'horario_previsto' => $p->horario_previsto?->format('H:i'),
                        ])->all() : []);
                    @endphp
                    @foreach($paradasOld as $i => $p)
                        <div class="row parada-row mb-2" data-index="{{ $i }}">
                            <input type="hidden" name="paradas[{{ $i }}][id]" value="{{ $p['id'] ?? '' }}">
                            <div class="col-md-5">
                                <input type="text" name="paradas[{{ $i }}][nome]" class="form-control" placeholder="Nome da parada" value="{{ $p['nome'] ?? '' }}" maxlength="80">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="paradas[{{ $i }}][ordem]" class="form-control" placeholder="Ordem" value="{{ $p['ordem'] ?? $i + 1 }}" min="1" max="50">
                            </div>
                            <div class="col-md-3">
                                <input type="time" name="paradas[{{ $i }}][horario_previsto]" class="form-control" value="{{ $p['horario_previsto'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-sm btn-remover-parada"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-parada">
                    <i class="fa fa-plus"></i> Adicionar parada
                </button>
            </div>

            <div class="mt-3">
                <a href="{{ route('rota_transporte.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<template id="tpl-parada">
    <div class="row parada-row mb-2">
        <input type="hidden" name="paradas[__INDEX__][id]" value="">
        <div class="col-md-5">
            <input type="text" name="paradas[__INDEX__][nome]" class="form-control" placeholder="Nome da parada" maxlength="80">
        </div>
        <div class="col-md-2">
            <input type="number" name="paradas[__INDEX__][ordem]" class="form-control" placeholder="Ordem" min="1" max="50">
        </div>
        <div class="col-md-3">
            <input type="time" name="paradas[__INDEX__][horario_previsto]" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm btn-remover-parada"><i class="fa fa-trash"></i></button>
        </div>
    </div>
</template>

<script>
(function () {
    const container = document.getElementById('paradas-container');
    const tpl = document.getElementById('tpl-parada');
    let idx = {{ count($paradasOld) }};

    function renomearIndices() {
        container.querySelectorAll('.parada-row').forEach((row, i) => {
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/paradas\[\d+\]/, 'paradas[' + i + ']');
            });
            const ordem = row.querySelector('[name$="[ordem]"]');
            if (ordem && !ordem.value) ordem.value = i + 1;
        });
    }

    document.getElementById('btn-add-parada').addEventListener('click', () => {
        const html = tpl.innerHTML.replaceAll('__INDEX__', idx++);
        container.insertAdjacentHTML('beforeend', html);
        renomearIndices();
    });

    container.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remover-parada')) {
            e.target.closest('.parada-row').remove();
            renomearIndices();
        }
    });
})();
</script>
@stop
