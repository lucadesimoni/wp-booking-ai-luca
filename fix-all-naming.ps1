# Comprehensive Fix Script for WP booking Luca
# Fixes all naming inconsistencies

$baseDir = Get-Location

Write-Host "=== Fixing All Naming Issues ===" -ForegroundColor Cyan
Write-Host ""

# Fix 1: Replace wrong function calls (capital W)
Write-Host "Fixing function calls..." -ForegroundColor Yellow
$phpFiles = Get-ChildItem -Path $baseDir -Include *.php -Recurse -File | Where-Object { 
    $_.FullName -notlike "*\build\*" -and 
    $_.FullName -notlike "*\node_modules\*" -and
    $_.Name -ne "check-consistency.ps1" -and
    $_.Name -ne "consistency-check.php" -and
    $_.Name -ne "fix-all-naming.ps1"
}

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    if ($null -eq $content) { continue }
    
    $original = $content
    
    # Fix function call casing
    $content = $content -replace 'WP_Booking_System_Luca_luca\(\)', 'wp_booking_system_luca()'
    $content = $content -replace 'WP_BOOKING_SYSTEM_LUCA_LUCA_', 'WP_BOOKING_SYSTEM_LUCA_'
    $content = $content -replace 'WP_Booking_System_Luca_Luca', 'WP_Booking_System_Luca'
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "Done!" -ForegroundColor Green
Write-Host ""




