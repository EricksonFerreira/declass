<div class="hs-sec">
    <i class="fa fa-chalkboard-teacher"></i> Professores atribuídos
    <span class="hs-sec-badge" style="background:#8b5cf6;">{{ $turma->atribuicoes->count() }}</span>
    @can('turma.atribuicao.store')
        <button type="button" class="te-sec-btn" style="background:#8b5cf6;" data-toggle="modal" data-target="#modal-atribuir-professor">
            <i class="fa fa-plus mr-1"></i> Atribuir professor
        </button>
    @endcan
</div>

<div class="hs-lista mb-3">
    @forelse($turma->atribuicoes as $atribuicao)
        @php
            $pnome = trim((string) ($atribuicao->user->nome ?? '?'));
            $piniciais = collect(preg_split('/\s+/', $pnome) ?: [])->filter()->take(2)
                ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
        @endphp
        <div class="hs-item">
            <span class="hs-item-icone" style="--app:{{ $atribuicao->esta_ativa ? '#8b5cf6' : '#94a3b8' }}; font-size:.68rem; font-weight:800;">{{ $piniciais }}</span>
            <div class="hs-item-info">
                <b>{{ $atribuicao->user->nome ?? '—' }}</b>
                <small>
                    {{ $atribuicao->disciplina->nome ?? 'Todas as disciplinas' }} ·
                    {{ $papeis[$atribuicao->papel] ?? $atribuicao->papel }}
                </small>
            </div>
            <div class="ml-auto d-flex align-items-center" style="gap:.4rem;">
                @if($atribuicao->esta_ativa)
                    <span class="badge badge-success">Ativa</span>
                @else
                    <span class="badge badge-secondary">Inativa</span>
                @endif
                @can('turma.atribuicao.destroy')
                    <form method="POST" action="{{ route('turma.atribuicao.destroy', [$turma->id, $atribuicao->id]) }}" onsubmit="return confirm('Remover esta atribuição?');" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger" title="Remover atribuição"><i class="fa fa-times"></i></button>
                    </form>
                @endcan
            </div>
        </div>
    @empty
        <div class="hs-item-vazio">
            <i class="fas fa-chalkboard-teacher"></i>
            Nenhum professor atribuído.
        </div>
    @endforelse
</div>

