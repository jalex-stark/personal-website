<?php
if (!defined('ABSPATH'))
    exit;
    
$isConfig = WSKO_Class_Core::is_configured();
?>
<div class="wsko-container wsko_wrapper wsko-updated-wrapper" style="max-width:1000px; margin:15px auto;">
	<div class="wsko-row" style="margin-top:50px">
		<div class="wsko-col-sm-12" style="text-align:center; margin-bottom:10px;">
			<div style=" margin:20px 0px;"><img class="wsko_logo" src="<?=WSKO_PLUGIN_URL.'admin/img/logo-bl.png'?>" /></div>
			<h2><?=__('WP SEO Keyword Optimizer is now BAVOKO SEO Tools', 'wsko')?></h2>
			<p class="text-off wsko-uppercase"><?=__('The Most Comprehensive All-in-One WordPress SEO Plugin', 'wsko')?></p>			
		</div>
	</div>
	<div class="wsko-row" style="margin:30px 0px">
		<div class="wsko-col-sm-6" style="padding: 20px 40px;">
			<h3 style="font-weight:normal;"><?=__('Extensive data and optimized workflows', 'wsko')?></h3>
			<p><?=__('BAVOKO SEO Tools is the first WordPress SEO plugin that combines both SEO analysis and optimization in just one application.
			With the help of integrated search, onpage, performance, backlink and social media tools, all aspects of your search engine optimization can be easily accessed via your WordPress backend. Thanks to the intelligent architecture of BAVOKO SEO Tools, you can now make changes and optimize your pages in just seconds.', 'wsko')?></p>
		</div>
		<div class="wsko-col-sm-6" style="height:270px;">
			<iframe src="https://www.youtube.com/embed/_s-HcePDwww" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
		</div>
	</div>
	
	<p style="text-align:center; margin-top:50px;">	
		<?php 
		if ($isConfig) {
			WSKO_Class_Template::render_page_link(WSKO_Controller_Dashboard::get_instance(), false, __('Continue to Dashboard', 'wsko'), array('button' => true));
		} else {
			WSKO_Class_Template::render_page_link(WSKO_Controller_Setup::get_instance(), false, __('Continue to Setup', 'wsko'), array('button' => true));
		}	
		?>
	</p>
</div>