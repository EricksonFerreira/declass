<div class="btn-group" role="group" aria-label="Ações da Reserva">
    <a href="{{ route('reserva.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>

    @if($reserva->podeEditar())
        <a href="{{ route('reserva.edit', $reserva->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-edit mr-1"></i> Editar Reserva
        </a>
    @endif

    @if( $reserva->podeEfetuarPagamento() )
        <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-credit-card mr-1"></i> Efetuar Pagamento
        </a>
    @endif
    

    @if($reserva->podeCriarContrato() || $reserva->podeVerContrato() || $reserva->podeAssinarContrato())
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-file-contract mr-1"></i> Contrato
            </button>
            <div class="dropdown-menu">
                @if($reserva->podeCriarContrato())
                    <a class="dropdown-item border-bottom" href="{{route('reserva.checkin',$reserva->id)}}" id="btnCheckInReserva">
                        <i class="fas fa-plus mr-1"></i> Criar Contrato
                    </a>
                @endif

                @if($reserva->podeVerContrato())
                    <a class="dropdown-item border-bottom" href="{{route('reserva.verContratoCheckin',$reserva->id)}}" id="btnVerContratoCheckinReserva">
                        <i class="fas fa-eye mr-1"></i> Ver Contrato
                    </a>
                @endif

                @if($reserva->podeAssinarContrato())
                    <a class="dropdown-item" href="{{route('reserva.assinarContratoCheckin',$reserva->id)}}" id="btnCheckInReserva">
                        <i class="fas fa-signature mr-1"></i> Assinar Contrato
                    </a>
                @endif
            </div>
        </div>
    @endif

    @if( $reserva->podeFazerCancelamento() )
        <button type="button" class="btn btn-sm btn-outline-danger" id="btnCancelarReserva">
            <i class="fas fa-times mr-1"></i> Cancelar Reserva
        </button>
    @endif

    @if( $reserva->podeFazerCheckout() )
        <button type="button" class="btn btn-sm btn-outline-success" id="btnCheckOutReserva">
            <i class="fas fa-check-circle mr-1"></i> Check-Out
        </button>
    @endif
</div>

<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script src="{{ asset('js/alertConfirm.js') }}"></script>
<script src="{{ asset('js/alertConfirmPequena.js') }}"></script>
<script src="{{ asset('js/ajaxRequest.js') }}"></script>
<script>
    document.getElementById('btnCancelarReserva').addEventListener('click', function() {
        alertConfirm('Cancelar Reserva', 'Tem certeza que deseja cancelar a reserva?', 'warning', 'Sim, cancelar reserva!', 'Não, manter reserva', () => {
            ajaxRequest(
                '{{ route('reserva.cancelar', $reserva->id) }}',
                'POST',
                {
                    _token: '{{ csrf_token() }}'
                },
                () => {
                    alertConfirmPequena('Cancelada!', 'A reserva foi cancelada com sucesso.', 'success');

                    setTimeout(() => {
                        location.reload();
                    }, 2000); // 2 segundos
                }
            ),
            () =>{
                alertConfirmPequena('Problema ao cancelar reserva!', 'A reserva não foi cancelada.', 'error');
            }
        });
    });
</script>
