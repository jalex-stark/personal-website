<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( current_user_can( 'manage_options' ) ) {
    global  $wp_roles ;
    $roles = $wp_roles->get_names();
    $post_types = WSKO_Class_Helper::get_public_post_types( 'objects' );
    $lang = WSKO_Class_Core::get_plugin_language( true );
    $backups = WSKO_Class_Backup::get_backups();
    $onpage_analysis_interval = WSKO_Class_Core::get_setting( 'onpage_analysis_interval' );
    if ( !$onpage_analysis_interval ) {
        $onpage_analysis_interval = 7;
    }
    ?>
	<div class="row wsko-settings-wrapper">
		<div class="col-md-12">
		
			<ul class="nav nav-tabs bsu-tabs border-dark">
				<li class="active"><a id="tab_settings_link" href="#tab_settings" data-toggle="tab"><?php 
    echo  __( 'General', 'wsko' ) ;
    ?></a><li>
				<li><a id="tab_advanced_link" href="#tab_advanced" data-toggle="tab"><?php 
    echo  __( 'Advanced', 'wsko' ) ;
    ?></a><li>
				<?php 
    ?>
				<li><a id="tab_apis_link" href="#tab_apis" data-toggle="tab"><?php 
    echo  __( 'API Settings', 'wsko' ) ;
    ?></a><li>
				<?php 
    
    if ( WSKO_Class_Core::get_setting( 'activate_log' ) ) {
        ?><li><a href="#tab_debug" data-toggle="tab"><?php 
        echo  __( 'Error Reports', 'wsko' ) ;
        ?></a><li><?php 
    }
    
    ?>
			</ul>
		
			<div class="wsko_box_wrapper">
				<div class="tab-content">
					<div id="tab_settings" class="tab-pane fade in active">				
						<div class="row">
							<!-- panel content optimizer -->
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Content Optimizer Settings', 'wsko' ) ;
    ?></p>
									<div class="panel-body">
										<!--div class="row form-group">
											<div class="col-sm-3">
												<p class="m0">Activate Content Optimizer</p>
												<small class="text-off">Show the Content Optimizer Widget on every post edit page.</small>
											</div>
											<div class="col-sm-9">
												<label>
													<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_setting( 'activate_content_optimizer' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="settings" data-wsko-setting="activate_content_optimizer">
												</label>
											</div>
										</div-->
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Activate Content Optimizer for Post Types', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Activate Content Optimizer widget on post type\'s single post edit pages.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<div class="row wsko-settings-co-include-post-types" style="overflow-y:auto;max-height:150px;">
													<?php 
    //$excluded = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_optimizer_post_types_exclude'));
    $included = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'content_optimizer_post_types_include' ) );
    foreach ( $post_types as $pt ) {
        ?><div class="col-md-3">
															<label><input class="form-control wsko-ajax-input" type="checkbox" <?php 
        echo  ( in_array( $pt->name, $included ) ? 'checked="checked"' : '' ) ;
        ?> data-wsko-target="settings" data-wsko-setting="content_optimizer_post_types_include" data-multi-parent=".wsko-settings-co-include-post-types" value="<?php 
        echo  $pt->name ;
        ?>"><?php 
        echo  $pt->label ;
        ?> <span class="settings-sub font-unimportant"><?php 
        echo  $pt->name ;
        ?></span></label>
														</div><?php 
    }
    ?>
												</div>
												<?php 
    WSKO_Class_Template::render_notification( 'warning', array(
        'msg' => wsko_loc( 'notif_settings', 'co_h1' ),
    ) );
    ?>
											</div>
										</div>
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Stick Content Optimizer', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Stick Content Optimizer widget on post edit pages over the content field. This settings is ignored when using a custom post builder.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
													<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( !WSKO_Class_Core::get_user_setting( 'unstick_content_optimizer' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="user_settings" data-wsko-setting="unstick_content_optimizer">
												</label>
											</div>
										</div>
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Meta Autosave', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Mimics the old meta & social snippet save system (with a timeout). For example: When editing the post meta title your changes will be saved after a 2 seconds, when you click out of the textbox.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
													<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_user_setting( 'metas_view_autosave' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="user_settings" data-wsko-setting="metas_view_autosave">
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
								
							<!-- panel search -->
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Search Settings', 'wsko' ) ;
    ?></p>
									<div class="panel-body">
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Cache Limit (Days to keep data rows in cache)', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  sprintf( __( 'Select the time range to cache for the \'Search\'-section. Normally the last %d days are fetched and after that every new day is added to the cache. Uncached days are not visible/calculatable.', 'wsko' ), WSKO_SEARCH_REPORT_SIZE ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<span>
													<input class="form-control wsko-ajax-input" type="number" name="cache_time_limit" value="<?php 
    echo  WSKO_Class_Core::get_setting( 'cache_time_limit' ) ;
    ?>" data-wsko-target="settings" data-wsko-setting="cache_time_limit" min="90" placeholder="<?php 
    echo  __( 'Default: Infinite (Min.: 90)', 'wsko' ) ;
    ?>">
												</span>
											</div>
										</div>
									</div>					
								</div>
							</div>						
								
							<!-- panel onpage -->
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Onpage Settings', 'wsko' ) ;
    ?></p>
									<div class="panel-body">
										<?php 
    WSKO_Class_Template::render_template( 'onpage/template-analysis-config.php', array(
        'widget' => true,
    ) );
    ?>
									</div>	
								</div>	
							</div>	
										
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Tools Settings', 'wsko' ) ;
    ?></p>
									<div class="panel-body">
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Unlock .htaccess Editor', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Activate saving for the Onpage .htaccess editor', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
												<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_setting( 'activate_editor_htaccess' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="settings" data-wsko-setting="activate_editor_htaccess">
												<?php 
    echo  __( 'Unlock Editor', 'wsko' ) ;
    ?>
												</label>
												<?php 
    WSKO_Class_Template::render_notification( 'error', array(
        'msg' => wsko_loc( 'notif_settings', 'htaccess_editor' ),
    ) );
    ?>
											</div>
										</div>

										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Unlock robots.txt Editor', 'wsko' ) ;
    ?>Unlock robots.txt Editor</p>
												<small class="text-off"><?php 
    echo  __( 'Activate saving for the Onpage robots.txt editor', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
												<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_setting( 'activate_editor_robots' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="settings" data-wsko-setting="activate_editor_robots">
												<?php 
    echo  __( 'Unlock Editor', 'wsko' ) ;
    ?>
												</label>
												<?php 
    WSKO_Class_Template::render_notification( 'error', array(
        'msg' => wsko_loc( 'notif_settings', 'robots_editor' ),
    ) );
    ?>
											</div>
										</div>
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Automatic Social Snippet', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'If activated, empty social meta snippets will automatically be filled with the corresponding Google Meta Tags, or (in lower priority) data from your post.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
													<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_setting( 'auto_social_snippet' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="settings" data-wsko-setting="auto_social_snippet">
													<?php 
    echo  __( 'Activate automatic Snippets', 'wsko' ) ;
    ?>
												</label>
											</div>
										</div>
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Set \'Featured Image\' as preview image', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Set the posts \'featured image\' as the preview image for your social metas. If you have disabled Auto-Snippets this will only affect posts with a Facebook or Twitter meta set.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
													<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_setting( 'auto_social_thumbnail' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="settings" data-wsko-setting="auto_social_thumbnail">
													<?php 
    echo  __( 'Set Thumbnail as Preview', 'wsko' ) ;
    ?>
												</label>
											</div>
										</div>
									</div>					
								</div>
							</div>
							
							<?php 
    /*
    <!-- panel social -->
    <div class="bsu-panel col-sm-12 col-xs-12">
    	<div class="panel panel-default">
    		<p class="panel-heading m0"><?=__('Social Settings', 'wsko');?></p>
    		<div class="panel-body">								
    			<div class="row form-group">
    				<div class="col-sm-3">
    					<p class="m0">Automatic Social Snippet</p>
    					<small class="text-off">If activated, BST will automatically fill every empty social meta with your corresponding Google Meta, or (in lower priority) data from your post.</small>
    				</div>
    				<div class="col-sm-9">
    					<label>
    						<input class="form-control wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('auto_social_snippet') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="auto_social_snippet">
    						Activate automatic Snippets
    					</label>
    				</div>
    			</div>
    			<div class="row form-group">
    				<div class="col-sm-3">
    					<p class="m0">Set Post Thumbnail as preview image</p>
    					<small class="text-off">Set your Post Thumbnail as the preview image for your social metas. If you have disabled Auto-Snippets this will only affect posts with a Facebook or Twitter meta set.</small>
    				</div>
    				<div class="col-sm-9">
    					<label>
    						<input class="form-control wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('auto_social_thumbnail') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="auto_social_thumbnail">
    						Set Thumbnail as Preview
    					</label>
    				</div>
    			</div>
    		</div>	
    	</div>
    </div>	
    */
    ?>		
						</div>	
					</div>
					
					<div id="tab_advanced" class="tab-pane fade">
						<div class="row">
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Advanced Settings', 'wsko' ) ;
    ?></p>
									
									<div class="panel-body">
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Plugin Language', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Force a specific lanuage or let WordPress decide.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<select class="selectpicker wsko-ajax-input form-control" data-wsko-target="settings" data-wsko-setting="plugin_lang" data-reload-real="true">
													<option value="auto" <?php 
    echo  ( !$lang || $lang == 'auto' ? 'selected' : '' ) ;
    ?>> <?php 
    echo  __( 'Auto', 'wsko' ) ;
    ?></option>
													<option value="en_EN" <?php 
    echo  ( $lang == 'en_EN' ? 'selected' : '' ) ;
    ?> data-content="<img class='mr5' src='<?php 
    echo  WSKO_PLUGIN_URL ;
    ?>includes/famfamfam_flags/us.png'> English"></option>
													<option value="de_DE" <?php 
    echo  ( $lang == 'de_DE' ? 'selected' : '' ) ;
    ?> data-content="<img class='mr5' src='<?php 
    echo  WSKO_PLUGIN_URL ;
    ?>includes/famfamfam_flags/de.png'> German"></option>
												</select>
											</div>
										</div>
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Additional Permissions', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Add user roles to gain access to the plugin functionality. Only Admins are able to edit the settings and interact with the cache.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<p>
													<div class="row wsko-settings-additional-permissions" style="overflow-y:auto;max-height:150px;">
													<?php 
    $additional = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'additional_permissions' ) );
    foreach ( $roles as $role_key => $role ) {
        
        if ( $role_key != 'administrator' ) {
            ?><div class="col-md-3">
																<label><input class="form-control wsko-ajax-input" type="checkbox" <?php 
            echo  ( in_array( $role_key, $additional ) ? 'checked="checked"' : '' ) ;
            ?> data-wsko-target="settings" data-wsko-setting="additional_permissions" data-multi-parent=".wsko-settings-additional-permissions" value="<?php 
            echo  $role_key ;
            ?>"><?php 
            echo  $role ;
            ?> <span class="settings-sub font-unimportant"><?php 
            echo  $role_key ;
            ?></span></label>
															</div><?php 
        }
    
    }
    ?>
													</div>
													<?php 
    WSKO_Class_Template::render_notification( 'info', array(
        'msg' => wsko_loc( 'notif_settings', 'permissions' ),
    ) );
    ?>
												</p>
											</div>
										</div>
					
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Error-Reporting', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Activate the logging view with your current error reports.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<label>
													<input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?php 
    echo  ( WSKO_Class_Core::get_setting( 'activate_log' ) ? 'checked="checked"' : '' ) ;
    ?> data-wsko-target="settings" data-wsko-setting="activate_log" data-reload="true">
													<?php 
    echo  __( 'Show Error Reporting View', 'wsko' ) ;
    ?>
												</label>
											</div>
										</div> 

										<?php 
    /*<div class="row form-group">
    			<div class="col-sm-3">
    				<p class="m0">Non-Latin Content</p>
    				<small class="text-off">If your content is written in non-latin characters the "Page Length" Attribute in your Content Optimizer may show a wrong number. Warning: This mode may show incorrect data with latin characters.</small>
    			</div>
    			<div class="col-sm-9">
    				<label>
    					<input class="form-control wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('non_latin') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="non_latin">
    					Use Non-Latin Mode
    				</label>
    			</div>
    		</div> */
    ?>
										
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Clear Session Cache', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'If you have seemingly false data, try deleting the Cache and revisit the corresponding page.', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<?php 
    WSKO_Class_Template::render_ajax_button(
        __( 'Clear Session Cache', 'wsko' ),
        'clear_session_cache',
        array(),
        array()
    );
    ?>
											</div>
										</div>
									</div>
								</div>
							</div>		
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Import from other Plugins', 'wsko' ) ;
    ?></p>
									
									<div class="panel-body">
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Import from other SEO Plugins', 'wsko' ) ;
    ?></p>
											</div>
											<div class="col-sm-9">
												<?php 
    WSKO_Class_Template::render_template( 'settings/view-import.php', array() );
    ?>														
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Backup plugin configuration', 'wsko' ) ;
    ?></p>
									
									<div class="panel-body">
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Backup Interval', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'How often should a backup be created? 1 - every day, 2 - every 2 days, 3 - every 3 days,...', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<input class="form-control wsko-ajax-input" type="number" placeholder="<?php 
    echo  __( 'Default: 1', 'wsko' ) ;
    ?>" value="<?php 
    echo  WSKO_Class_Core::get_setting( 'conf_backup_interval' ) ;
    ?>" data-wsko-target="settings" data-wsko-setting="conf_backup_interval">
											</div>
										</div>
										
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Backup Limit', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'How many automatic backups should be stored', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<input class="form-control wsko-ajax-input" type="number" placeholder="<?php 
    echo  __( 'Default: 7', 'wsko' ) ;
    ?>" value="<?php 
    echo  WSKO_Class_Core::get_setting( 'conf_backup_limit' ) ;
    ?>" data-wsko-target="settings" data-wsko-setting="conf_backup_limit">
											</div>
										</div>
		
										<div class="row form-group">
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Save and restore Backup', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Backup your plugins configuration. Please note: This backup only contains your general configuration (e.g. what you see on the settings page).', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
											<?php 
    if ( WSKO_Class_Core::get_option( 'search_query_running' ) || WSKO_Class_Core::get_option( 'backlink_update_running' ) || WSKO_Class_Core::get_option( 'backlink_update_prem_running' ) || WSKO_Class_Core::get_option( 'onpage_analysis_running' ) || WSKO_Class_Core::get_option( 'onpage_analysis_premium_running' ) ) {
        WSKO_Class_Template::render_notification( 'warning', array(
            'msg'     => wsko_loc( 'notif_settings', 'backup_load_warning' ),
            'subnote' => wsko_loc( 'notif_settings', 'backup_load_warning_sub' ),
        ) );
    }
    ?>
												<a class="button wsko-collapse-toggle-button collapsed" data-toggle="collapse" href="#wsko_backup_collapse" data-parent="#wsko_backup_views_wrapper" role="button" style="display:inline-block;" aria-expanded="false"><?php 
    echo  __( 'Backup Current Configuration', 'wsko' ) ;
    ?></a>
												<a class="button wsko-collapse-toggle-button collapsed" data-toggle="collapse" href="#wsko_import_backup_collapse" data-parent="#wsko_backup_views_wrapper" role="button" style="display:inline-block;" aria-expanded="false"><?php 
    echo  __( 'Import Backup', 'wsko' ) ;
    ?></a>
												<?php 
    if ( $backups ) {
        WSKO_Class_Template::render_ajax_button(
            __( 'Delete Backups', 'wsko' ),
            'delete_conf_backups',
            array(),
            array(
            'class' => '',
            'alert' => __( 'Do you really want to delete all of your backup files?', 'wsko' ),
        )
        );
    }
    ?>
												
												<div id="wsko_backup_views_wrapper">
													<div class="bsu-panel">
														<div class="collapse wsko-import-collapse-wrapper" id="wsko_backup_collapse">
															<div class="row wsko-import-backup-wrapper">
																<div class="col-sm-12">
																	<p>Choose what to backup:</p>
																</div>	
																<div class="col-md-12">
																	<label><input class="form-control wsko-backup-settings" data-multi-source="true" type="checkbox" checked name="targets" value="general" readonly disabled>General <?php 
    WSKO_Class_Template::render_infoTooltip( __( "The general plugin configuration will allways be included in the backup", 'wsko' ), 'info' );
    ?></label><br/>
																	<label><input class="form-control wsko-backup-settings" data-multi-source="true" type="checkbox" checked name="targets" value="redirects"><?php 
    echo  __( 'Redirects', 'wsko' ) ;
    ?> <?php 
    WSKO_Class_Template::render_infoTooltip( __( "Add your custom and automatic redirect rules to this backup", 'wsko' ), 'info' );
    ?></label><br/>
																	<label><input class="form-control wsko-backup-settings" data-multi-source="true" type="checkbox" checked name="targets" value="metas"><?php 
    echo  __( 'Metas', 'wsko' ) ;
    ?> <?php 
    WSKO_Class_Template::render_infoTooltip( __( "Add your post metas to this backup", 'wsko' ), 'info' );
    ?></label>
																</div>
																<div class="col-md-12 wsko-mt10">
																	<?php 
    WSKO_Class_Template::render_ajax_button(
        __( 'Create', 'wsko' ),
        'backup_configuration',
        array(),
        array(
        'class'   => 'button-primary',
        'sources' => '#wsko_backup_collapse .wsko-backup-settings',
    )
    );
    ?>
																	<a class="button" data-toggle="collapse" style="display:inline-block;" href="#wsko_backup_collapse" data-parent="#wsko_backup_views_wrapper" role="button" aria-expanded="false"><?php 
    echo  __( 'Cancel', 'wsko' ) ;
    ?></a>
																</div>
															</div>
														</div>	
													</div>

													<div class="bsu-panel">
														<div class="collapse wsko-import-collapse-wrapper" id="wsko_import_backup_collapse">
															<div class="row wsko-import-backup-wrapper">
																<div class="col-sm-12">
																	<p><?php 
    echo  __( 'Import configuration backup file:', 'wsko' ) ;
    ?></p>
																</div>	
																<div class="col-md-12">
																	<input type="file" placeholder="<?php 
    echo  __( 'Select .bst_backup file', 'wsko' ) ;
    ?>" accept=".bst_backup" class="wsko-import-backup-file form-control">
																</div>
																<div class="col-md-12 wsko-mt10">
																	<a class="button wsko-import-backup button-primary" href="#" data-nonce="<?php 
    echo  wp_create_nonce( 'wsko_import_configuration_backup' ) ;
    ?>"><?php 
    echo  __( 'Import', 'wsko' ) ;
    ?></a>
																	<a class="button" data-toggle="collapse" style="display:inline-block;" href="#wsko_import_backup_collapse" data-parent="#wsko_backup_views_wrapper" role="button" aria-expanded="false"><?php 
    echo  __( 'Cancel', 'wsko' ) ;
    ?></a>
																</div>
															</div>
														</div>		
													</div>
												</div>
												<ul class="wsko-backup-list">
													<?php 
    
    if ( $backups ) {
        $backups = array_reverse( $backups, true );
        ?>
														<p class="small text-off"><?php 
        echo  __( 'CURRENT BACKUPS', 'wsko' ) ;
        ?></p>
														<?php 
        foreach ( $backups as $k => $b ) {
            $targets = array();
            if ( isset( $b['data'] ) ) {
                $targets[] = 'General';
            }
            if ( isset( $b['data_me'] ) ) {
                $targets[] = 'Metas';
            }
            if ( isset( $b['data_re'] ) ) {
                $targets[] = 'Redirects';
            }
            
            if ( $b['auto'] ) {
                ?><li>
																	<span class="pull-right"><?php 
                WSKO_Class_Template::render_ajax_button(
                    __( 'Restore', 'wsko' ),
                    'load_configuration_backup',
                    array(
                    'key' => $k,
                ),
                    array(
                    'alert' => __( 'Are you sure? This will reset all your settings, including your metas.', 'wsko' ),
                )
                );
                ?> <a class="button" href="<?php 
                echo  WSKO_Controller_Download::get_download_link( 'backup', $k ) ;
                ?>" target="_blank"><i class="fa fa-download"></i></a> <?php 
                WSKO_Class_Template::render_ajax_button(
                    '<i class="fa fa-times"></i>',
                    'delete_configuration_backup',
                    array(
                    'key' => $k,
                ),
                    array(
                    'class' => 'wsko-button-danger',
                    'alert' => __( 'Are you sure you want to delete this backup?', 'wsko' ),
                )
                );
                ?></span>
																	<p>
																		<?php 
                echo  sprintf( __( 'Auto-Backup for \'%s\' (created at \'%s\')', 'wsko' ), date( 'd.m.Y', $b['time'] ), date( 'H:i', $b['time'] ) ) ;
                ?><br/>
																		<small class="text-off"><?php 
                echo  implode( ' | ', $targets ) ;
                ?></small>
																	</p>
																</li><?php 
            } else {
                ?><li>
																	<span class="pull-right"><?php 
                WSKO_Class_Template::render_ajax_button(
                    __( 'Restore', 'wsko' ),
                    'load_configuration_backup',
                    array(
                    'key' => $k,
                ),
                    array(
                    'alert' => __( 'Are you sure? This will reset all your settings, including your metas.', 'wsko' ),
                )
                );
                ?> <a class="button" href="<?php 
                echo  WSKO_Controller_Download::get_download_link( 'backup', $k ) ;
                ?>" target="_blank"><i class="fa fa-download"></i></a> <?php 
                WSKO_Class_Template::render_ajax_button(
                    '<i class="fa fa-times"></i>',
                    'delete_configuration_backup',
                    array(
                    'key' => $k,
                ),
                    array(
                    'class' => 'wsko-button-danger',
                    'alert' => __( 'Are you sure you want to delete this backup?', 'wsko' ),
                )
                );
                ?></span>
																	<p>
																		<?php 
                echo  sprintf( __( 'Backup from \'%s\'', 'wsko' ), date( 'd.m.Y H:i', $b['time'] ) ) ;
                ?><br/>
																		<small class="text-off"><?php 
                echo  implode( ' | ', $targets ) ;
                ?></small>
																	</p>
																</li><?php 
            }
        
        }
    } else {
        ?><li><?php 
        echo  __( 'No Backup found.', 'wsko' ) ;
        ?></li><?php 
    }
    
    ?>
												</ul>
											</div>	
										</div>
									</div>
								</div>
							</div>
							
							<div class="bsu-panel col-sm-12 col-xs-12">
								<div class="panel panel-default">
									<p class="panel-heading m0"><?php 
    echo  __( 'Reset BAVOKO SEO Tools', 'wsko' ) ;
    ?></p>
									<div class="panel-body">
										<div class="row">	
											<div class="col-sm-3">
												<p class="m0"><?php 
    echo  __( 'Reset BAVOKO SEO Tools to factory default', 'wsko' ) ;
    ?></p>
												<small class="text-off"><?php 
    echo  __( 'Do you really want to reset BAVOKO SEO Tools? This will delete your entire configuration and reset the plugin to it\'s unconfigured state. An automatic backup of your config will be created unless you tick "Delete Configuration Backups".', 'wsko' ) ;
    ?></small>
											</div>
											<div class="col-sm-9">
												<!-- Reset -->				
												<ul>
													<li><input class="form-control" type="checkbox" id="wsko_reset_opt_metas"><?php 
    echo  __( 'Delete Post & Term Metas', 'wsko' ) ;
    ?> <?php 
    WSKO_Class_Template::render_infoTooltip( __( 'Removes all post/term data (meta title, SEO keywords, canonicals, etc.)', 'wsko' ), 'info' );
    ?></li>
													<li><input class="form-control" type="checkbox" id="wsko_reset_opt_redirects"><?php 
    echo  __( 'Delete Redirects', 'wsko' ) ;
    ?> <?php 
    WSKO_Class_Template::render_infoTooltip( __( 'Removes all custom and automatic redirects', 'wsko' ), 'info' );
    ?></li>
													<li><input class="form-control" type="checkbox" id="wsko_reset_opt_cache"><?php 
    echo  __( 'Delete Database Cache', 'wsko' ) ;
    ?> <?php 
    WSKO_Class_Template::render_infoTooltip( __( 'Removes all caching databases and coresponding data', 'wsko' ), 'info' );
    ?></li>
													<li><input class="form-control" type="checkbox" id="wsko_reset_opt_backups"><?php 
    echo  __( 'Delete Configuration Backups', 'wsko' ) ;
    ?> <?php 
    WSKO_Class_Template::render_infoTooltip( __( "Don't create a backup and delete all backup files", 'wsko' ), 'info' );
    ?></li>
												</ul>
												<br/>
												<div class="row">
													<div class="col-md-6">
														<a id="wsko_reset_configuration" class="button wsko-button-danger" style="margin-top:10px;" data-nonce="<?php 
    echo  wp_create_nonce( 'wsko_reset_configuration' ) ;
    ?>"> <?php 
    echo  __( 'Reset Configuration', 'wsko' ) ;
    ?></a>
													</div>
												</div>
											</div>	
										</div>	
									</div>	
								</div>	
							</div>

						</div>
					</div>
					
					<?php 
    ?>

					<div id="tab_apis" class="tab-pane fade wsko-settings-tab-api">
						<?php 
    WSKO_Class_Template::render_template( 'setup/template-intool-setup-widget.php', array(
        'elem' => 'search,analytics,bavoko',
    ) );
    ?>
					</div>	

					<?php 
    
    if ( WSKO_Class_Core::get_setting( 'activate_log' ) ) {
        ?>
					<div id="tab_debug" class="tab-pane fade">				
						<div class="row">
							<div class="col-sm-12">
								<div class="bsu-panel panel panel-default">
									<p class="panel-heading m0"><?php 
        echo  __( 'Error Log', 'wsko' ) ;
        ?></p>
									<div class="panel-body">
										<?php 
        WSKO_Class_Template::render_template( 'settings/view-debug.php', array() );
        ?>
									</div>
								</div>
							</div>	
						</div>
					</div>	
					<?php 
    }
    
    /*<div id="tab_import" class="tab-pane fade">
    			<h2>Import Plugin Data</h2>
    			<?php WSKO_Class_Template::render_template('settings/view-import.php', array()); ?>
    		</div>
    		
    		<div id="tab_status" class="tab-pane fade">				
    			<div class="row">
    				<div class="col-sm-12">
    					<?php WSKO_Class_Template::render_lazy_field('tab_status', 'big', 'center'); ?>
    				</div>	
    			</div>
    		</div>*/
    ?>
				</div>
			</div>
		</div>
	</div>
	<?php 
    
    if ( isset( $_REQUEST['showtab'] ) && $_REQUEST['showtab'] ) {
        $target = false;
        switch ( $_REQUEST['showtab'] ) {
            case 'apis':
                $target = "#tab_apis_link";
                break;
            case 'advanced':
                $target = "#tab_advanced_link";
                break;
        }
        
        if ( $target ) {
            ?><script type="text/javascript">
			jQuery(document).ready(function($){
				$('<?php 
            echo  $target ;
            ?>').click();
			});
			</script><?php 
        }
    
    }

} else {
    WSKO_Class_Template::render_notification( 'error', array(
        'msg' => wsko_loc( 'notif_settings', 'no_admin' ),
    ) );
}
