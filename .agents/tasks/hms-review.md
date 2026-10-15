# HMS Website Kenyan Localisation Review

Kenyan localisation implementation verified against requirements specification. All critical placeholder content replaced with Kenya-specific data. Emergency contact numbers, phone formats, addresses, insurance providers, payment methods, and data protection references updated throughout the site.

**Watch for:** No blocking issues remain. Minor enhancement opportunities exist (image alt attributes, explicit currency symbols) but the implementation meets all 14 checklist requirements.

**Verdict**: APPROVED

## High-level view

Emergency numbers (999/112) and the hospital-specific emergency line (+254 700 911 911) appear on every page in the top bar, with the hospital emergency number embedded in the tel link structure. Navigation includes the Emergency link and Contact Us button on all seven pages. Mobile hamburger navigation and 44px minimum tap targets are enforced in CSS below 768px breakpoint. Footer copyright year is set dynamically via JavaScript using `new Date().getFullYear()`. All phone numbers follow the +254 format; no US +1 prefixes remain. US insurance names (Blue Cross, Aetna, Cigna, Medicare, Medicaid, United Healthcare) have been replaced with Kenyan providers (SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, Resolution) across all pages including services.html insurance grid. M-Pesa appears as a payment option in services.html insurance grid and contact.html FAQ. The contact page includes all required elements: four contact cards, complete contact form with honeypot field and consent checkbox, Kenya Data Protection Act reference, Google Maps embed, 12-department directory with open/closed badges, and FAQ accordion with Kenyan insurance list.

<details>
<summary>Issues (0)</summary>

No issues found. All checklist items pass.

</details>

<details>
<summary>Details</summary>

## Kenyan emergency numbers on every page

The top bar on all seven HTML files (index.html, about.html, services.html, doctors.html, appointments.html, patient-portal.html, contact.html) displays "Emergency: 999 / 112 | +254 700 911 911" with a tel link. The hospital-specific emergency number (+254 700 911 911) is embedded in the phone link as `tel:+254700911911`. No 911 references exist outside this context.

## Navigation structure present on all pages

Every page includes the Emergency link styled with class `emergency-btn` and a Contact Us button with classes `btn contact-us-btn`. Both appear in the `nav-links` list on all seven pages.

## Mobile navigation and tap targets

CSS defines `.hamburger` with `display: flex` below the 768px breakpoint, enforcing `min-height: 44px` and `min-width: 44px`. Navigation links, buttons, radio inputs, filter tabs, and time-slot labels all receive 44px minimum dimensions on mobile. The hamburger button includes `aria-label="Toggle menu"`.

## Footer privacy policy and dynamic copyright

All seven pages include footer links to "Privacy Policy (Kenya Data Protection Act 2019)" and "Terms of Service". The copyright year is rendered via `<span id="currentYear">2024</span>` and updated by JavaScript: `document.getElementById('currentYear').textContent = new Date().getFullYear()`.

## Phone numbers fully localised

All phone numbers follow the +254 format. The top bar emergency line is +254 700 911 911. Main reception uses +254 20 700 1234 and +254 733 456 789. Department extensions follow the +254 20 700 12XX pattern. No +1 area codes remain anywhere.

## Address localisation complete

Every page shows "Ngong Road, Upper Hill, Nairobi, Kenya" in the top bar and footer. The contact page provides detailed directions including matatu routes (23 and 125), parking notes, and accessibility information (step-free entry, wheelchair accessible, ambulance bay).

## Insurance providers replaced

US insurance names have been removed. The services.html page lists SHA (Social Health Authority), NHIF, AAR Insurance, Jubilee Insurance, Britam, CIC Insurance, Madison Insurance, and Resolution Health in the insurance grid. The appointments.html insurance dropdown includes the same providers. The contact.html FAQ lists these insurers plus M-Pesa and cash options.

## M-Pesa payment integration

M-Pesa appears in the services.html insurance grid as a payment option and in the contact.html FAQ answer about accepted insurance and payment methods.

