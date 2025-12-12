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
-- SAMPLE DATA - Indian Names & Tamil Nadu Regions
-- ============================================================

-- ============================================================
-- Government Bodies (All 38 Districts + Major Municipal Corporations)
-- ============================================================
INSERT INTO government_bodies (name, jurisdiction, address, contact_phone, contact_email) VALUES
-- Municipal Corporations (headed by Mayor)
('Chennai Municipal Corporation', 'Chennai Metropolitan Area', 'Ripon Buildings, Park Town, Chennai 600003', '044-25619000', 'mayor@chennaicorp.gov.in'),
('Coimbatore Municipal Corporation', 'Coimbatore City', 'Town Hall, Big Bazaar Street, Coimbatore 641001', '0422-2390836', 'mayor@coimbatorecorp.gov.in'),
('Madurai Municipal Corporation', 'Madurai City', 'Corporation Office, Madurai 625001', '0452-2531245', 'mayor@maduraicorp.gov.in'),
('Tiruchirappalli Municipal Corporation', 'Trichy City', 'Corporation Office, Trichy 620001', '0431-2414551', 'mayor@trichycorp.gov.in'),
('Salem Municipal Corporation', 'Salem City', 'Corporation Office, Salem 636001', '0427-2212848', 'mayor@salemcorp.gov.in'),
('Tirunelveli Municipal Corporation', 'Tirunelveli City', 'Corporation Office, Tirunelveli 627001', '0462-2501255', 'mayor@tirunelvelicorp.gov.in'),
('Erode Municipal Corporation', 'Erode City', 'Corporation Office, Erode 638001', '0424-2256255', 'mayor@erodecorp.gov.in'),
('Vellore Municipal Corporation', 'Vellore City', 'Corporation Office, Vellore 632001', '0416-2224555', 'mayor@vellorecorp.gov.in'),
('Thoothukudi Municipal Corporation', 'Thoothukudi City', 'Corporation Office, Thoothukudi 628001', '0461-2320744', 'mayor@thoothukudicorp.gov.in'),
('Tiruppur Municipal Corporation', 'Tiruppur City', 'Corporation Office, Tiruppur 641601', '0421-2200766', 'mayor@tiruppurcorp.gov.in'),
('Dindigul Municipal Corporation', 'Dindigul City', 'Corporation Office, Dindigul 624001', '0451-2420344', 'mayor@dindigulcorp.gov.in'),
('Thanjavur Municipal Corporation', 'Thanjavur City', 'Corporation Office, Thanjavur 613001', '04362-230255', 'mayor@thanjavurcorp.gov.in'),
('Nagercoil Municipal Corporation', 'Nagercoil City', 'Corporation Office, Nagercoil 629001', '04652-230566', 'mayor@nagercoilcorp.gov.in'),
('Avadi Municipal Corporation', 'Avadi Area', 'Corporation Office, Avadi 600054', '044-26550544', 'mayor@avadicorp.gov.in'),
('Tambaram Municipal Corporation', 'Tambaram Area', 'Corporation Office, Tambaram 600045', '044-22261833', 'mayor@tambaramcorp.gov.in'),

