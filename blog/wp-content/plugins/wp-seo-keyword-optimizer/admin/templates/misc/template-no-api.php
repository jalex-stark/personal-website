<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wsko_no_cache_wrapper">
	<i class="fa fa-times fa-no-data-icon" aria-hidden="true"></i><br/>
	<b><?=__('API Error', 'wsko')?></b><br/>
	<?php if (current_user_can('manage_options'))
	{ 
		WSKO_Class_Template::render_page_link(WSKO_Controller_Settings::get_instance(), '#apis', __('Set API access', 'wsko'), array('button' => true));
	} ?>
</div>