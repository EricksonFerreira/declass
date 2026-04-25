<?php

use App\Models\Regra;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use JeroenNoten\LaravelAdminLte\AdminLte;

function criarSuperadmin(): User
{
    $user = User::factory()->create([
        'role_id' => 1,
        'empresa_id' => null,
    ]);

    if (Schema::hasColumn('users', 'tipo_usuario_id')) {
        DB::table('users')
            ->where('id', $user->id)
            ->update(['tipo_usuario_id' => '1']);
    }

    return $user->refresh();
}

function criarAdministradorEmpresa(): User
{
    $regra = Regra::firstOrCreate([
        'name' => 'administrador',
        'guard_name' => 'web',
    ]);

    return User::factory()->create([
        'role_id' => $regra->id,
    ]);
}

function menuSidebar(): array
{
    app()->forgetInstance(AdminLte::class);

    return app(AdminLte::class)->menu('sidebar');
}

function localizarItemPlanos(array $items): ?array
{
    foreach ($items as $item) {
        if (! is_array($item)) {
            continue;
        }

        if (($item['text'] ?? null) === 'Planos' && ! empty($item['submenu'])) {
            return $item;
        }
    }

    return null;
}

it('mostra a aba de planos global apenas com os itens definidos para superadmin', function () {
    $this->actingAs(criarSuperadmin());

    $itemPlanos = localizarItemPlanos(menuSidebar());

    expect($itemPlanos)->not->toBeNull();
    expect(collect($itemPlanos['submenu'])->pluck('text')->values()->all())
        ->toBe([
            'Planos',
            'Política de Acesso',
            'Regras',
            'Empresas',
            'Permissões',
            'Perfis de Usuários',
            'Replicação de Base',
        ]);
});

it('oculta a aba administrativa global para administrador que nao e superadmin', function () {
    $this->actingAs(criarAdministradorEmpresa());

    expect(localizarItemPlanos(menuSidebar()))->toBeNull();
});

it('bloqueia acesso direto as rotas globais para administrador que nao e superadmin', function () {
    $admin = criarAdministradorEmpresa();

    $this->actingAs($admin);

    foreach ([
        'planos.hub',
        'regra.index',
        'empresa.index',
        'permissao.index',
        'replicacao_base.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertForbidden();
    }
});

it('permite que o superadmin acesse a central de planos', function () {
    $this->actingAs(criarSuperadmin())
        ->get(route('planos.hub'))
        ->assertOk();
});
