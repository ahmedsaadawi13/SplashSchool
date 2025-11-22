# SplashSchool - Multi-Tenant School Management System

A complete, secure, and scalable School Management SaaS built with PHP and MySQL.

## Features

### Multi-Tenant Architecture
- Complete tenant isolation
- Subscription-based billing
- Usage tracking and quota enforcement
- Trial period support

### Student Management
- Comprehensive student records
- Document management
- Student categories (Regular, Scholarship, Special Needs)
- Student promotion system
- Photo uploads

### Parent Portal
- View student attendance
- View grades and report cards
- Check homework assignments
- View fee invoices and payment history
- Behavior reports

### Teacher Management
- Teacher profiles and documents
- Class assignments
- Qualification tracking
- Teacher portal access

### Academic Management
- Classes and sections
- Subjects management
- Academic year management
- Timetable/schedule management

### Attendance System
- Daily attendance marking
- Class-wise attendance
- Attendance reports
- Student attendance history
- Multiple status options (Present, Absent, Late, Excused)

### Exams & Grades
- Exam management
- Grade submission
- Automatic grade calculation
- Report card generation
- Subject-wise performance tracking

### Fee Management
- Invoice generation
- Payment recording
- Multiple payment methods
- Outstanding balance tracking
- Payment history
- Fee structure by class

### Behavior & Discipline
- Behavior record tracking
- Positive/negative points system
- Action tracking
- Parent visibility

### Transportation
- Bus route management
- Student route assignments
- Driver information
- Pickup/drop time tracking

### Notifications
- Email notification queue
- Automated alerts
- Homework notifications
- Fee reminders
- Attendance alerts

### Reports
- Student lists
- Attendance reports
- Grade reports
- Fee reports
- CSV export functionality

### REST API
- API key authentication
- Add attendance entries
- Get student information
- Submit grades
- Tenant-scoped access

### User Roles
- **Platform Admin**: Full system control, tenant management
- **School Admin**: Full school management
- **Teacher**: Class management, attendance, grades
- **Parent**: Read-only student portal
- **Accountant**: Fee and payment management
- **Receptionist**: Admissions and inquiries
- **Student Portal**: Read-only access for students

## Technology Stack

- **Backend**: PHP 7.0+ (compatible up to PHP 8.x)
- **Database**: MySQL 5.7+ (InnoDB)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Architecture**: Custom lightweight MVC framework

## Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### PHP Extensions Required
- PDO
- pdo_mysql
- mbstring
- json
- fileinfo
- gd (for image uploads)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/SplashSchool.git
cd SplashSchool
```

### 2. Configure Environment

Copy the environment example file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```
DB_HOST=localhost
DB_NAME=splashschool_db
DB_USER=your_db_user
DB_PASS=your_db_password
```

### 3. Create Database

Create a new MySQL database:

```sql
CREATE DATABASE splashschool_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import the schema:

```bash
mysql -u your_db_user -p splashschool_db < database.sql
```

### 4. Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 storage/uploads
chmod -R 755 storage/logs
chown -R www-data:www-data storage
```

### 5. Configure Web Server

#### Apache Configuration

Create a virtual host or add to your existing config:

```apache
<VirtualHost *:80>
    ServerName splashschool.local
    DocumentRoot /path/to/SplashSchool/public

    <Directory /path/to/SplashSchool/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashschool-error.log
    CustomLog ${APACHE_LOG_DIR}/splashschool-access.log combined
