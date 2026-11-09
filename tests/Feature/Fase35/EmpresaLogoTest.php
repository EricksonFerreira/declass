<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('logo valido e salvo no update da empresa', function () {
    Storage::fake('public');

    $empresa = tenant();
    $admin = superadmin();

    $this->actingAs($admin)->put(route('empresa.update', $empresa), [
        'nome' => $empresa->nome,
        'slug' => $empresa->slug,
        'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
    ])->assertRedirect();

    $logoPath = $empresa->refresh()->logo_path;
    expect($logoPath)->not->toBeEmpty();
    Storage::disk('public')->assertExists($logoPath);
});

test('upload de logo quebrado retorna erro de validacao, nao erro 500', function () {
    Storage::fake('public');

    $empresa = tenant();
    $admin = superadmin();

    $fake = UploadedFile::fake()->image('logo.png');
    $quebrado = new UploadedFile(
        $fake->getPathname(),
        'logo.png',
        'image/png',
        UPLOAD_ERR_PARTIAL,
        true
    );

    $this->actingAs($admin)->put(route('empresa.update', $empresa), [
        'nome' => $empresa->nome,
        'slug' => $empresa->slug,
        'logo' => $quebrado,
    ])->assertSessionHasErrors('logo');

    expect($empresa->refresh()->logo_path)->toBeNull();
});
