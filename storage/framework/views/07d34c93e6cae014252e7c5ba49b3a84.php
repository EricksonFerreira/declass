<?php $__env->startSection('title', $titulo); ?>

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
<?php
    // Reutiliza o mesmo mapa do index para exibir ícone + label nos chips
    $grupoMeta = [
        'reserva'                          => ['label' => 'Reservas',                   'icon' => 'fas fa-calendar-check'],
        'veiculo'                          => ['label' => 'Veículos',                   'icon' => 'fas fa-car'],
        'usuario'                          => ['label' => 'Usuários',                   'icon' => 'fas fa-users'],
        'permissao'                        => ['label' => 'Permissões',                 'icon' => 'fas fa-key'],
        'regra'                            => ['label' => 'Regras de Acesso',           'icon' => 'fas fa-shield-alt'],
        'multa'                            => ['label' => 'Multas',                     'icon' => 'fas fa-file-invoice-dollar'],
        'sinistro'                         => ['label' => 'Sinistros',                  'icon' => 'fas fa-car-crash'],
        'movimentacao'                     => ['label' => 'Movimentações',              'icon' => 'fas fa-exchange-alt'],
        'financeiro'                       => ['label' => 'Financeiro',                 'icon' => 'fas fa-dollar-sign'],
        'calcao'                           => ['label' => 'Caução',                     'icon' => 'fas fa-hand-holding-usd'],
        'empresa'                          => ['label' => 'Empresa',                    'icon' => 'fas fa-building'],
        'estacao'                          => ['label' => 'Estações',                   'icon' => 'fas fa-map-marker-alt'],
        'modelo'                           => ['label' => 'Modelos de Veículo',         'icon' => 'fas fa-car-side'],
        'marca'                            => ['label' => 'Marcas',                     'icon' => 'fas fa-tag'],
        'grupo_frota'                      => ['label' => 'Grupos de Frota',            'icon' => 'fas fa-layer-group'],
        'frota'                            => ['label' => 'Frota',                      'icon' => 'fas fa-truck'],
        'dano'                             => ['label' => 'Danos',                      'icon' => 'fas fa-exclamation-triangle'],
        'checklist'                        => ['label' => 'Checklists',                 'icon' => 'fas fa-tasks'],
        'nfse'                             => ['label' => 'NFS-e',                      'icon' => 'fas fa-file-alt'],
        'site'                             => ['label' => 'Site de Apresentação',       'icon' => 'fas fa-globe'],
        'dashboard'                        => ['label' => 'Dashboard',                  'icon' => 'fas fa-chart-bar'],
        'pagamento'                        => ['label' => 'Pagamento',                  'icon' => 'fas fa-credit-card'],
        'opcional'                         => ['label' => 'Opcionais',                  'icon' => 'fas fa-plus-circle'],
        'cupom_desconto'                   => ['label' => 'Cupons de Desconto',         'icon' => 'fas fa-ticket-alt'],
        'stripe'                           => ['label' => 'Stripe',                     'icon' => 'fab fa-stripe-s'],
    ];
