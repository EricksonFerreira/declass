<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login concluído</title>
</head>
<body>
    <p>Login concluído. Você pode fechar esta janela.</p>
    <script>
        (function () {
            const payload = @json($payload ?? []);
            const signalKey = 'site_reserva_login_sync';
            const origin = window.location.origin;

            try {
                localStorage.setItem(signalKey, JSON.stringify({
                    name: payload.name || '',
                    email: payload.email || '',
                    ts: Date.now(),
                }));
            } catch (error) {
                // ignore
            }

            if (window.opener) {
                try {
                    window.opener.postMessage({
                        type: 'site-reserva-login-success',
                        payload: payload,
                    }, origin);
                } catch (error) {
                    // ignore
                }
            }

            setTimeout(function () {
                window.close();
            }, 100);
        })();
    </script>
</body>
</html>
