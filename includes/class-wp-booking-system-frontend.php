<?php
/**
 * Frontend class for booking form
 *
 * @package WP_Booking_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Frontend Class
 */
class WP_Booking_System_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_shortcode( 'wp_booking_form', array( $this, 'render_booking_form' ) );
		add_shortcode( 'wp_booking_manage', array( $this, 'render_booking_manage' ) );
		add_shortcode( 'wp_booking_calendar', array( $this, 'render_booking_calendar' ) );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'wp-booking-system-frontend',
			WP_BOOKING_SYSTEM_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			WP_BOOKING_SYSTEM_VERSION
		);

		wp_enqueue_script(
			'wp-booking-system-frontend',
			WP_BOOKING_SYSTEM_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			WP_BOOKING_SYSTEM_VERSION,
			true
		);

		wp_localize_script(
			'wp-booking-system-frontend',
			'wpbsFrontend',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wp-booking-system-frontend' ),
				'i18n'    => array(
					'checking'      => __( 'Checking availability...', 'wp-booking-system' ),
					'available'     => __( 'Available', 'wp-booking-system' ),
					'unavailable'   => __( 'Unavailable', 'wp-booking-system' ),
					'selectDates'   => __( 'Please select check-in and check-out dates', 'wp-booking-system' ),
					'invalidDates'  => __( 'Check-out date must be after check-in date', 'wp-booking-system' ),
					'calculating'   => __( 'Calculating price...', 'wp-booking-system' ),
				),
			)
		);

		// Flatpickr for date picker.
		wp_enqueue_style(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
			array(),
			'4.6.13'
		);

		wp_enqueue_script(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js',
			array(),
			'4.6.13',
			true
		);

		// FullCalendar for widget calendar.
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
	 * Render booking form shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_booking_form( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'title' => __( 'Book Your Stay', 'wp-booking-system' ),
			),
			$atts,
			'wp_booking_form'
		);

		ob_start();
		?>
		<div class="wpbs-booking-form-wrapper">
			<h2 class="wpbs-form-title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<form id="wpbs-booking-form" class="wpbs-booking-form">
				<div class="wpbs-form-row">
					<div class="wpbs-form-group">
						<label for="wpbs-check-in"><?php esc_html_e( 'Check-in', 'wp-booking-system' ); ?></label>
						<input type="text" id="wpbs-check-in" name="check_in" class="wpbs-date-input" required readonly />
					</div>
					<div class="wpbs-form-group">
						<label for="wpbs-check-out"><?php esc_html_e( 'Check-out', 'wp-booking-system' ); ?></label>
						<input type="text" id="wpbs-check-out" name="check_out" class="wpbs-date-input" required readonly />
					</div>
				</div>

				<div class="wpbs-form-row">
					<div class="wpbs-form-group">
						<label for="wpbs-adults"><?php esc_html_e( 'Adults', 'wp-booking-system' ); ?></label>
						<input type="number" id="wpbs-adults" name="adults" min="1" value="2" required />
					</div>
					<div class="wpbs-form-group">
						<label for="wpbs-kids"><?php esc_html_e( 'Kids', 'wp-booking-system' ); ?></label>
						<input type="number" id="wpbs-kids" name="kids" min="0" value="0" required />
					</div>
				</div>

				<div class="wpbs-form-row">
					<div class="wpbs-form-group wpbs-form-group-full">
						<label for="wpbs-first-name"><?php esc_html_e( 'First Name', 'wp-booking-system' ); ?></label>
						<input type="text" id="wpbs-first-name" name="first_name" required />
					</div>
				</div>

				<div class="wpbs-form-row">
					<div class="wpbs-form-group wpbs-form-group-full">
						<label for="wpbs-last-name"><?php esc_html_e( 'Last Name', 'wp-booking-system' ); ?></label>
						<input type="text" id="wpbs-last-name" name="last_name" required />
					</div>
				</div>

				<div class="wpbs-form-row">
					<div class="wpbs-form-group wpbs-form-group-full">
						<label for="wpbs-email"><?php esc_html_e( 'Email', 'wp-booking-system' ); ?></label>
						<input type="email" id="wpbs-email" name="email" required />
					</div>
				</div>

				<div class="wpbs-form-row">
					<div class="wpbs-form-group wpbs-form-group-full">
						<label for="wpbs-phone"><?php esc_html_e( 'Phone', 'wp-booking-system' ); ?></label>
						<input type="tel" id="wpbs-phone" name="phone" />
					</div>
				</div>

				<div class="wpbs-form-row">
					<div class="wpbs-form-group wpbs-form-group-full">
						<label for="wpbs-notes"><?php esc_html_e( 'Notes', 'wp-booking-system' ); ?></label>
						<textarea id="wpbs-notes" name="notes" rows="4"></textarea>
					</div>
				</div>

				<div class="wpbs-price-summary" id="wpbs-price-summary" style="display: none;">
					<div class="wpbs-price-row">
						<span class="wpbs-price-label"><?php esc_html_e( 'Total Price:', 'wp-booking-system' ); ?></span>
						<span class="wpbs-price-value" id="wpbs-total-price"></span>
					</div>
				</div>

				<div class="wpbs-form-messages" id="wpbs-form-messages"></div>

				<button type="submit" class="wpbs-submit-button">
					<?php esc_html_e( 'Book Now', 'wp-booking-system' ); ?>
				</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render booking management page.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_booking_manage( $atts = array() ) {
		$token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';

		if ( empty( $token ) ) {
			return '<p>' . esc_html__( 'Invalid booking token.', 'wp-booking-system' ) . '</p>';
		}

		$booking = wp_booking_system()->database->get_booking_by_token( $token );

		if ( ! $booking ) {
			return '<p>' . esc_html__( 'Booking not found.', 'wp-booking-system' ) . '</p>';
		}

		ob_start();
		?>
		<div class="wpbs-booking-manage-wrapper">
			<h2><?php esc_html_e( 'Manage Your Booking', 'wp-booking-system' ); ?></h2>
			<div class="wpbs-booking-details">
				<p><strong><?php esc_html_e( 'Guest:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->first_name . ' ' . $booking->last_name ); ?></p>
				<p><strong><?php esc_html_e( 'Check-in:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_in ) ) ); ?></p>
				<p><strong><?php esc_html_e( 'Check-out:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $booking->check_out ) ) ); ?></p>
				<p><strong><?php esc_html_e( 'Guests:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( $booking->adults . ' ' . __( 'adults', 'wp-booking-system' ) . ', ' . $booking->kids . ' ' . __( 'kids', 'wp-booking-system' ) ); ?></p>
				<p><strong><?php esc_html_e( 'Total Price:', 'wp-booking-system' ); ?></strong> <?php echo esc_html( number_format( $booking->total_price, 2 ) . ' ' . get_option( 'wpbs_currency', 'CHF' ) ); ?></p>
				<p><strong><?php esc_html_e( 'Status:', 'wp-booking-system' ); ?></strong> <span class="wpbs-status wpbs-status-<?php echo esc_attr( $booking->status ); ?>"><?php echo esc_html( ucfirst( $booking->status ) ); ?></span></p>
			</div>

			<?php if ( $booking->status !== 'cancelled' ) : ?>
				<div class="wpbs-booking-actions">
					<button type="button" class="wpbs-cancel-booking" data-token="<?php echo esc_attr( $token ); ?>">
						<?php esc_html_e( 'Cancel Booking', 'wp-booking-system' ); ?>
					</button>
				</div>
			<?php endif; ?>

			<div class="wpbs-form-messages" id="wpbs-manage-messages"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render booking calendar shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_booking_calendar( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'title' => __( 'Booking Calendar', 'wp-booking-system' ),
			),
			$atts,
			'wp_booking_calendar'
		);

		// Get unavailable dates.
		$bookings = wp_booking_system()->database->get_bookings(
			array(
				'status' => '',
			)
		);

		$unavailable_dates = array();
		foreach ( $bookings as $booking ) {
			if ( 'cancelled' === $booking->status ) {
				continue;
			}

			$check_in  = new DateTime( $booking->check_in );
			$check_out = new DateTime( $booking->check_out );

			$current = clone $check_in;
			while ( $current < $check_out ) {
				$unavailable_dates[] = $current->format( 'Y-m-d' );
				$current->modify( '+1 day' );
			}
		}
		$unavailable_dates = array_unique( $unavailable_dates );

		ob_start();
		?>
		<div class="wpbs-calendar-shortcode-wrapper">
			<?php if ( ! empty( $atts['title'] ) ) : ?>
				<h3 class="wpbs-calendar-title"><?php echo esc_html( $atts['title'] ); ?></h3>
			<?php endif; ?>
			<div id="wpbs-calendar-shortcode" class="wpbs-calendar-shortcode"></div>
			<div class="wpbs-calendar-legend">
				<span class="wpbs-legend-item">
					<span class="wpbs-legend-available"></span>
					<?php esc_html_e( 'Available', 'wp-booking-system' ); ?>
				</span>
				<span class="wpbs-legend-item">
					<span class="wpbs-legend-booked"></span>
					<?php esc_html_e( 'Booked', 'wp-booking-system' ); ?>
				</span>
			</div>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			if (typeof FullCalendar !== 'undefined') {
				const calendarEl = document.getElementById('wpbs-calendar-shortcode');
				if (calendarEl) {
					const calendar = new FullCalendar.Calendar(calendarEl, {
						initialView: 'dayGridMonth',
						headerToolbar: {
							left: 'prev,next',
							center: 'title',
							right: ''
						},
						height: 'auto',
						events: function(fetchInfo, successCallback, failureCallback) {
							$.ajax({
								url: wpbsFrontend.ajaxUrl,
								type: 'GET',
								data: {
									action: 'wpbs_get_calendar_availability',
									nonce: wpbsFrontend.nonce,
									start: fetchInfo.startStr,
									end: fetchInfo.endStr
								},
								success: function(response) {
									if (response.success) {
										successCallback(response.data);
									} else {
										failureCallback();
									}
								},
								error: function() {
									failureCallback();
								}
							});
						},
						dayCellClassNames: function(arg) {
							const dateStr = arg.date.toISOString().split('T')[0];
							const unavailableDates = <?php echo wp_json_encode( $unavailable_dates ); ?>;
							if (unavailableDates.includes(dateStr)) {
								return ['wpbs-unavailable-date'];
							}
							return [];
						},
						dateClick: function(info) {
							// Set the date in the booking form if it exists.
							const checkInInput = document.getElementById('wpbs-check-in');
							const checkOutInput = document.getElementById('wpbs-check-out');
							
							if (checkInInput && !checkInInput.value) {
								checkInInput.value = info.dateStr;
								if (typeof flatpickr !== 'undefined') {
									const fp = flatpickr(checkInInput);
									if (fp) fp.setDate(info.dateStr);
								}
							} else if (checkOutInput && !checkOutInput.value && checkInInput && checkInInput.value) {
								const checkInDate = new Date(checkInInput.value);
								const clickedDate = new Date(info.dateStr);
								if (clickedDate > checkInDate) {
									checkOutInput.value = info.dateStr;
									if (typeof flatpickr !== 'undefined') {
										const fp = flatpickr(checkOutInput);
										if (fp) fp.setDate(info.dateStr);
									}
									// Trigger change event to calculate price.
									$(checkOutInput).trigger('change');
								}
							}
						},
						eventDisplay: 'background',
						eventBackgroundColor: '#8B0000',
						eventBorderColor: '#8B0000'
					});
					calendar.render();
				}
			}
		});
		</script>
		<?php
		return ob_get_clean();
	}
}

