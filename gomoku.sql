-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2024-12-15 17:26:43
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `gomoku`
--
CREATE DATABASE IF NOT EXISTS `gomoku` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gomoku`;

-- --------------------------------------------------------

--
-- 資料表結構 `games`
--
-- 建立時間： 2024-12-15 14:48:59
--

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `black_player_id` int(11) DEFAULT NULL,
  `white_player_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `game_type` enum('pvp','pve') NOT NULL,
  `moves` text NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `games`
--

TRUNCATE TABLE `games`;
-- --------------------------------------------------------

--
-- 資料表結構 `users`
--
-- 建立時間： 2024-12-07 15:49:51
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rating` int(11) DEFAULT 1500,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `draws` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 資料表新增資料前，先清除舊資料 `users`
--

TRUNCATE TABLE `users`;
--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `rating`, `wins`, `losses`, `draws`, `created_at`, `updated_at`) VALUES
(1, '123', '123', '', 1500, 0, 0, 0, '2024-12-15 14:49:10', '2024-12-15 14:49:10'),
(8, '123456', '$2y$10$Xk6SI14QCL.ZJCPJ4GyFkeIxxusavPpco6EReuRcByqhE9C3fQZe6', '1@gmail.com', 1500, 0, 0, 0, '2024-12-15 16:08:21', '2024-12-15 16:08:21'),
(9, '123456789', '$2y$10$RmC9thbyX35q2//L.KzYqexSj0hFhHX6qIsfzzg3OlNoC1H925w5K', 'nutc112025@gmail.com', 1500, 0, 0, 0, '2024-12-15 16:08:51', '2024-12-15 16:08:51'),
(10, '張宸碩', '$2y$10$xR5R9mkOYLmE66yYMxk8v.sJIpERbbKMe8Gx6rfxW8cMx6th76hw6', '422@example.com', 1500, 0, 0, 0, '2024-12-15 16:13:20', '2024-12-15 16:13:20'),
(11, 's1110931025', '$2y$10$DMz/5Lq7SpcFqCfC4WDGp.BcJi0CJaXzCGQAYbnmML/jaky14pNBm', 's1110931025@gmail.com', 1500, 0, 0, 0, '2024-12-15 16:16:04', '2024-12-15 16:16:04'),
(12, '1110931025', '$2y$10$ME/orGtokJwTj5HdbiFb5O8hdRRgjnft1peyF0wrkaiqQasxQIhOu', '1110931025@gmail.com', 1500, 0, 0, 0, '2024-12-15 16:20:52', '2024-12-15 16:20:52');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `black_player_id` (`black_player_id`),
  ADD KEY `white_player_id` (`white_player_id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`black_player_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`white_player_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `games_ibfk_3` FOREIGN KEY (`winner_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
