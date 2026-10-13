<?php
/**
 * Authentication Service
 * JWT Token Management for HMS
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private $secretKey;
    private $issuer;
    private $audience;
    
    public function __construct() {
        $this->secretKey = getenv('JWT_SECRET') ?: 'change-this-secret-in-production';
        $this->issuer = 'hms.co.ke';
        $this->audience = 'hms-users';
    }
    
    /**
     * Generate JWT token
     */
    public function generateToken($user) {
        $issuedAt = time();
        $expiresAt = $issuedAt + (8 * 60 * 60); // 8 hours
        
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'data' => [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'facility_id' => $user['facility_id']
            ]
        ];
        
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
    
    /**
     * Validate and decode JWT token
     */
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array)$decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Extract token from Authorization header
     */
    public function extractTokenFromHeader() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Generate OTP for phone verification
     */
    public function generateOTP() {
        return str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Store OTP in session/cache
     */
    public function storeOTP($phone, $otp) {
        // In production, use Redis or similar
        $_SESSION['otp_' . $phone] = [
            'code' => $otp,
            'expires' => time() + 300 // 5 minutes
        ];
    }
    
    /**
     * Verify OTP
     */
    public function verifyOTP($phone, $code) {
        $key = 'otp_' . $phone;
        
        if (!isset($_SESSION[$key])) {
            return false;
        }
        
        $data = $_SESSION[$key];
        
        if (time() > $data['expires']) {
            unset($_SESSION[$key]);
            return false;
        }
        
        if ($data['code'] !== $code) {
            return false;
        }
        
        unset($_SESSION[$key]);
        return true;
    }
}
