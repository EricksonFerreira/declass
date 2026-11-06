@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Matriz — '.$turnoLabel" :subtitle="$escola->nome" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-info small mb-3">
            <i class="fas fa-info-circle"></i>
            Defina os <strong>dias letivos</strong> e as <strong>aulas do dia</strong> (horário de início e fim de cada uma).
            A mesma grade vale para todos os dias marcados — é ela que aparece na grade horária das turmas deste turno.
        </div>

        <form method="POST" action="{{ route('horario_matriz.update', [$escola->id, $turno]) }}" id="form-matriz">
            @csrf @method('PUT')

            <h6 class="font-weight-bold">Dias letivos</h6>
            <div class="form-check form-check-inline mb-3">
                @foreach($dias as $valor => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="dias[]" value="{{ $valor }}" id="dia_{{ $valor }}"
                            {{ in_array($valor, old('dias', $diasAtuais)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="dia_{{ $valor }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            @error('dias')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

            <h6 class="font-weight-bold">Aulas do dia <small class="text-muted">(em ordem)</small></h6>
            <div id="lista-aulas">
                @php $aulasOld = old('aulas'); @endphp
                @forelse(($aulasOld ?: $slotsAtuais->map(fn ($s) => ['inicio' => substr((string) $s->hora_inicio, 0, 5), 'fim' => substr((string) $s->hora_fim, 0, 5)])) as $i => $aula)
                    <div class="row align-items-end mb-2 aula-row">
                        <div class="col-md-1">
                            <label class="form-label mb-0">#</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $i + 1 }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-0">Início</label>
                            <input type="time" name="aulas[{{ $i }}][inicio]" class="form-control form-control-sm" value="{{ $aula['inicio'] ?? '' }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-0">Fim</label>
                            <input type="time" name="aulas[{{ $i }}][fim]" class="form-control form-control-sm" value="{{ $aula['fim'] ?? '' }}" required>
                        </div>
                        <div class="col-md-2">
                            @if($i > 0)
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remover"><i class="fa fa-trash"></i></button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="row align-items-end mb-2 aula-row">
                        <div class="col-md-1"><label class="form-label mb-0">#</label><input type="text" class="form-control form-control-sm" value="1" readonly></div>
                        <div class="col-md-3"><label class="form-label mb-0">Início</label><input type="time" name="aulas[0][inicio]" class="form-control form-control-sm" required></div>
                        <div class="col-md-3"><label class="form-label mb-0">Fim</label><input type="time" name="aulas[0][fim]" class="form-control form-control-sm" required></div>
                        <div class="col-md-2"></div>
                    </div>
                @endforelse
            </div>
            @error('aulas')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
            @error('aulas.*.inicio')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
            @error('aulas.*.fim')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

            <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="btn-add-aula">
                <i class="fa fa-plus"></i> Adicionar aula
            </button>

            <div class="mt-3">
                <a href="{{ route('horario_matriz.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar matriz</button>
            </div>
        </form>
    </div>
</div>

<template id="tpl-aula">
    <div class="row align-items-end mb-2 aula-row">
        <div class="col-md-1"><label class="form-label mb-0">#</label><input type="text" class="form-control form-control-sm num-aula" readonly></div>
        <div class="col-md-3"><label class="form-label mb-0">Início</label><input type="time" name="__IDX__[inicio]" class="form-control form-control-sm" required></div>
        <div class="col-md-3"><label class="form-label mb-0">Fim</label><input type="time" name="__IDX__[fim]" class="form-control form-control-sm" required></div>
        <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger btn-remover"><i class="fa fa-trash"></i></button></div>
    </div>
</template>
@stop

@section('js')
<script>
(function () {
    var lista = document.getElementById('lista-aulas');
    var tpl = document.getElementById('tpl-aula');
    var idx = {{ count(old('aulas') ?: $slotsAtuais) }};

    function renumerar() {
        lista.querySelectorAll('.aula-row').forEach(function (row, i) {
            row.querySelector('.num-aula') && (row.querySelector('.num-aula').value = i + 1);
            row.querySelectorAll('input[type="time"]').forEach(function (inp) {
                inp.name = inp.name.replace(/aulas\[\d+\]/, 'aulas[' + i + ']');
            });
        });
    }

    document.getElementById('btn-add-aula').addEventListener('click', function () {
        if (lista.querySelectorAll('.aula-row').length >= 12) return;
        var node = tpl.content.firstElementChild.cloneNode(true);
        node.querySelectorAll('input[type="time"]').forEach(function (inp) {
            inp.name = inp.name.replace('__IDX__', 'aulas[' + idx + ']');
        });
        idx++;
        lista.appendChild(node);
        renumerar();
    });

    lista.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remover');
        if (!btn) return;
        if (lista.querySelectorAll('.aula-row').length <= 1) return;
        btn.closest('.aula-row').remove();
        renumerar();
    });
})();
</script>
@stop
