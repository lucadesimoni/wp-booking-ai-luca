# Final comprehensive fix for all naming inconsistencies
Write-Host "=== Final Comprehensive Fix ===" -ForegroundColor Cyan
Write-Host ""

$baseDir = Get-Location
$phpFiles = Get-ChildItem -Path $baseDir -Include *.php -Recurse -File | Where-Object { 
    $_.FullName -notlike "*\build\*" -and 
    $_.FullName -notlike "*\node_modules\*" -and
    $_.FullName -notlike "*\*.ps1" -and
    $_.FullName -notlike "*\consistency-check.php"
}

$jsFiles = Get-ChildItem -Path $baseDir -Include *.js -Recurse -File | Where-Object { 
    $_.FullName -notlike "*\build\*" -and 
    $_.FullName -notlike "*\node_modules\*"
}

$fixedCount = 0

# Fix PHP files
Write-Host "Fixing PHP files..." -ForegroundColor Yellow
foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
    if ($null -eq $content) { continue }
    
    $original = $content
    
    # Fix shortcodes
    $content = $content -replace "add_shortcode\( 'wp_booking_form'", "add_shortcode( 'wp_booking_form_luca'"
    $content = $content -replace "add_shortcode\( 'wp_booking_manage'", "add_shortcode( 'wp_booking_manage_luca'"
    $content = $content -replace "add_shortcode\( 'wp_booking_calendar'", "add_shortcode( 'wp_booking_calendar_luca'"
    $content = $content -replace "'wp_booking_form'", "'wp_booking_form_luca'"
    $content = $content -replace "'wp_booking_calendar'", "'wp_booking_calendar_luca'"
    
    # Fix nonces
    $content = $content -replace "'wp-booking-system-frontend'", "'wp-booking-system-luca-frontend'"
    $content = $content -replace "'wp-booking-system-admin'", "'wp-booking-system-luca-admin'"
    $content = $content -replace "check_ajax_referer\( 'wp-booking-system-frontend'", "check_ajax_referer( 'wp-booking-system-luca-frontend'"
    $content = $content -replace "check_ajax_referer\( 'wp-booking-system-admin'", "check_ajax_referer( 'wp-booking-system-luca-admin'"
    
    # Fix JS variable names
    $content = $content -replace "'wpbsFrontend'", "'wpbslFrontend'"
    $content = $content -replace "'wpbsAdmin'", "'wpbslAdmin'"
    
    # Fix script handles
    $content = $content -replace "'wp-booking-system-frontend'", "'wp-booking-system-luca-frontend'"
    $content = $content -replace "'wp-booking-system-admin'", "'wp-booking-system-luca-admin'"
    $content = $content -replace "'wp-booking-system-block'", "'wp-booking-system-luca-block'"
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $fixedCount++
        Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
    }
}

# Fix JS files
Write-Host ""
Write-Host "Fixing JavaScript files..." -ForegroundColor Yellow
foreach ($file in $jsFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
    if ($null -eq $content) { continue }
    
    $original = $content
    
    # Fix JS variable names
    $content = $content -replace '\bwpbsFrontend\b', 'wpbslFrontend'
    $content = $content -replace '\bwpbsAdmin\b', 'wpbslAdmin'
    
    # Fix AJAX action names (if still using old ones)
    $content = $content -replace "action: 'wpbs_", "action: 'wpbsl_"
    $content = $content -replace "&action=wpbs_", "&action=wpbsl_"
    
    if ($content -ne $original) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        $fixedCount++
        Write-Host "  Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Fixed $fixedCount files total" -ForegroundColor Green
Write-Host ""

