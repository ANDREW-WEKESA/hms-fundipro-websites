# Implementation Plan: HMS & FundiPro Kenyan Localisation

## Overview

This plan covers the localisation of two existing websites from US placeholder content to Kenyan content, implementing requirements from Parts A (HMS contact page), B (FundiPro landing page), and E (cross-cutting requirements).

**Current state:**
- HMS: 7 HTML pages (index, about, services, doctors, appointments, patient-portal, contact) + CSS + JS
- FundiPro: 5 HTML pages (index, about, services, professionals, contact) + CSS + JS
- Both contain US placeholders: +1 phone numbers, 911 emergency, US addresses, US insurance names, USD currency, 2024 copyright

**Target state:**
- All US content replaced with Kenyan equivalents
- All M-priority requirements from Parts A, B, and E implemented
- Dynamic copyright year
- Kenyan Data Protection Act 2019 compliance mentions
- New FundiPro pricing.html page

---

## Phase 1: HMS Localisation (Parallel Track A)

All HMS changes can be done in parallel with FundiPro changes since they are separate projects.

### FEAT-1: HMS Global Content Localisation (ALL pages)

- [ ] **Step 1.1: Replace emergency numbers in top bar across all HMS pages**
  - Files: All 7 HMS HTML files (index.html, about.html, services.html, doctors.html, appointments.html, patient-portal.html, contact.html)
  - Change: Replace `Emergency: +1-800-HMS-CARE` with `Emergency: 999 / 112 | +254 700 123 456`
  - Verify: Open each page in browser and confirm top bar shows new emergency numbers

- [ ] **Step 1.2: Replace address in top bar and footers across all HMS pages**
  - Files: All 7 HMS HTML files
  - Change: Replace `123 Healthcare Ave, Medical District, City 12345` with `Kenyatta Avenue, Nairobi CBD, Nairobi, Kenya`
  - Verify: Check top bar and footer contact section on each page

- [ ] **Step 1.3: Replace phone numbers in footers and contact sections**
  - Files: All 7 HMS HTML files
  - Change: Replace all `+1-` phone numbers with `+254 7XX XXX XXX` format (use +254 700 123 456, +254 733 456 789, etc.)
  - Verify: Use browser Find function (Ctrl+F) to search for `+1` and confirm none remain

- [ ] **Step 1.4: Replace email domains if needed**
  - Files: All 7 HMS HTML files
  - Change: Keep `info@hms-hospital.com` format (already generic, no US-specific domain)
  - Verify: Emails remain functional format

- [ ] **Step 1.5: Update insurance providers globally**
  - Files: index.html, services.html, about.html, appointments.html, contact.html
  - Change: Replace all mentions of "Blue Cross Blue Shield", "United Healthcare", "Aetna", "Cigna", "Medicare", "Medicaid" with "SHA (Social Health Authority), NHIF, AAR Insurance, Jubilee Insurance, Britam, CIC Insurance, Madison Insurance, Resolution Health, M-Pesa, Cash"
  - Specific locations:
    - services.html: Insurance Partners section (grid of 12 items → replace with 9 Kenyan options)
    - contact.html: FAQ section insurance answer
    - appointments.html: Insurance dropdown in Step 3
  - Verify: Search for "Blue Cross", "Aetna", "Medicare" etc. - should return zero results

- [ ] **Step 1.6: Make copyright year dynamic**
  - Files: All 7 HMS HTML files (footer)
  - Change: Replace `&copy; 2024` with `&copy; <span id="currentYear"></span>`
  - Add to js/main.js: `document.getElementById('currentYear')?.textContent = new Date().getFullYear();` at the end
  - Verify: Footer shows current year (2025), test by changing system date

- [ ] **Step 1.7: Add Privacy Policy data protection mention in footers**
  - Files: All 7 HMS HTML files
  - Change: Replace `Privacy Policy | Terms of Service` with `Privacy Policy (Kenya Data Protection Act 2019) | Terms of Service`
  - Verify: Footer contains Act reference on all pages

### FEAT-2: HMS Contact Page Specific Requirements (contact.html)

- [ ] **Step 2.1: Update contact cards section (HMS-W-10, HMS-W-11, HMS-W-12, HMS-W-13, HMS-W-14)**
  - File: hms/contact.html
  - Changes:
    - Main Reception card: Update phone numbers to +254 format
    - Emergency card: Apply red border (`style="border: 2px solid #dc3545; background: #ffe5e5;"`), update to show `999 / 112` prominently in large red text, plus hospital emergency line `+254 700 911 911`
    - Email card: Add text "Response within 24 hours on business days" (already present, verify)
    - Hours card: Keep structure (OPD, Emergency, Pharmacy, Lab) - already editable format
  - Verify: Emergency card visually distinct, all numbers use +254 format, tel: and mailto: links present

