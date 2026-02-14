-- ============================================
-- FIXED CAMELCASE MIGRATION
-- Handles current database state
-- ============================================

USE ziya;

SET FOREIGN_KEY_CHECKS=0;

-- Fix table names that got partially renamed
ALTER TABLE coursecontent RENAME TO courseContent;
ALTER TABLE profilerequests RENAME TO profileRequests;
ALTER TABLE course_colleges RENAME TO courseColleges;

-- colleges table
ALTER TABLE colleges CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- courseColleges table
ALTER TABLE courseColleges CHANGE COLUMN course_id courseId INT(11) NOT NULL;
ALTER TABLE courseColleges CHANGE COLUMN college_id collegeId INT(11) NOT NULL;
ALTER TABLE courseColleges CHANGE COLUMN assigned_by assignedBy INT(11);
ALTER TABLE courseColleges CHANGE COLUMN assigned_at assignedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- courseContent table
ALTER TABLE courseContent CHANGE COLUMN course_id courseId INT(11) NOT NULL;
ALTER TABLE courseContent CHANGE COLUMN content_type contentType ENUM('video','pdf','text','link') NOT NULL;
ALTER TABLE courseContent CHANGE COLUMN content_data contentData TEXT;
ALTER TABLE courseContent CHANGE COLUMN uploaded_by uploadedBy INT(11);
ALTER TABLE courseContent CHANGE COLUMN college_id collegeId INT(11);
ALTER TABLE courseContent CHANGE COLUMN sort_order sortOrder INT(11) DEFAULT 0;
ALTER TABLE courseContent CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE courseContent CHANGE COLUMN subject_id subjectId INT(11);

-- courses table
ALTER TABLE courses CHANGE COLUMN course_code courseCode VARCHAR(50) UNIQUE;
ALTER TABLE courses CHANGE COLUMN course_type courseType VARCHAR(50);
ALTER TABLE courses CHANGE COLUMN academic_year academicYear VARCHAR(20);
ALTER TABLE courses CHANGE COLUMN created_by createdBy INT(11);
ALTER TABLE courses CHANGE COLUMN approved_by approvedBy INT(11);
ALTER TABLE courses CHANGE COLUMN approved_at approvedAt TIMESTAMP NULL;
ALTER TABLE courses CHANGE COLUMN rejection_reason rejectionReason TEXT;
ALTER TABLE courses CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE courses CHANGE COLUMN updated_at updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- enrollments table
ALTER TABLE enrollments CHANGE COLUMN student_id studentId INT(11) NOT NULL;
ALTER TABLE enrollments CHANGE COLUMN course_id courseId INT(11) NOT NULL;
ALTER TABLE enrollments CHANGE COLUMN enrolled_at enrolledAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE enrollments CHANGE COLUMN completed_at completedAt TIMESTAMP NULL;

-- events table
ALTER TABLE events CHANGE COLUMN event_date eventDate DATE NOT NULL;
ALTER TABLE events CHANGE COLUMN event_time eventTime TIME;
ALTER TABLE events CHANGE COLUMN image_url imageUrl VARCHAR(255);
ALTER TABLE events CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- profileRequests table
ALTER TABLE profileRequests CHANGE COLUMN user_id userId INT(11) NOT NULL;
ALTER TABLE profileRequests CHANGE COLUMN request_reason requestReason TEXT;
ALTER TABLE profileRequests CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE profileRequests CHANGE COLUMN resolved_at resolvedAt TIMESTAMP NULL;
ALTER TABLE profileRequests CHANGE COLUMN resolved_by resolvedBy INT(11);

-- subjects table
ALTER TABLE subjects CHANGE COLUMN course_id courseId INT(11) NOT NULL;
ALTER TABLE subjects CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- topics table
ALTER TABLE topics CHANGE COLUMN subject_id subjectId INT(11) NOT NULL;
ALTER TABLE topics CHANGE COLUMN created_by createdBy INT(11);
ALTER TABLE topics CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- users table
ALTER TABLE users CHANGE COLUMN college_id collegeId INT(11);
ALTER TABLE users CHANGE COLUMN roll_number rollNumber VARCHAR(20);
ALTER TABLE users CHANGE COLUMN profile_photo profilePhoto VARCHAR(255);
ALTER TABLE users CHANGE COLUMN github_url githubUrl VARCHAR(255);
ALTER TABLE users CHANGE COLUMN linkedin_url linkedinUrl VARCHAR(255);
ALTER TABLE users CHANGE COLUMN hackerrank_url hackerrankUrl VARCHAR(255);
ALTER TABLE users CHANGE COLUMN leetcode_url leetcodeUrl VARCHAR(255);
ALTER TABLE users CHANGE COLUMN is_locked isLocked TINYINT(1) DEFAULT 0;
ALTER TABLE users CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

SET FOREIGN_KEY_CHECKS=1;
