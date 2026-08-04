<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato {{ $numero }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #0f172a; line-height: 1.5; }
        .page { max-width: 800px; margin: 0 auto; padding: 28px; }
        .header { border-bottom: 2px solid #e2e8f0; padding-bottom: 14px; margin-bottom: 18px; }
        .title { font-size: 20px; font-weight: 800; margin: 0 0 6px; }
        .meta { color: #475569; font-size: 11px; }
        .box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 14px; }
        .section-title { font-size: 13px; font-weight: 700; margin: 0 0 8px; }
        .muted { color: #64748b; }
        .signature { margin-top: 28px; display: flex; justify-content: space-between; gap: 24px; }
        .sig-line { width: 48%; border-top: 1px solid #94a3b8; padding-top: 8px; text-align: center; }
        ul { margin: 8px 0 0 20px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <p class="title">Contrato da Empresa</p>
            <div class="meta">Número: {{ $numero }} | Gerado em {{ $geradoEm->format('d/m/Y H:i') }}</div>
        </div>

        <div class="box">
            <div class="section-title">Dados da empresa</div>
            <div><strong>Nome:</strong> {{ $empresa->nome }}</div>
            <div><strong>CNPJ:</strong> {{ $empresa->cnpj ?? '-' }}</div>
            <div><strong>E-mail:</strong> {{ $empresa->email ?? '-' }}</div>
            <div><strong>Telefone:</strong> {{ $empresa->telefone ?? '-' }}</div>
        </div>

        <div class="box">
            <div class="section-title">Cláusulas básicas</div>
            <p class="muted">Este documento registra a relação contratual da empresa no sistema, incluindo a referência interna do contrato e o histórico de geração.</p>
            <ul>
                <li>O contrato fica vinculado à empresa cadastrada no sistema.</li>
                <li>O conteúdo gerado pode ser assinado ou exportado futuramente.</li>
                <li>Os dados registrados seguem o contexto da empresa e do usuário logado.</li>
            </ul>
        </div>

        <div class="box">
            <div class="section-title">Observações</div>
            <p class="muted">Este documento foi gerado automaticamente pelo sistema em {{ $geradoEm->format('d/m/Y H:i') }}.</p>
        </div>

        <div class="signature">
            <div class="sig-line">Representante da empresa</div>
            <div class="sig-line">Responsável pelo sistema</div>
        </div>
    </div>
</body>
</html>
