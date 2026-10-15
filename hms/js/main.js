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
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Animate hamburger
            const spans = hamburger.querySelectorAll('span');
            spans.forEach(span => span.classList.toggle('active'));
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('active');
            }
        });
        
        // Close menu when clicking a link
        const links = navLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
            });
        });
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
    const target = parseInt(element.textContent);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(function() {
        current += step;
        if (current >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current) + '+';
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

// Doctor Filter Tabs
function initDoctorFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const doctorCards = document.querySelectorAll('.doctor-card');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter doctors
            doctorCards.forEach(card => {
                const specialty = card.getAttribute('data-specialty');
                
                if (filter === 'all' || specialty === filter) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
}

// Doctor Search
function initDoctorSearch() {
    const searchInput = document.querySelector('.doctor-search');
    const doctorCards = document.querySelectorAll('.doctor-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            doctorCards.forEach(card => {
                const name = card.querySelector('.doctor-name').textContent.toLowerCase();
                const specialty = card.querySelector('.doctor-specialty').textContent.toLowerCase();
                
                if (name.includes(query) || specialty.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
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
