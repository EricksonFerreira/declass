@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($matricula) ? 'Editar Matrícula' : 'Nova Matrícula'" />
@stop

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
                <div class="col-md-3">
                    <x-form-input name="numero_chamada" type="number" label="Nº de chamada" :value="old('numero_chamada', $matricula->numero_chamada ?? '')" min="1" max="999" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_matricula" type="date" label="Data da matrícula" :value="old('data_matricula', isset($matricula) && $matricula->data_matricula ? $matricula->data_matricula->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_saida" type="date" label="Data de saída" :value="old('data_saida', isset($matricula) && $matricula->data_saida ? $matricula->data_saida->format('Y-m-d') : '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="plano_mensalidade_id" label="Plano de mensalidade" :options="$planos ?? []" :selected="old('plano_mensalidade_id', $matricula->plano_mensalidade_id ?? '')" placeholder="Sem plano" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="bolsa_percentual" type="number" label="Bolsa / desconto (%)" :value="old('bolsa_percentual', $matricula->bolsa_percentual ?? '')" min="0" max="100" step="0.01" placeholder="0" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="3" maxlength="2000">{{ old('observacoes', $matricula->observacoes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('matricula.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

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
