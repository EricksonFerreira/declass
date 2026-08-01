<div class="modal fade" id="modalCobrarDano" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content dano-modal">
            <form class="js-form-cobrar-dano" method="POST" action="{{ route('reserva.checkout.revisao.ocorrencia', $codigoReserva) }}">
                @csrf
                <div class="dano-modal__header">
                    <div class="dano-modal__header-left">
                        <div class="dano-modal__header-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div>
                            <div class="dano-modal__eyebrow">Alterar Decisao</div>
                            <h6 class="dano-modal__title js-dano-titulo">Cobranca de dano</h6>
                        </div>
                    </div>
                    <button type="button" class="dano-modal__close" data-dismiss="modal" aria-label="Fechar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="dano-modal__body">
                    <input type="hidden" class="js-dano-id" name="dano_id">
                    <input type="hidden" class="js-modo-cobranca" name="modo_cobranca" value="cobrar">
                    <input type="hidden" class="js-usar-calcao-hidden" name="usar_calcao" value="1">
                    <input type="hidden" name="valor" value="0">

                    <div class="dano-block">
                        <div class="dano-block__label">
                            <i class="fas fa-file-invoice"></i>
                            <div>
                                <strong>Resumo da cobranca</strong>
                                <span class="js-dano-descricao-preview">-</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Dano</div>
                                <div class="font-weight-bold js-dano-nome-preview">-</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Local</div>
                                <div class="font-weight-bold js-dano-local-preview">-</div>
                            </div>
                            <div class="col-md-6 mb-3 js-dano-valor-wrap">
                                <label class="mb-1">Valor da cobranca</label>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm js-dano-valor-input" name="valor" value="0">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="mb-2 d-block">Decisão</label>
                                <div class="dano-decisao-group">
                                    <label class="dano-decisao-card dano-decisao-card--primary">
                                        <input type="radio" name="decisao_cobranca" value="cobrar" checked>
                                        <span class="dano-decisao-card__title">Cobrar cliente</span>
                                        <span class="dano-decisao-card__subtitle">Aplica a cobrança ao dano selecionado</span>
                                    </label>
                                    <label class="dano-decisao-card dano-decisao-card--muted">
                                        <input type="radio" name="decisao_cobranca" value="nao_cobrar">
                                        <span class="dano-decisao-card__title">Não cobrar</span>
                                        <span class="dano-decisao-card__subtitle">Registra o dano sem cobrança financeira</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="mb-1">Justificativa</label>
                                <textarea class="form-control form-control-sm js-dano-justificativa" name="justificativa" rows="3" placeholder="Informe o motivo da decisao..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="small text-muted mb-2">Imagens</div>
                                <div class="dano-preview-grid js-dano-imagens"></div>
                                <div class="text-muted small js-dano-imagens-empty">Sem imagens cadastradas.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dano-modal__footer">
                    <button type="button" class="dano-btn dano-btn--ghost" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="dano-btn dano-btn--primary js-btn-confirmar-cobranca">Salvar decisao</button>
                </div>
            </form>
        </div>
    </div>
</div>
