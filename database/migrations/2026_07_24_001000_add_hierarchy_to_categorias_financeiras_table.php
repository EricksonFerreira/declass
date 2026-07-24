<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias_financeiras', function (Blueprint $table) {
            if (!Schema::hasColumn('categorias_financeiras', 'categoria_pai_id')) {
                $table->foreignId('categoria_pai_id')
                    ->nullable()
                    ->after('empresa_id')
                    ->constrained('categorias_financeiras')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('categorias_financeiras', 'codigo')) {
                $table->string('codigo', 30)->nullable()->after('categoria_pai_id');
            }

            if (!Schema::hasColumn('categorias_financeiras', 'status')) {
                $table->string('status', 20)->default('ativo')->after('descricao');
            }

            if (!Schema::hasColumn('categorias_financeiras', 'permite_lancamento')) {
                $table->boolean('permite_lancamento')->default(true)->after('status');
            }

            if (!Schema::hasColumn('categorias_financeiras', 'origem')) {
                $table->string('origem', 30)->default('manual')->after('permite_lancamento');
            }
        });

        // Populate existing categories with sequential codes before adding unique constraint, in case they have nulls
        $empresas = DB::table('categorias_financeiras')->select('empresa_id')->distinct()->pluck('empresa_id');
        foreach ($empresas as $empresaId) {
            $tipos = ['receita', 'despesa'];
            foreach ($tipos as $tipo) {
                $cats = DB::table('categorias_financeiras')
                    ->where('empresa_id', $empresaId)
                    ->where('tipo', $tipo)
                    ->whereNull('categoria_pai_id')
                    ->orderBy('id')
                    ->get();

                $i = 1;
                foreach ($cats as $cat) {
                    $prefix = $tipo === 'receita' ? 'R' : 'D';
                    $codigo = sprintf('%s.%02d', $prefix, $i++);
                    DB::table('categorias_financeiras')->where('id', $cat->id)->update(['codigo' => $codigo]);
                }
            }
        }

        // Add unique constraint
        Schema::table('categorias_financeiras', function (Blueprint $table) {
            $table->unique(['empresa_id', 'codigo'], 'cat_fin_empresa_codigo_unique');
        });
    }

    public function down(): void
    {
        Schema::table('categorias_financeiras', function (Blueprint $table) {
            $table->dropUnique('cat_fin_empresa_codigo_unique');
            $table->dropForeign(['categoria_pai_id']);
            $table->dropColumn(['categoria_pai_id', 'codigo', 'status', 'permite_lancamento', 'origem']);
        });
    }
};
