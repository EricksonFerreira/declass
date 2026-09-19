<?php

namespace App\Actions\Importacao;

use App\Services\Importacao\CsvReader;
use Illuminate\Support\Facades\DB;

/**
 * Importa projetos de CSV. Cabecalhos aceitos (qualquer ordem):
 * nome*; codigo_cc; numero; ano; status; cliente; descricao;
 * horas_orcadas; valor_faturado; estimativas
 *
 * - status: andamento/em_andamento, concluido, congelado, cancelado
 * - cliente: resolvido por nome (cria quando nao existe)
 * - numero: vazio + ano preenchido => proximo sequencial do ano
 * - estimativas: "Funcao:horas|Funcao:horas" → clockify_projeto_estimativas
 * - dedup por (empresa_id, nome)
 */
class ImportarProjetosAction
{
    public const MAPEAMENTO = [
        'nome' => ['projeto', 'titulo', 'name'],
        'codigo' => ['codigo_cc', 'cc', 'centro_de_custo', 'centro_custo'],
        'numero' => ['num', 'sequencial', 'n'],
        'ano' => [],
        'status' => ['situacao'],
        'cliente' => ['cliente_nome'],
        'descricao' => ['descricao', 'escopo', 'objeto'],
        'horas_orcadas' => ['horas_orcada', 'orcado', 'hh_orcado'],
        'valor_faturado' => ['valor', 'faturado', 'valor_total'],
        'estimativas' => ['estimativa', 'estimativa_por_funcao'],
    ];

    private const STATUS_MAP = [
        'andamento' => 'em_andamento',
        'em_andamento' => 'em_andamento',
        'em andamento' => 'em_andamento',
        'concluido' => 'concluido',
        'concluida' => 'concluido',
        'congelado' => 'congelado',
        'congelada' => 'congelado',
        'pausado' => 'congelado',
        'cancelado' => 'cancelado',
        'cancelada' => 'cancelado',
    ];

    public function __construct(private readonly CsvReader $reader) {}

    /**
     * @return array<int, array{linha:int, dados:array<string,string>, erro:string|null}>
     */
    public function analisar(string $caminho, int $empresaId): array
    {
        ['header' => $header, 'rows' => $rows] = $this->reader->ler($caminho);

        $resultado = [];
        foreach ($rows as $numLinha => $celulas) {
            $dados = CsvReader::mapear($header, $celulas, self::MAPEAMENTO);
            $resultado[] = [
                'linha' => $numLinha,
                'dados' => $dados,
                'erro' => $this->validar($dados),
            ];
        }

        return $resultado;
    }

    /**
     * @return array{criados:int, atualizados:int, estimativas:int, erros:array<int, string>}
     */
    public function handle(string $caminho, int $empresaId, ?int $usuarioId = null): array
    {
        $analise = $this->analisar($caminho, $empresaId);
        $resumo = ['criados' => 0, 'atualizados' => 0, 'estimativas' => 0, 'erros' => []];

        foreach ($analise as $item) {
            if ($item['erro']) {
                $resumo['erros'][] = "Linha {$item['linha']}: {$item['erro']}";

                continue;
            }

            try {
                $this->importarLinha($item['dados'], $empresaId, $usuarioId, $resumo);
            } catch (\Throwable $e) {
                $resumo['erros'][] = "Linha {$item['linha']}: {$e->getMessage()}";
            }
        }

        return $resumo;
    }

