// HMS Main JavaScript File

// ── Page-load spinner — shown immediately, hidden once DOM is ready ───────────
(function() {
    var spinner = document.createElement('div');
    spinner.className = 'page-load-spinner';
    spinner.setAttribute('role', 'status');
    spinner.setAttribute('aria-label', 'Loading…');
    var dot = document.createElement('div');
    dot.className = 'spinner';
    spinner.appendChild(dot);
    // Insert as first child so it renders before anything else
    if (document.body) {
        document.body.insertBefore(spinner, document.body.firstChild);
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            document.body.insertBefore(spinner, document.body.firstChild);
        });
    }

    function hideSpinner() {
        spinner.classList.add('hidden');
        setTimeout(function() { spinner.remove(); }, 450);
    }

    if (document.readyState === 'complete') {
        hideSpinner();
    } else {
        window.addEventListener('load', hideSpinner);
        // Fallback: hide after 2.5 s even if load event doesn't fire
        setTimeout(hideSpinner, 2500);
    }
}());

// ── Dark mode — apply saved preference before paint to avoid flash ────────────
(function() {
    var saved = localStorage.getItem('hms-theme');
    if (saved === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}());

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {

    // 1. Page transition: fade-in body on load
    document.body.classList.add('page-transition');

    // Initialize all functionality
    initMobileMenu();
    initStickyNav();
    initSmoothScroll();
    initBackToTop();
    initAnimateOnScroll();
    initStatsCounter();
    initEmergencyFAB();

    // Dark mode toggle
    initDarkMode();

    // 3. Button ripple effect
    initButtonRipple();

    // 4. Loading spinner on form submit
    initFormSpinner();

    // 5. Scroll reveal via IntersectionObserver
    initScrollReveal();

    // Testimonials carousel (homepage)
    if (document.getElementById('testimonialsCarousel')) {
        initTestimonialsCarousel();
    }

    // Site-wide search (all pages)
    if (document.getElementById('siteSearchToggle')) {
        initSiteSearch();
    }

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
            if (window.scrollY > 300) {
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

// ─── Commit 9: Button Ripple, Form Spinner, Scroll Reveal ───────────────────

// 3. Button ripple effect
function initButtonRipple() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn');
        if (!btn) return;

        var ripple = document.createElement('span');
        ripple.className = 'ripple';

        var rect = btn.getBoundingClientRect();
        var size = Math.max(rect.width, rect.height);
        ripple.style.width  = size + 'px';
        ripple.style.height = size + 'px';
        ripple.style.left   = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top    = (e.clientY - rect.top  - size / 2) + 'px';

        btn.appendChild(ripple);

        ripple.addEventListener('animationend', function() {
            ripple.remove();
        });
    });
}

// 4. Loading spinner — shown on any form submit, hidden after 1.5 s
function initFormSpinner() {
    // Create the overlay element once
    var overlay = document.createElement('div');
    overlay.className = 'spinner-overlay';
    overlay.setAttribute('role', 'status');
    overlay.setAttribute('aria-label', 'Processing…');
    var spinner = document.createElement('div');
    spinner.className = 'spinner';
    overlay.appendChild(spinner);
    document.body.appendChild(overlay);

    document.addEventListener('submit', function(e) {
        // Only show spinner for forms that don't already handle their own
        // success/failure UI (multi-step, booking, portal register).
        // Those forms prevent default and show inline banners — skip them.
        var form = e.target;
        var skipIds = ['appointmentBookingForm', 'registerForm', 'contactForm'];
        if (skipIds.indexOf(form.id) !== -1) return;

        overlay.classList.add('active');
        setTimeout(function() {
            overlay.classList.remove('active');
        }, 1500);
    });
}

// 5. Scroll reveal using IntersectionObserver
function initScrollReveal() {
    if (!('IntersectionObserver' in window)) {
        // Fallback: make all reveal elements visible immediately
        document.querySelectorAll('.reveal').forEach(function(el) {
            el.classList.add('visible');
        });
        return;
    }

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Unobserve once visible so it doesn't re-trigger
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -40px 0px'
    });

    document.querySelectorAll('.reveal').forEach(function(el) {
        observer.observe(el);
    });
}

