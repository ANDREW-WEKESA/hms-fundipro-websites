-- Migration: Create user sessions and token management tables
-- Version: 1.0.0
-- Date: 2026-10-13
-- Description: Creates tables for JWT session management and token blacklisting

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at),
    INDEX idx_revoked_at (revoked_at),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Token blacklist table
CREATE TABLE IF NOT EXISTS token_blacklist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    reason ENUM('logout', 'expired', 'revoked', 'security') DEFAULT 'logout',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Refresh tokens table (for long-lived sessions)
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    access_token_hash VARCHAR(64) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_token_hash (token_hash),
    INDEX idx_access_token_hash (access_token_hash),
    INDEX idx_expires_at (expires_at),
    INDEX idx_revoked_at (revoked_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session devices table (track devices for security)
CREATE TABLE IF NOT EXISTS session_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_id VARCHAR(64) NOT NULL,
    device_name VARCHAR(100) DEFAULT NULL,
    device_type ENUM('desktop', 'mobile', 'tablet', 'other') DEFAULT 'other',
    browser VARCHAR(50) DEFAULT NULL,
    os VARCHAR(50) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    trusted BOOLEAN DEFAULT FALSE,
    last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_device_id (device_id),
    INDEX idx_trusted (trusted),
    INDEX idx_last_seen_at (last_seen_at),
    
    UNIQUE KEY unique_user_device (user_id, device_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Two-factor authentication table
CREATE TABLE IF NOT EXISTS two_factor_auth (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    secret VARCHAR(32) NOT NULL,
    method ENUM('totp', 'sms', 'email') NOT NULL DEFAULT 'totp',
    enabled BOOLEAN DEFAULT FALSE,
    backup_codes JSON DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_enabled (enabled),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security questions table (additional account recovery)
CREATE TABLE IF NOT EXISTS security_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    question VARCHAR(255) NOT NULL,
    answer_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts table (brute force protection)
CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT DEFAULT 1,
    locked_until DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_identifier (identifier),
    INDEX idx_ip_address (ip_address),
    INDEX idx_locked_until (locked_until),
    
    UNIQUE KEY unique_identifier_ip (identifier, ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Account lockouts table
CREATE TABLE IF NOT EXISTS account_lockouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    reason ENUM('failed_login', 'security_violation', 'admin_action', 'other') NOT NULL,
    locked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_until DATETIME DEFAULT NULL,
    unlocked_at DATETIME DEFAULT NULL,
    locked_by BIGINT UNSIGNED DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_locked_until (locked_until),
    INDEX idx_reason (reason),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (locked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
