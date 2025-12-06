-- ============================================================
-- Pin2Fix - GEO TAGGED ISSUE REPORTING SYSTEM
-- Complete MySQL 8.x Schema 
-- Updated to match Spring Boot entity models
-- NOTE: Passwords stored in plaintext per user request (NOT secure).
-- ============================================================

-- 0) Create and use database
CREATE DATABASE IF NOT EXISTS pin2fix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pin2fix;

-- ============================================================
-- 1) GOVERNMENT BODIES
-- ============================================================
DROP TABLE IF EXISTS government_bodies;
CREATE TABLE government_bodies (
  gov_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  jurisdiction VARCHAR(255),
  address TEXT,
  contact_phone VARCHAR(32),
  contact_email VARCHAR(255),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2) DEPARTMENTS
-- ============================================================
DROP TABLE IF EXISTS departments;
CREATE TABLE departments (
  dept_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  gov_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  contact_email VARCHAR(255),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_gov FOREIGN KEY (gov_id) REFERENCES government_bodies(gov_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3) USERS
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  user_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('CITIZEN','GOV_BODY','DEPT_HEAD','AREA_HEAD','WORKER','ADMIN') NOT NULL DEFAULT 'CITIZEN',
  phone VARCHAR(32),
  dept_id BIGINT,
  gov_id BIGINT,
  area_code VARCHAR(50),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_dept FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE SET NULL,
  CONSTRAINT fk_user_gov FOREIGN KEY (gov_id) REFERENCES government_bodies(gov_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4) ISSUES (Using separate lat/lng columns)
-- ============================================================
DROP TABLE IF EXISTS issues;
CREATE TABLE issues (
  issue_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  status ENUM(
      'PENDING','FORWARDED','ASSIGNED','IN_PROGRESS',
      'EVIDENCE_SUBMITTED','HEAD_APPROVED','COMPLETED','REOPENED','REJECTED'
  ) NOT NULL DEFAULT 'PENDING',
  severity TINYINT NOT NULL DEFAULT 3,
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL,
  address_text VARCHAR(512),
  reporter_id BIGINT,
  gov_id BIGINT,
  dept_id BIGINT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_issues_reporter FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_issues_gov FOREIGN KEY (gov_id) REFERENCES government_bodies(gov_id) ON DELETE SET NULL,
  CONSTRAINT fk_issues_dept FOREIGN KEY (dept_id) REFERENCES departments(dept_id) ON DELETE SET NULL,
  INDEX idx_issues_status (status),
  INDEX idx_issues_reporter (reporter_id),
  INDEX idx_issues_gov (gov_id),
  INDEX idx_issues_dept (dept_id),
  INDEX idx_issues_location (latitude, longitude)
) ENGINE=InnoDB;

-- ============================================================
-- 5) PHOTOS (initial evidence attached by reporter)
-- ============================================================
DROP TABLE IF EXISTS photos;
CREATE TABLE photos (
  photo_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  url TEXT NOT NULL,
  caption VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_photos_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6) ASSIGNMENTS (tracking assignment chain)
-- ============================================================
DROP TABLE IF EXISTS assignments;
CREATE TABLE assignments (
  assignment_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  assigned_by BIGINT,
  assignee_id BIGINT,
  role_assignee ENUM('DEPT_HEAD','AREA_HEAD','WORKER'),
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  due_date TIMESTAMP NULL,
  status ENUM('ASSIGNED','IN_PROGRESS','COMPLETED','REOPENED') NOT NULL DEFAULT 'ASSIGNED',
  comment TEXT,
  CONSTRAINT fk_assign_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_assign_by FOREIGN KEY (assigned_by) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_assign_to FOREIGN KEY (assignee_id) REFERENCES users(user_id) ON DELETE SET NULL,
  INDEX idx_assign_issue (issue_id),
  INDEX idx_assign_assignee (assignee_id),
  INDEX idx_assign_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 7) WORK EVIDENCE (uploaded by worker when task completed)
-- ============================================================
DROP TABLE IF EXISTS work_evidence;
CREATE TABLE work_evidence (
  evidence_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  assignment_id BIGINT NOT NULL,
  worker_id BIGINT,
  photo_url TEXT NOT NULL,
  notes TEXT,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evidence_assign FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
  CONSTRAINT fk_evidence_worker FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 8) HEAD APPROVALS (Dept Head / Area Head approval)
-- ============================================================
DROP TABLE IF EXISTS head_approvals;
CREATE TABLE head_approvals (
  approval_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  head_id BIGINT,
  status ENUM('APPROVED','REJECTED') NOT NULL,
  comment TEXT,
  approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_headapproval_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_headapproval_head FOREIGN KEY (head_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 9) GOV APPROVALS (final approval by gov body)
-- ============================================================
DROP TABLE IF EXISTS gov_approvals;
CREATE TABLE gov_approvals (
  approval_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  gov_user_id BIGINT,
  status ENUM('APPROVED','REJECTED') NOT NULL,
  comment TEXT,
  approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_govapproval_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_govapproval_user FOREIGN KEY (gov_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 10) FEEDBACK (submitted by citizen after completion)
-- ============================================================
DROP TABLE IF EXISTS feedback;
CREATE TABLE feedback (
  feedback_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  user_id BIGINT,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  message TEXT,
  is_positive BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_feedback_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  UNIQUE KEY uk_feedback_issue_user (issue_id, user_id)
) ENGINE=InnoDB;

-- ============================================================
-- 11) NOTIFICATIONS
-- ============================================================
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
  notification_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  issue_id BIGINT,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_notif_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE SET NULL,
  INDEX idx_notif_user (user_id),
  INDEX idx_notif_read (is_read)
) ENGINE=InnoDB;

