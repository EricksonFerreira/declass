<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed inicial do sistema escolar:
     *  - empresa/tenant demo
     *  - escola de exemplo
     *  - superadmin da plataforma
     *  - administrador do tenant demo
     *  - todos os perfis escolares com matriz padrao
     */
    public function run(): void
    {
        $empresa = Empresa::query()->firstOrCreate(
            ['slug' => 'escola-demo'],
            [
                'nome' => 'Escola Demo',
                'ativo' => 'S',
                'email' => 'contato@escolademo.test',
                'cor_primaria' => '#1e3a8a',
                'cor_secundaria' => '#f59e0b',
            ]
        );

        $escolaExistente = DB::table('escolas')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'unidade-central')
            ->first();

        if (! $escolaExistente) {
            DB::table('escolas')->insert([
                'empresa_id' => $empresa->id,
                'nome' => 'Unidade Central',
                'slug' => 'unidade-central',
                'email' => 'central@escolademo.test',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'capacidade_alunos' => 500,
                'turno_padrao' => 'integral',
                'ativa' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Superadmin da plataforma (tipo_usuario_id = 1, sem empresa).
        // tipo_usuario_id nao pode ir no create(): o mutator do model
        // redireciona a escrita para role_id. Mesmo motivo para
        // email_verified_at (fora do fillable) — ambos via update direto.
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@declass.test'],
            [
                'name' => 'Super Admin',
                'nome' => 'Super Admin',
                'password' => Hash::make('password'),
                'ativo' => true,
            ]
        );
        DB::table('users')->where('id', $superAdmin->id)->update([
            'tipo_usuario_id' => 1,
            'email_verified_at' => now(),
        ]);

        // Administrador do tenant demo.
        $admin = User::query()->firstOrCreate(
            ['email' => 'diretor@escolademo.test'],
            [
                'name' => 'Diretor Demo',
                'nome' => 'Diretor Demo',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'ativo' => true,
            ]
        );
        DB::table('users')->where('id', $admin->id)->update([
            'email_verified_at' => now(),
        ]);

        $this->call(PerfisEscolaresSeeder::class);

        // Vincula o admin a todos os perfis? Nao — role_id aponta 1 perfil.
        // O papel 'administrador' do tenant e o perfil padrao dele.
        $roleAdmin = DB::table('roles')
            ->where('empresa_id', $empresa->id)
            ->where('name', 'administrador')
            ->value('id');

        if ($roleAdmin) {
            DB::table('users')->where('id', $admin->id)->update(['role_id' => $roleAdmin]);
        }
    }
}
