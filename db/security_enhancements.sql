-- Additional Security Enhancements for Doctor Portal

-- Activity Logs Table (for audit trail)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'doctor', 'patient') NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_user_type (user_type),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Reset Tokens (for future password recovery)
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('admin', 'doctor', 'patient') NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used BOOLEAN DEFAULT FALSE,
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts (for brute force protection)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50),
    attempt_type ENUM('success', 'failed') NOT NULL,
    user_type ENUM('admin', 'doctor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_address (ip_address),
    INDEX idx_created_at (created_at),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update existing tables with additional security fields
ALTER TABLE admins ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE admins ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE admins ADD COLUMN locked_until TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE doctors ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE doctors ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE doctors ADD COLUMN locked_until TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE doctors ADD COLUMN is_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE doctors ADD COLUMN verification_token VARCHAR(255);
