{{--
    Componente principal da operação do veículo.
    Caminho sugerido:
    resources/views/veiculo/components/operacional/index.blade.php
--}}
@php
    $showPercentualDisponibilidade = (int) ($dispResumo['percentual'] ?? 0);
    $showStatusDisponibilidade = (string) ($dispResumo['status_mes'] ?? 'off');

    $showDisponivel = $showStatusDisponibilidade === 'ok' && $showPercentualDisponibilidade >= 100;
    $showParcial = $showStatusDisponibilidade === 'partial'
        || ($showPercentualDisponibilidade > 0 && $showPercentualDisponibilidade < 100);

    $showLabelDisponibilidade = match (true) {
        $showDisponivel => 'Disponível',
        $showParcial => 'Disponibilidade parcial',
        default => 'Indisponível',
    };

    $showIconDisponibilidade = match (true) {
        $showDisponivel => 'fa-check-circle',
        $showParcial => 'fa-adjust',
        default => 'fa-clock',
    };

    $showClassDisponibilidade = match (true) {
        $showDisponivel => 'is-success',
        $showParcial => 'is-warning',
        default => 'is-danger',
    };
@endphp

<section class="vs-shell vs-shell--compact mb-4" aria-labelledby="vehicle-operation-title">
    <header class="vs-compact-head">
        <div>
            <span class="vs-shell__eyebrow">Operação e histórico</span>
            <h3 class="vs-shell__title" id="vehicle-operation-title">Acompanhamento do veículo</h3>
        </div>

        <span class="vs-badge-soft {{ $showClassDisponibilidade }}">
            <i class="fas {{ $showIconDisponibilidade }}"></i>
            {{ $showLabelDisponibilidade }} · {{ $showPercentualDisponibilidade }}%
        </span>
    </header>

    {{--
        FIPE foi removida desta navegação porque já existe na área de dados
        gerenciais acima. Isso evita duas entradas para a mesma informação.
    --}}
    <nav class="vs-steps vs-steps--compact" id="vsSteps" aria-label="Seções operacionais do veículo">
        <button class="vs-step is-active" type="button" data-step="disp">
            <span class="vs-step__icon"><i class="fas fa-calendar-check"></i></span>
            <span class="vs-step__content"><strong>Disponibilidade</strong></span>
            <span class="vs-step__count">{{ $veiculo->reservas->count() }}</span>
        </button>

        <button class="vs-step" type="button" data-step="avarias">
            <span class="vs-step__icon"><i class="fas fa-car-crash"></i></span>
            <span class="vs-step__content"><strong>Avarias</strong></span>
            <span class="vs-step__count {{ $veiculo->veiculoDanos->whereNull('correcao_data')->count() ? 'vs-step__count--alert' : '' }}">
                {{ $veiculo->veiculoDanos->count() }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="os">
            <span class="vs-step__icon"><i class="fas fa-tools"></i></span>
            <span class="vs-step__content"><strong>Ordens de serviço</strong></span>
            <span class="vs-step__count">{{ $veiculo->ordensServico->count() }}</span>
        </button>

        <button class="vs-step" type="button" data-step="mov">
            <span class="vs-step__icon"><i class="fas fa-route"></i></span>
            <span class="vs-step__content"><strong>Movimentações</strong></span>
            <span class="vs-step__count">{{ $veiculo->movimentacoes->count() }}</span>
        </button>

        <button class="vs-step" type="button" data-step="sinistros">
            <span class="vs-step__icon"><i class="fas fa-shield-alt"></i></span>
            <span class="vs-step__content"><strong>Sinistros</strong></span>
            <span class="vs-step__count {{ $veiculo->sinistros->whereIn('status', ['aberto','em_analise','aguardando_documentos'])->count() ? 'vs-step__count--alert' : '' }}">
                {{ $veiculo->sinistros->count() }}
            </span>
        </button>

        <button class="vs-step" type="button" data-step="multas">
            <span class="vs-step__icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span class="vs-step__content"><strong>Multas</strong></span>
            <span class="vs-step__count {{ $veiculo->multas->whereIn('status_multa', ['pendente','em_analise'])->count() ? 'vs-step__count--alert' : '' }}">
                {{ $veiculo->multas->count() }}
            </span>
        </button>
    </nav>

    @include('veiculo.components.operacional.etapas.disponibilidade')
    @include('veiculo.components.operacional.etapas.avarias')
    @include('veiculo.components.operacional.etapas.ordens-servico')
    @include('veiculo.components.operacional.etapas.movimentacoes')
    @include('veiculo.components.operacional.etapas.sinistros')
    @include('veiculo.components.operacional.etapas.multas')
</section>
