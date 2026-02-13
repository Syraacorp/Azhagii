-- ============================================
-- Migration v3: Add department, year, roll_number, username to users
-- ============================================
USE ziya;

ALTER TABLE users ADD COLUMN username VARCHAR(100) UNIQUE NULL AFTER email;
ALTER TABLE users ADD COLUMN department VARCHAR(50) NULL AFTER college_id;
ALTER TABLE users ADD COLUMN year VARCHAR(20) NULL AFTER department;
ALTER TABLE users ADD COLUMN roll_number VARCHAR(20) UNIQUE NULL AFTER year;
