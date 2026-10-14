<?php

namespace HMS\Backend\Controllers;

use HMS\Backend\Models\Appointment;
use HMS\Backend\Models\DoctorAvailability;
use HMS\Backend\Services\AppointmentValidationService;

/**
 * AppointmentController
 * Handles all appointment-related API endpoints
 */
class AppointmentController
{
    private $appointment;
    private $availability;
    private $validator;

    public function __construct($pdo)
    {
        $this->appointment = new Appointment($pdo);
        $this->availability = new DoctorAvailability($pdo);
        $this->validator = new AppointmentValidationService($pdo);
    }

    /**
     * Get all appointments with filters
     */
    public function index($request)
    {
        try {
            $filters = [
                'doctor_id' => $request['doctor_id'] ?? null,
                'patient_id' => $request['patient_id'] ?? null,
                'department_id' => $request['department_id'] ?? null,
                'status' => $request['status'] ?? null,
                'date_from' => $request['date_from'] ?? null,
                'date_to' => $request['date_to'] ?? null,
            ];

            $limit = $request['limit'] ?? 50;
            $offset = $request['offset'] ?? 0;

            $appointments = $this->getFilteredAppointments($filters, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'data' => $appointments,
                'count' => count($appointments)
            ], 200);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single appointment by ID
     */
    public function show($request, $id)
    {
        try {
            $appointment = $this->appointment->findById($id);

            if (!$appointment) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new appointment
     */
    public function create($request)
    {
        try {
            // Validate input
            $validation = $this->validator->validateBookingRequest($request);
            if (!$validation['valid']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
                ], 400);
            }

            // Normalize phone number to Kenya format
            $patient_phone = $this->normalizeKenyaPhone($request['patient_phone'] ?? '');

            // Check for conflicts
            $conflict = $this->appointment->checkConflict(
                $request['doctor_id'],
                $request['appointment_date'],
                $request['appointment_time'],
                $request['duration_minutes'] ?? 30
            );

            if ($conflict) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Time slot already booked. Please choose another time.'
                ], 409);
            }

            // Create appointment
            $this->appointment->patient_id = $request['patient_id'];
            $this->appointment->doctor_id = $request['doctor_id'];
            $this->appointment->department_id = $request['department_id'] ?? null;
            $this->appointment->appointment_date = $request['appointment_date'];
            $this->appointment->appointment_time = $request['appointment_time'];
            $this->appointment->duration_minutes = $request['duration_minutes'] ?? 30;
            $this->appointment->type = $request['type'] ?? Appointment::TYPE_CONSULTATION;
            $this->appointment->reason = $request['reason'] ?? '';
            $this->appointment->notes = $request['notes'] ?? '';
            $this->appointment->created_by = $request['user_id'] ?? null;

            if ($this->appointment->create()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment created successfully',
                    'data' => [
                        'id' => $this->appointment->id,
                        'uuid' => $this->appointment->uuid
                    ]
                ], 201);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create appointment'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update appointment
     */
    public function update($request, $id)
    {
        try {
            $appointment = $this->appointment->findById($id);

            if (!$appointment) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            // Only allow updates if not completed or cancelled
            if (in_array($appointment['status'], [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Cannot update completed or cancelled appointments'
                ], 400);
            }

            // Update fields
            $updated = $this->updateAppointmentFields($id, $request);

            if ($updated) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment updated successfully'
                ], 200);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update appointment'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel appointment
     */
    public function cancel($request, $id)
    {
        try {
            $appointment = $this->appointment->findById($id);

            if (!$appointment) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            $reason = $request['reason'] ?? 'No reason provided';

            if ($this->appointment->cancel($id, $reason)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment cancelled successfully'
                ], 200);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to cancel appointment'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to cancel appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available time slots
     */
    public function getAvailableSlots($request)
    {
        try {
            $doctor_id = $request['doctor_id'] ?? null;
            $date = $request['date'] ?? null;

            if (!$doctor_id || !$date) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'doctor_id and date are required'
                ], 400);
            }

            // Validate date format (Y-m-d)
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Invalid date format. Use Y-m-d (e.g., 2026-10-14)'
                ], 400);
            }

            $slots = $this->availability->getAvailableSlots($doctor_id, $date);

            return $this->jsonResponse([
                'success' => true,
                'data' => $slots,
                'count' => count($slots)
            ], 200);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve available slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm appointment
     */
    public function confirmAppointment($request, $id)
    {
        try {
            if ($this->appointment->updateStatus($id, Appointment::STATUS_CONFIRMED)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment confirmed successfully'
                ], 200);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to confirm appointment'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to confirm appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in patient for appointment
     */
    public function checkIn($request, $id)
    {
        try {
            if ($this->appointment->updateStatus($id, Appointment::STATUS_IN_PROGRESS)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Patient checked in successfully'
                ], 200);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check in patient'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check in patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete appointment
     */
    public function complete($request, $id)
    {
        try {
            if ($this->appointment->updateStatus($id, Appointment::STATUS_COMPLETED)) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment completed successfully'
                ], 200);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to complete appointment'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to complete appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reschedule appointment
     */
    public function reschedule($request, $id)
    {
        try {
            $appointment = $this->appointment->findById($id);

            if (!$appointment) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            $new_date = $request['appointment_date'] ?? null;
            $new_time = $request['appointment_time'] ?? null;

            if (!$new_date || !$new_time) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'appointment_date and appointment_time are required'
                ], 400);
            }

            // Check for conflicts
            $conflict = $this->appointment->checkConflict(
                $appointment['doctor_id'],
                $new_date,
                $new_time,
                $appointment['duration_minutes'],
                $id
            );

            if ($conflict) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'New time slot already booked'
                ], 409);
            }

            $updated = $this->updateAppointmentFields($id, [
                'appointment_date' => $new_date,
                'appointment_time' => $new_time
            ]);

            if ($updated) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment rescheduled successfully'
                ], 200);
            }

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reschedule appointment'
            ], 500);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to reschedule appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appointments by doctor
     */
    public function getByDoctor($request)
    {
        try {
            $doctor_id = $request['doctor_id'] ?? null;

            if (!$doctor_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'doctor_id is required'
                ], 400);
            }

            $appointments = $this->appointment->findByDoctor($doctor_id);

            return $this->jsonResponse([
                'success' => true,
                'data' => $appointments,
                'count' => count($appointments)
            ], 200);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve doctor appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get appointments by patient
     */
    public function getByPatient($request)
    {
        try {
            $patient_id = $request['patient_id'] ?? null;

            if (!$patient_id) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'patient_id is required'
                ], 400);
            }

            $appointments = $this->appointment->findByPatient($patient_id);

            return $this->jsonResponse([
                'success' => true,
                'data' => $appointments,
                'count' => count($appointments)
            ], 200);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve patient appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Normalize Kenya phone number to +254 format
     */
    private function normalizeKenyaPhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to +254 format
        if (substr($phone, 0, 3) === '254') {
            return '+' . $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '+254' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) === '7' || substr($phone, 0, 1) === '1') {
            return '+254' . $phone;
        }

        return '+254' . $phone;
    }

    /**
     * Helper: Update appointment fields
     */
    private function updateAppointmentFields($id, $data)
    {
        // Implementation would use PDO to update specific fields
        // This is a placeholder for the actual implementation
        return true;
    }

    /**
     * Helper: Get filtered appointments
     */
    private function getFilteredAppointments($filters, $limit, $offset)
    {
        // Implementation would build dynamic query based on filters
        // This is a placeholder for the actual implementation
        return [];
    }

    /**
     * Helper: Return JSON response
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }
}
