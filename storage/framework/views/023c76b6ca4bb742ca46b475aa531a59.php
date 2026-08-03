<div class="doc-footer">
    <div class="doc-footer-inner">
        <div class="doc-footer-col">
            <span class="doc-footer-brand"><?php echo e($get('documento.empresa.nome', 'Locadora')); ?></span>
            <?php if($get('documento.empresa.site', null)): ?>
                <span class="doc-footer-sep">|</span>
                <span><?php echo e($get('documento.empresa.site')); ?></span>
            <?php endif; ?>
            <?php if($get('documento.empresa.telefone', null)): ?>
                <span class="doc-footer-sep">|</span>
                <span><?php echo e($get('documento.empresa.telefone')); ?></span>
            <?php endif; ?>
        </div>
        <div class="doc-footer-col doc-footer-center">
            <span>Processado por DEcars &mdash; <?php echo e(now()->format('d/m/Y H:i')); ?></span>
            <?php if($get('documento.hash', null)): ?>
                <br><span class="doc-footer-hash">Hash: <?php echo e($get('documento.hash')); ?></span>
            <?php endif; ?>
        </div>
        <div class="doc-footer-col doc-footer-right">
            <span>Contrato <?php echo e($get('contrato.numero', '#00000')); ?></span>
            <span class="doc-footer-sep">|</span>
            <span>Página</span>
        </div>
    </div>
    <?php if($get('documento.qr_code', null)): ?>
        <div class="doc-footer-qr">
            <img class="qr-code" src="<?php echo e($get('documento.qr_code')); ?>" alt="QR Code de validação">
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/footer.blade.php ENDPATH**/ ?>