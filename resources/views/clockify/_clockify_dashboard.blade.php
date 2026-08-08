{{--
    Partial: _clockify_dashboard.blade.php
    Painel Clockify exibido na home — calcula dados inline via @php
--}}
@php
    use App\Models\ClockifyProjeto;
    use App\Models\ClockifyTimeTracker;
    use Carbon\Carbon;

    $hoje       = Carbon::today();
    $semanaIni  = Carbon::now()->startOfWeek();
    $semanaFim  = Carbon::now()->endOfWeek();
    $mesIni     = Carbon::now()->startOfMonth();
    $mesFim     = Carbon::now()->endOfMonth();

    // ── Projetos ativos
    $projetosAtivos = ClockifyProjeto::where('ativo', true)->count();

    // ── Trackers da semana (segundos → horas)
    $trackersSemana = ClockifyTimeTracker::whereBetween('inicio', [$semanaIni, $semanaFim])
        ->whereNotNull('fim')->get();
    $horasSemana = $trackersSemana->sum(fn($t) => $t->duration_in_seconds) / 3600;

    // ── Trackers do mês
    $trackersMes = ClockifyTimeTracker::whereBetween('inicio', [$mesIni, $mesFim])
        ->whereNotNull('fim')->get();
    $horasMes = $trackersMes->sum(fn($t) => $t->duration_in_seconds) / 3600;

    // ── Horas hoje
    $trackersHoje = ClockifyTimeTracker::whereDate('inicio', $hoje)
        ->whereNotNull('fim')->get();
    $horasHoje = $trackersHoje->sum(fn($t) => $t->duration_in_seconds) / 3600;

    // ── Top 5 projetos por horas registradas (mês)
    $topProjetos = ClockifyProjeto::where('ativo', true)
        ->with('trackers')
        ->get()
        ->map(function($p) use ($mesIni, $mesFim) {
            $horas = $p->trackers
                ->filter(fn($t) => $t->inicio >= $mesIni && $t->inicio <= $mesFim && $t->fim)
                ->sum(fn($t) => $t->duration_in_seconds) / 3600;
            return ['nome' => $p->nome, 'horas' => round($horas, 1), 'id' => $p->id];
        })
        ->sortByDesc('horas')
        ->take(5)
        ->values();

    $maxHorasTop = $topProjetos->max('horas') ?: 1;

    // ── Projetos em andamento vs concluídos
    $projetosConcluidos = ClockifyProjeto::where('ativo', false)->count();

    // ── Timer rodando agora (sem fim)
    $timerAtivo = ClockifyTimeTracker::whereNull('fim')->first();

    // ── Pessoa mais ativa no mês
    $pessoaAtiva = ClockifyTimeTracker::whereBetween('inicio', [$mesIni, $mesFim])
        ->whereNotNull('fim')
        ->with('user')
        ->get()
        ->groupBy('user_id')
        ->map(fn($g) => [
            'nome'  => optional($g->first()->user)->name ?? 'Usuário',
            'horas' => round($g->sum(fn($t) => $t->duration_in_seconds) / 3600, 1),
        ])
        ->sortByDesc('horas')
        ->first();
@endphp

