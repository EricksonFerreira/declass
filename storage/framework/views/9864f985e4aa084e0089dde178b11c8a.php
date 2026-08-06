<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-circle-exclamation"></i></div>
        <div>
            <div class="rs-card__title">Multas</div>
            <div class="rs-card__sub">Infrações vinculadas a esta reserva</div>
        </div>
    </div>
    <div class="rs-card__body">
        <?php $multasCliente = $reserva->multas ?? collect(); ?>
        <?php if($multasCliente->isEmpty()): ?>
            <div class="rs-note">Nenhuma multa vinculada a esta reserva.</div>
        <?php else: ?>
            <div class="rs-list">
                <?php $__currentLoopData = $multasCliente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $multa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $statusMulta = strtolower((string) ($multa->status_multa ?? 'pendente'));
                        $labelMulta = match ($statusMulta) {
                            'paga' => 'Paga',
                            'pendente', 'em_analise' => 'Pendente',
                            'cancelada' => 'Cancelada',
                            default => ucfirst($statusMulta),
                        };
                    ?>
                    <div class="rs-list__item" style="border-bottom:1px solid var(--ma-line);padding:10px 0;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="rs-list__label">Multa #<?php echo e($multa->id); ?></div>
                                <div class="rs-list__value"><?php echo e($multa->local_infracao ?: 'Local não informado'); ?></div>
                                <div class="text-muted" style="font-size:.74rem;">
                                    <?php echo e($multa->data_hora_infracao ? \Carbon\Carbon::parse($multa->data_hora_infracao)->format('d/m/Y H:i') : '-'); ?>

                                </div>
                            </div>
                            <span class="badge badge-<?php echo e($statusMulta === 'paga' ? 'success' : ($statusMulta === 'cancelada' ? 'danger' : 'warning')); ?>"><?php echo e($labelMulta); ?></span>
                        </div>
                        <div class="mt-2 text-dark font-weight-bold">R$ <?php echo e(number_format((float) ($multa->valor_final ?? $multa->valor_original ?? 0), 2, ',', '.')); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/cliente_multas.blade.php ENDPATH**/ ?>