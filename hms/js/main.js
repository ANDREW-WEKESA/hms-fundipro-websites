// HMS Main JavaScript File

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all functionality
    initMobileMenu();
    initStickyNav();
    initSmoothScroll();
    initBackToTop();
    initAnimateOnScroll();
    initStatsCounter();

    // Services page — live open/closed badges
    if (document.querySelector('.dept-cards-grid')) {
        initServiceStatusBadges();
    }
    
    // Page-specific functionality
    if (document.querySelector('.multi-step-form')) {
        initMultiStepForm();
    }

    if (document.querySelector('#appointmentBookingForm')) {
        initAppointmentBookingForm();
    }
    
    if (document.querySelector('.filter-tabs')) {
        initDoctorFilters();
    }
    
    if (document.querySelector('.doctor-search')) {
        initDoctorSearch();
    }
    
    if (document.querySelector('.contact-form')) {
        initContactForm();
    }
    
    if (document.querySelector('.inline-appointment-form')) {
        initInlineForm();
    }

    // Patient Portal
    if (document.querySelector('.portal-tabs')) {
        initPatientPortal();
    }

    // Contact page enhancements
    if (document.querySelector('#faqAccordion')) {
        initFaqAccordion();
    }
    if (document.querySelector('#deptDirectoryTable')) {
        initContactDeptStatus();
    }
    if (document.querySelector('#contactForm')) {
        initContactFormToast();
    }
});

// Mobile Menu Toggle
function initMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = navLinks.classList.toggle('active');
            
            // Animate hamburger spans into X
            const spans = hamburger.querySelectorAll('span');
            spans.forEach(span => span.classList.toggle('active'));
            
            // Update ARIA for accessibility
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('active');
                const spans = hamburger.querySelectorAll('span');
                spans.forEach(span => span.classList.remove('active'));
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Close menu when clicking a link
        const links = navLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
                const spans = hamburger.querySelectorAll('span');
                spans.forEach(span => span.classList.remove('active'));
                hamburger.setAttribute('aria-expanded', 'false');
            });
        });
        
        // Set initial aria state
        hamburger.setAttribute('aria-expanded', 'false');
    }
}

// Sticky Navigation with Scroll Effect
function initStickyNav() {
    const nav = document.querySelector('nav');
    
    if (nav) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    }
}

// Smooth Scrolling for Anchor Links
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                
                const target = document.querySelector(href);
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

// Back to Top Button
function initBackToTop() {
    const backToTop = document.querySelector('.back-to-top');
    
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 500) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// Animate Elements on Scroll
function initAnimateOnScroll() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe cards and sections
    const elements = document.querySelectorAll('.card, .doctor-card, .service-item, .feature-card, .article-card, .testimonial-card');
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Stats Counter Animation
function initStatsCounter() {
    const stats = document.querySelectorAll('.stat-number');
    
    if (stats.length > 0) {
        const observerOptions = {
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    entry.target.classList.add('counted');
                    animateCounter(entry.target);
                }
            });
        }, observerOptions);
        
        stats.forEach(stat => observer.observe(stat));
    }
}

function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target') || element.textContent, 10);
    const duration = 2000;
    const steps = duration / 16;
    const step = target / steps;
    let current = 0;

    element.textContent = '0+';

    const timer = setInterval(function() {
        current += step;
        if (current >= target) {
            element.textContent = target.toLocaleString() + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString() + '+';
        }
    }, 16);
}

