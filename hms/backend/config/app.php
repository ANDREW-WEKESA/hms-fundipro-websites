<?php

namespace HMS\Backend\Config;

/**
 * Application Configuration
 * 
 * Main configuration file for HMS backend
 * 
 * @package HMS\Backend\Config
 * @version 1.0.0
 */
class AppConfig
{
    /**
     * Get all configuration settings
     * 
     * @return array Configuration array
     */
    public static function get(): array
    {
        return [
            // Application settings
            'app' => [
                'name' => self::env('APP_NAME', 'HMS FundiPro'),
                'version' => '1.0.0',
                'env' => self::env('APP_ENV', 'production'),
                'debug' => self::env('APP_DEBUG', false),
                'url' => self::env('APP_URL', 'https://hms.fundipro.co.ke'),
                'timezone' => 'Africa/Nairobi',
                'locale' => 'en_KE',
                'currency' => 'KES'
            ],

            // Database configuration
            'database' => [
                'driver' => self::env('DB_DRIVER', 'mysql'),
                'host' => self::env('DB_HOST', 'localhost'),
                'port' => self::env('DB_PORT', 3306),
                'database' => self::env('DB_DATABASE', 'hms_fundipro'),
                'username' => self::env('DB_USERNAME', 'root'),
                'password' => self::env('DB_PASSWORD', ''),
                'charset' => self::env('DB_CHARSET', 'utf8mb4'),
                'collation' => self::env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => self::env('DB_PREFIX', ''),
                'timezone' => '+03:00',
                'options' => [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            ],

            // JWT Authentication
            'jwt' => [
                'secret' => self::env('JWT_SECRET', 'your-secret-key-change-this'),
                'algo' => 'HS256',
                'expiry' => (int) self::env('JWT_EXPIRY', 86400), // 24 hours
                'refresh_expiry' => (int) self::env('JWT_REFRESH_EXPIRY', 604800), // 7 days
                'issuer' => self::env('JWT_ISSUER', 'hms.fundipro.co.ke'),
                'audience' => self::env('JWT_AUDIENCE', 'hms-api')
            ],

            // Africa's Talking SMS Configuration
            'africastalking' => [
                'api_key' => self::env('AFRICASTALKING_API_KEY', ''),
                'username' => self::env('AFRICASTALKING_USERNAME', 'sandbox'),
                'sender_id' => self::env('AFRICASTALKING_SENDER_ID', 'HMS'),
                'environment' => self::env('AFRICASTALKING_ENV', 'sandbox') // sandbox or production
            ],

            // M-PESA Configuration (Safaricom)
            'mpesa' => [
                'consumer_key' => self::env('MPESA_CONSUMER_KEY', ''),
                'consumer_secret' => self::env('MPESA_CONSUMER_SECRET', ''),
                'passkey' => self::env('MPESA_PASSKEY', ''),
                'shortcode' => self::env('MPESA_SHORTCODE', '522522'),
                'environment' => self::env('MPESA_ENV', 'sandbox'), // sandbox or production
                'callback_url' => self::env('MPESA_CALLBACK_URL', ''),
                'timeout_url' => self::env('MPESA_TIMEOUT_URL', '')
            ],

            // Email Configuration
            'mail' => [
                'driver' => self::env('MAIL_DRIVER', 'smtp'),
                'host' => self::env('MAIL_HOST', 'smtp.gmail.com'),
                'port' => (int) self::env('MAIL_PORT', 587),
                'username' => self::env('MAIL_USERNAME', ''),
                'password' => self::env('MAIL_PASSWORD', ''),
                'encryption' => self::env('MAIL_ENCRYPTION', 'tls'),
                'from_address' => self::env('MAIL_FROM_ADDRESS', 'noreply@fundipro.co.ke'),
                'from_name' => self::env('MAIL_FROM_NAME', 'HMS FundiPro')
            ],

            // Storage Configuration
            'storage' => [
                'driver' => self::env('STORAGE_DRIVER', 'local'),
                'local_path' => self::env('STORAGE_LOCAL_PATH', __DIR__ . '/../../storage'),
                'public_path' => self::env('STORAGE_PUBLIC_PATH', __DIR__ . '/../../public/storage'),
                'max_file_size' => (int) self::env('STORAGE_MAX_FILE_SIZE', 10485760), // 10MB
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']
            ],

            // Session Configuration
            'session' => [
                'lifetime' => (int) self::env('SESSION_LIFETIME', 120), // minutes
                'expire_on_close' => false,
                'same_site' => 'lax'
            ],

            // Cache Configuration
            'cache' => [
                'driver' => self::env('CACHE_DRIVER', 'file'),
                'ttl' => (int) self::env('CACHE_TTL', 3600), // 1 hour
                'prefix' => self::env('CACHE_PREFIX', 'hms_')
            ],

            // Logging Configuration
            'logging' => [
                'channel' => self::env('LOG_CHANNEL', 'daily'),
                'level' => self::env('LOG_LEVEL', 'error'),
                'path' => self::env('LOG_PATH', __DIR__ . '/../../storage/logs'),
                'max_files' => (int) self::env('LOG_MAX_FILES', 14)
            ],

            // Security Configuration
            'security' => [
                'password_min_length' => 8,
                'password_require_uppercase' => true,
                'password_require_lowercase' => true,
                'password_require_numbers' => true,
                'password_require_symbols' => true,
                'max_login_attempts' => 5,
                'lockout_duration' => 900, // 15 minutes
                'session_timeout' => 3600, // 1 hour
                'force_https' => self::env('FORCE_HTTPS', true)
            ],

            // API Configuration
            'api' => [
                'rate_limit' => (int) self::env('API_RATE_LIMIT', 60),
                'rate_limit_window' => 60, // seconds
                'pagination_limit' => (int) self::env('API_PAGINATION_LIMIT', 50),
                'max_pagination_limit' => 100
            ],

            // Hospital Configuration
            'hospital' => [
                'name' => self::env('HOSPITAL_NAME', 'FundiPro Hospital'),
                'address' => self::env('HOSPITAL_ADDRESS', 'Nairobi, Kenya'),
                'phone' => self::env('HOSPITAL_PHONE', '+254700000000'),
                'email' => self::env('HOSPITAL_EMAIL', 'info@fundipro.co.ke'),
                'website' => self::env('HOSPITAL_WEBSITE', 'https://fundipro.co.ke'),
                'license_number' => self::env('HOSPITAL_LICENSE', ''),
                'working_hours' => [
                    'monday' => '08:00-17:00',
                    'tuesday' => '08:00-17:00',
                    'wednesday' => '08:00-17:00',
                    'thursday' => '08:00-17:00',
                    'friday' => '08:00-17:00',
                    'saturday' => '08:00-13:00',
                    'sunday' => 'closed'
                ]
            ],

            // Appointment Configuration
            'appointments' => [
                'slot_duration' => 30, // minutes
                'booking_advance_days' => 30,
                'cancellation_hours' => 24,
                'reminder_hours' => 24,
                'auto_confirm' => false
            ],

            // Billing Configuration
            'billing' => [
                'currency' => 'KES',
                'tax_rate' => 0.16, // 16% VAT
                'payment_methods' => ['cash', 'mpesa', 'card', 'insurance'],
                'invoice_prefix' => 'INV',
                'receipt_prefix' => 'RCT'
            ],

            // NHIF (National Hospital Insurance Fund - Kenya)
            'nhif' => [
                'enabled' => self::env('NHIF_ENABLED', false),
                'facility_code' => self::env('NHIF_FACILITY_CODE', ''),
                'api_url' => self::env('NHIF_API_URL', ''),
                'api_key' => self::env('NHIF_API_KEY', '')
            ],

            // Features Flags
            'features' => [
                'telemedicine' => self::env('FEATURE_TELEMEDICINE', false),
                'pharmacy' => self::env('FEATURE_PHARMACY', true),
                'laboratory' => self::env('FEATURE_LABORATORY', true),
                'radiology' => self::env('FEATURE_RADIOLOGY', true),
                'inventory' => self::env('FEATURE_INVENTORY', true),
                'reports' => self::env('FEATURE_REPORTS', true),
                'notifications' => self::env('FEATURE_NOTIFICATIONS', true),
                'two_factor_auth' => self::env('FEATURE_2FA', false)
            ],

            // Queue Configuration (for background jobs)
            'queue' => [
                'driver' => self::env('QUEUE_DRIVER', 'database'),
                'retry_after' => 90,
                'max_attempts' => 3
            ],

            // Notification Channels
            'notifications' => [
                'channels' => ['sms', 'email', 'push'],
                'default_channel' => 'sms'
            ]
        ];
    }

    /**
     * Get environment variable
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value
     * @return mixed Value
     */
    private static function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        // Convert string boolean to actual boolean
        if (strtolower($value) === 'true') {
            return true;
        }

        if (strtolower($value) === 'false') {
            return false;
        }

        // Convert string null to actual null
        if (strtolower($value) === 'null') {
            return null;
        }

        return $value;
    }

    /**
     * Get specific configuration value
     * 
     * @param string $key Configuration key (dot notation)
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    public static function getConfig(string $key, mixed $default = null): mixed
    {
        $config = self::get();
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }

    /**
     * Get database connection string
     * 
     * @return string DSN string
     */
    public static function getDatabaseDSN(): string
    {
        $config = self::get()['database'];
        
        return sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
    }
}
