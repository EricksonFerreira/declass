<?php

use App\Models\Aluno;
use App\Models\AlunoDocumento;
use App\Models\Pessoa;
use App\Support\EmpresaContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function setupAlunoDoc($empresa, string $nome = 'Maria Silva'): Aluno
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id]);

    return Aluno::create([
        'pessoa_id' => $pessoa->id,
        'registro_academico' => 'RA-'.fake()->unique()->numberBetween(1000, 9999),
        'empresa_id' => $empresa->id,
    ]);
}

function payloadDocumento(array $overrides = []): array
{
    return array_merge([
        'tipo' => 'rg',
        'titulo' => 'RG frente',
        'descricao' => 'Documento de identidade',
    ], $overrides);
}

test('documento pode ser anexado ao aluno com arquivo', function () {
    Storage::fake('local');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = setupAlunoDoc($empresa);

    $response = $this->actingAs($admin)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento() + ['arquivo' => UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf')]
    );

    $response->assertRedirect(route('aluno.show', $aluno->id));
    $response->assertSessionHasNoErrors();

    $doc = AlunoDocumento::first();
    expect($doc)->not->toBeNull()
        ->and($doc->aluno_id)->toBe($aluno->id)
        ->and($doc->tipo)->toBe('rg')
        ->and($doc->arquivo_path)->not->toBeNull();

    Storage::disk('local')->assertExists($doc->arquivo_path);
});

test('documento exige arquivo no cadastro', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = setupAlunoDoc($empresa);

    $this->actingAs($admin)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento()
    )->assertSessionHasErrors('arquivo');
});

test('documento pode ser editado e excluido removendo arquivo', function () {
    Storage::fake('local');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = setupAlunoDoc($empresa);

    $this->actingAs($admin)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento() + ['arquivo' => UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf')]
    );
    $doc = AlunoDocumento::first();
    $path = $doc->arquivo_path;

    $this->actingAs($admin)->put(
        route('aluno.documento.update', [$aluno->id, $doc->id]),
        payloadDocumento(['tipo' => 'cpf', 'titulo' => 'CPF'])
    )->assertRedirect(route('aluno.show', $aluno->id));

    expect($doc->fresh()->tipo)->toBe('cpf')
        ->and($doc->fresh()->titulo)->toBe('CPF');

    $this->actingAs($admin)->delete(route('aluno.documento.destroy', [$aluno->id, $doc->id]));

    expect(AlunoDocumento::count())->toBe(0);
    Storage::disk('local')->assertMissing($path);
});

test('download do arquivo funciona', function () {
    Storage::fake('local');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = setupAlunoDoc($empresa);

    $this->actingAs($admin)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento() + ['arquivo' => UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf')]
    );
    $doc = AlunoDocumento::first();

    $this->actingAs($admin)->get(route('aluno.documento.arquivo', [$aluno->id, $doc->id]))
        ->assertOk();
});

test('documento nao pode ser gerenciado por aluno de outro tenant', function () {
    Storage::fake('local');

    $empresaA = tenant();
    $empresaB = tenant();
    $adminA = usuarioTenant($empresaA, 'administrador');

    $alunoA = setupAlunoDoc($empresaA, 'Aluno A');

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $alunoB = setupAlunoDoc($empresaB, 'Aluno B');

    // documento do aluno B nao resolve sob contexto de A (escopo tenant => 404)
    app(EmpresaContext::class)->setEmpresaId($empresaA->id);

    $this->actingAs($adminA)->post(
        route('aluno.documento.store', $alunoA->id),
        payloadDocumento() + ['arquivo' => UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf')]
    );
    $docA = AlunoDocumento::first();

    // tentar excluir doc de A passando aluno de B -> 404 (pertenceAoAluno falha)
    $this->actingAs($adminA)->delete(route('aluno.documento.destroy', [$alunoB->id, $docA->id]))
        ->assertNotFound();

    expect(AlunoDocumento::count())->toBe(1);
});

test('aluno show exibe documentos', function () {
    Storage::fake('local');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = setupAlunoDoc($empresa);

    $this->actingAs($admin)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento() + ['arquivo' => UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf')]
    );

    $this->actingAs($admin)->get(route('aluno.show', $aluno->id))
        ->assertOk()
        ->assertSee('RG frente')
        ->assertSee('RG / Identidade');
});

test('professor nao acessa documentos de alunos', function () {
    Storage::fake('local');

    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professor = usuarioTenant($empresa, 'professor');
    $aluno = setupAlunoDoc($empresa);

    $this->actingAs($admin)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento() + ['arquivo' => UploadedFile::fake()->create('rg.pdf', 100, 'application/pdf')]
    );
    $doc = AlunoDocumento::first();

    $this->actingAs($professor)->get(route('aluno.documento.arquivo', [$aluno->id, $doc->id]))
        ->assertForbidden();

    $this->actingAs($professor)->post(
        route('aluno.documento.store', $aluno->id),
        payloadDocumento(['titulo' => 'Tentativa']) + ['arquivo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]
    )->assertForbidden();
});
