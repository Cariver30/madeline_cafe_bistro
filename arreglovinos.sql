-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: sdb-73.hosting.stackcp.net
-- Generation Time: Nov 02, 2025 at 12:58 PM
-- Server version: 10.6.18-MariaDB-log
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prmenu-353036332458`
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

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `relevance` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `order`, `created_at`, `updated_at`, `relevance`) VALUES
(5, 'APERITIVOS', 0, '2024-07-21 13:40:40', '2024-07-21 13:40:40', 0),
(6, 'ENSALADAS', 0, '2024-07-21 13:57:17', '2024-07-21 13:57:17', 0),
(7, 'SOPAS', 0, '2024-07-21 14:00:25', '2024-07-21 14:00:25', 0),
(8, 'AVES', 0, '2024-07-21 14:01:33', '2024-07-21 14:01:33', 0),
(9, 'CERDO', 0, '2024-07-21 14:03:42', '2024-07-21 14:03:42', 0),
(10, 'CARNES ROJAS', 0, '2024-07-21 14:05:13', '2024-07-21 14:05:13', 0),
(11, 'SEAFOOD', 0, '2024-07-21 14:08:57', '2024-07-21 14:08:57', 0),
(12, 'VEGGIE', 0, '2024-07-21 14:18:02', '2024-07-21 14:18:02', 0),
(13, 'KID\'S MENU (Hasta 10 años)', 0, '2024-07-21 14:19:40', '2024-07-21 14:19:40', 0),
(14, 'ACOMPAÑANTES', 0, '2024-07-21 14:30:39', '2024-07-21 14:30:39', 0),
(15, 'ESPECIAL DEL CHEF', 0, '2024-07-21 14:40:42', '2024-07-21 14:41:41', 0),
(16, 'POSTRES', 0, '2024-07-21 14:41:58', '2024-07-21 14:41:58', 0);

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cocktails`
--

