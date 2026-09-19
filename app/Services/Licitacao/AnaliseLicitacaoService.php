<?php

namespace App\Services\Licitacao;

use App\Models\Empresa;
use App\Models\Licitacao;
use App\Models\LicitacaoAnalise;
use App\Services\Ia\AnonimizadorIaService;
use App\Services\Ia\IaService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Análise de licitação: monta o perfil real da empresa (projetos,
 * equipe, custo-hora) e pede à IA a nota técnica estimada contra
 * os critérios de julgamento do edital (Lei 14.133, art. 36/37).
 * Provedor, chave, modelo, tokens e anonização vêm de Gerenciar →
 * Integração com IA (config por empresa, fallback .env).
 */
class AnaliseLicitacaoService
{
    private const MAX_CHARS_EDITAL = 300_000;

    public function __construct(
        private readonly IaService $ia,
        private readonly AnonimizadorIaService $anonimizador,
    ) {}

    public function analisar(Licitacao $licitacao, int $userId): LicitacaoAnalise
    {
        if (trim((string) $licitacao->texto_edital) === '') {
            throw new RuntimeException('Cole o texto do edital (ou trechos das seções de julgamento e habilitação) antes de analisar.');
        }

        $empresa = Empresa::findOrFail($licitacao->empresa_id);
        $cfg = $this->ia->configEmpresa($licitacao->empresa_id);

        $promptUsuario = $this->promptUsuario($licitacao, $empresa);
        if ($cfg->anonimizar === 'S') {
            $promptUsuario = $this->anonimizador->anonimizar($promptUsuario, $licitacao->empresa_id);
        }

        $resposta = $this->ia->chatJson(
            $licitacao->empresa_id,
            $this->promptSistema($cfg),
            $promptUsuario
        );

        $dados = json_decode($resposta['conteudo'], true);
        if (! is_array($dados)) {
            throw new RuntimeException('A IA não retornou um JSON válido. Tente novamente.');
        }

        return DB::transaction(function () use ($licitacao, $dados, $resposta, $userId) {
            $analise = LicitacaoAnalise::create([
                'empresa_id' => $licitacao->empresa_id,
                'licitacao_id' => $licitacao->id,
                'user_id' => $userId,
                'modelo' => $resposta['modelo'] ?? null,
                'viabilidade' => $this->viabilidade($dados),
                'nota_tecnica_estimada' => $this->nota($dados),
                'parecer' => (string) ($dados['parecer'] ?? ''),
                'criterios' => $dados['criterios'] ?? null,
                'exigencias' => $dados['exigencias'] ?? null,
                'gaps' => $dados['gaps'] ?? null,
                'preco_sugerido' => is_numeric($dados['preco_sugerido'] ?? null) ? $dados['preco_sugerido'] : null,
                'tokens_entrada' => $resposta['tokens_entrada'],
                'tokens_saida' => $resposta['tokens_saida'],
            ]);

            $licitacao->update(['status' => 'analisada']);

            return $analise;
        });
    }

    private function promptSistema(\App\Models\EmpresaIaConfig $cfg): string
    {
        $prompt = <<<'PROMPT'
Você é um consultor especialista em licitações públicas brasileiras sob a Lei 14.133/2021,
com foco em obras e serviços de engenharia. Analise o edital contra o PERFIL REAL da empresa
fornecido e responda SOMENTE com um JSON válido neste schema:

{
  "viabilidade": "viavel" | "atencao" | "inviavel",
  "nota_tecnica_estimada": number (0 a 100, nota técnica estimada da proposta),
  "parecer": "texto corrido com o resumo executivo da análise (3-6 frases)",
  "criterios": [
    {"criterio": "nome do critério de pontuação do edital", "pontos_max": number,
     "pontos_estimados": number, "observacao": "por que essa nota, citando dados da empresa"}
  ],
  "exigencias": [
    {"exigencia": "exigência de habilitação do edital", "atende": true|false,
     "evidencia": "dado da empresa que atende, ou o que falta"}
  ],
  "gaps": ["lista de pendências/impedimentos concretos"],
  "preco_sugerido": number|null (sugestão de preço da proposta, se estimável a partir dos custos da empresa)
}

Regras:
- Pontue cada critério de julgamento do edital usando SOMENTE dados do perfil da empresa.
  Quando faltar dado, pontue conservadoramente e marque a lacuna em "gaps".
- "viavel" = habilitável e competitiva; "atencao" = habilitável com ressalvas;
  "inviavel" = há impedimento de habilitação.
- Não invente atestados, certificações ou dados que não estejam no perfil.
PROMPT;

        $tamanho = [
            'curto' => 'Seja conciso: parecer de até 3 frases e observações de 1 linha.',
            'detalhado' => 'Seja detalhado: parecer completo e observações com evidências e números.',
        ][$cfg->tamanho_resumo] ?? null;
        if ($tamanho) {
            $prompt .= "\n\n".$tamanho;
        }

        if (trim((string) $cfg->instrucoes_extras) !== '') {
            $prompt .= "\n\nInstruções adicionais da empresa:\n".trim((string) $cfg->instrucoes_extras);
        }

        return $prompt;
    }

