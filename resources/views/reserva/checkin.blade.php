@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

    <div class="col-12 px-0">
        <div class="ci-card">

            {{-- ══════════════════════════════════════
                 HEADER ESCURO
            ══════════════════════════════════════ --}}
            <div class="ci-card-header">
                <div class="ci-header-left">
                    <div class="ci-header-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="ci-header-eyebrow">Check-in</div>
                        <h4 class="ci-header-title">Reserva #{{ $reserva->id ?? '' }}</h4>
                        <p class="ci-header-sub">
                            <i class="fas fa-user mr-1"></i>{{ $reserva->usuario->nome ?? 'Cliente' }}
                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-car mr-1"></i>{{ $reserva->modelos->nome ?? 'Veiculo' }}
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

            {{-- ══════════════════════════════════════
                 BODY
            ══════════════════════════════════════ --}}
            <div class="ci-body">

                <input type="hidden" id="reserva_id" name="reserva_id" value="{{ $reserva->id }}">

                {{-- Indicador de passos --}}
                <div class="step-indicator" role="tablist">
                    @php
                        $steps = [
                            ['label' => 'Dados do Veiculo', 'icon' => 'fas fa-car'],
                            ['label' => 'Fotos do Veiculo', 'icon' => 'fas fa-camera'],
                            ['label' => 'Estado do Veiculo', 'icon' => 'fas fa-search'],
                            ['label' => 'Observacoes', 'icon' => 'fas fa-comment-alt'],
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
                     PASSO 1 — Dados do Veiculo
                ═══════════════════════════════════ --}}
                <section class="wizard-step active" data-step="1">
                    <div class="step-head">
                        <h5><i class="fas fa-car mr-2"></i>Dados do Veiculo</h5>
                        <p>Selecione o veiculo, registre a quilometragem atual e defina data e hora de saida.</p>
                    </div>

                    <form id="form_dados_veiculos">

                        {{-- Banner de upgrade de categoria (exibido via JS quando categoria original indisponivel) --}}
                        <div id="upgrade-banner" class="alert alert-warning d-none mb-3" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-arrow-circle-up fa-2x mr-3 text-warning mt-1"></i>
                                <div>
                                    <strong>Upgrade por Indisponibilidade de Categoria</strong>
                                    <p class="mb-1 small">
                                        Nao ha veiculos disponiveis na categoria reservada
                                        (<span id="upgrade-grupo-original"></span>).
                                        Selecione um veiculo de categoria superior abaixo.
                                    </p>
                                    <div class="form-group mb-1">
                                        <label class="small mb-0">Motivo do upgrade</label>
                                        <input type="text" id="motivo_upgrade_input" class="form-control form-control-sm"
                                               value="Indisponibilidade na categoria original" maxlength="500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Selecao do veiculo --}}
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-car"></i>
                                <div>
                                    <strong>Identificacao do Veiculo</strong>
                                    <span>Marcas e modelos vêm apenas de veículos deste grupo de frota. Depois de escolher os dois, listamos os veículos livres no período desta reserva.</span>
                                </div>
                            </div>
                            @if($marcas->isEmpty())
                                <p class="text-warning small mb-2"><i class="fas fa-exclamation-triangle"></i> Não há veículos cadastrados neste grupo de frota (ou sem modelo). Cadastre veículos antes do check-in.</p>
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="marca">Marca <span class="req">*</span></label>
                                    <select required name="marca" id="marca" class="form-control form-control-sm select2ci">
                                        @php $marcaReserva = (int) ($reserva->modelos->marca_id ?? 0); @endphp
                                        <option value="" disabled {{ $marcaReserva === 0 ? 'selected' : '' }}>Selecione uma marca</option>
                                        @foreach ($marcas as $marca)
                                            <option value="{{ $marca->id }}" {{ $marcaReserva === (int) $marca->id ? 'selected' : '' }}>{{ $marca->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="modelo">Modelo <span class="req">*</span></label>
                                    <select required name="modelo" id="modelo" class="form-control form-control-sm select2ci">
                                        <option value="" selected disabled>Selecione a marca</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="veiculo">Veiculo <span class="req">*</span></label>
                                    <select required name="veiculo" id="veiculo" class="form-control form-control-sm select2ci">
                                        <option value="" selected disabled>Selecione marca e modelo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Bloco: Condicao e saida --}}
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-tachometer-alt"></i>
                                <div>
                                    <strong>Condicao e Saida</strong>
                                    <span>Quilometragem atual, nivel de combustivel e data/hora de saida</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="quilometragem">Quilometragem Atual <span class="req">*</span></label>
                                    <input type="number" required class="form-control form-control-sm" id="quilometragem" name="quilometragem" placeholder="Ex: 45200">
                                </div>
                                <div class="col-md-3">
                                    <label for="combustivel">Nivel de Combustivel <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" id="combustivel" required name="combustivel">
                                        <option>Reserva</option>
                                        <option>1/8</option>
                                        <option>1/4</option>
                                        <option>3/8</option>
                                        <option>1/2</option>
                                        <option>5/8</option>
                                        <option>3/4</option>
                                        <option>7/8</option>
                                        <option>Cheio</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="data_saida">Data de Saida <span class="req">*</span></label>
                                    <input type="date" class="form-control form-control-sm" required id="data_saida" name="data_saida" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="hora_saida">Hora de Saida <span class="req">*</span></label>
                                    <input type="time" class="form-control form-control-sm" required id="hora_saida" name="hora_saida">
                                </div>
                            </div>
                        </div>

                    </form>
                </section>

                {{-- ═══════════════════════════════════
                     PASSO 2 — Estado do Veiculo
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="2">
                    <div class="step-head">
                        <h5><i class="fas fa-camera mr-2"></i>Fotos do Veiculo</h5>
                        <p>Envie as fotos obrigatorias para registrar o estado do veiculo no check-in. Nao e possivel continuar sem concluir este passo.</p>
                    </div>

                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-images"></i>
                            <div>
                                <strong>Fotos Obrigatorias</strong>
                                <span>Direita, esquerda, frente, tras, velocimetro e combustivel</span>
                            </div>
                        </div>

                        @php
                            $fotos = [
                                ['id' => 'foto_direita',     'label' => 'Direita'],
                                ['id' => 'foto_esquerda',    'label' => 'Esquerda'],
                                ['id' => 'foto_frente',      'label' => 'Frente'],
                                ['id' => 'foto_tras',        'label' => 'Tras'],
                                ['id' => 'foto_velocimetro', 'label' => 'Velocimetro'],
                                ['id' => 'foto_combustivel', 'label' => 'Medidor de Combustivel'],
                            ];
                        @endphp

                        <div class="row">
                            @foreach($fotos as $f)
                                <div class="col-md-4">
                                    <div class="ci-photo-card">
                                        <label for="{{ $f['id'] }}">{{ $f['label'] }} <span class="req">*</span></label>
                                        <input type="file" class="form-control form-control-sm" id="{{ $f['id'] }}" name="{{ $f['id'] }}" required accept="image/*">
                                        <div class="ci-photo-preview" data-preview-for="{{ $f['id'] }}">
                                            <span class="ci-photo-preview__placeholder">
                                                <i class="far fa-image mr-1"></i>Sem imagem
                                            </span>
                                        </div>
                                        <small class="ci-hint">Max 8MB. Formatos: JPG/PNG/WEBP.</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="wizard-step" data-step="3">
                    <div class="step-head">
                        <h5><i class="fas fa-search mr-2"></i>Estado do Veiculo</h5>
                        <p>Clique em uma das vistas do veiculo para registrar um novo dano. O veiculo deve estar selecionado no Passo 1.</p>
                    </div>

                    {{-- Vistas do carro --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-car-crash"></i>
                            <div>
                                <strong>Vistas do Veiculo</strong>
                                <span>Clique na vista onde o dano se encontra para registra-lo</span>
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

                    {{-- Tabelas de danos --}}
                    <div class="ci-damages-grid">
                        <div class="ci-block ci-block--no-mb">
                            <div class="ci-block-label">
                                <i class="fas fa-history"></i>
                                <div>
                                    <strong>Danos Existentes</strong>
                                    <span>Registros anteriores a esta reserva</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localizacao</th>
                                            <th>Descricao</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_existentes">
                                        <tr>
                                            <td colspan="3" class="damage-empty">
                                                <i class="fas fa-check-circle"></i>
                                                Selecione um veiculo para carregar os danos
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
                                    <strong>Danos Detectados Nesta Reserva</strong>
                                    <span>Danos registrados durante o check-in atual</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localizacao</th>
                                            <th>Descricao</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_detectados">
                                        <tr>
                                            <td colspan="3" class="damage-empty">
                                                <i class="fas fa-shield-alt"></i>
                                                Nenhum dano detectado ainda
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
                     PASSO 3 — Observacoes
                ═══════════════════════════════════ --}}
                <section class="wizard-step" data-step="4">
                    <div class="step-head">
                        <h5><i class="fas fa-comment-alt mr-2"></i>Observacoes e Notas</h5>
                        <p>Registre quaisquer observacoes adicionais sobre a saida do veiculo.</p>
                    </div>

                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Notas e Observacoes na Saida</strong>
                                <span>Informacoes que ficam registradas no contrato</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="notas_saida">Notas na Saida</label>
                                <textarea class="form-control form-control-sm" id="notas_saida" name="notas_saida" rows="5" placeholder="Ex: Cliente informou arranhao no para-choque traseiro..."></textarea>
                                <small class="ci-hint">Notas visíveis no contrato gerado.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="observacoes_saida">Observacoes na Saida</label>
                                <textarea class="form-control form-control-sm" id="observacoes_saida" name="observacoes_saida" rows="5" placeholder="Ex: Veiculo entregue com tanque cheio..."></textarea>
                                <small class="ci-hint">Observacoes internas do operador.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Resumo antes de criar --}}
                    <div class="ci-summary-card">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Pronto para criar o contrato?</strong>
                            <p>Verifique se todos os dados dos Passos 1 e 2 estao preenchidos. Ao clicar em <em>Criar Contrato</em> o contrato sera gerado e voce sera redirecionado para a reserva.</p>
                        </div>
                        <button type="button" class="ci-btn-create" id="btn_criar_contrato">
                            <i class="fas fa-file-contract"></i>
                            Criar Contrato
                        </button>
                    </div>
                </section>

                {{-- Acoes do wizard --}}
                <div class="wizard-actions">
                    <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </button>
                    <div class="wizard-actions-right">
                        <button type="button" class="btn-action btn-action-primary" id="wizardNext">
                            Continuar<i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="button" class="btn-action btn-action-success d-none" id="wizardCreateBtn">
                            <i class="fas fa-file-contract mr-1"></i>Criar Contrato
                        </button>
                    </div>
                </div>

            </div>{{-- /ci-body --}}
        </div>{{-- /ci-card --}}
    </div>

