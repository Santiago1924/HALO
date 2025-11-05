-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-11-2025 a las 20:48:28
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `halo_style`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avatars`
--

CREATE TABLE `avatars` (
  `id_avatar` int(11) NOT NULL,
  `name` varchar(80) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `avatars`
--

INSERT INTO `avatars` (`id_avatar`, `name`, `image_url`, `description`) VALUES
(1, 'Master Chief - John-117', 'img/personajes/117.png', 'El legendario supersoldado Spartan, simbolo de la humanidad.'),
(2, 'Sangheili - Didact\'s Chosen', 'img/personajes/elite.png', 'Guerrero de la raza Sangheili, leal al Covenant Remanente.'),
(3, 'Spartan-IV - GUNGNIR', 'img/personajes/gungnir.png', 'Spartan de combate equipado con la armadura Mjolnir GUNGNIR.'),
(4, 'Elite Zealot', 'img/personajes/elite_zealot.png', 'Guerrero Sangheili de alto rango del Covenant. Porta armadura energetica roja y hojas de energia dobles. Su ferocidad en combate es legendaria.'),
(5, 'Cortana', 'img/personajes/cortana.png', 'IA holografica avanzada y aliada clave del Jefe Maestro.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `battle_details`
--

CREATE TABLE `battle_details` (
  `id_detail` int(11) NOT NULL,
  `id_game_events` int(11) NOT NULL,
  `id_attacker` int(11) NOT NULL,
  `id_target` int(11) NOT NULL,
  `id_weapons` int(11) NOT NULL,
  `id_damage_body` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `battle_details`
--

INSERT INTO `battle_details` (`id_detail`, `id_game_events`, `id_attacker`, `id_target`, `id_weapons`, `id_damage_body`, `id_game`, `created_at`) VALUES
(1, 3, 10, 9, 1, 1, 61, '2025-11-02 14:45:05'),
(2, 4, 10, 9, 1, 1, 63, '2025-11-02 15:37:24'),
(3, 5, 10, 9, 1, 1, 63, '2025-11-02 15:37:36'),
(4, 6, 9, 10, 1, 1, 63, '2025-11-02 15:37:49'),
(5, 7, 9, 10, 1, 1, 63, '2025-11-02 15:37:50'),
(6, 8, 10, 9, 3, 3, 64, '2025-11-02 16:08:21'),
(7, 9, 9, 10, 3, 2, 64, '2025-11-02 16:08:41'),
(8, 10, 9, 10, 3, 2, 64, '2025-11-02 16:41:40'),
(9, 11, 10, 9, 3, 1, 66, '2025-11-03 12:34:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `damage_part_body`
--

CREATE TABLE `damage_part_body` (
  `id_damage_body` int(11) NOT NULL,
  `name` varchar(75) DEFAULT NULL,
  `damage` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `damage_part_body`
--

INSERT INTO `damage_part_body` (`id_damage_body`, `name`, `damage`, `description`) VALUES
(1, 'Cabeza', 10, 'Da?o cr?tico al disparar a la cabeza'),
(2, 'Torso', 10, 'Da?o normal al torso'),
(3, 'Piernas', 10, 'Da?o reducido a las piernas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detail_room_players_user`
--

