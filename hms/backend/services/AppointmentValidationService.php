<?php

namespace HMS\Backend\Services;

use PDO;
use DateTime;
use Exception;

/**
 * AppointmentValidationService
 * Handles appointment booking validation and conflict detection
 */
class AppointmentValidationService
{
    private $pdo;
    
    // Business rules
    const MAX_APPOINTMENTS_PER_DOCTOR_PER_DAY = 20;
    const MIN_ADVANCE_BOOKING_MINUTES = 15;
    const WORKING_HOURS_START = '08:00';
    const WORKING_HOURS_END = '17:00';
    const MIN_APPOINTMENT_DURATION = 15;
    const MAX_APPOINTMENT_DURATION = 180;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Validate complete booking request
     */
    public function validateBookingRequest($data)
    {
        $errors = [];

        // Required fields validation
        if (empty($data['patient_id'])) {
            $errors[] = 'Patient ID is required';
        }

        if (empty($data['doctor_id'])) {
            $errors[] = 'Doctor ID is required';
        }

        if (empty($data['appointment_date'])) {
            $errors[] = 'Appointment date is required';
        }

        if (empty($data['appointment_time'])) {
            $errors[] = 'Appointment time is required';
        }

        // If basic validation fails, return early
        if (!empty($errors)) {
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }

        // Date format validation
        if (!$this->isValidDateFormat($data['appointment_date'])) {
            $errors[] = 'Invalid date format. Use Y-m-d (e.g., 2026-10-14)';
        }

        // Time format validation
        if (!$this->isValidTimeFormat($data['appointment_time'])) {
            $errors[] = 'Invalid time format. Use H:i (e.g., 14:30)';
        }

        // Future date validation
        if (!$this->validateFutureDate($data['appointment_date'], $data['appointment_time'])) {
            $errors[] = 'Appointment must be at least ' . self::MIN_ADVANCE_BOOKING_MINUTES . ' minutes in the future';
        }

        // Working hours validation
        if (!$this->validateWorkingHours($data['appointment_time'])) {
            $errors[] = 'Appointment time must be between ' . self::WORKING_HOURS_START . ' and ' . self::WORKING_HOURS_END;
        }

        // Weekend validation for elective appointments
        $type = $data['type'] ?? 'consultation';
        if ($this->isWeekend($data['appointment_date']) && $type !== 'emergency') {
            $errors[] = 'Elective appointments cannot be booked on weekends';
        }

        // Duration validation
        $duration = $data['duration_minutes'] ?? 30;
        if ($duration < self::MIN_APPOINTMENT_DURATION || $duration > self::MAX_APPOINTMENT_DURATION) {
            $errors[] = sprintf(
                'Appointment duration must be between %d and %d minutes',
                self::MIN_APPOINTMENT_DURATION,
                self::MAX_APPOINTMENT_DURATION
            );
        }

        // Doctor availability validation
        if (!empty($data['doctor_id']) && !empty($data['appointment_date']) && !empty($data['appointment_time'])) {
            $availabilityCheck = $this->checkDoctorAvailability(
                $data['doctor_id'],
                $data['appointment_date'],
                $data['appointment_time']
            );

            if (!$availabilityCheck['available']) {
                $errors[] = $availabilityCheck['message'];
            }

            // Doctor conflict check
            $conflictCheck = $this->checkDoctorConflict(
                $data['doctor_id'],
                $data['appointment_date'],
                $data['appointment_time'],
                date('H:i:s', strtotime($data['appointment_time']) + ($duration * 60))
            );

            if ($conflictCheck['has_conflict']) {
                $errors[] = $conflictCheck['message'];
            }

            // Patient conflict check
            if (!empty($data['patient_id'])) {
                $patientConflict = $this->checkPatientConflict(
                    $data['patient_id'],
                    $data['appointment_date'],
                    $data['appointment_time']
                );

                if ($patientConflict['has_conflict']) {
                    $errors[] = $patientConflict['message'];
                }
            }

            // Daily appointment limit check
            $dailyLimitCheck = $this->checkDailyAppointmentLimit(
                $data['doctor_id'],
                $data['appointment_date']
            );

            if (!$dailyLimitCheck['within_limit']) {
                $errors[] = $dailyLimitCheck['message'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check for doctor scheduling conflicts
     */
    public function checkDoctorConflict($doctor_id, $date, $start_time, $end_time)
    {
        try {
            $query = "SELECT a.*, 
                             p.first_name as patient_first_name,
                             p.last_name as patient_last_name
                      FROM appointments a
                      LEFT JOIN patients p ON a.patient_id = p.id
                      WHERE a.doctor_id = :doctor_id
                      AND a.appointment_date = :date
                      AND a.status NOT IN ('cancelled', 'no_show')
                      AND (
                          (a.appointment_time < :end_time AND 
                           ADDTIME(a.appointment_time, SEC_TO_TIME(a.duration_minutes * 60)) > :start_time)
                      )";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->execute();

            $conflict = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conflict) {
                return [
                    'has_conflict' => true,
                    'message' => sprintf(
                        'Doctor has conflicting appointment at %s with %s %s',
                        date('H:i', strtotime($conflict['appointment_time'])),
                        $conflict['patient_first_name'],
                        $conflict['patient_last_name']
                    ),
                    'conflicting_appointment' => $conflict
                ];
            }

            return ['has_conflict' => false];

        } catch (Exception $e) {
            error_log("Error checking doctor conflict: " . $e->getMessage());
            return [
                'has_conflict' => true,
                'message' => 'Unable to verify doctor availability'
            ];
        }
    }

    /**
     * Check for patient double-booking
     */
    public function checkPatientConflict($patient_id, $date, $start_time)
    {
        try {
            $query = "SELECT a.*,
                             CONCAT(u.first_name, ' ', u.last_name) as doctor_name
                      FROM appointments a
                      LEFT JOIN users u ON a.doctor_id = u.id
                      WHERE a.patient_id = :patient_id
                      AND a.appointment_date = :date
                      AND a.status NOT IN ('cancelled', 'no_show')
                      AND a.appointment_time = :start_time";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':patient_id', $patient_id);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':start_time', $start_time);
            $stmt->execute();

            $conflict = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conflict) {
                return [
                    'has_conflict' => true,
                    'message' => sprintf(
                        'Patient already has appointment at %s with %s',
                        date('H:i', strtotime($conflict['appointment_time'])),
                        $conflict['doctor_name']
                    ),
                    'conflicting_appointment' => $conflict
                ];
            }

            return ['has_conflict' => false];

        } catch (Exception $e) {
            error_log("Error checking patient conflict: " . $e->getMessage());
            return [
                'has_conflict' => true,
                'message' => 'Unable to verify patient availability'
            ];
        }
    }

    /**
     * Check doctor availability based on schedule
     */
    public function checkDoctorAvailability($doctor_id, $date, $time)
    {
        try {
            $day_of_week = date('w', strtotime($date));

            $query = "SELECT * FROM doctor_schedules
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
                return [
                    'available' => false,
                    'message' => 'Doctor is not available at this time'
                ];
            }

            // Check for blocked time slots
            $query = "SELECT * FROM schedule_blocks
                      WHERE doctor_id = :doctor_id
                      AND :datetime BETWEEN start_datetime AND end_datetime";

            $datetime = $date . ' ' . $time;
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':datetime', $datetime);
            $stmt->execute();

            $block = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($block) {
                return [
                    'available' => false,
                    'message' => 'Doctor is not available at this time: ' . ($block['reason'] ?? 'Time slot blocked')
                ];
            }

            return ['available' => true];

        } catch (Exception $e) {
            error_log("Error checking doctor availability: " . $e->getMessage());
            return [
                'available' => false,
                'message' => 'Unable to verify doctor availability'
            ];
        }
    }

