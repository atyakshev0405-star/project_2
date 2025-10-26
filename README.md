# Canteen Schedule Management System

A web application for automating the formation of canteen visit schedules for student groups.

## System Description

The application allows administrators to manage student groups and automatically generate canteen visit schedules taking into account streams and time intervals. Unregistered users can view the schedule for today and the upcoming week.

## Technical Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache (Open Server)
- **Browser**: Modern browser with HTML5 support

## Installation and Setup

### 1. Database Installation

Import the SQL schema from the `database.sql` file:

```bash
mysql -u root -p < database.sql
```

Or execute the SQL script via phpMyAdmin.

### 2. Database Connection Configuration

Edit the `config/database.php` file and specify the connection parameters:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'canteen_schedule');
```

### 3. Deployment on Open Server

1. Copy the `php-canteen-app` folder to the `domains` directory of your Open Server
2. Restart Open Server
3. Open your browser and navigate to: `http://php-canteen-app/public/`

## Project Structure

```
php-canteen-app/
├── admin/                      # Administrative panel
│   ├── index.php              # Admin panel main page
│   ├── view_schedule.php      # View schedule (admin)
│   ├── export_excel.php       # Export schedule to Excel
│   └── logout.php             # Logout
├── assets/
│   └── css/
│       └── style.css          # Main application styles
├── config/
│   └── database.php           # Database connection settings
├── includes/                   # PHP classes and models
│   ├── Auth.php               # Authentication class
│   ├── Group.php              # Group model
│   ├── Schedule.php           # Schedule model
│   └── ScheduleGenerator.php  # Schedule generator
├── public/                     # Public pages
│   ├── index.php              # Main page (public schedule)
│   ├── login.php              # Login page
│   └── search.php             # Search by groups
├── database.sql               # Database SQL schema
└── README.md                  # Documentation
```

## Administrator Credentials

**Login:** pav313  
**Password:** sip313

> **Important:** After the first login, it is recommended to change the password in the database.

## Functional Features

### For Administrators:

1. **Group Management**
   - Adding new groups
   - Specifying stream (1, 2, 3)
   - Automatic generation of student count (20-30)
   - Deleting groups

2. **Schedule Generation**
   - Forming schedule for the work week (Mon-Fri)
   - Automatic group rotation by time intervals
   - Distribution of groups by streams considering time constraints

3. **Viewing and Export**
   - Viewing schedule for any period
   - Export schedule to Excel

### For Unregistered Users:

1. **Schedule Viewing**
   - Schedule for today
   - Schedule for the upcoming week

2. **Search**
   - Search for meal time by group and date
   - Generating report on lunch for a specific group

## Stream Schedule

- **Stream 1 groups:** 11:35 - 12:15 (interval: 5 minutes, up to 3 groups per slot)
- **Stream 2 groups:** 13:00 - 13:30 (interval: 5 minutes, up to 3 groups per slot)
- **Stream 3 groups:** 14:15 - 14:30 (interval: 5 minutes, up to 3 groups per slot)

## Rotation Algorithm

The system automatically distributes groups so that:
- Each group during the week visits different time intervals
- Groups of the same stream do not overlap in time
- Load on time slots is even

## Database

### Tables:

- **admins** - Administrator accounts
- **groups** - Student groups
- **schedules** - Generated schedules

### Relationships:

- `schedules.group_id` → `groups.id` (CASCADE DELETE)

## Usage Examples

### Adding a New Group

1. Log into the admin panel
2. In the "Add Group" form, enter the name (e.g.: SIP-113/25)
3. Select the stream
4. Click "Add Group"

### Schedule Generation

1. In the "Generate Schedule" form, select the start date (Monday)
2. Select the end date (Friday of the same week)
3. Click "GENERATE SCHEDULE"
4. The schedule will be automatically created considering all rotation rules

### Export to Excel

1. Go to the "View Schedule (Admin)" section
2. Select the date range
3. Click "Export to Excel"
4. The file will be downloaded automatically

## Security

- All passwords are hashed using `password_hash()`
- Protection against SQL injection via PDO prepared statements
- Access rights checking on all administrative pages
- User input sanitization

## Support

If problems occur:
1. Check the database connection settings in `config/database.php`
2. Make sure the database is created and contains the necessary tables
3. Check PHP error logs

## License

Developed for educational purposes.
