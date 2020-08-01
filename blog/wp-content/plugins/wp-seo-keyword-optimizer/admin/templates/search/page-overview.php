<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$is_dashboard = ( isset( $template_args['widget'] ) && $template_args['widget'] ? true : false );

if ( !$is_dashboard ) {
    ?>
	<div class="row">
		<?php 
    WSKO_Class_Template::render_panel( array(
        'type'    => 'hero',
        'title'   => __( 'All Keywords', 'wsko' ),
        'col'     => 'col-md-3 col-sm-6 col-xs-12',
        'fa'      => 'key',
        'lazyVar' => 'total_keywords',
    ) );
    WSKO_Class_Template::render_panel( array(
        'type'    => 'hero',
        'title'   => __( 'Keywords in Top 10', 'wsko' ),
        'col'     => 'col-md-3 col-sm-6 col-xs-12',
        'fa'      => 'search',
        'lazyVar' => 'total_keyword_dist',
    ) );
    WSKO_Class_Template::render_panel( array(
        'type'    => 'hero',
        'title'   => __( 'Clicks', 'wsko' ),
        'col'     => 'col-md-3 col-sm-6 col-xs-12',
        'fa'      => 'mouse-pointer',
        'lazyVar' => 'total_clicks',
    ) );
    WSKO_Class_Template::render_panel( array(
        'type'    => 'hero',
        'title'   => __( 'Impressions', 'wsko' ),
        'col'     => 'col-md-3 col-sm-6 col-xs-12',
        'fa'      => 'eye',
        'lazyVar' => 'total_impressions',
    ) );
    ?>
	</div>	
<?php 
}

?>

<div class="row">	
	<?php 
WSKO_Class_Template::render_panel( array(
    'type'    => 'lazy',
    'title'   => __( 'Ranking Keywords History', 'wsko' ),
    'col'     => 'col-sm-6 col-xs-12',
    'lazyVar' => 'chart_history_keywords',
) );
WSKO_Class_Template::render_panel( array(
    'type'    => 'lazy',
    'title'   => __( 'Ranking Pages History', 'wsko' ),
    'col'     => 'col-sm-6 col-xs-12',
    'lazyVar' => 'chart_history_pages',
) );
WSKO_Class_Template::render_panel( array(
    'type'    => 'lazy',
    'title'   => __( 'Click History', 'wsko' ),
    'col'     => 'col-sm-6 col-xs-12',
    'lazyVar' => 'chart_history_clicks',
) );
WSKO_Class_Template::render_panel( array(
    'type'    => 'lazy',
    'title'   => __( 'Impressions History', 'wsko' ),
    'col'     => 'col-sm-6 col-xs-12',
    'lazyVar' => 'chart_history_impressions',
) );
WSKO_Class_Template::render_panel( array(
    'type'    => 'lazy',
    'title'   => __( 'Position History', 'wsko' ),
    'col'     => 'col-sm-6 col-xs-12',
    'lazyVar' => 'chart_history_position',
) );
WSKO_Class_Template::render_panel( array(
    'type'    => 'lazy',
    'title'   => __( 'CTR History', 'wsko' ),
    'col'     => 'col-sm-6 col-xs-12',
    'lazyVar' => 'chart_history_ctr',
) );
?>
</div>

<?php 

if ( !$is_dashboard ) {
    ?>
	
	<div class="row">	
		<div class="col-sm-12">		
			<div class="panel bsu-panel panel-default">	
				<p class="panel-heading m0"><?php 
    echo  __( 'Ranking Distribution', 'wsko' ) ;
    ?></p>
				<div class="panel-inner">
					<ul class="nav nav-tabs bsu-tabs">
						<li class="waves-effect active"><a data-toggle="tab" href="#ranking_dist_history"><?php 
    echo  __( 'History', 'wsko' ) ;
    ?></a></li>
						<li class="waves-effect"><a data-toggle="tab" href="#ranking_dist_distribution"><?php 
    echo  __( 'Ranking Distribution', 'wsko' ) ;
    ?></a></li>
					</ul>

					<div class="tab-content">
						<div id="ranking_dist_history" class="tab-pane fade in active">
							<?php 
    WSKO_Class_Template::render_panel( array(
        'type'    => 'lazy',
        'title'   => __( 'Ranking Distribution History', 'wsko' ),
        'class'   => 'panel-transparent',
        'lazyVar' => 'chart_history_serp',
    ) );
    ?>
						</div>
						<div id="ranking_dist_distribution" class="tab-pane fade">
							<?php 
    WSKO_Class_Template::render_panel( array(
        'type'    => 'lazy',
        'title'   => __( 'Ranking Distribution', 'wsko' ),
        'class'   => 'panel-transparent',
        'lazyVar' => 'chart_history_rankings',
    ) );
    ?>
						</div>
					</div>
				</div>
			</div>
		</div>				
	</div>
<?php 
}

?>

<div class="row">		
	<?php 
WSKO_Class_Template::render_panel( array(
    'type'      => 'table-simple',
    'title'     => __( 'Top 5 Keywords', 'wsko' ),
    'col'       => 'col-sm-12 col-xs-12',
    'lazyVar'   => 'table_top_keywords',
    'tableLink' => array(
    'link'  => WSKO_Controller_Search::get_link( 'keywords' ),
    'title' => __( 'All Keywords', 'wsko' ),
),
) );
WSKO_Class_Template::render_panel( array(
    'type'      => 'table-simple',
    'title'     => __( 'Top 5 Pages', 'wsko' ),
    'col'       => 'col-sm-12 col-xs-12',
    'lazyVar'   => 'table_top_pages',
    'tableLink' => array(
    'link'  => WSKO_Controller_Search::get_link( 'pages' ),
    'title' => __( 'All Pages', 'wsko' ),
),
) );
?>
</div>