// ─── Commit 10: Dark Mode ─────────────────────────────────────────────────────

/**
 * initDarkMode — wires up the dark mode toggle button.
 * The saved preference is applied before paint by the IIFE at the top of
 * this file; this function just keeps the button icon in sync and handles
 * user clicks.
 */
function initDarkMode() {
    var toggleBtn = document.getElementById('darkModeToggle');
    if (!toggleBtn) return;

    // Sync icon to current state
    function syncIcon() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        toggleBtn.textContent = isDark ? '☀️' : '🌙';
        toggleBtn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    }

    syncIcon();

    toggleBtn.addEventListener('click', function() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        if (isDark) {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('hms-theme', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('hms-theme', 'dark');
        }
        syncIcon();
    });
}


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

// ─── Commit 13: Patient Testimonials Carousel ────────────────────────────────

/**
 * Auto-rotating testimonials carousel.
 * - Rotates every 4 seconds.
 * - Pauses on hover or focus-within.
 * - Dot indicators and prev/next arrow buttons.
 * - Fully keyboard accessible.
 */
function initTestimonialsCarousel() {
    var track   = document.getElementById('testimonialsTrack');
    var dotsEl  = document.getElementById('carouselDots');
    var prevBtn = document.getElementById('carouselPrev');
    var nextBtn = document.getElementById('carouselNext');
    var wrapper = document.getElementById('testimonialsCarousel');

    if (!track || !dotsEl || !prevBtn || !nextBtn || !wrapper) return;

    var slides    = track.querySelectorAll('.carousel-slide');
    var dots      = dotsEl.querySelectorAll('.carousel-dot');
    var total     = slides.length;
    var current   = 0;
    var autoTimer = null;
    var INTERVAL  = 4000;

    // Move carousel to given index
    function goTo(index) {
        // Wrap around
        if (index < 0)     index = total - 1;
        if (index >= total) index = 0;

        current = index;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';

        // Update dots
        dots.forEach(function(dot, i) {
            var isActive = i === current;
            dot.classList.toggle('active', isActive);
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    // Start auto-rotation
    function startAuto() {
        stopAuto();
        autoTimer = setInterval(function() {
            goTo(current + 1);
        }, INTERVAL);
    }

    function stopAuto() {
        if (autoTimer) {
            clearInterval(autoTimer);
            autoTimer = null;
        }
    }

    // Arrow buttons
    prevBtn.addEventListener('click', function() {
        goTo(current - 1);
        startAuto(); // restart timer on manual interaction
    });

    nextBtn.addEventListener('click', function() {
        goTo(current + 1);
        startAuto();
    });

    // Dot buttons
    dots.forEach(function(dot) {
        dot.addEventListener('click', function() {
            var idx = parseInt(this.getAttribute('data-index'), 10);
            goTo(idx);
            startAuto();
        });
    });

    // Keyboard left/right arrow navigation
    wrapper.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            goTo(current - 1);
            startAuto();
        } else if (e.key === 'ArrowRight') {
            goTo(current + 1);
            startAuto();
        }
    });

    // Pause on hover / focus-within
    wrapper.addEventListener('mouseenter', stopAuto);
    wrapper.addEventListener('focusin',    stopAuto);
    wrapper.addEventListener('mouseleave', startAuto);
    wrapper.addEventListener('focusout',   function(e) {
        // Only restart if focus leaves the carousel entirely
        if (!wrapper.contains(e.relatedTarget)) startAuto();
    });

    // Init
    goTo(0);
    startAuto();
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

// ─── Commit 11: Emergency FAB + Modal ────────────────────────────────────────

/**
 * Injects the floating emergency button and modal into the page,
 * then wires up open/close and accordion behaviour.
 * Called once from DOMContentLoaded.
 */
function initEmergencyFAB() {

    // ── Inject markup ──────────────────────────────────────────────────────

    var fabHTML = [
        '<button class="emergency-fab" id="emergencyFAB" aria-label="Open emergency contacts" title="Emergency">',
        '  🚨',
        '</button>',

        '<div class="emg-modal-overlay" id="emgModalOverlay" role="dialog" aria-modal="true" aria-labelledby="emgModalTitle">',
        '  <div class="emg-modal">',
        '    <div class="emg-modal-header">',
        '      <h2 id="emgModalTitle">🚨 Emergency Contacts</h2>',
        '      <button class="emg-modal-close" id="emgModalClose" aria-label="Close emergency modal">✕</button>',
        '    </div>',
        '    <div class="emg-modal-body">',

        '      <div class="emg-call-grid">',
        '        <a href="tel:999" class="emg-call-btn">',
        '          <span class="emg-icon">📞</span>',
        '          <span class="emg-num">999</span>',
        '          <span class="emg-label">Police / Ambulance</span>',
        '        </a>',
        '        <a href="tel:112" class="emg-call-btn">',
        '          <span class="emg-icon">🚑</span>',
        '          <span class="emg-num">112</span>',
        '          <span class="emg-label">International SOS</span>',
        '        </a>',
        '        <a href="tel:+254700911911" class="emg-call-btn">',
        '          <span class="emg-icon">🏥</span>',
        '          <span class="emg-num">+254 700 911 911</span>',
        '          <span class="emg-label">HMS Emergency Line</span>',
        '        </a>',
        '      </div>',

        '      <p class="emg-accordion-title">🩹 First Aid Quick Tips</p>',
        '      <div class="emg-accordion" id="emgAccordion">',

        '        <div class="emg-accordion-item">',
        '          <button class="emg-accordion-btn" aria-expanded="false" aria-controls="emg-tip-1">',
        '            💔 Cardiac Arrest — CPR',
        '            <i class="emg-accordion-icon">▼</i>',
        '          </button>',
        '          <div class="emg-accordion-panel" id="emg-tip-1" hidden>',
        '            Call 999 immediately. Place the heel of your hand on the centre of the chest and push hard and fast — at least 100–120 compressions per minute. If trained, give 2 rescue breaths after every 30 compressions. Continue until help arrives.',
        '          </div>',
        '        </div>',

        '        <div class="emg-accordion-item">',
        '          <button class="emg-accordion-btn" aria-expanded="false" aria-controls="emg-tip-2">',
        '            🩸 Severe Bleeding',
        '            <i class="emg-accordion-icon">▼</i>',
        '          </button>',
        '          <div class="emg-accordion-panel" id="emg-tip-2" hidden>',
        '            Apply firm, direct pressure to the wound using a clean cloth or bandage. Do not remove the cloth if it soaks through — add more on top. Elevate the limb above heart level if possible and keep the patient calm and still.',
        '          </div>',
        '        </div>',

        '        <div class="emg-accordion-item">',
        '          <button class="emg-accordion-btn" aria-expanded="false" aria-controls="emg-tip-3">',
        '            🔥 Burns',
        '            <i class="emg-accordion-icon">▼</i>',
        '          </button>',
        '          <div class="emg-accordion-panel" id="emg-tip-3" hidden>',
        '            Cool the burn under cool (not cold) running water for at least 10–20 minutes. Remove jewellery near the burn area if possible. Do NOT use ice, butter, or toothpaste. Cover loosely with a clean, non-fluffy material and seek medical help.',
        '          </div>',
        '        </div>',

        '        <div class="emg-accordion-item">',
        '          <button class="emg-accordion-btn" aria-expanded="false" aria-controls="emg-tip-4">',
        '            😵 Choking',
        '            <i class="emg-accordion-icon">▼</i>',
        '          </button>',
        '          <div class="emg-accordion-panel" id="emg-tip-4" hidden>',
        '            For adults — give up to 5 firm back blows between the shoulder blades, then up to 5 abdominal thrusts (Heimlich). Alternate until the object is cleared or emergency services arrive. For infants use back blows and chest thrusts only.',
        '          </div>',
        '        </div>',

        '        <div class="emg-accordion-item">',
        '          <button class="emg-accordion-btn" aria-expanded="false" aria-controls="emg-tip-5">',
        '            🧠 Stroke — FAST',
        '            <i class="emg-accordion-icon">▼</i>',
        '          </button>',
        '          <div class="emg-accordion-panel" id="emg-tip-5" hidden>',
        '            Use the FAST test: <strong>F</strong>ace drooping, <strong>A</strong>rm weakness, <strong>S</strong>peech difficulty — if any present, <strong>T</strong>ime to call 999. Keep the person calm, do not give food or water, and note the exact time symptoms started.',
        '          </div>',
        '        </div>',

        '      </div><!-- /.emg-accordion -->',
        '    </div><!-- /.emg-modal-body -->',
        '  </div><!-- /.emg-modal -->',
        '</div><!-- /.emg-modal-overlay -->'
    ].join('\n');

    var container = document.createElement('div');
    container.innerHTML = fabHTML;
    while (container.firstChild) {
        document.body.appendChild(container.firstChild);
    }

    // ── Element references ──────────────────────────────────────────────────

    var fab     = document.getElementById('emergencyFAB');
    var overlay = document.getElementById('emgModalOverlay');
    var closeBtn = document.getElementById('emgModalClose');

    // ── Open / close helpers ────────────────────────────────────────────────

    function openModal() {
        overlay.classList.add('active');
        closeBtn.focus();
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('active');
        fab.focus();
        document.body.style.overflow = '';
    }

    fab.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);

    // Close on overlay background click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            closeModal();
        }
    });

    // ── First Aid Accordion ─────────────────────────────────────────────────

    var accordion = document.getElementById('emgAccordion');
    if (accordion) {
        var accordionBtns = accordion.querySelectorAll('.emg-accordion-btn');

        accordionBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var isExpanded = this.getAttribute('aria-expanded') === 'true';
                var panelId    = this.getAttribute('aria-controls');
                var panel      = document.getElementById(panelId);

                // Collapse all items
                accordionBtns.forEach(function(otherBtn) {
                    var otherId    = otherBtn.getAttribute('aria-controls');
                    var otherPanel = document.getElementById(otherId);
                    otherBtn.setAttribute('aria-expanded', 'false');
                    if (otherPanel) otherPanel.hidden = true;
                });

                // Toggle clicked item open
                if (!isExpanded) {
                    this.setAttribute('aria-expanded', 'true');
                    if (panel) panel.hidden = false;
                }
            });
        });
    }
}

