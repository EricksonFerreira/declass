<?php

namespace App\Providers;

use App\Menu\CustomAdminLte;
use App\Models\User;
use App\Support\EmpresaContext;
use App\Support\EscolaContext;
use App\Support\EspelhamentoContext;
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
        $this->app->scoped(EscolaContext::class, fn () => new EscolaContext());
        $this->app->scoped(EspelhamentoContext::class, fn () => new EspelhamentoContext());
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

        // Superadmin tem acesso irrestrito a tudo, sem excecao —
        // EXCETO durante espelhamento: ai as permissoes avaliadas sao
        // as do usuario espelhado (hasPermissao ja delega para ele).
        Gate::before(function (User $user, mixed $ability) {
            if (! is_string($ability)) {
                return null;
            }

            if ($user->isSuperAdmin() && ! app(EspelhamentoContext::class)->ativo()) {
                return true;
            }

            if (in_array($ability, ['gerenciar-empresa', 'acessar-superadmin', 'acessar-perfil'], true)) {
                return null;
            }

            return $user->hasPermissao($ability) ? true : null;
        });

        // Troca de tenant de contexto e recurso da plataforma.
        // Durante espelhamento, as telas exclusivas de superadmin ficam
        // indisponiveis — o espelho mostra so o que o alvo veria.
        Gate::define('gerenciar-empresa', function (User $user) {
            return $user->isSuperAdmin() && ! app(EspelhamentoContext::class)->ativo();
        });

        Gate::define('acessar-superadmin', function (User $user) {
            return $user->isSuperAdmin() && ! app(EspelhamentoContext::class)->ativo();
        });

        // LogViewer (opcodesio/log-viewer): somente superadmin.
        Gate::define('viewLogViewer', function (User $user) {
            return $user->isSuperAdmin() && ! app(EspelhamentoContext::class)->ativo();
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
            // Escopo de escola da sessao: quando o usuario escolhe uma
            // unidade no seletor do navbar, todas as consultas de tabelas
            // com escola_id sao filtradas por ela. escola_id nulo = registro
            // da rede toda, visivel em qualquer unidade.
            $class::addGlobalScope('escola', function (Builder $builder) use ($table) {
                // A tabela de unidades nao tem escola_id: quando o usuario
                // tem unidades restritas (professor = onde leciona; demais
                // = vinculos em user_escolas), a listagem de unidades fica
                // limitada a elas em qualquer tela/seletor.
                if ($table === 'escolas') {
                    $permitidas = auth()->user()?->escolasContextoIds();
                    if ($permitidas !== null) {
                        $builder->whereIn('escolas.id', $permitidas);
                    }

                    return;
                }

                if (! SchemaCache::hasColumn($table, 'escola_id')) {
                    return;
                }

                $escolaId = app(EscolaContext::class)->escolaId();

                if ($escolaId === null) {
                    return;
                }

                $builder->where(function ($query) use ($table, $escolaId) {
                    $query->where($table.'.escola_id', $escolaId)
                        ->orWhereNull($table.'.escola_id');
                });
            });

            // Auto-preenche escola_id desnormalizado nas tabelas
            // transacionais, resolvendo pela relacao pai (turma/aluno/etc).
            $class::creating(function ($instance) use ($table) {
                if (! SchemaCache::hasColumn($table, 'escola_id') || ! empty($instance->escola_id)) {
                    return;
                }

                $resolver = self::escolaResolvers()[$table] ?? null;

                if ($resolver !== null) {
                    $instance->escola_id = $resolver($instance);
                }
            });
        }
    }

    /**
     * Resolve escola_id no insert das tabelas transacionais que guardam a
     * coluna desnormalizada, a partir das relacoes do proprio model.
     */
    private static function escolaResolvers(): array
    {
        return [
            'matriculas' => fn ($m) => $m->turma?->escola_id,
            'mensalidades' => fn ($m) => $m->matricula?->turma?->escola_id,
            'diarios' => fn ($m) => $m->turma?->escola_id,
            'frequencias' => fn ($m) => $m->diario?->turma?->escola_id ?? $m->matricula?->turma?->escola_id,
            'avaliacoes' => fn ($m) => $m->turma?->escola_id,
            'notas' => fn ($m) => $m->matricula?->turma?->escola_id ?? $m->avaliacao?->turma?->escola_id,
            'emprestimos_livros' => fn ($m) => $m->aluno?->escola_id,
            'tarefas' => fn ($m) => $m->turma?->escola_id,
            'entregas_tarefa' => fn ($m) => $m->tarefa?->turma?->escola_id ?? $m->matricula?->turma?->escola_id,
            'saude_alunos' => fn ($m) => $m->aluno?->escola_id,
            'aluno_documentos' => fn ($m) => $m->aluno?->escola_id,
            'transporte_alunos' => fn ($m) => $m->aluno?->escola_id ?? $m->rota?->escola_id,
            'paradas_transporte' => fn ($m) => $m->rota?->escola_id,
            'atribuicoes' => fn ($m) => $m->turma?->escola_id,
            'pesquisa_respostas' => fn ($m) => $m->pesquisa?->escola_id,
            'movimentacoes_estoque' => fn ($m) => $m->material?->escola_id,
            'horarios_turma' => fn ($m) => $m->turma?->escola_id,
        ];
    }
}
