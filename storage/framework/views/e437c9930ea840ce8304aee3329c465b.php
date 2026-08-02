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
    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('financeiro.fluxo-caixa.index')); ?>" class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label for="descricao" class="fc-label">Descrição</label>
                        <input type="text" name="descricao" id="descricao" class="form-control" value="<?php echo e(request('descricao')); ?>" placeholder="Buscar descrição">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="tipo" class="fc-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="receita" <?php echo e(request('tipo') === 'receita' ? 'selected' : ''); ?>>Receita</option>
                            <option value="despesa" <?php echo e(request('tipo') === 'despesa' ? 'selected' : ''); ?>>Despesa</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="status" class="fc-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendente" <?php echo e(request('status') === 'pendente' ? 'selected' : ''); ?>>Pendente</option>
                            <option value="pago" <?php echo e(request('status') === 'pago' ? 'selected' : ''); ?>>Pago</option>
                            <option value="cancelado" <?php echo e(request('status') === 'cancelado' ? 'selected' : ''); ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="categoria_id" class="fc-label">Categoria</label>
                        <select name="categoria_id" id="categoria_id" class="form-control">
                            <option value="">Todas</option>
                            <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>" <?php echo e(request('categoria_id') == $cat->id ? 'selected' : ''); ?>>
                                    <?php echo e($cat->codigo); ?> — <?php echo e($cat->categoriaPai ? $cat->categoriaPai->nome . ' > ' : ''); ?><?php echo e($cat->nome); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="reserva_id" class="fc-label">Reserva</label>
                        <select name="reserva_id" id="reserva_id" class="form-control">
                            <option value="">Todas</option>
                            <?php $__currentLoopData = $reservas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reserva): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $codigoReserva = $reserva->codigoReserva?->codigo ?? $reserva->id;
                                ?>
                                <option value="<?php echo e($reserva->id); ?>" <?php echo e(request('reserva_id') == $reserva->id ? 'selected' : ''); ?>>
                                    #<?php echo e($codigoReserva); ?> - <?php echo e($reserva->user?->nome ?? 'Cliente'); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="order_by" class="fc-label">Ordenar por</label>
                        <select name="order_by" id="order_by" class="form-control">
                            <option value="data_lancamento" <?php echo e(request('order_by') === 'data_lancamento' || !request()->has('order_by') ? 'selected' : ''); ?>>Data de Lançamento</option>
                            <option value="data_vencimento" <?php echo e(request('order_by') === 'data_vencimento' ? 'selected' : ''); ?>>Data de Vencimento</option>
                            <option value="data_pagamento" <?php echo e(request('order_by') === 'data_pagamento' ? 'selected' : ''); ?>>Data de Pagamento</option>
                            <option value="valor" <?php echo e(request('order_by') === 'valor' ? 'selected' : ''); ?>>Valor</option>
                            <option value="descricao" <?php echo e(request('order_by') === 'descricao' ? 'selected' : ''); ?>>Descrição</option>
                            <option value="created_at" <?php echo e(request('order_by') === 'created_at' ? 'selected' : ''); ?>>Data de Criação</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label for="order_direction" class="fc-label">Direção</label>
                        <select name="order_direction" id="order_direction" class="form-control">
                            <option value="desc" <?php echo e(request('order_direction') === 'desc' || !request()->has('order_direction') ? 'selected' : ''); ?>>Decrescente</option>
                            <option value="asc" <?php echo e(request('order_direction') === 'asc' ? 'selected' : ''); ?>>Crescente</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1"><i class="fas fa-filter"></i> Filtrar</button>
                        <a href="<?php echo e(route('financeiro.fluxo-caixa.index')); ?>" class="btn btn-outline-secondary fc-btn">Limpar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h3 class="fc-title"><i class="fas fa-receipt"></i><span>Lançamentos<span class="fc-subtitle">Entradas, saídas, vencimentos e vínculos</span></span></h3>
                    <div class="fc-actions">
                        <a href="<?php echo e(route('financeiro.fluxo-caixa.categorias.index')); ?>" class="btn btn-outline-primary fc-btn">
                            <i class="fas fa-tags"></i> Categorias
                        </a>
                        <a href="<?php echo e(route('financeiro.fluxo-caixa.create')); ?>" class="btn btn-success fc-btn">
                            <i class="fas fa-plus"></i> Novo lançamento
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover fc-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Vencimento</th>
                                <th class="py-3">Data de Lançamento</th>
                                <th class="py-3">Descrição</th>
                                <th class="py-3">Categoria</th>
                                <th class="py-3">Tipo</th>
                                <th class="py-3">Valor</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Vínculo</th>
                                <th class="py-3 text-right px-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $lancamentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $codigoReserva = $item->reserva?->codigoReserva?->codigo ?? $item->reserva?->id;
                                    $dataLancamentoExibicao = $item->data_lancamento ?? $item->data_pagamento ?? $item->updated_at ?? $item->created_at;
                                ?>
                                <tr>
                                    <td class="px-4 py-3"><?php echo e($item->data_vencimento ? $item->data_vencimento->format('d/m/Y') : '-'); ?></td>
                                    <td class="py-3"><?php echo e($dataLancamentoExibicao ? $dataLancamentoExibicao->format('d/m/Y H:i') : '-'); ?></td>
                                    <td class="py-3">
                                        <span class="font-weight-bold text-dark"><?php echo e($item->descricao); ?></span>
                                        <?php if($item->data_pagamento): ?>
                                            <div class="text-xs text-muted">Pago em: <?php echo e($item->data_pagamento->format('d/m/Y H:i')); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-secondary">
                                        <?php if($item->categoria): ?>
                                            <span class="text-xs font-weight-bold text-muted"><?php echo e($item->categoria->codigo); ?></span><br>
                                            <span class="text-dark"><?php echo e($item->categoria->categoriaPai ? $item->categoria->categoriaPai->nome . ' > ' : ''); ?><?php echo e($item->categoria->nome); ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="fc-badge <?php echo e($item->tipo === 'receita' ? 'fc-badge--receita' : 'fc-badge--despesa'); ?>">
                                            <?php echo e($item->tipo === 'receita' ? 'Receita' : 'Despesa'); ?>

                                        </span>
                                    </td>
                                    <td class="py-3 font-weight-bold <?php echo e($item->tipo === 'receita' ? 'text-success' : 'text-danger'); ?>">
                                        <?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($item->valor, 2, ',', '.')); ?>

                                    </td>
                                    <td class="py-3">
                                        <span class="fc-badge fc-badge--<?php echo e($item->status); ?>">
                                            <?php echo e(ucfirst($item->status)); ?>

                                        </span>
                                    </td>
                                    <td class="py-3 text-xs">
                                        <?php if($item->veiculo): ?>
                                            <span class="font-weight-bold text-primary"><i class="fas fa-car mr-1"></i><?php echo e($item->veiculo->placa_licenca); ?></span>
                                        <?php elseif($item->reserva): ?>
                                            <span class="font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-1"></i>Reserva #<?php echo e($codigoReserva); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-right px-4">
                                        <a href="<?php echo e(route('financeiro.fluxo-caixa.edit', $item->id)); ?>" class="btn btn-sm btn-outline-primary mr-1" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('financeiro.fluxo-caixa.destroy', $item->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja excluir este lançamento?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">Nenhum lançamento encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($lancamentos->hasPages()): ?>
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-end">
                        <?php echo e($lancamentos->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php echo $__env->make('financeiro.fluxo_caixa._styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/financeiro/fluxo_caixa/lancamentos/index.blade.php ENDPATH**/ ?>