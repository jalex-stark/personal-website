<?php
if (!defined('ABSPATH')) exit;

?><div style="margin: 18px;float:right;" class="wsko-header-meta-links text-off"><?php
WSKO_Class_Template::render_popup_link(__('Crawl Information', 'wsko'), __('Onpage Crawl Information', 'wsko'), WSKO_Class_Template::render_template('onpage/view-crawl-info.php', array(), true));
$controller = WSKO_Class_Core::get_current_controller();
$subpage = $controller->get_current_subpage();
switch ($subpage)
{
    case 'links':
    if (WSKO_Class_Core::is_premium())
    {
        ?> | <?php
        WSKO_Class_Template::render_page_link(WSKO_Controller_Tools::get_instance(), 'linking', __('Internal Linking Manager', 'wsko'), array('button' => false));
    }
    break;
    case 'canon':
    ?> | <?php
    WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), false, __('Canonical Settings', 'wsko'), array('button' => false));
    break;
}
?></div>