# NSBM EventHub – University Event Planning & Scheduling System

**NSBM EventHub** is a web application designed for university clubs, societies, faculty administration, and students to organize, manage, schedule, and participate in campus events and extracurricular activities.

---

## 🌟 Live Demo & Deployment Options

### 1. 🚀 GitHub Pages (Instant Live Interactive Demo)
This repository includes an interactive client-side live demo that deploys directly to **GitHub Pages**:
1. Go to your repository settings on GitHub: **Settings > Pages**.
2. Under **Build and deployment > Source**, select **GitHub Actions** (or select branch `main` with root `/`).
3. Your live demo will be accessible at: `https://<your-username>.github.io/NSBM_event_hub/`

---

### 2. 💻 Local Setup (PHP & MySQL)

#### Prerequisites:
- **PHP 8.0+** (with `pdo_mysql` enabled)
- **MySQL 8.0+**

#### Step-by-step Setup:
1. **Import the Database Schema & Seed Data**:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
2. **Configure Database Credentials**:
   If needed, customize your credentials in `config/database.php` or set environment variables:
   - `DB_HOST` (default: `127.0.0.1`)
   - `DB_PORT` (default: `3306`)
   - `DB_NAME` (default: `nsbm_eventhub`)
   - `DB_USER` (default: `root`)
   - `DB_PASS` (default: `12345`)

3. **Start the PHP Development Server**:
   ```bash
   php -S localhost:8000
   ```
4. **Access the Application**:
   Open your browser at [http://localhost:8000](http://localhost:8000)

---

### 3. ☁️ Cloud Deployment (Free PHP & MySQL Hosting)

#### Option A: Railway.app / Render.com / Heroku
- Connect this GitHub repository to Railway or Render.
- Add a MySQL Database plugin/service.
- Set the `DATABASE_URL` or `MYSQL_URL` environment variable. The app will automatically connect!
- Import `database/schema.sql` to your cloud MySQL instance.

#### Option B: InfinityFree / 000webhost / cPanel / Shared Hosting
1. Upload all repository files to `htdocs` or `public_html`.
2. Create a MySQL database and import `database/schema.sql`.
3. Update `config/database.php` with your database credentials.
4. The included `.htaccess` file handles URL routing and security automatically.

---

## 🔑 Default Credentials

### Administrator Portal
- **Email:** `admin@nsbm.ac.lk`
- **Password:** `admin123`

### Sample Student Account
- **Email:** `kamal.p@students.nsbm.ac.lk`
- **Password:** `student123`

---

## 🛠️ Technology Stack
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6+), Bootstrap Icons, Google Fonts
- **Backend:** PHP 8+ (PDO Database Layer, Session Management, CSRF-safe forms)
- **Database:** MySQL 8.0 / MariaDB
- **Web Server:** Apache / PHP Built-in Server
