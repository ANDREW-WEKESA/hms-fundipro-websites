# HMS Requirements - Kenya Healthcare

## Functional Requirements

### Patient Management
- Registration with Kenya ID validation
- Phone number in +254 format
- Insurance details (SHA, NHIF, private)
- Next of kin information
- Medical history tracking

### Appointment System
- Online booking
- SMS reminders via Africa's Talking
- Doctor availability management
- Walk-in support

### Clinical Features
- Vitals recording
- Diagnosis (ICD-10 codes)
- Prescriptions
- Lab orders
- Medical records

### Billing & Payments
- M-Pesa integration (Daraja API)
- Insurance claims (SHA, NHIF)
- Invoice generation
- Receipt printing

## Non-Functional Requirements

### Localization
- Kenya phone format: +254
- Emergency numbers: 999, 112
- Currency: KES
- Timezone: Africa/Nairobi
- Languages: English, Swahili

### Compliance
- Kenya Data Protection Act 2019
- Patient confidentiality
- Audit logging
- Data backup

### Performance
- Page load < 3s on 3G
- Support 100+ concurrent users
- Mobile-responsive design