-- ============================================================
-- 12) ACTIVITY LOG
-- ============================================================
DROP TABLE IF EXISTS activity_log;
CREATE TABLE activity_log (
  log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT,
  actor_id BIGINT,
  action VARCHAR(100) NOT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE SET NULL,
  CONSTRAINT fk_log_actor FOREIGN KEY (actor_id) REFERENCES users(user_id) ON DELETE SET NULL,
  INDEX idx_log_issue (issue_id),
  INDEX idx_log_actor (actor_id)
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Insert Government Bodies
INSERT INTO government_bodies (name, jurisdiction, address, contact_phone, contact_email) VALUES
('Chennai Corporation', 'Chennai Metropolitan Area', 'Ripon Buildings, Chennai 600003', '044-25619000', 'info@chennaicorporation.gov.in'),
('Coimbatore Municipal Corporation', 'Coimbatore District', 'Town Hall, Coimbatore 641001', '0422-2390836', 'info@coimbatorecorp.gov.in');

-- Insert Departments
INSERT INTO departments (gov_id, name, description) VALUES
(1, 'Roads & Infrastructure', 'Maintenance of roads, bridges, and public infrastructure'),
(1, 'Water Supply', 'Water distribution and sewage management'),
(1, 'Solid Waste Management', 'Garbage collection and waste disposal'),
(1, 'Street Lighting', 'Street light maintenance and installation'),
(2, 'Roads & Infrastructure', 'Maintenance of roads and public infrastructure'),
(2, 'Water Supply', 'Water distribution and management');

-- Insert Admin User
INSERT INTO users (name, email, password, role) VALUES
('System Admin', 'admin@pin2fix.com', 'admin123', 'ADMIN');

-- Insert Government Body Users
INSERT INTO users (name, email, password, role, gov_id) VALUES
('Chennai Corp User', 'gov@chennai.gov.in', 'gov123', 'GOV_BODY', 1),
('Coimbatore Corp User', 'gov@coimbatore.gov.in', 'gov123', 'GOV_BODY', 2);

-- Insert Department Heads
INSERT INTO users (name, email, password, role, dept_id, gov_id) VALUES
('Roads Dept Head', 'roads.head@chennai.gov.in', 'head123', 'DEPT_HEAD', 1, 1),
('Water Dept Head', 'water.head@chennai.gov.in', 'head123', 'DEPT_HEAD', 2, 1);

-- Insert Workers
INSERT INTO users (name, email, password, role, dept_id, gov_id, area_code) VALUES
('Worker Rajesh', 'rajesh@chennai.gov.in', 'worker123', 'WORKER', 1, 1, 'ZONE-1'),
('Worker Kumar', 'kumar@chennai.gov.in', 'worker123', 'WORKER', 1, 1, 'ZONE-2'),
('Worker Priya', 'priya@chennai.gov.in', 'worker123', 'WORKER', 2, 1, 'ZONE-1');

-- Insert Citizens
INSERT INTO users (name, email, password, role, phone) VALUES
('Arun Citizen', 'arun@gmail.com', 'citizen123', 'CITIZEN', '9876543210'),
('Priya Citizen', 'priya@gmail.com', 'citizen123', 'CITIZEN', '9876543211'),
('Vijay Citizen', 'vijay@gmail.com', 'citizen123', 'CITIZEN', '9876543212');

-- Insert Sample Issues
INSERT INTO issues (title, description, severity, latitude, longitude, address_text, reporter_id, status) VALUES
('Large Pothole on Main Road', 'There is a dangerous pothole approximately 2 feet wide on the main road near the bus stop. Multiple vehicles have been damaged.', 4, 13.0827, 80.2707, 'Main Road, Near Bus Stop, T Nagar, Chennai', 8, 'PENDING'),
('Street Light Not Working', 'The street light at the corner of 4th street has not been working for the past week. The area is very dark at night.', 3, 13.0850, 80.2750, '4th Street Corner, Anna Nagar, Chennai', 9, 'PENDING'),
('Garbage Not Collected', 'Garbage has not been collected from our street for 3 days. It is causing health hazards.', 4, 13.0810, 80.2680, '2nd Cross Street, Mylapore, Chennai', 10, 'PENDING');

-- Insert Sample Photos
INSERT INTO photos (issue_id, url, caption) VALUES
(1, '/uploads/issues/1/pothole1.jpg', 'Wide view of the pothole'),
(1, '/uploads/issues/1/pothole2.jpg', 'Close-up showing depth'),
(2, '/uploads/issues/2/streetlight1.jpg', 'Street light pole'),
(3, '/uploads/issues/3/garbage1.jpg', 'Uncollected garbage pile');

SELECT 'Pin2Fix database schema created successfully!' AS Status;
