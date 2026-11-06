<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Support\SchemaCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupProducaoCommand extends Command
{
    protected $signature = 'declass:setup-producao {--demo : Popula tambem os dados de demonstracao escolar}';

    protected $description = 'Sobe a producao: optimize:clear + migrate + seed, tenant declass e superadmins com troca de senha no primeiro acesso';

    private const SENHA_INICIAL = 'Senh@123';

    private const SUPERADMINS = [
        ['nome' => 'David', 'email' => 'david@declass.app', 'usuario' => 'david'],
        ['nome' => 'Erickson', 'email' => 'erickson@declass.app', 'usuario' => 'erickson'],
    ];

    public function handle(): int
    {
        $this->info('=== optimize:clear ===');
        Artisan::call('optimize:clear');
        $this->line(Artisan::output());

        // Logo/publicos: cria public/storage (idempotente — avisa se ja existe)
        $this->info('=== storage:link ===');
        Artisan::call('storage:link');
        $this->line(Artisan::output());

        $this->info('=== migrate ===');
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        $this->info('=== db:seed ===');
        Artisan::call('db:seed', ['--force' => true]);
        $this->line(Artisan::output());

        if ($this->option('demo')) {
            $this->info('=== db:seed --class=DemoEscolarSeeder ===');
            Artisan::call('db:seed', [
                '--force' => true,
                '--class' => \Database\Seeders\DemoEscolarSeeder::class,
            ]);
            $this->line(Artisan::output());
        }

        $empresa = $this->garantirEmpresaDeclock();
        $this->info("Tenant declass: id {$empresa->id}");

        foreach (self::SUPERADMINS as $admin) {
            $criado = $this->garantirSuperAdmin($admin['nome'], $admin['email'], $admin['usuario']);
            $this->info("{$admin['email']} (usuario: {$admin['usuario']}): ".($criado ? 'criado (senha inicial + troca obrigatoria)' : 'atualizado como superadmin'));
        }

        $this->info('Setup de producao concluido.');

        return self::SUCCESS;
    }

    private function garantirEmpresaDeclock(): Empresa
    {
        $empresa = Empresa::withTrashed()->firstOrCreate(
            ['slug' => 'declass'],
            ['nome' => 'DECLASS', 'ativo' => 'S']
        );

        if ($empresa->trashed()) {
            $empresa->restore();
            $empresa->ativo = 'S';
            $empresa->save();
        }

        return $empresa;
    }

    /**
     * tipo_usuario_id = 1 e o que torna o usuario superadmin, mas o mutator
     * do model redireciona escritas para role_id — por isso o update e
     * feito direto na tabela (DB::table), como a propria docstring do
     * model indica.
     */
    private function garantirSuperAdmin(string $nome, string $email, string $usuario): bool
    {
        $existente = DB::table('users')->where('email', $email)->first();
        $temColunaUsuario = SchemaCache::hasColumn('users', 'usuario');

        if (! $existente) {
            DB::table('users')->insert(array_filter([
                'nome' => $nome,
                'email' => $email,
                'usuario' => $temColunaUsuario ? $usuario : null,
                'empresa_id' => null,
                'password' => Hash::make(self::SENHA_INICIAL),
                'ativo' => 1,
                'forcar_troca_senha' => 1,
                'email_verified_at' => now(),
                'tipo_usuario_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ], fn ($v) => $v !== null));

            return true;
        }

        DB::table('users')->where('id', $existente->id)->update(array_filter([
            'nome' => $nome,
            'usuario' => $temColunaUsuario ? $usuario : null,
            'empresa_id' => null,
            'ativo' => 1,
            'tipo_usuario_id' => 1,
            'updated_at' => now(),
        ], fn ($v) => $v !== null));

        return false;
    }
}
