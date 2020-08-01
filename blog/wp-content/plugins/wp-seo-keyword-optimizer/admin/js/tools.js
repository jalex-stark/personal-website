jQuery(document).ready(function($){
	
	$('#wsko_check_redirect_form:not(.wsko_init)').addClass('wsko_init').submit(function(event){
		event.preventDefault();
		var $this = $(this),
		page = $this.find('.wsko-field-url').val(),
		status_check = $this.find('.wsko-field-status-check').is(':checked');
		
		if (page)
		{
			window.wsko_post_element({action: 'wsko_check_redirect', url: page, status_check: status_check, nonce: $this.data('nonce')},
			function(res) {
				if (res.success)
					$('#wsko_check_redirect_results').html(res.view);
			},
			false,
			$this.find('.wsko-form-submit'), false);
		}
	});
	$('#wsko_save_robots:not(.wsko_init)').addClass('wsko_init').click(function(event){
		event.preventDefault();
		var $this = $(this);
		window.wsko_post_element({action: 'wsko_save_robots', robots: $('#wsko_robots_field').val(), nonce: $this.data('nonce')}, false, false, $this, false);
	});
	$('#wsko_save_htaccess:not(.wsko_init)').addClass('wsko_init').click(function(event){
		event.preventDefault();
		var $this = $(this);
		window.wsko_post_element({action: 'wsko_save_htaccess', htaccess: $('#wsko_htaccess_field').val(), nonce: $this.data('nonce')}, function(res){ if (!res.success) $('#wsko_htaccess_field').val($('#wsko_htaccess_field').data('old-val')); else $('#wsko_htaccess_field').data('old-val', $('#wsko_htaccess_field').val()); }, false, $this, false);
	}).data('old-val', $('#wsko_htaccess_field').val());
	$('#wsko_htaccess_field:not(.wsko_old_val_applied)').addClass('wsko_old_val_applied').data('old-val', $('#wsko_htaccess_field').val());

	$('#wsko_add_redirect_form:not(.wsko_init)').addClass('wsko_init').submit(function(event){
		event.preventDefault();
		var $this = $(this),
		page = $this.find('.wsko-field-page').val(),
		redirect = $this.find('.wsko-field-redirect').val();
		
		if (page && redirect)
		{
			if (page.toLowerCase().indexOf('https://') !== -1 && !window.wsko_get_ssl() && !confirm('You are using a link with "https", but SSL is not activated. Add redirect regardless?'))
				return;
			window.wsko_post_element({action: 'wsko_add_redirect', comp: $this.find('.wsko-field-comp').val(), page: page, type: $this.find('.wsko-field-type').val(), comp_to: $this.find('.wsko-field-comp-to').val(), redirect_to: redirect, nonce: $this.data('nonce')}, false, false, $this.find('.wsko-save-btn'), true);
		}
	});
	$('#wsko_automatic_redirect_form:not(.wsko_init)').addClass('wsko_init').submit(function(event){
		event.preventDefault();
		var $this = $(this);
		window.wsko_post_element({action: 'wsko_update_automatic_redirect', activate: $this.find('.wsko-field-activate').is(':checked'), type: $this.find('.wsko-field-type').val(), custom: $this.find('.wsko-field-custom').val(), nonce: $this.data('nonce')}, false, false, $this.find('.wsko-save-btn'), false);
	});
	$('#wsko_sitemap_settings_wrapper:not(.wsko_init)').addClass('wsko_init').on('wsko_update_sitemap', function(event){
		event.preventDefault();
		var $this = $(this),
		types = [],
		tax = [],
		stati = [];
		
		$('.wsko-sitemap-param-type').each(function(index){
			var $type = $(this);
			if($type.find('.wsko-sitemap-type-activate').is(':checked'))
			{
				types.push({name: $type.find('.wsko-sitemap-type-activate').val(), freq: $type.find('.wsko-sitemap-subparam-freq').val(), prio: $type.find('.wsko-sitemap-subparam-prio').val()});
			}
		});

		$('.wsko-sitemap-param-tax').each(function(index){
			var $type = $(this);
			if($type.find('.wsko-sitemap-tax-activate').is(':checked'))
			{
				tax.push({name: $type.find('.wsko-sitemap-tax-activate').val(), freq: $type.find('.wsko-sitemap-subparam-freq').val(), prio: $type.find('.wsko-sitemap-subparam-prio').val()});
			}
		});
		
		$('.wsko-sitemap-param-status').each(function(index){
			var $type = $(this);
			if($type.find('.wsko-sitemap-status-activate').is(':checked'))
			{
				stati.push($type.find('.wsko-sitemap-status-activate').val());
			}
		});
		if (types.length != 0)
			window.wsko_post_element({action: 'wsko_update_sitemap', auto_generation: $('.wsko-sitemap-param-activate').is(':checked'), ping: $('.wsko-sitemap-param-ping').is(':checked'), types: types, tax: tax, stati: stati, excluded_posts: $('.wsko-sitemap-param-excposts').val(), nonce: $this.data('nonce')}, false, false, $this.find('.wsko-sitemap-settings-heading'), false);
		else if (types.length == 0)
			window.wsko_notification(false, 'Please select at least one Post Type', '');
	}).find(':input').change(function(event){ $('#wsko_sitemap_settings_wrapper').trigger('wsko_update_sitemap'); });
	$('.wsko-sitemap-type-activate:not(.wsko_init)').addClass('wsko_init').on('change wsko_init', function(){
		var $wrap = $(this).closest('.wsko-sitemap-param-type');
		if ($(this).is(':checked'))
		{
			$wrap.find('.wsko-sitemap-subparam-freq').attr('disabled', false);
			$wrap.find('.wsko-sitemap-subparam-prio').attr('disabled', false);
		}
		else
		{
			$wrap.find('.wsko-sitemap-subparam-freq').attr('disabled', true);
			$wrap.find('.wsko-sitemap-subparam-prio').attr('disabled', true);
		}
	}).trigger("wsko_init");
	$('.wsko-sitemap-tax-activate:not(.wsko_init)').addClass('wsko_init').on('change wsko_init', function(){
		var $wrap = $(this).closest('.wsko-sitemap-param-tax');
		if ($(this).is(':checked'))
		{
			$wrap.find('.wsko-sitemap-subparam-freq').attr('disabled', false);
			$wrap.find('.wsko-sitemap-subparam-prio').attr('disabled', false);
		}
		else
		{
			$wrap.find('.wsko-sitemap-subparam-freq').attr('disabled', true);
			$wrap.find('.wsko-sitemap-subparam-prio').attr('disabled', true);
		}
	}).trigger("wsko_init");
	
	$('#wsko_add_redirect_form .wsko-field-comp:not(.wsko_init)').addClass('wsko_init').change(function(){
		var $this = $(this),
		val = $this.val();
		$('.wsko-redirect-type-infos').hide();
		$('.wsko-redirect-type-infos.wsko-infos-'+val).show();
		$('#wsko_add_redirect_form .wsko-field-page').attr('placeholder', $this.data('ph-'+val));
		if (val == 'exact')
		{
			$('#wsko_add_redirect_form .wsko-field-comp-to').attr('disabled', true).val('exact').trigger('change');
		}
		else
		{
			$('#wsko_add_redirect_form .wsko-field-comp-to').attr('disabled', false);
		}
	}).trigger("change");
	$('#wsko_add_redirect_form .wsko-field-comp-to:not(.wsko_init)').addClass('wsko_init').change(function(){
		var $this = $(this),
		val = $this.val();
		$('#wsko_add_redirect_form .wsko-field-redirect').attr('placeholder', $this.data('ph-'+val));
	}).trigger("change");
	
	$('#wsko_automatic_redirect_form .wsko-field-type:not(.wsko_init)').addClass('wsko_init').change(function(){
		if ($(this).val() == '3')
			$('#wsko_automatic_redirect_form .wsko-field-custom-wrapper').show();
		else
			$('#wsko_automatic_redirect_form .wsko-field-custom-wrapper').hide();
	}).trigger("change");
	
	$('.wsko-breadcrumb-insert-type:not(.wsko_init)').addClass('wsko_init').change(function(){
		var $this = $(this);
		if ($this.val() == 'custom')
		{
			$this.closest('.wsko-breadcrumb-type-wrapper').find('.wsko-breadcrumb-insert-custom').attr('readonly', false).attr('disabled', false);
			$this.closest('.wsko-breadcrumb-type-wrapper').find('.wsko-breadcrumb-insert-custom-wrapper').show();
		}
		else
		{
			$this.closest('.wsko-breadcrumb-type-wrapper').find('.wsko-breadcrumb-insert-custom').attr('readonly', true).attr('disabled', true);
			$this.closest('.wsko-breadcrumb-type-wrapper').find('.wsko-breadcrumb-insert-custom-wrapper').hide();
		}
	}).change();

	$('.wsko-insert-widget-style:not(.wsko_init)').addClass('wsko_init').click(function(){
		event.preventDefault();
		event.stopPropagation();

		var $this = $(this);
		if (!$this.find('.wsko-temp-select').length)
		{
			$('.wsko-insert-widget-style').not($this).find('.wsko-temp-select').remove();
			$('<p class="wsko-temp-select"><i class="fa fa-css3"></i> Add style rule</p>').appendTo($this).click(function(event){
				event.preventDefault();
				event.stopPropagation();
				var prev = $('#wsko_widget_header_styles').val();
				window.wsko_scroll_element($('#wsko_widget_header_styles').val(prev+(prev?'\r\n':'')+$this.data('selector')+'{\r\n\t/*Your style*/\r\n}'));
				$('.wsko-insert-widget-style .wsko-temp-select').remove();
			});
			$('<p class="wsko-temp-select"><i class="fa fa-mouse-pointer"></i> Add click event</p>').appendTo($this).click(function(event){
				event.preventDefault();
				event.stopPropagation();
				var prev = $('#wsko_widget_header_scripts').val();
				window.wsko_scroll_element($('#wsko_widget_header_scripts').val(prev+(prev?'\r\n':'')+'jQuery(document).ready(function($){\r\n\t$("'+$this.data('selector')+'").click(function(event){\r\n\t\tevent.preventDefault();\r\n\t\t//Your code\r\n\t});\r\n});').focus());
				$('.wsko-insert-widget-style .wsko-temp-select').remove();
			});
		}
	});
	$(document).click(function(event){
		$('.wsko-insert-widget-style .wsko-temp-select').remove();
	});
	$('#wsko_frontend_widget_breadcrumbs :input').on('wsko_ajax_input_save_success wsko_init_temp', function(){
		var $wrapper = $('#wsko_frontend_widget_breadcrumbs');
		window.wsko_post_element({action: 'wsko_get_widget_preview', widget: 'breacrumbs', nonce: $wrapper.data('nonce')}, function(res){ if (res.success) { $('#wsko_breadcrumbs_preview').html(res.view); window.wsko_frontend_reinit(); return true; } }, false, $('#wsko_breadcrumbs_preview'), false);
	}).first().trigger('wsko_init_temp');
	$('#wsko_frontend_widget_content_table :input').on('wsko_ajax_input_save_success wsko_init_temp', function(){
		var $wrapper = $('#wsko_frontend_widget_content_table');
		window.wsko_post_element({action: 'wsko_get_widget_preview', widget: 'content_table', nonce: $wrapper.data('nonce')}, function(res){ if (res.success) { $('#wsko_content_table_preview').html(res.view); window.wsko_frontend_reinit(); return true; } }, false, $('#wsko_content_table_preview'), false);
	}).first().trigger('wsko_init_temp');

	$('.wsko-rich-snippet-add-item:not(.wsko_init)').addClass('wsko_init').click(function(){
		event.preventDefault();
		var $this = $(this),
		type = $this.data('snippet'),
		type_name = $this.data('snippet-text');
		$('#wsko_add_rich_snippets_type_step').hide();
		$('#wsko_add_rich_snippets_location_step').show();
		$('#wsko_add_rich_snippets_config_step').hide();

		$('#wsko_add_rich_snippets_type').val(type);
		$('#wsko_add_rich_snippets_type_name').text(type_name);
		$('#wsko_add_rich_snippet_config_default').html('').removeClass('wsko-snippet-config-loaded');

		window.wsko_post_element({action: 'wsko_get_rich_snippet_config', type: type, nonce: $('#wsko_add_rich_snippets_type').data('config-nonce')},
			function(res){ if (res.success) { $('#wsko_add_rich_snippet_config_default').html(res.view).addClass('wsko-snippet-config-loaded'); window.wsko_set_rich_snippets(); return true; } },
		 	false, $('#wsko_add_rich_snippets_type'), false);
	});
	$('#wsko_add_rich_snippet_reselect_type:not(.wsko_init)').addClass('wsko_init').click(function(){
		event.preventDefault();
		var $this = $(this);
		$('#wsko_add_rich_snippets_type_step').show();
		$('#wsko_add_rich_snippets_location_step').hide();
		$('#wsko_add_rich_snippets_config_step').hide();
	});
	$('#wsko_add_rich_snippets_location:not(.wsko_init)').addClass('wsko_init').change(function(){
		var $this = $(this);
		$('.wsko-snippet-location').hide();
		$('.wsko-snippet-location[data-location="'+$this.val()+'"]').show();
	});
	$('#wsko_add_rich_snippet_reselect_loc:not(.wsko_init)').addClass('wsko_init').click(function(){
		event.preventDefault();
		var $this = $(this);
		$('#wsko_add_rich_snippets_type_step').hide();
		$('#wsko_add_rich_snippets_location_step').show();
		$('#wsko_add_rich_snippets_config_step').hide();
	});
	$('#wsko_add_rich_snippet_set_config:not(.wsko_init)').addClass('wsko_init').click(function(){
		event.preventDefault();
		var $this = $(this);
		$('#wsko_add_rich_snippets_type_step').hide();
		$('#wsko_add_rich_snippets_location_step').hide();
		$('#wsko_add_rich_snippets_config_step').show();
		$('#wsko_add_rich_snippets_location_name').text($('#wsko_add_rich_snippets_location option:selected').text()+' - ');
	});
	$('#wsko_add_rich_snippet_confirm:not(.wsko_init)').addClass('wsko_init').click(function(){
		event.preventDefault();
		var $this = $(this),
		type = $('#wsko_add_rich_snippets_type').val(),
		location = $('#wsko_add_rich_snippets_location').val(),
		post_types = [],
		posts = $('.wsko-snippet-location[data-location="'+location+'"] .wsko-snippet-loc-post').val(),
		data = $('#wsko_add_rich_snippet_config_form').find(':input:not(.wsko-rs-template-input)').serialize();
 		$('.wsko-snippet-location[data-location="'+location+'"] .wsko-snippet-loc-post-type:checked').each(function(index){
			post_types.push($(this).val());
		});

		if ($('#wsko_add_rich_snippet_config_default').hasClass('wsko-snippet-config-loaded'))
		{
			window.wsko_post_element({action: 'wsko_create_rich_snippet', type: type, location: location, post_types: post_types, posts: posts, data: data, nonce: $('#wsko_add_rich_snippet_config_form').data('nonce')},
				function(res){ if (res.success) {
					$('#wsko_add_rich_snippets_type_step').show();
					$('#wsko_add_rich_snippets_location_step').hide();
					$('#wsko_add_rich_snippets_config_step').hide();
					$('#wsko_add_rich_snippets_type_name').text('');
					$('#wsko_add_rich_snippets_location_name').text('');
				} },
				 false, $('#wsko_add_rich_snippets_type'), false);
		}
		else
		{
			window.wsko_notification(false, window.wsko_text('snippet_cfg_error'), '');
		}
	});
	$('.wsko-rich-snippets-toggle-link').addClass('wsko_init').click(function(){
		event.preventDefault();
		
		$('.wsko-rich-snippets-toggle-link').toggle();
		$('.wsko-rich-snippets-toggle-wrapper').toggle();
	});
	
});