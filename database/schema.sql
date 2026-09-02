CREATE DATABASE IF NOT EXISTS nsbm_eventhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nsbm_eventhub;

DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    student_id VARCHAR(50) NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    faculty VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'bi-bookmark',
    color_code VARCHAR(20) NOT NULL DEFAULT '#006838',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    club_name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    venue VARCHAR(200) NOT NULL,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    capacity INT NOT NULL DEFAULT 100,
    banner_image VARCHAR(255) NULL,
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    ticket_code VARCHAR(60) NOT NULL UNIQUE,
    status ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    special_requirements VARCHAR(255) NULL,
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_reg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT uk_user_event UNIQUE (event_id, user_id)
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('normal', 'important', 'urgent') NOT NULL DEFAULT 'normal',
    target_audience ENUM('all', 'students', 'club_members') NOT NULL DEFAULT 'all',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ann_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (id, full_name, student_id, email, password, role, faculty, phone) VALUES
(1, 'NSBM Admin', 'ADM-001', 'admin@nsbm.ac.lk', '$2y$10$wE9KvZ7M3/hX0r6xqyU97O0wP7e5sT4d8a1x2v3b4n5m6l7k8j9h0', 'admin', 'Administration', '+94 11 544 5000'),
(2, 'Kamal Perera', 'NSBM-2024-0129', 'kamal.p@students.nsbm.ac.lk', '$2y$10$wE9KvZ7M3/hX0r6xqyU97O0wP7e5sT4d8a1x2v3b4n5m6l7k8j9h0', 'student', 'Faculty of Computing', '+94 77 123 4567'),
(3, 'Sanduni Silva', 'NSBM-2024-0458', 'sanduni.s@students.nsbm.ac.lk', '$2y$10$wE9KvZ7M3/hX0r6xqyU97O0wP7e5sT4d8a1x2v3b4n5m6l7k8j9h0', 'student', 'Faculty of Business', '+94 71 987 6543'),
(4, 'Nipuna Jayawardena', 'NSBM-2024-0891', 'nipuna.j@students.nsbm.ac.lk', '$2y$10$wE9KvZ7M3/hX0r6xqyU97O0wP7e5sT4d8a1x2v3b4n5m6l7k8j9h0', 'student', 'Faculty of Engineering', '+94 76 555 4321');

INSERT INTO categories (id, name, description, icon, color_code) VALUES
(1, 'Computing & Tech', 'Hackathons, coding challenges, AI seminars, and open source workshops', 'bi-laptop', '#0d6efd'),
(2, 'Business & Leadership', 'Entrepreneurship summits, leadership symposiums, and networking forums', 'bi-briefcase', '#198754'),
(3, 'Sports & Esports', 'Inter-faculty championships, cricket tournaments, and gaming battles', 'bi-trophy', '#d97706'),
(4, 'Music & Cultural Arts', 'Acoustic evenings, drama fests, traditional dance, and talent displays', 'bi-music-note-beamed', '#8b5cf6'),
(5, 'Volunteering & Community', 'Green environmental drives, charity fundraisers, and blood donation camps', 'bi-heart', '#ef4444'),
(6, 'Engineering & Robotics', 'Robotics showcases, drone design showcases, and CAD competitions', 'bi-cpu', '#06b6d4');

INSERT INTO events (id, category_id, title, club_name, description, venue, event_date, start_time, end_time, capacity, banner_image, status, created_by) VALUES
(1, 1, 'NSBM CodeBlast Hackathon 2026', 'FOSS Community NSBM', 'A 24-hour intense software development competition challenging students to build sustainable AI solutions for modern university problems. Grand prizes and internship opportunities with top tech companies.', 'Computing Lab Complex 04 & Auditorium', '2026-09-15', '09:00:00', '18:00:00', 120, 'assets/img/hackathon.jpg', 'upcoming', 1),
(2, 2, 'Future Leaders Global Summit', 'NSBM Rotaract Club', 'Annual leadership forum featuring industry executives, startup founders, and global keynote speakers discussing entrepreneurship, ethical management, and career acceleration.', 'Main University Auditorium', '2026-09-20', '10:00:00', '15:30:00', 300, 'assets/img/summit.jpg', 'upcoming', 1),
(3, 3, 'Inter-Faculty Cricket Championship', 'NSBM Sports Council', 'The annual cricket fiesta featuring teams from Computing, Business, and Engineering battling for the prestigious Green Trophy. Live commentary, food stalls, and music DJ.', 'NSBM International Cricket Grounds', '2026-09-25', '08:30:00', '17:00:00', 500, 'assets/img/sports.jpg', 'upcoming', 1),
(4, 4, 'Symphony of Green: Acoustic Night', 'NSBM Music Circle', 'An unplugged evening under the stars featuring student soloists, university band performances, fusion music, and guest artists.', 'Student Activity Center Amphitheatre', '2026-10-02', '18:00:00', '22:00:00', 250, 'assets/img/music.jpg', 'upcoming', 1),
(5, 5, 'Campus Green Forestation Drive', 'NSBM Nature & Wildlife Club', 'Tree planting campaign across university premises aiming to plant 500 native saplings. Refreshments and certificate of participation provided to all volunteers.', 'Faculty of Science Grounds & Green Park', '2026-10-10', '07:30:00', '12:00:00', 150, 'assets/img/nature.jpg', 'upcoming', 1),
(6, 6, 'RoboWars 2026: Battle of Autonomous Bots', 'IEEE Student Branch of NSBM', 'Witness autonomous robot combat, line follower challenges, and obstacle course races designed and programmed by engineering students.', 'Engineering Workshops & Atrium', '2026-10-18', '09:30:00', '16:00:00', 200, 'assets/img/robotics.jpg', 'upcoming', 1);

