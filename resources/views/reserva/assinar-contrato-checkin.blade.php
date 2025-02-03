@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />

    <style>
      canvas {
        border: 2px solid #000;
        border-radius: 5px;
        display: block;
        margin: 10px auto;
      }
      button {
        margin: 5px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
      }
    </style>
@stop

@section('content')

    <div class="card card-dark">

            <div class="card-header d-flex pb-0">

            

            </div>
            <div class="card-body">
              <h4 class="font-weight-bold">Assinatura Digital</h4>
              <canvas id="signature-pad" class="m-0" width="500" height="200"></canvas>
              <button class="btn btn-xs btn-warning" id="clear"> <i class="fa fa-eraser"></i> Limpar</button>
          <button class="btn btn-xs btn-primary" id="save"> <i class="fa fa-save"></i> Salvar</button>
              <p id="status" style="color: green;"></p>
            
              <!-- Biblioteca Signature Pad -->
              <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
            </div>
    </div>
@stop

@section('js')
 
  <!-- Biblioteca Signature Pad -->
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

  <!-- Script Principal -->
  <script>
    // Inicializando o Canvas para Assinatura
    const canvas = document.getElementById("signature-pad");
    const signaturePad = new SignaturePad(canvas);

    // Botão para Limpar
    document.getElementById("clear").addEventListener("click", () => {
      signaturePad.clear();
    });

    // Botão para Salvar
    document.getElementById("save").addEventListener("click", () => {
      if (signaturePad.isEmpty()) {
        alert("Por favor, desenhe uma assinatura.");
      } else {
        const dataURL = signaturePad.toDataURL(); // Assinatura em Base64
        const reserva_id = "{!! $reserva->id !!}";
        const veiculo_id = "{!! $reserva->contratoCheckin->veiculo_id !!}";
        const contrato_id = "{!! $reserva->contratoCheckin->id !!}";
        
        // Requisição Ajax para salvar a assinatura
        $.ajax({
          url: "{{ route('reserva.salvarAssinarContratoCheckIn', ['id' => $reserva->id]) }}",
          method: 'POST',
          data: {
            assinatura: dataURL,
            veiculo_id: veiculo_id,
            contrato_id: contrato_id,
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            document.getElementById("status").textContent = "Assinatura salva com sucesso!";
            window.location.href = "{{ route('reserva.show', ['reserva' => $reserva->id]) }}";
          },
          error: function(xhr, status, error) {
            alert("Erro ao salvar assinatura: " + error);
            console.error("Erro:", error);
          }
        });
      }
    });

    document.getElementById("data_emissao_carta_conducao").addEventListener("change", function() {
        const dataEmissao = new Date(this.value);
        const hoje = new Date();
        
        if (dataEmissao >= hoje) {
            alert("A data de emissão deve ser anterior à data atual");
            this.value = '';
        }
    });
  </script>
@endsection
