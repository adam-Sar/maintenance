<p align="center">
  <img src="images/maintenancehub-removebg-preview.png" alt="MaintenanceHub Logo" width="120" height="120">
</p>

<h1 align="center">🏢 MaintenanceHub</h1>

<p align="center">
  <strong>A Modern Property Maintenance Request Management System</strong>
</p>

<p align="center">
  <em>Streamline maintenance workflows between landlords and tenants with ease</em>
</p>

<p align="center">
  <a href="#-features">Features</a> •
  <a href="#-demo">Demo</a> •
  <a href="#-installation">Installation</a> •
  <a href="#-usage">Usage</a> •
  <a href="#-tech-stack">Tech Stack</a> •
  <a href="#-team">Team</a>
</p>

---

## 📋 Overview

**MaintenanceHub** is a comprehensive web-based property maintenance management system designed to bridge the communication gap between landlords and tenants. It provides a seamless platform for submitting, tracking, and managing maintenance requests across multiple properties and units.

Whether you're a property manager overseeing dozens of buildings or a tenant needing a quick plumbing fix, MaintenanceHub simplifies the entire maintenance workflow—eliminating phone calls, paperwork, and confusion.

---

## ✨ Features

### 🏗️ For Landlords / Property Managers

| Feature | Description |
|---------|-------------|
| **Multi-Organization Management** | Create and manage multiple properties/organizations from a single dashboard |
| **Unit Management** | Add, edit, and organize units within each organization (apartments, studios, garages, etc.) |
| **Tenant Management** | Approve or reject tenant join requests with full control over unit access |
| **Centralized Request Tracking** | View all maintenance requests across all properties in one place |
| **Status Management** | Update request statuses (Pending → In Progress → Resolved) with one click |
| **Advanced Filtering** | Filter requests by status, category, priority, and organization |
| **Real-Time Statistics** | Dashboard with live counts of total, pending, in-progress, and resolved requests |

### 👤 For Tenants / Residents

| Feature | Description |
|---------|-------------|
| **Easy Organization Discovery** | Browse and join available organizations/properties |
| **Unit Selection** | Choose your specific unit within an organization |
| **Quick Request Submission** | Submit maintenance requests in seconds with category, priority, and description |
| **Request Tracking** | View all your submitted requests and their current status |
| **Multiple Organizations** | Join and manage multiple organizations if you have units in different properties |
| **No Phone Calls Required** | Everything is handled online—submit requests anytime, anywhere |

### 🔐 Authentication & Security

- **Role-Based Access Control**: Separate dashboards and permissions for landlords and tenants
- **Secure Registration**: Email validation, password strength requirements, and duplicate prevention
- **Session Management**: Secure login/logout with session handling
- **Input Validation**: Comprehensive server-side validation with regex patterns
- **SQL Injection Prevention**: Escaped inputs and parameterized queries

### 🎨 User Experience

- **Modern, Responsive Design**: Beautiful UI that works on desktop, tablet, and mobile
- **Intuitive Navigation**: Hamburger menu sidebar for easy access to all features
- **Real-Time Feedback**: Success/error messages for all user actions
- **Priority Indicators**: Visual badges for request priority (Low, Standard, High, Emergency)
- **Status Badges**: Color-coded status indicators for quick scanning
- **Category Icons**: Emoji-enhanced categories for visual clarity

---

## 🖥️ Demo

### Landing Page
The landing page showcases the platform's value proposition for both landlords and tenants, featuring:
- Hero section with compelling CTAs
- Feature highlights with icons
- Statistics and benefits overview
- Smooth scrolling navigation

### Landlord Dashboard
```
📊 Dashboard Overview
├── 🏢 My Organizations (Create/Manage properties)
├── 👥 Manage Tenants (Approve/Reject join requests)
├── 📋 All Requests (Filter, view, and update statuses)
└── 👤 Profile (View account details)
```

### Tenant Dashboard
```
📊 Dashboard Overview
├── 🏢 My Organizations (View joined organizations)
│   └── 🏠 Organization Units (View/select units)
│       └── 🔧 Unit Detail (Submit and track requests)
├── 📋 All Requests (View all your submitted requests)
└── 👤 Profile (View account details)
```

---

## 🚀 Installation

### Prerequisites

- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **Laragon** (recommended for Windows) or XAMPP/WAMP

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/maintenancehub.git
cd maintenancehub
```

### Step 2: Configure Database

1. Create a new MySQL database named `Maintenance`:

```sql
CREATE DATABASE Maintenance;
USE Maintenance;
```

2. Create the required tables:

```sql
-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('landlord', 'tenant') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Organizations Table
CREATE TABLE organizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255),
    description TEXT,
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Units Table
CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
);

-- User-Units Relationship Table
CREATE TABLE user_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organization_id INT NOT NULL,
    unit_id INT NOT NULL,
    status TINYINT DEFAULT 0, -- 0 = pending, 1 = approved
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Complaints/Requests Table
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organization_id INT NOT NULL,
    user_id INT NOT NULL,
    unit_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'standard', 'high', 'emergency') DEFAULT 'standard',
    status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);
```

### Step 3: Configure Database Connection

Edit `db_connect.php` with your database credentials:

```php
<?php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "Maintenance";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
```

### Step 4: Start the Server

If using Laragon:
1. Place the project folder in `C:\laragon\www\`
2. Start Laragon
3. Access via `http://localhost/Rent/` or your configured domain

