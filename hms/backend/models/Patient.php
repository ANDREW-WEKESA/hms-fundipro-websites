<?php
/**
 * Patient Model
 * HMS - Hospital Management System
 * Kenya Patient Management
 */

class Patient {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create new patient with Kenyan format validation
     */
    public function create($data) {
        // Validate Kenya phone format (+254 or 07/01)
        if (!$this->validateKenyanPhone($data['phone'])) {
            throw new Exception('Invalid Kenyan phone number format');
        }
        
        // Generate unique patient number
        $patientNumber = $this->generatePatientNumber();
        
        $sql = "INSERT INTO patients (
            facility_id, patient_number, first_name, last_name,
            date_of_birth, gender, phone, email, national_id,
            county, address, next_of_kin_name, next_of_kin_phone,
            insurance_provider, insurance_number
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['facility_id'],
            $patientNumber,
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $this->formatKenyanPhone($data['phone']),
            $data['email'] ?? null,
            $data['national_id'] ?? null,
            $data['county'] ?? 'Nairobi',
            $data['address'] ?? null,
            $data['next_of_kin_name'] ?? null,
            $data['next_of_kin_phone'] ?? null,
            $data['insurance_provider'] ?? null,
            $data['insurance_number'] ?? null
        ]);
    }
    
    /**
     * Validate Kenyan phone number format
     */
    private function validateKenyanPhone($phone) {
        // Match +254XXXXXXXXX or 07XXXXXXXX or 01XXXXXXXX
        $pattern = '/^(\+254|0)[17]\d{8}$/';
        return preg_match($pattern, $phone);
    }
    
    /**
     * Format phone to +254 format
     */
    private function formatKenyanPhone($phone) {
        $phone = preg_replace('/\s+/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            return '+254' . substr($phone, 1);
        }
        return $phone;
    }
    
    /**
     * Generate unique patient number
     */
    private function generatePatientNumber() {
        $year = date('Y');
        $month = date('m');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "PAT-{$year}{$month}-{$random}";
    }
    
    /**
     * Search patients by various criteria
     */
    public function search($criteria) {
        $sql = "SELECT * FROM patients WHERE facility_id = ?";
        $params = [$criteria['facility_id']];
        
        if (!empty($criteria['search'])) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? 
                     OR patient_number LIKE ? OR phone LIKE ?)";
            $search = "%{$criteria['search']}%";
            $params = array_merge($params, [$search, $search, $search, $search]);
        }
        
        if (!empty($criteria['national_id'])) {
            $sql .= " AND national_id = ?";
            $params[] = $criteria['national_id'];
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get patient by ID with insurance details
     */
    public function getById($id) {
        $sql = "SELECT p.*, ip.name as insurance_name 
                FROM patients p
                LEFT JOIN insurance_providers ip ON p.insurance_provider = ip.code
                WHERE p.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
