<?php
if (!defined('ABSPATH')) exit;

$critical = isset($template_args['critical']) ? $template_args['critical'] : false;

if ($critical)
{
    WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_onpage', 'onpage_prem_crit_error', array('error' => $critical)), 'subnote' => wsko_loc('notif_onpage', 'onpage_prem_crit_error_sub')));
}
else
{
    WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_onpage', 'onpage_prem_crit_queries'), 'subnote' => wsko_loc('notif_onpage', 'onpage_prem_crit_queries_sub')));
}
?>