<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Aviso;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Empresa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Models\User;
use App\Support\EmpresaContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Massa de demonstracao do ciclo academico completo.
 *
 * Pressupoe a empresa 'escola-demo' (criada pelo DatabaseSeeder).
 * Popula estrutura, pessoas, matriculas, diario, frequencia,
 * avaliacoes, notas e contas de portal — tudo idempotente:
 * se o tenant ja tiver alunos, o seeder nao duplica nada.
 *
 * Execute: php artisan db:seed --class=DemoEscolarSeeder
 *
 * ─────────────────────────────────────────────────────────────
 * COMO ACESSAR (tela /login — 3 campos)
 * ─────────────────────────────────────────────────────────────
 *
 * Campo "Slug da empresa":  escola-demo
 * Campo "E-mail ou usuário": um dos e-mails abaixo
 * Campo "Senha":             password   (igual para todos)
 *
 * Contas de teste por perfil:
 *
 *  diretor@escolademo.test        administrador  — acesso total a tudo
 *  prof1@escolademo.test          professor      — diario, chamada,
 *                                                 avaliacoes e boletim
 *                                                 apenas das turmas
 *                                                 em que leciona
 *  prof2@escolademo.test          professor      — idem prof1
 *  secretaria@escolademo.test     secretario    — cadastro e gestao
 *                                                 Academica/Administrativa
 *  coordenador@escolademo.test    coordenador    — visao academica
 *                                                 (sem escrita em
 *                                                 diarios/notas)
 *  responsavel@escolademo.test    responsavel    — PORTAL: ve o
 *                                                 boletim dos 2
 *                                                 primeiros alunos
 *  aluno@escolademo.test          aluno          — PORTAL: ve o
 *                                                 proprio boletim
 *  financeiro@escolademo.test     financeiro     — mensalidades,
 *                                                 planos, baixas e
 *                                                 inadimplencia
 *  bibliotecario@escolademo.test   bibliotecario  — acervo e
 *                                                 emprestimos de
 *                                                 livros
 *
 * Superadmin da plataforma (slug diferente):
 *  slug:   declass
 *  e-mail: admin@declass.test
 *  senha:  password
 *
 * O que conferir apos o login:
 *  - Menu lateral + cards do dashboard mudam conforme o perfil
 *  - responsavel/aluno caem direto no "Portal Familiar"
 *  - professor so ve as proprias turmas no diario/avaliacoes
 *  - boletim mostra medias, frequencia e notas por disciplina
 * ─────────────────────────────────────────────────────────────
 */
class DemoEscolarSeeder extends Seeder
{
    private const SENHA = 'password';

    public function run(): void
    {
        $empresa = Empresa::query()->where('slug', 'escola-demo')->first();

        if (! $empresa) {
            $this->command->warn('Empresa escola-demo nao encontrada. Rode db:seed primeiro.');

            return;
        }

        app(EmpresaContext::class)->setEmpresaId($empresa->id);

        $this->call(PerfisEscolaresSeeder::class);

        $escola = Escola::query()->where('empresa_id', $empresa->id)->first();
        $usuarios = $this->usuarios($empresa);

        // Massa academica: so popula se o tenant ainda nao tem alunos.
        if (! Aluno::query()->where('empresa_id', $empresa->id)->exists()) {
            $this->estruturar($empresa, $escola);
            [$alunos] = $this->pessoas($empresa, $escola, $usuarios);
            $this->operacao($empresa, $usuarios, $alunos);
        }

        // Financeiro: idempotente — pode ser reexecutado sobre a
        // massa existente para gerar planos e mensalidades.
        $this->financeiro($empresa);

        // Comunicacao: avisos gerais e por turma para o portal.
        $this->comunicacao($empresa);

        // Biblioteca: acervo e circulacao de emprestimos.
        $this->biblioteca($empresa);

        $this->command->info('Massa demo criada com sucesso.');
    }

