<?php
/**
 * Gutenberg Block Class for Booking Calendar
 *
 * @package WP_Booking_System_Luca
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Luca_Block Class
 */
class WP_Booking_System_Luca_Block {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register Gutenberg block.
	 */
	public function register_block() {
		// Check if Gutenberg is active.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register block script.
		wp_register_script(
			'wp-booking-system-luca-block',
			WP_BOOKING_SYSTEM_LUCA_PLUGIN_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			WP_BOOKING_SYSTEM_LUCA_VERSION,
			true
		);

		// Register block.
		register_block_type(
			'wp-booking-system/calendar',
			array(
				'editor_script' => 'wp-booking-system-luca-block',
				'render_callback' => array( $this, 'render_block' ),
				'attributes' => array(
					'title' => array(
						'type' => 'string',
						'default' => __( 'Booking Calendar', 'wp-booking-system-luca' ),
					),
				),
			)
		);
	}

	/**
	 * Render the block on frontend.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_block( $attributes ) {
		$title = isset( $attributes['title'] ) ? $attributes['title'] : __( 'Booking Calendar', 'wp-booking-system-luca' );

		// Use the existing shortcode render method.
		$frontend = wp_booking_system_luca()->frontend;
		return $frontend->render_booking_calendar( array( 'title' => $title ) );
	}
}
