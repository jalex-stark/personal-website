<?php
if (!defined('ABSPATH')) exit;

$global_analysis_data = WSKO_Class_Onpage::get_onpage_analysis();
?>
<div class="row">
    <div class="col-sm-12 col-xs-12">
    <?php
        $chart_dist_fb = array();
        $chart_dist_tw = array();
        $analysis = false;
        if ($global_analysis_data && isset($global_analysis_data['current_report']))
        {
            $analysis = $global_analysis_data['current_report'];
            foreach($analysis['facebook_meta_dist'] as $k => $dupl)
            {
                switch ($k) {
                    case 'title_length': $k = __('Title Length', 'wsko'); break;
                    case 'desc_length': $k = __('Description Length', 'wsko'); break;
                    case 'image': $k = 'Image'; break;
                }
                $chart_dist_fb[]= array($k,
                    array('v' => $dupl[0], 'f' => WSKO_Class_Helper::format_Number($dupl[0])), 
                    array('v' => $dupl[1], 'f' => WSKO_Class_Helper::format_Number($dupl[1])), 
                    array('v' => isset($dupl[2]) ? $dupl[2] : 0, 'f' => WSKO_Class_Helper::format_Number(isset($dupl[2]) ? $dupl[2] : 0)), 
                    array('v' => isset($dupl[3]) ? $dupl[3] : 0, 'f' => WSKO_Class_Helper::format_Number(isset($dupl[3]) ? $dupl[3] : 0))
                );
            }
            foreach($analysis['twitter_meta_dist'] as $k => $dupl)
            {
                switch ($k) {
                    case 'title_length': $k = __('Title Length', 'wsko'); break;
                    case 'desc_length': $k = __('Description Length', 'wsko'); break;
                    case 'image': $k = 'Image'; break;
                }
                $chart_dist_tw[]= array($k,
                    array('v' => $dupl[0], 'f' => WSKO_Class_Helper::format_Number($dupl[0])), 
                    array('v' => $dupl[1], 'f' => WSKO_Class_Helper::format_Number($dupl[1])), 
                    array('v' => isset($dupl[2]) ? $dupl[2] : 0, 'f' => WSKO_Class_Helper::format_Number(isset($dupl[2]) ? $dupl[2] : 0)), 
                    array('v' => isset($dupl[3]) ? $dupl[3] : 0, 'f' => WSKO_Class_Helper::format_Number(isset($dupl[3]) ? $dupl[3] : 0))
                );
            }
        }
        $matrix_fb = array(
            array('og_title_length:0:0', 'og_desc_length:0:0', 'og_img_provided:0:0'),
            array('og_title_length:'.WSKO_ONPAGE_FB_TITLE_MIN.':'.WSKO_ONPAGE_FB_TITLE_MAX, 'og_desc_length:'.WSKO_ONPAGE_FB_DESC_MIN.':'.WSKO_ONPAGE_FB_DESC_MAX, 'og_img_provided:1:1'),
            array('og_title_length:0:'.(WSKO_ONPAGE_FB_TITLE_MIN-1), 'og_desc_length:0:'.(WSKO_ONPAGE_FB_DESC_MIN-1), ''),
            array('og_title_length:'.(WSKO_ONPAGE_FB_TITLE_MAX-1).':500', 'og_desc_length:'.(WSKO_ONPAGE_FB_DESC_MAX-1).':500', '')
        );
        $matrix_tw = array(
            array('tw_title_length:0:0', 'tw_desc_length:0:0', 'tw_img_provided:0:0'),
            array('tw_title_length:'.WSKO_ONPAGE_TW_TITLE_MIN.':'.WSKO_ONPAGE_TW_TITLE_MAX, 'tw_desc_length:'.WSKO_ONPAGE_TW_DESC_MIN.':'.WSKO_ONPAGE_TW_DESC_MAX, 'tw_img_provided:1:1'),
            array('tw_title_length:0:'.(WSKO_ONPAGE_TW_TITLE_MIN-1), 'tw_desc_length:0:'.(WSKO_ONPAGE_TW_DESC_MIN-1), ''),
            array('tw_title_length:'.(WSKO_ONPAGE_TW_TITLE_MAX-1).':500', 'tw_desc_length:'.(WSKO_ONPAGE_TW_DESC_MAX-1).':500', '')
        );
        ?>
        <ul class="nav nav-tabs bsu-tabs border-dark">
            <li class="active"><a href="#wsko_onpage_snippet_analysis_facebook" data-toggle="tab"><?=__('Facebook', 'wsko')?></a></li>
            <li><a href="#wsko_onpage_snippet_analysis_twitter" data-toggle="tab"><?=__('Twitter', 'wsko')?></a></li>
        </ul>
        <div class="tab-content">
            <div id="wsko_onpage_snippet_analysis_facebook" class="tab-pane fade in active">
                <div class="row">
                    <?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __('Facebook Snippet Analysis', 'wsko'), 'col' => 'col-sm-12 col-xs-12', 
                        'custom' => $chart_dist_fb ? WSKO_Class_Template::render_chart('column', array(__('Meta', 'wsko'), __('Not Set', 'wsko'), __('Okay', 'wsko'), __('Too Short', 'wsko'), __('Too Long', 'wsko')), $chart_dist_fb, array('isStacked' => true, 'chart_id' => 'fb_snippet', 'colors' => array('#d9534f', '#5cb85c', '#f0ad4e', '#f0ad4e'), 'table_filter' => array('table' => '#wsko_facebook_analysis_table', 'value_matrix' => $matrix_fb), 'axisTitleY' => __('Page Count', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'), true) : WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true))); ?>
                            
                    <?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __('Pages', 'wsko'), 'col' => 'col-sm-12 col-xs-12', 
                        'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), array('name' => __('Title length', 'wsko'), 'width' => '15%'), array('name' => __('Desc length', 'wsko'), 'width' => '15%'), array('name' => __('Image set', 'wsko'), 'width' => '15%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_facebook_analysis_table', 'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'social_fb'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis ? $analysis['max']['onpage_score'] : 100), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'og_title_length' => array('title' => __('Title Length', 'wsko'), 'type' => 'number_range', 'max' => 500), 'og_desc_length' => array('title' => __('Description Length', 'wsko'), 'type' => 'number_range', 'max' => 500), 'og_img_provided' => array('title' => __('Image', 'wsko'), 'type' => 'number_range', 'max' => 1))), true))); ?>
                </div>
            </div>
            <div id="wsko_onpage_snippet_analysis_twitter" class="tab-pane fade">
                <div class="row">
                    <?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __('Twitter Snippet Analysis', 'wsko'), 'col' => 'col-sm-12 col-xs-12', 
                        'custom' => $chart_dist_tw ? WSKO_Class_Template::render_chart('column', array(__('Meta', 'wsko'), __('Not Set', 'wsko'), __('Okay', 'wsko'), __('Too Short', 'wsko'), __('Too Long', 'wsko')), $chart_dist_tw, array('isStacked' => true, 'chart_id' => 'tw_snippet', 'colors' => array('#d9534f', '#5cb85c', '#f0ad4e', '#f0ad4e'), 'table_filter' => array('table' => '#wsko_twitter_analysis_table', 'value_matrix' => $matrix_tw), 'axisTitleY' => __('Page Count', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'), true) : WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true))); ?>
                            
                    <?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __('Pages', 'wsko'), 'col' => 'col-sm-12 col-xs-12', 
                        'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), array('name' => __('Title length', 'wsko'), 'width' => '15%'), array('name' => __('Desc length', 'wsko'), 'width' => '15%'), array('name' => __('Image set', 'wsko'), 'width' => '15%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_twitter_analysis_table', 'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'social_tw'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'tw_title_length' => array('title' => __('Title Length', 'wsko'), 'type' => 'number_range', 'max' => 500), 'tw_desc_length' => array('title' => __('Description Length', 'wsko'), 'type' => 'number_range', 'max' => 500), 'tw_img_provided' => array('title' => __('Image', 'wsko'), 'type' => 'number_range', 'max' => 1))), true))); ?>
                </div>
            </div>
        </div>
    </div>
</div>