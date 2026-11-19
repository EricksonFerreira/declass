@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Turmas em lote" subtitle="Crie várias turmas de uma vez na mesma estrutura" />
@stop

@push('css')
<style>
    .tf-card {
        border: 1px solid #e5e7f0;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(15,23,42,.06);
    }
    .tf-sec {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
        margin: 1.1rem 0 .7rem;
        padding-bottom: .4rem;
        border-bottom: 1px solid #f1f3f9;
    }
    .tf-sec:first-child { margin-top: 0; }
    .tf-sec i { color: #007bff; }
    .tl-row {
        display: grid;
        grid-template-columns: 2fr 2fr 42px;
        gap: 8px;
        align-items: center;
        padding: 7px 0;
        border-bottom: 1px solid #f1f3f9;
    }
    .tl-row:last-of-type { border-bottom: 0; }
    .tl-row--head {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        border-bottom: 1px solid #e5e7f0;
        padding: 0 0 6px;
    }
    .tl-gen {
        display: grid;
        grid-template-columns: 2fr 2fr auto;
        gap: 8px;
        align-items: end;
        background: #f8fafc;
        border: 1px dashed #d7dce8;
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 12px;
    }
    .tf-dica {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f9;
        align-items: flex-start;
    }
    .tf-dica:last-child { border-bottom: 0; padding-bottom: 0; }
    .tf-dica i {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        flex-shrink: 0;
        color: #fff;
    }
    .tf-dica b { font-size: .8rem; color: #0f172a; display: block; }
    .tf-dica span { font-size: .74rem; color: #94a3b8; }
    #tl-contador { font-size: .8rem; color: #6b7280; }
    @media (max-width: 767.98px) {
        .tl-row { grid-template-columns: 1fr 1fr 38px; }
    }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('turma.store_lote') }}" id="form-lote">
    @csrf
    <input type="hidden" name="ativo" value="S">

    <div class="row">
        <div class="col-lg-8">
            <div class="card tf-card">
                <div class="card-body">
                    <div class="tf-sec"><i class="fa fa-graduation-cap mr-1"></i> Estrutura comum</div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id')" placeholder="Selecionar" required />
                        </div>
                        <div class="col-md-6">
                            <x-form-select name="ano_letivo_id" label="Ano letivo" :options="$anosLetivos ?? []" :selected="old('ano_letivo_id')" placeholder="Selecionar" required />
                        </div>
                        <div class="col-md-6">
                            <x-form-select name="etapa_id" label="Etapa" :options="$etapas ?? []" :selected="old('etapa_id')" placeholder="Selecionar" required />
                        </div>
                        <div class="col-md-6">
                            <x-form-select name="turno" label="Turno" :options="$turnos ?? []" :selected="old('turno')" placeholder="Selecionar" hint="Filtra as grades de horário das turmas." />
                        </div>
                    </div>

                    <div class="tf-sec"><i class="fa fa-door-open mr-1"></i> Operação (aplicada a todas)</div>
                    <div class="row">
                        <div class="col-md-4">
                            <x-form-input name="sala" label="Sala" :value="old('sala')" maxlength="40" placeholder="101" />
                        </div>
                        <div class="col-md-4">
                            <x-form-input name="capacidade_maxima" type="number" label="Capacidade" :value="old('capacidade_maxima')" min="1" max="500" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grade para todas</label>
                            <select id="grade-todas" class="form-control">
                                <option value="">— Escolher por turma —</option>
                            </select>
                            <small class="text-muted">Preenche a grade de todas as linhas.</small>
                        </div>
                    </div>

                    <div class="tf-sec d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-list mr-1"></i> Turmas</span>
                        <span id="tl-contador"></span>
                    </div>

                    <div class="tl-gen">
                        <div>
                            <label class="form-label mb-1">Nome base</label>
                            <input type="text" id="gen-base" class="form-control form-control-sm" placeholder="Ex.: 1º Ano">
                        </div>
                        <div>
                            <label class="form-label mb-1">Sufixos</label>
                            <input type="text" id="gen-sufixos" class="form-control form-control-sm" value="A, B, C, D">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="gen-add"><i class="fa fa-magic mr-1"></i> Gerar linhas</button>
                    </div>

                    <div class="tl-row tl-row--head">
                        <span>Nome da turma</span>
                        <span>Grade de horário</span>
                        <span></span>
                    </div>
                    <div id="tl-rows"></div>

                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="tl-add">
                        <i class="fa fa-plus mr-1"></i> Adicionar linha
                    </button>
                </div>
                <div class="card-footer d-flex justify-content-end" style="border-radius:0 0 16px 16px;">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-check mr-1"></i> Criar turmas</button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card tf-card">
                <div class="card-body">
                    <div class="tf-sec"><i class="fa fa-lightbulb mr-1"></i> Como funciona</div>
                    <div class="tf-dica">
                        <i class="fa fa-magic" style="background:#8b5cf6;"></i>
                        <div><b>Gerar linhas</b><span>Digite o nome base e os sufixos — cada sufixo vira uma turma (ex.: "1º Ano A").</span></div>
                    </div>
                    <div class="tf-dica">
                        <i class="fa fa-clock" style="background:#007bff;"></i>
                        <div><b>Grade por turma ou igual</b><span>Escolha a grade linha a linha, ou use "Grade para todas" para aplicar a mesma.</span></div>
                    </div>
                    <div class="tf-dica">
                        <i class="fa fa-copy" style="background:#f59e0b;"></i>
                        <div><b>Sem duplicar</b><span>Turmas cujo nome já existe no ano letivo são puladas automaticamente.</span></div>
                    </div>
                    <div class="tf-dica">
                        <i class="fa fa-layer-group" style="background:#10b981;"></i>
                        <div><b>Várias etapas?</b><span>Use o gerador "Geração em massa" na tela do ano letivo (etapas × sufixos × turnos).</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('js')
<script>
(function () {
    var horarios = @json($horariosJson ?? []);
    var escolaSel = document.querySelector('[name="escola_id"]');
    var turnoSel = document.querySelector('[name="turno"]');
    var gradeTodas = document.getElementById('grade-todas');
    var rows = document.getElementById('tl-rows');
    var contador = document.getElementById('tl-contador');
    var seq = 0;

    function gradesDisponiveis() {
        var escolaId = parseInt(escolaSel.value, 10);
        var turno = turnoSel.value;
        return horarios.filter(function (h) {
            return h.escola_id === escolaId && (!turno || h.turno === turno);
        });
    }

    function optionsHtml(selecionado) {
        var html = '<option value="">Padrão do turno (matriz legada)</option>';
        gradesDisponiveis().forEach(function (h) {
            html += '<option value="' + h.id + '"' + (String(h.id) === String(selecionado) ? ' selected' : '') + '>' + h.label + '</option>';
        });
        return html;
    }

    function atualizarGradeTodas() {
        var atual = gradeTodas.value;
        gradeTodas.innerHTML = '<option value="">— Escolher por turma —</option>' + optionsHtml(atual).replace(/<option value="">.*?<\/option>/, '');
        gradeTodas.value = atual;
    }

    function novaLinha(nome, horarioId) {
        var i = seq++;
        var div = document.createElement('div');
        div.className = 'tl-row';
        div.innerHTML =
            '<input type="text" name="turmas[' + i + '][nome]" class="form-control form-control-sm" maxlength="120" required placeholder="Ex.: 1º Ano A" value="' + (nome || '').replace(/"/g, '&quot;') + '">' +
            '<select name="turmas[' + i + '][horario_id]" class="form-control form-control-sm tl-grade">' + optionsHtml(horarioId || '') + '</select>' +
            '<button type="button" class="btn btn-xs btn-outline-danger tl-del" title="Remover"><i class="fa fa-times"></i></button>';
        div.querySelector('.tl-del').addEventListener('click', function () {
            div.remove();
            atualizarContador();
        });
        rows.appendChild(div);
        atualizarContador();
        return div;
    }

    function atualizarContador() {
        var n = rows.querySelectorAll('.tl-row').length;
        contador.textContent = n + (n === 1 ? ' turma' : ' turmas');
    }

    function repreencherGrades() {
        rows.querySelectorAll('.tl-grade').forEach(function (sel) {
            var atual = sel.value;
            sel.innerHTML = optionsHtml(atual);
            sel.value = atual;
        });
        atualizarGradeTodas();
    }

    escolaSel.addEventListener('change', repreencherGrades);
    turnoSel.addEventListener('change', repreencherGrades);

    gradeTodas.addEventListener('change', function () {
        var v = gradeTodas.value;
        if (v === '') return;
        rows.querySelectorAll('.tl-grade').forEach(function (sel) { sel.value = v; });
    });

    document.getElementById('tl-add').addEventListener('click', function () { novaLinha('', ''); });

    document.getElementById('gen-add').addEventListener('click', function () {
        var base = document.getElementById('gen-base').value.trim();
        var sufixos = document.getElementById('gen-sufixos').value
            .split(',').map(function (s) { return s.trim(); }).filter(Boolean);
        if (!base || sufixos.length === 0) {
            alert('Informe o nome base e os sufixos.');
            return;
        }
        var grade = gradeTodas.value;
        sufixos.forEach(function (s) { novaLinha(base + ' ' + s, grade); });
    });

    // linhas iniciais: restaura old() em erro de validacao ou 3 vazias
    var antigas = @json(old('turmas', []));
    if (antigas && antigas.length) {
        antigas.forEach(function (t) { novaLinha(t.nome || '', t.horario_id || ''); });
    } else {
        novaLinha('', ''); novaLinha('', ''); novaLinha('', '');
    }
    atualizarGradeTodas();
})();
</script>
@stop
