<?php
/**
 * Authentication Service - FundiPro
 * JWT token generation and OTP management
 * Phone-first auth for Kenya
 */
class AuthService {
    private $secret;

    public function __construct() {
        $this->secret = getenv('JWT_SECRET') ?: 'change-in-production';
    }

    public function generateToken($user) {
        $payload = [
            'iss' => 'fundipro.co.ke',
            'iat' => time(),
            'exp' => time() + (24 * 3600),
            'uid' => $user['id'],
            'role' => $user['role'],
            'phone' => $user['phone'],
        ];
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode($payload));
        $sig = hash_hmac('sha256', "$header.$payload", $this->secret, true);
        return "$header.$payload." . base64_encode($sig);
    }

    public function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        [$header, $payload, $sig] = $parts;
        $expected = base64_encode(hash_hmac('sha256', "$header.$payload", $this->secret, true));
        if (!hash_equals($expected, $sig)) return false;
        $data = json_decode(base64_decode($payload), true);
        if ($data['exp'] < time()) return false;
        return $data;
    }

    public function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
