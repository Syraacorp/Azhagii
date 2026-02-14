-- ============================================
-- COMPREHENSIVE DATABASE CAMELCASE MIGRATION
-- Date: 2026-02-14  
-- Description: Converts all tables and columns to camelCase
-- ============================================

-- BACKUP FIRST: mysqldump -u root -p ziya > backup_before_camelcase.sql

USE ziya;

SET FOREIGN_KEY_CHECKS=0;

-- ============================================
-- STEP 1: RENAME TABLES
-- ============================================

ALTER TABLE course_colleges RENAME TO courseColleges;
ALTER TABLE course_content RENAME TO courseContent;
ALTER TABLE profile_requests RENAME TO profileRequests;

-- ============================================
-- STEP 2: RENAME COLUMNS - colleges
-- ============================================

ALTER TABLE colleges 
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ============================================
-- STEP 3: RENAME COLUMNS - courseColleges
-- ============================================

ALTER TABLE courseColleges
CHANGE COLUMN course_id courseId INT(11) NOT NULL,
CHANGE COLUMN college_id collegeId INT(11) NOT NULL,
CHANGE COLUMN assigned_by assignedBy INT(11),
CHANGE COLUMN assigned_at assignedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ============================================
-- STEP 4: RENAME COLUMNS - courseContent  
-- ============================================

ALTER TABLE courseContent
CHANGE COLUMN course_id courseId INT(11) NOT NULL,
CHANGE COLUMN content_type contentType ENUM('video','pdf','text','link') NOT NULL,
CHANGE COLUMN content_data contentData TEXT,
CHANGE COLUMN uploaded_by uploadedBy INT(11),
CHANGE COLUMN college_id collegeId INT(11),
CHANGE COLUMN sort_order sortOrder INT(11) DEFAULT 0,
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN subject_id subjectId INT(11);

-- ============================================
-- STEP 5: RENAME COLUMNS - courses
-- ============================================

ALTER TABLE courses
CHANGE COLUMN course_code courseCode VARCHAR(50),
CHANGE COLUMN course_type courseType VARCHAR(50),
CHANGE COLUMN academic_year academicYear VARCHAR(20),
CHANGE COLUMN created_by createdBy INT(11),
CHANGE COLUMN approved_by approvedBy INT(11),
CHANGE COLUMN approved_at approvedAt TIMESTAMP NULL,
CHANGE COLUMN rejection_reason rejectionReason TEXT,
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN updated_at updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ============================================
-- STEP 6: RENAME COLUMNS - enrollments
-- ============================================

ALTER TABLE enrollments
CHANGE COLUMN student_id studentId INT(11) NOT NULL,
CHANGE COLUMN course_id courseId INT(11) NOT NULL,
CHANGE COLUMN enrolled_at enrolledAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN completed_at completedAt TIMESTAMP NULL;

-- ============================================
-- STEP 7: RENAME COLUMNS - events
-- ============================================

ALTER TABLE events
CHANGE COLUMN event_date eventDate DATE NOT NULL,
CHANGE COLUMN event_time eventTime TIME NOT NULL,
CHANGE COLUMN image_url imageUrl VARCHAR(255),
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ============================================
-- STEP 8: RENAME COLUMNS - profileRequests
-- ============================================

ALTER TABLE profileRequests
CHANGE COLUMN user_id userId INT(11) NOT NULL,
CHANGE COLUMN request_reason requestReason TEXT,
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN resolved_at resolvedAt TIMESTAMP NULL,
CHANGE COLUMN resolved_by resolvedBy INT(11);

-- ============================================
-- STEP 9: RENAME COLUMNS - subjects
-- ============================================

ALTER TABLE subjects
CHANGE COLUMN course_id courseId INT(11) NOT NULL,
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ============================================
-- STEP 10: RENAME COLUMNS - topics
-- ============================================

ALTER TABLE topics
CHANGE COLUMN subject_id subjectId INT(11) NOT NULL,
CHANGE COLUMN created_by createdBy INT(11),
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ============================================
-- STEP 11: RENAME COLUMNS - users
-- ============================================

ALTER TABLE users
CHANGE COLUMN college_id collegeId INT(11),
CHANGE COLUMN roll_number rollNumber VARCHAR(20),
CHANGE COLUMN profile_photo profilePhoto VARCHAR(255),
CHANGE COLUMN github_url githubUrl VARCHAR(255),
CHANGE COLUMN linkedin_url linkedinUrl VARCHAR(255),
CHANGE COLUMN hackerrank_url hackerrankUrl VARCHAR(255),
CHANGE COLUMN leetcode_url leetcodeUrl VARCHAR(255),
CHANGE COLUMN is_locked isLocked TINYINT(1) DEFAULT 0,
CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

SET FOREIGN_KEY_CHECKS=1;

-- ============================================
-- VERIFICATION
-- ============================================

SELECT 'Migration completed!' as message;
SHOW TABLES;
