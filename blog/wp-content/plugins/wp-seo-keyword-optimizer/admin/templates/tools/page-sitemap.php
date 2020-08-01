<?php
if (!defined('ABSPATH')) exit;

$onpage_data = WSKO_Class_Onpage::get_onpage_data();
$sitemap_params = WSKO_Class_Onpage::get_sitemap_params();

$post_types = WSKO_Class_Helper::get_public_post_types('objects');
$taxonomies = WSKO_Class_Helper::get_public_taxonomies('objects');
$post_stati = get_post_stati(array('public' => true), 'objects');

$sitemap_path = get_home_path().'sitemap.xml';
$index_file = false;
$real_files = false;
if (file_exists($sitemap_path))
{
	$sitemap = file_get_contents($sitemap_path);
	if ($sitemap)
	{
		$sitemap_element = new SimpleXMLElement($sitemap);
		if (isset($sitemap_element->sitemap))
		{
			$index_file = home_url('sitemap.xml');
			foreach($sitemap_element->sitemap as $sitemap)
			{
				$real_files[]= $sitemap->loc;
			}
		}
		else
		{
			$real_files = array($sitemap_path);
		}
	}
}
$sitemap_active = WSKO_Class_Core::get_setting('automatic_sitemap');
?>
<div class="row">
	<?php
	if (current_user_can('manage_options')) {
		if (is_writable(ABSPATH))
		{ 		
			if ($sitemap_active) {	?>
				<div class="col-sm-12 col-xs-12">
					<div class="bs-callout wsko-notice wsko-notice-success" style="margin-bottom: 15px;">
						<p><?=sprintf(__('If you want to register a change as soon as possible please follow <a href="%s" target="_blank">this instruction</a> to submit your sitemap to the Google Search Console manually.', 'wsko'), WSKO_Class_Search::get_external_link('sitemap_guide'))?></p>
					</div>
				</div>	
			<?php } ?>	
			<div class="bsu-panel bsu-panel-custom col-md-12">
				<div class="panel panel-default">
					<p class="panel-heading m0"><?=__('Current Sitemap', 'wsko')?></p>
					<div class="panel-body">
						<div class="wsko-row row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p><?=__('Download current XML Sitemap', 'wsko')?></p>
								<small class="text-off"><?=home_url('sitemap_bst.xml')?></small>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<?php /* <a class="btn btn-flat" href="<?=home_url('sitemap.xml')?>" target="_blank" download>Download Sitemap Register</a> */ ?>
								<?php 
								if ($real_files) {
									$count_real = count($real_files);
									if ($index_file)
									{
										?><a class="button" style="margin-right:5px;" href="<?=$index_file?>" target="_blank" download><?=__('Download Sitemap Index', 'wsko')?></a><?php
									}
									if ($count_real == 1 && !$index_file)
									{
										?><a class="button" style="margin-right:5px;" href="<?=$real_files[0]?>" target="_blank" download><?=__('Download Sitemap', 'wsko')?></a><?php
									}
									else
									{
										if ($real_files)
										{
											$count = 1;
											foreach($real_files as $file)
											{
												?><a class="button" style="margin-right:5px;" href="<?=$file?>" target="_blank" download><?=__('Download Sitemap', 'wsko')?><?=$count_real > 1 ? ' Part '.$count : ''?></a><?php
												$count++;
											}
										}
									} ?>
								<?php } else { ?>
									<span class="text-off"><?=__('No Sitemap available.', 'wsko')?></span>
								<?php } ?>
							</div>
						</div>
						<div class="wsko-row row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p><?=__('Controls', 'wsko')?></p>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<?php WSKO_Class_Template::render_ajax_button(__('Regenerate Sitemap', 'wsko'), 'update_sitemap_real', array(), array()) ?>
							</div>	
						</div>
					</div>	
				</div>
			</div>
			<div id="wsko_sitemap_settings_wrapper" class="bsu-panel bsu-panel-custom col-sm-12" data-nonce="<?=wp_create_nonce('wsko_update_sitemap')?>">
				<div class="panel panel-default">
					<p class="panel-heading m0 wsko-sitemap-settings-heading"><?=__('Sitemap Settings','wsko')?> <span id="wsko_sitemap_status_beacon"><?php WSKO_Class_Template::render_badge($sitemap_active, array('class' => 'pull-right', 'title' => $sitemap_active ? __('Active - Last update', 'wsko') . ' '. (isset($onpage_data['last_sitemap_upload']) ? date('d M, Y H:i', $onpage_data['last_sitemap_upload']) : __('Never', 'wsko')) : __('Inactive', 'wsko'), 'style' => 'float: none !important; margin: 0px; margin-left: 10px;')) ?></span></p>
					<div class="panel-body">
						<div class="wsko-row row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p><?=__('Automatic Sitemap Generation', 'wsko');?></p>
								<p class="font-unimportant"><?=__('Sitemap will be automatically generated every hour.', 'wsko');?></p>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<label><input class="wsko-sitemap-param-activate wsko-switch form-control" type="checkbox" <?=$sitemap_active ? 'checked="checked"' : ''?>><?=__('Activate automatic Sitemap', 'wsko')?></label>
							</div>
						</div>
						<div class="wsko-row row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p><?=__('Automatic Sitemap Ping', 'wsko');?></p>
								<p class="font-unimportant"><?=__('Sitemap will be sent to Google and Bing in hourly intervals after you changed something in your content.', 'wsko');?></p>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<label><input class="wsko-sitemap-param-ping wsko-switch form-control" type="checkbox" <?=WSKO_Class_Core::get_setting('automatic_sitemap_ping') ? 'checked="checked"' : ''?>><?=__('Activate Ping', 'wsko')?></label>
							</div>
						</div>
						<div class="wsko-row row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p><?=__('Post Type Settings', 'wsko');?></p>
								<p class="font-unimportant"><?=__('Select and configure post types, that should be included in the sitemap', 'wsko');?></p>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<div class="row">
									<div class="col-sm-3 col-xs-3 col-sm-offset-6 align-right">
										<span><?=__('Frequency', 'wsko')?> <?=WSKO_Class_Template::render_infoTooltip(__('The estimate rate you are going to update your website with.', 'wsko'), 'info');?></span>
									</div>
									<div class="col-sm-3 col-xs-3 align-right">
										<span><?=__('Priority', 'wsko')?> <?=WSKO_Class_Template::render_infoTooltip(__('Set a value between 0 and 1 for low or respective high priority.' ,'wsko'), 'info');?></span>
									</div>
								</div>
								<ul style="list-style-type:none;max-height:300px;overflow-y:auto;overflow-x:hidden;">
									<?php 
									foreach($post_types as $pt)
									{
										$data = false;
										if (isset($sitemap_params['types'][$pt->name]))
											$data = $sitemap_params['types'][$pt->name];
											
										?><li class="wsko-sitemap-param-type">
											<div class="row">
												<div class="col-sm-6 col-xs-12">
													<label>
														<input class="form-control wsko-sitemap-type-activate" type="checkbox" name="post_types[]" value="<?=$pt->name?>" <?=(!$data && ($pt->name == 'page' || $pt->name == 'post')) || $data ? 'checked' : ''?>> <?=$pt->label?> (<?=$pt->name?>)
													</label>
												</div>
												<div class="col-sm-6 col-xs-12 align-right">
													<div class="wsko-sitemap-sub-params">
														<div class="row">
															<div class="col-sm-6 col-xs-6">
																<select class="wsko-sitemap-subparam-freq form-control" <?=!$data ? 'disabled' : ''?>>
																	<option value="always" <?=$data && $data['freq'] == 'always' ? 'selected' : ''?>><?=__('Always (generated new every time)', 'wsko')?></option>
																	<option value="hourly" <?=$data && $data['freq'] == 'hourly' ? 'selected' : ''?>><?=__('Hourly', 'wsko')?></option>
																	<option value="daily" <?=$data && $data['freq'] == 'daily' ? 'selected' : ''?>><?=__('Daily', 'wsko')?></option>
																	<option value="weekly" <?=$data && $data['freq'] == 'weekly' ? 'selected' : ''?>><?=__('Weekly', 'wsko')?></option>
																	<option value="monthly" <?=!$data || $data && $data['freq'] == 'monthly' ? 'selected' : ''?>><?=__('Monthly', 'wsko')?></option>
																	<option value="yearly" <?=$data && $data['freq'] == 'yearly' ? 'selected' : ''?>><?=__('Yearly', 'wsko')?></option>
																	<option value="never" <?=$data['freq'] == 'never' ? 'selected' : ''?>><?=__('Never (archived)', 'wsko')?></option>
																</select>
															</div>
															<div class="col-sm-6 col-xs-6">
																<input class="wsko-sitemap-subparam-prio form-control" placeholder="<?=__('0.0 - 1.0 | Default: 0.5', 'wsko')?>" <?=!$data ? 'disabled' : ''?> value="<?=$data && isset($data['prio']) && ($data['prio'] || $data['prio'] === 0) ? $data['prio'] : ''?>">
															</div>
														</div>	
													</div>
												</div>
											</div>	
										</li><?php
									}
									?>
								</ul>
							</div>
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p><?=__('Taxonomy Settings', 'wsko');?></p>
								<p class="font-unimportant"><?=__('Select and configure taxonomy archives, that should be included in the sitemap', 'wsko');?></p>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<ul style="list-style-type:none;max-height:300px;overflow-y:auto;overflow-x:hidden;">
									<?php 
									foreach($taxonomies as $tax)
									{
										$data = false;
										if (isset($sitemap_params['tax'][$tax->name]))
											$data = $sitemap_params['tax'][$tax->name];
											
										?><li class="wsko-sitemap-param-tax">
											<div class="row">
												<div class="col-sm-6 col-xs-12">
													<label>
														<input class="form-control wsko-sitemap-tax-activate" type="checkbox" name="tax[]" value="<?=$tax->name?>" <?=$data ? 'checked' : ''?>> <?=$tax->label?> (<?=$tax->name?>)
													</label>
												</div>
												<div class="col-sm-6 col-xs-12 align-right">
													<div class="wsko-sitemap-sub-params">
														<div class="row">
															<div class="col-sm-6 col-xs-6">
																<select class="wsko-sitemap-subparam-freq form-control" <?=!$data ? 'disabled' : ''?>>
																	<option value="always" <?=$data && $data['freq'] == 'always' ? 'selected' : ''?>><?=__('Always (generated new every time)', 'wsko')?></option>
																	<option value="hourly" <?=$data && $data['freq'] == 'hourly' ? 'selected' : ''?>><?=__('Hourly', 'wsko')?></option>
																	<option value="daily" <?=$data && $data['freq'] == 'daily' ? 'selected' : ''?>><?=__('Daily', 'wsko')?></option>
																	<option value="weekly" <?=$data && $data['freq'] == 'weekly' ? 'selected' : ''?>><?=__('Weekly', 'wsko')?></option>
																	<option value="monthly" <?=!$data || $data && $data['freq'] == 'monthly' ? 'selected' : ''?>><?=__('Monthly', 'wsko')?></option>
																	<option value="yearly" <?=$data && $data['freq'] == 'yearly' ? 'selected' : ''?>><?=__('Yearly', 'wsko')?></option>
																	<option value="never" <?=$data['freq'] == 'never' ? 'selected' : ''?>><?=__('Never (archived)', 'wsko')?></option>
																</select>
															</div>
															<div class="col-sm-6 col-xs-6">
																<input class="wsko-sitemap-subparam-prio form-control" placeholder="0.0 - 1.0 | Default: 0.5" <?=!$data ? 'disabled' : ''?> value="<?=$data && isset($data['prio']) && ($data['prio'] || $data['prio'] === 0) ? $data['prio'] : ''?>">
															</div>
														</div>	
													</div>
												</div>
											</div>	
										</li><?php
									}
									?>
								</ul>
							</div>
						</div>
						
						<?php /*
						<div class="wsko-row row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
								<p>Exclude posts</p>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
								<input class="wsko-sitemap-param-excposts form-control" placeholder="e. g. 99, 219, 55" value="<?=isset($sitemap_params['excluded_posts']) ? implode(", ", $sitemap_params['excluded_posts']) : ''?>">
							</div>
						</div>
						*/ ?>

						<div style="display:block;">
							<div class="row form-group">
								<div class="col-md-9 col-sm-offset-3">	
									<a class="mb10 btn btn-flat" data-toggle="collapse" data-target="#advance_sitemap_settings"><?=__('Advanced', 'wsko');?></a>
								</div>
							</div>	
							
							<div id="advance_sitemap_settings" class="collapse">
								<div class="wsko-row row form-group">
									<div class="wsko-col-sm-3 wsko-col-xs-12 col-sm-3 col-xs-12">
										<p><?=__('Public Post Status', 'wsko')?></p>
										<small class="text-off"><?=__('If you want to add posts with a custom post status other than "publish", select them here.', 'wsko')?></small>
									</div>
									<div class="wsko-col-sm-9 wsko-col-xs-12 col-sm-9 col-xs-12">
										<ul style="list-style-type:none;">
											<?php 
											foreach($post_stati as $ps)
											{
												?><li class="wsko-sitemap-param-status inline-block mr5"><label><input class="form-control wsko-sitemap-status-activate" type="checkbox" name="post_stati[]" value="<?=$ps->name?>" <?=in_array($ps->name, $sitemap_params['stati']) ? 'checked' : ''?>> <?=$ps->label?> (<?=$ps->name?>)</label></li><?php
											}
											?>
										</ul>
									</div>
								</div>
							</div>	
						</div>
					</div>	
				</div>
			</div>
			
			<?php /*
			
			<div class="bsu-panel bsu-panel-custom col-md-12">
				<div class="panel panel-default">
					<p class="panel-heading m0">Current Sitemap</p>
					<?php WSKO_Class_Template::render_table(array('Page', 'Last Modified', 'Update Frequenzy', 'Priority'), array(), array('ajax' => array('action' => 'wsko_table_tools', 'arg' => 'sitemap')));?>
				</div>
			</div>
			*/ ?>
		<?php }
		else
		{
			?><div class="col-md-12"><?php
			WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'sitemap_no_access')));
			?></div><?php
		}
	}
	else
	{
		?><div class="col-md-12"><?php
		WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'sitemap_no_admin')));
		?></div><?php
	} ?>
</div>