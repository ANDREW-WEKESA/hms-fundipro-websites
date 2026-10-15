# HMS - Hospital Management System Website

A complete, modern hospital management system website showcasing comprehensive healthcare services, departments, doctors, and patient portal functionality.

## Features

### Pages
- **Home (index.html)** - Hero section, quick services, departments, featured doctors, testimonials, health tips
- **About (about.html)** - Hospital history, mission/vision/values, statistics, accreditations, leadership team
- **Services (services.html)** - Detailed service offerings across OPD, IPD, Emergency, Lab, Radiology, Pharmacy, Surgery, and Rehabilitation
- **Doctors (doctors.html)** - Complete medical team directory with search and filter by specialty
- **Appointments (appointments.html)** - Multi-step appointment booking form with validation
- **Contact (contact.html)** - Contact information, inquiry form, department directory, map
- **Patient Portal (patient-portal.html)** - Secure login, portal features showcase, HIPAA compliance information

### Design & Styling
- **Medical color scheme** - Blues (#1565c0, #0d47a1, #42a5f5) and whites for professional healthcare aesthetic
- **Responsive design** - Mobile-first approach with breakpoints at 768px and 1024px
- **Google Fonts** - Roboto for body text, Merriweather for headings
- **Accessible** - ARIA labels, semantic HTML5, WCAG-friendly contrast ratios
- **SEO optimized** - Meta tags, semantic structure, proper heading hierarchy

### JavaScript Functionality
- Mobile hamburger menu with smooth toggle
- Sticky navigation with scroll effect
- Multi-step appointment form wizard with validation
- Department filter tabs on doctors page
- Doctor search functionality
- Contact form validation
- Stats counter animation on scroll
- Smooth scrolling for anchor links
- Back to top button
- Animate on scroll effects using IntersectionObserver

### Technical Stack
- Pure HTML5, CSS3, and vanilla JavaScript
- No external frameworks or dependencies
- CSS custom properties for theming
- Semantic HTML structure
- Inline SVG/emoji icons
- Form validation with visual feedback

## File Structure

```
hms/
├── index.html              # Homepage
├── about.html              # About page
├── services.html           # Services page
├── doctors.html            # Doctors directory
├── appointments.html       # Appointment booking
├── contact.html            # Contact page
├── patient-portal.html     # Patient portal login
├── css/
│   └── style.css          # Complete stylesheet
├── js/
│   └── main.js            # All JavaScript functionality
├── images/
│   └── .gitkeep           # Placeholder for images
└── README.md              # This file
```

## Getting Started

1. Open `index.html` in a web browser
2. Navigate through pages using the top navigation
3. All pages are fully functional and linked
4. Forms include validation (client-side demo)

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Features Highlights

### Multi-Step Appointment Form
- 4-step wizard with progress indicators
- Step 1: Select department and doctor
- Step 2: Choose date and time slots
- Step 3: Patient information
- Step 4: Confirmation summary
- Full validation on each step
- Date picker with past date prevention

### Doctor Filtering & Search
- Filter by specialty (8 departments)
- Real-time search by name or specialty
- Smooth animations on filter changes
- 16 featured doctors across specialties

### Responsive Navigation
- Desktop: Horizontal menu with hover effects
- Mobile: Hamburger menu with slide animation
- Sticky header with scroll shadow effect
- Emergency button with pulse animation

### Accessibility
- ARIA labels on all interactive elements
- Semantic HTML5 structure
- Keyboard navigation support
- Screen reader friendly
- High contrast ratios

## Customization

### Colors
Edit CSS custom properties in `css/style.css`:
```css
:root {
    --primary-blue: #1565c0;
    --dark-blue: #0d47a1;
    --light-blue: #42a5f5;
    /* ... more colors */
}
```

### Content
- All text content is in HTML files
- Doctor information in `doctors.html`
- Service details in `services.html`
- Contact information in footer (all pages)

### Images
- Add images to `images/` directory
- Replace emoji icons with actual SVG or image files
- Update avatar placeholders with real photos

## Future Enhancements

- Backend integration for form submissions
- Real database for doctors and appointments
- Patient portal authentication system
- Payment gateway integration
- Live chat functionality
- Telemedicine video consultation
- Email notification system
- SMS appointment reminders

## License

Copyright © 2024 HMS - Hospital Management System. All rights reserved.

## Contact

For questions or support:
- Email: info@hms-hospital.com
- Phone: +1-800-HMS-CARE
- Address: 123 Healthcare Ave, Medical District, City 12345
