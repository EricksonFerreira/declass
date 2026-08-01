<?php

namespace Database\Seeders;

use App\Models\Permissao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PermissoesSistemaSeeder extends Seeder
{
    private array $ignoredPrefixes = [
        'login',
        'logout',
        'register',
        'password.',
        'verification.',
        'sanctum.',
        'ignition.',
        'debugbar.',
        'generated::',
    ];

    private array $ignoredExact = [
        'acessar-superadmin',
        'gerenciar-planos',
    ];

    private array $ignoredRoutePrefixes = [
        'superadmin.',
        'admin.super',
        'fipe.catalogo.run',
    ];

    public function run(): void
    {
        if (! Route::has('home')) {
            $this->command?->warn('Rotas ainda nao carregadas. Seeder de permissoes ignorado.');
            return;
        }

        $permissionNames = $this->collectPermissionNames();
        if (empty($permissionNames)) {
            $this->command?->warn('Nenhuma permissao encontrada para sincronizar.');
            return;
        }

        $created = 0;
        foreach ($permissionNames as $name) {
            $exists = Permissao::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->exists();

            if ($exists) {
                continue;
            }

            Permissao::query()->create([
                'name' => $name,
                'guard_name' => 'web',
                'descricao' => null,
                'grupo' => $this->grupoFromName($name),
            ]);
            $created++;
        }

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        $this->command?->info("PermissoesSistemaSeeder concluido. Criadas: {$created} permissao(ões).");
    }

    /**
     * @return array<int, string>
     */
    private function collectPermissionNames(): array
    {
        $names = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name) {
                continue;
            }

            if ($this->shouldIgnore($name)) {
                continue;
            }

            $names[] = $name;
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    private function shouldIgnore(string $name): bool
    {
        if (in_array($name, $this->ignoredExact, true)) {
            return true;
        }

        foreach ($this->ignoredPrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        foreach ($this->ignoredRoutePrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return str_contains($name, 'superadmin');
    }

    private function grupoFromName(string $name): ?string
    {
        $prefix = (string) str($name)->before('.');

        return match ($prefix) {
            'assinatura', 'plano', 'empresa' => 'Gestão',
            'reserva', 'checkout', 'checkin', 'multa', 'sinistro', 'dano' => 'Operações',
            'usuario', 'permissao', 'regra', 'tipo_usuario' => 'Segurança',
            'movimentacao', 'financeiro', 'pagamento' => 'Financeiro',
            default => ucfirst(str_replace('_', ' ', $prefix)),
        };
    }
}
