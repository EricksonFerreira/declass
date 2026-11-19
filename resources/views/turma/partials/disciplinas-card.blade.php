<div class="hs-sec">
    <i class="fa fa-book"></i> {{ $isProfessor ? 'Disciplinas que você leciona' : 'Disciplinas ofertadas' }}
    <span class="hs-sec-badge" style="background:#007bff;">{{ $disciplinasVisiveis->count() }}</span>
    @can('turma.disciplina.store')
        @unless($isProfessor)
            @if(count($disciplinasOpcoes) > 0)
                <button type="button" class="te-sec-btn" style="background:#007bff;" data-toggle="modal" data-target="#modal-ofertar-disciplina">
                    <i class="fa fa-plus mr-1"></i> Ofertar disciplina
                </button>
            @endif
        @endunless
    @endcan
</div>

<div class="hs-lista mb-3">
    @forelse($disciplinasVisiveis as $disciplina)
        <div class="hs-item" style="align-items:flex-start;">
            <span class="hs-item-icone" style="--app:#007bff;"><i class="fas fa-book-open"></i></span>
            <div class="hs-item-info">
                <b>
                    <a href="{{ route('turma.disciplina.show', [$turma->id, $disciplina->id]) }}" style="color:inherit;">{{ $disciplina->nome }}</a>
                    @if($disciplina->sigla)<span class="te-chip te-chip--blue">{{ $disciplina->sigla }}</span>@endif
                </b>
                @if($isProfessor)
                    <small>{{ (int) ($minhasAulas[$disciplina->id] ?? 0) }} aula(s)/semana</small>
                @else
                    <div class="d-flex align-items-center flex-wrap mt-1" style="gap:.3rem;">
                        <span class="te-chip" title="Carga horária semanal"><i class="fas fa-clock mr-1"></i>{{ (int) $disciplina->pivot->carga_horaria_semanal }}h/sem</span>
                        <span class="te-chip" title="Média de aprovação"><i class="fas fa-chart-line mr-1"></i>média {{ $disciplina->pivot->media_aprovacao !== null ? number_format((float) $disciplina->pivot->media_aprovacao, 1, ',', '') : number_format((float) $disciplina::MEDIA_APROVACAO_PADRAO, 1, ',', '') }}</span>
                    </div>
                    @can('turma.disciplina.update')
                    <form method="POST" action="{{ route('turma.disciplina.update', [$turma->id, $disciplina->id]) }}" class="te-edit d-none align-items-center flex-wrap mt-1" style="gap:.35rem;">
                        @csrf @method('PUT')
                        <input type="number" name="carga_horaria_semanal" class="form-control form-control-sm" style="width:70px;" min="1" max="40" value="{{ $disciplina->pivot->carga_horaria_semanal }}" title="Carga horária semanal" placeholder="h/sem">
                        <input type="number" name="media_aprovacao" class="form-control form-control-sm" style="width:70px;" min="0" max="10" step="0.1" value="{{ $disciplina->pivot->media_aprovacao }}" placeholder="{{ $disciplina::MEDIA_APROVACAO_PADRAO }}" title="Média de aprovação">
                        <button class="btn btn-xs btn-primary" title="Salvar"><i class="fa fa-check"></i></button>
                    </form>
                    @endcan
                @endif
            </div>
            <div class="ml-auto d-flex" style="gap:.3rem;">
                @if($isProfessor)
                    @can('diario.frequencia')
                        <a href="{{ route('diario.chamada', ['turma_id' => $turma->id, 'disciplina_id' => $disciplina->id, 'data' => now()->toDateString()]) }}" class="btn btn-xs btn-outline-success" title="Fazer chamada desta disciplina"><i class="fa fa-clipboard-check"></i></a>
                    @endcan
                    @can('avaliacao.create')
                        <a href="{{ route('turma.avaliacao.create', $turma->id) }}?disciplina_id={{ $disciplina->id }}" class="btn btn-xs btn-outline-danger" title="Nova avaliação nesta disciplina"><i class="fa fa-star"></i></a>
                    @endcan
                @else
                    @can('turma.disciplina.update')
                        <button type="button" class="btn btn-xs btn-outline-secondary te-edit-toggle" title="Editar carga/média"><i class="fa fa-pen"></i></button>
                    @endcan
                    @can('turma.disciplina.destroy')
                        <form method="POST" action="{{ route('turma.disciplina.destroy', [$turma->id, $disciplina->id]) }}" onsubmit="return confirm('Remover esta disciplina da turma?');" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger" title="Remover disciplina"><i class="fa fa-times"></i></button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    @empty
        <div class="hs-item-vazio">
            <i class="fas fa-book-open"></i>
            {{ $isProfessor ? 'Você não leciona nenhuma disciplina nesta turma.' : 'Nenhuma disciplina ofertada.' }}
        </div>
    @endforelse
</div>

@can('turma.disciplina.store')
    @unless($isProfessor)
    <div class="modal fade" id="modal-ofertar-disciplina" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius:14px;">
                <form method="POST" action="{{ route('turma.disciplina.store', $turma->id) }}">
                    @csrf
                    <div class="modal-header" style="border-radius:14px 14px 0 0;">
                        <h5 class="modal-title font-weight-bold"><i class="fa fa-book mr-1" style="color:#007bff;"></i> Ofertar disciplina</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Disciplina <span class="text-danger">*</span></label>
                            <select name="disciplina_id" class="form-control te-select2" data-placeholder="Digite para buscar..." required>
                                <option value="">Selecionar disciplina...</option>
                                @foreach($disciplinasOpcoes as $did => $dnome)
                                    <option value="{{ $did }}">{{ $dnome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">C.H. semanal</label>
                                    <input type="number" name="carga_horaria_semanal" class="form-control" min="1" max="40" placeholder="—">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Média aprovação</label>
                                    <input type="number" name="media_aprovacao" class="form-control" min="0" max="10" step="0.1" placeholder="{{ \App\Models\Disciplina::MEDIA_APROVACAO_PADRAO }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary"><i class="fa fa-check mr-1"></i> Ofertar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endunless
@endcan

@push('js')
<script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.te-edit-toggle');
        if (!btn) return;
        var item = btn.closest('.hs-item');
        var form = item && item.querySelector('.te-edit');
        if (form) {
            form.classList.toggle('d-none');
            form.classList.toggle('d-flex');
        }
    });
</script>
@endpush
