# FundiPro Backend Requirements Documentation

## 1. Platform Overview

FundiPro is a digital marketplace connecting Kenyan customers with skilled professionals across various service categories. The platform enables customers to post job requirements, receive competitive quotes from verified professionals, and complete secure bookings with integrated mobile money payments.

**Target Market**: Kenya (all 47 counties)  
**Primary Users**: Individual customers, service professionals, platform administrators  
**Core Value Proposition**: Trusted, affordable, and convenient access to skilled professionals

## 2. User Types

### 2.1 Customers
- Post job requests with descriptions, budgets, and location
- Receive and compare quotes from multiple professionals
- Book services and make payments via M-Pesa
- Rate and review completed jobs
- Track job history and active bookings

### 2.2 Professionals
- Create detailed profiles with skills, experience, and pricing
- Browse available jobs in their category and county
- Submit competitive quotes with custom messages
- Manage bookings and service delivery
- Build reputation through customer reviews
- Access subscription tiers for enhanced visibility

### 2.3 Platform Administrators
- Verify professional credentials and certifications
- Monitor platform activity and resolve disputes
- Manage service categories and counties
- Generate analytics and business reports
- Handle customer support escalations

## 3. Core Features

### 3.1 Phone-Based Authentication
- Primary identifier: Kenyan mobile phone numbers (+254 format)
- OTP verification via Africa's Talking SMS Gateway
- Password-based login after initial registration
- Phone number validation: +254[17]\d{8} format
- Support for 07xx and 01xx normalization to +254 format

### 3.2 Job Posting & Management
- Title, description, category selection
- Budget range in KES (Kenyan Shillings)
- County/location specification
- Photo uploads for visual context
- Status tracking: open, quoted, booked, completed, cancelled
- Job expiry after 30 days of inactivity

### 3.3 Quote Submission & Comparison
- Professionals submit quotes with pricing and timeline
- Custom messages to differentiate offerings
- Quote validity period (7 days default)
- Customer can accept, reject, or negotiate
- Automatic quote ranking by price and professional rating

### 3.4 Booking & Payment
- M-Pesa integration via Safaricom Daraja API
- STK Push for seamless payment experience
- Escrow system: funds held until job completion
- Payment confirmation via callbacks
- Transaction history and receipts
- Supported amounts: KES 50 - KES 500,000 per transaction

### 3.5 Rating & Review System
- 5-star rating scale
- Written reviews with character limits
- Mutual reviews (customer reviews professional, vice versa)
- Review moderation for inappropriate content
- Average rating display on professional profiles
- Review verification (only for completed jobs)

### 3.6 Professional Subscription Tiers

#### Free Tier
- Basic profile listing
- Submit up to 5 quotes per month
- Standard search visibility
- Cost: KES 0

#### Pro Tier (KES 999/month)
- Enhanced profile with photo gallery
- Unlimited quote submissions
- Priority placement in search results
- Badge indicating verified professional
- Monthly performance analytics
- Cost: KES 999/month

#### Business Tier (KES 2,499/month)
- All Pro features
- Featured listing on category pages
- Lead notifications via SMS
- Dedicated support channel
- Custom branding options
- Cost: KES 2,499/month

## 4. Geographic Coverage

### 4.1 Kenyan Counties (All 47)
Mombasa, Kwale, Kilifi, Tana River, Lamu, Taita-Taveta, Garissa, Wajir, Mandera, Marsabit, Isiolo, Meru, Tharaka-Nithi, Embu, Kitui, Machakos, Makueni, Nyandarua, Nyeri, Kirinyaga, Murang'a, Kiambu, Turkana, West Pokot, Samburu, Trans-Nzoia, Uasin Gishu, Elgeyo-Marakwet, Nandi, Baringo, Laikipia, Nakuru, Narok, Kajiado, Kericho, Bomet, Kakamega, Vihiga, Bungoma, Busia, Siaya, Kisumu, Homa Bay, Migori, Kisii, Nyamira, Nairobi

### 4.2 Location Filtering
- Professionals specify service counties
- Jobs tagged with county location
- Distance-based matching for urban areas
- Multi-county support for professionals

