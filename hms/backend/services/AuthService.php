<?php

namespace HMS\Backend\Services;

use PDO;
use PDOException;
use Exception;

/**
 * Authentication Service
 * 
 * Handles JWT token generation, validation, and password management
 * Uses HS256 algorithm for JWT signing
 * Implements token blacklist for logout functionality
 * 
 * @package HMS\Backend\Services
 * @version 1.0.0
 */
class AuthService
{
    private PDO $db;
    private string $secretKey;
    private int $tokenExpiry;
    private string $timezone;

    /**
     * AuthService constructor
     * 
     * @param PDO $db Database connection
     * @param string $secretKey JWT secret key
     * @param int $tokenExpiry Token expiry time in seconds (default 24 hours)
     */
    public function __construct(PDO $db, string $secretKey, int $tokenExpiry = 86400)
    {
        $this->db = $db;
        $this->secretKey = $secretKey;
        $this->tokenExpiry = $tokenExpiry;
        $this->timezone = 'Africa/Nairobi';
        date_default_timezone_set($this->timezone);
    }

    /**
     * Generate JWT token for authenticated user
     * 
     * @param int $userId User ID
     * @param string $role User role
     * @param array $additionalClaims Additional claims to include
     * @return string JWT token
     */
    public function generateToken(int $userId, string $role, array $additionalClaims = []): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->tokenExpiry;
        
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        $payload = array_merge([
            'sub' => $userId,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'tz' => $this->timezone
        ], $additionalClaims);
        
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = $this->generateSignature($headerEncoded . '.' . $payloadEncoded);
        
        $token = $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
        
        // Store token session in database
        $this->storeTokenSession($userId, $token, $expiresAt);
        
