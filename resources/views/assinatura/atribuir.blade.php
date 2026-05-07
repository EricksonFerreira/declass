@extends('adminlte::page')

@section('title', 'Atribuir Plano')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link mr-2"></i> Atribuir Plano a Empresa</h3>
                </div>
                <form id="formAtribuir" action="{{ route('assinatura.atribuir') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Empresa <span class="text-danger">*</span></label>
                                    <select name="empresa_id" id="empresa_id" class="form-control" required>
                                        <option value="">Selecione a empresa...</option>
                                        @foreach ($empresas as $emp)
                                            <option value="{{ $emp->id }}" {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Plano <span class="text-danger">*</span></label>
                                    <select name="plano_id" id="plano_id" class="form-control" required>
                                        <option value="">Selecione o plano...</option>
                                        @foreach ($planos as $p)
                                            <option value="{{ $p->id }}" data-mensal="{{ $p->valor_mensal }}" data-anual="{{ $p->valor_anual }}">
                                                {{ $p->nome }} — R$ {{ number_format($p->valor_mensal, 2, ',', '.') }}/mês
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Periodicidade <span class="text-danger">*</span></label>
                                    <select name="periodicidade" id="periodicidade" class="form-control" required>
                                        <option value="mensal">Mensal</option>
                                        <option value="anual">Anual (com desconto)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Valor a cobrar</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="text" id="valorExibido" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Dia de Vencimento</label>
                                    <input type="number" name="dia_vencimento" class="form-control" min="1" max="28" placeholder="Ex.: 10">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Método de Cobrança <span class="text-danger">*</span></label>
                                    <select name="metodo_cobranca" id="metodo_cobranca" class="form-control" required>
                                        <option value="dinheiro">Dinheiro (pagamento manual)</option>
                                        <option value="pix">PIX</option>
                                        <option value="cartao">Cartão de Crédito</option>
                                        <option value="boleto">Boleto Bancário</option>
                                        <option value="automatico">Automático (débito)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Data de Início <span class="text-danger">*</span></label>
                                    <input type="date" name="data_inicio" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Card Section (shown only when "cartao" is selected) --}}
                        <div id="cardSection" class="row d-none">
                            <div class="col-12">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h5 class="card-title"><i class="fas fa-credit-card mr-2"></i> Dados do Cartão</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="cardErrors" class="alert alert-danger d-none"></div>
                                        <div id="cardElement" class="form-control" style="padding:10px;min-height:40px;"></div>
                                        <small class="text-muted mt-2 d-block">Pagamento à vista no valor total do plano.</small>
                                        <input type="hidden" name="payment_method_id" id="payment_method_id">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Observação</label>
                                    <textarea name="observacao" class="form-control" rows="2" placeholder="Observação interna sobre esta atribuição..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" id="btnSubmit" class="btn btn-primary">
                            <i class="fas fa-check mr-1"></i> <span id="btnText">Atribuir e Cobrar</span>
                        </button>
                        <a href="{{ route('assinatura.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    #cardElement.StripeElement--focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75,128,255,.18); }
    #cardElement.StripeElement--invalid { border-color: #dc3545; }
</style>
@stop

@section('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function() {
    const stripeKey = @json($stripePublicKey ?? '');
    const metodoSelect = document.getElementById('metodo_cobranca');
    const cardSection = document.getElementById('cardSection');
    const cardErrors = document.getElementById('cardErrors');
    const form = document.getElementById('formAtribuir');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnText = document.getElementById('btnText');
    const pmInput = document.getElementById('payment_method_id');

    let stripe = null;
    let elements = null;
    let cardElem = null;

    // ── Toggle card section ──
    function toggleCard() {
        if (metodoSelect.value === 'cartao' && stripeKey) {
            cardSection.classList.remove('d-none');
            if (!stripe) {
                stripe = Stripe(stripeKey);
                elements = stripe.elements();
                cardElem = elements.create('card', {
                    style: {
                        base: { fontSize: '15px', fontFamily: 'Arial, sans-serif', color: '#1a2035' },
                        invalid: { color: '#dc3545' },
                    }
                });
                cardElem.mount('#cardElement');

                cardElem.on('change', function(event) {
                    if (event.error) {
                        cardErrors.textContent = event.error.message;
                        cardErrors.classList.remove('d-none');
                    } else {
                        cardErrors.classList.add('d-none');
                    }
                });
            }
        } else {
            cardSection.classList.add('d-none');
        }
    }
    metodoSelect.addEventListener('change', toggleCard);
    toggleCard();

    // ── Valor preview ──
    function atualizarValor() {
        const plano = document.getElementById('plano_id').selectedOptions[0];
        const periodicidade = document.getElementById('periodicidade').value;
        const valorEl = document.getElementById('valorExibido');
        if (plano && plano.value) {
            const valor = periodicidade === 'anual'
                ? parseFloat(plano.dataset.anual || 0)
                : parseFloat(plano.dataset.mensal || 0);
            valorEl.value = valor.toFixed(2).replace('.', ',');
        } else {
            valorEl.value = '';
        }
    }
    document.getElementById('plano_id').addEventListener('change', atualizarValor);
    document.getElementById('periodicidade').addEventListener('change', atualizarValor);
    atualizarValor();

    // ── Submit ──
    form.addEventListener('submit', async function(e) {
        if (metodoSelect.value === 'cartao' && stripe && cardElem) {
            e.preventDefault();
            btnSubmit.disabled = true;
            btnText.textContent = 'Processando...';

            const { error, paymentMethod } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElem,
            });

            if (error) {
                cardErrors.textContent = error.message;
                cardErrors.classList.remove('d-none');
                btnSubmit.disabled = false;
                btnText.textContent = 'Atribuir e Cobrar';
                return;
            }

            pmInput.value = paymentMethod.id;
            form.submit();
        }
    });
})();
</script>
@stop
