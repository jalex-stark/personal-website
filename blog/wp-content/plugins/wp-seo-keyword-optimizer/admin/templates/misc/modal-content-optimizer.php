<?php
if (!defined('ABSPATH')) exit;
?>
<div id="wsko_content_optimizer_modal" class="wsko-modal wsko_wrapper">
	<div class="wsko-modal-backdrop"></div>
	<div class="wsko-modal-box wsko_wrapper" data-name="wsko_content_optimizer">
		<div class="wsko-modal-close"></div>
		<div class="wsko-modal-multi-container-bar" style="display:none">
			<p class="wsko-back-to-multi-wrapper wsko-m0-important"><a href="#" class="wsko-modal-back-to-multi"><i class="fa fa-angle-left fa-fw"></i> <?=__('Back to list', 'wsko')?></a></p>
		</div>
		<div class="wsko-modal-multi-container" style="display:none"></div>
			<div class="wsko-modal-loader">
			  <div class="loader">
				<svg class="circular" viewBox="25 25 50 50">
				  <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
				</svg>
			  </div>
			</div>
		<div class="wsko-modal-content"></div>
	</div>
</div>