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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'createUrl' => route('veiculo.create')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <?php if(isset($filters) && is_array($filters) && count($filters) > 0): ?>
        <div class="veiculo-filter-sticky">
            <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['additionalFilters' => $additionalFilters,'filters' => $filters,'itensFiltered' => $itensFiltered] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
        </div>
    <?php endif; ?>

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success"><?php echo e($veiculos->total()); ?></span>
                </h3>

                <div class="align-bottom d-flex flex-row justify-between" style="gap:6px;flex-wrap:wrap;">
                    <form action="<?php echo e(route('veiculo.setPerPage')); ?>" method="POST">
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

                    
                    <form action="<?php echo e(route('veiculo.setOrdenacao')); ?>" method="POST" class="d-flex align-items-center"
                        style="gap:4px;">
                        <?php echo csrf_field(); ?>
                        <select name="orderBy" class="form-control form-control-xs"
                            style="height:28px;font-size:.8rem;padding:2px 6px;border-radius:6px;border:1px solid #d0daea;"
                            onchange="this.form.submit()">
                            <?php
                                $ordens = [
                                    'id' => 'ID',
                                    'nome' => 'Nome',
                                    'placa_licenca' => 'Placa',
                                    'renavam' => 'RENAVAM',
                                    'ano_fabricacao' => 'Ano Fab.',
                                    'kms' => 'Km',
                                    'created_at' => 'Cadastro',
                                ];
                            ?>
                            <?php $__currentLoopData = $ordens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($val); ?>" <?php echo e(($orderBy ?? 'id') === $val ? 'selected' : ''); ?>>
                                    Ord.: <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <select name="orderDir" class="form-control form-control-xs"
                            style="height:28px;font-size:.8rem;padding:2px 6px;border-radius:6px;border:1px solid #d0daea;"
                            onchange="this.form.submit()">
                            <option value="asc" <?php echo e(($orderDir ?? 'asc') === 'asc' ? 'selected' : ''); ?>>↑ Cresc.</option>
                            <option value="desc" <?php echo e(($orderDir ?? 'asc') === 'desc' ? 'selected' : ''); ?>>↓ Decresc.
                            </option>
                        </select>
                    </form>

                    <div class="veiculo-disponibilidade-filter" title="Filtrar por disponibilidade (pagina atual)">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="todos">
                            <i class="fas fa-list mr-1"></i>Todos
                        </button>

                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="disponivel">
                            <i class="fas fa-check mr-1"></i>Disponível
                        </button>

                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="parcial">
                            <i class="fas fa-adjust mr-1"></i>Parcial
                        </button>

                        <button type="button" class="btn btn-xs btn-outline-secondary" data-avail="indisponivel">
                            <i class="fas fa-times mr-1"></i>Indisponível
                        </button>
                    </div>

                    <div class="veiculo-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        <?php echo e($veiculos->links()); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 veiculo-index-body" data-view="cards">
            <div class="veiculo-cards">
                <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $nomeVeiculo = (string) ($veiculo->nome ?? 'Veiculo');
                        $placaVeiculo = (string) ($veiculo->placa_licenca ?? '');

                        $partesNome = preg_split('/\s+/', trim($nomeVeiculo)) ?: [];
                        $iniciais = collect($partesNome)
                            ->filter()
                            ->take(2)
                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                            ->implode('');

                        $iniciais = $iniciais ?: 'VC';

                        $resumoDisponibilidade = $resumoVeiculosIndex[$veiculo->id] ?? [
                            'percentual' => 0,
                            'status_mes' => 'off',
                            'dias_parciais' => 0,
                            'dias_indisponiveis' => 0,
                        ];

                        $percentualDisponibilidade = (int) ($resumoDisponibilidade['percentual'] ?? 0);
                        $statusMesDisponibilidade = (string) ($resumoDisponibilidade['status_mes'] ?? 'off');

                        $disponivel = $statusMesDisponibilidade === 'ok' && $percentualDisponibilidade >= 100;
                        $parcial =
                            $statusMesDisponibilidade === 'partial' ||
                            ($percentualDisponibilidade > 0 && $percentualDisponibilidade < 100);
                        $indisponivel = $percentualDisponibilidade <= 0;

                        $availKey = $disponivel ? 'disponivel' : ($parcial ? 'parcial' : 'indisponivel');

                        $accentClass = match ($availKey) {
                            'disponivel' => 'vc-status--green',
                            'parcial' => 'vc-status--amber',
                            default => 'vc-status--red',
                        };

                        $statusLabelDisponibilidade = match ($availKey) {
                            'disponivel' => 'Disponível',
                            'parcial' => 'Parcial',
                            default => 'Indisponível',
                        };

                        $statusBadgeClassDisponibilidade = match ($availKey) {
                            'disponivel' => 'vc__badge--green',
                            'parcial' => 'vc__badge--amber',
                            default => 'vc__badge--red',
                        };

                        $statusCor = (string) ($veiculo->statusVeiculo?->cor ?? 'secondary');
                        $statusNome = (string) ($veiculo->statusVeiculo?->nome ?? '');
                        $localNome = (string) ($veiculo->localDisponibilidade?->nome ?? '-');

                        $dataDisp = $veiculo->data_hora_disponibilidade
                            ? \Carbon\Carbon::parse($veiculo->data_hora_disponibilidade)->format('d/m/Y H:i')
                            : '-';

                        $showStatusBadge = $statusNome !== '';
                        $pendenciasCadastro = [];
                        if (empty($veiculo->local_disponibilidade)) $pendenciasCadastro[] = 'Estação';
                        if (empty($veiculo->data_hora_disponibilidade)) $pendenciasCadastro[] = 'Disponibilidade';
                    ?>

                    <article class="vc <?php echo e($accentClass); ?>" data-avail="<?php echo e($availKey); ?>"
                        onclick="window.location='<?php echo e(route('veiculo.show', $veiculo->id)); ?>';">
                        <div class="vc__accent"></div>

                        <div class="vc__head">
                            <div class="vc__avatar"><?php echo e($iniciais); ?></div>
                            <div class="vc__head-info">
                                <div class="vc__id">VEICULO #<?php echo e($veiculo->id); ?></div>
                                <div class="vc__title"><?php echo e($nomeVeiculo); ?></div>
                                <div class="vc__sub"><?php echo e($placaVeiculo ? 'Placa: ' . $placaVeiculo : 'Placa: -'); ?></div>
                            </div>
                            <div class="vc__badges">
                                <span class="vc__badge <?php echo e($statusBadgeClassDisponibilidade); ?>">
                                    <?php echo e($statusLabelDisponibilidade); ?> · <?php echo e($percentualDisponibilidade); ?>%
                                </span>
                                <?php if($pendenciasCadastro): ?>
                                    <span class="vc__badge vc__badge--amber" title="<?php echo e(implode(', ', $pendenciasCadastro)); ?>">
                                        <?php echo e(count($pendenciasCadastro)); ?> pendência(s)
                                    </span>
                                <?php endif; ?>
                                <?php if($showStatusBadge): ?>
                                    <span class="badge badge-xs bg-<?php echo e($statusCor); ?>" title="Status do veiculo">
                                        <?php echo e($statusNome); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="vc__meta">
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-layer-group mr-1"></i>Grupo</span>
                                <strong class="vc__meta-value"><?php echo e($veiculo->grupoFrota?->nome ?? '-'); ?></strong>
                            </div>
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-truck-pickup mr-1"></i>Tipo</span>
                                <strong class="vc__meta-value"><?php echo e($veiculo->tipoFrota?->nome ?? '-'); ?></strong>
                            </div>
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-car mr-1"></i>Modelo</span>
                                <strong class="vc__meta-value">
                                    <?php echo e($veiculo->marca?->nome ?? '-'); ?><?php echo e($veiculo->modelo?->nome ? ' / ' . $veiculo->modelo->nome : ''); ?>

                                </strong>
                            </div>
                            <div class="vc__meta-row">
                                <span class="vc__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Local</span>
                                <strong class="vc__meta-value"><?php echo e($localNome); ?></strong>
                            </div>
                            <div class="vc__meta-row vc__meta-row--full">
                                <span class="vc__meta-label"><i class="fas fa-clock mr-1"></i>Disponibilidade</span>
                                <strong class="vc__meta-value">
                                    <?php echo e($statusLabelDisponibilidade); ?> no mês · <?php echo e($percentualDisponibilidade); ?>%
                                </strong>
                                <small class="text-muted">
                                    Inicial: <?php echo e($dataDisp); ?>

                                </small>
                            </div>
                        </div>

                        <div class="vc__foot">
                            
                            
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="veiculo-table table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo de Frota</th>
                            <th>Grupo de Frota</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Status</th>
                            <th>Local Disponivel Inicial</th>
                            <th style="width: 180px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $resumoDisponibilidade = $resumoVeiculosIndex[$veiculo->id] ?? [
                                    'percentual' => 0,
                                    'status_mes' => 'off',
                                ];

                                $percentualDisponibilidade = (int) ($resumoDisponibilidade['percentual'] ?? 0);
                                $statusMesDisponibilidade = (string) ($resumoDisponibilidade['status_mes'] ?? 'off');

                                $availKey = match (true) {
                                    $statusMesDisponibilidade === 'ok' && $percentualDisponibilidade >= 100
                                        => 'disponivel',
                                    $statusMesDisponibilidade === 'partial' ||
                                        ($percentualDisponibilidade > 0 && $percentualDisponibilidade < 100)
                                        => 'parcial',
                                    default => 'indisponivel',
                                };

                                $statusLabelDisponibilidade = match ($availKey) {
                                    'disponivel' => 'Disponível',
                                    'parcial' => 'Parcial',
                                    default => 'Indisponível',
                                };

                                $badgeDisponibilidade = match ($availKey) {
                                    'disponivel' => 'bg-green',
                                    'parcial' => 'bg-amber',
                                    default => 'bg-red',
                                };

                                $pendenciasCadastro = [];
                                if (empty($veiculo->local_disponibilidade)) $pendenciasCadastro[] = 'Estação';
                                if (empty($veiculo->data_hora_disponibilidade)) $pendenciasCadastro[] = 'Disponibilidade';
                            ?>
                            <tr data-avail="<?php echo e($availKey); ?>"
                                onclick="window.location='<?php echo e(route('veiculo.show', $veiculo->id)); ?>';"
                                style="cursor: pointer;">
                                <td><?php echo e($veiculo->nome); ?></td>
                                <td><?php echo e($veiculo->tipoFrota?->nome); ?></td>
                                <td><?php echo e($veiculo->grupoFrota?->nome); ?></td>
                                <td><?php echo e($veiculo->marca?->nome); ?></td>
                                <td><?php echo e($veiculo->modelo?->nome); ?></td>
                                <td>
                                    <?php $stCor = optional($veiculo->statusVeiculo)->cor ?? 'secondary'; ?>
                                    <span
                                        class="badge badge-xs bg-<?php echo e($stCor); ?>"><?php echo e($veiculo->statusVeiculo?->nome ?? '—'); ?></span>
                                </td>
                                <td>
                                    <div>
                                        <?php echo e($veiculo->localDisponibilidade?->nome ?? '—'); ?>

                                    </div>

                                    <div class="small text-muted">
                                        Inicial:
                                        <?php echo e($veiculo->data_hora_disponibilidade
                                            ? \Carbon\Carbon::parse($veiculo->data_hora_disponibilidade)->format('d/m/Y H:i')
                                            : '—'); ?>

                                    </div>

                                    <span class="badge badge-xs <?php echo e($badgeDisponibilidade); ?>">
                                        <?php echo e($statusLabelDisponibilidade); ?> · <?php echo e($percentualDisponibilidade); ?>%
                                    </span>
                                    <?php if($pendenciasCadastro): ?>
                                        <span class="badge badge-warning mt-1" title="<?php echo e(implode(', ', $pendenciasCadastro)); ?>">
                                            <?php echo e(count($pendenciasCadastro)); ?> pendência(s)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="width: 180px;" onclick="event.stopPropagation();">
                                    <a href="<?php echo e(route('veiculo.show', $veiculo->id)); ?>" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="<?php echo e(route('veiculo.edit', $veiculo->id)); ?>" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                    
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        (function() {
            const body = document.querySelector('.veiculo-index-body');
            const buttons = document.querySelectorAll('.veiculo-view-toggle [data-view]');
            const availButtons = document.querySelectorAll('.veiculo-disponibilidade-filter [data-avail]');
            if (!body || !buttons.length) return;

            function setView(view) {
                body.setAttribute('data-view', view);
                localStorage.setItem('veiculoIndexView', view);
                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            function setAvailabilityFilter(key) {
                localStorage.setItem('veiculoAvailabilityFilter', key);
                availButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.avail === key);
                });

                const targets = body.querySelectorAll('[data-avail]');
                targets.forEach(el => {
                    if (key === 'todos') {
                        el.style.display = '';
                        return;
                    }
                    el.style.display = (el.dataset.avail === key) ? '' : 'none';
                });
            }

            const saved = localStorage.getItem('veiculoIndexView') || 'cards';
            setView(saved);

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });

            if (availButtons.length) {
                const savedAvail = localStorage.getItem('veiculoAvailabilityFilter') || 'todos';
                setAvailabilityFilter(savedAvail);
                availButtons.forEach(btn => {
                    btn.addEventListener('click', () => setAvailabilityFilter(btn.dataset.avail));
                });
            }
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .veiculo-filter-sticky {
            position: sticky;
            top: 10px;
            z-index: 1030;
        }

        .veiculo-disponibilidade-filter {
            display: flex;
            gap: 5px;
            margin-left: 12px;
        }

        .veiculo-disponibilidade-filter .btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .veiculo-view-toggle {
            display: flex;
            gap: 5px;
            margin-left: 12px;
        }

        .veiculo-view-toggle .btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .veiculo-index-body[data-view="table"] .veiculo-cards {
            display: none;
        }

        .veiculo-index-body[data-view="cards"] .veiculo-table {
            display: none;
        }

        .veiculo-index-body[data-view="table"] .veiculo-table {
            max-height: calc(100vh - 360px);
            overflow: auto;
            border-radius: 6px;
        }

        .veiculo-index-body[data-view="table"] .veiculo-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
        }

        .veiculo-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 16px;
            padding: 10px 8px 16px;
        }

        .vc {
            position: relative;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 18px;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(11, 18, 32, .06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .vc:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(11, 18, 32, .13);
        }

        .vc__accent {
            height: 3px;
            background: #e2e9f4;
        }

        .vc-status--green .vc__accent {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .vc-status--red .vc__accent {
            background: linear-gradient(90deg, #ef4444, #f87171);
        }

        .vc-status--amber .vc__accent {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .vc-status--slate .vc__accent {
            background: linear-gradient(90deg, #64748b, #94a3b8);
        }

        .vc__head {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .vc__avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff;
            font-size: .82rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            letter-spacing: .04em;
        }

        .vc__head-info {
            flex: 1;
            min-width: 0;
        }

        .vc__id {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .vc__title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vc__sub {
            font-size: .74rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .vc__badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            margin-left: 6px;
        }

        .vc__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .70rem;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 999px;
            line-height: 1;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .vc__badge--green {
            background: rgba(16, 185, 129, .12);
            color: #047857;
            border-color: rgba(16, 185, 129, .22);
        }

        .vc__badge--amber {
            background: rgba(245, 158, 11, .14);
            color: #b45309;
            border-color: rgba(245, 158, 11, .28);
        }

        .vc__badge--red {
            background: rgba(239, 68, 68, .12);
            color: #b91c1c;
            border-color: rgba(239, 68, 68, .22);
        }

        .vc__badge--slate {
            background: rgba(100, 116, 139, .12);
            color: #475569;
            border-color: rgba(100, 116, 139, .2);
        }

        .vc__meta {
            padding: 12px 16px 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 12px;
        }

        .vc__meta-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .vc__meta-row--full {
            grid-column: 1 / -1;
        }

        .vc__meta-label {
            font-size: .70rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .vc__meta-value {
            font-size: .86rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vc__foot {
            margin-top: auto;
            padding: 12px 16px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 991.98px) {
            .veiculo-cards {
                grid-template-columns: 1fr;
                padding: 6px 4px 12px;
            }

            .veiculo-index-body[data-view="table"] .veiculo-cards {
                display: grid;
            }

            .veiculo-index-body[data-view="table"] .veiculo-table {
                display: none;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/index.blade.php ENDPATH**/ ?>