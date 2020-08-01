<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$post_id = ( isset( $template_args['post_id'] ) ? $template_args['post_id'] : false );
$widget = ( isset( $template_args['widget'] ) && $template_args['widget'] ? true : false );
$frontend_widget = ( isset( $template_args['frontend_widget'] ) && $template_args['frontend_widget'] ? true : false );
$keywords = ( isset( $template_args['keywords'] ) ? $template_args['keywords'] : false );
$page_data = ( isset( $template_args['page_data'] ) ? $template_args['page_data'] : false );
$kw_count = ( isset( $template_args['kw_count'] ) ? $template_args['kw_count'] : false );
$kw_count_ref = ( isset( $template_args['kw_count_ref'] ) ? $template_args['kw_count_ref'] : false );
$preview = ( isset( $template_args['preview'] ) ? $template_args['preview'] : false );
$open_tab = ( isset( $template_args['open_tab'] ) ? $template_args['open_tab'] : false );
$uniqid = WSKO_Class_Helper::get_unique_id();

if ( $post_id ) {
    $backlinks_available = false;
    $linking_available = false;
    $performance_available = false;
    //global $post;
    $post = get_post( $post_id );
    $title_o = get_the_title( $post->ID );
    $content = ( $preview && $preview['post_content'] ? $preview['post_content'] : $post->post_content );
    $title = ( $preview && $preview['post_title'] ? $tile_o = $preview['post_title'] : $post->post_title );
    $slug = ( $preview && $preview['post_slug'] ? $preview['post_slug'] : $post->post_name );
    $link = get_permalink( $post_id );
    $edit_link = get_edit_post_link( $post_id );
    $op_report = WSKO_Class_Onpage::get_onpage_report( $post_id, $preview );
    $priority_keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
    $priority_keywords_count = 0;
    
    if ( $priority_keywords ) {
        foreach ( $priority_keywords as $pk => $pk_data ) {
            
            if ( isset( $op_report['issues']['keyword_density'][$pk] ) ) {
                $priority_keywords[$pk]['den'] = $op_report['issues']['keyword_density'][$pk]['density'];
                $priority_keywords[$pk]['den_type'] = $op_report['issues']['keyword_density'][$pk]['type'];
            }
        
        }
        $priority_keywords_count = count( $priority_keywords );
    }
    
    if ( $keywords ) {
        foreach ( $keywords as $kw ) {
            $kw_data = array(
                'clicks' => '-',
                'pos'    => '-',
                'kw_den' => '-',
            );
            
            if ( isset( $priority_keywords[$kw->keyval] ) ) {
                $priority_keywords[$kw->keyval]['clicks'] = $kw->clicks;
                $priority_keywords[$kw->keyval]['pos'] = $kw->position;
            }
        
        }
    }
    $crawl_data = WSKO_Class_Onpage::get_onpage_page_crawl_data( $link );
    $http_code = false;
    $http_codes = WSKO_Class_Helper::get_http_status_codes( $link, array(), false );
    if ( $http_codes && isset( $http_codes[0]['code'] ) ) {
        $http_code = $http_codes[0]['code'];
    }
    ?><div class="wsko-content-optimizer wsko_wrapper <?php 
    echo  ( $widget ? 'wsko-co-widget wsko-short-view-active' : 'wsko-co-modal' ) ;
    ?> <?php 
    echo  ( $frontend_widget ? 'wsko-co-frontend-widget' : '' ) ;
    ?>">
		<?php 
    
    if ( !$widget ) {
        ?>
			<div class="panel-heading wsko-bg-gray">
				<div class="row">
					<div class="wsko-col-sm-9 wsko-co-header">
						<h4 style="margin:5px 0px 0px;"><?php 
        echo  ( $title_o ? esc_html( $title_o ) : WSKO_Class_Helper::get_empty_page_title() ) ;
        ?> <a class="wsko-small dark button" target="_blank" href="<?php 
        echo  $edit_link ;
        ?>"><i class="fa fa-external-link"></i> <?php 
        echo  __( 'Edit', 'wsko' ) ;
        ?></a> <!--a target="_blank" style="margin-left: -15px;" class="button wsko-fetch-as-google" href="<?php 
        echo  WSKO_Class_Search::get_external_link( 'tool_fetch', WSKO_Class_Helper::get_host_base( true ), ltrim( WSKO_Class_Helper::get_relative_url( get_permalink( $post_id ) ), '/' ) ) ;
        ?>"><i class="fa fa-external-link"></i> <?php 
        echo  __( 'Fetch as Google', 'wsko' ) ;
        ?></a--></h4>
						<span class="wsko-text-off wsko-small"><a class="dark" href="<?php 
        echo  $link ;
        ?>" target="_blank"><i class="fa fa-external-link"></i> <?php 
        echo  $link ;
        ?></a><span class="wsko-mr3 wsko-ml3"> • </span> <span><?php 
        echo  __( 'Last edited:', 'wsko' ) ;
        ?> <?php 
        echo  get_post_modified_time( 'd.m.Y H:i', true, $post_id ) ;
        ?></span> <span class="wsko-co-saving-prefix wsko-mr3 wsko-ml3" style="display:none"> • </span> <span class="wsko-co-saving-text"></span> 
					</div>
					<div class="wsko-col-sm-3">
						<div class="wsko-post-kpi-inner external-co" style="position: absolute;width: 200px;right: 15px;transform: scale(.9);top: -5px;">
							<div class="wsko-co-progress-wrapper wsko-align-center wsko-mb10" style="position:relative;">
								<?php 
        WSKO_Class_Template::render_radial_progress( WSKO_Class_Template::get_status_code_color( $http_code ), __( 'HTTP Code', 'wsko' ), array(
            'val'          => $http_code,
            'hide_percent' => true,
            'class'        => 'wsko-inline-block hidden-xs',
        ) );
        WSKO_Class_Template::render_radial_progress( 'success', __( 'Content Score', 'wsko' ), array(
            'val'   => ( $op_report ? $op_report['onpage_score'] : '-' ),
            'class' => 'wsko-inline-block hidden-xs',
        ) );
        ?>
							</div>
						</div>	
					</div>
				</div>
			</div>
			<?php 
    } else {
        $box_height = WSKO_Class_Core::get_option( 'co_box_height_' . get_current_user_id() );
        ?><div class="wsko-resizable-wrapper wsko_wrapper" style="min-height:200px;max-height:300px;height:<?php 
        echo  ( $box_height ? $box_height : '0' ) ;
        ?>px" data-height="<?php 
        echo  ( $box_height ? $box_height : '0' ) ;
        ?>" data-nonce="<?php 
        echo  wp_create_nonce( 'wsko_co_change_height' ) ;
        ?>">
				<div style="position:relative;height:100%;overflow-y:auto;overflow-x:hidden;" class="wsko-resizable-content"><?php 
    }
    
    ?>
			<div class="wsko-shadow <?php 
    echo  ( $widget ? '' : 'wsko-bg-gray' ) ;
    ?>">
				<ul class="wsko-nav wsko-nav-main bsu-tabs" <?php 
    echo  ( $widget ? 'style="display:none;"' : '' ) ;
    ?>>
				  <li><a class="wsko-nav-link <?php 
    echo  ( !$open_tab ? 'wsko-nav-link-active' : '' ) ;
    ?>" href="#wsko_overview_<?php 
    echo  $uniqid ;
    ?>"><?php 
    echo  __( 'Overview', 'wsko' ) ;
    ?></a></li>
				  <li><a class="wsko-nav-link <?php 
    echo  ( $open_tab === 'metas' ? 'wsko-nav-link-active' : '' ) ;
    ?>" href="#wsko_metas_<?php 
    echo  $uniqid ;
    ?>"><?php 
    echo  __( 'Metas & Snippets', 'wsko' ) ;
    ?></a></li>
				  <?php 
    /* <li><a class="wsko-nav-link <?=$open_tab === 'social' ?  'wsko-nav-link-active' : ''?>" href="#wsko_snippets_<?=$uniqid?>"><?=__( 'Social Snippets', 'wsko' ) ?></a></li> */
    ?>
				  <li><a class="wsko-nav-link <?php 
    echo  ( $open_tab === 'keywords' ? 'wsko-nav-link-active' : '' ) ;
    ?>" href="#wsko_keywords_<?php 
    echo  $uniqid ;
    ?>"><?php 
    echo  __( 'Keywords', 'wsko' ) ;
    ?></a></li>
				  <?php 
    
    if ( !$widget ) {
        ?><li><a id="wsko_co_nav_link_content" class="wsko-nav-link <?php 
        echo  ( $open_tab === 'content' ? 'wsko-nav-link-active' : '' ) ;
        ?>" href="#wsko_content_<?php 
        echo  $uniqid ;
        ?>"><?php 
        echo  __( 'Content', 'wsko' ) ;
        ?></a></li><?php 
    }
    
    ?>
				  <?php 
    ?>
					<li><a class="wsko-nav-link <?php 
    echo  ( $open_tab === 'technical' ? 'wsko-nav-link-active' : '' ) ;
    ?>" href="#wsko_technical_<?php 
    echo  $uniqid ;
    ?>"><?php 
    echo  __( 'Advanced', 'wsko' ) ;
    ?></a></li>
				</ul>
				<?php 
    if ( $widget ) {
        ?>
					<a href="#" class="wsko-resizable-wrapper-quick-up dark wsko-co-collapse-top text-off">
						<i class="fa fa-times fa-fw"></i> 
					</a>
					<a href="#" class="wsko-resizable-wrapper-quick-down dark wsko-co-collapse-top text-off">
						<i class="fa fa-arrows-alt fa-fw"></i>
					</a>
				<?php 
    }
    ?>	
			</div>	
			<div class="wsko-co-main <?php 
    echo  ( $widget ? 'mt15' : 'm15' ) ;
    ?>">	
				<div class="wsko-row">
					<div class="wsko-co-notifications-overlay" style="display:none;"></div>
				</div>	
				<div class="wsko-row" style="margin-right:-13px;margin-left:-13px;">
					<div class="wsko-col-sm-8 wsko-col-xs-12 wsko-widget-main">
					<?php 
    if ( WSKO_Class_Core::is_demo() ) {
        WSKO_Class_Template::render_notification( 'warning', array(
            'msg' => wsko_loc( 'notif_co', 'demo' ),
        ) );
    }
    ?>
						<div class="wsko-tab-content wsko-content-optimizer-inner">
						 
						  <div id="wsko_overview_<?php 
    echo  $uniqid ;
    ?>" class="wsko-tab <?php 
    echo  ( !$open_tab ? 'wsko-tab-active' : '' ) ;
    ?>">
								<div class="wsko-content-optimizer-overview">
									<div class="wsko-row wsko-overview-hero-wrapper">
									  <?php 
    WSKO_Class_Template::render_panel( array(
        'type'   => 'hero-custom',
        'fa'     => '',
        'title'  => __( 'Keywords', 'wsko' ),
        'col'    => 'wsko-col-sm-3 wsko-col-xs-6 col-sm-3 col-xs-6',
        'custom' => ( $keywords ? WSKO_Class_Helper::format_number( $kw_count ) . (( $kw_count_ref != null ? WSKO_Class_Template::render_progress_icon(
        $kw_count_ref,
        WSKO_Class_Helper::get_ref_value( $kw_count, $kw_count_ref ),
        array(
        'decimals' => 2,
    ),
        true
    ) : '' )) : '-' ),
    ) );
    WSKO_Class_Template::render_panel( array(
        'type'   => 'hero-custom',
        'fa'     => '',
        'title'  => __( 'Klicks', 'wsko' ),
        'col'    => 'wsko-col-sm-3 wsko-col-xs-6 col-sm-3 col-xs-6',
        'custom' => ( $page_data ? WSKO_Class_Helper::format_number( $page_data->clicks ) . (( $page_data->clicks_ref != null ? WSKO_Class_Template::render_progress_icon(
        $page_data->clicks_ref,
        WSKO_Class_Helper::get_ref_value( $page_data->clicks, $page_data->clicks_ref ),
        array(
        'decimals' => 2,
    ),
        true
    ) : '' )) : '-' ),
    ) );
    WSKO_Class_Template::render_panel( array(
        'type'   => 'hero-custom',
        'fa'     => '',
        'title'  => __( 'Impressions', 'wsko' ),
        'col'    => 'wsko-col-sm-3 wsko-col-xs-6 col-sm-3 col-xs-6',
        'custom' => ( $page_data ? WSKO_Class_Helper::format_number( $page_data->impressions ) . (( $page_data->impressions_ref != null ? WSKO_Class_Template::render_progress_icon(
        $page_data->impressions_ref,
        WSKO_Class_Helper::get_ref_value( $page_data->impressions, $page_data->impressions_ref ),
        array(
        'decimals' => 2,
    ),
        true
    ) : '' )) : '-' ),
    ) );
    WSKO_Class_Template::render_panel( array(
        'type'   => 'hero-custom',
        'fa'     => '',
        'title'  => __( 'Avg. Position', 'wsko' ),
        'col'    => 'wsko-col-sm-3 wsko-col-xs-6 col-sm-3 col-xs-6',
        'custom' => ( $page_data ? WSKO_Class_Helper::format_number( $page_data->position ) . (( $page_data->position_ref != null ? WSKO_Class_Template::render_progress_icon(
        round( abs( $page_data->position_ref - $page_data->position ), 2 ),
        -WSKO_Class_Helper::get_ref_value( $page_data->position, $page_data->position_ref ),
        array(
        'decimals' => 2,
        'absolute' => true,
        'tooltip'  => $page_data->position_ref,
    ),
        true
    ) : '' )) : '-' ),
    ) );
    ?>
									</div>
									<?php 
    WSKO_Class_Template::render_template( 'content-optimizer/template-co-issues.php', array(
        'op_report' => $op_report,
    ) );
    ?>
								</div>	
						  </div>

						  <div id="wsko_metas_<?php 
    echo  $uniqid ;
    ?>" class="wsko-tab <?php 
    echo  ( $open_tab === 'metas' ? 'wsko-tab-active' : '' ) ;
    ?>">
							<?php 
    
    if ( WSKO_Class_Onpage::seo_plugins_disabled() ) {
        WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
            'meta_view' => 'co',
            'post_id'   => $post_id,
            'type'      => 'post_id',
        ) );
    } else {
        $source = WSKO_Class_Compatibility::get_seo_plugin_preview( 'metas' );
        
        if ( $source ) {
            WSKO_Class_Template::render_template( 'misc/template-seo-plugins-disable.php', array(
                'preview' => true,
            ) );
            WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                'meta_view' => 'co',
                'post_id'   => $post_id,
                'type'      => 'post_id',
                'preview'   => $source,
            ) );
        } else {
            WSKO_Class_Template::render_template( 'misc/template-seo-plugins-disable.php', array() );
        }
    
    }
    
    ?>
						  </div>
						
						<?php 
    /*
      <div id="wsko_snippets_<?=$uniqid?>" class="wsko-tab <?=$open_tab === 'social' ?  'wsko-tab-active' : ''?>">
    	<?php 
    	if (WSKO_Class_Onpage::seo_plugins_disabled())
    	{
    		WSKO_Class_Template::render_template('tools/template-metas-view.php', array('meta_view' => 'social', 'post_id' => $post_id, 'type' => 'post_id'));
    	}	
    	else
    	{
    		WSKO_Class_Template::render_template('misc/template-seo-plugins-disable.php', array());
    	} ?>
      </div>
    */
    ?>
						  <div id="wsko_keywords_<?php 
    echo  $uniqid ;
    ?>" class="wsko-reloadable-keywords wsko-tab <?php 
    echo  ( $open_tab === 'keywords' ? 'wsko-tab-active' : '' ) ;
    ?>">
						  	<div class="wsko-co-lazy-tab" data-post="<?php 
    echo  $post_id ;
    ?>" data-tab="keywords"></div>
						  </div>

						  <div id="wsko_technical_<?php 
    echo  $uniqid ;
    ?>" class="wsko-co-advanced wsko-tab <?php 
    echo  ( $open_tab === 'technical' ? 'wsko-tab-active' : '' ) ;
    ?>">
								<?php 
    WSKO_Class_Template::render_template( 'content-optimizer/template-co-technical.php', array(
        'post_id' => $post_id,
    ) );
    ?>
						  </div>

						  <?php 
    
    if ( !$widget ) {
        ?>
							  <div id="wsko_content_<?php 
        echo  $uniqid ;
        ?>" class="wsko-reloadable-content wsko-tab <?php 
        echo  ( $open_tab === 'content' ? 'wsko-tab-active' : '' ) ;
        ?>">
							  	<div class="wsko-co-lazy-tab" data-post="<?php 
        echo  $post_id ;
        ?>" data-tab="content"></div>
							  </div>
						  <?php 
    }
    
    ?>
						<?php 
    ?>
						</div>
					</div>
					<div class="wsko-col-sm-4 wsko-col-xs-12 wsko-widget-sidebar">
						<?php 
    
    if ( $widget ) {
        ?>
							<div class="wsko-post-kpi-inner">
								<div class="wsko-co-progress-wrapper wsko-align-center wsko-mb10" style="position:relative;">
									<?php 
        WSKO_Class_Template::render_radial_progress( WSKO_Class_Template::get_status_code_color( $http_code ), __( 'HTTP Code', 'wsko' ), array(
            'val'          => $http_code,
            'hide_percent' => true,
            'class'        => 'wsko-inline-block hidden-xs',
        ) );
        WSKO_Class_Template::render_radial_progress( 'success', __( 'Content Score', 'wsko' ), array(
            'val'   => ( $op_report ? $op_report['onpage_score'] : '-' ),
            'class' => 'wsko-inline-block hidden-xs',
        ) );
        ?>
								</div>
							</div>	
						<?php 
    }
    
    ?>	
						<div class="wsko-sidebar-keywords-view">
							<?php 
    WSKO_Class_Template::render_template( 'content-optimizer/template-co-sidebar-keywords.php', array(
        'post_id'           => $post_id,
        'priority_keywords' => $priority_keywords,
        'keyword_data'      => $keywords,
        'op_report'         => $op_report,
    ) );
    ?>
						</div>
					</div>
				</div>	
				<div class="clearfix">
				</div>
			</div>	
		<?php 
    
    if ( $widget ) {
        ?>
				</div>
				<div class="wsko-co-control-wrapper wsko-align-center">
					<div class="wsko-rezisable-wrapper-thumb text-off" style="margin-right: 15px;">
						<i class="fa fa-expand fa-fw"></i> <?php 
        echo  __( 'Drag', 'wsko' ) ;
        ?>
					</div>
					<a href="#" class="wsko-resizable-wrapper-quick-down dark text-off">
						<i class="fa fa-arrows-alt fa-fw"></i> <?php 
        echo  __( 'Expand', 'wsko' ) ;
        ?>
					</a>
					<a href="#" class="wsko-resizable-wrapper-quick-up dark text-off">
						<i class="fa fa-times"></i> <?php 
        echo  __( 'Collapse', 'wsko' ) ;
        ?>
					</a>					
				</div>	
			</div>
			<?php 
        //iframe height fix
        ?>
			<style>body,#wpwrap{min-height:unset!important;height:auto!important}</style><?php 
    }
    
    ?>
		<?php 
    /* <a class="wsko-expand-content-optimizer-link wsko-co-expand-icon dark text-off" href="#"><span class="wsko-expand-content-optimizer"><i class="fa fa-angle-down fa-fw fa-2x"></i></span></a>
    		<p class="wsko-align-center" style="margin-bottom:0px;"><a class="wsko-expand-content-optimizer-link wsko-co-expand-icon-close dark text-off" href="#"><span class="wsko-expand-content-optimizer"><i class="fa fa-angle-up fa-fw fa-2x"></i></span></a></p> */
    ?>
		<!--div class="wsko-co-notifications-overlay" style="position:absolute;bottom:10px;right:10px;"></div-->
	</div>
	<?php 
} else {
    _e( 'Post ID not set!', 'wsko' );
}
