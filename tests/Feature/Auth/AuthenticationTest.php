<?php

test('login screen can be rendered', function () {
    $this->get('/admin/login')->assertOk();
});

test('tenant user can authenticate with slug, email and password', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    $response = $this->post('/admin/login', [
        'slug' => $empresa->slug,
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
});

test('user cannot authenticate with wrong password', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');

    $this->post('/admin/login', [
        'slug' => $empresa->slug,
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('user of another tenant cannot authenticate with this slug', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $userB = usuarioTenant($empresaB, 'administrador');

    $this->post('/admin/login', [
        'slug' => $empresaA->slug,
        'email' => $userB->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('unknown slug rejects login', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa);

    $this->post('/admin/login', [
        'slug' => 'slug-inexistente',
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('superadmin can authenticate with the platform slug', function () {
    $admin = superadmin(['email' => 'admin@declass.test']);

    $response = $this->post('/admin/login', [
        'slug' => 'declass',
        'email' => 'admin@declass.test',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect(route('home'));
});

test('tenant slug cannot be registered as declass', function () {
    $this->post('/admin/comecar', [
        'empresa_nome' => 'Declass Escola',
        'admin_nome' => 'Admin',
        'admin_email' => 'admin-'.uniqid().'@test.com',
    ])->assertSessionDoesntHaveErrors();

    // O slug gerado nao pode ser o reservado da plataforma.
    $this->assertDatabaseMissing('empresas', ['slug' => 'declass']);
});

test('users can logout', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa);

    $response = $this->actingAs($user)->post('/admin/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
