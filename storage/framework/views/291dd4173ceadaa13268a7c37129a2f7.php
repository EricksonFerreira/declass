<?php
    $selecionadosPorId = [];

    if (!empty($opcionaisSelecionados) && is_iterable($opcionaisSelecionados)) {
        foreach ($opcionaisSelecionados as $itemSelecionado) {
            $idSelecionado = (int) data_get($itemSelecionado, 'id', 0);
            if ($idSelecionado <= 0) {
                continue;
            }

            $quantidadeSelecionada = (int) data_get(
                $itemSelecionado,
                'quantidade',
                data_get($itemSelecionado, 'pivot.quantidade', 1)
            );

            $selecionadosPorId[$idSelecionado] = max(0, $quantidadeSelecionada);
        }
    }
?>

<style>
    .opcionais-modern {
        width: 100%;
        margin-top: 2px;
    }

    .opcional-grupo {
        border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748b) 28%, #d9e3ef 72%);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 12px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .05);
    }

    .opcional-grupo__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: linear-gradient(
            120deg,
            color-mix(in srgb, var(--empresa-cor-primaria, #0f172a) 86%, #000 14%) 0%,
            color-mix(in srgb, var(--empresa-cor-terciaria, #1e293b) 86%, #000 14%) 100%
        );
        color: #fff;
    }

    .opcional-grupo__title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .94rem;
        font-weight: 700;
    }

    .opcional-grupo__count {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 999px;
        padding: 2px 9px;
        font-size: .72rem;
        font-weight: 700;
        background: rgba(255, 255, 255, .08);
    }

    .opcional-grupo__body {
        padding: 8px;
    }

    .opcional-item {
        border: 1px solid #dce5f1;
        border-radius: 10px;
        padding: 8px 10px;
        background: #fff;
        transition: all .16s ease;
    }

    .opcional-item + .opcional-item {
        margin-top: 8px;
    }

    .opcional-item.activated {
        border-color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 45%, #88a7d0 55%);
        background: color-mix(in srgb, var(--empresa-cor-primaria, #eff6ff) 7%, #ffffff 93%);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 18%, transparent 82%);
    }

    .opcional-item__main {
        display: grid;
        grid-template-columns: minmax(180px, 1.5fr) minmax(120px, .9fr) auto;
        gap: 10px;
        align-items: center;
    }

    .opcional-item__name {
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
        word-break: break-word;
    }

    .opcional-item__details {
        margin-top: 3px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 0;
        background: transparent;
        color: #64748b;
        font-size: .74rem;
        font-weight: 700;
        padding: 0;
        cursor: pointer;
    }

    .opcional-item__details:hover {
        color: var(--empresa-cor-primaria, #2563eb);
    }

    .opcional-price {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d6e3f3;
        border-radius: 999px;
        padding: 5px 10px;
        color: #1e293b;
        background: #f8fbff;
        white-space: nowrap;
    }

    .opcional-price__value {
        font-size: .78rem;
        font-weight: 800;
    }

    .opcional-price__type {
        margin-left: 4px;
        font-size: .68rem;
        font-weight: 700;
        color: #64748b;
        text-transform: lowercase;
    }

    .opcional-item__actions {
        display: inline-flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px;
        min-width: 150px;
    }

    .opcional-action-btn {
        min-width: 102px;
        border-radius: 8px;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .03em;
        padding: 6px 9px;
    }

    .opcional-stepper {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #d7e2ef;
        border-radius: 10px;
        padding: 3px;
        background: #f8fbff;
    }

    .opcional-stepper .btn {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        padding: 0;
        line-height: 1;
        font-size: .95rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .opcional-stepper__value {
        min-width: 20px;
        text-align: center;
        font-size: .84rem;
        font-weight: 800;
        color: #1e293b;
    }

    .opcional-item__description {
        display: none;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed #d8e3ef;
        font-size: .76rem;
        color: #475569;
    }

    .opcional-item__description i {
        margin-right: 6px;
        color: #64748b;
    }

    .opcional-empty {
        width: 100%;
        border: 1px dashed #bdd0e7;
        border-radius: 12px;
        background: #f8fbff;
        color: #4b5563;
        font-size: .84rem;
        font-weight: 600;
        padding: 14px 12px;
        text-align: center;
    }

    .disabled {
        pointer-events: none;
        opacity: .45;
    }

    @media (max-width: 991px) {
        .opcional-item__main {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .opcional-item__actions {
            justify-content: flex-start;
            min-width: 0;
        }

        .opcional-price {
            width: fit-content;
        }
    }
</style>

<div class="opcionais-modern">
    <?php $exibiuAlgumGrupo = false; ?>

    <?php $__currentLoopData = $tiposOpcionais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipoOpcional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $opcionaisDoTipo = $tipoOpcional['opcionais'] ?? [];
            $totalOpcionaisTipo = is_countable($opcionaisDoTipo) ? count($opcionaisDoTipo) : 0;
        ?>

        <?php if($totalOpcionaisTipo > 0): ?>
            <?php $exibiuAlgumGrupo = true; ?>
            <section class="opcional-grupo">
                <div class="opcional-grupo__header">
                    <div class="opcional-grupo__title">
                        <i class="fas fa-tags"></i>
                        <span><?php echo e($tipoOpcional['tipo']->nome); ?></span>
                    </div>
                    <span class="opcional-grupo__count"><?php echo e($totalOpcionaisTipo); ?> itens</span>
                </div>

                <div class="opcional-grupo__body">
                    <?php $__currentLoopData = $opcionaisDoTipo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opcional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $idOpcional = (int) $opcional->id;
                            $quantidadeSelecionada = (int) ($selecionadosPorId[$idOpcional] ?? 0);
                            $estaAtivo = $quantidadeSelecionada > 0;
                            $cobrancaPorDia = $opcional->tipo_cobranca === 'por_dia';
                            $valorExibicao = $cobrancaPorDia ? (float) $opcional->preco_por_dia : (float) $opcional->preco_integral;
                            $sufixoCobranca = $cobrancaPorDia ? '/por dia' : '/integral';
                        ?>

                        <article id="list_opcional<?php echo e($idOpcional); ?>" class="opcional-item <?php echo e($estaAtivo ? 'activated' : ''); ?>">
                            <div class="opcional-item__main">
                                <div>
                                    <div class="opcional-item__name" id="<?php echo e($idOpcional); ?>_nome"><?php echo e($opcional->nome); ?></div>
                                    <button type="button" class="opcional-item__details" onclick="toggleDescCard('opcional<?php echo e($idOpcional); ?>')">
                                        <span>Detalhes</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>

                                <div>
                                    <span class="opcional-price">
                                        <span class="opcional-price__value" data-valor="<?php echo e($valorExibicao); ?>">
                                            <?php echo e(number_format($valorExibicao, 2, ',', '.')); ?>

                                        </span>
                                        <span class="opcional-price__type"><?php echo e($sufixoCobranca); ?></span>
                                    </span>
                                </div>

                                <div class="opcional-item__actions">
                                    <input type="hidden" id="opcional<?php echo e($idOpcional); ?>_input" name="opcional[<?php echo e($idOpcional); ?>]" value="<?php echo e($quantidadeSelecionada); ?>">

                                    <?php if(!$cobrancaPorDia): ?>
                                        <button
                                            type="button"
                                            class="btn opcional-action-btn <?php echo e($estaAtivo ? 'btn-outline-dark' : 'btn-dark'); ?>"
                                            id="modificaEstadoopcional<?php echo e($idOpcional); ?>"
                                            onclick="modificaEstado('opcional<?php echo e($idOpcional); ?>')"
                                        >
                                            <?php echo e($estaAtivo ? 'REMOVER' : 'ADICIONAR'); ?>

                                        </button>
                                    <?php else: ?>
                                        <div class="opcional-stepper">
                                            <button
                                                type="button"
                                                class="btn btn-dark <?php echo e($quantidadeSelecionada <= 0 ? 'disabled' : ''); ?>"
                                                id="decrementopcional<?php echo e($idOpcional); ?>"
                                                onclick="decrement('opcional<?php echo e($idOpcional); ?>')"
                                            >
                                                -
                                            </button>

                                            <span class="opcional-stepper__value" id="valueopcional<?php echo e($idOpcional); ?>"><?php echo e($quantidadeSelecionada); ?></span>

                                            <button
                                                type="button"
                                                class="btn btn-dark"
                                                id="incrementopcional<?php echo e($idOpcional); ?>"
                                                onclick="increment('opcional<?php echo e($idOpcional); ?>')"
                                            >
                                                +
                                            </button>

                                            <input type="hidden" id="max_accepted_qtyopcional<?php echo e($idOpcional); ?>" name="max_accepted_qtyopcional<?php echo e($idOpcional); ?>" value="2">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div id="desc_card_opcional<?php echo e($idOpcional); ?>" class="opcional-item__description desc_card">
                                <i class="far fa-file-alt"></i>
                                <?php echo e($opcional->descricao ?: 'Sem descricao informada para este opcional.'); ?>

                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if(!$exibiuAlgumGrupo): ?>
        <div class="opcional-empty">
            Nenhum opcional disponivel para o grupo selecionado.
        </div>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function() {
        const tiposOpcionais = <?php echo isset($tiposOpcionais) ? json_encode($tiposOpcionais) : '[]'; ?>;

        if (typeof removeAllOpcional === 'function') {
            removeAllOpcional();
        }

        if (Array.isArray(tiposOpcionais) && tiposOpcionais.length > 0) {
            tiposOpcionais.forEach(function(tipoOpcional) {
                if (!tipoOpcional || !Array.isArray(tipoOpcional.opcionais)) {
                    return;
                }

                tipoOpcional.opcionais.forEach(function(opcional) {
                    addOpcional(opcional);
                });
            });
        }

        formatarPrecosOpcionais();
    });

    function formatarPrecosOpcionais() {
        if (typeof formatarMoeda !== 'function') {
            return;
        }

        $('.opcional-price__value').each(function() {
            const valor = Number($(this).data('valor'));
            $(this).text(formatarMoeda(Number.isFinite(valor) ? valor : 0));
        });
    }

    function toggleDescCard(code) {
        $('#desc_card_' + code).stop(true, true).slideToggle(120);
    }

    function modificaEstado(id) {
        const botaoElemento = $('#modificaEstado' + id);
        const idOpcional = id.replace('opcional', '');
        const opcionalCard = $('#list_' + id);

        if (botaoElemento.text().trim() === 'ADICIONAR') {
            botaoElemento.text('REMOVER');
            botaoElemento.addClass('btn-outline-dark').removeClass('btn-dark');
            opcionalCard.addClass('activated');
            addOpcionalReserva(idOpcional, true);
        } else {
            botaoElemento.text('ADICIONAR');
            botaoElemento.removeClass('btn-outline-dark').addClass('btn-dark');
            opcionalCard.removeClass('activated');
            removeOpcionalReserva(idOpcional);
        }

        atualizarMinhaReserva();
        if (typeof window.atualizarResumoOpcionaisReserva === 'function') {
            window.atualizarResumoOpcionaisReserva();
        }
    }

    function increment(id) {
        const valueElement = $('#value' + id);
        const incrementButton = $('#increment' + id);
        const decrementButton = $('#decrement' + id);
        const countMaxIncrement = $('#max_accepted_qty' + id);
        const value = parseInt(valueElement.text(), 10) || 0;
        const novoValor = value + 1;

        incrementButton.removeClass('disabled');
        decrementButton.removeClass('disabled');

        if (parseInt(countMaxIncrement.val(), 10) >= novoValor) {
            valueElement.text(novoValor);
            $('#list_' + id).addClass('activated');

            if (parseInt(countMaxIncrement.val(), 10) === novoValor) {
                incrementButton.addClass('disabled');
            }
        }

        const idOpcional = id.replace('opcional', '');
        addOpcionalReserva(idOpcional, novoValor);
        atualizarMinhaReserva();
        if (typeof window.atualizarResumoOpcionaisReserva === 'function') {
            window.atualizarResumoOpcionaisReserva();
        }
    }

    function decrement(id) {
        const valueElement = $('#value' + id);
        const incrementButton = $('#increment' + id);
        const decrementButton = $('#decrement' + id);
        const value = parseInt(valueElement.text(), 10) || 0;

        incrementButton.removeClass('disabled');
        decrementButton.removeClass('disabled');

        let novoValor = 0;
        if (value > 0) {
            novoValor = value - 1;
            valueElement.text(novoValor);

            if (novoValor === 0) {
                $('#list_' + id).removeClass('activated');
                decrementButton.addClass('disabled');
            }
        }

        const idOpcional = id.replace('opcional', '');
        removeOpcionalReserva(idOpcional);
        atualizarMinhaReserva();
        if (typeof window.atualizarResumoOpcionaisReserva === 'function') {
            window.atualizarResumoOpcionaisReserva();
        }
    }
</script>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/components/opcionais.blade.php ENDPATH**/ ?>