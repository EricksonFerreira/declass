<?php

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\PeriodoLetivo;
use App\Models\Turma;
use App\Support\EmpresaContext;

function plannerFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Unidade', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026,
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-12-15',
        'status' => 'letivo', 'periodicidade' => 'semestral',
        'empresa_id' => $empresa->id,
    ]);
    PeriodoLetivo::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'nome' => '1º Semestre', 'ordem' => 1, 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create(['nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);

    $turmaA = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano EF A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaB = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano EF B', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $turmaC = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano EF C', 'turno' => 'vespertino',
        'empresa_id' => $empresa->id,
    ]);

    $mat = Disciplina::create(['nome' => 'Matemática', 'empresa_id' => $empresa->id]);
    $por = Disciplina::create(['nome' => 'Português', 'empresa_id' => $empresa->id]);
    $art = Disciplina::create(['nome' => 'Arte', 'empresa_id' => $empresa->id]);

    $turmaA->disciplinas()->attach($mat->id, ['carga_horaria_semanal' => 5]);
    $turmaA->disciplinas()->attach($por->id, ['carga_horaria_semanal' => 4]);

    return compact('escola', 'anoLetivo', 'etapa', 'turmaA', 'turmaB', 'turmaC', 'mat', 'por', 'art');
}

test('show do ano letivo exibe o planejador com periodos, turmas e disciplinas', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['anoLetivo' => $anoLetivo] = plannerFixture($empresa);

    $this->actingAs($user)->get("/admin/ano-letivo/{$anoLetivo->id}")
        ->assertOk()
        ->assertSee('1º Semestre')
        ->assertSee('1º Ano EF A')
        ->assertSee('Replicar organização entre turmas')
        ->assertSee('Geração de turmas em massa');
});

test('replicar organizacao copia ofertas e cargas para as turmas destino', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['anoLetivo' => $anoLetivo, 'turmaA' => $turmaA, 'turmaB' => $turmaB, 'turmaC' => $turmaC, 'mat' => $mat, 'por' => $por] = plannerFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id, $turmaC->id],
    ])->assertRedirect(route('ano_letivo.show', $anoLetivo->id));

    foreach ([$turmaB, $turmaC] as $destino) {
        $ofertas = $destino->disciplinas()->get()->keyBy('id');
        expect($ofertas)->toHaveCount(2)
            ->and($ofertas[$mat->id]->pivot->carga_horaria_semanal)->toBe(5)
            ->and($ofertas[$por->id]->pivot->carga_horaria_semanal)->toBe(4);
    }
});

test('replicacao atualiza carga existente e nao remove ofertas do destino', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['turmaA' => $turmaA, 'turmaB' => $turmaB, 'mat' => $mat, 'por' => $por, 'art' => $art] = plannerFixture($empresa);

    // destino ja tem Matematica com carga diferente e uma oferta extra
    $turmaB->disciplinas()->attach($mat->id, ['carga_horaria_semanal' => 2]);
    $turmaB->disciplinas()->attach($art->id, ['carga_horaria_semanal' => 2]);

    $this->actingAs($user)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id],
    ])->assertRedirect();

    $ofertas = $turmaB->disciplinas()->get()->keyBy('id');
    expect($ofertas)->toHaveCount(3) // Arte preservada
        ->and($ofertas[$mat->id]->pivot->carga_horaria_semanal)->toBe(5) // carga atualizada
        ->and($ofertas[$por->id]->pivot->carga_horaria_semanal)->toBe(4) // nova oferta
        ->and($ofertas[$art->id])->not->toBeNull();
});

test('[PADRAO] validacao exige origem e destinos diferentes', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['turmaA' => $turmaA, 'turmaB' => $turmaB] = plannerFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [],
    ])->assertSessionHasErrors('turmas_destino');

    $this->actingAs($user)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaA->id],
    ])->assertSessionHasErrors('turmas_destino.0');
});

