# Comprehensive Fix Script - Fixes all naming inconsistencies
Write-Host "=== Comprehensive Code Quality Fix ===" -ForegroundColor Cyan
Write-Host ""

$baseDir = Get-Location
$files = Get-ChildItem -Path $baseDir -Include *.php -Recurse -File | Where-Object { 
    $_.FullName -notlike "*\build\*" -and 
    $_.FullName -notlike "*\node_modules\*" -and
    $_.FullName -notlike "*\check-consistency.ps1" -and
    $_.FullName -notlike "*\consistency-check.php" -and
    $_.FullName -notlike "*\fix-all-naming.ps1" -and
    $_.FullName -notlike "*\comprehensive-fix.ps1"
}

$fixedCount = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
    if ($null -eq $content) { continue }
    
    $original = $content
    
    # Fix 1: Class names - should be WP_Booking_System_Luca_* (Title Case)
    $content = $content -replace 'class WP_BOOKING_SYSTEM_LUCA_', 'class WP_Booking_System_Luca_'
    $content = $content -replace '\bWP_BOOKING_SYSTEM_LUCA_Admin\b', 'WP_Booking_System_Luca_Admin'
    $content = $content -replace '\bWP_BOOKING_SYSTEM_LUCA_Ajax\b', 'WP_Booking_System_Luca_Ajax'
    $content = $content -replace '\bWP_BOOKING_SYSTEM_LUCA_Database\b', 'WP_Booking_System_Luca_Database'
    $content = $content -replace '\bWP_BOOKING_SYSTEM_LUCA_Email\b', 'WP_Booking_System_Luca_Email'
    $content = $content -replace '\bWP_BOOKING_SYSTEM_LUCA_Block\b', 'WP_Booking_System_Luca_Block'
    
    # Fix 2: Constants - should be ALL_CAPS
    $content = $content -replace 'WP_Booking_System_Luca_PLUGIN_URL', 'WP_BOOKING_SYSTEM_LUCA_PLUGIN_URL'
    $content = $content -replace 'WP_Booking_System_Luca_PLUGIN_DIR', 'WP_BOOKING_SYSTEM_LUCA_PLUGIN_DIR'
    
    # Fix 3: Package names in docblocks
    $content = $content -replace '@package WP_Booking_System_Luca_Luca', '@package WP_Booking_System_Luca'
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $fixedCount++
        Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Fixed $fixedCount files" -ForegroundColor Green
Write-Host ""


