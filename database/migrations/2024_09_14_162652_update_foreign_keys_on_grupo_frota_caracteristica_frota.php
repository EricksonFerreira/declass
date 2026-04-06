<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeysOnGrupoFrotaCaracteristicaFrota extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('grupo_frota_caracteristica_frota')) {
            return;
        }

        $this->dropForeignIfExists('grupo_frota_caracteristica_frota', ['grupo_frota_id']);
        $this->dropForeignIfExists('grupo_frota_caracteristica_frota', ['caracteristica_frota_id']);

        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'grupo_frota_id', 'grupos_frota', 'cascade');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'grupo_frota_id', 'grupo_frotas', 'cascade');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'caracteristicas_frota', 'cascade');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'caracteristica_frotas', 'cascade');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'caracteristicas_frotas', 'cascade');
    }

    public function down()
    {
        if (! Schema::hasTable('grupo_frota_caracteristica_frota')) {
            return;
        }

        $this->dropForeignIfExists('grupo_frota_caracteristica_frota', ['grupo_frota_id']);
        $this->dropForeignIfExists('grupo_frota_caracteristica_frota', ['caracteristica_frota_id']);

        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'grupo_frota_id', 'grupos_frota', 'restrict');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'grupo_frota_id', 'grupo_frotas', 'restrict');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'caracteristicas_frota', 'restrict');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'caracteristica_frotas', 'restrict');
        $this->addForeignIfPossible('grupo_frota_caracteristica_frota', 'caracteristica_frota_id', 'caracteristicas_frotas', 'restrict');
    }

    private function dropForeignIfExists(string $table, array $columns): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->dropForeign($columns);
            });
        } catch (\Throwable $exception) {
            // Algumas bases antigas nao possuem essas FKs; nesses casos seguimos
            // para aplicar apenas as restricoes que faltam.
        }
    }

    private function addForeignIfPossible(string $table, string $column, string $referencesTable, string $onDelete): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasTable($referencesTable)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $referencesTable, $onDelete) {
                $blueprint->foreign($column)
                    ->references('id')->on($referencesTable)
                    ->onDelete($onDelete);
            });
        } catch (\Throwable $exception) {
            // Ignora bases legadas com estrutura divergente.
        }
    }
}
