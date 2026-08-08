

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
<?php
    $isEdit = isset($movimentacao) && $movimentacao;
    $prefill = $prefill ?? [];
    $dataRegistro        = $isEdit && $movimentacao->data_registro         ? \Carbon\Carbon::parse($movimentacao->data_registro)->format('Y-m-d\TH:i')         : now()->format('Y-m-d\TH:i');
    $dataSaida           = $isEdit && $movimentacao->data_saida            ? \Carbon\Carbon::parse($movimentacao->data_saida)->format('Y-m-d\TH:i')            : '';
    $dataChegadaPrevista = $isEdit && $movimentacao->data_chegada_prevista ? \Carbon\Carbon::parse($movimentacao->data_chegada_prevista)->format('Y-m-d\TH:i') : '';
    $dataChegadaReal     = $isEdit && $movimentacao->data_chegada_real     ? \Carbon\Carbon::parse($movimentacao->data_chegada_real)->format('Y-m-d\TH:i')     : '';

    $selTipo    = old('tipo_movimentacao_id',    $isEdit ? $movimentacao->tipo_movimentacao_id    : ($prefill['tipo_movimentacao_id']    ?? ''));
    $selStatus  = old('status_movimentacao_id',  $isEdit ? $movimentacao->status_movimentacao_id  : ($prefill['status_movimentacao_id']  ?? ''));
    $selVeiculo = old('veiculo_id',              $isEdit ? $movimentacao->veiculo_id              : ($prefill['veiculo_id']              ?? ''));
    $selOrigem  = old('local_origem_id',         $isEdit ? $movimentacao->local_origem_id         : ($prefill['local_origem_id']         ?? ''));
    $selDestino = old('local_destino_id',        $isEdit ? $movimentacao->local_destino_id        : ($prefill['local_destino_id']        ?? ''));
    $selUsuario = old('user_id',                 $isEdit ? $movimentacao->user_id                 : ($prefill['user_id']                 ?? auth()->id()));
    $selReserva = old('reserva_id',              $isEdit ? $movimentacao->reserva_id              : '');
    $selOs      = old('ordem_servico_id',        $isEdit ? $movimentacao->ordem_servico_id        : '');
    $kmSaida    = old('km_saida',                $isEdit ? $movimentacao->km_saida                : ($prefill['km_saida'] ?? ''));
    $kmChegada  = old('km_chegada',              $isEdit ? $movimentacao->km_chegada              : '');

    // Labels para display nos inputs readonly
    $veiculoLabel  = '';
    $origemLabel   = '';
    $destinoLabel  = '';
    $usuarioLabel  = '';

    if ($selVeiculo && isset($veiculos[$selVeiculo])) {
        $veiculoLabel = $veiculos[$selVeiculo];
    }
    foreach ($estacoes as $e) {
        $eid = is_object($e) ? $e->id : ($e['id'] ?? null);
        $enome = is_object($e) ? $e->nome : ($e['nome'] ?? '');
        if ($eid == $selOrigem) $origemLabel  = $enome;
        if ($eid == $selDestino) $destinoLabel = $enome;
    }
    if (isset($usuariosObj)) {
        foreach ($usuariosObj as $u) {
            if ($u->id == $selUsuario) { $usuarioLabel = $u->nome; break; }
        }
    } else {
        foreach ($usuarios as $uid => $unome) {
            if ($uid == $selUsuario) $usuarioLabel = $unome;
        }
    }
