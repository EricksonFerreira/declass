<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Power BI Report</title>
    <script src="https://cdn.jsdelivr.net/npm/powerbi-client@2.13.2/dist/powerbi.js"></script>
</head>
<body>
    <div id="reportContainer" style="height: 800px;"></div>

    <script>
        // Obter os dados do servidor
        const embedUrl = @json($embedUrl);
        const accessToken = @json($accessToken);

        // Configuração do relatório
        const models = window['powerbi-client'].models;
        const config = {
            type: 'report',
            tokenType: models.TokenType.Embed,
            accessToken: accessToken,
            embedUrl: embedUrl,
            settings: {
                filterPaneEnabled: false,
                navContentPaneEnabled: false
            }
        };

        // Embutir o relatório
        const reportContainer = document.getElementById('reportContainer');
        const powerbi = new window['powerbi-client'].service.Service(
            window['powerbi-client'].factories.hpmFactory,
            window['powerbi-client'].factories.wpmpFactory,
            window['powerbi-client'].factories.routerFactory
        );

        // Tratamento de erros
        try {
            powerbi.embed(reportContainer, config);
        } catch (error) {
            console.error("Erro ao embutir o relatório:", error);
        }
    </script>
</body>
</html>
