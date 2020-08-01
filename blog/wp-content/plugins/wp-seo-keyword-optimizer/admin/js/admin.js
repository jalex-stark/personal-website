jQuery(document).ready(function($)
{
	//Customs
	if (!$('body').hasClass('bavoko-seo-tools-wrapper'))
	{
		$('body').addClass('bavoko-seo-tools-wrapper');	
		/*setTimeout(function(){
		var $elem = $(this).closest('li.menu-top.opensub').find('.wp-submenu.wp-submenu-wrap');
		if ($elem.length)
		{
		}}, 1000);*/
		$(window).scroll(function(){
			wsko_recalc_page_scroll();		
		});
		$('.wp-submenu.wp-submenu-wrap').each(function(index){
			$(this).hover(function(){ $(this).css({'top': '0px'}); }, function(){ $(this).css({'top': ''}); });
		});
		function wsko_recalc_page_scroll()
		{
			return;
			var $window = $(window);
			if ($window.width() >= 782)//956)
			{
				var docViewTop = $window.scrollTop();
				var docHeight = $window.height();
				var docViewBottom = docViewTop + docHeight;

				var wskoBodyTop = 110;//$('#wsko_admin_view_content').scrollTop();
				var wskoBodyHeight = $('#wsko_admin_view_content').height();
				var wskoBodyBottom  = wskoBodyTop + wskoBodyHeight;			

				var sidebarTop = 32;//$('#adminmenuwrap').offset().top;
				var sidebarHeight = $('#adminmenuwrap').height();
				var sidebarBottom = sidebarTop + sidebarHeight;

				var is_diff_small = false;
				var diff = wskoBodyBottom - sidebarBottom;
				if ((diff < 100) && (diff > -100)) {
					is_diff_small = true;
				}
				if (sidebarHeight > docHeight && $(document).scrollTop() > sidebarHeight-docHeight)
				{
					if ((sidebarHeight-docHeight) < $(document).scrollTop())
					$('#adminmenuwrap').css({'margin-top': ($(document).scrollTop()-(sidebarHeight-docHeight)-$('#adminmenumain').offset().top)+'px'});
				}
				else
				{
					if (sidebarHeight > docHeight)
					{
						$('#adminmenuwrap').css({'margin-top': '0'});
					}
					else
						$('#adminmenuwrap').css({'margin-top': ($(document).scrollTop())+'px'});
				}
				//$('#adminmenuwrap').css({'top': '0'});
				if (wskoBodyHeight > docHeight)
				{
					if ((docViewBottom >= wskoBodyBottom) &&  (wskoBodyHeight < sidebarHeight) && !is_diff_small)
					{
						$('#wsko_admin_view_content').css({'position': 'fixed', 'bottom': '0px', 'top': 'unset'}).addClass('fixed');
					}
					else
					{
						$('#wsko_admin_view_content').css({'position': '', 'bottom': '', 'top': '80px'}).removeClass('fixed');
					}
				}
			}
		};
		wsko_recalc_page_scroll();
	}
	window.wsko_show_admin_modal = function(title, content)
	{
		var $modal = $('#wsko_modal_general');
		$modal.modal('show');
		if (title != false)
			$modal.find('.modal-title').html(title);
		$modal.find('.modal-msg').html(content);
	};
	
	//Init
	window.wsko_init_admin = function wsko_init_admin()
	{
		//Init Admin
		$('.wsko-open-modal:not(.wsko-m-init)').addClass('wsko-m-init').click(function(event) {
			event.preventDefault();
			
			var $this = $(this),
			$modal = $('#wsko_modal_general');
			if ($this.data('modal-ajax'))
			{
				$modal.find('.modal-title').html($this.data('modal-title'));
				$modal.find('#wsko_modal_general_wrapper').removeClass().addClass('modal-dialog').addClass($this.data('modal-class'));
				$loader = window.wsko_set_element_ajax_loader($this);
				var data = $this.data('modal-data');
				if (!data)
					data = {};
				data['action'] = $this.data('modal-ajax');
				data['nonce'] = $this.data('modal-nonce');
				window.wsko_post_element(data, function(res){
						if (res.success)
						{
							window.wsko_show_admin_modal(res.title ? res.title : false, res.view)
							window.wsko_add_element_ajax_result($this, true);
							return true;
						}
					}, function()
					{
						//return true;
					}, $this, false);
			}
			else
			{
				window.wsko_show_admin_modal($this.data('modal-title'), $this.data('modal-content'));
			}
			
			window.wsko_init_admin();
		});
		$('.wsko-close-modal-inline:not(.wsko-m-init)').addClass('wsko-m-init').click(function(event) {
			event.preventDefault();
			$(this).closest('.modal').modal('hide');
		});
		$('.wsko-load-lazy-page:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			objData = $this.data(),
			data = {};
			$.each(objData, function(k, v) {
				if (k.startsWith('wskoPost'))
					data[k.substr(8).toLowerCase()] = v;
			});
			window.wsko_load_lazy_page($this.data('controller'), $this.data('subpage'), $this.data('subtab'), $this.attr('href')+($this.data('subtab')?'&showtab='+$this.data('subtab'):''), data, false);
		});
		/*$('.wsko-ajax-slider:not(.wsko-ap-init)').addClass('wsko-ap-init').each(function(){
			var $this = $(this);
			$this.data('wsko-old-val', $this.val());
			//var $slider = $('<div class="wsko-slider-obj"></div>').prepend($this);
			var slider = noUiSlider.create($this.get(0), {
				start: $this.val(),
				step: 1,
				orientation: 'horizontal', // 'horizontal' or 'vertical'
				range: {
					'min': $this.data('min'),
					'max': $this.data('max')
				}
			}).on('change', function(){
				var val = $this.get(0).noUiSlider.get();
				if (val != $this.data('wsko-old-val'))
				{
					var old_val = $this.data('wsko-old-val');
					$this.data('wsko-old-val', val);
					window.wsko_post_element({action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_admin_data.save_ajax_input_nonce},
						function(res){
							$this.attr('disabled', false);
							if (!res.success)
								$this.val(old_val);
						}, function(){
							$this.attr('disabled', false);
							$this.val(old_val);
						}, $this, false);
				}
			});
		});*/
		$('.wsko-admin-timespan-picker-form:not(.wsko-init)').addClass('wsko-init').each(function(index){
			var $this = $(this),
			$start_time = $this.find('.wsko-start-time'),
			$end_time = $this.find('.wsko-end-time');
			$this.submit(function(event){
				event.preventDefault();
				window.wsko_post_element({
						wsko_controller: wsko_admin_data.controller,
						wsko_controller_sub: wsko_admin_data.subpage,
						start_time: $start_time.val(),
						end_time: $end_time.val(),
						action : 'wsko_set_timespan',
						nonce: wsko_admin_data.timespan_nonce
					}, function(res){
						if (res.success)
						{
							$(document).trigger("wsko_event_timespan_set");
						}
					}, false, $this.find('.wsko-loader'), true);
			});
			$this.find('.wsko-admin-timespan-picker').daterangepicker({
				startDate: moment.unix(parseInt(wsko_admin_data.timespan_start)).startOf('day'),
				endDate: moment.unix(parseInt(wsko_admin_data.timespan_end)).startOf('day'),
				//minDate: moment.unix(parseInt(wsko_admin_data.first_date)).startOf('day'),
				maxDate: moment().startOf('day').subtract(3, 'days'),
				ranges: {
				   //'Today': [moment(), moment()],
				   //'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				   'Last 7 Days': [moment().startOf('day').subtract(3 + 6, 'days'), moment().startOf('day').subtract(3, 'days')],
				   'Last 28 Days': [moment().startOf('day').subtract(3 + 27, 'days'), moment().startOf('day').subtract(3, 'days')],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}).on('apply.daterangepicker', function(ev, picker) {
				var $this = $(this);
				wsko_admin_data.timespan_start = picker.startDate.unix();//.utc().startOf('day').unix();
				wsko_admin_data.timespan_end = picker.endDate.unix();//.utc().startOf('day').unix();
				$this.find('.wsko-timespan-label').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
				$start_time.val(wsko_admin_data.timespan_start);
				$end_time.val(wsko_admin_data.timespan_end);
				$this.parents('.wsko-admin-timespan-picker-form').submit();
			});
			$this.find('.wsko-timespan-label').html(moment.unix(wsko_admin_data.timespan_start).format('MMMM D, YYYY') + ' - ' + moment.unix(wsko_admin_data.timespan_end).format('MMMM D, YYYY'));
			$start_time.val(wsko_admin_data.timespan_start);
			$end_time.val(wsko_admin_data.timespan_end);
		});
		$('.wsko-give-feedback:not(.wsko-init)').addClass('wsko-init').click(function(event) {
			event.preventDefault();
			
			var $this = $(this),
			$modal = $('#wsko_modal_feedback').modal('show');
			
			$modal.find('.wsko-feedback-msg').val('');
			$modal.find('.wsko-feedback-title').val('');
		});
		//Pro Conversion Modal
		$('.wsko-pro-modal-link:not(.wsko-init)').addClass('wsko-init').click(function(event) {
			event.preventDefault();
			$modal = $('#wsko_pro_modal_wrapper').modal('show');
		});
		$('.wsko-give-rating:not(.wsko-init)').addClass('wsko-init').click(function(event) {
			event.preventDefault();
			
			var $this = $(this),
			$modal = $('#wsko_modal_rating').modal('show');
			
			//$modal.find('.wsko-feedback-msg').val('');
			//$modal.find('.wsko-feedback-title').val('');
		});
		$('.wsko-import-plugin:not(.wsko-init)').addClass('wsko-init').click(function(event) {
			event.preventDefault();
			
			var $this = $(this),
			$wrapper = $this.closest('.wsko-import-plugin-wrapper'),
			options = [];
			$wrapper.find('.wsko-import-plugin-option:checked').each(function(index){
				options.push($(this).val());
			});
			if (options.length)
			{
				window.wsko_post_element({
						'plugin': $wrapper.data('plugin'),
						'options': options,
						'nonce': $wrapper.data('nonce'),
						action : 'wsko_import_plugin',
					}, function(res){
						if (res.success)
						{
							window.wsko_show_admin_modal(res.report_title, res.report);
						}
					}, false, $this, false);
			}
			else
			{
				window.wsko_notification(false, window.wsko_text('import_invalid'), '');
			}
		});
		
		$('.wsko-admin-request-api-access:not(.wsko-init)').addClass('wsko-init').submit(function(event){
			event.preventDefault();
			var $this = $(this),
			code = $this.find('.wsko-token-field').val(),
			api = $this.data('api');
			$this.closest('.wsko-settings-api-box').find('.wsko-api-login-help-box').hide();
			$this.closest('.wsko-settings-api-box').find('.wsko-api-login-help-box-custom').hide().html('');
			if (code)
				window.wsko_post_element({action: 'wsko_request_api_access', code: code, type: api, nonce: $this.data('nonce')}, 
				function(res) {
					
					if (res.success) 
					{
						if (res.view)
						{
							$this.closest('.wsko-settings-api-wrapper').replaceWith(res.view);
						}
					}
					else
					{
						if (!res.err_view)
							$this.closest('.wsko-settings-api-box').find('.wsko-api-login-help-box').show();
						else
							$this.closest('.wsko-settings-api-box').find('.wsko-api-login-help-box-custom').show().html(res.err_view);
					}
				},
				false, $this.find('.wsko-request-btn'), false);
		});
		$('#wsko_reset_configuration:not(.wsko-init)').addClass('wsko-init').click(function(event){
			var $this = $(this);
			if(confirm(window.wsko_text('delete_cfg_confirm')))
				window.wsko_post_element({action: 'wsko_reset_configuration', delete_metas: $('#wsko_reset_opt_metas').is(':checked'), delete_cache: $('#wsko_reset_opt_cache').is(':checked'), delete_redirects: $('#wsko_reset_opt_redirects').is(':checked'), delete_backups: $('#wsko_reset_opt_backups').is(':checked'), nonce: $this.data('nonce')}, false, false, $this, true);
		});
		$('#wsko_modal_feedback .wsko-feedback-type-btn input').change(function(){
			if ($(this).is(':checked'))
			{
				if ($(this).val() == '1')
					$('#wsko_modal_feedback').find('.wsko-support-options').show();
				else
					$('#wsko_modal_feedback').find('.wsko-support-options').hide();
			}
		});
		
		//Resolve URLs
		var urlFields = [];
		$('.wsko-ajax-url-field:not(.wsko-ajax-loaded)').each(function(index){
			var $this = $(this);
			if ($this.parents('.wsko_tables').length == 0)
			{
				if (!urlFields.includes($this.data('url')))
					urlFields.push($this.data('url'));
				$this.addClass('wsko-ajax-load');
			}
		});
		if (urlFields.length > 0)
		{
			window.wsko_post_element({action: 'wsko_resolve_url', urls: urlFields, nonce: wsko_admin_data.resolve_nonce},
				function(res){
					if (res.success)
					{
						$.each(res.urls, function(k, v){
							$('.wsko-ajax-url-field.wsko-ajax-load[data-url="'+k+'"]').html(v.title).removeClass('wsko-ajax-load').addClass('wsko-ajax-loaded');
						});
					}
					else
					{
						$('.wsko-ajax-url-field.wsko-ajax-load').html('<p style="color:red">'+window.wsko_text('resolve_error')+'</p>').removeClass('wsko-ajax-load').addClass('wsko-ajax-loaded');
					}
					return true;
				}, function(){
					$('.wsko-ajax-url-field.wsko-ajax-load').html('<p style="color:red">'+window.wsko_text('resolve_error')+'</p>').removeClass('wsko-ajax-load').addClass('wsko-ajax-loaded');
					return true;
				}, false, false);
		}
		
		$('.wsko-metas-bulk-collapse:not(.wsko_init)').addClass('wsko_init').each(function(index){
			var $this = $(this);
			$this.click(function(event){
				event.preventDefault();
				$this.closest('.wsko-set-metas-wrapper').find('ul.wsko-tabs-social-snippets').toggle();
				var old = $this.html();
				$this.html($this.data('toggle-heading'));
				$this.data('toggle-heading', old);
			});

		});
		$('.wsko-onpage-prem-freq:not(.wsko-init)').addClass('wsko-init').change(function(event) {
			event.preventDefault();
			var $this = $(this),
			val = $this.val(),
			$container = $this.closest('.wsko-onpage-prem-freq-wrapper');
			if (val > 200)
				$container.find('.wsko-onpage-prem-freq-warning').show();
			else
				$container.find('.wsko-onpage-prem-freq-warning').hide();
			if (val <= 0)
				val = 100; //default
			if (val <= 50)
			{
				$this.val(50).change();
				return;
			}
			var ref = parseInt($this.data('ref')) / val;
			$container.find('.wsko-onpage-prem-freq-text').html(ref > 60 ? Math.round(ref/60, 2)+' h' : Math.round(ref, 2)+' min');
		}).change();
		//init misc
		window.wsko_init_misc_widgets();

		//Do customs
		$(document).trigger("wsko_init_page");
	}
	
	//Mobile Navigation
	$('.mobile-nav-toggle').click(function() {
		if ( $('.wsko_wrapper.wsko-mobile-nav.active').length > 0 ) {
			$('.mobile-nav-toggle i').removeClass('fa-times').addClass('fa-bars');			
		} else {
			$('.mobile-nav-toggle i').removeClass('fa-bars').addClass('fa-times');			
		};
		
		$('.wsko_wrapper.wsko-mobile-nav').toggleClass('active');
	});
	
	//WSKO Help System
	$('.wsko-toggle-help').click(function() {
		$modal = $('.wsko-help-wrapper');
		$chat = $('#tidio-chat');
		if ( $modal.css('display') === 'none' ) {
			$modal.fadeIn();
			$chat.addClass('help-active').show();
				
		} else {
			$modal.fadeOut();
			$chat.removeClass('help-active');
		};		
	});
	
	//Statics
	$('#wsko_feedback_form').submit(function(event){
		event.preventDefault();
		
		var $this = $(this),
		$btn = $this.find('.wsko-feedback-submit'),
		name = $this.find('.wsko-feedback-name').val(),
		mail = $this.find('.wsko-feedback-email').val(),
		sub = $this.find('.wsko-feedback-title').val(),
		msg = $this.find('.wsko-feedback-msg').val(),
		append_reports = $this.find('.wsko-feedback-reports').is(':checked'),
		type = $('.wsko-feedback-type input:checked').val();
		
		if (type && mail && sub && msg)
			window.wsko_post_element({action: 'wsko_send_feedback', name: name, email:mail, title:sub, message:msg, append_reports:append_reports, type: type, nonce:wsko_admin_data.feedback_nonce}, function(res){ if (res.success) { $('#wsko_modal_feedback').modal('hide'); window.wsko_notification(true, 'Message sent. Thank you!', ''); return true; } else { $('#wsko_modal_feedback .wsko-feedback-notices').html(res.help_notice); } }, false, $btn, false);
	});

	/* Tidio Hide */
	$(window).on("scroll", function() {
		var scrollHeight = $(document).height();
		var windowHeight = $(window).height();
		var scrollPosition = $(window).height() + $(window).scrollTop();
		var sidebarHeight = $('#adminmenuwrap').height();
		if (/* (sidebarHeight > windowHeight) && */ !$('#tidio-chat').hasClass('help-active') && (scrollHeight - scrollPosition) / scrollHeight < 0.01) {
			$('#tidio-chat').hide();
		} else {
			$('#tidio-chat').show();
		}
	});

	$('input[type="range"]').change(function() {
		$val = $(this).val();
		$(this).closest('.range-field').find('.text').html($val);
	});

	//Main Nav just toggle
	$('.wsko-admin-main-navbar-item .panel-heading a').click(function(event) {
		event.preventDefault();
	});
	
	//Extensions
	window.wsko_get_controller = function() {
		return {wsko_controller: wsko_admin_data.controller, wsko_controller_sub: wsko_admin_data.subpage};
	};
	window.wsko_get_ssl = function()
	{
		return wsko_admin_data.ssl_enabled;
	}
	
	//lazy page loading
	window.wsko_reload_lazy_page = function()
	{
		window.wsko_load_lazy_page(wsko_admin_data.controller, wsko_admin_data.subpage, false, false, false, true);
	}
	
	window.wsko_load_lazy_page = function(controller, subpage, tab, page_link, custom_data, is_reload)
	{
		if (window.wsko_is_in_page_load)
			window.wsko_is_in_page_load_double = true;
		window.wsko_is_in_page_load = true;
		$.wskoXhrPool.abortAll();
		window.wsko_is_in_page_load_double = false;
		
		$('#wsko_admin_view_loading').show();
		var tab_states = [];
		if (is_reload)
		{
			$('.tab-pane').each(function(index){
				var $this = $(this);
				tab_states.push({id: $this.attr('id'), active:$this.hasClass('active')});
			});
		}
		var data = {
			wsko_controller: controller,
			wsko_controller_sub: subpage,
			showtab: tab,
			action: 'wsko_load_lazy_page',
			nonce: wsko_admin_data.lazy_page_nonce
		};
		if (custom_data)
			$.extend(data, custom_data);
		window.wsko_post_element(data,
			function(res){
				window.wsko_is_in_page_load = false;
				if (res.success)
				{
					$('#wsko_admin_view_loading').hide();
					//remove previous actions
					$(document).off('wsko_init_page wsko_event_timespan_set');
					
					//Update globals
					wsko_admin_data.controller = controller;
					wsko_admin_data.subpage = subpage;
					
					//Update history
					if (page_link)
						window.history.replaceState({}, res.tab_title, page_link);
					document.title=res.tab_title;
					
					//Update view
					$('.wsko-admin-main-navbar-sub-panel').removeClass('wsko-active');
					$('.wsko-admin-main-navbar-item').removeClass('wsko-active');
					$('.wsko-admin-main-navbar-item[data-link="'+controller+'"]').addClass('wsko-active').find('.wsko-admin-main-navbar-sub-panel').addClass('wsko-active');
					
					$('.wsko-admin-main-sub-navbar-item').removeClass('wsko-active');
					$('.wsko-admin-main-sub-navbar-item[data-link="'+controller+"_"+subpage+'"]').addClass('wsko-active');
					
					$('#wsko_main_title').html(res.title);
					$('#wsko_main_breadcrumb').html(res.breadcrumb);
					$('#wsko_help_breadcrumb').html(res.breadcrumb);
					
					$('#wsko_admin_view_header_wrapper').html(res.header);
					$('#wsko_admin_view_notification_wrapper').html(res.notif);
					$('#wsko_admin_view_wrapper').html(res.view);
					$('#wsko_admin_view_content_footer_wrapper').html(res.footer);
					$('#wsko_admin_view_script_wrapper').html(res.scripts);
					
					$('#wsko_admin_ajax_notifications').html("");
					$('html').scrollTop(0);
					if (is_reload)
					{
						$.each(tab_states, function(k, v){
							if (v.active)
							{
								$('.nav-tabs a[href="#' + v.id + '"]').closest('li').addClass('active');
								$('#' + v.id).addClass('in active');
							}
							else
							{
								$('.nav-tabs a[href="#' + v.id + '"]').closest('li').removeClass('active');
								$('#' + v.id).removeClass('in active');
							}
						});
					}
					window.wsko_reload_help_content();
				}
				else
				{
					//window.wsko_notification(false, "Page could not be loaded per AJAX! Attempting manual load...", "");
					if (page_link)
						window.location.href = page_link;
					else
						window.location.reload(true);
					window.wsko_reload_help_content();
				}
				return true;
			}, function(){
				//$('#wsko_admin_view_loading').hide();
				//if (!window.wsko_is_in_page_load_double)
					//window.wsko_notification(false, "Page could not be loaded!", "");
				if (page_link)
					window.location.href = page_link;
				else
					window.location.reload(true);
				return true;
			}, false, false);
	}

	window.wsko_load_lazy_page_widget = function(controller, subpage, tab, custom_data, success, error)
	{
		var data = {
			wsko_controller: controller,
			wsko_controller_sub: subpage,
			showtab: tab,
			widget: true,
			action: 'wsko_load_lazy_page',
			nonce: wsko_admin_data.lazy_page_nonce
		};
		if (custom_data)
			$.extend(data, custom_data);
		window.wsko_post_element(data, success, error, false, false);
	}
	
	//lazy data loading
	window.wsko_load_lazy_data = function($container, action, args, resetOld)
	{
		window.wsko_load_lazy_data_for_controller($container, wsko_admin_data.controller, wsko_admin_data.subpage, action, args, resetOld);
	};

	window.wsko_reload_help_content = function()
	{
		var $help_view = $('#wsko_admin_view_help_wrapper');
		if ($help_view && $help_view.length)
		{
			$help_view.html('');
			window.wsko_post_element({action: 'wsko_get_help_content', wsko_controller: wsko_admin_data.controller, wsko_controller_sub: wsko_admin_data.subpage, nonce: wsko_admin_data.get_help_content_nonce},
			function(res){
				if (res.view)
				{
					$help_view.html(res.view);
				}
				else
				{
					$help_view.html(window.wsko_text('undefined_error'));
				}
				return true;
			}, function() {
				$help_view.html(window.wsko_text('server_error'));
				return true;
			}, $help_view, false);
		}
	}
	window.wsko_reload_help_content();
});	