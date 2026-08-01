<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reservas')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table): void {
            if (! Schema::hasColumn('reservas', 'revisao_checkout_concluida')) {
                $table->char('revisao_checkout_concluida', 1)
                    ->default('N')
                    ->after('cobranca_efetuada_pessoalmente_justificativa');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('reservas')) {
            return;
        }

        Schema::table('reservas', function (Blueprint $table): void {
            if (Schema::hasColumn('reservas', 'revisao_checkout_concluida')) {
                $table->dropColumn('revisao_checkout_concluida');
            }
        });
    }
};