-- Municipalities (headed by Chairman)
('Ariyalur Municipality', 'Ariyalur District', 'Municipal Office, Ariyalur 621704', '04329-222044', 'chairman@ariyalur.gov.in'),
('Chengalpattu Municipality', 'Chengalpattu District', 'Municipal Office, Chengalpattu 603001', '044-27422255', 'chairman@chengalpattu.gov.in'),
('Cuddalore Municipality', 'Cuddalore District', 'Municipal Office, Cuddalore 607001', '04142-230455', 'chairman@cuddalore.gov.in'),
('Dharmapuri Municipality', 'Dharmapuri District', 'Municipal Office, Dharmapuri 636701', '04342-230855', 'chairman@dharmapuri.gov.in'),
('Kallakurichi Municipality', 'Kallakurichi District', 'Municipal Office, Kallakurichi 606202', '04151-220344', 'chairman@kallakurichi.gov.in'),
('Kancheepuram Municipality', 'Kancheepuram District', 'Municipal Office, Kancheepuram 631501', '044-27222455', 'chairman@kancheepuram.gov.in'),
('Karur Municipality', 'Karur District', 'Municipal Office, Karur 639001', '04324-241455', 'chairman@karur.gov.in'),
('Krishnagiri Municipality', 'Krishnagiri District', 'Municipal Office, Krishnagiri 635001', '04343-230655', 'chairman@krishnagiri.gov.in'),
('Mayiladuthurai Municipality', 'Mayiladuthurai District', 'Municipal Office, Mayiladuthurai 609001', '04364-222255', 'chairman@mayiladuthurai.gov.in'),
('Nagapattinam Municipality', 'Nagapattinam District', 'Municipal Office, Nagapattinam 611001', '04365-242566', 'chairman@nagapattinam.gov.in'),
('Namakkal Municipality', 'Namakkal District', 'Municipal Office, Namakkal 637001', '04286-230344', 'chairman@namakkal.gov.in'),
('Nilgiris (Ooty) Municipality', 'Nilgiris District', 'Municipal Office, Ooty 643001', '0423-2444255', 'chairman@nilgiris.gov.in'),
('Perambalur Municipality', 'Perambalur District', 'Municipal Office, Perambalur 621212', '04328-222566', 'chairman@perambalur.gov.in'),
('Pudukkottai Municipality', 'Pudukkottai District', 'Municipal Office, Pudukkottai 622001', '04322-220455', 'chairman@pudukkottai.gov.in'),
('Ramanathapuram Municipality', 'Ramanathapuram District', 'Municipal Office, Ramanathapuram 623501', '04567-220655', 'chairman@ramanathapuram.gov.in'),
('Ranipet Municipality', 'Ranipet District', 'Municipal Office, Ranipet 632401', '04172-230766', 'chairman@ranipet.gov.in'),
('Sivaganga Municipality', 'Sivaganga District', 'Municipal Office, Sivaganga 630561', '04575-241455', 'chairman@sivaganga.gov.in'),
('Tenkasi Municipality', 'Tenkasi District', 'Municipal Office, Tenkasi 627811', '04633-230544', 'chairman@tenkasi.gov.in'),
('Theni Municipality', 'Theni District', 'Municipal Office, Theni 625531', '04546-252344', 'chairman@theni.gov.in'),
('Tirupathur Municipality', 'Tirupathur District', 'Municipal Office, Tirupathur 635601', '04179-220455', 'chairman@tirupathur.gov.in'),
('Tiruvallur Municipality', 'Tiruvallur District', 'Municipal Office, Tiruvallur 602001', '044-27662455', 'chairman@tiruvallur.gov.in'),
('Tiruvannamalai Municipality', 'Tiruvannamalai District', 'Municipal Office, Tiruvannamalai 606601', '04175-230755', 'chairman@tiruvannamalai.gov.in'),
('Tiruvarur Municipality', 'Tiruvarur District', 'Municipal Office, Tiruvarur 610001', '04366-242344', 'chairman@tiruvarur.gov.in'),
('Viluppuram Municipality', 'Viluppuram District', 'Municipal Office, Viluppuram 605602', '04146-230455', 'chairman@viluppuram.gov.in'),
('Virudhunagar Municipality', 'Virudhunagar District', 'Municipal Office, Virudhunagar 626001', '04562-243566', 'chairman@virudhunagar.gov.in');

-- ============================================================
-- Departments (For Major Corporations)
-- ============================================================
INSERT INTO departments (gov_id, name, description, contact_email) VALUES
-- Chennai Corporation Departments (gov_id = 1)
(1, 'Roads & Infrastructure', 'Construction and maintenance of roads, bridges, flyovers and public infrastructure', 'roads@chennaicorp.gov.in'),
(1, 'Water Supply & Drainage', 'Water distribution, sewage management and drainage maintenance', 'water@chennaicorp.gov.in'),
(1, 'Solid Waste Management', 'Garbage collection, waste disposal and recycling operations', 'swm@chennaicorp.gov.in'),
(1, 'Street Lighting', 'Installation and maintenance of street lights and traffic signals', 'lighting@chennaicorp.gov.in'),
(1, 'Public Health', 'Sanitation, pest control and public health services', 'health@chennaicorp.gov.in'),
(1, 'Town Planning', 'Urban planning, building approvals and land use management', 'planning@chennaicorp.gov.in'),
(1, 'Parks & Gardens', 'Maintenance of public parks, playgrounds and green spaces', 'parks@chennaicorp.gov.in'),

-- Coimbatore Corporation Departments (gov_id = 2)
(2, 'Roads & Infrastructure', 'Road maintenance and infrastructure development', 'roads@coimbatorecorp.gov.in'),
(2, 'Water Supply & Drainage', 'Water supply and drainage system management', 'water@coimbatorecorp.gov.in'),
(2, 'Solid Waste Management', 'Waste collection and disposal services', 'swm@coimbatorecorp.gov.in'),
(2, 'Street Lighting', 'Street light maintenance', 'lighting@coimbatorecorp.gov.in'),
(2, 'Public Health', 'Health and sanitation services', 'health@coimbatorecorp.gov.in'),

-- Madurai Corporation Departments (gov_id = 3)
(3, 'Roads & Infrastructure', 'Road and bridge maintenance', 'roads@maduraicorp.gov.in'),
(3, 'Water Supply & Drainage', 'Water distribution and sewage', 'water@maduraicorp.gov.in'),
(3, 'Solid Waste Management', 'Garbage collection services', 'swm@maduraicorp.gov.in'),
(3, 'Street Lighting', 'Street lighting services', 'lighting@maduraicorp.gov.in'),

-- Trichy Corporation Departments (gov_id = 4)
(4, 'Roads & Infrastructure', 'Road infrastructure maintenance', 'roads@trichycorp.gov.in'),
(4, 'Water Supply & Drainage', 'Water and drainage services', 'water@trichycorp.gov.in'),
(4, 'Solid Waste Management', 'Waste management services', 'swm@trichycorp.gov.in'),