INSERT INTO `cocktails` (`id`, `name`, `description`, `price`, `category_id`, `created_at`, `updated_at`, `image`, `visible`) VALUES
(28, 'La Montaña', 'Rum Añejo, sweet vermouth, licor averna y angostura. Cóctel aperitivo, spirit forward.  Perfecto para comenzar.', 10.00, 5, '2025-05-16 17:45:00', '2025-05-16 17:45:00', 'cocktail_images/et03k792mj8uIiAZ0Ky9Un9FZBymiQzmwGLcZOP6.jpg', 1),
(29, 'Asado', 'Fat wash de Ron Barrilito 3 y grasa de costillas ahumadas, sirope de pimienta negra y bitter aromaticos.  Acompañado de una deliciosa costilla la cual complemente el cóctel.', 15.00, 5, '2025-05-16 17:55:44', '2025-08-10 18:26:22', 'cocktail_images/vp8ZJ4eiBUe5hO0wY9GLYuRPGhcC6TgG6aHqBdYv.jpg', 0),
(30, 'Conciencia', 'Mezcal, aperol, amargo italiano y chocolate bitter.  Cóctel aperitivo, spirit forward.  Perfecto para comenzar.', 10.00, 5, '2025-05-16 17:58:56', '2025-05-17 00:31:47', 'cocktail_images/do9sLnEAo32Cl618YTPIhSMh5DMZvdoK8VQzSs03.jpg', 1),
(31, 'Jardin de Elena', 'Cóctel refrescante a base  de vodka, st. germain, homemade raspberries / basil syrup and fresh lime.', 11.00, 5, '2025-05-16 18:08:06', '2025-05-16 18:15:31', 'cocktail_images/sxCzYamdK8mgC0cqFKi0kypycIqv7dd5544c7HTs.jpg', 1),
(32, 'El Cielo', 'Cóctel interactivo mezclando vodka, vermouth bianco, genepy, cotton candy syrup, toronja rosada y cherry bitter.  Acompañado  de un algodón de azúcar.', 13.00, 5, '2025-05-16 18:13:47', '2025-05-16 18:13:47', 'cocktail_images/lcBTqJ38kV9bvgi6IW9s2hL6X0g4K5VaVy8VYNN6.jpg', 1),
(34, 'Violeta', 'Cóctel floral que combina la ginebra, crema de violeta, st. germain, lima y cherry bitter. Terminado con vino espumoso.', 11.00, 5, '2025-05-16 18:20:00', '2025-05-16 18:20:00', 'cocktail_images/RB8kNqgKYlhbVOsOdUtkGRrcJshPNgZNYcK4ncFV.jpg', 1),
(35, 'Mongo', 'Ginebra, grand marnier, sirope de romero, lima, ginger beer de flor de jamaica confeccionada en Asador. Conocido como nuestra variación de un London Mule.', 13.00, 5, '2025-05-16 18:23:51', '2025-05-17 00:29:42', 'cocktail_images/JzKkWLlNOBMJTv6tE90QeGzvmTnMCLu6JjSyiLQv.jpg', 1),
(36, 'El Mercado', 'Combina el tequila, una amarga y dulce fusión de aperol y piña hecha en la casa, lima y un toque de jugo de parcha.', 12.00, 5, '2025-05-16 18:26:35', '2025-05-17 14:32:47', 'cocktail_images/jYcgehu9LShYurNCRQR1TUpnxbRMTI4hgSvQ9UEI.jpg', 1),
(37, 'El Milagro', 'Tequila, licor 43, agave reducido, crema de cassis, lima y aquafaba. Cóctel sedoso de perfil tropical.', 12.00, 5, '2025-05-16 18:28:29', '2025-05-16 18:49:19', 'cocktail_images/Wmh3nOEQTKeKUMuCz7lTrH1S2cR9llPusIh6dpmX.jpg', 1),
(38, 'Flor de México', 'Mezcal, tequila, Licor Ancho Reyes (chiles secados al sol), sirope de flor de jamaica y tintura de limón. Una combinación balanceada que te permitirá saborear cada ingrediente de manera sutil.', 12.00, 5, '2025-05-16 18:29:43', '2025-05-16 18:35:34', 'cocktail_images/LaxKkP6YZzKqtv5PsuJvpBZMOX6EXaVPqyoyu3Up.jpg', 1),
(39, 'Mango Bajito', 'Mezcal, 1800 Coco, sirope mango y canela, lima y tintura de camomila. Cóctel amigable al paladar de perfil tropical.', 11.00, 5, '2025-05-16 18:34:23', '2025-05-16 18:45:10', 'cocktail_images/Qft2Y9ssZPCLRSlWOLflmr82cKb2BJX6GtZNTGGJ.jpg', 1),
(40, 'Lado Obscuro', 'Scotch, fernet branca, tamarindo, lima, sirope de canela y chocolate bitter.', 12.00, 5, '2025-05-16 18:47:48', '2025-05-16 18:47:48', 'cocktail_images/uu1SzbqMHF2JVOviR74tVDaiBybUAGB3hSdnJyOL.jpg', 1),
(41, 'El Mañanero', 'Whiskey, licor averna, sirope de café y aromatic bitter. Terminado con espuma de coco. El cóctel perfecto para culminar tu cena.', 11.00, 5, '2025-05-16 18:51:54', '2025-05-17 00:31:25', 'cocktail_images/FaE1tFWqZ9oXm3QED4DzG3NenmuyQKNtBwpCTQ7T.jpg', 1),
(42, 'Arcángel', 'Rum, whiskey, licor amaretto, jugo de naranja, lima, sirope de mango, cúrcuma, jengibre; cardamom bitter. Nuestro cóctel tiki, de sabores exóticos y llamativos.', 13.00, 5, '2025-05-16 18:52:38', '2025-05-17 14:34:14', 'cocktail_images/ujqVrJ7OVcFSP8uyfySZVzxWALJ4jHcR0ie45hf2.jpg', 1),
(44, 'Sabor del Caribe', 'Mocktail refrescante y herbal, inspirado en un Frech 75.   Sus ingredientes son: jugo de melón fresco, lima, efervescencia, infusión de albahaca y romero (Disponible como Cóctel $12.00)', 10.00, 5, '2025-05-16 19:12:05', '2025-05-17 00:30:00', 'cocktail_images/kPm8zzo1aFeLUwVgW025U32kCio9r6aJUjL7jYnj.jpg', 1),
(45, 'Brisa Botánica', 'Mocktail botánico y refrescante.  Infusión de pepino fresco y romero, lima.  (Disponible como Cóctel $12.00)', 10.00, 5, '2025-05-16 19:13:34', '2025-05-17 14:33:45', 'cocktail_images/D8yMuZxEmMadsfm0en2nvNdHcu2nonleNkkPGg4L.jpg', 1),
(46, 'Néctar Dorado', 'Mocktail de sabores pronunciados y tropicales.  Contiene miel reducida, infusión de piña y tomillo.  (Disponible como Cóctel $12.00)', 10.00, 5, '2025-05-16 19:23:08', '2025-05-16 19:25:50', 'cocktail_images/VAC5JUlv99yGyrrtkGc9I5cB8r0BZdMvcNz3WMZh.jpg', 1),
(48, 'San Rafael', 'Sabores caribeños y amigables.  Woodford, licor de parcha, licor de banana, sirope de coco, lima y cacao bitter.', 12.00, 5, '2025-05-16 19:28:05', '2025-05-16 19:55:51', 'cocktail_images/m3NPUSFUc4PIqJieFOp2YlzRGqWCChCiiEKfLg6q.jpg', 1),
(49, 'Sabor de Campo', 'Whiskey fusionado con miel, horchata, sirope de jengibre y canela y aromatic Bitter. Acompañado de un chocolate obscuro preparado en la casa (contiene nueces).', 11.00, 5, '2025-05-16 19:28:53', '2025-05-16 19:28:53', 'cocktail_images/TWX570rRApMrWfuslcYtsoPMhdH0t6DMlvPUpDhw.jpg', 1),
(50, 'Brisket Old Fashioned', 'Fat wash de brisket y ron barrilito 3 estrellas, syrup de canela y cacao bitters.', 12.00, 5, '2025-06-01 15:46:33', '2025-06-13 21:42:48', 'cocktail_images/4CrkNdSTVqLpE9xqFovZpuNawUEW0Xe9GjyTK8P4.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cocktail_categories`
--

CREATE TABLE `cocktail_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cocktail_categories`
--

INSERT INTO `cocktail_categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(5, 'COCTELES', '2024-07-21 15:18:44', '2024-07-21 15:18:44');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `name`, `description`, `price`, `image`, `category_id`, `created_at`, `updated_at`, `visible`) VALUES
(21, 'Pan con Bacalao', 'Pan sobao, salsa pesto y bacalaito frito.', 12.00, 'dish_images/3hhrMCMn74xzHBlpOcMi0zbYaCjEzNHGwrStgL1W.jpg', 5, '2024-07-21 13:41:18', '2025-09-13 00:08:22', 0),
(22, 'Queso Kasseri', 'Exquisito queso turco de cabra y oveja.  Acompañado de una mermelada de higo y un bollito de pan sobao.  Flameado en mesa con Brandy de Jerez y extinguido con zumo de lima.', 14.00, 'dish_images/gIyWRwZi5abAtIKldkE1uSpP0QBX9eYeb4hncECj.jpg', 5, '2024-07-21 13:42:19', '2024-10-04 16:38:18', 1),
(23, 'Chorizo Parrillero', 'Jugoso chorizo parrillero glaseados en salsa de mango habanero.', 12.00, 'dish_images/neXOnRnBLUDa4yfnUkii3xNqLt0Kj38n5MTcz55v.jpg', 5, '2024-07-21 13:43:27', '2024-11-02 03:55:15', 0),
(24, 'La Ahumada', 'Tierna carne de cerdo ahumada salteada en Ron Barrilito y Cidra.', 16.00, 'dish_images/Ra6QZtUvlGhT1HeKcBLvAdVQl4wFjkCXxrli9eir.jpg', 5, '2024-07-21 13:44:31', '2024-09-28 20:09:47', 1),
(25, 'Humus', 'Crema de garbanzos cocidos con zumo de limón, aceite de oliva y ajo.  Incluye zanahorias y celery.', 14.00, 'dish_images/dBO4Zu82GU62ZZn5VB54ba1jPu5pRFD8yBDMqphE.jpg', 5, '2024-07-21 13:47:51', '2024-11-10 19:22:53', 1),
(26, 'Croquetas de Temporada', '100% hechas en casa.', 15.00, 'dish_images/fZqcmVgyeubnMoeJnaCGm5OIPJPbejacDt6TvYk6.jpg', 5, '2024-07-21 13:48:25', '2024-09-28 19:42:25', 1),
(27, 'Chinchulines', 'Chicharrón de Pork Belly.', 18.00, 'dish_images/b6vTL6TI8Zql1RyPn4X2cNCFfiRTYhYPcxCE7Al9.jpg', 5, '2024-07-21 13:48:55', '2024-11-13 18:52:21', 1),
(28, 'Ceviche de Chinchulines', 'Chicharrón de Pork Belly.', 21.00, 'dish_images/o5kujAyQk8dXqojAOm6tu77j3bxOcaRhXhCQn9xT.jpg', 5, '2024-07-21 13:49:33', '2024-09-28 19:41:10', 1),
(29, 'Chicharrón KanKan', 'Crujientes pedazos de cerdo fritos con salsa de guayaba y jengibre.', 16.00, 'dish_images/pEnFK9LigRoA8fKWZKlgcL3EKuiZtwY5FpdOq7Dl.png', 5, '2024-07-21 13:50:14', '2024-10-16 17:45:19', 1),
(30, 'Chicharrón de Pollo Ahumado', 'En salsa de sésamo y chinas.', 15.00, 'dish_images/DiYVNiHJt350sGrT0uAC6U1lfelpE4OBVkxuRA9q.jpg', 5, '2024-07-21 13:51:22', '2024-10-17 18:21:52', 1),
(31, 'Ceviche de Camarones y Almojabanas', 'Tiernas frituras elaboradas con harina de arroz que sirven de acompañante a nuestro ceviche de camarones y mango.', 16.00, 'dish_images/z2HiieAYENNBhhvKqcxTHim1t4RY1tcnKdS8v70O.jpg', 5, '2024-07-21 13:54:15', '2024-09-28 20:08:16', 1),
(32, 'Tapas del Chef', 'Una selección de cortes de queso y jamones.', 28.00, 'dish_images/b7cpdlttMcvJsKcesnJzth4RwpgK45Nc9apIwjum.jpg', 5, '2024-07-21 13:54:56', '2024-10-17 18:24:34', 1),
(33, 'Asador', 'Nuestra interpretación del Asador en 5 cortes asados, 10 camarones, 10oz de churrasco, 10oz de pechuga de pollo a la parrilla, 10oz de costillas ahumadas y 8oz de longaniza.', 95.00, 'dish_images/GqyXur9eBqgABHr0tMtZ7OtMllqBAE0hGetRo7DE.jpg', 5, '2024-07-21 13:56:09', '2024-11-23 21:09:37', 1),
(34, 'Ensalada del Chef', 'Spring mix, cebolla lila y zanahoria en vinagreta de la casa.', 12.00, 'dish_images/8W4ZeXVK8DzWo2pPWQW7GberDgDi4W2NavWJ64IS.png', 6, '2024-07-21 13:58:03', '2024-10-17 18:32:50', 1),
(35, 'Ensalada de Granos', 'Jugosos granos mixtos en aceite de oliva, vinagre, sal y pimienta.', 9.00, 'dish_images/yZVYoNHeeFV17In2SdMdH6LWROvJzYr7DMKWDh3x.jpg', 6, '2024-07-21 13:58:50', '2024-10-16 17:47:59', 1),
(36, 'Ensalada Caprese', 'Vinagre balsámico, aceite de oliva,sal, albahaca, tomate y mozzarella fresco.', 18.00, 'dish_images/vyl2Bo3qZy6loxuHRpDrHD2dPmpGZYxm7qryd8iH.png', 6, '2024-07-21 13:59:59', '2024-10-16 17:50:44', 1),
(37, 'Sopa del Dìa', 'Pregunta por la sopa del día', 7.00, 'dish_images/57Vl94iaQGUheIezFYlGFlhAw9TnYDhhMGkBqA2t.jpg', 7, '2024-07-21 14:01:00', '2024-10-17 18:37:14', 1),
(38, '1/2 Pollo Asado a la Brasa', 'Jugoso 1/2  pollo con hueso asado a la brasa bañada en salsa de miel.', 21.00, 'dish_images/OrlqMhujvNez8hyazPctsmEwuHnJWdbNm7UXpJsr.jpg', 8, '2024-07-21 14:02:10', '2024-07-21 14:56:44', 1),
(39, 'Pechuga Asador', 'Pechuga rellena de mortadella italiana y queso cheddar.  Enrollada en tocineta y arropada en salsa de setas, queso mascarpone, cebollas y licor de jerez.', 28.00, 'dish_images/kCOu60thMSYTvd3DQX7RJfg1SxsZ1BSugIlDkOzu.jpg', 8, '2024-07-21 14:02:43', '2024-09-28 20:11:39', 1),
(40, 'Milanesa de pollo', 'Jugosa pechuga empanada arropada en salsa de tomates pomodoros y queso mozarella gratinado.', 28.00, 'dish_images/y6k1DrSShOaNnpT3zgk0Mq260HVxQJ94ijL7e7Wv.jpg', 8, '2024-07-21 14:03:19', '2024-10-04 17:02:45', 1),
(41, 'Costillas', 'Corte St. Louis ahumadas en madera de manzana en salsa BBQ de la casa acompañadas con un sabroso pedazo de pan de maíz.', 29.00, 'dish_images/dglxxLPqkMUxsAsdV21R2XGEKAsAaAEI4CucsPBm.png', 9, '2024-07-21 14:04:47', '2024-10-16 17:56:25', 1),
(42, 'Hamburguesa Asador 8oz', 'Fresca carne de res picada a mano rellena de maduros, chorizo , tocineta con queso crema.  Acompáñala con tu queso de preferencia (provolone, suizo y cheddar).', 18.00, 'dish_images/rDEnwNWWNWVd9EVf32EA3tZKQmaghLoCtveCETeY.jpg', 10, '2024-07-21 14:05:48', '2024-10-04 17:12:15', 1),
(43, 'Churrasco 10oz', 'Encebollado con salsa de la casa sobre platina de hierro ardiente.', 33.00, 'dish_images/AP0wOU3VmdgstoJYP5obO0EDKDjcJNf3QeQu6I90.png', 10, '2024-07-21 14:06:27', '2024-09-28 21:33:53', 1),
(44, 'Mignon 10oz', 'Tierno filete de res con salsa de queso turco original del asador.', 36.00, 'dish_images/JhB8GYwRJhzSW2GZUPcUQJuJdf253FacAGQgIAxZ.jpg', 10, '2024-07-21 14:07:14', '2024-09-28 19:49:20', 1),
(45, 'Tomahawk', 'Aproximadamente 36oz de carne angus en salsa de mantequilla con hierbas y especias, cocido a la perfección en nuestra clásica parrilla al término deseado. Servido en su hueso natural y pimientos dulces.', 145.00, 'dish_images/PQlGI4126E2w3dKetgChE4DYPv9kaVwKFb1HbuVm.jpg', 10, '2024-07-21 14:08:01', '2024-07-21 15:09:22', 1),
(46, 'Salmón 8oz', 'Tierno y jugoso filete sellado a la plancha en un glaseado de mandarina y sésamos.', 23.00, 'dish_images/ilbHSse3W66iKnCd6PFoflPCcoORB6PQ8syune4Z.jpg', 11, '2024-07-21 14:09:35', '2024-07-21 14:56:02', 1),
(47, 'Dorado 8oz', 'Empanado clasico sobre coleslaw y salsa tartara.', 28.00, 'dish_images/PpC5i1fLqjvjcH7wQXI4x9Y6lFkXaBsuJ5kamQtM.png', 11, '2024-07-21 14:16:56', '2024-09-28 19:51:36', 1),
(48, 'Rabo de Langosta', 'Al termidor. Plato tipico de la cocina Francesa.', 96.00, 'dish_images/W8jJv5BBax3AlYPxGBffrMVRjwNe24X8PJbli1I4.png', 11, '2024-07-21 14:17:42', '2024-10-16 17:57:19', 1),
(49, 'Veggie Burger', 'Deliciosa hamburguesa de soya ahumada con pan de romero y tiritas de plátano con esencia de aceite de trufas.', 17.00, 'dish_images/0yXXE5jmbsoa1iCD2AXnvPOcfR1V2Aggj8X2atpY.png', 12, '2024-07-21 14:18:36', '2024-10-16 17:58:42', 1),
(50, 'Manjar Vegetariano', 'Melange de couscous y quinoa en salsa de crema liviana con vegetales de temporada, tomates frescos, alcachofas y papas horneadas en bechamel con esencia de trufa.', 21.00, 'dish_images/KdxODHHR0Pxk6pjJFVUdi2fNlRDBg7XsF7cmgMoT.jpg', 12, '2024-07-21 14:19:09', '2024-07-21 15:10:48', 1),
(51, 'Mac & Cheese', 'Añádele pechuga por $6.', 9.00, 'dish_images/1SHsVFnrSneQs3HgCGXaFYgI2BzTPOh0UTIp9d5m.jpg', 13, '2024-07-21 14:20:22', '2024-10-17 18:39:14', 1),
(52, 'Chicken Fingers', 'Pollo empanado con el sabor único de Asador San Miguel, acompañado de papas fritas. Cambios en acompañante $3.00', 12.00, 'dish_images/4TIC1eSJJt7ojRxqIpNRnfvj2NgNl7gQdb2LHPvV.png', 13, '2024-07-21 14:21:05', '2024-10-17 18:43:34', 1),
(53, 'Pechuga a la Parrilla', '4oz con papas fritas. Cambios en acompañante $3.00', 12.00, 'dish_images/MWOJONiiIAMrW2LGMs7SbFD6Dcrgb2Fzmyg88McM.png', 13, '2024-07-21 14:25:37', '2024-10-16 18:00:12', 1),
(54, 'Kids Burger', '4oz de carne con queso y papas fritas.', 12.00, 'dish_images/Ap33qDYFay4Zd7Q4SrWXiPeeVvN0HDoxM8AfiZwi.png', 13, '2024-07-21 14:29:34', '2024-10-16 18:00:59', 1),
(55, 'Papas Fritas', 'Papas Fritas', 4.50, 'dish_images/3LJrMUIsBIlQPEcQVG4iAfjzVIA1eD0H4o7gLNF6.png', 14, '2024-07-21 14:31:07', '2024-10-17 18:45:59', 1),
(56, 'Arroz Blanco con Habichuelas', 'Arroz Blanco con Habichuelas', 6.00, 'dish_images/gCithoYOPK1yPjotmoH7YD0WwDT3iPlgxGlK9ayu.png', 14, '2024-07-21 14:31:33', '2024-07-21 14:32:17', 1),
(57, 'Tostones Plátano', 'Tostones Plátano', 6.00, 'dish_images/jdougaDDNs5fFS8dLeISXnAVhRIoDx0k850uiyAd.jpg', 14, '2024-07-21 14:32:01', '2024-10-17 18:49:35', 1),
(58, 'Majado del Día', 'Majado del Día', 6.00, 'dish_images/UB4KnTuUSIGe23JcTciFNJnNi5YqATeq5RbczWYl.png', 14, '2024-07-21 14:33:01', '2024-07-21 14:33:07', 1),
(59, 'Mamposteao', 'Momposteao', 6.00, 'dish_images/9yEW1TY8qNQe4U8WHPoQwDNGeJKwutMHcjzigDZ8.png', 14, '2024-07-21 14:33:29', '2024-10-16 18:02:46', 1),
(60, 'Ensalada de Granos', 'Ensalada de Granos', 9.00, 'dish_images/MIJ3Xl2lQQetot6ac5O5Z6wAMDhKhmOxjeGFskLl.jpg', 14, '2024-07-21 14:33:57', '2024-10-16 18:04:05', 1),
(61, 'Papas Gratinadas', 'Papas Gratinadas', 7.00, 'dish_images/68xDw3b9Bg4VLbTFmF0DPWs0SxB2XPkwbKGohHJE.jpg', 14, '2024-07-21 14:35:32', '2024-10-17 18:56:57', 1),
(62, 'Amarillos', 'Amarillos', 7.00, 'dish_images/erCRtVHYE6eSgb3YkMlPwXZvPWGd1qY2eeEgfyie.jpg', 14, '2024-07-21 14:36:12', '2024-10-17 18:58:36', 1),
(63, 'Vegetales de Temporada', 'Vegetales de Temporada', 7.50, 'dish_images/2pLFHMJtD7ZGJ5mohHDtMOvCP3JlqzrttSKqFFNc.png', 14, '2024-07-21 14:36:49', '2024-07-21 14:37:03', 1),
(64, 'Ensalada del Chef', 'Ensalada del Chef', 8.00, 'dish_images/w6bUB0h7dPowhOElb0lz2aczWbmX6d2IiQoVRcWR.png', 14, '2024-07-21 14:37:35', '2024-10-17 18:59:09', 1),
(65, 'Risotto del Día', 'Risotto del Día', 8.00, 'dish_images/eiDfOeSKixutCvv8x8oLk6PoiFCNZup5CGWVA2mt.png', 14, '2024-07-21 14:38:17', '2024-10-16 18:06:51', 1),
(66, 'Papitas Fritas en Aceite de Trufas', 'Papitas Fritas en Aceite de Trufas', 9.00, 'dish_images/Xx6F1kWP2m9FQcmygFSxsrgDER2SyzSjAIXvZrZE.jpg', 14, '2024-07-21 14:39:35', '2024-07-21 15:04:23', 1),
(67, 'Pasta en Salsa de Queso Boursin', 'Pasta en Salsa de Queso Boursin', 13.00, 'dish_images/7hvqrxpxAtVuXOgDUzuQbUPAp3vzoXqfDj26teAg.jpg', 14, '2024-07-21 14:40:15', '2024-10-17 18:55:44', 1),
(68, 'Single Scoop de Gelato', 'Single Scoop de Gelato', 3.00, 'dish_images/zzlkbsqUdo1L9w8KijlKy3ZCPmSmb2otEbJA1TBo.png', 16, '2024-07-21 14:41:21', '2024-10-16 18:06:10', 1),
(69, 'Conchita de Coco', 'Gelato de coco en su cáscara.', 9.00, 'dish_images/TeGXUrSNPAVL12OqCpM6krU9gXUDbVDsrfpAzhkq.png', 16, '2024-07-21 14:42:41', '2024-09-28 19:56:30', 1),
(70, 'Brownie con gelato', 'Brownie con gelato', 9.00, 'dish_images/q8SgMQy8g9O9g73RmAKGSFS0KCIQPhjU2uQPmlnO.jpg', 16, '2024-07-21 14:43:10', '2024-09-28 20:05:28', 1),
(71, 'Cheesecake de Temporada', 'Pregunte por sabores disponibles.', 9.00, 'dish_images/biYyFMUReJSXO9ieMgkCrhMFbOYGdhPFkDZLi1Kq.png', 16, '2024-07-21 14:43:43', '2024-09-28 19:54:55', 1),
(72, 'Tres Leches', 'Tradicional torta de pastel bañado con la combinación de tres leches cubierto con merengue italiano flameado.', 9.00, 'dish_images/BPH83ccHJry9QXGWXFN2QhpHh7SR3jKZd39XZ3rA.jpg', 16, '2024-07-21 14:44:49', '2024-09-28 19:58:45', 1),
(73, 'Crema Quemada', 'Tipico postre francés (Crême Brûlée).', 9.00, 'dish_images/C3VbgI6EJdsyyabdBuBMmG7T4AgG1DOlUOmJ1d1g.jpg', 16, '2024-07-21 14:45:33', '2024-10-11 00:21:56', 1),
(74, 'Tortitas de Calabaza con Gelato', 'Tortitas de Calabaza con Gelato', 12.00, 'dish_images/UKmcwOZkFOtQujoC6NyYE4bOLwr0FZmdFZ0Kqz7l.png', 16, '2024-07-21 14:46:02', '2024-10-16 18:05:47', 1),
(75, 'Crème de la Crème', 'Helado de naranja, queso mascarpone y Aperol Spritz. Acompañado de caramelo y mermelada de naranjas en el tope.', 12.00, 'dish_images/a26nl4E9dj0XoJrpCY9BRTgJ0R3FdwElQQ2desOH.jpg', 16, '2024-07-21 14:46:37', '2025-03-30 19:26:10', 0),
(77, 'Jamón 5J', '100% Iberico. Etiqueta Negra. \r\nPrecio por onza.', 12.00, 'dish_images/YrHHshBrg1IKfnGcXCAVGxWFYBBTsCUxMfc0h5Me.jpg', 15, '2024-07-21 14:48:26', '2024-09-28 19:57:47', 1),
(78, 'Secreto Ibérico', 'Sabroso y jugoso corte de carne que se encuentra entre la paleta y la panceta del cerdo. Servido con Papas en salsa.', 89.00, 'dish_images/sy7yj6UnElkfnYmcAFkKx28u6WCMG0pvFPNfZG4B.png', 15, '2024-07-21 14:49:05', '2024-10-11 00:13:45', 1),
(79, 'New York Wagyu Steak (Japones)', '3/4 de libra cocido en sartén de hierro acompañado de papas con miel de trufas.', 146.25, 'dish_images/foaTUULPApL1CjgJuV2KroHE1FUPLD7OIQQL4RZN.png', 15, '2024-07-21 14:49:47', '2025-01-26 15:46:05', 1),
(84, 'Ceviche de Longaniza Asador', 'Longaniza de pollo o cerdo confeccionada y ahumada en el Asador.', 21.00, 'dish_images/uyCsIptbLGiRDUk5COlQsWPLc0E3uCGEbMXwbxDl.jpg', 5, '2024-10-04 20:10:21', '2024-11-02 03:58:16', 1),
(86, 'Tostones Pana', 'Tostones Pana', 6.00, 'dish_images/L39xNgm97TN5lUsLfOuxFF77lRNRx3hcAr1EsLQu.png', 14, '2024-10-17 18:52:28', '2024-10-17 18:53:09', 1),
(87, 'Papas Salteadas', 'Papas Salteadas', 8.00, 'dish_images/zLr8wMRsHs1zcrHxU5ejozfJSkfbKmIAxwKVUOku.png', 14, '2024-10-19 21:02:16', '2024-10-19 21:04:35', 1),
(88, 'Morcillas', 'Morcillas puertorriqueñas hechas con arroz, sangre de cerdo y una mezcla especial de sofrito criollo con toque picoso de nuestro pique ahumado hecho en casa.', 21.00, 'dish_images/VsCN4BCcPJZZYrJMAMjTd0CClrPuWpDm60Lt2f5T.jpg', 5, '2025-08-03 16:32:34', '2025-08-03 16:33:02', 1);

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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_05_01_170425_create_categories_table', 1),
(5, '2024_05_01_170425_create_dishes_table', 1),
(6, '2024_05_02_161951_sushi', 1),
(7, '2024_05_06_232958_add_relevance_to_categories_table', 1),
(8, '2024_05_08_175314_update_image_paths', 1),
(9, '2024_06_06_192829_create_cantina_items_table', 1),
(10, '2024_06_06_195740_add_image_to_cantina_items_table', 1),
(11, '2024_06_06_203620_create_cantina_categories_table', 1),
(12, '2024_06_06_225421_add_name_to_cantina_categories_table', 1),
(13, '2024_06_06_230839_add_category_id_to_cantina_items_table', 1),
(14, '2024_06_09_140119_create_cocktail_categories_table', 2),
(15, '2024_06_09_144240_create_cocktail_categories_table', 3),
(17, '2024_06_09_144241_create_cocktail_items_table', 4),
(18, '2024_06_10_145724_create_cocktail_categories_table', 5),
(19, '2024_06_10_145736_create_cocktails_table', 5),
(20, '2024_06_10_152006_create_wine_categories_table', 6),
(21, '2024_06_10_152006_create_wines_table', 6),
(23, '2024_06_10_175518_add_image_to_cocktails_table', 7),
(24, '2024_06_10_183503_create_settings_table', 8),
(25, '2024_06_10_191713_add_background_images_to_settings_table', 9),
(26, '2024_06_10_195512_add_style_settings_to_settings_table', 10),
(27, '2024_06_10_201832_add_card_opacity_to_settings_table', 11),
(28, '2024_06_10_202126_add_button_color_to_settings_table', 12),
(29, '2024_06_10_202928_add_view_settings_to_settings_table', 13),
(30, '2024_06_10_204657_add_view_settings_columns_to_settings_table', 14),
(31, '2024_06_11_154045_add_visible_to_cocktails_table', 15),
(32, '2024_06_11_154045_add_visible_to_dishes_table', 15),
(33, '2024_06_11_154045_add_visible_to_wines_table', 15),
(35, '2024_06_11_192051_add_logo_to_settings_table', 16),
(36, '2024_06_11_203829_add_socials_and_info_to_settings', 17),
(37, '2024_06_12_135939_add_category_styles_to_settings', 18),
(39, '2024_06_12_135939_add_category_styles_to_settings_table_new', 19),
(40, '2024_06_12_172726_add_card_background_colors_to_settings_table', 20),
(41, '2024_06_14_105034_add_button_font_size_cover_to_settings_table', 21),
(42, '2024_06_14_110831_add_font_size_and_color_to_fixed_bottom_info_in_settings_table', 22),
(43, '2024_06_14_105034_add_button_font_size_and_fixed_bottom_font_settings_to_settings_table', 23),
(44, '2024_06_16_173254_create_popups_table', 24),
(45, '2024_06_18_192540_add_repeat_days_to_popups_table', 25);

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

--
-- Dumping data for table `popups`
--

INSERT INTO `popups` (`id`, `title`, `image`, `view`, `start_date`, `end_date`, `active`, `created_at`, `updated_at`, `repeat_days`) VALUES
(36, '.', 'popup_images/lTVDnafDvCwpbTD9VGDmUUO9KZj4CDutAVZFBW3L.jpg', 'menu', '2025-10-03 00:00:00', '2025-11-01 00:00:00', 1, '2025-08-02 21:43:07', '2025-10-03 19:01:33', '5'),
(39, 'Postre del dia', 'popup_images/T6Zw4pJbh0DxWF9vREJrr3ZPZhf6rOpAxnrfFvHQ.png', 'menu', '2025-10-25 00:00:00', '2025-10-27 00:00:00', 1, '2025-10-24 20:37:19', '2025-10-24 23:05:12', NULL);

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
('0ftMkLTbduBqZPJn0vHlT2MQrBC0dWCBvODv5cND', NULL, '70.45.85.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNk5jU0h1bGpRTzNqWTRraGdueFJIdDdJSFp3VThqNzVheWZ4a3JraCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762081583),
('84MRFVL0hhzz4TOXM3PumR2fGevckFxonwrafZIl', NULL, '2606:6a40:69:30ff:7db2:d9aa:d4f2:d688', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFJ2ZUJJYTdnRUVpTEMxWkE5S2RpaGVjTW1JTjl3V1A3VGJEUmxxUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762085294),
('bFKoX3n5uluQ8ZbLm31vKIErz3oec7jEBqvPtXiH', NULL, '2607:fb90:cd92:7331:8dd0:330d:29f7:3955', 'WhatsApp/2.23.20.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZVRtUnMxVG5QMjJvZVI3SFRVaG9nVjA2N0ZjdllBQzJ1NWtvRUx0byI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1762088291),
('CDY8KYitpEoKTiTQqKW0ltTKQVWoZNtyOjjxex8v', NULL, '2607:fb90:cd9a:8896:15fe:3c0e:ba37:44ec', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiakgyRUVDN2lQVndaQTM4YnU2Qk9wQnIwWFM1SHFuWGF3SWZyZXNZZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762087930),
('GOl1KWOpCSacGhGfXmF24mBR3TtN8U8VkoruxRRI', NULL, '24.139.224.183', 'WhatsApp/2.23.20.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic3gxWHo2UVBHTFlOQkVHSU5xQ1F4YjlTN2MxU201Q3U0S0RRU3hMNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762085266),
('jDlFL58NwJvw15yZMwwT1NbVkPlMMZ2yUPA0kEPT', NULL, '2607:fb90:cd20:a349:d174:af6c:aa25:5550', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUxNMDBkd2NRV0xyd1BaYnkwdUR4UW1iRjY5TFF0NUFNZDJRdDVRRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762088317),
('K37QpeKrRf95pfb8GnF0BdBHlAD8oJCzgbyX6aVu', NULL, '24.139.146.5', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSW5CcVJHUmhXd29QcTVYRVBmdlQ5RVp5TGk2VTVQUzZCUG1WZGZ2byI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762087905),
('OfN9apPkToNp8rXKtiZBU2tFUWQIUUdXaZV6VLmT', NULL, '67.224.128.213', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHBieEdmOWZONHB3dE9nOGJBcVZac1lsTFFaaVdGNTg5NmlqdEZ6TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762084757),
('P7MPDKg7cYfy0UlY2xGb9VEZMgjAFlL1TUKzA8mu', NULL, '2a09:bac2:4fc9:2678::3d5:c', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUEdVbzJaVFhCQnFiS0hydjlvVWt6aGRNUFlUWXFWa0JvM0ZmdlR1ZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762086199),
('srW7GxFzhCDK2aeBLSU2IjefBynPukXfPG4piiNT', NULL, '2607:fb90:cd92:7331:8dd0:330d:29f7:3955', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.0.1 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSG1zOFJTOXdlSGJtN0JyVlVaVGxOYXNpWUFqdHAzdEZrRjBvYU5aeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762088312),
('vqNchdHxP2Q7vLMS8zhVdb5MFFKPprIBanAbFChU', NULL, '2a03:2880:f804:4c::', 'meta-externalagent/1.1 (+https://developers.facebook.com/docs/sharing/webmasters/crawler)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUkhsWTlKRHAyWDQ5ZE5nZ3A4bjNrVjg3ZExIOXhTd01pR1lLcnNTNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vd2luZXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1762083484),
('YWwpLNtOqopViMBigDcmAZgwatdNmQosJmzGZJ4u', NULL, '2600:387:a:7::39', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRmtTT3l1Y3NRWlc0Q2ROR0daazVPWENhT3VETHoxN2MxSDdUYzVZViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vbWVudS5hc2Fkb3JzYW5taWd1ZWxwci5jb20vbWVudSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1762087849);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `background_image_cover` varchar(255) DEFAULT NULL,
  `background_image_menu` varchar(255) DEFAULT NULL,
  `font_family` varchar(255) DEFAULT NULL,
  `text_color` varchar(255) DEFAULT NULL,
  `opacity` decimal(3,2) DEFAULT NULL,
  `card_opacity` decimal(3,2) DEFAULT NULL,
  `button_color` varchar(255) DEFAULT NULL,
  `background_image_cocktails` varchar(255) DEFAULT NULL,
  `background_image_wines` varchar(255) DEFAULT NULL,
  `text_color_cocktails` varchar(255) DEFAULT NULL,
  `text_color_wines` varchar(255) DEFAULT NULL,
  `font_family_cocktails` varchar(255) DEFAULT NULL,
  `font_family_wines` varchar(255) DEFAULT NULL,
  `card_opacity_cocktails` decimal(3,2) DEFAULT NULL,
  `card_opacity_wines` decimal(3,2) DEFAULT NULL,
  `button_color_cocktails` varchar(255) DEFAULT NULL,
  `button_color_wines` varchar(255) DEFAULT NULL,
  `text_color_cover` varchar(255) DEFAULT NULL,
  `text_color_menu` varchar(255) DEFAULT NULL,
  `card_opacity_cover` decimal(3,2) DEFAULT NULL,
  `card_opacity_menu` decimal(3,2) DEFAULT NULL,
  `font_family_cover` varchar(255) DEFAULT NULL,
  `font_family_menu` varchar(255) DEFAULT NULL,
  `button_color_cover` varchar(255) DEFAULT NULL,
  `button_font_size_cover` int(11) NOT NULL DEFAULT 18,
  `button_color_menu` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `business_hours` text DEFAULT NULL,
  `category_name_bg_color` varchar(255) DEFAULT NULL,
  `category_name_text_color` varchar(255) DEFAULT NULL,
  `category_name_font_size` int(11) DEFAULT NULL,
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
  `fixed_bottom_font_size` int(11) NOT NULL DEFAULT 14,
  `fixed_bottom_font_color` varchar(255) NOT NULL DEFAULT '#000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `background_image`, `created_at`, `updated_at`, `background_image_cover`, `background_image_menu`, `font_family`, `text_color`, `opacity`, `card_opacity`, `button_color`, `background_image_cocktails`, `background_image_wines`, `text_color_cocktails`, `text_color_wines`, `font_family_cocktails`, `font_family_wines`, `card_opacity_cocktails`, `card_opacity_wines`, `button_color_cocktails`, `button_color_wines`, `text_color_cover`, `text_color_menu`, `card_opacity_cover`, `card_opacity_menu`, `font_family_cover`, `font_family_menu`, `button_color_cover`, `button_font_size_cover`, `button_color_menu`, `logo`, `facebook_url`, `twitter_url`, `instagram_url`, `phone_number`, `business_hours`, `category_name_bg_color`, `category_name_text_color`, `category_name_font_size`, `category_name_bg_color_menu`, `category_name_text_color_menu`, `category_name_font_size_menu`, `category_name_bg_color_cocktails`, `category_name_text_color_cocktails`, `category_name_font_size_cocktails`, `category_name_bg_color_wines`, `category_name_text_color_wines`, `category_name_font_size_wines`, `card_bg_color_menu`, `card_bg_color_cocktails`, `card_bg_color_wines`, `fixed_bottom_font_size`, `fixed_bottom_font_color`) VALUES
(1, NULL, '2024-06-10 23:18:54', '2024-10-19 15:03:14', 'background_images/zLz9cFfQA3iKlAZ3vjL49fHNzgZfTFlU6t8Y0aay.jpg', 'background_images/fTOslD6MBcInlrPmuSQ07kPXOjXsYnk3KdlUi3Qp.jpg', 'Arial', '#b5a921', NULL, 1.00, '#532828', 'background_images/JvN6o3K7ocQLUVBF1bTzpN8JDtbTi432E8k2sJdH.jpg', 'background_images/HvTNl6aP76mOaSfKbF4Y3wgS3SMpQGNrLVND0gvU.jpg', '#000000', '#ffffff', NULL, NULL, 0.90, 0.90, '#ff6a00', '#ea9a3e', '#fcfcfc', '#000000', 1.00, 1.00, 'Arial', NULL, '#d55510', 20, '#000000', 'logos/vdpTsSiMlUQkCXUfis9jHs0Fs1UsCJt0b0Ox9nRd.png', 'https://www.facebook.com/asadorsmiguel', NULL, 'https://www.instagram.com/asadorsanmiguel?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==', '7879004682', 'Viernes y Sábado 12pm a 10pm\r\n\r\nDomingo 12pm a 8pm', NULL, NULL, NULL, '#d55510', '#ffffff', 20, '#f95b06', '#ffffff', 20, '#e96235', '#ffffff', 20, '#fffbb9', '#fffbb9', '#000000', 14, '#ffffff');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'carlo', 'cariver30@gmail.com', NULL, '$2y$12$dppHNF3HcOBabcDx1JI2yO9ZKFJPNWIjOnK2R7R1MEdJq0I5bp71K', NULL, '2024-06-19 03:12:02', '2024-06-19 03:12:02'),
(2, 'Menu User', 'menu@menu.com', NULL, '$2y$12$Yw.gw0R.HanLHu00dL1Fou4LVWGAgAfmTvSvzVtSnzS6au9mhD77u', 'MNIpcCvsSh5ugjL3VfNziJtaQmfgHvKbvuD8QMJQwP3IIWInooocBh3JbmZu', '2024-06-19 20:31:47', '2024-06-19 20:31:47');

-- --------------------------------------------------------

--
-- Table structure for table `wines`
--

CREATE TABLE `wines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wines`
--

INSERT INTO `wines` (`id`, `name`, `description`, `price`, `category_id`, `image`, `created_at`, `updated_at`, `visible`) VALUES
(70, 'Granbazán', 'Albariño 2023, Rías Baixas', 33.00, 7, NULL, '2025-09-25 14:53:25', '2025-09-25 14:53:25', 1),
(71, 'Granbazán', 'Albariño 2023, Rías Baixas', 33.00, 7, NULL, '2025-09-25 14:53:25', '2025-09-25 14:53:25', 1),
(72, 'Blanquito', 'Albariño 2023, Rías Baixas', 24.00, 7, NULL, '2025-09-25 14:54:28', '2025-09-25 14:54:28', 1),
(73, 'Blusa De REI', 'Albariño 2023, Rías Baixas', 28.00, 7, NULL, '2025-09-25 14:56:03', '2025-09-25 14:56:03', 1),
(74, 'Santiago Ruiz', 'Albariño 2023, Rías Baixas', 35.00, 7, NULL, '2025-09-25 14:57:27', '2025-09-25 14:57:27', 1),
(75, 'Pazo Barrantes', 'Albariño 2021, Rías Baixas', 63.00, 7, NULL, '2025-09-25 14:58:26', '2025-09-25 14:58:26', 1),
(76, 'Attis Lías Finas', 'Albariño 2023, Rías Baixas', 36.00, 7, NULL, '2025-09-25 15:00:41', '2025-09-25 15:00:41', 1),
(77, 'Attis Mar', 'Albariño 2023, Rías Baixas', 36.00, 7, NULL, '2025-09-25 15:01:34', '2025-09-25 15:01:34', 1),
(78, 'Raeburn', 'Chardonnay 2022, Sonoma County', 37.00, 7, NULL, '2025-09-25 15:06:42', '2025-09-25 15:06:42', 1),
(79, 'Maldonado', 'Chardonnay 2021, Sonoma County', 58.00, 7, NULL, '2025-09-25 15:07:55', '2025-09-25 15:07:55', 1),
(80, 'Maldonado', 'Chardonnay 2021, Sonoma County', 58.00, 7, NULL, '2025-09-25 15:07:55', '2025-09-25 15:07:55', 1),
(81, 'Justin', 'Chardonnay 2022, Sonoma County', 72.00, 7, NULL, '2025-09-25 15:08:31', '2025-09-25 15:08:31', 1),
(82, 'Hedonist', 'Chardonnay 2022, Sta Rita Hills, California.', 68.00, 7, NULL, '2025-09-25 15:09:16', '2025-09-25 15:09:16', 1),
(83, 'Landmark Vineyard', 'Chardonnay 2019, Sonoma County, California', 42.00, 7, NULL, '2025-09-25 15:10:24', '2025-09-25 15:10:24', 1),
(84, 'Montagny', 'Chardonnay 2022, Grand Vin De Bourgogne, Francia.', 56.00, 7, NULL, '2025-09-25 15:12:13', '2025-09-25 15:12:13', 1),
(85, 'Amelia', 'Chardonnay 2023, D.O. Valle del Limarí, Chile.', 65.00, 7, NULL, '2025-09-25 15:13:04', '2025-09-25 15:13:04', 1),
(86, 'Catena', 'Chardonnay 2022, Mendoza Argentina', 135.00, 7, NULL, '2025-09-25 15:13:57', '2025-09-25 15:13:57', 1),
(87, 'Cantina Zacagnini', 'Pinot Grigio 2023, S.p.A. Bolognano Pe Italia.', 27.00, 6, NULL, '2025-09-25 15:19:08', '2025-09-25 15:19:08', 1),
(90, 'Vino Dell Amicizia', 'Pinot Grigio 2023 Friuli, Italia.', 27.00, 7, NULL, '2025-09-25 16:34:42', '2025-09-25 16:34:42', 1),
(91, 'Valduero', 'Albillo, 2023, Ribera del Duero, España. Criado sobre Lías.', 42.00, 7, NULL, '2025-09-25 16:35:57', '2025-09-25 16:35:57', 1),
(92, 'Valduero', 'ALBILLO Mayor 2016, Reserva, Ribera del Duero, España.', 168.00, 7, NULL, '2025-09-25 16:37:08', '2025-09-25 16:37:08', 1),
(93, 'Valduero', 'Albillo Mayor 2015, Grand Reserva, Ribera del Duero, España.', 249.00, 7, NULL, '2025-09-25 16:38:06', '2025-09-25 16:38:06', 1),
(94, 'El Aeronauta', 'Godello 2021, S.A. Quintanilla de Onésimo, España.', 33.00, 7, NULL, '2025-09-25 16:38:53', '2025-09-25 16:38:53', 1),
(95, 'El Zarzal', 'Godello 2022, Bierzo España.', 28.00, 7, NULL, '2025-09-25 16:39:40', '2025-09-25 16:39:40', 1),
(97, 'OLuar Do Sil', 'Godello 2024, Valdeorras, España', 33.00, 7, NULL, '2025-09-25 16:43:40', '2025-09-25 16:43:40', 1),
(98, 'Emmolo', 'Saugvignion Blanc 2023, Fierfield, California.', 36.00, 6, NULL, '2025-09-25 16:45:35', '2025-09-25 16:45:35', 1),
(99, 'Emmolo', 'Saugvignion Blanc 2023, Fierfield, California.', 36.00, 7, NULL, '2025-09-25 16:46:08', '2025-09-25 16:46:08', 1),
(100, 'Impromptu', 'Sauvignion Blanc 2022, Requena, España.', 43.00, 7, NULL, '2025-09-25 16:47:09', '2025-09-25 16:47:09', 1),
(101, 'Raíces del Miño', 'Blend; Albariño, Treixadura, Godello, Loureira, 2019, Ribeiro, España.', 35.00, 7, NULL, '2025-09-25 16:48:57', '2025-09-25 16:48:57', 1),
(102, 'Cunqueiro', 'Blend; Treixadura, Godello, Albariño, Loureira, 2020, Ribeiro, España.', 62.00, 7, NULL, '2025-09-25 16:49:54', '2025-09-25 16:49:54', 1),
(103, 'Bico Amarelo', 'Blend; Loureira, Alvarinho, Avesso, Vino Verde, Portugal', 27.00, 7, NULL, '2025-09-25 16:51:11', '2025-09-25 16:51:11', 1),
(104, 'Pomares', 'Blend; Viosinho, Gouveio, Rabigato 2020, Douro, Portugal.', 25.00, 7, NULL, '2025-09-25 16:51:55', '2025-09-25 16:51:55', 1),
(105, 'Erath', 'Pinot Gris 2021, Oregón, USA.', 27.00, 7, NULL, '2025-09-25 16:52:54', '2025-09-25 16:52:54', 1),
(106, 'Rakrus', 'Kidonitsa, 2023, Grecia', 59.00, 7, NULL, '2025-09-25 16:53:47', '2025-09-25 16:53:47', 1),
(107, 'DR', 'Riesling 2021, Alemania', 26.00, 7, NULL, '2025-09-25 16:54:33', '2025-09-25 16:54:33', 1),
(108, 'Portar', 'Riesling 2019, Willimate Valley, Oregon.', 28.00, 7, NULL, '2025-09-25 16:55:11', '2025-09-25 16:55:11', 1),
(109, 'Portar', 'Riesling 2019, Willimate Valley, Oregon.', 28.00, 7, NULL, '2025-09-25 16:55:12', '2025-09-25 16:55:12', 1),
(110, 'Abadía de San Quirce', 'Verdejo 2023, Valladolid, España. Elavordo sobre Lías.', 29.00, 7, NULL, '2025-09-25 16:56:28', '2025-09-25 16:56:28', 1),
(111, 'Oremus Mandolás Vega Sicilia', 'Furmint 2021, Hungría.', 65.00, 7, NULL, '2025-09-25 16:58:12', '2025-09-25 16:58:12', 1),
(113, 'Notorious Pink', 'Garnacha 2023, Francia', 28.00, 8, NULL, '2025-09-25 17:02:05', '2025-09-25 17:02:05', 1),
(114, 'Miraval', 'Garnacha y Syrah 2022, Provence, Francia.', 35.00, 8, NULL, '2025-09-25 17:02:55', '2025-09-25 17:02:55', 1),
(115, 'Belle Gloss', 'Pinot Noir 2023, Sonoma County California', 38.00, 8, NULL, '2025-09-25 17:09:58', '2025-09-25 17:09:58', 1),
(116, 'Maldonado', 'Pinot Noir 2019, Napa Valley California.', 38.00, 8, NULL, '2025-09-25 17:11:32', '2025-09-25 17:11:32', 1),
(117, 'Hampton Water', 'Garnacha, Cinsault, Mourvedre, Syrah 2023, Francia.', 32.00, 8, NULL, '2025-09-25 17:13:55', '2025-09-25 17:13:55', 1),
(118, 'Fabre en Provence', 'Garnacha 2022, Francia.', 27.00, 8, NULL, '2025-09-25 17:15:06', '2025-09-25 17:15:06', 1),
(119, 'Coré', 'Negroamaro 2022, Coppi, Italia.', 29.00, 8, NULL, '2025-09-25 17:15:59', '2025-09-25 17:15:59', 1),
(120, 'Sables de AZUR', '2023 Cotes de Provence, Francia', 27.00, 8, NULL, '2025-09-25 17:17:05', '2025-09-25 17:17:05', 1),
(121, 'Olivia', 'Rías Baixas 2021, España.', 32.00, 8, NULL, '2025-09-25 17:17:52', '2025-09-25 17:17:52', 1),
(122, 'Cristal Luis Roeder', 'Champange 2015, Francia.', 650.00, 6, NULL, '2025-09-25 17:33:48', '2025-09-25 17:33:48', 1),
(123, 'Billecart-Salmón', 'Champange, Le Reserve, Francia', 85.00, 6, NULL, '2025-09-25 17:34:46', '2025-09-25 17:34:46', 1),
(124, 'G.H.Mumm', 'Champange, Brut, Francia.', 69.00, 6, NULL, '2025-09-25 17:36:17', '2025-09-25 17:36:17', 1),
(125, 'Don Perignon', 'Champange Vintage 2015, Brut, Francia.', 460.00, 6, NULL, '2025-09-25 17:36:55', '2025-09-25 17:36:55', 1),
(126, 'Rare', 'Champange Millésime 2013, Brut, Francia.', 290.00, 6, NULL, '2025-09-25 17:37:53', '2025-09-25 17:37:53', 1),
(127, 'Perdieron Jouet', 'Champange 2014, Belle Epoque, Brut, Francia.', 350.00, 6, NULL, '2025-09-25 17:38:44', '2025-09-25 17:38:44', 1),
(128, 'Laurent Perrier Grand Siecle', 'Champange, brut, Francia.', 369.00, 6, NULL, '2025-09-25 17:40:01', '2025-09-25 17:40:01', 1),
(129, 'Louis Roderer', 'Champange, Collection, Brut, Francia', 114.00, 6, NULL, '2025-09-25 17:40:55', '2025-09-25 17:40:55', 1),
(130, 'Moët & Chandon', 'Champange, Imperial brut, Francia', 65.00, 6, NULL, '2025-09-25 17:41:55', '2025-09-25 17:41:55', 1),
(131, 'Charles Heidsieck', 'Champange Brut Reserve, Francia', 80.00, 6, NULL, '2025-09-25 17:42:59', '2025-09-25 17:42:59', 1),
(132, 'Philipponnat', 'Champange Brut, Francia.', 120.00, 6, NULL, '2025-09-25 17:43:46', '2025-09-25 17:43:46', 1),
(133, 'Pipetas Heidsick', 'Champange Cuvee Brut, Francia.', 62.00, 6, NULL, '2025-09-25 17:44:29', '2025-09-25 17:44:29', 1),
(134, 'Joseph Perrier', 'Champange Brut Nature, Francia.', 65.00, 6, NULL, '2025-09-25 17:47:05', '2025-09-25 17:47:05', 1),
(135, 'Laurent Perrier', 'Champange Brut, Francia', 70.00, 6, NULL, '2025-09-25 17:47:40', '2025-09-25 17:47:40', 1),
(136, 'Piper Heidsick', 'Champange Brut Riviera, Francia.', 63.00, 6, NULL, '2025-09-25 17:48:47', '2025-09-25 17:48:47', 1),
(137, 'Ruinart', 'Champange Blanc de Blancs Brut Reims, Francia.', 139.00, 6, NULL, '2025-09-25 17:49:43', '2025-09-25 17:49:43', 1),
(138, 'Louis Roderer', 'Champange Blanc de Blancs 2015, Brut, Francia.', 159.00, 6, NULL, '2025-09-25 17:50:45', '2025-09-25 17:50:45', 1),
(139, 'Moët & Chandon', 'Champange Rosé Imperial, Brut, Francia.', 75.00, 6, NULL, '2025-09-25 17:52:12', '2025-09-25 17:52:12', 1),
(140, 'Veguero Clicquot', 'Champange Rosé Brut, Francia.', 97.00, 6, NULL, '2025-09-25 17:53:04', '2025-09-25 17:53:04', 1),
(141, 'Laurent Perrier', 'Champange Rosé Brut, Francia.', 95.00, 6, NULL, '2025-09-25 17:55:00', '2025-09-25 17:55:00', 1),
(142, 'Moët & Chandon', 'Champange Rosé Néctar Imperial, Francia.', 95.00, 6, NULL, '2025-09-25 17:55:44', '2025-09-25 17:55:44', 1),
(143, 'Bohigas', 'Cava Brut Nature, Grand Reserva, España.', 29.00, 6, NULL, '2025-09-25 17:56:23', '2025-09-25 17:56:23', 1),
(144, 'Gran Barón', 'Cava Brut, España', 25.00, 6, NULL, '2025-09-25 17:57:59', '2025-09-25 17:57:59', 1),
(145, 'Marta', 'Cava Brut, España', 29.00, 6, NULL, '2025-09-25 17:58:39', '2025-09-25 17:58:39', 1),
(146, 'Anna de Codorníu', 'Cava Brut, Blanc de Blancs', 29.00, 6, NULL, '2025-09-25 17:59:25', '2025-09-25 17:59:25', 1),
(147, 'Tamtum Ergo', 'Cava Pinot Noir, España', 55.00, 6, NULL, '2025-09-25 18:00:19', '2025-09-25 18:00:19', 1),
(148, 'Ruggeri', 'Prosecco Doc, Argeo, Italia.', 35.00, 6, NULL, '2025-09-25 18:03:43', '2025-09-25 18:03:43', 1),
(149, 'Bitters', 'Prosecco Doc Gold, Italia.', 28.00, 6, NULL, '2025-09-25 18:05:07', '2025-09-25 18:05:07', 1),
(150, 'Cuvee Aurora', 'Prosecco Doc Gold, Italia.', 70.00, 6, NULL, '2025-09-25 18:05:51', '2025-09-25 18:05:51', 1),
(151, 'Val D´ Oca Valdobbiadene', 'Prosecco Superiore 2023, Italia.', 29.00, 6, NULL, '2025-09-25 18:07:10', '2025-09-25 18:07:10', 1),
(152, 'Rosa Regale', 'Prosecco Rosé 2022, Italia.', 35.00, 6, NULL, '2025-09-25 18:07:57', '2025-09-25 18:07:57', 1),
(153, '1928 Rosé', 'Prosecco Rosé, Italia', 28.00, 6, NULL, '2025-09-25 18:08:47', '2025-09-25 18:08:47', 1),
(154, '1928 Prosecco', 'Prosecco, Italia.', 28.00, 6, NULL, '2025-09-25 18:09:55', '2025-09-25 18:09:55', 1),
(156, 'El Molino', 'Pinot Noir 2018, Rutherford, Napa Valley, California.', 98.00, 9, NULL, '2025-09-25 18:34:50', '2025-09-25 18:34:50', 1),
(157, 'Broadley Vineyards', 'Pinot Noir 2022, Willimette Valley, Oregon.', 38.00, 9, NULL, '2025-09-25 18:35:47', '2025-09-25 18:35:47', 1),
(158, 'Elizabeth Ipencer', 'Pinot Noir, 2021, Sonoma County, California', 52.00, 9, NULL, '2025-09-25 18:36:27', '2025-09-25 18:36:27', 1),
(159, 'Archery Summit', 'Pinot Noir 2021, Willimette Valley, Oregón.', 98.00, 9, NULL, '2025-09-25 18:37:35', '2025-09-25 18:37:35', 1),
(160, 'Belle Gloss', 'Pinot Noir 2022, Monterey County, California.', 56.00, 9, NULL, '2025-09-25 18:43:24', '2025-09-25 18:43:24', 1),
(161, 'Landmark Vineyard', 'Overlook Pinot Noir 2018', 42.00, 9, NULL, '2025-09-25 18:44:27', '2025-09-25 18:44:27', 1),
(162, 'Miura', 'Pinot Noir 2022, Monterey County, California.', 45.00, 9, NULL, '2025-09-25 18:45:00', '2025-09-25 18:45:00', 1),
(163, 'Amelia', 'Pinot Noir 2022, Valle del Limarí, Chile.', 65.00, 9, NULL, '2025-09-25 18:45:34', '2025-09-25 18:45:34', 1),
(164, 'Bassus', 'Pinot Noir 2021, Requena España', 43.00, 9, NULL, '2025-09-25 18:46:37', '2025-09-25 18:46:37', 1),
(165, 'Vino Dell´ Amicizia', 'Pinot Noir 2023,  Veneto', 27.00, 9, NULL, '2025-09-25 18:47:54', '2025-09-25 18:47:54', 1),
(166, 'Landmark Vineyard', 'Hoy Kilm Estate Pinot Noir 2018, River Valley California.', 2018.00, 9, NULL, '2025-09-25 18:48:52', '2025-09-25 18:48:52', 1),
(167, 'Donum', 'Pinot Noir 2019, Carneros, Sonoma County California.', 125.00, 9, NULL, '2025-09-25 18:49:53', '2025-09-25 18:49:53', 1),
(168, 'Balade', 'Pinot Noir 2023, Monterey County, California.', 45.00, 9, NULL, '2025-09-25 18:50:30', '2025-09-25 18:50:30', 1),
(169, 'La Linterna', 'Pinot Noir 2016, Los Árboles Mendoza, Argentina.', 89.00, 9, NULL, '2025-09-25 18:51:13', '2025-09-25 18:51:13', 1),
(170, 'Valmoissine', 'Pinot Noir 2022, San Rafael California.', 35.00, 9, NULL, '2025-09-25 18:53:12', '2025-09-25 18:53:12', 1),
(171, 'La Jota Vineyard', 'MERLOT 2018, Howell Mountain, Napa Valley, California.', 122.00, 9, NULL, '2025-09-25 18:55:45', '2025-09-25 18:55:45', 1),
(172, 'Woodward Canyon', 'MERLOT 2020, Walla Walla Valley, Washington.', 85.00, 9, NULL, '2025-09-25 18:56:14', '2025-09-25 18:56:14', 1),
(173, 'Emmolo', 'Merlot 2022, Napa Valley California', 69.00, 9, NULL, '2025-09-25 18:56:58', '2025-09-25 18:56:58', 1),
(174, 'Noble Vines 181', 'Merlot 2022, Lodi, California', 28.00, 9, NULL, '2025-09-25 18:57:47', '2025-09-25 18:57:47', 1),
(175, 'Spellbound', 'Merlot 2019, Napa Valley, California', 25.00, 9, NULL, '2025-09-25 18:59:16', '2025-09-25 18:59:16', 1),
(176, 'Lupa', 'Malbec 2019, Paraje Altamira, Argentina', 50.00, 9, NULL, '2025-09-25 19:00:01', '2025-09-25 19:00:01', 1),
(177, 'Judas', 'Malbec 2018, Mendoza, Argentina', 80.00, 9, NULL, '2025-09-25 19:00:37', '2025-09-25 19:00:37', 1),
(178, 'La Flor', 'Malbec 2022, Mendoza, Argentina.', 26.00, 9, NULL, '2025-09-25 19:01:09', '2025-09-25 19:01:09', 1),
(179, 'El Enemigo', 'Malbec 2020, Mendoza Argentina', 36.00, 9, NULL, '2025-09-25 19:01:50', '2025-09-25 19:01:50', 1),
(180, 'Proyecto Las Compuertas', 'Malbec 2022, Mendoza Argentina', 42.00, 9, NULL, '2025-09-25 19:02:32', '2025-09-25 19:02:32', 1),
(181, 'Alpasión', 'Gran Malbec 2021, Mendoza Argentina', 43.00, 9, NULL, '2025-09-25 19:03:19', '2025-09-25 19:03:19', 1),
(182, 'Hisenda Miret', 'Garnacha 2019, Pares Balta, Españas', 55.00, 9, NULL, '2025-09-25 19:04:06', '2025-09-25 19:04:06', 1),
(183, 'Izadi', 'Garnacha 2020, Rioja, España.', 45.00, 9, NULL, '2025-09-25 19:08:48', '2025-09-25 19:08:48', 1),
(184, 'Lignum', 'Blend Frappato, Syrah 2022, Italia.', 45.00, 9, NULL, '2025-09-25 19:09:43', '2025-09-25 19:09:43', 1),
(185, 'Edizione', 'Blend Montepulciano, Malvasía Negra, Primitivo, Sanguivese, Negroamaro 2020, Ortona, Italia.', 45.00, 9, NULL, '2025-09-25 19:10:27', '2025-09-25 19:10:27', 1),
(186, 'Conte Giangirolamo', 'Blend Primitivo, Negroamaro 2018, Italia.', 45.00, 9, NULL, '2025-09-25 19:11:20', '2025-09-25 19:11:20', 1),
(187, 'Podernouvo', 'Blend Cabernet Sauvignion, Merlot, Sangiovese, 2022, Toscana, Italia.', 33.00, 9, NULL, '2025-09-25 19:12:08', '2025-09-25 19:12:08', 1),
(188, 'Felipe Staiti', 'Blend Malbec, Cabernet Franc, Cabernet Sauvignion 2019, Mendoza, Argentina.', 75.00, 9, NULL, '2025-09-25 19:12:57', '2025-09-25 19:12:57', 1),
(189, 'Del Fin del Mundo', 'Blend Malbec, Cabernet Sauvignion, Merlot 2021, Patagonia Argentina.', 45.00, 9, NULL, '2025-09-25 19:14:01', '2025-09-25 19:14:01', 1),
(190, 'Lote Negro', 'Blend Malbec, Cabernet Franc 2022, Mendoza Argentina', 40.00, 9, NULL, '2025-09-25 19:15:38', '2025-09-25 19:15:38', 1),
(191, 'Dehesa del Carrizal', 'Blend Cabernet Sauvignion, Petit Verdot, Syrah 2019, España', 39.00, 9, NULL, '2025-09-25 19:16:24', '2025-09-25 19:16:24', 1),
(192, 'Vintage Claret', 'Blend Cabernet Sauvignion, Cabernet Franc, Merlot, Burdeos, Francia.', 35.00, 9, NULL, '2025-09-25 19:18:58', '2025-09-25 19:18:58', 1),
(193, 'Du Cartillon', 'Blend Cabernet, Merlot, Petit Verdot, Cabernet Franc, Haut Medoc Red 2021 Francia', 69.00, 9, NULL, '2025-09-25 19:20:44', '2025-09-25 19:20:44', 1),
(194, 'Brunello di Montalcino', 'Sangiovese 2019, Brunello de Montalcino.', 70.00, 9, NULL, '2025-09-25 19:21:40', '2025-09-25 19:21:40', 1),
(195, 'Lorenzo', 'Sangiovese 2018, Chianti Classico, Italia.', 69.00, 9, NULL, '2025-09-25 19:22:15', '2025-09-25 19:22:15', 1),
(196, 'Pietro', 'Sangiovese 2019, Barberio, Italia.', 99.00, 9, NULL, '2025-09-25 19:22:55', '2025-09-25 19:22:55', 1),
(197, 'Bocelli', 'Sangiovese 2020, Toscana, Italia.', 35.00, 9, NULL, '2025-09-25 19:23:40', '2025-09-25 19:23:40', 1),
(198, 'Bocelli Alcide', 'Sangiovese 2014, Rosso Toscana, Italia.', 85.00, 9, NULL, '2025-09-25 19:24:46', '2025-09-25 19:24:46', 1),
(199, 'Bocelli Terre Di Sandro', 'Sangiovese 2015, Rosso Toscana, Italia.', 95.00, 9, NULL, '2025-09-25 19:25:31', '2025-09-25 19:25:31', 1),
(200, 'Marcarini', 'Nebbiolo 2019, Barolo, Italia.', 78.00, 9, NULL, '2025-09-25 19:26:13', '2025-09-25 19:26:13', 1),
(201, 'Albe', 'Nebbiolo 2020, Barolo, Italia.', 68.00, 9, NULL, '2025-09-25 19:26:42', '2025-09-25 19:26:42', 1),
(202, 'Monti', 'Nebbiolo 2018, Barolo, Italia.', 89.00, 9, NULL, '2025-09-25 19:27:41', '2025-09-25 19:27:41', 1),
(203, 'GD VAJRA', 'Barbera 2020 Barbera, Italia.', 62.00, 9, NULL, '2025-09-25 19:28:59', '2025-09-25 19:28:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wine_categories`
--

CREATE TABLE `wine_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wine_categories`
--

INSERT INTO `wine_categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(6, 'ESPUMOSOS', '2024-08-02 19:09:35', '2024-08-02 19:09:35'),
(7, 'BLANCOS', '2024-08-02 19:09:56', '2024-08-02 19:09:56'),
(8, 'ROSADOS', '2024-08-02 19:10:16', '2024-08-02 19:10:16'),
(9, 'TINTOS', '2024-08-02 19:10:30', '2024-08-02 19:10:30');

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
-- Indexes for table `cocktails`
--
ALTER TABLE `cocktails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cocktails_category_id_foreign` (`category_id`);

--
-- Indexes for table `cocktail_categories`
--
ALTER TABLE `cocktail_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dishes_category_id_foreign` (`category_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `popups`
--
ALTER TABLE `popups`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `wines`
--
ALTER TABLE `wines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wines_category_id_foreign` (`category_id`);

--
-- Indexes for table `wine_categories`
--
ALTER TABLE `wine_categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cantina_categories`
--
ALTER TABLE `cantina_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cantina_items`
--
ALTER TABLE `cantina_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `cocktails`
--
ALTER TABLE `cocktails`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `cocktail_categories`
--
ALTER TABLE `cocktail_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `popups`
--
ALTER TABLE `popups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wines`
--
ALTER TABLE `wines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `wine_categories`
--
ALTER TABLE `wine_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cantina_items`
--
ALTER TABLE `cantina_items`
  ADD CONSTRAINT `cantina_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `cantina_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cocktails`
--
ALTER TABLE `cocktails`
  ADD CONSTRAINT `cocktails_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `cocktail_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wines`
--
ALTER TABLE `wines`
  ADD CONSTRAINT `wines_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `wine_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
