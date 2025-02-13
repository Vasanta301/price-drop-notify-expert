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

		function autofillDetails() {
			if ($('#pcne-autofill-details').is(':checked')) {
				var _this = $(this);
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
							_this.closest('#name').val(response.data.name);
							_this.closest('#email').val(response.data.email);
							_this.closest('#phone').val(response.data.phone);
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

		var variableProducts = $(document).find('#pcne-product-popup-form').data('variable-data');

		// Handle select change
		$('.attribute-select').on('change', function () {
			var selectedAttributes = {};

			// Collect selected values
			$('.attribute-select').each(function () {
				var attrName = $(this).data('attribute');
				var selectedValue = $(this).val();
				if (selectedValue) {
					selectedAttributes[attrName] = selectedValue;
				}
			});

			// Filter available variations based on selected attributes
			var availableVariations = [];
			$.each(variableProducts, function (variationId, variation) {
				var match = true;
				$.each(selectedAttributes, function (attrName, attrValue) {
					if (variation.attributes[attrName] !== attrValue) {
						match = false;
					}
				});
				if (match) {
					availableVariations.push(variationId);
				}
			});
		});


		/** 
		 * Firebase Configuration
		 */

		// With this initialization flow
		const initializeFirebase = async () => {
			try {
				if (!firebase.apps.length) {
					await firebase.initializeApp({
						apiKey: "AIzaSyDNCTIga_URLSJWzHlFXGArE8CVwGCVFk0",
						authDomain: "price-drop-45bc4.firebaseapp.com",
						projectId: "price-drop-45bc4",
						storageBucket: "price-drop-45bc4.firebasestorage.app",
						messagingSenderId: "236199324060",
						appId: "1:236199324060:web:fcb7987b5d0ed6226b0059"
					});
				}

			} catch (error) {
				console.error('Firebase initialization error:', error);
				return null;
			}
		};
		// Handle form submission
		$('#pcne-product-popup-form').submit(async function (e) {
			e.preventDefault();
			const $this = $(this);
			const formData = {
				name: $this.find('#name').val(),
				email: $this.find('#email').val(),
				phone: $this.find('#phone').val(),
				product_id: $this.data('product-id'),
				preferred_method_of_notify: $this.find('#preferred_method_of_notify').val(),
				selected_attributes: {}
			};

			// Collect selected attributes
			$('.attribute-select').each(function () {
				const attrName = $(this).data('attribute');
				const selectedValue = $(this).val();
				const selectedLabel = $(this).find('option:selected').text();
				if (selectedValue) {
					formData.selected_attributes[attrName] = {
						id: selectedValue,
						label: selectedLabel
					};
				}
			});

			navigator.serviceWorker.register('/firebase-messaging-sw.js')
				.then(async (registration) => {
					console.log('Service Worker registered:', registration);

					// Initialize Firebase if not already
					if (!firebase.apps.length) {
						console.log('Firebase not initialized, initializing now...');
						firebase.initializeApp({
							apiKey: "AIzaSyDNCTIga_URLSJWzHlFXGArE8CVwGCVFk0",
							authDomain: "price-drop-45bc4.firebaseapp.com",
							projectId: "price-drop-45bc4",
							storageBucket: "price-drop-45bc4.appspot.com",
							messagingSenderId: "236199324060",
							appId: "1:236199324060:web:fcb7987b5d0ed6226b0059"
						});
					}

					// Get Firebase Messaging
					const messaging = firebase.messaging();

					// Request notification permission
					Notification.requestPermission().then(async function (permission) {
						if (permission === 'granted') {
							try {
								const token = await messaging.getToken({
									vapidKey: 'BJbUQBPEyPc6i52Af56iUJffOVjvz8RK7KYZNihRpM9r_R5kZkt-cLPzfJxjq75kmmAsXeZM7wbMGqbOnlXi1Yc',
									serviceWorkerRegistration: registration
								});

								console.log('FCM Token:', token);

								if (token) {
									$.ajax({
										type: 'POST',
										url: pcne_ajax_object.ajax_url,
										data: {
											action: 'handle_form_submission',
											formData: formData,
											token: token,
											pcne_nonce: pcne_ajax_object.pcne_nonce
										},
										success: function (response) {
											$('#pcne-form-response').html(`<p style="color: ${response.status === 'success' ? 'green' : 'red'};">${response.message}</p>`);
											if (response.status === 'success') {
												$('#pcne-product-popup-form')[0].reset();
											}
										}
									});
								}

							} catch (error) {
								console.error('Error getting token:', error);
							}
						}
					});
				})
				.catch((error) => console.error('Service Worker registration failed:', error));
		});

	});

})(jQuery);