// Multi-Step Appointment Form
function initMultiStepForm() {
    const form = document.querySelector('.multi-step-form');
    if (!form) return;
    
    let currentStep = 1;
    const totalSteps = 4;
    
    const nextButtons = form.querySelectorAll('.btn-next');
    const backButtons = form.querySelectorAll('.btn-back');
    const submitButton = form.querySelector('.btn-submit');
    
    // Next button handlers
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        });
    });
    
    // Back button handlers
    backButtons.forEach(button => {
        button.addEventListener('click', function() {
            goToStep(currentStep - 1);
        });
    });
    
    // Submit handler
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateStep(currentStep)) {
                submitAppointment();
            }
        });
    }
    
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        
        // Hide current step
        const currentContent = form.querySelector(`.step-content[data-step="${currentStep}"]`);
        const currentIndicator = form.querySelector(`.form-step[data-step="${currentStep}"]`);
        
        if (currentContent) currentContent.classList.remove('active');
        if (currentIndicator) currentIndicator.classList.remove('active');
        
        // Mark as completed if moving forward
        if (step > currentStep && currentIndicator) {
            currentIndicator.classList.add('completed');
        }
        
        // Show new step
        currentStep = step;
        const newContent = form.querySelector(`.step-content[data-step="${currentStep}"]`);
        const newIndicator = form.querySelector(`.form-step[data-step="${currentStep}"]`);
        
        if (newContent) newContent.classList.add('active');
        if (newIndicator) newIndicator.classList.add('active');
        
        // Scroll to form
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    function validateStep(step) {
        const stepContent = form.querySelector(`.step-content[data-step="${step}"]`);
        if (!stepContent) return true;
        
        const inputs = stepContent.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            const formGroup = input.closest('.form-group');
            
            if (!input.value.trim()) {
                isValid = false;
                if (formGroup) {
                    formGroup.classList.add('error');
                }
            } else {
                if (formGroup) {
                    formGroup.classList.remove('error');
                }
                
                // Email validation
                if (input.type === 'email' && !isValidEmail(input.value)) {
                    isValid = false;
                    if (formGroup) {
                        formGroup.classList.add('error');
                    }
                }
                
                // Phone validation
                if (input.type === 'tel' && !isValidPhone(input.value)) {
                    isValid = false;
                    if (formGroup) {
                        formGroup.classList.add('error');
                    }
                }
            }
        });
        
        // Check time slots for step 2
        if (step === 2) {
            const timeSlot = stepContent.querySelector('input[name="timeSlot"]:checked');
            if (!timeSlot) {
                isValid = false;
                alert('Please select a time slot');
            }
        }
        
        return isValid;
    }
    
    function submitAppointment() {
        // Collect form data
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        console.log('Appointment Data:', data);
        
        // Show success message
        alert('Appointment booked successfully! You will receive a confirmation email shortly.');
        
        // Reset form
        form.reset();
        goToStep(1);
        
        // Remove completed class from indicators
        const indicators = form.querySelectorAll('.form-step');
        indicators.forEach(indicator => {
            indicator.classList.remove('completed');
        });
    }
    
    // Date input validation - prevent past dates
    const dateInput = form.querySelector('input[type="date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
}

// Doctor Filter Tabs + Search (shared state)
// Both filters and search cooperate: a card is visible only when it matches
// the active department filter AND the current search query.

function initDoctorFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            applyDoctorVisibility();
        });
    });
}

function initDoctorSearch() {
    const searchInput = document.querySelector('.doctor-search');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyDoctorVisibility();
        });
    }
}

/**
 * applyDoctorVisibility — show/hide doctor cards based on the currently
 * active filter tab and the current search query.  Shows a "no results"
 * message when every card is hidden.
 */
function applyDoctorVisibility() {
    const activeTab  = document.querySelector('.filter-tab.active');
    const searchInput = document.querySelector('.doctor-search');
    const noResults  = document.getElementById('doctorsNoResults');

    const filter = activeTab ? activeTab.getAttribute('data-filter') : 'all';
    const query  = searchInput ? searchInput.value.toLowerCase().trim() : '';

    const doctorCards = document.querySelectorAll('.doctor-card');
    let visibleCount = 0;

    doctorCards.forEach(card => {
        const specialty = card.getAttribute('data-specialty') || '';
        const name      = card.querySelector('.doctor-name')
            ? card.querySelector('.doctor-name').textContent.toLowerCase()
            : '';
        const specialtyText = card.querySelector('.doctor-specialty')
            ? card.querySelector('.doctor-specialty').textContent.toLowerCase()
            : '';
        const qualifications = card.querySelector('.doctor-qualifications')
            ? card.querySelector('.doctor-qualifications').textContent.toLowerCase()
            : '';

        const matchesDept   = filter === 'all' || specialty === filter;
        const matchesSearch = !query ||
            name.includes(query) ||
            specialtyText.includes(query) ||
            qualifications.includes(query);

        if (matchesDept && matchesSearch) {
            card.style.display = 'block';
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 10);
            visibleCount++;
        } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.display = 'none';
            }, 300);
        }
    });

    if (noResults) {
        noResults.hidden = visibleCount > 0;
    }
}

