-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2025 at 09:12 AM
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
-- Database: `sitokoperak`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`, `updated_at`) VALUES
(3, 4, NULL, '2025-11-21 23:59:01', '2025-11-21 23:59:01');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `produk_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foto_produk`
--

CREATE TABLE `foto_produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_foto_produk` varchar(255) NOT NULL,
  `produk_id` bigint(20) UNSIGNED NOT NULL,
  `file_foto_produk` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `foto_produk`
--

INSERT INTO `foto_produk` (`id`, `kode_foto_produk`, `produk_id`, `file_foto_produk`, `created_at`, `updated_at`) VALUES
(10, 'FT0010', 5, 'foto_produk/wedding-ring1.jpg', '2025-05-01 22:31:43', '2025-05-01 22:31:43'),
(11, 'FT0011', 6, 'foto_produk/kalung1.jpg', '2025-05-01 22:48:29', '2025-05-01 22:48:29'),
(12, 'FT0012', 6, 'foto_produk/kalung2.jpg', '2025-05-01 22:52:44', '2025-05-01 22:52:44'),
(13, 'FT0001', 7, 'foto_produk/cincin1.jpg', '2025-05-01 23:14:04', '2025-05-01 23:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_usaha`
--

CREATE TABLE `jenis_usaha` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_jenis_usaha` varchar(255) NOT NULL,
  `nama_jenis_usaha` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_usaha`
--

INSERT INTO `jenis_usaha` (`id`, `kode_jenis_usaha`, `nama_jenis_usaha`, `created_at`, `updated_at`) VALUES
(1, 'JNS001', 'Logam', '2025-04-30 09:56:37', '2025-04-30 09:56:37'),
(2, 'JNS002', 'Emas', '2025-04-30 09:56:46', '2025-04-30 09:56:46'),
(3, 'JNS003', 'Perak', '2025-04-30 09:56:58', '2025-04-30 09:56:58'),
(4, 'JNS004', 'Tembaga', '2025-04-30 09:57:11', '2025-04-30 09:57:11'),
(5, 'JNS005', 'Kuningan', '2025-04-30 09:57:21', '2025-04-30 09:57:21');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_produk`
--

CREATE TABLE `kategori_produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_kategori_produk` varchar(255) NOT NULL,
  `nama_kategori_produk` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_produk`
--