-- Salem Corporation Departments (gov_id = 5)
(5, 'Roads & Infrastructure', 'Road maintenance services', 'roads@salemcorp.gov.in'),
(5, 'Water Supply & Drainage', 'Water supply services', 'water@salemcorp.gov.in'),
(5, 'Solid Waste Management', 'Waste collection services', 'swm@salemcorp.gov.in');

-- ============================================================
-- Users - Admin
-- ============================================================
INSERT INTO users (name, email, password, role, phone) VALUES
('Tamil Admin', 'admin@pin2fix.gov.in', 'admin@123', 'ADMIN', '9000000001'),
('Super Administrator', 'superadmin@pin2fix.gov.in', 'superadmin@123', 'ADMIN', '9000000002');

-- ============================================================
-- Users - Government Body Representatives
-- ============================================================
INSERT INTO users (name, email, password, role, phone, gov_id) VALUES
('Dr. Mohan Raj IAS', 'mohan.raj@chennaicorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000001', 1),
('Srinivasan Iyer IAS', 'srinivasan.i@coimbatorecorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000002', 2),
('Anbazhagan Muthu IAS', 'anbazhagan.m@maduraicorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000003', 3),
('Ramachandran Nair IAS', 'ramachandran.n@trichycorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000004', 4),
('Krishnamoorthy Pillai IAS', 'krishnamoorthy.p@salemcorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000005', 5),
('Jayalakshmi Devi IAS', 'jayalakshmi.d@tirunelvelicorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000006', 6),
('Parthiban Gounder', 'parthiban.g@erodecorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000007', 7),
('Venkatachalam Naidu', 'venkatachalam.n@vellorecorp.gov.in', 'govpass@123', 'GOV_BODY', '9888000008', 8);

-- ============================================================
-- Users - Department Heads
-- ============================================================
INSERT INTO users (name, email, password, role, phone, dept_id, gov_id) VALUES
-- Chennai Corporation Dept Heads
('Selvakumar Raja', 'selvakumar.r@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100001', 1, 1),
('Palaniswamy Gounder', 'palaniswamy.g@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100002', 2, 1),
('Muthukumar Thevar', 'muthukumar.t@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100003', 3, 1),
('Ganesan Pillai', 'ganesan.p@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100004', 4, 1),
('Shanmugam Nadar', 'shanmugam.n@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100005', 5, 1),
('Lakshmi Priya', 'lakshmi.p@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100006', 6, 1),
('Saravanan Murugan', 'saravanan.m@chennaicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100007', 7, 1),
-- Coimbatore Corporation Dept Heads
('Ramesh Krishnan', 'ramesh.k@coimbatorecorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100008', 8, 2),
('Sundarajan Iyer', 'sundarajan.i@coimbatorecorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100009', 9, 2),
('Velusamy Gounder', 'velusamy.g@coimbatorecorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100010', 10, 2),
-- Madurai Corporation Dept Heads
('Pandian Thevar', 'pandian.t@maduraicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100011', 13, 3),
('Alagarsamy Nadar', 'alagarsamy.n@maduraicorp.gov.in', 'depthead@123', 'DEPT_HEAD', '9888100012', 14, 3);

-- ============================================================
-- Users - Area Heads
-- ============================================================
INSERT INTO users (name, email, password, role, phone, dept_id, gov_id, area_code) VALUES
-- Chennai Area Heads
('Thirunavukkarasu Pillai', 'thiru.p@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200001', 1, 1, 'CHENNAI-ZONE-1'),
('Kannan Chettiar', 'kannan.c@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200002', 1, 1, 'CHENNAI-ZONE-2'),
('Perumal Konar', 'perumal.k@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200003', 2, 1, 'CHENNAI-ZONE-1'),
('Subramaniam Iyer', 'subramaniam.i@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200004', 2, 1, 'CHENNAI-ZONE-2'),
('Venkatesan Mudaliar', 'venkatesan.m@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200005', 3, 1, 'CHENNAI-ZONE-1'),
('Arumugam Naicker', 'arumugam.n@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200006', 3, 1, 'CHENNAI-ZONE-2'),
('Balasubramanian Chetty', 'balasubramanian.c@chennaicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200007', 4, 1, 'CHENNAI-ZONE-1'),
-- Coimbatore Area Heads
('Manikandan Pillai', 'manikandan.p@coimbatorecorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200008', 8, 2, 'CBE-ZONE-1'),
('Senthilkumar Gounder', 'senthilkumar.g@coimbatorecorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200009', 8, 2, 'CBE-ZONE-2'),
('Velmurugan Chettiar', 'velmurugan.c@coimbatorecorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200010', 9, 2, 'CBE-ZONE-1'),
-- Madurai Area Heads
('Chinnadurai Thevar', 'chinnadurai.t@maduraicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200011', 13, 3, 'MDU-ZONE-1'),
('Karuppasamy Nadar', 'karuppasamy.n@maduraicorp.gov.in', 'areahead@123', 'AREA_HEAD', '9888200012', 14, 3, 'MDU-ZONE-1');

