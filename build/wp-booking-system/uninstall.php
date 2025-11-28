<?php
/**
 * This file runs when the plugin in uninstalled (deleted).
 * This will not run when the plugin is deactivated.
 * Ideally you will add all your clean-up scripts here
 * that will clean-up unused meta, options, etc. in the database.
 *
 * @package WP_Booking_System/Uninstall
 */

// If plugin is not being uninstalled, exit (do nothing).
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete database table.
$table_name = $wpdb->prefix . 'wpbs_bookings';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery

// Delete options.
delete_option( 'wpbs_price_adult' );
delete_option( 'wpbs_price_kid' );
delete_option( 'wpbs_currency' );
delete_option( 'wpbs_email_from' );
delete_option( 'wpbs_email_from_name' );

// Clear any cached data.
wp_cache_flush();
