<?php
if (!defined('ABSPATH')) exit;

$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();

$analysis = isset($global_analysis['current_report']) ? $global_analysis['current_report'] : false;

$chart_dupl = array();
if ($analysis)
{
	foreach($analysis['title_duplicate_dist'] as $k => $dupl)
	{
		if ($k != 0)
			$chart_dupl[]= array(sprintf(_n('%s Duplicate', '%s Duplicates', $k, 'wsko'), number_format_i18n($k)), array('v' => $dupl, 'f' => WSKO_Class_Helper::format_Number($dupl)));
	}
}
$matrix_title = array(array(/*'title_duplicates:0:0',*/'title_duplicates:1:1','title_duplicates:2:2','title_duplicates:3:3','title_duplicates:4:4','title_duplicates:5:5','title_duplicates:6:6','title_duplicates:7:7','title_duplicates:8:8','title_duplicates:9:'.$analysis['max']['desc_dupl']));
if (isset($analysis['ext_source']['metas']))
{
	WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_onpage', 'preview_titles', array('source' => $analysis['ext_source']['metas']))));
}
?>
<div class="row">
	<div class="col-sm-12">
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="waves-effect active"><a data-toggle="tab" href="#wsko_onpage_analysis_titles_length"><?=__( 'Length', 'wsko' ) ?></a></li>
			<li class="waves-effect"><a data-toggle="tab" href="#wsko_onpage_analysis_titles_duplicates"><?=__( 'Duplicates', 'wsko' ) ?></a></li>
		</ul>

		<div class="tab-content">
			<div id="wsko_onpage_analysis_titles_length" class="tab-pane fade in active">
				<div class="row">
					<?php WSKO_Class_Template::render_panel(array('type' => 'progress', 'title' => __( 'Title Length', 'wsko' ).' '.WSKO_Class_Template::render_infoTooltip(__('Shows the meta title length distribution over all crawled pages', 'wsko'), 'info', true), 'col' => 'col-sm-12 col-xs-12',
						'items' => array(
							array('title' => __( 'OK', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['title_length_dist']['ok'], 'wsko'), number_format_i18n($analysis['title_length_dist']['ok'])), 'value' => $analysis['title_length_dist']['ok'], 'max' => $analysis['total_pages'], 'progress_class' => 'success', 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_titles_table', 'val' => 'title_length:'.WSKO_ONPAGE_TITLE_MIN.':'.WSKO_ONPAGE_TITLE_MAX)),
							array('title' => __( 'Too Short', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['title_length_dist']['too_short'], 'wsko'), number_format_i18n($analysis['title_length_dist']['too_short'])), 'value' => $analysis['title_length_dist']['too_short'], 'max' => $analysis['total_pages'], 'progress_class' => 'warning', 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_titles_table', 'val' => 'title_length:1:'.(WSKO_ONPAGE_TITLE_MIN-1))),
							array('title' => __( 'Too Long', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['title_length_dist']['too_long'], 'wsko'), number_format_i18n($analysis['title_length_dist']['too_long'])), 'value' => $analysis['title_length_dist']['too_long'], 'max' => $analysis['total_pages'], 'progress_class' => 'warning', 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_titles_table', 'val' => 'title_length:'.(WSKO_ONPAGE_TITLE_MAX+1).':'.$analysis['max']['title_length'])),
							//array('title' => __( 'Not Set', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['title_length_dist']['not_set'], 'wsko'), number_format_i18n($analysis['title_length_dist']['not_set'])), 'value' => $analysis['title_length_dist']['not_set'], 'max' => $analysis['total_pages'], 'progress_class' => 'danger', 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_titles_table', 'val' => 'title_length:0:0')),
							array('title' => __( 'No custom title set', 'wsko' ), 'tooltip' => sprintf(_n('%s Page', '%s Pages', $analysis['title_length_dist']['no_custom_set'], 'wsko'), number_format_i18n($analysis['title_length_dist']['no_custom_set'])), 'value' => $analysis['title_length_dist']['no_custom_set'], 'max' => $analysis['total_pages'], 'progress_class' => 'danger', 'class' => 'wsko-external-table-filter', 'data' => array('table' => '#wsko_onpage_analysis_titles_table', 'val' => 'title_custom:0'))
						))); ?>
						
					<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __('Pages', 'wsko'), 'col' => 'col-sm-12 col-xs-12', 
						'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), array('name' => __('Title Length', 'wsko'), 'width' => '15%'), array('name' => __('Duplicates', 'wsko'), 'width' => '15%'), array('name' => __('Title', 'wsko'), 'width' => '30%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_analysis_titles_table', 'order'=> array('col' => 2, 'dir' => 'desc'), 'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'titles'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'title_length' => array('title' => __('Title Length', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['title_length']), 'title_custom' => array('title' => __('Title Type', 'wsko'), 'type' => 'select', 'values' => array('0' => __('Default Title', 'wsko'), '1' => __('Custom Title', 'wsko'))), 'title_duplicates' => array('title' => __('Title Duplicates', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['title_dupl']))), true))); ?>
				</div>	
			</div>
			<div id="wsko_onpage_analysis_titles_duplicates" class="tab-pane fade">
				<div class="row">
					<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Title Duplicates', 'wsko' ).' '.WSKO_Class_Template::render_infoTooltip(__('Shows the meta title duplicate distribution over all crawled pages', 'wsko'), 'info', true), 'col' => 'col-sm-12 col-xs-12',  
						'custom' => $chart_dupl ? WSKO_Class_Template::render_chart('column', array(__('Duplicates', 'wsko'), __('Page Count', 'wsko')), $chart_dupl, array('table_filter' => array('table' => '#wsko_onpage_analysis_titles_dupl_table', 'value_matrix' => $matrix_title), 'colors' => array('#d9534f'), 'chart_id' => 'title_dupl', 'axisTitleY' => __('Page Count', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'), true) : WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true))); ?>
						
					<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => __( 'Pages with duplicate titles', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 
						'custom' => WSKO_Class_Template::render_table(array(__('CS', 'wsko') . WSKO_Class_Template::render_infoTooltip(__('Content Score', 'wsko'), 'info', true), __('Page', 'wsko'), array('name' => __('Title Length', 'wsko'), 'width' => '15%'), array('name' => __('Duplicates', 'wsko'), 'width' => '15%'), array('name' => __('Title', 'wsko'), 'width' => '30%'), array('name' => '', 'width' => '5%')), array(), array('id' => 'wsko_onpage_analysis_titles_dupl_table', 'order'=> array('col' => 3, 'dir' => 'desc'), 'ajax' => array('action' => 'wsko_table_onpage', 'arg' => 'titles_dupl'), 'filter' => array('onpage_score' => array('title' => __('Onpage Score', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['onpage_score']), 'url' => array('title' => __('URL', 'wsko'), 'type' => 'text'), 'post_type' => array('title' => __('Post Type', 'wsko'), 'type' => 'select', 'values' => isset($analysis['post_types']) ? WSKO_Class_Helper::get_post_type_labels($analysis['post_types']) : array()), 'title_length' => array('title' => __('Title Length', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['title_length']), 'title_custom' => array('title' => __('Title Type', 'wsko'), 'type' => 'select', 'values' => array('0' => __('Default Title', 'wsko'), '1' => __('Custom Title', 'wsko'))), 'title_duplicates' => array('title' => __('Title Duplicates', 'wsko'), 'type' => 'number_range', 'max' => $analysis['max']['title_dupl']))), true))); ?>
				</div>		
			</div>
		</div>
	</div>
	
</div>