{{-- ══ CLOCKIFY DASHBOARD ══ --}}
<div style="font-family: 'Plus Jakarta Sans', sans-serif; margin-bottom: 28px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="font-size:22px; font-weight:800; color:#0f172a; margin:0; letter-spacing:-.02em;">
                <i class="fas fa-clock" style="color:#0891b2; margin-right:8px;"></i>
                Painel Clockify
            </h2>
            <p style="color:#64748b; font-size:13px; margin:4px 0 0;">
                {{ Carbon::now()->translatedFormat('l, d \d\e F \d\e Y') }}
                @if($timerAtivo)
                    &nbsp;·&nbsp;<span style="color:#10b981; font-weight:700;"><i class="fas fa-circle" style="font-size:8px; vertical-align:middle;"></i> Timer ativo agora</span>
                @endif
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('clockify.tracker') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px; font-size:12px; font-weight:600;">
                <i class="fas fa-history mr-1"></i> Histórico
            </a>
            <a href="{{ route('clockify.projetos.index') }}" class="btn btn-sm" style="background:#0891b2; color:#fff; border-radius:8px; font-size:12px; font-weight:600; border:none;">
                <i class="fas fa-project-diagram mr-1"></i> Ver Projetos
            </a>
        </div>
    </div>

    {{-- ── KPI Row ── --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap:14px; margin-bottom:20px;">

        {{-- Hoje --}}
        <div style="background:#fff; border-radius:16px; padding:20px 18px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #0891b2; position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                <i class="fas fa-sun" style="color:#f59e0b; margin-right:4px;"></i> Hoje
            </div>
            <div style="font-size:32px; font-weight:800; color:#0f172a; line-height:1;">
                {{ number_format($horasHoje, 1, ',', '.') }}<span style="font-size:16px; font-weight:600; color:#94a3b8; margin-left:2px;">h</span>
            </div>
            <div style="font-size:12px; color:#94a3b8; margin-top:6px;">horas registradas</div>
        </div>

        {{-- Semana --}}
        <div style="background:#fff; border-radius:16px; padding:20px 18px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #7c3aed; position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                <i class="fas fa-calendar-week" style="color:#7c3aed; margin-right:4px;"></i> Esta semana
            </div>
            <div style="font-size:32px; font-weight:800; color:#0f172a; line-height:1;">
                {{ number_format($horasSemana, 1, ',', '.') }}<span style="font-size:16px; font-weight:600; color:#94a3b8; margin-left:2px;">h</span>
            </div>
            <div style="font-size:12px; color:#94a3b8; margin-top:6px;">{{ $semanaIni->format('d/m') }} – {{ $semanaFim->format('d/m') }}</div>
        </div>

        {{-- Mês --}}
        <div style="background:#fff; border-radius:16px; padding:20px 18px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #10b981; position:relative; overflow:hidden;">
            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                <i class="fas fa-calendar-alt" style="color:#10b981; margin-right:4px;"></i> Este mês
            </div>
            <div style="font-size:32px; font-weight:800; color:#0f172a; line-height:1;">
                {{ number_format($horasMes, 1, ',', '.') }}<span style="font-size:16px; font-weight:600; color:#94a3b8; margin-left:2px;">h</span>
            </div>
            <div style="font-size:12px; color:#94a3b8; margin-top:6px;">{{ Carbon::now()->translatedFormat('F Y') }}</div>
        </div>

        {{-- Projetos ativos --}}
        <div style="background:linear-gradient(135deg,#0f172a,#1e293b 60%,#0e7490); border-radius:16px; padding:20px 18px; box-shadow:0 4px 18px rgba(15,23,42,.18); color:#fff;">
            <div style="font-size:11px; font-weight:700; color:rgba(255,255,255,.6); text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                <i class="fas fa-project-diagram" style="color:#38bdf8; margin-right:4px;"></i> Projetos ativos
            </div>
            <div style="font-size:32px; font-weight:800; line-height:1;">{{ $projetosAtivos }}</div>
            <div style="font-size:12px; color:rgba(255,255,255,.5); margin-top:6px;">{{ $projetosConcluidos }} concluídos</div>
        </div>

        {{-- Pessoa mais ativa --}}
        @if($pessoaAtiva)
        <div style="background:#fff; border-radius:16px; padding:20px 18px; box-shadow:0 2px 12px rgba(0,0,0,.06); border-left:4px solid #f59e0b;">
            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">
                <i class="fas fa-trophy" style="color:#f59e0b; margin-right:4px;"></i> + ativa no mês
            </div>
            <div style="font-size:18px; font-weight:800; color:#0f172a; line-height:1.2;">{{ Str::limit($pessoaAtiva['nome'], 16) }}</div>
            <div style="font-size:13px; color:#10b981; font-weight:700; margin-top:6px;">{{ $pessoaAtiva['horas'] }}h registradas</div>
        </div>
        @endif

    </div>

    {{-- ── Linha inferior: Top Projetos + Atalhos ── --}}
    <div style="display:grid; grid-template-columns:1fr 320px; gap:14px; align-items:start;" class="ck-bottom-grid">

        {{-- Top projetos do mês --}}
        <div style="background:#fff; border-radius:16px; padding:22px 22px 18px; box-shadow:0 2px 12px rgba(0,0,0,.06);">
            <div style="font-size:14px; font-weight:800; color:#0f172a; margin-bottom:16px;">
                <i class="fas fa-chart-bar" style="color:#0891b2; margin-right:6px;"></i>
                Top projetos — {{ Carbon::now()->translatedFormat('F') }}
            </div>
            @forelse($topProjetos as $proj)
                @php
                    $pct = $maxHorasTop > 0 ? round(($proj['horas'] / $maxHorasTop) * 100) : 0;
                    $cor = match(true) {
                        $pct >= 80 => '#0891b2',
                        $pct >= 50 => '#7c3aed',
                        default    => '#10b981',
                    };
                @endphp
                <div style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                        <a href="{{ route('clockify.projetos.show', $proj['id']) }}"
                           style="font-size:13px; font-weight:700; color:#1e293b; text-decoration:none; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ $proj['nome'] }}
                        </a>
                        <span style="font-size:13px; font-weight:800; color:{{ $cor }};">{{ $proj['horas'] }}h</span>
                    </div>
                    <div style="height:8px; background:#f1f5f9; border-radius:6px; overflow:hidden;">
                        <div style="height:100%; width:{{ $pct }}%; background:{{ $cor }}; border-radius:6px; transition:width 0.6s ease;"></div>
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:30px 0; color:#94a3b8;">
                    <i class="fas fa-clock" style="font-size:2rem; margin-bottom:8px; display:block;"></i>
                    Nenhuma hora registrada este mês
                </div>
            @endforelse
        </div>

        {{-- Atalhos rápidos --}}
        <div style="display:flex; flex-direction:column; gap:10px;">

            <div style="font-size:13px; font-weight:800; color:#0f172a; margin-bottom:4px; padding-left:2px;">
                <i class="fas fa-bolt" style="color:#f59e0b; margin-right:6px;"></i> Atalhos rápidos
            </div>

            @php
                $atalhos = [
                    ['icon'=>'fa-stopwatch','label'=>'Novo Tracker','url'=>route('clockify.tracker'),'color'=>'#0891b2'],
                    ['icon'=>'fa-folder-plus','label'=>'Novo Projeto','url'=>route('clockify.projetos.create'),'color'=>'#10b981'],
                    ['icon'=>'fa-history','label'=>'Histórico de Tempo','url'=>route('clockify.tracker'),'color'=>'#7c3aed'],
                    ['icon'=>'fa-users','label'=>'Planejar Equipe','url'=>route('clockify.projetos.index'),'color'=>'#f59e0b'],
                ];
            @endphp

            @foreach($atalhos as $at)
                <a href="{{ $at['url'] }}" style="display:flex; align-items:center; gap:12px; background:#fff; border-radius:12px; padding:14px 16px; box-shadow:0 2px 10px rgba(0,0,0,.05); text-decoration:none; transition:transform 0.15s, box-shadow 0.15s;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(0,0,0,.1)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 10px rgba(0,0,0,.05)';">
                    <div style="width:36px; height:36px; border-radius:10px; background:{{ $at['color'] }}1a; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas {{ $at['icon'] }}" style="color:{{ $at['color'] }}; font-size:15px;"></i>
                    </div>
                    <span style="font-size:13px; font-weight:700; color:#1e293b;">{{ $at['label'] }}</span>
                    <i class="fas fa-chevron-right" style="color:#cbd5e1; font-size:11px; margin-left:auto;"></i>
                </a>
            @endforeach

        </div>
    </div>

    {{-- Linha separadora --}}
    <hr style="border:none; border-top:1px solid #e2e8f0; margin:24px 0 20px;">
</div>

<style>
@media (max-width: 768px) {
    .ck-bottom-grid { grid-template-columns: 1fr !important; }
}
</style>
