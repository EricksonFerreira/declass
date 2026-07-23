@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

@php
    $ctx = $contextoAssinatura ?? [];
    $empresa = $ctx['empresa'] ?? $reserva->empresa ?? null;
    $usuarioAssinatura = $ctx['usuario'] ?? $reserva->usuario ?? $reserva->user ?? null;
    $codigoAssinatura = $ctx['codigo'] ?? ($reserva->codigoReserva?->codigo ?? $reserva->id);
    $dataInicioAssinatura = $ctx['data_inicio'] ?? $reserva->data_inicio ?? null;
    $dataFimAssinatura = $ctx['data_fim'] ?? $reserva->data_fim ?? null;
    $diasAssinatura = $ctx['quantidade_dias']
        ?? ($dataInicioAssinatura && $dataFimAssinatura ? \Carbon\Carbon::parse($dataInicioAssinatura)->diffInDays(\Carbon\Carbon::parse($dataFimAssinatura)) : 1);
    if ($diasAssinatura <= 0) $diasAssinatura = 1;
    $tarifaLabelAssinatura = $ctx['tarifa_label'] ?? 'Tarifa diaria';
    $tarifaBaseAssinatura = (float) ($ctx['tarifa_valor'] ?? $reserva->tarifa_base ?? 0);
    $taxaEntregaAssinatura = (float) ($ctx['taxa_entrega'] ?? $reserva->taxa_entrega ?? 0);
    $taxaColetaAssinatura = (float) ($ctx['taxa_coleta'] ?? $reserva->taxa_coleta ?? 0);
    $isMap = ($ctx['tipo'] ?? 'RES') === 'MAP';
    $subtotalAssinatura = $isMap
        ? $tarifaBaseAssinatura
        : $tarifaBaseAssinatura * (int) $diasAssinatura;
    $valorTotalAssinatura = (float) ($ctx['valor_total'] ?? ($subtotalAssinatura + $taxaEntregaAssinatura + $taxaColetaAssinatura));

    $co = $reserva->contratoCheckout ?? null;
    $veic = $co?->veiculo ?? $reserva->veiculo ?? ($ctx['veiculo'] ?? null);
    $marcaNome = $co?->marca?->nome ?? $veic?->marca?->nome ?? '-';
    $modeloNome = $co?->modelo?->nome ?? $veic?->modelo?->nome ?? '-';
    $placa = $veic?->placa_licenca ?? '-';
    $grupoNome = $veic?->grupoFrota?->nome ?? $ctx['plano_nome'] ?? '-';
    $cor = $veic?->cor ?? '-';
    $ano = $veic?->ano_fabricacao ?? ($veic?->modelo?->ano_modelo ?? '-');
    $km = $co?->quilometragem_devolucao ?? $reserva->quilometragem_final ?? $veic?->quilometragem ?? 0;
    $comb = $co?->nivel_combustivel_devolucao ?? $reserva->nivel_combustivel_final ?? '-';
@endphp

