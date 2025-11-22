# SplashSchool Testing Checklist

## Authentication & Authorization Tests

### Login Tests
- [ ] Successful login with valid credentials
- [ ] Failed login with invalid username
- [ ] Failed login with invalid password
- [ ] Failed login with inactive account
- [ ] Failed login with expired subscription
- [ ] Session creation upon successful login
- [ ] Last login timestamp update
- [ ] CSRF token validation on login form

### Registration Tests
- [ ] Successful school registration
- [ ] Duplicate subdomain rejection
- [ ] Duplicate username rejection
- [ ] Email validation
- [ ] Password confirmation matching
- [ ] Tenant creation
- [ ] Admin user creation
- [ ] Trial period initialization
- [ ] API key generation

### Logout Tests
- [ ] Session destruction
- [ ] Redirect to login page
- [ ] Cannot access protected pages after logout

## Tenant Isolation Tests

### Data Isolation
- [ ] Users can only see data from their tenant
- [ ] Students query filtered by tenant_id
- [ ] Teachers query filtered by tenant_id
- [ ] Classes query filtered by tenant_id
- [ ] Attendance query filtered by tenant_id
- [ ] Grades query filtered by tenant_id
- [ ] Invoices query filtered by tenant_id

### Cross-Tenant Access Prevention
- [ ] Cannot access another tenant's students via direct URL
- [ ] Cannot access another tenant's data via API
- [ ] Cannot modify another tenant's records
- [ ] Session tenant_id validation

## CRUD Operations Tests

### Student Management
- [ ] Create new student
- [ ] Duplicate student code prevention
- [ ] View student details
- [ ] Edit student information
- [ ] Upload student photo
- [ ] Student photo validation (type, size)
- [ ] Soft delete student (status change)
- [ ] Search students by code/name/email
- [ ] List students with pagination

### Teacher Management
- [ ] Create new teacher
- [ ] Duplicate teacher code prevention
- [ ] View teacher details
- [ ] Edit teacher information
- [ ] Link teacher to user account
- [ ] Upload teacher documents

### Parent Management
- [ ] Create parent record
- [ ] Link parent to student
- [ ] Unlink parent from student
- [ ] Enable parent portal access
- [ ] Parent login with credentials

### Class Management
- [ ] Create class
- [ ] Create section
- [ ] Assign subjects to class
- [ ] Assign teacher to subject
- [ ] List classes by academic year

## Attendance Tests

### Marking Attendance
- [ ] Mark attendance for entire class
- [ ] Update existing attendance
- [ ] Prevent duplicate attendance entries
- [ ] Validate attendance status values
- [ ] Record who marked attendance

### Attendance Reports
- [ ] Daily attendance summary
- [ ] Student attendance history
- [ ] Date range filtering
- [ ] Class filtering
- [ ] Export to CSV

## Grades & Exams Tests

### Exam Management
- [ ] Create exam
- [ ] Assign subjects to exam
- [ ] Set max marks and pass marks

### Grade Submission
- [ ] Submit grade for student
- [ ] Update existing grade
- [ ] Automatic grade calculation (A, B, C, D, F)
- [ ] Prevent duplicate grade entries
- [ ] View student report card

## Fee Management Tests

### Invoice Management
- [ ] Generate unique invoice number
- [ ] Create invoice for student
- [ ] Invoice number format validation
- [ ] Link invoice to class and academic year

### Payment Recording
- [ ] Record payment against invoice
- [ ] Update invoice paid amount
- [ ] Automatic balance calculation
- [ ] Status update (unpaid → partially_paid → paid)
- [ ] Payment method validation
- [ ] Multiple payments per invoice

### Fee Reports
- [ ] Outstanding invoices list
- [ ] Payment history
- [ ] Total collected amount
- [ ] Filter by student/class/status

## Subscription & Billing Tests

### Usage Tracking
- [ ] Student count tracking
- [ ] Teacher count tracking
- [ ] Class count tracking
- [ ] Storage size tracking

### Quota Enforcement
- [ ] Block adding students when limit reached
- [ ] Block adding teachers when limit reached
- [ ] Warning when approaching limits

### Subscription Status
- [ ] Trial period expiration check
- [ ] Read-only mode when subscription expired
- [ ] Active subscription allows full access
- [ ] Platform admin bypasses subscription checks

## API Tests

### Authentication
- [ ] Valid API key acceptance
- [ ] Invalid API key rejection
- [ ] Missing API key rejection
- [ ] Tenant identification from API key

