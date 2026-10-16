// FundiPro Main JavaScript File

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set dynamic year in footer
    const yearSpan = document.getElementById('currentYear');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }
    
    initNavigation();
    initScrollAnimations();
    initBackToTop();
    initSearchForm();
    initContactForm();
    initFAQ();
    initProfessionalFilters();
    initCounterAnimation();
    initActiveNavLinks();
    initGeolocation();
    initProfessionalFormDetection();
});

// ===== NAVIGATION =====
function initNavigation() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const navbar = document.getElementById('navbar');
    
    // Hamburger menu toggle
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
    
    // Sticky navbar shadow on scroll
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
}

// ===== SCROLL ANIMATIONS =====
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    // Create Intersection Observer
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        }
    );
    
    // Observe all animated elements
    animatedElements.forEach(element => {
        observer.observe(element);
    });
}

// ===== BACK TO TOP BUTTON =====
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        // Scroll to top on click
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ===== SEARCH FORM =====
function initSearchForm() {
    const searchForm = document.getElementById('searchForm');
    const professionalSearchForm = document.getElementById('professionalSearchForm');
    
    // Main search form (homepage)
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const serviceType = document.getElementById('serviceType').value;
            const location = document.getElementById('location').value;
            
            // Validate
            if (!serviceType || !location) {
                alert('Please select a service and enter your location.');
                return;
            }
            
            // Redirect to professionals page with query params
            window.location.href = `professionals.html?service=${encodeURIComponent(serviceType)}&location=${encodeURIComponent(location)}`;
        });
    }
    
    // Professional search form
    if (professionalSearchForm) {
        professionalSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterProfessionals();
        });
    }
}

// ===== CONTACT FORM =====
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    const professionalForm = document.getElementById('professionalForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            clearFormErrors();
            
            // Get form values
            const fullName = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value.trim();
            
            // Validate
            let isValid = true;
            
            if (fullName.length < 2) {
                showError('fullName', 'Please enter a valid name');
                isValid = false;
            }
            
            if (!isValidEmail(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            }
            
            if (!isValidPhone(phone)) {
                showError('phone', 'Please enter a valid phone number');
                isValid = false;
            }
            
            if (!subject) {
                showError('subject', 'Please select a subject');
                isValid = false;
            }
            
            if (message.length < 10) {
                showError('message', 'Please enter a message (at least 10 characters)');
                isValid = false;
            }
            
            // If valid, show success modal
            if (isValid) {
                // In a real application, you would send this data to a server
                console.log('Form submitted:', { fullName, email, phone, subject, message });
                
                // Reset form
                contactForm.reset();
                
                // Show success modal
                showSuccessModal();
            }
        });
    }
    
    if (professionalForm) {
        professionalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            clearFormErrors();
            
            // Get form values
            const businessName = document.getElementById('businessName').value.trim();
            const ownerName = document.getElementById('ownerName').value.trim();
            const proPhone = document.getElementById('proPhone').value.trim();
            const proEmail = document.getElementById('proEmail').value.trim();
            const county = document.getElementById('county').value;
            const experience = document.getElementById('experience').value;
            const services = Array.from(document.querySelectorAll('input[name="services"]:checked')).map(cb => cb.value);
            const proMessage = document.getElementById('proMessage').value.trim();
            
            // Validate
            let isValid = true;
            
            if (businessName.length < 2) {
                showError('businessName', 'Please enter a valid business name');
                isValid = false;
            }
            
            if (ownerName.length < 2) {
                showError('ownerName', 'Please enter a valid name');
                isValid = false;
            }
            
            if (!isValidPhone(proPhone)) {
                showError('proPhone', 'Please enter a valid phone number');
                isValid = false;
            }
            
            if (!isValidEmail(proEmail)) {
                showError('proEmail', 'Please enter a valid email address');
                isValid = false;
            }
            
            if (!county) {
                showError('county', 'Please select your county');
                isValid = false;
            }
            
            if (!experience || experience < 0) {
                showError('experience', 'Please enter years of experience');
                isValid = false;
            }
            
            if (services.length === 0) {
                showError('services', 'Please select at least one service');
                isValid = false;
            }
            
            if (proMessage.length < 10) {
                showError('proMessage', 'Please provide more details about your business');
                isValid = false;
            }
            
            // If valid, show success modal
            if (isValid) {
                console.log('Professional registration submitted:', { 
                    businessName, ownerName, proPhone, proEmail, county, experience, services, proMessage 
                });
                
                // Reset form
                professionalForm.reset();
                
                // Show success modal with custom message
                showSuccessModal('Registration Submitted!', 'Thank you for your interest in joining FundiPro. Our team will review your application and contact you within 48 hours.');
            }
        });
    }
}

// Form validation helpers
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    // Allow various phone formats (basic validation)
    const phoneRegex = /^[\d\s\+\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorSpan = document.getElementById(fieldId + 'Error');
    
    if (field && errorSpan) {
        field.classList.add('error');
        errorSpan.textContent = message;
        errorSpan.style.display = 'block';
    }
}

