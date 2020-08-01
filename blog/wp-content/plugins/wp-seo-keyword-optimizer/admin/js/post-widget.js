jQuery(document).ready(function($){
	var co_parent_view = false

	//Generals
	function wsko_set_generals()
	{
		$('.wsko-ajax-input:not(.wsko_st_init)').addClass('wsko_st_init').on('change', function(event){
			$('.wsko-co-saving-text').html('<i class="fa fa-spinner fa-spin"></i> '+window.wsko_text('co_saving'));
			$('.wsko-co-saving-prefix').show();
			window.parent.postMessage('wsko_co_saving', location.origin);
		}).on('wsko_ajax_input_save_success', function(event){
			$('.wsko-co-saving-text').text(window.wsko_text('co_saving_success'));
			$('.wsko-co-saving-prefix').show();
			window.parent.postMessage('wsko_co_saving_success', location.origin);
		}).on('wsko_ajax_input_save_fail', function(event){
			$('.wsko-co-saving-text').html('<i class="fa fa-times"></i> '+window.wsko_text('co_saving_failed'));
			$('.wsko-co-saving-prefix').show();
			window.parent.postMessage('wsko_co_saving_failed', location.origin);
		});
		$('.wsko-co-lazy-tab').each(function(index){
			var $this = $(this),
			$pane = $this.closest('.wsko-tab');
			id = $pane.attr('id');
			if (id)
			{
				var $link = $('a.wsko-nav-link[href="#'+id+'"]');
				if ($pane.hasClass('wsko-tab-active'))
				{
					if (!$pane.hasClass('wsko-loaded'))
					{
						switch($this.data('tab'))
						{
							case 'content': wsko_reload_content($this.data('post'), $this); break;
							case 'keywords': wsko_reload_keywords($this.data('post'), $this); break;
							case 'linking': wsko_reload_linking($this.data('post'), $this); break;
							case 'backlinks': wsko_reload_backlinks($this.data('post'), $this); break;
							case 'performance': wsko_reload_performance($this.data('post'), $this); break;
						}
						$pane.addClass('wsko-loaded');
					}
				}
				else
				{
					$link.click(function(event){
						if (!$pane.hasClass('wsko-loaded'))
						{
							switch($this.data('tab'))
							{
								case 'content': wsko_reload_content($this.data('post'), $this); break;
								case 'keywords': wsko_reload_keywords($this.data('post'), $this); break;
								case 'linking': wsko_reload_linking($this.data('post'), $this); break;
								case 'backlinks': wsko_reload_backlinks($this.data('post'), $this); break;
								case 'performance': wsko_reload_performance($this.data('post'), $this); break;
							}
							$pane.addClass('wsko-loaded');
						}
					});
				}
			}
		});
		$('#wsko_reload_co_content_btn:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			wsko_reload_content($(this).data('post'), $(this));
		});
		$('.wsko-co-set-link-btn:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $link = $(this);

			window.wsko_post_element({action: 'wsko_co_set_link', post: $link.data('post'), index: $link.data('index'), length: $link.data('length'), target: $link.data('target'), nonce: wsko_co_data.set_link_nonce},
				function(res){
					if (res.success)
					{
						wsko_reload_linking($link.data('r-post'), $link);
					}
				}, function() {
				}, $link, false);
		});
		$('.wsko-content-optimizer-link:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $link = $(this);

			window.wsko_open_optimizer_modal($link.data('post'), 'post_id', $link.data('opentab'));
		});

		$('#wsko_content_optimizer_modal:not(.wsko-init)').addClass('wsko-init').click(function(event){
			var $modal = $('#wsko_content_optimizer_modal').removeClass('wsko-modal-active');
			$modal.find('.wsko-modal-multi-container').hide();
			co_parent_view = false;
			$("body").removeClass("modal-open");
		});
		$('#wsko_content_optimizer_modal .wsko-modal-close:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $modal = $('#wsko_content_optimizer_modal').removeClass('wsko-modal-active');
			$modal.find('.wsko-modal-multi-container').hide();
			co_parent_view = false;
			$("body").removeClass("modal-open");
		});
		$('.wsko-content-optimizer-multi-link:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $link = $(this),
			$modal = $('#wsko_content_optimizer_modal').addClass('wsko-modal-active'),
			$multi_content = $modal.find('.wsko-modal-multi-container').show().html('');
			$modal.find('.wsko-modal-multi-container-bar').hide();
			$modal.find('.wsko-modal-loader').hide();
			$modal.find('.wsko-modal-content').hide();
			$("body").addClass("modal-open");
			co_parent_view = true;
			var posts = $link.data('posts') ? $link.data('posts')/*JSON.parse($link.data('posts'))*/ : {};
			
			$multi_content.append('<h4 class="wsko-content-optimizer-multi-link-title modal-title panel-heading">'+$link.data('title')+'</h4>');
			
			$.each(posts, function(k, val){
				$multi_content.append('<a href="#" class="wsko-content-optimizer-link wsko-multi-link dark" data-post="'+k+'">'+val.title+'<br/><small class="wsko-content-optimizer-multi-link-url text-off">'+val.url+'</small></a>');
			});
			window.wsko_init_core();
		});
		$('.wsko-content-optimizer-multi-ajax-link:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $link = $(this),
			$modal = $('#wsko_content_optimizer_modal').addClass('wsko-modal-active'),
			$multi_content = $modal.find('.wsko-modal-multi-container').show().html('');
			$modal.find('.wsko-modal-multi-container-bar').hide();
			$modal.find('.wsko-modal-loader').show();
			$modal.find('.wsko-modal-content').hide();
			$("body").addClass("modal-open");
			co_parent_view = true;
			var action = $link.data('action'),
			nonce = $link.data('nonce'),
			arg = $link.data('ajax-arg');
			
			window.wsko_post_element({action: action, arg: arg, nonce: nonce}, 
				function(res){
					$modal.find('.wsko-modal-loader').hide();
					if (res.success)
					{
						$multi_content.html(res.view);
						if (res.title)
							$multi_content.prepend('<h4 class="wsko-content-optimizer-multi-link-title modal-title panel-heading">'+res.title+'</h4>');
						window.wsko_init_core();
						return true;
					}
					else
						$modal.find('.wsko-modal-close').click();
				},
				function()
				{
					$modal.find('.wsko-modal-loader').hide();
					$modal.find('.wsko-modal-close').click();
				}, false, false);
		});

		//Tabs
		$('.wsko-nav .wsko-nav-link:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			$this.closest('.wsko-nav').find('.wsko-nav-link').removeClass('wsko-nav-link-active');
			$this.addClass('wsko-nav-link-active');
			var $tab = $($this.attr('href'));
			if ($tab.hasClass('wsko-tab'))
			{
				$tab.closest('.wsko-tab-content').children('.wsko-tab').removeClass('wsko-tab-active').hide();
				$tab.addClass('wsko-tab-active').fadeIn();
			}
		});
		
		$('.wsko-content-optimizer input').keypress(function(e) {
			if(e.which == 13) {
				$(this).change();
				return false;
			}
		});

		//Media Picker
		$('.wsko-co-media-picker:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			$field = $this.closest('.wsko-co-media-picker-container').find('.wsko-co-media-picker-target');
			if (typeof wp !== 'undefined' && wp.media && wp.media.editor)
			{
				wp.media.editor.send.attachment = function(props, attachment) {
					$field.val(attachment.url);
					$field.change();
                };
                wp.media.editor.open($this);
			}
		});
	}
	
	//Metas
	function wsko_set_meta_elements()
	{
		$('.wsko-metas-save-button:not(.wsko-init)').addClass('wsko-init').click(function(event){
			wsko_save_metas($(this));
		});
		$('.wsko-metas-slide-control:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $container = $(this).closest('.wsko-metas-slide-wrapper'),
			slide = $container.data('slide'),
			dir = $(this).data('slide'),
			max = $container.data('max'),
			$left_control = $container.find('.wsko-metas-slide-control[data-slide="left"]'),
			$right_control = $container.find('.wsko-metas-slide-control[data-slide="right"]');
			if (!slide)
				slide = 0;
			if (dir == 'right')
			{
				slide++;
				if (slide >= max)
					slide = max-1;
			}
			else
			{
				slide--;
				if (slide < 0)
					slide = 0;
			}
			$container.find('.wsko-metas-slide').removeClass('wsko-metas-slide-active');
			$container.find('.wsko-metas-slide[data-slide="'+slide+'"]').addClass('wsko-metas-slide-active');

			$left_control.removeClass('wsko-metas-slide-inactive');
			$right_control.removeClass('wsko-metas-slide-inactive');
			if (slide == max-1)
			{
				$left_control.removeClass('wsko-metas-slide-inactive');
				$right_control.addClass('wsko-metas-slide-inactive');
			}
			else if (slide == 0)
			{
				$right_control.removeClass('wsko-metas-slide-inactive');
				$left_control.addClass('wsko-metas-slide-inactive');
			}
			$container.data('slide', slide);
		});
		$('.wsko-metas-search-field:not(.wsko-init)').addClass('wsko-init').on('keyup', function(event){
			var $this = $(this),
			val = $this.val();
			if (event.which == 13)
				return false;
			if (val)
			{
				val = val.toLowerCase();
				$this.closest('.wsko-metas-search-wrapper').find('.wsko-metas-placeholder').each(function(index){
					if ($(this).data('search').search(val) != -1)
						$(this).show();
					else
						$(this).hide();
				});
			}
			else
				$this.closest('.wsko-metas-search-wrapper').find('.wsko-metas-placeholder').show();
			
			$this.closest('.wsko-metas-search-wrapper').find('.wsko-snippet-fields').each(function(index){
				if ($(this).find('.wsko-metas-placeholder:visible').length == 0)
					$(this).find('.wsko-metas-search-no-items').show();
				else
					$(this).find('.wsko-metas-search-no-items').hide();
			});
		});
		$('.wsko-metas-field-hide-slug:not(.wsko-init)').addClass('wsko-init').change(function(){
			if ($(this).is(':checked'))
				$(this).closest('.wsko-set-metas-wrapper').find('.wsko-metas-field-url').attr('disabled', true);
			else
				$(this).closest('.wsko-set-metas-wrapper').find('.wsko-metas-field-url').attr('disabled', false);
		}).change();
		$('.wsko-hightlight-input:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			$container = $this.closest($this.data('container'));
			$container.find('.wsko-hightlight-input').each(function(index){
				var $this = $(this);
				if ($this.data('highlight-timeout'))
					clearTimeout($this.data('highlight-timeout'));
				$container.find($this.data('input')).removeClass('wsko-input-highlight');
			});
			var $meta_field = $container.find($this.data('input')).focus().addClass('wsko-input-highlight');
			$meta_field.data('highlight-timeout', setTimeout(function(){$meta_field.removeClass('wsko-input-highlight');}, 2000));
		});
		
		var $metas = $('.wsko-set-metas-wrapper:not(.wsko-init)').addClass('wsko-init').on('wsko_metas_submit',function(e){
			var $this = $(this),
			$container = $this.closest('.wsko-set-metas-wrapper');
			if ($this.data('saving-disabled'))
				return;
				
			$container.find('.wsko-metas-save-button').removeClass('wsko-button-success wsko-metas-changed');
			/*title = $this.find('.wsko-metas-field-title').val(),
			desc = $this.find('.wsko-metas-field-desc').val(),
			robots_ni = $this.find('.wsko-metas-field-robotsni').is(':checked'),
			robots_nf = $this.find('.wsko-metas-field-robotsnf').is(':checked');*/
			var data = $this.find(':input').serialize();
			if ($this.data('robots'))
			{
				if (!$this.find('.wsko-metas-field-robotsni').is(':checked'))
					data += (data?'&':'')+"robots_ni=false";
				if (!$this.find('.wsko-metas-field-robotsnf').is(':checked'))
					data += (data?'&':'')+"robots_nf=false";
			}
			if ($this.data('canonical'))
			{
				/*if ($this.find('.wsko-metas-field-canonical-off').is(':checked'))
					data += (data?'&':'')+"no_canon=true&canon=";
				else if ($this.find('.wsko-metas-field-canonical-auto').is(':checked'))
					data += (data?'&':'')+"auto_canon=2&canon=";
				else*/
					data += (data?'&':'')+"canon="+$this.find('.wsko-metas-field-canonical-spec').val();

			}
			if ($this.find('.wsko-metas-field-hide-slug').length)
			{
				if (!$this.find('.wsko-metas-field-hide-slug').is(':checked'))
					data += (data?'&':'')+"hide_slug=false";
			}
			$container.find('.wsko-metas-load-overlay').show();
			var last_tab = $container.find('.wsko-tabs-social-snippets li a.wsko-nav-link-active').closest('li').index();
			if ($this.data('new-links') && confirm(window.wsko_text('auto_redirect_confirm')))
				data += (data?'&':'')+"create_redirects=true";
				
			$('.wsko-co-saving-text').html('<i class="fa fa-spinner fa-spin"></i> '+window.wsko_text('co_saving'));
			window.parent.postMessage('wsko_co_saving', location.origin);
			window.wsko_post_element({action: 'wsko_set_metas', data: data, type: $container.data('type'), arg: $container.data('arg'), nonce: $container.data('nonce')},
				function(res){
					if (res.success)
					{
						$('.wsko-co-saving-text').text(window.wsko_text('co_saving_success'));
						window.parent.postMessage('wsko_co_saving_success', location.origin);
						window.wsko_notification(false, 'test');
						wsko_reload_issues($container.data('arg'), $container);
						$this.data('new-links', false);
						var $new = $(res.new_view);
						$container.replaceWith($new);

						//wsko_set_meta_elements();
						window.wsko_init_core();
						$new.find('.wsko-tabs-social-snippets li').eq(last_tab).find('a').click();
					}
					else
					{
						$('.wsko-co-saving-text').html('<i class="fa fa-times"></i> '+window.wsko_text('co_saving_fail'));
						window.parent.postMessage('wsko_co_saving_failed', location.origin);
					}
					$('.wsko-co-saving-prefix').show();
					$container.find('.wsko-metas-load-overlay').hide();
				}, function() {
					$('.wsko-co-saving-text').html('<i class="fa fa-times"></i> '+window.wsko_text('co_saving_fail'));
					window.parent.postMessage('wsko_co_saving_failed', location.origin);
					$('.wsko-co-saving-prefix').show();
					$container.find('.wsko-metas-load-overlay').hide();
				}, false, false);
		}).each(function(index){
			if ($(this).data('saving-disabled'))
				$(this).find(':input').attr('disabled', true);
		});
		/*$metas.find('.wsko-metas-field-canonical-off').change(function(event){
			$metas.find('.wsko-metas-field-canonical-auto').attr('checked', false);
		});
		$metas.find('.wsko-metas-field-canonical-auto').change(function(event){
			$metas.find('.wsko-metas-field-canonical-off').attr('checked', false);
		});*/
		$metas.find('.wsko-meta-tab :input:not(.wsko-metas-search-field)').on('change keydown', function(){
			var $this = $(this),
			$wrapper = $this.closest('.wsko-set-metas-wrapper');
			if ($this.attr('name') == 'url' || $this.attr('name') == 'hide_slug')
				$wrapper.data('new-links', true);
			wsko_metas_changed($this);
		});
		/*$('.wsko-reset-metas-button:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			$container = $this.closest('.wsko-set-metas-wrapper');
			$container.find('.wsko-metas-load-overlay').show();
			window.wsko_post_element({action: 'wsko_set_metas', type: $this.data('type'), arg: $this.data('arg'), reset: true, nonce: $this.data('nonce')},
				function(res){
					if (res.success)
					{
						wsko_reload_issues($this.data('arg'), $this);
						$container.replaceWith(res.new_view);
						wsko_set_meta_elements();
					}
					$container.find('.wsko-metas-load-overlay').hide();
				}, function() {
					$container.find('.wsko-metas-load-overlay').hide();
				}, false, false);
		});*/
		if (typeof(jQuery.ui) != 'undefined' && typeof(jQuery.ui.draggable) != 'undefined' && typeof(jQuery.ui.droppable) != 'undefined')
		{
			$('.wsko-metas-placeholder:not(.wsko-init)').addClass('wsko-init').draggable({ revert: true, helper: "clone" });
			$('.wsko-metas-field-desc,.wsko-metas-field-title').not('.wsko-init').addClass('wsko-init').droppable({
				accept: '.wsko-metas-placeholder',
				drop: function( event, ui ) {
					var text = $(ui.draggable.eq(0)).data('tag'),
					element = $(this).get(0);
					if (document.selection) {  
						element.focus();  
						var sel = document.selection.createRange();  
						sel.text = text;  
						element.focus();  
					} else if (element.selectionStart || element.selectionStart === 0) {  
						var startPos = element.selectionStart;  
						var endPos = element.selectionEnd;  
						var scrollTop = element.scrollTop;  
						element.value = element.value.substring(0, startPos) + text +   
										 element.value.substring(endPos, element.value.length);  
						element.focus();  
						element.selectionStart = startPos + text.length;  
						element.selectionEnd = startPos + text.length;  
						element.scrollTop = scrollTop;  
					} else {  
						element.value += text;  
						element.focus();  
					}  
					$(element).change();
					/*$(this).
				  .addClass( "ui-state-highlight" )
				  .find( "p" )
					.html( "Dropped!" );*/
				}
			});
		}
	}
	
	//Keywords
	function wsko_set_keyword_elements()
	{
		$('.wsko-keyword-research-submit-button:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $btn = $(this);
			$this = $('#wsko_keyword_research_form_dummy');
			if ($this.data('demo'))
			{
				$suggest = $('#wsko_keyword_research_form_dummy').find('.wsko-keyword-report-suggests a.wsko-open-keyword-research').first();
				if ($suggest.length)
				{
					$suggest.click();
				}
			}
			else
			{
				if ($this.find('input[name="keyword"]').val())
				{
					var types = []
					$this.find('input[name="type[]"]:checked').each(function(index) {
						types.push($(this).val());
					});
					$('.wsko-close-keyword-research').click();
					if (types.length > 0)
						window.wsko_post_element({action: 'wsko_do_keyword_research', keyword: $this.find('input[name="keyword"]').val(), loc: $this.find('select[name="loc"]').val(), lang: $this.find('select[name="lang"]').val(), type: types, nonce: $this.data('nonce')}, 
						function(res){
							if (res.success) { 
								$('#wsko_keyword_research_history').collapse('hide');
								$('#wsko_keyword_research_overlay').fadeIn();
								$('#wsko_keyword_research_container').html(res.view);
							}
						}, false, $btn, false);
				}
			}
		});
		$('.wsko-open-keyword-research:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this);
			$('#wsko_keyword_research_container').html('');
			window.wsko_post_element({action: 'wsko_get_keyword_research', id: $this.data('research'), nonce:$this.data('nonce')},
				function(res){
					if (res.success)
					{
						$('#wsko_keyword_research_history').collapse('hide');
						$('#wsko_keyword_research_overlay').fadeIn();
						$('#wsko_keyword_research_container').html(res.view);
					}
					else
					{
						$('#wsko_keyword_research_container').html('<i class="fa fa-times fa-5x"></i>');
					} 
				}, false, $this, false);
		});
		$('.wsko-keyword-report-search-field:not(.wsko-init)').addClass('wsko-init').on('keyup change', function(event){
			var $this = $(this),
			timeout = $this.data('timeout'),
			all = $this.data('all') ? true : false,
			val = $this.val(),
			$suggests = $('#wsko_keyword_research_form_dummy').find('.wsko-keyword-report-suggests');
			if (timeout)
				clearTimeout(timeout);
			if (event.which == 13)
			{
				$('#wsko_keyword_research_form_dummy .wsko-keyword-research-submit-button').click();
			}
			if (all || val != $this.data('old-val'))
			{
				$this.data('timeout', setTimeout(function()
				{
					if (val)
					{
						$suggests.html('<div class="wsko-loader-small"></div>');
						window.wsko_post_element({action: 'wsko_get_report_suggestions', keyword: val, all: all, nonce: $this.data('nonce')},
							function(res){
								if (res.success && res.view)
								{
									$this.data('old-val', val);								
									$suggests.addClass('active').html(res.view);							
									wsko_set_keyword_elements();
								}
								else
								{
									$suggests.html("").removeClass('active');
								}
								return true;
							}, function(){
								$suggests.html("").removeClass('active');
								return true;
						}, false, false);
					}
					else
					{
						$suggests.removeClass('active').html("");
					}
				}, 1000));
			}
		}).on('blur', function(){
			$suggests = $('#wsko_keyword_research_form_dummy').find('.wsko-keyword-report-suggests');	
			
			setTimeout(function(){
				$suggests.removeClass('active');
			}, 200);
		}).on('focusin', function(){
			$suggests = $('#wsko_keyword_research_form_dummy').find('.wsko-keyword-report-suggests').has('li');		
			$suggests.addClass('active');
		});
		$('.wsko-research-get-all-suggestions:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			event.stopPropagation();
			$('.wsko-keyword-report-search-field').focus().data('all', true).change().data('all', false);
		});
		$('.wsko-close-keyword-research:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			$('#wsko_keyword_research_overlay').fadeOut();
		});

		$('.wsko-co-add-priority-keyword:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $pk = $(this).closest('.wsko-co-priority-keywords-container'),
			keyword = $pk.find('.wsko-co-keyword-input').val()/*,
			prio = $pk.find('.wsko-co-keyword-prio').val()*/;
			if (keyword)
			{
				window.wsko_post_element({action: 'wsko_co_add_priority_keyword', post_id: $pk.data('post'), keyword: keyword, /*prio: prio,*/ nonce: $pk.data('nonce')},
					function(res){
						if (res.success)
						{
							wsko_reload_issues($pk.data('post'), $pk);
							wsko_reload_linking($pk.data('post'), $pk);
							$pk.closest('.wsko-content-optimizer').find('.wsko-co-priority-keyword-group[data-prio="'+res.prio+'"]').append(res.view).find('.wsko-co-keyword-group-no-items').hide();
							$('.wsko-prio-kw-count-1').text(res.count_pr1);
							$('.wsko-prio-kw-count-2').text(res.count_pr2);
							wsko_set_keyword_elements();
						}
						else if (res.limit)
						{
							//$('#wsko_keyword_limit_pro_notice').show();
						}
					}, false, $pk, false);
			}
			else
			{
				window.wsko_notification(false, window.wsko_text('keyword_empty'), "");
			}
		});
		$('.wsko-co-add-priority-keyword-inline:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $pk = $(this)/*,
			prio = $pk.data('prio')*/;
			window.wsko_post_element({action: 'wsko_co_add_priority_keyword', post_id: $pk.data('post'), keyword: $pk.data('keyword'), /*prio: prio,*/ nonce: $pk.data('nonce')},
				function(res){
					if (res.success)
					{
						wsko_reload_issues($pk.data('post'), $pk);
						wsko_reload_linking($pk.data('post'), $pk);
						$pk.closest('.wsko-content-optimizer').find('.wsko-co-priority-keyword-group[data-prio="'+res.prio+'"]').append(res.view).find('.wsko-co-keyword-group-no-items').hide();
						$('.wsko-prio-kw-count-1').text(res.count_pr1);
						$('.wsko-prio-kw-count-2').text(res.count_pr2);
						wsko_set_keyword_elements();
					}
					else if (res.limit)
					{
						//$('#wsko_keyword_limit_pro_notice').show();
					}
				}, false, $pk, false);
		});
		$('.wsko-co-delete-priority-keyword:not(.wsko_init)').addClass('wsko_init').click(function(event){
			event.preventDefault();
			var $pk = $(this).closest('.wsko-co-priority-keyword');
			window.wsko_post_element({action: 'wsko_co_delete_priority_keyword', post_id: $pk.data('post'), keyword: $pk.data('keyword'), nonce: $pk.data('nonce')},
				function(res){
					if (res.success)
					{
						$pk = $pk.closest('.wsko-content-optimizer').find('.wsko-co-priority-keyword[data-keyword="'+$pk.data('keyword')+'"]');
						$group = $pk.fadeOut('fast', function(){
							$(this).remove();
						}).closest('.wsko-co-priority-keyword-group');
						if ($group.find('.wsko-co-priority-keyword').length == 1)
							$group.find('.wsko-co-keyword-group-no-items').show();
						$('.wsko-prio-kw-count-1').text(res.count_pr1);
						$('.wsko-prio-kw-count-2').text(res.count_pr2);
						wsko_reload_issues($pk.data('post'), $pk);
						wsko_reload_linking($pk.data('post'), $pk);
					}
				}, false, $pk, false);
		});
		if (typeof(jQuery.ui) != 'undefined' && typeof(jQuery.ui.draggable) != 'undefined' && typeof(jQuery.ui.droppable) != 'undefined')
		{
			$('.wsko-co-similar-prio-kw-list:not(.wsko_init),.wsko-co-priority-keyword-group:not(.wsko_init)').addClass('wsko_init').sortable({
				accept: '.wsko-co-priority-keyword,.wsko-co-keyword-draggable,.wsko-co-similar-prio-kw',
				//connectWith: '.wsko-co-priority-keyword-group,.wsko-co-similar-prio-kw-list',
				receive: function(event, ui) {
					// so if > 10v
					var $this = $(this);
				},
				update: function (event, ui) {
				//drop: function( event, ui ){
					//var $pk = $(ui.draggable.eq(0)),
					var $pk = $(ui.item),
					$target = $(this);
					//$container = $target.find('.wsko-co-priority-keyword-placeholder').parent();
					$target.find('.wsko-co-priority-keyword-placeholder').remove();
					if ($pk.hasClass('wsko-co-keyword-draggable'))
					{
						if ($target.hasClass('wsko-co-priority-keyword-group'))
						{
							var $sort = [];
							$target.closest('.wsko-co-priority-keywords-container').find('.wsko-co-priority-keyword-group').each(function(index){
								$(this).find('.wsko-co-keyword-draggable').each(function(index){
									$sort.push($(this).data('keyword'));
								});
								if ($(this).data('prio') == $target.data('prio'))
									$sort.push($pk.data('keyword'));
							});
							window.wsko_post_element({action: 'wsko_co_add_priority_keyword', sort: $sort, post_id: $target.closest('.wsko-co-priority-keywords-container').data('post'), keyword: $pk.data('keyword'), prio: $target.data('prio'), nonce: $target.closest('.wsko-co-priority-keywords-container').data('nonce')},
								function(res){
									if (res.success)
									{
										wsko_reload_issues($pk.data('post'), $pk);
										wsko_reload_linking($pk.data('post'), $pk);
										$pk.remove();
										$target.closest('.wsko-content-optimizer').find('.wsko-co-priority-keyword-group[data-prio="'+$target.data('prio')+'"]').append(res.view);
										$('.wsko-prio-kw-count-1').text(res.count_pr1);
										$('.wsko-prio-kw-count-2').text(res.count_pr2);
										wsko_set_keyword_elements();
									}
									else if (res.limit)
									{
										//$('#wsko_keyword_limit_pro_notice').show();
									}
								}, false, $target, false);
						}
						else if ($target.hasClass('wsko-co-similar-prio-kw-list'))
						{
							var $sort = [];
							$target.find('.wsko-co-priority-keyword').each(function(index){
								$sort.push($(this).data('keyword'));
							});
							$sort.push($pk.data('keyword'));
							window.wsko_post_element({action: 'wsko_co_add_similar_priority_keyword', sort: $sort, post_id: $target.closest('.wsko-co-priority-keywords-container').data('post'), keyword: $pk.data('keyword'), keyword_key: $target.data('keyword'), nonce: $target.closest('.wsko-co-priority-keywords-container').data('nonce-similar')},
								function(res){
									if (res.success)
									{
										wsko_reload_issues($pk.data('post'), $pk);
										wsko_reload_linking($pk.data('post'), $pk);
										$pk.remove();
										$target.closest('.wsko-co-priority-keyword-group').append(res.view);
										wsko_set_keyword_elements();
									}
								}, false, $target, false);
						}
					}
					else if ($pk.hasClass('wsko-co-priority-keyword'))
					{
						if ($target.hasClass('wsko-co-priority-keyword-group'))
						{
							var $sort = [];
							$target.closest('.wsko-co-priority-keywords-container').find('.wsko-co-priority-keyword-group').each(function(index){
								$(this).find('.wsko-co-priority-keyword').each(function(index){
									$sort.push($(this).data('keyword'));
								});
								//if ($(this).data('prio') == $target.data('prio'))
									//$sort.push($pk.data('keyword'));
							});
							if ($pk.data('prio') == $target.data('prio'))
							{
								window.wsko_post_element({action: 'wsko_co_sort_priority_keyword', sort: $sort, post_id: $pk.data('post'), nonce: $pk.closest('.wsko-co-priority-keywords-container').data('nonce-sort')}, false, false, $pk, false);
							}
							else
							{
								window.wsko_post_element({action: 'wsko_co_add_priority_keyword', sort: $sort, post_id: $pk.data('post'), keyword: $pk.data('keyword'), prio: $target.data('prio'), nonce: $pk.closest('.wsko-co-priority-keywords-container').data('nonce')},
									function(res){
										if (res.success)
										{ 
											/*$container.append(res.view);*/
											$pk.data('prio', $target.data('prio'));
											$('.wsko-prio-kw-count-1').text(res.count_pr1);
											$('.wsko-prio-kw-count-2').text(res.count_pr2);
											wsko_reload_issues($pk.data('post'), $pk);
										}
										else
										{ 
											if (res.limit)
											{
												//$('#wsko_keyword_limit_pro_notice').show();
											}
											$pk.appendTo($target.closest('.wsko-co-priority-keywords-container').find('.wsko-co-priority-keyword-group[data-prio="'+$pk.data('prio')+'"]'));
										}
									}, false, $pk, false);
							}
						}
						/*else if ($target.hasClass('wsko-co-similar-prio-kw-list'))
						{
							var $sort = [];
							$target.find('.wsko-co-priority-keyword').each(function(index){
								$sort.push($(this).data('keyword'));
							});
							$sort.push($pk.data('keyword'));
							window.wsko_post_element({action: 'wsko_co_add_similar_priority_keyword', sort: $sort, post_id: $target.closest('.wsko-co-priority-keywords-container').data('post'), keyword: $pk.data('keyword'), keyword_key: $target.data('keyword'), nonce: $target.closest('.wsko-co-priority-keywords-container').data('nonce-similar')},
								function(res){
									$pk.remove();
									if (res.success)
									{
										wsko_reload_issues($pk.data('post'), $pk);
										wsko_reload_linking($pk.data('post'), $pk);
										$target.closest('.wsko-co-priority-keyword').replaceWith(res.view);
										wsko_set_keyword_elements();
									}
								}, false, $target, false);
						}*/
					}
					else if ($pk.hasClass('wsko-co-similar-prio-kw'))
					{
						/*if ($target.hasClass('wsko-co-priority-keyword-group'))
						{
							var $sort = [];
							$target.closest('.wsko-co-priority-keywords-container').find('.wsko-co-priority-keyword-group').each(function(index){
								$(this).find('.wsko-co-priority-keyword').each(function(index){
									$sort.push($(this).data('keyword'));
								});
							});
							$sort.push($pk.data('keyword'));
							window.wsko_post_element({action: 'wsko_co_add_priority_keyword', sort: $sort, post_id: $pk.data('post'), keyword: $pk.data('keyword'), prio: false, nonce: $pk.closest('.wsko-co-priority-keywords-container').data('nonce')}, function(res){ $pk.remove(); $container.append(res.view); wsko_reload_issues($pk.data('post'), $pk);
											if (!res.success && res.limit)
											{
												$('.wsko-prio-kw-count-1').text(res.count_pr1);
												$('.wsko-prio-kw-count-2').text(res.count_pr2);
												//$('#wsko_keyword_limit_pro_notice').show();
											} }, false, $pk, false);
						}
						else*/ if ($target.hasClass('wsko-co-similar-prio-kw-list'))
						{
							var $sort = [];
							$target.find('.wsko-co-similar-prio-kw').each(function(index){
								$sort.push($(this).data('keyword'));
							});
							//$sort.push($pk.data('keyword'));
							if ($pk.data('keyword-key') == $target.data('keyword'))
							{
								window.wsko_post_element({action: 'wsko_co_sort_priority_keyword', sort: $sort, similar:$pk.data('keyword-key'), post_id: $pk.data('post'), nonce: $pk.closest('.wsko-co-priority-keywords-container').data('nonce-sort')}, false, false, $pk, false);
							}
							else
							{
								window.wsko_post_element({action: 'wsko_co_add_similar_priority_keyword', sort: $sort, post_id: $target.closest('.wsko-co-priority-keywords-container').data('post'), keyword: $pk.data('keyword'), keyword_key: $target.data('keyword'), nonce: $target.closest('.wsko-co-priority-keywords-container').data('nonce-similar')},
									function(res){
										$pk.remove();
										if (res.success)
										{
											wsko_reload_issues($pk.data('post'), $pk);
											wsko_reload_linking($pk.data('post'), $pk);
											$target.closest('.wsko-co-priority-keyword').replaceWith(res.view);
											wsko_set_keyword_elements();
										}
									}, false, $target, false);
							}
						}
					}
					$pk.css({'top': '0px', 'left': '0px'});
					$('.wsko-co-priority-keyword-group').each(function(index){
						var $this = $(this);
						if($this.children().length == 1)
							$this.find('.wsko-co-keyword-group-no-items').show();
						else
							$this.find('.wsko-co-keyword-group-no-items').hide();
					});
				},
				/*over: function(event, ui) {
					var $pk = $(ui.draggable.eq(0));
					$(this).append('<div class="wsko-co-priority-keyword-placeholder">'+$pk.data('keyword')+'</div>');
				},
				out: function(event, ui) {
				   $(this).find('.wsko-co-priority-keyword-placeholder').remove();
				}*/
			}).disableSelection();
			$('.wsko-co-priority-keyword:not(.wsko_init)').addClass('wsko_init').each(function(index){ 
				var $this = $(this);
				$this.draggable({connectToSortable: ".wsko-co-priority-keyword-group", containment: $this.closest('.wsko-co-priority-keywords-container') });
			});
			$('.wsko-co-keyword-draggable:not(.wsko_init)').addClass('wsko_init').each(function(index){ 
				var $this = $(this);
				$this.draggable({connectToSortable: ".wsko-co-priority-keyword-group,.wsko-co-similar-prio-kw-list", containment: $this.closest('.wsko-content-optimizer'), revert: true, scroll: false, helper: "clone", appendTo: $this.closest('.wsko-content-optimizer')});
			});
			$('.wsko-co-similar-prio-kw:not(.wsko_init)').addClass('wsko_init').each(function(index){ 
				var $this = $(this);
				$this.draggable({connectToSortable: $this.closest(".wsko-co-similar-prio-kw-list"), containment: $this.closest('.wsko-co-priority-keywords-container') });
			});
		}
		$('.wsko-co-keyword-input:not(.wsko_init)').addClass('wsko_init').each(function(index){
			var timeout;
			var $this = $(this),
			$container = $this.closest('.wsko-co-priority-keywords-container'),
			$suggests = $container.find('.wsko-co-keyword-suggests'),
			$loader = $container.find('.wsko-co-keyword-suggests-loader');
			$this.on('keyup change', function(){
				if (timeout)
					clearTimeout(timeout);
				if ($this.val() != $this.data('old-val'))
				{
					timeout = setTimeout(function()
					{
						$loader.show();
						$suggests.hide();
						window.wsko_post_element({action: 'wsko_co_get_keyword_suggests', keyword: $this.val(), nonce: $this.data('nonce')},
							function(res){
								if (res.success)
								{
									$this.data('old-val', $this.val());
									$suggests.html(res.view);
									if ($suggests.find('li').length != 0)
									{
										$suggests.show();
										$suggests.find('.wsko-co-keyword-suggestion').click(function(event){
											event.preventDefault();
											$this.val($(this).data('val')).focus().change();
										});
										$suggests.find('.wsko-keyword-suggestion-add').click(function(event){
											event.preventDefault();
											event.stopPropagation();
											var $pk = $(this);
											window.wsko_post_element({action: 'wsko_co_add_priority_keyword', post_id: $pk.closest('.wsko-co-priority-keywords-container').data('post'), keyword: $pk.data('keyword'), prio: $pk.data('prio'), nonce: $pk.closest('.wsko-co-priority-keywords-container').data('nonce')},
												function(res){
													if (res.success)
													{
														var postid = $pk.closest('.wsko-co-priority-keywords-container').data('post');
														wsko_reload_linking(postid, $pk);
														wsko_reload_issues(postid, $pk);
														$pk.closest('.wsko-content-optimizer').find('.wsko-co-priority-keyword-group[data-prio="'+res.prio+'"]').append(res.view).find('.wsko-co-keyword-group-no-items').hide();
														$('.wsko-prio-kw-count-1').text(res.count_pr1);
														$('.wsko-prio-kw-count-2').text(res.count_pr2);
														wsko_set_keyword_elements();
													}
													else if (res.limit)
													{
														//$('#wsko_keyword_limit_pro_notice').show();
													}
												}, false, $pk, false);
											$pk.closest('.wsko-co-keyword-suggest-wrapper').find('input.wsko-co-keyword-input').focus();
										});
									}
								}
								else
								{
									$suggests.html("");
								}
								$loader.hide();
								return true;
							}, function(){
								$suggests.html("");
								$loader.hide();
								return true;
						}, false, false);
					}, 1000);
				}
			}).keypress(function(e){
				if(e.which == 13) {
					$(this).closest('.wsko-co-keyword-suggest-wrapper').find('.wsko-co-add-priority-keyword').click();
				}
			});
		});
		/*$('.keyword-suggest-inner-wrapper:not(.wsko-init)').addClass('wsko-init').each(function(index){ 
			var $this = $(this),
			$input = $this.closest('.wsko-co-keyword-suggest-wrapper').find('input.wsko-co-keyword-input');
			var timeout;
			$input.focusout(function(event){
				timeout = setTimeout(function(){
					$this.hide();
				}, 500)
			});
			$input.focusin(function(event){
				if (timeout) clearTimeout(timeout);
				$this.show();
			});
		});*/
		$('.wsko-co-add-similar-kw:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			$wrapper = $(this).closest('.wsko-co-priority-keyword');
			
			var $button = $(this),//.hide(),
				$input = $('<p data-tooltip="'+window.wsko_text('similar_kw_enter')+'"><input type="text" placeholder="'+window.wsko_text('similar_kw_add')+'" class="wsko-form-control wsko-similar-keyword-input"></p>').insertAfter($($wrapper).find('.wsko-prio-keyword-text-wrapper')).find('input').focus();
			$input.focusout(function(event){
				$input.fadeOut('fast', function(){
					$input.remove();
					//$button.fadeIn('fast');
				});
			}).keypress(function(e){
				if(e.which == 13)
				{
					e.preventDefault();
					$input.attr('readonly', true);
					window.wsko_post_element({action: 'wsko_co_add_similar_priority_keyword', post_id: $button.data('post'), keyword_key: $button.data('keyword'), keyword: $input.val(), nonce: $button.data('nonce')},
						function(res){
							if (res.success)
							{
								wsko_reload_issues($button.data('post'), $button);
								wsko_reload_linking($button.data('post'), $button);
								$button.closest('.wsko-co-priority-keyword').replaceWith(res.view).find('.wsko-co-keyword-group-no-items').hide();
								wsko_set_keyword_elements();
							}
						}, false, $input, false);
				}
			});
			window.wsko_init_core();
		});
		$('.wsko-co-delete-similar-kw:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $button = $(this);
			window.wsko_post_element({action: 'wsko_co_delete_similar_priority_keyword', post_id: $button.data('post'), keyword_key: $button.data('keyword-key'), keyword: $button.data('keyword'), nonce: $button.data('nonce')},
				function(res){
					if (res.success)
					{
						wsko_reload_issues($button.data('post'), $button);
						wsko_reload_linking($button.data('post'), $button);
						$button.closest('.wsko-co-similar-prio-kw').remove();
					}
				}, false, $button, false);
		});
	}
	
	//Content
	function wsko_set_content_elements()
	{
		$('.wsko-co-content-field-content:not(.wsko-init)').addClass('wsko-init').click(function(event){
		});
		$('.wsko-co-save-content:not(.wsko-init)').addClass('wsko-init').click(function(event){
			event.preventDefault();
			var $this = $(this),
			title = $this.closest('.wsko-co-update-content').find('.wsko-co-content-field-title').val(),
			content = $this.closest('.wsko-co-update-content').find('.wsko-co-content-field-content').val(),
			slug = $this.closest('.wsko-co-update-content').find('.wsko-co-content-field-slug').val();
			if (title && content)
			{
				window.wsko_post_element({action: 'wsko_co_save_content', post_id: $this.data('post'), title: title, content: content, slug:slug, nonce: $this.data('nonce')}, function(res) { wsko_reload_content_iframe(); wsko_reload_issues($this.data('post'), $this); }, false, $this, false);
			}
		});
		
		$('.wsko-nav-link:not(.wsko-side-init)').addClass('wsko-side-init').click(function(event){
			wsko_control_visual_editor();
		});
	}

	function wsko_control_visual_editor()
	{
		isTabContent = false;
		isTabVisualEditor = false;

		if( $('#wsko_co_nav_link_content').length ) {
			if( $('#wsko_co_nav_link_content').hasClass('wsko-nav-link-active') ) {
				isTabContent = true;
			}		
		}

		if( $('#wsko_content_tab_gutenberg').length ) {
			if( $('#wsko_content_tab_gutenberg').hasClass('wsko-nav-link-active') ) {
				isTabVisualEditor = true;
			}		
		} else {
			isTabVisualEditor = true;
		}

		if (isTabVisualEditor && isTabContent){
			$('.wsko-co-main').removeClass('is-html-editor').addClass('is-visual-editor');
		} else {
			$('.wsko-co-main').removeClass('is-visual-editor').addClass('is-html-editor');
		}
	}

	//Content Optimizer Sticky Options
	function wsko_post_widget_sticky()
	{
		/*var iScrollPos = 0;
		$(window).scroll(function () {
			var iCurScrollPos = $(this).scrollTop();
			if (iCurScrollPos > iScrollPos) {
				$('.wsko-co-widget.wsko-short-view-active .bsu-tabs').fadeOut();
				//$('.wsko-content-optimizer').addClass('wsko-nav-hidden');
			} else {
				$('.wsko-co-widget.wsko-short-view-active .bsu-tabs').fadeIn();
				//$('.wsko-content-optimizer').removeClass('wsko-nav-hidden');
			}
			iScrollPos = iCurScrollPos;
		});
		
		$('.wsko-short-view-active .wsko-nav a').not('.wsko-init').addClass('wsko-init').click(function () {
			event.preventDefault();
			if ($(this).closest('.wsko-short-view-active').length)
			{
				$('.wsko-co-widget').toggleClass('wsko-short-view-active');	
			}
		});
		*/
		$('.wsko-co-widget.wsko-short-view-active .bsu-tabs').fadeIn();
	}

	//Technicals
	function wsko_set_technical_seo_elements()
	{
		$('.wsko-co-canonical-type:not(.wsko-init)').addClass('wsko-init').change(function(event){
			var $this = $(this);
			if ($this.val() == '2' || $this.val() == '3')
				$this.closest('.wsko-co-technical-wrapper').find('.wsko-co-canonical-arg').show();
			else
				$this.closest('.wsko-co-technical-wrapper').find('.wsko-co-canonical-arg').hide();
		});
		$('.wsko-co-redirect-activate:not(.wsko-init),.wsko-co-redirect-type:not(.wsko-init),.wsko-co-redirect-to:not(.wsko-init)').addClass('wsko-init').change(function(event){
			//event.preventDefault();
			var $this = $(this),
			$wrapper = $this.closest('.wsko-co-technical-wrapper'),
			activate_redirect = $wrapper.find('.wsko-co-redirect-activate').is(':checked'),
			redirect_type = $wrapper.find('.wsko-co-redirect-type').val(),
			redirect_to = $wrapper.find('.wsko-co-redirect-to').val();
			
			var data = {activate_redirect: activate_redirect, redirect_type: redirect_type, redirect_to: redirect_to};
			window.wsko_post_element({action: 'wsko_co_save_technicals', post_id: $wrapper.data('post'), data: data, nonce: $wrapper.data('nonce')}, false, false, $wrapper, false);
		});
	}
	
	//Helpers
	function wsko_metas_changed($el)
	{
		if ($el && $el.length && !$el.hasClass('wsko-set-metas-wrapper'))
			$el = $el.closest('.wsko-set-metas-wrapper');
		if ($el && $el.length)
		{
			if (wsko_co_data.meta_view_autosave || $el.data('canonical'))
			{
				var ms_timeout = $el.data('wsko-meta-save-timeout');
				if (ms_timeout)
					clearTimeout(ms_timeout);
				$el.data('wsko-meta-save-timeout', setTimeout(function(){
					wsko_save_metas($el);
				}, 2000));
			}
			$el.find('.wsko-metas-save-button').addClass('wsko-button-success wsko-metas-changed');
		}
	}

	function wsko_save_metas($el)
	{
		if ($el && $el.length && !$el.hasClass('wsko-set-metas-wrapper'))
			$el = $el.closest('.wsko-set-metas-wrapper');
		if ($el && $el.length)
		{
			$el.trigger('wsko_metas_submit');
		}
	}

	function wsko_reload_content_iframe()
	{
		$('#wsko_co_content_iframe').attr('src', $('#wsko_co_content_iframe').attr('src'));
	}

	function wsko_reload_issues(post_id, $el)
	{
		var $widget = $el.closest('.wsko-content-optimizer'),
		$issues = $widget.find('.wsko-co-onpage-issues-wrapper').html(''),
		$score = $widget.find('.wsko-co-onpage-score-wrapper').html('');
		window.wsko_set_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="score"><div class=""></div></div>').appendTo($score));
		window.wsko_set_big_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="onpage_issues"><div class=""></div></div>').appendTo($issues));

		var $container = $issues.add($score);
		window.wsko_load_lazy_data_for_controller($container, 'wsko_optimizer', false, 'onpage_issues', {post_id: post_id}, false);
	}

	function wsko_reload_keywords(post_id, $el)
	{
		var $widget = $el.closest('.wsko-content-optimizer'),
		$keywords = $widget.find('.wsko-reloadable-keywords').html('');
		window.wsko_set_big_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="tab_keywords"><div class=""></div></div>').appendTo($keywords));

		var $container = $keywords;
		window.wsko_load_lazy_data_for_controller($container, 'wsko_optimizer', false, 'keywords', {post_id: post_id}, false);
	}

	function wsko_reload_linking(post_id, $el)
	{
		if (wsko_co_data.is_premium)
		{
			var $widget = $el.closest('.wsko-content-optimizer'),
			$linking = $widget.find('.wsko-reloadable-linking').html('');
			window.wsko_set_big_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="tab_linking"><div class=""></div></div>').appendTo($linking));

			var $container = $linking;
			window.wsko_load_lazy_data_for_controller($container, 'wsko_optimizer', false, 'linking', {post_id: post_id}, false);
		}
	}

	function wsko_reload_backlinks(post_id, $el)
	{
		if (wsko_co_data.is_premium)
		{
			var $widget = $el.closest('.wsko-content-optimizer'),
			$backlinks = $widget.find('.wsko-reloadable-backlinks').html('');
			window.wsko_set_big_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="tab_backlinks"><div class=""></div></div>').appendTo($backlinks));

			var $container = $backlinks;
			window.wsko_load_lazy_data_for_controller($container, 'wsko_optimizer', false, 'backlinks', {post_id: post_id}, false);
		}
	}

	function wsko_reload_performance(post_id, $el)
	{
		if (wsko_co_data.is_premium)
		{
			var $widget = $el.closest('.wsko-content-optimizer'),
			$performance = $widget.find('.wsko-reloadable-performance').html('');
			window.wsko_set_big_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="tab_performance"><div class=""></div></div>').appendTo($performance));

			var $container = $performance;
			window.wsko_load_lazy_data_for_controller($container, 'wsko_optimizer', false, 'performance', {post_id: post_id}, false);
		}
	}
	function wsko_reload_content(post_id, $el)
	{
		var $widget = $el.closest('.wsko-content-optimizer'),
		$content = $widget.find('.wsko-reloadable-content').html('');
		window.wsko_set_big_element_ajax_loader($('<div class="wsko-lazy-field" data-wsko-lazy-var="tab_content"><div class=""></div></div>').appendTo($content));

		var $container = $content;
		window.wsko_load_lazy_data_for_controller($container, 'wsko_optimizer', false, 'content', {post_id: post_id}, false);
	}

	//Rich Snippets
	window.wsko_set_rich_snippets = function()
	{
		if (wsko_co_data.is_premium)
		{
			$('#wsko_co_add_rich_snippet:not(.wsko_init)').addClass('wsko_init').click(function(){
				event.preventDefault();
				$('#wsko_co_add_rich_snippets_wrapper').toggle();
			});
			$('.wsko-co-rich-snippet-add-item:not(.wsko_init)').addClass('wsko_init').click(function(){
				event.preventDefault();
				var $this = $(this),
				$container = $this.closest('.wsko-rich-snippet-container'),
				type = $this.data('snippet'),
				type_name = $this.data('snippet-text');
				$('#wsko_co_add_rich_snippets_type_step').hide();
				$('#wsko_co_add_rich_snippets_config_step').show();
		
				$('#wsko_co_add_rich_snippets_type').val(type);
				//$('#wsko_co_add_rich_snippets_type_name').text(type_name);
				$('#wsko_co_add_rich_snippet_config_default').html('').removeClass('wsko-snippet-config-loaded');
		
				window.wsko_post_element({action: 'wsko_get_rich_snippet_config', type: type, nonce: $('#wsko_co_add_rich_snippets_type').data('config-nonce')},
					function(res){ if (res.success) { $('#wsko_co_add_rich_snippet_config_default').html(res.view).addClass('wsko-snippet-config-loaded'); window.wsko_set_rich_snippets(); return true; } },
					 false, $('#wsko_co_add_rich_snippets_type'), false);
			});
			$('#wsko_co_add_rich_snippet_reselect:not(.wsko_init)').addClass('wsko_init').click(function(){
				event.preventDefault();
				var $this = $(this);
				$('#wsko_co_add_rich_snippets_type_step').show();
				$('#wsko_co_add_rich_snippets_config_step').hide();
			});
			$('#wsko_co_add_rich_snippet_confirm:not(.wsko_init)').addClass('wsko_init').click(function(){
				event.preventDefault();
				var $this = $(this),
				type = $('#wsko_co_add_rich_snippets_type').val(),
				post = $this.data('post'),
				data = $('#wsko_co_add_rich_snippets_config_step').find(':input:not(.wsko-rs-template-input)').serialize();

				if ($('#wsko_co_add_rich_snippet_config_default').hasClass('wsko-snippet-config-loaded'))
				{
					window.wsko_post_element({action: 'wsko_create_rich_snippet', type: type, post: post, data: data, nonce: $this.data('nonce')},
						function(res){ if (res.success) {
							$('#wsko_co_add_rich_snippets_type_step').show();
							$('#wsko_co_add_rich_snippets_config_step').hide();
							
							wsko_save_metas($this);
							//$('#wsko_co_add_rich_snippets_type_name').text('');
						} },
						false, $('#wsko_co_add_rich_snippets_type'), false);
				}
				else
				{
					window.wsko_notification(false, window.wsko_text('snippet_cfg_error'), '');
				}
			});
			$('.wsko-rich-snippets-setting-add:not(.wsko_init)').addClass('wsko_init').click(function(){
				event.preventDefault();
				var $this = $(this),
				$list = $this.closest('.wsko-rich-snippets-settings-list'),
				$temp = $list.find('.wsko-rich-snippets-setting-list-item:first').clone().show().appendTo($list),
				key = $this.data('key'),
				index = $list.data('index');
				if (!index)
					index = 0;
				$list.data('index', index+1);
				if (key)
				{
					$temp.find(':input').each(function(){
						$(this).attr('name', $(this).attr('name').replace(key, index));
					});
				}
				$temp.find('.wsko-rs-template-input:input').removeClass('wsko-rs-template-input');
				window.wsko_set_rich_snippets();
			});
			$('.wsko-rich-snippets-setting-list-item').not(':first').find('.wsko-rich-snippets-setting-delete:not(.wsko_init)').addClass('wsko_init').click(function(){
				event.preventDefault();
				var $this = $(this);
				$this.closest('.wsko-rich-snippets-setting-list-item').hide().remove();
			});
			$('.wsko-edit-rich-snippet-save:not(.wsko_init)').addClass('wsko_init').click(function(event){
				event.preventDefault();
				var $this = $(this);
				window.wsko_post_element({action: 'wsko_save_rich_snippet_config', key: $this.data('key'), location: $this.data('location'), post: $this.data('post'), data: $this.closest('.wsko-edit-rich-snippet-container').find(':input:not(.wsko-rs-template-input)').serialize(), nonce: $this.data('nonce')}, false, false, $this, false);
			});
			if (typeof(jQuery.ui) != 'undefined' && typeof(jQuery.ui.draggable) != 'undefined' && typeof(jQuery.ui.droppable) != 'undefined')
			{
				$('.wsko-metas-placeholder:not(.wsko-init)').addClass('wsko-init').draggable({ revert: true, helper: "clone" });
				$('.wsko-rich-snippet-setting').not('.wsko-dnd-init').addClass('wsko-dnd-init').droppable({
					accept: '.wsko-metas-placeholder',
					tolerance: 'pointer',
					drop: function( event, ui ) {
						var text = $(ui.draggable.eq(0)).data('tag'),
						element = $(this).get(0);

						//insert text
						if (document.selection) {  
							element.focus();  
							var sel = document.selection.createRange();  
							sel.text = text;  
							element.focus();  
						} else if (element.selectionStart || element.selectionStart === 0) {  
							var startPos = element.selectionStart;  
							var endPos = element.selectionEnd;  
							var scrollTop = element.scrollTop;  
							element.value = element.value.substring(0, startPos) + text +   
											element.value.substring(endPos, element.value.length);  
							element.focus();  
							element.selectionStart = startPos + text.length;  
							element.selectionEnd = startPos + text.length;  
							element.scrollTop = scrollTop;  
						} else {  
							element.value += text;  
							element.focus();  
						} 
						$(element).change();
					}
				});
			}
		}
	}

	//Interface
	window.wsko_open_optimizer_modal = function(post_id_or_url, type, open_tab){
		$modal = $('#wsko_content_optimizer_modal').addClass('wsko-modal-active'),
		$content = $modal.find('.wsko-modal-content').show().html('');
		$modal.find('.wsko-modal-multi-container').hide();
		$("body").addClass("modal-open");
		if (co_parent_view)
			$modal.find('.wsko-modal-multi-container-bar').show();
		else
			$modal.find('.wsko-modal-multi-container-bar').hide();
		$('#wsko_content_optimizer_modal .wsko-modal-loader').show();
		window.wsko_post_element({action: 'wsko_get_content_optimizer', post: post_id_or_url, type: type, open_tab: open_tab, nonce: wsko_co_data.content_optimizer_nonce}, 
			function(res){
				$('#wsko_content_optimizer_modal .wsko-modal-loader').hide();
				if (res.success)
				{
					$content.html(res.view);
					return true;
				}
				else
				{
					//$content.html("Content Optimizer could not be loaded. Please try again.");
					$('#wsko_content_optimizer_modal .wsko-modal-close').click();
				}
			},
			function()
			{
				$('#wsko_content_optimizer_modal .wsko-modal-loader').hide();
				//$content.html("A Server Error occured. Please try again.");
				$('#wsko_content_optimizer_modal .wsko-modal-close').click();
			}, false, false);
	};
	
	$(document).on("wsko_init_core", function(e){
		wsko_set_generals();
		wsko_set_meta_elements();
		wsko_set_content_elements();
		wsko_set_keyword_elements();
		wsko_set_technical_seo_elements();
		wsko_post_widget_sticky();
		window.wsko_set_rich_snippets();

		//Resizable Wrapper
		$('.wsko-resizable-wrapper:not(.wsko-init)').addClass('wsko-init').each(function(index) {
			var $this = $(this);
			$this.find('.wsko-rezisable-wrapper-thumb').mousedown(function(){
				window.wsko_move_resizable_wrapper = [$this,$this.offset().top];
			});
			$this.find('.wsko-resizable-wrapper-quick-up').click(function(index){
				event.preventDefault();
				event.stopPropagation();
				$this.css("height", $this.data('height')); //$this.css("min-height"));
				
				if (!$('.wsko-co-widget').hasClass('wsko-short-view-active'))
					$('.wsko-co-widget').toggleClass('wsko-short-view-active');
					
				//$('	.wsko-co-widget .wsko-nav li a').removeClass('wsko-nav-link-active');
				//$('	.wsko-co-widget .wsko-tab').removeClass('wsko-tab-active');
				//$('	.wsko-co-widget .wsko-nav li:first-child a').addClass('wsko-nav-link-active');
				//$('	.wsko-co-widget .wsko-tab:first-child').addClass('wsko-tab-active');
				
				$(window).scrollTop($(window).scrollTop()-1); //dirty refresh
				$(window).scrollTop($(window).scrollTop()+1); //dirty refresh
			});
			$this.find('.wsko-resizable-wrapper-quick-down').click(function(index){
				event.preventDefault();
				event.stopPropagation();
				$this.css("height", $this.css("max-height"));
				
				if ($('.wsko-co-widget').hasClass('wsko-short-view-active'))
					$('.wsko-co-widget').toggleClass('wsko-short-view-active');
				
				$(window).scrollTop($(window).scrollTop()-1); //dirty refresh
				$(window).scrollTop($(window).scrollTop()+1); //dirty refresh
			});
		});
	});
	var $doc = $(document);
	try{
		var doc = parent.document;
		if(!doc)
			throw new Error('Unaccessible');
		$doc.add($(window.top.document));
		// accessible
	}	catch(e){
		// not accessible
	}
	$doc.mousedown(function(){
		if (window.wsko_move_resizable_wrapper && window.wsko_move_resizable_wrapper !== false)
		{
			return false;
		}
	}).mousemove(function(e){
		if (window.wsko_move_resizable_wrapper && window.wsko_move_resizable_wrapper !== false)
		{
			var new_height = e.pageY-window.wsko_move_resizable_wrapper[1]+10;
			window.wsko_move_resizable_wrapper[0].css("height", new_height).data('height', new_height);
		}
	}).mouseup(function(e){
		if (window.wsko_move_resizable_wrapper && window.wsko_move_resizable_wrapper !== false) 
		{
			$(window).scrollTop($(window).scrollTop()-1); //dirty refresh
			$(window).scrollTop($(window).scrollTop()+1); //dirty refresh
			window.wsko_post_element({action: 'wsko_co_change_height', height:window.wsko_move_resizable_wrapper[0].height(), nonce:window.wsko_move_resizable_wrapper[0].data('nonce')},
				function(res){
					return true;
				}, function(){
					return true;
				}, false, false);
			window.wsko_move_resizable_wrapper = false;
		}
	});
	wsko_set_generals();
	wsko_set_meta_elements();
	wsko_set_content_elements();
	wsko_set_keyword_elements();
	wsko_set_technical_seo_elements();
	wsko_post_widget_sticky();
	window.wsko_set_rich_snippets();
});