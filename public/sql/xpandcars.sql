-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 28, 2025 at 02:03 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xpandcars`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('admin@admin.com|127.0.0.1', 'i:2;', 1737679421),
('admin@admin.com|127.0.0.1:timer', 'i:1737679421;', 1737679421),
('admin@admin|127.0.0.1', 'i:2;', 1737679302),
('admin@admin|127.0.0.1:timer', 'i:1737679302;', 1737679302),
('erickson.rinho@hotmail.com|127.0.0.1', 'i:2;', 1737679280),
('erickson.rinho@hotmail.com|127.0.0.1:timer', 'i:1737679280;', 1737679280);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caracteristicas_frotas`
--

CREATE TABLE `caracteristicas_frotas` (
  `id` int NOT NULL,
  `codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `tipo_valor_caracteristica_frota_id` int NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caracteristicas_grupo_frota`
--

CREATE TABLE `caracteristicas_grupo_frota` (
  `id` int NOT NULL,
  `combustivel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transmissao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assentos` int DEFAULT NULL,
  `portas` int DEFAULT NULL,
  `malas` int DEFAULT NULL,
  `ar_condicionado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `potencia_hp` int DEFAULT NULL,
  `emissoes_co2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caracteristicas_tipo_frota`
--

CREATE TABLE `caracteristicas_tipo_frota` (
  `id` int NOT NULL,
  `tipo_frota_id` int NOT NULL,
  `caracteristica_frota_id` int NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `caracteristicas_tipo_frota`
--

INSERT INTO `caracteristicas_tipo_frota` (`id`, `tipo_frota_id`, `caracteristica_frota_id`, `created_at`, `updated_at`) VALUES
(3, 1, 1, '2024-08-20 05:45:51', NULL),
(4, 1, 3, '2024-08-20 05:45:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categoria_danos`
--

CREATE TABLE `categoria_danos` (
  `id` int NOT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoria_danos`
--

INSERT INTO `categoria_danos` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Carroçaria', 'S', '2025-01-25 15:16:02', NULL),
(2, 'Jantes e Pneus', 'S', '2025-01-25 15:16:02', NULL),
(3, 'Vidros', 'S', '2025-01-25 15:16:27', NULL),
(4, 'Interior', 'S', '2025-01-25 15:16:27', NULL),
(5, 'Diversos', 'S', '2025-01-25 15:16:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `checklists`
--

CREATE TABLE `checklists` (
  `id` int NOT NULL,
  `veiculo_id` int NOT NULL,
  `reserva_id` int NOT NULL,
  `user_id` int NOT NULL,
  `tipo_checklist_id` int NOT NULL,
  `nivel_combustivel` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quilometragem` int DEFAULT NULL,
  `resumo_condicao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configuracao_checklist`
--

CREATE TABLE `configuracao_checklist` (
  `id` bigint UNSIGNED NOT NULL,
  `tipo_checklist_id` bigint UNSIGNED DEFAULT NULL,
  `item_checklist_id` bigint UNSIGNED DEFAULT NULL,
  `ativo` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuracao_checklist`
--

INSERT INTO `configuracao_checklist` (`id`, `tipo_checklist_id`, `item_checklist_id`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(2, 1, 2, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(3, 1, 3, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(4, 1, 4, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(5, 1, 5, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(6, 1, 6, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(7, 1, 7, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(8, 1, 8, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(9, 1, 9, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(10, 1, 10, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(11, 1, 11, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(12, 1, 12, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(13, 1, 13, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(14, 1, 14, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(15, 1, 15, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(16, 1, 16, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(17, 1, 17, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(18, 1, 18, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(19, 1, 19, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(20, 1, 20, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(21, 1, 21, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(22, 1, 22, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(23, 1, 23, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(24, 1, 24, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(25, 1, 25, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(26, 1, 26, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(27, 1, 27, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(28, 1, 28, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(29, 1, 29, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(30, 1, 30, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(31, 1, 31, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(32, 1, 32, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(33, 1, 33, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(34, 1, 34, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(35, 1, 35, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(36, 1, 36, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(37, 1, 37, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(38, 1, 38, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(39, 1, 39, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(40, 1, 40, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(41, 1, 41, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(42, 1, 42, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(43, 1, 43, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(44, 1, 44, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(45, 1, 45, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(46, 1, 46, 'S', '2024-12-02 02:23:22', '2024-12-02 02:23:22'),
(47, 1, 1, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(48, 1, 5, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(49, 1, 9, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(50, 1, 13, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(51, 1, 17, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(52, 1, 21, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(53, 1, 25, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(54, 1, 29, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(55, 1, 33, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(56, 1, 37, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(57, 1, 41, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(58, 1, 45, 'S', '2024-12-02 02:24:11', '2024-12-02 02:24:11'),
(59, 1, 1, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(60, 1, 5, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(61, 1, 9, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(62, 1, 13, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(63, 1, 17, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(64, 1, 21, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(65, 1, 25, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(66, 1, 29, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(67, 1, 33, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(68, 1, 37, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(69, 1, 41, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(70, 1, 45, 'S', '2024-12-02 02:44:16', '2024-12-02 02:44:16'),
(71, 1, 1, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(72, 1, 5, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(73, 1, 9, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(74, 1, 13, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(75, 1, 17, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(76, 1, 21, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(77, 1, 25, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(78, 1, 29, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(79, 1, 33, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(80, 1, 37, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(81, 1, 41, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(82, 1, 45, 'S', '2024-12-02 02:44:51', '2024-12-02 02:44:51'),
(83, 1, 1, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(84, 1, 5, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(85, 1, 9, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(86, 1, 13, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(87, 1, 17, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(88, 1, 21, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(89, 1, 25, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(90, 1, 29, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(91, 1, 33, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(92, 1, 37, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(93, 1, 41, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(94, 1, 45, 'S', '2024-12-02 02:48:28', '2024-12-02 02:48:28'),
(95, 1, 1, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(96, 1, 5, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(97, 1, 9, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(98, 1, 13, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(99, 1, 17, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(100, 1, 21, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(101, 1, 25, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(102, 1, 29, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(103, 1, 33, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(104, 1, 37, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(105, 1, 41, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(106, 1, 45, 'S', '2024-12-02 02:48:40', '2024-12-02 02:48:40'),
(107, 1, 1, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(108, 1, 5, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(109, 1, 9, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(110, 1, 13, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(111, 1, 17, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(112, 1, 21, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(113, 1, 25, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(114, 1, 29, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(115, 1, 33, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(116, 1, 37, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(117, 1, 41, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(118, 1, 45, 'S', '2024-12-02 02:51:12', '2024-12-02 02:51:12'),
(119, 1, 1, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(120, 1, 5, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(121, 1, 9, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(122, 1, 13, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(123, 1, 17, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(124, 1, 21, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(125, 1, 25, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(126, 1, 29, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(127, 1, 33, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(128, 1, 37, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(129, 1, 41, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(130, 1, 45, 'S', '2024-12-02 02:55:45', '2024-12-02 02:55:45'),
(131, 1, 1, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(132, 1, 5, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(133, 1, 9, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(134, 1, 13, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(135, 1, 17, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(136, 1, 21, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(137, 1, 25, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(138, 1, 29, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(139, 1, 33, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(140, 1, 37, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(141, 1, 41, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(142, 1, 45, 'S', '2024-12-02 02:56:13', '2024-12-02 02:56:13'),
(143, 1, 1, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(144, 1, 5, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(145, 1, 9, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(146, 1, 13, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(147, 1, 17, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(148, 1, 21, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(149, 1, 25, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(150, 1, 29, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(151, 1, 33, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(152, 1, 37, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(153, 1, 41, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31'),
(154, 1, 45, 'S', '2024-12-02 02:56:31', '2024-12-02 02:56:31');

-- --------------------------------------------------------

--
-- Table structure for table `contrato_checkin`
--

CREATE TABLE `contrato_checkin` (
  `id` int NOT NULL,
  `reserva_id` int DEFAULT NULL,
  `data_saida` date DEFAULT NULL,
  `hora_saida` time DEFAULT NULL,
  `marca_id` int DEFAULT NULL,
  `modelo_id` int DEFAULT NULL,
  `assinado` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `nivel_combustivel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas_saida` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes_saida` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quilometragem_atual` decimal(10,2) DEFAULT NULL,
  `veiculo_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contrato_checkin`
--

INSERT INTO `contrato_checkin` (`id`, `reserva_id`, `data_saida`, `hora_saida`, `marca_id`, `modelo_id`, `assinado`, `nivel_combustivel`, `notas_saida`, `observacoes_saida`, `quilometragem_atual`, `veiculo_id`, `created_at`, `updated_at`) VALUES
(2, 21, '2025-01-30', '00:39:00', 3, 2, 'N', 'Reserva', NULL, NULL, '21.00', 2, '2025-01-27 06:36:49', '2025-01-27 06:36:49'),
(3, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:48:17', '2025-01-27 06:48:17'),
(4, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:48:37', '2025-01-27 06:48:37'),
(5, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:49:45', '2025-01-27 06:49:45'),
(6, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:49:51', '2025-01-27 06:49:51'),
(7, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:50:14', '2025-01-27 06:50:14'),
(8, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:50:48', '2025-01-27 06:50:48'),
(9, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:51:39', '2025-01-27 06:51:39'),
(10, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:52:10', '2025-01-27 06:52:10'),
(11, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:52:27', '2025-01-27 06:52:27'),
(12, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:52:41', '2025-01-27 06:52:41'),
(13, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:52:55', '2025-01-27 06:52:55'),
(14, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:53:12', '2025-01-27 06:53:12'),
(15, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:54:09', '2025-01-27 06:54:09'),
(16, 21, '2025-01-29', '00:52:00', 3, 2, 'N', 'Reserva', NULL, NULL, '3.00', 2, '2025-01-27 06:55:22', '2025-01-27 06:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `cupom_desconto`
--

CREATE TABLE `cupom_desconto` (
  `id` int NOT NULL,
  `nome` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cupom_desconto`
--

INSERT INTO `cupom_desconto` (`id`, `nome`, `codigo`, `valor`, `ativo`, `created_at`, `updated_at`) VALUES
(9, 'Desconto Inicial', 'XPAND10', '10', 'S', '2024-09-18 10:06:58', NULL),
(10, 'Desconto de Feriado', 'FERIADO15', '15', 'S', '2024-09-18 10:06:58', NULL),
(11, 'Desconto de Fim de Semana', 'FDS20', '20', 'S', '2024-09-18 10:06:58', NULL),
(12, 'Desconto para Clientes VIP', 'VIP25', '25', 'S', '2024-09-18 10:06:58', NULL),
(13, 'Desconto para Primeira Locação', 'PRIMEIRA30', '30', 'S', '2024-09-18 10:06:58', NULL),
(14, 'Desconto Black Friday', 'BLACK50', '50', 'S', '2024-09-18 10:06:58', NULL),
(15, 'Desconto de Aniversário', 'BIRTHDAY20', '20', 'S', '2024-09-18 10:06:58', NULL),
(16, 'Desconto de Renovação', 'RENOVA15', '15', 'S', '2024-09-18 10:06:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `danos`
--

CREATE TABLE `danos` (
  `id` int NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int DEFAULT NULL,
  `localizacao` enum('interior','exterior') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'interior',
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `danos`
--

INSERT INTO `danos` (`id`, `nome`, `categoria_id`, `localizacao`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Capot', NULL, NULL, 'S', '2025-01-25 17:30:13', '2025-01-25 15:19:34'),
(2, 'Limpeza Especial do Veiculo', NULL, 'exterior', 'S', '2025-01-25 18:22:58', '2025-01-25 16:32:40'),
(3, 'Radio', 4, 'interior', 'S', '2025-01-25 18:29:38', '2025-01-25 18:29:38');

-- --------------------------------------------------------

--
-- Table structure for table `dias_uteis`
--

CREATE TABLE `dias_uteis` (
  `id` int NOT NULL,
  `dia` int DEFAULT NULL,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dias_uteis`
--

INSERT INTO `dias_uteis` (`id`, `dia`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 2, 'Segunda', 'S', '2024-08-12 22:58:02', '2024-08-12 23:12:46'),
(3, 3, 'Terça-Feira', 'S', '2024-08-13 02:02:49', '2024-08-13 02:56:57'),
(4, 4, 'Quarta Feira', 'S', '2024-08-13 02:03:06', '2024-08-12 23:12:39'),
(5, 5, 'Quinta Feira', 'S', '2024-08-13 02:04:13', '2024-08-12 23:12:52'),
(6, 6, 'Sexta Feira', 'S', '2024-08-13 02:09:55', '2024-08-12 23:12:34'),
(8, 1, 'Domingo', 'S', '2024-08-13 02:10:32', '2024-08-12 23:12:25'),
(9, 7, 'Sábado', 'S', '2024-08-13 02:11:29', '2024-08-13 02:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `disponibilidade_inicial_veiculo`
--

CREATE TABLE `disponibilidade_inicial_veiculo` (
  `id` int NOT NULL,
  `veiculo_id` int NOT NULL,
  `estacao_id` int NOT NULL,
  `data_hora` timestamp NOT NULL,
  `efetuou` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disponibilidade_inicial_veiculo`
--

INSERT INTO `disponibilidade_inicial_veiculo` (`id`, `veiculo_id`, `estacao_id`, `data_hora`, `efetuou`, `created_at`, `updated_at`) VALUES
(1, 3, 7, '2024-09-13 04:51:00', 'N', '2024-09-13 07:51:17', '2024-09-13 07:51:17'),
(2, 4, 8, '2024-09-13 05:04:00', 'N', '2024-09-13 08:04:27', '2024-09-13 08:04:27'),
(3, 5, 7, '2024-09-13 05:05:00', 'N', '2024-09-13 08:05:36', '2024-09-13 08:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `estacaos`
--

CREATE TABLE `estacaos` (
  `id` bigint UNSIGNED NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `estacoes`
--

CREATE TABLE `estacoes` (
  `id` int NOT NULL,
  `nome` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dia_util_id` int DEFAULT NULL,
  `endereco` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_postal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pais_id` bigint UNSIGNED DEFAULT NULL,
  `latitude` text COLLATE utf8mb4_unicode_ci,
  `longitude` text COLLATE utf8mb4_unicode_ci,
  `telefone` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `celular` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `cobrar_na_coleta` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `cobrar_na_entrega` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `valido_no_intervalo` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `ativo` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `checkin` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `checkout` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estacoes`
--

INSERT INTO `estacoes` (`id`, `nome`, `dia_util_id`, `endereco`, `numero`, `cidade`, `codigo_postal`, `pais_id`, `latitude`, `longitude`, `telefone`, `celular`, `zip_code`, `email`, `observacoes`, `cobrar_na_coleta`, `cobrar_na_entrega`, `valido_no_intervalo`, `ativo`, `checkin`, `checkout`, `created_at`, `updated_at`) VALUES
(1, 'Xpand Cars', NULL, 'Rua Dr. Barata Salgueiro', '19', 'Samouco', NULL, NULL, '0.0000000', '0.0000000', '212483407', '(00) 93392-1567', '123457456', 'contacto@xpandcars.com', NULL, 'N', 'N', 'N', 'N', 'S', 'N', '2024-08-13 04:43:12', '2024-08-13 04:43:12'),
(2, 'Xpand Cars2', NULL, 'Rua Dr. Barata Salgueiro', '19', 'Samouco', NULL, NULL, '0.0000000', '0.0000000', '212483407', '(00) 93392-1567', '123457456', 'contacto@xpandcars.com', NULL, 'N', 'N', 'N', 'N', 'S', 'N', '2024-08-13 04:45:36', '2024-08-13 04:45:36'),
(5, 'Xpand Carsa', NULL, '115', '25', 'Igarassu5', NULL, 1, '95', '105', '1231235', '(12) 31312-3125', '95', 'teste@teste5', NULL, 'S', 'S', 'S', 'N', 'N', 'N', '2024-08-13 19:47:22', '2024-08-16 06:56:13'),
(6, 'Xpand Cars6', NULL, '11', '2', 'Igarassu', NULL, 18, '9', '10', '123123', '(12) 31312-3123', '9', 'teste@teste', NULL, 'N', 'N', 'N', 'S', 'S', 'S', '2024-08-13 19:49:29', '2024-08-13 19:49:29'),
(7, 'Xpand Cars7', NULL, '11', '2', 'Igarassu', NULL, 18, '9', '10', '123123', '(12) 31312-3123', '9', 'teste@teste', NULL, 'N', 'N', 'N', 'S', 'S', 'S', '2024-08-13 19:51:09', '2024-08-13 19:51:09'),
(8, 'Xpand Cars8', NULL, '11', '2', 'Igarassu', NULL, 18, '9', '10', '123123', '(12) 31312-3123', '9', 'teste@teste', NULL, 'N', 'N', 'N', 'S', 'S', 'S', '2024-08-13 19:51:32', '2024-08-13 19:51:32'),
(10, 'erick', NULL, '2', '3', '1', NULL, 17, '5', '6', '1112', '(32) 11232-1313', '4', 'sony@ss', NULL, 'S', 'N', 'S', 'N', 'S', 'N', '2024-08-16 07:05:59', '2024-08-16 07:05:59'),
(11, 'dd', NULL, 'dd4', 'd5', 'dd3', NULL, 18, 'dd7', 'dd8', 'dd1', '(81) 98692-2586', 'dd6', 'dd2@d', NULL, 'S', 'N', 'S', 'N', 'N', 'N', '2024-08-16 07:09:37', '2024-08-16 07:09:37');

-- --------------------------------------------------------

--
-- Table structure for table `estacoes_dias_uteis`
--

CREATE TABLE `estacoes_dias_uteis` (
  `estacao_id` int NOT NULL,
  `dia_util_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estacoes_dias_uteis`
--

INSERT INTO `estacoes_dias_uteis` (`estacao_id`, `dia_util_id`) VALUES
(5, 1),
(8, 1),
(11, 1),
(5, 3),
(8, 3),
(10, 3),
(11, 3),
(5, 4),
(10, 4),
(11, 4),
(5, 5),
(10, 5),
(11, 5),
(11, 6);

-- --------------------------------------------------------

--
-- Table structure for table `frotas_caracteristicas`
--

CREATE TABLE `frotas_caracteristicas` (
  `id` int NOT NULL,
  `frota_id` int DEFAULT NULL,
  `caracteristica_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grupos_caracteristicas`
--

CREATE TABLE `grupos_caracteristicas` (
  `id` int NOT NULL,
  `grupo_id` int DEFAULT NULL,
  `caracteristica_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grupos_frota`
--

CREATE TABLE `grupos_frota` (
  `id` int NOT NULL,
  `tipo_frota_id` int DEFAULT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_sipp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem_classificacao` int DEFAULT NULL,
  `imagem` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_diario` decimal(10,2) DEFAULT '0.00',
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grupos_frota`
--

INSERT INTO `grupos_frota` (`id`, `tipo_frota_id`, `codigo`, `codigo_sipp`, `nome`, `ordem_classificacao`, `imagem`, `valor_diario`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 1, 'JWAD', 'FMAD', 'Wagon/Estate Auto Unspecified Drive Diesel Air', NULL, NULL, '75.00', 'S', '2024-08-21 06:14:30', '2024-08-24 23:29:16'),
(2, 1, 'FMAD', 'FMAD', 'Monospace Auto Unspecified Drive Diesel Air', NULL, NULL, '150.00', 'S', '2024-08-21 06:39:55', '2024-08-21 06:54:27'),
(3, 1, '3422', '3', 'teste', NULL, 'github.png', '20.00', 'S', '2024-08-24 21:07:39', '2024-09-14 19:33:28');

-- --------------------------------------------------------

--
-- Table structure for table `grupo_frota_caracteristica_frota`
--

CREATE TABLE `grupo_frota_caracteristica_frota` (
  `id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `caracteristica_frota_id` int NOT NULL,
  `valor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grupo_frota_modelo`
--

CREATE TABLE `grupo_frota_modelo` (
  `id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `modelo_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grupo_frota_modelo`
--

INSERT INTO `grupo_frota_modelo` (`id`, `grupo_frota_id`, `modelo_id`, `created_at`, `updated_at`) VALUES
(3, 1, 1, '2024-08-24 20:29:16', '2024-08-24 20:29:16'),
(4, 1, 2, '2024-08-24 20:29:16', '2024-08-24 20:29:16'),
(5, 1, 3, '2024-08-24 20:29:16', '2024-08-24 20:29:16'),
(9, 2, 5, '2024-08-24 20:58:51', '2024-08-24 20:58:51'),
(10, 2, 6, '2024-08-24 20:58:51', '2024-08-24 20:58:51'),
(11, 2, 1, '2024-08-24 21:07:10', '2024-08-24 21:07:10'),
(12, 2, 2, '2024-08-24 21:07:10', '2024-08-24 21:07:10'),
(13, 2, 3, '2024-08-25 02:46:50', '2024-08-25 02:46:50'),
(14, 2, 4, '2024-08-25 02:46:50', '2024-08-25 02:46:50');

-- --------------------------------------------------------

--
-- Table structure for table `grupo_frota_opcional`
--

CREATE TABLE `grupo_frota_opcional` (
  `id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `opcional_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grupo_frota_opcional`
--

INSERT INTO `grupo_frota_opcional` (`id`, `grupo_frota_id`, `opcional_id`, `created_at`, `updated_at`) VALUES
(2, 1, 1, '2024-08-24 20:29:16', '2024-08-24 20:29:16'),
(3, 1, 4, '2024-08-24 20:29:16', '2024-08-24 20:29:16'),
(4, 1, 2, '2024-08-24 20:29:16', '2024-08-24 20:29:16'),
(5, 2, 1, '2024-08-24 20:58:51', '2024-08-24 20:58:51'),
(7, 2, 2, '2024-08-24 21:07:10', '2024-08-24 21:07:10');

-- --------------------------------------------------------

--
-- Table structure for table `horarios`
--

CREATE TABLE `horarios` (
  `id` int NOT NULL,
  `estacao_id` int DEFAULT NULL,
  `horario_diario_id` int DEFAULT NULL,
  `hora_abertura` time NOT NULL DEFAULT '00:00:00',
  `hora_fechamento` time NOT NULL DEFAULT '00:00:00',
  `hora_inicio_almoço` time NOT NULL DEFAULT '00:00:00',
  `hora_fim_almoço` time NOT NULL DEFAULT '00:00:00',
  `hora_inicio_fora_expediente` time NOT NULL DEFAULT '00:00:00',
  `hora_fim_fora_expediente` time NOT NULL DEFAULT '00:00:00',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `horarios`
--

INSERT INTO `horarios` (`id`, `estacao_id`, `horario_diario_id`, `hora_abertura`, `hora_fechamento`, `hora_inicio_almoço`, `hora_fim_almoço`, `hora_inicio_fora_expediente`, `hora_fim_fora_expediente`, `created_at`, `updated_at`) VALUES
(1, 8, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '03:00:00', '00:00:00', '2024-08-16 05:52:53', NULL),
(2, 8, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '05:00:00', '00:00:00', '2024-08-16 05:52:53', NULL),
(3, 8, 3, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '17:00:00', '00:00:00', '2024-08-16 05:52:53', NULL),
(4, 8, 4, '16:00:00', '09:00:00', '05:00:00', '10:00:00', '15:00:00', '00:00:00', '2024-08-16 05:52:53', NULL),
(11, 2, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-24 00:01:54', NULL),
(12, 2, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-24 00:01:54', NULL),
(13, 2, 3, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-24 00:01:54', NULL),
(14, 2, 4, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-24 00:01:54', NULL),
(15, 7, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 05:38:47', NULL),
(16, 7, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 05:38:47', NULL),
(17, 7, 3, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 05:38:47', NULL),
(18, 7, 4, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 05:38:47', NULL),
(35, 6, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-25 06:29:22', '2024-08-16 06:37:56'),
(36, 6, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-25 06:29:22', '2024-08-16 06:37:56'),
(37, 6, 3, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-25 06:29:22', '2024-08-16 06:37:56'),
(38, 6, 4, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-25 06:29:22', '2024-08-16 06:37:56'),
(63, 5, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '04:00:00', '00:00:00', '2024-08-16 06:56:15', '2024-08-16 06:56:13'),
(64, 5, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '12:00:00', '00:00:00', '2024-08-16 06:56:15', '2024-08-16 06:56:13'),
(65, 5, 3, '06:00:00', '00:00:00', '00:00:00', '00:00:00', '06:00:00', '00:00:00', '2024-08-16 06:56:15', '2024-08-16 06:56:13'),
(66, 5, 4, '02:00:00', '00:00:00', '00:00:00', '00:00:00', '12:00:00', '00:00:00', '2024-08-16 06:56:15', '2024-08-16 06:56:13'),
(67, 10, NULL, '02:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:05:59', '2024-08-16 07:05:59'),
(68, 10, NULL, '00:00:00', '00:02:00', '00:00:00', '00:00:00', '00:00:00', '02:00:00', '2024-08-16 07:05:59', '2024-08-16 07:05:59'),
(69, 10, NULL, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:05:59', '2024-08-16 07:05:59'),
(70, 10, NULL, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '02:00:00', '00:00:00', '2024-08-16 07:05:59', '2024-08-16 07:05:59'),
(71, 10, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:06:03', NULL),
(72, 10, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:06:03', NULL),
(73, 10, 3, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:06:03', NULL),
(74, 10, 4, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:06:03', NULL),
(75, 11, 1, '02:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:09:40', '2024-08-16 07:09:37'),
(76, 11, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:09:40', '2024-08-16 07:09:37'),
(77, 11, 3, '00:00:00', '05:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:09:40', '2024-08-16 07:09:37'),
(78, 11, 4, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-16 07:09:40', '2024-08-16 07:09:37'),
(79, 1, 1, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-23 19:27:14', NULL),
(80, 1, 2, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-23 19:27:14', NULL),
(81, 1, 3, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-23 19:27:14', NULL),
(82, 1, 4, '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '00:00:00', '2024-08-23 19:27:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `horarios_diarios`
--

CREATE TABLE `horarios_diarios` (
  `id` int NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `horarios_diarios`
--

INSERT INTO `horarios_diarios` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Week', 'S', '2024-08-13 02:49:23', '2024-08-13 02:58:40'),
(2, 'Saturday', 'S', '2024-08-13 02:52:35', '2024-08-13 02:58:34'),
(3, 'Sunday', 'S', '2024-08-13 02:57:42', '2024-08-13 02:57:42'),
(4, 'Holiday', 'S', '2024-08-13 02:57:50', '2024-08-13 02:57:50');

-- --------------------------------------------------------

--
-- Table structure for table `item_checklist`
--

CREATE TABLE `item_checklist` (
  `id` int NOT NULL,
  `checklist_id` int DEFAULT NULL,
  `item` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_checklist_id` int DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `url_foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_checklist`
--

INSERT INTO `item_checklist` (`id`, `checklist_id`, `item`, `status_checklist_id`, `observacoes`, `url_foto`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, 'Farol Esq.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(2, NULL, NULL, NULL, NULL, NULL, 'Farol Dir.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(3, NULL, NULL, NULL, NULL, NULL, 'Pisca Esq.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(4, NULL, NULL, NULL, NULL, NULL, 'Pisca Dir.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(5, NULL, NULL, NULL, NULL, NULL, 'Lanterna Esq.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(6, NULL, NULL, NULL, NULL, NULL, 'Lanterna Dir.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(7, NULL, NULL, NULL, NULL, NULL, 'Luz Freio', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(8, NULL, NULL, NULL, NULL, NULL, 'Luz Placa', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(9, NULL, NULL, NULL, NULL, NULL, 'Buzina', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(10, NULL, NULL, NULL, NULL, NULL, 'Ar condicionado', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(11, NULL, NULL, NULL, NULL, NULL, 'Retrovisor Interno', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(12, NULL, NULL, NULL, NULL, NULL, 'Retrovisor Esq.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(13, NULL, NULL, NULL, NULL, NULL, 'Retrovisor Dir.', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(14, NULL, NULL, NULL, NULL, NULL, 'Nível de Óleo Motor', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(15, NULL, NULL, NULL, NULL, NULL, 'Nível Óleo Hidráulico', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(16, NULL, NULL, NULL, NULL, NULL, 'Nível Água Parabrisa', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(17, NULL, NULL, NULL, NULL, NULL, 'Nível Fluído de Freio', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(18, NULL, NULL, NULL, NULL, NULL, 'Nível Líq. Arrefecimento', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(19, NULL, NULL, NULL, NULL, NULL, 'Limpador Parabrisa', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(20, NULL, NULL, NULL, NULL, NULL, 'Vidros Laterais', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(21, NULL, NULL, NULL, NULL, NULL, 'Parabrisa Traseiro', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(22, NULL, NULL, NULL, NULL, NULL, 'Parabrisa Dianteiro', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(23, NULL, NULL, NULL, NULL, NULL, 'Vidros Elétricos', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(24, NULL, NULL, NULL, NULL, NULL, 'Rádio', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(25, NULL, NULL, NULL, NULL, NULL, 'Estofamento', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(26, NULL, NULL, NULL, NULL, NULL, 'Bancos', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(27, NULL, NULL, NULL, NULL, NULL, 'Tapetes Internos', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(28, NULL, NULL, NULL, NULL, NULL, 'Forro Interno', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(29, NULL, NULL, NULL, NULL, NULL, 'Macaco', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(30, NULL, NULL, NULL, NULL, NULL, 'Chave de Roda', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(31, NULL, NULL, NULL, NULL, NULL, 'Estepe', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(32, NULL, NULL, NULL, NULL, NULL, 'Triângulo', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(33, NULL, NULL, NULL, NULL, NULL, 'Extintor', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(34, NULL, NULL, NULL, NULL, NULL, 'Bateria', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(35, NULL, NULL, NULL, NULL, NULL, 'Indicadores', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(36, NULL, NULL, NULL, NULL, NULL, 'Painel', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(37, NULL, NULL, NULL, NULL, NULL, 'Documento Veicular', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(38, NULL, NULL, NULL, NULL, NULL, 'Manual do Carro', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(39, NULL, NULL, NULL, NULL, NULL, 'Cartão Seguro', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(40, NULL, NULL, NULL, NULL, NULL, 'Cartão Abastecimento', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(41, NULL, NULL, NULL, NULL, NULL, 'Cartão Estacionamento', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(42, NULL, NULL, NULL, NULL, NULL, 'GPS', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(43, NULL, NULL, NULL, NULL, NULL, 'Cintos de segurança', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(44, NULL, NULL, NULL, NULL, NULL, 'Limpeza Interior', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(45, NULL, NULL, NULL, NULL, NULL, 'Limpeza Exterior', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21'),
(46, NULL, NULL, NULL, NULL, NULL, 'Chave Ignição', 'S', '2024-12-01 18:46:21', '2024-12-01 18:46:21');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `nome`, `created_at`, `updated_at`) VALUES
(1, 'Rotina de atraso das reservas confirmadas e nao retiradas', '2024-09-12 01:05:37', NULL),
(2, 'Rotina de expiração das reservas atrasadas', '2024-12-31 13:43:18', NULL),
(3, 'Rotina de expiração de reservas criadas', '2024-12-31 13:43:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `log_jobs`
--

CREATE TABLE `log_jobs` (
  `id` int NOT NULL,
  `job_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `log_jobs`
--

INSERT INTO `log_jobs` (`id`, `job_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-09-12 04:38:42', '2024-09-12 04:38:42'),
(2, 1, '2024-09-12 05:09:06', '2024-09-12 05:09:06'),
(3, 1, '2024-09-12 05:28:42', '2024-09-12 05:28:42'),
(4, 1, '2025-01-02 02:02:02', '2025-01-02 02:02:02'),
(5, 2, '2025-01-02 02:02:02', '2025-01-02 02:02:02'),
(6, 1, '2025-01-02 02:04:30', '2025-01-02 02:04:30'),
(7, 2, '2025-01-02 02:04:30', '2025-01-02 02:04:30'),
(8, 1, '2025-01-02 02:05:26', '2025-01-02 02:05:26'),
(9, 2, '2025-01-02 02:05:26', '2025-01-02 02:05:26'),
(10, 1, '2025-01-02 02:05:44', '2025-01-02 02:05:44'),
(11, 2, '2025-01-02 02:05:44', '2025-01-02 02:05:44'),
(12, 1, '2025-01-02 02:06:01', '2025-01-02 02:06:01'),
(13, 2, '2025-01-02 02:06:01', '2025-01-02 02:06:01'),
(14, 1, '2025-01-02 02:06:03', '2025-01-02 02:06:03'),
(15, 2, '2025-01-02 02:06:03', '2025-01-02 02:06:03'),
(16, 1, '2025-01-02 02:06:04', '2025-01-02 02:06:04'),
(17, 2, '2025-01-02 02:06:04', '2025-01-02 02:06:04'),
(18, 1, '2025-01-02 02:06:17', '2025-01-02 02:06:17'),
(19, 2, '2025-01-02 02:06:17', '2025-01-02 02:06:17'),
(20, 1, '2025-01-02 02:06:41', '2025-01-02 02:06:41'),
(21, 2, '2025-01-02 02:06:41', '2025-01-02 02:06:41'),
(22, 1, '2025-01-02 02:07:48', '2025-01-02 02:07:48'),
(23, 2, '2025-01-02 02:07:48', '2025-01-02 02:07:48'),
(24, 1, '2025-01-02 02:07:57', '2025-01-02 02:07:57'),
(25, 2, '2025-01-02 02:07:57', '2025-01-02 02:07:57'),
(26, 1, '2025-01-02 02:08:05', '2025-01-02 02:08:05'),
(27, 2, '2025-01-02 02:08:05', '2025-01-02 02:08:05'),
(28, 1, '2025-01-02 02:09:16', '2025-01-02 02:09:16'),
(29, 2, '2025-01-02 02:09:16', '2025-01-02 02:09:16'),
(30, 1, '2025-01-02 02:35:33', '2025-01-02 02:35:33'),
(31, 2, '2025-01-02 02:35:33', '2025-01-02 02:35:33'),
(32, 1, '2025-01-02 02:44:11', '2025-01-02 02:44:11'),
(33, 2, '2025-01-02 02:44:11', '2025-01-02 02:44:11'),
(34, 1, '2025-01-02 02:44:49', '2025-01-02 02:44:49'),
(35, 2, '2025-01-02 02:44:49', '2025-01-02 02:44:49'),
(36, 1, '2025-01-02 02:45:41', '2025-01-02 02:45:41'),
(37, 2, '2025-01-02 02:45:41', '2025-01-02 02:45:41'),
(38, 3, '2025-01-02 02:45:41', '2025-01-02 02:45:41'),
(39, 1, '2025-01-02 02:46:55', '2025-01-02 02:46:55'),
(40, 2, '2025-01-02 02:46:55', '2025-01-02 02:46:55'),
(41, 3, '2025-01-02 02:46:55', '2025-01-02 02:46:55'),
(42, 1, '2025-01-02 02:49:15', '2025-01-02 02:49:15'),
(43, 2, '2025-01-02 02:49:15', '2025-01-02 02:49:15'),
(44, 3, '2025-01-02 02:49:15', '2025-01-02 02:49:15'),
(45, 1, '2025-01-02 02:50:57', '2025-01-02 02:50:57'),
(46, 2, '2025-01-02 02:50:57', '2025-01-02 02:50:57'),
(47, 3, '2025-01-02 02:50:57', '2025-01-02 02:50:57'),
(48, 1, '2025-01-02 02:54:22', '2025-01-02 02:54:22'),
(49, 2, '2025-01-02 02:54:22', '2025-01-02 02:54:22'),
(50, 3, '2025-01-02 02:54:22', '2025-01-02 02:54:22'),
(51, 1, '2025-01-25 05:10:31', '2025-01-25 05:10:31'),
(52, 2, '2025-01-25 05:10:31', '2025-01-25 05:10:31'),
(53, 1, '2025-01-25 05:11:28', '2025-01-25 05:11:28'),
(54, 2, '2025-01-25 05:11:28', '2025-01-25 05:11:28'),
(55, 3, '2025-01-25 05:11:28', '2025-01-25 05:11:28');

-- --------------------------------------------------------

--
-- Table structure for table `marcas`
--

CREATE TABLE `marcas` (
  `id` int NOT NULL,
  `nome` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marcas`
--

INSERT INTO `marcas` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Mercedes Benz', 'S', '2024-08-21 04:31:06', '2024-08-21 04:31:06'),
(2, 'Hyundai', 'S', '2024-08-21 04:31:18', '2024-08-21 04:31:18'),
(3, 'Peugeot', 'S', '2024-08-21 04:31:28', '2024-08-21 04:31:28'),
(4, 'Ford', 'S', '2024-08-21 04:31:57', '2024-08-21 04:31:57');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '2024_08_02_005622_create_customers_table', 1),
(4, '2024_08_02_005640_create_cards_table', 1),
(5, '2024_08_02_005727_create_driving_licences_table', 1),
(6, '2024_08_02_010227_create_consents_table', 1),
(7, '2024_08_02_010234_create_customers_cards_table', 1),
(8, '2024_08_02_010244_create_customers_driving_licences_table', 1),
(9, '2024_08_02_010253_create_customers_concents_table', 1),
(10, '2024_08_02_010302_create_fleets_table', 1),
(11, '2024_08_02_010314_create_feature_fleets_table', 1),
(12, '2024_08_02_010329_create_fleet_features_table', 1),
(13, '2024_08_02_010336_create_groups_fleets_table', 1),
(14, '2024_08_02_010346_create_feature_group_fleets_table', 1),
(15, '2024_08_02_010351_create_group_features_table', 1),
(16, '2024_08_02_010358_create_bookings_table', 1),
(17, '2024_08_05_141940_create_pais_table', 2),
(18, '2024_08_05_141950_create_paises_table', 2),
(19, '2024_08_05_143457_create_asds_table', 2),
(20, '2024_08_05_143823_create_ddsas_table', 2),
(21, '2024_08_05_144034_create_tipo_transacaos_table', 2),
(22, '2024_08_08_104519_create_permission_tables', 3),
(23, '2024_08_08_205144_create_usuarios_table', 4),
(24, '2024_08_10_180146_create_pais_table', 4),
(25, '2024_08_12_215317_create_estacaos_table', 4),
(26, '2024_12_05_023129_create_customer_columns', 5),
(27, '2024_12_05_023130_create_subscriptions_table', 6),
(28, '2024_12_05_023131_create_subscription_items_table', 7),
(29, '2024_12_05_023629_create_payments_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `modelos`
--

CREATE TABLE `modelos` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `marca_id` int DEFAULT NULL,
  `segmento_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modelos`
--

INSERT INTO `modelos` (`id`, `nome`, `ativo`, `marca_id`, `segmento_id`, `created_at`, `updated_at`) VALUES
(1, '2008 Allure 1.2 Puretech 130CV EAT8', 'S', 3, 2, '2024-08-21 04:28:46', '2024-08-21 05:00:28'),
(2, '508 SW Allure 1.5 BlueHDI 130CV EAT8', 'S', 3, 1, '2024-08-21 04:29:17', '2024-08-21 05:00:43'),
(3, '5008 Allure 1.5 BlueHDI 130CV EAT8', 'S', 3, 5, '2024-08-21 04:29:27', '2024-08-21 05:01:01'),
(4, 'Tucson 1.6 CRDI ou similar', 'S', 2, 2, '2024-08-21 04:29:43', '2024-08-21 05:01:21'),
(5, 'C 300 D AMG Line Station', 'S', 1, 1, '2024-08-21 04:29:57', '2024-08-21 04:48:36'),
(6, 'Focus SW 1.0 Ecoboost MHEV ST-Line X', 'S', 4, 3, '2024-08-21 04:43:44', '2024-08-21 04:47:46');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movimentacoes`
--

CREATE TABLE `movimentacoes` (
  `id` int UNSIGNED NOT NULL,
  `veiculo_id` int DEFAULT NULL,
  `modelo_id` int DEFAULT NULL,
  `tipo_movimentacao_id` int NOT NULL,
  `local_origem_id` int DEFAULT NULL,
  `local_destino_id` int DEFAULT NULL,
  `data_registro` datetime DEFAULT NULL,
  `status_movimentacao_id` int DEFAULT NULL,
  `reserva_id` int DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ordem_servico_id` int DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movimentacoes`
--

INSERT INTO `movimentacoes` (`id`, `veiculo_id`, `modelo_id`, `tipo_movimentacao_id`, `local_origem_id`, `local_destino_id`, `data_registro`, `status_movimentacao_id`, `reserva_id`, `user_id`, `ordem_servico_id`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, 11, 7, 7, '2024-09-25 07:14:00', 3, NULL, 1, NULL, '', '2024-09-24 13:19:38', '2024-09-24 18:02:01'),
(4, 3, 5, 11, 7, 7, '2024-09-25 07:14:00', 6, NULL, 1, NULL, 'Reserva criada', '2024-09-24 18:34:37', '2024-09-24 18:34:37'),
(14, 3, 5, 11, 7, 7, '2025-01-01 15:15:00', 6, 10, 1, NULL, 'Reserva criada', '2025-01-02 02:01:11', '2025-01-02 02:01:11'),
(23, 3, 5, 11, 7, 7, '2025-01-02 00:44:00', 6, 19, 1, NULL, 'Reserva criada', '2025-01-02 03:46:40', '2025-01-02 03:46:40'),
(24, 2, NULL, 2, 6, NULL, NULL, 2, 21, 1, NULL, NULL, '2025-01-27 06:55:22', '2025-01-27 06:55:22');

-- --------------------------------------------------------

--
-- Table structure for table `opcionais`
--

CREATE TABLE `opcionais` (
  `id` int NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_cobranca` enum('por_dia','valor_integral') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_opcional_id` int NOT NULL,
  `preco_por_dia` decimal(10,2) DEFAULT NULL,
  `preco_integral` decimal(10,2) DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `opcionais`
--

INSERT INTO `opcionais` (`id`, `nome`, `tipo_cobranca`, `tipo_opcional_id`, `preco_por_dia`, `preco_integral`, `descricao`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Cadeira de Criança', 'por_dia', 1, '17.00', NULL, 'cadeira', 'S', '2024-08-23 13:50:51', '2024-08-23 19:18:41'),
(2, 'Assento de Elevação', 'por_dia', 2, '12.00', NULL, 'assento', 'S', '2024-08-23 13:52:08', '2024-09-14 19:01:56'),
(3, 'Taxa de Fronteira', 'valor_integral', 0, NULL, '100.00', 'taxa', 'S', '2024-08-23 13:56:12', '2024-08-23 13:56:12'),
(4, 'Assento de Elevação', 'valor_integral', 1, NULL, '10.00', NULL, 'S', '2024-08-23 19:44:46', '2024-08-28 05:55:01');

-- --------------------------------------------------------

--
-- Table structure for table `opcionais_grupos_veiculos`
--

CREATE TABLE `opcionais_grupos_veiculos` (
  `id` int NOT NULL,
  `opcional_id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pais`
--

CREATE TABLE `pais` (
  `id` bigint UNSIGNED NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sigla` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paises`
--

CREATE TABLE `paises` (
  `id` bigint UNSIGNED NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigla` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paises`
--

INSERT INTO `paises` (`id`, `nome`, `sigla`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Afghanistan', 'AF', 'S', NULL, NULL),
(2, 'Albania', 'AL', 'S', NULL, NULL),
(3, 'Algeria', 'DZ', 'S', NULL, NULL),
(4, 'Andorra', 'AD', 'S', NULL, NULL),
(5, 'Angola', 'AO', 'S', NULL, NULL),
(6, 'Antigua and Barbuda', 'AG', 'S', NULL, NULL),
(7, 'Argentina', 'AR', 'S', NULL, NULL),
(8, 'Armenia', 'AM', 'S', NULL, NULL),
(9, 'Australia', 'AU', 'S', NULL, NULL),
(10, 'Austria', 'AT', 'S', NULL, NULL),
(11, 'Azerbaijan', 'AZ', 'S', NULL, NULL),
(12, 'Bahamas', 'BS', 'S', NULL, NULL),
(13, 'Bahrain', 'BH', 'S', NULL, NULL),
(14, 'Bangladesh', 'BD', 'S', NULL, NULL),
(15, 'Barbados', 'BB', 'S', NULL, NULL),
(16, 'Belarus', 'BY', 'S', NULL, NULL),
(17, 'Belgium', 'BE', 'S', NULL, NULL),
(18, 'Belize', 'BZ', 'S', NULL, NULL),
(19, 'Benin', 'BJ', 'S', NULL, NULL),
(20, 'Bhutan', 'BT', 'S', NULL, NULL),
(21, 'Bolivia', 'BO', 'S', NULL, NULL),
(22, 'Bosnia and Herzegovina', 'BA', 'S', NULL, NULL),
(23, 'Botswana', 'BW', 'S', NULL, NULL),
(24, 'Brazil', 'BR', 'S', NULL, NULL),
(25, 'Brunei', 'BN', 'S', NULL, NULL),
(26, 'Bulgaria', 'BG', 'S', NULL, NULL),
(27, 'Burkina Faso', 'BF', 'S', NULL, NULL),
(28, 'Burundi', 'BI', 'S', NULL, NULL),
(29, 'Cabo Verde', 'CV', 'S', NULL, NULL),
(30, 'Cambodia', 'KH', 'S', NULL, NULL),
(31, 'Cameroon', 'CM', 'S', NULL, NULL),
(32, 'Canada', 'CA', 'S', NULL, NULL),
(33, 'Central African Republic', 'CF', 'S', NULL, NULL),
(34, 'Chad', 'TD', 'S', NULL, NULL),
(35, 'Chile', 'CL', 'S', NULL, NULL),
(36, 'China', 'CN', 'S', NULL, NULL),
(37, 'Colombia', 'CO', 'S', NULL, NULL),
(38, 'Comoros', 'KM', 'S', NULL, NULL),
(39, 'Congo', 'CG', 'S', NULL, NULL),
(40, 'Congo (DRC)', 'CD', 'S', NULL, NULL),
(41, 'Costa Rica', 'CR', 'S', NULL, NULL),
(42, 'Croatia', 'HR', 'S', NULL, NULL),
(43, 'Cuba', 'CU', 'S', NULL, NULL),
(44, 'Cyprus', 'CY', 'S', NULL, NULL),
(45, 'Czech Republic', 'CZ', 'S', NULL, NULL),
(46, 'Denmark', 'DK', 'S', NULL, NULL),
(47, 'Djibouti', 'DJ', 'S', NULL, NULL),
(48, 'Dominica', 'DM', 'S', NULL, NULL),
(49, 'Dominican Republic', 'DO', 'S', NULL, NULL),
(50, 'East Timor', 'TL', 'S', NULL, NULL),
(51, 'Ecuador', 'EC', 'S', NULL, NULL),
(52, 'Egypt', 'EG', 'S', NULL, NULL),
(53, 'El Salvador', 'SV', 'S', NULL, NULL),
(54, 'Equatorial Guinea', 'GQ', 'S', NULL, NULL),
(55, 'Eritrea', 'ER', 'S', NULL, NULL),
(56, 'Estonia', 'EE', 'S', NULL, NULL),
(57, 'Eswatini', 'SZ', 'S', NULL, NULL),
(58, 'Ethiopia', 'ET', 'S', NULL, NULL),
(59, 'Fiji', 'FJ', 'S', NULL, NULL),
(60, 'Finland', 'FI', 'S', NULL, NULL),
(61, 'France', 'FR', 'S', NULL, NULL),
(62, 'Gabon', 'GA', 'S', NULL, NULL),
(63, 'Gambia', 'GM', 'S', NULL, NULL),
(64, 'Georgia', 'GE', 'S', NULL, NULL),
(65, 'Germany', 'DE', 'S', NULL, NULL),
(66, 'Ghana', 'GH', 'S', NULL, NULL),
(67, 'Greece', 'GR', 'S', NULL, NULL),
(68, 'Grenada', 'GD', 'S', NULL, NULL),
(69, 'Guatemala', 'GT', 'S', NULL, NULL),
(70, 'Guinea', 'GN', 'S', NULL, NULL),
(71, 'Guinea-Bissau', 'GW', 'S', NULL, NULL),
(72, 'Guyana', 'GY', 'S', NULL, NULL),
(73, 'Haiti', 'HT', 'S', NULL, NULL),
(74, 'Honduras', 'HN', 'S', NULL, NULL),
(75, 'Hungary', 'HU', 'S', NULL, NULL),
(76, 'Iceland', 'IS', 'S', NULL, NULL),
(77, 'India', 'IN', 'S', NULL, NULL),
(78, 'Indonesia', 'ID', 'S', NULL, NULL),
(79, 'Iran', 'IR', 'S', NULL, NULL),
(80, 'Iraq', 'IQ', 'S', NULL, NULL),
(81, 'Ireland', 'IE', 'S', NULL, NULL),
(82, 'Israel', 'IL', 'S', NULL, NULL),
(83, 'Italy', 'IT', 'S', NULL, NULL),
(84, 'Jamaica', 'JM', 'S', NULL, NULL),
(85, 'Japan', 'JP', 'S', NULL, NULL),
(86, 'Jordan', 'JO', 'S', NULL, NULL),
(87, 'Kazakhstan', 'KZ', 'S', NULL, NULL),
(88, 'Kenya', 'KE', 'S', NULL, NULL),
(89, 'Kiribati', 'KI', 'S', NULL, NULL),
(90, 'Kuwait', 'KW', 'S', NULL, NULL),
(91, 'Kyrgyzstan', 'KG', 'S', NULL, NULL),
(92, 'Laos', 'LA', 'S', NULL, NULL),
(93, 'Latvia', 'LV', 'S', NULL, NULL),
(94, 'Lebanon', 'LB', 'S', NULL, NULL),
(95, 'Lesotho', 'LS', 'S', NULL, NULL),
(96, 'Liberia', 'LR', 'S', NULL, NULL),
(97, 'Libya', 'LY', 'S', NULL, NULL),
(98, 'Liechtenstein', 'LI', 'S', NULL, NULL),
(99, 'Lithuania', 'LT', 'S', NULL, NULL),
(100, 'Luxembourg', 'LU', 'S', NULL, NULL),
(101, 'Madagascar', 'MG', 'S', NULL, NULL),
(102, 'Malawi', 'MW', 'S', NULL, NULL),
(103, 'Malaysia', 'MY', 'S', NULL, NULL),
(104, 'Maldives', 'MV', 'S', NULL, NULL),
(105, 'Mali', 'ML', 'S', NULL, NULL),
(106, 'Malta', 'MT', 'S', NULL, NULL),
(107, 'Marshall Islands', 'MH', 'S', NULL, NULL),
(108, 'Mauritania', 'MR', 'S', NULL, NULL),
(109, 'Mauritius', 'MU', 'S', NULL, NULL),
(110, 'Mexico', 'MX', 'S', NULL, NULL),
(111, 'Micronesia', 'FM', 'S', NULL, NULL),
(112, 'Moldova', 'MD', 'S', NULL, NULL),
(113, 'Monaco', 'MC', 'S', NULL, NULL),
(114, 'Mongolia', 'MN', 'S', NULL, NULL),
(115, 'Montenegro', 'ME', 'S', NULL, NULL),
(116, 'Morocco', 'MA', 'S', NULL, NULL),
(117, 'Mozambique', 'MZ', 'S', NULL, NULL),
(118, 'Myanmar', 'MM', 'S', NULL, NULL),
(119, 'Namibia', 'NA', 'S', NULL, NULL),
(120, 'Nauru', 'NR', 'S', NULL, NULL),
(121, 'Nepal', 'NP', 'S', NULL, NULL),
(122, 'Netherlands', 'NL', 'S', NULL, NULL),
(123, 'New Zealand', 'NZ', 'S', NULL, NULL),
(124, 'Nicaragua', 'NI', 'S', NULL, NULL),
(125, 'Niger', 'NE', 'S', NULL, NULL),
(126, 'Nigeria', 'NG', 'S', NULL, NULL),
(127, 'North Korea', 'KP', 'S', NULL, NULL),
(128, 'North Macedonia', 'MK', 'S', NULL, NULL),
(129, 'Norway', 'NO', 'S', NULL, NULL),
(130, 'Oman', 'OM', 'S', NULL, NULL),
(131, 'Pakistan', 'PK', 'S', NULL, NULL),
(132, 'Palau', 'PW', 'S', NULL, NULL),
(133, 'Panama', 'PA', 'S', NULL, NULL),
(134, 'Papua New Guinea', 'PG', 'S', NULL, NULL),
(135, 'Paraguay', 'PY', 'S', NULL, NULL),
(136, 'Peru', 'PE', 'S', NULL, NULL),
(137, 'Philippines', 'PH', 'S', NULL, NULL),
(138, 'Poland', 'PL', 'S', NULL, NULL),
(139, 'Portugal', 'PT', 'S', NULL, NULL),
(140, 'Qatar', 'QA', 'S', NULL, NULL),
(141, 'Romania', 'RO', 'S', NULL, NULL),
(142, 'Russia', 'RU', 'S', NULL, NULL),
(143, 'Rwanda', 'RW', 'S', NULL, NULL),
(144, 'Saint Kitts and Nevis', 'KN', 'S', NULL, NULL),
(145, 'Saint Lucia', 'LC', 'S', NULL, NULL),
(146, 'Saint Vincent and the Grenadines', 'VC', 'S', NULL, NULL),
(147, 'Samoa', 'WS', 'S', NULL, NULL),
(148, 'San Marino', 'SM', 'S', NULL, NULL),
(149, 'Sao Tome and Principe', 'ST', 'S', NULL, NULL),
(150, 'Saudi Arabia', 'SA', 'S', NULL, NULL),
(151, 'Senegal', 'SN', 'S', NULL, NULL),
(152, 'Serbia', 'RS', 'S', NULL, NULL),
(153, 'Seychelles', 'SC', 'S', NULL, NULL),
(154, 'Sierra Leone', 'SL', 'S', NULL, NULL),
(155, 'Singapore', 'SG', 'S', NULL, NULL),
(156, 'Slovakia', 'SK', 'S', NULL, NULL),
(157, 'Slovenia', 'SI', 'S', NULL, NULL),
(158, 'Solomon Islands', 'SB', 'S', NULL, NULL),
(159, 'Somalia', 'SO', 'S', NULL, NULL),
(160, 'South Africa', 'ZA', 'S', NULL, NULL),
(161, 'South Korea', 'KR', 'S', NULL, NULL),
(162, 'South Sudan', 'SS', 'S', NULL, NULL),
(163, 'Spain', 'ES', 'S', NULL, NULL),
(164, 'Sri Lanka', 'LK', 'S', NULL, NULL),
(165, 'Sudan', 'SD', 'S', NULL, NULL),
(166, 'Suriname', 'SR', 'S', NULL, NULL),
(167, 'Sweden', 'SE', 'S', NULL, NULL),
(168, 'Switzerland', 'CH', 'S', NULL, NULL),
(169, 'Syria', 'SY', 'S', NULL, NULL),
(170, 'Taiwan', 'TW', 'S', NULL, NULL),
(171, 'Tajikistan', 'TJ', 'S', NULL, NULL),
(172, 'Tanzania', 'TZ', 'S', NULL, NULL),
(173, 'Thailand', 'TH', 'S', NULL, NULL),
(174, 'Togo', 'TG', 'S', NULL, NULL),
(175, 'Tonga', 'TO', 'S', NULL, NULL),
(176, 'Trinidad and Tobago', 'TT', 'S', NULL, NULL),
(177, 'Tunisia', 'TN', 'S', NULL, NULL),
(178, 'Turkey', 'TR', 'S', NULL, NULL),
(179, 'Turkmenistan', 'TM', 'S', NULL, NULL),
(180, 'Tuvalu', 'TV', 'S', NULL, NULL),
(181, 'Uganda', 'UG', 'S', NULL, NULL),
(182, 'Ukraine', 'UA', 'S', NULL, NULL),
(183, 'United Arab Emirates', 'AE', 'S', NULL, NULL),
(184, 'United Kingdom', 'GB', 'S', NULL, NULL),
(185, 'United States', 'US', 'S', NULL, NULL),
(186, 'Uruguay', 'UY', 'S', NULL, NULL),
(187, 'Uzbekistan', 'UZ', 'S', NULL, NULL),
(188, 'Vanuatu', 'VU', 'S', NULL, NULL),
(189, 'Vatican City', 'VA', 'S', NULL, NULL),
(190, 'Venezuela', 'VE', 'S', NULL, NULL),
(191, 'Vietnam', 'VN', 'S', NULL, NULL),
(192, 'Yemen', 'YE', 'S', NULL, NULL),
(193, 'Zambia', 'ZM', 'S', NULL, NULL),
(194, 'Zimbabwe', 'ZW', 'S', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Acesso à Regras', 'auth', NULL, '2024-08-08 16:02:57'),
(2, 'Acesso à Permissões', 'auth', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservas`
--

CREATE TABLE `reservas` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
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
  `cupom_desconto_id` int DEFAULT NULL,
  `percentual_desconto` decimal(10,2) DEFAULT '0.00',
  `valor_total_com_desconto` decimal(10,2) DEFAULT '0.00',
  `valor_opcionais` decimal(10,2) DEFAULT '0.00',
  `valor_total_final` decimal(10,2) DEFAULT '0.00',
  `data_cancelamento` datetime DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservas`
--

INSERT INTO `reservas` (`id`, `user_id`, `veiculo_id`, `modelo_id`, `estacao_retirada_id`, `estacao_devolucao_id`, `grupo_frota_id`, `pagamento_efetuado`, `tarifa_base`, `quantidade_dias`, `data_inicio`, `data_fim`, `status_reserva_id`, `valor_total_tarifa_base`, `data_criacao`, `cupom_desconto_id`, `percentual_desconto`, `valor_total_com_desconto`, `valor_opcionais`, `valor_total_final`, `data_cancelamento`, `observacoes`, `created_at`, `updated_at`) VALUES
(2, 1, NULL, 2, 6, 6, 1, 'N', '75.00', 21, '2024-12-31 04:51:00', '2025-01-21 04:51:00', 3, '0.00', '2024-12-30 04:51:20', NULL, NULL, '0.00', '0.00', '0.00', NULL, NULL, '2024-12-30 10:51:20', '2025-01-01 17:19:45'),
(3, 10, NULL, 5, 7, 7, 1, 'S', '75.00', 15, '2024-12-31 04:53:00', '2025-01-15 04:53:00', 3, '700.00', '2024-12-30 05:00:01', NULL, NULL, '0.00', '0.00', '0.00', NULL, NULL, '2024-12-30 11:00:01', '2025-01-01 17:18:47'),
(4, 5, NULL, 3, 7, 7, 2, 'N', '150.00', 20, '2025-01-01 17:49:00', '2025-01-21 17:49:00', 3, '1070.00', '2024-12-31 17:51:09', NULL, NULL, '0.00', '0.00', '0.00', NULL, NULL, '2024-12-31 23:51:09', '2025-01-01 17:19:10'),
(5, 10, NULL, 3, 8, 7, 2, 'N', '150.00', 6, '2025-01-01 20:46:00', '2025-01-07 20:46:00', 3, '0.00', '2024-12-31 20:46:51', 11, '20.00', '0.00', '0.00', '426.00', NULL, NULL, '2025-01-01 02:46:51', '2025-01-01 16:59:10'),
(6, 10, NULL, 3, 7, 7, 2, 'N', '150.00', 17, '2025-01-02 11:22:00', '2025-01-19 11:22:00', 3, '0.00', '2025-01-01 11:25:35', NULL, NULL, '0.00', '0.00', '3128.00', NULL, NULL, '2025-01-01 17:25:35', '2025-01-01 18:11:06'),
(7, 10, NULL, 5, 7, 7, 1, 'N', '75.00', 13, '2025-01-15 11:46:00', '2025-01-28 11:46:00', 3, '0.00', '2025-01-01 11:50:41', NULL, '0.00', '0.00', '0.00', '0.00', NULL, NULL, '2025-01-01 17:50:41', '2025-01-01 18:10:47'),
(8, 10, NULL, 3, 7, 8, 2, 'N', '150.00', 19, '2025-02-05 11:57:00', '2025-02-24 11:57:00', 3, '0.00', '2025-01-01 11:57:32', NULL, '0.00', '0.00', '0.00', '0.00', NULL, NULL, '2025-01-01 17:57:32', '2025-01-01 18:10:02'),
(9, 10, NULL, 3, 7, 8, 2, 'N', '150.00', 6, '2025-01-22 11:58:00', '2025-01-28 11:58:00', 3, '0.00', '2025-01-01 12:01:36', NULL, '0.00', '0.00', '276.00', '1176.00', NULL, NULL, '2025-01-01 18:01:36', '2025-01-01 18:09:26'),
(10, 10, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-01 15:15:00', '2025-01-13 12:13:00', 11, '900.00', '2025-01-01 12:13:44', NULL, '0.00', '0.00', '418.00', '1318.00', NULL, NULL, '2025-01-01 18:13:44', '2025-01-02 02:50:57'),
(11, 10, NULL, 2, 6, 6, 1, 'N', '75.00', 6, '2025-01-01 20:53:00', '2025-01-07 20:52:00', 11, '450.00', '2025-01-01 20:53:19', NULL, '0.00', '0.00', '0.00', '450.00', NULL, NULL, '2025-01-02 02:53:19', '2025-01-02 02:54:22'),
(12, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:27:51', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:27:51', '2025-01-02 03:45:38'),
(13, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:31:52', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:31:52', '2025-01-02 03:45:47'),
(14, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:34:26', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:34:26', '2025-01-02 03:45:54'),
(15, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:35:24', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:35:24', '2025-01-02 03:46:09'),
(16, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:35:34', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:35:34', '2025-01-02 03:46:01'),
(17, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:35:41', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:35:41', '2025-01-02 03:46:21'),
(18, 1, NULL, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:53:00', '2025-01-13 20:53:00', 3, '900.00', '2025-01-01 21:37:28', NULL, '0.00', '0.00', '0.00', '900.00', NULL, NULL, '2025-01-02 03:37:28', '2025-01-02 03:46:16'),
(19, 10, 6, 5, 7, 7, 1, 'N', '75.00', 12, '2025-01-02 00:44:00', '2025-01-13 21:45:00', 11, '900.00', '2025-01-01 21:46:40', NULL, '0.00', '0.00', '204.00', '1104.00', NULL, NULL, '2025-01-02 03:46:40', '2025-01-25 05:11:28'),
(20, 1, NULL, 2, 6, 7, 1, 'N', '75.00', 4, '2025-01-27 23:11:00', '2025-01-31 23:11:00', 1, '300.00', '2025-01-24 23:12:14', NULL, '0.00', '0.00', '68.00', '368.00', NULL, NULL, '2025-01-25 05:12:14', '2025-01-25 05:12:14'),
(21, 1, 2, 2, 6, 7, 1, 'S', '75.00', 4, '2025-01-27 23:11:00', '2025-01-31 23:11:00', 2, '300.00', '2025-01-24 23:12:50', NULL, '0.00', '0.00', '68.00', '368.00', NULL, NULL, '2025-01-25 05:12:50', '2025-01-27 06:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `reservas_opcionais`
--

CREATE TABLE `reservas_opcionais` (
  `id` int NOT NULL,
  `reserva_id` int NOT NULL,
  `opcional_id` int NOT NULL,
  `quantidade` int DEFAULT '1',
  `preco_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `preco_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservas_opcionais`
--

INSERT INTO `reservas_opcionais` (`id`, `reserva_id`, `opcional_id`, `quantidade`, `preco_unitario`, `preco_total`, `created_at`, `updated_at`) VALUES
(39, 3, 1, 1, '0.00', '255.00', '2024-12-30 11:00:01', '2024-12-30 11:00:01'),
(40, 3, 4, 1, '0.00', '10.00', '2024-12-30 11:00:01', '2024-12-30 11:00:01'),
(41, 3, 2, 2, '0.00', '180.00', '2024-12-30 11:00:01', '2024-12-30 11:00:01'),
(42, 4, 1, 2, '0.00', '340.00', '2024-12-31 23:51:09', '2024-12-31 23:51:09'),
(43, 4, 2, 1, '0.00', '240.00', '2024-12-31 23:51:09', '2024-12-31 23:51:09'),
(44, 5, 1, 2, '0.00', '102.00', '2025-01-01 02:46:51', '2025-01-01 02:46:51'),
(45, 5, 2, 1, '0.00', '72.00', '2025-01-01 02:46:51', '2025-01-01 02:46:51'),
(46, 6, 1, 2, '0.00', '289.00', '2025-01-01 17:25:35', '2025-01-01 17:25:35'),
(47, 7, 1, 2, '0.00', '221.00', '2025-01-01 17:50:41', '2025-01-01 17:50:41'),
(48, 7, 4, 1, '0.00', '10.00', '2025-01-01 17:50:41', '2025-01-01 17:50:41'),
(49, 8, 1, 2, '0.00', '323.00', '2025-01-01 17:57:32', '2025-01-01 17:57:32'),
(50, 8, 2, 1, '0.00', '228.00', '2025-01-01 17:57:32', '2025-01-01 17:57:32'),
(51, 9, 1, 2, '0.00', '102.00', '2025-01-01 18:01:36', '2025-01-01 18:01:36'),
(52, 9, 2, 1, '0.00', '72.00', '2025-01-01 18:01:36', '2025-01-01 18:01:36'),
(55, 10, 1, 1, '0.00', '204.00', '2025-01-02 02:01:11', '2025-01-02 02:01:11'),
(56, 10, 4, 1, '0.00', '10.00', '2025-01-02 02:01:11', '2025-01-02 02:01:11'),
(57, 19, 1, 1, '0.00', '204.00', '2025-01-02 03:46:40', '2025-01-02 03:46:40'),
(58, 20, 1, 1, '0.00', '68.00', '2025-01-25 05:12:14', '2025-01-25 05:12:14'),
(59, 21, 1, 1, '0.00', '68.00', '2025-01-25 05:12:50', '2025-01-25 05:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `reserva_logs`
--

CREATE TABLE `reserva_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `reserva_id` int UNSIGNED DEFAULT NULL,
  `detalhes` json DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reserva_logs`
--

INSERT INTO `reserva_logs` (`id`, `reserva_id`, `detalhes`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '{\"acao\": \"Pagamento efetuado\", \"status\": \"succeeded\", \"currency\": \"brl\", \"customer_id\": \"cus_RLBRYNp6k1Ek9n\", \"valor_total\": \" 243.90 \", \"id_pagamento\": \"pi_3QbdgCI8GjEr2tfY1OQslBiL\", \"data_pagamento\": \"2024-12-30 07:46:26\"}', 1, '2024-12-30 10:46:26', NULL),
(2, 1, '{\"acao\": \"Pagamento efetuado\", \"status\": \"succeeded\", \"currency\": \"brl\", \"customer_id\": \"cus_RLBRYNp6k1Ek9n\", \"valor_total\": \" 243.90 \", \"id_pagamento\": \"pi_3Qbdh8I8GjEr2tfY0mYo9f3a\", \"data_pagamento\": \"2024-12-30 07:47:24\"}', 1, '2024-12-30 10:47:24', NULL),
(3, 2, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-21T04:51\", \"modelo_id\": \"2\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2024-12-31T04:51\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 21, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"estacao_retirada_id\": \"6\", \"estacao_devolucao_id\": \"6\", \"estacao_retirada_nome\": \"\\n                Xpand Cars6\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars6\\n            \"}}', 1, '2024-12-30 10:51:20', NULL),
(4, 3, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-15T04:53\", \"modelo_id\": \"5\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 1}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}, {\"id\": 4, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 4, \"grupo_frota_id\": 1}, \"descricao\": null, \"created_at\": \"2024-08-23T16:44:46.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-08-28T02:55:01.000000Z\", \"preco_por_dia\": null, \"tipo_cobranca\": \"valor_integral\", \"preco_integral\": \"10.00\", \"tipo_opcional_id\": 1}, {\"id\": 2, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 2, \"grupo_frota_id\": 1}, \"descricao\": \"assento\", \"created_at\": \"2024-08-23T10:52:08.000000Z\", \"quantidade\": 2, \"updated_at\": \"2024-09-14T16:01:56.000000Z\", \"preco_por_dia\": \"12.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 2}], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2024-12-31T04:53\", \"tarifa_base\": \"75.00\", \"valor_total\": 700, \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 15, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"estacao_retirada_id\": \"7\", \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \"}}', 1, '2024-12-30 11:00:01', NULL),
(5, 3, '{\"acao\": \"Pagamento efetuado\", \"status\": \"succeeded\", \"currency\": \"brl\", \"customer_id\": \"cus_RLBRYNp6k1Ek9n\", \"valor_total\": \"700\", \"id_pagamento\": \"pi_3QbdtbI8GjEr2tfY0zggnF3w\", \"data_pagamento\": \"2024-12-30 08:00:17\"}', 1, '2024-12-30 11:00:17', NULL),
(6, 4, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"5\", \"data_fim\": \"2025-01-21T17:49\", \"modelo_id\": \"3\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 2}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 2, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}, {\"id\": 2, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 2, \"grupo_frota_id\": 2}, \"descricao\": \"assento\", \"created_at\": \"2024-08-23T10:52:08.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-09-14T16:01:56.000000Z\", \"preco_por_dia\": \"12.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 2}], \"user_nome\": \"Victor Bezzer do Nascimento Vieira\", \"data_inicio\": \"2025-01-01T17:49\", \"tarifa_base\": \"150.00\", \"valor_total\": 1070, \"cupom_desconto\": null, \"grupo_frota_id\": 2, \"quantidade_dias\": 20, \"grupo_frota_nome\": \"Monospace Auto Unspecified Drive Diesel Air\", \"estacao_retirada_id\": \"7\", \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \"}}', 1, '2024-12-31 23:51:09', NULL),
(7, 5, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-07T20:46\", \"modelo_id\": \"3\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"quantidade\": 2, \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null}, {\"id\": 2, \"nome\": \"Assento de Elevação\", \"quantidade\": 1, \"preco_por_dia\": \"12.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-01T20:46\", \"tarifa_base\": \"150.00\", \"valor_total\": 426, \"cupom_desconto\": {\"id\": 11, \"nome\": \"Desconto de Fim de Semana\", \"ativo\": \"S\", \"valor\": \"20\", \"codigo\": \"FDS20\", \"created_at\": \"2024-09-18T07:06:58.000000Z\", \"updated_at\": null}, \"grupo_frota_id\": 2, \"quantidade_dias\": 6, \"valor_opcionais\": 276, \"grupo_frota_nome\": \"Monospace Auto Unspecified Drive Diesel Air\", \"estacao_retirada_id\": \"8\", \"estacao_devolucao_id\": \"7\", \"valor_total_por_dias\": \"150.00\", \"estacao_retirada_nome\": \"\\n                Xpand Cars8\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \"}}', 1, '2025-01-01 02:46:51', NULL),
(8, 6, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-19T11:22\", \"modelo_id\": \"3\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 2}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 2, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-02T11:22\", \"tarifa_base\": \"150.00\", \"valor_total\": 3128, \"cupom_desconto\": null, \"grupo_frota_id\": 2, \"quantidade_dias\": 17, \"valor_opcionais\": 578, \"grupo_frota_nome\": \"Monospace Auto Unspecified Drive Diesel Air\", \"estacao_retirada_id\": \"7\", \"estacao_devolucao_id\": \"7\", \"valor_total_por_dias\": 2550, \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \"}}', 1, '2025-01-01 17:25:35', NULL),
(9, 7, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-28T11:46\", \"modelo_id\": \"5\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 1}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 2, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}, {\"id\": 4, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 4, \"grupo_frota_id\": 1}, \"descricao\": null, \"created_at\": \"2024-08-23T16:44:46.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-08-28T02:55:01.000000Z\", \"preco_por_dia\": null, \"tipo_cobranca\": \"valor_integral\", \"preco_integral\": \"10.00\", \"tipo_opcional_id\": 1}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-15T11:46\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 13, \"valor_opcionais\": 452, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 1427, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_com_desconto\": 0, \"valor_diario_reserva_base\": 975}}', 1, '2025-01-01 17:50:41', NULL),
(10, 8, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-02-24T11:57\", \"modelo_id\": \"3\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"quantidade\": 2, \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null}, {\"id\": 2, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 2, \"grupo_frota_id\": 2}, \"descricao\": \"assento\", \"created_at\": \"2024-08-23T10:52:08.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-09-14T16:01:56.000000Z\", \"preco_por_dia\": \"12.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 2}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-02-05T11:57\", \"tarifa_base\": \"150.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 2, \"quantidade_dias\": 19, \"valor_opcionais\": 874, \"grupo_frota_nome\": \"Monospace Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 3724, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"8\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars8\\n            \", \"valor_total_com_desconto\": 0, \"valor_diario_reserva_base\": 2850}}', 1, '2025-01-01 17:57:32', NULL),
(11, 9, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-28T11:58\", \"modelo_id\": \"3\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 2}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 2, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}, {\"id\": 2, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 2, \"grupo_frota_id\": 2}, \"descricao\": \"assento\", \"created_at\": \"2024-08-23T10:52:08.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-09-14T16:01:56.000000Z\", \"preco_por_dia\": \"12.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 2}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-22T11:58\", \"tarifa_base\": \"150.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 2, \"quantidade_dias\": 6, \"valor_opcionais\": 276, \"grupo_frota_nome\": \"Monospace Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 1176, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"8\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars8\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-01 18:01:36', NULL),
(12, 10, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-13T12:13\", \"modelo_id\": \"5\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 1}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 2, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}, {\"id\": 4, \"nome\": \"Assento de Elevação\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 4, \"grupo_frota_id\": 1}, \"descricao\": null, \"created_at\": \"2024-08-23T16:44:46.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-08-28T02:55:01.000000Z\", \"preco_por_dia\": null, \"tipo_cobranca\": \"valor_integral\", \"preco_integral\": \"10.00\", \"tipo_opcional_id\": 1}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-01T15:15\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 418, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 1318, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-01 18:13:44', NULL),
(13, 10, '{\"acao\": \"Reserva atualizada\", \"data\": {\"user_id\": 10, \"data_fim\": \"2025-01-13 12:13:00\", \"modelo_id\": 5, \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"quantidade\": 1, \"preco_total\": \"204.00\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null}, {\"id\": 4, \"nome\": \"Assento de Elevação\", \"quantidade\": 1, \"preco_total\": \"10.00\", \"preco_por_dia\": null, \"tipo_cobranca\": \"valor_integral\", \"preco_integral\": \"10.00\"}], \"data_inicio\": \"2025-01-01 15:15:00\", \"modelo_nome\": \"C 300 D AMG Line Station\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 214, \"valor_total_final\": 1114, \"estacao_retirada_id\": 7, \"percentual_desconto\": 0, \"estacao_devolucao_id\": 7, \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 02:01:11', NULL),
(14, 11, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-07T20:52\", \"modelo_id\": \"2\", \"opcionais\": [], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-01T23:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 6, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 450, \"estacao_retirada_id\": \"6\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"6\", \"estacao_retirada_nome\": \"\\n                Xpand Cars6\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars6\\n            \", \"valor_total_tarifa_base\": 450, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 02:53:19', NULL),
(15, 12, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:27:51', NULL),
(16, 13, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:31:52', NULL),
(17, 14, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:34:26', NULL),
(18, 15, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:35:24', NULL),
(19, 16, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:35:34', NULL),
(20, 17, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:35:41', NULL),
(21, 18, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-13T20:53\", \"modelo_id\": \"5\", \"opcionais\": [], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-02T00:53\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 0, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 900, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:37:28', NULL),
(22, 19, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"10\", \"data_fim\": \"2025-01-13T21:45\", \"modelo_id\": \"5\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"quantidade\": 1, \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null}], \"user_nome\": \"Erickson Ferreira da Silva Nascimento\", \"data_inicio\": \"2025-01-02T00:44\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 12, \"valor_opcionais\": 204, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 1104, \"estacao_retirada_id\": \"7\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars7\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 900, \"valor_total_com_desconto\": 0}}', 1, '2025-01-02 03:46:40', NULL),
(23, 21, '{\"acao\": \"Reserva criada\", \"data\": {\"user_id\": \"1\", \"data_fim\": \"2025-01-31T23:11\", \"modelo_id\": \"2\", \"opcionais\": [{\"id\": 1, \"nome\": \"Cadeira de Criança\", \"ativo\": \"S\", \"pivot\": {\"opcional_id\": 1, \"grupo_frota_id\": 1}, \"descricao\": \"cadeira\", \"created_at\": \"2024-08-23T10:50:51.000000Z\", \"quantidade\": 1, \"updated_at\": \"2024-08-23T16:18:41.000000Z\", \"preco_por_dia\": \"17.00\", \"tipo_cobranca\": \"por_dia\", \"preco_integral\": null, \"tipo_opcional_id\": 1}], \"user_nome\": \"Paulo da Silva Santana Arraes\", \"data_inicio\": \"2025-01-27T23:11\", \"tarifa_base\": \"75.00\", \"cupom_desconto\": null, \"grupo_frota_id\": 1, \"quantidade_dias\": 4, \"valor_opcionais\": 68, \"grupo_frota_nome\": \"Wagon/Estate Auto Unspecified Drive Diesel Air\", \"valor_total_final\": 368, \"estacao_retirada_id\": \"6\", \"percentual_desconto\": 0, \"estacao_devolucao_id\": \"7\", \"estacao_retirada_nome\": \"\\n                Xpand Cars6\\n            \", \"estacao_devolucao_nome\": \"\\n                Xpand Cars7\\n            \", \"valor_total_tarifa_base\": 300, \"valor_total_com_desconto\": 0}}', 1, '2025-01-25 05:12:50', NULL),
(24, 21, '{\"acao\": \"Pagamento efetuado\", \"status\": \"succeeded\", \"currency\": \"brl\", \"customer_id\": \"cus_RLBRYNp6k1Ek9n\", \"valor_total\": \"368.00\", \"id_pagamento\": \"pi_3QkzHII8GjEr2tfY074gwkGh\", \"data_pagamento\": \"2025-01-25 02:39:19\"}', 1, '2025-01-25 05:39:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'auth', NULL, NULL),
(2, 'Gerente', 'auth', NULL, NULL),
(3, 'Analista', 'auth', NULL, NULL),
(4, 'Cliente', 'auth', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `segmentos`
--

CREATE TABLE `segmentos` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `segmentos`
--

INSERT INTO `segmentos` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Familiar', 'S', '2024-08-21 04:26:31', '2024-08-21 04:26:31'),
(2, 'SUV', 'S', '2024-08-21 04:26:43', '2024-08-21 04:26:43'),
(3, 'Compacto', 'S', '2024-08-21 04:27:00', '2024-08-21 04:27:18'),
(4, 'Econômico', 'S', '2024-08-21 04:27:38', '2024-08-21 04:27:50'),
(5, 'Minivan', 'S', '2024-08-21 04:28:01', '2024-08-21 04:28:01'),
(6, 'SUV Compacto', 'S', '2024-08-21 04:28:29', '2024-08-21 04:28:29');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ERcrXUycef18t00AMHajcphHWPTLkKXRQ7bOYlLh', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiUEJLRDNPa3dMRUgzQ3IzeWpVQ1llZVh4VXB4N1N3S3F3ZnFhQ0JuUCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vY29tYW5kb3MudGVzdC9yZXNlcnZhLzIxIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjc6InJlc2VydmEiO2E6Mjp7czo5OiJ1cmxQYWRyYW8iO3M6NzoicmVzZXJ2YSI7czoxMjoicm90YVJlZGlyZWN0IjtzOjEzOiJyZXNlcnZhLmluZGV4Ijt9fQ==', 1738028278);

-- --------------------------------------------------------

--
-- Table structure for table `status_item_checklist`
--

CREATE TABLE `status_item_checklist` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_item_checklist`
--

INSERT INTO `status_item_checklist` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'OK', 'S', '2024-09-24 21:35:11', '2024-09-24 21:35:11'),
(2, 'DANIFICADO', 'S', '2024-09-24 21:35:24', '2024-09-24 21:35:24'),
(3, 'FALTANDO', 'S', '2024-09-24 21:35:24', '2024-09-24 21:35:38'),
(4, 'PRECISA REVISAR', 'S', '2024-09-24 21:35:52', '2024-09-24 21:35:52');

-- --------------------------------------------------------

--
-- Table structure for table `status_movimentacao`
--

CREATE TABLE `status_movimentacao` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_movimentacao`
--

INSERT INTO `status_movimentacao` (`id`, `nome`, `cor`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Concluído', 'success', 'S', '2024-08-30 14:08:34', '2025-01-03 01:01:41'),
(2, 'Em Trânsito', 'info', 'S', '2024-08-30 14:08:55', '2025-01-03 01:02:06'),
(3, 'Cancelado', 'secondary', 'S', '2024-08-30 14:09:06', '2025-01-03 01:01:20'),
(4, 'Quebrado', 'danger', 'S', '2024-08-30 14:09:14', '2025-01-03 01:01:33'),
(5, 'Em Manutenção', 'warning', 'S', '2024-08-30 14:09:37', '2025-01-03 01:01:57'),
(6, 'Em Espera', 'purple', 'S', '2024-09-03 06:11:39', '2025-01-24 06:40:47'),
(7, 'Indisponível', 'orange', 'S', '2024-09-12 02:57:26', '2025-01-24 06:40:10'),
(8, 'Aguardando Manutenção', 'teal', 'S', '2025-01-24 04:04:28', '2025-01-24 04:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `status_reserva`
--

CREATE TABLE `status_reserva` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `cor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_reserva`
--

INSERT INTO `status_reserva` (`id`, `nome`, `descricao`, `ativo`, `cor`, `created_at`, `updated_at`) VALUES
(1, 'Criada', 'A reserva foi registrada com sucesso e aguarda o próximo passo, como a confirmação de pagamento. Este status indica que a reserva está em sua fase inicial e pode ser alterado para \"confirmada\", \"cancelada\" ou \"expirada\" conforme o andamento do processo.', 'S', 'info', '2024-08-21 04:31:06', '2024-12-31 04:23:58'),
(2, 'Confirmada', 'A reserva foi validada e o pagamento concluído. O veículo está disponível para retirada ou utilização, conforme a data e hora do agendamento. A próxima fase natural da reserva é \"Em Atendimento\", embora ela ainda possa ser atrasada ou cancelada antes de atingir esse estágio.', 'S', 'success', '2024-08-21 04:31:18', '2024-12-31 04:31:24'),
(3, 'Cancelada', 'A reserva foi cancelada antes da confirmação ou utilização do veículo. Isso pode ocorrer por solicitação do cliente ou por motivos administrativos, e a reserva não será mais considerada válida. Caso o pagamento tenha sido efetuado, solicite o estorno.', 'S', 'danger', '2024-08-21 04:31:28', '2024-12-31 04:37:07'),
(4, 'Em Andamento', 'O check-in foi realizado e a reserva está em andamento. O veículo foi retirado ou está sendo utilizado, e o processo segue conforme o agendamento. Se tudo ocorrer bem, a reserva avançará para o status \"Concluída\".', 'S', 'warning', '2024-08-21 04:31:57', '2024-12-31 05:18:19'),
(5, 'Concluída', 'A reserva foi concluída com sucesso. O veículo foi entregue e retornado conforme o previsto, e todos os procedimentos foram finalizados.', 'S', 'primary', '2024-09-03 03:52:58', '2024-12-31 05:01:48'),
(6, 'Atrasada', 'O horário agendado para a retirada ou utilização do veículo já passou e o check-in ainda não foi realizado. A reserva está em atraso e, se não for para “Em Atendimento” em até 2 horas após o agendamento, será considerada expirada.', 'S', 'orange', '2024-09-03 03:52:58', '2024-12-31 05:46:11'),
(7, 'Em Manutenção', 'O veículo está passando por manutenção agendada ou reparos. A reserva pode estar temporariamente suspensa até que a manutenção seja concluída e o veículo esteja disponível novamente.', 'N', 'secondary', '2024-09-03 03:53:22', '2024-12-31 05:00:40'),
(8, 'Suspensa', 'A reserva foi suspensa temporariamente devido a questões que precisam ser resolvidas antes de prosseguir. Isso pode incluir problemas com o veículo, documentação ou outros fatores.', 'N', 'danger', '2024-09-03 03:53:22', '2024-12-31 05:47:03'),
(9, 'Falha', 'Houve uma falha no processamento da reserva devido a problemas técnicos ou administrativos. Pode ser necessário revisar e corrigir a reserva para prosseguir.', 'N', 'black', '2024-09-03 03:53:46', '2024-12-31 05:46:54'),
(11, 'Expirado', 'A reserva não foi confirmada dentro do período estipulado e foi automaticamente cancelada. O veículo não está mais reservado e a oportunidade foi perdida.', 'S', 'secondary', '2024-12-31 04:26:17', '2024-12-31 04:36:34');

-- --------------------------------------------------------

--
-- Table structure for table `status_veiculos`
--

CREATE TABLE `status_veiculos` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_veiculos`
--

INSERT INTO `status_veiculos` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Ativo', 'S', '2024-08-21 13:37:21', '2024-08-21 13:37:21'),
(2, 'Desativado', 'S', '2024-08-21 13:37:31', '2024-08-21 13:37:31'),
(3, 'Vendido', 'S', '2024-08-21 13:37:39', '2024-08-21 13:37:39'),
(4, 'Quebrado', 'S', '2024-08-21 13:37:48', '2024-08-21 13:37:48');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_items`
--

CREATE TABLE `subscription_items` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_product` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stripe_price` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tempo_entre_estacoes`
--

CREATE TABLE `tempo_entre_estacoes` (
  `id` int NOT NULL,
  `estacao_origem_id` int NOT NULL,
  `estacao_destino_id` int NOT NULL,
  `tempo_deslocamento` time NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tempo_entre_estacoes`
--

INSERT INTO `tempo_entre_estacoes` (`id`, `estacao_origem_id`, `estacao_destino_id`, `tempo_deslocamento`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 7, 2, '11:47:00', 'S', '2024-08-29 13:43:57', '2024-08-30 13:09:06'),
(2, 7, 8, '00:15:00', 'S', '2024-09-07 13:15:35', '2024-09-07 13:15:35');

-- --------------------------------------------------------

--
-- Table structure for table `termo_consentimento`
--

CREATE TABLE `termo_consentimento` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `texto` longtext COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `termo_consentimento`
--

INSERT INTO `termo_consentimento` (`id`, `nome`, `texto`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Termo 1', 'especificações', 'S', '2024-08-21 07:36:31', '2024-08-21 13:13:09');

-- --------------------------------------------------------

--
-- Table structure for table `tipos_movimentacao`
--

CREATE TABLE `tipos_movimentacao` (
  `id` int NOT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipos_movimentacao`
--

INSERT INTO `tipos_movimentacao` (`id`, `nome`, `descricao`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Adição ao Estoque', 'Movimentação usada quando um novo veículo é adicionado ao estoque da frota.', 'S', '2024-08-30 13:51:12', '2024-09-11 06:15:49'),
(2, 'Retirada de Veículo (Check-in)', 'Quando o cliente retira o veículo (check-in), esse evento precisa ser registrado, assim como o horário e os dados da reserva.', 'S', '2024-08-30 13:52:51', '2025-01-24 06:42:00'),
(3, 'Devolução de Veículo (Check-out)', 'Quando o cliente devolve o veículo (check-out), essa movimentação é importante para atualizar o status de disponibilidade do veículo.', 'S', '2024-08-30 13:53:13', '2025-01-24 06:34:02'),
(4, 'Problema Mecânico', 'Quando o veículo apresenta um problema mecânico, é necessário registrar essa movimentação para saber quando ele ficará indisponível para locação e precisa ser reparado.', 'S', '2024-09-11 06:16:54', '2025-01-24 06:34:18'),
(5, 'Transporte para Oficina', 'Quando o veículo é movido para uma oficina para reparos ou manutenção, isso deve ser registrado.', 'S', '2024-09-11 06:17:24', '2025-01-24 06:34:33'),
(6, 'Chegada à Oficina', 'Quando o veículo chega à oficina para reparos, essa movimentação é importante para o controle de seu estado.', 'S', '2024-09-11 06:17:50', '2025-01-24 06:34:48'),
(7, 'Retorno da Oficina', 'Após os reparos, quando o veículo é transportado de volta à estação, isso deve ser registrado.', 'S', '2024-09-11 06:18:12', '2025-01-24 06:35:02'),
(8, 'Chegada à Estação', 'Quando veiculo chegou para alguma estação', 'S', '2024-09-11 06:18:33', '2025-01-24 06:35:23'),
(9, 'Veículo Indisponível', 'Se o veículo estiver temporariamente fora de circulação por qualquer motivo (por exemplo, aguardando reparo, manutenção ou inspeção), essa movimentação pode ser registrada.', 'S', '2024-09-11 06:18:45', '2025-01-24 06:35:38'),
(10, 'Transferência Entre Estações', 'Quando um veículo é transferido de uma estação para outra, seja por solicitação de cliente ou necessidade de movimentação interna, é importante registrar a movimentação.', 'S', '2024-09-11 06:21:39', '2025-01-24 06:35:55'),
(11, 'Fora de Serviço', 'Se o veículo for retirado permanentemente da locação por algum motivo (descontinuação, venda, etc.), essa movimentação pode ser registrada para o encerramento do histórico de uso.', 'S', '2024-09-11 06:23:21', '2025-01-24 06:36:33'),
(14, 'Venda de Veículo', 'Caso o veículo seja vendido, essa movimentação deve ser registrada como parte de seu histórico final no sistema.', 'S', '2025-01-12 05:30:07', '2025-01-24 06:39:29');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_checklist`
--

CREATE TABLE `tipo_checklist` (
  `id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_checklist`
--

INSERT INTO `tipo_checklist` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Pré-locação', 'S', '2024-09-24 21:56:54', '2024-09-24 21:58:57'),
(2, 'Pós-locação', 'S', '2024-09-24 21:57:06', '2024-09-24 21:59:19');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_frota`
--

CREATE TABLE `tipo_frota` (
  `id` int NOT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_frota`
--

INSERT INTO `tipo_frota` (`id`, `codigo`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'automoveis', 'Automoveis', 'S', '0000-00-00 00:00:00', NULL),
(2, 'van', 'Van', 'S', '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_opcional`
--

CREATE TABLE `tipo_opcional` (
  `id` int NOT NULL,
  `nome` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_opcional`
--

INSERT INTO `tipo_opcional` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'Extras', 'S', '2024-08-23 13:38:36', '2024-08-23 13:38:36'),
(2, 'Taxas', 'S', '2024-08-23 13:38:49', '2024-08-23 13:38:49'),
(3, 'Insegurança', 'S', '2024-08-23 13:38:57', '2024-08-23 13:38:57'),
(4, 'Pacotes de Insegurança', 'S', '2024-08-23 13:39:06', '2024-08-23 13:39:06');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_transacoes`
--

CREATE TABLE `tipo_transacoes` (
  `id` int NOT NULL,
  `nome` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_transacoes`
--

INSERT INTO `tipo_transacoes` (`id`, `nome`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 'PIX', 'S', '2024-08-06 02:43:43', NULL),
(2, 'BOLETO', 'S', '2024-08-06 02:43:43', NULL),
(3, 'TED', 'S', '2024-08-06 02:43:43', NULL),
(4, 'DOC', 'S', '2024-08-06 02:43:43', NULL),
(5, 'DINHEIRO', 'S', '2024-08-06 02:43:43', NULL),
(6, 'CARTÃO', 'S', '2024-08-06 02:43:43', NULL),
(13, 'Teste3', 'S', '2024-08-08 04:04:14', '2024-08-08 04:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_valor_caracteristica_frota`
--

CREATE TABLE `tipo_valor_caracteristica_frota` (
  `id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuracao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_valor_caracteristica_frota`
--

INSERT INTO `tipo_valor_caracteristica_frota` (`id`, `nome`, `configuracao`, `ativo`, `created_at`, `updated_at`) VALUES
(2, 'Numérico', NULL, 'S', '2024-08-20 02:55:59', '2024-08-20 02:55:59'),
(3, 'Texto', NULL, 'S', '2024-08-20 04:26:08', '2024-08-20 04:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `transacoes`
--

CREATE TABLE `transacoes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `reserva_id` int UNSIGNED DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `metodo_pagamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metodo_pagamento_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_pagamento_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transacoes`
--

INSERT INTO `transacoes` (`id`, `user_id`, `reserva_id`, `valor`, `metodo_pagamento`, `metodo_pagamento_id`, `tipo_pagamento_id`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, '10.00', NULL, 'master', 'credit_card', 'Completa', '2024-12-01 00:30:12', '2024-12-01 00:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `tipo_usuario_id` bigint DEFAULT NULL,
  `sexo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_identificacao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais_sigla` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `local_nascimento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pm_last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tipo_usuario_id`, `sexo`, `nome`, `email`, `telefone`, `celular`, `fax`, `endereco`, `numero_identificacao`, `cidade`, `zip_code`, `pais_sigla`, `tax_number`, `data_nascimento`, `local_nascimento`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`) VALUES
(1, 1, 'M', 'Paulo da Silva Santana Arraes', '123@123.com', '0', '(12) 12312-3123', NULL, '1231231', '12312313', '12312313', '123123', '1', NULL, '1999-06-10', 'Olinda - PE', NULL, '$2y$12$4aoCFKUUmXlUjZ0.sfMMp.JkcJAzvSfM/VPcPv6bZklpxo5v/B1Zm', NULL, '2024-08-04 03:21:26', '2024-12-30 07:13:06', 'cus_RLBRYNp6k1Ek9n', NULL, NULL, NULL),
(5, NULL, NULL, 'Victor Bezzer do Nascimento Vieira', '456@456.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Y9cHz3q/3klk36edVoHsieGV9f./q8Zjdg6YnQrtLCu2/Mi6/7yyi', NULL, '2024-08-25 07:42:27', '2024-08-25 07:42:27', NULL, NULL, NULL, NULL),
(6, 1, 'M', 'Sistema', 'sistema@xpandCars.com.br', '0', '(00) 00000-0000', NULL, '.', '.', 'Porto', '0', '24', NULL, '2000-01-11', 'Portugal', NULL, '$2y$12$HDtgGJCP9SsWDpFoxDwDmueMWIktfGR4eoeIorvAYWksRUqNQf4KK', NULL, '2024-09-12 05:22:53', '2024-09-12 05:22:53', NULL, NULL, NULL, NULL),
(10, 4, 'M', 'Erickson Ferreira da Silva Nascimento', 'erickson.rinho@hotmail.com', '123', '(81) 98692-2586', NULL, 'Rua Bom Clima, 2', '9.700.185', 'Igarassu', '53635-120', '24', NULL, '1999-01-15', 'Olinda - PE - Brasil', NULL, '$2y$12$OBJydk70c6Dt6heaU4GAQOoOyWMahV7eAHgEOe58lbk3YQ068CUYO', NULL, '2024-12-31 16:04:04', '2024-12-31 16:04:04', 'cus_RV5HkexMqAhXql', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint UNSIGNED NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `veiculos`
--

CREATE TABLE `veiculos` (
  `id` int NOT NULL,
  `nome` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_frota_id` int NOT NULL,
  `grupo_frota_id` int NOT NULL,
  `marca_id` int NOT NULL,
  `modelo_id` int NOT NULL,
  `status_veiculo_id` int NOT NULL,
  `ano_fabricacao` int NOT NULL,
  `ano_modelo` int NOT NULL,
  `placa_licenca` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chassi` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `renavam` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_placa_licenca` date DEFAULT NULL,
  `data_compra` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `kms` int NOT NULL DEFAULT '0',
  `limite_kms` int DEFAULT NULL,
  `numero_motor` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vendido` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `disponivel` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S',
  `data_vendido` date DEFAULT NULL,
  `proxima_revisao_kms` int DEFAULT NULL,
  `proxima_manutencao_kms` int DEFAULT '0',
  `data_proxima_manutencao` date DEFAULT NULL,
  `data_proxima_inspecao` date DEFAULT NULL,
  `data_hora_disponibilidade` timestamp NULL DEFAULT NULL,
  `local_disponibilidade` int DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `veiculos`
--

INSERT INTO `veiculos` (`id`, `nome`, `tipo_frota_id`, `grupo_frota_id`, `marca_id`, `modelo_id`, `status_veiculo_id`, `ano_fabricacao`, `ano_modelo`, `placa_licenca`, `chassi`, `renavam`, `cor`, `data_placa_licenca`, `data_compra`, `data_validade`, `kms`, `limite_kms`, `numero_motor`, `vendido`, `disponivel`, `data_vendido`, `proxima_revisao_kms`, `proxima_manutencao_kms`, `data_proxima_manutencao`, `data_proxima_inspecao`, `data_hora_disponibilidade`, `local_disponibilidade`, `created_at`, `updated_at`) VALUES
(2, 'vei0002', 2, 1, 1, 2, 1, 2015, 2000, '1212', '123asd', 'dsa123', '123', '2024-08-25', '2024-08-24', '2024-08-23', 111111, 22222, '123456789', 'S', 'S', '2024-08-20', 33333, 44444, '2024-08-22', '2024-08-21', '2024-09-14 04:40:00', 6, '2024-08-21 19:23:11', '2024-09-13 07:43:11'),
(3, 'vei0003', 1, 1, 1, 5, 1, 2022, 2022, 'AI-51-UX', 'W1K2062061F046269', NULL, 'CINZENTO', '2022-03-24', '2022-03-24', '2024-09-13', 23096, 1, '1', 'N', 'S', NULL, 33000, 33000, '2025-03-24', '2026-03-24', '2024-09-13 04:51:00', 7, '2024-09-08 07:49:25', '2024-09-13 07:51:17'),
(4, 'vei0004', 1, 2, 2, 3, 1, 2022, 2022, '515915162', '15sdf1asdf51s', NULL, 'red', '2024-02-08', '2024-02-08', '2024-10-02', 4645, 1, 'asdfa1423', 'N', 'S', NULL, 300000, 100000, '2025-02-08', '2029-02-08', '2024-09-13 05:04:00', 8, '2024-09-09 05:06:46', '2024-09-13 08:04:27'),
(5, 'vei0005', 1, 2, 2, 3, 1, 2022, 2022, '515915162', '15sdf1asdf51s', NULL, 'red', '2024-02-08', '2024-02-08', '2024-09-13', 4645, 1, 'asdfa1423', 'N', 'S', NULL, 300000, 100000, '2025-02-08', '2029-02-08', '2024-09-13 05:05:00', 7, '2024-09-09 05:22:36', '2024-09-13 08:05:36'),
(6, 'vei0006', 1, 1, 2, 3, 1, 2022, 2022, '515915162', '15sdf1asdf51s', NULL, 'red', '2024-02-08', '2024-02-08', NULL, 4645, 1, 'asdfa1423', 'N', 'S', NULL, 300000, 100000, '2025-02-08', '2029-02-08', NULL, NULL, '2024-09-09 05:30:26', '2024-09-09 05:30:26'),
(7, 'vei0007', 1, 1, 2, 3, 1, 2022, 2022, '515915162', '15sdf1asdf51s', NULL, 'red', '2024-02-08', '2024-02-08', NULL, 4645, 1, 'asdfa1423', 'N', 'S', NULL, 300000, 100000, '2025-02-08', '2029-02-08', NULL, NULL, '2024-09-09 05:31:27', '2024-09-09 05:31:27');

-- --------------------------------------------------------

--
-- Table structure for table `veiculo_danos`
--

CREATE TABLE `veiculo_danos` (
  `id` int NOT NULL,
  `categoria_id` int NOT NULL,
  `localizacao_problema` enum('Interior','Exterior') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dano_id` int NOT NULL,
  `tipo_dano` enum('ausente','amolgadela_leve','amolgadela_grave','risco_leve','risco_grave') COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_dano` enum('direita','esquerda','frente','atras','cima') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `custo_estimado` decimal(10,2) NOT NULL,
  `quilometragem_veiculo` int NOT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `veiculo_id` int NOT NULL,
  `reserva_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `veiculo_danos`
--

INSERT INTO `veiculo_danos` (`id`, `categoria_id`, `localizacao_problema`, `dano_id`, `tipo_dano`, `local_dano`, `custo_estimado`, `quilometragem_veiculo`, `observacoes`, `veiculo_id`, `reserva_id`, `created_at`, `updated_at`) VALUES
(3, 4, 'Interior', 3, 'ausente', 'direita', '1.00', 2, NULL, 2, 21, '2025-01-27 03:50:59', '2025-01-27 03:50:59'),
(4, 4, 'Interior', 3, 'ausente', 'direita', '2.00', 3, 'safdfasdfdas dfasdfasdfa sdfasdfasdfasdf asdfasdfasdfasdfasdf asdfasdfasdfas dfasdfas dfasd fasd fasd fasdf asdf asdf asdf asdf as df asd f asdf   sdfadfasdfasdf ', 2, 21, '2025-01-27 03:54:06', '2025-01-27 01:28:43'),
(5, 4, 'Interior', 3, 'ausente', 'cima', '32.00', 213, 'teste', 2, NULL, '2025-01-27 03:55:15', '2025-01-27 01:22:13'),
(6, 4, 'Interior', 3, 'ausente', 'esquerda', '3.00', 4, 'wqerqw', 2, 22, '2025-01-27 03:58:26', '2025-01-27 01:22:11'),
(7, 4, 'Interior', 3, 'ausente', 'esquerda', '123.00', 23, NULL, 2, 21, '2025-01-27 04:01:21', '2025-01-27 04:01:21'),
(8, 4, 'Interior', 3, 'ausente', 'esquerda', '234.00', 31, 'sdafas', 2, NULL, '2025-01-27 04:01:48', '2025-01-27 01:22:07'),
(9, 4, 'Interior', 3, 'ausente', 'atras', '23.00', 123131, 'teste', 2, 21, '2025-01-27 04:18:54', '2025-01-27 04:18:54'),
(10, 4, 'Interior', 3, 'ausente', 'frente', '2432.00', 324234, 'dssfg', 2, 21, '2025-01-27 04:21:06', '2025-01-27 04:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `veiculo_danos_imagens`
--

CREATE TABLE `veiculo_danos_imagens` (
  `id` int NOT NULL,
  `dano_veiculo_id` int NOT NULL,
  `caminho_imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `caracteristicas_frotas`
--
ALTER TABLE `caracteristicas_frotas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `caracteristicas_grupo_frota`
--
ALTER TABLE `caracteristicas_grupo_frota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `caracteristicas_tipo_frota`
--
ALTER TABLE `caracteristicas_tipo_frota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categoria_danos`
--
ALTER TABLE `categoria_danos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `checklists`
--
ALTER TABLE `checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`),
  ADD KEY `reserva_id` (`reserva_id`);

--
-- Indexes for table `configuracao_checklist`
--
ALTER TABLE `configuracao_checklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_checklist_id` (`tipo_checklist_id`),
  ADD KEY `item_checklist_id` (`item_checklist_id`);

--
-- Indexes for table `contrato_checkin`
--
ALTER TABLE `contrato_checkin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cupom_desconto`
--
ALTER TABLE `cupom_desconto`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `danos`
--
ALTER TABLE `danos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indexes for table `dias_uteis`
--
ALTER TABLE `dias_uteis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disponibilidade_inicial_veiculo`
--
ALTER TABLE `disponibilidade_inicial_veiculo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estacaos`
--
ALTER TABLE `estacaos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `estacoes`
--
ALTER TABLE `estacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dia_util_id` (`dia_util_id`),
  ADD KEY `pais_id` (`pais_id`);

--
-- Indexes for table `estacoes_dias_uteis`
--
ALTER TABLE `estacoes_dias_uteis`
  ADD PRIMARY KEY (`estacao_id`,`dia_util_id`) USING BTREE,
  ADD KEY `dia_utile_id` (`dia_util_id`);

--
-- Indexes for table `frotas_caracteristicas`
--
ALTER TABLE `frotas_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frota_id` (`frota_id`),
  ADD KEY `caracteristica_id` (`caracteristica_id`);

--
-- Indexes for table `grupos_caracteristicas`
--
ALTER TABLE `grupos_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grupo_id` (`grupo_id`),
  ADD KEY `caracteristica_id` (`caracteristica_id`);

--
-- Indexes for table `grupos_frota`
--
ALTER TABLE `grupos_frota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frota_id` (`tipo_frota_id`);

--
-- Indexes for table `grupo_frota_caracteristica_frota`
--
ALTER TABLE `grupo_frota_caracteristica_frota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grupo_frota_id` (`grupo_frota_id`),
  ADD KEY `modelo_id` (`caracteristica_frota_id`) USING BTREE;

--
-- Indexes for table `grupo_frota_modelo`
--
ALTER TABLE `grupo_frota_modelo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grupo_frota_id` (`grupo_frota_id`),
  ADD KEY `modelo_id` (`modelo_id`);

--
-- Indexes for table `grupo_frota_opcional`
--
ALTER TABLE `grupo_frota_opcional`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opcional_id` (`opcional_id`),
  ADD KEY `grupo_frota_id` (`grupo_frota_id`);

--
-- Indexes for table `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_estacao` (`estacao_id`,`horario_diario_id`),
  ADD KEY `id_horario_diario` (`horario_diario_id`);

--
-- Indexes for table `horarios_diarios`
--
ALTER TABLE `horarios_diarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_checklist`
--
ALTER TABLE `item_checklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_jobs`
--
ALTER TABLE `log_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marca_id` (`marca_id`),
  ADD KEY `segmento_id` (`segmento_id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`),
  ADD KEY `tipo_movimentacao_id` (`tipo_movimentacao_id`),
  ADD KEY `local_origem` (`local_origem_id`),
  ADD KEY `local_destino` (`local_destino_id`),
  ADD KEY `status_movimentacao_id` (`status_movimentacao_id`),
  ADD KEY `reserva_id` (`reserva_id`),
  ADD KEY `modelo_id` (`modelo_id`),
  ADD KEY `ordem_servico_id` (`ordem_servico_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `opcionais`
--
ALTER TABLE `opcionais`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opcionais_grupos_veiculos`
--
ALTER TABLE `opcionais_grupos_veiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opcional_id` (`opcional_id`),
  ADD KEY `grupo_frota_id` (`grupo_frota_id`);

--
-- Indexes for table `pais`
--
ALTER TABLE `pais`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_user_id_foreign` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estacao_retirada_id` (`estacao_retirada_id`),
  ADD KEY `estacao_devolucao_id` (`estacao_devolucao_id`),
  ADD KEY `cupom_desconto_id` (`cupom_desconto_id`),
  ADD KEY `modelo_id` (`modelo_id`),
  ADD KEY `reservas_ibfk_6` (`veiculo_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status_reserva_id`),
  ADD KEY `grupo_frota_id` (`grupo_frota_id`),
  ADD KEY `valor_desconto` (`percentual_desconto`),
  ADD KEY `valor_opcionais` (`valor_opcionais`),
  ADD KEY `valor_total` (`valor_total_final`),
  ADD KEY `valor_total_por_dias` (`valor_total_tarifa_base`),
  ADD KEY `valor_total_com_desconto` (`valor_total_com_desconto`),
  ADD KEY `valor_total_final` (`valor_total_final`);

--
-- Indexes for table `reservas_opcionais`
--
ALTER TABLE `reservas_opcionais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reserva_id` (`reserva_id`),
  ADD KEY `opcional_id` (`opcional_id`);

--
-- Indexes for table `reserva_logs`
--
ALTER TABLE `reserva_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reserva_id` (`reserva_id`),
  ADD KEY `usuario_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `segmentos`
--
ALTER TABLE `segmentos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `status_item_checklist`
--
ALTER TABLE `status_item_checklist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_movimentacao`
--
ALTER TABLE `status_movimentacao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_reserva`
--
ALTER TABLE `status_reserva`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status_veiculos`
--
ALTER TABLE `status_veiculos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_stripe_id_unique` (`stripe_id`),
  ADD KEY `subscriptions_user_id_stripe_status_index` (`user_id`,`stripe_status`);

--
-- Indexes for table `subscription_items`
--
ALTER TABLE `subscription_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_items_stripe_id_unique` (`stripe_id`),
  ADD KEY `subscription_items_subscription_id_stripe_price_index` (`subscription_id`,`stripe_price`);

--
-- Indexes for table `tempo_entre_estacoes`
--
ALTER TABLE `tempo_entre_estacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_estacao_origem` (`estacao_origem_id`),
  ADD KEY `fk_estacao_destino` (`estacao_destino_id`);

--
-- Indexes for table `termo_consentimento`
--
ALTER TABLE `termo_consentimento`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipos_movimentacao`
--
ALTER TABLE `tipos_movimentacao`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_checklist`
--
ALTER TABLE `tipo_checklist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_frota`
--
ALTER TABLE `tipo_frota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_opcional`
--
ALTER TABLE `tipo_opcional`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_transacoes`
--
ALTER TABLE `tipo_transacoes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tipo_valor_caracteristica_frota`
--
ALTER TABLE `tipo_valor_caracteristica_frota`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transacoes`
--
ALTER TABLE `transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_stripe_id_index` (`stripe_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_frota_id` (`tipo_frota_id`),
  ADD KEY `grupo_frota_id` (`grupo_frota_id`),
  ADD KEY `marca_id` (`marca_id`),
  ADD KEY `modelo_id` (`modelo_id`),
  ADD KEY `status_veiculo_id` (`status_veiculo_id`),
  ADD KEY `disponivel` (`disponivel`);

--
-- Indexes for table `veiculo_danos`
--
ALTER TABLE `veiculo_danos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `veiculo_id` (`veiculo_id`),
  ADD KEY `reserva_id` (`reserva_id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `tipo_dano` (`tipo_dano`),
  ADD KEY `local_dano` (`local_dano`);

--
-- Indexes for table `veiculo_danos_imagens`
--
ALTER TABLE `veiculo_danos_imagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dano_veiculo_id` (`dano_veiculo_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caracteristicas_frotas`
--
ALTER TABLE `caracteristicas_frotas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `caracteristicas_grupo_frota`
--
ALTER TABLE `caracteristicas_grupo_frota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `caracteristicas_tipo_frota`
--
ALTER TABLE `caracteristicas_tipo_frota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categoria_danos`
--
ALTER TABLE `categoria_danos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `checklists`
--
ALTER TABLE `checklists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `configuracao_checklist`
--
ALTER TABLE `configuracao_checklist`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `contrato_checkin`
--
ALTER TABLE `contrato_checkin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cupom_desconto`
--
ALTER TABLE `cupom_desconto`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `danos`
--
ALTER TABLE `danos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dias_uteis`
--
ALTER TABLE `dias_uteis`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `disponibilidade_inicial_veiculo`
--
ALTER TABLE `disponibilidade_inicial_veiculo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `estacaos`
--
ALTER TABLE `estacaos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `estacoes`
--
ALTER TABLE `estacoes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `frotas_caracteristicas`
--
ALTER TABLE `frotas_caracteristicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grupos_caracteristicas`
--
ALTER TABLE `grupos_caracteristicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grupos_frota`
--
ALTER TABLE `grupos_frota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `grupo_frota_caracteristica_frota`
--
ALTER TABLE `grupo_frota_caracteristica_frota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `grupo_frota_modelo`
--
ALTER TABLE `grupo_frota_modelo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `grupo_frota_opcional`
--
ALTER TABLE `grupo_frota_opcional`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `horarios_diarios`
--
ALTER TABLE `horarios_diarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `item_checklist`
--
ALTER TABLE `item_checklist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `log_jobs`
--
ALTER TABLE `log_jobs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `movimentacoes`
--
ALTER TABLE `movimentacoes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `opcionais`
--
ALTER TABLE `opcionais`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `opcionais_grupos_veiculos`
--
ALTER TABLE `opcionais_grupos_veiculos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pais`
--
ALTER TABLE `pais`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paises`
--
ALTER TABLE `paises`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reservas_opcionais`
--
ALTER TABLE `reservas_opcionais`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `reserva_logs`
--
ALTER TABLE `reserva_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `segmentos`
--
ALTER TABLE `segmentos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `status_item_checklist`
--
ALTER TABLE `status_item_checklist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `status_movimentacao`
--
ALTER TABLE `status_movimentacao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `status_reserva`
--
ALTER TABLE `status_reserva`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `status_veiculos`
--
ALTER TABLE `status_veiculos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_items`
--
ALTER TABLE `subscription_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tempo_entre_estacoes`
--
ALTER TABLE `tempo_entre_estacoes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `termo_consentimento`
--
ALTER TABLE `termo_consentimento`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tipos_movimentacao`
--
ALTER TABLE `tipos_movimentacao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tipo_checklist`
--
ALTER TABLE `tipo_checklist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipo_frota`
--
ALTER TABLE `tipo_frota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipo_opcional`
--
ALTER TABLE `tipo_opcional`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tipo_transacoes`
--
ALTER TABLE `tipo_transacoes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tipo_valor_caracteristica_frota`
--
ALTER TABLE `tipo_valor_caracteristica_frota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transacoes`
--
ALTER TABLE `transacoes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `veiculo_danos`
--
ALTER TABLE `veiculo_danos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `veiculo_danos_imagens`
--
ALTER TABLE `veiculo_danos_imagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checklists`
--
ALTER TABLE `checklists`
  ADD CONSTRAINT `checklists_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`),
  ADD CONSTRAINT `checklists_ibfk_2` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`);

--
-- Constraints for table `danos`
--
ALTER TABLE `danos`
  ADD CONSTRAINT `danos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria_danos` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `estacoes`
--
ALTER TABLE `estacoes`
  ADD CONSTRAINT `estacoes_fk_pais_id` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `estacoes_ibfk_1` FOREIGN KEY (`dia_util_id`) REFERENCES `dias_uteis` (`id`);

--
-- Constraints for table `estacoes_dias_uteis`
--
ALTER TABLE `estacoes_dias_uteis`
  ADD CONSTRAINT `estacoes_dias_uteis_ibfk_1` FOREIGN KEY (`estacao_id`) REFERENCES `estacoes` (`id`),
  ADD CONSTRAINT `estacoes_dias_uteis_ibfk_2` FOREIGN KEY (`dia_util_id`) REFERENCES `dias_uteis` (`id`);

--
-- Constraints for table `frotas_caracteristicas`
--
ALTER TABLE `frotas_caracteristicas`
  ADD CONSTRAINT `frotas_caracteristicas_ibfk_1` FOREIGN KEY (`frota_id`) REFERENCES `tipo_frota` (`id`),
  ADD CONSTRAINT `frotas_caracteristicas_ibfk_2` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas_frotas` (`id`);

--
-- Constraints for table `grupos_caracteristicas`
--
ALTER TABLE `grupos_caracteristicas`
  ADD CONSTRAINT `grupos_caracteristicas_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_frota` (`id`),
  ADD CONSTRAINT `grupos_caracteristicas_ibfk_2` FOREIGN KEY (`caracteristica_id`) REFERENCES `caracteristicas_grupo_frota` (`id`);

--
-- Constraints for table `grupos_frota`
--
ALTER TABLE `grupos_frota`
  ADD CONSTRAINT `grupos_frota_ibfk_1` FOREIGN KEY (`tipo_frota_id`) REFERENCES `tipo_frota` (`id`);

--
-- Constraints for table `grupo_frota_caracteristica_frota`
--
ALTER TABLE `grupo_frota_caracteristica_frota`
  ADD CONSTRAINT `fk_grupo_frota_caracteristica_frota_caracteristica_frota_id` FOREIGN KEY (`caracteristica_frota_id`) REFERENCES `caracteristicas_frotas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grupo_frota_caracteristica_frota_grupo_frota_id` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grupo_frota_modelo`
--
ALTER TABLE `grupo_frota_modelo`
  ADD CONSTRAINT `grupo_frota_modelo_ibfk_1` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grupo_frota_modelo_ibfk_2` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grupo_frota_opcional`
--
ALTER TABLE `grupo_frota_opcional`
  ADD CONSTRAINT `fk_grupo_frota_opcional_grupo_frota_id` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grupo_frota_opcional_opcional_id` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`horario_diario_id`) REFERENCES `horarios_diarios` (`id`),
  ADD CONSTRAINT `horarios_ibfk_2` FOREIGN KEY (`estacao_id`) REFERENCES `estacoes` (`id`);

--
-- Constraints for table `item_checklist`
--
ALTER TABLE `item_checklist`
  ADD CONSTRAINT `item_checklist_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `checklists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_jobs`
--
ALTER TABLE `log_jobs`
  ADD CONSTRAINT `fk_log_jobs_fk1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movimentacoes`
--
ALTER TABLE `movimentacoes`
  ADD CONSTRAINT `movimentacoes_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimentacoes_ibfk_2` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `movimentacoes_ibfk_3` FOREIGN KEY (`tipo_movimentacao_id`) REFERENCES `tipos_movimentacao` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_ibfk_4` FOREIGN KEY (`local_origem_id`) REFERENCES `estacoes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_ibfk_5` FOREIGN KEY (`local_destino_id`) REFERENCES `estacoes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_ibfk_6` FOREIGN KEY (`status_movimentacao_id`) REFERENCES `status_movimentacao` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimentacoes_ibfk_7` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `movimentacoes_ibfk_8` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `opcionais_grupos_veiculos`
--
ALTER TABLE `opcionais_grupos_veiculos`
  ADD CONSTRAINT `opcionais_grupos_veiculos_ibfk_1` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opcionais_grupos_veiculos_ibfk_2` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_10` FOREIGN KEY (`grupo_frota_id`) REFERENCES `grupos_frota` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reservas_ibfk_11` FOREIGN KEY (`status_reserva_id`) REFERENCES `status_reserva` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`estacao_retirada_id`) REFERENCES `estacoes` (`id`),
  ADD CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`estacao_devolucao_id`) REFERENCES `estacoes` (`id`),
  ADD CONSTRAINT `reservas_ibfk_4` FOREIGN KEY (`modelo_id`) REFERENCES `modelos` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reservas_ibfk_6` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reservas_ibfk_7` FOREIGN KEY (`cupom_desconto_id`) REFERENCES `cupom_desconto` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reservas_ibfk_8` FOREIGN KEY (`status_reserva_id`) REFERENCES `status_reserva` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `reservas_ibfk_9` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `reservas_opcionais`
--
ALTER TABLE `reservas_opcionais`
  ADD CONSTRAINT `reservas_opcionais_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_opcionais_ibfk_2` FOREIGN KEY (`opcional_id`) REFERENCES `opcionais` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tempo_entre_estacoes`
--
ALTER TABLE `tempo_entre_estacoes`
  ADD CONSTRAINT `fk_estacao_destino` FOREIGN KEY (`estacao_destino_id`) REFERENCES `estacoes` (`id`),
  ADD CONSTRAINT `fk_estacao_origem` FOREIGN KEY (`estacao_origem_id`) REFERENCES `estacoes` (`id`);

--
-- Constraints for table `transacoes`
--
ALTER TABLE `transacoes`
  ADD CONSTRAINT `transacoes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `veiculo_danos`
--
ALTER TABLE `veiculo_danos`
  ADD CONSTRAINT `veiculo_danos_ibfk_1` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`);

--
-- Constraints for table `veiculo_danos_imagens`
--
ALTER TABLE `veiculo_danos_imagens`
  ADD CONSTRAINT `veiculo_danos_imagens_ibfk_1` FOREIGN KEY (`dano_veiculo_id`) REFERENCES `veiculo_danos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
