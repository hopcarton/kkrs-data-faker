/**
 * KKSR Data Faker - Admin JavaScript
 *
 * Handles AJAX requests for generating data.
 *
 * @since 3.0.0
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Generate All Data button handler.
		$('#kksr-generate-all-btn').on('click', function() {
			// Show confirmation dialog.
			if (!confirm('Bạn có chắc muốn tạo dữ liệu ratings cho TẤT CẢ bài viết?\n\nDữ liệu sẽ được LƯU THẬT vào database.')) {
				return;
			}

			var $btn = $(this);
			var $status = $('#kksr-generate-status');

			// Disable button and show loading.
			$btn.prop('disabled', true);
			$status.html('<span style="color: #0073aa;">Đang xử lý...</span>');

			// Send AJAX request.
			$.ajax({
				url: kksrFakerAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'kksr_generate_all_data',
					nonce: kksrFakerAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						$status.html('<div style="color: #46b450; margin-top: 10px;">' + response.data.message + '</div>');
					} else {
						$status.html('<span style="color: #dc3232;">' + (response.data.message || 'Có lỗi xảy ra.') + '</span>');
					}
				},
				error: function() {
					$status.html('<span style="color: #dc3232;">Có lỗi xảy ra khi kết nối server.</span>');
				},
				complete: function() {
					$btn.prop('disabled', false);
				}
			});
		});
	});
})(jQuery);

