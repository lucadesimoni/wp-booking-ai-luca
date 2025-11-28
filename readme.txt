=== WP Booking System ===
Contributors: famiglia-desimoni
Tags: booking, calendar, reservation, booking-system
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and modern booking system for WordPress with calendar management, email notifications, and price calculations.

== Description ==

WP Booking System is a clean and modern booking solution for WordPress. It provides a simple interface for managing bookings with the following features:

* Admin calendar view for managing all bookings
* Frontend booking form with date selection
* Price calculation based on number of adults and kids
* Email notifications for booking confirmations and cancellations
* Unique links for guests to manage or cancel their bookings
* Modern, responsive design that fits seamlessly into your website

Perfect for vacation rentals, hotels, or any accommodation booking needs.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-booking-system` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Bookings > Settings to configure pricing and email settings
4. Use the shortcode `[wp_booking_form]` to display the booking form on any page
5. Create a page with the shortcode `[wp_booking_manage]` for guests to manage their bookings (the token will be sent via email)

== Screenshots ==

1. Admin calendar view for managing bookings
2. Frontend booking form with modern design
3. Booking management page for guests

== Frequently Asked Questions ==

= How do I display the booking form? =

Use the shortcode `[wp_booking_form]` on any page or post where you want the booking form to appear.

= How do guests manage their bookings? =

Guests receive an email with a unique link to manage or cancel their booking. You can also create a page with the shortcode `[wp_booking_manage]` - guests will access it via the token in the URL.

= How do I set pricing? =

Go to Bookings > Settings in your WordPress admin and configure the price per adult and price per kid. Prices are calculated per night.

= Can I customize the email templates? =

Currently, email templates are built into the plugin. Future versions may include customizable templates.

== Changelog ==

= 1.0.0 =
* 2025-11-28
* Initial release
* Admin calendar interface
* Frontend booking form
* Price calculation based on adults and kids
* Email notifications
* Booking management and cancellation system
