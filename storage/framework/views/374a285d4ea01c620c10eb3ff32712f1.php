

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'createUrl' => route('movimentacao.create')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <?php
        $filtroStatusAtivo = session('movimentacao.filtro.status_movimentacao_id') ?? null;
    ?>

    <?php if(!empty($filters)): ?>
        <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['additionalFilters' => $additionalFilters ?? [],'filters' => $filters,'itensFiltered' => $itensFiltered ?? [],'statusList' => $statusList ?? []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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

    <div class="mv-idx-page">

        
        <div class="mv-idx-statusbar mb-3">
            <div class="mv-idx-statusbar__label">
                <i class="fas fa-filter"></i> Filtrar por status:
            </div>
            <?php
                $filtroStatusAtivo = session('movimentacao.filtro.status_movimentacao_id') ?? null;

                if (is_array($filtroStatusAtivo)) {
                    $filtroStatusAtivo = $filtroStatusAtivo['valor'] ?? null;
                }
            ?>

            <div class="mv-idx-statusbar__chips">
                <a href="<?php echo e(route('movimentacao.removerFiltro', ['atributo' => 'status_movimentacao_id'])); ?>"
                    class="mv-status-chip <?php echo e(empty($filtroStatusAtivo) ? 'is-active' : ''); ?>"
                    onclick="limparFiltroStatus(event, this)">
                    <i class="fas fa-list"></i> Todas
                </a>

                <?php
                    $corMap = [
                        'success' => ['bg' => '#d1fae5', 'txt' => '#065f46', 'border' => '#6ee7b7'],
                        'info' => ['bg' => '#dbeafe', 'txt' => '#1e40af', 'border' => '#93c5fd'],
                        'warning' => ['bg' => '#fef3c7', 'txt' => '#92400e', 'border' => '#fcd34d'],
                        'danger' => ['bg' => '#fee2e2', 'txt' => '#991b1b', 'border' => '#fca5a5'],
                        'secondary' => ['bg' => '#f1f5f9', 'txt' => '#475569', 'border' => '#cbd5e1'],
                        'purple' => ['bg' => '#ede9fe', 'txt' => '#5b21b6', 'border' => '#c4b5fd'],
                        'orange' => ['bg' => '#ffedd5', 'txt' => '#9a3412', 'border' => '#fdba74'],
                        'teal' => ['bg' => '#ccfbf1', 'txt' => '#115e59', 'border' => '#5eead4'],
                    ];
                ?>

                <?php $__currentLoopData = $statusList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $cor = $st->cor ?? 'secondary';
                        if (!is_string($cor) || !isset($corMap[$cor])) {
                            $cor = 'secondary';
                        }

                        $cm = $corMap[$cor];
                        $isActive = (string) ($filtroStatusAtivo ?? '') === (string) $st->id;

                        $styleAtivo = $isActive
                            ? 'background:' .
                                $cm['bg'] .
                                ';color:' .
                                $cm['txt'] .
                                ';border-color:' .
                                $cm['border'] .
                                ';'
                            : '';
                    ?>

                    <button type="button" class="mv-status-chip <?php echo e($isActive ? 'is-active' : ''); ?>"
                        style="<?php echo e($styleAtivo); ?>" data-status-id="<?php echo e($st->id); ?>" data-cor-bg="<?php echo e($cm['bg']); ?>"
                        data-cor-txt="<?php echo e($cm['txt']); ?>" data-cor-border="<?php echo e($cm['border']); ?>"
                        onclick="filtrarPorStatus(this)">
                        <span class="mv-status-dot" style="background: <?php echo e($cm['txt']); ?>"></span>
                        <?php echo e($st->nome); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="card mv-registros-card border-0 shadow-sm">
            <div class="card-header bg-white px-3 py-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:10px">
                    <h3 class="card-title text-sm text-bold mb-0">
                        REGISTROS <span class="badge badge-success"><?php echo e($movimentacaos->total()); ?></span>
                    </h3>

                    <div class="d-flex align-items-center flex-wrap" style="gap:8px">
                        <form action="<?php echo e(route('movimentacao.setPerPage')); ?>" method="POST"
                            class="d-flex align-items-center" style="gap:4px">
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

                        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
                            <button type="button" class="btn btn-outline-secondary active" id="btnViewTable"
                                title="Ver como tabela">
                                <i class="fas fa-list mr-1"></i>Tabela
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnViewCards"
                                title="Ver como cards">
                                <i class="fas fa-th mr-1"></i>Cards
                            </button>
                        </div>

                        <div class="d-flex align-items-center mv-registros-pagination">
                            <?php echo e($movimentacaos->links()); ?>

                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                
                <div id="viewTable" class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead style="background:#f8fafd;border-bottom:2px solid #e2e9f4">
                        <tr>
                            <th class="pl-3" style="width:50px">#</th>
                            <th>Tipo / Status</th>
                            <th class="text-center" style="width:140px">Gerou disponibilidade</th>
                            <th>Usuário</th>
                            <th>Veículo</th>
                            <th>Origem → Destino</th>
                            <th>Datas</th>
                            <th class="text-center" style="width:140px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $movimentacaos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                            $cor = $mov->statusMovimentacao?->cor ?? 'secondary';
                            $isConcluido =
                                strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluído' ||
                                strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluido';
                            $geraDisponibilidade = in_array($mov->gera_disponibilidade, ['S', 's', true, 1, '1'], true);
                            $veiculoLabel = null;
                            if ($mov->veiculo) {
                                $veiculoLabel = $mov->veiculo->nome ?? 'Veículo #' . $mov->veiculo->id;
                                if (!empty($mov->veiculo->placa_licenca)) {
                                    $veiculoLabel .= ' · ' . $mov->veiculo->placa_licenca;
                                    }
                                }
                            ?>
                            <tr data-mov-id="<?php echo e($mov->id); ?>" data-status-cor="<?php echo e($cor); ?>"
                                data-status-nome="<?php echo e(strtolower($mov->statusMovimentacao?->nome ?? '')); ?>"
                                data-concluir-url="<?php echo e(route('movimentacao.concluir', $mov->id)); ?>">
                                <td class="pl-3">
                                    #<?php echo e($mov->id); ?>

                                </td>
                                <td>
                                    <div class="font-weight-bold small"><?php echo e($mov->tipoMovimentacao?->nome ?? '—'); ?></div>
                                    <span
                                        class="badge badge-<?php echo e($cor); ?> mt-1"><?php echo e($mov->statusMovimentacao?->nome ?? '—'); ?></span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-<?php echo e($geraDisponibilidade ? 'success' : 'secondary'); ?>">
                                        <?php echo e($geraDisponibilidade ? 'S' : 'N'); ?> 
                                    </span>
                                </td>
                                <td class="small"><?php echo e($mov->usuario?->nome ?? '—'); ?></td>
                                <td class="small">
                                    <div class="font-weight-bold"><?php echo e($veiculoLabel ?? '—'); ?></div>
                                    <?php if($mov->veiculo?->grupoFrota): ?>
                                        <small class="text-muted"><?php echo e($mov->veiculo->grupoFrota->nome); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <div><?php echo e($mov->localOrigem?->nome ?? '—'); ?></div>
                                    <?php if($mov->localDestino): ?>
                                        <div class="text-muted"><i
                                                class="fas fa-arrow-right fa-xs mr-1"></i><?php echo e($mov->localDestino->nome); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <?php if($mov->data_saida): ?>
                                        <div><span class="text-muted">Saída:</span>
                                            <?php echo e(\Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i')); ?></div>
                                    <?php endif; ?>
                                    <?php if($mov->data_chegada_real): ?>
                                        <div><span class="text-success">Chegada:</span>
                                            <?php echo e(\Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i')); ?></div>
                                    <?php elseif($mov->data_chegada_prevista): ?>
                                        <div><span class="text-muted">Prev.:</span>
                                            <?php echo e(\Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i')); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center" style="gap:4px;flex-wrap:wrap">
                                        <a href="<?php echo e(route('movimentacao.edit', $mov->id)); ?>"
                                            class="btn btn-xs btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if(!$isConcluido): ?>
                                            <button type="button" class="btn btn-xs btn-outline-success btn-concluir-mov"
                                                title="Concluir movimentação" data-id="<?php echo e($mov->id); ?>"
                                                data-url="<?php echo e(route('movimentacao.concluir', $mov->id)); ?>">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('movimentacao.destroy', $mov->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-route fa-2x mb-2 d-block"></i>
                                    Nenhuma movimentação encontrada.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>

                
                <div id="viewCards" class="p-3" style="display:none">
            <?php $__empty_1 = true; $__currentLoopData = $movimentacaos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $cor = $mov->statusMovimentacao?->cor ?? 'secondary';
                    $isConcluido =
                        strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluído' ||
                        strtolower($mov->statusMovimentacao?->nome ?? '') === 'concluido';
                    $veiculoLabel = null;
                    if ($mov->veiculo) {
                        $veiculoLabel = $mov->veiculo->nome ?? 'Veículo #' . $mov->veiculo->id;
                        if (!empty($mov->veiculo->placa_licenca)) {
                            $veiculoLabel .= ' · ' . $mov->veiculo->placa_licenca;
                        }
                    }
                    $corMap2 = [
                        'success' => ['bg' => '#d1fae5', 'txt' => '#065f46', 'accent' => '#10b981'],
                        'info' => ['bg' => '#dbeafe', 'txt' => '#1e40af', 'accent' => '#3b82f6'],
                        'warning' => ['bg' => '#fef3c7', 'txt' => '#92400e', 'accent' => '#f59e0b'],
                        'danger' => ['bg' => '#fee2e2', 'txt' => '#991b1b', 'accent' => '#ef4444'],
                        'secondary' => ['bg' => '#f1f5f9', 'txt' => '#475569', 'accent' => '#94a3b8'],
                        'purple' => ['bg' => '#ede9fe', 'txt' => '#5b21b6', 'accent' => '#8b5cf6'],
                        'orange' => ['bg' => '#ffedd5', 'txt' => '#9a3412', 'accent' => '#f97316'],
                        'teal' => ['bg' => '#ccfbf1', 'txt' => '#115e59', 'accent' => '#14b8a6'],
                    ];
                    $cm2 = $corMap2[$cor] ?? $corMap2['secondary'];
                ?>
                <div class="mv-card mb-3">
                    <div class="mv-card__accent" style="background:<?php echo e($cm2['accent']); ?>"></div>
                    <div class="mv-card__body">
                        <div class="mv-card__top">
                            <div class="d-flex align-items-center" style="gap:8px">
                                <span class="badge badge-secondary">#<?php echo e($mov->id); ?></span>
                                <span
                                    class="badge badge-<?php echo e($cor); ?>"><?php echo e($mov->statusMovimentacao?->nome ?? '—'); ?></span>
                                <span class="badge badge-<?php echo e($geraDisponibilidade ? 'success' : 'secondary'); ?>">
                                    <?php echo e($geraDisponibilidade ? 'S' : 'N'); ?>

                                </span>
                            </div>
                            <div class="mv-card__actions">
                                <a href="<?php echo e(route('movimentacao.edit', $mov->id)); ?>" class="btn btn-xs btn-outline-primary"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if(!$isConcluido): ?>
                                    <button type="button" class="btn btn-xs btn-outline-success btn-concluir-mov"
                                        title="Concluir" data-id="<?php echo e($mov->id); ?>"
                                        data-url="<?php echo e(route('movimentacao.concluir', $mov->id)); ?>">
                                        <i class="fas fa-check-double mr-1"></i>Concluir
                                    </button>
                                <?php endif; ?>
                                <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('movimentacao.destroy', $mov->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                        </div>

                        <div class="mv-card__title"><?php echo e($mov->tipoMovimentacao?->nome ?? '—'); ?></div>

                        <div class="mv-card__grid">
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-car"></i> Veículo</span>
                                <span><?php echo e($veiculoLabel ?? '—'); ?></span>
                            </div>
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-user"></i> Usuário</span>
                                <span><?php echo e($mov->usuario?->nome ?? '—'); ?></span>
                            </div>
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-map-marker-alt"></i> Origem</span>
                                <span><?php echo e($mov->localOrigem?->nome ?? '—'); ?></span>
                            </div>
                            <div class="mv-card__field">
                                <span class="mv-card__field-label"><i class="fas fa-flag-checkered"></i> Destino</span>
                                <span><?php echo e($mov->localDestino?->nome ?? '—'); ?></span>
                            </div>
                            <?php if($mov->data_saida): ?>
                                <div class="mv-card__field">
                                    <span class="mv-card__field-label"><i class="fas fa-sign-out-alt"></i> Saída</span>
                                    <span><?php echo e(\Carbon\Carbon::parse($mov->data_saida)->format('d/m/Y H:i')); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($mov->data_chegada_real): ?>
                                <div class="mv-card__field">
                                    <span class="mv-card__field-label" style="color:#10b981"><i
                                            class="fas fa-check-circle"></i> Chegada</span>
                                    <span
                                        style="color:#10b981;font-weight:600"><?php echo e(\Carbon\Carbon::parse($mov->data_chegada_real)->format('d/m/Y H:i')); ?></span>
                                </div>
                            <?php elseif($mov->data_chegada_prevista): ?>
                                <div class="mv-card__field">
                                    <span class="mv-card__field-label"><i class="fas fa-clock"></i> Prevista</span>
                                    <span><?php echo e(\Carbon\Carbon::parse($mov->data_chegada_prevista)->format('d/m/Y H:i')); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if($mov->reserva_id || $mov->ordem_servico_id): ?>
                            <div class="mv-card__links">
                                <?php if($mov->reserva_id): ?>
                                    <span class="badge badge-light border">
                                        <i class="fas fa-calendar-check mr-1"></i>Reserva #<?php echo e($mov->reserva_id); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if($mov->ordem_servico_id): ?>
                                    <span class="badge badge-light border">
                                        <i class="fas fa-tools mr-1"></i>OS #<?php echo e($mov->ordem_servico_id); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-route fa-2x mb-2 d-block"></i>
                    Nenhuma movimentação encontrada.
                </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    
    <form id="formConcluir" method="POST" action="">
        <?php echo csrf_field(); ?>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        /* ── Status bar ── */
        .mv-idx-statusbar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 14px;
            padding: 10px 16px;
            flex-wrap: wrap;
        }

        .mv-idx-statusbar__label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
            white-space: nowrap;
        }

        .mv-idx-statusbar__chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .mv-status-chip {
            appearance: none;
            border: 1.5px solid #e2e9f4;
            background: #f8fafd;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: .75rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all .15s;
        }

        .mv-status-chip:hover {
            border-color: #4338ca;
            color: #4338ca;
            background: #eef2ff;
            text-decoration: none;
        }

        .mv-status-chip.is-active {
            background: #1a2848;
            border-color: #1a2848;
            color: #fff;
        }

        .mv-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        .mv-registros-card {
            border-radius: 12px;
            overflow: hidden;
        }

        .mv-registros-card .card-header {
            border-bottom: 1px solid #e2e9f4;
        }

        .mv-registros-pagination .pagination {
            margin-bottom: 0;
        }

        #viewTable {
            max-height: calc(100vh - 330px);
            overflow: auto;
        }

        #viewTable thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafd;
        }

        @media (max-width: 767.98px) {
            .mv-registros-card .card-header > div {
                align-items: flex-start !important;
                flex-direction: column;
            }

            .mv-registros-pagination {
                width: 100%;
                overflow-x: auto;
            }
        }

        /* ── Cards ── */
        .mv-card {
            position: relative;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(12, 20, 40, .05);
            transition: box-shadow .2s;
        }

        .mv-card:hover {
            box-shadow: 0 4px 16px rgba(12, 20, 40, .1);
        }

        .mv-card__accent {
            height: 4px;
            width: 100%;
        }

        .mv-card__body {
            padding: 14px 16px;
        }

        .mv-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 6px;
        }

        .mv-card__actions {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .mv-card__title {
            font-size: .92rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .mv-card__grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 8px 12px;
        }

        .mv-card__field {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: .78rem;
        }

        .mv-card__field-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
        }

        .mv-card__links {
            margin-top: 10px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ── Tabela ── */
        #viewTable table thead th {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: none;
            padding: 10px 8px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        (function() {
            'use strict';

            // ── Toggle table/cards ────────────────────────────────────
            const SK_VIEW = 'mvIdxView';
            var savedView = localStorage.getItem(SK_VIEW) || 'table';

            function setView(v) {
                document.getElementById('viewTable').style.display = v === 'table' ? '' : 'none';
                document.getElementById('viewCards').style.display = v === 'cards' ? '' : 'none';
                document.getElementById('btnViewTable').classList.toggle('active', v === 'table');
                document.getElementById('btnViewCards').classList.toggle('active', v === 'cards');
                localStorage.setItem(SK_VIEW, v);
            }

            setView(savedView);
            document.getElementById('btnViewTable').addEventListener('click', function() {
                setView('table');
            });
            document.getElementById('btnViewCards').addEventListener('click', function() {
                setView('cards');
            });

            // ── Filtro por status (session-based via form POST) ────────
            window.filtrarPorStatus = function(btn) {
                var statusId = btn.dataset.statusId;

                // Atualiza visual imediatamente
                document.querySelectorAll('.mv-status-chip').forEach(function(c) {
                    c.classList.remove('is-active');
                });
                btn.classList.add('is-active');

                // Submete via filtro
                var form = document.createElement('form');
                form.method = 'GET';
                form.action = '<?php echo e(route('movimentacao.filtro')); ?>';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'status_movimentacao_id';
                input.value = statusId;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            };

            window.limparFiltroStatus = function(e, el) {
                e.preventDefault();
                window.location.href = el.href;
            };

            // ── Concluir movimentação ─────────────────────────────────
            document.querySelectorAll('.btn-concluir-mov').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var movId = this.dataset.id;
                    var url = this.dataset.url;
                    var rowEl = this;

                    Swal.fire({
                        title: 'Concluir movimentação #' + movId + '?',
                        html: '<p class="text-muted mb-3">Informe a data/hora de chegada real (opcional — padrão: agora).</p>' +
                            '<input type="datetime-local" id="swal-chegada" class="swal2-input" ' +
                            'value="' + new Date(new Date().getTime() - new Date()
                                .getTimezoneOffset() * 60000).toISOString().slice(0, 16) + '">',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-check-double mr-1"></i>Sim, concluir',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        focusConfirm: false,
                        preConfirm: function() {
                            return {
                                chegada_real: document.getElementById('swal-chegada').value
                            };
                        }
                    }).then(function(result) {
                        if (!result.isConfirmed) return;

                        var chegadaReal = result.value.chegada_real;

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                },
                                body: JSON.stringify({
                                    chegada_real: chegadaReal
                                })
                            })
                            .then(function(r) {
                                return r.json();
                            })
                            .then(function(data) {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Concluído!',
                                        text: 'Movimentação #' + movId +
                                            ' concluída com sucesso.',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                }
                            })
                            .catch(function() {
                                Swal.fire('Erro',
                                    'Não foi possível concluir a movimentação.', 'error'
                                );
                            });
                    });
                });
            });

        })();
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'movimentacao-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Lista de movimentações', description: 'Aqui você acompanha todas as movimentações de veículos, com status, origem, destino, usuário e datas.' } },
        { element: '.btn-create-resource, a[href*="movimentacao/create"]', popover: { title: 'Nova movimentação', description: 'Use este botão para registrar uma nova saída, transferência, retorno ou envio para manutenção.', side: 'left' } },
        { element: '.mv-idx-statusbar', popover: { title: 'Filtro por status', description: 'Os chips no topo permitem filtrar rapidamente a listagem pelas situações cadastradas para a movimentação.', side: 'bottom' } },
        { element: '#btnViewTable, #btnViewCards', popover: { title: 'Modos de visualização', description: 'Alterne entre a visão em tabela e a visão em cards para analisar os registros da forma mais prática.', side: 'left' } },
        { element: '#viewTable table thead tr', popover: { title: 'Dados exibidos', description: 'A tabela mostra tipo e status, usuário, veículo, origem, destino, datas e as ações disponíveis para cada movimentação.', side: 'bottom' } },
        { element: '.btn-concluir-mov, #viewTable tbody tr:first-child td:last-child', popover: { title: 'Concluir e editar', description: 'Você pode editar o registro, concluir a movimentação informando a chegada real ou excluir quando necessário.', side: 'left' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo {{current}} de {{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/movimentacao/index.blade.php ENDPATH**/ ?>