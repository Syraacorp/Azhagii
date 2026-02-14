-- ============================================  
-- OPTIONAL: Rename Database from 'ziya' to 'azhagii'
-- Date: 2026-02-14
-- ============================================

-- WARNING: This is OPTIONAL. Only run if you want to rename the entire database.
-- The application will continue to work with database name 'ziya'

-- BEFORE RUNNING:
-- 1. Backup your database: mysqldump -u root -p ziya > backup_ziya_full.sql
-- 2. Update db.php to use 'azhagii' as database name
-- 3. Close all connections to the database

-- Method 1: Create new database and copy data
CREATE DATABASE IF NOT EXISTS azhagii CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Method 2: Dump and restore (recommended)
-- Step by step:
-- 1. Export: mysqldump -u root -p ziya > ziya_export.sql
-- 2. Create: CREATE DATABASE azhagii;
-- 3. Import: mysql -u root -p azhagii < ziya_export.sql
-- 4. Verify the import was successful
-- 5. Update db.php: $dbname = getenv('DB_NAME') ?: 'azhagii';
-- 6. Test the application thoroughly
-- 7. Drop old database: DROP DATABASE ziya;

-- Verification queries (run against new 'azhagii' database)
USE azhagii;
SELECT 'Tables' as type, COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'azhagii'
UNION ALL
SELECT 'Users', COUNT(*) FROM users
UNION ALL  
SELECT 'Colleges', COUNT(*) FROM colleges
UNION ALL
SELECT 'Courses', COUNT(*) FROM courses;

-- Note: Remember to update environment variables if using Docker:
-- DB_NAME=azhagii
