-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-01-2026 a las 05:29:13
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
-- Base de datos: `sorteo_schema`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_admin`
--

CREATE TABLE `auditoria_admin` (
  `id_log` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `tipo_accion` varchar(50) DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `recurso` varchar(255) DEFAULT NULL,
  `modulo` varchar(50) DEFAULT NULL,
  `detalles_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles_json`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) DEFAULT 'success',
  `es_alerta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria_admin`
--

INSERT INTO `auditoria_admin` (`id_log`, `id_admin`, `id_tipo`, `tipo_accion`, `accion`, `recurso`, `modulo`, `detalles_json`, `ip_address`, `user_agent`, `fecha_hora`, `estado`, `es_alerta`) VALUES
(1, 4, NULL, 'login_exitoso', 'Inicio de sesión exitoso', 'admin@sorteos.web', 'Autenticación', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-17 20:39:42', 'success', 0),
(2, 4, NULL, 'creacion_sorteo', 'Creación de Sorteo', 'Sorteo ID: 6 - Título: Rifa Honda', NULL, NULL, '::1', NULL, '2026-01-17 20:43:19', 'success', 0),
(3, 4, NULL, 'baneo_temporal', 'Baneo Temporal de Usuario', 'user: 10 (Ana Ruiz) - Baneo temporal hasta: 2026-01-18 - Razón: odio', NULL, NULL, '::1', NULL, '2026-01-17 21:00:00', 'success', 1),
(4, 4, NULL, 'baneo_permanente', 'Baneo Permanente de Usuario', 'user: 12 (Sofi Castro) - Razón: fraude', NULL, NULL, '::1', NULL, '2026-01-17 21:05:33', 'success', 1),
(5, 4, NULL, 'login_exitoso', 'Inicio de sesión exitoso', 'admin@sorteos.web', 'Autenticación', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-18 00:44:11', 'success', 0),
(6, 4, NULL, 'login_exitoso', 'Inicio de sesión exitoso', 'admin@sorteos.web', 'Autenticación', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-18 01:39:41', 'success', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_tipos`
--

