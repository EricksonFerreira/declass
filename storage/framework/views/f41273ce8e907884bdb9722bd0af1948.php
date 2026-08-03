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
            <a href="<?php echo e(route('reserva.verContratoCheckin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                <i class="fas fa-eye"></i> Ver contrato
            </a>
            <?php if(!$ciAssinado && !$checkoutJaRegistrado): ?>
                <?php if($pagamentoEmDiaAssinatura): ?>
                    <a href="<?php echo e(route('reserva.assinarContratoCheckin', $reserva->codigoReserva?->codigo ?? $reserva->id)); ?>" class="rs-ci-btn rs-ci-btn--sign">
                        <i class="fas fa-signature"></i> Assinar
                    </a>
                <?php else: ?>
                    <span class="rs-ci-btn rs-ci-btn--disabled"
                          data-toggle="tooltip"
                          title="Pagamento insuficiente. Restam R$ <?php echo e(number_format($saldoPendenteAssinatura, 2, ',', '.')); ?> para liberar a assinatura.">
                        <i class="fas fa-lock"></i> Assinar
                    </span>
                    <a href="<?php echo e(route('reserva.pagamento.efetuar', ['id' => $reserva->codigoReserva?->codigo ?? ('RES-' . $reserva->id)])); ?>" class="rs-ci-btn rs-ci-btn--sign">
                        <i class="fas fa-credit-card"></i> Pagar restante
                    </a>
                <?php endif; ?>
                <a href="<?php echo e(route('reserva.checkin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" class="rs-ci-btn rs-ci-btn--edit">
                    <i class="fas fa-edit"></i> Editar contrato
                </a>
            <?php endif; ?>
            <?php if(!$checkoutJaRegistrado): ?>
                <button type="button" class="rs-ci-btn rs-ci-btn--delete js-excluir-contrato-checkin"
                        data-url="<?php echo e(route('reserva.checkin.excluirContrato', $reserva->id)); ?>"
                        data-assinado="<?php echo e($ciAssinado ? '1' : '0'); ?>">
                    <i class="fas fa-trash"></i>
                    <?php echo e($ciAssinado ? 'Excluir e recriar' : 'Excluir contrato'); ?>

                </button>
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
        <?php if($pagamentoEfetuado): ?>
            <a href="<?php echo e(route('reserva.checkin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id])); ?>" class="rs-btn rs-btn--outline mt-2">
                <i class="fas fa-plus"></i> Criar check-in
            </a>
        <?php else: ?>
            <div class="mt-3 d-flex align-items-center justify-content-center" style="gap:.5rem;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:.6rem 1rem;font-size:.83rem;color:#92400e;font-weight:600">
                <i class="fas fa-lock"></i> Pagamento necessário para criar o check-in
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/common_checkin.blade.php ENDPATH**/ ?>