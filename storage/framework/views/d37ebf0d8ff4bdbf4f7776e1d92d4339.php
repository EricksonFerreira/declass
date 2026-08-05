

<?php $__env->startSection('title', 'Detalhes da Reserva'); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="rs-page">

    
    <div class="rs-hero rs-hero--<?php echo e($statusReservaSlug); ?>">
        <div class="rs-hero__accent"></div>
        <div class="rs-hero__body">
            <div class="rs-hero__left">
                <div class="rs-hero__eyebrow">
                    <i class="fas fa-ticket-alt"></i>
                    <span style="font-size:1.8rem;font-weight:900;letter-spacing:.04em;color:#fff;"><?php echo e($codigoReservaExibicao); ?></span>
                </div>
                <h1 class="rs-hero__title"><?php echo e($titulo); ?></h1>
                <div class="rs-hero__meta">
                    <span><i class="fas fa-building"></i><?php echo e($reserva->empresa->nome ?? 'Empresa'); ?></span>
                    <span><i class="fas fa-calendar-plus"></i>Criada em <?php echo e($createdAt); ?></span>
                    <span><i class="fas fa-user-tie"></i>
                        <?php if($reserva->origem_cadastro === 'site'): ?>
                            Site
                        <?php else: ?>
                            <?php echo e($reserva->criadoPor?->nome ?? $reserva->criadoPor?->name ?? 'Colaborador'); ?>

                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <div class="rs-hero__right">
                <span class="rs-status-badge <?php echo e($statusReservaClass); ?>">
                    <i class="<?php echo e($statusIcon); ?>"></i>
                    <?php echo e(strtoupper($statusReservaNome)); ?>

                </span>
                <div class="rs-hero__kpis">
                    <div class="rs-kpi">
                        <span>Valor total</span>
                        <strong>R$&nbsp;<?php echo e(number_format($valorTotal, 2, ',', '.')); ?></strong>
                    </div>
                    <div class="rs-kpi-divider"></div>
                    <div class="rs-kpi">
                        <span>Duração</span>
                        <strong><?php echo e($diasReserva); ?>&nbsp;<?php echo e($diasReserva === 1 ? 'dia' : 'dias'); ?></strong>
                    </div>
                    <?php if($percentualDesconto > 0): ?>
                        <div class="rs-kpi-divider"></div>
                        <div class="rs-kpi rs-kpi--accent">
                            <span>Desconto</span>
                            <strong><?php echo e(number_format($percentualDesconto, 0)); ?>%</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php echo $__env->make('reserva.components.botoes_acoes_show_reserva', ['moduloNfse' => []], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    

    
    <?php if (isset($component)) { $__componentOriginal5e1dba3797e20c01833c533aa71a910c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5e1dba3797e20c01833c533aa71a910c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.reserva.etapas','data' => ['reserva' => $reserva]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('reserva.etapas'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['reserva' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reserva)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5e1dba3797e20c01833c533aa71a910c)): ?>
<?php $attributes = $__attributesOriginal5e1dba3797e20c01833c533aa71a910c; ?>
<?php unset($__attributesOriginal5e1dba3797e20c01833c533aa71a910c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5e1dba3797e20c01833c533aa71a910c)): ?>
<?php $component = $__componentOriginal5e1dba3797e20c01833c533aa71a910c; ?>
<?php unset($__componentOriginal5e1dba3797e20c01833c533aa71a910c); ?>
<?php endif; ?>

    
    <div class="rs-tabs-wrapper">
        <ul class="nav nav-tabs rs-nav-tabs" id="reservaMainTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-visao-geral" data-toggle="tab" href="#pane-visao-geral" role="tab">
                    <i class="fas fa-home"></i>
                    <span>Visão Geral</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-checkin" data-toggle="tab" href="#pane-checkin" role="tab">
                    <i class="fas fa-file-signature"></i>
                    <span>Check-in</span>
                    <?php if($reserva->contratoCheckin): ?>
                        <?php $ciAssinadoTab = $reserva->contratoCheckin->assinado === 'S'; ?>
                        <span class="rs-tab-badge <?php echo e($ciAssinadoTab ? 'rs-tab-badge--green' : 'rs-tab-badge--amber'); ?>">
                            <?php echo e($ciAssinadoTab ? 'Assinado' : 'Pendente'); ?>

                        </span>
                    <?php endif; ?>
                </a>
            <?php if($reserva->contratoCheckin): ?>
            <li class="nav-item">
                <a class="nav-link" id="tab-checkout" data-toggle="tab" href="#pane-checkout" role="tab">
                    <i class="fas fa-flag-checkered"></i>
                    <span>Check-out</span>
                    <?php if($reserva->contratoCheckout): ?>
                        <?php
                            $co = $reserva->contratoCheckout;
                            $coAssinado = ! empty($co->assinatura) || ! empty($co->data_assinatura) || $co->assinado === 'S';
                        ?>
                        <span class="rs-tab-badge <?php echo e($coAssinado ? 'rs-tab-badge--green' : 'rs-tab-badge--amber'); ?>">
                            <?php echo e($coAssinado ? 'Assinado' : 'Pendente'); ?>

                        </span>
                    <?php else: ?>
                        <span class="rs-tab-badge rs-tab-badge--amber">Pendente</span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" id="tab-financeiro" data-toggle="tab" href="#pane-financeiro" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Financeiro</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" id="tab-danos" data-toggle="tab" href="#pane-danos" role="tab">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Danos</span>
                    <?php if($totalDano > 0): ?>
                        <span class="rs-tab-badge rs-tab-badge--red"><?php echo e($totalDano); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            
            <li class="nav-item">
                <a class="nav-link" id="tab-multas" data-toggle="tab" href="#pane-multas" role="tab">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Multas</span>
                    <?php if($totalMultas > 0): ?>
                        <span class="rs-tab-badge rs-tab-badge--red"><?php echo e($totalMultas); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" id="tab-sinistros" data-toggle="tab" href="#pane-sinistros" role="tab">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Sinistros</span>
                    <?php if($totalSinistros > 0): ?>
                        <span class="rs-tab-badge rs-tab-badge--red"><?php echo e($totalSinistros); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-historico" data-toggle="tab" href="#pane-historico" role="tab">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                    <span class="rs-tab-badge rs-tab-badge--slate d-none" id="historicoCount"></span>
                </a>
            </li>
        </ul>

        <div class="tab-content rs-tab-content" id="reservaMainTabContent">

            
            <div class="tab-pane fade show active" id="pane-visao-geral" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_visao_geral', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            
            <div class="tab-pane fade" id="pane-checkin" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_checkin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            
            <div class="tab-pane fade" id="pane-checkout" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_checkout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            
            <div class="tab-pane fade" id="pane-danos" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_danos', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            
            <div class="tab-pane fade" id="pane-financeiro" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_financeiro', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>

            
            <div class="tab-pane fade" id="pane-danos" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_danos', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            
            <div class="tab-pane fade" id="pane-multas" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_multas', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            
            <div class="tab-pane fade" id="pane-sinistros" role="tabpanel">
                <?php echo $__env->make('reserva.partials.common_sinistros', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            

            
            <div class="tab-pane fade" id="pane-historico" role="tabpanel">
                <div class="rs-card rs-historico-card">
                    <div class="rs-card__header">
                        <i class="fas fa-history rs-card__icon"></i>
                        <h2>Histórico da Reserva</h2>
                        <span class="ml-auto badge badge-secondary" id="historicoCountPane">—</span>
                    </div>
                    <div id="historicoLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Carregando histórico...
                    </div>
                    <div id="historicoTimeline" class="rs-timeline d-none"></div>
                </div>
            </div>

        </div>
    </div>

    
    <?php if(isset($calcao) && $calcao?->podeCapturar()): ?>
    <div class="modal fade" id="modalCapturarCalcao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-usd mr-1"></i> Capturar Calcao</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Valor a capturar (max R$ <?php echo e(number_format((float)$calcao->valor_autorizado, 2, ',', '.')); ?>)</label>
                        <input type="number" step="0.01" id="valor_captura_input" class="form-control"
                               max="<?php echo e($calcao->valor_autorizado); ?>" min="0.01"
                               value="<?php echo e($calcao->valor_autorizado); ?>">
                    </div>
                    <div class="form-group">
                        <label>Justificativa <span class="text-danger">*</span></label>
                        <textarea id="justificativa_captura_input" class="form-control" rows="3" placeholder="Motivo da captura..."></textarea>
                    </div>
                    <div id="calcao-capture-status"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn-confirmar-captura">
                        <i class="fas fa-check mr-1"></i> Confirmar Captura
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('calcao.autorizar')): ?>
    <div class="modal fade" id="modalCalcaoPresencial" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cash-register mr-1"></i> Calção Presencial</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Método <span class="text-danger">*</span></label>
                        <select id="calcao_presencial_metodo" class="form-control">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">Pix</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor (opcional)</label>
                        <input type="number" step="0.01" min="0.01" id="calcao_presencial_valor" class="form-control"
                               placeholder="Deixe vazio para usar o valor calculado"
                               value="<?php echo e(number_format($valorTotal, 2, '.', '')); ?>">
                    </div>
                    <div class="form-group mb-0">
                        <label>Observação (opcional)</label>
                        <textarea id="calcao_presencial_obs" class="form-control" rows="3" placeholder="Ex.: Recebido no balcão"></textarea>
                    </div>
                    <div id="calcao-presencial-status" class="small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-salvar-calcao-presencial">
                        <i class="fas fa-check mr-1"></i> Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if(isset($calcao) && empty($calcao->payment_intent_id) && ($calcao->status ?? '') !== 'cancelado'): ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('calcao.cancelar')): ?>
    <div class="modal fade" id="modalDevolverCalcaoPresencial" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-heart mr-1"></i> Devolver Calção Presencial</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-2">
                        Confirma a devolução do calção presencial? Esta ação registra uma transação negativa.
                    </div>
                    <div class="form-group mb-0">
                        <label>Observação (opcional)</label>
                        <textarea id="calcao_devolver_obs" class="form-control" rows="3" placeholder="Ex.: Devolvido no checkout"></textarea>
                    </div>
                    <div id="calcao-devolver-status" class="small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-devolver-calcao-presencial">
                        <i class="fas fa-check mr-1"></i> Devolver
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    
    <div class="modal fade" id="modalDetalhesLog" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rs-dano-modal">
                <div class="modal-header rs-dano-modal__header">
                    <div>
                        <div class="rs-dano-modal__eyebrow">Detalhes do registro</div>
                        <h5 class="modal-title" id="logModalAcao"></h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-4 pb-3" style="border-bottom:2px solid #e2e8f0;gap:16px;flex-wrap:wrap">
                        <span class="small" style="display:flex;align-items:center;gap:6px"><i class="fas fa-user text-muted"></i> <strong id="logModalUsuario"></strong></span>
                        <span class="small" style="display:flex;align-items:center;gap:6px"><i class="fas fa-clock text-muted"></i> <span id="logModalData"></span></span>
                        <span class="ml-auto"><span class="badge" id="logModalNivel"></span></span>
                    </div>
                    <div id="logModalDetalhes"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div id="rs-lightbox" class="rs-lightbox d-none" role="dialog" aria-modal="true">
        <div class="rs-lightbox__backdrop" id="rs-lightbox-backdrop"></div>
        <div class="rs-lightbox__inner">
            <div class="rs-lightbox__label" id="rs-lightbox-label"></div>
            <img src="" alt="" id="rs-lightbox-img" class="rs-lightbox__img">
            <button class="rs-lightbox__close" id="rs-lightbox-close" aria-label="Fechar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