    /** Anos letivos, etapas, disciplinas, turmas e atribuicoes. */
    private function estruturar(Empresa $empresa, Escola $escola): void
    {
        $anoLetivo = \App\Models\AnoLetivo::firstOrCreate(
            ['escola_id' => $escola->id, 'ano' => 2026],
            ['empresa_id' => $empresa->id, 'status' => 'ativo']
        );

        $etapas = [];
        foreach ([
            ['Educação Infantil', 'infantil', 1],
            ['Fundamental I', 'fundamental', 2],
            ['Fundamental II', 'fundamental', 3],
            ['Ensino Médio', 'medio', 4],
        ] as [$nome, $segmento, $ordem]) {
            $etapas[$segmento.$ordem] = Etapa::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome],
                ['segmento' => $segmento, 'ordem' => $ordem]
            );
        }

        $disciplinas = [];
        foreach ([
            ['Matemática', 'MAT'], ['Português', 'POR'], ['Ciências', 'CIE'],
            ['História', 'HIS'], ['Geografia', 'GEO'], ['Inglês', 'ING'],
            ['Educação Física', 'EDF'], ['Artes', 'ART'],
        ] as [$nome, $sigla]) {
            $disciplinas[$sigla] = Disciplina::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome],
                ['sigla' => $sigla]
            );
        }

        $turmas = [];
        foreach ([
            ['1º Ano A', 'matutino', $etapas['fundamental2'], ['MAT', 'POR', 'CIE']],
            ['5º Ano A', 'vespertino', $etapas['fundamental3'], ['MAT', 'POR', 'CIE', 'HIS', 'GEO', 'ING']],
        ] as [$nome, $turno, $etapa, $siglas]) {
            $turma = Turma::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome, 'ano_letivo_id' => $anoLetivo->id],
                [
                    'escola_id' => $escola->id,
                    'etapa_id' => $etapa->id,
                    'turno' => $turno,
                    'sala' => 'Sala '.fake()->unique()->numberBetween(1, 20),
                    'capacidade_maxima' => 35,
                    'ativo' => 'S',
                ]
            );
            $turmas[$nome] = $turma;

            foreach ($siglas as $sigla) {
                DB::table('turma_disciplina')->updateOrInsert(
                    ['turma_id' => $turma->id, 'disciplina_id' => $disciplinas[$sigla]->id],
                    ['carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Atribuicoes: professor titular por turma+disciplina
        $professores = User::query()
            ->where('empresa_id', $empresa->id)
            ->whereIn('email', ['prof1@escolademo.test', 'prof2@escolademo.test'])
            ->get();

        $i = 0;
        foreach ($turmas as $turma) {
            foreach ($turma->disciplinas as $disciplina) {
                Atribuicao::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'turma_id' => $turma->id,
                        'disciplina_id' => $disciplina->id,
                    ],
                    [
                        'user_id' => $professores[$i % max(1, $professores->count())]->id ?? null,
                        'papel' => 'titular',
                        'ativo' => 'S',
                    ]
                );
                $i++;
            }
        }
    }

    /** Usuarios de acesso para testar os perfis. */
    private function usuarios(Empresa $empresa): array
    {
        $criar = function (string $email, string $nome, string $perfil) use ($empresa) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nome,
                    'nome' => $nome,
                    'password' => Hash::make(self::SENHA),
                    'empresa_id' => $empresa->id,
                    'ativo' => true,
                ]
            );

            $roleId = DB::table('roles')
                ->where('empresa_id', $empresa->id)
                ->where('name', $perfil)
                ->where('guard_name', 'web')
                ->value('id');

            DB::table('users')->where('id', $user->id)->update([
                'role_id' => $roleId,
                'email_verified_at' => now(),
            ]);

            return $user->fresh();
        };

        return [
            'prof1' => $criar('prof1@escolademo.test', 'Professora Ana Souza', 'professor'),
            'prof2' => $criar('prof2@escolademo.test', 'Professor Carlos Lima', 'professor'),
            'secretaria' => $criar('secretaria@escolademo.test', 'Secretaria Beatriz', 'secretario'),
            'coordenador' => $criar('coordenador@escolademo.test', 'Coordenadora Paula Reis', 'coordenador_pedagogico'),
            'responsavel' => $criar('responsavel@escolademo.test', 'Responsável Demo', 'responsavel_legal'),
            'aluno' => $criar('aluno@escolademo.test', 'Aluno Demo Portal', 'aluno'),
            'financeiro' => $criar('financeiro@escolademo.test', 'Financeiro Demo', 'financeiro'),
            'bibliotecario' => $criar('bibliotecario@escolademo.test', 'Bibliotecário Demo', 'bibliotecario'),
        ];
    }

    /** Alunos, responsaveis e vinculos. */
    private function pessoas(Empresa $empresa, Escola $escola, array $usuarios): array
    {
        $alunos = [];
        $nomes = [
            ['Ana Beatriz Oliveira', 'F', '2015-03-12'],
            ['Bruno Henrique Santos', 'M', '2014-07-25'],
            ['Carla Eduarda Lima', 'F', '2013-11-02'],
            ['Diego Fernandes Rocha', 'M', '2015-01-30'],
            ['Elisa Martins Costa', 'F', '2014-09-18'],
            ['Felipe Augusto Alves', 'M', '2013-05-05'],
            ['Gabriela Nunes Pereira', 'F', '2015-08-22'],
            ['Heitor Ramos Barbosa', 'M', '2014-02-14'],
        ];

        $turma1 = Turma::query()->where('nome', '1º Ano A')->first();
        $turma2 = Turma::query()->where('nome', '5º Ano A')->first();
        $anoLetivo = \App\Models\AnoLetivo::query()->where('escola_id', $escola->id)->first();

        foreach ($nomes as $i => [$nome, $sexo, $nasc]) {
            $pessoa = Pessoa::create([
                'empresa_id' => $empresa->id,
                'nome' => $nome,
                'cpf' => fake()->unique()->numerify('###.###.###-##'),
                'data_nascimento' => $nasc,
                'sexo' => $sexo,
                'email' => fake()->unique()->safeEmail(),
                'telefone' => fake()->numerify('119########'),
            ]);

            $aluno = Aluno::create([
                'empresa_id' => $empresa->id,
                'pessoa_id' => $pessoa->id,
                'escola_id' => $escola->id,
                'registro_academico' => 'RA-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'data_ingresso' => '2025-02-01',
                'turno_preferencial' => $i < 4 ? 'matutino' : 'vespertino',
                'ativo' => 'S',
            ]);
            $alunos[] = $aluno;

            $turma = $i < 4 ? $turma1 : $turma2;
            Matricula::create([
                'empresa_id' => $empresa->id,
                'aluno_id' => $aluno->id,
                'turma_id' => $turma->id,
                'ano_letivo_id' => $anoLetivo->id,
                'status' => 'ativa',
                'numero_chamada' => $i + 1,
                'data_matricula' => '2025-02-01',
            ]);
        }

        // Responsaveis: um principal por aluno + um financeiro compartilhado
        $responsaveis = [];
        foreach ($alunos as $i => $aluno) {
            $pessoa = Pessoa::create([
                'empresa_id' => $empresa->id,
                'nome' => 'Responsável de '.$aluno->pessoa->nome,
                'cpf' => fake()->unique()->numerify('###.###.###-##'),
                'data_nascimento' => fake()->dateTimeBetween('-45 years', '-25 years')->format('Y-m-d'),
                'sexo' => fake()->randomElement(['M', 'F']),
                'email' => fake()->unique()->safeEmail(),
                'celular' => fake()->numerify('119########'),
            ]);

            $responsavel = Responsavel::create([
                'empresa_id' => $empresa->id,
                'pessoa_id' => $pessoa->id,
                'profissao' => fake()->randomElement(['Comerciante', 'Professor', 'Enfermeiro', 'Advogado', 'Autônomo']),
                'local_trabalho' => fake()->company(),
                'ativo' => 'S',
            ]);
            $responsaveis[] = $responsavel;

            $aluno->responsaveis()->attach($responsavel->id, [
                'tipo' => 'legal',
                'principal' => true,
                'pode_retirar' => true,
                'autoriza_contato' => true,
            ]);
        }

        // Responsavel financeiro compartilhado (vinculado a todos)
        $pessoaFin = Pessoa::create([
            'empresa_id' => $empresa->id,
            'nome' => 'Financeiro Demo',
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => '1985-04-10',
            'sexo' => 'F',
            'email' => 'financeiro@escolademo.test',
        ]);
        $respFin = Responsavel::create([
            'empresa_id' => $empresa->id,
            'pessoa_id' => $pessoaFin->id,
            'profissao' => 'Contadora',
            'ativo' => 'S',
        ]);
        foreach ($alunos as $aluno) {
            $aluno->responsaveis()->attach($respFin->id, [
                'tipo' => 'financeiro',
                'principal' => false,
                'pode_retirar' => false,
                'autoriza_contato' => true,
            ]);
        }

        // Contas de portal: responsavel ve os 2 primeiros filhos;
        // aluno demo ve a si mesmo
        $responsaveis[0]->update(['user_id' => $usuarios['responsavel']->id]);
        $responsaveis[1]->update(['user_id' => $usuarios['responsavel']->id]);

        $alunos[2]->update(['user_id' => $usuarios['aluno']->id]);

        return [$alunos, $responsaveis];
    }

    /** Diarios, frequencias, avaliacoes e notas. */
    private function operacao(Empresa $empresa, array $usuarios, array $alunos): void
    {
        $turmas = Turma::query()->with('disciplinas')->get();
        $matriculasPorTurma = Matricula::query()
            ->whereIn('turma_id', $turmas->pluck('id'))
            ->where('status', 'ativa')
            ->get()
            ->groupBy('turma_id');

        $professorPorTurma = [];
        foreach (Turma::query()->with('atribuicoes')->get() as $turma) {
            $professorPorTurma[$turma->id] = $turma->atribuicoes->first()?->user_id
                ?? $usuarios['prof1']->id;
        }

        // Diarios: 3 aulas por disciplina nas ultimas semanas
        foreach ($turmas as $turma) {
            $matriculas = $matriculasPorTurma->get($turma->id, collect());
            if ($matriculas->isEmpty()) {
                continue;
            }

            foreach ($turma->disciplinas as $disciplina) {
                $atribuicao = Atribuicao::query()
                    ->where('turma_id', $turma->id)
                    ->where('disciplina_id', $disciplina->id)
                    ->first();

                foreach ([3, 2, 1] as $diasAtras) {
                    $data = now()->subDays($diasAtras)->toDateString();

                    $diario = Diario::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'turma_id' => $turma->id,
                            'disciplina_id' => $disciplina->id,
                            'data' => $data,
                        ],
                        [
                            'user_id' => $atribuicao?->user_id ?? $professorPorTurma[$turma->id],
                            'conteudo' => fake()->randomElement([
                                'Revisão de conteúdo', 'Exercícios práticos', 'Leitura e interpretação',
                                'Apresentação de projeto', 'Dinâmica em grupo', 'Correção de atividade',
                            ]),
                        ]
                    );

                    foreach ($matriculas as $matricula) {
                        $status = fake()->randomElement(
                            ['presente', 'presente', 'presente', 'presente', 'falta', 'justificada']
                        );
                        Frequencia::firstOrCreate(
                            [
                                'empresa_id' => $empresa->id,
                                'diario_id' => $diario->id,
                                'matricula_id' => $matricula->id,
                            ],
                            [
                                'status' => $status,
                                'justificativa' => $status === 'justificada' ? 'Atestado médico' : null,
                            ]
                        );
                    }
                }

                // Avaliacoes: prova + trabalho por disciplina
                foreach ([
                    ['prova', 'Avaliação 1', 10],
                    ['trabalho', 'Trabalho Prático', 10],
                ] as [$tipo, $nome, $max]) {
                    $avaliacao = Avaliacao::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'turma_id' => $turma->id,
                            'disciplina_id' => $disciplina->id,
                            'nome' => $nome,
                        ],
                        [
                            'user_id' => $atribuicao?->user_id ?? $professorPorTurma[$turma->id],
                            'tipo' => $tipo,
                            'periodo' => '1º Bimestre',
                            'nota_maxima' => $max,
                        ]
                    );

                    foreach ($matriculas as $matricula) {
                        Nota::firstOrCreate(
                            [
                                'empresa_id' => $empresa->id,
                                'avaliacao_id' => $avaliacao->id,
                                'matricula_id' => $matricula->id,
                            ],
                            ['nota' => fake()->randomFloat(1, 4, 10)]
                        );
                    }
                }
            }
        }
    }

    /**
     * Avisos de comunicacao: gerais da escola + um por turma.
     * Idempotente: firstOrCreate por (empresa, escola, titulo).
     */
    private function comunicacao(Empresa $empresa): void
    {
        $escola = Escola::query()->where('empresa_id', $empresa->id)->first();
        if (! $escola) {
            return;
        }

        $turma = Turma::query()->where('escola_id', $escola->id)->first();

        $avisos = [
            [
                'escola_id' => $escola->id, 'turma_id' => null,
                'titulo' => 'Reunião de pais e mestres',
                'mensagem' => 'A reunião de pais e mestres acontecerá no dia '.now()->addDays(10)->format('d/m/Y').' às 19h, no auditório da escola. A presença de todos os responsáveis é muito importante.',
                'tipo' => 'evento', 'data_publicacao' => now()->subDays(2)->toDateString(),
            ],
            [
                'escola_id' => $escola->id, 'turma_id' => null,
                'titulo' => 'Calendário de provas do bimestre',
                'mensagem' => 'O calendário de avaliações foi publicado no portal. Fiquem atentos aos horários de cada disciplina e tragam os materiais solicitados.',
                'tipo' => 'info', 'data_publicacao' => now()->subDay()->toDateString(),
            ],
            [
                'escola_id' => $escola->id, 'turma_id' => null,
                'titulo' => 'Atualize os dados cadastrais',
                'mensagem' => 'Responsáveis, verifiquem se seus dados de contato e dos alunos estão atualizados na secretaria. Contato desatualizado dificulta a comunicação em emergências.',
                'tipo' => 'urgente', 'data_publicacao' => now()->subDays(4)->toDateString(),
            ],
        ];

        if ($turma) {
            $avisos[] = [
                'escola_id' => $escola->id, 'turma_id' => $turma->id,
                'titulo' => 'Passeio educativo da turma',
                'mensagem' => "A turma {$turma->nome} participará de um passeio educativo. O termo de autorização deve ser entregue na secretaria até ".now()->addDays(5)->format('d/m/Y').'.',
                'tipo' => 'evento', 'data_publicacao' => now()->subDay()->toDateString(),
            ];
        }

        foreach ($avisos as $dados) {
            Aviso::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'escola_id' => $dados['escola_id'],
                    'titulo' => $dados['titulo'],
                ],
                $dados + ['ativo' => 'S']
            );
        }
    }

    /**
     * Planos de mensalidade + cobrancas dos ultimos meses.
     * Idempotente: firstOrCreate por (matricula, competencia).
     */
    /** Acervo e circulacao de emprestimos da biblioteca. */
    private function biblioteca(Empresa $empresa): void
    {
        $escola = Escola::query()->where('empresa_id', $empresa->id)->first();
        if (! $escola) {
            return;
        }

        $catalogo = [
            ['titulo' => 'Dom Casmurro', 'autor' => 'Machado de Assis', 'categoria' => 'literatura', 'exemplares' => 3],
            ['titulo' => 'O Pequeno Príncipe', 'autor' => 'Antoine de Saint-Exupéry', 'categoria' => 'infantil', 'exemplares' => 5],
            ['titulo' => 'Memórias Póstumas de Brás Cubas', 'autor' => 'Machado de Assis', 'categoria' => 'literatura', 'exemplares' => 2],
            ['titulo' => 'A Revolução dos Bichos', 'autor' => 'George Orwell', 'categoria' => 'juvenil', 'exemplares' => 4],
            ['titulo' => 'O Alquimista', 'autor' => 'Paulo Coelho', 'categoria' => 'literatura', 'exemplares' => 3],
            ['titulo' => 'Cem Anos de Solidão', 'autor' => 'Gabriel García Márquez', 'categoria' => 'literatura', 'exemplares' => 2],
            ['titulo' => 'Matemática para o Ensino Fundamental', 'autor' => 'Equipe Declass', 'categoria' => 'didatico', 'exemplares' => 10],
            ['titulo' => 'Gramática Reflexiva', 'autor' => 'Celso Cunha & Lindley Cintra', 'categoria' => 'didatico', 'exemplares' => 8],
            ['titulo' => 'O Problema das Pessoas', 'autor' => 'Millor Fernandes', 'categoria' => 'literatura', 'exemplares' => 2],
            ['titulo' => 'História Geral', 'autor' => 'Boris Fausto', 'categoria' => 'referencia', 'exemplares' => 4],
            ['titulo' => 'Atlas Geográfico Escolar', 'autor' => 'IBGE', 'categoria' => 'referencia', 'exemplares' => 6],
            ['titulo' => 'Diário de um Banana', 'autor' => 'Jeff Kinney', 'categoria' => 'juvenil', 'exemplares' => 5],
        ];

        $livros = [];
        foreach ($catalogo as $dados) {
            $livros[] = \App\Models\Livro::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'escola_id' => $escola->id,
                    'titulo' => $dados['titulo'],
                ],
                $dados + ['ativo' => 'S']
            );
        }

        $alunos = Aluno::query()->where('empresa_id', $empresa->id)->orderBy('id')->limit(8)->get();
        if ($alunos->isEmpty() || $livros === []) {
            return;
        }

        // Circulacao demo: mix de devolvidos, no prazo e atrasados.
        // Bloco inteiro idempotente — so cria se nao houver emprestimos.
        if (\App\Models\EmprestimoLivro::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }

        foreach (range(0, 9) as $i) {
            $aluno = $alunos[$i % $alunos->count()];
            $livro = $livros[$i % count($livros)];

            $dados = [
                'empresa_id' => $empresa->id,
                'livro_id' => $livro->id,
                'aluno_id' => $aluno->id,
                'data_emprestimo' => now()->subDays(20)->toDateString(),
                'data_devolucao_prevista' => now()->subDays(5)->toDateString(),
                'status' => 'ativo',
            ];

            if ($i % 3 === 0) {
                // Devolvido no prazo
                $dados['data_devolucao'] = now()->subDays(6)->toDateString();
                $dados['status'] = 'devolvido';
            } elseif ($i % 3 === 1) {
                // Atrasado: previsto ha 5 dias, sem devolucao
                $dados['observacoes'] = 'Aluno avisado para regularizar.';
            } else {
                // No prazo
                $dados['data_emprestimo'] = now()->subDays(2)->toDateString();
                $dados['data_devolucao_prevista'] = now()->addDays(13)->toDateString();
            }

            \App\Models\EmprestimoLivro::create($dados);
        }
    }

    private function financeiro(Empresa $empresa): void
    {
        $integral = \App\Models\PlanoMensalidade::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nome' => 'Mensalidade Integral'],
            ['valor' => 850.00, 'descricao' => 'Turno integral, materiais inclusos', 'ativo' => 'S']
        );
        $parcial = \App\Models\PlanoMensalidade::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nome' => 'Mensalidade Parcial'],
            ['valor' => 550.00, 'descricao' => 'Meio periodo', 'ativo' => 'S']
        );

        // Atribui plano as matriculas ativas que ainda nao tem
        $matriculas = Matricula::query()
            ->where('empresa_id', $empresa->id)
            ->where('status', 'ativa')
            ->get();

        foreach ($matriculas as $i => $matricula) {
            if (! $matricula->plano_mensalidade_id) {
                $matricula->update(['plano_mensalidade_id' => ($i % 2 === 0 ? $integral : $parcial)->id]);
                $matricula->load('planoMensalidade');
            }
        }

        // Competencias: mes anterior (metade paga, metade vencida)
        // e mes atual (todas pendentes)
        $competencias = [
            now()->subMonth()->format('Y-m') => now()->subMonth()->startOfMonth()->addDays(9)->toDateString(),
            now()->format('Y-m') => now()->addDays(5)->toDateString(),
        ];

        foreach ($competencias as $competencia => $vencimento) {
            $ehMesAnterior = $competencia === now()->subMonth()->format('Y-m');

            foreach ($matriculas as $i => $matricula) {
                $plano = $matricula->planoMensalidade;
                if (! $plano) {
                    continue;
                }

                $paga = $ehMesAnterior && $i % 2 === 0;

                \App\Models\Mensalidade::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'matricula_id' => $matricula->id,
                        'competencia' => $competencia,
                    ],
                    [
                        'plano_mensalidade_id' => $plano->id,
                        'valor' => $plano->valor,
                        'vencimento' => $vencimento,
                        'status' => $paga ? 'paga' : 'pendente',
                        'data_pagamento' => $paga ? \Illuminate\Support\Carbon::parse($vencimento)->addDays(2)->toDateString() : null,
                        'forma_pagamento' => $paga ? fake()->randomElement(['pix', 'boleto', 'cartao_credito']) : null,
                    ]
                );
            }
        }
    }
}
