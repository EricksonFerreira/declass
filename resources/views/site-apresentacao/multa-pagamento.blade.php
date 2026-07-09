@php
    $corPrimaria = $empresa->cor_primaria ?: '#1f4f7a';
    $corSecundaria = $empresa->cor_secundaria ?: '#14324d';
    $moeda = strtoupper((string) ($moedaPadrao ?? 'BRL'));
    $simbolo = $moeda === 'USD' ? '$' : ($moeda === 'EUR' ? 'EUR' : 'R$');
    $pixData = session('pix');
    $primeiroMetodo = $metodosPagamento[0]['id'] ?? '';
    $valorMultaFormatado = number_format((float) ($valorMulta ?? 0), 2, ',', '.');
    $statusMulta = strtolower((string) ($multa->status_multa ?? 'pendente'));
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento da multa | {{ $empresa->nome }}</title>
    <style>
        :root { --p: {{ $corPrimaria }}; --s: {{ $corSecundaria }}; --line: #dbe4ee; --ink: #0f172a; --muted: #64748b; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: var(--ink); background: #f4f8fc; }
        .container { width: min(1060px, calc(100% - 24px)); margin: 0 auto; }
        .top { background: #fff; border-bottom: 1px solid var(--line); }
        .top .inner { min-height: 70px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; text-decoration: none; color: inherit; }
        .brand img { width: 42px; height: 42px; border-radius: 10px; object-fit: contain; background: var(--p); border: 1px solid #e2e8f0; padding: 4px; }
        .back { text-decoration: none; border: 1px solid var(--line); border-radius: 999px; padding: 10px 14px; color: #334155; font-size: 13px; font-weight: 700; background: #fff; }
        .layout { display: grid; grid-template-columns: 1fr 320px; gap: 14px; padding: 16px 0 24px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 10px 22px rgba(15, 23, 42, .06); }
        .head { padding: 18px; color: #fff; background: linear-gradient(120deg, var(--p), var(--s)); border-radius: 14px 14px 0 0; }
        .head h1 { margin: 0 0 6px; font-size: 22px; }
        .head p { margin: 0; font-size: 13px; opacity: .92; }
        .steps { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; padding: 12px 18px 0; }
        .step-pill { border: 1px solid var(--line); background: #f8fbff; border-radius: 10px; min-height: 46px; display: flex; align-items: center; gap: 8px; padding: 8px; font-size: 12px; font-weight: 700; color: #334155; }
        .step-pill .num { width: 22px; height: 22px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; background: #dbeafe; color: #1d4ed8; }
        .step-pill.active { border-color: var(--p); background: #fff; }
        .step-pill.active .num { background: var(--p); color: #fff; }
        .body { padding: 16px 18px; }
        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px; }
        label { font-size: 12px; color: #334155; font-weight: 700; }
        select { min-height: 40px; border: 1px solid var(--line); border-radius: 9px; padding: 8px 10px; font-size: 14px; background: #f8fbff; color: var(--ink); }
        select:focus { outline: none; border-color: var(--p); box-shadow: 0 0 0 2px rgba(37, 99, 235, .12); background: #fff; }
        .pay-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .option { border: 1px solid var(--line); border-radius: 999px; padding: 8px 12px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; background: #fff; }
        .option input { accent-color: var(--p); }
        .chip { padding: 3px 8px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; }
        .stripe-box { border: 1px solid var(--line); border-radius: 10px; padding: 10px; background: #f8fbff; }
        .stripe-element { padding: 10px; border: 1px solid var(--line); border-radius: 10px; background: #fff; }
        .btn { min-height: 40px; border: 0; border-radius: 999px; padding: 9px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: var(--p); color: #fff; }
        .btn-secondary { border: 1px solid var(--line); background: #fff; color: #334155; }
        .alert { border-radius: 10px; padding: 10px 12px; font-size: 14px; margin-bottom: 10px; }
        .ok { border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
        .err { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
        .summary { padding: 14px; position: sticky; top: 85px; }
        .summary h3 { margin: 0 0 10px; font-size: 16px; }
        .sum { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; padding: 7px 0; border-bottom: 1px dashed var(--line); }
        .sum.total { border-bottom: 0; font-weight: 800; font-size: 16px; margin-top: 6px; }
        .detail { border: 1px solid var(--line); border-radius: 12px; background: #f8fbff; padding: 10px 12px; margin-bottom: 10px; font-size: 13px; color: #334155; }
        .detail strong { display: block; font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); display: none; align-items: center; justify-content: center; padding: 16px; z-index: 50; }
        .modal { width: min(520px, 100%); background: #fff; border-radius: 16px; border: 1px solid var(--line); box-shadow: 0 24px 60px rgba(15, 23, 42, .3); }
        .modal-head { padding: 14px 16px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
        .modal-body { padding: 16px; }
        .modal-actions { padding: 0 16px 16px; display: flex; gap: 8px; justify-content: flex-end; }
        .toast-wrap { position: fixed; top: 16px; right: 16px; display: grid; gap: 8px; z-index: 60; }
        .toast { min-width: 260px; max-width: 360px; border-radius: 12px; padding: 12px 14px; font-size: 13px; font-weight: 700; box-shadow: 0 18px 30px rgba(15, 23, 42, .18); border: 1px solid var(--line); background: #fff; }
        .toast.ok { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
        .toast.err { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        @media (max-width: 980px) { .layout { grid-template-columns: 1fr; } .summary { position: static; } }
        @media (max-width: 740px) { .steps { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="toast-wrap" id="toastWrap"></div>
    <header class="top">
        <div class="container inner">
            <a class="brand" href="{{ route('site.apresentacao') }}">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}">
                @endif
                <span>{{ $empresa->nome }}</span>
            </a>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="back" href="{{ $urlVoltarPortal }}">Voltar ao portal</a>
            </div>
        </div>
    </header>

    <main class="container layout">
        <section class="card">
            <div class="head">
                <h1>Pagamento da multa #{{ $multa->id }}</h1>
                <p>Finalize o pagamento da multa para manter sua conta regularizada.</p>
            </div>

            <div class="steps">
                <div class="step-pill"><span class="num">1</span><span>Multa</span></div>
                <div class="step-pill active"><span class="num">2</span><span>Pagamento</span></div>
            </div>

            <div class="body">
                @if(session('success'))
                    <div class="alert ok">{{ session('success') }}</div>
                @endif
                @if(session('warning'))
                    <div class="alert" style="border:1px solid #fde68a;background:#fffbeb;color:#92400e;">
                        {{ session('warning') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert err">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form id="formPagamento" method="POST" action="{{ $urlProcessar }}">
                    @csrf
                    <input type="hidden" name="payment_method" id="payment_method_value" value="{{ $primeiroMetodo }}">
                    <input type="hidden" name="salvar_metodo" id="salvar_metodo" value="S">
                    <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                    <input type="hidden" name="token" id="token" value="">
                    <input type="hidden" name="installments" id="installments" value="1">
                    <input type="hidden" name="payment_type" id="payment_type" value="">

                    <div class="detail">
                        <strong>Valor da multa</strong>
                        <div style="font-size:18px;font-weight:800;">{{ $simbolo }} {{ $valorMultaFormatado }}</div>
                        <div style="font-size:12px;color:var(--muted);">Moeda: {{ $moeda }}</div>
                    </div>

                    @if(($gatewayPadrao ?? 'stripe') === 'stripe')
                        <div class="field">
                            <label for="payment_method_select">Metodos salvos</label>
                            <select id="payment_method_select" {{ count($metodosPagamento) ? '' : 'disabled' }}>
                                @if(count($metodosPagamento) == 0)
                                    <option value="" disabled selected>Nenhum metodo salvo</option>
                                @else
                                    @foreach($metodosPagamento as $metodo)
                                        <option value="{{ $metodo['id'] }}">
                                            {{ strtoupper($metodo['card']->brand) }} - **** {{ substr($metodo['card']->last4, -4) }}
                                            ({{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small style="font-size:12px;color:var(--muted);">Use um metodo salvo ou adicione um novo cartao no modal.</small>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <button type="button" class="btn btn-secondary" id="openMetodoModal" {{ empty($stripePublicKey) ? 'disabled' : '' }}>
                                Adicionar metodo
                            </button>
                            @if(empty($stripePublicKey))
                                <small style="font-size:12px;color:var(--muted);">Chave publica do Stripe nao configurada.</small>
                            @endif
                        </div>
                    @endif

                    @if(($gatewayPadrao ?? '') === 'mercado_livre')
                        <div class="field">
                            <label>Forma de pagamento</label>
                            <div class="pay-options">
                                <label class="option">
                                    <input type="radio" name="forma_pagamento" value="pix" checked>
                                    Pix
                                </label>
                                <span class="chip">Instantaneo</span>
                            </div>
                            <small style="font-size:12px;color:var(--muted);">Ao confirmar, geramos o QR Code Pix para pagamento.</small>
                        </div>
                    @endif

                    @if(!empty($pixData))
                        <div class="detail">
                            <strong>Pix gerado</strong>
                            <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">Escaneie o QR Code abaixo para concluir o pagamento.</div>
                            @if(!empty($pixData['qr_code_base64']))
                                <img src="data:image/png;base64,{{ $pixData['qr_code_base64'] }}" alt="QR Code Pix" style="width:200px;max-width:100%;border-radius:10px;border:1px solid var(--line);background:#fff;padding:8px;">
                            @endif
                            @if(!empty($pixData['qr_code']))
                                <div style="margin-top:8px;font-size:12px;word-break:break-all;border:1px dashed var(--line);padding:8px;border-radius:8px;background:#fff;">
                                    {{ $pixData['qr_code'] }}
                                </div>
                            @endif
                            @if(!empty($pixData['ticket_url']))
                                <div style="margin-top:8px;font-size:12px;color:var(--muted);">Link: {{ $pixData['ticket_url'] }}</div>
                            @endif
                        </div>
                    @endif

                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                        <button type="submit" class="btn btn-primary">Efetuar pagamento</button>
                    </div>
                </form>
            </div>
        </section>

        <aside class="card summary">
            <h3>Resumo</h3>
            <div class="detail">
                <strong>Cliente</strong>
                <div>{{ $multa->reserva?->usuario?->nome ?? $multa->cliente?->nome ?? '-' }}</div>
                <div>{{ $multa->reserva?->usuario?->email ?? $multa->cliente?->email ?? '-' }}</div>
            </div>

            <div class="detail">
                <strong>Multa</strong>
                <div>Nº {{ $multa->id }}</div>
                <div>{{ $multa->descricao ?? 'Sem descrição' }}</div>
            </div>

            <div class="detail">
                <strong>Reserva</strong>
                <div>#{{ $multa->reserva?->id ?? '-' }}</div>
                <div>{{ $multa->reserva?->data_inicio ? \Carbon\Carbon::parse($multa->reserva->data_inicio)->format('d/m/Y H:i') : '-' }}</div>
            </div>

            <div class="detail">
                <strong>Vencimento</strong>
                <div>{{ $multa->data_vencimento?->format('d/m/Y') ?? '-' }}</div>
                <div>{{ $multa->orgao_autuador ?? '-' }}</div>
            </div>

            <div class="detail">
                <strong>Veiculo</strong>
                <div>{{ $multa->veiculo?->placa ?? $multa->placa_momento ?? '-' }}</div>
                <div>{{ $multa->veiculo?->modelo?->nome ?? '-' }}</div>
            </div>

            <div class="sum"><span>Valor original</span><strong>{{ $simbolo }} {{ number_format((float) ($multa->valor_original ?? 0), 2, ',', '.') }}</strong></div>
            <div class="sum"><span>Desconto</span><strong>{{ $simbolo }} {{ number_format((float) ($multa->valor_desconto ?? 0), 2, ',', '.') }}</strong></div>
            <div class="sum total"><span>Total</span><strong>{{ $simbolo }} {{ $valorMultaFormatado }}</strong></div>
            <div class="sum"><span>Status</span><strong>{{ ucfirst((string) ($multa->label_status_multa ?? $multa->status_multa ?? 'pendente')) }}</strong></div>
        </aside>
    </main>

    @if(($gatewayPadrao ?? 'stripe') === 'stripe')
        <div class="modal-backdrop" id="modalMetodo">
            <div class="modal">
                <div class="modal-head">
                    <strong>Adicionar cartao</strong>
                    <button type="button" class="btn btn-secondary" id="closeMetodoModal">Fechar</button>
                </div>
                <div class="modal-body">
                    <div class="stripe-box">
                        <label>Dados do cartao</label>
                        <div id="stripe-card-element" class="stripe-element"></div>
                    </div>
                    @if(empty($stripePublicKey))
                        <div class="alert err" style="margin-top:10px;">
                            Chave publica do Stripe nao configurada para esta empresa.
                        </div>
                    @endif
                    <div id="stripe-error" class="alert err" style="display:none; margin-top:10px;"></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="saveMetodoButton">Salvar cartao</button>
                    {{-- <button type="button" class="btn btn-primary" id="payNoSaveButton">Pagar sem salvar</button> --}}
                </div>
            </div>
        </div>

        <form id="formSalvarMetodo" method="POST" action="{{ $urlAdicionarMetodo }}">
            @csrf
            <input type="hidden" name="stripeToken" id="stripeTokenInput" value="">
        </form>
    @endif

    @if(($gatewayPadrao ?? 'stripe') === 'stripe' && !empty($stripePublicKey))
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            (function () {
                const stripeKey = @json($stripePublicKey);
                if (!stripeKey) {
                    return;
                }

                const stripe = Stripe(stripeKey);
                const elements = stripe.elements();
                const cardElement = elements.create('card', {
                    style: {
                        base: {
                            fontSize: '14px',
                            color: '#0f172a',
                            '::placeholder': { color: '#94a3b8' }
                        }
                    }
                });
                cardElement.mount('#stripe-card-element');

                const modal = document.getElementById('modalMetodo');
                const saveBtn = document.getElementById('saveMetodoButton');
                const payBtn = document.getElementById('payNoSaveButton');
                const errorBox = document.getElementById('stripe-error');
                const formSalvar = document.getElementById('formSalvarMetodo');
                const tokenInput = document.getElementById('stripeTokenInput');
                const pagamentoForm = document.getElementById('formPagamento');
                const paymentValue = document.getElementById('payment_method_value');
                const salvarMetodo = document.getElementById('salvar_metodo');
                const selectMetodo = document.getElementById('payment_method_select');

                const showError = (message) => {
                    if (!errorBox) return;
                    errorBox.style.display = 'block';
                    errorBox.textContent = message;
                };

                const clearError = () => {
                    if (!errorBox) return;
                    errorBox.style.display = 'none';
                    errorBox.textContent = '';
                };

                if (saveBtn) {
                    saveBtn.addEventListener('click', async () => {
                        clearError();
                        saveBtn.disabled = true;
                        try {
                            const result = await stripe.createToken(cardElement);
                            if (result.error) {
                                showError(result.error.message || 'Nao foi possivel validar o cartao.');
                                return;
                            }
                            tokenInput.value = result.token.id;
                            formSalvar.submit();
                        } finally {
                            saveBtn.disabled = false;
                        }
                    });
                }

                if (payBtn) {
                    payBtn.addEventListener('click', async () => {
                        clearError();
                        payBtn.disabled = true;
                        try {
                            const result = await stripe.createPaymentMethod({
                                type: 'card',
                                card: cardElement
                            });
                            if (result.error) {
                                showError(result.error.message || 'Nao foi possivel validar o cartao.');
                                return;
                            }
                            paymentValue.value = result.paymentMethod.id;
                            salvarMetodo.value = 'N';
                            modal.style.display = 'none';
                            pagamentoForm.submit();
                        } finally {
                            payBtn.disabled = false;
                        }
                    });
                }

                if (selectMetodo) {
                    selectMetodo.addEventListener('change', () => {
                        paymentValue.value = selectMetodo.value || '';
                        salvarMetodo.value = 'S';
                    });
                    if (selectMetodo.value) {
                        paymentValue.value = selectMetodo.value;
                    }
                }

                const openBtn = document.getElementById('openMetodoModal');
                const closeBtn = document.getElementById('closeMetodoModal');
                if (openBtn && modal) {
                    openBtn.addEventListener('click', () => {
                        modal.style.display = 'flex';
                        clearError();
                    });
                }
                if (closeBtn && modal) {
                    closeBtn.addEventListener('click', () => {
                        modal.style.display = 'none';
                    });
                }
                if (modal) {
                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) {
                            modal.style.display = 'none';
                        }
                    });
                }
            })();
        </script>
    @endif

    <script>
        (function () {
            const form = document.getElementById('formPagamento');
            const gateway = @json($gatewayPadrao ?? '');
            const paymentValue = document.getElementById('payment_method_value');
            const paymentMethodId = document.getElementById('payment_method_id');
            const paymentType = document.getElementById('payment_type');
            const toastWrap = document.getElementById('toastWrap');
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());

            if (!form) return;

            const showToast = (message, type) => {
                if (!toastWrap || !message) return;
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                toastWrap.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            };

            if (successMessage) showToast(successMessage, 'ok');
            if (Array.isArray(errorMessages)) errorMessages.forEach(msg => showToast(msg, 'err'));

            form.addEventListener('submit', function (event) {
                if (gateway === 'stripe' && !paymentValue.value) {
                    event.preventDefault();
                    showToast('Selecione ou adicione um cartao para continuar.', 'err');
                    return;
                }

                if (gateway === 'mercado_livre') {
                    paymentType.value = 'pix';
                    paymentMethodId.value = 'pix';
                }
            });
        })();
    </script>
</body>
</html>