// ─── Cookie Consent Banner ──────────────────────────────────────────────────

function initCookieConsent() {
    // Don't show if user already made a choice
    if (localStorage.getItem('hms_cookie_consent')) return;

    var banner = document.getElementById('cookieBanner');
    if (!banner) return;

    // Show banner after a brief delay
    setTimeout(function() {
        banner.classList.add('visible');
    }, 800);

    var acceptBtn  = document.getElementById('cookieAccept');
    var declineBtn = document.getElementById('cookieDecline');

    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            localStorage.setItem('hms_cookie_consent', 'accepted');
            hideBanner();
        });
    }

    if (declineBtn) {
        declineBtn.addEventListener('click', function() {
            localStorage.setItem('hms_cookie_consent', 'declined');
            hideBanner();
        });
    }

    function hideBanner() {
        banner.classList.remove('visible');
        setTimeout(function() {
            banner.style.display = 'none';
        }, 400);
    }
}

// ─── Dynamic Copyright Year ──────────────────────────────────────────────────

function updateCopyrightYear() {
    var yearSpans = document.querySelectorAll('#currentYear');
    var year = new Date().getFullYear();
    yearSpans.forEach(function(span) {
        span.textContent = year;
    });
}

// Call cookie consent and copyright year on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initCookieConsent();
    updateCopyrightYear();
});

