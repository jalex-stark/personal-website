<?php
if (!defined('ABSPATH')) exit;

$breadcrumb = isset($template_args['breadcrumb']) ? $template_args['breadcrumb'] : '';
?>
<div class="wsko-modal wsko-help-wrapper" style="display:none;">
    <div class="wsko-modal-backdrop wsko-toggle-help"></div>
    <div class="bsu-panel panel panel-default wsko-help-inner">
        <section class="wsko-help-header">
            <a class="wsko-toggle-help pull-right dark" style="z-index: 9; position: relative; cursor:pointer;"><i class="fa fa-times"></i></a>
            <div class="text-off wsko-uppercase">
                <i style="display:inline-block" class="fa fa-info-circle wsko-fa-help"></i>
                <div style="display:inline-block">
                    <h4><?=__('Help', 'wsko')?> • <?=__('BAVOKO SEO Tools', 'wsko')?></h4> 
                    <small id="wsko_help_breadcrumb"><?=$breadcrumb?></small>
                </div>    
            </div>    
        </section>
        <section class="wsko-help-content">
            <div id="wsko_admin_view_help_wrapper"></div>
        </section>
        <section class="wsko-help-footer">
            <a class="wsko-inline-block btn btn-link btn-sm wsko-toggle-help" href="#" onclick="event.preventDefault(); window.wsko_open_controller_tour();"><?=__('Tour', 'wsko')?></a>
            <?=WSKO_Class_Template::render_page_link(WSKO_Controller_Knowledge::get_instance(), '', __('Knowledge Base', 'wsko'), array('class' => 'btn btn-flat btn-sm wsko-toggle-help'), true);?>
            <a class="wsko-give-feedback wsko-toggle-help wsko-inline-block btn btn-link btn-sm"><?=__('Support', 'wsko')?></a>
            <?php /*
            <a class="wsko-toggle-help wsko-inline-block btn btn-link btn-sm" href="<?=WSKO_CONTACT_URL?>"><?=__('Contact page', 'wsko')?></a>
            <a class="wsko-toggle-help wsko-inline-block btn btn-link btn-sm" href="#" onclick="tidioChatApi.open();"><?=__('Live Chart', 'wsko')?></a>
            <br/>
            <small class="text-off">Or contact us at <a href="mailto:<?=WSKO_SUPPORT_MAIL?>"><?=WSKO_SUPPORT_MAIL?></a></small>
            */ ?>
    </section>
    </div>
</div>    