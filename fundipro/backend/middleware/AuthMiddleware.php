<?php
/**
 * Auth Middleware - FundiPro
 * JWT validation for protected routes
 */
class AuthMiddleware {
    private $auth;

    public function __construct() {
        $this->auth = new AuthService();
    }

    public function handle() {
        $headers = getallheaders();
        $token   = null;

        if (!empty($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)$/i', $headers['Authorization'], $m)) {
                $token = $m[1];
            }
        }

        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required.']);
            exit;
        }

        $user = $this->auth->validateToken($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired token.']);
            exit;
        }

        return $user;
    }
}
