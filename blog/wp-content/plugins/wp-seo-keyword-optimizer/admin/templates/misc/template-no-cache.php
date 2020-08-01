<?php
if (!defined('ABSPATH'))
	exit;
$api = isset($template_args['api']) ? $template_args['api'] : false;
?>
<div class="wsko_no_cache_wrapper">
<?php if (current_user_can('manage_options'))
{
	?><i class="fa fa-retweet fa-no-data-icon" aria-hidden="true"></i><br/>
	<span><?=__('Dataset empty.', 'wsko')?> <?=$api ? '<br/>'.WSKO_Class_Template::render_recache_api_button($api, array(), true) : (__('Try updating your cache under the Settings. ', 'wsko').WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), '#apis', __('View Settings', 'wsko'), array('button' => false)).'</span>')?><?php
}
else
{
	?>
	<i class="fa fa-times fa-no-data-icon" aria-hidden="true"></i><br/>
	<span class="wsko-text-off wsko-small"><?=__('No Data available', 'wsko')?></span>
	<?php
} ?>
</div>