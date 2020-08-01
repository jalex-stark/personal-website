<?php
function kb_helpful($atts)
{
	ob_start();
	?>
		<div class="kb-helpful">
			<p><?=__('Was this article helpful?', 'wsko')?></p>
			<a class="btn btn-primary btn-sm" style="color:#fff;"><?=__('Yes', 'wsko')?></a>
			<a class="btn btn-secondary btn-sm" style="color:#fff;"><?=__('No', 'wsko')?></a>
		</div>	
	<?php
	return ob_get_clean();
}
add_shortcode('kb_helpful', 'kb_helpful');
?>