@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($tarefa) ? 'Editar Tarefa' : 'Nova Tarefa'" />
@stop

@push('css')
<style>
    .tarefa-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin: 0 4px 1.4rem;
    }
    .tarefa-steps::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 12%;
        right: 12%;
        height: 2px;
        background: #e5e7eb;
        z-index: 0;
    }
    .tarefa-step-ind {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        min-width: 90px;
    }
    .tarefa-step-num {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .8rem;
        background: #fff;
        border: 2px solid #d1d5db;
        color: #9ca3af;
        transition: all .15s ease;
    }
    .tarefa-step-label {
        font-size: .7rem;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-align: center;
    }
    .tarefa-step-ind.done .tarefa-step-num {
        background: #10b981;
        border-color: #10b981;
        color: #fff;
    }
    .tarefa-step-ind.done .tarefa-step-label { color: #059669; }
    .tarefa-step-ind.active .tarefa-step-num {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-color: #4f46e5;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .15);
    }
    .tarefa-step-ind.active .tarefa-step-label { color: #4f46e5; }
    .tarefa-step-pane { display: none; }
    .tarefa-step-pane.active { display: block; }
    .tarefa-step-pane__titulo {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: .9rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .tarefa-step-pane__titulo .badge { font-weight: 600; }
    .tarefa-step-pane__sub {
        font-size: .8rem;
        color: #64748b;
        margin: -.4rem 0 .9rem;
    }
    .tarefa-wizard-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.1rem;
        padding-top: 1rem;
        border-top: 1px solid #f0f3f8;
    }
    .tarefa-wizard-foot__nav { display: flex; gap: .5rem; }
    @media (max-width: 575.98px) {
        .tarefa-step-label { font-size: .6rem; }
        .tarefa-step-ind { min-width: 60px; }
        .tarefa-steps::before { left: 15%; right: 15%; }
    }

    /* Cards de secao (padrao uf-*) */
    .uf-card { background: #fff; border: 1px solid #e8edf5; border-radius: 16px; box-shadow: 0 1px 3px rgba(15,23,42,.05); margin-bottom: 1rem; overflow: hidden; }
    .uf-head { display: flex; align-items: center; gap: .6rem; padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9; background: #fbfcfe; }
    .uf-head i { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .78rem; flex-shrink: 0; }
    .uf-head .uf-t { font-size: .86rem; font-weight: 800; color: #0f172a; }
    .uf-head .uf-s { font-size: .72rem; color: #94a3b8; }
    .uf-head .uf-opt { margin-left: auto; font-size: .66rem; font-weight: 700; color: #94a3b8; background: #f1f5f9; border-radius: 999px; padding: 2px 9px; }
    .uf-ic--blue { background: #eff6ff; color: #3b82f6; }
    .uf-ic--green { background: #f0fdf4; color: #22c55e; }
    .uf-ic--violet { background: #f5f3ff; color: #8b5cf6; }
    .uf-ic--amber { background: #fffbeb; color: #f59e0b; }
    .uf-body { padding: 1.1rem 1.15rem .4rem; }
    .uf-body .form-control { border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .85rem; }
    .uf-body .form-control:focus { border-color: #93c5fd; box-shadow: 0 0 0 .15rem rgba(59,130,246,.12); }
    .uf-body label { font-size: .78rem; font-weight: 700; color: #475569; }
    .uf-pills { display: flex; gap: .45rem; flex-wrap: wrap; }
    .uf-pill { margin: 0; }
    .uf-pill input { position: absolute; opacity: 0; pointer-events: none; }
    .uf-pill span { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem .95rem; border-radius: 999px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .78rem; font-weight: 700; color: #64748b; cursor: pointer; transition: all .13s; }
    .uf-pill span:hover { border-color: #c7d2fe; }
    .uf-pill input:checked + span { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    .uf-pill--blue input:checked + span { background: #3b82f6; border-color: #3b82f6; }
    .uf-pill--green input:checked + span { background: #16a34a; border-color: #16a34a; }
    .uf-pill--red input:checked + span { background: #dc2626; border-color: #dc2626; }
    .uf-hint { display: flex; align-items: flex-start; gap: .45rem; background: #f8fafc; border: 1.5px dashed #e2e8f0; border-radius: 10px; padding: .55rem .8rem; font-size: .72rem; color: #64748b; }
    .uf-hint i { color: #8b5cf6; margin-top: .1rem; }
    .uf-dlist { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: .45rem .7rem; min-height: 40px; max-height: 150px; overflow-y: auto; background: #fbfcfe; }
    .uf-dlist .form-check { padding: .15rem 0 .15rem 1.4rem; font-size: .82rem; }
    .tarefa-wizard-foot .btn { border-radius: 999px; font-weight: 700; }
</style>
@endpush

@section('content')
        <div class="tarefa-steps" id="tarefaSteps">
            <div class="tarefa-step-ind" data-goto="0">
                <span class="tarefa-step-num">1</span>
                <span class="tarefa-step-label">Geral</span>
            </div>
            <div class="tarefa-step-ind" data-goto="1">
                <span class="tarefa-step-num">2</span>
                <span class="tarefa-step-label">Conteúdo e entrega</span>
            </div>
            <div class="tarefa-step-ind" data-goto="2">
                <span class="tarefa-step-num">3</span>
                <span class="tarefa-step-label">Questões <span class="badge badge-light border font-weight-normal text-lowercase">opcional</span></span>
            </div>
        </div>

        <form method="POST" action="{{ isset($tarefa) ? route('tarefa.update', $tarefa->id) : route('tarefa.store') }}" id="tarefaForm" novalidate>
            @csrf
            @if(isset($tarefa))
                @method('PUT')
            @endif

            {{-- Etapa 1: dados gerais --}}
            <div class="tarefa-step-pane active" data-step="0">
                <div class="uf-card">
                    <div class="uf-head">
                        <i class="uf-ic--blue fas fa-info-circle"></i>
                        <div>
                            <div class="uf-t">Informações gerais</div>
                            <div class="uf-s">Para qual turma e disciplina a tarefa será publicada</div>
                        </div>
                    </div>
                    <div class="uf-body">
                        <div class="row">
                            <div class="col-md-6">
                                @if(isset($tarefa))
                                    <x-form-input name="turma_nome" label="Turma" :value="$tarefa->turma->nome ?? ''" disabled />
                                @else
                                    <label class="form-label" for="turma_display">Turma <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" id="turma_display" class="form-control" placeholder="Nenhuma turma selecionada" readonly tabindex="-1">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" id="btnBuscarTurma" title="Buscar turma"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="turma_id" id="turma_id" value="{{ old('turma_id', $turmaPreId ?? '') }}" required>
                                    @error('turma_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if(isset($tarefa))
                                    <x-form-select name="disciplina_id" label="Disciplina" :options="['' => 'Sem disciplina específica']" id="disciplina_id" :selected="old('disciplina_id', $tarefa->disciplina_id ?? '')" />
                                @else
                                    <label class="form-label">Disciplinas</label>
                                    <div id="disciplinas-lista" class="uf-dlist">
                                        <small class="text-muted">Selecione a turma para listar as disciplinas.</small>
                                    </div>
                                    <small class="form-text text-muted">Nenhuma marcada = vale para a turma toda.</small>
                                    @error('disciplina_ids')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <x-form-input name="titulo" label="Título" :value="old('titulo', $tarefa->titulo ?? '')" required maxlength="200" placeholder="Ex.: Exercícios da página 42" />
                            </div>
                            <div class="col-md-3">
                                <x-form-input name="data_entrega" type="date" label="Data de entrega" :value="old('data_entrega', isset($tarefa) ? $tarefa->data_entrega->format('Y-m-d') : '')" required />
                            </div>
                            <div class="col-md-3">
                                <x-form-input name="nota_maxima" type="number" label="Nota máx." :value="old('nota_maxima', $tarefa->nota_maxima ?? '')" min="0" max="100" step="0.5" />
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Tipo</label>
                                @php $tipoSel = old('tipo', $tarefa->tipo ?? 'atividade'); @endphp
                                <div class="uf-pills mb-3">
                                    @foreach(['atividade' => ['Atividade', 'fa-tasks'], 'prova' => ['Prova', 'fa-star'], 'trabalho' => ['Trabalho', 'fa-briefcase']] as $tv => $td)
                                        <label class="uf-pill">
                                            <input type="radio" name="tipo" value="{{ $tv }}" {{ $tipoSel === $tv ? 'checked' : '' }}>
                                            <span><i class="fa {{ $td[1] }}"></i>{{ $td[0] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('tipo')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                @php $ativoSel = old('ativo', $tarefa->ativo ?? 'S'); @endphp
                                <div class="uf-pills mb-3">
                                    <label class="uf-pill uf-pill--green">
                                        <input type="radio" name="ativo" value="S" {{ $ativoSel === 'S' ? 'checked' : '' }}>
                                        <span><i class="fa fa-check-circle"></i>Ativa</span>
                                    </label>
                                    <label class="uf-pill uf-pill--red">
                                        <input type="radio" name="ativo" value="N" {{ $ativoSel === 'N' ? 'checked' : '' }}>
                                        <span><i class="fa fa-ban"></i>Inativa</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Etapa 2: conteudo e entrega --}}
            <div class="tarefa-step-pane" data-step="1">
                <div class="uf-card">
                    <div class="uf-head">
                        <i class="uf-ic--green fas fa-file-alt"></i>
                        <div>
                            <div class="uf-t">Conteúdo e entrega</div>
                            <div class="uf-s">Como o aluno recebe e devolve a tarefa pelo portal</div>
                        </div>
                    </div>
                    <div class="uf-body">
                        <label class="form-label">Entrega pelo portal</label>
                        @php $entregaSel = old('tipo_entrega', $tarefa->tipo_entrega ?? 'nenhuma'); @endphp
                        <div class="uf-pills mb-2">
                            @foreach(['nenhuma' => ['Sem envio online', 'fa-ban'], 'leitura' => ['Confirmação de leitura', 'fa-book-open'], 'texto' => ['Texto digitado', 'fa-keyboard'], 'foto' => ['Fotos/arquivos', 'fa-camera']] as $ev => $ed)
                                <label class="uf-pill uf-pill--blue">
                                    <input type="radio" name="tipo_entrega" value="{{ $ev }}" {{ $entregaSel === $ev ? 'checked' : '' }}>
                                    <span><i class="fa {{ $ed[1] }}"></i>{{ $ed[0] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="uf-hint mb-3">
                            <i class="fa fa-info-circle"></i>
                            <span>Leitura: o aluno só confirma que leu. Texto: ele digita a resposta. Fotos/arquivos: ele envia imagens do que fez. Envios ficam <strong>aguardando sua correção</strong> para receber nota.</span>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Descrição</label>
                                <textarea name="descricao" class="form-control" rows="4" maxlength="4000"
                                    placeholder="O que deve ser feito, formato de entrega, materiais necessários.">{{ old('descricao', $tarefa->descricao ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Instruções</label>
                                <textarea name="instrucoes" class="form-control" rows="3" maxlength="4000"
                                    placeholder="Passo a passo, links, observações para os alunos.">{{ old('instrucoes', $tarefa->instrucoes ?? '') }}</textarea>
                            </div>
                        </div>

                        @if(!isset($tarefa))
                            <div class="uf-hint mb-3">
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="gerar_entregas" value="1" class="form-check-input" id="gerar_entregas" {{ old('gerar_entregas') ? 'checked' : 'checked' }}>
                                    <label class="form-check-label" for="gerar_entregas" style="font-weight:700;">
                                        Criar controle de entregas para todos os alunos da turma
                                        <div class="text-muted" style="font-weight:400;">Permite lançar status e notas por aluno</div>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Etapa 3: questoes online (opcional) --}}
            <div class="tarefa-step-pane" data-step="2">
                <div class="uf-card">
                    <div class="uf-head">
                        <i class="uf-ic--amber fas fa-question-circle"></i>
                        <div>
                            <div class="uf-t">Questões respondidas online</div>
                            <div class="uf-s">O aluno responde pelo celular no portal — objetivas corrigem sozinhas</div>
                        </div>
                        <span class="uf-opt">opcional</span>
                    </div>
                    <div class="uf-body">
                        <div class="d-flex justify-content-end mb-2" style="gap:.4rem;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-preview-questoes"><i class="fa fa-eye mr-1"></i> Ver prévia</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-questao"><i class="fa fa-plus"></i> Adicionar questão</button>
                        </div>
                        <div id="questoes-lista"></div>

                {{-- Previa das questoes em formato de prova --}}
                <div class="modal fade" id="modalPreviewQuestoes" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header py-2">
                                <h5 class="modal-title"><i class="fas fa-file-alt mr-1"></i> Prévia das questões</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span>&times;</span></button>
                            </div>
                            <div class="modal-body" id="previewQuestoes"></div>
                            <div class="modal-footer py-2">
                                <span class="mr-auto small text-muted">O botão imprimir permite salvar como PDF pelo navegador.</span>
                                <button type="button" class="btn btn-sm btn-primary" id="btnImprimirPreview"><i class="fa fa-print mr-1"></i> Imprimir / PDF</button>
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>

            <div class="tarefa-wizard-foot">
                <a href="{{ route('tarefa.index') }}" class="btn btn-secondary">Cancelar</a>
                <div class="tarefa-wizard-foot__nav">
                    <button type="button" class="btn btn-outline-secondary" id="btnVoltar" style="display:none;">
                        <i class="fa fa-arrow-left mr-1"></i> Voltar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnProximo">
                        Próximo <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="btnSalvar" style="display:none;">
                        <i class="fa fa-check mr-1"></i> Salvar tarefa
                    </button>
                </div>
            </div>
        </form>

<script>
(function () {
    // ---------- Stepper ----------
    const panes = Array.from(document.querySelectorAll('.tarefa-step-pane'));
    const inds = Array.from(document.querySelectorAll('.tarefa-step-ind'));
    const btnVoltar = document.getElementById('btnVoltar');
    const btnProximo = document.getElementById('btnProximo');
    const btnSalvar = document.getElementById('btnSalvar');
    const form = document.getElementById('tarefaForm');
    let atual = 0;

    function paintInds() {
        inds.forEach(function (ind, i) {
            const num = ind.querySelector('.tarefa-step-num');
            ind.classList.toggle('active', i === atual);
            ind.classList.toggle('done', i < atual);
            num.textContent = i < atual ? '✓' : String(i + 1);
        });
    }

    function mostrar(i) {
        atual = Math.max(0, Math.min(i, panes.length - 1));
        panes.forEach(function (p, j) { p.classList.toggle('active', j === atual); });
        paintInds();
        const ultimo = atual === panes.length - 1;
        btnVoltar.style.display = atual > 0 ? '' : 'none';
        btnProximo.style.display = ultimo ? 'none' : '';
        btnSalvar.style.display = ultimo ? '' : 'none';
    }
    window.__tarefaMostrar = mostrar;

    function primeiroInvalido(pane, silencioso) {
        const els = pane.querySelectorAll('input, select, textarea');
        for (const el of els) {
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
    inds.forEach(function (ind) {
        ind.addEventListener('click', function () { mostrar(parseInt(ind.dataset.goto, 10)); });
    });

    // Se o servidor devolveu erros, reabre a etapa do primeiro campo invalido.
    const paneComErro = panes.find(function (p) { return p.querySelector('.invalid-feedback'); });
    if (paneComErro) {
        mostrar(panes.indexOf(paneComErro));
    } else {
        mostrar(0);
    }

    // Validacao propria no submit: com novalidate, nenhum campo obrigatorio
    // fica escondido sem feedback — a etapa do primeiro invalido e reaberta.
    form.addEventListener('submit', function (e) {
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

    // ---------- Disciplinas por turma ----------
    const disciplinasPorTurma = {!! $disciplinasPorTurma ?? '{}' !!};
    const selTurma = document.getElementById('turma_id');
    const selDisc = document.getElementById('disciplina_id');
    const listaDisc = document.getElementById('disciplinas-lista');
    const oldDisc = "{{ old('disciplina_id', $tarefa->disciplina_id ?? '') }}";
    const oldDiscIds = @json(old('disciplina_ids', null));

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function preencherDisciplinas() {
        const lista = disciplinasPorTurma[selTurma ? selTurma.value : ''] || {};

        if (listaDisc) {
            const ids = Object.keys(lista);
            listaDisc.innerHTML = '';
            if (!(selTurma && selTurma.value)) {
                listaDisc.innerHTML = '<small class="text-muted">Selecione a turma para listar as disciplinas.</small>';
            } else if (!ids.length) {
                listaDisc.innerHTML = '<small class="text-muted">Turma sem disciplinas ofertadas — a tarefa vale para a turma toda.</small>';
            } else {
                // Padrao: todas marcadas; em erro de validacao volta a selecao feita.
                const marcadas = Array.isArray(oldDiscIds) ? oldDiscIds.map(String) : ids;
                ids.forEach(function (id) {
                    const wrap = document.createElement('div');
                    wrap.className = 'form-check';
                    wrap.innerHTML =
                        '<input type="checkbox" class="form-check-input" name="disciplina_ids[]" value="' + esc(id) + '" id="disc_' + esc(id) + '"' +
                        (marcadas.indexOf(String(id)) >= 0 ? ' checked' : '') + '>' +
                        '<label class="form-check-label" for="disc_' + esc(id) + '">' + esc(lista[id]) + '</label>';
                    listaDisc.appendChild(wrap);
                });
            }
        }

        if (selDisc) {
            selDisc.innerHTML = '<option value="">Sem disciplina específica</option>';
            Object.entries(lista).forEach(([id, nome]) => {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = nome;
                if (String(id) === oldDisc) opt.selected = true;
                selDisc.appendChild(opt);
            });
        }
    }

    if (selTurma) {
        selTurma.addEventListener('change', preencherDisciplinas);
        preencherDisciplinas();
    }

    // ---------- Questoes online: linhas dinamicas com alternativas A-E ----------
    const questoesIniciais = {!! isset($tarefa) ? $tarefa->questoes->map(fn ($q) => ['tipo' => $q->tipo, 'enunciado' => $q->enunciado, 'alternativas' => $q->alternativas ?? [], 'gabarito' => $q->gabarito, 'valor' => $q->valor])->values()->toJson() : '[]' !!};
    const lista = document.getElementById('questoes-lista');
    const letras = ['A', 'B', 'C', 'D', 'E'];
    let idx = 0;

    function escQ(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function templateQuestao(i, q, expandida) {
        q = q || {};
        const alts = (q.alternativas || {});
        let altsHtml = '';
        letras.forEach(function (letra) {
            altsHtml += '<div class="input-group input-group-sm mb-1">' +
                '<div class="input-group-prepend"><span class="input-group-text font-weight-bold">' + letra + '</span></div>' +
                '<input type="text" class="form-control" name="questoes[' + i + '][alternativas][' + letra + ']" maxlength="500" value="' + (alts[letra] || '').replace(/"/g, '&quot;') + '">' +
                '</div>';
        });
        let gabHtml = '<option value="">Gabarito…</option>';
        letras.forEach(function (letra) {
            gabHtml += '<option value="' + letra + '"' + (q.gabarito === letra ? ' selected' : '') + '>' + letra + '</option>';
        });

        const div = document.createElement('div');
        div.className = 'card card-outline card-secondary mb-2 questao-item';
        div.innerHTML =
            '<div class="questao-head d-flex align-items-center px-2 py-1 border-bottom" style="gap:.5rem;cursor:pointer;" title="Expandir / recolher">' +
            '<i class="fas fa-chevron-down questao-caret" style="font-size:.7rem;transition:transform .15s;"></i>' +
            '<strong class="questao-num small">Questão</strong>' +
            '<span class="questao-tipo-lbl badge badge-light border">Objetiva</span>' +
            '<span class="questao-valor badge badge-light border" style="display:none;"></span>' +
            '<span class="questao-resumo text-muted small text-truncate flex-grow-1"></span>' +
            '<button type="button" class="btn btn-xs btn-outline-danger btn-del-questao"><i class="fa fa-trash"></i></button>' +
            '</div>' +
            '<div class="questao-body"><div class="card-body py-2">' +
            '<div class="d-flex align-items-center mb-2" style="gap:.5rem;">' +
            '<select name="questoes[' + i + '][tipo]" class="form-control form-control-sm questao-tipo" style="max-width:170px;">' +
            '<option value="objetiva"' + (q.tipo !== 'discursiva' ? ' selected' : '') + '>Objetiva (A–E)</option>' +
            '<option value="discursiva"' + (q.tipo === 'discursiva' ? ' selected' : '') + '>Discursiva</option>' +
            '</select>' +
            '<input type="number" name="questoes[' + i + '][valor]" class="form-control form-control-sm questao-valor-inp" style="max-width:110px;" placeholder="Valor (pts)" min="0" step="0.5" value="' + (q.valor ?? '') + '">' +
            '</div>' +
            '<textarea name="questoes[' + i + '][enunciado]" class="form-control form-control-sm mb-2 questao-enunciado" rows="2" maxlength="2000" placeholder="Enunciado da questão" required>' + escQ(q.enunciado || '') + '</textarea>' +
            '<div class="questao-alts">' + altsHtml +
            '<select name="questoes[' + i + '][gabarito]" class="form-control form-control-sm mt-1 questao-gabarito" style="max-width:170px;">' + gabHtml + '</select>' +
            '</div>' +
            '</div></div>';

        const body = div.querySelector('.questao-body');
        const caret = div.querySelector('.questao-caret');
        const head = div.querySelector('.questao-head');
        const enun = div.querySelector('.questao-enunciado');
        const valor = div.querySelector('.questao-valor-inp');
        const tipo = div.querySelector('.questao-tipo');
        const resumo = div.querySelector('.questao-resumo');
        const tipoLbl = div.querySelector('.questao-tipo-lbl');
        const valorLbl = div.querySelector('.questao-valor');

        function setExp(exp) {
            body.style.display = exp ? '' : 'none';
            caret.style.transform = exp ? '' : 'rotate(-90deg)';
        }
        function syncResumo() {
            resumo.textContent = (enun.value || '').trim().substring(0, 90);
            tipoLbl.textContent = tipo.value === 'discursiva' ? 'Discursiva' : 'Objetiva';
            valorLbl.textContent = valor.value ? valor.value + ' pts' : '';
            valorLbl.style.display = valor.value ? '' : 'none';
        }

        head.addEventListener('click', function (e) {
            if (e.target.closest('.btn-del-questao')) { return; }
            setExp(body.style.display === 'none');
        });
        enun.addEventListener('input', syncResumo);
        valor.addEventListener('input', syncResumo);
        tipo.addEventListener('change', function () {
            div.querySelector('.questao-alts').style.display = this.value === 'objetiva' ? '' : 'none';
            syncResumo();
        });
        div.querySelector('.btn-del-questao').addEventListener('click', function () {
            div.remove();
            renumerar();
        });
        if (q.tipo === 'discursiva') { div.querySelector('.questao-alts').style.display = 'none'; }

        setExp(expandida !== false);
        syncResumo();

        return div;
    }

    function renumerar() {
        lista.querySelectorAll('.questao-item').forEach(function (d, i) {
            d.querySelector('.questao-num').textContent = 'Questão ' + (i + 1);
        });
    }

    function addQuestao(q, expandida) {
        lista.appendChild(templateQuestao(idx++, q, expandida));
        renumerar();
    }

    document.getElementById('btn-add-questao').addEventListener('click', function () { addQuestao(); });
    // Questoes ja salvas (edicao) comecam recolhidas para facilitar o panorama.
    questoesIniciais.forEach(function (q) { addQuestao(q, false); });

    // ---------- Previa das questoes (formato prova) ----------
    function lerQuestoes() {
        return Array.from(document.querySelectorAll('.questao-item')).map(function (d) {
            const alts = {};
            letras.forEach(function (l) {
                const inp = d.querySelector('input[name$="[alternativas][' + l + ']"]');
                alts[l] = inp ? inp.value.trim() : '';
            });
            return {
                tipo: d.querySelector('.questao-tipo').value,
                valor: d.querySelector('.questao-valor-inp').value,
                enunciado: d.querySelector('.questao-enunciado').value.trim(),
                gabarito: d.querySelector('.questao-gabarito').value,
                alternativas: alts,
            };
        });
    }

    function htmlQuestao(q, i) {
        const meta = [
            q.tipo === 'discursiva' ? 'Discursiva' : 'Objetiva',
            q.valor ? q.valor + ' pts' : null,
            q.gabarito ? 'Gabarito: ' + q.gabarito : null,
        ].filter(Boolean).join(' · ');
        let h = '<div style="margin-bottom:14px;">' +
            '<div style="display:flex;"><strong style="margin-right:6px;">' + (i + 1) + '.</strong>' +
            '<div style="flex:1;">' +
            '<div>' + escQ(q.enunciado || '—').replace(/\n/g, '<br>') + '</div>';
        if (q.tipo !== 'discursiva') {
            h += '<ul style="list-style:none;padding-left:18px;margin:6px 0 2px;">';
            letras.forEach(function (l) {
                if (!q.alternativas[l]) { return; }
                const correta = q.gabarito === l;
                h += '<li style="margin:2px 0;' + (correta ? 'font-weight:700;color:#15803d;' : '') + '">' +
                    l + ') ' + escQ(q.alternativas[l]) + (correta ? ' ✓' : '') + '</li>';
            });
            h += '</ul>';
        }
        return h + '<small style="color:#64748b;">' + escQ(meta) + '</small></div></div></div>';
    }

    document.getElementById('btn-preview-questoes').addEventListener('click', function () {
        const questoes = lerQuestoes();
        const box = document.getElementById('previewQuestoes');
        box.innerHTML = questoes.length
            ? questoes.map(htmlQuestao).join('')
            : '<p class="text-muted text-center py-4 mb-0">Nenhuma questão adicionada ainda.</p>';
        $('#modalPreviewQuestoes').modal('show');
    });

    document.getElementById('btnImprimirPreview').addEventListener('click', function () {
        const questoes = lerQuestoes();
        const w = window.open('', '_blank');
        w.document.write(
            '<html><head><title>Questões da tarefa</title>' +
            '<style>body{font-family:Arial,sans-serif;font-size:13px;padding:24px;color:#0f172a;}' +
            'h1{font-size:16px;margin-bottom:16px;}</style></head><body>' +
            '<h1>Questões da tarefa (' + questoes.length + ')</h1>' +
            questoes.map(htmlQuestao).join('') +
            '</body></html>'
        );
        w.document.close();
        w.print();
    });
})();
</script>

@unless(isset($tarefa))
@include('partials.entity-picker')
<script>
(function () {
    const turmasData = @json($turmasPicker ?? []);
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
            { campo: 'ano_letivo_id', campoRotulo: 'ano', label: 'Ano letivo' },
        ],
        onSelect: function (d) {
            input.value = d.id;
            preencherDisplay();
            input.dispatchEvent(new Event('change'));
        },
    });

    document.getElementById('btnBuscarTurma').addEventListener('click', function () {
        EntityPicker.abrir('turma');
    });
    input.addEventListener('change', preencherDisplay);
    preencherDisplay();

    // Turma e obrigatoria: valida e sinaliza o display no submit.
    document.getElementById('tarefaForm').addEventListener('submit', function (e) {
        if (!input.value) {
            e.preventDefault();
            e.stopImmediatePropagation();
            display.classList.add('is-invalid');
            window.__tarefaMostrar(0);
            setTimeout(function () { EntityPicker.abrir('turma'); }, 60);
        }
    }, true);
})();
</script>
@endunless
@stop
