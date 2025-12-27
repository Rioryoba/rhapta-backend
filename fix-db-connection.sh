#!/bin/bash

# Script to help fix Laravel database connection issues

echo "=== Laravel Database Connection Fixer ==="
echo ""

# Check current .env DB settings
echo "Current database configuration in .env:"
grep -E "^DB_" .env 2>/dev/null | head -10
echo ""

# Check if MySQL is accessible
echo "Checking MySQL connection..."
if command -v mysql &> /dev/null; then
    echo "MySQL client found"
    
    # Try to connect with common credentials
    echo ""
    echo "Attempting to connect to MySQL..."
    echo "Note: You may need to enter your MySQL root password"
    echo ""
    
    # Check if we can list databases
    mysql -u root -p -e "SHOW DATABASES;" 2>&1 | head -5
    
    echo ""
    echo "=== Suggested Fix ==="
    echo ""
    echo "If connection failed, try one of these solutions:"
    echo ""
    echo "Option 1: Create a Laravel database user (Recommended)"
    echo "  sudo mysql"
    echo "  CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo "  CREATE USER IF NOT EXISTS 'laravel'@'localhost' IDENTIFIED BY 'your_password';"
    echo "  GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'localhost';"
    echo "  FLUSH PRIVILEGES;"
    echo "  EXIT;"
    echo ""
    echo "Then update your .env file:"
    echo "  DB_USERNAME=laravel"
    echo "  DB_PASSWORD=your_password"
    echo "  DB_DATABASE=laravel"
    echo ""
    echo "Option 2: Use SQLite (for development)"
    echo "  Update .env:"
    echo "  DB_CONNECTION=sqlite"
    echo "  DB_DATABASE=/absolute/path/to/database.sqlite"
    echo ""
    echo "Then create the database:"
    echo "  touch database/database.sqlite"
    echo "  php artisan migrate"
    echo ""
else
    echo "MySQL client not found. Please install MySQL client tools."
fi