## Contact page completeness

contact.html includes four contact cards (Main Reception, Emergency, Email, Hours). The Emergency card uses red styling (`border: 2px solid #dc3545; background: #ffe5e5`) and displays 999, 112, and +254 700 911 911.

The contact form includes all required fields: Full Name, Email Address, Phone Number (placeholder "+254 700 000 000"), Department (dropdown with 14 options including the 12 required departments), Subject (maxlength="150"), and Message (minlength="10" maxlength="2000"). A honeypot field (`name="website"`) with `position: absolute; left: -9999px` is present. The emergency disclaimer states "NOT for emergencies: For medical emergencies call 999, 112, or +254 700 911 911 immediately." The consent checkbox reads "I consent to HMS collecting and processing my personal data in accordance with the Kenya Data Protection Act, 2019" with a link to the privacy policy.

The Google Maps embed is present with `src="https://www.google.com/maps/embed?pb=..."` and an "Open in Google Maps" link below it. The directions section lists the address, nearby landmark (Kenya National Hospital), matatu routes (23, 125), parking, and accessibility notes.

The department directory table has 12 rows (Cardiology, Neurology, Orthopedics, Pediatrics, Oncology, Gynecology, Emergency, Laboratory, Radiology, Pharmacy, Billing & Insurance, Medical Records) with phone extensions, hours, and `data-open-days`, `data-open-start`, `data-open-end` attributes. JavaScript reads these attributes, compares against Africa/Nairobi time, and appends " OPEN" or " CLOSED" badges to the hours cell.

The FAQ accordion has four cards covering appointment booking, required documents, walk-ins, and accepted insurance. The insurance answer lists SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, Resolution, M-Pesa, and cash.

## Contact form validation

The contact form includes inline error messages (`<span class="error-message">`) below each required field. JavaScript in main.js (`initContactForm`) applies client-side validation on blur and submit, adding/removing the `error` class on form groups. Email validation uses `/^[^\s@]+@[^\s@]+\.[^\s@]+$/` and phone validation uses `/^[\d\s\-\+\(\)]{10,}$/`.

## Department table open/closed badges

contact.html includes inline JavaScript that reads `data-open-days`, `data-open-start`, and `data-open-end` from each table row, compares the current hour and day in Africa/Nairobi timezone against these values, and appends an " OPEN" or " CLOSED" badge with green or red styling.

## Currency representation

No explicit currency symbols appear in the HTML (prices are not displayed on the public site). The insurance section on services.html mentions "cashless treatment" and contact.html FAQ mentions "cash" alongside M-Pesa, implying KES without stating it explicitly. This is appropriate for a public hospital site focused on insurance and payment method compatibility rather than pricing.

## Accessibility implementation

Every page includes `role="navigation"` on the nav element. All hamburger buttons, back-to-top buttons, and social links include `aria-label` attributes. Contact form fields use `<label for="...">` elements. No img elements are present (the site uses emoji icons rather than image files), so no alt attributes are required. This is an unusual but valid choice; emoji have inherent semantic meaning and are announced by screen readers.

</details>

<details>
<summary>File map</summary>

- **index.html** — Homepage with hero, stats, departments, doctors, testimonials
- **about.html** — Mission/vision, leadership, facilities, community commitment
- **services.html** — OPD, IPD, emergency, lab, radiology, pharmacy, surgery, rehabilitation; insurance provider grid
- **doctors.html** — Doctor directory with filter tabs, search, 16 doctor cards
- **appointments.html** — Multi-step booking form (department/doctor, date/time, patient info, confirmation)
- **patient-portal.html** — Patient login, registration CTA, portal features, security notes
- **contact.html** — Four contact cards, contact form, map, department directory, FAQ
- **css/style.css** — Global styles, mobile breakpoints, 44px tap targets
- **js/main.js** — Mobile menu, form validation, stats counter, copyright year updater

Full diff: `git diff main -- hms/`

</details>
