-- FundiPro Database Schema
-- MySQL 8.0+
-- Character Set: utf8mb4 for full Unicode support including emojis

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- CORE USER TABLES
-- ============================================================================

-- Counties table (47 Kenyan counties)
CREATE TABLE IF NOT EXISTS counties (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert all 47 Kenyan counties
INSERT INTO counties (id, name, code) VALUES
(1, 'Mombasa', 'MSA'),
(2, 'Kwale', 'KWL'),
(3, 'Kilifi', 'KLF'),
(4, 'Tana River', 'TNR'),
(5, 'Lamu', 'LAM'),
(6, 'Taita-Taveta', 'TTA'),
(7, 'Garissa', 'GRS'),
(8, 'Wajir', 'WJR'),
(9, 'Mandera', 'MND'),
(10, 'Marsabit', 'MBT'),
(11, 'Isiolo', 'ISL'),
(12, 'Meru', 'MRU'),
(13, 'Tharaka-Nithi', 'THN'),
(14, 'Embu', 'EMB'),
(15, 'Kitui', 'KTU'),
(16, 'Machakos', 'MCH'),
(17, 'Makueni', 'MKN'),
(18, 'Nyandarua', 'NDA'),
(19, 'Nyeri', 'NYR'),
(20, 'Kirinyaga', 'KRG'),
(21, 'Murang\'a', 'MRG'),
(22, 'Kiambu', 'KBU'),
(23, 'Turkana', 'TRK'),
(24, 'West Pokot', 'WPK'),
(25, 'Samburu', 'SMB'),
(26, 'Trans-Nzoia', 'TNZ'),
(27, 'Uasin Gishu', 'UGS'),
(28, 'Elgeyo-Marakwet', 'ELM'),
(29, 'Nandi', 'NND'),
(30, 'Baringo', 'BRN'),
(31, 'Laikipia', 'LKP'),
(32, 'Nakuru', 'NKR'),
(33, 'Narok', 'NRK'),
(34, 'Kajiado', 'KJD'),
(35, 'Kericho', 'KRC'),
(36, 'Bomet', 'BMT'),
(37, 'Kakamega', 'KKM'),
(38, 'Vihiga', 'VHG'),
(39, 'Bungoma', 'BNG'),
(40, 'Busia', 'BSA'),
(41, 'Siaya', 'SYA'),
(42, 'Kisumu', 'KSM'),
(43, 'Homa Bay', 'HMB'),
(44, 'Migori', 'MGR'),
(45, 'Kisii', 'KSI'),
(46, 'Nyamira', 'NYM'),
(47, 'Nairobi', 'NRB');

-- Users table (customers, professionals, admins)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE COMMENT 'Format: +254xxxxxxxxx',
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'professional', 'admin') DEFAULT 'customer',
    county_id INT UNSIGNED,
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    phone_verified BOOLEAN DEFAULT FALSE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL,
    FOREIGN KEY (county_id) REFERENCES counties(id) ON DELETE SET NULL,
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_county (county_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP verification table
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    purpose ENUM('registration', 'login', 'password_reset') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone_purpose (phone, purpose),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICE CATEGORIES
-- ============================================================================

CREATE TABLE IF NOT EXISTS service_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    typical_price_min_kes DECIMAL(10, 2),
    typical_price_max_kes DECIMAL(10, 2),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert common service categories
INSERT INTO service_categories (name, slug, description, icon, typical_price_min_kes, typical_price_max_kes, display_order) VALUES
('Plumbing', 'plumbing', 'Pipe repairs, installations, drainage, water systems', 'wrench', 500.00, 50000.00, 1),
('Electrical', 'electrical', 'Wiring, repairs, installations, solar systems', 'bolt', 800.00, 100000.00, 2),
('Carpentry', 'carpentry', 'Furniture making, repairs, installations', 'hammer', 1000.00, 150000.00, 3),
('Painting & Decoration', 'painting', 'Interior/exterior painting, wallpaper installation', 'paint-brush', 2000.00, 80000.00, 4),
('Cleaning Services', 'cleaning', 'Home, office, deep cleaning, fumigation', 'broom', 500.00, 20000.00, 5),
('Masonry & Construction', 'masonry', 'Building, repairs, renovations', 'hard-hat', 5000.00, 500000.00, 6),
('Landscaping & Gardening', 'landscaping', 'Lawn care, garden design, maintenance', 'leaf', 1000.00, 50000.00, 7),
('Appliance Repair', 'appliance-repair', 'Fridges, TVs, washing machines, microwaves', 'tools', 500.00, 30000.00, 8),
('HVAC Services', 'hvac', 'Air conditioning installation and repair', 'fan', 2000.00, 80000.00, 9),
('Security Services', 'security', 'Alarm installation, CCTV, guard services', 'shield', 3000.00, 200000.00, 10),
('Moving & Transportation', 'moving', 'Relocation, delivery, logistics', 'truck', 2000.00, 100000.00, 11),
('IT & Tech Support', 'tech-support', 'Computer repair, networking, software', 'laptop', 1000.00, 50000.00, 12),
('Automotive Services', 'automotive', 'Mechanics, car wash, detailing', 'car', 500.00, 100000.00, 13),
('Beauty & Personal Care', 'beauty', 'Hairdressing, barbering, makeup', 'scissors', 300.00, 15000.00, 14),
('Event Services', 'events', 'Catering, decoration, photography', 'calendar', 5000.00, 300000.00, 15);

-- ============================================================================
-- PROFESSIONALS
-- ============================================================================

CREATE TABLE IF NOT EXISTS professionals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    category_id INT UNSIGNED NOT NULL,
    bio TEXT,
    years_experience INT UNSIGNED DEFAULT 0,
    hourly_rate_kes DECIMAL(10, 2),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_notes TEXT,
    subscription_tier ENUM('free', 'pro', 'business') DEFAULT 'free',
    subscription_expires_at TIMESTAMP NULL,
    total_jobs_completed INT UNSIGNED DEFAULT 0,
    average_rating DECIMAL(3, 2) DEFAULT 0.00,
    total_reviews INT UNSIGNED DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE RESTRICT,
    INDEX idx_category (category_id),
    INDEX idx_verification (verification_status),
    INDEX idx_subscription (subscription_tier, subscription_expires_at),
    INDEX idx_rating (average_rating DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Professional service counties (many-to-many)
CREATE TABLE IF NOT EXISTS professional_service_counties (
    professional_id INT UNSIGNED NOT NULL,
    county_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (professional_id, county_id),
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE,
    FOREIGN KEY (county_id) REFERENCES counties(id) ON DELETE CASCADE,
    INDEX idx_county (county_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Professional certifications
CREATE TABLE IF NOT EXISTS professional_certifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    professional_id INT UNSIGNED NOT NULL,
    certification_name VARCHAR(200) NOT NULL,
    issuing_organization VARCHAR(200),
    issue_date DATE,
    expiry_date DATE,
    document_url VARCHAR(255),
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE,
    INDEX idx_professional (professional_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- JOBS
-- ============================================================================

CREATE TABLE IF NOT EXISTS jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    county_id INT UNSIGNED NOT NULL,
    specific_location VARCHAR(255),
    budget_min_kes DECIMAL(10, 2),
    budget_max_kes DECIMAL(10, 2),
    status ENUM('open', 'quoted', 'booked', 'in_progress', 'completed', 'cancelled', 'expired') DEFAULT 'open',
    preferred_start_date DATE,
    urgency ENUM('low', 'medium', 'high', 'emergency') DEFAULT 'medium',
    views_count INT UNSIGNED DEFAULT 0,
    quotes_count INT UNSIGNED DEFAULT 0,
    expires_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (county_id) REFERENCES counties(id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_category (category_id),
    INDEX idx_county (county_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at DESC),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job images
CREATE TABLE IF NOT EXISTS job_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX idx_job (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- QUOTES
-- ============================================================================

CREATE TABLE IF NOT EXISTS quotes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    professional_id INT UNSIGNED NOT NULL,
    amount_kes DECIMAL(10, 2) NOT NULL,
    message TEXT,
    estimated_duration_days INT UNSIGNED,
    status ENUM('pending', 'accepted', 'rejected', 'withdrawn', 'expired') DEFAULT 'pending',
    valid_until TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE,
    UNIQUE KEY unique_job_professional (job_id, professional_id),
    INDEX idx_job (job_id),
    INDEX idx_professional (professional_id),
    INDEX idx_status (status),
    INDEX idx_valid_until (valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOKINGS & PAYMENTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL UNIQUE,
    quote_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    professional_id INT UNSIGNED NOT NULL,
    total_amount_kes DECIMAL(10, 2) NOT NULL,
    platform_fee_kes DECIMAL(10, 2) NOT NULL,
    professional_payout_kes DECIMAL(10, 2) NOT NULL,
    status ENUM('pending_payment', 'paid', 'in_progress', 'completed', 'cancelled', 'refunded') DEFAULT 'pending_payment',
    scheduled_date DATE,
    completion_date DATE,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE RESTRICT,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_professional (professional_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    mpesa_receipt_number VARCHAR(50) UNIQUE,
    phone_number VARCHAR(20) NOT NULL,
    amount_kes DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('mpesa_stk', 'mpesa_c2b', 'manual') DEFAULT 'mpesa_stk',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_response TEXT,
    callback_response TEXT,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
    INDEX idx_booking (booking_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_mpesa_receipt (mpesa_receipt_number),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Professional payouts
CREATE TABLE IF NOT EXISTS payouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    professional_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED NOT NULL,
    amount_kes DECIMAL(10, 2) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100) UNIQUE,
    mpesa_conversation_id VARCHAR(100),
    payout_response TEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE RESTRICT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
    INDEX idx_professional (professional_id),
    INDEX idx_booking (booking_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- REVIEWS & RATINGS
-- ============================================================================

CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED NOT NULL,
    reviewer_id INT UNSIGNED NOT NULL,
    reviewee_id INT UNSIGNED NOT NULL,
    reviewer_role ENUM('customer', 'professional') NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_public BOOLEAN DEFAULT TRUE,
    is_flagged BOOLEAN DEFAULT FALSE,
    flag_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_reviewer (booking_id, reviewer_id),
    INDEX idx_reviewee (reviewee_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATIONS & MESSAGING
-- ============================================================================

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    related_entity_type VARCHAR(50),
    related_entity_id INT UNSIGNED,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SUBSCRIPTION MANAGEMENT
-- ============================================================================

CREATE TABLE IF NOT EXISTS subscription_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    professional_id INT UNSIGNED NOT NULL,
    tier ENUM('pro', 'business') NOT NULL,
    amount_kes DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('mpesa', 'manual') DEFAULT 'mpesa',
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professional_id) REFERENCES professionals(id) ON DELETE CASCADE,
    INDEX idx_professional (professional_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ADMIN & ANALYTICS
-- ============================================================================

CREATE TABLE IF NOT EXISTS admin_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin (admin_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================================

-- Active jobs with category and county details
CREATE OR REPLACE VIEW active_jobs_view AS
SELECT 
    j.id,
    j.title,
    j.description,
    j.budget_min_kes,
    j.budget_max_kes,
    j.status,
    j.created_at,
    u.name AS customer_name,
    u.phone AS customer_phone,
    sc.name AS category_name,
    sc.slug AS category_slug,
    c.name AS county_name,
    j.quotes_count
FROM jobs j
JOIN users u ON j.customer_id = u.id
JOIN service_categories sc ON j.category_id = sc.id
JOIN counties c ON j.county_id = c.id
WHERE j.status IN ('open', 'quoted')
AND (j.expires_at IS NULL OR j.expires_at > NOW());

-- Professional profiles with ratings
CREATE OR REPLACE VIEW professional_profiles_view AS
SELECT 
    p.id,
    p.user_id,
    u.name,
    u.phone,
    u.email,
    u.county_id,
    c.name AS county_name,
    p.category_id,
    sc.name AS category_name,
    p.bio,
    p.years_experience,
    p.hourly_rate_kes,
    p.verification_status,
    p.subscription_tier,
    p.total_jobs_completed,
    p.average_rating,
    p.total_reviews,
    p.is_available,
    p.created_at
FROM professionals p
JOIN users u ON p.user_id = u.id
JOIN service_categories sc ON p.category_id = sc.id
LEFT JOIN counties c ON u.county_id = c.id
WHERE u.is_active = TRUE;

-- End of schema
