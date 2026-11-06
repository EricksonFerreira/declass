<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DECLASS — Gestão Escolar</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Figtree',sans-serif; background:#f8fafc; color:#0f172a; }
        .hero { background:linear-gradient(135deg,#1e3a8a 0%,#312e81 100%); color:#fff; padding:4rem 1rem; text-align:center; }
        .hero h1 { font-size:2.4rem; font-weight:700; margin-bottom:.75rem; }
        .hero p { font-size:1.1rem; opacity:.85; max-width:640px; margin:0 auto 1.75rem; }
        .hero .btn { display:inline-block; padding:.8rem 1.8rem; border-radius:10px; font-weight:600; text-decoration:none; margin:0 .35rem; }
        .btn-primary { background:#f59e0b; color:#1e293b; }
        .btn-ghost { border:2px solid rgba(255,255,255,.6); color:#fff; }
        .features { max-width:1000px; margin:-2.5rem auto 0; padding:0 1rem; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
        .feature { background:#fff; border-radius:14px; padding:1.4rem; box-shadow:0 10px 30px rgba(15,23,42,.08); }
        .feature h3 { font-size:1rem; margin-bottom:.4rem; color:#1e3a8a; }
        .feature p { font-size:.85rem; color:#64748b; }
        .feature .ico { font-size:1.4rem; margin-bottom:.6rem; }
        footer { text-align:center; padding:2rem; color:#94a3b8; font-size:.8rem; }
    </style>
</head>
<body>
    <section class="hero">
        <h1>DECLASS</h1>
        <p>Plataforma SaaS de gestão escolar multi-tenant: organizações, escolas, usuários, perfis de acesso e documentos em um só lugar.</p>
        <a href="{{ route('login') }}" class="btn btn-ghost">Acessar</a>
        <a href="{{ route('cadastro.empresa.create') }}" class="btn btn-primary">Cadastrar minha escola</a>
    </section>

    <section class="features">
        <div class="feature"><div class="ico">🏫</div><h3>Multi-tenant</h3><p>Cada escola ou rede é um tenant isolado, com dados, configurações e identidade visual próprias.</p></div>
        <div class="feature"><div class="ico">🛡️</div><h3>Perfis de acesso</h3><p>19 perfis escolares prontos + perfis personalizados, com permissões por ação e por registro.</p></div>
        <div class="feature"><div class="ico">🏢</div><h3>Redes e unidades</h3><p>Organizações, mantenedoras e escolas/unidades com vínculos N:N de usuários.</p></div>
        <div class="feature"><div class="ico">📄</div><h3>Documentos</h3><p>Documentos institucionais do tenant com controle de validade e download seguro.</p></div>
    </section>

    <footer>DECLASS — Gestão Escolar SaaS</footer>
</body>
</html>
