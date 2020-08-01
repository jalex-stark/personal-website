<?php if (!defined('ABSPATH')) exit;
$controller = isset($template_args['controller']) ? $template_args['controller'] : false;
$subpage = isset($template_args['subpage']) ? $template_args['subpage'] : false;

if (!WSKO_Class_Core::is_premium()) { ?>
    <a class="wsko-pro-modal-link btn-wsko-header btn btn-primary pull-right" href=""><?=__('Be a Pro!', 'wsko')?></a>
<?php }
?>