<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wsko-knowledge-base-wrapper">
	<?php WSKO_Class_Template::render_lazy_field('kb_articles', 'big', 'center'); ?>
	<div class="wsko-kb-loading-overlay" style="display:none;position:absolute;top:0px;left:0px;width:100%;height:100%;">
		<div style="position:absolute;top:0px;left:0px;width:100%;height:100%;background-color:white;opacity:0.7;">
		</div>
		<?php WSKO_Class_Template::render_preloader(array('size' => 'big')); ?>
	</div>
</div>