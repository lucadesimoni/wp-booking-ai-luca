<?php
/**
 * Plugin Name: WP booking Luca
 * Version: 1.0.0
 * Plugin URI: https://famiglia-desimoni.ch/
 * Description: A simple and modern booking system for WordPress with calendar management, email notifications, and price calculations.
 * Author: Famiglia De Simoni
 * Author URI: https://famiglia-desimoni.ch/
 * Requires at least: 5.0
 * Tested up to: 6.4
 *
 * Text Domain: wp-booking-system
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Famiglia De Simoni
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'WP_BOOKING_SYSTEM_VERSION', '1.0.0' );
define( 'WP_BOOKING_SYSTEM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_BOOKING_SYSTEM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load plugin class files.
require_once 'includes/class-wp-booking-system.php';
require_once 'includes/class-wp-booking-system-database.php';
require_once 'includes/class-wp-booking-system-admin.php';
require_once 'includes/class-wp-booking-system-frontend.php';
require_once 'includes/class-wp-booking-system-ajax.php';
require_once 'includes/class-wp-booking-system-email.php';
require_once 'includes/class-wp-booking-system-widget.php';
require_once 'includes/class-wp-booking-system-block.php';

/**
 * Returns the main instance of WP_Booking_System to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_Booking_System
 */
function wp_booking_system() {
	$instance = WP_Booking_System::instance( __FILE__, WP_BOOKING_SYSTEM_VERSION );

	return $instance;
}

// Register activation hook.
register_activation_hook( __FILE__, array( 'WP_Booking_System', 'activate' ) );

// Initialize the plugin.
$wp_booking_system = wp_booking_system();

// Register deactivation hook after plugin is initialized.
register_deactivation_hook( __FILE__, array( $wp_booking_system, 'deactivate' ) );
