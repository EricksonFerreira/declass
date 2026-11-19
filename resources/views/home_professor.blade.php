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
        ['icone' => 'fas fa-clock', 'cor' => '#0891b2', 'valor' => $totalAulasHoje, 'label' => 'Aulas hoje', 'url' => '#aulas-hoje'],
        ['icone' => 'fas fa-clipboard-check', 'cor' => '#16a34a', 'valor' => $chamadasHoje.'/'.$totalAulasHoje, 'label' => 'Chamadas feitas', 'url' => '#aulas-hoje'],
        ['icone' => 'fas fa-chalkboard-teacher', 'cor' => '#d97706', 'valor' => $totalTurmas, 'label' => 'Minhas turmas', 'url' => route('turma.index')],
        ['icone' => 'fas fa-star-half-alt', 'cor' => '#dc2626', 'valor' => $avaliacoesSemana, 'label' => 'Avaliações (7d)', 'url' => route('avaliacao.agenda')],
        ['icone' => 'fas fa-check-double', 'cor' => '#7c3aed', 'valor' => $totalCorrecoes, 'label' => 'Para corrigir', 'url' => '#correcoes'],
    ];
@endphp

{{-- Header estilo app --}}
<div class="hs-app-header">
    <img class="hs-app-avatar" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}">
    <div class="hs-app-ola">
        <b>{{ $saudacao }}, {{ $primeiroNome }}</b>
        <small>{{ $dataExtenso }}</small>
    </div>
</div>

{{-- Atalhos estilo tela de celular --}}
<div class="hs-app-grid hs-app-grid--5">
    @foreach($tiles as $t)
        <a href="{{ $t['url'] }}" class="hs-app" style="--app:{{ $t['cor'] }}">
            <span class="hs-app-icone">
                <i class="{{ $t['icone'] }}"></i>
                <em class="hs-app-badge">{{ $t['valor'] }}</em>
            </span>
            <span class="hs-app-label">{{ $t['label'] }}</span>
        </a>
    @endforeach
</div>

{{-- Aulas de hoje: lista estilo app com popup de acao --}}
<div class="hs-sec" id="aulas-hoje"><i class="fa fa-calendar-day mr-1"></i> Aulas de hoje
    <a href="{{ route('meus_horarios.index') }}" class="hs-sec-link">Ver horários</a>
</div>
<div class="hs-lista">
    @forelse($aulasHoje as $aula)
        <a href="#" class="hs-item" style="--app:#007bff" data-toggle="modal" data-target="#aula-acao-modal"
            data-titulo="{{ $aula['disciplina'] }}"
            data-subtitulo="{{ $aula['turma'] }}{{ $aula['sala'] ? ' · Sala '.$aula['sala'] : '' }}"
            data-hora="{{ $aula['inicio'] }}{{ $aula['fim'] ? ' – '.$aula['fim'] : '' }}"
            data-turma-url="{{ route('turma.show', $aula['turma_id']) }}"
            data-chamada-url="{{ route('diario.chamada', ['turma_id' => $aula['turma_id'], 'disciplina_id' => $aula['disciplina_id'], 'data' => $aula['chamada_data']]) }}">
            <span class="hs-item-icone"><i class="fas fa-book-open"></i></span>
            <span class="hs-item-info">
                <b>{{ $aula['disciplina'] }}</b>
                <small>{{ $aula['turma'] }}@if($aula['sala']) · Sala {{ $aula['sala'] }}@endif</small>
            </span>
            <span class="hs-item-qtd">{{ $aula['inicio'] ?: '—' }}</span>
            <i class="fas fa-chevron-right hs-item-seta"></i>
        </a>
    @empty
        <div class="hs-item-vazio">
            <i class="fas fa-mug-hot"></i>
            <b>Sem aulas hoje</b>
            <small>Sua grade está livre neste dia.</small>
        </div>
    @endforelse
</div>

{{-- Atividades entregues aguardando correcao --}}
<div class="hs-sec" id="correcoes"><i class="fa fa-check-double mr-1"></i> Correções pendentes
    @if($totalCorrecoes > 0)<span class="hs-sec-badge">{{ $totalCorrecoes }}</span>@endif
</div>
<div class="hs-lista">
    @forelse($turmasComCorrecao as $item)
        <a href="{{ route('turma.show', $item['turma']->id) }}#tab-tarefas" class="hs-item" style="--app:#7c3aed">
            <span class="hs-item-icone"><i class="fas fa-pen"></i></span>
            <span class="hs-item-info">
                <b>{{ $item['turma']->nome }}</b>
                <small>{{ $item['turma']->escola->nome ?? '' }}</small>
            </span>
            <span class="hs-item-qtd">{{ $item['pendentes'] }}</span>
            <i class="fas fa-chevron-right hs-item-seta"></i>
        </a>
    @empty
        <div class="hs-item-vazio">
            <i class="fas fa-check-circle"></i>
            <b>Nada para corrigir</b>
            <small>Nenhuma atividade entregue aguardando correção.</small>
        </div>
    @endforelse
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

@include('partials.home-app-styles')
<style>
    .hs-app-grid--5 { grid-template-columns:repeat(5,1fr); }
    @media (max-width: 991px) { .hs-app-grid--5 { grid-template-columns:repeat(3,1fr); } }
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
