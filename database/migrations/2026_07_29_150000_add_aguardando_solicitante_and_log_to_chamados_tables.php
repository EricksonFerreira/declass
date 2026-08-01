<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamados', function (Blueprint $table) {
            $table->dateTime('aguardando_solicitante_em')->nullable()->after('cancelado_em');
        });

        Schema::table('chamado_anexos', function (Blueprint $table) {
            $table->foreignId('chamado_log_id')->nullable()->after('chamado_id')->constrained('chamado_logs')->nullOnDelete();
            $table->string('contexto', 30)->nullable()->after('chamado_log_id');
        });
    }

    public function down(): void
    {
        Schema::table('chamado_anexos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chamado_log_id');
            $table->dropColumn('contexto');
        });

        Schema::table('chamados', function (Blueprint $table) {
            $table->dropColumn('aguardando_solicitante_em');
        });
    }
};
