# Leave Management System Setup

## Overview
The Leave Management System allows employees to apply for leaves and administrators to approve or reject them. The system includes:

1. **Employee Features:**
   - Apply for different types of leaves (Sick, Casual, Emergency, Annual, Maternity, Paternity)
   - View leave application history
   - Edit pending applications

2. **Admin Features:**
   - View all leave applications
   - Approve or reject applications
   - Add comments to decisions
   - Manage leave records

## Database Setup

### 1. Run Migration
First, ensure your database is configured in `.env`, then run:
```bash
php artisan migrate
```

This will create the `leaves` table with the following structure:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `employee_name` - Employee name
- `leave_type` - Type of leave (enum)
- `from_date` - Start date
- `to_date` - End date
- `days_hours` - Number of days/hours
- `day_type` - Full Day, Half Day, or Hours
- `reason` - Reason for leave
- `status` - Applied, Approved, or Rejected
- `approved_by` - Admin who approved/rejected
- `approved_at` - Timestamp of approval
- `admin_comments` - Admin comments
- `created_at` / `updated_at` - Laravel timestamps

## Features

### Navigation
- Access the Leave Management system from the sidebar: **Leave**
- The route is: `/superadmin/leaves`

### Apply for Leave
1. Click "Apply Leave" button
2. Fill in the form:
   - Select Employee
   - Choose Leave Type
   - Set From and To dates (system auto-calculates days)
   - Enter Days/Hours
   - Select Day Type
   - Provide reason
3. Submit the application

### Admin Approval Workflow
1. View all applications in the main table
2. For "Applied" status leaves:
   - Click **Approve** (green button) or **Reject** (red button)
   - Add optional comments
   - Submit decision

### Status Types
- **Applied** (Yellow badge) - Pending approval
- **Approved** (Green badge) - Approved by admin
- **Rejected** (Red badge) - Rejected by admin

## API Routes

The following routes are available:

- `GET /superadmin/leaves` - View all leaves (index)
- `POST /superadmin/leaves` - Create new leave application
- `PUT /superadmin/leaves/{leave}` - Update leave application
- `PUT /superadmin/leaves/{leave}/approve` - Approve/reject leave
- `DELETE /superadmin/leaves/{leave}` - Delete leave application

## File Locations

### Controller
- `app/Http/Controllers/SuperAdmin/LeaveController.php`

### Model
- `app/Models/Leave.php`

### Views
- `resources/views/superadmin/leaves/leave.blade.php`

### Routes
- `routes/superadmin.php` (lines around 339-347)

### Migration
- `database/migrations/YYYY_MM_DD_HHMMSS_create_leaves_table.php`

## Testing the System

1. **Start the server:**
   ```bash
   php artisan serve
   ```

2. **Access the application:**
   - Navigate to your admin panel
   - Go to the "Leave" section from the sidebar

3. **Test workflow:**
   - Apply for a leave (ensure users exist in database)
   - View the application in the table
   - Approve/reject as admin
   - Verify status changes

## Sample Data

If you need to test with sample data, you can manually insert users and leaves into the database:

```sql
-- Insert sample users (if not existing)
INSERT INTO users (name, email, password, created_at, updated_at) VALUES 
('John Doe', 'john@example.com', '$2y$10$hash', NOW(), NOW()),
('Jane Smith', 'jane@example.com', '$2y$10$hash', NOW(), NOW());

-- Insert sample leaves
INSERT INTO leaves (user_id, employee_name, leave_type, from_date, to_date, days_hours, reason, status, created_at, updated_at) VALUES 
(1, 'John Doe', 'Sick Leave', '2024-12-24', '2024-12-24', 1, 'Fever and cold', 'Approved', '2024-12-23 10:00:00', '2024-12-23 14:30:00'),
(2, 'Jane Smith', 'Casual Leave', '2024-12-10', '2024-12-10', 1, 'Personal work', 'Applied', '2024-12-09 09:15:00', '2024-12-09 09:15:00');
```

## JavaScript Features

The system includes JavaScript for:
- Auto-calculating days between from and to dates
- Modal handling for approve/reject actions
- Form validation and user experience enhancements

## Troubleshooting

1. **Database Connection Issues:**
   - Check `.env` file database configuration
   - Ensure MySQL/database server is running

2. **Route Not Found:**
   - Clear route cache: `php artisan route:clear`
   - Check if route is properly defined in `routes/superadmin.php`

3. **View Not Found:**
   - Ensure view file exists at correct path
   - Check case sensitivity in file names

4. **Permission Issues:**
   - Ensure proper middleware is applied to routes
   - Check user authentication and permissions

## Future Enhancements

Potential improvements to consider:
1. Leave balance tracking per employee
2. Leave calendar view
3. Email notifications for applications and approvals
4. Leave types configuration from admin panel
5. Reporting and analytics
6. Employee self-service portal
7. Leave policy configuration
8. Integration with HR systems

---

The leave management system is now fully functional and ready to use!