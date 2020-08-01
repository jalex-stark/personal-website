<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( current_user_can( 'manage_options' ) ) {
    $post_types = WSKO_Class_Helper::get_public_post_types( 'objects' );
    $widget = ( isset( $template_args['widget'] ) ? $template_args['widget'] : false );
    $pt_width = ( isset( $template_args['pt_width'] ) ? $template_args['pt_width'] : 'col-sm-4 col-md-4 col-xs-12' );
    $collapsed = ( isset( $template_args['collapsed'] ) ? $template_args['collapsed'] : false );
    
    if ( !$widget ) {
        ?><div class="bsu-panel panel panel-default onpage-analysis-config-wrapper">
		<?php 
        
        if ( !$collapsed ) {
            ?>
			<p class="panel-heading m0"><?php 
            echo  __( 'Onpage Analysis Settings', 'wsko' ) ;
            ?></p><?php 
        } else {
            ?>
			<div class="panel-heading m0"><a data-toggle="collapse" class="dark" data-parent="#wsko_setup_accordion" href="#onpage_settings"><i class="fa fa-angle-down fa-fw mr5"></i><?php 
            echo  __( 'Onpage Analysis Settings', 'wsko' ) ;
            ?></a></div><?php 
        }
        
        ?>	
		<div id="onpage_settings" class="panel-body <?php 
        echo  ( $collapsed ? 'panel-collapse collapse' : '' ) ;
        ?>">
	<?php 
    }
    
    ?>
			<?php 
    
    if ( (WSKO_Class_Helper::is_dev() || WSKO_Class_Core::is_demo()) && current_user_can( 'manage_options' ) ) {
        ?>
				<div class="row form-group">
					<div class="col-sm-3">
						<p class="m0"><?php 
        echo  __( 'Controls', 'wsko' ) ;
        ?></p>
					</div>
					<div class="col-sm-9">
						<label>
							<?php 
        WSKO_Class_Template::render_ajax_button(
            __( 'Regenerate Analysis', 'wsko' ),
            'regen_onpage_analysis',
            array(),
            array(
            'no_reload' => true,
            'alert'     => __( 'Do you really want to regenerate your Onpage report?', 'wsko' ),
        )
        );
        ?>
						</label>
					</div>
				</div>
			<?php 
    }
    
    //klassen "wsko-onpage-post-types-h1-title" und "wsko-onpage-include-post-types" müssen auf unterschiedlichen parent divs liegen
    ?>
			<div class="row wsko-onpage-include-post-types form-group">
				<div class="col-sm-3 col-xs-12">
					<p class="m0"><?php 
    echo  __( '<b>Include</b> post types in Onpage Analysis', 'wsko' ) ;
    ?></p>
					<small class="text-off"><?php 
    echo  __( 'The Onpage Analysis generation is based on your posts. Select the post types you want to be included in the analysis.', 'wsko' ) ;
    ?></small>
				</div>
				<div class="col-sm-9 col-xs-12 wsko-onpage-post-types-h1-title">
					<div class="row" style="overflow-y:auto;max-height:250px;overflow: visible;">
						<?php 
    $post_types_h1 = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'onpage_title_h1' ) );
    //$excluded = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('onpage_exclude_post_types'));
    $included = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'onpage_include_post_types' ) );
    foreach ( $post_types as $pt ) {
        $is_active = in_array( $pt->name, $included );
        ?><div class="<?php 
        echo  $pt_width ;
        ?> wsko-onpage-include-pt-wrapper">
								<div class="wsko-onpage-include-pt-inner">
									<input class="form-control wsko-ajax-input wsko-switch wsko-onpage-include-pt" style="float:right; margin-top:5px;" type="checkbox" <?php 
        echo  ( $is_active ? 'checked="checked"' : '' ) ;
        ?> data-wsko-target="settings" data-wsko-setting="onpage_include_post_types" data-multi-parent=".wsko-onpage-include-post-types" value="<?php 
        echo  $pt->name ;
        ?>">
									<?php 
        echo  $pt->label ;
        ?> <span class="settings-sub font-unimportant" style="margin-left:0px;"><?php 
        echo  $pt->name ;
        ?></span>
									<div class="wsko-onpage-include-pt-add"> <?php 
        /*=$is_active?'':'style="display:none"'*/
        ?>
										<label class="small">
											<input class="form-control wsko-ajax-input" type="checkbox" <?php 
        echo  ( in_array( $pt->name, $post_types_h1 ) ? 'checked="checked"' : '' ) ;
        ?> data-wsko-target="settings" data-wsko-setting="onpage_title_h1" data-multi-parent=".wsko-onpage-post-types-h1-title" value="<?php 
        echo  $pt->name ;
        ?>">
											Post title is H1 <?php 
        WSKO_Class_Template::render_infoTooltip( __( 'The post type template is rendering the post title inside a H1 on the post page', 'wsko' ), 'info' );
        ?>
										</label>
									</div>
								</div>	
							</div><?php 
    }
    ?>
					</div>	
				</div>	
			</div>
			<?php 
    
    if ( WSKO_Class_Core::is_configured() ) {
        ?>
				<div id="wsko_onpage_advanced_conf_collapse" class="collapse">
					<?php 
        $posts = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'onpage_exclude_posts' ) );
        ?>
					<div class="row form-group">
						<div class="col-sm-3">
							<p class="m0"><?php 
        echo  __( '<b>Exclude</b> posts from Onpage Analysis', 'wsko' ) ;
        ?></p>
							<small class="text-off"><?php 
        echo  __( 'Exclude single posts from Onpage Analysis (Post IDs separated by a comma)', 'wsko' ) ;
        ?></small>
						</div>
						<div class="col-sm-9">
							<!--input class="form-control wsko-ajax-input" type="text" data-wsko-target="settings" data-wsko-setting="onpage_exclude_posts" value="<?php 
        echo  WSKO_Class_Core::get_setting( 'onpage_exclude_posts' ) ;
        ?>" placeholder="Post IDs (1,12,355)"-->
							<select multiple class="form-control wsko-selectpicker-ajax wsko-ajax-input" data-live-search="true" data-show-subtext="true" data-ajax-action="wsko_search_posts_selectpicker" data-ajax-nonce="<?php 
        echo  wp_create_nonce( 'wsko_search_posts_selectpicker' ) ;
        ?>" data-wsko-target="settings" data-wsko-setting="onpage_exclude_posts">
								<?php 
        foreach ( $posts as $p ) {
            ?>
									<option value="<?php 
            echo  $p ;
            ?>" data-subtext="<?php 
            echo  get_the_title( $p ) ;
            ?>" selected>#<?php 
            echo  $p ;
            ?></option>
								<?php 
        }
        ?>
							</select>
						</div>	
					</div>
					<?php 
        /*
        					<div class="row form-group no-border">
        						<div class="col-sm-4">
        							<p class="m0">Content Optimizer Timespan</p>
        							<small class="text-off">Select time range to consider, when displaying content optimizer values.</small>
        						</div>
        						<div class="col-sm-8">
        							<span>
        								<input class="form-control wsko-ajax-input" type="number" name="content_optimizer_time" value="<?=WSKO_Class_Core::get_setting('content_optimizer_time')?>" data-wsko-target="settings" data-wsko-setting="content_optimizer_time" placeholder="Default: 27 (days)">
        							</span>
        						</div>
        					</div>			
        					
        					<div class="row form-group">
        						<div class="col-sm-3">
        							<p class="m0">Onpage Analysis - Interval</p>
        							<small class="text-off">Set the interval in days after which the Onpage Analysis should be regenerated.<br/>[1-30, default:7]</small>
        						</div>
        						<div class="col-sm-9">
        							<div class="row wsko-settings-include-post-types" style="overflow-y:auto;max-height:150px;margin:20px 0px;">
        								<div class="range-field">
        									<input type="range" class="wsko-ajax-input" type="number" value="<?=$onpage_analysis_interval?>" data-wsko-target="settings" data-wsko-setting="onpage_analysis_interval" min="1" max="30"/>
        									<p class="text"><?=$onpage_analysis_interval?></p>
        								</div>
        								<!--div class="wsko-ajax-input wsko-ajax-slider" type="number" value="<?=$onpage_analysis_interval?>" data-wsko-target="settings" data-wsko-setting="onpage_analysis_interval" data-min="1" data-max="30"></div-->
        							</div>
        						</div>
        					</div> */
        ?>
					<?php 
        ?></div>
				<a href="#wsko_onpage_advanced_conf_collapse" class="button" data-toggle="collapse"  style="display:inline-block;"><?php 
        echo  __( 'Advanced Settings', 'wsko' ) ;
        ?></a><?php 
    }
    
    $id = WSKO_Class_Helper::get_unique_id( 'wsko_onpage_crawl_remove_' );
    ?>
			<div id="<?php 
    echo  $id ;
    ?>" style="display:inline-block;">
				<?php 
    if ( (WSKO_Class_Core::is_configured() || !WSKO_Class_Core::get_option( 'start_with_onpage_crawl' )) && !wp_next_scheduled( 'wsko_onpage_analysis' ) ) {
        WSKO_Class_Template::render_ajax_button(
            __( 'Start Onpage Crawl', 'wsko' ),
            'refresh_analysis',
            array(),
            array(
            'remove'    => '#' . $id,
            'class'     => 'button-primary',
            'no_reload' => !WSKO_Class_Core::is_configured(),
        )
        );
    }
    ?>
			</div>
	<?php 
    if ( !$widget ) {
        ?>
		</div>
	</div>
	<?php 
    }
} else {
    WSKO_Class_Template::render_notification( 'error', array(
        'msg' => wsko_loc( 'notif', 'no_admin_api' ),
    ) );
}
