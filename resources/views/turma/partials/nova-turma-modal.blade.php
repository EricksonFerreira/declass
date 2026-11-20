{{-- Modal "Nova turma" no contexto de um ano letivo. Variaveis: $anoLetivo, $etapasOpcoes, $turnosOpcoes, $horariosJson, $podeCriarTurma --}}
@if(($podeCriarTurma ?? false) && $anoLetivo)
    @once
        @include('turma.partials.turma-modal-styles')
    @endonce

    <div class="modal fade tm-modal" id="modal-nova-turma" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('turma.store') }}">
                    @csrf
                    <input type="hidden" name="escola_id" value="{{ $anoLetivo->escola_id }}">
                    <input type="hidden" name="ano_letivo_id" value="{{ $anoLetivo->id }}">
                    <input type="hidden" name="from_ano_letivo_id" value="{{ $anoLetivo->id }}">

                    <div class="tm-hero tm-hero--blue">
                        <div class="tm-hero__icon"><i class="fa fa-chalkboard"></i></div>
                        <div class="tm-hero__info">
                            <h5 class="tm-hero__title">Nova turma</h5>
                            <div class="tm-hero__sub">Ano letivo {{ $anoLetivo->ano }} · {{ $anoLetivo->escola->nome ?? '' }}</div>
                        </div>
                        <button type="button" class="tm-hero__close" data-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="modal-body">
                        <div class="tm-sec"><i class="fa fa-graduation-cap"></i> Estrutura</div>
                        <div class="row">
                            <div class="col-md-7 mb-2">
                                <label class="font-weight-bold">Nome da turma <span class="text-danger">*</span></label>
                                <input type="text" name="nome" class="form-control" maxlength="120" required placeholder="Ex.: 1º Ano A">
                            </div>
                            <div class="col-md-5 mb-2">
                                <label class="font-weight-bold">Etapa <span class="text-danger">*</span></label>
                                <select name="etapa_id" class="form-control" required>
                                    <option value="">Selecionar...</option>
                                    @foreach($etapasOpcoes as $etapa)
                                        <option value="{{ $etapa->id }}">{{ $etapa->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="tm-sec"><i class="fa fa-clock"></i> Horário</div>
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label class="font-weight-bold">Turno</label>
                                <select name="turno" id="nt-turno" class="form-control">
                                    <option value="">Sem turno</option>
                                    @foreach($turnosOpcoes as $valor => $label)
                                        <option value="{{ $valor }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7 mb-2">
                                <label class="font-weight-bold">Grade de horário <small class="text-muted">(opcional)</small></label>
                                <select name="horario_id" id="nt-horario" class="form-control">
                                    <option value="">Sem grade</option>
                                </select>
                                <small class="text-muted">O turno filtra as grades da escola.</small>
                            </div>
                        </div>

                        <div class="tm-sec"><i class="fa fa-door-open"></i> Operação</div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="font-weight-bold">Sala <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="sala" class="form-control" maxlength="40" placeholder="Ex.: 101">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="font-weight-bold">Capacidade <small class="text-muted">(opcional)</small></label>
                                <input type="number" name="capacidade_maxima" class="form-control" min="1" max="500" placeholder="Ex.: 40">
                            </div>
                        </div>
                    </div>

                    <div class="tm-foot">
                        <span class="tm-foot__hint">A turma já nasce ativa neste ano letivo.</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check mr-1"></i> Criar turma</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var horarios = @json($horariosJson ?? []);
        var turnoSel = document.getElementById('nt-turno');
        var horarioSel = document.getElementById('nt-horario');

        function preencher() {
            var turno = turnoSel.value;
            horarioSel.querySelectorAll('option:not(:first-child)').forEach(function (o) { o.remove(); });
            horarios.filter(function (h) { return !turno || h.turno === turno; })
                .forEach(function (h) {
                    var o = document.createElement('option');
                    o.value = h.id; o.textContent = h.label;
                    horarioSel.appendChild(o);
                });
        }

        turnoSel.addEventListener('change', preencher);
        $('#modal-nova-turma').on('show.bs.modal', preencher);
    })();
    </script>
@endif
