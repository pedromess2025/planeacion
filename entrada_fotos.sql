-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-01-2026 a las 22:37:33
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
-- Estructura de tabla para la tabla `entrada_fotos`
--

CREATE TABLE `entrada_fotos` (
  `id` int(11) NOT NULL,
  `id_regEntrada` int(11) NOT NULL,
  `ruta` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci;

--
-- Volcado de datos para la tabla `entrada_fotos`
--

INSERT INTO `entrada_fotos` (`id`, `id_regEntrada`, `ruta`) VALUES
(1, 1, 'imgEntradas/entrada_1_1768865938_0'),
(2, 2, 'imgEntradas/entrada_2_1768866423_0'),
(3, 3, 'imgEntradas/entrada_3_1768866601_0'),
(4, 4, 'imgEntradas/entrada_4_1769030644_0'),
(5, 5, 'imgEntradas/entrada_5_1769031790_0'),
(6, 6, 'imgEntradas/entrada_6_1769033783_0');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `entrada_fotos`
--
ALTER TABLE `entrada_fotos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `entrada_fotos`
--
ALTER TABLE `entrada_fotos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
