<?php

use App\Models\Escola;

test('administrador can access school management routes', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->get('/admin/escola')->assertOk();
    $this->actingAs($user)->get('/admin/usuario')->assertOk();
    $this->actingAs($user)->get('/admin/regra')->assertOk();
});

test('professor cannot access user management', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get('/admin/usuario')->assertForbidden();
});

test('professor can access own profile area', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get('/admin/profile')->assertOk();
});

test('professor e aluno so alteram a foto no perfil', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $aluno = usuarioTenant($empresa, 'aluno');

    $this->actingAs($professor)->put('/admin/profile', ['telefone' => '11999999999'])
        ->assertForbidden();
    $this->actingAs($aluno)->put('/admin/profile', ['telefone' => '11999999999'])
        ->assertForbidden();

    $this->actingAs($professor)->put('/admin/profile/foto', [
        'foto' => \Illuminate\Http\UploadedFile::fake()->image('foto.jpg'),
    ])->assertRedirect(route('profile.edit'));

    $path = $professor->fresh()->foto_perfil;
    expect($path)->not->toBeNull();
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($path);
});

test('administrador ainda edita dados de contato no perfil', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->put('/admin/profile', ['telefone' => '1133334444'])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->telefone)->toBe('1133334444');
});

test('user without role has no permissions', function () {
    $empresa = tenant();
    sincronizarPerfis($empresa);

    $user = \App\Models\User::factory()->create([
        'empresa_id' => $empresa->id,
        'role_id' => null,
        'ativo' => true,
    ]);

    expect($user->hasPermissao('usuario.index'))->toBeFalse()
        ->and($user->hasPermissao('escola.index'))->toBeFalse();
});

test('membro baseline can access home but not management', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'membro');

    expect($user->hasPermissao('home'))->toBeTrue()
        ->and($user->hasPermissao('usuario.index'))->toBeFalse()
        ->and($user->hasPermissao('escola.index'))->toBeFalse();
});

test('each empresa gets its own set of school profiles', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    sincronizarPerfis($empresaA);
    sincronizarPerfis($empresaB);

    $rolesA = \Illuminate\Support\Facades\DB::table('roles')
        ->where('empresa_id', $empresaA->id)->count();
    $rolesB = \Illuminate\Support\Facades\DB::table('roles')
        ->where('empresa_id', $empresaB->id)->count();

    expect($rolesA)->toBeGreaterThanOrEqual(19)
        ->and($rolesB)->toBeGreaterThanOrEqual(19)
        ->and($rolesA)->toBe($rolesB);
});

test('superadmin bypasses permission checks', function () {
    $admin = superadmin();

    expect($admin->hasPermissao('usuario.index'))->toBeTrue()
        ->and($admin->hasPermissao('qualquer.rota'))->toBeTrue();
});

test('tenant user cannot access another tenant record via global scope', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $escolaB = Escola::create(['empresa_id' => $empresaB->id, 'nome' => 'Escola B']);

    // Simula request autenticada do tenant A.
    $userA = usuarioTenant($empresaA, 'administrador');
    $this->actingAs($userA)->get("/admin/escola/{$escolaB->id}")->assertNotFound();
});
