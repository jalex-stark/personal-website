<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
$analysis = ( isset( $global_analysis['current_report'] ) ? $global_analysis['current_report'] : false );
$chart_dist = array();
$chart_den_dist = array();
$chart_dupl_dist = array();
$matrix_dupl = array();
$headers_den = array( 'Size', __( 'Prio 1 Keywords', 'wsko' ) );
$is_prem = false;

if ( $analysis ) {
    $chart_dist[] = array( __( 'Prio 1 Keywords', 'wsko' ), array(
        'v' => $analysis['priority_kw_dist']['prio1'][0],
        'f' => WSKO_Class_Helper::format_Number( $analysis['priority_kw_dist']['prio1'][0] ),
    ), array(
        'v' => $analysis['priority_kw_dist']['prio1'][1],
        'f' => WSKO_Class_Helper::format_Number( $analysis['priority_kw_dist']['prio1'][1] ),
    ) );
    if ( $is_prem ) {
        $chart_dist[] = array( __( 'Prio 2 Keywords', 'wsko' ), array(
            'v' => $analysis['priority_kw_dist']['prio2'][0],
            'f' => WSKO_Class_Helper::format_Number( $analysis['priority_kw_dist']['prio2'][0] ),
        ), array(
            'v' => $analysis['priority_kw_dist']['prio2'][1],
            'f' => WSKO_Class_Helper::format_Number( $analysis['priority_kw_dist']['prio2'][1] ),
        ) );
    }
    foreach ( $analysis['priority_kw_den_dist'] as $k => $dupl ) {
        
        if ( $is_prem ) {
            $chart_den_dist[] = array( $k, array(
                'v' => $dupl['prio1'],
                'f' => WSKO_Class_Helper::format_Number( $dupl['prio1'] ),
            ), array(
                'v' => $dupl['prio2'],
                'f' => WSKO_Class_Helper::format_Number( $dupl['prio2'] ),
            ) );
        } else {
            $chart_den_dist[] = array( $k, array(
                'v' => $dupl['prio1'],
                'f' => WSKO_Class_Helper::format_Number( $dupl['prio1'] ),
            ) );
        }
    
    }
    foreach ( $analysis['priority_kw_duplicate_dist'] as $k => $dupl ) {
        if ( $k != 0 ) {
            
            if ( $k == 9 ) {
                $chart_dupl_dist[] = array( sprintf( __( '> %d Duplicates', 'wsko' ), $k - 1 ), array(
                    'v' => $dupl,
                    'f' => WSKO_Class_Helper::format_Number( $dupl ),
                ) );
            } else {
                $chart_dupl_dist[] = array( sprintf( _n(
                    '%s Duplicate',
                    '%s Duplicates',
                    $k,
                    'wsko'
                ), number_format_i18n( $k ) ), array(
                    'v' => $dupl,
                    'f' => WSKO_Class_Helper::format_Number( $dupl ),
                ) );
            }
        
        }
    }
    $matrix_dupl = array( array(
        'post_count:2:2',
        'post_count:3:3',
        'post_count:4:4',
        'post_count:5:5',
        'post_count:6:6',
        'post_count:7:7',
        'post_count:8:' . $analysis['max']['kw_dupl']
    ) );
}

