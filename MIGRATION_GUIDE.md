# Migration Guide: XAMPP to MySQL Workbench

## Current Setup Status

✅ **PHP Installed**: PHP 8.2.12 (via XAMPP at `C:\xampp\php\php.exe`)  
✅ **MySQL**: Available via XAMPP  
📋 **Next Step**: Connect MySQL Workbench to XAMPP's MySQL

---

## Option 1: Use MySQL Workbench with XAMPP MySQL (Recommended)

You don't need to start from scratch! You can use MySQL Workbench as a GUI tool while keeping XAMPP's MySQL server.

### Steps:

1. **Keep XAMPP MySQL Running**
   - Open XAMPP Control Panel
   - Make sure MySQL is started (green highlight)
   - Note the port (default: 3306)

2. **Install MySQL Workbench** (if not installed)
   - Download from: https://dev.mysql.com/downloads/workbench/
   - Install with default settings

3. **Connect MySQL Workbench to XAMPP**
   - Open MySQL Workbench
   - Click "+" next to "MySQL Connections"
   - Configure connection:
     ```
     Connection Name: XAMPP Local
     Hostname: 127.0.0.1 or localhost
     Port: 3306
     Username: root
     Password: (leave empty unless you set one)
     ```
   - Click "Test Connection"
   - Click "OK" to save

4. **Run Your Database Setup**
   - Double-click your new connection
   - Open the SQL file: `File > Open SQL Script`
   - Navigate to: `C:\xampp\htdocs\fastfood\setup_database.sql`
   - Click the lightning bolt icon ⚡ to execute
   - Your database and tables will be created!

5. **Your PHP Code Stays the Same**
   - No code changes needed
   - PHP still connects to `localhost:3306`
   - MySQL Workbench is just a better GUI than phpMyAdmin

---

## Option 2: Fresh Start with Standalone MySQL

If you want to completely replace XAMPP's MySQL with a standalone installation:

### Steps:

1. **Stop XAMPP MySQL**
   - Open XAMPP Control Panel
   - Stop MySQL service
   - (Optional) Uninstall MySQL from XAMPP

2. **Install MySQL Server**
   - Download MySQL Installer: https://dev.mysql.com/downloads/installer/
   - Choose "Developer Default" or "Server only"
   - During installation:
     - Set root password (remember this!)
     - Port: 3306 (default)
     - Start MySQL as Windows Service

3. **Install MySQL Workbench** (included in MySQL Installer)

4. **Update PHP Configuration**
   - Your PHP code needs to use the new password
   - Update all database connection files:
     ```php
     // Old (XAMPP)
     $password = "";
     
     // New (Standalone MySQL)
     $password = "your_root_password";
     ```

5. **Update Files to Change**:
   - `db.php`
   - `submit_signup.php`
   - `login.php`
   - `save_order.php`
   - `save_payment.php`
   - Any other files with database connections

---

## Option 3: Add PHP to System PATH (Optional but Useful)

This allows you to run `php` command from anywhere in terminal.

### Steps:

1. **Open System Environment Variables**
   - Press `Win + X` → System
   - Click "Advanced system settings"
   - Click "Environment Variables"

2. **Edit PATH Variable**
   - Under "System variables", find "Path"
   - Click "Edit"
   - Click "New"
   - Add: `C:\xampp\php`
   - Click "OK" on all dialogs

3. **Restart Terminal**
   - Close and reopen PowerShell/Command Prompt
   - Test: `php --version`
   - Should now work from any directory!

---

## Recommended Approach for You

**I recommend Option 1** because:

✅ No code changes needed  
✅ Keep existing XAMPP setup  
✅ Get better database management with MySQL Workbench  
✅ Can still use phpMyAdmin if needed  
✅ Faster to set up (5 minutes vs 30+ minutes)  

You can always migrate to standalone MySQL later if needed.

---

## Quick Setup Commands

### To run the database setup via PHP:
```powershell
C:\xampp\php\php.exe -f C:\xampp\htdocs\fastfood\setup_database.php
```

### Or via browser:
```
http://localhost/fastfood/setup_database.php
```

### To test PHP from command line:
```powershell
C:\xampp\php\php.exe -r "echo 'PHP is working!';"
```

---

## Current Database Connection Settings

All your PHP files should use:
```php
$servername = "localhost";  // or "127.0.0.1"
$username = "root";
$password = "";             // empty for XAMPP default
$dbname = "fastfood";
```

---

## Need Help?

- **XAMPP MySQL not starting?** Check if port 3306 is already in use
- **Connection refused?** Make sure MySQL service is running in XAMPP
- **Access denied?** Verify username is "root" and password is empty (or your custom password)