### Endpoints
- [ ] POST /api/attendance/add
- [ ] GET /api/students/get
- [ ] POST /api/grades/add
- [ ] Proper JSON response format
- [ ] Error handling
- [ ] HTTP status codes

## File Upload Tests

### Security
- [ ] MIME type validation
- [ ] File size limit enforcement
- [ ] File extension validation
- [ ] Unique filename generation
- [ ] Directory traversal prevention

### Storage
- [ ] Files saved to correct directory
- [ ] File metadata recorded in database
- [ ] Old file deletion on update
- [ ] Storage quota tracking

## Security Tests

### CSRF Protection
- [ ] All POST forms include CSRF token
- [ ] Invalid CSRF token rejection
- [ ] Token validation on form submission

### SQL Injection Prevention
- [ ] All queries use prepared statements
- [ ] No raw SQL with user input
- [ ] Parameter binding for all variables

### XSS Prevention
- [ ] Output escaping in views
- [ ] htmlspecialchars() usage
- [ ] User input sanitization

### Password Security
- [ ] Passwords hashed with password_hash()
- [ ] Password verification with password_verify()
- [ ] Minimum password length enforcement
- [ ] No plaintext passwords in database

### Input Validation
- [ ] Required field validation
- [ ] Email format validation
- [ ] Date format validation
- [ ] Numeric validation
- [ ] String length validation

## Role-Based Access Control Tests

### Platform Admin
- [ ] Can view all tenants
- [ ] Can access platform dashboard
- [ ] Bypasses subscription checks

### School Admin
- [ ] Full access to school data
- [ ] Can manage students, teachers, classes
- [ ] Can access all reports
- [ ] Cannot access other tenants

### Teacher
- [ ] Can mark attendance
- [ ] Can submit grades
- [ ] Can view assigned classes
- [ ] Cannot access admin functions

### Parent
- [ ] Read-only access to student data
- [ ] Can view attendance
- [ ] Can view grades
- [ ] Can view invoices
- [ ] Cannot modify any data

### Accountant
- [ ] Can manage fees and invoices
- [ ] Can record payments
- [ ] Can access financial reports
- [ ] Cannot access academic data

## Report Tests

### CSV Export
- [ ] Students list export
- [ ] Attendance report export
- [ ] Grades report export
- [ ] Fee report export
- [ ] Proper CSV formatting
- [ ] Correct headers

### Filtering
- [ ] Date range filtering
- [ ] Class filtering
- [ ] Status filtering
- [ ] Student filtering

## Performance Tests

### Database Queries
- [ ] Indexes on frequently queried columns
- [ ] No N+1 query problems
- [ ] Efficient joins
- [ ] Pagination for large datasets

### Page Load
- [ ] Homepage loads < 2 seconds
- [ ] Dashboard loads < 3 seconds
- [ ] Student list loads < 3 seconds

## Browser Compatibility

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile responsive design

## Error Handling

### User-Friendly Messages
- [ ] Validation errors display clearly
- [ ] Success messages after actions
- [ ] Error messages for failed operations

### Logging
- [ ] PHP errors logged to file
- [ ] Database errors logged
- [ ] Application errors logged
- [ ] No sensitive data in logs

## Notification Tests

### Email Queue
- [ ] Notifications stored in database
- [ ] Proper email format
- [ ] Recipient email validation
- [ ] Notification status tracking

## Edge Cases

### Empty States
- [ ] No students found message
- [ ] No classes found message
- [ ] No attendance records message
- [ ] Empty dashboard stats

### Boundary Conditions
- [ ] Maximum file size upload
- [ ] Minimum password length
- [ ] Date validation (past/future)
- [ ] Numeric range validation

## Regression Tests

After any code changes, verify:
- [ ] Login still works
- [ ] Student CRUD operations work
- [ ] Attendance marking works
- [ ] Fee management works
- [ ] Reports generate correctly
- [ ] API endpoints respond correctly

## Load Testing

- [ ] 100 concurrent users
- [ ] 1000+ students in database
- [ ] 10,000+ attendance records
- [ ] Multiple file uploads simultaneously

## Backup & Recovery

- [ ] Database backup procedure tested
- [ ] File backup procedure tested
- [ ] Restore from backup tested
- [ ] Data integrity after restore

---

**Testing completed by**: _________________
**Date**: _________________
**Version tested**: _________________
