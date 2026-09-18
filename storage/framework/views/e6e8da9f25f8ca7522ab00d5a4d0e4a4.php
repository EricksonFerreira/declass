<?php $__env->startSection('title', 'Plano Contratado'); ?>

<?php $__env->startSection('content_header'); ?>
    <div>
        <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">Contratacao Concluida</h1>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="text-center mb-4">
            <div style="font-size:4rem;margin-bottom:.75rem;">&#9989;</div>
            <h2 class="font-weight-bold" style="color:#0a1628;">Tudo pronto!</h2>
            <p class="text-muted" style="font-size:1rem;max-width:480px;margin:0 auto;">
                Seu plano foi contratado e o pagamento confirmado. Aproveite todos os recursos liberados.
            </p>
        </div>

        <?php if($plano): ?>
        <div class="card" style="border:2px solid #22c55e;border-radius:16px;overflow:hidden;">
            <div class="card-header text-white text-center py-3" style="background:#16a34a;">
                <i class="fas fa-check-circle mr-1"></i>
                <strong style="font-size:1.1rem;"><?php echo e($plano->nome); ?></strong>
                <?php if($assinatura?->periodicidade === 'anual'): ?>
                    <span class="badge bg-white text-success ml-2">Anual</span>
                <?php else: ?>
                    <span class="badge bg-white text-primary ml-2">Mensal</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0" style="font-size:.88rem;">
                            <tr>
                                <td class="text-muted pl-0" style="width:45%;">Valor</td>
                                <td class="font-weight-bold">R$ <?php echo e(number_format($pagamento->valor ?? $assinatura?->valor_cobrado, 2, ',', '.')); ?></td>
                            </tr>
                            <?php if($assinatura): ?>
                            <tr>
                                <td class="text-muted pl-0">Periodicidade</td>
                                <td class="font-weight-bold"><?php echo e($assinatura->periodicidade === 'anual' ? 'Anual' : 'Mensal'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Inicio</td>
                                <td class="font-weight-bold"><?php echo e($assinatura->data_inicio?->format('d/m/Y')); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Vencimento</td>
                                <td class="font-weight-bold"><?php echo e($assinatura->data_vencimento?->format('d/m/Y')); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Metodo</td>
                                <td class="font-weight-bold"><?php echo e(ucfirst($pagamento->metodo ?? $assinatura->metodo_cobranca ?? '—')); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if($pagamento?->pago_em): ?>
                            <tr>
                                <td class="text-muted pl-0">Pago em</td>
                                <td class="font-weight-bold"><?php echo e($pagamento->pago_em->format('d/m/Y H:i')); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:#f0fdf4;">
                            <h6 class="font-weight-bold mb-2" style="font-size:.85rem;color:#065f46;">
                                <i class="fas fa-check-circle mr-1"></i> O que esta incluso
                            </h6>
                            <ul class="list-unstyled mb-0" style="font-size:.82rem;">
                                <?php $__currentLoopData = $plano->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="mb-1">
                                        <i class="fas fa-check text-success mr-2" style="font-size:.7rem;"></i>
                                        <?php echo e($item->descricao); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <hr class="my-3">
                <div class="d-flex" style="gap:1.5rem;flex-wrap:wrap;">
                    <?php if($plano->limite_veiculos): ?>
                        <div>
                            <span class="text-muted" style="font-size:.78rem;">Limite de veiculos</span>
                            <div class="font-weight-bold" style="font-size:1.1rem;"><?php echo e($plano->limite_veiculos); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if($plano->limite_usuarios): ?>
                        <div>
                            <span class="text-muted" style="font-size:.78rem;">Limite de usuarios</span>
                            <div class="font-weight-bold" style="font-size:1.1rem;"><?php echo e($plano->limite_usuarios); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php
                        $storageMb = $plano->limite_armazenamento_mb ?? 0;
                        $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                    ?>
                    <div>
                        <span class="text-muted" style="font-size:.78rem;">Armazenamento</span>
                        <div class="font-weight-bold" style="font-size:1.1rem;"><i class="fas fa-database mr-1"></i><?php echo e($storageTxt); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center mt-4 d-flex justify-content-center" style="gap:.75rem;flex-wrap:wrap;">
            <a href="<?php echo e(route('assinatura.meu-plano')); ?>" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-id-card mr-1"></i> Ir para Meu Plano
            </a>
            <a href="<?php echo e(route('home')); ?>" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fas fa-tachometer-alt mr-1"></i> Ir para o Painel
            </a>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\assinatura\concluido.blade.php ENDPATH**/ ?>