@php
    // Chat do forum estilo WhatsApp Web: lista de conversas a
    // esquerda e conversa ativa a direita, com bolhas (proprias
    // a direita), separadores de data e composer no rodape.
    $euId = auth()->id();
    $paleta = ['#00a884', '#53bdeb', '#e542a3', '#ffa02e', '#2a81cb', '#7f66ff', '#d64045', '#a05eb5', '#0b846f', '#cc4b83'];
    $corAutor = fn ($id) => $paleta[abs((int) $id) % count($paleta)];
    $rotuloData = function ($data) {
        if ($data->isToday()) {
            return 'Hoje';
        }
        if ($data->isYesterday()) {
            return 'Ontem';
        }

        return $data->format('d/m/Y');
    };
    $horaLista = fn ($msg) => $msg->created_at?->isToday()
        ? $msg->created_at->format('H:i')
        : $msg->created_at?->format('d/m/Y');
    $turmaFinalizada = $turma->finalizada();
@endphp

<div class="wa-app">
    <div class="wa-list">
        <div class="wa-list-head">
            <img class="wa-avatar" src="{{ auth()->user()->foto_perfil_url }}" alt="{{ auth()->user()->nome }}">
            <span class="wa-list-head-titulo">Fórum</span>
            @unless($turmaFinalizada)
                <button type="button" class="wa-icon-btn wa-novo-btn" title="Nova conversa"><i class="fas fa-comment-dots"></i></button>
            @endunless
        </div>
        <div class="wa-topics">
            @forelse($posts as $post)
                @php
                    $ultima = $post->respostas->last() ?? $post;
                    $remetente = \Illuminate\Support\Str::before($ultima->autor->nome ?? '', ' ');
                    $preview = $remetente.': '.\Illuminate\Support\Str::limit($ultima->mensagem, 55);
                @endphp
                <div class="wa-topic" data-post="{{ $post->id }}" role="button" tabindex="0">
                    <img class="wa-avatar" src="{{ $post->autor->foto_perfil_url ?? '' }}" alt="{{ $post->autor->nome ?? '' }}">
                    <div class="wa-topic-info">
                        <div class="wa-topic-top">
                            <span class="wa-topic-titulo">@if($post->estaEncerrada())<i class="fas fa-lock wa-lock" title="Conversa encerrada"></i>@endif{{ $post->titulo ?: \Illuminate\Support\Str::limit($post->mensagem, 40) }}</span>
                            <span class="wa-topic-hora {{ ($post->nao_lidas ?? 0) > 0 ? 'wa-hora-nao-lida' : '' }}">{{ $horaLista($ultima) }}</span>
                        </div>
                        <div class="wa-topic-sub">
                            <span class="wa-topic-preview">{{ $preview }}</span>
                            @if(($post->nao_lidas ?? 0) > 0)
                                <span class="wa-topic-count">{{ $post->nao_lidas }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="wa-vazio-lista">
                    <i class="fas fa-comments"></i>
                    <p>Nenhuma conversa ainda.@unless($turmaFinalizada)<br>Toque no botão acima para começar.@endunless</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="wa-pane">
        {{-- Tela de nova conversa (titulo + primeira mensagem) --}}
        <div class="wa-conv wa-conv-novo" data-post="novo">
            <div class="wa-head">
                <button type="button" class="wa-icon-btn wa-voltar" title="Voltar"><i class="fas fa-arrow-left"></i></button>
                <div class="wa-head-info min-w-0">
                    <div class="wa-head-titulo">Nova conversa</div>
                    <div class="wa-head-sub">Mensagem para a turma</div>
                </div>
            </div>
            <form method="POST" action="{{ route('portal.turma.disciplina.forum.store', [$turma->id, $disciplina->id]) }}" class="wa-novo-form">
                @csrf
                <div class="wa-novo-body">
                    <input type="text" name="titulo" class="wa-input" placeholder="Assunto da conversa (opcional)" maxlength="200" autocomplete="off">
                    <textarea name="mensagem" class="wa-input wa-textarea" placeholder="Escreva a mensagem para a turma..." maxlength="3000" required></textarea>
                </div>
                <div class="wa-composer">
                    <span class="wa-novo-dica"><i class="fas fa-users mr-1"></i>Todos da turma podem responder</span>
                    <button type="button" class="wa-icon-btn wa-fmt" data-fmt="*" title="Negrito"><i class="fas fa-bold"></i></button>
                    <button type="button" class="wa-icon-btn wa-fmt" data-fmt="_" title="Itálico"><i class="fas fa-italic"></i></button>
                    <button class="wa-send" type="submit" title="Iniciar conversa"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>

        @foreach($posts as $post)
            @php $totalMsgs = $post->respostas->count() + 1; @endphp
            <div class="wa-conv" data-post="{{ $post->id }}">
                <div class="wa-head">
                    <button type="button" class="wa-icon-btn wa-voltar" title="Voltar"><i class="fas fa-arrow-left"></i></button>
                    <img class="wa-avatar" src="{{ $post->autor->foto_perfil_url ?? '' }}" alt="{{ $post->autor->nome ?? '' }}">
                    <div class="wa-head-info min-w-0">
                        <div class="wa-head-titulo">@if($post->estaEncerrada())<i class="fas fa-lock wa-lock" title="Conversa encerrada"></i>@endif{{ $post->titulo ?: \Illuminate\Support\Str::limit($post->mensagem, 40) }}</div>
                        <div class="wa-head-sub">
                            {{ $post->autor->nome ?? '' }} · {{ $totalMsgs }} {{ $totalMsgs === 1 ? 'mensagem' : 'mensagens' }}{{ $post->estaEncerrada() ? ' · encerrada' : '' }}
                        </div>
                    </div>
                    @if($post->user_id === $euId)
                        <div class="dropdown">
                            <button type="button" class="wa-icon-btn" data-toggle="dropdown" title="Opções da conversa"><i class="fas fa-ellipsis-v"></i></button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button type="button" class="dropdown-item wa-editar-btn"><i class="fas fa-pen mr-2"></i>Editar título</button>
                                <form method="POST" action="{{ route('portal.turma.disciplina.forum.encerrar', [$turma->id, $disciplina->id, $post->id]) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas {{ $post->estaEncerrada() ? 'fa-lock-open' : 'fa-lock' }} mr-2"></i>{{ $post->estaEncerrada() ? 'Reabrir conversa' : 'Encerrar conversa' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
                @if($post->user_id === $euId)
                    <form method="POST" action="{{ route('portal.turma.disciplina.forum.update', [$turma->id, $disciplina->id, $post->id]) }}" class="wa-editar" hidden>
                        @csrf @method('PUT')
                        <input type="text" name="titulo" class="wa-input" maxlength="200" autocomplete="off"
                               value="{{ $post->titulo }}" placeholder="Assunto da conversa">
                        <button class="wa-send" type="submit" title="Salvar título"><i class="fas fa-check"></i></button>
                    </form>
                @endif
                <div class="wa-msgs">
                    @php $dataAnterior = null; $autorAnterior = null; @endphp
                    @foreach(collect([$post])->concat($post->respostas) as $msg)
                        @php
                            $dataMsg = $msg->created_at?->toDateString();
                            $propria = $msg->user_id === $euId;
                            $primeira = $dataMsg !== $dataAnterior || $msg->user_id !== $autorAnterior;
                        @endphp
                        @if($dataMsg !== $dataAnterior)
                            <div class="wa-data"><span>{{ $rotuloData($msg->created_at) }}</span></div>
                        @endif
                        @if(($post->primeira_nao_lida_id ?? null) === $msg->id)
                            <div class="wa-nao-lidas"><span>{{ $post->nao_lidas }} {{ $post->nao_lidas === 1 ? 'mensagem não lida' : 'mensagens não lidas' }}</span></div>
                        @endif
                        <div class="wa-msg {{ $propria ? 'wa-msg-out' : 'wa-msg-in' }} {{ $primeira ? 'wa-tail' : '' }}" data-msg="{{ $msg->id }}">
                            @unless($propria)
                                <div class="wa-autor" style="color:{{ $corAutor($msg->user_id) }}">{{ $msg->autor->nome ?? '' }}</div>
                            @endunless
                            @if($msg->id === $post->id && $msg->titulo)
                                <div class="wa-titulo">{{ $msg->titulo }}</div>
                            @endif
                            <div class="wa-texto">{!! $msg->mensagem_formatada !!}<span class="wa-hora-espaco"></span></div>
                            <span class="wa-hora">
                                {{ $msg->created_at?->format('H:i') }}
                                @if($propria)<i class="fas fa-check-double"></i>@endif
                            </span>
                        </div>
                        @php $dataAnterior = $dataMsg; $autorAnterior = $msg->user_id; @endphp
                    @endforeach
                </div>
                @if($turmaFinalizada)
                    <div class="wa-composer wa-composer-off"><i class="fas fa-lock mr-1"></i> Turma finalizada — fórum somente para consulta</div>
                @elseif($post->estaEncerrada())
                    <div class="wa-composer wa-composer-off"><i class="fas fa-lock mr-1"></i> Conversa encerrada — novas respostas desabilitadas</div>
                @else
                    <form method="POST" action="{{ route('portal.turma.disciplina.forum.comentar', [$turma->id, $disciplina->id, $post->id]) }}" class="wa-composer">
                        @csrf
                        <button type="button" class="wa-icon-btn wa-fmt" data-fmt="*" title="Negrito"><i class="fas fa-bold"></i></button>
                        <button type="button" class="wa-icon-btn wa-fmt" data-fmt="_" title="Itálico"><i class="fas fa-italic"></i></button>
                        <input type="text" name="mensagem" class="wa-input" placeholder="Mensagem" maxlength="2000" required autocomplete="off" data-post="{{ $post->id }}">
                        <button class="wa-send" type="submit" title="Enviar"><i class="fas fa-paper-plane"></i></button>
                    </form>
                @endif
            </div>
        @endforeach

        {{-- Estado vazio do painel (desktop, nenhuma conversa aberta) --}}
        <div class="wa-pane-vazio">
            <i class="fas fa-comments"></i>
            <div class="wa-pane-vazio-titulo">Fórum da disciplina</div>
            <small>Selecione uma conversa ao lado ou inicie uma nova</small>
        </div>
    </div>
</div>
