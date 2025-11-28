/**
 * Admin JavaScript for WP Booking System
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize calendar if on calendar page
		if ($('#wpbs-calendar').length) {
			initCalendar();
		}

		// Handle booking deletion
		$('.wpbs-delete-booking').on('click', handleDeleteBooking);

		// Handle booking view
		$('.wpbs-view-booking').on('click', handleViewBooking);
	});

	/**
	 * Initialize FullCalendar
	 */
	function initCalendar() {
		// Wait for FullCalendar to be available
		if (typeof FullCalendar === 'undefined') {
			setTimeout(initCalendar, 100);
			return;
		}

		const calendarEl = document.getElementById('wpbs-calendar');
		if (!calendarEl) {
			return;
		}

		const calendar = new FullCalendar.Calendar(calendarEl, {
			initialView: 'dayGridMonth',
			headerToolbar: {
				left: 'prev,next today',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,timeGridDay'
			},
			events: function(fetchInfo, successCallback, failureCallback) {
				$.ajax({
					url: wpbsAdmin.ajaxUrl,
					type: 'GET',
					data: {
						action: 'wpbs_get_bookings',
						nonce: wpbsAdmin.nonce,
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
			eventClick: function(info) {
				// Show booking details
				viewBookingDetails(info.event.id);
			},
			eventDisplay: 'block',
			height: 'auto'
		});

		calendar.render();
	}

	/**
	 * View booking details
	 */
	function viewBookingDetails(bookingId) {
		$.ajax({
			url: wpbsAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpbs_get_booking',
				nonce: wpbsAdmin.nonce,
				id: bookingId
			},
			success: function(response) {
				if (response.success) {
					const booking = response.data;
					const message = `
						<strong>Guest:</strong> ${booking.first_name} ${booking.last_name}<br>
						<strong>Email:</strong> ${booking.email}<br>
						<strong>Phone:</strong> ${booking.phone || 'N/A'}<br>
						<strong>Check-in:</strong> ${booking.check_in}<br>
						<strong>Check-out:</strong> ${booking.check_out}<br>
						<strong>Guests:</strong> ${booking.adults} adults, ${booking.kids} kids<br>
						<strong>Price:</strong> ${booking.total_price} ${wpbsAdmin.currency || 'CHF'}<br>
						<strong>Status:</strong> ${booking.status}<br>
						${booking.notes ? '<strong>Notes:</strong> ' + booking.notes + '<br>' : ''}
					`;
					alert(message);
				}
			}
		});
	}

	/**
	 * Handle booking deletion
	 */
	function handleDeleteBooking(e) {
		e.preventDefault();

		if (!confirm(wpbsAdmin.i18n.confirmDelete)) {
			return;
		}

		const link = $(this);
		const bookingId = link.data('id');
		const row = link.closest('tr');

		$.ajax({
			url: wpbsAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpbs_delete_booking',
				nonce: wpbsAdmin.nonce,
				id: bookingId
			},
			success: function(response) {
				if (response.success) {
					row.fadeOut(300, function() {
						$(this).remove();
					});
				} else {
					alert(response.data.message || 'Failed to delete booking.');
				}
			},
			error: function() {
				alert('An error occurred. Please try again.');
			}
		});
	}

	/**
	 * Handle booking view
	 */
	function handleViewBooking(e) {
		e.preventDefault();
		const bookingId = $(this).data('id');
		viewBookingDetails(bookingId);
	}

})(jQuery);

