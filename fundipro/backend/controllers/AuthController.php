<?php
/**
 * Auth Controller - FundiPro
 * Phone-first registration and login for Kenya
 */
class AuthController {
    private $userModel;
    private $authService;
    private $smsService;

    public function __construct($db) {
        $this->userModel   = new User($db);
        $this->authService = new AuthService();
        $this->smsService  = new SMSService();
    }

    // POST /api/auth/register
    public function register($data) {
        $required = ['phone', 'password', 'first_name', 'last_name', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Field '$field' is required."];
            }
        }

        try {
            $this->userModel->create($data);
            $user = $this->userModel->findByPhone($data['phone']);

            // Send OTP
            $otp = $this->authService->generateOTP();
            $_SESSION['otp_' . $data['phone']] = ['code' => $otp, 'exp' => time() + 300];
            $this->smsService->sendOTP($data['phone'], $otp);

            return ['success' => true, 'message' => 'OTP sent to ' . $data['phone']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // POST /api/auth/login
    public function login($data) {
        $credential = $data['phone'] ?? $data['email'] ?? null;
        if (!$credential || empty($data['password'])) {
            return ['success' => false, 'error' => 'Phone/email and password required.'];
        }

        $user = str_contains($credential, '@')
            ? $this->userModel->findByEmail($credential)
            : $this->userModel->findByPhone($credential);

        if (!$user || !$this->userModel->verifyPassword($user, $data['password'])) {
            return ['success' => false, 'error' => 'Invalid credentials.'];
        }

        $this->userModel->updateLastLogin($user['id']);
        $token = $this->authService->generateToken($user);

        return [
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'         => $user['id'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'phone'      => $user['phone'],
                'role'       => $user['role'],
            ],
        ];
    }

    // POST /api/auth/verify-otp
    public function verifyOTP($data) {
        $key = 'otp_' . ($data['phone'] ?? '');
        $stored = $_SESSION[$key] ?? null;
        if (!$stored || time() > $stored['exp'] || $stored['code'] !== $data['otp']) {
            return ['success' => false, 'error' => 'Invalid or expired OTP.'];
        }
        unset($_SESSION[$key]);
        // Mark phone verified
        return ['success' => true, 'message' => 'Phone verified successfully.'];
    }
}
