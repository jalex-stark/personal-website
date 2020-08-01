jQuery(document).ready(function($){
	$(document).on('wsko_init_page', function(e) {
		$('.wsko-remove-monitoring-keyword:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			window.wsko_post_element({action: 'wsko_remove_monitoring_keyword', keyword: $this.data('keyword'), nonce: $this.data('nonce')}, false, false, $this, true);
		});
		wsko_set_search_query_elements();
	});
	$('.wsko-add-monitoring-keyword-input:not(.wsko-init)').addClass('wsko-init').keydown(function(e){
		if (e.which == 13)
		{
			$(this).closest('.wsko-keyword-monitoring-add').find('.wsko-add-monitoring-keyword').click();
		}
	});
	$('.wsko-add-monitoring-keyword:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this),
		keyword = $this.closest('.wsko-keyword-monitoring-add').find('.wsko-add-monitoring-keyword-input').val();
		if (keyword)
			window.wsko_post_element({action: 'wsko_add_monitoring_keyword', keyword: keyword, multi:true, nonce: $this.data('nonce')}, false, false, $this, true);
	});
	function wsko_set_search_query_elements()
	{
		$('.wsko-search-query-link:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			window.wsko_post_element({action: 'wsko_query_search_custom', dimension: $this.data('dimension'), arg: $this.data('arg'), nonce: $this.data('nonce')}, 
			function(res){
				if (res.success)
				{
					$this.closest('.wsko-search-query-wrapper').find('.wsko-search-query-default').fadeOut();
					$this.closest('.wsko-search-query-wrapper').find('.wsko-search-query-overlay').fadeIn().find('.wsko-search-query-overlay-content').html(res.view);
				}
			}, 
			false,
			$this, false);
		});
		$('.wsko-search-query-overlay-back:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			$(this).closest('.wsko-search-query-wrapper').find('.wsko-search-query-overlay').fadeOut();
			$(this).closest('.wsko-search-query-wrapper').find('.wsko-search-query-default').fadeIn();
		});
	}
	wsko_set_search_query_elements();
});