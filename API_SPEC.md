# API Specification (Future Backend)

## Overview

This document outlines the planned REST API for both HMS and FundiPro platforms.

## Authentication

All authenticated endpoints require a JWT token in the Authorization header:
```
Authorization: Bearer <token>
```

## Common Response Format

### Success Response
```json
{
  "success": true,
  "data": {},
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message"
  }
}
```

## HMS API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - Patient registration
- `POST /api/auth/otp` - Send OTP
- `POST /api/auth/verify-otp` - Verify OTP

### Appointments
- `GET /api/appointments` - List appointments
- `POST /api/appointments` - Book appointment
- `PUT /api/appointments/:id` - Update appointment
- `DELETE /api/appointments/:id` - Cancel appointment

### Patients
- `GET /api/patients/:id` - Get patient details
- `PUT /api/patients/:id` - Update patient info
- `GET /api/patients/:id/visits` - Visit history

### Doctors
- `GET /api/doctors` - List all doctors
- `GET /api/doctors/:id` - Get doctor details
- `GET /api/doctors/:id/availability` - Check availability

### Departments
- `GET /api/departments` - List departments
- `GET /api/departments/:id/hours` - Department hours

### Contact
- `POST /api/contact` - Submit contact form

### Payments (M-Pesa)
- `POST /api/payments/initiate` - Initiate M-Pesa payment
- `POST /api/payments/callback` - M-Pesa callback
- `GET /api/payments/:id/status` - Check payment status

## FundiPro API Endpoints

### Authentication
- `POST /api/auth/login` - User/Fundi login
- `POST /api/auth/register` - Registration
- `POST /api/auth/otp` - Send OTP
- `POST /api/auth/verify-otp` - Verify OTP

### Professionals
- `GET /api/professionals` - Search professionals
- `GET /api/professionals/:id` - Get profile
- `PUT /api/professionals/:id` - Update profile
- `POST /api/professionals/:id/verify` - Submit verification

### Jobs
- `GET /api/jobs` - List jobs
- `POST /api/jobs` - Post new job
- `PUT /api/jobs/:id` - Update job
- `GET /api/jobs/:id/quotes` - Get quotes

### Quotes
- `POST /api/quotes` - Send quote
- `PUT /api/quotes/:id` - Update quote
- `POST /api/quotes/:id/accept` - Accept quote

### Reviews
- `GET /api/reviews/:professionalId` - Get reviews
- `POST /api/reviews` - Submit review
- `PUT /api/reviews/:id` - Update review

### Payments (M-Pesa)
- `POST /api/payments/initiate` - Initiate payment
- `POST /api/payments/callback` - M-Pesa callback
- `GET /api/payments/:id` - Payment status

### Subscriptions
- `GET /api/subscriptions/plans` - List plans
- `POST /api/subscriptions` - Subscribe
- `PUT /api/subscriptions/:id/cancel` - Cancel subscription

## M-Pesa Integration

### STK Push Request
```json
{
  "phone": "+254700123456",
  "amount": 500,
  "reference": "SUB-12345",
  "description": "FundiPro Pro Subscription"
}
```

### Callback Response
```json
{
  "ResultCode": 0,
  "ResultDesc": "Success",
  "TransactionID": "OGK1234567",
  "Amount": 500
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## Rate Limiting

- 100 requests per minute per IP
- 1000 requests per hour per user
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

## Webhooks

### M-Pesa Callback
```
POST /webhooks/mpesa
```

### SMS Status
```
POST /webhooks/sms
```

## SMS Integration (Africa's Talking)

### Send SMS
```json
{
  "to": "+254700123456",
  "message": "Your appointment is confirmed"
}
```

## Future Enhancements

- GraphQL API
- WebSocket for real-time updates
- File upload endpoints
- Batch operations
- API versioning (v2)
