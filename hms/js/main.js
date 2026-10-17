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
