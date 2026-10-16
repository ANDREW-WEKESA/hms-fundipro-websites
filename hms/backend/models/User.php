<?php
/**
 * User Model
 * HMS - Hospital Management System
 * Kenya User Management
 */

class User {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create new user with hashed password
     */
    public function create($data) {
        // Validate Kenya phone format
        if (!$this->validateKenyanPhone($data['phone'])) {
            throw new Exception('Invalid Kenyan phone number format');
        }
        
        // Hash password using bcrypt
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = "INSERT INTO users (
            facility_id, email, phone, password_hash, role,
            first_name, last_name, national_id, license_number, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['facility_id'],
            $data['email'],
            $this->formatKenyanPhone($data['phone']),
            $passwordHash,
            $data['role'],
            $data['first_name'],
            $data['last_name'],
            $data['national_id'] ?? null,
            $data['license_number'] ?? null,
            $data['is_active'] ?? true
        ]);
    }
    
    /**
     * Find user by email or phone
     */
    public function findByCredential($credential) {
        $sql = "SELECT * FROM users WHERE (email = ? OR phone = ?) AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$credential, $credential]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Validate Kenya phone number
     */
    private function validateKenyanPhone($phone) {
        $pattern = '/^(\+254|0)[17]\d{8}$/';
        return preg_match($pattern, $phone);
    }
    
    /**
     * Format to +254 format
     */
    private function formatKenyanPhone($phone) {
        $phone = preg_replace('/\s+/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            return '+254' . substr($phone, 1);
        }
        return $phone;
    }
    
    /**
     * Check if user has role
     */
    public function hasRole($userId, $allowedRoles) {
        $sql = "SELECT role FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && in_array($user['role'], (array)$allowedRoles);
    }
}
