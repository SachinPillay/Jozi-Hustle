/**
 * Jozi Hustle - Interactive JavaScript
 * Enhanced user interactions and functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("Welcome to Jozi Hustle!");
    
    // Initialize all interactive features
    initMobileMenu();
    initWishlistButtons();
    initSearchEnhancements();
    initProductCardAnimations();
    initNotificationSystem();
    
    // Welcome message with fade-in animation
    setTimeout(() => {
        const fadeElements = document.querySelectorAll('.fade-in');
        fadeElements.forEach((element, index) => {
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 200);
        });
    }, 100);
});

// Mobile Navigation Toggle
function initMobileMenu() {
    const navContainer = document.querySelector('.nav-container');
    const navLinks = document.querySelector('.nav-links');
    
    if (navContainer && navLinks) {
        // Create mobile menu toggle button
        const toggleButton = document.createElement('button');
        toggleButton.className = 'mobile-menu-toggle';
        toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
        toggleButton.setAttribute('aria-label', 'Toggle navigation menu');
        
        // Insert toggle button before nav-links
        navContainer.insertBefore(toggleButton, navLinks);
        
        // Toggle menu on button click
        toggleButton.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const isOpen = navLinks.classList.contains('active');
            toggleButton.innerHTML = isOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navContainer.contains(event.target) && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }
}

// Enhanced Wishlist Functionality
function initWishlistButtons() {
    const wishlistButtons = document.querySelectorAll('.wishlist-btn');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const adId = this.getAttribute('data-ad-id');
            
            // Show loading state
            const originalContent = this.innerHTML;
            this.innerHTML = '<div class="loading"></div>';
            this.disabled = true;
            
            // Make AJAX call to wishlist handler
            fetch('handlers/wishlist_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `ad_id=${adId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.add('added');
                    this.innerHTML = '<i class="fas fa-heart"></i>';
                    showNotification('Added to wishlist!', 'success');
                } else {
                    this.innerHTML = originalContent;
                    showNotification('Failed to add to wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = originalContent;
                showNotification('Something went wrong', 'error');
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });
}

// Search Enhancements
function initSearchEnhancements() {
    const searchInput = document.querySelector('.search-input');
    const searchForm = document.querySelector('.search-filters form');
    
    if (searchInput) {
        // Add search suggestions (basic implementation)
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            if (query.length > 2) {
                // Could implement search suggestions here
                this.style.borderColor = '#667eea';
            } else {
                this.style.borderColor = '#e1e5e9';
            }
        });
        
        // Enhanced form submission
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalContent = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<div class="loading"></div> Searching...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after a short delay (form will redirect)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalContent;
                        submitBtn.disabled = false;
                    }, 2000);
                }
            });
        }
    }
}

// Product Card Animations
function initProductCardAnimations() {
    const productCards = document.querySelectorAll('.product-card');
    
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    productCards.forEach((card, index) => {
        // Initial state for animation
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        
        // Observe for scroll animation
        observer.observe(card);
        
        // Add click handler for product cards
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (e.target.closest('.btn') || e.target.closest('button')) {
                return;
            }
            
            const viewLink = this.querySelector('a[href*="ad_detail"]');
            if (viewLink) {
                window.location.href = viewLink.href;
            }
        });
    });
}

// Notification System
function initNotificationSystem() {
    // Create notification container if it doesn't exist
    if (!document.querySelector('.notification-container')) {
        const container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info') {
    const container = document.querySelector('.notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.pointerEvents = 'auto';
    
    container.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Utility Functions
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

function smoothScrollTo(element, duration = 800) {
    const start = window.pageYOffset;
    const target = element.offsetTop - 100; // 100px offset for fixed header
    const distance = target - start;
    let startTime = null;
    
    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const progress = Math.min(timeElapsed / duration, 1);
        const ease = easeInOutCubic(progress);
        
        window.scrollTo(0, start + (distance * ease));
        
        if (timeElapsed < duration) {
            requestAnimationFrame(animation);
        }
    }
    
    function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
    }
    
    requestAnimationFrame(animation);
}

// Form Enhancement
function enhanceForm(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        // Add floating label effect
        if (input.type !== 'file' && input.type !== 'checkbox' && input.type !== 'radio') {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
            
            // Check if input has value on load
            if (input.value) {
                input.parentElement.classList.add('focused');
            }
        }
    });
}

// Initialize form enhancements for common forms
document.addEventListener('DOMContentLoaded', function() {
    enhanceForm('.form-container form');
    enhanceForm('.search-filters form');
});

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    showNotification('Something went wrong. Please refresh the page.', 'error');
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(function() {
            const perfData = performance.timing;
            const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
            console.log('Page Load Time:', pageLoadTime + 'ms');
        }, 0);
    });
}
