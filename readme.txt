=== WP booking Luca ===
Contributors: famiglia-desimoni
Tags: booking, calendar, reservation, booking-system
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and modern booking system for WordPress with calendar management, email notifications, and price calculations.

== Description ==

WP booking Luca is a clean and modern booking solution for WordPress. It provides a simple interface for managing bookings with the following features:

* Admin calendar view for managing all bookings
* Frontend booking form with date selection
* Price calculation based on number of adults and kids
* Email notifications for booking confirmations and cancellations
* Unique links for guests to manage or cancel their bookings
* Modern, responsive design that fits seamlessly into your website

Perfect for vacation rentals, hotels, or any accommodation booking needs.

== Installation ==

1. Upload the plugin ZIP through Plugins > Add New > Upload Plugin, or extract it to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress. On activation it automatically creates a "Book Now" page and a "Manage Booking" page, so everything works immediately.
3. Go to WP booking Luca > Settings to configure pricing, email settings, and chalet capacity
4. To embed elsewhere, use the shortcode `[wp_booking_form_luca]` (form), `[wp_booking_calendar_luca]` (availability calendar) or `[wp_booking_manage_luca]` (management page), or add the "Booking Form" / "Booking Calendar" blocks when editing a page
5. Guests receive a confirmation email containing a unique magic link to the "Manage Booking" page where they can view or cancel their reservation

== Screenshots ==

1. Admin calendar view for managing bookings
2. Frontend booking form with modern design
3. Booking management page for guests

== Frequently Asked Questions ==

= How do I display the booking form? =

Activation creates a "Book Now" page for you. To place the form elsewhere, use the shortcode `[wp_booking_form_luca]` or the "Booking Form" block on any page or post.

= How do guests manage their bookings? =

Guests receive an email with a unique magic link to the auto-created "Manage Booking" page (which uses the `[wp_booking_manage_luca]` shortcode), where they can view or cancel their booking.

= How do I set pricing? =

Go to Bookings > Settings in your WordPress admin and configure the price per adult and price per kid. Prices are calculated per night.

= Can I customize the email templates? =

Currently, email templates are built into the plugin. Future versions may include customizable templates.

== Changelog ==

= 1.1.0 =
* 2026-06-11
* Fixed activation fatal errors and a BOM issue that broke page headers
* Magic-link booking management now works out of the box (auto-created pages)
* Added a Booking Form block and admin Confirm/Cancel actions
* Booking assets now load only where needed
* Added automated tests and a build script

= 1.0.0 =
* 2025-11-28
* Initial release
* Admin calendar interface
* Frontend booking form
* Price calculation based on adults and kids
* Email notifications
* Booking management and cancellation system
