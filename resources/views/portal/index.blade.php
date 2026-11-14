@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('css')
@include('partials.home-cards-styles')
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
        ['icone' => 'fas fa-tasks', 'cor' => 'bg-info', 'valor' => $tarefasPendentes, 'label' => 'Tarefas pendentes', 'url' => route('portal.tarefas')],
        ['icone' => 'fas fa-bullhorn', 'cor' => 'bg-success', 'valor' => $avisosCount, 'label' => 'Avisos', 'url' => route('portal.avisos')],
        ['icone' => 'fas fa-wallet', 'cor' => 'bg-warning', 'valor' => $mensalidadesAbertas, 'label' => 'Mensalidades em aberto', 'url' => route('portal.financeiro')],
        ['icone' => 'fas fa-calendar-alt', 'cor' => 'bg-danger', 'valor' => $eventosCount, 'label' => 'Próximos eventos', 'url' => route('portal.agenda')],
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

{{-- Saudacao --}}
<div class="callout callout-info prof-saudacao">
    <img class="prof-avatar" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}">
    <div>
        <h5 class="mb-0">{{ $saudacao }}, {{ $primeiroNome }}</h5>
        <small class="text-muted"><i class="far fa-calendar mr-1"></i>{{ $dataExtenso }} · {{ $isResponsavel ? 'Acompanhe seus alunos' : 'Acompanhe seus estudos' }}</small>
    </div>
</div>

{{-- Resumo (padrao small-box do AdminLTE, igual a tela do professor) --}}
<div class="row">
    @foreach($tiles as $t)
        <div class="col-md-3 col-6">
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

{{-- Alunos vinculados (so no perfil de responsavel): clique abre o popup de acao --}}
@if($isResponsavel)
<div class="card">
    <div class="card-header font-weight-bold">
        <i class="fas fa-user-graduate mr-1"></i> Alunos vinculados
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                @foreach($alunos as $aluno)
                    @php
                        $matricula = $matriculas->get($aluno->id);
                        $turma = $matricula?->turma;
                        $nome = $aluno->pessoa->nome ?? $aluno->nome;
                    @endphp
                    <tr @if($turma) class="portal-aluno-linha" data-toggle="modal" data-target="#aluno-acao-modal"
                        data-titulo="{{ $nome }}"
                        data-subtitulo="{{ $turma->nome }}{{ $turma->etapa ? ' · '.$turma->etapa->nome : '' }} · {{ $turma->anoLetivo->ano ?? '' }}"
                        data-pdf="{{ route('portal.aluno.pdf', $aluno->id) }}"
                        data-calendario="{{ route('portal.calendario', ['aluno_id' => $aluno->id]) }}"
                        style="cursor:pointer;" @endif>
                        <td style="width:52px;">
                            @if($aluno->foto_url)
                                <img class="portal-avatar" src="{{ $aluno->foto_url }}" alt="{{ $nome }}">
                            @else
                                <span class="portal-avatar"><i class="fas fa-user-graduate" aria-hidden="true"></i></span>
                            @endif
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $nome }}</div>
                            @if($turma)
                                <small class="text-muted">{{ $turma->nome }}@if($turma->etapa) · {{ $turma->etapa->nome }}@endif · {{ $turma->anoLetivo->ano ?? '' }}</small>
                            @else
                                <small class="text-muted">Sem matrícula ativa no momento.</small>
                            @endif
                        </td>
                        <td class="text-right text-nowrap" style="width:50px;vertical-align:middle;">
                            @if($turma)<i class="fas fa-chevron-right text-muted"></i>@endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endif

{{-- Demais modulos do portal: mesmo visual dos cards da home --}}
<div class="home-launcher">
    <div class="home-section">
        <div class="home-section-title">Serviços</div>
        <div class="row">
            @foreach($servicos as $s)
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <a href="{{ $s['url'] }}" class="home-card" style="--card-cor: {{ $s['cor'] }};">
                        <span class="home-card-icone"><i class="{{ $s['icone'] }}"></i></span>
                        <span class="home-card-texto">
                            <span class="home-card-nome">{{ $s['titulo'] }}</span>
                            <p class="home-card-desc">{{ $s['texto'] }}</p>
                        </span>
                        <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
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

<style>
    .prof-saudacao { display: flex; align-items: center; gap: .8rem; }
    .prof-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .portal-avatar { width: 36px; height: 36px; border-radius: 50%; background: #eef2f7; color: #4a6da7; display: inline-flex; align-items: center; justify-content: center; object-fit: cover; flex-shrink: 0; }
    .portal-aluno-linha td { vertical-align: middle; }
    .portal-aluno-linha:hover td { background: #f8fafc; }
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
