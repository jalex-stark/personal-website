<?php
if (!defined('ABSPATH')) exit;

$controller = WSKO_Class_Core::get_current_controller();
$is_admin = current_user_can('manage_options');
	
$star = "<i class='fa fa-star'></i>";
if (!$controller->uses_fixed_timespan)
{
	?><form class="wsko-admin-timespan-picker-form">
		<div class="wsko-admin-timespan-picker pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ddd; margin:12px 15px;">
			<!--i class="fa fa-spinner fa-pulse wsko-loader"></i>&nbsp;-->
			<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
			<span class="wsko-timespan-label" style="width:280px"></span> <b class="caret"></b>
			<input class="wsko-start-time" name="start_time" type="hidden" value="<?=$controller->timespan_start?>">
			<input class="wsko-end-time" name="end_time" type="hidden" value="<?=$controller->timespan_end?>">
			<i class="fa fa-info-circle wsko_info" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="<?=sprintf(__('Note: Today and the past two days (%s, %s and %s) are not accessible due to limitations with the APIs.', 'wsko'), date("d.m.y", time() - 60 * 60 * 24 * 1), date("d.m.y", time() - 60 * 60 * 24), date("d.m.y"))?>"></i>
		</div>
	</form><?php
} ?>