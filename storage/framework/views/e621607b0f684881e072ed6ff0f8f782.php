<div class="rs-checkin-layout">
    
    <div class="rs-card">
        <div class="rs-card__header rs-card__header--between">
            <div class="rs-card__header-left">
                <i class="fas fa-car-crash rs-card__icon" style="color:#f59e0b"></i>
                <h2>Avarias Registradas</h2>
            </div>
            <?php if(($danosReserva->count() ?? 0) > 0): ?>
                <span class="rs-status-badge rs-status-badge--sm is-warning">
                    <?php echo e($danosPendentes->count()); ?> pendente(s)
                </span>
            <?php endif; ?>
        </div>

<?php if(($danosReserva->count() ?? 0) > 0): ?>
    <?php
        $reservaCodigoAtual = $reserva->codigoReserva?->id ?? null;
        $reservaIdAtual = $reserva->id ?? null;
    ?>
    <div class="rs-info-rows mb-3">
                <div class="rs-info-row">
                    <span><i class="fas fa-list"></i>Total registradas</span>
                    <strong><?php echo e($danosReserva->count()); ?></strong>
                </div>
                <div class="rs-info-row">
                    <span><i class="fas fa-receipt"></i>Cobranças</span>
                    <strong><?php echo e($totalCobrancasAvaria); ?></strong>
                </div>
            </div>

            <div class="rs-damage-block rs-damage-block--amber">
                <div class="rs-damage-block__label">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Danos Nesta Reserva</strong>
                        <span>Avarias registradas no check-in ou nesta devolução</span>
                    </div>
                </div>

                <div class="rs-damage-table-wrap">
                    <table class="rs-damage-table" id="danos-detectados-table">
                        <thead>
                            <tr>
                                <th>Localização</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Etapa</th>
                                <th>Custo Est.</th>
                                <th>Imagem</th>
                            </tr>
                        </thead>
                        <tbody id="danos-detectados-container">
                <?php $__currentLoopData = $danosReserva; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $origemDano = !empty($d->contrato_id) ? 'Check-in' : 'Check-out';
                        $pertenceReservaAtual = (int) ($d->reserva_id ?? 0) === (int) $reservaIdAtual
                            || (int) ($d->reserva_codigo_id ?? 0) === (int) $reservaCodigoAtual;
                        $rotuloReserva = $pertenceReservaAtual ? 'Desta reserva' : 'Outra reserva';
                        $imagensDano = $d->imagens
                            ->map(function ($img) {
                                $path = $img->caminho_imagem ?: $img->nome_imagem;
                                return [
                                    'url' => $path
                                        ? asset('storage/' . ltrim(str_replace('\\', '/', $path), '/'))
                                        : null,
                                ];
                            })
                            ->filter(function ($img) {
                                return !empty($img['url']);
                            })
                            ->values();
                        $danoPayload = [
                            'id' => $d->id,
                            'localizacao' => strtoupper((string) ($d->local_dano ?? $d->localizacao_problema ?? '-')),
                            'descricao' => $d->observacoes,
                            'tipo_dano' => $d->tipo_dano,
                            'categoria' => $d->dano?->categoria?->nome,
                            'local_dano' => $d->local_dano,
                            'localizacao_problema' => $d->localizacao_problema,
                            'custo_estimado' => $d->custo_estimado,
                            'quilometragem_veiculo' => $d->quilometragem_veiculo,
                            'created_at' => $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i') : null,
                            'correcao_descricao' => $d->correcao_descricao,
                            'correcao_data' => $d->correcao_data ? \Carbon\Carbon::parse($d->correcao_data)->format('d/m/Y H:i') : null,
                            'correcao_usuario' => $d->usuarioCorrecao?->nome,
                            'correcao_realizada' => (bool) $d->correcao_data,
                            'imagens' => $imagensDano->all(),
                            'contrato_id' => $d->contrato_id,
                        ];
                    ?>
                    <tr class="js-dano-detalhe-trigger rs-damage-table__row" data-dano='<?php echo json_encode($danoPayload, 15, 512) ?>'>
                        <td>
                            <strong><?php echo e(strtoupper($d->local_dano ?? $d->localizacao_problema ?? '-')); ?></strong><br>
                            <small class="text-muted"><?php echo e($d->dano?->categoria?->nome ?? '—'); ?> · <?php echo e($d->localizacao_problema ?? '—'); ?></small>
                        </td>
                        <td>
                            <?php echo e($d->observacoes ?: '—'); ?>

                            <?php if($d->quilometragem_veiculo): ?>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-tachometer-alt mr-1"></i>KM <?php echo e(number_format((float) $d->quilometragem_veiculo, 0, ',', '.')); ?>

                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>
                                <span class="badge badge-light text-dark border"><?php echo e($d->tipo_dano ?: '—'); ?></span>
                                <?php if($d->tipo_dano): ?>
                                    <small class="d-block text-muted mt-1"><?php echo e($d->tipo_dano); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1">
                                    <?php if($d->correcao_data): ?>
                                        <span class="badge badge-success">Corrigido</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pendente</span>
                                    <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column" style="gap:4px;">
                                <span class="badge <?php echo e($origemDano === 'Check-in' ? 'badge-info' : 'badge-danger'); ?>">
                                    <i class="fas <?php echo e($origemDano === 'Check-in' ? 'fa-sign-in-alt' : 'fa-sign-out-alt'); ?> mr-1"></i>
                                    <?php echo e($origemDano); ?>

                                </span>
                                <small class="text-muted"><?php echo e($rotuloReserva); ?></small>
                            </div>
                        </td>
                        <td>
                            <strong>R$ <?php echo e(number_format((float) ($d->custo_estimado ?? 0), 2, ',', '.')); ?></strong>
                        </td>
                        <td>
                            <div class="rs-damage-thumbs">
                                <?php $__empty_1 = true; $__currentLoopData = $imagensDano; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <a href="<?php echo e($img['url']); ?>" target="_blank" rel="noopener" onclick="event.stopPropagation()">
                                        <img src="<?php echo e($img['url']); ?>" alt="Foto do dano" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;margin-right:4px">
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="rs-damage-empty-inline">
                                        <i class="far fa-image"></i> Sem imagem
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="rs-empty">
                <i class="fas fa-check-circle" style="color:#10b981"></i>
                <span>Nenhuma avaria registrada nesta reserva.</span>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/common_danos.blade.php ENDPATH**/ ?>