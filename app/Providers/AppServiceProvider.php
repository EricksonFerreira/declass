<?php

namespace App\Providers;

use App\Menu\CustomAdminLte;
use App\Models\User;
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
        // (bug do insert sem empresa_id após migrate). Re-registra os
        // escopos após migrate — em testes (banco recriado apos o boot)
        // e em deploys onde o codigo sobe antes das migrations.
        Event::listen(MigrationsEnded::class, function () {
            SchemaCache::clear();
            $this->bootEmpresaScope();
        });

        // Superadmin tem acesso irrestrito a tudo, sem excecao.
        Gate::before(function (User $user, mixed $ability) {
            if (! is_string($ability)) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            if (in_array($ability, ['gerenciar-empresa', 'acessar-superadmin', 'acessar-perfil'], true)) {
                return null;
            }

            return $user->hasPermissao($ability) ? true : null;
        });

        // Troca de tenant de contexto e recurso da plataforma.
        Gate::define('gerenciar-empresa', function (User $user) {
            return $user->isSuperAdmin();
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

        $this->bootEmpresaScope();

        // Replace the AdminLTE singleton with our custom version that hides
        // orphan section headers when the user has no visible items under them.
        $this->app->extend(AdminLte::class, function () {
            return new CustomAdminLte(config('adminlte.filters', []));
        });
    }

    private function bootEmpresaScope(): void
    {
        foreach (File::allFiles(app_path('Models')) as $file) {
            $class = 'App\\Models\\'.str_replace(
                ['/', '\\', '.php'],
                ['\\', '\\', ''],
                $file->getRelativePathname()
            );

            if (! class_exists($class)
                || ! is_subclass_of($class, EloquentModel::class)) {
                continue;
            }

            /** @var EloquentModel $model */
            $model = app($class);
            $table = $model->getTable();

            // Tabelas globais do sistema (nao devem ser tenant-scoped,
            // nem auto-preencher empresa_id).
            if (in_array($table, [
                'permissions', 'roles', 'empresas',
                'model_has_permissions', 'model_has_roles', 'role_has_permissions',
            ], true)) {
                continue;
            }

            // Schema verificado na hora da query/evento — o boot roda
            // antes das migrations em bancos novos e em testes, e os
            // escopos precisam ser re-registrados a cada aplicacao
            // (o Laravel limpa global scopes entre testes).
            $class::addGlobalScope('empresa', function (Builder $builder) use ($table) {
                if (! SchemaCache::hasColumn($table, 'empresa_id')) {
                    return;
                }

                $empresaId = app(EmpresaContext::class)->empresaId();

                if ($empresaId === null) {
                    return;
                }

                // Contas de plataforma (empresa_id null — ex.: superadmin)
                // precisam resolver a sessao em qualquer contexto de tenant.
                if ($table === 'users') {
                    $builder->where(function ($query) use ($table, $empresaId) {
                        $query->where($table.'.empresa_id', $empresaId)
                            ->orWhereNull($table.'.empresa_id');
                    });

                    return;
                }

                $builder->where($table.'.empresa_id', $empresaId);
            });

            $class::creating(function ($instance) use ($table) {
                if (! SchemaCache::hasColumn($table, 'empresa_id')) {
                    return;
                }

                $empresaId = app(EmpresaContext::class)->empresaId();

                if ($empresaId !== null && empty($instance->empresa_id)) {
                    $instance->empresa_id = $empresaId;
                }
            });
        }
    }
}
