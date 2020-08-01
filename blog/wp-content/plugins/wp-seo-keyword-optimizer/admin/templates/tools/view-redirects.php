<?php
if (!defined('ABSPATH')) exit;

$table_redirects = isset($template_args['redirects']) && $template_args['redirects'] ? $template_args['redirects'] : array();
$table_page_redirects = isset($template_args['page_redirects']) && $template_args['page_redirects'] ? $template_args['page_redirects'] : array();
$table_auto_redirects = isset($template_args['auto_redirects']) && $template_args['auto_redirects'] ? $template_args['auto_redirects'] : array();
?>

<div class="p15" style="padding-bottom:0px;">
	<ul class="bsu-tabs nav nav-tabs">
		<li class="active"><a href="#wsko_redirects_general" data-toggle="tab"><?=__('Custom Redirects', 'wsko')?> (<?=count($table_redirects)?>)</a></li>
		<li><a href="#wsko_redirects_pages" data-toggle="tab"><?=__('Content Optimizer Redirects', 'wsko')?> (<?=count($table_page_redirects)?>)</a></li>
		<li><a href="#wsko_redirects_auto_redirects" data-toggle="tab"><?=__('Automatic Redirects', 'wsko')?> (<?=count($table_auto_redirects)?>)</a></li>
	</ul>
</div>	
<div class="tab-content">
	<div id="wsko_redirects_general" class="tab-pane fade in active">
		<?php WSKO_Class_Template::render_table(array(__('Comp', 'wsko'), __('Page', 'wsko'), __('Type', 'wsko'), __('Comp to', 'wsko'), __('Redirect to', 'wsko'), __('Options', 'wsko')), $table_redirects, array());?>
	</div>
	<div id="wsko_redirects_pages" class="tab-pane fade">
		<?php WSKO_Class_Template::render_table(array(__('Page', 'wsko'), __('Type', 'wsko'), __('Redirect to', 'wsko'), __('Options', 'wsko')), $table_page_redirects, array());?>
	</div>
	<div id="wsko_redirects_auto_redirects" class="tab-pane fade">
		<?php WSKO_Class_Template::render_table(array(__('Post/Post Type', 'wsko'), __('Type', 'wsko'), __('Redirects', 'wsko'), __('Options', 'wsko')), $table_auto_redirects, array());?>
	</div>
</div>