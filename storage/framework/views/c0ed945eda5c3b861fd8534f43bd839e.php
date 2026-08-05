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
    <div class="d-flex flex-column flex-sm-row justify-content-between align-sm-items-center mb-3">
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
        
        <?php if(!request()->routeIs('reserva.minhas')): ?>
            <?php
                $empresa = auth()->user()->empresa;
                $siteTipoReserva = $empresa?->site_tipo_reserva ?? 'ambas';
                $aluguelAppAtivo = ($empresa?->aluguel_app_ativo ?? 'N') === 'S';
                
                $temComum = in_array($siteTipoReserva, ['reserva_comum', 'ambas']);
                $temApp = $aluguelAppAtivo && in_array($siteTipoReserva, ['motorista_app', 'ambas']);
            ?>
            
            <div class="d-flex flex-wrap mt-2 mt-sm-0" style="gap: 10px;">
                <?php if($temComum): ?>
                    <a href="<?php echo e(route('reserva.create')); ?>" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus-circle mr-1"></i> Nova Reserva Comum
                    </a>
                <?php endif; ?>
                <?php if($temApp): ?>
                    <a href="<?php echo e(route('reserva-motorista-app.create')); ?>" class="btn btn-info shadow-sm">
                        <i class="fas fa-car mr-1"></i> Nova Reserva APP
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(isset($filters) && is_array($filters) && count($filters) > 0): ?>
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
    <?php endif; ?>
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success"><?php echo e($reservas->total()); ?></span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="<?php echo e(route('reserva.setPerPage')); ?>" method="POST">
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
                    <div class="reserva-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                    <div class="d-flex align-items-end">
                        <?php echo e($reservas->links()); ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 reserva-index-body" data-view="cards">
            <div class="reserva-cards">
                <?php $__currentLoopData = $reservas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reserva): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $totalCalculado = $reserva->valor_total_final;
                        if ($totalCalculado === null) { $totalCalculado = $reserva->valor_total_com_desconto; }
                        if ($totalCalculado === null) { $totalCalculado = $reserva->valor_total_tarifa_base; }
                        if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                            $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                        }
                        $totalCalculado    = (float) ($totalCalculado ?? 0);
                        $pagamentoEfetuado = method_exists($reserva, 'isPagamentoEfetuado')
                            ? $reserva->isPagamentoEfetuado()
                            : in_array($reserva->pagamento_efetuado ?? false, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
                        $isMotoristaApp    = ($reserva->origem_tipo ?? 'reserva') === 'motorista_app';
                        $pagamentoPendente = $isMotoristaApp
                            ? (bool) ($reserva->pagamento_pendente ?? false)
                            : ! $pagamentoEfetuado;
                        $diasReserva       = (int) ($reserva->quantidade_dias ?? 0);
                        $statusNome        = strtolower((string) ($reserva->status_nome ?? $reserva->statusReserva?->nome ?? ''));
                        $statusClass       = match ($statusNome) {
                            'confirmada'         => 'rc-status--green',
                            'cancelada'          => 'rc-status--red',
                            'criada', 'pendente' => 'rc-status--amber',
                            default              => 'rc-status--slate',
                        };
                        $nomeCliente  = $reserva->usuario?->nome ?? $reserva->user?->nome ?? 'Sem cliente';
                        $partesNome   = preg_split('/\s+/', trim($nomeCliente)) ?: [];
                        $iniciais     = collect($partesNome)->filter()->take(2)
                                            ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        $iniciais     = $iniciais ?: 'CL';
                        $dataInicio   = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : '-';
                        $horaInicio   = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('H:i') : '';
                        $dataFim      = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : '-';
                        $horaFim      = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('H:i')      : '';
                        $codigoReserva = $reserva->codigo_exibicao ?? $reserva->codigo_reserva ?? ('#' . $reserva->id);
                        $urlDetalhes  = $reserva->url_detalhes ?? route('reserva.show', $reserva->id);
                        $statusExibicao = $reserva->status_exibicao ?? ($reserva->statusReserva?->nome ?? 'Status');
                    ?>

                    <article class="rc <?php echo e($statusClass); ?> <?php echo e($isMotoristaApp ? 'rc--app' : ''); ?>" onclick="window.location='<?php echo e($urlDetalhes); ?>';">

                        
                        <div class="rc__accent"></div>

                        
                        <div class="rc__head">
                            <div class="rc__head-info">
                                <div class="rc__id" style="font-size:1.1rem;font-weight:800;"><?php echo e($codigoReserva); ?></div>
                                <div class="rc__cliente"><?php echo e($nomeCliente); ?></div>
                                <?php if(!empty($reserva->usuario?->email ?? $reserva->user?->email)): ?>
                                    <div class="rc__email"><?php echo e($reserva->usuario?->email ?? $reserva->user?->email); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="rc__badges">
                                <?php if($isMotoristaApp): ?>
                                    <span class="rc__badge rc__badge--app">
                                        <i class="fas fa-redo-alt mr-1"></i> Motorista APP
                                    </span>
                                    <?php if($pagamentoPendente): ?>
                                        <span class="rc__badge rc__badge--amber" title="Há uma cobrança recorrente pendente">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Falta pagamento
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="rc__badge <?php echo e($pagamentoEfetuado ? 'rc__badge--green' : 'rc__badge--amber'); ?>">
                                        <?php echo e($pagamentoEfetuado ? 'Pago' : 'Pendente'); ?>

                                    </span>
                                <?php endif; ?>
                                <span class="rc__badge rc__badge--status rc__badge--<?php echo e(strtolower(str_replace(' ', '-', $statusNome))); ?> bg-<?php echo e($reserva->statusReserva?->cor); ?>" title="<?php echo e($reserva->statusReserva?->descricao); ?>">
                                    <?php echo e($statusExibicao); ?>

                                </span>
                            </div>
                        </div>

                        <?php if($isMotoristaApp): ?>
                            <div class="rc__route rc__route--app">
                                <div class="rc__stop">
                                    <div class="rc__stop-dot rc__stop-dot--a"></div>
                                    <div>
                                        <div class="rc__stop-label">Retirada</div>
                                        <div class="rc__stop-place"><?php echo e($reserva->estacaoRetirada?->nome ?? 'Estação não informada'); ?></div>
                                        <div class="rc__stop-date"><?php echo e($dataInicio); ?><?php if($horaInicio): ?> <span><?php echo e($horaInicio); ?></span><?php endif; ?></div>
                                    </div>
                                </div>
                                <div class="rc__route-mid">
                                    <div class="rc__route-line"></div>
                                    <div class="rc__route-pill <?php echo e($pagamentoPendente ? 'bg-danger' : 'bg-success'); ?>"><?php echo e($pagamentoPendente ? 'Pgto' : 'OK'); ?></div>
                                    <div class="rc__route-line"></div>
                                </div>
                                <div class="rc__stop rc__stop--right">
                                    <div class="rc__stop-dot rc__stop-dot--b"></div>
                                    <div>
                                        <div class="rc__stop-label">Vigência</div>
                                        <div class="rc__stop-place"><?php echo e($reserva->modelos?->nome ?? 'Plano recorrente'); ?></div>
                                        <div class="rc__stop-date"><?php echo e($dataFim); ?><?php if($horaFim): ?> <span><?php echo e($horaFim); ?></span><?php endif; ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="rc__route">
                                <div class="rc__stop">
                                    <div class="rc__stop-dot rc__stop-dot--a"></div>
                                    <div>
                                        <div class="rc__stop-label">Retirada</div>
                                        <div class="rc__stop-place"><?php echo e($reserva->estacaoRetirada?->nome ?? '-'); ?></div>
                                        <div class="rc__stop-date"><?php echo e($dataInicio); ?><?php if($horaInicio): ?> <span><?php echo e($horaInicio); ?></span><?php endif; ?></div>
                                    </div>
                                </div>
                                <div class="rc__route-mid">
                                    <div class="rc__route-line"></div>
                                    <div class="rc__route-pill"><?php echo e($diasReserva); ?>d</div>
                                    <div class="rc__route-line"></div>
                                </div>
                                <div class="rc__stop rc__stop--right">
                                    <div class="rc__stop-dot rc__stop-dot--b"></div>
                                    <div>
                                        <div class="rc__stop-label">Devolução</div>
                                        <div class="rc__stop-place"><?php echo e($reserva->estacaoDevolucao?->nome ?? '-'); ?></div>
                                        <div class="rc__stop-date"><?php echo e($dataFim); ?><?php if($horaFim): ?> <span><?php echo e($horaFim); ?></span><?php endif; ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <div class="rc__foot">
                            <?php if($isMotoristaApp): ?>
                                <div class="rc__vehicle">
                                    <i class="fas fa-redo-alt"></i>
                                    <span><?php echo e($reserva->modelos?->nome ?? 'Plano recorrente'); ?></span>
                                    <span class="rc__group"><?php echo e($reserva->tipo_label ?? 'Motorista APP'); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="rc__vehicle">
                                    <i class="fas fa-car"></i>
                                    <span><?php echo e($reserva->modelos?->nome ?? 'Modelo não definido'); ?></span>
                                    <?php if(!empty($reserva->grupoFrota?->nome)): ?>
                                        <span class="rc__group"><?php echo e($reserva->grupoFrota->nome); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="rc__total">
                                <span>R$</span>
                                <strong><?php echo e(number_format($totalCalculado, 2, ',', '.')); ?></strong>
                            </div>
                        </div>

                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="reserva-table table-responsive">
                <table class="table table-hover table-bordered table-sm">
    <thead class="thead-light">
        <tr>
            <th class="text-center">#</th>
            <th>Cliente</th>
            <th>Origem</th>
            <th>Veículo</th>
            <th>Retirada</th>
            <th>Devolução</th>
            <th class="text-right">Total</th>
            <th>Pagamento</th>
            <th>Status</th>
            
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $reservas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reserva): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $totalCalculado = $reserva->valor_total_final;
                if ($totalCalculado === null) {
                    $totalCalculado = $reserva->valor_total_com_desconto;
                }
                if ($totalCalculado === null) {
                    $totalCalculado = $reserva->valor_total_tarifa_base;
                }
                if ($totalCalculado === null && $reserva->tarifa_base && $reserva->quantidade_dias) {
                    $totalCalculado = $reserva->tarifa_base * $reserva->quantidade_dias;
                }
                $totalCalculado = (float) ($totalCalculado ?? 0);
                $pagamentoEfetuado = method_exists($reserva, 'isPagamentoEfetuado')
                    ? $reserva->isPagamentoEfetuado()
                    : in_array($reserva->pagamento_efetuado ?? false, [true, 1, '1', 'S', 's', 'sim', 'SIM'], true);
                $isMotoristaApp = ($reserva->origem_tipo ?? 'reserva') === 'motorista_app';
                $pagamentoPendente = $isMotoristaApp
                    ? (bool) ($reserva->pagamento_pendente ?? false)
                    : ! $pagamentoEfetuado;
                $urlDetalhes = $reserva->url_detalhes ?? route('reserva.show', $reserva->id);
                $statusExibicao = $reserva->status_exibicao ?? ($reserva->statusReserva?->nome ?? '-');
            ?>
            <tr onclick="window.location='<?php echo e($urlDetalhes); ?>';" style="cursor: pointer;" role="link" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){window.location='<?php echo e($urlDetalhes); ?>';}">
                <td class="text-center" style="font-size:1.1rem;font-weight:800;"><?php echo e($reserva->codigo_exibicao ?? $reserva->codigo_reserva ?? ('#' . $reserva->id)); ?></td>
                <td>
                    <strong><?php echo e($reserva->usuario?->nome ?? $reserva->user?->nome ?? 'Sem cliente'); ?></strong><br>
                    <small><?php echo e($reserva->usuario?->email ?? $reserva->user?->email ?? '-'); ?></small>
                </td>
                <td>
                    <span class="badge badge-xs <?php echo e($isMotoristaApp ? 'badge-info' : (($reserva->origem_cadastro ?? '') === 'site' ? 'badge-info' : 'badge-primary')); ?>">
                        <?php echo e($isMotoristaApp ? 'Motorista APP' : ($reserva->label_origem_cadastro ?? 'Reservas')); ?>

                    </span><br>
                    <small><?php echo e($isMotoristaApp ? ($pagamentoPendente ? 'Cobrança recorrente pendente' : 'Ciclo recorrente em dia') : (($reserva->origem_cadastro ?? '') === 'site' ? 'Portal do cliente' : 'Colaborador interno')); ?></small>
                </td>
                <td>
                    <?php if($isMotoristaApp): ?>
                        <strong><?php echo e($reserva->modelos?->nome ?? 'Plano recorrente'); ?></strong><br>
                        <small><?php echo e($reserva->grupoFrota?->nome ?? 'Motorista APP'); ?></small>
                    <?php else: ?>
                        <strong><?php echo e($reserva->grupoFrota?->nome ?? '-'); ?></strong><br>
                        <small><?php echo e($reserva->modelos?->nome ?? 'Modelo não definido'); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo e($reserva->estacaoRetirada?->nome ?? '-'); ?></strong><br>
                    <small><?php echo e(\Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i')); ?></small>
                </td>
                <td>
                    <strong><?php echo e($isMotoristaApp ? 'Vigência contratada' : ($reserva->estacaoDevolucao?->nome ?? '-')); ?></strong><br>
                    <small><?php echo e(\Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i')); ?></small>
                </td>
                <td class="text-right">
                    <strong>BRL <?php echo e(number_format($totalCalculado, 2, ',', '.')); ?></strong><br>
                    <small><?php echo e((int) ($reserva->quantidade_dias ?? 0)); ?> dias</small>
                </td>
                <td>
                    <span class="badge badge-xs <?php echo e($pagamentoEfetuado ? 'badge-success' : 'badge-warning'); ?>">
                        <?php echo e($isMotoristaApp ? 'Motorista APP' : 'Reservas'); ?>

                    </span>
                    <br>
                    <small class="<?php echo e($pagamentoPendente ? 'text-warning' : 'text-success'); ?>">
                        <i class="fas <?php echo e($pagamentoPendente ? 'fa-exclamation-triangle' : 'fa-check-circle'); ?> mr-1"></i>
                        <?php echo e($pagamentoPendente ? 'Pagamento pendente' : 'Em dia'); ?>

                    </small>
                </td>
                <td>
                    <span class="badge badge-xs bg-<?php echo e($reserva->statusReserva?->cor); ?>" style="cursor: help;" data-toggle="tooltip" title="<?php echo e($reserva->statusReserva?->descricao); ?>">
                        <?php echo e($statusExibicao); ?>

                    </span>
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
        (function () {
            const body = document.querySelector('.reserva-index-body');
            const buttons = document.querySelectorAll('.reserva-view-toggle [data-view]');
            if (!body || !buttons.length) return;

            function setView(view) {
                body.setAttribute('data-view', view);
                localStorage.setItem('reservaIndexView', view);
                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });
            }

            const saved = localStorage.getItem('reservaIndexView') || 'cards';
            setView(saved);

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });
        })();
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID      = 'reserva-index-v2';
    const STATUS_URL   = '/admin/tour/status/'   + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY    = 'tour_feito__' + TOUR_ID;
    const forceTour    = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Lista de Reservas', description: 'Aqui você visualiza todas as reservas cadastradas no sistema. Use os filtros e a busca para encontrar reservas específicas.' } },
        { element: '.reserva-view-toggle', popover: { title: 'Alternar visualização', description: 'Escolha entre a visualização em Cards (mais visual) ou em Tabela (mais compacta). A preferência é salva automaticamente.', side: 'bottom' } },
        { element: '.reserva-cards .rc', popover: { title: 'Card de reserva', description: 'Cada card mostra: cliente, status, estações de retirada e devolução, duração, veículo e valor total. Clique no card para abrir os detalhes.', side: 'right' } },
        { element: '.rc__badges', popover: { title: 'Status de pagamento e reserva', description: 'O primeiro badge indica se o pagamento foi efetuado. O segundo mostra o status atual da reserva (Criada, Confirmada, Cancelada…).', side: 'left' } },
        { element: '.rc__route', popover: { title: 'Rota da reserva', description: 'Veja a estação de retirada (ponto verde) e devolução (ponto vermelho), com datas e a duração total em dias.', side: 'top' } },
        { element: '.btn-create-resource', popover: { title: 'Nova reserva', description: 'Clique aqui para criar uma nova reserva. Você será guiado por um formulário passo a passo.', side: 'bottom' } },
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

