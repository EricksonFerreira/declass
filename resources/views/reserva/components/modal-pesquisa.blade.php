<!-- Modal de Pesquisa -->
<div class="modal fade" id="modalPesquisa" tabindex="-1" role="dialog" aria-labelledby="modalPesquisaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPesquisaLabel">Pesquisar Veículos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Filtros -->
                    <div class="form-group col-md-3">
                        <label for="grupo">Grupo:</label>
                        <input type="text" class="form-control form-control-sm select2" id="grupo_modal">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="modelo">Modelo:</label>
                        <input type="text" class="form-control form-control-sm select2" id="modelo_modal">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="marca">Marca:</label>
                        <input type="text" class="form-control form-control-sm select2" id="marca_modal">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="segmento">Segmento:</label>
                        <input type="text" class="form-control form-control-sm select2" id="segmento_modal">
                    </div>
                </div>
                <div class="row mt-1 mb-1">
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-primary btn-xs" onclick="procurar()">
                            <i class="fa fa-search"></i> Procurar</button>
                    </div>
                </div>
                <!-- Tabela de Resultados -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Modelo</th>
                            <th>Marca</th>
                            <th>Segmento</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Linhas da tabela com os resultados filtrados -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="itemselecionadomodal(this)">Selecionar</button>
            </div>
        </div>
    </div>
</div>
<!-- Select2 -->
<link rel="stylesheet" href="{{asset('css/select2.min.css')}}">
<script src="{{asset('js/select2.min.js')}}"></script>
