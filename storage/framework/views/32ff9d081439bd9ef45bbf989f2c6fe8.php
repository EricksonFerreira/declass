<div class="section-title" style="margin-bottom:10px !important">Estado da Viatura na Devolução</div>
<?php
    $checkoutFotos = $data['checkout_fotos'] ?? [];
    $fotosBase = [
        'Direita' => asset('assets/imgs/carro/direita.jpg'),
        'Esquerda' => asset('assets/imgs/carro/esquerda.jpg'),
        'Frente' => asset('assets/imgs/carro/frente.jpg'),
        'Trás' => asset('assets/imgs/carro/atras.jpg'),
        'Velocímetro' => asset('assets/imgs/carro/cima.jpg'),
        'Combustível' => asset('assets/imgs/carro/frente.jpg'),
    ];
    $resolverUrlFoto = function ($path) {
        if (empty($path)) {
            return null;
        }

        $cleanPath = ltrim((string) $path, '/');

        if (str_contains($cleanPath, 'http://') || str_contains($cleanPath, 'https://')) {
            return $cleanPath;
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'public/')) {
            return asset('storage/' . ltrim(substr($cleanPath, 7), '/'));
        }

        return asset('storage/' . $cleanPath);
    };
?>
<div class="viatura-images">
    <?php $__currentLoopData = $fotosBase; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $fallback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $fotoUrl = $resolverUrlFoto($checkoutFotos[$label] ?? null) ?? $fallback;
        ?>
        <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($label); ?> do check-out">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php if(is_countable($estadoViatura) && count($estadoViatura) > 0): ?>
    <div class="dano-box">
        <table>
            <?php $__currentLoopData = $estadoViatura; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($item['local'] ?? '-'); ?></strong></td>
                    <td>
                        <div>
                            <?php echo e($item['localizacao'] ?? ''); ?>

                            -
                            <?php echo e($item['observacoes'] ?? ''); ?>

                            <?php echo e($formatarTipoDano($item['tipo_dano'] ?? null)); ?>

                        </div>
                        <?php if(!empty($item['correcao'])): ?>
                            <div style="margin-top:3px;font-size:8px;color:#047857;">
                                <strong>Correção:</strong> <?php echo e($item['correcao']); ?>

                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </div>
<?php else: ?>
    <div class="muted" style="font-style:italic;margin-top:4px">
        Sem danos registrados no check-out.
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/pdf/contratos/partials/checkout-estado-viatura.blade.php ENDPATH**/ ?>