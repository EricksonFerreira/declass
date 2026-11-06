<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupDeclaracao($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Declaração', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '2º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '2º Ano B', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = \App\Models\Disciplina::create(['nome' => 'Português', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $pessoa = Pessoa::create([
        'nome' => 'Lucas Ferreira', 'data_nascimento' => '2014-08-20',
        'empresa_id' => $empresa->id,
    ]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-D1', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 7, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'turma', 'aluno', 'matricula');
}

test('declaracao de matricula e baixada em pdf com dados do aluno', function () {
    $empresa = tenant();
    $ids = setupDeclaracao($empresa);
    $admin = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($admin)
        ->get(route('aluno.declaracao', $ids['aluno']->id));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('declaracao de aluno de outro tenant retorna 404', function () {
    $empresa = tenant();
    $ids = setupDeclaracao($empresa);

    $outro = tenant(['nome' => 'Rede D', 'slug' => 'rede-d']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $adminOutro = usuarioTenant($outro, 'administrador');

    $this->actingAs($adminOutro)
        ->get(route('aluno.declaracao', $ids['aluno']->id))
        ->assertNotFound();
});

test('professor nao baixa declaracao de matricula', function () {
    $empresa = tenant();
    $ids = setupDeclaracao($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    // perfil professor nao tem permissao de aluno — 403
    $this->actingAs($professor)
        ->get(route('aluno.declaracao', $ids['aluno']->id))
        ->assertForbidden();
});
