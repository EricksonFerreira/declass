<?php

namespace App\Services\Importacao;

use RuntimeException;

/**
 * Leitor de CSV tolerante ao Excel BR: detecta delimitador (; ou ,),
 * converte ISO-8859-1 para UTF-8 e normaliza cabecalhos.
 */
class CsvReader
{
    /**
     * @return array{header: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function ler(string $caminho): array
    {
        $conteudo = file_get_contents($caminho);
        if ($conteudo === false) {
            throw new RuntimeException('Não foi possível ler o arquivo.');
        }

        // BOM + encoding: planilhas do Excel BR costumam vir em ISO-8859-1.
        $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
        if (! mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
        }

        $linhas = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', $conteudo),
            fn ($l) => trim((string) $l) !== ''
        ));

        if (count($linhas) < 2) {
            throw new RuntimeException('O arquivo precisa de cabeçalho e ao menos uma linha de dados.');
        }

        $delimitador = $this->detectarDelimitador($linhas[0]);

        $header = array_map(
            fn ($h) => $this->normalizarChave($h),
            str_getcsv($linhas[0], $delimitador, '"', '')
        );

        $rows = [];
        foreach (array_slice($linhas, 1) as $i => $linha) {
            $celulas = str_getcsv($linha, $delimitador, '"', '');
            $rows[$i + 2] = array_map(fn ($c) => trim((string) $c), $celulas); // chave = nº da linha no arquivo
        }

        return ['header' => $header, 'rows' => $rows];
    }

    private function detectarDelimitador(string $primeiraLinha): string
    {
        return substr_count($primeiraLinha, ';') >= substr_count($primeiraLinha, ',') ? ';' : ',';
    }

    /**
     * "Número" → numero, "Código CC" → codigo_cc, "Função" → funcao.
     */
    private function normalizarChave(string $campo): string
    {
        $campo = mb_strtolower(trim($campo));
        $campo = str_replace(
            ['á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'],
            $campo
        );

        return (string) preg_replace('/[^a-z0-9]+/', '_', $campo);
    }

    /**
     * Associa uma linha às chaves normalizadas; aceita aliases por coluna.
     *
     * @param  array<int, string>  $header
     * @param  array<int, string>  $celulas
     * @param  array<string, array<int, string>>  $mapa  chave_canonica => aliases aceitos
     * @return array<string, string>
     */
    public static function mapear(array $header, array $celulas, array $mapa): array
    {
        $posicoes = [];
        foreach ($mapa as $canonico => $aliases) {
            foreach (array_merge([$canonico], $aliases) as $alias) {
                $pos = array_search($alias, $header, true);
                if ($pos !== false) {
                    $posicoes[$canonico] = $pos;
                    break;
                }
            }
        }

        $saida = [];
        foreach ($posicoes as $canonico => $pos) {
            $saida[$canonico] = $celulas[$pos] ?? '';
        }

        return $saida;
    }

    /**
     * Número BR → float: "1.234,56" | "1234,56" | "1234.56" → 1234.56
     */
    public static function numero(string $valor): ?float
    {
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        $valor = preg_replace('/[^\d,.\-]/', '', $valor);

        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor); // milhar BR
        }
        $valor = str_replace(',', '.', $valor);

        return is_numeric($valor) ? (float) $valor : null;
    }
}
