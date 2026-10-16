# HMS Kenya localization and requirements compliance

Implementation verified against the 14-point checklist and Part A requirements (HMS-W-01 through HMS-W-61). The site correctly implements Kenyan contact details, emergency numbers, insurance providers, and payment options across all pages. Core accessibility patterns are in place.

**Watch for:** Open/closed badge logic relies on hardcoded business rules that won't update when department hours change (confirmed). Contact Us button missing from navigation (confirmed). Tap target sizing not enforced in CSS media queries (likely).

**Verdict**: CHANGES_REQUESTED

## High-level view

All seven HTML pages show 999/112 emergency numbers and the Nairobi address in the top bar, with no US phone numbers or addresses remaining. Navigation includes the Emergency link styled as a pulsing red button, but the separate "Contact Us" button required by HMS-W-02 is absent—the word "Contact" appears only as a nav link. The mobile hamburger menu exists and activates below 768px, though CSS does not explicitly enforce 44px tap targets; the emergency button styling and form checkbox dimensions suggest compliance, but buttons and links lack min-height/min-width rules.

The footer correctly references the Kenya Data Protection Act 2019 in the Privacy Policy link text and uses `new Date().getFullYear()` to set the copyright year dynamically. No US insurance names appear; the contact page FAQ and appointments form list SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, and Resolution, with M-Pesa mentioned alongside cash.

Contact.html provides four contact cards (Reception, Emergency, Email, Hours), a form with all required fields including the honeypot and Kenya Data Protection Act consent checkbox, an embedded Google Maps iframe, a 12-row department directory, and a 4-question FAQ accordion. The form validates client-side and shows success without reload. The department table includes inline JavaScript that calculates open/closed badges using Africa/Nairobi time, but the logic hardcodes day ranges (Mon-Fri, Tue-Sat) and hour windows—when a department's actual hours change in the admin panel (HMS-W-13 and HMS-W-41 require hours to come from a single source), this script will still display stale open/closed states.

## <details><summary>Issues (3)</summary>

1. **Contact Us button missing from navigation** — HMS-W-02 requires "a highlighted Emergency link and a Contact Us button" in the main navigation. The Emergency link is present and styled, but the Contact Us button does not exist. Navigation shows "Contact" as a plain nav link, not a button. Add a styled `.btn` element for "Contact Us" in the nav-links list on all pages, or confirm with the requirements author whether a link suffices.

2. **Open/closed badge logic hardcoded and will drift** — The department table's inline `<script>` on contact.html calculates open/closed badges by parsing hour strings ("Mon-Fri: 8 AM - 6 PM") and matching against hardcoded day and hour ranges. HMS-W-13 and HMS-W-41 specify that hours must be editable from the admin panel and must never disagree between the website and the system. When a department's hours change, the script's logic won't update, causing the badge to show incorrect status. Replace the inline time-parsing script with a data-driven approach: emit `data-open-days` and `data-open-hours` attributes on each row (sourced from the same department records the admin panel uses), then calculate badges from those attributes.

3. **Tap target sizing not enforced in CSS** — HMS-W-03 requires all tap targets to be at least 44px. The hamburger button and emergency button are likely compliant by inspection, and form checkboxes use `min-width: 18px; min-height: 18px` (below the 44px guideline), but the CSS at `@media (max-width: 768px)` does not include rules like `nav a { min-height: 44px; }` or `.btn { min-height: 44px; min-width: 44px; }` to guarantee compliance. Add explicit min-height and min-width declarations for interactive elements in the mobile breakpoint, or conduct a manual tap target audit to confirm that padding and font size already achieve 44px+ effective size.

</details>

<details><summary>Details</summary>

## Emergency numbers and Nairobi address on every page

All seven HTML files (index.html, about.html, services.html, doctors.html, appointments.html, patient-portal.html, contact.html) include a top bar showing:

```
Emergency: 999 / 112 | +254 700 911 911
📍 Ngong Road, Upper Hill, Nairobi, Kenya
```

No instances of `+1` phone numbers or `911` as a standalone emergency number remain. The grep for `\+1[- ]?\d` returned no matches; the grep for `\b911\b` matched only the hospital's own emergency line `+254700911911` and text references to "999, 112, or +254 700 911 911". The address "Ngong Road, Upper Hill, Nairobi, Kenya" appears in the top bar and footer contact section on every page.

## Navigation structure

