<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre — DECLASS</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Figtree',sans-serif; background:#f8fafc; color:#0f172a; }
        .wrap { max-width:760px; margin:0 auto; padding:3rem 1rem; }
        h1 { color:#1e3a8a; margin-bottom:1rem; }
        p { color:#475569; line-height:1.7; margin-bottom:1rem; }
        a { color:#1d4ed8; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Sobre o DECLASS</h1>
        <p>O DECLASS é uma plataforma SaaS de gestão escolar multi-tenant, desenvolvida para escolas particulares, públicas e redes educacionais com múltiplas unidades.</p>
        <p>A arquitetura trabalha em três níveis: <strong>tenant</strong> (cliente da plataforma), <strong>organização</strong> (mantenedora ou rede) e <strong>escola/unidade</strong> (estabelecimento físico), com isolamento rigoroso de dados entre tenants.</p>
        <p><a href="{{ route('home') }}">← Voltar</a></p>
    </div>
</body>
</html>
