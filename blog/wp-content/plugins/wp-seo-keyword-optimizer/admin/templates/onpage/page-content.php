<?php
if (!defined('ABSPATH')) exit;

$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();

$analysis = isset($global_analysis['current_report']) ? $global_analysis['current_report'] : false;
$chart_dist = array();
if ($analysis)
{
	foreach($analysis['content_length_dist'] as $k => $dupl)
	{
		$chart_dist[]= array($k, array('v' => $dupl, 'f' => WSKO_Class_Helper::format_Number($dupl)));
	}
}
$matrix_content = array(array('word_count:0:100','word_count:100:250','word_count:250:500','word_count:500:1000','word_count:1000:2000','word_count:2000:3000','word_count:3000:'.$analysis['max']['word_count']));
?>
<div class="row">
	<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Content Length Distribution', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
		'custom' => $chart_dist ? WSKO_Class_Template::render_chart('column', array(__('Content Length', 'wsko'), __('Page Count', 'wsko')), $chart_dist, array('table_filter' => array('table' => '#wsko_onpage_analysis_content_table', 'value_matrix' => $matrix_content), 'colors' => array('#4ea04e'), 'row_colors' => array('#d9534f', '#E6854E', '#EAC050', '#C5D955', '#88C859', '#4ea04e', '#88C859'), 'chart_id' => 'content_length', 'axisTitle' => __('Words', 'wsko'), 'axisTitleY' => __('Page Count', 'wsko'), 'chart_left' => '15', 'chart_width' => '70', 'hide_legend' => true), true) : WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true))); ?>
			
	<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
		'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), /*'Content Length',*/ __('Word Count', 'wsko'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_analysis_content_table', 'order' => array('col' => 2, 'dir' => 'desc'), 'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'content'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), /*'content_length' => array('title' => 'Content Length', 'type' => 'number_range', 'max' => $analysis['max']['content_length']),*/ 'word_count' => array('title' => __('Word Count', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['word_count']))), true))); ?>
</div>