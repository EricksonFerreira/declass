<?php $__env->startSection('title', 'Configurações Fiscais'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Configurações Fiscais da Empresa'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <?php echo $__env->make('financeiro.fluxo_caixa._styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="fc-page">
        <?php if(session('success')): ?>
            <div class="alert alert-success shadow-sm mb-3" style="border-radius: 8px;"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('fiscal.config.salvar')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="card fc-card">
                <div class="card-header py-3">
                    <h5 class="fc-title mb-0">
                        <i class="fas fa-sliders-h text-primary"></i>
                        <div>
                            <span>Configuração Geral de Emissão e Integrações</span>
                            <span class="fc-subtitle">Defina o regime de emissão das notas, envie o certificado A1 e configure credenciais de API</span>
                        </div>
                    </h5>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <!-- Regime de Emissão -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Regime de Emissão de Documentos Fiscais *</label>
                            <select name="regime_emissao" class="form-control" required>
                                <option value="NFSE_APENAS" <?php echo e($empresa->regime_emissao === 'NFSE_APENAS' ? 'selected' : ''); ?>>
                                    Apenas NFS-e Nacional (Emitir para 100% da Locação)
                                </option>
                                <option value="RECIBO_APENAS" <?php echo e($empresa->regime_emissao === 'RECIBO_APENAS' ? 'selected' : ''); ?>>
                                    Apenas Recibo/Fatura de Locação (Isento de ISS)
                                </option>
                                <option value="MISTO" <?php echo e($empresa->regime_emissao === 'MISTO' ? 'selected' : ''); ?>>
                                    Regime Misto (Recibo para Diárias + NFS-e para Opcionais/Serviços)
                                </option>
                            </select>
                            <small class="text-muted">
                                De acordo com a Súmula 31 do STF, a locação de veículos pura é isenta de ISSQN. O regime misto automatiza a separação legal.
                            </small>
                        </div>

                        <!-- Ambiente NFS-e -->
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Ambiente de Transmissão *</label>
                            <select name="nfse_ambiente" class="form-control" required>
                                <option value="homologacao" <?php echo e($empresa->nfse_ambiente === 'homologacao' ? 'selected' : ''); ?>>Homologação (Testes sem valor fiscal)</option>
                                <option value="producao" <?php echo e($empresa->nfse_ambiente === 'producao' ? 'selected' : ''); ?>>Produção (Emissão real autorizada)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Certificado Digital Block -->
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-key text-primary mr-1"></i> Certificado Digital A1
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Arquivo de Certificado Digital (.pfx / .p12)</label>
                            <input type="file" name="certificado_file" class="form-control-file" accept=".pfx,.p12">
                            <?php if($empresa->certificado_path): ?>
                                <small class="text-success font-weight-bold d-block mt-1">
                                    <i class="fas fa-check-circle"></i> Certificado A1 já cadastrado no sistema.
                                </small>
                            <?php else: ?>
                                <small class="text-muted">Selecione o arquivo .pfx fornecido pela sua certificadora digital.</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Senha do Certificado Digital</label>
                            <input type="password" name="certificado_senha" class="form-control" placeholder="Preencha apenas para cadastrar/alterar">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- API Integration Block -->
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-network-wired text-primary mr-1"></i> Integração com o Gateway Fiscal
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Token de API (Bearer Token)</label>
                            <input type="text" name="fiscal_token" class="form-control" value="<?php echo e($empresa->fiscal_token); ?>" placeholder="Chave secreta obtida no painel da API">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fc-label">Client ID (se aplicável)</label>
                            <input type="text" name="fiscal_client_id" class="form-control" value="<?php echo e($empresa->fiscal_client_id); ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Dados Cadastrais Adicionais -->
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fas fa-file-invoice mr-1 text-primary"></i> Informações Cadastrais NFS-e
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="fc-label">Inscrição Municipal</label>
                            <input type="text" name="nfse_inscricao_municipal" class="form-control" value="<?php echo e($empresa->nfse_inscricao_municipal); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fc-label">CNAE Principal</label>
                            <input type="text" name="nfse_cnae" class="form-control" value="<?php echo e($empresa->nfse_cnae); ?>" placeholder="ex: 7711-0/00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fc-label">Código de Serviço Padrão</label>
                            <input type="text" name="nfse_codigo_servico" class="form-control" value="<?php echo e($empresa->nfse_codigo_servico); ?>">
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end" style="gap: 8px;">
                    <a href="<?php echo e(route('fiscal.dashboard')); ?>" class="btn btn-outline-secondary fc-btn">Cancelar</a>
                    <button type="submit" class="btn btn-primary fc-btn">
                        <i class="fas fa-save mr-1"></i> Salvar Configurações
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/fiscal/configuracoes.blade.php ENDPATH**/ ?>