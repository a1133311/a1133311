-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1:3306
-- 產生時間： 2026-06-25 09:13:24
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `dancemaster`
--

-- --------------------------------------------------------

--
-- 資料表結構 `checkins`
--

CREATE TABLE `checkins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `checked_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `checkins`
--

INSERT INTO `checkins` (`id`, `user_id`, `video_id`, `checked_at`) VALUES
(1, 1, 6, '2026-06-15 22:31:57');

-- --------------------------------------------------------

--
-- 資料表結構 `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_role` tinyint(4) NOT NULL DEFAULT 0,
  `text` varchar(500) NOT NULL,
  `created` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `comments`
--

INSERT INTO `comments` (`id`, `video_id`, `user_id`, `user_name`, `user_role`, `text`, `created`) VALUES
(1, 1, 2, '林老師', 1, '副歌第 3 拍重心要再壓低，建議用 0.5x 先抓 weight shift，再回到原速。', '2026-05-29'),
(2, 1, 1, 'Chaewon', 0, '鏡面模式救了我，終於不用左右腦反轉了 🙏', '2026-05-29'),
(3, 6, 1, '喵', 0, '紅紅超讚<3', '2026-06-15');

-- --------------------------------------------------------

--
-- 資料表結構 `likes`
--

CREATE TABLE `likes` (
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `likes`
--

INSERT INTO `likes` (`user_id`, `video_id`, `created_at`) VALUES
(1, 6, '2026-06-15 22:23:39'),
(4, 5, '2026-06-15 22:22:16');

-- --------------------------------------------------------

--
-- 資料表結構 `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `from_uid` int(11) NOT NULL,
  `to_uid` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `sponsors`
--

INSERT INTO `sponsors` (`id`, `from_uid`, `to_uid`, `amount`, `created_at`) VALUES
(1, 1, 2, 100, '2026-06-15 22:22:44');

-- --------------------------------------------------------

--
-- 資料表結構 `subscriptions`
--

CREATE TABLE `subscriptions` (
  `user_id` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `handle` varchar(50) NOT NULL,
  `role` tinyint(4) NOT NULL DEFAULT 0,
  `bio` text DEFAULT NULL,
  `avatar_color` varchar(20) DEFAULT '#01696f',
  `created` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `name`, `handle`, `role`, `bio`, `avatar_color`, `created`) VALUES
(1, '喵', 'user1281', 1, '只是一個咪咪', '#437a22', '2026-06-15'),
(2, 'Chaewon', 'chaewon', 0, '資管系學生，正在學 K-pop。', '#01696f', '2026-05-01'),
(3, '林老師', 'teacher_lin', 1, '十年編舞經驗，專長 K-pop 與爵士。', '#a84b2f', '2026-04-10'),
(4, 'Dance Lab', 'dancelab', 1, '街舞工作室，每週更新教學。', '#7a39bb', '2026-04-20');

-- --------------------------------------------------------

--
-- 資料表結構 `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `desc` text DEFAULT NULL,
  `source` varchar(20) NOT NULL,
  `src` varchar(255) NOT NULL,
  `author` varchar(50) NOT NULL,
  `author_role` tinyint(4) NOT NULL DEFAULT 0,
  `owner_id` int(11) NOT NULL,
  `visibility` enum('public','private') NOT NULL DEFAULT 'private',
  `practice_count` int(11) NOT NULL DEFAULT 0,
  `likes` int(11) NOT NULL DEFAULT 0,
  `created` date NOT NULL,
  `cover` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `videos`
--

INSERT INTO `videos` (`id`, `title`, `desc`, `source`, `src`, `author`, `author_role`, `owner_id`, `visibility`, `practice_count`, `likes`, `created`, `cover`) VALUES
(1, 'K-pop 副歌 8 拍分解教學', '把副歌最難的 8 拍拆成慢動作，新手也能跟上。', 'youtube', 'dQw4w9WgXcQ', '林老師', 1, 2, 'public', 128, 64, '2026-05-28', 'https://images.unsplash.com/photo-1547153760-18fc86324498?w=800&q=70'),
(2, 'Hip-hop 基礎 Groove 律動', '從零開始抓 groove，先把重拍踩穩。', 'youtube', 'M7lc1UVf-VE', '林老師', 1, 2, 'public', 96, 41, '2026-05-30', 'https://images.unsplash.com/photo-1504609773096-104ff2c73ba4?w=800&q=70'),
(3, 'Waacking 手臂控制練習', '手臂線條與甩動的速度控制。', 'youtube', 'ScMzIvxBSi4', 'Dance Lab', 1, 3, 'public', 74, 33, '2026-06-01', 'https://images.unsplash.com/photo-1524117074681-31bd4de22ad3?w=800&q=70'),
(4, 'Locking Point 定點訓練', 'Locking 的瞬間定格與 point 練習。', 'youtube', 'C0DPdy98e4c', 'Dance Lab', 1, 3, 'public', 51, 22, '2026-06-02', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800&q=70'),
(5, '爵士 Isolation 身體分離', '胸腔、肩膀、髖部的分離控制。', 'youtube', 'kffacxfA7G4', '林老師', 1, 2, 'public', 40, 19, '2026-06-03', 'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?w=800&q=70'),
(6, 'redred', '', 'youtube', '8trNT1WjIg0', '林老師', 1, 3, 'public', 3, 1, '2026-06-15', 'https://img.youtube.com/vi/8trNT1WjIg0/hqdefault.jpg');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `checkins`
--
ALTER TABLE `checkins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `video_id` (`video_id`);

--
-- 資料表索引 `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_id` (`video_id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`user_id`,`video_id`),
  ADD KEY `video_id` (`video_id`);

--
-- 資料表索引 `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_uid` (`from_uid`),
  ADD KEY `to_uid` (`to_uid`);

--
-- 資料表索引 `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`user_id`,`target_user_id`),
  ADD KEY `target_user_id` (`target_user_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `handle` (`handle`);

--
-- 資料表索引 `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `checkins`
--
ALTER TABLE `checkins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

