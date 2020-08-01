jQuery(document).ready(function($)
{
	window.wsko_init_core = function ()
	{
		//Load necessaries
		if (!(typeof google === 'undefined') && !(typeof google.charts === 'undefined'))
			google.charts.load('current', {'packages':['corechart', 'geochart'], 'mapsApiKey': wsko_data.mapsApiKey});

		//Fixes
		$('#wsko_admin_view_body a[href="#"]:not(.wsko-init),.wsko-content-optimizer a[href="#"]:not(.wsko-f-init)').addClass('wsko-f-init').click(function(event){
			event.preventDefault();
		});

		$('.wsko-focus-table-row-element:not(.wsko-fo-init)').addClass('wsko-fo-init').hover(function(event){
			$(this).closest('tr').find('td').addClass('wsko-table-focus');
		},function(event){
			$(this).closest('tr').find('td').removeClass('wsko-table-focus');
		});

		$('.wsko_modal,.wsko-modal').on("show", function () {
			$("body,html").addClass("modal-open");
		}).on("hidden", function () {
			$("body,html").removeClass("modal-open");
		});

		if ($.fn.wpColorPicker)
		{
			$('.wsko-color-picker').wpColorPicker({
				change: function(event, ui) {
					var $this = $(event.target);
					if ($this.data('change-timeout'))
						clearTimeout($this.data('change-timeout'));
					$this.data('change-timeout', setTimeout(function(){
						$this.trigger('wsko_trigger_save');
					}, 1000))
				}
			});
		}
	 
		//Custom Collapse
		$('.wsko-collapse').click(function(event){
			event.preventDefault();
			var $content = $(this).closest('.wsko-collapse-wrapper').find('.wsko-collapse-content').first();
			if ($content.css('max-height') != '0px' || $content.css('max-height') === 'none' ) {
				$content.css('max-height', $content.get(0).scrollHeight + "px");
				var timeout;
				if (timeout = $content.data('timeout'))
					clearTimeout(timeout);
				$content.data('timeout', setTimeout(function() { $content.css('max-height', '0px'); }, 200));
				$content.closest('.wsko-collapse-wrapper').toggleClass('wsko-collapse-in');
			}
			else {
				$content.css('max-height', $content.get(0).scrollHeight + "px");
				var timeout;
				if (timeout = $content.data('timeout'))
					clearTimeout(timeout);
				$content.data('timeout', setTimeout(function() { $content.css('max-height', 'none'); }, 1000));
				$content.closest('.wsko-collapse-wrapper').toggleClass('wsko-collapse-in');
			}
		});

		//General Controls
		$('.wsko-ajax-button:not(.wsko-ajax-init)').addClass('wsko-ajax-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			objData = $this.data(),
			ajax_reload = $this.data('ajax-reload'),
			sources = $this.data('sources'),
			data = {action: $this.data('action'), nonce: $this.data('nonce')};
			if (sources)
			{
				$(sources).each(function(index){
					var $this = $(this);
					if ($this.data('multi-source'))
					{
						if (!data[$this.attr('name')])
							data[$this.attr('name')] = [];
						
						data[$this.attr('name')].push($this.val());
					}
					else
						data[$this.attr('name')] = $this.val();
				});
			}
			$.each(objData, function(k, v) {
				if (k.startsWith('wskoPost'))
					data[k.substr(8).toLowerCase()] = v;
			});
			if (!$this.data('alert') || confirm($this.data('alert')))
				window.wsko_post_element(data, 
					function(res){ 
						if (res.success)
						{
							if ($this.data('remove'))
							{ 
								$($this.data('remove')).fadeOut('fast', function(){ $(this).remove(); });
							}
							if ($this.data('remove-parent'))
							{ 
								$this.closest($this.data('remove-parent')).fadeOut('fast', function(){ $(this).remove(); });
							}
							if (ajax_reload && res.view)
							{
								$this.closest(ajax_reload).replaceWith(res.view);
							}
							if ($this.data('table-reload'))
							{
								$this.closest('.wsko-tables').trigger('wsko_data_source_updated');
							}
						}
					}, false, $this, (($this.data('no-reload') || ajax_reload) ? false : ($this.data('reload-real') ? 2 : true)));
		});

		$('.wsko-validate-input:not(.wsko-va-init)').addClass('wsko-va-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			$this.change(function(event){
				var is_valid = true,
				val = $this.val();
				if ($this.attr('required'))
				{
					if (!val)
						is_valid = false;
				}
				if ($this.attr('max') != undefined)
				{
					if (val > $this.attr('max'))
						is_valid = false;
				}
				if ($this.attr('min') != undefined)
				{
					if (val < $this.attr('min'))
						is_valid = false;
				}
				if (!is_valid)
				{
					if ($this.data('validate-reset'))
						$this.val('');

					event.preventDefault();
					event.stopPropagation();
				}
			});
		});

		$('.wsko-ajax-input:not(.wsko-ap-init)').addClass('wsko-ap-init').each(function(){
			var $this = $(this);
			if ($this.is("input") || $this.is("textarea") || $this.is("select"))
			{
				if ($this.hasClass('wsko-ajax-slider'))
				{
					//var $slider = $('<div class="wsko-slider-obj"></div>').prepend($this);
					var slider = noUiSlider.create($this.get(0), {
						start: $this.val(),
						connect: true,
						step: 1,
						orientation: 'horizontal', // 'horizontal' or 'vertical'
						range: {
							'min': $this.data('min'),
							'max': $this.data('max')
						},
						format: wNumb({
							decimals: 0
						})
					}).on('change wsko_trigger_save', function(){
						var val = $this.get(0).noUiSlider.get();
						if (val != $this.data('wsko-old-val'))
						{
							var silent = $this.data('silent') ? true : false;
							var old_val = $this.data('wsko-old-val');
							$this.data('wsko-old-val', val);
							window.wsko_post_element({action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_data.save_ajax_input_nonce},
								function(res){
									$this.attr('disabled', false);
									if (!res.success)
										$this.val(old_val);
									else if (reload_real)
										location.reload(true);
									if (res.dynamic_elements)
									{
										$.each(res.dynamic_elements, function($k, $v){
											$($k).html($v);
										});
									}
									if (res.success)
										$this.trigger('wsko_ajax_input_save_success');
									else
										$this.trigger('wsko_ajax_input_save_fail');
									return silent;
								}, function(){
									$this.trigger('wsko_ajax_input_save_fail');
									$this.attr('disabled', false);
									$this.val(old_val);
									return silent;
								}, silent ? false : $this, reload_real ? false : reload);
						}
					});
				}
				else
				{
					$this.data('wsko-old-val', $this.val());
					if ($this.is('select'))
					{
						if ($this.data('multi-parent'))
						{
							var $multi_parent = $this.closest($this.data('multi-parent'));
							$this.on('change wsko_trigger_save', function(){
								var timeout = false;
								if (timeout = $multi_parent.data('wsko-ajax-submit-timeout'))
									clearTimeout(timeout);
								$multi_parent.data('wsko-ajax-submit-timeout', setTimeout(function(){
									val = {},
									alert_m = $(this).data('alert'),
									alert_send = $(this).data('alert-send') ? true : false;
									
									$multi_parent.find('.wsko-ajax-input[data-multi-parent="'+$this.data('multi-parent')+'"]').each(function(index){
										if ($(this).val())
											val[$(this).data('key')] = $(this).val();
									});
									//val = val.join(',');

									var silent = $this.data('silent') ? true : false;
									var old_val = $this.data('wsko-old-val');
									$this.data('wsko-old-val', val);
									$this.attr('disabled', true);
									
									var alert_r = false,
									data = {action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, deep:true, nonce: wsko_data.save_ajax_input_nonce};
									if (!alert_m || confirm(alert_m))
										alert_r = alert_m ? true : false;
									if (alert_m && alert_send)
										data['alert'] = alert_r;
									if (alert_m && !alert_send && !alert_r)
										return;
									if ($this.data('wsko-arg'))
										data['arg'] = $this.data('wsko-arg');
									window.wsko_post_element(data,
										function(res){
											$this.attr('disabled', false);
											if (!res.success)
												$this.attr('checked', old_val && old_val != 'false' ? true : false);
											if (res.dynamic_elements)
											{
												$.each(res.dynamic_elements, function($k, $v){
													$($k).html($v);
												});
											}
											if (res.success)
												$this.trigger('wsko_ajax_input_save_success');
											else
												$this.trigger('wsko_ajax_input_save_fail');
											return silent;
										}, function(){
											$this.trigger('wsko_ajax_input_save_fail');
											$this.attr('disabled', false);
											$this.attr('checked', old_val && old_val != 'false' ? true : false);
											return silent;
										}, silent ? false : $this, $this.data('reload') ? true : false);
								}, 1000));
							});
						}
						else
						{
							$this.on('change wsko_trigger_save', function(){
								var val = '',
								reload = $(this).data('reload') ? true : false,
								reload_real = $(this).data('reload-real') ? true : false;
								$(this).find("option:selected").each(function(index){ val += (index==0?'':',')+$(this).val(); });
								if (val != $this.data('wsko-old-val'))
								{
									var silent = $this.data('silent') ? true : false;
									var old_val = $this.data('wsko-old-val');
									$this.data('wsko-old-val', val);
									window.wsko_post_element({action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_data.save_ajax_input_nonce},
										function(res){
											$this.attr('disabled', false);
											if (!res.success)
												$this.val(old_val);
											else if (reload_real)
												location.reload(true);
											if (res.dynamic_elements)
											{
												$.each(res.dynamic_elements, function($k, $v){
													$($k).html($v);
												});
											}
											if (res.success)
												$this.trigger('wsko_ajax_input_save_success');
											else
												$this.trigger('wsko_ajax_input_save_fail');
											return silent;
										}, function(){
											$this.trigger('wsko_ajax_input_save_fail');
											$this.attr('disabled', false);
											$this.val(old_val);
											return silent;
										}, silent ? false : $this, reload_real ? false : reload);
								}
							});
						}
					}
					else if ($this.is('[type="checkbox"]'))
					{
						if ($this.data('multi-parent'))
						{
							var $multi_parent = $this.closest($this.data('multi-parent'));
							$this.on('change wsko_trigger_save', function(){
								var timeout = false;
								if (timeout = $multi_parent.data('wsko-ajax-submit-timeout'))
									clearTimeout(timeout);
								$multi_parent.data('wsko-ajax-submit-timeout', setTimeout(function(){
									val = [],
									alert_m = $(this).data('alert'),
									alert_send = $(this).data('alert-send') ? true : false;
									
									$multi_parent.find('.wsko-ajax-input[type="checkbox"][data-multi-parent="'+$this.data('multi-parent')+'"]:checked').each(function(index){
										val.push($(this).val());
									});
									val = val.join(',');

									var silent = $this.data('silent') ? true : false;
									var old_val = $this.data('wsko-old-val');
									$this.data('wsko-old-val', val);
									$this.attr('disabled', true);
									
									var alert_r = false,
									data = {action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_data.save_ajax_input_nonce};
									if (!alert_m || confirm(alert_m))
										alert_r = alert_m ? true : false;
									if (alert_m && alert_send)
										data['alert'] = alert_r;
									if (alert_m && !alert_send && !alert_r)
										return;
									if ($this.data('wsko-arg'))
										data['arg'] = $this.data('wsko-arg');
									window.wsko_post_element(data,
										function(res){
											$this.attr('disabled', false);
											if (!res.success)
												$this.attr('checked', old_val && old_val != 'false' ? true : false);
											if (res.dynamic_elements)
											{
												$.each(res.dynamic_elements, function($k, $v){
													$($k).html($v);
												});
											}
											if (res.success)
												$this.trigger('wsko_ajax_input_save_success');
											else
												$this.trigger('wsko_ajax_input_save_fail');
											return silent;
										}, function(){
											$this.trigger('wsko_ajax_input_save_fail');
											$this.attr('disabled', false);
											$this.attr('checked', old_val && old_val != 'false' ? true : false);
											return silent;
										}, silent ? false : $this, $this.data('reload') ? true : false);
								}, 1000));
							});
						}
						else
						{
							$this.on('change wsko_trigger_save', function(){
								var timeout = false;
								if (timeout = $this.data('wsko-ajax-submit-timeout'))
									clearTimeout(timeout);
								$this.data('wsko-ajax-submit-timeout', setTimeout(function(){
									var multi_parent = $this.data('multi-parent'),
									val = multi_parent ? [] : $this.is(':checked'),
									alert_m = $(this).data('alert'),
									alert_send = $(this).data('alert-send') ? true : false;

									if (val != $this.data('wsko-old-val'))
									{
										var silent = $this.data('silent') ? true : false;
										var old_val = $this.data('wsko-old-val');
										$this.data('wsko-old-val', val);
										$this.attr('disabled', true);
										
										var alert_r = false,
										data = {action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_data.save_ajax_input_nonce};
										if (!alert_m || confirm(alert_m))
											alert_r = alert_m ? true : false;
										if (alert_m && alert_send)
											data['alert'] = alert_r;
										if (alert_m && !alert_send && !alert_r)
											return;
										if ($this.data('wsko-arg'))
											data['arg'] = $this.data('wsko-arg');
										window.wsko_post_element(data,
											function(res){
												$this.attr('disabled', false);
												if (!res.success)
													$this.attr('checked', old_val && old_val != 'false' ? true : false);
												if (res.dynamic_elements)
												{
													$.each(res.dynamic_elements, function($k, $v){
														$($k).html($v);
													});
												}
												if (res.success)
													$this.trigger('wsko_ajax_input_save_success');
												else
													$this.trigger('wsko_ajax_input_save_fail');
												return silent;
											}, function(){
												$this.trigger('wsko_ajax_input_save_fail');
												$this.attr('disabled', false);
												$this.attr('checked', old_val && old_val != 'false' ? true : false);
												return silent;
											}, silent ? false : $this, $this.data('reload') ? true : false);
									}
								}, 1000));
							});
						}
					}
					else if ($this.is('[type="range"]'))
					{
						$this.on('change wsko_trigger_save', function(){
							var val = $this.val();
							if (val != $this.data('wsko-old-val'))
							{
								var silent = $this.data('silent') ? true : false;
								var old_val = $this.data('wsko-old-val');
								$this.data('wsko-old-val', val);
								window.wsko_post_element({action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_data.save_ajax_input_nonce},
									function(res){
										$this.attr('disabled', false);
										if (!res.success)
											$this.val(old_val);
										if (res.dynamic_elements)
										{
											$.each(res.dynamic_elements, function($k, $v){
												$($k).html($v);
											});
										}
										if (res.success)
											$this.trigger('wsko_ajax_input_save_success');
										else
											$this.trigger('wsko_ajax_input_save_fail');
										return silent;
									}, function(){
										$this.trigger('wsko_ajax_input_save_fail');
										$this.attr('disabled', false);
										$this.val(old_val);
										return silent;
									}, silent ? false : $this, false);
							}
						})
					}
					else
					{
						$this.on('focusout wsko_trigger_save', function(){
							var val = $this.val();
							if (val != $this.data('wsko-old-val'))
							{
								var silent = $this.data('silent') ? true : false;
								var old_val = $this.data('wsko-old-val');
								$this.data('wsko-old-val', val);
								window.wsko_post_element({action: 'wsko_save_ajax_input', target: $this.data('wsko-target'), setting: $this.data('wsko-setting'), val: val, nonce: wsko_data.save_ajax_input_nonce},
									function(res){
										$this.attr('disabled', false);
										if (!res.success)
											$this.val(old_val);
										if (res.dynamic_elements)
										{
											$.each(res.dynamic_elements, function($k, $v){
												$($k).html($v);
											});
										}
										if (res.success)
											$this.trigger('wsko_ajax_input_save_success');
										else
											$this.trigger('wsko_ajax_input_save_fail');
										return silent;
									}, function(){
										$this.trigger('wsko_ajax_input_save_fail');
										$this.attr('disabled', false);
										$this.val(old_val);
										return silent;
									}, silent ? false : $this, false);
							}
						});
					}
				}
			}
		});

		$('#wsko_content_optimizer_modal .wsko-modal-box:not(.wsko-init)').addClass('wsko-init').click(function(event){ event.stopPropagation(); });
		$('#wsko_content_optimizer_modal .wsko-modal-back-to-multi:not(.wsko-init)').addClass('wsko-init').click(function(event){ 
			event.preventDefault();
			var $modal = $('#wsko_content_optimizer_modal');
			$modal.find('.wsko-modal-content').hide();
			$modal.find('.wsko-modal-multi-container-bar').hide();
			$modal.find('.wsko-modal-multi-container').fadeIn();
		});
		
		//Keyword Monitoring
		$('.wsko-set-monitoring-keyword:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			set = $this.data('set') && $this.data('set') != 'false';
			window.wsko_post_element({action: set ? 'wsko_add_monitoring_keyword' : 'wsko_remove_monitoring_keyword', keyword: $this.data('keyword'), nonce: set ? wsko_data.add_monitoring_keyword_nonce : wsko_data.remove_monitoring_keyword_nonce}, function(res){
					if (res.success)
					{
						$this.data('set', !set);
						if (set) {
							$this.find('i').removeClass('fa-star-o').addClass('fa-star');
						} else {
							$this.find('i').removeClass('fa-star').addClass('fa-star-o');
						}
						var title = $this.attr('title');
						$this.attr('title', $this.attr('data-original-title'));
						$this.attr('data-original-title', title);
						return true;
					}
				}, false, $this, false);
		});

		//Keyword Research
		/*$('.wsko-do-keyword-research-link:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $this = $(this);
		});*/

		//Onpage
		/*
		$('.wsko-onpage-include-pt:not(.wsko_pt_init)').addClass('wsko_pt_init').change(function(event){
			var $this = $(this);
			if ($this.is(':checked'))
			{
				$this.closest('.wsko-onpage-include-pt-wrapper').find('.wsko-onpage-include-pt-add').show();
			}
			else
			{
				$this.closest('.wsko-onpage-include-pt-wrapper').find('.wsko-onpage-include-pt-add').hide();
			}
		});
		*/
		
		//Progress Bars
		$('.wsko-circle-progress:not(.wsko_init)').addClass('wsko_init').circliful();
		
		//Datatables
		function wsko_resolve_table_ajax_fields()
		{
			var posts = [];
			var grouped_posts = {};
			$('.wsko-table-lazy-ajax-field').each(function(index){
				var $this = $(this)
				group = $this.data('group'),
				action = $this.data('ajax'),
				nonce = $this.data('nonce'),
				arg = $this.data('ajax-data');
				if (group)
				{
					var key = action+':'+group;
					if (grouped_posts[key])
						grouped_posts[key].arg.push(arg);
					else
						grouped_posts[key] = {action: action, arg: [arg], nonce: nonce};
				}
				else
				{
					window.wsko_post_element({action: action, arg: arg, nonce: nonce}, 
						function(res){
							if (res.success)
							{
								$this.replaceWith(res.data);
								return true;
							}
						},
						function()
						{
							$this.remove();
						}, false, false);
				}
			});
			if (grouped_posts)
			{
				$.each(grouped_posts, function(k, v){
					window.wsko_post_element({action: v.action, arg: v.arg, nonce: v.nonce}, 
						function(res){
							if (res.success)
							{
								$.each(res.data, function(k2, v2){
									$('.wsko-table-lazy-ajax-field[data-ajax="'+v.action+'"][data-ajax-data="'+k2+'"]').replaceWith(v2);
								});
								return true;
							}
						},
						function()
						{
							$.each(v.arg, function(k2, v2){
								$('.wsko-table-lazy-ajax-field[data-ajax="'+v.action+'"][data-ajax-data="'+v2+'"]').remove();
							});
						}, false, false);
				});
			}
		}
		$('.wsko-tables:not(.wsko-init)').addClass('wsko-init').each(function(index){
			var $this = $(this),
			ajax_table = $this.hasClass('wsko-ajax-tables'),
			scrollY = $this.data('scrollY'),
			scrollX = $this.data('scrollX'),
			$wrapper = $this.closest('.wsko-table-wrapper'),
			order = false;
			if ($this.data('def-order') != undefined)
			{
				order = [[parseInt($this.data('def-order')), $this.data('def-orderdir')]];
			}
			var columns = [];
			$this.find('thead tr th').each(function(index){
				var data = {data: $(this).data('name')};
				var width = false;
				if (width = $(this).data('width'))
					data['width'] = width;
				if (class_n = $(this).data('class'))
					data['className'] = class_n;
				columns.push(data);
			});
			/*$wrapper.find('.wsko-table-custom-filter').each(function(index){
				var $filter = $(this),
				$field = $filter.find('.wsko-table-custom-filter-input');
				d.custom_filter.push({key: $filter.data('name'), val: $field.is('[type="checkbox"]') ? $field.is(':checked') : $field.val(), comp: $filter.data('comp')});
			});*/
			var args = {};
			if (ajax_table)
			{
				args = {
					"order": order ? order : [[ 0, "desc" ]],
					"processing": true,
					"serverSide": true,
					//"scrollY": scrollY ? scrollY : false,
					//"scrollX": scrollX ? scrollX : false,
					"searchDelay": 1000,
					"pageLength": 25,
					"columns": columns,
					"dom": "TBlfrtip",
					"buttons": [
						'copy', 'csv', 'excel', 'pdf', 'print'
					],
					"ajax": {
						"url": wsko_admin_data.ajaxurl,
						"type": "POST",
						"data": function(d) {
							d.action = $this.data('action');
							d.nonce = $this.data('nonce');
							if ($this.data('arg'))
								d.arg = $this.data('arg');
							if ($this.data('arg2'))
								d.arg2 = $this.data('arg2');
							d.wsko_controller = wsko_admin_data.controller;
							d.wsko_controller_sub = wsko_admin_data.subpage;
							d.custom_filter = [];
							$wrapper.find('.wsko-table-custom-filter').each(function(index){
								var $filter = $(this),
								$field = $filter.find('.wsko-table-custom-filter-input');
								d.custom_filter.push({key: $filter.data('name'), val: $field.is('[type="checkbox"]') ? $field.is(':checked') : $field.val(), comp: $filter.data('comp')});
							});
						},
						"dataSrc": function(json) {
							if (json.dynamic_elements)
							{
								$.each(json.dynamic_elements, function($k, $v){
									$($k).html($v);
								});
							}
							return json.data;
						}
					},
					"drawCallback": function( settings ) {
						var r_class = $this.data('row-class');
						if (r_class)
						{
							$this.find('tbody tr').addClass(r_class);
						}
						$sort = $wrapper.find('thead th.sorting_asc,thead th.sorting_desc');
						$wrapper.find('tfoot th').removeClass('sorting_asc sorting_desc');
						if ($sort.length)
						{
							var sortClass = "sorting_desc";
							if ($sort.hasClass('sorting_asc'))
								sortClass = "sorting_asc";
							$wrapper.find('tfoot tr').children().eq($sort.index()).addClass(sortClass);
						}
						wsko_resolve_table_ajax_fields();
						window.wsko_init_core();
						$this.find('.wsko-table-style-trigger').each(function(index){
							var $trigger = $(this),
							$target = $([]);
							if ($trigger.data('all'))
								$target = $trigger.closest('tr').find('td');
							else
							$target = $trigger.closest('td');
							$target.css($trigger.data('attr'), $trigger.data('val')).addClass('wsko-column-styled');
							$trigger.remove();
						});
					}
				};
			}
			else
			{
				args = {
					"order": order ? order : [[ 0, "desc" ]],
					"pageLength": 25,
					"drawCallback": function( settings ) {
						var r_class = $this.data('row-class');
						if (r_class)
						{
							$this.find('tbody tr').addClass(r_class);
						}
						$sort = $this.find('thead th.sorting_asc,thead th.sorting_desc');
						$this.find('tfoot th').removeClass('sorting_asc sorting_desc');
						if ($sort.length)
						{
							var sortClass = "sorting_desc";
							if ($sort.hasClass('sorting_asc'))
								sortClass = "sorting_asc";
							$this.find('tfoot tr').children().eq($sort.index()).addClass(sortClass);
						}
						wsko_resolve_table_ajax_fields();
						window.wsko_init_core();
					},
					//"scrollY": scrollY ? scrollY : false,
					//"scrollX": scrollX ? scrollX : false,
					"dom": "TBlfrtip",
					"buttons": [
						'copy', 'csv', 'excel', 'pdf', 'print'
					],
					"columns": columns
				};
				if (!window.wsko_datatable_search_function_added)
				{
					window.wsko_datatable_search_function_added = true;
					$.fn.dataTable.ext.search.push(
						function( settings, data, dataIndex ) {
							var $wrapper = $('#'+settings.sTableId).closest('.wsko-table-wrapper'),
							result = true;
							$wrapper.find('.wsko-table-custom-filter').each(function(index){
								var $filter = $(this),
								$field = $filter.find('.wsko-table-custom-filter-input'),
								type = $filter.data('comp'),
								val = $field.is('[type="checkbox"]') ? $field.is(':checked') : $field.val(),
								key = $filter.data('name');
								switch(type)
								{
									case 'co':
										var res = false;
										$.each(data, function(k, v){
											v = ($(settings.aoData[dataIndex].anCells[k]).data('order') ? $(settings.aoData[dataIndex].anCells[k]).data('order') : v);
											if (k == key && (v+'').includes(val))
												res = true;
										});
										result = res;
										break;
									case 'eq':
										var res = false;
										$.each(data, function(k, v){
											v = ($(settings.aoData[dataIndex].anCells[k]).data('order') ? $(settings.aoData[dataIndex].anCells[k]).data('order') : v);
											if (k == key && v == val)
												res = true;
										});
										result = res;
										break;
									case 'ra':
										val = (val+'').split(':');
										val[0] = parseInt(val[0]);
										val[1] = parseInt(val[1]);
										var res = false;
										$.each(data, function(k, v){
											v = ($(settings.aoData[dataIndex].anCells[k]).data('order') ? $(settings.aoData[dataIndex].anCells[k]).data('order') : v);
											if (k == key && v >= val[0] && v <= val[1])
												res = true;
										});
										result = res;
										break;
									case 'set':
										val = (val+'').split(':');
										var res = false;
										$.each(data, function(k, v){
											v = ($(settings.aoData[dataIndex].anCells[k]).data('order') ? $(settings.aoData[dataIndex].anCells[k]).data('order') : v);
											if (k == key && ((v && val) || (!v && !val)))
												res = true;
										});
										result = res;
										break;
								}
							});
							return result;
						}     
					);
				}
			}
			var dataTable = $this.DataTable(args);
			$wrapper.find('.wsko-reload-ajax-table').click(function(event){
				event.preventDefault();
				if (ajax_table)
					dataTable.ajax.reload();
			});
			$wrapper.find('tfoot th').click(function(event){
				event.preventDefault();
				$wrapper.find('thead tr').children().eq($(this).index()).click(); 
			});
			var search_timeout;
			$wrapper.find('input[type="search"]').off().on("keydown", function (event) {
				if (search_timeout)
					clearTimeout(search_timeout);
				search_timeout = setTimeout(function(){
					dataTable.search($wrapper.find('input[type="search"]').val()).draw();
				}, 1000);
			});
			$wrapper.find('.wsko-table-add-filter').click(function(event){
				event.preventDefault();

				var $filter = $(this),
				type = $filter.data('type');
				var arg = false;
				if (type == 'select')
					arg = $filter.data('values');
				else if (type == 'number_range')
					arg = $filter.data('max');
				wsko_add_table_filter($filter.data('name'), $filter.data('title'), type, arg, "", false);
			});
			$this.on('wsko_add_external_filter', function(e, arg){
				var arg_a = arg.split(',');
				$.each(arg_a, function(k, arg){
					var args = arg.split(':'),
					$filter = $wrapper.find('.wsko-table-add-filter[data-name="'+args[0]+'"]'),
					val = "",
					type = $filter.data('type');
					if (args.length == 1)
						val = "";
					else if (args.length == 2)
						val = args[1];
					else if (args.length == 3)
						val = [args[1],args[2]];
					if ($filter.length != 0)
					{
						var arg = false;
						if (type == 'select')
							arg = $filter.data('values');
						else if (type == 'number_range')
							arg = $filter.data('max');
						wsko_add_table_filter(args[0], $filter.data('title'), type, arg, val, true);
					}
				});
			});
			$this.on('wsko_data_source_updated', function(e, arg){
				if (ajax_table) dataTable.ajax.reload(); else dataTable.draw();
			});
			$this.parents('.tab-pane,.wsko-tab').each(function(index){
				var $pane = $(this);
				id = $pane.attr('id');
				if (id)
				{
					$('a[href="#'+id+'"][data-toggle="tab"]').on('shown.bs.tab', function(event){
						dataTable.columns.adjust();
					});
					$('a.wsko-nav-link[href="#'+id+'"]').click(function(event){
						dataTable.columns.adjust();
					});
				}
			});
			function wsko_add_table_filter(name, title, type, arg, value, replace)
			{
				if (value == undefined)
					value = "";
				var $old_filter = $wrapper.find('.wsko-table-custom-filter[data-name="'+name+'"]');
				if ($old_filter.length != 0)
				{
					if (replace)
					{
						$old_filter.remove();
					}
					else
						return;
				}
				var $temp = $('<div class="wsko-table-custom-filter row" data-name="'+name+'"></div>');
				switch(type)
				{
					case 'text':
						$temp.data('comp', 'co');
						$temp.append('<div class="col-sm-2 col-xs-3"><label>'+title+'</label></div>');
						$temp.append('<div class="col-sm-9 col-xs-8"><input class="form-control wsko-table-custom-filter-input" type="text" value="'+value+'"></div>');
						break;
					case 'exact':
						$temp.data('comp', 'eq');
						$temp.append('<div class="col-sm-2 col-xs-3"><label>'+title+'</label></div>');
						$temp.append('<div class="col-sm-9 col-xs-8"><input class="form-control wsko-table-custom-filter-input" type="text" value="'+value+'"></div>');
						break;
					case 'number':
						$temp.data('comp', 'eq');
						$temp.append('<div class="col-sm-2 col-xs-3"><label>'+title+'</label></div>');
						$temp.append('<div class="col-sm-9 col-xs-8"><input class="form-control wsko-table-custom-filter-input" type="number" value="'+value+'"></div>');
						break;
					case 'number_range':
						$temp.data('comp', 'ra');
						$temp.append('<div class="col-sm-2 col-xs-3"><label>'+title+'</label></div>');					
						$temp.append('<div class="col-sm-9 col-xs-8"><div class="wsko-range-slider" data-max="'+arg+'" data-val1="'+(value?value[0]:"")+'" data-val2="'+(value?value[1]:"")+'" data-label="true"><input class="wsko-range-slider-input wsko-table-custom-filter-input" type="hidden" value="'+(value?value[0]+':'+value[1]:"")+'"></div></div>');
						break;
					case 'select':
						$temp.data('comp', 'eq');
						$temp.append('<div class="col-sm-2 col-xs-3"><label>'+title+'</label></div>');
						var input ='<div class="col-sm-9 col-xs-8"><select class="form-control wsko-table-custom-filter-input">';
						$.each(arg, function (k, v){
							input += '<option value="'+k+'" '+(value == k ? 'selected' : '')+'>'+v+'</option>';
						});
						input += '</select></div>';
						$temp.append(input);
						break;
					case 'set':
						$temp.data('comp', 'set');
						$temp.append('<div class="col-sm-2 col-xs-3"><label>'+title+'</label></div>');
						$temp.append('<div class="col-sm-9 col-xs-8"><input class="form-control wsko-table-custom-filter-input" type="checkbox" '+(value=='on'?'checked':'')+'> Is set</div>');
						break;
				}
				$temp.append('<div class="col-sm-1 col-xs-1 align-right"><a href="#" class="wsko-delete-custom-filter pull-right"><i class="fa fa-times"></i></a></div>').find('.wsko-delete-custom-filter').click(function(event){
					$(this).parents('.wsko-table-custom-filter').fadeOut('fast', function(){ $(this).remove(); if (ajax_table) dataTable.ajax.reload(); else dataTable.draw(); $wrapper.find('.wsko-table-add-filter[data-name="'+name+'"]').removeClass('wsko-disabled'); });
				});
				$temp.find('.wsko-table-custom-filter-input').change(function(){ if (ajax_table) dataTable.ajax.reload(); else dataTable.draw(); });
				$wrapper.find('.wsko-table-filter-box').append($temp);
				$wrapper.find('.wsko-table-add-filter[data-name="'+name+'"]').addClass('wsko-disabled');
				window.wsko_init_core();
				if (value != "" || type == 'select')
				{
					if (ajax_table) dataTable.ajax.reload(); else dataTable.draw();
				}
			}
		});
		$('.wsko-external-table-filter:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			$($this.data('table')).trigger('wsko_add_external_filter', [$this.data('val')]);
		});
		$('.wsko-range-slider:not(.wsko-init)').addClass('wsko-init').each(function(index){
			var $this = $(this),
			$slider = $this.prepend('<div class="wsko-slider-obj"></div>'),
			min = $this.data('min') || $this.data('min') == '0' ? $this.data('min') : 0,
			max = $this.data('max') || $this.data('max') == '0' ? $this.data('max') : 100;
			if (min == max)
				max++;
			var start = $this.data('val1') || $this.data('val1') == '0' ? $this.data('val1') : min,
			end = $this.data('val2') || $this.data('val2') == '0' ? $this.data('val2') : max;
			if (start < min)
				start = min;
			if (end > max)
				end = max;
			var slider = noUiSlider.create($slider.get(0), {
				start: [start, end],
				connect: true,
				step: 1,
				orientation: 'horizontal', // 'horizontal' or 'vertical'
				range: {
					'min': min,
					'max': max
				},
				format: wNumb({
					decimals: 0
				})
			}).on('change', function(){
				var val = $slider.get(0).noUiSlider.get();
				$this.find('.wsko-range-slider-input').val(val.join(':')).trigger('change');
				$this.find('.wsko-range-slider-from').html(Math.round(val[0] * 100) / 100);
				$this.find('.wsko-range-slider-to').html(Math.round(val[1] * 100) / 100);
			});
			$this.find('.wsko-range-slider-input').val(start+':'+end);
			if ($this.data('label'))
			{
				$slider.prepend('<span class="wsko-range-val"><span class="wsko-range-slider-from">'+start+'</span> - <span class="wsko-range-slider-to">'+end+'</span></span>');
				
				$this.find('.wsko-range-slider-from').html(Math.round(start * 100) / 100);
				$this.find('.wsko-range-slider-to').html(Math.round(end * 100) / 100);
			}
		});

		//DataTables Export fix
		$('.wsko-tables.wsko-init').on( 'draw.dt', function () {
			var $this = $(this);
			$export_old = $this.closest('.wsko-table-wrapper').find('.dt-buttons');
			$export_new = $this.closest('.wsko-table-wrapper').find('.wsko-table-controls .table-export-link .dropdown-menu');
			$export_old.appendTo($export_new);
			if ($export_old.length)
				$this.closest('.wsko-table-wrapper').find('.wsko-table-controls .table-export-link').show();
			$this.find('.wsko-table-row-click:not(.wsko-init)').addClass('wsko-init').click(function(event){
				event.preventDefault();
				var $row_link = $(this);
				var $wrapper = $row_link.closest('.wsko-table-wrapper').hide();
				$($row_link.data('container')).show();
				var $back_link = $($row_link.data('back')).show();
				if (!$back_link.hasClass('wsko-init'))
				{
					$back_link.addClass('wsko-init').click(function(event){
						event.preventDefault();
						$back_link.closest('.wsko-multi-table-wrapper').find('.wsko-multi-table-item').hide();
						$wrapper.show();
						$back_link.hide();
					});
				}
			});
		});

		if (window.wsko_init_admin)
		{
			//Init Admin
			window.wsko_init_admin();
		}
		
		//Notifications
		$('.wsko-setup-wrapper .wsko-setup-notifications-wrapper:not(.wsko-notif-init)').addClass('wsko-notif-init').click(function(event){ $(this).fadeOut(); });
		$('#wsko_content_optimizer_modal.wsko-modal-active .wsko-co-notifications-overlay:not(.wsko-notif-init)').addClass('wsko-notif-init').click(function(event){ $(this).fadeOut(); });
		$('#wsko_admin_view_ajax_notification:not(.wsko-notif-init)').addClass('wsko-notif-init').click(function(event){ $(this).fadeOut(); });
		$('.wsko-content-optimizer.wsko-co-widget .wsko-co-notifications-overlay:not(.wsko-notif-init)').addClass('wsko-notif-init').click(function(event){ $(this).fadeOut(); });
		
		//Code Highlighting
		$('.wsko-previewable-textarea:not(.wsko-init)').addClass('wsko-init').each(function(index) {
			var $this = $(this),
			highlights = $this.data('highlights'),
			$area = $this.find('textarea'),
			$preview = $this.find('.wsko-textarea-preview');
			console.log(highlights);
			if (!highlights)
				highlights = [];
			//highlights.push({color:'red', regex: /(&lt;\?)(.*?)(\?&gt;)/g, type: 2});
			//highlights.push({color:'red', regex: /(test)/g, type: 1});
			//highlights.push({color:'red', regex: /(<b>)(.*?)(<\/b>)/g, type: 2});
			$area.on('input propertychanged',function(){
				var preview_html = $(this).val();
				//preview_html = wsko_replace_html(preview_html);
				preview_html = preview_html.replace(/</g, '&lt;');
				preview_html = preview_html.replace(/>/g, '&gt;');
				preview_html = preview_html.replace(/\\r/g, '<br/>');
				preview_html = preview_html.replace(/\\n/g, '<br/>');
				$.each(highlights, function(k, v){
					switch(v.type)
					{
						case 1:
						preview_html = preview_html.replace(new RegExp(v.regex, 'gi'), function(match, p1, offest, string) { return '<p style="color:'+v.color+'">'+p1+'</p>'; });
						break;
						case 2:
						preview_html = preview_html.replace(new RegExp(v.regex, 'gi'), function(match, p1, p2, p3, offest, string) { return '<p style="color:'+v.color+'">'+p1+'</p>'+p2+'<p style="color:'+v.color+'">'+p3+'</p>'; });
						break;
						case 3:
						preview_html = preview_html.replace(new RegExp(v.regex, 'gi'), function(match, p1, p2, p3, p4, offest, string) { return '<p style="color:'+v.color+'">'+(p1?p1:'')+'</p>'+(p2?p2:'')+'<p style="color:'+v.color+'">'+(p3?p3:'')+(p4?p4:'')+'</p>'; });
						break;
					}
				});
				$preview.html(preview_html);
				$preview.scrollTop($(this).scrollTop());
			}).focus(function(){
				//$preview.hide();
			}).focusout(function(){
				$preview.show();
			}).on('scroll', function () {
				$preview.scrollTop($(this).scrollTop());
			});
		});
		
		//Knowledge Base
		$('.wsko-open-knowledge-base-article:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $link = $(this),
			$modal = $('#wsko_knowledge_base_modal').addClass('wsko-modal-active').trigger('show'),
			$content = $modal.find('.wsko-modal-content').html('');
			$('#wsko_knowledge_base_modal .wsko-modal-loader').show();
			window.wsko_post_element({action: 'wsko_get_knowledge_base_article', article: $link.data('article'), type: $link.data('type'), nonce: wsko_data.knowledge_base_nonce}, 
				function(res){
					$('#wsko_knowledge_base_modal .wsko-modal-loader').hide();
					if (res.success)
					{
						$content.html(res.view);
						return true;
					}
					else
					{
						$content.html("Article could not be loaded");
					}
				},
				function()
				{
					$content.html("A Server Error occured. Please try again.");
					$('#wsko_knowledge_base_modal .wsko-modal-loader').hide();
				}, false, false);
		});
		$('#wsko_knowledge_base_modal .wsko-modal-box:not(.wsko-init)').addClass('wsko-init').click(function(event){ event.stopPropagation(); });
		$('#wsko_knowledge_base_modal:not(.wsko-init)').addClass('wsko-init').click(function(event){
			var $modal = $('#wsko_knowledge_base_modal').removeClass('wsko-modal-active').trigger('hidden');
		});
		$('#wsko_knowledge_base_modal .wsko-modal-close:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $modal = $('#wsko_knowledge_base_modal').removeClass('wsko-modal-active').trigger('hidden');
		});
		var timeout;
		var kb_instant_search = false;
		$('.wsko-search-knowledge-base:not(.wsko-init)').addClass('wsko-init').on('change keyup', function(event){
			var $this = $(this),
			$wrap = $this.closest('.wsko-search-knowledge-base-wrapper');//.find('.wsko-search-knowledge-base-list');
			var timeout = $(this).data('search_timeout');
			if (timeout)
				clearInterval(timeout);
			var cats = [];
			$wrap.find('.wsko-search-knowledge-base-cat').each(function(index){
				if ($(this).is(':checked'))
				{
					cats.push($(this).val());
				}
			});
			$this.data('search_timeout', setTimeout(function(){
				var $overlay = $wrap.closest('.wsko-knowledge-base-wrapper').find('.wsko-kb-loading-overlay').show();
				window.wsko_post_element({action: 'wsko_search_knowledge_base', search:$this.val(), cats: cats, page: $wrap.find('.wsko-search-knowledge-base-page').val(), nonce:$this.data('nonce')},
					function(res){
						if (res.success)
						{
							$wrap.replaceWith($(res.view));
						}
						$overlay.hide();
						return true;
					}, function(){
							$overlay.hide();
						return true;
					}, false, false);
			}, kb_instant_search ? 1 : 1000));
		});
		$('.wsko-search-knowledge-base-cat:not(.wsko-init)').addClass('wsko-init').change(function(){
			var $this = $(this);
			if ($this.is(':checked'))
				$this.closest('label').addClass('wsko-kb-cat-selected');
			else
				$this.closest('label').removeClass('wsko-kb-cat-selected');
			kb_instant_search = true;
			$this.closest('.wsko-search-knowledge-base-wrapper').find('.wsko-search-knowledge-base').change();
			kb_instant_search = false;
		});
		$('.wsko-search-knowledge-base-page-set:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			$wrap = $this.closest('.wsko-search-knowledge-base-wrapper'),
			$field = $wrap.find('.wsko-search-knowledge-base-page');
			if ($field.val() != $this.data('page'))
				$field.val($this.data('page'));
			kb_instant_search = true;
			$wrap.find('.wsko-search-knowledge-base').change();
			kb_instant_search = false;
		});
		$('.wsko-kb-rate-article:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			if ($this.data('good') && $this.data('good') != 'false')
				rating = 'good';
			else
				rating = 'bad';
			window.wsko_post_element({action: 'wsko_rate_knowledge_base_article', post: $this.data('post'), type: rating, nonce:$this.data('nonce')},
				function(res){
					if (res.success)
					{
						$this.closest('.kb-helpful').html('Thank you!');
					}
					return true;
				}, function(){
					return true;
				}, $this.closest('.kb-helpful'), false);
		});
		
		//NPS
		$('.nps-form:not(.wsko-init)').addClass('wsko-init').each(function(index){
			var $form = $(this),
			$container = $form.closest('.nps-wrapper');
			$form.submit(function(event){
				event.preventDefault();
				window.wsko_post_element({action: 'wsko_send_nps_feedback', rating: $form.find('.nps-rating:checked').val(), msg: $form.find('.nps-msg').val(), nonce: $form.data('nonce')}, function(res){
					if (res.success)
					{
						$container.find('.nps-second-step').show();           
						$container.find('.nps-first-step').hide();    
					}
				}, function(){}, $container, false);
			}); 
			if ($container.hasClass('active'))  
				$container.delay('5000').addClass('in');
		
			$container.find('.nps-close').click(function(event){
				event.preventDefault();        
				$container.removeClass('active').fadeOut( 1000 );
			});
		
			$container.find('.nps-rating-wrapper input').change(function() {
				$container.find('.nps-inner-form').show();
			});
			/*$container.find('.nps-submit').click(function(event) {
				//event.stopPropagation();        
				event.preventDefault();         
				$container.find('.nps-second-step').show();           
				$container.find('.nps-first-step').hide();     
			});   */
		});

		//Other
		$('.wsko-indent-textarea:not(.wsko-indent-init)').addClass('wsko-indent-init').on('keydown', function(event){
			if (event.keyCode == 9 || event.which == 9)
			{
				event.preventDefault();
				var s = this.selectionStart;
				this.value = this.value.substring(0,this.selectionStart) + "\t" + this.value.substring(this.selectionEnd);
				this.selectionEnd = s+1; 
			}
			if (event.keyCode == 13 || event.which == 13)
			{
				event.preventDefault();
				var pos = this.selectionStart,
				val = this.value,
				start = val.lastIndexOf('\n', pos - 1) + 1,
				end = val.indexOf('\n', pos);
		  
				if (end == -1)
					end = val.length;
				var line = val.substr(start, end - start),
				tabs = "",
				count = 0,
				index = 0;
				while (line.charAt(index++) === "\t") {
					tabs += "\t";
					count++;
				}
				count++;
				var s = this.selectionStart;
				this.value = this.value.substring(0,this.selectionStart)+"\r\n"+tabs+ this.value.substring(this.selectionEnd);
				this.selectionEnd = s+count; 
			}
		});

		//Externals
		if ($.fn.tooltip)
			$('[data-toggle="tooltip"]').tooltip();
		if ($.fn.collapse)
			$('[data-toggle="collapse"]:not(.wsko-col-init)').addClass('wsko-col-init').collapse({
				toggle: false
			});
		if ($.fn.popover)
			$('[data-toggle="popover"]').popover();
		if ($.fn.selectpicker)
		{
			$('.selectpicker').selectpicker();

			$('.wsko-selectpicker-ajax').each(function(index){
				var $this = $(this);
				$this.selectpicker().ajaxSelectPicker({
					ajax: {
						url: wsko_data.ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: $this.data('ajax-action'),
						  	nonce: $this.data('ajax-nonce'),
						  	q: '{{{q}}}'
						}
					},
					preprocessData: function (data) {
						var array = [];
						if (data.success)
						{
							$.each(data.data, function(k, v){
								array.push({
									text: v.text,
									value: v.value,
									data:
									{
										subtext: v.subtext
									}
								});
							});
						}
						return array;
					}
				});
			});
		}

		//Do customs
		$(document).trigger("wsko_init_core");
	};
	
	window.wsko_init_misc_widgets = function()
	{
		$('.wsko-misc-content-table').each(function(index){
			var $this = $(this),
			$target = $this.find('.wsko-misc-content-table-target'),
			$table_wrapper = $('<div class="wsko-misc-content-table-wrapper"><ol class="wsko-misc-content-table-gen"></ol></div>'),
			$table = $table_wrapper.find('ol'),
			$headings = [],
			level = 1,
			$cur = null;
			$this.find('h1,h2,h3,h4,h5,h6').each(function(index){
				var $el = $(this),
				dl = parseInt($el.prop("tagName").toLowerCase().replace("h","")),
				$link = $('<a href="#" class="wsko-misc-content-table-link">'+$el.text()+'</a>');
				$link.data('target', $el);
				if (index && dl != 1)
				{
					if (level < dl)
					{
						level++;
						dl = level;
						$cur = $('<li><ol></ol></li>').appendTo($cur.find('ol'));
						$cur.prepend($link);
					}
					else if (level > dl)
					{
						for (var i = level; i > dl; i--)
						{
							$cur = $cur.parent().closest('li');
						}
						$cur = $('<li><ol></ol></li>').appendTo($cur.closest('ol'));
						$cur.prepend($link);
					}
					else
					{
						$cur = $('<li><ol></ol></li>').appendTo($cur.closest('ol'));
						$cur.prepend($link);
					}
				}
				else
				{
					$cur = $('<li><ol></ol></li>').appendTo($table);
					$cur.prepend($link);
				}
				level = dl;
			})
			if ($target.length)
				$target.append($table_wrapper).removeClass('wsko-misc-content-table-target');
			else
				$this.prepend($table_wrapper);
			$this.removeClass('wsko-misc-content-table');
		});
		$('.wsko-misc-content-table-link:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			
			$(window.wsko_get_scroll_parent($this.get(0))).animate({
				scrollTop: $this.data('target').offset().top
			}, 500);
		});
	};

	//helpers
	function wsko_replace_html(string)
	{
		var buf = [];
		for (var i=string.length-1;i>=0;i--) {
			buf.unshift(['&#', string[i].charCodeAt(), ';'].join(''));
		}
		return buf.join('');
	}
	
	//Notifications
	var notification_timeout_modal;
	var notification_timeout_admin;
	var notification_timeout_widget;
	var notification_timeout_setup;
	window.wsko_notification = function(success, msg, title)
	{
		var z_index = 2147483647,
		timeout = success ? 3000 : 10000;

		var $setup = $('.wsko-setup-wrapper .wsko-setup-notifications-wrapper');
		var $modal = $('#wsko_content_optimizer_modal.wsko-modal-active .wsko-co-notifications-overlay');
		var $admin = $('#wsko_admin_view_ajax_notification');
		var $widget = $('.wsko-content-optimizer.wsko-co-widget .wsko-co-notifications-overlay,.wsko-content-optimizer.wsko-co-frontend-widget .wsko-co-notifications-overlay');
		if ($setup.length)
		{
			if (notification_timeout_setup)
			{
				clearTimeout(notification_timeout_setup);
				$setup.hide();
			}

			if (success)
				$setup.css('background-color', '#27ae60').css('z-index', z_index);
			else
				$setup.css('background-color', '#e74c3c').css('z-index', z_index);
			window.wsko_clean_radial_timer($setup);
			$setup.html(msg).fadeIn();
			window.wsko_add_radial_timer($setup, timeout);
			$setup.prepend('<small class="text-off" style="float:right;cursor:pointer;"><i class="fa fa-times fa-fw"></i></small>');
			notification_timeout_setup = setTimeout(function(){ $setup.fadeOut('fast', function(){ $(this).css('z-index','-1'); }); }, timeout);
		}
		else if ($modal.length)
		{
			if (notification_timeout_modal)
			{
				clearTimeout(notification_timeout_modal);
				$modal.hide();
			}
		
			if (success)
				$modal.css('background-color', '#27ae60').css('z-index', z_index);
			else
				$modal.css('background-color', '#e74c3c').css('z-index', z_index);
			window.wsko_clean_radial_timer($modal);
			$modal.html(msg).fadeIn();
			window.wsko_add_radial_timer($modal, timeout);
			$modal.prepend('<small class="text-off" style="float:right;cursor:pointer;"><i class="fa fa-times fa-fw"></i></small>');
			notification_timeout_modal = setTimeout(function(){ $modal.fadeOut('fast', function(){ $(this).css('z-index','-1'); }); }, timeout);
		}
		else if ($admin.length)
		{
			if (notification_timeout_admin)
			{
				clearTimeout(notification_timeout_admin);
				$admin.hide();
			}
		
			if (success)
				$admin.css('background-color', '#27ae60').css('z-index', z_index);
			else
				$admin.css('background-color', '#e74c3c').css('z-index', z_index);
			window.wsko_clean_radial_timer($admin);
			$admin.html(msg).fadeIn();
			window.wsko_add_radial_timer($admin, timeout);
			$admin.prepend('<small class="text-off" style="float:right;cursor:pointer;"><i class="fa fa-times fa-fw"></i></small>');
			notification_timeout_admin = setTimeout(function(){ $admin.fadeOut('fast', function(){ $(this).css('z-index','-1'); }); }, timeout);
		}
		else if ($widget.length)
		{
			if (notification_timeout_widget)
			{
				clearTimeout(notification_timeout_widget);
				$widget.hide();
			}
		
			if (success)
				$widget.css('background-color', '#27ae60').css('z-index', z_index);
			else
				$widget.css('background-color', '#e74c3c').css('z-index', z_index);
			window.wsko_clean_radial_timer($widget);
			$widget.html(msg).fadeIn();
			window.wsko_add_radial_timer($widget, timeout);
			$widget.prepend('<small class="text-off" style="float:right;cursor:pointer;"><i class="fa fa-times fa-fw"></i></small>');
			notification_timeout_widget = setTimeout(function(){ $widget.fadeOut('fast', function(){ $(this).css('z-index','-1'); }); }, timeout);
		}
	}
	window.wsko_add_radial_timer = function($el, timeout)
	{
		timeout_s = timeout / 1000;
		var $circle = $el.find('.wsko-circle-timer');
		if (!$circle.length)
			$circle = $('<div class="wsko-circle-timer"><div class="wsko-circle-timer-number">'+timeout_s+'</div><svg><circle r="18" cx="20" cy="20"></circle></svg></div>').prependTo($el);
		$circle.find('circle').css({
			'-webkit-animation-duration': timeout_s+'s',
			'-moz-animation-duration': timeout_s+'s',
			'-ms-animation-duration': timeout_s+'s',
			'-o-animation-duration': timeout_s+'s',
			'animation-duration': timeout_s+'s'
		});
		$text = $circle.find('.wsko-circle-timer-number');
		if (interval_h = $circle.data('circle-interval'))
		{
			clearInterval(interval_h);
			$circle.data('circle-interval', false);
		}
		$circle.data('circle-interval', setInterval(function(){
			var co = parseInt($text.text());
			if (co)
				$text.text(co-1);
			else
				$text.text('0');
		}, 1000));
		if (timeout_h = $circle.data('circle-timeout'))
		{
			clearTimeout(timeout_h);
			$circle.data('circle-timeout', false);
		}
		$circle.data('circle-timeout', setTimeout(function(){
			$circle.fadeOut();
			if (interval_h = $circle.data('circle-interval'))
			{
				clearInterval(interval_h);
				$circle.data('circle-interval', false);
			}
		}, timeout));
		//$circle.prependTo($el);
	}
	window.wsko_clean_radial_timer = function($el)
	{
		var $circle = $el.find('.wsko-circle-timer');
		if ($circle.length)
		{
			if (timeout_h = $circle.data('circle-timeout'))
			{
				clearTimeout(timeout_h);
				$circle.data('circle-timeout', false);
			}
			if (interval_h = $circle.data('circle-interval'))
			{
				clearInterval(interval_h);
				$circle.data('circle-interval', false);
			}
		}
	}
	
	//ajax
	if ($.wskoXhrPool == undefined)
	{
		$.wskoXhrPool = [];
		$.wskoXhrPool.abortAll = function() {
			$.each($.wskoXhrPool, function(i, jqXHR) {   //  cycle through list of recorded connection
				if (jqXHR)
					jqXHR.abort();  //  aborts connection
				$.wskoXhrPool.splice(i, 1); //  removes from list by index
			});
		}
	}
	window.wsko_post = function(data, before, success, error)
	{
		$.ajax({
			url: wsko_data.ajaxurl,
			type: 'post',
			data: data,
			async: true,
			beforeSend: function(jqXHR)
			{
				$.wskoXhrPool.push(jqXHR);
				if (before)
					before();
			},
			success: function(res)
			{
				if (success)
					success(res);
				window.wsko_init_core();
			},
			error: function()
			{
				if (error)
					error();
			},
			complete: function(jqXHR)
			{
				var i = $.wskoXhrPool.indexOf(jqXHR);   //  get index for current connection completed
				if (i > -1) $.wskoXhrPool.splice(i, 1); //  removes from list by index
			}
		});
	};
	window.wsko_post_file = function(file, data, before, success, error, $btn, reload)
	{
		var $loader;
		var formData = new FormData();
		formData.append('file', file);
		$.each(data, function(key, val){
			formData.append(key, val);
		});
		$.ajax({
			url: wsko_data.ajaxurl,
			type: 'post',
			data: formData,
			processData: false,
			contentType: false,
			beforeSend: function(jqXHR)
			{
				$loader = window.wsko_set_element_ajax_loader($btn);
				$.wskoXhrPool.push(jqXHR);
				if (before)
					before();
			},
			success: function(res)
			{
				if ($loader)
					$loader.hide();
				var succ_res = false;
				if(success)
					succ_res = success(res);
				if (!succ_res)
				{
					if (res.success)
					{
						if (res.msg)
							window.wsko_notification(true, res.msg, "");
						else
							window.wsko_notification(true, window.wsko_text('core_ajax_saved'), "");
					}
					else
					{
						if (res.msg)
							window.wsko_notification(false, res.msg, "");
						else
							window.wsko_notification(false, window.wsko_text('core_ajax_error'), "");
					}
				}
				if (res.success)
				{
					if ($btn && !($btn.is(':input')))
						window.wsko_add_element_ajax_result($btn, true);
					
					if (reload)
					{
						if (res.redirect)
							location.href = res.redirect;
						else
						{
							if (wsko_data.is_configured && window.wsko_reload_lazy_page != undefined)
								window.wsko_reload_lazy_page();
							else
								location.reload();
						}
					}
				}
				else
				{
					if ($btn && !($btn.is(':input')))
						window.wsko_add_element_ajax_result($btn, false);
				}
				window.wsko_init_core();
			},
			error: function()
			{
				if ($loader)
					$loader.hide();
				if ($btn && !($btn.is(':input')))
					window.wsko_add_element_ajax_result($btn, true);
				var err_res = false;
				if (error)
					err_res = error();
				if (!window.wsko_is_in_page_load && !err_res)
					window.wsko_notification(false, window.wsko_text('core_ajax_server_error'), "");
			},
			complete: function(jqXHR)
			{
				var i = $.wskoXhrPool.indexOf(jqXHR);   //  get index for current connection completed
				if (i > -1) $.wskoXhrPool.splice(i, 1); //  removes from list by index
			}
		});
	};
	window.wsko_post_element = function(data, succ, error, $btn, reload)
	{
		if ($btn)
		{
			if ($btn.data('wsko-ajax-lock'))
				return;
			$btn.data('wsko-ajax-lock', true);
		}
		var $loader;
		if (window.wsko_get_controller)
		{
			var controller = window.wsko_get_controller();
			if (!data['wsko_controller'])
				data['wsko_controller'] = controller.wsko_controller;
			if (!data['wsko_controller_sub'])
				data['wsko_controller_sub'] = controller.wsko_controller_sub;
		}
		window.wsko_post(data, function(){
			$loader = window.wsko_set_element_ajax_loader($btn);
		}, function(res){
			if ($btn)
				$btn.data('wsko-ajax-lock', false);
			var succ_res = false;
			if(succ)
				succ_res = succ(res);
			if (!succ_res)
			{
				if (res.success)
				{
					if (res.msg)
						window.wsko_notification(true, res.msg, "");
					else
						window.wsko_notification(true, window.wsko_text('core_ajax_saved'), "");
				}
				else
				{
					if (res.msg)
						window.wsko_notification(false, res.msg, "");
					else
						window.wsko_notification(false, window.wsko_text('core_ajax_error'), "");
				}
			}
			if (res.dynamic_elements)
			{
				$.each(res.dynamic_elements, function($k, $v){
					$($k).html($v);
				});
			}
			if (res.success)
			{
				if ($btn && !($btn.is(':input')))
					window.wsko_add_element_ajax_result($btn, true);
				if (reload)
				{
					if (res.redirect)
						location.href = res.redirect;
					else
					{
						if (wsko_data.is_configured && window.wsko_reload_lazy_page != undefined && reload != 2)
							window.wsko_reload_lazy_page();
						else
							location.reload();
					}
				}
			}
			else
			{
				if (res.redirect)
					location.href = res.redirect;

				if ($btn && !($btn.is(':input')))
					window.wsko_add_element_ajax_result($btn, false);
			}
			if ($loader)
				$loader.hide();
		}, function(){
			if ($btn)
				$btn.data('wsko-ajax-lock', false);
			var err_res = false;
			if (error)
				err_res = error();
			if (!window.wsko_is_in_page_load && !err_res)
				window.wsko_notification(false, window.wsko_text('core_ajax_server_error'), "");
			if ($loader)
				$loader.hide();
			if ($btn && !($btn.is(':input')))
				window.wsko_add_element_ajax_result($btn, false);
		});
	};
	window.wsko_set_element_ajax_loader = function($btn)
	{
		var $loader;
		if ($btn)
		{
			var $btn_r = $btn.is(':input')||$btn.is('button')?$btn.parent():$btn;
			$loader = $btn_r.find('.wsko-loader-small').length == 0 ? $('<div class="loader wsko-loader wsko-loader-small"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg></div>').prependTo($btn_r) : $btn_r.find('.wsko-loader').show();
			$btn.find('.wsko-ajax-result').remove();
		}
		return $loader;
	};
	window.wsko_set_big_element_ajax_loader = function($btn)
	{
		var $loader;
		if ($btn)
		{
			var $btn_r = $btn.is(':input')||$btn.is('button')?$btn.parent():$btn;
			$loader = $btn_r.find('.wsko-loader-big').length == 0 ? $('<div class="loader wsko-loader"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg></div>').prependTo($btn_r) : $btn_r.find('.wsko-loader').show();
			$btn.find('.wsko-ajax-result').remove();
		}
		return $loader;
	};
	window.wsko_add_element_ajax_result = function($el, success)
	{
		var $old = $el.find('.wsko-ajax-result');
		if ($old.length)
		{
			$old.each(function(index){ clearInterval($(this).data('timeout')); });
			$old.remove();
		}
		var $new = $('<i class="fa fa-'+(success?'check':'times')+' wsko-ajax-result"></i> ').prependTo($el);
		$new.data('timer', setTimeout(function(){
			$new.fadeOut();
		}, 2000));
	};
	window.wsko_load_lazy_data_for_controller = function($container, controller, subpage, action, args, resetOld)
	{
		var $fields = $container.find('.wsko-lazy-field');
		if (resetOld)
		{
			$fields.each(function(index){
				var $this = $(this);
				if ($this.data('wsko-old-html'))
					$this.html($this.data('wsko-old-html'));
			});
		}
		
		window.wsko_post_element({
				wsko_controller: controller,
				wsko_controller_sub: subpage,
				wsko_action: action,
				lazy_data: args,
				action : 'wsko_load_lazy_data',
				nonce: wsko_data.lazy_data_nonce
			}, 
			function(res)
			{
				if (res.success)
				{
					$fields.each(function(index){
						var $this = $(this),
						lazy_var = $this.data('wsko-lazy-var'),
						res_f = wsko_data.template_no_data;
						if (res.data[lazy_var])
							res_f = res.data[lazy_var];
						//if (!$this.data('wsko-old-html'))
							//$this.data('wsko-old-html', $this.html());
						$this.html(res_f);
						if ($this.data('wsko-lazy-wrapper'))
						{
							var $wrapper = $this.parents($this.data('wsko-lazy-wrapper')+'.wsko-lazy-wrapper');
							$wrapper.find('.wsko-lazy-wrapper-var').show();
							$wrapper.find('.wsko-lazy-wrapper-preview').hide();
						}
					});
					/*$.each(res.data, function(key, value){
						$container.find('.wsko-lazy-field[data-wsko-lazy-var="'+key+'"]').each(function(index){
							var $this = $(this);
							if (!$this.data('wsko-old-html'))
								$this.data('wsko-old-html', $this.html());
							$this.html(value);
							if ($this.data('wsko-lazy-wrapper'))
							{
								var $wrapper = $this.parents($this.data('wsko-lazy-wrapper')+'.wsko-lazy-wrapper');
								$wrapper.find('.wsko-lazy-wrapper-var').show();
								$wrapper.find('.wsko-lazy-wrapper-preview').hide();
							}
						});
					});*/
					
					//if (res.new_notifs)
						$('#wsko_admin_ajax_notifications').html(res.notif);
					return true;
				}
			}, false, false, false);
	};
	window.wsko_text = function(id)
	{
		return wsko_data.texts[id];
	};
	
	/* PROGRESS CIRCLE COMPONENT */
	(function ($) {

		$.fn.circliful = function (options, callback) {

			var settings = $.extend({
				// These are the defaults.
				startdegree: 0,
				fgcolor: "#556b2f",
				bgcolor: "#eee",
				fill: false,
				width: 15,
				dimension: 200,
				fontsize: 15,
				percent: 50,
				animationstep: 1.0,
				iconsize: '20px',
				iconcolor: '#999',
				border: 'default',
				complete: null,
				bordersize: 10
			}, options);

			return this.each(function () {

				var customSettings = ["fgcolor", "bgcolor", "fill", "width", "dimension", "fontsize", "animationstep", "endPercent", "icon", "iconcolor", "iconsize", "border", "startdegree", "bordersize"];

				var customSettingsObj = {};
				var icon = '';
				var endPercent = 0;
				var obj = $(this);
				var fill = false;
				var text, info;

				obj.addClass('circliful');

				checkDataAttributes(obj);

				if (obj.data('text') != undefined) {
					text = obj.data('text');

					if (obj.data('icon') != undefined) {
						icon = $('<i></i>')
							.addClass('fa ' + $(this).data('icon'))
							.css({
								'color': customSettingsObj.iconcolor,
								'font-size': customSettingsObj.iconsize
							});
					}

					if (obj.data('type') != undefined) {
						type = $(this).data('type');

						if (type == 'half') {
							addCircleText(obj, 'circle-text-half', (customSettingsObj.dimension / 1.45));
						} else {
							addCircleText(obj, 'circle-text', customSettingsObj.dimension);
						}
					} else {
						addCircleText(obj, 'circle-text', customSettingsObj.dimension);
					}
				}

				if ($(this).data("total") != undefined && $(this).data("part") != undefined) {
					var total = $(this).data("total") / 100;

					percent = (($(this).data("part") / total) / 100).toFixed(3);
					endPercent = ($(this).data("part") / total).toFixed(3)
				} else {
					if ($(this).data("percent") != undefined) {
						percent = $(this).data("percent") / 100;
						endPercent = $(this).data("percent")
					} else {
						percent = settings.percent / 100
					}
				}

				if ($(this).data('info') != undefined) {
					info = $(this).data('info');

					if ($(this).data('type') != undefined) {
						type = $(this).data('type');

						if (type == 'half') {
							addInfoText(obj, 0.9);
						} else {
							addInfoText(obj, 1.25);
						}
					} else {
						addInfoText(obj, 1.25);
					}
				}

				$(this).width(customSettingsObj.dimension + 'px');

				var canvas = $('<canvas></canvas>').attr({
					width: customSettingsObj.dimension,
					height: customSettingsObj.dimension
				}).appendTo($(this)).get(0);

				var context = canvas.getContext('2d');
				var container = $(canvas).parent();
				var x = canvas.width / 2;
				var y = canvas.height / 2;
				var degrees = customSettingsObj.percent * 360.0;
				var radians = degrees * (Math.PI / 180);
				var radius = canvas.width / 2.5;
				var startAngle = 2.3 * Math.PI;
				var endAngle = 0;
				var counterClockwise = false;
				var curPerc = customSettingsObj.animationstep === 0.0 ? endPercent : 0.0;
				var curStep = Math.max(customSettingsObj.animationstep, 0.0);
				var circ = Math.PI * 2;
				var quart = Math.PI / 2;
				var type = '';
				var fireCallback = true;
				var additionalAngelPI = (customSettingsObj.startdegree / 180) * Math.PI;

				if ($(this).data('type') != undefined) {
					type = $(this).data('type');

					if (type == 'half') {
						startAngle = 2.0 * Math.PI;
						endAngle = 3.13;
						circ = Math.PI;
						quart = Math.PI / 0.996;
					}
				}
			  
				/**
				 * adds text to circle
				 *
				 * @param obj
				 * @param cssClass
				 * @param lineHeight
				 */
				function addCircleText(obj, cssClass, lineHeight) {
					$("<span></span>")
						.appendTo(obj)
						.addClass(cssClass)
						.text(text)
						.prepend(icon)
						.css({
							'line-height': lineHeight + 'px',
							'font-size': customSettingsObj.fontsize + 'px'
						});
				}

				/**
				 * adds info text to circle
				 *
				 * @param obj
				 * @param factor
				 */
				function addInfoText(obj, factor) {
					$('<span></span>')
						.appendTo(obj)
						.addClass('circle-info-half')
						.css(
							'line-height', (customSettingsObj.dimension * factor) + 'px'
						)
						.text(info);
				}

				/**
				 * checks which data attributes are defined
				 * @param obj
				 */
				function checkDataAttributes(obj) {
					$.each(customSettings, function (index, attribute) {
						if (obj.data(attribute) != undefined) {
							customSettingsObj[attribute] = obj.data(attribute);
						} else {
							customSettingsObj[attribute] = $(settings).attr(attribute);
						}

						if (attribute == 'fill' && obj.data('fill') != undefined) {
							fill = true;
						}
					});
				}

				/**
				 * animate foreground circle
				 * @param current
				 */
				function animate(current) {

					context.clearRect(0, 0, canvas.width, canvas.height);

					context.beginPath();
					context.arc(x, y, radius, endAngle, startAngle, false);

					context.lineWidth = customSettingsObj.bordersize + 1;

					context.strokeStyle = customSettingsObj.bgcolor;
					context.stroke();

					if (fill) {
						context.fillStyle = customSettingsObj.fill;
						context.fill();
					}

					context.beginPath();
					context.arc(x, y, radius, -(quart) + additionalAngelPI, ((circ) * current) - quart + additionalAngelPI, false);

					if (customSettingsObj.border == 'outline') {
						context.lineWidth = customSettingsObj.width + 13;
					} else if (customSettingsObj.border == 'inline') {
						context.lineWidth = customSettingsObj.width - 13;
					}

					context.strokeStyle = customSettingsObj.fgcolor;
					context.stroke();

					if (curPerc < endPercent) {
						curPerc += curStep;
						requestAnimationFrame(function () {
							animate(Math.min(curPerc, endPercent) / 100);
						}, obj);
					}

					if (curPerc == endPercent && fireCallback && typeof(options) != "undefined") {
						if ($.isFunction(options.complete)) {
							options.complete();

							fireCallback = false;
						}
					}
				}

				animate(curPerc / 100);

			});
		};
	}(jQuery));
	
	//Init
	window.wsko_init_core();
});