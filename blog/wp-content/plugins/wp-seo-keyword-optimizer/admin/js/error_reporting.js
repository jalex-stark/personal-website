jQuery(document).ready(function($)
{
	$('#wsko_clear_log').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		if (confirm(window.wsko_text('clear_log_confirm')))
		{
			$this.find('i').show();
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					nonce: $this.data('nonce'),
					action : 'wsko_delete_log_reports',
				},
				beforeSend: function()
				{
				},
				success: function(res)
				{
					$this.find('i').hide();
					window.location.href = window.location.href.replace( /[\?#].*|$/, "?page=wsko_reporting&res=" + res.success + "&msg=" + encodeURIComponent(res.msg));
				}
			});
		}
	});
});