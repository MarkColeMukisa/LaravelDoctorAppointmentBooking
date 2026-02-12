# 🏥 Doctor Appointment Booking System

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-4.x-4E56A6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 4">
  <img src="https://img.shields.io/badge/TailwindCSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/PHP-8.5+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.5+">
</p>

A comprehensive, production-ready doctor appointment booking system built with the **TALL Stack** (Tailwind CSS, Alpine.js, Laravel & Livewire). This application provides a seamless experience for patients to book appointments with doctors, while offering powerful management tools for doctors and administrators.

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Key Features](#-key-features)
- [Technologies Used](#-technologies-used)
- [Installation Guide](#-installation-guide)
- [Admin Login Credentials](#-admin-login-credentials)
- [How to Use the Application](#-how-to-use-the-application)
- [Environment Configuration](#-environment-configuration)
- [Deployment Guide](#-deployment-guide)
- [Folder Structure Overview](#-folder-structure-overview)
- [Screenshots](#-screenshots)
- [Credits & License](#-credits--license)

---

## 🎯 Project Overview

The **Doctor Appointment Booking System** is a full-stack web application designed to streamline the process of scheduling medical appointments. The system enables patients to search for doctors, view real-time availability, and book appointments either on-site or via live consultation. Doctors can manage their schedules, view appointments, and handle rescheduling requests. Administrators have complete control over the system, managing doctors, specialities, appointments, and users.

### Core Functionality

- **Patient Portal**: Search doctors, view availability, book appointments, manage bookings
- **Doctor Dashboard**: Manage schedules, view appointments, handle rescheduling
- **Admin Dashboard**: Complete system management with statistics and oversight
- **Real-time Availability**: Live slot checking based on doctor schedules and existing appointments
- **Email Notifications**: Automated email notifications for appointment creation, updates, and cancellations
- **Multi-role Authentication**: Secure role-based access control (Patient, Doctor, Admin, Guest)

---

## ✨ Key Features

### For Patients
- 🔍 **Doctor Search & Filtering**: Browse doctors by speciality, view profiles, and filter results
- 📅 **Live Availability Timeline**: Real-time view of available appointment slots for the next 21 days
- 📝 **Easy Booking**: Simple booking flow with date and time slot selection
- 🏥 **Appointment Types**: Choose between on-site visits or live consultations
- 📋 **Appointment Management**: View, reschedule, or cancel appointments
- 🔐 **Secure Authentication**: Email verification and password protection

### For Doctors
- 📊 **Dashboard Overview**: Statistics and recent appointments at a glance
- ⏰ **Schedule Management**: Create, edit, and delete availability schedules by day of week
- 📅 **Appointment Viewing**: View all appointments with patient details
- 🔄 **Rescheduling Tools**: Handle patient rescheduling requests
- 👤 **Profile Management**: Update profile information and images

### For Administrators
- 📈 **Comprehensive Dashboard**: System-wide statistics and overview
- 👨‍⚕️ **Doctor Management**: Create, edit, delete, and feature doctors
- 🏷️ **Speciality Management**: Manage medical specialities
- 📋 **Appointment Oversight**: View, filter, search, and manage all appointments
- 👥 **User Management**: Monitor and manage all system users
- 🔍 **Advanced Search & Pagination**: Filter and search across all data with pagination support

### System Features
- ⚡ **Real-time Updates**: Livewire-powered real-time UI updates
- 📧 **Email Notifications**: Automated emails for appointment lifecycle events
- 🎨 **Modern UI**: Beautiful, responsive design with TailwindCSS and Preline UI
- 🔒 **Role-based Access Control**: Secure middleware protection for routes
- 📱 **Responsive Design**: Mobile-friendly interface
- 🔍 **Search Functionality**: Advanced search across appointments, doctors, and users
- 📄 **Pagination**: Efficient data pagination for large datasets

---

## 🛠️ Technologies Used

### Backend
- **Laravel 12.51+** - PHP framework
- **Livewire 4.1+** - Full-stack framework for dynamic interfaces
- **Livewire Volt 1.10+** - Single-file Livewire components
- **Laravel Breeze 2.4+** - Authentication scaffolding

### Frontend
- **TailwindCSS 4.1+** - Utility-first CSS framework
- **Alpine.js** - Lightweight JavaScript framework (via Livewire)
- **Preline UI 4.0+** - UI component library
- **Vite 7.3+** - Next-generation frontend tooling

### Database
- **SQLite** (default) - Lightweight database for development
- **MySQL/MariaDB** - Production-ready database option
- **PostgreSQL** - Alternative database option

### Development Tools
- **Composer** - PHP dependency manager
- **NPM** - Node package manager
- **PHP 8.5+** - Programming language

### Additional Libraries
- **Moment.js** - Date manipulation library
- **Pikaday** - Date picker library
- **Carbon** - PHP date and time library

---

## 📦 Installation Guide

Follow these step-by-step instructions to set up the project on your local machine.

### Prerequisites

- **PHP 8.5 or higher** with extensions: `pdo`, `pdo_sqlite`, `mbstring`, `xml`, `curl`, `zip`, `gd`
- **Composer** - [Install Composer](https://getcomposer.org/download/)
- **Node.js 18+** and **NPM** - [Install Node.js](https://nodejs.org/)
- **Git** - [Install Git](https://git-scm.com/downloads)

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/LaravelDoctorAppointmentBooking.git
cd LaravelDoctorAppointmentBooking
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install NPM Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

Create a `.env` file from the example:

```bash
# On Windows (PowerShell)
copy .env.example .env

# On Linux/Mac
cp .env.example .env
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

### Step 6: Configure Environment Variables

Open `.env` and update the following settings:

```env
APP_NAME="AmSam Clinic"
APP_ENV=local
APP_KEY=base64:... (generated by key:generate)
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (SQLite - Default)
DB_CONNECTION=sqlite
# DB_DATABASE is automatically set to database/database.sqlite

# OR use MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=doctor_appointment
# DB_USERNAME=root
# DB_PASSWORD=

# Mail Configuration (for email notifications)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note**: If using SQLite, ensure the database file exists:

```bash
# On Windows (PowerShell)
New-Item -ItemType File -Path database\database.sqlite -Force

# On Linux/Mac
touch database/database.sqlite
```

### Step 7: Run Database Migrations

```bash
php artisan migrate
```

### Step 8: Seed the Database

Populate the database with sample data (admins, doctors, patients, specialities):

```bash
php artisan db:seed
```

This will create:
- **2 Admin users** (see [Admin Login Credentials](#-admin-login-credentials))
- **3 Doctor users** with schedules
- **5 Patient users**
- **Multiple Medical Specialities**
- **Sample schedules** for doctors

### Step 9: Create Storage Symbolic Link

Link the storage directory for public access to uploaded files:

```bash
php artisan storage:link
```

### Step 10: Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### Step 11: Start the Development Server

In a new terminal window:

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### Step 12: (Optional) Run Queue Worker

If you're using queues for email notifications:

```bash
php artisan queue:work
```

---

## 🔑 Admin Login Credentials

After running the database seeder, you can log in with the following admin credentials:

### Admin Account 1
- **Email**: `amina.patel@mediplus.test`
- **Password**: `password`

### Admin Account 2
- **Email**: `kelvin.smith@mediplus.test`
- **Password**: `password`

### Additional Test Accounts

**Doctors** (from `DoctorSeeder`):
- `laila.hassan@mediplus.test` / `password`
- `mateo.alvarez@mediplus.test` / `password`
- `chen.wei@mediplus.test` / `password`

**Patients** (from `PatientSeeder`):
- `sophia.ndlovu@patients.test` / `password`
- `ethan.brooks@patients.test` / `password`
- `ravi.kumar@patients.test` / `password`
- `melissa.ortega@patients.test` / `password`
- `chiamaka.obi@patients.test` / `password`

### Modifying Credentials

To change admin credentials, edit `database/seeders/AdminSeeder.php`:

```php
$admins = [
    [
        'name' => 'Your Admin Name',
        'email' => 'your-email@example.com',
    ],
    // ...
];

// Password is set to: Hash::make('your-password')
```

Then re-run the seeder:

```bash
php artisan db:seed --class=AdminSeeder
```

---

## 📖 How to Use the Application

### For Patients

#### 1. **Search for Doctors**
- Visit the homepage to see featured doctors
- Click "View All Doctors" or navigate to `/all/doctors`
- Filter doctors by speciality using the speciality filter
- View doctor profiles with bio, experience, and hospital information

#### 2. **View Live Availability**
- On the homepage, use the **Live Availability Timeline** component
- Select a doctor from the dropdown
- Choose a preferred day (or "Any Day")
- View available slots for the next 21 days
- Slots are automatically filtered to show only available times

#### 3. **Book an Appointment**
- Click on a doctor's "Book Appointment" button
- Or click an available slot from the timeline
- Select an appointment date from the calendar (only available dates are shown)
- Choose a time slot (only available slots are displayed)
- Select appointment type:
  - **On-site** (0): Physical visit to the clinic
  - **Live Consultation** (1): Virtual appointment
- Click "Book Appointment"
- You'll receive an email confirmation

#### 4. **Manage Appointments**
- Navigate to "My Appointments" from the dashboard
- View all your appointments with doctor details
- Use the search bar to filter appointments
- Click "Reschedule" to change appointment date/time
- Click "Cancel" to cancel an appointment
- Pagination controls are available at the bottom

### For Doctors

#### 1. **Access Doctor Dashboard**
- Log in with doctor credentials
- Navigate to `/doctor/dashboard`
- View statistics and recent appointments

#### 2. **Manage Schedules**
- Go to "My Schedules" (`/doctor/schedules`)
- Click "Add Schedule" to create new availability
- Select:
  - **Day of Week**: Sunday (0) through Saturday (6)
  - **From Time**: Start time (e.g., 09:00)
  - **To Time**: End time (e.g., 17:00)
- Click "Save Schedule"
- Edit or delete existing schedules as needed
- The system prevents overlapping schedules on the same day

#### 3. **View Appointments**
- Navigate to "My Appointments" (`/doctor/appointments`)
- View all appointments for your profile
- Search appointments by date, time, or patient name
- Use pagination to navigate through results
- Click "Reschedule" to modify appointment details
- Click "Start" to begin a live consultation

### For Administrators

#### 1. **Access Admin Dashboard**
- Log in with admin credentials
- Navigate to `/admin/dashboard`
- View system-wide statistics:
  - Total doctors, patients, appointments
  - Recent activity

#### 2. **Manage Doctors**
- Go to "Doctors" (`/admin/doctors`)
- View all doctors with speciality and status
- Click "Create Doctor" to add a new doctor
- Click "Edit" to modify doctor information
- Toggle "Featured" status to highlight doctors on homepage
- Click "Delete" to remove a doctor

#### 3. **Manage Specialities**
- Navigate to "Specialities" (`/admin/specialities`)
- View all medical specialities
- Create new specialities
- Edit or delete existing specialities

#### 4. **Manage Appointments**
- Go to "Appointments" (`/admin/appointments`)
- View all appointments across the system
- Use the search bar to filter by:
  - Date
  - Time
  - Doctor name
  - Patient name
- Adjust pagination per page (default: 5)
- Click "Reschedule" to modify any appointment
- Click "Cancel" to remove an appointment

#### 5. **Pagination & Filtering**
- All list views support pagination
- Use the search functionality to filter results
- Adjust items per page where available
- Navigate between pages using pagination controls

---

## ⚙️ Environment Configuration

### Mail Configuration

For email notifications to work, configure your mail settings in `.env`:

#### Using Mailpit (Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Using Gmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Using Mailtrap (Testing)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

### Database Configuration

#### SQLite (Default - Development)
```env
DB_CONNECTION=sqlite
# No additional configuration needed
# Database file: database/database.sqlite
```

#### MySQL (Production)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doctor_appointment
DB_USERNAME=root
DB_PASSWORD=your-password
```

#### PostgreSQL
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=doctor_appointment
DB_USERNAME=postgres
DB_PASSWORD=your-password
```

### Application URL

Update `APP_URL` based on your environment:

```env
# Local Development
APP_URL=http://localhost:8000

# Staging
APP_URL=https://staging.example.com

# Production
APP_URL=https://example.com
```

### Storage Permissions

Ensure proper permissions for storage:

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (usually not needed, but ensure write permissions)
```

### Queue Configuration

If using queues for background jobs:

```env
QUEUE_CONNECTION=database
```

Then run migrations and start the queue worker:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

---

## 🚀 Deployment Guide

### Deployment to Laravel Forge

1. **Connect Repository**
   - Link your GitHub/GitLab repository
   - Select branch (usually `main` or `master`)

2. **Server Configuration**
   - Choose PHP 8.5+
   - Set web directory to `public`
   - Configure database (MySQL recommended)

3. **Environment Setup**
   - Add environment variables in Forge dashboard
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure database credentials
   - Set up mail configuration

4. **Deploy Script**
   ```bash
   cd /home/forge/default
   git pull origin main
   composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm ci
   npm run build
   php artisan storage:link
   ```

5. **Queue Setup**
   - Enable "Queue Daemon" in Forge
   - Or set up Supervisor for queue workers

### Deployment to Shared Hosting

1. **Upload Files**
   - Upload all files except `node_modules`, `vendor`, `.git`
   - Ensure `.env` is uploaded and configured

2. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

3. **Configure Database**
   - Create MySQL database via hosting control panel
   - Update `.env` with database credentials

4. **Run Commands via SSH**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link
   ```

5. **Point Domain**
   - Point domain to `public` directory
   - Or configure `.htaccess` if needed

### Deployment to Localhost (XAMPP/WAMP)

1. **Place Project**
   - Copy project to `htdocs` (XAMPP) or `www` (WAMP)

2. **Configure Virtual Host** (Optional)
   ```apache
   <VirtualHost *:80>
       ServerName doctor-appointment.test
       DocumentRoot "D:/xampp/htdocs/LaravelDoctorAppointmentBooking/public"
       <Directory "D:/xampp/htdocs/LaravelDoctorAppointmentBooking/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Update Hosts File**
   ```
   127.0.0.1 doctor-appointment.test
   ```

4. **Configure `.env`**
   ```env
   APP_URL=http://doctor-appointment.test
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=doctor_appointment
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run Setup**
   ```bash
   composer install
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   npm install
   npm run build
   ```

---

## 📁 Folder Structure Overview

```
LaravelDoctorAppointmentBooking/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Application controllers
│   │   │   ├── AdminController.php
│   │   │   ├── DoctorController.php
│   │   │   └── PatientController.php
│   │   └── Middleware/           # Custom middleware (role-based)
│   │
│   ├── Livewire/                 # Livewire components (core functionality)
│   │   ├── Actions/              # Livewire actions
│   │   ├── Forms/                # Form components
│   │   ├── AllAppointments.php   # Appointment listing with pagination
│   │   ├── AllDoctors.php        # Doctor listing
│   │   ├── BookingComponent.php  # Appointment booking logic
│   │   ├── DoctorAvailabilityPanel.php  # Schedule management
│   │   ├── HomeAvailabilityTimeline.php  # Live availability view
│   │   └── ...                   # Other Livewire components
│   │
│   ├── Mail/                     # Email notification classes
│   │   ├── AppointmentCreated.php
│   │   ├── AppointmentUpdated.php
│   │   └── AppointmentCancelled.php
│   │
│   ├── Models/                   # Eloquent models
│   │   ├── User.php              # User model with roles
│   │   ├── Doctor.php            # Doctor model
│   │   ├── Appointment.php       # Appointment model with search scope
│   │   ├── DoctorSchedule.php    # Schedule model
│   │   └── Specialities.php      # Speciality model
│   │
│   └── Providers/                # Service providers
│
├── database/
│   ├── migrations/               # Database migrations
│   │   ├── create_users_table.php
│   │   ├── create_doctors_table.php
│   │   ├── create_appointments_table.php
│   │   └── ...
│   │
│   └── seeders/                  # Database seeders
│       ├── DatabaseSeeder.php    # Main seeder
│       ├── AdminSeeder.php       # Admin users
│       ├── DoctorSeeder.php      # Doctor users with schedules
│       ├── PatientSeeder.php     # Patient users
│       ├── SpecialitySeeder.php  # Medical specialities
│       └── GuestUserSeeder.php   # Guest users
│
├── resources/
│   ├── views/
│   │   ├── admin/                # Admin dashboard views
│   │   ├── doctor/               # Doctor dashboard views
│   │   ├── patient/              # Patient dashboard views
│   │   ├── livewire/             # Livewire component views
│   │   ├── components/           # Blade components
│   │   ├── layouts/              # Layout templates
│   │   └── emails/               # Email templates
│   │
│   ├── css/                      # TailwindCSS styles
│   └── js/                       # JavaScript files
│
├── routes/
│   ├── web.php                   # Web routes (main routing)
│   └── auth.php                  # Authentication routes (Breeze)
│
├── public/                       # Public assets
│   └── storage/                  # Storage symlink
│
├── storage/                      # File storage
│   └── app/public/               # Public file uploads
│
├── config/                       # Configuration files
│   ├── app.php                   # Application config
│   ├── database.php              # Database config
│   ├── mail.php                  # Mail config
│   └── ...
│
├── composer.json                 # PHP dependencies
├── package.json                  # NPM dependencies
├── tailwind.config.js            # TailwindCSS configuration
├── vite.config.js                # Vite configuration
└── .env                          # Environment variables (create from .env.example)
```

### Key Directories

- **`app/Livewire/`**: Contains all Livewire components that power the dynamic UI
- **`resources/views/livewire/`**: Blade templates for Livewire components
- **`database/seeders/`**: Seeders for populating test data
- **`routes/web.php`**: Main application routes with role-based middleware
- **`app/Models/`**: Eloquent models with relationships and business logic

---

## 📸 Screenshots

> **Note**: Add screenshots of your application here to showcase the UI and features.

### Suggested Screenshots:
- Homepage with featured doctors
- Live availability timeline
- Booking page
- Patient dashboard
- Doctor schedule management
- Admin dashboard
- Appointment listing with pagination

Example format:
```markdown
### Homepage
![Homepage](screenshots/homepage.png)

### Booking Interface
![Booking](screenshots/booking.png)
```

---

## 📚 Credits & License

### Resources Used

- **Moment.js** - [https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js](https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js)
- **Pikaday Library** - [https://github.com/Pikaday/Pikaday](https://github.com/Pikaday/Pikaday)
- **Laravel Framework** - [https://laravel.com](https://laravel.com)
- **Livewire** - [https://livewire.laravel.com](https://livewire.laravel.com)
- **TailwindCSS** - [https://tailwindcss.com](https://tailwindcss.com)
- **Preline UI** - [https://preline.co](https://preline.co)

### License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

### Copyright

© 2024 Doctor Appointment Booking System. All rights reserved.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📞 Support

For support, email support@example.com or create an issue in the repository.

---

## 🙏 Acknowledgments

- Laravel community for the amazing framework
- Livewire team for the reactive components
- All contributors and testers

---

<p align="center">Made with ❤️ using the TALL Stack</p>
