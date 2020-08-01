<?php
if (!defined('ABSPATH')) exit;

$stats = isset($template_args['stats']) ? $template_args['stats'] : false;
?><p><?php _e('Please check your imported data if everything is correct. Specific plugin features may not be imported properly.', 'wsko'); ?></p><?php
?>
<?php if ($stats['title']) { ?>
    <p><?=__('Post Titles:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['title'])?></p>
<?php } ?>
<?php if ($stats['desc']) { ?>
    <p><?=__('Post Descriptions:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['desc'])?></p>
<?php } ?>
<?php if ($stats['robots']) { ?>
    <p><?=__('Post Noindex/Nofollow:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['robots'])?></p>
<?php } ?>
<?php if ($stats['kw']) { ?>
    <p><?=__('Post Focus Keywords:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['kw'])?></p>
<?php } ?>
<?php if ($stats['pt_title']) { ?>
    <p><?=__('Post Type Titles:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['pt_title'])?></p>
<?php } ?>
<?php if ($stats['pt_desc']) { ?>
    <p><?=__('Post Type Descriptions:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['pt_desc'])?></p>
<?php } ?>
<?php if ($stats['pt_robots']) { ?>
    <p><?=__('Post Type Noindex:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['pt_robots'])?></p>
<?php } ?>
<?php if ($stats['tax_title']) { ?>
    <p><?=__('Taxonomy Titles:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['tax_title'])?></p>
<?php } ?>
<?php if ($stats['tax_desc']) { ?>
    <p><?=__('Taxonomy Descriptions:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['tax_desc'])?></p>
<?php } ?>
<?php if ($stats['redirects']) { ?>
    <p><?=__('Redirects:', 'wsko')?> <?=WSKO_Class_Helper::format_number($stats['redirects'])?></p>
<?php } ?>
<?php if (!array_sum($stats)) { 
    WSKO_Class_Template::render_notification('warning', array('msg' => wsko_loc('notif', 'import_empty')));
 } ?>
<a href="#" class="button button-primary wsko-close-modal-inline" style="float:right;"><?=__('Close', 'wsko')?></a>
<div class="clearfix"></div>