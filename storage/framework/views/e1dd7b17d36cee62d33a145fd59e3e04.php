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
    <div class="reserva-page mt-3">
        <div class="row">
            <div class="col-xl-8 col-lg-8">
                <div class="ci-card">
                    
                    
                    <div class="ci-card-header">
                        <div class="ci-header-left">
                            <div class="ci-header-icon">
                                <i class="fas fa-car-side"></i>
                            </div>
                            <div>
                                <div class="ci-header-eyebrow"><?php echo e(isset($reserva) ? 'EDITAR RESERVA APP' : 'NOVA RESERVA APP'); ?></div>
                                <h4 class="ci-header-title"><?php echo e($titulo); ?></h4>
                                <p class="ci-header-sub">
                                    <i class="fas fa-id-card mr-1"></i>Cadastro administrativo de motorista de aplicativo
                                </p>
                            </div>
                        </div>
                        <div class="ci-header-right">
                            <a href="<?php echo e(route('reserva.index')); ?>" class="ci-btn-back">
                                <i class="fas fa-arrow-left"></i>
                                <span>Voltar</span>
                            </a>
                        </div>
                    </div>

                    
                    <form action="<?php echo e(isset($reserva) ? route('reserva-motorista-app.update', $reserva->id) : route('reserva-motorista-app.store')); ?>" method="POST" id="formMotoristaApp">
                        <?php echo csrf_field(); ?>
                        <?php if(isset($reserva)): ?>
                            <?php echo method_field('PUT'); ?>
                        <?php endif; ?>

                        <div class="ci-body p-4">
                            <?php if($errors->any()): ?>
                                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 8px;">
                                    <h5 class="alert-heading font-weight-bold"><i class="fas fa-ban mr-2"></i> Por favor, corrija os erros abaixo:</h5>
                                    <ul class="mb-0">
                                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($error); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            
                            <div class="col-12 rw-step-indicator-wrap px-0 mb-4">
                                <div class="rw-step-indicator d-flex justify-content-between" role="tablist" aria-label="Passos da reserva">
                                    <button type="button" class="rw-step-btn is-active flex-fill text-center py-2" data-step="1">
                                        <span class="rw-step-num">1</span>
                                        <span class="rw-step-label"><i class="fas fa-calendar-alt mr-1"></i> Retirada & Período</span>
                                    </button>
                                    <div class="rw-step-divider align-self-center flex-grow-1" style="height:2px; background:#e2e9f4; margin:0 10px;"></div>
                                    <button type="button" class="rw-step-btn flex-fill text-center py-2" data-step="2" disabled>
                                        <span class="rw-step-num">2</span>
                                        <span class="rw-step-label"><i class="fas fa-car mr-1"></i> Planos & Veículo</span>
                                    </button>
                                    <div class="rw-step-divider align-self-center flex-grow-1" style="height:2px; background:#e2e9f4; margin:0 10px;"></div>
                                    <button type="button" class="rw-step-btn flex-fill text-center py-2" data-step="3" disabled>
                                        <span class="rw-step-num">3</span>
                                        <span class="rw-step-label"><i class="fas fa-puzzle-piece mr-1"></i> Opcionais</span>
                                    </button>
                                    <div class="rw-step-divider align-self-center flex-grow-1" style="height:2px; background:#e2e9f4; margin:0 10px;"></div>
                                    <button type="button" class="rw-step-btn flex-fill text-center py-2" data-step="4" disabled>
                                        <span class="rw-step-num">4</span>
                                        <span class="rw-step-label"><i class="fas fa-user mr-1"></i> Usuário & Docs</span>
                                    </button>
                                </div>
                            </div>

                            
                            <div class="wizard-step-content" id="step-1-content">
                                <div class="ci-block mb-4">
                                    <div class="ci-block-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div>
                                            <strong>Local de Retirada e Período</strong>
                                            <span>Selecione onde o carro será retirado e a data de início da vigência.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        
                                        <div class="col-md-12 form-group">
                                            <label for="estacao_retirada_id" class="font-weight-bold">Estação de Retirada <span class="text-danger">*</span></label>
                                            <select name="estacao_retirada_id" id="estacao_retirada_id" class="form-control select2" required>
                                                <option value="" disabled selected>Selecione a estação</option>
                                                <?php $__currentLoopData = $estacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($e->id); ?>" <?php echo e(old('estacao_retirada_id', $reserva->estacao_retirada_id ?? '') == $e->id ? 'selected' : ''); ?>>
                                                        <?php echo e($e->nome); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-md-4 form-group">
                                            <label for="data_inicio_flatpickr" class="font-weight-bold">Data de Início <span class="text-danger">*</span></label>
                                            <input type="text" id="data_inicio_flatpickr" class="form-control form-control-sm flatpickr-input active" placeholder="Clique para selecionar" readonly="readonly" value="<?php echo e(old('data_inicio', isset($reserva) && $reserva->data_inicio ? $reserva->data_inicio->format('d/m/Y') : date('d/m/Y'))); ?>">
                                            <input type="hidden" name="data_inicio" id="data_inicio" value="<?php echo e(old('data_inicio', isset($reserva) && $reserva->data_inicio ? $reserva->data_inicio->format('Y-m-d') : date('Y-m-d'))); ?>">
                                        </div>

                                        
                                        <div class="col-md-2 form-group">
                                            <label for="hora_inicio" class="font-weight-bold">Hora <span class="text-danger">*</span></label>
                                            <select name="hora_inicio" id="hora_inicio" class="form-control" required>
                                                <option value="09:00">09:00</option>
                                            </select>
                                            <small id="hora-loading" class="form-text text-muted d-none"><i class="fas fa-spinner fa-spin mr-1"></i>Carregando horários...</small>
                                        </div>

                                        <input type="hidden" name="data_fim" id="data_fim" value="<?php echo e(old('data_fim', isset($reserva) && $reserva->data_fim ? $reserva->data_fim->format('Y-m-d') : date('Y-m-d', strtotime('+7 days')))); ?>">
                                        <input type="hidden" id="quantidade_dias_exibicao" value="7">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary px-4 btn-next-step" data-target="2">
                                        Avançar <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>

                            
                            <div class="wizard-step-content d-none" id="step-2-content">
                                <div class="ci-block mb-4">
                                    <div class="ci-block-label">
                                        <i class="fas fa-car"></i>
                                        <div>
                                            <strong>Escolha do Plano e Veículo</strong>
                                            <span>Selecione o plano desejado. A duração e os valores serão ajustados de forma automática.</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-md-6 form-group">
                                            <label for="plano_motorista_app_id" class="font-weight-bold">Plano Contratado <span class="text-danger">*</span></label>
                                            <select name="plano_motorista_app_id" id="plano_motorista_app_id" class="form-control select2" required>
                                                <option value="" disabled selected>Selecione o plano</option>
                                                <?php $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($p->id); ?>" 
                                                        data-valor="<?php echo e($p->valor_periodo); ?>" 
                                                        data-calcao="<?php echo e($p->calcao); ?>"
                                                        data-periodo="<?php echo e($p->periodo); ?>"
                                                        data-grupos="<?php echo e(json_encode($p->gruposFrota->pluck('id')->toArray())); ?>"
                                                        data-opcionais="<?php echo e(json_encode($p->opcionais->map(fn($o) => ['id' => $o->id, 'nome' => $o->nome, 'tipo_cobranca' => $o->tipo_cobranca, 'preco' => (float)($o->pivot->valor ?? ($o->tipo_cobranca === 'por_dia' ? $o->preco_por_dia : $o->preco_integral) ?? 0)])->toArray())); ?>"
                                                        <?php echo e(old('plano_motorista_app_id', $reserva->plano_motorista_app_id ?? '') == $p->id ? 'selected' : ''); ?>>
                                                        <?php echo e($p->nome); ?> - R$ <?php echo e(number_format($p->valor_periodo, 2, ',', '.')); ?> / <?php echo e($p->periodo_label); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>

                                        
                                        <div class="col-md-6 form-group">
                                            <label for="veiculo_id" class="font-weight-bold">Veículo Vinculado</label>
                                            <div class="input-group">
                                                <select name="veiculo_id" id="veiculo_id" class="form-control select2">
                                                    <option value="" data-grupo-id="">Nenhum veículo vinculado (Definir na retirada)</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" id="btnSearchVeiculo" data-toggle="modal" data-target="#modalSearchVeiculo" title="Pesquisar Veículo" disabled>
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted" id="veiculo-plano-warning">Selecione a estação, datas, horários e plano primeiro para listar os veículos disponíveis.</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-md-4 form-group">
                                            <label for="valor_plano" class="font-weight-bold">Valor do Plano (Ciclo)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                                <input type="number" step="0.01" name="valor_plano" id="valor_plano" class="form-control bg-light" 
                                                    value="<?php echo e(old('valor_plano', $reserva->valor_plano ?? '')); ?>" readonly required placeholder="0,00">
                                            </div>
                                        </div>

                                        
                                        <div class="col-md-4 form-group">
                                            <label for="valor_caucao" class="font-weight-bold">Valor do Caução</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                                <input type="number" step="0.01" name="valor_caucao" id="valor_caucao" class="form-control bg-light" 
                                                    value="<?php echo e(old('valor_caucao', $reserva->valor_caucao ?? '')); ?>" readonly placeholder="0,00">
                                            </div>
                                        </div>

                                        
                                        <div class="col-md-4 form-group">
                                            <label for="status" class="font-weight-bold">Status da Reserva <span class="text-danger">*</span></label>
                                            <select id="status" class="form-control bg-light" disabled required>
                                                <option value="pendente_analise" <?php echo e(old('status', $reserva->status ?? 'pendente_analise') === 'pendente_analise' ? 'selected' : ''); ?>>Pendente de Análise</option>
                                                <option value="pendente_pagamento" <?php echo e(old('status', $reserva->status ?? 'pendente_analise') === 'pendente_pagamento' ? 'selected' : ''); ?>>Pendente de Pagamento</option>
                                                <option value="ativa" <?php echo e(old('status', $reserva->status ?? 'pendente_analise') === 'ativa' ? 'selected' : ''); ?>>Ativa</option>
                                                <option value="cancelada" <?php echo e(old('status', $reserva->status ?? 'pendente_analise') === 'cancelada' ? 'selected' : ''); ?>>Cancelada</option>
                                            </select>
                                            <input type="hidden" name="status" id="status_hidden" value="<?php echo e(old('status', $reserva->status ?? 'pendente_analise')); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary px-4 btn-prev-step" data-target="1">
                                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                                    </button>
                                    <button type="button" class="btn btn-primary px-4 btn-next-step" data-target="3">
                                        Avançar <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>

                            
                            <div class="wizard-step-content d-none" id="step-3-content">
                                <div class="ci-block mb-4">
                                    <div class="ci-block-label">
                                        <i class="fas fa-puzzle-piece"></i>
                                        <div>
                                            <strong>Serviços Opcionais</strong>
                                            <span>Selecione os opcionais vinculados a este plano.</span>
                                        </div>
                                    </div>

                                    <div id="opcionais-container" class="row p-3">
                                        <div class="col-12 text-center py-3 text-muted" id="opcionais-placeholder">
                                            Selecione um plano no passo anterior para carregar os opcionais.
                                        </div>
                                    </div>

                                    <div class="row mt-3 border-top pt-3">
                                        <div class="col-md-6 offset-md-6 text-right">
                                            <h5 class="font-weight-bold">Total Opcionais: <span class="text-indigo" id="opcionais-total-label">R$ 0,00</span></h5>
                                            <input type="hidden" name="valor_opcionais" id="valor_opcionais" value="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary px-4 btn-prev-step" data-target="2">
                                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                                    </button>
                                    <button type="button" class="btn btn-primary px-4 btn-next-step" data-target="4">
                                        Avançar <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>

                            
                            <div class="wizard-step-content d-none" id="step-4-content">
                                <div class="ci-block mb-4">
                                    <div class="ci-block-label">
                                        <i class="fas fa-user-check"></i>
                                        <div>
                                            <strong>Dados do Motorista e Validação</strong>
                                            <span>Vincule um motorista existente, crie um novo ou envie a documentação de validação.</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        
                                        <div class="col-md-12 form-group">
                                            <label for="user_id" class="font-weight-bold">Motorista (Cliente) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="user_id" id="user_id" class="form-control select2" required>
                                                    <option value="" disabled selected>Selecione o motorista</option>
                                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($u->id); ?>" <?php echo e(old('user_id', $reserva->user_id ?? '') == $u->id ? 'selected' : ''); ?>>
                                                            <?php echo e($u->nome); ?> (<?php echo e($u->email); ?>)
                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#modalSearchUser" title="Pesquisar Motorista">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-success" id="btnShowCreateUser" title="Adicionar Novo Motorista">
                                                        <i class="fas fa-plus"></i> Novo
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div id="divCreateUser" class="card card-body bg-white border mb-3 d-none">
                                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3"><i class="fas fa-user-plus mr-1"></i> Cadastrar Novo Motorista</h6>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="new_nome" class="font-weight-bold">Nome Completo</label>
                                                <input type="text" id="new_nome" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="new_email" class="font-weight-bold">E-mail</label>
                                                <input type="email" id="new_email" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label for="new_celular" class="font-weight-bold">Celular</label>
                                                <input type="text" id="new_celular" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label for="new_cpf" class="font-weight-bold">CPF</label>
                                                <input type="text" id="new_cpf" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <label for="new_cnh" class="font-weight-bold">Nº CNH</label>
                                                <input type="text" id="new_cnh" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-xs btn-secondary mr-2" id="btnCancelCreateUser">Cancelar</button>
                                            <button type="button" class="btn btn-xs btn-primary" id="btnSaveUser">Salvar Motorista</button>
                                        </div>
                                    </div>

                                    
                                    <div id="divStatusValidacao" class="card card-body bg-white border d-none">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                            <h6 class="font-weight-bold text-dark mb-0"><i class="fas fa-shield-alt mr-1"></i> Validação de Documentos</h6>
                                            <div>
                                                <span class="badge badge-secondary py-2 px-3 text-uppercase" id="spanStatusSolicitacao">Pendente</span>
                                                <button type="button" class="btn btn-xs btn-outline-primary ml-2" id="btnAtualizarStatus" title="Consultar status de aprovação atual">
                                                    <i class="fas fa-sync"></i> Atualizar Status
                                                </button>
                                            </div>
                                        </div>

                                        
                                        <div id="divUploadDocumentos">
                                            <p class="text-muted text-sm mb-3">Selecione e envie os documentos para iniciar o processo de análise de perfil.</p>
                                            <form id="formDocsAjax" enctype="multipart/form-data">
                                                <div class="row">
                                                    <div class="col-md-4 form-group">
                                                        <label class="font-weight-bold text-xs">CNH Frente</label>
                                                        <input type="file" id="doc_cnh_frente" name="doc_cnh_frente" class="form-control-file">
                                                        <div class="text-xs text-muted mt-1" id="link_cnh_frente"></div>
                                                    </div>
                                                    <div class="col-md-4 form-group">
                                                        <label class="font-weight-bold text-xs">CNH Verso</label>
                                                        <input type="file" id="doc_cnh_verso" name="doc_cnh_verso" class="form-control-file">
                                                        <div class="text-xs text-muted mt-1" id="link_cnh_verso"></div>
                                                    </div>
                                                    <div class="col-md-4 form-group">
                                                        <label class="font-weight-bold text-xs">Comprovante de Residência</label>
                                                        <input type="file" id="doc_comprovante_residencia" name="doc_comprovante_residencia" class="form-control-file">
                                                        <div class="text-xs text-muted mt-1" id="link_comprovante_residencia"></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-2">
                                                    <button type="button" class="btn btn-xs btn-info" id="btnUploadDocs">
                                                        <i class="fas fa-upload mr-1"></i> Salvar e Enviar Documentos
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary px-4 btn-prev-step" data-target="3">
                                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                                    </button>
                                    <button type="submit" class="btn btn-success px-5 font-weight-bold" style="background: var(--c-green); border-color: var(--c-green);">
                                        <i class="fas fa-check-circle mr-1"></i> Concluir e Criar Reserva
                                    </button>
                                </div>
                            </div>

                            
                            <input type="hidden" name="status_pagamento" value="<?php echo e($reserva->status_pagamento ?? 'pendente'); ?>">

                            
                            <div class="form-group mb-0 mt-3 d-none" id="observacoes-wrapper">
                                <label for="observacoes" class="font-weight-bold">Observações</label>
                                <textarea name="observacoes" id="observacoes" class="form-control" rows="3" placeholder="Informações gerenciais adicionais..."><?php echo e(old('observacoes', $reserva->observacoes ?? '')); ?></textarea>
                            </div>
                        </div>

                        
                        <div class="ci-body-footer d-none justify-content-between p-4 bg-white" style="border-top: 1px solid #e2e9f4; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                            <a href="<?php echo e(isset($reserva) ? route('reserva.show', ['reserva' => $reserva->codigoReserva?->codigo ?? $reserva->id]) : route('reserva.index')); ?>" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 8px;">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            
            <div class="col-xl-4 col-lg-4 reserva-sidebar">
                <?php $__env->startComponent('reserva.components.card_valores'); ?>
                <?php echo $__env->renderComponent(); ?>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalSearchUser" tabindex="-1" role="dialog" aria-labelledby="modalSearchUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold" id="modalSearchUserLabel"><i class="fas fa-search mr-2"></i>Buscar Motorista</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="text" id="inputSearchUser" class="form-control" placeholder="Digite o nome ou e-mail para filtrar...">
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped" id="tableSearchUser">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr data-name="<?php echo e(strtolower($u->nome)); ?>" data-email="<?php echo e(strtolower($u->email)); ?>">
                                        <td><strong><?php echo e($u->nome); ?></strong></td>
                                        <td><?php echo e($u->email); ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary btn-select-user" data-id="<?php echo e($u->id); ?>" data-dismiss="modal">
                                                Selecionar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="modalSearchVeiculo" tabindex="-1" role="dialog" aria-labelledby="modalSearchVeiculoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold" id="modalSearchVeiculoLabel"><i class="fas fa-search mr-2"></i>Buscar Veículo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <input type="text" id="inputSearchVeiculo" class="form-control" placeholder="Digite o modelo, placa ou cor para filtrar...">
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped" id="tableSearchVeiculo">
                            <thead>
                                <tr>
                                    <th>Modelo</th>
                                    <th>Placa</th>
                                    <th>Cor</th>
                                    <th class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tableSearchVeiculoBody">
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
    <?php echo $__env->make('reserva.partials.vistoria_reserva_estilos_inner', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <style>
        .reserva-page {
            --reserva-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 35%, #dbe3ef 65%);
            --reserva-title: var(--empresa-cor-primaria, #0f172a);
        }
        .ci-body { overflow: hidden; }
        .select2-container--bootstrap .select2-selection {
            border-radius: 8px !important;
            height: calc(1.5em + .75rem + 2px) !important;
        }
        .input-group > .select2-container--bootstrap {
            width: auto !important;
            flex: 1 1 auto !important;
        }
        .input-group > .select2-container--bootstrap .select2-selection {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
        .rw-step-btn {
            background: transparent;
            border: none;
            outline: none;
            transition: all 0.3s;
        }
        .rw-step-btn .rw-step-num {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            border-radius: 50%;
            background: #e2e9f4;
            color: #64748b;
            font-weight: bold;
            margin-right: 6px;
        }
        .rw-step-btn.is-active .rw-step-num {
            background: var(--c-indigo);
            color: #fff;
        }
        .rw-step-btn.is-active {
            font-weight: bold;
            color: var(--c-indigo);
        }
        .text-indigo {
            color: var(--c-indigo);
        }

        /* ===== Minha Reserva Sidebar Styles ===== */
        .minha-reserva-skeleton-overlay {
            position: absolute; inset: 0; z-index: 10;
            background: rgba(255,255,255,.96);
            border-radius: 18px;
            padding: 1.2rem 1.1rem;
            display: flex; flex-direction: column;
        }
        .skl-line {
            border-radius: 6px;
            background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: skl-shimmer 1.4s infinite;
            height: 12px;
        }
        .skl-line--sm  { height: 10px; }
        .skl-line--md  { height: 13px; }
        .skl-line--lg  { height: 18px; }
        .skl-line--xl  { height: 40px; border-radius: 10px; }
        .skl-line--25  { width: 25%; }
        .skl-line--30  { width: 30%; }
        .skl-line--40  { width: 40%; }
        .skl-line--50  { width: 50%; }
        .skl-line--60  { width: 60%; }
        .skl-line--70  { width: 70%; }
        .skl-line--80  { width: 80%; }
        .skl-line--90  { width: 90%; }
        .skl-line--100 { width: 100%; }
        .skl-spinner {
            display: flex; align-items: center; gap: .5rem;
            justify-content: center; margin-top: auto;
            font-size: .8rem; color: #94a3b8;
        }
        .skl-spinner i { font-size: 1rem; color: #cbd5e1; }
        @keyframes skl-shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        #minhaReservaCard { position: relative; }

        .minha-reserva-card {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
        }
        .minha-reserva-card__header {
            padding: 16px 16px 14px;
            background: linear-gradient(
                135deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 86%, #000 14%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 86%, #000 14%) 100%
            );
            color: var(--empresa-cor-primaria-contraste, #fff);
            position: relative;
        }
        .minha-reserva-card__header::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
        }
        .minha-reserva-card__title {
            display: flex;
            gap: 12px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .minha-reserva-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            color: var(--empresa-cor-primaria-contraste, #fff);
            backdrop-filter: blur(8px);
        }
        .minha-reserva-card__eyebrow {
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            opacity: .85;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .minha-reserva-card__name {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: -.02em;
            line-height: 1.15;
        }
        .minha-reserva-card__section {
            padding: 14px 16px 6px;
        }
        .minha-reserva-card__section + .minha-reserva-card__section {
            border-top: 1px solid #eef2f7;
        }
        .minha-reserva-card__section-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            font-weight: 900;
            color: color-mix(in srgb, var(--empresa-cor-primaria, #0f172a) 82%, #0f172a 18%);
            margin-bottom: 10px;
        }
        .minha-reserva-card__section-title i {
            color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 70%, #0f172a 30%);
        }
        .minha-reserva-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 8px;
        }
        .minha-reserva-list__item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid #eef2f7;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }
        .minha-reserva-list__label {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }
        .minha-reserva-list__value {
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
        }
        .minha-reserva-list__empty {
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px dashed #e2e8f0;
            color: #64748b;
            font-size: 12px;
            background: #f8fafc;
        }
        .minha-reserva-card__total {
            padding: 14px 16px 16px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: flex-end;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
        .minha-reserva-card__total-label {
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: .12em;
        }
        .minha-reserva-card__total-hint {
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
        }
        .minha-reserva-card__total-value {
            font-size: 22px;
            font-weight: 1000;
            letter-spacing: -.02em;
            color: #0f172a;
            text-align: right;
        }
        .minha-reserva-card__total-sub {
            margin-top: 4px;
            font-size: 12px;
            color: #334155;
            text-align: right;
            font-weight: 700;
        }
        .minha-reserva-card__total-sub .muted {
            color: #64748b;
            font-weight: 700;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script src="<?php echo e(asset('js/select2.min.js')); ?>"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap'
            });

            // Wizard Step Navigation
            let currentStep = 1;
            
            function showStep(stepNumber) {
                $('.wizard-step-content').addClass('d-none');
                $(`#step-${stepNumber}-content`).removeClass('d-none');
                
                // Indicators status
                $('.rw-step-btn').removeClass('is-active');
                for (let i = 1; i <= 4; i++) {
                    const btn = $(`.rw-step-btn[data-step="${i}"]`);
                    if (i === stepNumber) {
                        btn.addClass('is-active');
                    }
                    if (i <= stepNumber) {
                        btn.prop('disabled', false);
                    }
                }
                currentStep = stepNumber;
                
                // Show observations wrapper on step 4
                if (stepNumber === 4) {
                    $('#observacoes-wrapper').removeClass('d-none');
                } else {
                    $('#observacoes-wrapper').addClass('d-none');
                }
            }

            $('.btn-next-step').on('click', function() {
                const target = $(this).data('target');
                showStep(target);
            });

            $('.btn-prev-step').on('click', function() {
                const target = $(this).data('target');
                showStep(target);
            });

            $('.rw-step-btn').on('click', function() {
                const step = $(this).data('step');
                if (!$(this).prop('disabled')) {
                    showStep(step);
                }
            });

            // Modal user search logic
            $('#inputSearchUser').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#tableSearchUser tbody tr').filter(function() {
                    $(this).toggle(
                        $(this).data('name').indexOf(value) > -1 || 
                        $(this).data('email').indexOf(value) > -1
                    );
                });
            });

            $('.btn-select-user').on('click', function() {
                const userId = $(this).data('id');
                $('#user_id').val(userId).trigger('change');
            });

            // Create New Motorista Inline Toggle
            $('#btnShowCreateUser').on('click', function() {
                $('#divCreateUser').removeClass('d-none');
            });

            $('#btnCancelCreateUser').on('click', function() {
                $('#divCreateUser').addClass('d-none');
            });

            $('#btnSaveUser').on('click', function() {
                const nome = $('#new_nome').val();
                const email = $('#new_email').val();
                const celular = $('#new_celular').val();
                const cpf = $('#new_cpf').val();
                const cnh = $('#new_cnh').val();

                if (!nome || !email) {
                    alert('Nome e E-mail são obrigatórios!');
                    return;
                }

                $.ajax({
                    url: '<?php echo e(route("reserva-motorista-app.criar-motorista")); ?>',
                    method: 'POST',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>',
                        nome: nome,
                        email: email,
                        celular: celular,
                        numero_identificacao: cpf,
                        numero_carta_conducao: cnh
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add option to select
                            const newOption = new Option(response.user.nome + ' (' + response.user.email + ')', response.user.id, true, true);
                            $('#user_id').append(newOption).trigger('change');
                            
                            // Clear form
                            $('#new_nome').val('');
                            $('#new_email').val('');
                            $('#new_celular').val('');
                            $('#new_cpf').val('');
                            $('#new_cnh').val('');
                            $('#divCreateUser').addClass('d-none');
                            
                            alert('Motorista cadastrado e selecionado com sucesso!');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Erro ao criar o motorista.');
                    }
                });
            });

            // Document status check & document upload logic
            function fetchSolicitacaoStatus(userId) {
                if (!userId) return;
                
                $('#divStatusValidacao').removeClass('d-none');
                
                $.ajax({
                    url: '/admin/reserva-motorista-app/solicitacao-status/' + userId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const status = response.status;
                            const span = $('#spanStatusSolicitacao');
                            
                            span.text(status);
                            span.removeClass('badge-secondary badge-warning badge-success badge-danger');
                            
                            if (status === 'aprovado') {
                                span.addClass('badge-success');
                            } else if (status === 'rejeitado') {
                                span.addClass('badge-danger');
                            } else if (status === 'em_analise' || status === 'pendente') {
                                span.addClass('badge-warning');
                            } else {
                                span.addClass('badge-secondary').text('Sem Solicitação');
                            }
                            
                            // Populate links if documents exist
                            const docs = response.solicitacao?.documentos_json || {};
                            
                            if (docs.cnh_frente?.path) {
                                $('#link_cnh_frente').html('<a href="/storage/' + docs.cnh_frente.path + '" target="_blank"><i class="fas fa-file-download mr-1"></i>Ver CNH Frente</a>');
                            } else {
                                $('#link_cnh_frente').html('Não enviado');
                            }
                            
                            if (docs.cnh_verso?.path) {
                                $('#link_cnh_verso').html('<a href="/storage/' + docs.cnh_verso.path + '" target="_blank"><i class="fas fa-file-download mr-1"></i>Ver CNH Verso</a>');
                            } else {
                                $('#link_cnh_verso').html('Não enviado');
                            }
                            
                            if (docs.comprovante_residencia?.path) {
                                $('#link_comprovante_residencia').html('<a href="/storage/' + docs.comprovante_residencia.path + '" target="_blank"><i class="fas fa-file-download mr-1"></i>Ver Comprovante</a>');
                            } else {
                                $('#link_comprovante_residencia').html('Não enviado');
                            }
                        }
                    }
                });
            }

            $('#user_id').on('change', function() {
                const userId = $(this).val();
                fetchSolicitacaoStatus(userId);
            });

            $('#btnAtualizarStatus').on('click', function() {
                const userId = $('#user_id').val();
                fetchSolicitacaoStatus(userId);
            });

            // Upload Documents AJAX
            $('#btnUploadDocs').on('click', function() {
                const userId = $('#user_id').val();
                const planoId = $('#plano_motorista_app_id').val();
                if (!userId) {
                    alert('Selecione o motorista primeiro!');
                    return;
                }
                
                const formData = new FormData();
                formData.append('_token', '<?php echo e(csrf_token()); ?>');
                formData.append('user_id', userId);
                if (planoId) {
                    formData.append('plano_id', planoId);
                }
                
                const fileCnhFrente = $('#doc_cnh_frente')[0].files[0];
                const fileCnhVerso = $('#doc_cnh_verso')[0].files[0];
                const fileRes = $('#doc_comprovante_residencia')[0].files[0];
                
                if (fileCnhFrente) formData.append('doc_cnh_frente', fileCnhFrente);
                if (fileCnhVerso) formData.append('doc_cnh_verso', fileCnhVerso);
                if (fileRes) formData.append('doc_comprovante_residencia', fileRes);
                
                $.ajax({
                    url: '<?php echo e(route("reserva-motorista-app.salvar-documentos")); ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Documentos enviados com sucesso para análise!');
                            fetchSolicitacaoStatus(userId);
                            // Clear inputs
                            $('#doc_cnh_frente').val('');
                            $('#doc_cnh_verso').val('');
                            $('#doc_comprovante_residencia').val('');
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Erro ao enviar documentos.');
                    }
                });
            });

            // Dynamic vehicle filter and calculation logic based on Plano
            const planoSelect = $('#plano_motorista_app_id');
            const valorInput = $('#valor_plano');
            const caucaoInput = $('#valor_caucao');
            const veiculoSelect = $('#veiculo_id');
            const estacaoSelect = $('#estacao_retirada_id');
            const dataInicioInput = $('#data_inicio');
            const horaSelect = $('#hora_inicio');
            const dataFimInput = $('#data_fim');
            const diasExibicao = $('#quantidade_dias_exibicao');

            // Calculate Period in Days
            function getPeriodoEmDias(periodo) {
                switch(periodo) {
                    case 'semana': return 7;
                    case 'quinze': return 15;
                    case 'mes': return 30;
                    case 'trimestre': return 90;
                    case 'semestre': return 180;
                    case 'ano': return 365;
                    default: return 1;
                }
            }

            // Fetch Available hours from agenda
            function fetchHorariosDisponiveis() {
                const estacaoId = estacaoSelect.val();
                const dataInicio = dataInicioInput.val();
                
                if (!estacaoId || !dataInicio) return;
                
                $('#hora-loading').removeClass('d-none');
                
                $.ajax({
                    url: '<?php echo e(route("reserva.agendaDisponivelPorEstacao")); ?>',
                    method: 'GET',
                    data: {
                        estacao_id: estacaoId,
                        tipo: 'retirada',
                        data_base: dataInicio,
                        limite: 1
                    },
                    success: function(response) {
                        $('#hora-loading').addClass('d-none');
                        horaSelect.empty();
                        
                        if (response.agenda && response.agenda.length > 0 && response.agenda[0].slots) {
                            response.agenda[0].slots.forEach(slot => {
                                horaSelect.append(new Option(slot.label, slot.label));
                            });
                            horaSelect.trigger('change');
                        } else {
                            horaSelect.append(new Option('Sem horários', ''));
                            alert('Atenção: Não há horários de funcionamento disponíveis para a data selecionada nesta estação.');
                        }
                    },
                    error: function() {
                        $('#hora-loading').addClass('d-none');
                    }
                });
            }

            // Fetch Real Available Vehicles based on period and plan
            function fetchVeiculosDisponiveis() {
                const estacaoId = estacaoSelect.val();
                const dataInicio = dataInicioInput.val();
                const horaInicio = horaSelect.val();
                const dataFim = dataFimInput.val();
                const planoId = planoSelect.val();
                
                if (!estacaoId || !dataInicio || !horaInicio || !dataFim || !planoId) {
                    veiculoSelect.val('').trigger('change.select2');
                    veiculoSelect.prop('disabled', true);
                    $('#btnSearchVeiculo').prop('disabled', true);
                    return;
                }
                
                veiculoSelect.prop('disabled', false);
                $('#btnSearchVeiculo').prop('disabled', false);
                $('#veiculo-plano-warning').text('Verificando veículos disponíveis para o período...').removeClass('text-success text-danger').addClass('text-muted');
                
                $.ajax({
                    url: '<?php echo e(route("reserva-motorista-app.veiculos-disponiveis")); ?>',
                    method: 'GET',
                    data: {
                        estacao_retirada_id: estacaoId,
                        data_inicio: dataInicio,
                        hora_inicio: horaInicio,
                        data_fim: dataFim,
                        plano_id: planoId
                    },
                    success: function(response) {
                        veiculoSelect.empty();
                        veiculoSelect.append(new Option('Nenhum veículo vinculado (Definir na retirada)', ''));
                        
                        const tableBody = $('#tableSearchVeiculoBody');
                        tableBody.empty();
                        
                        if (response.success && response.veiculos && response.veiculos.length > 0) {
                            response.veiculos.forEach(v => {
                                const label = `${v.modelo?.nome || 'Veículo'} - Placa: ${v.placa_licenca} (${v.cor})`;
                                veiculoSelect.append(new Option(label, v.id));
                                
                                // Table Modal row
                                const rowHtml = `
                                    <tr class="veiculo-search-row" data-id="${v.id}" data-search="${(v.modelo?.nome || '').toLowerCase()} ${v.placa_licenca.toLowerCase()} ${v.cor.toLowerCase()}">
                                        <td><strong>${v.modelo?.nome || 'Veículo'}</strong></td>
                                        <td><span class="badge badge-secondary">${v.placa_licenca}</span></td>
                                        <td>${v.cor}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-primary btn-select-veiculo" data-id="${v.id}" data-dismiss="modal">
                                                Selecionar
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(rowHtml);
                            });
                            $('#veiculo-plano-warning').text('Veículos disponíveis encontrados e listados.').removeClass('text-muted text-danger').addClass('text-success');
                        } else {
                            $('#veiculo-plano-warning').text('Nenhum veículo disponível no período para este plano.').removeClass('text-muted text-success').addClass('text-danger');
                        }
                        veiculoSelect.trigger('change.select2');
                    }
                });
            }

            // Opcionais dynamic list logic
            function loadOpcionaisForPlano() {
                const selected = planoSelect.find(':selected');
                const container = $('#opcionais-container');
                
                if (!selected.val()) {
                    container.html('<div class="col-12 text-center py-3 text-muted">Selecione um plano no passo anterior para carregar os opcionais.</div>');
                    return;
                }
                
                let opcionais = [];
                try {
                    opcionais = selected.data('opcionais') || [];
                } catch(e) {
                    opcionais = [];
                }
                
                if (opcionais.length === 0) {
                    container.html('<div class="col-12 text-center py-3 text-muted"><i class="fas fa-info-circle mr-1"></i>Nenhum opcional vinculado a este plano.</div>');
                    return;
                }
                
                let html = '';
                opcionais.forEach(op => {
                    const labelPreco = op.tipo_cobranca === 'por_dia' ? `R$ ${parseFloat(op.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} / dia` : `R$ ${parseFloat(op.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="custom-control custom-checkbox p-3 border rounded bg-white shadow-sm h-100 d-flex align-items-center">
                                <input type="checkbox" class="custom-control-input chk-opcional" id="chk_op_${op.id}" data-id="${op.id}" data-preco="${op.preco}" data-tipo-cobranca="${op.tipo_cobranca}">
                                <label class="custom-control-label w-100 font-weight-bold" for="chk_op_${op.id}" style="cursor:pointer;">
                                    ${op.nome}
                                    <span class="d-block text-muted text-xs font-weight-normal">+ ${labelPreco}</span>
                                </label>
                            </div>
                        </div>
                    `;
                });
                
                container.html(html);
                recalcularOpcionais();
            }
            
            function recalcularOpcionais() {
                const dias = parseInt(diasExibicao.val()) || 1;
                let total = 0;
                $('.chk-opcional:checked').each(function() {
                    const tipo = $(this).data('tipo-cobranca');
                    const preco = parseFloat($(this).data('preco')) || 0;
                    if (tipo === 'por_dia') {
                        total += preco * dias;
                    } else {
                        total += preco;
                    }
                });
                
                $('#opcionais-total-label').text('R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#valor_opcionais').val(total.toFixed(2));
                atualizarResumoReserva();
            }
            
            $(document).on('change', '.chk-opcional', recalcularOpcionais);

            function atualizarResumoReserva() {
                $('#minhaReserva_periodo_section').show();
                
                // Retirada
                const estacaoNome = estacaoSelect.find(':selected').val() ? estacaoSelect.find(':selected').text().trim() : '—';
                $('#minhaReserva_estacao_retirada').text(estacaoNome);
                $('#minhaReserva_estacao_devolucao').text(estacaoNome);
                
                const dataInicioFormatada = $('#data_inicio_flatpickr').val() || '—';
                const horaInicio = horaSelect.val() || '00:00';
                $('#minhaReserva_data_retirada').text(dataInicioFormatada + ' às ' + horaInicio);
                
                // Devolução
                const dias = parseInt(diasExibicao.val()) || 0;
                
                let dataFimFormatada = '—';
                const dataFimRaw = dataFimInput.val();
                if (dataFimRaw) {
                    const parts = dataFimRaw.split('-');
                    dataFimFormatada = `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                
                $('#minhaReserva_data_devolucao').text(dataFimFormatada);
                $('#quantidade_dias').text('/ ' + dias + ' dias');
                
                // Tarifa Base
                const valorPlano = parseFloat(valorInput.val()) || 0;
                $('#tarifa_base').text('R$ ' + valorPlano.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                // Opcionais
                const opcionaisUl = $('#minhaReserva_opcionals_ul');
                opcionaisUl.empty();
                
                let totalOpcionais = 0;
                const checkedOpcionais = $('.chk-opcional:checked');
                
                if (checkedOpcionais.length === 0) {
                    opcionaisUl.append('<li class="minha-reserva-list__empty">Selecione opcionais para aparecerem aqui.</li>');
                } else {
                    checkedOpcionais.each(function() {
                        const name = $(this).closest('.custom-control').find('label').text().split('+')[0].trim();
                        const tipo = $(this).data('tipo-cobranca');
                        const preco = parseFloat($(this).data('preco')) || 0;
                        let val = preco;
                        if (tipo === 'por_dia') {
                            val = preco * dias;
                        }
                        totalOpcionais += val;
                        
                        opcionaisUl.append(`
                            <li class="minha-reserva-list__item">
                                <span class="minha-reserva-list__label">${name}</span>
                                <span class="minha-reserva-list__value">R$ ${val.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </li>
                        `);
                    });
                }
                
                // Totais
                const totalGeral = valorPlano + totalOpcionais;
                $('#valor_total_reserva').text('R$ ' + totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                const valorDiario = dias > 0 ? (totalGeral / dias) : 0;
                $('#valor_diario_reserva').text('R$ ' + valorDiario.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }

            // Calculate dates and days strictly based on plan's period
            function recalcularPeriodoPorPlano() {
                const selected = planoSelect.find(':selected');
                if (!selected.val()) return;

                const periodo = selected.data('periodo');
                const dias = getPeriodoEmDias(periodo);
                
                diasExibicao.val(dias);

                const dataInicioStr = dataInicioInput.val();
                if (dataInicioStr) {
                    const dataInicio = new Date(dataInicioStr + 'T12:00:00'); // avoiding timezone shift
                    const dataFim = new Date(dataInicio.getTime() + (dias * 24 * 60 * 60 * 1000));
                    
                    const ano = dataFim.getFullYear();
                    const mes = String(dataFim.getMonth() + 1).padStart(2, '0');
                    const dia = String(dataFim.getDate()).padStart(2, '0');
                    
                    dataFimInput.val(`${ano}-${mes}-${dia}`);
                }
                atualizarResumoReserva();
            }

            planoSelect.on('change', function() {
                const selected = planoSelect.find(':selected');
                const valor = selected.data('valor');
                const calcao = selected.data('calcao');

                if (valor !== undefined) {
                    valorInput.val(parseFloat(valor).toFixed(2));
                }
                if (calcao !== undefined) {
                    caucaoInput.val(parseFloat(calcao).toFixed(2));
                }

                recalcularPeriodoPorPlano();
                fetchVeiculosDisponiveis();
                loadOpcionaisForPlano();
            });

            // Initialize Flatpickr on data_inicio_flatpickr
            let pickerInicio = flatpickr("#data_inicio_flatpickr", {
                dateFormat: "d/m/Y",
                disableMobile: "true",
                locale: "pt",
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        const rawDate = instance.formatDate(selectedDates[0], "Y-m-d");
                        $('#data_inicio').val(rawDate);
                    } else {
                        $('#data_inicio').val('');
                    }
                    recalcularPeriodoPorPlano();
                    fetchHorariosDisponiveis();
                }
            });

            // Fetch available days for Flatpickr based on Estacao
            function fetchDiasDisponiveis() {
                const estacaoId = estacaoSelect.val();
                if (!estacaoId) return;

                $.ajax({
                    url: '/reserva/dias-disponiveis',
                    method: 'GET',
                    data: {
                        estacao_id: estacaoId,
                        dias: 90,
                        tipo: 'retirada'
                    },
                    success: function(response) {
                        if (response.dias_disponiveis) {
                            const diasHabilitados = response.dias_disponiveis.map(item => item.data);
                            pickerInicio.set('enable', [
                                function(date) {
                                    const dStr = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                                    return diasHabilitados.includes(dStr);
                                }
                            ]);
                            
                            // If current value is not in available days, clear it
                            const currentVal = dataInicioInput.val();
                            if (currentVal && !diasHabilitados.includes(currentVal)) {
                                pickerInicio.clear();
                            }
                        }
                    }
                });
            }



            // Listeners for Hour, Station and Start Date
            estacaoSelect.on('change', function() {
                fetchDiasDisponiveis();
                fetchHorariosDisponiveis();
                atualizarResumoReserva();
            });
            
            horaSelect.on('change', function() {
                fetchVeiculosDisponiveis();
                atualizarResumoReserva();
            });

            // Modal vehicle search logic (internal search list)
            $('#inputSearchVeiculo').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#tableSearchVeiculo tbody tr').each(function() {
                    const textMatch = $(this).data('search').indexOf(value) > -1;
                    if (textMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            $(document).on('click', '.btn-select-veiculo', function() {
                const veiculoId = $(this).data('id');
                veiculoSelect.val(veiculoId).trigger('change');
            });

            // Trigger filters initially
            fetchDiasDisponiveis();
            recalcularPeriodoPorPlano();
            fetchHorariosDisponiveis();
            loadOpcionaisForPlano();
            atualizarResumoReserva();
            
            if ($('#user_id').val()) {
                fetchSolicitacaoStatus($('#user_id').val());
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/add-edit-motorista-app.blade.php ENDPATH**/ ?>