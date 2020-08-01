<?php
if (!defined('ABSPATH'))
	exit;

WSKO_Class_Template::render_ajax_button(__('Clear Logs', 'wsko'), 'clear_error_reports', array(), array('alert' => __('Do you really want to delete all your error reports?', 'wsko')));

WSKO_Class_Template::render_table(array(__('Type', 'wsko'), __('Title', 'wsko'), __('Data', 'wsko'), array('name' => __('Date', 'wsko'), 'width' => '10%')), array(), array('ajax' => array('action' => 'wsko_table_settings', 'arg' => 'error_logs'), 'order' => array('col' => 3, 'dir' => 'desc')));
?>