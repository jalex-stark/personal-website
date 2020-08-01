<?php
if (!defined('ABSPATH')) exit;

$uniqid = WSKO_Class_Helper::get_unique_id();

$clicks = isset($template_args['clicks']) ? $template_args['clicks'] : array();
$impressions = isset($template_args['impressions']) ? $template_args['impressions'] : array();
$position = isset($template_args['position']) ? $template_args['position'] : array();
$ctr = isset($template_args['ctr']) ? $template_args['ctr'] : array();

$tmp_fail = WSKO_Class_Template::render_template('misc/template-no-data.php', array(), true);
?>
<ul class="nav nav-tabs bsu-tabs">
	<li class="active"><a href="#wsko_kw_monitoring_history_clicks_<?=$uniqid?>" data-toggle="tab"><?=__('Clicks', 'wsko')?></a></li>
	<li><a href="#wsko_kw_monitoring_history_impressions_<?=$uniqid?>" data-toggle="tab"><?=__('Impressions', 'wsko')?></a></li>
	<li><a href="#wsko_kw_monitoring_history_position_<?=$uniqid?>" data-toggle="tab"><?=__('Position', 'wsko')?></a></li>
	<li><a href="#wsko_kw_monitoring_history_ctr_<?=$uniqid?>" data-toggle="tab"><?=__('CTR', 'wsko')?></a></li>
</ul>
<div class="tab-content">
	<div id="wsko_kw_monitoring_history_clicks_<?=$uniqid?>" class="tab-pane fade in active">
		<?php if ($clicks)  
			WSKO_Class_Template::render_chart('area', array(__('Date', 'wsko'), __('Sum. Clicks', 'wsko'), __('Sum. Clicks Ref', 'wsko')), $clicks, array('axisTitleY' => __('Clicks', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'));
		else
			echo $tmp_fail; ?>
	</div>
	<div id="wsko_kw_monitoring_history_impressions_<?=$uniqid?>" class="tab-pane fade">
		<?php if ($impressions)  
			WSKO_Class_Template::render_chart('area', array(__('Date', 'wsko'), __('Sum. Impressions', 'wsko'), __('Sum. Impressions Ref', 'wsko')), $impressions, array('axisTitleY' => __('Impressions', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'));
		else
			echo $tmp_fail; ?>
	</div>
	<div id="wsko_kw_monitoring_history_position_<?=$uniqid?>" class="tab-pane fade">
		<?php if ($position)  
			WSKO_Class_Template::render_chart('area', array(__('Date', 'wsko'), __('Avg. Position', 'wsko'), __('Avg. Position Ref', 'wsko')), $position, array('axisTitleY' => __('Avg. Position', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'));
		else
			echo $tmp_fail; ?>
	</div>	
	<div id="wsko_kw_monitoring_history_ctr_<?=$uniqid?>" class="tab-pane fade">
		<?php if ($ctr)  
			WSKO_Class_Template::render_chart('area', array(__('Date', 'wsko'), __('Avg. Click Through Rate', 'wsko'), __('Avg. Click Through Rate Ref', 'wsko')), $ctr, array('format' => array('1' => '{0} %'), 'axisTitleY' => __('CTR', 'wsko'), 'chart_left' => '15', 'chart_width' => '70'));
		else
			echo $tmp_fail; ?>
	</div>
</div>