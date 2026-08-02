

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
        $mostrarAbaDisponibilidade =
            !isset($veiculo) || empty($veiculo->local_disponibilidade) || empty($veiculo->data_hora_disponibilidade);
        $chavesErro = array_keys($errors->messages());

        $qtdTiposFrota = is_countable($tipoFrotas ?? null) ? count($tipoFrotas) : 0;
        $qtdGruposFrota = is_countable($grupoFrotas ?? null) ? count($grupoFrotas) : 0;
        $qtdMarcas = is_countable($marcas ?? null) ? count($marcas) : 0;
        $qtdModelos = is_countable($modelos ?? null) ? count($modelos) : 0;
        $qtdStatusVeiculo = is_countable($statusVeiculos ?? null) ? count($statusVeiculos) : 0;
        $qtdEstacoes = is_countable($estacoes ?? null) ? count($estacoes) : 0;

        $faltasPreRequisitosVeiculo = [];
        if ($qtdTiposFrota === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um tipo de frota ativo.';
        }
        if ($qtdGruposFrota === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um grupo de frota ativo.';
        }
        if ($qtdMarcas === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos uma marca ativa.';
        }
        if ($qtdModelos === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um modelo ativo.';
        }
        if ($qtdStatusVeiculo === 0) {
            $faltasPreRequisitosVeiculo[] = 'Cadastre pelo menos um status de veículo ativo.';
        }
        if ($mostrarAbaDisponibilidade && $qtdEstacoes === 0) {
            $faltasPreRequisitosVeiculo[] =
                'Cadastre pelo menos uma estação ativa para definir a disponibilidade inicial.';
        }

        $acoesPreRequisitosVeiculo = [];
        if ($qtdTiposFrota === 0 && Route::has('tipo_frota.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar tipo de frota', 'url' => route('tipo_frota.create')];
        }
        if ($qtdGruposFrota === 0 && Route::has('grupo_frota.create')) {
            $acoesPreRequisitosVeiculo[] = [
                'label' => 'Cadastrar grupo de frota',
                'url' => route('grupo_frota.create'),
            ];
        }
        if ($qtdMarcas === 0 && Route::has('marca.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar marca', 'url' => route('marca.create')];
        }
        if ($qtdModelos === 0 && Route::has('modelo.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar modelo', 'url' => route('modelo.create')];
        }
        if ($qtdStatusVeiculo === 0 && Route::has('status_veiculo.create')) {
            $acoesPreRequisitosVeiculo[] = [
                'label' => 'Cadastrar status de veículo',
                'url' => route('status_veiculo.create'),
            ];
        }
        if ($mostrarAbaDisponibilidade && $qtdEstacoes === 0 && Route::has('estacao.create')) {
            $acoesPreRequisitosVeiculo[] = ['label' => 'Cadastrar estação', 'url' => route('estacao.create')];
        }

        $podeCriarVeiculo = empty($faltasPreRequisitosVeiculo);
        $bloquearSalvarVeiculo = $metodo === 'POST' && !$podeCriarVeiculo;

        // Determine start step from validation errors
        $errosIdent = ['ano_fabricacao', 'placa_licenca', 'chassi', 'cor', 'numero_motor', 'renavam'];
        $errosDatas = [
            'valor_compra',
            'valor_fipe',
            'codigo_fipe',
            'referencia_fipe',
            'data_consulta_fipe',
            'taxa_depreciacao_anual',
            'data_placa_licenca',
            'data_compra',
            'data_validade',
            'kms',
            'limite_kms',
            'proxima_revisao_kms',
        ];
        $errosDisp = ['local_disponibilidade', 'data_hora_disponibilidade'];
        $startStep = 1;
        if (count(array_intersect($chavesErro, $errosIdent))) {
            $startStep = 2;
        } elseif (count(array_intersect($chavesErro, $errosDatas))) {
            $startStep = 3;
        } elseif (count(array_intersect($chavesErro, $errosDisp))) {
            $startStep = 4;
        }

        $grupoFrotaSelecionadoId = old('grupo_frota_id', $veiculo->grupo_frota_id ?? null);
        $marcaSelecionadaId = old('marca_id', $veiculo->marca_id ?? null);
        $modeloSelecionadoId = old('modelo_id', $veiculo->modelo_id ?? null);
        $grupoFrotaSelecionadoNome = $grupoFrotas[$grupoFrotaSelecionadoId] ?? '';
        $marcaSelecionadaNome = $marcas[$marcaSelecionadaId] ?? '';
        $modeloSelecionadoNome = $modelos[$modeloSelecionadoId] ?? '';
    ?>



    <div class="col-xl-11 col-12 px-0">
        <?php if (isset($component)) { $__componentOriginal1483a54ad1eed584cc353dbfbe0aaf49 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1483a54ad1eed584cc353dbfbe0aaf49 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pre-requisitos-alerta','data' => ['title' => 'Pré-requisitos pendentes para adicionar veículo','chips' => [
            ['label' => 'Tipos de Frota', 'value' => $qtdTiposFrota],
            ['label' => 'Grupos de Frota', 'value' => $qtdGruposFrota],
            ['label' => 'Marcas', 'value' => $qtdMarcas],
            ['label' => 'Modelos', 'value' => $qtdModelos],
            ['label' => 'Status Veículo', 'value' => $qtdStatusVeiculo],
            ['label' => 'Estações', 'value' => $qtdEstacoes],
        ],'missingItems' => $faltasPreRequisitosVeiculo,'actionLinks' => $acoesPreRequisitosVeiculo]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pre-requisitos-alerta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pré-requisitos pendentes para adicionar veículo','chips' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            ['label' => 'Tipos de Frota', 'value' => $qtdTiposFrota],
            ['label' => 'Grupos de Frota', 'value' => $qtdGruposFrota],
            ['label' => 'Marcas', 'value' => $qtdMarcas],
            ['label' => 'Modelos', 'value' => $qtdModelos],
            ['label' => 'Status Veículo', 'value' => $qtdStatusVeiculo],
            ['label' => 'Estações', 'value' => $qtdEstacoes],
        ]),'missing-items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($faltasPreRequisitosVeiculo),'action-links' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($acoesPreRequisitosVeiculo)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1483a54ad1eed584cc353dbfbe0aaf49)): ?>
<?php $attributes = $__attributesOriginal1483a54ad1eed584cc353dbfbe0aaf49; ?>
<?php unset($__attributesOriginal1483a54ad1eed584cc353dbfbe0aaf49); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1483a54ad1eed584cc353dbfbe0aaf49)): ?>
<?php $component = $__componentOriginal1483a54ad1eed584cc353dbfbe0aaf49; ?>
<?php unset($__componentOriginal1483a54ad1eed584cc353dbfbe0aaf49); ?>
<?php endif; ?>

        <div class="card card-veiculo shadow-sm border-0">
            <div class="card-header card-header-veiculo border-0">
                <div>
                    <h4 class="mb-1"><?php echo e($titulo); ?></h4>
                    <p class="mb-0 text-muted">Configure os dados gerais, identificação, datas/km e disponibilidade inicial
                        do veículo.</p>
                </div>
            </div>

            <div class="card-body p-4">

                
                <div class="vc-wizard-steps mb-4">
                    <?php
                        $steps = [
                            1 => ['icon' => 'fa-car', 'label' => 'Geral'],
                            2 => ['icon' => 'fa-id-card', 'label' => 'Identificação'],
                            3 => ['icon' => 'fa-calendar-alt', 'label' => 'Datas e Km'],
                            4 => ['icon' => 'fa-map-marker-alt', 'label' => 'Disponibilidade'],
                        ];
                        if (!$mostrarAbaDisponibilidade) {
                            unset($steps[4]);
                        }
                    ?>
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" class="vc-step-btn" data-step="<?php echo e($n); ?>">
                            <span class="vc-step-circle"><i class="fas <?php echo e($s['icon']); ?>"></i></span>
                            <span class="vc-step-label"><?php echo e($s['label']); ?></span>
                        </button>
                        <?php if(!$loop->last): ?>
                            <div class="vc-step-line"></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if (isset($component)) { $__componentOriginal6766b3cb6af7c7cfd7a1aef880a34502 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6766b3cb6af7c7cfd7a1aef880a34502 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.campos-obrigatorios-pendentes','data' => ['alertClass' => 'js-veiculo-validacao-alerta mb-3','actionClass' => 'js-veiculo-ir-passo-base','summaryClass' => 'js-veiculo-validacao-resumo','listClass' => 'js-veiculo-validacao-lista']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('campos-obrigatorios-pendentes'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alert-class' => 'js-veiculo-validacao-alerta mb-3','action-class' => 'js-veiculo-ir-passo-base','summary-class' => 'js-veiculo-validacao-resumo','list-class' => 'js-veiculo-validacao-lista']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6766b3cb6af7c7cfd7a1aef880a34502)): ?>
