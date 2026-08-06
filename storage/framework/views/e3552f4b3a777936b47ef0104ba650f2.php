<?php if($reserva->contratoCheckout): ?>
<?php
    $co = $reserva->contratoCheckout;
    $coAssinado = ! empty($co->assinatura) || ! empty($co->data_assinatura) || $co->assinado === 'S';
    $coVeiculo = $co->veiculo?->nome ?? ('ID ' . $co->veiculo_id);
    $coBorder = $coAssinado ? '#10b981' : '#f59e0b';
    $coBgFrom = $coAssinado ? '#ecfdf5' : '#fffbeb';
    $coBgTo = $coAssinado ? '#d1fae5' : '#fef3c7';
    $coBadgeBg = $coAssinado ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.18)';
    $coBadgeClr = $coAssinado ? '#047857' : '#b45309';
    $coBadgeTxt = $coAssinado ? 'Contrato Assinado' : 'Aguardando Assinatura';
    $coBadgeIcon = $coAssinado ? 'fa-check-circle' : 'fa-clock';

    $fotosCheckoutPaths = [
        'Direita' => $co->foto_direita_path ?? null,
        'Esquerda' => $co->foto_esquerda_path ?? null,
        'Frente' => $co->foto_frente_path ?? null,
        'Trás' => $co->foto_tras_path ?? null,
        'Velocímetro' => $co->foto_velocimetro_path ?? null,
        'Combustível' => $co->foto_combustivel_path ?? null,
    ];
?>

<div class="rs-checkin-layout">
    <div class="rs-ci-card" style="border-left:5px solid <?php echo e($coBorder); ?>;background:linear-gradient(135deg,<?php echo e($coBgFrom); ?>,<?php echo e($coBgTo); ?>)">
        <div class="rs-ci-card__top">
            <div class="rs-ci-card__icon" style="background:<?php echo e($coBorder); ?>">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div class="rs-ci-card__info">
                <div class="rs-ci-card__title">Contrato de Check-out</div>
                <span class="rs-ci-card__badge" style="background:<?php echo e($coBadgeBg); ?>;color:<?php echo e($coBadgeClr); ?>">
                    <i class="fas <?php echo e($coBadgeIcon); ?> mr-1"></i><?php echo e($coBadgeTxt); ?>

                </span>
            </div>
        </div>

        <div class="rs-ci-card__details">
            <div class="rs-ci-detail">
                <span><i class="fas fa-car mr-1"></i>Veículo</span>
                <strong><?php echo e($coVeiculo); ?></strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-tachometer-alt mr-1"></i>Quilometragem</span>
                <strong><?php echo e(number_format((float)($co->quilometragem_devolucao ?? 0), 0, ',', '.')); ?> km</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                <strong><?php echo e($co->nivel_combustivel_devolucao ?? '—'); ?></strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-calendar mr-1"></i>Devolução</span>
                <strong>
                    <?php echo e($co->data_devolucao ? \Carbon\Carbon::parse($co->data_devolucao)->format('d/m/Y') : '—'); ?>

                    <?php echo e($co->hora_devolucao ? substr($co->hora_devolucao, 0, 5) : ''); ?>

                </strong>
            </div>
            <?php if($coAssinado && $co->data_assinatura): ?>
            <div class="rs-ci-detail">
                <span><i class="fas fa-pen-nib mr-1"></i>Assinado em</span>
                <strong><?php echo e(\Carbon\Carbon::parse($co->data_assinatura)->format('d/m/Y H:i')); ?></strong>
            </div>
            <?php endif; ?>
            <?php if(!empty($co->notas_devolucao)): ?>
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-sticky-note mr-1"></i>Notas de devolução</span>
                <strong><?php echo e($co->notas_devolucao); ?></strong>
            </div>
            <?php endif; ?>
            <?php if(!empty($co->observacoes_devolucao)): ?>
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-comment-alt mr-1"></i>Observações</span>
                <strong><?php echo e($co->observacoes_devolucao); ?></strong>
            </div>
            <?php endif; ?>
        </div>

        <div class="rs-ci-card__actions">
            <?php if($coAssinado): ?>
                <a href="<?php echo e(route('reserva.minhas.vercontratocheckout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                    <i class="fas fa-eye"></i> Ver contrato
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('reserva.minhas.assinar-checkout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" class="rs-ci-btn rs-ci-btn--sign">
                    <i class="fas fa-signature"></i> Assinar Check-out / Devolução
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-camera rs-card__icon"></i>
            <h2>Fotos do Veículo no Check-out</h2>
        </div>
        <div class="row rs-photo-grid">
            <?php $__currentLoopData = $fotosCheckoutPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                   <?php
        $fotoUrl = $path
            ? asset('storage/' . ltrim($path, '/'))
            : null;
    ?>
                <div class="col-6 col-md-2 rs-photo-item">
                    <?php if($fotoUrl): ?>
                        <div class="rs-photo-frame js-lightbox-trigger"
                             data-src="<?php echo e($fotoUrl); ?>" data-label="<?php echo e($label); ?> (Check-out)">
                            <img src="<?php echo e($fotoUrl); ?>" alt="Foto <?php echo e($label); ?> do check-out" class="rs-photo-thumb">
                            <div class="rs-photo-zoom">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="rs-photo-placeholder">
                            <i class="fas fa-image"></i>
                            <span>Sem foto</span>
                        </div>
                    <?php endif; ?>
                    <div class="rs-photo-label"><?php echo e($label); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php else: ?>
    <div class="rs-empty py-5">
        <i class="fas fa-file-signature" style="font-size:2.5rem;color:#cbd5e1"></i>
        <span>Nenhum contrato de check-out cadastrado para esta reserva.</span>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/cliente_checkout.blade.php ENDPATH**/ ?>