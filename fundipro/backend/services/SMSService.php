<?php
/**
 * SMS Service - FundiPro
 * Africa's Talking API integration for Kenya
 */
class SMSService {
    private $apiKey;
    private $username;
    private $senderId;

    public function __construct() {
        $this->apiKey   = getenv('AT_API_KEY');
        $this->username = getenv('AT_USERNAME') ?: 'sandbox';
        $this->senderId = getenv('AT_SENDER_ID') ?: 'FundiPro';
    }

    public function sendOTP($phone, $otp) {
        $message = "Your FundiPro verification code is: $otp. Valid for 5 minutes. Do not share this code.";
        return $this->send($phone, $message);
    }

    public function sendJobAlert($phone, $jobTitle, $location) {
        $message = "New job alert! '$jobTitle' in $location. Open FundiPro to submit a quote. Reply STOP to opt out.";
        return $this->send($phone, $message);
    }

    public function sendQuoteAccepted($phone, $customerName, $jobTitle) {
        $message = "Great news! $customerName accepted your quote for '$jobTitle'. Log in to FundiPro to confirm. Piga simu: fundipro.co.ke";
        return $this->send($phone, $message);
    }

    public function sendPaymentReceived($phone, $amount) {
        $kes = 'KES ' . number_format($amount, 2);
        $message = "Payment of $kes received via M-Pesa. Thank you for using FundiPro!";
        return $this->send($phone, $message);
    }

    public function sendSubscriptionReminder($phone, $plan, $daysLeft) {
        $message = "Your FundiPro $plan subscription expires in $daysLeft days. Renew via M-Pesa to keep getting job leads. fundipro.co.ke";
        return $this->send($phone, $message);
    }

    private function send($phone, $message) {
        $url = 'https://api.africastalking.com/version1/messaging';
        $data = [
            'username' => $this->username,
            'to'       => $phone,
            'message'  => $message,
            'from'     => $this->senderId,
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apiKey: ' . $this->apiKey,
                'Accept: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
