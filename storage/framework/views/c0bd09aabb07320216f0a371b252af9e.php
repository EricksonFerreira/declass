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
    <!-- Filters & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="btn-group shadow-sm" role="group" style="border-radius: 8px; overflow: hidden;">
            <a href="<?php echo e(route('alerta.index', ['resolvido' => 'N'])); ?>" 
               class="btn btn-white font-weight-bold px-4 <?php echo e(($filtros['resolvido'] ?? 'N') === 'N' ? 'active btn-primary text-white' : 'text-secondary'); ?>">
                <i class="fas fa-exclamation-circle mr-1"></i> Ativos
            </a>
            <a href="<?php echo e(route('alerta.index', ['resolvido' => 'S'])); ?>" 
               class="btn btn-white font-weight-bold px-4 <?php echo e(($filtros['resolvido'] ?? '') === 'S' ? 'active btn-primary text-white' : 'text-secondary'); ?>">
                <i class="fas fa-check-circle mr-1"></i> Resolvidos
            </a>
            <a href="<?php echo e(route('alerta.index', ['resolvido' => 'todos'])); ?>" 
               class="btn btn-white font-weight-bold px-4 <?php echo e(($filtros['resolvido'] ?? '') === 'todos' ? 'active btn-primary text-white' : 'text-secondary'); ?>">
                Todos
            </a>
        </div>
    </div>

    <!-- Alert Cards List -->
    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $nivelClass = $alerta->nivel_class; // danger, warning, info
                $nivelIcon = $alerta->nivel_icone;   // fas fa-times-circle, etc.
                $borderLeft = match ($alerta->nivel) {
                    'critico' => '5px solid #dc3545',
                    'aviso'   => '5px solid #ffc107',
                    default   => '5px solid #17a2b8',
                };
            ?>
            <div class="col-12 mb-3 alerta-item-card" id="alerta-card-<?php echo e($alerta->id); ?>">
                <div class="card shadow-sm border-0 h-100" style="border-left: <?php echo e($borderLeft); ?>; border-radius: 12px;">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-start">
                            <div class="mr-3 text-<?php echo e($nivelClass); ?>" style="font-size: 1.5rem; margin-top: 2px;">
                                <i class="<?php echo e($nivelIcon); ?>"></i>
                            </div>
                            <div>
                                <h5 class="font-weight-bold mb-1 text-dark" style="font-size: 1.05rem;">
                                    <?php echo e($alerta->titulo); ?>

                                </h5>
                                <p class="text-secondary mb-1" style="font-size: 0.9rem;">
                                    <?php echo e($alerta->mensagem); ?>

                                </p>
                                <div class="d-flex align-items-center gap-3 text-xs text-muted" style="font-size: 0.8rem;">
                                    <span><i class="far fa-clock mr-1"></i>Gerado em <?php echo e($alerta->created_at->format('d/m/Y H:i')); ?></span>
                                    <?php if($alerta->resolvido): ?>
                                        <span class="ml-3 text-success">
                                            <i class="fas fa-check-circle mr-1"></i>Resolvido em <?php echo e($alerta->resolvido_em->format('d/m/Y H:i')); ?> 
                                            <?php if($alerta->resolvidoPor): ?>
                                                por <?php echo e($alerta->resolvidoPor->nome); ?>

                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <?php if(!$alerta->resolvido): ?>
                            <div class="ml-3">
                                <button onclick="resolverAlerta(<?php echo e($alerta->id); ?>)" 
                                        class="btn btn-sm btn-outline-success font-weight-bold px-3 py-2 btn-resolver-alerta" 
                                        style="border-radius: 8px;">
                                    <i class="fas fa-check mr-1"></i> Marcar como Resolvido
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted mb-3" style="font-size: 3rem;">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h5 class="text-secondary font-weight-bold">Nenhuma pendência ou alerta encontrado!</h5>
                <p class="text-muted">Tudo sob controle no momento.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if($alertas->hasPages()): ?>
        <div class="d-flex justify-content-end mt-3">
            <?php echo e($alertas->appends($filtros)->links()); ?>

        </div>
    <?php endif; ?>

    <!-- Ajax Script -->
    <script>
        function resolverAlerta(id) {
            if (!confirm('Deseja marcar este alerta como resolvido?')) {
                return;
            }

            const card = document.getElementById(`alerta-card-${id}`);
            const btn = card.querySelector('.btn-resolver-alerta');
            
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processando...';
            }

            fetch(`<?php echo e(url('admin/alerta')); ?>/${id}/resolver`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Falha ao processar requisição');
                }
                return response.json();
            })
            .then(data => {
                if (data.ok) {
                    // Remove do DOM ou esconde com animação
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        // Se não sobrar nenhum alerta na tela, recarrega para mostrar aviso de lista vazia
                        if (document.querySelectorAll('.alerta-item-card').length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                } else {
                    alert('Não foi possível resolver o alerta.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Marcar como Resolvido';
                    }
                }
            })
            .catch(error => {
                console.error(error);
                alert('Erro de conexão ao resolver alerta.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i> Marcar como Resolvido';
                }
            });
        }
    </script>

    <style>
        .btn-white {
            background-color: #fff;
            border: 1px solid #dee2e6;
            color: #495057;
        }
        .btn-white:hover {
            background-color: #f8f9fa;
            color: #495057;
        }
        .btn-white.active {
            box-shadow: none !important;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\alerta\index.blade.php ENDPATH**/ ?>