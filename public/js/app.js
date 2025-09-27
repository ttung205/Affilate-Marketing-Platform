// Affiliate Marketing - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components with performance optimization
    initScrollAnimations();
    initStatsCounter();
    initSmoothScrolling();
    initHeaderEffects();
    initParallaxEffects();
    initInteractiveElements();
    initMobileMenu();
    initTypingEffect();
    initLazyLoading();
    initScrollProgress();
    initBackToTop();
    initStickyElements();
});

// Enhanced Scroll Animations with Intersection Observer
function initScrollAnimations() {
    const observerOptions = {
        threshold: [0.1, 0.3, 0.5, 0.7, 0.9],
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                
                // Add animation class based on data attribute
                const animationType = element.dataset.animation || 'slide-up';
                element.classList.add(`animate-${animationType}`, 'animate-in');
                
                // Add staggered animation for child elements
                const children = element.querySelectorAll('[data-stagger]');
                children.forEach((child, index) => {
                    const staggerDelay = parseInt(child.dataset.stagger) * 100;
                    setTimeout(() => {
                        child.classList.add('animate-in');
                    }, staggerDelay);
                });
                
                // Unobserve after animation to improve performance
                if (entry.intersectionRatio > 0.7) {
                    observer.unobserve(element);
                }
            }
        });
    }, observerOptions);

    // Observe all sections and animated elements
    const animatedElements = document.querySelectorAll('section, [data-animation], .animate-on-scroll');
    animatedElements.forEach(element => {
        observer.observe(element);
    });
}

// Enhanced Stats Counter with smooth counting
function initStatsCounter() {
    const statsSection = document.querySelector('.stats-section');
    if (!statsSection) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    observer.observe(statsSection);
}

function animateStats() {
    const stats = document.querySelectorAll('.stat-number[data-count]');
    
    stats.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-count'));
        const duration = 2500; // 2.5 seconds for smoother animation
        const startTime = performance.now();
        
        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Use easing function for smoother animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(target * easeOutQuart);
            
            // Format number with commas and add suffix if exists
            const suffix = stat.dataset.suffix || '';
            stat.textContent = current.toLocaleString() + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }
        
        requestAnimationFrame(updateCounter);
    });
}

// Enhanced Smooth Scrolling with easing
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
                const targetPosition = target.offsetTop - headerHeight - 20;
                
                // Use custom easing function for smoother scroll
                smoothScrollTo(targetPosition, 1000);
            }
        });
    });
}

// Custom smooth scroll with easing
function smoothScrollTo(targetPosition, duration) {
    const startPosition = window.pageYOffset;
    const distance = targetPosition - startPosition;
    let startTime = null;
    
    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const progress = Math.min(timeElapsed / duration, 1);
        
        // Easing function (easeInOutCubic)
        const ease = progress < 0.5 
            ? 4 * progress * progress * progress 
            : 1 - Math.pow(-2 * progress + 2, 3) / 2;
        
        window.scrollTo(0, startPosition + distance * ease);
        
        if (progress < 1) {
            requestAnimationFrame(animation);
        }
    }
    
    requestAnimationFrame(animation);
}

// Enhanced Header Effects with smooth transitions
function initHeaderEffects() {
    const header = document.querySelector('.header');
    if (!header) return;
    
    let lastScrollY = window.scrollY;
    let ticking = false;
    
    function updateHeader() {
        const currentScrollY = window.scrollY;
        
        // Add scrolled class for background effect
        if (currentScrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Header luôn hiển thị - không ẩn khi scroll
        header.style.transform = 'translateY(0)';
        
        lastScrollY = currentScrollY;
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateHeader);
            ticking = true;
        }
    });
}

// Enhanced Parallax Effects with performance optimization
function initParallaxEffects() {
    let ticking = false;
    
    function updateParallax() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax');
        
        parallaxElements.forEach(element => {
            const speed = parseFloat(element.dataset.speed) || 0.5;
            const yPos = -(scrolled * speed);
            element.style.transform = `translate3d(0, ${yPos}px, 0)`;
        });
        
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    });
}