## 5. Service Categories

### 5.1 Primary Categories
1. **Plumbing** - pipe repairs, installations, drainage, water systems
2. **Electrical** - wiring, repairs, installations, solar systems
3. **Carpentry** - furniture making, repairs, installations
4. **Painting & Decoration** - interior/exterior painting, wallpaper
5. **Cleaning Services** - home, office, deep cleaning, fumigation
6. **Masonry & Construction** - building, repairs, renovations
7. **Landscaping & Gardening** - lawn care, garden design, maintenance
8. **Appliance Repair** - fridges, TVs, washing machines, microwaves
9. **HVAC Services** - air conditioning installation and repair
10. **Security Services** - alarm installation, CCTV, guard services
11. **Moving & Transportation** - relocation, delivery, logistics
12. **IT & Tech Support** - computer repair, networking, software
13. **Automotive Services** - mechanics, car wash, detailing
14. **Beauty & Personal Care** - hairdressing, barbering, makeup
15. **Event Services** - catering, decoration, photography

### 5.2 Category Attributes
- Name, slug, icon, description
- Typical price range guidance
- Required certifications (where applicable)
- Common job templates

## 6. Non-Functional Requirements

### 6.1 Security
- HTTPS encryption for all API endpoints
- Password hashing using bcrypt (minimum cost factor 10)
- SQL injection prevention via prepared statements
- XSS protection through input sanitization
- CSRF token validation for state-changing operations
- Rate limiting: 100 requests per minute per IP
- Session timeout: 24 hours for customers, 7 days for professionals
- PCI DSS compliance for payment handling (via M-Pesa)

### 6.2 Performance
- API response time: < 500ms for 95th percentile
- Database query optimization with proper indexing
- Caching strategy for frequently accessed data (Redis)
- Image optimization and CDN delivery
- Maximum file upload size: 5MB per image
- Concurrent user support: 10,000+ active sessions

### 6.3 Scalability
- Horizontal scaling capability for web servers
- Database replication for read-heavy operations
- Asynchronous job processing for emails, SMS, notifications
- Microservices architecture for payment and messaging
- Load balancing across multiple application instances

### 6.4 Availability
- Target uptime: 99.5% (excluding planned maintenance)
- Automated health checks and monitoring
- Graceful degradation for third-party service failures
- Database backup: daily full, hourly incremental
- Disaster recovery plan with 4-hour RTO

### 6.5 Compliance
- Kenya Data Protection Act 2019 compliance
- User consent for data collection and processing
- Right to data deletion and portability
- Transparent privacy policy and terms of service
- KRA compliance for tax reporting on transactions

## 7. Integration Requirements

### 7.1 Africa's Talking SMS Gateway
- OTP delivery for phone verification
- Bulk SMS for notifications and alerts
- Delivery status tracking
- Fallback to alternative provider on failure

### 7.2 Safaricom M-Pesa Daraja API
- STK Push for customer payments
- C2B payments for manual M-Pesa transfers
- B2C disbursements for professional payouts
- Transaction status queries and reconciliation
- Webhook callbacks for payment confirmation

### 7.3 Email Service (SMTP/SendGrid)
- Transactional emails: welcome, password reset, receipts
- Notification emails for new quotes, bookings
- Marketing emails for subscribed users
- Email templates with branding

## 8. API Design Principles

- RESTful architecture
- JSON request and response format
- Versioned endpoints (/api/v1/)
- Consistent error response structure
- Pagination for list endpoints (limit, offset)
- Authentication via JWT tokens
- Comprehensive API documentation (OpenAPI/Swagger)

## 9. Data Retention

- User accounts: retained until deletion request
- Job postings: 1 year after completion
- Transaction records: 7 years (legal requirement)
- Reviews: permanent (unless flagged and removed)
- Application logs: 90 days
- Anonymization of deleted user data

## 10. Future Enhancements

- Mobile app (iOS and Android)
- Real-time chat between customers and professionals
- Video consultations for remote diagnostics
- Multi-language support (English, Swahili)
- AI-powered professional recommendations
- Background check integration for professionals
- Insurance options for high-value jobs
- Referral program and loyalty rewards
