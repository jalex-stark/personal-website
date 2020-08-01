<?php
if (!defined('ABSPATH')) exit;

//WSKO_Class_Template::render_template('misc/template-coming-soon.php', array()); 
//return;

$post_types = WSKO_Class_Helper::get_public_post_types('objects');
$taxonomies = WSKO_Class_Helper::get_public_taxonomies('objects');
?>
<div class="row">
    <div class="col-md-12">
        <div id="wsko_frontend_widget_breadcrumbs" class="bsu-panel panel panel-default" data-nonce="<?=wp_create_nonce('wsko_get_widget_preview')?>">
            <p class="panel-heading m0"><?=__('Breadcrumbs', 'wsko');?></p>
            <div class="panel-body">
            <?php if (WSKO_Class_Core::is_demo()) { ?>
                <div class="row form-group">
                    <div class="col-md-12">
                        <?php WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_tools', 'widgets_demo'))); ?>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row form-group">
                    <div class="col-sm-3">
                        <p class="m0"><?=__('Activate Breadcrumbs', 'wsko')?></p>
                    </div>
                    <div class="col-sm-9">
                        <label>
                            <input class="form-control wsko-switch wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('activate_auto_breadcrumbs') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="activate_auto_breadcrumbs">
                            <?=__('Activate Breadcrumbs', 'wsko')?>
                        </label>
                    </div>
                </div>
                <div class="row form-group wsko-border-bottom">
                    <div class="col-sm-3">
                        <p class="m0">Breadcrumb Position', 'wsko')?></p>
                    </div>
                    <div class="col-sm-9">
                        <div class="row wsko-breadcrumb-type-wrapper">
                            <div class="col-sm-6">
                                <?php $target = WSKO_Class_Core::get_setting('auto_breadcrumb_target'); ?>
                                <select class="form-control wsko-breadcrumb-insert-type wsko-ajax-input" data-wsko-target="settings" data-wsko-setting="auto_breadcrumb_target">
                                    <option value="content_start" <?=!$target || ($target == 'content_start') ? 'selected' : ''?>><?=__('Before Content', 'wsko')?></option>
                                    <option value="custom" <?=$target == 'custom' ? 'selected' : ''?>><?=__('Custom Element', 'wsko')?></option>
                                    <?php /*
                                    <option value="" <?=!$target ? 'selected' : ''?>>Before Header</option>
                                    <option value="content_end" <?=$target == 'content_end' ? 'selected' : ''?>>After Content</option>
                                    <option value="footer" <?=$target == 'footer' ? 'selected' : ''?>>Before Footer</option>
                                    */ ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <div class="wsko-breadcrumb-insert-custom-wrapper">
                                    <input class="form-control wsko-breadcrumb-insert-custom wsko-ajax-input" type="text" readonly value="<?=WSKO_Class_Core::get_setting('auto_breadcrumb_target_custom'); ?>" data-wsko-target="settings" data-wsko-setting="auto_breadcrumb_target_custom">
                                    <small class="text-off"><?=__('Choose a custom jQuery selector in your browsers developer tools to append the breadcrumbs to a specific html element (e. g. \'.entry-title\', \'#custom_id\')', 'wsko')?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-7">
                        <div class="row form-group">     
                            <div class="col-sm-3">
                                <label><?=__('Separator', 'wsko')?></label>
                            </div>    
                            <div class="col-sm-9">
                                <input class="form-control wsko-ajax-input" type="text" placeholder=">" value="<?=WSKO_Class_Core::get_setting('breadcrumb_separator')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_separator">
                            </div>
                        </div>
                        <div class="row form-group"> 
                            <div class="col-sm-3">
                                <label><?=__('Homepage Format', 'wsko')?></label>
                            </div>
                            <div class="col-sm-9">
                                <input class="form-control wsko-ajax-input" type="text" placeholder="<?=__('Homepage', 'wsko')?>" value="<?=WSKO_Class_Core::get_setting('breadcrumb_home_format')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_home_format">
                            </div>
                        </div>
                        <div class="row form-group">         
                            <div class="col-sm-3">
                                <label><?=__('Breadcrumb Format', 'wsko')?></label>
                                <small class="text-off"><?=__('Breadcrumb Format for Posts, Taxonomies and Archives. You can use the markups shown in the placeholders or any from your dynamic metas (e.g. %post:post_title% from Tools > Metas)', 'wsko')?></small>
                            </div>
                            <div class="col-sm-9">    
                                <input style="margin-bottom:10px;" class="form-control wsko-ajax-input" type="text" placeholder="<?=__('%title% (Posts)', 'wsko')?>" value="<?=WSKO_Class_Core::get_setting('breadcrumb_post_format')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_post_format">
                                <input style="margin-bottom:10px;" class="form-control wsko-ajax-input" type="text" placeholder="<?=__('%tax%: %term% (Taxonomies)', 'wsko')?>" value="<?=WSKO_Class_Core::get_setting('breadcrumb_tax_format')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_tax_format">
                                <input class="form-control wsko-ajax-input" type="text" placeholder="<?=__('Archive for %title% (Archives)', 'wsko')?>" value="<?=WSKO_Class_Core::get_setting('breadcrumb_archive_format')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_archive_format">
                            </div>
                        </div>  

                        <div class="row form-group">
                            <div class="col-sm-3">
                                <label><?=__('Prefix & Suffix', 'wsko')?></label>
                            </div>    
                            <div class="col-sm-9">     
                                <div class="row">   
                                    <div class="col-sm-6">
                                        <input class="form-control wsko-ajax-input" type="text" placeholder="<?=__('Prefix', 'wsko')?>" value="<?=WSKO_Class_Core::get_setting('breadcrumb_prefix')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_prefix">
                                    </div>
                                    <div class="col-sm-6">
                                        <input class="form-control wsko-ajax-input" type="text" placeholder="<?=__('Suffix', 'wsko')?>" value="<?=WSKO_Class_Core::get_setting('breadcrumb_suffix')?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_suffix">
                                    </div>
                                </div>    
                            </div>        
                        </div> 

                        <div class="row form-group">
                            <div class="col-sm-3">
                                <p class="m0"><?=__('Post Taxonomy Relation', 'wsko')?></p>
                                <small class="text-off"><?=__('Set taxonomy breadcrumbs for your post types.', 'wsko')?></small>
                            </div>
                            <div class="col-sm-9">
                                <div class="row wsko-breadcrumbs-post-type-settings" style="overflow-y:auto;max-height:150px;">
                                <?php 
                                $post_tax_rel = WSKO_Class_Core::get_setting('breadcrumb_post_tax_relations');
                                if (!$post_tax_rel)
                                    $post_tax_rel = array();
                                foreach ($post_types as $pt)
                                {
                                    $tax_sel = isset($post_tax_rel[$pt->name]) ? $post_tax_rel[$pt->name] : false;
                                    ?><div class="col-md-3">
                                        <label><?=$pt->label?> <p class="settings-sub font-unimportant" style="display:inline-block!important;">(<?=$pt->name?>)</p></label>
                                        <select class="form-control wsko-ajax-input" data-key="<?=$pt->name?>" data-wsko-target="settings" data-wsko-setting="breadcrumb_post_tax_relations" data-multi-parent=".wsko-breadcrumbs-post-type-settings">
                                            <option value="">None', 'wsko')?></option>
                                            <?php foreach ($taxonomies as $tax)
                                            {
                                                ?><option value="<?=$tax->name?>" <?=$tax_sel == $tax->name ? 'selected' : ''?>><?=$tax->label?></option><?php
                                            } ?>
                                        </select>
                                    </div><?php
                                }
                                ?>
                                </div>
                            </div>
                        </div>

                        <div class="wsko-collapse-wrapper">
                            <a href="#" class="wsko-collapse button" style="margin-bottom:15px;"><?=__('Advanced Settings', 'wsko')?></a>
                            <div class="wsko-collapse-content" style="max-height:0px;"> 
                                <div class="row form-group">
                                    <div class="col-sm-3 col-xs-12">
                                        <label><?=__('Exclude Post Types', 'wsko')?></label>
                                    </div>
                                    <div class="col-sm-9 col-xs-12">    
                                        <div class="row wsko-breadcrumbs-post-type-exclude" style="overflow-y:auto;max-height:150px;">
                                        <?php 
                                        $excluded = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('breadcrumb_exclude_posts'));
                                        foreach ($post_types as $pt)
                                        {
                                            ?><div class="col-md-3">
                                                <label><input class="form-control wsko-ajax-input" type="checkbox" <?=in_array($pt->name, $excluded) ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="breadcrumb_exclude_posts" data-multi-parent=".wsko-breadcrumbs-post-type-exclude" value="<?=$pt->name?>"><?=$pt->label?> <span class="settings-sub font-unimportant"><?=$pt->name?></span></label>
                                            </div><?php
                                        }
                                        ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row form-group">
                                    <div class="col-sm-3 col-xs-12">
                                        <label><?=__('Exclude Special Pages', 'wsko')?></label>
                                    </div>
                                    <div class="col-sm-9 col-xs-12">    

                                        <input class="form-control wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('activate_auto_breadcrumbs_all') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="activate_auto_breadcrumbs_all">
                                        <?=__('Add to every other page (Taxonomy pages, archives,...)', 'wsko')?>
                                    </div>    
                                </div>
                                
                                <div class="row form-group">
                                    <div class="col-sm-3">
                                        <p class="m0"><?=__('Last Breadcrumb', 'wsko')?></p>
                                        <small class="text-off"><?=__('Choose whether and how to display the last breadcrumb (current post).', 'wsko')?></small>
                                    </div>
                                    <div class="col-sm-9">
                                        <?php $last_link = WSKO_Class_Core::get_setting('breadcrumb_last_mode'); ?>
                                        <select class="form-control wsko-ajax-input" data-wsko-target="settings" data-wsko-setting="breadcrumb_last_mode">
                                            <option value="" <?=!$last_link ? 'selected' : ''?>><?=__('Show Text', 'wsko')?></option>
                                            <option value="show_link" <?=$last_link == 'show_link' ? 'selected' : ''?>><?=__('Show Link', 'wsko')?></option>
                                            <option value="hide" <?=$last_link == 'hide' ? 'selected' : ''?>><?=__('Hide', 'wsko')?></option>
                                        </select>
                                        <small class="text-off"><?=__('Shortcode attribute: "link_last"', 'wsko')?></small>
                                    </div>
                                </div>

                            </div>
                        </div> 
                    </div>
                    <div class="col-sm-5">
                        <ul class="nav nav-tabs bsu-tabs">
                            <li class="active"><a data-toggle="tab" href="#wsko_widgets_bc_preview_tab"><?=__('Preview', 'wsko')?></a></li>
                            <li><a data-toggle="tab" href="#wsko_widgets_bc_structure_tab"><?=__('Structure', 'wsko')?></a></li>
                            <li><a data-toggle="tab" href="#wsko_widgets_bc_shortcode_tab"><?=__('Shortcode', 'wsko')?></a></li>
                        </ul>

                        <div class="tab-content">
                            <div id="wsko_widgets_bc_preview_tab" class="tab-pane fade in active">
                                <div id="wsko_breadcrumbs_preview">
                                </div>
                            </div>
                            <div id="wsko_widgets_bc_structure_tab" class="tab-pane fade">
                                <?=WSKO_Class_Template::render_widget_structure('breadcrumbs')?>
                            </div>
                            <div id="wsko_widgets_bc_shortcode_tab" class="tab-pane fade">
                                <?php WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_tools', 'widgets_breadcrumb_shortcode'),
                                        'list' => array(
                                            'separator <small class="text-off">(char string)</small>',
                                            'home_format <small class="text-off">(markup string)</small>',
                                            'tax_format <small class="text-off">(markup string)</small>',
                                            'archive_format <small class="text-off">(markup string)</small>',
                                            'post_format <small class="text-off">(markup string)</small>',
                                            'prefix <small class="text-off">(string)</small>',
                                            'suffix <small class="text-off">(string)</small>',
                                            'link_last <small class="text-off">(show_text, show_link or hide)</small>'
                                    ))); ?>
                            </div>
                           
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>    

        <div id="wsko_frontend_widget_content_table" class="bsu-panel panel panel-default" data-nonce="<?=wp_create_nonce('wsko_get_widget_preview')?>">
            <p class="panel-heading m0"><?=__('Table of Contents', 'wsko')?></p>
            <div class="panel-body">
                <?php if (WSKO_Class_Core::is_demo()) { ?>
                    <div class="row form-group">
                        <div class="col-md-12">
                            <?php WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_tools', 'widgets_demo'))); ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Activate Widget', 'wsko')?></p>
                                    <small class="text-off"><?=__('Add \'Table of Contents\' widget in posts', 'wsko')?></small>
                                </div>
                                <div class="col-sm-9">
                                    <label>
                                        <input class="form-control wsko-ajax-input wsko-switch" type="checkbox" <?=WSKO_Class_Core::get_setting('activate_auto_content_table') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="activate_auto_content_table">
                                        <?=__('Activate \'Table of Contents\'', 'wsko')?>
                                    </label>
                                </div>
                            </div>
                            <div class="wsko-separator"></div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Heading', 'wsko')?></p>
                                    <small class="text-off"><?=__('The heading displayed above the Table of Contents', 'wsko')?></small>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control wsko-ajax-input" data-trigger="no-event" value="<?=WSKO_Class_Core::has_setting('content_table_heading') ? WSKO_Class_Core::get_setting('content_table_heading') : ''?>" data-wsko-target="settings" data-wsko-setting="content_table_heading" />
                                    <small class="text-off"><?=__('Use %h1% to insert the content of the first h1 tag', 'wsko')?></small>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Append H1', 'wsko')?></p>
                                    <small class="text-off"><?=__('Append the H1 to the Table of Contents', 'wsko')?></small>
                                </div>
                                <div class="col-sm-9">
                                    <label><input type="checkbox" class="form-control wsko-switch wsko-ajax-input" data-trigger="no-event" <?=WSKO_Class_Core::get_setting('content_table_append_h1') ? 'checked' : ''?> data-wsko-target="settings" data-wsko-setting="content_table_append_h1" /></label>
                                </div>
                            </div>
                            <div class="wsko-separator"></div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('List Type', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <?php $list_type = WSKO_Class_Core::get_setting('content_table_list_type'); ?>
                                    <select class="form-control wsko-ajax-input" data-wsko-target="settings" data-wsko-setting="content_table_type">
                                        <option value="" <?=!$list_type ? 'selected' : ''?>>ol</option>
                                        <option value="ul" <?=$list_type == 'ul' ? 'selected' : ''?>>ul</option>
                                    </select><br/>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Numeration Color', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="wsko-color-picker wsko-ajax-input" data-trigger="no-event" value="<?=WSKO_Class_Core::has_setting('content_table_num_color') ? WSKO_Class_Core::get_setting('content_table_num_color') : '#000'?>" data-wsko-target="settings" data-wsko-setting="content_table_text_color" />
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Text Color', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="wsko-color-picker wsko-ajax-input" data-trigger="no-event" value="<?=WSKO_Class_Core::has_setting('content_table_text_color') ? WSKO_Class_Core::get_setting('content_table_text_color') : '#000'?>" data-wsko-target="settings" data-wsko-setting="content_table_text_color" />
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Background Color', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="wsko-color-picker wsko-ajax-input" data-trigger="no-event" value="<?=WSKO_Class_Core::has_setting('content_table_background_color') ? WSKO_Class_Core::get_setting('content_table_background_color') : '#fff'?>" data-wsko-target="settings" data-wsko-setting="content_table_background_color" />
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Border', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                            <?php $border_type = WSKO_Class_Core::get_setting('content_table_list_type'); ?>
                                            <select class="form-control wsko-ajax-input" data-wsko-target="settings" data-wsko-setting="content_table_border_type">
                                                <option <?=!$border_type ? 'selected' : ''?>><?=__('Solid', 'wsko')?></option>
                                                <option value="dotted" <?=$border_type == 'ul' ? 'selected' : ''?>><?=__('Dotted', 'wsko')?></option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <input placeholder="1" type="text" class="form-control" data-wsko-target="settings" data-wsko-setting="content_table_border_width" value="<?=WSKO_Class_Core::has_setting('content_table_border_width') ? WSKO_Class_Core::get_setting('content_table_border_width') : '1'?>"/><span style="position: absolute; right: 25px; top: 8px; opacity: .7;">px</span>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="margin-top: 5px;">
                                            <input class="wsko-color-picker wsko-ajax-input" type="text" value="<?=WSKO_Class_Core::has_setting('content_table_border_color') ? WSKO_Class_Core::get_setting('content_table_border_color') : '#eee'?>" data-wsko-target="settings" data-wsko-setting="content_table_border_color" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-sm-3">
                                    <p class="m0"><?=__('Box Shadow', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                            <input class="form-control wsko-ajax-input wsko-switch" type="checkbox" <?=WSKO_Class_Core::get_setting('content_table_box_shadow') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="content_table_box_shadow">
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <input type="text" class="wsko-color-picker wsko-ajax-input" data-trigger="no-event" value="<?=WSKO_Class_Core::has_setting('content_table_box_shadow_color') ? WSKO_Class_Core::get_setting('content_table_box_shadow_color') : '#eee'?>" data-wsko-target="settings" data-wsko-setting="content_table_box_shadow_color" />
                                        </div>
                                    </div>
                                </div>
                            </div>                     
                            
                            <p>
                                <a class="button" data-toggle="collapse" href="#toc_advanced" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"><?=__('Advanced Settings', 'wsko')?></a>
                            </p>
                            <div class="row">
                            <div class="col col-sm-12">
                                <div class="collapse multi-collapse" id="toc_advanced">
                                    <div class="row form-group">
                                        <div class="col-sm-3">
                                            <p class="m0"><?=__('Exclude Post Types', 'wsko')?></p>
                                            <small class="text-off"><?=__('Exclude specific Post Types from auto insertion.', 'wsko')?></small>
                                        </div>
                                        <div class="col-sm-9">
                                            <div class="row wsko-content-table-post-type-exclude" style="overflow-y:auto;max-height:150px;">
                                            <?php 
                                            $excluded = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('content_table_exclude_posts'));
                                            foreach ($post_types as $pt)
                                            {
                                                ?><div class="col-md-3">
                                                    <label><input class="form-control wsko-ajax-input" type="checkbox" <?=in_array($pt->name, $excluded) ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="content_table_exclude_posts" data-multi-parent=".wsko-content-table-post-type-exclude" value="<?=$pt->name?>"><?=$pt->label?> <span class="settings-sub font-unimportant"><?=$pt->name?></span></label>
                                                </div><?php
                                            }
                                            ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-sm-3">
                                            <p class="m0"><?=__('Generation Overrides', 'wsko')?></p>
                                            <small class="text-off"><?=__('By default the container from which the headings are fetched and where the table is prepended, is the HTML parent tag of the Post Content/Shortcode Location. If you need to customize this behaviour, choose a jQuery selector to identify your container.', 'wsko')?></small>
                                        </div>
                                        <div class="col-sm-9">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <input class="form-control wsko-ajax-input" value="<?=WSKO_Class_Core::get_setting('content_table_post_source')?>" data-wsko-target="settings" data-wsko-setting="content_table_post_source" type="text" placeholder="<?=__('Post content container', 'wsko')?>">
                                                    <small class="text-off"><?=__('Choose a Target (jQuery selector) to get headings from', 'wsko')?></small><br/>
                                                </div>
                                                <div class="col-sm-6">
                                                    <input class="form-control wsko-ajax-input" value="<?=WSKO_Class_Core::get_setting('content_table_post_target')?>" data-wsko-target="settings" data-wsko-setting="content_table_post_target" type="text" placeholder="<?=__('Table of Contents target', 'wsko')?>">
                                                    <small class="text-off"><?=__('Choose a Target (jQuery selector) to prepend the table to', 'wsko')?></small><br/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-sm-3">
                                            <p class="m0"><?=__('Level Separator', 'wsko')?></p>
                                            <small class="text-off"><?=__('Choose a charachter to separate sublevels.', 'wsko')?></small>
                                        </div>
                                        <div class="col-sm-9">
                                            <input class="form-control wsko-ajax-input" value="<?=WSKO_Class_Core::get_setting('content_table_separator')?>" data-wsko-target="settings" data-wsko-setting="content_table_separator" placeholder="<?=__('Default: \'.\'', 'wsko')?>">
                                            <label>
                                                <input class="form-control wsko-ajax-input" type="checkbox" <?=WSKO_Class_Core::get_setting('content_table_separator_end') ? 'checked="checked"' : ''?> data-wsko-target="settings" data-wsko-setting="content_table_separator_end">
                                                <?=__('Add separator at end', 'wsko')?>
                                            </label><br/>
                                            <small class="text-off"><?=__('Numeric: 1., 1.1., 1.1.2.,...', 'wsko')?></small><br/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-sm-5 col-xs-12">
                            <ul class="nav nav-tabs bsu-tabs">
                                <li class="active"><a data-toggle="tab" href="#wsko_widgets_ct_preview_tab"><?=__('Preview', 'wsko')?></a></li>
                                <li><a data-toggle="tab" href="#wsko_widgets_ct_structure_tab"><?=__('Structure', 'wsko')?></a></li>
                                <li><a data-toggle="tab" href="#wsko_widgets_ct_shortcode_tab"><?=__('Shortcode', 'wsko')?></a></li>
                            </ul>

                            <div class="tab-content">
                                <div id="wsko_widgets_ct_preview_tab" class="tab-pane fade in active">
                                    <div id="wsko_content_table_preview">
                                    </div>
                                </div>
                                <div id="wsko_widgets_ct_structure_tab" class="tab-pane fade">
                                    <?=WSKO_Class_Template::render_widget_structure('content_table')?>
                                </div>
                                <div id="wsko_widgets_ct_shortcode_tab" class="tab-pane fade">
                                    <?php WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_tools', 'widgets_table_shortcode'), 
                                            'list' => array(
                                                'list_type <small class="text-off">(ul or ol)</small>',
                                                'num_color <small class="text-off">(color string or hex)</small>',
                                                'text_color <small class="text-off">(color string or hex)</small>',
                                                'backgound_color <small class="text-off">(color string or hex)</small>',
                                                'border_type <small class="text-off">(solid or dotted)</small>',
                                                'border_width <small class="text-off">(pixels or relative value)</small>',
                                                'border_color <small class="text-off">(color string or hex)</small>',
                                                'box_shadow <small class="text-off">(true or false)</small>',
                                                'box_shadow_color <small class="text-off">(color string or hex)</small>',
                                                'source <small class="text-off">(jQuery selector)</small>',
                                                'target <small class="text-off">(jQuery selector)</small>',
                                                'separator <small class="text-off">(char string)</small>',
                                                'separator_end <small class="text-off">(true or false)</small>'
                                            ))); ?>
                                </div>
                            </div>
                        </div>
                    </div>        
                <?php } ?>
            </div>
        </div>    
        <div class="bsu-panel panel panel-default">
            <p class="panel-heading m0"><?=__('Custom Code', 'wsko')?></p>
            <div class="panel-body">
                <?php if (WSKO_Class_Core::is_demo()) { ?>
                    <div class="row form-group">
                        <div class="col-md-12">
                            <?php WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_tools', 'widgets_demo'))); ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <ul class="nav nav-tabs bsu-tabs">
                        <li class="active"><a href="#tab_header_code" data-toggle="tab"><?=__('Header Code', 'wsko') ?></a><li>
                        <li><a href="#tab_footer_code" data-toggle="tab"><?=__('Footer Code', 'wsko') ?></a><li>
                    </ul>
                    <div class="tab-content">
                        <div id="tab_header_code" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-sm-3">    
                                    <p><?=__('Custom CSS (Header)', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">
                                    <textarea id="wsko_widget_header_styles" class="form-control wsko-ajax-input wsko-indent-textarea" rows="10" placeholder="<?=__('Custom CSS (don\'t add <style></style> tag)', 'wsko')?>" data-wsko-target="settings" data-wsko-setting="widgets_custom_style"><?=WSKO_Class_Core::get_setting('widgets_custom_style_head')?></textarea>
                                </div>
                            </div>    
                            <div class="row" style="margin-top:15px">   
                                <div class="col-sm-3">                     
                                    <p><?=__('Custom JS (Header)', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">    
                                    <textarea id="wsko_widget_header_scripts" class="form-control wsko-ajax-input wsko-indent-textarea" rows="10" placeholder="<?=__('Custom JS (don\'t add <script></script> tag)', 'wsko')?>" data-wsko-target="settings" data-wsko-setting="widgets_custom_script"><?=WSKO_Class_Core::get_setting('widgets_custom_script_head')?></textarea>      
                                </div>
                            </div>
                        </div>
                        <div id="tab_footer_code" class="tab-pane fade">
                            <div class="row">
                                <div class="col-sm-3">  
                                    <p><?=__('Custom CSS (Footer)', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">    
                                    <textarea class="form-control wsko-ajax-input wsko-indent-textarea" rows="10" placeholder="<?=__('Custom CSS (don\'t add <style></style> tag)', 'wsko')?>" data-wsko-target="settings" data-wsko-setting="widgets_custom_style"><?=WSKO_Class_Core::get_setting('widgets_custom_style_foot')?></textarea>
                                </div>
                            </div>
                            <div class="row" style="margin-top:15px">
                                <div class="col-sm-3">  
                                    <p><?=__('Custom JS (footer)', 'wsko')?></p>
                                </div>
                                <div class="col-sm-9">     
                                    <textarea class="form-control wsko-ajax-input wsko-indent-textarea" rows="10" placeholder="<?=__('Custom JS (don\'t add <script></script> tag)', 'wsko')?>" data-wsko-target="settings" data-wsko-setting="widgets_custom_script"><?=WSKO_Class_Core::get_setting('widgets_custom_script_foot')?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>   
                <?php } ?>
            </div>
        </div>
    </div>
</div>