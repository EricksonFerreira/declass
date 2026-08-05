<div class="doc-header">
    <div class="doc-header__logo">
        <?php if(!empty($get('documento.empresa.logo_url', null))): ?>
            <img src="<?php echo e($get('documento.empresa.logo_url')); ?>" alt="Logo da empresa">
        <?php else: ?>
            <img class="sistema-logo" src="<?php echo e(asset('assets/imgs/logo.png')); ?>" alt="DEcars">
        <?php endif; ?>
    </div>
    <div class="doc-header__info">
        <div class="doc-header__title">Contrato de Aluguel</div>
        <div class="doc-header__subtitle">
            <?php echo e($get('documento.empresa.nome', 'Locadora')); ?>

            <?php if($get('documento.empresa.cnpj', null)): ?>
                &bull; CNPJ: <?php echo e($get('documento.empresa.cnpj')); ?>

            <?php endif; ?>
            &bull; Condições Particulares
        </div>
        <div class="doc-header__company">
            <?php if($get('documento.empresa.endereco', null)): ?>
                <?php echo e($get('documento.empresa.endereco')); ?>

            <?php endif; ?>
            <?php if($get('documento.empresa.telefone', null)): ?>
                &bull; <?php echo e($get('documento.empresa.telefone')); ?>

            <?php endif; ?>
            <?php if($get('documento.empresa.email', null)): ?>
                &bull; <?php echo e($get('documento.empresa.email')); ?>

            <?php endif; ?>
        </div>
        <div class="doc-header__num">
            <?php echo e($get('contrato.numero', '#00000')); ?>

        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/header.blade.php ENDPATH**/ ?>