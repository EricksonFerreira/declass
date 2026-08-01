<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrato_checkin', function (Blueprint $table) {
            if (!Schema::hasColumn('contrato_checkin', 'aceite_termo')) {
                $table->char('aceite_termo', 1)->default('N');
            }
            if (!Schema::hasColumn('contrato_checkin', 'termo_texto')) {
                $table->text('termo_texto')->nullable();
            }
            if (!Schema::hasColumn('contrato_checkin', 'signatario_nome')) {
                $table->string('signatario_nome')->nullable();
            }
            if (!Schema::hasColumn('contrato_checkin', 'signatario_cpf')) {
                $table->string('signatario_cpf')->nullable();
            }
            if (!Schema::hasColumn('contrato_checkin', 'assinatura_ip')) {
                $table->string('assinatura_ip')->nullable();
            }
            if (!Schema::hasColumn('contrato_checkin', 'assinatura_dispositivo')) {
                $table->string('assinatura_dispositivo')->nullable();
            }
            if (!Schema::hasColumn('contrato_checkin', 'pdf_hash')) {
                $table->string('pdf_hash')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contrato_checkin', function (Blueprint $table) {
            $columns = [
                'aceite_termo',
                'termo_texto',
                'signatario_nome',
                'signatario_cpf',
                'assinatura_ip',
                'assinatura_dispositivo',
                'pdf_hash'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('contrato_checkin', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
