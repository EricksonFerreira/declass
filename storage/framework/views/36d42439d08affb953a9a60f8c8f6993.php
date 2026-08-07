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
    <?php if (isset($component)) { $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4 = $attributes; } ?>
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $attributes = $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $component = $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('financeiro.fluxo_caixa._styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $financeiro = str_starts_with((string) Route::currentRouteName(), 'financeiro.');
        $routePrefix = $financeiro ? 'financeiro.' : '';
        $totalPagina = $nfseList->count();
        $emitidasPagina = $nfseList->getCollection()->filter(fn($n) => !empty($n->nfse_id))->count();
        $errosPagina = $nfseList->getCollection()->filter(fn($n) => strtolower((string) $n->status) === 'erro')->count();
    ?>

    <div class="fc-page">
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="<?php echo e(route($routePrefix.'nfse.index')); ?>" class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Reserva</label>
                        <input type="number" name="reserva_id" class="form-control" value="<?php echo e($filters['reserva_id'] ?? ''); ?>">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = $statusDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status); ?>" <?php echo e(($filters['status'] ?? '') === $status ? 'selected' : ''); ?>><?php echo e($status); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Numero</label>
                        <input type="text" name="numero" class="form-control" value="<?php echo e($filters['numero'] ?? ''); ?>">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Ambiente</label>
                        <select name="ambiente" class="form-control">
                            <option value="">Todos</option>
                            <?php $__currentLoopData = $ambientesDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ambiente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($ambiente); ?>" <?php echo e(($filters['ambiente'] ?? '') === $ambiente ? 'selected' : ''); ?>><?php echo e($ambiente); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Data inicio</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?php echo e($filters['data_inicio'] ?? ''); ?>">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?php echo e($filters['data_fim'] ?? ''); ?>">
                    </div>
                    <?php if(auth()->user()?->isAdministrador() && $empresasDisponiveis->isNotEmpty()): ?>
                        <div class="col-lg-4 col-md-6 mb-2">
                            <label class="fc-label">Empresa</label>
                            <select name="empresa_id" class="form-control">
                                <option value="">Todas</option>
                                <?php $__currentLoopData = $empresasDisponiveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($empresa->id); ?>" <?php echo e((string) ($filters['empresa_id'] ?? '') === (string) $empresa->id ? 'selected' : ''); ?>><?php echo e($empresa->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="col-lg-8 col-md-6 mb-2 fc-actions justify-content-end">
                        <button class="btn btn-primary fc-btn" type="submit">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?php echo e(route($routePrefix.'nfse.index')); ?>" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i>
                        </a>
                        <?php if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador()): ?>
                            <button class="btn btn-outline-primary fc-btn" type="submit" form="nfse-importar-pendentes-form" <?php echo e($podeImportar ? '' : 'disabled'); ?>>
                                <i class="fas fa-sync-alt"></i> Importar pendentes
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
                <?php if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador()): ?>
                    <form id="nfse-importar-pendentes-form" action="<?php echo e(route($routePrefix.'nfse.importarPendentes')); ?>" method="POST" class="d-none">
                        <?php echo csrf_field(); ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if(auth()->user()?->hasPermissao('nfse.importarPendentes') || auth()->user()?->isAdministrador()): ?>
            <div class="card fc-card mb-4">
                <div class="card-body py-3 text-muted">
                    <?php if(!$podeImportar && !$ultimaImportacao && !$proximaImportacao): ?>
                        Selecione uma empresa para importar notas fiscais.
                    <?php elseif($ultimaImportacao): ?>
                        Ultima importacao: <?php echo e($ultimaImportacao->format('d/m/Y H:i')); ?>

                        <?php if(!$podeImportar && $proximaImportacao): ?>
                            · Proxima liberada: <?php echo e($proximaImportacao->format('d/m/Y H:i')); ?>

                        <?php endif; ?>
                    <?php else: ?>
                        Nenhuma importacao registrada.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Total listado</span>
                        <strong class="text-primary"><?php echo e($nfseList->total()); ?></strong>
                    </div>
                    <small><?php echo e($totalPagina); ?> nesta pagina</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Emitidas nesta pagina</span>
                        <strong class="text-success"><?php echo e($emitidasPagina); ?></strong>
                    </div>
                    <small>Com XML/DPS disponivel</small>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Erros nesta pagina</span>
                        <strong class="text-danger"><?php echo e($errosPagina); ?></strong>
                    </div>
                    <small>Requerem revisao operacional</small>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-file-invoice"></i>
                        <span>Notas fiscais emitidas<span class="fc-subtitle">Documentos fiscais vinculados às reservas</span></span>
                    </h5>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table fc-table">
                    <thead>
                        <tr>
                            <th class="px-3">#</th>
                            <th>Reserva</th>
                            <th>Empresa</th>
                            <th>Status</th>
                            <th>Numero</th>
                            <th>Ambiente</th>
                            <th>Emissao</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $nfseList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nfse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $status = strtolower((string) ($nfse->status ?? ''));
                                $badgeClass = $status === 'erro'
                                    ? 'fc-badge--despesa'
                                    : (!empty($nfse->nfse_id) ? 'fc-badge--pago' : 'fc-badge--pendente');
                            ?>
                            <tr>
                                <td class="px-3 font-weight-bold">#<?php echo e($nfse->id); ?></td>
                                <td>
                                    <span class="fc-badge bg-success-light">
                                        <i class="fas fa-hashtag"></i><?php echo e($nfse->reserva_id ?? '-'); ?>

                                    </span>
                                </td>
                                <td class="font-weight-bold"><?php echo e($nfse->empresa?->nome ?? '-'); ?></td>
                                <td><span class="fc-badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($nfse->status ?? '-')); ?></span></td>
                                <td><?php echo e($nfse->numero ?? '-'); ?></td>
                                <td><?php echo e($nfse->ambiente ?? '-'); ?></td>
                                <td><?php echo e($nfse->created_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                <td class="text-center">
                                    <?php if(!empty($nfse->nfse_id)): ?>
                                        <a class="btn btn-sm btn-outline-primary fc-btn" href="<?php echo e(route($routePrefix.'nfse.download', ['id' => $nfse->id, 'tipo' => 'xml'])); ?>">
                                            XML
                                        </a>
                                        <a class="btn btn-sm btn-outline-secondary fc-btn" href="<?php echo e(route($routePrefix.'nfse.download', ['id' => $nfse->id, 'tipo' => 'dps'])); ?>">
                                            DPS
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fas fa-file-invoice d-block mb-3" style="font-size: 2.4rem; opacity: .18;"></i>
                                    Nenhuma nota fiscal encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                <?php echo e($nfseList->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/nfse/index.blade.php ENDPATH**/ ?>