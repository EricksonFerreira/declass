<?php $__env->startSection('title', $empresa->nome . ' · Armazenamento'); ?>

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

<?php
    $formatBytes = function (int $bytes): string {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2, ',', '.') . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        return $bytes . ' B';
    };

    $limitBytes = (int) ($empresa->storage_limit_bytes ?? 0);
    $usedBytes = (int) ($empresa->storage_used_bytes ?? 0);
    $percent = (int) ($empresa->storage_percent ?? 0);
    $restanteBytes = max(0, $limitBytes - $usedBytes);
    $progressClass = $percent >= 90 ? 'bg-danger' : ($percent >= 60 ? 'bg-warning' : 'bg-info');

    $moduloSelecionado = request('modulo');
    $entidadeSelecionada = request('entidade');
    $ordemTamanhoSelecionada = request('ordenar_tamanho');
?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
                <div>
                    <div class="text-muted small text-uppercase font-weight-bold">Armazenamento da empresa</div>
                    <h3 class="card-title mb-0"><?php echo e($empresa->nome); ?></h3>
                </div>
                <span class="text-sm badge <?php echo e($percent >= 90 ? 'badge-danger' : 'badge-success'); ?> p-2"><?php echo e($percent); ?>% usado</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Arquivos</div>
                    <div class="h4 mb-0"><?php echo e($stats['total_arquivos']); ?></div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Uso atual</div>
                    <div class="h4 mb-0"><?php echo e($formatBytes($usedBytes)); ?></div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Limite</div>
                    <div class="h4 mb-0"><?php echo e($formatBytes($limitBytes)); ?></div>
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <div class="small text-muted">Restante</div>
                    <div class="h4 mb-0"><?php echo e($formatBytes($restanteBytes)); ?></div>
                </div>
            </div>
            <div class="progress mb-2" style="height:10px;">
                <div class="progress-bar <?php echo e($progressClass); ?>" role="progressbar" style="width: <?php echo e(min($percent, 100)); ?>%;" aria-valuenow="<?php echo e($percent); ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="text-muted small"><?php echo e($percent); ?>% usado do limite contratado</div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
            <h3 class="card-title mb-0"><i class="fas fa-folder-open mr-1"></i> Arquivos rastreados</h3>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="<?php echo e(route('empresa.armazenamento', $empresa->id)); ?>">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="modulo" class="font-weight-normal">Filtrar por módulo</label>
                        <select name="modulo" id="modulo" class="form-control">
                            <option value="">Todos os módulos</option>
                            <?php $__currentLoopData = $modulos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($modulo); ?>" <?php if($moduloSelecionado === $modulo): echo 'selected'; endif; ?>><?php echo e($modulo); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="entidade" class="font-weight-normal">Filtrar por entidade</label>
                        <select name="entidade" id="entidade" class="form-control">
                            <option value="">Todas as entidades</option>
                            <?php $__currentLoopData = $entidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entidade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($entidade); ?>" <?php if($entidadeSelecionada === $entidade): echo 'selected'; endif; ?>><?php echo e($entidade); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 mb-md-0">
                        <label for="ordenar_tamanho" class="font-weight-normal">Tamanho</label>
                        <select name="ordenar_tamanho" id="ordenar_tamanho" class="form-control">
                            <option value="">Recentes</option>
                            <option value="maior" <?php if($ordemTamanhoSelecionada === 'maior'): echo 'selected'; endif; ?>>Maior</option>
                            <option value="menor" <?php if($ordemTamanhoSelecionada === 'menor'): echo 'selected'; endif; ?>>Menor</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="<?php echo e(route('empresa.armazenamento', $empresa->id)); ?>" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Arquivo</th>
                            <th>Módulo</th>
                            <th>Entidade</th>
                            <th>Campo</th>
                            <th>Tamanho</th>
                            <th>Tipo</th>
                            <th>Rastreio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $arquivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $path = (string) ($arquivo->path ?? '');
                                $disk = $arquivo->disk ?: 'public';
                                $url = $path !== '' ? asset('storage/' . $path) : null;
                                $collapseId = 'rastreio-' . $arquivo->id;
                            ?>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($arquivo->original_name ?? basename($path) ?: 'Arquivo'); ?></div>
                                    <div class="text-muted small text-break"><?php echo e($path ?: '—'); ?></div>
                                </td>
                                <td><span class="badge badge-info"><?php echo e($arquivo->modulo ?? 'geral'); ?></span></td>
                                <td>
                                    <div class="small">
                                        <div><strong><?php echo e($arquivo->entidade ?? '—'); ?></strong></div>
                                        <div class="text-muted">ID <?php echo e($arquivo->entidade_id ?? '—'); ?></div>
                                    </div>
                                </td>
                                <td><?php echo e($arquivo->campo ?? '—'); ?></td>
                                <td><?php echo e($formatBytes((int) ($arquivo->size_bytes ?? 0))); ?></td>
                                <td><?php echo e($arquivo->mime_type ?? '—'); ?></td>
                                <td>
                                    <?php if($url): ?>
                                        <button class="btn btn-xs btn-outline-info" type="button" data-toggle="collapse" data-target="#<?php echo e($collapseId); ?>" aria-expanded="false" aria-controls="<?php echo e($collapseId); ?>">
                                            <i class="fas fa-search-plus mr-1"></i> Ver rastreio
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($url): ?>
                                        <a href="<?php echo e($url); ?>" target="_blank" class="btn btn-xs btn-primary">
                                            <i class="fas fa-file-alt mr-1"></i> Arquivo
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if($url): ?>
                                <tr class="collapse bg-light" id="<?php echo e($collapseId); ?>">
                                    <td colspan="8">
                                        <div class="small text-muted">
                                            <div><strong>Contexto:</strong> <?php echo e($arquivo->contexto ?? '—'); ?></div>
                                            <div><strong>Disco:</strong> <?php echo e($disk); ?></div>
                                            <div><strong>Hash:</strong> <?php echo e($arquivo->hash_sha1 ?? '—'); ?></div>
                                            <div><strong>Criado em:</strong> <?php echo e($arquivo->created_at?->format('d/m/Y H:i') ?? '—'); ?></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Nenhum arquivo rastreado encontrado para esta empresa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($arquivos->hasPages()): ?>
            <div class="card-footer clearfix">
                <div class="float-right"><?php echo e($arquivos->links()); ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\empresa\armazenamento.blade.php ENDPATH**/ ?>