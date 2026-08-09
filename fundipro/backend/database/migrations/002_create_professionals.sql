-- Migration 002: Create Professionals Table
-- FundiPro - Kenya Professional Services
-- Date: 2026-08-09

CREATE TABLE IF NOT EXISTS professionals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    bio TEXT,
    years_experience INT DEFAULT 0,
    hourly_rate DECIMAL(10,2) COMMENT 'Rate in KES',
    service_areas JSON COMMENT 'Array of county IDs',
    trades JSON COMMENT 'Array of service types',
    languages JSON COMMENT 'e.g. [English, Kiswahili]',
    availability JSON,
    verification_status ENUM('unverified','pending','verified','rejected') DEFAULT 'unverified',
    national_id VARCHAR(20),
    id_photo_url VARCHAR(255),
    selfie_url VARCHAR(255),
    certificate_urls JSON,
    subscription_tier ENUM('free','pro','business') DEFAULT 'free',
    subscription_expires DATE NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    total_jobs INT DEFAULT 0,
    response_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verification (verification_status),
    INDEX idx_subscription (subscription_tier),
    INDEX idx_rating (rating DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
