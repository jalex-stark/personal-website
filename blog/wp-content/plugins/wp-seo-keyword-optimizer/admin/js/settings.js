jQuery(document).ready(function($){
	$('.wsko-import-backup:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this);
		
		window.wsko_post_file($this.closest('.wsko-import-backup-wrapper').find('.wsko-import-backup-file')[0].files[0], {action: 'wsko_import_configuration_backup', nonce: $this.data('nonce')}, false, function(res){
			if (res.success)
				location.reload();
		}, function(){
		}, $this, true);
	});
	$('.wsko-reporting-white-label-image-button:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this),
		$wrap = $this.closest('.wsko-reporting-white-label-image-wrapper');
		if (this.window === undefined) {
			this.window = wp.media({
					title: 'Insert a media',
					library: {type: 'image'},
					multiple: false,
					button: {text: 'Insert'}
				});

			var self = this;
			this.window.on('select', function() {
				var first = self.window.state().get('selection').first().toJSON();

				window.wsko_post_element({action: 'wsko_import_white_label', url: first.url, nonce: $wrap.data('nonce')}, function(res){
					if (res.success)
					{
						$wrap.find('.wsko-reporting-white-label-image').attr('src', first.url);
						$wrap.find('.wsko-reporting-white-label-image-button-remove').show();
					}
					return false;
				}, function(){
				}, $this, false);
			});
		}
		this.window.open();
	});
	$('.wsko-reporting-white-label-image-button-remove:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this),
		$wrap = $this.closest('.wsko-reporting-white-label-image-wrapper');
		window.wsko_post_element({action: 'wsko_import_white_label', url: '', nonce: $wrap.data('nonce')}, function(res){
			if (res.success)
			{
				$wrap.find('.wsko-reporting-white-label-image').attr('src', '');
				$wrap.find('.wsko-reporting-white-label-image-button-remove').hide();
			}
			return false;
		}, function(){
		}, $this, false);
	});
	$('#wsko_reporting_add_mail:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this);
		$('#wsko_reporting_mail_wrapper .wsko-reporting-mail-input').show().focus();
	});
	$('#wsko_reporting_mail_wrapper .wsko-reporting-mail-input:not(.wsko-init)').addClass('wsko-init').blur(function(event){
		event.preventDefault();
		var $this = $(this);
		$this.hide();
	}).keyup(function(event){
		if (event.which == 13)
		{
			var $this = $(this),
			mail = $this.val();

			window.wsko_post_element({action: 'wsko_reporting_add_mail', mail: mail, nonce: $this.data('nonce')}, 
				function(res){
					if (res.success)
					{
						var $temp = $('#wsko_reporting_mail_item_template').clone().attr('id', '').show().appendTo('#wsko_reporting_mail_wrapper .wsko-reporting-mail-list');
						$temp.find('.wsko-reporting-mail-text').html(mail);
						$temp.data('mail', mail);
						$this.blur();
						$this.val('');
						wsko_setttings_set_reporting_items();
					}
				},
				function()
				{
				}, $this, false);
		}
	});
	$('.wsko-reporting-select-add-view:not(.wsko-init)').addClass('wsko-init').change(function(event){
		event.preventDefault();
		var $this = $(this),
		key = $this.val();
		if (Array.isArray(key))
		{
			var arrayLength = key.length,
			has_hit = false;
			for (var i = 0; i < arrayLength; i++)
			{
				if (key[i] != '-1')
				{
					key = key[i];
					has_hit = true;
					break;
				}
			}
			if (!has_hit)
			key = "-1";
		}
		if (key != '-1')
		{
			var $lastGroup = $('.wsko-reporting-view.wsko-reporting-view-enabled').last(),
			$lastItem = $('.wsko-reporting-view-item-'+key).closest('.wsko-reporting-view').find('.wsko-reporting-view-item.wsko-reporting-view-enabled').last(),
			$item = $('.wsko-reporting-view-item-'+key).addClass('wsko-reporting-view-enabled'),
			$group = $item.closest('.wsko-reporting-view');
			if ($lastItem && $lastItem.length)
			{
				$item.insertAfter($lastItem);
			}
			if (!$group.hasClass('wsko-reporting-view-enabled') && $lastGroup && $lastGroup.length)
			{
				$group.insertAfter($lastGroup);
			}
			$group.addClass('wsko-reporting-view-enabled');

			$this.find('option.wsko-reporting-select-view-item-'+key).attr('disabled', true);
			$this.val('-1').change();
			$this.selectpicker('refresh');
			wsko_settings_update_reporting();
		}
	});
	if (typeof(jQuery.ui) != 'undefined' && typeof(jQuery.ui.draggable) != 'undefined' && typeof(jQuery.ui.droppable) != 'undefined')
	{
		$( ".wsko-reporting-views-list" ).sortable({
			start: function(event,ui){
				wsko_settings_cancel_update_reporting();
			},
			stop: function(event,ui){
				wsko_settings_update_reporting();
			}
		});
		$( ".wsko-reporting-view-items-list" ).sortable({
			start: function(event,ui){
				wsko_settings_cancel_update_reporting();
			},
			stop: function(event,ui){
				wsko_settings_update_reporting();
			}
		});
		$( ".wsko-reporting-views-list" ).disableSelection();
		$( ".wsko-reporting-view-items-list" ).disableSelection();
		/*$(".wsko-reporting-view").draggable({ handle: ".wsko-reporting-view-drag-handle" });
		$(".wsko-reporting-view-item").draggable({ handle: ".wsko-reporting-view-item-drag-handle" });*/
	}
	$('.wsko-reporting-view-delete:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this);
		$('.wsko-reporting-view-'+$this.data('view')).removeClass('wsko-reporting-view-enabled').find('.wsko-reporting-view-item-delete').click();
		$('select.wsko-reporting-select-add-view').find('optgroup.wsko-reporting-select-view-'+$this.data('view')+' option').attr('disabled', false);
		$('select.wsko-reporting-select-add-view').selectpicker('refresh');
		wsko_settings_update_reporting();
	});
	$('.wsko-reporting-view-item-delete:not(.wsko-init)').addClass('wsko-init').click(function(event){
		event.preventDefault();
		var $this = $(this);
		$('.wsko-reporting-view-item-'+$this.data('view')+'-'+$this.data('item')).removeClass('wsko-reporting-view-enabled');
		if (!($('.wsko-reporting-view-'+$this.data('view')+' .wsko-reporting-view-item.wsko-reporting-view-enabled').length))
		{
			$('.wsko-reporting-view-'+$this.data('view')).removeClass('wsko-reporting-view-enabled');
			$('select.wsko-reporting-select-add-view').find('optgroup.wsko-reporting-select-view-'+$this.data('view')+' option').attr('disabled', false);
		}
		else
			$('select.wsko-reporting-select-add-view').find('option.wsko-reporting-select-view-item-'+$this.data('view')+'-'+$this.data('item')).attr('disabled', false);
		$('select.wsko-reporting-select-add-view').selectpicker('refresh');
		wsko_settings_update_reporting();
	});
	$('.wsko-reporting-item-setting:not(.wsko-init)').addClass('wsko-init').change(function(event){
		wsko_settings_update_reporting();
	});
	$('.wsko-toggle-reporting-switch').on('wsko_ajax_input_save_success wsko_ajax_input_save_fail', function(event){
		if ($(this).is(':checked'))
		{
			$('.wsko-reporting-toggle-visible').show();
			$('.wsko-reporting-toggle-hidden').hide();
		}
		else
		{
			$('.wsko-reporting-toggle-visible').hide();
			$('.wsko-reporting-toggle-hidden').show();
		}
	});
	function wsko_settings_cancel_update_reporting()
	{
		var $list = $('#wsko_reporting_objects_list'),
		timeout = $list.data('update-timeout');
		if (timeout)
		{
			clearTimeout(timeout);
		}
	}
	function wsko_settings_update_reporting()
	{
		wsko_settings_cancel_update_reporting();

		var $list = $('#wsko_reporting_objects_list');
		$list.data('update-timeout', setTimeout(function(){
			var data = {};
			$('#wsko_reporting_objects_list .wsko-reporting-view.wsko-reporting-view-enabled').each(function(index){
				var $view = $(this),
				view_data = {};
				$view.find('.wsko-reporting-view-item.wsko-reporting-view-enabled').each(function(index){
					var $item = $(this),
					settings = {f:true};
					$item.find('.wsko-reporting-item-setting').each(function(index){
						var $setting = $(this);
						settings[$setting.attr('name')] = $setting.val();
					});
					view_data[$item.data('item')] = {settings:settings};
				});
				data[$view.data('view')] = view_data;
			});
			window.wsko_post_element({action: 'wsko_reporting_update_data', data: data, nonce: $('#wsko_reporting_objects_list').data('update-nonce')}, 
				function(res){
					if (res.success)
					{
					}
				},
				function()
				{
				}, $('#wsko_reporting_objects_list'), false);
		}, 2000));
	}

	function wsko_setttings_set_reporting_items()
	{
		$('.wsko-reporting-mail-item:not(#wsko_reporting_mail_item_template) .wsko-change-reporting-mail:not(.wsko-rp-init)').addClass('wsko-rp-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			mail = $this.closest('.wsko-reporting-mail-item').data('mail');

			window.wsko_post_element({action: 'wsko_reporting_change_mail', mail: mail, set: $this.is(':checked'), nonce: $('#wsko_reporting_mail_wrapper').data('change-nonce')}, 
				function(res){
					if (res.success)
					{
					}
				},
				function()
				{
				}, $this, false);
		});
		$('.wsko-reporting-mail-item:not(#wsko_reporting_mail_item_template) .wsko-remove-reporting-mail:not(.wsko-rp-init)').addClass('wsko-rp-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			mail = $this.closest('.wsko-reporting-mail-item').data('mail');

			window.wsko_post_element({action: 'wsko_reporting_remove_mail', mail: mail, nonce: $('#wsko_reporting_mail_wrapper').data('remove-nonce')}, 
				function(res){
					if (res.success)
					{
						$this.closest('.wsko-reporting-mail-item').remove();
					}
				},
				function()
				{
				}, $this, false);
		});
	}
	wsko_setttings_set_reporting_items();
});
