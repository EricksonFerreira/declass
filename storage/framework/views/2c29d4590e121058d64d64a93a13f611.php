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
    <?php if($errors->any()): ?>
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-empresa shadow-sm border-0">
            <div class="card-header card-header-empresa border-0">
                <div>
                    <h4 class="mb-1"><?php echo e($titulo); ?></h4>
                    <p class="mb-0 text-muted">Configure os dados da empresa e as informações de pagamento.</p>
                </div>
                <?php if(isset($empresa)): ?>
                    <a href="<?php echo e(route('empresa.site-apresentacao.edit', $empresa->id)); ?>" class="btn btn-outline-primary btn-sm mt-2 mt-md-0">
                        Configurar site de apresentação
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body p-4">
            <?php if(!isset($empresa)): ?>
                <div class="alert alert-info py-2 rounded-lg">
                    Ao salvar, a nova empresa será criada usando a <strong>empresa-padrão</strong> como modelo de informações.
                </div>
            <?php endif; ?>
            <?php $isEdit = isset($empresa); ?>
            <?php if (isset($component)) { $__componentOriginal18ad2e0d264f9740dc73fff715357c28 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18ad2e0d264f9740dc73fff715357c28 = $attributes; } ?>
<?php $component = App\View\Components\Form::resolve(['method' => ''.e($metodo).'','rowClass' => 'row'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Form::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e($rota).'','enctype' => 'multipart/form-data']); ?>
                <div class="col-12 empresa-wizard-top">
                    <div class="empresa-wizard-steps" role="tablist" aria-label="Passos da empresa">
                        <button type="button" class="empresa-wizard-step is-active" data-empresa-step-btn="1">
                            <span class="num">1</span> Dados
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="2">
                            <span class="num">2</span> Contato
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="3">
                            <span class="num">3</span> Domínio
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="4">
                            <span class="num">4</span> Identidade
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="5">
                            <span class="num">5</span> Gateway
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="6">
                            <span class="num">6</span> Credenciais
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="7">
                            <span class="num">7</span> Fiscal
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="8">
                            <span class="num">8</span> App
                        </button>
                    </div>
                </div>

                <div class="col-12" data-empresa-step="1">
                    <div class="empresa-section-title" id="sec-dados">Dados da Empresa</div>
                </div>

                <div class="col-lg-8" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'nome','label' => 'Nome','value' => ''.e($empresa->nome ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'nome','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'ativo','label' => 'Status','options' => $status,'placeholder' => 'Selecione um valor','selected' => old(
                                'ativo',
                                isset($empresa->ativo) && $empresa->ativo ? $empresa->ativo : 'S',
                            )] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4b64953296fad43ab18567788f55889a)): ?>
