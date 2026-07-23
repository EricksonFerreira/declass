<div class="page-break"></div>

<div class="clause-page">
    <div class="clause-header">
        <div class="clause-header-bar"></div>
            <div class="clause-header-content">
            <div>
                <div class="clause-header-title">CONDIÇÕES GERAIS DA LOCAÇÃO</div>
                <div class="clause-header-subtitle">
                    Cláusulas contratuais que regem a relação entre LOCADORA e LOCATÁRIO
                </div>
            </div>
        </div>
        <div class="clause-header-bar"></div>
    </div>

    @php
        $todasClausulas = $data['condicoes_gerais'] ?? [];
    @endphp

    @if(!empty($todasClausulas))
        <div class="clause-list">
            @foreach($todasClausulas as $clausula)
                <div class="clause-item">
                    <div class="clause-num">{{ str_pad($clausula['ordem'] ?? $loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="clause-body">
                        <strong>{{ $clausula['titulo'] ?? '' }}</strong>
                        {!! $clausula['texto'] ?? '' !!}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="clause-item">
            <div class="clause-body">Nenhuma condição geral cadastrada para esta empresa.</div>
        </div>
    @endif

    <div class="clause-footer-bar">
        <div class="clause-footer-bar-inner">
            <span>LOCADORA: {{ $get('documento.empresa.nome', 'Locadora') }}</span>
            <span class="clause-footer-bull">&bull;</span>
            <span>Contrato: {{ $get('contrato.numero', '#00000') }}</span>
            <span class="clause-footer-bull">&bull;</span>
            <span>Data: {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>
</div>
