<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Protocolo amigavel da solicitacao de matricula: 6 caracteres
 * (3 letras + 3 digitos embaralhados), unico por tenant.
 * Exibido ao responsavel no formulario publico e usado como
 * chave de busca na fila da secretaria.
 */
return new class extends Migration
{
    private const LETRAS = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // sem I/O (confundem com 1/0)

    private const DIGITOS = '23456789'; // sem 0/1

    public function up(): void
    {
        Schema::table('solicitacoes_matricula', function (Blueprint $table) {
            $table->string('codigo', 6)->nullable()->after('turma_id');
        });

        $this->backfillCodigos();

        Schema::table('solicitacoes_matricula', function (Blueprint $table) {
            $table->unique(['empresa_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::table('solicitacoes_matricula', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'codigo']);
            $table->dropColumn('codigo');
        });
    }

    /** Gera codigo retroativo para solicitacoes ja existentes. */
    private function backfillCodigos(): void
    {
        $usados = [];

        DB::table('solicitacoes_matricula')->orderBy('id')->each(function ($row) use (&$usados) {
            $usados[$row->empresa_id] ??= [];

            do {
                $codigo = self::gerarCodigo();
            } while (isset($usados[$row->empresa_id][$codigo]));

            $usados[$row->empresa_id][$codigo] = true;

            DB::table('solicitacoes_matricula')->where('id', $row->id)->update(['codigo' => $codigo]);
        });
    }

    /** Mesmo formato do model (duplicado: migration nao depende de codigo de app). */
    private static function gerarCodigo(): string
    {
        $chars = array_merge(
            self::sorteio(self::LETRAS, 3),
            self::sorteio(self::DIGITOS, 3),
        );
        shuffle($chars);

        return implode('', $chars);
    }

    /** @return string[] */
    private static function sorteio(string $alfabeto, int $qtd): array
    {
        $max = strlen($alfabeto) - 1;
        $out = [];
        for ($i = 0; $i < $qtd; $i++) {
            $out[] = $alfabeto[random_int(0, $max)];
        }

        return $out;
    }
};
