<?php

namespace HMS\Backend\Models;

use PDO;
use PDOException;
use DateTime;

/**
 * User Model
 * 
 * Handles all user-related database operations for the HMS system
 * Supports multiple user roles: Admin, Doctor, Nurse, Receptionist, Patient
 * 
 * @package HMS\Backend\Models
 * @version 1.0.0
 */
class User
{
    // Role constants
    public const ROLE_ADMIN = 'admin';
    public const ROLE_DOCTOR = 'doctor';
    public const ROLE_NURSE = 'nurse';
    public const ROLE_RECEPTIONIST = 'receptionist';
    public const ROLE_PATIENT = 'patient';

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_PENDING = 'pending';

    private PDO $db;
    
    public ?int $id = null;
    public ?string $uuid = null;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password_hash = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $phone = null;
    public ?string $role = null;
    public ?string $status = null;
    public ?string $profile_picture = null;
    public ?string $last_login = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * User constructor
     * 
     * @param PDO $db Database connection instance
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return User|null User object or null if not found
     */
    public function findById(int $id): ?User
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE id = :id 
                LIMIT 1
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return $this->mapRowToObject($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error finding user by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by email address
     * 
     * @param string $email Email address
     * @return User|null User object or null if not found
     */
    public function findByEmail(string $email): ?User
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE email = :email 
                LIMIT 1
            ");
            
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return $this->mapRowToObject($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find user by phone number (Kenya +254 format)
     * 
     * @param string $phone Phone number
     * @return User|null User object or null if not found
     */
    public function findByPhone(string $phone): ?User
    {
        try {
            $normalizedPhone = $this->normalizeKenyanPhone($phone);
            
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE phone = :phone 
                LIMIT 1
            ");
            
            $stmt->bindParam(':phone', $normalizedPhone, PDO::PARAM_STR);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return $this->mapRowToObject($row);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error finding user by phone: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|false User ID on success, false on failure
     */
    public function create(array $data): int|false
    {
        try {
            $uuid = $this->generateUuid();
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $normalizedPhone = $this->normalizeKenyanPhone($data['phone']);
            $now = date('Y-m-d H:i:s');
            
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    uuid, username, email, password_hash, 
                    first_name, last_name, phone, role, 
                    status, created_at, updated_at
                ) VALUES (
                    :uuid, :username, :email, :password_hash,
                    :first_name, :last_name, :phone, :role,
                    :status, :created_at, :updated_at
                )
            ");
            
            $status = $data['status'] ?? self::STATUS_PENDING;
            
            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':phone', $normalizedPhone);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            
            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user information
     * 
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            $allowedFields = [
                'username', 'email', 'first_name', 'last_name', 
                'phone', 'role', 'status', 'profile_picture'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if ($field === 'phone') {
                        $data[$field] = $this->normalizeKenyanPhone($data[$field]);
                    }
                    $fields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $fields[] = "updated_at = :updated_at";
            $params[':updated_at'] = date('Y-m-d H:i:s');
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     * 
     * @param int $id User ID
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password_hash = :password_hash, 
                    updated_at = :updated_at 
                WHERE id = :id
            ");
            
            $now = date('Y-m-d H:i:s');
            
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':updated_at', $now);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update last login timestamp
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function updateLastLogin(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET last_login = :last_login 
                WHERE id = :id
            ");
            
            $now = date('Y-m-d H:i:s');
            
            $stmt->bindParam(':last_login', $now);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users with optional filtering
     * 
     * @param array $filters Optional filters (role, status, search)
     * @param int $limit Results limit
     * @param int $offset Results offset
     * @return array Array of User objects
     */
    public function getAll(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT * FROM users WHERE 1=1";
            $params = [];
            
            if (!empty($filters['role'])) {
                $sql .= " AND role = :role";
                $params[':role'] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $this->mapRowToObject($row);
            }
            
            return $users;
        } catch (PDOException $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete user (soft delete by setting status to inactive)
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = :status, 
                    updated_at = :updated_at 
                WHERE id = :id
            ");
            
            $status = self::STATUS_INACTIVE;
            $now = date('Y-m-d H:i:s');
            
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':updated_at', $now);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalize Kenyan phone number to +254 format
     * 
     * @param string $phone Phone number
     * @return string Normalized phone number
     */
    private function normalizeKenyanPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '+254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '254')) {
            $phone = '+' . $phone;
        } elseif (!str_starts_with($phone, '+254')) {
            $phone = '+254' . $phone;
        }
        
        return $phone;
    }

    /**
     * Generate UUID v4
     * 
     * @return string UUID
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Map database row to User object
     * 
     * @param array $row Database row
     * @return User User object
     */
    private function mapRowToObject(array $row): User
    {
        $user = new User($this->db);
        $user->id = (int) $row['id'];
        $user->uuid = $row['uuid'];
        $user->username = $row['username'];
        $user->email = $row['email'];
        $user->password_hash = $row['password_hash'];
        $user->first_name = $row['first_name'] ?? null;
        $user->last_name = $row['last_name'] ?? null;
        $user->phone = $row['phone'] ?? null;
        $user->role = $row['role'];
        $user->status = $row['status'];
        $user->profile_picture = $row['profile_picture'] ?? null;
        $user->last_login = $row['last_login'] ?? null;
        $user->created_at = $row['created_at'];
        $user->updated_at = $row['updated_at'];
        
        return $user;
    }
}
