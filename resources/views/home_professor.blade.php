@extends('adminlte::page')

@section('title', $titulo)

@section('content')
@php
    $usuario = auth()->user();
    $hora = (int) now()->format('H');
    $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
    $primeiroNome = \Illuminate\Support\Str::of((string) $usuario->nome)->explode(' ')->first();
    $dataExtenso = ucfirst(\Carbon\Carbon::now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM'));

    $totalAulasHoje = $aulasHoje->count();
    $tiles = [
        ['icone' => 'fas fa-clock', 'cor' => 'bg-info', 'valor' => $totalAulasHoje, 'label' => 'Aulas hoje', 'url' => '#aulas-hoje'],
        ['icone' => 'fas fa-clipboard-check', 'cor' => 'bg-success', 'valor' => $chamadasHoje.' de '.$totalAulasHoje, 'label' => 'Chamadas feitas', 'url' => '#aulas-hoje'],
        ['icone' => 'fas fa-chalkboard-teacher', 'cor' => 'bg-warning', 'valor' => $totalTurmas, 'label' => 'Minhas turmas', 'url' => route('turma.index')],
        ['icone' => 'fas fa-star-half-alt', 'cor' => 'bg-danger', 'valor' => $avaliacoesSemana, 'label' => 'Avaliações (7 dias)', 'url' => route('avaliacao.agenda')],
        ['icone' => 'fas fa-check-double', 'cor' => 'bg-secondary', 'valor' => $totalCorrecoes, 'label' => 'Para corrigir', 'url' => '#correcoes'],
    ];
@endphp

{{-- Saudacao do professor --}}
<div class="callout callout-info prof-saudacao">
    <img class="prof-avatar" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}">
    <div>
        <h5 class="mb-0">{{ $saudacao }}, {{ $primeiroNome }}</h5>
        <small class="text-muted"><i class="far fa-calendar mr-1"></i>{{ $dataExtenso }}</small>
    </div>
</div>

{{-- Resumo do dia (padrao small-box do AdminLTE) --}}
<div class="row">
    @foreach($tiles as $t)
        <div class="col-md col-6">
            <div class="small-box {{ $t['cor'] }}">
                <div class="inner">
                    <h3>{{ $t['valor'] }}</h3>
                    <p>{{ $t['label'] }}</p>
                </div>
                <div class="icon"><i class="{{ $t['icone'] }}"></i></div>
                <a href="{{ $t['url'] }}" class="small-box-footer">Abrir <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    @endforeach
</div>

{{-- Aulas de hoje com popup de acao --}}
<div class="card" id="aulas-hoje">
    <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-day mr-1"></i> Aulas de hoje</span>
        <a href="{{ route('meus_horarios.index') }}" class="btn btn-outline-primary btn-sm">Ver horários</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                @forelse($aulasHoje as $aula)
                    <tr class="prof-aula-linha" data-toggle="modal" data-target="#aula-acao-modal"
                        data-titulo="{{ $aula['disciplina'] }}"
                        data-subtitulo="{{ $aula['turma'] }}{{ $aula['sala'] ? ' · Sala '.$aula['sala'] : '' }}"
                        data-hora="{{ $aula['inicio'] }}{{ $aula['fim'] ? ' – '.$aula['fim'] : '' }}"
                        data-turma-url="{{ route('turma.show', $aula['turma_id']) }}"
                        data-chamada-url="{{ route('diario.chamada', ['turma_id' => $aula['turma_id'], 'disciplina_id' => $aula['disciplina_id'], 'data' => $aula['chamada_data']]) }}"
                        style="cursor:pointer;">
                        <td class="text-center" style="width:90px;">
                            <span class="badge badge-primary px-2 py-1">{{ $aula['inicio'] ?: '—' }}</span>
                            @if($aula['fim'])<br><small class="text-muted">{{ $aula['fim'] }}</small>@endif
                        </td>
                        <td class="font-weight-bold">{{ $aula['disciplina'] }}</td>
                        <td class="text-muted">{{ $aula['turma'] }}@if($aula['sala']) · Sala {{ $aula['sala'] }}@endif</td>
                        <td class="text-right text-nowrap" style="width:60px;">
                            <i class="fas fa-chevron-right text-muted"></i>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted py-4">
                            <i class="fas fa-mug-hot mb-1 d-block" style="font-size:1.4rem;opacity:.4;"></i>
                            <strong>Sem aulas hoje</strong><br>
                            <small>Sua grade está livre neste dia.</small>
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>
</div>

{{-- Atividades entregues pelos alunos aguardando correcao, por turma --}}
<div class="card" id="correcoes">
    <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-check-double mr-1"></i> Correções pendentes</span>
        @if($totalCorrecoes > 0)
            <span class="badge badge-danger">{{ $totalCorrecoes }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                @forelse($turmasComCorrecao as $item)
                    <tr onclick="window.location='{{ route('turma.show', $item['turma']->id) }}#tab-tarefas'" style="cursor:pointer;">
                        <td class="font-weight-bold">{{ $item['turma']->nome }}</td>
                        <td class="text-muted">{{ $item['turma']->escola->nome ?? '' }}</td>
                        <td class="text-right text-nowrap" style="width:140px;">
                            <span class="badge badge-danger">{{ $item['pendentes'] }} para corrigir</span>
                            <i class="fas fa-chevron-right text-muted ml-1"></i>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted py-4">
                            <i class="fas fa-check-circle mb-1 d-block" style="font-size:1.4rem;opacity:.4;"></i>
                            <strong>Nada para corrigir</strong><br>
                            <small>Nenhuma atividade entregue aguardando correção.</small>
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>
</div>

{{-- Popup de acao rapida: ir para a turma ou fazer a chamada da aula --}}
<div class="modal fade" id="aula-acao-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="min-w-0">
                    <h5 class="modal-title mb-0" data-alvo="titulo"></h5>
                    <small class="text-muted" data-alvo="subtitulo"></small>
                </div>
                <span class="badge badge-primary" data-alvo="hora"></span>
            </div>
            <div class="modal-body">
                <a href="#" class="btn btn-outline-primary btn-block text-left" data-alvo="turma">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Ir para turma
                    <br><small class="text-muted ml-4">Diário, avaliações e alunos</small>
                </a>
                <a href="#" class="btn btn-success btn-block text-left" data-alvo="chamada">
                    <i class="fas fa-clipboard-check mr-1"></i> Fazer chamada
                    <br><small class="text-white-50 ml-4">Registrar presença da aula</small>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .prof-saudacao { display: flex; align-items: center; gap: .8rem; }
    .prof-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .prof-aula-linha td { vertical-align: middle; }
    .prof-aula-linha:hover td { background: #f8fafc; }
    .min-w-0 { min-width: 0; }
</style>
@stop

@push('js')
<script>
    // Preenche o popup com os dados da aula clicada
    $('#aula-acao-modal').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var modal = $(this);
        modal.find('[data-alvo="titulo"]').text(btn.data('titulo'));
        modal.find('[data-alvo="subtitulo"]').text(btn.data('subtitulo'));
        modal.find('[data-alvo="hora"]').text(btn.data('hora'));
        modal.find('[data-alvo="turma"]').attr('href', btn.data('turmaUrl'));
        modal.find('[data-alvo="chamada"]').attr('href', btn.data('chamadaUrl'));
    });
</script>
@endpush
