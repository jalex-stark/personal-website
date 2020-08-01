<?php
if (!defined('ABSPATH')) exit;

if (current_user_can('manage_options'))
{
    $loc_sources = WSKO_Class_Premium::get_kw_locations();
    $sources = WSKO_Class_Premium::get_competitor_sources();

    $curr_s = WSKO_Class_Premium::get_premium_data('competitor_source');
    $curr_ls = WSKO_Class_Premium::get_premium_data('search_source');

    $updated = WSKO_Class_Core::get_option('premium_quota_updated');

    $collapsed = isset($template_args['collapsed']) ? $template_args['collapsed'] : false;
    $is_setup = isset($template_args['is_setup']) ? $template_args['is_setup'] : false;

    ?>
    <div class="wsko-settings-api-wrapper wsko-bavoko-api-wrapper bsu-panel panel panel-default">
        <div class="pull-right m10">
        </div>

        <?php
        if (!$collapsed) { ?>
            <p class="panel-heading m0"><?=__('BAVOKO API', 'wsko')?></p><?php 
        } else { ?>
            <div class="panel-heading m0"><a data-toggle="collapse" class="dark" data-parent="#wsko_setup_accordion" href="#bavoko"><i class="fa fa-angle-down fa-fw mr5"></i><?=__('BAVOKO API', 'wsko')?></a></div><?php 
        } ?>

        <div id="bavoko" class="panel-body <?=$collapsed ? 'panel-collapse collapse' : ''?>">
            <div class="wsko-settings-api-box">
				<div class="wsko-api-login-help-box" style="display:none;margin-bottom:10px">
				</div>
				<div class="wsko-api-login-help-box-custom" style="display:none;margin-bottom:10px">
				</div>
                <div class="row form-group">
                    <div class="col-sm-3">
                        <p class="m0"><?=__('Search - Competitors Database', 'wsko')?></p>
                        <small class="text-off"><?=__('Select a search engine for which your competitors should be displayed. Note: Competitor info is updated every 7 days.', 'wsko')?></small>
                    </div>
                    <div class="col-sm-9">
                        <select class="selectpicker wsko-ajax-input form-control show-tick" data-size="10" data-dropdown-align-right="true" data-wsko-target="api_premium" data-wsko-setting="competitor_source" data-live-search="true">
                            <optgroup label="Desktop">
                                <?php foreach ($sources['bing'] as $k => $s)
                                {
                                    $san_k = WSKO_Class_Premium::sanitize_competitor_source($k); ?><option value="<?=$k?>" <?=((!$curr_s && $k === 'us') || $curr_s === $k) ? 'selected' : ''?> data-content="<img class='mr5' src='<?=WSKO_PLUGIN_URL?>includes/famfamfam_flags/<?=$san_k?>.png'> <?=$san_k?> - <?=$s?>"></option><?php
                                } ?>
                                <?php foreach ($sources['google'] as $k => $s)
                                {
                                    $san_k = WSKO_Class_Premium::sanitize_competitor_source($k); ?><option value="<?=$k?>" <?=((!$curr_s && $k === 'us') || $curr_s === $k) ? 'selected' : ''?> data-content="<img class='mr5' src='<?=WSKO_PLUGIN_URL?>includes/famfamfam_flags/<?=$san_k?>.png'> <?=$san_k?> - <?=$s?>"></option><?php
                                } ?>
                            </optgroup>
                            <optgroup label="Mobile">
                                <?php foreach ($sources['mobile'] as $k => $s)
                                {
                                    $san_k = WSKO_Class_Premium::sanitize_competitor_source($k); ?><option value="<?=$k?>" <?=((!$curr_s && $k === 'us') || $curr_s === $k) ? 'selected' : ''?> data-content="<img class='mr5' src='<?=WSKO_PLUGIN_URL?>includes/famfamfam_flags/<?=$san_k?>.png'> <?=$san_k?> - <?=$s?>"></option><?php
                                } ?>
                            </optgroup>
                        </select>
                        <small class="text-off"><?=__('The Database source (or region) of your keywords search volume, competitors and keyword research data.', 'wsko')?></small>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-3">
                        <p class="m0"><?=__('Search - Keyword Info Database', 'wsko')?></p>
                        <small class="text-off"><?=__('Select a location from which Search Volume and CPC should be fetched.', 'wsko')?></small>
                    </div>
                    <div class="col-sm-9">
                        <select class="selectpicker form-control show-tick wsko-ajax-input" data-size="10" data-dropdown-align-right="true" data-live-search="true" data-wsko-target="api_premium" data-wsko-setting="search_source">
                            <?php foreach ($loc_sources as $k => $s)
                            {
                                ?><option value="<?=$k?>" <?=((!$curr_ls && $k === 'US') || $curr_ls === $k) ? 'selected' : ''?> data-content="<img class='mr5' src='<?=WSKO_PLUGIN_URL?>includes/famfamfam_flags/<?=strtolower($k)?>.png'> <?=$s?>"></option><?php
                            } ?>
                        </select>
                    </div>
                </div>

                <?php if (!$is_setup) { ?>
                    <div class="row form-group">
                        <div class="col-sm-3">
                            <p class="m0"><?=__('Controls', 'wsko')?></p>
                        </div>
                        <div class="col-sm-9">
                            <?php WSKO_Class_Template::render_recache_api_button('premium_keyword_infos', array('title' => __('Update Keyword Info Cache', 'wsko'), 'ajax_reload' => '.wsko-settings-api-wrapper')); ?>
                            <?php WSKO_Class_Template::render_delete_api_cache_button('premium_keyword_infos', array('title' => __('Clear Keyword Info Cache', 'wsko'), 'ajax_reload' => '.wsko-settings-api-wrapper')); ?>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-3">
                            <p class="m0"><?=__('Premium Quota', 'wsko')?></p>
                            <small class="text-off"><?=sprintf(__('Your current quota usage. Each counter is reset on a monthly basis. Values will update after a query. Last Update: %s', 'wsko'), $updated ? date('d.m.Y H:i', $updated) : __('never', 'wsko'))?></small>
                        </div>
                        <div class="col-sm-9">
                            <?php WSKO_Class_Template::render_template('premium/template-premium-quota.php', array()); ?>
                        </div>
                    </div> 
                <?php } ?>    
            </div>
        </div>
    </div><?php
} else {
	WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif', 'no_admin_api')));
} ?>