?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger col-xl-8 col-lg-10 col-md-12">
            <ul class="mb-0 pl-3">
                <?php $__currentLoopData = array_unique($errors->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="col-xl-8 col-lg-10 col-md-12 px-0">
        <div class="pme-card shadow-sm">

            
            <div class="pme-card__header">
                <div class="pme-card__icon">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h4 class="pme-card__title"><?php echo e(isset($permissao) ? 'Editar Permissão' : 'Nova Permissão'); ?></h4>
                    <p class="pme-card__sub">
                        Permissões controlam o que cada perfil de usuário pode acessar ou fazer no sistema.
                    </p>
                </div>
            </div>

            
            <div class="pme-card__body">
                <?php if (isset($component)) { $__componentOriginal18ad2e0d264f9740dc73fff715357c28 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18ad2e0d264f9740dc73fff715357c28 = $attributes; } ?>
<?php $component = App\View\Components\Form::resolve(['method' => ''.e($metodo).'','rowClass' => 'row'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Form::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e($rota).'']); ?>

                    
                    <div class="col-12 mb-3">
                        <div class="pme-section-title">
                            <i class="fas fa-info-circle mr-1"></i> Identificação
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label class="pme-label" for="name">
                                Nome técnico <span class="text-danger">*</span>
                                <span class="pme-hint">Ex: <code>reserva.index</code>, <code>veiculo.create</code></span>
                            </label>
                            <input type="text" id="name" name="name"
                                class="form-control form-control-sm pme-input"
                                value="<?php echo e(old('name', $permissao->name ?? '')); ?>"
                                placeholder="modulo.acao"
                                required autocomplete="off">
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <label class="pme-label" for="guard_name">
                                Guard <span class="text-danger">*</span>
                                <span class="pme-hint">Normalmente <code>web</code></span>
                            </label>
                            <input type="text" id="guard_name" name="guard_name"
                                class="form-control form-control-sm pme-input"
                                value="<?php echo e(old('guard_name', $permissao->guard_name ?? 'web')); ?>"
                                required>
                        </div>
                    </div>

                    
                    <div class="col-12 mb-2">
                        <div class="pme-section-title">
                            <i class="fas fa-th-large mr-1"></i> Grupo / Módulo
                            <span id="pme-grupo-badge" class="pme-grupo-preview d-none ml-2"></span>
                        </div>
                    </div>

                    <div class="col-12 mb-1">
                        <div class="form-group mb-1">
                            <label class="pme-label" for="grupo">
                                Nome do grupo
                                <span class="pme-hint">Clique em um módulo abaixo ou digite manualmente</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" id="grupo" name="grupo"
                                    class="form-control form-control-sm pme-input"
                                    value="<?php echo e(old('grupo', $permissao->grupo ?? $grupoPreSelecionado)); ?>"
                                    placeholder="Ex: reserva, veiculo, usuario…"
                                    autocomplete="off"
                                    style="max-width:280px">
                                <button type="button" id="pme-btn-clear-grupo" class="btn btn-sm btn-outline-secondary pme-btn-clear"
                                    title="Limpar grupo" style="display:none">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-12 mb-3">
                        <div class="pme-chips-wrap">
                            <?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $m = $grupoMeta[$g] ?? null; ?>
                                <button type="button"
                                    class="pme-chip <?php echo e(old('grupo', $permissao->grupo ?? $grupoPreSelecionado) === $g ? 'pme-chip--active' : ''); ?>"
                                    data-grupo="<?php echo e($g); ?>"
                                    title="<?php echo e($m['label'] ?? $g); ?>">
                                    <?php if($m): ?>
                                        <i class="<?php echo e($m['icon']); ?> pme-chip__icon"></i>
                                        <span><?php echo e($m['label']); ?></span>
                                    <?php else: ?>
                                        <i class="fas fa-folder pme-chip__icon"></i>
                                        <span><?php echo e($g); ?></span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-lightbulb text-warning mr-1"></i>
                            Se deixar em branco, o grupo é detectado automaticamente pelo prefixo do nome técnico.
                        </small>
                    </div>

                    
                    <div class="col-12 mb-3">
                        <div class="pme-section-title">
                            <i class="fas fa-align-left mr-1"></i> Descrição
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="pme-label" for="descricao">
                                O que esta permissão concede
                                <span class="pme-hint">Em linguagem simples — o que o usuário poderá fazer</span>
                            </label>
                            <textarea id="descricao" name="descricao" rows="3"
                                class="form-control form-control-sm pme-input"
                                placeholder="Ex: Permite que o usuário visualize a lista de todas as reservas da empresa."><?php echo e(old('descricao', $permissao->descricao ?? '')); ?></textarea>
                        </div>
                    </div>

                    
                    <div class="col-12 mb-3">
                        <div class="alert alert-light pme-info-box">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            <div>
                                <strong>Padrão de nomenclatura:</strong>
                                Use <code>modulo.acao</code> para agrupamento automático.
                                Exemplos: <code>reserva.index</code>, <code>veiculo.edit</code>, <code>multa.delete</code>.
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-12 d-flex gap-2 mt-1">
                        <button type="submit" class="btn btn-primary pme-btn-save flex-fill">
                            <i class="fas fa-save mr-1"></i> Salvar Permissão
                        </button>
                        <a href="<?php echo e(route('permissao.index')); ?>" class="btn btn-outline-secondary pme-btn-cancel">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                    </div>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18ad2e0d264f9740dc73fff715357c28)): ?>
