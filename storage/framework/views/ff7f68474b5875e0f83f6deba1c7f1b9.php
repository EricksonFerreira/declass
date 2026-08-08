<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'modalId'    => 'modalOsMov',
    'buscarUrl'  => '',
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
    'modalId'    => 'modalOsMov',
    'buscarUrl'  => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $buscarUrl = $buscarUrl ?: route('movimentacao.buscarOrdens');
?>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#92400e,#b45309);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Buscar Ordem de Serviço</h5>
                        <small style="opacity:.75">Vincule esta movimentação a uma OS existente</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 form-group mb-0">
                            <label class="small mb-1 font-weight-600">ID da OS</label>
                            <input type="number" id="<?php echo e($modalId); ?>_id"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: 5">
                        </div>
                        <div class="col-md-5 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Número / Referência</label>
                            <input type="text" id="<?php echo e($modalId); ?>_numero"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: OS-2026-00005…">
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <button type="button" class="btn btn-primary btn-sm w-100"
                                    onclick="buscarOrdensMov('<?php echo e($modalId); ?>', '<?php echo e($buscarUrl); ?>')">
                                <i class="fas fa-search mr-1"></i>Buscar
                            </button>
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                                    onclick="['_id','_numero'].forEach(function(s){var el=document.getElementById('<?php echo e($modalId); ?>'+s);if(el)el.value='';});buscarOrdensMov('<?php echo e($modalId); ?>', '<?php echo e($buscarUrl); ?>')">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <div id="<?php echo e($modalId); ?>_loading" class="text-center py-3 d-none">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th style="width:110px">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="<?php echo e($modalId); ?>_lista">
                            <tr><td colspan="5" class="text-center text-muted py-3">
                                <i class="fas fa-search mr-1"></i>Preencha os filtros e clique em Buscar
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('7a434b30-a13d-4ec1-b42b-5762e288c8d7')): $__env->markAsRenderedOnce('7a434b30-a13d-4ec1-b42b-5762e288c8d7'); ?>
<?php $__env->startPush('js'); ?>
<script>
window.buscarOrdensMov = function(modalId, url) {
    var id     = (document.getElementById(modalId + '_id')?.value     || '').trim();
    var numero = (document.getElementById(modalId + '_numero')?.value || '').trim();
    var q      = id || numero;

    var loading = document.getElementById(modalId + '_loading');
    var lista   = document.getElementById(modalId + '_lista');

    if (loading) loading.classList.remove('d-none');
    if (lista)   lista.innerHTML = '';

    var params = new URLSearchParams();
    if (q) params.set('q', q);

    fetch(url + '?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (loading) loading.classList.add('d-none');
            if (!data.length) {
                lista.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Nenhuma OS encontrada.</td></tr>';
                return;
            }
            lista.innerHTML = data.map(function(os) {
                return '<tr>' +
                    '<td><strong>#' + os.id + '</strong></td>' +
                    '<td>' + os.numero + '</td>' +
                    '<td><span class="badge badge-warning text-dark">' + os.status + '</span></td>' +
                    '<td>' + os.data + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-primary btn-sel-os-modal" ' +
                        'data-id="' + os.id + '" data-label="OS ' + os.numero + '">' +
                        '<i class="fas fa-check mr-1"></i>Selecionar</button></td>' +
                    '</tr>';
            }).join('');

            lista.querySelectorAll('.btn-sel-os-modal').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var hiddenInput  = document.getElementById('mv_os_id');
                    var displayInput = document.getElementById('mv_os_display');
                    var clearBtn     = document.getElementById('btnLimparOsMov');
                    if (hiddenInput)  hiddenInput.value  = this.dataset.id;
                    if (displayInput) displayInput.value = this.dataset.label;
                    if (clearBtn)     clearBtn.classList.remove('d-none');
                    $('#' + modalId).modal('hide');
                });
            });
        })
        .catch(function() { if (loading) loading.classList.add('d-none'); });
};
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/movimentacao/modal-os.blade.php ENDPATH**/ ?>