?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 pl-3">
            <?php $__currentLoopData = array_unique($errors->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($e); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="mv-page">

    
    <div class="mv-guide mb-4">
        <div class="mv-guide-title">
            <i class="fas fa-lightbulb"></i>
            Como preencher? Escolha o cenário mais comum:
        </div>
        <div class="mv-guide-cards">
            <button type="button" class="mv-scenario" data-scenario="transferencia">
                <i class="fas fa-exchange-alt"></i>
                <strong>Transferência entre estações</strong>
                <span>Mover veículo de uma estação para outra</span>
            </button>
            <button type="button" class="mv-scenario" data-scenario="reserva">
                <i class="fas fa-calendar-check"></i>
                <strong>Saída para reserva</strong>
                <span>Veículo saindo para atender uma reserva</span>
            </button>
            <button type="button" class="mv-scenario" data-scenario="manutencao">
                <i class="fas fa-tools"></i>
                <strong>Manutenção / OS</strong>
                <span>Veículo indo para manutenção ou revisão</span>
            </button>
            <button type="button" class="mv-scenario" data-scenario="retorno">
                <i class="fas fa-undo-alt"></i>
                <strong>Retorno de reserva</strong>
                <span>Veículo retornando após entrega ao cliente</span>
            </button>
        </div>
        <div class="mv-guide-hint" id="mvGuideHint" style="display:none"></div>
    </div>

    
    <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0 pt-3 px-4">
            <div class="d-flex align-items-center" style="gap:.75rem">
                <div class="mv-header-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?php echo e($titulo); ?></h4>
                    <p class="text-muted small mb-0">Preencha os 4 passos abaixo para registrar a movimentação.</p>
                </div>
            </div>
        </div>

        <div class="card-body px-4 pb-4 pt-3">
            <form id="formMov" method="POST" action="<?php echo e($rota); ?>">
                <?php echo csrf_field(); ?>
                <?php if($metodo === 'PUT'): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                
                <div class="mv-wizard-top mb-3">
                    <div class="mv-wizard-steps">
                        <button type="button" class="mv-step is-active" data-mv-step-btn="1">
                            <span class="num">1</span><span class="txt">Tipo & Status</span>
                        </button>
                        <div class="mv-step-line"></div>
                        <button type="button" class="mv-step" data-mv-step-btn="2">
                            <span class="num">2</span><span class="txt">Veículo & Locais</span>
                        </button>
                        <div class="mv-step-line"></div>
                        <button type="button" class="mv-step" data-mv-step-btn="3">
                            <span class="num">3</span><span class="txt">Vínculos</span>
                        </button>
                        <div class="mv-step-line"></div>
                        <button type="button" class="mv-step" data-mv-step-btn="4">
                            <span class="num">4</span><span class="txt">Datas & Obs.</span>
                        </button>
                    </div>
                </div>

                
                <div class="mv-panel" data-mv-panel="1">
                    <div class="mv-section-title">Classificação da Movimentação</div>

                    <div class="form-row">
                        <div class="col-lg-5 form-group">
                            <label class="mv-label">
                                Tipo de Movimentação <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnAbrirModalTipo" title="Consultar tipos">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <select name="tipo_movimentacao_id" id="tipo_movimentacao_id" class="form-control form-control-sm" required>
                                    <option value="">Selecione um tipo…</option>
                                    <?php $__currentLoopData = $tiposMovimentacao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php echo e($selTipo == $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Clique na lupa para ver a descrição de cada tipo</small>
                        </div>
                        <div class="col-lg-5 form-group">
                            <label class="mv-label">
                                Status da Movimentação <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnAbrirModalStatus" title="Consultar status">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <select name="status_movimentacao_id" id="status_movimentacao_id" class="form-control form-control-sm" required>
                                    <option value="">Selecione um status…</option>
                                    <?php $__currentLoopData = $statusMovimentacao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php echo e($selStatus == $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Clique na lupa para ver a descrição de cada status</small>
                        </div>
                        <div class="col-lg-2 form-group">
                            <label class="mv-label">Status do registro</label>
                            <select name="ativo" class="form-control form-control-sm">
                                <option value="S" <?php echo e(old('ativo', $isEdit ? $movimentacao->ativo : 'S') === 'S' ? 'selected' : ''); ?>>Ativo</option>
                                <option value="N" <?php echo e(old('ativo', $isEdit ? $movimentacao->ativo : 'S') === 'N' ? 'selected' : ''); ?>>Inativo</option>
                            </select>
                        </div>
                        <div class="col-lg-2 form-group">
                            <label class="mv-label">Gera disponibilidade</label>
                            <select name="gera_disponibilidade" class="form-control form-control-sm">
                                <option value="N" <?php echo e(old('gera_disponibilidade', $isEdit ? $movimentacao->gera_disponibilidade : 'N') === 'N' ? 'selected' : ''); ?>>Não</option>
                                <option value="S" <?php echo e(old('gera_disponibilidade', $isEdit ? $movimentacao->gera_disponibilidade : 'N') === 'S' ? 'selected' : ''); ?>>Sim</option>
                            </select>
                            <small class="text-muted d-block mt-1">Use S para liberar a disponibilidade do veículo ao concluir a movimentação.</small>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Nome / Descrição (opcional)</label>
                            <input type="text" name="nome" class="form-control form-control-sm"
                                   value="<?php echo e(old('nome', $isEdit ? $movimentacao->nome : '')); ?>"
                                   placeholder="Ex: Transferência AGO/2026">
                        </div>
                    </div>
                </div>

                
                <div class="mv-panel d-none" data-mv-panel="2">
                    <div class="mv-section-title">Veículo e Quilometragem</div>
                    <div class="form-row">
                        <div class="col-lg-8 form-group">
                            <label class="mv-label">Veículo</label>
                            <input type="hidden" name="veiculo_id" id="mv_veiculo_id" value="<?php echo e($selVeiculo); ?>">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculoMov" title="Buscar veículo">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_veiculo_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="<?php echo e($veiculoLabel); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm <?php echo e($selVeiculo ? '' : 'd-none'); ?>" id="btnLimparVeiculoMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted" id="mv_veiculo_km_hint"></small>
                        </div>
                    </div>

                    <div class="mv-section-title mt-3">Locais de Origem e Destino</div>
                    <div class="form-row">
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Estação de Origem</label>
                            <input type="hidden" name="local_origem_id" id="mv_origem_id" value="<?php echo e($selOrigem); ?>">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" data-buscar-estacao="origem" title="Buscar estação de origem">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_origem_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar estação…"
                                       value="<?php echo e($origemLabel); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm <?php echo e($selOrigem ? '' : 'd-none'); ?>" id="btnLimparOrigem">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Estação de Destino</label>
                            <input type="hidden" name="local_destino_id" id="mv_destino_id" value="<?php echo e($selDestino); ?>">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" data-buscar-estacao="destino" title="Buscar estação de destino">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_destino_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar estação…"
                                       value="<?php echo e($destinoLabel); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm <?php echo e($selDestino ? '' : 'd-none'); ?>" id="btnLimparDestino">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mv-section-title mt-3">Quilometragem</div>
                    <div class="form-row">
                        <div class="col-lg-4 form-group">
                            <label class="mv-label">
                                KM de Saída
                                <span class="mv-km-badge" id="mv_km_veiculo_badge" style="display:none"></span>
                            </label>
                            <input type="number" name="km_saida" id="mv_km_saida" class="form-control form-control-sm"
                                   value="<?php echo e($kmSaida); ?>" min="0" step="1" placeholder="Ex: 45230">
                            <small class="text-danger" id="mv_km_saida_erro" style="display:none">
                                KM de saída não pode ser menor que o último KM registrado do veículo.
                            </small>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="mv-label">KM de Chegada</label>
                            <input type="number" name="km_chegada" id="mv_km_chegada" class="form-control form-control-sm"
                                   value="<?php echo e($kmChegada); ?>" min="0" step="1" placeholder="Ex: 45890">
                            <small class="text-danger" id="mv_km_chegada_erro" style="display:none">
                                KM de chegada deve ser maior ou igual ao KM de saída.
                            </small>
                        </div>
                    </div>
                </div>

                
                <div class="mv-panel d-none" data-mv-panel="3">
                    <div class="mv-section-title">Usuário Responsável</div>
                    <div class="form-row">
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Usuário <span class="text-danger">*</span></label>
                            <input type="hidden" name="user_id" id="mv_usuario_id" value="<?php echo e($selUsuario); ?>" required>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarUsuarioMov" title="Buscar usuário">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_usuario_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="<?php echo e($usuarioLabel); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm <?php echo e($selUsuario ? '' : 'd-none'); ?>" id="btnLimparUsuarioMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mv-section-title mt-3">Reserva e Ordem de Serviço (opcionais)</div>
                    <div class="form-row">
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Reserva</label>
                            <input type="hidden" name="reserva_id" id="mv_reserva_id" value="<?php echo e($selReserva); ?>">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarReservaMov" title="Buscar reserva">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_reserva_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="<?php echo e($selReserva ? 'Reserva #' . $selReserva : ''); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm <?php echo e($selReserva ? '' : 'd-none'); ?>" id="btnLimparReservaMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Ordem de Serviço</label>
                            <input type="hidden" name="ordem_servico_id" id="mv_os_id" value="<?php echo e($selOs); ?>">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarOsMov" title="Buscar OS">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_os_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="<?php echo e($selOs ? 'OS #' . $selOs : ''); ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm <?php echo e($selOs ? '' : 'd-none'); ?>" id="btnLimparOsMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="mv-panel d-none" data-mv-panel="4">

                    
                    <div id="mv_horario_aviso" class="mv-horario-aviso d-none">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="mv_horario_aviso_txt"></span>
                    </div>

                    <div class="mv-section-title">Datas e Horários</div>

                    
                    <div class="form-row">
                        <div class="col-lg-6 col-md-6 form-group">
                            <label class="mv-label">
                                Data de Registro <span class="text-danger">*</span>
                            </label>
                            
                            <input type="hidden" name="data_registro" id="mv_data_registro_hidden"
                                   value="<?php echo e($dataRegistro); ?>">
                            <div class="mv-dt-group">
                                <div class="mv-dt-field">
                                    <i class="fas fa-calendar-alt mv-dt-icon"></i>
                                    <input type="text" id="mv_data_registro_date"
                                           class="form-control form-control-sm mv-datepicker"
                                           placeholder="dd/mm/aaaa" autocomplete="off" required>
                                </div>
                                <div class="mv-dt-sep">às</div>
                                <div class="mv-dt-field mv-dt-field--time">
                                    <i class="fas fa-clock mv-dt-icon"></i>
                                    <select id="mv_data_registro_time" class="form-control form-control-sm mv-time-select">
                                        <option value="">Hora</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="form-row">
                        <div class="col-lg-6 col-md-6 form-group">
                            <label class="mv-label">
                                <i class="fas fa-arrow-up mv-dt-label-icon" style="color:#0891b2"></i>
                                Data de Saída
                                <span class="mv-dt-label-hint" id="mv_saida_origem_hint"></span>
                            </label>
                            <input type="hidden" name="data_saida" id="mv_data_saida_hidden"
                                   value="<?php echo e($dataSaida); ?>">
                            <div class="mv-dt-group">
                                <div class="mv-dt-field">
                                    <i class="fas fa-calendar-alt mv-dt-icon"></i>
                                    <input type="text" id="mv_data_saida_date"
                                           class="form-control form-control-sm mv-datepicker"
                                           data-slot="saida"
                                           placeholder="dd/mm/aaaa" autocomplete="off">
                                </div>
                                <div class="mv-dt-sep">às</div>
                                <div class="mv-dt-field mv-dt-field--time">
                                    <i class="fas fa-clock mv-dt-icon"></i>
                                    <select id="mv_data_saida_time" class="form-control form-control-sm mv-time-select"
                                            data-slot="saida">
                                        <option value="">Hora</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-6 col-md-6 form-group">
                            <label class="mv-label">
                                <i class="fas fa-arrow-down mv-dt-label-icon" style="color:#059669"></i>
                                Chegada Prevista
                                <span class="mv-dt-label-hint" id="mv_chegada_destino_hint"></span>
                            </label>
                            <input type="hidden" name="data_chegada_prevista" id="mv_data_chegada_prevista_hidden"
                                   value="<?php echo e($dataChegadaPrevista); ?>">
                            <div class="mv-dt-group">
                                <div class="mv-dt-field">
                                    <i class="fas fa-calendar-alt mv-dt-icon"></i>
                                    <input type="text" id="mv_data_chegada_prevista_date"
                                           class="form-control form-control-sm mv-datepicker"
                                           data-slot="chegada_prevista"
                                           placeholder="dd/mm/aaaa" autocomplete="off">
                                </div>
                                <div class="mv-dt-sep">às</div>
                                <div class="mv-dt-field mv-dt-field--time">
                                    <i class="fas fa-clock mv-dt-icon"></i>
                                    <select id="mv_data_chegada_prevista_time"
                                            class="form-control form-control-sm mv-time-select"
                                            data-slot="chegada_prevista">
                                        <option value="">Hora</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-danger" id="mv_erro_chegada_prevista" style="display:none">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Deve ser posterior à data de saída.
                            </small>
                        </div>
                    </div>

                    
                    <div class="form-row">
                        <div class="col-lg-6 col-md-6 form-group">
                            <label class="mv-label">
                                <i class="fas fa-flag-checkered mv-dt-label-icon" style="color:#7c3aed"></i>
                                Chegada Real
                            </label>
                            <input type="hidden" name="data_chegada_real" id="mv_data_chegada_real_hidden"
                                   value="<?php echo e($dataChegadaReal); ?>">
                            <div class="mv-dt-group">
                                <div class="mv-dt-field">
                                    <i class="fas fa-calendar-alt mv-dt-icon"></i>
                                    <input type="text" id="mv_data_chegada_real_date"
                                           class="form-control form-control-sm mv-datepicker"
                                           data-slot="chegada_real"
                                           placeholder="dd/mm/aaaa" autocomplete="off">
                                </div>
                                <div class="mv-dt-sep">às</div>
                                <div class="mv-dt-field mv-dt-field--time">
                                    <i class="fas fa-clock mv-dt-icon"></i>
                                    <select id="mv_data_chegada_real_time"
                                            class="form-control form-control-sm mv-time-select"
                                            data-slot="chegada_real">
                                        <option value="">Hora</option>
                                    </select>
                                </div>
                            </div>
                            <small class="text-danger" id="mv_erro_chegada_real" style="display:none">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Deve ser posterior à data de saída.
                            </small>
                        </div>
                    </div>

                    <div class="mv-section-title mt-2">Observações</div>
                    <div class="form-row">
                        <div class="col-12 form-group">
                            <textarea name="observacoes" class="form-control form-control-sm" rows="4"
                                      placeholder="Detalhes adicionais sobre a movimentação…"><?php echo e(old('observacoes', $isEdit ? $movimentacao->observacoes : '')); ?></textarea>
                        </div>
                    </div>
                </div>

                
                <div class="mv-wizard-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="mvPrev" disabled>
                        <i class="fas fa-arrow-left mr-1"></i>Anterior
                    </button>
                    <div class="d-flex" style="gap:8px">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" id="mvNext">
                            Continuar <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" id="mvSubmit" class="btn btn-success btn-sm rounded-pill d-none">
                            <i class="fas fa-save mr-1"></i>Salvar movimentação
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <?php if($isEdit && isset($movimentacao) && $movimentacao->relationLoaded('historico') && $movimentacao->historico->isNotEmpty()): ?>
    <div class="mv-historico-card mt-4">
        <div class="mv-historico-header">
            <div class="mv-historico-icon"><i class="fas fa-history"></i></div>
            <div>
                <strong>Histórico de Alterações</strong>
                <span><?php echo e($movimentacao->historico->count()); ?> registro(s)</span>
            </div>
        </div>
        <div class="mv-historico-timeline">
            <?php $__currentLoopData = $movimentacao->historico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $acaoIcon = match($h->acao) {
                    'create'  => ['icon' => 'fa-plus-circle',    'color' => '#10b981', 'bg' => '#d1fae5'],
                    'concluir'=> ['icon' => 'fa-check-double',   'color' => '#10b981', 'bg' => '#d1fae5'],
                    'update'  => ['icon' => 'fa-pen',            'color' => '#3b82f6', 'bg' => '#dbeafe'],
                    default   => ['icon' => 'fa-circle',         'color' => '#94a3b8', 'bg' => '#f1f5f9'],
                };
                $acaoLabel = match($h->acao) {
                    'create'  => 'Criado',
                    'concluir'=> 'Concluído',
                    'update'  => 'Alterado',
                    default   => ucfirst($h->acao),
                };
            ?>
            <div class="mv-historico-item">
                <div class="mv-historico-dot" style="background:<?php echo e($acaoIcon['bg']); ?>;color:<?php echo e($acaoIcon['color']); ?>">
                    <i class="fas <?php echo e($acaoIcon['icon']); ?>"></i>
                </div>
                <div class="mv-historico-body">
                    <div class="mv-historico-meta">
                        <span class="mv-historico-acao" style="color:<?php echo e($acaoIcon['color']); ?>"><?php echo e($acaoLabel); ?></span>
                        <span class="mv-historico-user">
                            <i class="fas fa-user fa-xs mr-1"></i><?php echo e($h->usuario?->nome ?? 'Sistema'); ?>

                        </span>
                        <span class="mv-historico-data">
                            <i class="fas fa-clock fa-xs mr-1"></i><?php echo e($h->created_at->format('d/m/Y H:i')); ?>

                        </span>
                    </div>
                    <?php if($h->observacao): ?>
                    <div class="mv-historico-obs"><?php echo e($h->observacao); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

</div>





<?php if (isset($component)) { $__componentOriginal29f5fcd4d4ae662e15785dac01680db5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal29f5fcd4d4ae662e15785dac01680db5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.movimentacao.modal-tipo','data' => ['tipos' => $tiposMovimentacaoObj ?? collect(),'modalId' => 'modalTipoMov']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('movimentacao.modal-tipo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tipos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($tiposMovimentacaoObj ?? collect()),'modal-id' => 'modalTipoMov']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal29f5fcd4d4ae662e15785dac01680db5)): ?>
<?php $attributes = $__attributesOriginal29f5fcd4d4ae662e15785dac01680db5; ?>
<?php unset($__attributesOriginal29f5fcd4d4ae662e15785dac01680db5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal29f5fcd4d4ae662e15785dac01680db5)): ?>
<?php $component = $__componentOriginal29f5fcd4d4ae662e15785dac01680db5; ?>
<?php unset($__componentOriginal29f5fcd4d4ae662e15785dac01680db5); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginaleac6b62be74d7267042495ac6b824643 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleac6b62be74d7267042495ac6b824643 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.movimentacao.modal-status','data' => ['statuses' => $statusMovimentacaoObj ?? collect(),'modalId' => 'modalStatusMov']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('movimentacao.modal-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['statuses' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusMovimentacaoObj ?? collect()),'modal-id' => 'modalStatusMov']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleac6b62be74d7267042495ac6b824643)): ?>
<?php $attributes = $__attributesOriginaleac6b62be74d7267042495ac6b824643; ?>
<?php unset($__attributesOriginaleac6b62be74d7267042495ac6b824643); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleac6b62be74d7267042495ac6b824643)): ?>
<?php $component = $__componentOriginaleac6b62be74d7267042495ac6b824643; ?>
<?php unset($__componentOriginaleac6b62be74d7267042495ac6b824643); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginalc2f2a0bcee6c863dac67ebc059279604 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2f2a0bcee6c863dac67ebc059279604 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.movimentacao.modal-estacao','data' => ['estacoes' => $estacoes,'modalId' => 'modalEstacaoMov']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('movimentacao.modal-estacao'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['estacoes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($estacoes),'modal-id' => 'modalEstacaoMov']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc2f2a0bcee6c863dac67ebc059279604)): ?>
<?php $attributes = $__attributesOriginalc2f2a0bcee6c863dac67ebc059279604; ?>
<?php unset($__attributesOriginalc2f2a0bcee6c863dac67ebc059279604); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc2f2a0bcee6c863dac67ebc059279604)): ?>
<?php $component = $__componentOriginalc2f2a0bcee6c863dac67ebc059279604; ?>
<?php unset($__componentOriginalc2f2a0bcee6c863dac67ebc059279604); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginal4a68801be7f58b4a0ed7d37c4282173f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a68801be7f58b4a0ed7d37c4282173f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.movimentacao.modal-usuario','data' => ['usuarios' => $usuariosObj ?? collect(),'modalId' => 'modalUsuarioMov']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('movimentacao.modal-usuario'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['usuarios' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($usuariosObj ?? collect()),'modal-id' => 'modalUsuarioMov']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a68801be7f58b4a0ed7d37c4282173f)): ?>
<?php $attributes = $__attributesOriginal4a68801be7f58b4a0ed7d37c4282173f; ?>
<?php unset($__attributesOriginal4a68801be7f58b4a0ed7d37c4282173f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a68801be7f58b4a0ed7d37c4282173f)): ?>
<?php $component = $__componentOriginal4a68801be7f58b4a0ed7d37c4282173f; ?>
<?php unset($__componentOriginal4a68801be7f58b4a0ed7d37c4282173f); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginalce1091a496a3f551c006335418b0d986 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce1091a496a3f551c006335418b0d986 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.movimentacao.modal-reserva','data' => ['modalId' => 'modalReservaMov']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('movimentacao.modal-reserva'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['modal-id' => 'modalReservaMov']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce1091a496a3f551c006335418b0d986)): ?>
<?php $attributes = $__attributesOriginalce1091a496a3f551c006335418b0d986; ?>
<?php unset($__attributesOriginalce1091a496a3f551c006335418b0d986); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce1091a496a3f551c006335418b0d986)): ?>
<?php $component = $__componentOriginalce1091a496a3f551c006335418b0d986; ?>
<?php unset($__componentOriginalce1091a496a3f551c006335418b0d986); ?>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginalec65586f0724090c34370f5969ef87f8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec65586f0724090c34370f5969ef87f8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.movimentacao.modal-os','data' => ['modalId' => 'modalOsMov']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('movimentacao.modal-os'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['modal-id' => 'modalOsMov']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec65586f0724090c34370f5969ef87f8)): ?>
<?php $attributes = $__attributesOriginalec65586f0724090c34370f5969ef87f8; ?>
<?php unset($__attributesOriginalec65586f0724090c34370f5969ef87f8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec65586f0724090c34370f5969ef87f8)): ?>
<?php $component = $__componentOriginalec65586f0724090c34370f5969ef87f8; ?>
<?php unset($__componentOriginalec65586f0724090c34370f5969ef87f8); ?>
<?php endif; ?>