// ─── Commit 14: Site-Wide Search ─────────────────────────────────────────────

/**
 * HMS Search Data
 * Covers: all 8 services/departments, 12 doctors, all 7 pages.
 */
var HMS_SEARCH_DATA = [
    // Pages
    { type: 'page',    emoji: '🏠', title: 'Home',            desc: 'HMS homepage — overview, stats, quick links', url: 'index.html' },
    { type: 'page',    emoji: '🏥', title: 'About HMS',       desc: 'Our history, mission, accreditations, leadership', url: 'about.html' },
    { type: 'page',    emoji: '💊', title: 'Services',        desc: 'All departments, operating hours, open/closed status', url: 'services.html' },
    { type: 'page',    emoji: '👨‍⚕️', title: 'Doctors',         desc: 'Find and filter our specialist doctors', url: 'doctors.html' },
    { type: 'page',    emoji: '📅', title: 'Appointments',    desc: 'Book an appointment online in 4 steps', url: 'appointments.html' },
    { type: 'page',    emoji: '🔐', title: 'Patient Portal',  desc: 'Login with OTP, view records, pay bills via M-Pesa', url: 'patient-portal.html' },
    { type: 'page',    emoji: '📞', title: 'Contact',         desc: 'Address, phone numbers, map, contact form', url: 'contact.html' },

    // Services / Departments
    { type: 'service', emoji: '🚑', title: 'Emergency & Trauma',     desc: '24/7 emergency care, trauma, advanced life support', url: 'services.html#emergency' },
    { type: 'service', emoji: '🤰', title: 'Maternity & NICU',       desc: 'Antenatal, delivery, postnatal, NICU for premature babies', url: 'services.html#maternity' },
    { type: 'service', emoji: '❤️', title: 'Cardiology',             desc: 'Heart care, angioplasty, catheterisation labs', url: 'services.html#cardiology' },
    { type: 'service', emoji: '🧠', title: 'Neurology & Neurosurgery', desc: 'Brain, spine, stroke management, epilepsy', url: 'services.html#neurology' },
    { type: 'service', emoji: '🦴', title: 'Orthopaedics',           desc: 'Bone & joint care, replacement surgery, physiotherapy', url: 'services.html#orthopedics' },
    { type: 'service', emoji: '👶', title: 'Paediatrics',            desc: 'Child healthcare, NICU, paediatric ICU', url: 'services.html#pediatrics' },
    { type: 'service', emoji: '🔬', title: 'Laboratory',             desc: 'Full diagnostic lab services, blood tests, pathology', url: 'services.html#laboratory' },
    { type: 'service', emoji: '📡', title: 'Radiology & Imaging',    desc: 'X-ray, MRI, CT scan, ultrasound, interventional radiology', url: 'services.html#radiology' },
    { type: 'service', emoji: '💉', title: 'Pharmacy',               desc: '24-hour in-house pharmacy, all prescribed medicines', url: 'services.html#pharmacy' },
    { type: 'service', emoji: '🎗️', title: 'Oncology',              desc: 'Cancer care, chemotherapy, radiation therapy', url: 'services.html#oncology' },

    // Doctors
    { type: 'doctor', emoji: '👨‍⚕️', title: 'Dr. John Davis',          desc: 'Cardiologist — 20 yrs experience',           url: 'doctors.html' },
    { type: 'doctor', emoji: '👩‍⚕️', title: 'Dr. Sarah Wilson',        desc: 'Neurologist — 15 yrs experience',            url: 'doctors.html' },
    { type: 'doctor', emoji: '👨‍⚕️', title: 'Dr. Michael Patel',       desc: 'Orthopaedic Surgeon — 18 yrs experience',    url: 'doctors.html' },
    { type: 'doctor', emoji: '👩‍⚕️', title: 'Dr. Emily Lee',           desc: 'Paediatrician — 12 yrs experience',          url: 'doctors.html' },
    { type: 'doctor', emoji: '👩‍⚕️', title: 'Dr. Anna Martinez',       desc: 'Interventional Cardiologist',                url: 'doctors.html' },
    { type: 'doctor', emoji: '👨‍⚕️', title: 'Dr. Rajesh Kumar',        desc: 'Neurosurgeon',                               url: 'doctors.html' },
    { type: 'doctor', emoji: '👩‍⚕️', title: 'Dr. Grace Mwangi',        desc: 'Obstetrician / Gynaecologist',               url: 'doctors.html' },
    { type: 'doctor', emoji: '👨‍⚕️', title: 'Dr. Tom Otieno',          desc: 'Emergency Medicine Specialist',              url: 'doctors.html' },
    { type: 'doctor', emoji: '👩‍⚕️', title: 'Dr. Patricia Waweru',     desc: 'General Practitioner',                       url: 'doctors.html' },
    { type: 'doctor', emoji: '👨‍⚕️', title: 'Dr. Ben Kariuki',         desc: 'Diagnostic Radiologist',                     url: 'doctors.html' },
    { type: 'doctor', emoji: '👩‍⚕️', title: 'Dr. Alice Mutua',         desc: 'Interventional Radiologist',                 url: 'doctors.html' },
    { type: 'doctor', emoji: '👨‍⚕️', title: 'Dr. Samuel Gitau',        desc: 'Clinical Pathologist',                       url: 'doctors.html' },

    // Quick actions
    { type: 'action', emoji: '📅', title: 'Book Appointment',       desc: 'Schedule a visit with a specialist', url: 'appointments.html' },
    { type: 'action', emoji: '🚨', title: 'Emergency (999)',        desc: 'Call 999 or 112 for emergencies',    url: 'tel:999' },
    { type: 'action', emoji: '📞', title: 'Contact Us',             desc: '+254 700 911 911 | info@hms.co.ke',  url: 'contact.html' }
];

