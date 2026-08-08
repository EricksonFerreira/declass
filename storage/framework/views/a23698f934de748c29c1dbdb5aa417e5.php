<?php
    $notificacoes = collect($notificacoes ?? []);
?>

<div class="px-2 py-2" style="min-width: 320px; max-width: 380px;">
    <div class="d-flex justify-content-between align-items-center px-2 pb-2 border-bottom">
        <strong>Notificações</strong>
        <small class="text-muted"><?php echo e($notificacoes->count()); ?> novas</small>
    </div>

    <div style="max-height: 320px; overflow:auto;">
        <?php $__empty_1 = true; $__currentLoopData = $notificacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notificacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $dados = $notificacao->data ?? [];
                $icone = $dados['icone'] ?? 'fas fa-bell';
                $cor = $dados['cor'] ?? 'warning';
                $titulo = $dados['titulo'] ?? 'Notificação';
                $mensagem = $dados['mensagem'] ?? '';
                $url = $dados['url'] ?? null;
            ?>
            <div class="dropdown-item py-2 px-2 border-bottom">
                <div class="d-flex align-items-start">
                    <span class="badge badge-<?php echo e($cor); ?> p-2 mt-1"><i class="<?php echo e($icone); ?>"></i></span>
                    <div class="flex-grow-1 ml-2">
                        <?php if($url): ?>
                            <a href="<?php echo e($url); ?>" class="font-weight-bold d-block" style="font-size: .9rem; color: #1f2937;"><?php echo e($titulo); ?></a>
                        <?php else: ?>
                            <div class="font-weight-bold" style="font-size: .9rem;"><?php echo e($titulo); ?></div>
                        <?php endif; ?>
                        <div class="text-muted" style="font-size: .78rem; line-height: 1.3;"><?php echo e($mensagem); ?></div>
                        <small class="text-muted"><?php echo e($notificacao->created_at?->diffForHumans()); ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="dropdown-item text-center text-muted py-4">
                Nenhuma notificação pendente.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/notificacoes/partials/navbar-dropdown.blade.php ENDPATH**/ ?>