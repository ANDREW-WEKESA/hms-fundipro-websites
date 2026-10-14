<?php

declare(strict_types=1);

namespace FundiPro\Services;

/**
 * Validation Service
 * 
 * Centralized validation for FundiPro platform with focus on Kenyan-specific data formats
 * Provides validation for phone numbers, currency amounts, and user input sanitization
 */
class ValidationService
{
    // Kenyan phone number regex: +254 followed by 7 or 1, then 8 digits
    private const KENYAN_PHONE_REGEX = '/^\+254[17]\d{8}$/';

    // Email validation pattern (basic RFC 5322)
    private const EMAIL_REGEX = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    // Maximum KES amount for transactions (10 million)
    private const MAX_KES_AMOUNT = 10000000.00;

    // Password requirements
    private const PASSWORD_MIN_LENGTH = 8;
    private const PASSWORD_MAX_LENGTH = 128;

    /**
     * Validate Kenyan phone number format
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid +254[17]xxxxxxxx format
     */
    public static function validateKenyanPhone(string $phone): bool
    {
        return preg_match(self::KENYAN_PHONE_REGEX, $phone) === 1;
    }

    /**
     * Normalize Kenyan phone number to +254 format
     * Supports multiple input formats: 07xx, 01xx, 2547xx, +254xxx
     * 
     * @param string $phone Phone number in various formats
     * @return string Normalized phone in +254 format
     * @throws \InvalidArgumentException If phone cannot be normalized
     */
    public static function normalizePhone(string $phone): string
    {
        // Remove whitespace, dashes, parentheses, and other separators
        $phone = preg_replace('/[\s\-\(\)\.]+/', '', $phone);

        // Handle 07xx or 01xx format (Kenyan local format)
        if (preg_match('/^0([17]\d{8})$/', $phone, $matches)) {
            return '+254' . $matches[1];
        }

        // Handle 7xx or 1xx format (without leading 0)
        if (preg_match('/^([17]\d{8})$/', $phone, $matches)) {
            return '+254' . $matches[1];
        }

        // Handle 2547xx or 2541xx format (without +)
        if (preg_match('/^254([17]\d{8})$/', $phone, $matches)) {
            return '+254' . $matches[1];
        }

        // Handle +2547xx or +2541xx format (already normalized)
        if (preg_match('/^\+254([17]\d{8})$/', $phone)) {
            return $phone;
        }

        // Could not normalize - invalid format
        throw new \InvalidArgumentException(
            "Invalid Kenyan phone number format. Expected formats: 07xx, 01xx, 2547xx, or +2547xx"
        );
    }

    /**
     * Validate email address format
     * 
     * @param string $email Email address to validate
     * @return bool True if valid email format
     */
    public static function validateEmail(string $email): bool
    {
        // Basic format check
        if (!preg_match(self::EMAIL_REGEX, $email)) {
            return false;
        }

        // Use PHP's built-in filter for additional validation
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate Kenyan Shilling (KES) amount
     * 
     * @param float $amount Amount to validate
     * @param float $minAmount Minimum allowed amount (default: 0.01)
     * @param float $maxAmount Maximum allowed amount (default: 10,000,000)
     * @return bool True if amount is within valid range
     */
    public static function validateKesAmount(
        float $amount, 
        float $minAmount = 0.01, 
        float $maxAmount = self::MAX_KES_AMOUNT
    ): bool {
        // Check if amount is positive and within range
        if ($amount < $minAmount || $amount > $maxAmount) {
            return false;
        }

        // Check if amount has more than 2 decimal places
        $decimalPlaces = strlen(substr(strrchr((string)$amount, "."), 1));
        if ($decimalPlaces > 2) {
            return false;
        }

        return true;
    }

    /**
     * Validate county ID (Kenya has 47 counties)
     * 
     * @param int $countyId County ID to validate
     * @return bool True if ID is between 1 and 47
     */
    public static function validateCountyId(int $countyId): bool
    {
        return $countyId >= 1 && $countyId <= 47;
    }

    /**
     * Sanitize string input for safe storage and display
     * Removes potential XSS vectors and excessive whitespace
     * 
     * @param string $input Raw input string
     * @param int $maxLength Maximum allowed length (0 = unlimited)
     * @return string Sanitized string
     */
    public static function sanitizeString(string $input, int $maxLength = 0): string
    {
        // Trim whitespace
        $sanitized = trim($input);

        // Convert special characters to HTML entities
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize multiple spaces to single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);

        // Apply length limit if specified
        if ($maxLength > 0 && mb_strlen($sanitized, 'UTF-8') > $maxLength) {
            $sanitized = mb_substr($sanitized, 0, $maxLength, 'UTF-8');
        }

        return $sanitized;
    }

