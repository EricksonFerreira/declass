<?php

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\Permissao;
use App\Models\Plano;
use App\Models\Regra;
use App\Models\User;
use App\Services\PlanoPermissaoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function criarAdminEmpresaComPlano(array $planoAttrs = []): array
{
    $empresa = Empresa::factory()->create();
    $plano = Plano::factory()->create($planoAttrs);

    EmpresaPlano::create([
        'empresa_id' => $empresa->id,
        'plano_id' => $plano->id,
        'periodicidade' => 'mensal',
        'status' => 'ativo',
        'metodo_cobranca' => 'pix',
        'data_inicio' => now()->toDateString(),
        'data_vencimento' => now()->addMonth()->toDateString(),
        'valor_cobrado' => 199.90,
    ]);

    $roleAdmin = Regra::firstOrCreate([
        'name' => 'administrador',
        'guard_name' => 'web',
    ]);

    $admin = User::factory()->create([
        'empresa_id' => $empresa->id,
        'role_id' => $roleAdmin->id,
    ]);

    if (Schema::hasColumn('users', 'tipo_usuario_id')) {
        DB::table('users')->where('id', $admin->id)->update(['tipo_usuario_id' => '2']);
    }

    return [$empresa, $plano->fresh(), $admin->fresh()];
}

it('permite acesso do administrador somente para permissões liberadas no plano', function () {
    [, $plano, $admin] = criarAdminEmpresaComPlano();

    $permitida = Permissao::firstOrCreate(
        ['name' => 'usuario.index', 'guard_name' => 'web'],
        ['descricao' => 'Listagem de usuários', 'grupo' => 'usuario'],
    );

    $bloqueada = Permissao::firstOrCreate(
        ['name' => 'financeiro.index', 'guard_name' => 'web'],
        ['descricao' => 'Listagem do financeiro', 'grupo' => 'financeiro'],
    );

    $plano->permissoes()->sync([$permitida->id]);

    expect($admin->hasPermissao('usuario.index'))->toBeTrue();
    expect($admin->hasPermissao('financeiro.index'))->toBeFalse();
});

it('valida limite e conjunto de permissões do perfil conforme o plano', function () {
    [, $plano, $admin] = criarAdminEmpresaComPlano([
        'limite_permissoes_por_regra' => 2,
    ]);

    $permissaoA = Permissao::firstOrCreate(
        ['name' => 'usuario.index', 'guard_name' => 'web'],
        ['grupo' => 'usuario'],
    );

    $permissaoB = Permissao::firstOrCreate(
        ['name' => 'usuario.create', 'guard_name' => 'web'],
        ['grupo' => 'usuario'],
    );

    $permissaoC = Permissao::firstOrCreate(
        ['name' => 'financeiro.index', 'guard_name' => 'web'],
        ['grupo' => 'financeiro'],
    );

    $plano->permissoes()->sync([$permissaoA->id, $permissaoB->id]);

    $service = app(PlanoPermissaoService::class);

    expect($service->validarSelecaoDePermissoes($admin, [$permissaoA->id, $permissaoB->id])['ok'])->toBeTrue();
    expect($service->validarSelecaoDePermissoes($admin, [$permissaoA->id, $permissaoB->id, $permissaoC->id])['ok'])->toBeFalse();
    expect($service->validarSelecaoDePermissoes($admin, [$permissaoA->id, $permissaoB->id, $permissaoA->id])['ok'])->toBeTrue();
});