// Enhanced Interactive Elements with better performance
function initInteractiveElements() {
    // Feature cards hover effects with transform3d for better performance
    const featureCards = document.querySelectorAll('.feature-card');
    
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translate3d(0, -15px, 0) scale(1.02)';
            this.style.boxShadow = '0 20px 40px rgba(0, 123, 255, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translate3d(0, 0, 0) scale(1)';
            this.style.boxShadow = '0 10px 30px rgba(0, 123, 255, 0.1)';
        });
    });
    
    // Floating cards animation with CSS transforms
    const floatingCards = document.querySelectorAll('.floating-card');
    
    floatingCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.5}s`;
        
        // Enhanced hover effect
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg) translateZ(0)';
            this.style.boxShadow = '0 15px 35px rgba(0, 123, 255, 0.3)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg) translateZ(0)';
            this.style.boxShadow = '0 10px 30px rgba(0, 123, 255, 0.1)';
        });
    });
    
    // Chart bars interaction with smooth transitions
    const chartBars = document.querySelectorAll('.chart-bar');
    
    chartBars.forEach(bar => {
        bar.addEventListener('mouseenter', function() {
            this.style.transform = 'scaleY(1.2) translateZ(0)';
            this.style.background = 'rgba(255, 255, 255, 0.8)';
            this.style.boxShadow = '0 0 10px rgba(255, 255, 255, 0.5)';
        });
        
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'scaleY(1) translateZ(0)';
            this.style.background = 'rgba(255, 255, 255, 0.3)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Stats items interaction
    const statItems = document.querySelectorAll('.stat-item');
    
    statItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translate3d(0, -10px, 0) scale(1.05)';
            this.style.boxShadow = '0 20px 40px rgba(0, 123, 255, 0.2)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translate3d(0, 0, 0) scale(1)';
            this.style.boxShadow = '0 10px 30px rgba(0, 123, 255, 0.1)';
        });
    });
}

// Enhanced Mobile Menu with smooth animations
function initMobileMenu() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            
            // Add smooth slide animation
            if (navMenu.classList.contains('active')) {
                navMenu.style.transform = 'translateX(0)';
            } else {
                navMenu.style.transform = 'translateX(-100%)';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
                navMenu.style.transform = 'translateX(-100%)';
            }
        });
    }
}

// Enhanced Typing Effect
function initTypingEffect() {
    const heroTitle = document.querySelector('.hero-title');
    if (!heroTitle) return;
    
    const text = heroTitle.textContent;
    const highlightSpan = heroTitle.querySelector('.highlight');
    
    if (highlightSpan) {
        const highlightText = highlightSpan.textContent;
        heroTitle.innerHTML = text.replace(highlightText, `<span class="highlight typing-text">${highlightText}</span>`);
        
        const typingElement = heroTitle.querySelector('.typing-text');
        typingElement.style.borderRight = '2px solid #007bff';
        typingElement.style.animation = 'typing 3s steps(40, end), blink-caret 0.75s step-end infinite';
    }
}

// New: Lazy Loading for images and components
function initLazyLoading() {
    const lazyElements = document.querySelectorAll('[data-src], [data-lazy]');
    
    const lazyObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                
                if (element.dataset.src) {
                    element.src = element.dataset.src;
                    element.classList.remove('lazy');
                }
                
                if (element.dataset.lazy) {
                    element.classList.add('lazy-loaded');
                }
                
                lazyObserver.unobserve(element);
            }
        });
    }, { threshold: 0.1 });
    
    lazyElements.forEach(element => lazyObserver.observe(element));
}

// New: Scroll Progress Indicator
function initScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #007bff, #00d4ff);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    
    document.body.appendChild(progressBar);
    
    let ticking = false;
    
    function updateProgress() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        
        progressBar.style.width = scrollPercent + '%';
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateProgress);
            ticking = true;
        }
    });
}

// New: Back to Top Button
function initBackToTop() {
    const backToTopBtn = document.createElement('button');
    backToTopBtn.className = 'back-to-top';
    backToTopBtn.innerHTML = '↑';
    backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    `;
    
    document.body.appendChild(backToTopBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.opacity = '1';
            backToTopBtn.style.visibility = 'visible';
        } else {
            backToTopBtn.style.opacity = '0';
            backToTopBtn.style.visibility = 'hidden';
        }
    });
    
    // Smooth scroll to top
    backToTopBtn.addEventListener('click', () => {
        smoothScrollTo(0, 800);
    });
    
    // Hover effects
    backToTopBtn.addEventListener('mouseenter', () => {
        backToTopBtn.style.transform = 'scale(1.1)';
        backToTopBtn.style.boxShadow = '0 6px 20px rgba(0, 123, 255, 0.4)';
    });
    
    backToTopBtn.addEventListener('mouseleave', () => {
        backToTopBtn.style.transform = 'scale(1)';
        backToTopBtn.style.boxShadow = '0 4px 12px rgba(0, 123, 255, 0.3)';
    });
}

// New: Sticky Elements with smooth transitions
function initStickyElements() {
    const stickyElements = document.querySelectorAll('[data-sticky]');
    
    stickyElements.forEach(element => {
        const stickyTop = parseInt(element.dataset.sticky) || 0;
        let isSticky = false;
        
        function checkSticky() {
            const rect = element.getBoundingClientRect();
            
            if (rect.top <= stickyTop && !isSticky) {
                element.classList.add('sticky');
                isSticky = true;
            } else if (rect.top > stickyTop && isSticky) {
                element.classList.remove('sticky');
                isSticky = false;
            }
        }
        
        window.addEventListener('scroll', throttle(checkSticky, 16));
    });
}

// Enhanced Performance Optimization
function optimizePerformance() {
    // Lazy load images with better performance
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    }, { threshold: 0.1, rootMargin: '50px' });
    
    images.forEach(img => imageObserver.observe(img));
    
    // Debounce scroll events for better performance
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(() => {
            // Handle scroll events here
        }, 16);
    });
}

// Enhanced Utility Functions
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

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Initialize performance optimizations
optimizePerformance();

// Add enhanced CSS classes for animations
document.addEventListener('DOMContentLoaded', () => {
    // Add animation classes to elements
    const animatedElements = document.querySelectorAll('[data-aos]');
    
    animatedElements.forEach(element => {
        element.classList.add('aos-animate');
    });
    
    // Enhanced button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
            this.style.boxShadow = '0 8px 25px rgba(0, 123, 255, 0.3)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 15px rgba(0, 123, 255, 0.2)';
        });
    });
    
    // Add smooth reveal animations for sections
    const sections = document.querySelectorAll('section');
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'all 0.8s ease';
        
        setTimeout(() => {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, index * 200);
    });
});

// Export enhanced functions for global use
window.TTungAffiliate = {
    animateStats,
    initScrollAnimations,
    initMobileMenu,
    initTypingEffect,
    smoothScrollTo,
    initScrollProgress,
    initBackToTop,
    initStickyElements
};

// Add CSS for new components
const additionalStyles = `
    .scroll-progress {
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #007bff, #00d4ff);
        z-index: 9999;
        transition: width 0.1s ease;
    }
    
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    
    .back-to-top:hover {
        background: #0056b3;
        transform: scale(1.1);
    }
    
    .sticky {
        position: sticky;
        top: 0;
        z-index: 100;
        transition: all 0.3s ease;
    }
    
    .lazy-loaded {
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;

// Inject additional styles
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);
