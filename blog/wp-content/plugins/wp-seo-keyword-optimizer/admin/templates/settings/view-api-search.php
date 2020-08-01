<?php
if (!defined('ABSPATH')) exit;

if (current_user_can('manage_options'))
{
	$collapsed = isset($template_args['collapsed']) ? $template_args['collapsed'] : false;
	$is_configured = WSKO_Class_Core::is_configured();
	$has_first_report = WSKO_Class_Core::get_option('search_query_first_run');
	$ga_client_se = WSKO_Class_Search::get_ga_client_se();
	$ga_token_se = WSKO_Class_Search::get_se_token();
	$ga_config_se = WSKO_Class_Search::get_se_property();
	$ga_site_ver = WSKO_Class_Search::get_site_verification();
	//$ga_ov_domain = WSKO_Class_Search::get_search_data('override_domain');
	$ga_valid_se = !WSKO_Class_Core::get_option('search_query_first_run') && $ga_token_se && $ga_config_se ? true : !WSKO_Class_Search::check_se_access(!WSKO_Class_Core::is_configured(), !$ga_config_se);
	?><div class="wsko-settings-api-wrapper bsu-panel panel panel-default">
		<div id="wsko_search_api_badge">
		<?php 
		WSKO_Class_Template::render_status_iconbar(array(
			array('condition' => $ga_client_se, 'text_t' => __('API loaded', 'wsko'), 'text_f' => __('API could not be loaded!', 'wsko')),
			array('condition' => $ga_token_se, 'text_t' => __('Credentials provided', 'wsko'), 'text_f' => __('No Credentials provided', 'wsko')),
			array('condition' => $ga_config_se, 'text_t' => __('Profile selected', 'wsko'), 'text_f' => __('No Profile selected', 'wsko')),
			array('condition' => $ga_valid_se, 'warning' => $ga_config_se ? false : true, 'text_t' => __('Permission granted', 'wsko'), 'text_f' => $ga_config_se ? __('Credentials invalid or insufficient permissions', 'wsko') : __('Credentials will be verified, when a profile is set', 'wsko')),
		), array('class' => 'pull-right m10')); ?>
		</div>
		
		<?php
		if (!$collapsed) { ?>
			<p class="panel-heading m0"><?=__('Google Search Console API', 'wsko')?></p><?php 
		} else { ?>
			<div class="panel-heading m0"><a data-toggle="collapse" class="dark" data-parent="#wsko_setup_accordion" href="#search"><i class="fa fa-angle-down fa-fw mr5"></i><?=__('Google Search Console API', 'wsko')?></a></div><?php 
		} ?>

		<div id="search" class="panel-body <?=$collapsed ? 'panel-collapse collapse' : ''?>">
			<div class="wsko-settings-api-box">
				<div class="wsko-api-login-help-box" style="display:none;margin-bottom:10px">
				</div>
				<div id="wsko_api_search_custom_help" class="wsko-api-login-help-box-custom" style="display:none;margin-bottom:10px">
				</div>
			<?php
				if ($ga_client_se)
				{
					if ($ga_token_se)
					{
						if ($ga_valid_se)
						{
							if ($is_configured && $ga_config_se)
							{
								if (!$has_first_report)
								{
									WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_search', 'first_report'), 'subnote' => wsko_loc('notif_search', 'first_report_sub', array('curr' => intval(WSKO_Class_Core::get_option('search_query_first_run_step')), 'all' => WSKO_SEARCH_INITIAL_REPORTS))));
								}
							}
						}
						else
						{
							WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_search', 'invalid_cred')));
						}
					}
				}
				if ($ga_client_se)
				{
					if ($ga_token_se)
					{
						if (!$ga_config_se)
						{
							?><div class="row">
								<div class="col-sm-3">
									<p><?=__('Select Property', 'wsko')?></p>
								</div>
								<div class="col-sm-9">
								<?php
									$profiles = WSKO_Class_Search::get_se_properties(false, true);
									if ($profiles && is_array($profiles))
									{
										?><div style="margin-bottom:10px">
											<select class="selectpicker wsko-ajax-input form-control show-tick" data-wsko-target="api_search" data-wsko-setting="token" data-live-search="true" title="<?=__('Choose your Profile', 'wsko')?>">
												<option value="false"><?=__('None', 'wsko')?></option><?php
												foreach ($profiles as $prof)
												{
													$selected = false;
													if ($prof['url'] == $ga_config_se)
														$selected = true;
													?><option data-subtext="<?=$prof['access']?>" value="<?=$prof['url']?>" <?=$selected ? 'selected' : ''?> <?=($prof['access'] != 'siteOwner' && $prof['access'] != 'siteFullUser') ? 'disabled' : ''?>><?=$prof['url']?></option><?php
												}
											?></select>
										</div>
										<?php
										if ($ga_config_se)
										{
											?><small class="text-off"><?=sprintf(__('Selected Domain: %s', 'wsko'), $ga_config_se)?></small><br/><?php
										} 			
									}
									else
									{
										_e('No accounts/profiles found!', 'wsko');
									} ?>
								</div>
							</div><?php
						} ?>
						<div class="row form-group">
							<div class="col-sm-3">
								<p class="m0"><?=__('Controls', 'wsko')?></p>
							</div>
							<div class="col-sm-9"><?php
								if ($ga_valid_se)
								{
									if ($is_configured)
									{
										if ($has_first_report)
										{
											WSKO_Class_Template::render_recache_api_button('ga_search', array('title' => __('Update Keyword Cache', 'wsko'), 'ajax_reload' => '.wsko-settings-api-wrapper'));
											WSKO_Class_Template::render_delete_api_cache_button('ga_search', array('title' => __('Clear Keyword Cache', 'wsko'), 'ajax_reload' => '.wsko-settings-api-wrapper'));
										}
									}
									else
									{
										WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif', 'api_setup_controls')));
									}
								}
								WSKO_Class_Template::render_revoke_api_button('ga_search', array('ajax_reload' => '.wsko-settings-api-wrapper')); ?>
							</div>
						</div><?php
					}
					else
					{
						$auth_url = WSKO_Class_Search::get_se_auth_url();
						if (!$auth_url)
						{
							WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_search', 'auth_url_fail')));
						}
						else
						{
							?>
							<div class="row form-group">
								<form class="wsko-admin-request-api-access" method="POST" data-nonce="<?=wp_create_nonce('wsko_request_api_access')?>" data-api="ga_search">
									<div class="col-sm-3">
										<p class="m0"><?=__('Access Token', 'wsko')?></p>
									</div>
									<div class="col-sm-9">
										<input placeholder="<?=__('Insert Access Token', 'wsko')?>" class="form-control wsko-token-field mb10" type="text" name="code" autocomplete="off" required>
										<a class="wp-core-ui button" style="margin-bottom: 5px;" href="<?=$auth_url?>" target="_blank"><?=__('Get Access Token', 'wsko')?></a>
										<input class="wsko-request-btn wp-core-ui button wsko-button-success" type="submit" value="Login">
									</div>
								</form>
							</div>
							<?php
						}
					}
					?>
					<div class="row form-group" style="padding: 0px; margin-bottom: 0px;">
						<div class="col-sm-12">
							<div class="wsko-collapse-wrapper">
								<p style="margin-bottom:10px;"><a href="#" class="wsko-collapse dark wsko-text-off wsko-mb-10"><?=__('Not connected to Google Search Console yet?', 'wsko')?></a></p>
								<div class="wsko-collapse-content" style="max-height:0px;">						
									<div class="row form-group">
										<div class="col-sm-3">
											<p class="m0"><?=__('Site Verification - HTML Tag', 'wsko')?></p>
											<small class="text-off">See <a target="_blank" href="<?=WSKO_Class_Search::get_external_link('property_guide')?>"><?=__('Google Documentation</a> for more information.', 'wsko')?></small>
										</div>
										<div class="col-sm-9">
											<input class="wsko-ajax-input form-control" data-wsko-target="api_search" data-wsko-setting="site_verification" value="<?=$ga_site_ver?>">
											<small class="text-off"><?=__('Insert your verification code to auto-create a HTML-Meta Tag.', 'wsko')?></small>
										</div>
									</div>
								</div>
							</div>
							<?php /*
							<div class="wsko-collapse-wrapper">
								<p style="margin-bottom:10px;"><a href="#" class="wsko-collapse dark wsko-text-off wsko-mb-10"><?=sprintf(__('"%s" is used to get your Search Console property. Correct domain?', 'wsko'), $ga_config_se)?></a></p>
								<div class="wsko-collapse-content" style="max-height:0px;">						
									<div class="row form-group">
										<div class="col-sm-3">
											<p class="m0"><?=__('Override Domain', 'wsko')?></p>
											<small class="text-off"><?=__('If you can't connect to the Search API, check for discrepancies between the default domain and the actual property url in your Search Console.', 'wsko')?></small>
										</div>
										<div class="col-sm-9">
											<input class="wsko-ajax-input form-control" data-wsko-target="api_search" data-wsko-setting="override_domain" value="<?=$ga_ov_domain?>" placeholder="https://www.example.com/">
											<small class="text-off"><?=sprintf(__('Default: %s', 'wsko'), WSKO_Class_Helper::get_host_base())?></small>
										</div>
									</div>
								</div>
							</div> */ ?>
						</div>
					</div>
					<?php
				}
				else
				{
					WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'google_api_error_box')));
				}
				?>
			</div>
		</div>
	</div><?php
} else {
	WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'no_admin_api')));
} ?>
