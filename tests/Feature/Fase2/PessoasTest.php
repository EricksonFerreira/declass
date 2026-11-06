<?php

use App\Models\Aluno;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Support\EmpresaContext;

function payloadAluno(array $overrides = []): array
{
    return array_merge([
        'nome' => 'Maria Silva',
        'cpf' => '123.456.789-00',
        'data_nascimento' => '2012-05-10',
        'sexo' => 'F',
        'email' => 'maria@aluno.com',
        'telefone' => '11988887777',
        'endereco' => 'Rua A, 100',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'cep' => '01000-000',
        'registro_academico' => 'RA-0001',
        'data_ingresso' => '2025-02-01',
        'turno_preferencial' => 'matutino',
        'ativo' => 'S',
    ], $overrides);
}

function payloadResponsavel(array $overrides = []): array
{
    return array_merge([
        'nome' => 'João Responsável',
        'cpf' => '987.654.321-00',
        'data_nascimento' => '1980-01-15',
        'sexo' => 'M',
        'email' => 'joao@resp.com',
        'celular' => '11977776666',
        'profissao' => 'Engenheiro',
        'local_trabalho' => 'Empresa X',
        'ativo' => 'S',
    ], $overrides);
}

test('aluno pode ser criado criando pessoa automaticamente', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->get(route('aluno.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());
    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('aluno.index'));

    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $aluno = Aluno::first();
    expect($aluno)->not->toBeNull()
        ->and($aluno->pessoa->nome)->toBe('Maria Silva')
        ->and($aluno->pessoa->empresa_id)->toBe($empresa->id)
        ->and($aluno->registro_academico)->toBe('RA-0001');
});

test('responsavel pode ser criado criando pessoa automaticamente', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->get(route('responsavel.index'))->assertOk();

    $response = $this->actingAs($admin)->post(route('responsavel.store'), payloadResponsavel());
    $response->assertSessionHasNoErrors();

    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $resp = Responsavel::first();
    expect($resp)->not->toBeNull()
        ->and($resp->pessoa->nome)->toBe('João Responsável')
        ->and($resp->profissao)->toBe('Engenheiro');
});

test('aluno e responsavel podem ser vinculados', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());
    $this->actingAs($admin)->post(route('responsavel.store'), payloadResponsavel());

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $aluno = Aluno::first();
    $resp = Responsavel::first();

    $response = $this->actingAs($admin)->post(route('aluno.responsavel.store', $aluno->id), [
        'responsavel_id' => $resp->id,
        'tipo' => 'legal',
        'principal' => '1',
        'pode_retirar' => '1',
        'autoriza_contato' => '1',
    ]);
    $response->assertRedirect(route('aluno.show', $aluno->id));

    expect($aluno->fresh()->responsaveis)->toHaveCount(1)
        ->and($aluno->fresh()->responsaveis->first()->pivot->tipo)->toBe('legal');
});

test('vinculo duplicado para o mesmo tipo e rejeitado', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());
    $this->actingAs($admin)->post(route('responsavel.store'), payloadResponsavel());

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $aluno = Aluno::first();
    $resp = Responsavel::first();

    $payload = [
        'responsavel_id' => $resp->id, 'tipo' => 'pedagogico',
        'principal' => '0', 'pode_retirar' => '0', 'autoriza_contato' => '0',
    ];

    $this->actingAs($admin)->post(route('aluno.responsavel.store', $aluno->id), $payload);
    $this->actingAs($admin)->post(route('aluno.responsavel.store', $aluno->id), $payload)
        ->assertSessionHasNoErrors(); // atualiza o vinculo existente

    expect(DB::table('aluno_responsavel')->count())->toBe(1);
});

