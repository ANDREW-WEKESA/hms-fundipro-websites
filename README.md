# HMS & FundiPro Websites

This repository contains two complete websites built for the Kenyan market:

## 🏥 HMS (Hospital Management System)
A comprehensive hospital management website with patient portal, appointment booking, and full Kenyan localization.

**Features:**
- Online appointment booking
- Patient portal access
- Department directory
- Emergency contact information
- Kenyan insurance support (SHA, NHIF, AAR, Jubilee, Britam, CIC, Madison, Resolution)
- M-Pesa payment integration
- Kenya Data Protection Act 2019 compliance

**Location:** `/hms/`

## 🔧 FundiPro (Professional Services Platform)
A marketplace connecting skilled professionals (fundis) with customers across Kenya.

**Features:**
- Service directory (14+ trades)
- Professional profiles with verification
- Location-based search (Kenyan counties)
- Pricing tiers (Free, Pro KES 500, Business KES 1,200)
- M-Pesa payment support
- Customer reviews and ratings

**Location:** `/fundipro/`

## 🚀 Getting Started

### View Locally
```bash
# HMS Website
cd hms
python -m http.server 8001

# FundiPro Website
cd fundipro
python -m http.server 8000
```

Then open:
- HMS: http://localhost:8001
- FundiPro: http://localhost:8000

## 🇰🇪 Kenyan Localization

Both websites are fully localized for Kenya:
- Phone format: +254
- Emergency numbers: 999/112
- Currency: KES
- Locations: Kenyan counties and towns
- Insurance: SHA, NHIF, and local providers
- Payment: M-Pesa integration

## 📋 Project Structure

```
├── hms/
│   ├── index.html
│   ├── about.html
│   ├── services.html
│   ├── doctors.html
│   ├── appointments.html
│   ├── patient-portal.html
│   ├── contact.html
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
├── fundipro/
│   ├── index.html
│   ├── about.html
│   ├── services.html
│   ├── professionals.html
│   ├── pricing.html
│   ├── contact.html
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
└── README.md
```

## 🛠️ Technology Stack

- HTML5
- CSS3 (Responsive design)
- Vanilla JavaScript
- No external dependencies

## 📱 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🔒 Compliance

- Kenya Data Protection Act 2019
- WCAG 2.1 AA accessibility standards
- Mobile-first responsive design
- 44px minimum tap targets

## 📄 License

MIT License

## 👨‍💻 Author

ANDREW WEKESA
