<?php
    $verde = '#059669'; $cinza = '#64748b'; $laranja = '#ea580c';
    $disp = collect($veiculos)->where('disponivel', true);
    $ind = collect($veiculos)->where('disponivel', false);
?>

<div id="diagRoot" data-count="<?php echo e(count($veiculos)); ?>">
    <div class="diag-summary d-flex align-items-center gap-2 mb-3 flex-wrap">
        <span class="badge badge-success p-2" style="font-size:.82rem;"><i class="fas fa-check-circle mr-1"></i><?php echo e($disp->count()); ?> disponivel</span>
        <span class="badge badge-secondary p-2" style="font-size:.82rem;"><i class="fas fa-times-circle mr-1"></i><?php echo e($ind->count()); ?> indisponivel</span>
        <span class="badge badge-info p-2" style="font-size:.82rem;"><i class="fas fa-car mr-1"></i><?php echo e(count($veiculos)); ?> total</span>
        <?php if($reserva_info ?? null): ?>
            <a href="<?php echo e($reserva_info['url'] ?? '#'); ?>" target="_blank" class="badge badge-light p-2" style="font-size:.82rem;border:1px solid #dee2e6;">
                <i class="fas fa-external-link-alt mr-1"></i>Reserva #<?php echo e($reserva_info['id'] ?? ''); ?>

            </a>
        <?php endif; ?>
    </div>

    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-0"><label class="small mb-1">Nome</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="nome" placeholder="Ex: Corolla" oninput="filtrarDiagnostico()"></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Placa</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="placa" placeholder="ABC-1234" oninput="filtrarDiagnostico()"></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Disponibilidade</label><select class="form-control form-control-sm diag-filtro" data-col="disponivel" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="1">Disponivel</option><option value="0">Indisponivel</option></select></div>
                <div class="col-md-4 form-group mb-0 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limparFiltrosDiag()" title="Limpar"><i class="fas fa-eraser"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-info ml-1" onclick="toggleFiltrosAvancados()"><i class="fas fa-sliders-h"></i> <span>Filtros Avancados</span></button>
                </div>
            </div>
            <div id="diagFiltrosAvancados" class="row" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;">
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Modelo</label><input type="text" class="form-control form-control-sm diag-filtro" data-col="modelo" placeholder="Ex: Civic" oninput="filtrarDiagnostico()"></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Estacao</label><select class="form-control form-control-sm diag-filtro" data-col="estacao" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Status Veiculo</label><select class="form-control form-control-sm diag-filtro" data-col="status" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Reservas</label><select class="form-control form-control-sm diag-filtro" data-col="reservas" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
                <div class="col-md-2 form-group mb-0"><label class="small mb-1">Movimentacoes</label><select class="form-control form-control-sm diag-filtro" data-col="movimentacoes" onchange="filtrarDiagnostico()"><option value="">Todos</option><option value="ok">OK</option><option value="fail">Falhou</option></select></div>
            </div>
        </div>
    </div>

    <div id="diagLista" class="diag-lista">
        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $regras = $v['regras'] ?? [];
                $r = [];
                foreach ($regras as $rr) { $r[$rr['nome']] = $rr; }
                $ordem = ['EstaÃ§Ã£o','Disponibilidade','Status','Reservas','MovimentaÃ§Ãµes'];
                $isDisp = $v['disponivel'];
                $grpOk = count(array_filter($regras, fn($rr) => $rr['ok']));
                $grpTot = count($regras);
                $accent = $isDisp ? $verde : $cinza;
                $borderC = $isDisp ? '#86efac' : '#e5eaf2';
                $sTxt = $isDisp ? 'Disponivel' : 'Indisponivel';
                $sBg = $isDisp ? 'rgba(16,185,129,.15)' : 'rgba(100,116,139,.13)';
                $sCo = $isDisp ? $verde : $cinza;
                $rStatus = [];
                foreach ($regras as $rr) { $rStatus[$rr['nome']] = $rr['ok'] ? '1' : '0'; }
                $parts = preg_split('/\s+/', trim($v['nome'] ?? '??'));
                $initials = mb_substr($parts[0] ?? '?', 0, 1) . mb_substr($parts[1] ?? '', 0, 1);
            ?>

            <div class="diag-card"
                data-nome="<?php echo e(strtolower($v['nome'] ?? '')); ?>"
                data-placa="<?php echo e(strtolower($v['placa'] ?? '')); ?>"
                data-modelo="<?php echo e(strtolower($v['modelo'] ?? '')); ?>"
                data-disponivel="<?php echo e($isDisp ? '1' : '0'); ?>"
                data-estacao="<?php echo e($rStatus['Estacao'] ?? '0'); ?>"
                data-status="<?php echo e($rStatus['Status'] ?? '0'); ?>"
                data-reservas="<?php echo e($rStatus['Reservas'] ?? '0'); ?>"
                data-movimentacoes="<?php echo e($rStatus['Movimentacoes'] ?? '0'); ?>"
                style="--diag-border: <?php echo e($borderC); ?>; --diag-accent: <?php echo e($accent); ?>;">
                <div class="diag-card__shell">
                    <div class="diag-card__topbar"></div>
                    <div class="diag-card__head">
                        <div class="diag-card__avatar"><?php echo e($initials); ?></div>
                        <div class="diag-card__identity">
                            <div class="diag-card__eyebrow">#<?php echo e($v['id']); ?> · <?php echo e($v['placa'] ?? '---'); ?></div>
                            <div class="diag-card__title"><?php echo e($v['nome']); ?></div>
                            <div class="diag-card__subtitle"><?php echo e($v['modelo'] ?? '---'); ?> · <?php echo e($v['cor'] ?? '---'); ?></div>
                        </div>
                        <div class="diag-card__status">
                            <span class="diag-pill" style="background:<?php echo e($sBg); ?>;color:<?php echo e($sCo); ?>;"><?php echo e($sTxt); ?></span>
                            <span class="diag-pill diag-pill--score" style="background:<?php echo e($grpOk === $grpTot ? 'rgba(16,185,129,.12)' : 'rgba(245,158,11,.15)'); ?>;color:<?php echo e($grpOk === $grpTot ? $verde : $laranja); ?>;"><?php echo e($grpOk); ?>/<?php echo e($grpTot); ?></span>
                        </div>
                    </div>

                    <div class="diag-card__rules">
                        <?php $__currentLoopData = $ordem; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!isset($r[$nk])): ?> <?php continue; ?> <?php endif; ?>
                            <?php
                                $rr = $r[$nk];
                                $ok = $rr['ok'];
                                $icC = $ok ? $verde : '#dc2626';
                            ?>
                            <div class="diag-rule">
                                <i class="fas fa-<?php echo e($ok ? 'check-circle' : 'times-circle'); ?>" style="color:<?php echo e($icC); ?>;"></i>
                                <div class="diag-rule__content">
                                    <strong><?php echo e($nk); ?></strong>
                                    <div><?php echo $rr['info'] ?? ''; ?></div>
                                    <?php if(!$ok && ($rr['solucao'] ?? null)): ?>
                                        <div class="diag-rule__solution"><i class="fas fa-lightbulb mr-1"></i><?php echo e($rr['solucao']); ?></div>
                                    <?php endif; ?>
                                    <?php if($rr['itens'] ?? null): ?>
                                        <div class="diag-rule__links">
                                            <?php $__currentLoopData = $rr['itens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $ic2 = $item['tipo'] === 'reserva' ? 'fa-calendar-alt' : 'fa-truck-moving'; ?>
                                                <a href="<?php echo e($item['url']); ?>" target="_blank">
                                                    <i class="fas <?php echo e($ic2); ?>"></i><?php echo e($item['label']); ?>

                                                </a>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="diag-card__footer">
                        <span><i class="fas fa-check-circle" style="color:<?php echo e($verde); ?>;"></i><?php echo e($grpOk); ?> ok</span>
                        <span><i class="fas fa-times-circle" style="color:#dc2626;"></i><?php echo e($grpTot - $grpOk); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/diagnostico.blade.php ENDPATH**/ ?>