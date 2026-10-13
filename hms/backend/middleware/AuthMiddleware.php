<?php

namespace HMS\Backend\Middleware;

use HMS\Backend\Models\User;
use HMS\Backend\Services\AuthService;
use PDO;

/**
 * Authentication Middleware
 * 
 * Validates JWT tokens and authenticates requests
 * Loads user information and attaches to request context
 * 
 * @package HMS\Backend\Middleware
 * @version 1.0.0
 */
class AuthMiddleware
{
    private AuthService $authService;
    private User $userModel;
    private PDO $db;

    /**
     * AuthMiddleware constructor
     * 
     * @param PDO $db Database connection
     * @param AuthService $authService Authentication service
     */
    public function __construct(PDO $db, AuthService $authService)
    {
        $this->db = $db;
        $this->authService = $authService;
        $this->userModel = new User($db);
    }

    /**
     * Handle authentication middleware
     * 
     * @param array $request Request data
     * @return array Response with authenticated user or error
     */
    public function handle(array $request): array
    {
        try {
            // Extract token from request
            $token = $this->extractToken($request);

            if (!$token) {
                return $this->unauthorizedResponse('No authentication token provided');
            }

            // Validate and decode token
            $payload = $this->validateAndDecode($token);

            if (!$payload) {
                return $this->unauthorizedResponse('Invalid or expired token');
            }

            // Load user from database
            $userId = $payload['sub'];
            $user = $this->userModel->findById($userId);

            if (!$user) {
                return $this->unauthorizedResponse('User not found');
            }

            // Check user status
            if ($user->status !== User::STATUS_ACTIVE) {
                return $this->forbiddenResponse('Account is not active');
            }

            // Set current user in request context
            $userData = $this->setCurrentUser($user, $payload);

            // Log authentication event
            $this->logAuthenticationEvent($user, true, $request);

            return [
                'authenticated' => true,
                'user' => $userData
            ];

        } catch (\Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return $this->unauthorizedResponse('Authentication failed');
        }
    }

    /**
     * Extract Bearer token from request
     * 
     * @param array $request Request data
     * @return string|null Token or null if not found
     */
    public function extractToken(array $request): ?string
    {
        // Check Authorization header
        if (isset($request['headers']['Authorization'])) {
            $authHeader = $request['headers']['Authorization'];
            
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return trim($matches[1]);
            }
        }