INSERT INTO `kategori_produk` (`id`, `kode_kategori_produk`, `nama_kategori_produk`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'KTG001', 'Tatah', 'tatah', '2025-04-30 10:01:09', '2025-04-30 10:01:09'),
(2, 'KTG002', 'Filigeri', 'filigeri', '2025-04-30 10:01:16', '2025-05-01 23:28:46'),
(3, 'KTG003', 'Wedding Ring', 'wedding-ring', '2025-04-30 10:01:23', '2025-04-30 10:01:23'),
(4, 'KTG004', 'CIncin Akik', 'cincin-akik', '2025-04-30 10:01:31', '2025-04-30 10:01:31'),
(5, 'KTG005', 'Aksesoris Manten', 'aksesoris-manten', '2025-04-30 10:01:40', '2025-04-30 10:01:40'),
(6, 'KTG006', 'Souvenir', 'souvenir', '2025-04-30 10:01:48', '2025-04-30 10:01:48');

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
(1, '2025_04_11_041024_create_sessions_tables', 1),
(2, '2025_04_17_150301_create_users_table', 1),
(3, '2025_04_17_151707_create_pengerajin_table', 1),
(4, '2025_04_17_151725_create_jenis_usaha_table', 1),
(5, '2025_04_17_151730_create_usaha_table', 1),
(6, '2025_04_17_151736_create_kategori_produk_table', 1),
(7, '2025_04_17_151750_create_produk_table', 1),
(8, '2025_04_17_151803_create_foto_produk_table', 1),
(9, '2025_04_17_151850_create_usaha_produk_table', 1),
(10, '2025_04_17_151851_create_usaha_jenis_table', 1),
(11, '2025_04_17_151852_create_usaha_pengerajin_table', 1),
(12, '2025_11_20_071644_add_profile_fields_to_users_table', 2),
(13, '2025_11_20_154623_create_carts_and_cart_items_tables', 3),
(14, '2025_11_22_055937_fix_cart_structure', 4),
(15, '2025_11_22_074346_create_orders_and_order_items_tables', 5);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `customer_address` text NOT NULL,
  `total_amount` bigint(20) NOT NULL,
  `status` enum('baru','dibayar','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'baru',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `customer_name`, `customer_phone`, `customer_address`, `total_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 'ORD-20251122-0001', 'Dave Aryanda Agape', '082125422017', 'jl samironobaru no 25', 115000000, 'baru', NULL, '2025-11-22 00:49:04', '2025-11-22 00:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `produk_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` bigint(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `produk_id`, `quantity`, `price_at_purchase`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 1, 25000000, '2025-11-22 00:49:04', '2025-11-22 00:49:04'),
(2, 1, 7, 1, 90000000, '2025-11-22 00:49:04', '2025-11-22 00:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `pengerajin`
--

CREATE TABLE `pengerajin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_pengerajin` varchar(255) NOT NULL,
  `nama_pengerajin` varchar(255) DEFAULT NULL,
  `jk_pengerajin` enum('P','W') DEFAULT NULL,
  `usia_pengerajin` int(11) DEFAULT NULL,
  `telp_pengerajin` varchar(255) DEFAULT NULL,
  `email_pengerajin` varchar(255) DEFAULT NULL,
  `alamat_pengerajin` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengerajin`
--

INSERT INTO `pengerajin` (`id`, `kode_pengerajin`, `nama_pengerajin`, `jk_pengerajin`, `usia_pengerajin`, `telp_pengerajin`, `email_pengerajin`, `alamat_pengerajin`, `created_at`, `updated_at`) VALUES
(1, 'PRJ001', 'Bambang Mursanyoto', 'P', 20, '0882319319391', 'bambang@gmail.com', 'Basen KG III / 161, RT 10 RW 04', '2025-04-30 09:52:20', '2025-04-30 09:52:20'),
(2, 'PRJ002', 'Ari Jatmiko', 'P', 30, '08343141431', 'ari@gmail.com', 'Basen RT 10 RW 04', '2025-04-30 09:53:47', '2025-04-30 09:53:47'),
(3, 'PRJ004', 'Sagiyo', 'P', 25, '087437747114124', 'sagiyo@gmail.com', 'Basen RT 12 RW 04', '2025-04-30 09:55:22', '2025-04-30 09:55:22'),
(4, 'PRJ003', 'Ike Yulianti', 'W', 27, '0873183818731', 'ika@gmail.com', 'Basen KG III / 199, RT 12 RW 04', '2025-04-30 09:56:15', '2025-04-30 09:56:15');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_produk` varchar(255) NOT NULL,
  `kategori_produk_id` bigint(20) UNSIGNED NOT NULL,
  `nama_produk` varchar(255) DEFAULT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `kode_produk`, `kategori_produk_id`, `nama_produk`, `deskripsi`, `harga`, `stok`, `slug`, `created_at`, `updated_at`) VALUES
(5, 'PRD005', 3, 'Cincin kawin berlian', 'Terbuat dari berlian murni', 20000000, 2, 'cincin-kawin-berlian', '2025-05-01 22:31:10', '2025-05-01 22:31:10'),
(6, 'PRD006', 5, 'Kalung Emas', 'Terbuat dari 10 gram emas  24 karat', 25000000, 2, 'kalung-emas', '2025-05-01 22:48:01', '2025-05-01 22:48:01'),
(7, 'PRD001', 3, 'Cincin Berlian', 'Terbuat dari berlian murni', 90000000, 1, 'cincin-berlian', '2025-05-01 23:13:46', '2025-05-01 23:13:46');

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
('6oAvZOOXjfj4GOtAXYYwJ0XWFNotbzswizxnMSyZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaDhZeTNBMTFjTG5DRFhORkRDQU80MFBtMVpPY2hQZDBaUTZOamlZcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758603774),
('geDSA3XTN9ylFsEXbYxfDYqHOt2jKCdRfeFg9Xyf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiajRNNE05bXI4R3plN2dQdWRuaVo0Zk5MS29PclRJRHBwT3k4SmRaZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758511453),
('IKq9sZuTGlqYwkYZe9MTTQb942nomrElQYxSxqRa', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNDV4VWVGNWo3bUxhODJiRjF4WHZVZlFjWnE0UjNURVNmVVJjQTRyTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWsva2F0ZWdvcmkvZmlsaWdlcmkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1758512647),
('jsrPPXsQJdJsBecua4wLxrngdgIKZLriW0hEcSQC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTldVVVV6RGgxeXBQeWx3VWhDNlNlVUdaOHg2ajVSRWV1anZsTUNRQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758512680),
('LkTwamZRsIKLfGQ5VBIGA7wEvPQrHUiafA5DYDb6', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiU2thelJpNHM5V0V2VXp3anhhbDB3cHJhTWw0Z2g5anlsYkhGWGhiUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWsva2F0ZWdvcmkvZmlsaWdlcmkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1758512710),
('NIHggM4kPp3M1W6D3dgrd0LyR0xR0mLGGEhStauk', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic1dkVE5rVnVrTXpWU3lId3U0STJKcXhXcU82TmRKUHgwOERJckJ3diI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWsva2F0ZWdvcmkvZmlsaWdlcmkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1758516027),
('pjcEYNnmNQRsbmU0HD7ptkIxb0AEQwM0eT0VF7Jm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQlBFVjZzOU1ZN2R3REpHRW90dmdaSmdZQ0JmQXMyTmRLc1Jtc1NHYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb250YWN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1758512653),
('Q1TIBRiAFjRWBhxOYYSZh9kJuNu7LRYPpZQmdzaO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUZDYUplY2xRRFU1bThRZW90SWVCb2dQZXNzNDlSWGIxNHpXSnIzcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hYm91dCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758512640),
('RFu0cavtye8XGz0tq9iISqDU9ONSEHfdWIyW6YGw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibjl4WGxDWFc5U1B5dXhOTjZHMW42bzZGMEtXcWJJUDhIY0xsUzJpbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wcm9kdWsva2F0ZWdvcmkvZmlsaWdlcmkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1758516834),
('RJaQt0yvxGZ9OXxnfe7COXpRJUs0OBnXFYVIxQV1', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRkdYSUtaZkJYR1JCYTI2WURXdFJ6bmJOTEhsNFAwU0F4OFpyNGdqUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758512660),
('RQHiq8PYka6NXSYzh9ZpfsoI5W24wvtOitVrTm41', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWxUbEVrOG8zdGdmVDd1WmVVUlpBdWNlMlk0VWh4MVZ0UGlOT0RjeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1758511944);

-- --------------------------------------------------------

--
-- Table structure for table `usaha`
--

CREATE TABLE `usaha` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_usaha` varchar(255) NOT NULL,
  `nama_usaha` varchar(255) DEFAULT NULL,
  `telp_usaha` varchar(255) DEFAULT NULL,
  `email_usaha` varchar(255) DEFAULT NULL,
  `deskripsi_usaha` varchar(255) DEFAULT NULL,
  `foto_usaha` varchar(255) DEFAULT NULL,
  `link_gmap_usaha` varchar(255) DEFAULT NULL,
  `status_usaha` enum('aktif','nonaktif','tutup','pending','dibekukan') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usaha`
--

INSERT INTO `usaha` (`id`, `kode_usaha`, `nama_usaha`, `telp_usaha`, `email_usaha`, `deskripsi_usaha`, `foto_usaha`, `link_gmap_usaha`, `status_usaha`, `created_at`, `updated_at`) VALUES
(1, 'USH001', 'G - Silver', '0831921930108', 'jaya@gmail.com', 'Berdiri sejak 1990, bergerak di bidang kuningan', 'foto_usaha/kerajinan-perak-kota-ged.jpeg', 'https://maps.app.goo.gl/NY4TZwgqztCJvEQx5', 'aktif', '2025-04-30 09:58:36', '2025-04-30 09:58:36'),
(2, 'USH002', 'Jaya Sentosa', '08319219301090', 'sentosa@gmail.com', 'Berdiri sejak 2009, bergerak di bidang emas', 'foto_usaha/usaha3.jpg', 'https://maps.app.goo.gl/wpQK3CL3BTeijsb49', 'nonaktif', '2025-04-30 10:00:03', '2025-04-30 10:00:03'),
(3, 'USH003', 'Jaya Hoki Abis', '08319219301044', 'hoki@gmail.com', 'Berdiri sejak 2005, bergerak di bidang perak', 'foto_usaha/usaha2.jpeg', 'https://maps.app.goo.gl/wpQK3CL3BTeijsb49', 'aktif', '2025-04-30 10:00:57', '2025-04-30 10:00:57');

-- --------------------------------------------------------

--
-- Table structure for table `usaha_jenis`
--

CREATE TABLE `usaha_jenis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_usaha_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usaha_jenis`
--

INSERT INTO `usaha_jenis` (`id`, `usaha_id`, `jenis_usaha_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-04-30 10:10:34', '2025-04-30 10:10:34'),
(2, 1, 3, '2025-04-30 10:10:41', '2025-04-30 10:10:41'),
(3, 2, 5, '2025-04-30 10:10:49', '2025-04-30 10:10:49'),
(4, 1, 5, '2025-04-30 10:10:58', '2025-04-30 10:10:58'),
(5, 2, 2, '2025-04-30 10:11:08', '2025-04-30 10:11:08'),
(6, 3, 2, '2025-04-30 10:11:14', '2025-04-30 10:11:14'),
(7, 3, 3, '2025-04-30 10:11:24', '2025-04-30 10:11:24'),
(8, 3, 4, '2025-04-30 10:11:34', '2025-04-30 10:11:34'),
(9, 1, 5, '2025-05-01 05:22:54', '2025-05-01 05:22:54');

-- --------------------------------------------------------

--
-- Table structure for table `usaha_pengerajin`
--

CREATE TABLE `usaha_pengerajin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `pengerajin_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usaha_pengerajin`
--

INSERT INTO `usaha_pengerajin` (`id`, `usaha_id`, `pengerajin_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-04-30 10:09:42', '2025-04-30 10:09:42'),
(2, 1, 2, '2025-04-30 10:09:49', '2025-04-30 10:09:49'),
(3, 1, 3, '2025-04-30 10:09:56', '2025-04-30 10:09:56'),
(4, 2, 1, '2025-04-30 10:10:02', '2025-04-30 10:10:02'),
(5, 2, 3, '2025-04-30 10:10:10', '2025-04-30 10:10:10'),
(6, 3, 4, '2025-04-30 10:10:19', '2025-04-30 10:10:19'),
(7, 3, 2, '2025-04-30 10:10:26', '2025-04-30 10:10:26');

-- --------------------------------------------------------

--
-- Table structure for table `usaha_produk`
--

CREATE TABLE `usaha_produk` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usaha_id` bigint(20) UNSIGNED NOT NULL,
  `produk_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guest') NOT NULL DEFAULT 'guest',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `phone`, `address`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', NULL, 'admin123@example.com', NULL, NULL, '$2y$12$009.trbsYSPUPvfdbHso1O7S8pJQUeaoG9HdtdNuuquMtFikgXIR6', 'admin', '2025-04-30 09:20:48', '2025-04-30 09:20:48'),
(2, 'ikan', NULL, 'ikan@gmail.com', NULL, NULL, '$2y$12$hZgXidGqZRhISgUmqwMhw.K8E35ZVuytcjb0EWUTA38MtH03dH.RO', 'guest', '2025-09-21 20:31:54', '2025-09-21 20:31:54'),
(4, 'ikan_tongkol', 'Dave Aryanda Agape', 'agadape@gmail.com', '082125422017', 'jl samironobaru no 25', '$2y$12$hY/nDWWD3aQ2smwbMJbJ1uJ8IlzAJfuNOVBZCrws3GhO.qvIfR1gm', 'guest', '2025-11-21 08:10:03', '2025-11-21 08:10:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_id_index` (`user_id`),
  ADD KEY `carts_session_id_index` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_cart_id_produk_id_unique` (`cart_id`,`produk_id`),
  ADD KEY `cart_items_produk_id_foreign` (`produk_id`);

--
-- Indexes for table `foto_produk`
--
ALTER TABLE `foto_produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `foto_produk_kode_foto_produk_unique` (`kode_foto_produk`),
  ADD KEY `foto_produk_produk_id_foreign` (`produk_id`);

--
-- Indexes for table `jenis_usaha`
--
ALTER TABLE `jenis_usaha`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jenis_usaha_kode_jenis_usaha_unique` (`kode_jenis_usaha`);

--
-- Indexes for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kategori_produk_kode_kategori_produk_unique` (`kode_kategori_produk`),
  ADD UNIQUE KEY `kategori_produk_slug_unique` (`slug`);

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
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_foreign` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_produk_id_foreign` (`produk_id`);

--
-- Indexes for table `pengerajin`
--
ALTER TABLE `pengerajin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengerajin_kode_pengerajin_unique` (`kode_pengerajin`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produk_kode_produk_unique` (`kode_produk`),
  ADD UNIQUE KEY `produk_slug_unique` (`slug`),
  ADD KEY `produk_kategori_produk_id_foreign` (`kategori_produk_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `usaha`
--
ALTER TABLE `usaha`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usaha_kode_usaha_unique` (`kode_usaha`);

--
-- Indexes for table `usaha_jenis`
--
ALTER TABLE `usaha_jenis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usaha_jenis_usaha_id_foreign` (`usaha_id`),
  ADD KEY `usaha_jenis_jenis_usaha_id_foreign` (`jenis_usaha_id`);

--
-- Indexes for table `usaha_pengerajin`
--
ALTER TABLE `usaha_pengerajin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usaha_pengerajin_usaha_id_foreign` (`usaha_id`),
  ADD KEY `usaha_pengerajin_pengerajin_id_foreign` (`pengerajin_id`);

--
-- Indexes for table `usaha_produk`
--
ALTER TABLE `usaha_produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usaha_produk_usaha_id_foreign` (`usaha_id`),
  ADD KEY `usaha_produk_produk_id_foreign` (`produk_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `foto_produk`
--
ALTER TABLE `foto_produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jenis_usaha`
--
ALTER TABLE `jenis_usaha`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengerajin`
--
ALTER TABLE `pengerajin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usaha`
--
ALTER TABLE `usaha`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usaha_jenis`
--
ALTER TABLE `usaha_jenis`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `usaha_pengerajin`
--
ALTER TABLE `usaha_pengerajin`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `usaha_produk`
--
ALTER TABLE `usaha_produk`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `foto_produk`
--
ALTER TABLE `foto_produk`
  ADD CONSTRAINT `foto_produk_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_kategori_produk_id_foreign` FOREIGN KEY (`kategori_produk_id`) REFERENCES `kategori_produk` (`id`);

--
-- Constraints for table `usaha_jenis`
--
ALTER TABLE `usaha_jenis`
  ADD CONSTRAINT `usaha_jenis_jenis_usaha_id_foreign` FOREIGN KEY (`jenis_usaha_id`) REFERENCES `jenis_usaha` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usaha_jenis_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usaha_pengerajin`
--
ALTER TABLE `usaha_pengerajin`
  ADD CONSTRAINT `usaha_pengerajin_pengerajin_id_foreign` FOREIGN KEY (`pengerajin_id`) REFERENCES `pengerajin` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usaha_pengerajin_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usaha_produk`
--
ALTER TABLE `usaha_produk`
  ADD CONSTRAINT `usaha_produk_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usaha_produk_usaha_id_foreign` FOREIGN KEY (`usaha_id`) REFERENCES `usaha` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