function clearFormErrors() {
    const errorInputs = document.querySelectorAll('.form-input.error');
    const errorSpans = document.querySelectorAll('.form-error');
    
    errorInputs.forEach(input => {
        input.classList.remove('error');
    });
    
    errorSpans.forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
}

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    const closeBtn = document.getElementById('closeModal');
    
    if (modal) {
        modal.classList.add('active');
        
        // Close modal on button click
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
            });
        }
        
        // Close modal on background click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
}

// ===== FAQ ACCORDION =====
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        if (question) {
            question.addEventListener('click', function() {
                // Close other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        }
    });
}

// ===== PROFESSIONAL FILTERS =====
function initProfessionalFilters() {
    const serviceFilters = document.querySelectorAll('.service-filter');
    const ratingFilters = document.querySelectorAll('.rating-filter');
    const locationFilter = document.getElementById('locationFilter');
    const sortBy = document.getElementById('sortBy');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    // Service filter checkboxes
    if (serviceFilters.length > 0) {
        serviceFilters.forEach(filter => {
            filter.addEventListener('change', filterProfessionals);
        });
    }
    
    // Rating filter radio buttons
    if (ratingFilters.length > 0) {
        ratingFilters.forEach(filter => {
            filter.addEventListener('change', filterProfessionals);
        });
    }
    
    // Location filter
    if (locationFilter) {
        locationFilter.addEventListener('input', debounce(filterProfessionals, 500));
    }
    
    // Sort dropdown
    if (sortBy) {
        sortBy.addEventListener('change', sortProfessionals);
    }
    
    // Clear filters button
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
    // Check URL params and apply filters
    applyURLFilters();
}

function filterProfessionals() {
    const professionalCards = document.querySelectorAll('.professional-card-full');
    const resultsCount = document.getElementById('resultsCount');
    
    if (professionalCards.length === 0) return;
    
    // Get selected filters
    const selectedServices = Array.from(document.querySelectorAll('.service-filter:checked'))
        .map(cb => cb.value);
    
    const selectedRating = document.querySelector('.rating-filter:checked')?.value || 'all';
    
    const locationQuery = document.getElementById('locationFilter')?.value.toLowerCase() || '';
    
    let visibleCount = 0;
    
    // Filter each card
    professionalCards.forEach(card => {
        const cardService = card.dataset.service;
        const cardRating = parseFloat(card.dataset.rating);
        const cardLocation = card.querySelector('.pro-location')?.textContent.toLowerCase() || '';
        
        let showCard = true;
        
        // Service filter
        if (selectedServices.length > 0 && !selectedServices.includes(cardService)) {
            showCard = false;
        }
        
        // Rating filter
        if (selectedRating !== 'all') {
            const minRating = parseFloat(selectedRating);
            if (cardRating < minRating) {
                showCard = false;
            }
        }
        
        // Location filter
        if (locationQuery && !cardLocation.includes(locationQuery)) {
            showCard = false;
        }
        
        // Show/hide card
        if (showCard) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update results count
    if (resultsCount) {
        resultsCount.textContent = visibleCount;
    }
}

function sortProfessionals() {
    const resultsContainer = document.getElementById('professionalsResults');
    const sortBy = document.getElementById('sortBy')?.value;
    
    if (!resultsContainer || !sortBy) return;
    
    const cards = Array.from(resultsContainer.querySelectorAll('.professional-card-full'));
    
    cards.sort((a, b) => {
        switch (sortBy) {
            case 'rating':
                return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
            case 'price-low':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price-high':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'reviews':
                const reviewsA = parseInt(a.querySelector('.reviews-count')?.textContent.match(/\d+/)?.[0] || 0);
                const reviewsB = parseInt(b.querySelector('.reviews-count')?.textContent.match(/\d+/)?.[0] || 0);
                return reviewsB - reviewsA;
            default:
                return 0;
        }
    });
    
    // Re-append in sorted order
    cards.forEach(card => {
        resultsContainer.appendChild(card);
    });
}

function clearAllFilters() {
    // Uncheck all service filters
    document.querySelectorAll('.service-filter').forEach(cb => {
        cb.checked = false;
    });
    
    // Reset rating to 'all'
    const allRatingFilter = document.querySelector('.rating-filter[value="all"]');
    if (allRatingFilter) {
        allRatingFilter.checked = true;
    }
    
    // Clear location input
    const locationFilter = document.getElementById('locationFilter');
    if (locationFilter) {
        locationFilter.value = '';
    }
    
    // Reset sort
    const sortBy = document.getElementById('sortBy');
    if (sortBy) {
        sortBy.value = 'rating';
    }
    
    // Re-filter to show all
    filterProfessionals();
}

function applyURLFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    const service = urlParams.get('service');
    const location = urlParams.get('location');
    
    // Apply service filter
    if (service) {
        const serviceCheckbox = document.querySelector(`.service-filter[value="${service}"]`);
        if (serviceCheckbox) {
            serviceCheckbox.checked = true;
        }
    }
    
    // Apply location filter
    if (location) {
        const locationFilter = document.getElementById('locationFilter');
        if (locationFilter) {
            locationFilter.value = location;
        }
    }
    
    // Apply filters if any were set
    if (service || location) {
        filterProfessionals();
    }
}

// Debounce helper for input events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===== COUNTER ANIMATION =====
function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number');
    
    if (counters.length === 0) return;
    
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseFloat(counter.dataset.target);
                animateCounter(counter, target);
                counterObserver.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

function animateCounter(element, target) {
    const duration = 2000; // 2 seconds
    const startTime = performance.now();
    const isDecimal = target % 1 !== 0;
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (easeOutQuart)
        const easeProgress = 1 - Math.pow(1 - progress, 4);
        
        const current = easeProgress * target;
        
        if (isDecimal) {
            element.textContent = current.toFixed(1);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        } else {
            if (isDecimal) {
                element.textContent = target.toFixed(1);
            } else {
                element.textContent = target.toLocaleString();
            }
        }
    }
    
    requestAnimationFrame(updateCounter);
}