        return $token;
    }

    /**
     * Validate JWT token and return payload
     * 
     * @param string $token JWT token
     * @return array|null Token payload or null if invalid
     */
    public function validateToken(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }
            
            [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;
            
            // Verify signature
            $signatureExpected = $this->generateSignature($headerEncoded . '.' . $payloadEncoded);
            
            if (!hash_equals($signatureExpected, $signatureProvided)) {
                return null;
            }
            
            // Decode payload
            $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
            
            if (!$payload) {
                return null;
            }
            
            // Check expiration
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }
            
            // Check if token is blacklisted
            if ($this->isTokenBlacklisted($token)) {
                return null;
            }
            
            return $payload;
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Refresh JWT token
     * 
     * @param string $oldToken Old JWT token
     * @return string|null New JWT token or null if refresh failed
     */
    public function refreshToken(string $oldToken): ?string
    {
        $payload = $this->validateToken($oldToken);
        
        if (!$payload) {
            return null;
        }
        
        // Check if token is near expiry (within 1 hour)
        $timeUntilExpiry = $payload['exp'] - time();
        
        if ($timeUntilExpiry > 3600) {
            // Token still has more than 1 hour, return same token
            return $oldToken;
        }
        
        // Generate new token
        $additionalClaims = array_diff_key($payload, array_flip(['sub', 'role', 'iat', 'exp', 'tz']));
        $newToken = $this->generateToken($payload['sub'], $payload['role'], $additionalClaims);
        
        // Blacklist old token
        $this->revokeToken($oldToken);
        
        return $newToken;
    }

    /**
     * Revoke token (add to blacklist)
     * 
     * @param string $token JWT token
     * @return bool Success status
     */
    public function revokeToken(string $token): bool
    {
        try {
            $payload = $this->validateToken($token);
            
            if (!$payload) {
                return false;
            }
            
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', $payload['exp']);
            $now = date('Y-m-d H:i:s');
            
            $stmt = $this->db->prepare("
                INSERT INTO token_blacklist (token_hash, expires_at, created_at)
                VALUES (:token_hash, :expires_at, :created_at)
                ON DUPLICATE KEY UPDATE created_at = :created_at
            ");
            
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':created_at', $now);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Token revocation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hash password using bcrypt
     * 
     * @param string $password Plain text password
     * @return string Password hash
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Password hash
     * @return bool True if password matches
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     * 
     * @param string $hash Password hash
     * @return bool True if rehash needed
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Generate password reset token
     * 
     * @param int $userId User ID
     * @param string $email User email
     * @return string Reset token
     */
    public function generatePasswordResetToken(int $userId, string $email): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
        $now = date('Y-m-d H:i:s');
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, email, token_hash, expires_at, created_at)
                VALUES (:user_id, :email, :token_hash, :expires_at, :created_at)
            ");
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':created_at', $now);
            
            $stmt->execute();
            
            return $token;
        } catch (PDOException $e) {
            error_log("Password reset token generation error: " . $e->getMessage());
            throw new Exception("Failed to generate password reset token");
        }
    }

    /**
     * Validate password reset token
     * 
     * @param string $token Reset token
     * @param string $email User email
     * @return int|null User ID if valid, null otherwise
     */
    public function validatePasswordResetToken(string $token, string $email): ?int
    {
        try {
            $tokenHash = hash('sha256', $token);
            
            $stmt = $this->db->prepare("
                SELECT user_id, expires_at, used_at 
                FROM password_resets 
                WHERE token_hash = :token_hash 
                AND email = :email
                ORDER BY created_at DESC
                LIMIT 1
            ");
            
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return null;
            }
            
            // Check if already used
            if ($row['used_at'] !== null) {
                return null;
            }
            
            // Check if expired
            if (strtotime($row['expires_at']) < time()) {
                return null;
            }
            
            return (int) $row['user_id'];
        } catch (PDOException $e) {
            error_log("Password reset token validation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark password reset token as used
     * 
     * @param string $token Reset token
     * @return bool Success status
     */
    public function markPasswordResetTokenUsed(string $token): bool
    {
        try {
            $tokenHash = hash('sha256', $token);
            $now = date('Y-m-d H:i:s');
            
            $stmt = $this->db->prepare("
                UPDATE password_resets 
                SET used_at = :used_at 
                WHERE token_hash = :token_hash
            ");
            
            $stmt->bindParam(':used_at', $now);
            $stmt->bindParam(':token_hash', $tokenHash);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error marking reset token as used: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up expired tokens from blacklist
     * 
     * @return int Number of tokens removed
     */
    public function cleanupExpiredTokens(): int
    {
        try {
            $now = date('Y-m-d H:i:s');
            
            $stmt = $this->db->prepare("
                DELETE FROM token_blacklist 
                WHERE expires_at < :now
            ");
            
            $stmt->bindParam(':now', $now);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Token cleanup error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generate signature for JWT
     * 
     * @param string $data Data to sign
     * @return string Base64 URL encoded signature
     */
    private function generateSignature(string $data): string
    {
        $signature = hash_hmac('sha256', $data, $this->secretKey, true);
        return $this->base64UrlEncode($signature);
    }

    /**
     * Base64 URL encode
     * 
     * @param string $data Data to encode
     * @return string Encoded data
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     * 
     * @param string $data Data to decode
     * @return string Decoded data
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Check if token is blacklisted
     * 
     * @param string $token JWT token
     * @return bool True if blacklisted
     */
    private function isTokenBlacklisted(string $token): bool
    {
        try {
            $tokenHash = hash('sha256', $token);
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM token_blacklist 
                WHERE token_hash = :token_hash 
                AND expires_at > NOW()
            ");
            
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $row['count'] > 0;
        } catch (PDOException $e) {
            error_log("Token blacklist check error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store token session in database
     * 
     * @param int $userId User ID
     * @param string $token JWT token
     * @param int $expiresAt Expiration timestamp
     * @return bool Success status
     */
    private function storeTokenSession(int $userId, string $token, int $expiresAt): bool
    {
        try {
            $tokenHash = hash('sha256', $token);
            $expiresAtDate = date('Y-m-d H:i:s', $expiresAt);
            $now = date('Y-m-d H:i:s');
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt = $this->db->prepare("
                INSERT INTO user_sessions (user_id, token_hash, ip_address, user_agent, expires_at, created_at)
                VALUES (:user_id, :token_hash, :ip_address, :user_agent, :expires_at, :created_at)
            ");
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':token_hash', $tokenHash);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->bindParam(':expires_at', $expiresAtDate);
            $stmt->bindParam(':created_at', $now);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Token session storage error: " . $e->getMessage());
            return false;
        }
    }
}
