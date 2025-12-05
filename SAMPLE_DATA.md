# Sample Data Inserted

## Test Accounts

### Landlord/Admin Accounts
| Email | Password | Organization | Role |
|-------|----------|--------------|------|
| john.manager@sunsettowers.com | password | Sunset Towers | Landlord |
| sarah.admin@riverside.com | password | Riverside Apartments | Landlord |
| mike@greenvalley.com | password | Green Valley Complex | Landlord |

### Tenant Accounts
| Email | Password | Unit | Organization |
|-------|----------|------|--------------|
| alice.johnson@email.com | password | A-101 | Sunset Towers |
| bob.smith@email.com | password | B-301 | Sunset Towers |
| carol.davis@email.com | password | 2A | Riverside Apartments |
| david.wilson@email.com | password | 105 | Green Valley Complex |
| emma.martinez@email.com | password | 207 | Green Valley Complex |

## Organizations

1. **Sunset Towers**
   - Address: 123 Sunset Boulevard, Los Angeles, CA 90001
   - Admin: john.manager@sunsettowers.com
   - Units: A-101, A-201, B-301, C-401

2. **Riverside Apartments**
   - Address: 456 River Road, Portland, OR 97201
   - Admin: sarah.admin@riverside.com
   - Units: 1B, 2A, 3C

3. **Green Valley Complex**
   - Address: 789 Valley Street, Austin, TX 73301
   - Admin: mike@greenvalley.com
   - Units: 105, 207, 310

## Sample Complaints/Requests

8 maintenance requests have been created with various statuses:
- **Pending**: 4 requests (awaiting action)
- **In Progress**: 3 requests (being worked on)
- **Resolved**: 1 request (completed)

Categories include: Plumbing, Electrical, HVAC, Appliances, General, Structural

## Database Setup

1. Import the database schema:
   ```bash
   mysql -u root -p < maint.sql
   ```

2. Insert sample data:
   ```bash
   Get-Content insert_sample_data.sql | mysql -u root -p
   ```

3. Login and test the application at: `http://localhost/Rent/`

## Notes
- All passwords are hashed using `password_hash()` with PASSWORD_DEFAULT
- The plaintext password for all accounts is: **password**
- JSON files have been removed - all data is now in MySQL
