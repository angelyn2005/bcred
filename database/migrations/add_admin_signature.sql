-- Add admin signature field to users table
ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL AFTER photo;

-- Or create a separate signatures table for better organization
CREATE TABLE IF NOT EXISTS admin_signatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    signature_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
