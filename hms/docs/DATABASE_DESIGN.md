# HMS Database Design

## Entity Relationship Diagram

### Core Tables

**facilities**
- Hospital/clinic information
- Address, phone, email
- License number

**users**
- Staff accounts (doctors, nurses, admins)
- Role-based access
- Phone/email authentication

**patients**
- Patient demographics
- Kenya ID, phone (+254 format)
- Insurance details (SHA, NHIF, AAR, etc.)
- Next of kin

**appointments**
- Patient-doctor bookings
- Date, time, status
- SMS reminders

**visits**
- Encounter tracking
- Vitals, consultation notes
- Diagnosis (ICD-10)
- Prescriptions

**lab_tests**
- Test orders
- Results
- Critical value alerts

**pharmacy**
- Drug inventory
- Dispensing records
- Expiry tracking

**invoices**
- Billing records
- M-Pesa payments
- Insurance claims

### Kenyan Specifics

**insurance_providers**
- SHA (Social Health Authority)
- NHIF (legacy)
- Private insurers (AAR, Jubilee, Britam, CIC, Madison, Resolution)

**Payment Methods**
- M-Pesa (primary)
- Cash
- Bank transfer
- Insurance
