# Troubleshooting Guide

## "Connection error. Please check if XAMPP is running" Error

### Quick Diagnosis Steps

1. **Open this test page in your browser:**
   ```
   http://localhost/fastfood/test_fetch.html
   ```
   - Click "Test Signup" button
   - Check if it works
   - Open browser console (F12) to see detailed logs

2. **Check XAMPP Services:**
   - Open XAMPP Control Panel
   - Verify Apache is running (green)
   - Verify MySQL is running (green)
   - If not, click "Start" for each

3. **Verify Database Connection:**
   ```
   http://localhost/fastfood/test_connection.php
   ```
   - Should show all green checkmarks
   - If not, run setup: http://localhost/fastfood/setup_database.php

4. **Check Browser Console:**
   - Open auth.html: http://localhost/fastfood/auth.html
   - Press F12 to open Developer Tools
   - Go to "Console" tab
   - Try to sign up
   - Look for error messages in red

### Common Issues & Solutions

#### Issue 1: Fetch fails immediately
**Symptom:** Error appears instantly without trying to connect

**Cause:** Browser blocking the request or wrong URL

**Solution:**
- Make sure you're accessing via `http://localhost/fastfood/auth.html`
- NOT via `file:///` or `C:\xampp\...`
- Check browser console for CORS errors

#### Issue 2: JSON parse error
**Symptom:** "Invalid response from server" or JSON parse error

**Cause:** PHP is outputting errors/warnings before JSON

**Solution:**
- Already fixed in submit_signup.php
- Check PHP error logs: `C:\xampp\php\logs\php_error_log`

#### Issue 3: 404 Not Found
**Symptom:** "Server returned 404"

**Cause:** File path is wrong

**Solution:**
- Verify file exists: `C:\xampp\htdocs\fastfood\submit_signup.php`
- Access auth.html via: `http://localhost/fastfood/auth.html`

#### Issue 4: Database connection failed
**Symptom:** "Database connection failed" message

**Solution:**
1. Start MySQL in XAMPP
2. Run: http://localhost/fastfood/setup_database.php
3. Check credentials in submit_signup.php (should be root with empty password)

#### Issue 5: Port 80 already in use
**Symptom:** Apache won't start in XAMPP

**Solution:**
1. Check what's using port 80:
   ```powershell
   netstat -ano | findstr :80
   ```
2. Stop the conflicting service (often Skype, IIS, or another web server)
3. Or change Apache port in XAMPP config

### Debug Mode

To see detailed error information:

1. **Edit submit_signup.php line 4:**
   ```php
   ini_set('display_errors', 1);  // Change 0 to 1
   ```

2. **Try signup again**

3. **Check response in browser console**

4. **Remember to change back to 0 after debugging!**

### Still Not Working?

1. **Check Apache error log:**
   - `C:\xampp\apache\logs\error.log`

2. **Check PHP error log:**
   - `C:\xampp\php\logs\php_error_log`

3. **Test direct PHP execution:**
   ```powershell
   C:\xampp\php\php.exe C:\xampp\htdocs\fastfood\test_signup.php
   ```

4. **Verify file permissions:**
   - Make sure XAMPP can read/write to the fastfood folder

### Browser Console Commands

Open browser console (F12) and run:

```javascript
// Test if fetch is available
console.log('Fetch available:', typeof fetch !== 'undefined');

// Test basic connectivity
fetch('submit_signup.php')
  .then(r => r.text())
  .then(t => console.log('Response:', t))
  .catch(e => console.error('Error:', e));
```

### Contact Information

If you're still having issues:
1. Check the browser console output
2. Check the test_fetch.html results
3. Share the exact error message from console
