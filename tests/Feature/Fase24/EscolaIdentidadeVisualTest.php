<?php

use App\Models\Empresa;
use App\Models\Escola;
use App\Support\EmpresaContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function escolaBrandingFixture(Empresa $empresa, array $attrs = []): Escola
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    return Escola::create(array_merge([
        'nome' => 'Escola Brand', 'empresa_id' => $empresa->id, 'ativa' => 'S',
    ], $attrs));
}

test('formulario publico usa cores e logo da escola', function () {
    Storage::fake('public');
    Storage::disk('public')->put('escolas/logo/logo.png', 'img');

    $empresa = tenant(['slug' => 'rede-brand']);
    $escola = escolaBrandingFixture($empresa, [
        'cor_primaria' => '#7c3aed',
        'cor_secundaria' => '#db2777',
        'logo_path' => 'escolas/logo/logo.png',
    ]);

    $this->get("/matricula/{$empresa->slug}/{$escola->slug}")
        ->assertOk()
        ->assertSee('--cor-primaria: #7c3aed', false)
        ->assertSee('--cor-secundaria: #db2777', false)
        ->assertSee('escolas/logo/logo.png', false)
        ->assertDontSee('class="logo-inicial"', false);
});

test('formulario publico usa logo da empresa quando escola nao tem', function () {
    Storage::fake('public');
    Storage::disk('public')->put('empresas/logo/empresa.png', 'img');

    $empresa = tenant(['slug' => 'rede-brand2', 'logo_path' => 'empresas/logo/empresa.png']);
    $escola = escolaBrandingFixture($empresa);

    $this->get("/matricula/{$empresa->slug}/{$escola->slug}")
        ->assertOk()
        ->assertSee('empresas/logo/empresa.png', false)
        ->assertSee('--cor-primaria: #1e3a8a', false);
});

test('formulario publico sem branding mostra inicial e cores padrao', function () {
    $empresa = tenant(['slug' => 'rede-brand3']);
    $escola = escolaBrandingFixture($empresa);

    $this->get("/matricula/{$empresa->slug}/{$escola->slug}")
        ->assertOk()
        ->assertSee('class="logo-inicial"', false)
        ->assertSee('>E<', false)
        ->assertSee('--cor-primaria: #1e3a8a', false)
        ->assertSee('--cor-secundaria: #312e81', false);
});

test('cadastro de escola salva logo enviada', function () {
    Storage::fake('public');
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->post('/admin/escola', [
        'nome' => 'Escola com Logo',
        'cor_primaria' => '#16a34a',
        'logo' => UploadedFile::fake()->image('logo.png', 400, 200),
    ])->assertRedirect();

    $escola = Escola::where('nome', 'Escola com Logo')->firstOrFail();
    expect($escola->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($escola->logo_path);
});

test('edicao de escola substitui logo e remove arquivo antigo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('escolas/logo/antiga.png', 'img');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $escola = escolaBrandingFixture($empresa, ['logo_path' => 'escolas/logo/antiga.png']);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->put("/admin/escola/{$escola->id}", [
        'nome' => $escola->nome,
        'logo' => UploadedFile::fake()->image('nova.png', 400, 200),
    ])->assertRedirect();

    $escola->refresh();
    expect($escola->logo_path)->not->toBe('escolas/logo/antiga.png');
    Storage::disk('public')->assertMissing('escolas/logo/antiga.png');
    Storage::disk('public')->assertExists($escola->logo_path);
});

test('edicao de escola sem logo mantem a atual', function () {
    Storage::fake('public');
    Storage::disk('public')->put('escolas/logo/manter.png', 'img');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $escola = escolaBrandingFixture($empresa, ['logo_path' => 'escolas/logo/manter.png']);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->put("/admin/escola/{$escola->id}", [
        'nome' => $escola->nome,
        'cor_primaria' => '#1d4ed8',
    ])->assertRedirect();

    expect($escola->fresh()->logo_path)->toBe('escolas/logo/manter.png');
    Storage::disk('public')->assertExists('escolas/logo/manter.png');
});