        // Check authorization header (lowercase)
        if (isset($request['headers']['authorization'])) {
            $authHeader = $request['headers']['authorization'];
            
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return trim($matches[1]);
            }
        }

        // Check query parameter (fallback for websockets, etc.)
        if (isset($request['query']['token'])) {
            return $request['query']['token'];
        }

        // Check cookie
        if (isset($request['cookies']['auth_token'])) {
            return $request['cookies']['auth_token'];
        }

        return null;
    }

    /**
     * Validate and decode JWT token
     * 
     * @param string $token JWT token
     * @return array|null Token payload or null if invalid
     */
    public function validateAndDecode(string $token): ?array
    {
        return $this->authService->validateToken($token);
    }

    /**
     * Set current user data from User object and token payload
     * 
     * @param User $user User object
     * @param array $payload Token payload
     * @return array User data for request context
     */
    public function setCurrentUser(User $user, array $payload): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'username' => $user->username,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'role' => $user->role,
            'status' => $user->status,
            'profile_picture' => $user->profile_picture,
            'token_issued_at' => $payload['iat'] ?? null,
            'token_expires_at' => $payload['exp'] ?? null
        ];
    }

    /**
     * Get current user from request
     * 
     * @param array $request Request data
     * @return array|null User data or null
     */
    public function getCurrentUser(array $request): ?array
    {
        return $request['user'] ?? null;
    }

    /**
     * Check if request is authenticated
     * 
     * @param array $request Request data
     * @return bool True if authenticated
     */
    public function isAuthenticated(array $request): bool
    {
        return isset($request['user']) && !empty($request['user']);
    }

    /**
     * Require authentication or return error
     * 
     * @param array $request Request data
     * @return array|null Null if authenticated, error response otherwise
     */
    public function requireAuth(array $request): ?array
    {
        if (!$this->isAuthenticated($request)) {
            return $this->unauthorizedResponse('Authentication required');
        }

        return null;
    }

    /**
     * Optional authentication (doesn't fail if not authenticated)
     * 
     * @param array $request Request data
     * @return array Request with user data if authenticated
     */
    public function optionalAuth(array $request): array
    {
        $result = $this->handle($request);

        if ($result['authenticated'] ?? false) {
            $request['user'] = $result['user'];
        }

        return $request;
    }

    /**
     * Validate API key (alternative authentication method)
     * 
     * @param array $request Request data
     * @return array Response with validation result
     */
    public function validateApiKey(array $request): array
    {
        $apiKey = $request['headers']['X-API-Key'] ?? 
                  $request['headers']['x-api-key'] ?? 
                  null;

        if (!$apiKey) {
            return $this->unauthorizedResponse('API key required');
        }

        // Validate API key against database
        try {
            $stmt = $this->db->prepare("
                SELECT user_id, permissions, status, expires_at
                FROM api_keys
                WHERE key_hash = :key_hash
                LIMIT 1
            ");

            $keyHash = hash('sha256', $apiKey);
            $stmt->bindParam(':key_hash', $keyHash);
            $stmt->execute();

            $keyData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$keyData) {
                return $this->unauthorizedResponse('Invalid API key');
            }

            // Check expiration
            if ($keyData['expires_at'] && strtotime($keyData['expires_at']) < time()) {
                return $this->unauthorizedResponse('API key expired');
            }

            // Check status
            if ($keyData['status'] !== 'active') {
                return $this->unauthorizedResponse('API key inactive');
            }

            // Load user
            $user = $this->userModel->findById($keyData['user_id']);

            if (!$user || $user->status !== User::STATUS_ACTIVE) {
                return $this->unauthorizedResponse('User account inactive');
            }

            return [
                'authenticated' => true,
                'auth_method' => 'api_key',
                'user' => [
                    'id' => $user->id,
                    'uuid' => $user->uuid,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'api_permissions' => json_decode($keyData['permissions'], true)
                ]
            ];

        } catch (\Exception $e) {
            error_log("API key validation error: " . $e->getMessage());
            return $this->unauthorizedResponse('API key validation failed');
        }
    }

    /**
     * Log authentication event
     * 
     * @param User $user User object
     * @param bool $success Authentication success status
     * @param array $request Request data
     * @return void
     */
    private function logAuthenticationEvent(User $user, bool $success, array $request): void
    {
        try {
            $ipAddress = $request['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $request['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                INSERT INTO authentication_logs (
                    user_id, 
                    ip_address, 
                    user_agent, 
                    status, 
                    created_at
                ) VALUES (
                    :user_id, 
                    :ip_address, 
                    :user_agent, 
                    :status, 
                    :created_at
                )
            ");

            $status = $success ? 'success' : 'failed';

            $stmt->bindParam(':user_id', $user->id, PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->bindParam(':user_agent', $userAgent);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':created_at', $now);

            $stmt->execute();

        } catch (\Exception $e) {
            error_log("Failed to log authentication event: " . $e->getMessage());
        }
    }

    /**
     * Get user's active sessions
     * 
     * @param int $userId User ID
     * @return array Array of active sessions
     */
    public function getActiveSessions(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id,
                    ip_address,
                    user_agent,
                    created_at,
                    expires_at
                FROM user_sessions
                WHERE user_id = :user_id
                AND expires_at > NOW()
                AND revoked_at IS NULL
                ORDER BY created_at DESC
            ");

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log("Failed to get active sessions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Revoke all user sessions
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function revokeAllSessions(int $userId): bool
    {
        try {
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                UPDATE user_sessions
                SET revoked_at = :revoked_at
                WHERE user_id = :user_id
                AND revoked_at IS NULL
            ");

            $stmt->bindParam(':revoked_at', $now);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (\Exception $e) {
            error_log("Failed to revoke sessions: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check for suspicious login patterns
     * 
     * @param int $userId User ID
     * @param string $ipAddress IP address
     * @return bool True if suspicious
     */
    public function isSuspiciousLogin(int $userId, string $ipAddress): bool
    {
        try {
            // Check for multiple failed attempts
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as failed_count
                FROM authentication_logs
                WHERE user_id = :user_id
                AND status = 'failed'
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['failed_count'] >= 5) {
                return true;
            }

            // Check for login from new location
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as login_count
                FROM authentication_logs
                WHERE user_id = :user_id
                AND ip_address = :ip_address
                AND status = 'success'
            ");

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ipAddress);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // New location
            if ($result['login_count'] === 0) {
                return true;
            }

            return false;

        } catch (\Exception $e) {
            error_log("Failed to check suspicious login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create unauthorized response
     * 
     * @param string $message Error message
     * @return array Response array
     */
    private function unauthorizedResponse(string $message): array
    {
        return [
            'authenticated' => false,
            'status' => 401,
            'data' => [
                'message' => $message,
                'error' => 'Unauthorized'
            ]
        ];
    }

    /**
     * Create forbidden response
     * 
     * @param string $message Error message
     * @return array Response array
     */
    private function forbiddenResponse(string $message): array
    {
        return [
            'authenticated' => false,
            'status' => 403,
            'data' => [
                'message' => $message,
                'error' => 'Forbidden'
            ]
        ];
    }
}
