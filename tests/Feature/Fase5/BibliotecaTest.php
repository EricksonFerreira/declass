<?php

use App\Models\Aluno;
use App\Models\EmprestimoLivro;
use App\Models\Escola;
use App\Models\Livro;
use App\Models\Pessoa;
use App\Support\EmpresaContext;

function setupBiblioteca($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);

    $pessoa = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'aluno');
}

function livroDemo(array $ids, array $overrides = []): Livro
{
    return Livro::create(array_merge([
        'empresa_id' => $ids['empresa']->id,
        'escola_id' => $ids['escola']->id,
        'titulo' => 'Livro teste',
        'autor' => 'Autor Teste',
        'categoria' => 'literatura',
        'exemplares' => 2,
        'ativo' => 'S',
    ], $overrides));
}

function emprestimoDemo(array $ids, Livro $livro, array $overrides = []): EmprestimoLivro
{
    return EmprestimoLivro::create(array_merge([
        'empresa_id' => $ids['empresa']->id,
        'livro_id' => $livro->id,
        'aluno_id' => $ids['aluno']->id,
        'data_emprestimo' => now()->subDays(10)->toDateString(),
        'data_devolucao_prevista' => now()->addDays(5)->toDateString(),
        'status' => 'ativo',
    ], $overrides));
}

test('bibliotecario cadastra livro e ele aparece no acervo', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;

    $user = usuarioTenant($empresa, 'bibliotecario');

    $this->actingAs($user)->post(route('livro.store'), [
        'escola_id' => $ids['escola']->id,
        'titulo' => 'O Alquimista',
        'autor' => 'Paulo Coelho',
        'categoria' => 'literatura',
        'exemplares' => 3,
        'ativo' => 'S',
    ])->assertRedirect();

    $this->assertDatabaseHas('livros', ['titulo' => 'O Alquimista', 'exemplares' => 3]);

    $this->actingAs($user)
        ->get(route('livro.index'))
        ->assertOk()
        ->assertSee('O Alquimista');
});

test('emprestimo respeita exemplares disponiveis', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;
    $livro = livroDemo($ids, ['exemplares' => 1]);

    $user = usuarioTenant($empresa, 'bibliotecario');

    $payload = [
        'aluno_id' => $ids['aluno']->id,
        'livro_id' => $livro->id,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(15)->toDateString(),
    ];

    $this->actingAs($user)->post(route('emprestimo.store'), $payload)->assertRedirect();
    $this->assertDatabaseHas('emprestimos_livros', ['livro_id' => $livro->id, 'status' => 'ativo']);

    // Segundo exemplar nao existe: mesmo livro, outro aluno -> erro
    $pessoa2 = Pessoa::create(['nome' => 'João Souza', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-0002', 'empresa_id' => $empresa->id,
    ]);

    $this->actingAs($user)->post(route('emprestimo.store'), $payload + ['aluno_id' => $aluno2->id])
        ->assertSessionHasErrors('livro_id');
});

test('aluno nao pode pegar o mesmo livro duas vezes', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;
    $livro = livroDemo($ids, ['exemplares' => 5]);

    $user = usuarioTenant($empresa, 'bibliotecario');

    $payload = [
        'aluno_id' => $ids['aluno']->id,
        'livro_id' => $livro->id,
        'data_emprestimo' => now()->toDateString(),
        'data_devolucao_prevista' => now()->addDays(15)->toDateString(),
    ];

    $this->actingAs($user)->post(route('emprestimo.store'), $payload)->assertRedirect();
    $this->actingAs($user)->post(route('emprestimo.store'), $payload)
        ->assertSessionHasErrors('livro_id');
});

test('devolver baixa o emprestimo e bloqueia segunda baixa', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;
    $livro = livroDemo($ids);
    $emp = emprestimoDemo($ids, $livro);

    $user = usuarioTenant($empresa, 'bibliotecario');

    $this->actingAs($user)
        ->post(route('emprestimo.devolver', $emp->id))
        ->assertRedirect();

    $emp->refresh();
    expect($emp->status)->toBe('devolvido')
        ->and($emp->data_devolucao)->not->toBeNull();

    // Livro volta a ficar disponivel
    expect($livro->fresh()->disponiveis())->toBe(2);

    // Segunda baixa falha
    $this->actingAs($user)
        ->post(route('emprestimo.devolver', $emp->id))
        ->assertStatus(422);
});

test('emprestimo devolvido nao pode ser excluido', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;
    $livro = livroDemo($ids);
    $emp = emprestimoDemo($ids, $livro, ['status' => 'devolvido', 'data_devolucao' => now()->toDateString()]);

    $user = usuarioTenant($empresa, 'bibliotecario');

    $this->actingAs($user)
        ->delete(route('emprestimo.destroy', $emp->id))
        ->assertForbidden();

    $this->assertDatabaseHas('emprestimos_livros', ['id' => $emp->id]);
});

test('filtro por situacao separa atrasados', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;
    $livro = livroDemo($ids, ['exemplares' => 10]);

    $noPrazo = emprestimoDemo($ids, $livro, ['data_devolucao_prevista' => now()->addDays(10)->toDateString()]);
    $atrasado = emprestimoDemo($ids, $livro, [
        'data_devolucao_prevista' => now()->subDays(3)->toDateString(),
    ]);

    $user = usuarioTenant($empresa, 'bibliotecario');

    // Aplica o filtro "atrasado" via sessao (mesmo formato do criarFiltros)
    $this->actingAs($user)->get(route('emprestimo.filtro', ['situacao' => 'atrasado']));

    $this->actingAs($user)
        ->get(route('emprestimo.index'))
        ->assertOk()
        ->assertSee($ids['aluno']->pessoa->nome)
        ->assertSee('Atrasado');
});

test('livro e emprestimo de outro tenant nao aparecem', function () {
    $empresaA = tenant();
    $idsA = setupBiblioteca($empresaA);
    $idsA['empresa'] = $empresaA;
    livroDemo($idsA, ['titulo' => 'Livro do tenant A']);

    $empresaB = tenant(['nome' => 'Escola B', 'slug' => 'escola-b']);
    setupBiblioteca($empresaB);

    $userB = usuarioTenant($empresaB, 'bibliotecario');

    $this->actingAs($userB)
        ->get(route('livro.index'))
        ->assertOk()
        ->assertDontSee('Livro do tenant A');
});

test('professor consulta acervo mas nao registra emprestimo', function () {
    $empresa = tenant();
    $ids = setupBiblioteca($empresa);
    $ids['empresa'] = $empresa;
    livroDemo($ids, ['titulo' => 'Livro visivel']);

    $prof = usuarioTenant($empresa, 'professor');

    $this->actingAs($prof)
        ->get(route('livro.index'))
        ->assertOk()
        ->assertSee('Livro visivel');

    $this->actingAs($prof)
        ->get(route('livro.create'))
        ->assertForbidden();

    $this->actingAs($prof)
        ->get(route('emprestimo.create'))
        ->assertForbidden();
});
