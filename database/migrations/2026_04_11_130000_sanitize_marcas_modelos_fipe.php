<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('marcas', function (Blueprint $table) {
            if (!Schema::hasColumn('marcas', 'codigo_fipe')) {
                $table->string('codigo_fipe', 20)->nullable()->after('nome');
            }

            if (!Schema::hasColumn('marcas', 'nome_fipe')) {
                $table->string('nome_fipe', 120)->nullable()->after('codigo_fipe');
            }
        });

        Schema::table('modelos', function (Blueprint $table) {
            if (!Schema::hasColumn('modelos', 'codigo_fipe')) {
                $table->string('codigo_fipe', 20)->nullable()->after('nome');
            }

            if (!Schema::hasColumn('modelos', 'nome_fipe')) {
                $table->string('nome_fipe', 120)->nullable()->after('codigo_fipe');
            }
        });

        DB::transaction(function () {
            $this->sanearMarcas();
            $this->sanearModelos();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('modelos', function (Blueprint $table) {
            if (Schema::hasColumn('modelos', 'nome_fipe')) {
                $table->dropColumn('nome_fipe');
            }

            if (Schema::hasColumn('modelos', 'codigo_fipe')) {
                $table->dropColumn('codigo_fipe');
            }
        });

        Schema::table('marcas', function (Blueprint $table) {
            if (Schema::hasColumn('marcas', 'nome_fipe')) {
                $table->dropColumn('nome_fipe');
            }

            if (Schema::hasColumn('marcas', 'codigo_fipe')) {
                $table->dropColumn('codigo_fipe');
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    private function sanearMarcas(): void
    {
        $correcoes = [
            'Mercedes Benz' => 'Mercedes-Benz',
        ];

        foreach (DB::table('marcas')->orderBy('id')->get(['id', 'nome']) as $marca) {
            $nomeOriginal = trim((string) ($marca->nome ?? ''));
            $nomeFipe = $correcoes[$nomeOriginal] ?? $nomeOriginal;
            $nomeFipe = preg_replace('/\s+/', ' ', $nomeFipe) ?: $nomeFipe;

            DB::table('marcas')
                ->where('id', (int) $marca->id)
                ->update([
                    'nome' => $nomeFipe,
                    'nome_fipe' => $nomeFipe,
                    'codigo_fipe' => null,
                ]);
        }

        $marcas = DB::table('marcas')
            ->select('id', 'nome')
            ->orderBy('id')
            ->get();

        $grupos = $marcas->groupBy(function ($marca) {
            return Str::slug(mb_strtolower(trim((string) $marca->nome)), '');
        });

        foreach ($grupos as $grupo) {
            if ($grupo->count() < 2) {
                continue;
            }

            $mantida = $grupo->sortBy('id')->first();
            $duplicadas = $grupo->where('id', '!=', $mantida->id);

            foreach ($duplicadas as $duplicada) {
                DB::table('veiculos')->where('marca_id', (int) $duplicada->id)->update(['marca_id' => (int) $mantida->id]);
                DB::table('modelos')->where('marca_id', (int) $duplicada->id)->update(['marca_id' => (int) $mantida->id]);
                DB::table('marcas')->where('id', (int) $duplicada->id)->delete();
            }
        }
    }

    private function sanearModelos(): void
    {
        $correcoes = [
            '2008 Allure 1.2 Puretech 130CV EAT8' => '2008',
            '5008 Allure 1.5 BlueHDI 130CV EAT8' => '5008',
            '508 SW Allure 1.5 BlueHDI 130CV EAT8' => '508 SW',
            'C 300 D AMG Line Station' => 'C 300 D',
            'Focus SW 1.0 Ecoboost MHEV ST-Line X' => 'Focus SW',
            'Ford KA' => 'Ka',
            'Tucson 1.6 CRDI ou similar' => 'Tucson',
        ];

        foreach (DB::table('modelos')->orderBy('id')->get(['id', 'nome']) as $modelo) {
            $nomeOriginal = trim((string) ($modelo->nome ?? ''));
            $nomeFipe = $correcoes[$nomeOriginal] ?? $nomeOriginal;
            $nomeFipe = preg_replace('/\s+/', ' ', $nomeFipe) ?: $nomeFipe;

            DB::table('modelos')
                ->where('id', (int) $modelo->id)
                ->update([
                    'nome' => $nomeFipe,
                    'nome_fipe' => $nomeFipe,
                    'codigo_fipe' => null,
                ]);
        }

        $modelos = DB::table('modelos')
            ->select('id', 'marca_id', 'nome')
            ->orderBy('id')
            ->get();

        $grupos = $modelos->groupBy(function ($modelo) {
            return (int) $modelo->marca_id . '|' . Str::slug(mb_strtolower(trim((string) $modelo->nome)), '');
        });

        foreach ($grupos as $grupo) {
            if ($grupo->count() < 2) {
                continue;
            }

            $mantido = $grupo->sortBy('id')->first();
            $duplicadas = $grupo->where('id', '!=', $mantido->id);

            foreach ($duplicadas as $duplicada) {
                DB::table('veiculos')->where('modelo_id', (int) $duplicada->id)->update(['modelo_id' => (int) $mantido->id]);
                DB::table('reservas')->where('modelo_id', (int) $duplicada->id)->update(['modelo_id' => (int) $mantido->id]);
                DB::table('movimentacoes')->where('modelo_id', (int) $duplicada->id)->update(['modelo_id' => (int) $mantido->id]);

                if (Schema::hasTable('contrato_checkin')) {
                    DB::table('contrato_checkin')->where('modelo_id', (int) $duplicada->id)->update(['modelo_id' => (int) $mantido->id]);
                }

                if (Schema::hasTable('configuracoes_calcao')) {
                    DB::table('configuracoes_calcao')->where('modelo_id', (int) $duplicada->id)->update(['modelo_id' => (int) $mantido->id]);
                }

                if (Schema::hasTable('grupo_frota_modelo')) {
                    DB::table('grupo_frota_modelo')->where('modelo_id', (int) $duplicada->id)->update(['modelo_id' => (int) $mantido->id]);
                }

                DB::table('modelos')->where('id', (int) $duplicada->id)->delete();
            }
        }
    }
};
