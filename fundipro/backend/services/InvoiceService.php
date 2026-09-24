<?php
/**
 * Invoice Service - FundiPro
 * Generate KES invoices for completed jobs
 * Shareable via WhatsApp and SMS
 */
class InvoiceService {
    private $db;
    const VAT_RATE = 0.16; // Kenya VAT 16%

    public function __construct($db) { $this->db = $db; }

    public function generate($bookingId, $professionalId) {
        $sql = "SELECT b.*, j.title, j.description,
                       u.first_name AS customer_first, u.last_name AS customer_last,
                       u.phone AS customer_phone,
                       pu.first_name AS pro_first, pu.last_name AS pro_last,
                       pu.phone AS pro_phone
                FROM bookings b
                JOIN jobs j ON b.job_id = j.id
                JOIN users u ON b.customer_id = u.id
                JOIN professionals p ON b.professional_id = p.id
                JOIN users pu ON p.user_id = pu.id
                WHERE b.id = ? AND b.professional_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookingId, $professionalId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking) throw new Exception('Booking not found.');

        $subtotal = $booking['amount'];
        $vat      = round($subtotal * self::VAT_RATE, 2);
        $total    = $subtotal + $vat;
        $invoiceNo = 'FP-INV-' . date('Ymd') . '-' . str_pad($bookingId, 4, '0', STR_PAD_LEFT);

        // Store invoice
        $this->db->prepare(
            "INSERT INTO invoices (booking_id, invoice_number, subtotal, vat, total, issued_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        )->execute([$bookingId, $invoiceNo, $subtotal, $vat, $total]);

        return [
            'invoice_number' => $invoiceNo,
            'customer'       => $booking['customer_first'] . ' ' . $booking['customer_last'],
            'professional'   => $booking['pro_first'] . ' ' . $booking['pro_last'],
            'job_title'      => $booking['title'],
            'subtotal'       => 'KES ' . number_format($subtotal, 2),
            'vat'            => 'KES ' . number_format($vat, 2),
            'total'          => 'KES ' . number_format($total, 2),
            'issued_at'      => date('d M Y'),
            'share_url'      => 'https://fundipro.co.ke/invoice/' . $invoiceNo,
        ];
    }
}
