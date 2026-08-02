# FundiPro Requirements

## Functional Requirements

### Professional (Fundi) Features
- Registration with phone (+254 format)
- Profile with trades, experience, service area
- Portfolio (before/after photos)
- Verification system (ID, certificates)
- Job lead inbox
- Quote submission
- Calendar & availability
- Client database
- Invoicing & receipts
- Subscription management

### Customer Features
- Guest or registered access
- Location-based search
- Post job with photos
- Receive & compare quotes
- Hire & pay (M-Pesa)
- Rate & review
- Job history

### Services Catalog
- Plumbing
- Electrical work
- Carpentry
- Painting
- Cleaning
- Landscaping
- Roofing
- Tiling
- Masonry
- Welding
- Mechanics (vehicles)
- Tailoring
- Solar installation
- CCTV installation

## Kenya-Specific Requirements

### Localization
- Phone: +254 format validation
- Locations: Kenyan counties (47) and major towns
- Currency: KES
- Payment: M-Pesa primary
- Languages: English, Kiswahili

### Subscription Pricing
- **Free:** 3 jobs/month
- **Pro:** KES 500/month (unlimited jobs)
- **Business:** KES 1,200/month (team features)

### Trust & Safety
- National ID verification
- Certificate validation
- Customer reviews
- Dispute resolution
- Report system

## Technical Requirements

### Performance
- Mobile-first (3G networks)
- Image compression for portfolios
- SMS delivery tracking

### Security
- Hashed passwords (bcrypt)
- JWT authentication
- Rate limiting
- Data encryption

### Integrations
- M-Pesa Daraja API
- Africa's Talking SMS
- Google Maps / location services
