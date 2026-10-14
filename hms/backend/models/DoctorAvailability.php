<?php

namespace HMS\Backend\Models;

use PDO;
use PDOException;
use DateTime;
use DateInterval;

/**
 * DoctorAvailability Model
 * Manages doctor schedules and availability
 */
class DoctorAvailability
{
    private $pdo;
    private $table = 'doctor_schedules';

    // Properties
    public $id;
    public $doctor_id;
    public $day_of_week; // 0 = Sunday, 6 = Saturday
    public $start_time;
    public $end_time;
    public $slot_duration_minutes;
    public $max_patients;
    public $is_available;
    public $effective_from;
    public $effective_to;
    public $created_at;
    public $updated_at;

    // Kenya business hours
    const BUSINESS_START = '08:00:00';
    const BUSINESS_END = '17:00:00';
    const DEFAULT_SLOT_DURATION = 30; // minutes
    const DEFAULT_MAX_PATIENTS = 20;

    // Kenya public holidays 2026
    private $public_holidays = [
        '2026-01-01', // New Year
        '2026-04-10', // Good Friday
        '2026-04-13', // Easter Monday
        '2026-05-01', // Labour Day
        '2026-06-01', // Madaraka Day
        '2026-10-10', // Huduma Day
        '2026-10-20', // Mashujaa Day
        '2026-12-12', // Jamhuri Day
        '2026-12-25', // Christmas
        '2026-12-26', // Boxing Day
    ];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get availability by doctor ID
     */
    public function getByDoctor($doctor_id)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE doctor_id = :doctor_id 
                  AND is_available = 1
                  AND (effective_to IS NULL OR effective_to >= CURDATE())
                  ORDER BY day_of_week ASC, start_time ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableSlots($doctor_id, $date)
    {
        // Check if date is a weekend or public holiday
        if ($this->isWeekend($date) || $this->isPublicHoliday($date)) {
            return [];
        }

        $day_of_week = date('w', strtotime($date));

        // Get doctor's schedule for this day
        $query = "SELECT * FROM {$this->table} 
                  WHERE doctor_id = :doctor_id 
                  AND day_of_week = :day_of_week
                  AND is_available = 1
                  AND (effective_from IS NULL OR effective_from <= :date)
                  AND (effective_to IS NULL OR effective_to >= :date)
                  LIMIT 1";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':day_of_week', $day_of_week);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$schedule) {
            return [];
        }

        // Generate time slots
        $slots = $this->generateTimeSlots(
            $schedule['start_time'],
            $schedule['end_time'],
            $schedule['slot_duration_minutes'] ?? self::DEFAULT_SLOT_DURATION
        );

        // Get existing appointments for this date
        $query = "SELECT appointment_time, duration_minutes 
                  FROM appointments 
                  WHERE doctor_id = :doctor_id 
                  AND appointment_date = :date
                  AND status NOT IN ('cancelled', 'no_show')";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        $booked_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get blocked time slots
        $blocked_slots = $this->getBlockedTimeSlots($doctor_id, $date);

        // Filter out booked and blocked slots
        $available_slots = [];
        foreach ($slots as $slot) {
            if (!$this->isSlotBooked($slot, $booked_appointments) && 
                !$this->isSlotBlocked($slot, $blocked_slots)) {
                $available_slots[] = [
                    'time' => $slot,
                    'available' => true,
                    'date' => $date
                ];
            }
        }

