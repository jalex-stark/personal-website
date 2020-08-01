<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$wsko_data = WSKO_Class_Core::get_data();
$is_update = isset( $wsko_data['version_pre'] ) && $wsko_data['version_pre'] && $wsko_data['version_pre'] != WSKO_VERSION;
$slide = 0;
$is_premium = false;
$ga_valid_se = !WSKO_Class_Search::check_se_access();
$importable_plugins = WSKO_Class_Compatibility::get_importable_plugins();
$step = 'search';

if ( !$ga_valid_se ) {
    
    if ( $is_premium ) {
        $step = 'bavoko';
    } else {
        $step = 'search';
    }

} else {
    
    if ( $is_premium && !$ga_valid_an ) {
        $step = 'analytics';
    } else {
        
        if ( $importable_plugins ) {
            $step = 'import';
        } else {
            $step = 'onpage';
        }
    
    }

}

?>

<div class="wsko-setup-wrapper wrap wsko_wrapper">
	<div class="align-center wsko-notif-fix">
		<img class="wsko_logo" src="<?php 
echo  WSKO_PLUGIN_URL . 'admin/img/logo-bl.png' ;
?>" />
		<h2><?php 
echo  __( 'BAVOKO SEO Tools Setup', 'wsko' ) ;
?></h2>
	</div>
	<div style="margin-top:20px;" class="align-center">
		<img class="wsko_logo" src="<?php 
echo  WSKO_PLUGIN_URL . 'admin/img/logo-bl.png' ;
?>" />
		<h2><?php 
echo  __( 'BAVOKO SEO Tools Setup', 'wsko' ) ;
?></h2>
	</div>

	<div class="wsko-setup-notifications-wrapper">
	</div>

	<div class="wsko-setup-slide-wrapper">
		<!-- Steps Legend -->	
		<div class="wsko-setup-slide-legend">
			<ul id="progressbar">
				<?php 
?>				
				<li class="<?php 
echo  ( in_array( $step, array(
    'onpage',
    'import',
    'analytics',
    'search'
) ) ? 'active' : '' ) ;
?>"><?php 
echo  __( 'Search API', 'wsko' ) ;
?></li>
				<?php 
?>					
				<?php 

if ( $importable_plugins ) {
    ?>
						<li class="<?php 
    echo  ( in_array( $step, array( 'onpage', 'import' ) ) ? 'active' : '' ) ;
    ?>"><?php 
    echo  __( 'Import Data', 'wsko' ) ;
    ?></li>
					<?php 
}

?>						
				<li class="<?php 
echo  ( in_array( $step, array( 'onpage' ) ) ? 'active' : '' ) ;
?>"><?php 
echo  __( 'Onpage', 'wsko' ) ;
?></li>
			</ul>
		</div>

		<!-- Steps Content -->
		<div class="setup-steps-wrapper">
			<?php 
?>

			<div class="setup-steps setup-step-<?php 
echo  ++$slide ;
?> wsko-section section wsko-setup-slide <?php 
echo  ( $step == 'search' ? 'wsko-setup-slide-active' : '' ) ;
?>" data-slide="<?php 
echo  $slide ;
?>">
				<h3 class="panel-heading"><?php 
echo  sprintf( __( '%d. Step: Google Search Console API', 'wsko' ), $slide ) ;
?></h3>
				<?php 
WSKO_Class_Template::render_template( 'settings/view-api-search.php', array() );
?>

				<a class="button wsko-setup-control wsko-setup-control-prev" href="#" data-action="prev"><?php 
echo  __( 'Previous', 'wsko' ) ;
?></a>				
				<a class="button wsko-setup-control wsko-setup-control-next" href="#" data-action="next"><?php 
echo  __( 'Next', 'wsko' ) ;
?></a>
			</div>

			<?php 
?>	

			<?php 

if ( $importable_plugins ) {
    ?>
				<div style="margin-bottom:20px;" class="setup-steps setup-step-<?php 
    echo  ++$slide ;
    ?> wsko-section section wsko-setup-slide <?php 
    echo  ( $step == 'import' ? 'wsko-setup-slide-active' : '' ) ;
    ?>" data-slide="<?php 
    echo  $slide ;
    ?>">
					<h3 class="panel-heading"><?php 
    echo  sprintf( __( '%d. Step: Import Data', 'wsko' ), $slide ) ;
    ?></h3>
					<?php 
    WSKO_Class_Template::render_template( 'settings/view-import.php', array() );
    ?>

					<a class="button wsko-setup-control wsko-setup-control-prev" href="#" data-action="prev"><?php 
    echo  __( 'Previous', 'wsko' ) ;
    ?></a>
					<a class="button wsko-setup-control wsko-setup-control-next" href="#" data-action="next"><?php 
    echo  __( 'Next', 'wsko' ) ;
    ?></a>
				</div>
			<?php 
}

?>

			<div style="margin-bottom:20px;" class="setup-steps setup-step-<?php 
echo  ++$slide ;
?> wsko-section section wsko-setup-slide <?php 
echo  ( $step == 'onpage' ? 'wsko-setup-slide-active' : '' ) ;
?>" data-slide="<?php 
echo  $slide ;
?>">
				<h3 class="panel-heading"><?php 
echo  sprintf( __( '%d. Step: Onpage Analysis', 'wsko' ), $slide ) ;
?></h3>
				<?php 
WSKO_Class_Template::render_template( 'onpage/template-analysis-config.php', array(
    'pt_width' => 'col-sm-6 col-md-6 col-xs-12',
) );
?>
			
				<a class="button wsko-setup-control wsko-setup-control-prev" href="#" data-action="prev"><?php 
echo  __( 'Previous', 'wsko' ) ;
?></a>
				<?php 
WSKO_Class_Template::render_ajax_button(
    __( 'Finish Setup', 'wsko' ),
    'finish_setup',
    array(),
    array(
    'class' => 'wsko-setup-control-finish button-default',
)
);
?>
			</div>
		</div>

		<div class="wsko-setup-slide-controls">
			<div style="display:block; margin-top: 5px; float:right;">
				<?php 
WSKO_Class_Template::render_ajax_button(
    __( 'Skip Setup', 'wsko' ),
    'finish_setup',
    array(),
    array(
    'class'     => 'wsko-setup-control-finish wsko-text-off dark',
    'no_button' => true,
)
);
?>
				<span class="wsko-text-off"> | </span>
				<a class="wsko-give-feedback wsko-inline-block text-off dark" style="cursor:pointer;"><?php 
echo  __( 'Contact Support', 'wsko' ) ;
?></a>
			</div>
		</div>
	</div>
</div>