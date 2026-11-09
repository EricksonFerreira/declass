<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('superadmin abre a tela de site publico da empresa', function () {
    $empresa = tenant();
    $admin = superadmin();

    $this->actingAs($admin)
        ->get(route('empresa.site.edit', $empresa))
        ->assertOk()
        ->assertSee('Site público')
        ->assertSee('modelo_classico', false)
        ->assertSee('modelo_hero', false)
        ->assertSee('modelo_minimal', false)
        ->assertSee('site_banner', false);
});

test('usuario de tenant nao acessa a tela de site publico', function () {
    $empresa = tenant();
    $usuario = usuarioTenant($empresa, 'administrador');

    $this->actingAs($usuario)
        ->get(route('empresa.site.edit', $empresa))
        ->assertForbidden();
});

test('update salva modelo, textos e toggles do site', function () {
    $empresa = tenant(['site_ativo' => 'N']);
    $admin = superadmin();

    $this->actingAs($admin)->put(route('empresa.site.update', $empresa), [
        'site_ativo' => 'S',
        'site_modelo' => 'hero',
        'site_titulo' => 'Educação que transforma',
        'site_descricao' => 'Nossa rede de escolas.',
        'site_whatsapp' => '(11) 99999-0000',
        'site_unidades' => 'S',
        'site_matricula' => 'N',
        'site_contato' => 'S',
    ])->assertRedirect(route('empresa.site.edit', $empresa));

    $empresa->refresh();
    expect($empresa->site_ativo)->toBe('S')
        ->and($empresa->site_modelo)->toBe('hero')
        ->and($empresa->site_titulo)->toBe('Educação que transforma')
        ->and($empresa->site_whatsapp)->toBe('11999990000')
        ->and($empresa->site_matricula)->toBe('N');
});

test('update com imagem salva o banner do site', function () {
    Storage::fake('public');

    $empresa = tenant();
    $admin = superadmin();

    $this->actingAs($admin)->put(route('empresa.site.update', $empresa), [
        'site_ativo' => 'S',
        'site_modelo' => 'classico',
        'site_banner' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
    ])->assertRedirect();

    $empresa->refresh();
    expect($empresa->site_banner)->not->toBeEmpty();
    Storage::disk('public')->assertExists($empresa->site_banner);
});

test('site publico usa o modelo escolhido', function () {
    $empresa = tenant([
        'site_ativo' => 'S',
        'site_modelo' => 'hero',
        'site_titulo' => 'Titulo Hero',
    ]);

    // Marcador do layout hero: seção de capa cheia com imagem de fundo.
    $this->get(route('rede.site', $empresa->slug))
        ->assertOk()
        ->assertSee('class="h-hero', false)
        ->assertSee('Titulo Hero');
});

test('modelo invalido e rejeitado', function () {
    $empresa = tenant();
    $admin = superadmin();

    $this->actingAs($admin)->put(route('empresa.site.update', $empresa), [
        'site_ativo' => 'S',
        'site_modelo' => 'inexistente',
    ])->assertSessionHasErrors('site_modelo');
});
