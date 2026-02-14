# Database Migration: Ziyaa → Azhagii

## Overview
This migration updates all role names in the database from the old "Ziyaa" naming convention to the new "Azhagii" branding.

## What Gets Updated

### User Roles (in `users` table):
- `ziyaaStudents` → `azhagiiStudents`
- `ziyaaCoordinator` → `azhagiiCoordinator`  
- `adminZiyaa` → `adminAzhagii`

## How to Run the Migration

### Method 1: Using PHP Script (Recommended)

1. **Backup your database first!**
   ```bash
   mysqldump -u root -p ziya > backup_before_azhagii.sql
   ```

2. **Run the migration via browser:**
   ```
   http://localhost/Ziya/migrate_db.php?key=azhagii_migration_2026
   ```

3. **Or run via command line:**
   ```bash
   cd d:\Software\Host\htdocs\Ziya
   php migrate_db.php azhagii_migration_2026
   ```

### Method 2: Using SQL Directly

1. **Backup your database first!**
   ```bash
   mysqldump -u root -p ziya > backup_before_azhagii.sql
   ```

2. **Run the SQL migration:**
   ```bash
   mysql -u root -p ziya < migrate_to_azhagii.sql
   ```

   Or via MySQL command line:
   ```sql
   USE ziya;
   source d:/Software/Host/htdocs/Ziya/migrate_to_azhagii.sql
   ```

## Post-Migration Steps

1. **Clear all active sessions** - Users must log out and log back in
2. **Verify the migration** by checking user roles in the database:
   ```sql
   SELECT role, COUNT(*) as count FROM users GROUP BY role;
   ```
3. **Test the application** with different user roles to ensure everything works

## Expected Results

After migration, you should see:
- ✅ All "ziyaa" role names updated to "azhagii"
- ✅ No database errors
- ✅ All users can log in with updated roles
- ✅ All role-based permissions working correctly

## Rollback (if needed)

If you need to revert the changes:
```sql
UPDATE users SET role = 'ziyaaStudents' WHERE role = 'azhagiiStudents';
UPDATE users SET role = 'ziyaaCoordinator' WHERE role = 'azhagiiCoordinator';
UPDATE users SET role = 'adminZiyaa' WHERE role = 'adminAzhagii';
```

Or restore from backup:
```bash
mysql -u root -p ziya < backup_before_azhagii.sql
```

## Verification Queries

Check if migration was successful:
```sql
-- Should return 0 rows
SELECT * FROM users WHERE role IN ('ziyaaStudents', 'ziyaaCoordinator', 'adminZiyaa');

-- Should show new role names
SELECT role, COUNT(*) as count FROM users GROUP BY role;
```

## Notes

- The migration uses transactions, so it will rollback automatically on any error
- A marker file `migration_completed.txt` will be created after successful migration
- The migration script is locked with a key to prevent accidental execution
- All code files have already been updated to use "Azhagii" naming
