<?php

namespace App\Providers;

use App\Menu\CustomAdminLte;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTimeTracker;
use App\Models\User;
use App\Observers\OnboardingObserver;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
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

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // O SchemaCache é rememberForever: sem isso, tabelas/colunas novas
        // ficam invisíveis ao tenant scope e ao auto-fill de empresa_id
        // (bug do insert sem empresa_id após migrate).
        Event::listen(MigrationsEnded::class, fn () => SchemaCache::clear());

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

        // LogViewer (opcodesio/log-viewer): somente superadmin.
        Gate::define('viewLogViewer', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('acessar-perfil', function (User $user) {
            return $user->id !== null;
        });

        Gate::define('tem-perfil', function (User $user) {
            return ! empty($user->role_id);
        });

        // Módulo Licitações: opcional por empresa (licitacoes_ativo=S)
        // + permissão de leitura da tela.
        Gate::define('licitacoes.acessar', function (User $user) {
            $empresaId = app(EmpresaContext::class)->empresaId() ?? $user->empresa_id;
            $empresa = $empresaId ? \App\Models\Empresa::find($empresaId) : null;

            return $empresa
                && $empresa->licitacoes_ativo === 'S'
                && $user->hasPermissao('licitacoes.index');
        });

        $this->bootEmpresaScope();

        Cashier::useCustomerModel(User::class);

        User::observe(OnboardingObserver::class);
        ClockifyProjeto::observe(OnboardingObserver::class);
        ClockifyTimeTracker::observe(OnboardingObserver::class);

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
                'empresa_planos',
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
