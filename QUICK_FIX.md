# Quick Fix for "Failed to fetch" Error

## The Problem

"Failed to fetch" means your browser **cannot reach the server at all**. This is different from a server error.

## Most Common Cause ⚠️

**You're opening the HTML file directly instead of through the web server!**

### ❌ WRONG WAY:
- Double-clicking `auth.html` in File Explorer
- Opening from VS Code with "Open with Live Server"
- URL looks like: `file:///C:/xampp/htdocs/fastfood/auth.html`
- URL looks like: `http://127.0.0.1:5500/auth.html` (Live Server)

### ✅ CORRECT WAY:
1. Make sure XAMPP Apache is running (green in XAMPP Control Panel)
2. Open your browser
3. Type this URL: `http://localhost/fastfood/auth.html`
4. Press Enter

## Quick Test

Open this diagnostic page in your browser:
```
http://localhost/fastfood/diagnose.html
```

It will tell you exactly what's wrong!

## Step-by-Step Fix

### 1. Start XAMPP
- Open XAMPP Control Panel
- Click "Start" next to Apache (should turn green)
- Click "Start" next to MySQL (should turn green)

### 2. Access via localhost
- Open browser
- Type: `http://localhost/fastfood/auth.html`
- Do NOT double-click the file!

### 3. Test Signup
- Fill in the signup form
- Click "SIGN UP"
- Should work now!

## Why This Happens

When you open an HTML file directly:
- Browser uses `file://` protocol
- JavaScript fetch() cannot make HTTP requests
- Security restrictions block cross-origin requests
- Result: "Failed to fetch" error

When you access via localhost:
- Browser uses `http://` protocol
- Apache web server serves the files
- PHP scripts can execute
- Fetch requests work normally

## Alternative: Use Browser Console

If you're not sure how you're accessing the page:

1. Press `F12` to open Developer Tools
2. Go to "Console" tab
3. Type: `window.location.href`
4. Press Enter

**If it shows `file:///`** → You're doing it wrong!  
**If it shows `http://localhost`** → You're doing it right!

## Still Not Working?

### Check Apache is Running:
```powershell
netstat -ano | findstr :80
```
Should show Apache listening on port 80.

### Check if file exists:
```powershell
Test-Path C:\xampp\htdocs\fastfood\submit_signup.php
```
Should return `True`.

### Test direct PHP access:
Open browser and go to:
```
http://localhost/fastfood/test_connection.php
```
Should show green checkmarks.

## Browser Extensions

Some browser extensions can block requests:
- Ad blockers
- Privacy extensions
- CORS blockers

Try in **Incognito/Private mode** to test without extensions.

## Firewall/Antivirus

Sometimes firewall blocks localhost:
- Add exception for Apache
- Temporarily disable to test
- Check Windows Defender settings
