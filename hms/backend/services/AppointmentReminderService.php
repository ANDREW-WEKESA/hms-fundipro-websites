<?php

namespace HMS\Backend\Services;

use PDO;
use DateTime;
use Exception;

/**
 * AppointmentReminderService
 * Handles automated SMS reminders for appointments
 */
class AppointmentReminderService
{
    private $pdo;
    private $smsService;
    private $reminderTable = 'appointment_reminders';

    // Reminder types
    const REMINDER_24H = '24_hours';
    const REMINDER_1H = '1_hour';
    const REMINDER_CONFIRMATION = 'confirmation';
    const REMINDER_CANCELLATION = 'cancellation';
    const REMINDER_RESCHEDULE = 'reschedule';

    public function __construct($pdo, $smsService = null)
    {
        $this->pdo = $pdo;
        $this->smsService = $smsService ?? new SMSService();
    }

    /**
     * Send reminders for appointments 24 hours before
     */
    public function sendDayBeforeReminders()
    {
        try {
            $target_time = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $start_range = date('Y-m-d H:i:s', strtotime('+23 hours 45 minutes'));
            $end_range = date('Y-m-d H:i:s', strtotime('+24 hours 15 minutes'));

            $query = "SELECT a.*, 
                             p.first_name as patient_first_name,
                             p.last_name as patient_last_name,
                             p.phone as patient_phone,
                             u.first_name as doctor_first_name,
                             u.last_name as doctor_last_name,
                             d.name as department_name
                      FROM appointments a
                      LEFT JOIN patients p ON a.patient_id = p.id
                      LEFT JOIN users u ON a.doctor_id = u.id
                      LEFT JOIN departments d ON a.department_id = d.id
                      WHERE CONCAT(a.appointment_date, ' ', a.appointment_time) BETWEEN :start_range AND :end_range
                      AND a.status IN ('scheduled', 'confirmed')
                      AND NOT EXISTS (
                          SELECT 1 FROM {$this->reminderTable} r 
                          WHERE r.appointment_id = a.id 
                          AND r.reminder_type = :reminder_type
                          AND r.status = 'sent'
                      )";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':start_range', $start_range);
            $stmt->bindParam(':end_range', $end_range);
            $reminder_type = self::REMINDER_24H;
            $stmt->bindParam(':reminder_type', $reminder_type);
            $stmt->execute();

            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sent_count = 0;
            foreach ($appointments as $appointment) {
                if ($this->send24HourReminder($appointment)) {
                    $sent_count++;
                }
            }

            error_log("24-hour reminders sent: {$sent_count} out of " . count($appointments));
            return $sent_count;

        } catch (Exception $e) {
            error_log("Error sending 24-hour reminders: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send reminders for appointments 1 hour before
     */
    public function sendHourBeforeReminders()
    {
        try {
            $start_range = date('Y-m-d H:i:s', strtotime('+55 minutes'));
            $end_range = date('Y-m-d H:i:s', strtotime('+65 minutes'));

            $query = "SELECT a.*, 
                             p.first_name as patient_first_name,
                             p.last_name as patient_last_name,
                             p.phone as patient_phone,
                             u.first_name as doctor_first_name,
                             u.last_name as doctor_last_name,
                             d.name as department_name
                      FROM appointments a
                      LEFT JOIN patients p ON a.patient_id = p.id
                      LEFT JOIN users u ON a.doctor_id = u.id
                      LEFT JOIN departments d ON a.department_id = d.id
                      WHERE CONCAT(a.appointment_date, ' ', a.appointment_time) BETWEEN :start_range AND :end_range
                      AND a.status IN ('scheduled', 'confirmed')
                      AND NOT EXISTS (
                          SELECT 1 FROM {$this->reminderTable} r 
                          WHERE r.appointment_id = a.id 
                          AND r.reminder_type = :reminder_type
                          AND r.status = 'sent'
                      )";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':start_range', $start_range);
            $stmt->bindParam(':end_range', $end_range);
            $reminder_type = self::REMINDER_1H;
            $stmt->bindParam(':reminder_type', $reminder_type);
            $stmt->execute();

            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sent_count = 0;
            foreach ($appointments as $appointment) {
                if ($this->send1HourReminder($appointment)) {
                    $sent_count++;
                }
            }

            error_log("1-hour reminders sent: {$sent_count} out of " . count($appointments));
            return $sent_count;

        } catch (Exception $e) {
            error_log("Error sending 1-hour reminders: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send confirmation SMS
     */
    public function sendConfirmationSMS($appointment)
    {
        try {
            $phone = $this->normalizeKenyaPhone($appointment['patient_phone']);
            
            // English message
            $message_en = sprintf(
                "Appointment confirmed at %s on %s at %s with Dr. %s. Ref: %s. Reply CANCEL to cancel.",
                $appointment['department_name'] ?? 'HMS',
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name'],
                substr($appointment['uuid'], 0, 8)
            );

            // Swahili message
            $message_sw = sprintf(
                "Miadi imethibitishwa %s tarehe %s saa %s na Dk. %s. Nambari: %s. Jibu CANCEL kufuta.",
                $appointment['department_name'] ?? 'HMS',
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name'],
                substr($appointment['uuid'], 0, 8)
            );

            // Send English version (default)
            $result = $this->smsService->send($phone, $message_en);

            // Log the reminder
            $this->logReminder(
                $appointment['id'],
                self::REMINDER_CONFIRMATION,
                $message_en,
                $result ? 'sent' : 'failed'
            );

            return $result;

        } catch (Exception $e) {
            error_log("Error sending confirmation SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send cancellation SMS
     */
    public function sendCancellationSMS($appointment)
    {
        try {
            $phone = $this->normalizeKenyaPhone($appointment['patient_phone']);
            
            $message_en = sprintf(
                "Your appointment on %s at %s with Dr. %s has been CANCELLED. Please contact us to reschedule. HMS",
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name']
            );

            $message_sw = sprintf(
                "Miadi yako tarehe %s saa %s na Dk. %s imeFUTWA. Tafadhali wasiliana nasi kupanga upya. HMS",
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name']
            );

            $result = $this->smsService->send($phone, $message_en);

            $this->logReminder(
                $appointment['id'],
                self::REMINDER_CANCELLATION,
                $message_en,
                $result ? 'sent' : 'failed'
            );

            return $result;

        } catch (Exception $e) {
            error_log("Error sending cancellation SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send reschedule notification
     */
    public function sendRescheduleNotification($appointment)
    {
        try {
            $phone = $this->normalizeKenyaPhone($appointment['patient_phone']);
            
            $message_en = sprintf(
                "Your appointment has been RESCHEDULED to %s at %s with Dr. %s. Ref: %s. HMS",
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name'],
                substr($appointment['uuid'], 0, 8)
            );

            $message_sw = sprintf(
                "Miadi yako imebadilishwa kuwa tarehe %s saa %s na Dk. %s. Nambari: %s. HMS",
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name'],
                substr($appointment['uuid'], 0, 8)
            );

            $result = $this->smsService->send($phone, $message_en);

            $this->logReminder(
                $appointment['id'],
                self::REMINDER_RESCHEDULE,
                $message_en,
                $result ? 'sent' : 'failed'
            );

            return $result;

        } catch (Exception $e) {
            error_log("Error sending reschedule notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process all pending reminders (main cron job entry point)
     */
    public function processReminders()
    {
        $results = [
            '24h_sent' => 0,
            '1h_sent' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        try {
            $results['24h_sent'] = $this->sendDayBeforeReminders();
            $results['1h_sent'] = $this->sendHourBeforeReminders();

            error_log("Reminder processing completed: " . json_encode($results));
            return $results;

        } catch (Exception $e) {
            error_log("Error processing reminders: " . $e->getMessage());
            return $results;
        }
    }

    /**
     * Send 24-hour reminder
     */
    private function send24HourReminder($appointment)
    {
        try {
            $phone = $this->normalizeKenyaPhone($appointment['patient_phone']);
            
            $message_en = sprintf(
                "Reminder: You have an appointment TOMORROW %s at %s with Dr. %s at %s. Ref: %s. HMS",
                date('d/m/Y', strtotime($appointment['appointment_date'])),
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name'],
                $appointment['department_name'] ?? 'Hospital',
                substr($appointment['uuid'], 0, 8)
            );

            $result = $this->smsService->send($phone, $message_en);

            $this->logReminder(
                $appointment['id'],
                self::REMINDER_24H,
                $message_en,
                $result ? 'sent' : 'failed'
            );

            return $result;

        } catch (Exception $e) {
            error_log("Error sending 24h reminder for appointment {$appointment['id']}: " . $e->getMessage());
            $this->logReminder($appointment['id'], self::REMINDER_24H, '', 'failed');
            return false;
        }
    }

    /**
     * Send 1-hour reminder
     */
    private function send1HourReminder($appointment)
    {
        try {
            $phone = $this->normalizeKenyaPhone($appointment['patient_phone']);
            
            $message_en = sprintf(
                "Reminder: Your appointment is in 1 HOUR at %s with Dr. %s. Location: %s. Please arrive 15 min early. HMS",
                date('H:i', strtotime($appointment['appointment_time'])),
                $appointment['doctor_last_name'],
                $appointment['department_name'] ?? 'Reception'
            );

            $result = $this->smsService->send($phone, $message_en);

            $this->logReminder(
                $appointment['id'],
                self::REMINDER_1H,
                $message_en,
                $result ? 'sent' : 'failed'
            );

            return $result;

        } catch (Exception $e) {
            error_log("Error sending 1h reminder for appointment {$appointment['id']}: " . $e->getMessage());
            $this->logReminder($appointment['id'], self::REMINDER_1H, '', 'failed');
            return false;
        }
    }

    /**
     * Log reminder send attempt
     */
    private function logReminder($appointment_id, $reminder_type, $message, $status)
    {
        try {
            $query = "INSERT INTO {$this->reminderTable} 
                      (appointment_id, reminder_type, message, status, sent_at, created_at)
                      VALUES 
                      (:appointment_id, :reminder_type, :message, :status, 
                       CASE WHEN :status = 'sent' THEN CURRENT_TIMESTAMP ELSE NULL END,
                       CURRENT_TIMESTAMP)";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':appointment_id', $appointment_id);
            $stmt->bindParam(':reminder_type', $reminder_type);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':status', $status);
            $stmt->execute();

            return true;

        } catch (Exception $e) {
            error_log("Error logging reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalize Kenya phone number to +254 format
     */
    private function normalizeKenyaPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 3) === '254') {
            return '+' . $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '+254' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) === '7' || substr($phone, 0, 1) === '1') {
            return '+254' . $phone;
        }

        return '+254' . $phone;
    }
}

/**
 * Mock SMSService class (to be replaced with actual SMS gateway integration)
 */
class SMSService
{
    public function send($phone, $message)
    {
        // TODO: Integrate with actual SMS gateway (Africa's Talking, Twilio, etc.)
        error_log("SMS to {$phone}: {$message}");
        return true;
    }
}
