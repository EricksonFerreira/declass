<?php
    $etapaAtiva = 'onboarding.orientacoes';
    $etapasConc = ['onboarding.empresa', 'onboarding.cores'];
?>


<?php $__env->startSection('page_title', 'Primeiros Passos'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.steps-list { display: flex; flex-direction: column; gap: .75rem; margin-bottom: 2rem; }
.step-item {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1.1rem 1.25rem; border-radius: 14px;
    border: 1.5px solid #e2e8f0; background: #fff;
    transition: border-color .2s, box-shadow .2s;
}
.step-item:not(.bloqueado):hover { border-color: #0a1628; box-shadow: 0 4px 16px rgba(10,22,40,.07); }
.step-item.feito { border-color: #22c55e; background: #f0fdf4; }
.step-item.bloqueado { border-color: #e2e8f0; background: #f8fafc; opacity: .55; cursor: not-allowed; }
.step-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: #f1f5f9; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: #0a1628; flex-shrink: 0;
    transition: background .2s;
}
.step-item.feito .step-icon { background: #dcfce7; color: #16a34a; }
.step-item.bloqueado .step-icon { background: #f1f5f9; color: #94a3b8; }
.step-body { flex: 1; }
.step-label { font-weight: 700; font-size: .9rem; color: #0f172a; margin-bottom: .2rem; }
.step-desc { font-size: .8rem; color: #64748b; line-height: 1.5; }
.step-item.feito .step-label { color: #15803d; }
.step-item.bloqueado .step-label { color: #94a3b8; }
.step-item.bloqueado .step-desc { color: #94a3b8; }
.step-check { color: #22c55e; font-size: .95rem; flex-shrink: 0; align-self: center; }
.step-lock { color: #cbd5e1; font-size: .95rem; flex-shrink: 0; align-self: center; }
.step-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .78rem; font-weight: 700; color: #0a1628;
    background: #f1f5f9; border: 1px solid #e2e8f0;
    padding: .3rem .7rem; border-radius: 7px; margin-top: .5rem;
    transition: background .2s;
}
.step-btn:hover { background: #e2e8f0; }
.step-bloqueado-hint {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .75rem; color: #94a3b8; margin-top: .5rem;
}

.progresso-bar-wrap { background: #f1f5f9; border-radius: 999px; height: 8px; margin: .5rem 0 .25rem; overflow: hidden; }
.progresso-bar { height: 100%; background: #0a1628; border-radius: 999px; transition: width .5s ease; }

.payment-info-box {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
    padding: 1.1rem 1.25rem; margin-bottom: 1.5rem;
}

.btn-painel-disabled {
    pointer-events: none; opacity: .45; cursor: not-allowed;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="onb-card" style="max-width:680px;">
    
    <div style="text-align:center; margin-bottom:2rem;">
        <div style="font-size:3rem; margin-bottom:.5rem;">🎉</div>
        <h1 class="onb-card-title" style="text-align:center;">Bem-vindo ao DECLOCK!</h1>
        <p style="font-size:.9rem; color:#64748b; line-height:1.65;">
            Sua conta está pronta. Siga os passos abaixo para
            configurar tudo e começar a gerenciar o tempo da sua equipe.
        </p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <?php
        $pagPendente = $empresa->pagamentosPlano()
            ->where('status', 'pendente')
            ->orderBy('created_at', 'desc')
            ->first();
    ?>
    <?php if($pagPendente): ?>
        <div class="payment-info-box">
            <div style="font-weight:700; color:#92400e; margin-bottom:.4rem;">
                <i class="fas fa-clock mr-1"></i> Pagamento pendente
            </div>
            <div style="font-size:.84rem; color:#78350f; line-height:1.6;">
                <?php echo e($pagPendente->instrucoes_pagamento ?? 'Você receberá as instruções de pagamento por e-mail em breve.'); ?>

            </div>
            <div style="font-size:.75rem; color:#92400e; margin-top:.5rem; font-weight:600;">
                Referência: <?php echo e($pagPendente->referencia_interna); ?> · Vencimento: <?php echo e($pagPendente->data_vencimento->format('d/m/Y')); ?>

            </div>
        </div>
    <?php endif; ?>

    
    <?php
        $planoAtivo = $empresa->planoAtivo()?->with('plano')->first()?->plano ?? null;
        $pct = $step->percentualConcluido($planoAtivo);
    ?>
    <div style="margin-bottom:1.5rem;">
        <div style="display:flex; justify-content:space-between; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:.3rem;">
            <span>Progresso de configuração</span>
            <span><?php echo e($pct); ?>%</span>
        </div>
        <div class="progresso-bar-wrap">
            <div class="progresso-bar" style="width:<?php echo e($pct); ?>%;"></div>
        </div>
    </div>

    
    <?php
        $acessoLiberado = (bool) ($step->step_plano_contratado || $step->step_pagamento_realizado);
        $stepsExibir    = $step->stepsParaExibir($planoAtivo);
        // Keys que ficam liberadas mesmo sem teste ativo
        $stepsLivres = ['step_empresa_configurada', 'step_plano_contratado'];
    ?>
    <div class="steps-list">
        <?php $__currentLoopData = $stepsExibir; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $podeIr    = Route::has($s['rota']);
                $bloqueado = !$acessoLiberado && !in_array($s['key'], $stepsLivres);
                $classes   = trim(($s['feito'] ? 'feito' : '') . ' ' . ($bloqueado ? 'bloqueado' : ''));
            ?>
            <div class="step-item <?php echo e($classes); ?>">
                <div class="step-icon">
                    <i class="fas fa-<?php echo e($bloqueado ? 'lock' : $s['icone']); ?>"></i>
                </div>
                <div class="step-body">
                    <div class="step-label"><?php echo e($s['label']); ?></div>
                    <div class="step-desc"><?php echo e($s['descricao']); ?></div>
                    <?php if($bloqueado): ?>
                        <span class="step-bloqueado-hint">
                            <i class="fas fa-lock"></i> Disponível após ativar o teste grátis
                        </span>
                    <?php elseif(!$s['feito'] && $podeIr): ?>
                        <?php
                            $url = route($s['rota'], $s['params'] ?? []);
                            if (!empty($s['query'])) {
                                $url .= '?' . http_build_query($s['query']);
                            }
                        ?>
                        <a href="<?php echo e($url); ?>" class="step-btn">
                            <i class="fas fa-arrow-right"></i> Fazer agora
                        </a>
                    <?php endif; ?>
                </div>
                <?php if($s['feito']): ?>
                    <i class="fas fa-check-circle step-check"></i>
                <?php elseif($bloqueado): ?>
                    <i class="fas fa-lock step-lock"></i>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem;">
        <span style="font-size:.83rem; color:#94a3b8;">
            <?php if(!$acessoLiberado): ?>
                Ative o teste grátis para liberar o acesso ao painel.
            <?php else: ?>
                Você pode completar a configuração depois, pelo painel.
            <?php endif; ?>
        </span>
        <?php if($acessoLiberado): ?>
            <form action="<?php echo e(route('onboarding.concluir')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-primary btn-lg">
                    Ir para o Painel <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        <?php else: ?>
            <button type="button" class="btn btn-primary btn-lg btn-painel-disabled" disabled title="Ative o teste grátis primeiro">
                Ir para o Painel <i class="fas fa-lock ml-1"></i>
            </button>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('onboarding._layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\onboarding\orientacoes.blade.php ENDPATH**/ ?>