// Contact Form Validation
function initContactForm() {
    const form = document.querySelector('.contact-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                const formGroup = input.closest('.form-group');
                
                if (!input.value.trim()) {
                    isValid = false;
                    if (formGroup) {
                        formGroup.classList.add('error');
                    }
                } else {
                    if (formGroup) {
                        formGroup.classList.remove('error');
                    }
                    
                    // Email validation
                    if (input.type === 'email' && !isValidEmail(input.value)) {
                        isValid = false;
                        if (formGroup) {
                            formGroup.classList.add('error');
                        }
                    }
                    
                    // Phone validation
                    if (input.type === 'tel' && !isValidPhone(input.value)) {
                        isValid = false;
                        if (formGroup) {
                            formGroup.classList.add('error');
                        }
                    }
                }
            });
            
            if (isValid) {
                // Collect form data
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                
                console.log('Contact Form Data:', data);
                
                // Show success message
                alert('Thank you for contacting us! We will get back to you shortly.');
                
                // Reset form
                form.reset();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                const formGroup = this.closest('.form-group');
                
                if (this.hasAttribute('required') && !this.value.trim()) {
                    if (formGroup) {
                        formGroup.classList.add('error');
                    }
                } else {
                    if (formGroup) {
                        formGroup.classList.remove('error');
                    }
                }
            });
            
            input.addEventListener('input', function() {
                const formGroup = this.closest('.form-group');
                if (this.value.trim() && formGroup) {
                    formGroup.classList.remove('error');
                }
            });
        });
    }
}

// Inline Appointment Form (Homepage)
function initInlineForm() {
    const form = document.querySelector('.inline-appointment-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Basic validation
            if (!data.name || !data.phone || !data.department || !data.date) {
                alert('Please fill in all fields');
                return;
            }
            
            console.log('Quick Appointment Data:', data);
            
            // Show success message
            alert('Appointment request received! We will call you shortly to confirm.');
            
            // Reset form
            form.reset();
        });
        
        // Date validation
        const dateInput = form.querySelector('input[type="date"]');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }
    }
}

// Validation Helper Functions
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[\d\s\-\+\(\)]{10,}$/;
    return re.test(phone);
}

// Set Active Navigation Link
function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
}

// Call on load
setActiveNavLink();

// Emergency Button Alert
const emergencyBtn = document.querySelector('.emergency-btn');
if (emergencyBtn) {
    emergencyBtn.addEventListener('click', function(e) {
        if (this.getAttribute('href') === '#emergency') {
            e.preventDefault();
            alert('Emergency Hotline: 999 / 112 / +254 700 911 911\n\nFor life-threatening emergencies, call immediately.');
        }
    });
}

// Set dynamic copyright year
document.addEventListener('DOMContentLoaded', function() {
    const yearSpan = document.getElementById('currentYear');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
});

// ─── Appointment Booking Form ────────────────────────────────────────────────

/**
 * Department → Doctor mapping.
 * Each entry: { value, label }
 */
var DEPT_DOCTORS = {
    cardiology: [
        { value: 'dr-john-davis',    label: 'Dr. John Davis — Cardiologist' },
        { value: 'dr-anna-martinez', label: 'Dr. Anna Martinez — Interventional Cardiologist' }
    ],
    neurology: [
        { value: 'dr-sarah-wilson',  label: 'Dr. Sarah Wilson — Neurologist' },
        { value: 'dr-rajesh-kumar',  label: 'Dr. Rajesh Kumar — Neurosurgeon' }
    ],
    orthopedics: [
        { value: 'dr-michael-patel',   label: 'Dr. Michael Patel — Orthopedic Surgeon' },
        { value: 'dr-lisa-thompson',   label: 'Dr. Lisa Thompson — Sports Medicine' }
    ],
    pediatrics: [
        { value: 'dr-emily-lee',   label: 'Dr. Emily Lee — Pediatrician' },
        { value: 'dr-james-brown', label: 'Dr. James Brown — Pediatric Surgeon' }
    ],
    general: [
        { value: 'dr-patricia-waweru', label: 'Dr. Patricia Waweru — General Practitioner' },
        { value: 'dr-kevin-omondi',    label: 'Dr. Kevin Omondi — General Practitioner' }
    ],
    maternity: [
        { value: 'dr-grace-mwangi',  label: 'Dr. Grace Mwangi — Obstetrician/Gynaecologist' },
        { value: 'dr-esther-kamau',  label: 'Dr. Esther Kamau — Midwife Specialist' }
    ],
    emergency: [
        { value: 'dr-tom-otieno',    label: 'Dr. Tom Otieno — Emergency Medicine' },
        { value: 'dr-nancy-njoroge', label: 'Dr. Nancy Njoroge — Trauma Surgeon' }
    ],
    radiology: [
        { value: 'dr-ben-kariuki',  label: 'Dr. Ben Kariuki — Diagnostic Radiologist' },
        { value: 'dr-alice-mutua',  label: 'Dr. Alice Mutua — Interventional Radiologist' }
    ],
    laboratory: [
        { value: 'dr-samuel-gitau',  label: 'Dr. Samuel Gitau — Clinical Pathologist' },
        { value: 'dr-diana-chebet',  label: 'Dr. Diana Chebet — Haematologist' }
    ],
    pharmacy: [
        { value: 'pharm-joseph-ndung\'u', label: 'Pharm. Joseph Ndung\'u — Clinical Pharmacist' },
        { value: 'pharm-rose-wanjiku',    label: 'Pharm. Rose Wanjiku — Clinical Pharmacist' }
    ]
};

