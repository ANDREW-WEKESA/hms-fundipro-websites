<?php

namespace HMS\Backend\Models;

use PDO;
use PDOException;

/**
 * Appointment Model
 * Manages patient appointment booking lifecycle
 */
class Appointment
{
    private $pdo;
    private $table = 'appointments';

    // Appointment properties
    public $id;
    public $uuid;
    public $patient_id;
    public $doctor_id;
    public $department_id;
    public $appointment_date;
    public $appointment_time;
    public $duration_minutes;
    public $status; // scheduled, confirmed, in_progress, completed, cancelled, no_show
    public $type; // consultation, follow_up, emergency, procedure
    public $reason;
    public $notes;
    public $created_by;
    public $created_at;
    public $updated_at;

    // Valid statuses
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    // Valid types
    const TYPE_CONSULTATION = 'consultation';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_PROCEDURE = 'procedure';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create new appointment
     */
    public function create()
    {
        $query = "INSERT INTO {$this->table} 
                  (uuid, patient_id, doctor_id, department_id, appointment_date, 
                   appointment_time, duration_minutes, status, type, reason, notes, created_by)
                  VALUES 
                  (:uuid, :patient_id, :doctor_id, :department_id, :appointment_date,
                   :appointment_time, :duration_minutes, :status, :type, :reason, :notes, :created_by)";

        try {
            $stmt = $this->pdo->prepare($query);
            
            $this->uuid = $this->uuid ?? $this->generateUUID();
            $this->status = $this->status ?? self::STATUS_SCHEDULED;
            $this->duration_minutes = $this->duration_minutes ?? 30;

            $stmt->bindParam(':uuid', $this->uuid);
            $stmt->bindParam(':patient_id', $this->patient_id);
            $stmt->bindParam(':doctor_id', $this->doctor_id);
            $stmt->bindParam(':department_id', $this->department_id);
            $stmt->bindParam(':appointment_date', $this->appointment_date);
            $stmt->bindParam(':appointment_time', $this->appointment_time);
            $stmt->bindParam(':duration_minutes', $this->duration_minutes);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':type', $this->type);
            $stmt->bindParam(':reason', $this->reason);
            $stmt->bindParam(':notes', $this->notes);
            $stmt->bindParam(':created_by', $this->created_by);

            if ($stmt->execute()) {
                $this->id = $this->pdo->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Appointment creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find appointment by ID
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find appointments by patient ID
     */
    public function findByPatient($patient_id, $limit = 50, $offset = 0)
    {
        $query = "SELECT a.*, 
                         u.first_name as doctor_first_name,
                         u.last_name as doctor_last_name,
                         d.name as department_name
                  FROM {$this->table} a
                  LEFT JOIN users u ON a.doctor_id = u.id
                  LEFT JOIN departments d ON a.department_id = d.id
                  WHERE a.patient_id = :patient_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find appointments by doctor ID
     */
    public function findByDoctor($doctor_id, $limit = 50, $offset = 0)
    {
        $query = "SELECT a.*,
                         p.first_name as patient_first_name,
                         p.last_name as patient_last_name,
                         p.phone as patient_phone
                  FROM {$this->table} a
                  LEFT JOIN patients p ON a.patient_id = p.id
                  WHERE a.doctor_id = :doctor_id
                  ORDER BY a.appointment_date DESC, a.appointment_time DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find appointments by date
     */
    public function findByDate($date, $doctor_id = null)
    {
        $query = "SELECT a.*,
                         p.first_name as patient_first_name,
                         p.last_name as patient_last_name
                  FROM {$this->table} a
                  LEFT JOIN patients p ON a.patient_id = p.id
                  WHERE a.appointment_date = :date";

        if ($doctor_id) {
            $query .= " AND a.doctor_id = :doctor_id";
        }

        $query .= " ORDER BY a.appointment_time ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':date', $date);
        if ($doctor_id) {
            $stmt->bindParam(':doctor_id', $doctor_id);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find appointments by date range
     */
    public function findByDateRange($start_date, $end_date, $doctor_id = null, $status = null)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE appointment_date BETWEEN :start_date AND :end_date";

        if ($doctor_id) {
            $query .= " AND doctor_id = :doctor_id";
        }
        if ($status) {
            $query .= " AND status = :status";
        }

        $query .= " ORDER BY appointment_date ASC, appointment_time ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        if ($doctor_id) {
            $stmt->bindParam(':doctor_id', $doctor_id);
        }
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update appointment status with validation
     */
    public function updateStatus($id, $new_status)
    {
        if (!$this->isValidStatusTransition($id, $new_status)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET status = :status, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Cancel appointment
     */
    public function cancel($id, $reason = null)
    {
        $query = "UPDATE {$this->table} 
                  SET status = :status, notes = CONCAT(notes, '\nCancellation reason: ', :reason),
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        $status = self::STATUS_CANCELLED;
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcoming($doctor_id = null, $days = 7)
    {
        $end_date = date('Y-m-d', strtotime("+{$days} days"));
        $today = date('Y-m-d');

        $query = "SELECT * FROM {$this->table} 
                  WHERE appointment_date BETWEEN :today AND :end_date
                  AND status IN (:scheduled, :confirmed)";

        if ($doctor_id) {
            $query .= " AND doctor_id = :doctor_id";
        }

        $query .= " ORDER BY appointment_date ASC, appointment_time ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':today', $today);
        $stmt->bindParam(':end_date', $end_date);
        $scheduled = self::STATUS_SCHEDULED;
        $confirmed = self::STATUS_CONFIRMED;
        $stmt->bindParam(':scheduled', $scheduled);
        $stmt->bindParam(':confirmed', $confirmed);
        
        if ($doctor_id) {
            $stmt->bindParam(':doctor_id', $doctor_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check for appointment conflicts
     */
    public function checkConflict($doctor_id, $date, $time, $duration_minutes, $exclude_id = null)
    {
        $end_time = date('H:i:s', strtotime($time) + ($duration_minutes * 60));

        $query = "SELECT * FROM {$this->table}
                  WHERE doctor_id = :doctor_id
                  AND appointment_date = :date
                  AND status NOT IN (:cancelled, :no_show)
                  AND (
                      (appointment_time <= :time AND ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)) > :time)
                      OR (appointment_time < :end_time AND appointment_time >= :time)
                  )";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':end_time', $end_time);
        $cancelled = self::STATUS_CANCELLED;
        $no_show = self::STATUS_NO_SHOW;
        $stmt->bindParam(':cancelled', $cancelled);
        $stmt->bindParam(':no_show', $no_show);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Validate status transition
     */
    private function isValidStatusTransition($id, $new_status)
    {
        $appointment = $this->findById($id);
        if (!$appointment) {
            return false;
        }

        $current_status = $appointment['status'];
        
        $valid_transitions = [
            self::STATUS_SCHEDULED => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
            self::STATUS_CONFIRMED => [self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED, self::STATUS_NO_SHOW],
            self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            self::STATUS_COMPLETED => [],
            self::STATUS_CANCELLED => [],
            self::STATUS_NO_SHOW => []
        ];

        return in_array($new_status, $valid_transitions[$current_status] ?? []);
    }

    /**
     * Generate UUID v4
     */
    private function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
