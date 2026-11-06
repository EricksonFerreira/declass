<?php

use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Ocorrencia;
use App\Models\Pessoa;
use App\Support\EmpresaContext;

function setupOcorrencia($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Disciplina', 'empresa_id' => $empresa->id]);
    $pessoa = Pessoa::create(['nome' => 'Aluno Ocorrencia', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id, 'registro_academico' => 'RA-O1', 'ativo' => 'S',
    ]);

    return compact('escola', 'aluno');
}

test('secretaria registra ocorrencia com tipo e gravidade', function () {
    $empresa = tenant();
    $ids = setupOcorrencia($empresa);
    $user = usuarioTenant($empresa, 'secretario');

    $this->actingAs($user)->post(route('ocorrencia.store'), [
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'data' => now()->toDateString(),
        'tipo' => 'advertencia',
        'gravidade' => 'media',
        'local' => 'Pátio',
        'descricao' => 'Atraso reiterado às aulas',
        'medida_adotada' => 'Convocação dos pais',
        'notifica_responsavel' => '1',
        'ativo' => 'S',
    ])->assertRedirect();

    $ocorrencia = Ocorrencia::first();
    expect($ocorrencia)->not->toBeNull()
        ->and($ocorrencia->tipo)->toBe('advertencia')
        ->and($ocorrencia->user_id)->toBe($user->id)
        ->and($ocorrencia->notifica_responsavel)->toBeTrue();
});

test('tipo e gravidade invalidos falham na validacao', function () {
    $empresa = tenant();
    $ids = setupOcorrencia($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('ocorrencia.store'), [
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'data' => now()->toDateString(),
        'tipo' => 'inexistente',
        'gravidade' => 'extrema',
        'descricao' => 'Teste',
    ])->assertSessionHasErrors(['tipo', 'gravidade']);
});

test('aluno de outro tenant nao pode receber ocorrencia', function () {
    $empresa = tenant();
    $ids = setupOcorrencia($empresa);

    $outro = tenant(['nome' => 'Rede C', 'slug' => 'rede-c']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $pessoaB = Pessoa::create(['nome' => 'Aluno B', 'empresa_id' => $outro->id]);
    $alunoB = Aluno::create([
        'empresa_id' => $outro->id, 'pessoa_id' => $pessoaB->id,
        'escola_id' => Escola::create(['nome' => 'Esc B', 'empresa_id' => $outro->id])->id,
        'registro_academico' => 'RA-B1', 'ativo' => 'S',
    ]);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');

    $this->actingAs($user)->post(route('ocorrencia.store'), [
        'aluno_id' => $alunoB->id,
        'escola_id' => $ids['escola']->id,
        'data' => now()->toDateString(),
        'tipo' => 'elogio',
        'descricao' => 'Teste cross-tenant',
    ])->assertSessionHasErrors('aluno_id');
});

test('ocorrencias tem escopo de tenant', function () {
    $empresa = tenant();
    $ids = setupOcorrencia($empresa);
    Ocorrencia::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id, 'data' => now()->toDateString(),
        'tipo' => 'elogio', 'descricao' => 'Bom desempenho', 'ativo' => 'S',
    ]);

    $outro = tenant(['nome' => 'Rede D', 'slug' => 'rede-d']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);

    expect(Ocorrencia::count())->toBe(0);

    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $user = usuarioTenant($empresa, 'administrador');
    $this->actingAs($user)->get(route('ocorrencia.index'))
        ->assertOk()->assertSee('Bom desempenho');
});

test('professor nao acessa ocorrencias', function () {
    $empresa = tenant();
    $ids = setupOcorrencia($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $ocorrencia = Ocorrencia::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id, 'data' => now()->toDateString(),
        'tipo' => 'ocorrencia', 'gravidade' => 'leve',
        'descricao' => 'Conversa durante explicação', 'ativo' => 'S',
    ]);

    $this->actingAs($user)->get(route('ocorrencia.index'))->assertForbidden();
    $this->actingAs($user)->post(route('ocorrencia.store'), [
        'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id,
        'data' => now()->toDateString(),
        'tipo' => 'ocorrencia',
        'gravidade' => 'leve',
        'descricao' => 'Conversa durante explicação',
    ])->assertForbidden();
    $this->actingAs($user)->get(route('ocorrencia.edit', $ocorrencia->id))
        ->assertForbidden();
    $this->actingAs($user)->delete(route('ocorrencia.destroy', $ocorrencia->id))
        ->assertForbidden();
});

test('filtros por tipo e gravidade funcionam', function () {
    $empresa = tenant();
    $ids = setupOcorrencia($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    Ocorrencia::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id, 'data' => now()->toDateString(),
        'tipo' => 'elogio', 'gravidade' => 'leve', 'descricao' => 'ELOGIO-FILTRO', 'ativo' => 'S',
    ]);
    Ocorrencia::create([
        'empresa_id' => $empresa->id, 'aluno_id' => $ids['aluno']->id,
        'escola_id' => $ids['escola']->id, 'data' => now()->toDateString(),
        'tipo' => 'suspensao', 'gravidade' => 'grave', 'descricao' => 'SUSPENSAO-FILTRO', 'ativo' => 'S',
    ]);

    $this->actingAs($user)->get(route('ocorrencia.filtro', ['tipo' => 'elogio']));

    $this->actingAs($user)->get(route('ocorrencia.index'))
        ->assertOk()->assertSee('ELOGIO-FILTRO')->assertDontSee('SUSPENSAO-FILTRO');
});
