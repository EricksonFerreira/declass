

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'createUrl' => route('empresa.create')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php
    $totalBytes = (int) \App\Models\ArquivoRastreado::query()->sum('size_bytes');
    if ($totalBytes >= 1073741824) {
        $storageFormatted = number_format($totalBytes / 1073741824, 2, ',', '.') . ' GB';
    } else {
        $storageFormatted = number_format($totalBytes / 1048576, 2, ',', '.') . ' MB';
    }
?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?php echo e($storageFormatted); ?></h3>
                    <p>Armazenamento total</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault();alert('Armazenamento total de todas as empresas somadas. O detalhamento por empresa aparece na tabela abaixo.');">
                    <i class="fas fa-chart-simple mr-1"></i> Detalhes por empresa na tabela
                </a>
            </div>
        </div>
    </div>

    <?php if(!empty($filters)): ?>
        <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['filters' => $filters,'additionalFilters' => $additionalFilters,'itensFiltered' => $itensFiltered] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nomeFiltro' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($nomeFiltro)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $attributes = $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $component = $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title mb-0 text-sm font-weight-bold">
                    Registros
                    <span class="badge badge-success ml-1"><?php echo e($empresas->total()); ?></span>
                </h3>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <div class="empresa-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                    <form action="<?php echo e(route('empresa.setPerPage')); ?>" method="POST" class="mb-0">
                        <?php echo csrf_field(); ?>
                        <?php if (isset($component)) { $__componentOriginal65c0c73c271c840668c600386ae808f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65c0c73c271c840668c600386ae808f3 = $attributes; } ?>