<?php $attributes = $__attributesOriginal4b64953296fad43ab18567788f55889a; ?>
<?php unset($__attributesOriginal4b64953296fad43ab18567788f55889a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4b64953296fad43ab18567788f55889a)): ?>
<?php $component = $__componentOriginal4b64953296fad43ab18567788f55889a; ?>
<?php unset($__componentOriginal4b64953296fad43ab18567788f55889a); ?>
<?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'slug','label' => 'Slug','value' => ''.e($empresa->slug ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'slug','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'cnpj','label' => 'CNPJ','value' => ''.e($empresa->cnpj ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'cnpj']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="2">
                    <div class="empresa-section-title" id="sec-contato">Contato</div>
                </div>

                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'email','name' => 'email','label' => 'E-mail','value' => ''.e($empresa->email ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'email']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'telefone','label' => 'Telefone','value' => ''.e($empresa->telefone ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'telefone']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'representante','label' => 'Representante','value' => ''.e(old('representante', $empresa->representante ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'representante']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-12" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'endereco','label' => 'Endereço','value' => ''.e(old('endereco', $empresa->endereco ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'endereco']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="3">
                    <div class="empresa-section-title" id="sec-dominio">Domínio e Reserva</div>
                </div>

                <div class="row" data-empresa-step="3">
                    <div class="form-group form-group-empresa col-4">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'dominio','label' => 'Domínio','value' => ''.e($empresa->dominio ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'dominio']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                    <div class="form-group form-group-empresa col-4">
                        <?php
                            $siteApresentacaoAtivo = old('site_apresentacao_ativo', $empresa->site_apresentacao_ativo ?? 'S');
                        ?>
                        <label for="site_apresentacao_ativo">Site de vendas</label>
                        <select class="form-control form-control-sm" id="site_apresentacao_ativo" name="site_apresentacao_ativo">
                            <option value="S" <?php echo e($siteApresentacaoAtivo === 'S' ? 'selected' : ''); ?>>Sim, quero site de vendas</option>
                            <option value="N" <?php echo e($siteApresentacaoAtivo === 'N' ? 'selected' : ''); ?>>Não, mostrar login no domínio</option>
                        </select>
                        <small class="text-muted d-block mt-1">Se desativado, o domínio da empresa abre direto a página de login do sistema.</small>
                    </div>
                    <div class="form-group form-group-empresa col-4">
                        <label for="site_tipo_reserva">Tipo de reserva no site</label>
                        <?php
                            $siteTipoReserva = old('site_tipo_reserva', $empresa->site_tipo_reserva ?? 'ambas');
                        ?>
                        <select class="form-control form-control-sm" id="site_tipo_reserva" name="site_tipo_reserva">
                            <option value="ambas" <?php echo e($siteTipoReserva === 'ambas' ? 'selected' : ''); ?>>Ambas (Comum e Motorista App)</option>
                            <option value="reserva_comum" <?php echo e($siteTipoReserva === 'reserva_comum' ? 'selected' : ''); ?>>Reserva Comum</option>
                            <option value="motorista_app" <?php echo e($siteTipoReserva === 'motorista_app' ? 'selected' : ''); ?>>Motorista de App</option>
                            <option value="nenhum" <?php echo e($siteTipoReserva === 'nenhum' ? 'selected' : ''); ?>>Nenhum (Sem site de reservas)</option>
                        </select>
                        <small class="text-muted d-block mt-1">Define qual tipo de reserva ficará disponível na página de apresentação (Modelo 1). Selecione "Nenhum" caso não queira site de reservas.</small>
                    </div>
                    <div class="form-group form-group-empresa  col-3 ">
                        <label for="reserva_selecionar_modelo">Selecionar modelo na reserva</label>
                        <?php
                            $selecionarModeloReserva = old('reserva_selecionar_modelo', $empresa->reserva_selecionar_modelo ?? 'N');
                        ?>
                        <select class="form-control form-control-sm" id="reserva_selecionar_modelo" name="reserva_selecionar_modelo">
                            <option value="N" <?php echo e($selecionarModeloReserva === 'N' ? 'selected' : ''); ?>>Não</option>
                            <option value="S" <?php echo e($selecionarModeloReserva === 'S' ? 'selected' : ''); ?>>Sim</option>
                        </select>
                        <small class="text-muted d-block mt-1">Se "Não", o modelo será escolhido automaticamente após selecionar o grupo.</small>
                    </div>
                    <div class="form-group form-group-empresa  col-3 ">
                        <label for="reserva_atraso_max_minutos">Máximo de minutos de atraso para cancelamento automático</label>
                        <input
                            type="number"
                            class="form-control form-control-sm"
                            id="reserva_atraso_max_minutos"
                            name="reserva_atraso_max_minutos"
                            min="0"
                            value="<?php echo e(old('reserva_atraso_max_minutos', $empresa->reserva_atraso_max_minutos ?? 120)); ?>"
                        >
                        <small class="text-muted d-block mt-1">Após esse tempo, reservas atrasadas são canceladas e o valor pago vira saldo.</small>
                    </div>
                    <div class="form-group form-group-empresa  col-3">
                        <label for="reserva_bloqueio_pos_reserva_horas">Horas de indisponibilidade após o fim da reserva</label>
                        <input
                            type="number"
                            class="form-control form-control-sm"
                            id="reserva_bloqueio_pos_reserva_horas"
                            name="reserva_bloqueio_pos_reserva_horas"
                            min="0"
                            value="<?php echo e(old('reserva_bloqueio_pos_reserva_horas', $empresa->reserva_bloqueio_pos_reserva_horas ?? 0)); ?>"
                        >
                        <small class="text-muted d-block mt-1">Usado como "folga" para limpeza/revisão: o veículo só fica disponível para nova reserva após o fim + X horas.</small>
                    </div>
                    <div class="form-group form-group-empresa  col-3">
                        <label for="adicionar_novas_marcas_automaticamente">Adicionar novas marcas automaticamente</label>
                        <?php
                            $adicionarMarcasAutomaticamente = old('adicionar_novas_marcas_automaticamente', $empresa->adicionar_novas_marcas_automaticamente ?? 'S');
                        ?>
                        <select class="form-control form-control-sm" id="adicionar_novas_marcas_automaticamente" name="adicionar_novas_marcas_automaticamente">
                            <option value="S" <?php echo e($adicionarMarcasAutomaticamente === 'S' ? 'selected' : ''); ?>>Sim</option>
                            <option value="N" <?php echo e($adicionarMarcasAutomaticamente === 'N' ? 'selected' : ''); ?>>Não</option>
                        </select>
                        <small class="text-muted d-block mt-1">Quando ativo, a sincronização FIPE replica novas marcas para esta empresa.</small>
                    </div>
                    <div class="form-group form-group-empresa  col-3">
                        <label for="adicionar_novos_modelos_automaticamente">Adicionar novos modelos automaticamente</label>
                        <?php
                            $adicionarModelosAutomaticamente = old('adicionar_novos_modelos_automaticamente', $empresa->adicionar_novos_modelos_automaticamente ?? 'S');
                        ?>
                        <select class="form-control form-control-sm" id="adicionar_novos_modelos_automaticamente" name="adicionar_novos_modelos_automaticamente">
                            <option value="S" <?php echo e($adicionarModelosAutomaticamente === 'S' ? 'selected' : ''); ?>>Sim</option>
                            <option value="N" <?php echo e($adicionarModelosAutomaticamente === 'N' ? 'selected' : ''); ?>>Não</option>
                        </select>
                        <small class="text-muted d-block mt-1">Quando ativo, a sincronização FIPE replica novos modelos para esta empresa.</small>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="4">
                    <div class="empresa-section-title" id="sec-identidade">Identidade Visual da Empresa</div>
                </div>

                <div class="col-lg-5" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="logo">Logo da empresa</label>
                        <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                        <small class="text-muted d-block mt-1">Formatos: JPG, PNG, WEBP ou SVG. Tamanho máximo: 2 MB.</small>
                    </div>
                </div>
                <div class="col-lg-7" data-empresa-step="4">
                    <div
                        class="empresa-logo-preview-wrap"
                        style="background: <?php echo e(old('cor_primaria', $empresa->cor_primaria ?? '#1F2937')); ?>;"
                    >
                        <?php if(isset($empresa) && $empresa->logo_url): ?>
                            <img src="<?php echo e($empresa->logo_url); ?>" alt="Logo atual" class="empresa-logo-preview">
                        <?php else: ?>
                            <div class="empresa-logo-placeholder">Sem logo cadastrada</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_primaria">Cor primária <small>Cor de fundo</small></label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_primaria"
                            name="cor_primaria"
                            value="<?php echo e(old('cor_primaria', $empresa->cor_primaria ?? '#1F2937')); ?>"
                        >
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_secundaria">Cor secundária <small>Cor das fontes</small></label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_secundaria"
                            name="cor_secundaria"
                            value="<?php echo e(old('cor_secundaria', $empresa->cor_secundaria ?? '#334155')); ?>"
                        >
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_terciaria">Cor terciária</label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_terciaria"
                            name="cor_terciaria"
                            value="<?php echo e(old('cor_terciaria', $empresa->cor_terciaria ?? '#64748B')); ?>"
                        >
                    </div>
                </div>

                <div class="col-12" data-empresa-step="4">
                    <div class="empresa-theme-preview" id="empresa-theme-preview"></div>
                    <small class="text-muted">Prévia aproximada das cores para fundo/realces no acesso do colaborador.</small>
                </div>

                <div class="col-12 mt-2" data-empresa-step="5">
                    <div class="empresa-section-title" id="sec-faturamento">Faturamento e Gateway</div>
                </div>

                <div class="col-lg-8" data-empresa-step="5">
                    <div class="form-group form-group-empresa">
                        <label for="gateway_pagamento">Gateway de pagamento padrão</label>
                        <select name="gateway_pagamento" id="gateway_pagamento" class="form-control form-control-sm">
                        <?php
                            $gatewaySelecionado = old('gateway_pagamento', $empresa->gateway_pagamento ?? 'stripe');
                        ?>
                        <option value="nenhum" <?php echo e($gatewaySelecionado === 'nenhum' ? 'selected' : ''); ?>>Nenhum gateway</option>
                        <option value="stripe" <?php echo e($gatewaySelecionado === 'stripe' ? 'selected' : ''); ?>>Stripe</option>
                        <option value="mercado_livre" <?php echo e($gatewaySelecionado === 'mercado_livre' ? 'selected' : ''); ?>>Mercado Livre</option>
                        <option value="paypal" <?php echo e($gatewaySelecionado === 'paypal' ? 'selected' : ''); ?>>PayPal</option>
                    </select>
                        <small class="text-muted d-block mt-1">Se selecionar "Nenhum", a reserva seguirá sem etapa de pagamento online e o cliente será levado direto para suas reservas.</small>
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="5">
                    <div class="form-group form-group-empresa">
                        <label for="moeda_pagamento">Moeda de pagamento</label>
                        <?php
                            $moedaSelecionada = strtoupper(old('moeda_pagamento', $empresa->moeda_pagamento ?? 'BRL'));
                        ?>
                        <select id="moeda_pagamento" name="moeda_pagamento" class="form-control form-control-sm">
                            <option value="BRL" <?php echo e($moedaSelecionada === 'BRL' ? 'selected' : ''); ?>>BRL - Real Brasileiro</option>
                            <option value="USD" <?php echo e($moedaSelecionada === 'USD' ? 'selected' : ''); ?>>USD - Dolar Americano</option>
                            <option value="EUR" <?php echo e($moedaSelecionada === 'EUR' ? 'selected' : ''); ?>>EUR - Euro</option>
                        </select>
                    </div>
                </div>

                <div class="col-12" data-empresa-step="5">
                    <?php
                        $calcaoAtivo = old('calcao_ativo', isset($calcaoConfigEmpresa) && $calcaoConfigEmpresa?->ativo ? 'S' : 'N');
                        $calcaoTipoRegra = old('calcao_tipo_regra_valor', $calcaoConfigEmpresa->tipo_regra_valor ?? 'fixo');
                    ?>
                    <div class="empresa-subsection empresa-subsection--calcao">
                        <div class="empresa-subsection__head">
                            <div>
                                <h5>Calção</h5>
                                <p>Defina como o sistema calcula o calção da empresa e, se precisar, crie exceções por grupo de frota.</p>
                            </div>
                            <div class="empresa-subsection__badge">
                                <i class="fas fa-shield-alt"></i> Prioridade: modelo > grupo > empresa
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group form-group-empresa">
                                    <label for="calcao_ativo">Usar calção</label>
                                    <select class="form-control form-control-sm" id="calcao_ativo" name="calcao_ativo">
                                        <option value="N" <?php echo e($calcaoAtivo === 'N' ? 'selected' : ''); ?>>Não</option>
                                        <option value="S" <?php echo e($calcaoAtivo === 'S' ? 'selected' : ''); ?>>Sim</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Se desativado, a reserva não exigirá calção.</small>
                                </div>
                            </div>
                        </div>

                        <div id="calcao-config-wrap" class="<?php echo e($calcaoAtivo === 'S' ? '' : 'd-none'); ?>">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group form-group-empresa">
                                        <label for="calcao_tipo_regra_valor">Tipo de cálculo</label>
                                        <select class="form-control form-control-sm" id="calcao_tipo_regra_valor" name="calcao_tipo_regra_valor">
                                            <option value="fixo" <?php echo e($calcaoTipoRegra === 'fixo' ? 'selected' : ''); ?>>Valor fixo</option>
                                            <option value="percentual" <?php echo e($calcaoTipoRegra === 'percentual' ? 'selected' : ''); ?>>Percentual da reserva</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-empresa">
                                        <label for="calcao_valor">Valor padrão</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="calcao_valor" name="calcao_valor"
                                               value="<?php echo e(old('calcao_valor', $calcaoConfigEmpresa->valor ?? '')); ?>">
                                        <small class="text-muted d-block mt-1">Informe em reais ou em %.</small>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-empresa">
                                        <label for="calcao_valor_minimo">Valor mínimo</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="calcao_valor_minimo" name="calcao_valor_minimo"
                                               value="<?php echo e(old('calcao_valor_minimo', $calcaoConfigEmpresa->valor_minimo ?? '')); ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-empresa">
                                        <label for="calcao_valor_maximo">Valor máximo</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="calcao_valor_maximo" name="calcao_valor_maximo"
                                               value="<?php echo e(old('calcao_valor_maximo', $calcaoConfigEmpresa->valor_maximo ?? '')); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="empresa-calcao-help">
                                <strong>Regras do calção:</strong> você escolhe se o valor será <strong>fixo</strong> ou um <strong>percentual da reserva</strong>. Se existir uma exceção por grupo, ela sobrescreve a regra padrão da empresa. No futuro, uma regra por modelo pode sobrescrever as duas.
                            </div>

                            <?php if(isset($empresa) && isset($gruposFrota) && $gruposFrota->isNotEmpty()): ?>
                                <div class="empresa-calcao-excecoes mt-3">
                                    <div class="empresa-calcao-excecoes__title">Exceções por grupo de frota</div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover empresa-calcao-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Grupo</th>
                                                    <th>Usar exceção</th>
                                                    <th>Tipo</th>
                                                    <th>Valor</th>
                                                    <th>Mínimo</th>
                                                    <th>Máximo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $gruposFrota; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $configGrupo = $calcaoConfigsGrupo[$grupo->id] ?? null;
                                                        $grupoOld = old('calcao_grupos.' . $grupo->id, []);
                                                        $grupoAtivo = $grupoOld['ativo'] ?? (($configGrupo && $configGrupo->ativo) ? 'S' : 'N');
                                                        $grupoTipo = $grupoOld['tipo_regra_valor'] ?? ($configGrupo->tipo_regra_valor ?? 'fixo');
                                                        $grupoValor = $grupoOld['valor'] ?? ($configGrupo->valor ?? '');
                                                        $grupoMin = $grupoOld['valor_minimo'] ?? ($configGrupo->valor_minimo ?? '');
                                                        $grupoMax = $grupoOld['valor_maximo'] ?? ($configGrupo->valor_maximo ?? '');
                                                    ?>
                                                    <tr>
                                                        <td class="align-middle font-weight-600"><?php echo e($grupo->nome); ?></td>
                                                        <td>
                                                            <select class="form-control form-control-sm" name="calcao_grupos[<?php echo e($grupo->id); ?>][ativo]">
                                                                <option value="N" <?php echo e($grupoAtivo === 'N' ? 'selected' : ''); ?>>Não</option>
                                                                <option value="S" <?php echo e($grupoAtivo === 'S' ? 'selected' : ''); ?>>Sim</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-control form-control-sm" name="calcao_grupos[<?php echo e($grupo->id); ?>][tipo_regra_valor]">
                                                                <option value="fixo" <?php echo e($grupoTipo === 'fixo' ? 'selected' : ''); ?>>Fixo</option>
                                                                <option value="percentual" <?php echo e($grupoTipo === 'percentual' ? 'selected' : ''); ?>>%</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                                                   name="calcao_grupos[<?php echo e($grupo->id); ?>][valor]" value="<?php echo e($grupoValor); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                                                   name="calcao_grupos[<?php echo e($grupo->id); ?>][valor_minimo]" value="<?php echo e($grupoMin); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                                                   name="calcao_grupos[<?php echo e($grupo->id); ?>][valor_maximo]" value="<?php echo e($grupoMax); ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php elseif(!isset($empresa)): ?>
                                <div class="empresa-calcao-help mt-2">
                                    As exceções por grupo podem ser definidas após salvar a empresa, quando os grupos já estiverem disponíveis.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="6">
                    <div class="empresa-section-title" id="sec-credenciais">Credenciais do Provedor</div>
                </div>

                <div class="col-12 gateway-fields gateway-stripe" data-empresa-step="6">
                    <div class="gateway-title">Stripe</div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-stripe" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="stripe_public_key">Stripe Public Key</label>
                        <input type="text" class="form-control form-control-sm" id="stripe_public_key" name="stripe_public_key"
                               value="<?php echo e(old('stripe_public_key', $empresa->stripe_public_key ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-stripe" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="stripe_secret">Stripe Secret</label>
                        <input type="text" class="form-control form-control-sm" id="stripe_secret" name="stripe_secret"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>

                <div class="col-12 gateway-fields gateway-mercado_livre" data-empresa-step="6">
                    <div class="gateway-title">Mercado Livre</div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-mercado_livre" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="mercado_livre_public_key">Mercado Livre Public Key</label>
                        <input type="text" class="form-control form-control-sm" id="mercado_livre_public_key" name="mercado_livre_public_key"
                               value="<?php echo e(old('mercado_livre_public_key', $empresa->mercado_livre_public_key ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-mercado_livre" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="mercado_livre_access_token">Mercado Livre Access Token</label>
                        <input type="text" class="form-control form-control-sm" id="mercado_livre_access_token" name="mercado_livre_access_token"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o token atual.</small>
                    </div>
                </div>

                <div class="col-12 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="gateway-title">PayPal</div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_client_id">PayPal Client ID</label>
                        <input type="text" class="form-control form-control-sm" id="paypal_client_id" name="paypal_client_id"
                               value="<?php echo e(old('paypal_client_id', $empresa->paypal_client_id ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_client_secret">PayPal Client Secret</label>
                        <input type="text" class="form-control form-control-sm" id="paypal_client_secret" name="paypal_client_secret"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_mode">PayPal Mode</label>
                        <select id="paypal_mode" name="paypal_mode" class="form-control form-control-sm">
                            <?php $paypalMode = old('paypal_mode', $empresa->paypal_mode ?? 'sandbox'); ?>
                            <option value="sandbox" <?php echo e($paypalMode === 'sandbox' ? 'selected' : ''); ?>>Sandbox</option>
                            <option value="live" <?php echo e($paypalMode === 'live' ? 'selected' : ''); ?>>Live</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="7">
                    <div class="empresa-section-title" id="sec-fiscal">Fiscal - NFS-e (Nuvem Fiscal)</div>
                </div>

                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_ativo">Emitir NFS-e</label>
                        <?php $nfseAtivo = old('nfse_ativo', $empresa->nfse_ativo ?? 'N'); ?>
                        <select id="nfse_ativo" name="nfse_ativo" class="form-control form-control-sm">
                            <option value="N" <?php echo e($nfseAtivo === 'N' ? 'selected' : ''); ?>>Não</option>
                            <option value="S" <?php echo e($nfseAtivo === 'S' ? 'selected' : ''); ?>>Sim</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_ambiente">Ambiente</label>
                        <?php $nfseAmbiente = old('nfse_ambiente', $empresa->nfse_ambiente ?? 'homologacao'); ?>
                        <select id="nfse_ambiente" name="nfse_ambiente" class="form-control form-control-sm">
                            <option value="homologacao" <?php echo e($nfseAmbiente === 'homologacao' ? 'selected' : ''); ?>>Homologação</option>
                            <option value="producao" <?php echo e($nfseAmbiente === 'producao' ? 'selected' : ''); ?>>Produção</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_provedor">Provedor</label>
                        <?php $nfseProvedor = old('nfse_provedor', $empresa->nfse_provedor ?? 'nuvemfiscal'); ?>
                        <select id="nfse_provedor" name="nfse_provedor" class="form-control form-control-sm">
                            <option value="nuvemfiscal" <?php echo e($nfseProvedor === 'nuvemfiscal' ? 'selected' : ''); ?>>Nuvem Fiscal (teste)</option>
                            <option value="nfephp" <?php echo e($nfseProvedor === 'nfephp' ? 'selected' : ''); ?>>NFePHP (final)</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_inscricao_municipal">Inscrição municipal</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_inscricao_municipal" name="nfse_inscricao_municipal"
                               value="<?php echo e(old('nfse_inscricao_municipal', $empresa->nfse_inscricao_municipal ?? '')); ?>">
                    </div>
                </div>

                <div class="col-lg-6" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_client_id">Nuvem Fiscal Client ID</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_client_id" name="nfse_client_id"
                               value="<?php echo e(old('nfse_client_id', $empresa->nfse_client_id ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_client_secret">Nuvem Fiscal Client Secret</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_client_secret" name="nfse_client_secret" value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_municipio_codigo">Código do município (IBGE)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_municipio_codigo" name="nfse_municipio_codigo"
                               value="<?php echo e(old('nfse_municipio_codigo', $empresa->nfse_municipio_codigo ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_codigo_servico">Código do serviço</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_codigo_servico" name="nfse_codigo_servico"
                               value="<?php echo e(old('nfse_codigo_servico', $empresa->nfse_codigo_servico ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_cnae">CNAE (opcional)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_cnae" name="nfse_cnae"
                               value="<?php echo e(old('nfse_cnae', $empresa->nfse_cnae ?? '')); ?>">
                    </div>
                </div>

                <div class="col-lg-8" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_descricao_servico">Descrição do serviço</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_descricao_servico" name="nfse_descricao_servico"
                               value="<?php echo e(old('nfse_descricao_servico', $empresa->nfse_descricao_servico ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_aliquota">Alíquota ISS (%)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="nfse_aliquota" name="nfse_aliquota"
                               value="<?php echo e(old('nfse_aliquota', $empresa->nfse_aliquota ?? '')); ?>">
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_tributacao_issqn">Tributação ISSQN</label>
                        <?php $trib = (string) old('nfse_tributacao_issqn', $empresa->nfse_tributacao_issqn ?? '1'); ?>
                        <select id="nfse_tributacao_issqn" name="nfse_tributacao_issqn" class="form-control form-control-sm">
                            <option value="1" <?php echo e($trib === '1' ? 'selected' : ''); ?>>1 - Tributação no município</option>
                            <option value="2" <?php echo e($trib === '2' ? 'selected' : ''); ?>>2 - Tributação fora do município</option>
                            <option value="3" <?php echo e($trib === '3' ? 'selected' : ''); ?>>3 - Isento</option>
                            <option value="4" <?php echo e($trib === '4' ? 'selected' : ''); ?>>4 - Imune</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_tomador_numero_padrao">Número do tomador (padrão)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_tomador_numero_padrao" name="nfse_tomador_numero_padrao"
                               value="<?php echo e(old('nfse_tomador_numero_padrao', $empresa->nfse_tomador_numero_padrao ?? '')); ?>">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_tomador_bairro_padrao">Bairro do tomador (padrão)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_tomador_bairro_padrao" name="nfse_tomador_bairro_padrao"
                               value="<?php echo e(old('nfse_tomador_bairro_padrao', $empresa->nfse_tomador_bairro_padrao ?? '')); ?>">
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="8">
                    <div class="empresa-section-title" id="sec-app">Motorista de App</div>
                </div>

                <input type="hidden" name="aluguel_app_ativo" id="aluguel_app_ativo" value="<?php echo e($empresa->aluguel_app_ativo ?? 'N'); ?>">

                <div class="col-lg-4" data-empresa-step="8">
                    <div class="form-group form-group-empresa">
                        <label for="aluguel_app_exigir_aprovacao_documentos">Exigir aprovação dos documentos?</label>
                        <?php
                            $exigirAprovacao = old('aluguel_app_exigir_aprovacao_documentos', $empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N');
                        ?>
                        <select class="form-control form-control-sm" id="aluguel_app_exigir_aprovacao_documentos" name="aluguel_app_exigir_aprovacao_documentos">
                            <option value="N" <?php echo e($exigirAprovacao === 'N' ? 'selected' : ''); ?>>Não</option>
                            <option value="S" <?php echo e($exigirAprovacao === 'S' ? 'selected' : ''); ?>>Sim</option>
                        </select>
                        <small class="text-muted d-block mt-1">Quando ativo, a locadora precisa aprovar o cadastro antes da liberação.</small>
                    </div>
                </div>

             

                <div class="col-12" data-empresa-step="8">
                    <?php
                        $docsApp = old('aluguel_app_documentos_obrigatorios', $empresa->aluguel_app_documentos_obrigatorios ?? []);
                        if (!is_array($docsApp)) {
                            $docsApp = [];
                        }
                        $docsApp = array_values(array_filter($docsApp, fn ($item) => is_string($item) && trim($item) !== ''));
                        $camposPersonalizados = old('aluguel_app_campos_personalizados', $empresa->aluguel_app_campos_personalizados ?? []);
                        if (!is_array($camposPersonalizados)) {
                            $camposPersonalizados = [];
                        }
                        $camposPersonalizados = array_values(array_filter($camposPersonalizados, fn ($item) => is_array($item)));
                        $opcoesDocs = [
                        ];
                    ?>
                    <div class="empresa-subsection empresa-subsection--calcao">
                        <div class="motorista-app-custom-fields">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:10px;">
                                <div>
                                    <h6 class="mb-1" style="font-weight:700;color:#1f334d;">Documentos do motorista de app</h6>
                                    <small class="text-muted">Estes campos ficam disponíveis apenas para esta empresa.</small>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" id="btnAddMotoristaCampo">
                                    <i class="fas fa-plus mr-1"></i>Adicionar campo
                                </button>
                            </div>

                            <input type="hidden" name="aluguel_app_campos_personalizados" id="aluguel_app_campos_personalizados" value='<?php echo json_encode($camposPersonalizados, JSON_UNESCAPED_UNICODE, 512) ?>'>

                            <div id="motoristaCustomFieldsList" class="motorista-app-custom-fields__list"></div>
                        </div>
                    </div>
                </div>

                <div class="motorista-app-modal d-none" id="motoristaCampoModal" aria-hidden="true">
                    <div class="motorista-app-modal__backdrop" data-close-motorista-modal></div>
                    <div class="motorista-app-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="motoristaCampoModalTitle">
                        <div class="d-flex align-items-start justify-content-between mb-3" style="gap:12px;">
                            <div>
                                <h5 class="mb-1" id="motoristaCampoModalTitle" style="font-weight:700;">Adicionar campo</h5>
                                <p class="text-muted mb-0">Defina o nome, tipo e se o campo deve entrar na seleção da empresa.</p>
                            </div>
                            <button type="button" class="btn btn-light btn-sm" data-close-motorista-modal>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="motoristaCampoLabel">Nome do campo</label>
                            <input type="text" class="form-control form-control-sm" id="motoristaCampoLabel" maxlength="120" placeholder="Ex: foto do veículo">
                        </div>

                        <div class="form-group">
                                <label for="motoristaCampoType">Tipo</label>
                                <select class="form-control form-control-sm" id="motoristaCampoType" data-enhance-select>
                                    <option value="arquivo">Arquivo</option>
                                    <option value="texto">Texto</option>
                                    <option value="texto_longo">Texto longo</option>
                                    <option value="data">Data</option>
                                    <option value="lista">Lista</option>
                                </select>
                            </div>

                        <div class="form-group d-none" id="motoristaCampoOptionsWrap">
                            <label for="motoristaCampoOptions">Valores da lista</label>
                            <textarea class="form-control form-control-sm" id="motoristaCampoOptions" rows="5" placeholder="Uma opção por linha"></textarea>
                            <small class="text-muted d-block mt-1">Use uma linha para cada valor que o motorista poderá selecionar.</small>
                        </div>

                        <div class="form-group d-none" id="motoristaCampoMultipleWrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="motoristaCampoMultiple">
                                <label class="form-check-label" for="motoristaCampoMultiple">Permitir múltiplas opções</label>
                            </div>
                            <small class="text-muted d-block mt-1">Quando ativado, o motorista poderá marcar mais de uma opção.</small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="motoristaCampoTemCondicao">
                                <label class="form-check-label" for="motoristaCampoTemCondicao">Mostrar somente quando outra condição for atendida</label>
                            </div>
                        </div>

                        <div class="empresa-condition-box d-none" id="motoristaCampoCondicaoWrap">
                            <div class="form-group">
                                <label for="motoristaCampoCondicaoCampo">Campo de referência</label>
                                <select class="form-control form-control-sm" id="motoristaCampoCondicaoCampo"></select>
                            </div>
                            <div class="form-group">
                                <label for="motoristaCampoCondicaoOperador">Condição</label>
                                <select class="form-control form-control-sm" id="motoristaCampoCondicaoOperador">
                                    <option value="igual">É igual a</option>
                                    <option value="diferente">É diferente de</option>
                                    <option value="preenchido">Está preenchido</option>
                                    <option value="vazio">Está vazio</option>
                                </select>
                            </div>
                            <div class="form-group" id="motoristaCampoCondicaoValorTextWrap">
                                <label for="motoristaCampoCondicaoValor">Valor esperado</label>
                                <input type="text" class="form-control form-control-sm" id="motoristaCampoCondicaoValor" maxlength="120" placeholder="Ex: Sim">
                                <small class="text-muted d-block mt-1">Para arquivos, use a condição “Está preenchido” ou “Está vazio”.</small>
                            </div>
                            <div class="form-group d-none" id="motoristaCampoCondicaoValorListWrap">
                                <label for="motoristaCampoCondicaoValorList">Itens esperados</label>
                                <select class="form-control form-control-sm" id="motoristaCampoCondicaoValorList" multiple data-enhance-select data-placeholder="Selecione um ou mais itens"></select>
                                <small class="text-muted d-block mt-1">Selecione um ou mais itens da lista de referência.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="motoristaCampoSelected" checked>
                                <label class="form-check-label" for="motoristaCampoSelected">Selecionado para esta empresa</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="motoristaCampoObrigatorio">
                                <label class="form-check-label" for="motoristaCampoObrigatorio">Obrigatório</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end" style="gap:8px;">
                            <button type="button" class="btn btn-light btn-sm" data-close-motorista-modal>Cancelar</button>
                            <button type="button" class="btn btn-primary btn-sm" id="motoristaCampoSave">Salvar campo</button>
                        </div>
                    </div>
                </div>

                <div class="col-12 empresa-wizard-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm empresa-wizard-btn" id="empresaWizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Anterior
                    </button>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <button type="button" class="btn btn-primary btn-sm empresa-wizard-btn empresa-wizard-btn--next" id="empresaWizardNext">
                            Próximo <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success btn-sm empresa-wizard-btn empresa-wizard-btn--save btn-save-empresa <?php echo e($isEdit ? '' : 'd-none'); ?>" id="btnSalvarEmpresa">
                            Salvar alterações
                        </button>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18ad2e0d264f9740dc73fff715357c28)): ?>
<?php $attributes = $__attributesOriginal18ad2e0d264f9740dc73fff715357c28; ?>
<?php unset($__attributesOriginal18ad2e0d264f9740dc73fff715357c28); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18ad2e0d264f9740dc73fff715357c28)): ?>
<?php $component = $__componentOriginal18ad2e0d264f9740dc73fff715357c28; ?>
<?php unset($__componentOriginal18ad2e0d264f9740dc73fff715357c28); ?>
<?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .card-empresa {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-empresa {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        flex-wrap: wrap;
    }

    .card-header-empresa h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .empresa-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .empresa-nav-item {
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid #dbe3ee;
        background: #f8fbff;
        color: #1f334d;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
    }

    .empresa-nav-item:hover {
        background: #eef4ff;
        border-color: #c6d6ef;
        color: #1d4ed8;
    }

    .empresa-nav {
        display: none;
    }

    .empresa-wizard-top {
        border: 1px solid #e5eaf2;
        border-radius: 14px;
        padding: 12px 12px 10px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        margin-bottom: 14px;
    }

    .empresa-wizard-steps {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .empresa-wizard-steps .div {
        flex: 1 1 20px;
        height: 2px;
        background: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
        border-radius: 999px;
        min-width: 12px;
    }

    .empresa-wizard-step {
        appearance: none;
        border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 30%, #dbe3ef 70%);
        background: #fff;
        border-radius: 999px;
        padding: 8px 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease, color .12s ease;
        white-space: nowrap;
    }

    .empresa-wizard-step .num {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 12%, #ffffff 88%);
        color: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 88%, #111827 12%);
        border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 25%, #dbe3ef 75%);
    }

    .empresa-wizard-step.is-active {
        background: var(--empresa-cor-primaria, #1F2937);
        border-color: var(--empresa-cor-primaria, #1F2937);
        color: var(--empresa-cor-primaria-contraste, #fff);
        box-shadow: 0 10px 22px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 22%, transparent 78%);
    }

    .empresa-wizard-step.is-active .num {
        background: rgba(255,255,255,.18);
        color: var(--empresa-cor-primaria-contraste, #fff);
        border-color: rgba(255,255,255,.24);
    }

    .empresa-wizard-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 0 0;
        margin-top: 14px;
        border-top: 1px dashed color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 28%, #e5edf7 72%);
    }

    .empresa-wizard-footer .btn {
        border-radius: 999px;
        font-weight: 700;
    }

    .empresa-wizard-btn {
        min-height: 38px;
        padding: .58rem 1.05rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
        transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    }

    .empresa-wizard-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
    }

    .empresa-wizard-btn--next {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .empresa-wizard-btn--next:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .empresa-wizard-btn--save {
        background: #16a34a;
        border-color: #16a34a;
        color: #fff;
    }

    .empresa-wizard-btn--save:hover {
        background: #15803d;
        border-color: #15803d;
        color: #fff;
    }

    .empresa-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1f334d;
        letter-spacing: .02em;
        padding: 8px 12px;
        border-radius: 10px;
        background: #f6f9ff;
        border: 1px solid #e5eaf2;
        margin-bottom: 12px;
        scroll-margin-top: 90px;
    }

    .gateway-title {
        background: #0f172a;
        color: #ffffff;
        border-radius: 8px;
        padding: .4rem .7rem;
        font-size: .86rem;
        font-weight: 700;
        margin-bottom: 6px;
        display: inline-block;
    }

    .form-group-empresa {
        margin-bottom: 1rem;
    }

    .card-empresa .form-control,
    .card-empresa .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-empresa .form-control:focus,
    .card-empresa .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-empresa {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    .empresa-logo-preview-wrap {
        min-height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #d8e1ec;
        border-radius: 10px;
        padding: .75rem;
    }

    .empresa-logo-preview {
        max-height: 90px;
        max-width: 100%;
        object-fit: contain;
    }

    .empresa-logo-placeholder {
        color: #64748b;
        font-size: .88rem;
    }

    .empresa-theme-preview {
        height: 72px;
        border-radius: 12px;
        border: 1px solid #d8e1ec;
        margin-bottom: .45rem;
    }

    .empresa-subsection {
        border: 1px solid #e5eaf2;
        border-radius: 14px;
        padding: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        margin-bottom: 10px;
    }

    .empresa-subsection__head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .empresa-subsection__head h5 {
        margin: 0 0 4px;
        font-weight: 700;
        color: #0f2740;
    }

    .empresa-subsection__head p {
        margin: 0;
        color: #64748b;
        font-size: .9rem;
    }

    .empresa-subsection__badge {
        align-self: flex-start;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: .78rem;
        font-weight: 700;
    }

    .motorista-app-custom-fields__list {
        display: grid;
        gap: 10px;
    }

    .motorista-app-custom-field {
        border: 1px solid #dbe5f0;
        border-radius: 12px;
        background: #ffffff;
        padding: 12px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
    }

    .motorista-app-custom-field__main {
        min-width: 0;
    }

    .motorista-app-custom-field__title {
        font-weight: 700;
        color: #0f2740;
        margin-bottom: 2px;
    }

    .motorista-app-custom-field__meta {
        color: #64748b;
        font-size: .86rem;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .motorista-app-custom-field__actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        flex-wrap: wrap;
        justify-content: flex-end;
        max-width: 300px;
    }

    .motorista-app-custom-field__order {
        display: flex;
        gap: 4px;
    }

    .motorista-app-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1d4ed8;
        font-size: .74rem;
        font-weight: 700;
    }

    .motorista-app-options-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }

    .motorista-app-option-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 9px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: .75rem;
        font-weight: 600;
    }

    .motorista-app-modal {
        position: fixed;
        inset: 0;
        z-index: 1055;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .motorista-app-modal.d-none {
        display: none;
    }

    .motorista-app-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        backdrop-filter: blur(2px);
    }

    .motorista-app-modal__dialog {
        position: relative;
        width: min(100%, 560px);
        border-radius: 16px;
        background: #fff;
        padding: 18px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        border: 1px solid rgba(255, 255, 255, .65);
    }

    .empresa-condition-box {
        border: 1px solid #dbe5f0;
        border-radius: 12px;
        padding: 12px;
        background: #f8fbff;
        margin-bottom: 1rem;
    }

    .empresa-condition-box .form-group:last-child {
        margin-bottom: 0;
    }

    .empresa-calcao-help {
        padding: 10px 12px;
        border-radius: 10px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: .85rem;
    }

    .empresa-calcao-excecoes__title {
        font-size: .9rem;
        font-weight: 700;
        color: #0f2740;
        margin-bottom: 10px;
    }

    .empresa-calcao-table thead th {
        border-top: 0;
        background: #f8fafc;
        color: #334155;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .font-weight-600 {
        font-weight: 600;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
<script>
    (function () {
        const isEdit = <?php echo json_encode($isEdit, 15, 512) ?>;
        const gateway = document.getElementById('gateway_pagamento');
        const cnpjInput = document.getElementById('cnpj');
        const telefoneInput = document.getElementById('telefone');
        const corPrimaria = document.getElementById('cor_primaria');
        const corSecundaria = document.getElementById('cor_secundaria');
        const corTerciaria = document.getElementById('cor_terciaria');
        const themePreview = document.getElementById('empresa-theme-preview');
        const blocks = document.querySelectorAll('.gateway-fields');
        const calcaoAtivo = document.getElementById('calcao_ativo');
        const calcaoWrap = document.getElementById('calcao-config-wrap');
        const customFieldsInput = document.getElementById('aluguel_app_campos_personalizados');
        const customFieldsList = document.getElementById('motoristaCustomFieldsList');
        const customFieldsModal = document.getElementById('motoristaCampoModal');
        const customFieldLabel = document.getElementById('motoristaCampoLabel');
        const customFieldType = document.getElementById('motoristaCampoType');
        const customFieldOptionsWrap = document.getElementById('motoristaCampoOptionsWrap');
        const customFieldOptions = document.getElementById('motoristaCampoOptions');
        const customFieldMultipleWrap = document.getElementById('motoristaCampoMultipleWrap');
        const customFieldMultiple = document.getElementById('motoristaCampoMultiple');
        const customFieldTemCondicao = document.getElementById('motoristaCampoTemCondicao');
        const customFieldCondicaoWrap = document.getElementById('motoristaCampoCondicaoWrap');
        const customFieldCondicaoCampo = document.getElementById('motoristaCampoCondicaoCampo');
        const customFieldCondicaoOperador = document.getElementById('motoristaCampoCondicaoOperador');
        const customFieldCondicaoValorTextWrap = document.getElementById('motoristaCampoCondicaoValorTextWrap');
        const customFieldCondicaoValor = document.getElementById('motoristaCampoCondicaoValor');
        const customFieldCondicaoValorListWrap = document.getElementById('motoristaCampoCondicaoValorListWrap');
        const customFieldCondicaoValorList = document.getElementById('motoristaCampoCondicaoValorList');
        const customFieldModalTitle = document.getElementById('motoristaCampoModalTitle');
        const customFieldSelected = document.getElementById('motoristaCampoSelected');
        const customFieldObrigatorio = document.getElementById('motoristaCampoObrigatorio');
        const customFieldSave = document.getElementById('motoristaCampoSave');
        const customFieldAdd = document.getElementById('btnAddMotoristaCampo');
        const DEFAULT_CONDITION_FIELDS = [
            { key: 'cnh_definitiva', label: 'CNH definitiva' },
            { key: 'cnh_ear', label: 'CNH com EAR' },
            { key: 'comprovante_residencia', label: 'Comprovante de residência' },
            { key: 'comprovante_cadastro_app', label: 'Comprovante de cadastro no app' },
            { key: 'documento_adicional', label: 'Documento adicional' },
        ];
        let motoristaCampos = [];
        let editingCustomFieldIndex = null;

        function parseCustomFields() {
            if (!customFieldsInput) {
                motoristaCampos = [];
                return;
            }

            try {
                const parsed = JSON.parse(customFieldsInput.value || '[]');
                motoristaCampos = Array.isArray(parsed) ? parsed.filter((item) => item && typeof item === 'object') : [];
            } catch (error) {
                motoristaCampos = [];
            }

            motoristaCampos = motoristaCampos
                .map((item, index) => normalizeField(item, index + 1))
                .sort((a, b) => (a.ordem || 0) - (b.ordem || 0));

            syncCustomFieldsInput();
        }

        function normalizeField(item, fallbackOrder) {
            const rawOptions = Array.isArray(item?.options)
                ? item.options
                : (Array.isArray(item?.opcoes) ? item.opcoes : []);
            const options = rawOptions
                .map((option) => String(option || '').trim())
                .filter(Boolean);
            const type = ['arquivo', 'texto', 'texto_longo', 'lista', 'data'].includes(item?.type)
                ? item.type
                : 'arquivo';
            const condicao = normalizeCondition(item?.condicao ?? item?.condition);

            return {
                key: String(item?.key || ''),
                label: String(item?.label || ''),
                type,
                selected: item?.selected !== false,
                obrigatorio: item?.obrigatorio === true,
                ordem: Number.isFinite(Number(item?.ordem)) ? Number(item.ordem) : fallbackOrder,
                options,
                multiple: item?.multiple === true,
                condicao,
            };
        }

        function normalizeCondition(value) {
            if (!value || typeof value !== 'object' || Array.isArray(value)) {
                return null;
            }

            const campoKey = String(value?.campo_key || value?.campo || '').trim();
            const operador = String(value?.operador || '').trim();
            const rawValor = value?.valor ?? [];
            const condicaoValor = Array.isArray(rawValor)
                ? rawValor.map((item) => String(item || '').trim()).filter(Boolean)
                : String(rawValor || '').trim();

            if (!campoKey || !['igual', 'diferente', 'preenchido', 'vazio'].includes(operador)) {
                return null;
            }

            if (['igual', 'diferente'].includes(operador) && (Array.isArray(condicaoValor) ? condicaoValor.length === 0 : !condicaoValor)) {
                return null;
            }

            return {
                campo_key: campoKey,
                operador,
                valor: ['igual', 'diferente'].includes(operador) ? condicaoValor : '',
            };
        }

        function syncCustomFieldsInput() {
            if (!customFieldsInput) {
                return;
            }

            motoristaCampos = motoristaCampos.map((item, index) => ({
                ...normalizeField(item, index + 1),
                ordem: index + 1,
            }));

            customFieldsInput.value = JSON.stringify(motoristaCampos);
        }

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function typeLabel(type) {
            if (type === 'texto') return 'Texto';
            if (type === 'texto_longo') return 'Texto longo';
            if (type === 'lista') return 'Lista';
            if (type === 'data') return 'Data';
            return 'Arquivo';
        }

        function toggleListOptions() {
            if (!customFieldOptionsWrap || !customFieldType) {
                return;
            }

            const isList = customFieldType.value === 'lista';
            customFieldOptionsWrap.classList.toggle('d-none', !isList);
            if (customFieldMultipleWrap) {
                customFieldMultipleWrap.classList.toggle('d-none', !isList);
            }
        }

        function getConditionFieldOptions() {
            const options = [...DEFAULT_CONDITION_FIELDS];

            motoristaCampos.forEach((campo) => {
                if (!campo || !campo.key || !campo.label) {
                    return;
                }

                if (options.some((option) => option.key === campo.key)) {
                    return;
                }

                options.push({
                    key: campo.key,
                    label: campo.label,
                });
            });

            return options;
        }

        function getConditionFieldDefinition(campoKey) {
            const fixed = DEFAULT_CONDITION_FIELDS.find((field) => field.key === campoKey);
            if (fixed) {
                return {
                    key: fixed.key,
                    label: fixed.label,
                    type: 'arquivo',
                    options: [],
                    multiple: false,
                };
            }

            const dynamic = motoristaCampos.find((campo) => campo.key === campoKey);
            if (!dynamic) {
                return null;
            }

            return {
                key: dynamic.key,
                label: dynamic.label,
                type: dynamic.type || 'arquivo',
                options: Array.isArray(dynamic.options) ? dynamic.options : [],
                multiple: dynamic.multiple === true,
            };
        }

        function renderConditionFieldOptions(selectedKey = '') {
            if (!customFieldCondicaoCampo) {
                return;
            }

            const options = getConditionFieldOptions();
            customFieldCondicaoCampo.innerHTML = [
                '<option value="">Selecione um campo</option>',
                ...options.map((option) => `<option value="${escapeHtml(option.key)}"${option.key === selectedKey ? ' selected' : ''}>${escapeHtml(option.label)}</option>`),
            ].join('');
        }

        function renderConditionValueChoices(options, selectedValues = []) {
            if (!customFieldCondicaoValorList) {
                return;
            }

            const values = Array.isArray(selectedValues)
                ? selectedValues.map((item) => String(item || '').trim()).filter(Boolean)
                : [];
            const normalizedOptions = Array.isArray(options)
                ? options.map((item) => String(item || '').trim()).filter(Boolean)
                : [];

            const instance = customFieldCondicaoValorList._choicesInstance;
            if (instance) {
                try {
                    instance.clearChoices();
                    instance.setChoices(normalizedOptions.map((option) => ({
                        value: option,
                        label: option,
                        selected: values.includes(option),
                        disabled: false,
                    })), 'value', 'label', true);
                } catch (error) {
                    customFieldCondicaoValorList.innerHTML = normalizedOptions
                        .map((option) => `<option value="${escapeHtml(option)}"${values.includes(option) ? ' selected' : ''}>${escapeHtml(option)}</option>`)
                        .join('');
                }
            } else {
                customFieldCondicaoValorList.innerHTML = normalizedOptions
                    .map((option) => `<option value="${escapeHtml(option)}"${values.includes(option) ? ' selected' : ''}>${escapeHtml(option)}</option>`)
                    .join('');
            }
        }

        function conditionOperatorLabel(operador) {
            if (operador === 'igual') return 'é igual a';
            if (operador === 'diferente') return 'é diferente de';
            if (operador === 'preenchido') return 'está preenchido';
            if (operador === 'vazio') return 'está vazio';
            return operador || '';
        }

        function conditionFieldLabel(campoKey) {
            return getConditionFieldDefinition(campoKey)?.label || campoKey;
        }

        function conditionSummary(condicao) {
            const normalized = normalizeCondition(condicao);
            if (!normalized) {
                return '';
            }

            const label = escapeHtml(conditionFieldLabel(normalized.campo_key));
            const operador = escapeHtml(conditionOperatorLabel(normalized.operador));
            const valorLista = Array.isArray(normalized.valor)
                ? normalized.valor.map((item) => escapeHtml(item)).join(', ')
                : '';
            const valor = Array.isArray(normalized.valor)
                ? (valorLista ? ` <strong>${valorLista}</strong>` : '')
                : (normalized.valor ? ` <strong>${escapeHtml(normalized.valor)}</strong>` : '');
            return `Se <strong>${label}</strong> ${operador}${valor}`;
        }

        function toggleConditionBox() {
            if (!customFieldCondicaoWrap || !customFieldTemCondicao) {
                return;
            }

            customFieldCondicaoWrap.classList.toggle('d-none', !customFieldTemCondicao.checked);
        }

        function toggleConditionValueField() {
            if (!customFieldCondicaoValorTextWrap || !customFieldCondicaoValorListWrap || !customFieldCondicaoOperador || !customFieldCondicaoCampo) {
                return;
            }

            const operador = customFieldCondicaoOperador.value;
            const campo = getConditionFieldDefinition(customFieldCondicaoCampo.value || '');
            const isListField = campo?.type === 'lista';
            const shouldHide = ['preenchido', 'vazio'].includes(operador);

            customFieldCondicaoValorTextWrap.classList.toggle('d-none', shouldHide || isListField);
            customFieldCondicaoValorListWrap.classList.toggle('d-none', shouldHide || !isListField);

            if (isListField) {
                renderConditionValueChoices(campo.options || [], normalizeConditionValueArray(customFieldCondicaoValorList?.selectedOptions || []));
            } else if (customFieldCondicaoValor && customFieldCondicaoValorList) {
                customFieldCondicaoValorList.innerHTML = '';
                const instance = customFieldCondicaoValorList._choicesInstance;
                if (instance) {
                    try {
                        instance.clearChoices();
                    } catch (error) {
                        // noop
                    }
                }
            }
        }

        function normalizeConditionValueArray(value) {
            if (Array.isArray(value)) {
                return value.map((item) => String(item || '').trim()).filter(Boolean);
            }

            if (!value) {
                return [];
            }

            if (typeof value.length === 'number' && value.length >= 0 && typeof value.item === 'function') {
                return [...value].map((item) => String(item?.value || '').trim()).filter(Boolean);
            }

            return [String(value || '').trim()].filter(Boolean);
        }

        function renderCustomFields() {
            if (!customFieldsList) {
                return;
            }

            if (!motoristaCampos.length) {
                customFieldsList.innerHTML = '<div class="text-muted">Nenhum campo personalizado cadastrado para esta empresa.</div>';
                syncCustomFieldsInput();
                return;
            }

            customFieldsList.innerHTML = motoristaCampos.map((campo, index) => {
                const checked = campo.selected ? 'checked' : '';
                const label = escapeHtml(campo.label || 'Campo sem nome');
                const type = escapeHtml(typeLabel(campo.type));
                const key = escapeHtml(campo.key || `campo_${index}`);
                const options = Array.isArray(campo.options) ? campo.options : [];
                const multipleBadge = campo.type === 'lista' && campo.multiple
                    ? '<span class="motorista-app-badge" style="background:#eef2ff;border-color:#c7d2fe;color:#4338ca;">Múltipla</span>'
                    : '';
                const optionsHtml = options.length
                    ? `<div class="motorista-app-options-list">${options.map((option) => `<span class="motorista-app-option-pill">${escapeHtml(option)}</span>`).join('')}</div>`
                    : '';
                const obrigatorioBadge = campo.obrigatorio
                    ? '<span class="motorista-app-badge" style="background:#fef3c7;border-color:#fcd34d;color:#92400e;">Obrigatório</span>'
                    : '';
                const condicaoBadge = campo.condicao
                    ? '<span class="motorista-app-badge" style="background:#ecfeff;border-color:#a5f3fc;color:#155e75;">Condicional</span>'
                    : '';
                const condicaoTexto = campo.condicao
                    ? `<div class="text-muted" style="font-size:.78rem;margin-top:6px;">${conditionSummary(campo.condicao)}</div>`
                    : '';

                return `
                    <div class="motorista-app-custom-field" data-motorista-custom-field="${key}">
                        <div class="motorista-app-custom-field__main">
                            <div class="motorista-app-custom-field__title">${label}</div>
                            <div class="motorista-app-custom-field__meta">
                                <span class="motorista-app-badge">${type}</span>
                                ${multipleBadge}
                                ${obrigatorioBadge}
                                ${condicaoBadge}
                                <span>Chave: ${key}</span>
                                <span>Ordem: ${index + 1}</span>
                            </div>
                            ${condicaoTexto}
                            ${optionsHtml}
                        </div>
                        <div class="motorista-app-custom-field__actions">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" ${checked} data-custom-field-selected="${index}" id="customFieldSelected_${index}">
                                <label class="form-check-label" for="customFieldSelected_${index}">Selecionado</label>
                            </div>
                            <div class="motorista-app-custom-field__order">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-custom-field-edit="${index}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-custom-field-up="${index}" ${index === 0 ? 'disabled' : ''}>
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-custom-field-down="${index}" ${index === motoristaCampos.length - 1 ? 'disabled' : ''}>
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-custom-field-remove="${index}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            customFieldsList.querySelectorAll('[data-custom-field-selected]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const index = Number(checkbox.getAttribute('data-custom-field-selected'));
                    if (!Number.isFinite(index) || !motoristaCampos[index]) {
                        return;
                    }
                    motoristaCampos[index].selected = checkbox.checked;
                    syncCustomFieldsInput();
                });
            });

            customFieldsList.querySelectorAll('[data-custom-field-remove]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.getAttribute('data-custom-field-remove'));
                    if (!Number.isFinite(index)) {
                        return;
                    }
                    motoristaCampos.splice(index, 1);
                    renderCustomFields();
                });
            });

            customFieldsList.querySelectorAll('[data-custom-field-edit]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.getAttribute('data-custom-field-edit'));
                    if (!Number.isFinite(index) || !motoristaCampos[index]) {
                        return;
                    }

                    openCustomFieldsModal(index);
                });
            });

            customFieldsList.querySelectorAll('[data-custom-field-up]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.getAttribute('data-custom-field-up'));
                    if (!Number.isFinite(index) || index <= 0) {
                        return;
                    }

                    const current = motoristaCampos[index];
                    motoristaCampos[index] = motoristaCampos[index - 1];
                    motoristaCampos[index - 1] = current;
                    renderCustomFields();
                });
            });

            customFieldsList.querySelectorAll('[data-custom-field-down]').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.getAttribute('data-custom-field-down'));
                    if (!Number.isFinite(index) || index >= motoristaCampos.length - 1) {
                        return;
                    }

                    const current = motoristaCampos[index];
                    motoristaCampos[index] = motoristaCampos[index + 1];
                    motoristaCampos[index + 1] = current;
                    renderCustomFields();
                });
            });

            syncCustomFieldsInput();
        }

        function fillCustomFieldModal(campo) {
            const tipoAtual = campo?.type || 'arquivo';
            customFieldLabel.value = campo?.label || '';
            customFieldType.value = tipoAtual;
            if (customFieldType._choicesInstance) {
                try {
                    customFieldType._choicesInstance.setChoiceByValue(tipoAtual);
                } catch (error) {
                    // noop
                }
            }
            if (customFieldOptions) {
                customFieldOptions.value = Array.isArray(campo?.options) ? campo.options.join('\n') : '';
            }
            if (customFieldMultiple) {
                customFieldMultiple.checked = !!campo?.multiple;
            }
            if (customFieldTemCondicao) {
                customFieldTemCondicao.checked = !!campo?.condicao;
            }

            renderConditionFieldOptions(campo?.condicao?.campo_key || '');
            toggleConditionBox();

            if (customFieldCondicaoOperador) {
                customFieldCondicaoOperador.value = campo?.condicao?.operador || 'igual';
            }

            if (customFieldCondicaoCampo) {
                customFieldCondicaoCampo.value = campo?.condicao?.campo_key || '';
            }

            const campoRef = getConditionFieldDefinition(campo?.condicao?.campo_key || '');
            const condicaoValor = campo?.condicao?.valor;

            if (campoRef?.type === 'lista') {
                renderConditionValueChoices(campoRef.options || [], normalizeConditionValueArray(condicaoValor));
            } else if (customFieldCondicaoValor) {
                customFieldCondicaoValor.value = Array.isArray(condicaoValor) ? condicaoValor.join(', ') : (condicaoValor || '');
            }

            if (customFieldSelected) {
                customFieldSelected.checked = campo?.selected !== false;
            }

            if (customFieldObrigatorio) {
                customFieldObrigatorio.checked = !!campo?.obrigatorio;
            }

            toggleListOptions();
            toggleConditionValueField();
        }

        function openCustomFieldsModal(index = null) {
            if (!customFieldsModal) {
                return;
            }

            editingCustomFieldIndex = Number.isInteger(index) && motoristaCampos[index] ? index : null;

            if (customFieldModalTitle) {
                customFieldModalTitle.textContent = editingCustomFieldIndex === null ? 'Adicionar campo' : 'Editar campo';
            }

            if (customFieldSave) {
                customFieldSave.textContent = editingCustomFieldIndex === null ? 'Salvar campo' : 'Atualizar campo';
            }

            if (editingCustomFieldIndex === null) {
                customFieldLabel.value = '';
                customFieldType.value = 'arquivo';
                if (customFieldType._choicesInstance) {
                    try {
                        customFieldType._choicesInstance.setChoiceByValue('arquivo');
                    } catch (error) {
                        // noop
                    }
                }
                if (customFieldOptions) {
                    customFieldOptions.value = '';
                }
                if (customFieldMultiple) {
                    customFieldMultiple.checked = false;
                }
                if (customFieldTemCondicao) {
                    customFieldTemCondicao.checked = false;
                }
                renderConditionFieldOptions('');
                toggleConditionBox();
                if (customFieldCondicaoOperador) {
                    customFieldCondicaoOperador.value = 'igual';
                }
                if (customFieldCondicaoValor) {
                    customFieldCondicaoValor.value = '';
                }
                if (customFieldCondicaoValorList) {
                    customFieldCondicaoValorList.innerHTML = '';
                }
                toggleConditionValueField();
                if (customFieldSelected) {
                    customFieldSelected.checked = true;
                }
                if (customFieldObrigatorio) {
                    customFieldObrigatorio.checked = false;
                }
                toggleListOptions();
            } else {
                fillCustomFieldModal(motoristaCampos[editingCustomFieldIndex]);
            }

            customFieldsModal.classList.remove('d-none');
            customFieldLabel.focus();
        }

        function closeCustomFieldsModal() {
            if (!customFieldsModal) {
                return;
            }
            editingCustomFieldIndex = null;
            customFieldsModal.classList.add('d-none');
        }

        function makeCustomFieldKey(label) {
            const base = String(label || 'campo')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');

            const fallback = base || 'campo';
            const unique = `${fallback}_${Date.now().toString(36)}`;
            return unique.slice(0, 80);
        }

        function onlyDigits(value) {
            return (value || '').replace(/\D/g, '');
        }

        function formatCnpj(value) {
            const digits = onlyDigits(value).slice(0, 14);

            if (digits.length <= 2) return digits;
            if (digits.length <= 5) return digits.replace(/^(\d{2})(\d+)/, '$1.$2');
            if (digits.length <= 8) return digits.replace(/^(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
            if (digits.length <= 12) return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
            return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d+)/, '$1.$2.$3/$4-$5');
        }

        function formatTelefone(value) {
            const digits = onlyDigits(value).slice(0, 11);

            if (digits.length <= 2) return digits;
            if (digits.length <= 6) return digits.replace(/^(\d{2})(\d+)/, '($1) $2');
            if (digits.length <= 10) return digits.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            return digits.replace(/^(\d{2})(\d{5})(\d+)/, '($1) $2-$3');
        }

        function bindMask(input, formatter) {
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                input.value = formatter(input.value);
            });

            input.value = formatter(input.value);
        }

        function syncGatewayFields() {
            if (!gateway) {
                return;
            }

            const selected = gateway.value;
            blocks.forEach((el) => {
                el.style.display = 'none';
            });

            document.querySelectorAll('.gateway-' + selected).forEach((el) => {
                el.style.display = '';
            });
        }

        function syncThemePreview() {
            if (!themePreview) {
                return;
            }

            const c1 = corPrimaria?.value || '#1F2937';
            const c2 = corSecundaria?.value || '#334155';
            const c3 = corTerciaria?.value || '#64748B';

            themePreview.style.background = 'linear-gradient(135deg, ' + c1 + ' 0%, ' + c2 + ' 55%, ' + c3 + ' 100%)';
        }

        function syncCalcaoFields() {
            if (!calcaoAtivo || !calcaoWrap) {
                return;
            }

            calcaoWrap.classList.toggle('d-none', calcaoAtivo.value !== 'S');
        }

        parseCustomFields();
        renderCustomFields();

        if (customFieldAdd) {
            customFieldAdd.addEventListener('click', openCustomFieldsModal);
        }

        document.querySelectorAll('[data-close-motorista-modal]').forEach((button) => {
            button.addEventListener('click', closeCustomFieldsModal);
        });

        if (customFieldSave) {
            customFieldSave.addEventListener('click', () => {
                const label = customFieldLabel?.value?.trim() || '';
                if (!label) {
                    customFieldLabel?.focus();
                    return;
                }

                let type = customFieldType?.value || 'arquivo';
                if (!['arquivo', 'texto', 'texto_longo', 'lista', 'data'].includes(type)) {
                    type = 'arquivo';
                    if (customFieldType) {
                        customFieldType.value = 'arquivo';
                    }
                }

                const options = type === 'lista'
                    ? (customFieldOptions?.value || '')
                        .split(/\r?\n/)
                        .map((item) => item.trim())
                        .filter(Boolean)
                    : [];

                if (type === 'lista' && options.length === 0) {
                    customFieldOptions?.focus();
                    return;
                }

                let condicao = null;
                if (customFieldTemCondicao?.checked) {
                    const campoKey = customFieldCondicaoCampo?.value || '';
                    const operador = customFieldCondicaoOperador?.value || 'igual';
                    const campoRef = getConditionFieldDefinition(campoKey);
                    const valor = campoRef?.type === 'lista'
                        ? normalizeConditionValueArray(customFieldCondicaoValorList?.selectedOptions || [])
                        : (customFieldCondicaoValor?.value?.trim() || '');

                    if (!campoKey) {
                        customFieldCondicaoCampo?.focus();
                        return;
                    }

                    if (['igual', 'diferente'].includes(operador)) {
                        const temValor = Array.isArray(valor) ? valor.length > 0 : String(valor || '').trim() !== '';
                        if (!temValor) {
                            if (campoRef?.type === 'lista') {
                                customFieldCondicaoValorList?.focus();
                            } else {
                                customFieldCondicaoValor?.focus();
                            }
                            return;
                        }
                    }

                    condicao = {
                        campo_key: campoKey,
                        operador,
                        valor: ['igual', 'diferente'].includes(operador) ? valor : '',
                    };
                }

                const payload = {
                    key: editingCustomFieldIndex !== null && motoristaCampos[editingCustomFieldIndex]
                        ? motoristaCampos[editingCustomFieldIndex].key
                        : makeCustomFieldKey(label),
                    label,
                    type,
                    selected: !!customFieldSelected?.checked,
                    obrigatorio: !!customFieldObrigatorio?.checked,
                    options,
                    multiple: type === 'lista' ? !!customFieldMultiple?.checked : false,
                    condicao,
                    ordem: editingCustomFieldIndex !== null && motoristaCampos[editingCustomFieldIndex]
                        ? motoristaCampos[editingCustomFieldIndex].ordem || (editingCustomFieldIndex + 1)
                        : motoristaCampos.length + 1,
                };

                if (editingCustomFieldIndex !== null && motoristaCampos[editingCustomFieldIndex]) {
                    motoristaCampos[editingCustomFieldIndex] = payload;
                } else {
                    motoristaCampos.push(payload);
                }

                renderCustomFields();
                closeCustomFieldsModal();
            });
        }

        [customFieldLabel, customFieldType].forEach((input) => {
            if (!input) {
                return;
            }

            input.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                customFieldSave?.click();
            });
        });

        customFieldType?.addEventListener('change', toggleListOptions);
        customFieldTemCondicao?.addEventListener('change', () => {
            toggleConditionBox();
            renderConditionFieldOptions(customFieldCondicaoCampo?.value || '');
            toggleConditionValueField();
        });
        customFieldCondicaoCampo?.addEventListener('change', () => {
            toggleConditionValueField();
        });
        customFieldCondicaoOperador?.addEventListener('change', toggleConditionValueField);

        // Wizard/Steps do cadastro de empresa (igual ao admin reserva)
        (function initEmpresaWizard() {
            const maxStep = 8;
            const storageKey = 'empresaWizardStep';

            const prevBtn = document.getElementById('empresaWizardPrev');
            const nextBtn = document.getElementById('empresaWizardNext');
            const submitBtn = document.getElementById('btnSalvarEmpresa');

            function getStep() {
                const saved = Number(localStorage.getItem(storageKey) || '1');
                return Number.isFinite(saved) && saved >= 1 && saved <= maxStep ? saved : 1;
            }

            function setStep(step, persist = true) {
                const s = Math.min(maxStep, Math.max(1, Number(step) || 1));

                document.querySelectorAll('[data-empresa-step]').forEach(el => {
                    el.classList.toggle('d-none', Number(el.dataset.empresaStep) !== s);
                });

                document.querySelectorAll('[data-empresa-step-btn]').forEach(btn => {
                    btn.classList.toggle('is-active', Number(btn.dataset.empresaStepBtn) === s);
                });

                if (prevBtn) prevBtn.disabled = s <= 1;
                if (nextBtn) nextBtn.classList.toggle('d-none', s >= maxStep);
                if (submitBtn) {
                    if (isEdit) {
                        submitBtn.classList.remove('d-none');
                    } else {
                        submitBtn.classList.toggle('d-none', s < maxStep);
                    }
                }

                if (persist) localStorage.setItem(storageKey, String(s));

                const top = document.querySelector('.empresa-wizard-top');
                if (top) {
                    top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            function validateStep(step) {
                const containers = Array.from(document.querySelectorAll(`[data-empresa-step="${step}"]`));
                const fields = [];
                containers.forEach(c => fields.push(...Array.from(c.querySelectorAll('input,select,textarea'))));

                for (const field of fields) {
                    if (field.disabled) continue;
                    if (field.type === 'hidden') continue;
                    if (typeof field.checkValidity !== 'function') continue;
                    if (!field.checkValidity()) {
                        if (typeof field.reportValidity === 'function') field.reportValidity();
                        field.focus?.();
                        return false;
                    }
                }

                return true;
            }

            window.empresaWizardSetStep = setStep;

            document.querySelectorAll('[data-empresa-step-btn]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const current = getStep();
                    const target = Number(btn.dataset.empresaStepBtn);
                    if (target > current && !validateStep(current)) return;
                    setStep(target);
                });
            });

            if (prevBtn) {
                prevBtn.addEventListener('click', () => setStep(getStep() - 1));
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const current = getStep();
                    if (!validateStep(current)) return;
                    setStep(current + 1);
                });
            }

            // Abrir step com erro de validacao do backend
            (function openStepWithErrors() {
                const errosCampos = <?php echo json_encode(array_keys($errors->messages()), 15, 512) ?>;
                if (!Array.isArray(errosCampos) || errosCampos.length === 0) return;

                const mapa = {
                    nome: 1, ativo: 1, slug: 1, cnpj: 1,
                    email: 2, telefone: 2,
                    dominio: 3, reserva_selecionar_modelo: 3, reserva_atraso_max_minutos: 3, reserva_bloqueio_pos_reserva_horas: 3,
                    adicionar_novas_marcas_automaticamente: 3, adicionar_novos_modelos_automaticamente: 3,
                    logo: 4, cor_primaria: 4, cor_secundaria: 4, cor_terciaria: 4,
                    gateway_pagamento: 5, moeda_pagamento: 5, calcao_ativo: 5, calcao_tipo_regra_valor: 5, calcao_valor: 5,
                    calcao_valor_minimo: 5, calcao_valor_maximo: 5, calcao_grupos: 5,
                    stripe_public_key: 6, stripe_secret: 6,
                    mercado_livre_public_key: 6, mercado_livre_access_token: 6,
                    paypal_client_id: 6, paypal_client_secret: 6, paypal_mode: 6,
                    nfse_ativo: 7, nfse_ambiente: 7, nfse_provedor: 7,
                    nfse_client_id: 7, nfse_client_secret: 7,
                    nfse_inscricao_municipal: 7, nfse_municipio_codigo: 7,
                    nfse_codigo_servico: 7, nfse_cnae: 7, nfse_descricao_servico: 7,
                    nfse_aliquota: 7, nfse_tributacao_issqn: 7,
                    nfse_tomador_numero_padrao: 7, nfse_tomador_bairro_padrao: 7,
                    aluguel_app_ativo: 8, aluguel_app_exigir_aprovacao_documentos: 8,
                    aluguel_app_documento_adicional_label: 8, aluguel_app_documentos_obrigatorios: 8,
                    aluguel_app_campos_personalizados: 8,
                };

                for (const campo of errosCampos) {
                    if (String(campo).startsWith('calcao_grupos.')) {
                        setStep(5, true);
                        return;
                    }
                    if (String(campo).startsWith('aluguel_app_campos_personalizados.')) {
                        setStep(8, true);
                        return;
                    }
                    const step = mapa[campo];
                    if (step) {
                        setStep(step, true);
                        return;
                    }
                }
            })();

            setStep(getStep(), false);
        })();

        if (gateway) {
            gateway.addEventListener('change', syncGatewayFields);
        }

        if (calcaoAtivo) {
            calcaoAtivo.addEventListener('change', syncCalcaoFields);
        }

        [corPrimaria, corSecundaria, corTerciaria].forEach((input) => {
            if (input) {
                input.addEventListener('input', syncThemePreview);
            }
        });

        bindMask(cnpjInput, formatCnpj);
        bindMask(telefoneInput, formatTelefone);

        const siteTipoReserva = document.getElementById('site_tipo_reserva');
        const aluguelAppAtivoHidden = document.getElementById('aluguel_app_ativo');

        function syncAluguelAppAtivo() {
            if (siteTipoReserva && aluguelAppAtivoHidden) {
                const val = siteTipoReserva.value;
                aluguelAppAtivoHidden.value = (val === 'motorista_app' || val === 'ambas') ? 'S' : 'N';
            }
        }

        if (siteTipoReserva) {
            siteTipoReserva.addEventListener('change', syncAluguelAppAtivo);
        }

        syncGatewayFields();
        syncCalcaoFields();
        syncThemePreview();
        syncAluguelAppAtivo();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/empresa/add-edit.blade.php ENDPATH**/ ?>