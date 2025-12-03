# Apartment Maintenance Request System

A simple, modern maintenance request management system for apartment complexes built with vanilla PHP and JSON storage.

## 🎯 Features

- **Role-Based Access**
  - Landlords/Property Managers can view and manage maintenance requests
  - Tenants can submit and track their maintenance requests

- **Organization Management**
  - Multiple apartment organizations/complexes support
  - Unit-specific complaint tracking

- **Maintenance Request System**
  - Submit requests with categories (Plumbing, Electrical, HVAC, etc.)
  - Priority levels (Low, Medium, High)
  - Status tracking (Pending, In Progress, Resolved)

## 📁 File Structure

```
Rent/
├── data/
│   ├── organizations.json  # Apartment complexes
│   ├── users.json          # User accounts
│   └── complaints.json     # Maintenance requests
├── login.php               # Login page
├── signup.php              # Registration page
├── admin_dashboard.php     # Landlord dashboard
├── user_dashboard.php      # Tenant dashboard
├── helpers.php             # Helper functions
├── auth.css                # Auth page styles
├── admin_styles.css        # Admin dashboard styles
└── user_styles.css         # User dashboard styles
```

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- Web server (Apache, Nginx, or PHP built-in server)

### Installation

1. Copy all files to your web directory (e.g., `c:\laragon\www\Rent`)

2. Make sure the `data/` directory is writable:
   ```bash
   chmod 755 data/
   ```

3. Access the application:
   ```
   http://localhost/Rent/signup.php
   ```

## 👤 Demo Accounts

### Landlord Account
- **Email:** admin@sunsetapts.com
- **Password:** password
- **Organization:** Sunset Apartments

### Tenant Accounts
- **Email:** sarah.j@email.com
- **Password:** password
- **Unit:** A-201 (Sunset Apartments)

- **Email:** mike.t@email.com
- **Password:** password
- **Unit:** B-105 (Sunset Apartments)

## 🏢 Available Organizations

1. **Sunset Apartments** - 123 Main Street, Downtown (24 units)
2. **Green Valley Residences** - 456 Oak Avenue, Midtown (18 units)
3. **Riverside Towers** - 789 River Road, Uptown (36 units)
4. **Harbor View Complex** - 321 Harbor Blvd, Waterfront (42 units)

## 📝 How to Use

### For Landlords:
1. Login with landlord credentials
2. View all maintenance requests from your organization
3. Update request status (Pending → In Progress → Resolved)
4. Monitor statistics and pending items

### For Tenants:
1. Create an account or login
2. Select your organization and unit number during signup
3. Submit maintenance requests with details
4. Track the status of your requests

## 🎨 Features

- **Modern UI Design**
  - Clean, professional teal/blue gradient theme
  - Responsive design for mobile and desktop
  - Smooth animations and transitions
  - Glassmorphism effects

- **Data Storage**
  - JSON-based file storage (no database required)
  - Automatic ID generation
  - Password hashing for security

- **Security**
  - Session-based authentication
  - Password hashing with bcrypt
  - Role-based access control
  - Input validation and sanitization

## 🔧 Customization

### Adding New Organizations
Edit `data/organizations.json`:
```json
{
    "id": 5,
    "name": "Your Apartment Name",
    "address": "Your Address",
    "total_units": 30,
    "admin_email": "admin@yourapt.com"
}
```

### Complaint Categories
Edit the category dropdown in `user_dashboard.php` to add/remove categories.

## 📊 Data Format

All data is stored in JSON format for easy management and portability. No database setup required!

## 🎯 Future Enhancements

- Image upload for maintenance requests
- Email notifications
- Maintenance history reports
- Priority sorting and filtering
- Search functionality
- Database migration option

## 📄 License

This project is open source and available for educational purposes.

## 🤝 Support

For issues or questions, please check the code comments or modify as needed for your specific requirements.
