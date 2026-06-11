<?php
/**
 * Stateless helper functions.
 *
 * Pure logic (pricing, validation, formatting) lives here so it can be
 * unit-tested without a running WordPress instance.
 *
 * @package WP_Booking_System_Luca
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Luca_Helpers Class
 */
class WP_Booking_System_Luca_Helpers {

	/**
	 * Number of nights between two dates (minimum 1).
	 *
	 * @param string $check_in  Check-in date (Y-m-d).
	 * @param string $check_out Check-out date (Y-m-d).
	 * @return int
	 */
	public static function calculate_nights( $check_in, $check_out ) {
		$in  = strtotime( $check_in );
		$out = strtotime( $check_out );

		if ( false === $in || false === $out || $out <= $in ) {
			return 1;
		}

		return (int) max( 1, floor( ( $out - $in ) / DAY_IN_SECONDS ) );
	}

	/**
	 * Calculate the total price of a stay.
	 *
	 * @param string $check_in    Check-in date (Y-m-d).
	 * @param string $check_out   Check-out date (Y-m-d).
	 * @param int    $adults      Number of adults.
	 * @param int    $kids        Number of kids.
	 * @param float  $price_adult Nightly price per adult.
	 * @param float  $price_kid   Nightly price per kid.
	 * @return float
	 */
	public static function calculate_price( $check_in, $check_out, $adults, $kids, $price_adult, $price_kid ) {
		$nights = self::calculate_nights( $check_in, $check_out );
		$adults = max( 0, (int) $adults );
		$kids   = max( 0, (int) $kids );

		$total = ( $adults * (float) $price_adult + $kids * (float) $price_kid ) * $nights;

		return round( (float) $total, 2 );
	}

	/**
	 * Validate a date string in Y-m-d format.
	 *
	 * @param string $date Date string.
	 * @return bool
	 */
	public static function is_valid_date( $date ) {
		if ( ! is_string( $date ) || '' === $date ) {
			return false;
		}

		$d = DateTime::createFromFormat( 'Y-m-d', $date );

		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Validate a booking token (64-character hex string).
	 *
	 * @param string $token Token.
	 * @return bool
	 */
	public static function is_valid_token( $token ) {
		return is_string( $token ) && (bool) preg_match( '/^[a-f0-9]{64}$/i', $token );
	}

	/**
	 * Whether the requested party exceeds the chalet capacity.
	 *
	 * @param int $adults       Number of adults.
	 * @param int $kids         Number of kids.
	 * @param int $max_capacity Maximum allowed guests.
	 * @return bool
	 */
	public static function exceeds_capacity( $adults, $kids, $max_capacity ) {
		return ( (int) $adults + (int) $kids ) > (int) $max_capacity;
	}

	/**
	 * Whether the requested check-out is after the check-in.
	 *
	 * @param string $check_in  Check-in date (Y-m-d).
	 * @param string $check_out Check-out date (Y-m-d).
	 * @return bool
	 */
	public static function is_valid_range( $check_in, $check_out ) {
		if ( ! self::is_valid_date( $check_in ) || ! self::is_valid_date( $check_out ) ) {
			return false;
		}

		return strtotime( $check_out ) > strtotime( $check_in );
	}

	/**
	 * Allowed booking statuses.
	 *
	 * @return string[]
	 */
	public static function allowed_statuses() {
		return array( 'pending', 'confirmed', 'cancelled' );
	}

	/**
	 * Whether a status string is valid.
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function is_valid_status( $status ) {
		return in_array( $status, self::allowed_statuses(), true );
	}
}
