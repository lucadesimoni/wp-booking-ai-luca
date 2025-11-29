# WP Booking System - Plugin Build Script
# Creates a distributable ZIP file for WordPress upload

$ErrorActionPreference = "Stop"

# Configuration
$PluginName = "wp-booking-system"
$Version = "1.0.0"
$BuildDir = "build"
$ZipFile = "$PluginName-v$Version.zip"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "WP Booking System - Build Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Clean previous build
if (Test-Path $BuildDir) {
    Write-Host "Cleaning previous build..." -ForegroundColor Yellow
    Remove-Item -Recurse -Force $BuildDir
}

if (Test-Path $ZipFile) {
    Write-Host "Removing old ZIP file..." -ForegroundColor Yellow
    Remove-Item -Force $ZipFile
}

# Create build directory
Write-Host "Creating build directory..." -ForegroundColor Green
New-Item -ItemType Directory -Path $BuildDir | Out-Null
New-Item -ItemType Directory -Path "$BuildDir\$PluginName" | Out-Null

# Files and directories to include
$IncludeFiles = @(
    "wp-booking-system.php",
    "index.php",
    "uninstall.php",
    "LICENSE",
    "readme.txt"
)

$IncludeDirs = @(
    "includes",
    "assets"
)

# Copy main plugin files
Write-Host "Copying plugin files..." -ForegroundColor Green
foreach ($file in $IncludeFiles) {
    if (Test-Path $file) {
        Copy-Item $file "$BuildDir\$PluginName\" -Force
        Write-Host "  [OK] $file" -ForegroundColor Gray
    } else {
        Write-Host "  [MISSING] $file" -ForegroundColor Red
    }
}

# Copy directories
foreach ($dir in $IncludeDirs) {
    if (Test-Path $dir) {
        Copy-Item -Recurse $dir "$BuildDir\$PluginName\" -Force
        Write-Host "  [OK] $dir/" -ForegroundColor Gray
    } else {
        Write-Host "  [MISSING] $dir/" -ForegroundColor Red
    }
}

# Create lang directory structure (for future translations)
Write-Host "Creating language directory..." -ForegroundColor Green
New-Item -ItemType Directory -Path "$BuildDir\$PluginName\lang" -Force | Out-Null
Copy-Item "index.php" "$BuildDir\$PluginName\lang\" -Force

# Verify essential files
Write-Host ""
Write-Host "Verifying essential files..." -ForegroundColor Green
$EssentialFiles = @(
    "$BuildDir\$PluginName\wp-booking-system.php",
    "$BuildDir\$PluginName\includes\class-wp-booking-system-luca.php",
    "$BuildDir\$PluginName\assets\css\frontend.css",
    "$BuildDir\$PluginName\assets\js\frontend.js"
)

$AllPresent = $true
foreach ($file in $EssentialFiles) {
    if (Test-Path $file) {
        Write-Host "  [OK] $(Split-Path $file -Leaf)" -ForegroundColor Green
    } else {
        Write-Host "  [MISSING] $(Split-Path $file -Leaf)" -ForegroundColor Red
        $AllPresent = $false
    }
}

if (-not $AllPresent) {
    Write-Host ""
    Write-Host "ERROR: Essential files are missing!" -ForegroundColor Red
    exit 1
}

# Create ZIP file
Write-Host ""
Write-Host "Creating ZIP archive..." -ForegroundColor Green
try {
    # Compress-Archive requires PowerShell 5.0+
    $SourcePath = "$BuildDir\$PluginName\*"
    Compress-Archive -Path $SourcePath -DestinationPath $ZipFile -Force
    Write-Host "  [OK] ZIP file created: $ZipFile" -ForegroundColor Green
} catch {
    Write-Host "  [ERROR] Failed to create ZIP: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Alternative: Manual ZIP creation" -ForegroundColor Yellow
    Write-Host "  1. Navigate to: $BuildDir\$PluginName" -ForegroundColor Yellow
    Write-Host "  2. Select all files and folders" -ForegroundColor Yellow
    Write-Host "  3. Right-click → Send to → Compressed (zipped) folder" -ForegroundColor Yellow
    Write-Host "  4. Rename to: $ZipFile" -ForegroundColor Yellow
    exit 1
}

# Get file size
$ZipSize = (Get-Item $ZipFile).Length / 1MB
    Write-Host "  [OK] File size: $([math]::Round($ZipSize, 2)) MB" -ForegroundColor Gray

# Cleanup build directory (optional)
Write-Host ""
$Cleanup = Read-Host "Remove build directory? (y/n)"
if ($Cleanup -eq "y" -or $Cleanup -eq "Y") {
    Remove-Item -Recurse -Force $BuildDir
    Write-Host "  [OK] Build directory removed" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Build Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Plugin ZIP: $ZipFile" -ForegroundColor Yellow
Write-Host ""
Write-Host "To install:" -ForegroundColor Cyan
Write-Host "  1. Go to WordPress Admin → Plugins → Add New" -ForegroundColor White
Write-Host "  2. Click 'Upload Plugin'" -ForegroundColor White
Write-Host "  3. Choose file: $ZipFile" -ForegroundColor White
Write-Host "  4. Click 'Install Now'" -ForegroundColor White
Write-Host "  5. Activate the plugin" -ForegroundColor White
Write-Host ""