/**
 * Initialises the site-wide search overlay.
 * Works on every page because the overlay HTML is injected into all pages.
 */
function initSiteSearch() {
    var toggleBtn = document.getElementById('siteSearchToggle');
    var overlay   = document.getElementById('siteSearchOverlay');
    var input     = document.getElementById('siteSearchInput');
    var results   = document.getElementById('siteSearchResults');
    var hint      = document.getElementById('siteSearchHint');
    var closeBtn  = document.getElementById('siteSearchClose');

    if (!toggleBtn || !overlay || !input || !results) return;

    // ── Open / close helpers ──────────────────────────────────────────────

    function openSearch() {
        overlay.removeAttribute('hidden');
        toggleBtn.setAttribute('aria-expanded', 'true');
        // Wait a frame so the animation plays
        requestAnimationFrame(function() { input.focus(); });
        document.body.style.overflow = 'hidden';
    }

    function closeSearch() {
        overlay.setAttribute('hidden', '');
        toggleBtn.setAttribute('aria-expanded', 'false');
        input.value = '';
        results.innerHTML = '';
        results.setAttribute('hidden', '');
        if (hint) hint.hidden = false;
        document.body.style.overflow = '';
        toggleBtn.focus();
    }

    // Toggle button
    toggleBtn.addEventListener('click', function() {
        if (overlay.hasAttribute('hidden')) {
            openSearch();
        } else {
            closeSearch();
        }
    });

    // Close button inside overlay
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSearch);
    }

    // ESC key closes
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !overlay.hasAttribute('hidden')) {
            closeSearch();
        }
    });

    // Click outside the box closes
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeSearch();
    });

    // ── Search logic ──────────────────────────────────────────────────────

    function search(query) {
        query = query.trim().toLowerCase();
        results.innerHTML = '';

        if (!query) {
            results.setAttribute('hidden', '');
            if (hint) { hint.textContent = 'Start typing to search…'; hint.hidden = false; }
            return;
        }

        var matches = HMS_SEARCH_DATA.filter(function(item) {
            return (
                item.title.toLowerCase().includes(query) ||
                item.desc.toLowerCase().includes(query)  ||
                item.type.toLowerCase().includes(query)
            );
        });

        if (matches.length === 0) {
            results.setAttribute('hidden', '');
            if (hint) { hint.textContent = 'No results found for "' + query + '"'; hint.hidden = false; }
            return;
        }

        if (hint) hint.hidden = true;

        matches.forEach(function(item, idx) {
            var li  = document.createElement('li');
            var btn = document.createElement('button');
            btn.className = 'site-search-result-item';
            btn.setAttribute('role', 'option');
            btn.setAttribute('aria-selected', 'false');
            btn.setAttribute('tabindex', '-1');

            btn.innerHTML =
                '<span class="site-search-result-emoji" aria-hidden="true">' + item.emoji + '</span>' +
                '<span class="site-search-result-text">' +
                    '<span class="site-search-result-title">' + escapeHtml(item.title) + '</span>' +
                    '<span class="site-search-result-desc">'  + escapeHtml(item.desc)  + '</span>' +
                '</span>';

            btn.addEventListener('click', function() {
                closeSearch();
                if (item.url.startsWith('tel:')) {
                    window.location.href = item.url;
                } else {
                    window.location.href = item.url;
                }
            });

            li.appendChild(btn);
            results.appendChild(li);
        });

        results.removeAttribute('hidden');

        // ── Keyboard navigation within results ─────────────────────────
        var resultBtns = results.querySelectorAll('.site-search-result-item');

        input.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' && resultBtns.length) {
                e.preventDefault();
                resultBtns[0].focus();
            }
        });

        resultBtns.forEach(function(btn, i) {
            btn.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    var next = resultBtns[i + 1];
                    if (next) next.focus(); else input.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    var prev = resultBtns[i - 1];
                    if (prev) prev.focus(); else input.focus();
                } else if (e.key === 'Enter') {
                    btn.click();
                }
            });
        });
    }

    // Debounce: only search after user stops typing for 120 ms
    var debounceTimer;
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        var q = this.value;
        debounceTimer = setTimeout(function() { search(q); }, 120);
    });
}

