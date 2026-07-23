<div class="ma-grid">
    <div class="ma-main">
        <div class="ma-card">
            <div class="ma-card__head">
                <div class="ma-card__icon"><i class="fas fa-route"></i></div>
                <div>
                    <div class="ma-card__title">Vigencia atual</div>
                    <div class="ma-card__sub">Janela ativa da reserva recorrente</div>
                </div>
            </div>
            <div class="ma-card__body">
                <div class="ma-legend">
                    <div class="ma-stop">
                        <div class="ma-dot ma-dot--a"></div>
                        <div>
                            <div class="ma-label">Inicio</div>
                            <div class="ma-place">{{ $dataInicio }}</div>
                        </div>
                    </div>
                    <div class="ma-mid">
                        <div class="ma-mid__line"></div>
                        <div class="ma-mid__pill">
                            {{ $registro->quantidade_dias ?? 0 }} {{ ((int) ($registro->quantidade_dias ?? 0) === 1) ? 'dia' : 'dias' }}
                        </div>
                        <div class="ma-mid__line"></div>
                    </div>
                    <div class="ma-stop ma-stop--right">
                        <div class="ma-dot ma-dot--b"></div>
                        <div>
                            <div class="ma-label">Fim</div>
                            <div class="ma-place">{{ $dataFim }}</div>
                            @if(!is_null($diasRestantes))
                                <div class="ma-date">
                                    @if($diasRestantes > 0)
                                        {{ $diasRestantes }} dia(s) restantes
                                    @elseif($diasRestantes === 0)
                                        vence hoje
                                    @else
                                        vencida ha {{ abs($diasRestantes) }} dia(s)
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="ma-mini">
                    <div class="ma-mini__item">
                        <div class="ma-mini__label">Vigência contratada</div>
                        <div class="ma-mini__value">{{ $inicioVigenciaFormatado }} até {{ $fimVigenciaFormatado }}</div>
                    </div>
                    <div class="ma-mini__item">
                        <div class="ma-mini__label">Veículo</div>
                        <div class="ma-mini__value">{{ $veiculoAtual?->nome ?? 'Sem vínculo' }}</div>
                    </div>
                    <div class="ma-mini__item">
                        <div class="ma-mini__label">Km inicial</div>
                        <div class="ma-mini__value">{{ $registro->quilometragem_inicial ? number_format((int) $registro->quilometragem_inicial, 0, ',', '.') . ' km' : '—' }}</div>
                    </div>
                    <div class="ma-mini__item">
                        <div class="ma-mini__label">Combustível</div>
                        <div class="ma-mini__value">{{ $registro->nivel_combustivel_inicial ?? '—' }}</div>
                    </div>
                </div>

                <div class="ma-actions">
                    @if($podeRenovar)
                        <button type="button" class="ma-btn ma-btn--primary" data-toggle="modal" data-target="#modalRenovarMotoristaApp">
                            <i class="fas fa-arrows-rotate"></i>
                            <span>Renovar plano</span>
                        </button>
                    @endif
                    @if($podeCancelar)
                        <button type="button" class="ma-btn ma-btn--ghost text-danger" data-toggle="modal" data-target="#modalCancelarMotoristaApp">
                            <i class="fas fa-ban"></i>
                            <span>Cancelar reserva</span>
                        </button>
                    @endif
                    <a href="{{ route('reserva.minhas') }}" class="ma-btn ma-btn--ghost">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>

                <div class="ma-note mt-3">
                    Aqui você pode acompanhar o pagamento e renovar o contrato. O cancelamento segue a mesma janela de 24 horas da regra comum do sistema.
                </div>
            </div>
        </div>
    </div>

    <div class="ma-side">
        <div class="ma-card">
            <div class="ma-card__head">
                <div class="ma-card__icon"><i class="fas fa-user"></i></div>
                <div>
                    <div class="ma-card__title">Dados do cliente</div>
                    <div class="ma-card__sub">Informacoes principais do titular</div>
                </div>
            </div>
            <div class="ma-card__body">
                <div class="ma-list">
                    <div class="ma-list__item">
                        <div class="ma-list__label">Nome</div>
                        <div class="ma-list__value">{{ $usuario?->nome ?? $usuario?->name ?? 'Nao informado' }}</div>
                    </div>
                    <div class="ma-list__item">
                        <div class="ma-list__label">Email</div>
                        <div class="ma-list__value">{{ $usuario?->email ?? '-' }}</div>
                    </div>
                    <div class="ma-list__item">
                        <div class="ma-list__label">Telefone</div>
                        <div class="ma-list__value">{{ $usuario?->telefone ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
