<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'modalId' => 'modalBuscarItemManutencao',
    'novoModalId' => 'modalNovoItemManutencao',
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
    'modalId' => 'modalBuscarItemManutencao',
    'novoModalId' => 'modalNovoItemManutencao',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<style>
    .manutencao-item-modal__content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 70px rgba(15, 23, 42, .22); }
    .manutencao-item-modal__header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: #fff; border-bottom: 0; position: relative; }
    .manutencao-item-modal__header::after { content: ''; position: absolute; inset: auto 0 0 0; height: 3px; background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 55%, #22c55e 100%); }
    .manutencao-item-modal__body { background: #f8fafc; padding: 1rem; }
    .manutencao-item-modal__toolbar { display: grid; grid-template-columns: minmax(0, 1fr) 140px 120px; gap: .75rem; align-items: center; margin-bottom: .85rem; }
    .manutencao-item-modal__toolbar .form-control, .manutencao-item-modal__toolbar .custom-select { border-radius: 12px; border-color: #d8e1ec; min-height: 42px; }
    .manutencao-item-modal__results { display: grid; gap: .6rem; }
    .manutencao-item-modal__card { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 6px 16px rgba(15, 23, 42, .04); padding: .7rem .8rem; }
    .manutencao-item-modal__card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: .6rem; margin-bottom: .45rem; }
    .manutencao-item-modal__title { margin: 0; font-size: .9rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .manutencao-item-modal__sub { color: #64748b; font-size: .75rem; margin-top: .15rem; }
    .manutencao-item-modal__grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .45rem; }
    .manutencao-item-modal__meta { display: flex; flex-direction: column; gap: .1rem; padding: .42rem .5rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; min-width: 0; }
    .manutencao-item-modal__meta-label { font-size: .66rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; }
    .manutencao-item-modal__meta-value { font-size: .78rem; font-weight: 700; color: #0f172a; line-height: 1.2; word-break: break-word; }
    .manutencao-item-modal__pagination { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: .9rem; flex-wrap: wrap; }
    .manutencao-item-modal__pager .page-link { border-radius: 10px; margin: 0 .12rem; }
    @media (min-width: 1200px) {
        .manutencao-item-modal__grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .manutencao-item-modal__body { padding: .75rem; }
        .manutencao-item-modal__toolbar { grid-template-columns: 1fr; }
        .manutencao-item-modal__grid { grid-template-columns: 1fr; }
        .manutencao-item-modal__card-head { flex-direction: column; }
    }
</style>

<div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content manutencao-item-modal__content">
            <div class="modal-header manutencao-item-modal__header py-3">
                <div class="d-flex align-items-start">
                    <div class="vehicle-modal__icon mr-3" style="background: rgba(255,255,255,.12); color:#fff;"><i class="fas fa-search"></i></div>
                    <div>
                        <span class="veiculo-busca-modal__eyebrow">Itens de manutenção</span>
                        <h5 class="modal-title veiculo-busca-modal__title"><?php echo e($title ?? 'Buscar item de manutenção'); ?></h5>
                        <small class="veiculo-busca-modal__subtitle">Use a busca para localizar o item e adicionar ao plano.</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body manutencao-item-modal__body">
                <div class="manutencao-item-modal__toolbar">
                    <input type="text" id="buscaItemManutencao" class="form-control" placeholder="Pesquisar por nome ou descrição...">
                    <select id="itensManutencaoPerPage" class="custom-select">
                        <option value="8">8 por página</option>
                        <option value="12" selected>12 por página</option>
                        <option value="16">16 por página</option>
                        <option value="20">20 por página</option>
                    </select>
                    <div class="text-right text-muted small" id="itensManutencaoResumo">0 itens</div>
                </div>
                <div class="manutencao-item-modal__results" id="resultadoBuscaItens">
                    <div class="text-muted small text-center py-3">Digite para pesquisar.</div>
                </div>
                <div class="manutencao-item-modal__pagination" id="itensManutencaoPaginacao"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="<?php echo e($novoModalId); ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo item de manutenção</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="novoItemNome">Nome</label>
                    <input type="text" id="novoItemNome" class="form-control" maxlength="255">
                </div>
                <div class="form-group mb-0">
                    <label for="novoItemDescricao">Descrição</label>
                    <textarea id="novoItemDescricao" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoItemManutencao">Salvar item</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\manutencao\busca-item-modal.blade.php ENDPATH**/ ?>