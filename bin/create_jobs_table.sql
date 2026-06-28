CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL DEFAULT 'default',
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    available_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_queue_available (queue, available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
