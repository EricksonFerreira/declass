<html>
    <head>
      <script src="https://sdk.mercadopago.com/js/v2"></script>
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <style>
        .container {
            display: flex;
            justify-content: center;
          width: 800px;
          border: 1px solid #3333;
          padding: 10px;
          border-radius: 1em;
        }
      </style>
    </head>
    <body>
        <h2>tese</h2>

<a href="https://www.mercadopago.com.br/payments/123456789/ticket?caller_id=123456&hash=123e4567-e89b-12d3-a456-426655440000" target="_blank">Pagar com Pix</a>

        <div class="container">
          <div id="cardPaymentBrick_container"></div>
        </div>
      <script>
        const mp = new MercadoPago("{!! env('MP_PUBLIC_KEY') !!}", {
          locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();
        const renderCardPaymentBrick = async (bricksBuilder) => {
          const settings = {
            initialization: {
              amount: 10, // total amount to be paid
              payer: {
                email: "",
              },
            },
            customization: {
              visual: {
                style: {
                //   customVariables: {
                //     theme: 'dark', // | 'dark' | 'bootstrap' | 'flat'
                //   }
                }
              },
                paymentMethods: {
                  maxInstallments: 1,
                }
            },
            callbacks: {
              onReady: () => {
                // callback called when the Brick is ready
              },
              onSubmit: (cardFormData) => {
                console.log("OnSubmit");
                  //  callback called when the user clicks on the submit data button
                  //  example of sending the data collected by our Brick to your server
                  return new Promise((resolve, reject) => {
                    const url = "{{ route('pagamento.teste') }}";
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    console.log(token);

                    fetch( url , {
                    method: "POST",
                    headers: {
                      "Content-Type": "application/json",
                      "X-CSRF-TOKEN": token, // Incluindo o token CSRF aqui
                    },
                    body: JSON.stringify(cardFormData)
                  })
                    .then((response) => {
                      console.log("OnSubmit Then");
                      // receive payment result
                      resolve();
                    })
                    .catch((error) => {
                      console.log("OnSubmit Catch");
                      // handle error response when trying to create payment
                      reject();
                    })
                });
              },
              onError: (error) => {
                console.log("OnError");
                console.log(error);
                // callback called to all error cases related to the Brick
              },
            },
          };
          window.cardPaymentBrickController = await bricksBuilder.create('cardPayment', 'cardPaymentBrick_container', settings);
        };
        renderCardPaymentBrick(bricksBuilder);
      </script>
    </body>
    </html>
