@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($aluno) ? 'Editar Aluno' : 'Novo Aluno'" />
@stop

@push('css')
<style>
    .al-steps { display:flex; justify-content:space-between; position:relative; margin:0 4px 1.4rem; }
    .al-steps::before { content:''; position:absolute; top:15px; left:14%; right:14%; height:2px; background:#e5e7eb; z-index:0; }
    .al-step-ind { position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; gap:4px; cursor:pointer; min-width:90px; }
    .al-step-num { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; background:#fff; border:2px solid #d1d5db; color:#9ca3af; }
    .al-step-label { font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; text-align:center; }
    .al-step-ind.done .al-step-num { background:#10b981; border-color:#10b981; color:#fff; }
    .al-step-ind.done .al-step-label { color:#059669; }
    .al-step-ind.active .al-step-num { background:#2563eb; border-color:#2563eb; color:#fff; box-shadow:0 0 0 4px rgba(37,99,235,.15); }
    .al-step-ind.active .al-step-label { color:#2563eb; }
    .al-pane { display:none; }
    .al-pane.active { display:block; }
    .al-pane-title { font-size:.82rem; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.9rem; }
    .al-foot { display:flex; justify-content:space-between; align-items:center; margin-top:1.1rem; padding-top:1rem; border-top:1px solid #f0f3f8; }
    .al-foot__nav { display:flex; gap:.5rem; }
</style>
@endpush

@section('content')
@php($p = $aluno->pessoa ?? null)
<div class="card">
    <div class="card-body">
        <div class="al-steps">
            <div class="al-step-ind" data-goto="0"><span class="al-step-num">1</span><span class="al-step-label">Dados pessoais</span></div>
            <div class="al-step-ind" data-goto="1"><span class="al-step-num">2</span><span class="al-step-label">Contato e endereço</span></div>
            <div class="al-step-ind" data-goto="2"><span class="al-step-num">3</span><span class="al-step-label">Escolares e saúde</span></div>
        </div>

        <form method="POST" action="{{ isset($aluno) ? route('aluno.update', $aluno->id) : route('aluno.store') }}" id="alunoForm" novalidate>
            @csrf
            @if(isset($aluno))
                @method('PUT')
            @endif

            {{-- Etapa 1: identificacao --}}
            <div class="al-pane active" data-step="0">
                <div class="al-pane-title"><i class="fas fa-user mr-1"></i> Identificação</div>
                <div class="row">
                    <div class="col-md-6">
                        <x-form-input name="nome" label="Nome completo" :value="old('nome', $p->nome ?? '')" required maxlength="255" />
                    </div>
                    <div class="col-md-6">
                        <x-form-input name="nome_social" label="Nome social" :value="old('nome_social', $p->nome_social ?? '')" maxlength="255" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <x-form-input name="cpf" label="CPF" :value="old('cpf', $p->cpf ?? '')" maxlength="20" placeholder="000.000.000-00" />
                    </div>
                    <div class="col-md-3">
                        <x-form-input name="rg" label="RG" :value="old('rg', $p->rg ?? '')" maxlength="20" />
                    </div>
                    <div class="col-md-3">
                        <x-form-input name="data_nascimento" type="date" label="Data de nascimento" :value="old('data_nascimento', optional($p->data_nascimento ?? null)->format('Y-m-d'))" />
                    </div>
                    <div class="col-md-3">
                        <x-form-select name="sexo" label="Sexo" :options="$sexos ?? []" :selected="old('sexo', $p->sexo ?? '')" placeholder="Selecionar" />
                    </div>
                </div>
            </div>

            {{-- Etapa 2: contato e endereco --}}
            <div class="al-pane" data-step="1">
                <div class="al-pane-title"><i class="fas fa-address-book mr-1"></i> Contato e endereço</div>
                <div class="row">
                    <div class="col-md-4">
                        <x-form-input name="email" type="email" label="E-mail" :value="old('email', $p->email ?? '')" maxlength="255" />
                    </div>
                    <div class="col-md-4">
                        <x-form-input name="telefone" label="Telefone" :value="old('telefone', $p->telefone ?? '')" maxlength="30" placeholder="(00) 0000-0000" />
                    </div>
                    <div class="col-md-4">
                        <x-form-input name="celular" label="Celular" :value="old('celular', $p->celular ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <x-form-input name="cep" label="CEP" :value="old('cep', $p->cep ?? '')" maxlength="10" placeholder="00000-000" />
                    </div>
                    <div class="col-md-5">
                        <x-form-input name="endereco" label="Endereço" :value="old('endereco', $p->endereco ?? '')" maxlength="500" />
                    </div>
                    <div class="col-md-3">
                        <x-form-input name="cidade" label="Cidade" :value="old('cidade', $p->cidade ?? '')" maxlength="120" />
                    </div>
                    <div class="col-md-1">
                        <x-form-input name="estado" label="UF" :value="old('estado', $p->estado ?? '')" maxlength="2" placeholder="SP" />
                    </div>
                </div>
            </div>

            {{-- Etapa 3: dados escolares e de saude --}}
            <div class="al-pane" data-step="2">
                <div class="al-pane-title"><i class="fas fa-graduation-cap mr-1"></i> Dados escolares</div>
                <div class="row">
                    <div class="col-md-4">
                        <x-form-input name="registro_academico" label="RA (Registro Acadêmico)" :value="old('registro_academico', $aluno->registro_academico ?? '')" maxlength="30" />
                    </div>
                    <div class="col-md-4">
                        <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $aluno->escola_id ?? '')" placeholder="Selecionar" />
                    </div>
                    <div class="col-md-4">
                        <x-form-input name="data_ingresso" type="date" label="Data de ingresso" :value="old('data_ingresso', optional($aluno->data_ingresso ?? null)->format('Y-m-d'))" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <x-form-select name="turno_preferencial" label="Turno preferencial" :options="$turnos ?? []" :selected="old('turno_preferencial', $aluno->turno_preferencial ?? '')" placeholder="Selecionar" />
                    </div>
                    <div class="col-md-4">
                        <x-form-select name="ativo" label="Status" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $aluno->ativo ?? 'S')" />
                    </div>
                    <div class="col-md-4">
                        <x-form-select name="user_id" label="Conta de acesso (portal)" :options="$usuariosPortal ?? []" :selected="old('user_id', $aluno->user_id ?? '')" placeholder="Sem acesso ao portal" />
                    </div>
                </div>

                <div class="al-pane-title mt-3"><i class="fas fa-heartbeat mr-1"></i> Saúde e observações</div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Necessidades especiais / acessibilidade</label>
                        <textarea name="necessidades_especiais" rows="3" class="form-control">{{ old('necessidades_especiais', $aluno->necessidades_especiais ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Informações de saúde</label>
                        <textarea name="informacoes_saude" rows="3" class="form-control">{{ old('informacoes_saude', $aluno->informacoes_saude ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Contatos de emergência</label>
                        <textarea name="contatos_emergencia" rows="3" class="form-control" placeholder="Nome, parentesco e telefone">{{ old('contatos_emergencia', $aluno->contatos_emergencia ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Restrições de acesso</label>
                        <textarea name="restricoes_acesso" rows="3" class="form-control">{{ old('restricoes_acesso', $aluno->restricoes_acesso ?? '') }}</textarea>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" rows="2" class="form-control">{{ old('observacoes', $aluno->observacoes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="al-foot">
                <a href="{{ route('aluno.index') }}" class="btn btn-secondary">Cancelar</a>
                <div class="al-foot__nav">
                    <button type="button" class="btn btn-outline-secondary" id="btnVoltar" style="display:none;"><i class="fa fa-arrow-left mr-1"></i> Voltar</button>
                    <button type="button" class="btn btn-primary" id="btnProximo">Próximo <i class="fa fa-arrow-right ml-1"></i></button>
                    <button type="submit" class="btn btn-primary" id="btnSalvar" style="display:none;"><i class="fa fa-check mr-1"></i> Salvar aluno</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const panes = Array.from(document.querySelectorAll('.al-pane'));
    const inds = Array.from(document.querySelectorAll('.al-step-ind'));
    const btnVoltar = document.getElementById('btnVoltar');
    const btnProximo = document.getElementById('btnProximo');
    const btnSalvar = document.getElementById('btnSalvar');
    const form = document.getElementById('alunoForm');
    let atual = 0;

    function paint() {
        inds.forEach(function (ind, i) {
            const num = ind.querySelector('.al-step-num');
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

    // ---------- Mascaras ----------
    function mascara(el, fn) {
        el.addEventListener('input', function () {
            const v = el.value.replace(/\D/g, '');
            el.value = fn(v);
        });
    }
    function maskCep(v) { return v.slice(0, 8).replace(/^(\d{5})(\d)/, '$1-$2'); }
    function maskCel(v) {
        v = v.slice(0, 11);
        if (v.length <= 2) { return v; }
        return v.length <= 6
            ? '(' + v.slice(0, 2) + ') ' + v.slice(2)
            : '(' + v.slice(0, 2) + ') ' + v.slice(2, 7) + '-' + v.slice(7);
    }
    function maskTel(v) {
        v = v.slice(0, 10);
        if (v.length <= 2) { return v; }
        return v.length <= 6
            ? '(' + v.slice(0, 2) + ') ' + v.slice(2)
            : '(' + v.slice(0, 2) + ') ' + v.slice(2, 6) + '-' + v.slice(6);
    }
    function maskCpf(v) {
        v = v.slice(0, 11);
        return v.replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }

    const cepEl = document.querySelector('[name="cep"]');
    const celEl = document.querySelector('[name="celular"]');
    const telEl = document.querySelector('[name="telefone"]');
    const cpfEl = document.querySelector('[name="cpf"]');
    if (cepEl) { mascara(cepEl, maskCep); }
    if (celEl) { mascara(celEl, maskCel); }
    if (telEl) { mascara(telEl, maskTel); }
    if (cpfEl) { mascara(cpfEl, maskCpf); }

    // ---------- ViaCEP: preenche cidade/UF/endereco (editaveis) ----------
    function setCampo(nome, valor) {
        const el = document.querySelector('[name="' + nome + '"]');
        if (el && !el.value) { el.value = valor; }
    }
    if (cepEl) {
        cepEl.addEventListener('blur', function () {
            const cep = cepEl.value.replace(/\D/g, '');
            if (cep.length !== 8) { return; }
            cepEl.style.backgroundImage = 'linear-gradient(to right,#e8f0fe,transparent)';
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    cepEl.style.backgroundImage = '';
                    if (d.erro) { return; }
                    setCampo('endereco', [d.logradouro, d.bairro].filter(Boolean).join(' — '));
                    setCampo('cidade', d.localidade || '');
                    setCampo('estado', d.uf || '');
                })
                .catch(function () { cepEl.style.backgroundImage = ''; });
        });
    }
})();
</script>
@stop