test('turma de outro tenant e rejeitada na replicacao', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['escola' => $escola, 'etapa' => $etapa, 'turmaA' => $turmaA] = plannerFixture($empresa);

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $escolaB = Escola::create(['nome' => 'Outra', 'empresa_id' => $empresaB->id]);
    $anoB = AnoLetivo::create(['escola_id' => $escolaB->id, 'ano' => 2026, 'empresa_id' => $empresaB->id]);
    $turmaB = Turma::create([
        'escola_id' => $escolaB->id, 'ano_letivo_id' => $anoB->id,
        'etapa_id' => $etapa->id, 'nome' => 'Turma B', 'empresa_id' => $empresaB->id,
    ]);

    $this->actingAs($user)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id],
    ])->assertSessionHasErrors('turmas_destino.0');
});

test('perfil sem permissao de turma nao replica organizacao', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'professor');
    ['turmaA' => $turmaA, 'turmaB' => $turmaB] = plannerFixture($empresa);

    $this->actingAs($user)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id],
    ])->assertForbidden();
});

test('replicacao copia atribuicoes de professores para as turmas destino', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof1 = usuarioTenant($empresa, 'professor', ['nome' => 'Prof Um']);
    $prof2 = usuarioTenant($empresa, 'professor', ['nome' => 'Prof Dois']);
    ['turmaA' => $turmaA, 'turmaB' => $turmaB, 'turmaC' => $turmaC, 'mat' => $mat, 'por' => $por] = plannerFixture($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turmaA->id,
        'disciplina_id' => $mat->id, 'user_id' => $prof1->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turmaA->id,
        'disciplina_id' => null, 'user_id' => $prof2->id,
        'papel' => 'auxiliar', 'ativo' => 'S',
    ]);

    $this->actingAs($admin)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id, $turmaC->id],
        'incluir_professores' => '1',
    ])->assertRedirect();

    foreach ([$turmaB, $turmaC] as $destino) {
        $atribuicoes = Atribuicao::where('turma_id', $destino->id)->get();
        expect($atribuicoes)->toHaveCount(2);

        $matAtrib = $atribuicoes->firstWhere('user_id', $prof1->id);
        expect($matAtrib->disciplina_id)->toBe($mat->id)
            ->and($matAtrib->papel)->toBe('titular')
            ->and($matAtrib->escola_id)->toBe($destino->escola_id);

        $geralAtrib = $atribuicoes->firstWhere('user_id', $prof2->id);
        expect($geralAtrib->disciplina_id)->toBeNull()
            ->and($geralAtrib->papel)->toBe('auxiliar');
    }
});

test('atribuicao existente no destino e atualizada sem duplicar', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    ['turmaA' => $turmaA, 'turmaB' => $turmaB, 'mat' => $mat] = plannerFixture($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turmaA->id,
        'disciplina_id' => $mat->id, 'user_id' => $prof->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);
    // destino ja tem o mesmo professor na mesma disciplina como auxiliar
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turmaB->id,
        'disciplina_id' => $mat->id, 'user_id' => $prof->id,
        'papel' => 'auxiliar', 'ativo' => 'S',
    ]);

    $this->actingAs($admin)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id],
    ])->assertRedirect();

    $atribuicoes = Atribuicao::where('turma_id', $turmaB->id)
        ->where('user_id', $prof->id)->get();

    expect($atribuicoes)->toHaveCount(1)
        ->and($atribuicoes->first()->papel)->toBe('titular');
});

test('incluir_professores desligado nao copia atribuicoes', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    ['turmaA' => $turmaA, 'turmaB' => $turmaB, 'mat' => $mat] = plannerFixture($empresa);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turmaA->id,
        'disciplina_id' => $mat->id, 'user_id' => $prof->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($admin)->post('/admin/turma/replicar-organizacao', [
        'turma_origem_id' => $turmaA->id,
        'turmas_destino' => [$turmaB->id],
        'incluir_professores' => '0',
    ])->assertRedirect();

    expect(Atribuicao::where('turma_id', $turmaB->id)->count())->toBe(0)
        ->and($turmaB->disciplinas()->count())->toBe(2); // disciplinas seguem replicadas
});