// ─── Services Page — Live Open/Closed Status Badges ─────────────────────────

/**
 * Returns the current time in Africa/Nairobi (UTC+3) as { day, hour, minute }.
 * day: 0 = Sunday … 6 = Saturday (same as Date.getDay()).
 */
function getNairobiTime() {
    var now = new Date();
    // Nairobi is UTC+3 with no DST
    var utcMs = now.getTime() + now.getTimezoneOffset() * 60000;
    var nairobiMs = utcMs + 3 * 3600000;
    var n = new Date(nairobiMs);
    return { day: n.getDay(), hour: n.getHours(), minute: n.getMinutes() };
}

/**
 * Returns true if `time` falls within [openH:openM, closeH:closeM).
 */
function isWithinHours(time, openH, openM, closeH, closeM) {
    var nowMins  = time.hour * 60 + time.minute;
    var openMins = openH   * 60 + openM;
    var closeMins = closeH * 60 + closeM;
    return nowMins >= openMins && nowMins < closeMins;
}

/**
 * Determines whether a department is currently open.
 * Returns true (open) or false (closed).
 */
function isDeptOpen(dept) {
    var t = getNairobiTime();
    var day = t.day; // 0=Sun, 1=Mon … 6=Sat

    switch (dept) {
        // Always open
        case 'emergency':
        case 'maternity':
        case 'icu':
            return true;

        // OPD: Mon–Fri 08:00–17:00, Sat 08:00–13:00
        case 'opd':
            if (day >= 1 && day <= 5) return isWithinHours(t, 8, 0, 17, 0);
            if (day === 6)            return isWithinHours(t, 8, 0, 13, 0);
            return false;

        // Pharmacy: Mon–Sat 07:00–21:00
        case 'pharmacy':
            if (day >= 1 && day <= 6) return isWithinHours(t, 7, 0, 21, 0);
            return false;

        // Laboratory: Mon–Fri 07:00–17:00, Sat 08:00–13:00
        case 'laboratory':
            if (day >= 1 && day <= 5) return isWithinHours(t, 7, 0, 17, 0);
            if (day === 6)            return isWithinHours(t, 8, 0, 13, 0);
            return false;

        // Radiology: Mon–Fri 08:00–17:00
        case 'radiology':
            if (day >= 1 && day <= 5) return isWithinHours(t, 8, 0, 17, 0);
            return false;

        // Surgery: Mon–Fri 08:00–17:00 (by appointment; emergency surgery 24/7)
        case 'surgery':
            if (day >= 1 && day <= 5) return isWithinHours(t, 8, 0, 17, 0);
            return false;

        default:
            return false;
    }
}

/**
 * Stamps each department card's badge with Open / Closed.
 * Refreshes every minute so the badge stays accurate without a page reload.
 */
function initServiceStatusBadges() {
    var depts = ['opd', 'emergency', 'maternity', 'pharmacy', 'laboratory', 'radiology', 'icu', 'surgery'];

    function updateBadges() {
        depts.forEach(function(dept) {
            var badge = document.getElementById('status-' + dept);
            if (!badge) return;

            var open = isDeptOpen(dept);
            badge.textContent      = open ? '● Open Now' : '● Closed';
            badge.className        = 'dept-status-badge ' + (open ? 'dept-status-badge--open' : 'dept-status-badge--closed');
            badge.setAttribute('aria-label', dept + ' is currently ' + (open ? 'open' : 'closed'));
        });
    }

    updateBadges();
    setInterval(updateBadges, 60000); // refresh every minute
}

