<div class="modal fade" id="modalPagarParcelaDocumentacao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content vehicle-modal vehicle-modal--alert">
            <form id="formPagarParcelaDocumentacao" method="POST" action="">
                <?php echo csrf_field(); ?>
                <div class="modal-header vehicle-modal__header border-bottom-0">
                    <div class="d-flex align-items-start">
                        <div class="vehicle-modal__icon mr-3">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div>
                            <div class="vehicle-modal__eyebrow">Documentação do veículo</div>
                            <h5 class="modal-title mb-1 vehicle-modal__title">Registrar pagamento</h5>
                            <small class="vehicle-modal__subtitle" id="modalPagarParcelaDocumentacaoLabel">Confirme a data do pagamento da parcela.</small>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body vehicle-modal__body">
                    <div class="form-group mb-0">
                        <label for="modal_data_pagamento_documentacao" class="vehicle-modal__label">Data do pagamento</label>
                        <input type="date" class="form-control vehicle-modal__control" id="modal_data_pagamento_documentacao" name="data_pagamento" value="<?php echo e(now()->toDateString()); ?>" required>
                    </div>
                </div>
                <div class="modal-footer vehicle-modal__footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-xp btn-xp--ghost btn-xp--sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-xp btn-xp--sm">
                        <i class="fas fa-check mr-1"></i> Confirmar pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('ffda76c1-344d-4600-adc3-f07b546dec8e')): $__env->markAsRenderedOnce('ffda76c1-344d-4600-adc3-f07b546dec8e'); ?>
    <style>
        .vehicle-modal {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(12, 20, 40, .18);
        }

        .vehicle-modal--alert .vehicle-modal__header {
            background: linear-gradient(135deg, #0c1428 0%, #1a2848 60%, #2e4080 100%);
            color: #fff;
            padding: 20px 22px;
            position: relative;
        }

        .vehicle-modal--alert .vehicle-modal__header::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #f97316, #10b981);
        }

        .vehicle-modal__icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            color: #60a5fa;
        }

        .vehicle-modal__eyebrow {
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(226, 232, 240, .78);
            margin-bottom: 4px;
        }

        .vehicle-modal__title {
            color: #fff;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .vehicle-modal__subtitle {
            color: rgba(226, 232, 240, .82);
        }

        .vehicle-modal__body {
            background: #fff;
            padding: 22px !important;
        }

        .vehicle-modal__footer {
            background: #f8fafc;
            padding: 14px 22px;
        }

        .vehicle-modal__label {
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 8px;
        }

        .vehicle-modal__control {
            min-height: 46px;
            border-radius: 12px;
            border-color: #dbe4f0;
            box-shadow: none;
        }

        .vehicle-modal__control:focus {
            border-color: rgba(67, 56, 202, .45);
            box-shadow: 0 0 0 .2rem rgba(67, 56, 202, .10);
        }
    </style>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/documentacao-pagamento-modal.blade.php ENDPATH**/ ?>