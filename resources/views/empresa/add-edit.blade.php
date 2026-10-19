@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    {{-- <x-page-title :title="$titulo" /> --}}
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-empresa shadow-sm border-0">
            <div class="card-header card-header-empresa border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados da empresa.</p>
                </div>
            </div>

            <div class="card-body p-4">
            @if(!isset($empresa))
                <div class="alert alert-info py-2 rounded-lg">
                    Ao salvar, a nova empresa será criada usando a <strong>empresa-padrão</strong> como modelo de informações.
                </div>
            @endif
            @php $isEdit = isset($empresa); @endphp
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row" enctype="multipart/form-data">
                <div class="col-12 empresa-wizard-top">
                    <div class="empresa-wizard-steps" role="tablist" aria-label="Passos da empresa">
                        <button type="button" class="empresa-wizard-step is-active" data-empresa-step-btn="1">
                            <span class="num">1</span> Dados
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="2">
                            <span class="num">2</span> Contato
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="3">
                            <span class="num">3</span> Domínio
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="4">
                            <span class="num">4</span> Identidade
                        </button>
                    </div>
                </div>

                <div class="col-12" data-empresa-step="1">
                    <div class="empresa-section-title" id="sec-dados">Dados da Empresa</div>
                </div>

                <div class="col-lg-8" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $empresa->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="old(
                                'ativo',
                                isset($empresa->ativo) && $empresa->ativo ? $empresa->ativo : 'S',
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>

                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="slug" name="slug"
                            label="Slug" value="{{ $empresa->slug ?? '' }}" required />
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="cnpj" name="cnpj"
                            label="CNPJ" value="{{ $empresa->cnpj ?? '' }}" />
                    </div>
                </div>

                <div class="col-12" data-empresa-step="1">
                    <div class="empresa-section-title" id="sec-modulos"><i class="fas fa-puzzle-piece mr-1"></i> Módulos da Empresa</div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-select name="licitacoes_ativo" label="Licitações (análise de editais com IA)" :options="$status"
                            :selected="old('licitacoes_ativo', $empresa->licitacoes_ativo ?? 'N')"
                            class="form-control form-control-sm" />
                        <small class="text-muted">Quando ativo, a empresa vê o menu Licitações e pode analisar editais com IA.</small>
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="number" class="form-control form-control-sm" id="ia_limite_tokens" name="ia_limite_tokens"
                            label="Limite mensal de IA (tokens)" value="{{ old('ia_limite_tokens', $empresa->ia_limite_tokens ?? '') }}" min="0" />
                        <small class="text-muted">Cota de tokens de IA por mês (entrada + saída). Vazio = ilimitado.</small>
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <label class="font-weight-bold" style="font-size:.82rem;">Escopo das tags</label>
                        <select name="tags_por_projeto" class="form-control form-control-sm">
                            <option value="0" @selected(! old('tags_por_projeto', $empresa->tags_por_projeto ?? false))>Tags globais — todas as tags disponíveis em qualquer projeto</option>
                            <option value="1" @selected(old('tags_por_projeto', $empresa->tags_por_projeto ?? false))>Tags por projeto — cada projeto tem suas próprias tags</option>
                        </select>
                        <small class="text-muted">No modo por projeto, o Rastreador exibe apenas as tags do projeto selecionado (além das globais).</small>
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <label class="font-weight-bold" style="font-size:.82rem;">Visibilidade dos membros</label>
                        <select name="visao_restrita" class="form-control form-control-sm">
                            <option value="1" @selected(old('visao_restrita', $empresa->visao_restrita ?? true))>Restrita — cada membro vê só sua produtividade, suas equipes e os projetos de que participa</option>
                            <option value="0" @selected(! old('visao_restrita', $empresa->visao_restrita ?? true))>Ampla — membros podem ver os dados de todos os colaboradores e projetos</option>
                        </select>
                        <small class="text-muted">Vale para relatórios, calendário, equipe e projetos. Administradores e líderes mantêm a visão ampliada de gestão.</small>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="2">
                    <div class="empresa-section-title" id="sec-contato">Contato</div>
                </div>

                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="email" class="form-control form-control-sm" id="email" name="email"
                            label="E-mail" value="{{ $empresa->email ?? '' }}" />
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="telefone" name="telefone"
                            label="Telefone" value="{{ $empresa->telefone ?? '' }}" />
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="representante" name="representante"
                            label="Representante" value="{{ old('representante', $empresa->representante ?? '') }}" />
                    </div>
                </div>
                <div class="col-lg-12" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="endereco" name="endereco"
                            label="Endereço" value="{{ old('endereco', $empresa->endereco ?? '') }}" />
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="3">
                    <div class="empresa-section-title" id="sec-dominio">Domínio</div>
                </div>

                <div class="row" data-empresa-step="3">
                    <div class="form-group form-group-empresa col-12">
                        <x-form-input type="text" class="form-control form-control-sm" id="dominio" name="dominio"
                            label="Domínio" value="{{ $empresa->dominio ?? '' }}" />
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="4">
                    <div class="empresa-section-title" id="sec-identidade">Identidade Visual da Empresa</div>
                </div>

                <div class="col-lg-5" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="logo">Logo da empresa</label>
                        <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                        <small class="text-muted d-block mt-1">Formatos: JPG, PNG, WEBP ou SVG. Tamanho máximo: 2 MB.</small>
                    </div>
                </div>
                <div class="col-lg-7" data-empresa-step="4">
                    <div
                        class="empresa-logo-preview-wrap"
                        style="background: {{ old('cor_primaria', $empresa->cor_primaria ?? '#1F2937') }};"
                    >
                        @if(isset($empresa) && $empresa->logo_url)
                            <img src="{{ $empresa->logo_url }}" alt="Logo atual" class="empresa-logo-preview">
                        @else
                            <div class="empresa-logo-placeholder">Sem logo cadastrada</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_primaria">Cor primária <small>Cor de fundo</small></label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_primaria"
                            name="cor_primaria"
                            value="{{ old('cor_primaria', $empresa->cor_primaria ?? '#1F2937') }}"
                        >
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_secundaria">Cor secundária <small>Cor das fontes</small></label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_secundaria"
                            name="cor_secundaria"
                            value="{{ old('cor_secundaria', $empresa->cor_secundaria ?? '#334155') }}"
                        >
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_terciaria">Cor terciária</label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_terciaria"
                            name="cor_terciaria"
                            value="{{ old('cor_terciaria', $empresa->cor_terciaria ?? '#64748B') }}"
                        >
                    </div>
                </div>

                <div class="col-12" data-empresa-step="4">
                    <div class="empresa-theme-preview" id="empresa-theme-preview"></div>
                    <small class="text-muted">Prévia aproximada das cores para fundo/realces no acesso do colaborador.</small>
                </div>

                <div class="col-12 empresa-wizard-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm empresa-wizard-btn" id="empresaWizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Anterior
                    </button>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <button type="button" class="btn btn-primary btn-sm empresa-wizard-btn empresa-wizard-btn--next" id="empresaWizardNext">
                            Próximo <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success btn-sm empresa-wizard-btn empresa-wizard-btn--save btn-save-empresa {{ $isEdit ? '' : 'd-none' }}" id="btnSalvarEmpresa">
                            Salvar alterações
                        </button>
                    </div>
                </div>
            </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-empresa {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-empresa {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        flex-wrap: wrap;
    }

    .card-header-empresa h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .empresa-wizard-top {
        border: 1px solid #e5eaf2;
        border-radius: 14px;
        padding: 12px 12px 10px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        margin-bottom: 14px;
    }

    .empresa-wizard-steps {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .empresa-wizard-steps .div {
        flex: 1 1 20px;
        height: 2px;
        background: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
        border-radius: 999px;
        min-width: 12px;
    }

    .empresa-wizard-step {
        appearance: none;
        border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 30%, #dbe3ef 70%);
        background: #fff;
        border-radius: 999px;
        padding: 8px 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease, color .12s ease;
        white-space: nowrap;
    }

    .empresa-wizard-step .num {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 12%, #ffffff 88%);
        color: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 88%, #111827 12%);
        border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 25%, #dbe3ef 75%);
    }

    .empresa-wizard-step.is-active {
        background: var(--empresa-cor-primaria, #1F2937);
        border-color: var(--empresa-cor-primaria, #1F2937);
        color: var(--empresa-cor-primaria-contraste, #fff);
        box-shadow: 0 10px 22px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 22%, transparent 78%);
    }

    .empresa-wizard-step.is-active .num {
        background: rgba(255,255,255,.18);
        color: var(--empresa-cor-primaria-contraste, #fff);
        border-color: rgba(255,255,255,.24);
    }

    .empresa-wizard-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 0 0;
        margin-top: 14px;
        border-top: 1px dashed color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 28%, #e5edf7 72%);
    }

    .empresa-wizard-footer .btn {
        border-radius: 999px;
        font-weight: 700;
    }

    .empresa-wizard-btn {
        min-height: 38px;
        padding: .58rem 1.05rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    }

    .empresa-wizard-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
    }

    .empresa-wizard-btn--next {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .empresa-wizard-btn--next:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .empresa-wizard-btn--save {
        background: #16a34a;
        border-color: #16a34a;
        color: #fff;
    }

    .empresa-wizard-btn--save:hover {
        background: #15803d;
        border-color: #15803d;
        color: #fff;
    }

    .empresa-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1f334d;
        letter-spacing: .02em;
        padding: 8px 12px;
        border-radius: 10px;
        background: #f6f9ff;
        border: 1px solid #e5eaf2;
        margin-bottom: 12px;
        scroll-margin-top: 90px;
    }

    .form-group-empresa {
        margin-bottom: 1rem;
    }

    .card-empresa .form-control,
    .card-empresa .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-empresa .form-control:focus,
    .card-empresa .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-empresa {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    .empresa-logo-preview-wrap {
        min-height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #d8e1ec;
        border-radius: 10px;
        padding: .75rem;
    }

    .empresa-logo-preview {
        max-height: 90px;
        max-width: 100%;
        object-fit: contain;
    }

    .empresa-logo-placeholder {
        color: #64748b;
        font-size: .88rem;
    }

    .empresa-theme-preview {
        height: 72px;
        border-radius: 12px;
        border: 1px solid #d8e1ec;
        margin-bottom: .45rem;
    }
</style>
@stop

@section('js')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    (function () {
        const isEdit = @json($isEdit);
        const cnpjInput = document.getElementById('cnpj');
        const telefoneInput = document.getElementById('telefone');
        const corPrimaria = document.getElementById('cor_primaria');
        const corSecundaria = document.getElementById('cor_secundaria');
        const corTerciaria = document.getElementById('cor_terciaria');
        const themePreview = document.getElementById('empresa-theme-preview');

        function onlyDigits(value) {
            return String(value || '').replace(/\D+/g, '');
        }

        function formatCnpj(value) {
            const digits = onlyDigits(value).slice(0, 14);
            return digits
                .replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        }

        function formatTelefone(value) {
            const digits = onlyDigits(value).slice(0, 11);
            if (digits.length > 10) {
                return digits.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
            return digits.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        }

        function bindMask(input, formatter) {
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                input.value = formatter(input.value);
            });
        }

        function syncThemePreview() {
            if (!themePreview) {
                return;
            }

            const c1 = corPrimaria?.value || '#1F2937';
            const c2 = corSecundaria?.value || '#334155';
            const c3 = corTerciaria?.value || '#64748B';
            themePreview.style.background = 'linear-gradient(135deg, ' + c1 + ' 0%, ' + c2 + ' 55%, ' + c3 + ' 100%)';
        }

        // Wizard/Steps do cadastro de empresa
        (function initEmpresaWizard() {
            const maxStep = 4;
            const storageKey = 'empresaWizardStep';

            const prevBtn = document.getElementById('empresaWizardPrev');
            const nextBtn = document.getElementById('empresaWizardNext');
            const submitBtn = document.getElementById('btnSalvarEmpresa');

            function getStep() {
                const saved = Number(localStorage.getItem(storageKey) || '1');
                return Number.isFinite(saved) && saved >= 1 && saved <= maxStep ? saved : 1;
            }

            function setStep(step, persist = true) {
                const s = Math.min(maxStep, Math.max(1, Number(step) || 1));

                document.querySelectorAll('[data-empresa-step]').forEach(el => {
                    el.classList.toggle('d-none', Number(el.dataset.empresaStep) !== s);
                });

                document.querySelectorAll('[data-empresa-step-btn]').forEach(btn => {
                    btn.classList.toggle('is-active', Number(btn.dataset.empresaStepBtn) === s);
                });

                if (prevBtn) prevBtn.disabled = s <= 1;
                if (nextBtn) nextBtn.classList.toggle('d-none', s >= maxStep);
                if (submitBtn) {
                    if (isEdit) {
                        submitBtn.classList.remove('d-none');
                    } else {
                        submitBtn.classList.toggle('d-none', s < maxStep);
                    }
                }

                if (persist) localStorage.setItem(storageKey, String(s));

                const top = document.querySelector('.empresa-wizard-top');
                if (top) {
                    top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            function validateStep(step) {
                const containers = Array.from(document.querySelectorAll(`[data-empresa-step="${step}"]`));
                const fields = [];
                containers.forEach(c => fields.push(...Array.from(c.querySelectorAll('input,select,textarea'))));

                for (const field of fields) {
                    if (field.disabled) continue;
                    if (field.type === 'hidden') continue;
                    if (typeof field.checkValidity !== 'function') continue;
                    if (!field.checkValidity()) {
                        if (typeof field.reportValidity === 'function') field.reportValidity();
                        field.focus?.();
                        return false;
                    }
                }

                return true;
            }

            window.empresaWizardSetStep = setStep;

            document.querySelectorAll('[data-empresa-step-btn]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const current = getStep();
                    const target = Number(btn.dataset.empresaStepBtn);
                    if (target > current && !validateStep(current)) return;
                    setStep(target);
                });
            });

            if (prevBtn) {
                prevBtn.addEventListener('click', () => setStep(getStep() - 1));
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const current = getStep();
                    if (!validateStep(current)) return;
                    setStep(current + 1);
                });
            }

            // Abrir step com erro de validacao do backend
            (function openStepWithErrors() {
                const errosCampos = @json(array_keys($errors->messages()));
                if (!Array.isArray(errosCampos) || errosCampos.length === 0) return;

                const mapa = {
                    nome: 1, ativo: 1, slug: 1, cnpj: 1, licitacoes_ativo: 1, ia_limite_tokens: 1,
                    email: 2, telefone: 2, representante: 2, endereco: 2,
                    dominio: 3,
                    logo: 4, cor_primaria: 4, cor_secundaria: 4, cor_terciaria: 4,
                };

                for (const campo of errosCampos) {
                    const step = mapa[campo];
                    if (step) {
                        setStep(step, true);
                        return;
                    }
                }
            })();

            setStep(getStep(), false);
        })();

        [corPrimaria, corSecundaria, corTerciaria].forEach((input) => {
            if (input) {
                input.addEventListener('input', syncThemePreview);
            }
        });

        bindMask(cnpjInput, formatCnpj);
        bindMask(telefoneInput, formatTelefone);

        syncThemePreview();
    })();
</script>
@stop
