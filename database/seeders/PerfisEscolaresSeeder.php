<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Perfis de acesso (roles) do sistema escolar.
 *
 * Os 19 perfis da especificacao + 'membro' (baseline self-service).
 * Perfis sao por tenant: cada empresa tem seus proprios roles
 * (roles.empresa_id). A matriz de cada perfil e feita de padroes:
 *
 *  - '*'               : todas as permissoes nao-internas
 *  - 'prefixo.*'       : permissoes cujo nome comeca com 'prefixo.'
 *  - 'prefixo'         : permissao exata
 *  - 'acao' generica   : nao usada — permissoes sao rotas nomeadas
 *
 * A matriz padrao so e aplicada na CRIACAO do perfil — re-syncs
 * nao sobrescrevem ajustes manuais feitos na tela de perfis.
 */
class PerfisEscolaresSeeder extends Seeder
{
    /** Baseline self-service: home, perfil e notificacoes sao sempre liberados. */
    private const BASE = ['home', 'dashboard'];

    /**
     * Matriz padrao por perfil (role.name => padroes de permissao).
     */
    public const PERFIS = [
        'administrador' => ['*'],

        'administrador_rede' => [
            ...self::BASE,
            'menu.*',
            'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.edit', 'usuario.update',
            'usuario.filtro', 'usuario.removerFiltro', 'usuario.setPerPage',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.*', 'documentos.*',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*', 'matricula.*', 'horario_matriz.*', 'horarios.*', 'calendario.*', 'feriado.*', 'justificativa.*', 'solicitacao_matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*', 'relatorio_aproveitamento.*', 'solicitacao.*', 'documento_solicitacao.*', 'tipo_solicitacao.*',
            'plano_mensalidade.*', 'mensalidade.*', 'despesa.*', 'conta_receber.*', 'fluxo_caixa.*', 'dre.*',
            'financeiro_visao_geral.index', 'aviso.*', 'livro.*', 'emprestimo.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*', 'saude.*', 'material.*',
            'aluno.*', 'responsavel.*',
        ],

        'diretor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.*',
            'escola.*',
            'documentos.*',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*', 'matricula.*', 'horario_matriz.*', 'horarios.*', 'calendario.*', 'feriado.*', 'justificativa.*', 'solicitacao_matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*', 'relatorio_aproveitamento.*', 'solicitacao.*', 'documento_solicitacao.*', 'tipo_solicitacao.*',
            'plano_mensalidade.*', 'mensalidade.*', 'despesa.*', 'conta_receber.*', 'fluxo_caixa.*', 'dre.*',
            'financeiro_visao_geral.index', 'aviso.*', 'livro.*', 'emprestimo.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*', 'saude.*', 'material.*',
            'aluno.*', 'responsavel.*',
        ],