// ===== ACTIVE NAV LINKS =====
function initActiveNavLinks() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        
        if (linkPage === currentPage || 
            (currentPage === '' && linkPage === 'index.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// ===== SMOOTH SCROLLING FOR ANCHOR LINKS =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        
        // Don't prevent default for href="#" or empty
        if (href === '#' || href === '') {
            return;
        }
        
        e.preventDefault();
        
        const targetId = href.substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            const navbarHeight = document.getElementById('navbar')?.offsetHeight || 0;
            const targetPosition = targetElement.offsetTop - navbarHeight - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// ===== PAGINATION =====
const paginationButtons = document.querySelectorAll('.pagination-number');
if (paginationButtons.length > 0) {
    paginationButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all
            paginationButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active to clicked
            this.classList.add('active');
            
            // Scroll to top of results
            const resultsSection = document.querySelector('.professionals-listing');
            if (resultsSection) {
                resultsSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

// Previous/Next pagination buttons
const prevBtn = document.querySelector('.pagination-btn:first-child');
const nextBtn = document.querySelector('.pagination-btn:last-child');

if (prevBtn && nextBtn) {
    prevBtn.addEventListener('click', function() {
        const activePage = document.querySelector('.pagination-number.active');
        const prevPage = activePage?.previousElementSibling;
        
        if (prevPage && prevPage.classList.contains('pagination-number')) {
            prevPage.click();
        }
    });
    
    nextBtn.addEventListener('click', function() {
        const activePage = document.querySelector('.pagination-number.active');
        const nextPage = activePage?.nextElementSibling;
        
        if (nextPage && nextPage.classList.contains('pagination-number')) {
            nextPage.click();
        }
    });
}

// Update prev/next button states based on active page
function updatePaginationButtons() {
    const activePage = document.querySelector('.pagination-number.active');
    const firstPage = document.querySelector('.pagination-number:first-of-type');
    const lastPage = document.querySelector('.pagination-number:last-of-type');
    
    if (prevBtn && activePage) {
        prevBtn.disabled = activePage === firstPage;
    }
    
    if (nextBtn && activePage) {
        nextBtn.disabled = activePage === lastPage;
    }
}

// Call on page load
updatePaginationButtons();

// ===== PROFESSIONAL CARD INTERACTIONS =====
const bookNowButtons = document.querySelectorAll('.professional-card-full .btn-primary');
bookNowButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const card = this.closest('.professional-card-full');
        const professionalName = card.querySelector('.pro-name')?.textContent || 'this professional';
        
        alert(`Booking feature coming soon! You selected ${professionalName}. In the full version, this would open a booking form or redirect to a booking page.`);
    });
});

// View Profile buttons
const viewProfileButtons = document.querySelectorAll('.professional-card-full .btn-outline');
viewProfileButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const card = this.closest('.professional-card-full');
        const professionalName = card.querySelector('.pro-name')?.textContent || 'this professional';
        
        alert(`Profile feature coming soon! This would show detailed information about ${professionalName}.`);
    });
});

// ===== CONSOLE LOG =====
console.log('FundiPro website loaded successfully!');
console.log('Version: 1.0.0');

// ===== GEOLOCATION =====
function initGeolocation() {
    const useLocationBtn = document.getElementById('useLocationBtn');
    if (useLocationBtn) {
        useLocationBtn.addEventListener('click', function() {
            if ("geolocation" in navigator) {
                this.disabled = true;
                this.textContent = 'Getting location...';
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const locationInput = document.getElementById('location');
                        if (locationInput) {
                            locationInput.value = `Lat: ${position.coords.latitude.toFixed(2)}, Lng: ${position.coords.longitude.toFixed(2)}`;
                        }
                        this.disabled = false;
                        this.textContent = '📍 Use My Location';
                        alert('Location detected! In production, this would reverse-geocode to show your area name.');
                    },
                    (error) => {
                        this.disabled = false;
                        this.textContent = '📍 Use My Location';
                        alert('Could not get location. Please enter manually or check your browser permissions.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        });
    }
}