- [ ] **Step 2.2: Update contact form (HMS-W-20 to HMS-W-26)**
  - File: hms/contact.html
  - Changes:
    - Phone Number field placeholder: Change to `+254 700 000 000` format
    - Add emergency disclaimer above submit button: `<p style="color: #dc3545; font-weight: 600; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px;"><strong>⚠️ NOT for emergencies:</strong> For medical emergencies call 999, 112, or +254 700 911 911 immediately.</p>`
    - Add consent checkbox before submit button: `<label style="display: flex; align-items: start; margin-bottom: 20px;"><input type="checkbox" required style="margin-right: 10px; margin-top: 4px; width: auto;"><span>I consent to HMS collecting and processing my personal data in accordance with the Kenya Data Protection Act, 2019. <a href="#" style="color: var(--primary-blue);">Privacy Policy</a></span></label>`
  - Verify: Form has emergency warning, consent checkbox is required, placeholders show Kenyan format

- [ ] **Step 2.3: Update map section (HMS-W-30, HMS-W-31)**
  - File: hms/contact.html
  - Changes:
    - Replace map placeholder with Google Maps embed: `<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.819737908327!2d36.8172!3d-1.286389!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMcKwMTcnMTEuMCJTIDM2wrA0OScwMi4wIkU!5e0!3m2!1sen!2ske!4v1234567890" width="100%" height="450" style="border:0; border-radius: 10px;" allowfullscreen="" loading="lazy"></iframe>` (use Nairobi CBD coordinates)
    - Add "Open in Google Maps" link: `<a href="https://maps.google.com/?q=-1.286389,36.8172" class="btn btn-outline" style="margin-top: 20px;">📍 Open in Google Maps</a>`
    - Update directions text: "Located on Kenyatta Avenue in Nairobi CBD. Accessible via Thika Road or Uhuru Highway. Matatu routes: 23, 34, 46 from Railways. Nearest stage: Kencom. Landmarks: Near KCB Building and KICC. Parking available on-site."
  - Verify: Map loads and shows Nairobi location, directions mention Kenyan matatu routes and landmarks

- [ ] **Step 2.4: Update department contact directory (HMS-W-40, HMS-W-41, HMS-W-42)**
  - File: hms/contact.html
  - Changes:
    - Update all phone numbers in table to +254 format (generate 12 unique numbers)
    - Add JavaScript for open/closed badges: After table, add `<script>document.addEventListener('DOMContentLoaded', function() { const now = new Date().toLocaleString('en-US', {timeZone: 'Africa/Nairobi'}); const nairobiTime = new Date(now); const hours = nairobiTime.getHours(); document.querySelectorAll('.department-table tbody tr').forEach(row => { const hoursCell = row.cells[2].textContent; const badge = document.createElement('span'); if(hoursCell.includes('24/7')) { badge.textContent = ' OPEN'; badge.style.cssText = 'color: #28a745; font-weight: bold; margin-left: 10px;'; } else { badge.textContent = hours >= 8 && hours < 18 ? ' OPEN' : ' CLOSED'; badge.style.cssText = hours >= 8 && hours < 18 ? 'color: #28a745; font-weight: bold; margin-left: 10px;' : 'color: #dc3545; font-weight: bold; margin-left: 10px;'; } row.cells[2].appendChild(badge); }); });</script>`
  - Verify: Department table shows Kenyan phone numbers, JavaScript displays OPEN/CLOSED badges based on Africa/Nairobi timezone

- [ ] **Step 2.5: Update FAQ insurance answer (HMS-W-50, HMS-W-52)**
  - File: hms/contact.html
  - Change: Replace insurance answer with "We accept SHA (Social Health Authority), NHIF (for existing members), AAR Insurance, Jubilee Insurance, Britam, CIC Insurance, Madison Insurance, Resolution Health, and other private insurers. We also accept M-Pesa and cash payments. Contact our Billing & Insurance department at +254 700 123 466 for specific inquiries."
  - Verify: FAQ card shows Kenyan insurance options

- [ ] **Step 2.6: Update CTA buttons (HMS-W-60)**
  - File: hms/contact.html
  - Change: "Call Us Now" button should be `<a href="tel:+254700123456" class="btn btn-secondary">Call Us Now</a>` with Kenyan number
  - Verify: Button shows tel: link and mobile devices can tap-to-call

### FEAT-3: HMS Additional Page Updates

- [ ] **Step 3.1: Update doctors.html**
  - File: hms/doctors.html
  - Changes:
    - Search bar placeholder: Keep generic "Search by doctor name or specialty..."
    - Emergency button in nav: Updates covered in FEAT-1
  - Verify: No US-specific content remains

