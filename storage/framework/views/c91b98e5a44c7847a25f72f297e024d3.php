<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [
        ['name' => 'Veículos', 'url' => route('veiculo.index')],
        ['name' => 'Documentação', 'url' => route('veiculo.documentacao')],
        ['name' => isset($documentacao) ? 'Editar' : 'Criar']
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
        $veiculoSelecionadoNome = $veiculoSelecionado?->nome ?? '';
        $veiculoSelecionadoPlaca = $veiculoSelecionado?->placa_licenca ?? '';
        $veiculoBloqueado = ! empty($veiculoId);
        $formAction = isset($documentacao)
            ? route('veiculo.documentacao.update', $documentacao)
            : route('veiculo.documentacao.store');
        $parcelasExistentes = isset($documentacao)
            ? $documentacao->parcelas->map(function ($parcela) {
                return [
                    'data_vencimento' => optional($parcela->data_vencimento)->format('Y-m-d') ?? '',
                    'data_pagamento' => optional($parcela->data_pagamento)->format('Y-m-d') ?? '',
                    'status' => (string) ($parcela->status ?? 'pendente'),
                ];
            })->values()->all()
            : [];

        $tipoAtual = old('tipo', $documentacao->tipo ?? '');
        $descricaoAtual = old('descricao', $documentacao->descricao ?? '');
        $parcelasTotalAtual = (int) old('parcelas_total', $documentacao->parcelas_total ?? 1);
        $valorParcelaAtual = old('valor_parcela', $documentacao->valor_parcela ?? '');
        $dataPrimeiroVencimentoAtual = old(
            'data_primeiro_vencimento',
            isset($documentacao) && $documentacao->parcelas->isNotEmpty()
                ? optional($documentacao->parcelas->first()->data_vencimento)->format('Y-m-d')
                : ''
        );
    ?>

    <div class="container-fluid px-0 vehicle-show-page">
        <div class="module-card mb-3">
            <div class="module-card-header vehicle-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Documentação</div>
                        <h1 class="module-header-title"><?php echo e(isset($documentacao) ? 'Editar documentação' : 'Nova documentação'); ?></h1>
                        <p class="module-header-sub">
                            Organize IPVA, licenciamento e seguro com geração automática de parcelas e lançamentos financeiros.
                        </p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="<?php echo e(route('veiculo.documentacao')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-list"></i> Lista
                    </a>
                    <a href="<?php echo e(route('veiculo.index')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 mb-3">
                <div class="config-card shadow-sm border-0">
                    <div class="vs-panel-head border-bottom">
                        <div>
                            <span class="vs-panel-head__eyebrow">Cadastro principal</span>
                            <h4 class="vs-panel-head__title mb-1"><?php echo e(isset($documentacao) ? 'Atualizar documento' : 'Cadastrar documento'); ?></h4>
                            <p class="vs-panel-head__sub mb-0">Preencha os dados do veículo, tipo do documento e o cronograma de parcelas.</p>
                        </div>
                        <span class="vs-badge-soft is-warning">
                            <i class="fas fa-file-alt"></i>
                            <?php echo e($veiculoBloqueado ? 'Veículo fixado pela URL' : 'Veículo selecionável'); ?>

                        </span>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="<?php echo e($formAction); ?>" id="documentacao-form">
                            <?php echo csrf_field(); ?>
                            <?php if(isset($documentacao)): ?>
                                <?php echo method_field('PUT'); ?>
                            <?php endif; ?>

                            <div class="vehicle-form-section">
                                <div class="vehicle-form-section__head">
                                    <div>
                                        <strong>Vínculo do veículo</strong>
                                        <span>Selecione o veículo ao qual a documentação será aplicada.</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-7 form-group">
                                        <label class="vehicle-modal__label">Veículo <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="hidden" name="veiculo_id" id="veiculo_id" value="<?php echo e(old('veiculo_id', $veiculoId ?? '')); ?>" required>
                                            <input
                                                type="text"
                                                id="veiculo_display"
                                                class="form-control vehicle-modal__control"
                                                value="<?php echo e(old('veiculo_display', $veiculoSelecionadoNome ? ($veiculoSelecionadoNome . ' - ' . $veiculoSelecionadoPlaca) : '')); ?>"
                                                placeholder="Selecione um veículo"
                                                readonly
                                            >
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculoDocumento" title="Buscar veículo" <?php echo e($veiculoBloqueado ? 'disabled' : ''); ?>>
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php if($veiculoBloqueado): ?>
                                            <small class="text-muted d-block mt-2">O veículo foi definido pela URL e está bloqueado para edição manual.</small>
                                        <?php else: ?>
                                            <small class="text-muted d-block mt-2">Use a busca para localizar por nome, placa, chassi, marca ou grupo.</small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-lg-5 form-group">
                                        <label class="vehicle-modal__label">Tipo <span class="text-danger">*</span></label>
                                        <select name="tipo" class="form-control vehicle-modal__control" required>
                                            <option value="">Selecione</option>
                                            <option value="ipva" <?php echo e($tipoAtual === 'ipva' ? 'selected' : ''); ?>>IPVA</option>
                                            <option value="licenciamento" <?php echo e($tipoAtual === 'licenciamento' ? 'selected' : ''); ?>>Licenciamento</option>
                                            <option value="seguro" <?php echo e($tipoAtual === 'seguro' ? 'selected' : ''); ?>>Seguro</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-8 form-group">
                                        <label class="vehicle-modal__label">Descrição</label>
                                        <input type="text" name="descricao" class="form-control vehicle-modal__control" value="<?php echo e($descricaoAtual); ?>" placeholder="Ex.: IPVA 2026, Seguro anual, Licenciamento">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label class="vehicle-modal__label">Status</label>
                                        <div class="vehicle-readonly-pill">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?php echo e(isset($documentacao) ? ucfirst((string) ($documentacao->status ?? 'ativo')) : 'Ativo'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="vehicle-form-section">
                                <div class="vehicle-form-section__head">
                                    <div>
                                        <strong>Parcelamento</strong>
                                        <span>Defina a data inicial, quantidade de parcelas e o valor de cada lançamento.</span>
                                    </div>
                                </div>

                                <div class="row align-items-end">
                                    <div class="col-md-3 form-group">
                                        <label class="vehicle-modal__label">Primeiro vencimento <span class="text-danger">*</span></label>
                                        <input type="date" name="data_primeiro_vencimento" class="form-control vehicle-modal__control" id="data_primeiro_vencimento" value="<?php echo e($dataPrimeiroVencimentoAtual); ?>" required>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label class="vehicle-modal__label">Parcelas <span class="text-danger">*</span></label>
                                        <input type="number" name="parcelas_total" class="form-control vehicle-modal__control" id="parcelas_total" min="1" value="<?php echo e($parcelasTotalAtual); ?>" required>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label class="vehicle-modal__label">Valor por parcela <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="valor_parcela" class="form-control vehicle-modal__control" id="valor_parcela" min="0" value="<?php echo e($valorParcelaAtual); ?>" required>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <button type="button" class="btn btn-xp btn-xp--outline btn-xp--sm w-100" id="gerar_parcelas">
                                            <i class="fas fa-sync-alt mr-1"></i> Gerar
                                        </button>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <button class="btn btn-xp btn-xp--sm w-100">
                                            <i class="fas fa-save mr-1"></i> Salvar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="vehicle-form-section mb-0">
                                <div class="vehicle-form-section__head">
                                    <div>
                                        <strong>Preview das parcelas</strong>
                                        <span>Confira as datas e status antes de gravar os lançamentos.</span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0 vehicle-preview-table" id="parcelas_preview">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Vencimento</th>
                                                <th>Data pagamento</th>
                                                <th>Valor</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Informe a data inicial e a quantidade de parcelas.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <script type="application/json" id="parcelas-existentes-data"><?php echo json_encode($parcelasExistentes, 15, 512) ?></script>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-3">
                <div class="config-card shadow-sm border-0 h-100">
                    <div class="vs-panel-head border-bottom">
                        <div>
                            <span class="vs-panel-head__eyebrow">Resumo</span>
                            <h4 class="vs-panel-head__title mb-1">Fluxo financeiro</h4>
                            <p class="vs-panel-head__sub mb-0">A documentação gera parcelas e lançamentos pendentes automaticamente.</p>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="vehicle-summary-stack">
                            <div class="vehicle-summary-card">
                                <span>Categoria financeira</span>
                                <strong>Despesa operacional</strong>
                                <small>Categoria criada ou reaproveitada no lançamento automático.</small>
                            </div>

                            <div class="vehicle-summary-card">
                                <span>Parcelas</span>
                                <strong><?php echo e($parcelasTotalAtual); ?>x</strong>
                                <small>O preview é atualizado conforme você altera os campos.</small>
                            </div>

                            <div class="vehicle-summary-card">
                                <span>Veículo</span>
                                <strong><?php echo e($veiculoSelecionadoNome ?: 'Não selecionado'); ?></strong>
                                <small><?php echo e($veiculoSelecionadoPlaca ?: 'Use a busca para preencher o vínculo'); ?></small>
                            </div>
                        </div>

                        <div class="vehicle-summary-note">
                            <i class="fas fa-info-circle"></i>
                            O sistema cria automaticamente os lançamentos financeiros vinculados à documentação após salvar.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($component)) { $__componentOriginald90448cde4b2f348f499ac36119d4b7a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald90448cde4b2f348f499ac36119d4b7a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.veiculo.busca-entidade-modal','data' => ['modalId' => 'modalBuscarVeiculoDocumento','title' => 'Buscar veículo','buscarUrl' => ''.e(route('veiculo.buscar')).'','triggerId' => 'btnBuscarVeiculoDocumento','targetSelectId' => 'veiculo_id','targetDisplayId' => 'veiculo_display','placeholder' => 'Digite nome, placa, chassi, marca ou grupo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('veiculo.busca-entidade-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['modal-id' => 'modalBuscarVeiculoDocumento','title' => 'Buscar veículo','buscar-url' => ''.e(route('veiculo.buscar')).'','trigger-id' => 'btnBuscarVeiculoDocumento','target-select-id' => 'veiculo_id','target-display-id' => 'veiculo_display','placeholder' => 'Digite nome, placa, chassi, marca ou grupo']); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    :root {
        --c-ink: #0c1428;
        --c-navy: #1a2848;
        --c-indigo: #4338ca;
        --c-border: #e2e9f4;
        --c-slate: #64748b;
    }

    .vehicle-show-page {
        padding-bottom: 8px;
    }

    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12, 20, 40, .10);
        background: #fff;
    }

    .module-card-header {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    .module-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }

    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }

    .module-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .module-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #60a5fa;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }

    .module-header-eyebrow {
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, .8);
        display: block;
        margin-bottom: 3px;
    }

    .module-header-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -.02em;
    }

    .module-header-sub {
        margin: 0;
        font-size: .82rem;
        color: rgba(148, 163, 184, .9);
    }

    .module-header-right {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 8px;
    }

    .config-card {
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
    }

    .btn-xp {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all .18s;
        letter-spacing: -.01em;
        box-shadow: 0 4px 12px rgba(67, 56, 202, .25);
    }

    .btn-xp:hover {
        opacity: .88;
        transform: translateY(-1px);
        color: #fff;
        text-decoration: none;
    }

    .btn-xp--sm {
        padding: 6px 12px;
        font-size: .78rem;
        border-radius: 8px;
    }

    .btn-xp--outline {
        background: transparent;
        color: var(--c-indigo);
        border: 1.5px solid var(--c-indigo);
        box-shadow: none;
    }

    .btn-xp--outline:hover {
        background: #eef2ff;
        color: var(--c-indigo);
    }

    .btn-xp--outline-light {
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .25);
        color: rgba(255, 255, 255, .9);
        box-shadow: none;
    }

    .btn-xp--outline-light:hover {
        background: rgba(255, 255, 255, .2);
        color: #fff;
    }

    .vs-panel-head {
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        background: #fff;
    }

    .vs-panel-head__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }

    .vs-panel-head__title {
        margin: 0 0 6px;
        font-size: 1.08rem;
        font-weight: 800;
        color: #0c1428;
    }

    .vs-panel-head__sub {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        max-width: 680px;
    }

    .vs-badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
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

    .vehicle-form-section {
        border: 1px solid #e2e9f4;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border-radius: 16px;
        padding: 18px;
        margin-bottom: 16px;
    }

    .vehicle-form-section__head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e5eaf3;
    }

    .vehicle-form-section__head strong {
        display: block;
        color: #0c1428;
        font-size: .92rem;
        font-weight: 800;
    }

    .vehicle-form-section__head span {
        display: block;
        color: #64748b;
        font-size: .78rem;
        margin-top: 2px;
    }

    .vehicle-readonly-pill {
        min-height: 46px;
        border: 1px solid #dbe4f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 14px;
        background: #f8fafc;
        color: #0f172a;
        font-weight: 700;
    }

    .vehicle-readonly-pill i {
        color: #10b981;
    }

    .vehicle-summary-stack {
        display: grid;
        gap: 12px;
    }

    .vehicle-summary-card {
        background: #f8fafc;
        border: 1px solid #e9f0fa;
        border-radius: 14px;
        padding: 14px 16px;
    }

    .vehicle-summary-card span {
        display: block;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }

    .vehicle-summary-card strong {
        display: block;
        font-size: .98rem;
        color: #0c1428;
        font-weight: 800;
    }

    .vehicle-summary-card small {
        display: block;
        color: #64748b;
        margin-top: 4px;
        line-height: 1.4;
    }

    .vehicle-summary-note {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #3730a3;
        display: flex;
        gap: 10px;
        align-items: flex-start;
        font-size: .84rem;
        line-height: 1.5;
    }

    .vehicle-summary-note i {
        margin-top: 2px;
    }

    .vehicle-preview-table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .vehicle-preview-table td,
    .vehicle-preview-table th {
        vertical-align: middle;
    }

    .vehicle-preview-table tbody tr:hover td {
        background: #f8fafc;
    }

    @media (max-width: 991px) {
        .module-header-right {
            width: 100%;
            flex-wrap: wrap;
        }

        .module-header-right .btn-xp {
            flex: 1 1 auto;
            justify-content: center;
        }

        .module-card-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    (function () {
        const parcelasInput = document.getElementById('parcelas_total');
        const dataInput = document.getElementById('data_primeiro_vencimento');
        const valorInput = document.getElementById('valor_parcela');
        const preview = document.querySelector('#parcelas_preview tbody');
        const gerarBtn = document.getElementById('gerar_parcelas');
        const parcelasExistentesEl = document.getElementById('parcelas-existentes-data');
        let parcelasExistentes = [];

        if (parcelasExistentesEl) {
            try {
                parcelasExistentes = JSON.parse(parcelasExistentesEl.textContent || '[]');
            } catch (e) {
                parcelasExistentes = [];
            }
        }

        function toInputDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function addMonths(date, months) {
            const d = new Date(date.getTime());
            const day = d.getDate();
            d.setMonth(d.getMonth() + months);
            if (d.getDate() !== day) {
                d.setDate(0);
            }
            return d;
        }

        function renderPreview() {
            const total = parseInt(parcelasInput.value || '0', 10);
            const dataBase = dataInput.value ? new Date(dataInput.value + 'T00:00:00') : null;
            const valor = parseFloat(valorInput.value || '0');

            if (!total || !dataBase) {
                if (parcelasExistentes.length) {
                    const existingRows = parcelasExistentes.map(function (parcela, index) {
                        const vencimento = parcela.data_vencimento || '-';
                        const dataPagamento = parcela.data_pagamento || '';
                        const status = parcela.status || 'pendente';
                        const statusClass = status === 'pago' ? 'status-badge--green' : (status === 'atrasado' ? 'status-badge--red' : 'status-badge--amber');
                        const pagamentoDisabled = status !== 'pago' ? 'disabled' : '';
                        const pagamentoRequired = status === 'pago' ? 'required' : '';

                        return `
                            <tr>
                                <td>${index + 1}/${parcelasExistentes.length}</td>
                                <td>
                                    <input type="date" name="parcelas[${index}][data_vencimento]" class="form-control form-control-sm" value="${vencimento}">
                                </td>
                                <td>
                                    <input type="date" name="parcelas[${index}][data_pagamento]" class="form-control form-control-sm parcela-data-pagamento" value="${dataPagamento}" ${pagamentoDisabled} ${pagamentoRequired}>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" value="R$ ${valor.toFixed(2).replace('.', ',')}" readonly>
                                </td>
                                <td>
                                    <select name="parcelas[${index}][status]" class="form-control form-control-sm parcela-status">
                                        <option value="pendente" ${status === 'pendente' ? 'selected' : ''}>Pendente</option>
                                        <option value="pago" ${status === 'pago' ? 'selected' : ''}>Pago</option>
                                    </select>
                                </td>
                            </tr>
                        `;
                    });

                    preview.innerHTML = existingRows.join('');
                    return;
                }

                preview.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Informe a data inicial e a quantidade de parcelas.</td></tr>';
                return;
            }

            const rows = [];
            for (let i = 0; i < total; i++) {
                const vencimento = addMonths(dataBase, i);
                const parcelaExistente = parcelasExistentes[i] || {};
                const statusAtual = parcelaExistente.status || 'pendente';
                const pagamentoAtual = parcelaExistente.data_pagamento || '';
                const pagamentoDisabled = statusAtual !== 'pago' ? 'disabled' : '';
                const pagamentoRequired = statusAtual === 'pago' ? 'required' : '';
                rows.push(`
                    <tr>
                        <td>${i + 1}/${total}</td>
                        <td>
                            <input type="date" name="parcelas[${i}][data_vencimento]" class="form-control form-control-sm" value="${parcelaExistente.data_vencimento || toInputDate(vencimento)}">
                        </td>
                        <td>
                            <input type="date" name="parcelas[${i}][data_pagamento]" class="form-control form-control-sm parcela-data-pagamento" value="${pagamentoAtual}" ${pagamentoDisabled} ${pagamentoRequired}>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" value="R$ ${valor.toFixed(2).replace('.', ',')}" readonly>
                        </td>
                        <td>
                            <select name="parcelas[${i}][status]" class="form-control form-control-sm parcela-status">
                                <option value="pendente" ${statusAtual === 'pendente' ? 'selected' : ''}>Pendente</option>
                                <option value="pago" ${statusAtual === 'pago' ? 'selected' : ''}>Pago</option>
                            </select>
                        </td>
                    </tr>
                `);
            }

            preview.innerHTML = rows.join('');
        }

        gerarBtn.addEventListener('click', renderPreview);
        parcelasInput.addEventListener('input', renderPreview);
        dataInput.addEventListener('change', renderPreview);
        valorInput.addEventListener('input', renderPreview);
        preview.addEventListener('change', function (event) {
            if (!event.target.classList.contains('parcela-status')) {
                return;
            }

            const row = event.target.closest('tr');
            const pagamentoInput = row ? row.querySelector('.parcela-data-pagamento') : null;

            if (!pagamentoInput) {
                return;
            }

            if (event.target.value === 'pago') {
                pagamentoInput.disabled = false;
                pagamentoInput.required = true;
                if (!pagamentoInput.value) {
                    pagamentoInput.value = toInputDate(new Date());
                }
            } else {
                pagamentoInput.required = false;
                pagamentoInput.disabled = true;
                pagamentoInput.value = '';
            }
        });
        renderPreview();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/documentacao-form.blade.php ENDPATH**/ ?>