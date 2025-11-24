-- Add is_active column to users table for account activation/deactivation
ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=deactivated' AFTER role;

-- Update existing users to be active by default
UPDATE users SET is_active = 1 WHERE is_active IS NULL;
