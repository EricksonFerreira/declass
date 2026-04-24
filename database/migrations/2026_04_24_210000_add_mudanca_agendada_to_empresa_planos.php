<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_planos', function (Blueprint $table) {
            $table->foreignId('proximo_plano_id')
                ->nullable()
                ->after('plano_id')
                ->constrained('planos');

            $table->enum('proxima_periodicidade', ['mensal', 'anual'])
                ->nullable()
                ->after('periodicidade');

            $table->enum('mudanca_tipo', ['downgrade', 'cancelamento'])
                ->nullable()
                ->after('status');

            $table->date('mudanca_efetiva_em')
                ->nullable()
                ->after('data_vencimento');

            $table->timestamp('mudanca_solicitada_em')
                ->nullable()
                ->after('mudanca_efetiva_em');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_planos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proximo_plano_id');
            $table->dropColumn([
                'proxima_periodicidade',
                'mudanca_tipo',
                'mudanca_efetiva_em',
                'mudanca_solicitada_em',
            ]);
        });
    }
};

