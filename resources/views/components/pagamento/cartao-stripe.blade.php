@props([
    'id' => 'cartaoStripe',
    'action',
    'adicionarUrl',
    'stripePublicKey' => null,
    'metodosPagamento' => [],
    'valor' => 0,
    'simbolo' => 'R$',
    'moeda' => 'BRL',
    'titulo' => 'Processar Pagamento',
    'subtitulo' => 'Cartao de credito',
    'descricao' => 'O saldo abaixo sera cobrado no metodo selecionado. Apos confirmar, o sistema registrara a transacao e atualizara o status.',
    'valorReadonly' => true,
    'showInstallments' => true,
    'hiddenInputs' => [],
    'submitLabel' => 'Confirmar e Processar Pagamento',
])

@php
    $uid = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $id);
    $metodos = collect($metodosPagamento ?? []);
    $temMetodos = $metodos->isNotEmpty();
    $valorFloat = (float) $valor;
    $stripeKey = (string) ($stripePublicKey ?? '');
@endphp

<div class="ep-card mb-3 ps-card" data-ps-root="{{ $uid }}">
    <div class="ep-card__header ep-card__header--green">
        <div class="ep-card__icon ep-card__icon--green"><i class="fas fa-credit-card"></i></div>
        <div>
            <div class="ep-card__title">{{ $titulo }}</div>
            <div class="ep-card__sub">
                <strong>{{ $subtitulo }}</strong>
                &nbsp;·&nbsp; Moeda: <strong>{{ $moeda }}</strong>
            </div>
        </div>
    </div>
    <div class="ep-card__body">
        <div class="ep-tip mb-3">
            <i class="fas fa-info-circle text-primary mr-2"></i>
            {{ $descricao }}
        </div>

        <form id="{{ $uid }}-form" action="{{ $action }}" method="POST">
            @csrf
            @foreach($hiddenInputs as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <div class="form-group">
                <label class="ep-form-label"><i class="fas fa-wallet mr-1"></i> Metodo de pagamento</label>
                <div class="d-flex" style="gap:.5rem">
                    <select id="{{ $uid }}-payment-method" class="form-control form-control-sm" name="payment_method" required>
                        @if(!$temMetodos)
                            <option value="" disabled selected>Nenhum metodo cadastrado</option>
                        @else
                            @foreach($metodos as $metodo)
                                <option value="{{ $metodo['id'] }}">
                                    {{ strtoupper($metodo['card']->brand ?? $metodo['brand'] ?? 'CARD') }}
                                    ****{{ $metodo['card']->last4 ?? $metodo['last4'] ?? '----' }}
                                    ({{ $metodo['card']->exp_month ?? $metodo['exp_month'] ?? '--' }}/{{ $metodo['card']->exp_year ?? $metodo['exp_year'] ?? '--' }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <button type="button" id="{{ $uid }}-open-modal" class="btn btn-sm btn-outline-primary" style="white-space:nowrap">
                        <i class="fas fa-plus"></i> Novo
                    </button>
                </div>
                @if(empty($stripeKey))
                    <small class="text-muted">Chave publica do Stripe nao configurada.</small>
                @endif
            </div>

            <div class="form-group">
                <label class="ep-form-label"><i class="fas fa-dollar-sign mr-1"></i> Valor a pagar</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text">{{ $simbolo }}</span></div>
                    @if($valorReadonly)
                        <input type="text" class="form-control form-control-sm" value="{{ number_format($valorFloat, 2, ',', '.') }}" readonly>
                        <input type="hidden" name="valor_pagamento" id="{{ $uid }}-valor" value="{{ number_format($valorFloat, 2, '.', '') }}">
                    @else
                        <input type="number" name="valor_pagamento" id="{{ $uid }}-valor" class="form-control form-control-sm" step="0.01" min="0.01" max="{{ number_format($valorFloat, 2, '.', '') }}" value="{{ number_format($valorFloat, 2, '.', '') }}" required>
                        <div class="input-group-append"><span class="input-group-text text-muted" style="font-size:11px;">max. {{ $simbolo }} {{ number_format($valorFloat, 2, ',', '.') }}</span></div>
                    @endif
                </div>
                <small class="text-muted">Pagamento por cartao de credito vinculado ao sistema.</small>
            </div>

            <div class="ep-total-box ep-total-box--warning mb-3" style="background:#fefce8; border:1px solid #fde047; border-radius:14px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                <div class="ep-total-box__label" style="color:#a16207; font-weight:900; text-transform:uppercase; font-size:.78rem; letter-spacing:.06em;">Saldo pendente</div>
                <div class="ep-total-box__value" style="color:#854d0e; font-weight:900; font-size:1.3rem;">{{ $simbolo }} {{ number_format($valorFloat, 2, ',', '.') }}</div>
            </div>

            @if($showInstallments)
                <div class="form-group">
                    <label class="ep-form-label"><i class="fas fa-calendar-alt mr-1"></i> Parcelamento</label>
                    <select id="{{ $uid }}-installments" name="installments" class="form-control form-control-sm">
                        <option value="1">A vista - {{ $simbolo }} {{ number_format($valorFloat, 2, ',', '.') }}</option>
                        @foreach([2,3,4,5,6,7,8,9,10,11,12] as $parc)
                            <option value="{{ $parc }}">{{ $parc }}x de {{ $simbolo }} {{ number_format($valorFloat / $parc, 2, ',', '.') }} {{ $parc <= 3 ? '- Sem juros' : '- Com juros' }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Ate 3x sem juros. Acima disso, consulte as taxas aplicaveis pela operadora.</small>
                </div>
            @endif

            <span class="ep-pay-btn-wrap" title="{{ $temMetodos ? '' : 'Adicione um metodo de pagamento salvo para habilitar o pagamento.' }}">
                <button type="submit" id="{{ $uid }}-submit" class="btn btn-success btn-block ep-btn-pay" {{ $temMetodos ? '' : 'disabled aria-disabled=true' }}>
                    <i class="fas fa-lock mr-2"></i> {{ $submitLabel }}
                </button>
            </span>
        </form>
    </div>
</div>

<div class="modal fade" id="{{ $uid }}-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header" style="background:linear-gradient(120deg,#f0f4ff,#e8f0fe)">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-credit-card mr-2 text-primary"></i> Adicionar Cartao
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Os dados sao transmitidos com seguranca diretamente ao Stripe e nao ficam armazenados em nossos servidores.
                </p>
                @if(empty($stripeKey))
                    <div class="alert alert-warning small mb-3">
                        Configure a chave publica do Stripe para salvar um novo cartao.
                    </div>
                @endif
                <div class="form-group mb-0">
                    <label class="text-xs font-weight-bold">Dados do Cartao</label>
                    <div id="{{ $uid }}-card-element" class="form-control" style="padding-top:10px;"></div>
                    <div id="{{ $uid }}-card-errors" class="text-danger text-xs mt-1" role="alert"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="{{ $uid }}-save">
                    <i class="fas fa-save mr-1"></i> Salvar Cartao
                </button>
            </div>
        </div>
    </div>
</div>

@once
    @push('js')
        <script src="https://js.stripe.com/v3/"></script>
    @endpush
@endonce

@push('js')
<script>
(function () {
    const uid = @json($uid);
    const stripeKey = @json($stripeKey);
    const adicionarUrl = @json($adicionarUrl);
    const csrf = @json(csrf_token());
    const form = document.getElementById(uid + '-form');
    const submitBtn = document.getElementById(uid + '-submit');
    const paySelect = document.getElementById(uid + '-payment-method');
    const openModal = document.getElementById(uid + '-open-modal');
    const saveBtn = document.getElementById(uid + '-save');
    const cardErrors = document.getElementById(uid + '-card-errors');
    const valorInput = document.getElementById(uid + '-valor');
    const installments = document.getElementById(uid + '-installments');
    const modalEl = document.getElementById(uid + '-modal');
    const simbolo = @json($simbolo);

    function releaseModalFocus() {
        if (modalEl && document.activeElement && modalEl.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    }

    function hasPaymentMethod() {
        return !!(paySelect && paySelect.value);
    }

    function refreshButton() {
        if (!submitBtn) return;
        const enabled = hasPaymentMethod();
        submitBtn.disabled = !enabled;
        submitBtn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
    }

    function refreshInstallments() {
        if (!installments || !valorInput) return;
        const valor = parseFloat(String(valorInput.value || '0').replace(',', '.')) || 0;
        installments.innerHTML = '';
        for (let parc = 1; parc <= 12; parc++) {
            const opt = document.createElement('option');
            opt.value = String(parc);
            opt.textContent = parc === 1
                ? 'A vista - ' + simbolo + ' ' + valor.toFixed(2).replace('.', ',')
                : parc + 'x de ' + simbolo + ' ' + (valor / parc).toFixed(2).replace('.', ',') + (parc <= 3 ? ' - Sem juros' : ' - Com juros');
            installments.appendChild(opt);
        }
    }

    if (paySelect) paySelect.addEventListener('change', refreshButton);
    if (valorInput) {
        valorInput.addEventListener('input', function () {
            var val = parseFloat(this.value) || 0;
            var maxVal = parseFloat(@json($valorFloat)) || 0;
            if (maxVal > 0 && val > maxVal) {
                this.value = maxVal.toFixed(2);
                alert('O valor a pagar não pode exceder o saldo pendente de ' + @json($simbolo) + ' ' + maxVal.toFixed(2).replace('.', ','));
            }
            refreshInstallments();
        });
    }
    refreshButton();
    refreshInstallments();

    if (form && submitBtn) {
        form.addEventListener('submit', function (e) {
            if (valorInput) {
                var val = parseFloat(valorInput.value) || 0;
                var maxVal = parseFloat(@json($valorFloat)) || 0;
                if (maxVal > 0 && val > maxVal + 0.001) {
                    e.preventDefault();
                    valorInput.value = maxVal.toFixed(2);
                    alert('O valor a pagar não pode exceder o saldo pendente de ' + @json($simbolo) + ' ' + maxVal.toFixed(2).replace('.', ','));
                    return false;
                }
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status"></span> Processando...';
        });
    }

    if (openModal) {
        openModal.addEventListener('click', function () {
            $('#' + uid + '-modal').modal('show');
        });
    }

    if (modalEl) {
        $('#' + uid + '-modal').on('hide.bs.modal', releaseModalFocus);
        $('#' + uid + '-modal').on('hidden.bs.modal', function () {
            if (openModal && document.body.contains(openModal)) {
                openModal.focus({ preventScroll: true });
            }
        });
    }

    if (!stripeKey || typeof Stripe === 'undefined') {
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.title = 'Configure a chave publica do Stripe.';
        }
        return;
    }

    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();
    const cardElem = elements.create('card');
    let mounted = false;

    $('#' + uid + '-modal').on('shown.bs.modal', function () {
        if (!mounted) {
            cardElem.mount('#' + uid + '-card-element');
            mounted = true;
        }
    });

    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            const original = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Salvando...';
            if (cardErrors) cardErrors.textContent = '';

            const result = await stripe.createToken(cardElem);
            if (result.error) {
                if (cardErrors) cardErrors.textContent = result.error.message;
                saveBtn.disabled = false;
                saveBtn.innerHTML = original;
                return;
            }

            try {
                const resp = await fetch(adicionarUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ stripeToken: result.token.id })
                });
                const data = await resp.json();
                if (!resp.ok) throw new Error(data.message || 'Erro ao adicionar metodo.');

                if (paySelect && Array.isArray(data.metodos)) {
                    paySelect.innerHTML = '';
                    data.metodos.forEach(function (m) {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = (m.brand || '').toUpperCase() + ' ****' + m.last4 + ' (' + m.exp_month + '/' + m.exp_year + ')';
                        paySelect.appendChild(opt);
                    });
                    if (paySelect.options.length) {
                        paySelect.value = paySelect.options[paySelect.options.length - 1].value;
                    }
                    refreshButton();
                }
                releaseModalFocus();
                $('#' + uid + '-modal').modal('hide');
            } catch (err) {
                if (cardErrors) cardErrors.textContent = err.message;
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = original;
            }
        });
    }
})();
</script>
@endpush
