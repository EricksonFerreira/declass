<div class="rev-top">
    <div>
        <h1 class="rev-title">
            Revisão do Check-out
            <span class="rev-badge <?php echo e($checkoutAssinado ? 'rev-badge--green' : ''); ?>">
                <i class="fas <?php echo e($checkoutAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
                <?php echo e($checkoutAssinado ? 'Check-out assinado' : 'Aguardando assinatura'); ?>

            </span>
        </h1>
        <div class="rev-sub">
            Confira danos, valores de cobrança e pendências antes de concluir a reserva.
        </div>
    </div>
    <div class="rev-actions">
        <a href="<?php echo e(route('reserva.show', $codigoReserva)); ?>" class="rev-btn">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        
        <?php $revisaoCheckoutConcluida = ($reserva->revisao_checkout_concluida ?? 'N') === 'S'; ?>

        <?php if($checkoutAssinado && ! $revisaoCheckoutConcluida): ?>
            <?php echo $__env->make('reserva.partials.revisao_checkout_add_dano_button', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/revisao_checkout_header.blade.php ENDPATH**/ ?>