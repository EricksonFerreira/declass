<?php

test('superadmin abre o log viewer', function () {
    $admin = superadmin();

    $this->actingAs($admin)
        ->get('/admin/logs')
        ->assertOk();
});

test('administrador de tenant nao abre o log viewer', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)
        ->get('/admin/logs')
        ->assertForbidden();
});
