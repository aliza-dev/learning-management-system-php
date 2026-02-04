# MySQL Shutdown Issue - Fix Guide

## Problem
MySQL in XAMPP is shutting down unexpectedly, causing blank screens in your PHP application.

## Common Causes & Solutions

### 1. **Port Conflict (Most Common)**
Another MySQL instance is already running on port 3306.

**Solution:**
- Check if MySQL Server or MariaDB is installed separately
- Stop any other MySQL services:
  - Open Services (Win+R → `services.msc`)
  - Look for "MySQL" or "MariaDB" services
  - Stop them if running
- Or change XAMPP MySQL port:
  - Edit `C:\xampp\mysql\bin\my.ini`
  - Change `port=3306` to `port=3307`
  - Update `db_connect.php` to use port 3307

### 2. **Corrupted MySQL Data Files**
**Solution:**
- Backup your databases first!
- Stop XAMPP completely
- Rename `C:\xampp\mysql\data` to `C:\xampp\mysql\data_backup`
- Copy `C:\xampp\mysql\backup` to `C:\xampp\mysql\data` (if exists)
- Or reinstall MySQL in XAMPP

### 3. **Antivirus/Firewall Blocking**
**Solution:**
- Add XAMPP folder to antivirus exclusions
- Allow MySQL through Windows Firewall

### 4. **Insufficient Permissions**
**Solution:**
- Run XAMPP Control Panel as Administrator
- Check folder permissions for `C:\xampp\mysql\data`

### 5. **Check MySQL Error Logs**
**Location:** `C:\xampp\mysql\data\*.err` or check XAMPP Control Panel → MySQL → Logs

## Quick Fix Steps:

1. **Stop all MySQL instances:**
   ```powershell
   # Stop XAMPP MySQL
   # Then check for other MySQL services
   Get-Service | Where-Object {$_.Name -like "*mysql*"}
   ```

2. **Check XAMPP MySQL Logs:**
   - Open XAMPP Control Panel
   - Click "Logs" button next to MySQL
   - Look for specific error messages

3. **Try starting MySQL manually:**
   - Open Command Prompt as Administrator
   - Navigate to: `C:\xampp\mysql\bin`
   - Run: `mysqld.exe --console`
   - This will show detailed error messages

4. **If port is blocked, change it:**
   - Edit `C:\xampp\mysql\bin\my.ini`
   - Find `[mysqld]` section
   - Change `port=3306` to `port=3307`
   - Restart MySQL

## After Fixing MySQL:

Once MySQL is running:
1. Access `http://localhost/aliza/university/error_check.php` to verify connection
2. If database doesn't exist, run `install.php` or `setup.php`
3. Your application should work normally


