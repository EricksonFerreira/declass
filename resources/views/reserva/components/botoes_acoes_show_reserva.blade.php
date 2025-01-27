<div class="btn-group" role="group" aria-label="Ações da Reserva">
    <a href="{{ route('reserva.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Voltar
    </a>

    @if(in_array($reserva->statusReserva->id, [1])) {{--  Criada  --}}
        <a href="{{ route('reserva.edit', $reserva->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-edit mr-1"></i> Editar Reserva
        </a>
    @endif

    @if(in_array($reserva->statusReserva->id, [1])) {{--  Criada  --}}
        <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-credit-card mr-1"></i> Efetuar Pagamento
        </a>
    @endif

    @if(in_array($reserva->statusReserva->id, [2]) && $reserva->contratoCheckin && $reserva->contratoCheckin == null) {{--  Confirmada  --}}
        <a href="{{route('reserva.checkin',$reserva->id)}}" class="btn btn-sm btn-outline-warning" id="btnCheckInReserva" >
            <i class="fas fa-check mr-1"></i> Criar Contrato
        </a>
    @endif

    @if(in_array($reserva->statusReserva->id, [2]) && $reserva->contratoCheckin && $reserva->contratoCheckin != null) {{--  Confirmada  --}}
        <a href="{{route('reserva.assinarContratoCheckin',$reserva->id)}}" class="btn btn-sm btn-outline-warning" id="btnCheckInReserva" >
            <i class="fas fa-check mr-1"></i> Assinar Contrato
        </a>
    @endif

    @if(in_array($reserva->statusReserva->id, [1,2])) {{--  Criada ou Confirmada  --}}
        <button type="button" class="btn btn-sm btn-outline-danger" id="btnCancelarReserva">
            <i class="fas fa-times mr-1"></i> Cancelar Reserva
        </button>
    @endif

    @if(in_array($reserva->statusReserva->id, [4])) {{--  Em andamento  --}}
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
