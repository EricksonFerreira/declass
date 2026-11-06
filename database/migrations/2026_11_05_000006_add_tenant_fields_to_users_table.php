<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colunas de tenant/perfil adicionadas a tabela users padrao do Laravel.
 *
 * - empresa_id  : tenant dono do usuario (null = conta de plataforma/superadmin)
 * - role_id     : perfil de acesso (roles.id) dentro do tenant
 * - tipo_usuario_id : legado de plataforma — valor 1 identifica superadmin
 * - usuario     : login/username alternativo ao e-mail
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nome')->nullable()->after('name');
            $table->string('usuario')->nullable()->after('nome');
            $table->string('telefone', 30)->nullable()->after('usuario');
            $table->string('celular', 30)->nullable()->after('telefone');
            $table->string('cidade')->nullable()->after('celular');
            $table->string('numero_identificacao', 30)->nullable()->after('cidade');
            $table->date('data_nascimento')->nullable()->after('numero_identificacao');
            $table->string('sexo', 1)->nullable()->after('data_nascimento');
            $table->string('foto_perfil')->nullable()->after('sexo');
            $table->string('cargo')->nullable()->after('foto_perfil');
            $table->unsignedBigInteger('role_id')->nullable()->after('cargo');
            $table->unsignedBigInteger('tipo_usuario_id')->nullable()->after('role_id');
            $table->unsignedBigInteger('empresa_id')->nullable()->after('tipo_usuario_id');
            $table->boolean('forcar_troca_senha')->default(false)->after('empresa_id');
            $table->boolean('ativo')->default(true)->after('forcar_troca_senha');

            $table->index('empresa_id');
            $table->index('role_id');
            $table->unique(['empresa_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'email']);
            $table->dropIndex(['empresa_id']);
            $table->dropIndex(['role_id']);
            $table->dropColumn([
                'nome', 'usuario', 'telefone', 'celular', 'cidade',
                'numero_identificacao', 'data_nascimento', 'sexo',
                'foto_perfil', 'cargo', 'role_id', 'tipo_usuario_id',
                'empresa_id', 'forcar_troca_senha', 'ativo',
            ]);
        });
    }
};
