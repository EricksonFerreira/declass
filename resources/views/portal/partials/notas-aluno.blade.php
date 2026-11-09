{{--
    Bloco de notas de uma matricula: cards por disciplina com media
    em % e as medias de cada periodo letivo (bimestre, semestre...).
    O clique no card (ou num periodo) abre um modal com todas as
    avaliacoes detalhadas; pelo periodo o modal ja rola ate ele.
    Usado na tela Notas do portal e na aba Notas do hub da turma.
    Espera: $matricula, $notasMatricula (disciplina => notas),
    $mediasMatricula (disciplina => media|null).
--}}
@php
    $fmtPct = fn ($v) => number_format($v, 1, ',', '').'%';
    $fmtNota = fn ($v) => number_format($v, 1, ',', '');
    $corMedia = fn ($v) => $v >= 75 ? 'success' : ($v >= 50 ? 'warning' : 'danger');
    $estNota = [
        'success' => ['#e6f4ea', '#1e7e34'],
        'warning' => ['#fff3cd', '#856404'],
        'danger' => ['#fbe7ea', '#a71d2a'],
    ];
    // media em % (nota * 100 / nota_maxima), mesma regra dos controllers do portal
    $mediaDeNotas = function ($lista) {
        $validas = $lista->filter(fn ($n) => $n->nota !== null && $n->avaliacao->nota_maxima > 0);

        return $validas->isEmpty() ? null
            : round($validas->avg(fn ($n) => $n->nota * 100 / $n->avaliacao->nota_maxima), 1);
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
    <span class="font-weight-bold">
        <i class="fas fa-user-graduate mr-1"></i>
        {{ $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome }}
        <small class="text-muted font-weight-normal">· {{ $matricula->turma?->nome ?? 'Sem turma' }} · {{ $matricula->turma?->anoLetivo->ano ?? '' }}</small>
    </span>
    <a href="{{ route('portal.aluno.pdf', ['aluno' => $matricula->aluno_id, 'ano_letivo_id' => $matricula->turma?->ano_letivo_id]) }}"
       class="btn btn-outline-danger btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Baixar boletim
    </a>
</div>

{{-- Cards por disciplina, coloridos como na grade/disciplinas do portal --}}
<div class="row">
    @forelse($notasMatricula as $disciplina => $lista)
        @php
            $media = $mediasMatricula[$disciplina] ?? null;
            $cor = $media !== null ? $corMedia($media) : 'secondary';
            // media geral: verde na media de aprovacao (>= 7 de 10), vermelho abaixo
            $estGeral = $media !== null ? $estNota[$media >= 70 ? 'success' : 'danger'] : null;
            $disc = $lista->first()->avaliacao->disciplina;
            $corD = \App\Services\TurmaGradeService::corDaDisciplina($disc?->id ?? $disciplina);
            $modalId = 'modalNotas-'.$matricula->id.'-'.$loop->index;
            $periodos = $lista
                ->groupBy(fn ($n) => $n->avaliacao->periodo_letivo_id
                    ? 'pl'.$n->avaliacao->periodo_letivo_id
                    : 'tx'.($n->avaliacao->periodo ?: 'sem'))
                ->map(function ($grupo) use ($mediaDeNotas) {
                    $av = $grupo->first()->avaliacao;

                    return [
                        'nome' => $av->periodoLetivo->nome ?? $av->periodo ?? 'Sem período',
                        'ordem' => $av->periodoLetivo->ordem ?? 999,
                        'media' => $mediaDeNotas($grupo),
                        'notas' => $grupo->values(),
                    ];
                })
                ->sortBy('ordem')
                ->values();
        @endphp
        <div class="col-md-6 col-xl-4 d-flex">
            <div class="card card-outline flex-fill np-card" style="border-top-color:{{ $corD['tx'] }};"
                 data-toggle="modal" data-target="#{{ $modalId }}" role="button" tabindex="0"
                 title="Clique para ver as avaliações">
                <div class="card-header d-flex justify-content-between align-items-center py-2"
                     style="background:{{ $corD['bg'] }}; border-bottom:0;">
                    <span class="font-weight-bold text-truncate" style="color:{{ $corD['tx'] }};" title="{{ $disciplina }}">
                        {{ $disciplina }}
                        @if($disc?->sigla)
                            <span class="badge align-middle" style="background:{{ $corD['tx'] }}; color:{{ $corD['bg'] }}">{{ $disc->sigla }}</span>
                        @endif
                    </span>
                    <span class="badge ml-1" style="background:{{ $corD['tx'] }}; color:{{ $corD['bg'] }}">
                        {{ $lista->count() }} {{ $lista->count() === 1 ? 'avaliação' : 'avaliações' }}
                    </span>
                </div>
                <div class="card-body py-2 d-flex flex-column">
                    <div class="d-flex align-items-end justify-content-between mb-1">
                        <div>
                            <small class="text-muted text-uppercase">Média da disciplina</small>
                            <div class="nota-grande text-{{ $cor }}">{{ $media !== null ? $fmtPct($media) : '—' }}</div>
                        </div>
                        @if($media === null)
                            <span class="badge badge-secondary">Aguardando lançamento</span>
                        @endif
                    </div>
                    @if($media !== null)
                        <div class="progress progress-xs mb-2">
                            <div class="progress-bar bg-{{ $cor }}" role="progressbar" style="width: {{ min(100, max(0, $media)) }}%"></div>
                        </div>
                    @endif
                    <div class="np-periodos">
                        @foreach($periodos as $pIdx => $periodo)
                            <button type="button" class="np-periodo" data-toggle="modal" data-target="#{{ $modalId }}" data-anchor="#{{ $modalId }}-p{{ $pIdx }}">
                                <span class="text-truncate mr-2">
                                    <i class="far fa-calendar-check fa-fw mr-1 text-muted"></i>{{ $periodo['nome'] }}
                                </span>
                                @if($periodo['media'] !== null)
                                    <span class="badge badge-{{ $corMedia($periodo['media']) }}">{{ $fmtPct($periodo['media']) }}</span>
                                @else
                                    <span class="badge badge-light border text-muted">—</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <small class="text-muted mt-auto pt-2">
                        <i class="fas fa-mouse-pointer mr-1"></i>Clique no card ou em um período para ver as avaliações
                    </small>
                </div>
            </div>
        </div>

        {{-- Modal com o detalhamento das avaliacoes por periodo --}}
        <div class="modal fade modal-notas" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content mn-card">
                    <div class="mn-head" style="background:linear-gradient(135deg, {{ $corD['tx'] }} 0%, {{ $corD['tx'] }} 55%, rgba(0,0,0,.32) 135%);">
                        <span class="mn-icone"><i class="fas fa-book-open"></i></span>
                        <div class="min-w-0 flex-grow-1">
                            <div class="mn-kind">Rendimento por período</div>
                            <h5 class="mn-titulo mb-0">
                                {{ $disciplina }}
                                @if($disc?->sigla)<span class="mn-sigla">{{ $disc->sigla }}</span>@endif
                            </h5>
                            <div class="mn-sub">
                                {{ $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome }}
                                · {{ $matricula->turma?->nome ?? 'Sem turma' }}
                            </div>
                        </div>
                        <div class="mn-media text-center" @if($estGeral) style="background:{{ $estGeral[0] }}; border-color:transparent; color:{{ $estGeral[1] }};" @endif>
                            <div class="mn-media-num">{{ $media !== null ? $fmtPct($media) : '—' }}</div>
                            <div class="mn-media-label">média geral</div>
                        </div>
                        <button type="button" class="close mn-close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body mn-scroll">
                        @foreach($periodos as $pIdx => $periodo)
                            @php $estP = $periodo['media'] !== null ? $estNota[$corMedia($periodo['media'])] : null; @endphp
                            <section class="mn-periodo" id="{{ $modalId }}-p{{ $pIdx }}">
                                <div class="mn-periodo-head">
                                    <span class="mn-periodo-nome"><i class="far fa-calendar-check fa-fw mr-1"></i>{{ $periodo['nome'] }}</span>
                                    <span class="d-flex align-items-center" style="gap:.45rem;">
                                        <small class="text-muted">{{ $periodo['notas']->count() }} {{ $periodo['notas']->count() === 1 ? 'avaliação' : 'avaliações' }}</small>
                                        @if($periodo['media'] !== null)
                                            <span class="mn-pill" style="background:{{ $estP[0] }}; color:{{ $estP[1] }}">{{ $fmtPct($periodo['media']) }}</span>
                                        @else
                                            <span class="mn-pill" style="background:#eef1f5; color:#8a93a0">—</span>
                                        @endif
                                    </span>
                                </div>
                                @if($periodo['media'] !== null)
                                    <div class="mn-barra"><i class="bg-{{ $corMedia($periodo['media']) }}" style="width: {{ min(100, max(0, $periodo['media'])) }}%"></i></div>
                                @endif
                                @foreach($periodo['notas'] as $nota)
                                    @php
                                        $av = $nota->avaliacao;
                                        $aprov = $nota->nota !== null && $av->nota_maxima > 0 ? $nota->nota / $av->nota_maxima * 100 : null;
                                        $estA = $aprov !== null ? $estNota[$corMedia($aprov)] : null;
                                    @endphp
                                    <div class="mn-av">
                                        <div class="min-w-0">
                                            <div class="mn-av-nome text-truncate" title="{{ $av->nome }}">{{ $av->nome }}</div>
                                            <div class="mn-av-meta">
                                                <span class="badge badge-light border">{{ \App\Models\Avaliacao::TIPOS[$av->tipo] ?? $av->tipo }}</span>
                                                <i class="far fa-calendar-alt fa-fw ml-1"></i> {{ $av->data?->format('d/m/Y') ?? 'sem data' }}
                                                · vale {{ $av->nota_maxima + 0 }} pts
                                            </div>
                                        </div>
                                        @if($aprov !== null)
                                            <span class="mn-nota" style="background:{{ $estA[0] }}; color:{{ $estA[1] }}">
                                                {{ $fmtNota($nota->nota) }}<small>/{{ $av->nota_maxima + 0 }}</small>
                                            </span>
                                        @else
                                            <span class="mn-nota mn-nota-vazia" title="Nota ainda não lançada">—</span>
                                        @endif
                                    </div>
                                @endforeach
                            </section>
                        @endforeach
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="far fa-star fa-2x mb-2 d-block"></i>
                    Nenhuma nota lançada ainda.
                </div>
            </div>
        </div>
    @endforelse
</div>

@once
<style>
    .nota-grande { font-size: 1.9rem; font-weight: 700; line-height: 1.1; }
    .progress-xs { height: 6px; }

    .np-card { cursor: pointer; transition: box-shadow .15s; }
    .np-card:hover { box-shadow: 0 .4rem 1.1rem rgba(0,0,0,.14); }
    .np-card:focus { outline: none; box-shadow: 0 0 0 2px rgba(0,123,255,.18); }

    .np-periodos { margin: 0 -.55rem; }
    .np-periodo { display: flex; align-items: center; justify-content: space-between; gap: .5rem; width: 100%; padding: .42rem .55rem; border: 0; background: transparent; border-radius: .45rem; font-size: .83rem; text-align: left; color: #33404f; transition: background .15s; }
    .np-periodo:hover { background: #f1f5fb; }
    .np-periodo:focus { outline: none; box-shadow: 0 0 0 2px rgba(0,123,255,.18); }
    .np-periodo + .np-periodo { border-top: 1px solid #f1f3f6; border-radius: 0 0 .45rem .45rem; }

    .modal-notas .modal-content { border: 0; border-radius: .9rem; overflow: hidden; box-shadow: 0 1.2rem 3rem rgba(0,0,0,.3); }
    .mn-head { display: flex; align-items: center; gap: .85rem; padding: 1.05rem 1.2rem; color: #fff; }
    .mn-icone { flex-shrink: 0; width: 46px; height: 46px; border-radius: .7rem; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.18); font-size: 1.15rem; box-shadow: inset 0 0 0 1px rgba(255,255,255,.25); }
    .mn-kind { font-size: .68rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; opacity: .78; }
    .mn-titulo { font-weight: 700; line-height: 1.25; }
    .mn-sigla { font-size: .7rem; font-weight: 700; background: rgba(255,255,255,.2); border-radius: 999px; padding: .15rem .55rem; vertical-align: middle; }
    .mn-sub { font-size: .78rem; opacity: .85; margin-top: .15rem; }
    .mn-media { flex-shrink: 0; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25); border-radius: .8rem; padding: .45rem .85rem; }
    .mn-media-num { font-size: 1.45rem; font-weight: 800; line-height: 1.05; }
    .mn-media-label { font-size: .62rem; text-transform: uppercase; letter-spacing: .07em; opacity: .8; }
    .mn-close { margin: 0 0 0 .4rem; padding: 0 .2rem; color: #fff; opacity: .7; text-shadow: none; font-size: 1.5rem; line-height: 1; }
    .mn-close:hover { color: #fff; opacity: 1; }
    .modal-notas .modal-body { background: #f7f9fc; max-height: 62vh; overflow-y: auto; padding: 1rem 1.15rem; }
    .mn-periodo { background: #fff; border: 1px solid #e8ecf1; border-radius: .7rem; padding: .85rem .95rem; scroll-margin-top: .5rem; }
    .mn-periodo + .mn-periodo { margin-top: .8rem; }
    .mn-periodo-head { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-bottom: .45rem; }
    .mn-periodo-nome { font-weight: 700; color: #33404f; }
    .mn-pill { font-size: .75rem; font-weight: 800; border-radius: 999px; padding: .18rem .6rem; white-space: nowrap; }
    .mn-barra { height: 5px; border-radius: 999px; background: #eef1f5; overflow: hidden; margin-bottom: .55rem; }
    .mn-barra > i { display: block; height: 100%; border-radius: 999px; }
    .mn-av { display: flex; align-items: center; justify-content: space-between; gap: .7rem; padding: .5rem 0; }
    .mn-av + .mn-av { border-top: 1px dashed #edf0f4; }
    .mn-av-nome { font-weight: 600; font-size: .86rem; color: #33404f; }
    .mn-av-meta { font-size: .72rem; color: #8a93a0; margin-top: .1rem; }
    .mn-nota { font-size: .8rem; font-weight: 800; border-radius: .5rem; padding: .28rem .6rem; white-space: nowrap; }
    .mn-nota small { font-weight: 600; opacity: .75; }
    .mn-nota-vazia { color: #a9b2bd; background: #f1f3f6; }
    .mn-scroll::-webkit-scrollbar { width: 6px; }
    .mn-scroll::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 3px; }
    @media (max-width: 575.98px) {
        .mn-media { display: none; }
    }
</style>
<script>
    // Clique num periodo do card abre o modal ja rolando ate aquele
    // periodo; clique no resto do card abre o modal no topo
    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('keydown', '.np-card', function (e) {
            if ((e.key === 'Enter' || e.key === ' ') && !$(e.target).closest('.np-periodo').length) {
                e.preventDefault();
                $($(this).data('target')).modal('show');
            }
        });
        $('.modal-notas').on('show.bs.modal', function (e) {
            var anchor = $(e.relatedTarget).data('anchor');
            if (!anchor) return;
            $(this).one('shown.bs.modal', function () {
                var $sec = $(anchor);
                var $body = $(this).find('.modal-body');
                if ($sec.length && $body.length) {
                    $body.scrollTop($body.scrollTop() + $sec.position().top - $body.position().top);
                }
            });
        });
    });
</script>
@endonce
