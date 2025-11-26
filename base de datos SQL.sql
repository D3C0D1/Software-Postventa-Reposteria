-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-07-2025 a las 04:06:12
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `kalua_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `nit` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#4e73df',
  `color_secundario` varchar(7) DEFAULT '#1cc88a',
  `color_texto` varchar(7) DEFAULT '#ffffff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nombre_empresa`, `nit`, `direccion`, `logo_url`, `color_primario`, `color_secundario`, `color_texto`) VALUES
(1, 'Kalua', '1000189717-5', 'Barrio Juan Pablo', 'public/uploads/logo.png', '#212529', '#343A40', '#F8F9FA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_caja`
--

CREATE TABLE `movimientos_caja` (
  `id` int(11) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `salsas_obligatorias` int(11) NOT NULL DEFAULT 0,
  `adiciones_obligatorias` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio_venta`, `imagen_url`, `estado`, `salsas_obligatorias`, `adiciones_obligatorias`) VALUES
(3, 'Ensalada 4 frutas', 15000.00, 'public/uploads/202ad70d6c1ecbae1d6f1db2ca7b4f02.jpg', 'activo', 0, 0),
(4, 'POPFLIX VAINILLA', 8000.00, 'public/uploads/dad942958c0d96a3cba39199cd6fbec9.jpg', 'activo', 0, 0),
(5, 'PACHIS VAINILLA', 5000.00, 'public/uploads/8b4649d5e47fefcc1541ddddfd259fcb.jpg', 'activo', 0, 0),
(6, 'KOKOCUP VAINILLA', 4000.00, 'public/uploads/9a58bcd73d66dc0f32e8ff168fb273e3.jpg', 'activo', 0, 0),
(7, 'KOKOCUP YOGURT', 4000.00, 'public/uploads/3410dbc6e3005ec63f7f156401919366.jpg', 'activo', 0, 0),
(8, 'POPFLIX YOGURT', 8000.00, 'public/uploads/8ac4086ab94d686d8d2923408cc2bf0f.jpg', 'activo', 0, 0),
(9, 'PACHIS YOGURT', 5000.00, 'public/uploads/ac8fc43b9607f6092c0fe61bbff93416.jpg', 'activo', 0, 0),
(10, 'SWEET FRUIT YOGURT', 12000.00, 'public/uploads/dfc3cad36b2da5a68f9be6d787b8d741.jpg', 'activo', 0, 0),
(11, 'SWEET FRUIT VAINILLA', 12000.00, 'public/uploads/f037daec0ca11e4524f8333c8ae46b59.jpg', 'activo', 0, 0),
(12, 'WAFFLE YOGURT', 15000.00, 'public/uploads/131efb7f3b03eee512f72fb96e0ea55b.jpg', 'activo', 0, 0),
(13, 'WAFFLE VAINILLA', 15000.00, 'public/uploads/180538937c8eb695845fbad073078ad4.jpg', 'activo', 0, 0),
(14, 'TOSCANO YOGURT', 9000.00, 'public/uploads/b307f1bba476b19ee98b0639469b55c2.jpg', 'activo', 0, 0),
(15, 'TOSCANO VAINILLA', 9000.00, 'public/uploads/1c230d53697fca55dfc00147511a8f47.jpg', 'activo', 0, 0),
(16, 'KOKOCUP CHOCOLATE YOGURT', 6000.00, 'public/uploads/1c975dad628a71444b006a2e37800971.jpg', 'activo', 2, 2),
(17, 'KOKOCUP CHOCLATE VAINILLA', 6000.00, 'public/uploads/8320af92023a44626ebf914adff91b3b.jpg', 'activo', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `subproducto_id` int(11) NOT NULL,
  `cantidad_necesaria` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`id`, `producto_id`, `subproducto_id`, `cantidad_necesaria`) VALUES
(6, 17, 6, 1.00),
(7, 17, 7, 260.00),
(8, 4, 8, 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subproductos`
--

CREATE TABLE `subproductos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `stock` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `categoria` enum('ingrediente','salsa') DEFAULT 'ingrediente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subproductos`
--

INSERT INTO `subproductos` (`id`, `nombre`, `precio`, `imagen`, `stock`, `unidad_medida`, `categoria`) VALUES
(4, 'Barquillo', 2000.00, '065bb92c45fd0609ec63e518a546a176.jpg', -6.00, 'unidad', 'ingrediente'),
(5, 'Fresas', 2000.00, '4daf2c13d9ba3fe24c558cda151fc474.jpg', 1.00, 'unidad', 'ingrediente'),
(6, 'Cono', 0.00, 'ce0392f3ee12f86f793fde9998e5f778.jpg', -3.00, 'unidad', 'ingrediente'),
(7, 'Vainilla', 0.00, 'fca9900b6cd7333db5ecd5f0babf7d1b.jpg', -1420.00, 'gramos', 'ingrediente'),
(8, 'Vasos', 0.00, '0fe7659bd791640f4d58ff893625edf5.jpg', 21.00, 'unidad', 'ingrediente'),
(9, 'Frutos rojos', 1000.00, '8b9d2dacb4fbd15975585c72a9a8ec2c.jpg', -7.00, 'unidad', 'salsa'),
(10, 'Cereal', 1000.00, 'ab7e26f9db373898cfe61d87a3962c9f.jpg', 21.00, 'gramos', 'ingrediente'),
(11, 'Portacomida', 0.00, 'dd343975600033d7f56b5fc708b3a4bf.jpg', 9.00, 'unidad', 'ingrediente'),
(12, 'GUSANITOS', 2000.00, '204dad7f75c5ac10fcfaf9c593aba90f.jpg', 20.00, 'unidad', 'ingrediente'),
(13, 'MORITAS', 2000.00, '741cef3ba713e155f40b11c169d9a86c.jpg', 18.00, 'unidad', 'ingrediente'),
(14, 'TRULULU SABORES', 2000.00, '126d2f61a6586213d14c91a2934298dc.jpg', 11.00, 'unidad', 'ingrediente'),
(15, 'CHOCOMELOS', 2000.00, 'fe9acd5842cb908205910696f8bde13b.jpg', 45.00, 'unidad', 'ingrediente'),
(16, 'DURAZNO', 2000.00, 'b7a41d132f52220a81da1ef238917db6.jpg', 10.00, 'unidad', 'ingrediente'),
(17, 'KIWI', 2000.00, 'c18a0ce67afe5a2feff24b391deb3795.jpg', 6.00, 'unidad', 'ingrediente'),
(18, 'BANANO', 2000.00, '74913a465c0d1a1229018b62dd188500.jpg', 0.00, 'unidad', 'ingrediente'),
(19, 'OSITOS', 2000.00, 'a1de31e1157becfa5b5a08f6d1276781.jpg', 28.00, 'unidad', 'ingrediente'),
(20, 'CHOCOLATINA JET', 2000.00, 'a5e7bd4984c08b6f97450a297267c3b3.jpg', 24.00, 'unidad', 'ingrediente'),
(21, 'MANI TRITURADO', 2000.00, 'be1a80cca16909e73f49317b3fe0176e.jpg', 300.00, 'gramos', 'ingrediente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','invitado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`) VALUES
(3, 'Operador', 'ingeniero20211@gmail.com', '$2y$10$LcoSUYpLPdZM4LBT1rP9QuLcDFr3Id7FOmr5TvZh5zPOMKzPvbt.a', 'admin'),
(4, 'Tatiana', 'tatiana@kalua.com', '$2y$10$mfgU63BQ9Y/mEtBlSUBrzebsEqkxADz2abXVzCrHPvu3Fru3mQbWC', 'admin'),
(5, 'Empleado', 'empleado@kalua.com', '$2y$10$I0pIczk4RRtep7x/JWuONOxwdjoANEksSMPLdcisF9obFAwYkuBWK', 'invitado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `detalle_venta` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_detalles`
--

CREATE TABLE `venta_detalles` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `subproducto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `subproducto_id` (`subproducto_id`);

--
-- Indices de la tabla `subproductos`
--
ALTER TABLE `subproductos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `subproductos`
--
ALTER TABLE `subproductos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`subproducto_id`) REFERENCES `subproductos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `venta_detalles`
--
ALTER TABLE `venta_detalles`
  ADD CONSTRAINT `venta_detalles_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `venta_detalles_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
