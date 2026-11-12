<?php

use Illuminate\Support\Facades\Storage;

it('fallback de /storage serve arquivo existente no disk publico', function () {
    Storage::fake('public');
    Storage::disk('public')->put('empresas/logo/teste.png', 'img-fake');

    $this->get('/storage/empresas/logo/teste.png')->assertOk();
});

it('fallback de /storage retorna 404 para arquivo inexistente', function () {
    Storage::fake('public');

    $this->get('/storage/empresas/logo/nao-existe.png')->assertNotFound();
});
