<?php
if (!defined('ABSPATH')) exit;

$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();

$analysis = isset($global_analysis['current_report']) ? $global_analysis['current_report'] : false;

$chart_dupl = array();
if ($analysis)
{
	foreach($analysis['desc_duplicate_dist'] as $k => $dupl)
	{
		if ($k != 0)
			$chart_dupl[]= array(sprintf(_n('%s Duplicate', '%s Duplicates', $k, 'wsko'), number_format_i18n($k)), array('v' => $dupl, 'f' => WSKO_Class_Helper::format_Number($dupl)));
	}
}
$matrix_desc = array(array(/*'desc_duplicates:0:0',*/'desc_duplicates:1:1','desc_duplicates:2:2','desc_duplicates:3:3','desc_duplicates:4:4','desc_duplicates:5:5','desc_duplicates:6:6','desc_duplicates:7:7','desc_duplicates:8:8','desc_duplicates:9:'.$analysis['max']['desc_dupl']));
if (isset($analysis['ext_source']['metas']))
{
	WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_onpage', 'preview_desc', array('source' => $analysis['ext_source']['metas']))));
}
?>
<div class="row">
	<div class="col-sm-12">
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="waves-effect active"><a data-toggle="tab" href="#wsko_onpage_analysis_descriptions_length"><?=__('Length', 'wsko')?></a></li>
			<li class="waves-effect"><a data-toggle="tab" href="#wsko_onpage_analysis_descriptions_duplicates"><?=__('Duplicates', 'wsko')?></a></li>
		</ul>

		<div class="tab-content">
			<div id="wsko_onpage_analysis_descriptions_length" class="tab-pane fade in active">
				<div class="row">
					<?php WSKO_Class_Template::render_panel(array('type' => 'progress', 'title' => __('Length Distribution', 'wsko').' '.WSKO_Class_Template::render_infoTooltip(__('Shows the meta description length distribution over all crawled pages', 'wsko'), 'info', true), 'col' => 'col-sm-12 col-xs-12', 
						'items' => array(
							array('title' => __( 'OK', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['desc_length_dist']['ok'], 'wsko'), number_format_i18n($analysis['desc_length_dist']['ok'])), 'value' => $analysis['desc_length_dist']['ok'], 'max' => $analysis['total_pages'], 'progress_class' => 'success', 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_descriptions_table', 'val' => 'desc_length:'.WSKO_ONPAGE_DESC_MIN.':'.WSKO_ONPAGE_DESC_MAX)),
							array('title' => __( 'Too Short', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['desc_length_dist']['too_short'], 'wsko'), number_format_i18n($analysis['desc_length_dist']['too_short'])), 'value' => $analysis['desc_length_dist']['too_short'],  'progress_class' => 'warning', 'max' => $analysis['total_pages'], 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_descriptions_table', 'val' => 'desc_length:1:'.(WSKO_ONPAGE_DESC_MIN-1))),
							array('title' => __( 'Too Long', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['desc_length_dist']['too_long'], 'wsko'), number_format_i18n($analysis['desc_length_dist']['too_long'])), 'value' => $analysis['desc_length_dist']['too_long'], 'progress_class' => 'warning', 'max' => $analysis['total_pages'], 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_descriptions_table', 'val' => 'desc_length:'.(WSKO_ONPAGE_DESC_MAX+1).':'.$analysis['max']['desc_length'])),
							array('title' => __( 'Not Set', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['desc_length_dist']['not_set'], 'wsko'), number_format_i18n($analysis['desc_length_dist']['not_set'])), 'value' => $analysis['desc_length_dist']['not_set'], 'progress_class' => 'danger', 'max' => $analysis['total_pages'], 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_descriptions_table', 'val' => 'desc_length:0:0'))
						))); ?>
						
					<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
						'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), array('name' => __('Desc Length', 'wsko'), 'width' => '15%'), array('name' => __('Duplicates', 'wsko'), 'width' => '15%'), array('name' => __('Description', 'wsko'), 'width' => '40%'),  array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_analysis_descriptions_table', 'order'=> array('col' => 2, 'dir' => 'desc'),  'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'desc'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'desc_length' => array('title' => __('Description Length', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['desc_length']), 'desc_duplicates' => array('title' => __('Description Duplicates', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['desc_dupl']))), true))); ?>
				</div>		
			</div>
			<div id="wsko_onpage_analysis_descriptions_duplicates" class="tab-pane fade">
				<div class="row">
					<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Description Duplicates', 'wsko' ).' '.WSKO_Class_Template::render_infoTooltip(__('Shows the meta description duplicate distribution over all crawled pages', 'wsko'), 'info', true), 'col' => 'col-sm-12 col-xs-12', 
						'custom' => $chart_dupl ? WSKO_Class_Template::render_chart('column', array(__('Duplicates', 'wsko'), __('Page Count', 'wsko')), $chart_dupl, array('table_filter' => array('table' => '#wsko_onpage_analysis_descriptions_dupl_table', 'value_matrix' => $matrix_desc), 'colors' => array('#d9534f'), 'chart_id' => 'desc_dupl', 'axisTitleY' => __('Page Count', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'), true) : WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true))); ?>
					
					<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages with duplicate descriptions', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
						'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), array('name' => __('Desc Length', 'wsko'), 'width' => '15%'), array('name' => __('Duplicates', 'wsko'), 'width' => '15%'), array('name' => __('Description', 'wsko'), 'width' => '40%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_analysis_descriptions_dupl_table', 'order'=> array('col' => 3, 'dir' => 'desc'),  'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'desc_dupl'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'desc_length' => array('title' => __('Description Length', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['desc_length']), 'desc_duplicates' => array('title' => __('Description Duplicates', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['desc_dupl']))), true))); ?>
				</div>
			</div>
		</div>
	</div>
	
</div>