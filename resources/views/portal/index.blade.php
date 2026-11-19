@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $usuario = auth()->user();
    $isResponsavel = $usuario->tipo_conta === \App\Models\User::TIPO_CONTA_RESPONSAVEL;
    $hora = (int) now()->format('H');
    $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
    $primeiroNome = \Illuminate\Support\Str::of((string) ($usuario->vinculo_nome ?? $usuario->nome))->explode(' ')->first();
    $dataExtenso = ucfirst(\Carbon\Carbon::now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM'));

    $tiles = [
        ['icone' => 'fas fa-tasks', 'cor' => '#0891b2', 'valor' => $tarefasPendentes, 'label' => 'Tarefas pendentes', 'url' => route('portal.tarefas')],
        ['icone' => 'fas fa-bullhorn', 'cor' => '#16a34a', 'valor' => $avisosCount, 'label' => 'Avisos', 'url' => route('portal.avisos')],
        ['icone' => 'fas fa-wallet', 'cor' => '#d97706', 'valor' => $mensalidadesAbertas, 'label' => 'Mensalidades em aberto', 'url' => route('portal.financeiro')],
        ['icone' => 'fas fa-calendar-alt', 'cor' => '#dc2626', 'valor' => $eventosCount, 'label' => 'Próximos eventos', 'url' => route('portal.agenda')],
    ];

    $servicos = [
        ['icone' => 'fas fa-book-open', 'cor' => '#2563eb', 'titulo' => 'Minhas Disciplinas', 'texto' => 'Grade de disciplinas e professores', 'url' => route('portal.disciplinas')],
        ['icone' => 'fas fa-chalkboard', 'cor' => '#0891b2', 'titulo' => 'Turmas', 'texto' => $isResponsavel ? 'Avisos, materiais e pessoas de cada turma' : 'Avisos, materiais, fórum e pessoas de cada turma', 'url' => route('portal.turmas')],
        ['icone' => 'fas fa-calendar-week', 'cor' => '#0d9488', 'titulo' => 'Horários', 'texto' => 'Grade semanal de aulas da turma', 'url' => route('portal.horarios')],
        ['icone' => 'fas fa-calendar-check', 'cor' => '#10b981', 'titulo' => 'Frequência', 'texto' => 'Presenças e faltas por disciplina', 'url' => route('portal.frequencia')],
        ['icone' => 'fas fa-star', 'cor' => '#f59e0b', 'titulo' => 'Notas', 'texto' => 'Notas lançadas nas avaliações', 'url' => route('portal.notas')],
        ['icone' => 'fas fa-exclamation-circle', 'cor' => '#dc2626', 'titulo' => 'Alertas', 'texto' => 'Ocorrências e comunicados para a família', 'url' => route('portal.alertas')],
        ['icone' => 'fas fa-inbox', 'cor' => '#4f46e5', 'titulo' => 'Solicitações', 'texto' => 'Declarações, atendimento e financeiro com a secretaria', 'url' => route('portal.solicitacoes')],
        ['icone' => 'fas fa-wallet', 'cor' => '#059669', 'titulo' => 'Pagamentos', 'texto' => 'Mensalidades, boletos e recibos', 'url' => route('portal.financeiro')],
        ['icone' => 'fas fa-file-alt', 'cor' => '#64748b', 'titulo' => 'Documentos', 'texto' => 'Documentos pedidos pela escola — envie por aqui', 'url' => route('portal.documentos')],
        ['icone' => 'fas fa-book', 'cor' => '#b45309', 'titulo' => 'Empréstimos', 'texto' => 'Livros da biblioteca', 'url' => route('portal.emprestimos')],
    ];
@endphp

{{-- Header estilo app --}}
<div class="hs-app-header">
    <img class="hs-app-avatar" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}">
    <div class="hs-app-ola">
        <b>{{ $saudacao }}, {{ $primeiroNome }}</b>
        <small>{{ $dataExtenso }} · {{ $isResponsavel ? 'Acompanhe seus alunos' : 'Acompanhe seus estudos' }}</small>
    </div>
</div>

{{-- Resumo: atalhos estilo tela de celular --}}
<div class="hs-app-grid hs-app-grid--4">
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

