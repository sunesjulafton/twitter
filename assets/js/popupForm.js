$(function() {
	$(document).on('click', '.addTweetBtn', function() {
		$('.status').removeClass().addClass('status-removed');
		$('.hash-box').removeClass().addClass('hash-removed');
		$('#count').attr('id', 'count-removed');

		$.post('http://localhost/twitter/core/ajax/tweetForm.php', function(data) {
			$('.popupTweet').html(data);

			$('.closeTweetPopup').click(function() {
				$('.popup-tweet-wrap').hide();
				$('.status-removed').removeClass().addClass('status');
				$('.hash-removed').removeClass().addClass('hash-box');
				$('#count-removed').attr('id', 'count');
			});

		});
	});
});