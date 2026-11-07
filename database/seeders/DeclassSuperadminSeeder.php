<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Garante o tenant da plataforma (slug "declass") e o superadmin
 * erickson@declass.com vinculado a ele.
 *
 * Idempotente: seguro rodar em producao quantas vezes precisar.
 */
class DeclassSuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $declass = Empresa::query()->firstOrCreate(
            ['slug' => 'declass'],
            [
                'nome' => 'DeClass',
                'ativo' => 'S',
                'email' => 'contato@declass.test',
            ]
        );

        // tipo_usuario_id nao pode ir no create(): o mutator do model
        // redireciona a escrita para role_id. Atualizado via query
        // direta abaixo, junto com empresa_id e email_verified_at.
        $usuario = User::query()->firstOrCreate(
            ['email' => 'erickson@declass.com'],
            [
                'name' => 'Erickson',
                'nome' => 'Erickson',
                'password' => Hash::make('password'),
                'empresa_id' => $declass->id,
                'ativo' => true,
            ]
        );

        DB::table('users')->where('id', $usuario->id)->update([
            'tipo_usuario_id' => 1,
            'empresa_id' => $declass->id,
            'email_verified_at' => $usuario->email_verified_at ?? now(),
        ]);

        $this->command?->info("Superadmin {$usuario->email} vinculado ao tenant {$declass->slug} (senha padrao: password).");
    }
}