/** Minimal HTML escape to prevent XSS when building result items. */
function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}


// ── Emergency Floating Button & Modal ────────────────────────────────────────
function initEmergencyFAB() {
    const fab = document.createElement('div');
    fab.innerHTML = `
    <button class="emergency-fab" aria-label="Emergency" id="emergencyFAB">🚨</button>
    <div class="emergency-modal-overlay" id="emergencyModalOverlay">
      <div class="emergency-modal" role="dialog" aria-label="Emergency Contacts">
        <button class="emergency-modal-close" id="closeEmergencyModal">&times;</button>
        <h2>🚨 Emergency Contacts</h2>
        <div class="emergency-call-btns">
          <a href="tel:999" class="ecall-btn red">📞 999</a>
          <a href="tel:112" class="ecall-btn red">📞 112</a>
          <a href="tel:+254700911911" class="ecall-btn orange">📞 +254 700 911 911</a>
        </div>
        <details class="first-aid"><summary>🩹 First Aid: Choking</summary><p>Give 5 back blows then 5 abdominal thrusts. Repeat until clear.</p></details>
        <details class="first-aid"><summary>🩹 First Aid: CPR</summary><p>30 chest compressions then 2 rescue breaths. Rate: 100-120/min.</p></details>
        <details class="first-aid"><summary>🩹 First Aid: Burns</summary><p>Cool under running water for 20 min. Do NOT apply ice or butter.</p></details>
        <details class="first-aid"><summary>🩹 First Aid: Bleeding</summary><p>Apply firm pressure with clean cloth. Elevate the wound above heart.</p></details>
        <details class="first-aid"><summary>🩹 First Aid: Stroke (FAST)</summary><p>Face drooping, Arm weakness, Speech difficulty — Time to call 999.</p></details>
      </div>
    </div>`;
    document.body.appendChild(fab);
    document.getElementById('emergencyFAB').addEventListener('click', function() {
        document.getElementById('emergencyModalOverlay').classList.add('active');
    });
    document.getElementById('closeEmergencyModal').addEventListener('click', function() {
        document.getElementById('emergencyModalOverlay').classList.remove('active');
    });
    document.getElementById('emergencyModalOverlay').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
}

