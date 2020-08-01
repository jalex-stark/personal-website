<?php
if (!defined('ABSPATH')) exit;

$ga_token_se = WSKO_Class_Search::get_se_token();
$ga_config_se = WSKO_Class_Search::get_se_property();

if ($ga_token_se && $ga_config_se)
{
    if (!WSKO_Class_Core::get_option('search_query_first_run'))
        WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_search', 'first_report'), 'subnote' => wsko_loc('notif_search', 'first_report_sub', array('curr' => intval(WSKO_Class_Core::get_option('search_query_first_run_step')), 'all' => WSKO_SEARCH_INITIAL_REPORTS))));
    else if (WSKO_Class_Core::get_option('search_query_running'))
        WSKO_Class_Template::render_notification('info', array('msg' => wsko_loc('notif_search', 'updating')));
    else //default
        WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif_search', 'pending')));
}
else
{
	WSKO_Class_Template::render_no_data_view(array('elem' => 'search,bavoko', 'collapsed' => true));	
}
?>