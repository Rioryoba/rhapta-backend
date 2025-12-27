-- Enable password authentication for root user
ALTER USER 'root'@'localhost' IDENTIFIED BY 'root';
FLUSH PRIVILEGES;
SELECT 'Root user password authentication enabled!' AS status;