// ── WhatsApp Floating Button ─────────────────────────────────────────────────
function initWhatsAppFAB() {
    const msg = encodeURIComponent('Hello HMS, I would like to enquire about your services.');
    const btn = document.createElement('a');
    btn.href = 'https://wa.me/254700911911?text=' + msg;
    btn.target = '_blank';
    btn.rel = 'noopener noreferrer';
    btn.className = 'whatsapp-fab';
    btn.setAttribute('aria-label', 'Chat on WhatsApp');
    btn.innerHTML = '💬';
    document.body.appendChild(btn);
}

// ── Cookie Consent Banner (Kenya DPA 2019) ───────────────────────────────────
function initCookieConsent() {
    if (localStorage.getItem('hms_cookie_consent')) return;
    const banner = document.createElement('div');
    banner.className = 'cookie-banner';
    banner.innerHTML = `
      <p>We use cookies to improve your experience. By continuing, you agree to our 
        <a href="contact.html">Privacy Policy</a> in accordance with the 
        <strong>Kenya Data Protection Act, 2019</strong>.
      </p>
      <div class="cookie-btns">
        <button id="cookieAccept" class="btn btn-primary btn-sm">Accept</button>
        <button id="cookieDecline" class="btn btn-secondary btn-sm">Decline</button>
      </div>`;
    document.body.appendChild(banner);
    document.getElementById('cookieAccept').addEventListener('click', function() {
        localStorage.setItem('hms_cookie_consent', 'accepted');
        banner.remove();
    });
    document.getElementById('cookieDecline').addEventListener('click', function() {
        localStorage.setItem('hms_cookie_consent', 'declined');
        banner.remove();
    });
}

