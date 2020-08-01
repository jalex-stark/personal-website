<?php
if (!defined('ABSPATH')) exit;

$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
$global_analysis_data_prem = WSKO_Class_Premium::get_onpage_analysis();
$post_types = $post_types_with_kw = WSKO_Class_Helper::get_public_post_types('objects');

$analysis = isset($global_analysis['current_report']) ? $global_analysis['current_report'] : false;

$chart_level_dist = array();
$level_dist = WSKO_Class_Premium::get_onpage_level_dist();
if ($level_dist)
{
    $chart_level_dist[] = array(__('Level 1', 'wsko'), $level_dist->level1, $level_dist->level1_u);
    $chart_level_dist[] = array(__('Level 2', 'wsko'), $level_dist->level2, $level_dist->level2_u);
    $chart_level_dist[] = array(__('Level 3', 'wsko'), $level_dist->level3, $level_dist->level3_u);
    $chart_level_dist[] = array(__('Level 4', 'wsko'), $level_dist->level4, $level_dist->level4_u);
    $chart_level_dist[] = array(__('Level 5+', 'wsko'), $level_dist->level5, $level_dist->level5_u);
    $chart_level_dist[] = array(__('No Level', 'wsko'), $level_dist->orphans, $level_dist->orphans_u);
}
WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Level Distribution', 'wsko' ), 'col' => '', 
	'custom' => $chart_level_dist ? WSKO_Class_Template::render_chart('column', array(__('Level', 'wsko'), __('Count Reachable', 'wsko'), __('Count Unreachable', 'wsko')), $chart_level_dist, array('colors' => array('#5cb85c', '#d9534f'), 'isStacked' => true, 'axisTitleY' => __('Page Count', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'), true) : WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true))); 
if ($post_types_with_kw)
{
    ?><ul class="nav nav-pills bsu-tabs-sm bsu-tabs border-dark">
        <?php
        $first = true;
        foreach ($post_types_with_kw as $type)
        {
            ?><li class="<?=$first ? 'active' : ''?>"><a data-toggle="tab" href="#wsko_linking_pt_<?=$type->name?>"><?=$type->label?></a></li><?php
            $first = false;
        } ?>
        <li class="<?=$first ? 'active' : ''?>"><a data-toggle="tab" href="#wsko_linking_static_pt_other"> <?=__('Other', 'wsko')?></a></li>
    </ul>
    <div class="tab-content row">
        <?php 
        $first = true;
        foreach ($post_types_with_kw as $pt)
        {
            $excluded_in = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('internal_linking_types_in_'.$pt->name));
            $excluded_out = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('internal_linking_types_out_'.$pt->name));
            ?><div id="wsko_linking_pt_<?=$pt->name?>" class="tab-pane fade <?=$first ? 'in active' : ''?>">
                <div class="bsu-panel col-sm-12 col-xs-12">
                    <div class="panel panel-default">
                        <p class="panel-heading m0"><a data-toggle="collapse" class="collapsed dark" href="#wsko_link_post_type_settings_<?=$pt->name?>"><i class="fa fa-angle-down pull-right" style="margin-top: 5px;"></i><?=__('Post Type Settings', 'wsko');?></a></p>
                        <div class="panel-collapse collapse" id="wsko_link_post_type_settings_<?=$pt->name?>">
                            <div class="panel-body">
                                <div class="row form-group">
                                    <div class="col-sm-3">
                                        <p class="m0"><?=__('Exclude incoming Post Types', 'wsko')?></p>
                                        <small class="text-off"><?=__('Exclude post types for incoming internal links', 'wsko')?></small>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="row wsko-settings-linking-in-post-types" style="overflow-y:auto;max-height:150px;">
                                            <?php 
                                            foreach ($post_types as $pt2)
                                            {
                                                ?><div class="col-md-3">
                                                    <label><input class="form-control wsko-ajax-input" type="checkbox" <?=in_array($pt2->name, $excluded_in) ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="internal_linking_types_in_<?=$pt->name?>" data-multi-parent=".wsko-settings-linking-in-post-types" value="<?=$pt2->name?>"><?=$pt2->label?> <span class="settings-sub font-unimportant"><?=$pt2->name?></span></label>
                                                </div><?php
                                            } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row form-group">
                                    <div class="col-sm-3">
                                        <p class="m0"><?=__('Exclude outgoing Post Types', 'wsko')?></p>
                                        <small class="text-off"><?=__('Exclude post types for outgoing internal links', 'wsko')?></small>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="row wsko-settings-linking-out-post-types" style="overflow-y:auto;max-height:150px;">
                                            <?php 
                                            foreach ($post_types as $pt2)
                                            {
                                                ?><div class="col-md-3">
                                                    <label><input class="form-control wsko-ajax-input" type="checkbox" <?=in_array($pt2->name, $excluded_out) ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="internal_linking_types_out_<?=$pt->name?>" data-multi-parent=".wsko-settings-linking-out-post-types" value="<?=$pt2->name?>"><?=$pt2->label?> <span class="settings-sub font-unimportant"><?=$pt2->name?></span></label>
                                                </div><?php
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                </div>
                <?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
                    'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Code', 'wsko'), __('URL', 'wsko'), array('name' => __('Level', 'wsko'), 'width' => '10%'), array('name' => __('Outgoing Links', 'wsko'), 'width' => '10%'), array('name' => __('Incoming Links', 'wsko'), 'width' => '10%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_linking_pt_'.$pt->name.'_table', 'ajax' => array('action' => 'wsko_table_tools', 'arg' => 'linking_pages', 'arg2' => $pt->name), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'level' => array('title' => __('Level', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_level']), 'links_c' => array('title' => __('Internal Links', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_p_links']), /*'ext_links_c' => array('title' => __('External Links', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_p_ext_links']),*/ 'rec_links_c' => array('title' => __('Incoming Links', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_p_rec_links']))), true))); ?>
            </div><?php 
            $first = false;
        } ?>
        <div id="wsko_linking_static_pt_other" class="tab-pane fade <?=$first ? 'in active' : ''?>">
                <?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
                    'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Code', 'wsko'), __('URL', 'wsko'), array('name' => __('Level', 'wsko'), 'width' => '10%'), array('name' => __('Outgoing Links', 'wsko'), 'width' => '10%'), array('name' => __('Incoming Links', 'wsko'), 'width' => '10%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_linking_pt_'.$pt->name.'_table', 'ajax' => array('action' => 'wsko_table_tools', 'arg' => 'linking_pages'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'level' => array('title' => __('Level', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_level']), 'links_c' => array('title' => __('Internal Links', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_p_links']), /*'ext_links_c' => array('title' => __('External Links', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_p_ext_links']),*/ 'rec_links_c' => array('title' => __('Incoming Links', 'wsko'), 'type' => 'number_range', 'max' => $global_analysis_data_prem['current_report']['max_p_rec_links']))), true))); ?>
        </div>
    </div><?php
}
else
{
    WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_co', 'linking_no_kws')));
} ?>