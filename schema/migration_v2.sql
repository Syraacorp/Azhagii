-- ============================================
-- Ziyaa LMS - Migration V2
-- Adds: Course Approval Workflow, Topics,
--        Syllabus, Academic Year/Semester
-- From Reference: LearningManagement repo
-- ============================================

USE ziya;

-- ============================================
-- 1. ALTER courses table - add approval & academic fields
-- ============================================

-- Add syllabus PDF path
ALTER TABLE courses ADD COLUMN syllabus VARCHAR(500) DEFAULT NULL AFTER thumbnail;

-- Add semester
ALTER TABLE courses ADD COLUMN semester VARCHAR(20) DEFAULT NULL AFTER syllabus;

-- Add regulation
ALTER TABLE courses ADD COLUMN regulation VARCHAR(50) DEFAULT NULL AFTER semester;

-- Add academic year
ALTER TABLE courses ADD COLUMN academic_year VARCHAR(20) DEFAULT NULL AFTER regulation;

-- Add course_code
ALTER TABLE courses ADD COLUMN course_code VARCHAR(50) DEFAULT NULL AFTER title;

-- Add course_type (theory/lab/elective)
ALTER TABLE courses ADD COLUMN course_type VARCHAR(50) DEFAULT 'theory' AFTER category;

-- Add approval fields
ALTER TABLE courses ADD COLUMN approved_by INT DEFAULT NULL AFTER status;
ALTER TABLE courses ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL AFTER approved_by;
ALTER TABLE courses ADD COLUMN rejection_reason TEXT DEFAULT NULL AFTER approved_at;

-- Add foreign key for approved_by
ALTER TABLE courses ADD FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;

-- Modify status ENUM to include pending/rejected
ALTER TABLE courses MODIFY COLUMN status ENUM('draft','pending','active','rejected','archived') DEFAULT 'draft';

-- ============================================
-- 2. CREATE topics table
-- ============================================
CREATE TABLE IF NOT EXISTS topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. Add subject_id index for performance
-- ============================================
-- (subject_id FK already exists from migration_subjects)

-- ============================================
-- Done! New features:
-- - Coordinators can create courses (status=pending)
-- - Admins approve/reject courses
-- - Courses have syllabus PDF, semester, regulation,
--   academic year, course_code, course_type
-- - Topics can be added under subjects (units)
-- - Students only see approved (active) courses
-- ============================================
