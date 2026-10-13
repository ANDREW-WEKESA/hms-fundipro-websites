<?php

namespace HMS\Backend\Services;

use PDO;
use PDOException;
use Exception;

/**
 * SMS Service
 * 
 * Integrates with Africa's Talking SMS Gateway for sending OTPs,
 * appointment reminders, billing notifications, and general messages
 * Optimized for Kenyan phone numbers (+254 format)
 * 
 * @package HMS\Backend\Services
 * @version 1.0.0
 */
class SMSService
{
    private PDO $db;
    private string $apiKey;
    private string $username;
    private string $senderId;
    private string $apiUrl;

    /**
     * SMSService constructor
     * 
     * @param PDO $db Database connection
     * @param string $apiKey Africa's Talking API key
     * @param string $username Africa's Talking username
     * @param string $senderId Sender ID for SMS
     */
    public function __construct(PDO $db, string $apiKey, string $username, string $senderId = 'HMS')
    {
        $this->db = $db;
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->senderId = $senderId;
        $this->apiUrl = 'https://api.africastalking.com/version1/messaging';
    }

    /**
     * Send OTP to phone number
     * 
     * @param string $phone Phone number (+254 format)
     * @param string $otp OTP code
     * @return array Response with status and message
     */
    public function sendOTP(string $phone, string $otp): array
    {
        try {
            $normalizedPhone = $this->normalizeKenyanPhone($phone);
            $message = "Your HMS verification code is: {$otp}. Valid for 10 minutes. Do not share this code.";

            // Store OTP in database
            $this->storeOTP($normalizedPhone, $otp);

            // Send SMS
            $result = $this->sendMessage($normalizedPhone, $message);

            if ($result['success']) {
                $this->logSMS($normalizedPhone, $message, 'otp', 'sent');
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send OTP'
            ];

        } catch (Exception $e) {
            error_log("OTP send error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP'
            ];
        }
    }

