@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($avaliacao) ? 'Editar Avaliação' : 'Nova Avaliação'">
        <span class="badge badge-danger align-middle ml-2" data-toggle="tooltip" title="Avaliação entra no diário de notas e conta na média do boletim — diferente de Tarefa, que é atividade de conteúdo com entrega pelo portal."><i class="fa fa-star mr-1"></i>Avaliação</span>
    </x-page-title>
@stop

@push('css')
<style>
    .av-steps { display:flex; justify-content:space-between; position:relative; margin:0 4px 1.4rem; }
    .av-steps::before { content:''; position:absolute; top:15px; left:12%; right:12%; height:2px; background:#e5e7eb; z-index:0; }
    .av-step-ind { position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; gap:4px; cursor:pointer; min-width:90px; }
    .av-step-num { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; background:#fff; border:2px solid #d1d5db; color:#9ca3af; }
    .av-step-label { font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; text-align:center; }
    .av-step-ind.done .av-step-num { background:#10b981; border-color:#10b981; color:#fff; }
    .av-step-ind.done .av-step-label { color:#059669; }
    .av-step-ind.active .av-step-num { background:#dc2626; border-color:#dc2626; color:#fff; box-shadow:0 0 0 4px rgba(220,38,38,.15); }
    .av-step-ind.active .av-step-label { color:#dc2626; }
    .av-pane { display:none; }
    .av-pane.active { display:block; }
    .av-foot { display:flex; justify-content:space-between; align-items:center; margin-top:1.1rem; padding-top:1rem; border-top:1px solid #f0f3f8; }
    .av-foot__nav { display:flex; gap:.5rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="av-steps">
            <div class="av-step-ind" data-goto="0"><span class="av-step-num">1</span><span class="av-step-label">Turma e avaliação</span></div>
            <div class="av-step-ind" data-goto="1"><span class="av-step-num">2</span><span class="av-step-label">Detalhes</span></div>
        </div>

        <form method="POST" action="{{ isset($avaliacao) ? route('avaliacao.update', $avaliacao->id) : route('avaliacao.store') }}" id="avaliacaoForm" novalidate>
            @csrf
            @if(isset($avaliacao))
                @method('PUT')
            @endif

            {{-- Etapa 1: turma, disciplina e o basico --}}
            <div class="av-pane active" data-step="0">
                <div class="row">
                    <div class="col-md-6">
                        @if(isset($avaliacao))
                            <x-form-input name="turma_nome" label="Turma" :value="$avaliacao->turma->nome ?? ''" disabled />
                        @else
                            <label class="form-label" for="turma_display">Turma <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="turma_display" class="form-control" placeholder="Nenhuma turma selecionada" readonly tabindex="-1">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="btnBuscarTurma" title="Buscar turma"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                            <input type="hidden" name="turma_id" id="turma_id" value="{{ old('turma_id', request('turma_id')) }}" required>
                            @error('turma_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if(isset($avaliacao))
                            <x-form-input name="disciplina_nome" label="Disciplina" :value="$avaliacao->disciplina->nome ?? ''" disabled />
                        @else
                            <div class="form-group">
                                <label class="form-label" for="disciplina_id">Disciplina <span class="text-danger">*</span></label>
                                <select name="disciplina_id" id="disciplina_id" class="form-control" required>
                                    <option value="">Selecione a turma primeiro</option>
                                </select>
                                @error('disciplina_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <x-form-input name="nome" label="Nome da avaliação" :value="old('nome', $avaliacao->nome ?? '')" required maxlength="150" placeholder="Ex.: Prova 1 — Equações" />
                    </div>
                    <div class="col-md-3">
                        <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $avaliacao->tipo ?? 'prova')" required />
                    </div>
                    <div class="col-md-2">
                        @if(isset($avaliacao))
                            <x-form-input name="periodo" label="Período" :value="$avaliacao->periodo ?? ''" disabled />
                        @else
                            <div class="form-group">
                                <label class="form-label">Período letivo</label>
                                <select name="periodo_letivo_id" id="periodo_letivo_id" class="form-control">
                                    <option value="">Sem período</option>
                                </select>
                                <small class="text-muted">Períodos do ano letivo da turma.</small>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <x-form-input name="data" type="date" label="Data de aplicação" :value="old('data', isset($avaliacao) && $avaliacao->data ? $avaliacao->data->format('Y-m-d') : '')" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <x-form-input name="nota_maxima" type="number" label="Nota máxima" :value="old('nota_maxima', $avaliacao->nota_maxima ?? 10)" min="0.1" max="1000" step="0.1" required />
                    </div>
                </div>
            </div>

            {{-- Etapa 2: detalhes --}}
            <div class="av-pane" data-step="1">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Descrição / instruções</label>
                        <textarea name="descricao" class="form-control" rows="4" maxlength="10000">{{ old('descricao', $avaliacao->descricao ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3" maxlength="2000">{{ old('observacoes', $avaliacao->observacoes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="av-foot">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
                <div class="av-foot__nav">
                    <button type="button" class="btn btn-outline-secondary" id="btnVoltar" style="display:none;"><i class="fa fa-arrow-left mr-1"></i> Voltar</button>
                    <button type="button" class="btn btn-danger" id="btnProximo">Próximo <i class="fa fa-arrow-right ml-1"></i></button>
                    <button type="submit" class="btn btn-danger" id="btnSalvar" style="display:none;"><i class="fa fa-check mr-1"></i> Salvar avaliação</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const panes = Array.from(document.querySelectorAll('.av-pane'));
    const inds = Array.from(document.querySelectorAll('.av-step-ind'));
    const btnVoltar = document.getElementById('btnVoltar');
    const btnProximo = document.getElementById('btnProximo');
    const btnSalvar = document.getElementById('btnSalvar');
    const form = document.getElementById('avaliacaoForm');
    let atual = 0;

    function paint() {
        inds.forEach(function (ind, i) {
            const num = ind.querySelector('.av-step-num');
            ind.classList.toggle('active', i === atual);
            ind.classList.toggle('done', i < atual);
            num.textContent = i < atual ? '✓' : String(i + 1);
        });
    }
    function mostrar(i) {
        atual = Math.max(0, Math.min(i, panes.length - 1));
        panes.forEach(function (p, j) { p.classList.toggle('active', j === atual); });
        paint();
        btnVoltar.style.display = atual > 0 ? '' : 'none';
        btnProximo.style.display = atual === panes.length - 1 ? 'none' : '';
        btnSalvar.style.display = atual === panes.length - 1 ? '' : 'none';
    }
    function primeiroInvalido(pane, silencioso) {
        for (const el of pane.querySelectorAll('input, select, textarea')) {
            if (el.willValidate && !el.checkValidity()) {
                if (!silencioso) { el.reportValidity(); }
                return el;
            }
        }
        return null;
    }

    btnProximo.addEventListener('click', function () {
        if (primeiroInvalido(panes[atual], false)) { return; }
        mostrar(atual + 1);
    });
    btnVoltar.addEventListener('click', function () { mostrar(atual - 1); });
    inds.forEach(function (ind) { ind.addEventListener('click', function () { mostrar(parseInt(ind.dataset.goto, 10)); }); });

    const paneComErro = panes.find(function (p) { return p.querySelector('.invalid-feedback'); });
    mostrar(paneComErro ? panes.indexOf(paneComErro) : 0);

    form.addEventListener('submit', function (e) {
        // Turma e hidden: valida antes de tudo.
        const turmaInput = document.getElementById('turma_id');
        if (turmaInput && !turmaInput.value) {
            e.preventDefault();
            e.stopImmediatePropagation();
            document.getElementById('turma_display').classList.add('is-invalid');
            mostrar(0);
            setTimeout(function () { EntityPicker.abrir('turma'); }, 60);
            return;
        }
        for (let i = 0; i < panes.length; i++) {
            const inv = primeiroInvalido(panes[i], true);
            if (inv) {
                e.preventDefault();
                mostrar(i);
                setTimeout(function () { inv.reportValidity(); }, 50);
                return;
            }
        }
    });

    @if(! isset($avaliacao))
    // ---------- Disciplina + periodo por turma ----------
    const disciplinasPorTurma = {!! $disciplinasPorTurma ?? '{}' !!};
    const periodosPorTurma = {!! $periodosJson ?? '{}' !!};
    const selTurma = document.getElementById('turma_id');
    const selDisc = document.getElementById('disciplina_id');
    const selPeriodo = document.getElementById('periodo_letivo_id');
    const oldDisc = "{{ old('disciplina_id') }}";
    const oldPeriodo = "{{ old('periodo_letivo_id') }}";

    function esc(s) { const d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    function preencherTurmaDependentes() {
        const listaDisc = disciplinasPorTurma[selTurma.value] || {};
        selDisc.innerHTML = '<option value="">Selecionar</option>';
        Object.entries(listaDisc).forEach(function (p) {
            const o = document.createElement('option');
            o.value = p[0]; o.textContent = p[1];
            if (String(p[0]) === oldDisc) { o.selected = true; }
            selDisc.appendChild(o);
        });
        const listaP = periodosPorTurma[selTurma.value] || [];
        selPeriodo.innerHTML = '<option value="">Sem período</option>';
        listaP.forEach(function (p) {
            const o = document.createElement('option');
            o.value = p.id; o.textContent = p.nome;
            if (String(p.id) === oldPeriodo) { o.selected = true; }
            selPeriodo.appendChild(o);
        });
    }
    selTurma.addEventListener('change', preencherTurmaDependentes);
    preencherTurmaDependentes();
    @endif
})();
</script>

@unless(isset($avaliacao))
@include('partials.entity-picker')
<script>
(function () {
    const turmasData = @json($turmasPicker ?? []);
    turmasData.forEach(function (t) {
        t.extraHtml = t.encerrado
            ? ' <span class="badge badge-danger ml-1">Encerrado</span>'
            : ' <span class="badge badge-success ml-1">Letivo</span>';
    });
    const input = document.getElementById('turma_id');
    const display = document.getElementById('turma_display');

    function preencherDisplay() {
        const item = turmasData.find(function (d) { return String(d.id) === input.value; });
        display.value = item ? item.rotulo : '';
        display.classList.remove('is-invalid');
    }

    EntityPicker.registrar('turma', {
        titulo: 'Buscar turma',
        dados: turmasData,
        filtros: [
            { campo: 'escola_id', campoRotulo: 'escola', label: 'Escola' },
            { campo: 'ano_letivo_id', campoRotulo: 'ano', label: 'Ano letivo', porRotulo: true },
        ],
        onSelect: function (d) {
            input.value = d.id;
            preencherDisplay();
            input.dispatchEvent(new Event('change'));
        },
    });
    document.getElementById('btnBuscarTurma').addEventListener('click', function () { EntityPicker.abrir('turma'); });
    input.addEventListener('change', preencherDisplay);
    preencherDisplay();
})();
</script>
@endunless
@stop
