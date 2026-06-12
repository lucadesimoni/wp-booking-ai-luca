<?php
/**
 * Email class for sending booking notifications
 *
 * @package WP_Booking_System_Luca
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Luca_Email Class
 */
class WP_Booking_System_Luca_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Apply the configured "From" address/name to every email WordPress sends
		// from this site (so even the test email and any fallback path use it).
		add_filter( 'wp_mail_from', array( $this, 'filter_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'filter_mail_from_name' ) );

		// Route mail through an external SMTP server (e.g. Gmail) when configured.
		add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ) );
	}

	/**
	 * Filter the global "From" email address.
	 *
	 * @param string $from Default from address.
	 * @return string
	 */
	public function filter_mail_from( $from ) {
		$configured = get_option( 'wpbsl_email_from', '' );

		return ( $configured && is_email( $configured ) ) ? $configured : $from;
	}

	/**
	 * Filter the global "From" name.
	 *
	 * @param string $name Default from name.
	 * @return string
	 */
	public function filter_mail_from_name( $name ) {
		$configured = get_option( 'wpbsl_email_from_name', '' );

		return $configured ? $configured : $name;
	}

	/**
	 * Configure PHPMailer to send through an external SMTP server.
	 *
	 * Hooked to `phpmailer_init`. When SMTP delivery is enabled in Settings,
	 * this reroutes WordPress mail (including this plugin's notifications)
	 * through the configured server — e.g. Gmail / Google Workspace.
	 *
	 * @param object $phpmailer PHPMailer instance (passed by reference by WP).
	 * @return void
	 */
	public function configure_phpmailer( $phpmailer ) {
		if ( ! (int) get_option( 'wpbsl_smtp_enabled', 0 ) ) {
			return;
		}

		$host = trim( (string) get_option( 'wpbsl_smtp_host', '' ) );

		if ( '' === $host ) {
			// Misconfigured: fall back to the default mail transport rather than failing.
			return;
		}

		$encryption = get_option( 'wpbsl_smtp_encryption', 'tls' );

		$phpmailer->isSMTP();
		$phpmailer->Host        = $host;
		$phpmailer->Port        = (int) get_option( 'wpbsl_smtp_port', 587 );
		$phpmailer->SMTPAuth    = (bool) (int) get_option( 'wpbsl_smtp_auth', 1 );
		$phpmailer->SMTPSecure  = in_array( $encryption, array( 'ssl', 'tls' ), true ) ? $encryption : '';
		$phpmailer->SMTPAutoTLS = ( 'none' !== $encryption );

		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = (string) get_option( 'wpbsl_smtp_username', '' );
			$phpmailer->Password = (string) get_option( 'wpbsl_smtp_password', '' );
		}
	}

	/**
	 * Send a test email to verify the current delivery configuration.
	 *
	 * @param string $to Recipient address.
	 * @return array { @type bool $success, @type string $message }
	 */
	public function send_test_email( $to ) {
		$to = sanitize_email( $to );

		if ( ! is_email( $to ) ) {
			return array(
				'success' => false,
				'message' => __( 'Please enter a valid email address to send the test to.', 'wp-booking-system-luca' ),
			);
		}

		// Capture any PHPMailer error so we can surface it to the admin.
		$error_holder = new stdClass();
		$error_holder->message = '';
		$capture = function ( $wp_error ) use ( $error_holder ) {
			$error_holder->message = $wp_error->get_error_message();
		};
		add_action( 'wp_mail_failed', $capture );

		$subject = sprintf(
			/* translators: %s: Site name */
			__( '[%s] Test email from WP booking Luca', 'wp-booking-system-luca' ),
			get_bloginfo( 'name' )
		);

		$smtp_on = (int) get_option( 'wpbsl_smtp_enabled', 0 );
		$message = sprintf(
			/* translators: %s: delivery method description */
			__( 'This is a test email from WP booking Luca. If you received it, your booking notifications are being delivered correctly (%s).', 'wp-booking-system-luca' ),
			$smtp_on ? sprintf( __( 'via SMTP host %s', 'wp-booking-system-luca' ), get_option( 'wpbsl_smtp_host', '' ) ) : __( 'via the default WordPress mailer', 'wp-booking-system-luca' )
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( $to, $subject, '<p>' . esc_html( $message ) . '</p>', $headers );

		remove_action( 'wp_mail_failed', $capture );

		if ( $sent ) {
			return array(
				'success' => true,
				/* translators: %s: recipient email address */
				'message' => sprintf( __( 'Test email sent to %s. Please check the inbox (and spam folder).', 'wp-booking-system-luca' ), $to ),
			);
		}

		return array(
			'success' => false,
			'message' => $error_holder->message
				? sprintf( __( 'Sending failed: %s', 'wp-booking-system-luca' ), $error_holder->message )
				: __( 'Sending failed. Check your SMTP settings or install an SMTP plugin.', 'wp-booking-system-luca' ),
		);
	}

	/**
	 * Build the magic-link URL a guest uses to manage their booking.
	 *
	 * Resolves to the page created on activation; falls back to a
	 * conventional slug if that page is missing.
	 *
	 * @param string $token Booking token.
	 * @return string
	 */
	private function get_manage_url( $token ) {
		$manage_page_id = (int) get_option( 'wpbsl_manage_page_id', 0 );

		if ( $manage_page_id && 'publish' === get_post_status( $manage_page_id ) ) {
			$base = get_permalink( $manage_page_id );
		} else {
			$base = home_url( '/booking-manage/' );
		}

		return add_query_arg( 'token', rawurlencode( $token ), $base );
	}

	/**
	 * Send booking confirmation email.
	 *
	 * @param object $booking Booking object.
	 * @return bool
	 */
	public function send_booking_confirmation( $booking ) {
		$to      = $booking->email;
		$subject = sprintf( __( 'Booking Confirmation - %s', 'wp-booking-system-luca' ), get_bloginfo( 'name' ) );

		$message = $this->get_confirmation_email_template( $booking );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'wpbsl_email_from_name', get_bloginfo( 'name' ) ) . ' <' . get_option( 'wpbsl_email_from', get_option( 'admin_email' ) ) . '>',
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
		$admin_email = get_option( 'wpbsl_admin_notification_email', get_option( 'admin_email' ) );

		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			return false;
		}

		$to      = $admin_email;
		$subject = sprintf( __( 'New Booking Received - %s', 'wp-booking-system-luca' ), get_bloginfo( 'name' ) );

		$message = $this->get_admin_notification_template( $booking );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'wpbsl_email_from_name', get_bloginfo( 'name' ) ) . ' <' . get_option( 'wpbsl_email_from', get_option( 'admin_email' ) ) . '>',
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
		$currency = get_option( 'wpbsl_currency', 'CHF' );
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
					<h1><?php esc_html_e( 'New Booking Received', 'wp-booking-system-luca' ); ?></h1>
				</div>
				<div class="content">
					<p><?php esc_html_e( 'A new booking has been submitted:', 'wp-booking-system-luca' ); ?></p>

					<div class="booking-details">
						<h3><?php esc_html_e( 'Booking Details', 'wp-booking-system-luca' ); ?></h3>
						<p><strong><?php esc_html_e( 'Guest:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->first_name . ' ' . $booking->last_name ); ?></p>
						<p><strong><?php esc_html_e( 'Email:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->email ); ?></p>
						<p><strong><?php esc_html_e( 'Phone:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->phone ? $booking->phone : __( 'N/A', 'wp-booking-system-luca' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Check-in:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Check-out:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Guests:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system-luca' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system-luca' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Total Price:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . $currency ); ?></p>
						<p><strong><?php esc_html_e( 'Status:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( ucfirst( $booking->status ) ); ?></p>
						<?php if ( ! empty( $booking->notes ) ) : ?>
							<p><strong><?php esc_html_e( 'Notes:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->notes ); ?></p>
						<?php endif; ?>
					</div>

					<a href="<?php echo esc_url( $admin_url ); ?>" class="button"><?php esc_html_e( 'View Booking', 'wp-booking-system-luca' ); ?></a>
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
		$subject = sprintf( __( 'Booking Cancelled - %s', 'wp-booking-system-luca' ), get_bloginfo( 'name' ) );

		$message = $this->get_cancellation_email_template( $booking );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'wpbsl_email_from_name', get_bloginfo( 'name' ) ) . ' <' . get_option( 'wpbsl_email_from', get_option( 'admin_email' ) ) . '>',
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
		$manage_url = $this->get_manage_url( $booking->booking_token );

		$currency = get_option( 'wpbsl_currency', 'CHF' );

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
					<h1><?php esc_html_e( 'Booking Confirmation', 'wp-booking-system-luca' ); ?></h1>
				</div>
				<div class="content">
					<p><?php echo sprintf( esc_html__( 'Dear %s %s,', 'wp-booking-system-luca' ), esc_html( $booking->first_name ), esc_html( $booking->last_name ) ); ?></p>
					<p><?php esc_html_e( 'Thank you for your booking! We are pleased to confirm your reservation.', 'wp-booking-system-luca' ); ?></p>

					<div class="booking-details">
						<h3><?php esc_html_e( 'Booking Details', 'wp-booking-system-luca' ); ?></h3>
						<p><strong><?php esc_html_e( 'Check-in:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Check-out:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></p>
						<p><strong><?php esc_html_e( 'Guests:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system-luca' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system-luca' ) ); ?></p>
						<p><strong><?php esc_html_e( 'Total Price:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . $currency ); ?></p>
						<?php if ( ! empty( $booking->notes ) ) : ?>
							<p><strong><?php esc_html_e( 'Notes:', 'wp-booking-system-luca' ); ?></strong> <?php echo esc_html( $booking->notes ); ?></p>
						<?php endif; ?>
					</div>

					<p><?php esc_html_e( 'You can manage or cancel your booking using the link below:', 'wp-booking-system-luca' ); ?></p>
					<a href="<?php echo esc_url( $manage_url ); ?>" class="button"><?php esc_html_e( 'Manage Booking', 'wp-booking-system-luca' ); ?></a>

					<p><?php esc_html_e( 'We look forward to welcoming you!', 'wp-booking-system-luca' ); ?></p>
					<p><?php esc_html_e( 'Best regards,', 'wp-booking-system-luca' ); ?><br><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
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
					<h1><?php esc_html_e( 'Booking Cancelled', 'wp-booking-system-luca' ); ?></h1>
				</div>
				<div class="content">
					<p><?php echo sprintf( esc_html__( 'Dear %s %s,', 'wp-booking-system-luca' ), esc_html( $booking->first_name ), esc_html( $booking->last_name ) ); ?></p>
					<p><?php esc_html_e( 'Your booking has been cancelled as requested.', 'wp-booking-system-luca' ); ?></p>
					<p><?php esc_html_e( 'We hope to welcome you in the future!', 'wp-booking-system-luca' ); ?></p>
					<p><?php esc_html_e( 'Best regards,', 'wp-booking-system-luca' ); ?><br><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
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

