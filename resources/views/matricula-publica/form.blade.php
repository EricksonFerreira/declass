<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Figtree',sans-serif; background:#f1f5f9; color:#0f172a; }
        .topo { background:linear-gradient(135deg,#1e3a8a,#312e81); color:#fff; padding:2rem 1rem; text-align:center; }
        .topo h1 { font-size:1.6rem; font-weight:700; }
        .topo p { opacity:.85; margin-top:.4rem; font-size:.95rem; }
        .card-box { max-width:720px; margin:-1.5rem auto 2rem; padding:0 1rem; }
        .card { background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(15,23,42,.08); padding:1.75rem; }
        h2 { font-size:1.15rem; color:#1e3a8a; margin-bottom:1.25rem; }
        .secao { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin:1.5rem 0 .75rem; border-bottom:1px solid #e2e8f0; padding-bottom:.4rem; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:.9rem; }
        .grid .full { grid-column:1/-1; }
        label { display:block; font-size:.82rem; font-weight:600; margin-bottom:.3rem; color:#334155; }
        label .req { color:#dc2626; }
        input, select, textarea { width:100%; padding:.6rem .75rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; font-family:inherit; background:#fff; }
        input:focus, select:focus, textarea:focus { outline:2px solid #93c5fd; border-color:#3b82f6; }
        .erro { color:#dc2626; font-size:.78rem; margin-top:.25rem; }
        .btn { display:inline-block; width:100%; padding:.8rem; border:none; border-radius:10px; background:#1e3a8a; color:#fff; font-weight:600; font-size:.95rem; cursor:pointer; margin-top:1.25rem; }
        .btn:hover { background:#1d4ed8; }
        .sucesso { text-align:center; padding:2rem 1rem; }
        .sucesso .ico { font-size:3rem; margin-bottom:1rem; }
        .sucesso h2 { margin-bottom:.75rem; }
        .sucesso p { color:#64748b; font-size:.9rem; line-height:1.6; }
        .protocolo { display:inline-block; background:#eff6ff; color:#1d4ed8; font-weight:700; padding:.4rem 1rem; border-radius:8px; margin:.75rem 0; }
        footer { text-align:center; padding:1.5rem; color:#94a3b8; font-size:.78rem; }
        @media (max-width:640px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="topo">
        <h1>{{ $empresa->nome }}</h1>
        <p>{{ $escola->nome }} — Solicitação de matrícula {{ now()->format('Y') }}</p>
    </div>

    <div class="card-box">
        <div class="card">
            @if($enviada)
                <div class="sucesso">
                    <div class="ico">✅</div>
                    <h2>Solicitação enviada!</h2>
                    <p>Sua solicitação de matrícula foi registrada e está na fila de análise da escola.<br>
                    Guarde o protocolo para acompanhamento:</p>
                    <div class="protocolo">#{{ $protocolo }}</div>
                    <p>A escola entrará em contato pelos dados informados.</p>
                </div>
            @else
                <h2>Dados do aluno</h2>
                <form method="POST" action="{{ url('/matricula/'.$empresa->slug.'/'.$escola->slug) }}">
                    @csrf

                    <div class="secao">Período pretendido</div>
                    <div class="grid">
                        <div>
                            <label>Ano letivo</label>
                            <select name="ano_letivo_id">
                                <option value="">—</option>
                                @foreach($anosLetivos as $ano)
                                    <option value="{{ $ano->id }}" {{ old('ano_letivo_id') == $ano->id ? 'selected' : '' }}>{{ $ano->ano }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Etapa pretendida</label>
                            <select name="etapa_id">
                                <option value="">—</option>
                                @foreach($etapas as $etapa)
                                    <option value="{{ $etapa->id }}" {{ old('etapa_id') == $etapa->id ? 'selected' : '' }}>{{ $etapa->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="secao">Aluno</div>
                    <div class="grid">
                        <div class="full">
                            <label>Nome completo <span class="req">*</span></label>
                            <input type="text" name="nome_aluno" value="{{ old('nome_aluno') }}" required maxlength="255" placeholder="Nome completo do aluno">
                            @error('nome_aluno')<div class="erro">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label>Data de nascimento</label>
                            <input type="date" name="data_nascimento_aluno" value="{{ old('data_nascimento_aluno') }}">
                            @error('data_nascimento_aluno')<div class="erro">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label>Sexo</label>
                            <select name="sexo_aluno">
                                <option value="">—</option>
                                <option value="masculino" {{ old('sexo_aluno') === 'masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="feminino" {{ old('sexo_aluno') === 'feminino' ? 'selected' : '' }}>Feminino</option>
                                <option value="outro" {{ old('sexo_aluno') === 'outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                        </div>
                    </div>

                    <div class="secao">Responsável</div>
                    <div class="grid">
                        <div class="full">
                            <label>Nome completo <span class="req">*</span></label>
                            <input type="text" name="nome_responsavel" value="{{ old('nome_responsavel') }}" required maxlength="255" placeholder="Nome de quem responde pelo aluno">
                            @error('nome_responsavel')<div class="erro">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label>Parentesco</label>
                            <select name="parentesco">
                                <option value="">—</option>
                                @foreach(\App\Models\SolicitacaoMatricula::PARENTESCOS as $valor => $label)
                                    <option value="{{ $valor }}" {{ old('parentesco') === $valor ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>CPF</label>
                            <input type="text" name="cpf_responsavel" value="{{ old('cpf_responsavel') }}" maxlength="20" placeholder="000.000.000-00">
                        </div>
                        <div>
                            <label>Telefone / WhatsApp</label>
                            <input type="text" name="telefone_responsavel" value="{{ old('telefone_responsavel') }}" maxlength="30" placeholder="(11) 99999-0000">
                        </div>
                        <div>
                            <label>E-mail</label>
                            <input type="email" name="email_responsavel" value="{{ old('email_responsavel') }}" maxlength="190" placeholder="voce@email.com">
                            @error('email_responsavel')<div class="erro">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="secao">Observações</div>
                    <div class="grid">
                        <div class="full">
                            <label>Informações adicionais</label>
                            <textarea name="observacoes" rows="3" maxlength="4000" placeholder="Alguma informação relevante para a análise?">{{ old('observacoes') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn">Enviar solicitação</button>
                </form>
            @endif
        </div>
    </div>

    <footer>{{ $empresa->nome }} · {{ $escola->nome }}</footer>
</body>
</html>