$matrix_pages = array( array( 'prio1_kw_den:off' ), array( 'prio1_kw_den:on' ) );
$matrix_keywords = array( array(
    'prio:1,density:0:0.5',
    'prio:1,density:0.5:1.5',
    'prio:1,density:1.5:2.5',
    'prio:1,density:2.5:3.5',
    'prio:1,density:3.5:5',
    'prio:1,density:5:100'
), array(
    'prio:2,density:0:0.5',
    'prio:2,density:0.5:1.5',
    'prio:2,density:1.5:2.5',
    'prio:2,density:2.5:3.5',
    'prio:2,density:3.5:5',
    'prio:2,density:5:100'
) );
$pages_header = array(
    __( 'CS', 'wsko' ) . WSKO_Class_Template::render_infoTooltip( __( 'Content Score', 'wsko' ), 'info', true ),
    __( 'Page', 'wsko' ),
    array(
    'name'  => __( 'Prio 1 Keywords', 'wsko' ),
    'width' => '15%',
),
    ''
);
$pages_filter = array(
    'onpage_score' => array(
    'title' => __( 'Onpage Score', 'wsko' ),
    'type'  => 'number_range',
    'max'   => $analysis['max']['onpage_score'],
),
    'url'          => array(
    'title' => __( 'URL', 'wsko' ),
    'type'  => 'text',
),
    'post_type'    => array(
    'title'  => __( 'Post Type', 'wsko' ),
    'type'   => 'select',
    'values' => ( isset( $analysis['post_types'] ) ? WSKO_Class_Helper::get_post_type_labels( $analysis['post_types'] ) : array() ),
),
    'prio1_kw_den' => array(
    'title' => __( 'Prio 1 Keywords', 'wsko' ),
    'type'  => 'set',
),
);
?>
<div class="row">
	<div class="col-sm-12 col-xs-12">
		<ul class="nav nav-tabs bsu-tabs border-dark">
			<li class="waves-effect active"><a data-toggle="tab" href="#wsko_onpage_analysis_keywords_pages"><?php 
echo  __( 'Pages', 'wsko' ) ;
?></a></li>
			<li class="waves-effect"><a data-toggle="tab" href="#wsko_onpage_analysis_keywords_keywords"><?php 
echo  __( 'All Keywords', 'wsko' ) ;
?></a></li>
			<li class="waves-effect"><a data-toggle="tab" href="#wsko_onpage_analysis_keywords_duplicates"><?php 
