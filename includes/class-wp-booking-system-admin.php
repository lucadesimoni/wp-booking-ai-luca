<?php
/**
 * Admin class for managing bookings
 *
 * @package WP_Booking_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Admin Class
 */
class WP_Booking_System_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Bookings', 'wp-booking-system' ),
			__( 'Bookings', 'wp-booking-system' ),
			'manage_options',
			'wp-booking-system',
			array( $this, 'render_calendar_page' ),
			'dashicons-calendar-alt',
			30
		);

		add_submenu_page(
			'wp-booking-system',
			__( 'All Bookings', 'wp-booking-system' ),
			__( 'All Bookings', 'wp-booking-system' ),
			'manage_options',
			'wp-booking-system-list',
			array( $this, 'render_list_page' )
		);

		add_submenu_page(
			'wp-booking-system',
			__( 'Settings', 'wp-booking-system' ),
			__( 'Settings', 'wp-booking-system' ),
			'manage_options',
			'wp-booking-system-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'wp-booking-system' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wp-booking-system-admin',
			WP_BOOKING_SYSTEM_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_BOOKING_SYSTEM_VERSION
		);

		wp_enqueue_script(
			'wp-booking-system-admin',
			WP_BOOKING_SYSTEM_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WP_BOOKING_SYSTEM_VERSION,
			true
		);

		wp_localize_script(
			'wp-booking-system-admin',
			'wpbsAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wp-booking-system-admin' ),
				'i18n'    => array(
					'confirmDelete' => __( 'Are you sure you want to delete this booking?', 'wp-booking-system' ),
				),
			)
		);

		// FullCalendar CSS and JS.
		wp_enqueue_style(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css',
			array(),
			'6.1.10'
		);

		wp_enqueue_script(
			'fullcalendar',
			'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js',
			array(),
			'6.1.10',
			true
		);
	}

	/**
	 * Render calendar page.
	 */
	public function render_calendar_page() {
		$bookings = wp_booking_system()->database->get_bookings();
		?>
		<div class="wrap wpbs-admin-wrap">
			<h1><?php esc_html_e( 'Booking Calendar', 'wp-booking-system' ); ?></h1>
			<div id="wpbs-calendar"></div>
		</div>
		<?php
	}

	/**
	 * Render list page.
	 */
	public function render_list_page() {
		$bookings = wp_booking_system()->database->get_bookings();
		?>
		<div class="wrap wpbs-admin-wrap">
			<h1><?php esc_html_e( 'All Bookings', 'wp-booking-system' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Guest', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Check-in', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Check-out', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Guests', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Price', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wp-booking-system' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'wp-booking-system' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $bookings ) ) : ?>
						<tr>
							<td colspan="9"><?php esc_html_e( 'No bookings found.', 'wp-booking-system' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $bookings as $booking ) : ?>
							<tr>
								<td><?php echo esc_html( $booking->id ); ?></td>
								<td><?php echo esc_html( $booking->first_name . ' ' . $booking->last_name ); ?></td>
								<td><?php echo esc_html( $booking->email ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></td>
								<td><?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system' ) ); ?></td>
								<td><?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . get_option( 'wpbs_currency', 'CHF' ) ); ?></td>
								<td>
									<span class="wpbs-status wpbs-status-<?php echo esc_attr( $booking->status ); ?>">
										<?php echo esc_html( ucfirst( $booking->status ) ); ?>
									</span>
								</td>
								<td>
									<a href="#" class="wpbs-view-booking" data-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'View', 'wp-booking-system' ); ?>
									</a> |
									<a href="#" class="wpbs-delete-booking" data-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'Delete', 'wp-booking-system' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( isset( $_POST['wpbs_save_settings'] ) && check_admin_referer( 'wpbs_settings' ) ) {
			// Validate and sanitize input.
			$price_adult     = isset( $_POST['wpbs_price_adult'] ) ? floatval( $_POST['wpbs_price_adult'] ) : 0;
			$price_kid       = isset( $_POST['wpbs_price_kid'] ) ? floatval( $_POST['wpbs_price_kid'] ) : 0;
			$currency        = isset( $_POST['wpbs_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['wpbs_currency'] ) ) : 'CHF';
			$email_from      = isset( $_POST['wpbs_email_from'] ) ? sanitize_email( wp_unslash( $_POST['wpbs_email_from'] ) ) : '';
			$email_from_name = isset( $_POST['wpbs_email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpbs_email_from_name'] ) ) : '';

			// Validate email.
			if ( ! is_email( $email_from ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid email address.', 'wp-booking-system' ) . '</p></div>';
			} else {
				update_option( 'wpbs_price_adult', $price_adult );
				update_option( 'wpbs_price_kid', $price_kid );
				update_option( 'wpbs_currency', $currency );
				update_option( 'wpbs_email_from', $email_from );
				update_option( 'wpbs_email_from_name', $email_from_name );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'wp-booking-system' ) . '</p></div>';
			}
		}

		$price_adult     = get_option( 'wpbs_price_adult', 50 );
		$price_kid       = get_option( 'wpbs_price_kid', 25 );
		$currency        = get_option( 'wpbs_currency', 'CHF' );
		$email_from      = get_option( 'wpbs_email_from', get_option( 'admin_email' ) );
		$email_from_name = get_option( 'wpbs_email_from_name', get_bloginfo( 'name' ) );
		?>
		<div class="wrap wpbs-admin-wrap">
			<h1><?php esc_html_e( 'Booking Settings', 'wp-booking-system' ); ?></h1>
			<form method="post" action="">
				<?php wp_nonce_field( 'wpbs_settings' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="wpbs_price_adult"><?php esc_html_e( 'Price per Adult (per night)', 'wp-booking-system' ); ?></label>
						</th>
						<td>
							<input type="number" step="0.01" id="wpbs_price_adult" name="wpbs_price_adult" value="<?php echo esc_attr( $price_adult ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbs_price_kid"><?php esc_html_e( 'Price per Kid (per night)', 'wp-booking-system' ); ?></label>
						</th>
						<td>
							<input type="number" step="0.01" id="wpbs_price_kid" name="wpbs_price_kid" value="<?php echo esc_attr( $price_kid ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbs_currency"><?php esc_html_e( 'Currency', 'wp-booking-system' ); ?></label>
						</th>
						<td>
							<input type="text" id="wpbs_currency" name="wpbs_currency" value="<?php echo esc_attr( $currency ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbs_email_from"><?php esc_html_e( 'Email From Address', 'wp-booking-system' ); ?></label>
						</th>
						<td>
							<input type="email" id="wpbs_email_from" name="wpbs_email_from" value="<?php echo esc_attr( $email_from ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbs_email_from_name"><?php esc_html_e( 'Email From Name', 'wp-booking-system' ); ?></label>
						</th>
						<td>
							<input type="text" id="wpbs_email_from_name" name="wpbs_email_from_name" value="<?php echo esc_attr( $email_from_name ); ?>" class="regular-text" />
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'wp-booking-system' ), 'primary', 'wpbs_save_settings' ); ?>
			</form>
		</div>
		<?php
	}
}

