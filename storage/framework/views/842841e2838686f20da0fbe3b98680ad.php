<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $uid = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $id);
    $metodos = collect($metodosPagamento ?? []);
    $temMetodos = $metodos->isNotEmpty();
    $valorFloat = (float) $valor;
    $stripeKey = (string) ($stripePublicKey ?? '');
?>

<div class="ep-card mb-3 ps-card" data-ps-root="<?php echo e($uid); ?>">
    <div class="ep-card__header ep-card__header--green">
        <div class="ep-card__icon ep-card__icon--green"><i class="fas fa-credit-card"></i></div>
        <div>
            <div class="ep-card__title"><?php echo e($titulo); ?></div>
            <div class="ep-card__sub">
                <strong><?php echo e($subtitulo); ?></strong>
                &nbsp;·&nbsp; Moeda: <strong><?php echo e($moeda); ?></strong>
            </div>
        </div>
    </div>
    <div class="ep-card__body">
        <div class="ep-tip mb-3">
            <i class="fas fa-info-circle text-primary mr-2"></i>
            <?php echo e($descricao); ?>

        </div>

        <form id="<?php echo e($uid); ?>-form" action="<?php echo e($action); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php $__currentLoopData = $hiddenInputs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden" name="<?php echo e($name); ?>" value="<?php echo e($value); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="form-group">
                <label class="ep-form-label"><i class="fas fa-wallet mr-1"></i> Metodo de pagamento</label>
                <div class="d-flex" style="gap:.5rem">
                    <select id="<?php echo e($uid); ?>-payment-method" class="form-control form-control-sm" name="payment_method" required>
                        <?php if(!$temMetodos): ?>
                            <option value="" disabled selected>Nenhum metodo cadastrado</option>
                        <?php else: ?>
                            <?php $__currentLoopData = $metodos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metodo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($metodo['id']); ?>">
                                    <?php echo e(strtoupper($metodo['card']->brand ?? $metodo['brand'] ?? 'CARD')); ?>

                                    ****<?php echo e($metodo['card']->last4 ?? $metodo['last4'] ?? '----'); ?>

                                    (<?php echo e($metodo['card']->exp_month ?? $metodo['exp_month'] ?? '--'); ?>/<?php echo e($metodo['card']->exp_year ?? $metodo['exp_year'] ?? '--'); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                    <button type="button" id="<?php echo e($uid); ?>-open-modal" class="btn btn-sm btn-outline-primary" style="white-space:nowrap">
                        <i class="fas fa-plus"></i> Novo
                    </button>
                </div>
                <?php if(empty($stripeKey)): ?>
                    <small class="text-muted">Chave publica do Stripe nao configurada.</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="ep-form-label"><i class="fas fa-dollar-sign mr-1"></i> Valor a pagar</label>
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><?php echo e($simbolo); ?></span></div>
                    <?php if($valorReadonly): ?>
                        <input type="text" class="form-control form-control-sm" value="<?php echo e(number_format($valorFloat, 2, ',', '.')); ?>" readonly>
                        <input type="hidden" name="valor_pagamento" id="<?php echo e($uid); ?>-valor" value="<?php echo e(number_format($valorFloat, 2, '.', '')); ?>">
                    <?php else: ?>
                        <input type="number" name="valor_pagamento" id="<?php echo e($uid); ?>-valor" class="form-control form-control-sm" step="0.01" min="0.01" max="<?php echo e(number_format($valorFloat, 2, '.', '')); ?>" value="<?php echo e(number_format($valorFloat, 2, '.', '')); ?>" required>
                        <div class="input-group-append"><span class="input-group-text text-muted" style="font-size:11px;">max. <?php echo e($simbolo); ?> <?php echo e(number_format($valorFloat, 2, ',', '.')); ?></span></div>
                    <?php endif; ?>
                </div>
                <small class="text-muted">Pagamento por cartao de credito vinculado ao sistema.</small>
            </div>

            <div class="ep-total-box ep-total-box--warning mb-3" style="background:#fefce8; border:1px solid #fde047; border-radius:14px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                <div class="ep-total-box__label" style="color:#a16207; font-weight:900; text-transform:uppercase; font-size:.78rem; letter-spacing:.06em;">Saldo pendente</div>
                <div class="ep-total-box__value" style="color:#854d0e; font-weight:900; font-size:1.3rem;"><?php echo e($simbolo); ?> <?php echo e(number_format($valorFloat, 2, ',', '.')); ?></div>
            </div>

            <?php if($showInstallments): ?>
                <div class="form-group">
                    <label class="ep-form-label"><i class="fas fa-calendar-alt mr-1"></i> Parcelamento</label>
                    <select id="<?php echo e($uid); ?>-installments" name="installments" class="form-control form-control-sm">
                        <option value="1">A vista - <?php echo e($simbolo); ?> <?php echo e(number_format($valorFloat, 2, ',', '.')); ?></option>
                        <?php $__currentLoopData = [2,3,4,5,6,7,8,9,10,11,12]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($parc); ?>"><?php echo e($parc); ?>x de <?php echo e($simbolo); ?> <?php echo e(number_format($valorFloat / $parc, 2, ',', '.')); ?> <?php echo e($parc <= 3 ? '- Sem juros' : '- Com juros'); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Ate 3x sem juros. Acima disso, consulte as taxas aplicaveis pela operadora.</small>
                </div>
            <?php endif; ?>

            <span class="ep-pay-btn-wrap" title="<?php echo e($temMetodos ? '' : 'Adicione um metodo de pagamento salvo para habilitar o pagamento.'); ?>">
                <button type="submit" id="<?php echo e($uid); ?>-submit" class="btn btn-success btn-block ep-btn-pay" <?php echo e($temMetodos ? '' : 'disabled aria-disabled=true'); ?>>
                    <i class="fas fa-lock mr-2"></i> <?php echo e($submitLabel); ?>

                </button>
            </span>
        </form>
    </div>
</div>

<div class="modal fade" id="<?php echo e($uid); ?>-modal" tabindex="-1" role="dialog" aria-hidden="true">
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
                <?php if(empty($stripeKey)): ?>
                    <div class="alert alert-warning small mb-3">
                        Configure a chave publica do Stripe para salvar um novo cartao.
                    </div>
                <?php endif; ?>
                <div class="form-group mb-0">
                    <label class="text-xs font-weight-bold">Dados do Cartao</label>
                    <div id="<?php echo e($uid); ?>-card-element" class="form-control" style="padding-top:10px;"></div>
                    <div id="<?php echo e($uid); ?>-card-errors" class="text-danger text-xs mt-1" role="alert"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="<?php echo e($uid); ?>-save">
                    <i class="fas fa-save mr-1"></i> Salvar Cartao
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('c50565f7-1dc8-4d33-b81c-289f8d9e061e')): $__env->markAsRenderedOnce('c50565f7-1dc8-4d33-b81c-289f8d9e061e'); ?>
    <?php $__env->startPush('js'); ?>
        <script src="https://js.stripe.com/v3/"></script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('js'); ?>