<?php
    $filtroMarcas  = collect([]);
    $filtroGrupos  = collect([]);
    $filtroTipos   = collect([]);
    $filtroStatus  = collect([]);
?>
<?php if (isset($component)) { $__componentOriginalcdba5f1885e71445158c7d13c4efb649 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcdba5f1885e71445158c7d13c4efb649 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.veiculo.busca-avancada-modal','data' => ['prefix' => 'mov','title' => 'Buscar Veículo','triggerId' => 'btnBuscarVeiculoMov','hiddenInputId' => 'mv_veiculo_id','displayInputId' => 'mv_veiculo_display','clearBtnId' => 'btnLimparVeiculoMov','filtroMarcas' => $filtroMarcas,'filtroGrupos' => $filtroGrupos,'filtroTipos' => $filtroTipos,'filtroStatus' => $filtroStatus]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('veiculo.busca-avancada-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['prefix' => 'mov','title' => 'Buscar Veículo','trigger-id' => 'btnBuscarVeiculoMov','hidden-input-id' => 'mv_veiculo_id','display-input-id' => 'mv_veiculo_display','clear-btn-id' => 'btnLimparVeiculoMov','filtro-marcas' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filtroMarcas),'filtro-grupos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filtroGrupos),'filtro-tipos' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filtroTipos),'filtro-status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filtroStatus)]); ?>
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

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
/* ── Page wrapper ── */
.mv-page {   margin: 0 auto; }

