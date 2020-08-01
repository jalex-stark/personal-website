<?php
if (!defined('ABSPATH')) exit;
$post_frame = isset($template_args['post_frame']) ? $template_args['post_frame'] : false;
?>
<div id="bst_co_iframe" class="bst-co-iframe-modal" <?=$post_frame?'data-post-frame="'.$post_frame.'"':''?>>
    <div class="bst-co-iframe-modal-backdrop"></div>
    <div class="bst-co-iframe-modal-wrapper">
        <div class="bst-co-iframe-modal-content"></div>
        <div class="bst-co-iframe-modal-loading">
            <?php echo WSKO_Class_Template::render_bst_preloader(array('size' => 'big')); ?>
        </div>
        <a class="bst-co-iframe-modal-close" href="#"></a>
    </div>
</div>