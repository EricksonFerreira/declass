@php
    $step = auth()->user()?->empresa?->onboardingStep ?? null;
    if (!$step || $step->concluido) return;
    $planoAtivo = auth()->user()?->empresa?->planoAtivo()?->with('plano')->first()?->plano ?? null;
    $pct   = $step->percentualConcluido($planoAtivo);
    $steps = $step->stepsParaExibir($planoAtivo);
    $pendentes = collect($steps)->where('feito', false);
@endphp

@if ($step && !$step->concluido && $pendentes->isNotEmpty())
<div class="onboarding-widget card mb-3" style="border:2px solid #0a1628;border-radius:16px;overflow:hidden;">
    <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap"
         style="background:#0a1628;gap:.5rem;">
        <div class="d-flex align-items-center" style="gap:.6rem;">
            <i class="fas fa-rocket text-white" style="font-size:.9rem;"></i>
            <span style="font-weight:700;font-size:.875rem;color:#fff;">
                Comece a usar o DECLOCK — {{ $pct }}% concluído
            </span>
        </div>
        <div class="d-flex align-items-center" style="gap:.75rem;">
            <span style="font-size:.75rem;color:rgba(255,255,255,.6);">
                {{ $pendentes->count() }} passo(s) restante(s)
            </span>
            <button type="button" class="btn btn-xs" style="background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;padding:.25rem .6rem;"
                    data-toggle="collapse" data-target="#onbWidgetBody" aria-expanded="true">
                <i class="fas fa-chevron-up" id="onbChevron" style="font-size:.7rem;"></i>
            </button>
        </div>
    </div>

    {{-- Barra de progresso --}}
    <div style="height:4px;background:#e2e8f0;">
        <div style="height:100%;width:{{ $pct }}%;background:#22c55e;transition:width .4s ease;"></div>
    </div>

    <div class="collapse show" id="onbWidgetBody">
        <div class="card-body py-2 px-3">
            <div class="row" style="row-gap:.5rem;">
                @foreach ($steps as $s)
                    @php $podeIr = Route::has($s['rota']); @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-center p-2 rounded" style="gap:.65rem;background:{{ $s['feito'] ? '#f0fdf4' : '#f8fafc' }};border:1px solid {{ $s['feito'] ? '#bbf7d0' : '#e2e8f0' }};">
                            <div style="width:30px;height:30px;border-radius:8px;background:{{ $s['feito'] ? '#dcfce7' : '#f1f5f9' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                @if ($s['feito'])
                                    <i class="fas fa-check" style="color:#16a34a;font-size:.75rem;"></i>
                                @else
                                    <i class="fas fa-{{ $s['icone'] }}" style="color:#94a3b8;font-size:.75rem;"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1" style="min-width:0;">
                                <div style="font-size:.78rem;font-weight:{{ $s['feito'] ? '600' : '700' }};color:{{ $s['feito'] ? '#15803d' : '#0f172a' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $s['label'] }}
                                </div>
                            </div>
                            @if (!$s['feito'] && $podeIr)
                                <a href="{{ route($s['rota'], $s['params'] ?? []) }}"
                                   class="btn btn-xs btn-outline-primary flex-shrink-0"
                                   style="font-size:.72rem;padding:.2rem .55rem;border-radius:6px;white-space:nowrap;">
                                    Fazer
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center mt-2 pt-2"
                 style="border-top:1px solid #f1f5f9;">
                <a href="{{ route('onboarding.orientacoes') }}" style="font-size:.78rem;color:#64748b;">
                    <i class="fas fa-list-check mr-1"></i> Ver todos os passos
                </a>
                <form action="{{ route('onboarding.concluir') }}" method="POST" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-secondary" style="font-size:.75rem;"
                            onclick="return confirm('Fechar o guia de configuração? Você pode acessá-lo novamente em Configurações.')">
                        <i class="fas fa-times mr-1"></i> Fechar guia
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.getElementById('onbWidgetBody')?.addEventListener('show.bs.collapse', function () {
    document.getElementById('onbChevron').className = 'fas fa-chevron-up';
});
document.getElementById('onbWidgetBody')?.addEventListener('hide.bs.collapse', function () {
    document.getElementById('onbChevron').className = 'fas fa-chevron-down';
});
</script>
@endpush
@endif
