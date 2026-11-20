@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($matricula) ? 'Editar Matrícula' : 'Nova Matrícula'" />
@stop

@push('css')
<style>
    .bolsa-box { border: 1px dashed #cbd5e1; border-radius: 12px; padding: .6rem .9rem; background: #f8fafc; }
    .bolseg { display: inline-flex; background: #fff; border: 1px solid #e2e8f0; border-radius: 999px; padding: 3px; gap: 2px; }
    .bolseg-opt { margin: 0; position: relative; }
    .bolseg-opt input { position: absolute; opacity: 0; pointer-events: none; }
    .bolseg-opt span { display: inline-flex; align-items: center; gap: .3rem; padding: .24rem .75rem; border-radius: 999px; font-size: .72rem; font-weight: 700; color: #64748b; cursor: pointer; transition: background .12s; }
    .bolseg-opt input:checked + span { background: #007bff; color: #fff; }
    .bolsa-corpo { margin-top: .6rem; display: flex; align-items: center; gap: .7rem; flex-wrap: wrap; }
    .bolsa-input { display: flex; align-items: center; gap: .35rem; }
    .bolsa-input input { width: 110px; }
    .bolsa-suf { font-size: .68rem; font-weight: 800; color: #64748b; background: #e2e8f0; border-radius: 8px; padding: .25rem .5rem; white-space: nowrap; }
    .bolsa-preview { font-size: .78rem; color: #475569; }
    .bolsa-preview b { color: #16a34a; }
    .bolsa-preview s { color: #94a3b8; }
    .bolsa-preview .bolsa-eco { color: #16a34a; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($matricula) ? route('matricula.update', $matricula->id) : route('matricula.store') }}">
            @csrf
            @if(isset($matricula))
                @method('PUT')
            @endif

            @if(isset($matricula))
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="aluno_nome" label="Aluno" :value="$matricula->aluno->pessoa->nome ?? ''" disabled />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="turma_nome" label="Turma" :value="$matricula->turma->nome ?? ''" disabled />
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="aluno_display">Aluno <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="aluno_display" class="form-control" placeholder="Nenhum aluno selecionado" readonly tabindex="-1">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btnBuscarAluno"><i class="fa fa-search mr-1"></i> Buscar</button>
                                <a href="{{ route('aluno.create') }}" target="_blank" class="btn btn-outline-success" title="Cadastrar novo aluno (abre em nova aba)"><i class="fa fa-user-plus"></i></a>
                            </div>
                        </div>
                        <small class="form-text text-muted">Aluno ainda não cadastrado? Cadastre pelo botão + e depois busque aqui.</small>
                        <input type="hidden" name="aluno_id" id="aluno_id" value="{{ old('aluno_id') }}">
                        @error('aluno_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="turma_display">Turma <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="turma_display" class="form-control" placeholder="Nenhuma turma selecionada" readonly tabindex="-1">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btnBuscarTurma"><i class="fa fa-search mr-1"></i> Buscar</button>
                            </div>
                        </div>
                        <input type="hidden" name="turma_id" id="turma_id" value="{{ old('turma_id', $turmaPreId ?? request('turma_id')) }}">
                        @error('turma_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-3">
                    <x-form-select name="status" label="Status" :options="$status ?? []" :selected="old('status', $matricula->status ?? 'ativa')" />
                </div>
                @if(isset($matricula))
                    <div class="col-md-3">
                        <x-form-input name="numero_chamada" type="number" label="Nº de chamada" :value="old('numero_chamada', $matricula->numero_chamada ?? '')" min="1" max="999" />
                    </div>
                @endif
                <div class="col-md-3">
                    <x-form-input name="data_matricula" type="date" label="Data da matrícula" :value="old('data_matricula', isset($matricula) && $matricula->data_matricula ? $matricula->data_matricula->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_saida" type="date" label="Data de saída" :value="old('data_saida', isset($matricula) && $matricula->data_saida ? $matricula->data_saida->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="plano_mensalidade_id" label="Plano de mensalidade" :options="$planos ?? []" :selected="old('plano_mensalidade_id', $matricula->plano_mensalidade_id ?? '')" placeholder="Sem plano" />
                </div>
                <div class="col-md-8">
                    @php
                        $bolsaTipoInicial = old('bolsa_tipo', $matricula->bolsa_tipo ?? '');
                        if ($bolsaTipoInicial === '' && (float) ($matricula->bolsa_valor ?? 0) > 0) {
                            $bolsaTipoInicial = 'valor';
                        }
                        if ($bolsaTipoInicial === '' && (float) ($matricula->bolsa_percentual ?? 0) > 0) {
                            $bolsaTipoInicial = 'percentual';
                        }
                    @endphp
                    <label class="form-label">Bolsa / desconto</label>
                    <div class="bolsa-box">
                        <div class="bolseg" id="bolseg">
                            <label class="bolseg-opt">
                                <input type="radio" name="bolsa_tipo" value="" {{ $bolsaTipoInicial === '' ? 'checked' : '' }}>
                                <span><i class="fas fa-ban"></i> Sem desconto</span>
                            </label>
                            <label class="bolseg-opt">
                                <input type="radio" name="bolsa_tipo" value="percentual" {{ $bolsaTipoInicial === 'percentual' ? 'checked' : '' }}>
                                <span><i class="fas fa-percent"></i> Percentual</span>
                            </label>
                            <label class="bolseg-opt">
                                <input type="radio" name="bolsa_tipo" value="valor" {{ $bolsaTipoInicial === 'valor' ? 'checked' : '' }}>
                                <span><i class="fas fa-dollar-sign"></i> Valor fixo</span>
                            </label>
                        </div>
                        <div class="bolsa-corpo" id="bolsa-corpo">
                            <div class="bolsa-input" id="bolsa-campo-percentual">
                                <input type="number" name="bolsa_percentual" id="bolsa_percentual" class="form-control form-control-sm"
                                       value="{{ old('bolsa_percentual', $matricula->bolsa_percentual ?? '') }}"
                                       min="0" max="100" step="0.01" placeholder="0">
                                <span class="bolsa-suf">% por mensalidade</span>
                            </div>
                            <div class="bolsa-input" id="bolsa-campo-valor">
                                <span class="bolsa-suf">R$</span>
                                <input type="number" name="bolsa_valor" id="bolsa_valor" class="form-control form-control-sm"
                                       value="{{ old('bolsa_valor', $matricula->bolsa_valor ?? '') }}"
                                       min="0" step="0.01" placeholder="0,00">
                                <span class="bolsa-suf">por mensalidade</span>
                            </div>
                            <div class="bolsa-preview" id="bolsa-preview"></div>
                        </div>
                    </div>
                    @error('bolsa_percentual')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                    @error('bolsa_valor')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3" maxlength="2000">{{ old('observacoes', $matricula->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var planosValores = @json($planosValores ?? {});
    var planoSel = document.querySelector('[name="plano_mensalidade_id"]');
    var corpo = document.getElementById('bolsa-corpo');
    var campoPct = document.getElementById('bolsa-campo-percentual');
    var campoVal = document.getElementById('bolsa-campo-valor');
    var inputPct = document.getElementById('bolsa_percentual');
    var inputVal = document.getElementById('bolsa_valor');
    var preview = document.getElementById('bolsa-preview');

    if (!planoSel || !corpo) { return; }

    function fmt(v) {
        return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function tipoAtual() {
        var r = document.querySelector('input[name="bolsa_tipo"]:checked');
        return r ? r.value : '';
    }

    function atualizar() {
        var tipo = tipoAtual();
        corpo.style.display = tipo === '' ? 'none' : 'flex';
        campoPct.style.display = tipo === 'percentual' ? 'flex' : 'none';
        campoVal.style.display = tipo === 'valor' ? 'flex' : 'none';

        var planoValor = parseFloat(planosValores[planoSel.value]) || 0;
        if (tipo === '' || planoValor <= 0) {
            preview.innerHTML = planoValor > 0 ? '' : 'Selecione um plano para ver a mensalidade final.';
            return;
        }

        var liquido = planoValor;
        if (tipo === 'percentual') {
            var p = Math.min(parseFloat(inputPct.value) || 0, 100);
            liquido = planoValor * (1 - p / 100);
        } else {
            liquido = Math.max(0, planoValor - (parseFloat(inputVal.value) || 0));
        }
        liquido = Math.round(liquido * 100) / 100;
        var economia = planoValor - liquido;

        preview.innerHTML = 'Mensalidade: <s>' + fmt(planoValor) + '</s> → <b>' + fmt(liquido) + '</b>'
            + (economia > 0 ? ' <span class="bolsa-eco">(−' + fmt(economia) + '/mês)</span>' : '');
    }

    document.querySelectorAll('input[name="bolsa_tipo"]').forEach(function (r) {
        r.addEventListener('change', atualizar);
    });
    planoSel.addEventListener('change', atualizar);
    inputPct.addEventListener('input', atualizar);
    inputVal.addEventListener('input', atualizar);

    atualizar();
})();
</script>

@unless(isset($matricula))
@include('partials.entity-picker')
<script>
(function () {
    const alunosData = @json($alunosPicker ?? []);
    const turmasData = @json($turmasPicker ?? []);
    const form = document.querySelector('form[action="{{ route('matricula.store') }}"]');

    function preencherDisplay(input, display, dados, vazio) {
        const item = dados.find(function (d) { return String(d.id) === input.value; });
        display.value = item ? item.rotulo : vazio;
        display.classList.toggle('is-invalid', false);
    }

    function registrar(campo) {
        const input = document.getElementById(campo + '_id');
        const display = document.getElementById(campo + '_display');
        const vazio = display.placeholder;
        input.addEventListener('change', function () { preencherDisplay(input, display, window['__' + campo + 'Data'], vazio); });
        return { input: input, display: display, vazio: vazio };
    }

    window['__alunoData'] = alunosData;
    window['__turmaData'] = turmasData;
    const aluno = registrar('aluno');
    const turma = registrar('turma');

    EntityPicker.registrar('aluno', {
        titulo: 'Buscar aluno',
        dados: alunosData,
        filtros: [{ campo: 'escola_id', campoRotulo: 'escola', label: 'Escola' }],
        onSelect: function (d) {
            aluno.input.value = d.id;
            aluno.input.dispatchEvent(new Event('change'));
        },
    });
    EntityPicker.registrar('turma', {
        titulo: 'Buscar turma',
        dados: turmasData,
        filtros: [
            { campo: 'escola_id', campoRotulo: 'escola', label: 'Escola' },
            { campo: 'ano_letivo_id', campoRotulo: 'ano', label: 'Ano letivo' },
        ],
        onSelect: function (d) {
            turma.input.value = d.id;
            turma.input.dispatchEvent(new Event('change'));
        },
    });

    document.getElementById('btnBuscarAluno').addEventListener('click', function () { EntityPicker.abrir('aluno'); });
    document.getElementById('btnBuscarTurma').addEventListener('click', function () { EntityPicker.abrir('turma'); });

    // Inicializa os displays (old() ou ?turma_id=)
    preencherDisplay(aluno.input, aluno.display, alunosData, aluno.vazio);
    preencherDisplay(turma.input, turma.display, turmasData, turma.vazio);
    if (!aluno.input.value) { aluno.display.value = ''; }
    if (!turma.input.value) { turma.display.value = ''; }

    form.addEventListener('submit', function (e) {
        let focou = false;
        [[aluno, 'aluno'], [turma, 'turma']].forEach(function (par) {
            const vazio = !par[0].input.value;
            par[0].display.classList.toggle('is-invalid', vazio);
            if (vazio && !focou) { focou = true; EntityPicker.abrir(par[1]); }
        });
        if (focou) { e.preventDefault(); }
    });
})();
</script>
@endunless
@stop
