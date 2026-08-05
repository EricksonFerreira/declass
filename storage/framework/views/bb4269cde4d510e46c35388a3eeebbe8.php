<div class="modal fade" id="modalPesquisaGrupo" tabindex="-1" role="dialog" aria-labelledby="modalPesquisaGrupoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(120deg,#0f172a 0%,#1e293b 100%)">
                <h5 class="modal-title text-white font-weight-bold" id="modalPesquisaGrupoLabel">
                    <i class="fas fa-search mr-2"></i>Buscar Grupo de Veículos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            
            <div class="modal-body pb-0">
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Filtros</h6>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-2">
                        <div class="row">
                            <div class="col-md-4 col-sm-6 form-group mb-2">
                                <label class="small mb-1">Grupo de Veículos</label>
                                <input type="text" id="pgf_grupo" class="form-control form-control-sm" placeholder="Nome do grupo...">
                            </div>
                            <div class="col-md-4 col-sm-6 form-group mb-2">
                                <label class="small mb-1">Modelo</label>
                                <input type="text" id="pgf_modelo" class="form-control form-control-sm" placeholder="Nome do modelo...">
                            </div>
                            <div class="col-md-4 col-sm-6 form-group mb-2">
                                <label class="small mb-1">Marca</label>
                                <input type="text" id="pgf_marca" class="form-control form-control-sm" placeholder="Ex: Toyota, Honda...">
                            </div>
                            <div class="col-md-4 col-sm-6 form-group mb-2">
                                <label class="small mb-1">Segmento</label>
                                <input type="text" id="pgf_segmento" class="form-control form-control-sm" placeholder="Ex: SUV, Sedan...">
                            </div>
                            <div class="col-md-2 col-sm-6 form-group mb-2">
                                <label class="small mb-1">Valor/dia mín.</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="number" id="pgf_valor_min" class="form-control form-control-sm" step="0.01" min="0" placeholder="0,00">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6 form-group mb-2">
                                <label class="small mb-1">Valor/dia máx.</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="number" id="pgf_valor_max" class="form-control form-control-sm" step="0.01" min="0" placeholder="9999,00">
                                </div>
                            </div>
                            <div class="col-md-12 d-flex justify-content-end align-items-center form-group mb-0 mt-1" style="gap:8px">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="pgf_btn_limpar">
                                    <i class="fas fa-eraser mr-1"></i>Limpar
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="pgf_btn_buscar">
                                    <i class="fas fa-search mr-1"></i>Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="pgf_loading" class="text-center py-4 d-none">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Buscando...</p>
                </div>
                <div id="pgf_empty" class="text-center py-4 d-none">
                    <i class="fas fa-box-open fa-2x text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Nenhum grupo encontrado com os filtros informados.</p>
                </div>

                
                <div id="pgf_results" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted" id="pgf_total_label"></small>
                        <div id="pgf_paginacao" class="d-flex align-items-center" style="gap:4px"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered mb-0" style="font-size:.85rem">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:52px"></th>
                                    <th>Grupo</th>
                                    <th>Modelos</th>
                                    <th>Marcas</th>
                                    <th>Segmentos</th>
                                    <th class="text-right" style="width:110px">Valor/dia</th>
                                    <th style="width:110px"></th>
                                </tr>
                            </thead>
                            <tbody id="pgf_tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/components/modal-pesquisa.blade.php ENDPATH**/ ?>