<div class="assinatura-page">

    {{-- Hero --}}
    <div class="assinatura-hero" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
        <div class="assinatura-hero__dot-bg"></div>
        <div class="assinatura-hero__stripe" style="background: #ef4444;"></div>
        <div class="assinatura-hero__inner">
            <div class="assinatura-hero__icon" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div>
                <div class="assinatura-hero__eyebrow">Termo de Devolução</div>
                <h1 class="assinatura-hero__title">Assinatura de Check-out</h1>
                <p class="assinatura-hero__sub">Reserva {{ $codigoAssinatura }}</p>
            </div>
        </div>
    </div>

    {{-- Body Card --}}
    <div class="assinatura-card">
        <div class="row">
            {{-- Resumo da Reserva & Veículo --}}
            <div class="col-lg-5">
                <div class="assinatura-section-title">
                    <i class="fas fa-info-circle"></i> Resumo da Locação & Veículo
                </div>

                {{-- Veículo --}}
                <div class="veiculo-badge mb-3">
                    <i class="fas fa-car mr-2 text-primary"></i>
                    <div>
                        <strong>{{ $marcaNome }} {{ $modeloNome }}</strong>
                        <span>Placa: {{ $placa }} · Cor: {{ $cor }}</span>
                    </div>
                </div>

                <div class="rs-info-summary">
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-tachometer-alt"></i> KM Final</span>
                        <span class="rs-info-val">{{ number_format($km, 0, ',', '.') }} km</span>
                    </div>
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-gas-pump"></i> Combustível Final</span>
                        <span class="rs-info-val">{{ $comb }}</span>
                    </div>
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-user"></i> Cliente</span>
                        <span class="rs-info-val font-weight-bold">{{ $usuarioAssinatura?->nome ?? $usuarioAssinatura?->name ?? '—' }}</span>
                    </div>
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-calendar-alt"></i> Devolvido Em</span>
                        <span class="rs-info-val">{{ $co?->data_devolucao ? \Carbon\Carbon::parse($co->data_devolucao)->format('d/m/Y') : '' }} {{ $co?->hora_devolucao ? substr($co->hora_devolucao, 0, 5) : '' }}</span>
                    </div>
                </div>
            </div>

            {{-- Quadro de Assinatura / Upload --}}
            <div class="col-lg-7 border-left border-light">
                {{-- Tabs de modo --}}
                <form id="form-assinatura-checkout" enctype="multipart/form-data">
                    <div class="assinatura-tabs mb-4">
                        <button type="button" class="assinatura-tab active" data-tab="digital">
                            <i class="fas fa-signature"></i> Assinar Digitalmente
                        </button>
                        <button type="button" class="assinatura-tab" data-tab="upload">
                            <i class="fas fa-file-upload"></i> Anexar Contrato em PDF
                        </button>
                    </div>

                    {{-- Tab: Assinatura Digital --}}
                    <div id="tab-digital" class="assinatura-tab-content active">
                        <div class="assinatura-section-title mt-4 mt-lg-0">
                            <i class="fas fa-pen-nib"></i> Assinatura Digital do Cliente
                        </div>
                        <p class="text-muted small">
                            Ao assinar no quadro abaixo, você confirma a vistoria de check-out, a quilometragem final e o nível de combustível indicados ao lado.
                        </p>

                        <div class="assinatura-pad-container mb-3">
                            <canvas id="signature-pad" class="assinatura-pad-canvas"></canvas>
                            <div class="assinatura-pad-hint">
                                <i class="fas fa-pencil-alt mb-2"></i>
                                <span>Desenhe sua assinatura digital aqui</span>
                            </div>
                        </div>

                        <div class="assinatura-actions">
                            <button type="button" class="assinatura-btn assinatura-btn--secondary" id="btn_limpar">
                                <i class="fas fa-eraser mr-1"></i> Limpar
                            </button>
                            <button type="button" class="assinatura-btn assinatura-btn--primary" id="btn_salvar" style="background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 6px 16px rgba(220,38,38,0.30);">
                                <i class="fas fa-check mr-1"></i> Confirmar & Salvar
                            </button>
                        </div>
                    </div>

                    {{-- Tab: Upload PDF --}}
                    <div id="tab-upload" class="assinatura-tab-content" style="display:none;">
                        <div class="assinatura-section-title mt-4 mt-lg-0">
                            <i class="fas fa-cloud-upload-alt"></i> Enviar Arquivo Contrato Assinado
                        </div>
                        <p class="text-muted small">
                            Anexe o termo de devolução/checkout assinado em formato PDF.
                        </p>

                        <div class="assinatura-upload-area" id="upload-drop-zone">
                            <input type="file" id="contrato_pdf" accept=".pdf,application/pdf" style="display:none;">
                            <div class="assinatura-upload-placeholder" id="upload-placeholder">
                                <i class="fas fa-file-pdf"></i>
                                <p>Arraste o arquivo PDF aqui ou <a href="#" id="upload-browse-link">clique para selecionar</a></p>
                                <span class="muted">Tamanho máximo: 10MB</span>
                            </div>
                            <div class="assinatura-upload-preview" id="upload-preview" style="display:none;">
                                <i class="fas fa-file-pdf"></i>
                                <div class="assinatura-upload-file-info">
                                    <strong id="upload-file-name"></strong>
                                    <span id="upload-file-size"></span>
                                </div>
                                <button type="button" id="upload-remove" class="assinatura-btn assinatura-btn--secondary" style="padding:6px 12px;font-size:.78rem; max-width: 100px;">
                                    <i class="fas fa-times"></i> Remover
                                </button>
                            </div>
                        </div>

                        <div class="assinatura-actions">
                            <button type="button" id="btn_upload_salvar" class="assinatura-btn assinatura-btn--primary" style="background: linear-gradient(135deg, #2563eb, #3b82f6);" disabled>
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Enviar Contrato Assinado
                            </button>
                        </div>
                    </div>
                </form>

                <div id="assinatura_status" class="assinatura-status" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos Premium */
    .assinatura-page {
        margin: 20px auto;
        max-width: 1040px;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    .assinatura-hero {
        position: relative;
        border-radius: 20px 20px 0 0;
        padding: 32px;
        color: #fff;
        overflow: hidden;
    }
    .assinatura-hero__dot-bg {
        position: absolute;
        inset: 0;
        opacity: 0.07;
        background-image: radial-gradient(#fff 1px, transparent 1px);
        background-size: 16px 16px;
    }
    .assinatura-hero__stripe {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
    }
    .assinatura-hero__inner {
        position: relative;
        display: flex;
        align-items: center;
        gap: 20px;
        z-index: 10;
    }
    .assinatura-hero__icon {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .assinatura-hero__eyebrow {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.7;
    }
    .assinatura-hero__title {
        font-size: 1.6rem;
        font-weight: 800;
        margin: 2px 0;
        letter-spacing: -0.5px;
    }
    .assinatura-hero__sub {
        font-size: 0.88rem;
        opacity: 0.8;
        margin: 0;
    }

    /* Card */
    .assinatura-card {
        background: #fff;
        border-radius: 0 0 20px 20px;
        padding: 32px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05);
    }
    .assinatura-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .assinatura-section-title i { color: #64748b; }

    /* Tabs */
    .assinatura-tabs {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 1px;
    }
    .assinatura-tab {
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 16px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .assinatura-tab:hover {
        color: #0f172a;
    }
    .assinatura-tab.active {
        color: #ef4444;
        border-bottom-color: #ef4444;
    }

    /* Veiculo */
    .veiculo-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
    }
    .veiculo-badge i { font-size: 1.4rem; }
    .veiculo-badge strong { display: block; color: #0f172a; font-size: 0.95rem; }
    .veiculo-badge span { font-size: 0.78rem; color: #64748b; }

    /* Info Summary */
    .rs-info-summary {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .rs-info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px dashed #f1f5f9;
    }
    .rs-info-label { font-size: 0.85rem; color: #64748b; }
    .rs-info-val { font-size: 0.88rem; color: #1e293b; font-weight: 600; }

    /* Pad container */
    .assinatura-pad-container {
        position: relative;
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 16px;
        overflow: hidden;
        height: 250px;
    }
    .assinatura-pad-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        cursor: crosshair;
        touch-action: none;
        z-index: 5;
    }
    .assinatura-pad-hint {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        z-index: 2;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .assinatura-pad-hint i { font-size: 1.8rem; }
    .assinatura-pad-hint span { font-size: 0.85rem; font-weight: 600; }

    /* Upload area */
    .assinatura-upload-area {
        border: 2px dashed #c7d2fe;
        border-radius: 16px;
        background: #fafbff;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.2s;
        margin-bottom: 16px;
        cursor: pointer;
    }
    .assinatura-upload-area:hover,
    .assinatura-upload-area.dragover {
        border-color: #3b82f6;
        border-style: solid;
        background: #eff6ff;
    }
    .assinatura-upload-placeholder i {
        font-size: 2.5rem;
        color: #c7d2fe;
        margin-bottom: 10px;
    }
    .assinatura-upload-placeholder p {
        font-size: 0.88rem;
        color: #475569;
        margin: 0 0 4px;
    }
    .assinatura-upload-placeholder p a {
        color: #2563eb;
        font-weight: 600;
        text-decoration: underline;
    }
    .assinatura-upload-placeholder .muted {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .assinatura-upload-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        text-align: left;
    }
    .assinatura-upload-preview > i {
        font-size: 2rem;
        color: #dc2626;
    }
    .assinatura-upload-file-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .assinatura-upload-file-info strong {
        font-size: 0.88rem;
        color: #0c1428;
    }
    .assinatura-upload-file-info span {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Actions */
    .assinatura-actions {
        display: flex;
        gap: 12px;
    }
    .assinatura-btn {
        flex: 1;
        border: none;
        border-radius: 12px;
        padding: 12px 20px;
        font-size: 0.88rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .assinatura-btn--secondary {
        background: #f1f5f9;
        color: #475569;
    }
    .assinatura-btn--secondary:hover { background: #e2e8f0; }
    .assinatura-btn--primary {
        color: #fff;
    }
    .assinatura-btn--primary:hover { opacity: 0.92; transform: translateY(-1px); }
    .assinatura-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* Status */
    .assinatura-status {
        margin-top: 14px;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .assinatura-status.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .assinatura-status.error   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

    @media (max-width: 600px) {
        .assinatura-hero { border-radius: 18px 18px 0 0; padding: 20px; }
        .assinatura-card { padding: 18px; border-radius: 0 0 18px 18px; }
        .assinatura-hero__title { font-size: 1.2rem; }
        .assinatura-actions { flex-direction: column; }
        .assinatura-btn { justify-content: center; }
        #signature-pad { height: 180px; }
    }
</style>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {
    // ─── Tabs ───
    const tabs = document.querySelectorAll('.assinatura-tab');
    const tabContents = document.querySelectorAll('.assinatura-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => { c.classList.remove('active'); c.style.display = 'none'; });
            tab.classList.add('active');
            const target = document.getElementById('tab-' + tab.dataset.tab);
            if (target) { target.classList.add('active'); target.style.display = 'block'; }
        });
    });

    // ─── Signature Pad ───
    const canvas = document.getElementById('signature-pad');
    const hint   = document.querySelector('.assinatura-pad-hint');

    function resizeCanvas() {
        const ratio  = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    const signaturePad = new SignaturePad(canvas, {
        minWidth: 1.5,
        maxWidth: 3,
        penColor: '#0c1428',
    });

    signaturePad.addEventListener('beginStroke', () => {
        if (hint) hint.style.opacity = '0';
    });

    document.getElementById('btn_limpar').addEventListener('click', () => {
        signaturePad.clear();
        if (hint) hint.style.opacity = '1';
    });

    document.getElementById('btn_salvar').addEventListener('click', () => {
        if (signaturePad.isEmpty()) {
            showStatus('Por favor, desenhe sua assinatura antes de confirmar.', 'error');
            return;
        }

        const btnSalvar = document.getElementById('btn_salvar');
        const originalHtml = btnSalvar.innerHTML;
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        const dataURL     = signaturePad.toDataURL();
        const veiculo_id  = "{{ $co?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}";
        const contrato_id = "{{ $co?->id ?? 0 }}";

        $.ajax({
            url: "{{ route('reserva.salvarAssinaturaContratoCheckout', ['codigo' => $codigoAssinatura]) }}",
            method: 'POST',
            data: {
                assinatura: dataURL,
                veiculo_id,
                contrato_id,
                modo: 'digital',
                _token: '{{ csrf_token() }}',
            },
            success: function (response) {
                showStatus('<i class="fas fa-check-circle"></i> Assinatura salva com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = @json($redirectAfterSignUrl ?? route('reserva.show', ['reserva' => $codigoAssinatura]));
                }, 1600);
            },
            error: function (xhr) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalHtml;
                const msg = xhr.responseJSON?.message || 'Erro ao salvar assinatura. Tente novamente.';
                showStatus('<i class="fas fa-exclamation-triangle"></i> ' + msg, 'error');
            },
        });
    });

    // ─── File Upload ───
    const fileInput = document.getElementById('contrato_pdf');
    const dropZone = document.getElementById('upload-drop-zone');
    const placeholder = document.getElementById('upload-placeholder');
    const preview = document.getElementById('upload-preview');
    const fileName = document.getElementById('upload-file-name');
    const fileSize = document.getElementById('upload-file-size');
    const removeBtn = document.getElementById('upload-remove');
    const btnUpload = document.getElementById('btn_upload_salvar');
    const browseLink = document.getElementById('upload-browse-link');
    let selectedFile = null;

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function setFile(file) {
        if (!file || file.type !== 'application/pdf') {
            showStatus('<i class="fas fa-exclamation-triangle"></i> Apenas arquivos PDF são aceitos.', 'error');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showStatus('<i class="fas fa-exclamation-triangle"></i> O arquivo excede o tamanho máximo de 10MB.', 'error');
            return;
        }
        selectedFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        placeholder.style.display = 'none';
        preview.style.display = 'flex';
        btnUpload.disabled = false;
    }

    function clearFile() {
        selectedFile = null;
        fileInput.value = '';
        placeholder.style.display = '';
        preview.style.display = 'none';
        btnUpload.disabled = true;
    }

    browseLink.addEventListener('click', (e) => { e.preventDefault(); fileInput.click(); });
    dropZone.addEventListener('click', (e) => {
        if (e.target === removeBtn || removeBtn.contains(e.target)) return;
        if (!selectedFile) fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) setFile(e.target.files[0]);
    });

    removeBtn.addEventListener('click', (e) => { e.stopPropagation(); clearFile(); });

    // Drag and drop
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('dragover'); });
    });
    dropZone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files.length > 0) setFile(e.dataTransfer.files[0]);
    });

    btnUpload.addEventListener('click', () => {
        if (!selectedFile) {
            showStatus('<i class="fas fa-exclamation-triangle"></i> Selecione um arquivo PDF.', 'error');
            return;
        }

        const originalHtml = btnUpload.innerHTML;
        btnUpload.disabled = true;
        btnUpload.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        const formData = new FormData();
        formData.append('contrato_pdf', selectedFile);
        formData.append('veiculo_id', "{{ $co?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}");
        formData.append('contrato_id', "{{ $co?->id ?? 0 }}");
        formData.append('modo', 'upload');
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: "{{ route('reserva.salvarAssinaturaContratoCheckout', ['codigo' => $codigoAssinatura]) }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                showStatus('<i class="fas fa-check-circle"></i> Contrato assinado enviado com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = @json($redirectAfterSignUrl ?? route('reserva.show', ['reserva' => $codigoAssinatura]));
                }, 1600);
            },
            error: function (xhr) {
                btnUpload.disabled = false;
                btnUpload.innerHTML = originalHtml;
                const msg = xhr.responseJSON?.message || 'Erro ao enviar o contrato. Tente novamente.';
                showStatus('<i class="fas fa-exclamation-triangle"></i> ' + msg, 'error');
            },
        });
    });

    // ─── Status ───
    function showStatus(html, type) {
        const el = document.getElementById('assinatura_status');
        el.innerHTML = html;
        el.className = 'assinatura-status ' + type;
        el.style.display = 'flex';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
})();
</script>
@endsection