-- ============================================================
-- Users - Workers
-- ============================================================
INSERT INTO users (name, email, password, role, phone, dept_id, gov_id, area_code) VALUES
-- Chennai Workers - Roads Dept
('Murugan Thevar', 'murugan.t@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300001', 1, 1, 'CHENNAI-ZONE-1'),
('Raman Pillai', 'raman.p@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300002', 1, 1, 'CHENNAI-ZONE-1'),
('Kathiresan Nadar', 'kathiresan.n@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300003', 1, 1, 'CHENNAI-ZONE-2'),
('Dhanush Kumar', 'dhanush.k@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300004', 1, 1, 'CHENNAI-ZONE-2'),
-- Chennai Workers - Water Dept
('Arjun Selvam', 'arjun.s@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300005', 2, 1, 'CHENNAI-ZONE-1'),
('Bhaskar Rao', 'bhaskar.r@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300006', 2, 1, 'CHENNAI-ZONE-2'),
-- Chennai Workers - SWM Dept
('Chelladurai Pillai', 'chelladurai.p@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300007', 3, 1, 'CHENNAI-ZONE-1'),
('Natarajan Iyer', 'natarajan.i@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300008', 3, 1, 'CHENNAI-ZONE-2'),
-- Chennai Workers - Street Lighting
('Ponnusamy Thevar', 'ponnusamy.t@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300009', 4, 1, 'CHENNAI-ZONE-1'),
('Ramasamy Gounder', 'ramasamy.g@chennaicorp.gov.in', 'worker@123', 'WORKER', '9888300010', 4, 1, 'CHENNAI-ZONE-2'),
-- Coimbatore Workers
('Subramani Gounder', 'subramani.g@coimbatorecorp.gov.in', 'worker@123', 'WORKER', '9888300011', 8, 2, 'CBE-ZONE-1'),
('Annamalai Pillai', 'annamalai.p@coimbatorecorp.gov.in', 'worker@123', 'WORKER', '9888300012', 8, 2, 'CBE-ZONE-2'),
('Palani Chettiar', 'palani.c@coimbatorecorp.gov.in', 'worker@123', 'WORKER', '9888300013', 9, 2, 'CBE-ZONE-1'),
('Muthu Krishnan', 'muthu.k@coimbatorecorp.gov.in', 'worker@123', 'WORKER', '9888300014', 10, 2, 'CBE-ZONE-1'),
-- Madurai Workers
('Kalimuthu Thevar', 'kalimuthu.t@maduraicorp.gov.in', 'worker@123', 'WORKER', '9888300015', 13, 3, 'MDU-ZONE-1'),
('Ayyanar Nadar', 'ayyanar.n@maduraicorp.gov.in', 'worker@123', 'WORKER', '9888300016', 14, 3, 'MDU-ZONE-1');

-- ============================================================
-- Users - Citizens
-- ============================================================
INSERT INTO users (name, email, password, role, phone) VALUES
('Arun Kumar', 'arun.kumar@gmail.com', 'citizen@123', 'CITIZEN', '9876543210'),
('Priya Lakshmi', 'priya.lakshmi@gmail.com', 'citizen@123', 'CITIZEN', '9876543211'),
('Karthik Subramanian', 'karthik.subbu@gmail.com', 'citizen@123', 'CITIZEN', '9876543212'),
('Deepika Venkatesh', 'deepika.v@gmail.com', 'citizen@123', 'CITIZEN', '9876543213'),
('Rajesh Pandian', 'rajesh.pandian@gmail.com', 'citizen@123', 'CITIZEN', '9876543214'),
('Saranya Murugan', 'saranya.m@gmail.com', 'citizen@123', 'CITIZEN', '9876543215'),
('Vijay Anand', 'vijay.anand@gmail.com', 'citizen@123', 'CITIZEN', '9876543216'),
('Kavitha Rajan', 'kavitha.rajan@gmail.com', 'citizen@123', 'CITIZEN', '9876543217'),
('Senthil Nathan', 'senthil.nathan@gmail.com', 'citizen@123', 'CITIZEN', '9876543218'),
('Meenakshi Sundaram', 'meenakshi.s@gmail.com', 'citizen@123', 'CITIZEN', '9876543219'),
('Bala Krishnan', 'bala.krishnan@gmail.com', 'citizen@123', 'CITIZEN', '9876543220'),
('Lakshmi Narayanan', 'lakshmi.n@gmail.com', 'citizen@123', 'CITIZEN', '9876543221'),
('Gopal Swamy', 'gopal.swamy@gmail.com', 'citizen@123', 'CITIZEN', '9876543222'),
('Revathi Shankar', 'revathi.shankar@gmail.com', 'citizen@123', 'CITIZEN', '9876543223'),
('Manikandan Ravi', 'manikandan.r@gmail.com', 'citizen@123', 'CITIZEN', '9876543224'),
('Divya Bharathi', 'divya.b@gmail.com', 'citizen@123', 'CITIZEN', '9876543225'),
('Ashok Sundaram', 'ashok.s@gmail.com', 'citizen@123', 'CITIZEN', '9876543226'),
('Nithya Devi', 'nithya.d@gmail.com', 'citizen@123', 'CITIZEN', '9876543227'),
('Suresh Babu', 'suresh.babu@gmail.com', 'citizen@123', 'CITIZEN', '9876543228'),
('Anitha Kumari', 'anitha.k@gmail.com', 'citizen@123', 'CITIZEN', '9876543229'),
('Prakash Raj', 'prakash.raj@gmail.com', 'citizen@123', 'CITIZEN', '9876543230'),
('Sangeetha Devi', 'sangeetha.d@gmail.com', 'citizen@123', 'CITIZEN', '9876543231'),
('Vignesh Kumar', 'vignesh.k@gmail.com', 'citizen@123', 'CITIZEN', '9876543232'),
('Ramya Krishnan', 'ramya.k@gmail.com', 'citizen@123', 'CITIZEN', '9876543233'),
('Surya Prakash', 'surya.p@gmail.com', 'citizen@123', 'CITIZEN', '9876543234');

