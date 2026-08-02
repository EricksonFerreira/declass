<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_plano', function (Blueprint $table) {
            if (! Schema::hasColumn('itens_plano', 'valor')) {
                $table->decimal('valor', 10, 2)->nullable()->after('descricao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('itens_plano', function (Blueprint $table) {
            if (Schema::hasColumn('itens_plano', 'valor')) {
                $table->dropColumn('valor');
            }
        });
    }
};