<?php $component = App\View\Components\PerPageSelector::resolve(['perPages' => $perPages,'selectedPerPage' => $perPage] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PerPageSelector::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $attributes = $__attributesOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__attributesOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $component = $__componentOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__componentOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
                    </form>
                    <?php echo e($empresas->links()); ?>

                </div>
            </div>
        </div>

        <div class="card-body p-0 empresa-index-body" data-view="cards">
            <div class="empresa-cards">
                <?php $__empty_1 = true; $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $planoAtivo = $empresa->planoAtivo;
                        $gateway = $empresa->gateway_pagamento ?? null;
                        $moeda = $empresa->moeda_pagamento ?? null;
                        $statusAtivo = ($empresa->ativo ?? 'S') === 'S';
                    ?>
                    <article class="empresa-card" onclick="window.location='<?php echo e(route('empresa.show', $empresa->id)); ?>';" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='<?php echo e(route('empresa.show', $empresa->id)); ?>';}">
                        <div class="empresa-card__head">
                            <?php if(!empty($empresa->logo_url)): ?>
                                <img src="<?php echo e($empresa->logo_url); ?>" alt="Logo <?php echo e($empresa->nome); ?>" class="empresa-card__logo">
                            <?php else: ?>
                                <div class="empresa-card__logo empresa-card__logo--fallback"><?php echo e(strtoupper(mb_substr($empresa->nome ?? 'E', 0, 1))); ?></div>
                            <?php endif; ?>
                            <div class="empresa-card__meta">
                                <div class="empresa-card__nome"><?php echo e($empresa->nome); ?></div>
                                <div class="empresa-card__slug">Slug: <?php echo e($empresa->slug); ?></div>
                                <div class="empresa-card__slug"><?php echo e($empresa->email ?? '-'); ?></div>
                            </div>
                        </div>
                        <div class="empresa-card__chips">
                            <span class="badge badge-<?php echo e($statusAtivo ? 'success' : 'danger'); ?>"><?php echo e($statusAtivo ? 'Ativa' : 'Inativa'); ?></span>
                            <span class="badge badge-light"><?php echo e($planoAtivo?->plano?->nome ?? 'Sem plano'); ?></span>
                            <span class="badge badge-info"><?php echo e($gateway ? strtoupper(str_replace('_', ' ', $gateway)) : 'Sem gateway'); ?></span>
                            <span class="badge badge-secondary"><?php echo e($moeda ?? 'Sem moeda'); ?></span>
                        </div>
                        <div class="empresa-card__footer">
                            <a href="<?php echo e(route('empresa.show', $empresa->id)); ?>" class="btn btn-xs btn-success" onclick="event.stopPropagation();">
                                <i class="fa fa-eye"></i> Ver
                            </a>
                            <a href="<?php echo e(route('empresa.edit', $empresa->id)); ?>" class="btn btn-xs btn-warning" onclick="event.stopPropagation();">
                                <i class="fa fa-edit"></i> Editar
                            </a>
                            <button
                                type="button"
                                class="btn btn-xs btn-primary btn-atribuir-assinatura"
                                data-toggle="modal"
                                data-target="#modalAtribuirAssinatura"
                                data-empresa-id="<?php echo e($empresa->id); ?>"
                                data-empresa-nome="<?php echo e($empresa->nome); ?>"
                                onclick="event.stopPropagation();"
                            >
                                <i class="fas fa-link mr-1"></i> Assinatura
                            </button>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-3 text-center text-muted">Nenhuma empresa encontrada.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body p-0 table-responsive empresa-index-body d-none" data-view="table">
            <table class="table table-hover table-sm mb-0 empresa-table" style="font-size:13px;">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Empresa</th>
                        <th>Contato</th>
                        <th>Plano</th>
                        <th>Gateway / Moeda</th>
                        <th>Armazenamento</th>
                        <th>Cores</th>
                        <th>Status</th>
                        <th class="text-center" style="width:290px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $gateway = $empresa->gateway_pagamento ?? null;
                            $moeda = $empresa->moeda_pagamento ?? null;
                            $corPrimaria = $empresa->cor_primaria ?? null;
                            $corSecundaria = $empresa->cor_secundaria ?? null;
                            $corTerciaria = $empresa->cor_terciaria ?? null;
                        ?>
                        <tr>
                            <td class="pl-3 align-middle">
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    <?php if(!empty($empresa->logo_url)): ?>
                                        <img
                                            src="<?php echo e($empresa->logo_url); ?>"
                                            alt="Logo <?php echo e($empresa->nome); ?>"
                                            class="empresa-logo-lista"
                                            style="background: <?php echo e($empresa->cor_primaria ?? '#1F2937'); ?>;"
                                        >
                                    <?php else: ?>
                                        <div class="empresa-logo-fallback">
                                            <?php echo e(strtoupper(mb_substr($empresa->nome ?? 'E', 0, 1))); ?>

                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <div class="font-weight-bold"><?php echo e($empresa->nome); ?></div>
                                        <div class="text-muted text-xs">Slug: <?php echo e($empresa->slug); ?></div>
                                        <?php if(!empty($empresa->dominio)): ?>
                                            <div class="text-muted text-xs"><?php echo e($empresa->dominio); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle">
                                <div class="text-xs">
                                    <div><strong>CNPJ:</strong> <?php echo e($empresa->cnpj ?? '-'); ?></div>
                                    <div><strong>E-mail:</strong> <?php echo e($empresa->email ?? '-'); ?></div>
                                    <div><strong>Telefone:</strong> <?php echo e($empresa->telefone ?? '-'); ?></div>
                                </div>
                            </td>

                            <?php
                                $planoAtivo = $empresa->planoAtivo;
                            ?>
                            <td class="align-middle text-xs">
                                <?php if($planoAtivo): ?>
                                    <div><strong><?php echo e($planoAtivo->plano?->nome ?? '-'); ?></strong></div>
                                    <div>
                                        <span class="badge badge-xs bg-<?php echo e($planoAtivo->status === 'ativo' ? 'success' : ($planoAtivo->status === 'trial' ? 'info' : ($planoAtivo->status === 'inadimplente' ? 'danger' : 'warning'))); ?>">
                                            <?php echo e($planoAtivo->status_badge['label'] ?? $planoAtivo->status); ?>

                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size:10px;">
                                        <?php echo e($planoAtivo->periodicidade_texto); ?>

                                        <?php echo e($planoAtivo->data_vencimento ? ' até ' . $planoAtivo->data_vencimento->format('d/m/Y') : ''); ?>

                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column" style="gap:4px;">
                                        <span class="text-muted font-italic">Sem plano</span>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-outline-primary btn-atribuir-assinatura"
                                            data-toggle="modal"
                                            data-target="#modalAtribuirAssinatura"
                                            data-empresa-id="<?php echo e($empresa->id); ?>"
                                            data-empresa-nome="<?php echo e($empresa->nome); ?>"
                                        >
                                            <i class="fas fa-link mr-1"></i> Atribuir assinatura
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="align-middle">
                                <div class="text-xs mb-1">
                                    <strong>Gateway:</strong>
                                    <?php if($gateway): ?>
                                        <?php echo e(strtoupper(str_replace('_', ' ', $gateway))); ?>

                                    <?php else: ?>
                                        <span class="text-muted font-italic">nao definido</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-xs mb-2">
                                    <strong>Moeda:</strong>
                                    <?php if($moeda): ?>
                                        <?php echo e($moeda); ?>

                                    <?php else: ?>
                                        <span class="text-muted font-italic">nao definida</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="align-middle text-xs">
                                <?php
                                    $pct = $empresa->storage_percent;
                                    $barColor = $pct >= 90 ? '#dc2626' : ($pct >= 60 ? '#d97706' : ($pct >= 30 ? '#2563eb' : '#16a34a'));
                                    $labelColor = $pct >= 90 ? 'text-danger' : ($pct >= 60 ? 'text-warning' : 'text-success');
                                ?>
                                <div class="<?php echo e($labelColor); ?>" style="font-weight:600;font-size:11px;">
                                    <a href="<?php echo e(route('empresa.armazenamento', $empresa->id)); ?>" class="text-reset" style="text-decoration:none;">
                                        <i class="fas fa-database mr-1"></i> <?php echo e($empresa->storage_used); ?>

                                    </a>
                                </div>
                                <div style="margin-top:3px;background:#e5e7eb;border-radius:4px;height:8px;width:100px;overflow:hidden;">
                                    <div style="width:<?php echo e(min($pct, 100)); ?>%;height:100%;background:<?php echo e($barColor); ?>;border-radius:4px;"></div>
                                </div>
                                <div class="text-muted" style="font-size:9px;margin-top:1px;">
                                    <a href="<?php echo e(route('empresa.armazenamento', $empresa->id)); ?>" class="text-muted" style="text-decoration:none;">
                                        <?php echo e($pct); ?>% do limite (<?php echo e($empresa->planoAtivo?->plano?->nome ?? 'Sem plano'); ?>)
                                    </a>
                                </div>
                            </td>

                            <td class="align-middle">
                                <?php if($corPrimaria || $corSecundaria || $corTerciaria): ?>
                                    <div class="d-flex flex-wrap align-items-center empresa-cor-group">
                                        <?php if($corPrimaria): ?>
                                            <button type="button" class="empresa-cor-chip js-copy-color" data-color="<?php echo e($corPrimaria); ?>" title="Clique para copiar <?php echo e($corPrimaria); ?>">
                                                <span class="empresa-cor-dot" style="background: <?php echo e($corPrimaria); ?>"></span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if($corSecundaria): ?>
                                            <button type="button" class="empresa-cor-chip js-copy-color" data-color="<?php echo e($corSecundaria); ?>" title="Clique para copiar <?php echo e($corSecundaria); ?>">
                                                <span class="empresa-cor-dot" style="background: <?php echo e($corSecundaria); ?>"></span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if($corTerciaria): ?>
                                            <button type="button" class="empresa-cor-chip js-copy-color" data-color="<?php echo e($corTerciaria); ?>" title="Clique para copiar <?php echo e($corTerciaria); ?>">
                                                <span class="empresa-cor-dot" style="background: <?php echo e($corTerciaria); ?>"></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-secondary">SEM CORES</span>
                                <?php endif; ?>
                            </td>

                            <td class="align-middle">
                                <span class="badge badge-<?php echo e(($empresa->ativo ?? 'S') === 'S' ? 'success' : 'danger'); ?>">
                                    <?php echo e(($empresa->ativo ?? 'S') === 'S' ? 'ATIVO' : 'INATIVO'); ?>

                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex flex-wrap justify-content-center empresa-actions-group">
                                    <a href="<?php echo e(route('empresa.show', $empresa->id)); ?>" class="btn btn-xs btn-success">
                                        <i class="fa fa-eye"></i> Show
                                    </a>
                                    <a href="<?php echo e(route('empresa.site-editor.edit', $empresa->id)); ?>" class="btn btn-xs btn-info" style="background:#0ea5e9;border-color:#0ea5e9;">
                                        <i class="fa fa-paint-brush"></i> Editor Site
                                    </a>
                                    <a href="<?php echo e(route('empresa.edit', $empresa->id)); ?>" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                    <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('empresa.destroy', $empresa->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\DeleteButton::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $attributes = $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $component = $__componentOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma empresa encontrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($empresas->hasPages()): ?>
            <div class="card-footer py-2 d-flex justify-content-end">
                <?php echo e($empresas->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .empresa-logo-lista {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        object-fit: contain;
        padding: 2px;
    }

    .empresa-logo-fallback {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: #1f2937;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .empresa-table .text-xs {
        font-size: 11px;
        line-height: 1.35;
    }

    .empresa-cor-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        padding: 0;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #d7e0ea;
        color: #334155;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .empresa-cor-chip:hover {
        transform: translateY(-1px);
        border-color: #94a3b8;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .10);
    }

    .empresa-cor-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 1px solid rgba(15, 23, 42, .15);
        display: inline-block;
    }

    .empresa-cor-group {
        gap: 4px;
    }

    .empresa-cor-copied {
        outline: 2px solid rgba(37, 99, 235, .24);
        border-color: #2563eb !important;
    }

    .empresa-actions-group {
        gap: 4px;
    }

    .empresa-actions-group > * {
        margin: 0 !important;
    }

    .empresa-view-toggle {
        display: inline-flex;
        gap: 6px;
        padding: 3px;
        border: 1px solid #dbe4f0;
        border-radius: 999px;
        background: #f8fafc;
    }

    .empresa-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
        padding: 12px;
    }

    .empresa-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        padding: 14px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .empresa-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, .10);
    }

    .empresa-card__head {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 10px;
    }

    .empresa-card__logo,
    .empresa-card__logo--fallback {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        flex: 0 0 48px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }

    .empresa-card__logo--fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #fff;
        background: #1f2937;
    }

    .empresa-card__nome {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
        color: #0f172a;
    }

    .empresa-card__slug {
        font-size: .78rem;
        color: #64748b;
        margin-top: 2px;
    }

    .empresa-card__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .empresa-card__footer {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .btn-atribuir-assinatura {
        width: fit-content;
    }

    .empresa-index-body[data-view="cards"] .table-responsive {
        display: none;
    }

    .empresa-index-body[data-view="table"] .empresa-cards {
        display: none;
    }
    .veiculo-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 14px;
    }

    .veiculo-info-tip {
        border: 1px solid #dbe6f3;
        border-radius: 10px;
        background: #f8fbff;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 10px;
        margin-bottom: 10px;
    }

    .veiculo-lookup-help {
        display: block;
        margin-top: .35rem;
        font-size: .75rem;
        color: #6b7280;
    }

    .form-control-sm {
        border-radius: 10px;
    }

    .modal-xl {
        max-width: 1180px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="<?php echo e(asset('js/select2.min.js')); ?>"></script>
<script>
    (function () {
            var viewButtons = document.querySelectorAll('.empresa-view-toggle [data-view]');
            var viewBodies = document.querySelectorAll('.empresa-index-body');

            function setEmpresaView(view) {
                viewBodies.forEach(function (body) {
                    body.classList.toggle('d-none', body.dataset.view !== view);
                });
                viewButtons.forEach(function (button) {
                    button.classList.toggle('btn-secondary', button.dataset.view === view);
                    button.classList.toggle('btn-outline-secondary', button.dataset.view !== view);
                });
            }

            viewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setEmpresaView(button.dataset.view || 'cards');
                });
            });

            setEmpresaView('cards');

            var $modal = $('#modalAtribuirAssinatura');
            var $form = $('#formAtribuirAssinatura');
            var $empresaId = $form.find('[name="empresa_id"]');
            var $empresaNome = $form.find('#empresa_nome_modal');
            var $planSelect = $form.find('[name="plano_id"]');
            var $periodicidade = $form.find('[name="periodicidade"]');
            var $dataInicio = $form.find('[name="data_inicio"]');
            var $dataVencimento = $form.find('[name="data_vencimento"]');
            var $valorExibido = $form.find('#valorExibido');
            var $planoStorage = $form.find('#planoStorage');

            $modal.on('show.bs.modal', function (event) {
                var trigger = $(event.relatedTarget);
                var empresaId = trigger.data('empresa-id');
                var empresaNome = trigger.data('empresa-nome');

                if (empresaId) {
                    $empresaId.val(String(empresaId));
                }

                if (empresaNome) {
                    $empresaNome.val(empresaNome);
                    $modal.find('.modal-title').text('Atribuir assinatura para ' + empresaNome);
                }

                atualizarPreview();
            });

        function formatarMoeda(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calcularVencimento(dataInicio, periodicidade) {
            if (!dataInicio) {
                return '';
            }

            var data = new Date(dataInicio + 'T00:00:00');
            if (Number.isNaN(data.getTime())) {
                return '';
            }

            if (periodicidade === 'anual') {
                data.setFullYear(data.getFullYear() + 1);
            } else {
                data.setMonth(data.getMonth() + 1);
            }

            return data.toISOString().slice(0, 10);
        }

        function atualizarPreview() {
            var $selected = $planSelect.find('option:selected');
            var mensal = parseFloat($selected.data('mensal') || 0);
            var anual = parseFloat($selected.data('anual') || 0);
            var storage = $selected.data('storage');
            var periodicidade = $periodicidade.val() || 'mensal';
            var valor = periodicidade === 'anual' ? anual : mensal;

            $valorExibido.val(formatarMoeda(valor));
            $planoStorage.val(storage ? storage + ' MB' : '');

            var vencimento = calcularVencimento($dataInicio.val(), periodicidade);
            if (vencimento) {
                $dataVencimento.val(vencimento);
            }
        }

        $planSelect.on('change', atualizarPreview);
        $periodicidade.on('change', atualizarPreview);
        $dataInicio.on('change', atualizarPreview);
        atualizarPreview();

        function copyColor(hex, trigger) {
            if (!hex) {
                return;
            }

            var text = String(hex).trim();
            var el = trigger instanceof HTMLElement ? trigger : null;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                var input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            }

            if (el) {
                el.classList.add('empresa-cor-copied');
                el.setAttribute('title', 'Copiado: ' + text);
                setTimeout(function () {
                    el.classList.remove('empresa-cor-copied');
                }, 700);
            }
        }

        document.querySelectorAll('.js-copy-color').forEach(function (button) {
            button.addEventListener('click', function () {
                copyColor(button.dataset.color || '', button);
            });
        });
    })();
