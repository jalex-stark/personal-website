<?php
if (!defined('ABSPATH')) exit;

$monitored_keywords = WSKO_Class_Search::get_monitored_keywords();
if (!$monitored_keywords || empty($monitored_keywords))
	WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_search', 'monitoring_empty')));
?>

<div class="row">
	<?php
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Ranking Keywords', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'key', 'lazyVar' => 'total_keywords'));
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Clicks', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'mouse-pointer', 'lazyVar' => 'total_clicks'));
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Impressions', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'eye', 'lazyVar' => 'total_impressions'));
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Avg. Position', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'ellipsis-v', 'lazyVar' => 'avg_position'));
	?>
</div>

<div class="row">	
	<?php WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Histories', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'chart_histories', 'panelBody' => true)); ?>
</div>
<?php /*
<div class="row">
	<?php WSKO_Class_Template::render_panel(array('type' => 'custom', 'title' => 'Monitored Keywords', 'col' => 'col-sm-12 col-xs-12',
	'custom' => '<div class="wsko-keyword-monitoring-add"><input class="form-control wsko-add-monitoring-keyword-input" type="text"><a class="form-control wsko-add-monitoring-keyword button" href="#" data-nonce="'.wp_create_nonce('wsko_add_monitoring_keyword').'">Add</a></div>')); ?>
</div>
*/ ?>
<div class="row">
	<?php WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Monitored Keywords', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_monitored_keywords',
	'custom' => '<div class="wsko-keyword-monitoring-add col-sm-12 col-xs-12" style="margin-top:10px;"><input class="form-control wsko-add-monitoring-keyword-input" placeholder="Add Keyword (use a comma to insert multiple keywords)" type="text"><a class="button wsko-add-monitoring-keyword" style="margin: 10px 0px;" href="#" data-nonce="'.wp_create_nonce('wsko_add_monitoring_keyword').'"><i class="wsko-loader"></i> Add Keyword</a></div>')); ?>
</div>