function initAppointmentBookingForm() {
    var form       = document.getElementById('appointmentBookingForm');
    var deptSelect = document.getElementById('apptDepartment');
    var docSelect  = document.getElementById('apptDoctor');
    var dateInput  = document.getElementById('apptDate');
    var banner     = document.getElementById('apptSuccessBanner');

    if (!form) return;

    // ── Set min date to today ──────────────────────────────────────────────
    var today = new Date();
    var yyyy  = today.getFullYear();
    var mm    = String(today.getMonth() + 1).padStart(2, '0');
    var dd    = String(today.getDate()).padStart(2, '0');
    dateInput.setAttribute('min', yyyy + '-' + mm + '-' + dd);

    // ── Populate doctor dropdown when department changes ───────────────────
    deptSelect.addEventListener('change', function() {
        var dept    = this.value;
        var doctors = DEPT_DOCTORS[dept] || [];

        // Reset doctor select
        docSelect.innerHTML = '';
        docSelect.disabled  = true;

        var placeholder = document.createElement('option');
        placeholder.value       = '';
        placeholder.textContent = doctors.length ? '— Select a doctor —' : '— Select a department first —';
        docSelect.appendChild(placeholder);

        doctors.forEach(function(doc) {
            var opt       = document.createElement('option');
            opt.value     = doc.value;
            opt.textContent = doc.label;
            docSelect.appendChild(opt);
        });

        if (doctors.length) {
            docSelect.disabled = false;
        }

        clearFieldError('apptDepartment');
        clearFieldError('apptDoctor');
    });

    // ── Real-time Sunday check on date input ──────────────────────────────
    dateInput.addEventListener('change', function() {
        validateDateField();
    });

    // ── Clear error on interaction ─────────────────────────────────────────
    ['apptPatientName', 'apptPhone', 'apptEmail', 'apptDoctor',
     'apptTime', 'apptReason'].forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() { clearFieldError(id); });
        el.addEventListener('change', function() { clearFieldError(id); });
    });

    // ── Form submit ────────────────────────────────────────────────────────
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (validateBookingForm()) {
            // Show success banner, hide form
            form.hidden        = true;
            banner.hidden      = false;
            banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // ── Helpers ────────────────────────────────────────────────────────────

    function validateBookingForm() {
        var valid = true;

        // Patient Name
        var name = document.getElementById('apptPatientName').value.trim();
        if (!name) {
            setFieldError('apptPatientName'); valid = false;
        } else {
            clearFieldError('apptPatientName');
        }

        // Phone — must start with +254 followed by exactly 9 digits
        var phone    = document.getElementById('apptPhone').value.trim();
        var phoneRe  = /^\+254[0-9]{9}$/;
        if (!phone || !phoneRe.test(phone)) {
            setFieldError('apptPhone'); valid = false;
        } else {
            clearFieldError('apptPhone');
        }

        // Email — optional, but validate format if provided
        var email   = document.getElementById('apptEmail').value.trim();
        var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRe.test(email)) {
            setFieldError('apptEmail'); valid = false;
        } else {
            clearFieldError('apptEmail');
        }

        // Department
        if (!deptSelect.value) {
            setFieldError('apptDepartment'); valid = false;
        } else {
            clearFieldError('apptDepartment');
        }

        // Doctor
        if (!docSelect.value) {
            setFieldError('apptDoctor'); valid = false;
        } else {
            clearFieldError('apptDoctor');
        }

        // Date
        if (!validateDateField()) {
            valid = false;
        }

        // Time
        if (!document.getElementById('apptTime').value) {
            setFieldError('apptTime'); valid = false;
        } else {
            clearFieldError('apptTime');
        }

        // Reason
        var reason = document.getElementById('apptReason').value.trim();
        if (!reason) {
            setFieldError('apptReason'); valid = false;
        } else {
            clearFieldError('apptReason');
        }

        return valid;
    }

    /**
     * Validates the date field.
     * Returns true if valid, false otherwise (and sets inline error).
     */
    function validateDateField() {
        var val = dateInput.value;
        var dateGroup = dateInput.closest('.form-group');

        if (!val) {
            setFieldError('apptDate');
            updateDateErrorMessage('Please select an appointment date.');
            return false;
        }

        // Parse date as local (YYYY-MM-DD) to avoid timezone shifts
        var parts = val.split('-');
        var selected = new Date(
            parseInt(parts[0], 10),
            parseInt(parts[1], 10) - 1,
            parseInt(parts[2], 10)
        );

        // No past dates
        var todayMidnight = new Date();
        todayMidnight.setHours(0, 0, 0, 0);
        if (selected < todayMidnight) {
            setFieldError('apptDate');
            updateDateErrorMessage('Please select a date from today onwards.');
            return false;
        }

        // No Sundays (day === 0)
        if (selected.getDay() === 0) {
            setFieldError('apptDate');
            updateDateErrorMessage('We are closed on Sundays. Please choose another day.');
            return false;
        }

        clearFieldError('apptDate');
        return true;
    }

    function updateDateErrorMessage(msg) {
        var errEl = document.getElementById('apptDateError');
        if (errEl) errEl.textContent = msg;
    }

    function setFieldError(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var group = el.closest('.form-group');
        if (group) group.classList.add('error');
    }

    function clearFieldError(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var group = el.closest('.form-group');
        if (group) group.classList.remove('error');
    }
}

// ─── Patient Portal ───────────────────────────────────────────────────────────
//
// Handles:
//  • Tab switching (Login / Register)
//  • Send OTP flow (shows OTP field, pre-fills demo value)
//  • OTP validation and dashboard reveal
//  • Register form submission
//  • Dashboard card navigation
//  • M-Pesa pay button
//  • Logout
// ─────────────────────────────────────────────────────────────────────────────

