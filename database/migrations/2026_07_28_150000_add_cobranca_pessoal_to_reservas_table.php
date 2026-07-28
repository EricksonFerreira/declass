<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (! Schema::hasColumn('reservas', 'cobranca_efetuada_pessoalmente_em')) {
                $table->dateTime('cobranca_efetuada_pessoalmente_em')->nullable()->after('taxa_entrega');
            }

            if (! Schema::hasColumn('reservas', 'cobranca_efetuada_pessoalmente_por_user_id')) {
                $table->unsignedBigInteger('cobranca_efetuada_pessoalmente_por_user_id')->nullable()->after('cobranca_efetuada_pessoalmente_em');
            }

            if (! Schema::hasColumn('reservas', 'cobranca_efetuada_pessoalmente_justificativa')) {
                $table->text('cobranca_efetuada_pessoalmente_justificativa')->nullable()->after('cobranca_efetuada_pessoalmente_por_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            foreach ([
                'cobranca_efetuada_pessoalmente_justificativa',
                'cobranca_efetuada_pessoalmente_por_user_id',
                'cobranca_efetuada_pessoalmente_em',
            ] as $column) {
                if (Schema::hasColumn('reservas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
