CREATE TABLE IF NOT EXISTS `tracking` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `waktu` datetime NOT NULL,
  `ip` varchar(45) NOT NULL,
  `path` varchar(255) NOT NULL,
  `durasi` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_tracking_waktu` (`waktu`),
  KEY `idx_tracking_path` (`path`),
  KEY `idx_tracking_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
