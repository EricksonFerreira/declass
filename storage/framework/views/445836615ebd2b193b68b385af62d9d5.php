<div class="section-title">Dados do Cliente</div>
<table class="info-table">
    <tr>
        <td style="width:50%">
            <div class="field-label">Nome Completo</div>
            <div class="field-value"><?php echo e($get('cliente.nome', 'Não informado')); ?></div>
        </td>
        <td style="width:25%">
            <div class="field-label">CPF / Documento</div>
            <div class="field-value"><?php echo e($get('cliente.nif', '-')); ?></div>
        </td>
        <td style="width:25%">
            <div class="field-label">Passaporte</div>
            <div class="field-value"><?php echo e($get('cliente.passaporte', '-')); ?></div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">E-mail</div>
            <div class="field-value"><?php echo e($get('cliente.email', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">Telefone</div>
            <div class="field-value"><?php echo e($get('cliente.telefone', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">Data Nascimento</div>
            <div class="field-value"><?php echo e($get('cliente.data_nascimento', '-')); ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="field-label">Endereço</div>
            <div class="field-value"><?php echo e($get('cliente.endereco', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">País</div>
            <div class="field-value"><?php echo e($get('cliente.pais', '-')); ?></div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">CNH Nº</div>
            <div class="field-value"><?php echo e($get('cliente.carta_conducao.numero', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">Emitida em</div>
            <div class="field-value"><?php echo e($get('cliente.carta_conducao.data_emissao', '-')); ?></div>
        </td>
        <td>
            <div class="field-label">Válida até</div>
            <div class="field-value"><?php echo e($get('cliente.carta_conducao.validade', '-')); ?></div>
        </td>
    </tr>
</table>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/dados-cliente.blade.php ENDPATH**/ ?>