@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

    <div class="col-12 px-0">
        <div class="ci-card">

            <div class="ci-card-header">
                <div class="ci-header-left">
                    <div class="ci-header-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <div>
                        <div class="ci-header-eyebrow">Check-out / Devolução</div>
                        <h4 class="ci-header-title">Reserva #{{ $reserva->id ?? '' }}</h4>
                        <p class="ci-header-sub">
                            <i class="fas fa-user mr-1"></i>{{ $reserva->usuario->nome ?? 'Cliente' }}
                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-car mr-1"></i>{{ $reserva->veiculo->nome ?? $reserva->modelos->nome ?? 'Veículo' }}
                        </p>
                    </div>
                </div>
                <div class="ci-header-right">
                    @php
                        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : null;
                        $dataFim    = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : null;
                    @endphp
                    @if($dataInicio)
                        <div class="ci-date-badge">
                            <i class="fas fa-calendar-alt"></i>
                            <span>{{ $dataInicio }}@if($dataFim) &rarr; {{ $dataFim }}@endif</span>
                        </div>
                    @endif
                    <a href="{{ route('reserva.show', $reserva->id) }}" class="ci-btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>

            <div class="ci-body">

                <input type="hidden" id="reserva_id" name="reserva_id" value="{{ $reserva->id }}">
                <input type="hidden" id="veiculo" name="veiculo" value="{{ $reserva->veiculo_id }}">

                {{-- Indicador de passos --}}
                <div class="step-indicator" role="tablist">
                    @php
                        $steps = [
                            ['label' => 'Dados da Devolução', 'icon' => 'fas fa-car-side'],
                            ['label' => 'Fotos da Devolução', 'icon' => 'fas fa-camera'],
                            ['label' => 'Vistoria do Veículo', 'icon' => 'fas fa-search'],
                            ['label' => 'Observações', 'icon' => 'fas fa-comment-alt'],
                        ];
                    @endphp
                    @foreach($steps as $i => $step)
                        <button type="button" class="step-btn {{ $i === 0 ? 'active' : '' }}" data-step-target="{{ $i + 1 }}">
                            <span class="step-num">{{ $i + 1 }}</span>
                            <span class="step-label-text">
                                <i class="{{ $step['icon'] }}"></i>
                                {{ $step['label'] }}
                            </span>
                        </button>
                        @if($i < count($steps) - 1)
                            <div class="step-divider"></div>
                        @endif
                    @endforeach
                </div>

                {{-- ═══════════════════════════════════
                     PASSO 1 — Dados da Devolução
                ═══════════════════════════════════ --}}
                <section class="wizard-step active" data-step="1">
                      <div class="rw-step-content-header">
                        <div class="rw-step-content-icon"><i class="fas fa-car"></i></div>
                        <div>
                            <h5>Dados do Veiculo</h5>
                            <p>Selecione o veiculo, registre a quilometragem atual e defina data e hora de saida.</p>
                        </div>
                    </div>

                    <form id="form_dados_devolucao">
                        {{-- Bloco: Veículo vinculado --}}
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-car"></i>
                                <div>
                                    <strong>Veículo Vinculado</strong>
                                    <span>Este é o veículo em uso nesta reserva</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="mb-2"><strong>{{ $reserva->veiculo->nome ?? ('#' . $reserva->veiculo_id) }}</strong>
                                        @if($reserva->veiculo?->placa_licenca)
                                            <span class="text-muted">({{ $reserva->veiculo->placa_licenca }})</span>
                                        @endif
                                    </p>
                                    @if($reserva->contratoCheckin)
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Referência check-in: KM {{ $reserva->contratoCheckin->quilometragem_atual ?? '—' }},
                                            combustível {{ $reserva->contratoCheckin->nivel_combustivel ?? '—' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Condição e devolução --}}
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-tachometer-alt"></i>
                                <div>
                                    <strong>Condição na Devolução</strong>
                                    <span>Quilometragem, nível de combustível e data/hora de devolução</span>
                                </div>
                            </div>
                            @php
                                $kmCheckin = (float) ($reserva->contratoCheckin->quilometragem_atual ?? 0);
                                $niveis    = ['Reserva','1/8','1/4','3/8','1/2','5/8','3/4','7/8','Cheio'];
                                $coContrato = $reserva->contratoCheckout ?? null;
                                $coKm        = old('quilometragem_devolucao',  $coContrato?->quilometragem_devolucao ?? ($kmCheckin ?: ''));
                                $coCombust   = old('combustivel_devolucao',    $coContrato?->nivel_combustivel_devolucao ?? '');
                                $coData      = old('data_devolucao',           $coContrato?->data_devolucao ?? date('Y-m-d'));
                                $coHora      = old('hora_devolucao',           $coContrato?->hora_devolucao  ? substr($coContrato->hora_devolucao, 0, 5) : date('H:i'));
                            @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="quilometragem_devolucao">
                                        Quilometragem na Devolução <span class="req">*</span>
                                        @if($kmCheckin)
                                            <small class="text-muted">(mín: {{ $kmCheckin }})</small>
                                        @endif
                                    </label>
                                    <input type="number" required class="form-control form-control-sm"
                                           id="quilometragem_devolucao" name="quilometragem_devolucao"
                                           placeholder="Ex: 45800"
                                           min="{{ $kmCheckin }}"
                                           value="{{ $coKm }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="combustivel_devolucao">Nível de Combustível <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" id="combustivel_devolucao" required name="combustivel_devolucao">
                                        <option value="" disabled {{ !$coCombust ? 'selected' : '' }}>Selecione</option>
                                        @foreach($niveis as $nivel)
                                            <option {{ $coCombust === $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="data_devolucao">Data de Devolução <span class="req">*</span></label>
                                    <input type="date" class="form-control form-control-sm" required
                                           id="data_devolucao" name="data_devolucao" value="{{ $coData }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="hora_devolucao">Hora de Devolução <span class="req">*</span></label>
                                    <input type="time" class="form-control form-control-sm" required
                                           id="hora_devolucao" name="hora_devolucao" value="{{ $coHora }}">
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                {{-- ═══════════════════════════════════
                     PASSO 2 — Fotos da Devolução
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="2">
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-images"></i>
                            <div>
                                <strong>Fotos na Devolução</strong>
                                <span>Direita, esquerda, frente, trás, velocímetro e combustível</span>
                            </div>
                        </div>

                        @php
                            $fotosCheckout = [
                                ['id' => 'foto_direita_co',     'label' => 'Direita',               'col' => 'foto_direita_path'],
                                ['id' => 'foto_esquerda_co',    'label' => 'Esquerda',              'col' => 'foto_esquerda_path'],
                                ['id' => 'foto_frente_co',      'label' => 'Frente',                'col' => 'foto_frente_path'],
                                ['id' => 'foto_tras_co',        'label' => 'Trás',                  'col' => 'foto_tras_path'],
                                ['id' => 'foto_velocimetro_co', 'label' => 'Velocímetro',           'col' => 'foto_velocimetro_path'],
                                ['id' => 'foto_combustivel_co', 'label' => 'Medidor de Combustível','col' => 'foto_combustivel_path'],
                            ];
                        @endphp

                        <div class="row">
                            @foreach($fotosCheckout as $f)
                                @php
                                    $fotoExistente = $coContrato?->{$f['col']} ?? null;
                                    $fotoUrl = $fotoExistente ? asset('storage/' . $fotoExistente) : null;
                                @endphp
                                <div class="col-md-4">
                                    <div class="ci-photo-card">
                                        <label for="{{ $f['id'] }}">{{ $f['label'] }}</label>
                                        <input type="file" class="form-control form-control-sm"
                                               id="{{ $f['id'] }}" name="{{ $f['id'] }}" accept="image/*">
                                        <div class="ci-photo-preview" data-preview-for="{{ $f['id'] }}">
                                            @if($fotoUrl)
                                                <img src="{{ $fotoUrl }}" alt="{{ $f['label'] }}" style="max-width:100%;max-height:160px;border-radius:6px;">
                                            @else
                                                <span class="ci-photo-preview__placeholder">
                                                    <i class="far fa-image mr-1"></i>Sem imagem
                                                </span>
                                            @endif
                                        </div>
                                        <small class="ci-hint">
                                            @if($fotoUrl)
                                                Foto atual. Selecione para substituir.
                                            @else
                                                Opcional. Max 8MB. JPG/PNG/WEBP.
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- ═══════════════════════════════════
                     PASSO 3 — Vistoria do Veículo
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="3">
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-car-crash"></i>
                            <div>
                                <strong>Vistas do Veículo</strong>
                                <span>Clique na vista onde o dano se encontra para registrá-lo</span>
                            </div>
                        </div>
                        <div class="car-views-grid">
                            @php
                                $vistas = [
                                    ['nome' => 'DIREITA',   'arquivo' => 'direita.jpg',   'icon' => 'fas fa-arrow-right'],
                                    ['nome' => 'ESQUERDA',  'arquivo' => 'esquerda.jpg',  'icon' => 'fas fa-arrow-left'],
                                    ['nome' => 'CIMA',      'arquivo' => 'cima.jpg',       'icon' => 'fas fa-arrow-up'],
                                    ['nome' => 'FRENTE',    'arquivo' => 'frente.jpg',     'icon' => 'fas fa-dot-circle'],
                                    ['nome' => 'ATRÁS',     'arquivo' => 'atras.jpg',      'icon' => 'fas fa-circle'],
                                ];
                            @endphp
                            @foreach($vistas as $vista)
                                <div class="car-view-card" onclick="setNomeDano('{{ $vista['nome'] }}')" data-toggle="modal" data-target="#detalhesDanoModal">
                                    <div class="car-view-card__img">
                                        <img src="{{ asset('assets/imgs/carro/' . $vista['arquivo']) }}" alt="{{ $vista['nome'] }}">
                                        <div class="car-view-card__overlay">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Registrar dano</span>
                                        </div>
                                    </div>
                                    <div class="car-view-card__label">
                                        <i class="{{ $vista['icon'] }}"></i>
                                        {{ $vista['nome'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="ci-damages-grid">
                        <div class="ci-block ci-block--no-mb">
                            <div class="ci-block-label">
                                <i class="fas fa-history"></i>
                                <div>
                                    <strong>Histórico no Veículo</strong>
                                    <span>Avarias de outras reservas / correções registradas</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Correção</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_existentes">
                                        <tr>
                                            <td colspan="4" class="damage-empty">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando…
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="ci-block ci-block--no-mb ci-block--amber">
                            <div class="ci-block-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Danos Nesta Reserva</strong>
                                    <span>Avarias registradas no check-in ou nesta devolução</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Tipo</th>
                                            <th>Custo Est.</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_detectados">
                                        <tr>
                                            <td colspan="5" class="damage-empty">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando…
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('reserva.components.modal_dano_checkin')
                </section>

                {{-- ═══════════════════════════════════
                     PASSO 4 — Observações
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="4">
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Notas e Observações na Devolução</strong>
                                <span>Informações que ficam registradas no contrato de check-out</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="notas_devolucao">Notas na Devolução</label>
                                <textarea class="form-control form-control-sm" id="notas_devolucao" name="notas_devolucao" rows="5"
                                          placeholder="Ex: Arranhão novo no para-lama…">{{ old('notas_devolucao', $coContrato?->notas_devolucao ?? '') }}</textarea>
                                <small class="ci-hint">Notas visíveis no contrato gerado.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="observacoes_devolucao">Observações Internas</label>
                                <textarea class="form-control form-control-sm" id="observacoes_devolucao" name="observacoes_devolucao" rows="5"
                                          placeholder="Ex: Funcionário que recebeu o veículo…">{{ old('observacoes_devolucao', $coContrato?->observacoes_devolucao ?? '') }}</textarea>
                                <small class="ci-hint">Observações internas do operador.</small>
                            </div>
                        </div>
                    </div>

                    <div class="ci-summary-card co-summary-checkout">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Concluir check-out</strong>
                            <p>Verifique os dados dos passos anteriores. Esta ação finaliza a reserva e registra a movimentação de devolução.</p>
                        </div>
                        <button type="button" class="ci-btn-create co-btn-finalizar" id="btn_finalizar_checkout">
                            <i class="fas fa-flag-checkered"></i>
                            Concluir check-out
                        </button>
                    </div>
                </section>

                {{-- Ações do wizard --}}
                <div class="wizard-actions">
                    <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </button>
                    <div class="wizard-actions-right">
                        <button type="button" class="btn-action btn-primary" id="wizardNext">
                            Continuar<i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="button" class="btn-action btn-action-success d-none" id="wizardCreateBtn">
                            <i class="fas fa-flag-checkered mr-1"></i>Concluir check-out
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
    @include('reserva.partials.vistoria_reserva_estilos_inner')

    /* ── Step content header (padrão add-edit) ── */
    .rw-step-content-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        background: #f8faff;
        border: 1px solid var(--c-border);
        border-left: 4px solid var(--c-indigo);
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    .rw-step-content-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: color-mix(in srgb, var(--c-indigo) 12%, #fff 88%);
        color: var(--c-indigo);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .rw-step-content-icon--blue  { background: #eff6ff; color: #2563eb; }
    .rw-step-content-icon--amber { background: #fffbeb; color: #f59e0b; }
    .rw-step-content-icon--green { background: #ecfdf5; color: #10b981; }
    .rw-step-content-header:has(.rw-step-content-icon--amber) { border-left-color: var(--c-amber); background: #fffdf5; }
    .rw-step-content-header:has(.rw-step-content-icon--green) { border-left-color: var(--c-green); background: #f0fdf9; }
    .rw-step-content-header h5 {
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--c-ink);
        margin: 0 0 2px;
        letter-spacing: -0.01em;
    }
    .rw-step-content-header p {
        font-size: 0.78rem;
        color: var(--c-slate);
        margin: 0;
    }
    .ci-photo-card {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 14px;
        background: #fff;
    }
    .ci-photo-preview {
        margin-top: 10px;
        height: 130px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px dashed rgba(0,0,0,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .ci-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ci-photo-preview__placeholder {
        color: rgba(0,0,0,.55);
        font-size: .9rem;
    }
</style>
@stop

@push('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {

    $(document).ready(function () {
        $('.select2ci-modal').select2({ dropdownParent: $('#detalhesDanoModal') });

        $('#detalhesDanoModal').on('show.bs.modal', function () {
            const km = document.getElementById('quilometragem_devolucao')?.value;
            const q = document.getElementById('quilometragem_dano');
            if (q && km) q.value = km;
        });

        obterDanosDetectados();
        obterDanosExistentes();
    });

    const stepBtns   = Array.from(document.querySelectorAll('.step-btn'));
    const stepPanels = Array.from(document.querySelectorAll('.wizard-step[data-step]'));
    const prevBtn    = document.getElementById('wizardPrev');
    const nextBtn    = document.getElementById('wizardNext');
    const createBtn  = document.getElementById('wizardCreateBtn');
    let current      = 1;
    const lastStep   = stepPanels.length;
    const PHOTO_INPUT_IDS = [
        'foto_direita_co',
        'foto_esquerda_co',
        'foto_frente_co',
        'foto_tras_co',
        'foto_velocimetro_co',
        'foto_combustivel_co',
    ];

    function setupPhotoPreviews() {
        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            const preview = document.querySelector(`.ci-photo-preview[data-preview-for="${id}"]`);
            if (!input || !preview) return;

            let objectUrl = null;
            input.addEventListener('change', function () {
                input.classList.remove('is-invalid');

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (this.files && this.files[0]) {
                    objectUrl = URL.createObjectURL(this.files[0]);
                    preview.innerHTML = `<img src="${objectUrl}" alt="Preview">`;
                } else {
                    preview.innerHTML = `
                        <span class="ci-photo-preview__placeholder">
                            <i class="far fa-image mr-1"></i>Sem imagem
                        </span>
                    `;
                }
            });
        });
    }

    setupPhotoPreviews();

    function showStep(n) {
        current = Math.min(Math.max(n, 1), lastStep);

        stepPanels.forEach(panel => {
            panel.classList.toggle('active', Number(panel.dataset.step) === current);
        });

        stepBtns.forEach(btn => {
            const t = Number(btn.dataset.stepTarget);
            btn.classList.toggle('active', t === current);
            btn.classList.toggle('completed', t < current);
        });

        prevBtn.style.visibility = current === 1 ? 'hidden' : 'visible';
        nextBtn.style.display    = current === lastStep ? 'none' : 'inline-flex';
        createBtn.classList.toggle('d-none', current !== lastStep);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    stepBtns.forEach(btn => {
        btn.addEventListener('click', () => showStep(Number(btn.dataset.stepTarget)));
    });

    prevBtn.addEventListener('click', () => showStep(current - 1));
    nextBtn.addEventListener('click', () => showStep(current + 1));

    function triggerFinalizar() {
        document.getElementById('form_dados_devolucao').dispatchEvent(new Event('submit'));
    }

    document.getElementById('btn_finalizar_checkout')?.addEventListener('click', triggerFinalizar);
    createBtn?.addEventListener('click', triggerFinalizar);

    document.getElementById('form_dados_devolucao').addEventListener('submit', function (event) {
        event.preventDefault();

        const kmInicialRaw = @json($reserva->contratoCheckin?->quilometragem_atual);
        const kmInicial = kmInicialRaw === null ? null : Number(kmInicialRaw);
        const kmDevolucao = Number(document.getElementById('quilometragem_devolucao')?.value || 0);
        if (kmInicial !== null && !Number.isNaN(kmInicial) && (Number.isNaN(kmDevolucao) || kmDevolucao < kmInicial)) {
            const campoKm = document.getElementById('quilometragem_devolucao');
            if (campoKm) campoKm.classList.add('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Quilometragem invÃ¡lida',
                text: `A quilometragem na devoluÃ§Ã£o deve ser maior ou igual ao KM inicial do check-in (${kmInicial}).`,
                confirmButtonColor: '#4338ca',
            });
            showStep(1);
            return;
        }

        const campos = document.querySelectorAll('#form_dados_devolucao input:required, #form_dados_devolucao select:required');
        const camposInvalidos = [];
        campos.forEach(campo => {
            const invalido = !campo.value;
            campo.classList.toggle('is-invalid', invalido);
            if (invalido) camposInvalidos.push(campo.name);
        });

        if (camposInvalidos.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Campos obrigatórios',
                html: `<ul style="text-align:left">${camposInvalidos.map(c => `<li>${c.replace(/_/g, ' ')}</li>`).join('')}</ul>`,
                confirmButtonColor: '#4338ca',
            });
            showStep(1);
            return;
        }

        finalizarCheckout();
    });

    function finalizarCheckout() {
        const reserva_id = document.getElementById('reserva_id').value;
        const url = "{{ route('reserva.checkout.finalizar', ':id') }}".replace(':id', reserva_id);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const payload = {
            quilometragem_devolucao: document.getElementById('quilometragem_devolucao')?.value,
            nivel_combustivel_devolucao: document.getElementById('combustivel_devolucao')?.value,
            data_devolucao: document.getElementById('data_devolucao')?.value,
            hora_devolucao: document.getElementById('hora_devolucao')?.value,
            notas_devolucao: document.getElementById('notas_devolucao')?.value || '',
            observacoes_devolucao: document.getElementById('observacoes_devolucao')?.value || '',
        };

        const btnFinal = document.getElementById('btn_finalizar_checkout');
        const wizBtn   = document.getElementById('wizardCreateBtn');
        [btnFinal, wizBtn].forEach(b => {
            if (b) {
                b.disabled = true;
                b._oh = b.innerHTML;
                b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            }
        });

        $.ajax({
            url,
            type: 'POST',
            data: payload,
            headers: { 'X-CSRF-TOKEN': csrf },
            success: function (data) {
                Swal.fire({
                    icon: 'success',
                    title: data.message || 'Check-out concluído!',
                    showConfirmButton: false,
                    timer: 2200,
                    toast: true,
                    position: 'top-right',
                });
                setTimeout(() => {
                    window.location.href = data.redirect || "{{ route('reserva.show', ':id') }}".replace(':id', reserva_id);
                }, 800);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Erro ao finalizar check-out.';
                Swal.fire({ icon: 'error', title: 'Erro', text: msg, confirmButtonColor: '#4338ca' });
                [btnFinal, wizBtn].forEach(b => {
                    if (b) { b.disabled = false; b.innerHTML = b._oh || b.innerHTML; }
                });
            }
        });
    }

    function rowImagens(dano) {
        const imgs = dano.imagens || [];
        return imgs.map(function (i) {
            const url = i.url || (i.path ? `{{ asset('storage') }}/${i.path}` : (i.imagem ? `{{ asset('storage') }}/${i.imagem}` : null));
            if (!url) return '';
            return `<a href="${url}" target="_blank" rel="noopener"><img src="${url}" alt="Dano" style="width:44px;height:44px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;margin-right:6px;"></a>`;
        }).filter(Boolean).join(' ');
    }

    function obterDanosDetectados() {
        const veiculo_id = document.getElementById('veiculo')?.value;
        const reserva_id = document.getElementById('reserva_id')?.value;

        $.ajax({
            url: "{{ route('dano.obterDanosDetectados') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_detectados');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="damage-empty"><i class="fas fa-shield-alt"></i>Nenhuma avaria nesta reserva</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}</td>
                            <td>${rowImagens(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    function obterDanosExistentes() {
        const veiculo_id = document.getElementById('veiculo')?.value;
        const reserva_id = document.getElementById('reserva_id')?.value;

        $.ajax({
            url: "{{ route('dano.obterDanosExistentes') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_existentes');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="4" class="damage-empty"><i class="fas fa-check-circle"></i>Sem histórico de avarias anteriores</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    let corr = '<span class="text-muted">—</span>';
                    if (dano.correcao_realizada) {
                        corr = `<span class="text-success"><i class="fas fa-wrench"></i> ${dano.correcao_descricao ?? ''}<br><small>${dano.correcao_data ?? ''}</small></span>`;
                    } else {
                        corr = '<span class="text-warning"><i class="fas fa-hourglass-half"></i> Pendente</span>';
                    }
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}</td>
                            <td>${corr}</td>
                            <td>${rowImagens(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    window.obterDanosDetectados = obterDanosDetectados;
    window.obterDanosExistentes = obterDanosExistentes;

    function setNomeDano(nome) {
        const el = document.getElementById('informacao_dano');
        if (el) el.innerText = nome;
    }
    window.setNomeDano = setNomeDano;

    showStep(1);

})();
</script>
@endpush
