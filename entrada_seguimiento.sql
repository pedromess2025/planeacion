-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-01-2026 a las 22:37:00
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
-- Estructura de tabla para la tabla `entrada_seguimiento`
--

CREATE TABLE `entrada_seguimiento` (
  `id_seguimiento` int(11) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `id_usuario_nota` int(11) NOT NULL,
  `nota` text NOT NULL,
  `fecha_seguimiento` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `entrada_seguimiento`
--

INSERT INTO `entrada_seguimiento` (`id_seguimiento`, `id_registro`, `id_usuario_nota`, `nota`, `fecha_seguimiento`) VALUES
(1, 3, 199, 'Prueba 1 Manual', '2026-01-21 19:49:55'),
(2, 6, 199, 'se retraso', '2026-01-22 21:03:46'),
(3, 6, 199, 'se estima 3 dias', '2026-01-22 21:11:25'),
(4, 1, 199, 'se retraso por capacidad del lab', '2026-01-22 21:22:23'),
(5, 2, 199, 'pruebajtdj', '2026-01-22 21:33:38');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entrada_seguimiento`
--
ALTER TABLE `entrada_seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD KEY `id_registro` (`id_registro`),
  ADD KEY `id_usuario_nota` (`id_usuario_nota`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entrada_seguimiento`
--
ALTER TABLE `entrada_seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
