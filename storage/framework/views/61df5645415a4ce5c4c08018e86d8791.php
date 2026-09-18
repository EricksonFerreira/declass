<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'estacoes'  => collect(),
    'modalId'   => 'modalEstacaoMov',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'estacoes'  => collect(),
    'modalId'   => 'modalEstacaoMov',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#065f46,#047857);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h5 class="mb-0" id="<?php echo e($modalId); ?>_titulo">Selecionar Estação</h5>
                        <small style="opacity:.75" id="<?php echo e($modalId); ?>_subtitulo">Escolha a estação de origem ou destino</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-9 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar estação</label>
                            <input type="text" id="<?php echo e($modalId); ?>_busca"
                                   class="form-control form-control-sm"
                                   placeholder="Filtrar por nome da estação…"
                                   oninput="filtrarModalEstacao('<?php echo e($modalId); ?>')">
                        </div>
                        <div class="col-md-3 form-group mb-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                    onclick="document.getElementById('<?php echo e($modalId); ?>_busca').value='';filtrarModalEstacao('<?php echo e($modalId); ?>')">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2" id="<?php echo e($modalId); ?>_count">
                    <?php echo e(is_array($estacoes) ? count($estacoes) : $estacoes->count()); ?> estações disponíveis
                </p>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Nome</th>
                                <th style="width:110px"></th>
                            </tr>
                        </thead>
                        <tbody id="<?php echo e($modalId); ?>_lista">
                            <?php $__currentLoopData = $estacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $est): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $eid   = is_object($est) ? $est->id   : ($est['id']   ?? '');
                                $enome = is_object($est) ? $est->nome  : ($est['nome'] ?? '');
                                $ecidade = is_object($est) ? ($est->cidade ?? '') : ($est['cidade'] ?? '');
                            ?>
                            <tr class="mv-estacao-row" data-nome="<?php echo e(mb_strtolower($enome)); ?>">
                                <td><span class="badge badge-secondary"><?php echo e($eid); ?></span></td>
                                <td>
                                    <strong><?php echo e($enome); ?></strong>
                                    <?php if($ecidade): ?><br><small class="text-muted"><?php echo e($ecidade); ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success btn-selecionar-estacao"
                                            data-id="<?php echo e($eid); ?>" data-nome="<?php echo e($enome); ?>">
                                        <i class="fas fa-check mr-1"></i>Selecionar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div id="<?php echo e($modalId); ?>_vazio" class="text-center text-muted py-4" style="display:none">
                    <i class="fas fa-map-marker-alt fa-2x mb-2"></i>
                    <p>Nenhuma estação encontrada.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('0e1fbbfd-b169-44a2-94ff-fcc90b7e8ab9')): $__env->markAsRenderedOnce('0e1fbbfd-b169-44a2-94ff-fcc90b7e8ab9'); ?>
<?php $__env->startPush('js'); ?>
<script>
window.filtrarModalEstacao = function(modalId) {
    var busca = (document.getElementById(modalId + '_busca')?.value || '').toLowerCase().trim();
    var rows  = document.querySelectorAll('#' + modalId + '_lista .mv-estacao-row');
    var visible = 0;
    rows.forEach(function(r) {
        var match = !busca || (r.dataset.nome || '').includes(busca);
        r.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    var count = document.getElementById(modalId + '_count');
    if (count) count.textContent = visible + ' estações disponíveis';
    var vazio = document.getElementById(modalId + '_vazio');
    if (vazio) vazio.style.display = visible === 0 ? '' : 'none';
};
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\movimentacao\modal-estacao.blade.php ENDPATH**/ ?>