    /**
     * Send appointment reminder
     * 
     * @param string $phone Patient phone number
     * @param string $patientName Patient name
     * @param string $doctorName Doctor name
     * @param string $dateTime Appointment date and time
     * @param string $location Location/department
     * @return array Response with status
     */
    public function sendAppointmentReminder(
        string $phone, 
        string $patientName, 
        string $doctorName, 
        string $dateTime, 
        string $location
    ): array {
        try {
            $normalizedPhone = $this->normalizeKenyanPhone($phone);
            
            $message = "Dear {$patientName}, reminder: You have an appointment with Dr. {$doctorName} on {$dateTime} at {$location}. Please arrive 15 minutes early. HMS FundiPro.";

            $result = $this->sendMessage($normalizedPhone, $message);

            if ($result['success']) {
                $this->logSMS($normalizedPhone, $message, 'appointment_reminder', 'sent');
                return [
                    'success' => true,
                    'message' => 'Reminder sent successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send reminder'
            ];

        } catch (Exception $e) {
            error_log("Appointment reminder error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send reminder'
            ];
        }
    }

    /**
     * Send billing notification
     * 
     * @param string $phone Patient phone number
     * @param float $amount Bill amount
     * @param string $billRef Bill reference number
     * @return array Response with status
     */
    public function sendBill(string $phone, float $amount, string $billRef): array
    {
        try {
            $normalizedPhone = $this->normalizeKenyanPhone($phone);
            
            $formattedAmount = number_format($amount, 2);
            $message = "HMS Bill: KES {$formattedAmount} (Ref: {$billRef}). Pay via M-PESA Paybill 522522, Account: {$billRef}. Thank you.";

            $result = $this->sendMessage($normalizedPhone, $message);

            if ($result['success']) {
                $this->logSMS($normalizedPhone, $message, 'billing', 'sent');
                return [
                    'success' => true,
                    'message' => 'Bill notification sent successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send bill notification'
            ];

        } catch (Exception $e) {
            error_log("Bill notification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send bill notification'
            ];
        }
    }

    /**
     * Send general SMS message
     * 
     * @param string $phone Phone number
     * @param string $message Message content
     * @return array Response with status
     */
    public function sendMessage(string $phone, string $message): array
    {
        try {
            $normalizedPhone = $this->normalizeKenyanPhone($phone);

            // Prepare API request
            $postData = [
                'username' => $this->username,
                'to' => $normalizedPhone,
                'message' => $message,
                'from' => $this->senderId
            ];

            // Initialize cURL
            $ch = curl_init($this->apiUrl);
            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'apiKey: ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            // Execute request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);

            if ($error) {
                error_log("SMS cURL error: " . $error);
                return [
                    'success' => false,
                    'message' => 'Network error',
                    'error' => $error
                ];
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 201 && isset($responseData['SMSMessageData']['Recipients'])) {
                $recipients = $responseData['SMSMessageData']['Recipients'];
                
                if (!empty($recipients) && isset($recipients[0]['status'])) {
                    $status = $recipients[0]['status'];
                    
                    if ($status === 'Success') {
                        return [
                            'success' => true,
                            'message' => 'SMS sent successfully',
                            'data' => $responseData
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'response' => $responseData
            ];

        } catch (Exception $e) {
            error_log("SMS send error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate random OTP
     * 
     * @param int $length OTP length (default 6)
     * @return string OTP code
     */
    public function generateOTP(int $length = 6): string
    {
        $otp = '';
        
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        
        return $otp;
    }

    /**
     * Validate OTP
     * 
     * @param string $phone Phone number
     * @param string $otp OTP to validate
     * @return bool True if valid
     */
    public function validateOTP(string $phone, string $otp): bool
    {
        try {
            $normalizedPhone = $this->normalizeKenyanPhone($phone);

            $stmt = $this->db->prepare("
                SELECT otp, expires_at, verified_at 
                FROM otp_verifications 
                WHERE phone = :phone 
                ORDER BY created_at DESC 
                LIMIT 1
            ");

            $stmt->bindParam(':phone', $normalizedPhone);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return false;
            }

            // Check if already verified
            if ($row['verified_at'] !== null) {
                return false;
            }

            // Check expiration
            if (strtotime($row['expires_at']) < time()) {
                return false;
            }

            // Verify OTP
            if ($row['otp'] !== $otp) {
                return false;
            }

            // Mark as verified
            $this->markOTPVerified($normalizedPhone, $otp);

            return true;

        } catch (PDOException $e) {
            error_log("OTP validation error: " . $e->getMessage());
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
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Convert 0712345678 to +254712345678
        if (str_starts_with($phone, '0')) {
            $phone = '+254' . substr($phone, 1);
        }
        // Convert 254712345678 to +254712345678
        elseif (str_starts_with($phone, '254')) {
            $phone = '+' . $phone;
        }
        // Convert 712345678 to +254712345678
        elseif (!str_starts_with($phone, '+254')) {
            $phone = '+254' . $phone;
        }

        return $phone;
    }

    /**
     * Store OTP in database
     * 
     * @param string $phone Phone number
     * @param string $otp OTP code
     * @return bool Success status
     */
    private function storeOTP(string $phone, string $otp): bool
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                INSERT INTO otp_verifications (phone, otp, expires_at, created_at)
                VALUES (:phone, :otp, :expires_at, :created_at)
            ");

            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otp);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':created_at', $now);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("OTP storage error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark OTP as verified
     * 
     * @param string $phone Phone number
     * @param string $otp OTP code
     * @return bool Success status
     */
    private function markOTPVerified(string $phone, string $otp): bool
    {
        try {
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                UPDATE otp_verifications 
                SET verified_at = :verified_at 
                WHERE phone = :phone 
                AND otp = :otp 
                AND verified_at IS NULL
            ");

            $stmt->bindParam(':verified_at', $now);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otp);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("OTP verification marking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log SMS for audit trail
     * 
     * @param string $phone Phone number
     * @param string $message Message content
     * @param string $type SMS type
     * @param string $status SMS status
     * @return bool Success status
     */
    private function logSMS(string $phone, string $message, string $type, string $status): bool
    {
        try {
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                INSERT INTO sms_logs (phone, message, type, status, created_at)
                VALUES (:phone, :message, :type, :status, :created_at)
            ");

            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':created_at', $now);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("SMS logging error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get SMS sending statistics
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Statistics
     */
    public function getStatistics(string $startDate, string $endDate): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    type,
                    status,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM sms_logs
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY type, status, DATE(created_at)
                ORDER BY created_at DESC
            ");

            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("SMS statistics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up expired OTPs
     * 
     * @return int Number of records deleted
     */
    public function cleanupExpiredOTPs(): int
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM otp_verifications 
                WHERE expires_at < NOW() 
                OR verified_at IS NOT NULL
            ");

            $stmt->execute();
            return $stmt->rowCount();

        } catch (PDOException $e) {
            error_log("OTP cleanup error: " . $e->getMessage());
            return 0;
        }
    }
}
