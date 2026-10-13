-- Migration: Create users and related authentication tables
-- Version: 1.0.0
-- Date: 2026-10-13
-- Description: Creates users table with authentication support for HMS system

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    role ENUM('admin', 'doctor', 'nurse', 'receptionist', 'patient') NOT NULL DEFAULT 'patient',
    status ENUM('active', 'inactive', 'suspended', 'pending') NOT NULL DEFAULT 'pending',
    profile_picture VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(100) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP verifications table
CREATE TABLE IF NOT EXISTS otp_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_phone (phone),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS logs table
CREATE TABLE IF NOT EXISTS sms_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('otp', 'appointment_reminder', 'billing', 'notification', 'general') NOT NULL,
    status ENUM('sent', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    response TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_phone (phone),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Authentication logs table
CREATE TABLE IF NOT EXISTS authentication_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    status ENUM('success', 'failed', 'blocked') NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API keys table
CREATE TABLE IF NOT EXISTS api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    key_hash VARCHAR(64) NOT NULL UNIQUE,
    permissions JSON DEFAULT NULL,
    status ENUM('active', 'inactive', 'revoked') NOT NULL DEFAULT 'active',
    last_used_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_key_hash (key_hash),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: Admin@2026 (should be changed on first login)
INSERT INTO users (
    uuid, 
    username, 
    email, 
    password_hash, 
    first_name, 
    last_name, 
    phone, 
    role, 
    status,
    created_at,
    updated_at
) VALUES (
    '00000000-0000-0000-0000-000000000001',
    'admin',
    'admin@hms.fundipro.co.ke',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYNweCR6qny',
    'System',
    'Administrator',
    '+254700000000',
    'admin',
    'active',
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE id=id;
