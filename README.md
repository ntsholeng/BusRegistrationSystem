# Bus Registration System
**Strive High Secondary School - ICT3715 Project**

## Project Overview
Complete online bus registration system with MIS reporting capabilities for Strive High Secondary School in Centurion, Gauteng. This system replaces the paper-based registration process with a modern web-based solution.

## System Requirements Met
- ✅ Web-based and mobile-friendly interface
- ✅ Complete MIS reporting system with 4 reports
- ✅ Search/drill-down functionality in all reports  
- ✅ 3NF normalized database design
- ✅ Parent authentication and dashboard
- ✅ Admin dashboard for management
- ✅ Backup and recovery system
- ✅ Email notification system
- ✅ Waiting list management

## File Structure
```
bus-registration-system/
├── README.md
├── setup.sql                    # Database structure
├── sample_data.sql             # Sample data for testing
├── db_connect.php              # Database connection
├── auth.php                    # Authentication handler
├── parent_login.html           # Parent login interface
├── parent_dashboard.html       # Parent dashboard (from artifacts)
├── mis_dashboard.html          # MIS reports dashboard (from artifacts)
├── parent_interface.html       # Registration form (from artifacts)
├── admin_dashboard.php         # Admin interface
├── mis_reports_handler.php     # MIS report backend
├── submit_application.php      # Application processing
├── move_from_waiting_list.php  # Waiting list management
└── backups/                    # Backup storage directory
```

## Installation Instructions

### 1. Database Setup
```sql
-- Create database and tables
mysql -u username -p < setup.sql

-- Add sample data for testing
mysql -u username -p < sample_data.sql
```

### 2. Configuration
1. Update `db_connect.php` with your database credentials:
```php
$user = "your_mysql_username";
$password = "your_mysql_password";
```

2. Create `backups/` directory with write permissions:
```bash
mkdir backups
chmod 755 backups
```

### 3. Web Server Setup
- Place files in your web server directory (htdocs for XAMPP)
- Ensure PHP and MySQL are running
- Access via `http://localhost/bus-registration-system/`

## Usage Guide

### Parent Access
1. **Registration**: Use `parent_interface.html` to register new parents
2. **Login**: Use `parent_login.html` with credentials
3. **Demo Login**: 
   - Email: mary.smith@email.com
   - Password: password123

### Admin Access
1. **Dashboard**: Access via `admin_dashboard.php`
2. **MIS Reports**: View via `mis_dashboard.html`
3. **Waiting List**: Manage through admin dashboard

## MIS Reports Included

### Daily Reports
1. **Waiting List Report**: Shows all learners awaiting approval
2. **Transport Users Report**: Shows approved daily users by route

### Weekly Reports  
3. **Weekly Summary**: Route utilization and capacity analysis

### Additional Report
4. **Route Capacity Analysis**: Strategic planning and recommendations

## Key Features

### Database Design (3NF)
- **11 Tables**: Parent, Learner, Route, Bus, Application, etc.
- **Foreign Keys**: Proper relationships maintained
- **Data Integrity**: Constraints and validation

### Authentication System
- Secure parent login with sessions
- Password validation and registration
- Dashboard access control

### MIS Reporting
- Real-time data visualization
- Interactive charts and tables
- Search and filtering capabilities
- Export functionality

### Admin Features
- Waiting list management
- Application approval workflow
- Backup and recovery tools
- Email notification system

## Assessment Compliance

This system meets all ICT3715 requirements:
- Complete database design with proper normalization
- All required MIS reports with search functionality
- Web-based interface that's mobile-friendly
- Backup and recovery implementation
- Business logic for bus capacity and waiting lists
- Professional documentation and code organization

## Technical Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache/Nginx with PHP support

## Demo Data
The system includes sample data for testing:
- 10 Parents with login credentials (password123)
- 10 Learners across different grades
- 3 Bus routes (Rooihuiskraal, Wierdapark, Centurion)
- Sample applications and waiting list entries

## Support
For issues or questions regarding this ICT3715 project implementation, refer to the comprehensive code comments and system documentation provided in the artifacts above.