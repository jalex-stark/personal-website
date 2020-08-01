
<?php
if (!defined('ABSPATH')) exit;

$contr = WSKO_Class_Core::get_current_controller();
$subpage = isset($template_args['subpage']) ? $template_args['subpage'] : false;

if ($subpage === 'competitors')
{
    $competitors = WSKO_Class_Premium::get_competitors(true);
    if (!empty($competitors))
    {
        ?>
    <div style="margin: 18px;float: right;">
        <span class="text-off" style="margin:0px;"><?=__('Last Update:', 'wsko')?> <?=date('d.m.Y', $competitors['updated'])?></span>
        <span class="text-off" style="margin:0px;"> <?=__('| Next Update:', 'wsko')?> <?=date('d.m.Y', $competitors['expire'])?></span>
    </div>
    <?php }
} ?>

<?php /*
<a target="_blank" style="float:right;margin:-7px 15px;" href="<?=WSKO_Class_Search::get_external_link('tool_fetch', WSKO_Class_Helper::get_host_base(true), '')?>">Fetch as Googlebot</a>
*/ ?>