<?php $__env->startSection('css'); ?>
<style>
    /* ── toggle view ── */
    .reserva-view-toggle {
        display: flex;
        gap: 5px;
        margin-left: 12px;
    }

    .reserva-view-toggle .btn.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }

    .reserva-index-body[data-view="table"] .reserva-cards { display: none; }
    .reserva-index-body[data-view="cards"] .reserva-table { display: none; }

    /* ── grid ── */
    .reserva-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
        padding: 10px 8px 16px;
    }

    /* ══════════════════════════════════════
       CARD BASE
    ══════════════════════════════════════ */
    .rc {
        position: relative;
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 18px;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 0;
        box-shadow: 0 2px 10px rgba(11,18,32,.06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .rc:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(11,18,32,.13);
    }

    /* ── accent top bar ── */
    .rc__accent {
        height: 3px;
        background: #e2e9f4;
    }

    .rc-status--green  .rc__accent { background: linear-gradient(90deg, #10b981, #34d399); }
    .rc-status--red    .rc__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
    .rc-status--amber  .rc__accent { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .rc-status--slate  .rc__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }
    .rc--app .rc__accent { background: linear-gradient(90deg, #2563eb, #60a5fa); }

    /* ── head ── */
    .rc__head {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 14px 16px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rc__head-info {
        flex: 1;
        min-width: 0;
    }

    .rc__id {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .rc__cliente {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc__email {
        font-size: .74rem;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 1px;
    }

    .rc__badges {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        flex-shrink: 0;
    }

    .rc__badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .04em;
        white-space: nowrap;
        text-transform: uppercase;
    }

    .rc__badge--green  { background: rgba(16,185,129,.12);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .rc__badge--amber  { background: rgba(245,158,11,.14);  color: #b45309; border: 1px solid rgba(245,158,11,.28); }
    .rc__badge--red    { background: rgba(239,68,68,.12);   color: #b91c1c; border: 1px solid rgba(239,68,68,.22); }
    .rc__badge--slate  { background: rgba(100,116,139,.12); color: #475569; border: 1px solid rgba(100,116,139,.2); }
    .rc__badge--app    { background: rgba(37,99,235,.12); color: #1d4ed8; border: 1px solid rgba(37,99,235,.22); }

    /* status badge color by name */
    .rc__badge--confirmada  { background: rgba(16,185,129,.12);  color: #047857; border: 1px solid rgba(16,185,129,.22); }
    .rc__badge--cancelada   { background: rgba(239,68,68,.12);   color: #b91c1c; border: 1px solid rgba(239,68,68,.22); }
    .rc__badge--criada,
    .rc__badge--pendente    { background: rgba(245,158,11,.14);  color: #b45309; border: 1px solid rgba(245,158,11,.28); }
    .rc__badge--status      { background: rgba(100,116,139,.12); color: #475569; border: 1px solid rgba(100,116,139,.2); }

    /* ── route ── */
    .rc__route {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 8px;
        align-items: center;
        padding: 14px 16px;
        background: linear-gradient(135deg, #f8faff, #f0f4ff);
        border-bottom: 1px solid #eef1fa;
    }

    .rc__stop {
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .rc__stop--right {
        flex-direction: row-reverse;
        text-align: right;
    }

    .rc__stop-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }

    .rc__stop-dot--a { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.2); }
    .rc__stop-dot--b { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.18); }

    .rc__stop-label {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .rc__stop-place {
        font-size: .82rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }

    .rc__stop-date {
        font-size: .76rem;
        font-weight: 600;
        color: #1e40af;
        margin-top: 2px;
    }

    .rc__stop-date span {
        font-weight: 400;
        color: #64748b;
        margin-left: 4px;
    }

    .rc__route-mid {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .rc__route-line {
        width: 1px;
        height: 14px;
        background: linear-gradient(180deg, #c7d7f4, #93c5fd);
    }

    .rc__route-pill {
        background: #1e40af;
        color: #fff;
        font-size: .64rem;
        font-weight: 800;
        padding: 3px 7px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* ── footer ── */
    .rc__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 16px;
    }

    .rc__vehicle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .8rem;
        color: #475569;
        font-weight: 600;
        min-width: 0;
        overflow: hidden;
    }

    .rc__vehicle i {
        color: #94a3b8;
        font-size: .78rem;
        flex-shrink: 0;
    }

    .rc__vehicle span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc__group {
        display: inline-flex;
        align-items: center;
        padding: 2px 7px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: .66rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .rc__total {
        display: flex;
        align-items: baseline;
        gap: 3px;
        flex-shrink: 0;
    }

    .rc__total span {
        font-size: .72rem;
        font-weight: 700;
        color: #64748b;
    }

    .rc__total strong {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -.01em;
    }

    /* ── responsive ── */
    @media (max-width: 768px) {
        .reserva-view-toggle { display: none; }
        .reserva-cards { grid-template-columns: 1fr; padding: 6px 4px 12px; }
        .reserva-index-body[data-view="table"] .reserva-cards  { display: grid; }
        .reserva-index-body[data-view="table"] .reserva-table  { display: none; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/index.blade.php ENDPATH**/ ?>