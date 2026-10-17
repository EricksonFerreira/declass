<?php

namespace App\Actions\Importacao;

use App\Services\Importacao\CsvReader;
use Illuminate\Support\Facades\DB;

/**
 * Importa colaboradores de CSV. Cabecalhos aceitos (qualquer ordem):
 * nome*; email*; usuario; funcao; papel; jornada_diaria; celular
 *
 * Dedup por (empresa_id, email): existente atualiza, novo cria com a
 * senha inicial informada e troca de senha obrigatoria no 1o acesso.
 */
class ImportarUsuariosAction
{
    public const MAPEAMENTO = [
        'nome' => ['nome_completo', 'colaborador', 'name'],
        'email' => ['e_mail', 'mail'],
        'usuario' => ['username', 'login', 'user'],
        'funcao' => ['funcao', 'funcao_nome', 'funcao_nome_'],
        'papel' => ['perfil', 'role'],
        'jornada_diaria' => ['jornada', 'horas_dia'],
        'celular' => ['telefone', 'fone'],
    ];

    private const PAPEIS = [
        'administrador' => 'administrador',
        'admin' => 'administrador',
        'usuario' => 'usuario',
        'lider' => 'usuario',
        'lideranca' => 'usuario',
        'colaborador' => 'usuario',
        'visualizador' => 'usuario',
        'diretor' => 'usuario',
    ];

    public function __construct(private readonly CsvReader $reader) {}

    /**
     * Analisa as linhas sem gravar — usado no preview.
     *
     * @return array<int, array{linha:int, dados:array<string,string>, erro:string|null}>
     */
    public function analisar(string $caminho, int $empresaId): array
    {
        ['header' => $header, 'rows' => $rows] = $this->reader->ler($caminho);

        // usuario -> e-mail do dono, para detectar conflito de unicidade por empresa
        $donoPorUsuario = DB::table('users')
            ->where('empresa_id', $empresaId)
            ->whereNotNull('usuario')
            ->pluck('email', 'usuario')
            ->mapWithKeys(fn ($email, $usuario) => [mb_strtolower((string) $usuario) => mb_strtolower((string) $email)])
            ->all();

        $resultado = [];
        foreach ($rows as $numLinha => $celulas) {
            $dados = CsvReader::mapear($header, $celulas, self::MAPEAMENTO);
            $erro = $this->validar($dados);

            if ($erro === null) {
                $usuario = mb_strtolower(trim($dados['usuario'] ?? ''));
                if ($usuario !== '') {
                    $dono = $donoPorUsuario[$usuario] ?? null;
                    $emailLinha = mb_strtolower(trim($dados['email'] ?? ''));
                    if ($dono !== null && $dono !== $emailLinha) {
                        $erro = "usuario ja em uso: {$usuario}";
                    } else {
                        $donoPorUsuario[$usuario] = $emailLinha;
                    }
                }
            }

            $resultado[] = [
                'linha' => $numLinha,
                'dados' => $dados,
                'erro' => $erro,
            ];
        }

        return $resultado;
    }

    /**
     * Executa a importacao de fato.
     *
     * @return array{criados:int, atualizados:int, erros:array<int, string>}
     */
    public function handle(string $caminho, int $empresaId, string $senhaInicial): array
    {
        $analise = $this->analisar($caminho, $empresaId);

        $resumo = ['criados' => 0, 'atualizados' => 0, 'erros' => []];
        $papelPadrao = $this->resolverPapel('usuario', $empresaId);

        foreach ($analise as $item) {
            if ($item['erro']) {
                $resumo['erros'][] = "Linha {$item['linha']}: {$item['erro']}";

                continue;
            }

            $d = $item['dados'];
            $email = mb_strtolower(trim($d['email']));
            $funcaoId = $this->resolverFuncao($d['funcao'] ?? '', $empresaId);
            $roleId = $this->resolverPapel($d['papel'] ?? '', $empresaId) ?? $papelPadrao;

            $attrs = [
                'nome' => trim($d['nome']),
                'usuario' => mb_strtolower(trim($d['usuario'] ?? '')) ?: null,
                'celular' => trim($d['celular'] ?? '') ?: null,
                'funcao_id' => $funcaoId,
                'role_id' => $roleId,
                'jornada_diaria' => CsvReader::numero($d['jornada_diaria'] ?? '') ?? 8.0,
                'ativo' => true,
                'updated_at' => now(),
            ];

            $existente = DB::table('users')
                ->where('empresa_id', $empresaId)->where('email', $email)->first();

            if ($existente) {
                DB::table('users')->where('id', $existente->id)->update($attrs);
                $resumo['atualizados']++;
            } else {
                DB::table('users')->insert(array_merge($attrs, [
                    'empresa_id' => $empresaId,
                    'email' => $email,
                    'password' => bcrypt($senhaInicial),
                    'forcar_troca_senha' => true,
                    'created_at' => now(),
                ]));
                $resumo['criados']++;
            }
        }

        return $resumo;
    }

    private function validar(array $dados): ?string
    {
        if (trim($dados['nome'] ?? '') === '') {
            return 'nome ausente';
        }
        $email = trim($dados['email'] ?? '');
        if ($email === '') {
            return 'e-mail ausente';
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "e-mail inválido: {$email}";
        }

        return null;
    }

    private function resolverFuncao(string $nome, int $empresaId): ?int
    {
        $nome = trim($nome);
        if ($nome === '') {
            return null;
        }

        $funcao = DB::table('funcoes')->where('empresa_id', $empresaId)->where('nome', $nome)->first();
        if ($funcao) {
            return (int) $funcao->id;
        }

        return (int) DB::table('funcoes')->insertGetId([
            'empresa_id' => $empresaId,
            'nome' => $nome,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolverPapel(string $papel, int $empresaId): ?int
    {
        $papel = mb_strtolower(trim($papel));
        if ($papel === '') {
            return null;
        }

        $nome = self::PAPEIS[$papel] ?? null;
        if (! $nome) {
            return null;
        }

        return DB::table('roles')->where('empresa_id', $empresaId)->where('name', $nome)->value('id')
            ?? DB::table('roles')->whereNull('empresa_id')->where('name', $nome)->value('id');
    }
}
