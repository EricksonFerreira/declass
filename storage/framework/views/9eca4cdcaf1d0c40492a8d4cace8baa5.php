<?php $__env->startSection('title', 'Ajuste de Frota — Downgrade'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <div>
            <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">Ajuste de Frota</h1>
            <small class="text-muted">
                O plano <strong><?php echo e($plano->nome); ?></strong> permite até
                <strong><?php echo e($plano->limite_veiculos_texto); ?></strong> veículo(s)
            </small>
        </div>
        <a href="<?php echo e(route('assinatura.meu-plano')); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="callout callout-warning mb-3">
                <h5 class="mb-1" style="font-size:.95rem;font-weight:700;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Ação necessária
                </h5>
                <p class="mb-0" style="font-size:.875rem;">
                    Sua frota ativa tem <strong><?php echo e($todos->count()); ?></strong> veículo(s).
                    O plano <strong><?php echo e($plano->nome); ?></strong> permite até
                    <strong><?php echo e($plano->limite_veiculos); ?></strong>.
                    Selecione ao menos <strong><?php echo e($excedentes->count()); ?></strong> veículo(s) para desativar.
                    Os veículos desativados <strong>não são excluídos</strong> — podem ser reativados com upgrade.
                </p>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><i class="fas fa-exclamation-circle mr-1"></i> <?php echo e($e); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Veículos ativos
                        <span class="badge badge-primary ml-1"><?php echo e($todos->count()); ?></span>
                    </h3>
                    <span class="text-muted" style="font-size:.8rem;">
                        Desativar selecionados:
                        <strong id="contadorBadge" class="text-danger">0</strong>
                        / <?php echo e($excedentes->count()); ?> necessários
                    </span>
                </div>

                <form action="<?php echo e(route('assinatura.downgrade.confirmar')); ?>" method="POST" id="formDowngrade">
                    <?php echo csrf_field(); ?>
                    <div class="card-body p-0">
                        <table class="table table-hover table-sm mb-0" style="font-size:13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-3" style="width:36px;">
                                        <input type="checkbox" id="selectAll" title="Selecionar todos">
                                    </th>
                                    <th>Veículo</th>
                                    <th>Grupo</th>
                                    <th>Placa</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $todos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $excede = $excedentes->contains('id', $v->id); ?>
                                    <tr class="veiculo-row <?php echo e($excede ? 'table-warning' : ''); ?>"
                                        data-id="<?php echo e($v->id); ?>"
                                        onclick="toggleVeiculo(<?php echo e($v->id); ?>)"
                                        style="cursor:pointer;">
                                        <td class="pl-3 align-middle" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="veiculos_desativar[]"
                                                   value="<?php echo e($v->id); ?>"
                                                   id="vch<?php echo e($v->id); ?>"
                                                   onchange="atualizarContador()"
                                                   class="veiculo-check"
                                                   style="accent-color:#dc2626;">
                                        </td>
                                        <td class="align-middle font-weight-bold">
                                            <?php echo e($v->nome); ?>

                                            <?php if($excede): ?>
                                                <span class="badge badge-warning ml-1" style="font-size:.65rem;">excedente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-muted"><?php echo e($v->grupoFrota?->nome ?? '—'); ?></td>
                                        <td class="align-middle">
                                            <?php if($v->placa_licenca): ?>
                                                <span class="badge badge-light border"><?php echo e($v->placa_licenca); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-success">Ativo</span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                        <div id="errVeiculos" class="text-danger" style="font-size:.83rem;display:none;">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <span id="errVeiculosMsg"></span>
                        </div>
                        <div class="ml-auto d-flex" style="gap:.5rem;">
                            <a href="<?php echo e(route('assinatura.meu-plano')); ?>" class="btn btn-secondary btn-sm">Cancelar</a>
                            <button type="button" onclick="confirmarDowngrade()" class="btn btn-danger btn-sm">
                                <i class="fas fa-check mr-1"></i> Confirmar Downgrade
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
const necessario = <?php echo e($excedentes->count()); ?>;
const limite     = <?php echo e($plano->limite_veiculos ?? 9999); ?>;
const totalAtivo = <?php echo e($todos->count()); ?>;

function toggleVeiculo(id) {
    const cb = document.getElementById('vch' + id);
    cb.checked = !cb.checked;
    atualizarContador();
}

function atualizarContador() {
    const n = document.querySelectorAll('.veiculo-check:checked').length;
    document.getElementById('contadorBadge').textContent = n;

    document.querySelectorAll('.veiculo-row').forEach(row => {
        const cb = row.querySelector('.veiculo-check');
        row.classList.toggle('table-danger', cb.checked);
        row.classList.toggle('table-warning', !cb.checked && row.dataset.excede === '1');
    });
}

document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.veiculo-check').forEach(cb => { cb.checked = this.checked; });
    atualizarContador();
});

function confirmarDowngrade() {
    const selecionados = document.querySelectorAll('.veiculo-check:checked').length;
    const restantes    = totalAtivo - selecionados;

    if (restantes > limite) {
        const faltam = restantes - limite;
        document.getElementById('errVeiculosMsg').textContent =
            `Desative mais ${faltam} veículo(s). Com a seleção atual, ${restantes} ficariam ativos (limite: ${limite}).`;
        document.getElementById('errVeiculos').style.display = 'block';
        return;
    }

    document.getElementById('errVeiculos').style.display = 'none';
    document.getElementById('formDowngrade').submit();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\assinatura\downgrade.blade.php ENDPATH**/ ?>