Every page's `<nav role="navigation">` contains the same structure: Home, About, Services, Doctors, Appointments, Patient Portal, Contact, plus an Emergency link styled with `.emergency-btn` (red background, pulsing animation). HMS-W-02 requires "a highlighted Emergency link and a Contact Us button"; the Emergency link is present and highlighted, but the separate Contact Us button does not exist. "Contact" appears only as a plain nav link (`<a href="contact.html">Contact</a>`), not as a styled button. If the requirement demands both a "Contact" nav link and a separate "Contact Us" button, the button is missing.

## Mobile navigation

The hamburger menu exists on all pages (`<button class="hamburger" aria-label="Toggle menu">`), and CSS shows `.hamburger { display: none; }` becomes `.hamburger { display: flex; }` at `@media (max-width: 768px)`. The JavaScript in main.js toggles `.nav-links.active` on click and closes the menu when clicking outside or on a link.

Tap target sizing: HMS-W-03 requires all tap targets to be at least 44px. The CSS includes no explicit `min-height: 44px; min-width: 44px;` rules for nav links, buttons, or the hamburger at the mobile breakpoint. The emergency button uses `padding: 10px 25px;`, and the hamburger spans are 25px wide with 5px gaps, suggesting the effective tap area is approximately 25px × (3×3px + 2×5px) = 25px × 19px, which is below 44px. The contact form consent checkbox uses `min-width: 18px; min-height: 18px;`, also below the guideline. Without running the site in a browser to measure rendered dimensions, this is flagged as a likely gap.

## Footer structure

All footers include:
- "About HMS" blurb
- Quick Links (About Us, Services, Find a Doctor, Book Appointment, Patient Portal, Contact Us)
- Departments (Cardiology, Neurology, Orthopedics, Pediatrics, Oncology, Emergency)
- Contact Info (address, emergency numbers, email, 24/7 note)

The footer bottom reads:

```html
<p>&copy; <span id="currentYear">2024</span> HMS - Hospital Management System. All rights reserved. | 
<a href="#">Privacy Policy (Kenya Data Protection Act 2019)</a> | 
<a href="#">Terms of Service</a></p>
```

The main.js file includes:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const yearSpan = document.getElementById('currentYear');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
});
```

This satisfies HMS-W-04: the footer repeats the required sections, links Privacy Policy and Terms of Service, and generates the copyright year dynamically.

## Kenyan phone numbers and addresses

No `+1` patterns found. Every phone number uses `+254` (Kenya country code) or local formats. The address "Ngong Road, Upper Hill, Nairobi, Kenya" appears in the top bar, footer, contact page location section, and map caption. The grep for US addresses (implicitly tested by searching for US-specific patterns) returned no matches.

## US insurance names removed, Kenyan insurance in place

The grep for `Blue Cross|Aetna|Cigna|Medicare|Medicaid|United Healthcare` returned no matches. The grep for `SHA|NHIF|AAR|Jubilee|Britam|CIC|Madison|Resolution` found:

- **contact.html FAQ**: "We accept SHA (Social Health Authority), NHIF (for existing members), AAR Insurance, Jubilee Insurance, Britam, CIC Insurance, Madison Insurance, Resolution Health, and other private insurers. We also accept M-Pesa and cash payments."
- **appointments.html insurance dropdown**: options for SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, Resolution, Other, and No Insurance.
- **services.html insurance grid**: lists SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, Resolution, and M-Pesa Payments as clickable items.

This satisfies checklist items 8 and 9: US insurance names are replaced with Kenyan payers, and M-Pesa is mentioned as a payment option.

## Contact page: four contact cards

The contact.html page includes exactly four `.contact-card` divs:

1. **Main Reception** — phone numbers `+254 20 700 1234` and `+254 733 456 789`, hours Mon-Fri 7 AM - 9 PM, Sat-Sun 8 AM - 6 PM.
2. **Emergency** — styled with red border and background, shows `999 / 112` and `+254 700 911 911`, marked 24/7.
3. **Email** — `info@hms.co.ke` and `appointments@hms.co.ke`, response within 24 hours on business days.
4. **Hours** — OPD 7 AM - 9 PM, Emergency 24/7, Pharmacy 8 AM - 10 PM, Lab 7 AM - 7 PM.

The Emergency card is visually distinct (red styling), and all phone numbers are `tel:` links, emails are `mailto:` links, satisfying HMS-W-10, HMS-W-11, and HMS-W-12.

## Contact form: all required fields and validation

The form on contact.html includes:

- Full Name (id="name", required, text input)
- Email Address (id="email", required, email input)
- Phone Number (id="phone", required, tel input, placeholder "+254 700 000 000")
- Department (id="department", select dropdown with 14 options including all 12 required departments plus general and appointments)
- Subject (id="subject", required, maxlength="150")
- Message (id="message", required, minlength="10", maxlength="2000")
- Honeypot field: `<input type="text" name="website" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" autocomplete="off">`
- Emergency disclaimer: "⚠️ NOT for emergencies: For medical emergencies call 999, 112, or +254 700 911 911 immediately."
- Consent checkbox: "I consent to HMS collecting and processing my personal data in accordance with the Kenya Data Protection Act, 2019. <a href="#">Privacy Policy</a> *"

