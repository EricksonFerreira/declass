<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'empresa',
    'contratos',
    'planosContrato',
    'razaoSocialPadrao',
    'cnpjPadrao',
    'emailPadrao',
    'telefonePadrao',
    'enderecoPadrao',
    'representantePadrao',
    'quantidadeVeiculosPadrao',
    'valorPadrao',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'empresa',
    'contratos',
    'planosContrato',
    'razaoSocialPadrao',
    'cnpjPadrao',
    'emailPadrao',
    'telefonePadrao',
    'enderecoPadrao',
    'representantePadrao',
    'quantidadeVeiculosPadrao',
    'valorPadrao',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="rs-card">
    <div class="rs-card__header">
        <i class="fas fa-file-signature rs-card__icon"></i>
        <h2>Contratos da Empresa</h2>
        <span class="rs-badge-count"><?php echo e($contratos->count()); ?></span>
        <button type="button" class="rs-btn rs-btn--primary ml-auto" data-toggle="modal" data-target="#modalGerarContratoEmpresa">
            <i class="fas fa-file-contract"></i> Gerar contrato
        </button>
    </div>

    <?php if($contratos->isEmpty()): ?>
        <div class="p-3">
            <div class="rs-empty">
                <i class="fas fa-file-alt"></i>
                <span>Nenhum contrato registrado para esta empresa.</span>
            </div>
        </div>
    <?php else: ?>
        <div class="rs-contratos-grid">
            <?php $__currentLoopData = $contratos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contrato): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $contratoAssinado = strtolower((string) $contrato->status) === 'assinado' || ! empty($contrato->assinado_em); ?>
                <div class="rs-contrato-card">
                    <div class="rs-contrato-top">
                        <div>
                            <div class="rs-contrato-numero"><?php echo e($contrato->numero); ?></div>
                            <div class="rs-contrato-titulo"><?php echo e($contrato->titulo); ?></div>
                        </div>
                        <span class="badge badge-<?php echo e($contratoAssinado ? 'success' : 'info'); ?>">
                            <?php echo e($contratoAssinado ? 'Assinado' : ucfirst($contrato->status)); ?>

                        </span>
                    </div>
                    <div class="rs-contrato-meta">Gerado em <?php echo e($contrato->gerado_em?->format('d/m/Y H:i') ?? '-'); ?></div>
                    <div class="rs-contrato-actions">
                        <?php if($contrato->arquivo_pdf_path): ?>
                            <a href="<?php echo e(route('empresa.contratos.download', ['empresa' => $empresa->id, 'contrato' => $contrato->id])); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i> <?php echo e($contratoAssinado ? 'Baixar contrato assinado' : 'Baixar contrato'); ?>

                            </a>
                        <?php endif; ?>
                        <?php if(! $contratoAssinado): ?>
                            <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#modalAssinarContrato<?php echo e($contrato->id); ?>">
                                <i class="fas fa-file-upload mr-1"></i> Inserir PDF assinado
                            </button>
                            <form action="<?php echo e(route('empresa.contratos.remover', ['empresa' => $empresa->id, 'contrato' => $contrato->id])); ?>" method="POST" onsubmit="return confirm('Deseja remover este contrato?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash mr-1"></i> Remover
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="small text-muted align-self-center">Contrato assinado. Apenas download disponível.</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<?php $__currentLoopData = $contratos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contrato): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="modalAssinarContrato<?php echo e($contrato->id); ?>" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="<?php echo e(route('empresa.contratos.assinar', ['empresa' => $empresa->id, 'contrato' => $contrato->id])); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title mb-0"><i class="fas fa-file-signature mr-2"></i>Inserir PDF assinado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Contrato <?php echo e($contrato->numero); ?></p>
                        <div class="form-group">
                            <label>Arquivo PDF assinado</label>
                            <input type="file" name="pdf_assinado" class="form-control" accept="application/pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload mr-1"></i> Enviar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="modal fade" id="modalGerarContratoEmpresa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form action="<?php echo e(route('empresa.contratos.gerar', $empresa->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0"><i class="fas fa-file-contract mr-2"></i>Gerar contrato</h5>
                        <small class="text-muted">Preencha os dados do cliente e selecione o plano.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Razão social</label>
                            <input type="text" name="razao_social" class="form-control" value="<?php echo e(old('razao_social', $razaoSocialPadrao)); ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>CNPJ do cliente</label>
                            <input type="text" name="cnpj_cliente" class="form-control" value="<?php echo e(old('cnpj_cliente', $cnpjPadrao)); ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>E-mail</label>
                            <input type="email" name="email_cliente" class="form-control" value="<?php echo e(old('email_cliente', $emailPadrao)); ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone_cliente" id="telefoneClienteContrato" class="form-control" value="<?php echo e(old('telefone_cliente', $telefonePadrao)); ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Quantidade de veículos</label>
                            <input type="number" min="1" step="1" name="quantidade_veiculos" id="quantidadeVeiculosContrato" class="form-control" value="<?php echo e(old('quantidade_veiculos', $quantidadeVeiculosPadrao)); ?>" required>
                        </div>
                        <div class="col-md-8 form-group">
                            <label>Endereço</label>
                            <input type="text" name="endereco_cliente" class="form-control" value="<?php echo e(old('endereco_cliente', $enderecoPadrao)); ?>" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Nome do representante</label>
                            <input type="text" name="representante_nome" class="form-control" value="<?php echo e(old('representante_nome', $representantePadrao)); ?>" required>
                        </div>
                        <div class="col-md-8 form-group">
                            <label>Plano</label>
                            <select name="plano_id" id="empresaContratoPlano" class="form-control" required>
                                <option value="">Selecione um plano</option>
                                <?php $__currentLoopData = $planosContrato; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pl->id); ?>" data-valor="<?php echo e($pl->valor_mensal ?: $pl->valor_anual); ?>" data-veiculos="<?php echo e($pl->limite_veiculos ?? 20); ?>">
                                        <?php echo e($pl->nome); ?> - R$ <?php echo e(number_format((float) ($pl->valor_mensal ?: $pl->valor_anual), 2, ',', '.')); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Valor</label>
                            <input type="number" step="0.01" min="0" name="valor" id="empresaContratoValor" class="form-control" value="<?php echo e(old('valor', $valorPadrao)); ?>" required>
                        </div>
                    </div>
                    <div class="alert alert-light border mb-0">
                        O valor pode ser ajustado manualmente antes de gerar o PDF.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGerarContratoEmpresa" class="btn btn-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/empresa/contratos.blade.php ENDPATH**/ ?>