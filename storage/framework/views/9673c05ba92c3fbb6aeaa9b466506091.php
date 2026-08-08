<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'statuses'  => collect(),   // Collection de StatusMovimentacao
    'selectId'  => 'status_movimentacao_id',
    'modalId'   => 'modalStatusMov',
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
    'statuses'  => collect(),   // Collection de StatusMovimentacao
    'selectId'  => 'status_movimentacao_id',
    'modalId'   => 'modalStatusMov',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
$corMap = [
    'success'   => ['bg'=>'#d1fae5','txt'=>'#065f46','label'=>'Concluído/Sucesso'],
    'info'      => ['bg'=>'#dbeafe','txt'=>'#1e40af','label'=>'Informativo'],
    'warning'   => ['bg'=>'#fef3c7','txt'=>'#92400e','label'=>'Atenção'],
    'danger'    => ['bg'=>'#fee2e2','txt'=>'#991b1b','label'=>'Crítico'],
    'secondary' => ['bg'=>'#f1f5f9','txt'=>'#475569','label'=>'Neutro'],
    'purple'    => ['bg'=>'#ede9fe','txt'=>'#5b21b6','label'=>'Aguardando'],
    'orange'    => ['bg'=>'#ffedd5','txt'=>'#9a3412','label'=>'Bloqueado'],
    'teal'      => ['bg'=>'#ccfbf1','txt'=>'#115e59','label'=>'Em Fila'],
];
?>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#0c1428,#1a2848);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Status da Movimentação</h5>
                        <small style="opacity:.75">Selecione o status atual desta movimentação</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-5 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar</label>
                            <input type="text" id="<?php echo e($modalId); ?>_busca"
                                   class="form-control form-control-sm"
                                   placeholder="Filtrar por nome…"
                                   oninput="filtrarModalStatus('<?php echo e($modalId); ?>')">
                        </div>
                        <div class="col-md-4 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Categoria de cor</label>
                            <select id="<?php echo e($modalId); ?>_cor" class="form-control form-control-sm"
                                    onchange="filtrarModalStatus('<?php echo e($modalId); ?>')">
                                <option value="">Todas as categorias</option>
                                <?php $__currentLoopData = $corMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cor => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cor); ?>"><?php echo e($meta['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                    onclick="document.getElementById('<?php echo e($modalId); ?>_busca').value='';document.getElementById('<?php echo e($modalId); ?>_cor').value='';filtrarModalStatus('<?php echo e($modalId); ?>')">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2" id="<?php echo e($modalId); ?>_count"><?php echo e($statuses->count()); ?> status disponíveis</p>

                <div class="row" id="<?php echo e($modalId); ?>_lista">
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $cor    = $st->cor ?? 'secondary';
                        $meta   = $corMap[$cor] ?? $corMap['secondary'];
                    ?>
                    <div class="col-md-6 col-lg-4 mb-3 mv-status-card-wrap"
                         data-nome="<?php echo e(mb_strtolower($st->nome ?? '')); ?>"
                         data-cor="<?php echo e($cor); ?>">
                        <div style="border:1px solid #e2e9f4;border-radius:12px;padding:14px;background:#fff;transition:all .2s;cursor:pointer"
                             onmouseover="this.style.borderColor='<?php echo e($meta['txt']); ?>';this.style.boxShadow='0 4px 16px rgba(0,0,0,.08)'"
                             onmouseout="this.style.borderColor='#e2e9f4';this.style.boxShadow='none'">
                            <div class="d-flex align-items-start" style="gap:10px">
                                <div style="width:38px;height:38px;border-radius:10px;background:<?php echo e($meta['bg']); ?>;color:<?php echo e($meta['txt']); ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.85rem">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div class="d-flex align-items-center" style="gap:6px;margin-bottom:4px">
                                        <strong style="font-size:.88rem"><?php echo e($st->nome); ?></strong>
                                        <span style="font-size:.68rem;padding:2px 7px;border-radius:20px;background:<?php echo e($meta['bg']); ?>;color:<?php echo e($meta['txt']); ?>;font-weight:600"><?php echo e($meta['label']); ?></span>
                                    </div>
                                    <?php if($st->descricao): ?>
                                    <p class="mb-2 text-muted" style="font-size:.78rem;line-height:1.4"><?php echo e($st->descricao); ?></p>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-primary btn-selecionar-status"
                                            data-id="<?php echo e($st->id); ?>" data-nome="<?php echo e($st->nome); ?>"
                                            style="font-size:.75rem;padding:3px 10px;border-radius:20px">
                                        <i class="fas fa-check mr-1"></i>Selecionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div id="<?php echo e($modalId); ?>_vazio" class="text-center text-muted py-4" style="display:none">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Nenhum status encontrado.</p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('aa0ef0f5-49c1-478c-bf34-c82ed5e64913')): $__env->markAsRenderedOnce('aa0ef0f5-49c1-478c-bf34-c82ed5e64913'); ?>
<?php $__env->startPush('js'); ?>
<script>
window.filtrarModalStatus = function(modalId) {
    var busca = (document.getElementById(modalId + '_busca')?.value || '').toLowerCase().trim();
    var cor   = (document.getElementById(modalId + '_cor')?.value || '').toLowerCase().trim();
    var wraps = document.querySelectorAll('#' + modalId + '_lista .mv-status-card-wrap');
    var visible = 0;
    wraps.forEach(function(w) {
        var matchBusca = !busca || (w.dataset.nome || '').includes(busca);
        var matchCor   = !cor   || (w.dataset.cor  || '') === cor;
        var show = matchBusca && matchCor;
        w.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var count = document.getElementById(modalId + '_count');
    if (count) count.textContent = visible + ' status disponíveis';
    var vazio = document.getElementById(modalId + '_vazio');
    if (vazio) vazio.style.display = visible === 0 ? '' : 'none';
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-selecionar-status').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id   = this.dataset.id;
            var sel  = document.getElementById('status_movimentacao_id');
            if (sel) sel.value = id;
            $('.modal').modal('hide');
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/movimentacao/modal-status.blade.php ENDPATH**/ ?>