<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'prefix' => 'veiculo',
    'title' => 'Buscar Veículo',
    'buscarUrl' => null,
    'triggerId' => null,
    'targetSelectId' => null,
    'hiddenInputId' => null,
    'displayInputId' => null,
    'clearBtnId' => null,
    'autoFillPlacaSelector' => null,
    'autoFillKmsSelector' => null,
    'autoFillNivelSelector' => null,
    'filtroMarcas' => [],
    'filtroGrupos' => [],
    'filtroTipos' => [],
    'filtroStatus' => [],
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
    'prefix' => 'veiculo',
    'title' => 'Buscar Veículo',
    'buscarUrl' => null,
    'triggerId' => null,
    'targetSelectId' => null,
    'hiddenInputId' => null,
    'displayInputId' => null,
    'clearBtnId' => null,
    'autoFillPlacaSelector' => null,
    'autoFillKmsSelector' => null,
    'autoFillNivelSelector' => null,
    'filtroMarcas' => [],
    'filtroGrupos' => [],
    'filtroTipos' => [],
    'filtroStatus' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $buscarUrl = $buscarUrl ?: route('veiculo.buscar');
    $modalId = $prefix . '_modalBuscarVeiculo';
?>

<style>
    .veiculo-busca-modal__modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
    }

    .veiculo-busca-modal__header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        border-bottom: 0;
        position: relative;
    }

    .veiculo-busca-modal__header::after {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 3px;
        background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 55%, #22c55e 100%);
    }

    .veiculo-busca-modal__eyebrow {
        display: block;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255,255,255,.72);
        margin-bottom: .15rem;
    }

    .veiculo-busca-modal__title {
        margin: 0;
        font-weight: 800;
        line-height: 1.1;
        color: #fff;
    }

    .veiculo-busca-modal__subtitle {
        color: rgba(255,255,255,.78);
        font-size: .88rem;
    }

    .veiculo-busca-modal__body {
        background: #f8fafc;
        padding: 1rem;
    }

    .veiculo-busca-modal__panel {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .veiculo-busca-modal__panel-header {
        padding: .9rem 1rem;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border-bottom: 1px solid #e2e8f0;
    }

    .veiculo-busca-modal__panel-title {
        font-size: .84rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #475569;
        margin: 0;
    }

    .veiculo-busca-modal__table-wrap {
        max-height: min(62vh, 540px);
        overflow: auto;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
    }

    .veiculo-busca-modal__table-wrap table {
        margin-bottom: 0;
    }

    .veiculo-busca-modal__table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f8fafc !important;
        box-shadow: 0 1px 0 rgba(0,0,0,.04);
    }

    .veiculo-busca-modal__results {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    .veiculo-busca-modal__cards {
        display: grid;
        gap: .75rem;
    }

    .veiculo-busca-modal__card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        padding: .9rem;
    }

    .veiculo-busca-modal__card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
    }

    .veiculo-busca-modal__card-plate {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 1.5rem;
        padding: .15rem .5rem;
        border-radius: 999px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }

    .veiculo-busca-modal__card-title {
        margin: 0;
        font-size: .98rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .veiculo-busca-modal__card-sub {
        color: #64748b;
        font-size: .8rem;
        margin-top: .2rem;
    }

    .veiculo-busca-modal__card-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .6rem .75rem;
    }

    .veiculo-busca-modal__meta-item {
        display: flex;
        flex-direction: column;
        gap: .1rem;
        padding: .55rem .6rem;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        min-width: 0;
    }

    .veiculo-busca-modal__meta-label {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .veiculo-busca-modal__meta-value {
        font-size: .82rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        word-break: break-word;
    }

    .veiculo-busca-modal__card .btn {
        white-space: nowrap;
        flex-shrink: 0;
    }

    .veiculo-busca-modal__toolbar .btn {
        border-radius: 10px;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .veiculo-busca-modal__body {
            padding: .75rem;
        }

        .veiculo-busca-modal__panel-header,
        .veiculo-busca-modal__panel .card-body {
            padding-left: .85rem;
            padding-right: .85rem;
        }

        .veiculo-busca-modal__card-grid {
            grid-template-columns: 1fr;
        }

        .veiculo-busca-modal__card-head {
            flex-direction: column;
        }

        .veiculo-busca-modal__card .btn {
            width: 100%;
        }
    }
</style>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo e($modalId); ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content veiculo-busca-modal__modal-content">
            <div class="modal-header veiculo-busca-modal__header py-3">
                <div class="d-flex align-items-start">
                    <div class="vehicle-modal__icon mr-3" style="background: rgba(255,255,255,.12); color:#fff;">
                        <i class="fas fa-search"></i>
                    </div>
                    <div>
                        <span class="veiculo-busca-modal__eyebrow">Consulta de frota</span>
                        <h5 class="modal-title veiculo-busca-modal__title" id="<?php echo e($modalId); ?>Label"><?php echo e($title); ?></h5>
                        <small class="veiculo-busca-modal__subtitle">Use os filtros para localizar o veículo e selecione com um clique.</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body veiculo-busca-modal__body">
                <div class="veiculo-busca-modal__panel mb-3">
                    <div class="veiculo-busca-modal__panel-header d-flex align-items-center justify-content-between">
                        <h6 class="veiculo-busca-modal__panel-title mb-0"><i class="fas fa-filter mr-1"></i> Pesquisa</h6>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body py-3">
                        <div class="row align-items-end">
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Placa</label>
                                <input type="text" id="<?php echo e($prefix); ?>_fv_placa" class="form-control form-control-sm" placeholder="ABC-1234">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Nome</label>
                                <input type="text" id="<?php echo e($prefix); ?>_fv_nome" class="form-control form-control-sm" placeholder="Ex: Corolla">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Marca</label>
                                <select id="<?php echo e($prefix); ?>_fv_marca_id" class="form-control form-control-sm">
                                    <option value="">Todas as marcas</option>
                                    <?php $__currentLoopData = $filtroMarcas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>"><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Grupo de Frota</label>
                                <select id="<?php echo e($prefix); ?>_fv_grupo_frota_id" class="form-control form-control-sm">
                                    <option value="">Todos os grupos</option>
                                    <?php $__currentLoopData = $filtroGrupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>"><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Status</label>
                                <select id="<?php echo e($prefix); ?>_fv_status_veiculo_id" class="form-control form-control-sm">
                                    <option value="">Todos os status</option>
                                    <?php $__currentLoopData = $filtroStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>"><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end form-group mb-0 veiculo-busca-modal__toolbar" style="gap:.5rem;">
                                <button type="button" class="btn btn-primary btn-sm" id="<?php echo e($prefix); ?>_btnAplicarFiltroVeiculo">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="<?php echo e($prefix); ?>_btnLimparFiltroVeiculo">
                                    <i class="fas fa-eraser mr-1"></i> Limpar
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="toggleAvancado<?php echo e($prefix); ?>()">
                                    <i class="fas fa-sliders-h mr-1"></i> Filtros avançados
                                </button>
                            </div>
                        </div>

                        <div id="<?php echo e($prefix); ?>_filtrosAvancados" class="row" style="display:none;margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0;">
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Chassi</label>
                                <input type="text" id="<?php echo e($prefix); ?>_fv_chassi" class="form-control form-control-sm" placeholder="Chassi">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Cor</label>
                                <input type="text" id="<?php echo e($prefix); ?>_fv_cor" class="form-control form-control-sm" placeholder="Preto">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Modelo (texto)</label>
                                <input type="text" id="<?php echo e($prefix); ?>_fv_modelo" class="form-control form-control-sm" placeholder="Modelo">
                            </div>
                            <div class="col-md-3 form-group mb-0">
                                <label class="small mb-1">Tipo de Frota</label>
                                <select id="<?php echo e($prefix); ?>_fv_tipo_frota_id" class="form-control form-control-sm">
                                    <option value="">Todos os tipos</option>
                                    <?php $__currentLoopData = $filtroTipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>"><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Ano modelo de</label>
                                <input type="number" id="<?php echo e($prefix); ?>_fv_ano_de" class="form-control form-control-sm" placeholder="2015" min="1990" max="<?php echo e(date('Y')+1); ?>">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Ano modelo ate</label>
                                <input type="number" id="<?php echo e($prefix); ?>_fv_ano_ate" class="form-control form-control-sm" placeholder="<?php echo e(date('Y')); ?>" min="1990" max="<?php echo e(date('Y')+1); ?>">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">KMs ate</label>
                                <input type="number" id="<?php echo e($prefix); ?>_fv_kms_ate" class="form-control form-control-sm" placeholder="100000" min="0">
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                <label class="small mb-1">Ativo</label>
                                <select id="<?php echo e($prefix); ?>_fv_ativo" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Sim</option>
                                    <option value="0">Nao</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <?php $__env->startPush('js'); ?>
                <script>
                    window['toggleAvancado<?php echo e($prefix); ?>'] = function() {
                        var el = document.getElementById('<?php echo e($prefix); ?>_filtrosAvancados');
                        if (el) el.style.display = el.style.display === 'none' ? '' : 'none';
                    };
                </script>
                <?php $__env->stopPush(); ?>

                <div id="<?php echo e($prefix); ?>_modalVeiculoLoading" class="text-center d-none py-3">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
                </div>
                <div id="<?php echo e($prefix); ?>_modalVeiculoResultados" class="veiculo-busca-modal__table-wrap"></div>
            </div>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('a1d1dff0-d766-49e3-8751-cdd178209f98')): $__env->markAsRenderedOnce('a1d1dff0-d766-49e3-8751-cdd178209f98'); ?>
    <script src="<?php echo e(asset('js/veiculo/busca-modal.js')); ?>"></script>
