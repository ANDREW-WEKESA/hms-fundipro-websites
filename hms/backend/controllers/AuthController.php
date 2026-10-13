<?php

namespace HMS\Backend\Controllers;

use HMS\Backend\Models\User;
use HMS\Backend\Services\AuthService;
use HMS\Backend\Services\SMSService;
use PDO;

/**
 * Authentication Controller
 * 
 * Handles all authentication-related HTTP requests
 * Includes login, registration, logout, password management
 * 
 * @package HMS\Backend\Controllers
 * @version 1.0.0
 */
class AuthController
{
    private PDO $db;
    private AuthService $authService;
    private SMSService $smsService;
    private User $userModel;

    /**
     * AuthController constructor
     * 
     * @param PDO $db Database connection
     * @param AuthService $authService Authentication service
     * @param SMSService $smsService SMS service
     */
    public function __construct(PDO $db, AuthService $authService, SMSService $smsService)
    {
        $this->db = $db;
        $this->authService = $authService;
        $this->smsService = $smsService;
        $this->userModel = new User($db);
    }

    /**
     * User login
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function login(array $request): array
    {
        try {
            // Validate input
            $validation = $this->validateLoginInput($request);
            if (!$validation['valid']) {
                return $this->jsonResponse($validation['errors'], 400);
            }

            $identifier = $request['identifier']; // email, phone, or username
            $password = $request['password'];

            // Find user by email, phone, or username
            $user = null;
            
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $user = $this->userModel->findByEmail($identifier);
            } elseif (preg_match('/^(\+254|254|0)[17]\d{8}$/', $identifier)) {
                $user = $this->userModel->findByPhone($identifier);
            } else {
                // Try to find by username
                $users = $this->userModel->getAll(['search' => $identifier], 1, 0);
                if (!empty($users)) {
                    $user = $users[0];
                }
            }

            if (!$user) {
                return $this->jsonResponse(['message' => 'Invalid credentials'], 401);
            }

            // Verify password
            if (!$this->authService->verifyPassword($password, $user->password_hash)) {
                return $this->jsonResponse(['message' => 'Invalid credentials'], 401);
            }

            // Check user status
            if ($user->status !== User::STATUS_ACTIVE) {
                return $this->jsonResponse([
                    'message' => 'Account is not active',
                    'status' => $user->status
                ], 403);
            }

            // Generate JWT token
            $token = $this->authService->generateToken($user->id, $user->role, [
                'username' => $user->username,
                'email' => $user->email
            ]);

            // Update last login
            $this->userModel->updateLastLogin($user->id);

            // Prepare user data (exclude password)
            $userData = [
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
                'last_login' => $user->last_login
            ];

            return $this->jsonResponse([
                'message' => 'Login successful',
                'token' => $token,
                'user' => $userData
            ], 200);

        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * User registration
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function register(array $request): array
    {
        try {
            // Validate input
            $validation = $this->validateRegistrationInput($request);
            if (!$validation['valid']) {
                return $this->jsonResponse($validation['errors'], 400);
            }

            // Check if user already exists
            $existingUser = $this->userModel->findByEmail($request['email']);
            if ($existingUser) {
                return $this->jsonResponse(['message' => 'Email already registered'], 400);
            }

            $existingPhone = $this->userModel->findByPhone($request['phone']);
            if ($existingPhone) {
                return $this->jsonResponse(['message' => 'Phone number already registered'], 400);
            }

            // Create user
            $userData = [
                'username' => $request['username'],
                'email' => $request['email'],
                'password' => $request['password'],
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'phone' => $request['phone'],
                'role' => $request['role'] ?? User::ROLE_PATIENT,
                'status' => User::STATUS_PENDING
            ];

            $userId = $this->userModel->create($userData);

            if (!$userId) {
                return $this->jsonResponse(['message' => 'Failed to create user'], 500);
            }

            // Send OTP for phone verification
            $otp = $this->smsService->generateOTP();
            $this->smsService->sendOTP($request['phone'], $otp);

            // Get created user
            $user = $this->userModel->findById($userId);

            return $this->jsonResponse([
                'message' => 'Registration successful. Please verify your phone number.',
                'user_id' => $userId,
                'phone' => $request['phone'],
                'otp_sent' => true
            ], 201);

        } catch (\Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * User logout
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function logout(array $request): array
    {
        try {
            $token = $this->extractToken($request);

            if (!$token) {
                return $this->jsonResponse(['message' => 'No token provided'], 400);
            }

            // Revoke token
            $revoked = $this->authService->revokeToken($token);

            if (!$revoked) {
                return $this->jsonResponse(['message' => 'Failed to logout'], 500);
            }

            return $this->jsonResponse(['message' => 'Logout successful'], 200);

        } catch (\Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Refresh JWT token
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function refreshToken(array $request): array
    {
        try {
            $token = $this->extractToken($request);

            if (!$token) {
                return $this->jsonResponse(['message' => 'No token provided'], 400);
            }

            $newToken = $this->authService->refreshToken($token);

            if (!$newToken) {
                return $this->jsonResponse(['message' => 'Failed to refresh token'], 401);
            }

            return $this->jsonResponse([
                'message' => 'Token refreshed successfully',
                'token' => $newToken
            ], 200);

        } catch (\Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Forgot password - send reset link
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function forgotPassword(array $request): array
    {
        try {
            if (empty($request['email'])) {
                return $this->jsonResponse(['message' => 'Email is required'], 400);
            }

            $email = filter_var($request['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                return $this->jsonResponse(['message' => 'Invalid email format'], 400);
            }

            $user = $this->userModel->findByEmail($email);

            // Always return success to prevent email enumeration
            if (!$user) {
                return $this->jsonResponse([
                    'message' => 'If the email exists, a reset link has been sent'
                ], 200);
            }

            // Generate reset token
            $resetToken = $this->authService->generatePasswordResetToken($user->id, $email);

            // Send reset link via SMS (Kenya context)
            if ($user->phone) {
                $resetLink = "https://hms.fundipro.co.ke/reset-password?token=" . $resetToken;
                $message = "HMS Password Reset: Use this link to reset your password: " . $resetLink . " (Valid for 1 hour)";
                $this->smsService->sendMessage($user->phone, $message);
            }

            return $this->jsonResponse([
                'message' => 'If the email exists, a reset link has been sent'
            ], 200);

        } catch (\Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Reset password with token
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function resetPassword(array $request): array
    {
        try {
            $validation = $this->validateResetPasswordInput($request);
            if (!$validation['valid']) {
                return $this->jsonResponse($validation['errors'], 400);
            }

            $token = $request['token'];
            $email = $request['email'];
            $newPassword = $request['password'];

            // Validate token
            $userId = $this->authService->validatePasswordResetToken($token, $email);

            if (!$userId) {
                return $this->jsonResponse(['message' => 'Invalid or expired reset token'], 400);
            }

            // Update password
            $updated = $this->userModel->updatePassword($userId, $newPassword);

            if (!$updated) {
                return $this->jsonResponse(['message' => 'Failed to update password'], 500);
            }

            // Mark token as used
            $this->authService->markPasswordResetTokenUsed($token);

            return $this->jsonResponse(['message' => 'Password reset successful'], 200);

        } catch (\Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Change password (authenticated user)
     * 
     * @param array $request Request data
     * @return array JSON response
     */
    public function changePassword(array $request): array
    {
        try {
            // Validate input
            if (empty($request['current_password']) || empty($request['new_password'])) {
                return $this->jsonResponse([
                    'message' => 'Current password and new password are required'
                ], 400);
            }

            // Get current user from token
            $token = $this->extractToken($request);
            $payload = $this->authService->validateToken($token);

            if (!$payload) {
                return $this->jsonResponse(['message' => 'Unauthorized'], 401);
            }

            $userId = $payload['sub'];
            $user = $this->userModel->findById($userId);

            if (!$user) {
                return $this->jsonResponse(['message' => 'User not found'], 404);
            }

            // Verify current password
            if (!$this->authService->verifyPassword($request['current_password'], $user->password_hash)) {
                return $this->jsonResponse(['message' => 'Current password is incorrect'], 400);
            }

            // Validate new password
            $passwordValidation = $this->validatePassword($request['new_password']);
            if (!$passwordValidation['valid']) {
                return $this->jsonResponse($passwordValidation['errors'], 400);
            }

            // Update password
            $updated = $this->userModel->updatePassword($userId, $request['new_password']);

            if (!$updated) {
                return $this->jsonResponse(['message' => 'Failed to change password'], 500);
            }

            return $this->jsonResponse(['message' => 'Password changed successfully'], 200);

        } catch (\Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return $this->jsonResponse(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Validate login input
     * 
     * @param array $data Input data
     * @return array Validation result
     */
    private function validateLoginInput(array $data): array
    {
        $errors = [];

        if (empty($data['identifier'])) {
            $errors['identifier'] = 'Email, phone, or username is required';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate registration input
     * 
     * @param array $data Input data
     * @return array Validation result
     */
    private function validateRegistrationInput(array $data): array
    {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Phone validation (Kenya +254 format)
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^(\+254|254|0)[17]\d{8}$/', $data['phone'])) {
            $errors['phone'] = 'Invalid Kenyan phone number format';
        }

        // Password validation
        $passwordValidation = $this->validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        }

        // Name validation
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate reset password input
     * 
     * @param array $data Input data
     * @return array Validation result
     */
    private function validateResetPasswordInput(array $data): array
    {
        $errors = [];

        if (empty($data['token'])) {
            $errors['token'] = 'Reset token is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        }

        $passwordValidation = $this->validatePassword($data['password'] ?? '');
        if (!$passwordValidation['valid']) {
            $errors = array_merge($errors, $passwordValidation['errors']);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate password strength
     * 
     * @param string $password Password
     * @return array Validation result
     */
    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must contain at least one number';
        } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors['password'] = 'Password must contain at least one special character';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Extract Bearer token from request
     * 
     * @param array $request Request data
     * @return string|null Token or null
     */
    private function extractToken(array $request): ?string
    {
        if (isset($request['headers']['Authorization'])) {
            $authHeader = $request['headers']['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Create JSON response
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return array Response
     */
    private function jsonResponse(array $data, int $statusCode): array
    {
        return [
            'status' => $statusCode,
            'data' => $data
        ];
    }
}