CREATE TABLE `auditoria_tipos` (
  `id_tipo` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `nivel` enum('Info','Alerta','Peligro') DEFAULT 'Info'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria_tipos`
--

INSERT INTO `auditoria_tipos` (`id_tipo`, `codigo`, `descripcion`, `nivel`) VALUES
(1, 'LOGIN', 'Inicio de sesión de usuario', 'Info'),
(2, 'LOGOUT', 'Cierre de sesión', 'Info'),
(3, 'CREAR_USUARIO', 'Creación de un nuevo usuario', 'Alerta'),
(4, 'EDITAR_USUARIO', 'Modificación de datos de usuario', 'Alerta'),
(5, 'BANEAR_USUARIO', 'Suspensión temporal o permanente de usuario', 'Peligro'),
(6, 'DESBLOQUEAR_USUARIO', 'Reactivación de usuario suspendido', 'Alerta'),
(7, 'CREAR_SORTEO', 'Creación de un nuevo sorteo', 'Alerta'),
(8, 'EDITAR_SORTEO', 'Modificación de un sorteo existente', 'Alerta'),
(9, 'ELIMINAR_SORTEO', 'Eliminación lógica o física de un sorteo', 'Peligro'),
(10, 'VALIDAR_PAGO', 'Aprobación manual de un pago', 'Alerta'),
(11, 'RECHAZAR_PAGO', 'Rechazo de un pago', 'Alerta'),
(12, 'PUBLICAR_GANADOR', 'Publicación de ganador de sorteo', 'Alerta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletos`
--

CREATE TABLE `boletos` (
  `id_boleto` int(11) NOT NULL,
  `id_sorteo` int(11) NOT NULL,
  `numero_boleto` varchar(10) NOT NULL,
  `estado` enum('Disponible','Reservado','Vendido') DEFAULT 'Disponible',
  `id_usuario_actual` int(11) DEFAULT NULL,
  `id_transaccion_reserva` int(11) DEFAULT NULL,
  `fecha_reserva` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `boletos`
--

INSERT INTO `boletos` (`id_boleto`, `id_sorteo`, `numero_boleto`, `estado`, `id_usuario_actual`, `id_transaccion_reserva`, `fecha_reserva`) VALUES
(1, 1, '001', 'Vendido', 2, 1, NULL),
(2, 1, '002', 'Vendido', 2, 1, NULL),
(3, 1, '003', 'Disponible', NULL, NULL, NULL),
(4, 1, '004', 'Disponible', NULL, NULL, NULL),
(5, 1, '005', 'Disponible', NULL, NULL, NULL),
(6, 1, '006', 'Disponible', NULL, NULL, NULL),
(7, 1, '007', 'Disponible', NULL, NULL, NULL),
(8, 1, '008', 'Disponible', NULL, NULL, NULL),
(9, 1, '009', 'Disponible', NULL, NULL, NULL),
(10, 1, '010', 'Disponible', NULL, NULL, NULL),
(46, 5, '0001', 'Disponible', NULL, NULL, NULL),
(47, 5, '0002', 'Disponible', NULL, NULL, NULL),
(48, 5, '0003', 'Disponible', NULL, NULL, NULL),
(49, 5, '0004', 'Disponible', NULL, NULL, NULL),
(50, 5, '0005', 'Disponible', NULL, NULL, NULL),
(51, 5, '0006', 'Disponible', NULL, NULL, NULL),
(52, 5, '0007', 'Disponible', NULL, NULL, NULL),
(53, 5, '0008', 'Disponible', NULL, NULL, NULL),
(54, 5, '0009', 'Disponible', NULL, NULL, NULL),
(55, 5, '0010', 'Disponible', NULL, NULL, NULL),
(56, 5, '0011', 'Disponible', NULL, NULL, NULL),
(57, 5, '0012', 'Disponible', NULL, NULL, NULL),
(58, 5, '0013', 'Disponible', NULL, NULL, NULL),
(59, 5, '0014', 'Disponible', NULL, NULL, NULL),
(60, 5, '0015', 'Disponible', NULL, NULL, NULL),
(61, 5, '0016', 'Disponible', NULL, NULL, NULL),
(62, 5, '0017', 'Disponible', NULL, NULL, NULL),
(63, 5, '0018', 'Disponible', NULL, NULL, NULL),
(64, 5, '0019', 'Disponible', NULL, NULL, NULL),
(65, 5, '0020', 'Disponible', NULL, NULL, NULL),
(66, 5, '0021', 'Disponible', NULL, NULL, NULL),
(67, 5, '0022', 'Disponible', NULL, NULL, NULL),
(68, 5, '0023', 'Disponible', NULL, NULL, NULL),
(69, 5, '0024', 'Disponible', NULL, NULL, NULL),
(70, 5, '0025', 'Disponible', NULL, NULL, NULL),
(71, 5, '0026', 'Disponible', NULL, NULL, NULL),
(72, 5, '0027', 'Disponible', NULL, NULL, NULL),
(73, 5, '0028', 'Disponible', NULL, NULL, NULL),
(74, 5, '0029', 'Disponible', NULL, NULL, NULL),
(75, 5, '0030', 'Disponible', NULL, NULL, NULL),
(76, 5, '0031', 'Disponible', NULL, NULL, NULL),
(77, 5, '0032', 'Disponible', NULL, NULL, NULL),
(78, 5, '0033', 'Disponible', NULL, NULL, NULL),
(79, 5, '0034', 'Disponible', NULL, NULL, NULL),
(80, 5, '0035', 'Disponible', NULL, NULL, NULL),
(81, 5, '0036', 'Disponible', NULL, NULL, NULL),
(82, 5, '0037', 'Disponible', NULL, NULL, NULL),
(83, 5, '0038', 'Disponible', NULL, NULL, NULL),
(84, 5, '0039', 'Disponible', NULL, NULL, NULL),
(85, 5, '0040', 'Disponible', NULL, NULL, NULL),
(86, 6, '0001', 'Disponible', NULL, NULL, NULL),
(87, 6, '0002', 'Disponible', NULL, NULL, NULL),
(88, 6, '0003', 'Disponible', NULL, NULL, NULL),
(89, 6, '0004', 'Disponible', NULL, NULL, NULL),
(90, 6, '0005', 'Disponible', NULL, NULL, NULL),
(91, 6, '0006', 'Disponible', NULL, NULL, NULL),
(92, 6, '0007', 'Disponible', NULL, NULL, NULL),
(93, 6, '0008', 'Disponible', NULL, NULL, NULL),
(94, 6, '0009', 'Disponible', NULL, NULL, NULL),
(95, 6, '0010', 'Disponible', NULL, NULL, NULL),
(96, 6, '0011', 'Disponible', NULL, NULL, NULL),
(97, 6, '0012', 'Disponible', NULL, NULL, NULL),
(98, 6, '0013', 'Disponible', NULL, NULL, NULL),
(99, 6, '0014', 'Disponible', NULL, NULL, NULL),
(100, 6, '0015', 'Disponible', NULL, NULL, NULL),
(101, 6, '0016', 'Disponible', NULL, NULL, NULL),
(102, 6, '0017', 'Disponible', NULL, NULL, NULL),
(103, 6, '0018', 'Disponible', NULL, NULL, NULL),
(104, 6, '0019', 'Disponible', NULL, NULL, NULL),
(105, 6, '0020', 'Disponible', NULL, NULL, NULL),
(106, 6, '0021', 'Disponible', NULL, NULL, NULL),
(107, 6, '0022', 'Disponible', NULL, NULL, NULL),
(108, 6, '0023', 'Disponible', NULL, NULL, NULL),
(109, 6, '0024', 'Disponible', NULL, NULL, NULL),
(110, 6, '0025', 'Disponible', NULL, NULL, NULL),
(111, 6, '0026', 'Disponible', NULL, NULL, NULL),
(112, 6, '0027', 'Disponible', NULL, NULL, NULL),
(113, 6, '0028', 'Disponible', NULL, NULL, NULL),
(114, 6, '0029', 'Disponible', NULL, NULL, NULL),
(115, 6, '0030', 'Disponible', NULL, NULL, NULL),
(116, 6, '0031', 'Disponible', NULL, NULL, NULL),
(117, 6, '0032', 'Disponible', NULL, NULL, NULL),
(118, 6, '0033', 'Disponible', NULL, NULL, NULL),
(119, 6, '0034', 'Disponible', NULL, NULL, NULL),
(120, 6, '0035', 'Disponible', NULL, NULL, NULL),
(121, 6, '0036', 'Disponible', NULL, NULL, NULL),
(122, 6, '0037', 'Disponible', NULL, NULL, NULL),
(123, 6, '0038', 'Disponible', NULL, NULL, NULL),
(124, 6, '0039', 'Disponible', NULL, NULL, NULL),
(125, 6, '0040', 'Disponible', NULL, NULL, NULL),
(126, 7, '0001', 'Disponible', NULL, NULL, NULL),
(127, 7, '0002', 'Disponible', NULL, NULL, NULL),
(128, 7, '0003', 'Disponible', NULL, NULL, NULL),
(129, 7, '0004', 'Disponible', NULL, NULL, NULL),
(130, 7, '0005', 'Disponible', NULL, NULL, NULL),
(131, 8, '0001', 'Disponible', NULL, NULL, NULL),
(132, 8, '0002', 'Disponible', NULL, NULL, NULL),
(133, 8, '0003', 'Disponible', NULL, NULL, NULL),
(134, 8, '0004', 'Disponible', NULL, NULL, NULL),
(135, 8, '0005', 'Disponible', NULL, NULL, NULL),
(136, 9, '0001', 'Disponible', NULL, NULL, NULL),
(137, 9, '0002', 'Disponible', NULL, NULL, NULL),
(138, 9, '0003', 'Disponible', NULL, NULL, NULL),
(139, 9, '0004', 'Disponible', NULL, NULL, NULL),
(140, 9, '0005', 'Disponible', NULL, NULL, NULL),
(141, 10, '0001', 'Disponible', NULL, NULL, NULL),
(142, 10, '0002', 'Disponible', NULL, NULL, NULL),
(143, 10, '0003', 'Disponible', NULL, NULL, NULL),
(144, 10, '0004', 'Disponible', NULL, NULL, NULL),
(145, 10, '0005', 'Disponible', NULL, NULL, NULL),
(146, 11, '0001', 'Disponible', NULL, NULL, NULL),
(147, 11, '0002', 'Disponible', NULL, NULL, NULL),
(148, 11, '0003', 'Disponible', NULL, NULL, NULL),
(149, 11, '0004', 'Disponible', NULL, NULL, NULL),
(150, 11, '0005', 'Disponible', NULL, NULL, NULL),
(151, 11, '0006', 'Disponible', NULL, NULL, NULL),
(152, 11, '0007', 'Disponible', NULL, NULL, NULL),
(153, 11, '0008', 'Disponible', NULL, NULL, NULL),
(154, 11, '0009', 'Disponible', NULL, NULL, NULL),
(155, 11, '0010', 'Disponible', NULL, NULL, NULL),
(156, 11, '0011', 'Disponible', NULL, NULL, NULL),
(157, 11, '0012', 'Disponible', NULL, NULL, NULL),
(158, 11, '0013', 'Disponible', NULL, NULL, NULL),
(159, 11, '0014', 'Disponible', NULL, NULL, NULL),
(160, 11, '0015', 'Disponible', NULL, NULL, NULL),
(161, 11, '0016', 'Disponible', NULL, NULL, NULL),
(162, 11, '0017', 'Disponible', NULL, NULL, NULL),
(163, 11, '0018', 'Disponible', NULL, NULL, NULL),
(164, 11, '0019', 'Disponible', NULL, NULL, NULL),
(165, 11, '0020', 'Disponible', NULL, NULL, NULL),
(166, 11, '0021', 'Disponible', NULL, NULL, NULL),
(167, 11, '0022', 'Disponible', NULL, NULL, NULL),
(168, 11, '0023', 'Disponible', NULL, NULL, NULL),
(169, 11, '0024', 'Disponible', NULL, NULL, NULL),
(170, 11, '0025', 'Disponible', NULL, NULL, NULL),
(171, 11, '0026', 'Disponible', NULL, NULL, NULL),
(172, 11, '0027', 'Disponible', NULL, NULL, NULL),
(173, 11, '0028', 'Disponible', NULL, NULL, NULL),
(174, 11, '0029', 'Disponible', NULL, NULL, NULL),
(175, 11, '0030', 'Disponible', NULL, NULL, NULL),
(176, 11, '0031', 'Disponible', NULL, NULL, NULL),
(177, 11, '0032', 'Disponible', NULL, NULL, NULL),
(178, 11, '0033', 'Disponible', NULL, NULL, NULL),
(179, 11, '0034', 'Disponible', NULL, NULL, NULL),
(180, 11, '0035', 'Disponible', NULL, NULL, NULL),
(181, 11, '0036', 'Disponible', NULL, NULL, NULL),
(182, 11, '0037', 'Disponible', NULL, NULL, NULL),
(183, 11, '0038', 'Disponible', NULL, NULL, NULL),
(184, 11, '0039', 'Disponible', NULL, NULL, NULL),
(185, 11, '0040', 'Disponible', NULL, NULL, NULL),
(186, 11, '0041', 'Disponible', NULL, NULL, NULL),
(187, 11, '0042', 'Disponible', NULL, NULL, NULL),
(188, 11, '0043', 'Disponible', NULL, NULL, NULL),
(189, 11, '0044', 'Disponible', NULL, NULL, NULL),
(190, 11, '0045', 'Disponible', NULL, NULL, NULL),
(191, 11, '0046', 'Disponible', NULL, NULL, NULL),
(192, 11, '0047', 'Disponible', NULL, NULL, NULL),
(193, 11, '0048', 'Disponible', NULL, NULL, NULL),
(194, 11, '0049', 'Disponible', NULL, NULL, NULL),
(195, 11, '0050', 'Disponible', NULL, NULL, NULL),
(196, 11, '0051', 'Disponible', NULL, NULL, NULL),
(197, 11, '0052', 'Disponible', NULL, NULL, NULL),
(198, 11, '0053', 'Disponible', NULL, NULL, NULL),
(199, 11, '0054', 'Disponible', NULL, NULL, NULL),
(200, 11, '0055', 'Disponible', NULL, NULL, NULL),
(201, 11, '0056', 'Disponible', NULL, NULL, NULL),
(202, 11, '0057', 'Disponible', NULL, NULL, NULL),
(203, 11, '0058', 'Disponible', NULL, NULL, NULL),
(204, 11, '0059', 'Disponible', NULL, NULL, NULL),
(205, 11, '0060', 'Disponible', NULL, NULL, NULL),
(206, 11, '0061', 'Disponible', NULL, NULL, NULL),
(207, 11, '0062', 'Disponible', NULL, NULL, NULL),
(208, 11, '0063', 'Disponible', NULL, NULL, NULL),
(209, 11, '0064', 'Disponible', NULL, NULL, NULL),
(210, 11, '0065', 'Disponible', NULL, NULL, NULL),
(211, 11, '0066', 'Disponible', NULL, NULL, NULL),
(212, 11, '0067', 'Disponible', NULL, NULL, NULL),
(213, 11, '0068', 'Disponible', NULL, NULL, NULL),
(214, 11, '0069', 'Disponible', NULL, NULL, NULL),
(215, 11, '0070', 'Disponible', NULL, NULL, NULL),
(216, 11, '0071', 'Disponible', NULL, NULL, NULL),
(217, 11, '0072', 'Disponible', NULL, NULL, NULL),
(218, 11, '0073', 'Disponible', NULL, NULL, NULL),
(219, 11, '0074', 'Disponible', NULL, NULL, NULL),
(220, 11, '0075', 'Disponible', NULL, NULL, NULL),
(221, 11, '0076', 'Disponible', NULL, NULL, NULL),
(222, 11, '0077', 'Disponible', NULL, NULL, NULL),
(223, 11, '0078', 'Disponible', NULL, NULL, NULL),
(224, 11, '0079', 'Disponible', NULL, NULL, NULL),
(225, 11, '0080', 'Disponible', NULL, NULL, NULL),
(226, 11, '0081', 'Disponible', NULL, NULL, NULL),
(227, 11, '0082', 'Disponible', NULL, NULL, NULL),
(228, 11, '0083', 'Disponible', NULL, NULL, NULL),
(229, 11, '0084', 'Disponible', NULL, NULL, NULL),
(230, 11, '0085', 'Disponible', NULL, NULL, NULL),
(231, 11, '0086', 'Disponible', NULL, NULL, NULL),
(232, 11, '0087', 'Disponible', NULL, NULL, NULL),
(233, 11, '0088', 'Disponible', NULL, NULL, NULL),
(234, 11, '0089', 'Disponible', NULL, NULL, NULL),
(235, 11, '0090', 'Disponible', NULL, NULL, NULL),
(236, 11, '0091', 'Disponible', NULL, NULL, NULL),
(237, 11, '0092', 'Disponible', NULL, NULL, NULL),
(238, 11, '0093', 'Disponible', NULL, NULL, NULL),
(239, 11, '0094', 'Disponible', NULL, NULL, NULL),
(240, 11, '0095', 'Disponible', NULL, NULL, NULL),
(241, 11, '0096', 'Disponible', NULL, NULL, NULL),
(242, 11, '0097', 'Disponible', NULL, NULL, NULL),
(243, 11, '0098', 'Disponible', NULL, NULL, NULL),
(244, 11, '0099', 'Disponible', NULL, NULL, NULL),
(245, 11, '0100', 'Disponible', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion`, `icono_url`) VALUES
(1, 'Tecnología', 'Lo último en gadgets y computación', 'laptop_icon.png'),
(2, 'Vehículos', 'Autos y motocicletas 0km', 'car_icon.png'),
(3, 'Hogar', 'Electrodomésticos y muebles', 'home_icon.png'),
(4, 'Efectivo', 'Premios en dinero', 'fa-money-bill');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_transaccion_boletos`
--

CREATE TABLE `detalle_transaccion_boletos` (
  `id_detalle` int(11) NOT NULL,
  `id_transaccion` int(11) NOT NULL,
  `id_boleto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_transaccion_boletos`
--

INSERT INTO `detalle_transaccion_boletos` (`id_detalle`, `id_transaccion`, `id_boleto`) VALUES
(1, 1, 1),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ganadores`
--

CREATE TABLE `ganadores` (
  `id_ganador` int(11) NOT NULL,
  `id_sorteo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_boleto` int(11) NOT NULL,
  `posicion_premio` int(11) DEFAULT 1,
  `premio_detalle` varchar(255) DEFAULT NULL,
  `fecha_anuncio` timestamp NOT NULL DEFAULT current_timestamp(),
  `entregado` tinyint(1) DEFAULT 0,
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `evidencia_entrega_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_saldos`
--

CREATE TABLE `historial_saldos` (
  `id_historial` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_transaccion` int(11) DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_anterior` decimal(12,2) NOT NULL,
  `saldo_nuevo` decimal(12,2) NOT NULL,
  `tipo_movimiento` enum('Carga','Compra','Premio','Retiro','Ajuste Admin') NOT NULL,
  `nota` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_saldos`
--

INSERT INTO `historial_saldos` (`id_historial`, `id_usuario`, `id_transaccion`, `monto`, `saldo_anterior`, `saldo_nuevo`, `tipo_movimiento`, `nota`, `fecha`) VALUES
(1, 2, 1, -20.00, 500.00, 480.00, 'Compra', 'Compra de 2 boletos para Sorteo iPhone 15', '2026-01-17 03:20:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `created_at`) VALUES
(1, 'Administrador', '2026-01-17 03:20:30'),
(2, 'Cliente', '2026-01-17 03:20:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sorteos`
--

CREATE TABLE `sorteos` (
  `id_sorteo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_boleto` decimal(10,2) NOT NULL,
  `total_boletos_crear` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `caracteristicas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`caracteristicas`)),
  `estado` enum('Borrador','Activo','Finalizado','Pausado') DEFAULT 'Borrador',
  `nro_resolucion_aj` varchar(50) DEFAULT NULL,
  `id_creador` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sorteos`
--

INSERT INTO `sorteos` (`id_sorteo`, `titulo`, `descripcion`, `precio_boleto`, `total_boletos_crear`, `fecha_inicio`, `fecha_fin`, `imagen_url`, `caracteristicas`, `estado`, `nro_resolucion_aj`, `id_creador`, `id_categoria`, `created_at`, `deleted_at`) VALUES
(1, 'Sorteo iPhone 15 Pro Max', 'Gana el último iPhone con 256GB de almacenamiento.', 10.00, 100, '2026-01-16 23:20:30', '2026-02-15 23:20:30', NULL, '{\"color\": \"Titanio Natural\", \"garantia\": \"1 año\"}', 'Activo', 'AJ-2026-001', 1, 1, '2026-01-17 03:20:30', NULL),
(2, 'Toyota Hilux 2026', 'La camioneta más resistente del mercado.', 150.00, 1000, '2026-01-16 23:20:30', '2026-03-17 23:20:30', NULL, '{\"motor\": \"2.8L\", \"traccion\": \"4x4\"}', 'Activo', 'AJ-2026-002', 1, 2, '2026-01-17 03:20:30', NULL),
(3, 'PlayStation 5 Slim', 'Sorteo rápido de fin de semana.', 20.00, 50, '2026-01-17 23:20:30', '2026-01-21 23:20:30', NULL, '{\"controles\": 2, \"juego_incluido\": \"Spider-Man 2\"}', 'Borrador', NULL, 1, 1, '2026-01-17 03:20:30', NULL),
(5, 'Rifa De auto', 'honda civic 2011', 250.00, 40, '2026-01-18 00:00:00', '2026-01-22 23:59:59', 'https://www.auto-data.net/images/f128/Honda-Civic-VIII-sedan_1.jpg', NULL, 'Activo', NULL, 4, NULL, '2026-01-17 20:22:44', NULL),
(6, 'Rifa Honda', 'honda civic 2011', 60.00, 40, '2026-01-18 00:00:00', '2026-01-24 23:59:59', 'https://www.auto-data.net/images/f128/Honda-Civic-VIII-sedan_1.jpg', NULL, 'Activo', NULL, 4, NULL, '2026-01-17 20:43:19', NULL),
(7, 'rifa del culo de victor', 'culo de victor', 1.00, 5, '2026-01-18 00:00:00', '2026-01-22 23:59:59', '0', '[\"sin caracteristicas\"]', 'Activo', '11111', 4, NULL, '2026-01-18 01:23:12', NULL),
(8, 'rifa del culo de victor', 'culo de victor', 1.00, 5, '2026-01-19 00:00:00', '2026-01-22 23:59:59', 'https://www.nibol.com.bo/wp-content/uploads/2022/07/nibol-noticias.jpg', NULL, 'Activo', NULL, 4, NULL, '2026-01-18 01:23:38', NULL),
(9, 'rifa del culo de victor', 'culo de victor', 1.00, 5, '2026-01-19 00:00:00', '2026-01-22 23:59:59', '0', '[\"Motor 2 \\ncolor negro\"]', 'Activo', '111111', 4, NULL, '2026-01-18 01:23:58', NULL),
(10, 'efoes', 'usado', 5.00, 5, '2026-01-19 00:00:00', '2026-01-23 23:59:59', '0', '[\"sin caracteristicas\"]', 'Activo', '11111', 4, NULL, '2026-01-18 01:32:20', NULL),
(11, 'ismael', 'autito', 10.00, 100, '2026-01-18 00:00:00', '2026-01-20 23:59:59', '0', '[\"aa\"]', 'Activo', 'RES-2024', 1, 2, '2026-01-18 03:13:59', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones`
--

CREATE TABLE `transacciones` (
  `id_transaccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('QR Simple','Tarjeta','Tigo Money','Saldo Interno') NOT NULL,
  `gateway_reference` varchar(255) DEFAULT NULL,
  `comprobante_url` varchar(255) DEFAULT NULL,
  `estado_pago` enum('Pendiente','Completado','Fallido','Expirado') DEFAULT 'Pendiente',
  `payment_data` text DEFAULT NULL,
  `fecha_expiracion` timestamp NULL DEFAULT NULL,
  `id_validador` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_confirmacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transacciones`
--

INSERT INTO `transacciones` (`id_transaccion`, `id_usuario`, `monto_total`, `metodo_pago`, `gateway_reference`, `comprobante_url`, `estado_pago`, `payment_data`, `fecha_expiracion`, `id_validador`, `fecha_creacion`, `fecha_confirmacion`) VALUES
(1, 2, 20.00, 'QR Simple', 'REF-99887766', NULL, 'Completado', NULL, NULL, 1, '2026-01-17 03:20:30', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) NOT NULL,
  `apellido_materno` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `saldo_disponible` decimal(12,2) DEFAULT 0.00,
  `estado` enum('Activo','Baneado','Inactivo') DEFAULT 'Activo',
  `id_rol` int(11) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `primer_nombre`, `apellido_paterno`, `apellido_materno`, `email`, `password_hash`, `telefono`, `saldo_disponible`, `estado`, `id_rol`, `last_login`, `created_at`, `deleted_at`) VALUES
(1, 'Juan', 'Perez', 'Vargas', 'admin@sorteo.bo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '77712345', 0.00, 'Activo', 1, NULL, '2026-01-17 03:20:30', NULL),
(2, 'Maria', 'Lopez', 'Daza', 'maria@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '60011223', 500.00, 'Inactivo', 2, NULL, '2026-01-17 03:20:30', NULL),
(3, 'Carlos', 'Mendoza', 'Solis', 'carlos@hotmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '71099887', 50.00, 'Baneado', 2, NULL, '2026-01-17 03:20:30', NULL),
(4, 'Administrador', 'Sistema', '', 'admin@sorteos.web', '$2y$10$U0DRpVh5Ykz7LCnVxR0vge38n7z05YFpcnUrGQR5lWT0v2.bHb17W', '', 0.00, 'Activo', 1, NULL, '2026-01-17 03:34:52', NULL),
(5, 'Roberto', 'Gomez', 'Bolaños', 'roberto@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70011223', 100.00, 'Inactivo', 2, NULL, '2026-01-17 19:51:33', NULL),
(6, 'Ana', 'Ruiz', 'Diaz', 'ana.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70022334', 0.00, 'Baneado', 2, NULL, '2026-01-17 19:51:33', NULL),
(7, 'Luis', 'Torres', 'Mejia', 'luis.ban@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70033445', 50.00, 'Inactivo', 2, NULL, '2026-01-17 19:51:33', NULL),
(8, 'Sofia', 'Castro', 'Lima', 'sofia.permaban@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70044556', 200.00, 'Baneado', 2, NULL, '2026-01-17 19:51:33', NULL),
(9, 'Robero', 'Gomez', 'Bolaños', 'robero@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70011223', 100.00, 'Baneado', 2, NULL, '2026-01-17 20:58:13', NULL),
(10, 'Ana', 'Ruiz', 'Diaz', 'ana.@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70022334', 0.00, 'Inactivo', 2, NULL, '2026-01-17 20:58:13', NULL),
(11, 'Lis', 'Tores', 'Mejia', 'luis.bn@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70033445', 50.00, 'Inactivo', 2, NULL, '2026-01-17 20:58:13', NULL),
(12, 'Sofi', 'Castro', 'Lima', 'sofia.peraban@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70044556', 200.00, 'Baneado', 2, NULL, '2026-01-17 20:58:13', NULL),
(13, 'Admin', 'Sistema', 'Central', 'admin@sorteosweb.com', '$2y$10$zL.IYyOlfDBIpwI2CMraHeK9/U2kVn2HavF/UHh5oSLB1JW.xzgai', '0000000000', 0.00, 'Activo', 1, NULL, '2026-01-18 00:05:10', NULL),
(14, 'paquita ', 'barrero', 'Bolaños', 'paquita@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '7775624', 100.00, 'Inactivo', 2, NULL, '2026-01-18 01:42:48', NULL),
(15, 'Darlin', 'Ruiz', 'añez', 'D.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70022335', 0.00, 'Inactivo', 2, NULL, '2026-01-18 01:42:48', NULL),
(16, 'victor', 'Terraza', 'Mejia', 'Victor.ban@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70033446', 50.00, 'Baneado', 2, NULL, '2026-01-18 01:42:48', NULL),
(17, 'Ronald', 'Pedraza', 'Lima', 'Ronald.permaban@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70044559', 200.00, 'Inactivo', 2, NULL, '2026-01-18 01:42:48', NULL),
(18, 'Carlos', 'Mendoza', 'Vaca', 'carlos.m@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '71055667', 150.50, 'Inactivo', 2, NULL, '2026-01-18 02:07:47', NULL),
(19, 'Elena', 'Rojas', 'Suarez', 'elena_rojas@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '72066778', 10.00, 'Activo', 2, NULL, '2026-01-18 02:07:47', NULL),
(20, 'Fernando', 'Paz', 'Soldan', 'f.paz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '73077889', 500.00, 'Activo', 2, NULL, '2026-01-18 02:07:47', NULL),
(21, 'Gabriela', 'Miranda', 'Loza', 'gaby.miranda@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '74088990', 0.00, 'Inactivo', 2, NULL, '2026-01-18 02:07:47', NULL),
(22, 'Hugo', 'Salazar', 'Justiniano', 'hugo.sal@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '75099001', 75.25, 'Activo', 2, NULL, '2026-01-18 02:07:47', NULL),
(23, 'Irene', 'Vargas', 'Pinto', 'irene.v@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '76011221', 1200.00, 'Activo', 2, NULL, '2026-01-18 02:07:47', NULL),
(24, 'Jorge', 'Aguilar', 'Chavez', 'jorge.ag@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '77022332', 45.00, 'Activo', 2, NULL, '2026-01-18 02:07:47', NULL),
(25, 'Karla', 'Peña', 'Guzman', 'karla.p@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '78033443', 320.10, 'Inactivo', 2, NULL, '2026-01-18 02:07:47', NULL),
(26, 'Mario', 'Duran', 'Rios', 'mario.duran@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '79044554', 0.00, 'Baneado', 2, NULL, '2026-01-18 02:07:47', NULL),
(27, 'Natalia', 'Ortiz', 'Sosa', 'nati.ortiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70055665', 85.00, 'Activo', 2, NULL, '2026-01-18 02:07:47', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_admin`
--
ALTER TABLE `auditoria_admin`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_admin` (`id_admin`),
  ADD KEY `fk_auditoria_tipo` (`id_tipo`);

--
-- Indices de la tabla `auditoria_tipos`
--
ALTER TABLE `auditoria_tipos`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `boletos`
--
ALTER TABLE `boletos`
  ADD PRIMARY KEY (`id_boleto`),
  ADD UNIQUE KEY `uk_sorteo_numero` (`id_sorteo`,`numero_boleto`),
  ADD KEY `idx_limpieza` (`estado`,`fecha_reserva`),
  ADD KEY `id_usuario_actual` (`id_usuario_actual`),
  ADD KEY `id_transaccion_reserva` (`id_transaccion_reserva`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `detalle_transaccion_boletos`
--
ALTER TABLE `detalle_transaccion_boletos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_transaccion` (`id_transaccion`),
  ADD KEY `id_boleto` (`id_boleto`);

--
-- Indices de la tabla `ganadores`
--
ALTER TABLE `ganadores`
  ADD PRIMARY KEY (`id_ganador`),
  ADD KEY `id_sorteo` (`id_sorteo`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_boleto` (`id_boleto`);

--
-- Indices de la tabla `historial_saldos`
--
ALTER TABLE `historial_saldos`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_transaccion` (`id_transaccion`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `sorteos`
--
ALTER TABLE `sorteos`
  ADD PRIMARY KEY (`id_sorteo`),
  ADD KEY `id_creador` (`id_creador`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD PRIMARY KEY (`id_transaccion`),
  ADD KEY `gateway_reference` (`gateway_reference`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_admin`
--
ALTER TABLE `auditoria_admin`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `auditoria_tipos`
--
ALTER TABLE `auditoria_tipos`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `boletos`
--
ALTER TABLE `boletos`
  MODIFY `id_boleto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `detalle_transaccion_boletos`
--
ALTER TABLE `detalle_transaccion_boletos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ganadores`
--
ALTER TABLE `ganadores`
  MODIFY `id_ganador` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_saldos`
--
ALTER TABLE `historial_saldos`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sorteos`
--
ALTER TABLE `sorteos`
  MODIFY `id_sorteo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `transacciones`
--
ALTER TABLE `transacciones`
  MODIFY `id_transaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria_admin`
--
ALTER TABLE `auditoria_admin`
  ADD CONSTRAINT `auditoria_admin_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_auditoria_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `auditoria_tipos` (`id_tipo`);

--
-- Filtros para la tabla `boletos`
--
ALTER TABLE `boletos`
  ADD CONSTRAINT `boletos_ibfk_1` FOREIGN KEY (`id_sorteo`) REFERENCES `sorteos` (`id_sorteo`) ON DELETE CASCADE,
  ADD CONSTRAINT `boletos_ibfk_2` FOREIGN KEY (`id_usuario_actual`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `boletos_ibfk_3` FOREIGN KEY (`id_transaccion_reserva`) REFERENCES `transacciones` (`id_transaccion`);

--
-- Filtros para la tabla `detalle_transaccion_boletos`
--
ALTER TABLE `detalle_transaccion_boletos`
  ADD CONSTRAINT `detalle_transaccion_boletos_ibfk_1` FOREIGN KEY (`id_transaccion`) REFERENCES `transacciones` (`id_transaccion`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_transaccion_boletos_ibfk_2` FOREIGN KEY (`id_boleto`) REFERENCES `boletos` (`id_boleto`);

--
-- Filtros para la tabla `ganadores`
--
ALTER TABLE `ganadores`
  ADD CONSTRAINT `ganadores_ibfk_1` FOREIGN KEY (`id_sorteo`) REFERENCES `sorteos` (`id_sorteo`),
  ADD CONSTRAINT `ganadores_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `ganadores_ibfk_3` FOREIGN KEY (`id_boleto`) REFERENCES `boletos` (`id_boleto`);

--
-- Filtros para la tabla `historial_saldos`
--
ALTER TABLE `historial_saldos`
  ADD CONSTRAINT `historial_saldos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `historial_saldos_ibfk_2` FOREIGN KEY (`id_transaccion`) REFERENCES `transacciones` (`id_transaccion`);

--
-- Filtros para la tabla `sorteos`
--
ALTER TABLE `sorteos`
  ADD CONSTRAINT `sorteos_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `sorteos_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `transacciones`
--
ALTER TABLE `transacciones`
  ADD CONSTRAINT `transacciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `limpiar_reservas_expiradas` ON SCHEDULE EVERY 1 MINUTE STARTS '2026-01-16 23:20:15' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE boletos 
  SET estado = 'Disponible', 
      id_usuario_actual = NULL, 
      id_transaccion_reserva = NULL,
      fecha_reserva = NULL
  WHERE estado = 'Reservado' 
  AND fecha_reserva < NOW() - INTERVAL 15 MINUTE$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
