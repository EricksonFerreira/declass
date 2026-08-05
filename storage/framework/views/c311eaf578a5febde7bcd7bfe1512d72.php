<div class="modal fade" id="modalNovoDano" tabindex="-1" role="dialog" aria-labelledby="detalhesDanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content dano-modal">
            <form id="form_dano">
                <div class="dano-modal__header">
                    <div class="dano-modal__header-left">
                        <div class="dano-modal__header-icon">
                            <i class="fas fa-car-crash"></i>
                        </div>
                        <div>
                            <div class="dano-modal__eyebrow">Registro de Dano</div>
                            <div class="d-flex align-items-center">
                                <h6 class="dano-modal__title mr-2">Vista:</h6>
                                <select id="vista_modal" required class="form-control form-control-sm select2ci-modal">
                                    <option value="">Selecione uma vista</option>
                                    <option value="DIREITA">DIREITA</option>
                                    <option value="ESQUERDA">ESQUERDA</option>
                                    <option value="CIMA">CIMA</option>
                                    <option value="FRENTE">FRENTE</option>
                                    <option value="ATRAS">ATRAS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="dano-modal__close" data-dismiss="modal" aria-label="Fechar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="dano-modal__body">
                    <input type="hidden" id="local_dano" name="local_dano" value="frente">
                    <input type="hidden" id="veiculo_id" name="veiculo_id" value="<?php echo e($veiculo->id ?? ''); ?>">

                    <div id="mensagem_erro"></div>

                    <div class="dano-block">
                        <div class="dano-block__label">
                            <i class="fas fa-tag"></i>
                            <div>
                                <strong>Categoria e Localizacao</strong>
                                <span>Classifique onde e em qual area o dano se encontra</span>
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'categoria_id','label' => 'Categoria do Problema','options' => $categoriaDanos,'placeholder' => 'Selecione uma categoria','selected' => old('categoria_id', null)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'categoria_problema','class' => 'form-control form-control-sm','required' => true,'onchange' => 'obterDanos()']); ?>
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
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="localizacao_problema">Localizacao</label>
                                    <select class="form-control form-control-sm" id="localizacao_problema" name="localizacao_problema" onchange="obterDanos()">
                                        <option value="exterior">Exterior</option>
                                        <option value="interior">Interior</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <div class="dano-select-label">
                                        <label for="danos">Tipo de Dano <span class="req">*</span></label>
                                        <img id="loading_dano" style="width:14px;height:14px;display:none" src="<?php echo e(asset('assets/gifs/loading.gif')); ?>" alt="Carregando...">
                                    </div>
                                    <select name="dano_id" id="danos" class="form-control form-control-sm select2ci-modal" required>
                                        <option disabled selected>Primeiro selecione uma categoria</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dano-block">
                        <div class="dano-block__label">
                            <i class="fas fa-camera"></i>
                            <div>
                                <strong>Severidade, Evidencias e Observacoes</strong>
                                <span>Adicione fotos e notas complementares sobre o dano</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="mb-2 d-block">Tipo de Avaria <span class="req">*</span></label>
                                <div class="dano-radio-group">
                                    <?php
                                        $tipos = [
                                            ['value' => 'ausente', 'label' => 'Ausente', 'icon' => 'fas fa-check', 'color' => 'green'],
                                            ['value' => 'amolgadela_leve', 'label' => 'Amolgadela Leve', 'icon' => 'fas fa-dot-circle', 'color' => 'amber'],
                                            ['value' => 'amolgadela_grave', 'label' => 'Amolgadela Grave', 'icon' => 'fas fa-circle', 'color' => 'red'],
                                            ['value' => 'risco_leve', 'label' => 'Risco Leve', 'icon' => 'fas fa-slash', 'color' => 'amber'],
                                            ['value' => 'risco_grave', 'label' => 'Risco Grave', 'icon' => 'fas fa-times', 'color' => 'red'],
                                        ];
                                    ?>
                                    <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <label class="dano-radio-card dano-radio-card--<?php echo e($tipo['color']); ?>">
                                            <input type="radio" name="tipo" value="<?php echo e($tipo['value']); ?>" <?php echo e($loop->first ? 'checked' : ''); ?>>
                                            <i class="<?php echo e($tipo['icon']); ?>"></i>
                                            <span><?php echo e($tipo['label']); ?></span>
                                        </label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="observacoes_dano">Observacoes</label>
                                <textarea class="form-control form-control-sm" id="observacoes_dano" name="observacoes_dano" rows="3" placeholder="Descreva detalhes adicionais sobre o dano..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label>Fotos do Dano</label>
                                <label for="fotos-dano" class="dano-upload-btn">
                                    <i class="fas fa-upload"></i>
                                    <span>Selecionar fotos</span>
                                </label>
                                <input type="file" class="dano-upload-input" id="fotos-dano" name="fotos[]" multiple accept="image/*">
                                <small class="dano-hint">JPG, PNG ou WEBP. Multiplas fotos permitidas.</small>
                            </div>
                        </div>

                        <div style="display:none" id="div_previsualizar">
                            <label class="mt-3 d-block">Pre-visualizacao</label>
                            <div id="previsualizar_imagens" class="dano-preview-grid"></div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="dano-modal__footer">
                <button type="button" class="dano-btn dano-btn--ghost" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" form="form_dano" class="dano-btn dano-btn--primary">
                    <i class="fas fa-save"></i>
                    Salvar Dano
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/revisao_checkout_modal_novo_dano.blade.php ENDPATH**/ ?>