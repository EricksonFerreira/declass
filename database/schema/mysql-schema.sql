/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `arquivos_rastreados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arquivos_rastreados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `modulo` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidade` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entidade_id` bigint unsigned DEFAULT NULL,
  `campo` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contexto` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_bytes` bigint unsigned NOT NULL DEFAULT '0',
  `hash_sha1` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arquivos_rastreados_empresa_id_foreign` (`empresa_id`),
  KEY `arquivos_rastreados_user_id_foreign` (`user_id`),
  KEY `arquivos_rastreados_modulo_entidade_entidade_id_index` (`modulo`,`entidade`,`entidade_id`),
  KEY `arquivos_rastreados_modulo_index` (`modulo`),
  KEY `arquivos_rastreados_entidade_index` (`entidade`),
  KEY `arquivos_rastreados_entidade_id_index` (`entidade_id`),
  KEY `arquivos_rastreados_campo_index` (`campo`),
  KEY `arquivos_rastreados_contexto_index` (`contexto`),
  KEY `arquivos_rastreados_hash_sha1_index` (`hash_sha1`),
  CONSTRAINT `arquivos_rastreados_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `arquivos_rastreados_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `avaria_cobrancas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avaria_cobrancas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `reserva_codigo_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `calcao_id` bigint unsigned DEFAULT NULL,
  `metodo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `moeda` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_calcao` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_stripe` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_pendente` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dano_veiculo_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `gateway_payment_intent_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `usuario_responsavel_id` bigint unsigned DEFAULT NULL,
  `cobrado_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `avaria_cobrancas_empresa_id_index` (`empresa_id`),
  KEY `avaria_cobrancas_veiculo_id_index` (`veiculo_id`),
  KEY `avaria_cobrancas_cliente_id_index` (`cliente_id`),
  KEY `avaria_cobrancas_calcao_id_index` (`calcao_id`),
  KEY `avaria_cobrancas_usuario_responsavel_id_index` (`usuario_responsavel_id`),
  KEY `avaria_cobrancas_reserva_codigo_id_foreign` (`reserva_codigo_id`),
  CONSTRAINT `avaria_cobrancas_reserva_codigo_id_foreign` FOREIGN KEY (`reserva_codigo_id`) REFERENCES `reserva_codigos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `calcoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calcoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `contrato_checkin_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `payment_intent_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_customer_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_calculado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_autorizado` decimal(10,2) DEFAULT NULL,
  `valor_capturado` decimal(10,2) DEFAULT NULL,
  `tipo_regra_aplicada` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'fixo|percentual',
  `origem_regra_aplicada` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'empresa|grupo|modelo',
  `configuracao_calcao_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente' COMMENT 'pendente|autorizado|parcialmente_capturado|capturado|cancelado|expirado|falhou',
  `data_hora_autorizacao` datetime DEFAULT NULL,
  `data_hora_captura` datetime DEFAULT NULL,
  `data_hora_cancelamento` datetime DEFAULT NULL,
  `usuario_responsavel_id` bigint unsigned DEFAULT NULL,
  `observacao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `justificativa_captura` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stripe_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Resposta completa do Stripe para auditoria',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calcoes_empresa_id_index` (`empresa_id`),
  KEY `calcoes_reserva_id_index` (`reserva_id`),
  KEY `calcoes_contrato_checkin_id_index` (`contrato_checkin_id`),
  KEY `calcoes_cliente_id_index` (`cliente_id`),
  KEY `calcoes_payment_intent_id_index` (`payment_intent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caracteristica_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caracteristica_frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caracteristica_grupo_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caracteristica_grupo_frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caracteristica_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caracteristica_grupos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caracteristicas_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caracteristicas_frotas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipo_valor_caracteristica_frota_id` int NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `caracteristicas_frotas_empresa_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `caracteristicas_frotas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caracteristicas_grupo_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caracteristicas_grupo_frota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `combustivel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transmissao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assentos` int DEFAULT NULL,
  `portas` int DEFAULT NULL,
  `malas` int DEFAULT NULL,
  `ar_condicionado` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `potencia_hp` int DEFAULT NULL,
  `emissoes_co2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caracteristicas_tipo_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caracteristicas_tipo_frota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_frota_id` int NOT NULL,
  `caracteristica_frota_id` int NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categoria_danos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categoria_danos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_danos_empresa_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `categoria_danos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias_financeiras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_financeiras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `categoria_pai_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('receita','despesa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `permite_lancamento` tinyint(1) NOT NULL DEFAULT '1',
  `origem` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_fin_empresa_codigo_unique` (`empresa_id`,`codigo`),
  KEY `categorias_financeiras_categoria_pai_id_foreign` (`categoria_pai_id`),
  CONSTRAINT `categorias_financeiras_categoria_pai_id_foreign` FOREIGN KEY (`categoria_pai_id`) REFERENCES `categorias_financeiras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categorias_financeiras_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chamado_anexos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamado_anexos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chamado_id` bigint unsigned NOT NULL,
  `chamado_log_id` bigint unsigned DEFAULT NULL,
  `contexto` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chamado_anexos_chamado_id_foreign` (`chamado_id`),
  KEY `chamado_anexos_user_id_foreign` (`user_id`),
  KEY `chamado_anexos_chamado_log_id_foreign` (`chamado_log_id`),
  CONSTRAINT `chamado_anexos_chamado_id_foreign` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chamado_anexos_chamado_log_id_foreign` FOREIGN KEY (`chamado_log_id`) REFERENCES `chamado_logs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chamado_anexos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chamado_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamado_categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chamado_categorias_empresa_id_nome_index` (`empresa_id`,`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chamado_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamado_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chamado_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `acao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nivel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chamado_logs_chamado_id_foreign` (`chamado_id`),
  KEY `chamado_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `chamado_logs_chamado_id_foreign` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chamado_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chamado_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamado_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'secondary',
  `ordem` int unsigned NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `padrao_entrada` tinyint(1) NOT NULL DEFAULT '0',
  `padrao_atendimento` tinyint(1) NOT NULL DEFAULT '0',
  `padrao_fechamento` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chamado_statuses_empresa_id_slug_index` (`empresa_id`,`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chamado_subcategorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamado_subcategorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `chamado_categoria_id` bigint unsigned NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `prazo_horas` int unsigned NOT NULL DEFAULT '24',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chamado_subcategorias_chamado_categoria_id_foreign` (`chamado_categoria_id`),
  KEY `chamado_subcategorias_empresa_id_chamado_categoria_id_index` (`empresa_id`,`chamado_categoria_id`),
  CONSTRAINT `chamado_subcategorias_chamado_categoria_id_foreign` FOREIGN KEY (`chamado_categoria_id`) REFERENCES `chamado_categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `chamados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chamados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `criado_por_user_id` bigint unsigned DEFAULT NULL,
  `chamado_categoria_id` bigint unsigned NOT NULL,
  `chamado_subcategoria_id` bigint unsigned DEFAULT NULL,
  `chamado_status_id` bigint unsigned NOT NULL,
  `descricao` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `solucao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `prazo_atendimento_em` datetime DEFAULT NULL,
  `aberto_em` datetime DEFAULT NULL,
  `em_atendimento_em` datetime DEFAULT NULL,
  `concluido_em` datetime DEFAULT NULL,
  `cancelado_em` datetime DEFAULT NULL,
  `aguardando_solicitante_em` datetime DEFAULT NULL,
  `responsavel_user_id` bigint unsigned DEFAULT NULL,
  `cancelamento_justificativa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chamados_user_id_foreign` (`user_id`),
  KEY `chamados_criado_por_user_id_foreign` (`criado_por_user_id`),
  KEY `chamados_chamado_categoria_id_foreign` (`chamado_categoria_id`),
  KEY `chamados_chamado_subcategoria_id_foreign` (`chamado_subcategoria_id`),
  KEY `chamados_chamado_status_id_foreign` (`chamado_status_id`),
  KEY `chamados_responsavel_user_id_foreign` (`responsavel_user_id`),
  KEY `chamados_empresa_id_chamado_status_id_index` (`empresa_id`,`chamado_status_id`),
  CONSTRAINT `chamados_chamado_categoria_id_foreign` FOREIGN KEY (`chamado_categoria_id`) REFERENCES `chamado_categorias` (`id`),
  CONSTRAINT `chamados_chamado_status_id_foreign` FOREIGN KEY (`chamado_status_id`) REFERENCES `chamado_statuses` (`id`),
  CONSTRAINT `chamados_chamado_subcategoria_id_foreign` FOREIGN KEY (`chamado_subcategoria_id`) REFERENCES `chamado_subcategorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chamados_criado_por_user_id_foreign` FOREIGN KEY (`criado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chamados_responsavel_user_id_foreign` FOREIGN KEY (`responsavel_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chamados_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `veiculo_id` int NOT NULL,
  `reserva_id` int NOT NULL,
  `user_id` int NOT NULL,
  `tipo_checklist_id` int NOT NULL,
  `nivel_combustivel` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quilometragem` int DEFAULT NULL,
  `resumo_condicao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `veiculo_id` (`veiculo_id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `checklists_empresa_id_index` (`empresa_id`),
  CONSTRAINT `checklists_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`),
  CONSTRAINT `checklists_ibfk_2` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classificacoes_fiscais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classificacoes_fiscais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ncm` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_bem` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_calendarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_calendarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `clockify_projeto_id` bigint unsigned NOT NULL,
  `clockify_projeto_cronograma_id` bigint unsigned DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `horas_por_dia` decimal(4,2) DEFAULT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `trabalho_junto` tinyint(1) NOT NULL DEFAULT '0',
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_calendarios_empresa_id_index` (`empresa_id`),
  KEY `clockify_calendarios_user_id_index` (`user_id`),
  KEY `clockify_calendarios_clockify_projeto_id_index` (`clockify_projeto_id`),
  KEY `clockify_calendarios_clockify_projeto_cronograma_id_foreign` (`clockify_projeto_cronograma_id`),
  CONSTRAINT `clockify_calendarios_clockify_projeto_cronograma_id_foreign` FOREIGN KEY (`clockify_projeto_cronograma_id`) REFERENCES `clockify_projeto_cronogramas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clockify_calendarios_clockify_projeto_id_foreign` FOREIGN KEY (`clockify_projeto_id`) REFERENCES `clockify_projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clockify_calendarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_clientes_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_projeto_cronogramas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_projeto_cronogramas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clockify_projeto_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'valido',
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `horas_orcadas` decimal(8,2) DEFAULT NULL,
  `valor_hora` decimal(10,2) DEFAULT NULL,
  `valor_faturado` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_projeto_cronogramas_clockify_projeto_id_foreign` (`clockify_projeto_id`),
  CONSTRAINT `clockify_projeto_cronogramas_clockify_projeto_id_foreign` FOREIGN KEY (`clockify_projeto_id`) REFERENCES `clockify_projetos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_projeto_estimativas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_projeto_estimativas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `clockify_projeto_id` bigint unsigned NOT NULL,
  `funcao_id` bigint unsigned NOT NULL,
  `horas_estimadas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projeto_estimativas_projeto_funcao_unique` (`clockify_projeto_id`,`funcao_id`),
  KEY `projeto_estimativas_funcao_fk` (`funcao_id`),
  CONSTRAINT `projeto_estimativas_funcao_fk` FOREIGN KEY (`funcao_id`) REFERENCES `funcoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projeto_estimativas_projeto_fk` FOREIGN KEY (`clockify_projeto_id`) REFERENCES `clockify_projetos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_projeto_planejamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_projeto_planejamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clockify_projeto_cronograma_id` bigint unsigned NOT NULL,
  `data` date NOT NULL,
  `horas` decimal(5,2) NOT NULL DEFAULT '0.00',
  `eh_extra` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cronograma_data` (`clockify_projeto_cronograma_id`,`data`),
  CONSTRAINT `fk_planejamentos_cronograma` FOREIGN KEY (`clockify_projeto_cronograma_id`) REFERENCES `clockify_projeto_cronogramas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_projeto_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_projeto_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clockify_projeto_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_projeto_user_clockify_projeto_id_foreign` (`clockify_projeto_id`),
  KEY `clockify_projeto_user_user_id_foreign` (`user_id`),
  CONSTRAINT `clockify_projeto_user_clockify_projeto_id_foreign` FOREIGN KEY (`clockify_projeto_id`) REFERENCES `clockify_projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clockify_projeto_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_projetos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_projetos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ano` smallint unsigned DEFAULT NULL,
  `numero` int unsigned DEFAULT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `clockify_cliente_id` bigint unsigned DEFAULT NULL,
  `responsavel_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `cor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publico` tinyint(1) NOT NULL DEFAULT '1',
  `horas_orcadas` decimal(8,2) DEFAULT NULL,
  `valor_hora` decimal(10,2) DEFAULT NULL,
  `valor_faturado` decimal(12,2) DEFAULT '0.00',
  `data_entrega_previsao` date DEFAULT NULL,
  `horas_diarias_planejamento` decimal(5,2) DEFAULT '8.00',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'em_andamento',
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_projetos_empresa_id_index` (`empresa_id`),
  KEY `clockify_projetos_clockify_cliente_id_index` (`clockify_cliente_id`),
  KEY `clockify_projetos_status_index` (`status`),
  KEY `clockify_projetos_responsavel_id_foreign` (`responsavel_id`),
  KEY `clockify_projetos_codigo_index` (`codigo`),
  KEY `clockify_projetos_empresa_id_ano_numero_index` (`empresa_id`,`ano`,`numero`),
  CONSTRAINT `clockify_projetos_clockify_cliente_id_foreign` FOREIGN KEY (`clockify_cliente_id`) REFERENCES `clockify_clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clockify_projetos_responsavel_id_foreign` FOREIGN KEY (`responsavel_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_tags_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_time_offs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_time_offs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_time_offs_empresa_id_index` (`empresa_id`),
  KEY `clockify_time_offs_user_id_index` (`user_id`),
  CONSTRAINT `clockify_time_offs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_time_trackers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_time_trackers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `clockify_projeto_id` bigint unsigned DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `inicio` datetime NOT NULL,
  `fim` datetime DEFAULT NULL,
  `faturavel` tinyint(1) NOT NULL DEFAULT '0',
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_time_trackers_empresa_id_index` (`empresa_id`),
  KEY `clockify_time_trackers_user_id_index` (`user_id`),
  KEY `clockify_time_trackers_clockify_projeto_id_index` (`clockify_projeto_id`),
  CONSTRAINT `clockify_time_trackers_clockify_projeto_id_foreign` FOREIGN KEY (`clockify_projeto_id`) REFERENCES `clockify_projetos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clockify_time_trackers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_time_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_time_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clockify_time_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_time_user_clockify_time_id_foreign` (`clockify_time_id`),
  KEY `clockify_time_user_user_id_foreign` (`user_id`),
  CONSTRAINT `clockify_time_user_clockify_time_id_foreign` FOREIGN KEY (`clockify_time_id`) REFERENCES `clockify_times` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clockify_time_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_times` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `lider_id` bigint unsigned DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clockify_times_empresa_id_index` (`empresa_id`),
  KEY `clockify_times_lider_id_foreign` (`lider_id`),
  CONSTRAINT `clockify_times_lider_id_foreign` FOREIGN KEY (`lider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clockify_tracker_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clockify_tracker_tag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clockify_time_tracker_id` bigint unsigned NOT NULL,
  `clockify_tag_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tracker_tag_tracker` (`clockify_time_tracker_id`),
  KEY `fk_tracker_tag_tag` (`clockify_tag_id`),
  CONSTRAINT `fk_tracker_tag_tag` FOREIGN KEY (`clockify_tag_id`) REFERENCES `clockify_tags` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tracker_tag_tracker` FOREIGN KEY (`clockify_time_tracker_id`) REFERENCES `clockify_time_trackers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuracao_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracao_checklist` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_checklist_id` bigint unsigned DEFAULT NULL,
  `item_checklist_id` bigint unsigned DEFAULT NULL,
  `ativo` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_checklist_id` (`tipo_checklist_id`),
  KEY `item_checklist_id` (`item_checklist_id`),
  KEY `configuracao_checklist_empresa_id_index` (`empresa_id`),
  CONSTRAINT `configuracao_checklist_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuracoes_calcao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes_calcao` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `grupo_frota_id` bigint unsigned DEFAULT NULL COMMENT 'NULL = regra de empresa; preenchido = regra de grupo',
  `modelo_id` bigint unsigned DEFAULT NULL COMMENT 'NULL = regra de empresa/grupo; preenchido = regra de modelo',
  `tipo_regra_valor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixo' COMMENT 'fixo|percentual',
  `valor` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor fixo em reais ou percentual (ex: 30 = 30%)',
  `valor_minimo` decimal(10,2) DEFAULT NULL,
  `valor_maximo` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `configuracoes_calcao_empresa_id_index` (`empresa_id`),
  KEY `configuracoes_calcao_grupo_frota_id_index` (`grupo_frota_id`),
  KEY `configuracoes_calcao_modelo_id_index` (`modelo_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `configuracoes_calcao_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contrato_checkin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contrato_checkin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reserva_id` int DEFAULT NULL,
  `data_saida` date DEFAULT NULL,
  `hora_saida` time DEFAULT NULL,
  `marca_id` int DEFAULT NULL,
  `modelo_id` int DEFAULT NULL,
  `assinatura` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_assinatura` timestamp NULL DEFAULT NULL,
  `assinado` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `nivel_combustivel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas_saida` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes_saida` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quilometragem_atual` decimal(10,2) DEFAULT NULL,
  `veiculo_id` int DEFAULT NULL,
  `grupo_frota_original_id` bigint unsigned DEFAULT NULL COMMENT 'Grupo/categoria originalmente reservado',
  `veiculo_previsto_id` bigint unsigned DEFAULT NULL COMMENT 'Veiculo que seria entregue originalmente, se houver',
  `houve_upgrade_categoria` tinyint(1) NOT NULL DEFAULT '0',
  `motivo_upgrade` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_upgrade_id` bigint unsigned DEFAULT NULL,
  `data_hora_upgrade` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `foto_direita_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_esquerda_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_frente_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_tras_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_velocimetro_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_combustivel_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aceite_termo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `termo_texto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `signatario_nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatario_cpf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura_dispositivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contrato_checkin_empresa_id_index` (`empresa_id`),
  CONSTRAINT `contrato_checkin_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contrato_checkout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contrato_checkout` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reserva_id` int unsigned NOT NULL,
  `veiculo_id` int unsigned NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `quilometragem_devolucao` decimal(12,2) NOT NULL,
  `nivel_combustivel_devolucao` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_devolucao` date NOT NULL,
  `hora_devolucao` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notas_devolucao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `observacoes_devolucao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `foto_direita_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_esquerda_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_frente_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_tras_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_velocimetro_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_combustivel_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_assinatura` datetime DEFAULT NULL,
  `pdf_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinado` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contrato_checkout_reserva_id_unique` (`reserva_id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `veiculo_id` (`veiculo_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `contrato_checkout_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `creditos_tributarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `creditos_tributarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `documento_fiscal_entrada_id` bigint unsigned DEFAULT NULL,
  `tributo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_original` decimal(15,2) NOT NULL,
  `valor_utilizado` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo` decimal(15,2) NOT NULL,
  `origem` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_apropriacao` datetime NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creditos_tributarios_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `creditos_tributarios_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cupom_desconto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cupom_desconto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cupom_desconto_empresa_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `cupom_desconto_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `danos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `danos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int DEFAULT NULL,
  `localizacao` enum('interior','exterior') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'interior',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `danos_tenant_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `danos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria_danos` (`id`),
  CONSTRAINT `danos_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dias_uteis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dias_uteis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dia` int DEFAULT NULL,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dias_uteis_empresa_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `disponibilidade_inicial_veiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disponibilidade_inicial_veiculo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `veiculo_id` int NOT NULL,
  `estacao_id` int NOT NULL,
  `data_hora` timestamp NOT NULL,
  `efetuou` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disponibilidade_inicial_veiculo_empresa_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `disponibilidade_inicial_veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disponibilidade_inicial_veiculos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documentacao_veiculo_parcelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentacao_veiculo_parcelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `documentacao_veiculo_id` bigint unsigned NOT NULL,
  `numero_parcela` int NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `valor` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documentacoes_veiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentacoes_veiculo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` int unsigned NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('ipva','licenciamento','seguro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `parcelas_total` int NOT NULL DEFAULT '1',
  `valor_parcela` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `categoria_financeira_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documentacoes_veiculo_categoria_financeira_id_foreign` (`categoria_financeira_id`),
  KEY `documentacoes_veiculo_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `documentacoes_veiculo_categoria_financeira_id_foreign` FOREIGN KEY (`categoria_financeira_id`) REFERENCES `categorias_financeiras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documentacoes_veiculo_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documentos_fiscais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos_fiscais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `operacao_fiscal_id` bigint unsigned NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_dps` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_nfse` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chave_acesso` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_tributacao` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `xml` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `xml_retorno` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `protocolo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `erro_codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `erro_mensagem` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `emitida_em` datetime DEFAULT NULL,
  `cancelada_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documentos_fiscais_empresa_id_foreign` (`empresa_id`),
  KEY `documentos_fiscais_operacao_fiscal_id_foreign` (`operacao_fiscal_id`),
  CONSTRAINT `documentos_fiscais_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documentos_fiscais_operacao_fiscal_id_foreign` FOREIGN KEY (`operacao_fiscal_id`) REFERENCES `operacoes_fiscais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa_alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_alertas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `tipo` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ex: veiculos_excedentes, modulo_indisponivel, etc',
  `nivel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aviso' COMMENT 'aviso, critico, info',
  `titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'dados extras para contexto',
  `resolvido` tinyint(1) NOT NULL DEFAULT '0',
  `resolvido_em` timestamp NULL DEFAULT NULL,
  `resolvido_por_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_alertas_resolvido_por_user_id_foreign` (`resolvido_por_user_id`),
  KEY `empresa_alertas_empresa_id_resolvido_index` (`empresa_id`,`resolvido`),
  CONSTRAINT `empresa_alertas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empresa_alertas_resolvido_por_user_id_foreign` FOREIGN KEY (`resolvido_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa_contratos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_contratos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `plano_id` bigint unsigned DEFAULT NULL,
  `numero` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `razao_social` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj_cliente` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco_cliente` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representante_nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(12,2) DEFAULT NULL,
  `valor_texto` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `conteudo_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `arquivo_pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gerado_em` timestamp NULL DEFAULT NULL,
  `assinado_em` timestamp NULL DEFAULT NULL,
  `gerado_por` bigint unsigned DEFAULT NULL,
  `assinado_por` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresa_contratos_numero_unique` (`numero`),
  KEY `empresa_contratos_plano_id_foreign` (`plano_id`),
  KEY `empresa_contratos_empresa_id_status_index` (`empresa_id`,`status`),
  CONSTRAINT `empresa_contratos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empresa_contratos_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orgao_emissor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `valor` decimal(14,2) DEFAULT NULL,
  `data_emissao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `arquivo_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_documentos_empresa_id_tipo_index` (`empresa_id`,`tipo`),
  KEY `empresa_documentos_empresa_id_data_validade_index` (`empresa_id`,`data_validade`),
  CONSTRAINT `empresa_documentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa_ia_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_ia_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `provedor_padrao` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deepseek',
  `tokens_limite` int unsigned NOT NULL DEFAULT '4000',
  `anonimizar` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `tamanho_resumo` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medio',
  `instrucoes_extras` text COLLATE utf8mb4_unicode_ci,
  `deepseek_key` text COLLATE utf8mb4_unicode_ci,
  `deepseek_model` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `openai_key` text COLLATE utf8mb4_unicode_ci,
  `openai_model` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `usualt` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `dataalt` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresa_ia_configs_empresa_id_unique` (`empresa_id`),
  CONSTRAINT `empresa_ia_configs_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa_plano_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_plano_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `empresa_plano_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `periodicidade` enum('mensal','anual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensal',
  `metodo` enum('boleto','cartao','pix','dinheiro','transferencia','manual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'boleto',
  `status` enum('pendente','pago','vencido','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `data_vencimento` date NOT NULL,
  `pago_em` timestamp NULL DEFAULT NULL,
  `gateway_payment_id` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_payment_intent_id` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boleto_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boleto_codigo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_chave` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_qr_code_image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pix_qr_code_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pix_expira_em` timestamp NULL DEFAULT NULL,
  `nosso_numero` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia_interna` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referencia para pagamentos manuais internos',
  `instrucoes_pagamento` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `registrado_por_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_plano_pagamentos_empresa_plano_id_foreign` (`empresa_plano_id`),
  KEY `empresa_plano_pagamentos_registrado_por_user_id_foreign` (`registrado_por_user_id`),
  KEY `empresa_plano_pagamentos_empresa_id_status_data_vencimento_index` (`empresa_id`,`status`,`data_vencimento`),
  KEY `empresa_plano_pagamentos_usuinc_foreign` (`usuinc`),
  KEY `empresa_plano_pagamentos_usuatu_foreign` (`usuatu`),
  CONSTRAINT `empresa_plano_pagamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empresa_plano_pagamentos_empresa_plano_id_foreign` FOREIGN KEY (`empresa_plano_id`) REFERENCES `empresa_planos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empresa_plano_pagamentos_registrado_por_user_id_foreign` FOREIGN KEY (`registrado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_plano_pagamentos_usuatu_foreign` FOREIGN KEY (`usuatu`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_plano_pagamentos_usuinc_foreign` FOREIGN KEY (`usuinc`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa_planos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_planos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `plano_id` bigint unsigned NOT NULL,
  `proximo_plano_id` bigint unsigned DEFAULT NULL,
  `periodicidade` enum('mensal','anual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mensal',
  `proxima_periodicidade` enum('mensal','anual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativo','inadimplente','cancelado','trial','aguardando_pagamento') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `mudanca_tipo` enum('downgrade','cancelamento') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metodo_cobranca` enum('boleto','pix','cartao','manual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `data_inicio` date NOT NULL,
  `data_vencimento` date NOT NULL,
  `mudanca_efetiva_em` date DEFAULT NULL,
  `mudanca_solicitada_em` timestamp NULL DEFAULT NULL,
  `data_renovacao` date DEFAULT NULL COMMENT 'Próxima data de cobrança automática',
  `dia_vencimento` int DEFAULT NULL COMMENT 'Dia do mês para cobranças automáticas',
  `renovacao_automatica` tinyint(1) NOT NULL DEFAULT '0',
  `cartao_ultimos_digitos` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cartao_bandeira` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_cobrado` decimal(10,2) NOT NULL COMMENT 'Valor real no momento da contratação',
  `gateway_cobranca` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'stripe, asaas, manual, etc.',
  `gateway_subscription_id` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID da assinatura no gateway',
  `gateway_customer_id` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `criado_por_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_planos_plano_id_foreign` (`plano_id`),
  KEY `empresa_planos_criado_por_user_id_foreign` (`criado_por_user_id`),
  KEY `empresa_planos_empresa_id_status_index` (`status`),
  KEY `empresa_planos_proximo_plano_id_foreign` (`proximo_plano_id`),
  KEY `empresa_planos_usuinc_foreign` (`usuinc`),
  KEY `empresa_planos_usuatu_foreign` (`usuatu`),
  KEY `empresa_planos_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `empresa_planos_criado_por_user_id_foreign` FOREIGN KEY (`criado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_planos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_planos_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`),
  CONSTRAINT `empresa_planos_proximo_plano_id_foreign` FOREIGN KEY (`proximo_plano_id`) REFERENCES `planos` (`id`),
  CONSTRAINT `empresa_planos_usuatu_foreign` FOREIGN KEY (`usuatu`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_planos_usuinc_foreign` FOREIGN KEY (`usuinc`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dominio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representante` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_apresentacao_ativo` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `site_apresentacao_modelo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'modelo_1',
  `site_apresentacao_titulo` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_apresentacao_subtitulo` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_apresentacao_descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `site_apresentacao_botao_texto` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_apresentacao_botao_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_apresentacao_textos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `site_apresentacao_imagens` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `site_apresentacao_cores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `site_apresentacao_imagens_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `logo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_primaria` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_secundaria` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor_terciaria` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_pagamento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_customer_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moeda_pagamento` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_public_key` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mercado_livre_public_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mercado_livre_access_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_client_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_client_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_mode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_ativo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `regime_emissao` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NFSE_APENAS',
  `certificado_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificado_senha` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fiscal_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiscal_client_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_ambiente` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `layout_nfse` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RTC',
  `versao_layout` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.04.00',
  `nfse_provedor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_client_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_client_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nfse_inscricao_municipal` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_municipio_codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_codigo_servico` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_cnae` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_descricao_servico` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nfse_aliquota` decimal(5,2) DEFAULT NULL,
  `nfse_tributacao_issqn` tinyint unsigned DEFAULT NULL,
  `nfse_tomador_numero_padrao` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_tomador_bairro_padrao` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_ultima_importacao_em` timestamp NULL DEFAULT NULL,
  `ativo` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `licitacoes_ativo` varchar(1) NOT NULL DEFAULT 'N',
  `ia_limite_tokens` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reserva_escolher_empresa` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `reserva_selecionar_modelo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `reserva_atraso_max_minutos` int unsigned DEFAULT NULL,
  `reserva_bloqueio_pos_reserva_horas` int unsigned DEFAULT NULL,
  `aluguel_app_ativo` enum('S','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `site_tipo_reserva` enum('motorista_app','reserva_comum','ambas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ambas',
  `aluguel_app_exigir_aprovacao_documentos` enum('S','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `aluguel_app_documentos_obrigatorios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `aluguel_app_documento_adicional_label` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aluguel_app_campos_personalizados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `adicionar_novas_marcas_automaticamente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `adicionar_novos_modelos_automaticamente` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `empresa_id` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresas_slug_unique` (`slug`),
  KEY `empresas_empresa_id_index` (`empresa_id`),
  KEY `cnpj` (`cnpj`),
  KEY `email` (`email`),
  KEY `dominio` (`dominio`),
  KEY `empresas_usuinc_foreign` (`usuinc`),
  KEY `empresas_usuatu_foreign` (`usuatu`),
  CONSTRAINT `empresas_usuatu_foreign` FOREIGN KEY (`usuatu`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresas_usuinc_foreign` FOREIGN KEY (`usuinc`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estacao_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estacao_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `estacao_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estacao_user_user_id_estacao_id_unique` (`user_id`,`estacao_id`),
  CONSTRAINT `estacao_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia_util_id` int DEFAULT NULL,
  `endereco` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nfse_municipio_codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_inscricao_municipal` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_postal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pais_id` bigint unsigned DEFAULT NULL,
  `latitude` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `longitude` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `telefone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `celular` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cobrar_na_coleta` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `valor_taxa_coleta` decimal(10,2) DEFAULT NULL COMMENT 'Valor da taxa fora do expediente cobrada na coleta',
  `cobrar_na_entrega` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `valor_taxa_entrega` decimal(10,2) DEFAULT NULL COMMENT 'Valor da taxa fora do expediente cobrada na entrega',
  `valido_no_intervalo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `checkin` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `checkout` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dia_util_id` (`dia_util_id`),
  KEY `pais_id` (`pais_id`),
  KEY `estacoes_empresa_id_index` (`empresa_id`),
  CONSTRAINT `estacoes_fk_pais_id` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`),
  CONSTRAINT `estacoes_ibfk_1` FOREIGN KEY (`dia_util_id`) REFERENCES `dias_uteis` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estacoes_dias_uteis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estacoes_dias_uteis` (
  `estacao_id` int NOT NULL,
  `dia_util_id` int NOT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`estacao_id`,`dia_util_id`) USING BTREE,
  KEY `dia_utile_id` (`dia_util_id`),
  CONSTRAINT `estacoes_dias_uteis_ibfk_1` FOREIGN KEY (`estacao_id`) REFERENCES `estacoes` (`id`),
  CONSTRAINT `estacoes_dias_uteis_ibfk_2` FOREIGN KEY (`dia_util_id`) REFERENCES `dias_uteis` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `execucoes_manutencao_preventiva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `execucoes_manutencao_preventiva` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` int NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `ordem_servico_id` bigint unsigned DEFAULT NULL,
  `data_execucao` date NOT NULL,
  `km_execucao` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `execucoes_manutencao_preventiva_empresa_id_foreign` (`empresa_id`),
  KEY `execucoes_manutencao_preventiva_veiculo_id_foreign` (`veiculo_id`),
  KEY `execucoes_manutencao_preventiva_item_id_foreign` (`item_id`),
  KEY `execucoes_manutencao_preventiva_ordem_servico_id_foreign` (`ordem_servico_id`),
  CONSTRAINT `execucoes_manutencao_preventiva_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `execucoes_manutencao_preventiva_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `itens_manutencao_preventiva` (`id`) ON DELETE CASCADE,
  CONSTRAINT `execucoes_manutencao_preventiva_ordem_servico_id_foreign` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE SET NULL,
  CONSTRAINT `execucoes_manutencao_preventiva_veiculo_id_foreign` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `feriados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feriados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `estacao_id` bigint unsigned DEFAULT NULL,
  `data` date NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `global` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `ativo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_feriados_empresa_estacao_data` (`empresa_id`,`estacao_id`,`data`),
  KEY `feriados_empresa_id_index` (`empresa_id`),
  KEY `feriados_estacao_id_index` (`estacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `frota_caracteristicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `frota_caracteristicas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `frotas_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `frotas_caracteristicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `frotas_caracteristicas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `frota_id` int DEFAULT NULL,
  `caracteristica_id` int DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `frota_id` (`frota_id`),
  KEY `caracteristica_id` (`caracteristica_id`),
  CONSTRAINT `frotas_caracteristicas_ibfk_1` FOREIGN KEY (`frota_id`) REFERENCES `tipo_frota` (`id`),
  CONSTRAINT `frotas_caracteristicas_ibfk_2` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas_frotas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `funcoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `funcoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custo_hora` decimal(10,2) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funcoes_empresa_id_nome_unique` (`empresa_id`,`nome`),
  KEY `funcoes_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grupo_frota_caracteristica_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_frota_caracteristica_frota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_frota_id` int NOT NULL,
  `caracteristica_frota_id` int NOT NULL,
  `valor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_frota_id` (`grupo_frota_id`),
  KEY `modelo_id` (`caracteristica_frota_id`) USING BTREE,
  CONSTRAINT `fk_grupo_frota_caracteristica_frota_caracteristica_frota_id` FOREIGN KEY (`caracteristica_frota_id`) REFERENCES `caracteristicas_frotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grupo_frota_caracteristica_frota_grupo_frota_id` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_frota_caracteristica_frota_caracteristica_frota_id_foreign` FOREIGN KEY (`caracteristica_frota_id`) REFERENCES `caracteristicas_frotas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_frota_caracteristica_frota_grupo_frota_id_foreign` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grupo_frota_modelo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_frota_modelo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_frota_id` int NOT NULL,
  `modelo_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_frota_id` (`grupo_frota_id`),
  KEY `modelo_id` (`modelo_id`),
  CONSTRAINT `grupo_frota_modelo_ibfk_1` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_frota_modelo_ibfk_2` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grupo_frota_opcional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_frota_opcional` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_frota_id` int NOT NULL,
  `opcional_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `opcional_id` (`opcional_id`),
  KEY `grupo_frota_id` (`grupo_frota_id`),
  CONSTRAINT `fk_grupo_frota_opcional_grupo_frota_id` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grupo_frota_opcional_opcional_id` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grupo_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grupos_caracteristicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos_caracteristicas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_id` int DEFAULT NULL,
  `caracteristica_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_id` (`grupo_id`),
  KEY `caracteristica_id` (`caracteristica_id`),
  CONSTRAINT `grupos_caracteristicas_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_frota` (`id`),
  CONSTRAINT `grupos_caracteristicas_ibfk_2` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas_grupo_frota` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grupos_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos_frota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_frota_id` int DEFAULT NULL,
  `codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_sipp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem_classificacao` int DEFAULT NULL,
  `imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagens` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `valor_diario` decimal(10,2) DEFAULT '0.00',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `ordem` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'Hierarquia de categoria para upgrade: maior = melhor',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `frota_id` (`tipo_frota_id`),
  KEY `grupos_frota_empresa_id_index` (`empresa_id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `grupos_frota_ibfk_1` FOREIGN KEY (`tipo_frota_id`) REFERENCES `tipo_frota` (`id`),
  CONSTRAINT `grupos_frota_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `horario_diarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_diarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estacao_id` int DEFAULT NULL,
  `horario_diario_id` int DEFAULT NULL,
  `hora_abertura` time NOT NULL DEFAULT '00:00:00',
  `hora_fechamento` time NOT NULL DEFAULT '00:00:00',
  `hora_inicio_almoco` time NOT NULL DEFAULT '00:00:00',
  `hora_fim_almoco` time NOT NULL DEFAULT '00:00:00',
  `tem_intervalo_almoco` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `hora_inicio_fora_expediente` time NOT NULL DEFAULT '00:00:00',
  `hora_fim_fora_expediente` time NOT NULL DEFAULT '00:00:00',
  `funciona_24h` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `funciona` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_estacao` (`estacao_id`,`horario_diario_id`),
  KEY `id_horario_diario` (`horario_diario_id`),
  KEY `horarios_empresa_id_index` (`empresa_id`),
  CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`horario_diario_id`) REFERENCES `horarios_diarios` (`id`),
  CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`estacao_id`) REFERENCES `estacoes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `horarios_diarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios_diarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `horarios_diarios_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ia_config_global`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ia_config_global` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provedor` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deepseek',
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `modelo` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assistente_ativo` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `transcricao_provedor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transcricao_key` text COLLATE utf8mb4_unicode_ci,
  `transcricao_modelo` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ia_usos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ia_usos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `provedor` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurso` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokens_entrada` int unsigned NOT NULL DEFAULT '0',
  `tokens_saida` int unsigned NOT NULL DEFAULT '0',
  `custo_estimado` decimal(12,6) DEFAULT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ok',
  `erro` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracao_ms` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ia_usos_user_id_foreign` (`user_id`),
  KEY `ia_usos_empresa_id_created_at_index` (`empresa_id`,`created_at`),
  KEY `ia_usos_recurso_created_at_index` (`recurso`,`created_at`),
  CONSTRAINT `ia_usos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ia_usos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_checklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `checklist_id` int DEFAULT NULL,
  `item` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_checklist_id` int DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url_foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklist_id` (`checklist_id`),
  KEY `item_checklist_empresa_id_index` (`empresa_id`),
  CONSTRAINT `item_checklist_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `checklists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `itens_manutencao_preventiva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens_manutencao_preventiva` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `itens_manutencao_preventiva_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `itens_manutencao_preventiva_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `itens_plano`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `itens_plano` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plano_id` bigint unsigned NOT NULL,
  `descricao` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `incluso` tinyint(1) NOT NULL DEFAULT '1',
  `ordem` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `itens_plano_plano_id_foreign` (`plano_id`),
  CONSTRAINT `itens_plano_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lancamentos_financeiros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lancamentos_financeiros` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `tipo` enum('receita','despesa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_financeira_id` bigint unsigned DEFAULT NULL,
  `data_lancamento` datetime DEFAULT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('pendente','pago','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `metodo_pagamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `veiculo_id` int unsigned DEFAULT NULL,
  `documentacao_veiculo_id` bigint unsigned DEFAULT NULL,
  `reserva_id` int unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `recorrente` tinyint(1) NOT NULL DEFAULT '0',
  `recorrencia` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelas_totais` int DEFAULT NULL,
  `parcela_atual` int DEFAULT NULL,
  `grupo_recorrencia_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lancamentos_financeiros_empresa_id_foreign` (`empresa_id`),
  KEY `lancamentos_financeiros_categoria_financeira_id_foreign` (`categoria_financeira_id`),
  KEY `lancamentos_financeiros_user_id_foreign` (`user_id`),
  KEY `lancamentos_financeiros_documentacao_veiculo_id_foreign` (`documentacao_veiculo_id`),
  CONSTRAINT `lancamentos_financeiros_categoria_financeira_id_foreign` FOREIGN KEY (`categoria_financeira_id`) REFERENCES `categorias_financeiras` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lancamentos_financeiros_documentacao_veiculo_id_foreign` FOREIGN KEY (`documentacao_veiculo_id`) REFERENCES `documentacoes_veiculo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lancamentos_financeiros_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lancamentos_financeiros_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `licitacao_analises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licitacao_analises` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `licitacao_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `modelo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nota_tecnica_estimada` decimal(5,2) DEFAULT NULL,
  `viabilidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parecer` text COLLATE utf8mb4_unicode_ci,
  `criterios` json DEFAULT NULL,
  `exigencias` json DEFAULT NULL,
  `gaps` json DEFAULT NULL,
  `divergencia` json DEFAULT NULL,
  `preco_sugerido` decimal(14,2) DEFAULT NULL,
  `preco_max_edital` decimal(14,2) DEFAULT NULL,
  `preco_motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokens_entrada` int unsigned DEFAULT NULL,
  `tokens_saida` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `licitacao_analises_empresa_id_foreign` (`empresa_id`),
  KEY `licitacao_analises_licitacao_id_foreign` (`licitacao_id`),
  KEY `licitacao_analises_user_id_foreign` (`user_id`),
  CONSTRAINT `licitacao_analises_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  CONSTRAINT `licitacao_analises_licitacao_id_foreign` FOREIGN KEY (`licitacao_id`) REFERENCES `licitacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `licitacao_analises_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `licitacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licitacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `orgao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `objeto` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_edital` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criterio_julgamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_abertura` date DEFAULT NULL,
  `valor_estimado` decimal(14,2) DEFAULT NULL,
  `url_edital` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arquivo_path` text COLLATE utf8mb4_unicode_ci,
  `texto_edital` longtext COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cadastrada',
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `licitacoes_empresa_id_status_index` (`empresa_id`,`status`),
  KEY `licitacoes_empresa_id_data_abertura_index` (`empresa_id`,`data_abertura`),
  CONSTRAINT `licitacoes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `fk_log_jobs_fk1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_fipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cars',
  `nome_fipe` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marcas_empresa_id_index` (`empresa_id`),
  KEY `marcas_vehicle_type_index` (`vehicle_type`),
  CONSTRAINT `marcas_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `modelos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modelos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_fipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cars',
  `nome_fipe` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ano_modelo` smallint unsigned DEFAULT NULL,
  `imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `marca_id` int DEFAULT NULL,
  `segmento_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marca_id` (`marca_id`),
  KEY `segmento_id` (`segmento_id`),
  KEY `modelos_empresa_id_index` (`empresa_id`),
  KEY `modelos_vehicle_type_index` (`vehicle_type`),
  CONSTRAINT `modelos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `motorista_app_solicitacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motorista_app_solicitacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `plano_motorista_app_id` bigint unsigned DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `dados_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `documentos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `motorista_app_solicitacoes_user_id_foreign` (`user_id`),
  KEY `motorista_app_solicitacoes_plano_motorista_app_id_foreign` (`plano_motorista_app_id`),
  KEY `motorista_app_solicitacoes_empresa_id_user_id_index` (`empresa_id`,`user_id`),
  CONSTRAINT `motorista_app_solicitacoes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `motorista_app_solicitacoes_plano_motorista_app_id_foreign` FOREIGN KEY (`plano_motorista_app_id`) REFERENCES `planos_motorista_app` (`id`) ON DELETE SET NULL,
  CONSTRAINT `motorista_app_solicitacoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimentacaos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacaos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacoes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `veiculo_id` int DEFAULT NULL,
  `modelo_id` int DEFAULT NULL,
  `tipo_movimentacao_id` int NOT NULL,
  `local_origem_id` int DEFAULT NULL,
  `local_destino_id` int DEFAULT NULL,
  `data_registro` datetime DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `data_saida` datetime DEFAULT NULL,
  `data_chegada_prevista` datetime DEFAULT NULL,
  `data_chegada_real` datetime DEFAULT NULL,
  `gera_disponibilidade` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `status_movimentacao_id` int DEFAULT NULL,
  `reserva_id` int DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ordem_servico_id` int DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `km_saida` int unsigned DEFAULT NULL,
  `km_chegada` int unsigned DEFAULT NULL,
  `ativo` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `veiculo_id` (`veiculo_id`),
  KEY `tipo_movimentacao_id` (`tipo_movimentacao_id`),
  KEY `local_origem` (`local_origem_id`),
  KEY `local_destino` (`local_destino_id`),
  KEY `status_movimentacao_id` (`status_movimentacao_id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `modelo_id` (`modelo_id`),
  KEY `ordem_servico_id` (`ordem_servico_id`),
  KEY `user_id` (`user_id`),
  KEY `movimentacoes_empresa_id_index` (`empresa_id`),
  CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimentacoes_ibfk_3` FOREIGN KEY (`tipo_movimentacao_id`) REFERENCES `tipos_movimentacao` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimentacoes_ibfk_4` FOREIGN KEY (`local_origem_id`) REFERENCES `estacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimentacoes_ibfk_5` FOREIGN KEY (`local_destino_id`) REFERENCES `estacoes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimentacoes_ibfk_6` FOREIGN KEY (`status_movimentacao_id`) REFERENCES `status_movimentacao` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimentacoes_ibfk_7` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimentacoes_ibfk_8` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimentacoes_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacoes_historico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `movimentacao_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `campo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_anterior` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor_novo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `acao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'update',
  `observacao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimentacoes_historico_movimentacao_id_created_at_index` (`movimentacao_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `multa_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multa_historico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `multa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `campo_alterado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_anterior` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_novo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `multa_historico_multa_id_index` (`multa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `multas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `reserva_motorista_app_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL COMMENT 'user_id do locatario',
  `condutor_id` bigint unsigned DEFAULT NULL COMMENT 'user_id do condutor responsavel, se diferente do locatario',
  `placa_momento` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Placa no momento da infracao',
  `numero_auto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Numero/auto da infracao',
  `orgao_autuador` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_hora_infracao` datetime DEFAULT NULL,
  `local_infracao` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor_original` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_desconto` decimal(10,2) DEFAULT NULL,
  `valor_final` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pontos` tinyint unsigned DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `status_multa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente' COMMENT 'pendente|em_analise|aguardando_identificacao_condutor|recorrida|deferida|indeferida|paga|cancelada',
  `status_desconto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nao_aplicado' COMMENT 'nao_aplicado|pendente|descontado|nao_descontar|estornado',
  `responsavel_lancamento_id` bigint unsigned DEFAULT NULL,
  `observacao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `destino_cobranca` enum('empresa','cliente') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'empresa' COMMENT 'Quem absorve o custo da multa',
  `forma_pagamento` enum('dinheiro','cartao_credito','cartao_debito','pix','boleto','debito_automatico') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pago_em` timestamp NULL DEFAULT NULL,
  `pago_por_user_id` bigint unsigned DEFAULT NULL,
  `referencia_pagamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nº do comprovante, NSU, código PIX etc.',
  `anexo_comprovante_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo_pdf_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `multas_empresa_id_index` (`empresa_id`),
  KEY `multas_reserva_id_index` (`reserva_id`),
  KEY `multas_veiculo_id_index` (`veiculo_id`),
  KEY `multas_cliente_id_index` (`cliente_id`),
  KEY `multas_pago_por_user_id_foreign` (`pago_por_user_id`),
  KEY `multas_reserva_motorista_app_id_index` (`reserva_motorista_app_id`),
  CONSTRAINT `multas_pago_por_user_id_foreign` FOREIGN KEY (`pago_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `nfse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nfse` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ambiente` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfse_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dps_id` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serie` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cancelamento_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `cancelada_em` timestamp NULL DEFAULT NULL,
  `ultima_consulta_em` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nfse_empresa_id_reserva_id_index` (`empresa_id`,`reserva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `onboarding_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `onboarding_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `step_empresa_configurada` tinyint(1) NOT NULL DEFAULT '0',
  `step_plano_contratado` tinyint(1) NOT NULL DEFAULT '0',
  `step_pagamento_realizado` tinyint(1) NOT NULL DEFAULT '0',
  `step_frota_cadastrada` tinyint(1) NOT NULL DEFAULT '0',
  `step_estacao_cadastrada` tinyint(1) NOT NULL DEFAULT '0',
  `step_usuario_criado` tinyint(1) NOT NULL DEFAULT '0',
  `step_site_configurado` tinyint(1) NOT NULL DEFAULT '0',
  `concluido` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `onboarding_steps_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `onboarding_steps_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opcionais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcionais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_cobranca` enum('por_dia','valor_integral') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_opcional_id` int NOT NULL,
  `preco_por_dia` decimal(10,2) DEFAULT NULL,
  `preco_integral` decimal(10,2) DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `opcionais_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opcionais_grupos_veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcionais_grupos_veiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `opcional_id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `opcional_id` (`opcional_id`),
  KEY `grupo_frota_id` (`grupo_frota_id`),
  CONSTRAINT `opcionais_grupos_veiculos_ibfk_1` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opcionais_grupos_veiculos_ibfk_2` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opcional_grupo_veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcional_grupo_veiculos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opcionals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcionals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_servico_municipal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `operacoes_fiscais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operacoes_fiscais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `locacao_id` bigint unsigned DEFAULT NULL,
  `fatura_id` bigint unsigned DEFAULT NULL,
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_fato_gerador` datetime NOT NULL,
  `valor_operacao` decimal(15,2) NOT NULL,
  `valor_base_calculo` decimal(15,2) NOT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operacoes_fiscais_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `operacoes_fiscais_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `operacoes_fiscais_tributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operacoes_fiscais_tributos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operacao_fiscal_id` bigint unsigned NOT NULL,
  `tributo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cst` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_calculo` decimal(15,2) NOT NULL,
  `aliquota` decimal(10,4) NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operacoes_fiscais_tributos_operacao_fiscal_id_foreign` (`operacao_fiscal_id`),
  CONSTRAINT `operacoes_fiscais_tributos_operacao_fiscal_id_foreign` FOREIGN KEY (`operacao_fiscal_id`) REFERENCES `operacoes_fiscais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordem_servico_anexos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordem_servico_anexos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ordem_servico_id` bigint unsigned NOT NULL,
  `path` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_bytes` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordem_servico_anexos_ordem_servico_id_created_at_index` (`ordem_servico_id`,`created_at`),
  CONSTRAINT `ordem_servico_anexos_ordem_servico_id_foreign` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordem_servico_campo_valores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordem_servico_campo_valores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ordem_servico_id` bigint unsigned NOT NULL,
  `os_subcategoria_campo_id` bigint unsigned NOT NULL,
  `valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordem_servico_campo_valores_ordem_servico_id_foreign` (`ordem_servico_id`),
  KEY `ordem_servico_campo_valores_os_subcategoria_campo_id_foreign` (`os_subcategoria_campo_id`),
  CONSTRAINT `ordem_servico_campo_valores_ordem_servico_id_foreign` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordem_servico_campo_valores_os_subcategoria_campo_id_foreign` FOREIGN KEY (`os_subcategoria_campo_id`) REFERENCES `os_subcategoria_campos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordem_servico_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordem_servico_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ordem_servico_id` bigint unsigned NOT NULL,
  `veiculo_dano_id` int unsigned DEFAULT NULL,
  `descricao_servico` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_item` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'servico',
  `quantidade` int NOT NULL DEFAULT '1',
  `valor_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `resultado` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pendencia_manutencao_preventiva_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ordem_servico_itens_ordem_servico_id_foreign` (`ordem_servico_id`),
  KEY `ordem_servico_itens_veiculo_dano_id_index` (`veiculo_dano_id`),
  KEY `fk_osi_pendencia` (`pendencia_manutencao_preventiva_id`),
  CONSTRAINT `fk_osi_pendencia` FOREIGN KEY (`pendencia_manutencao_preventiva_id`) REFERENCES `pendencias_manutencao_preventiva` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordem_servico_itens_ordem_servico_id_foreign` FOREIGN KEY (`ordem_servico_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordens_servico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordens_servico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_id` int unsigned DEFAULT NULL,
  `veiculo_id` int unsigned NOT NULL,
  `reserva_id` int unsigned DEFAULT NULL,
  `sinistro_id` bigint unsigned DEFAULT NULL,
  `user_id_abertura` bigint unsigned NOT NULL,
  `user_id_encerramento` bigint unsigned DEFAULT NULL,
  `status` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberta',
  `valor_pecas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_mao_obra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `relatorio_conclusao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_conclusao` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `os_categoria_id` bigint unsigned DEFAULT NULL,
  `os_subcategoria_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordens_servico_numero_unique` (`numero`),
  KEY `ordens_servico_status_created_at_index` (`status`,`created_at`),
  KEY `ordens_servico_veiculo_id_index` (`veiculo_id`),
  KEY `ordens_servico_sinistro_id_index` (`sinistro_id`),
  KEY `ordens_servico_os_categoria_id_foreign` (`os_categoria_id`),
  KEY `ordens_servico_os_subcategoria_id_foreign` (`os_subcategoria_id`),
  CONSTRAINT `ordens_servico_os_categoria_id_foreign` FOREIGN KEY (`os_categoria_id`) REFERENCES `os_categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordens_servico_os_subcategoria_id_foreign` FOREIGN KEY (`os_subcategoria_id`) REFERENCES `os_subcategorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordens_servico_sinistro_id_foreign` FOREIGN KEY (`sinistro_id`) REFERENCES `sinistros` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `os_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `os_categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `os_subcategoria_campos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `os_subcategoria_campos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `os_subcategoria_id` bigint unsigned NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `opcoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `ordem` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `os_subcategoria_campos_os_subcategoria_id_foreign` (`os_subcategoria_id`),
  CONSTRAINT `os_subcategoria_campos_os_subcategoria_id_foreign` FOREIGN KEY (`os_subcategoria_id`) REFERENCES `os_subcategorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `os_subcategorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `os_subcategorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `os_categoria_id` bigint unsigned NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_sinistro` tinyint(1) NOT NULL DEFAULT '0',
  `show_reserva` tinyint(1) NOT NULL DEFAULT '0',
  `show_avarias` tinyint(1) NOT NULL DEFAULT '0',
  `show_checklist_prevencao` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `os_subcategorias_os_categoria_id_foreign` (`os_categoria_id`),
  CONSTRAINT `os_subcategorias_os_categoria_id_foreign` FOREIGN KEY (`os_categoria_id`) REFERENCES `os_categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `paises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paises` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigla` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paises_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `stripe_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_user_id_foreign` (`user_id`),
  KEY `payments_empresa_id_index` (`empresa_id`),
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pendencias_manutencao_preventiva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendencias_manutencao_preventiva` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` int NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `data_prevista` date DEFAULT NULL,
  `km_previsto` int DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pendencias_manutencao_preventiva_empresa_id_foreign` (`empresa_id`),
  KEY `pendencias_manutencao_preventiva_veiculo_id_foreign` (`veiculo_id`),
  KEY `pendencias_manutencao_preventiva_item_id_foreign` (`item_id`),
  CONSTRAINT `pendencias_manutencao_preventiva_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendencias_manutencao_preventiva_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `itens_manutencao_preventiva` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendencias_manutencao_preventiva_veiculo_id_foreign` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_unique` (`name`,`guard_name`),
  KEY `permissions_empresa_id_index` (`empresa_id`),
  KEY `permissions_grupo_index` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plano_manutencao_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plano_manutencao_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plano_id` bigint unsigned NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `frequencia_meses` int DEFAULT NULL,
  `frequencia_km` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plano_manutencao_itens_plano_id_foreign` (`plano_id`),
  KEY `plano_manutencao_itens_item_id_foreign` (`item_id`),
  CONSTRAINT `plano_manutencao_itens_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `itens_manutencao_preventiva` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plano_manutencao_itens_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos_manutencao_preventiva` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plano_motorista_app_grupo_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plano_motorista_app_grupo_frota` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plano_motorista_app_id` bigint unsigned NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plano_motorista_app_grupo_frota_unique` (`plano_motorista_app_id`,`grupo_frota_id`),
  KEY `plano_motorista_app_grupo_frota_grupo_frota_id_foreign` (`grupo_frota_id`),
  CONSTRAINT `plano_motorista_app_grupo_frota_grupo_frota_id_foreign` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plano_motorista_app_grupo_frota_plano_motorista_app_id_foreign` FOREIGN KEY (`plano_motorista_app_id`) REFERENCES `planos_motorista_app` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plano_motorista_app_opcionais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plano_motorista_app_opcionais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plano_motorista_app_id` bigint unsigned NOT NULL,
  `opcional_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plano_motorista_app_opcional_unique` (`plano_motorista_app_id`,`opcional_id`),
  KEY `pmapp_opc_item_fk` (`opcional_id`),
  CONSTRAINT `pmapp_opc_item_fk` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pmapp_opc_plano_fk` FOREIGN KEY (`plano_motorista_app_id`) REFERENCES `planos_motorista_app` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plano_motorista_app_opcional_grupo_veiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plano_motorista_app_opcional_grupo_veiculo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plano_motorista_app_id` bigint unsigned NOT NULL,
  `opcional_grupo_veiculo_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plano_motorista_app_opcional_unique` (`plano_motorista_app_id`,`opcional_grupo_veiculo_id`),
  KEY `pmapp_opcional_fk` (`opcional_grupo_veiculo_id`),
  CONSTRAINT `pmapp_opcional_fk` FOREIGN KEY (`opcional_grupo_veiculo_id`) REFERENCES `opcional_grupo_veiculos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pmapp_plano_fk` FOREIGN KEY (`plano_motorista_app_id`) REFERENCES `planos_motorista_app` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plano_permissao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plano_permissao` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plano_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plano_permissao_unique` (`plano_id`,`permission_id`),
  KEY `plano_permissao_permission_id_foreign` (`permission_id`),
  CONSTRAINT `plano_permissao_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plano_permissao_plano_id_foreign` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `planos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datainc` timestamp NULL DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `datatu` timestamp NULL DEFAULT NULL,
  `nome` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor_mensal` decimal(10,2) NOT NULL,
  `valor_anual` decimal(10,2) NOT NULL COMMENT 'Valor mensal cobrado no plano anual (com desconto)',
  `valor_veiculo_adicional` decimal(10,2) DEFAULT NULL,
  `quantidade_filiais` int DEFAULT NULL,
  `valor_filial_adicional` decimal(10,2) DEFAULT NULL,
  `valor_armazenamento_adicional` decimal(10,2) DEFAULT NULL,
  `quantidade_armazenamento_adicional` int DEFAULT NULL,
  `limite_veiculos` int DEFAULT NULL COMMENT 'null = ilimitado',
  `limite_armazenamento_mb` int DEFAULT NULL,
  `limite_usuarios` smallint unsigned DEFAULT NULL,
  `limite_permissoes_por_regra` int unsigned DEFAULT NULL,
  `site_vendas` tinyint(1) NOT NULL DEFAULT '0',
  `modulo_multas` tinyint(1) NOT NULL DEFAULT '0',
  `modulo_nfse` tinyint(1) NOT NULL DEFAULT '0',
  `multi_estacao` tinyint(1) NOT NULL DEFAULT '0',
  `api_integracao` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `destaque` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Plano mais escolhido / recomendado',
  `ordem` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planos_slug_unique` (`slug`),
  KEY `planos_usuinc_foreign` (`usuinc`),
  KEY `planos_usuatu_foreign` (`usuatu`),
  CONSTRAINT `planos_usuatu_foreign` FOREIGN KEY (`usuatu`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `planos_usuinc_foreign` FOREIGN KEY (`usuinc`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `planos_manutencao_preventiva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planos_manutencao_preventiva` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` int unsigned DEFAULT NULL,
  `grupo_frota_id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planos_manutencao_preventiva_empresa_id_foreign` (`empresa_id`),
  KEY `planos_manutencao_preventiva_grupo_frota_id_foreign` (`grupo_frota_id`),
  CONSTRAINT `planos_manutencao_preventiva_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planos_manutencao_preventiva_grupo_frota_id_foreign` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `planos_motorista_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planos_motorista_app` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_periodo` decimal(10,2) NOT NULL,
  `periodo` enum('semana','quinze','mes','trimestre','semestre','ano') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `km_tipo` enum('livre','limitado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'livre',
  `km_por_periodo` int unsigned DEFAULT NULL,
  `calcao` decimal(10,2) DEFAULT NULL,
  `ativo` enum('S','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `ordem` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plano_motorista_app_empresa_ativo_ordem_idx` (`empresa_id`,`ativo`,`ordem`),
  CONSTRAINT `planos_motorista_app_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recebimentos_tributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recebimentos_tributos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transacao_id` bigint unsigned NOT NULL,
  `tributo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recebimentos_tributos_transacao_id_foreign` (`transacao_id`),
  CONSTRAINT `recebimentos_tributos_transacao_id_foreign` FOREIGN KEY (`transacao_id`) REFERENCES `transacoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `regras_tributarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regras_tributarias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tributo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_operacao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aliquota` decimal(8,4) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `gera_credito` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reserva_codigos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_codigos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_origem` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserva' COMMENT 'reserva, motorista_app',
  `reserva_id` bigint unsigned DEFAULT NULL,
  `reserva_motorista_app_id` bigint unsigned DEFAULT NULL,
  `status_label` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `valor_total` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reserva_codigos_codigo_unique` (`codigo`),
  KEY `reserva_codigos_empresa_id_index` (`empresa_id`),
  KEY `reserva_codigos_reserva_id_index` (`reserva_id`),
  KEY `reserva_codigos_reserva_motorista_app_id_index` (`reserva_motorista_app_id`),
  KEY `reserva_codigos_tipo_origem_index` (`tipo_origem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reserva_cupom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_cupom` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reserva_id` bigint unsigned NOT NULL,
  `cupom_desconto_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reserva_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reserva_id` int unsigned DEFAULT NULL,
  `acao` varchar(120) DEFAULT NULL,
  `nivel` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `detalhes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `usuario_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reserva_motorista_app_aditivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_motorista_app_aditivos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `reserva_motorista_app_id` bigint unsigned NOT NULL,
  `pagamento_id` bigint unsigned DEFAULT NULL,
  `contrato_checkin_id` bigint unsigned DEFAULT NULL,
  `numero_aditivo` int unsigned NOT NULL,
  `data_inicio_anterior` datetime NOT NULL,
  `data_fim_anterior` datetime NOT NULL,
  `data_inicio_nova` datetime NOT NULL,
  `data_fim_nova` datetime NOT NULL,
  `plano_anterior_id` bigint unsigned DEFAULT NULL,
  `plano_novo_id` bigint unsigned DEFAULT NULL,
  `valor_renovacao` decimal(12,2) NOT NULL,
  `valor_opcionais` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_total` decimal(12,2) NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aguardando_pagamento',
  `texto_contrato` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `snapshot_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `assinatura` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `assinado_em` datetime DEFAULT NULL,
  `ip_assinatura` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent_assinatura` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reserva_motorista_app_aditivos_empresa_id_foreign` (`empresa_id`),
  KEY `reserva_motorista_app_aditivos_reserva_motorista_app_id_foreign` (`reserva_motorista_app_id`),
  KEY `reserva_motorista_app_aditivos_pagamento_id_foreign` (`pagamento_id`),
  CONSTRAINT `reserva_motorista_app_aditivos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reserva_motorista_app_aditivos_pagamento_id_foreign` FOREIGN KEY (`pagamento_id`) REFERENCES `reserva_motorista_app_pagamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reserva_motorista_app_aditivos_reserva_motorista_app_id_foreign` FOREIGN KEY (`reserva_motorista_app_id`) REFERENCES `reservas_motorista_app` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reserva_motorista_app_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_motorista_app_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reserva_motorista_app_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `periodos` int unsigned NOT NULL DEFAULT '1',
  `periodo_unidade` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `data_inicio_prevista` datetime DEFAULT NULL,
  `data_fim_prevista` datetime DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `metodo_pagamento` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pago_em` datetime DEFAULT NULL,
  `referencia_pagamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reserva_motorista_app_pagamentos_user_id_foreign` (`user_id`),
  KEY `rmap_pags_reserva_status_idx` (`reserva_motorista_app_id`,`status`),
  KEY `rmap_pags_empresa_status_idx` (`empresa_id`,`status`),
  CONSTRAINT `reserva_motorista_app_pagamentos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reserva_motorista_app_pagamentos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rmap_pags_rmap_id_fk` FOREIGN KEY (`reserva_motorista_app_id`) REFERENCES `reservas_motorista_app` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reserva_satisfacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_satisfacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `reserva_id` int NOT NULL,
  `respondente_user_id` bigint unsigned DEFAULT NULL,
  `satisfacao_geral` tinyint unsigned NOT NULL,
  `facilidade_reserva` tinyint unsigned NOT NULL,
  `nps_nota` tinyint unsigned NOT NULL,
  `comentario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reserva_satisfacoes_reserva_id_unique` (`reserva_id`),
  KEY `reserva_satisfacoes_empresa_id_index` (`empresa_id`),
  KEY `reserva_satisfacoes_respondente_user_id_index` (`respondente_user_id`),
  CONSTRAINT `reserva_satisfacoes_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reserva_satisfacoes_reserva_id_foreign` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reserva_satisfacoes_respondente_user_id_foreign` FOREIGN KEY (`respondente_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `criado_por_user_id` bigint unsigned DEFAULT NULL,
  `origem_cadastro` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'colaborador',
  `veiculo_id` int DEFAULT NULL,
  `modelo_id` int DEFAULT NULL,
  `estacao_retirada_id` int DEFAULT NULL,
  `estacao_devolucao_id` int DEFAULT NULL,
  `grupo_frota_id` int DEFAULT NULL,
  `pagamento_efetuado` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `tarifa_base` decimal(10,2) NOT NULL,
  `quantidade_dias` int NOT NULL,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `status_reserva_id` int DEFAULT '1',
  `valor_total_tarifa_base` decimal(10,2) DEFAULT '0.00',
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `cupom_desconto_id` int DEFAULT NULL,
  `percentual_desconto` decimal(10,2) DEFAULT '0.00',
  `valor_total_com_desconto` decimal(10,2) DEFAULT '0.00',
  `valor_opcionais` decimal(10,2) DEFAULT '0.00',
  `valor_total_final` decimal(10,2) DEFAULT '0.00',
  `taxa_coleta` decimal(10,2) DEFAULT NULL COMMENT 'Taxa fora do expediente aplicada na coleta',
  `taxa_entrega` decimal(10,2) DEFAULT NULL COMMENT 'Taxa fora do expediente aplicada na entrega',
  `cobranca_efetuada_pessoalmente_em` datetime DEFAULT NULL,
  `cobranca_efetuada_pessoalmente_por_user_id` bigint unsigned DEFAULT NULL,
  `cobranca_efetuada_pessoalmente_justificativa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revisao_checkout_concluida` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `revisao_checkout_concluida_em` timestamp NULL DEFAULT NULL,
  `data_cancelamento` datetime DEFAULT NULL,
  `checkin_realizado_em` timestamp NULL DEFAULT NULL,
  `checkin_assinado_em` timestamp NULL DEFAULT NULL,
  `calcao_registrado_em` timestamp NULL DEFAULT NULL,
  `checkout_realizado_em` timestamp NULL DEFAULT NULL,
  `checkout_assinado_em` timestamp NULL DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estacao_retirada_id` (`estacao_retirada_id`),
  KEY `estacao_devolucao_id` (`estacao_devolucao_id`),
  KEY `cupom_desconto_id` (`cupom_desconto_id`),
  KEY `modelo_id` (`modelo_id`),
  KEY `reservas_ibfk_6` (`veiculo_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status_reserva_id`),
  KEY `grupo_frota_id` (`grupo_frota_id`),
  KEY `valor_desconto` (`percentual_desconto`),
  KEY `valor_opcionais` (`valor_opcionais`),
  KEY `valor_total` (`valor_total_final`),
  KEY `valor_total_por_dias` (`valor_total_tarifa_base`),
  KEY `valor_total_com_desconto` (`valor_total_com_desconto`),
  KEY `valor_total_final` (`valor_total_final`),
  KEY `reservas_empresa_id_index` (`empresa_id`),
  KEY `reservas_criado_por_user_id_index` (`criado_por_user_id`),
  KEY `reservas_origem_cadastro_index` (`origem_cadastro`),
  KEY `reservas_empresa_status_data_index` (`empresa_id`,`status_reserva_id`,`data_inicio`,`data_fim`),
  KEY `reservas_veiculo_status_data_index` (`veiculo_id`,`status_reserva_id`,`data_inicio`,`data_fim`),
  KEY `reservas_status_data_index` (`status_reserva_id`,`data_inicio`,`data_fim`),
  KEY `reservas_created_at_index` (`created_at`),
  KEY `reservas_empresa_created_at_index` (`empresa_id`,`created_at`),
  KEY `reservas_pagamento_efetuado_index` (`pagamento_efetuado`),
  CONSTRAINT `reservas_criado_por_user_id_foreign` FOREIGN KEY (`criado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_ibfk_10` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`),
  CONSTRAINT `reservas_ibfk_11` FOREIGN KEY (`status_reserva_id`) REFERENCES `status_reserva` (`id`),
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`estacao_retirada_id`) REFERENCES `estacoes` (`id`),
  CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`estacao_devolucao_id`) REFERENCES `estacoes` (`id`),
  CONSTRAINT `reservas_ibfk_4` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`),
  CONSTRAINT `reservas_ibfk_6` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`),
  CONSTRAINT `reservas_ibfk_7` FOREIGN KEY (`cupom_desconto_id`) REFERENCES `cupom_desconto` (`id`),
  CONSTRAINT `reservas_ibfk_8` FOREIGN KEY (`status_reserva_id`) REFERENCES `status_reserva` (`id`),
  CONSTRAINT `reservas_ibfk_9` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservas_motorista_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservas_motorista_app` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `motorista_app_solicitacao_id` bigint unsigned DEFAULT NULL,
  `plano_motorista_app_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` bigint unsigned DEFAULT NULL,
  `estacao_retirada_id` int unsigned DEFAULT NULL,
  `status` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente_pagamento',
  `status_pagamento` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `pagamento_efetuado` tinyint(1) NOT NULL DEFAULT '0',
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `data_retirada_real` datetime DEFAULT NULL,
  `contrato_efetivado_em` datetime DEFAULT NULL,
  `checkin_realizado_em` datetime DEFAULT NULL,
  `checkout_realizado_em` datetime DEFAULT NULL,
  `quantidade_dias` int unsigned DEFAULT NULL,
  `quilometragem_inicial` int unsigned DEFAULT NULL,
  `nivel_combustivel_inicial` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quilometragem_final` int unsigned DEFAULT NULL,
  `nivel_combustivel_final` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_plano` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_periodo_contrato` decimal(10,2) NOT NULL DEFAULT '0.00',
  `periodo_contrato` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contrato_snapshot_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `contrato_pdf_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_opcionais` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_caucao` decimal(10,2) DEFAULT NULL,
  `valor_total_final` decimal(10,2) NOT NULL DEFAULT '0.00',
  `opcionais_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `dados_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `documentos_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reservas_motorista_app_user_id_foreign` (`user_id`),
  KEY `reservas_motorista_app_motorista_app_solicitacao_id_foreign` (`motorista_app_solicitacao_id`),
  KEY `reservas_motorista_app_plano_motorista_app_id_foreign` (`plano_motorista_app_id`),
  KEY `reserva_motorista_app_empresa_user_status_idx` (`empresa_id`,`user_id`,`status`),
  KEY `reserva_motorista_app_empresa_pagamento_idx` (`empresa_id`,`pagamento_efetuado`),
  KEY `reserva_motorista_app_empresa_created_idx` (`empresa_id`,`created_at`),
  KEY `reservas_motorista_app_veiculo_id_index` (`veiculo_id`),
  CONSTRAINT `reservas_motorista_app_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_motorista_app_motorista_app_solicitacao_id_foreign` FOREIGN KEY (`motorista_app_solicitacao_id`) REFERENCES `motorista_app_solicitacoes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_motorista_app_plano_motorista_app_id_foreign` FOREIGN KEY (`plano_motorista_app_id`) REFERENCES `planos_motorista_app` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservas_motorista_app_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reservas_opcionais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservas_opcionais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reserva_id` int NOT NULL,
  `opcional_id` int NOT NULL,
  `quantidade` int DEFAULT '1',
  `preco_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `preco_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `opcional_id` (`opcional_id`),
  CONSTRAINT `reservas_opcionais_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservas_opcionais_ibfk_2` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_unique` (`name`,`guard_name`),
  KEY `roles_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `saldo_movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saldo_movimentacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` decimal(12,2) NOT NULL,
  `saldo_anterior` decimal(12,2) NOT NULL DEFAULT '0.00',
  `saldo_atual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saldo_movimentacoes_user_id_empresa_id_index` (`user_id`,`empresa_id`),
  KEY `saldo_movimentacoes_reserva_id_index` (`reserva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `segmentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `segmentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `segmentos_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sinistro_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinistro_historico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sinistro_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `campo_alterado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_anterior` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor_novo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sinistro_historico_sinistro_id_index` (`sinistro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sinistros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinistros` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `veiculo_id` bigint unsigned DEFAULT NULL,
  `reserva_id` bigint unsigned DEFAULT NULL,
  `reserva_motorista_app_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `condutor_id` bigint unsigned DEFAULT NULL,
  `responsavel_id` bigint unsigned DEFAULT NULL,
  `tipo_sinistro` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'colisao',
  `data_hora_ocorrido` datetime DEFAULT NULL,
  `local_ocorrido` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `placa_momento` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_bo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orgao_bo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terceiros_envolvidos` tinyint(1) NOT NULL DEFAULT '0',
  `terceiro_nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terceiro_telefone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terceiro_placa` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vitimas` tinyint(1) NOT NULL DEFAULT '0',
  `seguradora` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_apolice` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_sinistro_seguradora` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `franquia` decimal(10,2) DEFAULT NULL,
  `valor_prejuizo_estimado` decimal(10,2) DEFAULT NULL,
  `valor_aprovado_seguradora` decimal(10,2) DEFAULT NULL,
  `valor_responsabilidade_cliente` decimal(10,2) DEFAULT NULL,
  `destino_custo` enum('empresa','cliente','ambos') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'empresa',
  `valor_custo_empresa` decimal(10,2) DEFAULT NULL,
  `forma_pagamento_empresa` enum('dinheiro','cartao_credito','cartao_debito','pix','boleto','transferencia') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Forma de pagamento do custo absorvido pela empresa',
  `pago_empresa_em` timestamp NULL DEFAULT NULL,
  `pago_empresa_por_user_id` bigint unsigned DEFAULT NULL,
  `referencia_pagamento_empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forma_pagamento_cliente` enum('dinheiro','cartao_credito','cartao_debito','pix','boleto') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Forma de pagamento do custo cobrado do cliente',
  `pago_cliente_em` timestamp NULL DEFAULT NULL,
  `pago_cliente_por_user_id` bigint unsigned DEFAULT NULL,
  `referencia_pagamento_cliente` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `anexo_bo_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo_foto1_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo_foto2_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo_foto3_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anexo_laudo_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sinistros_empresa_id_index` (`empresa_id`),
  KEY `sinistros_veiculo_id_index` (`veiculo_id`),
  KEY `sinistros_reserva_id_index` (`reserva_id`),
  KEY `sinistros_cliente_id_index` (`cliente_id`),
  KEY `sinistros_pago_empresa_por_user_id_foreign` (`pago_empresa_por_user_id`),
  KEY `sinistros_pago_cliente_por_user_id_foreign` (`pago_cliente_por_user_id`),
  KEY `sinistros_reserva_motorista_app_id_index` (`reserva_motorista_app_id`),
  CONSTRAINT `sinistros_pago_cliente_por_user_id_foreign` FOREIGN KEY (`pago_cliente_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sinistros_pago_empresa_por_user_id_foreign` FOREIGN KEY (`pago_empresa_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_item_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_item_checklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_movimentacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_movimentacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_movimentacaos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_movimentacaos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_reserva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_reserva` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `cor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_reservas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status_veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_veiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cor` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'secondary',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscription_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint unsigned NOT NULL,
  `stripe_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_product` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_items_stripe_id_unique` (`stripe_id`),
  KEY `subscription_items_subscription_id_stripe_price_index` (`subscription_id`,`stripe_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_stripe_id_unique` (`stripe_id`),
  KEY `subscriptions_user_id_stripe_status_index` (`user_id`,`stripe_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tempo_entre_entacaos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tempo_entre_entacaos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tempo_entre_estacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tempo_entre_estacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estacao_origem_id` int NOT NULL,
  `estacao_destino_id` int NOT NULL,
  `tempo_deslocamento` time NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_estacao_origem` (`estacao_origem_id`),
  KEY `fk_estacao_destino` (`estacao_destino_id`),
  KEY `tempo_entre_estacoes_empresa_id_index` (`empresa_id`),
  CONSTRAINT `fk_estacao_destino` FOREIGN KEY (`estacao_destino_id`) REFERENCES `estacoes` (`id`),
  CONSTRAINT `fk_estacao_origem` FOREIGN KEY (`estacao_origem_id`) REFERENCES `estacoes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `termo_consentimento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termo_consentimento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordem` int NOT NULL DEFAULT '0',
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `termo_consentimento_empresa_id_index` (`empresa_id`),
  KEY `termo_consentimento_tipo_index` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `termo_consentimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termo_consentimentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tiop_valor_caracteristic_a_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiop_valor_caracteristic_a_frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_checklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_checklist_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_frota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_frota_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_movimentacaos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_movimentacaos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_opcional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_opcional` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_opcional_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_opcionals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_opcionals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_transacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_transacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'web',
  `ativo` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `empresa_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_usuarios_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_valor_caracteristica_frota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_valor_caracteristica_frota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_valor_caracteristica_frota_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_valor_caracteristica_frotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_valor_caracteristica_frotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipos_movimentacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_movimentacao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tour_realizacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tour_realizacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `tour_tela_id` bigint unsigned NOT NULL,
  `feito` tinyint(1) NOT NULL DEFAULT '0',
  `feito_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tour_realizacoes_user_id_tour_tela_id_unique` (`user_id`,`tour_tela_id`),
  KEY `tour_realizacoes_tour_tela_id_foreign` (`tour_tela_id`),
  CONSTRAINT `tour_realizacoes_tour_tela_id_foreign` FOREIGN KEY (`tour_tela_id`) REFERENCES `tour_telas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tour_realizacoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tour_telas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tour_telas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chave` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_exibicao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tour_telas_chave_unique` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `pago_por_user_id` bigint unsigned DEFAULT NULL,
  `reserva_id` int unsigned DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `valor_bruto` decimal(15,2) DEFAULT NULL,
  `valor_ibs` decimal(15,2) DEFAULT NULL,
  `valor_cbs` decimal(15,2) DEFAULT NULL,
  `valor_retido` decimal(15,2) DEFAULT NULL,
  `valor_liquido` decimal(15,2) DEFAULT NULL,
  `metodo_pagamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metodo_pagamento_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pagamento_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moeda` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_pagamento` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmado_em` timestamp NULL DEFAULT NULL,
  `confirmado_por_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `transacoes_empresa_id_index` (`empresa_id`),
  KEY `transacoes_confirmado_por_user_id_foreign` (`confirmado_por_user_id`),
  CONSTRAINT `transacoes_confirmado_por_user_id_foreign` FOREIGN KEY (`confirmado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transacoes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tributos_municipais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tributos_municipais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `municipio_codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_servico` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `aliquota_iss` decimal(5,2) NOT NULL,
  `tributacao_issqn` tinyint unsigned DEFAULT NULL,
  `inscricao_municipal` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnae` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao_servico` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tributos_municipais_emp_mun_serv_unique` (`empresa_id`,`municipio_codigo`,`codigo_servico`),
  KEY `tributos_municipais_emp_mun_index` (`empresa_id`,`municipio_codigo`),
  CONSTRAINT `tributos_municipais_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tutoriais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tutoriais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tutorial_categoria_id` bigint unsigned DEFAULT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capa_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem` int unsigned NOT NULL DEFAULT '0',
  `visibilidade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'todos',
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tutoriais_tutorial_categoria_id_foreign` (`tutorial_categoria_id`),
  KEY `tutoriais_ativo_tutorial_categoria_id_index` (`ativo`,`tutorial_categoria_id`),
  CONSTRAINT `tutoriais_tutorial_categoria_id_foreign` FOREIGN KEY (`tutorial_categoria_id`) REFERENCES `tutorial_categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tutorial_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tutorial_categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int unsigned NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tutorial_categorias_nome_unique` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `tipo_usuario_id` bigint DEFAULT '2',
  `role_id` bigint DEFAULT NULL,
  `cargo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `funcao_id` bigint unsigned DEFAULT NULL,
  `custo_hora` decimal(10,2) DEFAULT NULL,
  `jornada_diaria` decimal(4,1) NOT NULL DEFAULT '8.0',
  `fator_esforco` decimal(4,2) NOT NULL DEFAULT '1.00',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `sexo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_identificacao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais_sigla` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `local_nascimento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_last_four` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `numero_carta_conducao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_emissao_carta_conducao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais_emissao_carta_conducao` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_emissao_carta_conducao` date DEFAULT NULL,
  `data_validade_carta_conducao` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `acesso_todas_estacoes` tinyint(1) NOT NULL DEFAULT '1',
  `forcar_troca_senha` tinyint(1) NOT NULL DEFAULT '0',
  `foto_perfil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saldo_atual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_empresa_email_unique` (`empresa_id`,`email`),
  UNIQUE KEY `users_email_empresa_id_unique` (`email`,`empresa_id`),
  KEY `users_stripe_id_index` (`stripe_id`),
  KEY `users_empresa_id_index` (`empresa_id`),
  KEY `users_funcao_id_index` (`funcao_id`),
  CONSTRAINT `users_funcao_id_foreign` FOREIGN KEY (`funcao_id`) REFERENCES `funcoes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `veiculo_danos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veiculo_danos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria_id` int NOT NULL,
  `localizacao_problema` enum('Interior','Exterior') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dano_id` int NOT NULL,
  `tipo_dano` enum('ausente','amolgadela_leve','amolgadela_grave','risco_leve','risco_grave') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_dano` enum('direita','esquerda','frente','atras','cima') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `custo_estimado` decimal(10,2) DEFAULT '0.00',
  `quilometragem_veiculo` int DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `veiculo_id` int NOT NULL,
  `reserva_codigo_id` bigint unsigned DEFAULT NULL,
  `reserva_id` int DEFAULT NULL,
  `contrato_id` bigint unsigned DEFAULT NULL,
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `status_revisao` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_reparo` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `decisao_revisada_em` datetime DEFAULT NULL,
  `decisao_revisada_por` bigint unsigned DEFAULT NULL,
  `justificativa_decisao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nao_requer_reparo_em` datetime DEFAULT NULL,
  `nao_requer_reparo_por` bigint unsigned DEFAULT NULL,
  `nao_requer_reparo_justificativa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordem_servico_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `correcao_descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `correcao_data` timestamp NULL DEFAULT NULL,
  `correcao_user_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `veiculo_id` (`veiculo_id`),
  KEY `reserva_id` (`reserva_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `tipo_dano` (`tipo_dano`),
  KEY `local_dano` (`local_dano`),
  KEY `veiculo_danos_empresa_id_index` (`empresa_id`),
  KEY `veiculo_danos_reserva_codigo_id_idx` (`reserva_codigo_id`),
  CONSTRAINT `veiculo_danos_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`),
  CONSTRAINT `veiculo_danos_reserva_codigo_id_foreign` FOREIGN KEY (`reserva_codigo_id`) REFERENCES `reserva_codigos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `veiculo_danos_imagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veiculo_danos_imagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dano_veiculo_id` int NOT NULL,
  `caminho_imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dano_veiculo_id` (`dano_veiculo_id`),
  KEY `veiculo_danos_imagens_empresa_id_index` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `veiculos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veiculos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_frota_id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `marca_id` int NOT NULL,
  `modelo_id` int NOT NULL,
  `status_veiculo_id` int NOT NULL,
  `ano_fabricacao` int NOT NULL,
  `placa_licenca` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chassi` varchar(17) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `renavam` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_placa_licenca` date DEFAULT NULL,
  `data_compra` date DEFAULT NULL,
  `datainc` datetime DEFAULT NULL,
  `usuinc` bigint unsigned DEFAULT NULL,
  `datatu` datetime DEFAULT NULL,
  `usuatu` bigint unsigned DEFAULT NULL,
  `valor_compra` decimal(12,2) DEFAULT NULL,
  `valor_venda` decimal(12,2) DEFAULT NULL,
  `valor_fipe` decimal(12,2) DEFAULT NULL,
  `codigo_fipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia_fipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_consulta_fipe` date DEFAULT NULL,
  `taxa_depreciacao_anual` decimal(5,2) DEFAULT '15.00',
  `data_validade` date DEFAULT NULL,
  `kms` int DEFAULT NULL,
  `limite_kms` int DEFAULT NULL,
  `numero_motor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendido` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `disponivel` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `data_vendido` date DEFAULT NULL,
  `proxima_revisao_kms` int DEFAULT NULL,
  `proxima_manutencao_kms` int DEFAULT NULL,
  `data_proxima_manutencao` date DEFAULT NULL,
  `data_proxima_inspecao` date DEFAULT NULL,
  `data_hora_disponibilidade` timestamp NULL DEFAULT NULL,
  `local_disponibilidade` int DEFAULT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `classificacao_fiscal_id` bigint unsigned DEFAULT NULL,
  `documento_fiscal_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_frota_id` (`tipo_frota_id`),
  KEY `grupo_frota_id` (`grupo_frota_id`),
  KEY `marca_id` (`marca_id`),
  KEY `modelo_id` (`modelo_id`),
  KEY `status_veiculo_id` (`status_veiculo_id`),
  KEY `disponivel` (`disponivel`),
  KEY `veiculos_empresa_id_index` (`empresa_id`),
  KEY `local_disponibilidade` (`local_disponibilidade`),
  KEY `veiculos_classificacao_fiscal_id_foreign` (`classificacao_fiscal_id`),
  KEY `veiculos_documento_fiscal_id_foreign` (`documento_fiscal_id`),
  CONSTRAINT `veiculos_classificacao_fiscal_id_foreign` FOREIGN KEY (`classificacao_fiscal_id`) REFERENCES `classificacoes_fiscais` (`id`) ON DELETE SET NULL,
  CONSTRAINT `veiculos_documento_fiscal_id_foreign` FOREIGN KEY (`documento_fiscal_id`) REFERENCES `documentos_fiscais` (`id`) ON DELETE SET NULL,
  CONSTRAINT `veiculos_ibfk_1` FOREIGN KEY (`local_disponibilidade`) REFERENCES `estacoes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2024_08_02_005622_create_customers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_08_02_005640_create_cards_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_08_02_005727_create_driving_licences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_08_02_010227_create_consents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_08_02_010234_create_customers_cards_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_08_02_010244_create_customers_driving_licences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_08_02_010253_create_customers_concents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_08_02_010302_create_fleets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2024_08_02_010314_create_feature_fleets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_08_02_010329_create_fleet_features_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_08_02_010336_create_groups_fleets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2024_08_02_010346_create_feature_group_fleets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2024_08_02_010351_create_group_features_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2024_08_02_010358_create_bookings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2024_08_05_141940_create_pais_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2024_08_05_141950_create_paises_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2024_08_05_143457_create_asds_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2024_08_05_143823_create_ddsas_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2024_08_05_144034_create_tipo_transacaos_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2024_08_08_104519_create_permission_tables',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2024_08_08_205144_create_usuarios_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2024_08_10_180146_create_pais_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2024_08_12_215317_create_estacaos_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2024_12_05_023129_create_customer_columns',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2024_12_05_023130_create_subscriptions_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2024_12_05_023131_create_subscription_items_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2024_12_05_023629_create_payments_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_04_01_142800_add_date_columns_to_movimentacoes_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_04_01_150000_create_tenants_and_add_tenant_id_to_core_tables',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_04_01_160000_add_empresa_columns_to_tenants_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2024_08_12_224622_create_dia_utils_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2024_08_12_232143_create_horario_diarios_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2024_08_13_105446_create_tts_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2024_08_13_110830_create_ttts_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2024_08_16_043041_create_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2024_08_16_043052_create_caracteristica_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2024_08_16_043113_create_frota_caracteristicas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2024_08_16_043128_create_grupo_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2024_08_16_043142_create_caracteristica_grupo_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2024_08_16_043152_create_caracteristica_grupos_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2024_08_17_120837_create_tipo_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2024_08_17_123603_create_tiop_valor_caracteristic_a_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2024_08_17_123620_create_tipo_valor_caracteristica_frotas_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2024_08_20_075159_create_segmentos_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_04_01_174000_rename_tenant_id_to_empresa_id_in_database',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_04_01_175000_create_tipo_usuarios_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_04_01_200000_ensure_role_id_on_users_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_04_01_210000_add_foto_perfil_to_users_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_04_01_235000_add_branding_to_empresas_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_04_01_201000_add_estacao_scope_to_users',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_04_02_021000_add_forcar_troca_senha_to_users_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2024_08_20_075210_create_marcas_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2024_08_20_075217_create_modelos_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2024_08_21_042629_create_termo_consentimentos_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2024_08_21_102112_create_veiculos_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2024_08_21_103520_create_status_veiculos_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2024_08_22_235958_create_movimentacaos_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2024_08_23_003933_create_reservas_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2024_08_23_004515_create_tempo_entre_entacaos_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2024_08_23_100954_create_opcionals_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2024_08_23_101112_create_tipo_opcionals_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2024_08_23_101124_create_opcional_grupo_veiculos_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2024_08_25_010831_create_cupom_descontos_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2024_08_30_104403_create_tipo_movimentacaos_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2024_08_30_105927_create_status_movimentacaos_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2024_09_03_034659_create_status_reservas_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2024_09_09_024617_create_disponibilidade_inicial_veiculos_table',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_04_03_210000_add_imagem_to_caracteristicas_frotas_table',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_04_04_200000_add_reserva_escolher_empresa_to_empresas_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_04_04_210000_add_reserva_selecionar_modelo_to_empresas_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_04_04_220600_add_imagens_to_grupos_frota_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_04_04_220500_add_imagem_to_modelos_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_04_04_231000_change_stripe_secret_to_text_in_empresas_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_04_04_234000_create_nfse_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_04_04_233000_add_nfse_config_to_empresas_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_04_04_232000_add_pago_por_user_id_to_transacoes_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_04_03_100000_add_site_apresentacao_columns_to_empresas_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_04_03_101000_add_site_apresentacao_modelo_to_empresas_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_04_03_102000_add_site_apresentacao_content_to_empresas_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_04_03_103000_add_site_apresentacao_customization_columns_to_empresas_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2024_09_11_235842_create_status_reservas_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2024_09_14_162652_update_foreign_keys_on_grupo_frota_caracteristica_frota',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2026_04_06_120000_create_sessions_table_if_missing',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2026_04_04_240200_add_ultima_consulta_em_to_nfse_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2026_04_04_240300_add_nfse_import_permissions',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2026_04_04_240400_ensure_payment_metadata_on_transacoes_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2026_04_04_241000_add_nfse_ultima_importacao_em_to_empresas_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_04_05_110000_add_reserva_atraso_max_minutos_to_empresas_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_04_05_111000_add_saldo_to_users_and_create_saldo_movimentacoes_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_04_05_120000_add_correcao_fields_to_veiculo_danos_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_04_05_120001_create_contrato_checkout_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_04_05_180000_create_ordens_servico_tables',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2024_09_24_181449_create_checklists_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2024_09_24_182311_create_item_checklists_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2024_09_24_182319_create_status_item_checklists_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2024_09_24_182332_create_tipo_checklists_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2024_11_30_210440_create_transacoes',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2024_12_01_185726_create_configuracao_checklists_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_01_25_141605_create_categoria_danos_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_01_25_141610_create_danos_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_04_01_170000_rename_tenant_id_to_empresa_id_in_all_tables',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_04_01_171000_add_empresa_id_to_security_and_empresa_tables',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_04_01_172000_make_roles_and_permissions_unique_per_empresa',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_04_01_173000_ensure_empresa_id_as_tenant_on_used_tables',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_04_01_230000_ensure_default_empresa_template_exists',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_04_01_232000_add_gateway_pagamento_to_empresas_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_04_01_233000_add_gateway_credentials_to_empresas_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_04_01_234000_add_payment_metadata_to_transacoes_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2026_04_07_090000_add_checkin_photos_to_contrato_checkin_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2026_04_07_120000_create_ordem_servico_anexos_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2026_04_07_130000_add_ordem_to_grupos_frota_table',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2026_04_07_131000_add_upgrade_fields_to_contrato_checkin_table',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2026_04_07_132000_create_multas_table',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2026_04_07_133000_create_configuracoes_calcao_table',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2026_04_07_134000_add_new_permissions',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2026_04_04_210000_add_reserva_escolher_modelo_to_empresas_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2026_04_04_230000_add_reserva_index_permission_to_clientes',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2026_04_04_235000_add_nfse_permissions',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2026_04_07_000001_create_avaria_cobrancas_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2026_04_07_120000_add_km_fields_to_movimentacoes_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2026_04_07_150000_add_reserva_bloqueio_pos_reserva_horas_to_empresas_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2026_04_07_180000_create_sinistros_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2026_04_07_200000_add_descricao_to_status_movimentacao',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2026_04_07_210000_create_movimentacoes_historico_table',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2026_04_08_000000_add_ativo_to_movimentacoes_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2026_04_08_000001_add_ativo_to_movimentacoes_table',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2026_04_08_000002_add_ativo_to_movimentacoes_table',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2026_04_07_230000_add_acao_nivel_to_reserva_logs',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2026_04_08_100000_add_empresa_id_to_horario_diarios_table',99);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2026_04_08_110000_make_permissions_global',100);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2026_04_08_120000_create_feriados_table',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2026_04_08_150000_seed_standard_horarios_diarios',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2026_04_08_160000_seed_dias_uteis',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2026_04_08_170000_seed_status_reserva',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2026_04_08_180000_add_confirmacao_to_transacoes_table',101);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2026_04_08_190000_add_funciona_24h_to_horarios_table',102);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2026_04_08_220000_add_custo_fields_to_sinistros_table',103);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2026_04_08_230000_add_pagamento_fields_to_multas_sinistros',103);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2026_04_09_090000_make_roles_and_permissions_global',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2026_04_09_110000_add_nfse_fields_to_estacoes_and_create_tributos_municipais',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2026_04_09_120000_add_grupo_to_permissions_table',104);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2026_04_10_120000_add_reserva_minhas_permission_to_all_roles',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2026_04_10_150000_reset_roles_locadora',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2026_04_11_100000_add_fipe_depreciacao_to_veiculos_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2026_04_11_130000_sanitize_marcas_modelos_fipe',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2026_04_11_150000_add_fipe_sync_flags_and_empresa_id_to_marcas_modelos_and_empresas_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2026_04_11_160000_remove_duplicate_reserva_escolher_modelo_from_empresas_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2026_04_11_170000_add_funciona_and_tem_intervalo_almoco_to_horarios_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2026_04_11_180000_create_reserva_satisfacoes_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2026_04_11_190000_create_notifications_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2026_04_11_210000_add_origem_cadastro_to_reservas_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2026_04_12_090000_shift_dias_uteis_to_zero_based',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2026_04_12_100000_add_valor_taxa_to_estacoes_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2026_04_12_110000_add_taxa_fora_expediente_to_reservas_table',105);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2026_04_12_100000_add_ano_modelo_to_modelos_table',106);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2026_04_17_120000_remove_ano_modelo_from_veiculos_table',107);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2026_04_23_100000_create_planos_table',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2026_04_23_110000_add_gerenciar_planos_permission',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2026_04_24_005646_fix_metodo_cobranca_enum_in_empresa_planos',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2026_04_24_010015_add_pix_chave_to_empresa_plano_pagamentos',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2026_04_24_060550_add_cartao_fields_to_assinatura_tables',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2026_04_24_100000_create_onboarding_tables',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (198,'2026_04_24_164839_add_dinheiro_to_pagamento_metodo_enum',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (199,'2026_04_24_170337_add_stripe_pix_fields_to_pagamentos',108);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (200,'2026_04_24_172032_add_gateway_customer_id_to_empresas',109);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (201,'2026_04_24_180615_create_empresa_alertas_table',110);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (202,'2026_04_24_210000_add_mudanca_agendada_to_empresa_planos',111);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (203,'2026_04_25_000001_update_plano_gestao_limite_veiculos',112);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (204,'2026_04_25_000002_update_plano_crescimento_limite_veiculos',113);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (205,'2026_04_25_000003_add_limite_usuarios_to_planos',113);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (206,'2026_04_25_120000_add_access_policy_to_planos',114);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (207,'2026_04_27_000001_create_tour_telas_table',115);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (208,'2026_04_27_000002_create_tour_realizacoes_table',115);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (209,'2026_04_27_120000_update_horarios_diarios_empresa_1_to_portuguese',116);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (210,'2026_04_30_000001_make_status_tables_global',117);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (211,'2026_05_01_100000_change_users_email_unique_to_email_empresa_id',118);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (212,'2026_05_01_120000_add_cor_and_ativo_to_status_veiculos_table',118);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (213,'2026_05_01_130000_add_checkout_photos_to_contrato_checkout_table',118);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (214,'2026_05_04_100000_drop_estacaos_table',119);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (215,'2026_05_05_100000_add_contrato_id_to_veiculo_danos_table',120);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (216,'2026_05_06_100000_add_ordem_titulo_tipo_to_termo_consentimento_table',121);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (217,'2026_05_07_100000_add_pdf_path_to_contrato_checkin_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (218,'2026_05_07_110000_add_limite_armazenamento_mb_to_planos_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (219,'2026_05_08_100000_add_indexes_to_reservas_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (220,'2026_05_08_110000_create_reserva_codigos_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (221,'2026_07_11_000001_add_aluguel_app_ativo_to_empresas_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (222,'2026_07_11_120000_add_vehicle_type_to_marcas_and_modelos_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (223,'2026_07_15_000001_create_planos_motorista_app_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (224,'2026_07_15_000002_add_motorista_app_fields_to_empresas_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (225,'2026_07_15_000002_create_plano_motorista_app_opcionais_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (226,'2026_07_16_000001_add_motorista_app_custom_fields_and_solicitacoes_table',122);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (227,'2026_07_17_000001_create_reservas_motorista_app_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (228,'2026_07_17_120000_create_reserva_codigos_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (229,'2026_07_17_153000_create_reserva_motorista_app_pagamentos_and_links',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (230,'2026_07_17_170000_add_operacao_fields_to_reservas_motorista_app_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (231,'2026_07_19_230000_add_valor_caucao_to_reservas_motorista_app_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (232,'2026_07_21_100911_add_codigo_to_veiculo_danos_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (233,'2026_07_21_105146_add_reserva_codigo_id_to_avaria_cobrancas_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (234,'2026_07_21_110240_update_avaria_cobrancas_for_partial_payments',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (235,'2026_07_21_110514_replace_reserva_id_with_reserva_codigo_id_in_avaria_cobrancas',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (236,'2026_07_22_080000_add_site_tipo_reserva_to_empresas_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (237,'2026_07_22_102214_add_assinatura_to_contrato_checkout_table',123);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (238,'2026_07_22_120000_create_categorias_financeiras_table',124);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (239,'2026_07_22_121000_create_lancamentos_financeiros_table',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (240,'2026_07_22_1220000_add_recurrent_fields_to_lancamentos',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (241,'2026_07_22_130000_add_finance_details_to_ordem_servico',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (242,'2026_07_22_130000_add_valores_to_ordens_servico_table',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (243,'2026_07_22_140000_add_sinistro_id_to_ordens_servico',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (244,'2026_07_22_140000_add_status_to_veiculo_danos_table',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (245,'2026_07_22_180000_create_os_categorias_tables',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (246,'2026_07_22_190000_create_preventive_maintenance_tables',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (247,'2026_07_22_191000_add_pendencia_id_to_ordem_servico_itens',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (248,'2026_07_22_200000_create_os_subcategoria_campos_tables',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (249,'2026_07_22_210000_create_plano_motorista_app_grupo_frota_table',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (250,'2026_07_22_220000_alter_tipo_in_termo_consentimento_table',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (251,'2026_07_23_080000_add_pdf_path_to_contrato_checkout_table',125);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (252,'2024_07_24_080000_create_reserva_cupom_table',126);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (253,'2026_04_08_010000_create_horarios_diarios_table',126);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (254,'2026_07_22_122000_create_categorias_financeiras_table',126);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (255,'2026_07_24_000001_create_reserva_motorista_app_aditivos_table',127);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (256,'2026_07_24_001000_add_hierarchy_to_categorias_financeiras_table',127);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (257,'2026_07_24_130000_add_fk_reserva_to_reserva_cupom_table',128);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (258,'2026_07_24_130950_create_reserva_logs_table',128);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (259,'2026_07_24_140000_create_status_reserva_table',128);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (260,'2026_07_24_133600_make_custo_estimado_nullable_on_veiculo_danos_table',129);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (261,'2026_07_24_120000_make_optional_vehicle_fields_nullable',130);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (262,'2026_07_28_051000_create_documentacao_veiculo_parcelas_table',131);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (263,'2026_07_28_120000_add_veiculo_id_to_planos_manutencao_preventiva',132);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (264,'2026_07_28_140000_create_documentacoes_veiculo_table',133);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (265,'2026_07_28_140100_add_tipo_to_documentacoes_veiculo_table',133);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (266,'2026_07_28_140500_add_documentacao_veiculo_id_to_lancamentos_financeiros',133);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (267,'2026_07_28_141500_add_data_lancamento_to_lancamentos_financeiros',133);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (268,'2026_07_28_140001_add_veiculo_id_to_planos_manutencao_preventiva',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (269,'2026_07_28_150000_add_cobranca_pessoal_to_reservas_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (270,'2026_07_29_090000_create_chamado_statuses_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (271,'2026_07_29_090100_create_chamado_categorias_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (272,'2026_07_29_090200_create_chamado_subcategorias_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (273,'2026_07_29_090300_create_chamados_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (274,'2026_07_29_090400_create_chamado_anexos_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (275,'2026_07_29_120000_make_assunto_nullable_on_chamados_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (276,'2026_07_29_120100_create_chamado_logs_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (277,'2026_07_29_150000_add_aguardando_solicitante_and_log_to_chamados_tables',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (278,'2026_07_30_120000_create_arquivos_rastreados_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (279,'2026_07_30_130000_update_calcao_categories_names_and_codes',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (280,'2026_07_30_204200_add_signature_details_to_contrato_checkin_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (281,'2026_07_31_115710_add_assinado_to_contrato_checkout_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (282,'2026_08_01_000000_separate_checkout_review_and_repair_states_in_veiculo_danos_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (283,'2026_08_01_000001_add_audit_columns_to_core_subscription_tables',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (284,'2026_08_01_000002_drop_empresa_id_from_empresa_planos',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (285,'2026_08_01_170000_make_quilometragem_and_valor_nullable_on_veiculo_danos_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (286,'2026_08_01_180000_add_revisao_checkout_concluida_to_reservas_table',134);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (287,'2026_08_01_000003_add_empresa_id_to_empresa_planos',135);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (288,'2026_08_02_000000_add_gera_disponibilidade_to_movimentacoes_table',136);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (289,'2026_08_02_000001_add_valor_to_itens_plano_table',136);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (290,'2026_08_02_000001_change_gera_disponibilidade_to_sn_on_movimentacoes_table',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (291,'2026_08_03_000000_add_audit_columns_to_documentacoes_veiculo_table',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (292,'2026_08_03_000001_add_audit_columns_to_lancamentos_financeiros_table',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (293,'2026_08_03_000002_add_audit_columns_to_documentacao_veiculo_parcelas_table',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (294,'2026_08_03_000003_add_audit_columns_to_custom_tables',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (295,'2026_08_03_000004_add_audit_columns_to_reservas_financeiro_veiculos_tables',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (296,'2026_08_03_000005_add_audit_columns_to_modules_tables',137);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (297,'2026_08_03_000006_add_audit_columns_to_chamados_module_tables',138);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (298,'2026_08_04_000001_create_empresa_contratos_table',139);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (299,'2026_08_04_150000_add_representante_to_empresas_table',140);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (300,'2026_08_04_170000_add_endereco_to_empresas_table',140);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (301,'2026_08_05_000001_add_operational_timestamps_to_reservas_table',141);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (302,'2026_08_04_000007_alterar_frazao_social_para_nullable_em_empresa_contrato',142);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (303,'2026_08_06_add_audit_column_cnpj_cliente',143);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (304,'2026_08_05_000005_add_audit_endereco_cliente',144);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (305,'2026_08_04_000009_add_audit_column_endereco_cliente',145);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (306,'2026_08_06_000001_add_adicionais_to_planos_table',146);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (307,'2026_08_07_000000_create_fiscal_module_tables',147);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (308,'2026_08_07_000001_add_regime_emissao_to_empresas',147);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (309,'2026_08_07_000002_add_fiscal_details_to_empresas_and_opcionais',147);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (310,'2026_08_07_000003_add_documento_fiscal_id_to_veiculos',148);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (311,'2026_08_08_005355_add_valor_venda_to_veiculos_table',149);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (312,'2026_08_08_100000_create_clockify_tables',150);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (313,'2026_08_08_144758_add_budget_and_planning_fields_to_clockify_projetos_table',151);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (314,'2026_08_08_145150_create_clockify_projeto_planejamentos_table',152);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (315,'2026_08_08_145738_create_clockify_projeto_cronogramas_and_update_planejamentos',153);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (316,'2026_08_08_152654_add_valor_faturado_to_projects_and_cronogramas',154);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (317,'2026_08_08_153025_add_eh_extra_to_project_plannings',155);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (318,'2026_08_08_153221_add_trabalho_junto_to_clockify_calendarios',156);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (319,'2026_08_08_163918_add_cronograma_id_to_clockify_calendarios',157);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (320,'2026_08_08_180000_add_cargo_to_users_table',158);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (321,'2026_08_08_222515_add_ativo_to_users_table',159);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (322,'2026_09_17_100000_create_funcoes_estimativas_status',160);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (323,'2026_09_18_100000_add_lideranca_to_clockify_tables',161);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (324,'2026_09_21_100000_add_codigo_e_jornada',162);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (325,'2026_09_19_100000_create_licitacoes_module',163);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (326,'2026_09_19_120000_fator_esforco_e_codigo_projeto',164);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (327,'2026_09_19_130000_create_empresa_ia_configs',165);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (328,'2026_09_19_140000_create_ia_usos',166);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (329,'2026_09_19_150000_create_ia_config_global',167);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (330,'2026_09_19_160000_add_ia_limite_tokens_empresas',168);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (331,'2026_09_19_130353_add_arquivo_path_licitacoes',169);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (332,'2026_09_19_133345_alter_arquivo_path_licitacoes',170);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (333,'2026_09_19_140141_add_decisao_fields_licitacao_analises',171);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (334,'2026_09_19_141406_create_empresa_documentos',172);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (335,'2026_09_22_100000_create_tutoriais_tables',173);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (336,'2026_09_22_110000_add_deleted_at_empresas',174);
