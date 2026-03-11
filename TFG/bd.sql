-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para tfg
CREATE DATABASE IF NOT EXISTS `tfg` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `tfg`;

-- Volcando estructura para tabla tfg.contenedores
CREATE TABLE IF NOT EXISTS `contenedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `iso` varchar(100) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `estado` enum('online','offline') DEFAULT 'offline',
  `fecha_creado` datetime DEFAULT current_timestamp(),
  `puerto` int(11) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.contenedores: ~0 rows (aproximadamente)

-- Volcando estructura para tabla tfg.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) DEFAULT NULL,
  `accion` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.logs: ~128 rows (aproximadamente)
INSERT INTO `logs` (`id`, `usuario`, `accion`, `fecha`) VALUES
	(1, 'pepe', 'Cerró sesión', '2026-02-20 12:55:00'),
	(2, 'pepe', 'Inició sesión', '2026-02-20 12:56:22'),
	(3, 'pepe', 'Inició sesión', '2026-02-20 12:56:25'),
	(4, 'pruebaaa', 'Inició sesión', '2026-02-20 12:57:04'),
	(5, 'pruebaaa', 'Cambió su foto de perfil', '2026-02-20 13:05:15'),
	(6, 'pepe', 'Inició sesión', '2026-02-24 08:13:40'),
	(7, 'pepe', 'Inició sesión', '2026-02-24 11:54:04'),
	(8, 'pepe', 'Creó el contenedor \'prueba\'', '2026-02-24 12:01:50'),
	(9, 'pepe', 'Creó el contenedor \'asdasdad\'', '2026-02-24 12:14:27'),
	(10, 'pepe', 'Creó el contenedor \'prueba\'', '2026-02-24 12:20:33'),
	(11, 'pepe', 'Inició sesión', '2026-02-24 12:54:43'),
	(12, 'pepe', 'Creó el contenedor Ubuntu \'sdfsfafa\'', '2026-02-24 12:59:09'),
	(13, 'pepe', 'Creó el contenedor Ubuntu \'faffaf\'', '2026-02-24 13:03:15'),
	(14, 'pepe', 'Eliminó el contenedor \'faffaf\'', '2026-02-24 13:03:20'),
	(15, 'pepe', 'Inició sesión', '2026-02-26 12:59:08'),
	(16, 'javi', 'Inició sesión', '2026-02-26 13:10:35'),
	(17, 'javi', 'Cerró sesión', '2026-02-26 13:19:41'),
	(18, 'pepe', 'Inició sesión', '2026-02-26 13:19:43'),
	(19, 'pepe', 'Cerró sesión', '2026-02-26 13:19:49'),
	(20, 'pepe', 'Inició sesión', '2026-02-26 13:20:30'),
	(21, 'pepe', 'Cerró sesión', '2026-02-26 13:20:46'),
	(22, 'pepe', 'Inició sesión', '2026-02-26 13:20:48'),
	(23, 'pepe', 'Cerró sesión', '2026-02-26 13:22:48'),
	(24, 'pepe', 'Inició sesión', '2026-02-26 13:22:49'),
	(25, 'pepe', 'Cerró sesión', '2026-02-26 13:22:51'),
	(26, 'pepe', 'Inició sesión', '2026-02-26 13:22:53'),
	(27, 'pepe', 'Creó el contenedor Ubuntu \'pruebaaaa\'', '2026-03-02 12:35:47'),
	(28, 'pepe', 'Eliminó el contenedor \'pruebaaaa\'', '2026-03-02 12:37:28'),
	(29, 'pepe', 'Creó el contenedor MariaDB \'02\'', '2026-03-02 12:51:39'),
	(30, 'pepe', 'Eliminó el contenedor MariaDB \'02\'', '2026-03-02 13:01:54'),
	(31, 'pepe', 'Creó el contenedor MariaDB \'sdfsfafa\'', '2026-03-02 13:02:14'),
	(32, 'pepe', 'Editó contenedor \'prueba\'', '2026-03-02 13:26:16'),
	(33, 'pepe', 'Contenedor eliminado: prueba', '2026-03-02 13:27:07'),
	(34, 'pepe', 'Creó el contenedor MariaDB \'pruebaaaaaa\'', '2026-03-02 13:29:35'),
	(35, 'pepe', 'Contenedor detenido: pruebaaaaaa', '2026-03-02 13:29:55'),
	(36, 'pepe', 'Contenedor detenido: pruebaaaaaa', '2026-03-02 13:29:55'),
	(37, 'pepe', 'Contenedor iniciado: pruebaaaaaa', '2026-03-02 13:30:11'),
	(38, 'pepe', 'Contenedor detenido: pruebaaaaaa', '2026-03-02 13:30:26'),
	(39, 'pepe', 'Inició sesión', '2026-03-03 11:48:40'),
	(40, 'pepe', 'Contenedor iniciado: pruebaaaaaa', '2026-03-03 11:48:49'),
	(41, 'pepe', 'Contenedor detenido: pruebaaaaaa', '2026-03-03 11:49:06'),
	(42, 'pepe', 'Creó el contenedor Ubuntu \'ubuntu\'', '2026-03-03 13:09:39'),
	(43, 'pepe', 'Creó el contenedor Ubuntu \'pruebaa\'', '2026-03-03 13:17:13'),
	(44, 'pepe', 'Eliminó el contenedor \'pruebaa\'', '2026-03-03 13:24:02'),
	(45, 'pepe', 'Creó el contenedor MariaDB \'asdasdad\'', '2026-03-03 13:47:23'),
	(46, 'pepe', 'Eliminó el contenedor MariaDB \'asdasdad\'', '2026-03-03 13:47:34'),
	(47, 'pepe', 'Creó servidor Minecraft \'prueba\'', '2026-03-03 14:18:14'),
	(48, 'pepe', 'Eliminó el contenedor \'prueba\'', '2026-03-03 14:18:45'),
	(49, 'pepe', 'Inició sesión', '2026-03-03 14:33:22'),
	(50, 'pepe', 'Creó servidor Minecraft \'prueba\'', '2026-03-03 14:33:30'),
	(51, 'pepe', 'Contenedor detenido: prueba', '2026-03-03 14:33:48'),
	(52, 'pepe', 'Contenedor detenido: prueba', '2026-03-03 14:33:48'),
	(53, 'pepe', 'Inició sesión', '2026-03-03 14:33:55'),
	(54, 'pepe', 'Eliminó el contenedor \'prueba\'', '2026-03-03 14:34:54'),
	(55, 'pepe', 'Creó servidor Minecraft \'prueba\'', '2026-03-03 14:53:03'),
	(56, 'pepe', 'Contenedor detenido: prueba', '2026-03-03 14:53:41'),
	(57, 'pepe', 'Contenedor iniciado: prueba', '2026-03-03 14:55:21'),
	(58, 'pepe', 'Contenedor detenido: prueba', '2026-03-03 14:56:08'),
	(59, 'pepe', 'Creó servidor Minecraft \'sdfsfafa\'', '2026-03-04 08:20:59'),
	(60, 'pepe', 'Eliminó el contenedor \'sdfsfafa\'', '2026-03-04 08:21:21'),
	(61, 'pepe', 'Creó el contenedor MariaDB \'sdfsfafa\'', '2026-03-04 08:21:41'),
	(62, 'pepe', 'Creó servidor Minecraft \'faffaf\'', '2026-03-04 08:21:53'),
	(63, 'pepe', 'Eliminó el contenedor \'faffaf\'', '2026-03-04 08:22:14'),
	(64, 'pepe', 'Eliminó el contenedor MariaDB \'sdfsfafa\'', '2026-03-04 08:22:20'),
	(65, 'pepe', 'Creó servidor Minecraft \'asdasdad\'', '2026-03-04 08:24:34'),
	(66, 'pepe', 'Creó servidor Minecraft \'pruebaaaaaa\'', '2026-03-04 08:24:45'),
	(67, 'pepe', 'Creó servidor Minecraft \'sdfsfafa\'', '2026-03-04 08:24:53'),
	(68, 'pepe', 'Creó servidor Minecraft \'faffaf\'', '2026-03-04 08:25:02'),
	(69, 'pepe', 'Contenedor detenido: faffaf', '2026-03-04 08:25:24'),
	(70, 'pepe', 'Contenedor detenido: sdfsfafa', '2026-03-04 08:25:48'),
	(71, 'pepe', 'Contenedor detenido: pruebaaaaaa', '2026-03-04 08:26:09'),
	(72, 'pepe', 'Creó servidor Minecraft \'faffaffghgfhf\'', '2026-03-04 08:26:21'),
	(73, 'pepe', 'Creó servidor Minecraft \'sdfsfafahfghfgh\'', '2026-03-04 08:26:38'),
	(74, 'pepe', 'Eliminó el contenedor \'sdfsfafahfghfgh\'', '2026-03-04 08:26:57'),
	(75, 'pepe', 'Eliminó el contenedor \'faffaffghgfhf\'', '2026-03-04 08:27:26'),
	(76, 'pepe', 'Eliminó el contenedor \'faffaf\'', '2026-03-04 08:27:40'),
	(77, 'pepe', 'Eliminó el contenedor \'sdfsfafa\'', '2026-03-04 08:27:45'),
	(78, 'pepe', 'Eliminó el contenedor \'asdasdad\'', '2026-03-04 08:27:51'),
	(79, 'pepe', 'Eliminó el contenedor \'prueba\'', '2026-03-04 08:27:54'),
	(80, 'pepe', 'Creó servidor Minecraft \'ADRIAN\'', '2026-03-04 14:01:47'),
	(81, 'pepe', 'Eliminó servidor \'ADRIAN\' y su volumen', '2026-03-04 14:03:43'),
	(82, 'pepe', 'Inició sesión', '2026-03-05 11:02:19'),
	(83, 'pepe', 'Inició sesión', '2026-03-09 13:16:04'),
	(84, 'pepe', 'Contenedor eliminado: pruebaaaaaa', '2026-03-09 13:26:29'),
	(85, 'pepe', 'Creó servidor Minecraft \'nuevo\'', '2026-03-09 13:26:39'),
	(86, 'pepe', 'Creó servidor Minecraft \'sdfsfafa\'', '2026-03-09 13:43:24'),
	(87, 'pepe', 'Contenedor eliminado: nuevo', '2026-03-09 13:43:46'),
	(88, 'pepe', 'Contenedor eliminado: nuevo', '2026-03-09 13:43:47'),
	(89, 'pepe', 'Contenedor eliminado: nuevo', '2026-03-09 13:43:47'),
	(90, 'pepe', 'Contenedor eliminado: nuevo', '2026-03-09 13:43:47'),
	(91, 'pepe', 'Contenedor detenido: sdfsfafa', '2026-03-09 13:59:00'),
	(92, 'pepe', 'Contenedor iniciado: sdfsfafa', '2026-03-09 13:59:04'),
	(93, 'pepe', 'Contenedor iniciado: prueba', '2026-03-09 14:12:52'),
	(94, 'pepe', 'Eliminó servidor \'prueba\' y su volumen', '2026-03-09 14:13:03'),
	(95, 'pepe', 'Creó servidor Minecraft \'nuevo\'', '2026-03-09 14:13:11'),
	(96, 'pepe', 'Contenedor iniciado: pepe', '2026-03-09 14:13:27'),
	(97, 'pepe', 'Eliminó servidor \'pepe\' y su volumen', '2026-03-09 14:14:47'),
	(98, 'pepe', 'Creó servidor Minecraft \'asdasdad\'', '2026-03-09 14:14:55'),
	(99, 'pepe', 'Contenedor detenido: 887678', '2026-03-09 14:17:10'),
	(100, 'pepe', 'Contenedor detenido: 887678', '2026-03-09 14:17:10'),
	(101, 'pepe', 'Contenedor iniciado: 887678', '2026-03-09 14:17:17'),
	(102, 'pepe', 'Eliminó servidor \'887678\' y su volumen', '2026-03-09 14:19:31'),
	(103, 'pepe', 'Inició sesión', '2026-03-10 07:45:50'),
	(104, 'pepe', 'Inició sesión', '2026-03-10 08:27:49'),
	(105, NULL, 'Creó el servidor Minecraft \'prueba\'', '2026-03-10 09:39:32'),
	(106, NULL, 'Detuvo el servidor \'prueba\'', '2026-03-10 09:40:27'),
	(107, NULL, 'Inició el servidor \'prueba\'', '2026-03-10 09:41:05'),
	(108, NULL, 'Reinició el servidor \'prueba\'', '2026-03-10 09:41:14'),
	(109, NULL, 'Detuvo el servidor \'prueba\'', '2026-03-10 09:44:58'),
	(110, NULL, 'Creó el servidor Minecraft \'pruebaa\'', '2026-03-10 09:47:08'),
	(111, 'pepe', 'Detuvo el servidor \'pruebaa\'', '2026-03-10 09:47:35'),
	(112, 'pepe', 'Inició el servidor \'pruebaa\'', '2026-03-10 09:48:34'),
	(113, 'pepe', 'Reinició el servidor \'pruebaa\'', '2026-03-10 09:48:52'),
	(114, 'pepe', 'Reinició el servidor \'pruebaa\'', '2026-03-10 09:49:05'),
	(115, 'pepe', 'Detuvo el servidor \'pruebaa\'', '2026-03-10 09:50:06'),
	(116, 'pepe', 'Inició el servidor \'pruebaa\'', '2026-03-10 09:50:10'),
	(117, 'pepe', 'Reinició el servidor \'pruebaa\'', '2026-03-10 09:50:24'),
	(118, 'pepe', 'Reinició el servidor \'pruebaa\'', '2026-03-10 09:50:35'),
	(119, NULL, 'Creó el servidor Minecraft \'PRUEBA23323\'', '2026-03-10 09:51:48'),
	(120, 'pepe', 'Creó el servidor Minecraft \'dadafas\'', '2026-03-10 09:54:54'),
	(121, 'pepe', 'Eliminó el servidor \'dadafas\'', '2026-03-10 09:55:13'),
	(122, 'pepe', 'Creó el servidor Minecraft \'prueba\'', '2026-03-10 09:56:08'),
	(123, 'pepe', 'Detuvo el servidor \'prueba\'', '2026-03-10 09:57:57'),
	(124, 'pepe', 'Eliminó completamente el servidor \'prueba\' (contenedor + volumen + BD)', '2026-03-10 10:06:00'),
	(125, 'pepe', 'Creó el servidor Minecraft \'prueba\'', '2026-03-10 10:08:12'),
	(126, 'pepe', 'Detuvo el servidor \'prueba\'', '2026-03-10 10:08:27'),
	(127, 'pepe', 'Eliminó completamente el servidor \'prueba\' (contenedor + volumen + BD)', '2026-03-10 10:08:30'),
	(128, 'pepe', 'Creó el servidor Minecraft \'prueba\'', '2026-03-10 10:10:38'),
	(129, 'pepe', 'Eliminó el servidor \'prueba\'', '2026-03-10 10:10:43'),
	(130, 'pepe', 'Cerró sesión', '2026-03-10 10:29:18'),
	(131, 'pepe', 'Inició sesión', '2026-03-10 10:29:26'),
	(132, 'pepe', 'Creó el servidor Minecraft \'prueba\'', '2026-03-10 11:53:59'),
	(133, 'pepe', 'Creó el servidor Minecraft \'yutu\'', '2026-03-10 11:55:05');

-- Volcando estructura para tabla tfg.minecraft
CREATE TABLE IF NOT EXISTS `minecraft` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `version` varchar(50) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `puerto` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `minecraft_ibfk_1` FOREIGN KEY (`id`) REFERENCES `contenedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.minecraft: ~0 rows (aproximadamente)

