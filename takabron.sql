-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: sdb-72.hosting.stackcp.net
-- Generation Time: Jan 22, 2026 at 09:41 PM
-- Server version: 10.6.18-MariaDB-log
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `takabron-35303631abd7`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cantina_categories`
--

CREATE TABLE `cantina_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cantina_categories`
--

INSERT INTO `cantina_categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(2, 'TRAGOS DE LA CASA', '2024-06-07 13:15:08', '2024-06-07 13:15:08'),
(4, 'CERVEZAS', '2024-06-07 13:27:06', '2024-06-07 13:27:06'),
(6, 'COCTELERÍA', '2024-06-07 13:56:38', '2024-06-07 13:56:38'),
(7, 'VINOS', '2024-06-07 14:02:19', '2024-06-07 14:02:19'),
(8, 'BEBIDAS SIN ALCOHOL', '2024-06-07 14:12:07', '2024-06-07 14:12:07'),
(15, 'COCTELERIA DE TEMPORADA 🎄✨️🎉💃', '2025-10-04 14:10:18', '2025-12-09 16:36:05'),
(16, 'MARGARITAS', '2025-10-11 17:54:19', '2025-10-11 17:54:19'),
(17, 'MEZCALITAS', '2025-10-11 17:56:11', '2025-10-11 17:56:11'),
(18, 'MOJITOS', '2025-10-11 17:57:17', '2025-10-11 17:57:17'),
(19, 'OTROS', '2025-10-11 18:37:51', '2025-10-11 18:37:51'),
(20, 'CAFÉ', '2025-10-11 18:40:08', '2025-10-11 18:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `cantina_items`
--

CREATE TABLE `cantina_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cantina_items`
--

INSERT INTO `cantina_items` (`id`, `name`, `description`, `price`, `category_id`, `image`, `created_at`, `updated_at`) VALUES
(10, 'HIJOLE', 'Hijole', 10.00, 2, 'cantina_images/LiS9qKVOKdJCPfH7VjByEOt3lhCn6KfL6bazeyZd.jpg', '2024-06-07 13:17:20', '2024-07-15 01:06:20'),
(11, 'EL KBRON', 'El Kbron', 10.00, 2, 'cantina_images/kFtJNUaJA9gsE1KSqeE22ndEj4HMpADlnjEbwpdY.jpg', '2024-06-07 13:18:50', '2024-07-14 18:38:11'),
(12, 'EL KBRONCILLO', 'El Kbroncillo', 10.00, 2, 'cantina_images/KqU7a08ulpD4F0himXVea4MlgnIYv5zEJrwblM8O.jpg', '2024-06-07 13:20:38', '2024-07-14 18:39:51'),
(13, 'CANTARITO', 'Cantarito', 14.00, 2, 'cantina_images/W9ufedhD0jD3DkpNN48npvfh4knoOg2QZV0VmE0b.jpg', '2024-06-07 13:21:48', '2024-07-14 18:40:56'),
(14, 'PINK SEÑORITA', 'Pink Señorita', 10.00, 2, 'cantina_images/GeMQWV7iX8N46BxUEizpNZZZWWSplBMc7bAYQekh.jpg', '2024-06-07 13:23:02', '2024-07-14 18:41:52'),
(15, 'BLUE SEÑOR', 'Blue Señor', 10.00, 2, 'cantina_images/S52VBcpqzvKap51u3NWYUjEURtrmENqGFwiox8Uu.jpg', '2024-06-07 13:24:53', '2024-07-14 18:44:33'),
(16, 'EL HIDALGO', 'Disfruta del sabor ahumado del mezcal en esta sangría de la casa.', 10.00, 6, 'cantina_images/vITldnO71DoZFbr44Kx2ICkBXETHGPQwjFMKRww1.jpg', '2024-06-07 13:26:13', '2024-07-14 16:09:48'),
(18, 'MODELO RUBIA', 'Modelo Rubia', 3.50, 4, 'cantina_images/Bi14aJ42Qz5FGP5NTi6R35wA2ILNbFSh7NN8TmnW.jpg', '2024-06-07 13:28:37', '2024-11-04 20:20:39'),
(20, 'MILLER LITE', 'Miller Light', 2.75, 4, 'cantina_images/rA49ZhyJ4k8TRLTJ5VIhgRsjPKtsVmnX6TwAKeHO.jpg', '2024-06-07 13:30:05', '2024-11-04 20:21:07'),
(21, 'COORS LIGHT', 'Coors Light', 2.75, 4, 'cantina_images/yc7eXXLzihyIHnb6MQEIaPZe3f4SvliNbYUQGpvN.jpg', '2024-06-07 13:31:19', '2024-07-14 19:08:16'),
(22, 'HEINEKEN', 'Heineken', 3.50, 4, 'cantina_images/5fAR9k7EmoMISPEE2GVxjR8FGBY7yKC2MaXLVKGa.jpg', '2024-06-07 13:32:18', '2024-11-04 20:21:23'),
(23, 'HEINEKEN 0%', 'Heineken 0%', 3.25, 4, 'cantina_images/6Va5u5JFxiwou8GhQAJD7t6mPWrV642NuWY19wwK.jpg', '2024-06-07 13:32:57', '2024-07-14 19:10:52'),
(24, 'HEINEKEN LIGHT', 'Heineken Light', 3.25, 4, 'cantina_images/20EdRfcoRbg0W9uY85GcaYKedmauLBLxSVx3tG5r.jpg', '2024-06-07 13:39:29', '2024-07-14 19:11:38'),
(25, 'MICHELOB ULTRA', 'Michelob Ultra', 3.50, 4, 'cantina_images/zy1Gcr4ytTylTTuZiDAU6dFAwFAOqnf7CRuVxGkn.jpg', '2024-06-07 13:40:03', '2024-11-04 20:21:43'),
(26, 'MICHELOB PURE GOLD', 'Michelob Pure Gold', 4.00, 4, 'cantina_images/U1b7LvATKapKriHakjf0idjyp8nOsQhZQZDJiMrq.jpg', '2024-06-07 13:40:32', '2024-07-14 19:17:35'),
(27, 'MEDALLA ULTRA', 'Medalla Ultra', 3.00, 4, 'cantina_images/qWQjfhxGHi7tL7x04szu2Vcu3zJLwagmSvIZ518z.jpg', '2024-06-07 13:41:09', '2024-07-14 19:18:49'),
(28, 'MEDALLA', 'Medalla', 2.75, 4, 'cantina_images/qbdi3f53eVaXJozDPxIW1gbaZRPxkd8PmklrTXiD.jpg', '2024-06-07 13:44:02', '2024-07-14 19:19:38'),
(29, 'CORONA EXTRA', 'Corona Extra', 3.50, 4, 'cantina_images/n736BX7XK3drIf3OjHN04v9UGnXAkitD6Ga3lJVC.jpg', '2024-06-07 13:44:50', '2024-11-04 20:21:58'),
(30, 'MARGARITAS A LA ROCA O FROZEN', 'Original, Jalapeño🌶️, Lavanda, Parcha, Tamarindo, Coco, Fresa, Kiwi, Mangó, Pama, Piña, Piña Colada, Guayaba, Jengibre, Rasberry, Blue Curraçao, Guanabana y Melón. A la roca / +$2.00 Frozen', 10.00, 16, 'cantina_images/zQ37MiXCqT2U5bqkoVQUBoQUJoK3XxkygoII9Ztm.jpg', '2024-06-07 13:48:33', '2025-10-11 17:55:20'),
(31, 'MEZCALITAS A LA ROCA O FROZEN', 'Original, Jalapeño🌶️, Lavanda, Parcha, Tamarindo, Coco, Fresa, Kiwi, Mangó, Pama, Piña, Piña Colada, Guayaba, Jengibre, Rasberry, Blue Curraçao, Guanabana y Melón. / +$2.00 Frozen', 12.00, 17, 'cantina_images/CjhRpKYvW8855FrL1I7tmo8ltobbka4c0yzpApRw.jpg', '2024-06-07 13:49:08', '2025-10-11 19:05:53'),
(32, 'MOJITOS', 'Original, Jalapeño🌶️, Lavanda, Parcha, Tamarindo, Coco, Fresa, Kiwi, Mangó, Pama, Piña, Piña Colada, Guayaba, Jengibre, Rasberry, Blue Curraçao, Guanabana y Melón./ Frozen $12.00', 9.00, 18, 'cantina_images/YYumBVbG0HBaOt6PQL23wB9JfnSAFftfAqBljeUd.jpg', '2024-06-07 13:49:37', '2025-10-11 17:57:39'),
(33, 'CORONARITA', 'Margarita servida con un shot de Corona Extra', 10.00, 2, 'cantina_images/U8YvkqxynadlPwDNHp8Qahm98rskGwQ4TNg7GVpH.jpg', '2024-06-07 13:51:59', '2024-07-15 01:08:34'),
(35, 'MICHELADA', 'Michelada', 8.00, 2, 'cantina_images/U27dfKoUvmGHtlK651wm5vZkkea3NjtcbMXNdZCR.jpg', '2024-06-07 13:53:00', '2024-07-15 01:09:03'),
(37, 'OLD FASHIONED', 'Old Fashioned', 12.00, 6, 'cantina_images/Q4rZlZWHgr47kzvmkSykr89NksIIbQb1TmNhxRRQ.jpg', '2024-06-07 13:57:31', '2024-07-14 22:54:31'),
(39, 'MEXICAN MULE', 'Variación mexicana con tequila del tradicional Moscow Mule.', 12.00, 6, 'cantina_images/rQe0swhWEhMmUzvt2x3vdhzKiVcp3X9ORXdeWTM1.jpg', '2024-06-07 14:00:35', '2025-10-13 16:50:47'),
(43, 'JARRITOS', 'Variedad', 2.75, 8, 'cantina_images/99nESeLNCl7L91gNwldXkA8UtkkTyOhQZ15kDVDA.jpg', '2024-06-07 14:12:44', '2024-11-04 20:22:46'),
(44, 'LIMONADA', 'Variedad de sabores: original, lavanda, fresa, parcha, melón, mangó, coco.', 4.00, 8, 'cantina_images/PPTkhgrwC7WKPTmknTqEmWjhyrjjM0JMwWFuWmGU.jpg', '2024-06-07 14:13:13', '2024-07-15 00:32:29'),
(45, 'PIÑA COLADA 14oz', 'Piña Colada', 6.00, 8, 'cantina_images/jQQbHm0BGwm8FFAcWW9gZEazjS5Kyj6rYdmDf8gH.jpg', '2024-06-07 14:16:39', '2024-07-15 00:42:11'),
(46, 'PIÑA COLADA 10oz', 'Piña Colada 10oz', 4.00, 8, 'cantina_images/G3V3bn1CxoRzOWJm2I44mkgnfRWDu7bZQoAmA36y.jpg', '2024-06-07 14:18:14', '2024-11-04 20:23:07'),
(47, 'REFRESCOS', 'Coca Cola, Sprite, Coca Cola Zero, Spite Zero', 1.75, 8, 'cantina_images/hyhbNh7Cuh9c7NjC6MrgWExMleIPg74ECOiGHgsj.jpg', '2024-06-07 14:19:20', '2024-11-04 20:24:03'),
(48, 'AGUA', 'Agua embotellada', 1.25, 8, 'cantina_images/mdaXf6UXHcdEKkuJD8P6KrsB1F85bMGS1qCJWSeV.jpg', '2024-06-07 14:19:53', '2024-11-04 20:24:19'),
(49, 'HORCHATA', 'Horchata', 5.00, 8, 'cantina_images/LzV8w8A2yVshsgEgSU4swQQ4iDb5OgAcGFAHu6L0.jpg', '2024-06-07 14:24:46', '2025-10-11 18:45:18'),
(50, 'JAMAICA', 'Jamaica', 5.00, 8, 'cantina_images/uRZlf8IgLHSfOptFPzdx4ytHmP1hkrIwju1t43yz.jpg', '2024-06-07 14:25:20', '2025-10-11 18:45:04'),
(51, 'FRAPÉS', 'Fresa, parcha  mangó, rasberry, tamarindo, melón. / Nutella +1.25', 7.00, 8, 'cantina_images/4pJXNz0M9uH3avNduqb8LLOkJBYTcM07piKXMTBq.jpg', '2024-06-07 14:26:32', '2025-10-11 18:45:29'),
(54, 'PLAYA DEL CARMEN', 'A la roca o frozen +$2.00', 10.00, 6, 'cantina_images/jfh2z0mhcSLxDuDufzwNzH3De1CJ06eMdFfiI1E1.jpg', '2024-07-14 16:19:47', '2024-08-25 13:19:57'),
(55, 'TULUM', 'A la roca o frozen +$2.00', 10.00, 6, 'cantina_images/S1f5ncSeUp9aalgu1u0NQmGmDQDho5b35KWCa7sc.jpg', '2024-07-14 16:20:15', '2024-08-25 13:20:25'),
(62, 'JUGOS NATURALES', 'Variedad de sabores: china, agua de coco, toronja blanca  toronja rosada, parcha, tamarindo, cranberry, piña.', 4.00, 8, 'cantina_images/SVt3RSbPxtFEzrW48nirt1jZGd49M06kroKZ8X3q.png', '2024-07-14 16:56:35', '2024-07-15 01:05:09'),
(63, 'Agua Perrier', NULL, 3.25, 8, 'cantina_images/s8dqoVz1PLZUzZt3CyAGs26NrL5tJL7FQdPSNC0K.jpg', '2024-07-14 16:57:33', '2024-11-04 20:24:33'),
(64, 'LA LLORONA', 'Coctel ganador de la competencia de Bartender Auténtico de Palo Viejo como creación de nuestro bartender.', 12.00, 6, 'cantina_images/wJmMRx9kbWOzJINkr07kGljVfhc5oqXNPwXSd4O7.jpg', '2024-10-03 13:54:53', '2025-12-09 16:37:24'),
(68, 'LA PARRANDERA', 'Prueba el lado dulce de la Navidad 🎄✨️ con este cócteles de temporada. Tonos frutales, un toque de canela y una variedad de rones.', 12.00, 6, 'cantina_images/vpIrIGbrufQUlBuoA6VFWMXBQlnDJ8ZCKiaMBGk6.jpg', '2024-11-09 12:41:38', '2025-02-01 17:14:00'),
(84, 'PACÍFICO', 'lager clara y refrescante de estilo Pilsner, originaria de Mazatlán, México. Se caracteriza por su color dorado pálido, sabor suave y ligero, y un final refrescante con un toque de amargor. Se elabora con una combinación de cebada malteada, maíz y lúpulo.', 3.00, 4, 'cantina_images/Zof95k9BJd2tRZ636AMa8aj0ipQ5hwSDuG2fgV8O.jpg', '2025-10-11 18:04:56', '2025-10-11 18:04:56'),
(85, 'BOHEMIA', NULL, 4.25, 4, 'cantina_images/U6cXsAgtxxVF8mMgNFmYwYf0jzKKzgSOjhXRB42K.jpg', '2025-10-11 18:09:04', '2025-12-11 17:51:50'),
(86, 'VIVA LA CHELA', NULL, 5.75, 4, 'cantina_images/J52eZyqwNruMutCyHNlPzyLW7RWlfl3J1rQXht41.jpg', '2025-10-11 18:12:04', '2025-12-11 17:52:17'),
(87, 'ESPRESSO MARTINI', NULL, 12.00, 6, 'cantina_images/7jUhJahBgG18NJrR0ydNJil8LXJZHmHS5tKAhQ4J.png', '2025-10-11 18:16:08', '2025-10-11 18:27:09'),
(88, 'NOCHE EN OAXACA', 'Blueberrie, fresa y ron blanco para disfrutar de cualquier ocasión.', 12.00, 6, 'cantina_images/8BQpnEdYMjv1t9wkKnK5e5Lte7NH0YAU4nBxWk0G.jpg', '2025-10-11 18:25:54', '2025-10-11 18:25:54'),
(89, 'MOSCOW MULE', 'Clásico, refrescante y popular, conocido por su sabor picante a jengibre con notas cítricas de limón.', 12.00, 6, 'cantina_images/mrtnqsOJp0SJnIjc9FDrfe5mNcsDKlOgFaxJa0NI.jpg', '2025-10-11 18:29:09', '2025-10-11 18:29:09'),
(91, 'DE MUERTE', 'Monastrell 50%, Syrah 50%. Viñas viejas. Reposadas en barricas de roble francés. Color rojo violeta intenso, olor suave de fruta roja con matices balsámico, roble y especias. Suave y untuiso. Clásico 2021.', 28.00, 7, 'cantina_images/ADroow4w2JKitknCDH9xBmgpVeNGx2Kk2h5hfBwS.jpg', '2025-10-11 18:36:21', '2025-10-11 18:36:21'),
(92, 'UNBUZZD', 'Es una bebida o suplemento dietético que acelera el metabolismo del alcohol para reducir los síntomas de la embriaguez y la resaca', 7.00, 19, 'cantina_images/MJb4kIZY4maP7BCB9iX2lVS6Z8Uksgw107aGWoHE.png', '2025-10-11 18:39:58', '2025-10-11 18:39:58'),
(93, 'ESPRESSO 8OZ', NULL, 2.00, 20, 'cantina_images/u8ZRn3bsKlEk5stxKdOjItZkx4T1unnW4diOXRLc.png', '2025-10-11 18:41:00', '2025-10-11 18:41:00'),
(94, 'CAPUCCINO 8OZ', NULL, 3.00, 20, 'cantina_images/9dGEHlu4ahRiiQSTqzqzhPn2UZSi5SMjYKDR4zUt.png', '2025-10-11 18:41:47', '2025-10-11 18:41:47'),
(95, 'LATTE 8OZ', NULL, 2.50, 20, 'cantina_images/VaNgWNbxHgd6sWaZq9BDJcbJPP3TCBiFPYcdML0j.png', '2025-10-11 18:42:27', '2025-10-11 18:42:27'),
(96, 'ICED COFFE', NULL, 5.50, 20, 'cantina_images/B4PZFPTx6yPn8xPG45QYnsOh4a9UiKzK8zUQZVfi.jpg', '2025-10-11 18:44:36', '2025-10-11 18:44:36'),
(97, 'BRISA NAVIDEÑA 🎄', '¡El postre de la abuela en vaso! Sabor que te transporta directamente al Arroz con Dulce navideño. Es refrescante, elegante y la excusa perfecta para brindar.', 12.00, 15, 'cantina_images/3RrXdeGJKaKfvzdCh9zFV0vxbRzg3yNfrKlECZaK.jpg', '2025-12-09 16:39:31', '2025-12-09 16:39:31'),
(98, 'EL JÍBARO 🇵🇷', 'Fuerte, con carácter, y tan auténtico como nuestra gente. El trago que te prepara para el jangueo.', 12.00, 15, 'cantina_images/xeOkz67HfbjJHy2S6sJmAlAIZN5hc9Zj3KdSKe9z.jpg', '2025-12-09 16:40:36', '2025-12-09 16:40:36'),
(99, 'LA PARRANDERA 💃🎉', 'Dulce, pícara y perfecta para acompañar la algarabía navideña. ¡Saca la bailarina que llevas dentro!', 12.00, 15, 'cantina_images/A433UmB6FxwsmR0AQZsnI7pS6GLfM9y5nSIJYYKx.jpg', '2025-12-09 16:41:24', '2025-12-09 16:41:24'),
(100, 'CHUPITO NAVIDEÑO 🍶', 'Servido en una bolita de navidad, este shot es pura alegría concentrada. ¡El must-try de la temporada!', 2.00, 15, 'cantina_images/vAVx41SM5XkcFWH8Dd30JMy2tuj2MykmWEu5l0H3.jpg', '2025-12-09 16:42:27', '2025-12-09 16:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `show_on_cover` tinyint(1) NOT NULL DEFAULT 0,
  `cover_title` varchar(255) DEFAULT NULL,
  `cover_subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `relevance` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `order`, `show_on_cover`, `cover_title`, `cover_subtitle`, `created_at`, `updated_at`, `relevance`) VALUES
(2, 'APERITIVOS', 0, 0, NULL, NULL, '2024-06-07 10:22:56', '2024-06-07 10:22:56', 0),
(3, 'TACOS', 0, 0, NULL, NULL, '2024-06-07 10:41:54', '2024-06-07 10:41:54', 0),
(4, 'BURRITOS', 0, 0, NULL, NULL, '2024-06-07 10:57:12', '2024-06-07 10:57:12', 0),
(5, 'QUESADILLAS', 0, 0, NULL, NULL, '2024-06-07 11:14:16', '2024-06-07 11:14:16', 0),
(6, 'MENÚ VEGANO / VEGETARIANO', 0, 0, NULL, NULL, '2024-06-07 11:26:32', '2024-06-07 11:26:32', 0),
(7, 'PLATOS DE LA CASA', 0, 0, NULL, NULL, '2024-06-07 11:32:13', '2024-06-07 11:32:13', 0),
(8, 'BANDEJAS MEXICANAS', 0, 0, NULL, NULL, '2024-06-07 12:48:51', '2024-06-07 12:48:51', 0),
(9, 'MENÚ DE NIÑOS', 0, 0, NULL, NULL, '2024-06-07 12:53:33', '2024-06-07 12:53:33', 0),
(10, 'POSTRES', 0, 0, NULL, NULL, '2024-06-07 12:57:05', '2024-06-07 12:57:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `category_subcategories`
--

CREATE TABLE `category_subcategories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_tax`
--

CREATE TABLE `category_tax` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cocktails`
--

CREATE TABLE `cocktails` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `featured_on_cover` tinyint(1) NOT NULL DEFAULT 0,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cocktail_categories`
--

CREATE TABLE `cocktail_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `show_on_cover` tinyint(1) NOT NULL DEFAULT 0,
  `cover_title` varchar(255) DEFAULT NULL,
  `cover_subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cocktail_category_tax`
--

CREATE TABLE `cocktail_category_tax` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cocktail_category_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cocktail_dish`
--

CREATE TABLE `cocktail_dish` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cocktail_id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cocktail_subcategories`
--

CREATE TABLE `cocktail_subcategories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cocktail_category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cocktail_tax`
--

CREATE TABLE `cocktail_tax` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cocktail_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `featured_on_cover` tinyint(1) NOT NULL DEFAULT 0,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `name`, `description`, `price`, `image`, `category_id`, `position`, `created_at`, `updated_at`, `visible`, `featured_on_cover`, `subcategory_id`) VALUES
(2, 'JALAPEÑO POPPERS 🌶️', 'Jalapeños empanados fritos, rellenos de queso crema. Acompañados por salsa de la casa.', 8.25, 'images/BMpiEXX7gTPkbpBWv7M9qX9sOxguxLKLDGXcCPv5.jpg', 2, 1, '2024-06-07 10:24:25', '2024-07-10 19:40:56', 1, 0, NULL),
(3, 'QUESO DE LA CASA 🌶️', 'Queso blanco derretido con jalapeño. Servido con nachos.', 7.25, 'images/PJk5aW9WQG3ZtrkvdfHWKRLRqhip8VpiJ127W0Av.jpg', 2, 2, '2024-06-07 10:25:59', '2024-11-04 19:51:44', 1, 0, NULL),
(4, 'CHORIQUESO 🌶️', 'Queso de la casa con chorizo auténtico mexicano acompañado de nachos.', 8.25, 'images/lXTrXvWc5woKZdv0gSBCwCxNpQA788MHxHjczPqz.jpg', 2, 3, '2024-06-07 10:27:38', '2024-11-04 19:53:55', 1, 0, NULL),
(5, 'SORULLITOS JALAPEÑO 🌶️', 'Bolitas de sorullitos rellenas de queso y jalapeño. Acompañados por salsa chipotle.', 6.50, 'images/EsAk9rvIRdG1NxSwTTOaA41vIzOmcgpJaUafdCjr.jpg', 2, 4, '2024-06-07 10:29:33', '2024-11-04 19:54:08', 1, 0, NULL),
(6, '\"LA CHULITA\"', 'Mini sopa de arroz mexicano al estilo de la casa. Acompañada de nachos. Proteínas a escoger: Pollo, Carnita o Birria.', 7.25, 'images/qKfDIwCc99tU3EewR6WEGLvG8dqyRi1P54FURWQl.jpg', 2, 5, '2024-06-07 10:31:08', '2024-11-04 19:54:23', 1, 0, NULL),
(7, '\"PA MIS CARNALES\"', '5 jalapeño poppers, 5 sorullitos jalapeños, esquite, 5 mozzarella sticks, 5 cordon bleu y nachos con queso o salsa.', 28.00, 'images/62D4BixUBciXQ7jhw3VwLvdmnhg97WDOtKDGWf7Y.jpg', 2, 6, '2024-06-07 10:33:06', '2025-12-28 21:09:58', 1, 0, NULL),
(8, 'ELOTE', 'Mazorca de maíz fresca, cubierta con crema de la casa, queso y tajín.', 6.50, 'images/rLP4mo65Kl6K9TLMdHwRbKjCXS9MgvsiQaIYljBr.jpg', 2, 7, '2024-06-07 10:34:05', '2024-11-04 19:56:53', 1, 0, NULL),
(9, 'ESQUITE', 'Maíz en grano mezclado con crema de la casa, queso y tajín.', 5.50, 'images/1717760100.jpg', 2, 8, '2024-06-07 10:35:00', '2024-11-04 19:57:06', 1, 0, NULL),
(10, 'REFRITO', 'Frijoles majados cubiertos con queso mozzarella y servidos con nachos.', 5.00, 'images/5cALzhMk8LlcK6P5pZ81Yp0L935SaIDB2wMKMAC3.jpg', 2, 9, '2024-06-07 10:36:09', '2024-11-04 19:57:24', 1, 0, NULL),
(11, 'NACHOS 🌶️', 'Con queso o salsa.', 4.25, 'images/DP71N3UQJZ3ZDPgySrviQ2Y5zBr3uwTcUVh4200F.jpg', 2, 10, '2024-06-07 10:37:01', '2024-07-10 19:44:56', 1, 0, NULL),
(12, 'GUACAMOLE', 'Delicioso guacamole con pico de gallo. Servido con nachos.', 7.50, 'images/PGqSIfwcgzNNDZzGsJtNOgTac7ryTXQlLZCGlBIW.jpg', 2, 11, '2024-06-07 10:37:44', '2024-11-04 19:57:44', 1, 0, NULL),
(13, 'PICO DE GALLO', 'Servido con nachos', 7.25, 'images/YJVzGF6GDy0R3BBI0SHO5RxxP6NmwJvBHTrUIk1E.jpg', 2, 12, '2024-06-07 10:38:19', '2024-11-04 19:58:00', 1, 0, NULL),
(14, 'MOZZARELLA STICKS', 'Servidos con salsa marinara.', 8.00, 'images/JUF6DAxvErz3E2i21oFoQXWpBluJsH6U1zYuJdjN.jpg', 2, 13, '2024-06-07 10:38:55', '2024-07-12 21:07:08', 1, 0, NULL),
(15, 'COCTÉL DE CAMARONES', 'Coctél de camarones', 14.00, 'images/rPBkjHr18wDlY3lWVB1Qj9kcSudBdF8LxbBGKJte.jpg', 2, 14, '2024-06-07 10:39:40', '2024-11-04 19:58:15', 1, 0, NULL),
(17, 'TACO AUTÉNTICO MEXICANO', 'Plantilla suave de maíz, cilantro y cebolla. Proteínas a escoger: Pollo Desmenuzado o Carne Molida / +$2.00 Asada, Al Pastor, Carnita, Chorizo Mexicano, Pechuga o Pork Belly/+$4.00 Camarones', 4.00, 'images/s5JLUCjRsdA6KbOvJ3q0xlAfmx6X17GBwT6Go1vB.jpg', 3, 1, '2024-06-07 10:44:08', '2025-10-20 00:44:43', 1, 0, NULL),
(18, '\"TACO-BRONES\"', 'Tres tacos de birria con cilantro, cebolla y queso. Servidos con su consomé.', 19.00, 'images/y9xQQV052rkFUSQ3xYGb2ptzzDgusfeXuHd5KMr3.jpg', 3, 2, '2024-06-07 10:45:11', '2025-10-11 16:38:40', 1, 0, NULL),
(20, 'TACO DURO', 'Lechuga, tomate, queso y sour cream. Proteínas a escoger: Pollo Desmenuzado o Carne Molida / +$2.50 Asada / +$2.00 Pechuga, Carnita, Chorizo Mexicano / +$2.75 Al Pastor / +$4.00 Camarones', 3.50, 'images/GLsywiJMubFIustZVoaTgym1UyeuIKiDhlb5L3Ng.jpg', 3, 3, '2024-06-07 10:49:13', '2025-10-13 16:37:45', 1, 0, NULL),
(21, 'TACO SUAVE', 'Lechuga, tomate, queso y sour cream. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada / +$2.00 Pechuga, Carnita, Chorizo Mexicano / +$2.50 Mixto no Camarones / +$2.75 Al Pastor /+$4.00 Camarones', 3.75, 'images/c74jdqNTDYusQZ47WUyua7JZej9vFRg19HzwPStO.jpg', 3, 4, '2024-06-07 10:50:41', '2025-10-12 13:04:53', 1, 0, NULL),
(22, 'FISH TACOS 🌶️', 'Trozos de pescado empanado, plantilla suave de harina, cebolla lila, cilantro, jalapeño y salsa chipotle.', 6.50, 'images/1717761131.jpg', 3, 5, '2024-06-07 10:52:11', '2024-11-04 19:59:06', 1, 0, NULL),
(23, 'DOUBLE DECKER', 'Taco duro unido por una capa de queso derretido a una plantilla suave de harina. Lechuga, tomate, queso y sour cream. Servido con nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada / +$2.00 Pechuga, Carnita o Chorizo Mexicano /+$2.50 Mixto no Camarones /+$2.75 Al Pastor o de Chicharrón /+$4.00 Camarones', 4.75, 'images/Wi8Yfn6QSuysRPid39UfXWN9BYQeuhz83p3W72Ol.jpg', 3, 6, '2024-06-07 10:54:30', '2025-10-12 13:05:44', 1, 0, NULL),
(24, 'TACOS DE CHICHARRÓN', 'Chicharrón de cerdo en trozos cubiertos en salsa de aguacate y ajo, con cebolla y cilantro. Plantillas a escoger: Auténtica de Maíz o Suave de Harina.', 5.25, 'images/aYS90BUG4NTxmb2HtOeXtH0fK7oP4UGOQAOvxzbG.jpg', 3, 7, '2024-06-07 10:56:21', '2024-11-04 19:59:47', 1, 0, NULL),
(25, '\"TA-BURRÓN 12\" 🌶️', 'Burro de 12\" con lechuga, tomate, queso y sour cream. Cubierto con queso de la casa y pico de gallo. Servido con nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada / +$2.00 Pechuga o Carnita /+$4.00 Al Pastor o Mixto No Camarones /+$8.00 Mixto con Camarones.', 24.00, 'images/w8ua2L1YnthnxNsfBUYYmbqkSLcKCum6ezQbUXIH.jpg', 4, 1, '2024-06-07 11:00:17', '2025-10-12 13:06:31', 1, 0, NULL),
(26, 'MINI \"TA-BURRON\" 🌶️', 'Burro de 6\" con lechuga, tomate, queso y sour cream. Cubierto con queso de la casa y pico de gallo. Servido con nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Pechuga o Carnita /+$4.00 Al Pastor o Mixto no Camarones /+$6.00 Mixto con Camarones.', 14.00, 'images/1717761774.jpg', 4, 2, '2024-06-07 11:02:54', '2025-10-12 13:07:02', 1, 0, NULL),
(27, 'BURRITO GRATINADO', 'Burro de 6\" con lechuga, tomate, queso y sour cream. Cubierto en salsa roja, queso gratinado y pico de gallo. Servido con nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Carnita o Pechuga /+$4.00 Al Pastor o Mixto no Camarones /+$6.00 Mixto con Camarones.', 16.00, 'images/UkyR1HiFgKcZ6N5MH32ncHs2UVZODAQsye2F4is2.jpg', 4, 3, '2024-06-07 11:06:41', '2025-10-12 13:07:32', 1, 0, NULL),
(28, 'BURRITO \"EL MEXICANO\"', 'Lechuga, tomate, queso, sour cream, refrito, guacamole y arroz mexicano. Acompañado de nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Carnita o Pechuga /+$4.00 Al Pastor o Mixto No Camarones /+$6.00 Mixto con Camarones.', 18.00, 'images/tdc8yCWdOAW2tkEK0Q7SGFquNzZpjB6Z0PQsrcA6.jpg', 4, 4, '2024-06-07 11:10:08', '2025-10-13 16:40:11', 1, 0, NULL),
(29, 'BURRITO', 'Lechuga, tomate, queso y sour cream. Acompañado con nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Pechuga o Carnita /+$4.00 Al Pastor o Mixto No Camarones /+$6.00 Mixto con Camarones.', 12.00, 'images/1yMLJX1i34GQWyTEaSCRE62Yajxi3BuN7M8aM97O.jpg', 4, 5, '2024-06-07 11:12:25', '2025-10-12 13:08:50', 1, 0, NULL),
(30, 'QUESADILLA \"LA MEXICANA\"', 'Quesadilla doble de 8 pedazos, acompañada de lechuga, tomate, queso, sour cream, pico de gallo, arroz mexicano, refrito y guacamole. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Carnita o Pechuga /+$4.00Mixto no Camarones /+$6.00 Al Pastor /+$8.00 Mixto con Camarones', 23.00, 'images/JNoP59dWo2wHPPKMEExUV3yMeQkY6KEbu9qQ5eqv.jpg', 5, 1, '2024-06-07 11:21:12', '2025-10-12 13:09:17', 1, 0, NULL),
(31, 'BANDEJA BIRRIA', 'Tres quesadillas con queso mozzarella, cebolla y cilantro. Servidos con su consomé, arroz mexicano, refrito y pico de gallo.', 27.00, 'images/N8WioNEh2RFzw0NAnhKAaUX6QsmxGIGOh0UYNcEx.jpg', 5, 2, '2024-06-07 11:22:32', '2024-11-04 20:01:56', 1, 0, NULL),
(32, 'QUESABIRRIAS', 'Tres quesadillas con queso mozzarella, cebolla y cilantro. Servidos con su consomé.', 20.00, 'images/1717763021.jpg', 5, 3, '2024-06-07 11:23:41', '2025-10-11 16:38:06', 1, 0, NULL),
(33, 'QUESADILLA', 'Lechuga, tomate, queso y sour cream. Acompañada de nachos con queso. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Pechuga o Carnita /+$4.00 Al Pastor o Mixto No Camarones /+$6.00 Mixto Con Camarones.', 12.00, 'images/X1cvfBxhD8osnCai7u9YGPnMvqQ5GFztsB9kZOzb.jpg', 5, 4, '2024-06-07 11:25:42', '2025-10-12 13:09:55', 1, 0, NULL),
(34, 'QUESADILLA', 'VEGETARIANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, queso, sour cream, refrito y frijoles negros. VEGANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, refrito y frijoles negros.', 11.00, 'images/1717763310.jpg', 6, 1, '2024-06-07 11:28:30', '2024-07-17 17:42:23', 1, 0, NULL),
(35, 'BURRITO', 'VEGETARIANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, queso, sour cream, refrito y frijoles negros. VEGANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, refrito y frijoles negros.', 12.00, 'images/1717763370.jpg', 6, 2, '2024-06-07 11:29:30', '2024-07-17 17:40:06', 1, 0, NULL),
(36, 'TACO SALAD BOWL', 'VEGETARIANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, queso, sour cream, refrito y frijoles negros. VEGANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, refrito y frijoles negros.', 14.00, 'images/1717763412.jpg', 6, 3, '2024-06-07 11:30:12', '2024-07-17 17:43:03', 1, 0, NULL),
(37, 'CHIMICHANGA', 'VEGETARIANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, queso, sour cream, refrito y frijoles negros. VEGANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, refrito y frijoles negros.', 15.00, 'images/1717763434.jpg', 6, 4, '2024-06-07 11:30:34', '2024-06-07 11:30:34', 1, 0, NULL),
(38, 'TACO SUAVE', 'VEGETARIANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, queso, sour cream, refrito y frijoles negros. VEGANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, refrito y frijoles negros.', 5.50, 'images/1717763453.jpg', 6, 5, '2024-06-07 11:30:53', '2024-07-17 17:43:51', 1, 0, NULL),
(39, 'TACO DURO', 'VEGETARIANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, queso, sour cream, refrito y frijoles negros. VEGANO: Vegetales salteados (pimiento, cebolla y tomate), lechuga, tomate, cilantro, refrito y frijoles negros.', 5.50, 'images/1717763482.jpg', 6, 6, '2024-06-07 11:31:22', '2024-07-17 17:44:22', 1, 0, NULL),
(40, '\"EL MICHOACÁN\"🌶️', 'Carnes salteadas en pimiento, tomate y cebolla. Servidas sobre arroz mexicano, cubierto con queso de la casa. Proteínas a escoger: Pechuga /+$0.50 Asada /+$2.50 Asada con Pechuga /+$4.50 Asada con Camarones /+$4.00 Pechuga con Camarones /+$6.00 Camarones /+$6.50 Camarones con Asada y Pechuga.', 25.00, 'images/bA0y6LDDZw7gmDpJpIQQ5CUjR6j06U2Juy4YAuil.jpg', 7, 1, '2024-06-07 11:35:23', '2025-10-13 15:37:24', 1, 0, NULL),
(41, 'FAJITAS \"LA FRONTERA\"', 'Carnes salteadas en pimiento, tomate y cebolla. Acompañadas de arroz mexicano, refrito, lechuga, tomate y tres tortillas. Tortillas a escoger: Maíz o Harina. Proteínas a escoger:  Pechuga /+$.050 Asada /+$4.50 Asada con Pechuga /+$4.50 Asada con Camarones /+$4.00 Pechuga con Camarones /+$6.50 Asada con Camarones y Pechuga.', 25.00, 'images/CzxOK5481jZWZ8wD4qmKvloxv4RUDbmzzLv3BJ2u.jpg', 7, 2, '2024-06-07 11:38:24', '2025-10-17 19:59:09', 1, 0, NULL),
(42, 'TACO SALAD BOWL', 'Plantilla de harina frita rellena de refrito, lechuga, tomate y sour cream. Proteínas a escoger: Pollo Desmenuzado o Carne Molida, Chorizo Mexicano o Pechuga /+$2.00 Pechuga, Carnita o Mixto no Camarones /+$2.50 Asada /+$3.00 Al Pastor /+$4.00 Mixto con Camarones', 13.00, 'images/iYVi2Ag1zUVQhBj26UuyCvaZoR74ahFkwZZsm9Yz.jpg', 7, 3, '2024-06-07 11:40:37', '2025-10-13 15:41:51', 1, 0, NULL),
(43, '\"A LA RANCHERA\"', 'Chorizo mexicano servido sobre pechuga a la plancha y asada. Acompañados de arroz mexicano, refrito, lechuga, tomate, queso, sour cream y tres tortillas. Tortillas a escoger: Maíz o Harina.', 29.00, 'images/1717764150.jpg', 7, 4, '2024-06-07 11:42:30', '2025-10-13 15:42:44', 1, 0, NULL),
(44, 'CHIMICHANGA', 'Burrtio frito con chipotle y queso. Bañado en queso de la casa y pico de gallo. Acompañado de lechuga, tomate, queso, sour cream y nachos. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.50 Asada /+$2.00 Carnita o Pechuga /+$4.00 Al Pastor, Mixto no Camarones /+$5.00 Birria /+$6.00 Mixto con Camarones.', 17.00, 'images/oFVvCekLRawwNRCdiiGhxqnuPpxwWWEXiBrd1QOf.jpg', 7, 5, '2024-06-07 11:45:07', '2025-10-13 15:44:32', 1, 0, NULL),
(45, 'CHIMICHANGA A LA FAJITA', 'Burrito frito con vegetales salteados (pimiento, cebolla, y tomate) con chipotle y queso. Bañado en queso de la casa y pico de gallo. Servido con lechuga, tomate, queso, sour cream, refrito y arroz mexicano. Proteínas a escoger: Pechuga o Asada, Asada con Pechuga /+$2.50 Pechuga con Camarones o Asada con Camarones /+$4.50 Pechuga con Asada y Camarones.', 25.00, 'images/1717764488.jpg', 7, 6, '2024-06-07 11:48:08', '2025-10-13 15:51:42', 1, 0, NULL),
(46, 'CAMARONES \"A LA DIABLA\"', 'Cocidos en salsa picante y servidos con arroz mexicano, refrito, ensalada y tres tortillas. Tortillas a escoger: Harina o Maíz', 22.00, 'images/1717764600.jpg', 7, 7, '2024-06-07 11:50:00', '2024-11-04 20:04:40', 1, 0, NULL),
(47, 'PECHUGA \"LA MORRITA\"', 'Cubierta con salsa roja, queso derretido y pico de gallo. Acompañado de arroz mexicano, refrito, lechuga, tomate, queso y sour cream. Tres tortillas a escoger: Harina o Maíz.', 21.00, 'images/1717765026.jpg', 7, 8, '2024-06-07 11:57:06', '2024-11-04 20:05:08', 1, 0, NULL),
(48, 'PECHUGA EMPANADA', 'Deliciosa pechuga con empanado crujiente a la plancha al estilo mexicano. Acompañada de arroz mexicano, refrito, lechuga, tomate, queso y sour cream. Tres tortillas a escoger: Harina o Maíz', 18.00, 'images/vQv0z6pCCpmD8BYp3LzTktYrBc6IWXeRN6K4QfTb.jpg', 7, 9, '2024-06-07 12:00:17', '2024-11-04 20:05:22', 1, 0, NULL),
(49, 'POLLO EN CREMA', 'Pollo servido en deliciosa crema de la casa con un toque de chipotle. Acompañado de arroz mexicano, refrito, lechuga, tomate, queso y sour cream. Tres tortillas a escoger: Harina y Maíz', 20.00, 'images/VCHxWayCja9dwirs9GRCWNprN5OEF372tJ0UYLcY.jpg', 7, 10, '2024-06-07 12:02:42', '2024-11-04 20:05:35', 1, 0, NULL),
(50, '\"LAS CHIFLADAS\"', 'Enchiladas Verdes 🌶️: Cubiertas en salsa verde picosa, lechuga, tomate, queso y sour cream. Enchiladas Rojas: Cubierta en salsa roja y queso gratinado, lechuga, tomate, queso y sour cream. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.00 Carnita o Mixto no Camarones /+$2.50 Asada  /+$4.00 Mixto con Camarones /+$6.00 Birria', 15.00, 'images/EuULxbpRci0zDBCFUBep8De7ob9lrvKH0dZwiMRE.jpg', 7, 11, '2024-06-07 12:07:20', '2025-10-13 15:54:32', 1, 0, NULL),
(51, 'FLAUTAS \"TACOS DORADOS\"', 'Cuatro rollos de tacos fritos sobre una cama de lechuga, cubiertos con tomate, queso y sour cream. Proteínas a escoger: Pollo Desmenuzado, Carne Molida /+$2.00 Mixto no Camarones o Carnita /+$2.50 Asada /+$4.00 Mixto con Camarones /+$6.00 Birria', 15.00, 'images/56M2pGxipgjLgjLfakkWkcet3kPnwzaCfJGFjPe6.jpg', 7, 12, '2024-06-07 12:37:25', '2025-10-13 15:56:08', 1, 0, NULL),
(52, 'NACHOS \"POCO LOCO\"', 'Nachos cubiertos de carne, queso derretido, sour cream, bacon, queso rayado y pico de gallo. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.00 Carnita o Pechuga /+$2.50 Asada /+$4.00 Mixto no Camarones o Pork Belly / +$6.00  Mixto con Camarones.', 16.00, 'images/5B60mVW7pRHl4JJTcQhOTuGG3HwM2EOOf3c5g2ya.jpg', 7, 13, '2024-06-07 12:38:48', '2025-10-13 15:59:34', 1, 0, NULL),
(53, 'PAPAS \"LA CATRINA\"', 'Papas fritas cubiertas de carne, queso derretido, sour cream, bacon, queso rayado y pico de gallo. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.00 Carnita, Pechuga /+$2.50 Asada /+$4.00 Mixto no Camarones o Pork Belly /+$6.00 Mixto con Camarones.', 16.00, 'images/hLbQ42ds9o4vyARqgzEvzluScuwL7qdYtFQOkWYb.jpg', 7, 14, '2024-06-07 12:40:01', '2025-10-13 16:01:37', 1, 0, NULL),
(54, 'SOPA \"LA CHULA\"', 'Sopa de arroz mexicano al estilo de la casa. Acompañada de nachos. Proteínas a escoger: Pollo, Carnita. / Mixto (+$1.00)  o Birria (+2.00)', 12.00, 'images/xrlFcx2DnZTLHNM1t3A2wIWkxSEtSxLJKzRKVTLo.jpg', 7, 15, '2024-06-07 12:41:20', '2025-11-10 00:47:11', 1, 0, NULL),
(55, 'PLANCHITAS \"PANCHITAS\"', 'Arroz mexicano y frijoles negros servidos en una plancha de fajitas con tres tortillas. Tortillas a escoger: Harina o Maíz. Proteínas a escoger: Pechuga o Asada /+$3.50 Pechuga y Asada /+$3.00 Asada con Camarones o Pechuga con Camarones /+$5.50 Pechuga con Camarones y Asada.', 16.00, 'images/1717767875.jpg', 7, 16, '2024-06-07 12:44:35', '2025-10-13 16:03:44', 1, 0, NULL),
(56, '\"LA CHAROLA\"', '4 burros, 4 tacos duros de carne molida o pollo con lechuga, tomate, queso y sour cream. Acompañados de nachos con queso.', 34.00, 'images/1717768223.jpg', 8, 1, '2024-06-07 12:50:23', '2024-06-07 12:50:23', 1, 0, NULL),
(57, '\"EL CUATE\"', 'Un \"Ta-Burrón\", 4 tacos duros de carne o pollo con lechuga, tomate, queso y sour cream. Acompañados de nachos con queso.', 34.00, 'images/1717768324.jpg', 8, 2, '2024-06-07 12:52:04', '2024-06-07 12:52:04', 1, 0, NULL),
(58, '\"EL TACONAZO\"', '10 tacos duros de carne molida o pollo con lechuga, tomate, queso y sour cream. Acompañados por nachos con queso.', 30.00, 'images/ZaeFS3rMfEJLIzwR7fnQvOqi87qTlBUjPzwiK84g.jpg', 8, 3, '2024-06-07 12:53:10', '2025-10-13 16:46:58', 1, 0, NULL),
(59, 'PIZADILLA', 'SOLO PARA NIÑOS. Queso o Pepperoni', 6.00, 'images/BnCcqBiMbVyVqJAIrQqMycPuNZR4iLP3vTLrF2gk.jpg', 9, 1, '2024-06-07 12:54:01', '2025-04-27 21:20:16', 1, 0, NULL),
(60, 'MINI BURRITO', 'Solo para niños. Lechuga, tomate, queso rayado y sour cream. Acompañado de nachos con queso. Papas fritas + $2. Proteínas a escoger: Pollo Desmenuzado o Carne Molida/+$2.00 Pechuga o Mixto No Camarones /+$2.25 Asada.', 7.00, 'images/AEm5kAZF0bVjO2FecJYQ4k7CWN3xc36wh7qAKvbz.jpg', 9, 2, '2024-06-07 12:54:28', '2025-11-10 00:48:56', 1, 0, NULL),
(61, 'NUGGETS', 'SOLO PARA NIÑOS. Con papas o Arroz Mexicano +$2', 7.00, 'images/wGtOXf92LZJvOC4kM3p7rUGHM6BdnI5a7aVSb2pz.jpg', 9, 3, '2024-06-07 12:55:05', '2025-11-10 00:47:54', 1, 0, NULL),
(62, 'ARROZ Y TIRITAS DE PECHUGA A LA PLANCHA', 'SOLO PARA NIÑOS. Arroz y tiritas de pechuga a la plancha', 8.00, 'images/1717768546.jpg', 9, 4, '2024-06-07 12:55:46', '2025-04-27 21:21:04', 1, 0, NULL),
(63, 'MINI QUESADILLAS', 'SOLO PARA NIÑOS. Acompañada de Nachos. Cambiar por papas fritas +$2 Papas queso y bacon + $3\r\n\r\nProteínas a escoger: Solo queso, Pollo Desmenuzado o Carne Molida /+$2.00 Carnita o Pechuga /+$2.25 Asada.', 6.00, 'images/eO28ny87KyE2ghGOZj8YfWfXrxwLAIDL5UrKwSHt.jpg', 9, 5, '2024-06-07 12:56:12', '2025-11-10 00:46:22', 1, 0, NULL),
(64, 'CHURROS', 'Cuatro churros acompañados de dulce de leche, Nutella o crema.', 6.00, 'images/UVHhD12VJtgTKAM6j5otif4ra5F1zRkDg897RhSj.png', 10, 1, '2024-06-07 12:58:15', '2024-07-15 01:20:58', 1, 0, NULL),
(65, 'CHURROS \"FRIDA\"', 'Cuatro churros acompañados de dulce de leche, Nutella o crema. Servido con mantecado de vainilla.', 8.00, 'images/goOFN2edv6mtIf6gk8nAHCgzBYVbCnnKnjxEMwDK.jpg', 10, 2, '2024-06-07 13:00:29', '2024-07-15 01:22:21', 1, 0, NULL),
(66, 'VOLCÁN \"TOLUCA\"', 'Delicioso bizcocho de chocolate caliente con una explosión de chocolate derretido y mantecado de vainilla cubierto por una capa de chocolate duro.', 11.00, 'images/4IFzxEsVWIwJh4HEFc1VQ1414ACtaM2muusByfZV.jpg', 10, 3, '2024-06-07 13:02:03', '2024-07-14 15:00:04', 1, 0, NULL),
(68, 'CHEESECAKE', 'New York Cheesecake decorado con crema batida, Nutella y cherry. / Mantecado +$2.00', 8.00, 'images/tI1WzJBcICqcl7FylCWW7vMo2SAI71mqjmJuknh3.jpg', 10, 4, '2024-06-07 13:11:08', '2024-11-04 20:18:53', 1, 0, NULL),
(72, 'MANTECADO DE VAINILLA', 'Una bolita de mantecado', 3.50, 'images/1717769580.jpg', 10, 5, '2024-06-07 13:13:00', '2024-11-04 20:19:39', 1, 0, NULL),
(74, 'CORDON BLEU', 'Bolitas de pollo empanadas y fritas rellenas de queso y jamón.', 8.00, 'images/E04oISD9mGu3TbjfueOxRWkYfsTLQqdOvsGRjLNK.jpg', 2, 15, '2024-07-07 20:30:33', '2025-11-10 00:49:22', 1, 0, NULL),
(76, 'CHURRO CHEESECAKE', 'Delicioso cheesecake cubierto por una fina capa de churro decorado por nutella, dulce de leche o caramelo y crema batida. Mantecado (+2.00)', 8.00, 'images/RINPMiZ9pbm7nrNrE5lrTqXHhrX5bSpgU8XPExbO.png', 10, 6, '2024-07-07 22:01:32', '2024-07-14 15:01:13', 1, 0, NULL),
(77, 'NY CHURRO COOKIE', 'Deliciosa galleta al estilo New York hecha en casa decorada con azucar, canela, dulce de leche, nutella o caramelo. Mantecado cubierta de chocolate duro (+$2.00)', 8.00, 'images/1720393993.png', 10, 7, '2024-07-07 22:13:13', '2024-07-14 15:02:08', 1, 0, NULL),
(79, 'SURTIDO EL D.F.', 'Surtido de choriqueso, pico de gallo, refrito, salsa de la casa, guacamole y nachos.', 20.00, 'images/1720826611.jpg', 2, 16, '2024-07-12 22:23:32', '2024-07-12 22:23:32', 1, 0, NULL),
(80, 'PECHUGA A LA PLANCHA', 'Acompañada de arroz mexicano, refrito, lechuga, tomate, queso rayaso y sour cream. Tres tortillas a escoger (harina/maiz).', 18.00, 'images/1720831730.jpg', 7, 17, '2024-07-12 23:48:50', '2024-11-04 20:07:12', 1, 0, NULL),
(81, 'LA GUADALUPE', 'Deliciosa carne asada servida con arroz mexicano, refrito, pico de gallo, lechuga, tomate, queso rayado y sour cream con tres tortillas a escoger (harina/maiz).', 26.00, 'images/1720832792.jpg', 7, 18, '2024-07-13 00:06:32', '2025-10-13 16:11:13', 1, 0, NULL),
(82, 'MINI POCO LOCO', 'Nachos cubiertos de carnes, queso derretido, sour cream, bacon, queos rayado y pico de gallo. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.00 Carnita o Pechuga /+$2.50 Asada /+$4.00 Mixto no Camarones o Pork Belly /+$6.00 Mixto con Camarones.', 10.00, 'images/1720972323.jpg', 2, 17, '2024-07-14 14:52:03', '2025-10-13 16:12:27', 1, 0, NULL),
(84, 'TACOS AUTÉNTICOS DE PORK BELLY', '¡Llegaron los Tacos de Pork Belly más auténticos y deliciosos! 🌮🔥 Crujiente por fuera, jugoso por dentro y lleno de sabor, nuestro pork belly es la opción perfecta para los amantes de la buena comida.', 6.00, 'images/1760029365.jpg', 3, 8, '2025-10-09 16:02:45', '2025-10-09 16:02:45', 1, 0, NULL),
(85, '1 TACO-BRON', '1 solo taco de birria con cilantro, cebolla y queso. Servidos con su consomé.', 6.75, 'images/1760204260.jpg', 3, 9, '2025-10-11 16:37:41', '2025-10-11 16:37:41', 1, 0, NULL),
(86, '1 QUESABIRRIA', '1 quesadilla con queso mozzarella, cebolla y cilantro. Servida con su consomé.', 6.75, 'images/1760204824.png', 5, 5, '2025-10-11 16:47:04', '2025-10-11 16:47:04', 1, 0, NULL),
(88, 'MINI CATRINA', 'Papas fritas cubiertas de carne, queso derretido, sour cream, bacon, queso rayado y pico de gallo. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$2.00 Carnita o Pechuga /+$2.50 Asada /+$4.00 Mixto no Camarones.', 10.00, 'images/1760207773.png', 2, 18, '2025-10-11 17:36:13', '2025-10-11 17:36:13', 1, 0, NULL),
(89, 'POKE BOWL MEXICANO', 'Disfruta los sabores en un mismo bowl: selecciona la base entre lechuga o arroz mexicano. Cubierto con maíz, frijoles negros, sour cream, queso rayado y salsa de aguacate y ajo. Proteínas a escoger: Pollo Desmenuzado o Carne Molida /+$1.00 Pechuga o Carnita /+$1.50 Asada /+$2.00 Mixto no Camarones, Al Pastor/+$3.00 Birria, Camarones o Pork Belly.', 12.00, 'images/1760208415.jpg', 7, 19, '2025-10-11 17:46:55', '2025-10-11 17:46:55', 1, 0, NULL),
(90, 'Guacamole con Pork Belly y Pico de Gallo', 'Nuestro delicioso guacamole con pico de gallo, ahora con crujiente y jugoso Pork Belly.', 11.00, 'images/1760839646.jpg', 2, 19, '2025-10-19 01:07:26', '2025-10-19 01:07:26', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dish_food_pairing`
--

CREATE TABLE `dish_food_pairing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED NOT NULL,
  `food_pairing_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dish_recommendations`
--

CREATE TABLE `dish_recommendations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED NOT NULL,
  `recommended_dish_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dish_tax`
--

CREATE TABLE `dish_tax` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dish_wine`
--

CREATE TABLE `dish_wine` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED NOT NULL,
  `wine_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `map_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `additional_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_info`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_notifications`
--

CREATE TABLE `event_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `confirmation_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_promotions`
--

CREATE TABLE `event_promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `preview_text` varchar(255) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `body_html` text NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `status` enum('draft','sending','sent','failed') NOT NULL DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `send_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `send_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_sections`
--

CREATE TABLE `event_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `available_slots` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `price_per_person` decimal(10,2) NOT NULL DEFAULT 0.00,
  `flat_price` decimal(10,2) DEFAULT NULL,
  `layout_coordinates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`layout_coordinates`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_tickets`
--

CREATE TABLE `event_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `event_section_id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `guest_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `total_paid` decimal(10,2) NOT NULL,
  `ticket_code` varchar(255) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extras`
--

CREATE TABLE `extras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `group_required` tinyint(1) NOT NULL DEFAULT 0,
  `max_select` tinyint(3) UNSIGNED DEFAULT NULL,
  `kind` varchar(255) NOT NULL DEFAULT 'modifier',
  `price` decimal(8,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `view_scope` varchar(255) NOT NULL DEFAULT 'global',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extra_assignments`
--

CREATE TABLE `extra_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `extra_id` bigint(20) UNSIGNED NOT NULL,
  `assignable_type` varchar(255) NOT NULL,
  `assignable_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_pairings`
--

CREATE TABLE `food_pairings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `dish_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_pairing_wine`
--

CREATE TABLE `food_pairing_wine` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wine_id` bigint(20) UNSIGNED NOT NULL,
  `food_pairing_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grapes`
--

CREATE TABLE `grapes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `wine_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grape_wine`
--

CREATE TABLE `grape_wine` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wine_id` bigint(20) UNSIGNED NOT NULL,
  `grape_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_customers`
--

CREATE TABLE `loyalty_customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_visit_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_redemptions`
--

CREATE TABLE `loyalty_redemptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `loyalty_customer_id` bigint(20) UNSIGNED NOT NULL,
  `loyalty_reward_id` bigint(20) UNSIGNED NOT NULL,
  `points_used` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_rewards`
--

CREATE TABLE `loyalty_rewards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `points_required` int(10) UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_visits`
--

CREATE TABLE `loyalty_visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `server_id` bigint(20) UNSIGNED NOT NULL,
  `expected_name` varchar(255) NOT NULL,
  `expected_email` varchar(255) NOT NULL,
  `expected_phone` varchar(255) NOT NULL,
  `qr_token` varchar(255) NOT NULL,
  `status` enum('pending','confirmed','expired') NOT NULL DEFAULT 'pending',
  `points_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `customer_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customer_snapshot`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(25, '0001_01_01_000000_create_users_table', 1),
(26, '0001_01_01_000001_create_cache_table', 1),
(27, '0001_01_01_000002_create_jobs_table', 1),
(28, '2024_05_01_170425_create_categories_table', 1),
(29, '2024_05_01_170425_create_dishes_table', 1),
(30, '2024_05_02_161951_sushi', 1),
(31, '2024_05_06_232958_add_relevance_to_categories_table', 1),
(32, '2024_05_08_175314_update_image_paths', 1),
(33, '2024_06_06_192829_create_cantina_items_table', 1),
(34, '2024_06_06_195740_add_image_to_cantina_items_table', 1),
(35, '2024_06_06_203620_create_cantina_categories_table', 1),
(36, '2024_06_06_225421_add_name_to_cantina_categories_table', 1),
(37, '2024_06_06_230839_add_category_id_to_cantina_items_table', 1),
(38, '2024_06_10_145724_create_cocktail_categories_table', 2),
(39, '2024_06_10_145736_create_cocktails_table', 2),
(40, '2024_06_10_152006_create_wine_categories_table', 2),
(41, '2024_06_10_152006_create_wines_table', 2),
(42, '2024_06_10_175518_add_image_to_cocktails_table', 2),
(43, '2024_06_10_183503_create_settings_table', 2),
(44, '2024_06_10_191713_add_background_images_to_settings_table', 2),
(45, '2024_06_10_195512_add_style_settings_to_settings_table', 2),
(46, '2024_06_10_201832_add_card_opacity_to_settings_table', 2),
(47, '2024_06_10_202126_add_button_color_to_settings_table', 2),
(48, '2024_06_10_202928_add_view_settings_to_settings_table', 2),
(49, '2024_06_10_204657_add_view_settings_columns_to_settings_table', 2),
(50, '2024_06_11_154045_add_visible_to_cocktails_table', 2),
(51, '2024_06_11_154045_add_visible_to_dishes_table', 2),
(52, '2024_06_11_154045_add_visible_to_wines_table', 2),
(53, '2024_06_11_192051_add_logo_to_settings_table', 2),
(54, '2024_06_11_203829_add_socials_and_info_to_settings', 2),
(55, '2024_06_12_135939_add_category_styles_to_settings_table_new', 2),
(56, '2024_06_12_172726_add_card_background_colors_to_settings_table', 2),
(57, '2024_06_14_105034_add_button_font_size_and_fixed_bottom_font_settings_to_settings_table', 2),
(58, '2024_06_14_110831_add_font_size_and_color_to_fixed_bottom_info_in_settings_table', 2),
(59, '2024_06_16_173254_create_popups_table', 2),
(60, '2024_06_18_192540_add_repeat_days_to_popups_table', 2),
(61, '2025_11_02_132434_create_wine_types_table', 2),
(62, '2025_11_02_132537_create_grapes_table', 2),
(63, '2025_11_02_132602_create_regions_table', 2),
(64, '2025_11_02_132629_create_food_pairings_table', 2),
(65, '2025_11_02_132652_create_grape_wine_table', 2),
(66, '2025_11_02_132723_create_food_pairing_wine_table', 2),
(67, '2025_11_02_134852_add_type_and_region_to_wines_table', 2),
(68, '2025_11_02_151046_add_dish_id_to_food_pairings_table', 2),
(69, '2025_11_02_163441_remove_dish_id_from_food_pairings_table', 2),
(70, '2025_11_02_163456_create_dish_food_pairing_table', 2),
(71, '2025_11_02_170837_create_dish_wine_table', 2),
(72, '2025_11_02_171452_make_category_id_nullable_in_wines', 2),
(73, '2025_12_20_193414_create_events_table', 2),
(74, '2025_12_20_193420_create_event_sections_table', 2),
(75, '2025_12_20_193424_create_event_tickets_table', 2),
(76, '2025_12_20_210000_create_event_notifications_table', 2),
(77, '2025_12_20_220000_create_event_promotions_table', 2),
(78, '2025_12_20_230000_add_position_to_dishes_table', 2),
(79, '2025_12_20_230500_add_position_to_cocktails_table', 2),
(80, '2025_12_20_230600_add_position_to_wines_table', 2),
(81, '2025_12_20_232000_add_order_to_cocktail_and_wine_categories', 2),
(82, '2025_12_22_150204_add_missing_legacy_settings_columns', 2),
(83, '2025_12_22_150708_add_cover_button_labels_to_settings_table', 2),
(84, '2025_12_23_010000_add_cover_fields_to_category_tables', 2),
(85, '2025_12_23_011000_add_featured_flag_to_menu_items', 2),
(86, '2025_12_23_020000_add_hero_images_to_settings_table', 2),
(87, '2025_12_23_023000_add_cta_images_to_settings_table', 2),
(88, '2025_12_23_024000_add_card_bg_color_cover_to_settings_table', 2),
(89, '2025_12_24_004447_add_featured_card_colors_to_settings_table', 3),
(90, '2025_12_24_010000_add_role_and_invitation_to_users_table', 3),
(91, '2025_12_24_011000_create_loyalty_tables', 3),
(92, '2025_12_24_012000_add_loyalty_fields_to_settings_table', 3),
(93, '2025_12_24_013000_add_active_to_users_table', 3),
(94, '2025_12_24_020000_add_missing_tab_labels_to_settings_table', 3),
(95, '2025_12_24_030000_add_tab_visibility_flags_to_settings_table', 3),
(96, '2025_12_24_040000_add_secondary_cover_text_color_to_settings_table', 3),
(97, '2025_12_24_041000_add_cover_text_fields_to_settings_table', 3),
(98, '2025_12_24_042000_add_cover_cta_colors_to_settings_table', 3),
(99, '2025_12_24_043000_add_featured_colors_to_settings_table', 3),
(100, '2025_12_24_050500_add_vip_cta_colors_to_settings_table', 3),
(101, '2025_12_24_051500_add_cover_cta_visibility_flags', 3),
(102, '2025_12_24_052500_create_cocktail_dish_table', 3),
(103, '2025_12_24_052900_alter_cover_highlight_columns_to_text', 3),
(104, '2025_12_24_053100_alter_cover_cta_color_columns_to_text', 3),
(105, '2025_12_26_000000_create_dish_recommendations_table', 3),
(106, '2025_12_26_000001_create_extras_table', 3),
(107, '2025_12_26_000002_create_extra_assignments_table', 3),
(108, '2026_01_05_000000_add_api_token_to_users_table', 3),
(109, '2026_01_13_125441_create_table_sessions_table', 3),
(110, '2026_01_13_125442_create_orders_table', 3),
(111, '2026_01_13_125443_create_order_items_table', 3),
(112, '2026_01_13_125444_create_order_item_extras_table', 3),
(113, '2026_01_13_145625_patch_missing_cocktail_wine_categories', 3),
(114, '2026_01_14_000001_add_order_mode_to_table_sessions_table', 3),
(115, '2026_01_14_120000_create_printers_table', 3),
(116, '2026_01_14_120010_create_print_templates_table', 3),
(117, '2026_01_14_120020_create_printer_routes_table', 3),
(118, '2026_01_14_120030_create_print_jobs_table', 3),
(119, '2026_01_14_130000_add_loyalty_visit_to_table_sessions_table', 3),
(120, '2026_01_14_140000_add_group_fields_to_extras_table', 3),
(121, '2026_01_14_140010_add_group_fields_to_order_item_extras_table', 3),
(122, '2026_01_15_000000_create_order_batches_table', 3),
(123, '2026_01_15_000010_add_order_batch_id_to_order_items_table', 3),
(124, '2026_01_15_000020_add_open_order_to_table_sessions_table', 3),
(125, '2026_01_15_000030_add_group_rules_to_extras_table', 3),
(126, '2026_01_15_000040_add_service_channel_to_table_sessions_table', 3),
(127, '2026_01_15_000050_add_payment_fields_to_orders_table', 3),
(128, '2026_01_15_000060_create_category_subcategories_table', 3),
(129, '2026_01_15_000070_add_subcategory_id_to_dishes_table', 3),
(130, '2026_01_15_000080_create_cocktail_and_wine_subcategories_tables', 3),
(131, '2026_01_15_000090_add_subcategory_id_to_cocktails_and_wines_table', 3),
(132, '2026_01_15_000100_add_subcategory_styles_to_settings_table', 3),
(133, '2026_01_15_000110_update_order_batches_source_enum', 3),
(134, '2026_01_15_000140_add_void_fields_to_order_items_table', 3),
(135, '2026_01_15_000150_create_payments_table', 4),
(136, '2026_01_15_000160_create_refunds_table', 4),
(137, '2026_01_15_000170_add_stripe_fields_to_orders_table', 4),
(138, '2026_01_19_000200_create_prep_areas_and_labels_tables', 4),
(139, '2026_01_19_000210_create_order_item_prep_labels_table', 4),
(140, '2026_01_21_000300_add_tip_total_to_orders_table', 4),
(141, '2026_01_21_000400_create_taxes_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `table_session_id` bigint(20) UNSIGNED NOT NULL,
  `server_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_total` decimal(10,2) DEFAULT NULL,
  `tip_total` decimal(10,2) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_charge_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_batches`
--

CREATE TABLE `order_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `source` enum('server','table','pos') NOT NULL DEFAULT 'server',
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `itemable_type` varchar(255) NOT NULL,
  `itemable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `category_scope` varchar(20) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `category_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `voided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `void_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_item_extras`
--

CREATE TABLE `order_item_extras` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `extra_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `kind` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_item_prep_labels`
--

CREATE TABLE `order_item_prep_labels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `prep_label_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','preparing','ready','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `prepared_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `provider` varchar(255) NOT NULL DEFAULT 'stripe',
  `method` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_charge_id` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `popups`
--

CREATE TABLE `popups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `repeat_days` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prep_areas`
--

CREATE TABLE `prep_areas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prep_labelables`
--

CREATE TABLE `prep_labelables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prep_label_id` bigint(20) UNSIGNED NOT NULL,
  `labelable_type` varchar(255) NOT NULL,
  `labelable_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prep_labels`
--

CREATE TABLE `prep_labels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `prep_area_id` bigint(20) UNSIGNED NOT NULL,
  `printer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE `printers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `connection` varchar(255) NOT NULL DEFAULT 'cloudprnt',
  `device_id` varchar(255) DEFAULT NULL,
  `token` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printer_routes`
--

CREATE TABLE `printer_routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `printer_id` bigint(20) UNSIGNED NOT NULL,
  `print_template_id` bigint(20) UNSIGNED NOT NULL,
  `category_scope` varchar(255) NOT NULL DEFAULT 'all',
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_jobs`
--

CREATE TABLE `print_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `printer_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `print_template_id` bigint(20) UNSIGNED NOT NULL,
  `content_type` varchar(255) NOT NULL DEFAULT 'text/plain',
  `payload` longtext NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `printed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `print_templates`
--

CREATE TABLE `print_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'ticket',
  `body` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `stripe_refund_id` varchar(255) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0i17C5K8IOcIpk3WgUqH4zJfgo6AGUxIurm4J4TZ', NULL, '40.77.167.152', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTFRCSGY5MlRFQjZ5ekZLQmZmOEt2RDlpRzJ1N0gxQVN1eVVoWU5pcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769019514),
('0L9M55IG6YMbtgECWw6bpfbKB8NxrrAyDN66Bfbj', NULL, '2a03:2880:10ff:49::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid0lmcDJMMWxNRFdVNVFQbE5LNW4wbUtFZEl2S09ydTI5Skp3OFJYVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769102324),
('1ak8KBOQk2Me2HiIa6GNaBaEOdoY3pp2hPL8KpYM', NULL, '72.50.5.42', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN291YkdPcFJHc2c4d25TV0kybnhkZ0dtWTlWbVFjcDRqM0E4SlU2ZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769095849),
('1spyegZZngpWUsVpdqGNGD5dtv0qk4dSZZ8xSXOI', NULL, '2a03:2880:21ff:41::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFpIYWFldDNmcHFoMmluR0lnUFFGZzBUQkFZYjBkbFlvZFBHMklVdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769097136),
('4kcHhvik9GkZASmAChjvz9GPekQ2kraEJQLiUKob', NULL, '2a03:2880:13ff:3::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibG9tREFyVU1RVU9kYkxXSmhyREd1MllmMldzd0RJNXpFNEdOWnZNSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769099855),
('4Tz5lCJJXxbDZhYEfn5bwxdJdkLSEQJU41ke5Zht', NULL, '2a03:2880:18ff:53::', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWdOeTg0ODhjNjNRUDBnZUc0cTBRNGlUbWtibUNRZlFJVkZWUTJLbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTc0OiJodHRwczovL3d3dy50YWticm9ucHIuY29tLz9mYmNsaWQ9SXdaWGgwYmdOaFpXMENNVEVBYzNKMFl3WmhjSEJmYVdRTU1qVTJNamd4TURRd05UVTRBQUVlalphaTdEMlNPa2Q2bDhRMzZZRGR3d1BtbXl6RVNLTkc3T2NvMTU0VENfVHE4STkydFE1bVk0SGRQdUVfYWVtX2hPMW9OQ05kNGVOWkJpOFVjZVBYZFEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769042041),
('5c9MI7yyGHNaWcNv2DYw9sGbduCMd1uVWYERleVi', NULL, '2607:fb90:7612:fdaa:5c22:759e:6b03:bb6', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRjdaVkxEZG04TklCcW14QmFhaThFVFpTTHBrSE4wN1hGcWM5bk1qUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769016313),
('5pR3MryVcoYCiSh90Bk0KsCwuJgCeNNTDYFSOupI', NULL, '2606:6a40:6e:30cd:5c59:88a8:9502:8768', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmljNzNCZkFrN0g4QU5lM25NQ1VLVVBMY2Nnd3NEV25NeWlBVlB6TCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769037350),
('68kiSX42QI1T2R4BL64gldRpJVtwPuJNvIIRTjNt', NULL, '2a03:2880:32ff:7::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjREYUQ3SW1XVldVcDYwM0hJQlhoTVFNR3F3NUk3ME1BYTZmNEdjeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769037993),
('6Gc8xoBNmMRuwtFIn2m79MFF6XkhpCTLin4AGByB', NULL, '136.145.70.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_1_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU3ZiaTV0SkJvZlhNWkFia0JETVQ1MmZhNGd2Q2FwOXlQZkV6Z1piZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769091785),
('6jzJQ70Etj89Z066nm4jjgAv4vp5sF3v3J8V0oKu', NULL, '2a03:2880:11ff:42::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRG4wTzE2Y2FUeFV4bnFwWEFqZjVjVjhQNVRGdjQ5cVlHUFFFWVBiSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769022750),
('78FmftqZ3tHaw7GoK337z4ag7UQ7GRsivfzJY35X', NULL, '2607:fb91:189b:c981:d489:64e3:28da:8fbb', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM0N1a2tnSVlQU05DTXRCQTVvMlpvbWNpc25XZnlVaTF4bG10YjZoRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769088462),
('7nvvodDK8A7wJjRj4j3UAohxMRRsQpCzMAtFQKQ2', NULL, '142.44.233.59', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMnBPRmZtbVBJZ0twWWZqODJmNlZuOUppVEM3cGRNbXJwT21mcWhlUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769115018),
('8jMgoRLFPOoKJFfWOuym1LYWnOtzE1fvz3zIslPJ', NULL, '2a03:2880:25ff:71::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRm5YUkNKTjJJMGZWdmJEUkNQUlY1ck5taE5FUkl6Q1M2ZWpKWTVUWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769097978),
('9sVQspU0zlKtRgo7zS9PvxPF8WcsbtAk4WAQ98FS', NULL, '2607:fb90:7612:dcb0:f425:19f9:3cb7:e1c3', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVUtmQ0JzUWs1ZmtmdEhZUTg5dWxtbW10Tk5xYVNlZnR6Q1FuVEJBSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769118086),
('9vESaUCF0Jj7VtnsJeONeUJH36XPEv7Ac6exmw4y', NULL, '2a03:2880:12ff:4::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN011OWpVZExNOWRlVHZxZUROWUJuYUlwTm9wREZ4aFh6QmpuOFlvVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTc0OiJodHRwczovL3d3dy50YWticm9ucHIuY29tLz9mYmNsaWQ9SXdaWGgwYmdOaFpXMENNVEVBYzNKMFl3WmhjSEJmYVdRTU1qVTJNamd4TURRd05UVTRBQUVlalphaTdEMlNPa2Q2bDhRMzZZRGR3d1BtbXl6RVNLTkc3T2NvMTU0VENfVHE4STkydFE1bVk0SGRQdUVfYWVtX2hPMW9OQ05kNGVOWkJpOFVjZVBYZFEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769042076),
('9xdoXgccSMstYvnKenoARzHFcz4SBRE49E3kBIXs', NULL, '2a02:26f7:e7cc:4b80:0:d000:0:6', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiblo3WjBsZktDcW1zaWJiNEFFeXdoVGpmODRQVE5RQlV1WXRmSlNMMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769099196),
('alIOyhhpUbYwbbYkmyYOBrap6o1mEer3gYcFQn0z', NULL, '2606:6a40:f:da70:9961:724c:bc9c:f206', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS3A1TUhoMENadHZZWjg0dEtKaE1zMm9LaVVVa1NTWHB0NmUwTklrcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769027854),
('arHhbiU59vL6vYPXe2LsgjHvvHJ8ZfsW69uTShMS', NULL, '2a03:2880:30ff:70::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFNpZ2hxeHJQVkJSN3RmUElIZWVOaEpLS1lvOVdKUW9Memd1WWdVdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769100226),
('ATPs9nYJMcTNmdGYa2tRKHvm90sQ6Ys1Ixkx3pOw', NULL, '70.45.133.231', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTnlFTFM0UEc4bENQbDFadnA2bmc1Wko2SVdQMDA3SkthNFh2MmdFeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769094373),
('b1tf5wCPbnF3MQaEDs5BGqFWNy77rkujXgBb7rP4', NULL, '2607:fb90:762f:be16:40d8:4241:836c:deaf', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibkdHa1lJdnI2UHZheDd3M0VKZlhxU25HTEFrYjdielJoVFp5NThYRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769024922),
('BHddM0YLx5cjYCPXXFx1VxCtlVx71MMlCtiVoUBo', NULL, '2607:fb90:7610:cd8c:7973:3f48:ccc5:bd67', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXhvbklWNVNyZVhqa3ZrMTh1eTROelRRYzd1c0xjNDNlNE5QZHl4SyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769102891),
('CGOINnfMSP5y22VnBPieWlobxGYFedKurWsz46wM', NULL, '72.50.16.112', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_7_12 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVWQzV0h3ZmdWaEduQXI1OXc4T1ZlUlNLQm1tQ25VbGhlS1RmenREUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769017162),
('DTXjrTkOWF9LX3nniq0FZuPbubqsFH7vegoLf8gc', NULL, '72.50.16.112', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_7_12 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibHJUSWQxYk02MmF2OVRJWFRmZ1VzOTJ1R0FablQwekFPQzd5OXNQRiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769017154),
('EdEguiyD3MiEaFu1NKxUNMwx6PZTLn9Os9T2zcW3', NULL, '174.208.230.19', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjA4NU1vZ1dYaG5ORWJlcTV5OGhxY2FqQ1RVd3A3TXFXOTRUMHBvNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769024921),
('EKGo1v8algQAiUfOs7kSh8Mr5Z8NE7gsyKzntfiB', NULL, '2a03:2880:30ff:3::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibjVtb0lmNDk0bUM4cGxBSkUwNFk5bEhRYmpsZFBnNWg3RlVXTmpHdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769096955),
('elL5dl859wlTIM3uS0p1TajlSfpbY4VvdnYvwj63', NULL, '66.50.50.73', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23B85 Instagram 406.1.0.48.76 (iPhone16,2; iOS 26_1; es_LA; es; scale=3.00; 1290x2796; IABMV/1; 823565292) Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ0NjVXVCVklMMTB6eHkxOWhHNzhseUU4b1BYNU1DTkdiOU03QUxXUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769038606),
('eRJCHDyPv3mBszl4OQ4zl5CIuM5o3Ymhr9U2Kbvl', NULL, '2a03:2880:11ff:51::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ1dRY0FNTTgzOUNpVFVPV2VWSTdJekNZMHpDUUt2cEhwalNZMHF5SyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769091843),
('F3auUBxWkFAE46lWqUYXDPZ4JQ9W0ZJDPwL7VX3Y', NULL, '2a03:2880:11ff:44::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibmRJY3FwdXVyc2RMbTFZdXpndTllNmRIR2Z1dE53WlUwSHNoNTR6RSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769102278),
('F7it11AqyefRikljXwmAsJRGQbmmfLhtEeqUD0rg', NULL, '72.50.16.89', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23B85 Safari/604.1 [FBAN/FBIOS;FBAV/544.0.0.32.278;FBBV/860466667;FBDV/iPhone18,2;FBMD/iPhone;FBSN/iOS;FBSV/26.1;FBSS/3;FBID/phone;FBLC/en_US;FBOP/5;FBRV/866170147;IABMV/1]', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHczWlFOTFdLUEZaTlphcGFrdXhSOXpLN3VOdkJLcGRaVXN6YTYwRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769027449),
('fF8TCLpW2sV7X7EM2NKXxbdDFgZwmnifBQpmKmz5', NULL, '2607:fb90:7620:c27b:dc3a:5b29:4755:5e7d', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU1JLT1JVQ0JUTDU1RXFaT0FHWWJUTW1NblJ5SlJBaGJFSUNJcEJJWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769020325),
('FICrt9ANrrKXr73sNidtT0tBKQlOKDJDZeGmsWLg', NULL, '79.127.148.172', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidFVLS2IxUVFaS1VYRWFySWJTc2xuTkRxNUVCRWV4Y1U0a1NFZjVrSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769037475),
('ftk8bdrGUe9SM6TX8h3Wq89rHKAcOF14Ee9TU9h7', NULL, '2606:6a40:6b:bfad:4856:6cc3:f177:8393', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRXJxNExJUVRLSmxSWGRRbkdhYXFXU2k5T1JuRnQ4TFJIbFRPY055TCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769037544),
('fZd2hGo4TG8NendxwrAdYSFI7SgRG58TXLqI6jHe', NULL, '147.92.80.84', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23B85 [FBAN/FBIOS;FBAV/543.0.0.47.72;FBBV/845843444;FBDV/iPhone16,2;FBMD/iPhone;FBSN/iOS;FBSV/26.1;FBSS/3;FBID/phone;FBLC/en_US;FBOP/5;FBRV/861577609;IABMV/1]', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibFZOSVpWZnp6SFJyR1ZGbzd3UTdRMzlFS1pGTnN5Mnl5S2MwcHl4MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769027672),
('fzLLWLnZ2ZP5QifUiTs9cjLGS9rNPwdMpr8ukwES', NULL, '172.111.53.224', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQjUzTXNadExXRDN6ZG5iYmxPazluS25NRWtHMElKMGpJb1RxZzZ6QyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769030769),
('GWYIgMsYQdWrpobMUgFy2YgRzyuleC48mCWSXFSy', NULL, '2a03:2880:22ff:5e::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid29YY2JMb3VqUjZYRmlCYVNXVkh4R2l5a2tvcDA1cmI0SVdLMFRBNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769096936),
('HJUfaD4I9vbQlbsJz9tSvTpYJ8mtBzo3fHGKNBUs', NULL, '66.249.83.40', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS2xLQ2ZueWdVUmJRVFI3N0FXUk5ZNTVGbnl3VFpoTDhGVVBZSHRhMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769118066),
('hKq0Pc408Xx2L9NhmqDPfBBfTicqZULbZvCHw4Wq', NULL, '2a03:2880:7ff:12::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicktwNDY4MnpCdFlBSmtFcktRajNNUlREU3FnSlRlaHhaZVdGaGFhbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769099407),
('hksLdlTwWVbTIEqmAYpq8SOsmK8o7MSYpdNIwMwR', NULL, '2a03:2880:3ff:74::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSnNRNzQyWVJWbExUNU9CejhaOWJEY21MbkJ4WHRxQVI4ZDByN1Y0RiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769022754),
('HlD5V4srmHgFJS6M2aE1nEnBvZwIHrxoLUM8fvrO', NULL, '2a03:2880:22ff:5e::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHdiU01SbG9zUXNNRmgyOXdzS2NVVE1GZ1lkZVJLT1IyNnhzZTBhaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769096950),
('hWHUSg6lX8kfOlMrmrkoVwzzG4OW8KlQUCE2XEL9', NULL, '2a03:2880:ff:5a::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU3h1cDZPTXJmNGZkZW1jdDFoV0U2OGNMQVQ0NzIxblpzdkxQQURMWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769102620),
('iQEdF0octSbbl5Av13KKeF7ctXnqvGJOssLFL8GY', NULL, '2607:fb90:7624:eff8:197b:d0c0:11ee:e13f', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia3ptS3dSVHhIT3Q5WEN6M2xNbndUczBWUHZIak9KY3NvemZocHBWQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769027888),
('JnBqs5noe28darh06bUknPwpWAQThvtR9WNPAJA1', NULL, '51.222.168.41', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieXdQbWtlb3ZtNkdYakZxdzNzbWNMd2NMcGRNVURNM085SDBheTN5RiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769027742),
('JwIKPbw379RLHqPKKs1WW6nBAnDy7nlDLGrTnuss', NULL, '2a03:2880:30ff:5::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidGZUZEF4cnVBSzdZeUhFNWNkdzJTRGR6dlhqTmhLeU41cjBCeGx0ViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769022938),
('Kzi41saKvsq03APhklVA6r9gCIDL4oUDXFLfXtjD', NULL, '2a03:2880:15ff:72::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXVHOUpEMUNwdWxjaURwSlpyVDFiMXpMbkIwNWpTVUtvaDZSaVVnbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTc0OiJodHRwczovL3d3dy50YWticm9ucHIuY29tLz9mYmNsaWQ9SXdaWGgwYmdOaFpXMENNVEVBYzNKMFl3WmhjSEJmYVdRTU1qVTJNamd4TURRd05UVTRBQUVlNUJLdUkwbmFSZFhrMmtQRkh4LTVCbldyRzBlWDRMYWFuLXNldXR1ajBuRTlZdmN1TUtYYnBJbmwtM0FfYWVtX2VUa1Vyc25IUXdWczFvNG5BZDNEOGciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769099614),
('L5OL62srnmpRMlcOcKMFZalU08FZddWOUieqG0ZG', NULL, '2a03:2880:3ff:7::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia0p6a1hPVTZnT29LM1B6NE5xUWxLSGR3Y284c25zVkxia2FuUktCSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769103306),
('LV2h5KUSfCTOErrhkWfK5D5MQoaaELgDOUDUPHuD', NULL, '2a03:2880:21ff:9::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWlzUFRnYTRVaFh6T0VuMXd6ODE4aXhHSFNiQWtDNmE4V1lGSWNxYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769109238),
('lyxUGQwTM3FyBWW0fRePXiUfIpA3hyQHWUSrTUUG', NULL, '165.140.160.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibm5vdFVxWjY0TXlVdzVPdmhDUDAwU3Qyb09LTzRFOTU0OGFzSURiSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769032058),
('Ma9gdclasSZg1GqikVFmj3VcU8KHueMF9caLiPdD', NULL, '2400:5280:403:6::22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSkUzVXZSV052THhNNHJRYVQwYkhhR3FvTVJESzVFNG9MZmNNOXc0WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769069710),
('oGalIltsiR6dfhEfIgAQMnuapTnuMbU4B8NyKXML', NULL, '17.22.245.150', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15 (Applebot/0.1; +http://www.apple.com/go/applebot)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidVFLcm8xRk1mUU5HcEw4Z0lMUzhuTFJQOVljQXcyTW96OU9kTmZ4dyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769073802),
('OqIlEHrgETUNauxcfYub4g3LU2MP9tdqfxu6CCVh', NULL, '24.138.247.8', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ1dkSDF5WVNWZUx4Z2djWlBoeXUzWTRUMmhoWkdnQ1FCQmpraVkyQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769091827),
('pJKlej93It4G0VrRHiiY5Ywdk9swWfqWctJIi7dx', NULL, '2607:fb90:7612:fb7a:649f:d03f:9c5:71da', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZEN4VU9iNEwwN1ZOOHRaMmthS0xTSmpDdkdRUG5CUmltZTVyc0RvdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769016541),
('Q5xICojKwtTd2oql8o7Y9vMoJivA62koWHRoZJUz', NULL, '2607:fb90:cd91:a411:43b:eca2:2b04:243b', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY05FaGFqTW9zeGdTWHhHUWVzNGZpVEd2cE1nRGdxUVdsQVYyWTRmMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769085195),
('Q7h7Vd3Gj43FHecQG0kbiqDB4grOCc6JkyzFlHRP', NULL, '162.221.181.244', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUtsMUp1eHdBNEtsUFhXWm9Pc1JQUnR5VkZLUjg3UjNyQU1jNExqTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vY29ja3RhaWxzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769118083),
('quz3UajOPPdOXMBZhjgxgLr7INAUrw8T3r0sXBcJ', NULL, '66.249.83.41', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibGtjbWNtajFYaHE2c1ZaazZKZnlDYlVKWTBEWlRHN1h5d0JMUkpScCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769118066),
('RdljYO9Ul2ByQ7mtTN7TbvQAyqpT1vGylYRju3PT', NULL, '2607:fb90:cd09:cba1:8413:fbb6:b81a:a394', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaHM2TG5PVDJNaHVXaTN1cm5Na005eWNPaWY4U3RqcTdSMnVIUVdLTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769035777),
('RuISTwZ5IgZziSgldST24Vu4c1Sij1NSJSdwA1KN', NULL, '2001:4860:7:110e::fd', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNFh6bkQxUzVweTJBZ1hTbnkzek5lRWhPenQ5cGtqS0dIRDhJTGNHSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769088103),
('SNGX4y34aF6ua8ZVIVmRqbEiYMJ7t4TPimumVQxw', NULL, '2a03:2880:10ff:5c::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWjVMOXRueG16ajhxYzNtM2kyOEpSTmVXWHBmbm15cE5jUzdzdzdPVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769023364),
('SUE2PhFubrMVTP5GZ2kaytKElftwlG5h294fA5nR', NULL, '2a03:2880:18ff:40::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidUlkQW00dmt0YmZ3ZTA3Uk9GNzM2eWhaNGVPOGRrM3FhUE0zNG5adCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769077760),
('tCDP1nNKNtU4d9iNvafFi4hyibTFpZw36DeLtqeJ', NULL, '2001:4860:7:70a::e7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTnVrZHZHdGVJTXhjcURRaXV0c0p3cHNhWmxxaWxpekpsb2F1MlpNWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769098652),
('ThPpe0dLQbXdd02wkMwb6azQGBtxN1Bbb137sj1h', NULL, '2a03:2880:16ff:51::', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23C55 [FBAN/FBIOS;FBAV/544.0.0.32.278;FBBV/860466667;FBDV/iPhone14,5;FBMD/iPhone;FBSN/iOS;FBSV/26.2;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5;FBRV/866482018]', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid2VrS3pWaXNydjlydlNscG4zd1U1bTlSWkljYXZGYTV5anpWdEpIbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTc0OiJodHRwczovL3d3dy50YWticm9ucHIuY29tLz9mYmNsaWQ9SXdaWGgwYmdOaFpXMENNVEVBYzNKMFl3WmhjSEJmYVdRTU1qVTJNamd4TURRd05UVTRBQUVlNUJLdUkwbmFSZFhrMmtQRkh4LTVCbldyRzBlWDRMYWFuLXNldXR1ajBuRTlZdmN1TUtYYnBJbmwtM0FfYWVtX2VUa1Vyc25IUXdWczFvNG5BZDNEOGciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769099578),
('Tq8lk9cuR5MgFnE7P8CMZQmmhiSJ4WyBD9MiAuhA', NULL, '173.228.198.154', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUVaQVFyVGVzSEJERG0zYXc3MHZrZlI2WUtmMGE2emhnQkxtWE1pNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769025004),
('UgTLDItJbOIuHZNjxtUURuiG0su7RJt5oTSkCf2O', NULL, '2a03:2880:22ff:46::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUFJ3Q2Q2Y0tnWDJ2VVpQbnljSG92ZFBBSUlJS25vS2JQM1h0cGdqMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769096955),
('uLkCSxje3zj8chdonTt7r0aJ3QM9ypXWv48NxfBa', NULL, '2607:fb90:cd1f:1ac7:8d8f:d84a:7354:991d', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23A341 Safari/604.1 [FBAN/FBIOS;FBAV/543.0.0.47.72;FBBV/845843444;FBDV/iPhone17,4;FBMD/iPhone;FBSN/iOS;FBSV/26.0;FBSS/3;FBID/phone;FBLC/es_LA;FBOP/5;FBRV/861577609;IABMV/1]', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNTJ4dWpqdU5Kb0RQUk1tQnFGbzhtWDE5NEg3ZW1jMlZxMmNvaG5ZYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769099451),
('vSXHq6RANfrnsUYgSKikS8WU98GUncTISfPxgKnV', NULL, '2a03:2880:25ff:7c::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid2MxSGY4bU54dUNBVTlYelZpSkpCbkxranNqcmRYcWt3aWFDSXFSWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769109377),
('VX1WNSoGO5m0Rox4BPOLL1hUsOnHAZmX9ZLy4E4u', NULL, '173.228.198.240', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNllxUllETFhFQ2Q0cTRqUUdncG5PQTRZVEhVeXNEUGFoeXZGdUJ0eSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769117542),
('w5A45Gi7tseAwuc3tnO5TfrYuKl76t6SDuX0etZR', NULL, '2607:fb90:cd14:9960:357d:c701:f50:e496', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVFR6Y1YxSmtzeld1cjFQYndwazZDNXVHajk2dlZGSVBYY05aM0tuUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769079835),
('xIB67vMPHy3pcSpu6ZuTOpeEUD0fqu5o0xGCtDQ6', NULL, '24.139.137.163', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNTRZbzg5V044VnZxZFNDcURBZFJrNUtJZmFvVjhyTXF2OVRpVnN2MyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769024926),
('YjlXT8WUXk3JUEhkvMoFZoCvBMiXxYzsWdnUPQcZ', NULL, '2a02:26f7:e7cc:4b80:0:d000:0:6', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFJjSmExdHZrbDJsVGlnRnJzSWxraldrZHBobHQ1U2lQZTh6WEtKVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769099200),
('YQxh9tzoyurQINdpemMNOTgNObTwEBZ2s8YDkHQu', NULL, '173.228.198.120', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibkZvMExtQ2lIeGhFMTkyVzVTa2Y1NGxjZ1kxZ05GS0hXdUJvbEJlUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769034448),
('z6G276fI1nZmxDx5NoYU1IivYtIHQLWqiYYRNsL5', NULL, '2a03:2880:24ff:2::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia0dWQ2FFZXNNUHhKNXNTYVZJdGpDT0dKbWpvb1p4UnBlcWhMOVpRaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769041976),
('z7b8QOZqVxRzBS0c4RFXQA33m6zxSZDuCeVkbhzT', NULL, '2a03:2880:12ff:41::', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjVUTWl3dWo4SGJMemZmTTVLOWI4eVFWMGN4eG85RGN1QndhWkU1NyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1769097042),
('zIy0alP2joOOezcavyXCtIz5Tn5VkXf7OkxwRE01', NULL, '2606:5f00:9440:f934:a4e7:d880:a88:ffc2', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoickFGalZ1dUNseGkwWnkxcm5ZdkU2dE8zZndFclJCSDNSenk5MU82bCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769094176),
('ZJyIMa2X0t6nT02u6bARuX25VYeyKb7PugXLppoY', NULL, '66.249.83.40', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36 (compatible; Google-Read-Aloud; +https://support.google.com/webmasters/answer/1061943)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid3dLQnNPZ05Ka1NvYXV3ZThUSkNxUkFEM0lOWHYxMzc5Yjd1aGg3TSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769118067),
('zqZn4Z2DpIEAYhL8Z58kmcxv3hvP4KDJn6Y24CKz', NULL, '2607:fb90:761f:3de4:9467:2ae1:e281:7765', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/23C55 Instagram 412.0.0.17.89 (iPhone18,1; iOS 26_2; en_US; en; scale=3.00; 1206x2622; IABMV/1; 859471633) Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUJmWkQ2YmlnRWRNNVM5dENoN0QzV0JkQzVSNmtZMW00Qjh2TTZzcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vd3d3LnRha2Jyb25wci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1769047529),
('zugEM1fluKjBjbt9ChYilWPpErDsVJjXInFmXD7O', NULL, '2607:fb90:7624:eff8:197b:d0c0:11ee:e13f', 'WhatsApp/2.23.20.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSzNiaXJnWW5iNUtiUkVrRVdBdHgwdmE1Z1JsNDJiYUVVY0xRZHl0TiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9tZW51Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1769030506),
('zYi5E9hOr4cLjCye7zbisolDCBYgKWOFwQ5kNiqE', 2, '24.50.235.193', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMVFTUEZrZVptU3A3aE9USFMybkZFaWMwc2ZKM1ozV2JBZmtXQTRqTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vdGFrYnJvbnByLmNvbS9jb2NrdGFpbHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1769117690);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `background_image_cover` varchar(255) DEFAULT NULL,
  `background_image_menu` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `background_image_cocktails` varchar(255) DEFAULT NULL,
  `background_image_wines` varchar(255) DEFAULT NULL,
  `font_family` varchar(255) DEFAULT NULL,
  `text_color` varchar(255) DEFAULT NULL,
  `opacity` decimal(3,2) DEFAULT NULL,
  `card_opacity` decimal(3,2) DEFAULT NULL,
  `button_color` varchar(255) DEFAULT NULL,
  `text_color_cover` varchar(255) DEFAULT NULL,
  `text_color_cover_secondary` varchar(255) DEFAULT NULL,
  `cover_hero_kicker` text DEFAULT NULL,
  `cover_hero_title` text DEFAULT NULL,
  `cover_hero_paragraph` text DEFAULT NULL,
  `cover_location_text` text DEFAULT NULL,
  `cover_highlight_3_label` text DEFAULT NULL,
  `cover_highlight_3_title` text DEFAULT NULL,
  `cover_highlight_3_description` text DEFAULT NULL,
  `cover_cta_reservations_bg_color` text DEFAULT NULL,
  `cover_cta_reservations_text_color` text DEFAULT NULL,
  `cover_cta_events_bg_color` text DEFAULT NULL,
  `cover_cta_events_text_color` text DEFAULT NULL,
  `cover_cta_cocktails_bg_color` text DEFAULT NULL,
  `cover_cta_cocktails_text_color` text DEFAULT NULL,
  `cover_cta_cafe_bg_color` text DEFAULT NULL,
  `cover_cta_cafe_text_color` text DEFAULT NULL,
  `cover_cta_menu_bg_color` text DEFAULT NULL,
  `cover_cta_menu_text_color` text DEFAULT NULL,
  `cover_highlight_2_label` text DEFAULT NULL,
  `cover_highlight_2_title` text DEFAULT NULL,
  `cover_highlight_2_description` text DEFAULT NULL,
  `cover_highlight_1_label` text DEFAULT NULL,
  `cover_highlight_1_title` text DEFAULT NULL,
  `cover_highlight_1_description` text DEFAULT NULL,
  `text_color_menu` varchar(255) DEFAULT NULL,
  `text_color_cocktails` varchar(255) DEFAULT NULL,
  `text_color_wines` varchar(255) DEFAULT NULL,
  `card_opacity_cover` decimal(3,2) DEFAULT NULL,
  `card_bg_color_cover` varchar(255) DEFAULT NULL,
  `card_opacity_menu` decimal(3,2) DEFAULT NULL,
  `card_opacity_cocktails` decimal(3,2) DEFAULT NULL,
  `card_opacity_wines` decimal(3,2) DEFAULT NULL,
  `font_family_cover` varchar(255) DEFAULT NULL,
  `font_family_menu` varchar(255) DEFAULT NULL,
  `font_family_cocktails` varchar(255) DEFAULT NULL,
  `font_family_wines` varchar(255) DEFAULT NULL,
  `button_color_cover` varchar(255) DEFAULT NULL,
  `button_color_menu` varchar(255) DEFAULT NULL,
  `button_color_cocktails` varchar(255) DEFAULT NULL,
  `button_color_wines` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `business_hours` text DEFAULT NULL,
  `category_name_bg_color_menu` varchar(255) DEFAULT NULL,
  `category_name_text_color_menu` varchar(255) DEFAULT NULL,
  `category_name_font_size_menu` int(11) DEFAULT NULL,
  `category_name_bg_color_cocktails` varchar(255) DEFAULT NULL,
  `category_name_text_color_cocktails` varchar(255) DEFAULT NULL,
  `category_name_font_size_cocktails` int(11) DEFAULT NULL,
  `category_name_bg_color_wines` varchar(255) DEFAULT NULL,
  `category_name_text_color_wines` varchar(255) DEFAULT NULL,
  `category_name_font_size_wines` int(11) DEFAULT NULL,
  `card_bg_color_menu` varchar(255) DEFAULT NULL,
  `card_bg_color_cocktails` varchar(255) DEFAULT NULL,
  `card_bg_color_wines` varchar(255) DEFAULT NULL,
  `button_font_size_cover` int(11) DEFAULT 18,
  `fixed_bottom_font_size` int(11) DEFAULT 14,
  `fixed_bottom_font_color` varchar(255) DEFAULT '#ffffff',
  `background_image` varchar(255) DEFAULT NULL,
  `category_name_bg_color` varchar(255) DEFAULT NULL,
  `category_name_text_color` varchar(255) DEFAULT NULL,
  `category_name_font_size` int(11) DEFAULT NULL,
  `button_label_menu` varchar(255) DEFAULT 'Menú',
  `button_label_cocktails` varchar(255) DEFAULT 'Cócteles',
  `button_label_wines` varchar(255) DEFAULT 'Cafe',
  `button_label_events` varchar(255) DEFAULT 'Eventos especiales',
  `button_label_vip` varchar(255) DEFAULT 'Lista VIP',
  `button_label_reservations` varchar(255) DEFAULT 'Reservas',
  `cover_gallery_image_1` varchar(255) DEFAULT NULL,
  `cover_gallery_image_2` varchar(255) DEFAULT NULL,
  `cover_gallery_image_3` varchar(255) DEFAULT NULL,
  `menu_hero_image` varchar(255) DEFAULT NULL,
  `cocktail_hero_image` varchar(255) DEFAULT NULL,
  `coffee_hero_image` varchar(255) DEFAULT NULL,
  `cta_image_menu` varchar(255) DEFAULT NULL,
  `cta_image_cafe` varchar(255) DEFAULT NULL,
  `cta_image_cocktails` varchar(255) DEFAULT NULL,
  `cta_image_events` varchar(255) DEFAULT NULL,
  `cta_image_reservations` varchar(255) DEFAULT NULL,
  `loyalty_points_per_visit` int(10) UNSIGNED NOT NULL DEFAULT 10,
  `loyalty_terms` text DEFAULT NULL,
  `loyalty_email_copy` text DEFAULT NULL,
  `tab_label_menu` varchar(255) DEFAULT NULL,
  `tab_label_cocktails` varchar(255) DEFAULT NULL,
  `tab_label_wines` varchar(255) DEFAULT NULL,
  `tab_label_events` varchar(255) DEFAULT NULL,
  `tab_label_loyalty` varchar(255) DEFAULT NULL,
  `show_tab_menu` tinyint(1) NOT NULL DEFAULT 1,
  `show_tab_cocktails` tinyint(1) NOT NULL DEFAULT 1,
  `show_tab_wines` tinyint(1) NOT NULL DEFAULT 1,
  `show_tab_events` tinyint(1) NOT NULL DEFAULT 1,
  `show_tab_campaigns` tinyint(1) NOT NULL DEFAULT 1,
  `show_tab_popups` tinyint(1) NOT NULL DEFAULT 1,
  `show_tab_loyalty` tinyint(1) NOT NULL DEFAULT 1,
  `featured_card_bg_color` text DEFAULT NULL,
  `featured_card_text_color` text DEFAULT NULL,
  `featured_tab_bg_color` text DEFAULT NULL,
  `featured_tab_text_color` text DEFAULT NULL,
  `cover_cta_vip_bg_color` text DEFAULT NULL,
  `cover_cta_vip_text_color` text DEFAULT NULL,
  `show_cta_menu` tinyint(1) NOT NULL DEFAULT 1,
  `show_cta_cafe` tinyint(1) NOT NULL DEFAULT 1,
  `show_cta_cocktails` tinyint(1) NOT NULL DEFAULT 1,
  `show_cta_events` tinyint(1) NOT NULL DEFAULT 1,
  `show_cta_reservations` tinyint(1) NOT NULL DEFAULT 1,
  `show_cta_vip` tinyint(1) NOT NULL DEFAULT 1,
  `subcategory_name_bg_color_menu` text DEFAULT NULL,
  `subcategory_name_text_color_menu` text DEFAULT NULL,
  `subcategory_name_bg_color_cocktails` text DEFAULT NULL,
  `subcategory_name_text_color_cocktails` text DEFAULT NULL,
  `subcategory_name_bg_color_wines` text DEFAULT NULL,
  `subcategory_name_text_color_wines` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `background_image_cover`, `background_image_menu`, `created_at`, `updated_at`, `background_image_cocktails`, `background_image_wines`, `font_family`, `text_color`, `opacity`, `card_opacity`, `button_color`, `text_color_cover`, `text_color_cover_secondary`, `cover_hero_kicker`, `cover_hero_title`, `cover_hero_paragraph`, `cover_location_text`, `cover_highlight_3_label`, `cover_highlight_3_title`, `cover_highlight_3_description`, `cover_cta_reservations_bg_color`, `cover_cta_reservations_text_color`, `cover_cta_events_bg_color`, `cover_cta_events_text_color`, `cover_cta_cocktails_bg_color`, `cover_cta_cocktails_text_color`, `cover_cta_cafe_bg_color`, `cover_cta_cafe_text_color`, `cover_cta_menu_bg_color`, `cover_cta_menu_text_color`, `cover_highlight_2_label`, `cover_highlight_2_title`, `cover_highlight_2_description`, `cover_highlight_1_label`, `cover_highlight_1_title`, `cover_highlight_1_description`, `text_color_menu`, `text_color_cocktails`, `text_color_wines`, `card_opacity_cover`, `card_bg_color_cover`, `card_opacity_menu`, `card_opacity_cocktails`, `card_opacity_wines`, `font_family_cover`, `font_family_menu`, `font_family_cocktails`, `font_family_wines`, `button_color_cover`, `button_color_menu`, `button_color_cocktails`, `button_color_wines`, `logo`, `facebook_url`, `twitter_url`, `instagram_url`, `phone_number`, `business_hours`, `category_name_bg_color_menu`, `category_name_text_color_menu`, `category_name_font_size_menu`, `category_name_bg_color_cocktails`, `category_name_text_color_cocktails`, `category_name_font_size_cocktails`, `category_name_bg_color_wines`, `category_name_text_color_wines`, `category_name_font_size_wines`, `card_bg_color_menu`, `card_bg_color_cocktails`, `card_bg_color_wines`, `button_font_size_cover`, `fixed_bottom_font_size`, `fixed_bottom_font_color`, `background_image`, `category_name_bg_color`, `category_name_text_color`, `category_name_font_size`, `button_label_menu`, `button_label_cocktails`, `button_label_wines`, `button_label_events`, `button_label_vip`, `button_label_reservations`, `cover_gallery_image_1`, `cover_gallery_image_2`, `cover_gallery_image_3`, `menu_hero_image`, `cocktail_hero_image`, `coffee_hero_image`, `cta_image_menu`, `cta_image_cafe`, `cta_image_cocktails`, `cta_image_events`, `cta_image_reservations`, `loyalty_points_per_visit`, `loyalty_terms`, `loyalty_email_copy`, `tab_label_menu`, `tab_label_cocktails`, `tab_label_wines`, `tab_label_events`, `tab_label_loyalty`, `show_tab_menu`, `show_tab_cocktails`, `show_tab_wines`, `show_tab_events`, `show_tab_campaigns`, `show_tab_popups`, `show_tab_loyalty`, `featured_card_bg_color`, `featured_card_text_color`, `featured_tab_bg_color`, `featured_tab_text_color`, `cover_cta_vip_bg_color`, `cover_cta_vip_text_color`, `show_cta_menu`, `show_cta_cafe`, `show_cta_cocktails`, `show_cta_events`, `show_cta_reservations`, `show_cta_vip`, `subcategory_name_bg_color_menu`, `subcategory_name_text_color_menu`, `subcategory_name_bg_color_cocktails`, `subcategory_name_text_color_cocktails`, `subcategory_name_bg_color_wines`, `subcategory_name_text_color_wines`) VALUES
(1, NULL, NULL, '2026-01-22 20:38:31', '2026-01-22 21:16:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '#ca1616', '#bfbfbf', 'Tkeria , Cantina, Cockteles', 'Bienvenidos a Takbron', 'Taquria y cantina en el centro de la Isla', 'Barranquitas', NULL, NULL, NULL, '#000000', '#ffffff', '#000000', '#ffffff', '#000000', '#ffffff', '#000000', '#ffffff', '#000000', '#ffffff', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.00, '#000000', NULL, NULL, NULL, 'Arial', NULL, NULL, NULL, '#c53d26', NULL, NULL, NULL, 'default-logo.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 18, 14, '#ffffff', NULL, NULL, NULL, NULL, 'Menú', 'Bebidas', 'Cafe', 'Eventos especiales', 'Lista VIP', 'Reservas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10, NULL, NULL, 'Menú', 'Cócteles', 'Café & Brunch', 'Eventos', 'Fidelidad', 1, 1, 1, 1, 1, 1, 1, '#0f172a', '#ffffff', '#ffffff', '#ffffff', '#0f172a', '#ffffff', 1, 0, 1, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `table_sessions`
--

CREATE TABLE `table_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `server_id` bigint(20) UNSIGNED NOT NULL,
  `open_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_channel` varchar(255) NOT NULL DEFAULT 'table',
  `table_label` varchar(255) NOT NULL,
  `party_size` smallint(5) UNSIGNED NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `guest_phone` varchar(255) NOT NULL,
  `order_mode` varchar(255) NOT NULL DEFAULT 'table',
  `qr_token` varchar(255) NOT NULL,
  `status` enum('active','closed','expired') NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `loyalty_visit_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `api_token` varchar(80) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `invitation_token` varchar(255) DEFAULT NULL,
  `invitation_sent_at` timestamp NULL DEFAULT NULL,
  `invitation_accepted_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `api_token`, `role`, `invitation_token`, `invitation_sent_at`, `invitation_accepted_at`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Admin Café', 'info@bbtdpr.com', NULL, '$2y$12$6CyyEuqUr0cS1GKsvdbvpOplJPBHsboWstE8o263iqM82XNq6DQhm', NULL, NULL, 'admin', NULL, NULL, NULL, 1, '2026-01-22 20:51:01', '2026-01-22 20:51:01'),
(2, 'Admin Café', 'info@bbtspr.com', NULL, '$2y$12$EnOmmL4dBicS2LHJXkRj7us8vK1.RXXu/PR53ru8UAYpCdeCBDrYm', NULL, NULL, 'admin', NULL, NULL, NULL, 1, '2026-01-22 20:51:01', '2026-01-22 20:51:01');

-- --------------------------------------------------------

--
-- Table structure for table `wines`
--

CREATE TABLE `wines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `featured_on_cover` tinyint(1) NOT NULL DEFAULT 0,
  `region_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wine_categories`
--

CREATE TABLE `wine_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `show_on_cover` tinyint(1) NOT NULL DEFAULT 0,
  `cover_title` varchar(255) DEFAULT NULL,
  `cover_subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wine_category_tax`
--

CREATE TABLE `wine_category_tax` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wine_category_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wine_subcategories`
--

CREATE TABLE `wine_subcategories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wine_category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wine_tax`
--

CREATE TABLE `wine_tax` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wine_id` bigint(20) UNSIGNED NOT NULL,
  `tax_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wine_types`
--

CREATE TABLE `wine_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cantina_categories`
--
ALTER TABLE `cantina_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cantina_items`
--
ALTER TABLE `cantina_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cantina_items_category_id_foreign` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_subcategories`
--
ALTER TABLE `category_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_subcategories_category_id_foreign` (`category_id`);

--
-- Indexes for table `category_tax`
--
ALTER TABLE `category_tax`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_tax_category_id_tax_id_unique` (`category_id`,`tax_id`),
  ADD KEY `category_tax_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `cocktails`
--
ALTER TABLE `cocktails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cocktails_category_id_foreign` (`category_id`),
  ADD KEY `cocktails_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `cocktail_categories`
--
ALTER TABLE `cocktail_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cocktail_category_tax`
--
ALTER TABLE `cocktail_category_tax`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cocktail_category_tax_cocktail_category_id_tax_id_unique` (`cocktail_category_id`,`tax_id`),
  ADD KEY `cocktail_category_tax_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `cocktail_dish`
--
ALTER TABLE `cocktail_dish`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cocktail_dish_cocktail_id_foreign` (`cocktail_id`),
  ADD KEY `cocktail_dish_dish_id_foreign` (`dish_id`);

--
-- Indexes for table `cocktail_subcategories`
--
ALTER TABLE `cocktail_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cocktail_subcategories_cocktail_category_id_foreign` (`cocktail_category_id`);

--
-- Indexes for table `cocktail_tax`
--
ALTER TABLE `cocktail_tax`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cocktail_tax_cocktail_id_tax_id_unique` (`cocktail_id`,`tax_id`),
  ADD KEY `cocktail_tax_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dishes_category_id_foreign` (`category_id`),
  ADD KEY `dishes_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `dish_food_pairing`
--
ALTER TABLE `dish_food_pairing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_food_pairing_dish_id_foreign` (`dish_id`),
  ADD KEY `dish_food_pairing_food_pairing_id_foreign` (`food_pairing_id`);

--
-- Indexes for table `dish_recommendations`
--
ALTER TABLE `dish_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dish_recommendations_unique` (`dish_id`,`recommended_dish_id`),
  ADD KEY `dish_recommendations_recommended_dish_id_foreign` (`recommended_dish_id`);

--
-- Indexes for table `dish_tax`
--
ALTER TABLE `dish_tax`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dish_tax_dish_id_tax_id_unique` (`dish_id`,`tax_id`),
  ADD KEY `dish_tax_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `dish_wine`
--
ALTER TABLE `dish_wine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_wine_dish_id_foreign` (`dish_id`),
  ADD KEY `dish_wine_wine_id_foreign` (`wine_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `events_slug_unique` (`slug`);

--
-- Indexes for table `event_notifications`
--
ALTER TABLE `event_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_notifications_event_id_email_unique` (`event_id`,`email`);

--
-- Indexes for table `event_promotions`
--
ALTER TABLE `event_promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_sections`
--
ALTER TABLE `event_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_sections_event_id_foreign` (`event_id`);

--
-- Indexes for table `event_tickets`
--
ALTER TABLE `event_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_tickets_ticket_code_unique` (`ticket_code`),
  ADD KEY `event_tickets_event_id_foreign` (`event_id`),
  ADD KEY `event_tickets_event_section_id_foreign` (`event_section_id`);

--
-- Indexes for table `extras`
--
ALTER TABLE `extras`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `extra_assignments`
--
ALTER TABLE `extra_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `extra_assignments_unique` (`extra_id`,`assignable_id`,`assignable_type`),
  ADD KEY `extra_assignments_assignable_type_assignable_id_index` (`assignable_type`,`assignable_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `food_pairings`
--
ALTER TABLE `food_pairings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `food_pairings_name_unique` (`name`),
  ADD KEY `food_pairings_dish_id_foreign` (`dish_id`);

--
-- Indexes for table `food_pairing_wine`
--
ALTER TABLE `food_pairing_wine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_pairing_wine_wine_id_foreign` (`wine_id`),
  ADD KEY `food_pairing_wine_food_pairing_id_foreign` (`food_pairing_id`);

--
-- Indexes for table `grapes`
--
ALTER TABLE `grapes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grapes_name_unique` (`name`),
  ADD KEY `grapes_wine_type_id_foreign` (`wine_type_id`);

--
-- Indexes for table `grape_wine`
--
ALTER TABLE `grape_wine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grape_wine_wine_id_foreign` (`wine_id`),
  ADD KEY `grape_wine_grape_id_foreign` (`grape_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_customers`
--
ALTER TABLE `loyalty_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loyalty_customers_email_unique` (`email`);

--
-- Indexes for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loyalty_redemptions_loyalty_customer_id_foreign` (`loyalty_customer_id`),
  ADD KEY `loyalty_redemptions_loyalty_reward_id_foreign` (`loyalty_reward_id`),
  ADD KEY `loyalty_redemptions_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_visits`
--
ALTER TABLE `loyalty_visits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loyalty_visits_qr_token_unique` (`qr_token`),
  ADD KEY `loyalty_visits_server_id_foreign` (`server_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_table_session_id_foreign` (`table_session_id`),
  ADD KEY `orders_server_id_foreign` (`server_id`),
  ADD KEY `orders_status_server_id_index` (`status`,`server_id`);

--
-- Indexes for table `order_batches`
--
ALTER TABLE `order_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_batches_order_id_foreign` (`order_id`),
  ADD KEY `order_batches_status_order_id_index` (`status`,`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_itemable_type_itemable_id_index` (`itemable_type`,`itemable_id`),
  ADD KEY `order_items_category_scope_category_order_index` (`category_scope`,`category_order`),
  ADD KEY `order_items_order_batch_id_index` (`order_batch_id`),
  ADD KEY `order_items_voided_by_foreign` (`voided_by`);

--
-- Indexes for table `order_item_extras`
--
ALTER TABLE `order_item_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_item_extras_order_item_id_foreign` (`order_item_id`),
  ADD KEY `order_item_extras_extra_id_foreign` (`extra_id`);

--
-- Indexes for table `order_item_prep_labels`
--
ALTER TABLE `order_item_prep_labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_item_prep_labels_unique` (`order_item_id`,`prep_label_id`),
  ADD KEY `order_item_prep_labels_updated_by_foreign` (`updated_by`),
  ADD KEY `order_item_prep_labels_prep_label_id_status_index` (`prep_label_id`,`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_foreign` (`order_id`),
  ADD KEY `payments_created_by_foreign` (`created_by`);

--
-- Indexes for table `popups`
--
ALTER TABLE `popups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prep_areas`
--
ALTER TABLE `prep_areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prep_areas_slug_unique` (`slug`);

--
-- Indexes for table `prep_labelables`
--
ALTER TABLE `prep_labelables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prep_labelables_unique` (`prep_label_id`,`labelable_type`,`labelable_id`),
  ADD KEY `prep_labelables_labelable_type_labelable_id_index` (`labelable_type`,`labelable_id`);

--
-- Indexes for table `prep_labels`
--
ALTER TABLE `prep_labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prep_labels_slug_unique` (`slug`),
  ADD KEY `prep_labels_prep_area_id_foreign` (`prep_area_id`),
  ADD KEY `prep_labels_printer_id_foreign` (`printer_id`);

--
-- Indexes for table `printers`
--
ALTER TABLE `printers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `printers_token_unique` (`token`);

--
-- Indexes for table `printer_routes`
--
ALTER TABLE `printer_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `printer_routes_printer_id_foreign` (`printer_id`),
  ADD KEY `printer_routes_print_template_id_foreign` (`print_template_id`),
  ADD KEY `printer_routes_category_scope_category_id_index` (`category_scope`,`category_id`);

--
-- Indexes for table `print_jobs`
--
ALTER TABLE `print_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `print_jobs_order_id_foreign` (`order_id`),
  ADD KEY `print_jobs_print_template_id_foreign` (`print_template_id`),
  ADD KEY `print_jobs_printer_id_status_index` (`printer_id`,`status`);

--
-- Indexes for table `print_templates`
--
ALTER TABLE `print_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refunds_order_id_foreign` (`order_id`),
  ADD KEY `refunds_payment_id_foreign` (`payment_id`),
  ADD KEY `refunds_created_by_foreign` (`created_by`),
  ADD KEY `refunds_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `regions_name_unique` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table_sessions`
--
ALTER TABLE `table_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_sessions_qr_token_unique` (`qr_token`),
  ADD KEY `table_sessions_server_id_foreign` (`server_id`),
  ADD KEY `table_sessions_status_expires_at_index` (`status`,`expires_at`),
  ADD KEY `table_sessions_loyalty_visit_id_foreign` (`loyalty_visit_id`),
  ADD KEY `table_sessions_open_order_id_foreign` (`open_order_id`),
  ADD KEY `table_sessions_service_channel_index` (`service_channel`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_api_token_unique` (`api_token`);

--
-- Indexes for table `wines`
--
ALTER TABLE `wines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wines_category_id_foreign` (`category_id`),
  ADD KEY `wines_region_id_foreign` (`region_id`),
  ADD KEY `wines_type_id_foreign` (`type_id`),
  ADD KEY `wines_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `wine_categories`
--
ALTER TABLE `wine_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wine_category_tax`
--
ALTER TABLE `wine_category_tax`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wine_category_tax_wine_category_id_tax_id_unique` (`wine_category_id`,`tax_id`),
  ADD KEY `wine_category_tax_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `wine_subcategories`
--
ALTER TABLE `wine_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wine_subcategories_wine_category_id_foreign` (`wine_category_id`);

--
-- Indexes for table `wine_tax`
--
ALTER TABLE `wine_tax`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wine_tax_wine_id_tax_id_unique` (`wine_id`,`tax_id`),
  ADD KEY `wine_tax_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `wine_types`
--
ALTER TABLE `wine_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wine_types_name_unique` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cantina_categories`
--
ALTER TABLE `cantina_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `cantina_items`
--
ALTER TABLE `cantina_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `category_subcategories`
--
ALTER TABLE `category_subcategories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_tax`
--
ALTER TABLE `category_tax`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cocktails`
--
ALTER TABLE `cocktails`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cocktail_categories`
--
ALTER TABLE `cocktail_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cocktail_category_tax`
--
ALTER TABLE `cocktail_category_tax`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cocktail_dish`
--
ALTER TABLE `cocktail_dish`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cocktail_subcategories`
--
ALTER TABLE `cocktail_subcategories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cocktail_tax`
--
ALTER TABLE `cocktail_tax`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `dish_food_pairing`
--
ALTER TABLE `dish_food_pairing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dish_recommendations`
--
ALTER TABLE `dish_recommendations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dish_tax`
--
ALTER TABLE `dish_tax`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dish_wine`
--
ALTER TABLE `dish_wine`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_notifications`
--
ALTER TABLE `event_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_promotions`
--
ALTER TABLE `event_promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_sections`
--
ALTER TABLE `event_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_tickets`
--
ALTER TABLE `event_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extras`
--
ALTER TABLE `extras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extra_assignments`
--
ALTER TABLE `extra_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_pairings`
--
ALTER TABLE `food_pairings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_pairing_wine`
--
ALTER TABLE `food_pairing_wine`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grapes`
--
ALTER TABLE `grapes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grape_wine`
--
ALTER TABLE `grape_wine`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_customers`
--
ALTER TABLE `loyalty_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_visits`
--
ALTER TABLE `loyalty_visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_batches`
--
ALTER TABLE `order_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_item_extras`
--
ALTER TABLE `order_item_extras`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_item_prep_labels`
--
ALTER TABLE `order_item_prep_labels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `popups`
--
ALTER TABLE `popups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prep_areas`
--
ALTER TABLE `prep_areas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prep_labelables`
--
ALTER TABLE `prep_labelables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prep_labels`
--
ALTER TABLE `prep_labels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printers`
--
ALTER TABLE `printers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printer_routes`
--
ALTER TABLE `printer_routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_jobs`
--
ALTER TABLE `print_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `print_templates`
--
ALTER TABLE `print_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `table_sessions`
--
ALTER TABLE `table_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wines`
--
ALTER TABLE `wines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wine_categories`
--
ALTER TABLE `wine_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wine_category_tax`
--
ALTER TABLE `wine_category_tax`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wine_subcategories`
--
ALTER TABLE `wine_subcategories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wine_tax`
--
ALTER TABLE `wine_tax`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wine_types`
--
ALTER TABLE `wine_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cantina_items`
--
ALTER TABLE `cantina_items`
  ADD CONSTRAINT `cantina_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `cantina_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_subcategories`
--
ALTER TABLE `category_subcategories`
  ADD CONSTRAINT `category_subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_tax`
--
ALTER TABLE `category_tax`
  ADD CONSTRAINT `category_tax_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_tax_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cocktails`
--
ALTER TABLE `cocktails`
  ADD CONSTRAINT `cocktails_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `cocktail_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cocktails_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `cocktail_subcategories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cocktail_category_tax`
--
ALTER TABLE `cocktail_category_tax`
  ADD CONSTRAINT `cocktail_category_tax_cocktail_category_id_foreign` FOREIGN KEY (`cocktail_category_id`) REFERENCES `cocktail_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cocktail_category_tax_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cocktail_dish`
--
ALTER TABLE `cocktail_dish`
  ADD CONSTRAINT `cocktail_dish_cocktail_id_foreign` FOREIGN KEY (`cocktail_id`) REFERENCES `cocktails` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cocktail_dish_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cocktail_subcategories`
--
ALTER TABLE `cocktail_subcategories`
  ADD CONSTRAINT `cocktail_subcategories_cocktail_category_id_foreign` FOREIGN KEY (`cocktail_category_id`) REFERENCES `cocktail_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cocktail_tax`
--
ALTER TABLE `cocktail_tax`
  ADD CONSTRAINT `cocktail_tax_cocktail_id_foreign` FOREIGN KEY (`cocktail_id`) REFERENCES `cocktails` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cocktail_tax_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dishes_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `category_subcategories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dish_food_pairing`
--
ALTER TABLE `dish_food_pairing`
  ADD CONSTRAINT `dish_food_pairing_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dish_food_pairing_food_pairing_id_foreign` FOREIGN KEY (`food_pairing_id`) REFERENCES `food_pairings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dish_recommendations`
--
ALTER TABLE `dish_recommendations`
  ADD CONSTRAINT `dish_recommendations_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dish_recommendations_recommended_dish_id_foreign` FOREIGN KEY (`recommended_dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dish_tax`
--
ALTER TABLE `dish_tax`
  ADD CONSTRAINT `dish_tax_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dish_tax_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dish_wine`
--
ALTER TABLE `dish_wine`
  ADD CONSTRAINT `dish_wine_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dish_wine_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_notifications`
--
ALTER TABLE `event_notifications`
  ADD CONSTRAINT `event_notifications_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_sections`
--
ALTER TABLE `event_sections`
  ADD CONSTRAINT `event_sections_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_tickets`
--
ALTER TABLE `event_tickets`
  ADD CONSTRAINT `event_tickets_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_tickets_event_section_id_foreign` FOREIGN KEY (`event_section_id`) REFERENCES `event_sections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `extra_assignments`
--
ALTER TABLE `extra_assignments`
  ADD CONSTRAINT `extra_assignments_extra_id_foreign` FOREIGN KEY (`extra_id`) REFERENCES `extras` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_pairings`
--
ALTER TABLE `food_pairings`
  ADD CONSTRAINT `food_pairings_dish_id_foreign` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `food_pairing_wine`
--
ALTER TABLE `food_pairing_wine`
  ADD CONSTRAINT `food_pairing_wine_food_pairing_id_foreign` FOREIGN KEY (`food_pairing_id`) REFERENCES `food_pairings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_pairing_wine_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grapes`
--
ALTER TABLE `grapes`
  ADD CONSTRAINT `grapes_wine_type_id_foreign` FOREIGN KEY (`wine_type_id`) REFERENCES `wine_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `grape_wine`
--
ALTER TABLE `grape_wine`
  ADD CONSTRAINT `grape_wine_grape_id_foreign` FOREIGN KEY (`grape_id`) REFERENCES `grapes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grape_wine_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_redemptions`
--
ALTER TABLE `loyalty_redemptions`
  ADD CONSTRAINT `loyalty_redemptions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loyalty_redemptions_loyalty_customer_id_foreign` FOREIGN KEY (`loyalty_customer_id`) REFERENCES `loyalty_customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_redemptions_loyalty_reward_id_foreign` FOREIGN KEY (`loyalty_reward_id`) REFERENCES `loyalty_rewards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_visits`
--
ALTER TABLE `loyalty_visits`
  ADD CONSTRAINT `loyalty_visits_server_id_foreign` FOREIGN KEY (`server_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_server_id_foreign` FOREIGN KEY (`server_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_table_session_id_foreign` FOREIGN KEY (`table_session_id`) REFERENCES `table_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_batches`
--
ALTER TABLE `order_batches`
  ADD CONSTRAINT `order_batches_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_batch_id_foreign` FOREIGN KEY (`order_batch_id`) REFERENCES `order_batches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_item_extras`
--
ALTER TABLE `order_item_extras`
  ADD CONSTRAINT `order_item_extras_extra_id_foreign` FOREIGN KEY (`extra_id`) REFERENCES `extras` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_item_extras_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_item_prep_labels`
--
ALTER TABLE `order_item_prep_labels`
  ADD CONSTRAINT `order_item_prep_labels_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_prep_labels_prep_label_id_foreign` FOREIGN KEY (`prep_label_id`) REFERENCES `prep_labels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_item_prep_labels_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prep_labelables`
--
ALTER TABLE `prep_labelables`
  ADD CONSTRAINT `prep_labelables_prep_label_id_foreign` FOREIGN KEY (`prep_label_id`) REFERENCES `prep_labels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prep_labels`
--
ALTER TABLE `prep_labels`
  ADD CONSTRAINT `prep_labels_prep_area_id_foreign` FOREIGN KEY (`prep_area_id`) REFERENCES `prep_areas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prep_labels_printer_id_foreign` FOREIGN KEY (`printer_id`) REFERENCES `printers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `printer_routes`
--
ALTER TABLE `printer_routes`
  ADD CONSTRAINT `printer_routes_print_template_id_foreign` FOREIGN KEY (`print_template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `printer_routes_printer_id_foreign` FOREIGN KEY (`printer_id`) REFERENCES `printers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `print_jobs`
--
ALTER TABLE `print_jobs`
  ADD CONSTRAINT `print_jobs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `print_jobs_print_template_id_foreign` FOREIGN KEY (`print_template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `print_jobs_printer_id_foreign` FOREIGN KEY (`printer_id`) REFERENCES `printers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `refunds_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `refunds_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `table_sessions`
--
ALTER TABLE `table_sessions`
  ADD CONSTRAINT `table_sessions_loyalty_visit_id_foreign` FOREIGN KEY (`loyalty_visit_id`) REFERENCES `loyalty_visits` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `table_sessions_open_order_id_foreign` FOREIGN KEY (`open_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `table_sessions_server_id_foreign` FOREIGN KEY (`server_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wines`
--
ALTER TABLE `wines`
  ADD CONSTRAINT `wines_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `wine_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wines_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wines_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `wine_subcategories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wines_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `wine_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wine_category_tax`
--
ALTER TABLE `wine_category_tax`
  ADD CONSTRAINT `wine_category_tax_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wine_category_tax_wine_category_id_foreign` FOREIGN KEY (`wine_category_id`) REFERENCES `wine_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wine_subcategories`
--
ALTER TABLE `wine_subcategories`
  ADD CONSTRAINT `wine_subcategories_wine_category_id_foreign` FOREIGN KEY (`wine_category_id`) REFERENCES `wine_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wine_tax`
--
ALTER TABLE `wine_tax`
  ADD CONSTRAINT `wine_tax_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wine_tax_wine_id_foreign` FOREIGN KEY (`wine_id`) REFERENCES `wines` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
