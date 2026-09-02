# NSBM EventHub – Event Planning and Scheduling System

NSBM EventHub is a web application designed for university clubs, societies, and students to organize, manage, schedule, and participate in university events and extracurricular activities.

## Technology Stack
- Frontend: HTML5, CSS3, JavaScript (Vanilla ES6+), Bootstrap Icons
- Backend: PHP (PDO Database Layer, Session Management)
- Database: MySQL

## Core Features

### Admin Capabilities
- Portal Authentication & Secure Role Guard
- Create, edit, publish, and delete events
- Manage event categories with custom iconography and color coding
- View and manage student event registrations with attendance tracking
- Create, edit, and publish campus-wide or targeted announcements
- Generate and download participant lists in CSV format and printable roster sheets

### Student Capabilities
- Student account registration and sign-in
- Browse upcoming and ongoing campus events
- Real-time search and filter events by category, keyword, and date
- Event registration with unique pass generation and capacity tracking
- Personal event schedule with timeline view, ticket pass details, and cancellation options
- View campus announcements and notices

## System Accounts & Default Credentials

### Administrator Account
- Email: `admin@nsbm.ac.lk`
- Password: `admin123`

### Student Account (Sample)
- Email: `kamal.p@students.nsbm.ac.lk`
- Password: `student123`

## Database Setup
1. Open MySQL terminal or phpMyAdmin.
2. Import the schema and seed data:
   `mysql -u root -p < database/schema.sql`
3. If your MySQL credentials differ from the defaults (user: `root`, password: empty, port: `3306`), update `config/database.php`.

## Running the Application
Run the built-in PHP development server:
`php -S localhost:8000`

Open your web browser and navigate to:
`http://localhost:8000`