@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
    @include('reserva.partials.vistoria_reserva_estilos_inner')

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

@section('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {

    const CHECKIN_MODELOS = @json($checkinModelosJson ?? []);
    const RESERVA_MODELO_ID = @json((int) ($reserva->modelo_id ?? 0));
    const RESERVA_MARCA_ID = @json((int) ($reserva->modelos->marca_id ?? 0));

    function rebuildModeloSelect(marcaId) {
        const $m = $('#modelo');
        $m.empty();
        $m.append(new Option('Selecione um modelo', '', true, true));
        $m.find('option:first').prop('disabled', true);
        if (!marcaId) {
            $m.val('').trigger('change');
            return;
        }
        CHECKIN_MODELOS.filter(o => String(o.marca_id) === String(marcaId))
            .forEach(o => $m.append(new Option(o.nome, o.id)));
        $m.val('').trigger('change');
    }

    function limparVeiculosSelect() {
        const $v = $('#veiculo');
        $v.empty();
        $v.append(new Option('Selecione marca e modelo', '', true, true));
        $v.find('option:first').prop('disabled', true);
        $v.val('').trigger('change');
    }

    function carregarVeiculosDisponiveisCheckin() {
        const marcaId = $('#marca').val();
        const modeloId = $('#modelo').val();
        const reserva_id = $('#reserva_id').val();
        const $v = $('#veiculo');

        if (!marcaId || !modeloId) {
            limparVeiculosSelect();
            return;
        }

        const url = "{{ route('reserva.checkin.veiculosDisponiveis', ':id') }}".replace(':id', reserva_id);
        $v.prop('disabled', true);

        $.getJSON(url, { marca_id: marcaId, modelo_id: modeloId })
            .done(function (data) {
                $v.empty();
                $v.append(new Option('Selecione um veiculo', '', true, true));
                $v.find('option:first').prop('disabled', true);
                if (!data.length) {
                    $v.append(new Option('Nenhum veiculo disponivel neste periodo', '', true, true));
                    $v.find('option:last').prop('disabled', true);
                } else {
                    data.forEach(row => $v.append(new Option(row.nome, row.id)));
                }
                $v.prop('disabled', false).val('').trigger('change');
            })
            .fail(function (xhr) {
                $v.prop('disabled', false);
                limparVeiculosSelect();
                const msg = xhr.responseJSON?.message || 'Erro ao carregar veiculos.';
                Swal.fire({ icon: 'error', title: msg });
            });
    }

    /* ========================
       SELECT2
    ======================== */
    $(document).ready(function () {
        $('.select2ci').select2();

        const marcaInicial = $('#marca').val();
        if (marcaInicial) {
            rebuildModeloSelect(marcaInicial);
            if (RESERVA_MODELO_ID && RESERVA_MARCA_ID && String(RESERVA_MARCA_ID) === String(marcaInicial)) {
                $('#modelo').val(String(RESERVA_MODELO_ID)).trigger('change');
                carregarVeiculosDisponiveisCheckin();
            }
        }

        $('#marca').on('change', function () {
            rebuildModeloSelect($(this).val());
            limparVeiculosSelect();
        });

        $('#modelo').on('change', function () {
            if ($(this).val()) {
                carregarVeiculosDisponiveisCheckin();
            } else {
                limparVeiculosSelect();
            }
        });

        $('#veiculo').change(function () {
            obterDanosDetectados();
            obterDanosExistentes();
        });
    });

    /* ========================
       WIZARD STEPS
    ======================== */
    const PHOTO_INPUT_IDS = [
        'foto_direita',
        'foto_esquerda',
        'foto_frente',
        'foto_tras',
        'foto_velocimetro',
        'foto_combustivel',
    ];

    const stepBtns   = Array.from(document.querySelectorAll('.step-btn'));
    const stepPanels = Array.from(document.querySelectorAll('.wizard-step[data-step]'));
    const prevBtn    = document.getElementById('wizardPrev');
    const nextBtn    = document.getElementById('wizardNext');
    const createBtn  = document.getElementById('wizardCreateBtn');
    let current      = 1;
    const lastStep   = stepPanels.length;

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

    function validateStep1(showAlert = true) {
        const campos = document.querySelectorAll('#form_dados_veiculos input:required, #form_dados_veiculos select:required');
        const camposInvalidos = [];

        campos.forEach(campo => {
            const invalido = !campo.value || (campo.classList.contains('select2-hidden-accessible') && !$(campo).val());
            campo.classList.toggle('is-invalid', invalido);
            if (invalido) camposInvalidos.push(campo.name);
        });

        if (camposInvalidos.length > 0) {
            if (showAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos obrigatorios nao preenchidos',
                    html: `<ul style="text-align:left">${camposInvalidos.map(c => `<li>${String(c).replace(/_/g, ' ')}</li>`).join('')}</ul>`,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                    toast: false,
                });
            }
            return false;
        }

        return true;
    }

    function validateStep2Photos(showAlert = true) {
        const invalidos = [];

        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            const invalido = !input || !input.files || input.files.length === 0;
            if (input) input.classList.toggle('is-invalid', invalido);
            if (invalido) invalidos.push(id);
        });

        if (invalidos.length > 0) {
            if (showAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Fotos obrigatorias nao enviadas',
                    html: `<div style="text-align:left">Envie todas as fotos do Passo 2 para continuar.</div>`,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                    toast: false,
                });
            }
            return false;
        }

        return true;
    }

    function goToStep(target) {
        // Voltar nunca bloqueia; avancar exige concluir os passos anteriores.
        if (target <= current) {
            showStep(target);
            return;
        }

        if (target >= 2 && !validateStep1(true)) {
            showStep(1);
            return;
        }

        if (target >= 3 && !validateStep2Photos(true)) {
            showStep(2);
            return;
        }

        showStep(target);
    }

    stepBtns.forEach(btn => {
        btn.addEventListener('click', () => goToStep(Number(btn.dataset.stepTarget)));
    });

    prevBtn.addEventListener('click', () => showStep(current - 1));
    nextBtn.addEventListener('click', () => goToStep(current + 1));

    /* ========================
       CRIAR CONTRATO
    ======================== */
    function triggerCriarContrato() {
        if (!validateStep1(true)) {
            showStep(1);
            return;
        }
        if (!validateStep2Photos(true)) {
            showStep(2);
            return;
        }

        criarContrato();
    }

    document.getElementById('btn_criar_contrato')?.addEventListener('click', triggerCriarContrato);
    createBtn?.addEventListener('click', triggerCriarContrato);

    document.getElementById('form_dados_veiculos').addEventListener('submit', function (event) {
        event.preventDefault();
        if (!validateStep1(true)) {
            showStep(1);
            return;
        }

        // Enter no Passo 1 avanca para o Passo 2.
        goToStep(2);
    });

    function criarContrato() {
        const reserva_id = document.getElementById('reserva_id').value;
        const url = "{{ route('reserva.criarContratoCheckin', ':id') }}".replace(':id', reserva_id);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const contrato = {
            marca_id:           document.getElementById('marca')?.value          || null,
            modelo_id:          document.getElementById('modelo')?.value         || null,
            veiculo_id:         document.getElementById('veiculo')?.value        || null,
            quilometragem_atual: document.getElementById('quilometragem')?.value || null,
            nivel_combustivel:  document.getElementById('combustivel')?.value    || null,
            data_saida:         document.getElementById('data_saida')?.value     || null,
            hora_saida:         document.getElementById('hora_saida')?.value     || null,
            notas_saida:        document.getElementById('notas_saida')?.value    || null,
            observacoes_saida:  document.getElementById('observacoes_saida')?.value || null,
        };

        const fd = new FormData();
        Object.entries(contrato).forEach(([k, v]) => fd.append(k, v ?? ''));
        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            if (input && input.files && input.files[0]) {
                fd.append(id, input.files[0]);
            }
        });

        // Se for upgrade, envia motivo_upgrade para auditoria
        const selectedOpt = document.querySelector('#veiculo option:checked');
        const isUpgrade = selectedOpt?.dataset?.upgrade === '1';
        if (isUpgrade) {
            const motivo = document.getElementById('motivo_upgrade_input')?.value || '';
            fd.append('motivo_upgrade', motivo);
        }

        const createBtnEl = document.getElementById('btn_criar_contrato');
        const wizBtnEl    = document.getElementById('wizardCreateBtn');
        [createBtnEl, wizBtnEl].forEach(b => {
            if (b) {
                b.disabled = true;
                b._originalHtml = b.innerHTML;
                b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            }
        });

        $.ajax({
            url,
            type: 'POST',
            data: fd,
            headers: { 'X-CSRF-TOKEN': csrf },
            processData: false,
            contentType: false,
            success: function (data) {
                Swal.fire({
                    icon: 'success',
                    title: 'Contrato criado com sucesso!',
                    showConfirmButton: false,
                    timer: 2500,
                    toast: true,
                    position: 'top-right',
                });
                setTimeout(() => {
                    window.location.href = "{{ route('reserva.show', ':id') }}".replace(':id', reserva_id);
                }, 2600);
            },
            error: function (xhr) {
                let title = 'Erro ao criar contrato';
                let html = 'Verifique os dados e tente novamente.';

                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    title = 'Validacao falhou';
                    const msgs = Object.values(xhr.responseJSON.errors).flat();
                    html = `<ul style=\"text-align:left\">${msgs.map(m => `<li>${m}</li>`).join('')}</ul>`;
                } else if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    html = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title,
                    html,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                });
                [createBtnEl, wizBtnEl].forEach(b => { if (b) { b.disabled = false; b.innerHTML = b._originalHtml || b.innerHTML; } });
            }
        });
    }

    /* ========================
       DANOS
    ======================== */
    function rowImagensDano(dano) {
        const imgs = dano.imagens || [];
        return imgs.map(i => `<img src="{{ asset('storage') }}/${i.imagem}" alt="Dano">`).join(' ');
    }

    function obterDanosDetectados() {
        const veiculo_id = $('#veiculo').val();
        const reserva_id = $('#reserva_id').val();

        $.ajax({
            url: "{{ route('dano.obterDanosDetectados') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_detectados');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="damage-empty"><i class="fas fa-shield-alt"></i>Nenhum dano detectado</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    let extra = '';
                    if (dano.correcao_realizada) {
                        extra = `<br><small class="text-success"><i class="fas fa-wrench"></i> Corrigido: ${dano.correcao_descricao ?? ''} (${dano.correcao_data ?? ''})</small>`;
                    }
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}${extra}</td>
                            <td>${rowImagensDano(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    function obterDanosExistentes() {
        const veiculo_id = $('#veiculo').val();
        const reserva_id = $('#reserva_id').val();

        $.ajax({
            url: "{{ route('dano.obterDanosExistentes') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_existentes');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="damage-empty"><i class="fas fa-check-circle"></i>Nenhum dano anterior registrado</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    let extra = '';
                    if (dano.correcao_realizada) {
                        extra = `<br><small class="text-success"><i class="fas fa-wrench"></i> Corrigido: ${dano.correcao_descricao ?? ''} (${dano.correcao_data ?? ''})</small>`;
                    } else {
                        extra = `<br><small class="text-warning"><i class="fas fa-hourglass-half"></i> Correção pendente</small>`;
                    }
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}${extra}</td>
                            <td>${rowImagensDano(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    /* Expor globalmente para o modal_dano_checkin usar */
    window.obterDanosDetectados = obterDanosDetectados;
    window.obterDanosExistentes = obterDanosExistentes;

    function setNomeDano(nome) {
        const el = document.getElementById('informacao_dano');
        if (el) el.innerText = nome;
    }
    window.setNomeDano = setNomeDano;

    /* Init */
    showStep(1);

    /* ========================
       UPGRADE DE CATEGORIA
    ======================== */
    (function initUpgradeCheck() {
        const statusUpgradeUrl = @json(route('reserva.checkin.statusUpgrade', $reserva->id));

        fetch(statusUpgradeUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.tem_disponibilidade_categoria_original) return; // sem upgrade

                const banner = document.getElementById('upgrade-banner');
                const grupoLabel = document.getElementById('upgrade-grupo-original');
                if (banner) {
                    banner.classList.remove('d-none');
                    if (grupoLabel) grupoLabel.textContent = data.grupo_original?.nome ?? 'categoria original';
                }

                // Substitui o select de veiculo por um select de upgrade
                const veiculoSelect = document.getElementById('veiculo');
                if (!veiculoSelect) return;

                const todos = Array.isArray(data.veiculos_periodo) ? data.veiculos_periodo : [];
                if (!todos.length) {
                    veiculoSelect.innerHTML = '<option value="" disabled selected>Nenhum veiculo disponivel no periodo</option>';
                    veiculoSelect.disabled = true;
                    return;
                }

                veiculoSelect.disabled = false;
                veiculoSelect.innerHTML = '<option value="" disabled selected>Selecione um veiculo</option>';

                const gruposMap = new Map();
                todos.forEach(v => {
                    const grupoId = v.grupo?.id ?? 0;
                    if (!gruposMap.has(grupoId)) {
                        gruposMap.set(grupoId, { grupo: v.grupo ?? { id: 0, nome: 'Sem grupo', ordem: 0 }, veiculos: [] });
                    }
                    gruposMap.get(grupoId).veiculos.push(v);
                });

                const gruposOrdenados = Array.from(gruposMap.values()).sort((a, b) => {
                    const oa = Number(a.grupo?.ordem ?? 0);
                    const ob = Number(b.grupo?.ordem ?? 0);
                    return ob - oa; // melhores primeiro
                });

                gruposOrdenados.forEach(item => {
                    const veiculos = item.veiculos ?? [];
                    const existeElegivel = veiculos.some(v => !!v.elegivel_upgrade);
                    const labelPrefix = existeElegivel ? '\u2B06 ' : '\u2B07 ';
                    const labelSuffix = existeElegivel ? '(upgrade)' : '(downgrade bloqueado)';

                    const optGroup = document.createElement('optgroup');
                    optGroup.label = `${labelPrefix}${item.grupo?.nome ?? 'Grupo'} ${labelSuffix}`;

                    veiculos.forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = (v.nome ?? 'Veiculo') + (v.placa ? ' - ' + v.placa : '');
                        if (v.elegivel_upgrade) {
                            opt.dataset.upgrade = '1';
                        } else {
                            opt.disabled = true;
                            opt.textContent += ' (inferior)';
                        }
                        optGroup.appendChild(opt);
                    });

                    veiculoSelect.appendChild(optGroup);
                });

                return;

                veiculoSelect.innerHTML = '<option value="" disabled selected>Selecione veiculo de upgrade</option>';
                (data.grupos_upgrade ?? []).forEach(item => {
                    const optGroup = document.createElement('optgroup');
                    optGroup.label = '\u2B06 ' + item.grupo.nome + ' (upgrade)';
                    (item.veiculos ?? []).forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = v.nome + (v.placa ? ' — ' + v.placa : '');
                        opt.dataset.upgrade = '1';
                        optGroup.appendChild(opt);
                    });
                    veiculoSelect.appendChild(optGroup);
                });
            })
            .catch(() => { /* silencioso — funciona sem upgrade */ });
    })();

})();
</script>
@stop
