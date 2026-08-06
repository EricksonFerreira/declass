<?php if($reserva->contratoCheckin): ?>
<?php
    $ci          = $reserva->contratoCheckin;
    $ciAssinado  = $ci->assinado === 'S';
    $checkoutJaRegistrado = $reserva->contratoCheckout()->exists();
    $ciVeiculo   = $ci->veiculo?->nome ?? ('ID ' . $ci->veiculo_id);
    $ciBorder    = $ciAssinado ? '#10b981' : '#f59e0b';
    $ciBgFrom    = $ciAssinado ? '#ecfdf5' : '#fffbeb';
    $ciBgTo      = $ciAssinado ? '#d1fae5' : '#fef3c7';
    $ciBadgeBg   = $ciAssinado ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.18)';
    $ciBadgeClr  = $ciAssinado ? '#047857' : '#b45309';
    $ciBadgeTxt  = $ciAssinado ? 'Contrato Assinado' : 'Aguardando Assinatura';
    $ciBadgeIcon = $ciAssinado ? 'fa-check-circle' : 'fa-clock';

    $fotosPaths = [
        'Direita'      => $ci->foto_direita_path ?? null,
        'Esquerda'     => $ci->foto_esquerda_path ?? null,
        'Frente'       => $ci->foto_frente_path ?? null,
        'Trás'         => $ci->foto_tras_path ?? null,
        'Velocímetro'  => $ci->foto_velocimetro_path ?? null,
        'Combustível'  => $ci->foto_combustivel_path ?? null,
    ];
?>

<div class="rs-checkin-layout">

    
    <div class="rs-ci-card" style="border-left:5px solid <?php echo e($ciBorder); ?>;background:linear-gradient(135deg,<?php echo e($ciBgFrom); ?>,<?php echo e($ciBgTo); ?>)">
        <div class="rs-ci-card__top">
            <div class="rs-ci-card__icon" style="background:<?php echo e($ciBorder); ?>">
                <i class="fas fa-file-signature"></i>
            </div>
            <div class="rs-ci-card__info">
                <div class="rs-ci-card__title">Contrato de Check-in</div>
                <span class="rs-ci-card__badge" style="background:<?php echo e($ciBadgeBg); ?>;color:<?php echo e($ciBadgeClr); ?>">
                    <i class="fas <?php echo e($ciBadgeIcon); ?> mr-1"></i><?php echo e($ciBadgeTxt); ?>

                </span>
            </div>
        </div>

        <div class="rs-ci-card__details">
            <div class="rs-ci-detail">
                <span><i class="fas fa-car mr-1"></i>Veículo</span>
                <strong><?php echo e($ciVeiculo); ?></strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-tachometer-alt mr-1"></i>Quilometragem</span>
                <strong><?php echo e(number_format((float)($ci->quilometragem_atual ?? 0), 0, ',', '.')); ?> km</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                <strong><?php echo e($ci->nivel_combustivel ?? '—'); ?></strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-calendar mr-1"></i>Saída</span>
                <strong>
                    <?php echo e($ci->data_saida ? \Carbon\Carbon::parse($ci->data_saida)->format('d/m/Y') : '—'); ?>

                    <?php echo e($ci->hora_saida ? substr($ci->hora_saida, 0, 5) : ''); ?>

                </strong>
            </div>
            <?php if($ciAssinado && $ci->data_assinatura): ?>
            <div class="rs-ci-detail">
                <span><i class="fas fa-pen-nib mr-1"></i>Assinado em</span>
                <strong><?php echo e(\Carbon\Carbon::parse($ci->data_assinatura)->format('d/m/Y H:i')); ?></strong>
            </div>
            <?php endif; ?>
            <?php if(!empty($ci->notas_saida)): ?>
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-sticky-note mr-1"></i>Notas de saída</span>
                <strong><?php echo e($ci->notas_saida); ?></strong>
            </div>
            <?php endif; ?>
            <?php if(!empty($ci->observacoes_saida)): ?>
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-comment-alt mr-1"></i>Observações</span>
                <strong><?php echo e($ci->observacoes_saida); ?></strong>
            </div>
            <?php endif; ?>
        </div>

        <div class="rs-ci-card__actions">
            <?php if($ciAssinado): ?>
                <a href="<?php echo e(route('reserva.minhas.vercontratocheckin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                    <i class="fas fa-eye"></i> Ver contrato
                </a>
            <?php else: ?>
                <?php if(!$checkoutJaRegistrado): ?>
                    <a href="<?php echo e(route('reserva.minhas.assinar-checkin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" class="rs-ci-btn rs-ci-btn--sign">
                        <i class="fas fa-signature"></i> Assinar
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if($ci->houve_upgrade_categoria): ?>
    <div class="rs-card" style="border-left:4px solid #fd7e14">
        <div class="rs-card__header">
            <i class="fas fa-arrow-circle-up rs-card__icon" style="color:#fd7e14"></i>
            <h2>Upgrade de Categoria</h2>
        </div>
        <div class="rs-info-rows">
            <div class="rs-info-row">
                <span>Categoria Reservada</span>
                <strong><?php echo e($ci->grupoFrotaOriginal?->nome ?? 'Grupo #' . $ci->grupo_frota_original_id); ?></strong>
            </div>
            <div class="rs-info-row">
                <span>Veiculo Entregue</span>
                <strong><?php echo e($ci->veiculo?->nome ?? '—'); ?></strong>
            </div>
            <div class="rs-info-row">
                <span>Motivo</span>
                <strong><?php echo e($ci->motivo_upgrade ?? '—'); ?></strong>
            </div>
            <div class="rs-info-row">
                <span>Operador</span>
                <strong><?php echo e($ci->usuarioUpgrade?->nome ?? '—'); ?></strong>
            </div>
            <div class="rs-info-row">
                <span>Data/Hora</span>
                <strong><?php echo e($ci->data_hora_upgrade?->format('d/m/Y H:i') ?? '—'); ?></strong>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-camera rs-card__icon"></i>
            <h2>Fotos do Veículo no Check-in</h2>
        </div>
        <div class="row rs-photo-grid">
            <?php $__currentLoopData = $fotosPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $fotoUrl = $path ? asset('storage/' . $path) : null; ?>
                <div class="col-6 col-md-2 rs-photo-item">
                    <?php if($fotoUrl): ?>
                        <div class="rs-photo-frame js-lightbox-trigger"
                             data-src="<?php echo e($fotoUrl); ?>" data-label="<?php echo e($label); ?>">
                            <img src="<?php echo e($fotoUrl); ?>" alt="Foto <?php echo e($label); ?>" class="rs-photo-thumb">
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
        <span>Nenhum contrato de check-in cadastrado para esta reserva.</span>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/cliente_checkin.blade.php ENDPATH**/ ?>