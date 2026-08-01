@php $revisaoCheckoutConcluida = ($reserva->revisao_checkout_concluida ?? 'N') === 'S'; @endphp

{{-- <div class="sticky-actions mt-2 rounded-top p-3">
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-warning font-weight-bold px-4" data-toggle="modal" data-target="#modalOcorrencia">
            <i class="fas fa-exclamation-triangle mr-1"></i> Encontrei danos
        </button>
        @if(! $revisaoCheckoutConcluida)
            <button type="button" class="btn btn-danger font-weight-bold px-4" data-toggle="modal" data-target="#modalInvalidar">
                <i class="fas fa-ban mr-1"></i> Invalidar checkout
            </button>
        @endif
    </div>
</div> --}}
