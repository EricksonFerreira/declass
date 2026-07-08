<?php

namespace App\Providers;

use App\Menu\CustomAdminLte;
use App\Models\Estacao;
use App\Models\Movimentacao;
use App\Models\User;
use App\Models\Veiculo;
use App\Observers\OnboardingObserver;
use App\Services\DisponibilidadeInicialVeiculoService;
use App\Services\MovimentacaoService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\AdminLte;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Paginator::useBootstrapFour();
        $this->app->scoped(EmpresaContext::class, fn () => new EmpresaContext());


        $this->app->singleton(VeiculoService::class, function ($app) {
            return new VeiculoService(
                $app->make(Veiculo::class),
                $app->make(DisponibilidadeInicialVeiculoService::class)
            );
        });

        $this->app->singleton(MovimentacaoService::class, function ($app) {
            return new MovimentacaoService($app->make(Movimentacao::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Superadmin tem acesso irrestrito a tudo, sem excecao.
        Gate::before(function (User $user, mixed $ability) {
            if (! is_string($ability)) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            if ($ability === 'reserva.minhas') {
                return true;
            }

            if (in_array($ability, ['gerenciar-empresa', 'gerenciar-planos', 'acessar-superadmin', 'acessar-perfil'], true)) {
                return null;
            }

            return $user->hasPermissao($ability) ? true : null;
        });

        Gate::define('gerenciar-empresa', function (User $user) {
            return $user->isAdministrador();
        });

        // Superadmin sempre pode. Outros precisam de permissao explicita.
        Gate::define('gerenciar-planos', function (User $user) {
            return $user->isSuperAdmin() || $user->hasPermissao('gerenciar-planos');
        });

        Gate::define('acessar-superadmin', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('acessar-perfil', function (User $user) {
            return $user->id !== null;
        });

        Gate::define('tem-perfil', function (User $user) {
            return ! empty($user->role_id);
        });

        $this->bootEmpresaScope();

        $this->app->resolving(VeiculoService::class, function ($veiculoService, $app) {
            $veiculoService->setMovimentacaoService($app->make(MovimentacaoService::class));
        });

        $this->app->resolving(MovimentacaoService::class, function ($movimentacaoService, $app) {
            $movimentacaoService->setVeiculoService($app->make(VeiculoService::class));
        });

        Cashier::useCustomerModel(User::class);

        Veiculo::observe(OnboardingObserver::class);
        Estacao::observe(OnboardingObserver::class);
        User::observe(OnboardingObserver::class);

        // Replace the AdminLTE singleton with our custom version that hides
        // orphan section headers (OPERAÇÃO, FROTA, etc.) when the user has
        // no visible items under them.
        $this->app->extend(AdminLte::class, function () {
            return new CustomAdminLte(config('adminlte.filters', []));
        });
    }

    private function bootEmpresaScope(): void
    {
        if (! SchemaCache::hasTable('empresas')) {
            return;
        }

        foreach (File::allFiles(app_path('Models')) as $file) {
            $class = 'App\\Models\\'.str_replace(
                ['/', '\\', '.php'],
                ['\\', '\\', ''],
                $file->getRelativePathname()
            );

            if (! class_exists($class) || ! is_subclass_of($class, EloquentModel::class)) {
                continue;
            }

            /** @var EloquentModel $model */
            $model = app($class);
            $table = $model->getTable();

            if (! SchemaCache::hasTable($table) || ! SchemaCache::hasColumn($table, 'empresa_id')) {
                continue;
            }

            // Tabelas globais do sistema (não devem ser tenant-scoped, nem auto-preencher empresa_id).
            if (in_array($table, [
                'permissions', 'roles', 'empresas', 'paises',
                'status_reserva', 'status_movimentacao', 'tipos_movimentacao',
                'tipo_transacoes', 'status_veiculos', 'status_item_checklist',
            ], true)) {
                continue;
            }

            $class::addGlobalScope('empresa', function (Builder $builder) use ($table) {
                $empresaId = app(EmpresaContext::class)->empresaId();

                if ($empresaId !== null) {
                    $builder->where($table.'.empresa_id', $empresaId);
                }
            });

            $class::creating(function ($instance) {
                $empresaId = app(EmpresaContext::class)->empresaId();

                if ($empresaId !== null && empty($instance->empresa_id)) {
                    $instance->empresa_id = $empresaId;
                }
            });
        }
    }
}