    /**
     * Validate future date and time
     */
    public function validateFutureDate($date, $time = null)
    {
        try {
            $now = new DateTime();
            $appointment_datetime = new DateTime($date . ' ' . ($time ?? '00:00:00'));

            $diff_minutes = ($appointment_datetime->getTimestamp() - $now->getTimestamp()) / 60;

            return $diff_minutes >= self::MIN_ADVANCE_BOOKING_MINUTES;

        } catch (Exception $e) {
            error_log("Error validating future date: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate working hours
     */
    public function validateWorkingHours($time)
    {
        try {
            $check_time = new DateTime($time);
            $start_time = new DateTime(self::WORKING_HOURS_START);
            $end_time = new DateTime(self::WORKING_HOURS_END);

            return $check_time >= $start_time && $check_time <= $end_time;

        } catch (Exception $e) {
            error_log("Error validating working hours: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get conflicting appointments for a doctor on a date
     */
    public function getConflictingAppointments($doctor_id, $date)
    {
        try {
            $query = "SELECT a.*,
                             p.first_name as patient_first_name,
                             p.last_name as patient_last_name,
                             p.phone as patient_phone
                      FROM appointments a
                      LEFT JOIN patients p ON a.patient_id = p.id
                      WHERE a.doctor_id = :doctor_id
                      AND a.appointment_date = :date
                      AND a.status NOT IN ('cancelled', 'no_show')
                      ORDER BY a.appointment_time ASC";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':date', $date);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error getting conflicting appointments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check daily appointment limit for doctor
     */
    private function checkDailyAppointmentLimit($doctor_id, $date)
    {
        try {
            $query = "SELECT COUNT(*) as count
                      FROM appointments
                      WHERE doctor_id = :doctor_id
                      AND appointment_date = :date
                      AND status NOT IN ('cancelled', 'no_show')";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->bindParam(':date', $date);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'] ?? 0;

            if ($count >= self::MAX_APPOINTMENTS_PER_DOCTOR_PER_DAY) {
                return [
                    'within_limit' => false,
                    'message' => sprintf(
                        'Doctor has reached maximum appointments for this day (%d/%d)',
                        $count,
                        self::MAX_APPOINTMENTS_PER_DOCTOR_PER_DAY
                    ),
                    'current_count' => $count
                ];
            }

            return [
                'within_limit' => true,
                'current_count' => $count
            ];

        } catch (Exception $e) {
            error_log("Error checking daily limit: " . $e->getMessage());
            return [
                'within_limit' => false,
                'message' => 'Unable to verify appointment limit'
            ];
        }
    }

    /**
     * Check if date is weekend
     */
    private function isWeekend($date)
    {
        $day = date('w', strtotime($date));
        return ($day == 0 || $day == 6);
    }

    /**
     * Validate date format (Y-m-d)
     */
    private function isValidDateFormat($date)
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) !== false;
    }

    /**
     * Validate time format (H:i or H:i:s)
     */
    private function isValidTimeFormat($time)
    {
        return preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time) && strtotime($time) !== false;
    }
}
