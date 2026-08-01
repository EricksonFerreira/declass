<div class="modal fade" id="detalhesDanoModal" tabindex="-1" role="dialog" aria-labelledby="detalhesDanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content dano-modal">
            <div class="dano-modal__header">
                <div class="dano-modal__header-left">
                    <div class="dano-modal__header-icon">
                        <i class="fas fa-car-crash"></i>
                    </div>
                    <div>
                        <div class="dano-modal__eyebrow">Detalhes do Dano</div>
                        <h6 class="dano-modal__title">
                            <span class="js-detalhe-subtitulo">-</span>
                        </h6>
                    </div>
                </div>
                <button type="button" class="dano-modal__close" data-dismiss="modal" aria-label="Fechar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="dano-modal__body">
                <div class="dano-block">
                    <div class="dano-block__label">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Resumo</strong>
                            <span>Informacoes registradas no dano</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted">Nome</div>
                            <div class="font-weight-bold js-detalhe-nome">-</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="small text-muted">Local</div>
                            <div class="font-weight-bold js-detalhe-local">-</div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="small text-muted">Observacoes</div>
                            <div class="font-weight-bold js-detalhe-descricao">-</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="small text-muted">Valor</div>
                            <div class="font-weight-bold js-detalhe-valor">-</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="small text-muted">Decisao</div>
                            <div class="font-weight-bold js-detalhe-decisao">-</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="small text-muted">Reparo</div>
                            <div class="font-weight-bold js-detalhe-reparo">-</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-2">Imagens</div>
                            <div class="dano-preview-grid js-detalhe-imagens"></div>
                            <div class="text-muted small js-detalhe-sem-imagens">Nenhuma imagem cadastrada.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dano-modal__footer">
                <button type="button" class="dano-btn dano-btn--ghost" data-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
