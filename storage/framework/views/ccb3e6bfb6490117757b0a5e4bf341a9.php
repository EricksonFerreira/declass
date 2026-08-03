<div class="section-title">Danos e Avarias</div>
<?php
    $formatarTipoDano = $formatarTipoDano ?? function ($tipo) {
        $map = [
            'amolgadela_grave' => 'Amassado grave',
            'amolgadela_leve' => 'Amassado leve',
            'risco_leve' => 'Risco leve',
            'risco_profundo' => 'Risco profundo',
            'pneu_danificado' => 'Pneu danificado',
            'vidro_trincado' => 'Vidro trincado',
            'para_choque_danificado' => 'Para-choque danificado',
        ];
        if (blank($tipo)) {
            return '-';
        }
        return $map[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
    };
    // Flag to hide value and image columns for checkin contracts
    $hideValor = $hideValor ?? false;
?>
<?php if(!empty($danos)): ?>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Local</th>
                <th>Descrição</th>
                <th>Tipo</th>
                <?php if(!$hideValor): ?>
                    <th>Valor</th>
                <?php endif; ?>
                <th>Registrado em</th>
                <?php if(!$hideValor): ?>
                    <th>Imagens</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $danos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <strong><?php echo e($dano['localizacao'] ?? '-'); ?></strong><br>
                        <span class="muted">
                            <?php echo e($dano['nome_dano'] ?? $dano['categoria'] ?? 'Dano não informado'); ?>

                        </span>
                    </td>
                    <td>
                        <?php echo e($dano['observacoes'] ?? 'Sem descrição'); ?><br>
                        <span class="muted">KM: <?php echo e($dano['quilometragem'] ?? '-'); ?></span>
                    </td>
                    <td>
                        <?php echo e($formatarTipoDano($dano['tipo_dano'] ?? null)); ?>

                    </td>
                    <?php if(!$hideValor): ?>
                        <td class="money"><?php echo e($dano['custo_estimado'] ?? '-'); ?></td>
                    <?php endif; ?>
                    <td>
                        <span class="muted"><?php echo e($dano['registrado_em'] ?? '-'); ?></span>
                    </td>
                    <?php if(!$hideValor): ?>
                        <td>
                            <?php $__empty_1 = true; $__currentLoopData = ($dano['imagens'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <img class="thumb" src="<?php echo e($img); ?>" alt="Dano">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="muted">Sem foto</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="muted" style="font-style:italic;margin-top:4px">
        Nenhuma avaria registrada nesta reserva.
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/danos-avarias.blade.php ENDPATH**/ ?>