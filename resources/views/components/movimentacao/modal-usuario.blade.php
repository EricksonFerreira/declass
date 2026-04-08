@props([
    'usuarios'  => collect(),   // Collection de User com id, nome, email, numero_identificacao
    'modalId'   => 'modalUsuarioMov',
])

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);color:#fff">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Selecionar Usuário</h5>
                        <small style="opacity:.75">Responsável por esta movimentação</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                {{-- Filtros --}}
                <div class="card card-body py-2 mb-3 border-0 bg-light">
                    <div class="form-row align-items-end">
                        <div class="col-md-4 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar por nome</label>
                            <input type="text" id="{{ $modalId }}_nome"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: João Silva…"
                                   oninput="filtrarModalUsuario('{{ $modalId }}')">
                        </div>
                        <div class="col-md-4 form-group mb-0">
                            <label class="small mb-1 font-weight-600">Buscar por e-mail</label>
                            <input type="text" id="{{ $modalId }}_email"
                                   class="form-control form-control-sm"
                                   placeholder="Ex: joao@email.com…"
                                   oninput="filtrarModalUsuario('{{ $modalId }}')">
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <label class="small mb-1 font-weight-600">ID</label>
                            <input type="number" id="{{ $modalId }}_id"
                                   class="form-control form-control-sm"
                                   placeholder="#"
                                   oninput="filtrarModalUsuario('{{ $modalId }}')">
                        </div>
                        <div class="col-md-2 form-group mb-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                    onclick="['_nome','_email','_id'].forEach(function(s){var el=document.getElementById('{{ $modalId }}'+s);if(el)el.value='';});filtrarModalUsuario('{{ $modalId }}')">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-2" id="{{ $modalId }}_count">{{ $usuarios->count() }} usuários disponíveis</p>

                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:60px">ID</th>
                                <th>Usuário</th>
                                <th>E-mail</th>
                                <th style="width:120px">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="{{ $modalId }}_lista">
                            @foreach($usuarios as $u)
                            <tr class="mv-usuario-row"
                                data-id="{{ $u->id }}"
                                data-nome="{{ mb_strtolower($u->nome ?? '') }}"
                                data-email="{{ mb_strtolower($u->email ?? '') }}">
                                <td><span class="badge badge-secondary">#{{ $u->id }}</span></td>
                                <td>
                                    <strong>{{ $u->nome ?? '—' }}</strong>
                                    @if($u->numero_identificacao)
                                    <br><small class="text-muted">{{ $u->numero_identificacao }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $u->email ?? '—' }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-selecionar-usuario"
                                            data-id="{{ $u->id }}" data-nome="{{ $u->nome }}">
                                        <i class="fas fa-check mr-1"></i>Selecionar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="{{ $modalId }}_vazio" class="text-center text-muted py-4" style="display:none">
                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                    <p>Nenhum usuário encontrado.</p>
                </div>
            </div>

        </div>
    </div>
</div>

@once
@push('js')
<script>
window.filtrarModalUsuario = function(modalId) {
    var nome  = (document.getElementById(modalId + '_nome')?.value  || '').toLowerCase().trim();
    var email = (document.getElementById(modalId + '_email')?.value || '').toLowerCase().trim();
    var id    = (document.getElementById(modalId + '_id')?.value    || '').trim();
    var rows  = document.querySelectorAll('#' + modalId + '_lista .mv-usuario-row');
    var visible = 0;
    rows.forEach(function(r) {
        var matchNome  = !nome  || (r.dataset.nome  || '').includes(nome);
        var matchEmail = !email || (r.dataset.email || '').includes(email);
        var matchId    = !id    || String(r.dataset.id) === id;
        var show = matchNome && matchEmail && matchId;
        r.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    var count = document.getElementById(modalId + '_count');
    if (count) count.textContent = visible + ' usuários disponíveis';
    var vazio = document.getElementById(modalId + '_vazio');
    if (vazio) vazio.style.display = visible === 0 ? '' : 'none';
};
</script>
@endpush
@endonce
