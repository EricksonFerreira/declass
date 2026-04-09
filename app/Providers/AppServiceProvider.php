<?php

namespace App\Providers;

use App\Models\Movimentacao;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\DisponibilidadeInicialVeiculoService;
use App\Services\MovimentacaoService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
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
        Gate::before(function (User $user, mixed $ability) {
            if (!is_string($ability)) {
                return null;
            }

            if ($user->isAdministrador()) {
                return true;
            }

            // Acesso a empresa deve ser exclusivo de administrador.
            if ($ability === 'gerenciar-empresa') {
                return null;
            }

            return $user->hasPermissao($ability) ? true : null;
        });

        Gate::define('gerenciar-empresa', function (User $user) {
            return $user->isAdministrador();
        });

        Gate::define('acessar-perfil', function (User $user) {
            return $user->id !== null;
        });

        $this->bootEmpresaScope();

        $this->app->resolving(VeiculoService::class, function ($veiculoService, $app) {
            $veiculoService->setMovimentacaoService($app->make(MovimentacaoService::class));
        });

        $this->app->resolving(MovimentacaoService::class, function ($movimentacaoService, $app) {
            $movimentacaoService->setVeiculoService($app->make(VeiculoService::class));
        });

        Cashier::useCustomerModel(User::class);
    }

    private function bootEmpresaScope(): void
    {
        if (!Schema::hasTable('empresas')) {
            return;
        }

        foreach (File::allFiles(app_path('Models')) as $file) {
            $class = 'App\\Models\\' . str_replace(
                ['/', '\\', '.php'],
                ['\\', '\\', ''],
                $file->getRelativePathname()
            );

            if (!class_exists($class) || !is_subclass_of($class, EloquentModel::class)) {
                continue;
            }

            /** @var EloquentModel $model */
            $model = app($class);
            $table = $model->getTable();

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'empresa_id')) {
                continue;
            }

            // Tabelas globais do sistema (não devem ser tenant-scoped, nem auto-preencher empresa_id).
            if (in_array($table, ['permissions', 'roles'], true)) {
                continue;
            }

            $class::addGlobalScope('empresa', function (Builder $builder) use ($table) {
                $empresaId = app(EmpresaContext::class)->empresaId();

                if ($empresaId !== null) {
                    $builder->where($table . '.empresa_id', $empresaId);
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