function initPatientPortal() {

    // ── Element references ──────────────────────────────────────────────────
    var tabLogin        = document.getElementById('tab-login');
    var tabRegister     = document.getElementById('tab-register');
    var panelLogin      = document.getElementById('panel-login');
    var panelRegister   = document.getElementById('panel-register');

    var sendOtpBtn      = document.getElementById('sendOtpBtn');
    var otpGroup        = document.getElementById('otpGroup');
    var otpInput        = document.getElementById('otpInput');
    var otpError        = document.getElementById('otpError');
    var loginBtn        = document.getElementById('loginBtn');
    var loginError      = document.getElementById('loginError');
    var loginIdentifier = document.getElementById('loginIdentifier');
    var identifierError = document.getElementById('loginIdentifierError');

    var registerForm    = document.getElementById('registerForm');
    var registerSuccess = document.getElementById('registerSuccess');

    var authSection     = document.getElementById('portalAuthSection');
    var dashboard       = document.getElementById('patientDashboard');
    var dashWelcome     = document.getElementById('dashboardWelcome');
    var logoutBtn       = document.getElementById('logoutBtn');

    var DEMO_OTP = '123456';

    // ── Tab switching ───────────────────────────────────────────────────────

    function showTab(targetTab) {
        var isLogin = (targetTab === 'login');

        tabLogin.classList.toggle('portal-tab--active', isLogin);
        tabLogin.setAttribute('aria-selected', isLogin ? 'true' : 'false');

        tabRegister.classList.toggle('portal-tab--active', !isLogin);
        tabRegister.setAttribute('aria-selected', isLogin ? 'false' : 'true');

        panelLogin.hidden    = !isLogin;
        panelRegister.hidden = isLogin;
    }

    tabLogin.addEventListener('click', function() { showTab('login'); });
    tabRegister.addEventListener('click', function() { showTab('register'); });

    // ── OTP flow ────────────────────────────────────────────────────────────

    sendOtpBtn.addEventListener('click', function() {
        var val = loginIdentifier.value.trim();

        if (!val) {
            loginIdentifier.closest('.form-group').classList.add('error');
            return;
        }
        loginIdentifier.closest('.form-group').classList.remove('error');

        // Show OTP field and pre-fill with demo OTP
        otpGroup.hidden = false;
        otpInput.value  = DEMO_OTP;
        loginBtn.hidden = false;

        // Scroll OTP field into view smoothly
        otpGroup.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        otpInput.focus();

        sendOtpBtn.textContent = 'Resend OTP';
    });

    // Clear OTP error on typing
    otpInput.addEventListener('input', function() {
        otpError.hidden = true;
        loginError.hidden = true;
        otpInput.closest('.form-group').classList.remove('error');
    });

    // ── Login validation ────────────────────────────────────────────────────

    loginBtn.addEventListener('click', function() {
        var code = otpInput.value.trim();

        if (code !== DEMO_OTP) {
            otpInput.closest('.form-group').classList.add('error');
            otpError.removeAttribute('hidden');
            loginError.removeAttribute('hidden');
            return;
        }

        // Successful login — determine display name
        var identifier = loginIdentifier.value.trim();
        var displayName = deriveDisplayName(identifier);

        // Populate welcome message
        if (dashWelcome) {
            dashWelcome.textContent = 'Welcome back, ' + displayName;
        }

        // Hide auth section, show dashboard
        authSection.hidden = true;
        dashboard.hidden   = false;
        dashboard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    /**
     * Derive a friendly display name from the login identifier.
     * If it looks like an email, use the part before @.
     * Otherwise return the raw value.
     */
    function deriveDisplayName(identifier) {
        if (identifier.indexOf('@') !== -1) {
            return identifier.split('@')[0];
        }
        return identifier;
    }

    // ── Register form ───────────────────────────────────────────────────────

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateRegisterForm()) return;

            // Show success banner, hide form fields
            registerForm.hidden      = true;
            registerSuccess.hidden   = false;
            registerSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    function validateRegisterForm() {
        var valid = true;
        var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var phoneRe = /^\+254[0-9]{9}$/;

        var fields = [
            { id: 'regFullName',        check: function(v) { return v.length > 0; } },
            { id: 'regNationalId',      check: function(v) { return v.length > 0; } },
            { id: 'regPhone',           check: function(v) { return phoneRe.test(v); } },
            { id: 'regEmail',           check: function(v) { return emailRe.test(v); } },
            { id: 'regDob',             check: function(v) { return v.length > 0; } },
            { id: 'regGender',          check: function(v) { return v.length > 0; } },
            { id: 'regPassword',        check: function(v) { return v.length >= 8; } }
        ];

        fields.forEach(function(f) {
            var el = document.getElementById(f.id);
            if (!el) return;
            var group = el.closest('.form-group');
            if (!f.check(el.value.trim())) {
                if (group) group.classList.add('error');
                valid = false;
            } else {
                if (group) group.classList.remove('error');
            }
        });

        // Confirm password check
        var pw  = document.getElementById('regPassword');
        var cpw = document.getElementById('regConfirmPassword');
        if (pw && cpw) {
            var cpwGroup = cpw.closest('.form-group');
            if (!cpw.value || cpw.value !== pw.value) {
                if (cpwGroup) cpwGroup.classList.add('error');
                var errEl = document.getElementById('regConfirmError');
                if (errEl) errEl.textContent = cpw.value
                    ? 'Passwords do not match.'
                    : 'Please confirm your password.';
                valid = false;
            } else {
                if (cpwGroup) cpwGroup.classList.remove('error');
            }
        }

        return valid;
    }

    // Clear errors on input for register fields
    var regInputs = document.querySelectorAll('#registerForm input, #registerForm select');
    regInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var group = this.closest('.form-group');
            if (group) group.classList.remove('error');
        });
    });

    // ── Dashboard card navigation ───────────────────────────────────────────
    // Each card scrolls to its target section.

    var dashCards = document.querySelectorAll('.dashboard-card');
    dashCards.forEach(function(card) {
        card.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var target   = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── M-Pesa Pay buttons ──────────────────────────────────────────────────

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.portal-mpesa-btn');
        if (!btn) return;

        var amount  = btn.getAttribute('data-amount');
        var invoice = btn.getAttribute('data-invoice');

        alert(
            'M-Pesa Payment\n\n' +
            'Dial *334# on your Safaricom line to pay ' +
            'KES ' + formatAmount(amount) + ' for invoice ' + invoice + '.\n\n' +
            'Follow the on-screen prompts and enter your M-Pesa PIN to complete the payment.'
        );
    });

    /** Format a numeric string as a comma-separated number. */
    function formatAmount(val) {
        var num = parseInt(val, 10);
        if (isNaN(num)) return val;
        return num.toLocaleString();
    }

    // ── Logout ──────────────────────────────────────────────────────────────

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            // Hide dashboard, show auth section
            dashboard.hidden   = true;
            authSection.hidden = false;

            // Reset login form state
            loginIdentifier.value = '';
            otpInput.value        = '';
            otpGroup.hidden       = true;
            loginBtn.hidden       = true;
            loginError.hidden     = true;
            sendOtpBtn.textContent = 'Send OTP';

            loginIdentifier.closest('.form-group').classList.remove('error');

            authSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
}

