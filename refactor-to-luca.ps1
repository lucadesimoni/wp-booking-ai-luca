# Comprehensive Refactoring Script for wp_booking_system_luca
# This script will rename all internal identifiers

$ErrorActionPreference = "Stop"

Write-Host "Starting comprehensive refactoring to wp_booking_system_luca..." -ForegroundColor Cyan

# Get all PHP files to process (excluding build folder)
$phpFiles = Get-ChildItem -Path . -Include *.php -Recurse -Exclude build,node_modules | Where-Object { $_.FullName -notlike "*\build\*" }

Write-Host "Found $($phpFiles.Count) PHP files to process" -ForegroundColor Green

# Get all JS files
$jsFiles = Get-ChildItem -Path assets\js -Include *.js -Recurse

Write-Host "Found $($jsFiles.Count) JS files to process" -ForegroundColor Green

$totalReplacements = 0

# Function to perform replacements in a file
function Update-FileContent {
    param(
        [string]$FilePath,
        [hashtable]$Replacements
    )
    
    if (-not (Test-Path $FilePath)) {
        return 0
    }
    
    $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
    $originalContent = $content
    $fileReplacements = 0
    
    foreach ($old in $Replacements.Keys) {
        $new = $Replacements[$old]
        if ($content -match [regex]::Escape($old)) {
            $content = $content -replace [regex]::Escape($old), $new
            $fileReplacements++
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content -Path $FilePath -Value $content -Encoding UTF8 -NoNewline
        Write-Host "  Updated: $FilePath ($fileReplacements replacements)" -ForegroundColor Gray
    }
    
    return $fileReplacements
}

# Define all replacements
$replacements = @{
    # Class names
    'WP_Booking_System' = 'WP_Booking_System_Luca'
    'WP_Booking_System_Database' = 'WP_Booking_System_Luca_Database'
    'WP_Booking_System_Admin' = 'WP_Booking_System_Luca_Admin'
    'WP_Booking_System_Frontend' = 'WP_Booking_System_Luca_Frontend'
    'WP_Booking_System_Ajax' = 'WP_Booking_System_Luca_Ajax'
    'WP_Booking_System_Email' = 'WP_Booking_System_Luca_Email'
    'WP_Booking_System_Widget' = 'WP_Booking_System_Luca_Widget'
    'WP_Booking_System_Block' = 'WP_Booking_System_Luca_Block'
    
    # Function
    'wp_booking_system()' = 'wp_booking_system_luca()'
    
    # Constants
    'WP_BOOKING_SYSTEM_VERSION' = 'WP_BOOKING_SYSTEM_LUCA_VERSION'
    'WP_BOOKING_SYSTEM_PLUGIN_DIR' = 'WP_BOOKING_SYSTEM_LUCA_PLUGIN_DIR'
    'WP_BOOKING_SYSTEM_PLUGIN_URL' = 'WP_BOOKING_SYSTEM_LUCA_PLUGIN_URL'
    
    # Options - must be done carefully
    "'wpbs_" = "'wpbsl_"
    '"wpbs_' = '"wpbsl_'
    'wpbs_' = 'wpbsl_'  # For get_option, update_option without quotes
    
    # Text domain
    "'wp-booking-system'" = "'wp-booking-system-luca'"
    '"wp-booking-system"' = '"wp-booking-system-luca"'
    
    # Package names in docblocks
    '@package WP_Booking_System' = '@package WP_Booking_System_Luca'
}

# Process PHP files
Write-Host "`nProcessing PHP files..." -ForegroundColor Yellow
foreach ($file in $phpFiles) {
    $replacements_in_file = Update-FileContent -FilePath $file.FullName -Replacements $replacements
    $totalReplacements += $replacements_in_file
}

# Process JS files with JS-specific replacements
Write-Host "`nProcessing JS files..." -ForegroundColor Yellow
$jsReplacements = @{
    'wpbsAdmin' = 'wpbslAdmin'
    'wpbsFrontend' = 'wpbslFrontend'
    "'wp-booking-system'" = "'wp-booking-system-luca'"
    '"wp-booking-system"' = '"wp-booking-system-luca"'
    'wpbs_' = 'wpbsl_'  # For AJAX actions
}

foreach ($file in $jsFiles) {
    $replacements_in_file = Update-FileContent -FilePath $file.FullName -Replacements $jsReplacements
    $totalReplacements += $replacements_in_file
}

Write-Host "`nRefactoring complete! Total replacements: $totalReplacements" -ForegroundColor Green
Write-Host "`nNote: Please review the changes and test thoroughly." -ForegroundColor Yellow

