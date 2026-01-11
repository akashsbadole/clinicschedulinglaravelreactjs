# 🏥 Medical Clinic Billing and Scheduling System

A comprehensive Laravel + React application for managing medical clinic operations, including patient management, appointment scheduling, billing, medical records, and reporting.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### 🧑‍⚕️ Patient Management

- Complete patient profiles with medical history
- Document attachment support (PDF, images)
- Visit history tracking
- Advanced search by phone, name, or ID
- Blood group, allergies, and chronic conditions tracking

### 👨‍⚕️ Doctor Management

- Doctor availability and weekly schedules
- Consultation types (online/offline)
- Custom fees per service
- Leave management system

### 📅 Appointment System

- Time-slot based booking system
- Doctor-wise calendar views
- Auto-slot generation
- Waiting list management
- Appointment reminders and notifications
- Walk-in appointment support
- Configurable overbooking rules
- Appointment states: Booked → Checked-in → In Consultation → Completed → Billed

### 💰 Billing & Finance

- Multiple services per visit
- Medicine and lab test charges
- Discounts and taxes support
- Insurance integration (basic)
- Refunds and adjustments
- Advance payment tracking
- PDF invoice generation

### 💳 Payment Management

- Multiple payment methods
- Manual payment processing
- Payment tracking and history
- Refund management

### 📊 Reports & Analytics

**Financial Reports:**

- Daily/monthly revenue tracking
- Doctor-wise earnings analysis
- Payment method breakdown

**Operational Reports:**

- No-show rate analysis
- Doctor utilization metrics
- Appointment cancellation rates

### 🧾 Medical Records (EMR)

- Doctor consultation notes
- Prescription management
- Diagnosis tracking
- Follow-up scheduling
- Document attachments

### 🔔 Notification System

- Email notifications
- SMS reminders (configurable)
- Payment alerts
- Appointment reminders
- Overdue payment notifications

### 🏢 Multi-Clinic Support

- Separate clinic data isolation
- Shared doctors across clinics (optional)
- Central admin control

### 🔐 Security & Compliance

- Role-based access control (RBAC)
- Audit logging
- Soft deletes
- Data encryption
- Activity tracking
- HIPAA-compliant data handling

### 👥 User Roles

- **Super Admin**: Multi-clinic control
- **Clinic Admin**: Full clinic access
- **Receptionist**: Patients, appointments, billing
- **Doctor**: Schedule, patient notes
- **Accountant**: Billing and reports

## 🛠️ Requirements

- **PHP**: ^8.2
- **Node.js**: Latest LTS version
- **Composer**: Latest version
- **Database**: MySQL 8.0+, PostgreSQL 13+, or SQLite
- **Web Server**: Apache/Nginx with PHP support

### PHP Extensions

- `pdo`
- `pdo_mysql` or `pdo_pgsql` or `pdo_sqlite`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `bcmath`
- `curl`

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd medical-clinic-billing
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` file with your database and mail configuration:

```env
APP_NAME="Medical Clinic System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_clinic
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@clinic.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 7. Build Assets

```bash
npm run build
```

### 8. Start the Application

For development with hot reload:

```bash
composer run dev
```

Or for production:

```bash
php artisan serve
```

## ⚙️ Configuration

### Queue Configuration

Configure queue driver in `.env`:

```env
QUEUE_CONNECTION=database
```

Start queue worker:

```bash
php artisan queue:work
```

### Scheduled Tasks

Add to crontab for automated tasks:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### File Storage

Configure file storage for document uploads:

```env
FILESYSTEM_DISK=local
```

## 📖 Usage

### Accessing the Application

1. Open your browser and navigate to `http://localhost:8000`
2. Register a new account or login with existing credentials
3. Access features based on your role permissions

### Basic Workflow

1. **Patient Registration**: Create patient profiles with medical history
2. **Doctor Setup**: Configure doctor schedules and services
3. **Appointment Booking**: Schedule appointments with available time slots
4. **Consultation**: Record medical notes and prescriptions
5. **Billing**: Generate bills for services rendered
6. **Payment Processing**: Record payments and generate invoices
7. **Reports**: View financial and operational analytics

### User Interface

- **Dashboard**: Overview of key metrics and recent activities
- **Patient Management**: Search, view, and manage patient records
- **Appointment Calendar**: Visual calendar for scheduling
- **Billing Center**: Create and manage invoices
- **Reports Section**: Generate various reports and analytics

## 🔗 API Endpoints

### Authentication

```
POST   /api/login
POST   /api/register
POST   /api/logout
GET    /api/user
POST   /api/refresh-token
```

### Patient Management

```
GET    /api/patients
POST   /api/patients
GET    /api/patients/{id}
PUT    /api/patients/{id}
DELETE /api/patients/{id}
GET    /api/patients/{id}/appointments
GET    /api/patients/{id}/bills
GET    /api/patients/{id}/medical-records
```