-- ============================================================
-- Issues (Various locations across Tamil Nadu)
-- ============================================================
INSERT INTO issues (title, description, status, severity, latitude, longitude, address_text, reporter_id, gov_id, dept_id) VALUES
-- Chennai Issues
('Large Pothole on Anna Salai', 'Dangerous pothole approximately 2 feet wide near Gemini Flyover causing traffic issues and vehicle damage.', 'PENDING', 5, 13.06040000, 80.25180000, 'Anna Salai, Teynampet, Chennai 600018', 55, 1, 1),
('Broken Street Light at T Nagar', 'Street light not working for 5 days near Pondy Bazaar junction. Very dark and unsafe at night.', 'FORWARDED', 3, 13.04180000, 80.23160000, 'Pondy Bazaar, T Nagar, Chennai 600017', 56, 1, 4),
('Garbage Dump Overflowing at Mylapore', 'Municipal garbage bin overflowing at Mylapore tank area for 3 days causing health hazards.', 'ASSIGNED', 4, 13.03390000, 80.26920000, 'Mylapore Tank, Chennai 600004', 57, 1, 3),
('Water Pipe Leakage at Adyar', 'Major water leakage near Adyar Signal wasting thousands of liters daily.', 'IN_PROGRESS', 4, 13.00620000, 80.25590000, 'Adyar Signal, Chennai 600020', 58, 1, 2),
('Road Cave-in at Velachery', 'Road has caved in near Phoenix Mall area creating dangerous situation for vehicles.', 'PENDING', 5, 12.98190000, 80.21880000, 'Velachery Main Road, Chennai 600042', 59, 1, 1),
('Sewage Overflow at Chromepet', 'Sewage water overflowing on GST Road near Chromepet creating unhygienic conditions.', 'PENDING', 4, 12.95160000, 80.14060000, 'GST Road, Chromepet, Chennai 600044', 60, 1, 2),
('Damaged Footpath at Egmore', 'Footpath tiles broken near Egmore Railway Station causing inconvenience to pedestrians.', 'FORWARDED', 2, 13.07320000, 80.26180000, 'Gandhi Irwin Road, Egmore, Chennai 600008', 61, 1, 1),
('Stagnant Water at Ashok Nagar', 'Rainwater stagnation for past week breeding mosquitoes.', 'ASSIGNED', 4, 13.03800000, 80.21200000, '10th Avenue, Ashok Nagar, Chennai 600083', 62, 1, 2),
('Street Light Cluster Failure', 'Multiple street lights not working on entire stretch of Kodambakkam High Road.', 'PENDING', 3, 13.05200000, 80.22800000, 'Kodambakkam High Road, Chennai 600024', 63, 1, 4),
('Garbage Not Collected at Anna Nagar', 'Garbage not collected for 4 days in 2nd Avenue area.', 'EVIDENCE_SUBMITTED', 3, 13.08600000, 80.20900000, '2nd Avenue, Anna Nagar, Chennai 600040', 64, 1, 3),

-- Coimbatore Issues
('Street Light Malfunction RS Puram', 'Multiple street lights not working in RS Puram main area for a week.', 'PENDING', 3, 11.00830000, 76.95580000, 'RS Puram, Coimbatore 641002', 65, 2, 11),
('Deep Pothole at Gandhipuram', 'Deep pothole on main bus stand road causing accidents.', 'ASSIGNED', 4, 11.01680000, 76.97400000, 'Gandhipuram Bus Stand Road, Coimbatore 641012', 66, 2, 8),
('Drainage Block at Peelamedu', 'Drainage completely blocked causing water stagnation and foul smell.', 'IN_PROGRESS', 4, 11.02340000, 77.02490000, 'Peelamedu Main Road, Coimbatore 641004', 67, 2, 9),
('Garbage Collection Delay Saibaba Colony', 'No garbage collection for past week in Saibaba Colony area.', 'PENDING', 3, 11.02440000, 76.96670000, 'Saibaba Colony, Coimbatore 641011', 68, 2, 10),
('Road Damage at Race Course', 'Road surface badly damaged near Race Course area.', 'FORWARDED', 4, 11.01100000, 76.96200000, 'Race Course Road, Coimbatore 641018', 69, 2, 8),