    /**
     * Validate password strength
     * Requirements: minimum 8 characters, at least one uppercase, one lowercase, one digit
     * 
     * @param string $password Password to validate
     * @return bool True if password meets requirements
     */
    public static function validatePassword(string $password): bool
    {
        $length = strlen($password);

        // Check length
        if ($length < self::PASSWORD_MIN_LENGTH || $length > self::PASSWORD_MAX_LENGTH) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Get detailed password validation errors
     * 
     * @param string $password Password to validate
     * @return array Array of error messages (empty if valid)
     */
    public static function getPasswordErrors(string $password): array
    {
        $errors = [];
        $length = strlen($password);

        if ($length < self::PASSWORD_MIN_LENGTH) {
            $errors[] = "Password must be at least " . self::PASSWORD_MIN_LENGTH . " characters long";
        }

        if ($length > self::PASSWORD_MAX_LENGTH) {
            $errors[] = "Password must not exceed " . self::PASSWORD_MAX_LENGTH . " characters";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one digit";
        }

        return $errors;
    }

    /**
     * Validate rating value (1-5 stars)
     * 
     * @param int $rating Rating value to validate
     * @return bool True if rating is between 1 and 5
     */
    public static function validateRating(int $rating): bool
    {
        return $rating >= 1 && $rating <= 5;
    }

    /**
     * Validate slug format (lowercase alphanumeric with hyphens)
     * 
     * @param string $slug Slug to validate
     * @return bool True if valid slug format
     */
    public static function validateSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }

    /**
     * Generate a slug from a string
     * 
     * @param string $text Text to convert to slug
     * @param int $maxLength Maximum slug length
     * @return string Generated slug
     */
    public static function generateSlug(string $text, int $maxLength = 100): string
    {
        // Convert to lowercase
        $slug = strtolower($text);

        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');

        // Remove consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Apply length limit
        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }

    /**
     * Validate date string format (Y-m-d)
     * 
     * @param string $date Date string to validate
     * @return bool True if valid date format
     */
    public static function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate enum value against allowed values
     * 
     * @param string $value Value to validate
     * @param array $allowedValues Array of allowed values
     * @return bool True if value is in allowed list
     */
    public static function validateEnum(string $value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }

    /**
     * Sanitize filename for safe storage
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Get file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Remove special characters from basename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);

        // Limit length
        $basename = substr($basename, 0, 200);

        // Reconstruct filename
        return $extension ? $basename . '.' . $extension : $basename;
    }

    /**
     * Validate file upload size
     * 
     * @param int $fileSize File size in bytes
     * @param int $maxSize Maximum allowed size in bytes (default: 5MB)
     * @return bool True if file size is within limit
     */
    public static function validateFileSize(int $fileSize, int $maxSize = 5242880): bool
    {
        return $fileSize > 0 && $fileSize <= $maxSize;
    }

    /**
     * Validate image file type
     * 
     * @param string $mimeType MIME type of the file
     * @return bool True if valid image type
     */
    public static function validateImageType(string $mimeType): bool
    {
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        return in_array($mimeType, $allowedTypes, true);
    }

    /**
     * Format KES amount for display
     * 
     * @param float $amount Amount in KES
     * @return string Formatted amount (e.g., "KES 1,234.50")
     */
    public static function formatKesAmount(float $amount): string
    {
        return 'KES ' . number_format($amount, 2, '.', ',');
    }

    /**
     * Mask phone number for display (shows last 3 digits)
     * 
     * @param string $phone Phone number to mask
     * @return string Masked phone (e.g., "+254*****123")
     */
    public static function maskPhone(string $phone): string
    {
        if (strlen($phone) < 7) {
            return $phone;
        }

        $lastThree = substr($phone, -3);
        $prefix = substr($phone, 0, 4); // "+254"
        return $prefix . '*****' . $lastThree;
    }
}