<?php endif; ?>

<?php if($triggerId): ?>
    <?php $__env->startPush('js'); ?>
        <script>
            (function () {
                if (!window.VeiculoBuscaModal) return;
                window.VeiculoBuscaModal.init({
                    prefix: <?php echo json_encode($prefix, 15, 512) ?>,
                    buscarUrl: <?php echo json_encode($buscarUrl, 15, 512) ?>,
                    modalId: <?php echo json_encode($modalId, 15, 512) ?>,
                    triggerId: <?php echo json_encode($triggerId, 15, 512) ?>,
                    targetSelectId: <?php echo json_encode($targetSelectId, 15, 512) ?>,
                    hiddenInputId: <?php echo json_encode($hiddenInputId, 15, 512) ?>,
                    displayInputId: <?php echo json_encode($displayInputId, 15, 512) ?>,
                    clearBtnId: <?php echo json_encode($clearBtnId, 15, 512) ?>,
                    autoFillPlacaSelector: <?php echo json_encode($autoFillPlacaSelector, 15, 512) ?>,
                    autoFillKmsSelector: <?php echo json_encode($autoFillKmsSelector ?? null, 15, 512) ?>,
                    autoFillNivelSelector: <?php echo json_encode($autoFillNivelSelector ?? null, 15, 512) ?>,
                });
            }());
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/veiculo/busca-avancada-modal.blade.php ENDPATH**/ ?>