-- Madurai Issues
('Road Damage at Anna Nagar Madurai', 'Road completely damaged due to heavy rain near Anna Nagar junction.', 'PENDING', 5, 9.92520000, 78.13480000, 'Anna Nagar, Madurai 625020', 70, 3, 13),
('Water Supply Disruption Goripalayam', 'No water supply for 3 days in Goripalayam area affecting hundreds of families.', 'FORWARDED', 5, 9.91940000, 78.11600000, 'Goripalayam, Madurai 625002', 71, 3, 14),
('Open Manhole at Bypass Road', 'Manhole cover missing on Madurai Bypass Road - extremely dangerous for vehicles.', 'ASSIGNED', 5, 9.94560000, 78.08680000, 'Madurai Bypass Road, Madurai 625010', 72, 3, 13),
('Street Light Failure at Meenakshi Temple', 'Street lights not working near famous Meenakshi Temple area affecting tourists.', 'PENDING', 3, 9.91970000, 78.11930000, 'East Masi Street, Madurai 625001', 73, 3, 16),

-- Trichy Issues
('Bridge Crack at Srirangam', 'Visible cracks on bridge connecting to Srirangam temple - safety concern.', 'PENDING', 5, 10.86240000, 78.69470000, 'Srirangam Bridge Road, Trichy 620006', 74, 4, 17),
('Flooded Underpass at Chatram', 'Underpass at Chatram Bus Stand flooded with rainwater blocking traffic.', 'IN_PROGRESS', 4, 10.82020000, 78.68470000, 'Chatram Bus Stand, Trichy 620002', 75, 4, 18),
('Park Maintenance Required', 'Gandhi Park needs urgent maintenance - broken benches and overgrown vegetation.', 'PENDING', 2, 10.80900000, 78.69420000, 'Gandhi Market Area, Trichy 620001', 76, 4, NULL),

-- Salem Issues
('Pothole at New Bus Stand', 'Large pothole at Salem New Bus Stand entrance causing vehicle damage.', 'PENDING', 4, 11.66430000, 78.16740000, 'Salem New Bus Stand, Salem 636004', 77, 5, 20),
('Water Pipe Burst at Hasthampatti', 'Major water pipe burst flooding the street.', 'ASSIGNED', 5, 11.65800000, 78.14500000, 'Hasthampatti, Salem 636007', 78, 5, 21),

-- Other District Issues
('Road Damage at Tirunelveli', 'Road damaged near Palayamkottai Junction affecting daily commute.', 'PENDING', 3, 8.71390000, 77.72640000, 'Palayamkottai, Tirunelveli 627002', 79, 6, NULL),
('Drainage Overflow at Erode Market', 'Drainage overflow at Erode Market area creating unhygienic conditions.', 'FORWARDED', 4, 11.34100000, 77.71720000, 'Erode Market Road, Erode 638001', 55, 7, NULL),
('Fort Road Damage at Vellore', 'Road near Vellore Fort badly damaged affecting tourism.', 'PENDING', 3, 12.91650000, 79.13250000, 'Vellore Fort Road, Vellore 632001', 56, 8, NULL);

-- ============================================================
-- Photos (Evidence for Issues)
-- ============================================================
INSERT INTO photos (issue_id, url, caption) VALUES
(1, '/uploads/issues/1/pothole_anna_salai_1.jpg', 'Wide view of the pothole on Anna Salai'),
(1, '/uploads/issues/1/pothole_anna_salai_2.jpg', 'Close-up showing depth of pothole'),
(2, '/uploads/issues/2/broken_light_tnagar.jpg', 'Non-functional street light at T Nagar'),
(3, '/uploads/issues/3/garbage_mylapore_1.jpg', 'Overflowing garbage bin at Mylapore'),
(3, '/uploads/issues/3/garbage_mylapore_2.jpg', 'Garbage spilled on road'),
(4, '/uploads/issues/4/water_leak_adyar.jpg', 'Water pipe leakage at Adyar'),
(5, '/uploads/issues/5/road_cavein_velachery.jpg', 'Road cave-in at Velachery'),
(6, '/uploads/issues/6/sewage_chromepet.jpg', 'Sewage overflow at Chromepet'),
(7, '/uploads/issues/7/footpath_egmore.jpg', 'Damaged footpath at Egmore'),
(8, '/uploads/issues/8/stagnant_water_ashok_nagar.jpg', 'Stagnant water at Ashok Nagar'),
(10, '/uploads/issues/10/garbage_anna_nagar.jpg', 'Uncollected garbage at Anna Nagar'),
(11, '/uploads/issues/11/streetlight_rspuram.jpg', 'Malfunctioning street lights at RS Puram'),
(12, '/uploads/issues/12/pothole_gandhipuram.jpg', 'Pothole at Gandhipuram'),
(13, '/uploads/issues/13/drainage_peelamedu.jpg', 'Blocked drainage at Peelamedu'),
(16, '/uploads/issues/16/road_annanagar_madurai.jpg', 'Rain damaged road at Madurai'),
(18, '/uploads/issues/18/manhole_bypass.jpg', 'Open manhole on bypass road'),
(20, '/uploads/issues/20/bridge_srirangam.jpg', 'Cracks on Srirangam bridge');

