{{-- Seletor de escola da sessao: filtra todos os dados do tenant.
     Contas de portal (aluno/responsavel) nao trocam de escola:
     veem apenas os proprios dados vinculados. --}}
@if(Auth::check() && ! Auth::user()->isSuperAdmin() && ! Auth::user()->isPortalAccount())
    @php
        $escolasContexto = collect();
        $escolaContextoId = 0;
        $empresaContextoId = app(\App\Support\EmpresaContext::class)->empresaId();

        // So faz sentido com tenant ativo: na visao consolidada do
        // superadmin nao ha como saber a qual escola o dado pertence.
        if ($empresaContextoId !== null && \Illuminate\Support\Facades\Schema::hasTable('escolas')) {
            $permitidas = auth()->user()?->escolasContextoIds();

            $queryEscolas = \App\Models\Escola::query()->orderBy('nome');
            if ($permitidas !== null) {
                $queryEscolas->whereIn('id', $permitidas);
            }

            $escolasContexto = $queryEscolas->get(['id', 'nome']);
            $escolaContextoId = (int) app(\App\Support\EscolaContext::class)->escolaId() ?? 0;
        }
    @endphp

    @if($escolasContexto->count() > 1)
        <div class="escola-context-block">
            <span class="escola-context-label"><i class="fa fa-building mr-1"></i> Escola</span>
            <form method="POST" action="{{ route('escola_context.update') }}" class="mb-0">
                @csrf
                <select
                    name="escola_id"
                    class="form-control form-control-sm escola-context-select"
                    onchange="this.form.submit()"
                    title="Escola da sessão — filtra todos os dados"
                >
                    <option value="0" {{ $escolaContextoId === 0 ? 'selected' : '' }}>Todas as escolas</option>
                    @foreach($escolasContexto as $escolaCtx)
                        <option value="{{ $escolaCtx->id }}" {{ $escolaContextoId === (int) $escolaCtx->id ? 'selected' : '' }}>
                            {{ $escolaCtx->nome }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
@endif
