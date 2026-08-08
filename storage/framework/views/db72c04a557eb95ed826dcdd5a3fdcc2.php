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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'createUrl' => route('usuario.create')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['createCan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('usuario.create')]); ?>
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

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS
                    <span class="badge badge-success"><?php echo e($usuarios->total()); ?></span>
                </h3>

                <div class="align-bottom d-flex flex-row justify-between" style="gap:6px;flex-wrap:wrap;">
                    <form action="<?php echo e(route('usuario.setPerPage')); ?>" method="POST">
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

                    <div class="veiculo-view-toggle ux-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        <?php echo e($usuarios->links()); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 ux-index-body" data-view="cards">
            <div class="ux-cards p-3">
                <?php $__empty_1 = true; $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $canMetodoPagamento = auth()->user()->hasPermissao('usuario.metodoPagamento');
                        $canEditar = auth()->user()->hasPermissao('usuario.edit');
                        $canAlterarSenha = auth()->user()->hasPermissao('usuario.alterarSenha');
                        $canExcluir = auth()->user()->hasPermissao('usuario.destroy');
                        $temAlgumaAcao = $canMetodoPagamento || $canEditar || $canAlterarSenha || $canExcluir;
                        $nomeUsuario = trim((string) ($usuario->nome ?? 'Usuário'));
                        $partesNome = preg_split('/\s+/', $nomeUsuario) ?: [];
                        $iniciais = collect($partesNome)->filter()->take(2)
                            ->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('');
                        $iniciais = $iniciais ?: 'US';
                        $pendenciasUsuario = [];
                        if (empty($usuario->email)) $pendenciasUsuario[] = 'E-mail';
                        if (empty($usuario->numero_identificacao)) $pendenciasUsuario[] = 'Documento';
                        if (empty($usuario->celular)) $pendenciasUsuario[] = 'Celular';
                        if (empty($usuario->data_nascimento)) $pendenciasUsuario[] = 'Nascimento';
                        if (empty($usuario->pais_sigla) || empty($usuario->cidade) || empty($usuario->endereco)) $pendenciasUsuario[] = 'Contato/Endereço';
                        $statusUsuario = !empty($usuario->data_validade_carta_conducao) && \Carbon\Carbon::parse($usuario->data_validade_carta_conducao)->isPast()
                            ? 'Documento vencido'
                            : (count($pendenciasUsuario) > 0 ? 'Cadastro incompleto' : 'Apto para locação');
                    ?>

                    <article class="ux-card">
                        <div class="ux-card__accent"></div>
                        <div class="ux-card__head">
                            <div class="ux-card__avatar"><?php echo e($iniciais); ?></div>
                            <div class="ux-card__head-info">
                                <div class="ux-card__title"><?php echo e($usuario->nome); ?></div>
                                <div class="ux-card__sub"><?php echo e($usuario->email ?: 'Sem e-mail'); ?></div>
                            </div>
                        </div>

                        <div class="ux-card__meta">
                            <div class="ux-card__meta-row">
                                <span class="ux-card__meta-label"><i class="fas fa-user-tag mr-1"></i>Cargo</span>
                                <strong class="ux-card__meta-value"><?php echo e($usuario->cargo ?: 'Sem cargo'); ?></strong>
                            </div>
                            <div class="ux-card__meta-row">
                                <span class="ux-card__meta-label"><i class="fas fa-phone-alt mr-1"></i>Celular</span>
                                <strong class="ux-card__meta-value"><?php echo e($usuario->celular ?: 'Sem celular'); ?></strong>
                            </div>
                        </div>

                        <div class="ux-card__foot">
                            <?php if($canEditar): ?>
                                <a class="btn btn-xs btn-warning btn-editar-usuario" href="<?php echo e(route('usuario.edit', $usuario->id)); ?>"
                                   data-id="<?php echo e($usuario->id); ?>"
                                   data-nome="<?php echo e($usuario->nome); ?>"
                                   data-email="<?php echo e($usuario->email); ?>"
                                   data-celular="<?php echo e($usuario->celular); ?>"
                                   data-cargo="<?php echo e($usuario->cargo); ?>">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                            <?php endif; ?>

                            <?php if($canAlterarSenha): ?>
                                <form action="<?php echo e(route('usuario.salvarNovaSenha', $usuario->id)); ?>"
                                    method="POST"
                                    class="d-inline js-reset-senha-form"
                                    id="resetSenhaCardForm-<?php echo e($usuario->id); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="resetar_para_acesso" value="1">
                                    <input type="hidden" name="password" value="Senh@123">
                                    <input type="hidden" name="confirmacao_senha" value="Senh@123">
                                    <button type="button"
                                        class="btn btn-xs btn-info js-reset-senha"
                                        data-user-name="<?php echo e($usuario->nome); ?>"
                                        data-form-id="resetSenhaCardForm-<?php echo e($usuario->id); ?>">
                                        <i class="fas fa-key mr-1"></i>Resetar senha
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if($canExcluir): ?>
                                <form action="<?php echo e(route('usuario.destroy', $usuario->id)); ?>"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash mr-1"></i>Excluir
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if(!$temAlgumaAcao): ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-muted py-4 w-100">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        Nenhum usuário encontrado.
                    </div>
                <?php endif; ?>
            </div>

            <div class="ux-table table-responsive">
                <table class="table table-hover table-striped table-sm mb-0 tabela-usuarios">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3" style="min-width: 220px;">Usuário</th>
                            <th style="min-width: 220px;">Contato</th>
                            <th style="width: 140px;">Regra</th>
                            <th>Cargo</th>
                            <th class="text-center" style="min-width: 300px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $canMetodoPagamento = auth()->user()->hasPermissao('usuario.metodoPagamento');
                                $canEditar = auth()->user()->hasPermissao('usuario.edit');
                                $canAlterarSenha = auth()->user()->hasPermissao('usuario.alterarSenha');
                                $canExcluir = auth()->user()->hasPermissao('usuario.destroy');
                                $temAlgumaAcao = $canMetodoPagamento || $canEditar || $canAlterarSenha || $canExcluir;
                            ?>

                            <tr>
                                <td class="pl-3 align-middle">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark"><?php echo e($usuario->nome); ?></span>
                                        <small class="text-muted">ID: #<?php echo e($usuario->id); ?></small>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <div class="d-flex flex-column">
                                        <span><?php echo e($usuario->email ?: '—'); ?></span>
                                        <small class="text-muted">
                                            <i class="fas fa-phone-alt mr-1"></i><?php echo e($usuario->celular ?: 'Sem celular'); ?>

                                        </small>
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <?php if($usuario->tipoUsuario): ?>
                                        <span class="badge badge-info px-2 py-1"><?php echo e($usuario->tipoUsuario->name); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <td class="align-middle">
                                    <?php echo e($usuario->cargo ?: 'Sem cargo'); ?>

                                </td>


                                <td class="align-middle text-center">
                                    <?php if($temAlgumaAcao): ?>
                                        <div class="acoes-usuario">
                                            <?php if($canEditar): ?>
                                                <a class="btn btn-xs btn-warning btn-editar-usuario"
                                                    href="<?php echo e(route('usuario.edit', $usuario->id)); ?>"
                                                    data-id="<?php echo e($usuario->id); ?>"
                                                    data-nome="<?php echo e($usuario->nome); ?>"
                                                    data-email="<?php echo e($usuario->email); ?>"
                                                    data-celular="<?php echo e($usuario->celular); ?>"
                                                    data-cargo="<?php echo e($usuario->cargo); ?>">
                                                    <i class="fas fa-edit mr-1"></i> Editar
                                                </a>
                                            <?php endif; ?>

                                            <?php if($canAlterarSenha): ?>
                                                <form action="<?php echo e(route('usuario.salvarNovaSenha', $usuario->id)); ?>"
                                                    method="POST"
                                                    class="d-inline js-reset-senha-form"
                                                    id="resetSenhaForm-<?php echo e($usuario->id); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <input type="hidden" name="resetar_para_acesso" value="1">
                                                    <input type="hidden" name="password" value="Senh@123">
                                                    <input type="hidden" name="confirmacao_senha" value="Senh@123">
                                                    <button type="button"
                                                        class="btn btn-xs btn-info js-reset-senha"
                                                        data-user-name="<?php echo e($usuario->nome); ?>"
                                                        data-form-id="resetSenhaForm-<?php echo e($usuario->id); ?>">
                                                        <i class="fas fa-key mr-1"></i> Resetar senha
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if($canExcluir): ?>
                                                <form action="<?php echo e(route('usuario.destroy', $usuario->id)); ?>"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-xs btn-danger">
                                                        <i class="fas fa-trash mr-1"></i> Excluir
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($usuarios->hasPages()): ?>
            <div class="card-footer py-2 d-flex justify-content-end">
                <?php echo e($usuarios->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($component)) { $__componentOriginalfcb525a84b79fe7e89c655d15af702a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfcb525a84b79fe7e89c655d15af702a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.side-drawer','data' => ['id' => 'sideDrawer','title' => 'Cadastrar Usuário','formId' => 'drawerForm','action' => ''.e(route('usuario.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('side-drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'sideDrawer','title' => 'Cadastrar Usuário','formId' => 'drawerForm','action' => ''.e(route('usuario.store')).'']); ?>
        <div class="form-group">
            <label for="drawer_nome">Nome <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="drawer_nome" name="nome" value="<?php echo e(old('nome')); ?>" required>
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
            <label for="drawer_email">E-mail <span class="text-danger">*</span></label>
            <input type="email" class="form-control form-control-sm <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="drawer_email" name="email" value="<?php echo e(old('email')); ?>" required>
            <?php $__errorArgs = ['email'];
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
            <label for="drawer_celular">Celular <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm <?php $__errorArgs = ['celular'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="drawer_celular" name="celular" value="<?php echo e(old('celular')); ?>" placeholder="(99) 99999-9999" required>
            <?php $__errorArgs = ['celular'];
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
            <label for="drawer_cargo">Cargo <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm <?php $__errorArgs = ['cargo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="drawer_cargo" name="cargo" value="<?php echo e(old('cargo')); ?>" required>
            <?php $__errorArgs = ['cargo'];
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
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfcb525a84b79fe7e89c655d15af702a1)): ?>
<?php $attributes = $__attributesOriginalfcb525a84b79fe7e89c655d15af702a1; ?>
<?php unset($__attributesOriginalfcb525a84b79fe7e89c655d15af702a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfcb525a84b79fe7e89c655d15af702a1)): ?>
<?php $component = $__componentOriginalfcb525a84b79fe7e89c655d15af702a1; ?>
<?php unset($__componentOriginalfcb525a84b79fe7e89c655d15af702a1); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .veiculo-filter-sticky {
            position: sticky;
            top: 10px;
            z-index: 1030;
        }

        .ux-view-toggle {
            display: flex;
            gap: 5px;
        }

        .veiculo-view-toggle .btn.active,
        .ux-view-toggle .btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .ux-index-body[data-view="table"] .ux-cards { display: none; }
        .ux-index-body[data-view="cards"] .ux-table { display: none; }

        .ux-index-body[data-view="table"] .ux-table {
            max-height: calc(100vh - 360px);
            overflow: auto;
            border-radius: 6px;
        }

        .ux-index-body[data-view="table"] .ux-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
        }

        .ux-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 16px;
            padding: 10px 8px 16px;
        }

        .ux-card {
            position: relative;
            background: #fff;
            border: 1px solid #e2e9f4;
            border-radius: 18px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 10px rgba(11,18,32,.06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .ux-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(11,18,32,.13);
        }

        .ux-card__accent {
            height: 3px;
            background: linear-gradient(90deg, #1e293b, #334155);
        }

        .ux-card__head {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .ux-card__avatar {
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

        .ux-card__head-info { flex: 1; min-width: 0; }
        .ux-card__id {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .ux-card__title {
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ux-card__sub {
            font-size: .74rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        .ux-card__badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            margin-left: 6px;
        }

        .ux-card__badge {
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

        .ux-card__badge--blue {
            background: rgba(59,130,246,.12);
            color: #1d4ed8;
            border-color: rgba(59,130,246,.2);
        }
        .ux-card__badge--green {
            background: rgba(34,197,94,.12);
            color: #15803d;
            border-color: rgba(34,197,94,.2);
        }
        .ux-card__badge--amber {
            background: rgba(245,158,11,.15);
            color: #b45309;
            border-color: rgba(245,158,11,.25);
        }
        .ux-card__badge--red {
            background: rgba(239,68,68,.12);
            color: #b91c1c;
            border-color: rgba(239,68,68,.2);
        }
        .ux-card__badge--gray {
            background: rgba(148,163,184,.15);
            color: #334155;
            border-color: rgba(148,163,184,.2);
        }

        .ux-card__meta {
            padding: 12px 16px 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 12px;
            flex: 1;
        }

        .ux-card__meta-row {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .ux-card__meta-row--full { grid-column: 1 / -1; }

        .ux-card__meta-label {
            font-size: .70rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .ux-card__meta-value {
            font-size: .86rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ux-card__foot {
            margin-top: auto;
            padding: 12px 16px 14px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .tabela-usuarios thead th {
            vertical-align: middle;
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .tabela-usuarios tbody td {
            vertical-align: middle;
        }

        .tabela-usuarios tbody tr:hover {
            background-color: #f8fbff;
        }

        .acoes-usuario {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px;
        }

        .acoes-usuario .btn {
            min-width: 95px;
        }

        .tabela-usuarios .badge {
            font-size: 11px;
        }

        @media (max-width: 991.98px) {
            .ux-cards {
                grid-template-columns: 1fr;
                padding: 6px 4px 12px;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="<?php echo e(asset('js/sweetAlert.min.js')); ?>"></script>
    <script>
        $(function () {
            const senhaPadrao = 'Senh@123';

            // Side Drawer Controller
            const $drawer = $('#sideDrawer');
            const $backdrop = $('#sideDrawerBackdrop');
            const $form = $('#drawerForm');
            const $method = $('#drawerFormMethod');
            const $title = $('#sideDrawerTitle');

            // Load masks if available
            if (typeof $.fn.mask !== 'undefined') {
                $('#drawer_celular').mask('(00) 00000-0000');
            }

            function abrirDrawer(titulo, action, method, data = null) {
                $title.text(titulo);
                $form.attr('action', action);
                $method.val(method);
                
                // Clear validation errors
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();
                
                if (data) {
                    $('#drawer_nome').val(data.nome);
                    $('#drawer_email').val(data.email);
                    $('#drawer_celular').val(data.celular);
                    $('#drawer_cargo').val(data.cargo);
                } else {
                    $form[0].reset();
                }
                
                $backdrop.addClass('show');
                $drawer.addClass('open');
            }

            function fecharDrawer() {
                $drawer.removeClass('open');
                $backdrop.removeClass('show');
            }

            // Intercept "Cadastrar" button clicks
            $(document).on('click', 'a[href$="/usuario/create"], .btn-cadastrar-novo, a[href*="usuario/create"]', function (e) {
                e.preventDefault();
                abrirDrawer('Cadastrar Usuário', "<?php echo e(route('usuario.store')); ?>", 'POST');
            });

            // Intercept "Editar" button clicks
            $(document).on('click', '.btn-editar-usuario', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const id = $btn.data('id');
                const action = `/admin/usuario/${id}`;
                abrirDrawer('Editar Usuário', action, 'PUT', {
                    nome: $btn.data('nome'),
                    email: $btn.data('email'),
                    celular: $btn.data('celular'),
                    cargo: $btn.data('cargo')
                });
            });

            $('#closeSideDrawer, #cancelSideDrawer, #sideDrawerBackdrop').on('click', function () {
                fecharDrawer();
            });

            <?php if($errors->any()): ?>
                abrirDrawer(
                    "<?php echo e(old('_method', 'POST') === 'PUT' ? 'Editar Usuário' : 'Cadastrar Usuário'); ?>",
                    "<?php echo e(old('_method', 'POST') === 'PUT' ? route('usuario.update', old('id', 0)) : route('usuario.store')); ?>",
                    "<?php echo e(old('_method', 'POST') === 'PUT' ? 'PUT' : 'POST'); ?>",
                    "<?php echo e(old('_method', 'POST') === 'PUT'); ?>" ? {
                        nome: "<?php echo e(old('nome')); ?>",
                        email: "<?php echo e(old('email')); ?>",
                        celular: "<?php echo e(old('celular')); ?>",
                        cargo: "<?php echo e(old('cargo')); ?>"
                    } : null
                );
            <?php endif; ?>

            function escaparHtml(texto) {
                return String(texto ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function mostrarSucesso(mensagem) {
                const options = {
                    icon: 'success',
                    title: 'Senha atualizada',
                    html: `
                        <div style="text-align:left">
                            <p class="mb-2">${escaparHtml(mensagem)}</p>
                            <p class="mb-0">Oriente o usuário a entrar com a senha padrão e trocar no primeiro acesso.</p>
                        </div>
                    `,
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#16a34a',
                };

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire(options);
                    return;
                }

                if (typeof window.alertConfirmPequena === 'function') {
                    window.alertConfirmPequena(options.title, mensagem, 'success');
                    return;
                }

                window.alert(options.title + '\n\n' + mensagem);
            }

            function confirmarReset($form, userName) {
                const texto = `
                    <div style="text-align:left">
                        <p class="mb-2">Tem certeza que deseja resetar a senha de <strong>${escaparHtml(userName)}</strong>?</p>
                        <p class="mb-2">A senha será definida como <strong>${senhaPadrao}</strong>.</p>
                        <p class="mb-0">No próximo acesso, o colaborador será direcionado para criar uma nova senha.</p>
                    </div>
                `;

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Resetar senha?',
                        html: texto,
                        showCancelButton: true,
                        confirmButtonText: 'Sim, resetar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $form.trigger('submit');
                        }
                    });
                    return;
                }

                if (window.confirm('Tem certeza que deseja resetar a senha de ' + userName + ' para ' + senhaPadrao + '?')) {
                    $form.trigger('submit');
                }
            }

            $(document).on('click', '.js-reset-senha', function () {
                const $btn = $(this);
                const userName = String($btn.data('user-name') || 'este usuário');
                const formId = String($btn.data('form-id') || '');
                const $form = $('#' + formId);

                if (!$form.length) {
                    return;
                }

                confirmarReset($form, userName);
            });

            const resetMessage = <?php echo json_encode(session('usuarioResetSenhaMensagem'), 15, 512) ?>;
            if (resetMessage) {
                mostrarSucesso(resetMessage);
                return;
            }

            const successMessage = <?php echo json_encode(session('success'), 15, 512) ?>;
            if (successMessage) {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Sucesso',
                        text: successMessage,
                        confirmButtonColor: '#16a34a',
                    });
                } else if (typeof window.alertConfirmPequena === 'function') {
                    window.alertConfirmPequena('Sucesso', successMessage, 'success');
                } else {
                    window.alert('Sucesso\n\n' + successMessage);
                }
            }

            const body = document.querySelector('.ux-index-body');
            const btns = document.querySelectorAll('.ux-view-toggle [data-view]');
            const store = 'usuario_index_view';

            function setView(view) {
                if (!body) return;
                body.dataset.view = view;
                btns.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
                localStorage.setItem(store, view);
            }

            btns.forEach(btn => btn.addEventListener('click', function () {
                setView(btn.dataset.view);
            }));

            setView(localStorage.getItem(store) || 'cards');
        });
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'usuario-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
{ element: '.page-title', popover: { title: 'Lista de Usuários', description: 'Aqui você gerencia todos os usuários do sistema: clientes, colaboradores, gerentes e administradores. Cada usuário tem uma regra de acesso que define o que ele pode ver e fazer.' } },
        { element: '.btn-create-resource, a[href*="usuario/create"]', popover: { title: 'Adicionar usuário', description: 'Clique aqui para cadastrar um novo usuário. O formulário é dividido em etapas: Dados Pessoais, Contato e Endereço, Perfil e Acesso, Carta de Condução e Segurança (senha).', side: 'bottom' } },
        { element: '.tabela-usuarios thead tr', popover: { title: 'Tabela de usuários', description: 'Exibe nome, e-mail, telefone, regra de acesso, cidade e data de cadastro. Clique no cabeçalho para ordenar.', side: 'bottom' } },
        { element: '.tabela-usuarios tbody tr:first-child td', popover: { title: 'Regra de acesso', description: 'O badge colorido indica o perfil do usuário (Cliente, Colaborador, Gerente, Administrador). A regra controla quais módulos e ações cada usuário pode acessar.', side: 'right' } },
        { element: '.tabela-usuarios tbody tr:first-child td:last-child', popover: { title: 'Ações disponíveis', description: 'Para cada usuário: <b>Pagamento</b> (gerenciar cartões Stripe), <b>Editar</b> (alterar dados), <b>Resetar senha</b> (voltar para senha padrão) e <b>Excluir</b>.', side: 'left' } },
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


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/usuario/index.blade.php ENDPATH**/ ?>