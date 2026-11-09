<?php

use App\Models\Empresa;
use App\Models\Escola;

function empresaComSite(array $attrs = []): Empresa
{
    return tenant(array_merge([
        'site_titulo' => 'Educação que transforma',
        'site_descricao' => 'Rede de escolas com ensino de excelência.',
        'site_whatsapp' => '(11) 98888-7777',
        'site_ativo' => 'S',
        'site_unidades' => 'S',
        'site_matricula' => 'S',
        'site_contato' => 'S',
        'telefone' => '(11) 4002-8922',
        'email' => 'contato@rede.test',
    ], $attrs));
}

function escolaDaRede(Empresa $empresa, array $attrs = []): Escola
{
    return Escola::create(array_merge([
        'empresa_id' => $empresa->id,
        'nome' => 'Unidade Norte',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'telefone' => '(11) 3000-0000',
        'endereco' => 'Rua das Flores, 100',
        'ativa' => 'S',
    ], $attrs));
}

test('site publico da empresa mostra marca, unidades, matricula e contato', function () {
    $empresa = empresaComSite();
    escolaDaRede($empresa);

    $this->get(route('rede.site', $empresa->slug))
        ->assertOk()
        ->assertSee($empresa->nome)
        ->assertSee('Educação que transforma')
        ->assertSee('Nossas unidades')
        ->assertSee('Unidade Norte')
        ->assertSee('São Paulo — SP')
        ->assertSee('Matrícula')
        ->assertSee(route('matricula_publica.create', [$empresa->slug, 'unidade-norte']), false)
        ->assertSee('Fale conosco')
        ->assertSee('(11) 4002-8922')
        ->assertSee(route('login'), false);
});

test('site desligado ou empresa inativa retornam 404', function () {
    $desligado = empresaComSite(['site_ativo' => 'N']);
    $this->get(route('rede.site', $desligado->slug))->assertNotFound();

    $inativa = empresaComSite(['ativo' => 'N']);
    $this->get(route('rede.site', $inativa->slug))->assertNotFound();
});

test('modulos do site respeitam os toggles da empresa', function () {
    $empresa = empresaComSite([
        'site_unidades' => 'N',
        'site_matricula' => 'N',
        'site_contato' => 'N',
    ]);
    escolaDaRede($empresa);

    $this->get(route('rede.site', $empresa->slug))
        ->assertOk()
        ->assertDontSee('Nossas unidades')
        ->assertDontSee('Fale conosco')
        ->assertDontSee('matricula/');
});

test('periodo de matricula encerrado avisa no site e esconde links de matricula', function () {
    $empresa = empresaComSite([
        'site_matricula' => 'F',
        'site_matricula_ano' => '2027',
    ]);
    escolaDaRede($empresa);

    $this->get(route('rede.site', $empresa->slug))
        ->assertOk()
        ->assertSee('Matrículas 2027 encerradas', false)
        ->assertSee('s-aviso--encerrada', false)
        ->assertDontSee('matricula/');
});

test('periodo de matricula aberto gera aviso automatico com o ano', function () {
    $empresa = empresaComSite([
        'site_matricula' => 'S',
        'site_matricula_ano' => '2027',
    ]);
    escolaDaRede($empresa);

    $this->get(route('rede.site', $empresa->slug))
        ->assertOk()
        ->assertSee('Matrículas 2027 abertas', false)
        ->assertSee('matricula/');
});

test('raiz do dominio personalizado serve o site da empresa', function () {
    $empresa = empresaComSite(['dominio' => 'rede-exemplo.test']);
    escolaDaRede($empresa);

    $this->get('http://rede-exemplo.test/')
        ->assertOk()
        ->assertSee($empresa->nome)
        ->assertSee('Nossas unidades');
});

test('dominio personalizado sem site ativo vai para o login', function () {
    empresaComSite(['dominio' => 'sem-site.test', 'site_ativo' => 'N']);

    $this->get('http://sem-site.test/')
        ->assertRedirect(route('login'));
});

test('seeder vincula superadmins ao tenant declass', function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $declass = Empresa::where('slug', 'declass')->firstOrFail();
    $superadmin = \App\Models\User::where('email', 'admin@declass.test')->firstOrFail();

    expect($superadmin->isSuperAdmin())->toBeTrue()
        ->and($superadmin->empresa_id)->toBe($declass->id);
});