        return $available_slots;
    }

    /**
     * Check if doctor is available at specific date and time
     */
    public function isAvailable($doctor_id, $dateTime, $duration = 30)
    {
        $date = date('Y-m-d', strtotime($dateTime));
        $time = date('H:i:s', strtotime($dateTime));
        $day_of_week = date('w', strtotime($date));

        // Check weekend and holidays
        if ($this->isWeekend($date) || $this->isPublicHoliday($date)) {
            return false;
        }

        // Check doctor's schedule
        $query = "SELECT * FROM {$this->table} 
                  WHERE doctor_id = :doctor_id 
                  AND day_of_week = :day_of_week
                  AND is_available = 1
                  AND start_time <= :time
                  AND end_time >= :time
                  AND (effective_from IS NULL OR effective_from <= :date)
                  AND (effective_to IS NULL OR effective_to >= :date)";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':day_of_week', $day_of_week);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$schedule) {
            return false;
        }

        // Check for conflicts with existing appointments
        $end_time = date('H:i:s', strtotime($time) + ($duration * 60));
        
        $query = "SELECT COUNT(*) as count FROM appointments
                  WHERE doctor_id = :doctor_id
                  AND appointment_date = :date
                  AND status NOT IN ('cancelled', 'no_show')
                  AND (
                      (appointment_time <= :time AND ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)) > :time)
                      OR (appointment_time < :end_time AND appointment_time >= :time)
                  )";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':end_time', $end_time);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] == 0;
    }

    /**
     * Add schedule for doctor
     */
    public function addSchedule($doctor_id, $day_of_week, $start_time, $end_time, $slot_duration = null, $max_patients = null)
    {
        $query = "INSERT INTO {$this->table} 
                  (doctor_id, day_of_week, start_time, end_time, slot_duration_minutes, max_patients, is_available)
                  VALUES 
                  (:doctor_id, :day_of_week, :start_time, :end_time, :slot_duration, :max_patients, 1)";

        try {
            $stmt = $this->pdo->prepare($query);
            $slot_duration = $slot_duration ?? self::DEFAULT_SLOT_DURATION;
            $max_patients = $max_patients ?? self::DEFAULT_MAX_PATIENTS;

            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':day_of_week', $day_of_week);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':slot_duration', $slot_duration);
            $stmt->bindParam(':max_patients', $max_patients);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Schedule creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update doctor's schedule
     */
    public function updateSchedule($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['start_time', 'end_time', 'slot_duration_minutes', 'max_patients', 'is_available', 'effective_to'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Block time slot for a doctor
     */
    public function blockTimeSlot($doctor_id, $start_datetime, $end_datetime, $reason = null)
    {
        $query = "INSERT INTO schedule_blocks 
                  (doctor_id, start_datetime, end_datetime, reason, created_at)
                  VALUES 
                  (:doctor_id, :start_datetime, :end_datetime, :reason, CURRENT_TIMESTAMP)";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':start_datetime', $start_datetime);
            $stmt->bindParam(':end_datetime', $end_datetime);
            $stmt->bindParam(':reason', $reason);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Block time slot error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get doctors by department with availability on specific date
     */
    public function getDoctorsByDepartment($department_id, $date)
    {
        $day_of_week = date('w', strtotime($date));

        $query = "SELECT DISTINCT u.id, u.first_name, u.last_name, u.specialization,
                         COUNT(a.id) as appointments_count
                  FROM users u
                  INNER JOIN doctor_departments dd ON u.id = dd.doctor_id
                  LEFT JOIN {$this->table} ds ON u.id = ds.doctor_id 
                      AND ds.day_of_week = :day_of_week 
                      AND ds.is_available = 1
                  LEFT JOIN appointments a ON u.id = a.doctor_id 
                      AND a.appointment_date = :date
                      AND a.status NOT IN ('cancelled', 'no_show')
                  WHERE dd.department_id = :department_id
                  AND u.role = 'doctor'
                  AND u.status = 'active'
                  AND ds.id IS NOT NULL
                  GROUP BY u.id
                  HAVING appointments_count < ds.max_patients
                  ORDER BY appointments_count ASC";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':department_id', $department_id);
        $stmt->bindParam(':day_of_week', $day_of_week);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate time slots between start and end time
     */
    private function generateTimeSlots($start_time, $end_time, $duration_minutes)
    {
        $slots = [];
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        $interval = new DateInterval("PT{$duration_minutes}M");

        while ($start < $end) {
            $slots[] = $start->format('H:i:s');
            $start->add($interval);
        }

        return $slots;
    }

    /**
     * Check if slot is already booked
     */
    private function isSlotBooked($slot, $booked_appointments)
    {
        foreach ($booked_appointments as $appointment) {
            $appt_start = strtotime($appointment['appointment_time']);
            $appt_end = $appt_start + ($appointment['duration_minutes'] * 60);
            $slot_time = strtotime($slot);

            if ($slot_time >= $appt_start && $slot_time < $appt_end) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get blocked time slots for a date
     */
    private function getBlockedTimeSlots($doctor_id, $date)
    {
        $query = "SELECT start_datetime, end_datetime 
                  FROM schedule_blocks 
                  WHERE doctor_id = :doctor_id 
                  AND DATE(start_datetime) <= :date
                  AND DATE(end_datetime) >= :date";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if slot is blocked
     */
    private function isSlotBlocked($slot, $blocked_slots)
    {
        foreach ($blocked_slots as $block) {
            $block_start = strtotime($block['start_datetime']);
            $block_end = strtotime($block['end_datetime']);
            $slot_time = strtotime($slot);

            if ($slot_time >= $block_start && $slot_time < $block_end) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if date is weekend
     */
    private function isWeekend($date)
    {
        $day = date('w', strtotime($date));
        return ($day == 0 || $day == 6); // Sunday or Saturday
    }

    /**
     * Check if date is public holiday
     */
    private function isPublicHoliday($date)
    {
        return in_array($date, $this->public_holidays);
    }
}
