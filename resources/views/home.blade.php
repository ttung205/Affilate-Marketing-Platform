<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Marketing - Nền tảng Affiliate Marketing Hàng đầu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scroll-effects.css') }}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Chatbot CSS -->
    <link rel="stylesheet" href="{{ asset('css/chatbot/chatbot.css') }}">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand" data-aos="fade-right">
                <i class="fas fa-chart-line"></i>
                <span>Affiliate Marketing</span>
            </div>
            <div class="nav-menu" data-aos="fade-down">
                <a href="#home" class="nav-link">Trang chủ</a>
                <a href="#features" class="nav-link">Tính năng</a>
                <a href="#affiliate" class="nav-link">Affiliate</a>
                <a href="#about" class="nav-link">Giới thiệu</a>
                <a href="#contact" class="nav-link">Liên hệ</a>
            </div>
            <div class="nav-auth" data-aos="fade-left">
                <a href="{{ route('login') }}" class="btn btn-outline">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Đăng ký</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero scroll-animate section-transition">
        <div class="hero-bg">
            <div class="hero-bg-circle parallax" data-speed="0.3"></div>
            <div class="hero-bg-pattern parallax" data-speed="0.1"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text scroll-animate-left" data-animation="slide-left">
                    <h1 class="hero-title text-reveal">
                        <span>Nền tảng Affiliate Marketing hàng đầu</span>
                    </h1>
                    <p class="hero-description scroll-animate-fade">
                        Kết nối Publisher với Shop, tối ưu hóa doanh thu và xây dựng cộng đồng kinh doanh bền vững. 
                        Giải pháp toàn diện cho mọi thách thức trong affiliate marketing.
                    </p>
                    <div class="hero-buttons stagger-animate">
                        <a href="/register" class="btn btn-primary btn-large btn-enhanced" data-stagger="1">
                            <i class="fas fa-rocket"></i>
                            Bắt đầu ngay
                        </a>
                        <a href="#features" class="btn btn-outline btn-large btn-enhanced" data-stagger="2">
                            <i class="fas fa-play"></i>
                            Tìm hiểu thêm
                        </a>
                    </div>
                </div>
                <div class="hero-visual scroll-animate-right" data-animation="slide-right">
                    <div class="hero-3d-element">
                        <!-- Logo trung tâm -->
                        <div class="center-logo">
                            <div class="logo-container">
                                <i class="fas fa-chart-line logo-icon"></i>
                                <span class="logo-text">Affiliate</span>
                                <span class="logo-subtitle">Marketing</span>
                            </div>
                        </div>
                        
                        <!-- Floating cards bao quanh -->
                        <div class="floating-card card-1 hover-lift-enhanced" data-stagger="1">
                            <i class="fas fa-chart-line"></i>
                            <span>+45%</span>
                            <small>Hiệu suất</small>
                        </div>
                        <div class="floating-card card-2 hover-lift-enhanced" data-stagger="2">
                            <i class="fas fa-users"></i>
                            <span>2.5K+</span>
                            <small>Người dùng</small>
                        </div>
                        <div class="floating-card card-3 hover-lift-enhanced" data-stagger="3">
                            <i class="fas fa-dollar-sign"></i>
                            <span>$1.2M</span>
                            <small>Doanh thu</small>
                        </div>
                        <div class="floating-card card-4 hover-lift-enhanced" data-stagger="4">
                            <i class="fas fa-rocket"></i>
                            <span>100+</span>
                            <small>Dự án</small>
                        </div>
                        <div class="floating-card card-5 hover-lift-enhanced" data-stagger="5">
                            <i class="fas fa-star"></i>
                            <span>4.9</span>
                            <small>Đánh giá</small>
                        </div>
                        <div class="floating-card card-6 hover-lift-enhanced" data-stagger="6">
                            <i class="fas fa-globe"></i>
                            <span>50+</span>
                            <small>Quốc gia</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features scroll-animate section-transition">
        <div class="container">
            <div class="section-header scroll-animate-up" data-animation="slide-up">
                <h2 class="section-title">Tính năng nổi bật</h2>
                <p class="section-description">Khám phá những gì làm nên sự khác biệt của chúng tôi</p>
            </div>
            <div class="features-grid stagger-animate" data-animation="stagger">
                <div class="feature-card card-smooth hover-lift-enhanced" data-stagger="1">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Quản lý Publisher</h3>
                    <p class="feature-description">
                        Dashboard chuyên nghiệp với theo dõi hiệu suất, thanh toán tự động và báo cáo chi tiết
                    </p>
                </div>
                
                <div class="feature-card card-smooth hover-lift-enhanced" data-stagger="2">
                    <div class="feature-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3 class="feature-title">Quản lý Shop</h3>
                    <p class="feature-description">
                        Công cụ quản lý sản phẩm, đơn hàng và chiến dịch marketing hiệu quả
                    </p>
                </div>
                
                <div class="feature-card card-smooth hover-lift-enhanced" data-stagger="3">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="feature-title">Analytics & Báo cáo</h3>
                    <p class="feature-description">
                        Thống kê real-time, báo cáo chi tiết giúp tối ưu hóa chiến lược kinh doanh
                    </p>
                </div>

                <div class="feature-card card-smooth hover-lift-enhanced" data-stagger="4">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Bảo mật cao</h3>
                    <p class="feature-description">
                        Hệ thống bảo mật đa lớp, mã hóa SSL và xác thực 2FA
                    </p>
                </div>
                
                <div class="feature-card card-smooth hover-lift-enhanced" data-stagger="5">
                    <div class="feature-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h3 class="feature-title">Tích hợp API</h3>
                    <p class="feature-description">
                        API mạnh mẽ, tích hợp dễ dàng với các nền tảng và công cụ khác
                    </p>
                </div>

                <div class="feature-card card-smooth hover-lift-enhanced" data-stagger="6">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">Hỗ trợ 24/7</h3>
                    <p class="feature-description">
                        Đội ngũ hỗ trợ chuyên nghiệp sẵn sàng giúp đỡ bạn mọi lúc
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Affiliate Management Section -->
    <section id="affiliate" class="affiliate-section scroll-animate section-transition">
        <div class="container">
            <div class="affiliate-content">
                <div class="affiliate-text scroll-animate-left" data-animation="slide-left">
                    <h2 class="section-title-affiliate">Quản lý Affiliate Marketing</h2>
                    <p class="section-description-affiliate">
                        Hệ thống quản lý affiliate marketing toàn diện, giúp bạn tối ưu hóa hiệu suất và tăng doanh thu
                    </p>
                    
                    <div class="affiliate-features stagger-animate" data-animation="stagger">
                        <div class="affiliate-feature hover-lift-enhanced" data-stagger="1">
                            <div class="feature-check">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Link Tracking & Analytics</h4>
                                <p>Theo dõi hiệu suất link, click rate và conversion rate chi tiết</p>
                            </div>
                        </div>
                        
                        <div class="affiliate-feature hover-lift-enhanced" data-stagger="2">
                            <div class="feature-check">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Commission Management</h4>
                                <p>Quản lý hoa hồng tự động, thanh toán đúng hạn và báo cáo minh bạch</p>
                            </div>
                        </div>
                        
                        <div class="affiliate-feature hover-lift-enhanced" data-stagger="3">
                            <div class="feature-check">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Campaign Optimization</h4>
                                <p>Tối ưu hóa chiến dịch dựa trên dữ liệu thực tế và AI insights</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="affiliate-visual scroll-animate-right" data-animation="slide-right">
                    <div class="affiliate-dashboard card-smooth hover-lift-enhanced">
                        <div class="dashboard-header">
                            <div class="dashboard-title">Affiliate Dashboard</div>
                            <div class="dashboard-stats">
                                <span class="stat">$2,450</span>
                                <span class="stat-label" style="color: #fff;">Today's Revenue</span>
                            </div>
                        </div>
                        <div class="dashboard-chart">
                            <div class="chart-bar hover-scale-enhanced" style="height: 60%"></div>
                            <div class="chart-bar hover-scale-enhanced" style="height: 80%"></div>
                            <div class="chart-bar hover-scale-enhanced" style="height: 45%"></div>
                            <div class="chart-bar hover-scale-enhanced" style="height: 90%"></div>
                            <div class="chart-bar hover-scale-enhanced" style="height: 70%"></div>
                            <div class="chart-bar hover-scale-enhanced" style="height: 85%"></div>
                            <div class="chart-bar hover-scale-enhanced" style="height: 65%"></div>
                        </div>
                        <div class="dashboard-metrics">
                            <div class="metric">
                                <span class="metric-value">1,234</span>
                                <span class="metric-label">Clicks</span>
                            </div>
                            <div class="metric">
                                <span class="metric-value">89</span>
                                <span class="metric-label">Conversions</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about scroll-animate section-transition">
        <div class="container">
            <div class="about-content">
                <div class="about-text scroll-animate-left" data-animation="slide-left">
                    <h2 class="section-title">Về chúng tôi</h2>
                    <p class="about-description">
                        Affiliate Marketing là nền tảng tiếp thị liên kết cung cấp giải pháp toàn diện giúp kết nối
                        Publisher và Shop một cách nhanh chóng và hiệu quả. Chúng tôi cam kết mang đến trải nghiệm 
                        người dùng tối ưu với giao diện hiện đại, thao tác đơn giản và tính năng mạnh mẽ.
                    </p>
                    
                    <div class="about-highlights stagger-animate" data-animation="stagger">
                        <div class="highlight-item hover-lift-enhanced" data-stagger="1">
                            <i class="fas fa-award"></i>
                            <span>5+ năm kinh nghiệm</span>
                        </div>
                        <div class="highlight-item hover-lift-enhanced" data-stagger="2">
                            <i class="fas fa-globe"></i>
                            <span>50+ quốc gia</span>
                        </div>
                        <div class="highlight-item hover-lift-enhanced" data-stagger="3">
                            <i class="fas fa-star"></i>
                            <span>4.9/5 đánh giá</span>
                        </div>
                    </div>
                </div>
                
                <div class="about-image scroll-animate-right" data-animation="slide-right">
                    <div class="about-illustration">
                        <div class="floating-element hover-lift-enhanced">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="particle particle-1 parallax" data-speed="0.2"></div>
                        <div class="particle particle-2 parallax" data-speed="0.3"></div>
                        <div class="particle particle-3 parallax" data-speed="0.4"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer scroll-animate section-transition">
        <div class="container">
            <div class="footer-content stagger-animate" data-animation="stagger">
                <div class="footer-section hover-lift-enhanced" data-stagger="1">
                    <div class="footer-brand">
                        <i class="fas fa-chart-line"></i>
                        <span>Affiliate Marketing</span>
                    </div>
                    <p class="footer-description">
                        Nền tảng affiliate marketing hàng đầu, kết nối Publisher với Shop một cách hiệu quả và bền vững.
                    </p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/ttung180/" class="social-link hover-scale-enhanced"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link hover-scale-enhanced"><i class="fab fa-github"></i></a>
                        <a href="#" class="social-link hover-scale-enhanced"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link hover-scale-enhanced"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="footer-section hover-lift-enhanced" data-stagger="2">
                    <h3 class="footer-title">Liên kết nhanh</h3>
                    <ul class="footer-links">
                        <li><a href="#home" class="hover-scale-enhanced">Trang chủ</a></li>
                        <li><a href="#features" class="hover-scale-enhanced">Tính năng</a></li>
                        <li><a href="#affiliate" class="hover-scale-enhanced">Affiliate</a></li>
                        <li><a href="#about" class="hover-scale-enhanced">Giới thiệu</a></li>
                        <li><a href="/login" class="hover-scale-enhanced">Đăng nhập</a></li>
                    </ul>
                </div>
                
                <div class="footer-section hover-lift-enhanced" data-stagger="3">
                    <h3 class="footer-title">Dịch vụ</h3>
                    <ul class="footer-links">
                        <li><a href="#" class="hover-scale-enhanced">Publisher Program</a></li>
                        <li><a href="#" class="hover-scale-enhanced">Shop Integration</a></li>
                        <li><a href="#" class="hover-scale-enhanced">API Documentation</a></li>
                        <li><a href="#" class="hover-scale-enhanced">Support Center</a></li>
                        <li><a href="#" class="hover-scale-enhanced">Training Resources</a></li>
                    </ul>
                </div>
                
                <div class="footer-section hover-lift-enhanced" data-stagger="4">
                    <h3 class="footer-title">Liên hệ</h3>
                    <ul class="footer-contact">
                        <li><i class="fas fa-envelope"></i> tung18102k5@gmail.com</li>
                        <li><i class="fas fa-phone"></i> +84 968 799 517</li>
                        <li><i class="fas fa-map-marker-alt"></i> Hà Nội, Việt Nam</li>
                        <li><i class="fas fa-clock"></i> Hỗ trợ 24/7</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Affiliate Marketing. Tất cả quyền được bảo lưu.</p>
                <div class="footer-bottom-links">
                    <a href="#" class="hover-scale-enhanced">Chính sách bảo mật</a>
                    <a href="#" class="hover-scale-enhanced">Điều khoản sử dụng</a>
                    <a href="#" class="hover-scale-enhanced">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        // Initialize AOS with enhanced settings
        AOS.init({
            duration: 1200,
            easing: 'ease-out-cubic',
            once: true,
            offset: 150,
            delay: 100
        });

        // Enhanced smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
                    const targetPosition = target.offsetTop - headerHeight - 20;
                    
                    // Use custom smooth scroll function
                    if (window.TTungAffiliate && window.TTungAffiliate.smoothScrollTo) {
                        window.TTungAffiliate.smoothScrollTo(targetPosition, 1000);
                    } else {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Enhanced header scroll effect with smooth transitions
        let ticking = false;
        function updateHeader() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        });

        // Add page load animation
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('page-transition', 'loaded');
            
            // Add reveal animations to elements
            const revealElements = document.querySelectorAll('.reveal-on-scroll');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, { threshold: 0.1 });
            
            revealElements.forEach(element => revealObserver.observe(element));
        });

        // Enhanced mobile menu with smooth animations
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
        }

        // Add enhanced hover effects to interactive elements
        document.addEventListener('DOMContentLoaded', () => {
            // Enhanced button effects
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                    this.style.boxShadow = '0 8px 25px rgba(0, 123, 255, 0.3)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '0 4px 15px rgba(0, 123, 255, 0.2)';
                });
            });

            // Add parallax effect to floating elements
            const parallaxElements = document.querySelectorAll('.parallax');
            let ticking = false;
            
            function updateParallax() {
                const scrolled = window.pageYOffset;
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
        });
    </script>
    
    <!-- Chatbot Widget -->
    @include('chatbot.chatbot')
    
    <!-- Chatbot JS -->
    <script src="{{ asset('js/chatbot/chatbot.js') }}"></script>
</body>

</html>