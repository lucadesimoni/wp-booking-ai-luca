<?php
/**
 * Admin class for managing bookings
 *
 * @package WP_Booking_System_Luca
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Luca_Admin Class
 */
class WP_Booking_System_Luca_Admin {

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
			__( 'Bookings', 'wp-booking-system-luca' ),
			__( 'WP booking Luca', 'wp-booking-system-luca' ),
			'manage_options',
			'wp-booking-system-luca',
			array( $this, 'render_calendar_page' ),
			'dashicons-calendar-alt',
			30
		);

		add_submenu_page(
			'wp-booking-system-luca',
			__( 'All Bookings', 'wp-booking-system-luca' ),
			__( 'All Bookings', 'wp-booking-system-luca' ),
			'manage_options',
			'wp-booking-system-list',
			array( $this, 'render_list_page' )
		);

		add_submenu_page(
			'wp-booking-system-luca',
			__( 'Settings', 'wp-booking-system-luca' ),
			__( 'Settings', 'wp-booking-system-luca' ),
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
		if ( strpos( $hook, 'wp-booking-system-luca' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wp-booking-system-luca-admin',
			WP_BOOKING_SYSTEM_LUCA_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_BOOKING_SYSTEM_LUCA_VERSION
		);

		wp_enqueue_script(
			'wp-booking-system-luca-admin',
			WP_BOOKING_SYSTEM_LUCA_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			WP_BOOKING_SYSTEM_LUCA_VERSION,
			true
		);

		wp_localize_script(
			'wp-booking-system-luca-admin',
			'wpbslAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp-booking-system-luca-admin' ),
				'currency' => get_option( 'wpbsl_currency', 'CHF' ),
				'i18n'    => array(
					'confirmDelete'  => __( 'Are you sure you want to delete this booking?', 'wp-booking-system-luca' ),
					'confirmCancel'  => __( 'Cancel this booking and email the guest?', 'wp-booking-system-luca' ),
					'genericError'   => __( 'An error occurred. Please try again.', 'wp-booking-system-luca' ),
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
		$bookings = wp_booking_system_luca()->database->get_bookings();
		?>
		<div class="wrap wpbs-admin-wrap">
			<h1><?php esc_html_e( 'Booking Calendar', 'wp-booking-system-luca' ); ?></h1>
			<div id="wpbs-calendar"></div>
		</div>
		<?php
	}

	/**
	 * Render list page.
	 */
	public function render_list_page() {
		$bookings = wp_booking_system_luca()->database->get_bookings();
		?>
		<div class="wrap wpbs-admin-wrap">
			<h1><?php esc_html_e( 'All Bookings', 'wp-booking-system-luca' ); ?></h1>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Guest', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Check-in', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Check-out', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Guests', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Price', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wp-booking-system-luca' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'wp-booking-system-luca' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $bookings ) ) : ?>
						<tr>
							<td colspan="9"><?php esc_html_e( 'No bookings found.', 'wp-booking-system-luca' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $bookings as $booking ) : ?>
							<tr>
								<td><?php echo esc_html( $booking->id ); ?></td>
								<td><?php echo esc_html( $booking->first_name . ' ' . $booking->last_name ); ?></td>
								<td><?php echo esc_html( $booking->email ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></td>
								<td><?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system-luca' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system-luca' ) ); ?></td>
								<td><?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . get_option( 'wpbsl_currency', 'CHF' ) ); ?></td>
								<td>
									<span class="wpbs-status wpbs-status-<?php echo esc_attr( $booking->status ); ?>">
										<?php echo esc_html( ucfirst( $booking->status ) ); ?>
									</span>
								</td>
								<td>
									<a href="#" class="wpbs-view-booking" data-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'View', 'wp-booking-system-luca' ); ?>
									</a> |
									<?php if ( 'confirmed' !== $booking->status && 'cancelled' !== $booking->status ) : ?>
										<a href="#" class="wpbs-set-status" data-id="<?php echo esc_attr( $booking->id ); ?>" data-status="confirmed">
											<?php esc_html_e( 'Confirm', 'wp-booking-system-luca' ); ?>
										</a> |
									<?php endif; ?>
									<?php if ( 'cancelled' !== $booking->status ) : ?>
										<a href="#" class="wpbs-set-status" data-id="<?php echo esc_attr( $booking->id ); ?>" data-status="cancelled">
											<?php esc_html_e( 'Cancel', 'wp-booking-system-luca' ); ?>
										</a> |
									<?php endif; ?>
									<a href="#" class="wpbs-delete-booking" data-id="<?php echo esc_attr( $booking->id ); ?>">
										<?php esc_html_e( 'Delete', 'wp-booking-system-luca' ); ?>
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
		if ( isset( $_POST['wpbsl_save_settings'] ) && check_admin_referer( 'wpbsl_settings' ) ) {
			// Validate and sanitize input.
			$price_adult              = isset( $_POST['wpbsl_price_adult'] ) ? floatval( $_POST['wpbsl_price_adult'] ) : 0;
			$price_kid                = isset( $_POST['wpbsl_price_kid'] ) ? floatval( $_POST['wpbsl_price_kid'] ) : 0;
			$currency                 = isset( $_POST['wpbsl_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['wpbsl_currency'] ) ) : 'CHF';
			$email_from               = isset( $_POST['wpbsl_email_from'] ) ? sanitize_email( wp_unslash( $_POST['wpbsl_email_from'] ) ) : '';
			$email_from_name          = isset( $_POST['wpbsl_email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['wpbsl_email_from_name'] ) ) : '';
			$admin_notification_email = isset( $_POST['wpbsl_admin_notification_email'] ) ? sanitize_email( wp_unslash( $_POST['wpbsl_admin_notification_email'] ) ) : '';
			$chalet_capacity          = isset( $_POST['wpbsl_chalet_capacity'] ) ? absint( $_POST['wpbsl_chalet_capacity'] ) : 10;

			// Booking entry options.
			$min_nights       = isset( $_POST['wpbsl_min_nights'] ) ? max( 1, absint( $_POST['wpbsl_min_nights'] ) ) : 1;
			$max_nights       = isset( $_POST['wpbsl_max_nights'] ) ? absint( $_POST['wpbsl_max_nights'] ) : 0;
			$min_advance_days = isset( $_POST['wpbsl_min_advance_days'] ) ? absint( $_POST['wpbsl_min_advance_days'] ) : 0;
			$max_advance_days = isset( $_POST['wpbsl_max_advance_days'] ) ? absint( $_POST['wpbsl_max_advance_days'] ) : 0;
			$default_adults   = isset( $_POST['wpbsl_default_adults'] ) ? max( 1, absint( $_POST['wpbsl_default_adults'] ) ) : 2;
			$default_kids     = isset( $_POST['wpbsl_default_kids'] ) ? absint( $_POST['wpbsl_default_kids'] ) : 0;
			$require_phone    = isset( $_POST['wpbsl_require_phone'] ) ? 1 : 0;
			$show_notes       = isset( $_POST['wpbsl_show_notes'] ) ? 1 : 0;
			$auto_confirm     = isset( $_POST['wpbsl_auto_confirm'] ) ? 1 : 0;

			// Validate emails.
			if ( ! is_email( $email_from ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid email from address.', 'wp-booking-system-luca' ) . '</p></div>';
			} elseif ( ! empty( $admin_notification_email ) && ! is_email( $admin_notification_email ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid admin notification email address.', 'wp-booking-system-luca' ) . '</p></div>';
			} elseif ( $max_nights > 0 && $max_nights < $min_nights ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Maximum nights cannot be less than minimum nights.', 'wp-booking-system-luca' ) . '</p></div>';
			} elseif ( $max_advance_days > 0 && $max_advance_days < $min_advance_days ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Maximum advance days cannot be less than minimum advance days.', 'wp-booking-system-luca' ) . '</p></div>';
			} elseif ( $default_adults + $default_kids > $chalet_capacity ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Default guests cannot exceed the chalet capacity.', 'wp-booking-system-luca' ) . '</p></div>';
			} else {
				update_option( 'wpbsl_price_adult', $price_adult );
				update_option( 'wpbsl_price_kid', $price_kid );
				update_option( 'wpbsl_currency', $currency );
				update_option( 'wpbsl_email_from', $email_from );
				update_option( 'wpbsl_email_from_name', $email_from_name );
				update_option( 'wpbsl_admin_notification_email', $admin_notification_email );
				update_option( 'wpbsl_chalet_capacity', $chalet_capacity );
				update_option( 'wpbsl_min_nights', $min_nights );
				update_option( 'wpbsl_max_nights', $max_nights );
				update_option( 'wpbsl_min_advance_days', $min_advance_days );
				update_option( 'wpbsl_max_advance_days', $max_advance_days );
				update_option( 'wpbsl_default_adults', $default_adults );
				update_option( 'wpbsl_default_kids', $default_kids );
				update_option( 'wpbsl_require_phone', $require_phone );
				update_option( 'wpbsl_show_notes', $show_notes );
				update_option( 'wpbsl_auto_confirm', $auto_confirm );
				echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'wp-booking-system-luca' ) . '</p></div>';
			}
		}

		$price_adult     = get_option( 'wpbsl_price_adult', 50 );
		$price_kid       = get_option( 'wpbsl_price_kid', 25 );
		$currency        = get_option( 'wpbsl_currency', 'CHF' );
		$email_from      = get_option( 'wpbsl_email_from', get_option( 'admin_email' ) );
		$email_from_name = get_option( 'wpbsl_email_from_name', get_bloginfo( 'name' ) );
		?>
		<div class="wrap wpbs-admin-wrap">
			<h1><?php esc_html_e( 'Booking Settings', 'wp-booking-system-luca' ); ?></h1>
			<form method="post" action="">
				<?php wp_nonce_field( 'wpbsl_settings' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="wpbsl_price_adult"><?php esc_html_e( 'Price per Adult (per night)', 'wp-booking-system-luca' ); ?></label>
						</th>
						<td>
							<input type="number" step="0.01" id="wpbsl_price_adult" name="wpbsl_price_adult" value="<?php echo esc_attr( $price_adult ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbsl_price_kid"><?php esc_html_e( 'Price per Kid (per night)', 'wp-booking-system-luca' ); ?></label>
						</th>
						<td>
							<input type="number" step="0.01" id="wpbsl_price_kid" name="wpbsl_price_kid" value="<?php echo esc_attr( $price_kid ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbsl_currency"><?php esc_html_e( 'Currency', 'wp-booking-system-luca' ); ?></label>
						</th>
						<td>
							<input type="text" id="wpbsl_currency" name="wpbsl_currency" value="<?php echo esc_attr( $currency ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wpbsl_email_from"><?php esc_html_e( 'Email From Address', 'wp-booking-system-luca' ); ?></label>
						</th>
						<td>
							<input type="email" id="wpbsl_email_from" name="wpbsl_email_from" value="<?php echo esc_attr( $email_from ); ?>" class="regular-text" />
						</td>
					</tr>
				<tr>
					<th scope="row">
						<label for="wpbsl_email_from_name"><?php esc_html_e( 'Email From Name', 'wp-booking-system-luca' ); ?></label>
					</th>
					<td>
						<input type="text" id="wpbsl_email_from_name" name="wpbsl_email_from_name" value="<?php echo esc_attr( $email_from_name ); ?>" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpbsl_admin_notification_email"><?php esc_html_e( 'Admin Notification Email', 'wp-booking-system-luca' ); ?></label>
					</th>
					<td>
						<input type="email" id="wpbsl_admin_notification_email" name="wpbsl_admin_notification_email" value="<?php echo esc_attr( get_option( 'wpbsl_admin_notification_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Email address to receive notifications when new bookings are made.', 'wp-booking-system-luca' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wpbsl_chalet_capacity"><?php esc_html_e( 'Chalet Maximum Capacity', 'wp-booking-system-luca' ); ?></label>
					</th>
					<td>
						<input type="number" id="wpbsl_chalet_capacity" name="wpbsl_chalet_capacity" value="<?php echo esc_attr( get_option( 'wpbsl_chalet_capacity', 10 ) ); ?>" min="1" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Maximum number of guests (adults + kids) that can be accommodated.', 'wp-booking-system-luca' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Booking Rules', 'wp-booking-system-luca' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="wpbsl_min_nights"><?php esc_html_e( 'Minimum Stay (nights)', 'wp-booking-system-luca' ); ?></label></th>
					<td><input type="number" id="wpbsl_min_nights" name="wpbsl_min_nights" value="<?php echo esc_attr( get_option( 'wpbsl_min_nights', 1 ) ); ?>" min="1" class="small-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wpbsl_max_nights"><?php esc_html_e( 'Maximum Stay (nights)', 'wp-booking-system-luca' ); ?></label></th>
					<td>
						<input type="number" id="wpbsl_max_nights" name="wpbsl_max_nights" value="<?php echo esc_attr( get_option( 'wpbsl_max_nights', 0 ) ); ?>" min="0" class="small-text" />
						<p class="description"><?php esc_html_e( 'Set to 0 for no maximum.', 'wp-booking-system-luca' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpbsl_min_advance_days"><?php esc_html_e( 'Minimum Advance Notice (days)', 'wp-booking-system-luca' ); ?></label></th>
					<td>
						<input type="number" id="wpbsl_min_advance_days" name="wpbsl_min_advance_days" value="<?php echo esc_attr( get_option( 'wpbsl_min_advance_days', 0 ) ); ?>" min="0" class="small-text" />
						<p class="description"><?php esc_html_e( 'Earliest a guest may check in, in days from today. 0 = today allowed.', 'wp-booking-system-luca' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpbsl_max_advance_days"><?php esc_html_e( 'Booking Window (days ahead)', 'wp-booking-system-luca' ); ?></label></th>
					<td>
						<input type="number" id="wpbsl_max_advance_days" name="wpbsl_max_advance_days" value="<?php echo esc_attr( get_option( 'wpbsl_max_advance_days', 0 ) ); ?>" min="0" class="small-text" />
						<p class="description"><?php esc_html_e( 'How far ahead guests may book, in days. 0 = no limit.', 'wp-booking-system-luca' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'New Bookings', 'wp-booking-system-luca' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="wpbsl_auto_confirm" value="1" <?php checked( 1, (int) get_option( 'wpbsl_auto_confirm', 0 ) ); ?> />
							<?php esc_html_e( 'Confirm new bookings automatically (skip the pending step).', 'wp-booking-system-luca' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Booking Form', 'wp-booking-system-luca' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="wpbsl_default_adults"><?php esc_html_e( 'Default Adults', 'wp-booking-system-luca' ); ?></label></th>
					<td><input type="number" id="wpbsl_default_adults" name="wpbsl_default_adults" value="<?php echo esc_attr( get_option( 'wpbsl_default_adults', 2 ) ); ?>" min="1" class="small-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="wpbsl_default_kids"><?php esc_html_e( 'Default Kids', 'wp-booking-system-luca' ); ?></label></th>
					<td><input type="number" id="wpbsl_default_kids" name="wpbsl_default_kids" value="<?php echo esc_attr( get_option( 'wpbsl_default_kids', 0 ) ); ?>" min="0" class="small-text" /></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Phone Field', 'wp-booking-system-luca' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="wpbsl_require_phone" value="1" <?php checked( 1, (int) get_option( 'wpbsl_require_phone', 0 ) ); ?> />
							<?php esc_html_e( 'Require the guest to provide a phone number.', 'wp-booking-system-luca' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Notes Field', 'wp-booking-system-luca' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="wpbsl_show_notes" value="1" <?php checked( 1, (int) get_option( 'wpbsl_show_notes', 1 ) ); ?> />
							<?php esc_html_e( 'Show the optional notes field on the booking form.', 'wp-booking-system-luca' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save Settings', 'wp-booking-system-luca' ), 'primary', 'wpbsl_save_settings' ); ?>
		</form>
	</div>
	<?php
	}
}

