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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <?php echo $__env->make('partials.ux-index-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(!empty($filters)): ?>
        <div class="veiculo-filter-sticky">
            <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['filters' => $filters,'additionalFilters' => $additionalFilters,'itensFiltered' => $itensFiltered] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nomeFiltro' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($nomeFiltro)]); ?>
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

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary"><?php echo e($projetos->total()); ?></span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="<?php echo e(route($nomeFiltro . '.setPerPage')); ?>" method="POST" class="mb-0">
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
                        <?php echo $__env->make('partials.ux-view-toggle', ['viewKey' => 'projetos_index_view'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clockify.projetos.store')): ?>
                            <button type="button" class="btn btn-sm btn-primary" id="btn-create-projeto">
                                <i class="fas fa-plus mr-1"></i> Novo Projeto
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        <?php $__empty_1 = true; $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projeto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <article class="ux-card">
                                <div class="ux-card__accent" style="background: <?php echo e($projeto->cor ?? '#1e293b'); ?>"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar" style="background: <?php echo e($projeto->cor ?? '#334155'); ?>"><i class="fas fa-project-diagram"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title"><?php echo e($projeto->nome); ?></div>
                                        <div class="ux-card__sub"><?php echo e($projeto->cliente->nome ?? 'Sem cliente'); ?></div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--<?php echo e($projeto->ativo ? 'green' : 'red'); ?>">
                                            <?php echo e($projeto->ativo ? 'Ativo' : 'Inativo'); ?>

                                        </span>
                                        <?php if($projeto->responsavel): ?>
                                            <span class="ux-card__badge ux-card__badge--blue" title="Responsável pelo projeto"><i class="fas fa-user-tie mr-1"></i><?php echo e($projeto->responsavel->nome); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-money-bill-wave mr-1"></i>Faturamento</span>
                                        <strong class="ux-card__meta-value">
                                            <?php echo e($projeto->valor_faturado > 0 ? 'R$ ' . number_format($projeto->valor_faturado, 2, ',', '.') : '—'); ?>

                                        </strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="far fa-calendar-check mr-1"></i>Entrega prevista</span>
                                        <strong class="ux-card__meta-value">
                                            <?php echo e($projeto->data_entrega_previsao ? $projeto->data_entrega_previsao->format('d/m/Y') : 'Sem planejamento'); ?>

                                        </strong>
                                    </div>
                                    <div class="ux-card__meta-row ux-card__meta-row--full">
                                        <span class="ux-card__meta-label"><i class="fas fa-align-left mr-1"></i>Descrição</span>
                                        <strong class="ux-card__meta-value"><?php echo e($projeto->descricao ?: '—'); ?></strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="btn btn-xs btn-primary">
                                        <i class="fas fa-eye mr-1"></i>Detalhes
                                    </a>
                                    <?php if($projeto->podeSerGerenciadoPor(auth()->user())): ?>
                                        <button type="button" class="btn btn-xs btn-warning"
                                            onclick="editProjeto(<?php echo e(json_encode($projeto)); ?>)">
                                            <i class="fa fa-edit mr-1"></i>Editar
                                        </button>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clockify.projetos.destroy')): ?>
                                        <form action="<?php echo e(route('clockify.projetos.destroy', $projeto->id)); ?>" method="POST" class="d-inline"
                                            onsubmit="return confirm('Deseja realmente excluir este projeto? Todos os dados vinculados serão removidos.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Nenhum projeto cadastrado.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">Cor</th>
                                <th>Nome</th>
                                <th>Cliente</th>
                                <th>Faturamento</th>
                                <th>Planejamento de Entrega</th>
                                <th class="text-center" style="width: 80px;">Status</th>
                                <th style="width: 240px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projeto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <span class="badge text-white" style="background-color: <?php echo e($projeto->cor ?? '#6c757d'); ?>; width: 24px; height: 24px; display: inline-block; border-radius: 50%; border: 1px solid #dee2e6;">&nbsp;</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold">
                                            <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="text-dark font-weight-bold" style="text-decoration: none;"><?php echo e($projeto->nome); ?></a>
                                        </div>
                                        <?php if($projeto->descricao): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 250px;" title="<?php echo e($projeto->descricao); ?>">
                                                <?php echo e($projeto->descricao); ?>

                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo e($projeto->cliente->nome ?? '-'); ?></td>
                                    <td class="align-middle">
                                        <?php if($projeto->valor_faturado > 0): ?>
                                            <div><strong>R$ <?php echo e(number_format($projeto->valor_faturado, 2, ',', '.')); ?></strong></div>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if($projeto->data_entrega_previsao): ?>
                                            <div class="font-weight-bold text-sm text-primary">
                                                <i class="far fa-calendar-check mr-1"></i> <?php echo e($projeto->data_entrega_previsao->format('d/m/Y')); ?>

                                            </div>
                                            <div class="text-xs">
                                                <span>Restam: <strong><?php echo e($projeto->dias_uteis_planejamento); ?> dias úteis</strong></span><br>
                                                <span>Horas Planejadas: <strong><?php echo e(number_format($projeto->horas_planejadas, 1, ',', '.')); ?> h</strong> (<?php echo e(number_format($projeto->horas_diarias_planejamento ?? 8, 1)); ?>h/dia)</span>
                                                <?php if($projeto->valor_hora > 0): ?>
                                                    <br><span class="text-secondary font-weight-bold">Valor da Hora: R$ <?php echo e(number_format($projeto->valor_hora, 2, ',', '.')); ?></span>
                                                    <br><span class="text-dark font-weight-bold">Custo Previsto: R$ <?php echo e(number_format($projeto->custo_planejado, 2, ',', '.')); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">Sem planejamento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-<?php echo e($projeto->ativo ? 'success' : 'danger'); ?>">
                                            <?php echo e($projeto->ativo ? 'Ativo' : 'Inativo'); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle text-center" style="white-space: nowrap;">
                                        <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="btn btn-xs btn-primary mr-1">
                                            <i class="fas fa-eye"></i> Detalhes
                                        </a>
                                        <?php if($projeto->podeSerGerenciadoPor(auth()->user())): ?>
                                            <button type="button" class="btn btn-xs btn-warning mr-1"
                                                onclick="editProjeto(<?php echo e(json_encode($projeto)); ?>)">
                                                <i class="fa fa-edit"></i> Editar
                                            </button>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clockify.projetos.destroy')): ?>
                                            <form action="<?php echo e(route('clockify.projetos.destroy', $projeto->id)); ?>" method="POST" class="d-inline"
                                                onsubmit="return confirm('Deseja realmente excluir este projeto? Todos os dados vinculados serão removidos.')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                        Nenhum projeto cadastrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php if($projetos->hasPages()): ?>
                    <div class="card-footer py-2 d-flex justify-content-end">
                        <?php echo e($projetos->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'projetoDrawer','icon' => 'fas fa-project-diagram','title' => 'Novo Projeto','formId' => 'projeto-form','action' => ''.e(route('clockify.projetos.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'projetoDrawer','icon' => 'fas fa-project-diagram','title' => 'Novo Projeto','formId' => 'projeto-form','action' => ''.e(route('clockify.projetos.store')).'']); ?>
        <input type="hidden" name="id" id="projeto_id" value="<?php echo e(old('id')); ?>">
        <div class="form-group">
            <label for="nome">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nome')); ?>" required placeholder="Ex: Novo Site XPAND">
            <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="invalid-feedback"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="form-group">
            <label for="clockify_cliente_id">Cliente</label>
            <select name="clockify_cliente_id" id="clockify_cliente_id" class="form-control form-control-sm select2">
                <option value="">Nenhum Cliente</option>
                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cliente->id); ?>" <?php if((string) old('clockify_cliente_id') === (string) $cliente->id): echo 'selected'; endif; ?>><?php echo e($cliente->nome); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="responsavel_id">Responsável</label>
            <select name="responsavel_id" id="responsavel_id" class="form-control form-control-sm">
                <option value="">Sem responsável</option>
                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($usuario->id); ?>" <?php echo e((string) old('responsavel_id') === (string) $usuario->id ? 'selected' : ''); ?>><?php echo e($usuario->nome); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <small class="text-muted">O responsável pode editar este projeto e acompanhar seu consumo.</small>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="2" class="form-control form-control-sm" placeholder="Resumo do projeto..."><?php echo e(old('descricao')); ?></textarea>
        </div>
        <div class="form-row">
            <div class="col-6 form-group">
                <label for="valor_faturado">Valor Faturado (R$)</label>
                <input type="text" name="valor_faturado" id="valor_faturado" class="form-control form-control-sm money-mask" value="<?php echo e(old('valor_faturado')); ?>" placeholder="Ex: 15.000,00">
            </div>
            <div class="col-6 form-group">
                <label for="valor_hora">Valor por Hora (R$)</label>
                <input type="text" name="valor_hora" id="valor_hora" class="form-control form-control-sm money-mask" value="<?php echo e(old('valor_hora')); ?>" placeholder="Ex: 150,00">
            </div>
        </div>
        <div class="form-group">
            <label for="cor">Cor do Projeto</label>
            <input type="color" name="cor" id="cor" class="form-control form-control-sm" style="padding: 2px; height: 31px;" value="<?php echo e(old('cor', '#0284c7')); ?>">
        </div>
        <div class="form-group">
            <label for="publico">Visibilidade</label>
            <select name="publico" id="publico" class="form-control form-control-sm">
                <option value="1" <?php if(old('publico', '1') === '1'): echo 'selected'; endif; ?>>Público</option>
                <option value="0" <?php if(old('publico') === '0'): echo 'selected'; endif; ?>>Privado</option>
            </select>
        </div>
        <div class="form-group" id="status-group" style="display: none;">
            <label for="ativo">Status</label>
            <select name="ativo" id="ativo" class="form-control form-control-sm">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }

            if ($.fn.inputmask) {
                $('.money-mask').inputmask('currency', {
                    radixPoint: ',',
                    groupSeparator: '.',
                    allowMinus: false,
                    prefix: 'R$ ',
                    digits: 2,
                    autoGroup: true,
                    rightAlign: false,
                    unmaskAsNumber: true,
                    removeMaskOnSubmit: true
                });
            }

            $('#btn-create-projeto').on('click', function() {
                $('#projetoDrawerTitle').text('Novo Projeto');
                $('#projeto-form').attr('action', '<?php echo e(route('clockify.projetos.store')); ?>');
                $('#projeto-formMethod').val('POST');
                $('#projeto-form')[0].reset();
                $('#projeto_id').val('');
                $('#clockify_cliente_id').val('').trigger('change');
                $('#status-group').hide();
                $('#projetoDrawerBackdrop').addClass('show');
                $('#projetoDrawer').addClass('open');
            });

            <?php if($errors->any()): ?>
                <?php if(old('_method') === 'PUT'): ?>
                    $('#projetoDrawerTitle').text('Editar Projeto');
                    $('#projeto-form').attr('action', '<?php echo e(url('admin/projetos')); ?>/' + '<?php echo e(old('id')); ?>');
                    $('#projeto-formMethod').val('PUT');
                    $('#status-group').show();
                <?php else: ?>
                    $('#projetoDrawerTitle').text('Novo Projeto');
                    $('#projeto-form').attr('action', '<?php echo e(route('clockify.projetos.store')); ?>');
                    $('#projeto-formMethod').val('POST');
                <?php endif; ?>
                $('#projetoDrawerBackdrop').addClass('show');
                $('#projetoDrawer').addClass('open');
            <?php endif; ?>
        });

        function editProjeto(projeto) {
            $('#projetoDrawerTitle').text('Editar Projeto: ' + projeto.nome);
            $('#projeto-form').attr('action', '<?php echo e(url('admin/projetos')); ?>/' + projeto.id);
            $('#projeto-formMethod').val('PUT');
            $('#projeto_id').val(projeto.id);
            $('#nome').val(projeto.nome);
            $('#clockify_cliente_id').val(projeto.clockify_cliente_id).trigger('change');
            $('#responsavel_id').val(projeto.responsavel_id || '');
            $('#descricao').val(projeto.descricao);
            $('#valor_faturado').val(projeto.valor_faturado);
            $('#valor_hora').val(projeto.valor_hora);
            $('#cor').val(projeto.cor ?? '#0284c7');
            $('#publico').val(projeto.publico ? '1' : '0');
            $('#ativo').val(projeto.ativo ? '1' : '0');

            $('#status-group').show();
            $('#projetoDrawerBackdrop').addClass('show');
            $('#projetoDrawer').addClass('open');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\clockify\projetos.blade.php ENDPATH**/ ?>