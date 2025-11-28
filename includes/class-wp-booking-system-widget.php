<?php
/**
 * Frontend Calendar Widget Class
 *
 * @package WP_Booking_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Booking_System_Widget Class
 */
class WP_Booking_System_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'wp_booking_system_widget',
			__( 'Booking Calendar', 'wp-booking-system' ),
			array(
				'description' => __( 'Display a monthly calendar showing booking availability and allowing date selection.', 'wp-booking-system' ),
			)
		);
	}

	/**
	 * Output the widget content.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Booking Calendar', 'wp-booking-system' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		// Get unavailable dates.
		$unavailable_dates = $this->get_unavailable_dates();

		?>
		<div class="wpbs-widget-calendar-wrapper">
			<div id="wpbs-widget-calendar-<?php echo esc_attr( $this->id ); ?>" class="wpbs-widget-calendar"></div>
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
				const calendarEl = document.getElementById('wpbs-widget-calendar-<?php echo esc_js( $this->id ); ?>');
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

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Output the settings form.
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Booking Calendar', 'wp-booking-system' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'wp-booking-system' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Save widget settings.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}

	/**
	 * Get unavailable dates for the calendar.
	 *
	 * @return array Array of date strings (Y-m-d format).
	 */
	private function get_unavailable_dates() {
		$bookings = wp_booking_system()->database->get_bookings(
			array(
				'status' => '',
			)
		);

		$unavailable = array();

		foreach ( $bookings as $booking ) {
			if ( 'cancelled' === $booking->status ) {
				continue;
			}

			$check_in  = new DateTime( $booking->check_in );
			$check_out = new DateTime( $booking->check_out );

			// Add all dates between check-in and check-out (excluding check-out).
			$current = clone $check_in;
			while ( $current < $check_out ) {
				$unavailable[] = $current->format( 'Y-m-d' );
				$current->modify( '+1 day' );
			}
		}

		return array_unique( $unavailable );
	}
}

