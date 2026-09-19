<?php
/**
 * Job Controller - FundiPro
 * Customer job posting and professional quote management
 */
class JobController {
    private $jobModel;
    private $quoteModel;
    private $matching;
    private $subscription;
    private $db;

    public function __construct($db) {
        $this->db           = $db;
        $this->jobModel     = new Job($db);
        $this->quoteModel   = new Quote($db);
        $this->matching     = new MatchingService($db);
        $this->subscription = new SubscriptionMiddleware($db);
    }

    // POST /api/jobs
    public function postJob($customerId, $data) {
        $required = ['service_id', 'title', 'description', 'county_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Field '$field' is required."];
            }
        }

        $jobId = $this->jobModel->create(array_merge($data, ['customer_id' => $customerId]));

        // Match and notify nearby professionals
        $matched = $this->matching->matchJobToProfessionals($jobId);

        return [
            'success'           => true,
            'job_id'            => $jobId,
            'professionals_notified' => count($matched),
            'message'           => 'Job posted! ' . count($matched) . ' fundis notified.',
        ];
    }

    // POST /api/jobs/{jobId}/quote
    public function submitQuote($professionalId, $jobId, $data) {
        // Check subscription limit
        $check = $this->subscription->handle($professionalId);
        if (!$check['allowed']) {
            return ['success' => false, 'error' => $check['message']];
        }

        $quoteId = $this->quoteModel->create(array_merge($data, [
            'job_id'          => $jobId,
            'professional_id' => $professionalId,
        ]));

        return ['success' => true, 'quote_id' => $quoteId, 'message' => 'Quote submitted successfully.'];
    }

    // POST /api/quotes/{quoteId}/accept
    public function acceptQuote($customerId, $quoteId) {
        $professionalId = $this->quoteModel->accept($quoteId, $customerId);
        $booking        = new Booking($this->db);
        $bookingId      = $booking->createFromQuote($quoteId);

        return [
            'success'    => true,
            'booking_id' => $bookingId,
            'message'    => 'Quote accepted! The fundi has been notified.',
        ];
    }

    // GET /api/jobs/{jobId}/quotes
    public function getQuotes($jobId, $customerId) {
        $job = $this->jobModel->getById($jobId);
        if (!$job || $job['customer_id'] != $customerId) {
            return ['success' => false, 'error' => 'Job not found.'];
        }
        $quotes = $this->quoteModel->getByJobId($jobId);
        return ['success' => true, 'quotes' => $quotes];
    }
}
