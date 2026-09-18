<?php
    $etapaAtiva = 'onboarding.plano';
    $etapasConc = ['onboarding.empresa'];
?>


<?php $__env->startSection('page_title', 'Escolha seu Plano'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.planos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}
@media (max-width: 900px)  { .planos-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px)  { .planos-grid { grid-template-columns: 1fr; } }

.plan-card {
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
    cursor: pointer;
    transition: border-color .25s, box-shadow .25s, transform .25s;
    position: relative;
    background: #fff;
    display: flex; flex-direction: column;
}
.plan-card:hover { border-color: #0a1628; transform: translateY(-3px); box-shadow: 0 8px 24px rgba(10,22,40,.1); }
.plan-card.selected { border-color: #0a1628; box-shadow: 0 0 0 4px rgba(10,22,40,.08); }
.plan-card.destaque { border-color: #0a1628; }
.plan-badge {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    background: #0a1628; color: #fff;
    font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
    padding: .25rem .75rem; border-radius: 999px;
    white-space: nowrap;
}
.plan-name { font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 1.15rem; color: #0a1628; margin-bottom: 1rem; }
.plan-price { margin-bottom: 1rem; }
.plan-price .amount {
    font-family: 'Raleway', sans-serif; font-weight: 900;
    font-size: 2.2rem; color: #0a1628; line-height: 1;
    transition: all .3s;
}
.plan-price .period { font-size: .78rem; color: #64748b; }
.plan-price .annual-note { font-size: .72rem; color: #16a34a; display: none; margin-top: .2rem; }
.plan-desc { font-size: .8rem; color: #64748b; line-height: 1.6; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9; flex-grow: 1; }
.plan-features { list-style: none; display: flex; flex-direction: column; gap: .55rem; }
.plan-features li { font-size: .8rem; color: #374151; display: flex; align-items: flex-start; gap: .5rem; }
.plan-features li i { color: #22c55e; font-size: .72rem; margin-top: .18rem; flex-shrink: 0; }

.toggle-wrap { display: flex; align-items: center; justify-content: center; gap: .75rem; margin-bottom: 2rem; }
.toggle-wrap span { font-size: .875rem; font-weight: 600; color: #94a3b8; }
.toggle-wrap span.on { color: #0a1628; }
.toggle-switch {
    width: 44px; height: 24px; border-radius: 999px;
    background: #e2e8f0; border: none; cursor: pointer; position: relative;
    transition: background .25s;
}
.toggle-switch::after {
    content: ''; position: absolute; width: 18px; height: 18px; border-radius: 50%;
    background: #fff; top: 3px; left: 3px;
    transition: left .25s;
    box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.toggle-switch.anual { background: #0a1628; }
.toggle-switch.anual::after { left: 23px; }

.discount-badge { background: #dcfce7; color: #15803d; font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 999px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="onb-card onb-card-wide">
    <h1 class="onb-card-title">Escolha o plano ideal para sua empresa</h1>
    <p class="onb-card-sub">Sem taxa de implantação. Cancele quando quiser. Você pode mudar de plano a qualquer momento.</p>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="toggle-wrap">
        <span class="on" id="labelMensal">Mensal</span>
        <button type="button" class="toggle-switch" id="togglePeriodo" aria-label="Alternar período"></button>
        <span id="labelAnual">Anual <span class="discount-badge">Economize até 20%</span></span>
    </div>

    <form action="<?php echo e(route('onboarding.plano.confirmar')); ?>" method="POST" id="formPlano">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="periodicidade" id="inputPeriodicidade" value="mensal">
        <input type="hidden" name="plano_id" id="inputPlanoId" value="">
        <input type="hidden" name="metodo_pagamento" value="automatico">

        <div class="planos-grid">
            <?php $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="plan-card <?php echo e($plano->destaque ? 'destaque' : ''); ?>"
                     data-plano="<?php echo e($plano->id); ?>"
                     onclick="selecionarPlano(<?php echo e($plano->id); ?>)">
                    <?php if($plano->destaque): ?>
                        <div class="plan-badge"><i class="fas fa-star"></i> Mais escolhido</div>
                    <?php endif; ?>
                    <div class="plan-name"><?php echo e($plano->nome); ?></div>
                    <div class="plan-price">
                        <div>
                            <span class="amount"
                                  data-mensal="<?php echo e(number_format($plano->valor_mensal, 2, ',', '.')); ?>"
                                  data-anual="<?php echo e(number_format($plano->valor_anual, 2, ',', '.')); ?>">
                                <?php echo e(number_format($plano->valor_mensal, 2, ',', '.')); ?>

                            </span>
                        </div>
                        <span class="period">/mês</span>
                        <div class="annual-note">
                            <?php if($plano->economia_anual > 0): ?>
                                Economia de R$ <?php echo e(number_format($plano->economia_anual, 2, ',', '.')); ?>/ano
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="plan-desc"><?php echo e($plano->descricao); ?></p>
                    <ul class="plan-features">
                        <?php $__currentLoopData = $plano->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><i class="fas fa-check"></i> <?php echo e($item->descricao); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $storageMb = $plano->limite_armazenamento_mb ?? 0;
                            $storageTxt = $storageMb >= 1024 ? number_format($storageMb / 1024, 1, ',', '.') . ' GB' : $storageMb . ' MB';
                        ?>
                        <li><i class="fas fa-database"></i> Armazenamento: <?php echo e($storageTxt); ?></li>
                    </ul>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div id="erroPlano" class="alert alert-danger" style="display:none;">
            <i class="fas fa-exclamation-circle mr-1"></i> Selecione um plano para continuar.
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; margin-top:1rem;">
            <a href="<?php echo e(route('onboarding.empresa')); ?>" style="font-size:.875rem; color:#64748b;">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <button type="button" onclick="avancar()" class="btn btn-primary btn-lg">
                Continuar <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let planoSelecionado = null;
let isAnual = false;

function selecionarPlano(id) {
    planoSelecionado = id;
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.plan-card[data-plano="${id}"]`)?.classList.add('selected');
    document.getElementById('inputPlanoId').value = id;
    document.getElementById('erroPlano').style.display = 'none';
}

function avancar() {
    if (!planoSelecionado) {
        document.getElementById('erroPlano').style.display = 'block';
        document.querySelector('.planos-grid').scrollIntoView({ behavior: 'smooth' });
        return;
    }
    document.getElementById('formPlano').submit();
}

// Toggle período
document.getElementById('togglePeriodo').addEventListener('click', function () {
    isAnual = !isAnual;
    this.classList.toggle('anual', isAnual);
    document.getElementById('labelMensal').classList.toggle('on', !isAnual);
    document.getElementById('labelAnual').classList.toggle('on', isAnual);
    document.getElementById('inputPeriodicidade').value = isAnual ? 'anual' : 'mensal';

    document.querySelectorAll('.amount').forEach(el => {
        el.textContent = isAnual ? el.dataset.anual : el.dataset.mensal;
    });
    document.querySelectorAll('.annual-note').forEach(el => {
        el.style.display = isAnual ? 'block' : 'none';
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('onboarding._layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\onboarding\plano.blade.php ENDPATH**/ ?>