-- ============================================================
-- Assignments
-- ============================================================
INSERT INTO assignments (issue_id, assigned_by, assignee_id, role_assignee, status, comment, due_date) VALUES
-- Issue 3: Garbage Overflow - Assigned to Area Head
(3, 3, 29, 'AREA_HEAD', 'ASSIGNED', 'Urgent garbage clearance required at Mylapore. Please coordinate with SWM team.', DATE_ADD(NOW(), INTERVAL 2 DAY)),
-- Issue 4: Water Leakage - In Progress by Worker
(4, 3, 27, 'AREA_HEAD', 'IN_PROGRESS', 'Water department team assigned for immediate repair.', DATE_ADD(NOW(), INTERVAL 1 DAY)),
(4, 27, 39, 'WORKER', 'IN_PROGRESS', 'Worker assigned for pipe repair work.', DATE_ADD(NOW(), INTERVAL 1 DAY)),
-- Issue 8: Stagnant Water - Assigned
(8, 3, 27, 'AREA_HEAD', 'ASSIGNED', 'Clear stagnant water and ensure proper drainage.', DATE_ADD(NOW(), INTERVAL 3 DAY)),
-- Issue 10: Garbage Not Collected - Evidence Submitted
(10, 3, 29, 'AREA_HEAD', 'COMPLETED', 'Garbage clearance work completed.', DATE_ADD(NOW(), INTERVAL 1 DAY)),
(10, 29, 41, 'WORKER', 'COMPLETED', 'All garbage cleared from the area.', DATE_ADD(NOW(), INTERVAL 1 DAY)),
-- Issue 12: Pothole Gandhipuram - Assigned
(12, 4, 32, 'AREA_HEAD', 'ASSIGNED', 'Road repair team to fix pothole immediately.', DATE_ADD(NOW(), INTERVAL 2 DAY)),
-- Issue 13: Drainage Block - In Progress
(13, 4, 34, 'AREA_HEAD', 'IN_PROGRESS', 'Drainage clearing work started.', DATE_ADD(NOW(), INTERVAL 2 DAY)),
(13, 34, 47, 'WORKER', 'IN_PROGRESS', 'Worker clearing the blocked drain.', DATE_ADD(NOW(), INTERVAL 2 DAY)),
-- Issue 18: Open Manhole - Assigned
(18, 5, 35, 'AREA_HEAD', 'ASSIGNED', 'Priority - Replace manhole cover immediately.', DATE_ADD(NOW(), INTERVAL 1 DAY)),
-- Issue 21: Flooded Underpass - In Progress
(21, 6, NULL, 'DEPT_HEAD', 'IN_PROGRESS', 'Pumping operations underway to clear water.', DATE_ADD(NOW(), INTERVAL 1 DAY)),
-- Issue 24: Water Pipe Burst - Assigned
(24, 7, NULL, 'DEPT_HEAD', 'ASSIGNED', 'Emergency repair team dispatched.', NOW());

-- ============================================================
-- Work Evidence (Submitted by Workers)
-- ============================================================
INSERT INTO work_evidence (assignment_id, worker_id, photo_url, notes) VALUES
(6, 41, '/uploads/evidence/6/garbage_cleared_1.jpg', 'All garbage cleared from 2nd Avenue area. Bins replaced with new ones.'),
(6, 41, '/uploads/evidence/6/garbage_cleared_2.jpg', 'Area cleaned and sanitized.'),
(3, 39, '/uploads/evidence/3/pipe_repair_progress.jpg', 'Pipe repair work 50% complete. Expected completion by tomorrow.'),
(8, 47, '/uploads/evidence/8/drainage_clearing.jpg', 'Drainage clearing in progress. Removed major blockage.');

-- ============================================================
-- Head Approvals
-- ============================================================
INSERT INTO head_approvals (issue_id, head_id, status, comment) VALUES
(10, 17, 'APPROVED', 'Work completed satisfactorily. Area is clean now. Forwarding for final approval.');

-- ============================================================
-- Gov Approvals
-- ============================================================
INSERT INTO gov_approvals (issue_id, gov_user_id, status, comment) VALUES
(10, 3, 'APPROVED', 'Verified the completion. Issue resolved successfully. Good work by the team.');

-- ============================================================
-- Feedback
-- ============================================================
INSERT INTO feedback (issue_id, user_id, rating, message, is_positive) VALUES
(4, 58, 4, 'Work is in progress, team responded quickly. Thank you Chennai Corporation!', TRUE),
(10, 64, 5, 'Excellent service! Garbage was cleared within 24 hours of reporting. Very satisfied.', TRUE),
(13, 67, 3, 'Work started but taking longer than expected. Hope it gets completed soon.', TRUE);

