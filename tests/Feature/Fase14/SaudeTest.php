<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\SaudeAluno;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupSaude($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Saúde', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '2º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '2º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Ana Saúde', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-SA1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'Mãe da Ana', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    return compact('escola', 'turma', 'aluno', 'responsavel');
}

test('secretaria cadastra ficha de saude e ela aparece na listagem', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->put(route('saude.update', $ids['aluno']->id), [
        'tipo_sanguineo' => 'O+',
        'alergias' => 'Amendoim, lactose',
        'medicamentos' => 'Bombinha de asma',
        'condicoes_cronicas' => 'Asma',
        'contato_emergencia_nome' => 'Maria Souza',
        'contato_emergencia_parentesco' => 'Mãe',
        'contato_emergencia_telefone' => '(11) 99999-0000',
        'observacoes' => 'Usar uniforme sem lã',
    ])->assertRedirect(route('saude.show', $ids['aluno']->id));

    $ficha = SaudeAluno::where('aluno_id', $ids['aluno']->id)->firstOrFail();
    expect($ficha->tipo_sanguineo)->toBe('O+')
        ->and($ficha->alergias)->toContain('Amendoim')
        ->and($ficha->empresa_id)->toBe($empresa->id);

    $this->actingAs($user)->get(route('saude.index'))
        ->assertOk()
        ->assertSee('Ana Saúde')
        ->assertSee('Alergias');

    $this->actingAs($user)->get(route('saude.show', $ids['aluno']->id))
        ->assertOk()
        ->assertSee('Amendoim, lactose')
        ->assertSee('Maria Souza');
});

test('salvar duas vezes atualiza a mesma ficha (upsert)', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $payload = ['alergias' => 'Poeira', 'tipo_sanguineo' => 'A+'];
    $this->actingAs($user)->put(route('saude.update', $ids['aluno']->id), $payload);
    $this->actingAs($user)->put(route('saude.update', $ids['aluno']->id), [
        'alergias' => 'Dipirona', 'tipo_sanguineo' => 'B-',
    ]);

    expect(SaudeAluno::where('aluno_id', $ids['aluno']->id)->count())->toBe(1);
    $ficha = SaudeAluno::where('aluno_id', $ids['aluno']->id)->first();
    expect($ficha->alergias)->toBe('Dipirona')
        ->and($ficha->tipo_sanguineo)->toBe('B-');
});

test('tipo sanguineo invalido e rejeitado', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->put(route('saude.update', $ids['aluno']->id), [
        'tipo_sanguineo' => 'XYZ',
    ])->assertSessionHasErrors('tipo_sanguineo');
});

test('show retorna 404 quando aluno nao tem ficha', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->get(route('saude.show', $ids['aluno']->id))->assertNotFound();
});

test('professor ve ficha mas nao edita', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);
    SaudeAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'alergias' => 'Lactose', 'ativo' => 'S',
    ]);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)->get(route('saude.index'))->assertOk();
    $this->actingAs($user)->get(route('saude.show', $ids['aluno']->id))->assertOk();
    $this->actingAs($user)->get(route('saude.edit', $ids['aluno']->id))->assertForbidden();
    $this->actingAs($user)->put(route('saude.update', $ids['aluno']->id), [])->assertForbidden();
});

test('portal mostra ficha apenas dos alunos vinculados', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);

    // Outro aluno da mesma empresa, sem vínculo com o responsável
    $pessoa2 = Pessoa::create(['nome' => 'Outro Aluno', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-SA2', 'empresa_id' => $empresa->id,
    ]);

    SaudeAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'alergias' => 'Amendoim', 'ativo' => 'S',
    ]);
    SaudeAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $aluno2->id,
        'alergias' => 'Secreta do Outro', 'ativo' => 'S',
    ]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.saude'))
        ->assertOk()
        ->assertSee('Ana Saúde')
        ->assertSee('Amendoim')
        ->assertDontSee('Secreta do Outro');
});

test('portal bloqueia conta sem aluno vinculado', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'responsavel_legal');

    $this->actingAs($user)->get(route('portal.saude'))->assertForbidden();
});

test('ficha de outro tenant nao aparece na listagem', function () {
    $empresa = tenant();
    $ids = setupSaude($empresa);
    SaudeAluno::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'alergias' => 'Secreta A', 'ativo' => 'S',
    ]);

    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b-saude']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $userB = usuarioTenant($outro, 'secretario');

    $this->actingAs($userB)
        ->get(route('saude.index'))
        ->assertOk()
        ->assertDontSee('Ana Saúde');
});
