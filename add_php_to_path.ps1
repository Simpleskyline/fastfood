# PowerShell Script to Add PHP to System PATH
# Run as Administrator

Write-Host "=== Adding PHP to System PATH ===" -ForegroundColor Cyan
Write-Host ""

$phpPath = "C:\xampp\php"

# Check if PHP directory exists
if (-Not (Test-Path $phpPath)) {
    Write-Host "ERROR: PHP directory not found at $phpPath" -ForegroundColor Red
    Write-Host "Please verify your XAMPP installation path." -ForegroundColor Yellow
    exit 1
}

# Get current PATH
$currentPath = [Environment]::GetEnvironmentVariable("Path", "Machine")

# Check if PHP is already in PATH
if ($currentPath -like "*$phpPath*") {
    Write-Host "✓ PHP is already in your system PATH!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Testing PHP..." -ForegroundColor Cyan
    & php --version
} else {
    Write-Host "Adding PHP to system PATH..." -ForegroundColor Yellow
    
    # Add PHP to PATH
    $newPath = $currentPath + ";" + $phpPath
    
    try {
        [Environment]::SetEnvironmentVariable("Path", $newPath, "Machine")
        Write-Host "✓ PHP successfully added to PATH!" -ForegroundColor Green
        Write-Host ""
        Write-Host "IMPORTANT: Please restart your terminal/PowerShell for changes to take effect." -ForegroundColor Yellow
        Write-Host ""
        Write-Host "After restarting, test with: php --version" -ForegroundColor Cyan
    } catch {
        Write-Host "ERROR: Failed to update PATH. Make sure you run this script as Administrator." -ForegroundColor Red
        Write-Host "Right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
