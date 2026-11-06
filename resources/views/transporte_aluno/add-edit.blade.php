@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($passageiro) ? 'Editar Passageiro' : 'Novo Passageiro'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($passageiro) ? route('transporte_aluno.update', $passageiro->id) : route('transporte_aluno.store') }}">
            @csrf
            @if(isset($passageiro))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="aluno_id" label="Aluno" :options="['' => 'Selecionar'] + ($alunos ?? [])" :selected="old('aluno_id', $passageiro->aluno_id ?? '')" required />
                    <small class="text-muted">Apenas alunos sem transporte ativo aparecem na lista.</small>
                </div>
                <div class="col-md-4">
                    <x-form-select name="rota_id" label="Rota" :options="['' => 'Selecionar'] + ($rotas ?? [])" :selected="old('rota_id', $passageiro->rota_id ?? '')" required id="rota_id" />
                </div>
                <div class="col-md-2">
                    <x-form-select name="ativo" label="Situação" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $passageiro->ativo ?? 'S')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="parada_id" label="Parada de embarque" :options="['' => 'Ponto mais próximo / sem parada fixa']" id="parada_id" :selected="old('parada_id', $passageiro->parada_id ?? '')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" maxlength="2000">{{ old('observacoes', $passageiro->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('transporte_aluno.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const paradasPorRota = {!! $paradasPorRota ?? '{}' !!};
    const selRota = document.getElementById('rota_id');
    const selParada = document.getElementById('parada_id');
    const oldParada = "{{ old('parada_id', $passageiro->parada_id ?? '') }}";

    function preencherParadas() {
        const lista = paradasPorRota[selRota.value] || {};
        selParada.innerHTML = '<option value="">Ponto mais próximo / sem parada fixa</option>';
        Object.entries(lista).forEach(([id, nome]) => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = nome;
            if (String(id) === oldParada) opt.selected = true;
            selParada.appendChild(opt);
        });
        if (oldParada && !selParada.querySelector('option[value="' + oldParada + '"]:checked')) {
            selParada.value = '';
        }
    }

    selRota.addEventListener('change', preencherParadas);
    preencherParadas();
})();
</script>
@stop
