<?php
if (!defined('ABSPATH')) exit;


$current_user = wp_get_current_user();
$current_user_mail = $current_user->user_email;
$current_user_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
?>
<div class="modal fade wsko_modal" id="wsko_pro_modal_wrapper">
  <div class="modal-dialog" style="width: 900px;">
    <div class="modal-content">
	    <button type="button" class="close m5" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=__('Close', 'wsko')?></span></button>
        <div class="modal-header"><div>
        <div class="modal-body wsko-pro-modal-inner">
            <div class="wsko-pro-modal-header" style="text-align:center;margin-top:20px;">
              <p class="wsko-label-sm"><?=__('More SEO Tools & Data in the WordPress Backend', 'wsko')?></p>
              <h4 class="modal-title"><?=__('BAVOKO SEO Tools Pro', 'wsko')?></h4>
              <p style="max-width: 600px;margin: 10px auto;"><?=__('The PRO version of BAVOKO SEO Tools gives you access to numerous additional functions that provide you with even more effective support in search engine optimization.', 'wsko')?></p>
            </div>
            <ul class="pro-advantages-list" style="column-count: 2;padding: 20px 10px;">
              <?=implode('', wsko_loc('general', 'pro_advantages_list'))?>
            </ul>
            <?php WSKO_Class_Template::render_ajax_beacon('wsko_get_ranking_deltas', array('post' => array(), 'plain_js' => true)); ?>
            <div style="text-align:center;margin-bottom:20px;">
              <a class="btn btn-primary" href="https://www.bavoko.tools/pricing/" target="_blank"><?=__('View Plans', 'wsko')?></a>
            </div> 
        </div> 
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal --> 