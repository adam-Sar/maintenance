<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaintenanceHub - Streamline Your Apartment Maintenance Requests</title>
    <meta name="description" content="Simplify maintenance management with our powerful platform. Track requests, manage work orders, and keep tenants happy with seamless communication.">
    <link rel="stylesheet" href="landing.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="logo">🏢</div>
                <span class="brand-name">MaintenanceHub</span>
            </div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#dashboard">Dashboard</a></li>
                <li><a href="#benefits">Benefits</a></li>
            </ul>
            <div class="nav-actions">
                <a href="login.php" class="btn-signin">Sign In</a>
                <a href="signup.php" class="btn-getstarted">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Streamline Your<br>
                    <span class="highlight">Apartment Maintenance</span><br>
                    Requests
                </h1>
                <p class="hero-subtitle">
                    Simplify maintenance management with our powerful platform. Track requests, manage work orders, and keep tenants happy with seamless communication.
                </p>
                <div class="hero-actions">
                    <a href="signup.php" class="btn-primary">Start Free Trial →</a>
                    <button class="btn-secondary">Watch Demo</button>
                </div>
                <div class="hero-features">
                    <span>✓ 14-day free trial</span>
                    <span>✓ No credit card required</span>
                </div>
            </div>
            <div class="hero-preview">
                <div class="preview-card">
                    <div class="preview-header">
                        <h3>Recent Requests</h3>
                        <span class="badge-live">Live</span>
                    </div>
                    <div class="preview-items">
                        <div class="preview-item">
                            <div class="item-icon orange">🚰</div>
                            <div class="item-content">
                                <h4>Leaky faucet - Unit 4B</h4>
                                <p>Submitted 2 hours ago</p>
                            </div>
                            <span class="status-badge progress">In Progress</span>
                        </div>
                        <div class="preview-item">
                            <div class="item-icon green">❄️</div>
                            <div class="item-content">
                                <h4>AC repair - Unit 2A</h4>
                                <p>Submitted 1 day ago</p>
                            </div>
                            <span class="status-badge completed">Completed</span>
                        </div>
                        <div class="preview-item">
                            <div class="item-icon red">⚡</div>
                            <div class="item-content">
                                <h4>Electrical issue - Unit 1C</h4>
                                <p>Submitted 3 hours ago</p>
                            </div>
                            <span class="status-badge urgent">Urgent</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-container">
            <div class="section-header">
                <h2>Everything You Need to Manage Maintenance</h2>
                <p>Our comprehensive platform provides all the tools you need to efficiently handle apartment maintenance requests from submission to completion.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon blue">📱</div>
                    <h3>Easy Request Submission</h3>
                    <p>Tenants can submit maintenance requests instantly through our mobile-friendly interface with photos and detailed descriptions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon purple">📋</div>
                    <h3>Smart Work Order Management</h3>
                    <p>Automatically prioritize and assign work orders to the right maintenance staff based on urgency and expertise.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon teal">💬</div>
                    <h3>Real-time Communication</h3>
                    <p>Keep tenants informed with real-time updates and two-way messaging throughout the maintenance process.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Preview Section -->
    <section class="dashboard-preview" id="dashboard">
        <div class="dashboard-container">
            <div class="section-header">
                <h2>Powerful Dashboard for Complete Control</h2>
                <p>Get a bird's-eye view of all maintenance activities with our intuitive dashboard.</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">📊</div>
                    <div class="stat-info">
                        <div class="stat-number">247</div>
                        <div class="stat-label">Total Requests</div>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <div class="stat-number">189</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon">🔧</div>
                    <div class="stat-info">
                        <div class="stat-number">42</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <div class="stat-number">16</div>
                        <div class="stat-label">Urgent</div>
                    </div>
                </div>
            </div>
            <div class="activity-section">
                <h3>Recent Activity</h3>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-avatar">👤</div>
                        <div class="activity-content">
                            <h4>John Smith - Unit 3B</h4>
                            <p>Submitted: Kitchen sink disposal not working</p>
                        </div>
                        <span class="activity-badge pending">Pending</span>
                        <span class="activity-time">5 min ago</span>
                    </div>
                    <div class="activity-item">
                        <div class="activity-avatar">👤</div>
                        <div class="activity-content">
                            <h4>Mike Johnson - Maintenance</h4>
                            <p>Completed: HVAC filter replacement - Unit 5A</p>
                        </div>
                        <span class="activity-badge completed">Completed</span>
                        <span class="activity-time">1 hour ago</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <div class="benefits-container">
            <div class="benefits-content">
                <h2>Why Property Managers Choose MaintenanceHub</h2>
                <div class="benefit-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Reduce Response Time by 75%</h4>
                            <p>Automated workflows and instant notifications ensure maintenance requests are addressed immediately.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Increase Tenant Satisfaction</h4>
                            <p>Keep tenants happy with transparent communication and faster resolution times.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">✓</div>
                        <div class="benefit-text">
                            <h4>Save Administrative Costs</h4>
                            <p>Eliminate paperwork and streamline processes to reduce operational overhead.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="analytics-preview">
                <div class="analytics-card">
                    <h3>Performance Analytics</h3>
                    <div class="metric">
                        <div class="metric-label">Average Resolution Time</div>
                        <div class="metric-value">2.3 days</div>
                        <div class="progress-bar">
                            <div class="progress-fill blue" style="width: 76%;"></div>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Tenant Satisfaction</div>
                        <div class="metric-value">96%</div>
                        <div class="progress-bar">
                            <div class="progress-fill green" style="width: 96%;"></div>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="metric-label">Cost Reduction</div>
                        <div class="metric-value">45%</div>
                        <div class="progress-bar">
                            <div class="progress-fill teal" style="width: 45%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="cta-container">
            <h2>Ready to Transform Your Maintenance Management?</h2>
            <p>Join thousands of property managers who have streamlined their operations with MaintenanceHub.</p>
            <div class="cta-actions">
                <a href="signup.php" class="btn-cta-primary">Start Free Trial</a>
                <button class="btn-cta-secondary">Schedule Demo</button>
            </div>
            <p class="cta-note">No setup fees • 14-day free trial • Cancel anytime</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="footer-logo">
                    <div class="logo">🏢</div>
                    <span class="brand-name">MaintenanceHub</span>
                </div>
                <p>The complete solution for apartment maintenance request management.</p>
                <div class="social-links">
                    <a href="#" aria-label="Twitter">🐦</a>
                    <a href="#" aria-label="LinkedIn">💼</a>
                    <a href="#" aria-label="GitHub">💻</a>
                </div>
            </div>
            <div class="footer-links">
                <div class="footer-column">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="signup.php">Pricing</a></li>
                        <li><a href="#dashboard">Integrations</a></li>
                        <li><a href="#benefits">API</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#benefits">About</a></li>
                        <li><a href="#benefits">Careers</a></li>
                        <li><a href="#benefits">Press</a></li>
                        <li><a href="#benefits">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#features">Help Center</a></li>
                        <li><a href="README.md">Documentation</a></li>
                        <li><a href="#benefits">Community</a></li>
                        <li><a href="#benefits">Status</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 MaintenanceHub. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
