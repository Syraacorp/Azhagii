-- Final cleanup to complete camelCase migration

USE ziya;

SET FOREIGN_KEY_CHECKS=0;

-- Fix remaining snake_case columns

-- colleges
ALTER TABLE colleges CHANGE COLUMN created_at createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- coursecolleges
ALTER TABLE coursecolleges CHANGE COLUMN course_id courseId INT(11) NOT NULL;

-- coursecontent
ALTER TABLE coursecontent CHANGE COLUMN course_id courseId INT(11) NOT NULL;

-- courses
ALTER TABLE courses CHANGE COLUMN course_code courseCode VARCHAR(50) UNIQUE;

-- enrollments
ALTER TABLE enrollments CHANGE COLUMN student_id studentId INT(11) NOT NULL;

-- events
ALTER TABLE events CHANGE COLUMN event_date eventDate DATE NOT NULL;

-- profilerequests
ALTER TABLE profilerequests CHANGE COLUMN user_id userId INT(11) NOT NULL;

-- subjects
ALTER TABLE subjects CHANGE COLUMN course_id courseId INT(11) NOT NULL;

-- topics
ALTER TABLE topics CHANGE COLUMN subject_id subjectId INT(11) NOT NULL;

-- users
ALTER TABLE users CHANGE COLUMN college_id collegeId INT(11);

SET FOREIGN_KEY_CHECKS=1;
