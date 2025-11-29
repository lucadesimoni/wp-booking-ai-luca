# Fix remaining issues from automated refactoring

$ErrorActionPreference = "Stop"

Write-Host "Fixing remaining issues..." -ForegroundColor Cyan

# Get all PHP files
$phpFiles = Get-ChildItem -Path . -Include *.php,*.js -Recurse -Exclude build,node_modules | Where-Object { $_.FullName -notlike "*\build\*" }

$fixes = @{
    # Fix doubled class name in function calls
    'WP_Booking_System_Luca_luca\(\)' = 'wp_booking_system_luca()'
    'wp_booking_system_luca_luca\(\)' = 'wp_booking_system_luca()'
    
    # Fix nonces
    "'wp-booking-system-admin'" = "'wp-booking-system-luca-admin'"
    '"wp-booking-system-admin"' = '"wp-booking-system-luca-admin"'
    "'wp-booking-system-frontend'" = "'wp-booking-system-luca-frontend'"
    '"wp-booking-system-frontend"' = '"wp-booking-system-luca-frontend"'
    
    # Fix script handles
    "'wp-booking-system-admin'" = "'wp-booking-system-luca-admin'"
    "'wp-booking-system-frontend'" = "'wp-booking-system-luca-frontend'"
    
    # Fix AJAX action strings in JS
    "action: 'wpbsl_" = "action: 'wpbsl_"
    'action: "wpbsl_' = 'action: "wpbsl_'
}

$totalFixes = 0

foreach ($file in $phpFiles) {
    $content = Get-Content -Path $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    
    foreach ($pattern in $fixes.Keys) {
        $replacement = $fixes[$pattern]
        if ($content -match $pattern) {
            $content = $content -replace $pattern, $replacement
            $totalFixes++
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        Write-Host "  Fixed: $($file.Name)" -ForegroundColor Gray
    }
}

Write-Host "`nFixed $totalFixes issues" -ForegroundColor Green