// ─── Contact Page — FAQ Accordion ────────────────────────────────────────────

/**
 * Accessible FAQ accordion.
 * Each item has a <button.faq-question aria-expanded> and a
 * <div.faq-answer hidden> panel.  Clicking a button toggles its
 * own panel; all others collapse (single-open behaviour).
 */
function initFaqAccordion() {
    var accordion = document.getElementById('faqAccordion');
    if (!accordion) return;

    var buttons = accordion.querySelectorAll('.faq-question');

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var isExpanded = this.getAttribute('aria-expanded') === 'true';
            var panelId    = this.getAttribute('aria-controls');
            var panel      = document.getElementById(panelId);

            // Collapse all
            buttons.forEach(function(otherBtn) {
                var otherId    = otherBtn.getAttribute('aria-controls');
                var otherPanel = document.getElementById(otherId);
                otherBtn.setAttribute('aria-expanded', 'false');
                if (otherPanel) otherPanel.hidden = true;
            });

            // If this one was closed, open it now
            if (!isExpanded) {
                this.setAttribute('aria-expanded', 'true');
                if (panel) panel.hidden = false;
            }
        });
    });
}

// ─── Contact Page — Live Department Status ───────────────────────────────────

/**
 * Returns the current local time as Nairobi time (Africa/Nairobi = UTC+3,
 * no DST) as { day, hour, minute }.
 * day: 0 = Sunday … 6 = Saturday.
 */
function getContactNairobiTime() {
    var now     = new Date();
    var utcMs   = now.getTime() + now.getTimezoneOffset() * 60000;
    var nairobi = new Date(utcMs + 3 * 3600000);
    return {
        day:    nairobi.getDay(),
        hour:   nairobi.getHours(),
        minute: nairobi.getMinutes()
    };
}

/**
 * Returns true when t.hour:t.minute falls within [openH:00, closeH:00).
 */
