<?php

use JeroenNoten\LaravelAdminLte\AdminLte;

test('superadmin ve a tela de espelhamento com os usuarios da empresa', function () {
    $empresa = tenant();
    $admin = superadmin();
    $alvo = usuarioTenant($empresa, 'professor', ['nome' => 'Prof. Espelho']);

    $this->actingAs($admin)
        ->get(route('espelhamento.index'))
        ->assertOk()
        ->assertSee('Espelhar acesso')
        ->assertSee('Prof. Espelho')
        ->assertSee($alvo->email);
});

test('usuario comum nao acessa a tela de espelhamento', function () {
    $empresa = tenant();
    $usuario = usuarioTenant($empresa, 'professor');

    $this->actingAs($usuario)
        ->get(route('espelhamento.index'))
        ->assertForbidden();
});

test('superadmin espelha acessos mantendo a propria identidade', function () {
    $empresa = tenant();
    $admin = superadmin();
    $alvo = usuarioTenant($empresa, 'professor');

    $this->actingAs($admin)
        ->post(route('espelhamento.store', $alvo))
        ->assertRedirect(route('home'));

    // Identidade preservada: quem segue logado e o superadmin.
    expect(auth()->id())->toBe($admin->id)
        ->and(session('espelhando_usuario_id'))->toBe($alvo->id)
        ->and(session('empresa_context_id'))->toBe($empresa->id);

    // Banner visivel nas telas.
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Espelhando o acesso de')
        ->assertSee($alvo->name);
});

test('permissoes avaliadas durante o espelho sao as do usuario espelhado', function () {
    $empresa = tenant();
    $admin = superadmin();
    $professor = usuarioTenant($empresa, 'professor');

    // Professor nao administra usuarios — espelhando-o, o superadmin tambem nao.
    $this->actingAs($admin)
        ->post(route('espelhamento.store', $professor));

    $this->get(route('usuario.index'))->assertForbidden();
});

test('menu do superadmin espelhando professor mostra o menu do professor', function () {
    $empresa = tenant();
    $admin = superadmin();
    $professor = usuarioTenant($empresa, 'professor');

    $this->actingAs($admin)
        ->post(route('espelhamento.store', $professor));

    $menu = app(AdminLte::class)->menu('sidebar');
    $textos = collect($menu)->pluck('text')->all();

    expect($textos)->toContain('Minhas Turmas')
        ->and($textos)->not->toContain('Configurações');
});

test('parar espelhamento restaura o acesso e o contexto de empresa', function () {
    $empresa = tenant();
    $admin = superadmin();
    $alvo = usuarioTenant($empresa, 'professor');

    // Superadmin observava "todas as empresas" (0).
    $this->actingAs($admin)
        ->withSession(['empresa_context_id' => 0])
        ->post(route('espelhamento.store', $alvo));

    expect(session('espelhando_usuario_id'))->toBe($alvo->id);

    $this->delete(route('espelhamento.destroy'))
        ->assertRedirect(route('espelhamento.index'));

    expect(session('espelhando_usuario_id'))->toBeNull()
        ->and(session('empresa_context_id'))->toBe(0);

    // Voltou a acessar telas de superadmin.
    $this->get(route('usuario.index'))->assertOk();
});

test('nao e possivel espelhar superadmin nem a si mesmo', function () {
    $admin = superadmin();
    $outroAdmin = superadmin(['name' => 'Outro Admin']);

    $this->actingAs($admin);

    $this->post(route('espelhamento.store', $outroAdmin))
        ->assertSessionHas('error');
    expect(session('espelhando_usuario_id'))->toBeNull();

    $this->post(route('espelhamento.store', $admin))
        ->assertSessionHas('error');
    expect(session('espelhando_usuario_id'))->toBeNull();
});

test('item espelhar acesso aparece no menu do superadmin', function () {
    $admin = superadmin();
    $this->actingAs($admin);

    $menu = app(AdminLte::class)->menu('sidebar');
    $config = collect($menu)->firstWhere('key', 'configuracoes');
    $itens = collect($config['submenu'] ?? [])->pluck('text')->all();

    expect($itens)->toContain('Espelhar acesso')
        ->and($itens)->toContain('Comandos')
        ->and($itens)->toContain('Logs');
});
