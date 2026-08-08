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

<?php $__env->startSection('content'); ?>
    <div class="row">
        <!-- Listagem de Projetos -->
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary"><?php echo e($projetos->total()); ?></span></h3>
                    <div class="card-tools">
                        <form action="<?php echo e(route('clockify.projetos.index')); ?>" method="GET" class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Procurar..." value="<?php echo e(request('search')); ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">Cor</th>
                                <th>Nome</th>
                                <th>Cliente</th>
                                <th>Faturamento</th>
                                <th>Planejamento de Entrega</th>
                                <th class="text-center" style="width: 80px;">Status</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
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
                                        <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="btn btn-xs btn-primary font-weight-bold px-3 py-1 shadow-sm">
                                            <i class="fas fa-eye mr-1"></i> Detalhes
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Nenhum projeto cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <?php echo e($projetos->appends(request()->query())->links()); ?>

                </div>
            </div>
        </div>

        <!-- Formulário de Cadastro / Edição -->
        <div class="col-md-4">
            <div class="card card-outline card-success shadow-sm" id="form-card">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm" id="form-title">Cadastrar Novo Projeto</h3>
                </div>
                <form id="projeto-form" action="<?php echo e(route('clockify.projetos.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nome">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="nome" class="form-control form-control-sm" required placeholder="Ex: Novo Site XPAND">
                        </div>
                        <div class="form-group">
                            <label for="clockify_cliente_id">Cliente</label>
                            <select name="clockify_cliente_id" id="clockify_cliente_id" class="form-control form-control-sm select2">
                                <option value="">Nenhum Cliente</option>
                                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cliente->id); ?>"><?php echo e($cliente->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea name="descricao" id="descricao" rows="2" class="form-control form-control-sm" placeholder="Resumo do projeto..."></textarea>
                        </div>

                        <!-- Budget Fields -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valor_faturado">Valor Faturado (R$)</label>
                                    <input type="text" name="valor_faturado" id="valor_faturado" class="form-control form-control-sm money-mask" placeholder="Ex: 15.000,00" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valor_hora">Valor por Hora (R$)</label>
                                    <input type="text" name="valor_hora" id="valor_hora" class="form-control form-control-sm money-mask" placeholder="Ex: 150,00" />
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <label for="cor">Cor do Projeto</label>
                            <div class="input-group input-group-sm">
                                <input type="color" name="cor" id="cor" class="form-control form-control-sm" style="padding: 2px; height: 31px;" value="#0284c7">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="publico">Visibilidade</label>
                            <select name="publico" id="publico" class="form-control form-control-sm">
                                <option value="1">Público</option>
                                <option value="0">Privado</option>
                            </select>
                        </div>
                        <div class="form-group" id="status-group" style="display: none;">
                            <label for="ativo">Status</label>
                            <select name="ativo" id="ativo" class="form-control form-control-sm">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success float-right" id="save-btn">Salvar</button>
                        <button type="button" class="btn btn-sm btn-default" id="cancel-btn" style="display: none;" onclick="resetForm()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 se disponível
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }

            // Aplicar máscara monetária
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
        });



        function editProjeto(projeto) {
            $('#form-title').text('Editar Projeto: ' + projeto.nome);
            $('#projeto-form').attr('action', '/admin/clockify/projetos/' + projeto.id);
            $('#form-method').val('PUT');
            $('#nome').val(projeto.nome);
            
            // Set values and trigger change for Select2
            $('#clockify_cliente_id').val(projeto.clockify_cliente_id).trigger('change');
            
            $('#descricao').val(projeto.descricao);
            $('#cor').val(projeto.cor ?? '#0284c7');
            $('#publico').val(projeto.publico ? "1" : "0");
            $('#ativo').val(projeto.ativo ? "1" : "0");
            
            // Budget & Planning fields
            $('#valor_faturado').val(projeto.valor_faturado);
            $('#valor_hora').val(projeto.valor_hora);
            
            $('#status-group').show();
            $('#cancel-btn').show();
            $('#form-card').removeClass('card-success').addClass('card-warning');
            $('#save-btn').removeClass('btn-success').addClass('btn-warning').text('Atualizar');
        }

        function resetForm() {
            $('#form-title').text('Cadastrar Novo Projeto');
            $('#projeto-form').attr('action', '<?php echo e(route('clockify.projetos.store')); ?>');
            $('#form-method').val('POST');
            $('#projeto-form')[0].reset();
            
            // Reset Select2
            $('#clockify_cliente_id').val('').trigger('change');
            $('#valor_faturado').val('');
            $('#valor_hora').val('');

            $('#status-group').hide();
            $('#cancel-btn').hide();
            $('#form-card').removeClass('card-warning').addClass('card-success');
            $('#save-btn').removeClass('btn-warning').addClass('btn-success').text('Salvar');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/projetos.blade.php ENDPATH**/ ?>