@can('turma.atribuicao.store')
<div class="modal fade" id="modal-atribuir-professor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:14px;">
            <form method="POST" action="{{ route('turma.atribuicao.store', $turma->id) }}">
                @csrf
                <div class="modal-header" style="border-radius:14px 14px 0 0;">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-chalkboard-teacher mr-1" style="color:#8b5cf6;"></i> Atribuir professor</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Professor <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-control te-select2" data-placeholder="Digite para buscar..." required>
                            <option value="">Selecionar usuário...</option>
                            @foreach($professoresOpcoes as $uid => $unome)
                                <option value="{{ $uid }}">{{ $unome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold mb-1">Vínculo</label>
                        <div class="vinc-seg">
                            <label class="vinc-seg-opt">
                                <input type="radio" name="vinculo" value="todas" checked>
                                <span><i class="fa fa-graduation-cap"></i> Todas (regente)</span>
                            </label>
                            <label class="vinc-seg-opt">
                                <input type="radio" name="vinculo" value="disciplinas">
                                <span><i class="fa fa-book"></i> Escolher disciplinas</span>
                            </label>
                        </div>
                        <small class="text-muted">Regente cobre a turma toda — comum na educação infantil, em que o mesmo professor leciona tudo.</small>
                    </div>
                    <div class="form-group" id="vinc-disciplinas" style="display:none;">
                        <label class="font-weight-bold">Disciplinas <span class="text-danger">*</span></label>
                        <select name="disciplina_ids[]" class="form-control te-select2" multiple="multiple"
                                data-placeholder="Selecione uma ou mais disciplinas...">
                            @foreach($disciplinasOfertadas as $did => $dnome)
                                <option value="{{ $did }}">{{ $dnome }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Uma atribuição é criada para cada disciplina marcada.</small>
                        <small class="d-block mt-1 font-weight-bold" id="vinc-hint" style="color:#8b5cf6;display:none;"></small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Papel <span class="text-danger">*</span></label>
                        <select name="papel" class="form-control" required>
                            @foreach($papeis as $pk => $pl)
                                <option value="{{ $pk }}">{{ $pl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary"><i class="fa fa-check mr-1"></i> Atribuir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #modal-atribuir-professor .vinc-seg { display: inline-flex; background: #fff; border: 1px solid #e2e8f0; border-radius: 999px; padding: 3px; gap: 2px; }
    #modal-atribuir-professor .vinc-seg-opt { margin: 0; position: relative; }
    #modal-atribuir-professor .vinc-seg-opt input { position: absolute; opacity: 0; pointer-events: none; }
    #modal-atribuir-professor .vinc-seg-opt span { display: inline-flex; align-items: center; gap: .3rem; padding: .28rem .8rem; border-radius: 999px; font-size: .74rem; font-weight: 700; color: #64748b; cursor: pointer; }
    #modal-atribuir-professor .vinc-seg-opt input:checked + span { background: #8b5cf6; color: #fff; }
</style>

<script>
(function () {
    var modal = document.getElementById('modal-atribuir-professor');
    if (!modal) { return; }
    var box = document.getElementById('vinc-disciplinas');
    var hint = document.getElementById('vinc-hint');
    var selProf = modal.querySelector('select[name="user_id"]');
    var selDisc = modal.querySelector('select[name="disciplina_ids[]"]');
    var habilitacao = @json($professoresDisciplinas ?? []);

    // Guarda o rotulo original de cada disciplina para reescrever
    // o sufixo de habilitacao sem acumular texto.
    if (selDisc) {
        selDisc.querySelectorAll('option').forEach(function (o) {
            o.dataset.base = o.textContent;
        });
    }

    function toggleDisciplinas() {
        var checked = modal.querySelector('input[name="vinculo"]:checked');
        box.style.display = (checked && checked.value === 'disciplinas') ? 'block' : 'none';
    }

    function rank(o, info) {
        var id = parseInt(o.value, 10);
        if (info.principais.indexOf(id) !== -1) { return 0; }
        if (info.optativas.indexOf(id) !== -1) { return 1; }
        return 2;
    }

    // Sinaliza no select de disciplinas as que o professor leciona:
    // principais primeiro (marcadas), depois optativas, depois demais.
    function sinalizar() {
        if (!selDisc || !selProf) { return; }
        var info = habilitacao[selProf.value] || { principais: [], optativas: [] };
        var opts = Array.prototype.slice.call(selDisc.options);
        var nomes = { principais: [], optativas: [] };

        opts.forEach(function (o) {
            var id = parseInt(o.value, 10);
            var sufixo = '';
            if (info.principais.indexOf(id) !== -1) {
                sufixo = ' · principal';
                nomes.principais.push(o.dataset.base);
            } else if (info.optativas.indexOf(id) !== -1) {
                sufixo = ' · optativa';
                nomes.optativas.push(o.dataset.base);
            }
            o.textContent = o.dataset.base + sufixo;
        });

        opts.sort(function (a, b) {
            var d = rank(a, info) - rank(b, info);
            return d !== 0 ? d : a.dataset.base.localeCompare(b.dataset.base);
        });
        opts.forEach(function (o) { selDisc.appendChild(o); });

        if (window.jQuery) { window.jQuery(selDisc).trigger('change.select2'); }

        if (hint) {
            if (!selProf.value) {
                hint.style.display = 'none';
            } else if (!nomes.principais.length && !nomes.optativas.length) {
                hint.style.display = 'block';
                hint.textContent = 'Este professor ainda não tem disciplinas habilitadas (cadastro em Usuários).';
            } else {
                hint.style.display = 'block';
                var partes = [];
                if (nomes.principais.length) { partes.push('Principais: ' + nomes.principais.join(', ')); }
                if (nomes.optativas.length) { partes.push('Optativas: ' + nomes.optativas.join(', ')); }
                hint.textContent = partes.join(' · ');
            }
        }
    }

    modal.querySelectorAll('input[name="vinculo"]').forEach(function (r) {
        r.addEventListener('change', toggleDisciplinas);
    });
    toggleDisciplinas();

    if (window.jQuery && selProf) {
        window.jQuery(selProf).on('change', sinalizar);
    }
})();
</script>
@endcan
