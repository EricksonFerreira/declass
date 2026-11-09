<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Horario;
use App\Models\HorarioSlot;
use App\Models\HorarioTurma;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupHorariosPortal($empresa): array
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
        'tipo' => 'legal', 'principal' => true, 'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    $horario = Horario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turno' => 'matutino', 'nome' => 'Matutino A', 'ativo' => 'S',
    ]);
    $turma->update(['horario_id' => $horario->id]);

    foreach ([1, 2, 3, 4, 5] as $dia) {
        HorarioSlot::create([
            'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
            'horario_id' => $horario->id, 'turno' => 'matutino',
            'dia_semana' => $dia, 'ordem' => 1,
            'hora_inicio' => '07:30', 'hora_fim' => '08:20',
        ]);
    }

    $professor = usuarioTenant($empresa, 'professor', ['nome' => 'Prof. Carlos']);

    HorarioTurma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turma_id' => $turma->id, 'dia_semana' => 1, 'ordem' => 1,
        'disciplina_id' => $disciplina->id, 'user_id' => $professor->id, 'sala' => '12',
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'aluno', 'matricula', 'responsavel', 'horario', 'professor');
}

test('aluno ve sua grade semanal com disciplina, professor e sala', function () {
    $empresa = tenant();
    $ids = setupHorariosPortal($empresa);

    $user = usuarioTenant($empresa, 'aluno');
    $ids['aluno']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.horarios'))
        ->assertOk()
        ->assertSee('1º Ano A')
        ->assertSee('MAT')
        ->assertSee('Prof. Carlos')
        ->assertSee('07:30–08:20');
});

test('responsavel ve a grade semanal do filho', function () {
    $empresa = tenant();
    $ids = setupHorariosPortal($empresa);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.horarios'))
        ->assertOk()
        ->assertSee('1º Ano A')
        ->assertSee('MAT');
});

test('turma sem grade configurada mostra aviso', function () {
    $empresa = tenant();
    $ids = setupHorariosPortal($empresa);
    $ids['turma']->update(['horario_id' => null]);
    HorarioSlot::query()->where('horario_id', $ids['horario']->id)->delete();

    $user = usuarioTenant($empresa, 'aluno');
    $ids['aluno']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.horarios'))
        ->assertOk()
        ->assertSee('grade de horário configurada');
});

test('aluno sem matricula ativa ve estado vazio', function () {
    $empresa = tenant();
    $ids = setupHorariosPortal($empresa);
    $ids['matricula']->update(['status' => 'cancelada']);

    $user = usuarioTenant($empresa, 'aluno');
    $ids['aluno']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.horarios'))
        ->assertOk()
        ->assertSee('não tem matrícula ativa');
});

test('usuario sem aluno vinculado recebe 403 nos horarios', function () {
    $empresa = tenant();
    setupHorariosPortal($empresa);

    $user = usuarioTenant($empresa, 'responsavel_legal');

    $this->actingAs($user)
        ->get(route('portal.horarios'))
        ->assertForbidden();
});

test('responsavel nao ve grade de aluno nao vinculado', function () {
    $empresa = tenant();
    $ids = setupHorariosPortal($empresa);

    // Outro responsavel, vinculado a um aluno sem matricula
    $respOutro = Responsavel::create([
        'pessoa_id' => Pessoa::create(['nome' => 'Outro Responsável', 'empresa_id' => $empresa->id])->id,
        'empresa_id' => $empresa->id,
    ]);
    $alunoOutro = Aluno::create([
        'pessoa_id' => Pessoa::create(['nome' => 'Outro Aluno', 'empresa_id' => $empresa->id])->id,
        'escola_id' => $ids['escola']->id, 'empresa_id' => $empresa->id,
    ]);
    $alunoOutro->responsaveis()->attach($respOutro->id, ['tipo' => 'legal', 'principal' => true]);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $respOutro->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.horarios'))
        ->assertOk()
        ->assertSee('não tem matrícula ativa')
        ->assertDontSee('Prof. Carlos');
});