function contactIsWithinHours(t, openH, closeH) {
    var now   = t.hour * 60 + t.minute;
    var open  = openH  * 60;
    var close = closeH * 60;
    return now >= open && now < close;
}

/**
 * Determines if a department is currently open.
 * data-dept values map to opening schedules:
 *   always-open      — 24/7
 *   standard-weekday — Mon–Fri 08:00–17:00
 *   reception        — Mon–Fri 08:00–17:00 (same as weekday)
 *   pharmacy         — Mon–Sat 08:00–17:00
 *   lab              — Mon–Fri 08:00–17:00, Sat 08:00–13:00
 */
function contactIsDeptOpen(dept) {
    var t   = getContactNairobiTime();
    var day = t.day; // 0=Sun, 1=Mon … 6=Sat

    switch (dept) {
        case 'always-open':
            return true;

        case 'standard-weekday':
        case 'reception':
            return (day >= 1 && day <= 5) && contactIsWithinHours(t, 8, 17);

        case 'pharmacy':
            return (day >= 1 && day <= 6) && contactIsWithinHours(t, 8, 17);

        case 'lab':
            if (day >= 1 && day <= 5) return contactIsWithinHours(t, 8, 17);
            if (day === 6)            return contactIsWithinHours(t, 8, 13);
            return false;

        default:
            return false;
    }
}

/**
 * Stamps each .contact-status-badge with an 'Open Now' (green) or
 * 'Closed' (red) badge.  Refreshes every 60 seconds.
 */
function initContactDeptStatus() {
    function updateBadges() {
        document.querySelectorAll('.contact-status-badge').forEach(function(badge) {
            var dept = badge.getAttribute('data-dept');
            var open = contactIsDeptOpen(dept);

            badge.textContent = open ? '● Open Now' : '● Closed';
            badge.className   = 'contact-status-badge ' +
                (open ? 'contact-status-badge--open' : 'contact-status-badge--closed');
            badge.setAttribute('aria-label',
                badge.closest('tr').querySelector('td strong').textContent +
                ' is currently ' + (open ? 'open' : 'closed'));
        });
    }

    updateBadges();
    setInterval(updateBadges, 60000);
}

// ─── Contact Page — Form Submission Toast ────────────────────────────────────

/**
 * Handles the #contactForm submission:
 * validates required fields, then shows a success toast.
 */
function initContactFormToast() {
    var form  = document.getElementById('contactForm');
    var toast = document.getElementById('contactToast');
    if (!form || !toast) return;

    var toastTimer = null;

    // Show toast helper
    function showToast(message, isError) {
        clearTimeout(toastTimer);
        toast.textContent        = message;
        toast.style.background   = isError ? 'var(--emergency-red)' : 'var(--success-green)';
        toast.classList.add('contact-toast--visible');

        toastTimer = setTimeout(function() {
            toast.classList.remove('contact-toast--visible');
        }, 5000);
    }

    // Field-level helpers
    function setError(field) {
        var group = field.closest('.form-group');
        if (group) group.classList.add('error');
    }

    function clearError(field) {
        var group = field.closest('.form-group');
        if (group) group.classList.remove('error');
    }

    // Real-time clear on input
    form.querySelectorAll('input, select, textarea').forEach(function(el) {
        el.addEventListener('input', function()  { clearError(this); });
        el.addEventListener('change', function() { clearError(this); });
    });

    // Validate and submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var phoneRe = /^[\d\s\-\+\(\)]{7,}$/;
        var valid   = true;

        // Name
        var name = form.querySelector('#contactName');
        if (!name || !name.value.trim()) { setError(name); valid = false; }
        else clearError(name);

        // Phone
        var phone = form.querySelector('#contactPhone');
        if (!phone || !phone.value.trim() || !phoneRe.test(phone.value.trim())) {
            setError(phone); valid = false;
        } else clearError(phone);

        // Email
        var email = form.querySelector('#contactEmail');
        if (!email || !emailRe.test(email.value.trim())) {
            setError(email); valid = false;
        } else clearError(email);

        // Subject
        var subject = form.querySelector('#contactSubject');
        if (!subject || !subject.value) { setError(subject); valid = false; }
        else clearError(subject);

        // Message
        var message = form.querySelector('#contactMessage');
        if (!message || message.value.trim().length < 10) {
            setError(message); valid = false;
        } else clearError(message);

        if (!valid) {
            // Focus first error field for accessibility
            var firstErr = form.querySelector('.form-group.error input, .form-group.error select, .form-group.error textarea');
            if (firstErr) firstErr.focus();
            return;
        }

        // Success — reset and toast
        form.reset();
        showToast('✔ Thank you! Your message has been sent. We will get back to you shortly.', false);
    });
}