-- Volcando estructura para tabla tfg.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.usuarios: ~5 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `correo`, `imagen`, `admin`) VALUES
	(1, 'admuro', 'Adrian1212', 'asd@gmail.com', NULL, 1),
	(2, 'adrian', '$2y$10$8u2tQ0xwQJ7q8X9YQ0ZlUu4QJx9x9QJx9x9QJx9x9QJx9x9', 'adrian@example.com', NULL, 0),
	(4, 'prueba', '$2y$10$njMlk1f1NG0hP3IiS4hTLOrdu5b5TnfRGEETlIpCdTtQRhgRmACXu', 'asd1@gmail.com', NULL, 0),
	(6, 'pepe', '$2y$10$78lbZze9VKxi7EyD.CXJSuCNnCx1qZUfr4H9rUVU/kjjN.D7ly8Bu', 'asd2@gmail.com', '1771583245_exterior-4 (1).png', 1),
	(7, 'pruebaaa', '$2y$10$hvoKdZkpCi1cHgA3Z2h.SuxCzDfrof2SsnAH/ot/fFl..OSTrOm5m', 'dsadasd@gmail.com', '1771589115_istockphoto-1473771646-612x612.jpg', 0),
	(8, 'javi', '$2y$10$M6ZcSruSItuEG1/zjwGxguJJbzo7rjeFF4cN5zcvW3KGtd7r7nqD6', 'javiasd@gmail.com', NULL, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
