<?php
if (!defined('ABSPATH')) exit;

$link_data = isset($template_args['link_data']) ? $template_args['link_data'] : false;
$ext = isset($template_args['ext']) ? $template_args['ext'] : false;
$rec = isset($template_args['rec']) ? $template_args['rec'] : false;

if ($link_data)
{
    if ($ext)
        $links = $link_data->ext_links;
    else if ($rec)
        $links = $link_data->rec_links;
    else
        $links = $link_data->links;
    $links_table = array();
    foreach ($links as $link)
    {
        $links_table[] = array(
            array('order' => $link->u, 'value' => $ext ? $link->u : WSKO_Class_Template::render_url_post_field_s($link->u, array(), true)),
            array('order' => $link->f+$link->nf, 'value' => ($link->f?WSKO_Class_Helper::format_number($link->f).' follow':'').(($link->f && $link->nf)?' & ':'').($link->nf?WSKO_Class_Helper::format_number($link->nf).' nofollow':'')),
        );
    }
    
    WSKO_Class_Template::render_table(array(__('URL', 'wsko'), __('Count', 'wsko')), $links_table, array('order' => array('col' => 1, 'dir' => 'desc')), false);}
else
{
    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_tools', 'linking_no_data')));
}