// ── Site Search ───────────────────────────────────────────────────────────────
function initSiteSearch() {
    const searchData = [
        { title: 'Home', url: 'index.html', keywords: 'home hospital hms nairobi' },
        { title: 'About Us', url: 'about.html', keywords: 'about history mission vision leadership' },
        { title: 'Services', url: 'services.html', keywords: 'services opd emergency maternity pharmacy lab radiology icu surgery' },
        { title: 'Doctors', url: 'doctors.html', keywords: 'doctors specialists cardiology neurology pediatrics' },
        { title: 'Appointments', url: 'appointments.html', keywords: 'book appointment schedule' },
        { title: 'Patient Portal', url: 'patient-portal.html', keywords: 'patient portal login results invoices' },
        { title: 'Contact', url: 'contact.html', keywords: 'contact phone address directions map' },
        { title: 'Emergency', url: 'tel:999', keywords: 'emergency 999 112 ambulance' },
        { title: 'Dr. Amina Hassan – Cardiology', url: 'doctors.html', keywords: 'cardiology heart doctor' },
        { title: 'Dr. Grace Njeri – Pediatrics', url: 'doctors.html', keywords: 'pediatrics children doctor' },
        { title: 'Laboratory Services', url: 'services.html', keywords: 'lab blood test results' },
        { title: 'Pharmacy', url: 'services.html', keywords: 'pharmacy drugs prescription' },
    ];
    const searchBtn = document.querySelector('.search-toggle');
    const searchOverlay = document.getElementById('searchOverlay');
    if (!searchBtn || !searchOverlay) return;
    const input = searchOverlay.querySelector('.search-input');
    const results = searchOverlay.querySelector('.search-results');
    searchBtn.addEventListener('click', function() {
        searchOverlay.classList.add('active');
        input.focus();
    });
    searchOverlay.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') searchOverlay.classList.remove('active');
    });
    input.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        results.innerHTML = '';
        if (!q) return;
        const matches = searchData.filter(d => d.title.toLowerCase().includes(q) || d.keywords.includes(q));
        matches.slice(0, 6).forEach(function(item) {
            const a = document.createElement('a');
            a.href = item.url;
            a.className = 'search-result-item';
            a.textContent = item.title;
            results.appendChild(a);
        });
        if (!matches.length) results.innerHTML = '<p class="no-results">No results found.</p>';
    });
}

// ── Init all features ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    initEmergencyFAB();
    initWhatsAppFAB();
    initCookieConsent();
    initSiteSearch();
});

// emergency modal v2 - improved accessibility

// search v2 - highlight matched text