const csrfToken = <?php echo json_encode(csrf_token(), 15, 512) ?>;
const reservaId = <?php echo json_encode($reserva->id, 15, 512) ?>;

// ── Liberar calção ──
function liberarCalcao(calcaoId) {
    if (!confirm('Confirma a liberação do calção? O valor será devolvido ao cartão do cliente.')) return;
    fetch(<?php echo json_encode(route('reserva.calcao.liberar', $reserva->codigoReserva->codigo), 512) ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ observacao: 'Liberado no check-out' })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else alert(d.error ?? 'Erro ao liberar calção.');
    });
}

// ── Capturar calção ──
// -- Calcao presencial --
document.getElementById('btn-salvar-calcao-presencial')?.addEventListener('click', function () {
    const metodo = document.getElementById('calcao_presencial_metodo')?.value;
    const valor  = document.getElementById('calcao_presencial_valor')?.value;
    const obs    = document.getElementById('calcao_presencial_obs')?.value;
    const statusEl = document.getElementById('calcao-presencial-status');

    if (!metodo) {
        if (statusEl) statusEl.textContent = 'Selecione um método.';
        return;
    }

    if (statusEl) statusEl.textContent = 'Registrando...';

    fetch(<?php echo json_encode(route('reserva.calcao.presencial', $reserva->codigoReserva->codigo), 512) ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ metodo, valor: valor || null, observacao: obs || null })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else if (statusEl) statusEl.textContent = d.error ?? 'Erro ao registrar.';
    })
    .catch(() => { if (statusEl) statusEl.textContent = 'Erro ao registrar.'; });
});

document.getElementById('btn-devolver-calcao-presencial')?.addEventListener('click', function () {
    const obs = document.getElementById('calcao_devolver_obs')?.value;
    const statusEl = document.getElementById('calcao-devolver-status');
    if (statusEl) statusEl.textContent = 'Processando...';

    fetch(<?php echo json_encode(route('reserva.calcao.presencial.devolver', $reserva->codigoReserva->codigo), 512) ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ observacao: obs || null })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else if (statusEl) statusEl.textContent = d.error ?? 'Erro ao devolver.';
    })
    .catch(() => { if (statusEl) statusEl.textContent = 'Erro ao devolver.'; });
});

