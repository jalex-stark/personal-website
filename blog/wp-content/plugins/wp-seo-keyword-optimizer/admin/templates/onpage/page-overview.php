<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$is_dashboard = ( isset( $template_args['widget'] ) && $template_args['widget'] ? true : false );
$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
$analysis = ( isset( $global_analysis['current_report'] ) ? $global_analysis['current_report'] : false );
$onpage_score_history = array();
if ( isset( $global_analysis['onpage_history'] ) ) {
    foreach ( $global_analysis['onpage_history'] as $k => $re ) {
        $onpage_score_history[] = array( date( 'M. d, Y', $k ), array(
            'v' => $re,
            'f' => WSKO_Class_Helper::format_number( $re, 2 ),
        ) );
    }
}
$issues_data = array(
    'analysis' => WSKO_Class_Onpage::get_onpage_analysis(),
);
$head_dist = array( __( 'Range', 'wsko' ), __( 'Count', 'wsko' ) );
$is_premium = false;
$stacked = false;
$colors = false;
$onpage_score_dist = WSKO_Class_Onpage::get_onpage_score_graph( $is_premium );
$res_score_dist = array();
for ( $i = 20 ;  $i <= 100 ;  $i += 20 ) {
    $k = $i - 20 . ' - ' . $i;
    $res_score_dist[$k] = array( array(
        'v' => $k,
        'f' => $k,
    ), array(
        'v' => 0,
        'f' => '0',
    ) );
    if ( $is_premium ) {
        $res_score_dist[$k][2] = array(
            'v' => 0,
            'f' => '0',
        );
    }
    foreach ( $onpage_score_dist as $dist ) {
        
        if ( $dist->score_range == $i ) {
            $res_score_dist[$k][1] = array(
                'v' => $dist->count_pages,
                'f' => WSKO_Class_Helper::format_number( $dist->count_pages ),
            );
            
            if ( $is_premium ) {
                $res_score_dist[$k][2] = array(
                    'v' => $dist->count_pages,
                    'f' => WSKO_Class_Helper::format_number( $dist->count_pages ),
                );
                $res_score_dist[$k][1] = array(
                    'v' => $dist->count_pages_i,
                    'f' => WSKO_Class_Helper::format_number( $dist->count_pages_i ),
                );
            }
        
        }
    
    }
}
?>
<div class="row">
	<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'Content Score Distribution', 'wsko' ),
    'col'    => 'col-sm-6 col-xs-12',
    'custom' => ( $onpage_score_history ? WSKO_Class_Template::render_chart(
    'column',
    $head_dist,
    $res_score_dist,
    array(
    'axisTitle'   => __( 'Content Score Range', 'wsko' ),
    'isStacked'   => $stacked,
    'colors'      => $colors,
    'axisTitleY'  => 'Page Count',
    'chart_left'  => '15',
    'chart_width' => '70',
),
    true
) : WSKO_Class_Template::render_template( 'misc/template-no-data.php', array(), true ) ),
) );
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'Avg. Content Score', 'wsko' ),
    'col'    => 'col-sm-6 col-xs-12',
    'custom' => ( $onpage_score_history ? WSKO_Class_Template::render_chart(
    'area',
    array( __( 'Date', 'wsko' ), __( 'Content Score', 'wsko' ) ),
    $onpage_score_history,
    array(
    'axisTitleY'  => __( 'Content Score', 'wsko' ),
    'chart_left'  => '15',
    'chart_width' => '70',
),
    true
) : WSKO_Class_Template::render_template( 'misc/template-no-data.php', array(), true ) ),
) );
/*?>
		
	<div class="wsko-section clearfix col-sm-6 col-xs-12">	
		<p class="panel-heading m0"><?=__( 'Onpage Issues', 'wsko' ) ?></p>	
		<div class="panel-group  clearfix">
			
				<?php ; ?>
			</div>
		</div>	
	</div>
	<?php*/
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'id'     => 'wsko_onpage_issues',
    'class'  => 'wsko-onpage-analysis-issues',
    'title'  => __( 'Onpage Issues', 'wsko' ),
    'col'    => 'col-sm-6 col-xs-12',
    'custom' => '<div id="wsko_onpage_issues" class="wsko-mt10">' . WSKO_Class_Template::render_template( 'onpage/view-onpage-issues.php', $issues_data, true ) . '</div>',
) );
WSKO_Class_Template::render_panel( array(
    'type'    => 'table',
    'title'   => __( 'Pages with the lowest Content Score', 'wsko' ),
    'col'     => 'col-md-6 col-sm-6 col-xs-12',
    'fa'      => 'ellipsis-v',
    'lazyVar' => 'table_worst_pages',
) );
?>
</div>