# Renaming to wp_booking_system_luca - IN PROGRESS

## Status: Major Refactoring Started

This is a comprehensive renaming of all internal identifiers to include "luca" for uniqueness.

## Completed So Far
1. ✅ Created main class file: `includes/class-wp-booking-system-luca.php`
2. ✅ Updated main plugin file: `wp-booking-system.php` with new constants and function names

## Still To Do (Extensive)

### Critical Files to Update:
1. All class files in `includes/` - need to be renamed/recreated
2. All references to `wp_booking_system()` function
3. All option names (wpbs_* → wpbsl_*)
4. All AJAX actions
5. All nonces
6. All JavaScript variable names
7. Database table name
8. Text domain in all __() and esc_html__() calls

### Files Requiring Updates:
- includes/class-wp-booking-system-database.php → class-wp-booking-system-luca-database.php
- includes/class-wp-booking-system-admin.php → class-wp-booking-system-luca-admin.php
- includes/class-wp-booking-system-frontend.php → class-wp-booking-system-luca-frontend.php
- includes/class-wp-booking-system-ajax.php → class-wp-booking-system-luca-ajax.php
- includes/class-wp-booking-system-email.php → class-wp-booking-system-luca-email.php
- includes/class-wp-booking-system-widget.php → class-wp-booking-system-luca-widget.php
- includes/class-wp-booking-system-block.php → class-wp-booking-system-luca-block.php
- assets/js/admin.js
- assets/js/frontend.js
- assets/js/block.js
- uninstall.php

### Database Migration Needed:
- Options migration script needed
- Table name change requires migration

## Note
This refactoring will take many file edits. Working systematically through each file.