document.getElementById('btn-confirmar-captura')?.addEventListener('click', function () {
    const valor = document.getElementById('valor_captura_input')?.value;
    const just  = document.getElementById('justificativa_captura_input')?.value;
    const statusEl = document.getElementById('calcao-capture-status');

    if (!just || just.trim().length < 5) {
        statusEl.innerHTML = '<div class="alert alert-warning">Informe uma justificativa de ao menos 5 caracteres.</div>';
        return;
    }

    this.disabled = true;
    fetch(<?php echo json_encode(route('reserva.calcao.capturar', $reserva->codigoReserva->codigo), 512) ?>, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ valor_captura: valor, justificativa: just })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            statusEl.innerHTML = '<div class="alert alert-success">Capturado com sucesso.</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            statusEl.innerHTML = '<div class="alert alert-danger">' + (d.error ?? 'Erro ao capturar.') + '</div>';
            this.disabled = false;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<div class="modal fade" id="modalDetalheDanoReserva" tabindex="-1" role="dialog" aria-labelledby="modalDetalheDanoReservaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content rs-dano-modal">
            <div class="modal-header rs-dano-modal__header">
                <div>
                    <div class="rs-dano-modal__eyebrow">Detalhes do dano</div>
                    <h5 class="modal-title" id="modalDetalheDanoReservaLabel">Dano registrado no check-in</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="rs-dano-modal__grid" id="rsDanoModalInfos"></div>
                <div class="rs-dano-modal__correcao d-none" id="rsDanoModalCorrecaoWrap">
                    <h6>Correção</h6>
                    <div id="rsDanoModalCorrecao"></div>
                </div>
                <div class="rs-dano-modal__imagens d-none" id="rsDanoModalImagensWrap">
                    <h6>Fotos do dano</h6>
                    <div class="rs-dano-modal__thumbs" id="rsDanoModalImagens"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('css'); ?>
<style>
    .content-wrapper {
        background: #f0f4f9;
    }

    /* ═══════════════════════════
       PAGE
    ═══════════════════════════ */
    .rs-page {
        display: grid;
        gap: 20px;
        padding-bottom: 32px;
    }

    /* ═══════════════════════════
       HERO
    ═══════════════════════════ */
    .rs-hero {
        position: relative;
        border-radius: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.20);
    }

    .rs-hero__accent {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    }

    .rs-hero--confirmada .rs-hero__accent {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .rs-hero--cancelada .rs-hero__accent {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    .rs-hero--criada .rs-hero__accent,
    .rs-hero--pendente .rs-hero__accent {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .rs-hero__body {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 30px;
        flex-wrap: wrap;
    }

    .rs-hero__left {
        display: grid;
        gap: 8px;
    }

    .rs-hero__eyebrow {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, 0.9);
    }

    .rs-hero__title {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #fff;
        line-height: 1.15;
    }

    .rs-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        color: rgba(148, 163, 184, 0.85);
        font-size: 0.84rem;
    }

    .rs-hero__meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rs-hero__right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 16px;
    }

    .rs-hero__kpis {
        display: flex;
        align-items: center;
        gap: 0;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        overflow: hidden;
    }

    .rs-kpi {
        padding: 14px 20px;
        text-align: center;
    }

    .rs-kpi span {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(148, 163, 184, 0.8);
        margin-bottom: 4px;
    }

    .rs-kpi strong {
        display: block;
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
    }

    .rs-kpi--accent strong {
        color: #fbbf24;
    }

    .rs-kpi-divider {
        width: 1px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
    }

    /* ═══════════════════════════
       STATUS BADGE
    ═══════════════════════════ */
    .rs-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rs-status-badge--sm {
        padding: 5px 10px;
        font-size: 0.68rem;
    }

    .rs-status-badge.is-success {
        background: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .rs-status-badge.is-danger {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .rs-status-badge.is-warning {
        background: rgba(245, 158, 11, 0.18);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .rs-status-badge.is-neutral {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    /* ═══════════════════════════
       ALERT
    ═══════════════════════════ */
    .rs-alert {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 16px;
        flex-wrap: wrap;
    }

    .rs-alert--warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    .rs-alert__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #fef3c7;
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .rs-alert__content {
        flex: 1;
    }

    .rs-alert__content strong {
        display: block;
        color: #92400e;
        font-size: 0.88rem;
        margin-bottom: 2px;
    }

    .rs-alert__content p {
        margin: 0;
        color: #b45309;
        font-size: 0.82rem;
    }

    /* ═══════════════════════════
       FLOW STEPS (Etapas)
    ═══════════════════════════ */
    .rs-flow {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }
    .rs-flow__step {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #f8fafc;
        padding: 4px 10px;
        font-size: .72rem;
        white-space: nowrap;
    }
    .rs-flow__step.is-done { border-color: #bbf7d0; background: #f0fdf4; }
    .rs-flow__step.is-current { border-color: #fde68a; background: #fffbeb; }
    .rs-flow__step.is-cancelled { border-color: #fecaca; background: #fef2f2; }
    .rs-flow__icon { font-size: .65rem; }
    .rs-flow__step.is-done .rs-flow__icon { color: #16a34a; }
    .rs-flow__step.is-current .rs-flow__icon { color: #d97706; }
    .rs-flow__step.is-cancelled .rs-flow__icon { color: #dc2626; }
    .rs-flow__step:not(.is-done):not(.is-current) .rs-flow__icon { color: #94a3b8; }
    .rs-flow__label { font-weight: 800; color: #64748b; }
    .rs-flow__value { font-weight: 700; color: #0f172a; }

    /* ═══════════════════════════
       TABS WRAPPER (Modern & Robust Reset)
    ═══════════════════════════ */
    .rs-tabs-wrapper {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
    }

    ul.rs-nav-tabs,
    .rs-nav-tabs.nav-tabs {
        background: #f8fafc !important;
        border: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 0 16px !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        margin-bottom: 0 !important;
    }

    ul.rs-nav-tabs::-webkit-scrollbar { display: none; }

    ul.rs-nav-tabs .nav-item {
        flex-shrink: 0 !important;
        margin-bottom: 0 !important;
    }

    ul.rs-nav-tabs .nav-link {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        padding: 14px 20px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        border-radius: 0 !important;
        white-space: nowrap !important;
        transition: all 0.2s ease !important;
        margin-bottom: -2px !important;
    }

    ul.rs-nav-tabs .nav-link:hover,
    ul.rs-nav-tabs .nav-link:focus {
        color: #2563eb !important;
        background: transparent !important;
        border-color: transparent !important;
        border-bottom-color: #bfdbfe !important;
    }

    ul.rs-nav-tabs .nav-link.active,
    ul.rs-nav-tabs .nav-item.show .nav-link {
        color: #2563eb !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 3px solid #2563eb !important;
        font-weight: 700 !important;
    }

    ul.rs-nav-tabs .nav-link i {
        font-size: 0.9rem !important;
    }

    .rs-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 7px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        line-height: 1;
        margin-left: 4px;
    }

    .rs-tab-badge--green  { background: #dcfce7; color: #15803d; }
    .rs-tab-badge--amber  { background: #fef3c7; color: #b45309; }
    .rs-tab-badge--red    { background: #fee2e2; color: #dc2626; }
    .rs-tab-badge--slate  { background: #f1f5f9; color: #475569; }

    .rs-tab-content {
        padding: 24px;
        background: #fff;
    }

    /* ═══════════════════════════
       GRID (inside tabs)
    ═══════════════════════════ */
    .rs-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(290px, 0.75fr);
        gap: 20px;
        align-items: start;
    }

    .rs-main,
    .rs-sidebar {
        display: grid;
        gap: 16px;
    }

    /* Checkin/checkout single column layout */
    .rs-checkin-layout {
        display: grid;
        gap: 20px;
    }

    /* ═══════════════════════════
       CARDS
    ═══════════════════════════ */
    .rs-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
    }

    .rs-card__header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-card__header--between {
        justify-content: space-between;
    }

    .rs-card__header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rs-card__icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        flex-shrink: 0;
    }

    .rs-card__header h2 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }

    .rs-card__badge {
        margin-left: auto;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
    }

    .rs-mini-list {
        display: grid;
        gap: 10px;
    }

    .rs-mini-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #f1f5f9;
        border-radius: 14px;
        background: #fbfdff;
    }

    .rs-damage-block {
        border: 1px solid #e2e8f0;
        border-left: 3px solid #4f46e5;
        border-radius: 14px;
        padding: 1.1rem 1.3rem;
        background: #fff;
    }

    .rs-damage-block--amber {
        border-left-color: #f59e0b;
    }

    .rs-damage-block__label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 1rem;
        padding-bottom: .8rem;
        border-bottom: 1px dashed #e5eaf3;
    }

    .rs-damage-block__label > i {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #fffbeb;
        color: #f59e0b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .rs-damage-block__label strong {
        display: block;
        color: #0f172a;
        font-size: .88rem;
        font-weight: 700;
    }

    .rs-damage-block__label span {
        display: block;
        color: #64748b;
        font-size: .76rem;
        margin-top: 2px;
    }

    .rs-damage-table-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }

    .rs-damage-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .82rem;
    }

    .rs-damage-table thead tr {
        background: #fffbeb;
    }

    .rs-damage-table th {
        padding: 9px 12px;
        text-align: left;
        font-weight: 700;
        color: #92400e;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid #fde68a;
    }

    .rs-damage-table td {
        padding: 9px 12px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .rs-damage-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .rs-damage-table .damage-empty {
        text-align: center;
        padding: 22px !important;
        color: #94a3b8;
        font-size: 0.8rem;
    }
    .rs-damage-table .damage-empty i {
        display: block;
        font-size: 1.5rem;
        margin-bottom: 6px;
        opacity: 0.5;
    }

    .rs-damage-table__row {
        cursor: pointer;
        transition: background .16s ease;
    }

    .rs-damage-table__row:hover td {
        background: #fafbff;
    }

    .rs-damage-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .rs-damage-thumbs img {
        width: 46px;
        height: 46px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }



    .rs-damage-empty-inline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #94a3b8;
        font-size: .78rem;
    }

    /* ═══════════════════════════
       ROTA
    ═══════════════════════════ */
    .rs-route {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 16px;
        align-items: center;
        background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
        border: 1px solid #e2eaff;
        border-radius: 14px;
        padding: 20px;
    }

    .rs-route__stop {
        display: grid;
        gap: 10px;
    }

    .rs-route__stop--dest {
        text-align: right;
    }

    .rs-route__stop--dest .rs-route__dot {
        margin-left: auto;
    }

    .rs-route__dot {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .rs-route__dot--origin {
        background: #dcfce7;
        color: #16a34a;
    }

    .rs-route__dot--dest {
        background: #fee2e2;
        color: #dc2626;
    }

    .rs-route__label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #64748b;
    }

    .rs-route__place {
        display: block;
        font-size: 0.96rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }

    .rs-route__datetime {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .rs-route__stop--dest .rs-route__datetime {
        align-items: flex-end;
    }

    .rs-route__date {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e40af;
    }

    .rs-route__time {
        font-size: 0.78rem;
        color: #64748b;
    }

    .rs-route__middle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 0 8px;
    }

    .rs-route__line {
        flex: 1;
        width: 1px;
        min-height: 16px;
        background: linear-gradient(180deg, #c7d7f4, #93c5fd);
    }

    .rs-route__duration {
        background: #1e40af;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 999px;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ═══════════════════════════
       VEICULO
    ═══════════════════════════ */
    .rs-vehicle {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .rs-vehicle__media {
        background: linear-gradient(145deg, #f8fafc, #eef2ff);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 160px;
    }

    .rs-vehicle__media img {
        width: 100%;
        max-height: 170px;
        object-fit: contain;
        filter: drop-shadow(0 12px 20px rgba(15, 23, 42, 0.15));
    }

    .rs-vehicle__brand {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .rs-vehicle__name {
        margin: 4px 0 12px;
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .rs-vehicle__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
    }

    .rs-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .rs-chip--blue  { background: #eff6ff; color: #1d4ed8; }
    .rs-chip--slate { background: #f1f5f9; color: #475569; }
    .rs-chip--green { background: #f0fdf4; color: #15803d; }

    .rs-vehicle__specs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .rs-spec {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .rs-spec i {
        color: #64748b;
        font-size: 0.8rem;
    }

    .rs-spec span {
        font-size: 0.72rem;
        color: #64748b;
    }

    .rs-spec strong {
        font-size: 0.9rem;
        color: #0f172a;
        font-weight: 700;
    }

    .rs-vehicle__pricing {
        display: flex;
        gap: 16px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .rs-vehicle__pricing div {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .rs-vehicle__pricing span {
        font-size: 0.72rem;
        color: #64748b;
    }

    .rs-vehicle__pricing strong {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }

    /* ═══════════════════════════
       OPCIONAIS
    ═══════════════════════════ */
    .rs-optionals {
        display: grid;
        gap: 0;
    }

    .rs-optional-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-optional-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .rs-optional-row__info strong {
        display: block;
        font-size: 0.86rem;
        color: #0f172a;
        font-weight: 700;
    }

    .rs-optional-row__info span {
        font-size: 0.75rem;
        color: #64748b;
    }

    .rs-optional-row__price {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    /* ═══════════════════════════
       CLIENTE
    ═══════════════════════════ */
    .rs-client {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-client__avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        letter-spacing: 0.04em;
    }

    .rs-client__main {
        min-width: 0;
    }

    .rs-client__main strong {
        display: block;
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rs-client__main span {
        display: block;
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ═══════════════════════════
       INFO ROWS
    ═══════════════════════════ */
    .rs-info-rows {
        display: grid;
        gap: 0;
    }

    .rs-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-info-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .rs-info-row span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
    }

    .rs-info-row span i {
        width: 14px;
        text-align: center;
        color: #94a3b8;
    }

    .rs-info-row strong {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f172a;
        text-align: right;
    }

    /* ═══════════════════════════
       RESUMO FINANCEIRO
    ═══════════════════════════ */
    .rs-summary {
        display: grid;
        gap: 0;
    }

    .rs-summary__row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        font-size: 0.84rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-summary__row:last-of-type {
        border-bottom: 0;
    }

    .rs-summary__row--discount span,
    .rs-summary__row--discount {
        color: #16a34a;
    }

    .rs-summary__row--discount i {
        margin-right: 4px;
    }

    .rs-summary__divider {
        height: 1px;
        background: #e2e8f0;
        margin: 4px 0;
    }

    .rs-summary__total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 14px;
        margin-top: 10px;
        background: #0f172a;
        border-radius: 10px;
        color: #fff;
    }

    .rs-summary__total span {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(148, 163, 184, 0.9);
    }

    .rs-summary__total strong {
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
    }

    /* ═══════════════════════════
       FINANCEIRO KPIs
    ═══════════════════════════ */
    .rs-finance-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }

    .rs-fkpi {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
    }

    .rs-fkpi > i {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .rs-fkpi--green {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }

    .rs-fkpi--green > i {
        background: #dcfce7;
        color: #16a34a;
    }

    .rs-fkpi--red {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .rs-fkpi--red > i {
        background: #fee2e2;
        color: #dc2626;
    }

    .rs-fkpi--amber {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    .rs-fkpi--amber > i {
        background: #fef3c7;
        color: #d97706;
    }

    .rs-fkpi div {
        min-width: 0;
    }

    .rs-fkpi strong {
        display: block;
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rs-fkpi span {
        display: block;
        font-size: 0.68rem;
        color: #64748b;
    }

    .rs-fkpi--default {
        background: #f8fbff;
        border: 1px solid #dbe4ee;
    }

    .rs-fkpi--default > i {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .rs-fkpi--total {
        background: #0f172a;
        border: 1px solid #0f172a;
    }

    .rs-fkpi--total > i {
        background: rgba(255,255,255,.15);
        color: #fff;
    }

    .rs-fkpi--total strong {
        color: #fff;
    }

    .rs-fkpi--total span {
        color: rgba(255,255,255,.7);
    }

    /* ═══════════════════════════
       LIST (card items)
    ═══════════════════════════ */
    .rs-list { display: grid; gap: 8px; }
    .rs-list__item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        padding: 10px 14px;
    }
    .rs-list__label {
        font-size: .68rem;
        font-weight: 800;
        color: #7483a0;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 2px;
    }
    .rs-list__value {
        font-size: .88rem;
        color: #0f172a;
        font-weight: 700;
        line-height: 1.4;
    }

    /* ═══════════════════════════
       TABLE
    ═══════════════════════════ */
    .rs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .82rem;
    }
    .rs-table th {
        background: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-size: .68rem;
        font-weight: 800;
        padding: 8px 12px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .rs-table td {
        padding: 8px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #eef2f7;
    }
    .rs-table tbody tr:hover { background: #fbfdff; }

    /* ═══════════════════════════
       TRANSACOES
    ═══════════════════════════ */
    .rs-transactions {
        display: grid;
        gap: 0;
    }

    .rs-tx {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-tx:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .rs-tx__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .rs-tx__icon--green {
        background: #dcfce7;
        color: #16a34a;
    }

    .rs-tx__icon--red {
        background: #fee2e2;
        color: #dc2626;
    }

    .rs-tx__info {
        flex: 1;
        min-width: 0;
    }

    .rs-tx__info strong {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f172a;
    }

    .rs-tx__info span {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
    }

    .rs-tx__amount {
        text-align: right;
    }

    .rs-tx__amount strong {
        display: block;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .rs-tx__amount--green strong { color: #16a34a; }
    .rs-tx__amount--red   strong { color: #dc2626; }

    .rs-tx__amount span {
        display: block;
        font-size: 0.68rem;
        color: #94a3b8;
    }

    .rs-tx__confirmed {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.68rem;
        color: #16a34a;
        font-weight: 600;
        margin-top: 2px;
    }
    .rs-tx__confirmed i { font-size: .72rem; }

    /* ═══════════════════════════
       NFS-e
    ═══════════════════════════ */
    .rs-nfse-error {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 12px;
        margin-top: 12px;
        font-size: 0.8rem;
        color: #9a3412;
    }

    .rs-nfse-error i {
        margin-top: 1px;
        flex-shrink: 0;
    }

    .rs-nfse-error p {
        margin: 0;
        line-height: 1.5;
    }

    .rs-nfse-downloads {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    /* ═══════════════════════════
       BUTTONS
    ═══════════════════════════ */
    .rs-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.18s;
    }

    .rs-btn--warning {
        background: #d97706;
        color: #fff;
    }

    .rs-btn--warning:hover {
        background: #b45309;
        color: #fff;
        text-decoration: none;
    }

    .rs-btn--outline {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #d4dde8;
    }

    .rs-btn--outline:hover {
        background: #f1f5f9;
        color: #0f172a;
        text-decoration: none;
    }

    .rs-btn--ghost {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #d4dde8;
    }

    .rs-btn--ghost:hover {
        background: #f1f5f9;
        color: #0f172a;
        text-decoration: none;
    }

    .rs-btn--full {
        width: 100%;
        justify-content: center;
    }

    /* ═══════════════════════════
       EMPTY STATE
    ═══════════════════════════ */
    .rs-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px;
        color: #94a3b8;
        text-align: center;
    }

    .rs-empty i {
        font-size: 1.8rem;
    }

    .rs-empty span {
        font-size: 0.8rem;
    }

    /* ── Card Contrato de Check-in ── */
    .rs-ci-card {
        border-radius: 18px;
        border: 1px solid rgba(0,0,0,.07);
        padding: 20px 24px;
        box-shadow: 0 4px 18px rgba(15,23,42,.07);
    }
    .rs-ci-card__top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }
    .rs-ci-card__icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .rs-ci-card__title {
        font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;
    }
    .rs-ci-card__badge {
        display: inline-flex; align-items: center;
        padding: 4px 12px; border-radius: 999px;
        font-size: .76rem; font-weight: 700;
    }
    .rs-ci-card__details {
        display: flex; flex-wrap: wrap; gap: 6px 0;
        margin-bottom: 16px;
        border-top: 1px solid rgba(0,0,0,.07);
        padding-top: 14px;
    }
    .rs-ci-detail {
        width: 50%; display: flex; flex-direction: column;
        padding: 4px 12px 4px 0;
    }
    .rs-ci-detail span { font-size: .75rem; color: #64748b; margin-bottom: 2px; }
    .rs-ci-detail strong { font-size: .88rem; color: #0f172a; }
    .rs-ci-card__actions {
        display: flex; flex-wrap: wrap; gap: 8px;
        border-top: 1px solid rgba(0,0,0,.07);
        padding-top: 14px;
    }
    .rs-ci-btn {
        display: inline-flex; align-items: center; gap: 7px;
        border: none; border-radius: 10px;
        padding: 9px 16px; font-size: .83rem; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: opacity .15s, transform .15s;
    }
    .rs-ci-btn:hover { opacity:.82; transform:translateY(-1px); text-decoration:none; }
    .rs-ci-btn--view   { background:rgba(59,130,246,.13);  color:#1d4ed8; }
    .rs-ci-btn--sign   { background:rgba(16,185,129,.13);  color:#047857; }
    .rs-ci-btn--edit   { background:rgba(245,158,11,.14);  color:#92400e; }
    .rs-ci-btn--delete { background:rgba(239,68,68,.11);   color:#b91c1c; }
    .rs-ci-btn--disabled { background:rgba(100,116,139,.13); color:#64748b; cursor:not-allowed; }
    .rs-ci-btn--disabled:hover { opacity:1; transform:none; }

    /* ── Fotos check-in ── */
    .rs-photo-grid {
        margin-left: -6px;
        margin-right: -6px;
    }

    .rs-photo-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding-left: 6px;
        padding-right: 6px;
        margin-bottom: 12px;
    }

    .rs-photo-frame {
        position: relative;
        width: 100%;
        cursor: pointer;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .rs-photo-thumb {
        width: 100%;
        aspect-ratio: 1/1;
        object-fit: cover;
        display: block;
        transition: transform 0.18s, box-shadow 0.18s;
    }

    .rs-photo-zoom {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.38);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .18s ease;
        color: #fff;
        font-size: 1.1rem;
        pointer-events: none;
    }

    .rs-photo-frame:hover .rs-photo-thumb {
        transform: scale(1.04);
    }

    .rs-photo-frame:hover .rs-photo-zoom {
        opacity: 1;
    }

    .rs-photo-placeholder {
        width: 100%;
        aspect-ratio: 1/1;
        border-radius: 12px;
        border: 2px dashed #e2e8f0;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #cbd5e1;
        font-size: 0.75rem;
    }

    .rs-photo-placeholder i {
        font-size: 1.4rem;
    }

    .rs-photo-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
    }

    /* ── Lightbox ── */
    .rs-lightbox {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rs-lightbox__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.88);
        cursor: pointer;
    }

    .rs-lightbox__inner {
        position: relative;
        z-index: 1;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .rs-lightbox__img {
        max-width: 90vw;
        max-height: 80vh;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        object-fit: contain;
    }

    .rs-lightbox__label {
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.06em;
    }

    .rs-lightbox__close {
        position: absolute;
        top: -44px;
        right: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .rs-lightbox__close:hover {
        background: rgba(255,255,255,0.3);
    }

    /* ── Danos AJAX containers ── */
    .rs-dano-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        margin-bottom: 8px;
        background: #fbfdff;
    }

    .rs-dano-item,
    .rs-mini-item--clickable {
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .rs-dano-item:hover,
    .rs-mini-item--clickable:hover {
        transform: translateY(-1px);
        border-color: #cbd5e1;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .rs-dano-item:last-child { margin-bottom: 0; }
    .rs-dano-item--changed { border-color: #fbbf24; background: #fffbeb; }
    .rs-diff-before { color: #991b1b; }
    .rs-diff-after { color: #065f46; }
    .rs-diff-before del { color: #dc2626; }
    .rs-diff-after ins { color: #16a34a; text-decoration:none; font-weight:700; }

    .rs-dano-thumbs {
        margin-top: 8px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .rs-dano-thumbs img {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    .rs-dano-modal {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.24);
    }

    .rs-dano-modal__header {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #fff;
        border-bottom: 0;
        padding: 18px 22px;
    }

    .rs-dano-modal__header .close {
        color: #fff;
        opacity: .85;
        text-shadow: none;
    }

    .rs-dano-modal__eyebrow {
        text-transform: uppercase;
        letter-spacing: .14em;
        font-size: .72rem;
        font-weight: 700;
        opacity: .7;
        margin-bottom: 4px;
    }

    .rs-dano-modal__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }

    .rs-dano-modal__info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .rs-dano-modal__info span {
        display: block;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        margin-bottom: 5px;
        font-weight: 700;
    }

    .rs-dano-modal__info strong,
    .rs-dano-modal__info div {
        color: #0f172a;
        font-size: .95rem;
        word-break: break-word;
    }

    .rs-dano-modal__correcao,
    .rs-dano-modal__imagens {
        margin-top: 18px;
    }

    .rs-dano-modal__correcao h6,
    .rs-dano-modal__imagens h6 {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 10px;
    }

    .rs-dano-modal__correcao-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #10b981;
        border-radius: 14px;
        padding: 14px 16px;
    }

    .rs-dano-modal__thumbs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .rs-dano-modal__thumbs a {
        display: block;
    }

    .rs-dano-modal__thumbs img {
        width: 96px;
        height: 96px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    /* ── Histórico ── */
    .rs-historico-card .rs-card__header { display:flex; align-items:center; gap:10px; user-select:none; }
    .rs-timeline { padding: 1rem 1.5rem; }
    .rs-tl-item {
        display: flex;
        gap: 14px;
        padding-bottom: 1.4rem;
        position: relative;
    }
    .rs-tl-item:not(:last-child)::before {
        content:'';
        position:absolute;
        left: 17px;
        top: 36px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .rs-tl-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
        color: #fff;
    }
    .rs-tl-icon--info    { background: #3b82f6; }
    .rs-tl-icon--success { background: #10b981; }
    .rs-tl-icon--warning { background: #f59e0b; }
    .rs-tl-icon--danger  { background: #ef4444; }
    .rs-tl-body { flex:1; min-width:0; }
    .rs-tl-acao {
        font-size: .88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .rs-tl-meta {
        font-size: .76rem;
        color: #64748b;
        margin-bottom: 4px;
    }
    .rs-tl-detalhes {
        font-size: .78rem;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 10px;
        margin-top: 4px;
    }
    .rs-tl-table { width:100%; border-collapse:collapse; }
    .rs-tl-table tr + tr td { border-top: 1px solid #f1f5f9; }
    .rs-tl-table td { padding: 3px 8px 3px 0; vertical-align:top; font-size:.78rem; }
    .rs-tl-table__key { color:#94a3b8; white-space:nowrap; font-weight:600; width:35%; padding-right:10px; }
    .rs-tl-bloco { margin-top:6px; }
    .rs-tl-bloco__title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:3px; padding-bottom:2px; border-bottom:1px solid #e2e8f0; }
    .rs-tl-pre { margin:0; font-size:.72rem; background:#f1f5f9; border-radius:4px; padding:3px 6px; white-space:pre-wrap; word-break:break-all; }
    .rs-tl-item--clickable { cursor:pointer; transition:background .15s ease; border-radius:8px; margin-left:-8px; padding-left:8px; padding-right:8px; }
    .rs-tl-item--clickable:hover { background:#f1f5f9; }
    .rs-tl-chevron { display:flex; align-items:center; color:#94a3b8; font-size:.75rem; flex-shrink:0; padding-left:4px; }
    .rs-tl-details-badge { display:inline-flex; align-items:center; color:#3b82f6; font-weight:600; }

    /* ── Contrato legado ── */
    .rs-contrato-badge {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .rs-contrato-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .rs-contrato-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: opacity .15s, transform .15s;
    }
    .rs-contrato-btn:hover { opacity:.85; transform:translateY(-1px); text-decoration:none; }
    .rs-contrato-btn--view  { background:rgba(59,130,246,.12);  color:#1d4ed8; }
    .rs-contrato-btn--sign  { background:rgba(16,185,129,.12);  color:#047857; }
    .rs-contrato-btn--edit  { background:rgba(245,158,11,.13);  color:#b45309; }
    .rs-contrato-btn--delete{ background:rgba(239,68,68,.10);   color:#b91c1c; }

    /* ═══════════════════════════
       RESPONSIVE
    ═══════════════════════════ */
    @media (max-width: 1199px) {
        .rs-grid {
            grid-template-columns: 1fr;
        }

        .rs-hero__body {
            flex-direction: column;
        }

        .rs-hero__right {
            align-items: flex-start;
        }
    }

    @media (max-width: 992px) {
        .rs-photo-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .rs-hero__body {
            padding: 20px;
        }

        .rs-hero__title {
            font-size: 1.5rem;
        }

        .rs-hero__kpis {
            width: 100%;
        }

        .rs-kpi {
            flex: 1;
        }

        .rs-vehicle {
            grid-template-columns: 1fr;
        }

        .rs-vehicle__media {
            max-width: 100%;
        }

        .rs-vehicle__specs {
            grid-template-columns: 1fr 1fr;
        }

        .rs-route {
            grid-template-columns: 1fr;
        }

        .rs-route__middle {
            flex-direction: row;
            padding: 0;
        }

        .rs-route__line {
            height: 1px;
            width: auto;
            flex: 1;
            min-height: auto;
            background: linear-gradient(90deg, #c7d7f4, #93c5fd);
        }

        .rs-route__stop--dest {
            text-align: left;
        }

        .rs-route__stop--dest .rs-route__dot {
            margin-left: 0;
        }

        .rs-route__stop--dest .rs-route__datetime {
            align-items: flex-start;
        }

        .rs-alert {
            flex-direction: column;
            align-items: flex-start;
        }

        .rs-btn--full {
            width: 100%;
        }

        .rs-tab-content {
            padding: 16px;
        }

        .rs-photo-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .rs-ci-detail { width: 100%; }
        .rs-ci-card__actions { flex-direction:column; }
        .rs-ci-btn { justify-content:center; }
    }

    @media (max-width: 480px) {
        .rs-photo-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const showToast = (title, text, icon) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ title, text, icon, timer: 4200, showConfirmButton: false, toast: true, position: 'top-end' });
                    return;
                }
                alert(text || title);
            };

            const flashSuccess = <?php echo json_encode(session('success'), 15, 512) ?>;
            const flashWarning = <?php echo json_encode(session('warning'), 15, 512) ?>;
            const flashError = <?php echo json_encode(session('error'), 15, 512) ?>;

            if (flashSuccess) showToast('Reserva', flashSuccess, 'success');
            if (flashError)   showToast('Reserva', flashError, 'error');
            if (flashWarning) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Atenção',
                        text: flashWarning,
                        icon: 'warning',
                        iconColor: '#f59e0b',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#0891b2',
                    });
                } else {
                    alert(flashWarning);
                }
            }

            if (window.$ && typeof window.$.fn.tooltip === 'function') {
                window.$('[data-toggle="tooltip"]').tooltip();
            }

            const forms = document.querySelectorAll('.js-nfse-form');
            if (!forms.length) return;

            forms.forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const btn = form.querySelector('.js-nfse-btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Emitindo...';
                    }
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            }
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Falha ao emitir a NFS-e.');
                        const icon = data.status === 'success' ? 'success' : 'warning';
                        showToast('NFS-e', data.message || 'Processo concluido.', icon);
                        if (data.status === 'success') setTimeout(() => window.location.reload(), 1300);
                    } catch (error) {
                        showToast('NFS-e', error.message || 'Falha ao emitir a NFS-e.', 'error');
                    } finally {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-redo"></i> Tentar emitir';
                        }
                    }
                });
            });
        })();
    </script>

    <script>
    (function () {
        const RESERVA_ID    = <?php echo json_encode($reserva->id, 15, 512) ?>;
        const CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const HISTORICO_URL = "<?php echo e(route('reserva.historico', $reserva->id)); ?>";
        <?php if($reserva->contratoCheckin): ?>
        const VEICULO_ID    = <?php echo json_encode($reserva->contratoCheckin->veiculo_id, 15, 512) ?>;
        const DANOS_EXIST_URL    = "<?php echo e(route('dano.obterDanosExistentes')); ?>";
        const DANOS_DETECT_URL   = "<?php echo e(route('dano.obterDanosDetectados')); ?>";
        <?php endif; ?>

        /* ── Tab link helper (cross-tab navigation) ── */
        document.querySelectorAll('.rs-tab-link').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const tabId = this.dataset.tab;
                const tabEl = document.getElementById(tabId);
                if (tabEl) $(tabEl).tab('show');
            });
        });

        /* ── Ícones por nível ── */
        const nivelIcone = {
            info:    'fas fa-info-circle',
            success: 'fas fa-check-circle',
            warning: 'fas fa-exclamation-circle',
            danger:  'fas fa-times-circle',
        };

        /* ── Abre modal com detalhes do log ── */
        function openLogModal(log) {
            const modal   = document.getElementById('modalDetalhesLog');
            if (!modal) return;

            document.getElementById('logModalAcao').textContent  = log.acao || '';
            document.getElementById('logModalUsuario').textContent = log.usuario || '';
            document.getElementById('logModalData').textContent    = log.data || '';
            document.getElementById('logModalNivel').textContent   = (log.nivel || 'info').toUpperCase();

            const badge = document.getElementById('logModalNivel');
            badge.className = 'badge badge-' + ({
                info: 'info', success: 'success', warning: 'warning', danger: 'danger',
            }[log.nivel] || 'secondary');

            const det = log.detalhes || {};
            const container = document.getElementById('logModalDetalhes');
            container.innerHTML = '';

            const BLOCK_KEYS = ['antes', 'depois', 'data', 'payload'];
            const entries = Object.entries(det).filter(function (_ref) { var k = _ref[0]; return k !== 'acao'; });

            if (!entries.length) {
                container.innerHTML = '<div class="rs-empty"><i class="fas fa-info-circle"></i><span>Nenhum detalhe adicional.</span></div>';
                $(modal).modal('show');
                return;
            }

            var blocos = [];
            var simples = [];

            entries.forEach(function (_ref2) {
                var k = _ref2[0], v = _ref2[1];
                if (BLOCK_KEYS.includes(k) && typeof v === 'object' && v !== null) {
                    blocos.push({ key: k, value: v, label: k.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); }) });
                } else {
                    simples.push({ key: k, value: v, label: k.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); }) });
                }
            });

            if (simples.length) {
                var simpleGrid = document.createElement('div');
                simpleGrid.className = 'rs-dano-modal__grid mb-4';
                simples.forEach(function (s) {
                    var info = document.createElement('div');
                    info.className = 'rs-dano-modal__info';
                    info.innerHTML = '<span>' + escapeHtml(s.label) + '</span><div>' + formatLogValue(s.value) + '</div>';
                    simpleGrid.appendChild(info);
                });
                container.appendChild(simpleGrid);
            }

            blocos.forEach(function (b) {
                var isAntesDepois = b.key === 'antes' && typeof det.depois === 'object' && det.depois !== null;
                var v = b.value;

                if (isAntesDepois && typeof v === 'object' && !Array.isArray(v)) {
                    var diffCard = document.createElement('div');
                    diffCard.className = 'mb-4';
                    diffCard.innerHTML = '<h6 class="font-weight-bold mb-3" style="font-size:.85rem;color:#0f172a"><i class="fas fa-exchange-alt mr-2"></i>Comparacao: Antes vs Depois</h6>';

                    var allKeys = new Set([].concat(Object.keys(v), Object.keys(det.depois)));
                    allKeys.forEach(function (sk) {
                        var svBefore = v[sk];
                        var svAfter  = det.depois[sk];
                        var slabel  = sk.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                        var changed = String(svBefore) !== String(svAfter);

                        var item = document.createElement('div');
                        item.className = 'rs-dano-item' + (changed ? ' rs-dano-item--changed' : '');
                        item.innerHTML = '<div style="flex:1"><strong>' + escapeHtml(slabel) + '</strong>'
                            + '<div class="rs-diff-values" style="display:flex;gap:12px;margin-top:4px">'
                            + '<span class="rs-diff-before' + (changed ? '' : ' text-muted') + '"><span class="small text-muted">Antes:</span> ' + formatDiffValue(svBefore) + '</span>'
                            + '<span class="rs-diff-after' + (changed ? ' font-weight-bold' : ' text-muted') + '"><span class="small text-muted">Depois:</span> ' + formatDiffValue(svAfter) + '</span>'
                            + '</div></div>';
                        diffCard.appendChild(item);
                    });
                    container.appendChild(diffCard);
                } else {
                    var blockCard = document.createElement('div');
                    blockCard.className = 'mb-4';
                    blockCard.innerHTML = '<h6 class="font-weight-bold mb-3" style="font-size:.85rem;color:#0f172a">' + escapeHtml(b.label) + '</h6>';

                    if (Array.isArray(v)) {
                        var pre = document.createElement('pre');
                        pre.className = 'mb-0 small p-3';
                        pre.style.cssText = 'background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;max-height:200px;overflow:auto;white-space:pre-wrap';
                        pre.textContent = JSON.stringify(v, null, 2);
                        blockCard.appendChild(pre);
                    } else {
                        var entries2 = Object.entries(v);
                        entries2.forEach(function (_ref3) {
                            var sk = _ref3[0], sv = _ref3[1];
                            var slabel = sk.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                            var item2 = document.createElement('div');
                            item2.className = 'rs-dano-item';
                            item2.style.cursor = 'default';
                            item2.innerHTML = '<div style="flex:1"><strong>' + escapeHtml(slabel) + '</strong><div class="text-muted small" style="margin-top:2px">' + formatLogValue(sv) + '</div></div>';
                            blockCard.appendChild(item2);
                        });
                    }
                    container.appendChild(blockCard);
                }
            });

            $(modal).modal('show');
        }

        function formatLogValue(v) {
            if (v === null || v === undefined) return '<span class="text-muted">—</span>';
            if (typeof v === 'boolean') return v ? '<span class="text-success">Sim</span>' : '<span class="text-danger">Nao</span>';
            if (typeof v === 'object') return '<pre class="mb-0 small" style="background:#f1f5f9;border-radius:6px;padding:4px 8px;max-height:80px;overflow:auto;white-space:pre-wrap;margin:0">' + JSON.stringify(v, null, 2) + '</pre>';
            return String(v);
        }

        function formatDiffValue(v) {
            if (v === null || v === undefined) return '<span class="text-muted small">—</span>';
            if (typeof v === 'boolean') return v ? 'Sim' : 'Nao';
            if (typeof v === 'object') return '<span class="small">' + JSON.stringify(v) + '</span>';
            return String(v);
        }

        /* ── Render timeline ── */
        function renderHistorico(logs) {
            const container = document.getElementById('historicoTimeline');
            const countEl   = document.getElementById('historicoCount');
            const countPaneEl = document.getElementById('historicoCountPane');
            if (countEl) {
                countEl.textContent = logs.length;
                countEl.classList.toggle('d-none', logs.length === 0);
            }
            if (countPaneEl) countPaneEl.textContent = logs.length;

            if (!logs.length) {
                container.innerHTML = '<p class="text-muted text-center py-3"><i class="fas fa-inbox mr-1"></i>Nenhum registro encontrado.</p>';
                container.classList.remove('d-none');
                return;
            }

            function hasDetalhes(det) {
                return det && typeof det === 'object' && Object.keys(det).length > 0;
            }

            container.innerHTML = logs.map(function (log, idx) {
                const nivel    = log.nivel || 'info';
                const icon     = nivelIcone[nivel] || nivelIcone.info;
                const det      = log.detalhes || {};
                const hasDet   = hasDetalhes(det);
                const detCount = hasDet ? Object.keys(det).length : 0;
                const logJson  = hasDet ? JSON.stringify(log).replace(/"/g, '&quot;') : '';

                return `
                <div class="rs-tl-item ${hasDet ? 'rs-tl-item--clickable' : ''}" ${hasDet ? 'data-log=\'' + logJson + '\'' : ''}>
                    <div class="rs-tl-icon rs-tl-icon--${nivel}"><i class="${icon}"></i></div>
                    <div class="rs-tl-body">
                        <div class="rs-tl-acao">${log.acao || 'Ação registrada'}</div>
                        <div class="rs-tl-meta">
                            <i class="fas fa-user mr-1"></i>${log.usuario}
                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-clock mr-1"></i>${log.data}
                            ${hasDet ? '&nbsp;&middot;&nbsp;<span class="rs-tl-details-badge"><i class="fas fa-list mr-1"></i>' + detCount + ' detalhe(s)</span>' : ''}
                        </div>
                    </div>
                    ${hasDet ? '<div class="rs-tl-chevron"><i class="fas fa-chevron-right"></i></div>' : ''}
                </div>`;
            }).join('');

            container.querySelectorAll('.rs-tl-item--clickable').forEach(function (el) {
                el.addEventListener('click', function () {
                    try {
                        var logData = JSON.parse(this.dataset.log);
                        openLogModal(logData);
                    } catch (e) {
                        // ignore parse errors
                    }
                });
            });

            container.classList.remove('d-none');
        }

        /* ── Carregar histórico ── */
        let historicoCarregado = false;
        function carregarHistorico() {
            if (historicoCarregado) return;
            historicoCarregado = true;
            document.getElementById('historicoLoading')?.classList.remove('d-none');

            fetch(HISTORICO_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('historicoLoading')?.classList.add('d-none');
                    renderHistorico(data.logs || []);
                })
                .catch(() => {
                    const el = document.getElementById('historicoLoading');
                    if (el) el.textContent = 'Erro ao carregar histórico.';
                });
        }

        /* ── Auto-load histórico when tab opened ── */
        document.getElementById('tab-historico')?.addEventListener('shown.bs.tab', carregarHistorico);
        // jQuery fallback for AdminLTE Bootstrap 4
        $('#tab-historico').on('shown.bs.tab', carregarHistorico);

        <?php if($reserva->contratoCheckin): ?>
        /* ── Carregar danos (check-in tab) ── */
        let danosCarregados = false;

        const TIPO_LABEL = {
            ausente:          '<span class="badge badge-success">Ausente</span>',
            amolgadela_leve:  '<span class="badge badge-warning text-dark">Amolgadela Leve</span>',
            amolgadela_grave: '<span class="badge badge-danger">Amolgadela Grave</span>',
            risco_leve:       '<span class="badge badge-warning text-dark">Risco Leve</span>',
            risco_grave:      '<span class="badge badge-danger">Risco Grave</span>',
        };

        function tipoBadge(tipo) {
            return TIPO_LABEL[tipo] || '<span class="badge badge-secondary">' + (tipo || '—') + '</span>';
        }

        function formatCusto(val) {
            if (val === null || val === undefined || val === '') return '—';
            return 'R$ ' + Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        }

        function renderDanosExistentes(data) {
            const loading   = document.getElementById('danos-existentes-loading-table');
            const container = document.getElementById('danos-existentes-container');
            if (loading) loading.remove();
            if (!container) return;

            const items = data.danos || data || [];
            container.innerHTML = '';

            if (!items.length) {
                container.innerHTML = '<tr><td colspan="4" class="damage-empty"><i class="fas fa-check-circle"></i> Sem histórico de avarias anteriores</td></tr>';
            } else {
                items.forEach(function (d) {
                    const imagens = Array.isArray(d.imagens) ? d.imagens : [];
                    const thumbsHtml = imagens.map(function (img) {
                        const url = img.url || (img.path ? ('/storage/' + img.path) : null);
                        if (!url) return '';
                        return '<a href="' + url + '" target="_blank" rel="noopener" onclick="event.stopPropagation()" title="Ver imagem"><img src="' + url + '" alt="foto" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;margin-right:4px"></a>';
                    }).filter(Boolean).join('') || '<span class="text-muted small">—</span>';

                    var corr = '<span class="text-muted">—</span>';
                    if (d.correcao_realizada) {
                        corr = '<span class="text-success"><i class="fas fa-wrench"></i> ' + escapeHtml(d.correcao_descricao ?? '') + '<br><small>' + (d.correcao_data ?? '') + '</small></span>';
                    } else if (d.correcao_descricao || d.correcao_data) {
                        corr = '<span class="text-warning"><i class="fas fa-hourglass-half"></i> Pendente</span>';
                    }

                    var tr = document.createElement('tr');
                    tr.className = 'js-dano-detalhe-trigger rs-damage-table__row';
                    tr.dataset.dano = encodeURIComponent(JSON.stringify(d));
                    tr.innerHTML = '<td><strong>' + escapeHtml(d.localizacao || d.local_dano || '—') + '</strong></td>'
                        + '<td>' + escapeHtml(d.descricao || '') + '</td>'
                        + '<td>' + corr + '</td>'
                        + '<td>' + thumbsHtml + '</td>';
                    container.appendChild(tr);
                });
            }
            bindDanoDetalheTriggers(container);
        }

        function renderDanosDetectados(data) {
            const loading   = document.getElementById('danos-detectados-loading');
            const container = document.getElementById('danos-detectados-container');
            if (loading) loading.remove();
            if (!container) return;

            const items = data.danos || data || [];
            container.innerHTML = '';

            if (!items.length) {
                container.innerHTML = '<tr><td colspan="6" class="damage-empty"><i class="fas fa-shield-alt"></i> Nenhuma avaria nesta reserva</td></tr>';
            } else {
                items.forEach(function (d) {
                    const payload = encodeURIComponent(JSON.stringify(d));
                    const imagens = Array.isArray(d.imagens) ? d.imagens : [];
                    const thumbsHtml = imagens.map(function (img) {
                        const url = img.url || (img.path ? ('/storage/' + img.path) : null);
                        if (!url) return '';
                        return '<a href="' + url + '" target="_blank" rel="noopener" onclick="event.stopPropagation()" title="Ver imagem"><img src="' + url + '" alt="foto" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;margin-right:4px"></a>';
                    }).filter(Boolean).join('') || '<span class="text-muted small">—</span>';

                    const categoria = d.categoria ?? '—';
                    const local = d.localizacao_problema ?? '—';
                    const locLabel = '<strong>' + escapeHtml(d.localizacao || d.local_dano || '—') + '</strong><br><small class="text-muted">' + escapeHtml(categoria) + ' \u00b7 ' + escapeHtml(local) + '</small>';
                    const descricao = d.descricao ? escapeHtml(d.descricao) : '<span class="text-muted">—</span>';
                    const km = d.quilometragem_veiculo != null
                        ? '<small class="text-muted d-block mt-1"><i class="fas fa-tachometer-alt mr-1"></i>KM ' + Number(d.quilometragem_veiculo).toLocaleString('pt-BR') + '</small>'
                        : '';
                    const nomeDano = d.nome_dano
                        ? '<small class="d-block text-muted mt-1">' + escapeHtml(d.nome_dano) + '</small>'
                        : '';

                    const etapaBadge = d.contrato_id
                        ? '<span class="badge badge-info"><i class="fas fa-sign-in-alt mr-1"></i>Check-in</span>'
                        : '<span class="badge badge-danger"><i class="fas fa-sign-out-alt mr-1"></i>Check-out</span>';

                    var tr = document.createElement('tr');
                    tr.className = 'js-dano-detalhe-trigger rs-damage-table__row';
                    tr.dataset.dano = payload;
                    tr.innerHTML = '<td>' + locLabel + '</td>'
                        + '<td>' + descricao + km + '</td>'
                        + '<td>' + tipoBadge(d.tipo_dano) + nomeDano + '</td>'
                        + '<td>' + etapaBadge + '</td>'
                        + '<td>' + formatCusto(d.custo_estimado) + '</td>'
                        + '<td>' + thumbsHtml + '</td>';
                    container.appendChild(tr);
                });
            }
            bindDanoDetalheTriggers(container);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatCurrency(value) {
            const number = Number(value || 0);
            return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function openDanoDetalheModal(dano) {
            const infos = document.getElementById('rsDanoModalInfos');
            const correcaoWrap = document.getElementById('rsDanoModalCorrecaoWrap');
            const correcaoBox = document.getElementById('rsDanoModalCorrecao');
            const imagensWrap = document.getElementById('rsDanoModalImagensWrap');
            const imagensBox = document.getElementById('rsDanoModalImagens');

            if (!infos || !correcaoWrap || !correcaoBox || !imagensWrap || !imagensBox) return;

            const infoItems = [
                ['ID', dano.id ?? '—'],
                ['Vista', dano.localizacao || dano.local_dano || '—'],
                ['Área', dano.localizacao_problema || '—'],
                ['Categoria', dano.categoria || '—'],
                ['Tipo de dano', dano.tipo_dano || '—'],
                ['Custo estimado', dano.custo_estimado !== undefined && dano.custo_estimado !== null ? formatCurrency(dano.custo_estimado) : '—'],
                ['Quilometragem', dano.quilometragem_veiculo ? `${dano.quilometragem_veiculo} km` : '—'],
                ['Registrado em', dano.created_at || '—'],
                ['Observações', dano.descricao || '—'],
            ];

            infos.innerHTML = infoItems.map(([label, value]) => `
                <div class="rs-dano-modal__info">
                    <span>${escapeHtml(label)}</span>
                    <div>${escapeHtml(value)}</div>
                </div>
            `).join('');

            if (dano.correcao_realizada || dano.correcao_descricao || dano.correcao_data) {
                correcaoWrap.classList.remove('d-none');
                correcaoBox.innerHTML = `
                    <div class="rs-dano-modal__correcao-box">
                        <div><strong>Status:</strong> ${dano.correcao_realizada ? 'Corrigido' : 'Pendente'}</div>
                        <div><strong>Descrição:</strong> ${escapeHtml(dano.correcao_descricao || '—')}</div>
                        <div><strong>Data:</strong> ${escapeHtml(dano.correcao_data || '—')}</div>
                        <div><strong>Responsável:</strong> ${escapeHtml(dano.correcao_usuario || '—')}</div>
                    </div>
                `;
            } else {
                correcaoWrap.classList.add('d-none');
                correcaoBox.innerHTML = '';
            }

            const imagens = Array.isArray(dano.imagens) ? dano.imagens.filter(img => img && img.url) : [];
            if (imagens.length) {
                imagensWrap.classList.remove('d-none');
                imagensBox.innerHTML = imagens.map((img, idx) => `
                    <a href="${escapeHtml(img.url)}" target="_blank" rel="noopener">
                        <img src="${escapeHtml(img.url)}" alt="Foto do dano ${idx + 1}">
                    </a>
                `).join('');
            } else {
                imagensWrap.classList.add('d-none');
                imagensBox.innerHTML = '';
            }

            $('#modalDetalheDanoReserva').modal('show');
        }

        function bindDanoDetalheTriggers(scope) {
            (scope || document).querySelectorAll('.js-dano-detalhe-trigger').forEach(function (el) {
                if (el.dataset.boundDanoModal === '1') return;
                el.dataset.boundDanoModal = '1';
                el.addEventListener('click', function () {
                    try {
                        const raw = this.dataset.dano || '{}';
                        const payload = raw.startsWith('%7B') || raw.startsWith('%5B')
                            ? JSON.parse(decodeURIComponent(raw))
                            : JSON.parse(raw);
                        openDanoDetalheModal(payload);
                    } catch (e) {
                        console.error('Erro ao abrir detalhe do dano', e);
                    }
                });
            });
        }

        bindDanoDetalheTriggers(document);

        function carregarDanos() {
            if (danosCarregados) return;
            danosCarregados = true;

            // Danos existentes
            fetch(DANOS_EXIST_URL + '?veiculo_id=' + VEICULO_ID + '&reserva_id=' + RESERVA_ID, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
            })
            .then(r => r.json())
            .then(data => renderDanosExistentes(data))
            .catch(() => {
                const el = document.getElementById('danos-existentes-loading-table');
                if (el) el.outerHTML = '<tr><td colspan="4" class="damage-empty text-danger"><i class="fas fa-exclamation-circle"></i> Erro ao carregar danos existentes.</td></tr>';
            });

            // Danos detectados
            fetch(DANOS_DETECT_URL + '?veiculo_id=' + VEICULO_ID + '&reserva_id=' + RESERVA_ID, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
            })
            .then(r => r.json())
            .then(data => renderDanosDetectados(data))
            .catch(() => {
                const el = document.getElementById('danos-detectados-loading');
                if (el) el.outerHTML = '<tr><td colspan="5" class="damage-empty text-danger"><i class="fas fa-exclamation-circle"></i> Erro ao carregar danos detectados.</td></tr>';
            });
        }

        document.getElementById('tab-checkin')?.addEventListener('shown.bs.tab', carregarDanos);
        $('#tab-checkin').on('shown.bs.tab', carregarDanos);
        <?php endif; ?>

        /* ── Excluir contrato check-in ── */
        document.querySelectorAll('.js-excluir-contrato-checkin').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const url       = this.dataset.url;
                const assinado  = this.dataset.assinado === '1';
                const msg = assinado
                    ? 'Este contrato <strong>já foi assinado</strong>. Ao excluir você perderá a assinatura e precisará assinar novamente. Deseja continuar?'
                    : 'O contrato de check-in será excluído. Deseja continuar?';

                Swal.fire({
                    title: 'Excluir contrato de check-in?',
                    html: msg,
                    icon: assinado ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sim, excluir',
                    cancelButtonText: 'Cancelar',
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${CSRF_TOKEN}">
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        });

        /* ── Excluir contrato check-out ── */
        document.querySelectorAll('.js-excluir-contrato-checkout').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const url = this.dataset.url;
                const assinado = this.dataset.assinado === '1';
                const msg = assinado
                    ? 'Este contrato <strong>já foi assinado</strong>. A ação vai invalidar o checkout e exigir justificativa. Deseja continuar?'
                    : 'O contrato de check-out será excluído e o checkout será reaberto. Deseja continuar?';

                Swal.fire({
                    title: assinado ? 'Invalidar contrato de check-out?' : 'Excluir contrato de check-out?',
                    html: msg,
                    icon: assinado ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: assinado ? 'Sim, invalidar' : 'Sim, excluir',
                    cancelButtonText: 'Cancelar',
                    input: assinado ? 'textarea' : undefined,
                    inputPlaceholder: assinado ? 'Informe a justificativa da invalidação' : undefined,
                    inputAttributes: assinado ? { 'aria-label': 'Justificativa da invalidação' } : undefined,
                    inputValidator: assinado ? function (value) {
                        if (!value || value.trim().length < 5) {
                            return 'Informe uma justificativa com pelo menos 5 caracteres.';
                        }
                        return null;
                    } : undefined,
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${CSRF_TOKEN}">
                        <input type="hidden" name="_method" value="DELETE">
                        ${assinado ? `<input type="hidden" name="justificativa" value="${String(result.value || '').replace(/"/g, '&quot;')}">` : ''}
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        });

        /* ── Lightbox ── */
        const lightbox     = document.getElementById('rs-lightbox');
        const lightboxImg  = document.getElementById('rs-lightbox-img');
        const lightboxLbl  = document.getElementById('rs-lightbox-label');
        const lightboxClose = document.getElementById('rs-lightbox-close');
        const lightboxBack  = document.getElementById('rs-lightbox-backdrop');

        function openLightbox(src, label) {
            if (!lightbox) return;
            lightboxImg.src = src;
            lightboxLbl.textContent = label || '';
            lightbox.classList.remove('d-none');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            if (!lightbox) return;
            lightbox.classList.add('d-none');
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.js-lightbox-trigger').forEach(function (el) {
            el.addEventListener('click', function () {
                openLightbox(this.dataset.src, this.dataset.label);
            });
        });

        lightboxClose?.addEventListener('click', closeLightbox);
        lightboxBack?.addEventListener('click', closeLightbox);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
        });

    })();

    // ── Confirmar pagamento em dinheiro ──
    document.querySelectorAll('.btn-confirmar-dinheiro').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const transacaoId = this.dataset.transacaoId;
            const reservaId   = this.dataset.reservaId;

            const conf = await Swal.fire({
                title: 'Confirmar recebimento?',
                text: 'Confirma que o pagamento em dinheiro foi recebido?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a',
            });

            if (!conf.isConfirmed) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch(`/reserva/${reservaId}/confirmar-pagamento-dinheiro`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ transacao_id: transacaoId }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({ title: 'Confirmado!', text: data.message, icon: 'success', timer: 2500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 2600);
                } else {
                    Swal.fire({ title: 'Erro', text: data.error || 'Falha ao confirmar.', icon: 'error' });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar recebimento';
                }
            } catch(e) {
                Swal.fire({ title: 'Erro de rede', text: 'Tente novamente.', icon: 'error' });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar recebimento';
            }
        });
    });
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'reserva-show';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.rs-hero', popover: { title: 'Cabeçalho da reserva', description: 'Aqui ficam as informações principais: número da reserva, cliente, empresa, data de criação, status atual, valor total e duração.', side: 'bottom' } },
        { element: '.rs-status-badge', popover: { title: 'Status da reserva', description: 'Indica a situação atual: Criada, Confirmada, Cancelada, etc. O status muda conforme o fluxo de check-in e checkout é realizado.', side: 'left' } },
        { element: '.rs-hero__kpis', popover: { title: 'Indicadores financeiros', description: 'Mostra o valor total da reserva, a duração em dias e o percentual de desconto aplicado, quando houver.', side: 'bottom' } },
        { element: '.rs-nav-tabs', popover: { title: 'Abas de detalhes', description: 'Navegue pelas abas para acessar: Visão Geral, Check-in (contrato), Check-out (danos e devoluções), Pagamentos, NFS-e, Multas e Caução.', side: 'bottom' } },
        { element: '#tab-visao-geral', popover: { title: 'Visão Geral', description: 'Exibe os dados completos da reserva: cliente, veículo, datas, opcionais contratados e valores detalhados.', side: 'bottom' } },
        { element: '#tab-checkin', popover: { title: 'Check-in', description: 'Gerencie o contrato de check-in. Você pode gerar, enviar para assinatura e acompanhar o status do documento.', side: 'bottom' } },
        { element: '#tab-checkout', popover: { title: 'Check-out', description: 'Registre a devolução do veículo, danos identificados e cobranças por avarias.', side: 'bottom' } },
        { element: '.rs-actions-bar', popover: { title: 'Painel de ações', description: 'Botões para as principais operações: confirmar, cancelar, processar pagamento, emitir NFS-e, iniciar check-in e checkout.', side: 'bottom' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo {{current}} de {{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>



<?php
    $grupoMarcasModelos = [];
    if ($reserva->grupo_frota_id) {
        $veiculos = \App\Models\Veiculo::where('grupo_frota_id', $reserva->grupo_frota_id)
            ->with('marca', 'modelo')->get();
        $agg = [];
        foreach ($veiculos as $v) {
            $marca = $v->marca?->nome ?? '—';
            $modelo = $v->modelo?->nome ?? '—';
            $key = $marca . '|' . $modelo;
            if (!isset($agg[$key])) $agg[$key] = ['marca' => $marca, 'modelo' => $modelo, 'qtd' => 0];
            $agg[$key]['qtd']++;
        }
        $grupoMarcasModelos = array_values($agg);
    }
?>

<div class="modal fade" id="modalGrupo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5><i class="fas fa-layer-group mr-2"></i><?php echo e($reserva->grupoFrota->nome ?? 'Grupo'); ?></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if(count($grupoMarcasModelos)): ?>
                    <div class="d-flex gap-2 mb-2 align-items-center">
                        <input type="text" class="form-control form-control-sm" id="filtroGrupoModal" placeholder="Buscar por marca ou modelo..." oninput="filtrarGrupoModal()" style="flex:1;">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-primary btn-sm" id="btnTodosGrupo" onclick="filtrarGrupoTipo('todos')">Todos</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnDisponiveisGrupo" onclick="filtrarGrupoTipo('disponiveis')">Disponíveis</button>
                        </div>
                    </div>
                    <div style="max-height:400px;overflow-y:auto;">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.84rem;" id="tabelaGrupoModal">
                        <thead><tr><th style="width:35%;">Marca</th><th style="width:45%;">Modelo</th><th class="text-center">Qtd</th></tr></thead>
                        <tbody>
                            <?php $__currentLoopData = $grupoMarcasModelos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-qtd="<?php echo e($gm['qtd']); ?>"><td><?php echo e($gm['marca']); ?></td><td><?php echo e($gm['modelo']); ?></td><td class="text-center"><?php echo e($gm['qtd']); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    </div>
                    <small class="text-muted mt-1 d-block"><?php echo e(count($grupoMarcasModelos)); ?> modelo(s) · <?php echo e(collect($grupoMarcasModelos)->sum('qtd')); ?> veículo(s)</small>
                <?php else: ?>
                    <p class="text-muted mb-0">Nenhum veículo cadastrado neste grupo.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
var grupoFiltroAtivo = 'todos';
function filtrarGrupoModal() {
    aplicarFiltrosGrupo();
}
function filtrarGrupoTipo(tipo) {
    grupoFiltroAtivo = tipo;
    document.getElementById('btnTodosGrupo').className = tipo === 'todos' ? 'btn btn-primary btn-sm' : 'btn btn-outline-primary btn-sm';
    document.getElementById('btnDisponiveisGrupo').className = tipo === 'disponiveis' ? 'btn btn-primary btn-sm' : 'btn btn-outline-primary btn-sm';
    aplicarFiltrosGrupo();
}
function aplicarFiltrosGrupo() {
    var termo = (document.getElementById('filtroGrupoModal').value || '').toLowerCase().trim();
    var totalVisiveis = 0;
    document.querySelectorAll('#tabelaGrupoModal tbody tr').forEach(function(tr) {
        var texto = tr.textContent.toLowerCase();
        var qtd = Number(tr.dataset.qtd) || 0;
        var matchTexto = !termo || texto.includes(termo);
        var matchTipo = grupoFiltroAtivo === 'todos' || qtd > 0;
        var mostra = matchTexto && matchTipo;
        tr.style.display = mostra ? '' : 'none';
        if (mostra) totalVisiveis++;
    });
    var info = document.getElementById('filtroGrupoInfo');
    if (!info) {
        info = document.createElement('small');
        info.id = 'filtroGrupoInfo';
        info.className = 'text-muted d-block mt-1';
        document.querySelector('#modalGrupo .modal-body').appendChild(info);
    }
    var total = document.querySelectorAll('#tabelaGrupoModal tbody tr').length;
    info.textContent = grupoFiltroAtivo === 'disponiveis'
        ? totalVisiveis + ' de ' + total + ' modelos com veículos disponíveis'
        : 'Mostrando todos os ' + total + ' modelos';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/show.blade.php ENDPATH**/ ?>