    private function importarLinha(array $d, int $empresaId, ?int $usuarioId, array &$resumo): void
    {
        $nome = trim($d['nome']);
        $ano = CsvReader::numero($d['ano'] ?? '');
        $ano = $ano ? (int) $ano : null;
        $numero = CsvReader::numero($d['numero'] ?? '');
        $numero = $numero ? (int) $numero : null;

        // Sequencial automatico por ano quando o numero vem vazio.
        if ($ano && ! $numero) {
            $numero = (int) DB::table('clockify_projetos')
                ->where('empresa_id', $empresaId)->where('ano', $ano)->max('numero') + 1;
        }

        $clienteId = $this->resolverCliente($d['cliente'] ?? '', $empresaId);
        $status = self::STATUS_MAP[mb_strtolower(trim($d['status'] ?? ''))] ?? 'em_andamento';

        $attrs = [
            'clockify_cliente_id' => $clienteId,
            'codigo' => trim($d['codigo'] ?? '') ?: null,
            'ano' => $ano,
            'numero' => $numero,
            'status' => $status,
            'descricao' => trim($d['descricao'] ?? '') ?: null,
            'horas_orcadas' => CsvReader::numero($d['horas_orcadas'] ?? ''),
            'valor_faturado' => CsvReader::numero($d['valor_faturado'] ?? ''),
            'publico' => true,
            'ativo' => $status !== 'cancelado',
            'usuatu' => $usuarioId,
            'datatu' => now(),
            'updated_at' => now(),
        ];

        $existente = DB::table('clockify_projetos')
            ->where('empresa_id', $empresaId)->where('nome', $nome)->first();

        if ($existente) {
            DB::table('clockify_projetos')->where('id', $existente->id)->update($attrs);
            $projetoId = (int) $existente->id;
            $resumo['atualizados']++;
        } else {
            $projetoId = (int) DB::table('clockify_projetos')->insertGetId(array_merge($attrs, [
                'empresa_id' => $empresaId,
                'nome' => $nome,
                'usuinc' => $usuarioId,
                'datainc' => now(),
                'created_at' => now(),
            ]));
            $resumo['criados']++;
        }

        $resumo['estimativas'] += $this->importarEstimativas($d['estimativas'] ?? '', $projetoId, $empresaId);
    }

    /**
     * "Engenheiro Civil:530|Projetista CAD/BIM:130" → clockify_projeto_estimativas
     */
    private function importarEstimativas(string $texto, int $projetoId, int $empresaId): int
    {
        $texto = trim($texto);
        if ($texto === '') {
            return 0;
        }

        $gravadas = 0;
        foreach (preg_split('/[|;]/', $texto) as $par) {
            $partes = preg_split('/[:=]/', trim($par), 2);
            if (count($partes) !== 2) {
                continue;
            }
            [$nomeFuncao, $horas] = $partes;
            $horas = CsvReader::numero($horas);
            $nomeFuncao = trim($nomeFuncao);
            if ($nomeFuncao === '' || ! $horas) {
                continue;
            }

            $funcaoId = DB::table('funcoes')->where('empresa_id', $empresaId)->where('nome', $nomeFuncao)->value('id');
            if (! $funcaoId) {
                $funcaoId = (int) DB::table('funcoes')->insertGetId([
                    'empresa_id' => $empresaId, 'nome' => $nomeFuncao,
                    'ativo' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('clockify_projeto_estimativas')->updateOrInsert(
                ['clockify_projeto_id' => $projetoId, 'funcao_id' => $funcaoId],
                ['empresa_id' => $empresaId, 'horas_estimadas' => $horas, 'updated_at' => now(), 'created_at' => now()]
            );
            $gravadas++;
        }

        return $gravadas;
    }

    private function resolverCliente(string $nome, int $empresaId): ?int
    {
        $nome = trim($nome);
        if ($nome === '') {
            return null;
        }

        $cliente = DB::table('clockify_clientes')->where('empresa_id', $empresaId)->where('nome', $nome)->first();
        if ($cliente) {
            return (int) $cliente->id;
        }

        return (int) DB::table('clockify_clientes')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => $nome,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function validar(array $dados): ?string
    {
        if (trim($dados['nome'] ?? '') === '') {
            return 'nome do projeto ausente';
        }

        $status = mb_strtolower(trim($dados['status'] ?? ''));
        if ($status !== '' && ! isset(self::STATUS_MAP[$status])) {
            return "status não reconhecido: {$dados['status']} (use Andamento, Concluído, Congelado ou Cancelado)";
        }

        return null;
    }
}
