<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
	<div class="wsko-dashboard">
		<div class="row">	
			<div class="col-sm-12 col-xs-12">
				<div class="wsko-dashboard-title-wrapper">
					<div class="panel-title"><?php 
/* bloginfo( 'name' ); */
?></div>

					<div class="wsko-dashboard-head-wrapper collapsed wsko-mb15">
						<?php 
?>
						<div class="wsko-section wsko-dashboard-head">
							<?php 
echo  WSKO_Class_Template::render_panel( array(
    'type'      => 'hero',
    'title'     => __( 'Keywords', 'wsko' ),
    'col'       => 'col-md-3 col-sm-6 col-xs-12',
    'fa'        => 'key',
    'custom'    => '<div id="wsko_db_search_keywords">' . WSKO_Class_Template::render_preloader( array(
    'size' => 'small',
), true ) . '</div>',
    'tableLink' => array(
    'link'  => WSKO_Controller_Search::get_link( 'keywords' ),
    'title' => __( 'More', 'wsko' ),
),
) ) ;
?>
							<?php 
echo  WSKO_Class_Template::render_panel( array(
    'type'      => 'hero',
    'title'     => __( 'Google Clicks', 'wsko' ),
    'col'       => 'col-md-3 col-sm-6 col-xs-12',
    'fa'        => 'mouse-pointer',
    'custom'    => '<div id="wsko_db_search_clicks">' . WSKO_Class_Template::render_preloader( array(
    'size' => 'small',
), true ) . '</div>',
    'tableLink' => array(
    'link'  => WSKO_Controller_Search::get_link( 'overview' ),
    'title' => __( 'More', 'wsko' ),
),
) ) ;
?>
							<?php 
echo  WSKO_Class_Template::render_panel( array(
    'type'      => 'hero',
    'title'     => __( 'Content Score', 'wsko' ),
    'col'       => 'col-md-3 col-sm-6 col-xs-12',
    'fa'        => 'trophy',
    'custom'    => '<div id="wsko_db_onpage_score">' . WSKO_Class_Template::render_preloader( array(
    'size' => 'small',
), true ) . '</div>',
    'tableLink' => array(
    'link'  => WSKO_Controller_Onpage::get_link( 'overview' ),
    'title' => __( 'More', 'wsko' ),
),
) ) ;
?>
							<?php 
echo  WSKO_Class_Template::render_panel( array(
    'type'      => 'hero',
    'title'     => __( 'Onpage Issues', 'wsko' ),
    'col'       => 'col-md-3 col-sm-6 col-xs-12',
    'fa'        => 'cog',
    'custom'    => '<div id="wsko_db_onpage_issues">' . WSKO_Class_Template::render_preloader( array(
    'size' => 'small',
), true ) . '</div>',
    'tableLink' => array(
    'link'  => WSKO_Controller_Onpage::get_link( 'overview' ),
    'title' => __( 'More', 'wsko' ),
),
) ) ;
?>
							<?php 
/* if (wsko_fs()->is__premium_only()){if (wsko_fs()->can_use_premium_code()){ ?>
				<div class="col-sm-12">
					<div class="row">
						<div class="col-sm-12 col-xs-12">
							<p>Account information</p>
							<ul class="row wsko-account-information">
								<div class="col-sm-3"><li>Plan <span class="pull-right badge">BUSINESS</span></li></div>
								<div class="col-sm-3"><li>Keyword Research Queries</li></div>
								<div class="col-sm-3"><li>Keyword Queries</li></div>												
								<div class="col-sm-3"><li>Competitors Queries</li></div>																																			
							</ul>
						</div>		
					</div>
				</div>
			<?php }} */
?>		
						</div>
						<?php 
/*
<div class="wsko-head-expand-wrapper">
	<a href="#" class="wsko-head-expand"><i class="fa fa-angle-right"></i><small></small></a>
</div>	
*/
?>
					</div>		

					<div class="wsko-dashboard-nav">
						<ul class="nav nav-tabs bsu-tabs wsko-big-nav">
							<li class="active"><a data-toggle="tab" href="#wsko-dashboard-search"><i class="fa fa-search fa-fw mr5"></i><?php 
echo  __( 'Search', 'wsko' ) ;
?></a></li>
							<li class=""><a data-toggle="tab" href="#wsko-dashboard-onpage"><i class="fa fa-code fa-fw mr5"></i><?php 
echo  __( 'Onpage', 'wsko' ) ;
?></a></li>
							<?php 

if ( !wsko_fs()->is_premium() || !wsko_fs()->can_use_premium_code() ) {
    ?>
									<li class=""><a data-toggle="tab" href="#wsko-dashboard-upgrade"><i class="fa fa-link fa-fw mr5"></i><?php 
    echo  __( 'Backlinks', 'wsko' ) ;
    ?><span class="wsko-badge badge-success"><?php 
    echo  __( 'PRO', 'wsko' ) ;
    ?></span></a></li>
									<li class=""><a data-toggle="tab" href="#wsko-dashboard-upgrade"><i class="fa fa-rocket fa-fw mr5"></i><?php 
    echo  __( 'Performance', 'wsko' ) ;
    ?></a><span class="wsko-badge badge-success"><?php 
    echo  __( 'PRO', 'wsko' ) ;
    ?></span></a></li>
								<?php 
}

/*
<li class=""><a data-toggle="tab" href="#wsko-dashboard-social"><i class="fa fa-share-alt fa-fw mr5"></i><?=__( 'Social', 'wsko' ) ?></a></li>					
*/
?>						
						</ul>
					</div>						
				</div>		
			</div>	
	
			
			<div class="tab-content col-sm-12 col-xs-12">
				<?php 
//tab order will affect load order (slowest last)
?>
				<?php 

if ( !wsko_fs()->is_premium() || !wsko_fs()->can_use_premium_code() ) {
    ?>
					<div id="wsko-dashboard-upgrade" class="tab-pane fade">
						<?php 
    WSKO_Class_Template::render_template( 'dashboard/template-dashboard-upgrade.php', array() );
    ?>	
					</div>	
				<?php 
}

?>	

				<div id="wsko-dashboard-onpage" class="tab-pane fade">	
						<?php 
WSKO_Controller_Onpage::get_lazy_page_widget_beacon( 'overview' );
?>
				</div>
				
				<?php 
/*
<div id="wsko-dashboard-social" class="tab-pane fade">	
</div>
*/
?>

				<?php 
?>

				<div id="wsko-dashboard-search" class="tab-pane fade in active">			
					<?php 
WSKO_Controller_Search::get_lazy_page_widget_beacon( 'overview' );
?>
				</div>	
			</div>
		</div>
	</div>