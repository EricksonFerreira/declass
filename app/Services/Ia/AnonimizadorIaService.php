<?php

namespace App\Services\Ia;

use Illuminate\Support\Facades\DB;

/**
 * Anonização LGPD antes de enviar texto à IA: troca documentos, contatos
 * e nomes conhecidos (empresa, clientes, colaboradores) por marcadores.
 * Os fatos permanecem — a IA entende o conteúdo sem expor pessoas.
 */
class AnonimizadorIaService
{
    /**
     * Regex de PII estruturado + nomes conhecidos do tenant.
     * Ordem importa: CNPJ antes de CEP (14 dígitos), nomes maiores antes.
     */
    public function anonimizar(string $texto, int $empresaId): string
    {
        $padroes = [
            '/\b\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}\b/' => '[CNPJ]',
            '/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/' => '[CPF]',
            '/\b[\w.+-]+@[\w-]+\.[\w.]{2,}\b/' => '[E-MAIL]',
            '/\(?\d{2}\)?\s?9?\d{4}[-\s]?\d{4}\b/' => '[TELEFONE]',
            '/\b\d{5}-?\d{3}\b/' => '[CEP]',
        ];

        $texto = preg_replace(array_keys($padroes), array_values($padroes), $texto) ?? $texto;

        return $this->anonimizarNomes($texto, $empresaId);
    }

    /**
     * Substitui nomes próprios conhecidos (empresa, clientes, usuários)
     * por marcadores — um mapa nome→marcador montado com 3 consultas.
     */
    private function anonimizarNomes(string $texto, int $empresaId): string
    {
        $mapa = collect()
            ->merge(DB::table('empresas')->where('id', $empresaId)->pluck('nome')
                ->mapWithKeys(fn ($n) => [$n => '[EMPRESA]']))
            ->merge(DB::table('clockify_clientes')->where('empresa_id', $empresaId)->pluck('nome')
                ->mapWithKeys(fn ($n) => [$n => '[CLIENTE]']))
            ->merge(DB::table('users')->where('empresa_id', $empresaId)->pluck('nome')
                ->mapWithKeys(fn ($n) => [$n => '[COLABORADOR]']))
            ->filter(fn ($m, $nome) => mb_strlen(trim((string) $nome)) >= 3)
            ->sortByDesc(fn ($m, $nome) => mb_strlen($nome)); // compostos antes dos parciais

        foreach ($mapa as $nome => $marcador) {
            $texto = str_ireplace($nome, $marcador, $texto);
        }

        return $texto;
    }
}
