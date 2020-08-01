<?php
if (!defined('ABSPATH')) exit;

$post_id = isset($template_args['post_id']) ? $template_args['post_id'] : false;

if ($post_id)
{
    $link = get_permalink($post_id);

    $post_redirect = WSKO_Class_Onpage::get_page_redirect($post_id);
    $gen_params = WSKO_Class_Onpage::get_sitemap_params();
    $excluded_op = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('onpage_exclude_posts'));

    $sitemap_excluded = false;
    $onpage_excluded = false;
    if (isset($gen_params['excluded_posts']) && is_array($gen_params['excluded_posts']) && $gen_params['excluded_posts'])
        $sitemap_excluded = in_array($post_id, $gen_params['excluded_posts']);
    if ($excluded_op)
        $onpage_excluded = in_array($post_id, $excluded_op);
    ?>
    <ul class="wsko-nav bsu-tabs bsu-tabs-sm">
        <li><a class="wsko-nav-link wsko-nav-link-active" href="#wsko_tab_technical_settings">Settings</a></li>		
        <li><a class="wsko-nav-link" href="#wsko_tab_technical_redirects">Redirects</a></li>
    </ul>

    <div class="wsko-tab-content">
        <div id="wsko_tab_technical_settings" class="wsko-tab wsko-meta-tab wsko-tab-active">
            <div class="form-group wsko-row">
                <div class="wsko-col-sm-3 wsko-col-xs-12">
                    <p><?=__('Canonical Tag', 'wsko')?></p>
                </div>
                <div class="wsko-col-sm-9 wsko-col-xs-12">
                    <?php WSKO_Class_Template::render_template('tools/template-metas-view.php', array('meta_view' => 'canonical', 'post_id' => $post_id, 'type' => 'post_id')); ?>
                </div>	
            </div>	
            <?php if (WSKO_Class_Onpage::seo_plugins_disabled()) { ?>
            <div class="wsko-border wsko-mb10 wsko-mt10"></div>
            <div class="form-group wsko-row">
                <div class="wsko-col-sm-3 wsko-col-xs-12">
                    <p><?=__('Sitemap Generation', 'wsko')?></p>
                </div>
                <div class="wsko-col-sm-9 wsko-col-xs-12">
                    <label><input class="form-control wsko-ajax-input" type="checkbox" <?=$sitemap_excluded ? 'checked="checked"' : ''?> data-wsko-target="onpage_sitemap_post_exclude" data-wsko-setting="<?=$post_id?>"> <?=__('Exclude from Sitemap', 'wsko')?></label>
                </div>	
            </div>
            <?php } ?>
            <div class="wsko-border wsko-mb10 wsko-mt10"></div>
            <div class="form-group wsko-row">
                <div class="wsko-col-sm-3 wsko-col-xs-12">
                    <p><?=__('Onpage Analysis', 'wsko')?></p>
                </div>
                <div class="wsko-col-sm-9 wsko-col-xs-12">
                    <label><input class="form-control wsko-ajax-input" type="checkbox" <?=$onpage_excluded ? 'checked="checked"' : ''?> data-wsko-target="onpage_analysis_post_exclude" data-wsko-setting="<?=$post_id?>"> <?=__('Exclude from Onpage Analysis', 'wsko')?></label>
                </div>	
            </div>
        </div>
        
        <div id="wsko_tab_technical_redirects" class="wsko-tab wsko-meta-tab">
            <div class="wsko-co-technical-wrapper" data-post="<?=$post_id?>" data-nonce="<?=wp_create_nonce('wsko_co_save_technicals')?>">
                <div class="form-group wsko-row">
                    <div class="wsko-col-sm-3 wsko-col-xs-12">						
                        <p><?=__('Redirect To', 'wsko')?></p>
                    </div>
                    <div class="wsko-col-sm-9 wsko-col-xs-12">
                        <p><label><input class="wsko-co-redirect-activate wsko-form-control" type="checkbox" <?=$post_redirect ? 'checked' : ''?>> <?=__('Activate Redirect', 'wsko')?></label></p>
                        <input class="wsko-co-redirect-to wsko-form-control wsko-mb10" type="text" placeholder="/relative/link/ or https://external-domain.com/path " value="<?=isset($post_redirect['to']) ? $post_redirect['to'] : ''?>">
                        <select class="wsko-co-redirect-type wsko-form-control wsko-mb10">
                            <option value="1" <?=isset($post_redirect['type']) && $post_redirect['type'] == '301' ? 'selected' :''?>><?=__('301 (Permanent Redirect, SEO fiendly)', 'wsko')?></option>
                            <option value="2" <?=isset($post_redirect['type']) && $post_redirect['type'] == '302' ? 'selected' :''?>><?=__('302 (Temporary Redirect)', 'wsko')?></option>
                        </select>
                    </div>	
                </div>
                
                <div class="wsko-border wsko-mb10 wsko-mt10"></div>
                
                <div class="form-group wsko-row wsko-co-redirect-from-wrapper">
                    <div class="wsko-col-sm-3 wsko-col-xs-12">
                        <p><?=__('Redirects from this post', 'wsko')?></p>
                        <small class="wsko-text-off"><?=__('Lists redirect rules that match this post\'s url.', 'wsko')?><?php WSKO_Class_Template::render_page_link(WSKO_Controller_Tools::get_instance(), "links", __('View all rules', 'wsko'), array('button' => false), false)?></small>
                    </div>
                    <div class="wsko-col-sm-9 wsko-col-xs-12">									
                        <div class="wsko-row">
                            <div class="wsko-col-sm-12 wsko-col-xs-12">	
                                <?php WSKO_Class_Template::render_ajax_beacon('wsko_check_redirect', array('post' => array('url' => $link, 'status_check' => false))); ?>
                            </div>
                        </div>
                    </div>
                </div>			
                
                <div class="wsko-border wsko-mb10 wsko-mt10"></div>
                
                <div class="form-group wsko-row">
                    <div class="wsko-col-sm-3 wsko-col-xs-12">
                        <p><?=__('Auto Post Redirects', 'wsko')?></p>
                        <small class="wsko-text-off"><?=__('Lists automatic redirects from the posts old urls to its current url.', 'wsko')?><?php WSKO_Class_Template::render_page_link(WSKO_Controller_Tools::get_instance(), "links", __('View all rules', 'wsko'), array('button' => false), false)?></small>
                    </div>
                    <div class="wsko-col-sm-9 wsko-col-xs-12">	
                        <?php $redirects = WSKO_Class_Onpage::get_auto_redirects('post_id');
                        if ($redirects && isset($redirects[$post_id]))
                        {
                            $redirects = $redirects[$post_id];
                            if ($redirects)
                            {
                                $reds = array();
                                foreach($redirects as $key => $red)
                                {
                                    $reds[] = '<i>'.$red.'</i> '.(current_user_can('manage_options') ? WSKO_Class_Template::render_ajax_button('<i class="fa fa-times"></i>', 'remove_auto_redirect', array('type' => 'post_id', 'arg' => $post_id, 'key' => $key), array(), true) : '');
                                }
                                WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_co', 'post_redirects'), 'list' => $reds, 'notif-class' => 'wsko-post-redirects'));
                            } 
                            else
                            {
                                WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_co', 'post_redirects_empty')));
                            }
                        }
                        else {
                            WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_co', 'post_redirects_empty')));
                        }										
                        ?>
                    </div>
                </div>			
            </div>
        </div>
    </div>
<?php } ?>