{{-- Alunos vinculados (responsavel): lista estilo app, clique abre popup --}}
@if($isResponsavel)
<div class="hs-sec"><i class="fa fa-user-graduate mr-1"></i> Alunos vinculados</div>
<div class="hs-lista">
    @foreach($alunos as $aluno)
        @php
            $matricula = $matriculas->get($aluno->id);
            $turma = $matricula?->turma;
            $nome = $aluno->pessoa->nome ?? $aluno->nome;
        @endphp
        <a href="#" class="hs-item" style="--app:#2563eb" @if($turma) data-toggle="modal" data-target="#aluno-acao-modal"
            data-titulo="{{ $nome }}"
            data-subtitulo="{{ $turma->nome }}{{ $turma->etapa ? ' · '.$turma->etapa->nome : '' }} · {{ $turma->anoLetivo->ano ?? '' }}"
            data-pdf="{{ route('portal.aluno.pdf', $aluno->id) }}"
            data-calendario="{{ route('portal.calendario', ['aluno_id' => $aluno->id]) }}" @endif>
            <span class="hs-item-icone">
                @if($aluno->foto_url)
                    <img src="{{ $aluno->foto_url }}" alt="{{ $nome }}">
                @else
                    <i class="fas fa-user-graduate"></i>
                @endif
            </span>
            <span class="hs-item-info">
                <b>{{ $nome }}</b>
                <small>@if($turma){{ $turma->nome }}@if($turma->etapa) · {{ $turma->etapa->nome }}@endif · {{ $turma->anoLetivo->ano ?? '' }}@else Sem matrícula ativa no momento.@endif</small>
            </span>
            @if($turma)<i class="fas fa-chevron-right hs-item-seta"></i>@endif
        </a>
    @endforeach
</div>
@endif

{{-- Servicos: grid de icones estilo home screen --}}
<div class="hs-sec"><i class="fa fa-th mr-1"></i> Serviços</div>
<div class="hs-app-grid">
    @foreach($servicos as $s)
        <a href="{{ $s['url'] }}" class="hs-app" style="--app:{{ $s['cor'] }}" title="{{ $s['texto'] }}">
            <span class="hs-app-icone"><i class="{{ $s['icone'] }}"></i></span>
            <span class="hs-app-label">{{ $s['titulo'] }}</span>
            <span class="hs-app-desc">{{ $s['texto'] }}</span>
        </a>
    @endforeach
</div>

{{-- Popup de acao rapida do aluno: boletim, PDF ou calendario --}}
@if($isResponsavel)
<div class="modal fade" id="aluno-acao-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="min-w-0">
                    <h5 class="modal-title mb-0" data-alvo="titulo"></h5>
                    <small class="text-muted" data-alvo="subtitulo"></small>
                </div>
            </div>
            <div class="modal-body">
                <a href="#" class="btn btn-outline-danger btn-block text-left" data-alvo="pdf">
                    <i class="fas fa-file-pdf mr-1"></i> Baixar boletim
                    <br><small class="text-muted ml-4">Boletim completo em PDF</small>
                </a>
                <a href="#" class="btn btn-success btn-block text-left" data-alvo="calendario">
                    <i class="fas fa-calendar-check mr-1"></i> Calendário
                    <br><small class="text-white-50 ml-4">Dias de aula, feriados e eventos</small>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

@include('partials.home-app-styles')
<style>
    .hs-app-grid--4 { grid-template-columns:repeat(4,1fr); }
    @media (max-width: 991px) { .hs-app-grid--4 { grid-template-columns:repeat(3,1fr); } }
    .min-w-0 { min-width: 0; }
</style>
@stop

@push('js')
<script>
    // Preenche o popup com os dados do aluno clicado
    $('#aluno-acao-modal').on('show.bs.modal', function (e) {
        var linha = $(e.relatedTarget);
        var modal = $(this);
        modal.find('[data-alvo="titulo"]').text(linha.data('titulo'));
        modal.find('[data-alvo="subtitulo"]').text(linha.data('subtitulo'));
        modal.find('[data-alvo="pdf"]').attr('href', linha.data('pdf'));
        modal.find('[data-alvo="calendario"]').attr('href', linha.data('calendario'));
    });
</script>
@endpush