### Doctor Management

```
GET    /api/doctors
GET    /api/doctors/{id}
GET    /api/doctors/{id}/appointments
GET    /api/doctors/{id}/schedule
GET    /api/doctors/{id}/bills
POST   /api/doctors
PUT    /api/doctors/{id}
DELETE /api/doctors/{id}
POST   /api/doctors/{id}/schedule
```

### Appointment Management

```
GET    /api/appointments
POST   /api/appointments
GET    /api/appointments/{id}
PUT    /api/appointments/{id}
DELETE /api/appointments/{id}
GET    /api/appointments/doctor/{doctorId}/slots
POST   /api/appointments/{id}/complete
POST   /api/appointments/{id}/no-show
```

### Billing Management

```
GET    /api/bills
POST   /api/bills
GET    /api/bills/{id}
PUT    /api/bills/{id}
DELETE /api/bills/{id}
POST   /api/bills/{id}/pay
POST   /api/bills/{id}/cancel
GET    /api/bills/{id}/pdf
POST   /api/bills/from-appointment/{appointmentId}
GET    /api/bills/statistics
GET    /api/bills/service-catalog
```

### Medical Records

```
GET    /api/medical-records
POST   /api/medical-records
GET    /api/medical-records/{id}
PUT    /api/medical-records/{id}
DELETE /api/medical-records/{id}
GET    /api/medical-records/patient/{patientId}
GET    /api/medical-records/type/{type}
POST   /api/medical-records/appointment/{appointmentId}/consultation
POST   /api/medical-records/patient/{patientId}/prescription
```

### Reports

```
GET    /api/reports/dashboard
GET    /api/reports/financial
GET    /api/reports/operational
GET    /api/reports/export/financial
GET    /api/reports/export/operational
```

### Notifications

```
GET    /api/notifications
GET    /api/notifications/stats
PUT    /api/notifications/{id}/read
PUT    /api/notifications/mark-all-read
DELETE /api/notifications/{id}
POST   /api/notifications/send-appointment-reminders
POST   /api/notifications/send-payment-reminders
POST   /api/notifications/send-overdue-notifications
```

### Compliance & Security

```
GET    /api/compliance/audit-trail
GET    /api/compliance/data-access
GET    /api/compliance/security-incidents
GET    /api/compliance/summary
GET    /api/compliance/export
```

### API Authentication

All API endpoints require authentication via Sanctum tokens. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Structure

- **Feature Tests**: API endpoint testing, user workflows
- **Unit Tests**: Individual component testing
- **Browser Tests**: Frontend interaction testing (if implemented)

### Test Coverage Areas

- Authentication and authorization
- Patient management operations
- Appointment scheduling logic
- Billing and payment processing
- Medical record management
- Notification system
- Report generation
- Security and compliance features

### Test Database

Tests use SQLite in-memory database for fast execution. Configuration in `phpunit.xml`.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use meaningful commit messages
- Ensure all tests pass before submitting PR

### Code Quality

```bash
# Run code analysis
composer run pint
npm run lint

# Format code
npm run format
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue in the repository
- Check the documentation
- Review existing issues for similar problems

## 🚀 Deployment

### Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Configure production database
3. Set up proper mail configuration
4. Configure web server (Apache/Nginx)
5. Set up SSL certificate
6. Configure queue workers
7. Set up scheduled tasks
8. Optimize assets: `npm run build`

### Environment Variables for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

### Performance Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build optimized assets
npm run build
```

---

## **Built with ❤️ using Laravel 12 and React**

## 🔑 **Login Credentials for Testing**

The database has been seeded with test users for each role. Here are the login credentials you can use to test the medical clinic system:

### **Admin User** (Full system access)

- **Email:** `admin@test.com`
- **Password:** `password`
- **Role:** admin

### **Doctor User** (Medical operations)

- **Email:** `doctor@test.com`
- **Password:** `password`
- **Role:** doctor

### **Patient User** (Limited access to own data)

- **Email:** `patient@test.com`
- **Password:** `password`
- **Role:** patient

### **Staff User** (Clinic operations)

- **Email:** `staff@test.com`
- **Password:** `password`
- **Role:** staff

### **Original Test User**

- **Email:** `test@example.com`
- **Password:** `password`

## 🚀 **How to Test:**

1. **Start the server:** `php artisan serve`
2. **Login via API:** Use these credentials with the `/api/auth/login` endpoint
3. **Test different roles:** Each role has different permissions and access levels
4. **API Documentation:** All endpoints are protected with role-based middleware

## 📋 **Role Permissions:**

- **Admin:** Full access to all features
- **Doctor:** Patient management, appointments, medical records, billing
- **Staff:** Appointments, billing, patient management
- **Patient:** View own appointments, bills, and medical records

All users are associated with the "Test Clinic" and have sample data for testing. The system is now ready for comprehensive testing! 🎉
