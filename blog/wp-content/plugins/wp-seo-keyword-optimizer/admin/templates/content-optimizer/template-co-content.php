<?php
if (!defined('ABSPATH')) exit;

$post_id = isset($template_args['post_id']) ? $template_args['post_id'] : false;
$title = isset($template_args['title']) ? $template_args['title'] : false;
$slug = isset($template_args['slug']) ? $template_args['slug'] : false;
$content = isset($template_args['content']) ? $template_args['content'] : false;
if ($post_id)
{
    $post = get_post($post_id);
    $title = $post->post_title;
    $slug = $post->post_name;
    $content = $post->post_content;
    $edit_by = wp_check_post_lock($post_id);
    if ($edit_by) { 
        WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_co', 'post_lock', array('user' => $edit_by))));
    } else { ?>
        
        <ul class="wsko-nav bsu-tabs bsu-tabs-sm" style="clear:both;">
            <li style="display:grid;float:right"><a href="#" id="wsko_reload_co_content_btn" data-post="<?=$post_id?>"><i class="fa fa-refresh"></i> <?=__('Reload Editor', 'wsko')?></a></li>
            <li><a href="#ext_content_tab_iframe" id="wsko_content_tab_gutenberg" class="wsko-nav-link wsko-nav-link-active"><?=__('Visual Editor', 'wsko')?></a></li>
            <li><a href="#ext_content_tab_editor" id="wsko_content_tab_html" class="wsko-nav-link"><?=__('HTML Editor', 'wsko')?></a></li>
        </ul>    
        <div class="wsko-tab-content">
            <div id="ext_content_tab_editor" class="wsko-tab">
                <div class="wsko-co-update-content">
                    <?=WSKO_Class_Template::render_form(array('type' => 'input', 'title' => __('Post Title', 'wsko'), 'value' => $title, 'class' => 'wsko-co-content-field-title wsko-form-control', 'nonce' => 'wsko_co_save_content', 'fullWidth' => true));?>
                    <?=WSKO_Class_Template::render_form(array('type' => 'input', 'title' => __('Post Slug', 'wsko'), 'value' => $slug, 'class' => 'wsko-co-content-field-slug wsko-form-control', 'nonce' => 'wsko_co_save_content', 'fullWidth' => true));?>
                    <?php /* =WSKO_Class_Template::render_form(array('type' => 'textarea', 'title' => 'Post Content', 'value' => $content, 'class' => 'wsko-co-content-field-content wsko-form-control', 'nonce' => 'wsko_co_save_content', 'rows' => '15', 'fullWidth' => true)); */ ?>
                    <?=WSKO_Class_Template::render_form(array('type' => 'editor', 'title' => __('Post Content', 'wsko'), 'value' => $content, 'class' => 'wsko-co-content-field-content wsko-form-control', 'nonce' => 'wsko_co_save_content', 'rows' => '15', 'fullWidth' => true));?>
                    <?=WSKO_Class_Template::render_form(array('type' => 'submit', 'title' => '', 'class' => 'wsko-co-save-content', 'nonce' => 'wsko_co_save_content', 'data-post' => $post_id, 'fullWidth' => true));?>
                </div>
            </div>
            <div id="ext_content_tab_iframe" class="wsko-tab wsko-tab-active">
                <iframe id="wsko_co_content_iframe" class="wsko-co-content-iframe" src="<?=add_query_arg(array('bst_iframe' => '1'), get_edit_post_link($post_id))?>">
            </div>
        </div>
    <?php }
}
?>