<div class="page-break"></div>

<div class="clause-page">
    <div class="clause-header">
        <div class="clause-header-bar"></div>
            <div class="clause-header-content">
            <div>
                <div class="clause-header-title">CONDIÇÕES GERAIS DA LOCAÇÃO</div>
                <div class="clause-header-subtitle">
                    Cláusulas contratuais que regem a relação entre LOCADORA e LOCATÁRIO
                </div>
            </div>
        </div>
        <div class="clause-header-bar"></div>
    </div>

    <?php
        $todasClausulas = $data['condicoes_gerais'] ?? [];
    ?>

    <?php if(!empty($todasClausulas)): ?>
        <div class="clause-list">
            <?php $__currentLoopData = $todasClausulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clausula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="clause-item">
                    <div class="clause-num"><?php echo e(str_pad($clausula['ordem'] ?? $loop->iteration, 2, '0', STR_PAD_LEFT)); ?></div>
                    <div class="clause-body">
                        <strong><?php echo e($clausula['titulo'] ?? ''); ?></strong>
                        <?php echo $clausula['texto'] ?? ''; ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="clause-item">
            <div class="clause-body">Nenhuma condição geral cadastrada para esta empresa.</div>
        </div>
    <?php endif; ?>

    <div class="clause-footer-bar">
        <div class="clause-footer-bar-inner">
            <span>LOCADORA: <?php echo e($get('documento.empresa.nome', 'Locadora')); ?></span>
            <span class="clause-footer-bull">&bull;</span>
            <span>Contrato: <?php echo e($get('contrato.numero', '#00000')); ?></span>
            <span class="clause-footer-bull">&bull;</span>
            <span>Data: <?php echo e(now()->format('d/m/Y')); ?></span>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/condicoes-gerais.blade.php ENDPATH**/ ?>