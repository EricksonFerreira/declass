<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/*
|--------------------------------------------------------------------------
| Helpers do dominio escolar
|--------------------------------------------------------------------------
|
| Fixtures compartilhadas: tenant (empresa), sincronizacao de
| permissoes/rotas e usuarios com perfil dentro do tenant.
|
*/

use App\Models\Empresa;
use App\Models\User;
use App\Services\PermissaoSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cria um tenant (empresa) minimo para os testes.
 */
function tenant(array $attrs = []): Empresa
{
    return Empresa::create(array_merge([
        'nome' => 'Escola Teste '.Str::random(6),
        'slug' => 'escola-'.Str::lower(Str::random(10)),
        'ativo' => 'S',
    ], $attrs));
}

/**
 * Sincroniza permissoes (rotas nomeadas) e garante os perfis
 * escolares do tenant informado.
 */
function sincronizarPerfis(Empresa $empresa): void
{
    app(PermissaoSyncService::class)->sync();
}

/**
 * Cria um usuario vinculado ao tenant com o perfil informado
 * (ex.: 'administrador', 'professor', 'membro').
 */
function usuarioTenant(Empresa $empresa, string $perfil = 'administrador', array $attrs = []): User
{
    sincronizarPerfis($empresa);

    $roleId = DB::table('roles')
        ->where('empresa_id', $empresa->id)
        ->where('name', $perfil)
        ->where('guard_name', 'web')
        ->value('id');

    return User::factory()->create(array_merge([
        'empresa_id' => $empresa->id,
        'role_id' => $roleId,
        'ativo' => true,
    ], $attrs));
}

/**
 * Superadmin da plataforma (tipo_usuario_id = 1, sem empresa).
 * tipo_usuario_id vai por update direto — o mutator do model
 * redireciona escritas Eloquent para role_id.
 */
function superadmin(array $attrs = []): User
{
    $user = User::factory()->create(array_merge([
        'empresa_id' => null,
        'role_id' => null,
        'ativo' => true,
    ], $attrs));

    DB::table('users')->where('id', $user->id)->update(['tipo_usuario_id' => 1]);

    return $user->fresh();
}
