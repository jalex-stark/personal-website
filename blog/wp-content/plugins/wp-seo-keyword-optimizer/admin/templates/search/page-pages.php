<?php
if (!defined('ABSPATH')) exit;

$controller = $template_args['controller'];
$is_default_timerange = $controller->is_default_timespan;
?>
<div class="row">
	<?php
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Pages', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'key', 'lazyVar' => 'total_pages'));
	WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Pages in Top 10', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'search', 'lazyVar' => 'total_page_dist'));
	if ($is_default_timerange)
	{
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'New Pages', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'plus', 'lazyVar' => 'total_new_pages'));
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Lost Pages', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'minus', 'lazyVar' => 'total_lost_pages'));
	}
	else
	{
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'New Pages', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'plus', 'custom' => '-'));
		WSKO_Class_Template::render_panel(array('type' => 'hero', 'title' => __( 'Lost Pages', 'wsko' ), 'col' => 'col-md-3 col-sm-6 col-xs-12', 'fa' => 'minus', 'custom' => '-'));
	}
	?>
</div>	

<div class="row">
	<div class="col-sm-12">
		<ul class="nav nav-tabs bsu-tabs border-dark">
		  <li class="waves-effect active"><a data-toggle="tab" href="#all_pages"><?=__( 'All Pages', 'wsko' )?></a></li>
		  <li class="waves-effect"><a data-toggle="tab" href="#new_pages"><?=__( 'New Pages', 'wsko' )?> <span id="wsko_new_pages_t"></span></a></li>
		  <li class="waves-effect"><a data-toggle="tab" href="#lost_pages"><?=__( 'Lost Pages', 'wsko' )?> <span id="wsko_lost_pages_t"></span></a></li>
		</ul>

		<div class="tab-content row">
		  <div id="all_pages" class="tab-pane fade in active">
		  <?php WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'All Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_pages')); ?>
		  </div>
		  <div id="new_pages" class="tab-pane fade">
		  <?php if ($is_default_timerange)
				{ 
					WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'New Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_new_pages'));
				}
				else
				{
					?><div class="col-sm-12 col-xs-12"><?php
						WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif', 'timerange_unsupported').WSKO_Class_Template::render_ajax_button('<i class="fa fa-refresh"></i> '.__('Reset', 'wsko'), 'reset_timespan', array(), array('reload_real' => true), true)));
					?></div><?php
				} ?>
		  </div>
		  <div id="lost_pages" class="tab-pane fade">
		  <?php if ($is_default_timerange)
				{ 
					WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Lost Pages', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_lost_pages'));
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

