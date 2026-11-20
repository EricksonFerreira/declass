@if($podeGerar && $anoLetivo)
    @once
        @include('turma.partials.turma-modal-styles')
    @endonce

    <div class="modal fade tm-modal" id="modal-gerar-lote" tabindex="-1" role="dialog" aria-labelledby="modal-gerar-lote-label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('turma.gerar_lote') }}" id="form-gerar-lote">
                    @csrf
                    <input type="hidden" name="ano_letivo_id" value="{{ $anoLetivo->id }}">

                    <div class="tm-hero tm-hero--violet">
                        <div class="tm-hero__icon"><i class="fa fa-layer-group"></i></div>
                        <div class="tm-hero__info">
                            <h5 class="tm-hero__title" id="modal-gerar-lote-label">{{ $tituloGerador ?? 'Geração de turmas em massa' }}</h5>
                            <div class="tm-hero__sub">Crie todas as turmas do ano letivo {{ $anoLetivo->ano }} de uma vez</div>
                        </div>
                        <button type="button" class="tm-hero__close" data-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="modal-body">
                        <div class="tm-sec tm-sec--violet"><i class="fa fa-layer-group"></i> Etapas</div>
                        <div class="tm-chips mb-2">
                            @foreach($etapasOpcoes as $etapa)
                                <label class="tm-chip tm-chip--violet">
                                    <input type="checkbox" class="etapa-check" name="etapas[]" value="{{ $etapa->id }}" checked>
                                    <span>{{ $etapa->nome }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mb-2">Marque as etapas que terão turmas.</small>

                        <div class="tm-sec tm-sec--violet"><i class="fa fa-clock"></i> Turnos <span class="font-weight-normal text-lowercase">(nenhum = sem turno)</span></div>
                        <div class="tm-chips mb-2">
                            @foreach($turnosOpcoes as $valor => $label)
                                <label class="tm-chip tm-chip--violet">
                                    <input type="checkbox" class="turno-check" name="turnos[]" value="{{ $valor }}">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mb-2">Com 2+ turnos, o nome ganha o sufixo do turno (ex.: "1º Ano A — Matutino").</small>

                        <div class="tm-sec tm-sec--violet"><i class="fa fa-sliders-h"></i> Configuração</div>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Sufixos <span class="text-danger">*</span></label>
                                <input type="text" name="sufixos" id="input-sufixos" class="form-control" value="A, B, C, D" required>
                                <small class="text-muted">Letras (A, B, C) ou números (1, 2, 3).</small>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Sala <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="sala" class="form-control" maxlength="40" placeholder="Ex.: 101">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Capacidade <small class="text-muted">(opcional)</small></label>
                                <input type="number" name="capacidade_maxima" class="form-control" min="1" max="500" placeholder="Ex.: 40">
                            </div>
                        </div>

                        <div class="tm-sec tm-sec--violet"><i class="fa fa-eye"></i> Pré-visualização</div>
                        <div id="preview-lote" class="tm-preview"></div>
                    </div>

                    <div class="tm-foot">
                        <span class="tm-foot__hint">Turmas com nome já existente no ano serão puladas.</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-success" id="btn-gerar">
                            <i class="fa fa-check mr-1"></i> Criar turmas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var etapas = @json($etapasOpcoes->mapWithKeys(fn ($e) => [$e->id => $e->nome]));
        var turnos = @json($turnosOpcoes);
        var existentes = @json($nomesExistentes);
        var preview = document.getElementById('preview-lote');
        var btn = document.getElementById('btn-gerar');

        function nomes() {
            var sufixos = document.getElementById('input-sufixos').value
                .split(',').map(function (s) { return s.trim(); }).filter(Boolean);
            var turnosSel = Array.prototype.slice.call(document.querySelectorAll('.turno-check:checked'))
                .map(function (c) { return c.value; });
            var etapasSel = Array.prototype.slice.call(document.querySelectorAll('.etapa-check:checked'))
                .map(function (c) { return c.value; });
            var rotuloTurno = turnosSel.length > 1;
            var nomes = [];

            etapasSel.forEach(function (eid) {
                sufixos.forEach(function (suf) {
                    (turnosSel.length ? turnosSel : [null]).forEach(function (t) {
                        var nome = (etapas[eid] + ' ' + suf + (rotuloTurno && t ? ' — ' + turnos[t] : '')).trim();
                        nomes.push({ nome: nome, existe: existentes.indexOf(nome.toLowerCase()) !== -1 });
                    });
                });
            });
            return nomes;
        }

        function atualizar() {
            var lista = nomes();
            var novos = lista.filter(function (n) { return !n.existe; });
            var repetidos = lista.length - novos.length;

            preview.innerHTML = lista.length === 0
                ? '<span class="text-muted">Selecione etapas, turnos e sufixos para ver a pré-visualização.</span>'
                : '<strong>' + novos.length + ' turma(s) serão criadas</strong>' + (repetidos ? ' · ' + repetidos + ' já existem (serão puladas)' : '') +
                  '<ul class="mb-0 mt-1 pl-3">' +
                  lista.slice(0, 60).map(function (n) {
                      return '<li' + (n.existe ? ' class="text-muted" style="text-decoration: line-through;"' : '') + '>' + n.nome + '</li>';
                  }).join('') +
                  (lista.length > 60 ? '<li class="text-muted">… e mais ' + (lista.length - 60) + '</li>' : '') +
                  '</ul>';
            btn.disabled = novos.length === 0;
        }

        document.querySelectorAll('.etapa-check, .turno-check, #input-sufixos').forEach(function (el) {
            el.addEventListener('change', atualizar);
            el.addEventListener('input', atualizar);
        });
        atualizar();
    })();
    </script>
@endif