        'vice_diretor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.*',
            'escola.*',
            'documentos.*',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*', 'matricula.*', 'horario_matriz.*', 'horarios.*', 'calendario.*', 'feriado.*', 'justificativa.*', 'solicitacao_matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*', 'relatorio_aproveitamento.*', 'solicitacao.*', 'documento_solicitacao.*', 'tipo_solicitacao.*',
            'plano_mensalidade.*', 'mensalidade.*', 'despesa.*', 'conta_receber.*', 'fluxo_caixa.*', 'dre.*',
            'financeiro_visao_geral.index', 'aviso.*', 'livro.*', 'emprestimo.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*', 'saude.*', 'material.*',
            'aluno.*', 'responsavel.*',
        ],

        'coordenador_pedagogico' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'documentos.index',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*',
            'horario_matriz.*', 'horarios.*', 'calendario.*', 'feriado.*', 'justificativa.*',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.index', 'avaliacao.agenda', 'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf', 'aluno.filtro',
            'aluno.documento.arquivo',
            'aluno.removerFiltro', 'aluno.setPerPage',
            'responsavel.index', 'responsavel.show', 'responsavel.filtro', 'responsavel.removerFiltro', 'responsavel.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'ocorrencia.index', 'ocorrencia.create', 'ocorrencia.store', 'ocorrencia.edit', 'ocorrencia.update',
            'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'plano_atendimento.index', 'plano_atendimento.filtro', 'plano_atendimento.removerFiltro', 'plano_atendimento.setPerPage',
            'tarefa.index', 'tarefa.create', 'tarefa.store', 'tarefa.edit', 'tarefa.update',
            'tarefa.entregas', 'tarefa.entregas.salvar',
            'tarefa.filtro', 'tarefa.removerFiltro', 'tarefa.setPerPage',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'livro.index', 'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'veiculo.index', 'veiculo.filtro', 'veiculo.removerFiltro', 'veiculo.setPerPage',
            'rota_transporte.index', 'rota_transporte.filtro', 'rota_transporte.removerFiltro', 'rota_transporte.setPerPage',
            'transporte_aluno.index', 'transporte_aluno.filtro', 'transporte_aluno.removerFiltro', 'transporte_aluno.setPerPage',
            'cardapio.index', 'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.create', 'evento.store', 'evento.edit', 'evento.update',
            'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.create', 'pesquisa.store', 'pesquisa.edit', 'pesquisa.update',
            'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
            'saude.index', 'saude.show', 'saude.edit', 'saude.update',
            'saude.filtro', 'saude.removerFiltro', 'saude.setPerPage',
            'material.index', 'material.movimentacoes',
            'material.filtro', 'material.removerFiltro', 'material.setPerPage',
            'relatorio_frequencia.index', 'relatorio_frequencia.filtro', 'relatorio_frequencia.removerFiltro', 'relatorio_frequencia.setPerPage',
            'relatorio_transporte.index', 'relatorio_aproveitamento.index',
        ],

        'secretario' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.*',
            'escola.index', 'escola.show', 'escola.create', 'escola.store', 'escola.edit', 'escola.update',
            'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'documentos.*',
            'ano_letivo.index', 'ano_letivo.show', 'ano_letivo.filtro', 'ano_letivo.removerFiltro', 'ano_letivo.setPerPage',
            'periodo_letivo.index', 'periodo_letivo.show', 'periodo_letivo.filtro', 'periodo_letivo.removerFiltro', 'periodo_letivo.setPerPage',
            'etapa.index', 'etapa.filtro', 'etapa.removerFiltro', 'etapa.setPerPage',
            'disciplina.index', 'disciplina.filtro', 'disciplina.removerFiltro', 'disciplina.setPerPage',
            'turma.index', 'turma.show', 'turma.disciplina.show', 'turma.grade', 'turma.grade.pdf', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage',
            'horarios.index', 'horarios.exportar', 'horarios.vincular', 'calendario.index', 'feriado.*', 'justificativa.*',
            'matricula.*', 'solicitacao_matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'aviso.*', 'livro.*', 'emprestimo.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*', 'relatorio_aproveitamento.*', 'solicitacao.*', 'documento_solicitacao.*', 'tipo_solicitacao.*',
            'solicitacao.*', 'documento_solicitacao.*', 'tipo_solicitacao.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*', 'saude.*', 'material.*',
            'plano_mensalidade.*', 'mensalidade.*',
            'despesa.*', 'conta_receber.*', 'fluxo_caixa.index', 'dre.index', 'financeiro_visao_geral.index',
            'aluno.*', 'responsavel.*',
        ],

        'auxiliar_secretaria' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.create', 'usuario.store',
            'usuario.filtro', 'usuario.removerFiltro', 'usuario.setPerPage',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'ano_letivo.show', 'periodo_letivo.index', 'periodo_letivo.show', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.disciplina.show', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage',
            'horarios.index', 'horarios.vincular',
            'calendario.index', 'turma.calendario',
            'matricula.index', 'matricula.create', 'matricula.store', 'matricula.edit', 'matricula.update',
            'matricula.rematricula', 'matricula.rematricular',
            'solicitacao_matricula.index', 'solicitacao_matricula.create', 'solicitacao_matricula.store', 'solicitacao_matricula.show', 'solicitacao_matricula.edit', 'solicitacao_matricula.update', 'solicitacao_matricula.destroy', 'solicitacao_matricula.aprovar', 'solicitacao_matricula.recusar', 'solicitacao_matricula.cancelar', 'solicitacao_matricula.filtro', 'solicitacao_matricula.removerFiltro', 'solicitacao_matricula.setPerPage',
            'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.create', 'diario.store', 'diario.edit', 'diario.update',
            'avaliacao.index', 'avaliacao.agenda', 'avaliacao.create', 'avaliacao.store', 'avaliacao.edit', 'avaliacao.update',
            'diario.frequencia', 'diario.frequencia.salvar',
            'diario.chamada', 'diario.chamada.salvar', 'diario.disciplinas',
            'justificativa.*',
            'avaliacao.notas', 'avaliacao.notas.salvar',
            'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf', 'aluno.create', 'aluno.store', 'aluno.edit', 'aluno.update',
            'aluno.filtro', 'aluno.removerFiltro', 'aluno.setPerPage',
            'aluno.responsavel.store', 'aluno.responsavel.update', 'aluno.responsavel.destroy',
            'aluno.documento.store', 'aluno.documento.update', 'aluno.documento.destroy', 'aluno.documento.arquivo',
            'solicitacao.index', 'solicitacao.responder', 'solicitacao.anexo',
            'solicitacao.filtro', 'solicitacao.removerFiltro', 'solicitacao.setPerPage',
            'tipo_solicitacao.index', 'tipo_solicitacao.create', 'tipo_solicitacao.store',
            'tipo_solicitacao.edit', 'tipo_solicitacao.update', 'tipo_solicitacao.destroy',
            'documento_solicitacao.index', 'documento_solicitacao.store', 'documento_solicitacao.update', 'documento_solicitacao.arquivo',
            'documento_solicitacao.filtro', 'documento_solicitacao.removerFiltro', 'documento_solicitacao.setPerPage',
            'responsavel.index', 'responsavel.show', 'responsavel.create', 'responsavel.store',
            'responsavel.filtro', 'responsavel.removerFiltro', 'responsavel.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'plano_mensalidade.*', 'mensalidade.*',
            'despesa.*', 'conta_receber.*', 'fluxo_caixa.index', 'dre.index', 'financeiro_visao_geral.index',
            'aviso.index', 'aviso.create', 'aviso.store', 'aviso.edit', 'aviso.update',
            'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'livro.index', 'livro.create', 'livro.store', 'livro.edit', 'livro.update',
            'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.create', 'emprestimo.store', 'emprestimo.devolver',
            'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'veiculo.index', 'veiculo.create', 'veiculo.store', 'veiculo.edit', 'veiculo.update',
            'veiculo.filtro', 'veiculo.removerFiltro', 'veiculo.setPerPage',
            'rota_transporte.index', 'rota_transporte.create', 'rota_transporte.store', 'rota_transporte.edit', 'rota_transporte.update',
            'rota_transporte.filtro', 'rota_transporte.removerFiltro', 'rota_transporte.setPerPage',
            'transporte_aluno.index', 'transporte_aluno.create', 'transporte_aluno.store', 'transporte_aluno.edit', 'transporte_aluno.update',
            'transporte_aluno.filtro', 'transporte_aluno.removerFiltro', 'transporte_aluno.setPerPage',
            'cardapio.index', 'cardapio.create', 'cardapio.store', 'cardapio.edit', 'cardapio.update',
            'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.create', 'evento.store', 'evento.edit', 'evento.update',
            'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.create', 'pesquisa.store', 'pesquisa.edit', 'pesquisa.update',
            'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
            'saude.index', 'saude.show', 'saude.edit', 'saude.update',
            'saude.filtro', 'saude.removerFiltro', 'saude.setPerPage',
            'material.index', 'material.create', 'material.store', 'material.edit', 'material.update',
            'material.movimentacoes', 'material.movimentar',
            'material.movimentacao.index', 'material.movimentacao.store',
            'material.filtro', 'material.removerFiltro', 'material.setPerPage',
            'ocorrencia.index', 'ocorrencia.create', 'ocorrencia.store', 'ocorrencia.edit', 'ocorrencia.update',
            'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'relatorio_frequencia.index', 'relatorio_frequencia.filtro', 'relatorio_frequencia.removerFiltro', 'relatorio_frequencia.setPerPage',
            'relatorio_inadimplencia.index', 'relatorio_inadimplencia.filtro', 'relatorio_inadimplencia.removerFiltro', 'relatorio_inadimplencia.setPerPage',
            'relatorio_transporte.index', 'relatorio_aproveitamento.index',
        ],

        'professor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'turma.index', 'turma.show', 'turma.disciplina.show', 'turma.grade', 'turma.grade.pdf', 'turma.chamada', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage', 'calendario.index', 'feriado.index', 'meus_horarios.index',
            'turma.material.store', 'turma.material.destroy', 'turma.disciplina.material.store', 'turma.disciplina.material.destroy',
            'turma.forum.store', 'turma.forum.comentar',
            'turma.forum.update', 'turma.forum.encerrar', 'turma.forum.destroy',
            'tarefa.index', 'tarefa.create', 'tarefa.store', 'tarefa.edit', 'tarefa.update',
            'tarefa.entregas', 'tarefa.entregas.salvar',
            'tarefa.filtro', 'tarefa.removerFiltro', 'tarefa.setPerPage',
            'diario.index', 'diario.create', 'diario.store', 'diario.edit', 'diario.update',
            'avaliacao.index', 'avaliacao.agenda', 'avaliacao.create', 'avaliacao.store', 'avaliacao.edit', 'avaliacao.update',
            'diario.frequencia', 'diario.frequencia.salvar',
            'diario.chamada', 'diario.chamada.salvar', 'diario.disciplinas',
            'avaliacao.notas', 'avaliacao.notas.salvar',
            'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'livro.index', 'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'portal.emprestimos',
            'cardapio.index', 'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
        ],

        'professor_substituto' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'turma.index', 'turma.show', 'turma.disciplina.show', 'turma.grade', 'turma.grade.pdf', 'turma.chamada', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage', 'calendario.index', 'feriado.index', 'meus_horarios.index',
            'turma.material.store', 'turma.material.destroy', 'turma.disciplina.material.store', 'turma.disciplina.material.destroy',
            'turma.forum.store', 'turma.forum.comentar',
            'turma.forum.update', 'turma.forum.encerrar', 'turma.forum.destroy',
            'tarefa.index', 'tarefa.create', 'tarefa.store', 'tarefa.edit', 'tarefa.update',
            'tarefa.entregas', 'tarefa.entregas.salvar',
            'tarefa.filtro', 'tarefa.removerFiltro', 'tarefa.setPerPage',
            'diario.index', 'diario.create', 'diario.store', 'diario.edit', 'diario.update',
            'avaliacao.index', 'avaliacao.agenda', 'avaliacao.create', 'avaliacao.store', 'avaliacao.edit', 'avaliacao.update',
            'diario.frequencia', 'diario.frequencia.salvar',
            'diario.chamada', 'diario.chamada.salvar', 'diario.disciplinas',
            'avaliacao.notas', 'avaliacao.notas.salvar',
            'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'livro.index', 'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'portal.emprestimos',
            'cardapio.index', 'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
        ],

        'funcionario_administrativo' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show',
            'escola.index', 'escola.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'ano_letivo.show', 'periodo_letivo.index', 'periodo_letivo.show', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.disciplina.show',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.index', 'avaliacao.agenda', 'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf',
            'aluno.documento.arquivo',
            'responsavel.index', 'responsavel.show',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
            'saude.index', 'saude.show', 'saude.filtro', 'saude.removerFiltro', 'saude.setPerPage',
        ],

        'financeiro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show',
            'escola.index', 'escola.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'ano_letivo.show', 'periodo_letivo.index', 'periodo_letivo.show', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.disciplina.show',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.index', 'avaliacao.agenda', 'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf',
            'responsavel.index', 'responsavel.show', 'responsavel.filtro', 'responsavel.removerFiltro', 'responsavel.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'plano_mensalidade.*', 'mensalidade.*', 'despesa.*', 'conta_receber.*', 'fluxo_caixa.*', 'dre.*',
            'financeiro_visao_geral.index',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'relatorio_inadimplencia.index', 'relatorio_inadimplencia.filtro', 'relatorio_inadimplencia.removerFiltro', 'relatorio_inadimplencia.setPerPage',
            'material.index', 'material.movimentacoes',
            'material.filtro', 'material.removerFiltro', 'material.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'bibliotecario' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf',
            'livro.*', 'emprestimo.*',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'responsavel_transporte' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*',
            'relatorio_transporte.index',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'responsavel_alimentacao' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf',
            'cardapio.*',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'atendimento_especializado' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'ano_letivo.show', 'periodo_letivo.index', 'periodo_letivo.show', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.disciplina.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.certificado', 'aluno.historico', 'aluno.historico.pdf', 'aluno.historico.escolar', 'aluno.historico.escolar.pdf', 'aluno.edit',
            'aluno.documento.arquivo',
            'aluno.update',
            'aluno.filtro', 'aluno.removerFiltro', 'aluno.setPerPage',
            'ocorrencia.index', 'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'plano_atendimento.index', 'plano_atendimento.create', 'plano_atendimento.store',
            'plano_atendimento.edit', 'plano_atendimento.update', 'plano_atendimento.destroy',
            'plano_atendimento.filtro', 'plano_atendimento.removerFiltro', 'plano_atendimento.setPerPage',
            'saude.index', 'saude.show', 'saude.edit', 'saude.update',
            'saude.filtro', 'saude.removerFiltro', 'saude.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'aluno' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'portal.*',
        ],

        'responsavel_legal' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'portal.*',
        ],

        'responsavel_financeiro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'portal.*',
        ],

        // Baseline interno: usuario generico com acesso apenas as
        // telas self-service. Usado como fallback de remapeamento.
        'membro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
        ],
    ];

    /**
     * Resolve os padroes de um perfil contra as permissoes existentes.
     *
     * @param  array<int, string>  $padroes
     * @param  \Illuminate\Support\Collection<int, string>  $nomesPermissoes
     * @return array<int, string>
     */
    public static function resolverPadroes(array $padroes, $nomesPermissoes): array
    {
        $resolvidas = [];

        foreach ($padroes as $padrao) {
            if ($padrao === '*') {
                // Tudo que nao e permissao interna de plataforma.
                foreach ($nomesPermissoes as $nome) {
                    if (! \App\Models\Permissao::isNomeInterno($nome)) {
                        $resolvidas[] = $nome;
                    }
                }

                continue;
            }

            if (str_ends_with($padrao, '.*')) {
                $prefixo = substr($padrao, 0, -1); // mantem o ponto: 'escola.'
                foreach ($nomesPermissoes as $nome) {
                    if (str_starts_with($nome, $prefixo)) {
                        $resolvidas[] = $nome;
                    }
                }

                continue;
            }

            if (in_array($padrao, $nomesPermissoes->all(), true)) {
                $resolvidas[] = $padrao;
            }
        }

        return array_values(array_unique($resolvidas));
    }

    /**
     * Garante que todos os perfis existam para uma empresa
     * (usado no cadastro de novo tenant).
     */
    public function garantirPerfisParaEmpresa(int $empresaId): void
    {
        foreach (array_keys(self::PERFIS) as $nome) {
            DB::table('roles')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web', 'empresa_id' => $empresaId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Sincroniza permissoes (rotas nomeadas) e cria os perfis com a
     * matriz padrao para todas as empresas existentes.
     */
    public function run(): void
    {
        app(\App\Services\PermissaoSyncService::class)->sync();
    }
}
