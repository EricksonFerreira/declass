<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Processando reserva</title>
    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: #ffffff;
            color: #111827;
        }

        .box {
            max-width: 520px;
            width: calc(100% - 32px);
            padding: 28px 24px;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .spinner {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 5px solid #e5e7eb;
            border-top-color: #1f2937;
            margin: 0 auto 16px;
            animation: spin 0.9s linear infinite;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        p {
            margin: 0;
            color: #6b7280;
            line-height: 1.5;
        }

        .error {
            display: none;
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            text-align: left;
            white-space: pre-line;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="spinner" aria-hidden="true"></div>
        <h1>Finalizando sua reserva</h1>
        <p>Aguarde um instante enquanto vinculamos seu login e levamos você para o pagamento.</p>
        <div class="error" id="errorBox"></div>
    </div>

    <script>
        (function () {
            const draftKey = 'site_reserva_login_draft';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const storeUrl = @json(route('site.reserva.store'));
            const errorBox = document.getElementById('errorBox');

            function showError(message) {
                if (!errorBox) {
                    return;
                }
                errorBox.textContent = message;
                errorBox.style.display = 'block';
            }

            async function createReservaFromDraft() {
                let draft = null;
                try {
                    const raw = localStorage.getItem(draftKey);
                    if (raw) {
                        draft = JSON.parse(raw);
                    }
                } catch (error) {
                    draft = null;
                }

                if (!draft || typeof draft !== 'object') {
                    showError('Nao foi possivel recuperar os dados da reserva. Feche esta janela e tente novamente.');
                    return;
                }

                const formData = new FormData();
                Object.entries(draft).forEach(function ([key, value]) {
                    if (value === null || value === undefined) {
                        return;
                    }

                    if (Array.isArray(value) || typeof value === 'object') {
                        formData.append(key, JSON.stringify(value));
                        return;
                    }

                    formData.append(key, String(value));
                });

                try {
                    const response = await fetch(storeUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html,application/xhtml+xml,application/json',
                        },
                        body: formData,
                        redirect: 'follow',
                    });

                    const nextUrl = response.url || '';
                    if (!response.ok || !nextUrl || nextUrl.indexOf('/pagamento') === -1) {
                        const text = await response.text().catch(function () {
                            return '';
                        });
                        showError('Nao foi possivel concluir a reserva automaticamente. Verifique os dados selecionados e tente novamente.');
                        if (text && text.indexOf('error') !== -1) {
                            // keep the draft for another attempt
                        }
                        return;
                    }

                    try {
                        localStorage.removeItem(draftKey);
                    } catch (error) {
                        // ignore
                    }

                    if (window.opener && !window.opener.closed) {
                        window.opener.location.href = nextUrl;
                        window.close();
                        return;
                    }

                    window.location.replace(nextUrl);
                } catch (error) {
                    showError('Erro ao processar sua reserva. Tente novamente em instantes.');
                }
            }

            createReservaFromDraft();
        })();
    </script>
</body>
</html>
