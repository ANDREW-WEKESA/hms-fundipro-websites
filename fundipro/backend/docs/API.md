# FundiPro API Documentation

## Base URL
`https://fundipro.co.ke/api`

## Authentication
Pass JWT in header: `Authorization: Bearer <token>`

## Endpoints

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /auth/register | Register new user (phone + password) |
| POST | /auth/login | Login (phone or email) |
| POST | /auth/verify-otp | Verify phone with OTP |

### Professionals
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /professionals | Search professionals (service, county, rating) |
| GET | /professionals/{id} | Get profile |
| PUT | /professionals/{id} | Update profile (auth) |
| POST | /professionals/verify | Submit verification docs (auth) |

### Jobs
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /jobs | Post new job (auth) |
| GET | /jobs/{id}/quotes | View quotes (auth, owner) |
| POST | /jobs/{id}/quote | Submit quote (auth, verified fundi) |
| POST | /quotes/{id}/accept | Accept a quote (auth, customer) |

### Payments (M-Pesa)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /payments/initiate | STK push to phone |
| POST | /payments/callback | M-Pesa webhook |
| GET | /payments/{id} | Check status |

### Subscriptions
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /subscriptions/plans | List plans with KES pricing |
| POST | /subscriptions/subscribe | Subscribe via M-Pesa |
| POST | /subscriptions/cancel | Cancel subscription |

## Subscription Plans
| Plan | Price | Jobs/Month |
|------|-------|------------|
| Free | KES 0 | 3 |
| Pro | KES 500 | Unlimited |
| Business | KES 1,200 | Unlimited + Team |

## Response Format
```json
{ "success": true, "data": {} }
{ "success": false, "error": "message" }
```

## Kenya-Specific
- Phone: +254XXXXXXXXX
- Currency: KES
- Timezone: Africa/Nairobi
- Emergency: 999, 112
