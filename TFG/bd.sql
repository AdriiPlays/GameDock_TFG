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
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.contenedores: ~1 rows (aproximadamente)
INSERT INTO `contenedores` (`id`, `nombre`, `iso`, `version`, `estado`, `fecha_creado`, `puerto`) VALUES
	(20, 'pruebaaaaaa', 'minecraft', 'LATEST', 'online', '2026-03-04 08:24:45', 25578);

-- Volcando estructura para tabla tfg.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) DEFAULT NULL,
  `accion` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.logs: ~79 rows (aproximadamente)
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
	(79, 'pepe', 'Eliminó el contenedor \'prueba\'', '2026-03-04 08:27:54');

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
