<?php 
if (!defined('ABSPATH')) exit;

$report_key = isset($template_args['report_key']) ? $template_args['report_key'] : false;
$report = isset($template_args['report']) ? $template_args['report'] : false;
$pdf = isset($template_args['pdf']) && $template_args['pdf'] ? true : false;
$pdf_data = isset($template_args['pdf_data']) ? $template_args['pdf_data'] : false;
if ($report)
{
    $structure = WSKO_Class_Reporting::get_report_structure();
    $custom_structure = isset($report['structure']) ? $report['structure'] : false;
    ?><html <?=$pdf && !is_array($pdf_data) ? 'style="overflow:hidden"' : '' ?>>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <?php if (is_array($pdf_data)) { ?>
                <style><?=file_get_contents(WSKO_PLUGIN_URL.'includes/bootstrap/css/bootstrap.min.css')?></style>
                <style><?=file_get_contents(WSKO_PLUGIN_URL.'includes/font-awesome/css/font-awesome.min.css')?></style>
                <style><?=file_get_contents(WSKO_PLUGIN_URL.'admin/css/core.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css')?></style>
                <style><?=file_get_contents(WSKO_PLUGIN_URL.'admin/css/admin.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css')?></style>
                <style><?=file_get_contents(WSKO_PLUGIN_URL.'admin/css/charts.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css')?></style>
                <style><?=file_get_contents(WSKO_PLUGIN_URL.'admin/css/pdf.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css')?></style>
            <?php } else { ?>
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'includes/bootstrap/css/bootstrap.min.css' ?>">
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'includes/font-awesome/css/font-awesome.min.css' ?>">
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'admin/css/core.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>">
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'admin/css/admin.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>">
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'admin/css/charts.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>">
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'admin/css/pdf.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>">
                <link rel="stylesheet" href="<?=WSKO_PLUGIN_URL.'admin/css/pdf-html.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>">
                <script src="<?=includes_url('/js/jquery/jquery.js')?>"></script>
                <script src="https://www.gstatic.com/charts/loader.js"></script>
                <script src="<?=WSKO_PLUGIN_URL.'admin/js/misc.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>"></script>
                <script src="<?=WSKO_PLUGIN_URL.'admin/js/core.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>"></script>
                <script src="<?=WSKO_PLUGIN_URL.'admin/js/admin.'.(WSKO_Class_Helper::is_dev()?'':'min.').'css' ?>"></script>

                <?php if (!is_array($pdf_data)) { ?>
                <script type="text/javascript">
                window.wsko_generate_chart_pdfs = function(chart, elem){
                    elem.closest('.wsko-pdf-gen-chart').addClass('wsko-chart-ready').data('chart-data', chart.getImageURI());
                };
                var wsko_report_sent = false;
                jQuery(document).ready(function($){
                    if (!(typeof google === 'undefined') && !(typeof google.charts === 'undefined'))
                        google.charts.load('current', {'packages':['corechart', 'geochart'], 'mapsApiKey': '<?=WSKO_GOOGLE_MAPS_API_KEY?>'});
                    setInterval(() => {
                        if (!wsko_report_sent)
                        {
                            $('.wsko-pdf-gen-chart:not(.wsko-chart-ready)').each(function(index){
                                if (!$(this).find('.wsko-chart-outer-wrapper').length)
                                {
                                    $(this).addClass('wsko-chart-ready').data('chart-data', '');
                                }
                            });
                            if ($('.wsko-pdf-gen-chart:not(.wsko-chart-ready)').length == 0)
                            {
                                wsko_report_sent = true;
                                $('.wsko-pdf-gen-chart').each(function(index){
                                    var $this = $(this);
                                    $('#wsko_pdf_generator_form_wrapper').append('<input type="hidden" name="pdf_data['+$this.data('segment')+']['+$this.data('item')+']" value="'+$(this).data('chart-data')+'">');
                                });
                                $('#wsko_pdf_generator_form_wrapper').submit();
                            }
                        }
                    }, 1000);
                });
                </script>
                <?php }
            } ?>
        </head>
        <body><?php
            if (!$pdf || is_array($pdf_data))
            {
                $title = 'BST Report '.date('d.m.Y');
                $white_label = WSKO_Class_Core::get_white_label();
                ?><?php
                if ($pdf) 
                {
                    if (WSKO_Class_Core::get_setting('report_add_header')) { ?>
                    <header>
                        <img class="wsko-pdf-header-logo" src="<?=$white_label?>">
                        <div class="wsko-pdf-header-text">
                            <b style="font-size:16px"><?=$title?></b><br/>
                            <small class="wsko-text-off"><?=sprintf(__('for %s', 'wsko'), WSKO_Class_Helper::get_host_base())?></small><br/>
                            <small class="wsko-text-off"><?=sprintf(__('created at %s', 'wsko'), date('H:i'))?></small>
                        </div>
                    </header><?php
                    }
                    if (WSKO_Class_Core::get_setting('report_add_footer')) { ?>
                    <footer>
                        <div class="wsko-pdf-footer-text">
                            <?=$title?>
                        </div>
                        <?php if ($pdf) { ?>
                        <script type="text/php">
                            if (isset($pdf))
                            {
                                $font = $fontMetrics->get_font("helvetica");//, "bold");
                                $size = 8;
                                $y = $pdf->get_height() - 18;
                                $x = $pdf->get_width() - 10 - $fontMetrics->get_text_width('1 / 1', $font, $size);
                                $text = "{PAGE_NUM} / {PAGE_COUNT}";
                                $color = array(255,255,255);
                                $pdf->page_text($x, $y, $text, $font, $size, $color);
                            }
                        </script>
                        <?php } ?>
                    </footer><?php
                    }
                }
                else
                {
                    ?><div class="wsko-pdf-html-header-placeholder"></div>
                    <div class="wsko-pdf-html-header">
                        <img class="wsko-pdf-html-logo" src="<?=$white_label?>"/>
                        <h1><?=sprintf(__('SEO Report from %s', 'wsko'), date('d.m.Y', $report['created']))?></h1>
                        <a href="<?=WSKO_Class_Reporting::get_report_url($report_key, true)?>" style="float:right;"><i class="fa fa-download"></i> <?=__('Generate PDF', 'wsko')?></a>
                    </div><?php
                } ?>
                <main>
                    <?php if ($pdf && WSKO_Class_Core::get_setting('report_add_frontpage')) { ?>
                    <div class="wsko-pdf-frontpage">
                        <img class="wsko-pdf-frontpage-logo" src="<?=$white_label?>">
                        <h1><?=__('BST Report', 'wsko')?></h1>
                        <b><?=WSKO_Class_Helper::get_host_base()?></b>
                        <small><?=date('d.m.Y H:i')?></small>
                    </div><?php 
                    }
                    if ($custom_structure)
                    {
                        foreach ($custom_structure as $struct_key => $struct)
                        {
                            ?><div class="wsko-pdf-static-page">
                                <div class="row">
                                    <div class="col-md-12"><?php
                                        WSKO_Class_Reporting::get_report_view($struct_key);
                                    ?></div>
                                <?php
                                    
                                    foreach ($struct as $item_key => $item)
                                    {
                                        if (isset($structure[$struct_key]['items'][$item_key]))
                                        {
                                            $has_special = false;
                                            ?><div class="col-md-12">
                                                <div class="wsko-reporting-item-wrapper">
                                                    <h3><?=$structure[$struct_key]['items'][$item_key]['title']?></h3>
                                                    <div class="wsko-reporting-item-inner">    
                                                    <?php if ($pdf)
                                                        {
                                                            if ($structure[$struct_key]['items'][$item_key]['type'] == 'chart')
                                                            {
                                                                ?><img width="100%" src="<?=isset($pdf_data[$struct_key][$item_key]) ? esc_attr($pdf_data[$struct_key][$item_key]) : ''?>" alt="Chart"><?php
                                                                $has_special = true; 
                                                            }
                                                        }
                                                        if (!$has_special)
                                                            echo $report['data'][$struct_key][$item_key];
                                            ?></div></div></div><?php
                                        }
                                    }
                                ?>
                                </div>
                            </div><?php
                        }
                    }
                    else
                    {
                        WSKO_Class_Template::render_notification('error', array('msg' => __('Report is not configured', 'wsko')));
                    } ?>
                </main><?php
            }
            else
            {
                ?>
                    <form id="wsko_pdf_generator_form_wrapper" url="<?=WSKO_Class_Reporting::get_report_url($report_key, true)?>" method="POST">
                    </form>
                    <div id="wsko_pdf_generator_charts_wrapper"><?php
                    if ($custom_structure)
                    {
                        foreach ($custom_structure as $struct_key => $struct)
                        {
                            foreach ($struct as $item_key => $item)
                            {
                                if (isset($structure[$struct_key]['items'][$item_key]['type']) && $structure[$struct_key]['items'][$item_key]['type'] == 'chart')
                                {
                                    ?><div class="wsko-pdf-gen-chart" data-segment="<?=$struct_key?>" data-item="<?=$item_key?>"><?php
                                        echo $report['data'][$struct_key][$item_key];
                                    ?></div><?php
                                }
                            }
                        }
                    }
                    ?></div>
                    <div class="wsko-pdf-html-loader">
                        <b><?=__('Your PDF report is being generated...', 'wsko')?></b><br/>
                        <small class="text-off"><?=__('This can take up to a minute.', 'wsko')?></small>
                        <?php echo WSKO_Class_Template::render_wsko_preloader(array('size' => 'big')); ?>
                    </div>
                <?php
            }
    ?></body>
    </html><?php
} ?>