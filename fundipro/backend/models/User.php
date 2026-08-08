<?php
/**
 * User Model - FundiPro
 * Handles both professionals and customers
 * Kenya phone-first authentication
 */
class User {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function create($data) {
        if (!$this->validateKenyanPhone($data['phone'])) {
            throw new Exception('Invalid Kenyan phone number. Use +254 or 07/01 format.');
        }
        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = "INSERT INTO users (phone, email, password_hash, role, first_name, last_name)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->formatPhone($data['phone']),
            $data['email'] ?? null,
            $hash,
            $data['role'],
            $data['first_name'],
            $data['last_name']
        ]);
    }

    public function findByPhone($phone) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE phone = ? AND is_active = 1");
        $stmt->execute([$this->formatPhone($phone)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }

    private function validateKenyanPhone($phone) {
        return preg_match('/^(\+254|0)[17]\d{8}$/', preg_replace('/\s+/', '', $phone));
    }

    public function formatPhone($phone) {
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '+254' . substr($phone, 1);
        }
        return $phone;
    }

    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
