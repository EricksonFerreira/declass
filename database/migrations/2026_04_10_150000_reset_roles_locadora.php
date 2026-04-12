<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recria as regras de acesso para os perfis reais de uma locadora de veículos.
 *
 * Perfis definidos:
 *  1. Atendente        — balcão, check-in/out, reservas, pagamentos
 *  2. Gerente          — tudo do Atendente + frota + financeiro + usuários
 *  3. Supervisor de Frota — veículos, danos, OS, checklists, movimentações
 *  4. Financeiro       — relatórios, NFS-e, transações, visão de reservas
 *  5. Cliente          — apenas suas próprias reservas
 */
return new class extends Migration
{
    // ── Permissões por grupo ──────────────────────────────────────────────

    /** Permissões básicas que todo usuário autenticado precisa */
    private const COMUNS = [
        'home',
        'home.chart-data',
        'adminlte.darkmode.toggle',
        'atualizaStatusReservas',
        'profile.edit',
        'profile.password.edit',
        'profile.password.update',
        'profile.force-password.edit',
        'profile.force-password.update',
        'dashboard',
    ];

    /** Acesso ao site público de reservas (para clientes e atendentes) */
    private const SITE = [
        'site.apresentacao',
        'site.apresentacao.estacoes',
        'site.reserva.index',
        'site.reserva.create',
        'site.reserva.store',
        'site.reserva.disponibilidade',
        'site.reserva.estacoesDisponiveis',
        'site.reserva.pagamento',
        'site.reserva.pagamento.metodo',
        'site.reserva.processarPagamento',
        'site.reserva.success',
        'site.reserva.login',
        'site.reserva.verificarCupom',
        'site.admin.entry',
        'cancel',
        'success',
        'checkout',
    ];

    /** Operações de reserva (atendimento) */
    private const RESERVAS_OPERACAO = [
        'reserva.index',
        'reserva.show',
        'reserva.create',
        'reserva.store',
        'reserva.edit',
        'reserva.update',
        'reserva.destroy',
        'reserva.filtro',
        'reserva.setPerPage',
        'reserva.removerFiltro',
        'reserva.horarios_diarios',
        'reserva.salvar_horarios_diarios',
        'reserva.cancelar',
        'reserva.historico',
        'reserva.obterOpcionais',
        'reserva.estacoesDisponiveisNoPeriodo',
        'reserva.estacoesDisponiveisNaDataHora',
        'reserva.datasRetiradaDisponiveisPorEstacao',
        'reserva.agendaDisponivelPorEstacao',
        'reserva.validarFuncionamentoEstacao',
        'reserva.veiculosDisponiveis',
        'reserva.dadosEdicaoNoShow',
        'reserva.atualizarNoShow',
        'reserva.gerenciar_opcionais',
        'reserva.editar_no_show',
        // Check-in
        'reserva.checkin',
        'reserva.checkin.statusUpgrade',
        'reserva.checkin.veiculosDisponiveis',
        'reserva.checkin.veiculosModal',
        'reserva.checkin.excluirContrato',
        'reserva.criarContratoCheckin',
        'reserva.assinarContratoCheckin',
        'reserva.salvarAssinarContratoCheckIn',
        'reserva.salvar.assinatura',
        'reserva.verContratoCheckin',
        'reserva.adicionarDanoVeiculo',
        // Check-out
        'reserva.checkout.vistoria',
        'reserva.checkout.finalizar',
        // Pagamento
        'reserva.efetuarPagamento',
        'reserva.processarPagamento',
        'reserva.confirmarPagamentoDinheiro',
        'reserva.emitirNfse',
        // Caução
        'reserva.calcao',
        'reserva.calcao.autorizar',
        'reserva.calcao.capturar',
        'reserva.calcao.liberar',
        'reserva.calcao.presencial',
        'reserva.calcao.presencial.devolver',
        // Avarias pós-checkout
        'reserva.avarias.cobranca',
        'reserva.avarias.cobrar',
        'avaria.cobrar',
        // Caução standalone
        'calcao.autorizar',
        'calcao.capturar',
        'calcao.cancelar',
        'calcao.visualizar',
        // Stripe/pagamento
        'stripe.payment',
        'stripe.success',
        'stripe.failure',
        'stripe.processPayment',
        'cashier.payment',
        'cashier.webhook',
        'pagamento.teste',
        'subscription-checkout',
    ];

    /** Visão de veículos (leitura) */
    private const VEICULOS_LEITURA = [
        'veiculo.index',
        'veiculo.show',
        'veiculo.filtro',
        'veiculo.setPerPage',
        'veiculo.removerFiltro',
        'veiculo.buscar',
        'veiculo.disponibilidade',
        'veiculo.gruposPorTipo',
        'veiculo.modelosPorMarca',
        'grupo_frota.index',
        'grupo_frota.show',
        'grupo_frota.getGrupoFrotaDisponiveis',
        'grupo_frota.getModelosPorGrupoFrota',
        'tipo_frota.index',
        'tipo_frota.show',
        'estacao.index',
        'estacao.show',
        'estacao.getEstacoesDisponiveis',
        'estacao.horarios_diarios',
    ];

    /** Gestão completa de frota */
    private const FROTA_GESTAO = [
        'veiculo.create',
        'veiculo.store',
        'veiculo.edit',
        'veiculo.update',
        'veiculo.destroy',
        'veiculo.horarios_diarios',
        'veiculo.salvar_horarios_diarios',
        'veiculo.setOrdenacao',
        'grupo_frota.create',
        'grupo_frota.store',
        'grupo_frota.edit',
        'grupo_frota.update',
        'grupo_frota.destroy',
        'grupo_frota.filtro',
        'grupo_frota.setPerPage',
        'grupo_frota.removerFiltro',
        'tipo_frota.create',
        'tipo_frota.store',
        'tipo_frota.edit',
        'tipo_frota.update',
        'tipo_frota.destroy',
        'tipo_frota.filtro',
        'tipo_frota.setPerPage',
        'tipo_frota.removerFiltro',
        'marca.index',
        'marca.show',
        'marca.create',
        'marca.store',
        'marca.edit',
        'marca.update',
        'marca.destroy',
        'marca.filtro',
        'marca.setPerPage',
        'marca.removerFiltro',
        'modelo.index',
        'modelo.show',
        'modelo.create',
        'modelo.store',
        'modelo.edit',
        'modelo.update',
        'modelo.destroy',
        'modelo.filtro',
        'modelo.setPerPage',
        'modelo.removerFiltro',
        'modelo.getMarcaESegmento',
        'segmento.index',
        'segmento.show',
        'segmento.create',
        'segmento.store',
        'segmento.edit',
        'segmento.update',
        'segmento.destroy',
        'segmento.filtro',
        'segmento.setPerPage',
        'segmento.removerFiltro',
        'opcional.index',
        'opcional.show',
        'opcional.create',
        'opcional.store',
        'opcional.edit',
        'opcional.update',
        'opcional.destroy',
        'opcional.filtro',
        'opcional.setPerPage',
        'opcional.removerFiltro',
        'tipo_opcional.index',
        'tipo_opcional.show',
        'tipo_opcional.create',
        'tipo_opcional.store',
        'tipo_opcional.edit',
        'tipo_opcional.update',
        'tipo_opcional.destroy',
        'tipo_opcional.filtro',
        'tipo_opcional.setPerPage',
        'tipo_opcional.removerFiltro',
        'opcional_grupo_veiculo.index',
        'opcional_grupo_veiculo.show',
        'opcional_grupo_veiculo.create',
        'opcional_grupo_veiculo.store',
        'opcional_grupo_veiculo.edit',
        'opcional_grupo_veiculo.update',
        'opcional_grupo_veiculo.destroy',
        'opcional_grupo_veiculo.filtro',
        'opcional_grupo_veiculo.setPerPage',
        'opcional_grupo_veiculo.removerFiltro',
        'caracteristica_frota.index',
        'caracteristica_frota.show',
        'caracteristica_frota.create',
        'caracteristica_frota.store',
        'caracteristica_frota.edit',
        'caracteristica_frota.update',
        'caracteristica_frota.destroy',
        'caracteristica_frota.filtro',
        'caracteristica_frota.setPerPage',
        'caracteristica_frota.removerFiltro',
        'tipo_valor_caracteristica_frota.index',
        'tipo_valor_caracteristica_frota.show',
        'tipo_valor_caracteristica_frota.create',
        'tipo_valor_caracteristica_frota.store',
        'tipo_valor_caracteristica_frota.edit',
        'tipo_valor_caracteristica_frota.update',
        'tipo_valor_caracteristica_frota.destroy',
        'tipo_valor_caracteristica_frota.filtro',
        'tipo_valor_caracteristica_frota.setPerPage',
        'tipo_valor_caracteristica_frota.removerFiltro',
    ];

    /** Danos, OS e checklist */
    private const DANOS_OS = [
        'dano.index',
        'dano.show',
        'dano.create',
        'dano.store',
        'dano.edit',
        'dano.update',
        'dano.destroy',
        'dano.filtro',
        'dano.setPerPage',
        'dano.removerFiltro',
        'dano.obterDanosDetectados',
        'dano.obterDanosExistentes',
        'dano.obterDanosPorCategoriaELocalizacao',
        'categoria_dano.index',
        'categoria_dano.show',
        'categoria_dano.create',
        'categoria_dano.store',
        'categoria_dano.edit',
        'categoria_dano.update',
        'categoria_dano.destroy',
        'categoria_dano.filtro',
        'categoria_dano.setPerPage',
        'categoria_dano.removerFiltro',
        'veiculo-dano.monitoramento',
        'veiculo-dano.correcao',
        'ordem-servico.index',
        'ordem-servico.show',
        'ordem-servico.create',
        'ordem-servico.store',
        'ordem-servico.status',
        'ordem-servico.concluir',
        'ordem-servico.danos-pendentes',
        'ordem-servico.pdf',
        'checklist.index',
        'checklist.show',
        'checklist.create',
        'checklist.store',
        'checklist.edit',
        'checklist.update',
        'checklist.destroy',
        'checklist.filtro',
        'checklist.setPerPage',
        'checklist.removerFiltro',
        'configuracao-checklist.index',
        'configuracao-checklist.show',
        'configuracao-checklist.create',
        'configuracao-checklist.store',
        'configuracao-checklist.edit',
        'configuracao-checklist.update',
        'configuracao-checklist.destroy',
        'item_checklist.index',
        'item_checklist.show',
        'item_checklist.create',
        'item_checklist.store',
        'item_checklist.edit',
        'item_checklist.update',
        'item_checklist.destroy',
        'item_checklist.filtro',
        'item_checklist.setPerPage',
        'item_checklist.removerFiltro',
        'tipo_checklist.index',
        'tipo_checklist.show',
        'tipo_checklist.create',
        'tipo_checklist.store',
        'tipo_checklist.edit',
        'tipo_checklist.update',
        'tipo_checklist.destroy',
        'tipo_checklist.filtro',
        'tipo_checklist.setPerPage',
        'tipo_checklist.removerFiltro',
        'status_item_checklist.index',
        'status_item_checklist.show',
        'status_item_checklist.create',
        'status_item_checklist.store',
        'status_item_checklist.edit',
        'status_item_checklist.update',
        'status_item_checklist.destroy',
        'status_item_checklist.filtro',
        'status_item_checklist.setPerPage',
        'status_item_checklist.removerFiltro',
    ];

    /** Movimentações de veículos entre estações */
    private const MOVIMENTACOES = [
        'movimentacao.index',
        'movimentacao.show',
        'movimentacao.create',
        'movimentacao.store',
        'movimentacao.edit',
        'movimentacao.update',
        'movimentacao.destroy',
        'movimentacao.filtro',
        'movimentacao.setPerPage',
        'movimentacao.removerFiltro',
        'movimentacao.concluir',
        'movimentacao.buscarReservas',
        'movimentacao.buscarOrdens',
        'movimentacao.kmVeiculo',
        'tipo_movimentacao.index',
        'tipo_movimentacao.show',
        'tipo_movimentacao.create',
        'tipo_movimentacao.store',
        'tipo_movimentacao.edit',
        'tipo_movimentacao.update',
        'tipo_movimentacao.destroy',
        'tipo_movimentacao.filtro',
        'tipo_movimentacao.setPerPage',
        'tipo_movimentacao.removerFiltro',
        'status_movimentacao.index',
        'status_movimentacao.show',
        'status_movimentacao.create',
        'status_movimentacao.store',
        'status_movimentacao.edit',
        'status_movimentacao.update',
        'status_movimentacao.destroy',
        'status_movimentacao.filtro',
        'status_movimentacao.setPerPage',
        'status_movimentacao.removerFiltro',
        'tempo_entre_entacao.index',
        'tempo_entre_entacao.show',
        'tempo_entre_entacao.filtro',
        'tempo_entre_entacao.setPerPage',
        'tempo_entre_entacao.removerFiltro',
    ];

    /** Multas e sinistros */
    private const MULTAS_SINISTROS = [
        'multa.index',
        'multa.show',
        'multa.create',
        'multa.store',
        'multa.edit',
        'multa.update',
        'multa.destroy',
        'multa.delete',
        'multa.export',
        'multa.confirmarPagamento',
        'multa.buscarReservaPorVeiculoData',
        'sinistro.index',
        'sinistro.show',
        'sinistro.create',
        'sinistro.store',
        'sinistro.edit',
        'sinistro.update',
        'sinistro.destroy',
        'sinistro.export',
        'sinistro.pagarEmpresa',
        'sinistro.pagarCliente',
        'sinistro.buscarReservaPorVeiculoData',
    ];

    /** Financeiro */
    private const FINANCEIRO = [
        'financeiro.index',
        'financeiro.reservas',
        'nfse.index',
        'nfse.download',
        'nfse.importarPendentes',
        'tipotransacao.index',
        'tipotransacao.show',
        'tipotransacao.create',
        'tipotransacao.store',
        'tipotransacao.edit',
        'tipotransacao.update',
        'tipotransacao.destroy',
        'tipotransacao.setPerPage',
        'cupom_desconto.index',
        'cupom_desconto.show',
        'cupom_desconto.create',
        'cupom_desconto.store',
        'cupom_desconto.edit',
        'cupom_desconto.update',
        'cupom_desconto.destroy',
        'cupom_desconto.filtro',
        'cupom_desconto.setPerPage',
        'cupom_desconto.removerFiltro',
        'cupom_desconto.verificarCupom',
        'powerbi.report',
    ];

    /** Configurações operacionais (estações, feriados, dias úteis, horários) */
    private const CONFIG_OPERACAO = [
        'estacao.create',
        'estacao.store',
        'estacao.edit',
        'estacao.update',
        'estacao.destroy',
        'estacao.filtro',
        'estacao.setPerPage',
        'estacao.removerFiltro',
        'estacao.salvar_horarios_diarios',
        'feriado.index',
        'feriado.show',
        'feriado.create',
        'feriado.store',
        'feriado.edit',
        'feriado.update',
        'feriado.destroy',
        'feriado.filtro',
        'feriado.setPerPage',
        'feriado.removerFiltro',
        'dia_util.index',
        'dia_util.show',
        'dia_util.create',
        'dia_util.store',
        'dia_util.edit',
        'dia_util.update',
        'dia_util.destroy',
        'dia_util.filtro',
        'dia_util.setPerPage',
        'dia_util.removerFiltro',
        'horario_diario.index',
        'horario_diario.show',
        'horario_diario.create',
        'horario_diario.store',
        'horario_diario.edit',
        'horario_diario.update',
        'horario_diario.destroy',
        'horario_diario.filtro',
        'horario_diario.setPerPage',
        'horario_diario.removerFiltro',
        'pais.index',
        'pais.show',
        'pais.create',
        'pais.store',
        'pais.edit',
        'pais.update',
        'pais.destroy',
        'pais.filtro',
        'pais.setPerPage',
        'pais.removerFiltro',
        'status_reserva.index',
        'status_reserva.show',
        'status_reserva.create',
        'status_reserva.store',
        'status_reserva.edit',
        'status_reserva.update',
        'status_reserva.destroy',
        'status_reserva.filtro',
        'status_reserva.setPerPage',
        'status_reserva.removerFiltro',
        'status_veiculo.index',
        'status_veiculo.show',
        'status_veiculo.create',
        'status_veiculo.store',
        'status_veiculo.edit',
        'status_veiculo.update',
        'status_veiculo.destroy',
        'status_veiculo.filtro',
        'status_veiculo.setPerPage',
        'status_veiculo.removerFiltro',
        'tempo_entre_entacao.create',
        'tempo_entre_entacao.store',
        'tempo_entre_entacao.edit',
        'tempo_entre_entacao.update',
        'tempo_entre_entacao.destroy',
        'tempo_entre_entacao.horarios_diarios',
        'tempo_entre_entacao.salvar_horarios_diarios',
        'termo_consentimento.index',
        'termo_consentimento.show',
        'termo_consentimento.create',
        'termo_consentimento.store',
        'termo_consentimento.edit',
        'termo_consentimento.update',
        'termo_consentimento.destroy',
        'termo_consentimento.filtro',
        'termo_consentimento.setPerPage',
        'termo_consentimento.removerFiltro',
    ];

    /** Gestão de usuários */
    private const USUARIOS = [
        'usuario.index',
        'usuario.show',
        'usuario.create',
        'usuario.store',
        'usuario.edit',
        'usuario.update',
        'usuario.destroy',
        'usuario.filtro',
        'usuario.setPerPage',
        'usuario.removerFiltro',
        'usuario.buscar',
        'usuario.selectOptions',
        'usuario.alterarSenha',
        'usuario.salvarNovaSenha',
        'usuario.metodoPagamento',
        'usuario.adicionarMetodoPagamento',
        'usuario.excluirMetodoPagamento',
    ];

    /** Permissões para o cliente (apenas suas reservas) */
    private const CLIENTE = [
        'reserva.minhas',
        'reserva.show',
        'reserva.efetuarPagamento',
        'reserva.processarPagamento',
        'nfse.download',
        'site.apresentacao',
        'site.apresentacao.estacoes',
        'site.reserva.index',
        'site.reserva.create',
        'site.reserva.store',
        'site.reserva.disponibilidade',
        'site.reserva.estacoesDisponiveis',
        'site.reserva.pagamento',
        'site.reserva.pagamento.metodo',
        'site.reserva.processarPagamento',
        'site.reserva.success',
        'site.reserva.login',
        'site.reserva.verificarCupom',
        'cancel',
        'success',
        'stripe.payment',
        'stripe.success',
        'stripe.failure',
        'stripe.processPayment',
        'cashier.payment',
        'cashier.webhook',
        'subscription-checkout',
    ];

    // ── Definição dos perfis ──────────────────────────────────────────────

    private function perfis(): array
    {
        $atendente = array_merge(
            self::COMUNS,
            self::SITE,
            self::RESERVAS_OPERACAO,
            self::VEICULOS_LEITURA,
            self::MOVIMENTACOES,
            self::DANOS_OS,
            self::MULTAS_SINISTROS,
            ['usuario.show', 'usuario.selectOptions', 'usuario.buscar']
        );

        $supervisorFrota = array_merge(
            self::COMUNS,
            self::VEICULOS_LEITURA,
            self::FROTA_GESTAO,
            self::DANOS_OS,
            self::MOVIMENTACOES,
            self::MULTAS_SINISTROS,
            ['reserva.index', 'reserva.show', 'reserva.filtro', 'reserva.setPerPage', 'reserva.removerFiltro']
        );

        $financeiro = array_merge(
            self::COMUNS,
            self::FINANCEIRO,
            self::MULTAS_SINISTROS,
            [
                'reserva.index', 'reserva.show', 'reserva.filtro', 'reserva.setPerPage', 'reserva.removerFiltro',
                'reserva.efetuarPagamento', 'reserva.processarPagamento', 'reserva.confirmarPagamentoDinheiro',
                'reserva.emitirNfse', 'reserva.historico',
                'usuario.show', 'usuario.selectOptions',
            ]
        );

        $gerente = array_merge(
            $atendente,
            self::FROTA_GESTAO,
            self::DANOS_OS,
            self::FINANCEIRO,
            self::CONFIG_OPERACAO,
            self::USUARIOS,
            self::MULTAS_SINISTROS,
            [
                'regra.index', 'regra.show',
                'permissao.index', 'permissao.show',
            ]
        );

        $cliente = array_merge(
            self::COMUNS,
            self::CLIENTE
        );

        return [
            'Atendente'          => array_unique($atendente),
            'Gerente'            => array_unique($gerente),
            'Supervisor de Frota' => array_unique($supervisorFrota),
            'Financeiro'         => array_unique($financeiro),
            'Cliente'            => array_unique($cliente),
        ];
    }

    // ── Migration ─────────────────────────────────────────────────────────

    public function up(): void
    {
        // Remove todos os vínculos e regras existentes.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $perfis = $this->perfis();

        foreach ($perfis as $nome => $permNames) {
            // Cria a regra
            $roleId = DB::table('roles')->insertGetId([
                'name'       => $nome,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Resolve os IDs das permissões e vincula
            foreach (array_unique($permNames) as $permName) {
                $permId = DB::table('permissions')
                    ->where('name', $permName)
                    ->where('guard_name', 'web')
                    ->value('id');

                if ($permId) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permId,
                        'role_id'       => $roleId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
