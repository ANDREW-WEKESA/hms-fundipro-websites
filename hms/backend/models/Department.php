<?php

namespace HMS\Backend\Models;

use PDO;
use PDOException;

/**
 * Department Model
 * Manages hospital departments and their operations
 */
class Department
{
    private $pdo;
    private $table = 'departments';

    // Properties
    public $id;
    public $name;
    public $code;
    public $description;
    public $head_doctor_id;
    public $location;
    public $phone;
    public $email;
    public $status;
    public $created_at;
    public $updated_at;

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all departments
     */
    public function getAll($status = null)
    {
        $query = "SELECT d.*,
                         CONCAT(u.first_name, ' ', u.last_name) as head_doctor_name,
                         u.specialization as head_doctor_specialization
                  FROM {$this->table} d
                  LEFT JOIN users u ON d.head_doctor_id = u.id";

        if ($status) {
            $query .= " WHERE d.status = :status";
        }

        $query .= " ORDER BY d.name ASC";

        try {
            $stmt = $this->pdo->prepare($query);
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching departments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find department by ID
     */
    public function findById($id)
    {
        $query = "SELECT d.*,
                         CONCAT(u.first_name, ' ', u.last_name) as head_doctor_name,
                         u.phone as head_doctor_phone,
                         u.email as head_doctor_email
                  FROM {$this->table} d
                  LEFT JOIN users u ON d.head_doctor_id = u.id
                  WHERE d.id = :id
                  LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding department: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find department by code
     */
    public function findByCode($code)
    {
        $query = "SELECT * FROM {$this->table} WHERE code = :code LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding department by code: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new department
     */
    public function create()
    {
        $query = "INSERT INTO {$this->table} 
                  (name, code, description, head_doctor_id, location, phone, email, status)
                  VALUES 
                  (:name, :code, :description, :head_doctor_id, :location, :phone, :email, :status)";

        try {
            $stmt = $this->pdo->prepare($query);
            
            $this->status = $this->status ?? self::STATUS_ACTIVE;
            $this->code = $this->code ?? $this->generateCode($this->name);

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':code', $this->code);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':head_doctor_id', $this->head_doctor_id);
            $stmt->bindParam(':location', $this->location);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':status', $this->status);

            if ($stmt->execute()) {
                $this->id = $this->pdo->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Department creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update department
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed_fields = ['name', 'code', 'description', 'head_doctor_id', 'location', 'phone', 'email', 'status'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Department update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete department (soft delete)
     */
    public function delete($id)
    {
        $query = "UPDATE {$this->table} SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($query);
            $status = self::STATUS_INACTIVE;
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Department deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get doctors in department
     */
    public function getDoctors($department_id)
    {
        $query = "SELECT u.*,
                         dd.is_primary,
                         COUNT(DISTINCT a.id) as total_appointments,
                         COUNT(DISTINCT CASE WHEN a.appointment_date >= CURDATE() THEN a.id END) as upcoming_appointments
                  FROM users u
                  INNER JOIN doctor_departments dd ON u.id = dd.doctor_id
                  LEFT JOIN appointments a ON u.id = a.doctor_id
                  WHERE dd.department_id = :department_id
                  AND u.role = 'doctor'
                  AND u.status = 'active'
                  GROUP BY u.id
                  ORDER BY dd.is_primary DESC, u.first_name ASC";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching department doctors: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get department statistics
     */
    public function getStats($department_id)
    {
        try {
            $stats = [
                'total_doctors' => 0,
                'total_appointments_today' => 0,
                'total_appointments_week' => 0,
                'total_appointments_month' => 0,
                'active_patients' => 0,
                'revenue_month' => 0
            ];

            // Count doctors
            $query = "SELECT COUNT(DISTINCT dd.doctor_id) as count
                      FROM doctor_departments dd
                      INNER JOIN users u ON dd.doctor_id = u.id
                      WHERE dd.department_id = :department_id
                      AND u.status = 'active'";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_doctors'] = $result['count'] ?? 0;

            // Today's appointments
            $query = "SELECT COUNT(*) as count
                      FROM appointments
                      WHERE department_id = :department_id
                      AND appointment_date = CURDATE()
                      AND status NOT IN ('cancelled', 'no_show')";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_appointments_today'] = $result['count'] ?? 0;

            // This week's appointments
            $query = "SELECT COUNT(*) as count
                      FROM appointments
                      WHERE department_id = :department_id
                      AND YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1)
                      AND status NOT IN ('cancelled', 'no_show')";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_appointments_week'] = $result['count'] ?? 0;

            // This month's appointments
            $query = "SELECT COUNT(*) as count
                      FROM appointments
                      WHERE department_id = :department_id
                      AND MONTH(appointment_date) = MONTH(CURDATE())
                      AND YEAR(appointment_date) = YEAR(CURDATE())
                      AND status NOT IN ('cancelled', 'no_show')";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_appointments_month'] = $result['count'] ?? 0;

            // Active patients (unique patients this month)
            $query = "SELECT COUNT(DISTINCT patient_id) as count
                      FROM appointments
                      WHERE department_id = :department_id
                      AND MONTH(appointment_date) = MONTH(CURDATE())
                      AND YEAR(appointment_date) = YEAR(CURDATE())";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active_patients'] = $result['count'] ?? 0;

            return $stats;

        } catch (PDOException $e) {
            error_log("Error fetching department stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate department code from name
     */
    private function generateCode($name)
    {
        // Get first 3 letters of each word, uppercase
        $words = explode(' ', $name);
        $code = '';
        
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 3));
        }

        return substr($code, 0, 10); // Max 10 characters
    }

    /**
     * Seed common departments
     */
    public static function seedCommonDepartments($pdo)
    {
        $departments = [
            [
                'name' => 'Out Patient Department (OPD)',
                'code' => 'OPD',
                'description' => 'General outpatient consultation and primary care services',
                'location' => 'Ground Floor, Wing A'
            ],
            [
                'name' => 'Emergency Department',
                'code' => 'EMERG',
                'description' => '24/7 emergency and trauma services',
                'location' => 'Ground Floor, Main Entrance'
            ],
            [
                'name' => 'Pediatrics',
                'code' => 'PEDS',
                'description' => 'Child healthcare and pediatric services',
                'location' => 'First Floor, Wing B'
            ],
            [
                'name' => 'Maternity',
                'code' => 'MAT',
                'description' => 'Maternal health, labor, delivery and postnatal care',
                'location' => 'Second Floor, Wing C'
            ],
            [
                'name' => 'Surgery',
                'code' => 'SURG',
                'description' => 'Surgical procedures and post-operative care',
                'location' => 'Third Floor, Wing A'
            ],
            [
                'name' => 'Laboratory',
                'code' => 'LAB',
                'description' => 'Medical testing, diagnostics and pathology',
                'location' => 'Ground Floor, Wing B'
            ],
            [
                'name' => 'Pharmacy',
                'code' => 'PHARM',
                'description' => 'Medication dispensing and pharmaceutical services',
                'location' => 'Ground Floor, Main Building'
            ],
            [
                'name' => 'Radiology',
                'code' => 'RAD',
                'description' => 'X-ray, CT scan, ultrasound and imaging services',
                'location' => 'Basement, Wing A'
            ],
            [
                'name' => 'Intensive Care Unit (ICU)',
                'code' => 'ICU',
                'description' => 'Critical care for severely ill patients',
                'location' => 'Fourth Floor, Wing A'
            ],
            [
                'name' => 'Dental',
                'code' => 'DENT',
                'description' => 'Dental care and oral health services',
                'location' => 'First Floor, Wing C'
            ]
        ];

        $dept = new Department($pdo);
        $created = 0;

        foreach ($departments as $data) {
            // Check if department already exists
            if (!$dept->findByCode($data['code'])) {
                $dept->name = $data['name'];
                $dept->code = $data['code'];
                $dept->description = $data['description'];
                $dept->location = $data['location'];
                $dept->status = self::STATUS_ACTIVE;

                if ($dept->create()) {
                    $created++;
                }
            }
        }

        return $created;
    }
}
