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
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.contenedores: ~0 rows (aproximadamente)

-- Volcando estructura para tabla tfg.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) DEFAULT NULL,
  `accion` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.logs: ~14 rows (aproximadamente)
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
	(14, 'pepe', 'Eliminó el contenedor \'faffaf\'', '2026-02-24 13:03:20');

-- Volcando estructura para tabla tfg.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfg.usuarios: ~5 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `correo`, `imagen`) VALUES
	(1, 'admuro', 'Adrian1212', 'asd@gmail.com', NULL),
	(2, 'adrian', '$2y$10$8u2tQ0xwQJ7q8X9YQ0ZlUu4QJx9x9QJx9x9QJx9x9QJx9x9', 'adrian@example.com', NULL),
	(4, 'prueba', '$2y$10$njMlk1f1NG0hP3IiS4hTLOrdu5b5TnfRGEETlIpCdTtQRhgRmACXu', 'asd1@gmail.com', NULL),
	(6, 'pepe', '$2y$10$78lbZze9VKxi7EyD.CXJSuCNnCx1qZUfr4H9rUVU/kjjN.D7ly8Bu', 'asd2@gmail.com', '1771583245_exterior-4 (1).png'),
	(7, 'pruebaaa', '$2y$10$hvoKdZkpCi1cHgA3Z2h.SuxCzDfrof2SsnAH/ot/fFl..OSTrOm5m', 'dsadasd@gmail.com', '1771589115_istockphoto-1473771646-612x612.jpg');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