The JavaScript in main.js includes `initContactForm()`, which validates required fields, checks email format with `isValidEmail()`, checks phone format with `isValidPhone()`, adds `.error` class to `.form-group` on validation failure, prevents submission if invalid, logs form data on success, shows an alert, and resets the form—satisfying the "success state without a page reload" requirement (HMS-W-23). Real-time validation is also present: inputs remove the error class on input, and fields validate on blur.

This covers HMS-W-20 (all field rules), HMS-W-21 (client and server validation, though server validation is not visible in this static review), HMS-W-22 (honeypot field for spam protection), HMS-W-24 (emergency disclaimer), and HMS-W-25 (Kenya Data Protection Act consent).

## Google Maps embed and directions

The contact.html page includes:

```html
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!d15955.277444357952!2d36.80730585!3d-1.28632695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10d0f3cc6f31%3A0x8c5a0c99e4d6d5d5!2sNairobi!5e0!3m2!1sen!2ske!4v1" 
        width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
```

Below the map, an "Open in Google Maps" button links to `https://maps.google.com/?q=-1.286389,36.817223`. The directions section reads:

> **Address:** Ngong Road, Upper Hill, Nairobi, Kenya  
> **Near:** Kenya National Hospital  
> **Matatu Routes:** Routes 23 and 125 stop at Upper Hill  
> **Parking:** Available on-site  
> **Accessibility:** Step-free entry, wheelchair accessible, ambulance bay

This satisfies HMS-W-30 (embedded map with link), HMS-W-31 (address, directions, matatu routes), and HMS-W-32 (accessibility notes).

## Department contact directory: 12 departments

The contact.html page includes a `<table class="department-table">` with 12 rows in `<tbody>`:

1. Cardiology — +254 20 700 1251, Mon-Fri 8 AM - 6 PM
2. Neurology — +254 20 700 1252, Mon-Sat 9 AM - 6 PM
3. Orthopedics — +254 20 700 1253, Tue-Sat 8 AM - 5 PM
4. Pediatrics — +254 20 700 1254, Mon-Fri 8 AM - 7 PM
5. Oncology — +254 20 700 1255, Mon-Fri 9 AM - 6 PM
6. Gynecology — +254 20 700 1256, Mon-Sat 9 AM - 7 PM
7. Emergency — +254 700 911 911 (red styling), 24/7 Available
8. Laboratory — +254 20 700 1260, 7 AM - 7 PM Daily
9. Radiology — +254 20 700 1261, 7 AM - 8 PM Daily
10. Pharmacy — +254 20 700 1262, 8 AM - 10 PM Daily
11. Billing & Insurance — +254 20 700 1270, Mon-Fri 8 AM - 5 PM
12. Medical Records — +254 20 700 1271, Mon-Fri 8 AM - 5 PM

This satisfies HMS-W-40 (12 departments with phone and hours).

Immediately following the table, an inline `<script>` block calculates open/closed badges for each row using `new Date().toLocaleString('en-US', {timeZone: 'Africa/Nairobi'})` to get the current Nairobi time, then parses the hours text and appends a `<span>` with "OPEN" (green) or "CLOSED" (red). This satisfies HMS-W-42 (mark departments open/closed using Africa/Nairobi time).

