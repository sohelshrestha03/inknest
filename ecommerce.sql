-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 06:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `username`, `email`, `phone_no`, `password`) VALUES
(1, 'Manjeet', 'Dongol', 'admin', 'manjeetadmin@gmail.com', '981111110081', '$2y$10$90ENXHaxQmE7gGbEKCPBNufXFXMfjjaLEJzGTQ3.7GQ8cm.kUHzuK');

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 'Open', '2026-09-12 12:39:57', '2026-09-12 12:40:30'),
(3, 2, 'Open', '2026-09-12 12:42:06', '2026-09-12 12:42:39');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_type`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(3, 2, 'user', 1, 'hi', 1, '2026-09-12 12:40:00'),
(4, 2, 'admin', 1, 'How can I help you?', 1, '2026-09-12 12:40:30'),
(5, 3, 'user', 2, 'Hlo', 1, '2026-09-12 12:42:09'),
(6, 3, 'admin', 1, 'How can I help you?', 1, '2026-09-12 12:42:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('esewa','khalti','cash') NOT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'Pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `delivery_address` text NOT NULL,
  `stock_deducted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `order_date`, `payment_method`, `payment_status`, `transaction_id`, `email`, `phone`, `delivery_address`, `stock_deducted`) VALUES
(64, 1, 1400.00, 'Delivered', '2026-09-11 07:31:51', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 0),
(65, 2, 4900.00, 'Delivered', '2026-09-11 12:49:27', 'cash', 'Paid', NULL, 'paudelabhishek67@gmail.com', '9768432487', 'Bhode-04, Bhaktapur', 0),
(66, 1, 6100.00, 'Delivered', '2026-09-11 16:02:03', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 0),
(67, 1, 2800.00, 'Delivered', '2026-09-11 16:12:37', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 0),
(68, 1, 5500.00, 'Delivered', '2026-09-11 16:19:34', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 0),
(69, 1, 3000.00, 'Delivered', '2026-09-11 16:45:16', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 0),
(72, 2, 6100.00, 'Delivered', '2026-09-13 05:52:44', 'esewa', 'Paid', 'INK-72-1789278764', 'paudelabhishek67@gmail.com', '9768432487', 'Bhode-4, Bhaktapur', 0),
(73, 2, 6100.00, 'Delivered', '2026-09-13 05:54:52', 'esewa', 'Paid', 'INK-73-1789278892', 'paudelabhishek67@gmail.com', '9768432487', 'Bhode-4, Bhaktapur', 0),
(74, 2, 6100.00, 'Delivered', '2026-09-13 05:57:13', 'esewa', 'Paid', 'INK-74-1789279033', 'paudelabhishek67@gmail.com', '9768432487', 'Bhode-4, Bhaktapur', 0),
(76, NULL, 7300.00, 'Pending', '2026-09-13 15:55:22', 'cash', 'Pending', NULL, 'sosukeaizen308@gmmail.com', '9810103478', 'Balkhu-07, Kathmandu', 1),
(78, NULL, 4100.00, 'Pending', '2026-09-14 04:25:28', 'cash', 'Pending', NULL, 'sosukeaizen308@gmail.com', '9811672390', 'Nakhu-05, Lalitpur', 1),
(79, 1, 7400.00, 'Delivered', '2026-09-14 06:01:11', 'esewa', 'Paid', 'INK-79-1789365671', 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 0),
(80, 1, 5100.00, 'Delivered', '2026-09-14 06:42:46', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14,Lalitpur', 1),
(81, 1, 2600.00, 'Cancelled', '2026-09-14 06:50:23', 'cash', 'Refunded', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 1),
(82, 1, 2600.00, 'Delivered', '2026-09-14 07:11:08', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, Lalitpur', 1),
(83, 2, 7700.00, 'Delivered', '2026-09-15 04:59:44', 'cash', 'Paid', NULL, 'paudelabhishek67@gmail.com', '9768432487', 'Bhode-04, Lalitpur', 1),
(84, 8, 4200.00, 'Delivered', '2026-09-15 05:04:01', 'cash', 'Paid', NULL, 'trioasr@gmail.com', '9768234288', 'Dholahity-07, Lalitpur', 1),
(85, 8, 5100.00, 'Delivered', '2026-09-15 05:05:31', 'cash', 'Paid', NULL, 'trioasr@gmail.com', '9768234288', 'Dholahity-07, Lalitpur', 1),
(86, 16, 4700.00, 'Delivered', '2026-09-15 05:24:27', 'cash', 'Paid', NULL, 'sosukeaizen308@gmail.com', '9811456790', 'Kuleshower-13, Kathmandu', 1),
(87, 17, 12100.00, 'Delivered', '2026-09-15 05:30:27', 'cash', 'Paid', NULL, 'gotaku679@gmail.com', '9768223170', 'Thamel-07, Kathmandu', 1),
(88, 1, 2600.00, 'Delivered', '2026-09-16 06:57:30', 'khalti', 'Paid', '3KMg9prRkL4ChfFxM9AXqu', 'shrestha.sohel123@gmail.com', '9869223167', 'Nakhipot-14, Lalitpur', 0),
(89, 1, 2600.00, 'Delivered', '2026-09-16 07:29:13', 'khalti', 'Paid', 'g85d3jt5HigpB73L7SpeGz', 'shrestha.sohel123@gmail.com', '9869223167', 'Nakhipot-14, Lalitpur', 0),
(90, 1, 940.00, 'Delivered', '2026-09-16 07:59:37', 'khalti', 'Paid', 'CfQ3DZ2sxQafEBgxRujEPy', 'shrestha.sohel123@gmail.com', '9869223167', 'Nakhipot-14, Lalitpur', 1),
(91, 1, 43800.00, 'Delivered', '2026-09-16 08:10:54', 'cash', 'Paid', NULL, 'shrestha.sohel123@gmail.com', '9869223167', 'Nakhipot-14, lalitpur', 1),
(92, 18, 5150.00, 'Delivered', '2026-09-17 07:47:00', 'cash', 'Paid', NULL, 'gojousaturo83@gmail.com', '9756345670', 'Thimi-09 Bhaktapur', 1),
(93, 19, 8110.00, 'Delivered', '2026-09-17 07:52:02', 'cash', 'Paid', NULL, 'genji.takiya3540@gmail.com', '9811904567', 'Chaysal-09, Lalitpur', 1),
(94, 19, 10720.00, 'Delivered', '2026-09-19 16:30:25', 'cash', 'Paid', NULL, 'genji.takiya3540@gmail.com', '9768432488', 'Sanepa-07, Lalitpur', 1),
(95, 20, 4000.00, 'Delivered', '2026-09-19 16:31:51', 'cash', 'Paid', NULL, 'sohelshrestha03@gmail.com', '9768432488', 'Thecho- 07, Lalitpur', 1),
(96, 18, 4300.00, 'Delivered', '2026-09-19 16:33:16', 'cash', 'Paid', NULL, 'gotaku679@gmail.com', '9768223170', 'Dholahity-07, Lalitpur', 1),
(97, 1, 3300.00, 'Pending', '2026-09-28 10:32:33', 'esewa', 'Pending', 'INK-97-1790591553', 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, lalitpur', 0),
(98, 1, 3300.00, 'Pending', '2026-09-28 10:32:53', 'khalti', 'Pending', 'b6EqqgAzBQzZoSactoBe9N', 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, lalitpur', 0),
(99, 1, 4350.00, 'Pending', '2026-09-30 02:38:32', 'khalti', 'Pending', NULL, 'paudelabhishek67@gmail.com', '9768432487', 'lalitpur, mahalaxmi', 0),
(100, 1, 4350.00, 'Delivered', '2026-09-30 02:39:20', 'khalti', 'Paid', 'DBLVjhdcSdhcvM7fuQYeVJ', 'paudelabhishek67@gmail.com', '9768432487', 'lalitpur, mahalaxmi', 1),
(101, 1, 2020.00, 'Pending', '2026-09-30 03:42:55', 'khalti', 'Paid', '2HUzRWFror6FjoEvcEVLsP', 'shrestha.sohel123@gmail.com', '9768432488', 'Nakhipot-14, nLaitpur', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(70, 64, 7, 1, 1300.00),
(71, 65, 6, 1, 2500.00),
(72, 65, 8, 1, 2300.00),
(73, 66, 10, 2, 3000.00),
(74, 67, 5, 1, 2700.00),
(75, 68, 5, 2, 2700.00),
(76, 69, 9, 1, 2900.00),
(79, 72, 10, 2, 3000.00),
(80, 73, 10, 2, 3000.00),
(81, 74, 10, 2, 3000.00),
(85, 76, 7, 1, 1300.00),
(86, 76, 10, 1, 3000.00),
(87, 76, 9, 1, 2900.00),
(89, 78, 8, 1, 2300.00),
(90, 78, 11, 1, 1700.00),
(91, 79, 6, 2, 2500.00),
(92, 79, 8, 1, 2300.00),
(95, 82, 15, 1, 2500.00),
(96, 83, 5, 1, 2700.00),
(97, 83, 6, 1, 2500.00),
(98, 83, 4, 1, 2400.00),
(99, 84, 4, 1, 2400.00),
(100, 84, 11, 1, 1700.00),
(101, 85, 20, 2, 2500.00),
(102, 86, 22, 2, 600.00),
(103, 86, 11, 2, 1700.00),
(104, 87, 10, 2, 3000.00),
(105, 87, 18, 2, 2700.00),
(106, 87, 22, 1, 600.00),
(107, 88, 17, 1, 2500.00),
(108, 89, 20, 1, 2500.00),
(109, 90, 40, 1, 840.00),
(110, 91, 11, 1, 1700.00),
(111, 91, 21, 1, 42000.00),
(112, 92, 37, 1, 2550.00),
(113, 92, 20, 1, 2500.00),
(114, 93, 28, 1, 1250.00),
(115, 93, 27, 2, 2000.00),
(116, 93, 42, 2, 960.00),
(117, 93, 40, 1, 840.00),
(118, 94, 49, 1, 4250.00),
(119, 94, 50, 1, 3200.00),
(120, 94, 48, 1, 3170.00),
(121, 95, 27, 1, 2000.00),
(122, 95, 28, 1, 1250.00),
(123, 95, 34, 1, 650.00),
(124, 96, 40, 5, 840.00),
(125, 97, 50, 1, 3200.00),
(126, 98, 50, 1, 3200.00),
(127, 99, 49, 1, 4250.00),
(128, 100, 49, 1, 4250.00),
(129, 101, 42, 2, 960.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_otps`
--

CREATE TABLE `password_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `purpose` varchar(50) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_otps`
--

INSERT INTO `password_otps` (`id`, `user_id`, `email`, `otp_hash`, `purpose`, `expires_at`, `attempts`, `verified`, `created_at`) VALUES
(5, 6, 'sohelshrestha03@gmail.com', '$2y$10$Ruj8MtYbYm0XxqYXzwF4MebNZ.nrZSrtKQYnHuS0U7Ol8ymKLXxcW', 'change_password', '2026-09-12 18:55:07', 0, 1, '2026-09-12 22:30:07'),
(9, NULL, 'dummy_account@gmail.com', '$2y$10$rTZquyIn1BL8H5Fpaa8Y6u5UZ5RPV/rYCYOFPA/hbfVtaKObOOarW', 'register', '2026-09-13 05:01:31', 0, 0, '2026-09-13 08:36:31'),
(12, NULL, 'sohelshrestha03@gmail.com', '$2y$10$r3Mq4UFpAfuO5VaruWDkJ.kyLPX.coSZ0Sp7tydjDpSiIwrDwuOIm', 'register', '2026-09-13 05:09:41', 0, 0, '2026-09-13 08:44:41'),
(13, 1, 'shrestha.sohel123@gmail.com', '$2y$10$Wjc0To7wdef1RJt9nnYmIOnI1oF8EeJJUU7aAq78s2ZUvb5E5rdWK', 'change_password', '2026-09-13 07:28:38', 0, 1, '2026-09-13 11:03:38'),
(14, 1, 'shrestha.sohel123@gmail.com', '$2y$10$Euhhlgnyh5Iub4GWu1fzoe15DMz7zu1jvvtdVFtl0za4vUzQ9Wvxe', 'password_reset', '2026-09-13 07:50:23', 0, 1, '2026-09-13 11:25:23'),
(33, 8, 'trioasr@gmail.com', '$2y$10$VBcJGPcNSnUMoOZ8soGI5uDUweWDnYgvOqD9bMU7rohCbONmIUgbW', 'password_reset', '2026-09-15 07:11:58', 1, 1, '2026-09-15 10:46:58'),
(36, NULL, 'gojosaturo83@gmail.com', '$2y$10$zAPKjOWHhCTCTTa3T3riceCFXi8Sdz4gInmKaoPP5WYVxIEwCFykK', 'register', '2026-09-17 09:47:06', 0, 0, '2026-09-17 13:22:06'),
(39, NULL, 'deathwhite132@gail.com', '$2y$10$Avf2N.6Ziy2ZFv04RW7Of.ZhWM0myQ135rgVUL9VJpRSrjOAnQbWO', 'register', '2026-09-17 09:52:18', 0, 0, '2026-09-17 13:27:18');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `description`, `price`, `image`, `created_at`, `stock`, `is_deleted`) VALUES
(4, 'Tattoo Machine Kit Rotary Motor Tattoo Pen', 'Tattoo Machine', 'Complete Tattoo Machine Kit: This set includes everything you need for a professional tattooing experience, from the tattoo pen to the power supply and various needles and accessories.\r\nPowerful Motor with Low Noise: The integrated motor in the tattoo pen provides strong and consistent performance while operating quietly, ensuring a smooth tattooing process.\r\nAdjustable Output Power: The 1500mAh tattoo power supply offers five adjustable output power levels, allowing you to adjust the intensity of your tattoo work for different designs.\r\nDual Charging Protection: The power supply features double charging protection to prevent short circuits and ensure stable output, providing a safe and reliable tattooing experience.\r\nWhat You Can Get: You can get 1pcs Tattoo Pen, 1pcs 1500mAh Tattoo Power Supply, 10pcs 5RM Integrated Needle, 10pcs 5RL Integrated Needle, 4pcs Transfer Paper, 20pcs Ink Cup, 1pcs Practice Skin, 1 Pair Gloves, 1 Roll Bandage.', 2400.00, 'a1faa93ec23e14af97859df4b0142c0a.jpg', '2026-09-07 06:42:00', 8, 0),
(5, 'Chittek Tattoo Pen Kit,Tattoo Kit With Portable Pen Battery X2', 'Tattoo Machine', '[PORTABLE TATTOO MACHINE] Tattoo pen size is 3.7*1.25inches and machine weight 0.4lbs. Made of CNC aircraft grade aluminum. Flutes on the machine handle to Increase the friction so that tattoo artist can hold it tight to draw lines accurately. Tattoo gun is equipped powerful and stable imported tattoo machine electronic core. It works great on both line work and shading. There is pen size adjustable design to adjust pen size that suits you.', 2700.00, '6adfa64ddec435764f853fdc2a7d3698.jpg', '2026-09-07 06:45:09', 15, 0),
(6, 'POSEIDON Tattoo Gun Kit, Wireless Tattoo Pen with 40 Cartridge Needles', 'Tattoo Kits', 'ALL-INCLUSIVE TATTOO KIT: Everything you need to start practicing right away: 1 tattoo pen, 40 assorted cartridge needles, 1 wireless battery, 1 self-cohesive grip cover, 1 Type-C charging cable, 40 ink caps, 1 practice skins, 2 transfer papers, 10×5ml color inks, 1×30ml black ink, 1 pair of gloves and 1 instruction manual. A complete starter set for beginners, starters and apprentices', 2500.00, 'ea12edd11f3525e931871987cf38f447.jpg', '2026-09-07 06:50:25', 6, 0),
(7, '5RL - Tattoo Cartridge Needles 5 RL Pack Of 10 PC', 'Tattoo Needle', 'The cartridge Needle uses a membrane system and is not easily broken.\r\nThe cartridge needles can work together with most tattoo rotary and tattoo pen Machines.\r\nEach cartridge Needle is designed with a needle protection device to enhance stability during your tattoo sessions. \r\nThe durable silicone rebound rings are built to last, ensuring the needles retain their elasticity even after prolonged high-frequency use.\r\nOur new and improved model features an additional silicone outer ring, minimizing vibrations and improving the stability of the needle release.', 1300.00, '9ad5f684636b0ed7490cf9805bf8a5b3.jpg', '2026-09-07 06:52:34', 5, 0),
(8, '50Pcs Tattoo Needles Set Disposable Mixed Tattoo Needles RL,RS,M1, Beginner Set for Tattoo Machine & Tattoo Supplies', 'Tattoo Needle', 'It is made of high quality stainless steel, durable enough for your daily using. It is a must for a current or future needle.Each needle is individually packed in it\'s own factory,they are harmless to you health.Carefully designed, excellent for use', 2300.00, '068222184172abaf2bf9ecc48d3996cb.jpg', '2026-09-07 06:54:42', 4, 0),
(9, 'Lining Black Tattoo Ink 30ml/Bottle Body Arts Paint Tattoo Beauty Tools', 'Tattoo Accessories', 'Powerful, bright black, versatile for line, filling and shadows.Perfect for ism and black and gray easy. Tconsistency is perfect for areas with excellent saturation.One gram pigment is completely soluble in water, indicating color up very quickly.\r\nColor: lining black', 2900.00, '38aa8e457545bc3979b9f144b7f87d17.jpg', '2026-09-07 07:03:23', 6, 0),
(10, '6Pcs/Set Professional Multi Colors Tattoo Ink Pigment Set Kits 5Ml Bottles', 'Tattoo Accessories', 'Made of good quality material, safe to use.\r\nVery easy to use.\r\nWith different bright colors, can choose tone you like to make a tattoo.\r\nCreate dynamic shades and easy to be absorbed into skin.\r\nWildly among professional tattoo artists.', 3000.00, '5c7ad54fbed173098bf0b70e5db6ab52.jpg', '2026-09-07 07:04:39', 6, 0),
(11, 'Tattoo Skin Practice For Beginners A4 & A3 Size', 'Tattoo Supplies', 'Tattoo Practice Skin are available with 2 variation A4 (Medium Size) And A3 ( Large Size )\r\nIt is 100% original Products Imported From USA\r\nIt is only use for Tattooing Practice.\r\nIt is reliable and Durable Also\r\nThese Tattoo practice skin are manly Recommended by Tattoo Artist For Their Students.\r\nTattoo Practice Skin of No Poison and No Harm\r\nFlexible and has a similar feel as human skin, Thick enough for double sided usage\r\nAllow you to improve your skills without practicing on real skin\r\nBoth 2 sides with the same shape, you can also use for practice\r\nFake Tattoo Skin Pad Has 3mm Thickness', 1700.00, 'e0b3dec7bd82dc55a1449fcef9b83c5a.jpg', '2026-09-07 07:07:04', 11, 0),
(15, 'Tattoo Kit Tattoo Machine Strong Motor Handle Cartridge Needles Makeup Tool Ratings 11 Answered Questions', 'Tattoo Machine', 'Tattoo tool kit: Includes tattoo motor, handle and a bag of accessory.\r\nA good choice for beginners and professional tattoo artists.\r\nWith stable performance and low jitter, it is ideal for tattoo work.\r\nStart working voltage: 1.5V, best fit voltage: 6-8V, speed: 10500 rpm.\r\nTattooing machine is lightweight, low noise, rapid thermal diffusion, suitable for liner and shader.\r\nFeatures:\r\nTattoo tool kit: Includes tattoo motor, handle and a bag of accessory.\r\nTattooing machine is lightweight, low noise, rapid thermal diffusion, suitable for liner\r\nand shader.\r\nWith stable performance and low jitter, it is ideal for tattoo work.\r\nA good choice for be ners and professional tattoo artists.\r\nStart working voltage: 1.5V, best fit voltage: 6-8V, speed: 10500 rpm.\r\nSpecification:\r\nItem Type: Tattoo Machine Set\r\nMaterial: Alloy, Silicone\r\nItem Color: As Pictures Shown\r\nTattoo Handle Diameter: Approx. 19mm / 0.7in\r\nPackage Weight: Approx. 96g\r\nPackage Include:\r\n1 * Tattoo Machine\r\n1 * 16mm Tattoo Machine Handle\r\n1 * Bag of Accessory', 2500.00, 'eab6e009d67db8845b80b80be3068538.jpg', '2026-09-14 07:10:21', 13, 1),
(16, 'Tattoo Kit Tattoo Machine Strong Motor Handle Cartridge Needles Makeup Tool', 'Tattoo Machine', 'Tattoo tool kit: Includes tattoo motor, handle and a bag of accessory.\r\nA good choice for beginners and professional tattoo artists.\r\nWith stable performance and low jitter, it is ideal for tattoo work.\r\nStart working voltage: 1.5V, best fit voltage: 6-8V, speed: 10500 rpm.\r\nTattooing machine is lightweight, low noise, rapid thermal diffusion, suitable for liner and shader.\r\nFeatures:\r\nTattoo tool kit: Includes tattoo motor, handle and a bag of accessory.\r\nTattooing machine is lightweight, low noise, rapid thermal diffusion, suitable for liner\r\nand shader.\r\nWith stable performance and low jitter, it is ideal for tattoo work.\r\nA good choice for be ners and professional tattoo artists.\r\nStart working voltage: 1.5V, best fit voltage: 6-8V, speed: 10500 rpm.\r\nSpecification:\r\nItem Type: Tattoo Machine Set\r\nMaterial: Alloy, Silicone\r\nItem Color: As Pictures Shown\r\nTattoo Handle Diameter: Approx. 19mm / 0.7in\r\nPackage Weight: Approx. 96g\r\nPackage Include:\r\n1 * Tattoo Machine\r\n1 * 16mm Tattoo Machine Handle\r\n1 * Bag of Accessory', 2500.00, 'cf35262c88300c3a392200089d59ead4.jpg', '2026-09-14 07:35:09', 7, 1),
(17, 'Tattoo Kit Tattoo Machine Strong Motor Handle Cartridge Needles Makeup Tool', 'Tattoo Machine', 'Tattoo tool kit: Includes tattoo motor, handle and a bag of accessory.\r\nA good choice for beginners and professional tattoo artists.\r\nWith stable performance and low jitter, it is ideal for tattoo work.\r\nStart working voltage: 1.5V, best fit voltage: 6-8V, speed: 10500 rpm.\r\nTattooing machine is lightweight, low noise, rapid thermal diffusion, suitable for liner and shader.\r\nFeatures:\r\nTattoo tool kit: Includes tattoo motor, handle and a bag of accessory.\r\nTattooing machine is lightweight, low noise, rapid thermal diffusion, suitable for liner\r\nand shader.\r\nWith stable performance and low jitter, it is ideal for tattoo work.\r\nA good choice for be ners and professional tattoo artists.\r\nStart working voltage: 1.5V, best fit voltage: 6-8V, speed: 10500 rpm.\r\nSpecification:\r\nItem Type: Tattoo Machine Set\r\nMaterial: Alloy, Silicone\r\nItem Color: As Pictures Shown\r\nTattoo Handle Diameter: Approx. 19mm / 0.7in\r\nPackage Weight: Approx. 96g\r\nPackage Include:\r\n1 * Tattoo Machine\r\n1 * 16mm Tattoo Machine Handle\r\n1 * Bag of Accessory', 2500.00, 'c63adc8301e82f8b2e09a7b15ee0c4f5.jpg', '2026-09-14 07:35:09', 9, 0),
(18, '12PCS Tattoo Marker Pen Skin Marker Fine Point Large Capacity Scribe Tattoo Tool Waterproof Ink Eyebrow Tattoo Pen', 'Other Supplies', 'Features:\r\n\r\n12 colors, simulated tattoo pattern markers, long-lasting ink use.\r\n\r\nSpecifications:\r\nProduct size (including product self-packaging): 20x19.5x1.6cm/7.88x7.68x0.63\"\r\n\r\nSingle size: 13.6cm/5.36\"\r\nProduct weight: 116g\r\nPacking: boxed\r\n\r\nPacking: 1 set /12pcs\r\n\r\nNotes:\r\n1. Due to the different monitor and light effect, the actual color of the item might be slightly different from the color showed on the pictures. Thank you!\r\n2. Please allow 1-3cm measuring deviation due to manual measurement.\r\n\r\n12 XTattoo Transfer Pens', 2700.00, '3234207c36751e873780669e14a6abaa.jpg', '2026-09-14 07:41:05', 9, 0),
(19, '12PCS Tattoo Marker Pen Skin Marker Fine Point Large Capacity Scribe Tattoo Tool Waterproof Ink Eyebrow Tattoo Pen', 'Other Supplies', 'Features:\r\n\r\n12 colors, simulated tattoo pattern markers, long-lasting ink use.\r\n\r\nSpecifications:\r\nProduct size (including product self-packaging): 20x19.5x1.6cm/7.88x7.68x0.63\"\r\n\r\nSingle size: 13.6cm/5.36\"\r\nProduct weight: 116g\r\nPacking: boxed\r\n\r\nPacking: 1 set /12pcs\r\n\r\nNotes:\r\n1. Due to the different monitor and light effect, the actual color of the item might be slightly different from the color showed on the pictures. Thank you!\r\n2. Please allow 1-3cm measuring deviation due to manual measurement.\r\n\r\n12 XTattoo Transfer Pens', 2700.00, '0978000796e571229ecd8bb6e37370da.jpg', '2026-09-14 07:41:05', 6, 1),
(20, 'Wenefang Microblading 40ml Blue Soap Cleaning Soothing Solution Tattoo Studio Supply Tattoo Accessories Tattoo Cleaning Supplie', 'Cleaning & Hygiene', 'Microblading 40ml Blue Soap Cleaning Soothing Solution Tattoo Studio Supply Tattoo Accessories Tattoo Cleaning Supplie\r\nMicroblading 40ml Blue Soap Cleaning Soothing Solution Tattoo Studio Supply Tattoo Accessories Tattoo Cleaning Supplie\r\nMicroblading 40ml Blue Soap Cleaning & Soothing Solution Tattoo Studio Supply Tattoo Accessories Tattoo Cleaning Supplie\r\n\r\nDescription:\r\n\r\n It is used instead of traditional chlorella dilution, and it is more effective when used with a foaming bottle. It can effectively clean the tattoo area, relieve redness and swelling, bring analgesic effect to the tattoo wound, clean and refresh the new feeling, solve the pungent smell of traditional green algae, and retain and strengthen the cleaning effect.\r\n\r\nSpecifications:\r\n\r\n[Name]: BLUE SOAP cyanobacteria stock solution\r\n\r\n【Specifications】: 40ml\r\n\r\n[Use]: It is used instead of traditional chlorella dilution, and it is more effective when used with a foaming bottle. It can effectively clean the tattoo area, relieve redness and swelling, bring analgesic effect to the tattoo wound, clean and refresh the new feeling, solve the pungent smell of traditional green algae, and retain and strengthen the cleaning effect.\r\n\r\n[Usage method]: Fill the bubble bottle with pure water and then add a bottle of cyanobacteria with more caps, shake well to squeeze out rich bubbles\r\n\r\nPackage Included:\r\n\r\n1xTattoo Cleaner\r\n\r\nNotes:\r\n\r\nDue to manual measurement, there may be an error of 1-2 cm, please understand.\r\n\r\nThe color difference may vary depending on the monitor settings.\r\n\r\nWe provide you with the best products and services.\r\n\r\nIf you have any questions, please let us know and we will solve the problem as soon as possible.\r\n\r\nthank you very much.', 2500.00, 'a160b932cc153727351ab85c94cf76f0.png', '2026-09-14 07:45:23', 8, 0),
(21, 'Mini Cordless Tattoo Supply 1350mAh Display Portable Rechargeable Battery for Tattoo Machine Pen Interface C', 'Power & Electronics', 'Item: Rechargeable Wireless Tattoo Supply\r\nStandard: connection\r\nCharging time: 3 hours\r\nWorking time: 3-6 hours\r\nPowerful 1350mAh battery: High capacity battery, one battery can work for 3-6 hours, very easy to replace wireless accessories\r\n\r\nColour:C:Red\r\nMaterial:Aluminum Alloy\r\n\r\nPackage Contents:\r\n1 x Tattoo Supply\r\n\r\nOnly the above package content, other products are not included.\r\nNote: Light reflection and different displays may cause the color of the item in the picture a little different from the real thing. The measurement allowed error is +/- 1-3cm.', 42000.00, 'bd5ee426e30fc2e0ed724becd00b293c.png', '2026-09-15 05:17:32', 6, 0),
(22, '14RT Disposable Tips For Round Tattoo Needles (Pack of 50)', 'Disposable Supplies', 'Model Number: 14RT Disposable Tips\r\n\r\nNumber of Tips: Pack of 50\r\n\r\nSterilized: Yes\r\n\r\nUse For: 14RT Round Shader & Liner Tattoo Needle\r\n\r\nMaterial: Plastic\r\n\r\nSuitable For: Tattoo Parlor & Studio\r\n\r\nType : Round Sader & Round Liner Tip\r\n\r\n14RT Disposable Tip Box 14RT High-quality tattoo supplies. Sterile Professional small disposable tattoo tips. EO Gas Sterilized in individually sealed sterile pouch Colour full plastic disposable tips with built in back-stim 100% Guaranteed for craftsmanship and quality Box of 50 individually wrapped.\r\n\r\nRequires the use of a separate grip. This design provides the added convenience of fewer components requiring less pre-cleaning and sterilization\r\n\r\nIt is used for Round Shader & Liner Tattoo Needle', 600.00, 'f712b033f7e436812bc36017d881c5dc.png', '2026-09-15 05:22:07', 7, 0),
(23, '14RT Disposable Tips For Round Tattoo Needles (Pack of 50)', 'Disposable Supplies', 'Model Number: 14RT Disposable Tips\r\n\r\nNumber of Tips: Pack of 50\r\n\r\nSterilized: Yes\r\n\r\nUse For: 14RT Round Shader & Liner Tattoo Needle\r\n\r\nMaterial: Plastic\r\n\r\nSuitable For: Tattoo Parlor & Studio\r\n\r\nType : Round Sader & Round Liner Tip\r\n\r\n14RT Disposable Tip Box 14RT High-quality tattoo supplies. Sterile Professional small disposable tattoo tips. EO Gas Sterilized in individually sealed sterile pouch Colour full plastic disposable tips with built in back-stim 100% Guaranteed for craftsmanship and quality Box of 50 individually wrapped.\r\n\r\nRequires the use of a separate grip. This design provides the added convenience of fewer components requiring less pre-cleaning and sterilization\r\n\r\nIt is used for Round Shader & Liner Tattoo Needle', 600.00, '64f2c20c449ab31ef21a87f5f1791376.png', '2026-09-15 05:22:07', 10, 1),
(24, '14RT Disposable Tips For Round Tattoo Needles (Pack of 50)', 'Disposable Supplies', 'Model Number: 14RT Disposable Tips\r\n\r\nNumber of Tips: Pack of 50\r\n\r\nSterilized: Yes\r\n\r\nUse For: 14RT Round Shader & Liner Tattoo Needle\r\n\r\nMaterial: Plastic\r\n\r\nSuitable For: Tattoo Parlor & Studio\r\n\r\nType : Round Sader & Round Liner Tip\r\n\r\n14RT Disposable Tip Box 14RT High-quality tattoo supplies. Sterile Professional small disposable tattoo tips. EO Gas Sterilized in individually sealed sterile pouch Colour full plastic disposable tips with built in back-stim 100% Guaranteed for craftsmanship and quality Box of 50 individually wrapped.\r\n\r\nRequires the use of a separate grip. This design provides the added convenience of fewer components requiring less pre-cleaning and sterilization\r\n\r\nIt is used for Round Shader & Liner Tattoo Needle', 600.00, '7d3ad13d5f071682fa26ef9601e74d4d.png', '2026-09-15 05:22:07', 10, 1),
(25, 'Electric Digital Tattoo Machines Permanent Makeup Machine Pen For Eyebrows Lips Body Tattoo Cosmetic Kits', 'Tattoo Machine', 'Microblading tattoo machine electric permanent makeup machine tattoo gun device for eyebrow eyeliner lip body art beauty.\r\nNew designed microblading tattoo pen machine.', 12000.00, 'bcfa8cf29f282e9164a8e6685c536b63.jpg', '2026-09-16 02:57:32', 7, 0),
(26, 'Ambition Mars-U Adjustable Stroke Wireless Tattoo Machine', 'Tattoo Machine', 'Material: space aluminum alloy\r\n6Needle Strokes: 2.2mm, 2.6mm, 3.0mm, 3.4mm, 3.8mm, 4.2mm.\r\nMotor: Coreless Motor.\r\nMotor Speed: 10V - 9000RPM\r\nSingle Battery Capacity: 1800mAh\r\nCharging time: about2 hours\r\nOperation time: about 6 hours\r\nOutput Voltage : 5V-12V\r\nLCD display content: output voltage range + battery usage+working time\r\nInstructions for use:\r\n- Power ON/OFF: Long press \"O\" key for 3 seconds\r\n- Output pause / resume: Short press \"O\" key to pause the output, and short press it again to resume the output(the resume voltage is the working voltage before the pause).\r\n- Adjust output voltage: Press the \"+\" or\"-\" key to adjust output voltage. The voltage can be adjusted through button (+) and (-) respectively with the voltage of 0.1V on each press.\r\nTattoo Power Supply: The LED display will show the remaining power, and the full power will display 4 divisions. Fast charging through the matching charging cable can usually be fully charged about 2 hours.', 40000.00, '45542dd94b2799af529f10f8d53fcb34.jpg', '2026-09-16 03:03:06', 8, 0),
(27, 'Dynamic Triple Black Tattoo Ink 1 oz ( 30 ml )', 'Tattoo Accessories', 'Dynamic Triple Black Tattoo Ink - 1 oz. Bottle Professional Quality Pre-Dispersed Inks. made in the USA,\r\nDynamic Color provides premium quality tattoo ink with the most impressive pigment content in the business.\r\nNoted for its smooth flow rate, our inks are up to piece r high-pigment inks can be mixed to create your own custom blends.\r\nWith Dynamic Triple Black tattoo ink in your needle, the only limit is your own imagination. Vegan and Never Tested on Animals: Dynamic Color products contain zero animal products and are never tested on any furry friends.\r\nOur tattoo inks are safe on the skin, better for the environment, and ultimately preferred for your overall health.\r\nIt Is Mostly use For Outlining, Shading & Tribal Tattoo Work\r\nTried & True since 1990, Dynamic BLK Tattoo Ink is a true staple in the tattoo industry! Get back to the basics with Dynamic Color Black Tattoo Ink. Our Dynamic BLK original Black Tattoo Ink is a true black with a long-lasting, deep finish. Dark as night, this Black Tattoo Ink is ideal for outlining and shading. Formulated with an incredibly smooth flow rate, our tattoo pigments are up to any task, from precise linework to fine dotwork.\r\nProudly made in the USA, our Dynamic Black Tattoo Ink for sale is noted as having some of the most impressive pigment content on the market. This high-impact ink is easily wiped clean from the skin to allow for maximum work rate and a cleaner canvas. With Dynamic Color Black Tattoo Ink on your needle, the only limit is your own imagination.\r\nFeatures of Dynamic Triple Black Tattoo Ink:\r\n\r\nProfessional Quality Pre-Dispersed Inks: Proudly made in the USA, Dynamic Color provides premium quality tattoo ink with the most impressive pigment content in the business. Noted for its smooth flow rate, our inks are up to piece — from precise detail work to outlining and shading.\r\nAuthentic and Hygienic: We’re aware of the large volume of fraudulent products on the market, which is why all Dynamic Color inks arrive in crystal clear PET bottles with a Dynamic logo anti-counterfeiting coating. Even our heat seals, which secure your sterilized product, hold the Dynamic Color stamp of approval.\r\nTime-Tested, Reliable Healing: Say goodbye to stubborn healing processes or unhappy clients. Dynamic Color’s black tattoo ink goes in smooth and heals vibrantly for noticeably vivid results that can stand the test of time. And with skin in the game since 1990, we mean it when we say our inks are time-tested.\r\nBold, Bright, Long Lasting Colors: Every artist deserves high-quality tattoo ink. Available in an expanded range of 30 colors, our high-pigment inks can be mixed to create your own custom blends. With Dynamic Color tattoo ink in your needle, the only limit is your own imagination.\r\nVegan and Never Tested on Animals: Dynamic Color products contain zero animal products and are never tested on any furry friends. Our vegan tattoo ink is safe on the skin, better for the environment, and ultimately preferred for your overall health.', 2000.00, '15c2a7b3a651682e0b15c53d7854ee71.jpg', '2026-09-16 03:08:14', 5, 0),
(28, 'Blue Element Tattoo Ink 1oz Bottle', 'Tattoo Accessories', 'Element Tattoo Supply Blue Tattoo Ink is a bold, deep blue built for professional tattoo artists. Loaded with high-pigment organic pigments and pre-dispersed for smooth, consistent flow, it delivers clean coverage from line work through solid color packing. Vegan, cruelty-free, and made in the USA.\r\n\r\nBrand: Element Tattoo Supply\r\n\r\nColor: Blue\r\n\r\nSize: 1 oz (30 mL)\r\n\r\nFormula: High-pigment, organic pigments\r\n\r\nMade In: USA\r\n\r\nVegan: Yes, never tested on animals\r\n\r\nBest For: Color packing, cool blends, lining and strong accents\r\n\r\nBold Deep Blue: Rich, saturated pigment that heals true and stays vivid for the life of the tattoo.\r\nSmooth Flow: Pre-dispersed formula works cleanly through liners and shaders without clogging or skipping.\r\nVegan and Cruelty-Free: No animal-derived ingredients. Never tested on animals.\r\nMade in the USA: Professional-grade ink from an artist-run American brand.\r\nFor professional use by licensed tattoo artists only. Keep out of reach of children. Not for use on broken or irritated skin. Always follow proper sterilization and single-use protocols.', 1250.00, '3a1313203edc9a1a29a587f203f3ea8a.jpg', '2026-09-16 03:13:28', 10, 0),
(29, 'Lanyun Coil machine single machine tattoo set with mini tattoo power supply, 2025 4-color tattoo ink, hook wire, foot pedal, all available', 'Tattoo Kits', 'Lanyun Coil machine single machine tattoo set with mini tattoo power supply, 2025 4-color tattoo ink, hook wire, foot pedal, all available\r\nBrand:No Brand\r\nSKU:291155087_LK-1734181647\r\nUsage:Dry\r\nCoil machine single machine tattoo set with mini tattoo power supply, 2025 4-color tattoo ink, hook wire, foot pedal, all available\r\nThe single machine tattoo kit for the coil machine includes 1 flower three white triangle machine, 1 mini black power supply, multiple specifications of power cords, 4 colors of Panda brand tattoo ink, 10 color cups, 20 disposable tattoo needles, 20 needle tips, 1 stainless steel handle, 1 transparent tattoo pedal, 1 hook wire, 1 transfer paper, 1 bottle of 5ml transfer gel, 1 silicone white skin, repair paste, needle tip brush, adjustment kit, etc.', 25000.00, '6b618a996811d1710d3942ac141e6c8d.png', '2026-09-16 03:18:25', 10, 0),
(30, 'Lanyun Coil machine single machine tattoo set with mini tattoo power supply, 2025 4-color tattoo ink, hook wire, foot pedal, all available', 'Tattoo Kits', 'Lanyun Coil machine single machine tattoo set with mini tattoo power supply, 2025 4-color tattoo ink, hook wire, foot pedal, all available\r\nBrand:No Brand\r\nSKU:291155087_LK-1734181647\r\nUsage:Dry\r\nCoil machine single machine tattoo set with mini tattoo power supply, 2025 4-color tattoo ink, hook wire, foot pedal, all available\r\nThe single machine tattoo kit for the coil machine includes 1 flower three white triangle machine, 1 mini black power supply, multiple specifications of power cords, 4 colors of Panda brand tattoo ink, 10 color cups, 20 disposable tattoo needles, 20 needle tips, 1 stainless steel handle, 1 transparent tattoo pedal, 1 hook wire, 1 transfer paper, 1 bottle of 5ml transfer gel, 1 silicone white skin, repair paste, needle tip brush, adjustment kit, etc.', 25000.00, 'c0a5a788f5821b56c611afdd7da8d3e4.png', '2026-09-16 03:18:26', 10, 1),
(31, 'Tattoo Skin Practice For Beginners A4 & A3 Size', 'Tattoo Supplies', 'Tattoo Fake Skin Pad Practice Skin of No Poison and No Harm\r\nFlexible and has a similar feel as human skin, Thick enough for double sided usage\r\nAllow you to improve your skills without practicing on real skin\r\nBoth 2 sides with the same shape, you can also use for practice\r\nFake Tattoo Practice Skin Pad Has 6mm Thickness\r\nIt is 100% poriginal Products Imported From USA\r\nTattoo Practice Skin are available with 2 variation A4 (Medium Size) And A3 ( Large Size )\r\nIt is only use for Tattooing Practice.\r\nIt is reliable and Durable Also\r\nThese Tattoo practice skin are manly Recommended by Tattoo Artist For Their Students.\r\nTattoo Practice Skin are available with 2 variation A4 (Medium Size) And A3 ( Large Size )\r\nIt is 100% original Products Imported From USA\r\nIt is only use for Tattooing Practice.\r\nIt is reliable and Durable Also\r\nThese Tattoo practice skin are manly Recommended by Tattoo Artist For Their Students.\r\nTattoo Practice Skin of No Poison and No Harm\r\nFlexible and has a similar feel as human skin, Thick enough for double sided usage\r\nAllow you to improve your skills without practicing on real skin\r\nBoth 2 sides with the same shape, you can also use for practice\r\nFake Tattoo Skin Pad Has 3mm Thickness', 1000.00, '7632428f67caa17e302164fcc9a679d4.png', '2026-09-16 03:21:19', 6, 0),
(32, 'HOT YGirlash 5/10PCS Eyelash Training Silicone Practice Permanent Makeup Eyebrow Tatto Eyebrow Tattoo Skin Practice Tools', 'Tattoo Supplies', 'YGirlash 5/10PCS Eyelash प्रशिक्षण सिलिकन अभ्यास स्थायी मेकअप भौं ट्याटो आइब्रो ट्याटू छाला अभ्यास उपकरणहरू\r\nHign-concerned केमिकल: None\r\nMaterial:Sillicon\r\nModel Number:001\r\nOrigin:Mainland China\r\nType:Tattoo accesories\r\ncolor:skin color\r\nName:YGirlash 5/10PCS Eyelash Training Silicone Practice Permanent Makeup Eyebrow Tattoo Eye\r\nTatto Eyebrow', 1300.00, '8b2f276e5d4550e974a1081d3b91f095.png', '2026-09-16 03:25:11', 9, 0),
(33, 'Tattoo Practice Skin 1-30PCS Fake Skin Exercise Tool for Beginners Blank Tattoo Skin Eyebrow Paint Double Side Synthetic Leather', 'Tattoo Supplies', 'Double sides design: Each practice artificial skin measures approx. thick enough for double sided usages, it can clearly show what your have drawn, support for you to trace your drawing and lining.\r\n\r\n\r\nRealistic touch: these double sided practice skins contain a smooth surface and neat edges, can enhance the better skin tactility and bring you a realistic tattooing experience, their blank and smooth surface allow you to design your favorite eyebrow shape and practice different techniques from outlining to shading.', 1000.00, 'a7b7a5c8c1cafc7da2e9b4f19d11fb37.png', '2026-09-16 03:30:22', 5, 0),
(34, 'Double Permanent Tattoo Beginner Skin Practice Simulation Skin Body Art Tool 5a63', 'Tattoo Supplies', 'No impurities and better skin touch feeling. Individual packaging for each tattoo practice skin. Plain surface for freestyle design and practice. Practice different tattoo techniques, from outlining to shading. Great for testing your progress during tattoo practice sessions. Tattooing and microblading skin practice is flexible and has a similar feel as human skin. Ideal for wrapping around arms or legs and place on chests or backs. This fake skin was thick enough for double sided usage. Specification: Condition: 100% Brand New Material:Artificial Soft Leather Size: 15*20cm Package List: 1 x Tattoo Practice Skin Note: 1.Please allow 1-2cm errors due to manual measurement. 2.Item color displayed in photos may be showing slightly different on your computer monitor since monitors are not calibrated same.', 650.00, 'ac934d578a7115bfa5d46f5ea8d0a27d.png', '2026-09-16 03:32:47', 4, 0),
(35, '30ML Tattoo Green Soap High Concentration Original Liquid Cleaning Liquid Foam Rich Tattoo Cleaning Products 5a63', 'Cleaning & Hygiene', 'Formulated to remove blood, ink, and body fluids without irritating the skin. perfect for sensitive, freshly tattooed skin. A staple in tattoo studios worldwide for its reliable, skin-safe formula. Specification: 100% brand new quality Item Name:Tattoo Green Soap Ingredients: potassium, pigment, flavor, deionized water, thickener, preservative, benzyl alcohol, hydroxypropyl, methylcellulose, cocamidopropyl betaine, sodium sulfate alcohol ether, surfactant, etc. Color:As the picture show Size:10.5cm/4.13inch*2.7cm/1.06inch Shelf life: three years Net capacity:30ML Package includes: 1 x Tattoo Green Soap Note: 1. Please note that the photo does not show actual size, please refer to Description for size details. 2. Please allow differences due to manual measurement, thanks. 3. Due to the difference between different monitors, the image may not reflect the actual color of the item.', 1100.00, 'faaba86381e3d316ab5ed1090d9cf2b9.png', '2026-09-16 03:37:52', 5, 0),
(36, 'Wenefang 【Happy childhood memories】Soap Cleaning Solution Tattoo Wound Tattoos Lighten Redness Green Algae Soap Equipment Cleaning Liquid', 'Cleaning & Hygiene', 'Wenefang 【Happy childhood memories】Soap Cleaning Solution Tattoo Wound Tattoos Lighten Redness Green Algae Soap Equipment Cleaning Liquid\r\nBrand:No Brand\r\nSKU:360152331_LK-1985337149', 7600.00, '2d43d2955e47fd80754645e6141b53dd.png', '2026-09-16 03:39:51', 10, 0),
(37, 'NEW Supply Tools: Soap Cleaning Professional / 40 Accessories Hengli [Story] 500ml Tattoo Blue Soap Soothing Tattoo Accessories', 'Cleaning & Hygiene', 'Supply Tools: Soap Cleaning Professional / 40 Accessories Hengli [Story] 500ml Tattoo Blue Soap Soothing Tattoo Accessories\r\nSupply Tools: Soap Cleaning Professional /40 Accessories Hengli [Story] 500ml Tattoo Blue Soap Soothing Tattoo Accessories\r\nOrigin:Mainland China\r\nType:Tattoo Kits\r\nItem:Tattoo Green Soap\r\nType:Tattoo Accesories\r\nOther Item:Blue Algae Soap Liquid\r\nMaterial:Liquid\r\nColour:Blue\r\nFeature 1:Tattoo Cleaning Liquid\r\nFeature 2:Blue Algae Soap\r\nCapacity:40ml, 500ml /bottle', 2550.00, '32d1a58f92848b384f0cb958e42cf1bb.png', '2026-09-16 03:41:17', 5, 0),
(38, 'NEW Supply Tools: Soap Cleaning Professional / 40 Accessories Hengli [Story] 500ml Tattoo Blue Soap Soothing Tattoo Accessories', 'Cleaning & Hygiene', 'Supply Tools: Soap Cleaning Professional / 40 Accessories Hengli [Story] 500ml Tattoo Blue Soap Soothing Tattoo Accessories\r\nSupply Tools: Soap Cleaning Professional /40 Accessories Hengli [Story] 500ml Tattoo Blue Soap Soothing Tattoo Accessories\r\nOrigin:Mainland China\r\nType:Tattoo Kits\r\nItem:Tattoo Green Soap\r\nType:Tattoo Accesories\r\nOther Item:Blue Algae Soap Liquid\r\nMaterial:Liquid\r\nColour:Blue\r\nFeature 1:Tattoo Cleaning Liquid\r\nFeature 2:Blue Algae Soap\r\nCapacity:40ml, 500ml /bottle', 2550.00, '9c74b551736f7e5618150f2829135139.png', '2026-09-16 03:41:17', 6, 1),
(39, 'Wenefang STIGMA 30ML/120ML/360ML Tattoo Blue Soap Cleaning Soothing Solution 【2025 Version】 Full Set Tattoo Studio Eyeliner Lip Eyebrow Supply Accessories', 'Cleaning & Hygiene', 'STIGMA 30ML/120ML/360ML Tattoo Blue Soap Cleaning Soothing Solution 【2025 Version】 Full Set Tattoo Studio Eyeliner Lip Eyebrow Supply Accessories\r\nSTIGMA 30ML/120ML/360ML Tattoo Blue Soap Cleaning Soothing Solution Full Set Tattoo Studio Eyeliner Lip Eyebrow Supply Accessories\r\nSTIGMA 30ML/120ML/360ML Tattoo Blue Soap Cleaning Soothing Solution Full Set Tattoo Studio Eyeliner Lip Eyebrow Supply Accessories\r\nProduct information: rBrand：STIGMA rColor：Blue\r\n\r\n\r\nFeature: rEfficacy: Soothing cleaning, preventing infection rBlue soap is used before, during and after a tattoo procedure. rIt is used for avoiding stinging or skin irritation. rExcellent when diluted with water, great for cleaning.\r\n\r\n\r\nPackage includes：1Pc Tattoo Blue Soap\r\n\r\n\r\nNotes： r1.Stigma is our unique brand, we have our own factory, support retail and wholesale, if you need to buy in bulk, please contact us directly, you can enjoy big discounts! r2.Goods are still in stock and delivery will be arranged within 24 hours after order (except public holidays and non-working hours). r3.The machine will be inspected before leaving the factory to ensure the machine can work normally before leaving the factory.Please rest assured of our goods. Thank you for your support.', 5700.00, '166652aa196c326ee346eee29afd66a7.png', '2026-09-16 03:45:07', 9, 0),
(40, '100/50/20PCS Black Nitrile Gloves Disposable Thick Powder Free Cleaning Gloves Textured Kitchen Household Mechanic Tattoo Gloves', 'Disposable Supplies', 'igh Quality & Outstanding Durability: Our black nitrile disposable gloves are thicker than most gloves, designed to be rip and tear resistant and enhanced comfort, grip, durability.\r\nComfortable Fit & Healthier: Our disposable nitrile gloves thick and stretchy so they don\'t rip or tear easily, these disposable rubber gloves go on easy to wear and take off. They can also be used with phone touch screens. Moreover，Latex-free Powder-free, these non-latex gloves will more healthier. For single use only.\r\nFull Textured & Convenient Features: Micro-roughened surface full lightly textured to provide a better grip. Elastic construction forms to the hand to provide outstanding comfort and dexterity. Extraordinary strength and stretchable durability enable you to perform delicate and precise work.\r\nWidely Application: These black disposable gloves look professional while cleaning dirt and grime. They are a great fit for janitorial, tattoo, beauty salon, gardening use, automotive detailing, household work, cleaning, hair coloring, painters, cleaners, pet care, home decoration, home improvements, hobbies, and arts & crafts.\r\nSize: S, M, L, XL(Optional)\r\n\r\nS Glove Cycle Length: 16.5~17.8cm / 6.5~7inch, Length: Approx. 24cm / 9.4inch\r\n\r\nM Glove Cycle Length: 19.1~20.3cm / 7.5~8inch, Length: Approx. 24cm / 9.4inch\r\n\r\nL Glove Cycle Length: 21.6~22.9cm / 8.5~9inch, Length: Approx. 24cm / 9.4inch\r\n\r\nXL Glove Cycle Length: 24.1~25.4cm / 9.5~10inch, Length: Approx. 24cm / 9.4inch', 840.00, '953e35dc6454960fa494afa01cddbc2b.png', '2026-09-16 03:48:28', 0, 0),
(41, '20/50/100PCS Disposable Black Nitrile Gloves Disposable Nitrile Gloves Home Cleaning Tattoo Hairdressing Nail Art Pet Bath', 'Disposable Supplies', 'Can be used for other purposes also.', 670.00, '813c0999ffdfbd7eef22ee1e6ee0496a.png', '2026-09-16 03:51:47', 10, 0),
(42, 'Industrial Black Nitrile Gloves 8mil Heavy Duty Disposable Gloves with Diamond Textured Latex Free Mechanic Tattoo Auto Gloves', 'Disposable Supplies', 'Dura-Gold Duratection: Extra-thick 8 mil nitrile gloves with superior puncture resistance against cuts, snags, and abrasions for maximum safety protection. They offer incredible stretch with no tearing or pinching. See our glove sizing chart image to determine your correct glove size.\r\n\r\nRaised Diamond Texture: Comfortable, snug-fitting ambidextrous gloves with a fully enhanced raised diamond textured grip that provides a superior grip on wet, dry, slippery, and oily surfaces. The raised diamond texture means more surface area to channel away grease and liquids, giving you a firmer non-slip grip on tools and other objects.\r\n\r\nSuper Duty Safety Protection: Extra-thick gloves that are designed for heavy-duty tasks, providing durable barrier protection against grease, oil, and other petroleum-based chemicals. Latex-free, powder-free, and chemical-resistant nitrile gloves that are ideal for use by those who are allergic to natural rubber. The gloves have bare-hand sensitivity, so you can wear them when handling touchscreen phones.\r\n\r\nVersatile: Our durable safety protection gloves are worn worldwide in the most common home or workplace environments. The gloves are safe for household cleaning, sanitation, gardening, home, automotive, mechanical, industrial, manufacturing, workshop, painting, chemicals, tattoo artists, salon professionals, and schools, indoors and outdoors.\r\n\r\n\r\n\r\nSize: M, L,XL (Optional)\r\n\r\nM Glove Cycle Length: 19.1~20.3cm / 7.5~8inch, Length: Approx. 24cm / 9.4inch\r\n\r\nL Glove Cycle Length: 21.6~22.90cm / 8.5~9inch, Length: Approx. 24cm / 9.4inch\r\n\r\nXL Glove Cycle Length: 24.1~25.4cm / 9.5~10inch, Length: Approx. 24cm / 9.4inch', 960.00, '1bf4a580eab8d2b8400a3a95a81a9c87.png', '2026-09-16 03:53:56', 6, 0),
(43, 'Industrial Black Nitrile Gloves 8mil Heavy Duty Disposable Gloves with Diamond Textured Latex Free Mechanic Tattoo Auto Gloves', 'Disposable Supplies', 'Dura-Gold Duratection: Extra-thick 8 mil nitrile gloves with superior puncture resistance against cuts, snags, and abrasions for maximum safety protection. They offer incredible stretch with no tearing or pinching. See our glove sizing chart image to determine your correct glove size.\r\n\r\nRaised Diamond Texture: Comfortable, snug-fitting ambidextrous gloves with a fully enhanced raised diamond textured grip that provides a superior grip on wet, dry, slippery, and oily surfaces. The raised diamond texture means more surface area to channel away grease and liquids, giving you a firmer non-slip grip on tools and other objects.\r\n\r\nSuper Duty Safety Protection: Extra-thick gloves that are designed for heavy-duty tasks, providing durable barrier protection against grease, oil, and other petroleum-based chemicals. Latex-free, powder-free, and chemical-resistant nitrile gloves that are ideal for use by those who are allergic to natural rubber. The gloves have bare-hand sensitivity, so you can wear them when handling touchscreen phones.\r\n\r\nVersatile: Our durable safety protection gloves are worn worldwide in the most common home or workplace environments. The gloves are safe for household cleaning, sanitation, gardening, home, automotive, mechanical, industrial, manufacturing, workshop, painting, chemicals, tattoo artists, salon professionals, and schools, indoors and outdoors.\r\n\r\n\r\n\r\nSize: M, L,XL (Optional)\r\n\r\nM Glove Cycle Length: 19.1~20.3cm / 7.5~8inch, Length: Approx. 24cm / 9.4inch\r\n\r\nL Glove Cycle Length: 21.6~22.90cm / 8.5~9inch, Length: Approx. 24cm / 9.4inch\r\n\r\nXL Glove Cycle Length: 24.1~25.4cm / 9.5~10inch, Length: Approx. 24cm / 9.4inch', 960.00, '430e449382005df61ad32f6fd907aa1e.png', '2026-09-16 03:53:56', 7, 1),
(44, 'New specials 【hot】 Yilong 10 Piece Cartridge Tattoo Needles Rl Rs Rm M1 Disposable Sterilized Safety Tattoo Needle For Cartridge Machines Grips', 'Tattoo Needle', '• High-Quality Tattoo Needles :These tattoo needles are of high quality, ensured by the reputable brand YILONG. They are designed for optimal performance and safety in tattooing.\r\n\r\n\r\n\r\n\r\n• Sterilized Safety :The tattoo needles are meticulously sterilized to ensure a clean and safe tattooing experience. This reduces the risk of infection and promotes skin health.\r\n\r\n\r\n\r\n\r\n• Disposable Convenience :These tattoo needles are disposable, offering convenience and hygiene. Each pack comes with 10 pieces, providing ample supply for multiple tattooing sessions.\r\n\r\n\r\n\r\n\r\n• Versatile Tattoo Needles :These versatile tattoo needles are designed for round liner tattooing. They are suitable for both beginners and professional tattoo artists.\r\n\r\n\r\n\r\n\r\n• Origin : Mainland China\r\n\r\n\r\n\r\n\r\n• Compact and Portable :The compact and portable design of these tattoo needles makes them easy to carry and use, ideal for on-the-go tattoo artists.\r\n\r\n\r\n\r\n\r\nOFEYLE high-grade needle ,we doing high quality products and extreme spotless control,numerous safety and quality certification,more style and options are also available,we have adopt the higher standard for needles,to ensure the highest tattoo industry standard.\r\n\r\n\r\nExperienced soldering and over 20 years of industry of needles makes all details perfectly,Perfectly matches with needles and shell,high-stability,smooth unhindered ink flow,improves the requirement of your tattoo needle\'s quality ,Achieve more customer reputation', 3000.00, '98a8defc833f4914a90a3117e596aba3.png', '2026-09-18 08:44:59', 6, 0),
(45, '5RL Round Liner Tattoo Needle ( Pack of 50 )', 'Tattoo Needle', '5RL Tattoo Needle is Made of high quality stainless steel, sterilized, individually wrapped, Exellent performance with all kind of tattoo coil machine and tattoo rotary machine new Good Quality Tattoo Needles which Sterilized EO Gas and individually packed in Blister pack Tattoo Needles .\r\n\r\nYou will get Good Quality 50 Tattoo Needles in each box, Introduction : The Tattoo Needles produced are specially designed for professional tattoo artists. All Shading needles are made out of 0.35 mm surgical wire with extra long tapered, professionally grouped to give very fine tip of the needle. \r\n\r\nThe tattoo needles are the indispensable tools for tattooing. They are made from high quality surgical material, harmless to human health. This professional tattoo needles are fit for all sorts of tattoo machines. \r\n\r\nAll of them are pre-sterilized and convenient to Shading and lining. It features fine lines and detailed work, the making effect is remarkable .Absolutely safe and healthy to the skin. \r\n\r\nThe perfect choice for people who like the consistency in there tattoo work!', 1260.00, '83e8a749e0c9ceadf49d4e0e43da284a.png', '2026-09-18 08:46:09', 8, 0),
(46, '9 Curve Magnum ( 9rm) Tattoo Needle ( Pack of 50 )', 'Tattoo Needle', '9 Curve Magnum  and 9 Round Magnum ( 7rm ) are same Tattoo Needle is Made of high quality stainless steel, sterilized, individually wrapped, Excellent performance with all kind of tattoo coil machine and tattoo rotary machine new Good Quality Tattoo Needles which Sterilized EO Gas and individually packed in Blister pack Tattoo Needles .\r\n\r\nYou will get Good Quality 50 Tattoo Needles in each box, Introduction : The Tattoo Needles produced are specially designed for professional tattoo artists. All Shading needles are made out of 0.35 mm surgical wire with extra long tapered, professionally grouped to give very fine tip of the needle.\r\n\r\nThe tattoo needles are the indispensable tools for tattooing. They are made from high quality surgical material, harmless to human health. This professional tattoo needles are fit for all sorts of tattoo machines.\r\n\r\nAll of them are pre-sterilized and convenient to Shading and lining. It features fine lines and detailed work, the making effect is remarkable .Absolutely safe and healthy to the skin.\r\n\r\nThe perfect choice for people who like the consistency in there tattoo work!', 1600.00, '02277d198a5104c79e03b67a0d19c722.png', '2026-09-18 08:47:27', 9, 0),
(47, 'Wenefang 400/500ml Tattoo Cleaning Liquid Soap Quality Green Soap Tattoo Profesional Cleansing Soothing Solution Skin Clean Tattoo Tools Accessories', 'Cleaning & Hygiene', '400/500ml Tattoo Cleaning Liquid Soap Quality Green Soap Tattoo Profesional Cleansing Soothing Solution Skin Clean Tattoo Tools Accessories\r\n400/500ml Tattoo Cleaning Liquid Soap Quality Green Soap Tattoo Cleansing Soothing Solution Skin Clean Tattoo Tools Accessories\r\nFeatures:\r\n\r\nCleaning and soothing solution during the tattoo procedure.\r\n\r\nGood sealing effect, and not easy volatilizing, convenient and practical.\r\n\r\nCan prevent inflammation, cleaning wounds andavoiding stinging or skin irritation.\r\n\r\nThe solution does not just clean the skin, but it also soothes the skin and relieves the pain.\r\n\r\nMade from pure vegetable oil and glycerin,  environmentally friendly and safe to use.\r\n\r\n\r\nSpecification:\r\nCondition: 100% Brand New\r\nItem Type:Tattoo Cleaning Liquid\r\nIngredient: Vegetable oil, Glycerin\r\nContent: 500ml', 5600.00, '65276ff86e68fdb65d12f126958fefa8.png', '2026-09-18 08:49:06', 8, 0),
(48, 'HOT 30g Tattoo Dissolving Gel Plant Extract Tattoo Cleaner Gel Fast Effective Professional Tattoo Removal For Post-Tattoo Care', 'Cleaning & Hygiene', 'Features:\r\n\r\nTattoo Removal : Our Tattoo Removal , with its unique ingredient, effectively breaks down pigment-particles deep in the skin,\r\n\r\nfading tattoos.\r\n\r\nNatural Ingredients: The tattoo removal cream is supplemented by a mild formulas of natural plants, which will not cause any irritation or\r\n\r\ndamage to the skin.\r\n\r\nEasy Applicator Tattoo Gel: Our Tattoo Removal is user-friendly and doesn\'t require any professional skills.\r\n\r\nSkin Comfort: The tattoo removal cream will not clog pores, and you can see good results with continued use for a few weeks.\r\n\r\nWith Tattoo Removal , users can gradually alter their appearance by removing or lightening-tattoo\r\n\r\n\r\n\r\nNotice:\r\n\r\n1.Actual color may be slightly different from the image due to different light effect\r\n\r\n2.Please allow 1-3cm deviation due to manual measurement\r\n\r\n\r\nDescription:\r\n\r\nNet contant: 30g\r\n\r\nSizeL 4.1x2.8x10.9cm', 3170.00, 'b6a9b744638e061533551c543e2bcfe8.png', '2026-09-18 08:50:46', 6, 0),
(49, 'Permanent Makeup Tattoo Machine Dermograph Eyebrow PMU Beauty Tattoo Pen Kit Microblading for Embroidery Eyebrow Liner Shader', 'Tattoo Machine', 'New permanent makeup machine- 35000R/M\r\n\r\nSuitable for eyebrows, eyelids and lips,also for small tattoo design.great design and top quality ,low noise muffled,also have long time guarantee.Be able to bear the attrition.The working life is two twice longer than ordinary permanent makeup machines.\r\n\r\nNote: pls remark the USA Plug or Euro Plug in your order,or we will send you US Plug or EU Plug Randomly,thanks\r\n\r\nThe kit includs:\r\n1 X Suitable adapter (Europe or America)\r\n1 x machine box\r\n1 x Pen box\r\n\r\nFree gift:\r\n\r\n- 20 needles\r\n\r\n- 20 needle tips\r\n\r\n-20 Gloves\r\n\r\nPlease note: my friend,we will send you 1R needles,we have two kinds of needles packaging: blue packaging and green packaging are sent randomly, pls remark the USA Plug or Euro Plug in your order,or we will send you US Plug or EU Plug Randomly,thanks.', 4250.00, '2336c69bf80c768d5822598b6f47430b.png', '2026-09-18 08:53:04', 5, 0),
(50, 'Sutuiying Industry Beauty Pigment Tattoo Set Specialized Light Ink Body Art 15ml Tattoo Ink Pigment Makeup Tattoo Products Semi-Permanent', 'Tattoo Accessories', 'Features:\r\n\r\nBrand new and high quality.\r\n\r\n\r\nOne gram pigment is completely soluble in water, indicating color up very quickly.\r\n\r\n\r\nIt can be used together with the hollow tattoo template, which is easy to operate.\r\n\r\n\r\n8 colors are available, you can choose your favorite color.\r\n\r\n\r\nSkin easily absorb pigment, no fade after the repair, color is very positive.\r\n\r\n\r\nVery good choice as temporary tattoo, body makeup, and also it can cover scar on body.\r\n\r\n\r\nSpecification:\r\n\r\n\r\nN.W: 15ml\r\n\r\n\r\nColor: 14 colors\r\n\r\n\r\nNote:\r\n\r\nThere could be some slight differences in the color tone of the pictures and the actual item.\r\n\r\n\r\nPlease allow 1-2mm differs due to manual measurement, thanks.\r\n\r\n\r\nPackage Included:\r\n\r\n\r\n1 x Tattoo Ink\r\n\r\n\r\nNo Retail Box,Packing Safely in Bubble Bag.', 3200.00, '663f2fe7a4963c13b0f6836786f4a844.png', '2026-09-18 08:56:51', 8, 0),
(51, '【The Edge of Beauty】6 pcs/set tattoo piercing skin marker positioning permanent makeup art tool', 'Other Supplies', 'I hope you buy everything because you like it, not because it is cheap.\r\nIf you care about quality, respect the price.\r\nIf what you want is cheap, accept the quality.\r\nInk is non-toxic, environmental and harmless to skin without irritation, which won\'t remain on the skin forever\r\nFine ink particle will not enter into the skin\r\nCan draw smooth and clear marks fluently and is easy to color\r\nThe cap can protect the ink from dirt and being dried', 850.00, '8abb6a1425b4cd8e0d373ee7cc4d1e5d.png', '2026-09-20 03:04:38', 6, 0),
(52, 'Temporary Tattoo Markers for Skin,10/30 Colors Tattoo Pens,Brush Tip,Bright Colors,Face Paint Kit Cosmetic Quality,Halloween Mak', 'Other Supplies', 'Temporary Tattoo Markers for Skin,10/30 Colors Tattoo Pens,Brush Tip,Bright Colors,Face Paint Kit Cosmetic Quality,Halloween Mak', 3500.00, '1edc86d9f637f883d6de416bf4ed4bab.png', '2026-09-20 03:07:03', 3, 0),
(53, '8 Colors Sharpie Permanent Marker Pen Industrial Dust-Free Marker 1.0mm Laboratory Tattoo Pen Art Stationary Supplies', 'Other Supplies', 'U.S. Sharpie permanent marker 30001 industrial dust-free marker 1.0mm paint pen waterproof quick-drying non-fading oil art stationery\r\nBrand: sharpie\r\nModel: 3 series\r\nStyle: minimalist\r\nColor classification: black red blue green yellow orange purple coffee black (sharpie) Chinese version blue (sharpie) Chinese version red (sharpie) Chinese version green (sharpie) Chinese version 30001 black 12 pieces 30002 red 12 pieces 30003 blue 12 pieces 30004 green 12 pieces 30035 yellow 12 pieces 30006 orange 12 pieces 30008 purple 12 pieces 30037 Coffee 12pcs Black(sharp)Chinese version Red(sharp)Chinese version Blue(sharp)Chinese version Green(sharp)Chinese version\r\nRefill color: black red yellow orange green purple teal blue\r\nWhether double-ended: No\r\nPen type: oil-based pen\r\nItem number: 3 series\r\nNumber of colors: 8 colors\r\nNib Material: Fiber\r\nSuitable for people: students, white collar\r\nApplicable ink type: Oil-based ink', 3520.00, 'fa1cb068a39ebf5bca8aea501507d5a3.png', '2026-09-20 03:08:48', 10, 0);
INSERT INTO `products` (`id`, `product_name`, `category`, `description`, `price`, `image`, `created_at`, `stock`, `is_deleted`) VALUES
(54, 'NEW 【Too much love!】Tattoo Machine Kit Complete Lcd Power Supply Double Mode Permanent Makeup With 5pcs Cartridges Needles Rotary Pen Set Supplies', 'Power & Electronics', '【Too much love!】Tattoo Machine Kit Complete Lcd Power Supply Double Mode Permanent Makeup With 5pcs Cartridges Needles Rotary Pen Set Supplies\r\nOrigin:Mainland China\r\nType:Tattoo Kits\r\nStroke:0-3.5mm\r\nWorking Voltage:4-12v\r\nSpeed:10000rpm/max\r\nTattoo Power:Skull Power Supply\r\nOutput Voltage:0-18v\r\nFeature 1:Double Mode Adjustment Voltage\r\nFeature 2:Liner And Shading\r\n1:Tattoo Machine Kit Complete\r\n2:Tattoo Pen Machine Kit Wireless', 7500.00, '735efbd2614d31ffed9feadc9b718d71.png', '2026-09-20 03:11:44', 4, 0),
(55, 'Aluminium Alloy Shell Tattoo Machine Voltage Stabilizer Tattoo Tools Lightweight Portable High-sensitive Tattoo Power Supply', 'Power & Electronics', 'Features:\r\n1. Small size: This product is only the size of a palm, the weight is very light, and it is very convenient to carry.\r\n2. Good quality: This product is made of aluminum alloy material, the quality is very good, not easy to damage.\r\n3. Accurate voltage setting: This product can adjust the voltage arbitrarily below 18V.\r\n4. Foot switch: Connect the foot switch to the foot pedal jack, and connect the hook wire to the hook wire jack to use.\r\n5. LED display: turn on the power, after stepping on the foot, the LED will light up, indicating that the jack has voltage output.\r\n\r\nDescription:\r\nThis mini tattoo power supply, palm size; aluminum alloy shell, beautiful appearance, excellent workmanship; accurate voltage setting, suitable for all kinds of tattoo machine power supply, is an ideal tattoo tool for tattoo artists.\r\n\r\nSpecifications:\r\nMaterial: aluminum alloy\r\nInput voltage: 90-265y\r\nOutput voltage: 1.5-18V\r\nSize: 8*7*2.5cm\r\n\r\nPackage Included:\r\n1* Tattoo power supply\r\n\r\n\r\nNotes:\r\n1. Due to the difference between different monitors, the picture may not reflect the actual color of the item. We guarantee the style is the same as shown in the pictures.\r\n2. Please allow slight dimension difference due to different manual measurement.', 5700.00, 'bcda68938cef9a82741584b8b922679c.png', '2026-09-20 03:12:51', 7, 0),
(56, 'NEW 【Exclusive Discount】Wireless Tattoo Machine Type C Alloy Digital Lcd Pen Tattoo Machine Power Supply For Beauty Salon', 'Power & Electronics', 'Exclusive Discount】Wireless Tattoo Machine Type C Alloy Digital Lcd Pen Tattoo Machine Power Supply For Beauty Salon\r\nCommodity Quality Certification:Ce\r\nOrigin:Mainland China\r\nType:Tattoo Power Supply\r\nItem Type:Wireless Tattoo Machine Battery\r\nMaterial:Abs+ Alloy\r\nBattery:Li Ion Battery', 4500.00, '2ad636543e662f2631fe31e41e4017b6.png', '2026-09-20 03:14:14', 5, 0),
(57, 'NEW 【Exclusive Discount】1500mah Tattoo Power Supply Tattoo Pen Set Interface Led Digital Display For Tattoo Machine Pen', 'Power & Electronics', 'Exclusive Discount】1500mah Tattoo Power Supply Tattoo Pen Set Interface Led Digital Display For Tattoo Machine Pen\r\nCommodity Quality Certification:Ce\r\nOrigin:Mainland China\r\nType:Tattoo Power Supply\r\nCertification:Ce\r\nItem Type:Tattoo Power Supply\r\nBattery:Lithium Battery 1500mah(built In)\r\nPower Interface:Rca Interface\r\nType 1:Tattoo Pen Battery Set\r\nType 2:Tattoo Power Supply Kit\r\nType 3:Rca Tattoo Power Supply\r\nType 4: Tattoo Power Supply', 4360.00, 'd9975feec303fa40aef1476c23f2f70e.png', '2026-09-20 03:15:45', 6, 0),
(58, 'NEW 【Exclusive Discount】Mini Wireless Tattoo Power Supply Rca Connector 1500mah Tattoo For Tattoo Pen Machines Tattoo Power Supply Wireless', 'Power & Electronics', 'Exclusive Discount】Mini Wireless Tattoo Power Supply Rca Connector 1500mah Tattoo For Tattoo Pen Machines Tattoo Power Supply Wireless\r\nCommodity Quality Certification:Ce\r\nOrigin:Mainland China\r\nType:Tattoo Power Supply\r\nMaterial:Aluminium Alloy\r\nOutput Voltage:4-12v\r\nInput Voltage:Dc 5v/1a\r\nConnection:Rca Jack\r\nBattery Capacity:1500mah\r\nSize:28x82mm\r\nNet Weight:70g\r\nPackage Include:1 Pcxtattoo Battery & Power Cord\r\nColor:Black', 5700.00, '85fda4481757e0288f1459024f1485ef.png', '2026-09-20 03:17:18', 6, 0),
(59, 'Luxury Tattoo Kit SUANGL Memory Function Tattoo Pen Kit For Permanent Makeup Tattoo Machine Wireless Tattoo Power Supply Tattoo Kit', 'Tattoo Kits', 'Luxury Tattoo Kit SUANGL Memory Function Tattoo Pen Kit For Permanent Makeup Tattoo Machine Wireless Tattoo Power Supply Tattoo Kit\r\nProduct Name:Wireless Tattoo Kit\r\n\r\nColor：Black\r\n\r\nUsed for：Embroidery tattoo\r\n\r\nPlease power on current below 2A\r\n\r\nSelling point\r\n\r\n1.Quiet stability, strong movement motor, needle stability\r\n\r\n2.New hand-slip anti-slip design, comfortable pen grip, good hand feeling, not tired for long time use\r\n\r\n3.Tattoo machine pen has exquisite workmanship, high hardness and lightweight, unibody\r\n\r\n4.There is an aluminum alloy protective layer at the interface of the needle cylinder and the tattoo pen, which is tightly combined and more wear-resistant.\r\n\r\n5.Used for Embroidery tattoo\r\nOutput Voltage:4-12v\r\nInput Voltage:Dc 5v/1a\r\nConnection:Rca Jack\r\nBattery Capacity:1500mah\r\nSize:28x82mm\r\nNet Weight:70g\r\nPackage Include:1 Pcxtattoo Battery & Power Cord\r\nColor:Black', 7100.00, '75e8f238aa361d026c689dad861767c4.png', '2026-09-20 03:19:05', 8, 0),
(60, 'Bargain price Tattoo Pen Set Rotary Wireless Tattoo Machine Kit Coreless Motor Adjustable 7 Strokes Tattoo Machines Set for Tattoo Artist', 'Tattoo Kits', 'Bargain price Tattoo Pen Set Rotary Wireless Tattoo Machine Kit Coreless Motor Adjustable 7 Strokes Tattoo Machines Set for Tattoo Artist\r\nSPECIFICATIONS:\r\nItem: Wireless Tattoo Pen\r\n\r\nMaterial: Aluminum\r\n\r\nNeedle Protrusion: 1mm - 4.5mm\r\n\r\nMotor: Coreless Motor\r\n\r\nMotor Speed: 8V - 9000RPM\r\n\r\nBattery Capacity: 1800mAh\r\nStroke: 2.4mm - 4.2mm\r\n\r\nCharging Time: About 2 hours\r\n\r\nOperation Time: About 6 hours\r\n\r\nOutput Voltage: 4V-12V\r\n\r\nPackage: 1PCS Wireless Tattoo Pen and 1PCS USB Cord\r\n\r\nFEATURES:\r\n-Power ON/OFF: Long press \"II\" key for 3 seconds\r\n\r\n\r\n-Output pause /resume: Short press \"II\" key to pause the output, and short press it again to resume the output(the resume voltage is the working voltage before the pause).\r\n\r\n\r\n-Adjust output voltage: Press the left and right key to adjust output voltage. The voltage can be adjusted through button left and ightrrespectively with the voltage of 0.1V\r\n\r\n\r\n-Power: Fast charging through the matching fast-charging USB data cable can usually be fully charged in 2 hours.\r\n\r\n\r\nPlease pay attention to the change of power at any time to prevent sudden power loss from affecting your tattooing work.\r\n\r\n\r\nAbout the battery:\r\n\r\nCapacity is 1800mah\r\n\r\nSupport long-term tattoo work, stable output. The 8V output voltage can work for 5-6 hours\r\n\r\nKit Including\r\n1pc Wireless Rotary Tattoo Machine\r\n\r\n20pcs Tattoo Cartridge Needles\r\n\r\n1pc Tattoo Practice Skin\r\n\r\n3pcs Tattoo Grip Tape\r\n\r\n6pc Black Gloves\r\n\r\n40pc Tattoo Ink Caps(Medium/Small)\r\n\r\n4 pcs transfer paper\r\n\r\n1pc tattoo bib\r\n\r\n1pc Shaver\r\n\r\n1 x Tattoo Stancil(send by randomly)', 26000.00, '4f673ccc4fe1b633eb1c750e004af77d.png', '2026-09-20 03:20:37', 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_review_id` int(11) DEFAULT NULL,
  `rating` tinyint(1) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `parent_review_id`, `rating`, `comment`, `created_at`) VALUES
(39, 7, 1, NULL, 3, 'The quality needs improvement.', '2026-09-11 13:02:43'),
(40, 11, 1, NULL, 5, 'I like it.', '2026-09-11 16:59:10'),
(41, 11, 1, 40, NULL, 'hi', '2026-09-12 03:22:24'),
(45, 8, NULL, NULL, 5, 'Noice', '2026-09-13 15:53:53'),
(46, 10, NULL, NULL, 3, 'very bad', '2026-09-13 15:54:23'),
(48, 7, NULL, NULL, 3, 'bad', '2026-09-14 04:28:09'),
(49, 9, NULL, NULL, 4, 'It is better quality.', '2026-09-14 04:28:30'),
(50, 6, NULL, NULL, 5, 'Very good.', '2026-09-14 04:29:00'),
(51, 11, 1, 40, 5, 'hi hlo', '2026-09-14 04:41:17'),
(54, 4, 1, NULL, 4, 'good', '2026-09-14 06:05:42'),
(56, 4, 1, 54, 4, 'jhhgj', '2026-09-14 06:20:25'),
(57, 11, 1, 40, 5, 'lllklk', '2026-09-14 06:29:46'),
(60, 15, 1, NULL, 3, 'Bad', '2026-09-14 07:10:42'),
(61, 4, 2, NULL, 5, 'very nice', '2026-09-15 04:59:14'),
(62, 10, 8, NULL, 3, 'Colour is medium quality.', '2026-09-15 05:03:19'),
(63, 20, 8, NULL, 4, 'This is best for prevent from infection.', '2026-09-15 05:04:43'),
(64, 17, 2, NULL, 2, 'It is cheap quality.', '2026-09-15 05:06:14'),
(65, 20, 2, NULL, 4, 'It is best for me.', '2026-09-15 05:18:36'),
(66, 21, 2, NULL, 5, 'It so expensive but best.', '2026-09-15 05:19:07'),
(67, 8, 16, NULL, 5, 'Best quality.', '2026-09-15 05:25:34'),
(68, 8, 16, 67, 5, 'So cheap price.', '2026-09-15 05:25:55'),
(69, 7, 16, NULL, 2, 'Quality is bad.', '2026-09-15 05:26:23'),
(70, 21, 17, NULL, 4, 'Need little improvement.', '2026-09-15 05:27:43'),
(71, 4, 17, NULL, 3, 'best', '2026-09-15 05:28:06'),
(72, 20, 17, NULL, 2, 'It causes allergy for my skin.', '2026-09-15 05:28:55'),
(73, 37, 18, NULL, 3, 'It is average.', '2026-09-17 07:44:57'),
(74, 20, 18, NULL, 5, 'Very good.', '2026-09-17 07:45:26'),
(75, 8, 18, NULL, 2, 'Quality is not good.', '2026-09-17 07:47:39'),
(76, 7, 18, NULL, 5, 'Best of all time.', '2026-09-17 07:48:05'),
(77, 9, 19, NULL, 1, 'So worst.', '2026-09-17 07:49:44'),
(78, 42, 19, NULL, 3, 'Nice but not satisfied at all.', '2026-09-17 07:50:07'),
(79, 28, 19, NULL, 5, 'Best ink.', '2026-09-17 07:50:31'),
(80, 27, 19, NULL, 4, 'Wow', '2026-09-17 07:50:54'),
(81, 36, 20, NULL, 5, 'very best.', '2026-09-17 07:54:38'),
(82, 34, 20, NULL, 1, 'Cheap quality', '2026-09-17 07:54:54'),
(83, 50, 1, NULL, 5, 'High quality.', '2026-09-18 08:58:45'),
(84, 37, 1, NULL, 3, 'Causes allergy to skin.', '2026-09-18 08:59:23'),
(85, 34, 1, NULL, 5, 'best for beginner.', '2026-09-18 08:59:50'),
(86, 50, 2, NULL, 4, 'Good.', '2026-09-18 09:02:51'),
(87, 28, 2, NULL, 2, 'Not satisfied.', '2026-09-18 09:03:21'),
(88, 25, 1, NULL, 3, 'Cheap quality.', '2026-09-18 09:04:24'),
(89, 44, 2, NULL, 3, 'Nice.', '2026-09-18 09:04:45'),
(90, 17, 8, NULL, 2, 'Worst.', '2026-09-18 09:12:44'),
(91, 42, 16, NULL, 3, 'still need improvement.', '2026-09-18 09:13:25'),
(92, 21, 16, NULL, 5, 'Nice', '2026-09-18 09:13:47'),
(93, 10, 16, NULL, 4, 'best color', '2026-09-18 09:14:04'),
(94, 34, 16, NULL, 1, 'so bad', '2026-09-18 09:14:21'),
(95, 50, 16, NULL, 4, 'best', '2026-09-18 09:14:52'),
(96, 36, 8, NULL, 3, 'Not bad', '2026-09-18 09:15:08'),
(97, 49, 8, NULL, 5, 'very good.', '2026-09-18 09:15:37'),
(98, 44, 8, NULL, 3, 'little satisfied.', '2026-09-18 09:16:04'),
(99, 6, 8, NULL, 3, 'best', '2026-09-18 09:16:38'),
(100, 50, 18, NULL, 3, 'Average', '2026-09-19 16:17:42'),
(101, 10, 19, NULL, 4, 'best', '2026-09-19 16:18:17'),
(102, 8, 19, NULL, 3, 'Better quality.', '2026-09-19 16:18:55'),
(103, 20, 19, NULL, 5, 'best for cleaning.', '2026-09-19 16:19:19'),
(104, 17, 19, NULL, 1, 'worst', '2026-09-19 16:19:52'),
(105, 17, 18, NULL, 1, 'Bad', '2026-09-19 16:20:20'),
(106, 42, 18, NULL, 5, 'It quality is moderate.', '2026-09-19 16:20:58'),
(107, 40, 19, NULL, 2, 'Not good.', '2026-09-19 16:21:50'),
(108, 44, 19, NULL, 2, 'Not good.', '2026-09-19 16:22:29'),
(109, 44, 18, NULL, 3, 'Average', '2026-09-19 16:22:57'),
(110, 11, 17, NULL, 4, 'Nice but not satisfied.', '2026-09-19 16:25:21'),
(111, 42, 17, NULL, 1, 'low quality', '2026-09-19 16:25:41'),
(112, 49, 18, NULL, 4, 'better', '2026-09-19 16:26:13'),
(113, 50, 17, NULL, 5, 'Best quality for tattooing.', '2026-09-19 16:27:01'),
(114, 50, 20, NULL, 3, 'very good.', '2026-09-19 16:27:30'),
(115, 60, 1, NULL, 5, 'Best.', '2026-09-21 04:29:35'),
(116, 59, 1, NULL, 3, 'Average', '2026-09-21 04:29:51'),
(117, 58, 1, NULL, 3, 'Wow! Nice product', '2026-09-21 04:30:14'),
(118, 57, 1, NULL, 5, 'worth it', '2026-09-21 04:30:31'),
(119, 56, 1, NULL, 3, 'best', '2026-09-21 04:30:47'),
(120, 55, 1, NULL, 3, 'Average', '2026-09-21 04:31:06'),
(121, 54, 1, NULL, 4, 'Best', '2026-09-21 04:31:22'),
(122, 53, 1, NULL, 1, 'Low quality', '2026-09-21 04:31:43'),
(123, 52, 1, NULL, 2, 'not satisfied.', '2026-09-21 04:32:07'),
(124, 51, 1, NULL, 1, 'Not good.', '2026-09-21 04:32:36'),
(125, 49, 1, NULL, 4, 'worth it.', '2026-09-21 04:33:11'),
(126, 48, 1, NULL, 4, 'best for washing tattoo', '2026-09-21 04:33:53'),
(127, 47, 1, NULL, 4, 'Good', '2026-09-21 04:34:17'),
(128, 46, 1, NULL, 2, 'cheap metal quality', '2026-09-21 04:34:41'),
(129, 45, 1, NULL, 3, 'little satisifed', '2026-09-21 04:35:01'),
(130, 44, 1, NULL, 4, 'best', '2026-09-21 04:35:27'),
(131, 42, 1, NULL, 1, 'not good.', '2026-09-21 04:35:54'),
(132, 41, 1, NULL, 3, 'worth it for hygiene', '2026-09-21 04:36:17'),
(133, 40, 1, NULL, 3, 'little satisfied', '2026-09-21 04:36:48'),
(134, 39, 1, NULL, 3, 'Good for skin after tattoo', '2026-09-21 04:37:25'),
(135, 36, 1, NULL, 5, 'Best product', '2026-09-21 04:37:56'),
(136, 35, 1, NULL, 2, 'Not worth it', '2026-09-21 04:38:13'),
(137, 33, 1, NULL, 3, 'good', '2026-09-21 04:38:44'),
(138, 32, 1, NULL, 2, 'Not satisfied with this product.', '2026-09-21 04:39:11'),
(139, 31, 1, NULL, 5, 'Best quality', '2026-09-21 04:39:29'),
(140, 29, 1, NULL, 3, 'Average product', '2026-09-21 04:39:55'),
(141, 28, 1, NULL, 3, 'Good ink quality', '2026-09-21 04:40:19'),
(142, 27, 1, NULL, 5, 'best for tattooing', '2026-09-21 04:40:40'),
(143, 26, 1, NULL, 4, 'Best battery performance', '2026-09-21 04:41:26'),
(144, 22, 1, NULL, 2, 'unworthy', '2026-09-21 04:42:14'),
(145, 21, 1, NULL, 5, 'best tattoo battery', '2026-09-21 04:42:48'),
(146, 20, 1, NULL, 3, 'Average', '2026-09-21 04:43:16'),
(147, 18, 1, NULL, 4, 'good quality', '2026-09-21 04:43:40'),
(148, 17, 1, NULL, 1, 'worst product', '2026-09-21 04:43:59'),
(149, 10, 1, NULL, 4, 'Nice colour', '2026-09-21 04:44:36'),
(150, 9, 1, NULL, 1, 'bad item', '2026-09-21 04:44:58'),
(151, 8, 1, NULL, 3, 'Average quality', '2026-09-21 04:45:24'),
(152, 6, 1, NULL, 5, 'Best tattoo machine', '2026-09-21 04:46:03'),
(153, 5, 1, NULL, 3, 'Nice but battery performance is not so good.', '2026-09-21 04:46:41'),
(154, 60, 2, NULL, 4, 'good', '2026-09-21 04:47:59'),
(155, 59, 2, NULL, 4, 'product is good but needle quality is still bad', '2026-09-21 04:48:37'),
(156, 58, 2, NULL, 4, 'Nice battery backup', '2026-09-21 04:49:00'),
(157, 57, 2, NULL, 3, 'Average', '2026-09-21 04:49:20'),
(158, 56, 2, NULL, 5, 'Nice product', '2026-09-21 04:49:41'),
(159, 55, 2, NULL, 5, 'Best for battery backup', '2026-09-21 04:50:08'),
(160, 54, 2, NULL, 4, 'Nice product', '2026-09-21 04:50:28'),
(161, 53, 2, NULL, 3, 'I like this product', '2026-09-21 04:50:47'),
(162, 52, 2, NULL, 1, 'Cheap quality with cheap price', '2026-09-21 04:51:11'),
(163, 51, 2, NULL, 3, 'little satisfied', '2026-09-21 04:51:32'),
(164, 49, 2, NULL, 5, 'best product', '2026-09-21 04:52:09'),
(165, 48, 2, NULL, 3, 'Nice product', '2026-09-21 04:52:28'),
(166, 47, 2, NULL, 3, 'So expensive but worth it', '2026-09-21 04:52:59'),
(167, 46, 2, NULL, 1, 'So worst product', '2026-09-21 04:53:19'),
(168, 45, 2, NULL, 2, 'Not good.', '2026-09-21 04:53:35'),
(169, 42, 2, NULL, 3, 'Not good at all but still satisfied', '2026-09-21 04:54:34'),
(170, 41, 2, NULL, 4, 'Best', '2026-09-21 04:54:55'),
(171, 40, 2, NULL, 1, 'worst', '2026-09-21 04:55:11'),
(172, 39, 2, NULL, 2, 'Causes allegry yo my skin', '2026-09-21 04:55:35'),
(173, 37, 2, NULL, 3, 'Nice for hygience', '2026-09-21 04:56:04'),
(174, 36, 2, NULL, 4, 'worth it product', '2026-09-21 04:56:27'),
(175, 35, 2, NULL, 2, 'Bad item', '2026-09-21 04:56:55'),
(176, 34, 2, NULL, 3, 'best for begginer but quality is low', '2026-09-21 04:57:33'),
(177, 33, 2, NULL, 4, 'Best for practicing', '2026-09-21 04:58:03'),
(178, 32, 2, NULL, 1, 'worst for practice', '2026-09-21 04:59:57'),
(179, 31, 2, NULL, 4, 'Nice product', '2026-09-21 05:00:18'),
(180, 29, 2, NULL, 5, 'best tattoo kits', '2026-09-21 05:00:38'),
(181, 27, 2, NULL, 3, 'Good quality', '2026-09-21 05:01:06'),
(182, 26, 2, NULL, 3, 'better performance', '2026-09-21 05:01:44'),
(183, 25, 2, NULL, 1, 'quality is not good', '2026-09-21 05:02:12'),
(184, 22, 2, NULL, 1, 'worst', '2026-09-21 05:02:34'),
(185, 18, 2, NULL, 3, 'Not so good', '2026-09-21 05:03:13'),
(186, 11, 2, NULL, 5, 'Quality is satisfied', '2026-09-21 05:03:51'),
(187, 10, 2, NULL, 3, 'Average', '2026-09-21 05:04:23'),
(188, 9, 2, NULL, 1, 'I don\'t like this product.', '2026-09-21 05:04:48'),
(189, 8, 2, NULL, 3, 'best', '2026-09-21 05:05:10'),
(190, 7, 2, NULL, 2, 'not good', '2026-09-21 05:05:29'),
(191, 6, 2, NULL, 5, 'best', '2026-09-21 05:05:46'),
(192, 5, 2, NULL, 5, 'Best but needle is not good', '2026-09-21 05:06:17'),
(193, 60, 8, NULL, 4, 'Nice product', '2026-09-21 05:08:12'),
(194, 48, 8, NULL, 3, 'best for skin', '2026-09-21 05:08:32'),
(195, 59, 8, NULL, 5, 'Nice product', '2026-09-22 08:00:14'),
(196, 58, 8, NULL, 4, 'very good', '2026-09-22 08:00:37'),
(197, 57, 8, NULL, 3, 'Little satisfy', '2026-09-22 08:01:02'),
(198, 56, 8, NULL, 3, 'worth it product', '2026-09-22 08:01:23'),
(199, 55, 8, NULL, 3, 'Not so good but still worth it for battery backup', '2026-09-22 08:02:19'),
(200, 54, 8, NULL, 3, 'Average', '2026-09-22 08:03:05'),
(201, 53, 8, NULL, 2, 'cheap quality', '2026-09-22 08:03:38'),
(202, 52, 8, NULL, 1, 'worst product', '2026-09-22 08:04:04'),
(203, 51, 8, NULL, 3, 'Average product', '2026-09-22 08:04:31'),
(204, 50, 8, NULL, 4, 'Nice quality of ink', '2026-09-22 08:05:04'),
(205, 47, 8, NULL, 3, 'Nice but product is still not satisfied', '2026-09-22 08:06:20'),
(206, 46, 8, NULL, 1, 'So bad', '2026-09-22 08:06:43'),
(207, 45, 8, NULL, 2, 'Not good', '2026-09-22 08:07:06'),
(208, 42, 8, NULL, 4, 'Best disposable gloves for tattooing', '2026-09-22 08:08:11'),
(209, 60, 18, NULL, 4, 'best product.', '2026-09-28 08:24:29'),
(210, 59, 18, NULL, 3, 'Little satisfy with this product.', '2026-09-28 08:24:56'),
(211, 58, 18, NULL, 3, 'Not so good but still worth it.', '2026-09-28 08:25:31'),
(212, 57, 18, NULL, 3, 'Not good but still satisfy', '2026-09-28 08:26:01'),
(213, 56, 18, NULL, 5, 'Wow nice battery backup', '2026-09-28 08:26:32'),
(214, 55, 18, NULL, 3, 'good but still not satisfied with this product', '2026-09-28 08:27:15'),
(215, 54, 18, NULL, 3, 'The quality of product is average.', '2026-09-28 08:28:39'),
(216, 53, 18, NULL, 1, 'So worst quality', '2026-09-28 08:29:11'),
(217, 52, 18, NULL, 1, 'Unsatisfied', '2026-09-28 08:29:31'),
(218, 51, 18, NULL, 2, 'This product is made for kids.', '2026-09-28 08:30:04'),
(219, 4, 19, NULL, 3, 'not satisfied at all but still worth it.', '2026-09-28 08:32:35'),
(220, 5, 19, NULL, 3, 'dissatisfied with ink quality', '2026-09-28 08:33:05'),
(221, 6, 19, NULL, 4, 'Good product', '2026-09-28 08:33:47'),
(222, 7, 19, NULL, 3, 'Little bit satisfied with this product', '2026-09-28 08:34:37'),
(223, 47, 19, NULL, 5, 'Best hygiene equipment for tattooing', '2026-09-28 08:35:38'),
(224, 46, 19, NULL, 1, 'Not good at all', '2026-09-28 08:36:07'),
(225, 33, 19, NULL, 3, 'Best for begginer', '2026-09-28 08:36:44'),
(226, 34, 19, NULL, 1, 'Not satisifed at all', '2026-09-28 08:37:12'),
(227, 48, 19, NULL, 5, 'best product for tattooing', '2026-09-28 08:40:52'),
(228, 37, 19, NULL, 4, 'Satisfied but still bad product. Need some improvement', '2026-09-28 08:41:45'),
(229, 50, 19, NULL, 3, 'Good ink quality', '2026-09-28 08:42:18');

-- --------------------------------------------------------

--
-- Table structure for table `stock_activity`
--

CREATE TABLE `stock_activity` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `action` enum('Increase','Decrease') NOT NULL,
  `quantity` int(11) NOT NULL,
  `old_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_activity`
--

INSERT INTO `stock_activity` (`id`, `admin_id`, `product_id`, `action`, `quantity`, `old_stock`, `new_stock`, `created_at`) VALUES
(1, 1, 11, 'Increase', 1, 9, 10, '2026-09-12 06:55:11'),
(2, 1, 8, 'Decrease', 2, 4, 2, '2026-09-12 06:56:29'),
(3, NULL, 11, 'Decrease', 1, 10, 9, '2026-09-12 08:31:32'),
(4, NULL, 11, 'Decrease', 1, 9, 8, '2026-09-13 15:04:20'),
(5, NULL, 5, 'Decrease', 1, 3, 2, '2026-09-13 15:04:20'),
(6, NULL, 10, 'Decrease', 1, 10, 9, '2026-09-13 15:04:20'),
(7, NULL, 7, 'Decrease', 1, 6, 5, '2026-09-13 15:55:22'),
(8, NULL, 10, 'Decrease', 1, 9, 8, '2026-09-13 15:55:22'),
(9, NULL, 9, 'Decrease', 1, 1, 0, '2026-09-13 15:55:22'),
(10, 1, 9, 'Increase', 6, 0, 6, '2026-09-13 15:56:55'),
(11, NULL, 6, 'Decrease', 1, 5, 4, '2026-09-14 04:13:58'),
(12, NULL, 8, 'Decrease', 1, 2, 1, '2026-09-14 04:25:28'),
(13, NULL, 11, 'Decrease', 1, 8, 7, '2026-09-14 04:25:28'),
(15, NULL, 13, 'Decrease', 1, 5, 4, '2026-09-14 06:50:23'),
(16, NULL, 15, 'Decrease', 1, 4, 3, '2026-09-14 07:11:08'),
(17, 1, 5, 'Increase', 8, 7, 15, '2026-09-14 07:14:50'),
(18, 1, 16, 'Increase', 7, 0, 7, '2026-09-14 07:35:09'),
(19, 1, 17, 'Increase', 7, 0, 7, '2026-09-14 07:35:09'),
(20, 1, 18, 'Increase', 4, 0, 4, '2026-09-14 07:41:05'),
(21, 1, 19, 'Increase', 4, 0, 4, '2026-09-14 07:41:05'),
(22, 1, 20, 'Increase', 1, 0, 1, '2026-09-14 07:45:23'),
(23, NULL, 5, 'Decrease', 1, 15, 14, '2026-09-15 04:59:45'),
(24, NULL, 6, 'Decrease', 1, 7, 6, '2026-09-15 04:59:45'),
(25, NULL, 4, 'Decrease', 1, 10, 9, '2026-09-15 04:59:45'),
(26, NULL, 4, 'Decrease', 1, 9, 8, '2026-09-15 05:04:01'),
(27, NULL, 11, 'Decrease', 1, 15, 14, '2026-09-15 05:04:01'),
(28, NULL, 20, 'Decrease', 2, 10, 8, '2026-09-15 05:05:31'),
(29, 1, 15, 'Increase', 7, 3, 10, '2026-09-15 05:07:18'),
(30, 1, 15, 'Increase', 7, 10, 17, '2026-09-15 05:07:19'),
(31, 1, 15, 'Increase', 7, 17, 24, '2026-09-15 05:07:20'),
(32, 1, 15, 'Increase', 7, 24, 31, '2026-09-15 05:07:21'),
(33, 1, 15, 'Decrease', 3, 31, 28, '2026-09-15 05:07:41'),
(34, 1, 15, 'Decrease', 3, 28, 25, '2026-09-15 05:07:42'),
(35, 1, 15, 'Decrease', 3, 25, 22, '2026-09-15 05:07:53'),
(36, 1, 15, 'Decrease', 3, 22, 19, '2026-09-15 05:07:55'),
(37, 1, 15, 'Decrease', 3, 19, 16, '2026-09-15 05:08:06'),
(38, 1, 15, 'Decrease', 3, 16, 13, '2026-09-15 05:08:06'),
(39, 1, 15, 'Decrease', 3, 13, 10, '2026-09-15 05:08:06'),
(40, 1, 15, 'Decrease', 3, 10, 7, '2026-09-15 05:08:06'),
(41, 1, 15, 'Decrease', 3, 7, 4, '2026-09-15 05:08:07'),
(42, 1, 15, 'Decrease', 3, 4, 1, '2026-09-15 05:08:07'),
(43, 1, 15, 'Increase', 4, 1, 5, '2026-09-15 05:08:13'),
(44, 1, 15, 'Increase', 4, 5, 9, '2026-09-15 05:08:14'),
(45, 1, 15, 'Increase', 4, 9, 13, '2026-09-15 05:08:20'),
(46, 1, 21, 'Increase', 3, 0, 3, '2026-09-15 05:17:32'),
(47, 1, 22, 'Increase', 10, 0, 10, '2026-09-15 05:22:07'),
(48, 1, 23, 'Increase', 10, 0, 10, '2026-09-15 05:22:07'),
(49, 1, 24, 'Increase', 10, 0, 10, '2026-09-15 05:22:07'),
(50, NULL, 22, 'Decrease', 2, 10, 8, '2026-09-15 05:24:27'),
(51, NULL, 11, 'Decrease', 2, 14, 12, '2026-09-15 05:24:27'),
(52, NULL, 10, 'Decrease', 2, 8, 6, '2026-09-15 05:30:27'),
(53, NULL, 18, 'Decrease', 2, 4, 2, '2026-09-15 05:30:27'),
(54, NULL, 22, 'Decrease', 1, 8, 7, '2026-09-15 05:30:27'),
(55, 1, 21, 'Increase', 3, 3, 6, '2026-09-15 05:31:20'),
(56, 1, 19, 'Increase', 2, 4, 6, '2026-09-15 05:31:28'),
(57, 1, 18, 'Increase', 2, 2, 4, '2026-09-15 16:33:36'),
(58, 1, 18, 'Increase', 5, 4, 9, '2026-09-15 16:33:41'),
(59, 1, 18, 'Decrease', 6, 9, 3, '2026-09-15 16:33:46'),
(60, 1, 18, 'Increase', 2, 3, 5, '2026-09-15 16:33:50'),
(61, 1, 18, 'Increase', 4, 5, 9, '2026-09-15 16:33:55'),
(62, 1, 25, 'Increase', 7, 0, 7, '2026-09-16 02:57:32'),
(63, 1, 26, 'Increase', 4, 0, 4, '2026-09-16 03:03:06'),
(64, 1, 27, 'Increase', 8, 0, 8, '2026-09-16 03:08:15'),
(65, 1, 28, 'Increase', 6, 0, 6, '2026-09-16 03:13:28'),
(66, 1, 29, 'Increase', 10, 0, 10, '2026-09-16 03:18:25'),
(67, 1, 30, 'Increase', 10, 0, 10, '2026-09-16 03:18:26'),
(68, 1, 31, 'Increase', 6, 0, 6, '2026-09-16 03:21:19'),
(69, 1, 32, 'Increase', 9, 0, 9, '2026-09-16 03:25:11'),
(70, 1, 33, 'Increase', 1, 0, 1, '2026-09-16 03:30:22'),
(71, 1, 34, 'Increase', 5, 0, 5, '2026-09-16 03:32:47'),
(72, 1, 35, 'Increase', 5, 0, 5, '2026-09-16 03:37:52'),
(73, 1, 36, 'Increase', 10, 0, 10, '2026-09-16 03:39:51'),
(74, 1, 37, 'Increase', 6, 0, 6, '2026-09-16 03:41:17'),
(75, 1, 38, 'Increase', 6, 0, 6, '2026-09-16 03:41:17'),
(76, 1, 39, 'Increase', 9, 0, 9, '2026-09-16 03:45:07'),
(77, 1, 40, 'Increase', 4, 0, 4, '2026-09-16 03:48:28'),
(78, 1, 41, 'Increase', 10, 0, 10, '2026-09-16 03:51:47'),
(79, 1, 42, 'Increase', 7, 0, 7, '2026-09-16 03:53:56'),
(80, 1, 43, 'Increase', 7, 0, 7, '2026-09-16 03:53:56'),
(81, NULL, 40, 'Decrease', 1, 4, 3, '2026-09-16 08:00:39'),
(82, NULL, 11, 'Decrease', 1, 12, 11, '2026-09-16 08:10:54'),
(83, NULL, 21, 'Decrease', 1, 7, 6, '2026-09-16 08:10:54'),
(84, NULL, 37, 'Decrease', 1, 6, 5, '2026-09-17 07:47:00'),
(85, NULL, 20, 'Decrease', 1, 9, 8, '2026-09-17 07:47:00'),
(86, NULL, 28, 'Decrease', 1, 6, 5, '2026-09-17 07:52:02'),
(87, NULL, 27, 'Decrease', 2, 8, 6, '2026-09-17 07:52:02'),
(88, NULL, 42, 'Decrease', 2, 7, 5, '2026-09-17 07:52:02'),
(89, NULL, 40, 'Decrease', 1, 3, 2, '2026-09-17 07:52:02'),
(90, 1, 44, 'Increase', 6, 0, 6, '2026-09-18 08:44:59'),
(91, 1, 45, 'Increase', 5, 0, 5, '2026-09-18 08:46:09'),
(92, 1, 46, 'Increase', 9, 0, 9, '2026-09-18 08:47:27'),
(93, 1, 47, 'Increase', 5, 0, 5, '2026-09-18 08:49:06'),
(94, 1, 48, 'Increase', 7, 0, 7, '2026-09-18 08:50:46'),
(95, 1, 49, 'Increase', 7, 0, 7, '2026-09-18 08:53:04'),
(96, 1, 50, 'Increase', 5, 0, 5, '2026-09-18 08:56:51'),
(97, 1, 50, 'Increase', 3, 5, 8, '2026-09-18 09:05:51'),
(98, 1, 47, 'Increase', 3, 5, 8, '2026-09-18 09:05:57'),
(99, NULL, 49, 'Decrease', 1, 7, 6, '2026-09-19 16:30:25'),
(100, NULL, 50, 'Decrease', 1, 8, 7, '2026-09-19 16:30:25'),
(101, NULL, 48, 'Decrease', 1, 7, 6, '2026-09-19 16:30:25'),
(102, NULL, 27, 'Decrease', 1, 6, 5, '2026-09-19 16:31:51'),
(103, NULL, 28, 'Decrease', 1, 5, 4, '2026-09-19 16:31:51'),
(104, NULL, 34, 'Decrease', 1, 5, 4, '2026-09-19 16:31:51'),
(105, NULL, 40, 'Decrease', 5, 5, 0, '2026-09-19 16:33:16'),
(106, 1, 45, 'Increase', 3, 5, 8, '2026-09-19 16:34:31'),
(107, 1, 42, 'Increase', 3, 5, 8, '2026-09-19 16:34:36'),
(108, 1, 33, 'Increase', 4, 1, 5, '2026-09-19 16:34:42'),
(109, 1, 28, 'Increase', 6, 4, 10, '2026-09-19 16:34:48'),
(110, 1, 26, 'Increase', 4, 4, 8, '2026-09-19 16:34:53'),
(111, 1, 51, 'Increase', 6, 0, 6, '2026-09-20 03:04:38'),
(112, 1, 52, 'Increase', 3, 0, 3, '2026-09-20 03:07:03'),
(113, 1, 53, 'Increase', 10, 0, 10, '2026-09-20 03:08:48'),
(114, 1, 54, 'Increase', 4, 0, 4, '2026-09-20 03:11:44'),
(115, 1, 55, 'Increase', 7, 0, 7, '2026-09-20 03:12:51'),
(116, 1, 56, 'Increase', 5, 0, 5, '2026-09-20 03:14:14'),
(117, 1, 57, 'Increase', 6, 0, 6, '2026-09-20 03:15:45'),
(118, 1, 58, 'Increase', 6, 0, 6, '2026-09-20 03:17:18'),
(119, 1, 59, 'Increase', 8, 0, 8, '2026-09-20 03:19:05'),
(120, 1, 60, 'Increase', 4, 0, 4, '2026-09-20 03:20:37'),
(121, NULL, 49, 'Decrease', 1, 6, 5, '2026-09-30 02:39:41'),
(122, NULL, 42, 'Decrease', 2, 8, 6, '2026-09-30 03:43:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_no` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `new_password` varchar(255) NOT NULL,
  `confirm_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `user_name`, `email`, `phone_no`, `date_of_birth`, `new_password`, `confirm_password`, `created_at`) VALUES
(1, 'Sohel', 'Shrestha', 'sohel_stha', 'shrestha.sohel123@gmail.com', '9869223167', NULL, '$2y$10$OJMTwbTD6TMX18ktS9tr7eYNiARjqjkcQLBZkkp8DStxKfsqGlEHu', '$2y$10$OJMTwbTD6TMX18ktS9tr7eYNiARjqjkcQLBZkkp8DStxKfsqGlEHu', '2026-08-26 12:41:59'),
(2, 'Abhishek', 'Paudel', 'abhu_paudel365', 'paudelabhishek67@gmail.com', '9768432487', NULL, '$2y$10$EQkr7VdxDzguQtIL3k/NzOKeV8xKzH7HxEfPEnr2BwNw8quAnk5SG', '$2y$10$vxOyatEttZAUZzqNJY4kM.A1ooY7.h2gxQW8cE1TsjA1ph3tKR7t2', '2026-09-07 07:10:05'),
(8, 'Rubika', 'Limbu', 'rubka_lmb', 'trioasr@gmail.com', '9768234288', NULL, '$2y$10$iemPeWzsFMyib36L1ydDJONsk8QuaufKo57DFuMkju5aEwa7ifC8a', '$2y$10$iemPeWzsFMyib36L1ydDJONsk8QuaufKo57DFuMkju5aEwa7ifC8a', '2026-09-13 05:48:31'),
(16, 'Sohan', 'Kc', 'sohan_kc45', 'sosukeaizen308@gmail.com', '9811456790', '2000-07-19', '$2y$10$DIEpfMmLP8lq2lFe7RSBj.7rKo7MWrDTXrAxKzfUkrlzIEKxsSZW.', '$2y$10$DIEpfMmLP8lq2lFe7RSBj.7rKo7MWrDTXrAxKzfUkrlzIEKxsSZW.', '2026-09-15 04:55:25'),
(17, 'Maria', 'Thapa', 'maria_thp01', 'gotaku679@gmail.com', '9768223170', '1998-10-21', '$2y$10$7k89FxJ5Qog8/K4Qh3GGdOL0WnRFzdiycHVlta339p/Z9g0ovuf7W', '$2y$10$7k89FxJ5Qog8/K4Qh3GGdOL0WnRFzdiycHVlta339p/Z9g0ovuf7W', '2026-09-15 04:58:18'),
(18, 'Kishor', 'Shah', 'kishor_shah56', 'gojousaturo83@gmail.com', '9756345670', '2001-09-23', '$2y$10$GSRTNyqMsxGfwXpDGqAF0u78ABz3Hm5nXPbeO4spHdTrYuJ68S2Im', '$2y$10$GSRTNyqMsxGfwXpDGqAF0u78ABz3Hm5nXPbeO4spHdTrYuJ68S2Im', '2026-09-17 07:38:08'),
(19, 'Mohan', 'Gurung', 'mohan_grg78', 'genji.takiya3540@gmail.com', '9811904567', '2004-10-11', '$2y$10$zeyemsUAdu2IiJCoeavTX.pFC1JAkHJRYEo8P39vBQUYW.Ug5kJAG', '$2y$10$zeyemsUAdu2IiJCoeavTX.pFC1JAkHJRYEo8P39vBQUYW.Ug5kJAG', '2026-09-17 07:40:10'),
(20, 'Puja', 'Dong', 'puja_tmg8', 'deathwhite132@gmail.com', '9711230945', '2000-11-15', '$2y$10$EGdjous0B7LNo39BiD5tdeU4X8Cwc1x4Gmn1wp0BM7iuTvk42wyx6', '$2y$10$EGdjous0B7LNo39BiD5tdeU4X8Cwc1x4Gmn1wp0BM7iuTvk42wyx6', '2026-09-17 07:43:24');

-- --------------------------------------------------------

--
-- Table structure for table `user_product_activity`
--

CREATE TABLE `user_product_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_product_activity`
--

INSERT INTO `user_product_activity` (`id`, `user_id`, `product_id`, `activity_type`, `created_at`) VALUES
(24, 1, 10, 'Added to Cart', '2026-09-11 13:38:31'),
(28, 1, 10, 'Added to Cart', '2026-09-11 13:58:59'),
(29, 1, 11, 'Added to Cart', '2026-09-11 14:51:40'),
(30, 1, 10, 'Remove from Cart', '2026-09-11 15:47:58'),
(31, 1, 11, 'Remove from Cart', '2026-09-11 15:47:58'),
(32, 1, 10, 'Added to Cart', '2026-09-11 15:57:24'),
(33, 1, 10, 'Added to Cart', '2026-09-11 15:57:24'),
(34, 1, 10, 'Added to Cart', '2026-09-11 15:57:25'),
(35, 1, 10, 'Added to Cart', '2026-09-11 15:57:25'),
(36, 1, 10, 'Checkout', '2026-09-11 16:02:03'),
(37, 1, 5, 'Added to Cart', '2026-09-11 16:12:07'),
(38, 1, 5, 'Added to Cart', '2026-09-11 16:12:07'),
(39, 1, 5, 'Checkout', '2026-09-11 16:12:37'),
(40, 1, 5, 'Added to Cart', '2026-09-11 16:19:12'),
(41, 1, 5, 'Added to Cart', '2026-09-11 16:19:12'),
(42, 1, 5, 'Added to Cart', '2026-09-11 16:19:14'),
(43, 1, 5, 'Added to Cart', '2026-09-11 16:19:14'),
(44, 1, 5, 'Checkout', '2026-09-11 16:19:34'),
(45, 1, 5, 'Purchase', '2026-09-11 16:22:46'),
(46, 1, 9, 'Added to Cart', '2026-09-11 16:44:54'),
(47, 1, 9, 'Added to Cart', '2026-09-11 16:44:55'),
(48, 1, 9, 'Checkout', '2026-09-11 16:45:16'),
(49, 1, 9, 'Purchase', '2026-09-11 16:45:16'),
(50, 2, NULL, 'Logged In', '2026-09-12 04:07:02'),
(51, 2, NULL, 'Logged Out', '2026-09-12 04:10:17'),
(63, 1, NULL, 'Logged In', '2026-09-12 12:39:54'),
(64, 1, NULL, 'Logged Out', '2026-09-12 12:41:51'),
(65, 2, NULL, 'Logged In', '2026-09-12 12:42:03'),
(66, 2, NULL, 'Logged In', '2026-09-12 12:42:03'),
(67, 2, NULL, 'Logged Out', '2026-09-12 15:40:10'),
(70, 1, NULL, 'Logged In', '2026-09-13 05:18:20'),
(71, 1, NULL, 'Logged Out', '2026-09-13 05:35:22'),
(72, 2, NULL, 'Logged In', '2026-09-13 05:51:51'),
(73, 2, 10, 'Added to Cart', '2026-09-13 05:51:55'),
(74, 2, 10, 'Added to Cart', '2026-09-13 05:51:55'),
(75, 2, 10, 'Added to Cart', '2026-09-13 05:51:57'),
(76, 2, 10, 'Added to Cart', '2026-09-13 05:51:57'),
(77, 2, 10, 'Checkout', '2026-09-13 05:52:44'),
(78, 2, 10, 'Checkout', '2026-09-13 05:54:52'),
(79, 2, 10, 'Checkout', '2026-09-13 05:57:13'),
(80, 2, 10, 'Remove from Cart', '2026-09-13 06:19:48'),
(81, 1, NULL, 'Logged In', '2026-09-13 06:23:41'),
(82, 1, NULL, 'Logged Out', '2026-09-13 06:26:42'),
(83, 2, NULL, 'Logged In', '2026-09-13 06:26:57'),
(84, 2, 4, 'View', '2026-09-13 06:43:37'),
(85, 2, 4, 'View', '2026-09-13 06:45:03'),
(86, 2, NULL, 'Logged Out', '2026-09-13 07:00:13'),
(87, 1, NULL, 'Logged In', '2026-09-13 07:00:22'),
(88, 1, 7, 'View', '2026-09-13 07:02:39'),
(89, 1, 6, 'View', '2026-09-13 07:04:10'),
(90, 1, 5, 'Like', '2026-09-13 07:10:33'),
(91, 1, 5, 'Unlike', '2026-09-13 07:10:51'),
(92, 1, 7, 'View', '2026-09-13 07:12:00'),
(93, 1, 7, 'View', '2026-09-13 07:12:00'),
(94, 1, 7, 'View', '2026-09-13 07:12:00'),
(95, 1, 8, 'Like', '2026-09-13 07:12:10'),
(96, 1, 7, 'View', '2026-09-13 07:14:33'),
(97, 1, 8, 'Unlike', '2026-09-13 07:14:44'),
(98, 1, 10, 'Like', '2026-09-13 07:18:47'),
(99, 1, 10, 'Like', '2026-09-13 07:23:12'),
(100, 1, 10, 'Unlike', '2026-09-13 07:23:28'),
(101, 1, 8, 'Like', '2026-09-13 07:31:24'),
(102, 1, 8, 'Unlike', '2026-09-13 07:31:24'),
(103, 1, 8, 'Like', '2026-09-13 07:31:26'),
(104, 1, 8, 'Unlike', '2026-09-13 07:31:26'),
(105, 1, 8, 'Like', '2026-09-13 07:31:28'),
(106, 1, 8, 'Unlike', '2026-09-13 07:31:28'),
(107, 1, 8, 'Like', '2026-09-13 07:31:29'),
(108, 1, 8, 'Unlike', '2026-09-13 07:31:29'),
(109, 1, 8, 'Like', '2026-09-13 07:31:31'),
(110, 1, 8, 'Unlike', '2026-09-13 07:31:31'),
(111, 1, 8, 'Like', '2026-09-13 07:31:32'),
(112, 1, 8, 'Unlike', '2026-09-13 07:31:32'),
(113, 1, 8, 'Like', '2026-09-13 07:31:32'),
(114, 1, 8, 'Unlike', '2026-09-13 07:31:32'),
(115, 1, 8, 'Like', '2026-09-13 07:31:32'),
(116, 1, 8, 'Unlike', '2026-09-13 07:31:32'),
(117, 1, 11, 'Like', '2026-09-13 07:31:33'),
(118, 1, 11, 'Unlike', '2026-09-13 07:31:33'),
(119, 1, 6, 'Unlike', '2026-09-13 07:31:34'),
(120, 1, 6, 'Like', '2026-09-13 07:31:34'),
(121, 1, 6, 'Unlike', '2026-09-13 07:31:35'),
(122, 1, 6, 'Like', '2026-09-13 07:31:35'),
(123, 1, 8, 'Like', '2026-09-13 07:31:36'),
(124, 1, 8, 'Unlike', '2026-09-13 07:31:36'),
(125, 1, 8, 'Like', '2026-09-13 07:31:36'),
(126, 1, 8, 'Unlike', '2026-09-13 07:31:36'),
(127, 1, 8, 'Like', '2026-09-13 07:31:36'),
(128, 1, 8, 'Unlike', '2026-09-13 07:31:36'),
(129, 1, 8, 'Like', '2026-09-13 07:31:37'),
(130, 1, 8, 'Unlike', '2026-09-13 07:31:37'),
(131, 1, 8, 'Like', '2026-09-13 07:31:37'),
(132, 1, 8, 'Unlike', '2026-09-13 07:31:37'),
(133, 1, 8, 'Like', '2026-09-13 07:31:37'),
(134, 1, 8, 'Unlike', '2026-09-13 07:31:37'),
(135, 1, 8, 'Like', '2026-09-13 07:31:38'),
(136, 1, 8, 'Unlike', '2026-09-13 07:31:38'),
(137, 1, 8, 'Like', '2026-09-13 07:31:38'),
(138, 1, 8, 'Unlike', '2026-09-13 07:31:38'),
(139, 1, 8, 'Like', '2026-09-13 07:31:43'),
(140, 1, 8, 'Unlike', '2026-09-13 07:31:43'),
(141, 1, 8, 'Like', '2026-09-13 07:31:43'),
(142, 1, 8, 'Unlike', '2026-09-13 07:31:43'),
(143, 1, 8, 'Like', '2026-09-13 07:31:44'),
(144, 1, 8, 'Unlike', '2026-09-13 07:31:44'),
(145, 1, 8, 'Like', '2026-09-13 07:31:44'),
(146, 1, 8, 'Unlike', '2026-09-13 07:31:44'),
(147, 1, 8, 'Like', '2026-09-13 07:31:44'),
(148, 1, 8, 'Unlike', '2026-09-13 07:31:44'),
(149, 1, 8, 'Like', '2026-09-13 07:31:44'),
(150, 1, 8, 'Unlike', '2026-09-13 07:31:44'),
(151, 1, 8, 'Like', '2026-09-13 07:31:44'),
(152, 1, 8, 'Unlike', '2026-09-13 07:31:44'),
(153, 1, 8, 'Like', '2026-09-13 07:31:44'),
(154, 1, 8, 'Unlike', '2026-09-13 07:31:44'),
(155, 1, 8, 'Like', '2026-09-13 07:31:45'),
(156, 1, 8, 'Unlike', '2026-09-13 07:31:45'),
(157, 1, 8, 'Like', '2026-09-13 07:31:45'),
(158, 1, 8, 'Unlike', '2026-09-13 07:31:45'),
(159, 1, 8, 'Like', '2026-09-13 07:31:45'),
(160, 1, 8, 'Unlike', '2026-09-13 07:31:45'),
(161, 1, 8, 'Like', '2026-09-13 07:31:45'),
(162, 1, 8, 'Unlike', '2026-09-13 07:31:45'),
(163, 1, 8, 'Like', '2026-09-13 07:31:45'),
(164, 1, 8, 'Unlike', '2026-09-13 07:31:45'),
(165, 1, 8, 'Like', '2026-09-13 07:31:46'),
(166, 1, 8, 'Unlike', '2026-09-13 07:31:46'),
(167, 1, 8, 'Like', '2026-09-13 07:31:46'),
(168, 1, 8, 'Unlike', '2026-09-13 07:31:46'),
(169, 1, 8, 'Like', '2026-09-13 07:31:46'),
(170, 1, 8, 'Unlike', '2026-09-13 07:31:46'),
(171, 1, 8, 'Like', '2026-09-13 07:31:46'),
(172, 1, 8, 'Unlike', '2026-09-13 07:31:46'),
(173, 1, 8, 'Like', '2026-09-13 07:31:46'),
(174, 1, 8, 'Unlike', '2026-09-13 07:31:46'),
(175, 1, 8, 'Like', '2026-09-13 07:31:47'),
(176, 1, 8, 'Unlike', '2026-09-13 07:31:47'),
(177, 1, 8, 'Like', '2026-09-13 07:31:47'),
(178, 1, 8, 'Unlike', '2026-09-13 07:31:47'),
(179, 1, 8, 'Like', '2026-09-13 07:31:47'),
(180, 1, 8, 'Unlike', '2026-09-13 07:31:47'),
(181, 1, 8, 'Like', '2026-09-13 07:31:47'),
(182, 1, 8, 'Unlike', '2026-09-13 07:31:47'),
(183, 1, 8, 'Like', '2026-09-13 07:31:48'),
(184, 1, 8, 'Unlike', '2026-09-13 07:31:48'),
(185, 1, 6, 'Unlike', '2026-09-13 07:34:15'),
(186, 1, 6, 'Like', '2026-09-13 07:34:17'),
(187, 1, 7, 'Unlike', '2026-09-13 07:34:34'),
(188, 1, 7, 'Like', '2026-09-13 07:34:35'),
(189, 1, 7, 'Unlike', '2026-09-13 07:34:36'),
(190, 1, 7, 'Like', '2026-09-13 07:34:37'),
(191, 1, 7, 'Unlike', '2026-09-13 07:34:39'),
(192, 1, 7, 'Like', '2026-09-13 07:34:41'),
(193, 1, 7, 'View', '2026-09-13 07:34:43'),
(194, 1, 7, 'Unlike', '2026-09-13 07:34:45'),
(195, 1, 7, 'Like', '2026-09-13 07:34:46'),
(196, 1, 7, 'View', '2026-09-13 07:36:50'),
(197, 1, 11, 'View', '2026-09-13 08:16:17'),
(198, 1, 7, 'View', '2026-09-13 08:18:15'),
(199, 1, 11, 'View', '2026-09-13 08:18:33'),
(200, 1, NULL, 'Logged In', '2026-09-13 10:19:59'),
(201, 1, 6, 'Added to Cart', '2026-09-13 10:29:00'),
(202, 1, 6, 'Added to Cart', '2026-09-13 10:29:00'),
(203, 1, 6, 'Added to Cart', '2026-09-13 10:29:02'),
(204, 1, 6, 'Added to Cart', '2026-09-13 10:29:02'),
(205, 1, 4, 'Added to Cart', '2026-09-13 10:29:04'),
(206, 1, 4, 'Added to Cart', '2026-09-13 10:29:04'),
(207, 1, 4, 'Added to Cart', '2026-09-13 10:29:06'),
(208, 1, 4, 'Added to Cart', '2026-09-13 10:29:06'),
(209, 1, 4, 'Remove from Cart', '2026-09-13 10:29:14'),
(210, 1, 6, 'Remove from Cart', '2026-09-13 10:29:15'),
(235, NULL, NULL, 'Logged In', '2026-09-13 15:53:29'),
(236, NULL, 7, 'Added to Cart', '2026-09-13 15:53:32'),
(237, NULL, 7, 'Added to Cart', '2026-09-13 15:53:32'),
(238, NULL, 7, 'Like', '2026-09-13 15:53:33'),
(239, NULL, 4, 'Like', '2026-09-13 15:53:35'),
(240, NULL, 10, 'Added to Cart', '2026-09-13 15:53:36'),
(241, NULL, 10, 'Added to Cart', '2026-09-13 15:53:36'),
(242, NULL, 9, 'Like', '2026-09-13 15:53:38'),
(243, NULL, 9, 'Added to Cart', '2026-09-13 15:53:38'),
(244, NULL, 9, 'Added to Cart', '2026-09-13 15:53:38'),
(245, NULL, 9, 'View', '2026-09-13 15:53:39'),
(246, NULL, 8, 'View', '2026-09-13 15:53:45'),
(247, NULL, 8, 'View', '2026-09-13 15:53:53'),
(248, NULL, 8, 'View', '2026-09-13 15:53:59'),
(249, NULL, 8, 'View', '2026-09-13 15:54:03'),
(250, NULL, 10, 'View', '2026-09-13 15:54:15'),
(251, NULL, 10, 'View', '2026-09-13 15:54:23'),
(252, NULL, 7, 'Checkout', '2026-09-13 15:55:22'),
(253, NULL, 10, 'Checkout', '2026-09-13 15:55:22'),
(254, NULL, 9, 'Checkout', '2026-09-13 15:55:22'),
(255, NULL, 7, 'Purchase', '2026-09-13 15:55:22'),
(256, NULL, 10, 'Purchase', '2026-09-13 15:55:22'),
(257, NULL, 9, 'Purchase', '2026-09-13 15:55:22'),
(258, 1, NULL, 'Logged In', '2026-09-13 16:03:10'),
(259, 1, 11, 'View', '2026-09-13 16:03:12'),
(260, 1, 11, 'Added to Cart', '2026-09-13 16:03:13'),
(261, 1, 6, 'Added to Cart', '2026-09-13 16:03:24'),
(262, 1, 6, 'Added to Cart', '2026-09-13 16:03:24'),
(263, 1, 6, 'Remove from Cart', '2026-09-13 16:03:27'),
(264, 1, 11, 'View', '2026-09-13 16:03:30'),
(265, 1, 11, 'Like', '2026-09-13 16:03:33'),
(277, NULL, NULL, 'Logged In', '2026-09-14 04:24:24'),
(278, NULL, NULL, 'Logged In', '2026-09-14 04:24:24'),
(279, NULL, 8, 'Added to Cart', '2026-09-14 04:24:25'),
(280, NULL, 8, 'Added to Cart', '2026-09-14 04:24:25'),
(281, NULL, 8, 'Like', '2026-09-14 04:24:27'),
(282, NULL, 4, 'Like', '2026-09-14 04:24:29'),
(283, NULL, 10, 'Like', '2026-09-14 04:24:31'),
(284, NULL, 5, 'Like', '2026-09-14 04:24:33'),
(285, NULL, 6, 'Like', '2026-09-14 04:24:42'),
(286, NULL, 11, 'Added to Cart', '2026-09-14 04:24:49'),
(287, NULL, 11, 'Added to Cart', '2026-09-14 04:24:49'),
(288, NULL, 8, 'Checkout', '2026-09-14 04:25:28'),
(289, NULL, 11, 'Checkout', '2026-09-14 04:25:28'),
(290, NULL, 8, 'Purchase', '2026-09-14 04:25:28'),
(291, NULL, 11, 'Purchase', '2026-09-14 04:25:28'),
(292, NULL, NULL, 'Logged In', '2026-09-14 04:28:00'),
(293, NULL, 7, 'View', '2026-09-14 04:28:02'),
(294, NULL, 7, 'View', '2026-09-14 04:28:09'),
(295, NULL, 9, 'View', '2026-09-14 04:28:16'),
(296, NULL, 9, 'View', '2026-09-14 04:28:30'),
(297, NULL, 9, 'View', '2026-09-14 04:28:37'),
(298, NULL, 4, 'View', '2026-09-14 04:28:42'),
(299, NULL, 6, 'View', '2026-09-14 04:28:46'),
(300, NULL, 6, 'View', '2026-09-14 04:29:00'),
(301, 1, NULL, 'Logged In', '2026-09-14 04:34:31'),
(302, 1, 11, 'View', '2026-09-14 04:34:33'),
(303, 1, 11, 'View', '2026-09-14 04:34:44'),
(304, 1, 11, 'View', '2026-09-14 04:37:48'),
(305, 1, 11, 'View', '2026-09-14 04:39:57'),
(306, 1, 11, 'View', '2026-09-14 04:39:58'),
(307, 1, 11, 'View', '2026-09-14 04:39:58'),
(308, 1, 11, 'View', '2026-09-14 04:41:14'),
(309, 1, 11, 'View', '2026-09-14 04:41:18'),
(310, 1, 11, 'View', '2026-09-14 04:45:30'),
(311, 1, 11, 'View', '2026-09-14 04:45:34'),
(312, 1, 11, 'Added to Cart', '2026-09-14 04:45:49'),
(313, 1, 11, 'Added to Cart', '2026-09-14 04:45:49'),
(314, 1, 11, 'Remove from Cart', '2026-09-14 04:45:58'),
(315, 1, 11, 'View', '2026-09-14 04:46:01'),
(316, 1, 11, 'View', '2026-09-14 04:49:10'),
(317, 1, 11, 'Added to Cart', '2026-09-14 04:49:11'),
(318, 1, 11, 'View', '2026-09-14 04:50:41'),
(319, 1, 11, 'Added to Cart', '2026-09-14 04:50:43'),
(320, 1, 11, 'Added to Cart', '2026-09-14 04:50:43'),
(321, 1, 11, 'Remove from Cart', '2026-09-14 04:52:39'),
(322, 1, 11, 'View', '2026-09-14 04:52:41'),
(323, 1, 11, 'View', '2026-09-14 04:52:50'),
(324, 1, 11, 'View', '2026-09-14 05:29:56'),
(325, 1, 11, 'Added to Cart', '2026-09-14 05:29:57'),
(326, 1, 11, 'View', '2026-09-14 05:30:32'),
(327, 1, 11, 'View', '2026-09-14 05:30:32'),
(328, 1, 11, 'View', '2026-09-14 05:30:33'),
(329, 1, 11, 'View', '2026-09-14 05:30:33'),
(330, 1, 11, 'View', '2026-09-14 05:30:33'),
(331, 1, 11, 'View', '2026-09-14 05:30:43'),
(332, 1, 11, 'View', '2026-09-14 05:36:10'),
(333, 1, 8, 'Added to Cart', '2026-09-14 05:36:32'),
(334, 1, 8, 'Added to Cart', '2026-09-14 05:36:33'),
(335, 1, 8, 'View', '2026-09-14 05:39:16'),
(336, 1, 6, 'View', '2026-09-14 05:43:36'),
(337, 1, 6, 'Added to Cart', '2026-09-14 05:43:39'),
(338, 1, 6, 'Remove from Cart', '2026-09-14 05:52:04'),
(339, 1, 8, 'Remove from Cart', '2026-09-14 05:52:06'),
(340, 1, 11, 'Remove from Cart', '2026-09-14 05:52:07'),
(341, 1, 6, 'View', '2026-09-14 05:56:43'),
(342, 1, 6, 'Added to Cart', '2026-09-14 05:56:44'),
(343, 1, 6, 'Added to Cart', '2026-09-14 05:56:48'),
(344, 1, 6, 'Added to Cart', '2026-09-14 05:56:48'),
(345, 1, 8, 'Added to Cart', '2026-09-14 05:57:02'),
(346, 1, 8, 'Added to Cart', '2026-09-14 05:57:02'),
(347, 1, 6, 'Checkout', '2026-09-14 06:01:11'),
(348, 1, 8, 'Checkout', '2026-09-14 06:01:11'),
(349, 1, 6, 'View', '2026-09-14 06:02:16'),
(350, 1, 6, 'View', '2026-09-14 06:05:04'),
(351, 1, 6, 'Remove from Cart', '2026-09-14 06:05:22'),
(352, 1, 8, 'Remove from Cart', '2026-09-14 06:05:23'),
(353, 1, 4, 'View', '2026-09-14 06:05:32'),
(354, 1, 4, 'View', '2026-09-14 06:05:44'),
(355, 1, 4, 'View', '2026-09-14 06:06:39'),
(356, 1, 4, 'View', '2026-09-14 06:06:48'),
(357, 1, 4, 'View', '2026-09-14 06:16:32'),
(358, 1, 4, 'View', '2026-09-14 06:17:07'),
(359, 1, 4, 'View', '2026-09-14 06:20:08'),
(360, 1, 4, 'View', '2026-09-14 06:20:26'),
(361, 1, 11, 'Added to Cart', '2026-09-14 06:20:39'),
(362, 1, 11, 'Added to Cart', '2026-09-14 06:20:39'),
(363, 1, 4, 'View', '2026-09-14 06:20:47'),
(364, 1, 4, 'Added to Cart', '2026-09-14 06:20:48'),
(365, 1, 4, 'Added to Cart', '2026-09-14 06:20:52'),
(366, 1, 4, 'Remove from Cart', '2026-09-14 06:20:56'),
(367, 1, 11, 'Remove from Cart', '2026-09-14 06:20:57'),
(368, 1, 11, 'View', '2026-09-14 06:29:24'),
(369, 1, 11, 'View', '2026-09-14 06:29:47'),
(370, 1, 11, 'Added to Cart', '2026-09-14 06:29:51'),
(371, 1, 11, 'Remove from Cart', '2026-09-14 06:29:54'),
(372, 1, 11, 'View', '2026-09-14 06:30:00'),
(387, 1, 15, 'View', '2026-09-14 07:10:32'),
(388, 1, 15, 'Like', '2026-09-14 07:10:35'),
(389, 1, 15, 'View', '2026-09-14 07:10:43'),
(390, 1, 15, 'Added to Cart', '2026-09-14 07:10:45'),
(391, 1, 15, 'Checkout', '2026-09-14 07:11:08'),
(392, 1, 15, 'Purchase', '2026-09-14 07:11:08'),
(393, 1, NULL, 'Logged Out', '2026-09-14 07:12:34'),
(394, 1, NULL, 'Logged In', '2026-09-14 07:12:43'),
(395, 1, 7, 'View', '2026-09-14 07:55:15'),
(396, 1, 7, 'View', '2026-09-14 08:05:51'),
(397, 1, 8, 'Added to Cart', '2026-09-14 08:11:39'),
(398, 1, 8, 'Added to Cart', '2026-09-14 08:11:39'),
(399, 1, 8, 'Remove from Cart', '2026-09-14 08:11:45'),
(400, 1, NULL, 'Logged Out', '2026-09-14 08:18:49'),
(401, 1, NULL, 'Logged In', '2026-09-15 02:12:45'),
(402, 1, NULL, 'Logged Out', '2026-09-15 03:02:02'),
(403, NULL, NULL, 'Logged In', '2026-09-15 03:04:09'),
(404, NULL, NULL, 'Logged In', '2026-09-15 03:07:04'),
(405, NULL, NULL, 'Logged Out', '2026-09-15 03:09:49'),
(406, NULL, NULL, 'Logged In', '2026-09-15 03:10:04'),
(407, NULL, NULL, 'Logged In', '2026-09-15 03:10:05'),
(408, 1, NULL, 'Logged In', '2026-09-15 04:16:22'),
(409, 1, 4, 'View', '2026-09-15 04:16:29'),
(410, 2, NULL, 'Logged In', '2026-09-15 04:58:44'),
(411, 2, 5, 'Added to Cart', '2026-09-15 04:58:55'),
(412, 2, 5, 'Added to Cart', '2026-09-15 04:58:55'),
(413, 2, 6, 'Added to Cart', '2026-09-15 04:58:57'),
(414, 2, 6, 'Added to Cart', '2026-09-15 04:58:57'),
(415, 2, 4, 'View', '2026-09-15 04:59:02'),
(416, 2, 4, 'Added to Cart', '2026-09-15 04:59:04'),
(417, 2, 4, 'View', '2026-09-15 04:59:15'),
(418, 2, 5, 'Checkout', '2026-09-15 04:59:44'),
(419, 2, 6, 'Checkout', '2026-09-15 04:59:44'),
(420, 2, 4, 'Checkout', '2026-09-15 04:59:44'),
(421, 2, 5, 'Purchase', '2026-09-15 04:59:45'),
(422, 2, 6, 'Purchase', '2026-09-15 04:59:45'),
(423, 2, 4, 'Purchase', '2026-09-15 04:59:45'),
(424, 8, NULL, 'Logged In', '2026-09-15 05:02:41'),
(425, 8, NULL, 'Logged In', '2026-09-15 05:02:41'),
(426, 8, 4, 'Added to Cart', '2026-09-15 05:02:48'),
(427, 8, 4, 'Added to Cart', '2026-09-15 05:02:48'),
(428, 8, 11, 'Added to Cart', '2026-09-15 05:02:50'),
(429, 8, 11, 'Added to Cart', '2026-09-15 05:02:50'),
(430, 8, 6, 'Like', '2026-09-15 05:02:53'),
(431, 8, 10, 'View', '2026-09-15 05:02:55'),
(432, 8, 10, 'Like', '2026-09-15 05:02:57'),
(433, 8, 10, 'View', '2026-09-15 05:03:21'),
(434, 8, 4, 'Checkout', '2026-09-15 05:04:01'),
(435, 8, 11, 'Checkout', '2026-09-15 05:04:01'),
(436, 8, 4, 'Purchase', '2026-09-15 05:04:01'),
(437, 8, 11, 'Purchase', '2026-09-15 05:04:01'),
(438, 8, 20, 'View', '2026-09-15 05:04:09'),
(439, 8, 20, 'View', '2026-09-15 05:04:45'),
(440, 8, 20, 'Added to Cart', '2026-09-15 05:04:50'),
(441, 8, 20, 'Added to Cart', '2026-09-15 05:04:52'),
(442, 8, 20, 'Added to Cart', '2026-09-15 05:04:53'),
(443, 8, 20, 'Added to Cart', '2026-09-15 05:04:54'),
(444, 8, 20, 'Added to Cart', '2026-09-15 05:04:55'),
(445, 8, 20, 'Added to Cart', '2026-09-15 05:05:02'),
(446, 8, 20, 'Checkout', '2026-09-15 05:05:31'),
(447, 8, 20, 'Purchase', '2026-09-15 05:05:31'),
(448, 2, 17, 'View', '2026-09-15 05:05:40'),
(449, 2, 17, 'Like', '2026-09-15 05:05:43'),
(450, 2, 17, 'Unlike', '2026-09-15 05:05:45'),
(451, 2, 17, 'Like', '2026-09-15 05:05:48'),
(452, 2, 17, 'Unlike', '2026-09-15 05:05:53'),
(453, 2, 17, 'Like', '2026-09-15 05:05:55'),
(454, 2, 17, 'View', '2026-09-15 05:06:16'),
(455, 2, 20, 'View', '2026-09-15 05:18:22'),
(456, 2, 20, 'View', '2026-09-15 05:18:38'),
(457, 2, 21, 'View', '2026-09-15 05:18:49'),
(458, 2, 21, 'View', '2026-09-15 05:19:09'),
(459, 2, NULL, 'Logged Out', '2026-09-15 05:23:18'),
(460, 16, NULL, 'Logged In', '2026-09-15 05:23:26'),
(461, 16, 7, 'Like', '2026-09-15 05:23:28'),
(462, 16, 11, 'Like', '2026-09-15 05:23:30'),
(463, 16, 21, 'Like', '2026-09-15 05:23:33'),
(464, 16, 22, 'Like', '2026-09-15 05:23:34'),
(465, 16, 22, 'Added to Cart', '2026-09-15 05:23:36'),
(466, 16, 22, 'Added to Cart', '2026-09-15 05:23:36'),
(467, 16, 22, 'Added to Cart', '2026-09-15 05:23:38'),
(468, 16, 22, 'Added to Cart', '2026-09-15 05:23:38'),
(469, 16, 11, 'Added to Cart', '2026-09-15 05:23:40'),
(470, 16, 11, 'Added to Cart', '2026-09-15 05:23:40'),
(471, 16, 11, 'Added to Cart', '2026-09-15 05:23:44'),
(472, 16, 11, 'Added to Cart', '2026-09-15 05:23:44'),
(473, 16, 22, 'Checkout', '2026-09-15 05:24:27'),
(474, 16, 11, 'Checkout', '2026-09-15 05:24:27'),
(475, 16, 22, 'Purchase', '2026-09-15 05:24:27'),
(476, 16, 11, 'Purchase', '2026-09-15 05:24:27'),
(477, 16, 8, 'View', '2026-09-15 05:25:23'),
(478, 16, 8, 'View', '2026-09-15 05:25:35'),
(479, 16, 8, 'View', '2026-09-15 05:25:56'),
(480, 16, 7, 'View', '2026-09-15 05:26:05'),
(481, 16, 7, 'View', '2026-09-15 05:26:24'),
(482, 8, NULL, 'Logged Out', '2026-09-15 05:26:52'),
(483, 17, NULL, 'Logged In', '2026-09-15 05:27:13'),
(484, 17, NULL, 'Logged In', '2026-09-15 05:27:13'),
(485, 17, 21, 'View', '2026-09-15 05:27:18'),
(486, 17, 21, 'View', '2026-09-15 05:27:45'),
(487, 17, 21, 'Like', '2026-09-15 05:27:47'),
(488, 17, 4, 'View', '2026-09-15 05:27:55'),
(489, 17, 4, 'View', '2026-09-15 05:28:07'),
(490, 17, 20, 'View', '2026-09-15 05:28:26'),
(491, 17, 20, 'View', '2026-09-15 05:28:57'),
(492, 17, 10, 'Added to Cart', '2026-09-15 05:29:22'),
(493, 17, 10, 'Added to Cart', '2026-09-15 05:29:23'),
(494, 17, 10, 'Added to Cart', '2026-09-15 05:29:24'),
(495, 17, 10, 'Added to Cart', '2026-09-15 05:29:24'),
(496, 17, 18, 'Added to Cart', '2026-09-15 05:29:26'),
(497, 17, 18, 'Added to Cart', '2026-09-15 05:29:27'),
(498, 17, 18, 'Added to Cart', '2026-09-15 05:29:28'),
(499, 17, 18, 'Added to Cart', '2026-09-15 05:29:28'),
(500, 17, 22, 'Added to Cart', '2026-09-15 05:29:31'),
(501, 17, 22, 'Added to Cart', '2026-09-15 05:29:31'),
(502, 17, 10, 'Checkout', '2026-09-15 05:30:27'),
(503, 17, 18, 'Checkout', '2026-09-15 05:30:27'),
(504, 17, 22, 'Checkout', '2026-09-15 05:30:27'),
(505, 17, 10, 'Purchase', '2026-09-15 05:30:27'),
(506, 17, 18, 'Purchase', '2026-09-15 05:30:27'),
(507, 17, 22, 'Purchase', '2026-09-15 05:30:27'),
(508, 1, NULL, 'Logged In', '2026-09-15 12:23:33'),
(509, 1, 17, 'View', '2026-09-15 12:32:47'),
(510, 1, 17, 'View', '2026-09-15 12:33:02'),
(511, 1, 17, 'View', '2026-09-15 12:33:05'),
(512, 1, 20, 'View', '2026-09-15 12:38:10'),
(513, 1, 20, 'View', '2026-09-15 12:38:13'),
(514, 1, 21, 'View', '2026-09-15 12:49:54'),
(515, 1, 22, 'View', '2026-09-15 12:50:09'),
(516, 1, NULL, 'Logged In', '2026-09-16 02:52:26'),
(517, 1, 4, 'View', '2026-09-16 03:05:29'),
(518, 1, 22, 'View', '2026-09-16 03:06:08'),
(519, 1, 18, 'View', '2026-09-16 03:14:09'),
(520, 1, 18, 'View', '2026-09-16 03:14:19'),
(521, 1, 20, 'View', '2026-09-16 03:14:36'),
(522, 1, 11, 'View', '2026-09-16 03:18:49'),
(523, 1, 11, 'View', '2026-09-16 03:21:57'),
(524, 1, 32, 'View', '2026-09-16 03:30:27'),
(525, 1, 4, 'View', '2026-09-16 03:36:56'),
(526, 1, 4, 'View', '2026-09-16 03:37:02'),
(527, 1, 36, 'View', '2026-09-16 03:42:11'),
(528, 1, 40, 'View', '2026-09-16 03:48:41'),
(529, 1, NULL, 'Logged In', '2026-09-16 06:46:37'),
(530, 1, 17, 'Added to Cart', '2026-09-16 06:46:39'),
(531, 1, 17, 'Added to Cart', '2026-09-16 06:46:39'),
(532, 1, 17, 'Remove from Cart', '2026-09-16 06:47:00'),
(533, 1, 17, 'Added to Cart', '2026-09-16 06:56:58'),
(534, 1, 17, 'Added to Cart', '2026-09-16 06:56:58'),
(535, 1, 17, 'Checkout', '2026-09-16 06:57:30'),
(536, 1, 17, 'Remove from Cart', '2026-09-16 06:57:54'),
(537, 1, 20, 'Added to Cart', '2026-09-16 07:28:42'),
(538, 1, 20, 'Added to Cart', '2026-09-16 07:28:42'),
(539, 1, 20, 'Checkout', '2026-09-16 07:29:13'),
(540, 1, 20, 'Remove from Cart', '2026-09-16 07:31:31'),
(541, 1, 21, 'Added to Cart', '2026-09-16 07:31:52'),
(542, 1, 21, 'Added to Cart', '2026-09-16 07:31:52'),
(543, 1, 21, 'Remove from Cart', '2026-09-16 07:53:21'),
(544, 1, 40, 'Added to Cart', '2026-09-16 07:59:11'),
(545, 1, 40, 'Added to Cart', '2026-09-16 07:59:11'),
(546, 1, 40, 'Checkout', '2026-09-16 07:59:37'),
(547, 1, 40, 'Purchase', '2026-09-16 08:00:39'),
(548, 1, NULL, 'Logged Out', '2026-09-16 08:01:58'),
(549, 1, NULL, 'Logged In', '2026-09-16 08:04:07'),
(550, 1, 11, 'Added to Cart', '2026-09-16 08:07:00'),
(551, 1, 11, 'Added to Cart', '2026-09-16 08:07:00'),
(552, 1, 21, 'Added to Cart', '2026-09-16 08:07:01'),
(553, 1, 21, 'Added to Cart', '2026-09-16 08:07:01'),
(554, 1, 11, 'Checkout', '2026-09-16 08:10:54'),
(555, 1, 21, 'Checkout', '2026-09-16 08:10:54'),
(556, 1, 11, 'Purchase', '2026-09-16 08:10:54'),
(557, 1, 21, 'Purchase', '2026-09-16 08:10:54'),
(558, 1, 4, 'View', '2026-09-16 08:11:48'),
(559, 1, NULL, 'Logged In', '2026-09-16 13:19:48'),
(560, 1, NULL, 'Logged In', '2026-09-17 07:30:13'),
(561, 1, NULL, 'Logged In', '2026-09-17 07:30:13'),
(562, 1, 28, 'View', '2026-09-17 07:30:16'),
(563, 1, 28, 'Like', '2026-09-17 07:30:19'),
(564, 1, NULL, 'Logged Out', '2026-09-17 07:30:41'),
(565, 18, NULL, 'Logged In', '2026-09-17 07:44:24'),
(566, 18, 21, 'Like', '2026-09-17 07:44:26'),
(567, 18, 20, 'Like', '2026-09-17 07:44:29'),
(568, 18, 39, 'Like', '2026-09-17 07:44:33'),
(569, 18, 35, 'Like', '2026-09-17 07:44:34'),
(570, 18, 37, 'Like', '2026-09-17 07:44:36'),
(571, 18, 27, 'Like', '2026-09-17 07:44:39'),
(572, 18, 28, 'Like', '2026-09-17 07:44:39'),
(573, 18, 37, 'View', '2026-09-17 07:44:42'),
(574, 18, 37, 'View', '2026-09-17 07:44:58'),
(575, 18, 37, 'View', '2026-09-17 07:45:06'),
(576, 18, 20, 'View', '2026-09-17 07:45:14'),
(577, 18, 20, 'View', '2026-09-17 07:45:27'),
(578, 18, 42, 'Like', '2026-09-17 07:45:51'),
(579, 18, 40, 'Like', '2026-09-17 07:45:54'),
(580, 18, 37, 'Added to Cart', '2026-09-17 07:46:12'),
(581, 18, 37, 'Added to Cart', '2026-09-17 07:46:13'),
(582, 18, 20, 'Added to Cart', '2026-09-17 07:46:16'),
(583, 18, 20, 'Added to Cart', '2026-09-17 07:46:16'),
(584, 18, 37, 'Checkout', '2026-09-17 07:47:00'),
(585, 18, 20, 'Checkout', '2026-09-17 07:47:00'),
(586, 18, 37, 'Purchase', '2026-09-17 07:47:00'),
(587, 18, 20, 'Purchase', '2026-09-17 07:47:00'),
(588, 18, 39, 'View', '2026-09-17 07:47:13'),
(589, 18, 8, 'View', '2026-09-17 07:47:20'),
(590, 18, 8, 'View', '2026-09-17 07:47:40'),
(591, 18, 7, 'View', '2026-09-17 07:47:53'),
(592, 18, 7, 'View', '2026-09-17 07:48:06'),
(593, 18, NULL, 'Logged Out', '2026-09-17 07:48:35'),
(594, 19, NULL, 'Logged In', '2026-09-17 07:48:54'),
(595, 19, 9, 'Like', '2026-09-17 07:48:59'),
(596, 19, 42, 'Like', '2026-09-17 07:49:02'),
(597, 19, 41, 'Like', '2026-09-17 07:49:03'),
(598, 19, 40, 'Like', '2026-09-17 07:49:03'),
(599, 19, 28, 'Like', '2026-09-17 07:49:08'),
(600, 19, 27, 'Like', '2026-09-17 07:49:09'),
(601, 19, 9, 'View', '2026-09-17 07:49:31'),
(602, 19, 9, 'View', '2026-09-17 07:49:45'),
(603, 19, 42, 'View', '2026-09-17 07:49:50'),
(604, 19, 42, 'View', '2026-09-17 07:50:08'),
(605, 19, 28, 'View', '2026-09-17 07:50:22'),
(606, 19, 28, 'View', '2026-09-17 07:50:32'),
(607, 19, 27, 'View', '2026-09-17 07:50:39'),
(608, 19, 27, 'View', '2026-09-17 07:50:55'),
(609, 19, 28, 'Added to Cart', '2026-09-17 07:51:03'),
(610, 19, 28, 'Added to Cart', '2026-09-17 07:51:03'),
(611, 19, 27, 'Added to Cart', '2026-09-17 07:51:05'),
(612, 19, 27, 'Added to Cart', '2026-09-17 07:51:05'),
(613, 19, 42, 'Added to Cart', '2026-09-17 07:51:12'),
(614, 19, 42, 'Added to Cart', '2026-09-17 07:51:12'),
(615, 19, 42, 'Added to Cart', '2026-09-17 07:51:14'),
(616, 19, 42, 'Added to Cart', '2026-09-17 07:51:14'),
(617, 19, 40, 'Added to Cart', '2026-09-17 07:51:16'),
(618, 19, 40, 'Added to Cart', '2026-09-17 07:51:16'),
(619, 19, 27, 'Added to Cart', '2026-09-17 07:51:17'),
(620, 19, 27, 'Added to Cart', '2026-09-17 07:51:17'),
(621, 19, 28, 'Checkout', '2026-09-17 07:52:02'),
(622, 19, 27, 'Checkout', '2026-09-17 07:52:02'),
(623, 19, 42, 'Checkout', '2026-09-17 07:52:02'),
(624, 19, 40, 'Checkout', '2026-09-17 07:52:02'),
(625, 19, 28, 'Purchase', '2026-09-17 07:52:02'),
(626, 19, 27, 'Purchase', '2026-09-17 07:52:02'),
(627, 19, 42, 'Purchase', '2026-09-17 07:52:02'),
(628, 19, 40, 'Purchase', '2026-09-17 07:52:02'),
(629, 19, NULL, 'Logged Out', '2026-09-17 07:53:34'),
(630, 20, NULL, 'Logged In', '2026-09-17 07:54:01'),
(631, 20, 34, 'Like', '2026-09-17 07:54:07'),
(632, 20, 31, 'Like', '2026-09-17 07:54:09'),
(633, 20, 33, 'Like', '2026-09-17 07:54:10'),
(634, 20, 32, 'Like', '2026-09-17 07:54:11'),
(635, 20, 27, 'Like', '2026-09-17 07:54:12'),
(636, 20, 28, 'Like', '2026-09-17 07:54:13'),
(637, 20, 9, 'Like', '2026-09-17 07:54:17'),
(638, 20, 10, 'Like', '2026-09-17 07:54:17'),
(639, 20, 17, 'Like', '2026-09-17 07:54:19'),
(640, 20, 36, 'View', '2026-09-17 07:54:25'),
(641, 20, 36, 'View', '2026-09-17 07:54:39'),
(642, 20, 34, 'View', '2026-09-17 07:54:44'),
(643, 20, 34, 'View', '2026-09-17 07:54:55'),
(644, 20, NULL, 'Logged Out', '2026-09-17 09:23:51'),
(645, 1, NULL, 'Logged In', '2026-09-18 08:55:43'),
(646, 1, 5, 'View', '2026-09-18 08:57:48'),
(647, 1, 5, 'Added to Cart', '2026-09-18 08:57:50'),
(648, 1, 5, 'Remove from Cart', '2026-09-18 08:57:55'),
(649, 1, 50, 'View', '2026-09-18 08:58:33'),
(650, 1, 50, 'View', '2026-09-18 08:58:46'),
(651, 1, 37, 'View', '2026-09-18 08:58:52'),
(652, 1, 37, 'View', '2026-09-18 08:59:24'),
(653, 1, 34, 'View', '2026-09-18 08:59:38'),
(654, 1, 34, 'View', '2026-09-18 08:59:52'),
(655, 2, NULL, 'Logged In', '2026-09-18 09:02:32'),
(656, 2, 50, 'View', '2026-09-18 09:02:38'),
(657, 2, 50, 'View', '2026-09-18 09:02:53'),
(658, 2, 28, 'View', '2026-09-18 09:03:03'),
(659, 2, 28, 'View', '2026-09-18 09:03:23'),
(660, 1, 25, 'View', '2026-09-18 09:04:06'),
(661, 1, 25, 'View', '2026-09-18 09:04:25'),
(662, 2, 44, 'View', '2026-09-18 09:04:35'),
(663, 2, 44, 'View', '2026-09-18 09:04:47'),
(664, 1, NULL, 'Logged Out', '2026-09-18 09:11:11'),
(665, 8, NULL, 'Logged In', '2026-09-18 09:11:29'),
(666, 8, 17, 'View', '2026-09-18 09:12:34'),
(667, 8, 17, 'View', '2026-09-18 09:12:46'),
(668, 2, NULL, 'Logged Out', '2026-09-18 09:12:55'),
(669, 16, NULL, 'Logged In', '2026-09-18 09:13:10'),
(670, 16, 42, 'View', '2026-09-18 09:13:13'),
(671, 16, 42, 'View', '2026-09-18 09:13:27'),
(672, 16, 21, 'View', '2026-09-18 09:13:39'),
(673, 16, 21, 'View', '2026-09-18 09:13:49'),
(674, 16, 10, 'View', '2026-09-18 09:13:54'),
(675, 16, 10, 'View', '2026-09-18 09:14:05'),
(676, 16, 34, 'View', '2026-09-18 09:14:09'),
(677, 16, 34, 'View', '2026-09-18 09:14:23'),
(678, 16, 8, 'View', '2026-09-18 09:14:31'),
(679, 16, 50, 'View', '2026-09-18 09:14:42'),
(680, 16, 50, 'View', '2026-09-18 09:14:54'),
(681, 8, 36, 'View', '2026-09-18 09:15:00'),
(682, 8, 36, 'View', '2026-09-18 09:15:10'),
(683, 8, 17, 'View', '2026-09-18 09:15:17'),
(684, 8, 49, 'View', '2026-09-18 09:15:28'),
(685, 8, 49, 'View', '2026-09-18 09:15:38'),
(686, 8, 35, 'View', '2026-09-18 09:15:42'),
(687, 8, 44, 'View', '2026-09-18 09:15:49'),
(688, 8, 44, 'View', '2026-09-18 09:16:05'),
(689, 8, 41, 'View', '2026-09-18 09:16:11'),
(690, 8, 40, 'View', '2026-09-18 09:16:17'),
(691, 8, 6, 'View', '2026-09-18 09:16:29'),
(692, 8, 6, 'View', '2026-09-18 09:16:39'),
(693, 8, NULL, 'Logged Out', '2026-09-18 09:17:09'),
(694, 16, NULL, 'Logged Out', '2026-09-18 09:17:13'),
(695, 1, NULL, 'Logged In', '2026-09-19 05:16:37'),
(696, 18, NULL, 'Logged In', '2026-09-19 16:17:23'),
(697, 18, 50, 'View', '2026-09-19 16:17:30'),
(698, 18, 50, 'View', '2026-09-19 16:17:44'),
(699, 19, NULL, 'Logged In', '2026-09-19 16:18:00'),
(700, 19, 10, 'View', '2026-09-19 16:18:02'),
(701, 19, 10, 'View', '2026-09-19 16:18:19'),
(702, 19, 8, 'View', '2026-09-19 16:18:35'),
(703, 19, 8, 'View', '2026-09-19 16:18:57'),
(704, 19, 20, 'View', '2026-09-19 16:19:00'),
(705, 19, 20, 'View', '2026-09-19 16:19:20'),
(706, 19, 35, 'View', '2026-09-19 16:19:31'),
(707, 19, 17, 'View', '2026-09-19 16:19:40'),
(708, 19, 17, 'View', '2026-09-19 16:19:53'),
(709, 18, 17, 'View', '2026-09-19 16:20:12'),
(710, 18, 17, 'View', '2026-09-19 16:20:21'),
(711, 18, 42, 'View', '2026-09-19 16:20:34'),
(712, 18, 42, 'View', '2026-09-19 16:21:00'),
(713, 19, 41, 'View', '2026-09-19 16:21:11'),
(714, 19, 42, 'View', '2026-09-19 16:21:18'),
(715, 19, 40, 'View', '2026-09-19 16:21:35'),
(716, 19, 40, 'View', '2026-09-19 16:21:52'),
(717, 19, 46, 'View', '2026-09-19 16:21:59'),
(718, 19, 45, 'View', '2026-09-19 16:22:04'),
(719, 19, 44, 'View', '2026-09-19 16:22:09'),
(720, 19, 44, 'View', '2026-09-19 16:22:31'),
(721, 18, 44, 'View', '2026-09-19 16:22:43'),
(722, 18, 44, 'View', '2026-09-19 16:22:58'),
(723, 20, NULL, 'Logged In', '2026-09-19 16:24:18'),
(724, 17, NULL, 'Logged In', '2026-09-19 16:24:41'),
(725, 17, 20, 'View', '2026-09-19 16:24:48'),
(726, 17, 11, 'View', '2026-09-19 16:25:02'),
(727, 17, 11, 'View', '2026-09-19 16:25:22'),
(728, 17, 42, 'View', '2026-09-19 16:25:28'),
(729, 17, 42, 'View', '2026-09-19 16:25:43'),
(730, 18, 49, 'View', '2026-09-19 16:25:59'),
(731, 18, 49, 'View', '2026-09-19 16:26:15'),
(732, 18, 50, 'View', '2026-09-19 16:26:26'),
(733, 17, 50, 'View', '2026-09-19 16:26:37'),
(734, 17, 50, 'View', '2026-09-19 16:27:02'),
(735, 20, 50, 'View', '2026-09-19 16:27:15'),
(736, 20, 50, 'View', '2026-09-19 16:27:31'),
(737, 19, 49, 'Added to Cart', '2026-09-19 16:29:38'),
(738, 19, 49, 'Added to Cart', '2026-09-19 16:29:39'),
(739, 19, 50, 'Added to Cart', '2026-09-19 16:29:40'),
(740, 19, 50, 'Added to Cart', '2026-09-19 16:29:40'),
(741, 19, 48, 'Added to Cart', '2026-09-19 16:29:42'),
(742, 19, 48, 'Added to Cart', '2026-09-19 16:29:42'),
(743, 19, 49, 'Checkout', '2026-09-19 16:30:25'),
(744, 19, 50, 'Checkout', '2026-09-19 16:30:25'),
(745, 19, 48, 'Checkout', '2026-09-19 16:30:25'),
(746, 19, 49, 'Purchase', '2026-09-19 16:30:25'),
(747, 19, 50, 'Purchase', '2026-09-19 16:30:25'),
(748, 19, 48, 'Purchase', '2026-09-19 16:30:25'),
(749, 20, 27, 'Added to Cart', '2026-09-19 16:31:14'),
(750, 20, 27, 'Added to Cart', '2026-09-19 16:31:14'),
(751, 20, 28, 'Added to Cart', '2026-09-19 16:31:16'),
(752, 20, 28, 'Added to Cart', '2026-09-19 16:31:16'),
(753, 20, 34, 'Added to Cart', '2026-09-19 16:31:18'),
(754, 20, 34, 'Added to Cart', '2026-09-19 16:31:18'),
(755, 20, 27, 'Checkout', '2026-09-19 16:31:51'),
(756, 20, 28, 'Checkout', '2026-09-19 16:31:51'),
(757, 20, 34, 'Checkout', '2026-09-19 16:31:51'),
(758, 20, 27, 'Purchase', '2026-09-19 16:31:51'),
(759, 20, 28, 'Purchase', '2026-09-19 16:31:51'),
(760, 20, 34, 'Purchase', '2026-09-19 16:31:51'),
(761, 18, 40, 'Added to Cart', '2026-09-19 16:32:41'),
(762, 18, 40, 'Added to Cart', '2026-09-19 16:32:41'),
(763, 18, 40, 'Added to Cart', '2026-09-19 16:32:43'),
(764, 18, 40, 'Added to Cart', '2026-09-19 16:32:43'),
(765, 18, 40, 'Added to Cart', '2026-09-19 16:32:44'),
(766, 18, 40, 'Added to Cart', '2026-09-19 16:32:44'),
(767, 18, 40, 'Added to Cart', '2026-09-19 16:32:47'),
(768, 18, 40, 'Added to Cart', '2026-09-19 16:32:47'),
(769, 18, 40, 'Checkout', '2026-09-19 16:33:16'),
(770, 18, 40, 'Purchase', '2026-09-19 16:33:16'),
(771, 1, NULL, 'Logged In', '2026-09-20 03:01:23'),
(772, 1, NULL, 'Logged In', '2026-09-21 04:29:17'),
(773, 1, 60, 'View', '2026-09-21 04:29:24'),
(774, 1, 60, 'View', '2026-09-21 04:29:37'),
(775, 1, 59, 'View', '2026-09-21 04:29:43'),
(776, 1, 59, 'View', '2026-09-21 04:29:52'),
(777, 1, 58, 'View', '2026-09-21 04:29:56'),
(778, 1, 58, 'View', '2026-09-21 04:30:15'),
(779, 1, 57, 'View', '2026-09-21 04:30:21'),
(780, 1, 57, 'View', '2026-09-21 04:30:32'),
(781, 1, 56, 'View', '2026-09-21 04:30:38'),
(782, 1, 56, 'View', '2026-09-21 04:30:49'),
(783, 1, 55, 'View', '2026-09-21 04:30:57'),
(784, 1, 55, 'View', '2026-09-21 04:31:07'),
(785, 1, 54, 'View', '2026-09-21 04:31:13'),
(786, 1, 54, 'View', '2026-09-21 04:31:23'),
(787, 1, 53, 'View', '2026-09-21 04:31:28'),
(788, 1, 53, 'View', '2026-09-21 04:31:44'),
(789, 1, 52, 'View', '2026-09-21 04:31:53'),
(790, 1, 52, 'View', '2026-09-21 04:32:08'),
(791, 1, 51, 'View', '2026-09-21 04:32:16'),
(792, 1, 51, 'View', '2026-09-21 04:32:37'),
(793, 1, 50, 'View', '2026-09-21 04:32:43'),
(794, 1, 49, 'View', '2026-09-21 04:32:55'),
(795, 1, 49, 'View', '2026-09-21 04:33:12'),
(796, 1, 48, 'View', '2026-09-21 04:33:21'),
(797, 1, 48, 'View', '2026-09-21 04:33:55'),
(798, 1, 47, 'View', '2026-09-21 04:34:04'),
(799, 1, 47, 'View', '2026-09-21 04:34:18'),
(800, 1, 46, 'View', '2026-09-21 04:34:24'),
(801, 1, 46, 'View', '2026-09-21 04:34:42'),
(802, 1, 45, 'View', '2026-09-21 04:34:48'),
(803, 1, 45, 'View', '2026-09-21 04:35:02'),
(804, 1, 44, 'View', '2026-09-21 04:35:10'),
(805, 1, 44, 'View', '2026-09-21 04:35:28'),
(806, 1, 42, 'View', '2026-09-21 04:35:36'),
(807, 1, 42, 'View', '2026-09-21 04:35:55'),
(808, 1, 41, 'View', '2026-09-21 04:36:02'),
(809, 1, 41, 'View', '2026-09-21 04:36:18'),
(810, 1, 40, 'View', '2026-09-21 04:36:29'),
(811, 1, 40, 'View', '2026-09-21 04:36:49'),
(812, 1, 39, 'View', '2026-09-21 04:36:57'),
(813, 1, 39, 'View', '2026-09-21 04:37:27'),
(814, 1, 37, 'View', '2026-09-21 04:37:35'),
(815, 1, 36, 'View', '2026-09-21 04:37:45'),
(816, 1, 36, 'View', '2026-09-21 04:37:58'),
(817, 1, 35, 'View', '2026-09-21 04:38:05'),
(818, 1, 35, 'View', '2026-09-21 04:38:14'),
(819, 1, 34, 'View', '2026-09-21 04:38:20'),
(820, 1, 33, 'View', '2026-09-21 04:38:30'),
(821, 1, 33, 'View', '2026-09-21 04:38:45'),
(822, 1, 32, 'View', '2026-09-21 04:38:51'),
(823, 1, 32, 'View', '2026-09-21 04:39:12'),
(824, 1, 31, 'View', '2026-09-21 04:39:19'),
(825, 1, 31, 'View', '2026-09-21 04:39:30'),
(826, 1, 29, 'View', '2026-09-21 04:39:42'),
(827, 1, 29, 'View', '2026-09-21 04:39:56'),
(828, 1, 28, 'View', '2026-09-21 04:40:05'),
(829, 1, 28, 'View', '2026-09-21 04:40:20'),
(830, 1, 27, 'View', '2026-09-21 04:40:27'),
(831, 1, 27, 'View', '2026-09-21 04:40:42'),
(832, 1, 26, 'View', '2026-09-21 04:40:51'),
(833, 1, 26, 'View', '2026-09-21 04:41:27'),
(834, 1, 25, 'View', '2026-09-21 04:41:37'),
(835, 1, 22, 'View', '2026-09-21 04:41:52'),
(836, 1, 22, 'View', '2026-09-21 04:42:15'),
(837, 1, 21, 'View', '2026-09-21 04:42:22'),
(838, 1, 21, 'View', '2026-09-21 04:42:49'),
(839, 1, 20, 'View', '2026-09-21 04:42:59'),
(840, 1, 20, 'View', '2026-09-21 04:43:17'),
(841, 1, 18, 'View', '2026-09-21 04:43:28'),
(842, 1, 18, 'View', '2026-09-21 04:43:41'),
(843, 1, 17, 'View', '2026-09-21 04:43:49'),
(844, 1, 17, 'View', '2026-09-21 04:44:00'),
(845, 1, 11, 'View', '2026-09-21 04:44:12'),
(846, 1, 10, 'View', '2026-09-21 04:44:23'),
(847, 1, 10, 'View', '2026-09-21 04:44:37'),
(848, 1, 9, 'View', '2026-09-21 04:44:47'),
(849, 1, 9, 'View', '2026-09-21 04:44:59'),
(850, 1, 8, 'View', '2026-09-21 04:45:09'),
(851, 1, 8, 'View', '2026-09-21 04:45:25'),
(852, 1, 7, 'View', '2026-09-21 04:45:34'),
(853, 1, 6, 'View', '2026-09-21 04:45:43'),
(854, 1, 6, 'View', '2026-09-21 04:46:04'),
(855, 1, 5, 'View', '2026-09-21 04:46:09'),
(856, 1, 5, 'View', '2026-09-21 04:46:42'),
(857, 1, 4, 'View', '2026-09-21 04:46:51'),
(858, 1, NULL, 'Logged Out', '2026-09-21 04:47:14'),
(859, 2, NULL, 'Logged In', '2026-09-21 04:47:38'),
(860, 2, 60, 'View', '2026-09-21 04:47:49'),
(861, 2, 60, 'View', '2026-09-21 04:48:00'),
(862, 2, 59, 'View', '2026-09-21 04:48:04'),
(863, 2, 59, 'View', '2026-09-21 04:48:38'),
(864, 2, 58, 'View', '2026-09-21 04:48:45'),
(865, 2, 58, 'View', '2026-09-21 04:49:01'),
(866, 2, 57, 'View', '2026-09-21 04:49:08'),
(867, 2, 57, 'View', '2026-09-21 04:49:22'),
(868, 2, 56, 'View', '2026-09-21 04:49:28'),
(869, 2, 56, 'View', '2026-09-21 04:49:42'),
(870, 2, 56, 'View', '2026-09-21 04:49:49'),
(871, 2, 55, 'View', '2026-09-21 04:49:55'),
(872, 2, 55, 'View', '2026-09-21 04:50:10'),
(873, 2, 54, 'View', '2026-09-21 04:50:14'),
(874, 2, 54, 'View', '2026-09-21 04:50:29'),
(875, 2, 53, 'View', '2026-09-21 04:50:34'),
(876, 2, 53, 'View', '2026-09-21 04:50:49'),
(877, 2, 52, 'View', '2026-09-21 04:50:53'),
(878, 2, 52, 'View', '2026-09-21 04:51:12'),
(879, 2, 51, 'View', '2026-09-21 04:51:18'),
(880, 2, 51, 'View', '2026-09-21 04:51:33'),
(881, 2, 50, 'View', '2026-09-21 04:51:42'),
(882, 2, 49, 'View', '2026-09-21 04:51:50'),
(883, 2, 49, 'View', '2026-09-21 04:52:10'),
(884, 2, 48, 'View', '2026-09-21 04:52:16'),
(885, 2, 48, 'View', '2026-09-21 04:52:30'),
(886, 2, 47, 'View', '2026-09-21 04:52:43'),
(887, 2, 47, 'View', '2026-09-21 04:53:01'),
(888, 2, 46, 'View', '2026-09-21 04:53:08'),
(889, 2, 46, 'View', '2026-09-21 04:53:21'),
(890, 2, 45, 'View', '2026-09-21 04:53:26'),
(891, 2, 45, 'View', '2026-09-21 04:53:36'),
(892, 2, 44, 'View', '2026-09-21 04:53:42'),
(893, 2, 42, 'View', '2026-09-21 04:53:52'),
(894, 2, 42, 'View', '2026-09-21 04:54:35'),
(895, 2, 41, 'View', '2026-09-21 04:54:46'),
(896, 2, 41, 'View', '2026-09-21 04:54:56'),
(897, 2, 40, 'View', '2026-09-21 04:55:01'),
(898, 2, 40, 'View', '2026-09-21 04:55:12'),
(899, 2, 39, 'View', '2026-09-21 04:55:18'),
(900, 2, 39, 'View', '2026-09-21 04:55:37'),
(901, 2, 37, 'View', '2026-09-21 04:55:45'),
(902, 2, 37, 'View', '2026-09-21 04:56:05'),
(903, 2, 36, 'View', '2026-09-21 04:56:10'),
(904, 2, 36, 'View', '2026-09-21 04:56:28'),
(905, 2, 35, 'View', '2026-09-21 04:56:37'),
(906, 2, 35, 'View', '2026-09-21 04:56:57'),
(907, 2, 34, 'View', '2026-09-21 04:57:03'),
(908, 2, 34, 'View', '2026-09-21 04:57:35'),
(909, 2, 33, 'View', '2026-09-21 04:57:48'),
(910, 2, 33, 'View', '2026-09-21 04:58:04'),
(911, 2, 33, 'View', '2026-09-21 04:58:11'),
(912, 2, 32, 'View', '2026-09-21 04:59:44'),
(913, 2, 32, 'View', '2026-09-21 04:59:58'),
(914, 2, 31, 'View', '2026-09-21 05:00:08'),
(915, 2, 31, 'View', '2026-09-21 05:00:19'),
(916, 2, 29, 'View', '2026-09-21 05:00:26'),
(917, 2, 29, 'View', '2026-09-21 05:00:39'),
(918, 2, 28, 'View', '2026-09-21 05:00:46'),
(919, 2, 27, 'View', '2026-09-21 05:00:54'),
(920, 2, 27, 'View', '2026-09-21 05:01:08'),
(921, 2, 26, 'View', '2026-09-21 05:01:24'),
(922, 2, 26, 'View', '2026-09-21 05:01:45'),
(923, 2, 25, 'View', '2026-09-21 05:01:56'),
(924, 2, 25, 'View', '2026-09-21 05:02:14'),
(925, 2, 22, 'View', '2026-09-21 05:02:24'),
(926, 2, 22, 'View', '2026-09-21 05:02:35'),
(927, 2, 21, 'View', '2026-09-21 05:02:46'),
(928, 2, 20, 'View', '2026-09-21 05:02:55'),
(929, 2, 18, 'View', '2026-09-21 05:03:04'),
(930, 2, 18, 'View', '2026-09-21 05:03:14'),
(931, 2, 17, 'View', '2026-09-21 05:03:22'),
(932, 2, 11, 'View', '2026-09-21 05:03:33'),
(933, 2, 11, 'View', '2026-09-21 05:03:52'),
(934, 2, 10, 'View', '2026-09-21 05:04:01'),
(935, 2, 10, 'View', '2026-09-21 05:04:24'),
(936, 2, 9, 'View', '2026-09-21 05:04:31'),
(937, 2, 9, 'View', '2026-09-21 05:04:49'),
(938, 2, 8, 'View', '2026-09-21 05:04:57'),
(939, 2, 8, 'View', '2026-09-21 05:05:11'),
(940, 2, 7, 'View', '2026-09-21 05:05:18'),
(941, 2, 7, 'View', '2026-09-21 05:05:30'),
(942, 2, 6, 'View', '2026-09-21 05:05:38'),
(943, 2, 6, 'View', '2026-09-21 05:05:47'),
(944, 2, 5, 'View', '2026-09-21 05:05:54'),
(945, 2, 5, 'View', '2026-09-21 05:06:19'),
(946, 2, 4, 'View', '2026-09-21 05:06:28'),
(947, 2, NULL, 'Logged Out', '2026-09-21 05:07:33'),
(948, 8, NULL, 'Logged In', '2026-09-21 05:07:56'),
(949, 8, 60, 'View', '2026-09-21 05:07:59'),
(950, 8, 60, 'View', '2026-09-21 05:08:14'),
(951, 8, 48, 'View', '2026-09-21 05:08:16'),
(952, 8, 48, 'View', '2026-09-21 05:08:34'),
(953, 8, NULL, 'Logged In', '2026-09-22 07:59:43'),
(954, 8, 60, 'View', '2026-09-22 07:59:46'),
(955, 8, 59, 'View', '2026-09-22 07:59:58'),
(956, 8, 59, 'View', '2026-09-22 08:00:15'),
(957, 8, 58, 'View', '2026-09-22 08:00:21'),
(958, 8, 58, 'View', '2026-09-22 08:00:38'),
(959, 8, 57, 'View', '2026-09-22 08:00:46'),
(960, 8, 57, 'View', '2026-09-22 08:01:03'),
(961, 8, 56, 'View', '2026-09-22 08:01:09'),
(962, 8, 56, 'View', '2026-09-22 08:01:25'),
(963, 8, 55, 'View', '2026-09-22 08:01:34'),
(964, 8, 55, 'View', '2026-09-22 08:02:21'),
(965, 8, 54, 'View', '2026-09-22 08:02:32'),
(966, 8, 54, 'View', '2026-09-22 08:03:07'),
(967, 8, 53, 'View', '2026-09-22 08:03:14'),
(968, 8, 53, 'View', '2026-09-22 08:03:39'),
(969, 8, 52, 'View', '2026-09-22 08:03:50'),
(970, 8, 52, 'View', '2026-09-22 08:04:05'),
(971, 8, 51, 'View', '2026-09-22 08:04:16'),
(972, 8, 51, 'View', '2026-09-22 08:04:32'),
(973, 8, 50, 'View', '2026-09-22 08:04:40'),
(974, 8, 50, 'View', '2026-09-22 08:05:05'),
(975, 8, 49, 'View', '2026-09-22 08:05:26'),
(976, 8, 48, 'View', '2026-09-22 08:05:35'),
(977, 8, 47, 'View', '2026-09-22 08:05:42'),
(978, 8, 47, 'View', '2026-09-22 08:06:21'),
(979, 8, 46, 'View', '2026-09-22 08:06:30'),
(980, 8, 46, 'View', '2026-09-22 08:06:44'),
(981, 8, 45, 'View', '2026-09-22 08:06:56'),
(982, 8, 45, 'View', '2026-09-22 08:07:08'),
(983, 8, 44, 'View', '2026-09-22 08:07:18'),
(984, 8, 42, 'View', '2026-09-22 08:07:29'),
(985, 8, 42, 'View', '2026-09-22 08:08:12'),
(986, 18, NULL, 'Logged In', '2026-09-28 08:23:58'),
(987, 18, 60, 'View', '2026-09-28 08:24:07'),
(988, 18, 60, 'View', '2026-09-28 08:24:30'),
(989, 18, 59, 'View', '2026-09-28 08:24:36'),
(990, 18, 59, 'View', '2026-09-28 08:24:57'),
(991, 18, 58, 'View', '2026-09-28 08:25:04'),
(992, 18, 58, 'View', '2026-09-28 08:25:32'),
(993, 18, 57, 'View', '2026-09-28 08:25:36'),
(994, 18, 57, 'View', '2026-09-28 08:26:02'),
(995, 18, 56, 'View', '2026-09-28 08:26:10'),
(996, 18, 56, 'View', '2026-09-28 08:26:33'),
(997, 18, 55, 'View', '2026-09-28 08:26:40'),
(998, 18, 55, 'View', '2026-09-28 08:27:16'),
(999, 18, 54, 'View', '2026-09-28 08:27:20'),
(1000, 18, 54, 'View', '2026-09-28 08:28:40'),
(1001, 18, 53, 'View', '2026-09-28 08:28:48'),
(1002, 18, 53, 'View', '2026-09-28 08:29:13'),
(1003, 18, 52, 'View', '2026-09-28 08:29:17'),
(1004, 18, 52, 'View', '2026-09-28 08:29:32'),
(1005, 18, 51, 'View', '2026-09-28 08:29:41'),
(1006, 18, 51, 'View', '2026-09-28 08:30:05'),
(1007, 18, 50, 'View', '2026-09-28 08:30:14'),
(1008, 18, 49, 'View', '2026-09-28 08:30:25'),
(1009, 18, 48, 'View', '2026-09-28 08:30:34'),
(1010, 18, NULL, 'Logged Out', '2026-09-28 08:30:41'),
(1011, 19, NULL, 'Logged In', '2026-09-28 08:30:52'),
(1012, 19, 4, 'View', '2026-09-28 08:32:10'),
(1013, 19, 4, 'View', '2026-09-28 08:32:36'),
(1014, 19, 5, 'View', '2026-09-28 08:32:43'),
(1015, 19, 5, 'View', '2026-09-28 08:33:06'),
(1016, 19, 6, 'View', '2026-09-28 08:33:18'),
(1017, 19, 6, 'View', '2026-09-28 08:33:48'),
(1018, 19, 6, 'View', '2026-09-28 08:34:04'),
(1019, 19, 7, 'View', '2026-09-28 08:34:16'),
(1020, 19, 7, 'View', '2026-09-28 08:34:38'),
(1021, 19, 8, 'View', '2026-09-28 08:34:50'),
(1022, 19, 9, 'View', '2026-09-28 08:35:00'),
(1023, 19, 47, 'View', '2026-09-28 08:35:09'),
(1024, 19, 47, 'View', '2026-09-28 08:35:39'),
(1025, 19, 46, 'View', '2026-09-28 08:35:49'),
(1026, 19, 46, 'View', '2026-09-28 08:36:08'),
(1027, 19, 46, 'View', '2026-09-28 08:36:14'),
(1028, 19, 33, 'View', '2026-09-28 08:36:24'),
(1029, 19, 33, 'View', '2026-09-28 08:36:46'),
(1030, 19, 34, 'View', '2026-09-28 08:36:57'),
(1031, 19, 34, 'View', '2026-09-28 08:37:13'),
(1032, 19, 48, 'View', '2026-09-28 08:40:19'),
(1033, 19, 48, 'View', '2026-09-28 08:40:54'),
(1034, 19, 37, 'View', '2026-09-28 08:41:10'),
(1035, 19, 37, 'View', '2026-09-28 08:41:46'),
(1036, 19, 50, 'View', '2026-09-28 08:41:57'),
(1037, 19, 50, 'View', '2026-09-28 08:42:19'),
(1038, 1, NULL, 'Logged In', '2026-09-28 10:32:12'),
(1039, 1, 50, 'Added to Cart', '2026-09-28 10:32:14'),
(1040, 1, 50, 'Added to Cart', '2026-09-28 10:32:14'),
(1041, 1, 50, 'Checkout', '2026-09-28 10:32:33'),
(1042, 1, 50, 'Checkout', '2026-09-28 10:32:53'),
(1043, 1, NULL, 'Logged In', '2026-09-29 03:30:38'),
(1044, 1, 50, 'Remove from Cart', '2026-09-29 03:30:48'),
(1045, 1, NULL, 'Logged In', '2026-09-30 01:58:27'),
(1046, 2, NULL, 'Logged In', '2026-09-30 01:59:57'),
(1047, 1, NULL, 'Logged In', '2026-09-30 02:31:23'),
(1048, 1, 49, 'View', '2026-09-30 02:32:54'),
(1049, 1, 42, 'View', '2026-09-30 02:33:18'),
(1050, 1, 49, 'View', '2026-09-30 02:37:34'),
(1051, 1, 49, 'Added to Cart', '2026-09-30 02:37:43'),
(1052, 1, 49, 'Checkout', '2026-09-30 02:38:32'),
(1053, 1, 49, 'Checkout', '2026-09-30 02:39:20'),
(1054, 1, 49, 'Purchase', '2026-09-30 02:39:41'),
(1055, 1, 47, 'View', '2026-09-30 03:12:15'),
(1056, 1, 60, 'View', '2026-09-30 03:39:49'),
(1057, 1, 55, 'View', '2026-09-30 03:40:24'),
(1058, 1, 60, 'View', '2026-09-30 03:40:41'),
(1059, 1, 28, 'View', '2026-09-30 03:41:07'),
(1060, 1, 42, 'View', '2026-09-30 03:41:44'),
(1061, 1, 42, 'Added to Cart', '2026-09-30 03:41:55'),
(1062, 1, 42, 'Added to Cart', '2026-09-30 03:42:15'),
(1063, 1, 42, 'Checkout', '2026-09-30 03:42:55'),
(1064, 1, 42, 'Purchase', '2026-09-30 03:43:14'),
(1065, 1, 41, 'View', '2026-09-30 03:43:44'),
(1066, 1, 4, 'View', '2026-09-30 03:49:48'),
(1067, 1, 28, 'View', '2026-09-30 03:49:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `profile_picture`, `created_at`, `updated_at`) VALUES
(3, 1, 'profile_1_71c7038fdb875dd2.jpg', '2026-09-11 12:53:46', '2026-09-11 12:53:46'),
(4, 2, 'profile_2_fa057b629d6b9b49.jpg', '2026-09-11 12:54:35', '2026-09-11 12:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(7, 2, 11, '2026-09-13 06:52:26'),
(8, 2, 7, '2026-09-13 06:52:35'),
(13, 1, 10, '2026-09-13 07:18:47'),
(56, 1, 6, '2026-09-13 07:34:17'),
(60, 1, 7, '2026-09-13 07:34:46'),
(61, 9, 11, '2026-09-13 15:01:50'),
(62, 9, 5, '2026-09-13 15:02:29'),
(63, 9, 9, '2026-09-13 15:04:24'),
(64, 10, 7, '2026-09-13 15:53:33'),
(65, 10, 4, '2026-09-13 15:53:35'),
(66, 10, 9, '2026-09-13 15:53:38'),
(67, 1, 11, '2026-09-13 16:03:33'),
(68, 11, 5, '2026-09-14 04:12:28'),
(69, 11, 9, '2026-09-14 04:12:29'),
(70, 11, 4, '2026-09-14 04:12:30'),
(71, 11, 6, '2026-09-14 04:13:08'),
(72, 12, 8, '2026-09-14 04:24:27'),
(73, 12, 4, '2026-09-14 04:24:29'),
(74, 12, 10, '2026-09-14 04:24:31'),
(75, 12, 5, '2026-09-14 04:24:33'),
(76, 12, 6, '2026-09-14 04:24:42'),
(78, 1, 13, '2026-09-14 06:49:55'),
(79, 1, 15, '2026-09-14 07:10:35'),
(80, 8, 6, '2026-09-15 05:02:53'),
(81, 8, 10, '2026-09-15 05:02:57'),
(84, 2, 17, '2026-09-15 05:05:55'),
(85, 16, 7, '2026-09-15 05:23:28'),
(86, 16, 11, '2026-09-15 05:23:30'),
(87, 16, 21, '2026-09-15 05:23:33'),
(88, 16, 22, '2026-09-15 05:23:34'),
(89, 17, 21, '2026-09-15 05:27:47'),
(91, 18, 21, '2026-09-17 07:44:26'),
(92, 18, 20, '2026-09-17 07:44:29'),
(93, 18, 39, '2026-09-17 07:44:33'),
(94, 18, 35, '2026-09-17 07:44:34'),
(95, 18, 37, '2026-09-17 07:44:36'),
(96, 18, 27, '2026-09-17 07:44:39'),
(97, 18, 28, '2026-09-17 07:44:39'),
(98, 18, 42, '2026-09-17 07:45:51'),
(99, 18, 40, '2026-09-17 07:45:54'),
(100, 19, 9, '2026-09-17 07:48:59'),
(101, 19, 42, '2026-09-17 07:49:02'),
(102, 19, 41, '2026-09-17 07:49:03'),
(103, 19, 40, '2026-09-17 07:49:03'),
(104, 19, 28, '2026-09-17 07:49:08'),
(105, 19, 27, '2026-09-17 07:49:09'),
(106, 20, 34, '2026-09-17 07:54:07'),
(107, 20, 31, '2026-09-17 07:54:09'),
(108, 20, 33, '2026-09-17 07:54:10'),
(109, 20, 32, '2026-09-17 07:54:11'),
(110, 20, 27, '2026-09-17 07:54:12'),
(111, 20, 28, '2026-09-17 07:54:13'),
(115, 20, 42, '2026-09-17 08:45:26'),
(116, 20, 41, '2026-09-17 08:45:27'),
(117, 20, 40, '2026-09-17 08:45:28'),
(118, 1, 28, '2026-09-18 08:57:29'),
(119, 20, 36, '2026-09-19 16:28:37'),
(120, 19, 50, '2026-09-19 16:28:42'),
(121, 19, 21, '2026-09-19 16:28:45'),
(122, 19, 47, '2026-09-19 16:28:57'),
(123, 17, 50, '2026-09-19 16:29:09'),
(124, 17, 11, '2026-09-19 16:29:11'),
(125, 17, 49, '2026-09-19 16:29:13'),
(126, 8, 58, '2026-09-22 08:08:37'),
(127, 8, 55, '2026-09-22 08:08:39'),
(128, 8, 54, '2026-09-22 08:08:43'),
(129, 8, 5, '2026-09-22 08:08:48'),
(130, 8, 4, '2026-09-22 08:08:49'),
(131, 2, 58, '2026-09-30 02:00:09'),
(132, 2, 59, '2026-09-30 02:00:11'),
(133, 2, 36, '2026-09-30 02:00:17'),
(134, 2, 8, '2026-09-30 02:00:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_ibfk_1` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_otps`
--
ALTER TABLE `password_otps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `product_reviews_ibfk_2` (`user_id`);

--
-- Indexes for table `stock_activity`
--
ALTER TABLE `stock_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_no` (`phone_no`);

--
-- Indexes for table `user_product_activity`
--
ALTER TABLE `user_product_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_product_activity_ibfk_1` (`user_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `password_otps`
--
ALTER TABLE `password_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_activity`
--
ALTER TABLE `stock_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_product_activity`
--
ALTER TABLE `user_product_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1068;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_product_activity`
--
ALTER TABLE `user_product_activity`
  ADD CONSTRAINT `user_product_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_product_activity_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