<?php $attributes = $__attributesOriginal6766b3cb6af7c7cfd7a1aef880a34502; ?>
<?php unset($__attributesOriginal6766b3cb6af7c7cfd7a1aef880a34502); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6766b3cb6af7c7cfd7a1aef880a34502)): ?>
<?php $component = $__componentOriginal6766b3cb6af7c7cfd7a1aef880a34502; ?>
<?php unset($__componentOriginal6766b3cb6af7c7cfd7a1aef880a34502); ?>
<?php endif; ?>

                <form id="formulario" method="POST" action="<?php echo e($rota); ?>" novalidate>
                    <?php if($metodo == 'PUT'): ?>
                        <?php echo method_field('PUT'); ?>
                    <?php endif; ?>
                    <?php echo csrf_field(); ?>

                    
                    <div class="vc-step-pane" data-pane="1">
                        <div class="veiculo-section-title"><i class="fas fa-car mr-1"></i>Informações Gerais</div>
                        <div class="form-row">
                            <?php if(isset($veiculo)): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'nome','label' => 'Nome','value' => ''.e(old('nome', $veiculo->nome ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'nome','icon' => '1','iconHint' => 'Nome interno do veículo. Em geral é preenchido automaticamente após salvar.','disabled' => true,'readonly' => true]); ?>
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
                            <?php endif; ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'tipo_frota_id','label' => 'Tipo de Frota','options' => $tipoFrotas,'placeholder' => 'Selecione um valor','selected' => old('tipo_frota_id', $veiculo->tipo_frota_id ?? null)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => '1','iconHint' => 'Classificação principal do veículo, usada para regras, filtros e disponibilidade.','class' => 'form-control form-control-sm','required' => true]); ?>
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
                            <div class="col-lg-5 col-md-12">
                                <div class="form-group form-group-veiculo">
                                    <label for="grupo_frota_display">Grupo de Frota <span style="color: red;">*</span></label>
                                    <i class="fas fa-circle-info text-muted ml-1 vc-tooltip-icon" data-toggle="tooltip" data-placement="top" title="Grupo comercial/operacional do veículo. Você pode escolher um grupo existente ou criar um novo nome na criação."></i>
                                    <div class="input-group input-group-sm vc-search-lookup-group">
                                        <input type="text" id="grupo_frota_display" class="form-control form-control-sm vc-lookup-display" value="<?php echo e($grupoFrotaSelecionadoNome); ?>" placeholder="Clique na lupa para pesquisar o grupo" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary vc-select-search-btn" id="btnBuscarGrupoFrota" title="Pesquisa avançada">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <select name="grupo_frota_id" id="grupo_frota_id" class="d-none" required>
                                        <option value="">Selecione um valor</option>
                                        <?php $__currentLoopData = $grupoFrotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($key); ?>" <?php echo e((string) $grupoFrotaSelecionadoId === (string) $key ? 'selected' : ''); ?>>
                                                <?php echo e($value); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <?php if(!isset($veiculo)): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'nome_frota','label' => 'Nome da frota','value' => ''.e(old('nome_frota')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'nome_frota','icon' => '1','iconHint' => 'Caso não informe o sistema criará um nome único para identificar este veiculo na frota.','placeholder' => 'Ex.: vei1425']); ?>
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
                            <?php endif; ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="marca_display">Marca <span style="color: red;">*</span></label>
                                    <i class="fas fa-circle-info text-muted ml-1 vc-tooltip-icon" data-toggle="tooltip" data-placement="top" title="Marca do veículo. Ela também influencia a busca do modelo e a consulta FIPE."></i>
                                    <div class="input-group input-group-sm vc-search-lookup-group">
                                        <input type="text" id="marca_display" class="form-control form-control-sm vc-lookup-display" value="<?php echo e($marcaSelecionadaNome); ?>" placeholder="Clique na lupa para pesquisar a marca" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary vc-select-search-btn" id="btnBuscarMarca" title="Pesquisa avançada">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <select name="marca_id" id="marca_id" class="d-none" required>
                                        <option value="">Selecione um valor</option>
                                        <?php $__currentLoopData = $marcas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($key); ?>" <?php echo e((string) $marcaSelecionadaId === (string) $key ? 'selected' : ''); ?>>
                                                <?php echo e($value); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="modelo_display">Modelo <span style="color: red;">*</span></label>
                                    <i class="fas fa-circle-info text-muted ml-1 vc-tooltip-icon" data-toggle="tooltip" data-placement="top" title="Modelo do veículo. O filtro e a consulta FIPE usam a marca selecionada como referência."></i>
                                    <div class="input-group input-group-sm vc-search-lookup-group">
                                        <input type="text" id="modelo_display" class="form-control form-control-sm vc-lookup-display" value="<?php echo e($modeloSelecionadoNome); ?>" placeholder="Clique na lupa para pesquisar o modelo" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary vc-select-search-btn" id="btnBuscarModelo" title="Pesquisa avançada">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <select name="modelo_id" id="modelo_id" class="d-none" required>
                                        <option value="">Selecione um valor</option>
                                        <?php $__currentLoopData = $modelos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($key); ?>" data-ano-modelo="<?php echo e($modelosAno[$key] ?? ''); ?>" <?php echo e((string) $modeloSelecionadoId === (string) $key ? 'selected' : ''); ?>>
                                                <?php echo e($value); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'status_veiculo_id','label' => 'Status','options' => $statusVeiculos,'placeholder' => 'Selecione um valor','selected' => old(
                                            'status_veiculo_id',
                                            isset($veiculo->status_veiculo_id) && $veiculo->status_veiculo_id
                                                ? $veiculo->status_veiculo_id
                                                : $defaultStatusId ?? null,
                                        )] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'status_veiculo_id','icon' => '1','iconHint' => 'Define a situação atual do veículo no sistema.','class' => 'form-control form-control-sm','required' => true]); ?>
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
                            <div class="col-lg-3 col-md-6 d-none" id="div_data_vendido">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'date','name' => 'data_vendido','label' => 'Data de Venda','value' => ''.e(old('data_vendido', $veiculo->data_vendido ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'data_vendido','icon' => '1','iconHint' => 'Preencha apenas quando o veículo já estiver vendido.']); ?>
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
                        </div>
                    </div>

                    
                    <div class="vc-step-pane d-none" data-pane="2">
                        <div class="veiculo-section-title"><i class="fas fa-id-card mr-1"></i>Identificação do Veículo</div>
                        <div class="form-row">
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'number','name' => 'ano_fabricacao','label' => 'Ano de Fabricação','value' => ''.e(old('ano_fabricacao', $veiculo->ano_fabricacao ?? date('Y'))).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'ano_fabricacao','min' => '1900','required' => true]); ?>
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
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'cor','label' => 'Cor','value' => ''.e(old('cor', $veiculo->cor ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'cor','style' => 'text-transform:uppercase;','oninput' => 'this.value = this.value.toUpperCase()','maxlength' => '50','required' => true]); ?>
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
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'placa_licenca','label' => 'Placa','value' => ''.e(old('placa_licenca', $veiculo->placa_licenca ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'placa_licenca','maxlength' => '7','pattern' => '[A-Z]{3}[0-9][A-Z0-9][0-9]{2}|[A-Z]{3}[0-9]{4}','title' => 'Informe uma placa brasileira válida, por exemplo ABC1234 ou ABC1D23','style' => 'text-transform:uppercase;','oninput' => 'this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, \'\').slice(0, 7)','required' => true]); ?>
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
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'renavam','label' => 'RENAVAM','value' => ''.e(old('renavam', $veiculo->renavam ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'renavam']); ?>
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
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'chassi','label' => 'Chassi','value' => ''.e(old('chassi', $veiculo->chassi ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'chassi']); ?>
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
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'numero_motor','label' => 'Número do Motor','value' => ''.e(old('numero_motor', $veiculo->numero_motor ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'numero_motor']); ?>
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
                        </div>
                    </div>

                    
                    <div class="vc-step-pane d-none" data-pane="3" id="fipe-consulta">
                        <div class="veiculo-section-title"><i class="fas fa-coins mr-1"></i>Financeiro do Veículo</div>
                        <div class="veiculo-info-tip">
                            <i class="fas fa-info-circle mr-1"></i>
                            Consulte a tabela FIPE para preencher os dados base do veículo e acompanhar a depreciação.
                        </div>
                        <div class="form-row">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'valor_compra','label' => 'Valor de Compra','value' => ''.e(old('valor_compra', $veiculo->valor_compra ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['inputmode' => 'decimal','autocomplete' => 'off','data-money-field' => '1','class' => 'form-control form-control-sm','id' => 'valor_compra']); ?>
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
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'valor_fipe','label' => 'Valor FIPE','value' => ''.e(old('valor_fipe', $veiculo->valor_fipe ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['inputmode' => 'decimal','autocomplete' => 'off','data-money-field' => '1','class' => 'form-control form-control-sm','id' => 'valor_fipe']); ?>
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
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'number','name' => 'taxa_depreciacao_anual','label' => 'Taxa de Depreciacao Anual (%)','value' => ''.e(old('taxa_depreciacao_anual', $veiculo->taxa_depreciacao_anual ?? 15)).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['step' => '0.01','min' => '0','max' => '100','class' => 'form-control form-control-sm','id' => 'taxa_depreciacao_anual']); ?>
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
                            <div class="col-lg-3 col-md-6 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-3"
                                    id="btnConsultarFipe">
                                    <i class="fas fa-search-dollar mr-1"></i>Consultar FIPE
                                </button>
                            </div>
                        </div>
                        <div id="fipeLookupStatus" class="alert alert-danger d-none mt-2 mb-3" role="alert"></div>
                        <div class="form-row">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'codigo_fipe','label' => 'Código FIPE','value' => ''.e(old('codigo_fipe', $veiculo->codigo_fipe ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'codigo_fipe','readonly' => true]); ?>
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
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'referencia_fipe','label' => 'Referência FIPE','value' => ''.e(old('referencia_fipe', $veiculo->referencia_fipe ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'referencia_fipe','readonly' => true]); ?>
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
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'date','name' => 'data_consulta_fipe','label' => 'Data da Consulta','value' => ''.e(old('data_consulta_fipe', isset($veiculo->data_consulta_fipe) ? \Carbon\Carbon::parse($veiculo->data_consulta_fipe)->format('Y-m-d') : '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'data_consulta_fipe','readonly' => true]); ?>
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
                        </div>

                        <div class="row" id="depreciacao-config">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card card-outline card-primary h-100 mb-0">
                                    <div class="card-header py-2">
                                        <strong>Aquisição</strong>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="form-group form-group-veiculo">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'valor_compra','label' => 'Valor de compra','value' => ''.e(old('valor_compra', $veiculo->valor_compra ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['inputmode' => 'decimal','autocomplete' => 'off','data-money-field' => '1','class' => 'form-control form-control-sm','id' => 'valor_compra']); ?>
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
                                        <div class="form-group form-group-veiculo mb-0">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'date','name' => 'data_compra','label' => 'Data de compra','value' => ''.e(old('data_compra', $veiculo->data_compra ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'data_compra']); ?>
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
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card card-outline card-info h-100 mb-0">
                                    <div class="card-header py-2">
                                        <strong>Documentação</strong>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="form-group form-group-veiculo">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'date','name' => 'data_placa_licenca','label' => 'Emissão da placa/licença','value' => ''.e(old('data_placa_licenca', $veiculo->data_placa_licenca ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'data_placa_licenca','hint' => 'Deve ser menor ou igual à validade da documentação/licença.','required' => true]); ?>
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
                                        <div class="form-group form-group-veiculo mb-0">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'date','name' => 'data_validade','label' => 'Validade da documentação/licença','value' => ''.e(old('data_validade', $veiculo->data_validade ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'data_validade','hint' => 'Deve ser maior ou igual à emissão da placa/licença.','required' => true]); ?>
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
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-12 mb-3">
                                <div class="card card-outline card-success h-100 mb-0">
                                    <div class="card-header py-2">
                                        <strong>Quilometragem</strong>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="form-group form-group-veiculo">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'number','name' => 'kms','label' => 'Km atual','value' => ''.e(old('kms', $veiculo->kms ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'kms','hint' => 'Base usada para validar os limites abaixo.','min' => '0']); ?>
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
                                        <div class="form-group form-group-veiculo mb-0">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'number','name' => 'limite_kms','label' => 'Limite de km por aluguel','value' => ''.e(old('limite_kms', $veiculo->limite_kms ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'limite_kms','hint' => 'Não pode ser menor que o Km atual.','min' => '0']); ?>
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
                                        <div class="form-group form-group-veiculo mt-3 mb-0">
                                            <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'number','name' => 'proxima_revisao_kms','label' => 'Km da próxima revisão','value' => ''.e(old('proxima_revisao_kms', $veiculo->proxima_revisao_kms ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'proxima_revisao_kms','hint' => 'Não pode ser menor que o Km atual.','min' => '0']); ?>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <?php if($mostrarAbaDisponibilidade): ?>
                        <div class="vc-step-pane d-none" data-pane="4">
                            <div class="veiculo-section-title"><i class="fas fa-map-marker-alt mr-1"></i>Disponibilidade
                                Inicial</div>
                            <div class="veiculo-info-tip">
                                <i class="fas fa-info-circle mr-1"></i>
                                Defina em qual estação o veículo inicia e a data/hora inicial de disponibilidade.
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-lg-5 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'local_disponibilidade','label' => 'Estação de origem','options' => $estacoes,'placeholder' => 'Selecione um valor','selected' => old(
                                                'local_disponibilidade',
                                                $veiculo->local_disponibilidade ?? null,
                                            )] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm']); ?>
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
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group form-group-veiculo">
                                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'datetime-local','name' => 'data_hora_disponibilidade','label' => 'Data e Hora de Disponibilidade','value' => ''.e(old('data_hora_disponibilidade', $veiculo->data_hora_disponibilidade ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'data_hora_disponibilidade']); ?>
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
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <div class="vc-wizard-nav mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm vc-nav-btn vc-nav-btn--back d-none" id="btnPrev">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-sm vc-nav-btn vc-nav-btn--next" id="btnNext">
                            Próximo <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>

                    <div class="veiculo-save-dock js-veiculo-save-dock d-none">
                        <div class="veiculo-save-dock__content">
                            <div>
                                <strong>Salvar alterações</strong>
                                <small class="d-block text-muted">Disponível sem descer até o fim da página.</small>
                            </div>
                            <button type="submit" class="btn btn-sm vc-nav-btn vc-nav-btn--save"
                                id="btnSalvarFlutuante" <?php echo e($bloquearSalvarVeiculo ? 'disabled' : ''); ?>>
                                <i class="fas fa-save mr-1"></i> Salvar alterações
                            </button>
                        </div>
                    </div>
                    <?php if($bloquearSalvarVeiculo): ?>
                        <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                            Cadastre os itens obrigatórios acima para liberar a criação do veículo.
                        </small>
                    <?php endif; ?>
                </form>

                <?php if (isset($component)) { $__componentOriginald90448cde4b2f348f499ac36119d4b7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald90448cde4b2f348f499ac36119d4b7a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.veiculo.busca-entidade-modal','data' => ['modalId' => 'modalBuscarGrupoFrotaVeiculo','title' => 'Pesquisar grupo de frota','buscarUrl' => ''.e(route('veiculo.buscarGruposModal')).'','triggerId' => 'btnBuscarGrupoFrota','targetSelectId' => 'grupo_frota_id','targetDisplayId' => 'grupo_frota_display','placeholder' => 'Digite o nome do grupo','extraParams' => [
                        ['name' => 'tipo_frota_id', 'selector' => '#tipo_frota_id']
                    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('veiculo.busca-entidade-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['modal-id' => 'modalBuscarGrupoFrotaVeiculo','title' => 'Pesquisar grupo de frota','buscar-url' => ''.e(route('veiculo.buscarGruposModal')).'','trigger-id' => 'btnBuscarGrupoFrota','target-select-id' => 'grupo_frota_id','target-display-id' => 'grupo_frota_display','placeholder' => 'Digite o nome do grupo','extra-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                        ['name' => 'tipo_frota_id', 'selector' => '#tipo_frota_id']
                    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald90448cde4b2f348f499ac36119d4b7a)): ?>
<?php $attributes = $__attributesOriginald90448cde4b2f348f499ac36119d4b7a; ?>
<?php unset($__attributesOriginald90448cde4b2f348f499ac36119d4b7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald90448cde4b2f348f499ac36119d4b7a)): ?>
<?php $component = $__componentOriginald90448cde4b2f348f499ac36119d4b7a; ?>
<?php unset($__componentOriginald90448cde4b2f348f499ac36119d4b7a); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginald90448cde4b2f348f499ac36119d4b7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald90448cde4b2f348f499ac36119d4b7a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.veiculo.busca-entidade-modal','data' => ['modalId' => 'modalBuscarMarcaVeiculo','title' => 'Pesquisar marca','buscarUrl' => ''.e(route('veiculo.buscarMarcasModal')).'','triggerId' => 'btnBuscarMarca','targetSelectId' => 'marca_id','targetDisplayId' => 'marca_display','placeholder' => 'Digite o nome da marca','extraParams' => [
                        ['name' => 'grupo_frota_id', 'selector' => '#grupo_frota_id']
                    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('veiculo.busca-entidade-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['modal-id' => 'modalBuscarMarcaVeiculo','title' => 'Pesquisar marca','buscar-url' => ''.e(route('veiculo.buscarMarcasModal')).'','trigger-id' => 'btnBuscarMarca','target-select-id' => 'marca_id','target-display-id' => 'marca_display','placeholder' => 'Digite o nome da marca','extra-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                        ['name' => 'grupo_frota_id', 'selector' => '#grupo_frota_id']
                    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald90448cde4b2f348f499ac36119d4b7a)): ?>
<?php $attributes = $__attributesOriginald90448cde4b2f348f499ac36119d4b7a; ?>
<?php unset($__attributesOriginald90448cde4b2f348f499ac36119d4b7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald90448cde4b2f348f499ac36119d4b7a)): ?>
<?php $component = $__componentOriginald90448cde4b2f348f499ac36119d4b7a; ?>
<?php unset($__componentOriginald90448cde4b2f348f499ac36119d4b7a); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginald90448cde4b2f348f499ac36119d4b7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald90448cde4b2f348f499ac36119d4b7a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.veiculo.busca-entidade-modal','data' => ['modalId' => 'modalBuscarModeloVeiculo','title' => 'Pesquisar modelo','buscarUrl' => ''.e(route('veiculo.buscarModelosModal')).'','triggerId' => 'btnBuscarModelo','targetSelectId' => 'modelo_id','targetDisplayId' => 'modelo_display','placeholder' => 'Digite o nome do modelo','extraParams' => [
                        ['name' => 'grupo_frota_id', 'selector' => '#grupo_frota_id'],
                        ['name' => 'marca_id', 'selector' => '#marca_id']
                    ],'showMarcaColumn' => true,'afterSelect' => 'veiculoModeloSelecionadoFromModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('veiculo.busca-entidade-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['modal-id' => 'modalBuscarModeloVeiculo','title' => 'Pesquisar modelo','buscar-url' => ''.e(route('veiculo.buscarModelosModal')).'','trigger-id' => 'btnBuscarModelo','target-select-id' => 'modelo_id','target-display-id' => 'modelo_display','placeholder' => 'Digite o nome do modelo','extra-params' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                        ['name' => 'grupo_frota_id', 'selector' => '#grupo_frota_id'],
                        ['name' => 'marca_id', 'selector' => '#marca_id']
                    ]),'show-marca-column' => true,'after-select' => 'veiculoModeloSelecionadoFromModal']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald90448cde4b2f348f499ac36119d4b7a)): ?>
