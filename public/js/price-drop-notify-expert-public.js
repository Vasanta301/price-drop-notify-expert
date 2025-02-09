(function ($) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	document.addEventListener("DOMContentLoaded", function () {
		let popup = document.getElementById("pcne-form-popup");
		let openBtn = document.getElementById("pcne-open-form-btn");
		let closeBtn = document.getElementById("pcne-close-form-btn");

		openBtn.addEventListener("click", function () {
			popup.style.display = "flex";
		});

		closeBtn.addEventListener("click", function () {
			popup.style.display = "none";
		});

		window.addEventListener("click", function (event) {
			if (event.target === popup) {
				popup.style.display = "none";
			}
		});

		$('#pcne-product-popup-form').on('submit', function (e) {
			e.preventDefault();

			var formData = {
				name: $(this).find('#name').val(),
				email: $(this).find('#email').val(),
				phone: $(this).find('#phone').val(),
				product_id: $('#pcne-product-popup-form').data('product-id'),
			};

			$.ajax({
				type: 'POST',
				url: pcne_ajax_object.ajax_url,
				data: {
					action: 'handle_form_submission',
					formData: formData,
					pcne_nonce: pcne_ajax_object.pcne_nonce // Include nonce here
				},
				success: function (response) {
					if (response.status === 'success') {
						$('#pcne-form-response').html('<p style="color: green;">' + response.message + '</p>');
						$('#pcne-product-popup-form')[0].reset();
					} else {
						$('#pcne-form-response').html('<p style="color: red;">' + response.message + '</p>');
					}
				}
			});
		});

	});

	jQuery(document).ready(function ($) {
		function autofillDetails() {
			if ($('#pcne-autofill-details').is(':checked')) {
				$('#loader').show(); // Show loader
				$.ajax({
					type: 'POST',
					url: pcne_ajax_object.ajax_url,
					data: {
						action: 'get_user_info',
						pcne_nonce: pcne_ajax_object.pcne_nonce
					},
					success: function (response) {
						$('#loader').hide();
						if (response.success) {
							$(this).closest('#name').val(response.data.name);
							$(this).closest('#email').val(response.data.email);
							$(this).closest('#phone').val(response.data.phone);
						} else {
							$('#pcne-form-response').html('<p style="color: red;">Details not found. Please add your details manually.</p>');
						}
					},
					error: function (e) {
						$('#loader').hide();
						$('#pcne-form-response').html('Something went wrong. ' + e);
					}
				});
			} else {
				$('#name, #email, #phone').val('');
			}
		}

		$('#pcne-open-form-btn').on('click', autofillDetails);

		$('#pcne-autofill-details').on('change', autofillDetails);
	});

})(jQuery);
