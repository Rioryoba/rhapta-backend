-- SQL script to create the database and user for Rhapta Laravel application
-- Run this with: sudo mysql < setup-database.sql

-- Create the database
CREATE DATABASE IF NOT EXISTS rhapta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create the user (if it doesn't exist)
CREATE USER IF NOT EXISTS 'rhapta_user'@'localhost' IDENTIFIED BY 'rhapta_pass';

-- Grant privileges
GRANT ALL PRIVILEGES ON rhapta_db.* TO 'rhapta_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Show confirmation
SELECT 'Database and user created successfully!' AS Status;
SHOW DATABASES LIKE 'rhapta_db';





