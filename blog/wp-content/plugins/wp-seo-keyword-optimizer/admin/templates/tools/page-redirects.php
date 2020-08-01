<?php
if (!defined('ABSPATH')) exit;

$redirect_data = WSKO_Class_Onpage::get_all_redirects();
?>
<div class="row">
<?php if (current_user_can('manage_options')) { ?>	
		<div class="bsu-panel bsu-panel-custom col-md-12 col-xs-12">
			<div class="panel panel-default">
				<p class="panel-heading m0"><a class="dark" data-toggle="collapse" href="#settings"><span class="pull-right"><i class="fa fa-angle-down font-unimportant"></i></span><?=__('404 to 301 Settings', 'wsko');?> <span id="wsko_404_to_301_status"><?=WSKO_Class_Template::render_badge(isset($redirect_data['redirect_404']) && $redirect_data['redirect_404']['activate'])?></span></a></p>
				<div id="settings" class="panel-collapse collapse">
					<div class="panel-body">
						<form id="wsko_automatic_redirect_form" data-nonce="<?=wp_create_nonce('wsko_update_automatic_redirect')?>">
							<div class="row form-group">
								<div class="col-md-3">
								<?=__('Activate 404 to 301', 'wsko')?>
									<br/><small class="text-off"><?=__('Redirect every 404 page to a specific URL (using 301 status code)', 'wsko')?></small>
								</div>
								<div class="col-md-9">
									<input class="form-control wsko-field-activate wsko-switch" type="checkbox" <?=isset($redirect_data['redirect_404']) && $redirect_data['redirect_404']['activate'] ? 'checked' : ''?>>
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-3">
									Redirect to', 'wsko')?>
								</div>
								<div class="col-md-9">
									<select class="form-control wsko-field-type">
										<option value="1" <?=isset($redirect_data['redirect_404']) && $redirect_data['redirect_404']['type'] == '1' ? 'selected' : ''?>><?=sprintf(__('Home URL (%s)', 'wsko'), home_url())?></option>
										<?php if (WSKO_Class_Helper::get_host_base() != home_url()) {?><option value="2" <?=isset($redirect_data['redirect_404']) && $redirect_data['redirect_404']['type'] == '2' ? 'selected' : ''?>><?=sprintf(__('Domain Home URL (%s)', 'wsko'), WSKO_Class_Helper::get_host_base())?></option><?php } ?>
										<option value="3" <?=isset($redirect_data['redirect_404']) && $redirect_data['redirect_404']['type'] == '3' ? 'selected' : ''?>><?=__('Custom URL', 'wsko')?></option>
										<option value="4" <?=isset($redirect_data['redirect_404']) && $redirect_data['redirect_404']['type'] == '4' ? 'selected' : ''?>><?=__('Parent Directory', 'wsko')?></option>
									</select>
								</div>
							</div>
							<div class="row form-group wsko-field-custom-wrapper">
								<div class="col-md-3">
								<?=__('Custom URL', 'wsko')?>
								</div>
								<div class="col-md-9">
									<input class="form-control wsko-field-custom" type="text" placeholder="/relative/link/ (optional)" value="<?=isset($redirect_data['redirect_404']) ? $redirect_data['redirect_404']['custom'] : ''?>">
								</div>
							</div>
							<div class="row form-group">
								<div class="col-md-3">
								</div>
								<div class="col-md-9">
									<button class="button wsko-save-btn" type="submit"><?=__('Save', 'wsko')?></button>
								</div>
							</div>
						</form>
					</div>	
				</div>	
			</div>
		</div>

		<div class="bsu-panel bsu-panel-custom col-md-12 col-xs-12">
			<div class="panel panel-default">
				<p class="panel-heading m0"><?=__('Automatic redirects', 'wsko')?></p>
				<div class="panel-body"><div class="row form-group" style="padding-bottom:15px;">
						<div class="col-sm-6 col-xs-12">
							<div class="row">
								<div class="col-md-6">
									<p><?=__('Auto Post Redirect', 'wsko')?></p>
									<small class="text-off"><?=__('Auto-add 301 redirects when a post URL is changed', 'wsko')?></small>
								</div>
								<div class="col-md-6">
									<label>
										<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('auto_post_slug_redirects') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="auto_post_slug_redirects">
										<?=__('Activate Auto Redirect', 'wsko')?>
									</label>
								</div>
							</div>	
						</div>	
						<div class="col-sm-6 col-xs-12">
							<div class="row">
								<div class="col-md-6">
									<p><?=__('Redirect Attachment Pages', 'wsko')?></p>
									<small class="text-off"><?=__('Redirect all attachment pages to their file URL or the attached post (if it has one)', 'wsko')?></small>
								</div>
								<div class="col-md-6">
									<?php $redirect_attachment_pages = WSKO_Class_Core::get_setting('redirect_attachment_pages'); ?>
									<select class="form-control wsko-switch wsko-ajax-input" data-wsko-target="settings" data-wsko-setting="redirect_attachment_pages">
										<option value="" <?=!$redirect_attachment_pages ? 'selected' : ''?>><?=__('None', 'wsko')?></option>
										<option value="post" <?=$redirect_attachment_pages == 'post' ? 'selected' : ''?>><?=__('Parent Post', 'wsko')?></option>
										<option value="file" <?=$redirect_attachment_pages == 'file' ? 'selected' : ''?>><?=__('File URL', 'wsko')?></option>
									</select>
								</div>
							</div>	
						</div>	
					</div>
				</div>	
			</div>
		</div>

		<div class="bsu-panel bsu-panel-custom col-md-12 col-xs-12">
			<div class="panel panel-default">
				<p class="panel-heading m0"><?=__('Add custom redirect', 'wsko')?></p>
				<div class="panel-body">
					<form id="wsko_add_redirect_form" data-nonce="<?=wp_create_nonce('wsko_add_redirect')?>">
						<?php /*
						<div class="row">
							<div class="wsko-redirect-type-infos wsko-infos-exact" style="display:none">Help Text "Exact"</div>
							<?php /*<div class="wsko-redirect-type-infos wsko-infos-starts_with" style="display:none">Help Text "Starts with"</div>
							<div class="wsko-redirect-type-infos wsko-infos-contains" style="display:none">Help Text "Contains"</div>* /?>
							<div class="wsko-redirect-type-infos wsko-infos-replace" style="display:none">Help Text "Contains"</div>
						</div>
						*/ ?>
						<div class="row form-group">
							<div class="col-md-3">
								<p><?=__('Redirect from', 'wsko')?></p>
							</div>
							<div class="col-md-3">
								<select class="form-control wsko-field-comp" data-ph-exact="https://www.domain.com/path" data-ph-contains="/relative/link/">
									<option value="exact" selected><?=__('Exact match', 'wsko')?></option>
									<option value="contains"><?=__('Contains', 'wsko')?></option>
								</select>
							</div>
							<div class="col-md-6">
								<input class="form-control wsko-field-page" name="page" type="text" placeholder="" required>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-3">
							<?=__('Redirect to', 'wsko')?>
							</div>
							<div class="col-md-3">
								<select class="form-control wsko-field-comp-to" data-ph-exact="https://www.domain.com/path" data-ph-replace="/relative/link/">
									<option value="exact" selected><?=__('Exact match', 'wsko')?></option>
									<option value="replace"><?=__('Replace', 'wsko')?></option>
								</select>
							</div>
							<div class="col-md-6">
								<input class="form-control wsko-field-redirect" name="redirect_to" type="text" placeholder="" required>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-3">
							<?=__('Redirect type', 'wsko')?>
							</div>
							<div class="col-md-9">
								<select class="form-control wsko-field-type">
									<option value="1"><?=__('301 (Permanent Redirect, SEO friendly)', 'wsko')?></option>
									<option value="2"><?=__('302 (Temporary Redirect)', 'wsko')?></option>
								</select>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-3">
							</div>
							<div class="col-md-9">
								<button class="button wsko-save-btn" type="submit"><?=__('Add Redirect', 'wsko')?></button>
							</div>
						</div>
					</form>
				</div>	
			</div>
		</div>
	<?php } ?>
	<div class="bsu-panel bsu-panel-custom col-md-12 col-xs-12">
		<div class="panel panel-default">
			<p class="panel-heading m0"><?=__('Current Redirects', 'wsko')?></p>
			<?php WSKO_Class_Template::render_lazy_field('redirects', 'small'); ?>
		</div>
	</div>
</div>