/* ── Guia de cenários ── */
.mv-guide {
    background: linear-gradient(135deg,#f0f4ff,#fafbff);
    border: 1px solid #dde6f5;
    border-radius: 14px;
    padding: 16px 20px;
}
.mv-guide-title {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 12px;
}
.mv-guide-title i { color: #f59e0b; margin-right: 6px; }
.mv-guide-cards { display: flex; gap: 10px; flex-wrap: wrap; }
.mv-scenario {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    background: #fff;
    border: 1px solid #dde6f5;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .78rem;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, transform .12s;
    text-align: left;
    flex: 1 1 180px;
}
.mv-scenario i { font-size: 1rem; color: var(--c-indigo, #4338ca); margin-bottom: 2px; }
.mv-scenario strong { font-size: .82rem; color: #1e293b; }
.mv-scenario span { color: #64748b; font-size: .72rem; }
.mv-scenario:hover, .mv-scenario.is-active {
    border-color: #4338ca;
    box-shadow: 0 2px 10px rgba(67,56,202,.12);
    transform: translateY(-1px);
}
.mv-scenario.is-active { background: #eef2fb; }
.mv-guide-hint {
    margin-top: 12px;
    padding: 10px 14px;
    background: #fff8e1;
    border-left: 3px solid #f59e0b;
    border-radius: 6px;
    font-size: .82rem;
    color: #78350f;
}

/* ── Header icon ── */
.mv-header-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: linear-gradient(135deg,#1a2848,#2e4080);
    color: #93c5fd;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}

/* ── Wizard steps ── */
.mv-wizard-top {
    background: #f8fafd;
    border: 1px solid #e2e9f4;
    border-radius: 12px;
    padding: 12px 16px;
}
.mv-wizard-steps {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
}
.mv-step-line {
    flex: 1 1 20px;
    height: 2px;
    background: #dde6f5;
    border-radius: 99px;
    min-width: 12px;
}
.mv-step {
    appearance: none;
    border: 1.5px solid #dde6f5;
    background: #fff;
    border-radius: 999px;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: .75rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.mv-step .num {
    width: 20px; height: 20px; border-radius: 50%;
    background: #e2e9f4; color: #64748b;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 800;
}
.mv-step.is-active {
    background: #1a2848;
    border-color: #1a2848;
    color: #fff;
    box-shadow: 0 4px 12px rgba(26,40,72,.2);
}
.mv-step.is-active .num { background: rgba(255,255,255,.2); color: #fff; }
.mv-step.is-done { border-color: #10b981; color: #10b981; }
.mv-step.is-done .num { background: #d1fae5; color: #10b981; }

/* ── Panels ── */
.mv-panel { padding-top: 6px; }
.mv-section-title {
    font-size: .68rem; font-weight: 700; letter-spacing: .09em;
    text-transform: uppercase; color: #94a3b8;
    border-bottom: 1px solid #eef2fb;
    padding-bottom: 6px; margin-bottom: 14px;
}
.mv-label {
    font-size: .82rem; font-weight: 600; color: #374151;
    margin-bottom: .25rem; display: flex; align-items: center; gap: 6px;
}

/* ── KM badge ── */
.mv-km-badge {
    background: #fef3c7; border: 1px solid #fde68a;
    color: #92400e; font-size: .68rem; font-weight: 700;
    border-radius: 999px; padding: 1px 8px; white-space: nowrap;
}

/* ── Footer ── */
.mv-wizard-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding-top: 16px; margin-top: 16px;
    border-top: 1px dashed #e2e9f4;
}

/* ── Histórico ── */
.mv-historico-card {
    background: #fff;
    border: 1px solid #e2e9f4;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(12,20,40,.05);
}
.mv-historico-header {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px;
    background: linear-gradient(135deg,#f8fafd,#fff);
    border-bottom: 1px solid #eef2fb;
}
.mv-historico-header strong { font-size: .9rem; display: block; color: #1e293b; }
.mv-historico-header span { font-size: .72rem; color: #94a3b8; }
.mv-historico-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: #eef2ff; color: #4338ca;
    display: flex; align-items: center; justify-content: center; font-size: .85rem;
    flex-shrink: 0;
}
.mv-historico-timeline {
    padding: 14px 18px;
    display: flex; flex-direction: column; gap: 0;
}
.mv-historico-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed #f0f4fc;
    position: relative;
}
.mv-historico-item:last-child { border-bottom: none; }
.mv-historico-dot {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; flex-shrink: 0; margin-top: 1px;
}
.mv-historico-body { flex: 1; min-width: 0; }
.mv-historico-meta {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 3px;
}
.mv-historico-acao { font-size: .78rem; font-weight: 700; }
.mv-historico-user { font-size: .74rem; color: #475569; }
.mv-historico-data { font-size: .72rem; color: #94a3b8; }
.mv-historico-obs { font-size: .78rem; color: #64748b; line-height: 1.4; }

/* ══ Date-Time Picker custom ══ */
.mv-dt-group {
    display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;
}
.mv-dt-field {
    position: relative; flex: 1;
}
.mv-dt-field--time { flex: 0 0 140px; }
.mv-dt-icon {
    position: absolute; left: 9px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8; font-size: .72rem; pointer-events: none; z-index: 1;
}
.mv-dt-field input.form-control,
.mv-dt-field select.form-control {
    padding-left: 26px !important;
}
.mv-dt-sep {
    font-size: .78rem; font-weight: 600; color: #94a3b8;
    flex-shrink: 0; white-space: nowrap;
}
.mv-dt-label-icon { font-size: .7rem; }
.mv-dt-label-hint {
    font-size: .68rem; font-weight: 600; color: #0891b2;
    background: #e0f5fa; padding: 1px 7px;
    border-radius: 999px; margin-left: 4px;
}
.mv-time-select { appearance: none; -webkit-appearance: none; }
.mv-time-select option[disabled] { color: #cbd5e1; }

/* Aviso de horário da estação */
.mv-horario-aviso {
    background: #f0f9ff; border: 1px solid #bae6fd;
    border-radius: 10px; padding: 8px 14px;
    font-size: .8rem; color: #0369a1;
    margin-bottom: 14px; font-weight: 500;
}

/* Flatpickr overrides para combinar com AdminLTE */
.flatpickr-calendar {
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.08) !important;
    border: 1px solid #e2e9f4 !important;
    font-family: inherit !important;
}
.flatpickr-day.selected,
.flatpickr-day.selected:hover {
    background: #1a2848 !important;
    border-color: #1a2848 !important;
}
.flatpickr-day:hover:not(.disabled):not(.selected) {
    background: #eef2ff !important;
}
.flatpickr-day.disabled,
.flatpickr-day.disabled:hover {
    color: #e2e9f4 !important;
    background: transparent !important;
    cursor: not-allowed !important;
    text-decoration: line-through !important;
}
.flatpickr-day.today { border-color: #1a2848 !important; }
.flatpickr-months .flatpickr-month { background: #1a2848 !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months { color: #fff !important; }
.flatpickr-current-month input.cur-year { color: #fff !important; }
.flatpickr-weekdays { background: #f8fafd !important; }
.flatpickr-weekday { color: #64748b !important; font-weight: 700 !important; }
.numInputWrapper:hover { background: rgba(255,255,255,.1) !important; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
(function() {
'use strict';

const URL_KM_VEICULO = '<?php echo e(route("movimentacao.kmVeiculo")); ?>';
let kmVeiculoAtual   = <?php echo e($kmSaida ? (int)$kmSaida : 'null'); ?>;

// ── Wizard ───────────────────────────────────────────────
const MAX = 4;
const SK  = 'mvWizardStep';

let currentStep = (() => {
    <?php if($errors->any()): ?>
    return 1;
    <?php endif; ?>
    const s = parseInt(localStorage.getItem(SK) || '1', 10);
    return (s >= 1 && s <= MAX) ? s : 1;
})();

function setStep(s, persist = true) {
    s = Math.max(1, Math.min(MAX, s));
    currentStep = s;

    document.querySelectorAll('[data-mv-panel]').forEach(p => {
        const n = parseInt(p.dataset.mvPanel, 10);
        p.classList.toggle('d-none', n !== s);
    });
    document.querySelectorAll('[data-mv-step-btn]').forEach(b => {
        const n = parseInt(b.dataset.mvStepBtn, 10);
        b.classList.toggle('is-active', n === s);
        b.classList.toggle('is-done', n < s);
    });

    const prev   = document.getElementById('mvPrev');
    const next   = document.getElementById('mvNext');
    const submit = document.getElementById('mvSubmit');
    if (prev)   prev.disabled    = s <= 1;
    if (next)   next.classList.toggle('d-none', s >= MAX);
    if (submit) submit.classList.toggle('d-none', s < MAX);

    if (persist) localStorage.setItem(SK, String(s));
}

function validateCurrentStep() {
    const panel = document.querySelector(`[data-mv-panel="${currentStep}"]`);
    if (!panel) return true;

    if (currentStep === 2) {
        const kmS = parseFloat(document.getElementById('mv_km_saida').value);
        const kmC = parseFloat(document.getElementById('mv_km_chegada').value);
        const erroS = document.getElementById('mv_km_saida_erro');
        const erroC = document.getElementById('mv_km_chegada_erro');
        let ok = true;

        if (kmVeiculoAtual !== null && !isNaN(kmS) && kmS < kmVeiculoAtual) {
            erroS.style.display = 'block'; ok = false;
        } else { erroS.style.display = 'none'; }

        if (!isNaN(kmS) && !isNaN(kmC) && kmC > 0 && kmC < kmS) {
            erroC.style.display = 'block'; ok = false;
        } else { erroC.style.display = 'none'; }

        if (!ok) return false;
    }

    if (currentStep === 3) {
        const uid = document.getElementById('mv_usuario_id').value;
        if (!uid) { alert('Selecione um usuário responsável.'); return false; }
    }

    const fields = Array.from(panel.querySelectorAll('input, select, textarea'))
        .filter(f => f.willValidate && !f.disabled && f.type !== 'hidden');
    const invalid = fields.find(f => !f.checkValidity());
    if (invalid) { invalid.reportValidity(); return false; }

    return true;
}

document.getElementById('mvPrev')?.addEventListener('click', () => setStep(currentStep - 1));
document.getElementById('mvNext')?.addEventListener('click', () => {
    if (validateCurrentStep()) setStep(currentStep + 1);
});
document.querySelectorAll('[data-mv-step-btn]').forEach(b => {
    b.addEventListener('click', () => {
        const target = parseInt(b.dataset.mvStepBtn, 10);
        if (target > currentStep && !validateCurrentStep()) return;
        setStep(target);
    });
});

document.getElementById('formMov')?.addEventListener('submit', e => {
    const kmS = parseFloat(document.getElementById('mv_km_saida').value);
    const kmC = parseFloat(document.getElementById('mv_km_chegada').value);
    if (kmVeiculoAtual !== null && !isNaN(kmS) && kmS < kmVeiculoAtual) {
        e.preventDefault(); setStep(2);
        document.getElementById('mv_km_saida_erro').style.display = 'block';
        return;
    }
    if (!isNaN(kmS) && !isNaN(kmC) && kmC > 0 && kmC < kmS) {
        e.preventDefault(); setStep(2);
        document.getElementById('mv_km_chegada_erro').style.display = 'block';
        return;
    }
    localStorage.removeItem(SK);
});

setStep(currentStep, false);

// ── Guia de cenários ─────────────────────────────────────
const hints = {
    transferencia: '📍 <strong>Transferência entre estações:</strong> Selecione o tipo de movimentação correspondente à transferência. Defina a estação de <strong>Origem</strong> (onde o veículo está) e o <strong>Destino</strong>. Informe os KMs de saída. Vincule ao usuário responsável pela operação.',
    reserva:       '📋 <strong>Saída para reserva:</strong> Selecione o tipo "Saída/Reserva". Vincule o veículo e a reserva correspondente no passo 3. Informe o KM de saída. A chegada pode ser preenchida posteriormente.',
    manutencao:    '🔧 <strong>Manutenção / OS:</strong> Selecione o tipo correspondente à manutenção. Vincule a Ordem de Serviço no passo 3. Registre o KM de saída para controle de quilometragem.',
    retorno:       '🔄 <strong>Retorno de reserva:</strong> Selecione o tipo "Retorno/Devolução". Informe o KM de chegada. Vincule a reserva de origem no passo 3 para fechar o ciclo.',
};
document.querySelectorAll('.mv-scenario').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.mv-scenario').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        const hint = document.getElementById('mvGuideHint');
        hint.innerHTML = hints[btn.dataset.scenario] || '';
        hint.style.display = 'block';
    });
});

// ── Modal: Tipo ──────────────────────────────────────────
document.getElementById('btnAbrirModalTipo')?.addEventListener('click', () => {
    filtrarModalTipos('modalTipoMov');
    $('#modalTipoMov').modal('show');
});

// ── Modal: Status ────────────────────────────────────────
document.getElementById('btnAbrirModalStatus')?.addEventListener('click', () => {
    filtrarModalStatus('modalStatusMov');
    $('#modalStatusMov').modal('show');
});

// ── Modal: Estação ────────────────────────────────────────
let estacaoAlvo = 'origem';

function abrirModalEstacao(alvo) {
    estacaoAlvo = alvo;
    var titulo = document.getElementById('modalEstacaoMov_titulo');
    var sub    = document.getElementById('modalEstacaoMov_subtitulo');
    if (titulo) titulo.textContent = 'Estação de ' + (alvo === 'origem' ? 'Origem' : 'Destino');
    if (sub)    sub.textContent    = 'Selecione a estação de ' + (alvo === 'origem' ? 'origem' : 'destino');
    var busca = document.getElementById('modalEstacaoMov_busca');
    if (busca) { busca.value = ''; filtrarModalEstacao('modalEstacaoMov'); }
    $('#modalEstacaoMov').modal('show');
    setTimeout(function() { if (busca) busca.focus(); }, 300);
}

document.querySelectorAll('[data-buscar-estacao]').forEach(function(b) {
    b.addEventListener('click', function() { abrirModalEstacao(this.dataset.buscarEstacao); });
});

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-selecionar-estacao');
    if (!btn) return;
    var id = btn.dataset.id, nome = btn.dataset.nome;
    if (estacaoAlvo === 'origem') {
        document.getElementById('mv_origem_id').value      = id;
        document.getElementById('mv_origem_display').value = nome;
        document.getElementById('btnLimparOrigem').classList.remove('d-none');
    } else {
        document.getElementById('mv_destino_id').value      = id;
        document.getElementById('mv_destino_display').value = nome;
        document.getElementById('btnLimparDestino').classList.remove('d-none');
    }
    $('#modalEstacaoMov').modal('hide');
});

document.getElementById('btnLimparOrigem')?.addEventListener('click', function() {
    document.getElementById('mv_origem_id').value = '';
    document.getElementById('mv_origem_display').value = '';
    this.classList.add('d-none');
});
document.getElementById('btnLimparDestino')?.addEventListener('click', function() {
    document.getElementById('mv_destino_id').value = '';
    document.getElementById('mv_destino_display').value = '';
    this.classList.add('d-none');
});

// ── Veículo KM ───────────────────────────────────────────
const hiddenVeiculo = document.getElementById('mv_veiculo_id');
const kmHint        = document.getElementById('mv_veiculo_km_hint');
const kmBadge       = document.getElementById('mv_km_veiculo_badge');

function atualizarKmVeiculo(vid) {
    if (!vid) { if(kmHint) kmHint.textContent=''; if(kmBadge) kmBadge.style.display='none'; return; }
    fetch(URL_KM_VEICULO + '?veiculo_id=' + vid)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.kms !== null && data.kms !== undefined) {
                kmVeiculoAtual = parseFloat(data.kms);
                var fmt = new Intl.NumberFormat('pt-BR').format(kmVeiculoAtual);
                if(kmHint)  kmHint.textContent  = 'KM atual do veículo: ' + fmt + ' km';
                if(kmBadge) { kmBadge.textContent = 'KM atual: ' + fmt; kmBadge.style.display = ''; }
                var inp = document.getElementById('mv_km_saida');
                if (inp && !inp.value) inp.value = kmVeiculoAtual;
            } else {
                kmVeiculoAtual = null;
                if(kmHint)  kmHint.textContent = '';
                if(kmBadge) kmBadge.style.display = 'none';
            }
        }).catch(function() {});
}

if (hiddenVeiculo) {
    new MutationObserver(function() { atualizarKmVeiculo(hiddenVeiculo.value); })
        .observe(hiddenVeiculo, { attributes: true, attributeFilter: ['value'] });
    if (hiddenVeiculo.value) atualizarKmVeiculo(hiddenVeiculo.value);
}

document.getElementById('btnBuscarVeiculoMov')?.addEventListener('click', function() {
    $('#mov_modalBuscarVeiculo').modal('show');
});

document.getElementById('mv_km_saida')?.addEventListener('input', function() {
    var v = parseFloat(this.value);
    var erro = document.getElementById('mv_km_saida_erro');
    if(erro) erro.style.display = (kmVeiculoAtual !== null && !isNaN(v) && v < kmVeiculoAtual) ? 'block' : 'none';
});
document.getElementById('mv_km_chegada')?.addEventListener('input', function() {
    var chegada = parseFloat(this.value);
    var saida   = parseFloat(document.getElementById('mv_km_saida').value);
    var erro    = document.getElementById('mv_km_chegada_erro');
    if(erro) erro.style.display = (!isNaN(chegada) && !isNaN(saida) && chegada < saida) ? 'block' : 'none';
});

// ── Modal: Usuário ────────────────────────────────────────
document.getElementById('btnBuscarUsuarioMov')?.addEventListener('click', function() {
    ['_nome','_email','_id'].forEach(function(s){
        var el = document.getElementById('modalUsuarioMov'+s);
        if(el) el.value='';
    });
    filtrarModalUsuario('modalUsuarioMov');
    $('#modalUsuarioMov').modal('show');
    setTimeout(function() {
        var el = document.getElementById('modalUsuarioMov_nome');
        if(el) el.focus();
    }, 300);
});

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-selecionar-usuario');
    if (!btn) return;
    document.getElementById('mv_usuario_id').value      = btn.dataset.id;
    document.getElementById('mv_usuario_display').value = btn.dataset.nome;
    var clr = document.getElementById('btnLimparUsuarioMov');
    if (clr) clr.classList.remove('d-none');
    $('#modalUsuarioMov').modal('hide');
});

document.getElementById('btnLimparUsuarioMov')?.addEventListener('click', function() {
    document.getElementById('mv_usuario_id').value = '';
    document.getElementById('mv_usuario_display').value = '';
    this.classList.add('d-none');
});

// ── Modal: Reserva ────────────────────────────────────────
document.getElementById('btnBuscarReservaMov')?.addEventListener('click', function() {
    $('#modalReservaMov').modal('show');
});

document.getElementById('btnLimparReservaMov')?.addEventListener('click', function() {
    document.getElementById('mv_reserva_id').value = '';
    document.getElementById('mv_reserva_display').value = '';
    this.classList.add('d-none');
});

// ── Modal: OS ─────────────────────────────────────────────
document.getElementById('btnBuscarOsMov')?.addEventListener('click', function() {
    $('#modalOsMov').modal('show');
});

document.getElementById('btnLimparOsMov')?.addEventListener('click', function() {
    document.getElementById('mv_os_id').value = '';
    document.getElementById('mv_os_display').value = '';
    this.classList.add('d-none');
});

// ── Validação de datas ────────────────────────────────────
window.validarDatas = function() {
    var saida    = document.getElementById('mv_data_saida_hidden')?.value;
    var prevista = document.getElementById('mv_data_chegada_prevista_hidden');
    var real     = document.getElementById('mv_data_chegada_real_hidden');
    var erroPrev = document.getElementById('mv_erro_chegada_prevista');
    var erroReal = document.getElementById('mv_erro_chegada_real');

    if (prevista && erroPrev) {
        var invalido = saida && prevista.value && prevista.value <= saida;
        erroPrev.style.display = invalido ? '' : 'none';
    }
    if (real && erroReal) {
        var invalido2 = saida && real.value && real.value <= saida;
        erroReal.style.display = invalido2 ? '' : 'none';
    }
};

// Bloqueia submit se datas inválidas
document.getElementById('formMov')?.addEventListener('submit', function(e) {
    validarDatas();
    var erroPrev = document.getElementById('mv_erro_chegada_prevista');
    var erroReal = document.getElementById('mv_erro_chegada_real');
    if ((erroPrev && erroPrev.style.display !== 'none') ||
        (erroReal && erroReal.style.display !== 'none')) {
        e.preventDefault();
        setStep(4);
    }
}, true);

})();
</script>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
<script>
(function () {
'use strict';

const URL_HORARIOS = '/admin/estacao/{id}/disponibilidade-horarios';

// ──────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────

/** Converte "HH:MM" → minutos desde meia-noite */
function toMin(hhmm) {
    if (!hhmm) return 0;
    const [h, m] = hhmm.split(':').map(Number);
    return h * 60 + (m || 0);
}

/** Gera um array de strings "HH:MM" de 30 em 30 minutos entre abertura e fechamento,
    excluindo o intervalo de almoço quando aplicável. */
function gerarHorarios(info) {
    if (!info || !info.funciona) return [];
    if (info.funciona_24h) {
        // 00:00 → 23:30
        return gerarSlots('00:00', '23:30', null, null);
    }
    return gerarSlots(info.hora_abertura, info.hora_fechamento,
                      info.tem_almoco ? info.hora_inicio_almoco : null,
                      info.tem_almoco ? info.hora_fim_almoco    : null);
}

function gerarSlots(abertura, fechamento, almocoIni, almocoFim) {
    const inicio = toMin(abertura);
    const fim    = toMin(fechamento);
    const aIni   = almocoIni ? toMin(almocoIni) : null;
    const aFim   = almocoFim ? toMin(almocoFim) : null;
    const slots  = [];

    for (let m = inicio; m <= fim; m += 30) {
        if (aIni !== null && m >= aIni && m < aFim) continue; // intervalo almoço
        const hh = String(Math.floor(m / 60)).padStart(2, '0');
        const mm = String(m % 60).padStart(2, '0');
        slots.push(`${hh}:${mm}`);
    }
    return slots;
}

/** Preenche um <select> com as horas disponíveis */
function preencherSelect(selectEl, slots, valorAtual) {
    if (!selectEl) return;
    const prevVal = valorAtual || selectEl.value || '';
    selectEl.innerHTML = '<option value="">Hora</option>';
    slots.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s;
        opt.textContent = s;
        if (s === prevVal) opt.selected = true;
        selectEl.appendChild(opt);
    });
    if (!slots.length) {
        const opt = document.createElement('option');
        opt.value = ''; opt.disabled = true;
        opt.textContent = 'Estação fechada neste dia';
        selectEl.appendChild(opt);
    }
}

/** Atualiza o hidden field "YYYY-MM-DDTHH:mm" */
function syncHidden(hiddenId, dateStr, timeStr) {
    const hidden = document.getElementById(hiddenId);
    if (!hidden) return;
    if (dateStr && timeStr) {
        // dateStr vem como "DD/MM/AAAA" do flatpickr
        const [d, mo, y] = dateStr.split('/');
        hidden.value = `${y}-${mo}-${d}T${timeStr}`;
    } else {
        hidden.value = '';
    }
    validarDatas();
}

/** Converte "YYYY-MM-DDTHH:mm" → { date: "DD/MM/AAAA", time: "HH:MM" } */
function parseHidden(val) {
    if (!val) return { date: '', time: '' };
    const [datePart, timePart] = val.split('T');
    if (!datePart) return { date: '', time: timePart || '' };
    const [y, mo, d] = datePart.split('-');
    return { date: `${d}/${mo}/${y}`, time: (timePart || '').slice(0, 5) };
}

// ──────────────────────────────────────────────────────────────
// Cache de horários por estação
// ──────────────────────────────────────────────────────────────
const scheduleCache = {};

async function fetchSchedule(estacaoId) {
    if (!estacaoId) return null;
    if (scheduleCache[estacaoId]) return scheduleCache[estacaoId];
    try {
        const url = URL_HORARIOS.replace('{id}', estacaoId);
        const r = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!r.ok) return null;
        const data = await r.json();
        scheduleCache[estacaoId] = data.schedule || {};
        return scheduleCache[estacaoId];
    } catch (_) { return null; }
}

// ──────────────────────────────────────────────────────────────
// Flatpickr instances
// ──────────────────────────────────────────────────────────────
const pickers = {};

function criarPicker(inputId, opts = {}) {
    const el = document.getElementById(inputId);
    if (!el) return null;
    return flatpickr(el, {
        locale: 'pt',
        dateFormat: 'd/m/Y',
        allowInput: false,
        disableMobile: true,
        ...opts,
    });
}

/** Reconstrói o picker com dias desabilitados baseados no schedule */
function reconstruirPicker(inputId, schedule, onChangeCallback) {
    if (pickers[inputId]) { pickers[inputId].destroy(); }

    const disabledDays = [];
    for (let d = 0; d <= 6; d++) {
        const info = schedule[d];
        if (!info || !info.funciona) disabledDays.push(d);
    }

    pickers[inputId] = criarPicker(inputId, {
        disable: [
            function(date) {
                return disabledDays.includes(date.getDay());
            }
        ],
        onChange: function(selectedDates, dateStr) {
            if (onChangeCallback) onChangeCallback(selectedDates[0], dateStr);
        },
    });

    return pickers[inputId];
}

// ──────────────────────────────────────────────────────────────
// Slots de horário por dia selecionado
// ──────────────────────────────────────────────────────────────

function atualizarSelectHorario(selectId, schedule, dateObj, valorAtual) {
    const selectEl = document.getElementById(selectId);
    if (!selectEl) return;

    if (!dateObj || !schedule) {
        selectEl.innerHTML = '<option value="">Selecione uma data primeiro</option>';
        return;
    }

    const info  = schedule[dateObj.getDay()];
    const slots = gerarHorarios(info);
    preencherSelect(selectEl, slots, valorAtual);
}

// ──────────────────────────────────────────────────────────────
// Inicialização principal
// ──────────────────────────────────────────────────────────────

// Slots padrão (sem restrição de estação) — usado para Registro e campos sem origem/destino
const slotsLivres = gerarSlots('00:00', '23:30', null, null);

// ── Data de Registro (sem restrição de estação) ──
(function() {
    const hiddenId  = 'mv_data_registro_hidden';
    const dateId    = 'mv_data_registro_date';
    const timeId    = 'mv_data_registro_time';
    const initial   = parseHidden(document.getElementById(hiddenId)?.value);

    preencherSelect(document.getElementById(timeId), slotsLivres, initial.time);

    pickers[dateId] = criarPicker(dateId, {
        defaultDate: initial.date || null,
        onChange: function(_, dateStr) {
            syncHidden(hiddenId, dateStr, document.getElementById(timeId).value);
        },
    });
    if (initial.date) document.getElementById(dateId).value = initial.date;

    document.getElementById(timeId)?.addEventListener('change', function() {
        syncHidden(hiddenId, document.getElementById(dateId).value, this.value);
    });
})();

// ── Data de Saída (horário da estação de ORIGEM) ──
async function inicializarSaida(estacaoId) {
    const schedule = (await fetchSchedule(estacaoId)) || {};
    const hiddenId = 'mv_data_saida_hidden';
    const dateId   = 'mv_data_saida_date';
    const timeId   = 'mv_data_saida_time';
    const initial  = parseHidden(document.getElementById(hiddenId)?.value);

    reconstruirPicker(dateId, schedule, function(dateObj, dateStr) {
        atualizarSelectHorario(timeId, schedule, dateObj, '');
        syncHidden(hiddenId, dateStr, document.getElementById(timeId).value);
    });

    if (initial.date) {
        pickers[dateId]?.setDate(initial.date, true, 'd/m/Y');
        const [d, mo, y] = initial.date.split('/');
        const dt = new Date(+y, +mo - 1, +d);
        atualizarSelectHorario(timeId, schedule, dt, initial.time);
    } else {
        document.getElementById(timeId).innerHTML = '<option value="">Selecione uma data primeiro</option>';
    }

    document.getElementById(timeId)?.addEventListener('change', function() {
        syncHidden(hiddenId, document.getElementById(dateId).value, this.value);
    });

    // Aviso de horários da estação
    mostrarAviso(schedule, 'saida');
    atualizarHintOrigem(schedule, estacaoId);
}

// ── Chegada Prevista e Chegada Real (horário da estação de DESTINO) ──
async function inicializarChegadas(estacaoId) {
    const schedule = (await fetchSchedule(estacaoId)) || {};

    ['chegada_prevista', 'chegada_real'].forEach(slot => {
        const hiddenId = `mv_data_${slot}_hidden`;
        const dateId   = `mv_data_${slot}_date`;
        const timeId   = `mv_data_${slot}_time`;
        const initial  = parseHidden(document.getElementById(hiddenId)?.value);

        reconstruirPicker(dateId, schedule, function(dateObj, dateStr) {
            atualizarSelectHorario(timeId, schedule, dateObj, '');
            syncHidden(hiddenId, dateStr, document.getElementById(timeId).value);
            validarDatas();
        });

        if (initial.date) {
            pickers[dateId]?.setDate(initial.date, true, 'd/m/Y');
            const [d, mo, y] = initial.date.split('/');
            const dt = new Date(+y, +mo - 1, +d);
            atualizarSelectHorario(timeId, schedule, dt, initial.time);
        } else {
            document.getElementById(timeId).innerHTML = '<option value="">Selecione uma data primeiro</option>';
        }

        document.getElementById(timeId)?.addEventListener('change', function() {
            syncHidden(hiddenId, document.getElementById(dateId).value, this.value);
            validarDatas();
        });
    });

    atualizarHintDestino(schedule, estacaoId);
}

// ── Sem estação selecionada: pickers livres ──
function inicializarSemEstacao(slot) {
    const slots    = slotsLivres;
    const hiddenId = `mv_data_${slot}_hidden`;
    const dateId   = `mv_data_${slot}_date`;
    const timeId   = `mv_data_${slot}_time`;
    const initial  = parseHidden(document.getElementById(hiddenId)?.value);

    if (pickers[dateId]) pickers[dateId].destroy();
    pickers[dateId] = criarPicker(dateId, {
        defaultDate: initial.date || null,
        onChange: function(_, dateStr) {
            preencherSelect(document.getElementById(timeId), slots, document.getElementById(timeId).value);
            syncHidden(hiddenId, dateStr, document.getElementById(timeId).value);
        },
    });
    if (initial.date) document.getElementById(dateId).value = initial.date;

    preencherSelect(document.getElementById(timeId), slots, initial.time);

    document.getElementById(timeId)?.addEventListener('change', function() {
        syncHidden(hiddenId, document.getElementById(dateId).value, this.value);
    });
}

// ──────────────────────────────────────────────────────────────
// Aviso e hints de horário
// ──────────────────────────────────────────────────────────────

function mostrarAviso(schedule, _ctx) {
    const aviso = document.getElementById('mv_horario_aviso');
    const txt   = document.getElementById('mv_horario_aviso_txt');
    if (!aviso || !txt || !Object.keys(schedule).length) {
        aviso?.classList.add('d-none');
        return;
    }
    // Pega info do primeiro dia útil (Seg = 1)
    const info = schedule[1] || schedule[6] || schedule[0] || Object.values(schedule)[0];
    if (!info || !info.funciona) { aviso.classList.add('d-none'); return; }

    let msg = info.funciona_24h
        ? `Estação disponível 24h.`
        : `Horário de funcionamento: ${info.hora_abertura} às ${info.hora_fechamento}`;
    if (info.tem_almoco && !info.funciona_24h) {
        msg += ` (almoço: ${info.hora_inicio_almoco}–${info.hora_fim_almoco})`;
    }
    txt.textContent = msg;
    aviso.classList.remove('d-none');
}

function atualizarHintOrigem(schedule, estacaoId) {
    const hint = document.getElementById('mv_saida_origem_hint');
    if (!hint) return;
    if (!estacaoId || !Object.keys(schedule).length) { hint.textContent = ''; return; }
    const diasOk = [0,1,2,3,4,5,6].filter(d => schedule[d]?.funciona).length;
    hint.textContent = `${diasOk} dias/semana`;
}

function atualizarHintDestino(schedule, estacaoId) {
    const hint = document.getElementById('mv_chegada_destino_hint');
    if (!hint) return;
    if (!estacaoId || !Object.keys(schedule).length) { hint.textContent = ''; return; }
    const diasOk = [0,1,2,3,4,5,6].filter(d => schedule[d]?.funciona).length;
    hint.textContent = `${diasOk} dias/semana`;
}

// ──────────────────────────────────────────────────────────────
// Observa mudança de estação de origem e destino
// ──────────────────────────────────────────────────────────────

function getOrigemId()  { return document.getElementById('mv_origem_id')?.value || ''; }
function getDestinoId() { return document.getElementById('mv_destino_id')?.value || ''; }

async function aoMudarOrigem() {
    const id = getOrigemId();
    if (id) {
        await inicializarSaida(id);
    } else {
        inicializarSemEstacao('saida');
        document.getElementById('mv_horario_aviso')?.classList.add('d-none');
        const hint = document.getElementById('mv_saida_origem_hint');
        if (hint) hint.textContent = '';
    }
}

async function aoMudarDestino() {
    const id = getDestinoId();
    if (id) {
        await inicializarChegadas(id);
    } else {
        inicializarSemEstacao('chegada_prevista');
        inicializarSemEstacao('chegada_real');
        const hint = document.getElementById('mv_chegada_destino_hint');
        if (hint) hint.textContent = '';
    }
}

// Observa o hidden field de origem/destino com MutationObserver
function observarHidden(elementId, callback) {
    const el = document.getElementById(elementId);
    if (!el) return;
    let lastVal = el.value;
    const obs = new MutationObserver(() => {
        if (el.value !== lastVal) { lastVal = el.value; callback(); }
    });
    obs.observe(el, { attributes: true, attributeFilter: ['value'] });

    // Também observa via polling leve (para quando JS seta .value diretamente)
    setInterval(() => {
        if (el.value !== lastVal) { lastVal = el.value; callback(); }
    }, 400);
}

observarHidden('mv_origem_id',  aoMudarOrigem);
observarHidden('mv_destino_id', aoMudarDestino);

// ── Init imediato com valores já preenchidos ──
document.addEventListener('DOMContentLoaded', function() {
    // Registro (sem estação)
    // (já inicializado no IIFE acima)

    // Saída
    const origemId = getOrigemId();
    if (origemId) {
        inicializarSaida(origemId);
    } else {
        inicializarSemEstacao('saida');
    }

    // Chegadas
    const destinoId = getDestinoId();
    if (destinoId) {
        inicializarChegadas(destinoId);
    } else {
        inicializarSemEstacao('chegada_prevista');
        inicializarSemEstacao('chegada_real');
    }
});

})();
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'movimentacao-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.mv-guide', popover: { title: 'Guia de cenários', description: 'Os atalhos iniciais ajudam a escolher o cenário mais comum, como transferência entre estações, saída para reserva ou manutenção.', side: 'bottom' } },
        { element: '.mv-wizard-top', popover: { title: 'Formulário em 4 etapas', description: 'O cadastro é dividido em Tipo e Status, Veículo e Locais, Vínculos, e Datas e Observações. Avance etapa por etapa.', side: 'bottom' } },
        { element: '#tipo_movimentacao_id', popover: { title: 'Tipo da movimentação', description: 'Defina a natureza do deslocamento do veículo. A lupa ajuda a consultar os tipos disponíveis e suas descrições.', side: 'bottom' } },
        { element: '#status_movimentacao_id', popover: { title: 'Status da movimentação', description: 'Selecione o status inicial do registro para refletir a etapa atual do processo operacional.', side: 'bottom' } },
        { element: '#mv_veiculo_display', popover: { title: 'Veículo vinculado', description: 'Use a busca para localizar o veículo e aproveitar os controles de quilometragem e disponibilidade relacionados a ele.', side: 'bottom' } },
        { element: '#mv_origem_display, #mv_destino_display', popover: { title: 'Origem e destino', description: 'Informe as estações de saída e chegada. Esses vínculos controlam horários permitidos e o trajeto da movimentação.', side: 'bottom' } },
        { element: '#mv_usuario_display, #mv_reserva_display, #mv_os_display', popover: { title: 'Vínculos complementares', description: 'Associe o usuário responsável e, quando existir, a reserva ou a ordem de serviço relacionada à movimentação.', side: 'bottom' } },
        { element: '#mv_data_saida_date, #mv_data_chegada_prevista_date, #mv_horario_aviso', popover: { title: 'Datas e horários', description: 'Preencha saída, chegada prevista e chegada real conforme o funcionamento das estações selecionadas.', side: 'top' } },
        { element: '#mvSubmit', popover: { title: 'Salvar movimentação', description: 'Ao finalizar o preenchimento, salve para registrar a movimentação e disponibilizá-la na listagem.', side: 'top' } },
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/movimentacao/add-edit.blade.php ENDPATH**/ ?>