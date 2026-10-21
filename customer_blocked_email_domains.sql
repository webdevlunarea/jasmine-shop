CREATE TABLE IF NOT EXISTS customer_blocked_email_domains (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  domain VARCHAR(190) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY customer_blocked_email_domains_domain_unique (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
