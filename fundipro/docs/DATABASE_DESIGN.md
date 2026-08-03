# FundiPro Database Design

## Core Tables

### users
- Authentication for both fundis and customers
- Role: 'professional' or 'customer'
- Phone (+254), email, password

### professionals
- Profile information
- First name, last name, phone
- Trades (plumbing, electrical, etc.)
- Years of experience
- Service area (counties/towns)
- Rates, availability
- Verification status
- Subscription tier

### services
- Service catalog
- Name, description, category
- Average price range

### jobs
- Customer job postings
- Title, description, photos
- Location (county, town, estate)
- Budget range
- Preferred dates
- Status: open, quoted, booked, completed, cancelled

### quotes
- Fundi responses to jobs
- Job ID, professional ID
- Quote amount (KES)
- Breakdown (labor, materials)
- Valid until date
- Status: pending, accepted, rejected

### bookings
- Accepted quotes become bookings
- Schedule, status tracking
- Payment records

### reviews
- Customer reviews of fundis
- Rating (1-5 stars)
- Comment
- Photos
- Verified purchase only

### portfolios
- Fundi work showcase
- Before/after photos
- Description, date completed
- Customer testimonial (optional)

### subscriptions
- Fundi subscription tracking
- Plan: free, pro, business
- Status: active, expired, cancelled
- M-Pesa transaction records
- Renewal date

### payments
- All payment transactions
- M-Pesa transaction IDs
- Amount, status
- Type: subscription, job payment

## Kenyan Specific Tables

### counties
- 47 Kenyan counties
- ID, name, code

### towns
- Major towns per county
- Estate-level for Nairobi, Mombasa
