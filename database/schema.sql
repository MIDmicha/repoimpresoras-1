-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-12-2025 a las 06:23:51
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
-- Base de datos: `sistema_impresoras`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `id_usuario` int(11) DEFAULT NULL,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `tabla`, `id_registro`, `accion`, `datos_anteriores`, `datos_nuevos`, `id_usuario`, `ip_usuario`, `user_agent`, `fecha_hora`) VALUES
(1, 'usuarios', 1, '', NULL, '{\"username\":\"admin\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 02:30:09'),
(2, 'usuarios', 2, 'INSERT', NULL, '{\"username\":\"cristhian\",\"password\":\"cristhian\",\"nombre_completo\":\"cristhian coronado\",\"email\":\"test@gmail.com\",\"telefono\":\"956761889\",\"id_rol\":2,\"activo\":1}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 02:47:13'),
(3, 'usuarios', 1, '', NULL, '{\"username\":\"admin\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 02:47:23'),
(4, 'usuarios', 2, '', NULL, '{\"username\":\"cristhian\"}', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 02:47:30'),
(5, 'usuarios', 2, '', NULL, '{\"username\":\"cristhian\"}', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 02:49:59'),
(6, 'usuarios', 1, '', NULL, '{\"username\":\"admin\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 02:50:05'),
(7, 'estados_equipo', 5, 'UPDATE', NULL, '{\"nombre\":\"En GarantIa\",\"descripcion\":\"Equipo con garantIa vigente\",\"color\":\"#17a2b8\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:17:40'),
(8, 'distritos_fiscales', 1, 'INSERT', NULL, '{\"nombre\":\"DISTRIDO GENERAL\",\"codigo\":\"PR00000068\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:18:00'),
(9, 'sedes', 1, 'INSERT', NULL, '{\"nombre\":\"SEDE 1\",\"direccion\":\"CALLE ABCDE\",\"id_distrito\":1}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:18:21'),
(10, 'macro_procesos', 1, 'INSERT', NULL, '{\"nombre\":\"MACRO PROCESO GENERAL\",\"descripcion\":\"MACRO PROCESO GENERAL  DESCRIPCION\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:18:42'),
(11, 'despachos', 1, 'INSERT', NULL, '{\"nombre\":\"DESPACHO PRINCIPAL  COUNTERS\",\"id_sede\":1}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:19:00'),
(12, 'usuarios_finales', 1, 'INSERT', NULL, '{\"nombre_completo\":\"cristhian coronado\",\"dni\":\"72115227\",\"cargo\":\"USADOR DE EQUIPO\",\"telefono\":\"956761888\",\"email\":\"cristhian@gmail.com\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:19:32'),
(13, 'usuarios_finales', 1, 'UPDATE', NULL, '{\"nombre_completo\":\"cristhian coronado\",\"dni\":\"72115227\",\"cargo\":\"USADOR DE EQUIPO\",\"telefono\":\"956761888\",\"email\":\"cristhian@gmail.com\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:24:15'),
(14, 'equipos', 1, 'INSERT', NULL, '{\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":1,\"tiene_estabilizador\":1,\"anio_adquisicion\":2021,\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"id_usuario_creacion\":1,\"imagen\":\"uploads\\/equipos\\/equipo_69476ed03aa6e.webp\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:51:44'),
(15, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 1, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 5, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 03:53:23'),
(16, 'equipos', 1, 'UPDATE', '{\"id\":1,\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":1,\"tiene_estabilizador\":1,\"anio_adquisicion\":\"2021\",\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"imagen\":\"uploads\\/equipos\\/equipo_69476ed03aa6e.webp\",\"activo\":1,\"fecha_creacion\":\"2025-12-20 22:51:44\",\"fecha_actualizacion\":\"2025-12-20 22:51:44\",\"id_usuario_creacion\":1,\"id_usuario_actualizacion\":null,\"estado_nombre\":\"Operativo\",\"distrito_nombre\":\"DISTRIDO GENERAL\",\"sede_nombre\":\"SEDE 1\",\"macro_proceso_nombre\":\"MACRO PROCESO GENERAL\",\"despacho_nombre\":\"DESPACHO PRINCIPAL  COUNTERS\",\"usuario_final_nombre\":\"cristhian coronado\"}', '{\"id\":1,\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":5,\"tiene_estabilizador\":1,\"anio_adquisicion\":2021,\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"id_usuario_actualizacion\":\"1\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 03:53:23'),
(17, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 5, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 1, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:03:09'),
(18, 'equipos', 1, 'UPDATE', '{\"id\":1,\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":5,\"tiene_estabilizador\":1,\"anio_adquisicion\":\"2021\",\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"imagen\":\"uploads\\/equipos\\/equipo_69476ed03aa6e.webp\",\"activo\":1,\"fecha_creacion\":\"2025-12-20 22:51:44\",\"fecha_actualizacion\":\"2025-12-20 22:53:23\",\"id_usuario_creacion\":1,\"id_usuario_actualizacion\":1,\"estado_nombre\":\"En GarantIa\",\"distrito_nombre\":\"DISTRIDO GENERAL\",\"sede_nombre\":\"SEDE 1\",\"macro_proceso_nombre\":\"MACRO PROCESO GENERAL\",\"despacho_nombre\":\"DESPACHO PRINCIPAL  COUNTERS\",\"usuario_final_nombre\":\"cristhian coronado\"}', '{\"id\":1,\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":1,\"tiene_estabilizador\":1,\"anio_adquisicion\":2021,\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"id_usuario_actualizacion\":\"1\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 04:03:09'),
(19, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 1, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:20:01'),
(20, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:20:01'),
(21, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:33:34'),
(22, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:33:34'),
(23, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 3, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:34:30'),
(24, 'equipos', 1, 'UPDATE', '{\"id\":1,\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":2,\"tiene_estabilizador\":1,\"anio_adquisicion\":\"2021\",\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"imagen\":\"uploads\\/equipos\\/equipo_69476ed03aa6e.webp\",\"activo\":1,\"fecha_creacion\":\"2025-12-20 22:51:44\",\"fecha_actualizacion\":\"2025-12-20 23:20:01\",\"id_usuario_creacion\":1,\"id_usuario_actualizacion\":1,\"estado_nombre\":\"En Mantenimiento\",\"distrito_nombre\":\"DISTRIDO GENERAL\",\"sede_nombre\":\"SEDE 1\",\"macro_proceso_nombre\":\"MACRO PROCESO GENERAL\",\"despacho_nombre\":\"DESPACHO PRINCIPAL  COUNTERS\",\"usuario_final_nombre\":\"cristhian coronado\"}', '{\"id\":1,\"codigo_patrimonial\":\"cod001\",\"clasificacion\":\"impresora\",\"marca\":\"Epson\",\"modelo\":\"WorkForce Pro WF-M5299\",\"id_marca\":3,\"id_modelo\":12,\"numero_serie\":\"12315144445548413\",\"garantia\":\"12 meses\",\"id_estado\":3,\"tiene_estabilizador\":1,\"anio_adquisicion\":2021,\"id_distrito\":1,\"id_sede\":1,\"id_macro_proceso\":1,\"ubicacion_fisica\":\"despacho gerencial\",\"id_despacho\":1,\"id_usuario_final\":1,\"observaciones\":\"imprsora de gerente genral\",\"id_usuario_actualizacion\":\"1\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 04:34:30'),
(25, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 3, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:35:22'),
(26, 'equipos', 1, 'UPDATE', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '{\"codigo_patrimonial\": \"cod001\", \"estado\": 2, \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', 1, NULL, NULL, '2025-12-21 04:35:22'),
(27, 'usuarios', 1, '', NULL, '{\"username\":\"admin\"}', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 05:22:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_configuracion`
--

CREATE TABLE `auditoria_configuracion` (
  `id` int(11) NOT NULL,
  `tabla` varchar(50) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `accion` enum('crear','actualizar','eliminar','activar','desactivar') NOT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_accion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_equipos`
--

CREATE TABLE `auditoria_equipos` (
  `id` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `usuario_nombre` varchar(100) DEFAULT NULL,
  `accion` varchar(50) NOT NULL COMMENT 'CREAR, MODIFICAR, ELIMINAR, CAMBIO_ESTADO, etc',
  `descripcion` text DEFAULT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_equipos`
--

INSERT INTO `auditoria_equipos` (`id`, `id_equipo`, `id_usuario`, `usuario_nombre`, `accion`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `fecha_hora`, `ip_address`) VALUES
(1, 1, 1, 'Administrador', 'CREAR', 'Equipo creado: cod001', NULL, '{\"codigo_patrimonial\": \"cod001\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\"}', '2025-12-21 03:51:44', NULL),
(2, 1, 1, 'Administrador del Sistema', 'CAMBIO_ESTADO', 'Estado cambiado de \"En GarantIa\" a \"Operativo\"', '{\"id_estado\": 5, \"estado_nombre\": \"En GarantIa\"}', '{\"id_estado\": 1, \"estado_nombre\": \"Operativo\"}', '2025-12-21 04:03:09', NULL),
(3, 1, 1, 'Administrador del Sistema', 'CAMBIO_ESTADO', 'Estado cambiado de \"Operativo\" a \"En Mantenimiento\"', '{\"id_estado\": 1, \"estado_nombre\": \"Operativo\"}', '{\"id_estado\": 2, \"estado_nombre\": \"En Mantenimiento\"}', '2025-12-21 04:20:01', NULL),
(4, 1, 1, 'Administrador del Sistema', 'MODIFICAR', 'Equipo modificado', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '2025-12-21 04:20:01', NULL),
(5, 1, 1, 'Administrador del Sistema', 'MODIFICAR', 'Equipo modificado', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '2025-12-21 04:33:34', NULL),
(6, 1, 1, 'Administrador del Sistema', 'MODIFICAR', 'Equipo modificado', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '2025-12-21 04:33:34', NULL),
(7, 1, 1, 'Administrador del Sistema', 'CAMBIO_ESTADO', 'Estado cambiado de \"En Mantenimiento\" a \"Averiado\"', '{\"id_estado\": 2, \"estado_nombre\": \"En Mantenimiento\"}', '{\"id_estado\": 3, \"estado_nombre\": \"Averiado\"}', '2025-12-21 04:34:30', NULL),
(8, 1, 1, 'Administrador del Sistema', 'CAMBIO_ESTADO', 'Estado cambiado de \"Averiado\" a \"En Mantenimiento\"', '{\"id_estado\": 3, \"estado_nombre\": \"Averiado\"}', '{\"id_estado\": 2, \"estado_nombre\": \"En Mantenimiento\"}', '2025-12-21 04:35:22', NULL),
(9, 1, 1, 'Administrador del Sistema', 'MODIFICAR', 'Equipo modificado', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '{\"codigo_patrimonial\": \"cod001\", \"clasificacion\": \"impresora\", \"marca\": \"Epson\", \"modelo\": \"WorkForce Pro WF-M5299\", \"numero_serie\": \"12315144445548413\", \"id_estado\": 2, \"id_sede\": 1}', '2025-12-21 04:35:22', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `despachos`
--

CREATE TABLE `despachos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_sede` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `despachos`
--

INSERT INTO `despachos` (`id`, `nombre`, `id_sede`, `activo`, `fecha_creacion`) VALUES
(1, 'DESPACHO PRINCIPAL  COUNTERS', 1, 1, '2025-12-21 03:19:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distritos_fiscales`
--

CREATE TABLE `distritos_fiscales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `distritos_fiscales`
--

INSERT INTO `distritos_fiscales` (`id`, `nombre`, `codigo`, `activo`, `fecha_creacion`) VALUES
(1, 'DISTRIDO GENERAL', 'PR00000068', 1, '2025-12-21 03:18:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `codigo_patrimonial` varchar(50) NOT NULL,
  `clasificacion` enum('impresora','multifuncional') NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `id_modelo` int(11) DEFAULT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `garantia` varchar(100) DEFAULT NULL,
  `id_estado` int(11) NOT NULL,
  `tiene_estabilizador` tinyint(1) DEFAULT 0,
  `anio_adquisicion` year(4) DEFAULT NULL,
  `id_distrito` int(11) DEFAULT NULL,
  `id_sede` int(11) DEFAULT NULL,
  `id_macro_proceso` int(11) DEFAULT NULL,
  `ubicacion_fisica` varchar(200) DEFAULT NULL,
  `id_despacho` int(11) DEFAULT NULL,
  `id_usuario_final` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_usuario_creacion` int(11) DEFAULT NULL,
  `id_usuario_actualizacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `codigo_patrimonial`, `clasificacion`, `marca`, `modelo`, `id_marca`, `id_modelo`, `numero_serie`, `garantia`, `id_estado`, `tiene_estabilizador`, `anio_adquisicion`, `id_distrito`, `id_sede`, `id_macro_proceso`, `ubicacion_fisica`, `id_despacho`, `id_usuario_final`, `observaciones`, `imagen`, `activo`, `fecha_creacion`, `fecha_actualizacion`, `id_usuario_creacion`, `id_usuario_actualizacion`) VALUES
(1, 'cod001', 'impresora', 'Epson', 'WorkForce Pro WF-M5299', 3, 12, '12315144445548413', '12 meses', 2, 1, '2021', 1, 1, 1, 'despacho gerencial', 1, 1, 'imprsora de gerente genral', 'uploads/equipos/equipo_69476ed03aa6e.webp', 1, '2025-12-21 03:51:44', '2025-12-21 04:35:22', 1, 1);

--
-- Disparadores `equipos`
--
DELIMITER $$
CREATE TRIGGER `after_equipo_insert` AFTER INSERT ON `equipos` FOR EACH ROW BEGIN
    DECLARE usuario_nombre_var VARCHAR(100);
    
    
    SELECT nombre_completo INTO usuario_nombre_var
    FROM usuarios WHERE id = NEW.id_usuario_creacion;
    
    INSERT INTO auditoria_equipos (
        id_equipo,
        id_usuario,
        usuario_nombre,
        accion,
        descripcion,
        datos_nuevos,
        ip_address
    ) VALUES (
        NEW.id,
        NEW.id_usuario_creacion,
        usuario_nombre_var,
        'CREAR',
        CONCAT('Equipo creado: ', NEW.codigo_patrimonial),
        JSON_OBJECT(
            'codigo_patrimonial', NEW.codigo_patrimonial,
            'clasificacion', NEW.clasificacion,
            'marca', NEW.marca,
            'modelo', NEW.modelo,
            'id_estado', NEW.id_estado,
            'numero_serie', NEW.numero_serie
        ),
        NULL
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_equipo_update` AFTER UPDATE ON `equipos` FOR EACH ROW BEGIN
    DECLARE usuario_nombre_var VARCHAR(100);
    DECLARE descripcion_var TEXT;
    DECLARE estado_anterior VARCHAR(50);
    DECLARE estado_nuevo VARCHAR(50);
    
    
    SELECT nombre_completo INTO usuario_nombre_var
    FROM usuarios WHERE id = NEW.id_usuario_actualizacion;
    
    
    IF OLD.activo = 1 AND NEW.activo = 0 THEN
        
        SET descripcion_var = CONCAT('Equipo eliminado: ', NEW.codigo_patrimonial);
        
        INSERT INTO auditoria_equipos (
            id_equipo,
            id_usuario,
            usuario_nombre,
            accion,
            descripcion,
            datos_anteriores,
            datos_nuevos
        ) VALUES (
            NEW.id,
            NEW.id_usuario_actualizacion,
            usuario_nombre_var,
            'ELIMINAR',
            descripcion_var,
            JSON_OBJECT('activo', OLD.activo),
            JSON_OBJECT('activo', NEW.activo)
        );
        
    ELSEIF OLD.id_estado != NEW.id_estado THEN
        
        SELECT nombre INTO estado_anterior FROM estados_equipo WHERE id = OLD.id_estado;
        SELECT nombre INTO estado_nuevo FROM estados_equipo WHERE id = NEW.id_estado;
        
        SET descripcion_var = CONCAT('Estado cambiado de "', estado_anterior, '" a "', estado_nuevo, '"');
        
        INSERT INTO auditoria_equipos (
            id_equipo,
            id_usuario,
            usuario_nombre,
            accion,
            descripcion,
            datos_anteriores,
            datos_nuevos
        ) VALUES (
            NEW.id,
            NEW.id_usuario_actualizacion,
            usuario_nombre_var,
            'CAMBIO_ESTADO',
            descripcion_var,
            JSON_OBJECT('id_estado', OLD.id_estado, 'estado_nombre', estado_anterior),
            JSON_OBJECT('id_estado', NEW.id_estado, 'estado_nombre', estado_nuevo)
        );
        
    ELSE
        
        SET descripcion_var = 'Equipo modificado';
        
        
        IF OLD.codigo_patrimonial != NEW.codigo_patrimonial THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - C??digo patrimonial actualizado');
        END IF;
        
        IF OLD.marca != NEW.marca OR OLD.modelo != NEW.modelo THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Marca/Modelo actualizado');
        END IF;
        
        IF OLD.numero_serie != NEW.numero_serie OR (OLD.numero_serie IS NULL AND NEW.numero_serie IS NOT NULL) THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - N??mero de serie actualizado');
        END IF;
        
        IF OLD.id_sede != NEW.id_sede OR (OLD.id_sede IS NULL AND NEW.id_sede IS NOT NULL) THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Sede actualizada');
        END IF;
        
        IF OLD.imagen != NEW.imagen OR (OLD.imagen IS NULL AND NEW.imagen IS NOT NULL) THEN
            SET descripcion_var = CONCAT(descripcion_var, ' - Imagen actualizada');
        END IF;
        
        INSERT INTO auditoria_equipos (
            id_equipo,
            id_usuario,
            usuario_nombre,
            accion,
            descripcion,
            datos_anteriores,
            datos_nuevos
        ) VALUES (
            NEW.id,
            NEW.id_usuario_actualizacion,
            usuario_nombre_var,
            'MODIFICAR',
            descripcion_var,
            JSON_OBJECT(
                'codigo_patrimonial', OLD.codigo_patrimonial,
                'clasificacion', OLD.clasificacion,
                'marca', OLD.marca,
                'modelo', OLD.modelo,
                'numero_serie', OLD.numero_serie,
                'id_estado', OLD.id_estado,
                'id_sede', OLD.id_sede
            ),
            JSON_OBJECT(
                'codigo_patrimonial', NEW.codigo_patrimonial,
                'clasificacion', NEW.clasificacion,
                'marca', NEW.marca,
                'modelo', NEW.modelo,
                'numero_serie', NEW.numero_serie,
                'id_estado', NEW.id_estado,
                'id_sede', NEW.id_sede
            )
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_equipos_update` AFTER UPDATE ON `equipos` FOR EACH ROW BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, datos_anteriores, datos_nuevos, id_usuario)
    VALUES (
        'equipos',
        NEW.id,
        'UPDATE',
        JSON_OBJECT(
            'codigo_patrimonial', OLD.codigo_patrimonial,
            'estado', OLD.id_estado,
            'marca', OLD.marca,
            'modelo', OLD.modelo
        ),
        JSON_OBJECT(
            'codigo_patrimonial', NEW.codigo_patrimonial,
            'estado', NEW.id_estado,
            'marca', NEW.marca,
            'modelo', NEW.modelo
        ),
        NEW.id_usuario_actualizacion
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_equipo`
--

CREATE TABLE `estados_equipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados_equipo`
--

INSERT INTO `estados_equipo` (`id`, `nombre`, `descripcion`, `color`, `activo`) VALUES
(1, 'Operativo', 'Equipo funcionando correctamente', '#28a745', 1),
(2, 'En Mantenimiento', 'Equipo en proceso de mantenimiento', '#ffc107', 1),
(3, 'Averiado', 'Equipo con fallas', '#dc3545', 1),
(4, 'Fuera de Servicio', 'Equipo dado de baja', '#6c757d', 1),
(5, 'En GarantIa', 'Equipo con garantIa vigente', '#17a2b8', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `macro_procesos`
--

CREATE TABLE `macro_procesos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `macro_procesos`
--

INSERT INTO `macro_procesos` (`id`, `nombre`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'MACRO PROCESO GENERAL', 'MACRO PROCESO GENERAL  DESCRIPCION', 1, '2025-12-21 03:18:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_tipo_demanda` int(11) NOT NULL,
  `fecha_mantenimiento` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tecnico_responsable` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `id_estado_anterior` int(11) DEFAULT NULL,
  `id_estado_nuevo` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario_registro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mantenimientos`
--

INSERT INTO `mantenimientos` (`id`, `id_equipo`, `id_tipo_demanda`, `fecha_mantenimiento`, `descripcion`, `tecnico_responsable`, `observaciones`, `id_estado_anterior`, `id_estado_nuevo`, `fecha_creacion`, `id_usuario_registro`) VALUES
(1, 1, 1, '2025-12-18', 'serapio', 'el teo', 'tiene fallas de eimpresion', 2, 2, '2025-12-21 04:20:01', 1),
(2, 1, 1, '2025-12-21', 'falta recargar tonner', 'el teo', 'teo lo hace', 2, 2, '2025-12-21 04:33:34', 1),
(3, 1, 1, '2025-12-21', 'preventivo', 'el teo', 'sollo mantenimiento preventivo', 2, 2, '2025-12-21 04:35:22', 1);

--
-- Disparadores `mantenimientos`
--
DELIMITER $$
CREATE TRIGGER `trg_mantenimiento_actualizar_estado` AFTER INSERT ON `mantenimientos` FOR EACH ROW BEGIN
    IF NEW.id_estado_nuevo IS NOT NULL THEN
        UPDATE equipos 
        SET id_estado = NEW.id_estado_nuevo,
            id_usuario_actualizacion = NEW.id_usuario_registro
        WHERE id = NEW.id_equipo;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `nombre`, `descripcion`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'HP', 'Hewlett-Packard', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(2, 'Canon', 'Canon Inc.', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(3, 'Epson', 'Epson Corporation', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(4, 'Brother', 'Brother Industries', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(5, 'Samsung', 'Samsung Electronics', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(6, 'Xerox', 'Xerox Corporation', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(7, 'Ricoh', 'Ricoh Company', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(8, 'Kyocera', 'Kyocera Corporation', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(9, 'Lexmark', 'Lexmark International', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(10, 'Konica Minolta', 'Konica Minolta Inc.', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos`
--

CREATE TABLE `modelos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modelos`
--

INSERT INTO `modelos` (`id`, `nombre`, `id_marca`, `descripcion`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'LaserJet Pro M404dn', 1, 'Impresora l├íser monocrom├ítica', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(2, 'LaserJet Pro MFP M428fdw', 1, 'Multifuncional l├íser', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(3, 'Color LaserJet Pro M454dw', 1, 'Impresora l├íser color', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(4, 'OfficeJet Pro 9015e', 1, 'Multifuncional de tinta', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(5, 'imageCLASS MF445dw', 2, 'Multifuncional l├íser monocrom├ítica', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(6, 'imageCLASS LBP226dw', 2, 'Impresora l├íser monocrom├ítica', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(7, 'PIXMA G7020', 2, 'Multifuncional de tinta con sistema continuo', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(8, 'imageRUNNER ADVANCE C5560i', 2, 'Multifuncional color profesional', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(9, 'EcoTank L3250', 3, 'Multifuncional con sistema continuo', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(10, 'WorkForce Pro WF-C5790', 3, 'Multifuncional de tinta empresarial', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(11, 'EcoTank L14150', 3, 'Multifuncional formato A3', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(12, 'WorkForce Pro WF-M5299', 3, 'Impresora monocrom├ítica', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(13, 'HL-L2350DW', 4, 'Impresora l├íser monocrom├ítica', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(14, 'MFC-L2750DW', 4, 'Multifuncional l├íser monocrom├ítica', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(15, 'MFC-J6945DW', 4, 'Multifuncional de tinta A3', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02'),
(16, 'HL-L8360CDW', 4, 'Impresora l├íser color', 1, '2025-12-21 03:26:02', '2025-12-21 03:26:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuestos`
--

CREATE TABLE `repuestos` (
  `id` int(11) NOT NULL,
  `id_mantenimiento` int(11) NOT NULL,
  `parte_requerida` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` int(11) DEFAULT 1,
  `fecha_cambio` date NOT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_usuario_registro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 1, '2025-12-21 02:25:21', '2025-12-21 02:25:21'),
(2, 'Encargado', 'Gesti??n de equipos y mantenimientos', 1, '2025-12-21 02:25:21', '2025-12-21 02:25:21'),
(3, 'Usuario', 'Solo consulta de informaci??n', 1, '2025-12-21 02:25:21', '2025-12-21 02:25:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `id_distrito` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sedes`
--

INSERT INTO `sedes` (`id`, `nombre`, `direccion`, `id_distrito`, `activo`, `fecha_creacion`) VALUES
(1, 'SEDE 1', 'CALLE ABCDE', 1, 1, '2025-12-21 03:18:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_demanda`
--

CREATE TABLE `tipos_demanda` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_demanda`
--

INSERT INTO `tipos_demanda` (`id`, `nombre`, `descripcion`, `activo`) VALUES
(1, 'Mantenimiento Preventivo', 'Mantenimiento programado regular', 1),
(2, 'Mantenimiento Correctivo', 'Reparaci??n por falla', 1),
(3, 'Cambio de Repuesto', 'Reemplazo de piezas', 1),
(4, 'Instalaci??n', 'Instalaci??n inicial del equipo', 1),
(5, 'Traslado', 'Cambio de ubicaci??n del equipo', 1),
(6, 'Calibraci??n', 'Ajustes y calibraci??n del equipo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nombre_completo`, `email`, `telefono`, `id_rol`, `activo`, `ultimo_acceso`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'admin', '$2y$10$XGEHcakrpHdhytgRFOHwp.yuXJSlIz2YBKuyLh7F9fl287oX9Ojt2', 'Administrador del Sistema', 'admin@sistema.com', NULL, 1, 1, '2025-12-21 02:50:05', '2025-12-21 02:25:21', '2025-12-21 02:50:05'),
(2, 'cristhian', '$2y$10$1wb/Z.CpIabu5c9ho1AhqOCtD4HWDWfNQsB6nUJsdvFsW35.xofRe', 'cristhian coronado', 'test@gmail.com', '956761889', 2, 1, '2025-12-21 02:47:30', '2025-12-21 02:47:13', '2025-12-21 02:47:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_finales`
--

CREATE TABLE `usuarios_finales` (
  `id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios_finales`
--

INSERT INTO `usuarios_finales` (`id`, `nombre_completo`, `dni`, `cargo`, `telefono`, `email`, `activo`, `fecha_creacion`) VALUES
(1, 'cristhian coronado', '72115227', 'USADOR DE EQUIPO', '956761888', 'cristhian@gmail.com', 1, '2025-12-21 03:19:32');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_equipos_completa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_equipos_completa` (
`id` int(11)
,`codigo_patrimonial` varchar(50)
,`clasificacion` enum('impresora','multifuncional')
,`marca` varchar(50)
,`modelo` varchar(100)
,`numero_serie` varchar(100)
,`garantia` varchar(100)
,`estado` varchar(50)
,`estado_color` varchar(20)
,`tiene_estabilizador` tinyint(1)
,`anio_adquisicion` year(4)
,`distrito` varchar(100)
,`sede` varchar(100)
,`macro_proceso` varchar(100)
,`ubicacion_fisica` varchar(200)
,`despacho` varchar(100)
,`usuario_final` varchar(100)
,`observaciones` text
,`fecha_creacion` timestamp
,`fecha_actualizacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_mantenimientos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_mantenimientos` (
`id` int(11)
,`fecha_mantenimiento` date
,`codigo_patrimonial` varchar(50)
,`marca` varchar(50)
,`modelo` varchar(100)
,`tipo_demanda` varchar(100)
,`descripcion` text
,`tecnico_responsable` varchar(100)
,`estado_anterior` varchar(50)
,`estado_nuevo` varchar(50)
,`registrado_por` varchar(100)
,`fecha_creacion` timestamp
,`cantidad_repuestos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_modelos_con_marca`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_modelos_con_marca` (
`id` int(11)
,`modelo` varchar(100)
,`id_marca` int(11)
,`marca` varchar(100)
,`descripcion` text
,`activo` tinyint(1)
,`fecha_creacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_equipos_completa`
--
DROP TABLE IF EXISTS `vista_equipos_completa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_equipos_completa`  AS SELECT `e`.`id` AS `id`, `e`.`codigo_patrimonial` AS `codigo_patrimonial`, `e`.`clasificacion` AS `clasificacion`, `e`.`marca` AS `marca`, `e`.`modelo` AS `modelo`, `e`.`numero_serie` AS `numero_serie`, `e`.`garantia` AS `garantia`, `est`.`nombre` AS `estado`, `est`.`color` AS `estado_color`, `e`.`tiene_estabilizador` AS `tiene_estabilizador`, `e`.`anio_adquisicion` AS `anio_adquisicion`, `d`.`nombre` AS `distrito`, `s`.`nombre` AS `sede`, `mp`.`nombre` AS `macro_proceso`, `e`.`ubicacion_fisica` AS `ubicacion_fisica`, `desp`.`nombre` AS `despacho`, `uf`.`nombre_completo` AS `usuario_final`, `e`.`observaciones` AS `observaciones`, `e`.`fecha_creacion` AS `fecha_creacion`, `e`.`fecha_actualizacion` AS `fecha_actualizacion` FROM ((((((`equipos` `e` left join `estados_equipo` `est` on(`e`.`id_estado` = `est`.`id`)) left join `distritos_fiscales` `d` on(`e`.`id_distrito` = `d`.`id`)) left join `sedes` `s` on(`e`.`id_sede` = `s`.`id`)) left join `macro_procesos` `mp` on(`e`.`id_macro_proceso` = `mp`.`id`)) left join `despachos` `desp` on(`e`.`id_despacho` = `desp`.`id`)) left join `usuarios_finales` `uf` on(`e`.`id_usuario_final` = `uf`.`id`)) WHERE `e`.`activo` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_mantenimientos`
--
DROP TABLE IF EXISTS `vista_mantenimientos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_mantenimientos`  AS SELECT `m`.`id` AS `id`, `m`.`fecha_mantenimiento` AS `fecha_mantenimiento`, `e`.`codigo_patrimonial` AS `codigo_patrimonial`, `e`.`marca` AS `marca`, `e`.`modelo` AS `modelo`, `td`.`nombre` AS `tipo_demanda`, `m`.`descripcion` AS `descripcion`, `m`.`tecnico_responsable` AS `tecnico_responsable`, `ea`.`nombre` AS `estado_anterior`, `en`.`nombre` AS `estado_nuevo`, `u`.`nombre_completo` AS `registrado_por`, `m`.`fecha_creacion` AS `fecha_creacion`, (select count(0) from `repuestos` where `repuestos`.`id_mantenimiento` = `m`.`id`) AS `cantidad_repuestos` FROM (((((`mantenimientos` `m` join `equipos` `e` on(`m`.`id_equipo` = `e`.`id`)) join `tipos_demanda` `td` on(`m`.`id_tipo_demanda` = `td`.`id`)) left join `estados_equipo` `ea` on(`m`.`id_estado_anterior` = `ea`.`id`)) left join `estados_equipo` `en` on(`m`.`id_estado_nuevo` = `en`.`id`)) left join `usuarios` `u` on(`m`.`id_usuario_registro` = `u`.`id`)) ORDER BY `m`.`fecha_mantenimiento` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_modelos_con_marca`
--
DROP TABLE IF EXISTS `v_modelos_con_marca`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_modelos_con_marca`  AS SELECT `m`.`id` AS `id`, `m`.`nombre` AS `modelo`, `m`.`id_marca` AS `id_marca`, `marc`.`nombre` AS `marca`, `m`.`descripcion` AS `descripcion`, `m`.`activo` AS `activo`, `m`.`fecha_creacion` AS `fecha_creacion` FROM (`modelos` `m` join `marcas` `marc` on(`m`.`id_marca` = `marc`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tabla_registro` (`tabla`,`id_registro`),
  ADD KEY `idx_fecha` (`fecha_hora`),
  ADD KEY `idx_usuario` (`id_usuario`);

--
-- Indices de la tabla `auditoria_configuracion`
--
ALTER TABLE `auditoria_configuracion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `auditoria_equipos`
--
ALTER TABLE `auditoria_equipos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_equipo` (`id_equipo`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_fecha` (`fecha_hora`);

--
-- Indices de la tabla `despachos`
--
ALTER TABLE `despachos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sede` (`id_sede`);

--
-- Indices de la tabla `distritos_fiscales`
--
ALTER TABLE `distritos_fiscales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_patrimonial` (`codigo_patrimonial`),
  ADD KEY `id_distrito` (`id_distrito`),
  ADD KEY `id_macro_proceso` (`id_macro_proceso`),
  ADD KEY `id_despacho` (`id_despacho`),
  ADD KEY `id_usuario_final` (`id_usuario_final`),
  ADD KEY `id_usuario_creacion` (`id_usuario_creacion`),
  ADD KEY `id_usuario_actualizacion` (`id_usuario_actualizacion`),
  ADD KEY `idx_equipos_codigo` (`codigo_patrimonial`),
  ADD KEY `idx_equipos_estado` (`id_estado`),
  ADD KEY `idx_equipos_sede` (`id_sede`),
  ADD KEY `idx_marca` (`id_marca`),
  ADD KEY `idx_modelo` (`id_modelo`);

--
-- Indices de la tabla `estados_equipo`
--
ALTER TABLE `estados_equipo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `macro_procesos`
--
ALTER TABLE `macro_procesos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_demanda` (`id_tipo_demanda`),
  ADD KEY `id_estado_anterior` (`id_estado_anterior`),
  ADD KEY `id_estado_nuevo` (`id_estado_nuevo`),
  ADD KEY `id_usuario_registro` (`id_usuario_registro`),
  ADD KEY `idx_mantenimientos_equipo` (`id_equipo`),
  ADD KEY `idx_mantenimientos_fecha` (`fecha_mantenimiento`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_modelo_marca` (`nombre`,`id_marca`),
  ADD KEY `id_marca` (`id_marca`);

--
-- Indices de la tabla `repuestos`
--
ALTER TABLE `repuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_registro` (`id_usuario_registro`),
  ADD KEY `idx_repuestos_mantenimiento` (`id_mantenimiento`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_distrito` (`id_distrito`);

--
-- Indices de la tabla `tipos_demanda`
--
ALTER TABLE `tipos_demanda`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `usuarios_finales`
--
ALTER TABLE `usuarios_finales`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `auditoria_configuracion`
--
ALTER TABLE `auditoria_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria_equipos`
--
ALTER TABLE `auditoria_equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `despachos`
--
ALTER TABLE `despachos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `distritos_fiscales`
--
ALTER TABLE `distritos_fiscales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `estados_equipo`
--
ALTER TABLE `estados_equipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `macro_procesos`
--
ALTER TABLE `macro_procesos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `repuestos`
--
ALTER TABLE `repuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipos_demanda`
--
ALTER TABLE `tipos_demanda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios_finales`
--
ALTER TABLE `usuarios_finales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `auditoria_configuracion`
--
ALTER TABLE `auditoria_configuracion`
  ADD CONSTRAINT `auditoria_configuracion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `auditoria_equipos`
--
ALTER TABLE `auditoria_equipos`
  ADD CONSTRAINT `auditoria_equipos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auditoria_equipos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `despachos`
--
ALTER TABLE `despachos`
  ADD CONSTRAINT `despachos_ibfk_1` FOREIGN KEY (`id_sede`) REFERENCES `sedes` (`id`);

--
-- Filtros para la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `estados_equipo` (`id`),
  ADD CONSTRAINT `equipos_ibfk_2` FOREIGN KEY (`id_distrito`) REFERENCES `distritos_fiscales` (`id`),
  ADD CONSTRAINT `equipos_ibfk_3` FOREIGN KEY (`id_sede`) REFERENCES `sedes` (`id`),
  ADD CONSTRAINT `equipos_ibfk_4` FOREIGN KEY (`id_macro_proceso`) REFERENCES `macro_procesos` (`id`),
  ADD CONSTRAINT `equipos_ibfk_5` FOREIGN KEY (`id_despacho`) REFERENCES `despachos` (`id`),
  ADD CONSTRAINT `equipos_ibfk_6` FOREIGN KEY (`id_usuario_final`) REFERENCES `usuarios_finales` (`id`),
  ADD CONSTRAINT `equipos_ibfk_7` FOREIGN KEY (`id_usuario_creacion`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `equipos_ibfk_8` FOREIGN KEY (`id_usuario_actualizacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`),
  ADD CONSTRAINT `mantenimientos_ibfk_2` FOREIGN KEY (`id_tipo_demanda`) REFERENCES `tipos_demanda` (`id`),
  ADD CONSTRAINT `mantenimientos_ibfk_3` FOREIGN KEY (`id_estado_anterior`) REFERENCES `estados_equipo` (`id`),
  ADD CONSTRAINT `mantenimientos_ibfk_4` FOREIGN KEY (`id_estado_nuevo`) REFERENCES `estados_equipo` (`id`),
  ADD CONSTRAINT `mantenimientos_ibfk_5` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `modelos`
--
ALTER TABLE `modelos`
  ADD CONSTRAINT `modelos_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `repuestos`
--
ALTER TABLE `repuestos`
  ADD CONSTRAINT `repuestos_ibfk_1` FOREIGN KEY (`id_mantenimiento`) REFERENCES `mantenimientos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repuestos_ibfk_2` FOREIGN KEY (`id_usuario_registro`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD CONSTRAINT `sedes_ibfk_1` FOREIGN KEY (`id_distrito`) REFERENCES `distritos_fiscales` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