- [ ] **Step 3.2: Update appointments.html**
  - File: hms/appointments.html
  - Changes:
    - Phone number placeholder in Step 3: `+254 700 000 000`
    - Insurance dropdown options (Step 3): Already covered in FEAT-1 Step 1.5
  - Verify: Form uses Kenyan phone format

- [ ] **Step 3.3: Update patient-portal.html**
  - File: hms/patient-portal.html
  - Changes:
    - Technical Support phone: Update to +254 format
    - Add mention of Kenya Data Protection Act 2019 in Security & Privacy section: Add to HIPAA Compliant card text: "...and comply with the Kenya Data Protection Act, 2019 to ensure..."
  - Verify: Support contact is Kenyan, data protection act mentioned

- [ ] **Step 3.4: Update main.js emergency alert**
  - File: hms/js/main.js
  - Change: Line ~350 (Emergency button alert): `alert('Emergency Hotline: 999 / 112 / +254 700 911 911\n\nFor life-threatening emergencies, call immediately.');`
  - Verify: Clicking emergency link shows Kenyan emergency numbers

### Verification for HMS Phase:
- Run: `cd "c:\dev\New folder\hms" && Start-Process index.html`
- Check each page loads without errors
- Search all HTML files for "+1" - should return zero matches
- Search for "Blue Cross", "Aetna", "Medicare" - zero matches
- Verify footer shows current year dynamically
- Test contact form validation
- Confirm emergency numbers appear consistently across all pages

---

## Phase 2: FundiPro Localisation (Parallel Track B)

All FundiPro changes can be done in parallel with HMS.

### FEAT-4: FundiPro Global Content Localisation (ALL pages)

- [ ] **Step 4.1: Replace all phone numbers**
  - Files: All 5 FundiPro HTML files (index, about, services, professionals, contact)
  - Change: Replace `+1` or US-format numbers with `+254 7XX XXX XXX`
  - Contact page shows: +254 700 123 456 and +254 733 987 654
  - Verify: Search for "+1" returns zero results

- [ ] **Step 4.2: Replace addresses**
  - Files: contact.html, about.html, footer sections
  - Change: Replace US addresses with "Westlands Square, 4th Floor, Waiyaki Way, Nairobi, Kenya"
  - Verify: All address references show Kenyan location

- [ ] **Step 4.3: Update email domains**
  - Files: contact.html primarily
  - Change: Update to `info@fundipro.co.ke`, `support@fundipro.co.ke`, `partners@fundipro.co.ke`
  - Verify: Email links use .co.ke domain

- [ ] **Step 4.4: Update currency from USD to KES**
  - Files: services.html (all service cards showing hourly rates)
  - Change: Replace all "$" with "KES" and adjust amounts (e.g., "$20/hour" → "KES 1,500/hour")
  - Use these rates:
    - Plumbing: KES 1,500/hour
    - Electrical: KES 2,000/hour
    - Carpentry: KES 1,800/hour
    - Painting: KES 1,200/hour
    - Cleaning: KES 800/hour
    - Landscaping: KES 1,500/hour
    - Roofing: KES 2,500/hour
    - Tiling: KES 1,600/hour
    - Masonry: KES 1,800/hour
    - Welding: KES 2,000/hour
    - HVAC: KES 2,200/hour
    - Appliance Repair: KES 1,500/hour
  - Also update professionals.html professional card rates to match
  - Verify: No "$" symbols remain, all rates show "KES"

- [ ] **Step 4.5: Update professional locations**
  - Files: index.html (featured professionals), professionals.html (all professional cards)
  - Change: Verify locations already show Kenyan cities (Nairobi, Mombasa, Kisumu, Nakuru, etc.) - they do!
  - Verify: Professional locations are Kenyan

- [ ] **Step 4.6: Make copyright year dynamic**
  - Files: All 5 FundiPro HTML files (footer)
  - Change: Replace `&copy; 2024` with `&copy; <span id="currentYear"></span>`
  - In js/main.js, add after line 1: `document.getElementById('currentYear')?.textContent = new Date().getFullYear();`
  - Verify: Footer shows current year

- [ ] **Step 4.7: Add data protection act mention**
  - Files: All 5 FundiPro HTML files (footer)
  - Change: Update Privacy Policy link text to "Privacy Policy (Kenya Data Protection Act 2019)"
  - Verify: Footer contains Act reference