CREATE TABLE `detail_room_players_user` (
  `id_detail_room_player_user` int(11) NOT NULL,
  `id_room_player` int(11) DEFAULT NULL,
  `player1` int(11) DEFAULT NULL,
  `player2` int(11) DEFAULT NULL,
  `id_game_events` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `games`
--

CREATE TABLE `games` (
  `id_games` int(11) NOT NULL,
  `id_room` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `max_players` int(11) DEFAULT NULL,
  `started` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `games`
--

INSERT INTO `games` (`id_games`, `id_room`, `start_date`, `end_date`, `max_players`, `started`) VALUES
(52, 1, '2025-11-02 08:12:09', '2025-11-02 09:12:09', 10, 1),
(56, 1, '2025-11-02 08:29:13', '2025-11-02 09:29:13', 10, 1),
(60, 1, '2025-11-02 08:50:33', '2025-11-02 09:50:33', 10, 1),
(61, 1, '2025-11-02 09:40:54', '2025-11-02 10:40:54', 10, 1),
(62, 1, '2025-11-02 10:04:17', '2025-11-02 11:04:17', 10, 1),
(63, 7, '2025-11-02 10:07:13', '2025-11-03 12:27:59', 10, 1),
(64, 1, '2025-11-02 11:07:45', '2025-11-02 12:07:45', 10, 1),
(66, 13, '2025-11-03 07:33:50', '2025-11-03 09:33:45', 10, 0),
(67, 1, '2025-11-03 07:55:08', '2025-11-03 08:55:08', 10, 1),
(68, 1, '2025-11-03 07:59:34', '2025-11-03 08:59:34', 10, 1),
(74, 1, '2025-11-03 09:34:51', '2025-11-03 09:37:18', 10, 1),
(77, 1, '2025-11-03 10:04:32', '2025-11-03 10:21:41', 10, 1),
(78, 1, '2025-11-03 10:52:13', '2025-11-03 11:52:13', 10, 1),
(82, 1, '2025-11-03 11:17:38', '2025-11-03 12:17:38', 10, 1),
(84, 1, '2025-11-03 12:02:09', '2025-11-03 12:02:53', 10, 1),
(108, 8, '2025-11-03 14:30:06', '2025-11-03 14:31:22', 10, 0),
(111, 1, '2025-11-03 22:35:42', '2025-11-03 16:26:37', 10, 1),
(114, 2, '2025-11-03 17:58:15', '2025-11-03 18:58:15', 10, 0),
(127, 1, '2025-11-04 08:26:37', '2025-11-04 14:47:09', 10, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `game_events`
--

CREATE TABLE `game_events` (
  `id_game_events` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `weapon_id` int(11) DEFAULT NULL,
  `damage` int(11) DEFAULT NULL,
  `points_awarded` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `game_events`
--

INSERT INTO `game_events` (`id_game_events`, `game_id`, `timestamp`, `event_type`, `weapon_id`, `damage`, `points_awarded`) VALUES
(3, 61, '2025-11-02 09:45:05', 'disparo', 1, 75, 0),
(4, 63, '2025-11-02 10:37:24', 'disparo', 1, 75, 0),
(5, 63, '2025-11-02 10:37:36', 'disparo', 1, 75, 0),
(6, 63, '2025-11-02 10:37:49', 'disparo', 1, 75, 0),
(7, 63, '2025-11-02 10:37:50', 'disparo', 1, 75, 0),
(8, 64, '2025-11-02 11:08:21', 'disparo', 3, 60, 0),
(9, 64, '2025-11-02 11:08:41', 'disparo', 3, 100, 0),
(10, 64, '2025-11-02 11:41:40', 'disparo', 3, 100, 0),
(11, 66, '2025-11-03 07:34:19', 'disparo', 3, 250, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_partidas`
--

CREATE TABLE `historial_partidas` (
  `id_historial` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_game` int(11) NOT NULL,
  `result` enum('victoria','derrota','empate') NOT NULL,
  `puntos_cambiados` int(11) NOT NULL DEFAULT 0,
  `puntos_totales` int(11) NOT NULL DEFAULT 0,
  `kills` int(11) DEFAULT 0,
  `deaths` int(11) DEFAULT 0,
  `fecha_jugada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_partidas`
--

INSERT INTO `historial_partidas` (`id_historial`, `id_user`, `id_game`, `result`, `puntos_cambiados`, `puntos_totales`, `kills`, `deaths`, `fecha_jugada`) VALUES
(1, 9, 63, 'victoria', 50, 1050, 0, 0, '2025-11-03 08:20:18'),
(2, 10, 63, 'derrota', -20, 980, 0, 0, '2025-11-03 08:20:18'),
(3, 10, 66, 'victoria', 50, 1050, 0, 0, '2025-11-03 09:33:45'),
(4, 9, 66, 'derrota', -20, 1030, 0, 0, '2025-11-03 09:33:45'),
(5, 10, 74, 'derrota', -20, 1030, 0, 0, '2025-11-03 09:36:09'),
(6, 9, 74, 'victoria', 50, 1080, 0, 0, '2025-11-03 09:36:09'),
(7, 10, 74, 'derrota', -20, 1010, 0, 0, '2025-11-03 09:37:18'),
(8, 9, 74, 'victoria', 50, 1130, 0, 0, '2025-11-03 09:37:18'),
(9, 10, 74, 'derrota', -20, 990, 0, 0, '2025-11-03 09:57:47'),
(10, 10, 74, 'derrota', -20, 970, 0, 0, '2025-11-03 09:57:59'),
(11, 10, 74, 'derrota', -20, 950, 0, 0, '2025-11-03 09:57:59'),
(12, 10, 74, 'derrota', -20, 930, 0, 0, '2025-11-03 09:57:59'),
(13, 10, 74, 'derrota', -20, 910, 0, 0, '2025-11-03 09:57:59'),
(14, 10, 74, 'derrota', -20, 890, 0, 0, '2025-11-03 09:58:07'),
(15, 10, 74, 'derrota', -20, 870, 0, 0, '2025-11-03 09:58:08'),
(16, 10, 74, 'derrota', -20, 850, 0, 0, '2025-11-03 09:58:08'),
(17, 10, 74, 'derrota', -20, 830, 0, 0, '2025-11-03 09:58:09'),
(18, 10, 74, 'derrota', -20, 810, 0, 0, '2025-11-03 09:58:09'),
(19, 10, 74, 'derrota', -20, 790, 0, 0, '2025-11-03 09:58:24'),
(20, 9, 77, 'derrota', -20, 1110, 0, 0, '2025-11-03 10:06:24'),
(21, 9, 77, 'derrota', -20, 1090, 0, 0, '2025-11-03 10:06:26'),
(22, 9, 77, 'derrota', -20, 1070, 0, 0, '2025-11-03 10:06:29'),
(23, 9, 77, 'derrota', -20, 1050, 0, 0, '2025-11-03 10:06:31'),
(24, 10, 77, 'empate', 0, 1000, 0, 0, '2025-11-03 10:18:41'),
(31, 9, 84, 'victoria', 50, 50, 0, 0, '2025-11-03 12:02:53'),
(32, 10, 84, 'derrota', -20, 0, 0, 0, '2025-11-03 12:02:53'),
(43, 9, 108, 'victoria', 50, 10100, 0, 0, '2025-11-03 14:31:21'),
(44, 10, 108, 'derrota', -20, 9950, 0, 0, '2025-11-03 14:31:21'),
(77, 9, 111, 'victoria', 50, 10490, 0, 0, '2025-11-03 16:23:45'),
(78, 10, 111, 'derrota', -20, 9910, 0, 0, '2025-11-03 16:23:45'),
(79, 9, 111, 'victoria', 50, 10540, 0, 0, '2025-11-03 16:23:45'),
(80, 10, 111, 'derrota', -20, 9890, 0, 0, '2025-11-03 16:23:45'),
(81, 9, 111, 'victoria', 50, 10590, 0, 0, '2025-11-03 16:26:37'),
(82, 10, 111, 'derrota', -20, 9870, 0, 0, '2025-11-03 16:26:37'),
(97, 10, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:27:11'),
(98, 16, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:27:11'),
(99, 9, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:28:14'),
(100, 10, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:28:14'),
(101, 9, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:28:22'),
(102, 10, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:28:22'),
(103, 9, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:37:01'),
(104, 10, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:37:01'),
(105, 9, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 08:37:02'),
(106, 10, 127, 'victoria', 50, 50, 0, 0, '2025-11-04 08:37:02'),
(107, 9, 127, 'empate', 0, 0, 0, 0, '2025-11-04 08:37:29'),
(108, 10, 127, 'empate', 0, 50, 0, 0, '2025-11-04 08:37:29'),
(109, 9, 127, 'victoria', 50, 50, 0, 0, '2025-11-04 08:37:29'),
(110, 10, 127, 'derrota', -20, 30, 0, 0, '2025-11-04 08:37:29'),
(111, 9, 127, 'victoria', 50, 100, 0, 0, '2025-11-04 12:43:53'),
(112, 10, 127, 'derrota', -20, 10, 0, 0, '2025-11-04 12:43:53'),
(113, 9, 127, 'victoria', 50, 150, 0, 0, '2025-11-04 12:43:53'),
(114, 10, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 12:43:53'),
(115, 9, 127, 'victoria', 50, 200, 0, 0, '2025-11-04 12:43:54'),
(116, 10, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 12:43:54'),
(117, 9, 127, 'victoria', 50, 50, 0, 0, '2025-11-04 12:48:48'),
(118, 10, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 12:48:48'),
(119, 9, 127, 'victoria', 50, 100, 0, 0, '2025-11-04 12:48:48'),
(120, 10, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 12:48:48'),
(121, 9, 127, 'victoria', 50, 150, 0, 0, '2025-11-04 12:48:48'),
(122, 10, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 12:48:48'),
(123, 9, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 13:10:23'),
(124, 10, 127, 'victoria', 50, 50, 0, 0, '2025-11-04 13:10:23'),
(125, 9, 127, 'victoria', 50, 50, 0, 0, '2025-11-04 13:48:22'),
(126, 10, 127, 'derrota', -20, 30, 0, 0, '2025-11-04 13:48:22'),
(127, 9, 127, 'victoria', 50, 100, 0, 0, '2025-11-04 14:41:54'),
(128, 10, 127, 'derrota', -20, 10, 0, 0, '2025-11-04 14:41:54'),
(129, 19, 127, 'victoria', 50, 50, 0, 0, '2025-11-04 14:41:54'),
(130, 9, 127, 'derrota', -20, 80, 0, 0, '2025-11-04 14:47:09'),
(131, 10, 127, 'derrota', -20, 0, 0, 0, '2025-11-04 14:47:09'),
(132, 19, 127, 'victoria', 50, 150, 0, 0, '2025-11-04 14:47:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `levels`
--

CREATE TABLE `levels` (
  `level_id` int(11) NOT NULL,
  `name` varchar(80) DEFAULT NULL,
  `min_points` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `levels`
--

INSERT INTO `levels` (`level_id`, `name`, `min_points`) VALUES
(1, 'Nivel 1', 0),
(2, 'Nivel 2', 100),
(3, 'Nivel 3', 300),
(4, 'Nivel 4', 400),
(5, 'Nivel 5', 500),
(6, 'Nivel 6', 600),
(7, 'Nivel 7', 700),
(8, 'Nivel 8', 800),
(9, 'Nivel 9', 900),
(10, 'Nivel 10', 1000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `name`, `description`) VALUES
(1, 'Admin', 'Administrador del sistema'),
(2, 'Jugador', 'Usuario con rol de jugador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rooms`
--

CREATE TABLE `rooms` (
  `id_room` int(11) NOT NULL,
  `world_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `level_required` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rooms`
--

INSERT INTO `rooms` (`id_room`, `world_id`, `created_at`, `level_required`) VALUES
(1, 1, '2025-11-02 08:08:43', 1),
(2, 1, '2025-11-02 08:08:43', 1),
(7, 2, '2025-11-02 08:08:43', 5),
(8, 2, '2025-11-02 08:08:43', 5),
(13, 3, '2025-11-02 08:08:43', 10),
(30, 3, '2025-11-03 18:27:16', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rooms_backup`
--

CREATE TABLE `rooms_backup` (
  `id_room` int(11) NOT NULL DEFAULT 0,
  `world_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `level_required` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rooms_backup`
--

INSERT INTO `rooms_backup` (`id_room`, `world_id`, `created_at`, `level_required`) VALUES
(1, 1, '2025-10-01 13:04:16', 1),
(2, 2, '2025-10-27 11:04:31', 5),
(3, 3, '2025-10-27 11:04:33', 10),
(10, 1, '2025-11-02 07:59:54', 1),
(11, 1, '2025-11-02 07:59:54', 1),
(12, 1, '2025-11-02 07:59:54', 1),
(13, 1, '2025-11-02 07:59:54', 1),
(14, 1, '2025-11-02 07:59:54', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `room_players`
--

CREATE TABLE `room_players` (
  `id_room_player` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_games` int(11) DEFAULT NULL,
  `join_date` datetime DEFAULT NULL,
  `is_alive` tinyint(1) DEFAULT NULL,
  `current_hp` int(11) DEFAULT NULL,
  `ready` tinyint(1) DEFAULT 0,
  `team` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','left','eliminated') DEFAULT 'active',
  `last_active` datetime DEFAULT current_timestamp(),
  `active_state` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `room_players`
--

INSERT INTO `room_players` (`id_room_player`, `id_user`, `id_games`, `join_date`, `is_alive`, `current_hp`, `ready`, `team`, `status`, `last_active`, `active_state`) VALUES
(371, 10, 127, '2025-11-04 14:39:45', 0, 0, 1, 2, 'active', '2025-11-04 14:41:40', 'inactive'),
(372, 19, 127, '2025-11-04 14:46:23', 1, 65, 1, 1, 'active', '2025-11-04 14:46:37', 'active'),
(373, 9, 127, '2025-11-04 14:46:24', 0, 0, 1, 2, 'active', '2025-11-04 14:46:38', 'active');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status`
--

CREATE TABLE `status` (
  `id_status` int(11) NOT NULL,
  `name` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `status`
--

INSERT INTO `status` (`id_status`, `name`) VALUES
(1, 'Activo'),
(2, 'Inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `id_avatar` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `level_id` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `id_status` int(11) DEFAULT NULL,
  `id_weapons` int(11) DEFAULT NULL,
  `id_world` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `id_rol`, `id_avatar`, `points`, `level_id`, `last_login`, `id_status`, `id_weapons`, `id_world`) VALUES
(9, 'duvan', 'mileswatney@gmail.com', '$2y$10$5CdirFP.0J3tke0i3Ggz7ORcT/KBOgDCP6EW/JQCHgpRMXxbXtqQe', 2, 3, 0, 1, '2025-10-22 07:45:13', 1, 6, 1),
(10, 'cris', 'cris@gmial.com', '$2y$10$ddv55uYaHNnAfrsPb8Bzo.9WMiQGVXHn6ebH7mLr7xrdObeiyoQY6', 2, 1, 0, 1, '2025-10-27 13:27:31', 1, 6, 1),
(16, 'ADMIN1', 'duvan@gmail.com', '$2y$10$hYIPompB2wMmjr.nZL0jW.EyZWSJQe.8cA6E17/ePBywJzZCbaviq', 1, 1, 0, 1, '2025-11-03 13:13:53', 1, 6, 1),
(19, 'alex921', 'alex921@gmail.com', '$2y$10$cCfYZNM8HhLYC51OeYY9E.NNF47Uq/AsP6oIvV/YVEjmGsLAi18Sy', 2, 1, 0, 2, '2025-11-04 13:57:30', 1, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `weapons`
--

CREATE TABLE `weapons` (
  `id_weapons` int(11) NOT NULL,
  `name` varchar(80) DEFAULT NULL,
  `subtype` varchar(80) DEFAULT NULL,
  `bullets` int(11) DEFAULT NULL,
  `damage` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `level_arm` int(11) DEFAULT NULL,
  `id_damage_body` int(11) DEFAULT NULL,
  `id_type` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `weapons`
--

INSERT INTO `weapons` (`id_weapons`, `name`, `subtype`, `bullets`, `damage`, `image_url`, `level_arm`, `id_damage_body`, `id_type`) VALUES
(1, 'Rifle de asalto', 'Asalto', 30, 30, 'Hydra.png', 3, 2, 3),
(2, 'Escopeta Tactica', 'Escopeta', 12, 50, 'magnum.png', 2, 1, 2),
(3, 'Espada de Energia', 'Cuerpo a cuerpo', 10, 100, 'energy_sword.png', 1, 3, 1),
(4, 'Puños de Energia', 'Cuerpo a cuerpo', 10, 120, 'puños_energia.png', 3, 3, 1),
(5, 'Martillo Gravitatorio', 'Cuerpo a cuerpo', 10, 130, 'martillo_gravitatorio.png', 6, 3, 1),
(6, 'Pistola Magnum', 'Pistola', 12, 45, 'pistola_magnum.png', 1, 1, 2),
(7, 'Needler', 'Asalto', 20, 45, 'needler.png', 4, 2, 2),
(8, 'Covenant Carbine', 'Asalto', 18, 40, 'covenant_carbine.png', 5, 2, 3),
(9, 'SRS99-S5 AM', 'Francotirador', 4, 95, 'srs99_s5_am.png', 9, 3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `weapon_types`
--

CREATE TABLE `weapon_types` (
  `id_type` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `weapon_types`
--

INSERT INTO `weapon_types` (`id_type`, `type_name`) VALUES
(1, 'corto_alcance'),
(2, 'medio_alcance'),
(3, 'largo_alcance');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `weapon_videos`
--

CREATE TABLE `weapon_videos` (
  `id_video` int(11) NOT NULL,
  `id_weapon` int(11) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `weapon_videos`
--

INSERT INTO `weapon_videos` (`id_video`, `id_weapon`, `video_url`, `description`) VALUES
(1, 1, 'videos/rifle_de_asalto.mp4', 'Rifle de asalto - r?faga'),
(2, 2, 'videos/escopeta_tactica.mp4', 'Escopeta T?ctica - disparo de corto alcance'),
(3, 3, 'videos/espada_de_energia.mp4', 'Espada de energ?a - cuerpo a cuerpo'),
(4, 4, 'videos/pu?os_de_energia.mp4', 'Pu?os de energ?a - cuerpo a cuerpo'),
(5, 5, 'videos/martillo_gravitatorio.mp4', 'Martillo gravitatorio - cuerpo a cuerpo'),
(6, 6, 'videos/pistola_magnum.mp4', 'Pistola Magnum - disparo ?nico potente'),
(7, 7, 'videos/needler.mp4', 'Needler - proyectiles explosivos'),
(8, 8, 'videos/covenant_carbine.mp4', 'Covenant Carbine - asalto de precisi?n'),
(9, 9, 'videos/srs99_s5_am.mp4', 'SRS99-S5 AM - francotirador de largo alcance');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `worlds`
--

CREATE TABLE `worlds` (
  `id_world` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `image` varchar(250) DEFAULT NULL,
  `required_level` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `worlds`
--

INSERT INTO `worlds` (`id_world`, `name`, `image`, `required_level`) VALUES
(1, 'Breaker', 'img/mapas/breaker.jpeg', 1),
(2, 'Live Fire', 'img/mapas/live_fire.jpeg', 5),
(3, 'Recharge', 'img/mapas/recharge.jpeg', 10);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `avatars`
--
ALTER TABLE `avatars`
  ADD PRIMARY KEY (`id_avatar`);

--
-- Indices de la tabla `battle_details`
--
ALTER TABLE `battle_details`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_game_events` (`id_game_events`),
  ADD KEY `id_attacker` (`id_attacker`),
  ADD KEY `id_target` (`id_target`),
  ADD KEY `id_weapons` (`id_weapons`),
  ADD KEY `id_damage_body` (`id_damage_body`),
  ADD KEY `id_game` (`id_game`);

--
-- Indices de la tabla `damage_part_body`
--
ALTER TABLE `damage_part_body`
  ADD PRIMARY KEY (`id_damage_body`);

--
-- Indices de la tabla `detail_room_players_user`
--
ALTER TABLE `detail_room_players_user`
  ADD PRIMARY KEY (`id_detail_room_player_user`),
  ADD KEY `id_room_player` (`id_room_player`),
  ADD KEY `id_game_events` (`id_game_events`);

--
-- Indices de la tabla `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id_games`),
  ADD KEY `id_room` (`id_room`);

--
-- Indices de la tabla `game_events`
--
ALTER TABLE `game_events`
  ADD PRIMARY KEY (`id_game_events`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `weapon_id` (`weapon_id`);

--
-- Indices de la tabla `historial_partidas`
--
ALTER TABLE `historial_partidas`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_user` (`id_user`),
  ADD KEY `fk_historial_game` (`id_game`);

--
-- Indices de la tabla `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`level_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id_room`),
  ADD KEY `world_id` (`world_id`);

--
-- Indices de la tabla `room_players`
--
ALTER TABLE `room_players`
  ADD PRIMARY KEY (`id_room_player`),
  ADD KEY `id_games` (`id_games`),
  ADD KEY `fk_room_players_user` (`id_user`);

--
-- Indices de la tabla `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id_status`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `level_id` (`level_id`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `users_ibfk_2` (`id_avatar`),
  ADD KEY `fk_user_world` (`id_world`);

--
-- Indices de la tabla `weapons`
--
ALTER TABLE `weapons`
  ADD PRIMARY KEY (`id_weapons`),
  ADD KEY `id_damage_body` (`id_damage_body`),
  ADD KEY `fk_weapons_type` (`id_type`);

--
-- Indices de la tabla `weapon_types`
--
ALTER TABLE `weapon_types`
  ADD PRIMARY KEY (`id_type`);

--
-- Indices de la tabla `weapon_videos`
--
ALTER TABLE `weapon_videos`
  ADD PRIMARY KEY (`id_video`),
  ADD UNIQUE KEY `uk_weapon` (`id_weapon`);

--
-- Indices de la tabla `worlds`
--
ALTER TABLE `worlds`
  ADD PRIMARY KEY (`id_world`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `avatars`
--
ALTER TABLE `avatars`
  MODIFY `id_avatar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `battle_details`
--
ALTER TABLE `battle_details`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `damage_part_body`
--
ALTER TABLE `damage_part_body`
  MODIFY `id_damage_body` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detail_room_players_user`
--
ALTER TABLE `detail_room_players_user`
  MODIFY `id_detail_room_player_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `games`
--
ALTER TABLE `games`
  MODIFY `id_games` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT de la tabla `game_events`
--
ALTER TABLE `game_events`
  MODIFY `id_game_events` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `historial_partidas`
--
ALTER TABLE `historial_partidas`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT de la tabla `levels`
--
ALTER TABLE `levels`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id_room` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `room_players`
--
ALTER TABLE `room_players`
  MODIFY `id_room_player` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=374;

--
-- AUTO_INCREMENT de la tabla `status`
--
ALTER TABLE `status`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `weapons`
--
ALTER TABLE `weapons`
  MODIFY `id_weapons` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `weapon_types`
--
ALTER TABLE `weapon_types`
  MODIFY `id_type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `weapon_videos`
--
ALTER TABLE `weapon_videos`
  MODIFY `id_video` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `worlds`
--
ALTER TABLE `worlds`
  MODIFY `id_world` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `battle_details`
--
ALTER TABLE `battle_details`
  ADD CONSTRAINT `battle_details_ibfk_1` FOREIGN KEY (`id_game_events`) REFERENCES `game_events` (`id_game_events`),
  ADD CONSTRAINT `battle_details_ibfk_2` FOREIGN KEY (`id_attacker`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `battle_details_ibfk_3` FOREIGN KEY (`id_target`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `battle_details_ibfk_4` FOREIGN KEY (`id_weapons`) REFERENCES `weapons` (`id_weapons`),
  ADD CONSTRAINT `battle_details_ibfk_5` FOREIGN KEY (`id_damage_body`) REFERENCES `damage_part_body` (`id_damage_body`),
  ADD CONSTRAINT `battle_details_ibfk_6` FOREIGN KEY (`id_game`) REFERENCES `games` (`id_games`);

--
-- Filtros para la tabla `detail_room_players_user`
--
ALTER TABLE `detail_room_players_user`
  ADD CONSTRAINT `detail_room_players_user_ibfk_1` FOREIGN KEY (`id_room_player`) REFERENCES `room_players` (`id_room_player`),
  ADD CONSTRAINT `detail_room_players_user_ibfk_2` FOREIGN KEY (`id_game_events`) REFERENCES `game_events` (`id_game_events`);

--
-- Filtros para la tabla `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`id_room`) REFERENCES `rooms` (`id_room`);

--
-- Filtros para la tabla `game_events`
--
ALTER TABLE `game_events`
  ADD CONSTRAINT `game_events_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id_games`),
  ADD CONSTRAINT `game_events_ibfk_2` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id_weapons`);

--
-- Filtros para la tabla `historial_partidas`
--
ALTER TABLE `historial_partidas`
  ADD CONSTRAINT `fk_historial_game` FOREIGN KEY (`id_game`) REFERENCES `games` (`id_games`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`world_id`) REFERENCES `worlds` (`id_world`);

--
-- Filtros para la tabla `room_players`
--
ALTER TABLE `room_players`
  ADD CONSTRAINT `fk_room_players_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `room_players_ibfk_1` FOREIGN KEY (`id_games`) REFERENCES `games` (`id_games`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_world` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id_world`),
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`id_avatar`) REFERENCES `avatars` (`id_avatar`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`level_id`) REFERENCES `levels` (`level_id`),
  ADD CONSTRAINT `users_ibfk_4` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `weapons`
--
ALTER TABLE `weapons`
  ADD CONSTRAINT `fk_weapons_type` FOREIGN KEY (`id_type`) REFERENCES `weapon_types` (`id_type`) ON UPDATE CASCADE,
  ADD CONSTRAINT `weapons_ibfk_1` FOREIGN KEY (`id_damage_body`) REFERENCES `damage_part_body` (`id_damage_body`);

--
-- Filtros para la tabla `weapon_videos`
--
ALTER TABLE `weapon_videos`
  ADD CONSTRAINT `weapon_videos_ibfk_1` FOREIGN KEY (`id_weapon`) REFERENCES `weapons` (`id_weapons`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
