# Consistency Check Script for WP booking Luca Plugin
# Checks for naming inconsistencies and code quality issues

Write-Host "=== WP booking Luca - Consistency Check ===" -ForegroundColor Cyan
Write-Host ""

$baseDir = Get-Location
$issues = @()
$warnings = @()

# Check 1: Constants naming
Write-Host "Checking constants..." -ForegroundColor Yellow
$files = Get-ChildItem -Path $baseDir -Include *.php -Recurse -File | Where-Object { 
    $_.FullName -notlike "*\build\*" -and 
    $_.FullName -notlike "*\node_modules\*" -and 
    $_.Name -ne "consistency-check.php" -and
    $_.Name -notlike "*template*"
}
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($null -eq $content) { continue }
    if ($content -match 'WP_BOOKING_SYSTEM_LUCA_LUCA') {
        $issues += "Duplicate LUCA in constants: $($file.Name)"
    }
    if ($content -match 'WP_BOOKING_SYSTEM_VERSION[^_]|WP_BOOKING_SYSTEM_PLUGIN[^_]') {
        if ($content -notmatch 'WP_BOOKING_SYSTEM_LUCA') {
            $issues += "Old constant name (should have LUCA): $($file.Name)"
        }
    }
}

# Check 2: Class names
Write-Host "Checking class names..." -ForegroundColor Yellow
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($null -eq $content) { continue }
    if ($content -match 'class WP_Booking_System[^_]') {
        if ($file.Name -notlike "*luca*") {
            $issues += "Old class name (should be WP_Booking_System_Luca*): $($file.Name)"
        }
    }
    if ($content -match 'WP_Booking_System_Luca_Luca[^_]') {
        $issues += "Duplicate Luca in class name: $($file.Name)"
    }
}

# Check 3: Function names
Write-Host "Checking function names..." -ForegroundColor Yellow
foreach ($file in $files) {
    # Skip template files
    if ($file.Name -like "*template*") { continue }
    
    $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
    if ($null -eq $content) { continue }
    if ($content -match 'function wp_booking_system\(\)') {
        $issues += "Old function name (should be wp_booking_system_luca): $($file.Name)"
    }
    # More specific check: function name starting with capital letter AND containing _luca
    if ($content -match 'function [A-Z][a-zA-Z_]*_luca\(\)') {
        # Skip if it's actually a class method call or something else
        if ($content -match 'function [A-Z][a-zA-Z_]*_luca\(\)') {
            # Check if it's really a function definition (not a call)
            $matches = [regex]::Matches($content, 'function\s+([A-Z][a-zA-Z_]*_luca)\(\)')
            foreach ($match in $matches) {
                $funcName = $match.Groups[1].Value
                # Only flag if it's not a valid WordPress pattern
                if ($funcName -notmatch '^[a-z]') {
                    $issues += "Function name starts with capital (should be lowercase): $($file.Name) - $funcName"
                }
            }
        }
    }
}

# Check 4: File structure
Write-Host "Checking file structure..." -ForegroundColor Yellow
$requiredFiles = @(
    "wp-booking-system.php",
    "includes\class-wp-booking-system-luca.php"
)
foreach ($file in $requiredFiles) {
    $fullPath = Join-Path $baseDir $file
    if (-not (Test-Path $fullPath)) {
        $issues += "Missing required file: $file"
    }
}

# Summary
Write-Host ""
Write-Host "=== SUMMARY ===" -ForegroundColor Cyan
Write-Host ""

if ($issues.Count -eq 0) {
    Write-Host "All checks passed! No issues found." -ForegroundColor Green
    exit 0
}

if ($issues.Count -gt 0) {
    Write-Host "CRITICAL ISSUES FOUND: $($issues.Count)" -ForegroundColor Red
    foreach ($issue in $issues) {
        Write-Host "  - $issue" -ForegroundColor Red
    }
    Write-Host ""
}

Write-Host "Total issues: $($issues.Count)" -ForegroundColor Red
exit 1
