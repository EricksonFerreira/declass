<div class="ma-card">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-car-crash"></i></div>
        <div>
            <div class="ma-card__title">Danos e Avarias Detectadas</div>
            <div class="ma-card__sub">Avarias reportadas e monitoradas para o veículo desta reserva</div>
        </div>
    </div>
    <div class="ma-card__body">
        <div class="ma-legend mb-3">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap">
                <div>
                    <span class="font-weight-bold" style="color: var(--ma-ink);">Avarias do veículo ativo</span>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge badge-light text-dark border">
                        KM do veículo: {{ $veiculoAtual?->quilometragem_atual ? number_format((int) $veiculoAtual->quilometragem_atual, 0, ',', '.') . ' km' : '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="danos-detectados-table" style="font-size: 0.88rem;">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 120px;">Foto</th>
                        <th>Avaria</th>
                        <th>Local</th>
                        <th>Gravidade</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody id="danos-detectados-container">
                    <tr id="danos-detectados-loading">
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Carregando avarias...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
