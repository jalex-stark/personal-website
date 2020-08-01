<?php
if (!defined('ABSPATH')) exit;

$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();

$analysis = isset($global_analysis['current_report']) ? $global_analysis['current_report'] : false;
$chart_dist = array();
if ($analysis)
{
	foreach($analysis['heading_dist'] as $k => $dupl)
	{
		if ($k == 'h1') {
			$chart_dist[]= array('stacked' => true, 'title' => $k,
			'value' => array(
				array('class' => 'danger wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][0], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][0], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][0])), 'title' => __( 'None', 'wsko' ), 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':0:0')),
				array('class' => 'success wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][1], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][1], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][1])), 'title' => '1', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':1:1')),
				array('class' => 'danger wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][2], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][2], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][2])), 'title' => '2', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':2:2')),
				array('class' => 'danger wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][3], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][3], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][3])), 'title' => '3', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':3:3')),
				array('class' => 'danger wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][4], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][4], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][4])), 'title' => '4', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':4:4')),
				array('class' => 'danger wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][5], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][5], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][5])), 'title' => '5+', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':5:'.$analysis['max']['heading_count'])),
			), 'max' => $analysis['total_pages']);		
		} else {
			$chart_dist[]= array('stacked' => true, 'title' => $k,
			'value' => array(
				array('class' => ($k == 'h2' ? 'danger' : 'warning').' wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][0], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][0], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][0])), 'title' => __( 'None', 'wsko' ), 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':0:0')),
				array('class' => 'success wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][1], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][1], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][1])), 'title' => '1', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':1:1')),
				array('class' => 'success wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][2], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][2], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][2])), 'title' => '2', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':2:2')),
				array('class' => 'success wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][3], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][3], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][3])), 'title' => '3', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':3:3')),
				array('class' => 'success wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][4], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][4], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][4])), 'title' => '4', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':4:4')),
				array('class' => 'success wsko-external-table-filter', 'value' => $analysis['heading_dist'][$k][5], 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['heading_dist'][$k][5], 'wsko'), number_format_i18n($analysis['heading_dist'][$k][5])), 'title' => '5+', 'data' => array('table' => '#wsko_onpage_analysis_headings_table', 'val' => 'count_'.$k.':5:'.$analysis['max']['heading_count'])),
			), 'max' => $analysis['total_pages']);
		}	
	}
}
?>
<div class="row">
	<?php 
	if ($chart_dist)
	{
		WSKO_Class_Template::render_panel(array('type' => 'progress', 'title' => __( 'Heading Count Distribution', 'wsko' ), 'col' => 'col-sm-12 col-xs-12',
		'items' => $chart_dist)); 
	}
	else
	{
		WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Heading Count Distribution', 'wsko' ), 'col' => '', 'class' => 'col-sm-12 col-xs-12',
			'custom' => WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true)));
	}
	
	WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
		'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), 'Page', array('name' => 'H1', 'width' => '5%'), array('name' => 'H2', 'width' => '5%'), array('name' => 'H3', 'width' => '5%'), array('name' => 'H4', 'width' => '5%'), array('name' => 'H5', 'width' => '5%'), array('name' => 'H6', 'width' => '5%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_analysis_headings_table', 'order' => array('col' => 2, 'dir' => 'desc'), 'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'headings'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'count_h1' => array('title' => __('Count H1', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['heading_count']), 'count_h2' => array('title' => __('Count H2', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['heading_count']), 'count_h3' => array('title' => __('Count H3', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['heading_count']), 'count_h4' => array('title' => __('Count H4', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['heading_count']), 'count_h5' => array('title' => __('Count H5', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['heading_count']), 'count_h6' => array('title' => __('Count H6', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['heading_count']))), true))); ?>
</div>