    private function promptUsuario(Licitacao $l, Empresa $empresa): string
    {
        $texto = mb_substr(trim((string) $l->texto_edital), 0, self::MAX_CHARS_EDITAL);

        return "=== PERFIL DA EMPRESA ===\n".$this->perfilEmpresa($empresa)
            ."\n\n=== LICITAÇÃO ===\n"
            ."Órgão: {$l->orgao}\nObjeto: {$l->objeto}\nEdital nº: ".($l->numero_edital ?: '-')
            ."\nModalidade: ".($l->modalidade ?: '-')
            ."\nCritério de julgamento: ".($l->criterio_julgamento ?: 'não informado — identifique no edital')
            ."\nAbertura: ".($l->data_abertura?->format('d/m/Y') ?: '-')
            ."\nValor estimado: ".($l->valor_estimado ? 'R$ '.number_format((float) $l->valor_estimado, 2, ',', '.') : 'não informado')
            ."\n\n=== TEXTO DO EDITAL ===\n".$texto;
    }

    /**
     * Perfil técnico da empresa: histórico de projetos (atestados),
     * equipe por função e custo-hora médio — o que o edital pontua.
     */
    private function perfilEmpresa(Empresa $empresa): string
    {
        $linhas = [
            "Empresa: {$empresa->nome}",
            'CNPJ: '.($empresa->cnpj ?: 'não informado'),
            'Sede: '.($empresa->endereco ?: 'não informado'),
        ];

        $projetos = DB::table('clockify_projetos')
            ->leftJoin('clockify_clientes', 'clockify_clientes.id', '=', 'clockify_projetos.clockify_cliente_id')
            ->where('clockify_projetos.empresa_id', $empresa->id)
            ->select(
                'clockify_projetos.nome',
                'clockify_projetos.descricao',
                'clockify_projetos.status',
                'clockify_projetos.valor_faturado',
                'clockify_projetos.data_entrega_previsao',
                'clockify_clientes.nome as cliente'
            )
            ->orderByDesc('clockify_projetos.created_at')
            ->limit(20)
            ->get();

        if ($projetos->isNotEmpty()) {
            $linhas[] = "\nProjetos/atestados (".$projetos->count().' recentes):';
            foreach ($projetos as $p) {
                $valor = $p->valor_faturado ? ' — R$ '.number_format((float) $p->valor_faturado, 0, ',', '.') : '';
                $linhas[] = "- {$p->nome} ({$p->status}) para {$p->cliente}{$valor}: "
                    .mb_substr((string) $p->descricao, 0, 140);
            }
        }

        $equipe = DB::table('users')
            ->leftJoin('funcoes', 'funcoes.id', '=', 'users.funcao_id')
            ->where('users.empresa_id', $empresa->id)
            ->where('users.ativo', 1)
            ->select('funcoes.nome as funcao', DB::raw('count(*) as total'), DB::raw('avg(users.custo_hora) as custo_medio'))
            ->groupBy('funcoes.nome')
            ->get();

        if ($equipe->isNotEmpty()) {
            $linhas[] = "\nEquipe técnica disponível:";
            foreach ($equipe as $e) {
                $custo = $e->custo_medio ? ' — custo-hora médio R$ '.number_format((float) $e->custo_medio, 2, ',', '.') : '';
                $linhas[] = "- {$e->total}x ".($e->funcao ?: 'Sem função').$custo;
            }
        }

        return implode("\n", $linhas);
    }

    private function viabilidade(array $dados): ?string
    {
        $v = (string) ($dados['viabilidade'] ?? '');

        return array_key_exists($v, LicitacaoAnalise::VIABILIDADES) ? $v : null;
    }

    private function nota(array $dados): ?float
    {
        $nota = $dados['nota_tecnica_estimada'] ?? null;
        if (! is_numeric($nota)) {
            return null;
        }

        return max(0, min(100, (float) $nota));
    }
}