-- ============================================================
-- Notifications
-- ============================================================
INSERT INTO notifications (user_id, issue_id, title, message, is_read) VALUES
-- Notifications for Citizens
(55, 1, 'Issue Received', 'Your complaint about "Large Pothole on Anna Salai" has been received and is under review.', TRUE),
(56, 2, 'Issue Forwarded', 'Your complaint has been forwarded to the Street Lighting Department for action.', TRUE),
(57, 3, 'Issue Assigned', 'Your complaint about garbage overflow has been assigned to the area supervisor.', FALSE),
(58, 4, 'Work In Progress', 'Repair work has started on the water leakage issue you reported.', TRUE),
(64, 10, 'Issue Resolved', 'Your complaint about garbage collection has been resolved. Please provide feedback.', TRUE),
-- Notifications for Workers
(39, 4, 'New Assignment', 'You have been assigned to fix water pipe leakage at Adyar Signal.', TRUE),
(41, 10, 'Task Completed', 'Your work evidence has been approved by the Area Head.', TRUE),
(47, 13, 'New Assignment', 'You have been assigned to clear drainage block at Peelamedu.', FALSE),
-- Notifications for Area Heads
(27, 4, 'Work Update', 'Worker has submitted progress report for water leakage repair.', FALSE),
(29, 10, 'Approval Required', 'Worker has completed garbage clearance. Please review and approve.', TRUE),
-- Notifications for Dept Heads
(11, 3, 'Issue Escalation', 'Garbage overflow issue at Mylapore requires immediate attention.', TRUE),
(12, 4, 'Status Update', 'Water leakage repair is 50% complete.', FALSE),
-- Notifications for Gov Body
(3, 10, 'Approval Request', 'Issue #10 has been approved by Department Head. Awaiting your final approval.', TRUE);

-- ============================================================
-- Activity Log
-- ============================================================
INSERT INTO activity_log (issue_id, actor_id, action, details) VALUES
-- Issue 1 Activities
(1, 55, 'ISSUE_CREATED', 'Citizen Arun Kumar reported pothole on Anna Salai'),
(1, 3, 'ISSUE_VIEWED', 'Government body reviewed the issue'),
-- Issue 2 Activities
(2, 56, 'ISSUE_CREATED', 'Citizen Priya Lakshmi reported broken street light'),
(2, 3, 'ISSUE_FORWARDED', 'Issue forwarded to Street Lighting Department'),
-- Issue 3 Activities
(3, 57, 'ISSUE_CREATED', 'Citizen Karthik reported garbage overflow at Mylapore'),
(3, 3, 'ISSUE_FORWARDED', 'Issue forwarded to SWM Department'),
(3, 17, 'ISSUE_ASSIGNED', 'Department Head assigned issue to Area Head'),
-- Issue 4 Activities
(4, 58, 'ISSUE_CREATED', 'Citizen Deepika reported water leakage at Adyar'),
(4, 3, 'ISSUE_FORWARDED', 'Issue forwarded to Water Supply Department'),
(4, 16, 'ISSUE_ASSIGNED', 'Assigned to Area Head Perumal Konar'),
(4, 27, 'ISSUE_ASSIGNED', 'Area Head assigned to Worker Arjun Selvam'),
(4, 39, 'WORK_STARTED', 'Worker started repair work'),
(4, 39, 'EVIDENCE_UPLOADED', 'Worker uploaded progress photos'),
-- Issue 10 Activities
(10, 64, 'ISSUE_CREATED', 'Citizen Kavitha reported garbage not collected'),
(10, 3, 'ISSUE_FORWARDED', 'Issue forwarded to SWM Department'),
(10, 17, 'ISSUE_ASSIGNED', 'Assigned to Area Head'),
(10, 29, 'ISSUE_ASSIGNED', 'Assigned to Worker Chelladurai'),
(10, 41, 'WORK_STARTED', 'Worker started garbage clearance'),
(10, 41, 'WORK_COMPLETED', 'Worker completed garbage clearance'),
(10, 41, 'EVIDENCE_UPLOADED', 'Worker uploaded completion photos'),
(10, 29, 'HEAD_APPROVED', 'Area Head approved the work'),
(10, 17, 'HEAD_APPROVED', 'Department Head approved'),
(10, 3, 'GOV_APPROVED', 'Government body gave final approval'),
(10, 64, 'FEEDBACK_SUBMITTED', 'Citizen submitted positive feedback'),
-- Issue 12 Activities
(12, 66, 'ISSUE_CREATED', 'Citizen Vijay Anand reported pothole at Gandhipuram'),
(12, 4, 'ISSUE_FORWARDED', 'Issue forwarded to Roads Department'),
(12, 22, 'ISSUE_ASSIGNED', 'Assigned to Area Head'),
-- Issue 13 Activities
(13, 67, 'ISSUE_CREATED', 'Citizen Kavitha reported drainage block at Peelamedu'),
(13, 4, 'ISSUE_FORWARDED', 'Issue forwarded to Water Department'),
(13, 23, 'ISSUE_ASSIGNED', 'Assigned to Area Head'),
(13, 34, 'ISSUE_ASSIGNED', 'Assigned to Worker'),
(13, 47, 'WORK_STARTED', 'Worker started drainage clearing');

SELECT 'Pin2Fix database with comprehensive sample data created successfully!' AS Status;
