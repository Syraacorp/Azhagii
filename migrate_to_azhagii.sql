-- ============================================
-- Migration: Update Ziyaa to Azhagii
-- Date: 2026-02-14
-- Description: Updates all role names from Ziyaa to Azhagii
-- ============================================

USE ziya;

-- Backup note: Before running this migration, ensure you have a database backup
-- Run: mysqldump -u root -p ziya > backup_before_azhagii_migration.sql

-- Update user roles
UPDATE users SET role = 'azhagiiStudents' WHERE role = 'ziyaaStudents';
UPDATE users SET role = 'azhagiiCoordinator' WHERE role = 'ziyaaCoordinator';
UPDATE users SET role = 'adminAzhagii' WHERE role = 'adminZiyaa';

-- Verify the changes
SELECT role, COUNT(*) as count FROM users GROUP BY role;

-- Display migration summary
SELECT 
    'Migration completed successfully!' as message,
    (SELECT COUNT(*) FROM users WHERE role = 'azhagiiStudents') as azhagiiStudents_count,
    (SELECT COUNT(*) FROM users WHERE role = 'azhagiiCoordinator') as azhagiiCoordinator_count,
    (SELECT COUNT(*) FROM users WHERE role = 'adminAzhagii') as adminAzhagii_count,
    (SELECT COUNT(*) FROM users WHERE role = 'superAdmin') as superAdmin_count;