echo  __( 'Duplicates', 'wsko' ) ;
?></a></li>
		</ul>
		<div class="row">
			<div class="tab-content">
				<div id="wsko_onpage_analysis_keywords_pages" class="tab-pane fade in active">
					<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'SEO Keywords', 'wsko' ) . ' ' . WSKO_Class_Template::render_infoTooltip( __( 'Shows how many pages have SEO Keywords set', 'wsko' ), 'info', true ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => ( $chart_dist ? WSKO_Class_Template::render_chart(
    'column',
    array( __( 'Priority', 'wsko' ), __( 'Not Set', 'wsko' ), __( 'Set', 'wsko' ) ),
    $chart_dist,
    array(
    'isStacked'    => true,
    'chart_id'     => 'prio_keywords',
    'colors'       => array( '#d9534f', '#5cb85c' ),
    'table_filter' => array(
    'table'        => '#wsko_onpage_analysis_keywords_pages_table',
    'value_matrix' => $matrix_pages,
),
    'axisTitle'    => __( 'Keywords', 'wsko' ),
    'format'       => array(
    '1' => __( '{0} Pages', 'wsko' ),
    '2' => __( '{0} Pages', 'wsko' ),
),
    'axisTitleY'   => __( 'Page Count', 'wsko' ),
    'chart_left'   => '15',
    'chart_width'  => '70',
),
    true
) : WSKO_Class_Template::render_template( 'misc/template-no-data.php', array(), true ) ),
) );
?>
					
					<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'Pages', 'wsko' ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => WSKO_Class_Template::render_table(
    $pages_header,
    array(),
    array(
    'id'     => 'wsko_onpage_analysis_keywords_pages_table',
    'order'  => array(
    'col' => 2,
    'dir' => 'desc',
),
    'ajax'   => array(
    'action' => 'wsko_table_onpage',
    'arg'    => 'keywords',
),
    'filter' => $pages_filter,
),
    true
),
) );
?>
				</div>
				<div id="wsko_onpage_analysis_keywords_keywords" class="tab-pane fade">
					<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'Keyword Density Distribution', 'wsko' ) . ' ' . WSKO_Class_Template::render_infoTooltip( __( 'Shows the density range of your keywords over all posts', 'wsko' ), 'info', true ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => WSKO_Class_Template::render_chart(
    'column',
    $headers_den,
    $chart_den_dist,
    array(
    'isStacked'    => true,
    'chart_id'     => '',
    'table_filter' => array(
    'table'        => '#wsko_onpage_analysis_keywords_table',
    'value_matrix' => $matrix_keywords,
),
    'row_colors'   => array(
    '#d9534f',
    '#f0ad4e',
    '#5cb85c',
    '#f0ad4e',
    '#d9534f',
    '#d9534f'
),
    'colors'       => array( '#d9534f', '#d9534f' ),
    'axisTitle'    => __( 'Keyword Density', 'wsko' ),
    'format'       => array(
    '1' => __( '{0} Keywords', 'wsko' ),
    '2' => __( '{0} Pages', 'wsko' ),
),
    'axisTitleY'   => __( 'Keyword Count', 'wsko' ),
    'chart_left'   => '15',
    'chart_width'  => '70',
),
    true
),
) );
?>
					<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'SEO Keywords', 'wsko' ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => WSKO_Class_Template::render_table(
    array(
    array(
    'name'  => __( 'Keyword', 'wsko' ),
    'width' => '30%',
),
    __( 'Page', 'wsko' ),
    array(
    'name'  => __( 'Prio', 'wsko' ),
    'width' => '10%',
),
    array(
    'name'  => __( 'Keyword Density', 'wsko' ),
    'width' => '15%',
)
),
    array(),
    array(
    'id'     => 'wsko_onpage_analysis_keywords_table',
    'order'  => array(
    'col' => 3,
    'dir' => 'asc',
),
    'ajax'   => array(
    'action' => 'wsko_table_onpage',
    'arg'    => 'keywords_uniq',
),
    'filter' => array(
    'density' => array(
    'title' => __( 'Keyword Density', 'wsko' ),
    'type'  => 'number_range',
    'max'   => 100,
),
    'prio'    => array(
    'title'  => __( 'Priority', 'wsko' ),
    'type'   => 'select',
    'values' => array(
    '1' => __( 'Prio 1', 'wsko' ),
    '2' => __( 'Prio 2', 'wsko' ),
),
),
),
),
    true
),
) );
?>			
				</div>
				<div id="wsko_onpage_analysis_keywords_duplicates" class="tab-pane fade">
					<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'Keyword Duplicates', 'wsko' ) . ' ' . WSKO_Class_Template::render_infoTooltip( __( 'Shows the distribution of duplicate keywords over all crawled pages', 'wsko' ), 'info', true ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => WSKO_Class_Template::render_chart(
    'column',
    array( __( 'Duplicates', 'wsko' ), __( 'Keywords Count', 'wsko' ) ),
    $chart_dupl_dist,
    array(
    'isStacked'    => true,
    'chart_id'     => '',
    'table_filter' => array(
    'table'        => '#wsko_onpage_analysis_keywords_dupl_table',
    'value_matrix' => $matrix_dupl,
),
    'colors'       => array( '#d9534f' ),
    'axisTitle'    => __( 'Duplicates', 'wsko' ),
    'format'       => array(
    '1' => __( '{0} Duplicates', 'wsko' ),
),
    'axisTitleY'   => __( 'Keyword Count', 'wsko' ),
    'chart_left'   => '15',
    'chart_width'  => '70',
),
    true
),
) );
?>	
					<?php 
WSKO_Class_Template::render_panel( array(
    'type'   => 'custom',
    'title'  => __( 'SEO Keywords with Duplicates', 'wsko' ),
    'col'    => 'col-sm-12 col-xs-12',
    'custom' => WSKO_Class_Template::render_table(
    array( array(
    'name'  => __( 'Keyword', 'wsko' ),
    'width' => '50%',
), array(
    'name'  => __( 'Posts', 'wsko' ),
    'width' => '50%',
) ),
    array(),
    array(
    'id'     => 'wsko_onpage_analysis_keywords_dupl_table',
    'order'  => array(
    'col' => 1,
    'dir' => 'desc',
),
    'ajax'   => array(
    'action' => 'wsko_table_onpage',
    'arg'    => 'keywords_dupl',
),
    'filter' => array(
    'post_count' => array(
    'title' => __( 'Posts', 'wsko' ),
    'type'  => 'number_range',
    'max'   => $analysis['max']['kw_dupl'],
),
),
),
    true
),
) );
?>			
				</div>
			</div>
		</div>	
</div>