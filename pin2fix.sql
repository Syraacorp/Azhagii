-- ============================================================
-- Pin2Fix - GEO TAGGED ISSUE REPORTING SYSTEM
-- Complete MySQL 8.x Schema (Single file)
-- NOTE: Passwords stored in plaintext per user request (NOT secure).
-- ============================================================

-- 0) Create and use database
CREATE DATABASE IF NOT EXISTS pin2fix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pin2fix;

-- ============================================================
-- 1) USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  user_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL, -- plaintext as requested
  role ENUM('CITIZEN','GOV_BODY','DEPT_HEAD','AREA_HEAD','WORKER','ADMIN') NOT NULL,
  phone VARCHAR(32),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2) GOVERNMENT BODIES
-- ============================================================
CREATE TABLE IF NOT EXISTS government_bodies (
  gov_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  contact_email VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3) DEPARTMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS departments (
  dept_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  gov_id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  contact_email VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dept_gov FOREIGN KEY (gov_id) REFERENCES government_bodies(gov_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4) ISSUES (SPATIAL POINT + SPATIAL INDEX)
--    Use POINT(lng lat). To insert: ST_GeomFromText(CONCAT('POINT(', :lng, ' ', :lat, ')'))
-- ============================================================
CREATE TABLE IF NOT EXISTS issues (
  issue_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  status ENUM(
      'PENDING','TRIAGED','ASSIGNED','IN_PROGRESS',
      'WORK_COMPLETED_PENDING_HEAD_APPROVAL',
      'PENDING_GOV_APPROVAL','COMPLETED','REOPENED','REJECTED'
  ) NOT NULL DEFAULT 'PENDING',
  severity TINYINT NOT NULL DEFAULT 3,
  location POINT NOT NULL,
  address_text VARCHAR(512),
  reporter_id BIGINT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_issues_reporter FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE SET NULL,
  SPATIAL INDEX idx_issues_location (location)
) ENGINE=InnoDB;

-- ============================================================
-- 5) PHOTOS (initial evidence attached by reporter)
-- ============================================================
CREATE TABLE IF NOT EXISTS photos (
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
CREATE TABLE IF NOT EXISTS assignments (
  assignment_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  assigned_by BIGINT,    -- user id who assigned (gov body / dept head)
  assignee_id BIGINT,    -- user who will act (dept_head/area_head/worker)
  role_assignee ENUM('DEPT_HEAD','AREA_HEAD','WORKER'),
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  due_date TIMESTAMP NULL,
  status ENUM('ASSIGNED','IN_PROGRESS','COMPLETED','REOPENED') NOT NULL DEFAULT 'ASSIGNED',
  comment TEXT,
  CONSTRAINT fk_assign_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_assign_by FOREIGN KEY (assigned_by) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_assign_to FOREIGN KEY (assignee_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 7) WORK EVIDENCE (uploaded by worker when task completed)
-- ============================================================
CREATE TABLE IF NOT EXISTS work_evidence (
  evidence_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  assignment_id BIGINT NOT NULL,
  worker_id BIGINT,
  url TEXT NOT NULL,
  notes TEXT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evidence_assign FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
  CONSTRAINT fk_evidence_worker FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 8) HEAD APPROVALS (Area Head / Dept Head approvals)
-- ============================================================
CREATE TABLE IF NOT EXISTS head_approvals (
  approval_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  assignment_id BIGINT NOT NULL,
  approved_by BIGINT,
  status ENUM('APPROVED','REJECTED') NOT NULL,
  comment TEXT,
  approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_head_approval_assign FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
  CONSTRAINT fk_head_approval_user FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 9) GOV FINAL APPROVAL (government body confirmation)
-- ============================================================
CREATE TABLE IF NOT EXISTS gov_approvals (
  gov_approval_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  gov_id BIGINT,
  approved_by BIGINT,
  comment TEXT,
  approved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gov_approval_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_gov_approval_gov FOREIGN KEY (gov_id) REFERENCES government_bodies(gov_id) ON DELETE SET NULL,
  CONSTRAINT fk_gov_approval_user FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 10) FEEDBACK (citizen feedback after notification)
-- ============================================================
CREATE TABLE IF NOT EXISTS feedback (
  feedback_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  user_id BIGINT,
  rating TINYINT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_feedback_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 11) ACTIVITY LOGS (audit trail)
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_logs (
  log_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT,
  actor_id BIGINT,
  action VARCHAR(255),
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_logs_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_logs_user FOREIGN KEY (actor_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 12) ISSUE STATUS HISTORY (detailed status transitions)
-- ============================================================
CREATE TABLE IF NOT EXISTS issue_status_history (
  hist_id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  old_status VARCHAR(50),
  new_status VARCHAR(50),
  changed_by BIGINT,
  reason TEXT,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hist_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_hist_user FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 13) GOV BODY SATISFIED (records of final confirmations)
-- ============================================================
CREATE TABLE IF NOT EXISTS gov_body_satisfied (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  issue_id BIGINT NOT NULL,
  gov_id BIGINT,
  confirmed_by BIGINT,
  confirmed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  comment TEXT,
  CONSTRAINT fk_gov_satisfied_issue FOREIGN KEY (issue_id) REFERENCES issues(issue_id) ON DELETE CASCADE,
  CONSTRAINT fk_gov_satisfied_gov FOREIGN KEY (gov_id) REFERENCES government_bodies(gov_id) ON DELETE SET NULL,
  CONSTRAINT fk_gov_satisfied_user FOREIGN KEY (confirmed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TRIGGERS
-- - Record status changes into issue_status_history
-- - Auto-update issue status when assignment marked COMPLETED
-- ============================================================
DELIMITER $$
CREATE TRIGGER trg_issues_status_change
BEFORE UPDATE ON issues
FOR EACH ROW
BEGIN
  IF OLD.status <> NEW.status THEN
    INSERT INTO issue_status_history (issue_id, old_status, new_status, changed_by, reason, changed_at)
    VALUES (OLD.issue_id, OLD.status, NEW.status, NULL, CONCAT('Status changed to: ', NEW.status), NOW());
  END IF;
END$$

CREATE TRIGGER trg_assign_complete
AFTER UPDATE ON assignments
FOR EACH ROW
BEGIN
  IF OLD.status <> NEW.status AND NEW.status = 'COMPLETED' THEN
    UPDATE issues SET status = 'WORK_COMPLETED_PENDING_HEAD_APPROVAL', updated_at = NOW() WHERE issue_id = NEW.issue_id;
  END IF;
END$$
DELIMITER ;

-- ============================================================
-- VIEWS (dashboard / admin / GIS-friendly)
-- ============================================================

-- 1. Issue + Reporter Details
CREATE OR REPLACE VIEW vw_issue_full AS
SELECT 
  i.issue_id, i.title, i.description, i.status, i.severity,
  i.address_text, i.created_at, i.updated_at,
  u.user_id AS reporter_id, u.name AS reporter_name, u.email AS reporter_email
FROM issues i
LEFT JOIN users u ON u.user_id = i.reporter_id;

-- 2. Latest Assignment Per Issue
CREATE OR REPLACE VIEW vw_issue_latest_assign AS
SELECT 
  i.issue_id, i.title, i.status,
  a.assignment_id, a.assignee_id, u.name AS assignee_name,
  a.role_assignee, a.status AS assignment_status, a.assigned_at
FROM issues i
LEFT JOIN assignments a ON a.assignment_id = (
    SELECT assignment_id FROM assignments 
    WHERE issue_id = i.issue_id ORDER BY assigned_at DESC LIMIT 1
)
LEFT JOIN users u ON u.user_id = a.assignee_id;

-- 3. Issues Pending Government Approval (PENDING_GOV_APPROVAL)
CREATE OR REPLACE VIEW vw_issues_pending_gov AS
SELECT i.issue_id, i.title, i.status, i.severity, i.address_text, i.created_at
FROM issues i
WHERE i.status = 'PENDING_GOV_APPROVAL';

-- 4. Completed Issues with Worker Evidence
CREATE OR REPLACE VIEW vw_completed_evidence AS
SELECT 
    i.issue_id, i.title, i.status,
    we.evidence_id, we.url AS evidence_url, we.notes AS evidence_notes,
    w.user_id AS worker_id, w.name AS worker_name,
    a.assignment_id, a.assigned_at
FROM issues i
LEFT JOIN assignments a ON a.issue_id = i.issue_id
LEFT JOIN work_evidence we ON we.assignment_id = a.assignment_id
LEFT JOIN users w ON w.user_id = we.worker_id
WHERE i.status = 'COMPLETED';

-- 5. Feedback Summary per Issue
CREATE OR REPLACE VIEW vw_issue_feedback AS
SELECT 
    i.issue_id, i.title, 
    ROUND(AVG(f.rating),2) AS avg_rating,
    COUNT(f.feedback_id) AS feedback_count
FROM issues i
LEFT JOIN feedback f ON f.issue_id = i.issue_id
GROUP BY i.issue_id;

-- 6. Department Performance Overview
CREATE OR REPLACE VIEW vw_dept_performance AS
SELECT 
    d.dept_id, d.name AS department_name,
    COUNT(DISTINCT a.issue_id) AS total_issues_assigned,
    SUM(CASE WHEN i.status = 'COMPLETED' THEN 1 ELSE 0 END) AS completed_issues,
    SUM(CASE WHEN i.status IN ('REOPENED','REJECTED') THEN 1 ELSE 0 END) AS problematic_issues
FROM departments d
LEFT JOIN assignments a ON a.role_assignee = 'DEPT_HEAD' AND a.assignee_id IS NOT NULL
LEFT JOIN issues i ON i.issue_id = a.issue_id
GROUP BY d.dept_id;

-- 7. Issue Workflow Timeline (simple snapshot)
CREATE OR REPLACE VIEW vw_issue_timeline AS
SELECT
    i.issue_id, i.title,
    a.assignment_id, a.assigned_at,
    ha.status AS head_status, ha.approved_at AS head_approved_at,
    ga.approved_at AS gov_approved_at,
    i.status AS current_status, i.updated_at
FROM issues i
LEFT JOIN assignments a ON a.issue_id = i.issue_id
LEFT JOIN head_approvals ha ON ha.assignment_id = a.assignment_id
LEFT JOIN gov_approvals ga ON ga.issue_id = i.issue_id;

-- 8. GIS Coordinates View (latitude / longitude)
CREATE OR REPLACE VIEW vw_issue_coords AS
SELECT 
    issue_id, title, status,
    ST_X(location) AS longitude,
    ST_Y(location) AS latitude
FROM issues;

-- ============================================================
-- SAMPLE INDEX SUGGESTIONS (additional non-spatial indexes for performance)
-- ============================================================
CREATE INDEX idx_issues_status ON issues(status);
CREATE INDEX idx_assign_assignee ON assignments(assignee_id);
CREATE INDEX idx_photos_issue ON photos(issue_id);
CREATE INDEX idx_feedback_issue ON feedback(issue_id);
CREATE INDEX idx_head_approvals_assign ON head_approvals(assignment_id);

-- ============================================================
-- NOTES:
-- 1) Insert POINT: use ST_GeomFromText(CONCAT('POINT(', :lng, ' ', :lat, ')'))
--    or ST_PointFromText('POINT(lng lat)') depending on client library.
-- 2) Query distance (meters): use ST_Distance_Sphere(location, POINT(:lng, :lat))
-- 3) For production, remove plaintext password storage and add proper security.
-- ============================================================