<?php $attributes = $__attributesOriginald90448cde4b2f348f499ac36119d4b7a; ?>
<?php unset($__attributesOriginald90448cde4b2f348f499ac36119d4b7a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald90448cde4b2f348f499ac36119d4b7a)): ?>
<?php $component = $__componentOriginald90448cde4b2f348f499ac36119d4b7a; ?>
<?php unset($__componentOriginald90448cde4b2f348f499ac36119d4b7a); ?>
<?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .vc-select-search-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .vc-search-lookup-group .form-control[readonly] {
            background: #fff;
            cursor: pointer;
        }

        .vc-lookup-display {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .vc-tooltip-icon {
            cursor: help;
            font-size: .8rem;
            opacity: .9;
        }

        .vc-tooltip-icon:hover {
            opacity: 1;
        }

        .veiculo-section-title {
            margin-bottom: .75rem;
        }

        .form-group-veiculo {
            margin-bottom: .8rem;
        }

        .form-group-veiculo .form-control-sm {
            min-height: 36px;
            padding-top: .35rem;
            padding-bottom: .35rem;
        }

        .card-veiculo {
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header-veiculo {
            background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
            padding: 1.1rem 1.5rem;
        }

        .card-header-veiculo h4 {
            color: #0f2740;
            font-weight: 700;
        }

        /* â”€â”€ Wizard â”€â”€ */
        .vc-wizard-steps {
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .vc-step-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            background: none;
            border: none;
            padding: 0 8px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .vc-step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #d0daea;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: #94a3b8;
            transition: all .2s;
        }

        .vc-step-btn.active .vc-step-circle {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .vc-step-btn.done .vc-step-circle {
            background: #ecfdf5;
            border-color: #10b981;
            color: #10b981;
        }

        .vc-step-btn.warning .vc-step-circle {
            background: #fff7ed;
            border-color: #f59e0b;
            color: #f59e0b;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, .12);
        }

        .vc-step-label {
            font-size: .72rem;
            font-weight: 600;
            color: #94a3b8;
            white-space: nowrap;
        }

        .vc-step-btn.active .vc-step-label {
            color: #2563eb;
        }

        .vc-step-btn.done .vc-step-label {
            color: #10b981;
        }

        .vc-step-btn.warning .vc-step-label {
            color: #b45309;
        }

        .vc-step-line {
            flex: 1;
            height: 2px;
            background: #e5eaf2;
            min-width: 16px;
            margin-bottom: 18px;
        }

        /* â”€â”€ Form â”€â”€ */
        .veiculo-section-title {
            font-size: .96rem;
            font-weight: 700;
            color: #21364f;
            letter-spacing: .02em;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5eaf2;
            margin-bottom: 14px;
        }

        .form-group-veiculo {
            margin-bottom: 1rem;
        }

        .veiculo-info-tip {
            border: 1px solid #dbe6f3;
            border-radius: 10px;
            background: #f8fbff;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 10px;
            margin-bottom: 10px;
        }

        .card-veiculo .form-control,
        .card-veiculo .form-select {
            border-radius: 10px;
            border-color: #d8e1ec;
            min-height: 38px;
        }

        .card-veiculo .form-control:focus,
        .card-veiculo .form-select:focus {
            border-color: #4b80ff;
            box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
        }

        .vc-nav-btn {
            min-width: 138px;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .vc-nav-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
        }

        .vc-nav-btn--back {
            background: #fff;
            border: 1px solid #d6e2f1;
            color: #334155;
        }

        .vc-nav-btn--back:hover {
            background: #f8fbff;
            color: #0f2740;
        }

        .vc-nav-btn--next {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: 1px solid #1d4ed8;
            color: #fff;
        }

        .vc-nav-btn--next:hover {
            color: #fff;
        }

        .vc-nav-btn--save {
            background: linear-gradient(135deg, #16a34a, #15803d);
            border: 1px solid #15803d;
            color: #fff;
        }

        .vc-nav-btn--save:hover {
            color: #fff;
        }

        .js-veiculo-validacao-alerta {
            box-shadow: 0 10px 28px rgba(245, 158, 11, .12);
        }

        .js-veiculo-validacao-alerta.is-live {
            animation: veiculoPulse .35s ease-out;
        }

        .veiculo-save-dock {
            position: fixed;
            right: 24px;
            bottom: 20px;
            z-index: 1060;
            width: min(360px, calc(100vw - 32px));
        }

        .veiculo-save-dock__content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #dbe6f3;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 14px 36px rgba(15, 23, 42, .16);
            backdrop-filter: blur(8px);
        }

        .veiculo-save-dock__content strong {
            display: block;
            font-size: .92rem;
            color: #0f2740;
        }

        .veiculo-save-dock__content small {
            font-size: .76rem;
            line-height: 1.25;
        }

        #formulario {
            padding-bottom: 104px;
        }

        @media (max-width:575px) {
            .card-header-veiculo {
                padding: .85rem;
            }

            .card-body {
                padding: .85rem !important;
            }

            .form-group-veiculo {
                margin-bottom: .8rem;
            }

            .veiculo-save-dock {
                left: 12px;
                right: 12px;
                bottom: 12px;
                width: auto;
            }

            .veiculo-save-dock__content {
                padding: 11px 12px;
            }
        }

        @keyframes veiculoPulse {
            from { transform: scale(.99); opacity: .7; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
<?php $__env->stopSection(); ?>

    <?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            if (typeof $.fn.tooltip === 'function') {
                $('.vc-tooltip-icon, [data-toggle="tooltip"]').tooltip({
                    trigger: 'hover focus',
                    container: 'body',
                    boundary: 'window',
                    placement: 'top'
                });
            }

            // ── Wizard Steps ──────────────────────────────────────────────────
            const $panes   = $('.vc-step-pane');
            const $stepBtns = $('.vc-step-btn');
            const $btnPrev = $('#btnPrev');
            const $btnNext = $('#btnNext');
            const $dockSalvar = $('.js-veiculo-save-dock');
            const $alertaPendencias = $('.js-veiculo-validacao-alerta');
            const $listaPendencias = $('.js-veiculo-validacao-lista');
            const $resumoPendencias = $('.js-veiculo-validacao-resumo');
            const $botaoIrPassoBase = $('.js-veiculo-ir-passo-base');
            const $formulario = $('#formulario');
            const isEdit = <?php echo json_encode(isset($veiculo), 15, 512) ?>;
            const totalSteps = $panes.length;
            let currentStep = Math.min(<?php echo e($startStep); ?>, totalSteps);
            let ultimoPassoPendente = currentStep;

            const STEP_LABELS = {};
            $stepBtns.each(function () {
                const step = parseInt($(this).data('step'));
                STEP_LABELS[step] = $(this).find('.vc-step-label').first().text().trim() || ('Etapa ' + step);
            });

            function escaparHtml(texto) {
                return String(texto ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function obterRotuloCampo($campo) {
                const rotulo = String($campo.data('field-label') ?? '').trim();
                if (rotulo) {
                    return rotulo;
                }

                const $group = $campo.closest('.form-group');
                const textoLabel = String($group.find('label').first().text() ?? '').trim();
                if (textoLabel) {
                    return textoLabel;
                }

                return String($campo.attr('name') ?? 'Campo').replace(/\[\]$/, '');
            }

            function obterPassoCampo($campo) {
                const pane = parseInt($campo.closest('.vc-step-pane').data('pane'));
                return Number.isNaN(pane) ? 1 : pane;
            }

            function obterElementoFoco($campo) {
                const mapa = {
                    grupo_frota_id: '#grupo_frota_display',
                    marca_id: '#marca_display',
                    modelo_id: '#modelo_display',
                };

                const id = String($campo.attr('id') || '');
                const seletor = mapa[id];

                if (seletor && $(seletor).length) {
                    return $(seletor);
                }

                return $campo;
            }

            function obterAjudaCampo($campo) {
                const id = String($campo.attr('id') || '');

                const ajuda = {
                    data_placa_licenca: 'Deve ser menor ou igual à Validade da Documentação/Licença.',
                    data_validade: 'Deve ser maior ou igual à Emissão da Placa/Licença.',
                    data_proxima_manutencao: 'Deve ser hoje ou uma data futura.',
                    data_proxima_inspecao: 'Deve ser hoje ou uma data futura.',
                    kms: 'Base usada para validar os limites abaixo.',
                    limite_kms: 'Não pode ser menor que o Km Atual.',
                    proxima_revisao_kms: 'Não pode ser menor que o Km Atual.',
                    proxima_manutencao_kms: 'Não pode ser menor que o Km Atual.',
                };

                return ajuda[id] || '';
            }

            function limparPendencias() {
                $alertaPendencias.addClass('d-none').removeClass('is-live');
                $listaPendencias.empty();
                $resumoPendencias.empty();
                $('.vc-step-btn').removeClass('warning');
                $formulario.find('.js-validacao-manual')
                    .removeClass('is-invalid js-validacao-manual')
                    .removeAttr('aria-invalid')
                    .removeAttr('title')
                    .removeAttr('data-toggle')
                    .removeAttr('data-placement');
            }

            function montarLinhaPendencia(item) {
                const stepLabel = STEP_LABELS[item.step] || ('Etapa ' + item.step);
                return [
                    '<div class="pre-requisitos-alerta__detail-item">',
                    '<span class="pre-requisitos-alerta__detail-label">' + escaparHtml(item.label) + '</span>',
                    '<span class="badge badge-warning text-dark">' + escaparHtml(stepLabel) + '</span>',
                    item.message
                        ? '<div class="pre-requisitos-alerta__detail-message">' + escaparHtml(item.message) + '</div>'
                        : '',
                    '</div>',
                ].join('');
            }

            function mostrarPendencias(pendencias) {
                limparPendencias();

                if (!pendencias.length) {
                    return true;
                }

                const passosPendentes = [...new Set(pendencias.map(function (item) {
                    return item.step;
                }))];
                ultimoPassoPendente = Math.min(...passosPendentes);

                passosPendentes.forEach(function (step) {
                    $('.vc-step-btn[data-step="' + step + '"]').addClass('warning');
                });

                $resumoPendencias.text('Encontramos ' + pendencias.length + ' item(ns) obrigatório(s) pendente(s).');
                $botaoIrPassoBase.text('Ir para o passo ' + (STEP_LABELS[ultimoPassoPendente] || 'correto'));
                $listaPendencias.html(pendencias.map(montarLinhaPendencia).join(''));
                $alertaPendencias.removeClass('d-none').addClass('is-live');

                if (currentStep !== ultimoPassoPendente) {
                    goToStep(ultimoPassoPendente);
                }

                window.requestAnimationFrame(function () {
                    if ($alertaPendencias.length) {
                        $alertaPendencias[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                    }
                });

                const $primeiroCampoInvalido = pendencias[0]?.focusTarget || pendencias[0]?.field;
                if ($primeiroCampoInvalido && $primeiroCampoInvalido.length) {
                    window.setTimeout(function () {
                        $primeiroCampoInvalido.trigger('focus');
                    }, 150);
                }

                return false;
            }

            function coletarPendencias($escopo) {
                const pendencias = [];

                $escopo.find(':invalid').each(function () {
                    const $campo = $(this);

                    if ($campo.is(':disabled')) {
                        return;
                    }

                    $campo.addClass('is-invalid js-validacao-manual').attr('aria-invalid', 'true');
                    const mensagem = String($campo[0].validationMessage || '').trim();
                    const ajuda = mensagem || obterAjudaCampo($campo);
                    if (ajuda) {
                        $campo.attr('title', ajuda);
                    }
                    pendencias.push({
                        step: obterPassoCampo($campo),
                        label: obterRotuloCampo($campo),
                        message: ajuda,
                        field: $campo,
                        focusTarget: obterElementoFoco($campo),
                    });
                });

                return pendencias;
            }

            function goToStep(step) {
                if (step < 1 || step > totalSteps) return;
                currentStep = step;

                // Mostra/oculta panes
                $panes.each(function() {
                    const p = parseInt($(this).data('pane'));
                    $(this).toggleClass('d-none', p !== step);
                });

                // Atualiza visual dos step buttons
                $stepBtns.each(function() {
                    const s = parseInt($(this).data('step'));
                    $(this).removeClass('active done');
                    if (s === step)  $(this).addClass('active');
                    if (s < step)   $(this).addClass('done');
                });

                // Botões de navegação
                $btnPrev.toggleClass('d-none', step <= 1);
                $btnNext.toggleClass('d-none', step === totalSteps);
                $dockSalvar.toggleClass('d-none', !(isEdit || step === totalSteps));

                // Scroll para o topo do card
                $('html, body').animate({ scrollTop: $('.card-veiculo').offset().top - 20 }, 150);
            }

            // Clique direto nos step buttons
            $stepBtns.on('click', function() {
                const target = parseInt($(this).data('step'));
                // Permite ir para steps anteriores livremente; para avançar, valida o atual
                if (target <= currentStep) {
                    goToStep(target);
                } else {
                    // Avança um de cada vez validando
                    for (let s = currentStep; s < target; s++) {
                        const $pane = $panes.filter('[data-pane="' + s + '"]').removeClass('d-none');
                        const invalids = $pane.find(':invalid').length;
                        $pane.addClass('d-none');
                        if (invalids > 0) {
                            goToStep(s);
                            return;
                        }
                    }
                    goToStep(target);
                }
            });

            $btnNext.on('click', function() {
                // Valida campos do pane atual antes de avançar
                const $pane = $panes.filter('[data-pane="' + currentStep + '"]');
                const pendencias = coletarPendencias($pane);
                if (pendencias.length > 0) {
                    $formulario.addClass('was-validated');
                    mostrarPendencias(pendencias);
                    return;
                }
                $formulario.removeClass('was-validated');
                limparPendencias();
                goToStep(currentStep + 1);
            });

            $btnPrev.on('click', function() {
                goToStep(currentStep - 1);
            });

            $botaoIrPassoBase.on('click', function () {
                goToStep(ultimoPassoPendente);
            });

            $formulario.on('input change', '[required], .vc-step-pane input, .vc-step-pane select, .vc-step-pane textarea', function () {
                const $campo = $(this);
                $campo.removeClass('is-invalid js-validacao-manual').removeAttr('aria-invalid');
            });

            // Inicializa no step correto
            goToStep(currentStep);
            // ─────────────────────────────────────────────────────────────────

            function syncLookupDisplay(selectSelector, displaySelector) {
                const $select = $(selectSelector);
                const $display = $(displaySelector);
                if (!$select.length || !$display.length) {
                    return;
                }

                const selectedOption = $select.find('option:selected');
                const value = $select.val();
                $display.val(value ? (selectedOption.text() || '') : '');
            }

            function formatarMoedaBR(valor) {
                const numero = Number(valor || 0);
                return new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(numero);
            }

            function parseMoedaBR(valor) {
                if (valor === null || valor === undefined) {
                    return '';
                }

                const texto = String(valor).trim();
                if (texto === '') {
                    return '';
                }

                const somenteNumeros = texto.replace(/[^\d,.-]/g, '');
                if (somenteNumeros === '') {
                    return '';
                }

                if (somenteNumeros.includes(',')) {
                    return somenteNumeros.replace(/\./g, '').replace(',', '.');
                }

                return somenteNumeros.replace(/\./g, '');
            }

            function formatarCamposMoeda() {
                $('[data-money-field="1"]').each(function() {
                    const valorNormalizado = parseMoedaBR($(this).val());
                    if (valorNormalizado === '') {
                        $(this).val('');
                        return;
                    }

                    const numero = Number(valorNormalizado);
                    if (!Number.isNaN(numero)) {
                        $(this).val(formatarMoedaBR(numero));
                    }
                });
            }

            function desformatarCamposMoedaParaEnvio() {
                $('[data-money-field="1"]').each(function() {
                    const valorNormalizado = parseMoedaBR($(this).val());
                    $(this).val(valorNormalizado);
                });
            }

            function obterAnoModeloSelecionado() {
                const option = $('#modelo_id option:selected');
                const anoOption = parseInt(option.data('ano-modelo') || option.data('anoModelo') || 0, 10);
                if (!Number.isNaN(anoOption) && anoOption > 0) {
                    return anoOption;
                }

                const anoFabricacao = parseInt($('#ano_fabricacao').val() || 0, 10);
                return Number.isNaN(anoFabricacao) || anoFabricacao <= 0 ? null : anoFabricacao;
            }

            $(document).on('focus', '[data-money-field="1"]', function() {
                const valorNormalizado = parseMoedaBR($(this).val());
                if (valorNormalizado !== '') {
                    $(this).val(valorNormalizado);
                }
            });

            $(document).on('blur', '[data-money-field="1"]', function() {
                const valorNormalizado = parseMoedaBR($(this).val());
                if (valorNormalizado === '') {
                    $(this).val('');
                    return;
                }

                const numero = Number(valorNormalizado);
                if (!Number.isNaN(numero)) {
                    $(this).val(formatarMoedaBR(numero));
                }
            });

            function fireSwal(options) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    return window.Swal.fire(options);
                }

                const title = options?.title || options?.text || 'Aviso';
                const text = options?.text && options.text !== title ? `\n${options.text}` : '';
                window.alert(`${title}${text}`);
            }

            const $tipoFrota = $('#tipo_frota_id');
            const $marca = $('#marca_id');
            const $modelo = $('#modelo_id');
            const $btnConsultarFipe = $('#btnConsultarFipe');
            const $valorFipe = $('#valor_fipe');
            const $codigoFipe = $('#codigo_fipe');
            const $referenciaFipe = $('#referencia_fipe');
            const $dataConsultaFipe = $('#data_consulta_fipe');
            const $taxaDepreciacao = $('#taxa_depreciacao_anual');
            const $fipeLookupStatus = $('#fipeLookupStatus');
            const fipeUrl = '<?php echo e(route('veiculo.consultarFipe')); ?>';
            let ultimaFalhaFipe = null;

            function limparStatusFipe() {
                $fipeLookupStatus.addClass('d-none').removeClass('alert-success alert-warning alert-danger').empty();
            }

            function mostrarStatusFipe(tipo, mensagem) {
                $fipeLookupStatus
                    .removeClass('d-none alert-success alert-warning alert-danger')
                    .addClass(`alert-${tipo}`)
                    .html(mensagem);
            }

            function limparCacheFalhaFipe() {
                ultimaFalhaFipe = null;
                limparStatusFipe();
            }

            function limparMarcaSelecionada() {
                $marca.val('');
                syncLookupDisplay('#marca_id', '#marca_display');
            }

            function limparModeloSelecionado() {
                $modelo.val('');
                syncLookupDisplay('#modelo_id', '#modelo_display');
            }

            function atualizarEstadoBuscaModelo() {
                const temGrupo = String($('#grupo_frota_id').val() || '').trim() !== '';
                const $btnBuscarModelo = $('#btnBuscarModelo');

                $btnBuscarModelo.prop('disabled', !temGrupo);
                $btnBuscarModelo.attr(
                    'title',
                    temGrupo ? 'Pesquisa avançada' : 'Selecione um grupo de frota antes de pesquisar o modelo'
                );
            }

            function limparMarcaEmModeloDependente() {
                limparMarcaSelecionada();
                limparModeloSelecionado();
                atualizarEstadoBuscaModelo();
            }

            formatarCamposMoeda();

            $('#marca_id, #modelo_id').on('change input', function() {
                limparCacheFalhaFipe();
            });

            window.carregarGruposVeiculo = function(tipoId, selectValor) {
                const $grupoFrota = $('#grupo_frota_id');
                const textoAtual = $('#grupo_frota_display').val();

                $grupoFrota.prop('disabled', true).html('<option value="">Carregando...</option>');

                $.get('<?php echo e(route('veiculo.gruposPorTipo')); ?>', {
                    tipo_frota_id: tipoId
                }, function(grupos) {
                    let html = '<option value="">Selecione um valor</option>';
                    grupos.forEach(function(g) {
                        const sel = selectValor && String(selectValor) === String(g.id) ? ' selected' : '';
                        html += `<option value="${g.id}"${sel}>${g.nome}</option>`;
                    });

                    $grupoFrota.html(html).prop('disabled', false);
                    syncLookupDisplay('#grupo_frota_id', '#grupo_frota_display');
                    if (!selectValor && !grupos.length) {
                        $('#grupo_frota_display').val('');
                    }
                }).fail(function() {
                    $grupoFrota.html('<option value="">Selecione um valor</option>').prop('disabled', false);
                    if (!selectValor) {
                        $('#grupo_frota_display').val('');
                    } else if (textoAtual) {
                        $('#grupo_frota_display').val(textoAtual);
                    }
                });
            };

            window.carregarModelosVeiculo = function(marcaId, selectValor, selectTexto) {
                const $modeloLocal = $('#modelo_id');
                const textoAtual = $('#modelo_display').val();

                if (!marcaId) {
                    $modeloLocal.prop('disabled', false).html('<option value="">Selecione um valor</option>');
                    limparModeloSelecionado();
                    atualizarEstadoBuscaModelo();
                    return;
                }

                $modeloLocal.prop('disabled', true).html('<option value="">Carregando...</option>');

                $.get('<?php echo e(route('veiculo.modelosPorMarca')); ?>', {
                    marca_id: marcaId
                }, function(modelos) {
                    let html = '<option value="">Selecione um valor</option>';
                    modelos.forEach(function(m) {
                        const sel = selectValor && String(selectValor) === String(m.id) ? ' selected' : '';
                        const anoModelo = m.ano_modelo || '';
                        html += `<option value="${m.id}" data-ano-modelo="${anoModelo}"${sel}>${m.nome}</option>`;
                    });

                    $modeloLocal.html(html).prop('disabled', false);
                    if (selectValor) {
                        $modeloLocal.val(String(selectValor));
                    }
                    syncLookupDisplay('#modelo_id', '#modelo_display');
                    if (selectTexto) {
                        $('#modelo_display').val(selectTexto);
                    }
                    atualizarEstadoBuscaModelo();
                }).fail(function() {
                    $modeloLocal.html('<option value="">Selecione um valor</option>').prop('disabled', false);
                    if (!selectValor) {
                        $('#modelo_display').val('');
                    } else if (textoAtual) {
                        $('#modelo_display').val(textoAtual);
                    }
                    atualizarEstadoBuscaModelo();
                });
            };

            window.veiculoModeloSelecionadoFromModal = function(item) {
                if (!item) {
                    return;
                }

                if (item.marca_id) {
                    $('#marca_id').val(String(item.marca_id));
                    syncLookupDisplay('#marca_id', '#marca_display');
                    atualizarEstadoBuscaModelo();
                }

                window.carregarModelosVeiculo(item.marca_id || $marca.val(), item.id, item.nome);
            };

            $('#grupo_frota_id').on('change', function() {
                syncLookupDisplay('#grupo_frota_id', '#grupo_frota_display');
                limparCacheFalhaFipe();
                limparMarcaEmModeloDependente();
                atualizarEstadoBuscaModelo();
            });

            $('#marca_id').on('change', function() {
                syncLookupDisplay('#marca_id', '#marca_display');
                limparCacheFalhaFipe();
                const marcaId = String($(this).val() || '').trim();
                if (!marcaId) {
                    limparModeloSelecionado();
                    atualizarEstadoBuscaModelo();
                    return;
                }

                limparModeloSelecionado();
                window.carregarModelosVeiculo(marcaId, null);
            });

            $('#modelo_id').on('change', function() {
                syncLookupDisplay('#modelo_id', '#modelo_display');
            });

            $('#tipo_frota_id').on('change', function() {
                window.carregarGruposVeiculo($(this).val(), $('#grupo_frota_id').val() || null);
            });

            syncLookupDisplay('#grupo_frota_id', '#grupo_frota_display');
            syncLookupDisplay('#marca_id', '#marca_display');
            syncLookupDisplay('#modelo_id', '#modelo_display');
            atualizarEstadoBuscaModelo();

            const tipoInicial = $tipoFrota.val();
            const marcaInicial = $marca.val();
            const modeloInicial = $modelo.val();
            if (tipoInicial) {
                window.carregarGruposVeiculo(tipoInicial, $('#grupo_frota_id').val() || null);
            }
            if (marcaInicial) {
                window.carregarModelosVeiculo(marcaInicial, modeloInicial || null, $('#modelo_display').val() || null);
            } else {
                limparModeloSelecionado();
            }
            atualizarEstadoBuscaModelo();

            function consultarFipe() {
                const marcaId = $marca.val();
                const modeloId = $modelo.val();
                const anoFabricacao = parseInt($('#ano_fabricacao').val() || 0, 10);
                const anoModelo = obterAnoModeloSelecionado();
                const chaveConsulta = [marcaId || '', modeloId || '', anoFabricacao || anoModelo || ''].join('|');

                if (!marcaId || !modeloId ) {
                    fireSwal({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'O modelo selecionado precisa ter ano modelo cadastrado para consultar a FIPE.',
                        showConfirmButton: false,
                        timer: 5000
                    });
                    return;
                }

                if (ultimaFalhaFipe && ultimaFalhaFipe.chave === chaveConsulta) {
                    mostrarStatusFipe('warning', `<strong>Atenção:</strong> ${ultimaFalhaFipe.mensagem}`);
                    fireSwal({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: ultimaFalhaFipe.mensagem,
                        showConfirmButton: false,
                        timer: 5000
                    });
                    return;
                }

                limparStatusFipe();
                const originalButton = $btnConsultarFipe.html();
                $btnConsultarFipe.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Consultando...');

                $.get(fipeUrl, {
                    marca_id: marcaId,
                    modelo_id: modeloId,
                    ano_fabricacao: anoFabricacao > 0 ? anoFabricacao : null,
                    ano_modelo: anoModelo > 0 ? anoModelo : null
                }).done(function(resp) {
                    // Resposta com mensagem de erro (HTTP 200 mas sem dados)
                    if (resp.message && !resp.valor_fipe && resp.valor_fipe !== 0) {
                        ultimaFalhaFipe = {
                            chave: chaveConsulta,
                            mensagem: resp.message
                        };
                        mostrarStatusFipe('warning', `<strong>FIPE indisponível:</strong> ${resp.message}`);
                        fireSwal({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: resp.message,
                            showConfirmButton: false,
                            timer: 5000
                        });
                        return;
                    }

                    if (resp.valor_fipe !== null && resp.valor_fipe !== undefined) {
                        $valorFipe.val(formatarMoedaBR(resp.valor_fipe));
                    }
                    if (resp.codigo_fipe) {
                        $codigoFipe.val(resp.codigo_fipe);
                    }
                    if (resp.referencia_fipe) {
                        $referenciaFipe.val(resp.referencia_fipe);
                    }
                    if (resp.data_consulta_fipe) {
                        $dataConsultaFipe.val(resp.data_consulta_fipe);
                    }
                    if (!$taxaDepreciacao.val()) {
                        $taxaDepreciacao.val(15);
                    }

                    ultimaFalhaFipe = null;
                    limparStatusFipe();
                    fireSwal({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'FIPE consultada com sucesso',
                        text: resp.valor_fipe ? 'Valor: ' + formatarMoedaBR(resp.valor_fipe) : 'Dados atualizados.',
                        showConfirmButton: false,
                        timer: 4000
                    });
                }).fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Não foi possível consultar a FIPE.';
                    if (xhr.status === 422) {
                        ultimaFalhaFipe = {
                            chave: chaveConsulta,
                            mensagem: message
                        };
                        mostrarStatusFipe('warning', `<strong>FIPE indisponível:</strong> ${message}`);
                    }
                    fireSwal({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: message,
                        showConfirmButton: false,
                        timer: 5000
                    });
                }).always(function() {
                    $btnConsultarFipe.prop('disabled', false).html(originalButton);
                });
            }

            $btnConsultarFipe.on('click', consultarFipe);
            // â”€â”€ Validações de datas e km â”€â”€
            (function() {
                const agora = new Date();
                const today = [
                    agora.getFullYear(),
                    String(agora.getMonth() + 1).padStart(2, '0'),
                    String(agora.getDate()).padStart(2, '0'),
                ].join('-');

                const dataCompra = document.getElementById('data_compra');
                const dataEmissaoPlaca = document.getElementById('data_placa_licenca');
                const dataValidade = document.getElementById('data_validade');
                const dataManutencao = document.getElementById('data_proxima_manutencao');
                const dataInspecao = document.getElementById('data_proxima_inspecao');
                const kmsAtual = document.getElementById('kms');
                const limiteKms = document.getElementById('limite_kms');
                const proxRevisaoKms = document.getElementById('proxima_revisao_kms');
                const proxManutKms = document.getElementById('proxima_manutencao_kms');

                function formatarData(iso) {
                    if (!iso) return '';
                    const [y, m, d] = iso.split('-');
                    return d + '/' + m + '/' + y;
                }

                function formatarKm(valor) {
                    return Number(valor).toLocaleString('pt-BR');
                }

                function setMensagem(campo, mensagem) {
                    if (!campo) return;
                    campo.setCustomValidity(mensagem || '');
                    if (mensagem) {
                        campo.setAttribute('title', mensagem);
                        campo.setAttribute('data-toggle', 'tooltip');
                        campo.setAttribute('data-placement', 'top');
                    } else {
                        campo.removeAttribute('title');
                        campo.removeAttribute('data-toggle');
                        campo.removeAttribute('data-placement');
                    }
                }

                function validarDataEmissaoPlaca() {
                    if (!dataEmissaoPlaca) return;

                    let mensagem = '';
                    if (
                        dataEmissaoPlaca.value &&
                        dataValidade?.value &&
                        dataEmissaoPlaca.value > dataValidade.value
                    ) {
                        mensagem = 'A Emissão da Placa/Licença não pode ser posterior à Validade da Documentação/Licença (' +
                            formatarData(dataValidade.value) + ').';
                    }

                    setMensagem(dataEmissaoPlaca, mensagem);
                }

                function validarDataManutencao() {
                    if (!dataManutencao) return;

                    const mensagem = dataManutencao.value && dataManutencao.value < today
                        ? 'A Próxima Manutenção deve ser hoje ou uma data futura (hoje = ' + formatarData(today) + ').'
                        : '';

                    setMensagem(dataManutencao, mensagem);
                }

                function validarDataInspecao() {
                    if (!dataInspecao) return;

                    const mensagem = dataInspecao.value && dataInspecao.value < today
                        ? 'A Próxima Inspeção deve ser hoje ou uma data futura (hoje = ' + formatarData(today) + ').'
                        : '';

                    setMensagem(dataInspecao, mensagem);
                }

                function validarLimiteKms() {
                    if (!limiteKms) return;

                    let mensagem = '';
                    const km = Number.parseInt(kmsAtual?.value || '', 10);
                    const limite = Number.parseInt(limiteKms.value || '', 10);

                    if (
                        limiteKms.value !== '' &&
                        kmsAtual?.value !== '' &&
                        Number.isFinite(km) &&
                        Number.isFinite(limite) &&
                        limite < km
                    ) {
                        mensagem = 'O Limite de Km por Aluguel (' + formatarKm(limite) +
                            ' km) não pode ser menor que o Km Atual (' + formatarKm(km) + ' km).';
                    }

                    setMensagem(limiteKms, mensagem);
                }

                function validarKmRevisao() {
                    if (!proxRevisaoKms) return;

                    let mensagem = '';
                    const km = Number.parseInt(kmsAtual?.value || '', 10);
                    const pr = Number.parseInt(proxRevisaoKms.value || '', 10);

                    if (
                        proxRevisaoKms.value !== '' &&
                        kmsAtual?.value !== '' &&
                        Number.isFinite(km) &&
                        Number.isFinite(pr) &&
                        pr < km
                    ) {
                        mensagem = 'O Km da Próxima Revisão (' + formatarKm(pr) +
                            ' km) deve ser maior ou igual ao Km Atual (' + formatarKm(km) + ' km).';
                    }

                    setMensagem(proxRevisaoKms, mensagem);
                }

                function validarKmManut() {
                    if (!proxManutKms) return;

                    let mensagem = '';
                    const km = Number.parseInt(kmsAtual?.value || '', 10);
                    const pm = Number.parseInt(proxManutKms.value || '', 10);

                    if (
                        proxManutKms.value !== '' &&
                        kmsAtual?.value !== '' &&
                        Number.isFinite(km) &&
                        Number.isFinite(pm) &&
                        pm < km
                    ) {
                        mensagem = 'O Km da Próxima Manutenção (' + formatarKm(pm) +
                            ' km) deve ser maior ou igual ao Km Atual (' + formatarKm(km) + ' km).';
                    }

                    setMensagem(proxManutKms, mensagem);
                }

                function validarTudo() {
                    validarDataEmissaoPlaca();
                    validarDataManutencao();
                    validarDataInspecao();
                    validarLimiteKms();
                    validarKmRevisao();
                    validarKmManut();
                }

                dataEmissaoPlaca?.addEventListener('input', validarDataEmissaoPlaca);
                dataEmissaoPlaca?.addEventListener('change', validarDataEmissaoPlaca);

                dataValidade?.addEventListener('input', validarDataEmissaoPlaca);
                dataValidade?.addEventListener('change', validarDataEmissaoPlaca);

                dataManutencao?.addEventListener('input', validarDataManutencao);
                dataManutencao?.addEventListener('change', validarDataManutencao);

                dataInspecao?.addEventListener('input', validarDataInspecao);
                dataInspecao?.addEventListener('change', validarDataInspecao);

                kmsAtual?.addEventListener('input', function() {
                    validarLimiteKms();
                    validarKmRevisao();
                    validarKmManut();
                });
                kmsAtual?.addEventListener('change', function() {
                    validarLimiteKms();
                    validarKmRevisao();
                    validarKmManut();
                });

                limiteKms?.addEventListener('input', validarLimiteKms);
                limiteKms?.addEventListener('change', validarLimiteKms);

                proxRevisaoKms?.addEventListener('input', validarKmRevisao);
                proxRevisaoKms?.addEventListener('change', validarKmRevisao);

                proxManutKms?.addEventListener('input', validarKmManut);
                proxManutKms?.addEventListener('change', validarKmManut);

                validarTudo();
            })();

            // â”€â”€ Status Vendido â”€â”€
            const $status = $('[name="status_veiculo_id"]');
            const $divDataVendido = $('#div_data_vendido');
            const $inputDataVendido = $('#data_vendido');

            function isVendido(v) {
                return String(v || '') === '3';
            }

            function configDataVendido(show) {
                if (show) {
                    $divDataVendido.removeClass('d-none');
                    $inputDataVendido.attr('required', 'required');
                } else {
                    $divDataVendido.addClass('d-none');
                    $inputDataVendido.removeAttr('required');
                }
            }

            $status.on('change', function() {
                configDataVendido(isVendido($(this).val()));
            });
            configDataVendido(isVendido($status.val()) || ($inputDataVendido.val() || '').trim() !== '');

                // ── Validação e Exibição de Erros (Swal) ──
            <?php if($errors->any()): ?>
                let backendErrors = '<ul class="mb-0 pl-3 text-left" style="font-size:14px;">';
                <?php $__currentLoopData = array_unique($errors->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    backendErrors += '<li><?php echo e($error); ?></li>';
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                backendErrors += '</ul>';
                fireSwal({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Verifique os erros:',
                    html: backendErrors,
                    showConfirmButton: false,
                    timer: 7000
                });
            <?php endif; ?>

            $('#formulario').on('submit', function(e) {
                const invalids = $formulario.find(':invalid');

                if (invalids.length > 0) {
                    e.preventDefault();
                    e.stopPropagation();

                    const pendencias = coletarPendencias($formulario);
                    $formulario.addClass('was-validated');
                    mostrarPendencias(pendencias);
                    return;
                }

                desformatarCamposMoedaParaEnvio();
            });

        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/add-edit.blade.php ENDPATH**/ ?>