<?php $attributes = $__attributesOriginal18ad2e0d264f9740dc73fff715357c28; ?>
<?php unset($__attributesOriginal18ad2e0d264f9740dc73fff715357c28); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18ad2e0d264f9740dc73fff715357c28)): ?>
<?php $component = $__componentOriginal18ad2e0d264f9740dc73fff715357c28; ?>
<?php unset($__componentOriginal18ad2e0d264f9740dc73fff715357c28); ?>
<?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
.pme-card {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e5eaf2;
    background: #fff;
}
.pme-card__header {
    display: flex; align-items: center; gap: 1rem;
    background: linear-gradient(120deg, #f0f4ff 0%, #e8f0fe 100%);
    padding: 1.2rem 1.5rem;
}
.pme-card__icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: #4b80ff20;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: #4b80ff; flex-shrink: 0;
}
.pme-card__title { font-size: 1.1rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; }
.pme-card__sub   { font-size: .82rem; color: #6c757d; margin: 0; }
.pme-card__body  { padding: 1.5rem; }

.pme-section-title {
    font-size: .88rem; font-weight: 700; color: #21364f;
    letter-spacing: .02em; padding-bottom: 6px;
    border-bottom: 2px solid #e8f0fe; margin-bottom: 1rem;
    display: flex; align-items: center;
}
.pme-label {
    font-weight: 600; font-size: .84rem; color: #2c4a6e;
    display: block; margin-bottom: .35rem;
}
.pme-hint { font-weight: 400; font-size: .76rem; color: #9aacbf; margin-left: .3rem; }
.pme-input { border-radius: 8px; border-color: #d8e1ec; min-height: 36px; }
.pme-input:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75,128,255,.18); }

/* ── Chips ── */
.pme-chips-wrap {
    display: flex; flex-wrap: wrap; gap: .4rem;
    max-height: 180px; overflow-y: auto;
    padding: .5rem; background: #f8faff;
    border-radius: 10px; border: 1px solid #e5eaf2;
}
.pme-chip {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .28rem .65rem;
    border-radius: 20px;
    border: 1.5px solid #d8e1ec;
    background: #fff;
    font-size: .78rem; color: #4a5568;
    cursor: pointer; transition: all .15s;
    white-space: nowrap;
}
.pme-chip:hover { border-color: #4b80ff; color: #4b80ff; background: #f0f4ff; }
.pme-chip--active {
    border-color: #4b80ff; background: #4b80ff; color: #fff !important;
}
.pme-chip--active:hover { background: #3a6fe0; }
.pme-chip__icon { font-size: .72rem; opacity: .8; }

/* Grupo preview badge */
.pme-grupo-preview {
    font-size: .75rem; font-weight: 600;
    background: #4b80ff; color: #fff;
    padding: .15rem .55rem; border-radius: 10px;
    vertical-align: middle;
}

.pme-btn-clear { padding: .2rem .5rem; }
.pme-info-box {
    border: 1px solid #d0e3ff; border-radius: 10px;
    background: #f4f8ff;
    display: flex; align-items: flex-start; gap: .5rem; font-size: .82rem;
}
.pme-btn-save   { border-radius: 10px; font-weight: 600; }
.pme-btn-cancel { border-radius: 10px; }
.gap-2 { gap: .5rem; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
(function () {
    const nameEl   = document.getElementById('name');
    const grupoEl  = document.getElementById('grupo');
    const clearBtn = document.getElementById('pme-btn-clear-grupo');
    const badge    = document.getElementById('pme-grupo-badge');
    const chips    = document.querySelectorAll('.pme-chip');

    // ── Chip click → preenche campo ──
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const val = this.dataset.grupo;
            grupoEl.value = val;
            setActiveChip(val);
            updateBadge(val);
            updateClearBtn();
        });
    });

    // ── Campo grupo → atualiza chip ativo ──
    grupoEl.addEventListener('input', function () {
        setActiveChip(this.value.trim());
        updateBadge(this.value.trim());
        updateClearBtn();
    });

    // ── Nome técnico → sugere grupo automaticamente ──
    nameEl && nameEl.addEventListener('input', function () {
        const name = this.value.trim();
        // Só auto-preenche se grupo ainda estiver vazio
        if (grupoEl.value.trim() !== '') return;
        if (name.includes('.')) {
            const prefix = name.split('.')[0];
            grupoEl.value = prefix;
            setActiveChip(prefix);
            updateBadge(prefix);
            updateClearBtn();
        }
    });

    // ── Botão limpar ──
    clearBtn && clearBtn.addEventListener('click', function () {
        grupoEl.value = '';
        setActiveChip('');
        updateBadge('');
        updateClearBtn();
        grupoEl.focus();
    });

    function setActiveChip(val) {
        chips.forEach(function (c) {
            c.classList.toggle('pme-chip--active', c.dataset.grupo === val);
        });
    }

    function updateBadge(val) {
        if (val) {
            badge.textContent = val;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function updateClearBtn() {
        if (clearBtn) clearBtn.style.display = grupoEl.value.trim() ? '' : 'none';
    }

    // Init state
    setActiveChip(grupoEl.value.trim());
    updateBadge(grupoEl.value.trim());
    updateClearBtn();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\permissao\add-edit.blade.php ENDPATH**/ ?>