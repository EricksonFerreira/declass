<?php

use App\Models\Empresa;
use App\Models\Regra;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('guest can see tenant registration form', function () {
    $this->get('/admin/comecar')->assertOk();
});

test('tenant registration creates empresa, admin user and school profiles', function () {
    Mail::fake();

    $response = $this->post('/admin/comecar', [
        'empresa_nome' => 'Colégio Novo Horizonte',
        'empresa_cnpj' => null,
        'empresa_email' => 'contato@colegio.test',
        'empresa_telefone' => '(11) 99999-0000',
        'admin_nome' => 'Maria Diretora',
        'admin_email' => 'maria@colegio.test',
    ]);

    $response->assertRedirect(route('cadastro.empresa.success'));

    $empresa = Empresa::where('nome', 'Colégio Novo Horizonte')->first();
    expect($empresa)->not->toBeNull()
        ->and($empresa->slug)->not->toBeEmpty();

    $user = User::where('email', 'maria@colegio.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->empresa_id)->toBe($empresa->id)
        ->and($user->forcar_troca_senha)->toBeTrue();

    // Perfil 'administrador' do tenant vinculado ao usuario.
    $roleAdmin = Regra::where('empresa_id', $empresa->id)
        ->where('name', 'administrador')
        ->first();
    expect($roleAdmin)->not->toBeNull()
        ->and((int) $user->role_id)->toBe($roleAdmin->id);

    // Os 19 perfis escolares foram criados para o tenant.
    $totalPerfis = Regra::where('empresa_id', $empresa->id)->count();
    expect($totalPerfis)->toBeGreaterThanOrEqual(19);
});

test('registered admin receives welcome email', function () {
    Mail::fake();

    $this->post('/admin/comecar', [
        'empresa_nome' => 'Escola Email Test',
        'admin_nome' => 'Admin Email',
        'admin_email' => 'admin-email-'.uniqid().'@test.com',
    ]);

    Mail::assertSent(\App\Mail\BoasVindasAdminMail::class);
});

test('duplicate admin email is rejected', function () {
    $empresa = tenant();
    $existente = usuarioTenant($empresa);

    $this->post('/admin/comecar', [
        'empresa_nome' => 'Outra Escola',
        'admin_nome' => 'Duplicado',
        'admin_email' => $existente->email,
    ])->assertSessionHasErrors('admin_email');
});
