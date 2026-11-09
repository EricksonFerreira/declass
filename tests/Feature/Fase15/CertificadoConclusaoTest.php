<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupCertificado($empresa, string $statusMatricula = 'concluida'): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Certificado', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2025, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '9º Ano EF', 'segmento' => 'fundamental', 'emite_certificado' => true, 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '9º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoa = Pessoa::create([
        'nome' => 'Ana Concluinte', 'data_nascimento' => '2010-03-15',
        'empresa_id' => $empresa->id,
    ]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-C1', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => $statusMatricula,
        'numero_chamada' => 3, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'turma', 'aluno', 'matricula');
}

test('certificado de conclusao e baixado em pdf para matricula concluida', function () {
    $empresa = tenant();
    $ids = setupCertificado($empresa);
    $admin = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($admin)
        ->get(route('aluno.certificado', $ids['aluno']->id));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('certificado retorna 404 quando nao ha matricula concluida', function () {
    $empresa = tenant();
    $ids = setupCertificado($empresa, 'ativa');
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)
        ->get(route('aluno.certificado', $ids['aluno']->id))
        ->assertNotFound();
});

test('certificado de aluno de outro tenant retorna 404', function () {
    $empresa = tenant();
    $ids = setupCertificado($empresa);

    $outro = tenant(['nome' => 'Rede Cert B', 'slug' => 'rede-cert-b']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $adminOutro = usuarioTenant($outro, 'administrador');

    $this->actingAs($adminOutro)
        ->get(route('aluno.certificado', $ids['aluno']->id))
        ->assertNotFound();
});

test('botao de certificado aparece na ficha do aluno concluido', function () {
    $empresa = tenant();
    $ids = setupCertificado($empresa);
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)
        ->get(route('aluno.show', $ids['aluno']->id))
        ->assertOk()
        ->assertSee(route('aluno.certificado', $ids['aluno']->id), false);
});
