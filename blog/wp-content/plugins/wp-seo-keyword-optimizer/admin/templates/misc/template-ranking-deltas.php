<?php 
if (!defined('ABSPATH')) exit;

$type = isset($template_args['type']) ? $template_args['type'] : false;

if (WSKO_Class_Search::check_se_connected())
{
    $install_time = WSKO_Class_Core::get_install_time();
    if ($install_time)
    {
        $ranking_data = WSKO_Class_Search::get_ranking_data($install_time);
        if ($ranking_data)
        {
            $kws = $ranking_data['new']['kws'];
            $kws_ref = $ranking_data['old']['kws'];
            $kws_perc = WSKO_Class_Helper::get_ref_value($kws, $kws_ref);
            $clicks = $ranking_data['new']['clicks'];
            $clicks_ref = $ranking_data['old']['clicks'];
            $clicks_perc = WSKO_Class_Helper::get_ref_value($clicks, $clicks_ref);
            $imps = $ranking_data['new']['impressions'];
            $imps_ref = $ranking_data['old']['impressions'];
            $imps_perc = WSKO_Class_Helper::get_ref_value($imps, $imps_ref);
            /*$pos = $ranking_data['new']['position'];
            $pos_ref = $ranking_data['old']['position'];
            $pos_perc = -WSKO_Class_Helper::get_ref_value($pos, $pos_ref);*/
            
            if ($kws_perc > 20 || $clicks_perc > 20 || $imps_perc > 20)// || $pos_perc > 20)
            {
                if ($type == 'plugin_notice')
                {
                    ?><div style="display:inline-block;outline: solid 1px #ddd;width:100%"><?php
                }
                ?>
                <div class="wsko-ranking-delta">
                    <p style="font-size:14px;font-weight:600;"><?=sprintf(__('Your ranking progress since you installed BAVOKO SEO Tools (%s)', 'wsko'), date('d.m.Y', $install_time))?></p>
                    <ul><?php
                    if ($kws_perc > 20)
                    {
                        ?><li><div class="wsko-user-progress-percent"><?=WSKO_Class_Helper::format_number($kws_perc)?> %</div><div class="wsko-user-progress-meta"><span class="wsko-label-sm"><?=__('Monthly Keywords', 'wsko')?></span><span class="wsko-sublabel"><?=sprintf(__('From %s to %s', 'wsko'),WSKO_Class_Helper::format_number($kws_ref), WSKO_Class_Helper::format_number($kws))?></span></li><?php
                    }
                    if ($clicks_perc > 20)
                    {
                        ?><li><div class="wsko-user-progress-percent"><?=WSKO_Class_Helper::format_number($clicks_perc)?> %</div><div class="wsko-user-progress-meta"><span class="wsko-label-sm"><?=__('Monthly Clicks', 'wsko')?></span><span class="wsko-sublabel"><?=sprintf(__('From %s to %s', 'wsko'),WSKO_Class_Helper::format_number($clicks_ref), WSKO_Class_Helper::format_number($clicks))?></span></li><?php
                    }
                    if ($imps_perc > 20)
                    {
                        ?><li><div class="wsko-user-progress-percent"><?=WSKO_Class_Helper::format_number($imps_perc)?> %</div><div class="wsko-user-progress-meta"><span class="wsko-label-sm"><?=__('Monthly Impressions', 'wsko')?></span><span class="wsko-sublabel"><?=sprintf(__('From %s to %s', 'wsko'),WSKO_Class_Helper::format_number($imps_ref), WSKO_Class_Helper::format_number($imps))?></span></li><?php
                    }
                    ?>
                    </ul>
                </div>
                <style>
                .wsko-ranking-delta ul li {
                    display: inline-block;
                    padding:  0px 15px;
                }
                .wsko-ranking-delta {
                    text-align:center;
                    padding: 10px 0px;
                }
                .wsko-sublabel {
                    font-size: smaller !important;
                    opacity: 0.8;
                    margin: 0px !important;
                }
                .wsko-label-sm {
                    text-transform:uppercase;
                    opacity:0.8;
                    display:block;
                }
                .wsko-ranking-delta .wsko-user-progress-percent {
                    display: inline-block;
                    vertical-align: super;
                    margin-right: 15px;
                    font-size: 25px;
                    letter-spacing: -2px;
                    color: #27ae60;
                }
                .wsko-ranking-delta .wsko-user-progress-meta {
                    display: inline-block;
                    text-align:left;
                }
                </style><?php
                if ($type == 'plugin_notice')
                {
                    ?></div><?php
                }
            }
        }
    }
}
?>