<script>
(function () {
    const uid = <?php echo json_encode($uid, 15, 512) ?>;
    const stripeKey = <?php echo json_encode($stripeKey, 15, 512) ?>;
    const adicionarUrl = <?php echo json_encode($adicionarUrl, 15, 512) ?>;
    const csrf = <?php echo json_encode(csrf_token(), 15, 512) ?>;
    const form = document.getElementById(uid + '-form');
    const submitBtn = document.getElementById(uid + '-submit');
    const paySelect = document.getElementById(uid + '-payment-method');
    const openModal = document.getElementById(uid + '-open-modal');
    const saveBtn = document.getElementById(uid + '-save');
    const cardErrors = document.getElementById(uid + '-card-errors');
    const valorInput = document.getElementById(uid + '-valor');
    const installments = document.getElementById(uid + '-installments');
    const modalEl = document.getElementById(uid + '-modal');
    const simbolo = <?php echo json_encode($simbolo, 15, 512) ?>;

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
            var maxVal = parseFloat(<?php echo json_encode($valorFloat, 15, 512) ?>) || 0;
            if (maxVal > 0 && val > maxVal) {
                this.value = maxVal.toFixed(2);
                alert('O valor a pagar não pode exceder o saldo pendente de ' + <?php echo json_encode($simbolo, 15, 512) ?> + ' ' + maxVal.toFixed(2).replace('.', ','));
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
                var maxVal = parseFloat(<?php echo json_encode($valorFloat, 15, 512) ?>) || 0;
                if (maxVal > 0 && val > maxVal + 0.001) {
                    e.preventDefault();
                    valorInput.value = maxVal.toFixed(2);
                    alert('O valor a pagar não pode exceder o saldo pendente de ' + <?php echo json_encode($simbolo, 15, 512) ?> + ' ' + maxVal.toFixed(2).replace('.', ','));
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
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\pagamento\cartao-stripe.blade.php ENDPATH**/ ?>