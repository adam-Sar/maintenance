# 🧪 Test Users Guide

## How to Access the System

1. **Make sure Laragon is running**
2. **Open your browser and go to:**
   ```
   http://localhost/Rent/login.php
   ```

## 👥 Available Test Accounts

### 🏗️ LANDLORD ACCOUNTS (Can manage complaints)

#### Landlord 1 - Sunset Apartments
- **Email:** `admin@sunsetapts.com`
- **Password:** `password`
- **Organization:** Sunset Apartments (24 units)
- **Address:** 123 Main Street, Downtown
- **Can see:** Complaints from Sarah Johnson & Mike Thompson

#### Landlord 2 - Green Valley Residences
- **Email:** `manager@greenvalley.com`
- **Password:** `password`
- **Organization:** Green Valley Residences (18 units)
- **Address:** 456 Oak Avenue, Midtown
- **Can see:** Complaints from Robert Chen

---

### 👤 TENANT ACCOUNTS (Can submit complaints)

#### Tenant 1 - Sarah Johnson
- **Email:** `sarah.j@email.com`
- **Password:** `password`
- **Organization:** Sunset Apartments
- **Unit:** A-201
- **Has submitted:** 2 complaints (Kitchen faucet leak & Heating issue)

#### Tenant 2 - Mike Thompson
- **Email:** `mike.t@email.com`
- **Password:** `password`
- **Organization:** Sunset Apartments
- **Unit:** B-105
- **Has submitted:** 2 complaints (Light fixture & Window screen)

#### Tenant 3 - Robert Chen
- **Email:** `robert.c@email.com`
- **Password:** `password`
- **Organization:** Green Valley Residences
- **Unit:** 301
- **Has submitted:** 1 complaint (Refrigerator noise - RESOLVED)

---

## 🎯 Testing Scenarios

### Test as Landlord:
1. Login with `admin@sunsetapts.com` / `password`
2. See dashboard with 4 complaints from your tenants
3. View statistics (Total, Pending, In Progress, Resolved)
4. Update complaint status from Pending → In Progress → Resolved
5. See real-time updates

### Test as Tenant:
1. Login with `sarah.j@email.com` / `password`
2. See your 2 existing complaints
3. Submit a new maintenance request
4. Track status of your requests
5. See when landlord updates the status

### Create New Account:
1. Go to signup page
2. Choose role (Landlord or Tenant)
3. Select organization from dropdown
4. If tenant, enter unit number
5. Complete registration and login

---

## 📊 Existing Sample Data

### Organizations Available:
1. 🏢 Sunset Apartments - 123 Main Street, Downtown (24 units)
2. 🏢 Green Valley Residences - 456 Oak Avenue, Midtown (18 units)
3. 🏢 Riverside Towers - 789 River Road, Uptown (36 units)
4. 🏢 Harbor View Complex - 321 Harbor Blvd, Waterfront (42 units)

### Complaint Categories:
- 🚰 Plumbing
- ⚡ Electrical
- 🌡️ HVAC (Heating/Cooling)
- 🔌 Appliances
- 🏗️ Structural
- 🔧 General Maintenance

### Priority Levels:
- 🔴 High (Red badge)
- 🟡 Medium (Yellow badge)
- 🔵 Low (Blue badge)

### Status Types:
- ⏳ Pending (Yellow)
- 🔧 In Progress (Blue)
- ✅ Resolved (Green)

---

## 🚀 Quick Start

### Option 1: Test as Admin
```
URL: http://localhost/Rent/login.php
Email: admin@sunsetapts.com
Password: password
```
→ You'll see the admin dashboard with all complaints

### Option 2: Test as Tenant
```
URL: http://localhost/Rent/login.php
Email: mike.t@email.com
Password: password
```
→ You'll see the tenant dashboard to submit requests

---

## 💡 Tips

- All passwords are: `password`
- Data is stored in JSON files (easy to edit manually if needed)
- Refresh the page after updating complaint status to see changes
- You can create unlimited new accounts via signup page
- Each tenant must belong to an organization and have a unit number
