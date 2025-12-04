
--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
CREATE TABLE `actions` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `action` TEXT NOT NULL,
  `balanceBefore` REAL NOT NULL,
  `balanceAfter` REAL NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--

-- Table structure for table `bonus_levels`
--

DROP TABLE IF EXISTS `bonus_levels`;
CREATE TABLE `bonus_levels` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT DEFAULT NULL,
  `goal` INTEGER DEFAULT NULL,
  `reward` REAL NOT NULL DEFAULT '0.00',
  `background` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--

-- Table structure for table `bonuse_logs`
--

DROP TABLE IF EXISTS `bonuse_logs`;
CREATE TABLE `bonuse_logs` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `size` REAL NOT NULL,
  `type` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

--

-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
CREATE TABLE `games` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `game` TEXT NOT NULL,
  `bet` REAL NOT NULL,
  `chance` REAL NOT NULL,
  `win` REAL NOT NULL,
  `type` TEXT DEFAULT NULL,
  `fake` INTEGER NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--

-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `migration` TEXT NOT NULL,
  `batch` INTEGER NOT NULL
);

--

-- Table structure for table `mines`
--

DROP TABLE IF EXISTS `mines`;
CREATE TABLE `mines` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `amount` REAL NOT NULL,
  `bombs` INTEGER NOT NULL,
  `step` INTEGER NOT NULL DEFAULT '0',
  `grid` TEXT DEFAULT NULL,
  `status` INTEGER NOT NULL DEFAULT '0',
  `fake` INTEGER NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

--
-- Table structure for table `payments``
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `sum` REAL NOT NULL,
  `bonus` REAL NOT NULL DEFAULT '0.00',
  `wager` REAL DEFAULT NULL,
  `status` INTEGER NOT NULL DEFAULT '0',
  `system` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

--
-- Table structure for table `plinko_data``
--

DROP TABLE IF EXISTS `plinko_data`;
CREATE TABLE `plinko_data` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `data` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);


--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `owner_id` TEXT DEFAULT NULL,
  `post_id` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--
-- Table structure for table `profit``
--

DROP TABLE IF EXISTS `profit`;
CREATE TABLE `profit` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `bank_dice` REAL NOT NULL DEFAULT '0.00',
  `bank_mines` REAL NOT NULL DEFAULT '0.00',
  `bank_bubbles` REAL NOT NULL DEFAULT '0.00',
  `bank_wheel` REAL NOT NULL DEFAULT '0.00',
  `bank_plinko` REAL NOT NULL,
  `earn_bubbles` REAL NOT NULL DEFAULT '0.00',
  `comission` INTEGER NOT NULL DEFAULT '0',
  `earn_dice` REAL NOT NULL DEFAULT '0.00',
  `earn_mines` REAL NOT NULL DEFAULT '0.00',
  `earn_plinko` REAL NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--

-- Table structure for table `promocode_activations`
--

DROP TABLE IF EXISTS `promocode_activations`;
CREATE TABLE `promocode_activations` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `promo_id` INTEGER NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`promo_id`) REFERENCES `promocodes`(`id`)
);

--

-- Table structure for table `promocodes`
--

DROP TABLE IF EXISTS `promocodes`;
CREATE TABLE `promocodes` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL,
  `sum` REAL NOT NULL,
  `activation` INTEGER NOT NULL,
  `wager` REAL NOT NULL DEFAULT '0.00',
  `type` TEXT NOT NULL,
  `end_time` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--

-- Table structure for table `referral_profits`
--

DROP TABLE IF EXISTS `referral_profits`;
CREATE TABLE `referral_profits` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `from_id` INTEGER NOT NULL,
  `ref_id` INTEGER NOT NULL,
  `amount` REAL NOT NULL DEFAULT '0.00',
  `level` INTEGER NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);

--

-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `keywords` TEXT DEFAULT NULL,
  `kassa_id` TEXT DEFAULT NULL,
  `kassa_secret1` TEXT DEFAULT NULL,
  `kassa_secret2` TEXT DEFAULT NULL,
  `kassa_key` TEXT DEFAULT NULL,
  `wallet_id` TEXT DEFAULT NULL,
  `wallet_secret` TEXT DEFAULT NULL,
  `wallet_desc` TEXT DEFAULT NULL,
  `vlito_id` TEXT DEFAULT NULL,
  `vlito_secret` TEXT DEFAULT NULL,
  `min_payment_sum` REAL DEFAULT NULL,
  `min_bonus_sum` REAL DEFAULT NULL,
  `min_withdraw_sum` REAL DEFAULT NULL,
  `min_dep_withdraw` INTEGER DEFAULT NULL,
  `withdraw_request_limit` INTEGER DEFAULT NULL,
  `vk_url` TEXT DEFAULT NULL,
  `tg_channel` TEXT DEFAULT NULL,
  `tg_bot` TEXT DEFAULT NULL,
  `vk_id` TEXT DEFAULT NULL,
  `vk_token` TEXT DEFAULT NULL,
  `vk_service_token` TEXT DEFAULT NULL,
  `bot_timer` REAL DEFAULT NULL,
  `file_version` INTEGER NOT NULL DEFAULT '1',
  `antiminus` INTEGER NOT NULL DEFAULT '0',
  `daily_bonus_min` REAL NOT NULL DEFAULT '0.00',
  `daily_bonus_max` REAL NOT NULL DEFAULT '0.00',
  `hourly_bonus_min` REAL NOT NULL DEFAULT '0.00',
  `hourly_bonus_max` REAL NOT NULL DEFAULT '0.00',
  `onetime_bonus` REAL NOT NULL DEFAULT '0',
  `telegram_chat_id` INTEGER DEFAULT NULL,
  `telegram_token` TEXT DEFAULT NULL,
  `referral_domain` TEXT DEFAULT NULL,
  `referral_reward` double(16,2) NOT NULL DEFAULT '0.00', 
  `deposit_per_n` int(11) NOT NULL DEFAULT '0',
  `deposit_sum_n` double(16,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

--

-- Table structure for table `slots`
--

DROP TABLE IF EXISTS `slots`;
CREATE TABLE `slots` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` varchar(150) NOT NULL,
  `game_id` varchar(150) NOT NULL,
  `provider` varchar(150) NOT NULL,
  `icon` varchar(150) NOT NULL,
  `show` int(11) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

