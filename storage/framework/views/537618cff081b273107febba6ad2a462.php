

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
<div class="container-fluid px-0 vehicle-show-page">
<?php
    $pendenciasCadastro = [];
    if (empty($veiculo->local_disponibilidade)) $pendenciasCadastro[] = 'Estação de origem';
    if (empty($veiculo->data_hora_disponibilidade)) $pendenciasCadastro[] = 'Data inicial de disponibilidade';
?>

<?php
    $temPendenciaDisponibilidade = empty($veiculo->local_disponibilidade) || empty($veiculo->data_hora_disponibilidade);
?>

    
    <div class="module-card mb-3">
        <div class="module-card-header vehicle-hero-compact">
            <div class="module-header-left">
                <div class="module-header-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Frota &bull; Detalhes do veÃ­culo</div>
                    <h1 class="module-header-title"><?php echo e($veiculo->nome); ?></h1>
                    <p class="module-header-sub">
                        <strong><?php echo e($veiculo->placa_licenca ?? '-'); ?></strong>
                        &bull; <?php echo e(optional($veiculo->marca)->nome ?? '-'); ?>

                        <?php echo e(optional($veiculo->modelo)->nome ?? '-'); ?>

                        &bull; <?php echo e($veiculo->ano_fabricacao ?? '-'); ?>/<?php echo e($veiculo->modelo?->ano_modelo ?? '-'); ?>

                    </p>
                </div>
            </div>

            <div class="module-header-right">
                <a href="<?php echo e(route('veiculo.edit', $veiculo->id)); ?>" class="btn-xp btn-xp--sm">
                    <i class="fas fa-pen"></i> Editar
                </a>
                <a href="<?php echo e(route('veiculo.documentacao')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm" target="_blank" rel="noopener">
                    <i class="fas fa-file-alt"></i> Documentação
                </a>
                <a href="<?php echo e(route('veiculo.index')); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </div>

    
    <div class="vehicle-status-strip mb-3">
        <div class="vehicle-status-item">
            <span>Status</span>
            <strong><?php echo e(optional($veiculo->statusVeiculo)->nome ?? 'NÃ£o informado'); ?></strong>
        </div>
        <div class="vehicle-status-item">
            <span>Disponibilidade</span>
            <strong class="<?php echo e($veiculo->disponivel ? 'text-success' : 'text-danger'); ?>">
                <i class="fas <?php echo e($veiculo->disponivel ? 'fa-check-circle' : 'fa-times-circle'); ?> mr-1"></i>
                <?php echo e($veiculo->disponivel ? 'Disponível' : 'Indisponível'); ?>

            </strong>
        </div>
        <div class="vehicle-status-item">
            <span>Quilometragem</span>
            <strong><?php echo e(number_format($veiculo->kms ?? 0, 0, ',', '.')); ?> km</strong>
        </div>
        <div class="vehicle-status-item">
            <span>Local atual</span>
            <strong><?php echo e(optional($veiculo->localDisponibilidade)->nome ?? 'Não informado'); ?></strong>
        </div>
        <div class="vehicle-status-item">
            <span>Próxima manutenção</span>
            <strong><?php echo e($veiculo->data_proxima_manutencao ? \Carbon\Carbon::parse($veiculo->data_proxima_manutencao)->format('d/m/Y') : 'Não informada'); ?></strong>
        </div>
        
        <?php if($pendenciasCadastro): ?>
        <div class="vehicle-status-item vehicle-status-item--alerts">
            <span>Pendências de cadastro</span>
            <?php if(count($pendenciasCadastro) > 0 ): ?>
            <button type="button" id="btnAbrirModalDisponibilidadeVeiculo" class="btn-xp btn-xp--warning btn-xp--sm btn-pendencias-cadastro">
                <i class="fas fa-map-marked-alt"></i>
                <span class="text-white">Regularizar cadastro</span>
                
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="vehicle-action-bar mb-3">
        <a target="_blank" href="<?php echo e(route('veiculo.edit', $veiculo->id)); ?>" class="vehicle-action vehicle-action--primary">
            <i class="fas fa-pen"></i><span>Editar veículos</span>
        </a>
        <a target="_blank" href="<?php echo e(route('movimentacao.create', ['veiculo_id' => $veiculo->id, 'tipo_movimentacao_id' => 10])); ?>" class="vehicle-action">
            <i class="fas fa-route"></i><span>Transferir estação</span>
        </a>
        <a target="_blank" href="<?php echo e(route('ordem-servico.create', ['veiculo_id' => $veiculo->id])); ?>" class="vehicle-action">
            <i class="fas fa-tools"></i><span>Abrir OS</span>
        </a>
        <a target="_blank" href="<?php echo e(route('sinistro.create', ['veiculo_id' => $veiculo->id])); ?>" class="vehicle-action vehicle-action--danger">
            <i class="fas fa-car-crash"></i><span>Registrar sinistro</span>
        </a>
        <a target="_blank" href="<?php echo e(route('multa.create', ['veiculo_id' => $veiculo->id])); ?>" class="vehicle-action">
            <i class="fas fa-exclamation-triangle"></i><span>Cadastrar multa</span>
        </a>
        <a target="_blank" href="<?php echo e(route('veiculo-dano.monitoramento', ['filtro' => 'pendentes'])); ?>" class="vehicle-action">
            <i class="fas fa-shield-alt"></i><span>Ver avarias</span>
        </a>
    </div>


    <?php if($temPendenciaDisponibilidade): ?>
    <div class="modal fade" id="modalDisponibilidadeVeiculo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content vehicle-modal vehicle-modal--alert">
                <div class="modal-header vehicle-modal__header border-bottom-0">
                    <div class="d-flex align-items-start">
                        <div class="vehicle-modal__icon mr-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <div class="vehicle-modal__eyebrow">Pendências de cadastro</div>
                            <h5 class="modal-title mb-1 vehicle-modal__title">Informar local e data de disponibilidade</h5>
                            <small class="vehicle-modal__subtitle">Complete os campos abaixo para liberar o fluxo do veículo com segurança.</small>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formDisponibilidadeVeiculo" method="POST" action="<?php echo e(route('veiculo.adicionarDisponibilidade', $veiculo->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body vehicle-modal__body p-4">
                        <div id="disponibilidadeModalAlert" class="alert d-none mb-3" role="alert"></div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="modal_local_disponibilidade" class="vehicle-modal__label">Estação de origem</label>
                                    <select id="modal_local_disponibilidade" name="local_disponibilidade" class="form-control vehicle-modal__control" required>
                                        <option value="">Selecione</option>
                                        <?php $__currentLoopData = ($dispEstacoes ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estacaoId => $estacaoNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($estacaoId); ?>"><?php echo e($estacaoNome); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="modal_data_hora_disponibilidade" class="vehicle-modal__label">Data inicial</label>
                                    <input type="datetime-local" id="modal_data_hora_disponibilidade" name="data_hora_disponibilidade" class="form-control vehicle-modal__control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer vehicle-modal__footer border-top-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-xp btn-xp--ghost btn-xp--sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-xp btn-xp--sm" id="btnSalvarDisponibilidadeVeiculo">
                            <i class="fas fa-save"></i> <span>Salvar disponibilidade</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="config-card vehicle-info-tabs mb-3">
        <div class="vehicle-tabs-nav" role="tablist">
            <button class="vehicle-tab-button is-active" type="button" data-vehicle-tab="overview">
                <i class="fas fa-eye"></i> Visão geral
            </button>
            <button class="vehicle-tab-button" type="button" data-vehicle-tab="registration">
                <i class="fas fa-id-card"></i> Cadastro
            </button>
            <button class="vehicle-tab-button" type="button" data-vehicle-tab="financial">
                <i class="fas fa-chart-line"></i> Financeiro
            </button>
            <button class="vehicle-tab-button" type="button" data-vehicle-tab="fipe">
                <i class="fas fa-coins"></i> FIPE e depreciação
            </button>
        </div>

        <div class="vehicle-tab-panel is-active" data-vehicle-tab-panel="overview">
            <div class="veiculo-grid veiculo-grid--compact">
                <div class="veiculo-field"><span class="veiculo-field__label">Placa</span><span class="veiculo-field__value"><?php echo e($veiculo->placa_licenca ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Marca / Modelo</span><span class="veiculo-field__value"><?php echo e(optional($veiculo->marca)->nome ?? '-'); ?> / <?php echo e(optional($veiculo->modelo)->nome ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Ano</span><span class="veiculo-field__value"><?php echo e($veiculo->ano_fabricacao ?? '-'); ?>/<?php echo e($veiculo->modelo?->ano_modelo ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Cor</span><span class="veiculo-field__value"><?php echo e($veiculo->cor ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Tipo de frota</span><span class="veiculo-field__value"><?php echo e(optional($veiculo->tipoFrota)->nome ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Grupo de frota</span><span class="veiculo-field__value"><?php echo e(optional($veiculo->grupoFrota)->nome ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Próxima revisão</span><span class="veiculo-field__value"><?php echo e($veiculo->proxima_revisao_kms ? number_format($veiculo->proxima_revisao_kms, 0, ',', '.') . ' km' : '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Disponí   vel a partir de</span><span class="veiculo-field__value"><?php echo e($veiculo->data_hora_disponibilidade ? \Carbon\Carbon::parse($veiculo->data_hora_disponibilidade)->format('d/m/Y H:i') : '-'); ?></span></div>
            </div>
        </div>

        <div class="vehicle-tab-panel" data-vehicle-tab-panel="registration">
            <div class="veiculo-grid veiculo-grid--compact">
                <div class="veiculo-field"><span class="veiculo-field__label">Nome</span><span class="veiculo-field__value"><?php echo e($veiculo->nome ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Chassi</span><span class="veiculo-field__value"><?php echo e($veiculo->chassi ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">RENAVAM</span><span class="veiculo-field__value"><?php echo e($veiculo->renavam ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">NÂº do motor</span><span class="veiculo-field__value"><?php echo e($veiculo->numero_motor ?? '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Data de compra</span><span id="fipeResumoDataCompra" class="veiculo-field__value"><?php echo e($veiculo->data_compra ? \Carbon\Carbon::parse($veiculo->data_compra)->format('d/m/Y') : '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Validade da placa</span><span class="veiculo-field__value"><?php echo e($veiculo->data_placa_licenca ? \Carbon\Carbon::parse($veiculo->data_placa_licenca)->format('d/m/Y') : '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Próxima inspeção</span><span class="veiculo-field__value"><?php echo e($veiculo->data_proxima_inspecao ? \Carbon\Carbon::parse($veiculo->data_proxima_inspecao)->format('d/m/Y') : '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Situação na frota</span><span class="veiculo-field__value"><?php echo e($veiculo->vendido ? 'Vendido' : 'Em frota'); ?></span></div>
            </div>
        </div>

        <div class="vehicle-tab-panel" data-vehicle-tab-panel="financial">
            <div class="veiculo-grid veiculo-grid--compact">
                <div class="veiculo-field"><span class="veiculo-field__label">Receita total</span><span class="veiculo-field__value text-success">R$ <?php echo e(number_format($finReceita, 2, ',', '.')); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Custos com multas</span><span class="veiculo-field__value <?php echo e($finCustoMultas > 0 ? 'text-danger' : ''); ?>">R$ <?php echo e(number_format($finCustoMultas, 2, ',', '.')); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Custos com sinistros</span><span class="veiculo-field__value <?php echo e($finCustoSinistros > 0 ? 'text-danger' : ''); ?>">R$ <?php echo e(number_format($finCustoSinistros, 2, ',', '.')); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Lucro líquido</span><span class="veiculo-field__value <?php echo e($finLucroLiquido >= 0 ? 'text-success' : 'text-danger'); ?>">R$ <?php echo e(number_format($finLucroLiquido, 2, ',', '.')); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Rentabilidade</span><span class="veiculo-field__value"><?php echo e($finRentabilidade !== null ? number_format($finRentabilidade, 2, ',', '.') . '%' : '-'); ?></span></div>
                <div class="veiculo-field"><span class="veiculo-field__label">Payback</span><span class="veiculo-field__value"><?php echo e($finPaybackMeses !== null ? $finPaybackMeses . ' meses' : '-'); ?></span></div>
            </div>
        </div>

        <div class="vehicle-tab-panel" data-vehicle-tab-panel="fipe">
            <div class="vehicle-summary-block">
                
                <div class="veiculo-grid veiculo-grid--compact">
                    <div class="veiculo-field"><span class="veiculo-field__label">Valor de compra</span><span id="fipeResumoValorCompra" class="veiculo-field__value"><?php echo e($veiculo->valor_compra !== null ? 'R$ ' . number_format((float) $veiculo->valor_compra, 2, ',', '.') : '-'); ?></span></div>
                    <div class="veiculo-field"><span class="veiculo-field__label">Valor FIPE</span><span id="fipeResumoValor" class="veiculo-field__value"><?php echo e($veiculo->valor_fipe !== null ? 'R$ ' . number_format((float) $veiculo->valor_fipe, 2, ',', '.') : '-'); ?></span></div>
                    <div class="veiculo-field"><span class="veiculo-field__label">Valor estimado atual</span><span id="fipeResumoValorAtual" class="veiculo-field__value"><?php echo e($veiculo->valor_atual_estimado !== null ? 'R$ ' . number_format((float) $veiculo->valor_atual_estimado, 2, ',', '.') : '-'); ?></span></div>
                    <div class="veiculo-field"><span class="veiculo-field__label">Depreciação acumulada</span><span id="fipeResumoDepreciacao" class="veiculo-field__value"><?php echo e($veiculo->valor_depreciacao_acumulada !== null ? 'R$ ' . number_format((float) $veiculo->valor_depreciacao_acumulada, 2, ',', '.') : '-'); ?></span></div>
                    <div class="veiculo-field"><span class="veiculo-field__label">Taxa anual</span><span id="fipeResumoTaxa" class="veiculo-field__value"><?php echo e($veiculo->taxa_depreciacao_anual !== null ? number_format((float) $veiculo->taxa_depreciacao_anual, 2, ',', '.') . '% a.a.' : '-'); ?></span></div>
                    <div class="veiculo-field">
                        <span class="veiculo-field__label">Última consulta</span>
                        <span id="fipeResumoData" class="veiculo-field__value">
                            <?php echo e($veiculo->data_consulta_fipe ? \Carbon\Carbon::parse($veiculo->data_consulta_fipe)->format('d/m/Y') : '-'); ?>

                        </span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" id="btnExecutarConsultaFipe">
                            <i class="fas fa-sync-alt mr-1"></i> Consultar/atualizar FIPE
                        </button>
                      
                        <button type="button" class="btn btn-outline-primary  btn-sm" data-toggle="modal" data-target="#modalConfigurarDepreciacaoVeiculo">
                            <i class="fas fa-sliders-h mr-1"></i> Configurar depreciação
                        </button>
                    </div>
                    <div>
                    </div>
                </div>
            </div>

            <div class="vehicle-summary-actions">
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConsultarFipeVeiculo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom-0" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <div>
                        <h5 class="modal-title mb-1">Consultar e atualizar FIPE</h5>
                        <small class="text-muted">Busca os dados atuais da tabela FIPE e atualiza o resumo do veÃ­culo.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Atual</strong><br>
                        Marca: <?php echo e(optional($veiculo->marca)->nome ?? '-'); ?><br>
                        Modelo: <?php echo e(optional($veiculo->modelo)->nome ?? '-'); ?><br>
                        Ano modelo: <?php echo e($veiculo->modelo?->ano_modelo ?? '-'); ?>

                    </div>

                    <div id="fipeModalAlert" class="alert d-none" role="alert"></div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="veiculo-field mb-3">
                                <span class="veiculo-field__label">Valor FIPE</span>
                                <div id="fipeModalValor" class="veiculo-field__value">-</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="veiculo-field mb-3">
                                <span class="veiculo-field__label">Código FIPE</span>
                                <div id="fipeModalCodigo" class="veiculo-field__value"><?php echo e($veiculo->codigo_fipe ?? '-'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="veiculo-field mb-3">
                                <span class="veiculo-field__label">Última consulta</span>
                                <div id="fipeModalData" class="veiculo-field__value"><?php echo e($veiculo->data_consulta_fipe ? \Carbon\Carbon::parse($veiculo->data_consulta_fipe)->format('d/m/Y') : '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnExecutarConsultaFipeModal">
                        <i class="fas fa-sync-alt mr-1"></i> Consultar agora
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfigurarDepreciacaoVeiculo" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content vehicle-modal vehicle-modal--alert">
                <form id="formDepreciacaoVeiculo" method="POST" action="<?php echo e(route('veiculo.alterardepreciacao', $veiculo->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header vehicle-modal__header border-bottom-0">
                        <div class="d-flex align-items-start">
                            <div class="vehicle-modal__icon mr-3">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div>
                                <div class="vehicle-modal__eyebrow">FIPE e depreciação</div>
                                <h5 class="modal-title mb-1 vehicle-modal__title">Configurar depreciação</h5>
                                <small class="vehicle-modal__subtitle">Ajuste os dados que alimentam o cálculo do valor estimado.</small>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body vehicle-modal__body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="depr_valor_compra" class="vehicle-modal__label">Valor de compra</label>
                                    <input type="text" class="form-control vehicle-modal__control" id="depr_valor_compra" name="valor_compra" value="<?php echo e(old('valor_compra', $veiculo->valor_compra)); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="depr_data_compra" class="vehicle-modal__label">Data de compra</label>
                                    <input type="date" class="form-control vehicle-modal__control" id="depr_data_compra" name="data_compra" value="<?php echo e(old('data_compra', $veiculo->data_compra ? \Carbon\Carbon::parse($veiculo->data_compra)->format('Y-m-d') : '')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="depr_taxa" class="vehicle-modal__label">Taxa anual de depreciação (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control vehicle-modal__control" id="depr_taxa" name="taxa_depreciacao_anual" value="<?php echo e(old('taxa_depreciacao_anual', $veiculo->taxa_depreciacao_anual ?? 15)); ?>">
                        </div>
                    </div>
                    <div class="modal-footer vehicle-modal__footer border-top-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-xp btn-xp--ghost btn-xp--sm" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-xp btn-xp--sm">
                            <i class="fas fa-save mr-1"></i> Salvar configuração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        
    <?php echo $__env->make('veiculo.components.operacional.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    :root {
        --c-ink:    #0c1428;
        --c-navy:   #1a2848;
        --c-indigo: #4338ca;
        --c-green:  #10b981;
        --c-amber:  #f59e0b;
        --c-blue:   #0ea5e9;
        --c-red:    #ef4444;
        --c-slate:  #64748b;
        --c-border: #e2e9f4;
        --c-bg:     #f2f5fb;
    }

    /* â”€â”€ Hero â”€â”€ */
    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12,20,40,.10);
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
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }
    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
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
        width: 52px; height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; color: #60a5fa;
        flex-shrink: 0; backdrop-filter: blur(8px);
    }
    .module-header-eyebrow {
        font-size: .62rem; font-weight: 700;
        letter-spacing: .18em; text-transform: uppercase;
        color: rgba(148,163,184,.8); display: block; margin-bottom: 3px;
    }
    .module-header-title {
        font-size: 1.45rem; font-weight: 800;
        color: #fff; margin: 0 0 3px; letter-spacing: -.02em;
    }
    .module-header-sub {
        margin: 0; font-size: .82rem; color: rgba(148,163,184,.9);
    }
    .module-header-right { position: relative; z-index: 1; display: flex; gap: 8px; }

    /* â”€â”€ Config cards & blocks â”€â”€ */
    .config-card {
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(12,20,40,.06);
    }
    .config-block {
        padding: 18px 20px;
        border-left: 3px solid var(--c-indigo);
        border-bottom: 1px solid #f0f4fc;
    }
    .config-block:last-child { border-bottom: none; }
    .config-block-label {
        display: flex; align-items: flex-start;
        gap: 10px; margin-bottom: 14px;
        padding-bottom: 12px; border-bottom: 1px dashed #e5eaf3;
    }
    .config-block-label > i {
        width: 30px; height: 30px; border-radius: 9px;
        background: #eef2ff; color: var(--c-indigo);
        display: flex; align-items: center; justify-content: center;
        font-size: .78rem; flex-shrink: 0;
    }
    .config-block-label strong { display: block; color: #0c1428; font-size: .86rem; font-weight: 700; }
    .config-block-label span   { color: #64748b; font-size: .73rem; display: block; margin-top: 1px; }

    /* â”€â”€ Veiculo grid â”€â”€ */
    .veiculo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .veiculo-field {
        background: #f8fafc;
        border: 1px solid #e9f0fa;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .veiculo-field__label {
        display: block;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .veiculo-field__value {
        display: block;
        font-size: .88rem;
        font-weight: 600;
        color: #0c1428;
    }

    /* â”€â”€ Summary â”€â”€ */
    .veiculo-summary { display: flex; flex-direction: column; gap: 8px; }
    .veiculo-summary__row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f8fafc;
        border-radius: 9px;
        font-size: .84rem;
    }
    .veiculo-summary__row span { color: #475569; }
    .veiculo-summary__row strong { font-size: 1rem; }

    /* â”€â”€ ações rápidas grid â”€â”€ */
    .veiculo-acoes-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 4px;
    }
    .veiculo-acao {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 12px 8px;
        border-radius: 10px;
        border: 1px solid #e2e9f4;
        background: #f8fafd;
        color: #1a2848;
        font-size: .75rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: background .15s, box-shadow .15s, transform .12s;
        cursor: pointer;
    }
    .veiculo-acao i { font-size: 1.1rem; color: var(--c-indigo); }
    .veiculo-acao:hover {
        background: #eef2fb;
        box-shadow: 0 2px 8px rgba(67,56,202,.10);
        transform: translateY(-1px);
        color: #1a2848;
        text-decoration: none;
    }
    .veiculo-acao--primary {
        background: linear-gradient(135deg, #1a2848, #2e4080);
        border-color: transparent;
        color: #fff;
    }
    .veiculo-acao--primary i { color: #93c5fd; }
    .veiculo-acao--primary:hover { color: #fff; background: linear-gradient(135deg, #0c1428, #1a2848); }
    .veiculo-acao--danger { border-color: #fecaca; background: #fff5f5; }
    .veiculo-acao--danger i { color: #ef4444; }
    .veiculo-acao--danger:hover { background: #fee2e2; }

    /* â”€â”€ Sinistro items â”€â”€ */
    .veiculo-sinistro-item {
        padding: .5rem 0;
        border-bottom: 1px dashed #e5eaf3;
    }
    .veiculo-sinistro-item:last-child { border-bottom: none; }
    .veiculo-sinistro-item__head { display: flex; align-items: center; margin-bottom: 2px; }
    .veiculo-sinistro-item__body { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .veiculo-sinistro-item__tipo { font-size: .8rem; font-weight: 600; color: #333; }

    /* â”€â”€ Reserva items â”€â”€ */
    .veiculo-reserva-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px dashed #e5eaf3;
    }
    .veiculo-reserva-item:last-child { border-bottom: none; }
    .veiculo-reserva-item__id { flex-shrink: 0; }
    .veiculo-reserva-item__info { flex: 1; }
    .veiculo-reserva-item__info span { display: block; font-size: .82rem; font-weight: 600; color: #0c1428; }
    .veiculo-reserva-item__info small { color: #64748b; font-size: .72rem; }

    /* â”€â”€ Buttons â”€â”€ */
    .btn-xp {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        color: #fff; border: none; border-radius: 10px;
        padding: 9px 16px; font-size: .82rem; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: all .18s; letter-spacing: -.01em;
        box-shadow: 0 4px 12px rgba(67,56,202,.25);
    }
    .btn-xp:hover { opacity: .88; transform: translateY(-1px); color: #fff; text-decoration: none; }
    .btn-xp--sm { padding: 6px 12px; font-size: .78rem; border-radius: 8px; }
    .btn-xp--outline {
        background: transparent;
        color: var(--c-indigo);
        border: 1.5px solid var(--c-indigo);
        box-shadow: none;
    }
    .btn-xp--outline:hover { background: #eef2ff; color: var(--c-indigo); }
    .btn-xp--outline-light {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.25);
        color: rgba(255,255,255,.9);
        box-shadow: none;
    }
    .btn-xp--outline-light:hover { background: rgba(255,255,255,.2); color: #fff; }
    .btn-xp--warning {
        background: linear-gradient(135deg, #f59e0b, #f97316);
        box-shadow: 0 10px 20px rgba(245, 158, 11, .22);
    }
    .btn-xp--warning:hover { color: #fff; background: linear-gradient(135deg, #d97706, #ea580c); }
    .btn-xp--ghost {
        background: #fff;
        color: var(--c-slate);
        border: 1px solid var(--c-border);
        box-shadow: none;
    }
    .btn-xp--ghost:hover { background: #f8fafc; color: var(--c-ink); }
    .btn-pendencias-cadastro {
        padding-left: 14px;
        padding-right: 14px;
        gap: 8px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
    }
    .btn-pendencias-cadastro__count {
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: rgba(255,255,255,.18);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 800;
    }
    .vehicle-modal {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(12,20,40,.18);
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
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.16);
        color: #fbbf24;
        flex-shrink: 0;
    }
    .vehicle-modal__eyebrow {
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(226,232,240,.78);
        margin-bottom: 4px;
    }
    .vehicle-modal__title {
        color: #fff;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .vehicle-modal__subtitle {
        color: rgba(226,232,240,.82);
    }
    .vehicle-modal__body {
        background: #fff;
        padding-top: 20px !important;
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
        border-color: rgba(67,56,202,.45);
        box-shadow: 0 0 0 .2rem rgba(67,56,202,.10);
    }
    .vehicle-pending-summary {
        background: linear-gradient(180deg, #fff8eb 0%, #fff 100%);
        border: 1px solid #fde3b0;
        border-radius: 16px;
        padding: 14px 16px;
    }
    .vehicle-pending-summary__label {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #92400e;
        margin-bottom: 10px;
    }
    .vehicle-pending-summary__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .vehicle-pending-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff;
        border: 1px solid #f8cc7c;
        color: #7c2d12;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: .8rem;
        font-weight: 700;
    }
    .vehicle-pending-chip i {
        font-size: .45rem;
        color: #f59e0b;
    }
    .w-100 { width: 100%; justify-content: center; }

    /* â”€â”€ Status badges â”€â”€ */
    .status-badge {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 999px;
        font-size: .73rem; font-weight: 700; letter-spacing: .02em;
    }
    .status-badge--green  { background: #dcfce7; color: #15803d; }
    .status-badge--amber  { background: #fef3c7; color: #92400e; }
    .status-badge--blue   { background: #dbeafe; color: #1e40af; }
    .status-badge--gray   { background: #f1f5f9; color: #475569; }
    .status-badge--red    { background: #fee2e2; color: #991b1b; }
    .status-badge--orange { background: #ffedd5; color: #9a3412; }
    .status-badge--purple { background: #ede9fe; color: #6d28d9; }
    .status-badge--teal   { background: #ccfbf1; color: #115e59; }

    /* â”€â”€ Table â”€â”€ */
    .xp-table { font-size: .82rem; }
    .xp-table thead th {
        background: #f0f4ff;
        color: #3d4f63;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        border-bottom: 2px solid #dde8ff;
        padding: 10px 12px;
    }
    .xp-table tbody td { padding: 9px 12px; vertical-align: middle; border-color: #f0f4fc; }
    .xp-id { font-weight: 700; color: var(--c-indigo); font-size: .8rem; }
    .xp-link { color: var(--c-indigo); font-weight: 600; text-decoration: none; }
    .xp-link:hover { color: #3730a3; text-decoration: underline; }
    .obs-cell { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* â”€â”€ Empty state â”€â”€ */
    .empty-state {
        text-align: center; padding: 28px 16px;
        color: #94a3b8;
    }
    .empty-state i { font-size: 2rem; display: block; margin-bottom: 8px; }
    .empty-state p { margin: 0; font-size: .85rem; }

    /* -- Disponibilidade (show veiculo) -- */
    .vd__filters { margin-top: 8px; }
    .vd__filters-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: end;
        margin-bottom: 10px;
    }
    .vd__filters-item { min-width: 160px; }
    .vd__filters-actions { display: flex; gap: 8px; }
    .vd__label {
        display: block;
        font-size: .65rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .vd__summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        margin: 10px 0 10px;
    }
    .vd__kpi {
        background: #f8fafc;
        border: 1px solid #e9f0fa;
        border-radius: 12px;
        padding: 10px 12px;
    }
    .vd__kpi-label {
        font-size: .64rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .vd__kpi-value {
        font-size: .9rem;
        font-weight: 800;
        color: #0c1428;
    }
    .vd__note {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: .85rem;
        font-weight: 600;
        margin: 10px 0 10px;
    }
    .vd__legend {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin: 10px 0 10px;
    }
    .vd__legend-btn {
        border: 1px solid #dbe3ee;
        background: #f8fbff;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: .78rem;
        font-weight: 800;
        color: #1f334d;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .vd__legend-btn.is-active {
        border-color: var(--c-indigo);
        box-shadow: 0 6px 16px rgba(67,56,202,.12);
    }
    .vd__weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin: 8px 0 8px;
        font-size: .7rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vd__weekdays > div { text-align: center; }

    .vd__calendar {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }
    .vd__day {
        background: #ffffff;
        border: 1px solid #e9f0fa;
        border-radius: 12px;
        min-height: 68px;
        padding: 8px 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        position: relative;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .vd__day:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(12,20,40,.08);
        border-color: #c7d6f1;
        text-decoration: none;
        color: inherit;
    }
    .vd__day.is-empty { background: transparent; border: 0; box-shadow: none; }
    .vd__day.is-hidden { display: none; }
    .vd__day.is-today { outline: 2px solid rgba(14,165,233,.35); }
    .vd__num { font-weight: 900; color: #0c1428; }

    .vd__dot {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #fff;
        align-self: flex-start;
    }
    .vd__dot.is-ok { background: #16a34a; }
    .vd__dot.is-off { background: #dc2626; }
    .vd__dot.is-reserved { background: #d97706; }
    .vd__dot.is-pending { background: #64748b; }

    .vd__lists { margin-top: 12px; }
    .vd__details {
        border: 1px solid #e9f0fa;
        border-radius: 14px;
        padding: 10px 12px;
        background: #fff;
        margin-bottom: 10px;
    }
    .vd__details > summary {
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        user-select: none;
    }

    /* â”€â”€ Steps operacionais â”€â”€ */
    .vs-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border: 1px solid var(--c-border);
        border-radius: 22px;
        padding: 18px;
        box-shadow: 0 10px 28px rgba(12,20,40,.07);
        overflow: hidden;
    }
    .vs-shell__intro {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }
    .vs-shell__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vs-shell__title {
        margin: 0 0 6px;
        font-size: 1.12rem;
        font-weight: 800;
        color: #0c1428;
    }
    .vs-shell__sub {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        max-width: 680px;
    }
    .vs-shell__intro-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
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
    .vs-badge-soft.is-success {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #166534;
    }
    .vs-badge-soft.is-warning {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #9a3412;
    }

    .vs-steps {
        display: grid;
        grid-template-columns: repeat(6, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .vs-step {
        position: relative;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 18px;
        padding: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        min-height: 82px;
        text-align: left;
        transition: all .18s ease;
        box-shadow: 0 5px 14px rgba(15,23,42,.05);
    }
    .vs-step:hover {
        transform: translateY(-1px);
        border-color: #c7d2fe;
        box-shadow: 0 12px 26px rgba(67,56,202,.10);
    }
    .vs-step.is-active {
        background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
        border-color: #4338ca;
        box-shadow: 0 14px 28px rgba(67,56,202,.16);
    }
    .vs-step__icon {
        width: 26px;
        height: 26px;
        border-radius: 14px;
        background: #eef2ff;
        color: #4338ca;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
    }
    .vs-step__content {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .vs-step__content strong {
        color: #0c1428;
        font-size: .86rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .vs-step__content small {
        margin-top: 3px;
        color: #64748b;
        font-size: .71rem;
        line-height: 1.25;
    }
    .vs-step__count {
        margin-left: auto;
        min-width: 26px;
        height: 26px;
        padding: 0 8px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        color: #0f172a;
        font-size: .65rem;
        font-weight: 800;
    }
    .vs-step__count--alert {
        background: #fee2e2;
        color: #b91c1c;
    }
    .vs-step__count--warn {
        background: #ffedd5;
        color: #c2410c;
    }
    .vs-step__count--success {
        background: #dcfce7;
        color: #166534;
    }
    .vs-step__count--muted {
        background: #e2e8f0;
        color: #0f172a;
    }

    .vs-panel {
        display: none;
        border: 1px solid #e7edf8;
        border-radius: 20px;
        background: #fff;
        padding: 18px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.4);
    }
    .vs-panel.is-active {
        display: block;
        animation: fadeVsPanel .18s ease;
    }
    .vs-chart-card,
    .vs-metric-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }
    .vs-chart-card {
        padding: 1rem;
        height: 100%;
    }
    .vs-chart-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .75rem;
    }
    .vs-chart-card__head strong {
        display: block;
        font-size: 1rem;
        color: #0f172a;
    }
    .vs-chart-card__head small,
    .vs-metric-box small {
        display: block;
        color: #64748b;
        margin-top: .2rem;
    }
    .vs-chart-wrap {
        position: relative;
        min-height: 240px;
    }
    .vs-metrics-stack {
        display: grid;
        gap: .75rem;
    }
    .vs-metric-box {
        padding: .95rem 1rem;
    }
    .vs-metric-box__label {
        display: block;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        margin-bottom: .35rem;
        font-weight: 700;
    }
    .vs-metric-box strong {
        display: block;
        font-size: 1.25rem;
        color: #0f172a;
    }
    @keyframes fadeVsPanel {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .vs-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 16px;
    }
    .vs-panel-head__eyebrow {
        display: inline-block;
        margin-bottom: 5px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: #6366f1;
    }
    .vs-panel-head__title {
        margin: 0 0 5px;
        font-size: 1rem;
        font-weight: 800;
        color: #0c1428;
    }
    .vs-panel-head__sub {
        margin: 0;
        color: #64748b;
        font-size: .82rem;
        max-width: 700px;
    }

    .vs-kpis {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .vs-kpi {
        min-width: 145px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 12px;
    }
    .vs-kpi span {
        display: block;
        margin-bottom: 4px;
        font-size: .64rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vs-kpi strong {
        display: block;
        color: #0c1428;
        font-size: .95rem;
        font-weight: 800;
    }

    .vs-info-banner {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        margin-bottom: 14px;
        border-radius: 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        font-size: .82rem;
        font-weight: 600;
    }

    .vs-filter-wrap {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 14px;
        margin-bottom: 16px;
    }
    .vs-filter-top {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
    }
    .vs-filter-search {
        min-width: 280px;
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        height: 46px;
        padding: 0 14px;
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(15,23,42,.04);
    }
    .vs-filter-search i {
        color: #64748b;
    }
    .vs-search-input {
        border: none;
        outline: none;
        background: transparent;
        width: 100%;
        font-size: .88rem;
        color: #0f172a;
    }
    .vs-filter-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    .vs-chip {
        border: 1px solid #dbe3ee;
        background: #fff;
        color: #334155;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: .76rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }
    .vs-chip:hover {
        border-color: #c7d2fe;
        color: #3730a3;
    }
    .vs-chip.is-active {
        background: #eef2ff;
        border-color: #4338ca;
        color: #4338ca;
        box-shadow: 0 8px 18px rgba(67,56,202,.10);
    }

    .vs-advanced-filters {
        display: none;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed #dbe3ee;
    }
    .vs-advanced-filters.is-open {
        display: block;
    }
    .vs-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .vs-filter-field label {
        display: block;
        margin-bottom: 5px;
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vt-input,
    .vt-select {
        width: 100%;
        min-height: 42px;
        border: 1px solid #dbe3ee;
        border-radius: 12px;
        background: #fff;
        padding: 10px 12px;
        font-size: .84rem;
        color: #0f172a;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .vt-input:focus,
    .vt-select:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }

    .vs-filter-field--check {
        display: flex;
        align-items: end;
    }
    .vs-check {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 42px;
        margin: 0;
        padding: 10px 12px;
        border: 1px solid #dbe3ee;
        border-radius: 12px;
        background: #fff;
        font-size: .82rem;
        color: #334155;
        width: 100%;
    }
    .vs-check input {
        margin: 0;
    }

    .vs-filter-footer {
        margin-top: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .vs-filter-result {
        font-size: .8rem;
        font-weight: 700;
        color: #475569;
    }
    .vs-filter-footer__actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .vt-btn-filter,
    .vt-btn-clear {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        cursor: pointer;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
        transition: all .18s ease;
    }
    .vt-btn-filter {
        background: linear-gradient(135deg, var(--c-indigo), #6366f1);
        color: #fff;
        box-shadow: 0 4px 12px rgba(67,56,202,.22);
    }
    .vt-btn-filter:hover {
        opacity: .9;
        transform: translateY(-1px);
    }
    .vt-btn-clear {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #dbe3ee;
    }
    .vt-btn-clear:hover {
        background: #e2e8f0;
    }

    .vt-panel-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .vs-row-hidden {
        display: none !important;
    }

    @media (max-width: 1199px) {
        .vs-steps {
            grid-template-columns: repeat(6, minmax(220px, 1fr));
        }
    }
    @media (max-width: 991px) {
        .module-card-header,
        .vs-shell__intro,
        .vs-panel-head {
            flex-direction: column;
            align-items: flex-start;
        }
        .module-header-right,
        .vs-shell__intro-badges,
        .vs-kpis {
            justify-content: flex-start;
        }
        .veiculo-acoes-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767px) {
        .config-block,
        .vs-shell,
        .vs-panel {
            padding: 14px;
        }
        .vs-filter-search {
            min-width: 100%;
        }
        .vs-kpi {
            min-width: calc(50% - 6px);
        }
        .vs-filter-grid {
            grid-template-columns: 1fr;
        }
    }



    /* ===== Layout compacto da página do veÃ­culo ===== */
    .vehicle-show-page { padding-bottom: 20px; }
    .vehicle-hero-compact { padding: 17px 22px; }
    .vehicle-hero-compact .module-header-icon { width: 44px; height: 44px; border-radius: 12px; }
    .vehicle-hero-compact .module-header-title { font-size: 1.25rem; }

    .vehicle-status-strip {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        background: #fff;
        border: 1px solid var(--c-border);
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(12,20,40,.05);
        overflow: hidden;
    }
    .vehicle-status-item { padding: 12px 14px; border-right: 1px solid #edf1f7; min-width: 0; }
    .vehicle-status-item:last-child { border-right: 0; }
    .vehicle-status-item span { display: block; font-size: .62rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
    .vehicle-status-item strong { display: block; font-size: .84rem; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .vehicle-status-item--alerts strong { color: #dc2626; font-size: 1rem; }

    .vehicle-action-bar {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
    }
    .vehicle-action {
        min-height: 54px;
        padding: 9px 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #dfe7f2;
        border-radius: 12px;
        background: #fff;
        color: #1e293b;
        font-size: .76rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(12,20,40,.04);
        transition: .16s ease;
    }
    .vehicle-action i { color: var(--c-indigo); font-size: .95rem; }
    .vehicle-action:hover { transform: translateY(-1px); border-color: #c7d2fe; color: #1e293b; text-decoration: none; box-shadow: 0 5px 14px rgba(67,56,202,.10); }
    .vehicle-action--primary { background: linear-gradient(135deg, #172554, #273c75); color: #fff; border-color: transparent; }
    .vehicle-action--primary i, .vehicle-action--primary:hover { color: #fff; }
    .vehicle-action--danger { border-color: #fecaca; background: #fff7f7; }
    .vehicle-action--danger i { color: #dc2626; }

    .vehicle-info-tabs { overflow: hidden; }
    .vehicle-tabs-nav { display: flex; gap: 4px; padding: 8px; background: #f8fafc; border-bottom: 1px solid #e8eef7; overflow-x: auto; }
    .vehicle-tab-button { border: 0; background: transparent; color: #64748b; padding: 9px 13px; border-radius: 9px; font-size: .78rem; font-weight: 700; white-space: nowrap; cursor: pointer; }
    .vehicle-tab-button i { margin-right: 5px; }
    .vehicle-tab-button.is-active { background: #fff; color: var(--c-indigo); box-shadow: 0 2px 8px rgba(15,23,42,.08); }
    .vehicle-tab-panel { display: none; padding: 13px 14px; }
    .vehicle-tab-panel.is-active { display: block; }
    .veiculo-grid--compact { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
    .veiculo-grid--compact .veiculo-field { min-height: 56px; padding: 8px 10px; }

    .vs-shell__intro { padding-top: 16px; }

    @media (max-width: 1199px) {
        .vehicle-status-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .vehicle-status-item:nth-child(3) { border-right: 0; }
        .vehicle-status-item:nth-child(-n+3) { border-bottom: 1px solid #edf1f7; }
        .vehicle-action-bar { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .veiculo-grid--compact { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .module-card-header, .vehicle-hero-compact { align-items: flex-start; flex-direction: column; }
        .module-header-right { width: 100%; }
        .module-header-right .btn-xp { flex: 1; justify-content: center; }
        .vehicle-status-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .vehicle-status-item { border-bottom: 1px solid #edf1f7; }
        .vehicle-status-item:nth-child(odd) { border-right: 1px solid #edf1f7; }
        .vehicle-status-item:nth-child(even) { border-right: 0; }
        .vehicle-action-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .veiculo-grid--compact { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 480px) {
        .vehicle-status-strip, .vehicle-action-bar, .veiculo-grid--compact { grid-template-columns: 1fr; }
        .vehicle-status-item { border-right: 0 !important; }
    }



    /* Operação compacta: reduz a sensação de duas navegações concorrentes */
    .vs-shell--compact {
        padding: 0;
        overflow: hidden;
    }

    .vs-compact-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        border-bottom: 1px solid #e7edf6;
        background: #fff;
    }

    .vs-compact-head .vs-shell__title {
        margin: 2px 0 0;
        font-size: 1rem;
    }

    .vs-steps--compact {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0;
        padding: 0;
        overflow: visible;
        border-bottom: 1px solid #e7edf6;
        background: #f8fafc;
    }

    .vs-steps--compact .vs-step {
        min-width: 0;
        min-height: 58px;
        border: 0;
        border-right: 1px solid #e7edf6;
        border-radius: 0;
        box-shadow: none;
        padding: 10px 12px;
    }

    .vs-steps--compact .vs-step:last-child {
        border-right: 0;
    }

    .vs-steps--compact .vs-step.is-active {
        background: #fff;
        box-shadow: inset 0 -3px 0 var(--c-indigo);
    }

    .vs-steps--compact .vs-step__content small {
        display: none;
    }

    .vs-shell--compact .vs-panel {
        border: 0;
        border-radius: 0;
        padding: 18px;
    }

    @media (max-width: 1199px) {
        .vs-steps--compact {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .vs-steps--compact .vs-step:nth-child(3n) {
            border-right: 0;
        }
    }

    @media (max-width: 767px) {
        .vs-compact-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .vs-steps--compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .vs-steps--compact .vs-step:nth-child(3n) {
            border-right: 1px solid #e7edf6;
        }

        .vs-steps--compact .vs-step:nth-child(2n) {
            border-right: 0;
        }
    }

</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var buttons = document.querySelectorAll('[data-vehicle-tab]');
        var panels = document.querySelectorAll('[data-vehicle-tab-panel]');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var tab = this.dataset.vehicleTab;

                buttons.forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.dataset.vehicleTabPanel === tab);
                });
            });
        });
    });

    (function () {
        function q(id) {
            return document.getElementById(id);
        }

        function normalize(value) {
            return (value || '').toString().toLowerCase().trim();
        }

        function contains(value, search) {
            search = normalize(search);
            if (!search) return true;
            return normalize(value).indexOf(search) !== -1;
        }

        function inDateRange(value, from, to) {
            if (!from && !to) return true;
            if (!value) return false;
            if (from && value < from) return false;
            if (to && value > to) return false;
            return true;
        }

        function inNumberRange(value, min, max) {
            value = parseFloat(value);
            min = min === '' || min === null ? null : parseFloat(min);
            max = max === '' || max === null ? null : parseFloat(max);

            if (isNaN(value)) value = 0;
            if (min !== null && !isNaN(min) && value < min) return false;
            if (max !== null && !isNaN(max) && value > max) return false;
            return true;
        }

        function setResult(resultId, count) {
            var el = q(resultId);
            if (!el) return;
            el.textContent = count + ' registro(s) encontrado(s).';
        }

        function hideRows(rows, predicate, resultId) {
            var count = 0;
            rows.forEach(function (row) {
                var show = predicate(row);
                row.classList.toggle('vs-row-hidden', !show);
                if (show) count++;
            });
            setResult(resultId, count);
        }

        function activateChip(group, button) {
            document.querySelectorAll('[data-chip-group="' + group + '"]').forEach(function (chip) {
                chip.classList.remove('is-active');
            });
            if (button) button.classList.add('is-active');
        }

        function clearInput(id, value) {
            var el = q(id);
            if (!el) return;
            if (el.type === 'checkbox') {
                el.checked = !!value;
                return;
            }
            el.value = value || '';
        }

        window.toggleFiltrosAvancados = function (key) {
            var panel = q('vsAdvanced-' + key);
            if (!panel) return;
            panel.classList.toggle('is-open');
        };

        function activateStep(step) {
            document.querySelectorAll('.vs-step').forEach(function (btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-step') === step);
            });

            document.querySelectorAll('[data-step-panel]').forEach(function (panel) {
                panel.classList.toggle('is-active', panel.getAttribute('data-step-panel') === step);
            });
        }

        document.querySelectorAll('.vs-step').forEach(function (button) {
            button.addEventListener('click', function () {
                activateStep(this.getAttribute('data-step'));
            });
        });

        activateStep('disp');

        var fipeConsultaUrl = <?php echo json_encode(route('veiculo.consultarFipe', $veiculo->id), 512) ?>;
        var veiculoMarcaId = <?php echo json_encode((int) ($veiculo->marca_id ?? 0), 15, 512) ?>;
        var veiculoModeloId = <?php echo json_encode((int) ($veiculo->modelo_id ?? 0), 15, 512) ?>;
        var veiculoAnoFabricacao = <?php echo json_encode((int) ($veiculo->ano_fabricacao ?? 0), 15, 512) ?>;
        var veiculoAnoModelo = <?php echo json_encode((int) ($veiculo->modelo?->ano_modelo ?? 0), 15, 512) ?>;
        var fipeValorAtual = document.getElementById('fipeResumoValor');
        var fipeCodigoAtual = document.getElementById('fipeModalCodigo');
        var fipeDataAtual = document.getElementById('fipeResumoData');
        var fipeValorCompraAtual = document.getElementById('fipeResumoValorCompra');
        var fipeValorEstimadoAtual = document.getElementById('fipeResumoValorAtual');
        var fipeDepreciacaoAtual = document.getElementById('fipeResumoDepreciacao');
        var fipeTaxaAtual = document.getElementById('fipeResumoTaxa');
        var fipeAlert = document.getElementById('fipeModalAlert');
        var btnExecutarConsultaFipe = document.getElementById('btnExecutarConsultaFipe');
        var formDepreciacaoVeiculo = document.getElementById('formDepreciacaoVeiculo');
        var btnSalvarDepreciacao = formDepreciacaoVeiculo ? formDepreciacaoVeiculo.querySelector('button[type="submit"]') : null;
        var modalDisponibilidadeVeiculo = document.getElementById('modalDisponibilidadeVeiculo');
        var formDisponibilidadeVeiculo = document.getElementById('formDisponibilidadeVeiculo');
        var btnSalvarDisponibilidadeVeiculo = document.getElementById('btnSalvarDisponibilidadeVeiculo');
        var btnAbrirModalDisponibilidadeVeiculo = document.getElementById('btnAbrirModalDisponibilidadeVeiculo');
        var disponibilidadeModalAlert = document.getElementById('disponibilidadeModalAlert');

        function formatarMoedaBR(valor) {
            var numero = Number(valor || 0);
            return numero.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        function mostrarFipeAlert(tipo, texto) {
            if (!fipeAlert) return;
            fipeAlert.className = 'alert alert-' + tipo;
            fipeAlert.textContent = texto;
            fipeAlert.classList.remove('d-none');
        }

        function ocultarFipeAlert() {
            if (!fipeAlert) return;
            fipeAlert.className = 'alert d-none';
            fipeAlert.textContent = '';
        }

        function mostrarDisponibilidadeAlert(tipo, texto) {
            if (!disponibilidadeModalAlert) return;
            disponibilidadeModalAlert.className = 'alert alert-' + tipo;
            disponibilidadeModalAlert.textContent = texto;
            disponibilidadeModalAlert.classList.remove('d-none');
        }

        function ocultarDisponibilidadeAlert() {
            if (!disponibilidadeModalAlert) return;
            disponibilidadeModalAlert.className = 'alert d-none';
            disponibilidadeModalAlert.textContent = '';
        }

        if (btnAbrirModalDisponibilidadeVeiculo) {
            btnAbrirModalDisponibilidadeVeiculo.addEventListener('click', function () {
                ocultarDisponibilidadeAlert();
                if (window.jQuery && $('#modalDisponibilidadeVeiculo').modal) {
                    $('#modalDisponibilidadeVeiculo').modal('show');
                }
            });
        }

        if (formDisponibilidadeVeiculo) {
            formDisponibilidadeVeiculo.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!btnSalvarDisponibilidadeVeiculo) {
                    return;
                }

                var original = btnSalvarDisponibilidadeVeiculo.innerHTML;
                btnSalvarDisponibilidadeVeiculo.disabled = true;
                btnSalvarDisponibilidadeVeiculo.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Salvando...';
                ocultarDisponibilidadeAlert();

                fetch(formDisponibilidadeVeiculo.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: new FormData(formDisponibilidadeVeiculo)
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            throw new Error(result.payload?.message || 'Falha ao salvar a disponibilidade.');
                        }

                        if (window.jQuery && $('#modalDisponibilidadeVeiculo').modal) {
                            $('#modalDisponibilidadeVeiculo').modal('hide');
                        }

                        var mensagemSucesso = result.payload?.message || 'Disponibilidade cadastrada com sucesso.';

                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            window.Swal.fire({
                                icon: 'success',
                                title: 'Sucesso',
                                text: mensagemSucesso,
                                confirmButtonText: 'Recarregar página',
                                allowOutsideClick: false
                            }).then(function () {
                                window.location.reload();
                            });
                            return;
                        }

                        mostrarDisponibilidadeAlert('success', mensagemSucesso);
                        window.location.reload();
                    })
                    .catch(function (error) {
                        mostrarDisponibilidadeAlert('danger', error.message || 'Falha ao salvar a disponibilidade.');
                    })
                    .finally(function () {
                        btnSalvarDisponibilidadeVeiculo.disabled = false;
                        btnSalvarDisponibilidadeVeiculo.innerHTML = original;
                    });
            });
        }

        if (btnExecutarConsultaFipe) {
            btnExecutarConsultaFipe.addEventListener('click', function () {
                if (!veiculoMarcaId || !veiculoModeloId) {
                    mostrarFipeAlert('warning', 'NÃ£o foi possÃ­vel consultar a FIPE porque a marca ou o modelo estÃ£o ausentes.');
                    return;
                }

                var original = btnExecutarConsultaFipe.innerHTML;
                btnExecutarConsultaFipe.disabled = true;
                btnExecutarConsultaFipe.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Consultando...';
                ocultarFipeAlert();

                fetch(fipeConsultaUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        marca_id: veiculoMarcaId,
                        modelo_id: veiculoModeloId,
                        ano_fabricacao: veiculoAnoFabricacao > 0 ? veiculoAnoFabricacao : null,
                        ano_modelo: veiculoAnoModelo > 0 ? veiculoAnoModelo : null
                    })
                })
                    .then(function (response) {
                        var contentType = response.headers.get('content-type') || '';
                        if (!contentType.includes('application/json')) {
                            return response.text().then(function () {
                                throw new Error('A consulta de FIPE retornou uma resposta inválida. Verifique o ano do veÃ­culo ou tente novamente.');
                            });
                        }

                        return response.json().then(function (payload) {
                            return { ok: response.ok, status: response.status, payload: payload };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            throw new Error(result.payload?.message || 'Falha ao consultar a FIPE.');
                        }

                        if (result.payload?.valor_fipe !== undefined && fipeValorAtual) {
                            fipeValorAtual.textContent = formatarMoedaBR(result.payload.valor_fipe);
                        }
                        if (result.payload?.codigo_fipe !== undefined && fipeCodigoAtual) {
                            fipeCodigoAtual.textContent = result.payload.codigo_fipe || '-';
                        }
                        if (result.payload?.data_consulta_fipe !== undefined && fipeDataAtual) {
                            var data = result.payload.data_consulta_fipe;
                            if (data) {
                                var parts = String(data).split('-');
                                fipeDataAtual.textContent = parts.length === 3 ? (parts[2] + '/' + parts[1] + '/' + parts[0]) : data;
                            }
                        }
                        if (result.payload?.valor_atual_estimado !== undefined && fipeValorEstimadoAtual) {
                            fipeValorEstimadoAtual.textContent = result.payload.valor_atual_estimado !== null ? formatarMoedaBR(result.payload.valor_atual_estimado) : '-';
                        }
                        if (result.payload?.valor_compra !== undefined && fipeValorCompraAtual) {
                            fipeValorCompraAtual.textContent = result.payload.valor_compra !== null ? formatarMoedaBR(result.payload.valor_compra) : '-';
                        }
                        if (result.payload?.valor_depreciacao_acumulada !== undefined && fipeDepreciacaoAtual) {
                            fipeDepreciacaoAtual.textContent = result.payload.valor_depreciacao_acumulada !== null ? formatarMoedaBR(result.payload.valor_depreciacao_acumulada) : '-';
                        }
                        if (result.payload?.taxa_depreciacao_anual !== undefined && fipeTaxaAtual) {
                            fipeTaxaAtual.textContent = result.payload.taxa_depreciacao_anual !== null ? Number(result.payload.taxa_depreciacao_anual).toFixed(2).replace('.', ',') + '% a.a.' : '-';
                        }

                        mostrarFipeAlert('success', 'Consulta concluÃ­da com sucesso. Os dados foram atualizados no resumo.');
                    })
                    .catch(function (error) {
                        mostrarFipeAlert('danger', error.message || 'Falha ao consultar a FIPE.');
                    })
                    .finally(function () {
                        btnExecutarConsultaFipe.disabled = false;
                        btnExecutarConsultaFipe.innerHTML = original;
                    });
            });
        }

        if (formDepreciacaoVeiculo) {
            formDepreciacaoVeiculo.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!btnSalvarDepreciacao) {
                    return;
                }

                var original = btnSalvarDepreciacao.innerHTML;
                btnSalvarDepreciacao.disabled = true;
                btnSalvarDepreciacao.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Salvando...';

                fetch(formDepreciacaoVeiculo.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: new FormData(formDepreciacaoVeiculo)
                })
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            return { ok: response.ok, payload: payload };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            throw new Error(result.payload?.message || 'Falha ao salvar a depreciação.');
                        }

                        var payload = result.payload?.payload || {};

                        if (payload.valor_compra !== undefined && fipeValorCompraAtual) {
                            fipeValorCompraAtual.textContent = payload.valor_compra !== null ? formatarMoedaBR(payload.valor_compra) : '-';
                        }
                        if (payload.data_compra !== undefined) {
                            var dataCompraEl = document.querySelector('[data-depr-data-compra-resumo]') || document.getElementById('fipeResumoDataCompra');
                            if (dataCompraEl) {
                                if (payload.data_compra) {
                                    var partsCompra = String(payload.data_compra).split('-');
                                    dataCompraEl.textContent = partsCompra.length === 3 ? (partsCompra[2] + '/' + partsCompra[1] + '/' + partsCompra[0]) : payload.data_compra;
                                } else {
                                    dataCompraEl.textContent = '-';
                                }
                            }
                        }
                        if (payload.taxa_depreciacao_anual !== undefined && fipeTaxaAtual) {
                            fipeTaxaAtual.textContent = payload.taxa_depreciacao_anual !== null ? Number(payload.taxa_depreciacao_anual).toFixed(2).replace('.', ',') + '% a.a.' : '-';
                        }
                        if (payload.valor_atual_estimado !== undefined && fipeValorEstimadoAtual) {
                            fipeValorEstimadoAtual.textContent = payload.valor_atual_estimado !== null ? formatarMoedaBR(payload.valor_atual_estimado) : '-';
                        }
                        if (payload.valor_depreciacao_acumulada !== undefined && fipeDepreciacaoAtual) {
                            fipeDepreciacaoAtual.textContent = payload.valor_depreciacao_acumulada !== null ? formatarMoedaBR(payload.valor_depreciacao_acumulada) : '-';
                        }
                        if (window.jQuery && $('#modalConfigurarDepreciacaoVeiculo').modal) {
                            $('#modalConfigurarDepreciacaoVeiculo').modal('hide');
                        }

                        mostrarFipeAlert('success', result.payload?.message || 'Depreciação atualizada com sucesso.');
                    })
                    .catch(function (error) {
                        mostrarFipeAlert('danger', error.message || 'Falha ao salvar a depreciação.');
                    })
                    .finally(function () {
                        btnSalvarDepreciacao.disabled = false;
                        btnSalvarDepreciacao.innerHTML = original;
                    });
            });
        }

        var fipeCanvas = document.getElementById('fipeValueChart');
        if (fipeCanvas && window.Chart) {
            var valorCompra = Number(<?php echo json_encode((float) ($finValorCompra ?? 0), 15, 512) ?>);
            var valorFipe = Number(<?php echo json_encode((float) ($finValorFipe ?? 0), 15, 512) ?>);
            var valorAtual = Number(<?php echo json_encode((float) ($finValorAtualEstimado ?? 0), 15, 512) ?>);
            var depreciacao = Number(<?php echo json_encode((float) ($finDepreciacaoAcumulada ?? 0), 15, 512) ?>);

            new Chart(fipeCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Valor de compra', 'FIPE', 'Depreciação acumulada', 'Valor atual estimado'],
                    datasets: [{
                        data: [
                            valorCompra > 0 ? valorCompra : 0,
                            valorFipe > 0 ? valorFipe : 0,
                            depreciacao > 0 ? depreciacao : 0,
                            valorAtual > 0 ? valorAtual : 0
                        ],
                        backgroundColor: ['#2563eb', '#f59e0b', '#ef4444', '#22c55e'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 10,
                                padding: 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    var value = Number(context.raw || 0);
                                    return context.label + ': ' + value.toLocaleString('pt-BR', {
                                        style: 'currency',
                                        currency: 'BRL'
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        window.chipAvarias = function (button, action) {
            activateChip('avarias', button);
            limparFiltroAvarias(false);

            if (action === 'pendente') clearInput('avf_status', 'pendente');
            if (action === 'corrigido') clearInput('avf_status', 'corrigido');
            if (action === 'reserva') clearInput('avf_com_reserva', true);

            filtrarAvarias();
        };

        window.filtrarAvarias = function () {
            var busca = normalize(q('avf_busca') && q('avf_busca').value);
            var status = normalize(q('avf_status') && q('avf_status').value);
            var tipo = normalize(q('avf_tipo') && q('avf_tipo').value);
            var local = normalize(q('avf_local') && q('avf_local').value);
            var reserva = normalize(q('avf_reserva') && q('avf_reserva').value);
            var comReserva = q('avf_com_reserva') && q('avf_com_reserva').checked;

            var rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaAvarias tbody tr'));
            hideRows(rows, function (row) {
                var ds = row.dataset;
                var hasReserva = !!normalize(ds.avReserva);

                if (status && normalize(ds.avStatus) !== status) return false;
                if (!contains(ds.avTipo, tipo)) return false;
                if (!contains(ds.avLocal, local)) return false;
                if (reserva && normalize(ds.avReserva) !== reserva) return false;
                if (comReserva && !hasReserva) return false;
                if (busca && !contains(row.textContent, busca)) return false;

                return true;
            }, 'avf_result');
        };

        window.limparFiltroAvarias = function (resetChip) {
            ['avf_busca', 'avf_status', 'avf_tipo', 'avf_local', 'avf_reserva'].forEach(function (id) {
                clearInput(id, '');
            });
            clearInput('avf_com_reserva', false);
            if (resetChip !== false) {
                var chip = document.querySelector('[data-chip-group="avarias"]');
                activateChip('avarias', chip);
            }
            filtrarAvarias();
        };

        window.chipOs = function (button, action) {
            activateChip('os', button);
            limparFiltroOs(false);

            if (action === 'aberta') clearInput('osf_status', 'aberta');
            if (action === 'em_andamento') clearInput('osf_status', 'em_andamento');
            if (action === 'concluida') clearInput('osf_status', 'concluida');
            if (action === '30dias') {
                var today = '<?php echo e(now()->format('Y-m-d')); ?>';
                var past = '<?php echo e(now()->subDays(30)->format('Y-m-d')); ?>';
                clearInput('osf_data_de', past);
                clearInput('osf_data_ate', today);
            }

            filtrarOs();
        };

        window.filtrarOs = function () {
            var busca = normalize(q('osf_busca') && q('osf_busca').value);
            var status = normalize(q('osf_status') && q('osf_status').value);
            var numero = normalize(q('osf_numero') && q('osf_numero').value);
            var dataDe = q('osf_data_de') && q('osf_data_de').value;
            var dataAte = q('osf_data_ate') && q('osf_data_ate').value;

            var rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaOs tbody tr'));
            hideRows(rows, function (row) {
                var ds = row.dataset;

                if (status && normalize(ds.osStatus) !== status) return false;
                if (!contains(ds.osNumero, numero)) return false;
                if (!inDateRange(ds.osData, dataDe, dataAte)) return false;
                if (busca && !contains(row.textContent, busca)) return false;

                return true;
            }, 'osf_result');
        };

        window.limparFiltroOs = function (resetChip) {
            ['osf_busca', 'osf_status', 'osf_numero', 'osf_data_de', 'osf_data_ate'].forEach(function (id) {
                clearInput(id, '');
            });
            if (resetChip !== false) {
                var chip = document.querySelector('[data-chip-group="os"]');
                activateChip('os', chip);
            }
            filtrarOs();
        };

        window.chipMov = function (button, action) {
            activateChip('mov', button);
            limparFiltroMov(false);

            if (action === 'reserva') clearInput('mvf_com_reserva', true);
            if (action === 'sem_chegada') clearInput('mvf_sem_chegada', true);
            if (action === 'hoje') {
                var today = '<?php echo e(now()->format('Y-m-d')); ?>';
                clearInput('mvf_data_de', today);
                clearInput('mvf_data_ate', today);
            }

            filtrarMov();
        };

        window.filtrarMov = function () {
            var busca = normalize(q('mvf_busca') && q('mvf_busca').value);
            var tipo = normalize(q('mvf_tipo') && q('mvf_tipo').value);
            var status = normalize(q('mvf_status') && q('mvf_status').value);
            var origem = normalize(q('mvf_origem') && q('mvf_origem').value);
            var destino = normalize(q('mvf_destino') && q('mvf_destino').value);
            var dataDe = q('mvf_data_de') && q('mvf_data_de').value;
            var dataAte = q('mvf_data_ate') && q('mvf_data_ate').value;
            var reserva = normalize(q('mvf_reserva') && q('mvf_reserva').value);
            var usuario = normalize(q('mvf_usuario') && q('mvf_usuario').value);
            var comReserva = q('mvf_com_reserva') && q('mvf_com_reserva').checked;
            var semChegada = q('mvf_sem_chegada') && q('mvf_sem_chegada').checked;

            var rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaMov tbody tr'));
            hideRows(rows, function (row) {
                var ds = row.dataset;
                var hasReserva = !!normalize(ds.mvReserva);

                if (tipo && normalize(ds.mvTipo) !== tipo) return false;
                if (status && normalize(ds.mvStatus) !== status) return false;
                if (!contains(ds.mvOrigem, origem)) return false;
                if (!contains(ds.mvDestino, destino)) return false;
                if (!inDateRange(ds.mvData, dataDe, dataAte)) return false;
                if (reserva && normalize(ds.mvReserva) !== reserva) return false;
                if (!contains(ds.mvUsuario, usuario)) return false;
                if (comReserva && !hasReserva) return false;
                if (semChegada && ds.mvChegada !== '0') return false;
                if (busca && !contains(row.textContent, busca)) return false;

                return true;
            }, 'mvf_result');
        };

        window.limparFiltroMov = function (resetChip) {
            ['mvf_busca', 'mvf_tipo', 'mvf_status', 'mvf_origem', 'mvf_destino', 'mvf_data_de', 'mvf_data_ate', 'mvf_reserva', 'mvf_usuario'].forEach(function (id) {
                clearInput(id, '');
            });
            clearInput('mvf_com_reserva', false);
            clearInput('mvf_sem_chegada', false);
            if (resetChip !== false) {
                var chip = document.querySelector('[data-chip-group="mov"]');
                activateChip('mov', chip);
            }
            filtrarMov();
        };

        window.chipSn = function (button, action) {
            activateChip('sinistros', button);
            limparFiltroSn(false);

            if (action === 'aberto') clearInput('snf_status', 'aberto');
            if (action === 'aguardando_documentos') clearInput('snf_status', 'aguardando_documentos');
            if (action === 'encerrado') clearInput('snf_status', 'encerrado');

            filtrarSn();
        };

        window.filtrarSn = function () {
            var busca = normalize(q('snf_busca') && q('snf_busca').value);
            var status = normalize(q('snf_status') && q('snf_status').value);
            var tipo = normalize(q('snf_tipo') && q('snf_tipo').value);
            var dataDe = q('snf_data_de') && q('snf_data_de').value;
            var dataAte = q('snf_data_ate') && q('snf_data_ate').value;
            var seguradora = normalize(q('snf_seguradora') && q('snf_seguradora').value);
            var numSeg = normalize(q('snf_num_seg') && q('snf_num_seg').value);

            var rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaSn tbody tr'));
            hideRows(rows, function (row) {
                var ds = row.dataset;

                if (status && normalize(ds.snStatus) !== status) return false;
                if (tipo && normalize(ds.snTipo) !== tipo) return false;
                if (!inDateRange(ds.snData, dataDe, dataAte)) return false;
                if (!contains(ds.snSeguradora, seguradora)) return false;
                if (!contains(ds.snNumSeg, numSeg)) return false;
                if (busca && !contains(row.textContent, busca)) return false;

                return true;
            }, 'snf_result');
        };

        window.limparFiltroSn = function (resetChip) {
            ['snf_busca', 'snf_status', 'snf_tipo', 'snf_data_de', 'snf_data_ate', 'snf_seguradora', 'snf_num_seg'].forEach(function (id) {
                clearInput(id, '');
            });
            if (resetChip !== false) {
                var chip = document.querySelector('[data-chip-group="sinistros"]');
                activateChip('sinistros', chip);
            }
            filtrarSn();
        };

        window.chipMt = function (button, action) {
            activateChip('multas', button);
            limparFiltroMt(false);

            if (action === 'pendente') clearInput('mtf_status', 'pendente');
            if (action === 'paga') clearInput('mtf_status', 'paga');
            if (action === 'recorrida') clearInput('mtf_status', 'recorrida');
            if (action === 'com_desconto') clearInput('mtf_com_desconto', true);

            filtrarMt();
        };

        window.filtrarMt = function () {
            var busca = normalize(q('mtf_busca') && q('mtf_busca').value);
            var status = normalize(q('mtf_status') && q('mtf_status').value);
            var desconto = normalize(q('mtf_desconto') && q('mtf_desconto').value);
            var orgao = normalize(q('mtf_orgao') && q('mtf_orgao').value);
            var auto = normalize(q('mtf_auto') && q('mtf_auto').value);
            var dataDe = q('mtf_data_de') && q('mtf_data_de').value;
            var dataAte = q('mtf_data_ate') && q('mtf_data_ate').value;
            var valorMin = q('mtf_valor_min') && q('mtf_valor_min').value;
            var valorMax = q('mtf_valor_max') && q('mtf_valor_max').value;
            var comDesconto = q('mtf_com_desconto') && q('mtf_com_desconto').checked;

            var rows = Array.prototype.slice.call(document.querySelectorAll('#tabelaMt tbody tr'));
            hideRows(rows, function (row) {
                var ds = row.dataset;

                if (status && normalize(ds.mtStatus) !== status) return false;
                if (desconto && normalize(ds.mtDesconto) !== desconto) return false;
                if (!contains(ds.mtOrgao, orgao)) return false;
                if (!contains(ds.mtAuto, auto)) return false;
                if (!inDateRange(ds.mtData, dataDe, dataAte)) return false;
                if (!inNumberRange(ds.mtValor, valorMin, valorMax)) return false;
                if (comDesconto && ds.mtHasDesconto !== '1') return false;
                if (busca && !contains(row.textContent, busca)) return false;

                return true;
            }, 'mtf_result');
        };

        window.limparFiltroMt = function (resetChip) {
            ['mtf_busca', 'mtf_status', 'mtf_desconto', 'mtf_orgao', 'mtf_auto', 'mtf_data_de', 'mtf_data_ate', 'mtf_valor_min', 'mtf_valor_max'].forEach(function (id) {
                clearInput(id, '');
            });
            clearInput('mtf_com_desconto', false);
            if (resetChip !== false) {
                var chip = document.querySelector('[data-chip-group="multas"]');
                activateChip('multas', chip);
            }
            filtrarMt();
        };

        // Disponibilidade: mantÃ©m o filtro por legenda do calendário
        var buttons = Array.prototype.slice.call(document.querySelectorAll('[data-vd-filter]'));
        var calendar = q('vdCalendar');
        if (buttons.length && calendar) {
            function setCalendarFilter(key) {
                buttons.forEach(function (btn) {
                    btn.classList.toggle('is-active', btn.dataset.vdFilter === key);
                });

                calendar.querySelectorAll('[data-vd-type]').forEach(function (day) {
                    if (key === 'all') {
                        day.classList.remove('is-hidden');
                        return;
                    }
                    day.classList.toggle('is-hidden', day.dataset.vdType !== key);
                });
            }

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setCalendarFilter(this.dataset.vdFilter);
                });
            });

            setCalendarFilter('all');
        }

        filtrarAvarias();
        filtrarOs();
        filtrarMov();
        filtrarSn();
        filtrarMt();
    }());
</script>
<?php $__env->stopSection(); ?>













<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/show.blade.php ENDPATH**/ ?>