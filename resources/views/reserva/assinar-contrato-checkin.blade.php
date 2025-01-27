<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assinatura Digital</title>
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
</head>
<body>
  <h1>Assinatura Digital</h1>
  <canvas id="signature-pad" width="500" height="200"></canvas>
  <button id="clear">Limpar</button>
  <button id="save">Salvar</button>
  <p id="status" style="color: green;"></p>

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
        document.getElementById("status").textContent = "Assinatura salva!";
        console.log("Assinatura Base64:", dataURL);
      }
    });
  </script>
</body>
</html>