INSERT INTO registrations (id, event_id, user_id, ticket_code, status, special_requirements) VALUES
(1, 1, 2, 'TKT-CB26-0129-8812', 'registered', 'Vegetarian meal requested'),
(2, 2, 2, 'TKT-FL26-0129-9943', 'registered', 'None'),
(3, 3, 3, 'TKT-CC26-0458-1205', 'registered', 'Cheering squad coordinator'),
(4, 1, 4, 'TKT-CB26-0891-7734', 'registered', 'Need access for laptop charger');

INSERT INTO announcements (id, title, content, priority, target_audience, created_by, created_at) VALUES
(1, 'NSBM CodeBlast 2026 Registration Opened', 'Registration for the prestigious CodeBlast 24-Hour Hackathon is now live. Form your teams of 3-4 students and register before seats fill up. Cash prizes, certificates, and direct industry internship interviews provided to top 3 winning squads.', 'urgent', 'students', 1, '2026-08-27 07:00:00'),
(2, 'Rotaract Annual Blood Donation & Medical Camp', 'The Rotaract Club of NSBM in collaboration with the National Blood Transfusion Service is hosting the annual blood donation drive on Wednesday at the Student Activity Center Atrium from 9:00 AM to 4:00 PM. Refreshments will be served to all donors.', 'urgent', 'all', 1, '2026-08-27 05:00:00'),
(3, 'Shuttle Bus Timetable for Inter-Faculty Sports Fiesta', 'Special university shuttle services will operate continuously between Kottawa Multimodal Transport Hub, Homagama Station, and NSBM Campus gates on September 25th from 7:00 AM to 7:00 PM. Please present your student ID or EventHub pass.', 'important', 'all', 1, '2026-08-27 03:00:00'),
(4, 'IEEE Xtreme 19.0 Coding Workshop & Briefing', 'All registered participants of IEEE Xtreme must attend the mandatory training session on competitive programming algorithms and testing environments this Friday at 3:30 PM in Computing Lab Complex 04.', 'important', 'students', 1, '2026-08-26 23:00:00'),
(5, 'Inter-Faculty E-Sports Tournament Qualifier Schedule', 'Registrations are now confirmed for 32 student squads in Valorant, Mobile Legends, and FIFA 26. Group stage matches will commence this Saturday online via the official NSBM Gaming Discord server.', 'important', 'students', 1, '2026-08-26 19:00:00'),
(6, 'Club Stall Allocation & Budget Proposals for Welcome Week', 'All registered university club presidents and treasurers must submit their finalized budget requests and stall blueprint proposals to the Student Affairs Division before Friday 4:00 PM.', 'normal', 'club_members', 1, '2026-08-26 08:00:00'),
(7, 'Campus Green Forestation Campaign: 500 Saplings Drive', 'Join the Nature & Wildlife Circle this weekend to plant native trees across the Phase 2 boundary. Community service hours and volunteer certificates will be awarded to all participating students.', 'normal', 'all', 1, '2026-08-25 09:00:00'),
(8, 'Main Auditorium Audio & Lighting Stage Maintenance', 'Please be advised that the Main Auditorium will be inaccessible for rehearsal sessions on October 5th due to scheduled acoustic tuning and laser projector upgrades. All club rehearsals are relocated to Amphitheatre 2.', 'important', 'club_members', 1, '2026-08-24 10:00:00'),
(9, 'Phase 2 Library 24/7 Extended Study Hours', 'In preparation for mid-semester assessments, the Green Library and collaborative silent study pods on Level 3 will remain open 24 hours daily with high-speed Wi-Fi and power outlets.', 'normal', 'students', 1, '2026-08-23 11:00:00'),
(10, 'NSBM Industry Career Fair 2026: Company Roster Published', 'Over 60 leading tech, banking, and engineering corporate partners will be conducting on-campus recruitment booths at the Student Activity Center. Final year undergraduates are encouraged to bring printed CVs.', 'normal', 'students', 1, '2026-08-22 14:00:00');