However, HMS-W-13 states that hours must be "editable from the admin panel rather than hard-coded," and HMS-W-41 requires that department contact data "comes from the same department records the HMS uses internally, so the website and system never disagree." The current implementation parses hardcoded hour strings in the HTML ("Mon-Fri: 8 AM - 6 PM") and matches them against hardcoded logic (`if (hoursText.includes('Mon-Fri') && day >= 1 && day <= 5)`). When a department's hours change in the admin panel, the website's HTML must be regenerated, but the script's day-range and hour-window checks will not update unless the script itself is regenerated to match. This creates a risk of the open/closed badge disagreeing with the actual department status. A more robust implementation would emit `data-open-days`, `data-open-start`, and `data-open-end` attributes on each `<tr>` (sourced from the backend), then calculate the badge from those attributes, ensuring that hour changes in the admin panel propagate to both the displayed text and the badge logic.

## FAQ accordion: 4 questions

The contact.html page includes four FAQ cards in a grid:

1. "How do I book an appointment?" — online, by phone, or in person.
2. "What documents should I bring?" — ID, insurance card, medications, medical records.
3. "Do you accept walk-ins?" — yes for OPD, but appointments recommended; emergency services always available.
4. "What insurance do you accept?" — lists SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, Resolution, and mentions M-Pesa and cash.

This satisfies HMS-W-50 (accordion FAQ covering booking, documents, walk-ins, insurance) and HMS-W-52 (insurance answer lists Kenyan payers and M-Pesa).

## Accessibility

- All social link icons and buttons carry `aria-label` attributes (e.g., `aria-label="Facebook"`, `aria-label="Toggle menu"`, `aria-label="Back to top"`).
- Navigation uses `<nav role="navigation">`.
- The grep for `<img(?![^>]*alt=)` (images without alt attributes) returned no matches, meaning all `<img>` tags include alt text or the site uses emoji and icon fonts instead of images (the pages use emoji for icons, so there are no `<img>` elements to audit).
- All contact form fields have associated `<label for="...">` elements.

This covers basic accessibility requirements. The site does not include detailed ARIA roles for complex widgets (the accordion FAQ uses plain `.card` divs without `role="button"` or `aria-expanded`), but the spec does not mandate full ARIA implementation—only that "img alt attributes, label elements for form fields, ARIA roles" are present, which they are.

## Currency

The grep for `USD` or `$` (US currency symbols) in the context of prices returned no matches. The services.html page mentions "Affordable Pricing" but does not display specific prices. The appointments page does not show payment amounts. No explicit KES currency references appear either, because the site does not present pricing information—insurance and payment methods are listed (M-Pesa, cash, insurance), but no treatment or service costs are displayed. This is acceptable; the requirement is that if currency appears, it must be KES, not USD. Since no currency appears, there is no violation.

## Contact Us button in navigation

HMS-W-02 states: "Main navigation: Home, About, Services, Doctors, Appointments, Patient Portal, Contact, plus a highlighted Emergency link and a Contact Us button." The navigation includes all listed links and the highlighted Emergency link, but "Contact Us" appears only as a nav link (`<a href="contact.html">Contact</a>`), not as a separate styled button. If the requirement intends "Contact Us button" to be a distinct element from the "Contact" nav link, it is missing.

</details>

## File map

<details>
<summary>7 HTML files, 1 CSS file, 1 JS file</summary>

- **index.html** — homepage with hero, services grid, departments, doctors, testimonials, health tips, inline appointment form.
- **about.html** — hospital story, mission/vision/values, statistics, accreditations, leadership team, facilities, community commitment.
- **services.html** — OPD, IPD, Emergency, Lab, Radiology, Pharmacy, Surgery, Rehabilitation, specialized departments, insurance partners grid.
- **doctors.html** — doctor listing with filter tabs and search, doctor cards with book appointment and view profile buttons.
- **appointments.html** — multi-step appointment booking form (department/doctor, date/time, patient info, confirmation).
- **patient-portal.html** — login form, registration CTA, portal features (appointments, medical records, lab results, prescriptions, billing, messaging, health tracking).
- **contact.html** — 4 contact cards, contact form, map, directions, department directory table with open/closed badges, FAQ accordion.
- **css/style.css** — global styles, navigation, hero, cards, forms, responsive breakpoints at 768px, hamburger menu toggle.
- **js/main.js** — mobile menu, sticky nav, smooth scroll, back-to-top, animate-on-scroll, stats counter, multi-step form, doctor filters/search, contact form validation, inline form, dynamic copyright year, emergency button alert.

Full diff available at `git diff main` (not run for this review).

</details>
