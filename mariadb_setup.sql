-- Create database and dedicated user
CREATE DATABASE IF NOT EXISTS rhapta_db;
CREATE USER IF NOT EXISTS 'rhapta_user'@'localhost' IDENTIFIED BY 'rhapta_pass';
GRANT ALL PRIVILEGES ON rhapta_db.* TO 'rhapta_user'@'localhost';
FLUSH PRIVILEGES;
SELECT 'Database and user created successfully!' AS status;
