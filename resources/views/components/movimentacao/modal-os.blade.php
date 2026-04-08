@props([
    'modalId'    => 'modalOsMov',
    'buscarUrl'  => '',
])

@php
    $buscarUrl = $buscarUrl ?: route('movimentacao.buscarOrdens');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#92400e,#b45309);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Buscar Ordem de Serviço</h5>
                        <small style="opacity:.75">Vincule esta movimentação a uma OS existente</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                {{-- Filtros --}}
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 form-group mb-0">
                            <label class="small mb-1 font-weight-600">ID da OS</label>
                            <input type="number" id="{{ $modalId }}_id"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: 5">
                        </div>
                        <div class="col-md-5 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Número / Referência</label>
                            <input type="text" id="{{ $modalId }}_numero"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: OS-2026-00005…">
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <button type="button" class="btn btn-primary btn-sm w-100"
                                    onclick="buscarOrdensMov('{{ $modalId }}', '{{ $buscarUrl }}')">
                                <i class="fas fa-search mr-1"></i>Buscar
                            </button>
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100"
                                    onclick="['_id','_numero'].forEach(function(s){var el=document.getElementById('{{ $modalId }}'+s);if(el)el.value='';});buscarOrdensMov('{{ $modalId }}', '{{ $buscarUrl }}')">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <div id="{{ $modalId }}_loading" class="text-center py-3 d-none">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th style="width:110px">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="{{ $modalId }}_lista">
                            <tr><td colspan="5" class="text-center text-muted py-3">
                                <i class="fas fa-search mr-1"></i>Preencha os filtros e clique em Buscar
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@once
@push('js')
<script>
window.buscarOrdensMov = function(modalId, url) {
    var id     = (document.getElementById(modalId + '_id')?.value     || '').trim();
    var numero = (document.getElementById(modalId + '_numero')?.value || '').trim();
    var q      = id || numero;

    var loading = document.getElementById(modalId + '_loading');
    var lista   = document.getElementById(modalId + '_lista');

    if (loading) loading.classList.remove('d-none');
    if (lista)   lista.innerHTML = '';

    var params = new URLSearchParams();
    if (q) params.set('q', q);

    fetch(url + '?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (loading) loading.classList.add('d-none');
            if (!data.length) {
                lista.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Nenhuma OS encontrada.</td></tr>';
                return;
            }
            lista.innerHTML = data.map(function(os) {
                return '<tr>' +
                    '<td><strong>#' + os.id + '</strong></td>' +
                    '<td>' + os.numero + '</td>' +
                    '<td><span class="badge badge-warning text-dark">' + os.status + '</span></td>' +
                    '<td>' + os.data + '</td>' +
                    '<td><button type="button" class="btn btn-sm btn-primary btn-sel-os-modal" ' +
                        'data-id="' + os.id + '" data-label="OS ' + os.numero + '">' +
                        '<i class="fas fa-check mr-1"></i>Selecionar</button></td>' +
                    '</tr>';
            }).join('');

            lista.querySelectorAll('.btn-sel-os-modal').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var hiddenInput  = document.getElementById('mv_os_id');
                    var displayInput = document.getElementById('mv_os_display');
                    var clearBtn     = document.getElementById('btnLimparOsMov');
                    if (hiddenInput)  hiddenInput.value  = this.dataset.id;
                    if (displayInput) displayInput.value = this.dataset.label;
                    if (clearBtn)     clearBtn.classList.remove('d-none');
                    $('#' + modalId).modal('hide');
                });
            });
        })
        .catch(function() { if (loading) loading.classList.add('d-none'); });
};
</script>
@endpush
@endonce
