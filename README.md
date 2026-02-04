# Department and Faculty Management System

A complete PHP + MySQL system with admin panel and frontend to manage departments and faculty members.

## Features

### Admin Panel
- **Department Management**: Add, edit, and delete departments
- **Faculty Management**: Add, edit, and delete faculty members
- **Focal Person Management**: Assign focal person status to faculty (only one per department)
- **User Rights Management**: Each faculty has a `user_rights` field

### Faculty System
- **Focal Persons**: Can manage News & Updates, Notice Board, Notifications, and Events
- **Normal Faculty**: Limited access to view content
- **Role-based Access Control**: Automatic access control based on user rights

### Frontend
- **Dynamic Navbar**: Displays all departments from database
- **Department Pages**: `dpt.php` uses GET method to display department-specific data
- **Bootstrap-based UI**: Clean, modern, responsive design

## Database Schema

The system includes the following tables:
- `admin` - Admin users
- `departments` - Department information
- `faculty` - Faculty members with `user_rights` and `is_focal_person` fields
- `news` - News & Updates
- `notices` - Notice Board entries
- `events` - Events
- `notifications` - Notifications
- `students` - Student records
- `courses` - Course information

## Installation

1. **Database Setup**:
   ```sql
   -- Run the database_schema.sql file in phpMyAdmin or MySQL command line
   mysql -u root -p < database_schema.sql
   ```

2. **Database Configuration**:
   - Update `db_connect.php` with your database credentials if needed
   - Default: localhost, root, no password, database: university_db

3. **Default Admin Credentials**:
   - Email: `admin@university.edu`
   - Password: `admin123`

## File Structure

```
university/
├── database_schema.sql          # Complete database schema
├── db_connect.php              # Database connection
├── login.php                    # Login system
├── index.php                    # Frontend homepage with dynamic departments
├── dpt.php                      # Department page (uses GET method)
├── admin_dashboard.php          # Admin dashboard
├── admin_departments.php        # Department CRUD
├── admin_faculty.php            # Faculty CRUD with focal person management
├── focal_dashboard.php          # Focal person dashboard (News, Notices, Events, Notifications)
└── README.md                    # This file
```

## Key Features

### Focal Person System
- Only **one focal person per department** can be assigned
- When a new focal person is selected, the previous one is automatically removed
- Focal persons have `user_rights = 'focal_person'`
- Normal faculty have `user_rights = 'normal'`

### Department Pages
- Access via: `dpt.php?id=DEPARTMENT_ID`
- Displays:
  - Department information
  - Faculty members (with focal person badge)
  - News & Updates
  - Notice Board
  - Upcoming Events
  - Notifications

### Admin Functions
- **Add Department**: Code, Name, Description
- **Edit Department**: Update all fields
- **Delete Department**: Removes department and related data
- **Add Faculty**: Name, Email, Phone, Department, Hire Date, Bio, Password, Focal Person status
- **Edit Faculty**: Update all fields including focal person status
- **Delete Faculty**: Removes faculty member

### Focal Person Functions
- **News Management**: Add and delete news posts
- **Notice Board**: Add and delete notices
- **Events**: Add and delete events
- **Notifications**: Add and delete notifications

## Usage

1. **Login as Admin**:
   - Go to `login.php`
   - Select "Admin"
   - Use default credentials

2. **Create Departments**:
   - Navigate to "Departments" in admin panel
   - Click "Add Department"
   - Fill in details and save

3. **Add Faculty**:
   - Navigate to "Faculty" in admin panel
   - Click "Add Faculty"
   - Select department
   - Optionally mark as "Focal Person"
   - Save

4. **Login as Faculty**:
   - Go to `login.php`
   - Select "Faculty"
   - Use faculty email and password

5. **Focal Person Access**:
   - Focal persons can access News, Notices, Events, and Notifications tabs
   - Normal faculty see limited dashboard

6. **View Department Pages**:
   - Navigate to homepage
   - Click "Departments" dropdown
   - Select any department
   - View department-specific content

## Database Triggers

The system includes a trigger to ensure only one focal person per department:
- When a faculty member is set as focal person, all other faculty in the same department are automatically set to normal
- The `user_rights` field is automatically updated

## Security Features

- Password hashing using PHP `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control
- Input sanitization with `htmlspecialchars()`

## Technologies Used

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5.3
- HTML5, CSS3, JavaScript

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Notes

- Ensure PHP sessions are enabled
- Make sure MySQL database is running
- Check file permissions for uploads directory (if needed)
- Default admin password should be changed after first login

## Support

For issues or questions, please contact the system administrator.

