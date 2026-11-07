@php $espelhamentoContexto = app(\App\Support\EspelhamentoContext::class); @endphp
@if($espelhamentoContexto->ativo() && ($espelhado = $espelhamentoContexto->alvo()))
    <div class="espelhamento-banner">
        <div class="espelhamento-banner-texto">
            <i class="fas fa-user-secret mr-1"></i>
            <span>
                Espelhando o acesso de <strong>{{ $espelhado->name }}</strong>
                <span class="d-none d-sm-inline">({{ $espelhado->regra?->name ?? 'sem perfil' }})</span>
                — ações continuam no seu usuário
            </span>
        </div>
        <form method="POST" action="{{ route('espelhamento.destroy') }}" class="m-0">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-xs btn-dark">
                <i class="fas fa-times mr-1"></i> Parar
            </button>
        </form>
    </div>
    <style>
        .espelhamento-banner {
            position: fixed;
            top: calc(3.5rem + 1px);
            left: 0;
            right: 0;
            z-index: 1029;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: 0 .9rem;
            background: #fff3cd;
            color: #664d03;
            border-bottom: 1px solid #e8cf8a;
            font-size: .82rem;
        }
        .espelhamento-banner-texto { display: flex; align-items: center; min-width: 0; }
        .espelhamento-banner-texto span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .espelhamento-banner .btn { flex-shrink: 0; }
        .content-wrapper, .main-footer { margin-top: 40px !important; }
        .main-sidebar { top: calc(3.5rem + 1px + 40px) !important; }
    </style>
@endif
