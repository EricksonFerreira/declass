@props([
    'tipos'       => collect(),   // Collection de TipoMovimentacao
    'selectId'    => 'tipo_movimentacao_id',
    'modalId'     => 'modalTipoMov',
])

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1a2848,#1e3461);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Tipo de Movimentação</h5>
                        <small style="opacity:.75">Selecione o tipo que melhor descreve esta movimentação</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                {{-- Filtros --}}
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-8 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar</label>
                            <input type="text" id="{{ $modalId }}_busca"
                                   class="form-control form-control-sm"
                                   placeholder="Filtrar por nome ou descrição…"
                                   oninput="filtrarModalTipos('{{ $modalId }}')">
                        </div>
                        <div class="col-md-4 form-group mb-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                    onclick="document.getElementById('{{ $modalId }}_busca').value='';filtrarModalTipos('{{ $modalId }}')">
                                <i class="fas fa-times mr-1"></i>Limpar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2" id="{{ $modalId }}_count">{{ $tipos->count() }} tipos disponíveis</p>

                <div class="row" id="{{ $modalId }}_lista">
                    @foreach($tipos as $tipo)
                    @php
                        $icons = [
                            'adição'       => 'fa-plus-circle',
                            'retirada'     => 'fa-key',
                            'devolução'    => 'fa-undo',
                            'problema'     => 'fa-exclamation-triangle',
                            'transporte'   => 'fa-truck',
                            'chegada'      => 'fa-map-marker-alt',
                            'retorno'      => 'fa-undo-alt',
                            'indisponível' => 'fa-ban',
                            'transferência'=> 'fa-exchange-alt',
                            'fora'         => 'fa-times-circle',
                            'venda'        => 'fa-dollar-sign',
                            'manutenção'   => 'fa-tools',
                        ];
                        $icon = 'fa-tag';
                        foreach ($icons as $k => $v) {
                            if (str_contains(mb_strtolower($tipo->nome ?? ''), $k)) { $icon = $v; break; }
                        }
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-3 mv-tipo-card-wrap"
                         data-nome="{{ mb_strtolower($tipo->nome ?? '') }}"
                         data-descricao="{{ mb_strtolower($tipo->descricao ?? '') }}">
                        <div class="mv-tipo-card" data-select-tipo="{{ $tipo->id }}" data-nome-tipo="{{ $tipo->nome }}"
                             style="cursor:pointer;border:1px solid #e2e9f4;border-radius:12px;padding:14px;transition:all .2s;background:#fff"
                             onmouseover="this.style.borderColor='#4338ca';this.style.boxShadow='0 4px 16px rgba(67,56,202,.12)'"
                             onmouseout="this.style.borderColor='#e2e9f4';this.style.boxShadow='none'">
                            <div class="d-flex align-items-start" style="gap:10px">
                                <div style="width:38px;height:38px;border-radius:10px;background:#eef2ff;color:#4338ca;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem">
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <strong style="font-size:.88rem;display:block;margin-bottom:4px">{{ $tipo->nome }}</strong>
                                    @if($tipo->descricao)
                                    <p class="mb-0 text-muted" style="font-size:.78rem;line-height:1.4">{{ $tipo->descricao }}</p>
                                    @endif
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-primary btn-selecionar-tipo"
                                                data-id="{{ $tipo->id }}" data-nome="{{ $tipo->nome }}"
                                                style="font-size:.75rem;padding:3px 10px;border-radius:20px">
                                            <i class="fas fa-check mr-1"></i>Selecionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div id="{{ $modalId }}_vazio" class="text-center text-muted py-4" style="display:none">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Nenhum tipo encontrado para este filtro.</p>
                </div>
            </div>

        </div>
    </div>
</div>

@once
@push('js')
<script>
window.filtrarModalTipos = function(modalId) {
    var busca = (document.getElementById(modalId + '_busca')?.value || '').toLowerCase().trim();
    var wraps = document.querySelectorAll('#' + modalId + '_lista .mv-tipo-card-wrap');
    var visible = 0;
    wraps.forEach(function(w) {
        var match = !busca ||
            (w.dataset.nome || '').includes(busca) ||
            (w.dataset.descricao || '').includes(busca);
        w.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    var count = document.getElementById(modalId + '_count');
    if (count) count.textContent = visible + ' tipos disponíveis';
    var vazio = document.getElementById(modalId + '_vazio');
    if (vazio) vazio.style.display = visible === 0 ? '' : 'none';
};

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-selecionar-tipo').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id   = this.dataset.id;
            var nome = this.dataset.nome;
            var sel  = document.getElementById('tipo_movimentacao_id');
            if (sel) sel.value = id;
            $('.modal').modal('hide');
        });
    });
});
</script>
@endpush
@endonce