- [ ] **Step 4.8: Update about.html "since 2020" references**
  - File: fundipro/about.html
  - Change: Footer tagline and timeline are consistent with "since 2020" - keep as is (FP-W-21: don't claim false numbers)
  - Verify: Timeline and founding date reference 2020 appropriately

### FEAT-5: FundiPro Index Page Updates (FP-W-10 to FP-W-21, FP-W-30, FP-W-40)

- [ ] **Step 5.1: Update hero search bar service types (FP-W-11)**
  - File: fundipro/index.html
  - Change: Ensure all service types in dropdown match spec: Plumbing, Electrical, Carpentry, Painting, Cleaning, Landscaping, Roofing, Tiling (already present, verify complete)
  - Verify: Service dropdown has all 8 core services

- [ ] **Step 5.2: Enhance location input (FP-W-12)**
  - File: fundipro/index.html
  - Changes:
    - Update location input to include datalist:
      ```html
      <input type="text" id="location" name="location" list="locationList" placeholder="Enter your location" required>
      <datalist id="locationList">
        <option value="Nairobi">
        <option value="Mombasa">
        <option value="Kisumu">
        <option value="Nakuru">
        <option value="Eldoret">
        <option value="Thika">
        <option value="Nyeri">
        <option value="Machakos">
        <option value="Meru">
        <option value="Garissa">
        <option value="Malindi">
        <option value="Kitale">
        <option value="Kakamega">
        <option value="Kericho">
        <option value="Embu">
        <option value="Westlands">
        <option value="Karen">
        <option value="Kilimani">
        <option value="Eastleigh">
        <option value="South B">
        <option value="South C">
      </datalist>
      ```
    - Add "Use My Location" button after location input:
      ```html
      <button type="button" class="btn btn-outline" id="useLocationBtn" style="white-space: nowrap;">📍 Use My Location</button>
      ```
    - Add to main.js geolocation handler:
      ```javascript
      const useLocationBtn = document.getElementById('useLocationBtn');
      if(useLocationBtn) {
        useLocationBtn.addEventListener('click', function() {
          if("geolocation" in navigator) {
            this.disabled = true;
            this.textContent = 'Getting location...';
            navigator.geolocation.getCurrentPosition(
              (position) => {
                document.getElementById('location').value = `Lat: ${position.coords.latitude.toFixed(2)}, Lng: ${position.coords.longitude.toFixed(2)}`;
                this.disabled = false;
                this.textContent = '📍 Use My Location';
                alert('Location detected! In production, this would reverse-geocode to show area name.');
              },
              (error) => {
                this.disabled = false;
                this.textContent = '📍 Use My Location';
                alert('Could not get location. Please enter manually.');
              }
            );
          } else {
            alert('Geolocation not supported by your browser.');
          }
        });
      }
      ```
  - Verify: Location input shows autocomplete suggestions for Kenyan cities, geolocation button works

- [ ] **Step 5.3: Update search form submission (FP-W-13)**
  - File: fundipro/index.html + js/main.js
  - Change: Verify search redirects to professionals.html?service=X&location=Y (already implemented in main.js initSearchForm)
  - Verify: Searching redirects correctly with query params

- [ ] **Step 5.4: Update hero CTAs (FP-W-14)**
  - File: fundipro/index.html
  - Change: Verify buttons are:
    - "Find a Fundi" → professionals.html (already correct)
    - "Join as Professional" → contact.html?type=professional (update from generic contact.html)
  - Change Join button href to: `contact.html?type=professional`
  - Verify: Join button includes query parameter

- [ ] **Step 5.5: Add demo data disclaimer to stats (FP-W-20, FP-W-21)**
  - File: fundipro/index.html
  - Change: Add disclaimer above or below stats section:
    ```html
    <p style="text-align: center; font-size: 0.9rem; color: #666; margin-top: 20px;">
      * Statistics shown are projections based on platform growth. Live data will be available upon full launch.
    </p>
    ```
  - Verify: Stats section has disclaimer clearly visible

- [ ] **Step 5.6: Update service category cards (FP-W-30)**
  - File: fundipro/index.html
  - Change: Service count text change from hardcoded numbers to: "50+ professionals" format for each (to avoid false claims per FP-W-21)
  - E.g., change `<p class="service-count">1,200+ Professionals</p>` to `<p class="service-count">50+ Professionals</p>` for all 8 cards
  - Verify: All service cards show "50+ Professionals" (realistic claim)

- [ ] **Step 5.7: Verify "How It Works" section (FP-W-40)**
  - File: fundipro/index.html
  - Change: Ensure step 3 mentions "pay via M-Pesa" - update text to: "Review the work, pay securely via M-Pesa or bank transfer, and rate your experience."
  - Verify: M-Pesa mentioned in step 3

### FEAT-6: FundiPro Professionals Page Updates (FP-W-12, FP-W-13)

- [ ] **Step 6.1: Add search/filter functionality for URL params**
  - File: fundipro/professionals.html + js/main.js
  - Change: Verify applyURLFilters() function reads service and location from URL and pre-filters (already implemented in main.js around line 280)
  - Verify: Opening professionals.html?service=plumbing&location=Nairobi filters correctly

- [ ] **Step 6.2: Add verification badges to verified professionals**
  - File: fundipro/professionals.html
  - Change: Ensure professional cards show `<span class="badge">Verified</span>` (already present on most cards)
  - Verify: Badge appears on verified professionals

- [ ] **Step 6.3: Update location input in page search**
  - File: fundipro/professionals.html
  - Change: Add same datalist from Step 5.2 to the search bar location input at top of page
  - Verify: Search bar location has autocomplete

### FEAT-7: FundiPro Contact Page Updates

- [ ] **Step 7.1: Update contact info cards**
  - File: fundipro/contact.html
  - Change: Update to Kenyan address, phone, email (covered in FEAT-4)
  - Add: Business hours already present, verify hours are appropriate
  - Verify: Contact cards show Kenyan details

- [ ] **Step 7.2: Update contact form subject dropdown**
  - File: fundipro/contact.html
  - Change: Ensure "Join as Professional" option exists (already present as "join")
  - Verify: Subject dropdown has appropriate options

- [ ] **Step 7.3: Update map directions**
  - File: fundipro/contact.html
  - Change: Update directions text: "Located in Westlands, easily accessible via Waiyaki Way from Nairobi CBD. Matatu routes: 23, 33, 46 stop at Westlands Roundabout (5 min walk). Landmarks: Near Sarit Centre and ABC Place. Ample parking available."
  - Verify: Directions mention matatu routes and Nairobi landmarks

- [ ] **Step 7.4: Add data protection consent to form**
  - File: fundipro/contact.html
  - Change: Add after message field, before submit button:
    ```html
    <div class="form-group">
      <label style="display: flex; align-items: start; cursor: pointer;">
        <input type="checkbox" name="consent" required style="margin-right: 10px; margin-top: 4px; width: auto;">
        <span>I consent to FundiPro processing my personal data in accordance with the Kenya Data Protection Act, 2019. <a href="#" style="color: var(--primary-blue);">View Privacy Policy</a></span>
      </label>
    </div>
    ```
  - Update form validation in main.js to check consent checkbox
  - Verify: Consent checkbox is required, form won't submit without it

### FEAT-8: FundiPro New Pricing Page

- [ ] **Step 8.1: Create pricing.html page**
  - File: fundipro/pricing.html (NEW FILE)
  - Content:
    ```html
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="FundiPro pricing plans for professionals. Choose the plan that fits your business needs.">
        <title>Pricing - FundiPro</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <!-- Navigation Bar - copy from any existing page -->
        <nav class="navbar" id="navbar">
            <div class="container">
                <div class="nav-wrapper">
                    <div class="logo">
                        <a href="index.html">FundiPro</a>
                    </div>
                    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <ul class="nav-menu" id="navMenu">
                        <li><a href="index.html" class="nav-link">Home</a></li>
                        <li><a href="services.html" class="nav-link">Services</a></li>
                        <li><a href="professionals.html" class="nav-link">Professionals</a></li>
                        <li><a href="pricing.html" class="nav-link active">Pricing</a></li>
                        <li><a href="about.html" class="nav-link">About</a></li>
                        <li><a href="contact.html" class="nav-link">Contact</a></li>
                        <li><a href="contact.html?type=professional" class="btn btn-primary btn-nav">Get Started</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Hero -->
        <section class="page-hero">
            <div class="container">
                <div class="page-hero-content animate-on-scroll">
                    <h1 class="page-title">Pricing Plans</h1>
                    <p class="page-subtitle">Choose the plan that works best for your business</p>
                </div>
            </div>
        </section>

        <!-- Pricing Cards -->
        <section class="pricing-section section">
            <div class="container">
                <div class="pricing-grid">
                    <!-- Free Plan -->
                    <div class="pricing-card animate-on-scroll">
                        <div class="pricing-header">
                            <h3 class="pricing-title">Free</h3>
                            <div class="pricing-price">
                                <span class="price-currency">KES</span>
                                <span class="price-amount">0</span>
                                <span class="price-period">/month</span>
                            </div>
                            <p class="pricing-subtitle">Perfect for getting started</p>
                        </div>
                        <ul class="pricing-features">
                            <li>✓ Basic profile listing</li>
                            <li>✓ Up to 3 job requests per month</li>
                            <li>✓ Basic customer reviews</li>
                            <li>✓ Mobile app access</li>
                            <li>✗ Priority listing</li>
                            <li>✗ Advanced analytics</li>
                            <li>✗ Verified badge</li>
                        </ul>
                        <a href="contact.html?type=professional&plan=free" class="btn btn-outline btn-block">Get Started Free</a>
                    </div>

                    <!-- Pro Plan -->
                    <div class="pricing-card pricing-card-featured animate-on-scroll">
                        <div class="pricing-badge">Most Popular</div>
                        <div class="pricing-header">
                            <h3 class="pricing-title">Pro</h3>
                            <div class="pricing-price">
                                <span class="price-currency">KES</span>
                                <span class="price-amount">500</span>
                                <span class="price-period">/month</span>
                            </div>
                            <p class="pricing-subtitle">For growing professionals</p>
                        </div>
                        <ul class="pricing-features">
                            <li>✓ Enhanced profile with photos</li>
                            <li>✓ Unlimited job requests</li>
                            <li>✓ Priority in search results</li>
                            <li>✓ Customer reviews & ratings</li>
                            <li>✓ Basic analytics dashboard</li>
                            <li>✓ Email notifications</li>
                            <li>✓ Verified professional badge</li>
                        </ul>
                        <a href="contact.html?type=professional&plan=pro" class="btn btn-primary btn-block">Start Pro Trial</a>
                        <p style="text-align: center; font-size: 0.85rem; margin-top: 10px; color: #666;">14-day free trial</p>
                    </div>

                    <!-- Business Plan -->
                    <div class="pricing-card animate-on-scroll">
                        <div class="pricing-header">
                            <h3 class="pricing-title">Business</h3>
                            <div class="pricing-price">
                                <span class="price-currency">KES</span>
                                <span class="price-amount">1,200</span>
                                <span class="price-period">/month</span>
                            </div>
                            <p class="pricing-subtitle">For established businesses</p>
                        </div>
                        <ul class="pricing-features">
                            <li>✓ Premium profile with video</li>
                            <li>✓ Unlimited job requests</li>
                            <li>✓ Top priority placement</li>
                            <li>✓ Advanced analytics & insights</li>
                            <li>✓ Lead management tools</li>
                            <li>✓ Multiple team members</li>
                            <li>✓ Dedicated account manager</li>
                            <li>✓ Featured professional status</li>
                        </ul>
                        <a href="contact.html?type=professional&plan=business" class="btn btn-outline btn-block">Go Business</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Payment Methods -->
        <section class="payment-methods section section-alt">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2 class="section-title">Payment Methods</h2>
                    <p class="section-subtitle">We accept multiple payment options</p>
                </div>
                <div class="payment-grid">
                    <div class="payment-card animate-on-scroll">
                        <div class="payment-icon">📱</div>
                        <h3>M-Pesa</h3>
                        <p>Pay via M-Pesa Paybill or Till Number. Instant activation.</p>
                    </div>
                    <div class="payment-card animate-on-scroll">
                        <div class="payment-icon">🏦</div>
                        <h3>Bank Transfer</h3>
                        <p>Direct bank transfer to our account. Activates within 24 hours.</p>
                    </div>
                    <div class="payment-card animate-on-scroll">
                        <div class="payment-icon">💳</div>
                        <h3>Debit/Credit Card</h3>
                        <p>Visa, Mastercard accepted. Secure online payment.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section section">
            <div class="container">
                <div class="section-header animate-on-scroll">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                </div>
                <div class="faq-accordion">
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span>Can I change my plan later?</span>
                            <span class="faq-icon">+</span>
                        </button>
                        <div class="faq-answer">
                            <p>Yes! You can upgrade or downgrade your plan at any time. Changes take effect at the start of your next billing cycle.</p>
                        </div>
                    </div>
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span>Is there a setup fee?</span>
                            <span class="faq-icon">+</span>
                        </button>
                        <div class="faq-answer">
                            <p>No setup fees. You only pay the monthly subscription for your chosen plan.</p>
                        </div>
                    </div>
                    <div class="faq-item animate-on-scroll">
                        <button class="faq-question">
                            <span>What happens if I cancel?</span>
                            <span class="faq-icon">+</span>
                        </button>
                        <div class="faq-answer">
                            <p>You can cancel anytime. Your account will remain active until the end of your current billing period. No refunds for partial months.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="cta-banner">
            <div class="container">
                <div class="cta-content animate-on-scroll">
                    <h2 class="cta-title">Ready to Grow Your Business?</h2>
                    <p class="cta-subtitle">Join thousands of professionals on FundiPro</p>
                    <a href="contact.html?type=professional" class="btn btn-primary btn-lg">Get Started Today</a>
                </div>
            </div>
        </section>

        <!-- Footer - copy from any existing page -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-section">
                        <div class="footer-logo">FundiPro</div>
                        <p class="footer-tagline">Connecting skilled professionals with customers since 2020</p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook">📘</a>
                            <a href="#" aria-label="Twitter">🐦</a>
                            <a href="#" aria-label="Instagram">📷</a>
                            <a href="#" aria-label="LinkedIn">💼</a>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h4 class="footer-title">Quick Links</h4>
                        <ul class="footer-links">
                            <li><a href="index.html">Home</a></li>
                            <li><a href="services.html">Services</a></li>
                            <li><a href="professionals.html">Find Professionals</a></li>
                            <li><a href="pricing.html">Pricing</a></li>
                            <li><a href="about.html">About Us</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4 class="footer-title">For Professionals</h4>
                        <ul class="footer-links">
                            <li><a href="contact.html?type=professional">Join FundiPro</a></li>
                            <li><a href="pricing.html">Pricing</a></li>
                            <li><a href="#">Success Stories</a></li>
                            <li><a href="#">Resources</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4 class="footer-title">Support</h4>
                        <ul class="footer-links">
                            <li><a href="contact.html">Contact Us</a></li>
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Privacy Policy (Kenya Data Protection Act 2019)</a></li>
                            <li><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <span id="currentYear"></span> FundiPro. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <button class="back-to-top" id="backToTop" aria-label="Back to top">↑</button>
        <script src="js/main.js"></script>
    </body>
    </html>
    ```
  - Verify: pricing.html loads, styles apply, FAQ accordion works

- [ ] **Step 8.2: Add pricing link to all FundiPro page navigations**
  - Files: index.html, about.html, services.html, professionals.html, contact.html
  - Change: In nav menu, add after "Professionals": `<li><a href="pricing.html" class="nav-link">Pricing</a></li>`
  - Also add to footer "For Professionals" section: `<li><a href="pricing.html">Pricing</a></li>`
  - Verify: Pricing link appears in nav and footer on all pages

### Verification for FundiPro Phase:
- Run: `cd "c:\dev\New folder\fundipro" && Start-Process index.html`
- Check all pages load without errors
- Search all HTML files for "+1", "$", "USD" - zero matches
- Verify KES currency throughout
- Test search functionality with query params
- Confirm pricing page displays correctly
- Test geolocation button
- Verify M-Pesa mentioned in payment contexts

---

## Phase 3: Cross-Cutting Enhancements (Both Sites)

These apply to both HMS and FundiPro.

### FEAT-9: Accessibility Compliance (NFR-08)

- [ ] **Step 9.1: Add ARIA labels where missing**
  - Files: All HTML files in both projects
  - Check: All form inputs have associated `<label>` or `aria-label`
  - Check: All icon buttons have `aria-label` (hamburger menus, back-to-top, etc.)
  - Check: All images have `alt` attributes (or empty alt="" for decorative)
  - Verify: Run accessibility checker (browser DevTools Lighthouse) - no missing labels

- [ ] **Step 9.2: Ensure keyboard navigation**
  - Files: Both main.js files
  - Check: Hamburger menus, modals, and forms can be operated with Tab, Enter, Esc keys
  - Add: Esc key handler for modals
  - Verify: Tab through entire page, all interactive elements are reachable

- [ ] **Step 9.3: Add focus indicators**
  - Files: hms/css/style.css, fundipro/css/style.css
  - Add/verify focus styles exist:
    ```css
    a:focus, button:focus, input:focus, select:focus, textarea:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 2px;
    }
    ```
  - Verify: Focus visible when tabbing through pages

- [ ] **Step 9.4: Check color contrast**
  - Files: Both CSS files
  - Use browser DevTools or online contrast checker
  - Ensure text/background contrast ≥ 4.5:1 for normal text, ≥ 3:1 for large text
  - Verify: No contrast warnings in Lighthouse report

### FEAT-10: Performance Optimisation (NFR-01)

- [ ] **Step 10.1: Add lazy loading to images**
  - Files: All HTML files with `<img>` tags
  - Add: `loading="lazy"` attribute to all images that are not above the fold
  - Verify: Images below fold load only when scrolling near them

- [ ] **Step 10.2: Verify no heavy dependencies**
  - Files: All HTML files
  - Check: No external CDN libraries loaded (jQuery, Bootstrap, etc.) - confirmed both sites use vanilla JS
  - Verify: Pages load quickly, no blocking resources

### FEAT-11: Legal Compliance

- [ ] **Step 11.1: Ensure Privacy Policy and Terms links in footers**
  - Files: All HTML files (both projects)
  - Verify: Footer contains "Privacy Policy (Kenya Data Protection Act 2019)" and "Terms of Service" links
  - Verify: Links are present and clearly visible

- [ ] **Step 11.2: Consent checkboxes on forms**
  - Files: hms/contact.html, fundipro/contact.html
  - Verify: Both contact forms have required consent checkboxes mentioning Kenya Data Protection Act 2019
  - Verify: Forms cannot submit without consent

---

## Phase 4: Final Verification & Testing

### FEAT-12: Comprehensive Testing

- [ ] **Step 12.1: HMS Cross-browser testing**
  - Test in: Chrome, Firefox, Edge
  - Check: All pages render correctly, forms work, navigation functions
  - Check: Mobile responsive (resize to 375px width)
  - Verify: No console errors, no broken links

- [ ] **Step 12.2: FundiPro Cross-browser testing**
  - Test in: Chrome, Firefox, Edge
  - Check: All pages render correctly, search/filter works, pricing page displays
  - Check: Mobile responsive
  - Verify: No console errors, geolocation works, animations trigger

- [ ] **Step 12.3: Content verification checklist**
  - Run search across ALL files for these terms (should return ZERO matches):
    - "+1-" or "+1 " (US phone format)
    - "911" (except in emergency context with 999/112)
    - "Blue Cross"
    - "Aetna"
    - "Medicare"
    - "Medicaid"
    - "United Healthcare"
    - "Cigna"
    - "$" or "USD" (in FundiPro context)
    - "2024" (in copyright context - should be dynamic)
    - Any US city names (e.g., "City 12345")
  - Verify: All searches return zero or appropriate contextual matches only

- [ ] **Step 12.4: Functional testing**
  - HMS:
    - Submit contact form (with validation)
    - Click emergency button, verify alert shows Kenyan numbers
    - Check department open/closed badges update based on time
    - Book appointment flow (all 4 steps)
    - Test back-to-top button
  - FundiPro:
    - Search with service/location, verify redirect
    - Filter professionals by service, rating, location
    - Submit contact form with consent
    - Test "Use My Location" button
    - Navigate to pricing page, check all links
    - Test FAQ accordion

- [ ] **Step 12.5: Accessibility audit**
  - Run Lighthouse on 3 pages from each site (index, contact, one other)
  - Target scores: Accessibility ≥ 90
  - Fix any critical issues flagged
  - Verify: All forms have proper labels, focus indicators work, color contrast passes

- [ ] **Step 12.6: Performance audit**
  - Run Lighthouse Performance test
  - Target: Performance ≥ 80
  - Check: No large unoptimized images, lazy loading working
  - Verify: Pages load in < 3 seconds on 3G

- [ ] **Step 12.7: Create verification report**
  - File: c:\dev\New folder\.agents\tasks\verification-report.md
  - Document:
    - All requirements implemented (checklist from specs)
    - Browser compatibility confirmed
    - Lighthouse scores
    - Any known issues or limitations
    - Screenshots of key pages

---

## Summary of Parallel Execution

**Can be done in parallel:**
- FEAT-1, FEAT-2, FEAT-3 (HMS) run concurrently with FEAT-4, FEAT-5, FEAT-6, FEAT-7, FEAT-8 (FundiPro)
- Within each site, global changes (emergency numbers, addresses, copyright) should be done first, then page-specific changes
- FEAT-9, FEAT-10, FEAT-11 (cross-cutting) can be done once core localisation is complete
- FEAT-12 (testing) is final

**Recommended Order:**
1. Start with global changes (phone numbers, addresses, currency, copyright) in both sites simultaneously
2. Then do page-specific enhancements
3. Then cross-cutting accessibility/performance
4. Finally comprehensive testing

**Key Files Modified:**
- HMS: 7 HTML files + 1 JS file + potentially CSS for focus styles
- FundiPro: 5 HTML files + 1 NEW HTML (pricing) + 1 JS file + potentially CSS for focus styles

**Total Estimated Items:** ~70 checklist items across 12 FEATs

**Critical Success Criteria:**
- Zero US phone numbers, addresses, insurance names remain
- All currency in KES (FundiPro)
- Dynamic copyright year works
- Kenya Data Protection Act 2019 mentioned in privacy contexts
- Emergency numbers (999/112) prominent in HMS
- M-Pesa mentioned as payment option (both sites)
- All forms have proper validation and consent
- Accessibility score ≥ 90
- No broken links or console errors

---

## Notes for Implementer

- Use Find/Replace carefully - many phone numbers appear in multiple places
- Test forms after adding consent checkboxes to ensure validation works
- When updating JavaScript, test thoroughly - dynamic year, geolocation, department status badges
- Keep backup of original files before starting
- Use browser DevTools mobile emulation to test responsive design
- Verify email and tel: links are clickable on mobile devices
- Check that social media links (even though placeholder) still have proper aria-labels
- Ensure all maps/iframes load properly (may need actual Google Maps API key for production)
- The pricing page uses existing CSS classes - no new CSS needed
- Professional counts in FundiPro should be conservative ("50+") per FP-W-21 requirement
- When adding datalists, ensure they don't break existing functionality
- Test geolocation on HTTPS (required by browsers for security)

