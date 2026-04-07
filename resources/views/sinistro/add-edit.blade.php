@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card shadow-sm border-0">
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-car-crash text-danger mr-2"></i>{{ $titulo }}
                    </h4>
                    <p class="mb-0 text-muted small">Preencha os dados do sinistro, seguro e evidências.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formSinistro" method="POST" action="{{ $rota }}" enctype="multipart/form-data">
                    @csrf
                    @if($metodo === 'PUT') @method('PUT') @endif

                    <div class="sn-page">
                        <div class="sn-wizard-top">
                            <div class="sn-wizard-steps" role="tablist">
                                <button type="button" class="sn-wizard-step is-active" data-sn-step-btn="1">
                                    <span class="num">1</span><span class="txt">Ocorrido</span>
                                </button>
                                <div class="div"></div>
                                <button type="button" class="sn-wizard-step" data-sn-step-btn="2">
                                    <span class="num">2</span><span class="txt">Terceiros</span>
                                </button>
                                <div class="div"></div>
                                <button type="button" class="sn-wizard-step" data-sn-step-btn="3">
                                    <span class="num">3</span><span class="txt">Seguro</span>
                                </button>
                                <div class="div"></div>
                                <button type="button" class="sn-wizard-step" data-sn-step-btn="4">
                                    <span class="num">4</span><span class="txt">Evidências</span>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Avance pelos passos e finalize em "Evidências" para salvar.
                            </small>
                        </div>

                        {{-- STEP 1: Ocorrido --}}
                        <div data-sn-step="1">
                            <div class="sn-section-title">Vínculo com Veículo e Reserva</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Reserva</label>
                                    <input type="number" name="reserva_id" class="form-control form-control-sm"
                                           value="{{ old('reserva_id', $sinistro->reserva_id ?? $reserva?->id ?? '') }}"
                                           placeholder="ID da reserva">
                                </div>
                                <div class="col-lg-8 form-group">
                                    <label class="form-label-sm">Veículo</label>
                                    <input type="hidden" name="veiculo_id" id="veiculo_id_input"
                                           value="{{ old('veiculo_id', $sinistro->veiculo_id ?? $veiculo?->id ?? '') }}">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="veiculo_display" class="form-control form-control-sm"
                                               readonly placeholder="Clique na lupa para buscar um veículo..."
                                               value="{{ old('veiculo_id') ? '' : ($veiculo ? (($veiculo->nome ?? '') . ($veiculo->placa_licenca ? ' — ' . $veiculo->placa_licenca : '')) : '') }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculo" title="Buscar veículo">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger d-none" id="btnLimparVeiculo" title="Limpar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sn-section-title mt-3">Dados do Ocorrido</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Tipo de Sinistro <span class="text-danger">*</span></label>
                                    <select name="tipo_sinistro" class="form-control form-control-sm" required>
                                        @foreach($tipos as $key => $label)
                                            <option value="{{ $key }}" {{ old('tipo_sinistro', $sinistro->tipo_sinistro ?? 'colisao') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Data e Hora do Ocorrido <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="data_hora_ocorrido" class="form-control form-control-sm" required
                                           value="{{ old('data_hora_ocorrido', $sinistro?->data_hora_ocorrido?->format('Y-m-d\TH:i') ?? '') }}">
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Placa no Momento</label>
                                    <input type="text" name="placa_momento" class="form-control form-control-sm"
                                           value="{{ old('placa_momento', $sinistro->placa_momento ?? $veiculo?->placa_licenca ?? '') }}">
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Local do Ocorrido</label>
                                    <input type="text" name="local_ocorrido" class="form-control form-control-sm"
                                           value="{{ old('local_ocorrido', $sinistro->local_ocorrido ?? '') }}"
                                           placeholder="Endereço, cidade, rodovia...">
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Descrição do Ocorrido</label>
                                    <textarea name="descricao" class="form-control form-control-sm" rows="4"
                                              placeholder="Descreva como ocorreu o sinistro...">{{ old('descricao', $sinistro->descricao ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="sn-section-title mt-3">Boletim de Ocorrência</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Número do BO</label>
                                    <input type="text" name="numero_bo" class="form-control form-control-sm"
                                           value="{{ old('numero_bo', $sinistro->numero_bo ?? '') }}">
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Órgão (Delegacia, PRF, PM...)</label>
                                    <input type="text" name="orgao_bo" class="form-control form-control-sm"
                                           value="{{ old('orgao_bo', $sinistro->orgao_bo ?? '') }}">
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control form-control-sm" required>
                                        @foreach($statusOpts as $key => $label)
                                            <option value="{{ $key }}" {{ old('status', $sinistro->status ?? 'aberto') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 2: Terceiros --}}
                        <div data-sn-step="2" class="d-none">
                            <div class="sn-section-title">Terceiros e Vítimas</div>
                            <div class="form-row">
                                <div class="col-lg-6 form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="terceiros_envolvidos" value="0">
                                        <input type="checkbox" class="custom-control-input" id="terceiros_envolvidos"
                                               name="terceiros_envolvidos" value="1"
                                               {{ old('terceiros_envolvidos', $sinistro->terceiros_envolvidos ?? false) ? 'checked' : '' }}
                                               onchange="toggleTerceiros(this.checked)">
                                        <label class="custom-control-label" for="terceiros_envolvidos">
                                            Houve terceiros envolvidos?
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="vitimas" value="0">
                                        <input type="checkbox" class="custom-control-input" id="vitimas"
                                               name="vitimas" value="1"
                                               {{ old('vitimas', $sinistro->vitimas ?? false) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="vitimas">
                                            Houve vítimas?
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div id="dadosTerceiros" class="{{ old('terceiros_envolvidos', $sinistro->terceiros_envolvidos ?? false) ? '' : 'd-none' }}">
                                <div class="sn-section-title mt-3">Dados do Terceiro</div>
                                <div class="form-row">
                                    <div class="col-lg-5 form-group">
                                        <label class="form-label-sm">Nome do Terceiro</label>
                                        <input type="text" name="terceiro_nome" class="form-control form-control-sm"
                                               value="{{ old('terceiro_nome', $sinistro->terceiro_nome ?? '') }}">
                                    </div>
                                    <div class="col-lg-3 form-group">
                                        <label class="form-label-sm">Telefone</label>
                                        <input type="text" name="terceiro_telefone" class="form-control form-control-sm"
                                               value="{{ old('terceiro_telefone', $sinistro->terceiro_telefone ?? '') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label class="form-label-sm">Placa do Veículo do Terceiro</label>
                                        <input type="text" name="terceiro_placa" class="form-control form-control-sm"
                                               value="{{ old('terceiro_placa', $sinistro->terceiro_placa ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="sn-section-title mt-4">Observações</div>
                            <div class="form-row">
                                <div class="col-12 form-group">
                                    <textarea name="observacoes" class="form-control form-control-sm" rows="4"
                                              placeholder="Observações adicionais sobre o sinistro...">{{ old('observacoes', $sinistro->observacoes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 3: Seguro --}}
                        <div data-sn-step="3" class="d-none">
                            <div class="sn-section-title">Dados da Seguradora</div>
                            <div class="form-row">
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Seguradora</label>
                                    <input type="text" name="seguradora" class="form-control form-control-sm"
                                           value="{{ old('seguradora', $sinistro->seguradora ?? '') }}"
                                           placeholder="Nome da seguradora">
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Número da Apólice</label>
                                    <input type="text" name="numero_apolice" class="form-control form-control-sm"
                                           value="{{ old('numero_apolice', $sinistro->numero_apolice ?? '') }}">
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Número do Sinistro na Seguradora</label>
                                    <input type="text" name="numero_sinistro_seguradora" class="form-control form-control-sm"
                                           value="{{ old('numero_sinistro_seguradora', $sinistro->numero_sinistro_seguradora ?? '') }}">
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Franquia</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="franquia" class="form-control form-control-sm"
                                               value="{{ old('franquia', $sinistro->franquia ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="sn-section-title mt-3">Valores</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Prejuízo Estimado</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_prejuizo_estimado" class="form-control form-control-sm"
                                               value="{{ old('valor_prejuizo_estimado', $sinistro->valor_prejuizo_estimado ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Valor Aprovado pela Seguradora</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_aprovado_seguradora" class="form-control form-control-sm"
                                               value="{{ old('valor_aprovado_seguradora', $sinistro->valor_aprovado_seguradora ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Responsabilidade do Cliente</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_responsabilidade_cliente" class="form-control form-control-sm"
                                               value="{{ old('valor_responsabilidade_cliente', $sinistro->valor_responsabilidade_cliente ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 4: Evidências --}}
                        <div data-sn-step="4" class="d-none">
                            <div class="sn-section-title">Documentos e Fotos</div>
                            <div class="form-row">
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Boletim de Ocorrência (PDF ou imagem)</label>
                                    <input type="file" name="anexo_bo" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    @if(!empty($sinistro?->anexo_bo_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $sinistro->anexo_bo_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver arquivo
                                            </a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Laudo / Perícia (PDF ou imagem)</label>
                                    <input type="file" name="anexo_laudo" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    @if(!empty($sinistro?->anexo_laudo_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $sinistro->anexo_laudo_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver arquivo
                                            </a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Foto 1</label>
                                    <input type="file" name="anexo_foto1" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png">
                                    @if(!empty($sinistro?->anexo_foto1_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $sinistro->anexo_foto1_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver foto
                                            </a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Foto 2</label>
                                    <input type="file" name="anexo_foto2" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png">
                                    @if(!empty($sinistro?->anexo_foto2_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $sinistro->anexo_foto2_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver foto
                                            </a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Foto 3</label>
                                    <input type="file" name="anexo_foto3" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png">
                                    @if(!empty($sinistro?->anexo_foto3_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $sinistro->anexo_foto3_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver foto
                                            </a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Footer do wizard --}}
                        <div class="sn-wizard-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="snWizardPrev" disabled>
                                <i class="fas fa-arrow-left mr-1"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="snWizardNext">
                                Continuar <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                            <button type="submit" id="btnSalvarSinistro" class="btn btn-danger btn-sm d-none">
                                <i class="fas fa-save mr-1"></i>Salvar sinistro
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('sinistro.index') }}" class="btn btn-link text-muted">
                                <i class="fas fa-arrow-left mr-1"></i>Voltar para lista
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

<x-veiculo.busca-avancada-modal
    prefix="sinistro"
    title="Buscar veículo"
    trigger-id="btnBuscarVeiculo"
    hidden-input-id="veiculo_id_input"
    display-input-id="veiculo_display"
    clear-btn-id="btnLimparVeiculo"
    auto-fill-placa-selector="[name=placa_momento]"
    :filtro-marcas="$filtroMarcas"
    :filtro-grupos="$filtroGrupos"
    :filtro-tipos="$filtroTipos"
    :filtro-status="$filtroStatus"
/>

@section('css')
<style>
.sn-section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #888;
    border-bottom: 1px solid #eee;
    padding-bottom: .3rem;
    margin-bottom: 1rem;
}
.form-label-sm {
    font-size: .82rem;
    font-weight: 600;
    margin-bottom: .25rem;
    display: block;
    color: #444;
}
.sn-page {
    --sn-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 35%, #dbe3ef 65%);
}
.sn-wizard-top {
    border: 1px solid var(--sn-border);
    border-radius: 14px;
    padding: 12px 12px 10px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    margin-bottom: 14px;
}
.sn-wizard-steps {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.sn-wizard-steps .div {
    flex: 1 1 20px;
    height: 2px;
    background: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
    border-radius: 999px;
    min-width: 12px;
}
.sn-wizard-step {
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
}
.sn-wizard-step .num {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 12%, #ffffff 88%);
    color: color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 88%, #111827 12%);
    border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 25%, #dbe3ef 75%);
}
.sn-wizard-step.is-active {
    background: var(--empresa-cor-primaria, #dc3545);
    border-color: var(--empresa-cor-primaria, #dc3545);
    color: var(--empresa-cor-primaria-contraste, #fff);
    box-shadow: 0 10px 22px color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 22%, transparent 78%);
}
.sn-wizard-step.is-active .num {
    background: rgba(255,255,255,.18);
    color: var(--empresa-cor-primaria-contraste, #fff);
    border-color: rgba(255,255,255,.24);
}
.sn-wizard-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 0 0;
    margin-top: 14px;
    border-top: 1px dashed color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 28%, #e5edf7 72%);
}
.sn-wizard-footer .btn {
    border-radius: 999px;
    font-weight: 700;
}
</style>
@stop

@push('js')
<script>
    function toggleTerceiros(checked) {
        document.getElementById('dadosTerceiros').classList.toggle('d-none', !checked);
    }

    // Wizard
    (function initSnWizard() {
        const maxStep = 4;
        const storageKey = 'snWizardStep';

        const prevBtn   = document.getElementById('snWizardPrev');
        const nextBtn   = document.getElementById('snWizardNext');
        const submitBtn = document.getElementById('btnSalvarSinistro');

        function getStep() {
            const saved = Number(localStorage.getItem(storageKey) || '1');
            return Number.isFinite(saved) && saved >= 1 && saved <= maxStep ? saved : 1;
        }

        function setStep(step, persist = true) {
            const s = Math.min(maxStep, Math.max(1, Number(step) || 1));

            document.querySelectorAll('[data-sn-step]').forEach(el => {
                el.classList.toggle('d-none', Number(el.dataset.snStep) !== s);
            });
            document.querySelectorAll('[data-sn-step-btn]').forEach(btn => {
                btn.classList.toggle('is-active', Number(btn.dataset.snStepBtn) === s);
            });

            if (prevBtn) prevBtn.disabled = s <= 1;
            if (nextBtn) nextBtn.classList.toggle('d-none', s >= maxStep);
            if (submitBtn) submitBtn.classList.toggle('d-none', s < maxStep);

            if (persist) localStorage.setItem(storageKey, String(s));
        }

        function validateStep(step) {
            const containers = Array.from(document.querySelectorAll(`[data-sn-step="${step}"]`));
            const fields = [];
            containers.forEach(c => fields.push(...c.querySelectorAll('input, select, textarea')));
            const uniqueFields = Array.from(new Set(fields)).filter(f => f && !f.disabled && f.type !== 'hidden' && f.willValidate);
            const invalid = uniqueFields.find(f => !f.checkValidity());
            if (invalid) { invalid.reportValidity(); return false; }
            return true;
        }

        prevBtn?.addEventListener('click', () => setStep(getStep() - 1));
        nextBtn?.addEventListener('click', () => {
            const current = getStep();
            if (!validateStep(current)) return;
            setStep(current + 1);
        });
        document.querySelectorAll('[data-sn-step-btn]').forEach(btn => {
            btn.addEventListener('click', () => {
                const target  = Number(btn.dataset.snStepBtn);
                const current = getStep();
                if (target > current && !validateStep(current)) return;
                setStep(target);
            });
        });

        const form = document.getElementById('formSinistro');
        form?.addEventListener('submit', (event) => {
            const current = getStep();
            if (current >= maxStep) return;
            event.preventDefault();
            if (!validateStep(current)) return;
            setStep(current + 1);
        });

        window.snWizardSetStep = (step, persist = true) => setStep(step, persist);
        setStep(getStep(), false);
    })();

    // Abrir step com erro
    (function () {
        const errosCampos = @json(array_keys($errors->messages()));
        const mapaSteps = {
            'reserva_id': 1, 'veiculo_id': 1,
            'tipo_sinistro': 1, 'data_hora_ocorrido': 1,
            'local_ocorrido': 1, 'descricao': 1,
            'placa_momento': 1, 'numero_bo': 1,
            'orgao_bo': 1, 'status': 1,
            'terceiros_envolvidos': 2, 'terceiro_nome': 2,
            'terceiro_telefone': 2, 'terceiro_placa': 2,
            'vitimas': 2, 'observacoes': 2,
            'seguradora': 3, 'numero_apolice': 3,
            'numero_sinistro_seguradora': 3, 'franquia': 3,
            'valor_prejuizo_estimado': 3, 'valor_aprovado_seguradora': 3,
            'valor_responsabilidade_cliente': 3,
            'anexo_bo': 4, 'anexo_foto1': 4, 'anexo_foto2': 4,
            'anexo_foto3': 4, 'anexo_laudo': 4,
        };
        for (const campo of errosCampos) {
            const step = mapaSteps[campo];
            if (step) { window.snWizardSetStep?.(step, true); break; }
        }
    })();
</script>
@endpush