</VirtualHost>
```

Enable mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name splashschool.local;
    root /path/to/SplashSchool/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

### 6. Access the Application

Open your browser and navigate to:
- **Homepage**: http://splashschool.local
- **Login**: http://splashschool.local/auth/login

## Default Credentials

### Platform Admin
- **Username**: admin
- **Password**: admin123

### Demo School Admin
- **Username**: school_admin
- **Password**: admin123

**IMPORTANT**: Change these passwords immediately in production!

## Directory Structure

```
SplashSchool/
├── app/
│   ├── controllers/      # Application controllers
│   ├── models/          # Database models
│   ├── views/           # View templates
│   ├── core/            # Core MVC framework
│   └── helpers/         # Helper classes
├── config/              # Configuration files
├── public/              # Public web directory
│   ├── assets/          # CSS, JS, images
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── index.php        # Application entry point
├── storage/             # Storage directory
│   ├── uploads/         # Uploaded files
│   └── logs/            # Application logs
├── database.sql         # Database schema
├── .env                 # Environment configuration
└── README.md            # This file
```

## API Documentation

### Authentication

All API requests require an API key in the header or query parameter:

**Header:**
```
X-API-KEY: your_api_key_here
```

**Query Parameter:**
```
?api_key=your_api_key_here
```

### Endpoints

#### 1. Add Attendance Entry

**POST** `/api/attendance/add`

**Request Body:**
```json
{
    "student_code": "STU001",
    "date": "2025-01-15",
    "status": "present"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Attendance recorded successfully",
    "data": {
        "student_code": "STU001",
        "date": "2025-01-15",
        "status": "present"
    }
}
```

#### 2. Get Student Info

**GET** `/api/students/get?student_code=STU001`

**Response:**
```json
{
    "success": true,
    "message": "Student retrieved successfully",
    "data": {
        "id": 1,
        "student_code": "STU001",
        "full_name": "John Doe",
        "email": "john@example.com",
        ...
    }
}
```

#### 3. Submit Grade

**POST** `/api/grades/add`

**Request Body:**
```json
{
    "student_code": "STU001",
    "subject_code": "MATH101",
    "exam_id": 1,
    "marks": 85,
    "max_marks": 100
}
```

**Response:**
```json
{
    "success": true,
    "message": "Grade submitted successfully",
    "data": {
        "student_code": "STU001",
        "subject_code": "MATH101",
        "exam_id": 1,
        "marks_obtained": 85,
        "grade": "A"
    }
}
```

### Rate Limiting

API requests are limited to 100 requests per hour per API key.

## Security Features

- Password hashing using `password_hash()`
- CSRF protection on all forms
- Prepared statements for SQL queries
- Strict tenant isolation
- Input validation and sanitization
- XSS prevention with output escaping
- File upload validation (MIME type, size)
- Session management
- API key authentication

## Multi-Tenant Features

### Tenant Isolation
- All data queries are automatically scoped by tenant_id
- Users can only access data from their own tenant
- API requests are tenant-scoped

### Subscription Management
- Trial period: 30 days
- Subscription status: trialing, active, past_due, canceled
- Read-only mode when subscription expires
- Usage tracking and quota enforcement

### Usage Limits (Per Subscription Plan)
- Max students
- Max teachers
- Max classes
- Max storage size
- Feature flags (online payments, advanced reports, etc.)

## Testing Checklist

See TESTING.md for comprehensive testing guidelines.

## Deployment Guide

### Production Checklist

1. **Environment Configuration**
   - Set `APP_ENV=production` in `.env`
   - Use strong `ENCRYPTION_KEY`
   - Disable error display
   - Enable error logging

2. **Security**
   - Change all default passwords
   - Set file permissions correctly (755 for directories, 644 for files)
   - Disable directory browsing
   - Use HTTPS
   - Configure firewall

3. **Database**
   - Use separate database user with limited privileges
   - Enable MySQL query cache
   - Regular backups
   - Optimize tables regularly

4. **Performance**
   - Enable OPcache
   - Use CDN for static assets
   - Enable gzip compression
   - Implement caching where appropriate

5. **Monitoring**
   - Set up error monitoring
   - Monitor disk space (uploads directory)
   - Monitor database size
   - Set up uptime monitoring

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is open-source software licensed under the MIT license.

## Support

For support, email support@splashschool.com or create an issue in the repository.

## Credits

Developed with security, scalability, and simplicity in mind.

---

**Version**: 1.0.0
**Last Updated**: January 2025
