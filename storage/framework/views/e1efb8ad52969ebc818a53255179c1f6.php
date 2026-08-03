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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="col-12 px-0">
        <div class="ci-card">

            
            <div class="ci-card-header">
                <div class="ci-header-left">
                    <div class="ci-header-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <div class="ci-header-eyebrow">Check-in</div>
                        <h4 class="ci-header-title">Reserva #<?php echo e($codigoBreadcrumb ?? $reserva->codigoReserva->codigo ?? ''); ?></h4>
                        <p class="ci-header-sub">
                            <i class="fas fa-user mr-1"></i><?php echo e($reserva->usuario->nome ?? 'Cliente'); ?>

                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-car mr-1"></i><?php echo e($reserva->modelos->nome ?? 'Veiculo'); ?>

                        </p>
                    </div>
                </div>
                <div class="ci-header-right">
                    <?php
                        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : null;
                        $dataFim    = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : null;
                    ?>
                    <?php if($dataInicio): ?>
                        <div class="ci-date-badge">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo e($dataInicio); ?><?php if($dataFim): ?> &rarr; <?php echo e($dataFim); ?><?php endif; ?></span>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('reserva.show', $codigoBreadcrumb ?? $reserva->id)); ?>" class="ci-btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>

            
            <div class="ci-body">

                <input type="hidden" id="reserva_id" name="reserva_id" value="<?php echo e($reserva->id); ?>">
                <input type="hidden" id="codigo" name="codigo" value="<?php echo e($codigoBreadcrumb ?? $reserva->codigoReserva?->codigo ?? $reserva->id); ?>">
                <?php
                    $pagamentosCheckin = collect($reserva->transacoes ?? []);
                    $totalPagoCheckin = (float) $pagamentosCheckin
                        ->filter(fn($p) => strtolower((string) ($p->status ?? '')) === 'pago')
                        ->sum(fn($p) => (float) ($p->valor_total ?? $p->valor_unitario ?? $p->valor ?? 0));
                    $baseContratoCheckin = (float) (($reserva->valor_periodo_contrato ?? null) ?: ($reserva->valor_plano ?? 0));
                    $totalReservaCheckin = $baseContratoCheckin > 0
                        ? $baseContratoCheckin + (float) ($reserva->valor_opcionais ?? 0)
                        : (float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? 0);
                    $hasPendingPayment = false; // Removido bloqueio do wizard de check-in por pagamentos pendentes
                ?>

                
                <div class="step-indicator" role="tablist">
                    <?php
                        $steps = [
                            ['label' => 'Dados do Veiculo', 'icon' => 'fas fa-car'],
                            ['label' => 'Fotos do Veiculo', 'icon' => 'fas fa-camera'],
                            ['label' => 'Estado do Veiculo', 'icon' => 'fas fa-search'],
                            ['label' => 'Observacoes', 'icon' => 'fas fa-comment-alt'],
                        ];
                    ?>
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="step-btn <?php echo e($i === 0 ? 'active' : ''); ?>" data-step-target="<?php echo e($i + 1); ?>">
                            <span class="step-num"><?php echo e($i + 1); ?></span>
                            <span class="step-label-text">
                                <i class="<?php echo e($step['icon']); ?>"></i>
                                <?php echo e($step['label']); ?>

                            </span>
                        </button>
                        <?php if($i < count($steps) - 1): ?>
                            <div class="step-divider"></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <section class="wizard-step active" data-step="1">
                    <div class="rw-step-content-header">
                        <div class="rw-step-content-icon"><i class="fas fa-car"></i></div>
                        <div>
                            <h5>Dados do Veiculo</h5>
                            <p>Selecione o veiculo, registre a quilometragem atual e defina data e hora de saida.</p>
                        </div>
                    </div>

                    <form id="form_dados_veiculos">

                        <?php if($contratoExistente && $contratoExistente->assinado == 'S'): ?>
                        <div class="alert alert-danger mb-3" role="alert">
                            <i class="fas fa-lock mr-2"></i>
                            <strong>Contrato assinado.</strong> Os campos estão bloqueados para edição. Para modificar, acesse a reserva, exclua o contrato e crie um novo.
                        </div>
                        <?php endif; ?>

                        
                        <div id="upgrade-banner" class="alert alert-warning d-none mb-3" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-arrow-circle-up fa-2x mr-3 text-warning mt-1"></i>
                                <div>
                                    <strong>Upgrade por Indisponibilidade de Categoria</strong>
                                    <p class="mb-1 small">
                                        Nao ha veiculos disponiveis na categoria reservada
                                        (<span id="upgrade-grupo-original"></span>).
                                        Selecionamos abaixo os veiculos livres no periodo desta reserva.
                                        Somente os marcados como <strong>upgrade</strong> ficam habilitados.
                                    </p>
                                    <div class="small mb-2" style="opacity:.9">
                                        <span class="mr-2"><strong>Legenda:</strong></span>
                                        <span class="mr-3">⬆ elegivel (upgrade)</span>
                                        <span>⬇ inferior (bloqueado)</span>
                                    </div>
                                    <div class="form-group mb-1">
                                        <label class="small mb-0">Motivo do upgrade</label>
                                        <input type="text" id="motivo_upgrade_input" class="form-control form-control-sm"
                                               value="Indisponibilidade na categoria original" maxlength="500">
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="upgrade_mostrar_bloqueados">
                                        <label class="form-check-label small" for="upgrade_mostrar_bloqueados">
                                            Mostrar veiculos inferiores (bloqueados)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-car"></i>
                                <div>
                                    <strong>Identificacao do Veiculo</strong>
                                    <span>Clique na lupa para buscar os veículos disponíveis no período desta reserva na locadora.</span>
                                </div>
                            </div>
                            <?php
                                $ciVeiculoId  = old('veiculo',         $contratoExistente?->veiculo_id ?? '');
                                $ciVeiculoTxt = old('veiculo_display',
                                    $contratoExistente?->veiculo
                                        ? ($contratoExistente->veiculo->nome . ($contratoExistente->veiculo->placa_licenca ? ' (' . $contratoExistente->veiculo->placa_licenca . ')' : ''))
                                        : ''
                                );
                                $reqEstacao = $reserva->estacaoRetirada?->nome ?? 'Estação #' . $reserva->estacao_retirada_id;
                                $reqGrupo = $grupoCheckinLabel ?? $reserva->grupoFrota?->nome ?? ($reserva->grupo_frota_id ? 'Grupo #' . $reserva->grupo_frota_id : '—');
                                $reqModelo = $reserva->modelos?->nome ?? '—';
                            ?>
                            <div class="row">
                                <div class="col-md-6" id="veiculo_col">
                                    <label for="veiculo_display">Veiculo <span class="req">*</span></label>
                                    <div class="checkin-veiculo-row">
                                        <div class="input-group checkin-veiculo-group">
                                            <input type="hidden" id="veiculo" name="veiculo" value="<?php echo e($ciVeiculoId); ?>">
                                            <input type="text" id="veiculo_display" class="form-control form-control-sm" readonly placeholder="Clique na lupa para selecionar" value="<?php echo e($ciVeiculoTxt); ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary checkin-veiculo-btn" id="btnBuscarVeiculoCheckin" title="Buscar veículo disponível"><i class="fas fa-search"></i></button>
                                                <button type="button" class="btn btn-outline-danger d-none checkin-veiculo-btn" id="btnLimparVeiculoCheckin" title="Limpar veículo"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-info btn-sm checkin-veiculo-diagnostico" id="btnDiagnostico" title="Ver diagnóstico de disponibilidade">
                                            <i class="fas fa-stethoscope"></i> Diagnóstico
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-wrap gap-2 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.78rem;align-items:center;">
                                        <span><strong>Estação:</strong> <?php echo e($reqEstacao); ?></span>
                                        <span class="text-muted">|</span>
                                        <span>
                                            <strong><?php echo e(!empty($isMotoristaAppCheckin) ? 'Plano:' : 'Grupo:'); ?></strong> <?php echo e($reqGrupo); ?>

                                            <button type="button" class="btn btn-sm btn-link p-0 ml-1 text-primary" data-toggle="modal" data-target="#modalModelosPlano" title="Ver todos os modelos para este <?php echo e(!empty($isMotoristaAppCheckin) ? 'plano' : 'grupo'); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </span>

                                        <span class="text-muted">|</span>
                                        <span><strong>Período:</strong> <?php echo e($reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') : '—'); ?> → <?php echo e($reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') : '—'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        
                        <div class="modal fade" id="modalModelosPlano" tabindex="-1" role="dialog" aria-labelledby="modalModelosPlanoLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 1140px;">
                                <div class="modal-content" style="border-radius:18px;border:1px solid #dbe4ee;box-shadow:0 10px 30px rgba(0,0,0,0.15);">
                                    <div class="modal-header" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;border-top-left-radius:17px;border-top-right-radius:17px;padding:14px 20px;">
                                        <h5 class="modal-title" id="modalModelosPlanoLabel" style="font-weight:800;font-size:1.05rem;">
                                            <i class="fas fa-car mr-2"></i> Modelos do <?php echo e(!empty($isMotoristaAppCheckin) ? 'Plano' : 'Grupo'); ?> (<?php echo e($reqGrupo); ?>)
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:0.9;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body" style="padding:16px 20px;max-height:70vh;overflow-y:auto;">
                                        <div class="mb-3">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0" style="border-color:#cbd5e1;"><i class="fas fa-search text-muted"></i></span>
                                                </div>
                                                <input type="text" id="filtroModeloPlanoInput" class="form-control border-left-0" style="border-color:#cbd5e1;" placeholder="Filtrar modelo por nome ou marca..." oninput="filtrarModelosPlano(this.value)">
                                            </div>
                                        </div>

                                        <?php
                                            $listaModelos = !empty($modelos) && count($modelos) > 0 ? $modelos : (!empty($veiculosDisponiveis) ? $veiculosDisponiveis->pluck('modelo')->filter()->unique('id') : collect());
                                            $modelosPorMarca = $listaModelos->groupBy(function($m) {
                                                return $m->marcas?->nome ?? $m->marca?->nome ?? $m['marca']['nome'] ?? 'Outras Marcas';
                                            });
                                        ?>

                                        <div id="containerModelosPlano">
                                            <?php $__empty_1 = true; $__currentLoopData = $modelosPorMarca; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nomeMarca => $itensMarca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="marca-group-block mb-3" data-marca="<?php echo e(strtolower($nomeMarca)); ?>">
                                                    <div class="d-flex align-items-center mb-2 pb-1" style="border-bottom: 2px solid #e2e8f0;">
                                                        <i class="fas fa-industry text-primary mr-2" style="font-size:0.85rem;"></i>
                                                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size:0.95rem;"><?php echo e($nomeMarca); ?></h6>
                                                        <span class="badge badge-secondary ml-2 px-2 py-1" style="font-size:.7rem;border-radius:10px;"><?php echo e(count($itensMarca)); ?></span>
                                                    </div>
                                                    <div class="row no-gutters" style="margin: -4px;">
                                                        <?php $__currentLoopData = $itensMarca; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php
                                                                $nomeModelo = $m->nome ?? $m['nome'] ?? 'Modelo';
                                                            ?>
                                                            <div class="col-6 col-sm-4 col-md-3 p-1 modelo-item-card" data-nome="<?php echo e(strtolower($nomeModelo)); ?>" data-marca="<?php echo e(strtolower($nomeMarca)); ?>">
                                                                <div class="p-2 rounded border text-center h-100" style="background:#fff;border-color:#e2e8f0!important;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                                                                    <div class="font-weight-bold text-truncate" style="color:#0f172a;font-size:0.88rem;" title="<?php echo e($nomeModelo); ?>">
                                                                        <?php echo e($nomeModelo); ?>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                                                    Nenhum modelo elegível cadastrado para este <?php echo e(!empty($isMotoristaAppCheckin) ? 'plano' : 'grupo'); ?>.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:10px 20px;">
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        function filtrarModelosPlano(termo) {
                            termo = (termo || '').toLowerCase().trim();
                            const container = document.getElementById('containerModelosPlano');
                            if (!container) return;

                            const blocos = container.querySelectorAll('.marca-group-block');
                            blocos.forEach(bloco => {
                                let modelosVisiveis = 0;
                                const cards = bloco.querySelectorAll('.modelo-item-card');
                                cards.forEach(card => {
                                    const nome = card.getAttribute('data-nome') || '';
                                    const marca = card.getAttribute('data-marca') || '';
                                    if (!termo || nome.includes(termo) || marca.includes(termo)) {
                                        card.style.display = '';
                                        modelosVisiveis++;
                                    } else {
                                        card.style.display = 'none';
                                    }
                                });
                                bloco.style.display = modelosVisiveis > 0 ? '' : 'none';
                            });
                        }
                        </script>

                        
                        <?php if (isset($component)) { $__componentOriginalcdba5f1885e71445158c7d13c4efb649 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdba5f1885e71445158c7d13c4efb649 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.veiculo.busca-avancada-modal','data' => ['prefix' => 'checkin','title' => 'Veículos Disponíveis no Período','buscarUrl' => ''.e(route('reserva.checkin.veiculosModal', ['codigo' => $codigoBreadcrumb ?? $reserva->id])).'','triggerId' => 'btnBuscarVeiculoCheckin','hiddenInputId' => 'veiculo','displayInputId' => 'veiculo_display','clearBtnId' => 'btnLimparVeiculoCheckin','autoFillKmsSelector' => '#quilometragem','autoFillNivelSelector' => '#combustivel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('veiculo.busca-avancada-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['prefix' => 'checkin','title' => 'Veículos Disponíveis no Período','buscar-url' => ''.e(route('reserva.checkin.veiculosModal', ['codigo' => $codigoBreadcrumb ?? $reserva->id])).'','trigger-id' => 'btnBuscarVeiculoCheckin','hidden-input-id' => 'veiculo','display-input-id' => 'veiculo_display','clear-btn-id' => 'btnLimparVeiculoCheckin','auto-fill-kms-selector' => '#quilometragem','auto-fill-nivel-selector' => '#combustivel']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcdba5f1885e71445158c7d13c4efb649)): ?>
<?php $attributes = $__attributesOriginalcdba5f1885e71445158c7d13c4efb649; ?>
<?php unset($__attributesOriginalcdba5f1885e71445158c7d13c4efb649); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcdba5f1885e71445158c7d13c4efb649)): ?>
<?php $component = $__componentOriginalcdba5f1885e71445158c7d13c4efb649; ?>
<?php unset($__componentOriginalcdba5f1885e71445158c7d13c4efb649); ?>
<?php endif; ?>

                        
                        <div class="modal fade" id="modalDiagnostico" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable diag-modal-dialog" role="document">
                                <div class="modal-content diag-modal">
                                    <div class="diag-modal__header">
                                        <div class="diag-modal__title-wrap">
                                            <div class="diag-modal__icon">
                                                <i class="fas fa-stethoscope"></i>
                                            </div>
                                            <div>
                                                <div class="diag-modal__eyebrow">Check-in</div>
                                                <h5 class="diag-modal__title">Diagnóstico de Disponibilidade</h5>
                                                <p class="diag-modal__sub">Analisa estação, agenda, status e conflitos antes de liberar o veículo.</p>
                                            </div>
                                        </div>
                                        <button type="button" class="diag-modal__close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="diag-modal__body">
                                        <div class="diag-modal__intro">
                                            <div class="diag-modal__intro-chip">
                                                <i class="fas fa-info-circle"></i>
                                                Reserva #<?php echo e($codigoBreadcrumb ?? $reserva->id); ?>

                                            </div>
                                            <div class="diag-modal__intro-text">
                                                Use os filtros para comparar os veículos disponíveis e identifique rapidamente o motivo de cada bloqueio.
                                            </div>
                                        </div>
                                        <div class="diag-modal__result" id="diagnosticoResultado">
                                            <div class="diag-loading">
                                                <div class="diag-loading__icon">
                                                    <i class="fas fa-stethoscope fa-spin"></i>
                                                </div>
                                                <div class="diag-loading__title">Analisando veículos...</div>
                                                <div class="diag-loading__text">Carregando regras de disponibilidade e conflitos.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $__env->startPush('js'); ?>
                            <script>
                                (function () {
                                    // Auto-search quando o modal abre
                                    var modalEl = document.getElementById('checkin_modalBuscarVeiculo');
                                    if (modalEl) {
                                        modalEl.addEventListener('shown.bs.modal', function () {
                                            var btnBuscar = document.getElementById('checkin_btnAplicarFiltroVeiculo');
                                            if (btnBuscar) btnBuscar.click();
                                        });
                                        // Bootstrap 3/AdminLTE usa jQuery events
                                        if (window.$) {
                                            window.$('#checkin_modalBuscarVeiculo').on('shown.bs.modal', function () {
                                                var btnBuscar = document.getElementById('checkin_btnAplicarFiltroVeiculo');
                                                if (btnBuscar) btnBuscar.click();
                                            });
                                        }
                                    }

                                    // Ao selecionar veículo: preenche kms (min = kms do veículo) e combustível
                                    document.addEventListener('veiculo:selecionado', function (ev) {
                                        var d = ev.detail || {};
                                        var kms = d.kms || '';
                                        var quil = document.getElementById('quilometragem');

                                        if (kms && quil) {
                                            var kmsNum = Number(kms);
                                            quil.min = String(kmsNum);
                                            quil.value = kmsNum;
                                        }

                                        var combEl = document.getElementById('combustivel');
                                        if (combEl && d.nivel) {
                                            var found = Array.from(combEl.options).find(function (o) {
                                                return String(o.value).toLowerCase() === String(d.nivel).toLowerCase()
                                                    || String(o.text).toLowerCase() === String(d.nivel).toLowerCase();
                                            });
                                            if (found) combEl.value = found.value;
                                        }

                                        var dataEl = document.getElementById('data_saida');
                                        var horaEl = document.getElementById('hora_saida');
                                        var now = new Date();
                                        if (dataEl && !dataEl.value) {
                                            dataEl.value = now.getFullYear() + '-'
                                                + String(now.getMonth() + 1).padStart(2, '0') + '-'
                                                + String(now.getDate()).padStart(2, '0');
                                        }
                                        if (horaEl && !horaEl.value) {
                                            horaEl.value = String(now.getHours()).padStart(2, '0') + ':'
                                                + String(now.getMinutes()).padStart(2, '0');
                                        }
                                    });

                                    // Impede que o usuário diminua o valor de quilometragem abaixo do mínimo
                                    var quilEl = document.getElementById('quilometragem');
                                    if (quilEl) {
                                        quilEl.addEventListener('input', function () {
                                            var min = Number(this.min);
                                            if (min && Number(this.value) < min) {
                                                this.value = min;
                                            }
                                        });
                                    }
                                 }());
                            </script>
                             <script>
                                 var btnDiag = document.getElementById('btnDiagnostico');
                                 if (btnDiag) {
                                 btnDiag.addEventListener('click', function () {
                                     if (typeof renderDiagnostico === 'function') { renderDiagnostico(); }
                                     else {
                                          window.renderDiagnostico = function() {
                                             var modal = $('#modalDiagnostico');
                                             modal.modal('show');
                                             document.getElementById('diagnosticoResultado').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><p class="mt-3 text-muted">Analisando veiculos...</p></div>';
                                             fetch('<?php echo e(route('reserva.checkin.diagnostico', ['codigo' => $codigoBreadcrumb ?? $reserva->id])); ?>', { headers: { 'Accept': 'application/json' } })
                                                 .then(function (r) { return r.json(); })
                                                 .then(function (data) {
                                                     document.getElementById('diagnosticoResultado').innerHTML = data.html || '<div class="alert alert-danger">Erro ao carregar.</div>';
                                                 })
                                                 .catch(function (err) { console.error('Diagnostico error:', err); document.getElementById('diagnosticoResultado').innerHTML = '<div class="alert alert-danger text-center py-5"><i class="fas fa-exclamation-triangle fa-3x mb-3 d-block"></i><strong>Erro ao carregar diagnostico.</strong><br><span class="text-muted">' + (err.message || 'Tente novamente.') + '</span></div>'; });
                                        };
                                        renderDiagnostico();
                                    }
                                    window.filtrarDiagnostico = function() {
                                        var filtros = {};
                                        document.querySelectorAll('.diag-filtro').forEach(function(el) {
                                            var col = el.dataset.col;
                                            var val = el.value.trim().toLowerCase();
                                            if (val) filtros[col] = val;
                                        });
                                        document.querySelectorAll('.diag-card').forEach(function(card) {
                                            var show = true;
                                            for (var col in filtros) {
                                                var val = filtros[col];
                                                if (col === 'nome' || col === 'placa' || col === 'modelo') {
                                                    var campo = (card.dataset[col] || '').toLowerCase();
                                                    if (!campo.includes(val)) { show = false; break; }
                                                } else {
                                                    var campo = card.dataset[col] || '0';
                                                    if (val === 'ok' && campo !== '1') { show = false; break; }
                                                    if (val === 'fail' && campo !== '0') { show = false; break; }
                                                    if (val === '1' && campo !== '1') { show = false; break; }
                                                    if (val === '0' && campo !== '0') { show = false; break; }
                                                }
                                            }
                                            card.style.display = show ? '' : 'none';
                                        });
                                    };
                                    window.limparFiltrosDiag = function() {
                                        document.querySelectorAll('.diag-filtro').forEach(function(el) { el.value = ''; });
                                        filtrarDiagnostico();
                                    };
                                    window.toggleFiltrosAvancados = function() {
                                        var el = document.getElementById('diagFiltrosAvancados');
                                        var btn = document.getElementById('btnFiltroAvancado');
                                        if (!el || !btn) return;
                                        var aberto = el.style.display !== 'none';
                                        el.style.display = aberto ? 'none' : '';
                                        btn.classList.toggle('active', !aberto);
                                    };
                                });
                                }
                            </script>
                        <?php $__env->stopPush(); ?>

                        
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-tachometer-alt"></i>
                                <div>
                                    <strong>Condicao e Saida</strong>
                                    <span>Quilometragem atual, nivel de combustivel e data/hora de saida</span>
                                </div>
                            </div>
                            <?php
                                $ciKm        = old('quilometragem', $contratoExistente?->quilometragem_atual ?? '');
                                $ciCombust   = old('combustivel',   $contratoExistente?->nivel_combustivel ?? '');
                                $ciDataSaida = old('data_saida',    $contratoExistente?->data_saida ?? '');
                                $ciHoraSaida = old('hora_saida',    $contratoExistente?->hora_saida ? substr($contratoExistente->hora_saida, 0, 5) : '');
                                $niveis = ['Reserva','1/8','1/4','3/8','1/2','5/8','3/4','7/8','Cheio'];
                            ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="quilometragem">Quilometragem Atual <span class="req">*</span></label>
                                    <input type="number" required class="form-control form-control-sm" id="quilometragem" name="quilometragem"
                                           placeholder="Ex: 45200"
                                           value="<?php echo e($ciKm); ?>"
                                           min="<?php echo e($ciKm ?: 0); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="combustivel">Nivel de Combustivel <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" id="combustivel" required name="combustivel">
                                        <?php $__currentLoopData = $niveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nivel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option <?php echo e($ciCombust === $nivel ? 'selected' : ''); ?>><?php echo e($nivel); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="data_saida">Data de Saida <span class="req">*</span></label>
                                    <input type="date" class="form-control form-control-sm" required id="data_saida" name="data_saida"
                                           value="<?php echo e($ciDataSaida); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="hora_saida">Hora de Saida <span class="req">*</span></label>
                                    <input type="time" class="form-control form-control-sm" required id="hora_saida" name="hora_saida"
                                           value="<?php echo e($ciHoraSaida); ?>">
                                </div>
                            </div>
                        </div>

                    </form>
                </section>

                
                <section class="wizard-step" data-step="2">
                    

                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-images"></i>
                            <div>
                                <strong>Fotos Obrigatorias</strong>
                                <span>Direita, esquerda, frente, tras, velocimetro e combustivel</span>
                            </div>
                        </div>

                        <?php
                            $fotos = [
                                ['id' => 'foto_direita',     'label' => 'Direita',              'col' => 'foto_direita_path'],
                                ['id' => 'foto_esquerda',    'label' => 'Esquerda',             'col' => 'foto_esquerda_path'],
                                ['id' => 'foto_frente',      'label' => 'Frente',               'col' => 'foto_frente_path'],
                                ['id' => 'foto_tras',        'label' => 'Tras',                 'col' => 'foto_tras_path'],
                                ['id' => 'foto_velocimetro', 'label' => 'Velocimetro',          'col' => 'foto_velocimetro_path'],
                                ['id' => 'foto_combustivel', 'label' => 'Medidor de Combustivel','col' => 'foto_combustivel_path'],
                            ];
                        ?>

                        <div class="row">
                            <?php $__currentLoopData = $fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $fotoExistente = $contratoExistente?->{$f['col']} ?? null;
                                    $fotoUrl = $fotoExistente ? asset('storage/' . $fotoExistente) : null;
                                ?>
                                <div class="col-md-4">
                                    <div class="ci-photo-card">
                                        <label for="<?php echo e($f['id']); ?>">
                                            <?php echo e($f['label']); ?>

                                            <?php if(!$contratoExistente): ?><span class="req">*</span><?php endif; ?>
                                        </label>
                                        <input type="file" class="form-control form-control-sm" id="<?php echo e($f['id']); ?>" name="<?php echo e($f['id']); ?>" accept="image/*"
                                               <?php if(!$contratoExistente): ?> required <?php endif; ?>>
                                        <div class="ci-photo-preview" data-preview-for="<?php echo e($f['id']); ?>">
                                            <?php if($fotoUrl): ?>
                                                <img src="<?php echo e($fotoUrl); ?>" alt="<?php echo e($f['label']); ?>" style="max-width:100%;max-height:160px;border-radius:6px;">
                                            <?php else: ?>
                                                <span class="ci-photo-preview__placeholder">
                                                    <i class="far fa-image mr-1"></i>Sem imagem
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="ci-hint">
                                            <?php if($fotoUrl): ?>
                                                Foto atual exibida. Selecione um novo arquivo para substituir.
                                            <?php else: ?>
                                                Max 8MB. Formatos: JPG/PNG/WEBP.
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </section>

                <section class="wizard-step" data-step="3">
                    

                    
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-car-crash"></i>
                            <div>
                                <strong>Vistas do Veiculo</strong>
                                <span>Clique na vista onde o dano se encontra para registra-lo</span>
                            </div>
                        </div>
                        <div class="car-views-grid">
                            <?php
                                $vistas = [
                                    ['nome' => 'DIREITA',   'arquivo' => 'direita.jpg',   'icon' => 'fas fa-arrow-right'],
                                    ['nome' => 'ESQUERDA',  'arquivo' => 'esquerda.jpg',  'icon' => 'fas fa-arrow-left'],
                                    ['nome' => 'CIMA',      'arquivo' => 'cima.jpg',       'icon' => 'fas fa-arrow-up'],
                                    ['nome' => 'FRENTE',    'arquivo' => 'frente.jpg',     'icon' => 'fas fa-dot-circle'],
                                    ['nome' => 'ATRÁS',     'arquivo' => 'atras.jpg',      'icon' => 'fas fa-circle'],
                                ];
                            ?>
                            <?php $__currentLoopData = $vistas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vista): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="car-view-card" onclick="setNomeDano('<?php echo e($vista['nome']); ?>')" data-toggle="modal" data-target="#detalhesDanoModal">
                                    <div class="car-view-card__img">
                                        <img src="<?php echo e(asset('assets/imgs/carro/' . $vista['arquivo'])); ?>" alt="<?php echo e($vista['nome']); ?>">
                                        <div class="car-view-card__overlay">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Registrar dano</span>
                                        </div>
                                    </div>
                                    <div class="car-view-card__label">
                                        <i class="<?php echo e($vista['icon']); ?>"></i>
                                        <?php echo e($vista['nome']); ?>

                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div class="ci-damages-grid">
                        <div class="ci-block ci-block--no-mb">
                            <div class="ci-block-label">
                                <i class="fas fa-history"></i>
                                <div>
                                    <strong>Histórico no Veículo</strong>
                                    <span>Avarias de outras reservas / correções registradas</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Correção</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_existentes">
                                        <tr>
                                            <td colspan="4" class="damage-empty">
                                                <i class="fas fa-check-circle"></i>
                                                Selecione um veículo para carregar os danos
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="ci-block ci-block--no-mb ci-block--amber">
                            <div class="ci-block-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Danos Nesta Reserva</strong>
                                    <span>Avarias registradas no check-in ou nesta devolução</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Tipo</th>
                                            <th>Custo Est.</th>
                                            <th>Imagem</th>
                                            <th style="width:50px;">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_detectados">
                                        <tr>
                                            <td colspan="6" class="damage-empty">
                                                <i class="fas fa-shield-alt"></i>
                                                Nenhum dano detectado ainda
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="contrato_id" value="<?php echo e($contratoExistente->id ?? ''); ?>">
                    <script>window.danosModoLocal = true; window.danosCheckin = [];</script>
                    <?php echo $__env->make('reserva.components.modal_dano_checkin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </section>

                
                <section class="wizard-step" data-step="4">
                    

                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Notas e Observacoes na Saida</strong>
                                <span>Informacoes que ficam registradas no contrato</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="notas_saida">Notas na Saida</label>
                                <textarea class="form-control form-control-sm" id="notas_saida" name="notas_saida" rows="5" placeholder="Ex: Cliente informou arranhao no para-choque traseiro..."><?php echo e(old('notas_saida', $contratoExistente?->notas_saida ?? '')); ?></textarea>
                                <small class="ci-hint">Notas visíveis no contrato gerado.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="observacoes_saida">Observacoes na Saida</label>
                                <textarea class="form-control form-control-sm" id="observacoes_saida" name="observacoes_saida" rows="5" placeholder="Ex: Veiculo entregue com tanque cheio..."><?php echo e(old('observacoes_saida', $contratoExistente?->observacoes_saida ?? '')); ?></textarea>
                                <small class="ci-hint">Observacoes internas do operador.</small>
                            </div>
                        </div>
                    </div>

                    
                    <?php if($contratoExistente && $contratoExistente->assinado == 'S'): ?>
                    
                    <div class="ci-summary-card" style="background:linear-gradient(135deg,#fff5f5 0%,#fee2e2 100%);border-color:#fca5a5;">
                        <div class="ci-summary-card__icon" style="background:linear-gradient(135deg,#ef4444,#b91c1c);">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Contrato Assinado — Edição Bloqueada</strong>
                            <p>Este contrato já foi assinado em <em><?php echo e(\Carbon\Carbon::parse($contratoExistente->data_assinatura)->format('d/m/Y H:i')); ?></em> e não pode ser alterado. Para criar um novo contrato, acesse a página da reserva, exclua o contrato atual e recrie.</p>
                        </div>
                        <a href="<?php echo e(route('reserva.show', $codigoBreadcrumb ?? $reserva->id)); ?>" class="ci-btn-create" style="background:linear-gradient(135deg,#ef4444,#b91c1c);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            <i class="fas fa-arrow-left"></i>
                            Voltar para a Reserva
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="ci-summary-card">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-<?php echo e($contratoExistente ? 'edit' : 'rocket'); ?>"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <?php if($contratoExistente): ?>
                                <strong>Salvar alterações no contrato?</strong>
                                <p>Verifique se todos os dados estão corretos. Ao clicar em <em>Salvar Contrato</em> as alterações serão registradas e você será redirecionado para a reserva.</p>
                            <?php else: ?>
                                <strong>Pronto para criar o contrato?</strong>
                                <p>Verifique se todos os dados dos Passos 1 e 2 estao preenchidos. Ao clicar em <em>Criar Contrato</em> o contrato sera gerado e voce sera redirecionado para a reserva.</p>
                            <?php endif; ?>
                        </div>
                        <?php if(!$hasPendingPayment): ?>
                            <button type="button" class="ci-btn-create" id="wizardCreateBtn">
                                <i class="fas fa-<?php echo e($contratoExistente ? 'save' : 'file-contract'); ?> mr-1"></i>
                                <?php echo e($contratoExistente ? 'Salvar Contrato' : 'Criar Contrato'); ?>

                            </button>
                        <?php else: ?>
                            <button type="button" class="ci-btn-create" id="wizardCreateBtn" disabled title="Existem pagamentos pendentes.">
                                <i class="fas fa-<?php echo e($contratoExistente ? 'save' : 'file-contract'); ?> mr-1"></i>
                                <?php echo e($contratoExistente ? 'Salvar Contrato' : 'Criar Contrato'); ?>

                            </button>

                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </section>

                
                <div class="wizard-actions">
                    <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </button>
                    <div class="wizard-actions-right">
                        <?php if(!$hasPendingPayment): ?>
    <button type="button" class="btn-action btn-primary" id="wizardNext">
        Continuar<i class="fas fa-arrow-right ml-1"></i>
    </button>
<?php else: ?>
    <button type="button" class="btn-action btn-primary" id="wizardNext" disabled title="Existem pagamentos pendentes.">
        Continuar<i class="fas fa-arrow-right ml-1"></i>
    </button>
    
<?php endif; ?>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/select2.min.css')); ?>">
<style>
    <?php echo $__env->make('reserva.partials.vistoria_reserva_estilos_inner', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    /* ── Step content header (padrão add-edit) ── */
    .rw-step-content-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        background: #f8faff;
        border: 1px solid var(--c-border);
        border-left: 4px solid var(--c-indigo);
        border-radius: 12px;
        margin-bottom: 1rem;
    }
    .rw-step-content-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: color-mix(in srgb, var(--c-indigo) 12%, #fff 88%);
        color: var(--c-indigo);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .rw-step-content-icon--blue  { background: #eff6ff; color: #2563eb; }
    .rw-step-content-icon--amber { background: #fffbeb; color: #f59e0b; }
    .rw-step-content-icon--green { background: #ecfdf5; color: #10b981; }
    .rw-step-content-header:has(.rw-step-content-icon--amber) { border-left-color: var(--c-amber); background: #fffdf5; }
    .rw-step-content-header:has(.rw-step-content-icon--green) { border-left-color: var(--c-green); background: #f0fdf9; }
    .rw-step-content-header h5 {
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--c-ink);
        margin: 0 0 2px;
        letter-spacing: -0.01em;
    }
    .rw-step-content-header p {
        font-size: 0.78rem;
        color: var(--c-slate);
        margin: 0;
    }
    .ci-photo-card {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 14px;
        background: #fff;
    }
    .ci-photo-preview {
        margin-top: 10px;
        height: 130px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px dashed rgba(0,0,0,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .ci-photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .ci-photo-preview__placeholder {
        color: rgba(0,0,0,.55);
        font-size: .9rem;
    }

    .checkin-veiculo-row {
        display: flex;
        align-items: stretch;
        gap: 8px;
    }
    .checkin-veiculo-group {
        flex: 1 1 auto;
    }
    .checkin-veiculo-group .form-control {
        height: 38px;
    }
    .checkin-veiculo-group .input-group-append .btn,
    .checkin-veiculo-diagnostico {
        height: 38px;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .checkin-veiculo-diagnostico {
        white-space: nowrap;
    }

    /* ── Modal diagnóstico ── */
    .diag-modal-dialog {
        max-width: 1180px;
    }
    .diag-modal {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }
    .diag-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 22px;
        color: #fff;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 45%, #0f4c81 100%);
        border-bottom: 1px solid rgba(255,255,255,.12);
    }
    .diag-modal__title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .diag-modal__icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.18);
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.12);
    }
    .diag-modal__eyebrow {
        font-size: .68rem;
        letter-spacing: .12em;
        text-transform: uppercase;
        opacity: .72;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .diag-modal__title {
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.1;
    }
    .diag-modal__sub {
        margin: 4px 0 0;
        font-size: .82rem;
        opacity: .82;
    }
    .diag-modal__close {
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 12px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 1.7rem;
        line-height: 1;
        opacity: .9;
        transition: background .15s ease, transform .15s ease, opacity .15s ease;
    }
    .diag-modal__close:hover {
        background: rgba(255,255,255,.24);
        opacity: 1;
        transform: translateY(-1px);
    }
    .diag-modal__body {
        padding: 18px 20px 20px;
    }
    .diag-modal__intro {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #dbe7f5;
        background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
    }
    .diag-modal__intro-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .10);
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .diag-modal__intro-text {
        font-size: .84rem;
        color: #475569;
        font-weight: 500;
    }
    .diag-modal__result {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        padding: 16px;
        min-height: 240px;
    }
    .diag-loading {
        min-height: 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #475569;
    }
    .diag-loading__icon {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 12px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.6);
    }
    .diag-loading__title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .diag-loading__text {
        font-size: .85rem;
        margin-top: 4px;
    }

    /* ── Diagnóstico ── */
    #diagRoot {
        margin-top: 4px;
    }
    #diagRoot > .d-flex {
        gap: 8px;
    }
    #diagRoot .diag-card {
        width: calc(25% - 9px);
        flex: 0 0 calc(25% - 9px);
        box-sizing: border-box;
        border-radius: 16px;
        overflow: hidden;
        transition: box-shadow .2s, transform .2s;
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    #diagRoot .diag-card:hover {
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        transform: translateY(-2px);
    }
    #diagRoot .diag-card a {
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    #diagRoot .diag-card a:hover {
        border-color: #2563eb;
    }
    #diagRoot .card-header {
        transition: background .15s;
    }
    #diagRoot .card-header:hover {
        filter: brightness(.97);
    }
    #diagRoot .collapse .card-body {
        border-top: 1px solid #e5e7eb;
    }
    #diagFiltro:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59,130,246,.15);
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="<?php echo e(asset('js/select2.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/sweetAlert.min.js')); ?>"></script>
<script>
(function () {

    const CHECKIN_MODELOS = <?php echo json_encode($checkinModelosJson ?? [], 15, 512) ?>;
    const RESERVA_MODELO_ID = <?php echo json_encode((int) ($reserva->modelo_id ?? 0), 15, 512) ?>;
    const RESERVA_MARCA_ID = <?php echo json_encode((int) ($reserva->modelos->marca_id ?? 0), 15, 512) ?>;

    function rebuildModeloSelect(marcaId) {
        const $m = $('#modelo');
        $m.empty();
        $m.append(new Option('Selecione um modelo', '', true, true));
        $m.find('option:first').prop('disabled', true);
        if (!marcaId) {
            $m.val('').trigger('change');
            return;
        }
        CHECKIN_MODELOS.filter(o => String(o.marca_id) === String(marcaId))
            .forEach(o => $m.append(new Option(o.nome, o.id)));
        $m.val('').trigger('change');
    }

    function limparVeiculosSelect() {
        const $v = $('#veiculo');
        $v.empty();
        $v.append(new Option('Selecione marca e modelo', '', true, true));
        $v.find('option:first').prop('disabled', true);
        $v.val('').trigger('change');
    }

    function carregarVeiculosDisponiveisCheckin() {
        const marcaId = $('#marca').val();
        const modeloId = $('#modelo').val();
        const reserva_id = $('#reserva_id').val();
        const $v = $('#veiculo');

        if (!marcaId || !modeloId) {
            limparVeiculosSelect();
            return;
        }

        const url = <?php echo json_encode(route('reserva.checkin.veiculosDisponiveis', ['codigo' => $codigoBreadcrumb ?? $reserva->id]), 512) ?>;
        $v.prop('disabled', true);

        $.getJSON(url, { marca_id: marcaId, modelo_id: modeloId })
                .done(function (data) {
                $v.empty();
                $v.append(new Option('Selecione um veiculo', '', true, true));
                $v.find('option:first').prop('disabled', true);
                if (!data.length) {
                    $v.append(new Option('Nenhum veiculo disponivel neste periodo', '', true, true));
                    $v.find('option:last').prop('disabled', true);
                } else {
                    data.forEach(row => {
                        const opt = new Option(row.nome + (row.placa ? ' — ' + row.placa : ''), row.id);
                        // attach useful data for JS autofill
                        if (row.quilometragem_atual !== undefined && row.quilometragem_atual !== null) opt.dataset.quilometragem = String(row.quilometragem_atual);
                        if (row.nivel_combustivel !== undefined && row.nivel_combustivel !== null) opt.dataset.nivelCombustivel = String(row.nivel_combustivel);
                        $v.append(opt);
                    });
                }
                $v.prop('disabled', false).val('').trigger('change');
            })
            .fail(function (xhr) {
                $v.prop('disabled', false);
                limparVeiculosSelect();
                const msg = xhr.responseJSON?.message || 'Erro ao carregar veiculos.';
                Swal.fire({ icon: 'error', title: msg });
            });
    }

    /* ========================
       SELECT2
    ======================== */
    $(document).ready(function () {
        $('.select2ci').select2();

        const marcaInicial = $('#marca').val();
        if (marcaInicial) {
            rebuildModeloSelect(marcaInicial);
            if (RESERVA_MODELO_ID && RESERVA_MARCA_ID && String(RESERVA_MARCA_ID) === String(marcaInicial)) {
                $('#modelo').val(String(RESERVA_MODELO_ID)).trigger('change');
                carregarVeiculosDisponiveisCheckin();
            }
        }

        $('#marca').on('change', function () {
            rebuildModeloSelect($(this).val());
            limparVeiculosSelect();
        });

        $('#modelo').on('change', function () {
            if ($(this).val()) {
                carregarVeiculosDisponiveisCheckin();
            } else {
                limparVeiculosSelect();
            }
        });

        $('#veiculo').change(function () {
            obterDanosDetectados();
            obterDanosExistentes();

            // Autofill quilometragem, combustivel e data/hora de saida
            const sel = document.getElementById('veiculo');
            const quilEl = document.getElementById('quilometragem');
            const combEl = document.getElementById('combustivel');
            const dataEl = document.getElementById('data_saida');
            const horaEl = document.getElementById('hora_saida');

            const opt = sel && sel.options && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex] : null;
            if (opt) {
                const km = opt.dataset.quilometragem ?? opt.getAttribute('data-quilometragem') ?? '';
                if (km !== '') {
                    // set value and min
                    if (quilEl) {
                        quilEl.value = km;
                        try { quilEl.min = String(km); } catch (e) {}
                    }
                }

                const nivel = opt.dataset.nivelCombustivel ?? opt.getAttribute('data-nivel-combustivel') ?? '';
                if (nivel !== '') {
                    if (combEl) {
                        // try to select option by text or value
                        let found = Array.from(combEl.options).find(o => String(o.value).toLowerCase() === String(nivel).toLowerCase() || String(o.text).toLowerCase() === String(nivel).toLowerCase());
                        if (found) combEl.value = found.value;
                    }
                } else {
                    // default to Cheio
                    if (combEl) {
                        const cheioOpt = Array.from(combEl.options).find(o => o.text.trim().toLowerCase() === 'cheio' || String(o.value).toLowerCase() === 'cheio');
                        if (cheioOpt) combEl.value = cheioOpt.value;
                    }
                }
            } else {
                // no option selected: ensure defaults
                if (combEl) {
                    const cheioOpt = Array.from(combEl.options).find(o => o.text.trim().toLowerCase() === 'cheio' || String(o.value).toLowerCase() === 'cheio');
                    if (cheioOpt) combEl.value = cheioOpt.value;
                }
            }

            // set date/time to now by default
            const now = new Date();
            if (dataEl && !dataEl.value) {
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, '0');
                const dd = String(now.getDate()).padStart(2, '0');
                dataEl.value = `${yyyy}-${mm}-${dd}`;
            }
            if (horaEl && !horaEl.value) {
                const hh = String(now.getHours()).padStart(2, '0');
                const mi = String(now.getMinutes()).padStart(2, '0');
                horaEl.value = `${hh}:${mi}`;
            }
        });

        // Prevent entering smaller quilometragem than min
        const quilInput = document.getElementById('quilometragem');
        if (quilInput) {
            quilInput.addEventListener('input', function () {
                const min = this.min ? Number(this.min) : null;
                const val = this.value ? Number(this.value) : null;
                if (min !== null && val !== null && val < min) {
                    this.value = String(min);
                }
            });
        }
    });

    /* ========================
       WIZARD STEPS
    ======================== */
    const contraModoEdicao = <?php echo e($contratoExistente ? 'true' : 'false'); ?>;

    const PHOTO_INPUT_IDS = [
        'foto_direita',
        'foto_esquerda',
        'foto_frente',
        'foto_tras',
        'foto_velocimetro',
        'foto_combustivel',
    ];

    const stepBtns   = Array.from(document.querySelectorAll('.step-btn'));
    const stepPanels = Array.from(document.querySelectorAll('.wizard-step[data-step]'));
    const prevBtn    = document.getElementById('wizardPrev');
    const nextBtn    = document.getElementById('wizardNext');
    const createBtn  = document.getElementById('wizardCreateBtn');
    let current      = 1;
    const lastStep   = stepPanels.length;

    function setupPhotoPreviews() {
        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            const preview = document.querySelector(`.ci-photo-preview[data-preview-for="${id}"]`);
            if (!input || !preview) return;

            let objectUrl = null;
            input.addEventListener('change', function () {
                input.classList.remove('is-invalid');

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (this.files && this.files[0]) {
                    objectUrl = URL.createObjectURL(this.files[0]);
                    preview.innerHTML = `<img src="${objectUrl}" alt="Preview">`;
                } else {
                    preview.innerHTML = `
                        <span class="ci-photo-preview__placeholder">
                            <i class="far fa-image mr-1"></i>Sem imagem
                        </span>
                    `;
                }
            });
        });
    }

    setupPhotoPreviews();

    function showStep(n) {
        current = Math.min(Math.max(n, 1), lastStep);

        stepPanels.forEach(panel => {
            panel.classList.toggle('active', Number(panel.dataset.step) === current);
        });

        stepBtns.forEach(btn => {
            const t = Number(btn.dataset.stepTarget);
            btn.classList.toggle('active', t === current);
            btn.classList.toggle('completed', t < current);
        });

        prevBtn.style.visibility = current === 1 ? 'hidden' : 'visible';
        nextBtn.style.display    = current === lastStep ? 'none' : 'inline-flex';
        createBtn.classList.toggle('d-none', current !== lastStep);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep1(showAlert = true) {
        const campos = document.querySelectorAll('#form_dados_veiculos input:required, #form_dados_veiculos select:required');
        const camposInvalidos = [];

        campos.forEach(campo => {
            const invalido = !campo.value || (campo.classList.contains('select2-hidden-accessible') && !$(campo).val());
            campo.classList.toggle('is-invalid', invalido);
            if (invalido) camposInvalidos.push(campo.name);
        });

        if (camposInvalidos.length > 0) {
            if (showAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos obrigatorios nao preenchidos',
                    html: `<ul style="text-align:left">${camposInvalidos.map(c => `<li>${String(c).replace(/_/g, ' ')}</li>`).join('')}</ul>`,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                    toast: false,
                });
            }
            return false;
        }

        // Verifica que um veículo foi selecionado (campo hidden `veiculo`)
        const veiculoHidden = document.getElementById('veiculo');
        const veiculoDisplay = document.getElementById('veiculo_display');
        if (!veiculoHidden || !veiculoHidden.value) {
            if (veiculoDisplay) veiculoDisplay.classList.add('is-invalid');
            if (showAlert) {
                Swal.fire({ icon: 'error', title: 'Veículo não selecionado', text: 'Selecione um veículo para prosseguir.' });
            }
            return false;
        } else {
            if (veiculoDisplay) veiculoDisplay.classList.remove('is-invalid');
        }

        // valida quilometragem minima antes de aceitar o passo
        if (!validarQuilometragemMin()) {
            return false;
        }

        return true;
    }

    // adicional: valida quilometragem minimo
    function validarQuilometragemMin() {
        const quil = document.getElementById('quilometragem');
        if (!quil) return true;
        const min = quil.min ? Number(quil.min) : null;
        const val = quil.value ? Number(quil.value) : null;
        if (min !== null && val !== null && val < min) {
            quil.classList.add('is-invalid');
            Swal.fire({ icon: 'error', title: 'Quilometragem inválida', html: `A quilometragem informada não pode ser menor que a atual (${min}).` });
            return false;
        }
        quil.classList.remove('is-invalid');
        return true;
    }

    function validateStep2Photos(showAlert = true) {
        // Em modo edição, as fotos já existem — não obriga re-upload
        if (contraModoEdicao) return true;

        const invalidos = [];

        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            const invalido = !input || !input.files || input.files.length === 0;
            if (input) input.classList.toggle('is-invalid', invalido);
            if (invalido) invalidos.push(id);
        });

        if (invalidos.length > 0) {
            if (showAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Fotos obrigatorias nao enviadas',
                    html: `<div style="text-align:left">Envie todas as fotos do Passo 2 para continuar.</div>`,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                    toast: false,
                });
            }
            return false;
        }

        return true;
    }

    function goToStep(target) {
        // Voltar nunca bloqueia; avancar exige concluir os passos anteriores.
        if (target <= current) {
            showStep(target);
            return;
        }

        if (target >= 2 && !validateStep1(true)) {
            showStep(1);
            return;
        }

        if (target >= 3 && !validateStep2Photos(true)) {
            showStep(2);
            return;
        }

        showStep(target);
    }

    stepBtns.forEach(btn => {
        btn.addEventListener('click', () => goToStep(Number(btn.dataset.stepTarget)));
    });

    prevBtn.addEventListener('click', () => showStep(current - 1));
    nextBtn.addEventListener('click', () => goToStep(current + 1));

    /* ========================
       CRIAR CONTRATO
    ======================== */
    function triggerCriarContrato() {
        if (!validateStep1(true)) {
            showStep(1);
            return;
        }
        if (!validateStep2Photos(true)) {
            showStep(2);
            return;
        }

        criarContrato();
    }

    document.getElementById('btn_criar_contrato')?.addEventListener('click', triggerCriarContrato);
    createBtn?.addEventListener('click', triggerCriarContrato);

    document.getElementById('form_dados_veiculos').addEventListener('submit', function (event) {
        event.preventDefault();
        if (!validateStep1(true)) {
            showStep(1);
            return;
        }

        // Enter no Passo 1 avanca para o Passo 2.
        goToStep(2);
    });

    function criarContrato() {
        const reserva_id = document.getElementById('reserva_id').value;
        const url = <?php echo json_encode(route('reserva.criarContratoCheckin', ['codigo' => $codigoBreadcrumb ?? $reserva->id]), 512) ?>;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const contrato = {
            marca_id:           document.getElementById('marca')?.value          || null,
            modelo_id:          document.getElementById('modelo')?.value         || null,
            veiculo_id:         document.getElementById('veiculo')?.value        || null,
            quilometragem_atual: document.getElementById('quilometragem')?.value || null,
            nivel_combustivel:  document.getElementById('combustivel')?.value    || null,
            data_saida:         document.getElementById('data_saida')?.value     || null,
            hora_saida:         document.getElementById('hora_saida')?.value     || null,
            notas_saida:        document.getElementById('notas_saida')?.value    || null,
            observacoes_saida:  document.getElementById('observacoes_saida')?.value || null,
        };

        const fd = new FormData();
        Object.entries(contrato).forEach(([k, v]) => fd.append(k, v ?? ''));
        PHOTO_INPUT_IDS.forEach(id => {
            const input = document.getElementById(id);
            if (input && input.files && input.files[0]) {
                fd.append(id, input.files[0]);
            }
        });

        // Inclui danos coletados localmente
        if (window.danosCheckin && window.danosCheckin.length) {
            fd.append('danos', JSON.stringify(window.danosCheckin));
        }
        if (window.danosCheckinFotos && window.danosCheckinFotos.length) {
            window.danosCheckinFotos.forEach(function (file, idx) {
                fd.append('danos_fotos_' + idx, file);
            });
        }

        // Se for upgrade, envia motivo_upgrade para auditoria
        const selectedOpt = document.querySelector('#veiculo option:checked');
        const isUpgrade = selectedOpt?.dataset?.upgrade === '1';
        if (isUpgrade) {
            const motivo = document.getElementById('motivo_upgrade_input')?.value || '';
            fd.append('motivo_upgrade', motivo);
        }

        const createBtnEl = document.getElementById('btn_criar_contrato');
        const wizBtnEl    = document.getElementById('wizardCreateBtn');
        [createBtnEl, wizBtnEl].forEach(b => {
            if (b) {
                b.disabled = true;
                b._originalHtml = b.innerHTML;
                b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            }
        });

        $.ajax({
            url,
            type: 'POST',
            data: fd,
            headers: { 'X-CSRF-TOKEN': csrf },
            processData: false,
            contentType: false,
            success: function (data) {
                Swal.fire({
                    icon: 'success',
                    title: contraModoEdicao ? 'Contrato atualizado com sucesso!' : 'Contrato criado com sucesso!',
                    showConfirmButton: false,
                    timer: 2500,
                    toast: true,
                    position: 'top-right',
                });
                setTimeout(() => {
                    window.location.href = <?php echo json_encode(route('reserva.show', $codigoBreadcrumb ?? $reserva->id), 512) ?>;
                }, 2600);
            },
            error: function (xhr) {
                let title = 'Erro ao criar contrato';
                let html = 'Verifique os dados e tente novamente.';

                if (xhr && xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    title = 'Validacao falhou';
                    const msgs = Object.values(xhr.responseJSON.errors).flat();
                    html = `<ul style=\"text-align:left\">${msgs.map(m => `<li>${m}</li>`).join('')}</ul>`;
                } else if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    html = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title,
                    html,
                    showConfirmButton: true,
                    confirmButtonColor: '#4338ca',
                });
                [createBtnEl, wizBtnEl].forEach(b => { if (b) { b.disabled = false; b.innerHTML = b._originalHtml || b.innerHTML; } });
            }
        });
    }

    /* ========================
       DANOS
    ======================== */
    function rowImagens(dano) {
        const imgs = dano.imagens || [];
        if (!imgs.length) return '<span class="text-muted small">—</span>';
        return imgs.map(function (i) {
            const url = i.url || (i.path ? `<?php echo e(asset('storage')); ?>/${i.path}` : null) || (i.imagem ? `<?php echo e(asset('storage')); ?>/${i.imagem}` : null);
            if (!url) return '';
            return `<a href="${url}" target="_blank" rel="noopener" title="Ver imagem"><img src="${url}" alt="foto" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;margin-right:4px;" onerror="this.outerHTML='<span class=text-danger small>Erro</span>'"></a>`;
        }).filter(Boolean).join('');
    }

    const TIPO_LABEL = { ausente:'<span class="badge badge-success">Ausente</span>', amolgadela_leve:'<span class="badge badge-warning text-dark">Amolgadela Leve</span>', amolgadela_grave:'<span class="badge badge-danger">Amolgadela Grave</span>', risco_leve:'<span class="badge badge-warning text-dark">Risco Leve</span>', risco_grave:'<span class="badge badge-danger">Risco Grave</span>' };
    function tipoBadge(tipo) { return TIPO_LABEL[tipo] || '<span class="badge badge-secondary">' + (tipo || '—') + '</span>'; }
    function formatCusto(val) { if (val === null || val === undefined || val === '') return '—'; return 'R$ ' + Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2 }); }

    function removerDanoLocal(uid) {
        if (!window.danosCheckin) return;
        var idx = -1;
        window.danosCheckin.forEach(function (d, i) { if (d._uid === uid) idx = i; });
        if (idx < 0) return;
        Swal.fire({ title: 'Remover dano?', text: 'Este dano será removido da lista.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar' }).then(function (r) {
            if (!r.isConfirmed) return;
            window.danosCheckin.splice(idx, 1);
            obterDanosDetectados();
        });
    }

    function removerDanoServidor(danoId) {
        if (!danoId) return;
        Swal.fire({ title: 'Remover dano?', text: 'Este dano será excluído permanentemente.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sim, remover', cancelButtonText: 'Cancelar' }).then(function (r) {
            if (!r.isConfirmed) return;
            $.ajax({ url: '<?php echo e(url("/admin/veiculo-danos")); ?>/' + danoId, type: 'POST', data: { _method: 'DELETE', _token: '<?php echo e(csrf_token()); ?>' }, success: function () { obterDanosDetectados(); obterDanosExistentes(); Swal.fire({ icon: 'success', title: 'Removido!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 }); }, error: function () { Swal.fire({ icon: 'error', title: 'Erro ao remover' }); } });
        });
    }

    function obterDanosDetectados() {
        const tbody = document.getElementById('danos_detectados');
        tbody.innerHTML = '';

        var allDanos = [];

        if (window.danosCheckin && window.danosCheckin.length) {
            allDanos = allDanos.concat(window.danosCheckin);
        }

        const veiculo_id = document.getElementById('veiculo')?.value;
        if (veiculo_id) {
            $.ajax({
                url: "<?php echo e(route('dano.obterDanosDetectados')); ?>",
                type: 'GET',
                data: { veiculo_id, codigo: '<?php echo e($codigoBreadcrumb ?? $codigo ?? $reserva->id); ?>', reserva_id: document.getElementById('reserva_id')?.value },
                async: false,
                success: function (data) { allDanos = allDanos.concat(data); }
            });
        }

        if (!allDanos.length) { tbody.innerHTML = '<tr><td colspan="6" class="damage-empty"><i class="fas fa-shield-alt"></i> Nenhuma avaria nesta reserva</td></tr>'; return; }
        allDanos.forEach(function (dano, idx) {
            var localLabel = '<strong>' + (dano.localizacao || dano.local_dano || dano.local || '—') + '</strong>';
            var cat = dano.categoria || dano.categoria_nome || (dano.categoria_id ? 'Cat#' + dano.categoria_id : '');
            var locProb = dano.localizacao_problema || '';
            if (cat || locProb) localLabel += '<br><small class="text-muted">' + (cat || '—') + ' · ' + (locProb || '—') + '</small>';
            var descricao = dano.descricao || dano.observacoes || '<span class="text-muted">—</span>';
            if (dano.quilometragem_veiculo) descricao += '<small class="text-muted d-block mt-1"><i class="fas fa-tachometer-alt mr-1"></i>KM ' + Number(dano.quilometragem_veiculo).toLocaleString('pt-BR') + '</small>';
            var isLocal = dano._local === true;
            var uid = isLocal ? Math.random().toString(36).substr(2, 9) : (dano.id || 0);
            dano._uid = dano._uid || uid;
            var btnRemove = isLocal
                ? '<button type="button" class="btn btn-xs btn-outline-danger js-remover-dano" data-dano-tipo="local" data-dano-uid="' + dano._uid + '" title="Remover dano"><i class="fas fa-times"></i></button>'
                : '<button type="button" class="btn btn-xs btn-outline-danger js-remover-dano" data-dano-tipo="servidor" data-dano-id="' + (dano.id || 0) + '" title="Remover dano"><i class="fas fa-times"></i></button>';
            tbody.insertAdjacentHTML('beforeend', '<tr><td>' + localLabel + '</td><td>' + descricao + '</td><td>' + tipoBadge(dano.tipo_dano || dano.tipo) + '</td><td>' + formatCusto(dano.custo_estimado) + '</td><td>' + rowImagens(dano) + '</td><td class="text-center">' + btnRemove + '</td></tr>');
        });
    }

    function obterDanosExistentes() {
        const veiculo_id = document.getElementById('veiculo')?.value;
        const reserva_id = document.getElementById('reserva_id')?.value;
        $.ajax({
            url: "<?php echo e(route('dano.obterDanosExistentes')); ?>",
            type: 'GET',
            data: { veiculo_id, codigo: '<?php echo e($codigoBreadcrumb ?? $codigo ?? $reserva->id); ?>', reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_existentes');
                tbody.innerHTML = '';
                if (!data.length) { tbody.innerHTML = '<tr><td colspan="4" class="damage-empty"><i class="fas fa-check-circle"></i> Nenhum dano anterior</td></tr>'; return; }
                data.forEach(dano => {
                    const categoria = dano.categoria ?? '—';
                    const local = dano.localizacao_problema ?? '—';
                    const locLabel = '<strong>' + dano.localizacao + '</strong><br><small class="text-muted">' + categoria + ' · ' + local + '</small>';
                    const descricao = dano.descricao || '<span class="text-muted">—</span>';
                    const corrSpan = dano.correcao_realizada ? '<span class="text-success"><i class="fas fa-wrench"></i> Corrigido</span>' : '<span class="text-warning"><i class="fas fa-hourglass-half"></i> Pendente</span>';
                    tbody.insertAdjacentHTML('beforeend', '<tr><td>' + locLabel + '</td><td>' + descricao + '</td><td>' + corrSpan + '</td><td>' + rowImagens(dano) + '</td></tr>');
                });
            }
        });
    }

    window.abrirFotoDano = function abrirFotoDano(src) {
        let modal = document.getElementById('lightboxFotoDano');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'lightboxFotoDano';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out';
            modal.onclick = () => modal.remove();
            modal.innerHTML = '<img id="lightboxFotoDanoImg" style="max-width:92vw;max-height:90vh;border-radius:12px;box-shadow:0 8px 40px rgba(0,0,0,.6)">';
            document.body.appendChild(modal);
        }
        document.getElementById('lightboxFotoDanoImg').src = src;
        document.getElementById('lightboxFotoDano').style.display = 'flex';
    }

    window.obterDanosDetectados = obterDanosDetectados;
    window.obterDanosExistentes = obterDanosExistentes;
    window.removerDanoLocal = removerDanoLocal;
    window.removerDanoServidor = removerDanoServidor;

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('.js-remover-dano');
        if (!btn) return;

        var tipo = btn.dataset.danoTipo;
        if (tipo === 'local') {
            removerDanoLocal(btn.dataset.danoUid);
            return;
        }

        if (tipo === 'servidor') {
            removerDanoServidor(Number(btn.dataset.danoId || 0));
        }
    });

    function setNomeDano(nome) {
        const el = document.getElementById('informacao_dano');
        if (el) el.innerText = nome;
    }
    window.setNomeDano = setNomeDano;

    /* Init */
    showStep(1);

    // Em modo edição, carrega danos do veículo já selecionado
    <?php if($contratoExistente && $contratoExistente->veiculo_id): ?>
    (function() {
        setTimeout(function() {
            obterDanosExistentes();
            obterDanosDetectados();
        }, 300);
    })();
    <?php endif; ?>

    // Contrato assinado: bloqueia todos os campos e botões do wizard
    <?php if($contratoExistente && $contratoExistente->assinado == 'S'): ?>
    (function() {
        const form = document.getElementById('form_dados_veiculos');
        if (form) {
            form.querySelectorAll('input, select, textarea, button').forEach(function(el) {
                el.disabled = true;
            });
        }
        // Oculta botões de navegação do wizard (Continuar/Salvar)
        const nextBtn  = document.getElementById('wizardNext');
        const prevBtn  = document.getElementById('wizardPrev');
        const createBtn = document.getElementById('wizardCreateBtn');
        [nextBtn, createBtn].forEach(function(b) { if (b) b.classList.add('d-none'); });
        // Mantém o botão Voltar funcional mas apenas para navegar entre steps já preenchidos
    })();
    <?php endif; ?>

    /* ========================
       UPGRADE DE CATEGORIA
    ======================== */
    (function initUpgradeCheck() {
        const statusUpgradeUrl = <?php echo json_encode(route('reserva.checkin.statusUpgrade', $codigoBreadcrumb ?? $codigo ?? $reserva->id), 512) ?>;

        fetch(statusUpgradeUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.tem_disponibilidade_categoria_original) return; // sem upgrade

                const banner = document.getElementById('upgrade-banner');
                const grupoLabel = document.getElementById('upgrade-grupo-original');
                if (banner) {
                    banner.classList.remove('d-none');
                    if (grupoLabel) grupoLabel.textContent = data.grupo_original?.nome ?? 'categoria original';
                }

                // Ajusta UI do passo 1: ocultar marca/modelo e focar no veiculo disponivel no periodo
                const hintNormal = document.getElementById('identificacao_hint_normal');
                const hintUpgrade = document.getElementById('identificacao_hint_upgrade');
                if (hintNormal) hintNormal.classList.add('d-none');
                if (hintUpgrade) hintUpgrade.classList.remove('d-none');

                const marcaCol = document.getElementById('marca_col');
                const modeloCol = document.getElementById('modelo_col');
                const veiculoCol = document.getElementById('veiculo_col');
                if (marcaCol) marcaCol.classList.add('d-none');
                if (modeloCol) modeloCol.classList.add('d-none');
                if (veiculoCol) {
                    veiculoCol.classList.remove('col-md-4');
                    veiculoCol.classList.add('col-md-12');
                }

                const marcaEl = document.getElementById('marca');
                const modeloEl = document.getElementById('modelo');
                if (marcaEl) { marcaEl.required = false; marcaEl.disabled = true; }
                if (modeloEl) { modeloEl.required = false; modeloEl.disabled = true; }

                // Substitui o select de veiculo por um select de upgrade (somente elegiveis por padrao)
                const veiculoSelect = document.getElementById('veiculo');
                if (!veiculoSelect) return;

                const todos = Array.isArray(data.veiculos_periodo) ? data.veiculos_periodo : [];
                if (!todos.length) {
                    veiculoSelect.innerHTML = '<option value="" disabled selected>Nenhum veiculo disponivel no periodo</option>';
                    veiculoSelect.disabled = true;
                    return;
                }

                const mostrarBloqueadosEl = document.getElementById('upgrade_mostrar_bloqueados');

                const gruposMap = new Map();
                todos.forEach(v => {
                    const grupoId = v.grupo?.id ?? 0;
                    if (!gruposMap.has(grupoId)) {
                        gruposMap.set(grupoId, { grupo: v.grupo ?? { id: 0, nome: 'Sem grupo', ordem: 0 }, veiculos: [] });
                    }
                    gruposMap.get(grupoId).veiculos.push(v);
                });

                const gruposOrdenados = Array.from(gruposMap.values()).sort((a, b) => {
                    const oa = Number(a.grupo?.ordem ?? 0);
                    const ob = Number(b.grupo?.ordem ?? 0);
                    return ob - oa; // melhores primeiro
                });

                const eligibleGroups = [];
                const blockedGroups = [];

                gruposOrdenados.forEach(item => {
                    const veiculos = item.veiculos ?? [];
                    const elegiveis = veiculos.filter(v => !!v.elegivel_upgrade);
                    const bloqueados = veiculos.filter(v => !v.elegivel_upgrade);

                    if (elegiveis.length) eligibleGroups.push({ ...item, veiculos: elegiveis });
                    if (bloqueados.length) blockedGroups.push({ ...item, veiculos: bloqueados });
                });

                function buildOptions(includeBlocked) {
                    veiculoSelect.disabled = false;
                    veiculoSelect.innerHTML = '<option value="" disabled selected>Selecione um veículo</option>';

                    if (!eligibleGroups.length) {
                        veiculoSelect.innerHTML = '<option value="" disabled selected>Nenhum veículo elegível para upgrade</option>';
                        veiculoSelect.disabled = true;
                        return;
                    }

                    eligibleGroups.forEach(item => {
                        const optGroup = document.createElement('optgroup');
                        optGroup.label = `⬆ ${item.grupo?.nome ?? 'Grupo'} (upgrade)`;
                        (item.veiculos ?? []).forEach(v => {
                            const opt = document.createElement('option');
                            opt.value = v.id;
                                opt.textContent = (v.nome ?? 'Veículo') + (v.placa ? ' - ' + v.placa : '');
                                if (v.quilometragem_atual !== undefined && v.quilometragem_atual !== null) opt.dataset.quilometragem = String(v.quilometragem_atual);
                                if (v.nivel_combustivel !== undefined && v.nivel_combustivel !== null) opt.dataset.nivelCombustivel = String(v.nivel_combustivel);
                            opt.dataset.upgrade = '1';
                            optGroup.appendChild(opt);
                        });
                        veiculoSelect.appendChild(optGroup);
                    });

                    if (includeBlocked && blockedGroups.length) {
                        blockedGroups.forEach(item => {
                            const optGroup = document.createElement('optgroup');
                            optGroup.label = `⬇ ${item.grupo?.nome ?? 'Grupo'} (inferior - bloqueado)`;
                            (item.veiculos ?? []).forEach(v => {
                                const opt = document.createElement('option');
                                opt.value = v.id;
                                opt.textContent = (v.nome ?? 'Veículo') + (v.placa ? ' - ' + v.placa : '') + ' (inferior)';
                                opt.disabled = true;
                                optGroup.appendChild(opt);
                            });
                            veiculoSelect.appendChild(optGroup);
                        });
                    }

                    // refresh select2
                    try { $('#veiculo').trigger('change.select2'); } catch (e) {}
                }

                buildOptions(!!mostrarBloqueadosEl?.checked);
                mostrarBloqueadosEl?.addEventListener('change', function () {
                    buildOptions(!!this.checked);
                });
            })
            .catch(() => { /* silencioso — funciona sem upgrade */ });
    })();

})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/checkin.blade.php ENDPATH**/ ?>