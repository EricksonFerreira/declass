<?php
    $user = auth()->user();
    $empresa = $user?->empresa ?? null;
    $step = $empresa?->onboardingStep ?? null;
    $ehAdminEmpresa = (bool) ($user?->isAdministrador() ?? false) && (bool) $empresa;

    if (! $ehAdminEmpresa) return;
    if (!$step || $step->concluido) return;
    $planoAtivo = $empresa?->planoAtivo()?->with('plano')->first()?->plano ?? null;
    $pct        = $step->percentualConcluido($planoAtivo);
    $steps      = $step->stepsParaExibir($planoAtivo);
    $pendentes  = collect($steps)->where('feito', false);
    if ($pendentes->isEmpty()) return;
    $acessoLiberado = (bool) ($step->step_plano_contratado || $step->step_pagamento_realizado);
    $stepsLivres    = ['step_empresa_configurada', 'step_plano_contratado'];
?>

<style>
#onb-bubble{position:fixed;bottom:24px;right:24px;z-index:9990;width:54px;height:54px;border-radius:50%;background:#0a1628;color:#fff;font-size:1.2rem;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 20px rgba(10,22,40,.35);transition:transform .2s,box-shadow .2s;user-select:none}
#onb-bubble:hover{transform:scale(1.08);box-shadow:0 6px 28px rgba(10,22,40,.45)}
#onb-bubble-ring{position:absolute;inset:-4px;border-radius:50%;border:2px solid rgba(10,22,40,.25);animation:onb-pulse 2.4s ease-in-out infinite}
@keyframes onb-pulse{0%,100%{transform:scale(1);opacity:.6}50%{transform:scale(1.18);opacity:0}}
#onb-bubble-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;font-size:.65rem;font-weight:800;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff}
#onb-panel{position:fixed;bottom:90px;right:24px;z-index:9991;width:340px;background:#fff;border-radius:16px;box-shadow:0 8px 40px rgba(10,22,40,.18);border:1px solid #e2e8f0;display:none;flex-direction:column;max-height:80vh;overflow:hidden}
#onb-panel.open{display:flex;animation:onb-slide-up .22s ease}
@keyframes onb-slide-up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
#onb-panel-header{background:#0a1628;color:#fff;padding:.9rem 1rem;display:flex;justify-content:space-between;align-items:flex-start;border-radius:16px 16px 0 0;flex-shrink:0}
#onb-panel-title{font-weight:700;font-size:.88rem}
#onb-panel-sub{font-size:.73rem;color:rgba(255,255,255,.6);margin-top:.2rem}
#onb-panel-close{background:none;border:none;color:rgba(255,255,255,.6);font-size:1.3rem;cursor:pointer;line-height:1;padding:0;margin-left:.5rem;transition:color .15s}
#onb-panel-close:hover{color:#fff}
#onb-panel-progress{height:4px;background:#e2e8f0;flex-shrink:0}
#onb-panel-progress-bar{height:100%;background:#22c55e;transition:width .4s ease}
#onb-panel-steps{overflow-y:auto;padding:.6rem .75rem;display:flex;flex-direction:column;gap:.4rem;flex:1}
.onb-step-row{display:flex;align-items:center;gap:.65rem;padding:.55rem .65rem;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc}
.onb-step-feito{border-color:#bbf7d0;background:#f0fdf4}
.onb-step-pending{border-color:#e2e8f0;background:#fff}
.onb-step-bloq{opacity:.5}
.onb-step-icon{width:28px;height:28px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.75rem;background:#f1f5f9;color:#94a3b8}
.onb-step-feito .onb-step-icon{background:#dcfce7;color:#16a34a}
.onb-step-pending .onb-step-icon{background:#f1f5f9;color:#0a1628}
.onb-step-info{flex:1;min-width:0}
.onb-step-label{font-size:.78rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.onb-step-feito .onb-step-label{color:#15803d;font-weight:600}
.onb-step-hint{font-size:.68rem;color:#94a3b8;margin-top:.1rem}
.onb-step-btn{flex-shrink:0;font-size:.71rem;font-weight:700;color:#0a1628;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:.2rem .5rem;text-decoration:none;white-space:nowrap;transition:background .15s}
.onb-step-btn:hover{background:#e2e8f0;text-decoration:none;color:#0a1628}
.onb-step-check{color:#22c55e;font-size:.85rem;flex-shrink:0}
#onb-panel-footer{display:flex;justify-content:space-between;align-items:center;padding:.6rem .75rem;border-top:1px solid #f1f5f9;background:#f8fafc;border-radius:0 0 16px 16px;flex-shrink:0}
#onb-panel-link{font-size:.75rem;color:#64748b;text-decoration:none}
#onb-panel-link:hover{color:#0a1628}
#onb-panel-dismiss{font-size:.72rem;color:#94a3b8;background:none;border:none;cursor:pointer;padding:0;transition:color .15s}
#onb-panel-dismiss:hover{color:#ef4444}
@media(max-width:480px){#onb-panel{right:12px;left:12px;width:auto;bottom:80px}#onb-bubble{bottom:16px;right:16px}}
</style>


<div id="onb-bubble" title="Configuração da conta">
    <div id="onb-bubble-ring"></div>
    <i class="fas fa-rocket"></i>
    <span id="onb-bubble-badge"><?php echo e($pendentes->count()); ?></span>
</div>


<div id="onb-panel" aria-hidden="true">
    <div id="onb-panel-header">
        <div>
            <div id="onb-panel-title"><i class="fas fa-rocket mr-1"></i> Comece a usar o DECLOCK</div>
            <div id="onb-panel-sub"><?php echo e($pct); ?>% concluído · <?php echo e($pendentes->count()); ?> passo(s) restante(s)</div>
        </div>
        <button type="button" id="onb-panel-close" aria-label="Fechar">&times;</button>
    </div>
    <div id="onb-panel-progress">
        <div id="onb-panel-progress-bar" style="width:<?php echo e($pct); ?>%"></div>
    </div>
    <div id="onb-panel-steps">
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $bloqueado = !$acessoLiberado && !in_array($s['key'], $stepsLivres);
                $url = null;
                if (!$s['feito'] && !$bloqueado && Route::has($s['rota'])) {
                    $url = route($s['rota'], $s['params'] ?? []);
                    if (!empty($s['query'])) {
                        $url .= '?' . http_build_query($s['query']);
                    }
                }
            ?>
            <div class="onb-step-row <?php echo e($s['feito'] ? 'onb-step-feito' : ($bloqueado ? 'onb-step-bloq' : 'onb-step-pending')); ?>">
                <div class="onb-step-icon">
                    <?php if($s['feito']): ?>
                        <i class="fas fa-check"></i>
                    <?php elseif($bloqueado): ?>
                        <i class="fas fa-lock"></i>
                    <?php else: ?>
                        <i class="fas fa-<?php echo e($s['icone']); ?>"></i>
                    <?php endif; ?>
                </div>
                <div class="onb-step-info">
                    <div class="onb-step-label"><?php echo e($s['label']); ?></div>
                    <?php if($bloqueado): ?>
                        <div class="onb-step-hint">Disponível após ativar o teste grátis</div>
                    <?php endif; ?>
                </div>
                <?php if($url): ?>
                    <a href="<?php echo e($url); ?>" class="onb-step-btn">Ir <i class="fas fa-arrow-right"></i></a>
                <?php elseif($s['feito']): ?>
                    <i class="fas fa-check-circle onb-step-check"></i>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div id="onb-panel-footer">
        <a href="<?php echo e(route('onboarding.orientacoes')); ?>" id="onb-panel-link">
            <i class="fas fa-list-check mr-1"></i> Ver guia completo
        </a>
        <form action="<?php echo e(route('onboarding.concluir')); ?>" method="POST" style="margin:0;">
            <?php echo csrf_field(); ?>
            <button type="submit" id="onb-panel-dismiss"
                    onclick="return confirm('Fechar o guia de configuração?')">
                Fechar guia
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const bubble = document.getElementById('onb-bubble');
    const panel  = document.getElementById('onb-panel');
    const close  = document.getElementById('onb-panel-close');
    const STORE  = 'onb_panel_open';

    function openPanel()  { panel.classList.add('open'); panel.setAttribute('aria-hidden','false'); sessionStorage.setItem(STORE,'1'); }
    function closePanel() { panel.classList.remove('open'); panel.setAttribute('aria-hidden','true'); sessionStorage.removeItem(STORE); }

    bubble.addEventListener('click', function(e) {
        e.stopPropagation();
        panel.classList.contains('open') ? closePanel() : openPanel();
    });
    close.addEventListener('click', closePanel);
    document.addEventListener('click', function(e) {
        if (!panel.contains(e.target) && e.target !== bubble && !bubble.contains(e.target)) {
            closePanel();
        }
    });

    if (sessionStorage.getItem(STORE)) openPanel();
})();
</script>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\onboarding-bubble.blade.php ENDPATH**/ ?>