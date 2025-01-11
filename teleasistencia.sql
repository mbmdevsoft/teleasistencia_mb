-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-01-2025 a las 13:26:31
-- Versión del servidor: 10.4.25-MariaDB
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `teleasistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agendas`
--

CREATE TABLE `agendas` (
  `id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `beneficiario_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `fecha_programada` datetime DEFAULT NULL,
  `descripcion` text NOT NULL,
  `notas` text DEFAULT NULL,
  `estado_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda_estado`
--

CREATE TABLE `agenda_estado` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `agenda_estado`
--

INSERT INTO `agenda_estado` (`id`, `descripcion`) VALUES
(1, 'PENDIENTE'),
(2, 'REALIZADA'),
(3, 'CANCELADA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda_tipo`
--

CREATE TABLE `agenda_tipo` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `agenda_tipo`
--

INSERT INTO `agenda_tipo` (`id`, `descripcion`) VALUES
(1, 'MEDICA'),
(2, 'SOCIAL'),
(3, 'RECORDATORIO'),
(4, 'OTROS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `beneficiarios`
--

CREATE TABLE `beneficiarios` (
  `id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `nif_nie` varchar(15) DEFAULT NULL,
  `numero_expediente` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('H','M') NOT NULL,
  `vive_solo` tinyint(1) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `codigo_postal` varchar(5) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `poblacion` varchar(100) DEFAULT NULL,
  `telefono_fijo` varchar(15) DEFAULT NULL,
  `telefono_movil` varchar(15) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `centro_salud` varchar(200) DEFAULT NULL,
  `estado_id` int(11) NOT NULL,
  `enfermedades` text DEFAULT NULL,
  `alergias` text DEFAULT NULL,
  `medicacion` text DEFAULT NULL,
  `intervenciones` text DEFAULT NULL,
  `dieta` text DEFAULT NULL,
  `otras` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `beneficiario_estado`
--

CREATE TABLE `beneficiario_estado` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `beneficiario_estado`
--

INSERT INTO `beneficiario_estado` (`id`, `descripcion`) VALUES
(1, 'ACTIVO'),
(2, 'BAJA'),
(3, 'SUSPENDIDO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicaciones`
--

CREATE TABLE `comunicaciones` (
  `id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `beneficiario_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('ENTRANTE','SALIENTE') NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `duracion_minutos` int(11) NOT NULL DEFAULT 0,
  `motivo_llamada` text NOT NULL,
  `resolucion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicacion_categoria`
--

CREATE TABLE `comunicacion_categoria` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `comunicacion_categoria`
--

INSERT INTO `comunicacion_categoria` (`id`, `descripcion`) VALUES
(1, 'INFORMATIVAS'),
(2, 'DE EMERGENCIA'),
(3, 'SUGERENCIAS/RECLAMACIONES'),
(4, 'AGENDA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `beneficiario_id` int(11) NOT NULL,
  `parentesco_id` int(11) NOT NULL,
  `orden_prioridad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(200) NOT NULL,
  `tiene_llave` tinyint(1) DEFAULT 0,
  `direccion` varchar(255) DEFAULT NULL,
  `distancia_metros` int(11) DEFAULT NULL,
  `telefono_fijo` varchar(15) DEFAULT NULL,
  `telefono_movil` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `horario_disponibilidad` time DEFAULT NULL,
  `centro_trabajo` varchar(255) DEFAULT NULL,
  `telefono_trabajo` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto_parentesco`
--

CREATE TABLE `contacto_parentesco` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `contacto_parentesco`
--

INSERT INTO `contacto_parentesco` (`id`, `descripcion`) VALUES
(1, 'CONYUGE'),
(2, 'PADRE'),
(3, 'MADRE'),
(4, 'HERMAN@'),
(5, 'ABUELO@'),
(6, 'SUEGR@'),
(7, 'CUÑAD@'),
(8, 'PRIM@'),
(9, 'TI@'),
(10, 'VECIN@'),
(11, 'AMIG@'),
(12, 'CUIDADOR'),
(13, 'OTROS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `fecha_creacion`, `username`, `password`, `nombre`, `email`) VALUES
(1, '2024-11-09 17:23:16', 'root', '$2y$10$1NcHLQMYml9ZwDd3LInHSOaZ.CHI4if5lN8yxrfh16wy8/aVTOXQ2', 'admin', 'monicobellon@hotmail.es'),
(3, '2024-11-10 05:58:30', 'monico', '$2y$10$BNkHq2vH4yhUeTcyWsuEt.TQh7Xz13V4CvHjSNRtlp2UQrfEpOJkW', 'Monico Bellon Morales', 'monicobellon@hotmail.es'),
(23, '2024-11-10 05:58:30', 'joseluis', '$2y$10$BNkHq2vH4yhUeTcyWsuEt.TQh7Xz13V4CvHjSNRtlp2UQrfEpOJkW', 'Jose Luis Osorio', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beneficiario_id` (`beneficiario_id`),
  ADD KEY `creado_por` (`usuario_id`),
  ADD KEY `idx_agendas_fecha` (`fecha_programada`),
  ADD KEY `agendas_ibfk_3` (`tipo_id`),
  ADD KEY `agendas_ibfk_5` (`estado_id`);

--
-- Indices de la tabla `agenda_estado`
--
ALTER TABLE `agenda_estado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `agenda_tipo`
--
ALTER TABLE `agenda_tipo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `beneficiarios`
--
ALTER TABLE `beneficiarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nif_nie` (`nif_nie`),
  ADD UNIQUE KEY `numero_expediente` (`numero_expediente`),
  ADD KEY `idx_beneficiarios_nif` (`nif_nie`),
  ADD KEY `idx_beneficiarios_expediente` (`numero_expediente`),
  ADD KEY `beneficiarios_ibfk_1` (`estado_id`);

--
-- Indices de la tabla `beneficiario_estado`
--
ALTER TABLE `beneficiario_estado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunicaciones`
--
ALTER TABLE `comunicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `beneficiario_id` (`beneficiario_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `comunicaciones_ibfk_4` (`tipo`),
  ADD KEY `comunicaciones_ibfk_5` (`categoria_id`);

--
-- Indices de la tabla `comunicacion_categoria`
--
ALTER TABLE `comunicacion_categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contactos_beneficiario` (`beneficiario_id`),
  ADD KEY `contactos_ibfk_2` (`parentesco_id`);

--
-- Indices de la tabla `contacto_parentesco`
--
ALTER TABLE `contacto_parentesco`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `email` (`email`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `agenda_estado`
--
ALTER TABLE `agenda_estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `agenda_tipo`
--
ALTER TABLE `agenda_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `beneficiarios`
--
ALTER TABLE `beneficiarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `beneficiario_estado`
--
ALTER TABLE `beneficiario_estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `comunicaciones`
--
ALTER TABLE `comunicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `comunicacion_categoria`
--
ALTER TABLE `comunicacion_categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `contacto_parentesco`
--
ALTER TABLE `contacto_parentesco`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agendas`
--
ALTER TABLE `agendas`
  ADD CONSTRAINT `agendas_ibfk_1` FOREIGN KEY (`beneficiario_id`) REFERENCES `beneficiarios` (`id`),
  ADD CONSTRAINT `agendas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `agendas_ibfk_3` FOREIGN KEY (`tipo_id`) REFERENCES `agenda_tipo` (`id`),
  ADD CONSTRAINT `agendas_ibfk_5` FOREIGN KEY (`estado_id`) REFERENCES `agenda_estado` (`id`);

--
-- Filtros para la tabla `beneficiarios`
--
ALTER TABLE `beneficiarios`
  ADD CONSTRAINT `beneficiarios_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `beneficiario_estado` (`id`);

--
-- Filtros para la tabla `comunicaciones`
--
ALTER TABLE `comunicaciones`
  ADD CONSTRAINT `comunicaciones_ibfk_1` FOREIGN KEY (`beneficiario_id`) REFERENCES `beneficiarios` (`id`),
  ADD CONSTRAINT `comunicaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `comunicaciones_ibfk_5` FOREIGN KEY (`categoria_id`) REFERENCES `comunicacion_categoria` (`id`);

--
-- Filtros para la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD CONSTRAINT `contactos_ibfk_1` FOREIGN KEY (`beneficiario_id`) REFERENCES `beneficiarios` (`id`),
  ADD CONSTRAINT `contactos_ibfk_2` FOREIGN KEY (`parentesco_id`) REFERENCES `contacto_parentesco` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
