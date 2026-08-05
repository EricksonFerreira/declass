<div class="signature-grid no-break">
    <div class="signature-box">
        <div class="signature-line">
            <?php if(!empty($get('assinatura', null)) && $get('assinatura') !== '<br>'): ?>
                <img src="<?php echo e($get('assinatura')); ?>" alt="Assinatura do locatário">
            <?php endif; ?>
        </div>
        <div class="signature-name"><?php echo e($get('cliente.nome', 'Locatário')); ?></div>
        <div class="signature-role">Locatário</div>
        <div class="signature-doc">
            CPF: <?php echo e($get('cliente.nif', '-')); ?><br>
            CNH: <?php echo e($get('cliente.carta_conducao.numero', '-')); ?>

        </div>
        <?php if($get('documento.data_assinatura', null) && !empty($get('assinatura', null)) && $get('assinatura') !== '<br>'): ?>
            <div class="signature-doc" style="margin-top:2px;">
                Assinado em: <?php echo e($get('documento.data_assinatura')); ?>

            </div>
        <?php endif; ?>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div class="signature-name"><?php echo e($get('funcionario.nome', 'Atendente')); ?></div>
        <div class="signature-role">Atendente / Responsável</div>
        <div class="signature-doc">
            Emitido em: <?php echo e($get('documento.data_emissao', now()->format('d/m/Y H:i'))); ?>

        </div>
    </div>
</div>
<?php if($get('condutor_adicional.nome', null)): ?>
    <div class="signature-grid no-break" style="margin-top:20px">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name"><?php echo e($get('condutor_adicional.nome')); ?></div>
            <div class="signature-role">Condutor Adicional</div>
            <div class="signature-doc">Documento: <?php echo e($get('condutor_adicional.documento', '-')); ?></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-name">Vistoria de Entrega</div>
            <div class="signature-role">Confirmação das condições do veículo</div>
            <div class="signature-doc">Contrato: <?php echo e($get('contrato.numero', '-')); ?></div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/assinaturas.blade.php ENDPATH**/ ?>