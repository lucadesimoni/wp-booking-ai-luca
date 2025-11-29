/**
 * Frontend JavaScript for WP Booking System
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Initialize date picker
		initDatePicker();
		
		// Handle form submission
		$('#wpbs-booking-form').on('submit', handleFormSubmit);
		
		// Handle date changes
		$('#wpbs-check-in, #wpbs-check-out').on('change', handleDateChange);
		
		// Handle guest count changes
		$('#wpbs-adults, #wpbs-kids').on('change', calculatePrice);
		
		// Handle booking cancellation
		$('.wpbs-cancel-booking').on('click', handleCancelBooking);
	});

	/**
	 * Initialize Flatpickr date picker
	 */
	function initDatePicker() {
		if (typeof flatpickr === 'undefined') {
			return;
		}

		const checkInInput = document.getElementById('wpbs-check-in');
		const checkOutInput = document.getElementById('wpbs-check-out');

		if (!checkInInput || !checkOutInput) {
			return;
		}

		// Get unavailable dates from server
		const unavailableDates = getUnavailableDates();

		const checkInPicker = flatpickr(checkInInput, {
			minDate: 'today',
			dateFormat: 'Y-m-d',
			disable: unavailableDates,
			onChange: function(selectedDates, dateStr) {
				if (selectedDates.length) {
					checkOutPicker.set('minDate', dateStr);
					handleDateChange();
				}
			}
		});

		const checkOutPicker = flatpickr(checkOutInput, {
			minDate: 'today',
			dateFormat: 'Y-m-d',
			disable: unavailableDates,
			onChange: function() {
				handleDateChange();
			}
		});
	}

	/**
	 * Get unavailable dates (simplified - in production, fetch from server)
	 */
	function getUnavailableDates() {
		// This would be populated from server-side data
		return [];
	}

	/**
	 * Handle date change
	 */
	function handleDateChange() {
		const checkIn = $('#wpbs-check-in').val();
		const checkOut = $('#wpbs-check-out').val();

		if (checkIn && checkOut) {
			// Validate dates
			if (new Date(checkOut) <= new Date(checkIn)) {
				showMessage('error', wpbsFrontend.i18n.invalidDates);
				$('#wpbs-price-summary').hide();
				return;
			}

			// Check availability
			checkAvailability(checkIn, checkOut);
			
			// Calculate price
			calculatePrice();
		} else {
			$('#wpbs-price-summary').hide();
		}
	}

	/**
	 * Check availability
	 */
	function checkAvailability(checkIn, checkOut) {
		$.ajax({
			url: wpbsFrontend.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpbs_check_availability',
				nonce: wpbsFrontend.nonce,
				check_in: checkIn,
				check_out: checkOut
			},
			success: function(response) {
				if (response.success) {
					if (!response.data.available) {
						showMessage('error', wpbsFrontend.i18n.unavailable);
					}
				}
			}
		});
	}

	/**
	 * Calculate price
	 */
	function calculatePrice() {
		const checkIn = $('#wpbs-check-in').val();
		const checkOut = $('#wpbs-check-out').val();
		const adults = parseInt($('#wpbs-adults').val()) || 1;
		const kids = parseInt($('#wpbs-kids').val()) || 0;

		if (!checkIn || !checkOut) {
			$('#wpbs-price-summary').hide();
			return;
		}

		if (new Date(checkOut) <= new Date(checkIn)) {
			$('#wpbs-price-summary').hide();
			return;
		}

		$.ajax({
			url: wpbsFrontend.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpbs_calculate_price',
				nonce: wpbsFrontend.nonce,
				check_in: checkIn,
				check_out: checkOut,
				adults: adults,
				kids: kids
			},
			beforeSend: function() {
				$('#wpbs-total-price').text(wpbsFrontend.i18n.calculating);
				$('#wpbs-price-summary').show();
			},
			success: function(response) {
				if (response.success) {
					$('#wpbs-total-price').text(response.data.formatted);
					$('#wpbs-price-summary').show();
				} else {
					showMessage('error', response.data.message || 'Unable to calculate price.');
					$('#wpbs-price-summary').hide();
				}
			},
			error: function() {
				showMessage('error', 'An error occurred while calculating the price.');
				$('#wpbs-price-summary').hide();
			}
		});
	}

	/**
	 * Handle form submission
	 */
	function handleFormSubmit(e) {
		e.preventDefault();

		const form = $(this);
		const submitButton = form.find('.wpbs-submit-button');
		const formData = form.serialize();

		// Store original text if not already stored
		if (!submitButton.data('original-text')) {
			submitButton.data('original-text', submitButton.text());
		}

		submitButton.prop('disabled', true).text(wpbsFrontend.i18n.submitting || 'Submitting...');

		$.ajax({
			url: wpbsFrontend.ajaxUrl,
			type: 'POST',
			data: formData + '&action=wpbs_submit_booking&nonce=' + wpbsFrontend.nonce,
			success: function(response) {
				if (response.success) {
					showMessage('success', response.data.message);
					form[0].reset();
					$('#wpbs-price-summary').hide();
					
					// Reset date pickers
					if (typeof flatpickr !== 'undefined') {
						const checkInPicker = flatpickr('#wpbs-check-in');
						const checkOutPicker = flatpickr('#wpbs-check-out');
						if (checkInPicker) checkInPicker.clear();
						if (checkOutPicker) checkOutPicker.clear();
					}
				} else {
					showMessage('error', response.data.message || 'An error occurred. Please try again.');
				}
			},
			error: function() {
				showMessage('error', 'An error occurred. Please try again.');
			},
			complete: function() {
				submitButton.prop('disabled', false);
				const originalText = submitButton.data('original-text') || 'Book Now';
				submitButton.text(originalText);
			}
		});
	}

	/**
	 * Handle booking cancellation
	 */
	function handleCancelBooking(e) {
		e.preventDefault();

		if (!confirm('Are you sure you want to cancel this booking?')) {
			return;
		}

		const button = $(this);
		const token = button.data('token');

		button.prop('disabled', true);

		$.ajax({
			url: wpbsFrontend.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpbs_cancel_booking',
				nonce: wpbsFrontend.nonce,
				token: token
			},
			success: function(response) {
				if (response.success) {
					showMessage('success', response.data.message, '#wpbs-manage-messages');
					button.closest('.wpbs-booking-actions').fadeOut();
					$('.wpbs-status').removeClass('wpbs-status-confirmed wpbs-status-pending').addClass('wpbs-status-cancelled').text('Cancelled');
				} else {
					showMessage('error', response.data.message || 'Failed to cancel booking.', '#wpbs-manage-messages');
				}
			},
			error: function() {
				showMessage('error', 'An error occurred. Please try again.', '#wpbs-manage-messages');
			},
			complete: function() {
				button.prop('disabled', false);
			}
		});
	}

	/**
	 * Show message
	 */
	function showMessage(type, message, selector) {
		selector = selector || '#wpbs-form-messages';
		const messageEl = $(selector);
		messageEl.removeClass('wpbs-success wpbs-error').addClass('wpbs-' + type).text(message).show();
		
		// Scroll to message
		$('html, body').animate({
			scrollTop: messageEl.offset().top - 100
		}, 300);
	}

})(jQuery);

