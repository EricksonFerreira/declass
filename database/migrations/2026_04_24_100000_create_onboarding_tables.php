<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rastreia o progresso do onboarding por empresa.
        Schema::create('onboarding_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->boolean('step_empresa_configurada')->default(false);
            $table->boolean('step_plano_contratado')->default(false);
            $table->boolean('step_pagamento_realizado')->default(false);
            $table->boolean('step_frota_cadastrada')->default(false);
            $table->boolean('step_estacao_cadastrada')->default(false);
            $table->boolean('step_usuario_criado')->default(false);
            $table->boolean('step_site_configurado')->default(false);
            $table->boolean('concluido')->default(false);
            $table->timestamps();
        });

        // Registros de cobrança pendentes gerados pelo proprio sistema (sem gateway externo).
        Schema::table('empresa_plano_pagamentos', function (Blueprint $table) {
            $table->string('referencia_interna', 100)->nullable()->after('nosso_numero')
                ->comment('Referencia para pagamentos manuais internos');
            $table->text('instrucoes_pagamento')->nullable()->after('referencia_interna');
        });
    }

    public function down(): void
    {
        Schema::table('empresa_plano_pagamentos', function (Blueprint $table) {
            $table->dropColumn(['referencia_interna', 'instrucoes_pagamento']);
        });
        Schema::dropIfExists('onboarding_steps');
    }
};
