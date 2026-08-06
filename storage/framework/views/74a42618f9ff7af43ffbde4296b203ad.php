<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-car-crash"></i></div>
        <div>
            <div class="rs-card__title">Sinistros</div>
            <div class="rs-card__sub">Ocorrências ligadas a esta reserva</div>
        </div>
    </div>
    <div class="rs-card__body">
        <?php $sinistrosCliente = $reserva->avariaCobrancas ?? collect(); ?>
        <?php if($sinistrosCliente->isEmpty()): ?>
            <div class="rs-note">Nenhum sinistro vinculado a esta reserva.</div>
        <?php else: ?>
            <div class="rs-list">
                <?php $__currentLoopData = $sinistrosCliente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sinistro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $statusSinistro = strtolower((string) ($sinistro->status ?? 'aberto'));
                        $labelStatus = match ($statusSinistro) {
                            'aberto', 'em_analise' => 'Em análise',
                            'pago', 'cobrado' => 'Cobrado',
                            'cancelado' => 'Cancelado',
                            default => ucfirst($statusSinistro),
                        };
                    ?>
                    <div class="rs-list__item" style="border-bottom:1px solid var(--ma-line);padding:10px 0;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="rs-list__label"><?php echo e($sinistro->descricao ?: 'Sinistro'); ?></div>
                                <div class="text-muted" style="font-size:.74rem;">
                                    <?php echo e($sinistro->created_at ? \Carbon\Carbon::parse($sinistro->created_at)->format('d/m/Y') : '-'); ?>

                                </div>
                            </div>
                            <span class="badge badge-<?php echo e($statusSinistro === 'pago' || $statusSinistro === 'cobrado' ? 'success' : ($statusSinistro === 'cancelado' ? 'danger' : 'warning')); ?>"><?php echo e($labelStatus); ?></span>
                        </div>
                        <div class="mt-2 text-dark font-weight-bold">R$ <?php echo e(number_format((float) ($sinistro->valor ?? 0), 2, ',', '.')); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/cliente_sinistros.blade.php ENDPATH**/ ?>