--
-- Table structure for table `slots1`
--

DROP TABLE IF EXISTS `slots1`;
CREATE TABLE `slots1` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `game_id` varchar(150) NOT NULL,
  `title` varchar(150) NOT NULL,
  `provider` varchar(150) NOT NULL,
  `icon` varchar(150) NOT NULL,
  `show` int(11) NOT NULL DEFAULT '1',
  `priority` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

--
-- Table structure for table `slots_data``
--

DROP TABLE IF EXISTS `slots_data`;
CREATE TABLE `slots_data` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
);

--
-- Table structure for table `slots_mobule`
--

DROP TABLE IF EXISTS `slots_mobule`;
CREATE TABLE `slots_mobule` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `game_id` varchar(150) NOT NULL,
  `title` varchar(256) COLLATE NOCASE NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `provider` varchar(150) NOT NULL,
  `icon` varchar(150) NOT NULL,
  `show` int(11) NOT NULL DEFAULT '1',
  `is_live` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

--
-- Table structure for table `users``
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `unique_id` varchar(16) COLLATE NOCASE NOT NULL,
  `username` varchar(256) COLLATE NOCASE NOT NULL,
  `balance` double(255,2) NOT NULL DEFAULT '0.00',
  `promo_limit` timestamp NULL DEFAULT NULL,
  `promo_limit2` timestamp NULL DEFAULT NULL,
  `repost` int(11) NOT NULL DEFAULT '0',
  `bonus_balance` float(16,2) NOT NULL DEFAULT '0.00',
  `bonus_bank` double DEFAULT NULL,
  `wager` double(16,2) NOT NULL DEFAULT '0.00',
  `wager_status` int(11) NOT NULL DEFAULT '1',
  `avatar` varchar(300) COLLATE NOCASE DEFAULT NULL,
  `vk_id` bigint(20) DEFAULT NULL,
  `tg_id` varchar(50) COLLATE NOCASE NOT NULL DEFAULT '0',
  `vk_username` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `dice` double(16,2) NOT NULL DEFAULT '0.00',
  `mines` double(16,2) NOT NULL DEFAULT '0.00',
  `bubbles` double(16,2) NOT NULL DEFAULT '0.00',
  `wheel` double(16,2) NOT NULL DEFAULT '0.00',
  `plinko` double NOT NULL DEFAULT '0',
  `slots` double(16,2) DEFAULT '0.00',
  `total_reposts` int(11) NOT NULL DEFAULT '0',
  `is_bot` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin` int(11) NOT NULL DEFAULT '0',
  `is_youtuber` int(11) NOT NULL DEFAULT '0',
  `is_worker` int(11) NOT NULL DEFAULT '0',
  `referral_use` int(11) DEFAULT '0',
  `referral_send` int(11) NOT NULL DEFAULT '0',
  `referral_balance` double(16,2) NOT NULL DEFAULT '0.00',
  `ref_1_lvl` double(16,2) NOT NULL DEFAULT '0.00',
  `ref_2_lvl` double(16,2) NOT NULL DEFAULT '0.00',
  `ref_3_lvl` double(16,2) NOT NULL DEFAULT '0.00',
  `ban` int(11) NOT NULL DEFAULT '0',
  `auth_token` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `game_token` varchar(225) COLLATE NOCASE DEFAULT NULL,
  `game_token_date` varchar(225) COLLATE NOCASE DEFAULT NULL,
  `current_currency` varchar(225) COLLATE NOCASE NOT NULL DEFAULT 'RUB',
  `bonus_use` int(11) NOT NULL DEFAULT '0',
  `bonus_daily` bigint(20) NOT NULL DEFAULT '0',
  `bonus_hourly` bigint(20) NOT NULL DEFAULT '0',
  `vk_bonus_use` int(11) NOT NULL DEFAULT '0',
  `tg_bonus_use` int(11) NOT NULL DEFAULT '0',
  `created_ip` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `used_ip` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `videocard` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `fingerprint` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `logs_length` varchar(255) COLLATE NOCASE DEFAULT NULL,
  `current_id` int(11) DEFAULT NULL,
  `current_bet` double(16,2) DEFAULT NULL,
  `remember_token` varchar(100) COLLATE NOCASE NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);

--
-- Table structure for table `withdraws``
--

DROP TABLE IF EXISTS `withdraws`;
CREATE TABLE `withdraws` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` INTEGER NOT NULL,
  `sum` REAL NOT NULL,
  `sumWithCom` REAL NOT NULL DEFAULT '0.00',
  `wallet` TEXT NOT NULL COLLATE NOCASE,
  `system` TEXT NOT NULL COLLATE NOCASE,
  `reason` TEXT DEFAULT NULL COLLATE NOCASE,
  `status` INTEGER NOT NULL DEFAULT '0',
  `fake` INTEGER NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `is_youtuber` INTEGER NOT NULL DEFAULT '0',
  FOREIGN KEY (`created_at`) REFERENCES `payments`(`created_at`)
);
