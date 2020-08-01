<?php
if (!defined('ABSPATH')) exit;


$current_user = wp_get_current_user();
$current_user_mail = $current_user->user_email;
$current_user_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
?>
<div class="modal fade wsko_modal" id="wsko_modal_feedback">
  <div class="modal-dialog">
    <div class="modal-content">
	    <button type="button" class="close m5" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=__('Close', 'wsko')?></span></button>


      <div class="modal-header">
        <h4 class="modal-title"><?=__('Contact us!', 'wsko')?></h4>
      </div>
		<form id="wsko_feedback_form" class="wsko-form-feedback" data-nonce="<?=wp_create_nonce('wsko-feedback')?>">
	    <div class="modal-body">
			<div class="row">
				<div class="wsko-feedback-notices col-md-12"></div>
				<div class="form-group clearfix mb5">
					<div class="col-sm-9 col-sm-offset-3">	
						<fieldset class="wsko-feedback-type">
							<div class="btn-group btn-group-justified" data-toggle="buttons">
								<label class="btn btn-success active wsko-feedback-type-btn">
									<input type="radio" name="type" value="1" autocomplete="off" checked> <?=__('Support', 'wsko')?>
								</label>
								<label class="btn btn-success wsko-feedback-type-btn">
									<input type="radio" name="type" value="2" autocomplete="off"> <?=__('Feedback', 'wsko')?>
								</label>
								<label class="btn btn-success wsko-feedback-type-btn">
									<input type="radio" name="type" value="3" autocomplete="off"> <?=__('Questions', 'wsko')?>
								</label>
							</div>
						</fieldset>
					</div>
				</div>	
				<div class="form-group">
					<div class="col-sm-3">
						<label><?=__('Full Name', 'wsko')?></label><br/>
						<small class="text-off"><?=__('Your name (optional)', 'wsko')?></small>
					</div>
					<div class="col-sm-9">
						<input placeholder="<?=__('Enter your name (optional)', 'wsko')?>" class="form-control wsko-feedback-name" type="text" value="<?=$current_user_name ? $current_user_name : ''?>" name="name">
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<label><?=__('Email', 'wsko')?></label><br/>
						<small class="text-off"><?=__('Your mail adress, so we can answer your question', 'wsko')?></small>
					</div>
					<div class="col-sm-9">
						<input placeholder="<?=__('Your contact email adress', 'wsko')?>" class="form-control wsko-feedback-email" type="email" name="email" value="<?=$current_user_mail ? $current_user_mail : ''?>" required>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="form-group">
					<div class="col-sm-3">
						<label><?=__('Subject', 'wsko')?></label><br/>
						<small class="text-off"><?=__('A brief summary of your request', 'wsko')?></small>
					</div>
					<div class="col-sm-9">
						<input placeholder="<?=__('A brief title', 'wsko')?>" class="form-control wsko-feedback-title" type="text" name="title" required>
					</div>
					<div class="clearfix"></div>
				</div>
				
				<div class="form-group">
					<div class="col-sm-3">	
						<label><?=__('Message', 'wsko')?></label><br/>
						<small class="text-off"><?=__('Tell us what\'s on your mind', 'wsko')?></small>
					</div>
					<div class="col-sm-9">
						<textarea rows="10" placeholder="<?=__('Your Message', 'wsko')?>" class="form-control wsko-feedback-msg" name="msg" required></textarea>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="form-group wsko-support-options">
					<div class="col-sm-9 col-sm-offset-3">
						<label><input type="checkbox" class="form-control wsko-feedback-reports" name="reports" checked> <?=__('Append Error Reports', 'wsko')?></label><br/>
						<small class="text-off"><?=__('Add your last 20 error reports to your ticket, to make it easier for us to solve your problem. You can view every report in your settings, if the reporting view has been activated in the advanced panel.', 'wsko')?></small>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="form-group">
					<div class="col-sm-9 col-sm-offset-3">
						<button class="button button-primary wsko-feedback-submit" type="submit"><i class="fa fa-spin fa-spinner" style="display:none;"></i> <?=__('Send', 'wsko')?></button>
						<button type="button" class="button" data-dismiss="modal"><?=__('Cancel', 'wsko')?></button>
					</div>
				</div>				
			</div>	
	    </div>
	  </form>
	  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal --> 