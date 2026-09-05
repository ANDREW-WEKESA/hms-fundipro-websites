<?php
/**
 * M-Pesa Service - FundiPro
 * Daraja API integration for Kenya payments
 * Handles STK push for subscriptions and job payments
 */
class MpesaService {
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;
    private $callbackUrl;
    private $baseUrl;

    public function __construct() {
        $this->consumerKey    = getenv('MPESA_CONSUMER_KEY');
        $this->consumerSecret = getenv('MPESA_CONSUMER_SECRET');
        $this->shortcode      = getenv('MPESA_SHORTCODE');
        $this->passkey        = getenv('MPESA_PASSKEY');
        $this->callbackUrl    = getenv('MPESA_CALLBACK_URL');
        $this->baseUrl        = getenv('APP_ENV') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    public function stkPush($phone, $amount, $reference, $description) {
        $phone     = $this->formatPhone($phone);
        $token     = $this->getAccessToken();
        $timestamp = date('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int)$amount,
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $this->callbackUrl,
            'AccountReference'  => $reference,
            'TransactionDesc'   => $description,
        ];

        $ch = curl_init($this->baseUrl . '/mpesa/stkpush/v1/processrequest');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $response;
    }

    public function handleCallback($data) {
        $result = $data['Body']['stkCallback'];
        if ($result['ResultCode'] !== 0) {
            return ['success' => false, 'message' => $result['ResultDesc']];
        }
        $items = [];
        foreach ($result['CallbackMetadata']['Item'] as $item) {
            $items[$item['Name']] = $item['Value'] ?? null;
        }
        return [
            'success'        => true,
            'transaction_id' => $items['MpesaReceiptNumber'],
            'amount'         => $items['Amount'],
            'phone'          => $items['PhoneNumber'],
        ];
    }

    private function getAccessToken() {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        $ch = curl_init($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . $credentials],
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $response['access_token'];
    }

    private function formatPhone($phone) {
        $phone = preg_replace('/\s+/', '', $phone);
        if (str_starts_with($phone, '0')) return '254' . substr($phone, 1);
        if (str_starts_with($phone, '+')) return substr($phone, 1);
        return $phone;
    }
}
