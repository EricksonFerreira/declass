<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupPortal($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);

    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'João Pai', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);

    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'aluno', 'matricula', 'responsavel');
}

test('responsavel vinculado ve o portal com o filho', function () {
    $empresa = tenant();
    $ids = setupPortal($empresa);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.index'))
        ->assertOk()
        ->assertSee('Maria Silva');
});

test('responsavel acessa boletim do filho vinculado', function () {
    $empresa = tenant();
    $ids = setupPortal($empresa);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.aluno', $ids['aluno']->id))
        ->assertOk()
        ->assertSee('Matemática');
});

test('responsavel nao acessa boletim de aluno nao vinculado', function () {
    $empresa = tenant();
    $ids = setupPortal($empresa);

    // Segundo aluno sem vinculo com o responsavel
    $pessoa2 = Pessoa::create(['nome' => 'Pedro Outro', 'empresa_id' => $empresa->id]);
    $aluno2 = Aluno::create([
        'pessoa_id' => $pessoa2->id, 'escola_id' => $ids['escola']->id,
        'registro_academico' => 'RA-0002', 'empresa_id' => $empresa->id,
    ]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.aluno', $aluno2->id))
        ->assertForbidden();
});

test('aluno com conta propria ve seu boletim', function () {
    $empresa = tenant();
    $ids = setupPortal($empresa);

    $user = usuarioTenant($empresa, 'aluno');
    $ids['aluno']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.index'))
        ->assertOk()
        ->assertSee('Maria Silva');

    $this->actingAs($user)
        ->get(route('portal.aluno', $ids['aluno']->id))
        ->assertOk();
});

test('usuario sem aluno vinculado recebe 403 no portal', function () {
    $empresa = tenant();
    setupPortal($empresa);

    $user = usuarioTenant($empresa, 'responsavel_legal');

    $this->actingAs($user)
        ->get(route('portal.index'))
        ->assertForbidden();
});

test('usuario de outro tenant nao acessa aluno alheio', function () {
    $empresaA = tenant();
    $ids = setupPortal($empresaA);

    $empresaB = tenant();
    $userB = usuarioTenant($empresaB, 'responsavel_legal');

    app(EmpresaContext::class)->setEmpresaId($empresaA->id);

    $this->actingAs($userB)
        ->get(route('portal.aluno', $ids['aluno']->id))
        ->assertForbidden();
});

test('staff sem perfil de portal nao acessa', function () {
    $empresa = tenant();
    setupPortal($empresa);

    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)
        ->get(route('portal.index'))
        ->assertForbidden();
});
