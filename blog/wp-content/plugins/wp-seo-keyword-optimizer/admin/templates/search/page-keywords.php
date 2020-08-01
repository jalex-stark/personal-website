<?php
if (!defined('ABSPATH')) exit;

$controller = $template_args['controller'];
$is_default_timerange = $controller->is_default_timespan;
?>
<div class="row">
	<?php 
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Keywords', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'key', 'lazyVar' => 'total_keywords'));
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Keywords in Top 10', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'search', 'lazyVar' => 'total_keyword_dist'));
	if ($is_default_timerange)
	{
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'New Keywords', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'plus', 'lazyVar' => 'total_new_keywords'));
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Lost Keywords', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'minus', 'lazyVar' => 'total_lost_keywords'));
	}
	else
	{
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'New Keywords', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'plus', 'custom' => '-'));
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Lost Keywords', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'minus', 'custom' => '-'));
	}
	?>
</div>	

<div class="row">
	<div class="col-sm-12">
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="waves-effect active"><a data-toggle="tab" href="#all_keywords"><?=__( 'All Keywords', 'wsko' ) ?></a></li>
			<li class="waves-effect"><a data-toggle="tab" href="#new_keywords"><?=__( 'New Keywords', 'wsko' ) ?> <span id="wsko_new_keywords_t"></span></a></li>
			<li class="waves-effect"><a data-toggle="tab" href="#lost_keywords"><?=__( 'Lost Keywords', 'wsko' ) ?> <span id="wsko_lost_keywords_t"></span></a></li>
		</ul>

		<div class="tab-content row">
			<div id="all_keywords" class="tab-pane fade in active">
				<?php WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'All Keywords', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_keywords')); ?>
			</div>
			<div id="new_keywords" class="tab-pane fade">
				<?php if ($is_default_timerange)
				{ 
					WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'New Keywords', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_new_keywords'));
				}
				else
				{
					?><div class="col-sm-12 col-xs-12"><?php
						WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif', 'timerange_unsupported').WSKO_Class_Template::render_ajax_button('<i class="fa fa-refresh"></i> '.__('Reset', 'wsko'), 'reset_timespan', array(), array('reload_real' => true), true)));
					?></div><?php
				} ?>
			</div>
			<div id="lost_keywords" class="tab-pane fade">
				<?php if ($is_default_timerange)
				{ 
					WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Lost Keywords', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_lost_keywords'));
				}
				else
				{
					?><div class="col-sm-12 col-xs-12"><?php
						WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif', 'timerange_unsupported').WSKO_Class_Template::render_ajax_button('<i class="fa fa-refresh"></i> '.__('Reset', 'wsko'), 'reset_timespan', array(), array('reload_real' => true), true)));
					?></div><?php
				} ?>
			</div>
		</div>
	</div>
</div>