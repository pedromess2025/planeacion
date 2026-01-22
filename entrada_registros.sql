-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-01-2026 a las 22:37:22
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
-- Base de datos: `mess_rrhh`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrada_registros`
--

CREATE TABLE `entrada_registros` (
  `id_registro` int(11) NOT NULL,
  `cliente` varchar(255) NOT NULL,
  `area` varchar(100) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `no_serie` varchar(100) DEFAULT NULL,
  `notas_recepcion` text DEFAULT NULL,
  `fecha_promesa_entrega` date DEFAULT NULL,
  `fotos_ruta` varchar(500) DEFAULT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `estatus` varchar(500) DEFAULT '1' COMMENT 'Recibido, Diagnostico, Reparacion, Espera de refacciones, En calibracion, Terminado',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fechaTermino` datetime DEFAULT NULL,
  `fecha_asignacion` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `entrada_registros`
--

INSERT INTO `entrada_registros` (`id_registro`, `cliente`, `area`, `marca`, `modelo`, `no_serie`, `notas_recepcion`, `fecha_promesa_entrega`, `fotos_ruta`, `id_usuario_asignado`, `estatus`, `fecha_registro`, `fechaTermino`, `fecha_asignacion`) VALUES
(1, 'MESS-01', 'SFG', 'PRUEBA', 'PRUEBA', '', 'hgfjhgj', '2026-01-21', '', 199, 'REPARACION', '2026-01-19 23:38:58', '2026-01-29 00:00:00', '2026-01-21'),
(2, 'MESS-02', 'SFG', 'PRUEBA', 'PRUEBA', '200', 'gfdhvmjf', '2026-01-22', '', 199, 'REPARACION', '2026-01-19 23:47:03', '2026-01-28 00:00:00', '2026-01-21'),
(3, 'MESS-04', 'SFG', 'PRUEBA', 'PRUEBA', '400', 'mhtdjyc', '2026-01-30', '', 199, 'Recibido', '2026-01-19 23:50:01', '2026-01-23 00:00:00', '2026-01-21'),
(4, 'MESS-04', 'SFG', 'Multímetro Digital 87V', 'Fluke', 'FL-992011', 'Prueba 1', '2026-01-29', '', 199, 'Recibido', '2026-01-21 21:24:04', '2026-01-29 00:00:00', '2026-01-21'),
(5, 'MESS-05', 'SFG', 'Multímetro Digital 87V', 'Fluke', 'FL-992011', 'Prueba 2 ', '2026-01-27', '', 199, 'Recibido', '2026-01-21 21:43:10', '2026-01-27 00:00:00', '2026-01-22'),
(6, 'MESS-06', 'SFG', 'Multímetro Digital 87V', 'Fluke', 'FL-992011', 'Prueba con cambios us y fecha', '2026-01-28', '', 199, NULL, '2026-01-21 22:16:23', '2026-01-28 00:00:00', '2026-01-21');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entrada_registros`
--
ALTER TABLE `entrada_registros`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario_asignado` (`id_usuario_asignado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entrada_registros`
--
ALTER TABLE `entrada_registros`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