If using PHP's built-in server:
```bash
php -S localhost:8000
```

---

## 📖 Usage

### Getting Started as a Landlord

1. **Register**: Go to the signup page and select "Landlord / Property Manager"
2. **Create Organization**: Enter your organization/property name during registration
3. **Add Units**: Navigate to your organization and create units (apartments, studios, etc.)
4. **Manage Tenants**: Approve tenant join requests as they come in
5. **Track Requests**: Monitor and update maintenance request statuses

### Getting Started as a Tenant

1. **Register**: Go to the signup page and select "Tenant / Resident"
2. **Join Organization**: Browse available organizations and select one
3. **Select Unit**: Choose your specific unit within the organization
4. **Wait for Approval**: Your landlord will approve your join request
5. **Submit Requests**: Once approved, submit maintenance requests for your unit
6. **Track Progress**: Monitor the status of your requests

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white) | Backend server-side logic |
| ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white) | Relational database |
| ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white) | Modern styling and animations |
| ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black) | Client-side interactivity |
| ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white) | Semantic markup structure |
| ![Font Awesome](https://img.shields.io/badge/Font_Awesome-339AF0?style=flat&logo=fontawesome&logoColor=white) | Icon library |

### Architecture

```
MaintenanceHub/
├── 📄 index.php                 # Landing page
├── 📄 login.php                 # User authentication
├── 📄 signup.php                # User registration
├── 📄 profile.php               # User profile page
│
├── 🔧 db_connect.php            # Database connection
├── 🔧 helpers.php               # Reusable helper functions
│
├── 👤 Tenant Pages
│   ├── tenant_main.php          # Tenant dashboard (organizations)
│   ├── organization_units.php   # Units within an organization
│   ├── unit_detail.php          # Unit detail & request submission
│   ├── join_organization.php    # Join new organization
│   └── all_requests.php         # All tenant requests
│
├── 🏢 Admin/Landlord Pages
│   ├── admin_main.php           # Admin dashboard (organizations)
│   ├── admin_organization_units.php  # Manage organization units
│   ├── admin_manage_tenants.php      # Approve/reject tenants
│   ├── admin_all_requests.php        # All requests with filters
│   ├── admin_unit_requests.php       # Requests per unit
│   └── create_organization.php       # Create new organization
│
├── 🎨 Stylesheets
│   ├── landing.css              # Landing page styles
│   ├── auth.css                 # Login/signup styles
│   ├── units_main.css           # Main dashboard styles
│   ├── unit_detail.css          # Unit detail page styles
│   ├── all_requests.css         # Requests list styles
│   ├── admin_styles.css         # Admin-specific styles
│   ├── join_org.css             # Join organization styles
│   └── profile.css              # Profile page styles
│
└── 🖼️ images/                   # Icons and images
```

---

## 🔒 Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT`
- **Session Security**: Secure session handling with proper destruction on logout
- **Input Sanitization**: All user inputs are escaped using `mysqli_real_escape_string()`
- **XSS Prevention**: All outputs are escaped using `htmlspecialchars()`
- **CSRF Protection**: Form submissions include session validation
- **Access Control**: Role-based redirects prevent unauthorized access
- **Regex Validation**: Name and organization fields validated with pattern matching

---

## 📊 Request Categories

MaintenanceHub supports the following maintenance categories:

| Category | Icon | Description |
|----------|------|-------------|
| Plumbing | 🚰 | Leaks, clogs, water issues |
| Electrical | ⚡ | Outlets, wiring, lighting |
| HVAC | 🌡️ | Heating, ventilation, AC |
| Appliances | 🔌 | Refrigerator, washer, dryer |
| Structural | 🏗️ | Walls, floors, ceilings |
| General | 🔧 | Other maintenance needs |

---

## 📈 Priority Levels

| Priority | Color | Use Case |
|----------|-------|----------|
| Low | 🟢 Green | Minor cosmetic issues |
| Standard | 🔵 Blue | Regular maintenance needs |
| High | 🟠 Orange | Significant issues affecting comfort |
| Emergency | 🔴 Red | Critical issues requiring immediate attention |

---

## 🎯 Request Status Flow

```
┌──────────┐      ┌─────────────┐      ┌──────────┐
│ Pending  │ ───► │ In Progress │ ───► │ Resolved │
└──────────┘      └─────────────┘      └──────────┘
```

- **Pending**: Request submitted, awaiting landlord action
- **In Progress**: Landlord acknowledged, work is ongoing
- **Resolved**: Issue has been fixed and closed

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 👥 Team

<table>
  <tr>
    <td align="center">
      <strong>Michael Osta</strong><br>
      <em>Developer</em>
    </td>
    <td align="center">
      <strong>Adam Saraya</strong><br>
      <em>Developer</em>
    </td>
    <td align="center">
      <strong>Ibrahim Adawi</strong><br>
      <em>Developer</em>
    </td>
    <td align="center">
      <strong>Jaafar Toufaily</strong><br>
      <em>Developer</em>
    </td>
  </tr>
</table>

---

## 📄 License

This project was created as part of a PHP course project.

---

## 🙏 Acknowledgments

- Font Awesome for the beautiful icons
- The PHP and MySQL communities for excellent documentation
- All open-source contributors whose work made this possible

---

<p align="center">
  <strong>© 2025 MaintenanceHub. All rights reserved.</strong>
</p>

<p align="center">
  Made with ❤️ for property managers and tenants everywhere
</p>
