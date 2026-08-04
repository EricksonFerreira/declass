

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
        $tipoCobrancaSelecionado = old(
            'tipo_cobranca',
            isset($opcional->tipo_cobranca) && $opcional->tipo_cobranca ? $opcional->tipo_cobranca : '',
        );

        $qtdTiposOpcional = is_countable($tipoOpcional ?? null) ? count($tipoOpcional) : 0;
        $faltasPreRequisitosOpcional = [];
        if ($qtdTiposOpcional === 0) {
            $faltasPreRequisitosOpcional[] = 'Cadastre pelo menos um tipo de opcional para criar opcionais.';
        }

        $acoesPreRequisitosOpcional = [];
        if ($qtdTiposOpcional === 0 && \Illuminate\Support\Facades\Route::has('tipo_opcional.create')) {
            $acoesPreRequisitosOpcional[] = ['label' => 'Cadastrar tipo de opcional', 'url' => route('tipo_opcional.create')];
        }

        $podeCriarOpcional = empty($faltasPreRequisitosOpcional);
        $bloquearSalvarOpcional = strtoupper($metodo ?? 'POST') === 'POST' && !$podeCriarOpcional;
    ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                <?php $__currentLoopData = array_unique($errors->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <?php if (isset($component)) { $__componentOriginal1483a54ad1eed584cc353dbfbe0aaf49 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1483a54ad1eed584cc353dbfbe0aaf49 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pre-requisitos-alerta','data' => ['title' => 'Pre-requisitos pendentes para criar opcional','chips' => [
                ['label' => 'Tipos de Opcionais', 'value' => $qtdTiposOpcional],
            ],'missingItems' => $faltasPreRequisitosOpcional,'actionLinks' => $acoesPreRequisitosOpcional]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pre-requisitos-alerta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pre-requisitos pendentes para criar opcional','chips' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['label' => 'Tipos de Opcionais', 'value' => $qtdTiposOpcional],
            ]),'missing-items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($faltasPreRequisitosOpcional),'action-links' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($acoesPreRequisitosOpcional)]); ?>
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

        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1"><?php echo e($titulo); ?></h4>
                    <p class="mb-0 text-muted">Configure os dados do opcional e a forma de cobranca.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-opcional" method="POST" action="<?php echo e($rota); ?>">
                    <?php if(strtoupper($metodo ?? '') === 'PUT'): ?>
                        <?php echo method_field('PUT'); ?>
                    <?php endif; ?>
                    <?php echo csrf_field(); ?>

                    <div class="opcional-section-title">Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'nome','label' => 'Nome','value' => ''.e(old('nome', $opcional->nome ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-opcional">
                                <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'tipo_opcional_id','label' => 'Tipo de Opcional','options' => $tipoOpcional,'placeholder' => 'Selecione...','selected' => old(
                                        'tipo_opcional_id',
                                        isset($opcional->tipo_opcional_id) && $opcional->tipo_opcional_id ? $opcional->tipo_opcional_id : null,
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
                        <?php if(isset($opcional)): ?>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-opcional">
                                    <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'ativo','label' => 'Status','options' => $status,'placeholder' => 'Selecione...','selected' => old(
                                            'ativo',
                                            isset($opcional->ativo) && $opcional->ativo ? $opcional->ativo : null,
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
                        <?php else: ?>
                            <input type="hidden" name="ativo" value="S">
                        <?php endif; ?>
                    </div>

                    <div class="opcional-section-title">Precificação</div>
                    <div class="form-row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-opcional">
                                <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'tipo_cobranca','label' => 'Tipo de Cobranca','options' => ['por_dia' => 'Por Dia', 'valor_integral' => 'Integral'],'selected' => $tipoCobrancaSelecionado,'placeholder' => 'Selecione...'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'tipo_cobranca','class' => 'form-control form-control-sm','required' => true]); ?>
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
                        <div class="col-lg-4 col-md-6" id="preco_por_dia_div">
                            <div class="form-group form-group-opcional">
                                <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'preco_por_dia','label' => 'Preço por Dia','value' => ''.e(old('preco_por_dia', $opcional->preco_por_dia ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'preco_por_dia']); ?>
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
                        <div class="col-lg-4 col-md-6" id="preco_integral_div">
                            <div class="form-group form-group-opcional">
                                <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'preco_integral','label' => 'Preço Integral','value' => ''.e(old('preco_integral', $opcional->preco_integral ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'preco_integral']); ?>
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

                    <div class="opcional-section-title">Descrição</div>
                    <div class="form-row">
                        <div class="col-12">
                            <div class="form-group form-group-opcional">
                                <label for="descricao" class="form-label-opcional">Descrição</label>
                                <textarea class="form-control form-control-sm" name="descricao" id="descricao" rows="4"><?php echo e(old('descricao', $opcional->descricao ?? '')); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-opcional col-12 mt-2" <?php echo e($bloquearSalvarOpcional ? 'disabled' : ''); ?>>
                        Salvar
                    </button>
                    <?php if($bloquearSalvarOpcional): ?>
                        <small class="text-danger d-block mt-2 text-center font-weight-semibold">
                            Cadastre o tipo de opcional para liberar a criacao do opcional.
                        </small>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .card-opcional {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-opcional {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-opcional h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .opcional-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-opcional {
        margin-bottom: 1rem;
    }

    .card-opcional .form-control,
    .card-opcional .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-opcional .form-control:focus,
    .card-opcional .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .form-label-opcional {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .btn-save-opcional {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-opcional {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-opcional h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-opcional p {
            font-size: .85rem;
        }

        .opcional-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-opcional {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-opcional {
            margin-bottom: .8rem;
        }

        .card-opcional .form-control,
        .card-opcional .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(function() {
            $('#preco_por_dia').mask('0000000000,00', {
                reverse: true
            });
            $('#preco_integral').mask('0000000000,00', {
                reverse: true
            });

            function alternarCamposPreco(limparInativo = true) {
                const tipoCobranca = $('#tipo_cobranca').val();

                $('#preco_integral').removeAttr('required');
                $('#preco_por_dia').removeAttr('required');

                if (tipoCobranca === 'por_dia') {
                    $('#preco_por_dia_div').show();
                    $('#preco_integral_div').hide();
                    $('#preco_por_dia').attr('required', 'required');
                    if (limparInativo) {
                        $('#preco_integral').val('');
                    }
                    return;
                }

                if (tipoCobranca === 'valor_integral') {
                    $('#preco_por_dia_div').hide();
                    $('#preco_integral_div').show();
                    $('#preco_integral').attr('required', 'required');
                    if (limparInativo) {
                        $('#preco_por_dia').val('');
                    }
                    return;
                }

                $('#preco_por_dia_div').hide();
                $('#preco_integral_div').hide();
                if (limparInativo) {
                    $('#preco_por_dia').val('');
                    $('#preco_integral').val('');
                }
            }

            alternarCamposPreco(false);
            $('#tipo_cobranca').on('change', function() {
                alternarCamposPreco(true);
            });
        });
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'opcional-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-opcional', popover: { title: 'Cadastro de Opcional', description: 'Aqui você cria ou edita um opcional. Opcionais são serviços ou itens adicionais que o cliente pode contratar junto com a reserva, como seguro, GPS ou cadeirinha.' } },
        { element: '#nome', popover: { title: 'Nome do opcional', description: 'Informe o nome que será exibido ao cliente durante a reserva, como "Seguro Completo", "GPS Portátil" ou "Cadeirinha Infantil".', side: 'bottom' } },
        { element: '#tipo_opcional_id', popover: { title: 'Tipo de opcional', description: 'Selecione a categoria que classifica este opcional. Os tipos são configurados no menu Tipos de Opcional.', side: 'bottom' } },
        { element: '#tipo_cobranca', popover: { title: 'Forma de cobrança', description: 'Por dia: o preço é multiplicado pela quantidade de dias da reserva. Valor integral: cobrança única independente da duração.', side: 'bottom' } },
        { element: '#preco_por_dia, #preco_integral', popover: { title: 'Preço', description: 'Informe o valor do opcional. O campo exibido depende da forma de cobrança selecionada — por dia ou valor único.', side: 'bottom' } },
        { element: '#descricao', popover: { title: 'Descrição', description: 'Texto explicativo exibido ao cliente durante a reserva, descrevendo o que o opcional inclui e seus benefícios.', side: 'top' } },
        { element: '.btn-save-opcional', popover: { title: 'Salvar', description: 'Clique para gravar o opcional. Após salvar ele ficará disponível para vincular aos grupos de frota.', side: 'top' } },
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

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/opcional/add-edit.blade.php ENDPATH**/ ?>