</script>
<?php $__env->stopSection(); ?>

<div class="modal fade" id="modalAtribuirAssinatura" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formAtribuirAssinatura" action="<?php echo e(route('assinatura.atribuir.processar')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="empresa_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-link mr-1"></i> Atribuir assinatura</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-outline card-light mb-0">
                                <div class="card-body p-4">
                                    <div class="veiculo-section-title">
                                        <i class="fas fa-link mr-1"></i>Dados da assinatura
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="mb-1">Empresa <span class="text-danger">*</span></label>
                                            <input type="hidden" name="empresa_id" value="">
                                            <input type="text" class="form-control form-control-sm" id="empresa_nome_modal" readonly>
                                            <small class="veiculo-lookup-help">A empresa vem preenchida automaticamente a partir da linha selecionada.</small>
                                        </div>

                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label class="mb-1">Plano <span class="text-danger">*</span></label>
                                            <select name="plano_id" class="form-control form-control-sm" required>
                                                <option value="">Selecione o plano...</option>
                                                <?php $__currentLoopData = \App\Models\Plano::orderBy('nome')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($plano->id); ?>" data-mensal="<?php echo e($plano->valor_mensal); ?>" data-anual="<?php echo e($plano->valor_anual); ?>" data-storage="<?php echo e($plano->limite_armazenamento_mb ?? 0); ?>">
                                                        <?php echo e($plano->nome); ?> - R$ <?php echo e(number_format($plano->valor_mensal, 2, ',', '.')); ?>/mês
                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Periodicidade <span class="text-danger">*</span></label>
                                            <select name="periodicidade" class="form-control form-control-sm" required>
                                                <option value="mensal">Mensal</option>
                                                <option value="anual">Anual (com desconto)</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Dia de vencimento</label>
                                            <input type="number" name="dia_vencimento" class="form-control form-control-sm" min="1" max="28" placeholder="Ex.: 10">
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Data de início <span class="text-danger">*</span></label>
                                            <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?php echo e(now()->toDateString()); ?>" required>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Data de vencimento</label>
                                            <input type="date" name="data_vencimento" class="form-control form-control-sm" value="<?php echo e(now()->addMonth()->toDateString()); ?>">
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Valor a cobrar (R$) <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">R$</span>
                                                </div>
                                                <input type="text" id="valorExibido" class="form-control" readonly required>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Armazenamento do plano</label>
                                            <input type="text" class="form-control form-control-sm" id="planoStorage" readonly placeholder="Selecione um plano...">
                                        </div>

                                        <div class="col-12 mb-2">
                                            <label class="mb-1">Observação</label>
                                            <textarea name="observacao" class="form-control form-control-sm" rows="2" placeholder="Observação interna sobre esta atribuição..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mt-3 mt-lg-0">
                            <div class="card card-outline card-light h-100 mb-0">
                                <div class="card-body p-4">
                                    <div class="veiculo-section-title">
                                        <i class="fas fa-info-circle mr-1"></i>Resumo
                                    </div>
                                    <div class="veiculo-info-tip">
                                        Verifique o plano, a periodicidade e a data de vencimento antes de confirmar a atribuição.
                                    </div>
                                    <div class="veiculo-info-tip">
                                        O valor exibido muda conforme o plano selecionado e o tipo de cobrança.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Atribuir assinatura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/empresa/index.blade.php ENDPATH**/ ?>