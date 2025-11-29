<?php
/**
 * Email class for sending booking notifications
 *
 * @package WP_Booking_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Email Class
 */
class WP_Booking_System_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Email functionality is ready to use.
	}

	/**
	 * Send booking confirmation email.
	 *
	 * @param object $booking Booking object.
	 * @return bool
	 */
	public function send_booking_confirmation( $booking ) {
		$to      = $booking->email;
		$subject = sprintf( __( 'Booking Confirmation - %s', 'wp-booking-system' ), get_bloginfo( 'name' ) );

		$message = $this->get_confirmation_email_template( $booking );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'wpbs_email_from_name', get_bloginfo( 'name' ) ) . ' <' . get_option( 'wpbs_email_from', get_option( 'admin_email' ) ) . '>',
		);

		$result = wp_mail( $to, $subject, $message, $headers );

		// Send admin notification.
		$this->send_admin_notification( $booking );

		return $result;
	}

	/**
	 * Send admin notification email for new booking.
	 *
	 * @param object $booking Booking object.
	 * @return bool
	 */
	public function send_admin_notification( $booking ) {
		$admin_email = get_option( 'wpbs_admin_notification_email', get_option( 'admin_email' ) );

		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			return false;
		}

		$to      = $admin_email;
		$subject = sprintf( __( 'New Booking Received - %s', 'wp-booking-system' ), get_bloginfo( 'name' ) );

		$message = $this->get_admin_notification_template( $booking );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'wpbs_email_from_name', get_bloginfo( 'name' ) ) . ' <' . get_option( 'wpbs_email_from', get_option( 'admin_email' ) ) . '>',
		);

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Get admin notification email template.
	 *
	 * @param object $booking Booking object.
	 * @return string
	 */
	private function get_admin_notification_template( $booking ) {
		$currency = get_option( 'wpbs_currency', 'CHF' );
		$admin_url = admin_url( 'admin.php?page=wp-booking-system-list' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background-color: #8B0000; color: white; padding: 20px; text-align: center; }
				.content { background-color: #f9f9f9; padding: 20px; }
				.booking-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #8B0000; }
				.booking-details p { margin: 8px 0; }
				.button { display: inline-block; padding: 12px 24px; background-color: #8B0000; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
				.footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'New Booking Received', 'wp-booking-system' ); ?></h1>
				</div>
				<div class="content">
					<p><?php esc_html_e( 'A new booking has been submitted:', 'wp-booking-system' ); ?></p>

					<div class="booking-details">
						<h3><?php esc_html_e( 'Booking Details', 'wp-booking-system' ); ?></h3>
						<p><strong><?php esc_html_e( 'Guest:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->first_name . ' ' . $booking->last_name ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->email ); ?></p>
						<p><strong><?php esc_html_e( 'Phone:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->phone ? $booking->phone : __( 'N/A', 'wp-booking-system' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Check-in:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Check-out:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Guests:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Total Price:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . $currency ); ?></p>
						<p><strong><?php esc_html_e( 'Status:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( ucfirst( $booking->status ) ); ?></p>
						<?php if ( ! empty( $booking->notes ) ) : ?>
							<p><strong><?php esc_html_e( 'Notes:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->notes ); ?></p>
						<?php endif; ?>
					</div>

					<a href="<?php echo esc_url( $admin_url ); ?>" class="button"><?php esc_html_e( 'View Booking', 'wp-booking-system' ); ?></a>
				</div>
				<div class="footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?> | <?php echo esc_url( home_url() ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Send booking cancellation email.
	 *
	 * @param object $booking Booking object.
	 * @return bool
	 */
	public function send_booking_cancellation( $booking ) {
		$to      = $booking->email;
		$subject = sprintf( __( 'Booking Cancelled - %s', 'wp-booking-system' ), get_bloginfo( 'name' ) );

		$message = $this->get_cancellation_email_template( $booking );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'wpbs_email_from_name', get_bloginfo( 'name' ) ) . ' <' . get_option( 'wpbs_email_from', get_option( 'admin_email' ) ) . '>',
		);

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Get confirmation email template.
	 *
	 * @param object $booking Booking object.
	 * @return string
	 */
	private function get_confirmation_email_template( $booking ) {
		$manage_url = add_query_arg(
			array( 'token' => $booking->booking_token ),
			home_url( '/booking-manage/' )
		);

		$currency = get_option( 'wpbs_currency', 'CHF' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background-color: #8B0000; color: white; padding: 20px; text-align: center; }
				.content { background-color: #f9f9f9; padding: 20px; }
				.booking-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #8B0000; }
				.booking-details p { margin: 8px 0; }
				.button { display: inline-block; padding: 12px 24px; background-color: #8B0000; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
				.footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Confirmation', 'wp-booking-system' ); ?></h1>
				</div>
				<div class="content">
					<p><?php echo sprintf( esc_html__( 'Dear %s %s,', 'wp-booking-system' ), esc_html( $booking->first_name ), esc_html( $booking->last_name ) ); ?></p>
					<p><?php esc_html_e( 'Thank you for your booking! We are pleased to confirm your reservation.', 'wp-booking-system' ); ?></p>

					<div class="booking-details">
						<h3><?php esc_html_e( 'Booking Details', 'wp-booking-system' ); ?></h3>
						<p><strong><?php esc_html_e( 'Check-in:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Check-out:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Guests:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Total Price:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . $currency ); ?></p>
						<?php if ( ! empty( $booking->notes ) ) : ?>
							<p><strong><?php esc_html_e( 'Notes:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->notes ); ?></p>
						<?php endif; ?>
					</div>

					<p><?php esc_html_e( 'You can manage or cancel your booking using the link below:', 'wp-booking-system' ); ?></p>
					<a href="<?php echo esc_url( $manage_url ); ?>" class="button"><?php esc_html_e( 'Manage Booking', 'wp-booking-system' ); ?></a>

					<p><?php esc_html_e( 'We look forward to welcoming you!', 'wp-booking-system' ); ?></p>
					<p><?php esc_html_e( 'Best regards,', 'wp-booking-system' ); ?><br><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				</div>
				<div class="footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?> | <?php echo esc_url( home_url() ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get cancellation email template.
	 *
	 * @param object $booking Booking object.
	 * @return string
	 */
	private function get_cancellation_email_template( $booking ) {
		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background-color: #8B0000; color: white; padding: 20px; text-align: center; }
				.content { background-color: #f9f9f9; padding: 20px; }
				.footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1><?php esc_html_e( 'Booking Cancelled', 'wp-booking-system' ); ?></h1>
				</div>
				<div class="content">
					<p><?php echo sprintf( esc_html__( 'Dear %s %s,', 'wp-booking-system' ), esc_html( $booking->first_name ), esc_html( $booking->last_name ) ); ?></p>
					<p><?php esc_html_e( 'Your booking has been cancelled as requested.', 'wp-booking-system' ); ?></p>
					<p><?php esc_html_e( 'We hope to welcome you in the future!', 'wp-booking-system' ); ?></p>
					<p><?php esc_html_e( 'Best regards,', 'wp-booking-system' ); ?><br><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				</div>
				<div class="footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?> | <?php echo esc_url( home_url() ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}

