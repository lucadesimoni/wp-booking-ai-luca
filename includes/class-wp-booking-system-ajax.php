<?php
/**
 * AJAX class for handling frontend and admin requests
 *
 * @package WP_Booking_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Ajax Class
 */
class WP_Booking_System_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Frontend AJAX.
		add_action( 'wp_ajax_wpbs_check_availability', array( $this, 'check_availability' ) );
		add_action( 'wp_ajax_nopriv_wpbs_check_availability', array( $this, 'check_availability' ) );
		add_action( 'wp_ajax_wpbs_calculate_price', array( $this, 'calculate_price' ) );
		add_action( 'wp_ajax_nopriv_wpbs_calculate_price', array( $this, 'calculate_price' ) );
		add_action( 'wp_ajax_wpbs_submit_booking', array( $this, 'submit_booking' ) );
		add_action( 'wp_ajax_nopriv_wpbs_submit_booking', array( $this, 'submit_booking' ) );
		add_action( 'wp_ajax_wpbs_cancel_booking', array( $this, 'cancel_booking' ) );
		add_action( 'wp_ajax_nopriv_wpbs_cancel_booking', array( $this, 'cancel_booking' ) );

		// Admin AJAX.
		add_action( 'wp_ajax_wpbs_get_bookings', array( $this, 'get_bookings' ) );
		add_action( 'wp_ajax_wpbs_get_booking', array( $this, 'get_booking' ) );
		add_action( 'wp_ajax_wpbs_delete_booking', array( $this, 'delete_booking' ) );

		// Calendar availability (frontend).
		add_action( 'wp_ajax_wpbs_get_calendar_availability', array( $this, 'get_calendar_availability' ) );
		add_action( 'wp_ajax_nopriv_wpbs_get_calendar_availability', array( $this, 'get_calendar_availability' ) );
	}

	/**
	 * Check availability.
	 */
	public function check_availability() {
		check_ajax_referer( 'wp-booking-system-frontend', 'nonce' );

		$check_in  = isset( $_POST['check_in'] ) ? sanitize_text_field( wp_unslash( $_POST['check_in'] ) ) : '';
		$check_out = isset( $_POST['check_out'] ) ? sanitize_text_field( wp_unslash( $_POST['check_out'] ) ) : '';

		if ( empty( $check_in ) || empty( $check_out ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select both dates.', 'wp-booking-system' ) ) );
		}

		// Validate date format.
		if ( ! $this->validate_date( $check_in ) || ! $this->validate_date( $check_out ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date format.', 'wp-booking-system' ) ) );
		}

		$available = wp_booking_system()->database->is_available( $check_in, $check_out );

		wp_send_json_success( array( 'available' => $available ) );
	}

	/**
	 * Calculate price.
	 */
	public function calculate_price() {
		check_ajax_referer( 'wp-booking-system-frontend', 'nonce' );

		$check_in  = isset( $_POST['check_in'] ) ? sanitize_text_field( wp_unslash( $_POST['check_in'] ) ) : '';
		$check_out = isset( $_POST['check_out'] ) ? sanitize_text_field( wp_unslash( $_POST['check_out'] ) ) : '';
		$adults    = isset( $_POST['adults'] ) ? absint( $_POST['adults'] ) : 1;
		$kids      = isset( $_POST['kids'] ) ? absint( $_POST['kids'] ) : 0;

		if ( empty( $check_in ) || empty( $check_out ) ) {
			wp_send_json_error( array( 'message' => __( 'Please select both dates.', 'wp-booking-system' ) ) );
		}

		// Validate date format.
		if ( ! $this->validate_date( $check_in ) || ! $this->validate_date( $check_out ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date format.', 'wp-booking-system' ) ) );
		}

		// Validate capacity.
		$total_guests = $adults + $kids;
		$max_capacity = absint( get_option( 'wpbs_chalet_capacity', 10 ) );

		if ( $total_guests > $max_capacity ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: Maximum capacity */
						__( 'The chalet can accommodate a maximum of %d guests. Please reduce the number of guests.', 'wp-booking-system' ),
						$max_capacity
					),
				)
			);
		}

		$price = $this->calculate_booking_price( $check_in, $check_out, $adults, $kids );
		$currency = get_option( 'wpbs_currency', 'CHF' );

		wp_send_json_success(
			array(
				'price'    => $price,
				'currency' => $currency,
				'formatted' => number_format_i18n( $price, 2 ) . ' ' . esc_html( $currency ),
			)
		);
	}

	/**
	 * Submit booking.
	 */
	public function submit_booking() {
		check_ajax_referer( 'wp-booking-system-frontend', 'nonce' );

		$data = array(
			'first_name' => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
			'last_name'  => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
			'email'      => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
			'phone'      => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
			'check_in'   => isset( $_POST['check_in'] ) ? sanitize_text_field( wp_unslash( $_POST['check_in'] ) ) : '',
			'check_out'  => isset( $_POST['check_out'] ) ? sanitize_text_field( wp_unslash( $_POST['check_out'] ) ) : '',
			'adults'     => isset( $_POST['adults'] ) ? absint( $_POST['adults'] ) : 1,
			'kids'       => isset( $_POST['kids'] ) ? absint( $_POST['kids'] ) : 0,
			'notes'      => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		);

		// Validate required fields.
		if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) || empty( $data['email'] ) || empty( $data['check_in'] ) || empty( $data['check_out'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'wp-booking-system' ) ) );
		}

		// Validate email format.
		if ( ! is_email( $data['email'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'wp-booking-system' ) ) );
		}

		// Validate date format.
		if ( ! $this->validate_date( $data['check_in'] ) || ! $this->validate_date( $data['check_out'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date format.', 'wp-booking-system' ) ) );
		}

		// Validate date range.
		if ( strtotime( $data['check_out'] ) <= strtotime( $data['check_in'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Check-out date must be after check-in date.', 'wp-booking-system' ) ) );
		}

		// Validate guest counts.
		if ( $data['adults'] < 1 ) {
			wp_send_json_error( array( 'message' => __( 'At least one adult is required.', 'wp-booking-system' ) ) );
		}

		if ( $data['kids'] < 0 ) {
			$data['kids'] = 0;
		}

		// Validate capacity.
		$total_guests = $data['adults'] + $data['kids'];
		$max_capacity = absint( get_option( 'wpbs_chalet_capacity', 10 ) );

		if ( $total_guests > $max_capacity ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: Maximum capacity */
						__( 'The chalet can accommodate a maximum of %d guests. Please reduce the number of guests.', 'wp-booking-system' ),
						$max_capacity
					),
				)
			);
		}

		// Check availability.
		if ( ! wp_booking_system()->database->is_available( $data['check_in'], $data['check_out'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Selected dates are not available.', 'wp-booking-system' ) ) );
		}

		// Calculate price.
		$data['total_price'] = $this->calculate_booking_price( $data['check_in'], $data['check_out'], $data['adults'], $data['kids'] );

		// Insert booking.
		$booking_id = wp_booking_system()->database->insert_booking( $data );

		if ( ! $booking_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to create booking. Please try again.', 'wp-booking-system' ) ) );
		}

		// Get booking with token.
		$booking = wp_booking_system()->database->get_booking( $booking_id );

		// Send email.
		wp_booking_system()->email->send_booking_confirmation( $booking );

		wp_send_json_success(
			array(
				'message' => __( 'Booking submitted successfully! Check your email for confirmation.', 'wp-booking-system' ),
				'booking_id' => $booking_id,
			)
		);
	}

	/**
	 * Cancel booking.
	 */
	public function cancel_booking() {
		check_ajax_referer( 'wp-booking-system-frontend', 'nonce' );

		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

		if ( empty( $token ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking token.', 'wp-booking-system' ) ) );
		}

		// Validate token format (64 character hex string).
		if ( ! preg_match( '/^[a-f0-9]{64}$/i', $token ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking token format.', 'wp-booking-system' ) ) );
		}

		$booking = wp_booking_system()->database->get_booking_by_token( $token );

		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found.', 'wp-booking-system' ) ) );
		}

		// Update status to cancelled.
		$result = wp_booking_system()->database->update_booking( $booking->id, array( 'status' => 'cancelled' ) );

		if ( $result ) {
			// Send cancellation email.
			wp_booking_system()->email->send_booking_cancellation( $booking );
			wp_send_json_success( array( 'message' => __( 'Booking cancelled successfully.', 'wp-booking-system' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to cancel booking.', 'wp-booking-system' ) ) );
		}
	}

	/**
	 * Get bookings for calendar (admin).
	 */
	public function get_bookings() {
		check_ajax_referer( 'wp-booking-system-admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-booking-system' ) ) );
		}

		$start = isset( $_GET['start'] ) ? sanitize_text_field( wp_unslash( $_GET['start'] ) ) : '';
		$end   = isset( $_GET['end'] ) ? sanitize_text_field( wp_unslash( $_GET['end'] ) ) : '';

		$bookings = wp_booking_system()->database->get_bookings_for_calendar( $start, $end );

		$events = array();
		foreach ( $bookings as $booking ) {
			$events[] = array(
				'id'    => $booking->id,
				'title' => $booking->first_name . ' ' . $booking->last_name,
				'start' => $booking->check_in,
				'end'   => date( 'Y-m-d', strtotime( $booking->check_out . ' +1 day' ) ),
				'color' => $this->get_status_color( $booking->status ),
			);
		}

		wp_send_json_success( $events );
	}

	/**
	 * Get single booking (admin).
	 */
	public function get_booking() {
		check_ajax_referer( 'wp-booking-system-admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-booking-system' ) ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking ID.', 'wp-booking-system' ) ) );
		}

		$booking = wp_booking_system()->database->get_booking( $id );

		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found.', 'wp-booking-system' ) ) );
		}

		wp_send_json_success( $booking );
	}

	/**
	 * Delete booking (admin).
	 */
	public function delete_booking() {
		check_ajax_referer( 'wp-booking-system-admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-booking-system' ) ) );
		}

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid booking ID.', 'wp-booking-system' ) ) );
		}

		$result = wp_booking_system()->database->delete_booking( $id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Booking deleted successfully.', 'wp-booking-system' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete booking.', 'wp-booking-system' ) ) );
		}
	}

	/**
	 * Calculate booking price.
	 *
	 * @param string $check_in Check-in date.
	 * @param string $check_out Check-out date.
	 * @param int    $adults Number of adults.
	 * @param int    $kids Number of kids.
	 * @return float
	 */
	private function calculate_booking_price( $check_in, $check_out, $adults, $kids ) {
		$price_adult = floatval( get_option( 'wpbs_price_adult', 50 ) );
		$price_kid   = floatval( get_option( 'wpbs_price_kid', 25 ) );

		$check_in_timestamp  = strtotime( $check_in );
		$check_out_timestamp = strtotime( $check_out );
		$nights              = max( 1, floor( ( $check_out_timestamp - $check_in_timestamp ) / DAY_IN_SECONDS ) );

		$total = ( $adults * $price_adult + $kids * $price_kid ) * $nights;

		return $total;
	}

	/**
	 * Get calendar availability for frontend widget.
	 */
	public function get_calendar_availability() {
		check_ajax_referer( 'wp-booking-system-frontend', 'nonce' );

		$start = isset( $_GET['start'] ) ? sanitize_text_field( wp_unslash( $_GET['start'] ) ) : '';
		$end   = isset( $_GET['end'] ) ? sanitize_text_field( wp_unslash( $_GET['end'] ) ) : '';

		if ( empty( $start ) || empty( $end ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date range.', 'wp-booking-system' ) ) );
		}

		$bookings = wp_booking_system()->database->get_bookings_for_calendar( $start, $end );

		$events = array();
		foreach ( $bookings as $booking ) {
			$events[] = array(
				'id'    => $booking->id,
				'title' => __( 'Booked', 'wp-booking-system' ),
				'start' => $booking->check_in,
				'end'   => date( 'Y-m-d', strtotime( $booking->check_out . ' +1 day' ) ),
				'display' => 'background',
				'backgroundColor' => '#8B0000',
				'borderColor' => '#8B0000',
			);
		}

		wp_send_json_success( $events );
	}

	/**
	 * Validate date format (Y-m-d).
	 *
	 * @param string $date Date string.
	 * @return bool
	 */
	private function validate_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Get status color for calendar.
	 *
	 * @param string $status Booking status.
	 * @return string
	 */
	private function get_status_color( $status ) {
		$colors = array(
			'pending'  => '#ff9800',
			'confirmed' => '#4caf50',
			'cancelled' => '#f44336',
		);

		return isset( $colors[ $status ] ) ? $colors[ $status ] : '#757575';
	}
}

