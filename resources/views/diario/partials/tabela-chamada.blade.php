{{-- Tabela de presenca reutilizada pela chamada unificada e pela tela de frequencia.
     Espera: $matriculas, $registradas (keyBy matricula_id), $isProfessor.
     Checkbox unico: marcado = presente, desmarcado = falta. Sem registro salvo
     todos comecam como falta — o professor marca so quem veio. O checkbox do
     cabecalho marca/desmarca todos (sem rebaixar justificada/abonada). --}}
<div class="chamada-resumo-bar d-flex justify-content-between align-items-center flex-wrap">
    <small class="text-muted">Marque quem está presente — no cabeçalho dá para marcar/desmarcar todos.</small>
    <span id="freq-resumo" class="chamada-contador"></span>
</div>
<div class="table-responsive">
    <table class="table table-sm table-hover table-chamada mb-0">
        <thead class="thead-light">
            <tr>
                <th class="d-none d-sm-table-cell text-center" style="width:44px;">Nº</th>
                <th>Aluno</th>
                <th class="text-center" style="width:140px;">
                    <label class="freq-presenca freq-master mb-0" title="Marcar todos como presentes — clique de novo para desmarcar">
                        <input type="checkbox" id="freq-marcar-todos">
                        <span>Presença</span>
                    </label>
                </th>
                @unless($isProfessor)
                    <th>Justificativa</th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse($matriculas as $matricula)
                @php
                    $reg = $registradas->get($matricula->id);
                    $statusAtual = old("frequencias.$loop->index.status", $reg->status ?? 'falta');
                    $presente = $statusAtual === 'presente';
                @endphp
                <tr data-status-atual="{{ $statusAtual }}">
                    <td class="d-none d-sm-table-cell text-center text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                    <td class="font-weight-bold">
                        {{ $matricula->aluno->pessoa->nome ?? '—' }}
                        @if($reg?->justificativa_id)
                            <span class="badge badge-info" title="{{ $reg->justificativaRegistro?->tipoLabel() ?? 'Documento registrado' }}">Atestado</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <label class="freq-presenca {{ $presente ? 'is-on' : 'is-off' }} mb-0">
                            <input type="checkbox" class="freq-check" {{ $presente ? 'checked' : '' }}>
                            <span>{{ $presente ? 'Presente' : 'Falta' }}</span>
                        </label>
                        <input type="hidden" name="frequencias[{{ $loop->index }}][status]" class="freq-status" value="{{ $statusAtual }}">
                        <input type="hidden" name="frequencias[{{ $loop->index }}][matricula_id]" value="{{ $matricula->id }}">
                    </td>
                    @unless($isProfessor)
                        <td>
                            <input type="text" name="frequencias[{{ $loop->index }}][justificativa]" class="form-control form-control-sm" maxlength="2000"
                                placeholder="Obrigatória se falta justificada" value="{{ old("frequencias.$loop->index.justificativa", $reg->justificativa ?? '') }}">
                        </td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isProfessor ? 3 : 4 }}" class="text-center text-muted py-4">
                        <i class="fas fa-user-graduate mb-1 d-block" style="font-size:1.4rem;opacity:.4;"></i>
                        Nenhum aluno ativo nesta turma.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    .chamada-resumo-bar { padding: .4rem .6rem; border-bottom: 1px solid #eef1f5; gap: .4rem; }
    .chamada-contador { font-size: .78rem; font-weight: 600; color: #174ea6; background: #eaf1fd; border-radius: 1rem; padding: .18rem .6rem; white-space: nowrap; }
    .table-chamada th, .table-chamada td { padding: .4rem .55rem !important; font-size: .86rem; vertical-align: middle; }
    .table-chamada thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #8a94a1; border-bottom: 1px solid #e9edf2; }
    .freq-presenca {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin: 0;
        padding: .25rem .55rem;
        border: 1px solid #dde3ea;
        border-radius: .4rem;
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
        transition: background .15s, border-color .15s;
    }
    .freq-presenca input { width: 1.05rem; height: 1.05rem; margin: 0; accent-color: #28a745; cursor: pointer; }
    .freq-presenca span { font-size: .8rem; font-weight: 600; color: #98a2ae; min-width: 3.4rem; }
    .freq-presenca.is-on { background: #eafaf1; border-color: #bfe8cd; }
    .freq-presenca.is-on span { color: #1e7e34; }
    .freq-presenca.is-off { background: #fdf2f2; border-color: #f5c8c8; }
    .freq-presenca.is-off span { color: #b42318; }
    .freq-master { font-weight: 600; }
    .freq-master span { color: #495057; min-width: auto; }
    .freq-master input { accent-color: #2e6fda; }
</style>

@push('js')
<script>
    (function () {
        var master = document.getElementById('freq-marcar-todos');

        function sincronizarResumo() {
            var el = document.getElementById('freq-resumo');
            if (!el) return;
            var trs = document.querySelectorAll('tr[data-status-atual]');
            var presentes = 0;
            trs.forEach(function (tr) {
                if (tr.querySelector('.freq-check').checked) presentes++;
            });
            el.textContent = presentes + ' de ' + trs.length + ' presentes';
        }

        function sincronizarMaster() {
            if (!master) return;
            var checks = document.querySelectorAll('.freq-check');
            master.checked = checks.length > 0 && Array.prototype.every.call(checks, function (c) { return c.checked; });
        }

        function definirPresenca(tr, presente, forcarStatus) {
            var cb = tr.querySelector('.freq-check');
            var label = tr.querySelector('.freq-presenca');
            var statusInput = tr.querySelector('.freq-status');
            cb.checked = presente;
            label.classList.toggle('is-on', presente);
            label.classList.toggle('is-off', !presente);
            label.querySelector('span').textContent = presente ? 'Presente' : 'Falta';
            if (forcarStatus) {
                statusInput.value = forcarStatus;
                tr.dataset.statusAtual = forcarStatus;
            }
        }

        document.querySelectorAll('.freq-check').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var tr = cb.closest('tr');
                definirPresenca(tr, cb.checked, cb.checked ? 'presente' : 'falta');
                sincronizarMaster();
                sincronizarResumo();
            });
        });

        if (master) {
            master.addEventListener('change', function () {
                var marcar = master.checked;
                document.querySelectorAll('tr[data-status-atual]').forEach(function (tr) {
                    if (marcar) {
                        definirPresenca(tr, true, 'presente');
                    } else {
                        // desmarcar todos nao rebaixa falta justificada/abonada por atestado
                        var status = tr.dataset.statusAtual;
                        definirPresenca(tr, false, (status === 'justificada' || status === 'abonada') ? status : 'falta');
                    }
                });
                sincronizarResumo();
            });
        }

        sincronizarMaster();
        sincronizarResumo();
    })();
</script>
@endpush
