<?php
if (!defined('ABSPATH')) exit;

/* TODO
$active = false

if ( !$review_sent ) {
    if ( $installation_date + 3 days <= today ) {
        if ( $last_seen + 24h <= $current_time ) //zum testen mal alle 5 min {
            $active = true;
        }
    }
}
*/
$wsko_data = WSKO_Class_Core::get_data();
/* $install_time = WSKO_Class_Core::get_install_time();
if ((!isset($wsko_data['feedback_sent']) || !$wsko_data['feedback_sent']) &&
    (!$install_time || $install_time < (time()-(60*60*24*3))) &&
    (!isset($wsko_data['skipped_feedback_today']) || $wsko_data['skipped_feedback_today'] < (time()-(60*60*24))) &&
    (!isset($wsko_data['skipped_feedback']) || !$wsko_data['skipped_feedback'])) { */ ?>
    <div class="nps-wrapper active <?php /* TODO: if ($active) ? 'active' : '' */ ?>">
        <a href="#" class="pull-right wsko-text-off dark nps-close"><i class="fa fa-times"></i></a>
        
        <div class="nps-form-wrapper">
            <div class="nps-first-step">
                <?php WSKO_Class_Template::render_ajax_button(__('Later', 'wsko'), 'skip_nps_feedback', array('temp' => true), array()); ?>
                <?php WSKO_Class_Template::render_ajax_button(__('Never', 'wsko'), 'skip_nps_feedback', array(), array()); ?>
                <?php /* TODO: Send nps-form-data to zoho via api and (1 Review per user / disable reviewing for user, when data is sent) */ ?>
                <form class="nps-form" data-nonce="<?=wp_create_nonce('wsko_send_nps_feedback')?>">
                    <p><?=__('How likely are you to share BAVOKO SEO Tools with your Friends and Colleagues', 'wsko')?></p>
                    <fieldset class="rating nps-rating-wrapper">
                        <label><input type="radio" class="nps-rating" name="rating" value="1"> <?=__('Not likely', 'wsko')?> </label>
                        <label><input type="radio" class="nps-rating" name="rating" value="2"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="3"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="4"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="5"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="6"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="7"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="8"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="9"></label>
                        <label><input type="radio" class="nps-rating" name="rating" value="10"> <?=__('Very likely', 'wsko')?></label>                                                                                                                                            
                    </fieldset>                               

                    <div class="nps-inner-form" style="display:none;">
                        <textarea class="nps-msg" name="msg"></textarea>
                        <!-- TODO: Ajax send to a table (Zoho) -->
                        <input type="submit" value="<?=__('Send', 'wsko')?>" class="nps-submit btn btn-flat">
                    </div>    
                </form>    
            </div>

            <div style="display:none" class="nps-second-step">
                <!-- TODO: AJAX Succes: Change Icon on success/error  (fa-check-circle, fa-times-circle) -->
                <i class="fa fa-check-circle wsko-green nps-succes"></i> 
                <label><?=__('Thank you very much', 'wsko')?></label>
                <!-- TODO: if score > 7: --> <p><?=__('Please also rate us on Wordpress', 'wsko')?></p>
                <a class="" href="#"><?=__('Rate on WordPress.org', 'wsko')?></a>
                <a class="nps-close" href="#"><?=__('Skip', 'wsko')?></a>
            </div>
        </div>    
    </div>
<?php /* } */ ?>