test('vinculo com responsavel de outro tenant e rejeitado', function () {
    $empresaA = tenant();
    $empresaB = tenant();
    $adminA = usuarioTenant($empresaA, 'administrador');
    $adminB = usuarioTenant($empresaB, 'administrador');

    $this->actingAs($adminA)->post(route('aluno.store'), payloadAluno());
    $this->actingAs($adminB)->post(route('responsavel.store'), payloadResponsavel());

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    $alunoA = Aluno::first();

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $respB = Responsavel::first();

    // Tenant A nao ve registros de B pelo escopo global.
    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    expect(Aluno::count())->toBe(1)
        ->and(Responsavel::count())->toBe(0);

    // E o Form Request rejeita o responsavel_id de outro tenant.
    $this->actingAs($adminA)->post(route('aluno.responsavel.store', $alunoA->id), [
        'responsavel_id' => $respB->id, 'tipo' => 'legal',
    ])->assertSessionHasErrors('responsavel_id');

    expect(DB::table('aluno_responsavel')->count())->toBe(0);
});

test('index de alunos lista registros do tenant', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno(['nome' => 'Ana Souza']));
    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno(['nome' => 'Bruno Lima', 'cpf' => '111.222.333-44']));

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    expect(Aluno::count())->toBe(2);

    $this->actingAs($admin)->get(route('aluno.index'))
        ->assertOk()
        ->assertSee('Ana Souza')
        ->assertSee('Bruno Lima');
});

test('validacao exige nome do aluno', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $payload = payloadAluno();
    unset($payload['nome']);

    $this->actingAs($admin)->post(route('aluno.store'), $payload)
        ->assertSessionHasErrors('nome');
});

test('cpf duplicado no mesmo tenant e rejeitado', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());
    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno(['nome' => 'Outra Pessoa']))
        ->assertSessionHasErrors('cpf');
});

test('professor nao acessa cadastro de alunos', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $aluno = Aluno::first();

    $this->actingAs($prof)->get(route('aluno.index'))->assertForbidden();
    $this->actingAs($prof)->get(route('aluno.show', $aluno->id))->assertForbidden();
    $this->actingAs($prof)->get(route('aluno.create'))->assertForbidden();
    $this->actingAs($prof)->post(route('aluno.store'), payloadAluno(['nome' => 'Teste']))->assertForbidden();
});

test('atualizar aluno atualiza a pessoa vinculada', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $aluno = Aluno::first();

    $response = $this->actingAs($admin)->put(route('aluno.update', $aluno->id), payloadAluno([
        'nome' => 'Maria Atualizada',
        'cpf' => '123.456.789-00', // mesmo CPF: unique ignora a propria pessoa
    ]));
    $response->assertSessionHasNoErrors();

    expect($aluno->fresh()->pessoa->nome)->toBe('Maria Atualizada');
});

test('show do aluno exibe responsaveis vinculados', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());
    $this->actingAs($admin)->post(route('responsavel.store'), payloadResponsavel());

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $aluno = Aluno::first();
    $resp = Responsavel::first();

    $this->actingAs($admin)->post(route('aluno.responsavel.store', $aluno->id), [
        'responsavel_id' => $resp->id, 'tipo' => 'legal',
    ]);

    $this->actingAs($admin)->get(route('aluno.show', $aluno->id))
        ->assertOk()
        ->assertSee('João Responsável');
});

test('show do responsavel exibe alunos vinculados', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->post(route('aluno.store'), payloadAluno());
    $this->actingAs($admin)->post(route('responsavel.store'), payloadResponsavel());

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $aluno = Aluno::first();
    $resp = Responsavel::first();

    $this->actingAs($admin)->post(route('aluno.responsavel.store', $aluno->id), [
        'responsavel_id' => $resp->id, 'tipo' => 'legal',
    ]);

    $this->actingAs($admin)->get(route('responsavel.show', $resp->id))
        ->assertOk()
        ->assertSee('Maria Silva');
});

test('pessoas tem escopo de tenant', function () {
    $empresaA = tenant();
    $empresaB = tenant();

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    Pessoa::create(['nome' => 'Pessoa A', 'empresa_id' => $empresaA->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    Pessoa::create(['nome' => 'Pessoa B', 'empresa_id' => $empresaB->id]);

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);
    expect(Pessoa::count())->toBe(1)
        ->and(Pessoa::first()->nome)->toBe('Pessoa A');
});
